<?php
/**
 * WC_PB_DB_Sync class
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
 * Product hooks for DB lifecycle management of products, bundled items and their meta.
 *
 * @class    WC_PB_DB_Sync
 * @version  5.4.1
 */
class WC_PB_DB_Sync {

	/**
	 * Setup Admin class.
	 */
	public static function init() {

		// Duplicate bundled items when duplicating a bundle.
		if ( WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ) {
			add_action( 'woocommerce_product_duplicate_before_save', array( __CLASS__, 'duplicate_product_before_save' ), 10, 2 );
		} else {
			add_action( 'woocommerce_duplicate_product', array( __CLASS__, 'duplicate_product_legacy' ), 10, 2 );
		}

		// Delete bundled item DB entries when: i) the container bundle is deleted, or ii) the associated product is deleted.
		add_action( 'delete_post', array( __CLASS__, 'delete_post' ), 11 );
		if ( WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ) {
			add_action( 'woocommerce_delete_product', array( __CLASS__, 'delete_product' ), 11 );
		}

		// When deleting a bundled item from the DB, clear the transients of the container bundle.
		add_action( 'woocommerce_delete_bundled_item', array( __CLASS__, 'delete_bundled_item' ) );

		// Delete associated bundled items stock cache when clearing product transients under WC 2.6.
		if ( ! WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ) {
			add_action( 'woocommerce_delete_product_transients', array( __CLASS__, 'delete_bundle_transients' ) );
		}

		// Delete meta reserved to the bundle type.
		if ( WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ) {
			add_action( 'woocommerce_before_product_object_save', array( __CLASS__, 'delete_reserved_price_meta' ) );
		} else {
			add_action( 'save_post_product', array( __CLASS__, 'delete_reserved_price_post_meta' ) );
			add_action( 'save_post_product_variation', array( __CLASS__, 'delete_reserved_price_post_meta' ) );
		}

		if ( ! defined( 'WC_PB_DEBUG_STOCK_CACHE' ) ) {

			// Delete bundled item stock meta cache when stock changes.
			add_action( 'woocommerce_product_set_stock', array( __CLASS__, 'product_stock_changed' ), 100 );
			add_action( 'woocommerce_variation_set_stock', array( __CLASS__, 'product_stock_changed' ), 100 );

			// Delete bundled item stock meta cache when stock status changes.
			add_action( 'woocommerce_product_set_stock_status', array( __CLASS__, 'product_stock_status_changed' ), 100, 3 );
			add_action( 'woocommerce_variation_set_stock_status', array( __CLASS__, 'product_stock_status_changed' ), 100, 3 );
		}
	}

	/**
	 * Duplicates bundled items when duplicating a bundle.
	 *
	 * @param  WC_Product  $duplicated_product
	 * @param  WC_Product  $product
	 */
	public static function duplicate_product_before_save( $duplicated_product, $product ) {

		if ( $product->is_type( 'bundle' ) ) {

			$bundled_items      = $product->get_bundled_data_items( 'edit' );
			$bundled_items_data = array();

			if ( ! empty( $bundled_items ) ) {
				foreach ( $bundled_items as $bundled_item ) {

					$bundled_item_data = $bundled_item->get_data();

					$bundled_item_data[ 'bundled_item_id' ] = 0;

					$bundled_items_data[] = $bundled_item_data;
				}

				$duplicated_product->set_bundled_data_items( $bundled_items_data );
			}
		}
	}

	/**
	 * Duplicates bundled items when duplicating a bundle (legacy).
	 *
	 * @param  mixed    $new_product_id
	 * @param  WP_Post  $post
	 */
	public static function duplicate_product_legacy( $new_product_id, $post ) {

		$bundled_items = WC_PB_DB::query_bundled_items( array(
			'bundle_id' => $post->ID,
			'return'    => 'objects'
		) );

		if ( ! empty( $bundled_items ) ) {
			foreach ( $bundled_items as $bundled_item ) {
				$bundled_item_data = $bundled_item->get_data();
				WC_PB_DB::add_bundled_item( array(
					'bundle_id'  => $new_product_id,                    // Use the new bundle id.
					'product_id' => $bundled_item_data[ 'product_id' ],
				 	'menu_order' => $bundled_item_data[ 'menu_order' ],
				 	'meta_data'  => $bundled_item_data[ 'meta_data' ]
				 ) );
			}
			WC_Cache_Helper::get_transient_version( 'product', true );
		}
	}

	/**
	 * Deletes bundled item DB entries when: i) their container product bundle is deleted, or ii) the associated bundled product is deleted.
	 *
	 * @param  mixed  $id  ID of post being deleted.
	 */
	public static function delete_post( $id ) {

		if ( ! current_user_can( 'delete_posts' ) ) {
			return;
		}

		if ( $id > 0 ) {

			$post_type = get_post_type( $id );

			if ( 'product' === $post_type ) {
				self::delete_product( $id );
			}
		}
	}

	/**
	 * Deletes bundled item DB entries when: i) their container product bundle is deleted, or ii) the associated bundled product is deleted.
	 *
	 * @param  mixed  $id  ID of product being deleted.
	 */
	public static function delete_product( $id ) {

		// Delete bundled item DB entries and meta when deleting a bundle.
		$bundled_items = WC_PB_DB::query_bundled_items( array(
			'bundle_id' => $id,
			'return'    => 'objects'
		) );

		if ( ! empty( $bundled_items ) ) {
			foreach ( $bundled_items as $bundled_item ) {
				$bundled_item->delete();
			}
		}

		// Delete bundled item DB entries and meta when deleting an associated product.
		$bundled_item_ids = array_keys( wc_pb_get_bundled_product_map( $id, false ) );

		if ( ! empty( $bundled_item_ids ) ) {
			foreach ( $bundled_item_ids as $bundled_item_id ) {
				WC_PB_DB::delete_bundled_item( $bundled_item_id );
			}
		}
	}

