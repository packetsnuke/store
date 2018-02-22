<?php
/**
 *	Add and display main admin settings page
 *
 *	@package UltimateWoo Pro
 *	@author UltimateWoo
 *	@since 1.0
 */

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UltimateWoo_Pro_Settings_Page' ) ) :

class UltimateWoo_Pro_Settings_Page {

	private $screen_id, $settings;

	public $pages;

	public function __construct() {
		$this->includes();
		$this->hooks();	
	}

	public function includes() {
		require_once 'module-sections.php';
		require_once 'process-settings.php';
		require_once 'lib/simpleadminui/loader.php';
	}

	public function hooks() {

		add_action( 'current_screen', array( $this, 'set_screen_id' ) );

		add_action( 'init', array( $this, 'register_admin_page' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueues' ) );

		// Main page
		// {page_slug}_meta_boxes_{tab_key}
		add_action( 'ultimatewoo_meta_boxes_modules', array( $this, 'modules_tab' ) );
		add_action( 'ultimatewoo_meta_boxes_license', array( $this, 'license_tab' ) );
		add_action( 'ultimatewoo_meta_boxes_support', array( $this, 'support_tab' ) );

		// add_action( 'ultimatewoo_settings_mb_end_payment_gateways', array( $this, 'autorize_net_sim_enable' ) );
	}

	/**
	 *	Set the current screen ID
	 */
	public function set_screen_id() {
		if ( is_admin() ) {
			$this->settings = ultimatewoo_get_settings();
			$this->screen_id = get_current_screen()->id;
		}
	}

	/**
	 *	Set admin notices
	 */
	public function admin_notices() {
		
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'true' ) {
			include_once 'templates/settings-updated.php';
		}
	}

	/**
	 *	Load admin styles/scripts
	 */
	public function enqueues() {

		// Load on our settings page
		if ( get_current_screen()->base !== 'woocommerce_page_ultimatewoo' ) {
			return;
		}

		// Admin CSS
		wp_enqueue_style( 'ultimatewoo-admin-styles', ULTIMATEWOO_PLUGIN_DIR_URL . 'assets/css/admin.css', '', ULTIMATEWOO_PRO_VERSION );
	}

	/**
	 *	Add the settings page
	 */
	public function register_admin_page() {

		if ( ! is_admin() ) {
			return;
		}

		$this->pages = array(
			'ultimatewoo' => array(
				'page_title' => __( 'UltimateWoo Settings', 'ultimatewoo-pro' ), // page title
				'menu_title' => __( 'UltimateWoo', 'ultimatewoo-pro' ), // menu title
				'capabilities' => 'manage_options', // capability a user must have to see the page
				'priority' => 999, // priority for menu positioning
				'icon' => '', // URL to an icon, or name of a Dashicons helper class to use a font icon
				'body_content' => '', // callback that prints to the page, above the metaboxes
				'parent_slug' => 'woocommerce', // If subpage, slug of the parent page (i.e. woocommerce), or file name of core WordPress page (i.e. edit.php); leave empty for a top-level page
				'sortable' => true, // whether the meta boxes should be sortable
				'collapsable' => true, // whether the meta boxes should be collapsable
				'contains_media' => false, // whether the page utilizes the media uploader
				'tabs' => array(
					'modules' => __( 'Modules', 'ultimatewoo-pro' ),
					'license' => __( 'License', 'ultimatewoo-pro' ),
					'support' => __( 'Support', 'ultimatewoo-pro' ),
				),
			)
		);

		// Remove license tab when filtered
		if ( apply_filters( 'ultimatewoo_settings_page_remove_license_boxes', false ) == true ) {
			unset( $this->pages['ultimatewoo']['tabs']['license'] );
		}

		// Register them all
		new \UltimateWoo\AdminPages\Admin_Pages( $this->pages );
	}

	/**
	 *	Add Modules tab meta boxes
	 */
	public function modules_tab() {

		if ( isset( $this->settings['license']['site_status'] ) && $this->settings['license']['site_status'] == 'valid' ) {

			// Add a meta box for each section of modules
			foreach ( ultimatewoo_get_module_sections() as $key => $section ) {
				add_meta_box(
					"ultimatewoo_modules_{$key}",
					$section['section_title'],
					array( $this, 'render_modules_mb' ),
					$this->screen_id,
					'normal',
					'high',
					array(
						'key' => $key,
						'section' => $section
					)
				);
			}

		} else {

			add_meta_box( "ultimatewoo_modules_site_inactive", __( 'Site Inactive', 'ultimatewoo-pro' ), function() {
				include_once 'templates/site-inactive.php';
			}, $this->screen_id, 'normal', 'high' );
		}

		add_meta_box( 'ultimatewoo_active_modules', __( 'Active Modules', 'ultimatewoo-pro' ), array( $this, 'render_active_modules' ), $this->screen_id, 'side', 'high' );
	}

	/**
	 *	Modules meta boxes
	 */
	public function render_modules_mb( $post, $args ) {
		include 'templates/module-boxes.php';
	}

	/**
	 *	Active Modules meta box
	 */
	public function render_active_modules() {

		$all_modules = ultimatewoo_get_all_modules();

		if ( isset( $this->settings['modules'] ) && ! empty( $this->settings['modules'] ) ) {

			echo '<ol>';
			foreach ( $this->settings['modules'] as $key => $active_module ) {
				printf( '<li><a href="#%1$s">%2$s</a></li>', $key, $all_modules[$key] );
			}
			echo '</ol>';
		
		} else {

			_e( 'You have no active modules right now.', 'ultimatewoo-pro' );
		}
	}

	/**
	 *	Add License tab meta boxes
	 */
	public function license_tab() {		
		add_meta_box( 'ultimatewoo_license_key', __( 'License Key', 'ultimatewoo-pro' ), array( $this, 'render_license_key_mb' ), $this->screen_id, 'normal', 'high' );
		add_meta_box( 'ultimatewoo_license_data', __( 'License Data', 'ultimatewoo-pro' ), array( $this, 'render_license_data_mb' ), $this->screen_id, 'side', 'high' );
	}

	/**
	 *	Render License Key meta box
	 */
	public function render_license_key_mb() {
		$license_key = isset( $this->settings['license']['license_key'] ) ? $this->settings['license']['license_key'] : '';
		$site_status = isset( $this->settings['license']['site_status'] ) ? $this->settings['license']['site_status'] : '';
		$license_exp_date = isset( $this->settings['license']['license_exp_date'] ) ? $this->settings['license']['license_exp_date'] : '';
		$license_limit = isset( $this->settings['license']['license_limit'] ) ? $this->settings['license']['license_limit'] : '';
		$activations_left = isset( $this->settings['license']['activations_left'] ) ? $this->settings['license']['activations_left'] : '';
		$db_version = isset( $this->settings['license']['db_version'] ) ? $this->settings['license']['db_version'] : '';
		include_once 'templates/license-key.php';
	}

	/**
	 *	Render License Data meta box
	 */
	public function render_license_data_mb() {
		$key = isset( $this->settings['license']['license_key'] ) ? $this->settings['license']['license_key'] : '';
		$status = isset( $this->settings['license']['site_status'] ) ? $this->settings['license']['site_status'] : '';
		$exp = isset( $this->settings['license']['license_exp_date'] ) ? $this->settings['license']['license_exp_date'] : '';
		$limit = isset( $this->settings['license']['license_limit'] ) ? (int) $this->settings['license']['license_limit'] : '';
		$remaining = isset( $this->settings['license']['activations_left'] ) ? $this->settings['license']['activations_left'] : '';
		include_once 'templates/license-data.php';
	}

	/**
	 *	Add Support tab meta boxes
	 */
	public function support_tab() {		
		add_meta_box( 'ultimatewoo_support', __( 'Need support?', 'ultimatewoo-pro' ), array( $this, 'render_support_mb' ), $this->screen_id, 'normal', 'high' );
		add_meta_box( 'ultimatewoo_wc_developer', __( 'Need a WooCommerce developer?', 'ultimatewoo-pro' ), array( $this, 'render_wc_developer_mb' ), $this->screen_id, 'normal', 'high' );
		add_meta_box( 'ultimatewoo_mc_optin', __( 'UltimateWoo Updates?', 'ultimatewoo-pro' ), array( $this, 'render_mcoptin_mb' ), $this->screen_id, 'side', 'high' );
	}

	/**
	 *	Render Support meta box
	 */
	public function render_support_mb() {
		include_once 'templates/support.php';
	}

	/**
	 *	Render WooCommerce Developer meta box
	 */
	public function render_wc_developer_mb() {
		include_once 'templates/developer.php';
	}

	/**
	 *	Render Mailchimp Opt-in meta box
	 */
	public function render_mcoptin_mb() {
		include_once 'templates/mc-optin.php';
	}

	/**
	 *	Add the link for enabling Authorize.net SIM when AIM is active
	 *	Change WC_Authorize_Net_AIM->get_file() to public scope
	 */
	public function autorize_net_sim_enable() {

		global $status, $page, $s;

		// add an action to enabled the legacy SIM gateway
		if ( isset( $this->settings['modules']['authorize_net_aim'] ) && $this->settings['modules']['authorize_net_aim'] == 1 ) :
			
			echo '<div class="ultimatewoo-info">';

			printf( '<strong>%s</strong> ', __( 'Additional Option:', 'ultimatewoo-pro' ) );

			// Activate option if currently disabled, else option to Deactivate
			if ( get_option( 'wc_authorize_net_aim_sim_active' ) ) {
				printf( '<a href="%s">%s</a>',
					esc_url( wp_nonce_url( add_query_arg( array(
								'action'        => 'wc_authorize_net_toggle_sim',
								'gateway'       => 'deactivate',
								'plugin_status' => $status,
								'paged'         => $page,
								's'             => $s
							), 'admin.php'
						), wc_authorize_net_aim()->get_file() ) ),
					__( 'Deactivate SIM gateway', 'ultimatewoo-pro' )
				);
			} else {
				printf( '<a href="%s">%s</a>',
					esc_url( wp_nonce_url( add_query_arg( array(
								'action'        => 'wc_authorize_net_toggle_sim',
								'gateway'       => 'activate',
								'plugin_status' => $status,
								'paged'         => $page,
								's'             => $s
							), 'admin.php'
						), wc_authorize_net_aim()->get_file() ) ),
					__( 'Activate SIM gateway', 'ultimatewoo-pro' )
				);
			}

			echo '</div>';

		endif;
	}
}

endif;

new UltimateWoo_Pro_Settings_Page;