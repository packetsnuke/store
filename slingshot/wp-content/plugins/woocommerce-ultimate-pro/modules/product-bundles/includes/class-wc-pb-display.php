<?php
/**
 * WC_PB_Display class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    4.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Bundle front-end functions and filters.
 *
 * @class    WC_PB_Display
 * @version  5.4.2
 */
class WC_PB_Display {

	/**
	 * Indicates whether the bundled table item indent JS has already been enqueued.
	 * @var boolean
	 */
	private $enqueued_bundled_table_item_js = false;

	/**
	 * Workaround for $order arg missing from 'woocommerce_order_item_name' filter - set within the 'woocommerce_order_item_class' filter - @see 'order_item_class()'.
	 * @var boolean|WC_Order
	 */
	private $order_item_order = false;

	/**
	 * The single instance of the class.
	 * @var WC_PB_Display
	 *
	 * @since 5.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main WC_PB_Display instance. Ensures only one instance of WC_PB_Display is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_PB_Display
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
	 * Setup hooks and functions.
	 */
	protected function __construct() {

		// Single product template functions and hooks.
		require_once( 'wc-pb-template-functions.php' );
		require_once( 'wc-pb-template-hooks.php' );

		// Front end bundle add-to-cart script.
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ), 100 );

		// Allow ajax add-to-cart to work in WC 2.3/2.4.
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'loop_add_to_cart_link' ), 10, 2 );

		// Add preamble info to bundled products.
		add_filter( 'woocommerce_cart_item_name', array( $this, 'cart_item_title' ), 10, 3 );
		add_filter( 'woocommerce_order_item_name', array( $this, 'order_table_item_title' ), 10, 2 );

		// Change the tr class attributes when displaying bundled items in templates.
		add_filter( 'woocommerce_cart_item_class', array( $this, 'cart_item_class' ), 10, 2 );
		add_filter( 'woocommerce_order_item_class', array( $this, 'order_item_class' ), 10, 3 );

		// Filter cart item count.
		add_filter( 'woocommerce_cart_contents_count',  array( $this, 'cart_contents_count' ) );

		// Filter cart widget items.
		add_filter( 'woocommerce_before_mini_cart', array( $this, 'add_cart_widget_filters' ) );
		add_filter( 'woocommerce_after_mini_cart', array( $this, 'remove_cart_widget_filters' ) );

		// Wishlists compatibility.
		add_filter( 'woocommerce_wishlist_list_item_price', array( $this, 'wishlist_list_item_price' ), 10, 3 );
		add_action( 'woocommerce_wishlist_after_list_item_name', array( $this, 'wishlist_after_list_item_name' ), 10, 2 );

		// Visibility of bundled items.
		add_filter( 'woocommerce_order_item_visible', array( $this, 'order_item_visible' ), 10, 2 );
		add_filter( 'woocommerce_widget_cart_item_visible', array( $this, 'cart_item_visible' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_visible', array( $this, 'cart_item_visible' ), 10, 3 );
		add_filter( 'woocommerce_checkout_cart_item_visible', array( $this, 'cart_item_visible' ), 10, 3 );

		// Indent bundled items in emails.
		add_action( 'woocommerce_email_styles', array( $this, 'email_styles' ) );

		// Display info notice when editing a bundle from the cart. Notices are rendered at priority 10.
		add_action( 'woocommerce_before_single_product', array( $this, 'add_edit_in_cart_notice' ), 0 );

		// Modify price filter query results.
		add_filter( 'woocommerce_product_query_meta_query', array( $this, 'price_filter_query_params' ), 10, 2 );

		// Modify bundles structured data.
		add_filter( 'woocommerce_structured_data_product_offer', array( $this, 'structured_product_data' ), 10, 2 );
	}

	/**
	 * Frontend scripts.
	 *
	 * @return void
	 */
	public function frontend_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'wc-add-to-cart-bundle', WC_PB()->plugin_url() . '/assets/js/add-to-cart-bundle' . $suffix . '.js', array( 'jquery', 'wc-add-to-cart-variation' ), WC_PB()->version, true );

		wp_register_style( 'wc-bundle-css', WC_PB()->plugin_url() . '/assets/css/wc-pb-single-product.css', false, WC_PB()->version );
		wp_style_add_data( 'wc-bundle-css', 'rtl', 'replace' );

		wp_register_style( 'wc-bundle-style', WC_PB()->plugin_url() . '/assets/css/wc-pb-frontend.css', false, WC_PB()->version );
		wp_style_add_data( 'wc-bundle-style', 'rtl', 'replace' );

		wp_enqueue_style( 'wc-bundle-style' );

		/**
		 * 'woocommerce_bundle_front_end_params' filter.
		 *
		 * @param  array
		 */
		$params = apply_filters( 'woocommerce_bundle_front_end_params', array(
			'i18n_free'                           => __( 'Free!', 'woocommerce' ),
			'i18n_total'                          => __( 'Total', 'ultimatewoo-pro' ) . ': ',
			'i18n_subtotal'                       => __( 'Subtotal', 'ultimatewoo-pro' ) . ': ',
			'i18n_partially_out_of_stock'         => __( 'Insufficient stock', 'ultimatewoo-pro' ),
			'i18n_partially_on_backorder'         => __( 'Available on backorder', 'woocommerce' ),
			'i18n_select_options'                 => __( 'To continue, please choose product options&hellip;', 'ultimatewoo-pro' ),
			'i18n_qty_string'                     => _x( ' &times; %s', 'qty string', 'ultimatewoo-pro' ),
			'i18n_optional_string'                => _x( ' &mdash; %s', 'suffix', 'ultimatewoo-pro' ),
			'i18n_optional'                       => __( 'optional', 'ultimatewoo-pro' ),
			'i18n_contents'                       => __( 'Contents', 'ultimatewoo-pro' ),
			'i18n_title_meta_string'              => sprintf( _x( '%1$s &ndash; %2$s', 'title followed by meta', 'ultimatewoo-pro' ), '%t', '%m' ),
			'i18n_title_string'                   => sprintf( _x( '%1$s%2$s%3$s%4$s', 'title, quantity, price, suffix', 'ultimatewoo-pro' ), '<span class="item_title">%t</span>', '<span class="item_qty">%q</span>', '', '<span class="item_suffix">%o</span>' ),
			'i18n_unavailable_text'               => __( 'This product is currently unavailable.', 'ultimatewoo-pro' ),
			'currency_symbol'                     => get_woocommerce_currency_symbol(),
			'currency_position'                   => esc_attr( stripslashes( get_option( 'woocommerce_currency_pos' ) ) ),
			'currency_format_num_decimals'        => wc_get_price_decimals(),
			'currency_format_decimal_sep'         => esc_attr( stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ) ),
			'currency_format_thousand_sep'        => esc_attr( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ) ),
			'currency_format_trim_zeros'          => false === apply_filters( 'woocommerce_price_trim_zeros', false ) ? 'no' : 'yes',
			'price_display_suffix'                => esc_attr( get_option( 'woocommerce_price_display_suffix' ) ),
			'prices_include_tax'                  => esc_attr( get_option( 'woocommerce_prices_include_tax' ) ),
			'tax_display_shop'                    => esc_attr( get_option( 'woocommerce_tax_display_shop' ) ),
			'calc_taxes'                          => esc_attr( get_option( 'woocommerce_calc_taxes' ) ),
			'photoswipe_enabled'                  => WC_PB_Core_Compatibility::is_wc_version_gte_2_7() && current_theme_supports( 'wc-product-gallery-lightbox' ) ? 'yes' : 'no'
		) );

		wp_localize_script( 'wc-add-to-cart-bundle', 'wc_bundle_params', $params );
	}

	/**
	 * Allows ajax add-to-cart to work in WC 2.3/2.4.
	 * Fixes QuickView support when ajax add-to-cart is active and QuickView operates without a separate button.
	 *
	 * @param  string      $link
	 * @param  WC_Product  $product
	 * @return string
	 */
	public function loop_add_to_cart_link( $link, $product ) {

		if ( $product->is_type( 'bundle' ) ) {

			if ( $product->is_in_stock() && ! $product->requires_input() ) {
				// In WC 2.5, this is controlled by adding 'ajax_add_to_cart' support in the product ->supports property.
				if ( ! WC_PB_Core_Compatibility::is_wc_version_gte_2_5() ) {
					$link = str_replace( 'product_type_bundle', 'product_type_bundle product_type_simple', $link );
				}
			} else {
				$link = str_replace( 'product_type_bundle', 'product_type_bundle product_type_bundle_input_required', $link );
			}
		}

		return $link;
	}

	/**
	 * Override bundled item title in cart/checkout templates.
	 *
	 * @param  string  $content
	 * @param  array   $cart_item_values
	 * @param  string  $cart_item_key
	 * @return string
	 */
	public function cart_item_title( $content, $cart_item_values, $cart_item_key ) {

		if ( wc_pb_is_bundled_cart_item( $cart_item_values ) ) {

			$this->enqueue_bundled_table_item_js();

		} elseif ( wc_pb_is_bundle_container_cart_item( $cart_item_values ) ) {

			$product = $cart_item_values[ 'data' ];

			if ( function_exists( 'is_cart' ) && is_cart() && ! did_action( 'woocommerce_before_mini_cart' ) && 'bundle' === $product->get_type() ) {
				if ( $product->is_editable_in_cart( $cart_item_values ) ) {
					$content = sprintf( _x( '%1$s<br/><a class="edit_bundle_in_cart_text edit_in_cart_text" href="%2$s"><small>%3$s</small></a>', 'edit in cart text', 'ultimatewoo-pro' ), $content, $product->get_permalink( $cart_item_values ), __( '(click to edit)', 'ultimatewoo-pro' ) );
				}
			}
		}

		return $content;
	}

	/**
	 * Override bundled item title in order-details template.
	 *
	 * @param  string  $content
	 * @param  array   $order_item
	 * @return string
	 */
	public function order_table_item_title( $content, $order_item ) {

		if ( false !== $this->order_item_order && wc_pb_is_bundled_order_item( $order_item, $this->order_item_order ) ) {

			$this->order_item_order = false;

			if ( did_action( 'woocommerce_view_order' ) || did_action( 'woocommerce_thankyou' ) || did_action( 'before_woocommerce_pay' ) ) {
				$this->enqueue_bundled_table_item_js();
			}
		}

		return $content;
	}

	/**
	 * Enqeue js that wraps bundled table items in a div in order to apply indentation reliably.
	 *
	 * @return void
	 */
	private function enqueue_bundled_table_item_js() {

		if ( ! $this->enqueued_bundled_table_item_js ) {
			wc_enqueue_js( "
				var wc_pb_wrap_bundled_table_item = function() {
					jQuery( '.bundled_table_item td.product-name' ).each( function() {
						var el = jQuery( this );
						if ( el.find( '.bundled-product-name' ).length === 0 ) {
							el.wrapInner( '<div class=\"bundled-product-name bundled_table_item_indent\"></div>' );
						}
					} );
				};

				jQuery( 'body' ).on( 'updated_checkout updated_cart_totals', function() {
					wc_pb_wrap_bundled_table_item();
				} );

				wc_pb_wrap_bundled_table_item();
			" );

			$this->enqueued_bundled_table_item_js = true;
		}
	}

	/**
	 * Change the tr class of bundled items in cart templates to allow their styling.
	 *
	 * @param  string  $classname
	 * @param  array   $values
	 * @return string
	 */
	public function cart_item_class( $classname, $values ) {

		if ( wc_pb_is_bundled_cart_item( $values ) ) {
			$classname .= ' bundled_table_item';
		} elseif ( wc_pb_is_bundle_container_cart_item( $values ) ) {
			$classname .= ' bundle_table_item';
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

		if ( wc_pb_get_bundled_order_item_container( $values, $order ) ) {
			$classname .= ' bundled_table_item';
			$this->order_item_order = $order;
		} elseif ( wc_pb_is_bundle_container_order_item( $values ) ) {
			$classname .= ' bundle_table_item';
		}

		return $classname;
	}

	/**
	 * Filters the reported number of cart items: bundled items are not counted.
	 *
	 * @param  int  $count
	 * @return int
	 */
	public function cart_contents_count( $count ) {

		$cart     = WC()->cart->get_cart();
		$subtract = 0;

		foreach ( $cart as $key => $value ) {
			if ( wc_pb_is_bundled_cart_item( $value ) ) {
				$subtract += $value[ 'quantity' ];
			}
		}

		return $count - $subtract;
	}

	/**
	 * Add cart widget filters.
	 *
	 * @return void
	 */
	public function add_cart_widget_filters() {

		add_filter( 'woocommerce_widget_cart_item_visible', array( $this, 'cart_widget_item_visible' ), 10, 3 );
		add_filter( 'woocommerce_widget_cart_item_quantity', array( $this, 'cart_widget_item_qty' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_name', array( $this, 'cart_widget_container_item_name' ), 10, 3 );
	}

	/**
	 * Remove cart widget filters.
	 *
	 * @return void
	 */
	public function remove_cart_widget_filters() {

		remove_filter( 'woocommerce_widget_cart_item_visible', array( $this, 'cart_widget_item_visible' ), 10, 3 );
		remove_filter( 'woocommerce_widget_cart_item_quantity', array( $this, 'cart_widget_item_qty' ), 10, 3 );
		remove_filter( 'woocommerce_cart_item_name', array( $this, 'cart_widget_container_item_name' ), 10, 3 );
	}

	/**
	 * Do not show bundled items in mini cart.
	 *
	 * @param  boolean  $show
	 * @param  array    $cart_item
	 * @param  string   $cart_item_key
	 * @return boolean
	 */
	public function cart_widget_item_visible( $show, $cart_item, $cart_item_key ) {

		if ( wc_pb_is_bundled_cart_item( $cart_item ) ) {
			$show = false;
		}

		return $show;
	}

	/**
	 * Tweak bundle container qty.
	 *
	 * @param  bool    $qty
	 * @param  array   $cart_item
	 * @param  string  $cart_item_key
	 * @return bool
	 */
	public function cart_widget_item_qty( $qty, $cart_item, $cart_item_key ) {

		global $woocommerce_composite_products;

		if ( wc_pb_is_bundle_container_cart_item( $cart_item ) ) {
			$qty = '<span class="quantity">' . apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $cart_item[ 'data' ], $cart_item[ 'quantity' ] ), $cart_item, $cart_item_key ) . '</span>';
		}

		return $qty;
	}

	/**
	 * Tweak bundle container name.
	 *
	 * @param  bool    $show
	 * @param  array   $cart_item
	 * @param  string  $cart_item_key
	 * @return bool
	 */
	public function cart_widget_container_item_name( $name, $cart_item, $cart_item_key ) {

		if ( wc_pb_is_bundle_container_cart_item( $cart_item ) ) {
			$name = WC_PB_Helpers::format_product_shop_title( $name, $cart_item[ 'quantity' ] );
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

		if ( $item[ 'data' ]->is_type( 'bundle' ) && ! empty( $item[ 'stamp' ] ) ) {

			echo '<dl>';

			foreach ( $item[ 'stamp' ] as $bundled_item_id => $bundled_item_data ) {

				$bundled_product = wc_get_product( $bundled_item_data[ 'product_id' ] );

				if ( empty( $bundled_product ) ) {
					continue;
				}

				echo '<dt class="bundled_title_meta wishlist_bundled_title_meta">' . $bundled_product->get_title() . ' <strong class="bundled_quantity_meta wishlist_bundled_quantity_meta product-quantity">&times; ' . $bundled_item_data[ 'quantity' ] . '</strong></dt>';

				if ( ! empty ( $bundled_item_data[ 'attributes' ] ) ) {

					$attributes = '';

					foreach ( $bundled_item_data[ 'attributes' ] as $attribute_name => $attribute_value ) {

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

							$attribute_value    = apply_filters( 'woocommerce_variation_option_name', $attribute_value );
							$product_attributes = $bundled_product->get_attributes();

							if ( isset( $product_attributes[ str_replace( 'attribute_', '', $attribute_name ) ] ) ) {
								$label = wc_attribute_label( $product_attributes[ str_replace( 'attribute_', '', $attribute_name ) ][ 'name' ] );
							} else {
								$label = $attribute_name;
							}
						}

						$attributes = $attributes . $label . ': ' . $attribute_value . ', ';
					}
					echo '<dd class="bundled_attribute_meta wishlist_bundled_attribute_meta">' . rtrim( $attributes, ', ' ) . '</dd>';
				}
			}
			echo '</dl>';
			echo '<p class="bundled_notice wishlist_component_notice">' . __( '*', 'ultimatewoo-pro' ) . '&nbsp;&nbsp;<em>' . __( 'For accurate pricing details, please add the product to your cart.', 'ultimatewoo-pro' ) . '</em></p>';
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

		if ( $item[ 'data' ]->is_type( 'bundle' ) && ! empty( $item[ 'stamp' ] ) )
			$price = __( '*', 'ultimatewoo-pro' );

		return $price;
	}

	/**
	 * Visibility of bundled item in orders.
	 *
	 * @param  boolean  $visible
	 * @param  array    order_item
	 * @return boolean
	 */
	public function order_item_visible( $visible, $order_item ) {

		if ( wc_pb_maybe_is_bundled_order_item( $order_item ) && ! empty( $order_item[ 'bundled_item_hidden' ] ) ) {
			$visible = false;
		}

		return $visible;
	}

	/**
	 * Visibility of bundled item in cart.
	 *
	 * @param  boolean  $visible
	 * @param  array    $cart_item
	 * @param  string   $cart_item_key
	 * @return boolean
	 */
	public function cart_item_visible( $visible, $cart_item, $cart_item_key ) {

		if ( $bundle_container_item = wc_pb_get_bundled_cart_item_container( $cart_item ) ) {

			$bundle          = $bundle_container_item[ 'data' ];
			$bundled_item_id = $cart_item[ 'bundled_item_id' ];

			if ( $bundled_item = $bundle->get_bundled_item( $bundled_item_id ) ) {
				$visible = $bundled_item->is_visible( 'cart' );
			}
		}

		return $visible;
	}

	/**
	 * Indent bundled items in emails.
	 *
	 * @param  string  $css
	 * @return string
	 */
	public function email_styles( $css ) {
		$css = $css . ".bundled_table_item td:nth-child(1) { padding-left: 2.5em !important; } .bundled_table_item td { border-top: none; font-size: 0.875em; } #body_content table tr.bundled_table_item td ul.wc-item-meta { font-size: inherit; }";
		return $css;
	}

	/**
	 * Display info notice when editing a bundle from the cart.
	 */
	public function add_edit_in_cart_notice() {

		global $product;

		if ( $product->is_type( 'bundle' ) && isset( $_GET[ 'update-bundle' ] ) ) {
			$updating_cart_key = wc_clean( $_GET[ 'update-bundle' ] );
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
	 * Modify structured data for bundle-type products.
	 *
	 * @param  array       $data
	 * @param  WC_Product  $product
	 * @return array
	 */
	public function structured_product_data( $data, $product ) {

		if ( is_object( $product ) && $product->is_type( 'bundle' ) ) {
			$data[ 'price' ] = $product->get_bundle_price();
		}

		return $data;
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	public function woo_bundles_loop_add_to_cart_link( $link, $product ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::loop_add_to_cart_link()' );
		return $this->loop_add_to_cart_link( $link, $product );
	}
	public function woo_bundles_in_cart_item_title( $content, $cart_item_values, $cart_item_key ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::cart_item_title()' );
		return $this->cart_item_title( $content, $cart_item_values, $cart_item_key );
	}
	public function woo_bundles_order_table_item_title( $content, $order_item ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::order_table_item_title()' );
		return $this->order_table_item_title( $content, $order_item );
	}
	public function woo_bundles_table_item_class( $classname, $values ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::table_item_class()' );
		return false !== strpos( $classname, 'cart_item' ) ? $this->cart_item_class( $classname, $values ) : $this->order_item_class( $classname, $values, false );
	}
	public function woo_bundles_frontend_scripts() {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::frontend_scripts()' );
		return $this->frontend_scripts();
	}
	public function woo_bundles_cart_contents_count( $count ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::cart_contents_count()' );
		return $this->cart_contents_count( $count );
	}
	public function woo_bundles_add_cart_widget_filters() {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::add_cart_widget_filters()' );
		return $this->add_cart_widget_filters();
	}
	public function woo_bundles_remove_cart_widget_filters() {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::remove_cart_widget_filters()' );
		return $this->remove_cart_widget_filters();
	}
	public function woo_bundles_order_item_visible( $visible, $order_item ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::order_item_visible()' );
		return $this->order_item_visible( $visible, $order_item );
	}
	public function woo_bundles_cart_item_visible( $visible, $cart_item, $cart_item_key ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::cart_item_visible()' );
		return $this->cart_item_visible( $visible, $cart_item, $cart_item_key );
	}
	public function woo_bundles_email_styles( $css ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::email_styles()' );
		return $this->email_styles( $css );
	}
}
