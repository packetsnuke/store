<?php
/**
 * Legacy WC_CP_Order class (WC <= 2.6)
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    3.9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Composite order-related filters and functions.
 *
 * @class 	 WC_CP_Order
 * @version  3.8.3
 */
class WC_CP_Order {

	/**
	 * Flag to prevent 'woocommerce_order_get_items' filters from modifying original order line items when calling 'WC_Order::get_items'.
	 * @var boolean
	 */
	public static $override_order_items_filters = false;

	/**
	 * The single instance of the class.
	 * @var WC_CP_Order
	 *
	 * @since 3.7.0
	 */
	protected static $_instance = null;

	/**
	 * Main WC_CP_Order instance.
	 *
	 * Ensures only one instance of WC_CP_Order is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_CP_Order
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
	 * Construct, man.
	 */
	public function __construct() {

		// Filter price output shown in cart, review-order & order-details templates.
		add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'order_item_subtotal' ), 10, 3 );

		// Virtual composite containers should not affect order status unless one of their children does.
		add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'container_item_needs_processing' ), 10, 3 );

		// Modify order items to include composite meta.
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'add_order_item_meta' ), 10, 3 );

		// Hide composite configuration metadata in order line items.
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hide_order_item_meta' ) );

		// Filter order item count in the front-end.
		add_filter( 'woocommerce_get_item_count',  array( $this, 'order_item_count' ), 10, 3 );

		// Filter admin dashboard item count and classes.
		if ( is_admin() ) {
			add_filter( 'woocommerce_admin_order_item_count',  array( $this, 'order_item_count_string' ), 10, 2 );
			add_filter( 'woocommerce_admin_html_order_item_class',  array( $this, 'html_order_item_class' ), 10, 3 );
			add_filter( 'woocommerce_admin_order_item_class',  array( $this, 'html_order_item_class' ), 10, 3 );
		}

		// Modify product while completing payment - @see 'get_processing_product_from_item()' and 'container_item_needs_processing()'.
		add_action( 'woocommerce_pre_payment_complete', array( $this, 'apply_get_processing_product_from_item_filter' ) );
		add_action( 'woocommerce_payment_complete', array( $this, 'remove_processing_get_product_from_item_filter' ) );
	}

	/*
	|--------------------------------------------------------------------------
	| API functions.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Validates a composite configuration and adds all associated line items to an order. Relies on specifying a composite configuration array with all necessary data.
	 * The configuration array is passed as a 'configuration' key of the $args method argument. Example:
	 *
	 *    $args = array(
	 *        'configuration' => array(
	 *            134567890 => array(                       // ID of the component.
	 *                'quantity'          => 2,             // Qty of composited product, will fall back to min.
	 *                'discount'          => 50.0,          // Composited product discount, defaults to the defined value.
	 *                'attributes'        => array(         // Array of selected variation attribute names, sanitized.
	 *                    'attribute_color' => 'black',
	 *                    'attribute_size'  => 'medium'
	 *                 ),
	 *                'variation_id'      => 43,            // ID of chosen variation, if applicable.
	 *                'args'              => array()        // Custom composited item args to pass into 'WC_Order::add_product()', such as a 'totals' array.
	 *            )
	 *        )
	 *    );
	 *
	 * Returns the container order item ID if sucessful, or false otherwise.
	 *
	 * Note: Container/child order item totals are calculated without taxes, based on their pricing setup.
	 * - Container item totals can be overridden by passing a 'totals' array in $args, as with 'WC_Order::add_product()'.
	 * - Composited item totals can be overridden in the 'configuration' array, as shown in the example above.
	 *
	 *
	 * @param  WC_Product_Bundle  $bundle
	 * @param  WC_Order           $order
	 * @param  integer            $quantity
	 * @param  array              $args
	 * @return integer|WP_Error
	 */
	public function add_composite_to_order( $composite, $order, $quantity = 1, $args = array() ) {

		$added_to_order = false;

		$args = wp_parse_args( $args, array(
			'configuration' => array(),
			'silent'        => true
		) );

		if ( $composite && 'composite' === $composite->get_type() ) {

			$configuration = $args[ 'configuration' ];

			if ( WC_CP()->cart->validate_composite_configuration( $composite, $quantity, $configuration, 'add-to-order' ) ) {

				// Add container item.
				$container_order_item_id = $order->add_product( $composite, $quantity, $args );
				$added_to_order          = $container_order_item_id;

				// Unique hash to use in place of the cart item ID.
				$container_item_hash = md5( $container_order_item_id );

				// Add components.
				$components = $composite->get_components();

				// Hashes of children.
				$component_hashes = array();

				$bundled_weight = 0.0;

				if ( ! empty( $components ) ) {
					foreach ( $components as $component_id => $component ) {

						$component_configuration         = isset( $configuration[ $component_id ] ) ? $configuration[ $component_id ] : array();
						$component_quantity              = isset( $component_configuration[ 'quantity' ] ) ? absint( $component_configuration[ 'quantity' ] ) : $component->get_quantity();
						$component_option_id             = isset( $component_configuration[ 'product_id' ] ) ? $component_configuration[ 'product_id' ] : '';
						$component_option_product_id     = isset( $component_configuration[ 'variation_id' ] ) ? $component_configuration[ 'variation_id' ] : $component_configuration[ 'product_id' ];
						$component_option_variation_data = isset( $component_configuration[ 'attributes' ] ) ? $component_configuration[ 'attributes' ] : array();
						$component_discount              = isset( $component_configuration[ 'discount' ] ) ? wc_format_decimal( $component_configuration[ 'discount' ] ) : $component->get_discount();
						$component_args                  = isset( $component_configuration[ 'args' ] ) ? $component_configuration[ 'args' ] : array();

						if ( $component->is_optional() ) {
							if ( '0' === $component_option_id ) {
								$component_quantity = 0;
							}
						}

						if ( 0 === $component_quantity ) {
							continue;
						}

						$component_option              = $component->get_option( $component_option_id );
						$component_option_product_type = $component_option->get_product()->get_type();
						$component_option_product      = 'variable' === $component_option_product_type ? wc_get_product( $component_option_product_id ) : $component_option->get_product();

						if ( $component_option->is_priced_individually() ) {
							if ( $component_discount ) {
								$component_args[ 'totals' ] = array(
									'subtotal'     => isset( $component_args[ 'totals' ][ 'subtotal' ] ) ? $component_args[ 'totals' ][ 'subtotal' ] : $component_option_product->get_price_excluding_tax( $component_quantity * $quantity ) * ( 1 - (float) $component_discount / 100 ),
									'total'        => isset( $component_args[ 'totals' ][ 'total' ] ) ? $component_args[ 'totals' ][ 'total' ] : $component_option_product->get_price_excluding_tax( $component_quantity * $quantity ) * ( 1 - (float) $component_discount / 100 ),
									'subtotal_tax' => isset( $component_args[ 'totals' ][ 'subtotal_tax' ] ) ? $component_args[ 'totals' ][ 'subtotal_tax' ] : 0,
									'tax'          => isset( $component_args[ 'totals' ][ 'tax' ] ) ? $component_args[ 'totals' ][ 'tax' ] : 0
								);
							}
						} else {
							$component_args[ 'totals' ] = array(
								'subtotal'     => isset( $component_args[ 'totals' ][ 'subtotal' ] ) ? $component_args[ 'totals' ][ 'subtotal' ] : 0,
								'total'        => isset( $component_args[ 'totals' ][ 'total' ] ) ? $component_args[ 'totals' ][ 'total' ] : 0,
								'subtotal_tax' => isset( $component_args[ 'totals' ][ 'subtotal_tax' ] ) ? $component_args[ 'totals' ][ 'subtotal_tax' ] : 0,
								'tax'          => isset( $component_args[ 'totals' ][ 'tax' ] ) ? $component_args[ 'totals' ][ 'tax' ] : 0
							);
						}

						// Args to pass into 'add_product()'.
						$component_args[ 'variation' ] = 'variable' === $component_option_product_type ? $component_option_variation_data : array();

						// Add bundled item.
						$component_order_item_id = $order->add_product( $component_option_product, $component_quantity * $quantity, $component_args );

						if ( ! $component_order_item_id ) {
							continue;
						}

						/*
						 * Add bundled order item meta.
						 */

						wc_add_order_item_meta( $component_order_item_id, '_composite_parent', $container_item_hash );
						wc_add_order_item_meta( $component_order_item_id, '_composite_data', $configuration );
						wc_add_order_item_meta( $component_order_item_id, '_composite_item', $component_id );

						if ( false === $component->is_subtotal_visible( 'orders' ) ) {
							wc_add_order_item_meta( $order_item_id, '_component_subtotal_hidden', 'yes' );
						}

						// Pricing setup.
						wc_add_order_item_meta( $component_order_item_id, '_component_priced_individually', $component_option->is_priced_individually() ? 'yes' : 'no' );

						// Unique hash to use in place of the cart item ID.
						$component_hash     = md5( $component_order_item_id );
						$component_hashes[] = $component_hash;

						wc_add_order_item_meta( $component_order_item_id, '_composite_cart_key', $component_hash );

						// Shipping setup.
						$shipped_individually = false;

						if ( $component_option_product->needs_shipping() && $component_option->is_shipped_individually( $component_option_product ) ) {
							$shipped_individually = true;
						} elseif ( $component_option_product->needs_shipping() ) {
							/** Hook documented in 'WC_CP_Cart::set_composited_cart_item()'. */
							if ( apply_filters( 'woocommerce_composited_product_has_bundled_weight', false, $component_option_product, $component_id, $composite ) ) {
								$bundled_weight += (double) $component_option_product->get_weight() * $component_quantity;
							}
						}

						wc_add_order_item_meta( $component_order_item_id, '_composite_item_needs_shipping', $shipped_individually ? 'yes' : 'no' );

						do_action( 'woocommerce_composite_component_add_to_order', $component_order_item_id, $order, $component_option_product, $component_quantity, $component, $composite, $quantity, $component_args, $args );
					}
				}

				// Add container order item meta.
				wc_add_order_item_meta( $container_order_item_id, '_composite_data', $configuration );
				wc_add_order_item_meta( $container_order_item_id, '_composite_children', $component_hashes );
				wc_add_order_item_meta( $container_order_item_id, '_composite_cart_key', $container_item_hash );

				if ( $composite->needs_shipping() ) {
					wc_add_order_item_meta( $container_order_item_id, '_composite_weight', (double) $composite->get_weight() + $bundled_weight );
				}

			} else {

				$error_data = array( 'notices' => wc_get_notices( 'error' ) );
				$message    = __( 'The submitted composite configuration could not be added to this order.', 'ultimatewoo-pro' );

				if ( $args[ 'silent' ] ) {
					wc_clear_notices();
				}

				$added_to_order = new WP_Error( 'woocommerce_composite_configuration_invalid', $message, $error_data );
			}

		} else {
			$message        = __( 'A composite with this ID does not exist.', 'ultimatewoo-pro' );
			$added_to_order = new WP_Error( 'woocommerce_composite_invalid', $message );
		}

		return $added_to_order;
	}

	/**
	 * Modifies composite parent/child order items depending on their shipping setup. Reconstructs an accurate representation of a composite for shipping purposes.
	 * Used in combination with 'get_product_from_item', right below.
	 *
	 * Adds the totals of "packaged" items to the container totals and creates a container "Contents" meta field to provide a description of the included items.
	 *
	 * @param  array     $items
	 * @param  WC_Order  $order
	 * @return array
	 */
	public function get_order_items( $items, $order ) {

		// Nobody likes infinite loops.
		if ( self::$override_order_items_filters ) {
			return $items;
		}

		// Right?
		self::$override_order_items_filters = true;

		$return_items = array();

		foreach ( $items as $item_id => $item ) {

			if ( wc_cp_is_composite_container_order_item( $item ) ) {

				/*
				 * Add the totals of "packaged" items to the container totals and create a container "Contents" meta field to provide a description of the included products.
				 */
				$product = wc_get_product( $item[ 'product_id' ] );

				if ( $product && $product->needs_shipping() && $child_items = wc_cp_get_composited_order_items( $item, $items ) ) {

					if ( ! empty( $child_items ) ) {

						// Aggregate contents.
						$contents = array();

						// Aggregate prices.
						$bundle_totals = array(
							'line_subtotal'     => $item[ 'line_subtotal' ],
							'line_total'        => $item[ 'line_total' ],
							'line_subtotal_tax' => $item[ 'line_subtotal_tax' ],
							'line_tax'          => $item[ 'line_tax' ],
							'line_tax_data'     => maybe_unserialize( $item[ 'line_tax_data' ] )
						);

						foreach ( $child_items as $child_item_id => $child_item ) {

							// If the child is "packaged" in its parent...
							if ( isset( $child_item[ 'composite_item_needs_shipping' ] ) && 'no' === $child_item[ 'composite_item_needs_shipping' ] ) {

								$child_id = ! empty( $child_item[ 'variation_id' ] ) ? $child_item[ 'variation_id' ] : $child_item[ 'product_id' ];
								$child    = wc_get_product( $child_id );

								if ( ! $child || ! $child->needs_shipping() ) {
									continue;
								}

								/*
								 * Add item into a new container "Contents" meta.
								 */

								$sku = $child->get_sku();

								if ( ! $sku ) {
									$sku = '#' . $child_id;
								}

								$meta = '';

								if ( ! empty( $child_item[ 'item_meta' ] ) ) {

									$item_meta      = new WC_Order_Item_Meta( $child_item );
									$formatted_meta = $item_meta->display( true, true, '_', ', ' );

									if ( $formatted_meta ) {
										$meta = $formatted_meta;
									}
								}

								$product_title         = WC_CP_Helpers::format_product_title( $child->get_title(), '', $meta, true );
								$product_description   = '';
								$component_title       = '';
								$component_description = '';

								if ( ! empty( $child_item[ 'composite_item' ] ) ) {

									$component_id = $child_item[ 'composite_item' ];

									if ( $component = $product->get_component( $component_id ) ) {
										$component_title = $component->get_title( true );
									}
								}

								if ( $component_title ) {
									$component_description = sprintf( __( '%1$s &ndash; Quantity: %2$s, SKU: %3$s', 'ultimatewoo-pro' ), $product_title, $child_item[ 'qty' ], $sku );
								} else {
									$product_description = sprintf( __( 'Quantity: %1$s, SKU: %2$s', 'ultimatewoo-pro' ), $child_item[ 'qty' ], $sku );
								}

								$contents[] = array(
									'title'       => $component_title ? $component_title : $product_title,
									'description' => $component_description ? $component_description : $product_description
								);

								/*
								 * Add item totals to the container totals.
								 */

								$bundle_totals[ 'line_subtotal' ]     += $child_item[ 'line_subtotal' ];
								$bundle_totals[ 'line_total' ]        += $child_item[ 'line_total' ];
								$bundle_totals[ 'line_subtotal_tax' ] += $child_item[ 'line_subtotal_tax' ];
								$bundle_totals[ 'line_tax' ]          += $child_item[ 'line_tax' ];

								$child_item_line_tax_data = maybe_unserialize( $child_item[ 'line_tax_data' ] );

								$bundle_totals[ 'line_tax_data' ][ 'total' ]    = array_merge( $bundle_totals[ 'line_tax_data' ][ 'total' ], $child_item_line_tax_data[ 'total' ] );
								$bundle_totals[ 'line_tax_data' ][ 'subtotal' ] = array_merge( $bundle_totals[ 'line_tax_data' ][ 'subtotal' ], $child_item_line_tax_data[ 'subtotal' ] );
							}
						}

						$item[ 'line_tax_data' ] = serialize( $bundle_totals[ 'line_tax_data' ] );
						$item                    = array_merge( $item, $bundle_totals );

						// Create a meta field for each bundled item.
						if ( ! empty( $contents ) ) {

							$keys     = array_keys( $item[ 'item_meta_array' ] );
							$last_key = end( $keys );
							$loop     = 1;

							foreach ( $contents as $contained ) {
								$entry        = new stdClass();
								$entry->key   = $contained[ 'title' ];
								$entry->value = $contained[ 'description' ];

								$item[ 'item_meta_array' ][ $last_key + $loop ] = $entry;
								$item[ 'item_meta' ][ $contained[ 'title' ] ]   = $contained[ 'description' ];
								$loop++;
							}
						}
					}
				}
			} elseif ( wc_cp_is_composited_order_item( $item, $items ) ) {

				$product_id = ! empty( $item[ 'variation_id' ] ) ? $item[ 'variation_id' ]  : $item[ 'product_id' ];
				$product    = wc_get_product( $product_id );

				if ( $product && $product->needs_shipping() && isset( $item[ 'composite_item_needs_shipping' ] ) && 'no' === $item[ 'composite_item_needs_shipping' ] ) {

					$item[ 'line_subtotal' ]     = 0;
					$item[ 'line_total' ]        = 0;
					$item[ 'line_subtotal_tax' ] = 0;
					$item[ 'line_tax' ]          = 0;
					$item[ 'line_tax_data' ]     = serialize( array( 'total' => array(), 'subtotal' => array() ) );
				}
			}

			$return_items[ $item_id ] = $item;
		}

		// End of my awesome infinite looping prevention mechanism.
		self::$override_order_items_filters = false;

		return $return_items;
	}

	/**
	 * Modifies parent/child order products in order to reconstruct an accurate representation of a composite for shipping purposes:
	 *
	 * - If it's a container, then its weight is modified to include the weight of "packaged" children.
	 * - If a child is "packaged" inside its parent, then it is marked as virtual.
	 *
	 * Used in combination with 'get_order_items', right above.
	 *
	 * @param  WC_Product  $product
	 * @param  array       $item
	 * @param  WC_Order    $order
	 * @return WC_Product
	 */
	public function get_product_from_item( $product, $item, $order ) {

		if ( ! $product ) {
			return $product;
		}

		// If it's a container item...
		if ( wc_cp_is_composite_container_order_item( $item ) ) {

			if ( $product->needs_shipping() ) {

				// If it needs shipping, modify its weight to include the weight of all "packaged" items.
				if ( isset( $item[ 'composite_weight' ] ) ) {
					$product->weight = $item[ 'composite_weight' ];
				}

				// Override SKU with kit/bundle SKU if needed.
				$child_items         = wc_cp_get_composited_order_items( $item, $order );
				$packaged_products   = array();
				$packaged_quantities = array();

				// Find items shipped in the container:
				foreach ( $child_items as $child_item ) {

					if ( isset( $child_item[ 'composite_item_needs_shipping' ] ) && 'no' === $child_item[ 'composite_item_needs_shipping' ] ) {

						$child_product    = wc_get_product( $child_item[ 'product_id' ] );
						$child_product_id = ! empty( $child_item[ 'variation_id' ] ) ? $child_item[ 'variation_id' ] : $child_item[ 'product_id' ];

						if ( ! $child_product || ! $child_product->needs_shipping() ) {
							continue;
						}

						$packaged_products[]                       = $child_product;
						$packaged_quantities[ $child_product_id ] = $child_item[ 'qty' ];
					}
				}

				$product->sku = apply_filters( 'woocommerce_composite_sku_from_order_item', $product->sku, $product, $item, $order, $packaged_products, $packaged_quantities );
			}

		// If it's a child item...
		} elseif ( wc_cp_is_composited_order_item( $item, $order ) ) {

			if ( $product->needs_shipping() ) {

				// If it's "packaged" in its container, set it to virtual.
				if ( isset( $item[ 'composite_item_needs_shipping' ] ) && 'no' === $item[ 'composite_item_needs_shipping' ] ) {
					$product->virtual = 'yes';
				}
			}
		}

		return $product;
	}

	/**
	 * Modify product objects using order item meta in order to construct an accurate value/volume/weight representation of a composite for shipping purposes.
	 *
	 * Virtual containers/children are assigned a zero weight and tiny dimensions in order to maintain the value of the associated item in shipments:
	 *
	 * - If a bundled item is not shipped individually (virtual), its value must be included to ensure an accurate calculation of shipping costs (value/insurance).
	 * - If a composite is not shipped as a physical item (virtual), it may have a non-zero value that also needs to be included to ensure an accurate calculation of shipping costs (value/insurance).
	 *
	 * In both cases, the workaround is to assign a tiny weight and miniscule dimensions to the non-shipped order items, in order to:
	 *
	 * - ensure that they are included in the exported data, by having 'needs_shipping' return 'true', but also
	 * - minimize the impact of their inclusion on shipping costs.
	 *
	 * @param  WC_Product  $product
	 * @param  array       $item
	 * @param  WC_Order    $order
	 * @return WC_Product
	 */
	public function get_legacy_shipstation_product_from_item( $product, $item, $order ) {

		// If it's a container item...
		if ( wc_cp_is_composite_container_order_item( $item ) ) {

			if ( $product->needs_shipping() ) {

				if ( isset( $item[ 'composite_weight' ] ) ) {
					$product->weight = $item[ 'composite_weight' ];
				}

			} else {

				// Process container.
				if ( $child_items = wc_cp_get_composited_order_items( $item, $order ) ) {

					$non_virtual_child_exists = false;

					// Virtual container converted to non-virtual with zero weight and tiny dimensions if it has non-virtual bundled children.
					foreach ( $child_items as $child_item_id => $child_item ) {
						if ( isset( $child_item[ 'composite_item_needs_shipping' ] ) && 'yes' === $child_item[ 'composite_item_needs_shipping' ] ) {
							$non_virtual_child_exists = true;
							break;
						}
					}

					if ( $non_virtual_child_exists ) {
						$product->virtual = 'no';
					}
				}

				$product->weight = $product->weight > 0 ? 0.0 : $product->weight;
				$product->length = $product->length > 0 ? 0.001 : $product->length;
				$product->height = $product->height > 0 ? 0.001 : $product->height;
				$product->width  = $product->width > 0 ? 0.001 : $product->width;
			}

		// If it's a child item...
		} elseif ( wc_cp_is_composited_order_item( $item, $order ) ) {

			if ( $product->needs_shipping() ) {

				// If it's "packaged" in its container, set it to virtual.
				if ( isset( $item[ 'composite_item_needs_shipping' ] ) && 'no' === $item[ 'composite_item_needs_shipping' ] ) {

					$product->weight = $product->weight > 0 ? 0.0 : $product->weight;
					$product->length = $product->length > 0 ? 0.001 : $product->length;
					$product->height = $product->height > 0 ? 0.001 : $product->height;
					$product->width  = $product->width > 0 ? 0.001 : $product->width;
				}
			}
		}

		return $product;
	}


	/*
	|--------------------------------------------------------------------------
	| Filter hooks.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Modify the subtotal of order items depending on their pricing setup.
	 *
	 * @param  string    $subtotal
	 * @param  array     $item
	 * @param  WC_Order  $order
	 * @return string
	 */
	public function order_item_subtotal( $subtotal, $item, $order ) {

		// If it's a composited item...
		if ( $parent_item = wc_cp_get_composited_order_item_container( $item, $order ) ) {

			$item_priced_individually = isset( $item[ 'component_priced_individually' ] ) ? $item[ 'component_priced_individually' ] : false;
			$item_price_hidden        = isset( $item[ 'component_subtotal_hidden' ] ) ? $item[ 'component_subtotal_hidden' ] : false;

			// Back-compat.
			if ( false === $item_priced_individually ) {
				$item_priced_individually = isset( $parent_item[ 'per_product_pricing' ] ) ? $parent_item[ 'per_product_pricing' ] : get_post_meta( $parent_item[ 'product_id' ], '_bto_per_product_pricing', true );
			}

			if ( 'no' === $item_priced_individually || 'yes' === $item_price_hidden ) {
				$subtotal = '';
			} else {

				/**
				 * Controls whether to include composited order item subtotals in the container order item subtotal.
				 *
				 * @param  boolean   $add
				 * @param  array     $container_order_item
				 * @param  WC_Order  $order
				 */
				if ( apply_filters( 'woocommerce_add_composited_order_item_subtotals', true, $parent_item, $order ) ) {
					$subtotal = sprintf( _x( '%1$s: %2$s', 'component subtotal', 'ultimatewoo-pro' ), __( 'Option subtotal', 'ultimatewoo-pro' ), $subtotal );
				}

				$subtotal = '<span class="component-subtotal">' . $subtotal . '</span>';
			}
		}

		// If it's a parent item...
		if ( wc_cp_is_composite_container_order_item( $item ) ) {

			/** Documented right above. Look up. See? */
			$add_subtotals_into_container = apply_filters( 'woocommerce_add_composited_order_item_subtotals', true, $item, $order );

			if ( ! isset( $item[ 'subtotal_updated' ] ) && $add_subtotals_into_container ) {

				$children = wc_cp_get_composited_order_items( $item, $order, false, true );

				if ( ! empty( $children ) ) {

					foreach ( $children as $child ) {
						$item[ 'line_subtotal' ]     += $child[ 'line_subtotal' ];
						$item[ 'line_subtotal_tax' ] += $child[ 'line_subtotal_tax' ];
					}

					$item[ 'subtotal_updated' ] = 'yes';

					$subtotal = $order->get_formatted_line_subtotal( $item );
				}
			}
		}

		return $subtotal;
	}

	/**
	 * Composite Containers should not affect order status - let it be decided by composited items only.
	 *
	 * @param  bool        $is_needed
	 * @param  WC_Product  $product
	 * @param  int         $order_id
	 * @return bool
	 */
	public function container_item_needs_processing( $is_needed, $product, $order_id ) {

		if ( $product->is_type( 'composite' ) && isset( $product->composite_needs_processing ) && 'no' === $product->composite_needs_processing ) {
			$is_needed = false;
		}

		return $is_needed;
	}

	/**
	 * Hides composite metadata.
	 *
	 * @param  array  $hidden
	 * @return array
	 */
	public function hide_order_item_meta( $hidden ) {

		$current_meta = array( '_composite_parent', '_composite_item', '_composite_children', '_composite_cart_key', '_composite_item_needs_shipping', '_composite_weight', '_component_priced_individually', '_components_need_processing', '_component_subtotal_hidden' );
		$legacy_meta  = array(  '_per_product_pricing', '_per_product_shipping', '_bundled_shipping', '_bundled_weight' );

		return array_merge( $hidden, $current_meta, $legacy_meta );
	}

	/**
	 * Adds composite info to order items.
	 *
	 * @param  int 		$order_item_id
	 * @param  array 	$cart_item_values
	 * @param  string 	$cart_item_key
	 * @return void
	 */
	public function add_order_item_meta( $order_item_id, $cart_item_values, $cart_item_key ) {

		if ( wc_cp_is_composite_container_cart_item( $cart_item_values ) ) {
			wc_add_order_item_meta( $order_item_id, '_composite_children', $cart_item_values[ 'composite_children' ] );
		}

		if ( wc_cp_is_composited_cart_item( $cart_item_values ) ) {

			wc_add_order_item_meta( $order_item_id, '_composite_parent', $cart_item_values[ 'composite_parent' ] );
			wc_add_order_item_meta( $order_item_id, '_composite_item', $cart_item_values[ 'composite_item' ] );

			if ( $composite_container_item = wc_cp_get_composited_cart_item_container( $cart_item_values ) ) {

				$composite    = $composite_container_item[ 'data' ];
				$component_id = $cart_item_values[ 'composite_item' ];
				$product_id   = $cart_item_values[ 'product_id' ];

				if ( $component = $composite->get_component( $component_id ) ) {
					if ( false === $component->is_subtotal_visible( 'orders' ) ) {
						wc_add_order_item_meta( $order_item_id, '_component_subtotal_hidden', 'yes' );
					}
				}

				if ( $component_option = $composite->get_component_option( $component_id, $product_id ) ) {
					wc_add_order_item_meta( $order_item_id, '_component_priced_individually', $component_option->is_priced_individually() ? 'yes' : 'no' );
				}
			}
		}

		if ( isset( $cart_item_values[ 'composite_data' ] ) ) {

			wc_add_order_item_meta( $order_item_id, '_composite_cart_key', $cart_item_key );
			wc_add_order_item_meta( $order_item_id, '_composite_data', $cart_item_values[ 'composite_data' ] );

			/*
			 * Store shipping data - useful when exporting order content.
			 */

			$needs_shipping = $cart_item_values[ 'data' ]->needs_shipping() ? 'yes' : 'no';

			// If it's a physical child item, add a meta fild to indicate whether it is shipped individually.
			if ( wc_cp_is_composited_cart_item( $cart_item_values ) ) {

				wc_add_order_item_meta( $order_item_id, '_composite_item_needs_shipping', $needs_shipping );

			} elseif ( wc_cp_is_composite_container_cart_item( $cart_item_values ) ) {

				// If it's a physical container item, grab its aggregate weight from the package data.
				if ( 'yes' === $needs_shipping ) {

					$packaged_item_values = false;

					foreach ( WC()->cart->get_shipping_packages() as $package ) {
						if ( isset( $package[ 'contents' ][ $cart_item_key ] ) ) {
							$packaged_item_values = $package[ 'contents' ][ $cart_item_key ];
							break;
						}
					}

					if ( ! empty( $packaged_item_values ) ) {
						$bundled_weight = $packaged_item_values[ 'data' ]->get_weight();
						wc_add_order_item_meta( $order_item_id, '_composite_weight', $bundled_weight );
					}

				// If it's a virtual container item, look at its children to see if any of them needs processing.
				} elseif ( false === $this->components_need_processing( $cart_item_values ) ) {
					wc_add_order_item_meta( $order_item_id, '_components_need_processing', 'no' );
				}
			}
		}
	}

	/**
	 * Given a virtual composite container cart item, find if any of its children need processing.
	 *
	 * @since  3.7.0
	 *
	 * @param  array  $item_values
	 * @return mixed
	 */
	private function components_need_processing( $item_values ) {

		$child_keys        = wc_cp_get_composited_cart_items( $item_values, WC()->cart->cart_contents, true, true );
		$processing_needed = false;

		if ( ! empty( $child_keys ) && is_array( $child_keys ) ) {
			foreach ( $child_keys as $child_key ) {
				$child_product = WC()->cart->cart_contents[ $child_key ][ 'data' ];
				if ( false === $child_product->is_downloadable() || false === $child_product->is_virtual() ) {
					$processing_needed = true;
					break;
				}
			}
		}

		return $processing_needed;
	}

	/**
	 * Filters the reported number of order items - counts only composite containers.
	 *
	 * @param  int       $count
	 * @param  string    $type
	 * @param  WC_Order  $order
	 * @return int
	 */
	public function order_item_count( $count, $type, $order ) {

		$subtract = 0;

		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			foreach ( $order->get_items() as $item ) {
				if ( wc_cp_is_composited_order_item( $item, $order ) ) {
					$subtract += $item[ 'qty' ];
				}
			}
		}

		return $count - $subtract;
	}

	/**
	 * Filters the string of order item count.
	 * Include bundled items as a suffix.
	 *
	 * @param  int       $count
	 * @param  WC_Order  $order
	 * @return int
	 */
	public function order_item_count_string( $count, $order ) {

		$add = 0;

		foreach ( $order->get_items() as $item ) {
			if ( wc_cp_is_composited_order_item( $item, $order ) ) {
				$add += $item[ 'qty' ];
			}
		}

		if ( $add > 0 ) {
			$count = sprintf( __( '%1$s, %2$s composited', 'ultimatewoo-pro' ), $count, $add );
		}

		return $count;
	}

	/**
	 * Filters the order item admin class.
	 *
	 * @param  string  $class
	 * @param  array   $item
	 * @return string
	 */
	public function html_order_item_class( $class, $item, $order = false ) {

		if ( wc_cp_maybe_is_composited_cart_item( $item ) ) {
			if ( false === $order || wc_cp_is_composited_order_item( $item, $order ) ) {
				$class .= ' composited_item';
			}
		}

		return $class;
	}

	/**
	 * Activates the 'get_processing_product_from_item' filter below.
	 *
	 * @param  string  $order_id
	 * @return void
	 */
	public function apply_get_processing_product_from_item_filter( $order_id ) {
		add_filter( 'woocommerce_get_product_from_item', array( $this, 'get_processing_product_from_item' ), 10, 3 );
	}

	/**
	 * Deactivates the 'get_processing_product_from_item' filter below.
	 *
	 * @param  string  $order_id
	 * @return void
	 */
	public function remove_processing_get_product_from_item_filter( $order_id ) {
		remove_filter( 'woocommerce_get_product_from_item', array( $this, 'get_processing_product_from_item' ), 10, 3 );
	}

	/**
	 * Filters 'get_product_from_item' to add data used for 'woocommerce_order_item_needs_processing'.
	 *
	 * @param  WC_Product  $product
	 * @param  array       $item
	 * @param  WC_Order    $order
	 * @return WC_Product
	 */
	public function get_processing_product_from_item( $product, $item, $order ) {

		if ( ! empty( $product ) && $product->is_virtual() ) {

			// Process container.
			if ( $child_items = wc_cp_get_composited_order_items( $item, $order ) ) {

				// If no child requires processing and the container is virtual, it should not require processing - @see 'container_item_needs_processing()'.
				if ( $product->is_virtual() && sizeof( $child_items ) > 0 ) {
					if ( isset( $item[ 'components_need_processing' ] ) && 'no' === $item[ 'components_need_processing' ] ) {
						$product->composite_needs_processing = 'no';
					}
				}
			}
		}

		return $product;
	}


	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	public static function get_composite_children( $item, $order, $return_type = 'item', $strict_mode = false ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'wc_cp_get_composited_order_items()' );
		$return_ids = 'id' === $return_type;
		$deep_mode  = ! $strict_mode;
		return wc_cp_get_composited_order_items( $item, $order, $return_ids, $deep_mode );
	}
	public static function get_composite_parent( $item, $order, $return_type = 'item' ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'wc_cp_get_composited_order_item_container()' );
		$return_id = 'id' === $return_type;
		return wc_cp_get_composited_order_item_container( $item, $order, $return_id );
	}
	public function get_composited_order_item_container( $item, $order ) {
		_deprecated_function( __METHOD__ . '()', '3.5.0', 'wc_cp_get_composited_order_item_container()' );
		return wc_cp_get_composited_order_item_container( $item, $order );
	}
}
