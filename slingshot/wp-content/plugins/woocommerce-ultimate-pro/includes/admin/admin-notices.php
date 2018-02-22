<?php
/**
 *	Admin notices
 *	@package UltimateWoo Pro
 *	@author UltimateWoo
 */

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UltimateWoo_Admin_Notices' ) ) :

class UltimateWoo_Admin_Notices {

	private $user_id, $meta_key;

	public function __construct() {

		$this->hooks();
	}

	/**
	 *	Run
	 */
	public function hooks() {

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		add_action( 'admin_init', array( $this, 'dismiss_notices' ) );
	}

	/**
	 *	Notices displayed across the admin
	 */
	public function admin_notices() {

		// Exit if no options
		if ( ! $options = ultimatewoo_get_license_settings() ) {
			return;
		}

		$screen = get_current_screen();

		$screen_id = $screen->id;

		$status = isset( $options['license_status'] ) ? $options['license_status'] : false;

		$exp_date = date_format( date_create( $options['license_exp_date'] ), 'Y-m-d' );

		$days_until_exp = ( strtotime( $exp_date ) - strtotime( date('Y-m-d' ) ) ) / ( 3600*24 );

		$renew_url = ultimatewoo_get_renewal_link( 'button button-primary' );

		// Exit if current user is not an admin, or nag has been dismissed
		if ( ! current_user_can( 'manage_options' ) || intval( get_user_meta( $this->user_id, $this->meta_key, true ) ) === 1 ) {
			return;
		}

		// Don't show expiration admin notices if no license key
		if ( ! isset( $options['license_key'] ) || ! $options['license_key'] ) {
			return;
		}

		if ( $days_until_exp < 0 || $status == 'expired' ) {

			echo '<div class="updated notice" style="border-left: 4px solid #e74c3c; padding: 11px 15px;"><p>';

			_e( 'Your UltimateWoo license key has expired. Please renew your license to continue receiving important updates and support.', 'ultimatewoo-pro' );

			echo $renew_url;

			echo '</p></div>';

		} elseif ( $days_until_exp <= 14 && $status !== 'expired' ) {

			echo '<div class="updated notice" style="border-left: 4px solid #ffba00; padding: 11px 15px;"><p>';

			_e( 'Your UltimateWoo license key is set to expire in', 'ultimatewoo-pro' );

			printf( ' <strong>' . _n( '%s day', '%s days', $days_until_exp, 'ultimatewoo-pro' ) . '!</strong> ', $days_until_exp );

			_e( 'Please renew your license to continue receiving important updates and support.', 'ultimatewoo-pro' );

			echo $renew_url;

			echo '</p></div>';
		}
	}

	/**
	 *	Make admin notices dismissible
	 */
	public function dismiss_notices() {

		$this->user_id = get_current_user_id();

		$this->meta_key = 'ultimatewoo_licene_renew_nag';

		if ( isset( $_GET['uw_dismiss_update_nag'] ) && intval( $_GET['uw_dismiss_update_nag'] ) === 1 ) {

			if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'uw_dismiss_update_nag' ) ) {
				return;
			}

			update_user_meta( $this->user_id, $this->meta_key, 1 );
		}
	}
}

endif;

new UltimateWoo_Admin_Notices;