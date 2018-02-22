<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Min_Max_Quantities_Addons {

	/**
	 * Checks if product is of type "composite"
	 *
	 * @access public
	 * @since 2.3.9
	 * @version 2.3.9
	 * @return
	 */
	public function is_composite_product( $product_id ) {
		if ( empty( $product_id ) ) {
			return false;
		}

		$product = wc_get_product( $product_id );

		if ( 'composite' === $product->get_type() ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if checkout page is on set multiple shipping addresses
	 *
	 * @access public
	 * @since 2.3.15
	 * @version 2.3.15
	 * @return
	 */
	public function is_multiple_shipping_address_page() {
		$page_id = wc_get_page_id( 'multiple_addresses' );

		if ( $page_id !== -1 && is_page( $page_id ) ) {
			return true;
		}

		return false;
	}
}
