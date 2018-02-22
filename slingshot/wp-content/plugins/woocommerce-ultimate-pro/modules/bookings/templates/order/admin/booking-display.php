<?php
/**
 * The template for displaying a booking summary in the admin. 
 * It will display in one place: 
 * - When reviewing a customer order.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-bookings/order/admin/booking-display.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/bookings-templates/
 * @author  Automattic
 * @version 1.10.8
 * @since   1.10.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( $booking_ids ) {
	foreach ( $booking_ids as $booking_id ) {
		$booking = new WC_Booking( $booking_id );
		?>
		<div class="wc-booking-summary">
			<strong class="wc-booking-summary-number">
				<?php printf( __( 'Booking #%s', 'ultimatewoo-pro' ), esc_html( $booking->get_id() ) ); ?>
				<span class="status-<?php echo esc_attr( $booking->get_status() ); ?>">
					<?php echo esc_html( wc_bookings_get_status_label( $booking->get_status() ) ) ?>
				</span>
			</strong>
			<?php wc_bookings_get_summary_list( $booking, true ); ?>
			<div class="wc-booking-summary-actions">
				<?php if ( in_array( $booking->get_status(), array( 'pending-confirmation' ) ) ) : ?>
					<a href="<?php echo wp_nonce_url( admin_url( 'admin-ajax.php?action=wc-booking-confirm&booking_id=' . $booking_id ), 'wc-booking-confirm' ); ?>"><?php _e( 'Confirm booking', 'ultimatewoo-pro' ); ?></a>
				<?php endif; ?>

				<?php if ( $booking_id ) : ?>
					<a href="<?php echo admin_url( 'post.php?post=' . absint( $booking_id ) . '&action=edit' ); ?>"><?php _e( 'View booking &rarr;', 'ultimatewoo-pro' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
