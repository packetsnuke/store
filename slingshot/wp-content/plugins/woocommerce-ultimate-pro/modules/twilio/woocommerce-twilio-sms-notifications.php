<?php
/**
 * @package   WC-Twilio-SMS-Notifications
 * @author    SkyVerge
 * @category  Integration
 * @copyright Copyright (c) 2013-2017, SkyVerge, Inc.
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

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.6.0', __( 'WooCommerce Twilio SMS Notifications', 'ultimatewoo-pro' ), __FILE__, 'init_woocommerce_twilio_sms_notifications', array(
	'minimum_wc_version'   => '2.5.5',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4',
) );

function init_woocommerce_twilio_sms_notifications() {

/**
 * # WooCommerce Twilio SMS Notifications Plugin
 *
 * ## Plugin Overview
 *
 * This plugin sends SMS order notifications to admins and customers by hooking into WooCommerce order status changes.
 * Admins can customize the message templates, as well as what order status changes should trigger SMS sends.
 *
 * ## Admin Considerations
 *
 * + 'SMS' tab added to WooCommerce > Settings
 *
 * ## Frontend Considerations
 *
 * + Opt-in checkbox added to checkout page which determines whether SMS order updates will be sent to the customer. The
 * admin can override this on a global basis.
 *
 * ## Database
 *
 * ### Global Settings
 *
 * + `wc_twilio_sms_checkout_optin_checkbox_label` - the label for the optin checkbox on the checkout page
 *
 * + `wc_twilio_sms_checkout_optin_checkbox_default` - the default status for the optin checkbox, either checked or unchecked
 *
 * + `wc_twilio_sms_shorten_urls` - true to shorten URLs inside SMS messages with goo.gl, false otherwise
 *
 * + `wc_twilio_sms_enable_admin_sms` - true to send SMS admin new order notifications, false otherwise
 *
 * + `wc_twilio_sms_admin_sms_recipients` - mobile phone numbers to send SMS admin new order notifications to
 *
 * + `wc_twilio_sms_admin_sms_template` the message template to use for SMS admin new order notifications
 *
 * + `wc_twilio_sms_send_sms_order_statuses` - an array of order statuses to send an SMS update on
 *
 * + `wc_twilio_sms_default_sms_template` - the message template to use for SMS order notifications if an order status-specific template does not exist
 *
 * + `wc_twilio_sms_<order status>_sms_template - the message template to use for the particular order status
 *
 * + `wc_twilio_sms_account_sid` - the account SID for the Twilio API
 *
 * + `wc_twilio_sms_auth_token` - the auth token for the Twilio API
 *
 * + `wc_twilio_sms_from_number` - the number that SMS will be sent from, must exist in the Twilio account used
 *
 * + `wc_twilio_sms_log_errors` - true to log errors to the WC error log, false otherwise
 *
 * ### Options table
 *
 * + `wc_twilio_sms_version` - the current plugin version, set on install/upgrade
 *
 * ### Order meta
 *
 * + `_wc_twilio_sms_optin` - set to true if the customer has opted-in to SMS order updates for this given order, false otherwise
 *
 */
class WC_Twilio_SMS extends SV_WC_Plugin {


	/** version number */
	const VERSION = '1.9.0';

	/** @var WC_Twilio_SMS single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'twilio_sms';

	/** plugin text domain */
	const TEXT_DOMAIN = 'woocommerce-twilio-sms-notifications';

	/** @var \WC_Twilio_SMS_Admin instance */
	protected $admin;

	/** @var \WC_Twilio_SMS_AJAX instance */
	protected $ajax;

	/** @var \WC_Twilio_SMS_API instance */
	private $api;


	/**
	 * Setup main plugin class
	 *
	 * @since 1.0
	 * @return \WC_Twilio_SMS
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'text_domain' => 'woocommerce-twilio-sms-notifications',
			)
		);

		// Load classes
		$this->includes();

		// Add opt-in checkbox to checkout
		add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'add_opt_in_checkbox' ) );

		// Process opt-in checkbox after order is processed
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'process_opt_in_checkbox' ) );

		// Add order status hooks, at priority 11 as Order Status Manager adds
		// custom statuses at 10
		add_action( 'init', array( $this, 'add_order_status_hooks' ), 11 );
	}


	/**
	 * Loads required classes
	 *
	 * @since 1.0
	 */
	private function includes() {

		// Notification class manages sending the SMS notifications.
		require_once( $this->get_plugin_path() . '/includes/class-wc-twilio-sms-notification.php' );

		// Response class manages creating XML response message.
		if ( isset( $_REQUEST['wc_twilio_sms_response'] ) ) {
			$this->load_class( '/includes/class-wc-twilio-sms-response.php', 'WC_Twilio_SMS_Response' );
		}

		// load admin classes
		if ( is_admin() ) {
			$this->admin_includes();
		}
	}


