<?php
/**
 * Display the related orders for a subscription or order
 *
 * @var object $post The primitive post object that is being displayed (as an order or subscription)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="woocommerce_subscriptions_related_orders">
	<table>
		<thead>
			<tr>
				<th><?php esc_html_e( 'Order Number', 'ultimatewoo-pro' ); ?></th>
				<th><?php esc_html_e( 'Relationship', 'ultimatewoo-pro' ); ?></th>
				<th><?php esc_html_e( 'Date', 'ultimatewoo-pro' ); ?></th>
				<th><?php esc_html_e( 'Status', 'ultimatewoo-pro' ); ?></th>
				<th><?php echo esc_html_x( 'Total', 'table heading', 'ultimatewoo-pro' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php do_action( 'woocommerce_subscriptions_related_orders_meta_box_rows', $post ); ?>
		</tbody>
	</table>
</div>
