<?php
/**
 * WC_CP_Display class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    2.2.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Composite front-end filters and functions.
 *
 * @class    WC_CP_Display
 * @version  3.11.0
 */
class WC_CP_Display {

	/**
	 * Keep track of whether the bundled table JS has already been enqueued.
	 * @var boolean
	 */
	private $enqueued_composited_table_item_js = false;

	/**
	 * Workaround for $order arg missing from 'woocommerce_order_item_name' filter - set within the 'woocommerce_order_item_class' filter - @see 'order_item_class()'.
	 * @var false|WC_Order
	 */
	private $order_item_order = false;

	/**
	 * The single instance of the class.
	 * @var WC_CP_Display
	 *
	 * @since 3.7.0
	 */
	protected static $_instance = null;

	/**
	 * Main WC_CP_Display instance.
	 *
	 * Ensures only one instance of WC_CP_Display is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_CP_Display
	 * @since  3.7.0
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
	 * @since 3.7.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'ultimatewoo-pro' ), '3.7.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 3.7.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'ultimatewoo-pro' ), '3.7.0' );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

		// Single product template functions and hooks.
		require_once( 'wc-cp-template-functions.php' );
		require_once( 'wc-cp-template-hooks.php' );

		// Front end scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_action( 'wp_print_footer_scripts', array( $this, 'frontend_js_templates' ), 5 );

		// Change the tr class attributes when displaying bundled items in templates.
		add_filter( 'woocommerce_cart_item_class', array( $this, 'cart_item_class' ), 10, 2 );
		add_filter( 'woocommerce_order_item_class', array( $this, 'order_item_class' ), 10, 3 );

		// Add preamble info to composited products.
		add_filter( 'woocommerce_cart_item_name', array( $this, 'in_cart_component_title' ), 10, 3 );
		add_filter( 'woocommerce_checkout_cart_item_quantity', array( $this, 'cart_item_component_quantity' ), 10, 3 );

		add_filter( 'woocommerce_order_item_name', array( $this, 'order_table_component_title' ), 10, 2 );
		add_filter( 'woocommerce_order_item_quantity_html', array( $this, 'order_table_component_quantity' ), 10, 2 );

		// Filter cart item count.
		add_filter( 'woocommerce_cart_contents_count', array( $this, 'cart_contents_count' ) );

		// Filter cart widget items.
		add_filter( 'woocommerce_before_mini_cart', array( $this, 'add_cart_widget_filters' ) );
		add_filter( 'woocommerce_after_mini_cart', array( $this, 'remove_cart_widget_filters' ) );

		// Wishlists.
		add_filter( 'woocommerce_wishlist_list_item_price', array( $this, 'wishlist_list_item_price' ), 10, 3 );
		add_action( 'woocommerce_wishlist_after_list_item_name', array( $this, 'wishlist_after_list_item_name' ), 10, 2 );

		// Indent composited items in emails.
		add_action( 'woocommerce_email_styles', array( $this, 'email_styles' ) );

		// Display info notice when editing a bundle from the cart. Notices are rendered at priority 10.
		add_action( 'woocommerce_before_single_product', array( $this, 'add_edit_in_cart_notice' ), 0 );

		// Modify price filter query results.
		add_filter( 'woocommerce_product_query_meta_query', array( $this, 'price_filter_query_params' ), 10, 2 );

		// Modify composite products structured data.
		add_filter( 'woocommerce_structured_data_product_offer', array( $this, 'structured_product_data' ), 10, 2 );
	}

	/**
	 * Front-end JS templates.
	 */
	public function frontend_js_templates() {
		if ( wp_script_is( 'wc-add-to-cart-composite' ) ) {
			wc_get_template( 'composited-product/js/selection.php', array(), '', WC_CP()->plugin_path() . '/templates/' );
			wc_get_template( 'single-product/js/composite-navigation.php', array(), '', WC_CP()->plugin_path() . '/templates/' );
			wc_get_template( 'single-product/js/composite-pagination.php', array(), '', WC_CP()->plugin_path() . '/templates/' );
			wc_get_template( 'single-product/js/composite-status.php', array(), '', WC_CP()->plugin_path() . '/templates/' );
			wc_get_template( 'single-product/js/validation-message.php', array(), '', WC_CP()->plugin_path() . '/templates/' );
			wc_get_template( 'single-product/js/summary-element-content.php', array(), '', WC_CP()->plugin_path() . '/templates/' );
			wc_get_template( 'single-product/js/options-dropdown.php', array(), '', WC_CP()->plugin_path() . '/templates/' );
			wc_get_template( 'single-product/js/options-thumbnails.php', array(), '', WC_CP()->plugin_path() . '/templates/' );
			wc_get_template( 'single-product/js/options-radio-buttons.php', array(), '', WC_CP()->plugin_path() . '/templates/' );
			wc_get_template( 'single-product/js/options-pagination.php', array(), '', WC_CP()->plugin_path() . '/templates/' );
		}
	}

