<?php
/**
 * WC_PB_Compatibility class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    4.6.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles compatibility with other WC extensions.
 *
 * @class    WC_PB_Compatibility
 * @version  5.4.0
 */
class WC_PB_Compatibility {

	/**
	 * Min required plugin versions to check.
	 * @var array
	 */
	private $required = array();

	/**
	 * Publicly accessible props for use by compat classes. Still not moved for back-compat.
	 * @var array
	 */
	public static $addons_prefix          = '';
	public static $nyp_prefix             = '';
	public static $bundle_prefix          = '';
	public static $compat_product         = '';
	public static $compat_bundled_product = '';
	public static $stock_data;

	/**
	 * The single instance of the class.
	 * @var WC_PB_Compatibility
	 *
	 * @since 5.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main WC_PB_Compatibility instance. Ensures only one instance of WC_PB_Compatibility is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_PB_Compatibility
	 * @since  5.0.0
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
	 * @since 5.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'ultimatewoo-pro' ), '5.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 5.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'ultimatewoo-pro' ), '5.0.0' );
	}

	/**
	 * Setup compatibility class.
	 */
	protected function __construct() {

		// Define dependencies.
		$this->required = array(
			'cp'     => '3.10',
			'addons' => '2.7.16',
			'minmax' => '1.1'
		);

		// Initialize.
		$this->load_modules();
	}

	/**
	 * Initialize.
	 *
	 * @since  5.4.0
	 *
	 * @return void
	 */
	protected function load_modules() {

		if ( is_admin() ) {
			// Check plugin min versions.
			// ULTIMATEWOO fix
			// add_action( 'admin_init', array( $this, 'add_compatibility_notices' ) );
		}

		// Load modules.
		add_action( 'plugins_loaded', array( $this, 'module_includes' ), 100 );

		// Prevent initialization of deprecated mini-extensions.
		$this->unload_modules();
	}

	/**
	 * Core compatibility functions.
	 *
	 * @return void
	 */
	public static function core_includes() {
		require_once( 'core/class-wc-pb-core-compatibility.php' );
	}

	/**
	 * Prevent deprecated mini-extensions from initializing.
	 *
	 * @since  5.0.0
	 *
	 * @return void
	 */
	protected function unload_modules() {

		// Tabular Layout mini-extension was merged into Bundles.
		if ( class_exists( 'WC_PB_Tabular_Layout' ) ) {
			remove_action( 'plugins_loaded', array( 'WC_PB_Tabular_Layout', 'load_plugin' ), 10 );
		}
	}

	/**
	 * Load compatibility classes.
	 *
	 * @return void
	 */
	public function module_includes() {

		// Addons support.
		if ( class_exists( 'WC_Product_Addons' ) ) {
			require_once( 'modules/class-wc-addons-compatibility.php' );
		}

		// NYP support.
		if ( function_exists( 'WC_Name_Your_Price' ) ) {
			require_once( 'modules/class-wc-nyp-compatibility.php' );
		}

		// Points and Rewards support.
		if ( class_exists( 'WC_Points_Rewards_Product' ) ) {
			require_once( 'modules/class-wc-pnr-compatibility.php' );
		}

		// Pre-orders support.
		if ( class_exists( 'WC_Pre_Orders' ) ) {
			require_once( 'modules/class-wc-po-compatibility.php' );
		}

		// Composite Products support.
		if ( class_exists( 'WC_Composite_Products' ) && function_exists( 'WC_CP' ) && version_compare( WC_CP()->version, $this->required[ 'cp' ] ) >= 0 ) {
			require_once( 'modules/class-wc-cp-compatibility.php' );
		}

		// One Page Checkout support.
		if ( function_exists( 'is_wcopc_checkout' ) ) {
			require_once( 'modules/class-wc-opc-compatibility.php' );
		}

		// Cost of Goods support.
		if ( class_exists( 'WC_COG' ) ) {
			require_once( 'modules/class-wc-cog-compatibility.php' );
		}

		// QuickView support.
		if ( class_exists( 'WC_Quick_View' ) ) {
			require_once( 'modules/class-wc-qv-compatibility.php' );
		}

		// PIP support.
		if ( class_exists( 'WC_PIP' ) ) {
			require_once( 'modules/class-wc-pip-compatibility.php' );
		}

		// Subscriptions fixes.
		if ( class_exists( 'WC_Subscriptions' ) ) {
			require_once( 'modules/class-wc-subscriptions-compatibility.php' );
		}

		// Import/Export Suite support.
		if ( class_exists( 'WC_Product_CSV_Import_Suite' ) ) {
			require_once( 'modules/class-wc-ie-compatibility.php' );
		}

		// WP Import/Export support.
		require_once( 'modules/class-wp-ie-compatibility.php' );

		// WooCommerce Give Products support.
		if ( class_exists( 'WC_Give_Products' ) ) {
			require_once( 'modules/class-wc-give-products-compatibility.php' );
		}

		// Shipwire integration.
		if ( class_exists( 'WC_Shipwire' ) ) {
			require_once( 'modules/class-wc-shipwire-compatibility.php' );
		}

		// Shipstation integration.
		require_once( 'modules/class-wc-shipstation-compatibility.php' );
	}

