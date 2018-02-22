<?php
/**
 * Exit if accesses directly
 */
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'Pie_WCWL_Frontend_UI' ) ) {
	/**
	 * The front end user interface for the plugin
	 *
	 * @package  WooCommerce Waitlist
	 */
	class Pie_WCWL_Frontend_UI {

		/**
		 * WP_User object representing the currently logged in user
		 *
		 * @var object
		 * @access private
		 */
		private $user;
		/**
		 * WC_Product object currently being viewed
		 *
		 * @var object
		 * @access private
		 */
		private $product;
		/**
		 * the string used by this plugin for passing product_ids around in $_REQUEST variables
		 *
		 * @var string
		 * @access private
		 */
		private $product_id_slug;
		/**
		 * woocommerce global, used in this plugin for holding user notifications and error messages
		 *
		 * @var object
		 * @access private
		 */
		private $messages;

		/**
		 * Hooks up the frontend initialisation and any functions that need to run before the 'init' hook
		 *
		 * @todo   hook user waitlist into my account tabs
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'remove_woocommerce_add_to_cart_action_if_not_required' ), 5 );
			add_action( 'wp', array( $this, 'frontend_init' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_scripts' ), 99999 );
			add_shortcode( 'woocommerce_my_waitlist', array( $this, 'current_user_waitlist' ) );
		}

		/**
		 * Enqueue scripts and styles for the frontend if user is on a product page
		 *
		 * @access public
		 * @return void
		 * @since  1.3
		 */
		public function frontend_enqueue_scripts() {
			global $post;
			if ( 'product' !== get_post_type( $post ) ) {
				return;
			}
			wp_enqueue_script( 'wcwl_frontend', ULTIMATEWOO_MODULES_URL . 'waitlist/includes/js/wcwl_frontend.js', array(), '1.0.0', true );
			wp_enqueue_style( 'wcwl_frontend', ULTIMATEWOO_MODULES_URL . 'waitlist/includes/css/wcwl_frontend.css' );
		}

		/**
		 * initialises the frontend UI, hooking up required functions and setting up required objects for each product type
		 *
		 * If we're not viewing a product in the frontend, the whole thing just exits. Otherwise we populate the Class
		 * parameters (including adding our Waitlist object to the WC_Product) and hook up any required functions.
		 *
		 * @hooked action init
		 * @access public
		 * @return void
		 * @since  1.0
		 */
		public function frontend_init() {
			global $post;
			$this->user = is_user_logged_in() ? wp_get_current_user() : false;
			if ( 'product' !== get_post_type( $post ) ) {
				return;
			}
			$this->product = wc_get_product( $post );
			if ( $this->product->get_type() != 'grouped' && ! in_array( $this->product->get_type(), WooCommerce_Waitlist_Plugin::$product_types ) ) {
				return;
			}
			$this->setup_text_strings();
			if ( $this->user_has_altered_waitlist() ) {
				if ( ! $this->user ) {
					$this->handle_waitlist_when_new_user();
				} else {
					$this->toggle_waitlist_action( $_REQUEST[ WCWL_SLUG ] );
				}
			}
			$this->setup_frontend_class();
			$this->output_waitlist_elements();
		}

		/**
		 * Hook up required functions for displaying required waitlist elements
		 */
		public function output_waitlist_elements() {
			if ( $this->product->is_type( 'grouped' ) ) {
				$this->hook_functions_for_grouped_products();
			} elseif ( WooCommerce_Waitlist_Plugin::is_variable( $this->product ) ) {
				$this->hook_functions_for_variable_products();
			} else {
				$this->hook_functions_for_simple_product();
			}
		}

		/**
		 * Setup required variables for the frontend UI
		 *
		 * @access public
		 * @return void
		 * @since  1.3
		 */
		public function setup_frontend_class() {
			global $woocommerce;
			$this->messages        = $woocommerce;
			$this->product_id_slug = WCWL_SLUG . '_product_id';
			add_filter( 'woocommerce_add_to_cart_url', array( $this, 'remove_waitlist_parameters_from_query_string' ) );
			if ( WooCommerce_Waitlist_Plugin::is_simple( $this->product ) ) {
				$this->product->waitlist = new Pie_WCWL_Waitlist( $this->product );
			} else {
				$this->setup_child_waitlists();
			}
		}

		/**
		 * Setup child product waitlists for parent product
		 */
		public function setup_child_waitlists() {
			$children = array();
			foreach ( $this->product->get_children() as $child_id ) {
				$child                 = wc_get_product( $child_id );
				$child->waitlist       = $this->get_waitlist( $child );
				$children[ $child_id ] = $child;
			}
			$this->children = $children;
		}

		/**
		 * Add filters to append the required waitlist control elements to the frontend for simple products
		 *
		 * @access public
		 * @return void
		 * @since  1.3
		 */
		public function hook_functions_for_simple_product() {
			if ( ! $this->product->is_in_stock() ) {
				if ( Pie_WCWL_Compatibility::wc_is_at_least_3_0() ) {
					add_filter( 'woocommerce_get_stock_html', array( $this, 'append_waitlist_control' ), 20 );
				} else {
					add_filter( 'woocommerce_stock_html', array( $this, 'append_waitlist_control' ), 20 );
				}
				add_filter( 'woocommerce_get_availability', array( $this, 'append_waitlist_message_simple' ), 20, 2 );
			}
		}

		/**
		 * Hook functions required to output frontend UI for waitlist on variable products
		 *
		 * @access public
		 * @return void
		 * @since  1.3
		 */
		public function hook_functions_for_variable_products() {
			add_filter( 'woocommerce_get_availability', array( $this, 'append_waitlist_message_variable' ), 20, 2 );
			add_action( 'woocommerce_get_availability', array( $this, 'append_waitlist_control_for_variable_products', ), 21, 2 );
			if ( Pie_WCWL_Compatibility::wc_is_at_least_3_0() ) {
				add_filter( 'woocommerce_get_stock_html', array( $this, 'append_waitlist_control_if_user_unknown' ), 20 );
			} else {
				add_filter( 'woocommerce_stock_html', array( $this, 'append_waitlist_control_if_user_unknown' ), 20 );
			}
		}

		/**
		 * Hook functions required to output frontend UI for waitlist on grouped products
		 *
		 * @access public
		 * @return void
		 * @since  1.3
		 */
		public function hook_functions_for_grouped_products() {
			if ( $this->has_out_of_stock_children() ) {
				if ( Pie_WCWL_Compatibility::wc_is_at_least_2_1() ) {
					if ( Pie_WCWL_Compatibility::wc_is_at_least_3_0() ) {
						add_filter( 'woocommerce_get_stock_html', array( $this, 'append_waitlist_control_for_grouped_child_products', ), 20 );
					} else {
						add_filter( 'woocommerce_stock_html', array( $this, 'append_waitlist_control_for_grouped_child_products', ), 20 );
					}
				} else {
					add_filter( 'woocommerce_get_availability', array( $this, 'outdated_append_waitlist_control_for_children_of_grouped_products', ), 20, 2 );
				}
				add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'output_waitlist_control' ), 20 );
				add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'output_grouped_product_waitlist_message', ) );
				add_action( 'wp_print_styles', array( $this, 'print_grouped_product_style_block' ) );
			}
		}

		/**
		 * Check if grouped product has out of stock child products
		 *
		 * @return bool
		 */
		public function has_out_of_stock_children() {
			foreach ( $this->product->get_children() as $child ) {
				$child = wc_get_product( $child );
				if ( ! $child->is_in_stock() ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Checks to see if request to adjust waitlist is valid for user
		 *
		 * @access public
		 * @return boolean true if valid, false if not
		 * @since  1.3
		 */
		public function user_has_altered_waitlist() {
			if ( isset( $_REQUEST[ WCWL_SLUG ] ) && is_numeric( $_REQUEST[ WCWL_SLUG ] ) && ! isset( $_REQUEST['added-to-cart'] ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * This function modifies the string in place of the 'add to cart' option, adding in an email field when the user
		 * is not logged in. JS has to be added here as enqueuing it does not add it late enough (do not have access to the
		 * email input field yet)
		 *
		 * @param string $string current waitlist string
		 *
		 * @access public
		 * @return string $string modified string
		 * @todo   get access to variation id via woocommerce hook at this point to easily modify information shown rather
		 *         than using strpos to check the item is valid and str_replace to insert the email field in the correct
		 *         place. Move JS to its own file
		 * @since  1.3
		 */
		public function append_waitlist_control_if_user_unknown( $string ) {
			if ( is_user_logged_in() ) {
				return $string;
			}
			if ( false !== strpos( $string, 'woocommerce_waitlist_nonce' ) ) {
				if ( ! WooCommerce_Waitlist_Plugin::users_must_be_logged_in_to_join_waitlist() ) {
					$string = str_replace( '<div>', '</p>' . $this->get_waitlist_email_field() . '<div class="wcwl_control">', $string );
				}
				$string .= '<script type="text/javascript">
   							jQuery(document).ready( function( $ ){
   								var a_href = $("a.woocommerce_waitlist").attr("href");
								$("#wcwl_email").on("input",function(){
									var wcwl_email = $(this).val();
     								$("a.woocommerce_waitlist").prop("href", a_href+"&wcwl_email="+wcwl_email );
    							});
							});
						</script>';
			}

			return $string;
		}

		/**
		 * This function modifies the string outputted after the price field for grouped products, adding in a checkbox
		 * field for each out of stock product.
		 *
		 * At this stage global $product returns the object of the current child product as of woocommerce v2.1
		 * WC v2.2 requires a conditional to check for the 'out of stock' message before displaying our checkboxes
		 * otherwise they display for 'in stock' products also
		 *
		 * @param string $string current waitlist string
		 *
		 * @access public
		 * @return string modified string
		 * @since  1.3
		 */
		public function append_waitlist_control_for_grouped_child_products( $string ) {
			if ( strpos( $string, 'Out of stock' ) === false ) {
				return $string;
			}
			global $product;
			$child_product_waitlist = $this->children[ Pie_WCWL_Compatibility::get_product_id( $product ) ]->waitlist;
			if ( is_user_logged_in() && $child_product_waitlist->user_is_registered( $this->user ) ) {
				$context = 'leave';
				$checked = 'checked';
			} else {
				$context = 'join';
				$checked = '';
			}
			$string = '<p class="stock out-of-stock">' . __( 'Out of stock ', 'ultimatewoo-pro' ) . '<label class="' . WCWL_SLUG . '_label" > - ' . apply_filters( 'wcwl_' . $context . '_waitlist_button_text', $this->join_waitlist_button_text ) . '<input id="wcwl_checked_' . Pie_WCWL_Compatibility::get_product_id( $product ) . '" class="wcwl_checkbox" type="checkbox" name="' . ( 'join' == $context ? $context : $this->product_id_slug . '[]' ) . '" ' . $checked . '/></label></p>';

			return $string;
		}

		/**
		 * Appends the waitlist control HTML for child products of a grouped product to the 'availability' member of an
		 * array Not used since woocommerce 2.1
		 *
		 * @hooked     filter woocommerce_get_availability
		 * @deprecated 2.0.20
		 * @deprecated global product returns correct child product id as of v2.1
		 *
		 * @param array $array 'availability'=>availability string,'class'=>class for availability element
		 * @param       $child_product
		 *
		 * @return array The $array parameter with appropriate button HTML appended to $array['availability']
		 * @internal   param object $this_product WC_Product
		 * @access     public
		 * @since      1.0
		 */
		public function outdated_append_waitlist_control_for_children_of_grouped_products( $array, $child_product ) {
			if ( ! $child_product->is_in_stock() ) {
				$child_product_waitlist = $this->children[ Pie_WCWL_Compatibility::get_product_id( $child_product ) ]->waitlist;
				$context                = 'dummy';
				if ( is_user_logged_in() ) {
					$context = $child_product_waitlist->user_is_registered( $this->user ) ? 'leave' : 'join';
				}
				$array['availability'] .= $this->outdated_get_grouped_product_control( $context, $this->children[ Pie_WCWL_Compatibility::get_product_id( $child_product ) ] );
			}

			return $array;
		}

		/**
		 * Get waitlist control for grouped products
		 * Not used since woocommerce 2.1
		 *
		 * @param mixed $context       Description.
		 * @param mixed $child_product Description.
		 *
		 * @access     public
		 * @return mixed Value.
		 * @since      1.1.0
		 */
		public function outdated_get_grouped_product_control( $context, $child_product ) {
			return $this->get_waitlist_control( $context, 'checkbox', $child_product );
		}

		/**
		 * This function currently returns HTML for a list table of all the products a user is on the waitlist for, with a
		 * link through to the product to remove themselves. I would suggest this can
		 * be refactored and possibly moved to the waitlist Object. The HTML output could be removed also and placed within
		 * filters
		 *
		 * @todo   refactor into waitlist class? Pull out HTML into separate template
		 *
		 * @access public
		 * @return string The HTML for the waitlist table
		 * @since  1.1.3
		 */
		public function current_user_waitlist() {
			if ( ! $this->user ) {
				return '';
			}
			$waitlist_products = $this->get_waitlist_products_by_user_id();
			$content           = '<h2 class="my_account_titles">' . __( 'Your Waitlist', 'ultimatewoo-pro' ) . '</h2>';
			if ( is_array( $waitlist_products ) && ! empty( $waitlist_products ) ) {
				$cached_product = $this->product;
				$content .= '<p>' . __( 'You are currently on the waitlist for the following products.', 'ultimatewoo-pro' ) . '</p><table class="shop_table"><tbody>';
				foreach ( $waitlist_products as $post ) {
					$content = $this->return_html_for_each_product( $post, $content );
				}
				wp_reset_postdata();
				$this->product = $cached_product;
				$content .= '</tbody></table>';
			} else {
				$content .= '<p>' . __( 'You have not yet joined the waitlist for any products.', 'ultimatewoo-pro' ) . '</p>';
			}

			return $content;
		}

		/**
		 * Wrapper for get_posts, returning all products for which the user is on the waitlist. This is currently a
		 * patchfix function to enable a user waitlist summary in the frontend. It really should be factored out in
		 * the future. Possibly change the way we store waitlists? add usermeta?
		 *
		 * @static
		 *
		 * @access public
		 * @return array    array of post objects
		 * @since  1.1.3
		 */
		public static function get_waitlist_products_by_user_id() {
			$args = array(
				'post_type'   => WooCommerce_Waitlist_Plugin::$product_types,
				'numberposts' => '-1',
				'meta_key'    => WCWL_SLUG,
			);

			return array_filter( get_posts( $args ), array( __CLASS__, 'current_user_is_on_waitlist_for_product', ) );
		}

		/**
		 * Patch fix removing closure from function above
		 *
		 * @static
		 * @access public
		 *
		 * @param $product
		 *
		 * @return bool
		 * @since  1.1.4
		 */
		public static function current_user_is_on_waitlist_for_product( $product ) {
			if ( get_post_meta( $product->ID, WCWL_SLUG . '_has_dates', true ) ) {
				return array_key_exists( get_current_user_id(), get_post_meta( $product->ID, WCWL_SLUG, true ) );
			} else {
				return in_array( get_current_user_id(), get_post_meta( $product->ID, WCWL_SLUG, true ) );
			}
		}

		/**
		 * Returns the HTML for the required product in a table row ready for display on frontend
		 *
		 * @param  object $post    required product post object
		 * @param  string $content current HTML string
		 *
		 * @access public
		 * @return string          updated HTML string
		 */
		public function return_html_for_each_product( $post, $content ) {
			$this->product           = wc_get_product( $post->ID );
			$this->product->waitlist = new Pie_WCWL_Waitlist( $this->product );
			$content .= '<tr><td>';
			if ( has_post_thumbnail( $post->ID ) ) {
				$content .= apply_filters( 'wcwl_shortcode_thumbnail', get_the_post_thumbnail( $post->ID, 'shop_thumbnail' ), $post->ID );
			} else {
				$product = wc_get_product( $post->ID );
				$parent  = Pie_WCWL_Compatibility::get_parent_id( $product );
				if ( WooCommerce_Waitlist_Plugin::is_variation( $product ) && has_post_thumbnail( $parent ) ) {
					$content .= apply_filters( 'wcwl_shortcode_thumbnail', get_the_post_thumbnail( $parent, 'shop_thumbnail' ), $parent, $product );
				}
			}
			$title = apply_filters( 'wcwl_shortcode_product_title', esc_html( get_the_title( $post->ID ) ), $post->ID );
			$content .= '</td><td><a href="' . get_permalink( $post->ID ) . '"  >' . $title . '</a></td></tr>';

			return $content;
		}

		/**
		 * Catches the $_REQUEST parameter for waitlist toggling
		 *
		 * This function catches the input from any product type, performs some validation and then
		 * either sets the appropriate response message if invalid or calls the toggle_waitlist function if valid
		 *
		 * @access public
		 *
		 * @since  1.0
		 *
		 * @param $product_id
		 */
		public function toggle_waitlist_action( $product_id ) {
			if ( $this->product->is_type( 'grouped' ) ) {
				if ( isset( $_POST['add-to-cart'] ) ) {
					return;
				}
				$this->handle_waitlist_action_grouped();
				Pie_WCWL_Compatibility::add_notice( apply_filters( 'wcwl_update_waitlist_success_message_text', $this->update_waitlist_success_message_text ) );
			} else {
				$product  = wc_get_product( $product_id );
				$waitlist = $this->get_waitlist( $product );
				if ( $_GET[ WCWL_SLUG . '_action' ] == 'leave' && $waitlist->user_is_registered( $this->user ) && $waitlist->unregister_user( $this->user ) ) {
					Pie_WCWL_Compatibility::add_notice( apply_filters( 'wcwl_leave_waitlist_success_message_text', $this->leave_waitlist_success_message_text ) );
				}
				if ( $_GET[ WCWL_SLUG . '_action' ] == 'join' && ! $waitlist->user_is_registered( $this->user ) && $waitlist->register_user( $this->user ) ) {
					Pie_WCWL_Compatibility::add_notice( apply_filters( 'wcwl_join_waitlist_success_message_text', $this->join_waitlist_success_message_text ) );
				}
			}
		}

		/**
		 * Handle waitlists for grouped products
		 *
		 * @param bool $user
		 */
		private function handle_waitlist_action_grouped( $user = false ) {
			if ( ! $user ) {
				$user = $this->user;
			}
			if ( isset( $_REQUEST['wcwl_join'] ) && ! empty( $_REQUEST['wcwl_join'] ) ) {
				$to_join = explode( ',', $_REQUEST['wcwl_join'] );
				foreach ( $to_join as $product_id ) {
					$waitlist = new Pie_WCWL_Waitlist( wc_get_product( $product_id ) );
					$waitlist->register_user( $user );
				}
			}
			if ( isset( $_REQUEST['wcwl_leave'] ) && ! empty( $_REQUEST['wcwl_leave'] ) ) {
				$to_leave = explode( ',', $_REQUEST['wcwl_leave'] );
				foreach ( $to_leave as $product_id ) {
					$waitlist = new Pie_WCWL_Waitlist( wc_get_product( $product_id ) );
					$waitlist->unregister_user( $user );
				}
			}
		}

		/**
		 * Appends the waitlist button HTML to text string
		 *
		 * @hooked filter woocommerce_stock_html
		 *
		 * @param string $string HTML for Out of Stock message
		 *
		 * @access public
		 * @return string HTML with waitlist button appended if product is out of stock
		 * @since  1.0
		 */
		public function append_waitlist_control( $string = '' ) {
			$string .= '<div class="wcwl_control">';
			if ( ! is_user_logged_in() && WooCommerce_Waitlist_Plugin::is_simple( $this->product ) ) {
				$string = $this->append_waitlist_control_for_logged_out_user( $string );
			} else {
				if ( $this->product->is_type( 'grouped' ) ) {
					$string = $this->append_waitlist_control_for_grouped_products( $string );
				} elseif ( $this->product->waitlist->user_is_registered( $this->user ) ) {
					$string .= $this->get_waitlist_control( 'leave' );
				} else {
					$string .= $this->get_waitlist_control( 'join' );
				}
			}
			$string .= '</div>';

			return $string;
		}

		/**
		 * Appends the email input field and waitlist button HTML to text string for simple products
		 *
		 * @param string $string HTML for Out of Stock message
		 *
		 * @access public
		 * @return string HTML with email field and waitlist button appended
		 * @since  1.3
		 */
		public function append_waitlist_control_for_logged_out_user( $string ) {
			$url = $this->toggle_waitlist_url( false, 'join' );
			$string .= '<form name="wcwl_add_user_form" action="' . esc_url( $url ) . '" method="post">';
			if ( ! WooCommerce_Waitlist_Plugin::users_must_be_logged_in_to_join_waitlist() ) {
				$string .= $this->get_waitlist_email_field();
			}
			$string .= $this->get_waitlist_control( 'join' ) . '</form>';

			return $string;
		}

		/**
		 * Appends the waitlist button HTML to variable products
		 *
		 * @hooked   filter woocommerce_get_availability
		 *
		 * @param array $array 'availability'=>availability string,'class'=>class for availability element
		 * @param       $product
		 *
		 * @return array The $array parameter with appropriate message text appended to $array['availability']
		 * @internal param object $this_product WC_Product
		 * @access   public
		 * @since    1.0
		 */
		public function append_waitlist_control_for_variable_products( $array, $product ) {
			if ( $product && ! $product->is_in_stock() ) {
				$waitlist = $this->get_waitlist( $product );
				if ( isset( $waitlist ) && ! $waitlist->user_is_registered( $this->user ) ) {
					$array['availability'] .= '<div>' . $this->get_variable_product_control( 'join', $product ) . '</div>';
				} else {
					$array['availability'] .= '<div>' . $this->get_variable_product_control( 'leave', $product ) . '</div>';
				}
			}

			return $array;
		}

		/**
		 * Checks if user is logged in and appends the email input field and waitlist button HTML to text string as required
		 *
		 * @param string $string HTML for Out of Stock message
		 *
		 * @access public
		 * @return string HTML with appropriate fields and waitlist button appended
		 * @since  1.3
		 */
		public function append_waitlist_control_for_grouped_products( $string ) {
			if ( ! is_user_logged_in() && ! WooCommerce_Waitlist_Plugin::users_must_be_logged_in_to_join_waitlist() ) {
				$string .= $this->get_waitlist_email_field();
				$string .= $this->get_waitlist_control( 'join', 'grouped' );
			} else {
				$string .= $this->get_waitlist_control( 'update', 'grouped' );
			}

			return $string;
		}

		/**
		 * Outputs the required HTML from 'append_waitlist_control'
		 *
		 * @access public
		 * @return void
		 */
		public function output_waitlist_control() {
			echo $this->append_waitlist_control();
		}

		/**
		 * Checks whether product is in stock and if not, appends the waitlist message of 'join/leave waitlist' to the 'out
		 * of stock' message
		 *
		 * @param array  $array   stock details
		 * @param object $product the current product
		 *
		 * @access public
		 * @return mixed Value.
		 * @since  1.4.12
		 */
		public function append_waitlist_message_simple( $array, $product ) {
			if ( $product && ! $product->is_in_stock() ) {
				$product = $this->product;
				if ( ! is_user_logged_in() || ! $product->waitlist->user_is_registered( $this->user ) ) {
					$array['availability'] .= apply_filters( 'wcwl_join_waitlist_message_text', ' - ' . $this->join_waitlist_message_text );
				} else {
					$array['availability'] .= apply_filters( 'wcwl_leave_waitlist_message_text', ' - ' . $this->leave_waitlist_message_text );
				}
			}

			return $array;
		}

		/**
		 * Checks whether product is in stock and if not, appends the waitlist message of 'join/leave waitlist' to the 'out
		 * of stock' message
		 *
		 * @param array  $array   stock details
		 * @param object $product the current product
		 *
		 * @access public
		 * @return mixed Value.
		 * @since  1.4.12
		 */
		public function append_waitlist_message_variable( $array, $product ) {
			if ( $product && ! $product->is_in_stock() ) {
				$waitlist = $this->get_waitlist( $product );
				if ( isset( $waitlist ) && ( ! is_user_logged_in() || ! $waitlist->user_is_registered( $this->user ) ) ) {
					$array['availability'] .= apply_filters( 'wcwl_join_waitlist_message_text', ' - ' . $this->join_waitlist_message_text );
				} else {
					$array['availability'] .= apply_filters( 'wcwl_leave_waitlist_message_text', ' - ' . $this->leave_waitlist_message_text );
				}
			}

			return $array;
		}

		/**
		 * Return the waitlist for the given product
		 *
		 * @param $product
		 *
		 * @return mixed
		 */
		public function get_waitlist( $product ) {
			if ( isset( $this->children[ Pie_WCWL_Compatibility::get_product_id( $product ) ]->waitlist ) ) {
				return $this->children[ Pie_WCWL_Compatibility::get_product_id( $product ) ]->waitlist;
			}

			return new Pie_WCWL_Waitlist( $product );
		}

		/**
		 * Outputs the appropriate Grouped Product message HTML
		 *
		 * @hooked action woocommerce_after_add_to_cart_form
		 * @access public
		 * @return void
		 * @since  1.0
		 */
		public function output_grouped_product_waitlist_message() {
			$classes = implode( ' ', apply_filters( 'wcwl_grouped_product_message_classes', array(
				'out-of-stock',
				WCWL_SLUG,
			) ) );
			if ( is_user_logged_in() ) {
				$text = apply_filters( 'wcwl_grouped_product_message_text', $this->grouped_product_message_text );
			} else {
				$text = apply_filters( 'wcwl_grouped_product_message_text', $this->no_user_grouped_product_message_text );
			}
			echo apply_filters( 'wcwl_grouped_product_message_html', '<p class="' . esc_attr( $classes ) . '">' . $text . '</p>' );
		}

		/**
		 * Get HTML for variable products
		 *
		 * @param string $context       join/leave depending on whether user is on waitlist or not
		 * @param object $child_product the required product
		 *
		 * @access public
		 * @return mixed Value.
		 * @since  1.1.0
		 */
		public function get_variable_product_control( $context, $child_product ) {
			return $this->get_waitlist_control( $context, 'anchor', $child_product );
		}

		/**
		 * Get HTML for waitlist elements depending on product type
		 *
		 * @param string      $context the context in which the button should be generated (join|leave|dummy)
		 * @param string      $type    optional - the HTML element to generate. anchor|submit. Defaults to anchor
		 * @param bool|object $product WC_Product for which to get button HTML
		 *
		 * @return string HTML for join waitlist button
		 * @access public
		 * @since  1.0
		 */
		public function get_waitlist_control( $context, $type = 'anchor', $product = false ) {
			$text_parameter = $context . '_waitlist_button_text';
			$classes        = implode( ' ', apply_filters( 'wcwl_' . $context . '_waitlist_button_classes', array(
				'button',
				'alt',
				WCWL_SLUG,
				$context,
			) ) );
			$text           = apply_filters( 'wcwl_' . $context . '_waitlist_button_text', $this->$text_parameter );
			$product        = $product ? $product : $this->product;
			switch ( $type ) {
				case 'submit':
					return apply_filters( 'wcwl_' . $context . '_waitlist_submit_button_html', '<input type="submit" class="' . esc_attr( $classes ) . '" id="' . esc_attr( WCWL_SLUG ) . '-product-' . esc_attr( Pie_WCWL_Compatibility::get_product_id( $this->product ) ) . '" name="' . WCWL_SLUG . '" value="' . esc_attr( $text ) . '" />' );
					break;
				case 'checkbox':
					if ( ! isset ( $product->waitlist ) ) {
						$product->waitlist = new Pie_WCWL_Waitlist( $product );
					}
					$checked = $product->waitlist->user_is_registered( $this->user );

					return apply_filters( 'wcwl_' . $context . '_waitlist_submit_button_html', '<label> - ' . apply_filters( 'wcwl_' . $context . '_waitlist_button_text', $this->join_waitlist_button_text ) . '<input type="checkbox" class="wcwl_checkbox" id="wcwl_checked_' . esc_attr( $product ? Pie_WCWL_Compatibility::get_product_id( $product ) : Pie_WCWL_Compatibility::get_product_id( $this->product ) ) . '" name="' . ( 'dummy' == $context ? $context : $this->product_id_slug . '[]' ) . '" value="' . esc_attr( $product ? Pie_WCWL_Compatibility::get_product_id( $product ) : Pie_WCWL_Compatibility::get_product_id( $this->product ) ) . '" ' . ( $checked ? 'checked' : '' ) . ' /></label>' );
					break; //needed?
				case 'grouped':
					return $this->get_waitlist_control_for_grouped_product( $context, $classes, $text, $product );
				default: //anchor - variable and simple products
					if ( $product && $this->product->is_type( 'variable' ) ) {
						return $this->get_waitlist_control_for_variable_product( $context, $classes, $text, $product );
					} else {
						return $this->get_waitlist_control_for_simple_product( $context, $classes, $text );
					}
			}
		}

		/**
		 * Get HTML for variable product waitlist button
		 *
		 * @param string $context the context in which the button should be generated (join|leave)
		 * @param string $classes the classes to apply to the control element
		 * @param string $text    the text to display on the button (update|join|leave waitlist)
		 * @param object $product WC_Product for which to get button HTML
		 *
		 * @access public
		 * @return string HTML for join waitlist button
		 * @since  1.3
		 */
		public function get_waitlist_control_for_variable_product( $context, $classes, $text, $product ) {
			$url = $this->toggle_waitlist_url( Pie_WCWL_Compatibility::get_product_id( $product ), $context );

			return apply_filters( 'wcwl_' . $context . '_waitlist_button_html', '<div class="wcwl_control"><a href="' . esc_url( $url ) . '" class="' . esc_attr( $classes ) . '" data-id="' . Pie_WCWL_Compatibility::get_product_id( $product ) . '" id="wcwl-product-' . esc_attr( Pie_WCWL_Compatibility::get_product_id( $product ) ) . '">' . esc_html( $text ) . '</a></div>' );
		}

		/**
		 * Get HTML for grouped product waitlist button
		 *
		 * @param string $context the context in which the button should be generated (join|leave)
		 * @param string $classes the classes to apply to the control element
		 * @param string $text    the text to display on the button (update|join|leave waitlist)
		 * @param object $product WC_Product for which to get button HTML
		 *
		 * @access public
		 * @return string HTML for join waitlist button
		 * @since  1.3
		 */
		public function get_waitlist_control_for_grouped_product( $context, $classes, $text, $product ) {
			$url = $this->toggle_waitlist_url( false, $context );

			return apply_filters( 'wcwl_' . $context . '_waitlist_submit_button_html', '<div class="wcwl_control"><a href="' . esc_url( $url ) . '" class="' . esc_attr( $classes ) . '" data-id="' . Pie_WCWL_Compatibility::get_product_id( $product ) . '" id="wcwl-product-' . esc_attr( Pie_WCWL_Compatibility::get_product_id( $product ) ) . '">' . esc_html( $text ) . '</a></div>' );
		}

		/**
		 * Get HTML for simple product waitlist button
		 *
		 * @param string $context the context in which the button should be generated (join|leave)
		 * @param string $classes the classes to apply to the control element
		 * @param string $text    the text to display on the button (update|join|leave waitlist)
		 *
		 * @access public
		 * @return string HTML for join waitlist button
		 * @since  1.3
		 */
		public function get_waitlist_control_for_simple_product( $context, $classes, $text ) {
			$url = $this->toggle_waitlist_url( false, $context );

			return apply_filters( 'wcwl_' . $context . '_waitlist_button_html', '<div class="wcwl_control"><a href="' . esc_url( $url ) . '" class="' . esc_attr( $classes ) . '" data-id="' . Pie_WCWL_Compatibility::get_product_id( $this->product ) . '" id="wcwl-product-' . esc_attr( Pie_WCWL_Compatibility::get_product_id( $this->product ) ) . '">' . esc_html( $text ) . '</a></div>' );
		}

		/**
		 * Get HTML for waitlist email
		 *
		 * @access public
		 * @return  string
		 * @since  1.3
		 */
		public function get_waitlist_email_field() {
			return '<div class="wcwl_email_field">
					<label for="wcwl_email">' . $this->email_field_placeholder_text . '</label>
					<input type="email" name="wcwl_email" id="wcwl_email" />
				</div>';
		}

		/**
		 * Get URL to toggle waitlist status
		 *
		 * @param bool   $product_id
		 * @param string $action
		 *
		 * @return string Toggle waitlist URL for $this_product
		 * @internal param object $this_product WC_Product for which to get URL
		 * @access   private
		 * @since    1.0
		 */
		private function toggle_waitlist_url( $product_id = false, $action = '' ) {
			$product_id = $product_id ? $product_id : Pie_WCWL_Compatibility::get_product_id( $this->product );
			$url        = esc_url( add_query_arg( WCWL_SLUG, $product_id, get_permalink( Pie_WCWL_Compatibility::get_product_id( $this->product ) ) ) );
			$url        = add_query_arg( WCWL_SLUG . '_action', $action, $url );
			$url        = esc_url( add_query_arg( WCWL_SLUG . '_nonce', wp_create_nonce( __FILE__ ), $url ) );

			return apply_filters( 'wcwl_toggle_waitlist_url', $url );
		}

		private function toggle_waitlist_url_grouped() {
			$url = esc_url( add_query_arg( WCWL_SLUG . '_nonce', wp_create_nonce( __FILE__ ), $url ) );
		}

		/**
		 * Handles request to join the waitlist if user is not logged in
		 *
		 * Checks which waitlists need to be updated and processes the request, returning appropriate notifications upon
		 * completion
		 *
		 * @return void
		 * @internal param val $mixed $product if multiple products need updating this will be an array, else the product
		 *           object requiring updating
		 * @access   public
		 * @since    1.3
		 */
		public function handle_waitlist_when_new_user() {
			$product           = wc_get_product( $_REQUEST[ WCWL_SLUG ] );
			$product->waitlist = $this->get_waitlist( $product );
			if ( 'yes' == get_option( 'woocommerce_waitlist_registration_needed' ) ) {
				Pie_WCWL_Compatibility::add_notice( apply_filters( 'wcwl_join_waitlist_user_requires_registration_message_text', $this->users_must_register_and_login_message_text ), 'error' );
			} elseif ( ! isset( $_REQUEST['wcwl_email'] ) || ! is_email( $_REQUEST['wcwl_email'] ) ) {
				Pie_WCWL_Compatibility::add_notice( apply_filters( 'wcwl_join_waitlist_invalid_email_message_text', $this->join_waitlist_invalid_email_message_text ), 'error' );
			} elseif ( $product->is_type( 'grouped' ) && empty( $_REQUEST['wcwl_join'] ) ) {
				Pie_WCWL_Compatibility::add_notice( apply_filters( 'wcwl_toggle_waitlist_no_product_message_text', $this->toggle_waitlist_no_product_message_text ), 'error' );
			} else {
				if ( email_exists( $_REQUEST['wcwl_email'] ) ) {
					$current_user = get_user_by( 'email', $_REQUEST['wcwl_email'] );
				} else {
					$current_user = get_user_by( 'id', $product->waitlist->create_new_customer_from_email( $_REQUEST['wcwl_email'] ) );
				}
				if ( $product->is_type( 'grouped' ) ) {
					$this->handle_waitlist_action_grouped( $current_user );
					Pie_WCWL_Compatibility::add_notice( apply_filters( 'wcwl_grouped_product_joined_message_text', $this->grouped_product_joined_message_text ) );
				} else {
					if ( ! $product->waitlist->register_user( $current_user ) ) {
						Pie_WCWL_Compatibility::add_notice( apply_filters( 'wcwl_leave_waitlist_message_text', $this->leave_waitlist_message_text ) );
					} else {
						Pie_WCWL_Compatibility::add_notice( apply_filters( 'wcwl_join_waitlist_success_message_text', $this->join_waitlist_success_message_text ) );
					}
				}
			}
		}

		/**
		 * Removes waitlist parameters from query string
		 *
		 * @access public
		 *
		 * @param  string $query_string current query
		 *
		 * @return string               updated query
		 */
		public function remove_waitlist_parameters_from_query_string( $query_string ) {
			return esc_url( remove_query_arg( array(
				'woocommerce_waitlist',
				'woocommerce_waitlist_nonce',
				'wcwl_email',
				'wcwl_join',
				'wcwl_leave',
			), $query_string ) );
		}

		/**
		 * unhooks the woocommerce 'add to cart' action if not required
		 *
		 * This function only unhooks the action in the condition the add-to-cart $_REQUEST is set and we also have our own
		 * $_REQUEST variable. This is necessary because on grouped products our submit button has to share the same form
		 * element as the add-to-cart button. If they have clicked our button, we want to ignore the fact that the
		 * 'add-to-cart' is present.
		 *
		 * @hooked action init
		 * @access public
		 * @return void
		 * @since  1.0
		 */
		public function remove_woocommerce_add_to_cart_action_if_not_required() {
			if ( empty( $_REQUEST['add-to-cart'] ) || empty( $_REQUEST[ WCWL_SLUG ] ) ) {
				return;
			}
			remove_action( 'init', 'woocommerce_add_to_cart_action' );
		}

		/**
		 * Output style block for class "group_table" on Grouped Product
		 *
		 * @hooked action wp_print_styles
		 * @access public
		 * @return void
		 * @since  1.0
		 */
		public function print_grouped_product_style_block() {
			global $post;
			$product = wc_get_product( $post->ID );
			if ( ! $product->is_type( 'grouped' ) ) {
				return;
			}
			$css = apply_filters( WCWL_SLUG . '_grouped_product_style_block_css', 'p.' . WCWL_SLUG . '{padding-top:20px;clear:both;margin-bottom:10px;}' );
			echo apply_filters( WCWL_SLUG . '_grouped_product_style_block', '<style type="text/css">' . $css . '</style>' );
		}

		/**
		 * Sets up the text strings used by the plugin in the front end
		 *
		 * @hooked action plugins_loaded
		 * @access private
		 * @return void
		 * @since  1.0
		 */
		private function setup_text_strings() {
			$this->join_waitlist_button_text                             = __( 'Join waitlist', 'ultimatewoo-pro' );
			$this->dummy_waitlist_button_text                            = __( 'Join waitlist', 'ultimatewoo-pro' );
			$this->leave_waitlist_button_text                            = __( 'Leave waitlist', 'ultimatewoo-pro' );
			$this->update_waitlist_button_text                           = __( 'Update waitlist', 'ultimatewoo-pro' );
			$this->join_waitlist_message_text                            = __( "Join the waitlist to be emailed when this product becomes available", 'ultimatewoo-pro' );
			$this->leave_waitlist_message_text                           = __( 'You are on the waitlist for this product', 'ultimatewoo-pro' );
			$this->leave_waitlist_success_message_text                   = __( 'You have been removed from the waitlist for this product', 'ultimatewoo-pro' );
			$this->join_waitlist_success_message_text                    = __( 'You have been added to the waitlist for this product', 'ultimatewoo-pro' );
			$this->update_waitlist_success_message_text                  = __( 'You have updated your waitlist for these products', 'ultimatewoo-pro' );
			$this->toggle_waitlist_no_product_message_text               = __( 'You must select at least one product for which to update the waitlist', 'ultimatewoo-pro' );
			$this->toggle_waitlist_ambiguous_error_message_text          = __( 'Something seems to have gone awry. Are you trying to mess with the fabric of the universe?', 'ultimatewoo-pro' );
			$this->join_waitlist_invalid_email_message_text              = __( 'You must provide a valid email address to join the waitlist for this product', 'ultimatewoo-pro' );
			$this->users_must_register_and_login_message_text            = sprintf( __( 'You must register to use the waitlist feature. Please %slogin or create an account%s', 'ultimatewoo-pro' ), '<a href="' . wc_get_page_permalink( 'myaccount' ) . '">', '</a>' );
			$this->grouped_product_message_text                          = __( "Check the box alongside any Out of Stock products and update the waitlist to be emailed when those products become available", 'ultimatewoo-pro' );
			$this->no_user_grouped_product_message_text                  = __( "Check the box alongside any Out of Stock products, enter your email address and join the waitlist to be notified when those products become available", 'ultimatewoo-pro' );
			$this->grouped_product_joined_message_text                   = __( 'You have been added to the selected waitlist/s', 'ultimatewoo-pro' );
			$this->email_field_placeholder_text                          = __( "Email address", 'ultimatewoo-pro' );
		}
	}
}