	/**
	 * Front-end styles and scripts.
	 */
	public function frontend_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$dependencies = array( 'jquery', 'jquery-blockui', 'underscore', 'backbone', 'wc-add-to-cart-variation' );

		if ( class_exists( 'WC_Bundles' ) ) {
			$dependencies[] = 'wc-add-to-cart-bundle';
		}

		if ( class_exists( 'Product_Addon_Display' ) ) {
			$dependencies[] = 'woocommerce-addons';
		}

		/**
		 * Filter to allow adding custom script dependencies here.
		 *
		 * @param  array  $dependencies
		 */
		$dependencies = apply_filters( 'woocommerce_composite_script_dependencies', $dependencies );

		wp_register_script( 'wc-add-to-cart-composite', WC_CP()->plugin_url() . '/assets/js/add-to-cart-composite' . $suffix . '.js', $dependencies, WC_CP()->version );

		wp_register_style( 'wc-composite-single-css', WC_CP()->plugin_url() . '/assets/css/wc-composite-single.css', false, WC_CP()->version, 'all' );
		wp_style_add_data( 'wc-composite-single-css', 'rtl', 'replace' );

		wp_register_style( 'wc-composite-css', WC_CP()->plugin_url() . '/assets/css/wc-composite-styles.css', false, WC_CP()->version, 'all' );
		wp_style_add_data( 'wc-composite-css', 'rtl', 'replace' );

		wp_enqueue_style( 'wc-composite-css' );

