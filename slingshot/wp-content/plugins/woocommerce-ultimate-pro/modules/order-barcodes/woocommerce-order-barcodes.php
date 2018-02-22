<?php
/*
 * @package WordPress
 * @author Automattic
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( is_woocommerce_active() ) {

	// Include plugin class files
	require_once( 'includes/class-woocommerce-order-barcodes.php' );
	require_once( 'includes/class-woocommerce-order-barcodes-settings.php' );

	// Include plugin functions file
	require_once( 'includes/woocommerce-order-barcodes-functions.php' );

	/**
	 * Returns the main instance of WooCommerce_Order_Barcodes to prevent the need to use globals.
	 *
	 * @since  1.0.0
	 * @return object WooCommerce_Order_Barcodes instance
	 */
	function WC_Order_Barcodes () {
		$instance = WooCommerce_Order_Barcodes::instance( __FILE__, '1.3.0' );
		if( is_null( $instance->settings ) ) {
			$instance->settings = WooCommerce_Order_Barcodes_Settings::instance( $instance );
		}
		return $instance;
	}

	// Initialise plugin
	WC_Order_Barcodes();
}

//1.3.1