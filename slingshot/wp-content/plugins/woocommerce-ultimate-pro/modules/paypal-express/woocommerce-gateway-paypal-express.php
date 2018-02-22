<?php
/**
 * Copyright: (c) 2012-2016 SkyVerge, Inc.
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Gateway-PayPal-Express
 * @author    SkyVerge
 * @category  Gateway
 * @copyright Copyright (c) 2012-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

function woocommerce_gateway_paypal_express_upgrade_notice() {

	$message = sprintf(
		__( '%1$sA new version of the WooCommerce PayPal Express Gateway is ready%2$s. Please %3$sdownload and install it now%4$s to continue using PayPal Express Checkout.', 'ultimatewoo-pro' ),
		'<strong>',
		'</strong>',
		'<a href="' . esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce-gateway-paypal-express-checkout' ), 'install-plugin_woocommerce-gateway-paypal-express-checkout' ) ) . '">',
		'</a>'
	);

	echo sprintf( '<div class="error"><p>%s</p></div>', $message );
}
//add_action( 'admin_notices', 'woocommerce_gateway_paypal_express_upgrade_notice' );

//return;

// Required library class
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once SV_WC_FRAMEWORK_FILE;
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.4.0', __( 'WooCommerce PayPal Express Gateway', 'ultimatewoo-pro' ), __FILE__, 'init_woocommerce_gateway_paypal_express', array( 'is_payment_gateway' => true, 'minimum_wc_version' => '2.3.6', 'backwards_compatible' => '4.2.0' ) );

function init_woocommerce_gateway_paypal_express() {

/**
 * # WooCommerce PayPal Express Gateway Main Plugin Class
 *
 * ## Plugin Overview
 *
 * This plugin adds PayPal Express as a payment gateway.  This class handles all the
 * non-gateway tasks such as verifying dependencies are met, loading the text
 * domain, etc.
 *
 * ## Features
 *
 * + Payment Authorization
 * + Payment Charge
 * + Payment Auth Capture
 * + Detailed Customer Decline Messages
 *
 * ## Admin Considerations
 *
 * + A 'Capture Charge' order action link is added that allows the admin to capture a previously authorized charge for
 * an order
 *
 * ## Frontend Considerations
 *
 * PayPal Express is a unique hybrid redirect gateway. The basic process is:
 * 1) Customer clicks PPE button on cart page or checkout page
 * 2) Customer is redirected to PayPal, logs in, and confirms payment
 * 3) Customer is redirected back to checkout page with information pre-filled
 * 4) Customer clicks "Place Order" and is redirected to thank you page
 *
 * ## Database
 *
 * ### Global Settings
 *
 * + `woocommerce_paypal_express_settings` - the serialized gateway settings array
 *
 * ### Options table
 *
 * + `wc_paypal_express_version` - the current plugin version, set on install/upgrade
 *
 * ### Payment Order Meta
 *
 * + `_wc_paypal_express_environment` - the environment the transaction was created in, one of 'test' (sandbox) or 'production'
 * + `_wc_paypal_express_trans_id` - the transaction ID returned by PayPal
 * + `_wc_paypal_express_trans_date` - the payment transaction date
 * + `_wc_paypal_express_payer_id` - the payer ID returned by PayPal, unique to the buyer's account (not always provided)
 * + `_wc_paypal_express_charge_captured` - indicates if the transaction was captured, either `yes` or `no`
 * + `_wc_paypal_express_capture_trans_id` - the capture transaction ID returned by PayPal
 *
 * @since 2.0
 */
class WC_PayPal_Express extends SV_WC_Payment_Gateway_Plugin {


	/** plugin version number */
	const VERSION = '3.7.2';

	/** @var WC_PayPal_Express single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'paypal_express';

	/** plugin text domain, DEPRECATED as of 3.6.0 */
	const TEXT_DOMAIN = 'woocommerce-gateway-paypal-express';

	/** string the gateway class name */
	const GATEWAY_CLASS_NAME = 'WC_Gateway_PayPal_Express';

	/** string the gateway id */
	const GATEWAY_ID = 'paypal_express';


