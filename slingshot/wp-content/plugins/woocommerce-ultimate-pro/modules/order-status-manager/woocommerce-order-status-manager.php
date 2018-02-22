<?php
/**
 * Copyright: (c) 2015-2017, SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Order-Status-Manager
 * @author    SkyVerge
 * @category  Integration
 * @copyright Copyright (c) 2015-2017, SkyVerge, Inc.
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

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.6.0', __( 'WooCommerce Order Status Manager', 'ultimatewoo-pro' ), __FILE__, 'init_woocommerce_order_status_manager', array(
	'minimum_wc_version'   => '2.5.5',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4',
) );

function init_woocommerce_order_status_manager() {


/**
 * # WooCommerce Order Status Manager Main Plugin Class
 *
 * ## Plugin Overview
 *
 * This plugin allows adding custom order statuses to WooCommerce
 *
 * @since 1.0.0
 */
class WC_Order_Status_Manager extends SV_WC_Plugin {


	/** plugin version number */
	const VERSION = '1.7.1';

	/** @var WC_Order_Status_Manager single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'order_status_manager';

	/** plugin meta prefix */
	const PLUGIN_PREFIX = 'wc_order_status_manager_';

	/** @var \WC_Order_Status_Manager_Admin instance */
	protected $admin;

	/** @var \WC_Order_Status_Manager_Frontend instance */
	protected $frontend;

	/** @var \WC_Order_Status_Manager_AJAX instance */
	protected $ajax;

	/** @var \WC_Order_Status_Manager_Order_Statuses instance */
	protected $order_statuses;

	/** @var \WC_Order_Status_Manager_Emails instance */
	protected $emails;

	/** @var \WC_Order_Status_Manager_Icons instance */
	protected $icons;


	/**
	 * Initializes the plugin
	 *
	 * @since 1.0.0
	 * @return \WC_Order_Status_Manager
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'text_domain' => 'woocommerce-order-status-manager',
			)
		);

		// functions required before we hook into init
		require_once( $this->get_plugin_path() . '/includes/wc-order-status-manager-functions.php' );

		add_action( 'init', array( $this, 'init' ) );

		// make sure email template files are searched for in our plugin
		add_filter( 'woocommerce_locate_template',      array( $this, 'locate_template' ), 20, 3 );
		add_filter( 'woocommerce_locate_core_template', array( $this, 'locate_template' ), 20, 3 );

		// permit download for order custom statuses marked as paid:
		// we must keep this filter before init because WC_Download_Handler
		// instantiates early
		add_filter( 'woocommerce_order_is_download_permitted', array( $this, 'is_download_permitted' ), 10, 2 );

		if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_2_6() ) {

			// rename core order status labels with custom ones
			// this needs to be in main class to hook early before init
			add_filter( 'woocommerce_register_shop_order_post_statuses', array( $this, 'rename_core_order_status_labels' ), 20 );
			add_filter( 'wc_order_statuses',                             array( $this, 'rename_core_order_status_labels' ), 20 );
		}
	}


	/**
	 * Include Order Status Manager required files
	 *
	 * @since 1.0.0
	 */
	public function includes() {

		if ( null === $this->order_statuses ) {
			$this->order_statuses = $this->load_class( '/includes/class-wc-order-status-manager-order-statuses.php', 'WC_Order_Status_Manager_Order_Statuses' );
		}

		require_once( $this->get_plugin_path() . '/includes/class-wc-order-status-manager-post-types.php' );
		WC_Order_Status_Manager_Post_Types::initialize();

		$this->emails = $this->load_class( '/includes/class-wc-order-status-manager-emails.php', 'WC_Order_Status_Manager_Emails' );
		$this->icons  = $this->load_class( '/includes/class-wc-order-status-manager-icons.php', 'WC_Order_Status_Manager_Icons' );

		// load Frontend
		if ( ! is_admin() || is_ajax() ) {
			$this->frontend = $this->load_class( '/includes/class-wc-order-status-manager-frontend.php', 'WC_Order_Status_Manager_Frontend' );
		}

		// load Admin
		if ( is_admin() && ! is_ajax() ) {
			$this->admin_includes();
		}

		// load Ajax
		if ( is_ajax() ) {
			$this->ajax_includes();
		}
	}