		/**
		 * Filter front-end params.
		 *
		 * @param  array  $params
		 */
		$params = apply_filters( 'woocommerce_composite_front_end_params', array(
			'small_width_threshold'                 => 450,
			'full_width_threshold'                  => 450,
			'legacy_width_threshold'                => 450,
			'scroll_viewport_top_offset'            => 0,
			'i18n_qty_string'                       => _x( ' &times; %s', 'qty string', 'ultimatewoo-pro' ),
			'i18n_price_string'                     => _x( ' &ndash; %s', 'price suffix', 'ultimatewoo-pro' ),
			'i18n_title_string'                     => sprintf( _x( '%1$s%2$s%3$s', 'title quantity price', 'ultimatewoo-pro' ), '%t', '%q', '%p' ),
			'i18n_selected_product_string'          => sprintf( _x( '%1$s%2$s', 'product title followed by details', 'ultimatewoo-pro' ), '%t', '%m' ),
			'i18n_free'                             => __( 'Free!', 'woocommerce' ),
			'i18n_total'                            => __( 'Total', 'ultimatewoo-pro' ) . ': ',
			'i18n_no_options'                       => __( 'No options available&hellip;', 'ultimatewoo-pro' ),
			'i18n_no_selection'                     => __( 'No selection', 'ultimatewoo-pro' ),
			'i18n_no_option'                        => _x( 'No %s', 'dropdown empty-value option: optional selection (%s replaced by component title)','woocommerce-composite-products' ),
			'i18n_select_option'                    => _x( 'Choose %s&hellip;', 'dropdown empty-value option: mandatory selection (%s replaced by component title)', 'ultimatewoo-pro' ),
			'i18n_previous_step'                    => _x( '%s', 'previous step navigation button text', 'ultimatewoo-pro' ),
			'i18n_next_step'                        => _x( '%s', 'next step navigation button text', 'ultimatewoo-pro' ),
			'i18n_final_step'                       => _x( 'Review Configuration', 'final step navigation button text', 'ultimatewoo-pro' ),
			'i18n_reset_selection'                  => __( 'Reset selection', 'ultimatewoo-pro' ),
			'i18n_clear_selection'                  => __( 'Clear selection', 'ultimatewoo-pro' ),
			'i18n_validation_issues_for'            => sprintf( __( '<span class="msg-source">%1$s</span> &rarr; <span class="msg-content">%2$s</span>', 'ultimatewoo-pro' ), '%c', '%e' ),
			'i18n_item_unavailable_text'            => __( 'The selected item cannot be purchased at the moment.', 'ultimatewoo-pro' ),
			'i18n_unavailable_text'                 => __( 'This product cannot be purchased at the moment.', 'ultimatewoo-pro' ),
			'i18n_select_component_option'          => __( 'Please choose an option to continue&hellip;', 'ultimatewoo-pro' ),
			'i18n_select_component_option_for'      => __( 'Please choose an option.', 'ultimatewoo-pro' ),
			'i18n_selected_product_invalid'         => __( 'The chosen option is incompatible with your current configuration.', 'ultimatewoo-pro' ),
			'i18n_selected_product_options_invalid' => __( 'The chosen product options are incompatible with your current configuration.', 'ultimatewoo-pro' ),
			'i18n_select_product_options'           => __( 'Please choose product options to continue&hellip;', 'ultimatewoo-pro' ),
			'i18n_select_product_options_for'       => __( 'Please choose product options.', 'ultimatewoo-pro' ),
			'i18n_summary_empty_component'          => __( 'Configure', 'ultimatewoo-pro' ),
			'i18n_summary_configured_component'     => __( 'Change', 'ultimatewoo-pro' ),
			'i18n_summary_static_component'         => __( 'View', 'ultimatewoo-pro' ),
			'i18n_insufficient_stock'               => sprintf( _x( '<p class="stock out-of-stock insufficient-stock">%1$s &rarr; %2$s</p>', 'insufficient stock - composite template', 'ultimatewoo-pro' ), __( 'Insufficient stock', 'ultimatewoo-pro' ), '%s' ),
			'i18n_comma_sep'                        => sprintf( _x( '%1$s, %2$s', 'comma-separated items', 'ultimatewoo-pro' ), '%s', '%v' ),
			'i18n_reload_threshold_exceeded'        => __( 'Loading &quot;%s&quot; options is taking a bit longer than usual. Would you like to keep trying?', 'ultimatewoo-pro' ),
			'i18n_step_not_accessible'              => __( 'The configuration step you have requested to view (&quot;%s&quot;) is currently not accessible.', 'ultimatewoo-pro' ),
			'i18n_page_of_pages'                    => sprintf( __( 'Page %1$s of %2$s', 'ultimatewoo-pro' ), '%p', '%t' ),
			'i18n_loading_options'                  => __( '%s &rarr; updating options&hellip;', 'ultimatewoo-pro' ),
			'i18n_selection_request_timeout'        => __( 'Your selection could not be updated. If the issue persists, please refresh the page and try again.', 'ultimatewoo-pro' ),
			'currency_symbol'                       => get_woocommerce_currency_symbol(),
			'currency_position'                     => stripslashes( get_option( 'woocommerce_currency_pos' ) ),
			'currency_format_num_decimals'          => absint( get_option( 'woocommerce_price_num_decimals' ) ),
			'currency_format_decimal_sep'           => stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ),
			'currency_format_thousand_sep'          => stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ),
			'currency_format_trim_zeros'            => false === apply_filters( 'woocommerce_price_trim_zeros', false ) ? 'no' : 'yes',
			'script_debug_level'                    => array(), /* 'debug', 'debug:views', 'debug:events', 'debug:models', 'debug:scenarios' */
			'show_quantity_buttons'                 => 'no',
			'relocated_content_reset_on_return'     => 'yes',
			'is_wc_version_gte_2_3'                 => WC_CP_Core_Compatibility::is_wc_version_gte_2_3() ? 'yes' : 'no',
			'is_wc_version_gte_2_4'                 => WC_CP_Core_Compatibility::is_wc_version_gte_2_4() ? 'yes' : 'no',
			'is_wc_version_gte_2_7'                 => WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ? 'yes' : 'no',
			'use_wc_ajax'                           => WC_CP_Core_Compatibility::use_wc_ajax() ? 'yes' : 'no',
			'price_display_suffix'                  => get_option( 'woocommerce_price_display_suffix' ),
			'prices_include_tax'                    => wc_cp_prices_include_tax(),
			'tax_display_shop'                      => wc_cp_tax_display_shop(),
			'calc_taxes'                            => wc_cp_calc_taxes(),
			'photoswipe_enabled'                    => WC_CP_Core_Compatibility::is_wc_version_gte_2_7() && current_theme_supports( 'wc-product-gallery-lightbox' ) ? 'yes' : 'no'
		) );

		wp_localize_script( 'wc-add-to-cart-composite', 'wc_composite_params', $params );
	}

	/**
	 * Show composited product data in the front-end.
	 * Used on first product page load to display content for component defaults.
	 *
	 * @param  mixed                   $product_id
	 * @param  mixed                   $component_id
	 * @param  WC_Product_Composite    $container_id
	 * @return string
	 */
	public function show_composited_product( $product_id, $component_id, $composite ) {

		if ( '0' === $product_id || '' === $product_id ) {

			return '<div class="component_data" data-price="0" data-regular_price="0" data-product_type="none" style="display:none;"></div>';

		} else {

			$component_option = $composite->get_component_option( $component_id, $product_id );
			$product          = $component_option->get_product();

			if ( ! $product || ! $component_option->is_purchasable() ) {

				wc_get_template( 'composited-product/invalid-product.php', array(
					'is_static' => $composite->is_component_static( $component_id )
				), '', WC_CP()->plugin_path() . '/templates/' );
			}
		}

		ob_start();

		WC_CP_Products::add_filters( $component_option );

		/**
 		 * Action 'woocommerce_composite_show_composited_product'.
 		 *
 		 * @param  WC_Product            $product
 		 * @param  string                $component_id
 		 * @param  WC_Product_Composite  $composite
 		 */
		do_action( 'woocommerce_composite_show_composited_product', $product, $component_id, $composite );

		WC_CP_Products::remove_filters();

		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Change the tr class of composite parent/child items in cart templates to allow their styling.
	 *
	 * @param  string  $classname
	 * @param  array   $values
	 * @return string
	 */
	public function cart_item_class( $classname, $values ) {

		if ( wc_cp_is_composited_cart_item( $values ) ) {
			$classname .= ' component_table_item';
		} elseif ( wc_cp_is_composite_container_cart_item( $values ) ) {
			$classname .= ' component_container_table_item';
		}

		return $classname;
	}

	/**
	 * Change the tr class of bundled items in order templates to allow their styling.
	 *
	 * @param  string  $classname
	 * @param  array   $values
	 * @return string
	 */
	public function order_item_class( $classname, $values, $order ) {

		if ( wc_cp_is_composited_order_item( $values, $order ) ) {
			$classname .= ' component_table_item';
			$this->order_item_order = $order;
		} elseif ( wc_cp_is_composite_container_order_item( $values ) ) {
			$classname .= ' component_container_table_item';
		}

		return $classname;
	}

	/**
	 * Sets the 'order_item_order' prop.
	 *
	 * @param  WC_Order  $order
	 */
	public function set_order_item_order( $order ) {
		$this->order_item_order = $order;
	}

	/**
	 * Adds order item title preambles to cart items ( Composite Attribute Descriptions ).
	 *
	 * @param  string   $content
	 * @param  array    $cart_item_values
	 * @param  string   $cart_item_key
	 * @return string
	 */
	public function in_cart_component_title( $content, $cart_item_values, $cart_item_key, $append_qty = false ) {

		if ( wc_cp_is_composite_container_cart_item( $cart_item_values ) ) {

			$product = $cart_item_values[ 'data' ];

			if ( function_exists( 'is_cart' ) && is_cart() && ! did_action( 'woocommerce_before_mini_cart' ) && 'composite' === $product->get_type() ) {

				if ( $product->is_editable_in_cart() ) {
					$content = sprintf( _x( '%1$s<br/><a class="edit_composite_in_cart_text edit_in_cart_text" href="%2$s"><small>%3$s</small></a>', 'edit in cart text', 'ultimatewoo-pro' ), $content, $product->get_permalink( $cart_item_values ), __( '(click to edit)', 'ultimatewoo-pro' ) );
				}
			}

		} elseif ( wc_cp_is_composited_cart_item( $cart_item_values ) ) {

			$component_id = $cart_item_values[ 'composite_item' ];
			$item_title   = $cart_item_values[ 'composite_data' ][ $component_id ][ 'title' ];

			if ( is_checkout() || ( isset( $_REQUEST[ 'action' ] ) && 'woocommerce_update_order_review' === $_REQUEST[ 'action' ] ) ) {
				$append_qty = true;
			}

			if ( $append_qty ) {
				/**
				 * Filter qty html.
				 *
				 * @param  array   $cart_item_values
				 * @param  string  $cart_item_key
				 */
				$item_quantity = apply_filters( 'woocommerce_composited_cart_item_quantity_html', '<strong class="composited-product-quantity">' . sprintf( _x( ' &times; %s', 'qty string', 'ultimatewoo-pro' ), $cart_item_values[ 'quantity' ] ) . '</strong>', $cart_item_values, $cart_item_key );
			} else {
				$item_quantity = '';
			}

			$product_title = $content . $item_quantity;
			$item_data     = array( 'key' => $item_title, 'value' => $product_title );

			$this->enqueue_composited_table_item_js();

			ob_start();

			wc_get_template( 'component-item.php', array( 'component_data' => $item_data ), '', WC_CP()->plugin_path() . '/templates/' );

			$content = apply_filters( 'woocommerce_composited_cart_item_name', ob_get_clean(), $content, $cart_item_values, $cart_item_key, $item_quantity );
		}

		return $content;
	}

	/**
	 * Delete composited item quantity from the review-order.php template. Quantity is inserted into the product name by 'in_cart_component_title'.
	 *
	 * @param  string 	$quantity
	 * @param  array 	$cart_item
	 * @param  string 	$cart_key
	 * @return string
	 */
	public function cart_item_component_quantity( $quantity, $cart_item, $cart_key ) {

		if ( wc_cp_is_composited_cart_item( $cart_item ) ) {
			$quantity = '';
		}

		return $quantity;
	}

	/**
	 * Adds component title preambles to order-details template.
	 *
	 * @param  string  $content
	 * @param  array   $order_item
	 * @return string
	 */
	public function order_table_component_title( $content, $order_item ) {

		if ( false !== $this->order_item_order && wc_cp_is_composited_order_item( $order_item, $this->order_item_order ) ) {

			$component_id    = $order_item[ 'composite_item' ];
			$composite_data  = maybe_unserialize( $order_item[ 'composite_data' ] );
			$component_title = $composite_data[ $component_id ][ 'title' ];

			if ( did_action( 'woocommerce_view_order' ) || did_action( 'woocommerce_thankyou' ) || did_action( 'before_woocommerce_pay' ) ) {

				/**
				 * Filter qty html.
				 *
				 * @param  array  $order_item
				 */
				$item_quantity = apply_filters( 'woocommerce_composited_order_item_quantity_html', '<strong class="composited-product-quantity">' . sprintf( _x( ' &times; %s', 'qty string', 'ultimatewoo-pro' ), $order_item[ 'qty' ] ) . '</strong>', $order_item );

				$this->enqueue_composited_table_item_js();

			} else {

				$item_quantity = '';
			}

			$product_title = $content . $item_quantity;
			$item_data     = array( 'key' => $component_title, 'value' => $product_title );

			ob_start();

			wc_get_template( 'component-item.php', array( 'component_data' => $item_data ), '', WC_CP()->plugin_path() . '/templates/' );

			$content = apply_filters( 'woocommerce_composited_order_item_name', ob_get_clean(), $content, $order_item, $this->order_item_order, $item_quantity );
		}

		return $content;
	}

	/**
	 * Delete composited item quantity from order-details template. Quantity is inserted into the product name by 'order_table_component_title'.
	 *
	 * @param  string  $content
	 * @param  array   $order_item
	 * @return string
	 */
	public function order_table_component_quantity( $content, $order_item ) {

		if ( false !== $this->order_item_order && wc_cp_is_composited_order_item( $order_item, $this->order_item_order ) ) {
			$this->order_item_order = false;
			$content = '';
		}

		return $content;
	}

	/**
	 * Enqeue js that wraps bundled table items in a div in order to apply indentation reliably.
	 *
	 * @return void
	 */
	private function enqueue_composited_table_item_js() {

		if ( ! $this->enqueued_composited_table_item_js ) {
			wc_enqueue_js( "
				var wc_cp_wrap_composited_table_item = function() {
					jQuery( '.component_table_item td.product-name' ).each( function() {
						var el = jQuery( this );
						if ( el.find( '.component-name' ).length === 0 ) {
							el.wrapInner( '<div class=\"component-name component_table_item_indent\"></div>' );
						}
					} );
				};

				jQuery( 'body' ).on( 'updated_checkout updated_cart_totals', function() {
					wc_cp_wrap_composited_table_item();
				} );

				wc_cp_wrap_composited_table_item();
			" );

			$this->enqueued_composited_table_item_js = true;
		}
	}

	/**
	 * Filters the reported number of cart items - counts only composite containers.
	 *
	 * @param  int       $count
	 * @param  WC_Order  $order
	 * @return int
	 */
	function cart_contents_count( $count ) {

		$cart     = WC()->cart->get_cart();
		$subtract = 0;

		foreach ( $cart as $key => $value ) {

			if ( wc_cp_is_composited_cart_item( $value ) ) {
				$subtract += $value[ 'quantity' ];
			}
		}

		return $count - $subtract;
	}

	/**
	 * Add cart widget filters.
	 */
	function add_cart_widget_filters() {

		add_filter( 'woocommerce_widget_cart_item_visible', array( $this, 'cart_widget_item_visible' ), 10, 3 );
		add_filter( 'woocommerce_widget_cart_item_quantity', array( $this, 'cart_widget_item_qty' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_name', array( $this, 'cart_widget_container_item_name' ), 10, 3 );
	}

	/**
	 * Remove cart widget filters.
	 */
	function remove_cart_widget_filters() {

		remove_filter( 'woocommerce_widget_cart_item_visible', array( $this, 'cart_widget_item_visible' ), 10, 3 );
		remove_filter( 'woocommerce_widget_cart_item_quantity', array( $this, 'cart_widget_item_qty' ), 10, 3 );
		remove_filter( 'woocommerce_cart_item_name', array( $this, 'cart_widget_container_item_name' ), 10, 3 );
	}

	/**
	 * Tweak composite container qty.
	 *
	 * @param  bool    $qty
	 * @param  array   $cart_item
	 * @param  string  $cart_item_key
	 * @return bool
	 */
	function cart_widget_item_qty( $qty, $cart_item, $cart_item_key ) {

		if ( wc_cp_is_composite_container_cart_item( $cart_item ) ) {
			$qty = '<span class="quantity">' . apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $cart_item[ 'data' ], $cart_item[ 'quantity' ] ), $cart_item, $cart_item_key ) . '</span>';
		}

		return $qty;
	}

	/**
	 * Do not show composited items.
	 *
	 * @param  bool    $qty
	 * @param  array   $cart_item
	 * @param  string  $cart_item_key
	 * @return bool
	 */
	function cart_widget_item_visible( $show, $cart_item, $cart_item_key ) {

		if ( wc_cp_is_composited_cart_item( $cart_item ) ) {
			$show = false;
		}

		return $show;
	}

	/**
	 * Tweak composite container name.
	 *
	 * @param  bool    $qty
	 * @param  array   $cart_item
	 * @param  string  $cart_item_key
	 * @return bool
	 */
	function cart_widget_container_item_name( $name, $cart_item, $cart_item_key ) {

		if ( wc_cp_is_composite_container_cart_item( $cart_item ) ) {
			$name = WC_CP_Product::get_title_string( $name, $cart_item[ 'quantity' ] );
		}

		return $name;
	}

	/**
	 * Inserts bundle contents after main wishlist bundle item is displayed.
	 *
	 * @param  array  $item
	 * @param  array  $wishlist
	 * @return void
	 */
	public function wishlist_after_list_item_name( $item, $wishlist ) {

		if ( ! empty( $item[ 'composite_data' ] ) ) {
			echo '<dl>';
			foreach ( $item[ 'composite_data' ] as $composited_item => $composited_item_data ) {

				$composited_product = wc_get_product( $composited_item_data[ 'product_id' ] );

				if ( ! $composited_product ) {
					continue;
				}

				echo '<dt class="component_title_meta wishlist_component_title_meta">' . $composited_item_data[ 'title' ] . ':</dt>';
				echo '<dd class="component_option_meta wishlist_component_option_meta">' . $composited_product->get_title() . ' <strong class="component_quantity_meta wishlist_component_quantity_meta product-quantity">&times; ' . $composited_item_data[ 'quantity' ] . '</strong></dd>';

				if ( ! empty ( $composited_item_data[ 'attributes' ] ) ) {

					$attributes = '';

					foreach ( $composited_item_data[ 'attributes' ] as $attribute_name => $attribute_value ) {

						$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $attribute_name ) ) );

						// If this is a term slug, get the term's nice name.
			            if ( taxonomy_exists( $taxonomy ) ) {

			            	$term = get_term_by( 'slug', $attribute_value, $taxonomy );

			            	if ( ! is_wp_error( $term ) && $term && $term->name ) {
			            		$attribute_value = $term->name;
			            	}

			            	$label = wc_attribute_label( $taxonomy );

			            // If this is a custom option slug, get the options name.
			            } else {

							$product_attributes = $composited_product->get_attributes();
							$attribute_value    = apply_filters( 'woocommerce_variation_option_name', $attribute_value );

							if ( isset( $product_attributes[ str_replace( 'attribute_', '', $attribute_name ) ] ) ) {
								$label = wc_attribute_label( $product_attributes[ str_replace( 'attribute_', '', $attribute_name ) ][ 'name' ] );
							} else {
								$label = $attribute_name;
							}
						}

						$attributes = $attributes . $label . ': ' . $attribute_value . ', ';
					}
					echo '<dd class="component_attribute_meta wishlist_component_attribute_meta">' . rtrim( $attributes, ', ' ) . '</dd>';
				}
			}
			echo '</dl>';
			echo '<p class="component_notice wishlist_component_notice">' . __( '*', 'ultimatewoo-pro' ) . '&nbsp;&nbsp;<em>' . __( 'Accurate pricing info available in cart.', 'ultimatewoo-pro' ) . '</em></p>';
		}
	}

	/**
	 * Modifies wishlist bundle item price - the precise sum cannot be displayed reliably unless the item is added to the cart.
	 *
	 * @param  double  $price
	 * @param  array   $item
	 * @param  array   $wishlist
	 * @return string  $price
	 */
	public function wishlist_list_item_price( $price, $item, $wishlist ) {

		if ( ! empty( $item[ 'composite_data' ] ) ) {
			$price = __( '*', 'ultimatewoo-pro' );
		}

		return $price;

	}

	/**
	 * Indent composited items in emails.
	 *
	 * @param  string  $css
	 * @return string
	 */
	function email_styles( $css ) {
		$css = $css . ".component_table_item td:nth-child(1) { padding-left: 2.5em !important; } .component_table_item td { border-top: none; font-size: 0.875em; } .component_table_item td dl.component, .component_table_item td dl.component dt, .component_table_item td dl.component dd { margin: 0; padding: 0; } .component_table_item td dl.component dt { font-weight: bold; } .component_table_item td dl.component dd p { margin-bottom: 0 !important; } #body_content table tr.component_table_item td ul.wc-item-meta { font-size: inherit; }";
		return $css;
	}

	/**
	 * Display info notice when editing a composite from the cart.
	 */
	public function add_edit_in_cart_notice() {

		global $product;

		if ( $product->is_type( 'composite' ) && isset( $_GET[ 'update-composite' ] ) ) {
			$updating_cart_key = wc_clean( $_GET[ 'update-composite' ] );
			if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {
				$notice = sprintf ( __( 'You are currently editing &quot;%1$s&quot;. When finished, click the <strong>Update Cart</strong> button.', 'ultimatewoo-pro' ), $product->get_title() );
				wc_add_notice( $notice, 'notice' );
			}
		}
	}

	/**
	 * Enhance price filter widget meta query to include results based on max '_wc_sw_max_price' meta.
	 *
	 * @param  array     $meta_query
	 * @param  WC_Query  $wc_query
	 * @return array
	 */
	public function price_filter_query_params( $meta_query, $wc_query ) {

		if ( isset( $meta_query[ 'price_filter' ] ) && isset( $meta_query[ 'price_filter' ][ 'price_filter' ] ) && ! isset( $meta_query[ 'price_filter' ][ 'sw_price_filter' ] ) ) {

			$min = isset( $_GET[ 'min_price' ] ) ? floatval( $_GET[ 'min_price' ] ) : 0;
			$max = isset( $_GET[ 'max_price' ] ) ? floatval( $_GET[ 'max_price' ] ) : 9999999999;

			$price_meta_query = $meta_query[ 'price_filter' ];
			$price_meta_query = array(
				'sw_price_filter' => true,
				'price_filter'    => true,
				'relation'        => 'OR',
				$price_meta_query,
				array(
					'relation' => 'AND',
					array(
						'key'     => '_price',
						'compare' => '<=',
						'type'    => 'DECIMAL',
						'value'   => $max
					),
					array(
						'key'     => '_wc_sw_max_price',
						'compare' => '>=',
						'type'    => 'DECIMAL',
						'value'   => $min
					)
				)
			);

			$meta_query[ 'price_filter' ] = $price_meta_query;
		}

		return $meta_query;
	}

	/**
	 * Modify structured data for composite products.
	 *
	 * @param  array       $data
	 * @param  WC_Product  $product
	 * @return array
	 */
	public function structured_product_data( $data, $product ) {

		if ( is_object( $product ) && $product->is_type( 'composite' ) ) {
			$data[ 'price' ] = $product->get_composite_price();
		}

		return $data;
	}
}
