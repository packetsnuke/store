<?php
/**
 * WC_Product_Bundle_Data_Store_CPT class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    5.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC Product Bundle Data Store class
 *
 * Bundle data stored as Custom Post Type. For use with the WC 2.7+ CRUD API.
 *
 * @class  WC_Product_Bundle_Data_Store_CPT
 * @since  5.2.0
 */
class WC_Product_Bundle_Data_Store_CPT extends WC_Product_Data_Store_CPT {

	/**
	 * Data stored in meta keys, but not considered "meta" for the Bundle type.
	 * @var array
	 */
	protected $extended_internal_meta_keys = array(
		'_wc_pb_base_price',
		'_wc_pb_base_regular_price',
		'_wc_pb_base_sale_price',
		'_wc_pb_layout_style',
		'_wc_pb_edit_in_cart',
		'_wc_pb_sold_individually_context',
		'_wc_sw_max_price',
		'_wc_sw_max_regular_price'
	);

	/**
	 * Maps extended properties to meta keys.
	 * @var array
	 */
	protected $props_to_meta_keys = array(
		'price'                     => '_wc_pb_base_price',
		'regular_price'             => '_wc_pb_base_regular_price',
		'sale_price'                => '_wc_pb_base_sale_price',
		'layout'                    => '_wc_pb_layout_style',
		'editable_in_cart'          => '_wc_pb_edit_in_cart',
		'sold_individually_context' => '_wc_pb_sold_individually_context',
		'min_raw_price'             => '_price',
		'min_raw_regular_price'     => '_regular_price',
		'max_raw_price'             => '_wc_sw_max_price',
		'max_raw_regular_price'     => '_wc_sw_max_regular_price'
	);

	/**
	 * Callback to exclude bundle-specific meta data.
	 *
	 * @param  object  $meta
	 * @return bool
	 */
	protected function exclude_internal_meta_keys( $meta ) {
		return parent::exclude_internal_meta_keys( $meta ) && ! in_array( $meta->meta_key, $this->extended_internal_meta_keys );
	}

	/**
	 * Reads all bundle-specific post meta.
	 *
	 * @param  WC_Product_Bundle  $product
	 */
	protected function read_product_data( &$product ) {

		parent::read_product_data( $product );

		$id           = $product->get_id();
		$props_to_set = array();

		foreach ( $this->props_to_meta_keys as $property => $meta_key ) {

			// Get meta value.
			$meta_value = get_post_meta( $id, $meta_key, true );

			// Add to props array.
			$props_to_set[ $property ] = $meta_value;
		}

		// Base prices are overridden by NYP min price.
		if ( $product->is_nyp() ) {
			$props_to_set[ 'price' ]      = $props_to_set[ 'regular_price' ] = get_post_meta( $id, '_min_price', true );
			$props_to_set[ 'sale_price' ] = '';
		}

		$product->set_props( $props_to_set );
	}

	/**
	 * Writes all bundle-specific post meta.
	 *
	 * @param  WC_Product_Bundle  $product
	 * @param  boolean            $force
	 */
	protected function update_post_meta( &$product, $force = false ) {

		parent::update_post_meta( $product, $force );

		$id                 = $product->get_id();
		$meta_keys_to_props = array_flip( array_diff_key( $this->props_to_meta_keys, array( 'price' => 1, 'min_raw_price' => 1, 'min_raw_regular_price' => 1, 'max_raw_price' => 1, 'max_raw_regular_price' => 1 ) ) );
		$props_to_update    = $force ? $meta_keys_to_props : $this->get_props_to_update( $product, $meta_keys_to_props );

		foreach ( $props_to_update as $meta_key => $property ) {

			$property_get_fn = 'get_' . $property;

			// Get meta value.
			$meta_value = $product->$property_get_fn( 'edit' );

			// Sanitize it for storage.
			if ( 'editable_in_cart' === $property ) {
				$meta_value = wc_bool_to_string( $meta_value );
			}

			$updated = update_post_meta( $id, $meta_key, $meta_value );

			if ( $updated && ! in_array( $property, $this->updated_props ) ) {
				$this->updated_props[] = $property;
			}
		}
	}

	/**
	 * Handle updated meta props after updating meta data.
	 *
	 * @param  WC_Product_Bundle  $product
	 */
	protected function handle_updated_props( &$product ) {

		$id = $product->get_id();

		if ( in_array( 'date_on_sale_from', $this->updated_props ) || in_array( 'date_on_sale_to', $this->updated_props ) || in_array( 'regular_price', $this->updated_props ) || in_array( 'sale_price', $this->updated_props ) ) {
			if ( $product->is_on_sale( 'update-price' ) ) {
				update_post_meta( $id, '_wc_pb_base_price', $product->get_sale_price( 'edit' ) );
				$product->set_price( $product->get_sale_price( 'edit' ) );
			} else {
				update_post_meta( $id, '_wc_pb_base_price', $product->get_regular_price( 'edit' ) );
				$product->set_price( $product->get_regular_price( 'edit' ) );
			}
		}

		if ( in_array( 'stock_quantity', $this->updated_props ) ) {
			do_action( 'woocommerce_product_set_stock', $product );
		}

		if ( in_array( 'stock_status', $this->updated_props ) ) {
			do_action( 'woocommerce_product_set_stock_status', $product->get_id(), $product->get_stock_status(), $product );
		}

		// Trigger action so 3rd parties can deal with updated props.
		do_action( 'woocommerce_product_object_updated_props', $product, $this->updated_props );

		// After handling, we can reset the props array.
		$this->updated_props = array();
	}

	/**
	 * Writes bundle raw price meta to the DB.
	 *
	 * @param  WC_Product_Bundle  $product
	 */
	public function update_raw_prices( &$product ) {

		$id = $product->get_id();

		update_post_meta( $id, '_price', $product->get_min_raw_price( 'edit' ) );
		update_post_meta( $id, '_regular_price', $product->get_min_raw_regular_price( 'edit' ) );
		update_post_meta( $id, '_wc_sw_max_price', $product->get_max_raw_price( 'edit' ) );
		update_post_meta( $id, '_wc_sw_max_regular_price', $product->get_max_raw_regular_price( 'edit' ) );

		if ( $product->is_on_sale( 'edit' ) ) {
			update_post_meta( $id, '_sale_price', $product->get_min_raw_price( 'edit' ) );
		} else {
			update_post_meta( $id, '_sale_price', '' );
		}
	}
}
