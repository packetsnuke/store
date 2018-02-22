<?php
/**
 * Copyright: (c) 2013-2017, SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Intuit-Payments
 * @author    SkyVerge
 * @category  Payment-Gateways
 * @copyright Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library classss
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once SV_WC_FRAMEWORK_FILE;
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.6.4', __( 'WooCommerce Intuit Payments Gateway', 'ultimatewoo-pro' ), __FILE__, 'init_woocommerce_gateway_intuit_payments', array(
	'is_payment_gateway'   => true,
	'minimum_wc_version'   => '2.5.5',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4',
) );

function init_woocommerce_gateway_intuit_payments() {

/**
 * The main class for the Intuit Payments Gateway.  This class handles all the
 * non-gateway tasks such as verifying dependencies are met, loading the text
 * domain, etc.
 *
 * This plugin contains two distinct "integrations," each with their own set of
 * gateways. The primary integration is Payments, and integrations with the
 * latest QuickBooks Payments API. The second is the legacy QBMS API that was
 * used before v2.0.0.
 *
 * @since 2.0.0
 */
class WC_Intuit_Payments extends SV_WC_Payment_Gateway_Plugin {


	/** string the plugin version number */
	const VERSION = '2.1.0';

	/** string the plugin id */
	const PLUGIN_ID = 'intuit_payments';

	/** string the credit card gateway class name */
	const CREDIT_CARD_CLASS_NAME = 'WC_Gateway_Inuit_Payments_Credit_Card';

	/** string the credit card gateway ID */
	const CREDIT_CARD_ID = 'intuit_payments_credit_card';

	/** string the eCheck gateway class name */
	const ECHECK_CLASS_NAME = 'WC_Gateway_Inuit_Payments_eCheck';

	/** string the eCheck gateway ID */
	const ECHECK_ID = 'intuit_payments_echeck';

	/** @var \WC_Intuit_Payments_AJAX the Payments AJAX instance */
	protected $payments_ajax_instance;

	/** The legacy QBMS gateways **********************************************/

	/** string the ID for the QBMS group of gateways */
	const QBMS_PLUGIN_ID = 'intuit_qbms';

	/** string the QBMS credit card gateway class name */
	const QBMS_CREDIT_CARD_CLASS_NAME = 'WC_Gateway_Intuit_QBMS_Credit_Card';

	/** string the QBMS credit card gateway ID */
	const QBMS_CREDIT_CARD_ID = 'intuit_qbms_credit_card';

	/** string the QBMS eCheck gateway class name */
	const QBMS_ECHECK_CLASS_NAME = 'WC_Gateway_Intuit_QBMS_eCheck';

	/** string the QBMS eCheck gateway ID */
	const QBMS_ECHECK_ID = 'intuit_qbms_echeck';

	/** @var \WC_Intuit_Payments single instance of this plugin */
	protected static $instance;


	/**
	 * Sets up the main plugin class.
	 *
	 * @since 2.0.0
	 * @see SV_WC_Plugin::__construct()
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'gateways'     => $this->get_active_gateways(),
				'require_ssl'  => true,
				'supports'     => array(
					self::FEATURE_CUSTOMER_ID,
					self::FEATURE_CAPTURE_CHARGE,
					self::FEATURE_MY_PAYMENT_METHODS,
				),
				'dependencies'       => $this->get_active_integration_dependencies(),
				'display_php_notice' => true,
			)
		);

		// include required files
		add_action( 'sv_wc_framework_plugins_loaded', array( $this, 'includes' ) );

		// handle switching between the active integrations
		add_action( 'admin_action_wc_intuit_payments_change_integration', array( $this, 'change_integration' ) );
	}


	/**
	 * Loads any required files.
	 *
	 * @since 1.0
	 */
	public function includes() {

		$plugin_path = $this->get_plugin_path();

		// QBMS classes
		if ( $this->is_qbms_active() ) {

			require_once( $plugin_path . '/includes/qbms/class-wc-gateway-intuit-qbms.php' );
			require_once( $plugin_path . '/includes/qbms/class-wc-gateway-intuit-qbms-credit-card.php' );
			// require_once( $plugin_path . '/includes/qbms/class-wc-gateway-intuit-qbms-echeck.php' );  // commented out until/if QBMS really supports echecks
			require_once( $plugin_path . '/includes/qbms/class-wc-intuit-qbms-payment-token-handler.php' );
			require_once( $plugin_path . '/includes/qbms/class-wc-intuit-qbms-payment-token.php' );

			if ( is_admin() ) {
				require_once( $plugin_path . '/includes/qbms/class-wc-intuit-qbms-payment-token-editor.php' );
			}

		} else {

			require_once( $plugin_path . '/includes/abstract-wc-gateway-intuit-payments.php' );
			require_once( $plugin_path . '/includes/class-wc-gateway-intuit-payments-credit-card.php' );
			require_once( $plugin_path . '/includes/class-wc-gateway-intuit-payments-echeck.php' );
			require_once( $plugin_path . '/includes/api/class-wc-intuit-payments-api-oauth-helper.php' );

			$this->payments_ajax_instance = $this->load_class( '/includes/class-wc-intuit-payments-ajax.php', 'WC_Intuit_Payments_AJAX' );
		}
	}