	/**
	 * Checks versions of compatible/integrated/deprecated extensions.
	 *
	 * @return void
	 */
	public function add_compatibility_notices() {

		global $woocommerce_composite_products;

		// PB version check.
		if ( ! empty( $woocommerce_composite_products ) && version_compare( $woocommerce_composite_products->version, $this->required[ 'cp' ] ) < 0 ) {
			$notice = sprintf( __( '<strong>WooCommerce Product Bundles</strong> is not compatible with the version of <strong>WooCommerce Composite Products</strong> found on your system. Please update <strong>WooCommerce Composite Products</strong> to version <strong>%s</strong> or higher.', 'ultimatewoo-pro' ), $this->required[ 'cp' ] );
			WC_PB_Admin_Notices::add_notice( $notice, 'warning' );
		}

		// Addons version check.
		if ( class_exists( 'WC_Product_Addons' ) ) {
			$reflector   = new ReflectionClass( 'WC_Product_Addons' );
			$file        = $reflector->getFileName();
			$addons_data = get_plugin_data( $file, false, false );
			$version     = $addons_data[ 'Version' ];
			if ( version_compare( $version, $this->required[ 'addons' ] ) < 0 ) {
				$notice = sprintf( __( '<strong>WooCommerce Product Bundles</strong> is not compatible with the version of <strong>WooCommerce Product Addons</strong> found on your system. Please update <strong>WooCommerce Product Addons</strong> to version <strong>%s</strong> or higher.', 'ultimatewoo-pro' ), $this->required[ 'addons' ] );
				WC_PB_Admin_Notices::add_notice( $notice, 'warning' );
			}
		}

		// Tabular layout mini-extension check.
		if ( class_exists( 'WC_PB_Tabular_Layout' ) ) {
			$notice = sprintf( __( 'The <strong>WooCommerce Product Bundles - Tabular Layout</strong> mini-extension is now part of <strong>WooCommerce Product Bundles</strong>. Please deactivate and remove the <strong>WooCommerce Product Bundles - Tabular Layout</strong> plugin.', 'ultimatewoo-pro' ) );
			WC_PB_Admin_Notices::add_notice( $notice, 'warning' );
		}

		// Min/Max Items mini-extension version check.
		if ( class_exists( 'WC_PB_Min_Max_Items' ) && version_compare( WC_PB_Min_Max_Items::$version, $this->required[ 'minmax' ] ) < 0 ) {
			$min_max_repo_url = 'https://github.com/somewherewarm/woocommerce-product-bundles-min-max-items/releases';
			$notice = sprintf( __( 'The <strong>WooCommerce Product Bundles - Min/Max Items</strong> version found on your system is not compatible with the installed version of <strong>WooCommerce Product Bundles</strong>. Please <a href="%1$s" target="_blank">update</a> <strong>WooCommerce Product Bundles - Min/Max Items</strong> to version <strong>%2$s</strong> or higher.', 'ultimatewoo-pro' ), $min_max_repo_url, $this->required[ 'minmax' ] );
			WC_PB_Admin_Notices::add_notice( $notice, 'warning' );
		}
	}

	/**
	 * Tells if a product is a Name Your Price product, provided that the extension is installed.
	 *
	 * @param  mixed  $product_id
	 * @return boolean
	 */
	public function is_nyp( $product_id ) {

		if ( ! class_exists( 'WC_Name_Your_Price_Helpers' ) ) {
			return false;
		}

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Tells if a product is a subscription, provided that Subs is installed.
	 *
	 * @param  mixed  $product_id
	 * @return boolean
	 */
	public function is_subscription( $product_id ) {

		if ( ! class_exists( 'WC_Subscriptions' ) ) {
			return false;
		}

		return WC_Subscriptions_Product::is_subscription( $product_id );
	}

	/**
	 * Tells if an order item is a subscription, provided that Subs is installed.
	 *
	 * @param  mixed     $order
	 * @param  WC_Prder  $order
	 * @return boolean
	 */
	public function is_item_subscription( $order, $item ) {

		if ( ! class_exists( 'WC_Subscriptions_Order' ) ) {
			return false;
		}

		return WC_Subscriptions_Order::is_item_subscription( $order, $item );
	}

	/**
	 * Checks if a product has any required addons.
	 *
	 * @param  int  $product_id
	 * @return boolean
	 */
	public function has_required_addons( $product_id ) {

		if ( ! function_exists( 'get_product_addons' ) ) {
			return false;
		}

		$addons = get_product_addons( $product_id, false, false, true );

		if ( $addons && ! empty( $addons ) ) {
			foreach ( $addons as $addon ) {
				if ( '1' == $addon[ 'required' ] ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Alias to 'wc_cp_is_composited_cart_item'.
	 *
	 * @since  5.0.0
	 *
	 * @param  array  $item
	 * @return boolean
	 */
	public function is_composited_cart_item( $item ) {

		$is = false;

		if ( function_exists( 'wc_cp_is_composited_cart_item' ) ) {
			$is = wc_cp_is_composited_cart_item( $item );
		}

		return $is;
	}

	/**
	 * Alias to 'wc_cp_is_composited_order_item'.
	 *
	 * @since  5.0.0
	 *
	 * @param  array     $item
	 * @param  WC_Order  $order
	 * @return boolean
	 */
	public function is_composited_order_item( $item, $order ) {

		$is = false;

		if ( function_exists( 'wc_cp_is_composited_order_item' ) ) {
			$is = wc_cp_is_composited_order_item( $item, $order );
		}

		return $is;
	}
}

WC_PB_Compatibility::core_includes();
