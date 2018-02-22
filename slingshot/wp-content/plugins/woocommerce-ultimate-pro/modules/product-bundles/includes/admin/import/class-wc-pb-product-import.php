<?php
/**
 * WC_PB_Product_Import class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    5.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce core Product Importer support.
 *
 * @class    WC_PB_Product_Import
 * @version  5.4.0
 */
class WC_PB_Product_Import {

	/**
	 * Hook in.
	 */
	public static function init() {

		// Map custom column titles.
		add_filter( 'woocommerce_csv_product_import_mapping_options', array( __CLASS__, 'map_columns' ) );
		add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( __CLASS__, 'add_columns_to_mapping_screen' ) );

		// Parse bundled items.
		add_filter( 'woocommerce_product_importer_parsed_data', array( __CLASS__, 'parse_bundled_items' ), 10, 2 );

		// Set bundle-type props.
		add_filter( 'woocommerce_product_import_pre_insert_product_object', array( __CLASS__, 'set_bundle_props' ), 10, 2 );
	}

	/**
	 * Register the 'Custom Column' column in the importer.
	 *
	 * @param  array  $options
	 * @return array  $options
	 */
	public static function map_columns( $options ) {

		$options[ 'wc_pb_bundled_items' ]             = __( 'Bundled Items (JSON-encoded)', 'ultimatewoo-pro' );
		$options[ 'wc_pb_layout' ]                    = __( 'Bundle Layout', 'ultimatewoo-pro' );
		$options[ 'wc_pb_editable_in_cart' ]          = __( 'Bundle Cart Editing', 'ultimatewoo-pro' );
		$options[ 'wc_pb_sold_individually_context' ] = __( 'Bundle Sold Individually', 'ultimatewoo-pro' );

		return $options;
	}

	/**
	 * Add automatic mapping support for custom columns.
	 *
	 * @param  array  $columns
	 * @return array  $columns
	 */
	public static function add_columns_to_mapping_screen( $columns ) {

		$columns[ __( 'Bundled Items (JSON-encoded)', 'ultimatewoo-pro' ) ] = 'wc_pb_bundled_items';
		$columns[ __( 'Bundle Layout', 'ultimatewoo-pro' ) ]                = 'wc_pb_layout';
		$columns[ __( 'Bundle Cart Editing', 'ultimatewoo-pro' ) ]          = 'wc_pb_editable_in_cart';
		$columns[ __( 'Bundle Sold Individually', 'ultimatewoo-pro' ) ]     = 'wc_pb_sold_individually_context';

		// Always add English mappings.
		$columns[ 'Bundled Items (JSON-encoded)' ] = 'wc_pb_bundled_items';
		$columns[ 'Bundle Layout' ]                = 'wc_pb_layout';
		$columns[ 'Bundle Cart Editing' ]          = 'wc_pb_editable_in_cart';
		$columns[ 'Bundle Sold Individually' ]     = 'wc_pb_sold_individually_context';

		return $columns;
	}

	/**
	 * Decode bundled data items and parse relative IDs.
	 *
	 * @param  array                    $parsed_data
	 * @param  WC_Product_CSV_Importer  $importer
	 * @return array
	 */
	public static function parse_bundled_items( $parsed_data, $importer ) {

		if ( ! empty( $parsed_data[ 'wc_pb_bundled_items' ] ) ) {

			$bundled_data_items = json_decode( $parsed_data[ 'wc_pb_bundled_items' ], true );

			unset( $parsed_data[ 'wc_pb_bundled_items' ] );

			if ( is_array( $bundled_data_items ) ) {

				$parsed_data[ 'wc_pb_bundled_items' ] = array();

				foreach ( $bundled_data_items as $bundled_data_item_key => $bundled_data_item ) {

					$bundled_product_id = $bundled_data_items[ $bundled_data_item_key ][ 'product_id' ];

					$parsed_data[ 'wc_pb_bundled_items' ][ $bundled_data_item_key ]                 = $bundled_data_item;
					$parsed_data[ 'wc_pb_bundled_items' ][ $bundled_data_item_key ][ 'product_id' ] = $importer->parse_relative_field( $bundled_product_id );
				}
			}
		}

		return $parsed_data;
	}

	/**
	 * Set bundle-type props.
	 *
	 * @param  array  $parsed_data
	 * @return array
	 */
	public static function set_bundle_props( $product, $data ) {

		if ( is_a( $product, 'WC_Product' ) && $product->is_type( 'bundle' ) ) {

			$bundled_data_items = ! empty( $data[ 'wc_pb_bundled_items' ] ) ? $data[ 'wc_pb_bundled_items' ] : array();

			$props = array(
				'editable_in_cart'          => isset( $data[ 'wc_pb_editable_in_cart' ] ) && 1 === intval( $data[ 'wc_pb_editable_in_cart' ] ) ? 'yes' : 'no',
				'layout'                    => isset( $data[ 'wc_pb_layout' ] ) ? $data[ 'wc_pb_layout' ] : 'default',
				'sold_individually_context' => isset( $data[ 'sold_individually_context' ] ) ? $data[ 'sold_individually_context' ] : 'product',
				'bundled_data_items'        => $bundled_data_items
			);

			$product->set_props( $props );
		}

		return $product;
	}
}

WC_PB_Product_Import::init();