	/**
	 * Include required admin files
	 *
	 * @since 1.0.0
	 */
	private function admin_includes() {

		$this->admin = $this->load_class( '/includes/admin/class-wc-order-status-manager-admin.php', 'WC_Order_Status_Manager_Admin' );
	}


	/**
	 * Include required AJAX files
	 *
	 * @since 1.0.0
	 */
	private function ajax_includes() {

		$this->ajax = $this->load_class( '/includes/class-wc-order-status-manager-ajax.php', 'WC_Order_Status_Manager_AJAX' );
	}


	/**
	 * Initialize translation and post types
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// include required files
		$this->includes();
	}


	/**
	 * Locates the WooCommerce template files from our templates directory
	 *
	 * @since 1.0.0
	 * @param string $template Already found template
	 * @param string $template_name Searchable template name
	 * @param string $template_path Template path
	 * @return string Search result for the template
	 */
	public function locate_template( $template, $template_name, $template_path ) {

		// Only keep looking if no custom theme template was found or if
 		// a default WooCommerce template was found.
 		if ( ! $template || SV_WC_Helper::str_starts_with( $template, WC()->plugin_path() ) ) {

 			// Set the path to our templates directory
 			$plugin_path = $this->get_plugin_path() . '/templates/';

 			// If a template is found, make it so
 			if ( is_readable( $plugin_path . $template_name ) ) {
 				$template = $plugin_path . $template_name;
 			}
 		}

		return $template;
	}


	/**
	 * Rename custom order statuses with custom labels
	 *
	 * We run this filter callback for both
	 * 'woocommerce_register_shop_order_post_statuses'
	 * and 'wc_order_statuses'
	 *
	 * This callback needs to run before init as it hooks
	 * into WooCommerce post status registration
	 *
	 * @since 1.5.0
	 * @param array $order_statuses Associative array of order statuses
	 * @return array
	 */
	public function rename_core_order_status_labels( $order_statuses ) {

		// get custom statuses
		$custom_order_statuses = wc_order_status_manager_get_order_status_posts( array(
			'suppress_filters' => false,
		) );

		if ( ! empty( $custom_order_statuses ) ) {

			foreach ( $custom_order_statuses as $custom_order_status_post ) {

				if ( ! empty( $custom_order_status_post->post_name ) && isset( $order_statuses[ 'wc-' . $custom_order_status_post->post_name ] ) ) {

					$slug  = 'wc-' . $custom_order_status_post->post_name;
					$label = $custom_order_status_post->post_title;

					if ( ! isset( $order_statuses[ $slug ] ) ) {
						continue;
					}

					if ( 'woocommerce_register_shop_order_post_statuses' === current_filter() ) {

						if ( is_array( $order_statuses[ $slug ] ) && isset( $order_statuses[ $slug ]['label'], $order_statuses[ $slug ]['label_count'] ) ) {

							// do not rename if a custom label is is identical
							if ( $label === $order_statuses[ $slug ]['label'] ) {
								continue;
							}

							$count = is_rtl() ? '<span class="count">(%s)</span> ' . $label : $label . ' <span class="count">(%s)</span>';

							$order_statuses[ $slug ]['label']       = $custom_order_status_post->post_title;
							$order_statuses[ $slug ]['label_count'] = _n_noop( $count, $count );
						}

					} elseif ( 'wc_order_statuses' === current_filter() ) {

						if ( $label !== $order_statuses[ $slug ] && is_string( $order_statuses[ $slug ] ) ) {
							$order_statuses[ $slug ] = $label;
						}
					}
				}
			}
		}

		return $order_statuses;
	}