	/**
	 * Gets the deprecated/removed hooks.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_deprecated_hooks() {

		$hooks = array(
			'wc_gateway_intuit_qbms_manage_my_payment_methods' => array(
				'version'     => '2.0.0',
				'removed'     => true,
				'replacement' => 'wc_' . $this->get_id() . '_my_payment_methods_table_title',
				'map'         => true,
			),
			'wc_gateway_intuit_qbms_tokenize_payment_method_text' => array(
				'version'     => '2.0.0',
				'removed'     => true,
				'replacement' => 'wc_' . self::QBMS_CREDIT_CARD_ID . '_tokenize_payment_method_text',
				'map'         => true,
			),
		);

		return $hooks;
	}


	/** Integration switching methods *********************************************/


	/**
	 * Gets the required dependencies for the active integration.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_active_integration_dependencies() {

		$dependencies = array(
			'extensions' => array(),
			'functions'  => array(),
			'settings'   => array(),
		);

		if ( $this->is_qbms_active() ) {

			$dependencies['extensions'] = array(
				'SimpleXML',
				'xmlwriter',
				'dom',
				'iconv',
			);

		} else {

			$dependencies['extensions'] = array(
				'mcrypt',
				'json',
			);
		}

		return $dependencies;
	}


	/**
	 * Gets the active gateways based on the currently activated integration.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_active_gateways() {

		$available_gateways = $this->get_available_gateways();
		$active_integration = $this->get_active_integration();

		return ! empty( $available_gateways[ $active_integration ] ) ? $available_gateways[ $active_integration ] : array();
	}


	/**
	 * Gets the gateways available for activation.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_available_gateways() {

		$gateways = array(
			self::PLUGIN_ID => array(
				self::CREDIT_CARD_ID => self::CREDIT_CARD_CLASS_NAME,
				self::ECHECK_ID      => self::ECHECK_CLASS_NAME,
			),
			self::QBMS_PLUGIN_ID => array(
				self::QBMS_CREDIT_CARD_ID => self::QBMS_CREDIT_CARD_CLASS_NAME,
				// self::QBMS_ECHECK_ID      => self::QBMS_ECHECK_CLASS_NAME,
			),
		);

		return $gateways;
	}


	/**
	 * Gets the active integration ID.
	 *
	 * This is considered the active "set" of gateways, either the Legacy QBMS or
	 * Payments API Credit Card & eCheck gateways.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_active_integration() {

		return get_option( 'wc_intuit_payments_active_integration', self::PLUGIN_ID );
	}


	/**
	 * Determines if the legacy QBMS gateway is active.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_qbms_active() {

		return self::QBMS_PLUGIN_ID === $this->get_active_integration();
	}


	/**
	 * Gets the plugin action links.
	 *
	 * @since 2.0.0
	 * @param array $actions associative array of action names to anchor tags
	 * @return array associative array of plugin action links
	 */
	public function plugin_action_links( $actions ) {

		$actions = parent::plugin_action_links( $actions );

		$gateway_actions = array();

		$available_gateways = $this->get_available_gateways();

		$insert_after = 'configure';

		// use <gateway> links
		foreach ( $available_gateways as $integration => $gateways ) {

			if ( $integration === $this->get_active_integration() ) {

				end( $gateways );

				$insert_after = 'configure_' . key( $gateways );

				continue;
			}

			$gateway_actions[ "change_integration_{$integration}" ] = $this->get_change_integration_link( $integration );
		}

		return SV_WC_Helper::array_insert_after( $actions, $insert_after, $gateway_actions );
	}


