<?php
/**
 * Copyright: (c) 2014-2018 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   Memberships
 * @author    SkyVerge
 * @copyright Copyright (c) 2014-2018, SkyVerge, Inc.
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

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.9.0', __( 'WooCommerce Memberships', 'ultimatewoo-pro' ), __FILE__, 'init_woocommerce_memberships', array(
	'minimum_wc_version'   => '2.6.14',
	'minimum_wp_version'   => '4.4',
	'backwards_compatible' => '4.4.0',
) );

// Required Action Scheduler library
require_once( plugin_dir_path( __FILE__ ) . 'lib/prospress/action-scheduler/action-scheduler.php' );

function init_woocommerce_memberships() {


/**
 * WooCommerce Memberships Main Plugin Class.
 *
 * @since 1.0.0
 */
class WC_Memberships extends SV_WC_Plugin {


	/** plugin version number */
	const VERSION = '1.9.7';

	/** @var WC_Memberships single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'memberships';

	/** @var \WC_Memberships_Admin instance */
	protected $admin;

	/** @var \WC_Memberships_AJAX instance */
	protected $ajax;

	/** @var \WC_Memberships_Capabilities instance */
	protected $capabilities;

	/** @var \WC_Memberships_Emails instance */
	protected $emails;

	/** @var \WC_Memberships_Frontend instance */
	protected $frontend;

	/** @var WC_Memberships_Integrations instance */
	protected $integrations;

	/** @var \WC_Memberships_Member_Discounts instance */
	protected $member_discounts;

	/** @var \WC_Memberships_Membership_Plans instance */
	protected $plans;

	/** @var \WC_Memberships_Restrictions instance */
	protected $restrictions;

	/** @var \WC_Memberships_Rules instance */
	protected $rules;

	/** @var \WC_Memberships_User_Memberships instance */
	protected $user_memberships;


	/**
	 * Initializes the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'text_domain'        => 'woocommerce-memberships',
				'display_php_notice' => true,
				'dependencies'       => array(
					'mbstring',
				),
			)
		);

		// include required files
		add_action( 'sv_wc_framework_plugins_loaded', array( $this, 'includes' ) );

		// initialize
		add_action( 'init', array( $this, 'init' ) );

		// make sure template files are searched for in our plugin
		add_filter( 'woocommerce_locate_template',      array( $this, 'locate_template' ), 20, 3 );
		add_filter( 'woocommerce_locate_core_template', array( $this, 'locate_template' ), 20, 3 );

		// lifecycle
		add_action( 'admin_init', array ( $this, 'maybe_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// add query vars for rewrite endpoints
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
	}


	/**
	 * Includes required files.
	 *
	 * @since 1.0.0
	 */
	public function includes() {

		// load post types
		require_once( $this->get_plugin_path() . '/includes/class-wc-memberships-post-types.php' );

		// load user messages helper
		require_once( $this->get_plugin_path() . '/includes/class-wc-memberships-user-messages.php' );

		// load helper functions
		require_once( $this->get_plugin_path() . '/includes/functions/wc-memberships-functions.php' );

		// init general classes
		$this->rules            = $this->load_class( '/includes/class-wc-memberships-rules.php',            'WC_Memberships_Rules' );
		$this->plans            = $this->load_class( '/includes/class-wc-memberships-membership-plans.php', 'WC_Memberships_Membership_Plans' );
		$this->emails           = $this->load_class( '/includes/class-wc-memberships-emails.php',           'WC_Memberships_Emails' );
		$this->user_memberships = $this->load_class( '/includes/class-wc-memberships-user-memberships.php', 'WC_Memberships_User_Memberships' );
		$this->capabilities     = $this->load_class( '/includes/class-wc-memberships-capabilities.php',     'WC_Memberships_Capabilities' );
		$this->member_discounts = $this->load_class( '/includes/class-wc-memberships-member-discounts.php', 'WC_Memberships_Member_Discounts' );
		$this->restrictions     = $this->load_class( '/includes/class-wc-memberships-restrictions.php',     'WC_Memberships_Restrictions' );

		// frontend includes
		if ( ! is_admin() ) {
			$this->frontend_includes();
		}

		// admin includes
		if ( is_admin() && ! is_ajax() ) {
			$this->admin_includes();
		}

		// AJAX includes
		if ( is_ajax() ) {
			$this->ajax_includes();
		}

		// load integrations
		$this->integrations = $this->load_class( '/includes/integrations/class-wc-memberships-integrations.php', 'WC_Memberships_Integrations' );

		// WP CLI support
		if ( defined( 'WP_CLI' ) && WP_CLI && version_compare( PHP_VERSION, '5.3.0', '>=' ) ) {
			include_once $this->get_plugin_path() . '/includes/class-wc-memberships-cli.php';
		}
	}


