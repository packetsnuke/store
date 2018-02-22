<?php
/**
 * WC_CP_Product_Import class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    3.11.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce core Product Importer support.
 *
 * @class    WC_CP_Product_Import
 * @version  3.11.0
 */
class WC_CP_Product_Import {

	/**
	 * Hook in.
	 */
	public static function init() {

		// Map custom column titles.
		add_filter( 'woocommerce_csv_product_import_mapping_options', array( __CLASS__, 'map_columns' ) );
		add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( __CLASS__, 'add_columns_to_mapping_screen' ) );

		// Parse components.
		add_filter( 'woocommerce_product_importer_parsed_data', array( __CLASS__, 'parse_components' ), 10, 2 );

		// Parse scenarios.
		add_filter( 'woocommerce_product_importer_parsed_data', array( __CLASS__, 'parse_scenarios' ), 10, 2 );

		// Set composite-type props.
		add_filter( 'woocommerce_product_import_pre_insert_product_object', array( __CLASS__, 'set_composite_props' ), 10, 2 );
	}

	/**
	 * Register the 'Custom Column' column in the importer.
	 *
	 * @param  array  $options
	 * @return array  $options
	 */
	public static function map_columns( $options ) {

		$options[ 'wc_cp_components' ]                = __( 'Composite Components (JSON-encoded)', 'ultimatewoo-pro' );
		$options[ 'wc_cp_scenarios' ]                 = __( 'Composite Scenarios (JSON-encoded)', 'ultimatewoo-pro' );
		$options[ 'wc_cp_layout' ]                    = __( 'Composite Layout', 'ultimatewoo-pro' );
		$options[ 'wc_cp_editable_in_cart' ]          = __( 'Composite Cart Editing', 'ultimatewoo-pro' );
		$options[ 'wc_cp_sold_individually_context' ] = __( 'Composite Sold Individually', 'ultimatewoo-pro' );
		$options[ 'wc_cp_hide_shop_price' ]           = __( 'Composite Shop Price Hidden', 'ultimatewoo-pro' );

		return $options;
	}

	/**
	 * Add automatic mapping support for custom columns.
	 *
	 * @param  array  $columns
	 * @return array  $columns
	 */
	public static function add_columns_to_mapping_screen( $columns ) {

		$columns[ __( 'Composite Components (JSON-encoded)', 'ultimatewoo-pro' ) ] = 'wc_cp_components';
		$columns[ __( 'Composite Scenarios (JSON-encoded)', 'ultimatewoo-pro' ) ]  = 'wc_cp_scenarios';
		$columns[ __( 'Composite Layout', 'ultimatewoo-pro' ) ]                    = 'wc_cp_layout';
		$columns[ __( 'Composite Cart Editing', 'ultimatewoo-pro' ) ]              = 'wc_cp_editable_in_cart';
		$columns[ __( 'Composite Sold Individually', 'ultimatewoo-pro' ) ]         = 'wc_cp_sold_individually_context';
		$columns[ __( 'Composite Shop Price Hidden', 'ultimatewoo-pro' ) ]         = 'wc_cp_hide_shop_price';

		// Always add English mappings.
		$columns[ 'Composite Components (JSON-encoded)' ] = 'wc_cp_components';
		$columns[ 'Composite Scenarios (JSON-encoded)' ]  = 'wc_cp_scenarios';
		$columns[ 'Composite Layout' ]                    = 'wc_cp_layout';
		$columns[ 'Composite Cart Editing' ]              = 'wc_cp_editable_in_cart';
		$columns[ 'Composite Sold Individually' ]         = 'wc_cp_sold_individually_context';
		$columns[ 'Composite Shop Price Hidden' ]         = 'wc_cp_hide_shop_price';

		return $columns;
	}

	/**
	 * Decode component data and parse relative IDs.
	 *
	 * @param  array                    $parsed_data
	 * @param  WC_Product_CSV_Importer  $importer
	 * @return array
	 */
	public static function parse_components( $parsed_data, $importer ) {

		if ( ! empty( $parsed_data[ 'wc_cp_components' ] ) ) {

			$components_rest_data = json_decode( $parsed_data[ 'wc_cp_components' ], true );

			unset( $parsed_data[ 'wc_cp_components' ] );

			if ( is_array( $components_rest_data ) ) {

				$parsed_data[ 'wc_cp_components' ] = array();

				foreach ( $components_rest_data as $component_rest_data ) {

					$parsed_component_data = $component_rest_data;

					// Parse query data.
					if ( ! empty( $component_rest_data[ 'query_ids' ] ) ) {
						if ( isset( $component_rest_data[ 'query_type' ] ) && 'category_ids' === $component_rest_data[ 'query_type' ] ) {
							$parsed_component_data[ 'query_ids' ] = $importer->parse_categories_field( $component_rest_data[ 'query_ids' ] );
						} else {
							$parsed_component_data[ 'query_ids' ] = $importer->parse_relative_comma_field( $component_rest_data[ 'query_ids' ] );
						}
					}

					// Parse default option.
					if ( ! empty( $component_rest_data[ 'default_option_id' ] ) ) {
						$parsed_component_data[ 'default_option_id' ] = $importer->parse_relative_field( $component_rest_data[ 'default_option_id' ] );
					}

					// Sanitize.
					$parsed_data[ 'wc_cp_components' ][] = WC_CP_REST_API::sanitize_rest_api_component_data( $parsed_component_data );
				}
			}
		}

		return $parsed_data;
	}

	/**
	 * Decode scenario data and parse relative IDs.
	 *
	 * @param  array                    $parsed_data
	 * @param  WC_Product_CSV_Importer  $importer
	 * @return array
	 */
	public static function parse_scenarios( $parsed_data, $importer ) {

		if ( ! empty( $parsed_data[ 'wc_cp_scenarios' ] ) ) {

			$scenarios_rest_data = json_decode( $parsed_data[ 'wc_cp_scenarios' ], true );

			unset( $parsed_data[ 'wc_cp_scenarios' ] );

			if ( is_array( $scenarios_rest_data ) ) {

				$parsed_data[ 'wc_cp_scenarios' ] = array();

				foreach ( $scenarios_rest_data as $scenario_rest_data ) {

					$parsed_scenario_data = $scenario_rest_data;

					if ( ! empty( $scenario_rest_data[ 'configuration' ] ) ) {
						foreach ( $scenario_rest_data[ 'configuration' ] as $component_index => $component_configuration ) {
							if ( ! empty( $component_configuration[ 'component_options' ] ) ) {

								$option_ids = explode( ',', $component_configuration[ 'component_options' ] );

								$has_any  = in_array( 'selection:any', $option_ids ) || in_array( '0', $option_ids );
								$has_none = in_array( 'selection:none', $option_ids ) || in_array( '-1', $option_ids );

								$option_ids = array_diff( $option_ids, array( 'selection:any', 'selection:none', '0', '-1' ) );

								if ( ! empty( $option_ids ) ) {
									$option_ids = implode( ',', $option_ids );
									$parsed_scenario_data[ 'configuration' ][ $component_index ][ 'component_options' ] = $importer->parse_relative_comma_field( $option_ids );
								} else {
									$parsed_scenario_data[ 'configuration' ][ $component_index ][ 'component_options' ] = array();
								}

								if ( $has_any ) {
									$parsed_scenario_data[ 'configuration' ][ $component_index ][ 'component_options' ][] = '0';
								}

								if ( $has_none ) {
									$parsed_scenario_data[ 'configuration' ][ $component_index ][ 'component_options' ][] = '-1';
								}
							}
						}
					}

					// Sanitize.
					$parsed_data[ 'wc_cp_scenarios' ][] = WC_CP_REST_API::sanitize_rest_api_scenario_data( $parsed_scenario_data );
				}
			}
		}

		return $parsed_data;
	}

	/**
	 * Set composite-type props.
	 *
	 * @param  array  $parsed_data
	 * @return array
	 */
	public static function set_composite_props( $product, $data ) {

		if ( is_a( $product, 'WC_Product' ) && $product->is_type( 'composite' ) ) {

			try {

				$composite_data = array();

				if ( ! empty( $data[ 'wc_cp_components' ] ) ) {

					$timestamp = current_time( 'timestamp' );
					$loop      = 0;

					foreach ( $data[ 'wc_cp_components' ] as $component_data ) {

						if ( empty( $component_data[ 'id' ] ) ) {
							$component_id = strval( $timestamp + $loop );
							$loop++;
						} else {
							$component_id = $component_data[ 'id' ];
						}

						// Convert schema.
						$component_data = WC_CP_REST_API::convert_rest_api_component_data( $component_data );

						// Validate data.
						$composite_data[ $component_id ] = WC_CP_REST_API::validate_internal_component_data( $component_data );

						if ( ! empty( $component_data[ 'thumbnail_id' ] ) || ! empty( $component_data[ 'thumbnail_src' ] ) ) {

							$thumbnail_id  = ! empty( $component_data[ 'thumbnail_id' ] ) ? $component_data[ 'thumbnail_id' ] : '';
							$thumbnail_src = ! empty( $component_data[ 'thumbnail_src' ] ) ? $component_data[ 'thumbnail_src' ] : '';

							$composite_data[ $component_id ][ 'thumbnail_id' ] = WC_CP_Component::set_thumbnail( $thumbnail_id, $thumbnail_src, $product );
						}
					}
				}

				$scenarios_data = array();

				if ( ! empty( $data[ 'wc_cp_scenarios' ] ) ) {

					$timestamp = current_time( 'timestamp' );
					$loop      = 0;

					foreach ( $data[ 'wc_cp_scenarios' ] as $scenario_data ) {

						if ( empty( $scenario_data[ 'id' ] ) ) {
							$scenario_id = strval( $timestamp + $loop );
							$loop++;
						} else {
							$scenario_id = $scenario_data[ 'id' ];
						}

						// Validate data.
						$scenario_data = WC_CP_REST_API::validate_rest_api_scenario_data( $scenario_data );

						// Convert schema.
						$scenarios_data[ $scenario_id ] = WC_CP_REST_API::convert_rest_api_scenario_data( $scenario_data );
					}
				}

			} catch ( WC_REST_Exception $e ) {
				return new WP_Error( 'woocommerce_product_importer_error', $e->getMessage(), array( 'status' => $e->getCode() ) );
			}

			$props = array(
				'layout'                    => isset( $data[ 'wc_cp_layout' ] ) ? $data[ 'wc_cp_layout' ] : 'single',
				'sold_individually_context' => isset( $data[ 'wc_cp_sold_individually_context' ] ) ? $data[ 'wc_cp_sold_individually_context' ] : 'product',
				'editable_in_cart'          => isset( $data[ 'wc_cp_editable_in_cart' ] ) && 1 === intval( $data[ 'wc_cp_editable_in_cart' ] ) ? 'yes' : 'no',
				'hide_shop_price'           => isset( $data[ 'wc_cp_hide_shop_price' ] ) && 1 === intval( $data[ 'wc_cp_hide_shop_price' ] ) ? 'yes' : 'no',
			);

			$product->set_props( $props );

			$product->set_composite_data( $composite_data );
			$product->set_scenario_data( $scenarios_data );
		}

		return $product;
	}
}

WC_CP_Product_Import::init();
