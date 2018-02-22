<?php
/**
 * WC_PB_Cart class
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
 * Product Bundle cart functions and filters.
 *
 * @class    WC_PB_Cart
 * @version  5.4.3
 */
class WC_PB_Cart {

	/**
	 * Globally accessible validation context for 'validate_bundle_configuration'.
	 * Possible values: 'add-to-cart'|'cart'.
	 *
	 * @var string
	 */
	public static $validation_context = 'add-to-cart';

	/**
	 * The single instance of the class.
	 * @var WC_PB_Cart
	 *
	 * @since 5.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main WC_PB_Cart instance. Ensures only one instance of WC_PB_Cart is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_PB_Cart
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

	/*
	 * Setup hooks.
	 */
	protected function __construct() {

		// Validate bundle add-to-cart.
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_to_cart' ), 10, 6 );

		// Validate bundle configuration in cart.
		add_action( 'woocommerce_check_cart_items', array( $this, 'check_cart_items' ), 15 );

		// Add bundle-specific cart item data based on posted vars.
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 2 );

		// Add bundled items to the cart.
		add_action( 'woocommerce_add_to_cart', array( $this, 'bundle_add_to_cart' ), 10, 6 );

		// Modify cart items for bundled shipping strategy.
		add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item_filter' ), 10, 2 );