	/**
	 * Initializes the plugin
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'gateways' => array(
					self::GATEWAY_ID => self::GATEWAY_CLASS_NAME,
				),
				'supports' => array(
					self::FEATURE_CAPTURE_CHARGE,
				),
				'currencies' => array( 'AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR',
					'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'NOK', 'NZD', 'PHP',
					'PLN', 'GBP', 'RUB','SGD', 'SEK', 'CHF', 'TWD', 'THB', 'TRY', 'USD',
				),
			)
		);

		// include required files
		add_action( 'sv_wc_framework_plugins_loaded', array( $this, 'includes' ) );

		// display express checkout button in cart/mini-cart
		add_action( 'woocommerce_proceed_to_checkout',                 array( $this, 'render_express_checkout_button' ), 22 );
		add_action( 'woocommerce_widget_shopping_cart_before_buttons', array( $this, 'render_express_checkout_button' ), 12 );

		// maybe hide standard WC checkout button
		add_action( 'wp_footer', array( $this, 'maybe_hide_standard_checkout_button' ) );

		// process express checkout cancel link
		add_action( 'wp', array( $this, 'cancel_express_checkout' ) );

		// maybe redirect to PayPal when the gateway is included as a payment method at checkout
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'maybe_redirect' ) );

		// auto-generate username and password when using express checkout
		add_filter( 'pre_option_woocommerce_registration_generate_username', array( $this, 'conditional_yes' ) );
		add_filter( 'pre_option_woocommerce_registration_generate_password', array( $this, 'conditional_yes' ) );
	}


	/**
	 * Handle localization, WPML compatible
	 *
	 * @since 3.0.0
	 * @see SV_WC_Plugin::load_translation()
	 */
	public function load_translation() {

		load_plugin_textdomain( 'woocommerce-gateway-paypal-express', false, dirname( plugin_basename( $this->get_file() ) ) . '/i18n/languages' );
	}


	/**
	 * Loads any required files
	 *
	 * @since 3.0.0
	 */
	public function includes() {

		// gateway class
		require_once( $this->get_plugin_path() . '/includes/class-wc-gateway-paypal-express.php' );
	}


	/** Frontend methods ******************************************************/


	/**
	 * Helper to add the 'Express Checkout' button to the cart page, as payment
	 * gateways are not typically instantiated on non-checkout pages
	 *
	 * @since 3.0.0
	 */
	public function render_express_checkout_button() {

		$gateway = $this->get_gateway( self::GATEWAY_ID );

		$gateway->render_express_checkout_button();
	}


	/**
	 * Helper to hide the standard WC checkout button, on both the cart page
	 * and mini-cart. Note this needs to run on every page load, as the mini-cart
	 * is cached and it's associated action won't always be called
	 *
	 * @since 3.0.0
	 */
	public function maybe_hide_standard_checkout_button() {

		$gateway = $this->get_gateway( self::GATEWAY_ID );

		$gateway->maybe_hide_standard_checkout_button();
	}


	/**
	 * Helper to cancel express checkout on the cart page, as payment
	 * gateways are not typically instantiated on non-checkout pages
	 *
	 * @since 3.0.0
	 */
	public function cancel_express_checkout() {

		if ( is_cart() && ! empty( $_GET['wc_paypal_express_clear_session'] ) ) {

			$gateway = $this->get_gateway( self::GATEWAY_ID );
			$gateway->clear_session_data();
			wc_add_notice( __( 'You have cancelled express checkout. Please try to process your order again.', 'ultimatewoo-pro' ), 'notice' );
		}
	}


	/**
	 * Helper to maybe redirect to PayPal on checkout when the admin has opted
	 * to include PayPal Express as a payment method on the checkout page.
	 *
	 * @since 3.1.2
	 */
	public function maybe_redirect() {

		if ( isset( $_POST['payment_method'] ) && 'paypal_express' === $_POST['payment_method'] && SV_WC_Helper::wc_notice_count( 'error' ) == 0 ) {

			$gateway = $this->get_gateway( self::GATEWAY_ID );

			$gateway->maybe_redirect_after_checkout();
		}
	}


	/**
	 * Conditionally return 'yes' if currently doing express checkout.
	 *
	 * This method is used to alter the generate_username &
	 * generate_password option values
	 *
	 * @since 3.2.4
	 * @param string $value
	 * @return string
	 */
	public function conditional_yes( $value ) {

		return isset( WC()->session->paypal_express_checkout ) ? 'yes' : $value;
	}


	/** Helper methods ******************************************************/


