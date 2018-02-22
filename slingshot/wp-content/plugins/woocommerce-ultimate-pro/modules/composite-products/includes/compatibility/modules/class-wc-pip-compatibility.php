<?php
/**
 * WC_CP_PIP_Compatibility class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    3.6.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PIP Compatibility.
 *
 * @version  3.8.0
 */
class WC_CP_PIP_Compatibility {

	public static function init() {

		// Temporarily add order item data to array.
		add_filter( 'wc_pip_document_table_row_item_data', array( __CLASS__, 'filter_pip_row_item_data' ), 10, 5 );
		// Re-sort PIP table rows so that bundled items are always below their container.
		add_filter( 'wc_pip_document_table_rows', array( __CLASS__, 'filter_pip_table_rows' ), 51, 5 );
		// Add 'composited-product' class to pip row classes.
		add_filter( 'wc_pip_document_table_product_class', array( __CLASS__, 'filter_pip_document_table_bundled_item_class' ), 10, 4 );
		// Filter PIP item titles.
		add_filter( 'wc_pip_order_item_name', array( __CLASS__, 'filter_pip_document_table_component_name' ), 10, 6 );
		// Ensure bundle container line items are always dislpayed.
		add_filter( 'wc_pip_packing_list_hide_virtual_item', array( __CLASS__, 'filter_pip_hide_virtual_item' ), 10, 4 );
		// Prevent bundled order items from being sorted/categorized.
		add_filter( 'wc_pip_packing_list_group_item_as_uncategorized', array( __CLASS__, 'group_bundled_items_as_uncategorized' ), 10, 3 );
		// Add bundled item class CSS rule.
		add_action( 'wc_pip_styles', array( __CLASS__, 'add_pip_bundled_item_styles' ) );
	}

	/**
	 * Prevent composited order items from being sorted/categorized.
	 *
	 * @param  boolean   $uncategorize
	 * @param  array     $order_item
	 * @param  WC_Order  $order
	 * @return boolean
	 */
	public static function group_bundled_items_as_uncategorized( $uncategorize, $order_item, $order ) {

		if ( wc_cp_is_composited_order_item( $order_item, $order ) ) {
			$uncategorize = true;
		}

		return $uncategorize;
	}

	/**
	 * Ensure composite container line items are always displayed.
	 *
	 * @param  boolean     $hide
	 * @param  WC_Product  $product
	 * @param  array       $item
	 * @param  WC_Order    $order
	 * @return boolean
	 */
	public static function filter_pip_hide_virtual_item( $hide, $product, $item, $order ) {

		if ( ! empty( $item[ 'composite_children' ] ) ) {
			$hide = false;
		}

		return $hide;
	}


	/**
	 * Add composited item class CSS rule.
	 */
	public static function add_pip_bundled_item_styles() {
		?>
		.composited-product {
			padding-left: 2.5em;
			display: block;
		}
		.composited-product dl.component, .composited-product dl.component dt, .composited-product dl.component dd {
			margin: 0; padding: 0;
			line-height: 1.5;
		}
		.composited-product dl.component dd p {
			margin: 0 !important;
		}
		.component-subtotal {
			font-size: 0.875em;
			padding-right: 2em;
			display: block;
		}
		<?php
	}

	public static function filter_pip_document_table_component_name( $name, $item, $is_visible, $type, $product, $order ) {
		WC_CP()->display->set_order_item_order( $order );
		$name = WC_CP()->display->order_table_component_title( $name, $item );
		WC_CP()->display->set_order_item_order( false );
		return $name;
	}

	/**
	 * Add 'composited-product' class to pip row classes.
	 *
	 * @param  array       $classes
	 * @param  WC_Product  $product
	 * @param  array       $item
	 * @param  string      $type
	 * @return array
	 */
	public static function filter_pip_document_table_bundled_item_class( $classes, $product, $item, $type ) {

		if ( ! empty( $item[ 'composite_parent' ] ) ) {
			$classes[] = 'composited-product';
		}

		return $classes;
	}

