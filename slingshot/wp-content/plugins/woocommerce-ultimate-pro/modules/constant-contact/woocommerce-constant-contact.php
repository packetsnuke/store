<?php
/**
 * Copyright: (c) 2013-2017, SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Constant-Contact
 * @author    SkyVerge
 * @category  Marketing
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

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.6.0', __( 'WooCommerce Constant Contact', 'ultimatewoo-pro' ), __FILE__, 'init_woocommerce_constant_contact', array(
	'minimum_wc_version'   => '2.5.5',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4',
) );

function init_woocommerce_constant_contact() {

/**
 * # WooCommerce Constant Contact Main Plugin Class
 *
 * ## Plugin Overview
 *
 * Adds an opt-in checkbox on the checkout page that adds the customer to the admin-selected constant contact email list.
 * Also adds an opt-in button on the Thank You page for PayPal Express users (and as 2nd opportunity for customers to
 * opt-in to email list. Admins can also add signup forms to the website with the included widget.
 *
 * ## Admin Considerations
 *
 * Settings are added to WooCommerce > Settings > Constant Contact. A dashboard widget is added so the admin can view
 * quick stats about the chosen email list.
 *
 * ## Frontend Considerations
 *
 * Opt-in checkbox added to checkout page. Opt-in button added to thank you page. Opt-in form can be displayed on the
 * frontend with the included widget.
 *
 * ## Database
 *
 * ### Global Settings
 *
 *
 * ### Options table
 *
 * + `wc_constant_contact_version` - the current plugin version, set on install/upgrade
 * + `wc_constant_contact_subscribe_checkbox_label` - admin settings: Text displayed next to the opt-in checkbox on the Checkout page
 * + `wc_constant_contact_subscribe_checkbox_default` - admin settings: Default status for the Subscribe checkbox on the Checkout page
 * + `wc_constant_contact_email_list` - admin settings: string email list
 * + `wc_constant_contact_username` - admin settings: constant contact username
 * + `wc_constant_contact_password` - admin settings: constant contact password
 * + `wc_constant_contact_api_key` - admin settings: constant contact API key
 * + `wc_constant_contact_debug_mode` - admin settings: string 'yes' or 'no' to indicate whether debug mode is enabled
 *
 * ### User Meta Table
 *
 * + `_wc_constant_contact_id` - the customer constant contact id
 *
 * ### Transients
 *
 * + `wc_constant_contact_stats` - associative array containing list_name and list_subscribers (int count) indexes
 *
 * @since 1.0
 */
class WC_Constant_Contact extends SV_WC_Plugin {


	/** plugin version number */
	const VERSION = '1.8.0';

	/** @var WC_Constant_Contact single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'constant_contact';

	/** plugin text domain, DEPRECATED as of 1.6.0 */
	const TEXT_DOMAIN = 'woocommerce-constant-contact';

	/** @var \WC_Constant_Contact_API instance */
	private $api;

	/** @var \WC_Constant_Contact_Frontend instance */
	protected $frontend;

	/** @var \WC_Constant_Contact_Settings instance */
	protected $settings;

	/** @var \WC_Constant_Contact_Points_And_Rewards instance */
	protected $points_and_rewards;


	/**
	 * Initializes the plugin
	 *
	 * @since 1.0
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'text_domain' => 'woocommerce-constant-contact',
			)
		);

		// include required files
		$this->includes();

		// load widget
		add_action( 'widgets_init', array( $this, 'init_widget' ) );

		// log API if debug mode is enabled
		if ( 'on' !== get_option( 'wc_constant_contact_debug_mode' ) ) {
			remove_action( 'wc_' . $this->get_id() . '_api_request_performed', array( $this, 'log_api_request' ), 10 );
		}

		// admin
		if ( is_admin() && ! is_ajax() ) {

			// load dashboard
			add_action( 'wp_dashboard_setup', array( $this, 'init_dashboard' ) );

			// add settings page
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_settings_page' ) );
		}
	}


	/**
	 * Include required files
	 *
	 * @since 1.0
	 */
	private function includes() {

		$this->frontend = $this->load_class( '/includes/class-wc-constant-contact-frontend.php', 'WC_Constant_Contact_Frontend' );

		if ( $this->is_plugin_active( 'woocommerce-points-and-rewards.php' ) ) {
			$this->points_and_rewards = $this->load_class( '/includes/class-wc-constant-contact-points-and-rewards.php', 'WC_Constant_Contact_Points_and_Rewards' );
		}
	}


	/**
	 * Return frontend class instance
	 *
	 * @since 1.7.0
	 * @return \WC_Constant_Contact_Frontend
	 */
	public function get_frontend_instance() {
		return $this->frontend;
	}


	/**
	 * Return Points and Rewards class instance
	 *
	 * @since 1.7.0
	 * @return \WC_Constant_Contact_Points_And_Rewards
	 */
	public function get_points_and_rewards_instance() {
		return $this->points_and_rewards;
	}


	/**
	 * Return settings class instance
	 *
	 * @since 1.7.0
	 * @return \WC_Constant_Contact_Settings
	 */
	public function get_settings_instance() {
		return $this->settings;
	}


	/** Frontend methods ******************************************************/


	/**
	 * Load the 'Subscribe' widget
	 *
	 * @since 1.0
	 */
	public function init_widget() {

		require_once( $this->get_plugin_path() . '/includes/class-wc-constant-contact-widget.php' );

		register_widget( 'WC_Constant_Contact_Widget' );
	}


	/** Admin methods ******************************************************/


