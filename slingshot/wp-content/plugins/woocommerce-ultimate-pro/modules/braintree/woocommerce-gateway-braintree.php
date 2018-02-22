<?php
/**
 * Copyright: (c) 2011-2016 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Gateway-Braintree
 * @author    SkyVerge
 * @category  Gateway
 * @copyright Copyright (c) 2012-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library class
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once SV_WC_FRAMEWORK_FILE;
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.4.1', __( 'WooCommerce Braintree Gateway', 'ultimatewoo-pro' ), __FILE__, 'init_woocommerce_gateway_braintree', array(
	'is_payment_gateway'   => true,
	'minimum_wc_version'   => '2.4.13',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4.0',
) );

function init_woocommerce_gateway_braintree() {

/**
 * # WooCommerce Gateway Braintree Main Plugin Class
 *
 * ## Plugin Overview
 *
 * This plugin adds Braintree as a payment gateway. Braintree's javascript library is used to encrypt the credit card
 * fields prior to form submission, so it acts like a direct gateway but without the burden of heavy PCI compliance. Logged
 * in customers' credit cards are saved to the braintree vault by default. Subscriptions and Pre-Orders are supported via
 * the Add-Ons class.
 *
 * ## Admin Considerations
 *
 * A user view/edit field is added for the Braintree customer ID so it can easily be changed by the admin.
 *
 * ## Frontend Considerations
 *
 * Both the payment fields on checkout (and checkout->pay) and the My cards section on the My Account page are template
 * files for easy customization.
 *
 * ## Database
 *
 * ### Global Settings
 *
 * + `woocommerce_braintree_settings` - the serialized braintree settings array
 *
 * ### Options table
 *
 * + `wc_braintree_version` - the current plugin version, set on install/upgrade
 *
 * ### Order Meta
 *
 * + `_wc_braintree_trans_id` - the braintree transaction ID
 * + `_wc_braintree_trans_mode` - the environment the braintree transaction was created in
 * + `_wc_braintree_card_type` - the card type used for the order
 * + `_wc_braintree_card_last_four` - the last four digits of the card used for the order
 * + `_wc_braintree_card_exp_date` - the expiration date of the card used for the order
 * + `_wc_braintree_customer_id` - the braintree customer ID for the order, set only if the customer is logged in/creating an account
 * + `_wc_braintree_cc_token` - the braintree token for the credit card used for the order, set only if the customer is logged in/creating an account
 *
 * ### User Meta
 * + `_wc_braintree_customer_id` - the braintree customer ID for the user
 *
 */
class WC_Braintree extends SV_WC_Payment_Gateway_Plugin {


	/** plugin version number */
	const VERSION = '3.3.2';

	/** @var WC_Braintree single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'braintree';

	/** plugin text domain, DEPRECATED as of 3.1.0 */
	const TEXT_DOMAIN = 'woocommerce-gateway-braintree';

	/** credit card gateway class name */
	const CREDIT_CARD_GATEWAY_CLASS_NAME = 'WC_Gateway_Braintree_Credit_Card';

	/** credit card gateway ID */
	const CREDIT_CARD_GATEWAY_ID = 'braintree_credit_card';

	/** PayPal gateway class name */
	const PAYPAL_GATEWAY_CLASS_NAME = 'WC_Gateway_Braintree_PayPal';

	/** PayPal gateway ID */
	const PAYPAL_GATEWAY_ID = 'braintree_paypal';


