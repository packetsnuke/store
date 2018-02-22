<?php
/**
 * WC_CP_Stock_Manager and WC_CP_Stock_Manager_Item classes
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    3.0.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Used to create and store a product_id / variation_id representation of a product collection based on the included items' inventory requirements.
 *
 * @class    WC_CP_Stock_Manager
 * @version  3.8.0
 */
class WC_CP_Stock_Manager {

	private $items;
	public $product;

	/**
	 * Constructor.
	 *
	 * @param  WC_Product_Composite  $product
	 */
	public function __construct( $product ) {
		$this->items   = array();
		$this->product = $product;
	}

	/**
	 * Add a product to the collection.
	 *
	 * @param  WC_Product|int                  $product
	 * @param  false|WC_Product_Variation|int  $variation
	 * @param  integer                         $quantity
	 */
	public function add_item( $product, $variation = false, $quantity = 1 ) {
		$this->items[] = new WC_CP_Stock_Manager_Item( $product, $variation, $quantity );
	}

	/**
	 * Return the items of this collection.
	 *
	 * @return array
	 */
	public function get_items() {

		if ( ! empty( $this->items ) ) {
			return $this->items;
		}

		return array();
	}

	/**
	 * Merge another collection with this one.
	 *
	 * @param WC_CP_Stock_Manager  $stock
	 */
	public function add_stock( $stock ) {

		if ( ! is_object( $stock ) ) {
			return false;
		}

		$items_to_add = $stock->get_items();

		if ( ! empty( $items_to_add ) ) {
			foreach ( $items_to_add as $item ) {
				$this->items[] = $item;
			}
			return true;
		}

		return false;
	}

	/**
	 * Return the stock requirements of the items in this collection.
	 * To validate stock accurately, this method is used to add quantities and build a list of product/variation ids to check.
	 * Note that in some cases, stock for a variation might be managed by the parent - this is tracked by the managed_by_id property in WC_CP_Stock_Manager_Item.
	 *
	 * @return array
	 */
	public function get_managed_items() {

		$managed_items = array();

		if ( ! empty( $this->items ) ) {

			foreach ( $this->items as $purchased_item ) {

				$managed_by_id = $purchased_item->managed_by_id;

				if ( isset( $managed_items[ $managed_by_id ] ) ) {

					$managed_items[ $managed_by_id ][ 'quantity' ] += $purchased_item->quantity;

				} else {

					$managed_items[ $managed_by_id ][ 'quantity' ] = $purchased_item->quantity;

					if ( $purchased_item->variation_id && $purchased_item->variation_id == $managed_by_id ) {
						$managed_items[ $managed_by_id ][ 'is_variation' ] = true;
						$managed_items[ $managed_by_id ][ 'product_id' ]   = $purchased_item->product_id;
					} else {
						$managed_items[ $managed_by_id ][ 'is_variation' ] = false;
					}
				}
			}
		}

		return $managed_items;
	}

