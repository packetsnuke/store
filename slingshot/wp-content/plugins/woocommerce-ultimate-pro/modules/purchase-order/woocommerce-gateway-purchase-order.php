<?php
/*
	Copyright: © 2009-2017 WooCommerce.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Initialise the payment gateway.
 * @since  1.0.0
 * @return void
 */
function woocommerce_gateway_purchase_order_init () {
	// If we don't have access to the WC_Payment_Gateway class, get out.
	if( ! class_exists( 'WC_Payment_Gateway' ) ) return;
	add_filter('woocommerce_payment_gateways', 'woocommerce_gateway_purchase_order_register_gateway' );

	// Localisation
	load_plugin_textdomain( 'woocommerce-gateway-purchase-order', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	// Additional admin screen logic.
	require_once( 'includes/class-woocommerce-gateway-purchase-order-admin.php' );
	Woocommerce_Gateway_Purchase_Order_Admin();
} // End woocommerce_gateway_purchase_order_init()
add_action( 'plugins_loaded', 'woocommerce_gateway_purchase_order_init', 0 );

/**
 * Register this payment gateway within WooCommerce.
 * @access public
 * @since  1.0.0
 * @param  array $methods The array of registered payment gateways.
 * @return array          The modified array of registered payment gateways.
 */
function woocommerce_gateway_purchase_order_register_gateway ( $methods ) {
	require_once( 'includes/class-woocommerce-gateway-purchase-order.php' );

	$methods[] = 'Woocommerce_Gateway_Purchase_Order';
	return $methods;
} // End woocommerce_gateway_purchase_order_register_gateway()

//1.1.5
?>