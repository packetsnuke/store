<?php
/*
	Copyright: © 2009-2017 WooCommerce.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_BULK_STOCK_MANAGEMENT_VERSION', '2.2.9' );

if ( is_woocommerce_active() && ! class_exists( 'WC_Bulk_Stock_Management' ) ) {

	/**
	 * WC_Bulk_Stock_Management class
	 */
	class WC_Bulk_Stock_Management {

		/**
		 * Instance of WC_Stock_Management_List_Table.
		 *
		 * @var WC_Stock_Management_List_Table
		 */
		protected $stock_list_table;

		/**
		 * Constructor
		 */
		public function __construct() {
			// set the screen option
			add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 99, 3 );

			add_filter( 'woocommerce_screen_ids', array( $this, 'add_screen_id' ) );
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
			add_action( 'admin_menu', array( $this, 'register_menu' ) );
			add_action( 'init', array( $this, 'print_stock_report' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}

		/**
		 * Add screen ID to WC
		 * @param array
		 */
		public function add_screen_id( $screen_ids ) {
			$screen_ids[] = 'product_page_woocommerce-bulk-stock-management';

			return $screen_ids;
		}

		/**
		 * Handle localisation
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'woocommerce-bulk-stock-management', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Enqueue styles
		 */
		public function admin_css() {
			wp_enqueue_style( 'woocommerce_stock_management_css', ULTIMATEWOO_MODULES_URL . '/bulk-stock-management/css/admin.css' );
		}

		/**
		 * Enqueue JS.
		 */
		public function admin_enqueue_scripts( $hook ) {
			if ( 'product_page_woocommerce-bulk-stock-management' === $hook ) {
				wp_enqueue_script( 'woocommerce_stock_management_js', ULTIMATEWOO_MODULES_URL . '/bulk-stock-management/js/admin.js', array( 'jquery' ) );
			}
		}

		/**
		 * Add menus to WP admin
		 */
		public function register_menu() {
			// ULTIMATEWOO fix: submenu page slug - "woocommerce-bulk-stock-management"
			$page = add_submenu_page( 'edit.php?post_type=product', __( 'Stock Management', 'ultimatewoo-pro' ), __( 'Stock Management', 'ultimatewoo-pro' ), apply_filters( 'wc_bulk_stock_cap', 'edit_others_products' ), 'woocommerce-bulk-stock-management', array( $this, 'stock_management_page' ) );

			add_action( 'admin_print_styles-' . $page, array( $this, 'admin_css' ) );

			add_action( "load-$page", array( $this, 'add_screen_options' ) );
			add_action( "load-$page", array( $this, 'dispatch_request' ) );
		}

		/**
		 * Adds screen options for this page
		 *
		 * @access public
		 * @since 2.0.2
		 * @version 2.0.2
		 * @return bool
		 */
		public function add_screen_options() {
			$option = 'per_page';

			$args = array(
				'label'   => __( 'Products', 'woocommerce-product-vendors' ),
				'default' => apply_filters( 'wc_bulk_stock_default_items_per_page', 50 ),
				'option'  => 'wc_bulk_stock_products_per_page',
			);

			add_screen_option( $option, $args );

			return true;
		}

		/**
		 * Sets screen options for this page
		 *
		 * @access public
		 * @since 2.0.2
		 * @version 2.0.2
		 * @return mixed
		 */
		public function set_screen_option( $status, $option, $value ) {
			if ( 'wc_bulk_stock_products_per_page' === $option ) {
				return $value;
			}

			return $status;
		}

		/**
		 * Output the stock management page
		 */
		public function stock_management_page() {
			$stock_list_table = $this->get_stock_list_table();
		    $stock_list_table->prepare_items();

		    $this->maybe_show_notice();
		    ?>
		    <div class="wrap">
		        <h2><?php _e( 'Stock Management', 'ultimatewoo-pro' ); ?> <a href="<?php echo wp_nonce_url( add_query_arg( 'print', 'stock_report' ), 'print-stock' ) ?>" class="add-new-h2"><?php _e( 'View stock report', 'ultimatewoo-pro' ); ?></a></h2>
		        <form id="stock-management" method="get">
		            <input type="hidden" name="post_type" value="product" />
		            <input type="hidden" name="page" value="woocommerce-bulk-stock-management" />
		            <?php $stock_list_table->display() ?>
		        </form>
		    </div>
		    <?php
		}

		/**
		 * Display notice if there's updated products.
		 */
		public function maybe_show_notice() {
			$updated_count = ! empty( $_GET['updated'] ) ? absint( $_GET['updated'] ) : 0;
			if ( $updated_count ) {
				/* translators: 1: number of product(s) */
				echo '<div class="updated notice is-dismissible"><p>' . sprintf( _n( '%s product was updated', '%s products were updated', $updated_count, 'ultimatewoo-pro' ), $updated_count ) . '</p></div>';
			}
		}

		/**
		 * Dispatch request made into stock list table page.
		 */
		public function dispatch_request() {
			$stock_list_table = $this->get_stock_list_table();
			$action           = $stock_list_table->current_action();

			if ( $action ) {
				$this->dispatch_action( $action );
			} elseif ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
				wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
				exit;
			}
		}

		/**
		 * Dispatch action on stock list table.
		 *
		 * @param string $action Action's name
		 */
		public function dispatch_action( $action ) {
			check_admin_referer( 'bulk-products' );

			// Make sure bulk action is done via POST. The form wrapper of table
			// list is default to GET, but updated to POST, via JS, when bulk action
			// button is clicked or when user hits enter on stock quantity field.
			if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
				wp_die( __( 'Bulk action must be submitted via POST. Make sure JavaScript is enabled in your browser', 'ultimatewoo-pro' ) );
			}

			$stock_list_table = $this->get_stock_list_table();
			$pagenum          = $stock_list_table->get_pagenum();

			$sendback = remove_query_arg( array( 'updated' ), wp_get_referer() );
			if ( ! $sendback ) {
				$sendback = admin_url( 'edit.php?post_type=product&page=woocommerce-bulk-stock-management' );
			}
			$sendback = add_query_arg( 'paged', $pagenum, $sendback );

			$affected_rows = 0;
			if ( 'save' === $action ) {
				$quantities         = ! empty( $_POST['stock_quantity'] ) ? $_POST['stock_quantity'] : array();
				$current_quantities = ! empty( $_POST['current_stock_quantity'] ) ? $_POST['current_stock_quantity'] : array();

				foreach ( $quantities as $id => $qty ) {
					if ( '' === $qty ) {
						continue;
					}

					$id                 = absint( $id );
					$qty                = wc_stock_amount( $qty );
					$current_qty        = wc_stock_amount( get_post_meta( $id, '_stock', true ) );
					$posted_current_qty = wc_stock_amount( isset( $current_quantities[ $id ] ) ? $current_quantities[ $id ] : $current_qty );

					// Check the qty has not changed since showing the form
					if ( $current_qty === $posted_current_qty ) {

						do_action( 'wc_bulk_stock_before_process_qty', $id );

						// Stock management ON and then update
						update_post_meta( $id, '_manage_stock', 'yes' );
						wc_update_product_stock( $id, $qty );
						$affected_rows++;

						do_action( 'wc_bulk_stock_after_process_qty', $id );
					}
				}
			} else {
				$products = array_map( 'absint', ! empty( $_POST['product'] ) ? $_POST['product'] : array() );
				if ( $products ) {
					foreach ( $products as $id ) {
						$affected_rows++;
						do_action( 'wc_bulk_stock_before_process_action', $action, $id );

						if ( version_compare( WC_VERSION, '3.0.3', '<' ) ) {
							// we need to reset the transient in order to have WC update the latest products with statuses
							wc_delete_product_transients( $id );
						}

						switch ( $action ) {
							case 'in_stock' :
								wc_update_product_stock_status( $id, 'instock' );
							break;
							case 'out_of_stock' :
								wc_update_product_stock_status( $id, 'outofstock' );
							break;
							case 'allow_backorders' :
								update_post_meta( $id, '_backorders', 'yes' );
							break;
							case 'allow_backorders_notify' :
								update_post_meta( $id, '_backorders', 'notify' );
							break;
							case 'do_not_allow_backorders' :
								update_post_meta( $id, '_backorders', 'no' );
							break;
							case 'manage_stock' :
								update_post_meta( $id, '_manage_stock', 'yes' );
							break;
							case 'do_not_manage_stock' :
								update_post_meta( $id, '_manage_stock', 'no' );
								update_post_meta( $id, '_stock', '' );
							break;
							default :
								$affected_rows--;
							break;
						}
						do_action( 'wc_bulk_stock_after_process_action', $action, $id );
					}
				} // End if().
			} // End if().

			if ( $affected_rows > 0 ) {
				$sendback = add_query_arg( array( 'updated' => $affected_rows ), $sendback );
			}
			$sendback = remove_query_arg( array( 'action', 'action2' ), $sendback );

			wp_redirect( $sendback );
			exit;
		}

		/**
		 * Output the stock report table
		 */
		public function print_stock_report() {
			if ( ! empty( $_GET['print'] ) && 'stock_report' == $_GET['print'] ) {
				check_admin_referer( 'print-stock' );
		  		include( apply_filters( 'wc_stock_report_template', plugin_dir_path( __FILE__ ) . 'templates/stock-report.php' ) );
		  		die();
			}
		}

		/**
		 * Get stock list table object.
		 *
		 * @return WC_Stock_Management_List_Table Stock list table object
		 */
		protected function get_stock_list_table() {
			if ( ! $this->stock_list_table ) {
				require_once( 'includes/class-wc-stock-management-list-table.php' );
				$this->stock_list_table = new WC_Stock_Management_List_Table();
			}

			return $this->stock_list_table;
		}
	}

	new WC_Bulk_Stock_Management();
} // End if().

//2.2.9