	/**
	 * Gets the link for changing the active integration.
	 *
	 * @since 2.0.0
	 * @param string $integration the integration ID
	 * @return string
	 */
	protected function get_change_integration_link( $integration ) {

		$params = array(
			'action'      => 'wc_intuit_payments_change_integration',
			'integration' => $integration,
		);

		$url = wp_nonce_url( add_query_arg( $params, 'admin.php' ), $this->get_file() );

		if ( self::QBMS_PLUGIN_ID === $integration ) {
			$name = esc_html__( 'Use Legacy QBMS Gateway', 'ultimatewoo-pro' );
		} else {
			$name = esc_html__( 'Use Payments Gateway', 'ultimatewoo-pro' );
		}

		return sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', esc_url( $url ), $name );
	}


	/**
	 * Handles switching between the active integration.
	 *
	 * @since 2.0.0
	 */
	public function change_integration() {

		// security check
		if ( ! wp_verify_nonce( $_GET['_wpnonce'], $this->get_file() ) || ! current_user_can( 'manage_woocommerce' ) ) {
			wp_redirect( wp_get_referer() );
			exit;
		}

		$valid_integrations = array(
			self::PLUGIN_ID,
			self::QBMS_PLUGIN_ID,
		);

		if ( empty( $_GET['integration'] ) || ! in_array( $_GET['integration'], $valid_integrations, true ) ) {
			wp_redirect( wp_get_referer() );
			exit;
		}

		// switch the integration
		update_option( 'wc_intuit_payments_active_integration', $_GET['integration'] );

		$return_url = add_query_arg( array( 'integration_switched' => 1 ), 'plugins.php' );

		// back to whence we came
		wp_redirect( $return_url );
		exit;
	}


	/** Admin methods *********************************************************/


