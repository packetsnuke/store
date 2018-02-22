<?php
/**
 * Copyright: © 2009-2017 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_woocommerce_active() ) {
	return;
}

define( 'WC_STAMPS_INTEGRATION_VERSION', '1.3.2' );

/**
 * WC_Stamps_Integration class
 */
class WC_Stamps_Integration {

	/**
	 * Constructor
	 */
	public function __construct() {
		define( 'WC_STAMPS_INTEGRATION_FILE', __FILE__ );
		include_once( dirname( __FILE__ ) . '/includes/class-wc-stamps-settings.php' );

		$test_mode = defined( 'WC_STAMPS_TEST_MODE' ) && WC_STAMPS_TEST_MODE || 'yes' === get_option( 'wc_settings_stamps_test_mode' );
		if ( $test_mode ) {
			define( 'WC_STAMPS_INTEGRATION_WSDL_FILE', 'test-swsimv50.wsdl' );
			define( 'WC_STAMPS_INTEGRATION_AUTH_ENDPOINT', 'https://stamps.woocommerce.com/v50/authenticate/test.php' );
		} else {
			define( 'WC_STAMPS_INTEGRATION_WSDL_FILE', 'swsimv50.wsdl' );
			define( 'WC_STAMPS_INTEGRATION_AUTH_ENDPOINT', 'https://stamps.woocommerce.com/v50/authenticate/' );
		}

		include_once( 'includes/class-wc-stamps-api.php' );
		include_once( 'includes/class-wc-stamps-balance.php' );

		if ( is_admin() && current_user_can( 'manage_woocommerce' ) ) {
			include_once( 'includes/class-wc-stamps-order.php' );
			include_once( 'includes/class-wc-stamps-post-types.php' );
			include_once( 'includes/class-wc-stamps-labels.php' );
			include_once( 'includes/class-wc-stamps-label.php' );
			include_once( 'includes/class-wc-stamps-settings.php' );
		}

		register_activation_hook( __FILE__, array( $this, 'activation_check' ) );
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Check SOAP support on activation
	 */
	public function activation_check() {
		if ( ! class_exists( 'SoapClient' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
			wp_die( 'Sorry, but you cannot run this plugin, it requires the <a href="http://php.net/manual/en/class.soapclient.php">SOAP</a> support on your server to function.' );
		}
	}

	/**
	 * Localisation
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-shipping-stamps', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

function wc_stamps_init() {
	new WC_Stamps_Integration();
}

add_action( 'plugins_loaded', 'wc_stamps_init' );

//1.3.2