<?php
/**
 *	Update the database when needed
 *
 *	@package UltimateWoo Pro
 *	@author UltimateWoo
 */

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UltimateWoo_Database_Update' ) ) :

class UltimateWoo_Database_Update {

	private $options, $db_version, $new_options;

	public function __construct() {

		$this->options = ultimatewoo_get_settings();

		add_action( 'admin_notices', array( $this, 'db_update_notice' ) );

		add_action( 'admin_init', array( $this, 'update_database' ) );
	}

	/**
	 *	Admin notice for updating database
	 */
	public function db_update_notice() {

		if ( ! isset( $this->options['db_version'] ) || version_compare( $this->options['db_version'], ULTIMATEWOO_PRO_DATABASE_VERSION, '<' ) ) : ?>

		<div class="notice updated" style="margin-left: 0;">
			<p><?php _e( 'To complete your UltimateWoo installation/update, please run the database update. Before running the update, make sure you have a full backup of your database.', 'ultimatewoo-pro' ); ?></p>
			<p><a href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'ultimatewoo_update_database' ), ULTIMATEWOO_SETTINGS_PAGE_URL ), 'ultimatewoo_update_db_nonce' ); ?>" id="ultimatewoo-db-update" class="button button-primary" onclick="return confirm('<?php _e( "This will modify your database. Make sure to back up your database before proceeding.", "ultimatewoo-pro" ); ?>');">Run Database Update</a></p>
		</div>

		<?php endif;
	}

	/**
	 *	Update the database when the action is set
	 */
	public function update_database() {

		// Exit if update action was not requested
		if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'ultimatewoo_update_database' ) {
			return;
		}

		// Security check failed
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'ultimatewoo_update_db_nonce' ) ) {
			wp_die( __( 'You are not authorized to perform this action.', 'ultimatewoo-pro' ) );
		}

		// Get all database options
		$all_options = wp_load_alloptions();

		$this->new_options = array();

		// Continue only if no DB version or DB version is old
		if ( ! isset( $this->options['db_version'] ) || version_compare( $this->options['db_version'], ULTIMATEWOO_PRO_DATABASE_VERSION, '<' ) ) {

			// Get old options prefix
			$prefix = 'ultimatewoo_';
			$length = strlen( $prefix );

			// Loop through every option
			foreach( $all_options as $key => $val ) {

				// Only process old plugin options (has prefix)
				if ( ( substr( $key, 0, $length ) === $prefix ) ) {

					// Option has a value
					if ( $val ) {

						// Get the key, without old prefix
						$new_key = str_replace( $prefix, '', $key );

						// Check if option is a license or module setting
						if ( is_numeric( strpos( $new_key, 'license' ) ) || is_numeric( strpos( $new_key, 'activations_left' ) ) ) {

							// Changed license_status to site_status
							if ( $new_key == 'license_status' ) {
								$this->new_options['license']['site_status'] = $val;
							} else {
								$this->new_options['license'][$new_key] = $val;
							}

						} else {
							$this->new_options['modules'][$new_key] = $val;
						}
					}

					// Delete old option
					delete_option( $key );
				}
			}

			// Set database version element
			$this->new_options['db_version'] = ULTIMATEWOO_PRO_DATABASE_VERSION;

			// Merge current plugin options with new options and update the option
			update_option( 'ultimatewoo', array_merge( $this->options, $this->new_options ) );

			// Redirect
			wp_redirect( ULTIMATEWOO_SETTINGS_PAGE_URL );
			exit;
		}
	}
}

endif;

new UltimateWoo_Database_Update;