	/**
	 * Initializes the plugin
	 *
	 * @since 2.0
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'gateways' => array(
					self::CREDIT_CARD_GATEWAY_ID => self::CREDIT_CARD_GATEWAY_CLASS_NAME,
					self::PAYPAL_GATEWAY_ID      => self::PAYPAL_GATEWAY_CLASS_NAME,
				),
				'require_ssl' => false,
				'supports' => array(
					self::FEATURE_CAPTURE_CHARGE,
					self::FEATURE_MY_PAYMENT_METHODS,
					self::FEATURE_CUSTOMER_ID,
				),
				'dependencies' => array( 'curl', 'dom', 'hash', 'openssl', 'SimpleXML', 'xmlwriter' ),
			)
		);

		// include required files
		add_action( 'sv_wc_framework_plugins_loaded', array( $this, 'includes' ) );
	}


	/**
	 * Include required files
	 *
	 * @since 2.0
	 */
	public function includes() {

		// gateways
		require_once( $this->get_plugin_path() . '/includes/class-wc-gateway-braintree.php' );
		require_once( $this->get_plugin_path() . '/includes/class-wc-gateway-braintree-credit-card.php' );
		require_once( $this->get_plugin_path() . '/includes/class-wc-gateway-braintree-paypal.php' );

		// payment method
		require_once( $this->get_plugin_path() . '/includes/class-wc-braintree-payment-method-handler.php' );
		require_once( $this->get_plugin_path() . '/includes/class-wc-braintree-payment-method.php' );

		// payment forms
		require_once( $this->get_plugin_path() . '/includes/payment-forms/abstract-wc-braintree-payment-form.php' );
		require_once( $this->get_plugin_path() . '/includes/payment-forms/class-wc-braintree-hosted-fields-payment-form.php' );
		require_once( $this->get_plugin_path() . '/includes/payment-forms/class-wc-braintree-paypal-payment-form.php' );
	}


	/**
	 * Handle localization, WPML compatible
	 *
	 * @since 2.0
	 * @see SV_WC_Plugin::load_translation()
	 */
	public function load_translation() {

		load_plugin_textdomain( 'woocommerce-gateway-braintree', false, dirname( plugin_basename( $this->get_file() ) ) . '/i18n/languages' );
	}


	/** Admin methods ******************************************************/

