<?php
/*
   Author: Neil Pie
   License: GNU General Public License v3.0
   License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
/**
 * Exit if accesses directly
 */
defined( 'ABSPATH' ) or exit;

/**
 * Exit if WooCommerce isn't active
 */
if ( ! is_woocommerce_active() ) {
	return;
}

/**
 * Register Hooks
 */
register_activation_hook( __FILE__, array( 'WooCommerce_Waitlist_Plugin', 'create_empty_waitlists_on_published_products_with_no_existing_waitlist', ) );
register_activation_hook( __FILE__, array( 'WooCommerce_Waitlist_Plugin', 'add_default_options', ) );

if ( ! class_exists( 'WooCommerce_Waitlist_Plugin' ) ) {
	/**
	 * Namespace class for functions non-specific to any object within the plugin
	 *
	 * @package  WooCommerce Waitlist
	 */
	class WooCommerce_Waitlist_Plugin {

		/**
		 * Main plugin class instance
		 *
		 * @var object
		 */
		protected static $instance;
		/**
		 * Admin UI class object
		 *
		 * @var object
		 */
		public static $Pie_WCWL_Admin_UI;
		/**
		 * Frontend UI class object
		 *
		 * @var object
		 */
		public static $Pie_WCWL_Frontend_UI;
		/**
		 * Path to plugin directory
		 *
		 * @var string
		 */
		public static $path;
		/**
		 * Supported product types
		 *
		 * @var array
		 */
		public static $product_types;

		/**
		 * WooCommerce_Waitlist_Plugin constructor
		 */
		public function __construct() {
			require_once 'definitions.php';
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		}

		/**
		 * Initialise the plugin and load the required objects for the use context
		 *
		 * @access public
		 * @return void
		 * @since  1.0.0
		 */
		public function init() {
			if ( ! $this->met_minimum_wc_version() ) {
				return;
			}
			self::$path          = plugin_dir_path( __FILE__ );
			self::$product_types = $this->get_product_types();
			$this->load_classes();
			$this->add_api_handlers();
			$this->load_hooks();
		}

		/**
		 * Check users version of WooCommerce is high enough for our plugin
		 *
		 * @return bool
		 */
		public function met_minimum_wc_version() {
			global $woocommerce;
			if ( version_compare( $woocommerce->version, '2.1', '<' ) ) {
				if ( is_admin() && ! is_ajax() ) {
					add_action( 'admin_notices', array( $this, 'wcwl_woocommerce_version_notice' ) );
				}

				return false;
			}

			return true;
		}

		/**
		 * Display an admin notice notifying users their version of WooCommerce is too low
		 *
		 * @return void
		 */
		function wcwl_woocommerce_version_notice() {
			?>
			<div class="error">
				<p><?php _e( 'WooCommerce Waitlist is active but is not functional. This extension is not available with your version of WooCommerce. Please install and activate WooCommerce version 2.1 or higher.', 'ultimatewoo-pro' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Load required classes
		 */
		public function load_classes() {
			require_once 'classes/class-pie-wcwl-compatibility.php';
			require_once 'classes/class-pie-wcwl-waitlist.php';
			if ( is_admin() ) {
				require_once 'classes/class-pie-wcwl-custom-admin-tab.php';
				require_once 'classes/class-pie-wcwl-admin-ui.php';
				self::$Pie_WCWL_Admin_UI = new Pie_WCWL_Admin_UI();
				require_once 'classes/class-pie-wcwl-waitlist-settings.php';
				require_once 'classes/class-pie-wcwl-waitlist-archive.php';
			} else {
				require_once 'classes/class-pie-wcwl-frontend-ui.php';
				self::$Pie_WCWL_Frontend_UI = new Pie_WCWL_Frontend_UI();
			}
		}

		/**
		 * Add API handlers if mailouts are enabled
		 *
		 * @todo factor out, into waitlist/mailout class
		 */
		public function add_api_handlers() {
			add_action( 'woocommerce_product_set_stock_status', array( $this, 'perform_api_mailout_stock_status', ), 10, 2 );
			add_action( 'woocommerce_product_set_stock', array( $this, 'perform_api_mailout_stock', ), 10, 1 );
			add_action( 'woocommerce_variation_set_stock_status', array( $this, 'perform_api_mailout_stock_status', ), 10, 2 );
			add_action( 'woocommerce_variation_set_stock', array( $this, 'perform_api_mailout_stock', ), 10, 1 );
		}

		/**
		 * All other hooks pertinent to the main plugin class
		 *
		 * @todo remove all unused functionality (moving variable waitlists)
		 * @todo factor out hooks into appropriate classes
		 */
		public function load_hooks() {
			add_action( 'import_end', array( $this, 'create_empty_waitlists_on_published_products_with_no_existing_waitlist', ) );
			add_action( 'admin_init', array( $this, 'version_check' ) );
			add_action( 'init', array( $this, 'localization' ) );
			add_action( 'init', array( $this, 'email_loader' ) );
			add_action( 'woocommerce_reduce_order_stock', array( $this, 'check_order_for_waitlisted_items', ) );
			add_action( 'delete_user', array( $this, 'unregister_user_when_deleted' ) );
		}

		/**
		 * Define the product types we want to load waitlist into
		 *
		 * @todo use wc_get_product to find the product object type that's loaded up
		 * @todo make sure users know it's experimental as any extension can load something here
		 *
		 * @return mixed|void
		 */
		public function get_product_types() {
			return apply_filters( 'woocommerce_waitlist_supported_products', array(
				'simple',
				'variable',
				'product_variation',
				'subscription',
				'variable-subscription',
				'subscription-variation',
			) );
		}

		/**
		 * Update database options if they don't exist so that they are always present
		 */
		public function add_default_options() {
			if ( ! get_option( 'woocommerce_waitlist_archive_on' ) ) {
				update_option( 'woocommerce_waitlist_archive_on', 'yes' );
			}
			if ( ! get_option( 'woocommerce_waitlist_registration_needed' ) ) {
				update_option( 'woocommerce_waitlist_registration_needed', 'no' );
			}
		}

		/**
		 * Perform mailouts when stock status is updated and product is in stock
		 * We only want to do this for variations and simple products NOT variable (parent) products
		 *
		 * @todo factor to waitlist class
		 *
		 * @param $product_id
		 * @param $stock_status
		 */
		public function perform_api_mailout_stock_status( $product_id, $stock_status ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				if ( self::is_variable( $product ) && $product->managing_stock() ) {
					foreach( $product->get_available_variations() as $variation ) {
						$variation = wc_get_product( $variation['variation_id'] );
						if ( 'parent' === $variation->managing_stock() && ( 'instock' == $stock_status || $product->is_in_stock() ) ) {
							$this->do_mailout( $variation );
						}
					}
				} else {
					if ( 'instock' == $stock_status || $product->is_in_stock() ) {
						$this->do_mailout( $product );
					}
				}
			}
		}

		/**
		 * Perform mailouts when stock quantity is updated and product registers as in stock
		 * We only want to do this for variations and simple products NOT variable (parent) products
		 *
		 * @todo factor to waitlist class
		 *
		 * @param $product
		 */
		public function perform_api_mailout_stock( $product ) {
			$product = wc_get_product( $product );
			if ( $product ) {
				if ( self::is_variable( $product ) && $product->managing_stock() ) {
					foreach( $product->get_available_variations() as $variation ) {
						$variation = wc_get_product( $variation['variation_id'] );
						if ( 'parent' === $variation->managing_stock() && $product->is_in_stock() ) {
							$this->do_mailout( $variation );
						}
					}
				} else {
					if ( $product->is_in_stock() ) {
						$this->do_mailout( $product );
					}
				}
			}
		}

		/**
		 * Fire a call to perform the mailout for the given product
		 *
		 * @param $product
		 */
		private function do_mailout( $product ) {
			$product->waitlist = new Pie_WCWL_Waitlist( $product );
			$product->waitlist->waitlist_mailout( Pie_WCWL_Compatibility::get_product_id( $product ) );
		}

		/**
		 * Check to see if product is of type "variable"
		 *
		 * @param $product
		 *
		 * @return bool
		 */
		public static function is_variable( $product ) {
			if ( $product->is_type( 'variable' ) || $product->is_type( 'variable-subscription' ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Check to see if product is of type "variation"
		 *
		 * @param $product
		 *
		 * @return bool
		 */
		public static function is_variation( $product ) {
			if ( $product->is_type( 'variation' ) || $product->is_type( 'subscription_variation' ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Check to see if product is of type "simple"
		 *
		 * @param $product
		 *
		 * @return bool
		 */
		public static function is_simple( $product ) {
			if ( $product->is_type( 'simple' ) || $product->is_type( 'subscription' ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Get the user object, check which products they are on the waitlist for and unregister them from each one when deleted
		 *
		 * @param  int $user_id id of the user that is being deleted
		 *
		 * @todo factor out to waitlist class?
		 *
		 * @access public
		 * @return void
		 * @since  1.3
		 */
		public function unregister_user_when_deleted( $user_id ) {
			$posts = self::return_all_products();
			$user  = get_user_by( 'id', $user_id );
			if ( $user && $posts->have_posts() ) {
				while ( $posts->have_posts() ) {
					$posts->the_post();
					$product = wc_get_product( get_the_ID() );
					if ( $product ) {
						$waitlist = new Pie_WCWL_Waitlist( $product );
						$waitlist->unregister_user( $user );
					}
				}
			}
		}

		/**
		 * Return all product posts
		 *
		 * @static
		 * @access public
		 * @return array all product posts
		 * @since  1.3
		 */
		public static function return_all_products() {
			$args = array(
				'post_type'      => self::$product_types,
				'posts_per_page' => - 1,
			);

			return new WP_Query( $args );
		}

		/**
		 * First stage of two step hookup to add custom email.
		 *
		 * Our email class depends upon the WC_Email class in woocommerce. As such we must defer loading until the
		 * 'plugins_loaded' hook. Otherwise, we could apply this filter directly on init - as it is we get an error
		 * the WC_Email does not exist if we do it this way.
		 *
		 * @hooked plugins_loaded
		 * @static
		 * @access public
		 * @return void
		 */
		public static function email_loader() {
			add_filter( 'woocommerce_email_classes', array( __CLASS__, 'waitlist_mailout_init' ) );
		}

		/**
		 * Appends our Pie_WCWL_Waitlist_Mailout class to the array of WC_Email objects.
		 *
		 * @static
		 *
		 * @param  array $emails the woocommerce array of email objects
		 *
		 * @access public
		 * @return array         the woocommerce array of email objects with our email appended
		 */
		public static function waitlist_mailout_init( $emails ) {
			$emails['Pie_WCWL_Waitlist_Mailout'] = require 'classes/class-pie-wcwl-waitlist-mailout.php';

			return $emails;
		}

		/**
		 * Setup localization for plugin
		 *
		 * @hooked action plugins_loaded
		 * @static
		 * @access public
		 * @return void
		 */
		public static function localization() {
			load_plugin_textdomain( 'woocommerce-waitlist', false, dirname( plugin_basename( __FILE__ ) ) . '/assets/languages/' );
		}

		/**
		 * Check plugin version in DB and call required upgrade functions
		 *
		 * @todo check when these run!
		 * @todo rename upgrade version 1, run on first install
		 * @todo factor out upgrade function
		 *
		 * @hooked action admin_init
		 * @static
		 * @access public
		 * @return void
		 * @since  1.0.1
		 */
		public static function version_check() {
			$options = get_option( WCWL_SLUG );
			if ( ! isset( $options['version'] ) ) {
				self::upgrade_version_1_0();
			}
			if ( version_compare( $options['version'], '1.1.0' ) < 0 ) {
				self::upgrade_version_1_0_4();
			}
			$options            = get_option( WCWL_SLUG );
			$options['version'] = WCWL_VERSION;
			update_option( WCWL_SLUG, $options );
		}

		/**
		 * Individually calls all functions required to upgrade from version 1.0
		 *
		 * @static
		 * @access public
		 * @return void
		 * @since  1.0.1
		 */
		public static function upgrade_version_1_0() {
			self::create_empty_waitlists_on_published_products_with_no_existing_waitlist();
		}

		/**
		 * Individually calls all functions required to upgrade from version 1.0.4
		 *
		 * @static
		 * @access public
		 * @return void
		 * @since  1.1.0
		 */
		public static function upgrade_version_1_0_4() {
			self::move_variable_product_waitlist_entries_to_first_out_of_stock_variation();
		}

		/**
		 * Moves all waitlist entries on variable products to one of their variations
		 *
		 * This function is necessary when upgrading to version 1.1.0 - Prior to 1.1.0, waitlists for variable
		 * products were tracked against the parent product, and it was not possible to register for a waitlist on
		 * a product variation. This missing feature caused problems when one variation was out of stock and another
		 * in stock.
		 *
		 * In version 1.1.0, this feature has been added. Product variations can now hold their own waitlist, and
		 * the variable product parents now hold a waitlist containing all registrations for their child products.
		 * To bridge this upgrade gap, any waitlist registrations for a variable product will be moved to the first
		 * product variation that is out of stock.
		 *
		 * @static
		 * @access public
		 * @return void
		 * @since  1.1.0
		 */
		public static function move_variable_product_waitlist_entries_to_first_out_of_stock_variation() {
			global $wpdb;
			$products                         = $wpdb->get_col( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '" . WCWL_SLUG . "' and meta_value <> 'a:0:{}'" );
			$moved_waitlists_at_1_0_4_upgrade = array();
			foreach ( $products as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( $product->is_type( 'variable' ) ) {
					$waitlist                                        = get_post_meta( $product_id, WCWL_SLUG, true );
					$moved_waitlists_at_1_0_4_upgrade[ $product_id ] = array(
						'origin'   => $product_id,
						'user_ids' => $waitlist,
						'target'   => 0,
					);
					foreach ( $product->get_children() as $variation_id ) {
						$variation = wc_get_product( $variation_id );
						if ( $variation && ! $variation->is_in_stock() ) {
							$variation->waitlist = new Pie_WCWL_Waitlist( $variation );
							foreach ( $waitlist as $user_id ) {
								$variation->waitlist->register_user( get_user_by( 'id', $user_id ) );
							}
							$moved_waitlists_at_1_0_4_upgrade[ $product_id ]['target'] = $variation_id;
							break;
						}
					}
				}
			}
			if ( ! empty( $moved_waitlists_at_1_0_4_upgrade ) ) {
				$options                                     = get_option( WCWL_SLUG );
				$options['moved_waitlists_at_1_0_4_upgrade'] = $moved_waitlists_at_1_0_4_upgrade;
				update_option( WCWL_SLUG, $options );
				add_action( 'admin_notices', self::$Pie_WCWL_Admin_UI->alert_user_of_moved_waitlists_at_1_0_4_upgrade() );
			}
		}

		/**
		 * Adds an empty waitlist to all products with no waitlist
		 *
		 * lightweight function to loop through all products and create an empty waitlist for each. These empty
		 * waitlists fix a bug from 1.0 present when sorting by waitlist in the Admin UI. It is hooked onto
		 * activation and when an upgrade from version 1.0 is detected. It's also hooked onto import_end which
		 * lets us play nicely with WordPress Importer (http://wordpress.org/extend/plugins/wordpress-importer) and
		 * WooCommerce Product CSV Import Suite (http://www.woothemes.com/products/product-csv-import-suite)
		 *
		 * Due to memory issues reported when running this function on stores with many products, it has been
		 * wrapped in an if block to prevent triggering, and can be disabled by changing definitions.php and setting
		 * WCWL_AUTO_WAITLIST_CREATION to false.
		 *
		 * @hooked activation, import_end
		 * @static
		 * @access public
		 * @return void
		 * @since  1.0.1
		 */
		public static function create_empty_waitlists_on_published_products_with_no_existing_waitlist() {
			if ( WCWL_AUTO_WAITLIST_CREATION ) {
				global $wpdb;
				$products = $wpdb->get_col( "SELECT ID FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = 'product'" );
				foreach ( $products as $product_id ) {
					if ( ! is_array( get_post_meta( $product_id, WCWL_SLUG, true ) ) ) {
						update_post_meta( $product_id, WCWL_SLUG, array() );
						update_post_meta( $product_id, WCWL_SLUG . '_count', 0 );
					}
				}
			}
		}

		/**
		 * Check if users must log in to join waitlist
		 *
		 * This function is only returning true because the registration of logged out users onto waitlists is not
		 * currently being supported but may be added in a future version.
		 *
		 *
		 * @static
		 * @access public
		 * @return bool
		 * @since  1.0.1
		 */
		public static function users_must_be_logged_in_to_join_waitlist() {
			if ( get_option( 'woocommerce_waitlist_registration_needed' ) == 'yes' ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if persistent waitlists are disabled
		 *
		 * Filterable function to switch on persistent waitlists. Persistent waitlists will prevent users from being
		 * removed from a waitlist after email is sent, instead being removed when they purchase an item.
		 *
		 * @static
		 * @access public
		 *
		 * @param $product_id
		 *
		 * @return bool
		 * @since  1.1.1
		 */
		public static function persistent_waitlists_are_disabled( $product_id ) {
			return apply_filters( 'wcwl_persistent_waitlists_are_disabled', true, $product_id );
		}

		/**
		 * Check if we want to display empty waitlist meta boxes on in-stock products
		 *
		 * Filterable function to provide control over waitlist meta-box display. Default behaviour is for
		 * the waitlist meta-box to display on:
		 *        products that are out of stock
		 *        products that are in stock and have users on their waitlist
		 *
		 * making this filter return tru will display these meta boxes at all times
		 *
		 * @static
		 * @access public
		 * @return bool
		 * @since  1.3.9
		 */
		public static function display_empty_waitlists_on_in_stock_products() {
			return apply_filters( 'wcwl_display_empty_waitlists_on_in_stock_products', false );
		}

		/**
		 * Check if waitlist updates are disabled
		 *
		 * Filterable function to disable the automatic updates of waitlists. This is to prevent errors on sites
		 * with a massive collection of products
		 *
		 * @static
		 * @access public
		 * @return bool
		 * @since  1.5.5
		 *
		 */
		public static function auto_waitlist_updates_are_disabled() {
			return apply_filters( 'wcwl_disable_auto_waitlist_updates', false );
		}

		/**
		 * Check if automatic mailouts are disabled. If so, no email will be sent to waitlisted users when a product
		 * returns to stock and as such they will remain on the waitlist.
		 *
		 * @static
		 * @access public
		 *
		 * @param $product_id
		 *
		 * @return bool
		 * @since  1.1.8
		 */
		public static function automatic_mailouts_are_disabled( $product_id ) {
			return apply_filters( 'wcwl_automatic_mailouts_are_disabled', false, $product_id );
		}

		/**
		 * Removes user from waitlist on purchase if persistent waitlists are enabled
		 *
		 * @static
		 *
		 * @todo factor to waitlist class
		 *
		 * @param  object $order WC_Order object
		 *
		 * @access public
		 * @return void
		 */
		public static function check_order_for_waitlisted_items( $order ) {
			$user = get_user_by( 'id', $order->get_user_id() );
			foreach ( $order->get_items() as $item ) {
				if ( $item['id'] > 0 ) {
					$_product = $order->get_product_from_item( $item );
					$product = wc_get_product( $_product );
					if ( ! self::persistent_waitlists_are_disabled( Pie_WCWL_Compatibility::get_product_id( $product ) ) ) {
						continue;
					}
					if ( $product ) {
						$waitlist = new Pie_WCWL_Waitlist( $product );
						$waitlist->unregister_user( $user );
					}
				}
			}
		}

		/**
		 * Waitlist main instance, ensures only one instance is loaded
		 *
		 * @since 1.5.0
		 * @return WooCommerce_Waitlist_Plugin
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

	WooCommerce_Waitlist_Plugin::instance();
}

//1.5.7