<?php
/**
 * Exit if accesses directly
 */
defined( 'ABSPATH' ) or exit;

if ( !class_exists( 'Pie_WCWL_Admin_UI' ) ) {
	/**
	 * The Admin User Interface
	 *
	 * @package WooCommerce Waitlist
	 */
	class Pie_WCWL_Admin_UI {

		/**
		 * Hooks up the functions for the admin UI
		 *
		 * @access public
		 */
		public function __construct() {
			// Init
			add_action( 'plugins_loaded', array( &$this, 'setup_text_strings' ), 15 );
			add_action( 'init', array( $this, 'load_ajax' ) );
			add_action( 'init', array( &$this, 'load_waitlist' ), 20 );
			add_action( 'wc_bulk_stock_before_process_qty', array( &$this, 'load_waitlist_from_product_id' ), 5 );
			add_action( 'admin_notices', array( &$this, 'set_up_hide_out_of_stock_products_nag' ), 15 );
			add_action( 'admin_init', array( &$this, 'ignore_hide_out_of_stock_products_nag' ) );
			// Columns
			add_filter( 'manage_edit-product_columns', array( &$this, 'add_column_headers' ), 11 );
			add_action( 'manage_product_posts_custom_column', array( &$this, 'add_column_content' ), 10, 2 );
			add_filter( 'manage_edit-product_sortable_columns', array( &$this, 'price_column_register_sortable' ) );
			add_action( 'pre_get_posts', array( &$this, 'sort_by_waitlist_column' ), 10, 1 );
			// Archive
			add_action( 'admin_menu', array( $this, 'add_archived_waitlist_page' ), 10 );
			// Scripts and styles
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ), 10 );
		}

		/**
		 * Hook up ajax
		 */
		public function load_ajax() {
			add_action( 'wp_ajax_wcwl_get_products', array( $this, 'get_all_products_ajax' ) );
			add_action( 'wp_ajax_wcwl_update_counts', array( $this, 'update_waitlist_counts_ajax' ) );
		}

		/**
		 * Enqueue admin specific scripts and styles
		 */
		public function enqueue_scripts_and_styles() {
			if ( isset( $_GET['page'] ) && 'wcwl-waitlist-archive' == $_GET['page'] ) {
				wp_enqueue_style( 'wcwl_admin_archive_css', plugins_url() . '/woocommerce-waitlist/includes/css/wcwl_admin_archive.css' );
				wp_enqueue_script( 'wcwl_admin_archive_js', plugins_url() . '/woocommerce-waitlist/includes/js/wcwl_admin_archive.js' );
			}
		}

		/**
		 * Checks if we need to display the 'hide out of stock products' nag and displays if true
		 *
		 * The plugin has a small issue when viewing out-of-stock products in the front end when 'hide out of stock products
		 * from the catalogue' is set to true. This function outputs a nag (if necessary) reminding the user to switch it
		 * off.
		 *
		 * @hooked action admin_notices
		 * @access public
		 * @return void
		 * @since  1.1.0
		 */
		public function set_up_hide_out_of_stock_products_nag() {
			if ( get_option( 'woocommerce_hide_out_of_stock_items' ) == 'no' ) {
				return;
			}
			global $current_user;
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				return;
			}
			$usermeta = get_user_meta( $current_user->ID, WCWL_SLUG, true );
			if ( ! isset( $usermeta['ignore_hide_out_of_stock_products_nag'] ) || ! $usermeta['ignore_hide_out_of_stock_products_nag'] ) {
				echo '<div class="updated"><p>';
				echo apply_filters( 'wcwl_hide_out_of_stock_products_nag_text', sprintf( $this->hide_out_of_stock_products_nag_text, $this->get_inventory_settings_url() ) ) . ' | <a href="' . esc_url( add_query_arg( 'ignore_hide_out_of_stock_products_nag', true ) ) . '">' . apply_filters( 'wcwl_dismiss_nag_text', $this->dismiss_nag_text ) . '</a>';
				echo "</p></div>";
			}
		}

		/**
		 * Function to get the URL of of the inventory settings page. Settings URLs were refactored in 2.1 with no API
		 * provided to retrieve them
		 *
		 * @access public
		 * @return string
		 * @since  1.1.7
		 */
		public function get_inventory_settings_url() {
			global $woocommerce;
			if ( version_compare( $woocommerce->version, '2.1.0' ) < 0 ) {
				return admin_url( 'admin.php?page=woocommerce_settings&tab=inventory' );
			}

			return admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory' );
		}

		/**
		 * Checks for $_GET variable for hiding out of stock products and sets user meta
		 *
		 * @hooked action admin_init
		 * @access public
		 * @return void
		 * @since  1.1.0
		 */
		public function ignore_hide_out_of_stock_products_nag() {
			global $current_user;
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				return;
			}
			if ( isset( $_GET['ignore_hide_out_of_stock_products_nag'] ) && $_GET['ignore_hide_out_of_stock_products_nag'] ) {
				$usermeta                                          = get_user_meta( $current_user->ID, WCWL_SLUG, true );
				$usermeta['ignore_hide_out_of_stock_products_nag'] = true;
				add_user_meta( $current_user->ID, WCWL_SLUG, $usermeta, true );
			}
		}

		/**
		 * Appends the element needed to create a custom admin column to an array
		 *
		 * @hooked filter manage_edit-product_columns
		 *
		 * @param array $defaults the array to append
		 *
		 * @access public
		 * @return array The $defaults array with custom column values appended
		 * @since  1.0
		 */
		public function add_column_headers( $defaults ) {
			$defaults[ WCWL_SLUG . '_count' ] = $this->column_title;

			return $defaults;
		}

		/**
		 * Outputs total waitlist members for a given post ID if $column_name is our custom column
		 *
		 * @hooked action manage_product_posts_custom_column
		 *
		 * @param string $column_name name of the column for which we are outputting data
		 * @param mixed  $post_ID     ID of the post for which we are outputting data
		 *
		 * @access public
		 * @return void
		 * @since  1.0
		 */
		public function add_column_content( $column_name, $post_ID ) {
			if ( WCWL_SLUG . '_count' != $column_name ) {
				return;
			}
			$content = get_post_meta( $post_ID, WCWL_SLUG . '_count', true );
			echo empty( $content ) ? '<span class="na">–</span>' : $content;
		}

		/**
		 * Return number of users on requested waitlist and update meta so it can be quickly retrieved in the future
		 *
		 * @param  int $product product ID
		 *
		 * @access public
		 * @static
		 * @return int
		 */
		public function get_waitlist_count( $product ) {
			$product  = wc_get_product( $product );
			$waitlist = array();
			if ( $product->has_child() ) {
				foreach ( $product->get_children() as $child_id ) {
					$current_waitlist = get_post_meta( $child_id, WCWL_SLUG, true );
					$current_waitlist = is_array( $current_waitlist ) ? $current_waitlist : array();
					$waitlist         = array_merge( $waitlist, $current_waitlist );
				}
			} else {
				$waitlist = get_post_meta( Pie_WCWL_Compatibility::get_product_id( $product ), WCWL_SLUG, true );
			}
			$count = empty( $waitlist ) ? 0 : count( $waitlist );
			update_post_meta( Pie_WCWL_Compatibility::get_product_id( $product ), WCWL_SLUG . '_count', $count );

			return $count;
		}

		/**
		 * Ajax function to return all product IDs
		 */
		public function get_all_products_ajax() {
			$nonce = $_POST['wcwl_get_products'];
			if ( ! wp_verify_nonce( $nonce, 'wcwl-ajax-get-products-nonce' ) ) {
				die( __( 'Nonce Not Verified', 'ultimatewoo-pro' ) );
			}
			$products = get_posts( array( 'post_type' => 'product', 'posts_per_page' => - 1, 'fields' => 'ids' ) );
			echo json_encode( $products );
			die();
		}

		/**
		 * Ajax function to update waitlists for the given products - 10 at a time
		 */
		public function update_waitlist_counts_ajax() {
			$nonce = $_POST['wcwl_update_counts'];
			if ( ! wp_verify_nonce( $nonce, 'wcwl-ajax-update-counts-nonce' ) ) {
				die( __( 'Nonce Not Verified', 'ultimatewoo-pro' ) );
			}
			$products = $_POST['products'];
			foreach ( $products as $product ) {
				$count = $this->get_waitlist_count( $product );
				echo sprintf( __( 'Product %d - count updated to %d | ', 'ultimatewoo-pro' ), $product, $count );
			}
			die();
		}

		/**
		 * Appends our column ID to an array
		 *
		 * @hooked filter manage_edit-product_sortable_columns
		 *
		 * @param array $columns The WP admin sortable columns array.
		 *
		 * @access public
		 * @return array
		 * @since  1.0
		 */
		public function price_column_register_sortable( $columns ) {
			$columns[ WCWL_SLUG . '_count' ] = WCWL_SLUG . '_count';

			return $columns;
		}

		/**
		 * Sort columns by waitlist count when required
		 *
		 * @param $query
		 */
		public function sort_by_waitlist_column( $query ) {
			if ( ! is_admin() ) {
				return;
			}
			$orderby = $query->get( 'orderby' );
			if ( WCWL_SLUG . '_count' == $orderby ) {
				$query->set( 'meta_key', WCWL_SLUG . '_count' );
				$query->set( 'orderby', 'meta_value_num' );
			}
		}

		/**
		 * Sets up the waitlist and calls product tab function if required
		 *
		 * @hooked action init
		 * @access public
		 * @return void
		 * @since  1.0.1
		 */
		public function load_waitlist() {
			if ( ! isset ( $_REQUEST['post'] ) && ! isset ( $_REQUEST['post_ID'] ) ) {
				return;
			}
			$post_id = isset ( $_REQUEST['post'] ) ? $_REQUEST['post'] : $_REQUEST['post_ID'];
			if ( 'product' !== get_post_type( $post_id ) ) {
				return;
			}
			$this->load_waitlist_from_product_id( $post_id );
		}

		/**
		 * Sets up the waitlist from the post id and calls product tab function if required
		 *
		 * @param  int $post_id id of the post
		 *
		 * @access public
		 * @return void
		 */
		public function load_waitlist_from_product_id( $post_id ) {
			$product = wc_get_product( $post_id );
			if ( in_array( $product->get_type(), WooCommerce_Waitlist_Plugin::$product_types ) ) {
				new Pie_WCWL_Custom_Tab( $product );
			}
		}

		/**
		 * Alerts user of moved waitlists at 1.0.4 upgrade
		 *
		 * @access public
		 * @return void
		 */
		public function alert_user_of_moved_waitlists_at_1_0_4_upgrade() {
			$options = get_option( WCWL_SLUG, true );
			if ( isset( $options['moved_waitlists_at_1_0_4_upgrade'] ) && is_array( $options['moved_waitlists_at_1_0_4_upgrade'] ) && ! empty( $options['moved_waitlists_at_1_0_4_upgrade'] ) ) {
				echo '<div class="updated"><p>';
				echo apply_filters( 'wcwl_moved_waitlists_at_1_0_4_upgrade_text', sprintf( $this->moved_waitlists_at_1_0_4_upgrade_text, WCWL_VERSION ) );
				echo '</p><ul>';
				foreach ( $options['moved_waitlists_at_1_0_4_upgrade'] as $waitlist ) {
					echo '<li>';
					printf( esc_html__( 'Waitlist for product %s has been moved to %s (User IDs: %s)', 'ultimatewoo-pro' ), '<strong>' . get_the_title( $waitlist['origin'] ) . '</strong>', '<strong>' . get_the_title( $waitlist['target'] ) . '</strong>', implode( ', ', $waitlist['user_ids'] ) );
					echo ' - <a href="' . esc_url( admin_url( 'post.php?post=' . $waitlist['origin'] . '&action=edit' ) ) . '">' . __( 'Edit Product', 'ultimatewoo-pro' ) . '</a></li>';
				}
				echo '</ul></div>';
			}
		}

		/**
		 * Inserts the options required for the plugin into an array after general_options, or at the end if
		 * general_options not found
		 *
		 * @hooked filter woocommerce general settings
		 *
		 * @param array $general_settings The 'general_settings' element of the $woocommerce_settings array
		 *
		 * @access public
		 * @return array The passed in array with our options spliced / appended
		 * @since  1.0
		 */
		public function add_plugin_options_to_general_settings_array( $general_settings ) {
			$key    = array_search( array( 'type' => 'sectionend', 'id' => 'general_options' ), $general_settings );
			$key    = $key ? $key : count( $general_settings );
			$splice = array(
				array(
					"name" => $this->general_settings_option_group_title,
					"type" => "title",
					"desc" => $this->general_settings_option_group_description,
					"id"   => WCWL_SLUG . "_options",
				),
				array(
					"name"          => $this->general_settings_registration_option_heading,
					"desc"          => $this->general_settings_registration_option_one_label,
					"id"            => WCWL_SLUG . "_enable_guest_registration",
					"std"           => "no",
					"type"          => "checkbox",
					"checkboxgroup" => "start",
				),
				array(
					"type" => "sectionend",
					"id"   => "waitlist_account_options",
				),
			);
			array_splice( $general_settings, $key + 1, 0, $splice );

			return $general_settings;
		}

		/**
		 * Add new admin page for the waitlist archive
		 */
		public function add_archived_waitlist_page() {
			$archive = new Pie_WCWL_Waitlist_Archive();
			add_submenu_page( null, 'Archived Waitlists', 'Archived Waitlists', 'manage_options', 'wcwl-waitlist-archive', array(
				$archive,
				'render_archived_waitlist_page',
			) );
		}

		/**
		 * Sets up the text strings required by the admin UI
		 *
		 * @access public
		 * @return void
		 * @since  1.0
		 */
		public function setup_text_strings() {
			$this->column_title                                   = __( 'Waitlist', 'ultimatewoo-pro' );
			$this->general_settings_option_group_title            = __( "Out-of-stock Waitlist", 'ultimatewoo-pro' );
			$this->general_settings_option_group_description      = __( "The following options control the behaviour of the waitlist for out-of-stock products.", 'ultimatewoo-pro' );
			$this->general_settings_registration_option_heading   = __( "Registration", 'ultimatewoo-pro' );
			$this->general_settings_registration_option_one_label = __( "Enable guest waitlist registration (no account required)", 'ultimatewoo-pro' );
			$this->hide_out_of_stock_products_nag_text            = __( 'The WooCommerce Waitlist extension is active but you have the <em>Hide out of stock items from the catalog</em> option switched on. Please <a href="%s">change your settings</a> for WooCommerce Waitlist to function correctly.', 'ultimatewoo-pro' );
			$this->dismiss_nag_text                               = __( "Stop nagging me", 'ultimatewoo-pro' );
			$this->moved_waitlists_at_1_0_4_upgrade_text          = __( 'In order to support waitlists for product variations in WooCommerce Waitlist version %s, the waitlists for the following variable products have been moved to the corresponding product variations:', 'ultimatewoo-pro' );
			$this->original_variable_product                      = __( 'Original variable product', 'ultimatewoo-pro' );
			$this->new_product_variation                          = __( 'New product variation', 'ultimatewoo-pro' );
			$this->list_of_user_ids                               = __( 'List of user IDs', 'ultimatewoo-pro' );
		}
	}
}