	/**
	 * When deleting a bundled item from the DB, clear the transients of the container bundle.
	 *
	 * @param  WC_Bundled_Item_Data  $item  The bundled item DB object being deleted.
	 */
	public static function delete_bundled_item( $item ) {
		$bundle_id = $item->get_bundle_id();
		wc_delete_product_transients( $bundle_id );
	}

	/**
	 * Delete price meta reserved to bundles/composites (legacy).
	 *
	 * @param  int  $post_id
	 * @return void
	 */
	public static function delete_reserved_price_post_meta( $post_id ) {

		// Get product type.
		$product_type = WC_PB_Core_Compatibility::get_product_type( $post_id );

		if ( false === in_array( $product_type, array( 'bundle', 'composite' ) ) ) {
			delete_post_meta( $post_id, '_wc_sw_max_price' );
			delete_post_meta( $post_id, '_wc_sw_max_regular_price' );
		}
	}

	/**
	 * Delete price meta reserved to bundles/composites.
	 *
	 * @param  WC_Product  $product
	 * @return void
	 */
	public static function delete_reserved_price_meta( $product ) {

		$product->delete_meta_data( '_wc_pb_bundled_value' );
		$product->delete_meta_data( '_wc_pb_bundled_weight' );

		if ( false === in_array( $product->get_type(), array( 'bundle', 'composite' ) ) ) {
			$product->delete_meta_data( '_wc_sw_max_price' );
			$product->delete_meta_data( '_wc_sw_max_regular_price' );
		}
	}

	/**
	 * Delete bundled item stock meta cache when an associated product stock (status) changes.
	 *
	 * @param  WC_Product  $product
	 * @return void
	 */
	public static function delete_bundled_items_stock_cache( $product_id ) {
		global $wpdb;

		$bundled_item_ids = array_keys( wc_pb_get_bundled_product_map( $product_id, false ) );

		if ( ! empty( $bundled_item_ids ) ) {

			// Flush stock cache.
			WC_PB_DB::flush_stock_cache( $bundled_item_ids );

			do_action( 'woocommerce_delete_bundled_items_stock_cache', $product_id, $bundled_item_ids );

			/**
			 * 'woocommerce_sync_bundled_items_stock_status' filter.
			 *
			 * Use this filter to always re-sync all bundled items stock meta when the associated product stock (status) changes.
			 * Instead of deleting the bundled items stock meta and refreshing them on-demand, this will effectively keep them in sync all the time.
			 *
			 * Off by default -- enabling this may put a heavy load on the server in cases where the same product is contained in a large number of bundles.
			 *
			 * This makes it possible, for instance, to reliably run bundled item stock meta queries in order to:
			 *
			 * - Get all bundle ids that contain out of stock items.
			 * - Get all product ids associated with out of stock bundled items.
			 *
			 * @param  boolean  $sync_bundled_item_stock_meta
			 */
			if ( apply_filters( 'woocommerce_sync_bundled_items_stock_status', false ) ) {
				foreach ( $bundled_item_ids as $bundled_item_id ) {

					// Create a 'WC_Bundled_Item' instance to re-sync and update the bundled item stock meta.
					$bundled_item = wc_pb_get_bundled_item( $bundled_item_id );

					if ( $bundled_item ) {
						$bundled_item->sync_stock();
					}
				}
			}
		}
	}

	/**
	 * Delete bundled item stock meta cache when an associated product stock changes.
	 *
	 * @param  mixed   $product_id
	 * @param  string  $stock_status
	 * @param  mixed   $product
	 * @return void
	 */
	public static function product_stock_status_changed( $product_id, $stock_status, $product = null ) {

		if ( is_null( $product ) ) {
			$product = wc_get_product( $product_id );
		}

		$bundled_product_id = $product->is_type( 'variation' ) ? WC_PB_Core_Compatibility::get_parent_id( $product ) : WC_PB_Core_Compatibility::get_id( $product );

		self::delete_bundled_items_stock_cache( $bundled_product_id );
	}

	/**
	 * Delete bundled item stock meta cache when an associated product stock changes.
	 *
	 * @param  WC_Product  $product
	 * @return void
	 */
	public static function product_stock_changed( $product ) {

		$bundled_product_id = $product->is_type( 'variation' ) ? WC_PB_Core_Compatibility::get_parent_id( $product ) : WC_PB_Core_Compatibility::get_id( $product );

		self::delete_bundled_items_stock_cache( $bundled_product_id );
	}

	/**
	 * Delete associated bundled items stock cache when clearing product transients.
	 *
	 * @param  mixed  $post_id
	 * @return void
	 */
	public static function delete_bundle_transients( $post_id ) {
		if ( $post_id > 0 ) {

			/*
			 * Delete associated bundled items stock cache when clearing product transients.
			 * Workaround for https://github.com/somewherewarm/woocommerce-product-bundles/issues/22 .
			 */
			self::delete_bundled_items_stock_cache( $post_id );
		}
	}
}

WC_PB_DB_Sync::init();
