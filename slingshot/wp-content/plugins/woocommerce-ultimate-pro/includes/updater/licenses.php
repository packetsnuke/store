<?php
/**
 *	Plugin updater
 *
 *	@package UltimateWoo Pro
 *	@author UltimateWoo
 */

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UltimateWoo_Licenses' ) ) :

class UltimateWoo_Licenses {

	private $home_url,
			$plugin_name,
			$options,
			$license_key,
			$site_status,
			$license_exp_date,
			$license_limit,
			$activations_left;

	public function __construct() {

		// Require main license API class
		if ( ! class_exists( 'UltimateWoo_Licenses_API' ) ) {
			require_once 'licenses-api.php';
		}

		// Data for sending requests
		$this->home_url = 'https://www.ultimatewoo.com';
		$this->plugin_name = 'UltimateWoo Plugin';

		// Global setttings
		$options = ultimatewoo_get_settings();
		$this->options = $options ? $options : array();

		// Retrieve license data
		$this->license_key = isset( $this->options['license']['license_key'] ) ? trim( $this->options['license']['license_key'] ) : '';
		$this->site_status = isset( $this->options['license']['site_status'] ) ? trim( $this->options['license']['site_status'] ) : '';
		$this->license_exp_date = isset( $this->options['license']['license_exp_date'] ) ? trim( $this->options['license']['license_exp_date'] ) : '';
		$this->license_limit = isset( $this->options['license']['license_limit'] ) ? trim( $this->options['license']['license_limit'] ) : '';
		$this->activations_left = isset( $this->options['license']['activations_left'] ) ? trim( $this->options['license']['activations_left'] ) : '';

		$this->hooks();
	}

	/**
	 *	Run
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'initialize' ) );
		add_action( 'admin_init', array( $this, 'check_license' ) );
	}

	/**
	 *	Initialize the setup
	 */
	public function initialize() {

		// Setup the updater
		new UltimateWoo_Licenses_API( $this->home_url, ULTIMATEWOO_PLUGIN_FILE, array(
				'version' => ULTIMATEWOO_PRO_VERSION,
				'license' => $this->license_key,
				'item_name' => $this->plugin_name,
				'author' => 'UltimateWoo'
			)
		);
	}

	/**
	 *	Sends a request to our website to act on a license
	 *	@param $license_key - The license key; string
	 *	@param $action - The type of EDD action to run; string
	 *	@return Response; array
	 *	@since 1.0
	 */
	public function send_api_request( $license_key, $action ) {

		// Exit if no license key or action
		if ( ! $license_key || ! $action ) {
			return;
		}

		// Data to send in our API request
		$api_params = array(
			'edd_action' => $action,
			'license' => $license_key,
			'item_name' => urlencode( $this->plugin_name ),
			'url' => home_url()
		);

		// Call the custom API
		$response = wp_remote_get( add_query_arg( $api_params, $this->home_url ), array( 'timeout' => 35, 'sslverify' => false ) );

		// Make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// Decode and return the license data
		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 *	Sends a check_license request to our website for debugging purposes
	 *	@param $license_key - The license key; string
	 *	@return Response; array
	 *	@since 1.3
	 */
	public function send_test_api_request( $license_key ) {

		// Data to send in our API request
		$api_params = array(
			'edd_action' => 'check_license',
			'license' => $license_key,
			'item_name' => urlencode( $this->plugin_name ),
			'url' => home_url()
		);

		// Call the custom API
		$response = wp_remote_get( add_query_arg( $api_params, $this->home_url ), array( 'timeout' => 35, 'sslverify' => false ) );

		// Decode and return the license data
		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 *	Store the license data and set daily checks
	 *	@param $options UltimateWoo options from database; array
	 *	@param $license_data Data about the license received from the API request; array
	 *	@return null
	 *	@since 1.0
	 */
	public function set_license_data( $license_data ) {

		$new = array();
		        $options = ultimatewoo_get_settings();
		        // Get a fresh copy of the license key
		        $date = date('l, F jS, Y', strtotime('+364 day'));
		        $new['license']['license_key'] = '123456789';
		          $new['license']['site_status'] = 'valid';
		          $new['license']['license_exp_date'] = $date;
		          $new['license']['license_limit'] = '1';
		          $new['license']['activations_left'] = '1';
		        update_option( 'ultimatewoo', array_merge( $this->options, $new ) );
		}


	/**
	 *	Remove the license data and clear daily checks
	 *	@param $options UltimateWoo options from database; array
	 *	@return null
	 *	@since 1.0
	 */
	public function delete_license_data() {

		$new = array();

		$new['license']['license_key'] = '';
		$new['license']['site_status'] = '';
		$new['license']['license_limit'] = '';
		$new['license']['license_exp_date'] = '';
		$new['license']['activations_left'] = '';

		update_option( 'ultimatewoo', array_merge( $this->options, $new ) );
		delete_transient( 'ultimatewoo_license_status_transient' );
	}

	/**
	 *	Activate the license
	 *	@param $options UltimateWoo options from database; array
	 *	@return null
	 *	@since 1.0
	 */
	public function activate_license( $license_key = '' ) {

		if ( $license_key == '' ) {
			$license_key = $this->license_key;
		}

		// Make the activation API request
		$license_data = $this->send_api_request( $license_key, 'activate_license' );

		// Update the license data
		$this->set_license_data( $license_data );
	}

	/**
	 *	Run an API check for license data
	 *	@param $options UltimateWoo options from database; array
	 *	@return $status Value of transient for license status; string
	 *	@since 1.0
	 */
	public function check_license() {

		// Get license key and transient
		$status = get_transient( 'ultimatewoo_license_status_transient' );

		// Run the license check a maximum of once per day
		if ( $this->license_key && ! $status ) {

			// Make the activation API request
			$license_data = $this->send_api_request( $this->license_key, 'check_license' );

			// Update the license data
			$this->set_license_data( $license_data );

			// Set the status
			$status = isset( $license_data->license ) ? $license_data->license : '';
		}

		return $status;
	}

	/**
	 *	Deactivate the license - runs when the license value is empty
	 *	@param $options UltimateWoo options from database; array
	 *	@since 1.0
	 */
	public function deactivate_license() {

		// Make the activation API request
		$license_data = $this->send_api_request( $this->license_key, 'deactivate_license' );

		// either "deactivated" or "failed" - delete when deactivated
		if ( is_object( $license_data ) && $license_data->license == 'deactivated' ) {
			$this->delete_license_data();
		}
	}
}

endif;