	/**
	 * Includes required admin classes.
	 *
	 * @since 1.0.0
	 */
	private function admin_includes() {

		$this->admin = $this->load_class( '/includes/admin/class-wc-memberships-admin.php', 'WC_Memberships_Admin' );

		// message handler
		$this->admin->message_handler = $this->get_message_handler();
	}


	/**
	 * Includes required AJAX classes.
	 *
	 * @since 1.0.0
	 */
	private function ajax_includes() {

		$this->ajax = $this->load_class( '/includes/class-wc-memberships-ajax.php', 'WC_Memberships_AJAX' );
	}


	/**
	 * Includes required frontend classes.
	 *
	 * @since 1.0.0
	 */
	private function frontend_includes() {

		// init shortcodes
		require_once( $this->get_plugin_path() . '/includes/class-wc-memberships-shortcodes.php' );
		WC_Memberships_Shortcodes::initialize();

		// load front end
		$this->frontend = $this->load_class( '/includes/frontend/class-wc-memberships-frontend.php', 'WC_Memberships_Frontend' );
	}


	/**
	 * Returns the Admin instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Returns the AJAX instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_AJAX
	 */
	public function get_ajax_instance() {
		return $this->ajax;
	}


	/**
	 * Returns the Capabilities instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_Capabilities
	 */
	public function get_capabilities_instance() {
		return $this->capabilities;
	}


	/**
	 * Get the Restrictions instance.
	 *
	 * @since 1.9.0
	 *
	 * @return \WC_Memberships_Restrictions
	 */
	public function get_restrictions_instance() {
		return $this->restrictions;
	}


	/**
	 * Returns the Frontend instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_Frontend
	 */
	public function get_frontend_instance() {
		return $this->frontend;
	}


	/**
	 * Returns the Emails instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_Emails
	 */
	public function get_emails_instance() {
		return $this->emails;
	}


	/**
	 * Returns the Integrations instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_Integrations
	 */
	public function get_integrations_instance() {
		return $this->integrations;
	}


	/**
	 * Returns the Member Discounts instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_Member_Discounts
	 */
	public function get_member_discounts_instance() {
		return $this->member_discounts;
	}


	/**
	 * Returns the Membership Plans instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_Membership_Plans
	 */
	public function get_plans_instance() {
		return $this->plans;
	}


	/**
	 * Returns the Rules instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_Rules
	 */
	public function get_rules_instance() {
		return $this->rules;
	}


	/**
	 * Returns the User Memberships instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \WC_Memberships_User_Memberships
	 */
	public function get_user_memberships_instance() {
		return $this->user_memberships;
	}


	/**
	 * Initializes post types.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		WC_Memberships_Post_Types::initialize();

		$this->add_rewrite_endpoints();
	}


	/**
	 * Locates the WooCommerce template files from our templates directory.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param string $template already found template
	 * @param string $template_name searchable template name
	 * @param string $template_path template path
	 * @return string search result for the template
	 */
	public function locate_template( $template, $template_name, $template_path ) {

		// only keep looking if no custom theme template was found
		// or if a default WooCommerce template was found
		if ( ! $template || SV_WC_Helper::str_starts_with( $template, WC()->plugin_path() ) ) {

			// set the path to our templates directory
			$plugin_path = $this->get_plugin_path() . '/templates/';

			// if a template is found, make it so
			if ( is_readable( $plugin_path . $template_name ) ) {
				$template = $plugin_path . $template_name;
			}
		}

		return $template;
	}


	/** Admin methods ******************************************************/


