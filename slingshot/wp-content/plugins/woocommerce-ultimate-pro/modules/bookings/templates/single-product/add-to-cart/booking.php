<?php
/**
 * Booking product add to cart.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-bookings/single-product/add-to-cart.php
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

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<noscript><?php _e( 'Your browser must support JavaScript in order to make a booking.', 'ultimatewoo-pro' ); ?></noscript>

<form class="cart" method="post" enctype='multipart/form-data'>

	<div id="wc-bookings-booking-form" class="wc-bookings-booking-form" style="display:none">

		<?php do_action( 'woocommerce_before_booking_form' ); ?>

		<?php $booking_form->output(); ?>

		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<div class="wc-bookings-booking-cost" style="display:none"></div>

	</div>

	<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( is_callable( array( $product, 'get_id' ) ) ? $product->get_id() : $product->id ); ?>" class="wc-booking-product-id" />

	<button type="submit" class="wc-bookings-booking-form-button single_add_to_cart_button button alt disabled" style="display:none"><?php echo $product->single_add_to_cart_text(); ?></button>

<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

</form>

<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
