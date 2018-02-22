<?php
/**
 * Copyright: (c) 2017 WooCommerce
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

register_deactivation_hook( __FILE__, 'deactivate_woocommerce_products_compare' );

/**
 * Run on deactivate
 *
 * @since 1.0.0
 * @return bool
 */
function deactivate_woocommerce_products_compare() {

	// set the flag back to false so it can be reflushed on activate
	update_option( 'wc_products_compare_endpoint_set', false );

	flush_rewrite_rules();

	return true;
}

if ( ! class_exists( 'WC_Products_Compare' ) ) :

	define( 'WC_PRODUCTS_COMPARE_VERSION', '1.0.8' );

	/**
	 * main class.
	 *
	 * @package  WC_Products_Compare
	 */
	class WC_Products_Compare {

		/**
		 * init
		 *
		 * @access public
		 * @since 1.0.0
		 * @return bool
		 */
		public function __construct() {

			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			if ( is_woocommerce_active() ) {

				require_once( dirname( __FILE__ ) . '/includes/class-wc-products-compare-frontend.php' );

				add_action( 'widgets_init', array( $this, 'register_widget' ) );

				if ( is_admin() ) {
					require_once( dirname( __FILE__ ) . '/includes/class-wc-products-compare-admin.php' );
				}
			} else {

				add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );

			}

			return true;
		}

		/**
		 * load the plugin text domain for translation.
		 *
		 * @access public
		 * @since 1.0.0
		 * @return bool
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters( 'wc_compare_products_plugin_locale', get_locale(), 'ultimatewoo-pro' );

			load_textdomain( 'wocommerce-products-compare', trailingslashit( WP_LANG_DIR ) . 'woocommerce-products-compare/woocommerce-products-compare' . '-' . $locale . '.mo' );

			load_plugin_textdomain( 'wocommerce-products-compare', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

			return true;
		}

		/**
		 * WooCommerce fallback notice.
		 *
		 * @return string
		 */
		public function woocommerce_missing_notice() {
			echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Compare Products Plugin requires WooCommerce to be installed and active. You can download %s here.', 'ultimatewoo-pro' ), '<a href="http://www.woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</p></div>';
		}

		/**
		 * Registers the widget
		 *
		 * @access public
		 * @since 1.0.0
		 * @return bool
		 */
		public function register_widget() {
			require_once( dirname( __FILE__ ) . '/includes/class-wc-products-compare-widget.php' );

			register_widget( 'WC_Products_Compare_Widget' );

			return true;
		}

		/**
		 * Checks to make sure item is a product
		 *
		 * @access public
		 * @since 1.0.4
		 * @version 1.0.4
		 * @param object $product
		 * @return bool
		 */
		public static function is_product( $product ) {
			if ( $product && 'product' === get_post_type( $product->get_id() ) ) {
				return true;
			}

			return false;
		}
	}

	add_action( 'plugins_loaded', 'woocommerce_products_compare_init', 0 );

	/**
	 * init function
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	function woocommerce_products_compare_init() {
		new WC_Products_Compare();

		return true;
	}

endif;

//1.0.8