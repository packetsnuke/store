<?php
/**
 * WC_CP_Admin_Ajax class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    3.7.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin AJAX meta-box handlers.
 *
 * @class     WC_CP_Admin_Ajax
 * @version   3.9.0
 */
class WC_CP_Admin_Ajax {

	/**
	 * Hook in.
	 */
	public static function init() {

		// Ajax save composite config.
		add_action( 'wp_ajax_woocommerce_bto_composite_save', array( __CLASS__, 'ajax_composite_save' ) );

		// Ajax add component.
		add_action( 'wp_ajax_woocommerce_add_composite_component', array( __CLASS__, 'ajax_add_component' ) );

		// Ajax add scenario.
		add_action( 'wp_ajax_woocommerce_add_composite_scenario', array( __CLASS__, 'ajax_add_scenario' ) );

		// Ajax search products and variations in scenarios.
		add_action( 'wp_ajax_woocommerce_json_search_products_in_component', array( __CLASS__, 'search_products_in_component' ) );
		add_action( 'wp_ajax_woocommerce_json_search_products_and_variations_in_component', array( __CLASS__, 'search_products_and_variations_in_component' ) );
	}

	/**
	 * Handles saving composite config via ajax.
	 *
	 * @return void
	 */
	public static function ajax_composite_save() {

		check_ajax_referer( 'wc_bto_save_composite', 'security' );

		parse_str( $_POST[ 'data' ], $posted_composite_data );

		$post_id = absint( $_POST[ 'post_id' ] );

		WC_CP_Meta_Box_Product_Data::save_configuration( $post_id, $posted_composite_data );

		wc_delete_product_transients( $post_id );

		wp_send_json( WC_CP_Meta_Box_Product_Data::$ajax_notices );
	}

	/**
	 * Handles adding components via ajax.
	 *
	 * @return void
	 */
	public static function ajax_add_component() {

		check_ajax_referer( 'wc_bto_add_component', 'security' );

		$id      = intval( $_POST[ 'id' ] );
		$post_id = intval( $_POST[ 'post_id' ] );

		$component_data = array();

		/**
		 * Action 'woocommerce_composite_component_admin_html'.
		 *
		 * @param  int     $id
		 * @param  array   $component_data
		 * @param  int     $post_id
		 * @param  string  $state
		 *
		 * @hooked {@see component_admin_html} - 10
		 */
		do_action( 'woocommerce_composite_component_admin_html', $id, $component_data, $post_id, 'open' );

		die();
	}

	/**
	 * Handles adding scenarios via ajax.
	 *
	 * @return void
	 */
	public static function ajax_add_scenario() {

		check_ajax_referer( 'wc_bto_add_scenario', 'security' );

		$id      = intval( $_POST[ 'id' ] );
		$post_id = intval( $_POST[ 'post_id' ] );

		$composite      = new WC_Product_Composite( $post_id );
		$composite_data = $composite->get_composite_data();
		$scenario_data  = array();

		/**
		 * Action 'woocommerce_composite_scenario_admin_html'.
		 *
		 * @param  int     $id
		 * @param  array   $scenario_data
		 * @param  array   $composite_data
		 * @param  int     $post_id
		 * @param  string  $state
		 *
		 * @hooked {@see scenario_admin_html} - 10
		 */
		do_action( 'woocommerce_composite_scenario_admin_html', $id, $scenario_data, $composite_data, $post_id, 'open' );

		die();
	}

	/**
	 * Search for products and variations in component.
	 *
	 * @return void
	 */
	public static function search_products_and_variations_in_component() {
		self::search_products_in_component( array( 'include_variations' => true ) );
	}

	/**
	 * Search for products and variations in component.
	 *
	 * @param  array  $args
	 * @return void
	 */
	public static function search_products_in_component( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'include_variations' => false
		) );

		if ( ! empty( $_GET[ 'include' ] ) ) {

			$include           = $_GET[ 'include' ];
			$composite_id      = isset( $include[ 'composite_id' ] ) ? absint( $include[ 'composite_id' ] ) : false;
			$component_id      = isset( $include[ 'component_id' ] ) ? absint( $include[ 'component_id' ] ) : false;
			$composite         = $composite_id && $component_id ? wc_get_product( $composite_id ) : false;
			$component         = $composite ? $composite->get_component( $component_id ) : false;
			$component_options = $component ? WC_CP_Component::query_component_options( $component->get_data() ) : array();

			$_GET[ 'include' ] = WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ? $component_options : implode( ', ', $component_options );
		}

		if ( $args[ 'include_variations' ] ) {
			add_filter( 'woocommerce_json_search_found_products', array( __CLASS__, 'add_variations_to_component_search_results' ) );
		}

		WC_AJAX::json_search_products();

		if ( $args[ 'include_variations' ] ) {
			remove_filter( 'woocommerce_json_search_found_products', array( __CLASS__, 'add_variations_to_component_search_results' ) );
		}
	}

	/**
	 * Add variations to component product search results.
	 *
	 * @param  array  $search_results
	 * @return array
	 */
	public static function add_variations_to_component_search_results( $search_results ) {

		$search_results_incl_variations = array();

		if ( ! empty( $search_results ) ) {

			$search_result_objects = array_map( 'wc_get_product', array_keys( $search_results ) );

			foreach ( $search_result_objects as $product ) {
				if ( $product ) {

					$product_id                                    = WC_CP_Core_Compatibility::get_id( $product );
					$search_results_incl_variations[ $product_id ] = WC_CP_Helpers::get_product_title( $product, $product->is_type( 'variable' ) ? sprintf( _x( '%s &ndash; Any Variation', 'any product variation', 'ultimatewoo-pro' ), $product->get_title() ) : '' );

					if ( $product->is_type( 'variable' ) ) {

						$child_ids     = $product->get_children();
						$child_objects = array_map( 'wc_get_product', $child_ids );

						if ( ! empty( $child_objects ) ) {
							foreach ( $child_objects as $child ) {
								if ( $child ) {
									$child_id                                    = WC_CP_Core_Compatibility::get_id( $child );
									$search_results_incl_variations[ $child_id ] = WC_CP_Helpers::get_product_variation_title( $child, WC_CP_Core_Compatibility::is_wc_version_gte_2_7() );
								}
							}
						}
					}
				}
			}
		}

		return $search_results_incl_variations;
	}
}

WC_CP_Admin_Ajax::init();
