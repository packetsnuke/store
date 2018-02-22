<?php
/**
 * Legacy WC_PB_Order class (WC <= 2.6)
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    5.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Bundle order-related functions and filters.
 *
 * @class    WC_PB_Order
 * @version  5.2.1
 */
class WC_PB_Order {

	/**
	 * Flag to prevent 'woocommerce_order_get_items' filters from modifying original order line items when calling 'WC_Order::get_items'.
	 *
	 * @var boolean
	 */
	public static $override_order_items_filters = false;

	/**
	 * @var WC_PB_Order - the single instance of the class.
	 *
	 * @since 5.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main WC_PB_Order instance.
	 *
	 * Ensures only one instance of WC_PB_Order is loaded or can be loaded.
	 *
	 * @static
	 *
	 * @since  5.0.0
	 *
	 * @return WC_PB_Order
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
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'ultimatewoo-pro' ), '4.11.4' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 5.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'ultimatewoo-pro' ), '4.11.4' );
	}

	/**
	 * Setup order class.
	 */
	protected function __construct() {

		// Filter price output shown in cart, review-order & order-details templates.
		add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'order_item_subtotal' ), 10, 3 );

		// Virtual bundle containers should not affect order status unless one of their children does.
		add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'container_item_needs_processing' ), 10, 3 );

		// Modify order items to include bundle meta.
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'add_order_item_meta' ), 10, 3 );

		// Hide bundle configuration metadata in order line items.
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hidden_order_item_meta' ) );

		// Filter order item count in the front-end.
		add_filter( 'woocommerce_get_item_count',  array( $this, 'order_item_count' ), 10, 3 );

		// Filter admin dashboard item count and classes.
		if ( is_admin() ) {
			add_filter( 'woocommerce_admin_order_item_count',  array( $this, 'order_item_count_string' ), 10, 2 );
			add_filter( 'woocommerce_admin_html_order_item_class',  array( $this, 'html_order_item_class' ), 10, 3 );
			add_filter( 'woocommerce_admin_order_item_class',  array( $this, 'html_order_item_class' ), 10, 3 );
		}

		// Modify product while completing payment - @see 'get_processing_product_from_item()' and 'container_item_needs_processing()'.
		add_action( 'woocommerce_pre_payment_complete', array( $this, 'apply_get_product_from_item_filter' ) );
		add_action( 'woocommerce_payment_complete', array( $this, 'remove_get_product_from_item_filter' ) );
	}

	/*
	|--------------------------------------------------------------------------
	| API functions.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Validates a bundle configuration and adds all associated line items to an order. Relies on specifying a bundle configuration array with all necessary data.
	 * The configuration array is passed as a 'configuration' key of the $args method argument. Example:
	 *
	 *    $args = array(
	 *        'configuration' => array(
	 *            134 => array(                             // ID of bundled item.
	 *                'quantity'          => 2,             // Qty of bundled product, will fall back to min.
	 *                'discount'          => 50.0,          // Bundled product discount, defaults to the defined value.
	 *                'title'             => 'Test',        // Bundled product title, include only if overriding.
	 *                'optional_selected' => 'yes',         // If the bundled item is optional, indicate if chosen or not.
	 *                'attributes'        => array(         // Array of selected variation attribute names, sanitized.
	 *                    'attribute_color' => 'black',
	 *                    'attribute_size'  => 'medium'
	 *                 ),
	 *                'variation_id'      => 43,            // ID of chosen variation, if applicable.
	 *                'args'              => array()        // Custom bundled item args to pass into 'WC_Order::add_product()', such as a 'totals' array.
	 *            )
	 *        )
	 *    );
	 *
	 * Returns the container order item ID if sucessful, or false otherwise.
	 *
	 * Note: Container/child order item totals are calculated without taxes, based on their pricing setup.
	 * - Container item totals can be overridden by passing a 'totals' array in $args, as with 'WC_Order::add_product()'.
	 * - Bundled item totals can be overridden in the 'configuration' array, as shown in the example above.
	 *
	 *
	 * @param  WC_Product_Bundle  $bundle
	 * @param  WC_Order           $order
	 * @param  integer            $quantity
	 * @param  array              $args
	 * @return integer|WP_Error
	 */
	public function add_bundle_to_order( $bundle, $order, $quantity = 1, $args = array() ) {

		$added_to_order = false;

		$args = wp_parse_args( $args, array(
			'configuration' => array(),
			'silent'        => true
		) );

		if ( $bundle && 'bundle' === $bundle->get_type() ) {

			$configuration = $args[ 'configuration' ];

			if ( WC_PB()->cart->validate_bundle_configuration( $bundle, $quantity, $configuration, 'add-to-order' ) ) {

				// Add container item.
				$container_order_item_id = $order->add_product( $bundle, $quantity, $args );
				$added_to_order          = $container_order_item_id;

				// Unique hash to use in place of the cart item ID.
				$container_item_hash = md5( $container_order_item_id );

				// Add bundled items.
				$bundled_items = $bundle->get_bundled_items();

				// Hashes of children.
				$bundled_order_item_hashes = array();

				$bundled_weight = 0;

				if ( ! empty( $bundled_items ) ) {
					foreach ( $bundled_items as $bundled_item_id => $bundled_item ) {

						$bundled_item_configuration  = isset( $configuration[ $bundled_item_id ] ) ? $configuration[ $bundled_item_id ] : array();
						$bundled_item_quantity       = isset( $bundled_item_configuration[ 'quantity' ] ) ? absint( $bundled_item_configuration[ 'quantity' ] ) : $bundled_item->get_quantity();
						$bundled_product             = isset( $bundled_item_configuration[ 'variation_id' ] ) && in_array( $bundled_item->product->get_type(), array( 'variable', 'variable-subscription' ) ) ? wc_get_product( $bundled_item_configuration[ 'variation_id' ] ) : $bundled_item->product;
						$bundled_item_variation_data = isset( $bundled_item_configuration[ 'attributes' ] ) && in_array( $bundled_item->product->get_type(), array( 'variable', 'variable-subscription' ) ) ? $bundled_item_configuration[ 'attributes' ] : array();
						$bundled_item_discount       = isset( $bundled_item_configuration[ 'discount' ] ) ? wc_format_decimal( $bundled_item_configuration[ 'discount' ] ) : $bundled_item->get_discount();
						$bundled_item_args           = isset( $bundled_item_configuration[ 'args' ] ) ? $bundled_item_configuration[ 'args' ] : array();

						if ( $bundled_item->is_optional() ) {

							$optional_selected = isset( $bundled_item_configuration[ 'optional_selected' ] ) && 'yes' === $bundled_item_configuration[ 'optional_selected' ] ? 'yes' : 'no';

							if ( 'no' === $optional_selected ) {
								$bundled_item_quantity = 0;
							}
						}

						if ( 0 === $bundled_item_quantity ) {
							continue;
						}

						if ( $bundled_item->is_priced_individually() ) {
							if ( $bundled_item_discount ) {
								$bundled_item_args[ 'totals' ] = array(
									'subtotal'     => isset( $bundled_item_args[ 'totals' ][ 'subtotal' ] ) ? $bundled_item_args[ 'totals' ][ 'subtotal' ] : $bundled_product->get_price_excluding_tax( $bundled_item_quantity * $quantity ) * ( 1 - (float) $bundled_item_discount / 100 ),
									'total'        => isset( $bundled_item_args[ 'totals' ][ 'total' ] ) ? $bundled_item_args[ 'totals' ][ 'total' ] : $bundled_product->get_price_excluding_tax( $bundled_item_quantity * $quantity ) * ( 1 - (float) $bundled_item_discount / 100 ),
									'subtotal_tax' => isset( $bundled_item_args[ 'totals' ][ 'subtotal_tax' ] ) ? $bundled_item_args[ 'totals' ][ 'subtotal_tax' ] : 0,
									'tax'          => isset( $bundled_item_args[ 'totals' ][ 'tax' ] ) ? $bundled_item_args[ 'totals' ][ 'tax' ] : 0
								);
							}
						} else {
							$bundled_item_args[ 'totals' ] = array(
								'subtotal'     => isset( $bundled_item_args[ 'totals' ][ 'subtotal' ] ) ? $bundled_item_args[ 'totals' ][ 'subtotal' ] : 0,
								'total'        => isset( $bundled_item_args[ 'totals' ][ 'total' ] ) ? $bundled_item_args[ 'totals' ][ 'total' ] : 0,
								'subtotal_tax' => isset( $bundled_item_args[ 'totals' ][ 'subtotal_tax' ] ) ? $bundled_item_args[ 'totals' ][ 'subtotal_tax' ] : 0,
								'tax'          => isset( $bundled_item_args[ 'totals' ][ 'tax' ] ) ? $bundled_item_args[ 'totals' ][ 'tax' ] : 0
							);
						}

						// Args to pass into 'add_product()'.
						$bundled_item_args[ 'variation' ] = $bundled_item_variation_data;

						// Add bundled item.
						$bundled_order_item_id = $order->add_product( $bundled_product, $bundled_item_quantity * $quantity, $bundled_item_args );

						if ( ! $bundled_order_item_id ) {
							continue;
						}

						/*
						 * Add bundled order item meta.
						 */

						wc_add_order_item_meta( $bundled_order_item_id, '_bundled_by', $container_item_hash );
						wc_add_order_item_meta( $bundled_order_item_id, '_stamp', $configuration );
						wc_add_order_item_meta( $bundled_order_item_id, '_bundled_item_id', $bundled_item_id );

						if ( false === $bundled_item->is_visible( 'order' ) ) {
							wc_add_order_item_meta( $bundled_order_item_id, '_bundled_item_hidden', 'yes' );
						}

						if ( false === $bundled_item->is_price_visible( 'order' ) ) {
							wc_add_order_item_meta( $bundled_order_item_id, '_bundled_item_price_hidden', 'yes' );
						}

						if ( $bundled_item->has_title_override() ) {
							wc_add_order_item_meta( $bundled_order_item_id, '_bundled_item_title', isset( $bundled_item_configuration[ 'title' ] ) ? $bundled_item_configuration[ 'title' ] : $bundled_item->get_raw_title() );
						}

						// Pricing setup.
						wc_add_order_item_meta( $bundled_order_item_id, '_bundled_item_priced_individually', $bundled_item->is_priced_individually() ? 'yes' : 'no' );

						// Unique hash to use in place of the cart item ID.
						$bundled_item_hash           = md5( $bundled_order_item_id );
						$bundled_order_item_hashes[] = $bundled_item_hash;

						wc_add_order_item_meta( $bundled_order_item_id, '_bundle_cart_key', $bundled_item_hash );

						// Shipping setup.
						$shipped_individually = false;

						if ( $bundled_product->needs_shipping() && $bundled_item->is_shipped_individually( $bundled_product ) ) {
							$shipped_individually = true;
						} elseif ( $bundled_product->needs_shipping() ) {
							/** Hook documented in 'WC_PB_Cart::set_bundled_cart_item()'. */
							if ( apply_filters( 'woocommerce_bundled_item_has_bundled_weight', false, $bundled_product, $bundled_item_id, $bundle ) ) {
								$bundled_weight += $bundled_product->get_weight() * $bundled_item_quantity;
							}
						}

						wc_add_order_item_meta( $bundled_order_item_id, '_bundled_item_needs_shipping', $shipped_individually ? 'yes' : 'no' );

						do_action( 'woocommerce_bundled_add_to_order', $bundled_order_item_id, $order, $bundled_product, $bundled_item_quantity, $bundled_item, $bundle, $quantity, $bundled_item_args, $args );
					}
				}

				// Add container order item meta.
				wc_add_order_item_meta( $container_order_item_id, '_stamp', $configuration );
				wc_add_order_item_meta( $container_order_item_id, '_bundled_items', $bundled_order_item_hashes );
				wc_add_order_item_meta( $container_order_item_id, '_bundle_cart_key', $container_item_hash );

				if ( $bundle->needs_shipping() ) {
					wc_add_order_item_meta( $container_order_item_id, '_bundle_weight', $bundle->get_weight() + $bundled_weight );
				}

			} else {

				$error_data = array( 'notices' => wc_get_notices( 'error' ) );
				$message    = __( 'The submitted bundle configuration could not be added to this order.', 'ultimatewoo-pro' );

				if ( $args[ 'silent' ] ) {
					wc_clear_notices();
				}

				$added_to_order = new WP_Error( 'woocommerce_bundle_configuration_invalid', $message, $error_data );
			}

		} else {
			$message        = __( 'A bundle with this ID does not exist.', 'ultimatewoo-pro' );
			$added_to_order = new WP_Error( 'woocommerce_bundle_invalid', $message );
		}

		return $added_to_order;
	}

	/**
	 * Modifies bundle parent/child order items depending on their shipping setup. Reconstructs an accurate representation of a bundle for shipping purposes.
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

			if ( wc_pb_is_bundle_container_order_item( $item ) ) {

				/*
				 * Add the totals of "packaged" items to the container totals and create a container "Contents" meta field to provide a description of the included products.
				 */
				$product = wc_get_product( $item[ 'product_id' ] );

				if ( $product && $product->needs_shipping() && $child_items = wc_pb_get_bundled_order_items( $item, $items ) ) {

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
							if ( isset( $child_item[ 'bundled_item_needs_shipping' ] ) && 'no' === $child_item[ 'bundled_item_needs_shipping' ] ) {

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

								$bundled_item_title = WC_PB_Helpers::format_product_title( $child_item[ 'name' ], '', $meta, true );

								$contents[] = array(
									'title'       => $bundled_item_title,
									'description' => sprintf( __( 'Quantity: %1$s, SKU: %2$s', 'ultimatewoo-pro' ), $child_item[ 'qty' ], $sku )
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

			} elseif ( wc_pb_is_bundled_order_item( $item, $items ) ) {

				$product_id = ! empty( $item[ 'variation_id' ] ) ? $item[ 'variation_id' ]  : $item[ 'product_id' ];
				$product    = wc_get_product( $product_id );

				if ( $product && $product->needs_shipping() && isset( $item[ 'bundled_item_needs_shipping' ] ) && 'no' === $item[ 'bundled_item_needs_shipping' ] ) {

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
	 * Modifies parent/child order products in order to reconstruct an accurate representation of a bundle for shipping purposes:
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

		// If it's a container item...
		if ( wc_pb_is_bundle_container_order_item( $item ) ) {

			if ( $product->needs_shipping() ) {

				// If it needs shipping, modify its weight to include the weight of all "packaged" items.
				if ( isset( $item[ 'bundle_weight' ] ) ) {
					$product->weight = $item[ 'bundle_weight' ];
				}

				// Override SKU with kit/bundle SKU if needed.
				$child_items         = wc_pb_get_bundled_order_items( $item, $order );
				$packaged_products   = array();
				$packaged_quantities = array();

				// Find items shipped in the container:
				foreach ( $child_items as $child_item ) {

					if ( isset( $child_item[ 'bundled_item_needs_shipping' ] ) && 'no' === $child_item[ 'bundled_item_needs_shipping' ] ) {

						$child_product_id = ! empty( $child_item[ 'variation_id' ] ) ? $child_item[ 'variation_id' ] : $child_item[ 'product_id' ];
						$child_product    = wc_get_product( $child_product_id );

						if ( ! $child_product || ! $child_product->needs_shipping() ) {
							continue;
						}

						$packaged_products[]                      = $child_product;
						$packaged_quantities[ $child_product_id ] = $child_item[ 'qty' ];
					}
				}

				$product->sku = apply_filters( 'woocommerce_bundle_sku_from_order_item', $product->sku, $product, $item, $order, $packaged_products, $packaged_quantities );
			}

		// If it's a child item...
		} elseif ( wc_pb_is_bundled_order_item( $item, $order ) ) {

			if ( $product->needs_shipping() ) {

				// If it's "packaged" in its container, set it to virtual.
				if ( isset( $item[ 'bundled_item_needs_shipping' ] ) && 'no' === $item[ 'bundled_item_needs_shipping' ] ) {
					$product->virtual = 'yes';
				}
			}
		}

		return $product;
	}

	/**
	 * Alternative shipping representation of a bundle that reconstructs an accurate value/volume/weight representation of a bundle for shipping purposes.
	 * Use this when each item needs to appear as a separate line item. Legacy method of exporting to ShipStation.
	 *
	 * Virtual containers/children are assigned a zero weight and tiny dimensions in order to maintain the value of the associated item in shipments:
	 *
	 * - If a bundled item is not shipped individually (virtual), its value must be included to ensure an accurate calculation of shipping costs (value/insurance).
	 * - If a bundle is not shipped as a physical item (virtual), it may have a non-zero value that also needs to be included to ensure an accurate calculation of shipping costs (value/insurance).
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
		if ( wc_pb_is_bundle_container_order_item( $item ) ) {

			if ( $product->needs_shipping() ) {

				if ( isset( $item[ 'bundle_weight' ] ) ) {
					$product->weight = $item[ 'bundle_weight' ];
				}

			} else {

				// Process container.
				if ( $child_items = wc_pb_get_bundled_order_items( $item, $order ) ) {

					$non_virtual_child_exists = false;

					// Virtual container converted to non-virtual with zero weight and tiny dimensions if it has non-virtual bundled children.
					foreach ( $child_items as $child_item_id => $child_item ) {
						if ( isset( $child_item[ 'bundled_item_needs_shipping' ] ) && 'yes' === $child_item[ 'bundled_item_needs_shipping' ] ) {
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
		} elseif ( wc_pb_is_bundled_order_item( $item, $order ) ) {

			if ( $product->needs_shipping() ) {

				// If it's "packaged" in its container, set it to virtual.
				if ( isset( $item[ 'bundled_item_needs_shipping' ] ) && 'no' === $item[ 'bundled_item_needs_shipping' ] ) {

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

		// If it's a bundled item...
		if ( $parent_item = wc_pb_get_bundled_order_item_container( $item, $order ) ) {

			$bundled_item_priced_individually = isset( $item[ 'bundled_item_priced_individually' ] ) ? $item[ 'bundled_item_priced_individually' ] : false;
			$bundled_item_price_hidden        = isset( $item[ 'bundled_item_price_hidden' ] ) ? $item[ 'bundled_item_price_hidden' ] : false;

			// Back-compat.
			if ( false === $bundled_item_priced_individually ) {
				$bundled_item_priced_individually = isset( $parent_item[ 'per_product_pricing' ] ) ? $parent_item[ 'per_product_pricing' ] : get_post_meta( $parent_item[ 'product_id' ], '_wc_pb_v4_per_product_pricing', true );
			}

			if ( 'no' === $bundled_item_priced_individually || 'yes' === $bundled_item_price_hidden || WC_PB()->compatibility->is_composited_order_item( $parent_item, $order ) ) {
				$subtotal = '';
			} else {

				/**
				 * Controls whether to include bundled order item subtotals in the container order item subtotal.
				 *
				 * @param  boolean   $add
				 * @param  array     $container_order_item
				 * @param  WC_Order  $order
				 */
				if ( apply_filters( 'woocommerce_add_bundled_order_item_subtotals', true, $parent_item, $order ) ) {
					$subtotal = sprintf( _x( '%1$s: %2$s', 'bundled product subtotal', 'ultimatewoo-pro' ), __( 'Subtotal', 'ultimatewoo-pro' ), $subtotal );
				}

				$subtotal = '<span class="bundled-product-subtotal">' . $subtotal . '</span>';
			}
		}

		// If it's a bundle (parent item)...
		if ( wc_pb_is_bundle_container_order_item( $item ) ) {

			/** Documented right above. Look up. See? */
			$add_subtotals_into_container = apply_filters( 'woocommerce_add_bundled_order_item_subtotals', true, $item, $order );

			if ( ! isset( $item[ 'subtotal_updated' ] ) && $add_subtotals_into_container ) {

				$children = wc_pb_get_bundled_order_items( $item, $order );

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
	 * Filters the reported number of order items.
	 * Do not count bundled items.
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
				if ( wc_pb_get_bundled_order_item_container( $item, $order ) ) {
					$subtract += $item[ 'qty' ];
				}
			}
		}

		$new_count = $count - $subtract;

		return $new_count;
	}

	/**
	 * Filters the string of order item count.
	 * Include bundled items as a suffix.
	 *
	 * @see    order_item_count
	 *
	 * @param  int       $count
	 * @param  WC_Order  $order
	 * @return int
	 */
	public function order_item_count_string( $count, $order ) {

		$add = 0;

		foreach ( $order->get_items() as $item ) {
			if ( wc_pb_get_bundled_order_item_container( $item, $order ) ) {
				$add += $item[ 'qty' ];
			}
		}

		if ( $add > 0 ) {
			$count = sprintf( __( '%1$s, %2$s bundled', 'ultimatewoo-pro' ), $count, $add );
		}

		return $count;
	}

	/**
	 * Filters the order item admin class.
	 *
	 * @param  string    $class
	 * @param  array     $item
	 * @param  WC_Order  $order
	 * @return string
	 */
	public function html_order_item_class( $class, $item, $order = false ) {

		if ( wc_pb_maybe_is_bundled_order_item( $item ) ) {
			if ( false === $order || wc_pb_get_bundled_order_item_container( $item, $order ) ) {
				$class .= ' bundled_item';
			}
		}

		return $class;

	}

	/**
	 * Bundle Containers need no processing - let it be decided by bundled items only.
	 *
	 * @param  boolean     $is_needed
	 * @param  WC_Product  $product
	 * @param  int         $order_id
	 * @return boolean
	 */
	public function container_item_needs_processing( $is_needed, $product, $order_id ) {

		if ( $product->is_type( 'bundle' ) && isset( $product->bundle_needs_processing ) && 'no' === $product->bundle_needs_processing ) {
			$is_needed = false;
		}

		return $is_needed;
	}

	/**
	 * Hides bundle metadata.
	 *
	 * @param  array  $hidden
	 * @return array
	 */
	public function hidden_order_item_meta( $hidden ) {

		$current_meta = array( '_bundled_by', '_bundled_items', '_bundle_cart_key', '_bundled_item_id', '_bundled_item_hidden', '_bundled_item_price_hidden', '_bundled_item_title', '_bundled_item_needs_shipping', '_bundle_weight', '_bundled_item_priced_individually', '_bundled_items_need_processing' );
		$legacy_meta  = array(  '_per_product_pricing', '_per_product_shipping', '_bundled_shipping', '_bundled_weight' );

		return array_merge( $hidden, $current_meta, $legacy_meta );
	}

	/**
	 * Add bundle info meta to order items.
	 *
	 * @param  int     $order_item_id
	 * @param  array   $cart_item_values
	 * @param  strong  $cart_item_key
	 * @return void
	 */
	public function add_order_item_meta( $order_item_id, $cart_item_values, $cart_item_key ) {

		if ( wc_pb_is_bundled_cart_item( $cart_item_values ) ) {

			wc_add_order_item_meta( $order_item_id, '_bundled_by', $cart_item_values[ 'bundled_by' ] );
			wc_add_order_item_meta( $order_item_id, '_bundled_item_id', $cart_item_values[ 'bundled_item_id' ] );

			$bundled_item_id = $cart_item_values[ 'bundled_item_id' ];
			$visible         = true;

			if ( $bundle_container_item = wc_pb_get_bundled_cart_item_container( $cart_item_values ) ) {

				$bundle          = $bundle_container_item[ 'data' ];
				$bundled_item_id = $cart_item_values[ 'bundled_item_id' ];

				if ( $bundled_item = $bundle->get_bundled_item( $bundled_item_id ) ) {

					if ( false === $bundled_item->is_visible( 'order' ) ) {
						wc_add_order_item_meta( $order_item_id, '_bundled_item_hidden', 'yes' );
					}

					if ( false === $bundled_item->is_price_visible( 'order' ) ) {
						wc_add_order_item_meta( $order_item_id, '_bundled_item_price_hidden', 'yes' );
					}

					wc_add_order_item_meta( $order_item_id, '_bundled_item_priced_individually', $bundled_item->is_priced_individually() ? 'yes' : 'no' );
				}
			}

			if ( isset( $cart_item_values[ 'stamp' ][ $bundled_item_id ][ 'title' ] ) ) {
				$title = $cart_item_values[ 'stamp' ][ $bundled_item_id ][ 'title' ];
				wc_add_order_item_meta( $order_item_id, '_bundled_item_title', $title );
			}
		}

		if ( wc_pb_is_bundle_container_cart_item( $cart_item_values ) ) {
			if ( isset( $cart_item_values[ 'bundled_items' ] ) ) {
				wc_add_order_item_meta( $order_item_id, '_bundled_items', $cart_item_values[ 'bundled_items' ] );
			}
		}

		if ( isset( $cart_item_values[ 'stamp' ] ) ) {

			wc_add_order_item_meta( $order_item_id, '_stamp', $cart_item_values[ 'stamp' ] );
			wc_add_order_item_meta( $order_item_id, '_bundle_cart_key', $cart_item_key );

			/*
			 * Store shipping data - useful when exporting order content.
			 */

			$needs_shipping = $cart_item_values[ 'data' ]->needs_shipping() ? 'yes' : 'no';

			// If it's a physical child item, add a meta fild to indicate whether it is shipped individually.
			if ( wc_pb_is_bundled_cart_item( $cart_item_values ) ) {

				wc_add_order_item_meta( $order_item_id, '_bundled_item_needs_shipping', $needs_shipping );

			} elseif ( wc_pb_is_bundle_container_cart_item( $cart_item_values ) ) {

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
						wc_add_order_item_meta( $order_item_id, '_bundle_weight', $bundled_weight );
					}

				// If it's a virtual container item, look at its children to see if any of them needs processing.
				} elseif ( false === $this->bundled_items_need_processing( $cart_item_values ) ) {
					wc_add_order_item_meta( $order_item_id, '_bundled_items_need_processing', 'no' );
				}
			}
		}
	}

	/**
	 * Given a virtual bundle container cart item, find if any of its children need processing.
	 *
	 * @since  5.0.0
	 *
	 * @param  array  $item_values
	 * @return mixed
	 */
	private function bundled_items_need_processing( $item_values ) {

		$child_keys        = wc_pb_get_bundled_cart_items( $item_values, WC()->cart->cart_contents, true, true );
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
	 * Activates the 'get_product_from_item' filter below.
	 *
	 * @param  string  $order_id
	 * @return void
	 */
	public function apply_get_product_from_item_filter( $order_id ) {
		add_filter( 'woocommerce_get_product_from_item', array( $this, 'get_processing_product_from_item' ), 10, 3 );
	}

	/**
	 * Deactivates the 'get_product_from_item' filter below.
	 *
	 * @param  string  $order_id
	 * @return void
	 */
	public function remove_get_product_from_item_filter( $order_id ) {
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
			if ( $child_items = wc_pb_get_bundled_order_items( $item, $order ) ) {

				// If no child requires processing and the container is virtual, it should not require processing - @see 'container_item_needs_processing()'.
				if ( $product->is_virtual() && sizeof( $child_items ) > 0 ) {
					if ( isset( $item[ 'bundled_items_need_processing' ] ) && 'no' === $item[ 'bundled_items_need_processing' ] ) {
						$product->bundle_needs_processing = 'no';
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

	public function get_bundled_order_item_container( $item, $order ) {
		_deprecated_function( __METHOD__ . '()', '4.13.0', __CLASS__ . '::get_bundle_parent()' );
		return wc_pb_get_bundled_order_item_container( $item, $order );
	}
	public function woo_bundles_add_order_item_meta( $order_item_id, $cart_item_values, $cart_item_key ) {
		_deprecated_function( __METHOD__ . '()', '4.13.0', __CLASS__ . '::add_order_item_meta()' );
		$this->add_order_item_meta( $order_item_id, $cart_item_values, $cart_item_key );
	}
	public static function get_bundle_parent( $item, $order, $return_type = 'item' ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', 'wc_pb_get_bundled_order_item_container()' );
		return wc_pb_get_bundled_order_item_container( $item, $order, $return_type === 'id' );
	}
	public static function get_bundle_children( $item, $order, $return_type = 'item' ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', 'wc_pb_get_bundled_order_items()' );
		return wc_pb_get_bundled_order_items( $item, $order, $return_type === 'id' );
	}
}