	/**
	 * Loads admin classes
	 *
	 * @since 1.0
	 */
	private function admin_includes() {

		// admin
		$this->admin = $this->load_class( '/includes/admin/class-wc-twilio-sms-admin.php', 'WC_Twilio_SMS_Admin' );

		// AJAX
		$this->ajax = $this->load_class( '/includes/class-wc-twilio-sms-ajax.php', 'WC_Twilio_SMS_AJAX' );
	}


	/**
	 * Return admin class instance
	 *
	 * @since 1.8.0
	 * @return \WC_Twilio_SMS_Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Return ajax class instance
	 *
	 * @since 1.8.0
	 * @return \WC_Twilio_SMS_AJAX
	 */
	public function get_ajax_instance() {
		return $this->ajax;
	}


	/**
	 * Add hooks for the opt-in checkbox and customer / admin order status changes
	 *
	 * @since 1.1
	 */
	public function add_order_status_hooks() {

		$statuses = wc_get_order_statuses();

		// Customer order status change hooks
		foreach ( array_keys( $statuses ) as $status ) {

			$status_slug = ( 'wc-' === substr( $status, 0, 3 ) ) ? substr( $status, 3 ) : $status;

			add_action( 'woocommerce_order_status_' . $status_slug, array( $this, 'send_customer_notification' ) );
		}

		// Admin new order hooks
		foreach ( array( 'pending_to_on-hold', 'pending_to_processing', 'pending_to_completed', 'failed_to_on-hold', 'failed_to_processing', 'failed_to_completed' ) as $status ) {

			add_action( 'woocommerce_order_status_' . $status, array( $this, 'send_admin_new_order_notification' ) );
		}
	}


	/**
	 * Send customer an SMS when their order status changes
	 *
	 * @since 1.1
	 */
	public function send_customer_notification( $order_id ) {

		$notification = new WC_Twilio_SMS_Notification( $order_id );

		$notification->send_automated_customer_notification();
	}


	/**
	 * Send admins an SMS when a new order is received
	 *
	 * @since 1.1
	 */
	public function send_admin_new_order_notification( $order_id ) {

		$notification = new WC_Twilio_SMS_Notification( $order_id );

		$notification->send_admin_notification();
	}


	/**
	 * Returns the Twilio SMS API object
	 *
	 * @since  1.1
	 * @return \WC_Twilio_SMS_API the API object
	 */
	public function get_api() {

		if ( is_object( $this->api ) ) {
			return $this->api;
		}

		// Load API
		require_once( $this->get_plugin_path() . '/includes/class-wc-twilio-sms-api.php' );

		$account_sid = get_option( 'wc_twilio_sms_account_sid', '' );
		$auth_token  = get_option( 'wc_twilio_sms_auth_token', '' );
		$from_number = get_option( 'wc_twilio_sms_from_number', '' );

		$options = array();

		if ( $asid = get_option( 'wc_twilio_sms_asid' ) ) {
			$options['asid'] = $asid;
		}

		return $this->api = new WC_Twilio_SMS_API( $account_sid, $auth_token, $from_number, $options );
	}


	/**
	 * Adds checkbox to checkout page for customer to opt-in to SMS notifications
	 *
	 * @since 1.0
	 */
	public function add_opt_in_checkbox() {

		// use previous value or default value when loading checkout page
		if ( ! empty( $_POST['wc_twilio_sms_optin'] ) ) {
			$value = wc_clean( $_POST['wc_twilio_sms_optin'] );
		} else {
			$value = ( 'checked' === get_option( 'wc_twilio_sms_checkout_optin_checkbox_default', 'unchecked' ) ) ? 1 : 0;
		}

		$optin_label = get_option( 'wc_twilio_sms_checkout_optin_checkbox_label', '' );

		if ( ! empty( $optin_label ) ) {

			// output checkbox
			woocommerce_form_field( 'wc_twilio_sms_optin', array(
				'type'  => 'checkbox',
				'class' => array( 'form-row-wide' ),
				'label' => $optin_label,
			), $value );
		}
	}