		// Load bundle data from session into the cart.
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 10, 3 );

		// Refresh bundle configuration fields.
		add_filter( 'woocommerce_bundle_container_cart_item', array( $this, 'update_bundle_container_cart_item_configuration' ), 10, 2 );
		add_filter( 'woocommerce_bundled_cart_item', array( $this, 'update_bundled_cart_item_configuration' ), 10, 2 );

		// Ensure no orphans are in the cart at this point.
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'cart_loaded_from_session' ) );

		// Sync quantities of bundled items with bundle quantity.
		add_filter( 'woocommerce_cart_item_quantity', array( $this, 'cart_item_quantity' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'cart_item_remove_link' ), 10, 2 );

		// Sync quantities of bundled items with bundle quantity.
		add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'update_quantity_in_cart' ), 1, 2 );
		add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'update_quantity_in_cart' ), 1 );

		// Put back cart item data to allow re-ordering of bundles.
		add_filter( 'woocommerce_order_again_cart_item_data', array( $this, 'order_again' ), 10, 3 );

		// Filter cart item price.
		add_filter( 'woocommerce_cart_item_price', array( $this, 'cart_item_price_html' ), 10, 3 );

		// Modify cart items subtotals depending on how bundled items are priced.
		add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'item_subtotal' ), 10, 3 );
		add_filter( 'woocommerce_checkout_item_subtotal', array( $this, 'item_subtotal' ), 10, 3 );

		// Remove bundled items on removing parent item.
		add_action( 'woocommerce_cart_item_removed', array( $this, 'cart_item_removed' ), 10, 2 );
		add_action( 'woocommerce_cart_item_restored', array( $this, 'cart_item_restored' ), 10, 2 );

		// Shipping fix - ensure that non-virtual containers/children, which are shipped, have a valid price that can be used for insurance calculations.
		// Additionally, bundled item weights may have to be added in the container.
		add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'cart_shipping_packages' ), 5 );

		// Coupons - inherit bundled item coupon validity from parent.
		add_filter( 'woocommerce_coupon_is_valid_for_product', array( $this, 'coupon_is_valid_for_product' ), 10, 4 );

		// Remove recurring component of bundled subscription-type products in statically-priced bundles.
		add_action( 'woocommerce_subscription_cart_before_grouping', array( $this, 'add_subcription_filter' ) );
		add_action( 'woocommerce_subscription_cart_after_grouping', array( $this, 'remove_subcription_filter' ) );
	}

	/*
	|--------------------------------------------------------------------------
	| API methods.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Validates and adds a bundle to the cart. Relies on specifying a bundle configuration array with all necessary data - @see 'get_posted_bundle_configuration()' for details.
	 *
	 * @param  mixed  $product_id      Id of the bundle to add to the cart.
	 * @param  mixed  $quantity        Quantity of the bundle.
	 * @param  array  $configuration   Bundle configuration - @see 'get_posted_bundle_configuration()'.
	 * @param  array  $cart_item_data  Custom cart item data to pass to 'WC_Cart::add_to_cart()'.
	 * @return string|WP_Error
	 */
	public function add_bundle_to_cart( $product_id, $quantity, $configuration = array(), $cart_item_data = array() ) {

		$bundle        = wc_get_product( $product_id );
		$added_to_cart = false;

		if ( $bundle ) {

			if ( $this->validate_bundle_configuration( $bundle, $quantity, $configuration ) ) {
				$added_to_cart = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), array_merge( $cart_item_data, array( 'stamp' => $configuration, 'bundled_items' => array() ) ) );
			} else {

				// No other way to collect notices reliably, including notices from 3rd party extensions.
				$notices = wc_get_notices( 'error' );
				$message = __( 'The submitted bundle configuration could not be added to the cart.', 'ultimatewoo-pro' );

				$added_to_cart = new WP_Error( 'woocommerce_bundle_configuration_invalid', $message, array( 'notices' => $notices ) );
			}

		} else {
			$message       = __( 'A bundle with this ID does not exist.', 'ultimatewoo-pro' );
			$added_to_cart = new WP_Error( 'woocommerce_bundle_invalid', $message );
		}

		return $added_to_cart;
	}

	/**
	 * Parses a bundle configuration array to ensure that all mandatory cart item data fields are present.
	 * Can also be used to get an array with the minimum required data to fill in before calling 'add_bundle_to_cart'.
	 *
	 * @param  WC_Product_Bundle  $bundle         Product bundle whose configuration is being parsed or generated.
	 * @param  array              $configuration  Initial configuration array to parse. Leave empty to get a minimum array that you can fill with data - @see 'get_posted_bundle_configuration()'.
	 * @param  boolean            $strict_mode    Set true to initialize bundled product IDs to an empty string if undefined in the source array.
	 * @return array
	 */
	public function parse_bundle_configuration( $bundle, $configuration = array(), $strict_mode = false ) {

		$bundled_items       = $bundle->get_bundled_items();
		$parsed_configuration = array();

		foreach ( $bundled_items as $bundled_item_id => $bundled_item ) {

			$item_configuration = isset( $configuration[ $bundled_item_id ] ) ? $configuration[ $bundled_item_id ] : array();

			$defaults = array(
				'product_id' => $strict_mode ? '' : $bundled_item->product_id,
				'quantity'   => $bundled_item->get_quantity( 'min' )
			);

			$parsed_configuration[ $bundled_item_id ] = wp_parse_args( $item_configuration, $defaults );

			$parsed_configuration[ $bundled_item_id ][ 'discount' ] = $bundled_item->get_discount();

			if ( $bundled_item->has_title_override() ) {
				$parsed_configuration[ $bundled_item_id ][ 'title' ] = $bundled_item->get_raw_title();
			}
		}

		return $parsed_configuration;
	}

	/**
	 * Build bundle configuration array from posted data. Array example:
	 *
	 *    $config = array(
	 *        134 => array(                             // ID of bundled item.
	 *            'product_id'        => 15,            // ID of bundled product.
	 *            'quantity'          => 2,             // Qty of bundled product, will fall back to min.
	 *            'discount'          => 50.0,          // Bundled product discount, defaults to the defined value.
	 *            'title'             => 'Test',        // Bundled product title, include only if overriding.
	 *            'optional_selected' => 'yes',         // If the bundled item is optional, indicate if chosen or not.
	 *            'attributes'        => array(         // Array of selected variation attribute names, sanitized.
	 *                'attribute_color' => 'black',
	 *                'attribute_size'  => 'medium'
	 *             ),
	 *            'variation_id'      => 43             // ID of chosen variation, if applicable.
	 *        )
	 *    );
	 *
	 * @param  mixed  $product
	 * @return array
	 */
	public function get_posted_bundle_configuration( $product ) {

		$posted_config = array();

		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( is_object( $product ) && 'bundle' === $product->get_type() ) {

			$product_id    = WC_PB_Core_Compatibility::get_id( $product );
			$bundled_items = $product->get_bundled_items();

			if ( ! empty( $bundled_items ) ) {

				/*
				 * Choose between $_POST or $_GET for grabbing data.
				 * We will not rely on $_REQUEST because checkbox names may not exist in $_POST but they may well exist in $_GET, for instance when editing a bundle from the cart.
				 */

				$posted_data = $_POST;

				if ( empty( $_POST[ 'add-to-cart' ] ) && ! empty( $_GET[ 'add-to-cart' ] ) ) {
					$posted_data = $_GET;
				}

				foreach ( $bundled_items as $bundled_item_id => $bundled_item ) {

					$posted_config[ $bundled_item_id ] = array();

					$bundled_product_id   = $bundled_item->product_id;
					$bundled_product_type = $bundled_item->product->get_type();
					$is_optional          = $bundled_item->is_optional();

					/**
					 * 'woocommerce_product_bundle_field_prefix' filter.
					 *
					 * Used to post unique bundle data when posting multiple bundle configurations that could include the same bundle multiple times.
					 *
					 * @param  string  $prefix
					 * @param  mixed   $product_id
					 */
					$bundled_item_quantity_request_key = apply_filters( 'woocommerce_product_bundle_field_prefix', '', $product_id ) . 'bundle_quantity_' . $bundled_item_id;
					$bundled_product_qty               = isset( $posted_data[ $bundled_item_quantity_request_key ] ) ? absint( $posted_data[ $bundled_item_quantity_request_key ] ) : $bundled_item->get_quantity();

					$posted_config[ $bundled_item_id ][ 'product_id' ] = $bundled_product_id;

					if ( $bundled_item->has_title_override() ) {
						$posted_config[ $bundled_item_id ][ 'title' ] = $bundled_item->get_raw_title();
					}

					if ( $is_optional ) {

						/** Documented in method 'get_posted_bundle_configuration'. */
						$bundled_item_selected_request_key = apply_filters( 'woocommerce_product_bundle_field_prefix', '', $product_id ) . 'bundle_selected_optional_' . $bundled_item_id;

						$posted_config[ $bundled_item_id ][ 'optional_selected' ] = isset( $posted_data[ $bundled_item_selected_request_key ] ) ? 'yes' : 'no';

						if ( 'no' === $posted_config[ $bundled_item_id ][ 'optional_selected' ] ) {
							$bundled_product_qty = 0;
						}
					}

					$posted_config[ $bundled_item_id ][ 'quantity' ] = $bundled_product_qty;

					// Store variable product options in stamp to avoid generating the same bundle cart id.
					if ( 'variable' === $bundled_product_type || 'variable-subscription' === $bundled_product_type ) {

						$attr_stamp = array();
						$attributes = $bundled_item->product->get_attributes();

						foreach ( $attributes as $attribute ) {

							if ( ! $attribute[ 'is_variation' ] ) {
								continue;
							}

							$taxonomy = WC_PB_Core_Compatibility::wc_variation_attribute_name( $attribute[ 'name' ] );

							/** Documented in method 'get_posted_bundle_configuration'. */
							$bundled_item_taxonomy_request_key = apply_filters( 'woocommerce_product_bundle_field_prefix', '', $product_id ) . 'bundle_' . $taxonomy . '_' . $bundled_item_id;

							if ( isset( $posted_data[ $bundled_item_taxonomy_request_key ] ) ) {

								// Get value from post data.
								if ( $attribute[ 'is_taxonomy' ] ) {
									$value = sanitize_title( stripslashes( $posted_data[ $bundled_item_taxonomy_request_key ] ) );
								} else {
									$value = wc_clean( stripslashes( $posted_data[ $bundled_item_taxonomy_request_key ] ) );
								}

								$attr_stamp[ $taxonomy ] = $value;
							}
						}

						$posted_config[ $bundled_item_id ][ 'attributes' ]   = $attr_stamp;
						$bundled_item_variation_id_request_key               = apply_filters( 'woocommerce_product_bundle_field_prefix', '', $product_id ) . 'bundle_variation_id_' . $bundled_item_id;
						$posted_config[ $bundled_item_id ][ 'variation_id' ] = isset( $posted_data[ $bundled_item_variation_id_request_key ] ) ? $posted_data[ $bundled_item_variation_id_request_key ] : '';
					}
				}
			}
		}

		$posted_config = $this->parse_bundle_configuration( $product, $posted_config, true );

		return $posted_config;
	}

	/**
	 * Validates the selected bundled items in a bundle configuration.
	 *
	 * @param  mixed   $product
	 * @param  int     $product_quantity
	 * @param  array   $configuration
	 * @param  string  $context
	 * @return boolean
	 */
	public function validate_bundle_configuration( $product, $product_quantity, $configuration, $context = 'add-to-cart' ) {

		$passes_validation = true;

		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( is_object( $product ) && 'bundle' === $product->get_type() ) {

			$product_id    = WC_PB_Core_Compatibility::get_id( $product );
			$product_title = $product->get_title();

			// If a stock-managed product / variation exists in the bundle multiple times, its stock will be checked only once for the sum of all bundled quantities.
			// The stock manager class keeps a record of stock-managed product / variation ids.
			$bundled_stock = new WC_PB_Stock_Manager( $product );

			// Grab bundled items.
			$bundled_items = $product->get_bundled_items();

			if ( sizeof( $bundled_items ) ) {

				foreach ( $bundled_items as $bundled_item_id => $bundled_item ) {

					$bundled_product_id   = $bundled_item->product_id;
					$bundled_variation_id = '';
					$bundled_product_type = $bundled_item->product->get_type();

					// Optional.
					$is_optional           = $bundled_item->is_optional();
					$is_optional_selected  = $is_optional && isset( $configuration[ $bundled_item_id ][ 'optional_selected' ] ) && 'yes' === $configuration[ $bundled_item_id ][ 'optional_selected' ];

					if ( $is_optional && ! $is_optional_selected ) {
						continue;
					}

					// Check existence.
					if ( 'cart' === $context ) {
						if ( ! isset( $configuration[ $bundled_item_id ] ) || empty( $configuration[ $bundled_item_id ][ 'product_id' ] ) ) {
							wc_add_notice( sprintf( __( '&quot;%1$s&quot; cannot be purchased &ndash; some of its contents are missing from your cart.', 'ultimatewoo-pro' ), $product_title ), 'error' );
							return false;
						} elseif ( isset( $configuration[ $bundled_item_id ][ 'optional_selected' ] ) && 'no' === $configuration[ $bundled_item_id ][ 'optional_selected' ] ) {
							wc_add_notice( sprintf( __( '&quot;%1$s&quot; cannot be purchased &ndash; some of its contents are missing from your cart.', 'ultimatewoo-pro' ), $product_title ), 'error' );
							return false;
						}
					}

					// Check quantity.
					$item_quantity_min = $bundled_item->get_quantity();
					$item_quantity_max = $bundled_item->get_quantity( 'max' );

					if ( isset( $configuration[ $bundled_item_id ][ 'quantity' ] ) ) {
						$item_quantity = absint( $configuration[ $bundled_item_id ][ 'quantity' ] );
					} else {
						$item_quantity = $item_quantity_min;
					}

					if ( $item_quantity < $item_quantity_min ) {
						if ( in_array( $context, array( 'add-to-cart', 'add-to-order' ) ) ) {
							wc_add_notice( sprintf( __( '&quot;%1$s&quot; cannot be added to the cart. The quantity of &quot;%2$s&quot; cannot be lower than %3$d.', 'ultimatewoo-pro' ), $product_title, $bundled_item->get_raw_title(), $item_quantity_min ), 'error' );
						} elseif ( 'cart' === $context ) {
							wc_add_notice( sprintf( __( '&quot;%1$s&quot; cannot be purchased. The quantity of &quot;%2$s&quot; cannot be lower than %3$d.', 'ultimatewoo-pro' ), $product_title, $bundled_item->get_raw_title(), $item_quantity_min ), 'error' );
						}
						return false;
					} elseif ( $item_quantity_max && $item_quantity > $item_quantity_max ) {
						if ( in_array( $context, array( 'add-to-cart', 'add-to-order' ) ) ) {
							wc_add_notice( sprintf( __( '&quot;%1$s&quot; cannot be added to the cart. The quantity of &quot;%2$s&quot; cannot be higher than %3$d.', 'ultimatewoo-pro' ), $product_title, $bundled_item->get_raw_title(), $item_quantity_max ), 'error' );
						} elseif ( 'cart' === $context ) {
							wc_add_notice( sprintf( __( '&quot;%1$s&quot; cannot be purchased. The quantity of &quot;%2$s&quot; cannot be higher than %3$d.', 'ultimatewoo-pro' ), $product_title, $bundled_item->get_raw_title(), $item_quantity_max ), 'error' );
						}
						return false;
					}

					$quantity = $item_quantity * $product_quantity;

					// If quantity is zero, continue.
					if ( $quantity == 0 ) {
						continue;
					}

					// Purchasable?
					if ( false === $bundled_item->is_purchasable() ) {
						wc_add_notice( sprintf( __( '&quot;%1$s&quot; cannot be added to the cart &ndash; &quot;%2$s&quot; cannot be purchased at the moment.', 'ultimatewoo-pro' ), $product_title, $bundled_item->get_raw_title() ), 'error' );
						return false;
					}

					// Validate variation id.
					if ( 'variable' === $bundled_product_type || 'variable-subscription' === $bundled_product_type ) {

						$bundled_variation_id = isset( $configuration[ $bundled_item_id ][ 'variation_id' ] ) ? $configuration[ $bundled_item_id ][ 'variation_id' ] : '';
						$bundled_variation    = $bundled_variation_id ? wc_get_product( $bundled_variation_id ) : false;

						if ( $bundled_variation ) {

							if ( ! $bundled_variation || WC_PB_Core_Compatibility::get_parent_id( $bundled_variation ) !== absint( $bundled_product_id ) || false === $bundled_variation->is_purchasable() ) {
								wc_add_notice( sprintf( __( '&quot;%1$s&quot; cannot be added to the cart. The chosen &quot;%2$s&quot; variation cannot be purchased.', 'ultimatewoo-pro' ), $product_title, $bundled_item->get_raw_title() ), 'error' );
								return false;
							}

							// Add item for validation.
							$bundled_stock->add_item( $bundled_product_id, $bundled_variation, $quantity, array( 'bundled_item' => $bundled_item ) );
						}

						// Verify all attributes for the variable product were set.
						$attributes         = $bundled_item->product->get_attributes();
						$variation_data     = array();
						$missing_attributes = array();
						$all_set            = true;

						if ( $bundled_variation ) {

							$variation_data = wc_get_product_variation_attributes( $bundled_variation_id );

							// Verify all attributes.
							foreach ( $attributes as $attribute ) {

							    if ( ! $attribute[ 'is_variation' ] ) {
							    	continue;
							    }

							    $taxonomy = WC_PB_Core_Compatibility::wc_variation_attribute_name( $attribute[ 'name' ] );

							    if ( isset( $configuration[ $bundled_item_id ][ 'attributes' ][ $taxonomy ] ) && isset( $configuration[ $bundled_item_id ][ 'variation_id' ] ) ) {

									$valid_value = $variation_data[ $taxonomy ];

									if ( '' === $valid_value || $valid_value === $configuration[ $bundled_item_id ][ 'attributes' ][ $taxonomy ] ) {
										continue;
									}

									$missing_attributes[] = wc_attribute_label( $attribute[ 'name' ] );

								} else {
									$missing_attributes[] = wc_attribute_label( $attribute[ 'name' ] );
								}

								$all_set = false;
							}

						} else {
							$all_set = false;
						}

						if ( ! $all_set ) {
							if ( $missing_attributes ) {
								$required_fields_notice = sprintf( _n( '%1$s is a required &quot;%2$s&quot; field', '%1$s are required &quot;%2$s&quot; fields', sizeof( $missing_attributes ), 'ultimatewoo-pro' ), wc_format_list_of_items( $missing_attributes ), $bundled_item->get_raw_title() );
	    						wc_add_notice( sprintf( __( '&quot;%1$s&quot; cannot be added to the cart. %2$s.', 'ultimatewoo-pro' ), $product_title, $required_fields_notice ), 'error' );
	    						return false;
							} else {
								wc_add_notice( sprintf( __( '&quot;%1$s&quot; cannot be added to the cart. Please choose &quot;%2$s&quot; options&hellip;', 'ultimatewoo-pro' ), $product_title, $bundled_item->get_raw_title() ), 'error' );
								return false;
							}
						}

					} elseif ( 'simple' === $bundled_product_type || 'subscription' === $bundled_product_type ) {

						// Add item for validation.
						$bundled_stock->add_item( $bundled_product_id, false, $quantity, array( 'bundled_item' => $bundled_item ) );
					}

					if ( in_array( $context, array( 'add-to-cart', 'add-to-order' ) ) ) {
						/**
						 * 'woocommerce_bundled_item_add_to_cart_validation' filter.
						 *
						 * Use this filter to perform additional validation checks at bundled item level.
						 *
						 * @param  boolean          $result
						 * @param  WC_Product       $product
						 * @param  WC_Bundled_Item  $bundled_item
						 * @param  int              $quantity
						 * @param  mixed            $bundled_variation_id
						 * @param  array            $configuration
						 */
						if ( false === apply_filters( 'woocommerce_bundled_item_add_to_cart_validation', true, $product, $bundled_item, $quantity, $bundled_variation_id, $configuration ) ) {
							return false;
						}
					}
				}
			}

			if ( 'add-to-cart' === $context ) {

				// Check stock for stock-managed bundled items. If out of stock, don't proceed.
				if ( false === $bundled_stock->validate_stock() ) {
					return false;
				}

				/**
				 * 'woocommerce_add_to_cart_bundle_validation' filter.
				 *
				 * Use this filter to perform additional validation checks at bundle level.
				 *
				 * @param  boolean              $result
				 * @param  mixed                $product_id
				 * @param  WC_PB_Stock_Manager  $bundled_stock
				 * @param  array                $configuration
				 */
				if ( false === apply_filters( 'woocommerce_add_to_cart_bundle_validation', true, $product_id, $bundled_stock, $configuration ) ) {
					return false;
				}
			}

			// Composite Products compatibility.
			WC_PB_Compatibility::$stock_data = $bundled_stock;
		}

		return $passes_validation;
	}

	/**
	 * Outputs a formatted subtotal.
	 *
	 * @param  WC_Product  $product
	 * @param  string      $subtotal
	 * @return string
	 */
	public function format_product_subtotal( $product, $subtotal ) {

		$cart = WC()->cart;

		$taxable = $product->is_taxable();

		// Taxable.
		if ( $taxable ) {

			if ( 'excl' === get_option( 'woocommerce_tax_display_cart' ) ) {

				$product_subtotal = wc_price( $subtotal );

				if ( $cart->prices_include_tax && $cart->tax_total > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
				}

			} else {

				$product_subtotal = wc_price( $subtotal );

				if ( ! $cart->prices_include_tax && $cart->tax_total > 0 ) {
					$product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
				}
			}

		// Non-taxable.
		} else {
			$product_subtotal = wc_price( $subtotal );
		}

		return $product_subtotal;
	}

	/**
	 * When a bundle is static-priced, the price of all bundled items is set to 0.
	 * When the shipping mode is set to "bundled", all bundled items are marked as virtual when they are added to the cart.
	 * Otherwise, the container itself is a virtual product in the first place.
	 *
	 * @param  array              $cart_item
	 * @param  WC_Product_Bundle  $bundle
	 * @return array
	 */
	private function set_bundled_cart_item( $cart_item, $bundle ) {

		$bundled_item_id = $cart_item[ 'bundled_item_id' ];
		$bundled_item    = $bundle->get_bundled_item( $bundled_item_id );

		if ( $bundled_item ) {
			if ( false === $bundled_item->is_priced_individually() ) {

				WC_PB_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'regular_price', 0 );
				WC_PB_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'price', 0 );
				WC_PB_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'sale_price', '' );

				if ( WC_PB()->compatibility->is_subscription( $cart_item[ 'data' ] ) ) {
					$cart_item[ 'data' ]->subscription_sign_up_fee = 0;
					$cart_item[ 'data' ]->wc_pb_block_sub          = 'yes';
				}

			} else {
				WC_PB_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'price', $bundled_item->get_raw_price( $cart_item[ 'data' ], 'cart' ) );
			}

			if ( $bundled_item->has_title_override() ) {
				WC_PB_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'name', $bundled_item->get_raw_title() );
			}
		}

		if ( $cart_item[ 'data' ]->needs_shipping() ) {

			if ( false === $bundled_item->is_shipped_individually( $cart_item[ 'data' ] ) ) {

				/**
				 * 'woocommerce_bundled_item_has_bundled_weight' filter.
				 *
				 * When the shipping properties of a bundled product are overridden by its container ("Shipped Individually" option unchecked), the bundle container item weight is assumed static and the bundled product weight is ignored.
				 * You can use this filter to have the weight of bundled items appended to the container weight, instead of ignored, when the "Shipped Individually" option is unchecked.
				 *
				 * @param  boolean            $append_weight
				 * @param  WC_Product         $data
				 * @param  mixed              $bundled_item_id
				 * @param  WC_Product_Bundle  $bundle
				 */
				if ( apply_filters( 'woocommerce_bundled_item_has_bundled_weight', false, $cart_item[ 'data' ], $bundled_item_id, $bundle ) ) {
					WC_PB_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'bundled_weight', $cart_item[ 'data' ]->get_weight( 'edit' ) );
				}

				WC_PB_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'bundled_value', WC_PB_Core_Compatibility::get_prop( $cart_item[ 'data' ], 'price', 'edit' ) );

				WC_PB_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'virtual', 'yes' );
				WC_PB_Core_Compatibility::set_prop( $cart_item[ 'data' ], 'weight', '' );
			}
		}

		/**
		 * 'woocommerce_bundled_cart_item' filter.
		 *
		 * Last chance to filter bundled cart item data.
		 *
		 * @param  array              $cart_item
		 * @param  WC_Product_Bundle  $bundle
		 */
		return apply_filters( 'woocommerce_bundled_cart_item', $cart_item, $bundle );
	}

	/**
	 * Bundle container price must be set equal to the base price when individually-priced items exist.
	 *
	 * @param  array              $cart_item
	 * @param  WC_Product_Bundle  $bundle
	 * @return array
	 */
	private function set_bundle_container_cart_item( $cart_item ) {

		$bundle = $cart_item[ 'data' ];

		/**
		 * 'woocommerce_bundle_container_cart_item' filter.
		 *
		 * Last chance to filter bundle container cart item data.
		 *
		 * @param  array              $cart_item
		 * @param  WC_Product_Bundle  $bundle
		 */
		return apply_filters( 'woocommerce_bundle_container_cart_item', $cart_item, $bundle );
	}

	/**
	 * Refresh parent item configuration fields that might be out-of-date.
	 *
	 * @param  array              $cart_item
	 * @param  WC_Product_Bundle  $bundle
	 * @return array
	 */
	public function update_bundle_container_cart_item_configuration( $cart_item, $bundle ) {

		if ( isset( $cart_item[ 'stamp' ] ) ) {
			$cart_item[ 'stamp' ] = $this->parse_bundle_configuration( $bundle, $cart_item[ 'stamp' ], true );
		}

		return $cart_item;
	}

	/**
	 * Refresh child item configuration fields that might be out-of-date.
	 *
	 * @param  array              $cart_item
	 * @param  WC_Product_Bundle  $bundle
	 * @return array
	 */
	public function update_bundled_cart_item_configuration( $cart_item, $bundle ) {

		if ( $bundle_container_item = wc_pb_get_bundled_cart_item_container( $cart_item ) ) {
			$cart_item[ 'stamp' ] = $bundle_container_item[ 'stamp' ];
		}

		return $cart_item;
	}

	/**
	 * Adds a bundled product to the cart. Must be done without updating session data, recalculating totals or calling 'woocommerce_add_to_cart' recursively.
	 * For the recursion issue, see: https://core.trac.wordpress.org/ticket/17817.
	 *
	 * @param  int    $bundle_id
	 * @param  int    $product_id
	 * @param  int    $quantity
	 * @param  int    $variation_id
	 * @param  array  $variation
	 * @param  array  $cart_item_data
	 * @return boolean
	 */
	private function bundled_add_to_cart( $bundle_id, $product_id, $quantity = 1, $variation_id = '', $variation = '', $cart_item_data ) {

		if ( $quantity <= 0 ) {
			return false;
		}

		// Load cart item data when adding to cart.
		$cart_item_data = (array) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id );

		// Generate a ID based on product ID, variation ID, variation data, and other cart item data.
		$cart_id = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

		// See if this product and its options is already in the cart.
		$cart_item_key = WC()->cart->find_product_in_cart( $cart_id );

		// Ensure we don't add a variation to the cart directly by variation ID.
		if ( 'product_variation' == get_post_type( $product_id ) ) {
			$variation_id = $product_id;
			$product_id   = wp_get_post_parent_id( $variation_id );
		}

		// Get the product
		$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );

		// If cart_item_key is set, the item is already in the cart and its quantity will be handled by 'update_quantity_in_cart()'.
		if ( ! $cart_item_key ) {

			$cart_item_key = $cart_id;

			// Add item after merging with $cart_item_data - allow plugins and 'add_cart_item_filter()' to modify cart item.
			WC()->cart->cart_contents[ $cart_item_key ] = apply_filters( 'woocommerce_add_cart_item', array_merge( $cart_item_data, array(
				'product_id'   => absint( $product_id ),
				'variation_id' => absint( $variation_id ),
				'variation'    => $variation,
				'quantity'     => $quantity,
				'data'         => $product_data
			) ), $cart_item_key );

		}

		/**
		 * 'woocommerce_bundled_add_to_cart' action.
		 *
		 * @see 'woocommerce_add_to_cart' action.
		 *
		 * @param  string  $cart_item_key
		 * @param  mixed   $bundled_product_id
		 * @param  int     $quantity
		 * @param  mixed   $variation_id
		 * @param  array   $variation_data
		 * @param  array   $cart_item_data
		 * @param  mixed   $bundle_id
		 */
		do_action( 'woocommerce_bundled_add_to_cart', $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data, $bundle_id );

		return $cart_item_key;
	}


	/*
	|--------------------------------------------------------------------------
	| Filter hooks.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Check bundle cart item configurations on cart load.
	 */
	public function check_cart_items() {
		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {

			if ( wc_pb_is_bundle_container_cart_item( $cart_item ) ) {

				$configuration = isset( $cart_item[ 'stamp' ] ) ? $cart_item[ 'stamp' ] : $this->get_posted_bundle_configuration( $cart_item[ 'data' ] );

				self::$validation_context = 'cart';
				$this->validate_bundle_configuration( $cart_item[ 'data' ], $cart_item[ 'quantity' ], $configuration, self::$validation_context );
				self::$validation_context = 'add-to-cart';
			}
		}
	}

	/**
	 * Validates add-to-cart for bundles.
	 * Basically ensures that stock for all bundled products exists before attempting to add them to cart.
	 *
	 * @param  boolean  $add
	 * @param  int      $product_id
	 * @param  int      $product_quantity
	 * @param  mixed    $variation_id
	 * @param  array    $variations
	 * @param  array    $cart_item_data
	 * @return boolean
	 */
	public function validate_add_to_cart( $add, $product_id, $product_quantity, $variation_id = '', $variations = array(), $cart_item_data = array() ) {

		// Get product type.
		$product_type = WC_PB_Core_Compatibility::get_product_type( $product_id );

		// Prevent bundled items from getting validated when re-ordering: they will be added by the container item - @see 'validate_add_to_cart()'.
		if ( ( isset( $cart_item_data[ 'is_order_again_bundled' ] ) || isset( $cart_item_data[ 'is_order_again_composited' ] ) ) ) {
			$add = false;
		}

		if ( $add && 'bundle' === $product_type ) {

			$product = wc_get_product( $product_id );

			/**
			 * 'woocommerce_bundle_before_validation' filter.
			 *
			 * Early chance to stop/bypass any further validation.
			 *
			 * @param  boolean            $true
			 * @param  WC_Product_Bundle  $product
			 */
			if ( $product && apply_filters( 'woocommerce_bundle_before_validation', true, $product ) ) {

				$configuration = isset( $cart_item_data[ 'stamp' ] ) ? $cart_item_data[ 'stamp' ] : $this->get_posted_bundle_configuration( $product );

				if ( ! $this->validate_bundle_configuration( $product, $product_quantity, $configuration ) ) {
					$add = false;
				}

			} else {
				$add = false;
			}
		}

		return $add;
	}

	/**
	 * Redirect to the cart when editing a bundle "in-cart".
	 *
	 * @param  string  $url
	 * @return string
	 */
	public function edit_in_cart_redirect( $url ) {

		return WC()->cart->get_cart_url();
	}

	/**
	 * Filter the displayed notice after redirecting to the cart when editing a bundle "in-cart".
	 *
	 * @param  string  $url
	 * @return string
	 */
	public function edit_in_cart_redirect_message( $message ) {

		return __( 'Cart updated.', 'woocommerce' );
	}

	/**
	 * Adds bundle specific cart-item data.
	 * The 'stamp' var is a unique identifier for that particular bundle configuration.
	 *
	 * @param  array  $cart_item_data
	 * @param  int    $product_id
	 * @return array
	 */
	public function add_cart_item_data( $cart_item_data, $product_id ) {

		// Get product type.
		$product_type = WC_PB_Core_Compatibility::get_product_type( $product_id );

		if ( 'bundle' === $product_type ) {

			$updating_bundle_in_cart = false;

			// Updating bundle in cart?
			if ( isset( $_POST[ 'update-bundle' ] ) ) {

				$updating_cart_key = wc_clean( $_POST[ 'update-bundle' ] );

				if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {

					$updating_bundle_in_cart = true;

					// Remove.
					WC()->cart->remove_cart_item( $updating_cart_key );

					// Redirect to cart.
					add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'edit_in_cart_redirect' ) );

					// Edit notice.
					add_filter( WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ? 'wc_add_to_cart_message_html' : 'wc_add_to_cart_message', array( $this, 'edit_in_cart_redirect_message' ) );
				}
			}

			// Use posted data to build a bundle configuration 'stamp' array.
			if ( ! isset( $cart_item_data[ 'stamp' ] ) ) {

				$configuration = $this->get_posted_bundle_configuration( $product_id );

				foreach ( $configuration as $bundled_item_id => $bundled_item_configuration ) {

					/**
					 * 'woocommerce_bundled_item_cart_item_identifier' filter.
					 *
					 * Filters the config data array - use this to add any bundle-specific data that should result in unique container item ids being produced when the input data changes, such as add-ons data.
					 *
					 * @param  array  $posted_item_config
					 * @param  int    $bundled_item_id
					 * @param  mixed  $product_id
					 */
					$configuration[ $bundled_item_id ] = apply_filters( 'woocommerce_bundled_item_cart_item_identifier', $bundled_item_configuration, $bundled_item_id, $product_id );
				}

				$cart_item_data[ 'stamp' ] = $configuration;

				// Check "Sold Individually" option context.
				if ( false === $updating_bundle_in_cart && ( $product = wc_get_product( $product_id ) ) && $product->is_sold_individually() ) {
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
						if ( $product_id === $cart_item[ 'product_id' ] && 'product' === $product->get_sold_individually_context() ) {
							throw new Exception( sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', WC()->cart->get_cart_url(), __( 'View Cart', 'woocommerce' ), sprintf( __( 'You cannot add another &quot;%s&quot; to your cart.', 'woocommerce' ), $product->get_title() ) ) );
						} elseif ( wc_pb_is_bundle_container_cart_item( $cart_item ) && $configuration === $cart_item[ 'stamp' ] ) {
							throw new Exception( sprintf( '<a href="%s" class="button wc-forward">%s</a> %s', WC()->cart->get_cart_url(), __( 'View Cart', 'woocommerce' ), sprintf( __( 'You have already added an identical &quot;%s&quot; to your cart. You cannot add another one.', 'ultimatewoo-pro' ), $product->get_title() ) ) );
						}
					}
				}
			}

			// Prepare additional data for later use.
			if ( ! isset( $cart_item_data[ 'bundled_items' ] ) ) {
				$cart_item_data[ 'bundled_items' ] = array();
			}
		}

		return $cart_item_data;
	}

	/**
	 * Adds bundled items to the cart on the 'woocommerce_add_to_cart' action.
	 * The 'bundled_by' var is added to each item to identify between bundled and standalone instances of products.
	 * Important: Recursively calling the core add_to_cart function can lead to issus with the contained action hook: https://core.trac.wordpress.org/ticket/17817.
	 *
	 * @param  string  $bundle_cart_key
	 * @param  int     $bundle_id
	 * @param  int     $bundle_quantity
	 * @param  int     $variation_id
	 * @param  array   $variation
	 * @param  array   $cart_item_data
	 * @return void
	 */
	public function bundle_add_to_cart( $bundle_cart_key, $bundle_id, $bundle_quantity, $variation_id, $variation, $cart_item_data ) {

		if ( wc_pb_is_bundle_container_cart_item( $cart_item_data ) ) {

			// Note: The resulting cart item ID is unique.
			$bundled_items_cart_data = array( 'bundled_by' => $bundle_cart_key, 'stamp' => $cart_item_data[ 'stamp' ] );

			// The bundle.
			$bundle = WC()->cart->cart_contents[ $bundle_cart_key ][ 'data' ];

			if ( empty( $cart_item_data[ 'stamp' ] ) ) {
				throw new Exception( sprintf( __( 'The requested configuration of &quot;%s&quot; cannot be purchased at the moment.', 'ultimatewoo-pro' ), $bundle->get_title() ) );
				return false;
			}

			// Now add all items - yay.
			foreach ( $cart_item_data[ 'stamp' ] as $bundled_item_id => $bundled_item_stamp ) {

				if ( ! $bundle->has_bundled_item( $bundled_item_id ) ) {
					throw new Exception( sprintf( __( 'The requested configuration of &quot;%s&quot; cannot be purchased at the moment.', 'ultimatewoo-pro' ), $bundle->get_title() ) );
					return false;
				}

				$bundled_item           = $bundle->get_bundled_item( $bundled_item_id );
				$bundled_item_cart_data = $bundled_items_cart_data;

				if ( isset( $bundled_item_stamp[ 'optional_selected' ] ) && 'no' === $bundled_item_stamp[ 'optional_selected' ] ) {
					continue;
				}

				if ( isset( $bundled_item_stamp[ 'quantity' ] ) && absint( $bundled_item_stamp[ 'quantity' ] ) === 0 ) {
					continue;
				}

				$bundled_item_cart_data[ 'bundled_item_id' ] = $bundled_item_id;

				$item_quantity        = isset( $bundled_item_stamp[ 'quantity' ] ) ? absint( $bundled_item_stamp[ 'quantity' ] ) : $bundled_item->get_quantity();
				$quantity             = $item_quantity * $bundle_quantity;
				$product_id           = $bundled_item->product_id;
				$bundled_product_type = $bundled_item->product->get_type();

				if ( 'simple' === $bundled_product_type || 'subscription' === $bundled_product_type ) {

					$variation_id = '';
					$variations   = array();

				} elseif ( 'variable' === $bundled_product_type || 'variable-subscription' === $bundled_product_type ) {

					if ( isset( $bundled_item_stamp[ 'variation_id' ] ) && isset( $bundled_item_stamp[ 'attributes' ] ) ) {
						$variation_id = $bundled_item_stamp[ 'variation_id' ];
						$variations   = $bundled_item_stamp[ 'attributes' ];
					} else {
						throw new Exception( sprintf( __( 'The requested configuration of &quot;%s&quot; cannot be purchased at the moment.', 'ultimatewoo-pro' ), $bundle->get_title() ) );
						return false;
					}
				}

				/**
				 * 'woocommerce_bundled_item_cart_data' filter.
				 *
				 * An opportunity to copy/load child cart item data from the parent cart item data array.
				 *
				 * @param  array  $bundled_item_cart_data
				 * @param  array  $cart_item_data
				 */
				$bundled_item_cart_data = apply_filters( 'woocommerce_bundled_item_cart_data', $bundled_item_cart_data, $cart_item_data );

				/**
				 * 'woocommerce_bundled_item_before_add_to_cart' action.
				 *
				 * @param  int    $product_id
				 * @param  int    $quantity
				 * @param  int    $variation_id
				 * @param  array  $variations
				 * @param  array  $bundled_item_cart_data
				 */
				do_action( 'woocommerce_bundled_item_before_add_to_cart', $product_id, $quantity, $variation_id, $variations, $bundled_item_cart_data );

				// Add to cart.
				$bundled_item_cart_key = $this->bundled_add_to_cart( $bundle_id, $product_id, $quantity, $variation_id, $variations, $bundled_item_cart_data );

				if ( $bundled_item_cart_key && ! in_array( $bundled_item_cart_key, WC()->cart->cart_contents[ $bundle_cart_key ][ 'bundled_items' ] ) ) {
					WC()->cart->cart_contents[ $bundle_cart_key ][ 'bundled_items' ][] = $bundled_item_cart_key;
				}

				/**
				 * 'woocommerce_bundled_item_before_add_to_cart' action.
				 *
				 * @param  int    $product_id
				 * @param  int    $quantity
				 * @param  int    $variation_id
				 * @param  array  $variations
				 * @param  array  $bundled_item_cart_data
				 */
				do_action( 'woocommerce_bundled_item_after_add_to_cart', $product_id, $quantity, $variation_id, $variations, $bundled_item_cart_data );
			}
		}
	}

	/**
	 * When a bundle is static-priced, the price of all bundled items is set to 0.
	 * When the shipping mode is set to "bundled", all bundled items are marked as virtual when they are added to the cart.
	 * Otherwise, the container itself is a virtual product in the first place.
	 *
	 * @param  array   $cart_item
	 * @param  string  $cart_key
	 * @return array
	 */
	public function add_cart_item_filter( $cart_item, $cart_key ) {

		if ( wc_pb_is_bundle_container_cart_item( $cart_item ) ) {

			$cart_item = $this->set_bundle_container_cart_item( $cart_item );

		} elseif ( $bundle_container_item = wc_pb_get_bundled_cart_item_container( $cart_item ) ) {

			$bundle          = $bundle_container_item[ 'data' ];
			$bundled_item_id = $cart_item[ 'bundled_item_id' ];

			if ( $bundle->has_bundled_item( $bundled_item_id ) ) {
				$cart_item = $this->set_bundled_cart_item( $cart_item, $bundle );
			}
		}

		return $cart_item;
	}

	/**
	 * Reload all bundle-related session data in the cart.
	 *
	 * @param  array  $cart_item
	 * @param  array  $item_session_values
	 * @param  array  $cart_item_key
	 * @return array
	 */
	public function get_cart_item_from_session( $cart_item, $item_session_values, $cart_item_key ) {

		if ( ! isset( $cart_item[ 'stamp' ] ) && isset( $item_session_values[ 'stamp' ] ) ) {
			$cart_item[ 'stamp' ] = $item_session_values[ 'stamp' ];
		}

		if ( wc_pb_is_bundle_container_cart_item( $item_session_values ) ) {

			if ( 'bundle' === $cart_item[ 'data' ]->get_type() ) {

				if ( ! isset( $cart_item[ 'bundled_items' ] ) ) {
					$cart_item[ 'bundled_items' ] = $item_session_values[ 'bundled_items' ];
				}

				$cart_item = $this->set_bundle_container_cart_item( $cart_item );

			} else {

				if ( isset( $cart_item[ 'bundled_items' ] ) ) {
					unset( $cart_item[ 'bundled_items' ] );
				}
			}
		}

		if ( wc_pb_maybe_is_bundled_cart_item( $item_session_values ) ) {

			// Load 'bundled_by' field.
			if ( ! isset( $cart_item[ 'bundled_by' ] ) ) {
				$cart_item[ 'bundled_by' ] = $item_session_values[ 'bundled_by' ];
			}

			if ( ! isset( $cart_item[ 'bundled_item_id' ] ) ) {
				$cart_item[ 'bundled_item_id' ] = $item_session_values[ 'bundled_item_id' ];
			}

			if ( $bundle_container_item = wc_pb_get_bundled_cart_item_container( $item_session_values ) ) {

				$bundle = $bundle_container_item[ 'data' ];

				if ( 'bundle' === $bundle->get_type() && $bundle->has_bundled_item( $cart_item[ 'bundled_item_id' ] ) ) {
					$cart_item = $this->set_bundled_cart_item( $cart_item, $bundle );
				}
			}
		}

		return $cart_item;
	}

	/**
	 * Ensure any cart items marked as bundled have a valid parent. If not, silently remove them.
	 *
	 * @param  WC_Cart  $cart
	 * @return void
	 */
	public function cart_loaded_from_session( $cart ) {

		$cart_contents = $cart->cart_contents;

		if ( ! empty( $cart_contents ) ) {

			foreach ( $cart_contents as $cart_item_key => $cart_item_values ) {
				if ( wc_pb_maybe_is_bundled_cart_item( $cart_item_values ) ) {
					$container_item = wc_pb_get_bundled_cart_item_container( $cart_item_values );
					if ( ! $container_item || ! isset( $container_item[ 'bundled_items' ] ) || ! is_array( $container_item[ 'bundled_items' ] ) || ! in_array( $cart_item_key, $container_item[ 'bundled_items' ] ) ) {
						unset( WC()->cart->cart_contents[ $cart_item_key ] );
					} elseif ( isset( $cart_item_values[ 'bundled_item_id' ] ) && 'bundle' === $container_item[ 'data' ]->get_type() && ! $container_item[ 'data' ]->has_bundled_item( $cart_item_values[ 'bundled_item_id' ] ) ) {
						unset( WC()->cart->cart_contents[ $cart_item_key ] );
					}
				}
			}
		}
	}

	/**
	 * Bundled items can't be removed individually from the cart - this hides the remove buttons.
	 *
	 * @param  string  $link
	 * @param  string  $cart_item_key
	 * @return string
	 */
	public function cart_item_remove_link( $link, $cart_item_key ) {

		$cart_item = WC()->cart->cart_contents[ $cart_item_key ];

		if ( wc_pb_is_bundled_cart_item( $cart_item ) ) {
			return '';
		}

		return $link;
	}

	/**
	 * Bundled item quantities can't be changed individually. When adjusting quantity for the container item, the bundled products must follow.
	 *
	 * @param  int     $quantity
	 * @param  string  $cart_item_key
	 * @return int
	 */
	public function cart_item_quantity( $quantity, $cart_item_key ) {

		$cart_item = WC()->cart->cart_contents[ $cart_item_key ];

		if ( wc_pb_is_bundled_cart_item( $cart_item ) ) {
			$quantity = $cart_item[ 'quantity' ];
		}

		return $quantity;
	}

	/**
	 * Keep quantities between bundled products and container items in sync.
	 *
	 * @param  string   $cart_item_key
	 * @param  integer  $quantity
	 * @return void
	 */
	public function update_quantity_in_cart( $cart_item_key, $quantity = 0 ) {

		if ( ! empty( WC()->cart->cart_contents[ $cart_item_key ] ) ) {

			if ( $quantity == 0 || $quantity < 0 ) {
				$quantity = 0;
			} else {
				$quantity = WC()->cart->cart_contents[ $cart_item_key ][ 'quantity' ];
			}

			if ( wc_pb_is_bundle_container_cart_item( WC()->cart->cart_contents[ $cart_item_key ] ) ) {

				// Get bundled cart items.
				$bundled_cart_items = wc_pb_get_bundled_cart_items( WC()->cart->cart_contents[ $cart_item_key ] );

				// Change the quantity of all bundled items that belong to the same bundle config.
				if ( ! empty( $bundled_cart_items ) ) {
					foreach ( $bundled_cart_items as $key => $value ) {
						if ( $value[ 'data' ]->is_sold_individually() && $quantity > 0 ) {
							WC()->cart->set_quantity( $key, 1, false );
						} elseif ( isset( $value[ 'stamp' ] ) && isset( $value[ 'bundled_item_id' ] ) && isset( $value[ 'stamp' ][ $value[ 'bundled_item_id' ] ] ) ) {
							$bundle_quantity = $value[ 'stamp' ][ $value[ 'bundled_item_id' ] ][ 'quantity' ];
							WC()->cart->set_quantity( $key, $quantity * $bundle_quantity, false );
						}
					}
				}
			}
		}
	}

	/**
	 * Re-inialize cart item data for re-ordering purchased orders.
	 *
	 * @param  array     $cart_item_data
	 * @param  array     $order_item
	 * @param  WC_Order  $order
	 * @return array
	 */
	public function order_again( $cart_item_data, $order_item, $order ) {

		if ( wc_pb_is_bundle_container_order_item( $order_item ) && isset( $order_item[ 'stamp' ] ) && false === WC_PB()->compatibility->is_composited_order_item( $order_item, $order ) ) {

			$cart_item_data[ 'stamp' ]         = maybe_unserialize( $order_item[ 'stamp' ] );
			$cart_item_data[ 'bundled_items' ] = array();

			/*
			 * Make sure the 'stamp' array keys correspond to valid bundled item ids (might have changed).
			 */

			$bundle_id = $order_item[ 'product_id' ];

			// Get a map of bundled item ids => product ids for this bundle.
			$bundle_db_map = WC_PB_DB::query_bundled_items( array(
				'return'    => 'id=>product_id',
				'bundle_id' => $bundle_id
			) );

			foreach ( $cart_item_data[ 'stamp' ] as $item_id => $item_stamp_data ) {

				$bundled_product_id = isset( $item_stamp_data[ 'product_id' ] ) ? $item_stamp_data[ 'product_id' ] : '';

				if ( ! $bundled_product_id ) {
					continue;
				}

				// If bundled item ID looks invalid, search for the bundled product ID in the $bundle_db_map array to find the correct bundled item ID.
				if ( ! array_key_exists( $item_id, $bundle_db_map ) || ! is_numeric( $item_id ) ) {
					foreach ( $bundle_db_map as $map_item_id => $map_bundled_product_id ) {
						if ( absint( $map_bundled_product_id ) === absint( $bundled_product_id ) ) {
							$cart_item_data[ 'stamp' ][ $map_item_id ] = $cart_item_data[ 'stamp' ][ $item_id ];
							unset( $cart_item_data[ 'stamp' ][ $item_id ] );
							unset( $bundle_db_map[ $map_item_id ] );
						}
					}
				}
			}

		} elseif ( $bundle_order_item = wc_pb_get_bundled_order_item_container( $order_item, $order ) ) {

			$item_id = isset( $order_item[ 'bundled_item_id' ] ) ? $order_item[ 'bundled_item_id' ] : '';

			if ( $item_id ) {

				$bundle_id          = $bundle_order_item[ 'product_id' ];
				$bundled_product_id = $order_item[ 'product_id' ];

				// Get a map of bundled item ids => product ids for this bundle.
				$bundle_db_map = WC_PB_DB::query_bundled_items( array(
					'return'    => 'id=>product_id',
					'bundle_id' => $bundle_id
				) );

				// If bundled item ID looks invalid, search for the bundled product ID in the $bundle_db_map array to find the correct bundled item ID.
				if ( ! array_key_exists( $item_id, $bundle_db_map ) || ! is_numeric( $item_id ) ) {
					foreach ( $bundle_db_map as $map_item_id => $map_bundled_product_id ) {
						if ( absint( $map_bundled_product_id ) === absint( $bundled_product_id ) ) {
							$item_id = $map_item_id;
						}
					}
				}

				// Copy all cart data of the "orphaned" bundled cart item into the one already added along with the container.
				foreach ( WC()->cart->cart_contents as $check_cart_item_key => $check_cart_item_data ) {

					if ( isset( $check_cart_item_data[ 'bundled_item_id' ] ) && absint( $item_id ) === absint( $check_cart_item_data[ 'bundled_item_id' ] ) ) {

						$existing_bundled_cart_item_data = $check_cart_item_data;
						$existing_bundled_cart_item_key  = $check_cart_item_key;

						foreach ( $cart_item_data as $key => $value ) {
							if ( ! isset( $existing_bundled_cart_item_data[ $key ] ) ) {
								WC()->cart->cart_contents[ $existing_bundled_cart_item_key ][ $key ] = $value;
							}
						}
					}
				}
			}

			// Identify this as a cart item that is originally part of a bundle. Will be removed since it has already been added to the cart by its container.
			$cart_item_data[ 'is_order_again_bundled' ] = 'yes';
		}

		return $cart_item_data;
	}

	/**
	 * Modify the front-end price of bundled items and container items depending on their pricing setup.
	 *
	 * @param  double  $price
	 * @param  array   $values
	 * @param  string  $cart_item_key
	 * @return string
	 */
	public function cart_item_price_html( $price, $values, $cart_item_key ) {

		if ( $bundle_container_item = wc_pb_get_bundled_cart_item_container( $values ) ) {

			$bundled_item_id = $values[ 'bundled_item_id' ];

			if ( $bundled_item = $bundle_container_item[ 'data' ]->get_bundled_item( $bundled_item_id ) ) {

				if ( false === $bundled_item->is_priced_individually() && $values[ 'line_subtotal' ] == 0 ) {
					$price = '';
				} elseif ( WC_PB()->compatibility->is_composited_cart_item( $bundle_container_item ) && $values[ 'line_subtotal' ] == 0 ) {
					$price = '';
				} elseif ( false === $bundled_item->is_price_visible( 'cart' ) ) {
					$price = '';
				}
			}

		} elseif ( wc_pb_is_bundle_container_cart_item( $values ) ) {

			if ( $values[ 'data' ]->contains( 'priced_individually' ) && $values[ 'line_subtotal' ] == 0 ) {
				$price = '';
			}
		}

		return $price;
	}

	/**
	 * Modify the front-end subtotal of bundled items and container items depending on their pricing setup.
	 *
	 * @param  string  $subtotal
	 * @param  array   $values
	 * @param  string  $cart_item_key
	 * @return string
	 */
	public function item_subtotal( $subtotal, $values, $cart_item_key ) {

		if ( $bundle_container_item_key = wc_pb_get_bundled_cart_item_container( $values, WC()->cart->cart_contents, true ) ) {

			$bundle_container_item = WC()->cart->cart_contents[ $bundle_container_item_key ];
			$bundled_item_id       = $values[ 'bundled_item_id' ];

			if ( $bundled_item = $bundle_container_item[ 'data' ]->get_bundled_item( $bundled_item_id ) ) {

				if ( false === $bundled_item->is_price_visible( 'cart' ) || false === $bundled_item->is_priced_individually() || WC_PB()->compatibility->is_composited_cart_item( $bundle_container_item ) ) {
					$subtotal = '';
				} else {
					/**
					 * Controls whether to include bundled cart item subtotals in the container cart item subtotal.
					 *
					 * @param  boolean  $add
					 * @param  array    $container_cart_item
					 * @param  string   $container_cart_item_key
					 */
					if ( apply_filters( 'woocommerce_add_bundled_cart_item_subtotals', true, $bundle_container_item, $bundle_container_item_key ) ) {
						$subtotal = sprintf( _x( '%1$s: %2$s', 'bundled product subtotal', 'ultimatewoo-pro' ), __( 'Subtotal', 'ultimatewoo-pro' ), $subtotal );
					}

					$subtotal = '<span class="bundled-product-subtotal">' . $subtotal . '</span>';
				}
			}

		} elseif ( wc_pb_is_bundle_container_cart_item( $values ) ) {

			/** Documented right above. Look up. See? */
			if ( apply_filters( 'woocommerce_add_bundled_cart_item_subtotals', true, $values, $cart_item_key ) ) {

				$tax_display_cart    = get_option( 'woocommerce_tax_display_cart' );
				$bundled_item_keys   = wc_pb_get_bundled_cart_items( $values, WC()->cart->cart_contents, true );
				$bundled_items_price = 0.0;
				$bundle_price        = 'excl' === $tax_display_cart ? $values[ 'line_subtotal' ] : $values[ 'line_subtotal' ] + $values[ 'line_subtotal_tax' ];

				foreach ( $bundled_item_keys as $bundled_item_key ) {

					if ( ! isset( WC()->cart->cart_contents[ $bundled_item_key ] ) ) {
						continue;
					}

					$item_values = WC()->cart->cart_contents[ $bundled_item_key ];
					$item_id     = $item_values[ 'bundled_item_id' ];
					$product     = $item_values[ 'data' ];

					$bundled_item_price   = 'excl' === $tax_display_cart ? $item_values[ 'line_subtotal' ] : $item_values[ 'line_subtotal' ] + $item_values[ 'line_subtotal_tax' ];
					$bundled_items_price += (double) $bundled_item_price;
				}

				$subtotal = $this->format_product_subtotal( $values[ 'data' ], (double) $bundle_price + $bundled_items_price );
			}
		}

		return $subtotal;
	}

	/**
	 * Remove bundled cart items with parent.
	 *
	 * @param  string   $cart_item_key
	 * @param  WC_Cart  $cart
	 * @return void
	 */
	public function cart_item_removed( $cart_item_key, $cart ) {

		if ( wc_pb_is_bundle_container_cart_item( $cart->removed_cart_contents[ $cart_item_key ] ) ) {

			$bundled_item_cart_keys = wc_pb_get_bundled_cart_items( $cart->removed_cart_contents[ $cart_item_key ], $cart->cart_contents, true );

			foreach ( $bundled_item_cart_keys as $bundled_item_cart_key ) {

				$remove = $cart->cart_contents[ $bundled_item_cart_key ];
				$cart->removed_cart_contents[ $bundled_item_cart_key ] = $remove;

				unset( $cart->cart_contents[ $bundled_item_cart_key ] );

				/** WC core action. */
				do_action( 'woocommerce_cart_item_removed', $bundled_item_cart_key, $cart );
			}
		}
	}

	/**
	 * Restore bundled cart items with parent.
	 *
	 * @param  string   $cart_item_key
	 * @param  WC_Cart  $cart
	 * @return void
	 */
	public function cart_item_restored( $cart_item_key, $cart ) {

		if ( wc_pb_is_bundle_container_cart_item( $cart->cart_contents[ $cart_item_key ] ) ) {

			$bundled_item_cart_keys = wc_pb_get_bundled_cart_items( $cart->cart_contents[ $cart_item_key ], $cart->removed_cart_contents, true );

			foreach ( $bundled_item_cart_keys as $bundled_item_cart_key ) {

				$remove = $cart->removed_cart_contents[ $bundled_item_cart_key ];
				$cart->cart_contents[ $bundled_item_cart_key ] = $remove;

				unset( $cart->removed_cart_contents[ $bundled_item_cart_key ] );

				/** WC core action. */
				do_action( 'woocommerce_cart_item_restored', $bundled_item_cart_key, $cart );
			}
		}
	}

	/**
	 * Shipping fix - add the value of any children that are not shipped individually to the container value and, optionally, add their weight to the container weight, as well.
	 *
	 * @param  array  $packages
	 * @return array
	 */
	public function cart_shipping_packages( $packages ) {

		if ( ! empty( $packages ) ) {

			foreach ( $packages as $package_key => $package ) {

				if ( ! empty( $package[ 'contents' ] ) ) {
					foreach ( $package[ 'contents' ] as $cart_item_key => $cart_item_data ) {

						if ( wc_pb_is_bundle_container_cart_item( $cart_item_data ) ) {

							$bundle     = unserialize( serialize( $cart_item_data[ 'data' ] ) );
							$bundle_qty = $cart_item_data[ 'quantity' ];

							/*
							 * Container needs shipping: Aggregate the prices of any children that are physically packaged in their parent and, optionally, aggregate their weights into the parent, as well.
							 */

							if ( $bundle->needs_shipping() ) {

								$bundled_weight = 0.0;
								$bundled_value  = 0.0;

								$bundle_totals = array(
									'line_subtotal'     => $cart_item_data[ 'line_subtotal' ],
									'line_total'        => $cart_item_data[ 'line_total' ],
									'line_subtotal_tax' => $cart_item_data[ 'line_subtotal_tax' ],
									'line_tax'          => $cart_item_data[ 'line_tax' ],
									'line_tax_data'     => $cart_item_data[ 'line_tax_data' ]
								);

								foreach ( wc_pb_get_bundled_cart_items( $cart_item_data, WC()->cart->cart_contents, true ) as $child_item_key ) {

									$child_cart_item_data   = WC()->cart->cart_contents[ $child_item_key ];
									$bundled_product        = $child_cart_item_data[ 'data' ];
									$bundled_product_qty    = $child_cart_item_data[ 'quantity' ];
									$bundled_product_value  = WC_PB_Core_Compatibility::get_prop( $bundled_product, 'bundled_value', 'shipping' );
									$bundled_product_weight = WC_PB_Core_Compatibility::get_prop( $bundled_product, 'bundled_weight', 'shipping' );

									// Aggregate price of physically packaged child item - already converted to virtual.

									if ( $bundled_product_value ) {

										$bundled_value += $bundled_product_value * $bundled_product_qty;

										$bundle_totals[ 'line_subtotal' ]     += $child_cart_item_data[ 'line_subtotal' ];
										$bundle_totals[ 'line_total' ]        += $child_cart_item_data[ 'line_total' ];
										$bundle_totals[ 'line_subtotal_tax' ] += $child_cart_item_data[ 'line_subtotal_tax' ];
										$bundle_totals[ 'line_tax' ]          += $child_cart_item_data[ 'line_tax' ];

										$packages[ $package_key ][ 'contents_cost' ] += $child_cart_item_data[ 'line_total' ];

										$child_item_line_tax_data = $child_cart_item_data[ 'line_tax_data' ];

										$bundle_totals[ 'line_tax_data' ][ 'total' ]    = array_merge( $bundle_totals[ 'line_tax_data' ][ 'total' ], $child_item_line_tax_data[ 'total' ] );
										$bundle_totals[ 'line_tax_data' ][ 'subtotal' ] = array_merge( $bundle_totals[ 'line_tax_data' ][ 'subtotal' ], $child_item_line_tax_data[ 'subtotal' ] );
									}

									// Aggregate weight of physically packaged child item - already converted to virtual.

									if ( $bundled_product_weight ) {
										$bundled_weight += $bundled_product_weight * $bundled_product_qty;
									}
								}

								if ( $bundled_value > 0 ) {
									$bundle_price = WC_PB_Core_Compatibility::get_prop( $bundle, 'price', 'edit' );
									WC_PB_Core_Compatibility::set_prop( $bundle, 'price', (double) $bundle_price + $bundled_value / $bundle_qty );
								}

								$packages[ $package_key ][ 'contents' ][ $cart_item_key ] = array_merge( $cart_item_data, $bundle_totals );

								if ( $bundled_weight > 0 ) {
									$bundle_weight = WC_PB_Core_Compatibility::get_prop( $bundle, 'weight', 'edit' );
									WC_PB_Core_Compatibility::set_prop( $bundle, 'weight', (double) $bundle_weight + $bundled_weight / $bundle_qty );
								}

								$packages[ $package_key ][ 'contents' ][ $cart_item_key ][ 'data' ] = $bundle;
							}
						}
					}
				}
			}
		}

		return $packages;
	}

	/**
	 * Inherit coupon validity from parent:
	 *
	 * - Coupon is invalid for bundled item if parent is excluded.
	 * - Coupon is valid for bundled item if valid for parent, unless bundled item is excluded.
	 *
	 * @param  bool        $valid
	 * @param  WC_Product  $product
	 * @param  WC_Coupon   $coupon
	 * @param  array       $cart_item
	 * @return boolean
	 */
	public function coupon_is_valid_for_product( $valid, $product, $coupon, $cart_item ) {

		if ( ! empty( WC()->cart ) ) {

			if ( $container_cart_item = wc_pb_get_bundled_cart_item_container( $cart_item ) ) {

				$bundle    = $container_cart_item[ 'data' ];
				$bundle_id = $container_cart_item[ 'product_id' ];

				/**
				 * 'woocommerce_bundles_inherit_coupon_validity' filter.
				 *
				 * Uset this to prevent coupon valididty inheritance for bundled products.
				 *
				 * @param  boolean     $inherit
				 * @param  WC_Product  $product
				 * @param  WC_Coupon   $coupon
				 * @param  array       $cart_item
				 * @param  array       $container_cart_item
				 */
				if ( apply_filters( 'woocommerce_bundles_inherit_coupon_validity', true, $product, $coupon, $cart_item, $container_cart_item ) ) {

					$product_id = WC_PB_Core_Compatibility::get_id( $product );
					$parent_id  = WC_PB_Core_Compatibility::get_parent_id( $product );

					$excluded_product_ids        = WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ? $coupon->get_excluded_product_ids() : $coupon->exclude_product_ids;
					$excluded_product_categories = WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ? $coupon->get_excluded_product_categories() : $coupon->exclude_product_categories;
					$excludes_sale_items         = WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ? $coupon->get_exclude_sale_items() : ( 'yes' === $coupon->exclude_sale_items );

					if ( $valid ) {

						$parent_excluded = false;

						// Parent ID excluded from the discount.
						if ( sizeof( $excluded_product_ids ) > 0 ) {
							if ( in_array( $bundle_id, $excluded_product_ids ) ) {
								$parent_excluded = true;
							}
						}

						// Parent category excluded from the discount.
						if ( sizeof( $excluded_product_categories ) > 0 ) {

							$product_cats = WC_PB_Core_Compatibility::wc_get_product_cat_ids( $bundle_id );

							if ( sizeof( array_intersect( $product_cats, $excluded_product_categories ) ) > 0 ) {
								$parent_excluded = true;
							}
						}

						// Sale Items excluded from discount and parent on sale.
						if ( $excludes_sale_items ) {

							$product_ids_on_sale = wc_get_product_ids_on_sale();

							if ( in_array( $bundle_id, $product_ids_on_sale, true ) ) {
								$parent_excluded = true;
							}
						}

						if ( $parent_excluded ) {
							$valid = false;
						}

					} else {

						$bundled_product_excluded = false;

						// Bundled product ID excluded from the discount.
						if ( sizeof( $excluded_product_ids ) > 0 ) {
							if ( in_array( $product_id, $excluded_product_ids ) || ( $parent_id && in_array( $parent_id, $excluded_product_ids ) ) ) {
								$bundled_product_excluded = true;
							}
						}

						// Bundled product category excluded from the discount.
						if ( sizeof( $excluded_product_categories ) > 0 ) {

							$product_cats = $parent_id ? WC_PB_Core_Compatibility::wc_get_product_cat_ids( $parent_id ) : WC_PB_Core_Compatibility::wc_get_product_cat_ids( $product_id );

							if ( sizeof( array_intersect( $product_cats, $excluded_product_categories ) ) > 0 ) {
								$bundled_product_excluded = true;
							}
						}

						// Bundled product on sale and sale items excluded from discount.
						if ( $excludes_sale_items ) {

							$product_ids_on_sale = wc_get_product_ids_on_sale();

							if ( in_array( $product_id, $product_ids_on_sale ) || ( $parent_id && in_array( $parent_id, $product_ids_on_sale ) ) ) {
								$bundled_product_excluded = true;
							}
						}

						if ( ! $bundled_product_excluded && $coupon->is_valid_for_product( $bundle, $container_cart_item ) ) {
							$valid = true;
						}
					}
				}
			}
		}

		return $valid;
	}

	/**
	 * Treat bundled subs as non-sub products when bundled in statically-priced bundles.
	 * Method: Do not add product in any subscription cart group.
	 *
	 * @return bool
	 */
	public function add_subcription_filter() {
		add_filter( 'woocommerce_is_subscription', array( $this, 'is_subscription_filter' ), 100, 3 );
	}

	/**
	 * Treat bundled subs as non-sub products when bundled in statically-priced bundles.
	 * Method: Do not add product in any subscription cart group.
	 *
	 * @return bool
	 */
	public function remove_subcription_filter() {
		remove_filter( 'woocommerce_is_subscription', array( $this, 'is_subscription_filter' ), 100, 3 );
	}

	/**
	 * Treat bundled subs as non-sub products when bundled in statically-priced bundles.
	 *
	 * @param  bool        $is_sub
	 * @param  string      $product_id
	 * @param  WC_Product  $product
	 * @return bool
	 */
	public function is_subscription_filter( $is_sub, $product_id, $product ) {
		if ( is_object( $product ) && isset( $product->wc_pb_block_sub ) && 'yes' === $product->wc_pb_block_sub ) {
			$is_sub = false;
		}

		return $is_sub;
	}

	/**
	 * Deprecated class methods.
	 *
	 * @deprecated
	 */
	public function get_bundled_cart_item_container( $cart_item ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', 'wc_pb_get_bundled_cart_item_container()' );
		return wc_pb_get_bundled_cart_item_container( $cart_item );
	}
	public function is_bundled_cart_item( $cart_item ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', 'wc_pb_is_bundled_cart_item()' );
		return wc_pb_is_bundled_cart_item( $cart_item );
	}
	public function is_bundle_container_cart_item( $cart_item ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', 'wc_pb_is_bundle_container_cart_item()' );
		return wc_pb_is_bundle_container_cart_item( $cart_item );
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	public function woo_bundles_validation( $add, $product_id, $product_quantity, $variation_id = '', $variations = array(), $cart_item_data = array() ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::validate_add_to_cart()' );
		return $this->validate_add_to_cart( $add, $product_id, $product_quantity, $variation_id, $variations, $cart_item_data );
	}
	public function woo_bundles_add_cart_item_data( $cart_item_data, $product_id ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::add_cart_item_data()' );
		return $this->add_cart_item_data( $cart_item_data, $product_id );
	}
	public function woo_bundles_add_bundle_to_cart( $bundle_cart_key, $bundle_id, $bundle_quantity, $variation_id, $variation, $cart_item_data ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::bundle_add_to_cart()' );
		return $this->bundle_add_to_cart( $bundle_cart_key, $bundle_id, $bundle_quantity, $variation_id, $variation, $cart_item_data );
	}
	public function woo_bundles_add_cart_item_filter( $cart_item, $cart_key ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::add_cart_item_filter()' );
		return $this->add_cart_item_filter( $cart_item, $cart_key );
	}
	public function woo_bundles_get_cart_data_from_session( $cart_item, $item_session_values, $cart_item_key ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::get_cart_item_from_session()' );
		return $this->get_cart_item_from_session( $cart_item, $item_session_values, $cart_item_key );
	}
	public function woo_bundles_cart_item_quantity( $quantity, $cart_item_key ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::cart_item_quantity()' );
		return $this->cart_item_quantity( $quantity, $cart_item_key );
	}
	public function woo_bundles_cart_item_remove_link( $link, $cart_item_key ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::cart_item_remove_link()' );
		return $this->cart_item_remove_link( $link, $cart_item_key );
	}
	public function woo_bundles_update_quantity_in_cart( $cart_item_key, $quantity = 0 ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::update_quantity_in_cart()' );
		return $this->update_quantity_in_cart( $cart_item_key, $quantity );
	}
	public function woo_bundles_order_again( $cart_item_data, $order_item, $order ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::order_again()' );
		return $this->order_again( $cart_item_data, $order_item, $order );
	}
	public function woo_bundles_cart_item_price_html( $price, $values, $cart_item_key ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::cart_item_price_html()' );
		return $this->cart_item_price_html( $price, $values, $cart_item_key );
	}
	public function woo_bundles_item_subtotal( $subtotal, $values, $cart_item_key ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::item_subtotal()' );
		return $this->item_subtotal( $subtotal, $values, $cart_item_key );
	}
	public function woo_bundles_cart_item_removed( $cart_item_key, $cart ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::cart_item_removed()' );
		return cart_item_removed( $cart_item_key, $cart );
	}
	public function woo_bundles_cart_item_restored( $cart_item_key, $cart ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::cart_item_restored()' );
		return $this->cart_item_restored( $cart_item_key, $cart );
	}
	public function woo_bundles_shipping_packages_fix( $packages ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::cart_shipping_packages()' );
		return $this->cart_shipping_packages( $packages );
	}
	public function woo_bundles_coupon_validity( $valid, $product, $coupon, $cart_item ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::coupon_is_valid_for_product()' );
		return $this->coupon_is_valid_for_product( $valid, $product, $coupon, $cart_item );
	}
}
