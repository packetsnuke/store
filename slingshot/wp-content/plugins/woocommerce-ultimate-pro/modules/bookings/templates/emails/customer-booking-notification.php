<?php
/**
 * Customer booking notification email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-bookings/emails/customer-booking-notification.php
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/bookings-templates/
 * @author  Automattic
 * @version 1.4.10
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

do_action( 'woocommerce_email_header', $email_heading );
?>

<?php echo wpautop( wptexturize( $notification_message ) ); ?>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<tbody>
		<tr>
			<th scope="row" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Booked Product', 'ultimatewoo-pro' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $booking->get_product()->get_title(); ?></td>
		</tr>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php _e( 'Booking ID', 'ultimatewoo-pro' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $booking->get_id(); ?></td>
		</tr>
		<?php if ( $booking->has_resources() && ( $resource = $booking->get_resource() ) ) : ?>
			<tr>
				<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php _e( 'Booking Type', 'ultimatewoo-pro' ); ?></th>
				<td style="text-align:left; border: 1px solid #eee;"><?php echo $resource->post_title; ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php _e( 'Booking Start Date', 'ultimatewoo-pro' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $booking->get_start_date(); ?></td>
		</tr>
		<tr>
			<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php _e( 'Booking End Date', 'ultimatewoo-pro' ); ?></th>
			<td style="text-align:left; border: 1px solid #eee;"><?php echo $booking->get_end_date(); ?></td>
		</tr>
		<?php if ( $booking->has_persons() ) : ?>
			<?php
				foreach ( $booking->get_persons() as $id => $qty ) :
					if ( 0 === $qty ) {
						continue;
					}

					$person_type = ( 0 < $id ) ? get_the_title( $id ) : __( 'Person(s)', 'ultimatewoo-pro' );
			?>
				<tr>
					<th style="text-align:left; border: 1px solid #eee;" scope="row"><?php echo $person_type; ?></th>
					<td style="text-align:left; border: 1px solid #eee;"><?php echo $qty; ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>

<?php do_action( 'woocommerce_email_footer' ); ?>