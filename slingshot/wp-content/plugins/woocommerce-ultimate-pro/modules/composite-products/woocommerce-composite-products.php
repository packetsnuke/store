<?php
/*
* Copyright: © 2017 SomewhereWarm SMPC.
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check if WooCommerce is active.
if ( ! is_woocommerce_active() ) {
	return;
}

/**
 * Main plugin class.
 *
 * @class    WC_Composite_Products
 * @version  3.11.3
 */

class WC_Composite_Products {

	public $version  = '3.11.3';
	public $required = '2.5.0';

	/**
	 * The single instance of the class.
	 * @var WC_Composite_Products
	 *
	 * @since 3.2.3
	 */
	protected static $_instance = null;

	/**
	 * Main WC_Composite_Products instance.
	 *
	 * Ensures only one instance of WC_Composite_Products is loaded or can be loaded - @see 'WC_CP()'.
	 *
	 * @since  3.2.3
	 *
	 * @static
	 * @return WC_Composite_Products - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 3.2.3
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'ultimatewoo-pro' ), '3.2.3' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 3.2.3
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'ultimatewoo-pro' ), '3.2.3' );
	}

	/**
	 * Contructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Entry point.
		add_action( 'plugins_loaded', array( $this, 'initialize_plugin' ), 9 );
	}

	/**
	 * Auto-load in-accessible properties.
	 *
	 * @param  mixed  $key
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( in_array( $key, array( 'api', 'compatibility', 'cart', 'order', 'display' ) ) ) {
			$classname = 'WC_CP_' . ucfirst( $key );
			return call_user_func( array( $classname, 'instance' ) );
		}
	}

	/**
	 * Gets the plugin url.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function plugin_url() {
		return ULTIMATEWOO_MODULES_URL . '/composite-products';
	}

	/**
	 * Gets the plugin path.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function plugin_path() {
		return ULTIMATEWOO_MODULES_DIR . '/composite-products';
	}

	/**
	 * Plugin base path name getter.
	 *
	 * @since  3.7.0
	 *
	 * @return string
	 */
	public function plugin_basename() {
		return plugin_basename( __FILE__ );
	}


	/**
	 * Fire in the hole!
	 */
	public function initialize_plugin() {

		// WC version sanity check.
		if ( version_compare( WC()->version, $this->required ) < 0 ) {
			$notice = sprintf( __( 'WooCommerce Composite Products requires at least WooCommerce %s in order to function. Please upgrade WooCommerce.', 'ultimatewoo-pro' ), $this->required );
			require_once( 'includes/admin/class-wc-cp-admin-notices.php' );
			WC_CP_Admin_Notices::add_notice( $notice, 'error' );
			return false;
		}

		$this->define_constants();
		$this->includes();

		WC_CP_API::instance();
		WC_CP_Compatibility::instance();
		WC_CP_Cart::instance();
		WC_CP_Order::instance();
		WC_CP_Display::instance();

		// Load translations hook.
		add_action( 'init', array( $this, 'load_translation' ) );
	}

	/**
	 * Constants.
	 */
	public function define_constants() {

		if ( 'yes' === get_option( 'woocommerce_composite_products_debug_query_transients', null ) ) {
			/**
			 * 'WC_CP_DEBUG_QUERY_TRANSIENTS' constant.
			 *
			 * Disables the query transients cache.
			 */
			define( 'WC_CP_DEBUG_QUERY_TRANSIENTS', true );
		}

		if ( 'yes' === get_option( 'woocommerce_composite_products_debug_permutation_transients', null ) ) {
			/**
			 * 'WC_CP_DEBUG_PERMUTATION_TRANSIENTS' constant.
			 *
			 * Disables the permutation transients cache.
			 */
			define( 'WC_CP_DEBUG_PERMUTATION_TRANSIENTS', true );
		}

		if ( 'yes' === get_option( 'woocommerce_composite_products_debug_runtime_cache', null ) ) {
			/**
			 * 'WC_CP_DEBUG_RUNTIME_CACHE' constant.
			 *
			 * Disables the runtime object cache.
			 */
			define( 'WC_CP_DEBUG_RUNTIME_CACHE', true );
		}
	}

	/**
	 * Includes.
	 */
	public function includes() {

		// Class containing extensions compatibility functions and filters.
		require_once( 'includes/compatibility/class-wc-cp-compatibility.php' );

		// Install.
		require_once( 'includes/class-wc-cp-install.php' );

		// CRUD.
		if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ) {
			require_once( 'includes/data/class-wc-cp-data.php' );
		}

		// CP functions.
		require_once( 'includes/wc-cp-functions.php' );
		require_once( 'includes/wc-cp-deprecated-functions.php' );

		// Composite widget.
		require_once( 'includes/wc-cp-widget-functions.php' );

		// PHP 5.5+ cartesion product generator function.
		if ( WC_CP_Compatibility::php_version_gte( '5.5.0' ) ) {
			require_once( 'includes/wc-cp-generator-functions.php' );
		}

		// WP_Query wrapper for component option queries.
		require_once( 'includes/class-wc-cp-query.php' );

		// Component abstraction.
		require_once( 'includes/class-wc-cp-component.php' );

		// Component view state.
		require_once( 'includes/class-wc-cp-component-view.php' );

		// Composited product wrapper.
		require_once( 'includes/class-wc-cp-product.php' );

		// Filters and functions to support the "composited product" concept.
		require_once( 'includes/class-wc-cp-products.php' );

		// Composite products API.
		require_once( 'includes/class-wc-cp-api.php' );

		// Composite products Scenarios API.
		require_once( 'includes/class-wc-cp-scenario.php' );
		require_once( 'includes/class-wc-cp-scenarios-manager.php' );

		// Legacy Scenarios API - deprecated.
		require_once( 'includes/legacy/class-wc-cp-scenarios.php' );

		// Helper functions.
		require_once( 'includes/class-wc-cp-helpers.php' );

		// Composite products AJAX handlers.
		require_once( 'includes/class-wc-cp-ajax.php' );

		// Composite product class.
		if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ) {
			require_once( 'includes/class-wc-product-composite.php' );
		} else {
			require_once( 'includes/legacy/class-wc-product-composite.php' );
		}

		// Stock manager
		require_once( 'includes/class-wc-cp-stock-manager.php' );

		// Cart-related functions and filters.
		require_once( 'includes/class-wc-cp-cart.php' );

		// Order-related functions and filters.
		if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ) {
			require_once( 'includes/class-wc-cp-order.php' );
		} else {
			require_once( 'includes/legacy/class-wc-cp-order.php' );
		}

		// Front-end functions and filters.
		require_once( 'includes/class-wc-cp-display.php' );

		// REST API hooks.
		require_once( 'includes/class-wc-cp-rest-api.php' );

		// Admin functions and meta-boxes.
		if ( is_admin() ) {
			$this->admin_includes();
		}
	}

	/**
	 * Loads the Admin filters / hooks.
	 */
	private function admin_includes() {

		// Admin notices handling.
		require_once( 'includes/admin/class-wc-cp-admin-notices.php' );

		// Admin hooks.
		require_once( 'includes/admin/class-wc-cp-admin.php' );
	}

	/**
	 * Load textdomain.
	 */
	public function load_translation() {
		load_plugin_textdomain( 'woocommerce-composite-products', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/**
 * Returns the main instance of WC_Composite_Products to prevent the need to use globals.
 *
 * @since  3.2.3
 * @return WC_Composite_Products
 */
function WC_CP() {
  return WC_Composite_Products::instance();
}

$GLOBALS[ 'woocommerce_composite_products' ] = WC_CP();

//3.11.3