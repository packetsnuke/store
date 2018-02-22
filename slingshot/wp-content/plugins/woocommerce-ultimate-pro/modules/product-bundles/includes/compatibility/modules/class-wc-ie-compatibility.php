<?php
/**
 * WC_PB_WC_IE_Compatibility class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    5.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC CSV Import/Export Suite extension support.
 * Uses a dedicated CSV column to export bundle data using the 'get_data()' method of the WC_Bundled_Item_Data CRUD class.
 * Data is imported again using the WC_Bundled_Item_Data class.
 *
 * @version  5.1.3
 */
class WC_PB_WC_IE_Compatibility {

	public static function init() {

		// Add column to CSV export.
		add_filter( 'woocommerce_csv_product_post_columns', array( __CLASS__, 'csv_columns' ) );

		// Export bundle data.
		add_filter( 'woocommerce_csv_product_export_post', array( __CLASS__, 'csv_export_post' ), 10, 2 );

		// Import bundle data.
		add_action( 'woocommerce_csv_product_imported', array( __CLASS__, 'csv_import_post' ), 10, 3 );

		// Reassociate bundled items with products on import end.
		add_action( 'import_end', array( __CLASS__, 'csv_import_end' ) );
	}

	/**
	 * Add a CSV column for exporting bundle data.
	 *
	 * @param  array  $columns
	 * @return array
	 */
	public static function csv_columns( $columns ) {

		$columns[ 'wc_pb_bundled_items_data' ] = 'meta:_bundled_items_db_data';

		$columns[ '_wc_pb_layout_style' ]              = 'meta:_wc_pb_layout_style';
		$columns[ '_wc_pb_edit_in_cart' ]              = 'meta:_wc_pb_edit_in_cart';
		$columns[ '_wc_pb_sold_individually_context' ] = 'meta:_wc_pb_sold_individually_context';

		if ( ! isset( $columns[ 'base_price' ] ) ) {
			$columns[ '_wc_pb_base_price' ] = 'meta:_wc_pb_base_price';
		}
		if ( ! isset( $columns[ 'base_regular_price' ] ) ) {
			$columns[ '_wc_pb_base_regular_price' ] = 'meta:_wc_pb_base_regular_price';
		}
		if ( ! isset( $columns[ 'base_sale_price' ] ) ) {
			$columns[ '_wc_pb_base_sale_price' ] = 'meta:_wc_pb_base_sale_price';
		}

		return $columns;
	}

	/**
	 * Export bundle data using the 'get_data()' method of the WC_Bundled_Item_Data CRUD class.
	 * Data is exported as queried object property to allow the CSVIES logic to locate it and export it.
	 *
	 * @param  object  $post
	 * @param  array   $export_columns
	 * @return object
	 */
	public static function csv_export_post( $post, $export_columns ) {

		$bundled_items = WC_PB_DB::query_bundled_items( array(
			'return'    => 'objects',
			'bundle_id' => $post->ID
		) );

		if ( ! empty( $bundled_items ) ) {
			$data = array();
			foreach ( $bundled_items as $bundled_item ) {
				$data[ $bundled_item->get_id() ] = $bundled_item->get_data();
			}
			$post->wc_pb_bundled_items_data = json_encode( $data );
		}

		return $post;
	}

	/**
	 * Import json-encoded bundle data using the WC_Bundled_Item_Data CRUD class.
	 *
	 * @param  array                     $post_data
	 * @param  int                       $processed_id
	 * @param  WC_PCSVIS_Product_Import  $importer
	 * @return void
	 */
	public static function csv_import_post( $post_data, $processed_id, $importer ) {

		$merging     = ! empty( $post_data[ 'merging' ] );
		$imported_id = $importer->processed_posts[ $processed_id ];

		// Find if meta key exists.
		$bundle_data = false;
		foreach ( $post_data[ 'postmeta' ] as $meta_data ) {
			if ( '_bundled_items_db_data' === $meta_data[ 'key' ] ) {
				$bundle_data = $meta_data[ 'value' ];
				break;
			}
		}

		if ( ! empty( $bundle_data ) ) {

			if ( $merging ) {

				// Delete existing bundled items.
				$args = array(
					'bundle_id' => $imported_id,
					'return'    => 'ids',
				);

				$delete_items = WC_PB_DB::query_bundled_items( $args );

				if ( ! empty( $delete_items ) ) {
					foreach ( $delete_items as $delete_item ) {
						WC_PB_DB::delete_bundled_item( $delete_item );
					}
				}
			}

			foreach ( $bundle_data as $bundled_item_id => $bundled_item_data ) {
				WC_PB_DB::add_bundled_item( array(
					'bundle_id'  => $imported_id,                       // Use the new bundle id.
					'product_id' => $bundled_item_data[ 'product_id' ], // May get modified during import - @see 'csv_import_end().
					'menu_order' => $bundled_item_data[ 'menu_order' ],
					'meta_data'  => $bundled_item_data[ 'meta_data' ],
					'force_add'  => true                                // Bundled product may not exist in the DB yet, but get created later during import.
				) );
			}

			// Flush bundle transients.
			wc_delete_product_transients( $imported_id );

			// Delete imported meta.
			delete_post_meta( $imported_id, '_bundled_items_db_data' );
		}
	}

	/**
	 * Reassociate bundled item ids with modified bundled product ids on import end.
	 */
	public static function csv_import_end() {
		global $wpdb;

		if ( isset( $_POST[ 'processed_posts' ] ) ) {

			$processed_products = (array) $_POST[ 'processed_posts' ];
			$update_products    = array();

			if ( ! empty( $processed_products ) ) {
				foreach ( $processed_products as $old_id => $new_id ) {
					if ( absint( $old_id ) !== absint( $new_id ) ) {
						$update_products[ $old_id ] = 'WHEN ' . $old_id . ' THEN ' . $new_id;
					}
				}
			}

			if ( ! empty( $update_products ) ) {
				// Reassociate ids.
				$wpdb->query( "
					UPDATE {$wpdb->prefix}woocommerce_bundled_items
					SET product_id = CASE product_id " . implode( ' ', $update_products ) .  " ELSE product_id END
					WHERE product_id IN (" . implode( ',', array_keys( $update_products ) ) . ")
					AND bundle_id IN (" . implode( ',', array_keys( $update_products ) ) . ")
				" );
			}

			// Flush stock cache.
			WC_PB_DB::flush_stock_cache();
		}
	}
}

WC_PB_WC_IE_Compatibility::init();
