<?php
/**
 * WC_CP_PB_Compatibility class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    3.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks for Product Bundles compatibility.
 *
 * @version  3.7.0
 */
class WC_CP_PB_Compatibility {

	public static function init() {

		// Bundles support.
		add_action( 'woocommerce_add_cart_item', array( __CLASS__, 'bundled_cart_item_price_modification' ), 9, 2 );
		add_action( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'bundled_cart_item_session_price_modification' ), 9, 3 );

		add_action( 'woocommerce_add_cart_item', array( __CLASS__, 'bundled_cart_item_after_price_modification' ), 11 );
		add_action( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'bundled_cart_item_after_price_modification' ), 11 );
	}

	/**
	 * Add filters to modify bundled product prices when parent product is composited and has a discount.
	 *
	 * @param  array   $cart_item_data
	 * @param  string  $cart_item_key
	 * @return void
	 */
	public static function bundled_cart_item_price_modification( $cart_item_data, $cart_item_key ) {

		if ( isset( $cart_item_data[ 'bundled_by' ] ) ) {

			$bundle_key = $cart_item_data[ 'bundled_by' ];

			if ( isset( WC()->cart->cart_contents[ $bundle_key ] ) ) {

				$bundle_cart_data = WC()->cart->cart_contents[ $bundle_key ];

				if ( $composite_container_item = wc_cp_get_composited_cart_item_container( $bundle_cart_data ) ) {

					$bundle           = $bundle_cart_data[ 'data' ];
					$composite        = $composite_container_item[ 'data' ];
					$component_id     = $bundle_cart_data[ 'composite_item' ];
					$component_option = $composite->get_component_option( $component_id, WC_CP_Core_Compatibility::get_id( $bundle ) );

					WC_CP_Products::add_filters( $component_option );
				}
			}
		}

		return $cart_item_data;
	}

	/**
	 * Add filters to modify bundled product prices when parent product is composited and has a discount.
	 *
	 * @param  string  $cart_item_data
	 * @param  array   $session_item_data
	 * @param  string  $cart_item_key
	 * @return void
	 */
	public static function bundled_cart_item_session_price_modification( $cart_item_data, $session_item_data, $cart_item_key ) {
		return self::bundled_cart_item_price_modification( $cart_item_data, $cart_item_key );
	}

	/**
	 * Remove filters that modify bundled product prices when parent product is composited and has a discount.
	 *
	 * @param  string  $cart_item_data
	 * @return void
	 */
	public static function bundled_cart_item_after_price_modification( $cart_item_data ) {

		if ( isset( $cart_item_data[ 'bundled_by' ] ) ) {

			$bundle_key = $cart_item_data[ 'bundled_by' ];

			if ( isset( WC()->cart->cart_contents[ $bundle_key ] ) ) {

				$bundle_cart_data = WC()->cart->cart_contents[ $bundle_key ];

				if ( wc_cp_is_composited_cart_item( $bundle_cart_data ) ) {
					WC_CP_Products::remove_filters();
				}
			}
		}

		return $cart_item_data;
	}
}

WC_CP_PB_Compatibility::init();
