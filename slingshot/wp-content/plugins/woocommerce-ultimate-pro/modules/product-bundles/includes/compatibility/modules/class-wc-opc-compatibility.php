<?php
/**
 * WC_PB_OPC_Compatibility class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    4.11.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One Page Checkout Compatibility.
 *
 * @since  5.2.2
 */
class WC_PB_OPC_Compatibility {

	public static function init() {

		// OPC support.
		add_action( 'wcopc_bundle_add_to_cart', array( __CLASS__, 'opc_single_add_to_cart_bundle' ) );
		add_filter( 'wcopc_allow_cart_item_modification', array( __CLASS__, 'opc_disallow_bundled_cart_item_modification' ), 10, 4 );
	}

	/**
	 * OPC Single-product bundle-type add-to-cart template.
	 *
	 * @param  int  $opc_post_id
	 * @return void
	 */
	public static function opc_single_add_to_cart_bundle( $opc_post_id ) {

		global $product;

		// Enqueue script
		wp_enqueue_script( 'wc-add-to-cart-bundle' );
		wp_enqueue_style( 'wc-bundle-css' );

		if ( $product->is_purchasable() ) {

			$bundled_items = $product->get_bundled_items();

			if ( ! empty( $bundled_items ) ) {

				ob_start();

				wc_get_template( 'single-product/add-to-cart/bundle.php', array(
					'availability_html' => WC_PB_Core_Compatibility::wc_get_stock_html( $product ),
					'bundle_price_data' => $product->get_bundle_price_data(),
					'bundled_items'     => $bundled_items,
					'product'           => $product,
					'product_id'        => WC_PB_Core_Compatibility::get_id( $product )
				), false, WC_PB()->plugin_path() . '/templates/' );

				echo str_replace( array( '<form method="post" enctype="multipart/form-data"', '</form>' ), array( '<div', '</div>' ), ob_get_clean() );
			}
		}
	}

	/**
	 * Prevent OPC from managing bundled items.
	 *
	 * @param  bool    $allow
	 * @param  array   $cart_item
	 * @param  string  $cart_item_key
	 * @param  string  $opc_id
	 * @return bool
	 */
	public static function opc_disallow_bundled_cart_item_modification( $allow, $cart_item, $cart_item_key, $opc_id ) {
		if ( wc_pb_is_bundled_cart_item( $cart_item ) ) {
			$allow = false;
		}
		return $allow;
	}
}

WC_PB_OPC_Compatibility::init();