	/**
	 * Add settings page
	 *
	 * @since 1.3.1
	 * @param array $settings
	 * @return array
	 */
	public function add_settings_page( $settings ) {

		if ( ! $this->settings instanceof WC_Constant_Contact_Settings ) {
			$this->settings = $this->load_class( '/includes/admin/class-wc-constant-contact-settings.php', 'WC_Constant_Contact_Settings' );
		}

		$settings[] = $this->settings;
		return $settings;
	}


	/**
	 * Load dashboard stats
	 *
	 * @since 1.0
	 */
	public function init_dashboard() {

		if ( current_user_can( 'manage_woocommerce' ) && $this->get_api() ) {
			wp_add_dashboard_widget( 'wc_constant_contact_dashboard', __( 'Email List Subscribers', 'ultimatewoo-pro' ), array( $this, 'render_dashboard' ) );
		}
	}


	/**
	 * Render dashboard stats, which only includes total email subscribers at the moment
	 *
	 * @since 1.0
	 */
	public function render_dashboard() {

		if ( false === ( $stats = get_transient( 'wc_constant_contact_stats' ) ) ) {

			try {

				$stats = $this->get_api()->get_stats( get_option( 'wc_constant_contact_email_list' ) );

			} catch ( SV_WC_API_Exception $e ) {

				$this->log( sprintf( __( 'Error loading stats: %s', 'ultimatewoo-pro' ), $e->getMessage() ) );
			}

			if ( ! empty( $stats ) ) {
				set_transient( 'wc_constant_contact_stats', $stats, 60 * 60 * 1 );
			}
		}

		if ( empty( $stats ) ) {

			echo '<div class="error inline"><p>' . __( 'Unable to load stats from Constant Contact', 'ultimatewoo-pro' ) . '</p></div>';

		} else {

			?>
			<style type="text/css">ul.wc_constant_contact_stats{overflow:hidden;zoom:1}ul.wc_constant_contact_stats li{width:22%;padding:0 1.4%;float:left;font-size:0.8em;border-left:1px solid #fff;border-right:1px solid #ececec;text-align:center} ul.wc_constant_contact_stats li:first-child{border-left:0} ul.wc_constant_contact_stats li:last-child{border-right:0} ul.wc_constant_contact_stats strong{font-family:Georgia,"Times New Roman","Bitstream Charter",Times,serif;font-size:4em;line-height:1.2em;font-weight:normal;text-align:center;display:block}</style>
			<h2><?php echo esc_html( $stats['list_name'] ); ?></h2>
			<ul class="wc_constant_contact_stats">
				<li><strong><?php echo esc_html( $stats['list_subscribers'] ); ?></strong> <?php echo _n( 'Subscriber', 'Subscribers', $stats['list_subscribers'], 'ultimatewoo-pro' ); ?></li>
			</ul>
		<?php
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Main Constant Contact Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.4.0
	 * @see wc_constant_contact()
	 * @return WC_Constant_Contact
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Lazy load the Constant Contact API object
	 *
	 * @since 1.0
	 * @return null|\WC_Constant_Contact_API
	 */
	public function get_api() {

		if ( is_object( $this->api ) ) {
			return $this->api;
		}

		$username = get_option( 'wc_constant_contact_username' );
		$password = get_option( 'wc_constant_contact_password' );
		$api_key  = get_option( 'wc_constant_contact_api_key' );

		// bail if required info is not available
		if ( ! $username || ! $password || ! $api_key ) {
			return null;
		}

		// load API wrapper
		require_once( $this->get_plugin_path() . '/includes/api/class-wc-constant-contact-api.php' );
		require_once( $this->get_plugin_path() . '/includes/api/class-wc-constant-contact-api-request.php' );
		require_once( $this->get_plugin_path() . '/includes/api/class-wc-constant-contact-api-response.php' );

		return $this->api = new WC_Constant_Contact_API( $username, $password, $api_key );
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.2
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {

		return __( 'WooCommerce Constant Contact', 'ultimatewoo-pro' );
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

		return admin_url( 'admin.php?page=wc-settings&tab=constant_contact' );
	}


	/**
	 * Gets the plugin documentation URL
	 *
	 * @since  1.5.0
	 * @see    SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {

	    return 'http://docs.woocommerce.com/document/woocommerce-constant-contact/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since  1.5.0
	 * @see    SV_WC_Plugin::get_support_url()
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

		return isset( $_GET['page'] ) && 'wc-settings' == $_GET['page'] && isset( $_GET['tab'] ) && 'constant_contact' == $_GET['tab'];
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Run every time.  Used since the activation hook is not executed when updating a plugin
	 *
	 * @since 1.0
	 */
	protected function install() {

		// load settings so we can install defaults
		include_once( WC()->plugin_path() . '/includes/admin/settings/class-wc-settings-page.php' );

		$this->settings = $this->load_class( '/includes/admin/class-wc-constant-contact-settings.php', 'WC_Constant_Contact_Settings' );

		// default settings
		foreach ( $this->get_settings_instance()->get_settings() as $setting ) {

			if ( isset( $setting['default'] ) ) {
				add_option( $setting['id'], $setting['default'] );
			}
		}
	}


} // end \WC_Constant_Contact class


/**
 * Returns the One True Instance of Constant Contact
 *
 * @since 1.4.0
 * @return \WC_Constant_Contact
 */
function wc_constant_contact() {
	return WC_Constant_Contact::instance();
}

// fire it up!
wc_constant_contact();

} // init_woocommerce_constant_contact()

//1.8.0