	/**
	 * Main PayPal Express Instance, ensures only one instance is/can be loaded
	 *
	 * @since 3.3.0
	 * @see wc_paypal_express()
	 * @return WC_PayPal_Express
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Gets the plugin documentation url
	 *
	 * @since 3.0.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string documentation URL
	 */
	public function get_documentation_url() {
		return 'http://docs.woothemes.com/document/paypal-express-checkout/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 3.5.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'http://support.woothemes.com/';
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 3.0.0
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce PayPal Express Gateway', 'ultimatewoo-pro' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 3.0.0
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Install default settings
	 *
	 * @since 3.0.0
	 */
	protected function install() {

		// versions prior to 3.0.0 did not set a version option, so the upgrade method needs to be called manually
		if ( ! get_option( 'wc_paypal_express_version' ) && get_option( 'woocommerce_paypal_express_settings' ) ) {

			$this->upgrade( '2.3.4' );
		}
	}


	/**
	 * Perform any version-related changes.
	 *
	 * @since 3.0.0
	 * @param int $installed_version the currently installed version of the plugin
	 */
	protected function upgrade( $installed_version ) {

		// upgrade to 3.0.0
		if ( version_compare( $installed_version, '3.0.0', '<' ) ) {

			$old_settings = get_option( 'woocommerce_paypal_express_settings' );

			$new_settings = array();

			// migrate from old settings
			$new_settings['enabled']                       = isset( $old_settings['enabled'] ) ? $old_settings['enabled'] : 'no';
			$new_settings['title']                         = isset( $old_settings['title'] ) ? $old_settings['title'] : 'PayPal Express';
			$new_settings['description']                   = isset( $old_settings['description'] ) ? $old_settings['description'] : "Pay via PayPal; you can pay with your credit card if you don't have a PayPal account";
			$new_settings['transaction_type']              = 'charge';
			$new_settings['environment']                   = isset( $old_settings['testmode'] ) && 'yes' == $old_settings['testmode'] ? 'test' : 'production';
			$new_settings['api_username']                  = isset( $old_settings['api_username'] ) ? $old_settings['api_username'] : '';
			$new_settings['api_password']                  = isset( $old_settings['api_password'] ) ? $old_settings['api_password'] : '';
			$new_settings['api_signature']                 = isset( $old_settings['api_signature'] ) ? $old_settings['api_signature'] : '';
			$new_settings['sandbox_api_username']          = 'test' == $new_settings['environment'] && isset( $old_settings['api_username'] ) ? $old_settings['api_username'] : '';
			$new_settings['sandbox_api_password']          = 'test' == $new_settings['environment'] && isset( $old_settings['api_password'] ) ? $old_settings['api_password'] : '';
			$new_settings['sandbox_api_signature']         = 'test' == $new_settings['environment'] && isset( $old_settings['api_signature'] ) ? $old_settings['api_signature'] : '';
			$new_settings['invoice_prefix']                = 'WC-';
			$new_settings['debug_mode']                    = isset( $old_settings['debug'] ) && 'yes' == $old_settings['debug'] ? 'log' : 'off';
			$new_settings['brand_name']                    = '';
			$new_settings['template']                      = isset( $old_settings['template'] ) ? $old_settings['template'] : '';
			$new_settings['checkout_button_style']         = isset( $old_settings['checkout_with_pp_button'] ) && 'yes' == $old_settings['checkout_with_pp_button'] ? 'image' : 'button';
			$new_settings['hide_standard_checkout_button'] = isset( $old_settings['hide_checkout_button'] ) && 'yes' == $old_settings['hide_checkout_button'] ? 'yes' : 'no';
			$new_settings['show_on_checkout']              = isset( $old_settings['show_on_checkout'] ) && 'yes' == $old_settings['show_on_checkout'] ? 'yes' : 'no';
			$new_settings['paypal_account_optional']       = isset( $old_settings['paypal_account_optional'] ) && 'yes' == $old_settings['paypal_account_optional'] ? 'yes' : 'no';
			$new_settings['enable_bill_me_later']          = isset( $old_settings['show_bill_me_later'] ) && 'yes' == $old_settings['show_bill_me_later'] ? 'yes' : 'no';
			$new_settings['landing_page']                  = isset( $old_settings['landing_page'] ) ? $old_settings['landing_page'] : 'login';

			// update to new settings
			update_option( 'woocommerce_paypal_express_settings', $new_settings );

			// trash unused page
			wp_trash_post( wc_get_page_id( 'review_order' ) );
		}
	}


} // end WC_PayPal_Express


/**
 * Returns the One True Instance of PayPal Express
 *
 * @since 3.3.0
 * @return WC_PayPal_Express
 */
function wc_paypal_express() {
	return WC_PayPal_Express::instance();
}

// fire it up!
wc_paypal_express();

} // init_woocommerce_gateway_paypal_express()

//3.7.2