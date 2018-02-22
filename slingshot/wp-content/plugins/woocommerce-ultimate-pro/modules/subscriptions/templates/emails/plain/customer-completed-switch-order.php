<?php
/**
 * Customer completed subscription change email (plain text)
 *
 * @author  Brent Shepherd
 * @package WooCommerce_Subscriptions/Templates/Emails/Plain
 * @version 1.4.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

echo $email_heading . "\n\n";

// translators: placeholder is the name of the site
echo sprintf( __( 'Hi there. You have successfully changed your subscription items on %s. Your new order and subscription details are shown below for your reference:', 'ultimatewoo-pro' ), get_option( 'blogname' ) );

echo "\n\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";

do_action( 'woocommerce_subscriptions_email_order_details', $order, $sent_to_admin, $plain_text, $email );

// translators: placeholder is order's view url
echo "\n" . sprintf( __( 'View your order: %s', 'ultimatewoo-pro' ), $order->get_view_order_url() );

echo "\n\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

foreach ( $subscriptions as $subscription ) {

	do_action( 'woocommerce_subscriptions_email_order_details', $subscription, $sent_to_admin, $plain_text, $email );

	// translators: placeholder is subscription's view url
	echo "\n" . sprintf( __( 'View your subscription: %s', 'ultimatewoo-pro' ), $subscription->get_view_order_url() );
}
echo "\n\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";

do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