	/**
	 * Render a notice for the user to select their desired export format
	 *
	 * @since 2.1.3
	 * @see SV_WC_Plugin::add_admin_notices()
	 */
	public function add_admin_notices() {

		// show any dependency notices
		parent::add_admin_notices();

		$credit_card_gateway = $this->get_gateway( self::CREDIT_CARD_GATEWAY_ID );

		if ( $credit_card_gateway->is_advanced_fraud_tool_enabled() && ! $this->get_admin_notice_handler()->is_notice_dismissed( 'fraud-tool-notice' ) ) {

			$this->get_admin_notice_handler()->add_admin_notice(
				sprintf( __( 'Heads up! You\'ve enabled advanced fraud tools for Braintree. Please make sure that advanced fraud tools are also enabled in your Braintree account. Need help? See the %1$sdocumentation%2$s.', 'ultimatewoo-pro' ),
					'<a target="_blank" href="' . $this->get_documentation_url() . '">',
					'</a>'
				), 'fraud-tool-notice', array( 'always_show_on_settings' => false, 'dismissible' => true, 'notice_class' => 'updated' )
			);
		}

		$credit_card_settings = get_option( 'woocommerce_braintree_credit_card_settings' );
		$paypal_settings      = get_option( 'woocommerce_braintree_paypal_settings' );

		// install notice
		if ( empty( $credit_card_settings ) && empty( $paypal_settings ) && ! $this->get_admin_notice_handler()->is_notice_dismissed( 'install-notice' ) ) {

			$this->get_admin_notice_handler()->add_admin_notice(
				sprintf( __( 'Thanks for installing the WooCommerce Braintree plugin! To start accepting payments, %sset your Braintree API credentials%s. Need help? See the %sdocumentation%s. ', 'ultimatewoo-pro' ),
					'<a href="' . $this->get_settings_url( self::CREDIT_CARD_GATEWAY_ID ) . '">', '</a>',
					'<a target="_blank" href="' . $this->get_documentation_url() . '">', '</a>'
				), 'install-notice', array( 'notice_class' => 'updated' )
			);
		}

		// SSL check (only when PayPal is enabled in production mode)
		if ( isset( $paypal_settings['enabled'] ) && 'yes' == $paypal_settings['enabled'] ) {
			if ( isset( $paypal_settings['environment'] ) && 'production' == $paypal_settings['environment'] ) {

				if ( 'no' === get_option( 'woocommerce_force_ssl_checkout' ) && ! $this->get_admin_notice_handler()->is_notice_dismissed( 'ssl-recommended-notice' ) ) {

					$this->get_admin_notice_handler()->add_admin_notice( __( 'WooCommerce is not being forced over SSL -- Using PayPal with Braintree requires that checkout to be forced over SSL.', 'ultimatewoo-pro' ), 'ssl-recommended-notice' );
				}
			}
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Main Braintree Instance, ensures only one instance is/can be loaded
	 *
	 * @since 2.2.0
	 * @see wc_braintree()
	 * @return WC_Braintree
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 2.1
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce Braintree Gateway', 'ultimatewoo-pro' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 2.1
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


	/**
	 * Gets the plugin documentation url
	 *
	 * @since 2.1
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string documentation URL
	 */
	public function get_documentation_url() {
		return 'http://docs.woothemes.com/document/braintree/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 2.3.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'http://support.woothemes.com/';
	}


	/**
	 * Returns the "Configure Credit Card" or "Configure PayPal" plugin action
	 * links that go directly to the gateway settings page
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_Plugin::get_settings_url()
	 * @param string $gateway_id the gateway identifier
	 * @return string plugin configure link
	 */
	public function get_settings_link( $gateway_id = null ) {

		return sprintf( '<a href="%s">%s</a>',
			$this->get_settings_url( $gateway_id ),
			self::CREDIT_CARD_GATEWAY_ID === $gateway_id ? __( 'Configure Credit Card', 'ultimatewoo-pro' ) : __( 'Configure PayPal', 'ultimatewoo-pro' )
		);
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Perform any version-related changes.
	 *
	 * @since 2.0
	 * @param int $installed_version the currently installed version of the plugin
	 */
	protected function upgrade( $installed_version ) {

		// pre-2.0 upgrade
		if ( version_compare( $installed_version, '2.0', '<' ) ) {
			global $wpdb;

			// update from pre-2.0 Braintree version
			if ( $settings = get_option( 'woocommerce_braintree_settings' ) ) {

				// migrate from old settings
				$settings['cvv_required'] = $settings['cvvrequired'];
				$settings['merchant_id']  = $settings['merchantid'];
				$settings['public_key']   = $settings['publickey'];
				$settings['private_key']  = $settings['privatekey'];
				$settings['debug_mode']   = 'off';

				// remove unused settings
				foreach ( array( 'cvvrequired', 'vault', 'vaulttext', 'managecards', 'merchantid', 'publickey', 'privatekey' ) as $key ) {

					if ( isset( $settings[ $key ] ) )
						unset( $settings[ $key ] );
				}

				// update to new settings
				update_option( 'woocommerce_braintree_settings', $settings );

				// update user meta keys
				$wpdb->update( $wpdb->usermeta, array( 'meta_key' => '_wc_braintree_customer_id' ), array( 'meta_key' => 'woocommerce_braintree_customerid' ) );

				// update post meta keys
				$wpdb->update( $wpdb->postmeta, array( 'meta_key' => '_wc_braintree_cc_token' ), array( 'meta_key' => '_braintree_token' ) );

				// remove unused tokens
				$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => 'woocommerce_braintree_cc' ) );
			}

			// update from Braintree TR extension
			if ( $settings = get_option( 'woocommerce_braintree_tr_settings' ) ) {

				/* migrate from old settings */

				// debug mode
				if ( 'yes' == $settings['debug'] && 'yes' == $settings['log'] )
					$settings['debug_mode'] = 'both';
				elseif ( 'yes' == $settings['debug'] )
					$settings['debug_mode'] = 'checkout';
				elseif ( 'yes' == $settings['log'] )
					$settings['debug_mode'] = 'log';
				else
					$settings['debug_mode'] = 'off';

				// other settings
				$settings['card_types']  = $settings['cardtypes'];
				$settings['merchant_id'] = $settings['merchantid'];
				$settings['public_key']  = $settings['publickey'];
				$settings['private_key'] = $settings['privatekey'];

				// remove unused settings
				foreach ( array( 'debug', 'log', 'custom_order_numbers', 'vault', 'vaulttext', 'managecards', 'cardtypes', 'merchantid', 'publickey', 'privatekey' ) as $key ) {

					if ( isset( $settings[ $key ] ) )
						unset( $settings[ $key ] );
				}

				// update to new settings
				update_option( 'woocommerce_braintree_settings', $settings );

				// update user meta keys
				$wpdb->update( $wpdb->usermeta, array( 'meta_key' => '_wc_braintree_customer_id' ), array( 'meta_key' => 'woocommerce_braintree_customerid' ) );

				// update post meta keys
				$wpdb->update( $wpdb->postmeta, array( 'meta_key' => '_wc_braintree_customer_id' ),    array( 'meta_key' => '_braintree_customerid' ) );
				$wpdb->update( $wpdb->postmeta, array( 'meta_key' => '_wc_braintree_cc_token' ),       array( 'meta_key' => '_braintree_token' ) );
				$wpdb->update( $wpdb->postmeta, array( 'meta_key' => '_wc_braintree_trans_env' ),      array( 'meta_key' => '_braintree_transaction_environment' ) );
				$wpdb->update( $wpdb->postmeta, array( 'meta_key' => '_wc_braintree_card_exp_date' ),  array( 'meta_key' => '_braintree_cc_expiration' ) );
				$wpdb->update( $wpdb->postmeta, array( 'meta_key' => '_wc_braintree_card_type' ),      array( 'meta_key' => '_braintree_cc_card_type' ) );
				$wpdb->update( $wpdb->postmeta, array( 'meta_key' => '_wc_braintree_card_last_four' ), array( 'meta_key' => '_braintree_cc_last4' ) );
				$wpdb->update( $wpdb->postmeta, array( 'meta_key' => '_wc_braintree_trans_id' ),       array( 'meta_key' => '_braintree_transaction_id' ) );

				// remove unused tokens
				$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => 'woocommerce_braintree_cc' ) );

				// disable plugin by removing settings
				delete_option( 'woocommerce_braintree_tr_settings' );
			}
		}