	/**
	 * Permit downloads if a custom order status is marked as paid
	 *
	 * @see \WC_Download_Handler::check_order_is_valid()
	 *
	 * @since 1.3.0
	 * @param bool $maybe_permitted
	 * @param \WC_Order $order
	 * @return bool
	 */
	public function is_download_permitted( $maybe_permitted, $order ) {

		// callback runs early so we need to manually include necessary classes
		require_once( $this->get_plugin_path() . '/includes/class-wc-order-status-manager-order-status.php' );

		if ( null === $this->order_statuses ) {
			$this->order_statuses = $this->load_class( '/includes/class-wc-order-status-manager-order-statuses.php', 'WC_Order_Status_Manager_Order_Statuses' );
		}

		$order_status = new WC_Order_Status_Manager_Order_Status( $order->get_status() );

		if ( $order_status->get_id() > 0 ) {
			return $maybe_permitted || ( ! $order_status->is_core_status() && $order_status->is_paid() && 'yes' === get_option( 'woocommerce_downloads_grant_access_after_payment' ) );
		}

		return $maybe_permitted;
	}


	/** Getter methods ******************************************************/


	/**
	 * Get the Admin instance
	 *
	 * @since 1.5.0
	 * @return \WC_Order_Status_Manager_Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Get the Ajax instance
	 *
	 * @since 1.5.0
	 * @return \WC_Order_Status_Manager_AJAX
	 */
	public function get_ajax_instance() {
		return $this->ajax;
	}


	/**
	 * Get the Frontend instance
	 *
	 * @since 1.5.0
	 * @return \WC_Order_Status_Manager_Frontend
	 */
	public function get_frontend_instance() {
		return $this->frontend;
	}


	/**
	 * Get the Order Statuses instance
	 *
	 * @since 1.5.0
	 * @return \WC_Order_Status_Manager_Order_Statuses
	 */
	public function get_order_statuses_instance() {
		return $this->order_statuses;
	}


	/**
	 * Get the Emails instance
	 *
	 * @since 1.5.0
	 * @return \WC_Order_Status_Manager_Emails
	 */
	public function get_emails_instance() {
		return $this->emails;
	}


	/**
	 * Get the Icons instance
	 *
	 * @since 1.5.0
	 * @return \WC_Order_Status_Manager_Icons
	 */
	public function get_icons_instance() {
		return $this->icons;
	}


	/** Admin methods ******************************************************/


	/**
	 * Render a notice for the user to read the docs before using the plugin
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::add_admin_notices()
	 */
	public function add_admin_notices() {

		// show any dependency notices
		parent::add_admin_notices();

		$this->get_admin_notice_handler()->add_admin_notice(
			/* translators: 1$s - opening <a> link tag, 2$s - closing </a> link tag */
			sprintf( __( 'Thanks for installing Order Status Manager! Before you get started, please take a moment to %1$sread through the documentation%2$s.', 'ultimatewoo-pro' ),
				'<a href="' . $this->get_documentation_url() . '">',
				'</a>'
			),
			'read-the-docs',
			array( 'always_show_on_settings' => false, 'notice_class' => 'updated' )
		);
	}


	/** Helper methods ******************************************************/


	/**
	 * Main Order Status Manager Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.1.0
	 * @see wc_order_status_manager()
	 * @return \WC_Order_Status_Manager
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
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce Order Status Manager', 'ultimatewoo-pro' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


	/**
	 * Gets the URL to the settings page
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_settings_url()
	 * @param string $_ unused
	 * @return string URL to the settings page
	 */
	public function get_settings_url( $_ = null ) {
		return admin_url( 'edit.php?post_type=wc_order_status' );
	}


	/**
	 * Gets the plugin documentation URL
	 *
	 * @since 1.2.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {
		return 'https://docs.woocommerce.com/document/woocommerce-order-status-manager/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 1.2.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'https://woocommerce.com/my-account/tickets/';
	}


	/**
	 * Returns true if on the Order Status Manager settings page
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::is_plugin_settings()
	 * @return boolean true if on the settings page
	 */
	public function is_plugin_settings() {
		return isset( $_GET['post_type'] ) && 'wc_order_status' === $_GET['post_type'];
	}


