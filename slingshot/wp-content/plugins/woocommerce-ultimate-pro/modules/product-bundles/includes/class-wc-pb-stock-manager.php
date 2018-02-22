<?php
/**
 * WC_PB_Stock_Manager and WC_PB_Stock_Manager_Item classes
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    4.8.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Used to create and store a product_id / variation_id representation of a product collection based on the included items' inventory requirements.
 *
 * @class    WC_PB_Stock_Manager
 * @version  5.1.0
 */
class WC_PB_Stock_Manager {

	private $items;
	public $product;

	public function __construct( $product = false ) {

		$this->product = $product;
		$this->items   = array();
	}

	/**
	 * Add a product to the collection.
	 *
	 * @param  WC_Product|int                  $product
	 * @param  false|WC_Product_Variation|int  $variation
	 * @param  integer                         $quantity
	 * @param  array                           $args
	 */
	public function add_item( $product, $variation = false, $quantity = 1, $args = array() ) {
		$this->items[] = new WC_PB_Stock_Manager_Item( $product, $variation, $quantity, $args );
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
	 * @param  WC_PB_Stock_Manager  $stock
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
	 * Note that in some cases, stock for a variation might be managed by the parent - this is tracked by the managed_by_id property in WC_PB_Stock_Manager_Item.
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

					if ( $purchased_item->bundled_item ) {
						$managed_items[ $managed_by_id ][ 'is_secret' ] = $managed_items[ $managed_by_id ][ 'is_secret' ] && $purchased_item->bundled_item->is_secret();
						$managed_items[ $managed_by_id ][ 'title' ]     = $managed_items[ $managed_by_id ][ 'title' ] !== $purchased_item->bundled_item->get_raw_title() ? $purchased_item->bundled_item->product->get_title() : $managed_items[ $managed_by_id ][ 'title' ];
					}

				} else {

					$managed_items[ $managed_by_id ][ 'quantity' ]  = $purchased_item->quantity;
					$managed_items[ $managed_by_id ][ 'is_secret' ] = false;
					$managed_items[ $managed_by_id ][ 'title' ]     = '';

					if ( $purchased_item->bundled_item ) {
						$managed_items[ $managed_by_id ][ 'is_secret' ] = $purchased_item->bundled_item->is_secret();
						$managed_items[ $managed_by_id ][ 'title' ]     = $purchased_item->bundled_item->get_raw_title();
					}

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
	 * @param  int  $bundle_id
	 * @return boolean
	 */
	public function validate_stock() {

		$managed_items = $this->get_managed_items();

		if ( empty( $managed_items ) ) {
			return true;
		}

		$bundle_id    = WC_PB_Core_Compatibility::get_id( $this->product );
		$bundle_title = $this->product->get_title();

		// Product quantities already in cart.
		$quantities_in_cart = WC()->cart->get_cart_item_quantities();

		// If we are updating a bundle in-cart, subtract the bundled item cart quantites that belong to the bundle being updated, since it's going to be removed later on.
		if ( isset( $_POST[ 'update-bundle' ] ) ) {

			$updating_cart_key = wc_clean( $_POST[ 'update-bundle' ] );

			if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {

				$bundle_cart_item   = WC()->cart->cart_contents[ $updating_cart_key ];
				$bundled_cart_items = wc_pb_get_bundled_cart_items( $bundle_cart_item );

				if ( isset( $quantities_in_cart[ $bundle_cart_item[ 'product_id' ] ] ) ) {
					$quantities_in_cart[ $bundle_cart_item[ 'product_id' ] ] -= $bundle_cart_item[ 'quantity' ];
					// Unset if 0.
					if ( 0 === absint( $quantities_in_cart[ $bundle_cart_item[ 'product_id' ] ] ) ) {
						unset( $quantities_in_cart[ $bundle_cart_item[ 'product_id' ] ] );
					}
				}

				if ( ! empty( $bundled_cart_items ) ) {
					foreach ( $bundled_cart_items as $item_key => $item ) {

						$bundled_product_id = $item[ 'data' ]->is_type( 'variation' ) && true === $item[ 'data' ]->managing_stock() ? $item[ 'variation_id' ] : $item[ 'product_id' ];

						if ( isset( $quantities_in_cart[ $bundled_product_id ] ) ) {
							$quantities_in_cart[ $bundled_product_id ] -= $item[ 'quantity' ];
							// Unset if 0.
							if ( 0 === absint( $quantities_in_cart[ $bundled_product_id ] ) ) {
								unset( $quantities_in_cart[ $bundled_product_id ] );
							}
						}
					}
				}
			}
		}

		// Stock Validation.
		foreach ( $managed_items as $managed_item_id => $managed_item ) {

			try {

				$quantity = $managed_item[ 'quantity' ];

				// Get the product.
				$product_data = wc_get_product( $managed_item_id );

				if ( ! $product_data ) {
					return false;
				}

				$product_title = '' !== $managed_item[ 'title' ] ? $managed_item[ 'title' ] : $product_data->get_title();

				// Sanity check.
				if ( $product_data->is_sold_individually() && $quantity > 1 ) {
					wc_add_notice( sprintf( __( '&quot;%1$s&quot; cannot be added to the cart &mdash; only 1 &quot;%2$s&quot; may be purchased.', 'ultimatewoo-pro' ), $bundle_title, $product_title ), 'error' );
					return false;
				}

				$is_variable   = 'variable' === $product_data->get_type() || 'variable-subscription' === $product_data->get_type() || 'variation' === $product_data->get_type();
				$configuration = '';

				if ( false === $managed_item[ 'is_secret' ] && 'variation' === $product_data->get_type() && WC_PB_Core_Compatibility::is_wc_version_gte_2_5() ) {
					$configuration = sprintf( _x( ' (%s)', 'suffix', 'ultimatewoo-pro' ), WC_PB_Core_Compatibility::wc_get_formatted_variation( $product_data, true ) );
				}

				// Stock check - only check if we're managing stock and backorders are not allowed.
				if ( ! $product_data->is_in_stock() ) {

					if ( $is_variable ) {
						$error = sprintf( __( '&quot;%1$s&quot; cannot be added to the cart &ndash; the chosen &quot;%2$s&quot; variation%3$s is out of stock.', 'ultimatewoo-pro' ), $bundle_title, $product_title, $configuration );
					} else {
						$error = sprintf( __( '&quot;%1$s&quot; cannot be added to the cart &ndash; &quot;%2$s&quot; is out of stock.', 'ultimatewoo-pro' ), $bundle_title, $product_title );
					}

					throw new Exception( $error );

				} elseif ( ! $product_data->has_enough_stock( $quantity ) ) {

					if ( $is_variable ) {
						$error = sprintf(__( '&quot;%1$s&quot; cannot be added to the cart &ndash; the chosen &quot;%2$s&quot; variation%3$s does not have enough stock (%4$s remaining).', 'ultimatewoo-pro' ), $bundle_title, $product_title, $configuration, $product_data->get_stock_quantity() );
					} else {
						$error = sprintf( __( '&quot;%1$s&quot; cannot be added to the cart because there is not enough stock of &quot;%2$s&quot; (%3$s remaining).', 'ultimatewoo-pro' ), $bundle_title, $product_title, $product_data->get_stock_quantity() );
					}

					throw new Exception( $error );
				}

				// Stock check - this time accounting for whats already in-cart.
				if ( $product_data->managing_stock() ) {

					// Variations.
					if ( $is_variable ) {

						if ( isset( $quantities_in_cart[ $managed_item_id ] ) && ! $product_data->has_enough_stock( $quantities_in_cart[ $managed_item_id ] + $quantity ) ) {

							$error = sprintf(
								'<a href="%s" class="button wc-forward">%s</a> %s',
								WC()->cart->get_cart_url(),
								__( 'View Cart', 'woocommerce' ),
								sprintf( __( '&quot;%1$s&quot; cannot be added to the cart because the chosen &quot;%2$s&quot; variation%3$s does not have enough stock &mdash; we have %4$s in stock and you already have %5$s in your cart.', 'ultimatewoo-pro' ), $bundle_title, $product_title, $configuration, $product_data->get_stock_quantity(), $quantities_in_cart[ $managed_item_id ] )
							);

							throw new Exception( $error );
						}

					// Products.
					} else {

						if ( isset( $quantities_in_cart[ $managed_item_id ] ) && ! $product_data->has_enough_stock( $quantities_in_cart[ $managed_item_id ] + $quantity ) ) {

							$error = sprintf(
								'<a href="%s" class="button wc-forward">%s</a> %s',
								WC()->cart->get_cart_url(),
								__( 'View Cart', 'woocommerce' ),
								sprintf( __( '&quot;%1$s&quot; cannot be added to the cart because there is not enough stock of &quot;%2$s&quot; &mdash; we have %3$s in stock and you already have %4$s in your cart.', 'ultimatewoo-pro' ), $bundle_title, $product_title, $product_data->get_stock_quantity(), $quantities_in_cart[ $managed_item_id ] )
							);

							throw new Exception( $error );
						}
					}
				}

			} catch ( Exception $e ) {

				if ( $e->getMessage() ) {

					if ( $managed_item[ 'is_secret' ] ) {
						$error = sprintf( __( '&quot;%1$s&quot; cannot be added to the cart &ndash; the product is currently unavailable.', 'ultimatewoo-pro' ), $bundle_title );
					} else {
						$error = $e->getMessage();
					}

					wc_add_notice( $error, 'error' );
				}

				return false;
			}
		}

		return true;
	}
}

/**
 * Maps a product/variation in the collection to the item managing stock for it.
 * These 2 will differ only if stock for a variation is managed by its parent.
 *
 * @class    WC_PB_Stock_Manager_Item
 * @version  5.1.0
 * @since    4.8.7
 */
class WC_PB_Stock_Manager_Item {

	public $product_id;
	public $variation_id;
	public $quantity;
	public $bundled_item;

	public $managed_by_id;

	public function __construct( $product, $variation = false, $quantity = 1, $args = array() ) {

		$this->product_id   = is_object( $product ) ? WC_PB_Core_Compatibility::get_id( $product ) : $product;
		$this->variation_id = is_object( $variation ) ? WC_PB_Core_Compatibility::get_id( $variation ) : $variation;
		$this->quantity     = $quantity;
		$this->bundled_item = isset( $args[ 'bundled_item' ] ) ? $args[ 'bundled_item' ] : false;

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