	/**
	 * Validate that all managed items in the collection are in stock.
	 *
	 * @return boolean
	 */
	public function validate_stock() {

		$managed_items = $this->get_managed_items();

		if ( empty( $managed_items ) ) {
			return true;
		}

		$composite_id    = WC_CP_Core_Compatibility::get_id( $this->product );
		$composite_title = $this->product->get_title();

		// Product quantities already in cart.
		$quantities_in_cart = WC()->cart->get_cart_item_quantities();

		// If we are updating a composite in-cart, subtract the composited item cart quantites that belong to the composite being updated, since it's going to be removed later on.
		if ( isset( $_POST[ 'update-composite' ] ) ) {

			$updating_cart_key = wc_clean( $_POST[ 'update-composite' ] );

			if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {

				$parent_cart_item = WC()->cart->cart_contents[ $updating_cart_key ];
				$child_cart_items = wc_cp_get_composited_cart_items( $parent_cart_item );

				if ( isset( $quantities_in_cart[ $parent_cart_item[ 'product_id' ] ] ) ) {
					$quantities_in_cart[ $parent_cart_item[ 'product_id' ] ] -= $parent_cart_item[ 'quantity' ];
					// Unset if 0.
					if ( 0 === absint( $quantities_in_cart[ $parent_cart_item[ 'product_id' ] ] ) ) {
						unset( $quantities_in_cart[ $parent_cart_item[ 'product_id' ] ] );
					}
				}

				if ( ! empty( $child_cart_items ) ) {
					foreach ( $child_cart_items as $item_key => $item ) {

						$child_product_id = $item[ 'data' ]->is_type( 'variation' ) && true === $item[ 'data' ]->managing_stock() ? $item[ 'variation_id' ] : $item[ 'product_id' ];

						if ( isset( $quantities_in_cart[ $child_product_id ] ) ) {
							$quantities_in_cart[ $child_product_id ] -= $item[ 'quantity' ];
							// Unset if 0.
							if ( 0 === absint( $quantities_in_cart[ $child_product_id ] ) ) {
								unset( $quantities_in_cart[ $child_product_id ] );
							}
						}
					}
				}
			}
		}

		// Stock Validation.
		foreach ( $managed_items as $managed_item_id => $managed_item ) {

			$quantity = $managed_item[ 'quantity' ];

			// Get the product.
			$product_data = wc_get_product( $managed_item_id );

			if ( ! $product_data ) {
				return false;
			}

			if ( ! $quantity ) {
				continue;
			}

			// Sold individually?
			if ( $product_data->is_sold_individually() && $quantity > 1 ) {

				wc_add_notice( sprintf( __( 'This &quot;%1$s&quot; configuration cannot be added to the cart &mdash; only 1 &quot;%2$s&quot; may be purchased.', 'ultimatewoo-pro' ), $composite_title, $product_data->get_title() ), 'error' );
				return false;
			}

			// Check product is_purchasable.
			if ( ! $product_data->is_purchasable() ) {
				wc_add_notice( sprintf( __( 'This &quot;%1$s&quot; configuration cannot be added to the cart &mdash; &quot;%2$s&quot; cannot be purchased.', 'ultimatewoo-pro' ), $composite_title, $product_data->get_title() ), 'error' );
				return false;
			}

			$is_variable   = 'variable' === $product_data->get_type() || 'variation' === $product_data->get_type();
			$configuration = '';

			if ( 'variation' === $product_data->get_type() ) {
				$configuration = sprintf( _x( ' (%s)', 'suffix', 'ultimatewoo-pro' ), WC_CP_Core_Compatibility::wc_get_formatted_variation( $product_data, true ) );
			}

			// Stock check - only check if we're managing stock and backorders are not allowed.
			if ( ! $product_data->is_in_stock() ) {

				if ( $is_variable ) {
					wc_add_notice( sprintf( __( 'This &quot;%1$s&quot; configuration cannot be added to the cart &mdash; the chosen &quot;%2$s&quot; variation%3$s is out of stock.', 'ultimatewoo-pro' ), $composite_title, $product_data->get_title(), $configuration ), 'error' );
				} else {
					wc_add_notice( sprintf( __( 'This &quot;%1$s&quot; configuration cannot be added to the cart &mdash; &quot;%2$s&quot; is out of stock.', 'ultimatewoo-pro' ), $composite_title, $product_data->get_title() ), 'error' );
				}

				return false;

			} elseif ( ! $product_data->has_enough_stock( $quantity ) ) {

				if ( $is_variable ) {
					wc_add_notice( sprintf(__( 'This &quot;%1$s&quot; configuration cannot be added to the cart &mdash; the chosen &quot;%2$s&quot; variation%3$s does not have enough stock (%4$s remaining).', 'ultimatewoo-pro' ), $composite_title, $product_data->get_title(), $configuration, $product_data->get_stock_quantity() ), 'error' );
				} else {
					wc_add_notice( sprintf(__( 'This &quot;%1$s&quot; configuration cannot be added to the cart &mdash; there is not enough stock of &quot;%2$s&quot; (%3$s remaining).', 'ultimatewoo-pro' ), $composite_title, $product_data->get_title(), $product_data->get_stock_quantity() ), 'error' );
				}

				return false;
			}

			// Stock check, possibly accounting for what's in cart.
			if ( $product_data->managing_stock() ) {

				// Variations.
				if ( $is_variable ) {

					if ( isset( $quantities_in_cart[ $managed_item_id ] ) && ! $product_data->has_enough_stock( $quantities_in_cart[ $managed_item_id ] + $quantity ) ) {

						wc_add_notice( sprintf(
							'<a href="%s" class="button wc-forward">%s</a> %s',
							WC()->cart->get_cart_url(),
							__( 'View Cart', 'woocommerce' ),
							sprintf( __( 'This &quot;%1$s&quot; configuration cannot be added to the cart because the chosen &quot;%2$s&quot; variation%3$s does not have enough stock &mdash; we have %4$s in stock and you already have %5$s in your cart.', 'ultimatewoo-pro' ), $composite_title, $product_data->get_title(), $configuration, $product_data->get_stock_quantity(), $quantities_in_cart[ $managed_item_id ] )
						), 'error' );

						return false;
					}

				// Products.
				} else {

					if ( isset( $quantities_in_cart[ $managed_item_id ] ) && ! $product_data->has_enough_stock( $quantities_in_cart[ $managed_item_id ] + $quantity ) ) {
						wc_add_notice( sprintf(
							'<a href="%s" class="button wc-forward">%s</a> %s',
							WC()->cart->get_cart_url(),
							__( 'View Cart', 'woocommerce' ),
							sprintf( __( 'This &quot;%1$s&quot; configuration cannot be added to the cart because there is not enough stock of &quot;%2$s&quot; &mdash; we have %3$s in stock and you already have %4$s in your cart.', 'ultimatewoo-pro' ), $composite_title, $product_data->get_title(), $product_data->get_stock_quantity(), $quantities_in_cart[ $managed_item_id ] )
						), 'error' );

						return false;
					}
				}
			}
		}

		return true;
	}
}

/**
 * Class to represent stock-managed items.
 *
 * Maps a product/variation in the collection to the item managing stock for it.
 * These 2 will differ only if stock for a variation is managed by its parent.
 *
 * @class    WC_CP_Stock_Manager_Item
 * @version  3.8.0
 * @since    3.3.1
 */
class WC_CP_Stock_Manager_Item {

	public $product_id;
	public $variation_id;
	public $quantity;

	public $managed_by_id;

	public function __construct( $product, $variation = false, $quantity = 1 ) {

		$this->product_id   = is_object( $product ) ? WC_CP_Core_Compatibility::get_id( $product ) : $product;
		$this->variation_id = is_object( $variation ) ? WC_CP_Core_Compatibility::get_id( $variation ) : $variation;
		$this->quantity     = $quantity;
		$this->quantity     = $quantity;

		if ( $this->variation_id ) {

			$variation = is_object( $variation ) ? $variation : wc_get_product( $variation );

			// If stock is managed at variation level.
			if ( $variation && $variation->managing_stock() ) {
				$this->managed_by_id = $this->variation_id;
			// Otherwise stock is managed by the parent.
			} else {
				$this->managed_by_id = $this->product_id;
			}

		} else {
			$this->managed_by_id = $this->product_id;
		}
	}
}