		// upgrade to 3.0.0
		if ( version_compare( $installed_version, '3.0.0', '<' ) ) {

			$this->log( 'Starting upgrade to 3.0.0' );

			/** Upgrade settings */

			$old_settings = get_option( 'woocommerce_braintree_settings' );

			if ( $old_settings ) {

				// prior to 3.0.0, there was no settings for tokenization (always on) and enable_customer_decline_messages.

				// credit card
				$new_cc_settings = array(
						'enabled'                          => ( isset( $old_settings['enabled'] ) && 'yes' === $old_settings['enabled'] ) ? 'yes' : 'no',
						'title'                            => ( ! empty( $old_settings['title'] ) ) ? $old_settings['title'] : 'Credit Card',
						'description'                      => ( ! empty( $old_settings['description'] ) ) ? $old_settings['description'] : 'Pay securely using your credit card.',
						'require_csc'                      => ( isset( $old_settings['require_cvv'] ) && 'yes' === $old_settings['require_cvv'] ) ? 'yes' : 'no',
						'transaction_type'                 => ( isset( $old_settings['settlement'] ) && 'yes' === $old_settings['settlement'] ) ? 'charge' : 'authorization',
						'card_types'                       => ( ! empty( $old_settings['card_types'] ) ) ? $old_settings['card_types'] : array( 'VISA', 'MC', 'AMEX', 'DISC' ),
						'tokenization'                     => 'yes',
						'environment'                      => ( isset( $old_settings['environment'] ) && 'production' === $old_settings['environment'] ) ? 'production' : 'sandbox',
						'inherit_settings'                 => 'no',
						'public_key'                       => ( ! empty( $old_settings['public_key'] ) ) ? $old_settings['public_key'] : '',
						'private_key'                      => ( ! empty( $old_settings['private_key'] ) ) ? $old_settings['private_key'] : '',
						'merchant_id'                      => ( ! empty( $old_settings['merchant_id'] ) ) ? $old_settings['merchant_id'] : '',
						'sandbox_public_key'               => '',
						'sandbox_private_key'              => '',
						'sandbox_merchant_id'              => '',
						'name_dynamic_descriptor'          => '',
						'phone_dynamic_descriptor'         => '',
						'url_dynamic_descriptor'           => '',
						'fraud_tool'                       => 'basic',
						'threed_secure_enabled'            => 'no',
						'enable_customer_decline_messages' => 'no',
						'debug_mode'                       => ( ! empty( $old_settings['debug_mode'] ) ) ? $old_settings['debug_mode'] : 'off',
				);

				// no PayPal settings to migrate since it's a new gateway

				// migrate merchant account ID
				if ( ! empty( $old_settings['merchant_account_id'] ) ) {

					$currency = strtolower( get_woocommerce_currency() );

					// assume the merchant account ID set is for the active store currency
					$new_cc_settings[ "merchant_account_id_{$currency}" ] = $old_settings['merchant_account_id'];
				}

				// save new settings, remove old ones
				update_option( 'woocommerce_braintree_credit_card_settings', $new_cc_settings );
				delete_option( 'woocommerce_braintree_settings' );

				$this->log( 'Settings upgraded' );
			}


			/** Update user meta keys for customer ID */

			global $wpdb;

			// old key: _wc_braintree_customer_id
			// new key: wc_braintree_customer_id
			// note that we don't know on a per-user basis what environment the customer ID was set in, so we assume production, just to be safe
			$rows = $wpdb->update( $wpdb->usermeta, array( 'meta_key' => 'wc_braintree_customer_id' ), array( 'meta_key' => '_wc_braintree_customer_id' ) );

			$this->log( sprintf( '%d users updated for customer ID.', $rows ) );


			/** Update order meta keys for customer ID and CC token  */

			// old key: _wc_braintree_customer_id
			// new key: _wc_braintree_credit_card_customer_id
			$rows = $wpdb->update( $wpdb->postmeta, array( 'meta_key' => '_wc_braintree_credit_card_customer_id' ), array( 'meta_key' => '_wc_braintree_customer_id' ) );

			$this->log( sprintf( '%d orders updated for customer ID meta', $rows ) );

			// old key: _wc_braintree_cc_token
			// new key: _wc_braintree_credit_card_payment_token
			$rows = $wpdb->update( $wpdb->postmeta, array( 'meta_key' => '_wc_braintree_credit_card_payment_token' ), array( 'meta_key' => '_wc_braintree_cc_token' ) );

			$this->log( sprintf( '%d orders updated for payment token meta', $rows ) );


			/** Update order meta values for order payment method & recurring payment method */

			// meta key: _payment_method
			// old value: braintree
			// new value: braintree_credit_card
			$rows = $wpdb->update( $wpdb->postmeta, array( 'meta_value' => 'braintree_credit_card' ), array( 'meta_key' => '_payment_method', 'meta_value' => 'braintree' ) );

			$this->log( sprintf( '%d orders updated for payment method meta', $rows ) );

			// meta key: _recurring_payment_method
			// old value: braintree
			// new value: braintree_credit_Card
			$rows = $wpdb->update( $wpdb->postmeta, array( 'meta_value' => 'braintree_credit_card' ), array( 'meta_key' => '_recurring_payment_method', 'meta_value' => 'braintree' ) );

			$this->log( sprintf( '%d orders updated for recurring payment method meta', $rows ) );


			$this->log( 'Completed upgrade for 3.0.0' );
		}
	}


} // end \WC_Braintree


/**
 * Returns the One True Instance of Braintree
 *
 * @since 2.2.0
 * @return WC_Braintree
 */
function wc_braintree() {
	return WC_Braintree::instance();
}

// fire it up!
wc_braintree();

} // init_woocommerce_gateway_braintree()

//3.3.2