	/**
	 * Renders a notice for the user to read the docs before adding add-ons.
	 *
	 * @see \SV_WC_Plugin::add_admin_notices()
	 *
	 * @since 1.0.0
	 */
	public function add_admin_notices() {

		// show any dependency notices
		parent::add_admin_notices();

		$screen = get_current_screen();

		// only render on plugins or settings screen
		if ( 'plugins' === $screen->id || $this->is_plugin_settings() ) {

			$this->get_admin_notice_handler()->add_admin_notice(
				/* translators: the %s placeholders are meant for pairs of opening <a> and closing </a> link tags */
				sprintf( __( 'Thanks for installing Memberships! To get started, take a minute to %1$sread the documentation%2$s and then %3$ssetup a membership plan%4$s :)', 'ultimatewoo-pro' ),
					'<a href="https://docs.woocommerce.com/document/woocommerce-memberships/" target="_blank">',
					'</a>',
					'<a href="' . admin_url( 'edit.php?post_type=wc_membership_plan' ) . '">',
					'</a>' ),
				'get-started-notice',
				array( 'always_show_on_settings' => false, 'notice_class' => 'updated' )
			);
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Returns the Memberships instance singleton.
	 *
	 * Ensures only one instance is/can be loaded.
	 * @see wc_memberships()
	 *
	 * @since 1.0.0
	 *
	 * @return \WC_Memberships
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Returns the plugin documentation URL.
	 *
	 * @see \SV_WC_Plugin::get_documentation_url()
	 *
	 * @since 1.2.0
	 *
	 * @return string URL
	 */
	public function get_documentation_url() {
		return 'https://docs.woocommerce.com/document/woocommerce-memberships/';
	}


	/**
	 * Returns the plugin support URL.
	 *
	 * @see \SV_WC_Plugin::get_support_url()
	 *
	 * @since 1.2.0
	 *
	 * @return string URL
	 */
	public function get_support_url() {
		return 'https://woocommerce.com/my-account/tickets/';
	}


	/**
	 * Returns the plugin name, localized.
	 *
	 * @see \SV_WC_Plugin::get_plugin_name()
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce Memberships', 'ultimatewoo-pro' );
	}


	/**
	 * Returns the plugin filename path.
	 *
	 * @see \SV_WC_Plugin::get_file()
	 *
	 * @since 1.0.0
	 *
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


	/**
	 * Checks if the current is the Memberships Settings page.
	 *
	 * @see \SV_WC_Plugin::is_plugin_settings()
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_plugin_settings() {

		return isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && isset( $_GET['tab'] )
		       // the plugin's main settings page
		       && ( 'memberships' === $_GET['tab']
		       // the plugin's email settings pages
		       || ( 'email' === $_GET['tab'] && isset( $_GET['section'] ) && SV_WC_Helper::str_starts_with( $_GET['section'], 'wc_memberships_membership_' ) ) );
	}


	/**
	 * Returns the plugin configuration URL.
	 *
	 * @see \SV_WC_Plugin::get_settings_link()
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_id optional plugin identifier
	 * @return string plugin settings URL
	 */
	public function get_settings_url( $plugin_id = null ) {
		return admin_url( 'admin.php?page=wc-settings&tab=memberships' );
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Installs default settings & pages.
	 *
	 * @see \SV_WC_Plugin::install()
	 *
	 * @since 1.0.0
	 */
	protected function install() {

		// install default "content restricted" page
		$title   = _x( 'Content restricted', 'Page title', 'ultimatewoo-pro' );
		$slug    = _x( 'content-restricted', 'Page slug', 'ultimatewoo-pro' );
		$content = '[wcm_content_restricted]';

		wc_create_page( esc_sql( $slug ), 'wc_memberships_redirect_page_id', $title, $content );

		// include settings so we can install defaults
		include_once( WC()->plugin_path() . '/includes/admin/settings/class-wc-settings-page.php' );
		$settings = $this->load_class( '/includes/admin/class-wc-memberships-settings.php', 'WC_Settings_Memberships' );

		// install default settings for each section
		foreach ( $settings->get_sections() as $section => $label ) {

			foreach ( $settings->get_settings( $section ) as $setting ) {

				if ( isset( $setting['id'], $setting['default'] ) ) {

					update_option( $setting['id'], $setting['default'] );
				}
			}
		}
	}


	/**
	 * Runs upgrade scripts.
	 *
	 * @see \SV_WC_Plugin::install()
	 *
	 * @since 1.1.0
	 *
	 * @param string $installed_version semver
	 */
	protected function upgrade( $installed_version ) {

		require_once( $this->get_plugin_path() . '/includes/class-wc-memberships-upgrade.php' );

		WC_Memberships_Upgrade::run_update_scripts( $installed_version );

		$this->add_rewrite_endpoints();

		flush_rewrite_rules();
	}


	/**
	 * Handles plugin activation.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function maybe_activate() {

		$is_active = get_option( 'wc_memberships_is_active', false );

		if ( ! $is_active ) {

			update_option( 'wc_memberships_is_active', true );

			/**
			 * Runs when Memberships is activated.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wc_memberships_activated' );

			$this->add_rewrite_endpoints();

			flush_rewrite_rules();
		}
	}


	/**
	 * Handles plugin deactivation.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {

		delete_option( 'wc_memberships_is_active' );

		/**
		 * Runs when Memberships is deactivated.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_memberships_deactivated' );

		flush_rewrite_rules();
	}


	/**
	 * Adds rewrite rules endpoints.
	 *
	 * @since 1.9.0
	 */
	private function add_rewrite_endpoints() {

		// add Members Area endpoint
		add_rewrite_endpoint( get_option( 'woocommerce_myaccount_members_area_endpoint', 'members-area' ), EP_ROOT | EP_PAGES );
	}


	/**
	 * Handles query vars for endpoints.
	 *
	 * @internal
	 *
	 * @since 1.9.0
	 *
	 * @param array $query_vars associative array
	 * @return array
	 */
	public function add_query_vars( $query_vars ) {

		$query_vars[] = get_option( 'using_permalinks' ) ? get_option( 'woocommerce_myaccount_members_area_endpoint', 'members-area' ) : 'members_area';

		return $query_vars;
	}


	/** Deprecated methods ******************************************************/


	/**
	 * Backwards compatibility handler for deprecated methods.
	 *
	 * TODO remove deprecated methods when they are at least 3 minor versions older (as in x.Y.z semantic versioning) {FN 2017-06-23}
	 *
	 * @since 1.6.0
	 *
	 * @param string $method method called
	 * @param void|string|array|mixed $args optional argument(s)
	 * @return null|void|mixed
	 */
	public function __call( $method, $args ) {

		switch ( $method ) {

			/** @deprecated since 1.8.0 - remove by 1.11.0 or higher */
			case 'admin_list_post_links' :

				_deprecated_function( 'wc_memberships()->admin_list_post_links()', '1.8.0' );

				$posts = isset( $args[0] ) ? $args[0] : $args;

				if ( empty( $posts ) ) {
					return '';
				}

				$items = array();

				foreach ( $posts as $post ) {
					$items[] = '<a href="' . get_edit_post_link( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a>';
				}

				return wc_memberships_list_items( $items, __( 'and', 'ultimatewoo-pro' ) );

			/** @deprecated since 1.7.0 - remove by 1.10.0 or higher */
			case 'get_access_granting_purchased_product_ids' :

				_deprecated_function( 'wc_memberships()->get_access_granting_purchased_product_ids()', '1.7.0', 'wc_memberships_get_order_access_granting_product_ids()' );

				$plan        = isset( $args[0] ) ? $args[0] : null;
				$order       = isset( $args[1] ) ? $args[1] : null;
				$order_items = isset( $args[2] ) ? $args[2] : array();

				return wc_memberships_get_order_access_granting_product_ids( $plan, $order, $order_items );

			/** @deprecated since 1.9.0 - remove by version 1.12.0 or higher */
			case 'get_query_instance' :
				_deprecated_function( 'wc_memberships()->get_query_instance()', '1.9.0' );
				return null;

			/** @deprecated since 1.7.0 - remove by 1.10.0 or higher */
			case 'grant_membership_access' :

				_deprecated_function( 'wc_memberships()->grant_membership_access()', '1.7.0', 'wc_memberships()->get_plans_instance()->grant_access_to_membership_from_order()' );

				$plans     = wc_memberships()->get_plans_instance();
				$order_id  = isset( $args[0] ) ? $args[0] : $args;

				if ( $plans ) {
					$plans->grant_access_to_membership_from_order( $order_id );
				}

				return null;

		}

		// you're probably doing it wrong...
		trigger_error( 'Call to undefined method ' . __CLASS__ . '::' . $method, E_USER_ERROR );
		return null;
	}


} // end WC_Memberships class


/**
 * Returns the One True Instance of Memberships
 *
 * @since 1.0.0
 * @return WC_Memberships
 */
function wc_memberships() {
	return WC_Memberships::instance();
}

// fire it up!
wc_memberships();

} // init_woocommerce_memberships()

//1.9.7