	/**
	 * Temporarily add order item data to array.
	 *
	 * @param  array       $item_data
	 * @param  array       $item
	 * @param  WC_Product  $product
	 * @param  string      $order_id
	 * @param  string      $type
	 * @return array
	 */
	public static function filter_pip_row_item_data( $item_data, $item, $product, $order_id, $type ) {

		$item_data[ 'wc_cp_item_data' ] = $item;

		return $item_data;
	}

	/**
	 * Re-sort PIP table rows so that composited items are always below their container.
	 *
	 * @param  array   $table_rows
	 * @param  array   $items
	 * @param  string  $order_id
	 * @param  string  $type
	 * @return array
	 */
	public static function filter_pip_table_rows( $table_rows, $items, $order_id, $type, $pip_document = null ) {

		$order               = is_null( $pip_document ) ? wc_get_order( $order_id ) : $pip_document->order;
		$filtered_table_rows = array();

		if ( ! empty( $table_rows ) ) {

			foreach ( $table_rows as $table_row_key => $table_row_data ) {

				if ( empty( $table_row_data[ 'items' ] ) ) {
					continue;
				}

				$sorted_rows = array();

				foreach ( $table_row_data[ 'items' ] as $row_item ) {

					if ( isset( $row_item[ 'wc_cp_item_data' ] ) && isset( $row_item[ 'wc_cp_item_data' ][ 'composite_children' ] ) ) {

						$sorted_rows[] = $row_item;

						$children = wc_cp_get_composited_order_items( $row_item[ 'wc_cp_item_data' ], $order );

						// Look for its children in all table rows and bring them over in the original order.
						if ( ! empty( $children ) ) {
							foreach ( $children as $child_order_item ) {

								if ( empty( $child_order_item[ 'composite_cart_key' ] ) ) {
									continue;
								}

								// Look for the child in all table rows and bring it over.
								foreach ( $table_rows as $table_row_key_inner => $table_row_data_inner ) {
									foreach ( $table_row_data_inner[ 'items' ] as $row_item_inner ) {

										$is_child = false;

										if ( isset( $row_item_inner[ 'wc_cp_item_data' ] ) && isset( $row_item_inner[ 'wc_cp_item_data' ][ 'composite_cart_key' ] ) ) {
											$is_child = $row_item_inner[ 'wc_cp_item_data' ][ 'composite_cart_key' ] === $child_order_item[ 'composite_cart_key' ];
										}

										if ( $is_child ) {
											$sorted_rows[] = $row_item_inner;
										}
									}
								}
							}
						}

					} else {

						// Do not copy composited items (will be looked up by their parents).
						if ( ! isset( $row_item[ 'wc_cp_item_data' ] ) || ! isset( $row_item[ 'wc_cp_item_data' ][ 'composite_parent' ] ) ) {
							$sorted_rows[] = $row_item;
						}
					}
				}

				// Unset our (now redundant) data.
				foreach ( $sorted_rows as $sorted_row_item => $sorted_row_item_data ) {
					if ( isset( $sorted_row_item_data[ 'wc_cp_item_data' ] ) ) {
						unset( $sorted_rows[ $sorted_row_item ][ 'wc_cp_item_data' ]  );
					}
				}

				$filtered_table_rows[ $table_row_key ]            = $table_row_data;
				$filtered_table_rows[ $table_row_key ][ 'items' ] = $sorted_rows;
			}

			// Ensure empty categories are not displayed at all.
			foreach ( $filtered_table_rows as $table_row_key => $table_row_data ) {
				if ( empty( $table_row_data[ 'items' ] ) ) {
					unset( $filtered_table_rows[ $table_row_key ] );
				}
			}
		}

		return $filtered_table_rows;
	}
}

WC_CP_PIP_Compatibility::init();
