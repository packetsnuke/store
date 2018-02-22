<?php
/**
 * Customer booking confirmed email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-bookings/emails/customer-booking-confirmed.php
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

do_action( 'woocommerce_email_header', $email_heading );
?>

<?php if ( $booking->get_order() ) : ?>
	<p><?php printf( __( 'Hello %s', 'ultimatewoo-pro' ), ( is_callable( array( $booking->get_order(), 'get_billing_first_name' ) ) ? $booking->get_order()->get_billing_first_name() : $booking->get_order()->billing_first_name ) ); ?></p>
<?php endif; ?>

<p><?php _e( 'Your booking has been confirmed. The details of your booking are shown below.', 'ultimatewoo-pro' ); ?></p>

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

<?php if ( $order = $booking->get_order() ) : ?>

	<?php if ( 'pending' === $order->get_status() ) : ?>
		<p><?php printf( __( 'To pay for this booking please use the following link: %s', 'ultimatewoo-pro' ), '<a href="' . esc_url( $order->get_checkout_payment_url() ) . '">' . __( 'Pay for booking', 'ultimatewoo-pro' ) . '</a>' ); ?></p>
	<?php endif; ?>

	<?php do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text ); ?>

	<h2><?php

$pre_wc_30 = version_compare( WC_VERSION, '3.0', '<' );
if ( $pre_wc_30 ) {
	$order_date = $order->order_date;
} else {
	$order_date = $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d H:i:s' ) : '';
}

echo __( 'Order', 'ultimatewoo-pro' ) . ': ' . $order->get_order_number(); ?> (<?php printf( '<time datetime="%s">%s</time>', date_i18n( 'c', strtotime( $order_date ) ), date_i18n( wc_date_format(), strtotime( $order_date ) ) ); ?>)</h2>

	<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
		<thead>
			<tr>
				<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product', 'ultimatewoo-pro' ); ?></th>
				<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Quantity', 'ultimatewoo-pro' ); ?></th>
				<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Price', 'ultimatewoo-pro' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				switch ( $order->get_status() ) {

					case "completed" :
						echo $pre_wc_30 ? $order->email_order_items_table( array( 'show_sku' => false ) ) : wc_get_email_order_items( $order, array( 'show_sku' => false ) );
						break;

					case "processing" :
					default :
						echo $pre_wc_30 ? $order->email_order_items_table( array( 'show_sku' => true ) ) : wc_get_email_order_items( $order, array( 'show_sku' => true ) );
						break;
				}
			?>
		</tbody>
		<tfoot>
			<?php
				if ( $totals = $order->get_order_item_totals() ) {
					$i = 0;
					foreach ( $totals as $total ) {
						$i++;
						?><tr>
							<th scope="row" colspan="2" style="text-align:left; border: 1px solid #eee; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['label']; ?></th>
							<td style="text-align:left; border: 1px solid #eee; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>"><?php echo $total['value']; ?></td>
						</tr><?php
					}
				}
			?>
		</tfoot>
	</table>

	<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text ); ?>

	<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text ); ?>

<?php endif; ?>

<?php do_action( 'woocommerce_email_footer' ); ?>
