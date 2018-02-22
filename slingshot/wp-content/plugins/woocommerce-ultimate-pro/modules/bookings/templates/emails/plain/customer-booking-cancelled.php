<?php
/**
 * Customer booking cancelled email, plain text.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-bookings/emails/plain/customer-booking-cancelled.php
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

echo __(  'We are sorry to say that your booking could not be confirmed and has been cancelled. The details of the cancelled booking can be found below.', 'ultimatewoo-pro' ) . "\n\n";

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

echo __( 'Please contact us if you have any questions or concerns.', 'ultimatewoo-pro' ) . "\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
