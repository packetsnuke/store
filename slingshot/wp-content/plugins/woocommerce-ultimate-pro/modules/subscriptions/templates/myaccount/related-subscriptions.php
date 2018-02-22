<?php
/**
 * Related Subscriptions section beneath order details table
 *
 * @author   Prospress
 * @category WooCommerce Subscriptions/Templates
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<header>
	<h2><?php esc_html_e( 'Related Subscriptions', 'ultimatewoo-pro' ); ?></h2>
</header>
<table class="shop_table shop_table_responsive my_account_orders">
	<thead>
		<tr>
			<th class="order-number"><span class="nobr"><?php esc_html_e( 'Subscription', 'ultimatewoo-pro' ); ?></span></th>
			<th class="order-date"><span class="nobr"><?php esc_html_e( 'Status', 'ultimatewoo-pro' ); ?></span></th>
			<th class="order-status"><span class="nobr"><?php echo esc_html_x( 'Next Payment', 'table heading', 'ultimatewoo-pro' ); ?></span></th>
			<th class="order-total"><span class="nobr"><?php echo esc_html_x( 'Total', 'table heading', 'ultimatewoo-pro' ); ?></span></th>
			<th class="order-actions">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $subscriptions as $subscription_id => $subscription ) : ?>
			<tr class="order">
				<td class="subscription-id order-number" data-title="<?php esc_attr_e( 'ID', 'ultimatewoo-pro' ); ?>">
					<a href="<?php echo esc_url( $subscription->get_view_order_url() ); ?>">
						<?php echo sprintf( esc_html_x( '#%s', 'hash before order number', 'ultimatewoo-pro' ), esc_html( $subscription->get_order_number() ) ); ?>
					</a>
				</td>
				<td class="subscription-status order-status" style="white-space:nowrap;" data-title="<?php esc_attr_e( 'Status', 'ultimatewoo-pro' ); ?>">
					<?php echo esc_attr( wcs_get_subscription_status_name( $subscription->get_status() ) ); ?>
				</td>
				<td class="subscription-next-payment order-date" data-title="<?php echo esc_attr_x( 'Next Payment', 'table heading', 'ultimatewoo-pro' ); ?>">
					<?php echo esc_attr( $subscription->get_date_to_display( 'next_payment' ) ); ?>
				</td>
				<td class="subscription-total order-total" data-title="<?php echo esc_attr_x( 'Total', 'Used in data attribute. Escaped', 'ultimatewoo-pro' ); ?>">
					<?php echo wp_kses_post( $subscription->get_formatted_order_total() ); ?>
				</td>
				<td class="subscription-actions order-actions">
					<a href="<?php echo esc_url( $subscription->get_view_order_url() ) ?>" class="button view"><?php echo esc_html_x( 'View', 'view a subscription', 'ultimatewoo-pro' ); ?></a>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php do_action( 'woocommerce_subscription_after_related_subscriptions_table', $subscriptions, $order_id ); ?>
