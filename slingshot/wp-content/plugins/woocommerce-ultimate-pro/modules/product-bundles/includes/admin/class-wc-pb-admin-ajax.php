<?php
/**
 * WC_PB_Admin_Ajax class
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
 * Admin AJAX meta-box handlers.
 *
 * @class     WC_PB_Admin_Ajax
 * @version   5.2.2
 */
class WC_PB_Admin_Ajax {

	/**
	 * Hook in.
	 */
	public static function init() {

		// Ajax add bundled product.
		add_action( 'wp_ajax_woocommerce_add_bundled_product', array( __CLASS__, 'ajax_add_bundled_product' ) );

		// Ajax search bundled item variations.
		add_action( 'wp_ajax_woocommerce_search_bundled_variations', array( __CLASS__, 'ajax_search_bundled_variations' ) );
	}

	/**
	 * Ajax search for bundled variations.
	 */
	public static function ajax_search_bundled_variations() {

		if ( ! empty( $_GET[ 'include' ] ) ) {
			if ( $bundle = wc_get_product( absint( $_GET[ 'include' ] ) ) ) {
				$_GET[ 'include' ] = WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ? $bundle->get_children() : implode( ', ', $bundle->get_children() );
			} else {
				$_GET[ 'include' ] = WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ? array() : '';
			}
		}

		if ( WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ) {
			WC_AJAX::json_search_products( '', true );
		} else {
			WC_AJAX::json_search_products( '', array( 'product_variation' ) );
		}
	}

	/**
	 * Handles adding bundled products via ajax.
	 */
	public static function ajax_add_bundled_product() {

		check_ajax_referer( 'wc_bundles_add_bundled_product', 'security' );

		$loop       = intval( $_POST[ 'id' ] );
		$post_id    = intval( $_POST[ 'post_id' ] );
		$product_id = intval( $_POST[ 'product_id' ] );
		$item_id    = false;
		$toggle     = 'open';
		$tabs       = WC_PB_Meta_Box_Product_Data::get_bundled_product_tabs();
		$product    = wc_get_product( $product_id );
		$title      = $product->get_title();
		$sku        = $product->get_sku();
		$title      = WC_PB_Helpers::format_product_title( $title, $sku, '', true );
		$title      = sprintf( _x( '#%1$s: %2$s', 'bundled product admin title', 'ultimatewoo-pro' ), $product_id, $title );

		$item_data         = array();
		$item_availability = '';

		$response          = array(
			'markup'  => '',
			'message' => ''
		);

		if ( $product ) {

			if ( in_array( $product->get_type(), array( 'simple', 'variable', 'subscription', 'variable-subscription' ) ) ) {

				if ( ! $product->is_in_stock() ) {
					$item_availability = '<mark class="outofstock">' . __( 'Out of stock', 'woocommerce' ) . '</mark>';
				}

				ob_start();
				include( 'meta-boxes/views/html-bundled-product-admin.php' );
				$response[ 'markup' ] = ob_get_clean();

			} else {
				$response[ 'message' ] = __( 'The selected product cannot be bundled. Please select a simple product, a variable product, or a simple/variable subscription.', 'ultimatewoo-pro' );
			}

		} else {
			$response[ 'message' ] = __( 'The selected product is invalid.', 'ultimatewoo-pro' );
		}

		wp_send_json( $response );
	}
}

WC_PB_Admin_Ajax::init();
