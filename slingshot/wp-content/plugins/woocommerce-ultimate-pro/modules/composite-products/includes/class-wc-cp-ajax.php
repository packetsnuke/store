<?php
/**
 * WC_CP_AJAX class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Composited Products AJAX Handlers.
 *
 * @class 	 WC_CP_AJAX
 * @version  3.7.0
 */
class WC_CP_AJAX {

	public static function init() {

		// Use WC ajax if available, otherwise fall back to WP ajax.
		if ( WC_CP_Core_Compatibility::use_wc_ajax() ) {

			add_action( 'wc_ajax_woocommerce_show_composited_product', __CLASS__ . '::show_composited_product_ajax' );
			add_action( 'wc_ajax_woocommerce_show_component_options', __CLASS__ . '::show_component_options_ajax' );

		} else {

			add_action( 'wp_ajax_woocommerce_show_composited_product', __CLASS__ . '::show_composited_product_ajax' );
			add_action( 'wp_ajax_woocommerce_show_component_options', __CLASS__ . '::show_component_options_ajax' );

			add_action( 'wp_ajax_nopriv_woocommerce_show_composited_product', __CLASS__ . '::show_composited_product_ajax' );
			add_action( 'wp_ajax_nopriv_woocommerce_show_component_options', __CLASS__ . '::show_component_options_ajax' );
		}
	}

	/**
	 * Display paged component options via ajax. Effective in 'thumbnails' mode only.
	 */
	public static function show_component_options_ajax() {

		$data = array();

		if ( isset( $_POST[ 'load_page' ] ) && intval( $_POST[ 'load_page' ] ) > 0 && isset( $_POST[ 'composite_id' ] ) && intval( $_POST[ 'composite_id' ] ) > 0 && ! empty( $_POST[ 'component_id' ] ) ) {

			$component_id    = intval( $_POST[ 'component_id' ] );
			$composite_id    = intval( $_POST[ 'composite_id' ] );
			$selected_option = ! empty( $_POST[ 'selected_option' ] ) ? intval( $_POST[ 'selected_option' ] ) : '';
			$load_page       = intval( $_POST[ 'load_page' ] );

		} else {

			wp_send_json( array(
				'result'  => 'failure',
				'message' => __( 'Looks like something went wrong. Please refresh the page and try again.', 'ultimatewoo-pro' )
			) );
		}

		$product = wc_get_product( $composite_id );

		$query_args = array(
			'selected_option' => $selected_option,
			'load_page'       => $load_page,
		);

		// Include orderby argument if posted -- if not, the default ordering method will be used.
		if ( ! empty( $_POST[ 'orderby' ] ) ) {
			$query_args[ 'orderby' ] = $_POST[ 'orderby' ];
		}

		// Include filters argument if posted -- if not, no filters will be applied to the query.
		if ( ! empty( $_POST[ 'filters' ] ) ) {
			$query_args[ 'filters' ] = $_POST[ 'filters' ];
		}

		// Include scenario constraints if posted -- if not, no scenario constraints will be applied to the query.
		if ( ! empty( $_POST[ 'options_in_scenarios' ] ) ) {
			$query_args[ 'options_in_scenarios' ] = $_POST[ 'options_in_scenarios' ];
		}

		$component                 = $product->get_component( $component_id );
		$component_options_data    = $component->view->get_options_data( $query_args );
		$component_pagination_data = $component->view->get_pagination_data();
		$component_scenario_data   = $product->get_current_scenario_data( array( $component_id ) );

		wp_send_json( array(
			'result'          => 'success',
			'options_data'    => $component_options_data,
			'scenario_data'   => $component_scenario_data[ 'scenario_data' ][ $component_id ],
			'pagination_data' => $component_pagination_data
		) );
	}

	/**
	 * Ajax listener that fetches product markup when a new selection is made.
	 *
	 * @param  mixed  $product_id
	 * @param  mixed  $item_id
	 * @param  mixed  $container_id
	 * @return string
	 */
	public static function show_composited_product_ajax( $product_id = '', $component_id = '', $composite_id = '' ) {

		global $product;

		if ( isset( $_POST[ 'product_id' ] ) && intval( $_POST[ 'product_id' ] ) > 0 && isset( $_POST[ 'component_id' ] ) && ! empty( $_POST[ 'component_id' ] ) && isset( $_POST[ 'composite_id' ] ) && ! empty( $_POST[ 'composite_id' ] ) ) {

			$product_id   = intval( $_POST[ 'product_id' ] );
			$component_id = intval( $_POST[ 'component_id' ] );
			$composite_id = intval( $_POST[ 'composite_id' ] );

		} else {

			wp_send_json( array(
				'result' => 'failure',
				'reason' => 'required params missing',
				'markup' => sprintf( '<div class="component_data woocommerce-error" data-price="0" data-regular_price="0" data-product_type="invalid-data">%s</div>', __( 'There was an error while updating your selection. Please refresh the page and try again.', 'ultimatewoo-pro' ) )
			) );
		}

		$composite        = wc_get_product( $composite_id );
		$component_option = $composite->get_component_option( $component_id, $product_id );

		if ( ! $component_option || ! $component_option->is_purchasable() ) {

			ob_start();

			wc_get_template( 'composited-product/invalid-product.php', array(
				'is_static' => false
			), '', WC_CP()->plugin_path() . '/templates/' );

			$output = ob_get_clean();

			wp_send_json( array(
				'result' => 'failure',
				'reason' => 'product does not exist or is not purchasable',
				'markup' => $output
			) );
		}

		$product = $component_option->get_product();

		$composite->sync_composite();

		ob_start();

 		WC_CP_Products::add_filters( $component_option );

 		/**
 		 * Action 'woocommerce_composite_show_composited_product'.
 		 *
 		 * @param  WC_Product            $product
 		 * @param  string                $component_id
 		 * @param  WC_Product_Composite  $composite
 		 */
		do_action( 'woocommerce_composite_show_composited_product', $product, $component_id, $composite );

		WC_CP_Products::remove_filters();

		$output = ob_get_clean();

		wp_send_json( array(
			'result' => 'success',
			'markup' => $output,
		) );
	}

}

WC_CP_AJAX::init();