	/**
	 * Check if an object, id or a slug matches that of an Order Status post type
	 *
	 * Will return the order status object if true
	 *
	 * @since 1.3.0
	 * @param int|\WP_Post|string $status Post ID, post object or post slug
	 * @return false|\WC_Order_Status_Manager_Order_Status
	 */
	public function is_order_status_cpt( $status ) {

		if ( is_numeric( $status ) ) {
			$order_status_cpt = get_post( $status );
		} elseif ( is_object( $status ) ) {
			$order_status_cpt = $status;
		} else {
			$order_status_cpt = get_page_by_path( $status, OBJECT, 'wc_order_status' );
		}

		if ( $order_status_cpt && isset( $order_status_cpt->post_type ) && 'wc_order_status' === $order_status_cpt->post_type ) {
			return new WC_Order_Status_Manager_Order_Status( $order_status_cpt );
		}

		return false;
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Install defaults
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::install()
	 */
	protected function install() {

		$this->icons->update_icon_options();

		// create posts for all order statuses
		$this->order_statuses->ensure_statuses_have_posts();
	}


	/**
	 * Perform any version-related changes.
	 *
	 * @since 1.0.0
	 * @param int $installed_version the currently installed version of the plugin
	 */
	protected function upgrade( $installed_version ) {

		// Always update icon options
		$this->icons->update_icon_options();

		// upgrade to 1.1.0
		if ( version_compare( $installed_version, '1.1.0', '<' ) ) {

			foreach ( $this->order_statuses->get_core_order_statuses() as $slug => $core_status ) {

				$status  = new WC_Order_Status_Manager_Order_Status( $slug );
				$post_id = $status->get_id();

				$slug = str_replace( 'wc-', '', $slug );

				switch ( $slug ) {

					case 'processing':
					case 'on-hold':
					case 'completed':
					case 'refunded':
						update_post_meta( $post_id, '_include_in_reports', 'yes' );
					break;

				}
			}
		}

		// upgrade to 1.3.0
		if ( version_compare( $installed_version, '1.3.0', '<' ) ) {

			foreach ( $this->order_statuses->get_core_order_statuses() as $slug => $core_status ) {

				$status  = new WC_Order_Status_Manager_Order_Status( $slug );
				$post_id = $status->get_id();

				$slug = str_replace( 'wc-', '', $slug );

				switch ( $slug ) {

					case 'processing':
					case 'completed':
						update_post_meta( $post_id, '_is_paid', 'yes' );
					break;

				}
			}
		}

		// upgrade to 1.4.5
		if ( version_compare( $installed_version, '1.4.5', '<' ) ) {

			foreach ( $this->order_statuses->get_core_order_statuses() as $slug => $core_status ) {

				$status  = new WC_Order_Status_Manager_Order_Status( $slug );
				$post_id = $status->get_id();

				$slug = str_replace( 'wc-', '', $slug );

				switch ( $slug ) {

					case 'processing':
					case 'completed':
					case 'on-hold':
						add_post_meta( $post_id, '_bulk_action', 'yes', true );
					break;

				}
			}
		}

		// upgrade to 1.6.1
		if ( version_compare( $installed_version, '1.6.1', '<' ) ) {

			foreach ( $this->order_statuses->get_core_order_statuses() as $slug => $core_status ) {

				$status  = new WC_Order_Status_Manager_Order_Status( $slug );
				$post_id = $status->get_id();

				$slug = str_replace( 'wc-', '', $slug );

				// for pending and failed statuses, update them if they're not set to "paid"
				if ( in_array( $slug, array( 'pending', 'failed' ), true ) && 'yes' !== get_post_meta( $post_id, '_is_paid', true ) ) {
					update_post_meta( $post_id, '_is_paid', 'needs_payment' );
				}

				// if this status doesn't have "is paid" meta saved, default to 'no'
				if ( ! metadata_exists( 'post', $post_id, '_is_paid' ) ) {
					add_post_meta( $post_id, '_is_paid', 'no', true );
				}
			}
		}
	}


} // end \WC_Order_Status_Manager class


/**
 * Returns the One True Instance of Order Status Manager
 *
 * @since 1.1.0
 * @return \WC_Order_Status_Manager
 */
function wc_order_status_manager() {
	return WC_Order_Status_Manager::instance();
}

// Launch!
wc_order_status_manager();

} // init_woocommerce_order_status_manager()

//1.7.1