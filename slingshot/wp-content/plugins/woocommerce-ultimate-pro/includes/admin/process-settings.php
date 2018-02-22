<?php
/**
 *	Process the settings as they are saved.
 *
 *	@package UltimateWoo Pro
 *	@author UltimateWoo
 *	@since 1.0
 */

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UltimateWoo_Process_Settings' ) ) :

class UltimateWoo_Process_Settings {

	public function __construct() {

		$this->hooks();
	}

	/**
	 *	Run
	 */
	public function hooks() {

		add_action( 'admin_init', array( $this, 'process_settings' ) );
	}

	/**
	 *	Process the settings
	 */
	public function process_settings() {

		// Query arg not set or not correct
		if ( ! isset( $_POST['ultimatewoo'] ) ) {
			return;
		}

		if ( ! array_key_exists( 'license', $_POST['ultimatewoo'] ) && ! array_key_exists( 'modules', $_POST['ultimatewoo'] ) ) {
			return;
		}

		// No administrative privileges
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->no_permission_error();
		}

		// Get HTTP referer without settings-update query arg
		$referer = isset( $_POST['_wp_http_referer'] ) ? remove_query_arg( array( 'settings-updated', 'tab' ), $_POST['_wp_http_referer'] ) : '';

		// Required HTTP referer
		$required_referer = add_query_arg( 'page', 'ultimatewoo', parse_url( ULTIMATEWOO_SETTINGS_PAGE_URL, PHP_URL_PATH ) );

		// HTTP referer is not set, or is not the required referer
		if ( ! $referer || $referer !== $required_referer ) {
			$this->no_permission_error();
		}

		if ( ! isset( $_POST['ultimatewoo_admin_nonce'] ) || ! wp_verify_nonce( $_POST['ultimatewoo_admin_nonce'], 'ultimatewoo_admin_nonce' ) ) {
			$this->no_permission_error();
		}

		// Get our settings
		$options = ultimatewoo_get_settings();

		// License setting was updated
		if ( isset( $_POST['ultimatewoo']['license'] ) ) {

			// Deactivate license before empty license field is saved, then exit/redirect
			if ( isset( $_POST['deactivate-license'] ) && intval( $_POST['deactivate-license'] ) === 1 ) {
				UltimateWoo_Pro()->licenses->deactivate_license();
				$this->save_redirect();
			}

			$new = array();
			$cleaned_license_values = array();

			// License is set, so sanitize the input values
			if ( isset( $_POST['ultimatewoo']['license'] ) ) {
				foreach ( $_POST['ultimatewoo']['license'] as $key => $val ) {
					$cleaned_license_values[$key] = sanitize_text_field( $val );
				}
			}

			/**
			 *	Store new license array
			 *	Array of license data or empty array
			 */
			$new['license'] = $cleaned_license_values;

			// Merge new license array with other settings, and update option
			update_option( 'ultimatewoo', array_merge( $options, $new ) );

			// Activate license after license is saved
			if ( isset( $_POST['activate-license'] ) && intval( $_POST['activate-license'] ) === 1 ) {
				UltimateWoo_Pro()->licenses->activate_license( $_POST['ultimatewoo']['license']['license_key'] );
			}

			// Redirect
			$this->save_redirect();
		}

		// Module settings were updated
		elseif ( $_POST['ultimatewoo']['modules'] ) {

			unset( $_POST['ultimatewoo']['modules']['triggered'] );

			$new = array();
			$cleaned_modules_values = array();

			// Modules are enabled, so sanitize the input values
			if ( isset( $_POST['ultimatewoo']['modules'] ) ) {
				foreach ( $_POST['ultimatewoo']['modules'] as $key => $val ) {
					$cleaned_modules_values[$key] = $val ? 1 : '';
				}
			}

			/**
			 *	Store new license array
			 *	Array of enabled modules or empty array
			 */
			$new['modules'] = $cleaned_modules_values;

			// Merge new modules array with other settings, and update option
			update_option( 'ultimatewoo', array_merge( $options, $new ) );

			// Redirect
			$this->save_redirect();
		}
	}

	/**
	 *	Error message
	 */
	private function no_permission_error() {
		wp_die( __( 'Error.', 'ultimatewoo-pro' ) );
	}

	/**
	 *	Redirect
	 */
	private function save_redirect() {
		wp_redirect( add_query_arg( array(
			'settings-updated' => 'true',
			'tab' => $_GET['tab']
		), ULTIMATEWOO_SETTINGS_PAGE_URL ) );
		exit;
	}
}

endif;

new UltimateWoo_Process_Settings;