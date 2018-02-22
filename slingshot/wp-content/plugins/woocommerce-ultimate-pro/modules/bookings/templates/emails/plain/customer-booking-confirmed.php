<?php
/**
 * Customer booking confirmed email, plain text.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-bookings/emails/plain/customer-booking-confirmed.php
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/bookings-templates/
 * @author  Automattic
 * @version 1.10.0
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

echo "= " . $email_heading . " =\n\n";

if ( $booking->get_order() ) {
	echo sprintf( __( 'Hello %s', 'ultimatewoo-pro' ), ( is_callable( array( $booking->get_order(), 'get_billing_first_name' ) ) ? $booking->get_order()->get_billing_first_name() : $booking->get_order()->billing_first_name ) ) . "\n\n";
}

echo __(  'Your booking for has been confirmed. The details of your booking are shown below.', 'ultimatewoo-pro' ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo sprintf( __( 'Booked: %s', 'ultimatewoo-pro'), $booking->get_product()->get_title() ) . "\n";
echo sprintf( __( 'Booking ID: %s', 'ultimatewoo-pro'), $booking->get_id() ) . "\n";

if ( $booking->has_resources() && ( $resource = $booking->get_resource() ) ) {
	echo sprintf( __( 'Booking Type: %s', 'ultimatewoo-pro'), $resource->post_title ) . "\n";
}

echo sprintf( __( 'Booking Start Date: %s', 'ultimatewoo-pro'), $booking->get_start_date() ) . "\n";
echo sprintf( __( 'Booking End Date: %s', 'ultimatewoo-pro'), $booking->get_end_date() ) . "\n";

if ( $booking->has_persons() ) {
	foreach ( $booking->get_persons() as $id => $qty ) {
		if ( 0 === $qty ) {
			continue;
		}

		$person_type = ( 0 < $id ) ? get_the_title( $id ) : __( 'Person(s)', 'ultimatewoo-pro' );
		echo sprintf( __( '%s: %d', 'ultimatewoo-pro'), $person_type, $qty ) . "\n";
	}
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

if ( $order = $booking->get_order() ) {
	if ( 'pending' === $order->get_status() ) {
		echo sprintf( __( 'To pay for this booking please use the following link: %s', 'ultimatewoo-pro' ), $order->get_checkout_payment_url() ) . "\n\n";
	}

	do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text );

	$pre_wc_30 = version_compare( WC_VERSION, '3.0', '<' );

	if ( $pre_wc_30 ) {
		$order_date = $order->order_date;
	} else {
		$order_date = $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d H:i:s' ) : '';
	}

	echo sprintf( __( 'Order number: %s', 'ultimatewoo-pro'), $order->get_order_number() ) . "\n";
	echo sprintf( __( 'Order date: %s', 'ultimatewoo-pro'), date_i18n( wc_date_format(), strtotime( $order_date ) ) ) . "\n";

	do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text );

	echo "\n";

	switch ( $order->get_status() ) {
		case "completed" :
			echo $pre_wc_30 ? $order->email_order_items_table( array( 'show_sku' => false, 'plain_text' => true ) ) : wc_get_email_order_items( $order, array( 'show_sku' => false, 'plain_text' => true ) );
		break;
		case "processing" :
		default :
			echo $pre_wc_30 ? $order->email_order_items_table( array( 'show_sku' => true, 'plain_text' => true ) ) : wc_get_email_order_items( $order, array( 'show_sku' => true, 'plain_text' => true ) );
		break;
	}

	echo "==========\n\n";

	if ( $totals = $order->get_order_item_totals() ) {
		foreach ( $totals as $total ) {
			echo $total['label'] . "\t " . $total['value'] . "\n";
		}
	}

	echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

	do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text );
}

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
