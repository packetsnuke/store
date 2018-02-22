<?php
/**
 * Copyright: © 2009-2017 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Photography' ) ) :

	/**
	 * WooCommerce Photography main class.
	 */
	class WC_Photography {

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		const VERSION = '1.0.10';

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Initialize the plugin.
		 */
		private function __construct() {
			// Load plugin text domain
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Checks with WooCommerce is installed.
			if ( class_exists( 'WooCommerce' ) ) {
				$this->includes();

				if ( is_admin() ) {
					$this->admin_includes();

					add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
				}
			} else {
				add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			}
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Get plugin file.
		 *
		 * @return string
		 */
		public static function get_plugin_file() {
			return __FILE__;
		}

		/**
		 * Get templates path.
		 *
		 * @return string
		 */
		public static function get_templates_path() {
			return plugin_dir_path( __FILE__ ) . 'templates/';
		}

		/**
		 * Get assets url.
		 *
		 * @return string
		 */
		public static function get_assets_url() {
			return plugins_url( 'assets/', __FILE__ );
		}

		/**
		 * Includes.
		 *
		 * @return void
		 */
		private function includes() {
			// Classes.
			include_once( 'includes/class-wc-photography-taxonomies.php' );
			include_once( 'includes/class-wc-product-photography.php' );
			include_once( 'includes/class-wc-photography-frontend.php' );
			include_once( 'includes/class-wc-photography-products.php' );
			include_once( 'includes/class-wc-photography-ajax.php' );
			include_once( 'includes/class-wc-photography-emails.php' );
			include_once( 'includes/class-wc-photography-install.php' );

			// Integration with Products Add-ons.
			if ( class_exists( 'WC_Product_Addons' ) ) {
				include_once( 'includes/class-wc-photography-products-addons.php' );
			}

			// Functions.
			include_once( 'includes/wc-photography-template-functions.php' );
			include_once( 'includes/wc-photography-helpers.php' );
		}

		/**
		 * Admin includes.
		 *
		 * @return void
		 */
		private function admin_includes() {
			include_once( 'includes/admin/class-wc-photography-admin.php' );
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @return void
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'ultimatewoo-pro' );

			load_textdomain( 'woocommerce-photography', trailingslashit( WP_LANG_DIR ) . 'woocommerce-photography/woocommerce-photography-' . $locale . '.mo' );
			load_plugin_textdomain( 'woocommerce-photography', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * WooCommerce fallback notice.
		 *
		 * @return string
		 */
		public function woocommerce_missing_notice() {
			/* translators: 1: WooCommerce href html */
			echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Photography depends on the last version of %s to work!', 'ultimatewoo-pro' ), '<a href="https://woocommerce.com/woocommerce/" target="_blank">' . __( 'WooCommerce', 'ultimatewoo-pro' ) . '</a>' ) . '</p></div>';
		}

		/**
		 * Add relevant links to plugins page.
		 *
		 * @param  array $links
		 *
		 * @return array
		 */
		public function plugin_action_links( $links ) {
			$plugin_links = array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=wc-photography-settings' ) . '">' . __( 'Settings', 'ultimatewoo-pro' ) . '</a>',
				'support'  => '<a href="https://woocommerce.com/my-account/create-a-ticket/">' . __( 'Support', 'ultimatewoo-pro' ) . '</a>',
				'docs'     => '<a href="https://docs.woocommerce.com/documentation/woocommerce-extensions/photography/">' . __( 'Docs', 'ultimatewoo-pro' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Install method.
		 */
		public static function install() {
			include_once( 'includes/class-wc-photography-taxonomies.php' );
			include_once( 'includes/class-wc-photography-install.php' );

			WC_Photography_Install::install();
		}
	}

	register_activation_hook( __FILE__, array( 'WC_Photography', 'install' ) );

	add_action( 'plugins_loaded', array( 'WC_Photography', 'get_instance' ) );

endif;

//1.0.10