	/**
	 * Save opt-in as order meta
	 *
	 * TODO: This method will later need to instantiate an order / use a WC Data method. {BR 2017-02-22}
	 *
	 * @since 1.0
	 * @param int $order_id order ID for order being processed
	 */
	public function process_opt_in_checkbox( $order_id ) {

		if ( ! empty( $_POST['wc_twilio_sms_optin'] ) ) {
			update_post_meta( $order_id, '_wc_twilio_sms_optin', 1 );
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Main Twilio SMS Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.4.0
	 * @see wc_twilio_sms()
	 * @return WC_Twilio_SMS
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
	 * @since 1.2
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {

		return __( 'WooCommerce Twilio SMS Notifications', 'ultimatewoo-pro' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.2
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {

		return __FILE__;
	}


	/**
	 * Gets the URL to the settings page
	 *
	 * @since 1.2
	 * @see SV_WC_Plugin::is_plugin_settings()
	 * @param string $_ unused
	 * @return string URL to the settings page
	 */
	public function get_settings_url( $_ = '' ) {

		return admin_url( 'admin.php?page=wc-settings&tab=twilio_sms' );
	}


	/**
	 * Gets the plugin documentation URL
	 *
	 * @since 1.5.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {

		return 'http://docs.woocommerce.com/document/twilio-sms-notifications/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 1.5.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {

		return 'https://woocommerce.com/my-account/tickets/';
	}


	/**
	 * Returns true if on the plugin settings page
	 *
	 * @since 1.2
	 * @see SV_WC_Plugin::is_plugin_settings()
	 * @return boolean true if on the settings page
	 */
	public function is_plugin_settings() {

		return isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && isset( $_GET['tab'] ) && 'twilio_sms' === $_GET['tab'];
	}


	/**
	 * Log messages to WooCommerce error log if logging is enabled
	 *
	 * /wp-content/woocommerce/logs/twilio-sms.txt
	 *
	 * @since 1.1
	 * @param string $content message to log
	 * @param string $_ unused
	 */
	public function log( $content, $_ = null ) {

		if ( 'yes' === get_option( 'wc_twilio_sms_log_errors' ) ) {

			parent::log( $content );
		}
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Run every time.  Used since the activation hook is not executed when updating a plugin
	 *
	 * @since 1.0
	 */
	protected function install() {

		require_once( $this->get_plugin_path() . '/includes/admin/class-wc-twilio-sms-admin.php' );

		// install default settings
		foreach ( WC_Twilio_SMS_Admin::get_settings() as $setting ) {

			if ( isset( $setting['default'] ) ) {
				add_option( $setting['id'], $setting['default'] );
			}
		}
	}


	/**
	 * Perform any version-related changes.
	 *
	 * @since 1.0
	 * @param int $installed_version the currently installed version of the plugin
	 */
	protected function upgrade( $installed_version ) {

		// upgrade to 1.1.2 version
		if ( version_compare( $installed_version, '1.1.2' ) < 0 ) {

			delete_option( 'wc_twilio_sms_is_installed' );
		}

		// upgrade to 1.7.1 version
		if ( version_compare( $installed_version, '1.7.1', '<' ) ) {

			$sms_order_statuses = array();

			foreach ( wc_get_order_statuses() as $slug => $label ) {

				$old_slug = 'wc-' === substr( $slug, 0, 3 ) ? substr( $slug, 3 ) : $slug;

				if ( 'yes' === get_option( 'wc_twilio_sms_send_sms_' . $old_slug, 'yes' ) ) {
					$sms_order_statuses[] = $slug;
				}

				delete_option( 'wc_twilio_sms_send_sms_' . $old_slug );
			}

			update_option( 'wc_twilio_sms_send_sms_order_statuses', $sms_order_statuses );
		}
	}


} // end \WC_Twilio_SMS


/**
 * Returns the One True Instance of Twilio SMS
 *
 * @since 1.4.0
 * @return WC_Twilio_SMS
 */
function wc_twilio_sms() {
	return WC_Twilio_SMS::instance();
}

// fire it up!
wc_twilio_sms();

} // init_woocommerce_twilio_sms_notifications()

//1.9.0