	/**
	 * Adds a notice when gateways are switched.
	 *
	 * @since 2.0.0
	 * @see \SV_WC_Plugin::add_admin_notices()
	 */
	public function add_admin_notices() {

		parent::add_admin_notices();

		if ( isset( $_GET['integration_switched'] ) ) {

			if ( $this->is_qbms_active() ) {
				$message = __( 'Intuit QBMS Gateway is now active.', 'ultimatewoo-pro' );
			} else {
				$message = __( 'Intuit Payments Gateway is now active.', 'ultimatewoo-pro' );
			}

			$this->get_admin_notice_handler()->add_admin_notice( $message, 'integration-switched', array( 'dismissible' => false ) );
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * The one true Intuit Payments instance.
	 *
	 * @since 2.0.0
	 * @see wc_intuit_payments()
	 * @return \WC_Intuit_Payments
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Gets the plugin documentation URL.
	 *
	 * @since 2.0.0
	 * @see \SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {

		return 'http://docs.woothemes.com/document/woocommerce-intuit-qbms/'; // TODO: new docs URL? {CW 2016-11-09}
	}


	/**
	 * Gets the plugin support URL.
	 *
	 * @since 2.0.0
	 * @see \SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {

		return 'http://support.woothemes.com/';
	}


	/**
	 * Gets the plugin name, localized.
	 *
	 * @since 2.0.0
	 * @see \SV_WC_Payment_Gateway::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {

		return __( 'WooCommerce Intuit Payments Gateway', 'ultimatewoo-pro' );
	}


	/**
	 * Gets the "Configure Credit Cards" or "Configure eCheck" plugin action links that go
	 * directly to the gateway settings page.
	 *
	 * @since 2.0.0
	 * @see SV_WC_Payment_Gateway_Plugin::get_settings_url()
	 * @param string $gateway_id the gateway ID
	 * @return string
	 */
	public function get_settings_link( $gateway_id = null ) {

		if ( self::ECHECK_ID === $gateway_id ) {
			$label = __( 'Configure eChecks', 'ultimatewoo-pro' );
		} else if ( self::CREDIT_CARD_ID === $gateway_id ) {
			$label = __( 'Configure Credit Cards', 'ultimatewoo-pro' );
		} else {
			$label = __( 'Configure', 'ultimatewoo-pro' );
		}

		return sprintf( '<a href="%s">%s</a>',
			$this->get_settings_url( $gateway_id ),
			$label
		);
	}


	/**
	 * Gets __FILE__
	 *
	 * @since 2.0.0
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {

		return __FILE__;
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Handles installation tasks.
	 *
	 * @since 2.0.0
	 */
	protected function install() {

		// handle upgrades from pre v2.0.0 versions, as the plugin ID changed then
		// and the upgrade routine won't be triggered automatically
		if ( $old_version = get_option( 'wc_intuit_qbms_version' ) ) {

			$this->upgrade( $old_version );

		} else {

			update_option( 'wc_intuit_payments_active_integration', self::PLUGIN_ID );
		}
	}


	/**
	 * Handles upgrades.
	 *
	 * @since 2.0.0
	 * @param string $installed_version the currently installed version
	 */
	protected function upgrade( $installed_version ) {

		// upgrade to v2.0.0
		if ( version_compare( $installed_version, '2.0.0', '<' ) ) {

			global $wpdb;

			$this->log( 'Starting upgrade to v2.0.0' );

			/** Update order payment method meta ******************************/

			$this->log( 'Starting order meta upgrade.' );

			// meta key: _payment_method
			// old value: intuit_qbms
			// new value: intuit_qbms_credit_card
			$rows = $wpdb->update( $wpdb->postmeta, array( 'meta_value' => 'intuit_qbms_credit_card' ), array( 'meta_key' => '_payment_method', 'meta_value' => 'intuit_qbms' ) );

			$this->log( sprintf( '%d orders updated for payment method meta', $rows ) );

			// meta key: _recurring_payment_method
			// old value: intuit_qbms
			// new value: intuit_qbms_credit_card
			$rows = $wpdb->update( $wpdb->postmeta, array( 'meta_value' => 'intuit_qbms_credit_card' ), array( 'meta_key' => '_recurring_payment_method', 'meta_value' => 'intuit_qbms' ) );

			$this->log( sprintf( '%d orders updated for recurring payment method meta', $rows ) );

			$order_meta_keys = array(
				'trans_id',
				'capture_trans_id',
				'trans_date',
				'txn_authorization_stamp',
				'payment_grouping_code',
				'recon_batch_id',
				'merchant_account_number',
				'client_trans_id',
				'capture_client_trans_id',
				'card_type',
				'card_expiry_date',
				'charge_captured',
				'authorization_code',
				'capture_authorization_code',
				'account_four',
				'payment_token',
				'customer_id',
				'environment',
				'retry_count',
			);

			foreach ( $order_meta_keys as $key ) {

				// old key: _wc_intuit_qbms_*
				// new key: _wc_intuit_qbms_credit_card_*
				$wpdb->update( $wpdb->postmeta, array( 'meta_key' => '_wc_intuit_qbms_credit_card_' . $key ), array( 'meta_key' => '_wc_intuit_qbms_' . $key ) );
			}

			/** Update user token method meta *********************************/

			$this->log( 'Starting legacy token upgrade.' );

			// old key: _wc_intuit_qbms_payment_tokens_test
			// new key: _wc_intuit_qbms_credit_card_payment_tokens_test
			$rows = $wpdb->update( $wpdb->usermeta, array( 'meta_key' => '_wc_intuit_qbms_credit_card_payment_tokens_test' ), array( 'meta_key' => '_wc_intuit_qbms_payment_tokens_test' ) );

			// old key: _wc_intuit_qbms_payment_tokens
			// new key: _wc_intuit_qbms_credit_card_payment_tokens
			$rows = $wpdb->update( $wpdb->usermeta, array( 'meta_key' => '_wc_intuit_qbms_credit_card_payment_tokens' ), array( 'meta_key' => '_wc_intuit_qbms_payment_tokens' ) );

			/** Update the QBMS settings **************************************/

			if ( $settings = get_option( 'woocommerce_intuit_qbms_settings' ) ) {

				$this->log( 'Starting legacy settings upgrade.' );

				// update switcher option
				update_option( 'wc_intuit_payments_active_integration', self::QBMS_PLUGIN_ID );

				// store the settings under the new option name
				update_option( 'woocommerce_intuit_qbms_credit_card_settings', $settings );

				// remove the old option
				delete_option( 'woocommerce_intuit_qbms_settings' );
			}

			delete_option( 'wc_intuit_qbms_version' );

			$this->log( 'Completed upgrade for v2.0.0' );
		}

		// upgrade to v2.1.0
		if ( version_compare( $installed_version, '2.1.0', '<' ) ) {

			$this->log( 'Starting upgrade to v2.1.0' );

			update_option( 'wc_' . self::PLUGIN_ID . '_oauth_version', '1.0' );

			$this->log( 'Completed upgrade for v2.1.0' );
		}
	}


}


/**
 * Gets the one true instance of Intuit Payments.
 *
 * @since 3.6.0
 * @deprecated 2.0.0 due to the plugin being renamed
 * @return \WC_Intuit_Payments
 */
function wc_intuit_qbms() {

	_deprecated_function( __FUNCTION__, '2.0.0', 'wc_intuit_payments()' );

	return wc_intuit_payments();
}

/**
 * Gets the one true instance of Intuit Payments.
 *
 * @since 2.0.0
 * @return \WC_Intuit_Payments
 */
function wc_intuit_payments() {
	return WC_Intuit_Payments::instance();
}

// fire it up!
wc_intuit_payments();

}

//2.1.0