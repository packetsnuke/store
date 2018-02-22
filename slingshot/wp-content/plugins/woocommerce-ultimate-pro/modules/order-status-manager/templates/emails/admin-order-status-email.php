<?php
/**
 * WooCommerce Order Status Manager
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Order Status Manager to newer
 * versions in the future. If you wish to customize WooCommerce Order Status Manager for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-order-status-manager/ for more information.
 *
 * @package     WC-Order-Status-Manager/Templates
 * @author      SkyVerge
 * @copyright   Copyright (c) 2015-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Default admin order status email template.
 *
 * @type string $email_heading The email heading.
 * @type string $email_body_text The email body.
 * @type \WC_Order $order The order object.
 * @type bool $sent_to_admin Whether email is sent to admin.
 * @type bool $plain_text Whether email is plain text.
 * @type bool $show_download_links Whether to show download links.
 * @type bool $show_purchase_note Whether to show purchase note.
 *
 * @since 1.0.0
 * @version 1.7.0
 */
?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<?php if ( $email_body_text ) : ?>
	<div id="body_text"><?php echo $email_body_text; ?></div>
<?php endif; ?>

<?php do_action( 'woocommerce_email_before_order_table', $order, true, false ); ?>

<h2>
	<a href="<?php echo admin_url( 'post.php?post=' . SV_WC_Order_Compatibility::get_prop( $order, 'id' ) . '&action=edit' ); ?>">
		<?php /* translators: Placeholders: %s - order number */
		printf( __( 'Order: %s', 'ultimatewoo-pro' ), $order->get_order_number() ); ?>
		<?php $order_timestamp = SV_WC_Order_Compatibility::get_date_created( $order )->getTimestamp(); ?>
	</a> (<?php printf( '<time datetime="%s">%s</time>', date_i18n( 'c', $order_timestamp ), date_i18n( wc_date_format(), $order_timestamp ) ); ?>)
</h2>

<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
	<thead>
		<tr>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Product', 'ultimatewoo-pro' ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Quantity', 'ultimatewoo-pro' ); ?></th>
			<th scope="col" style="text-align:left; border: 1px solid #eee;"><?php esc_html_e( 'Price', 'ultimatewoo-pro' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php

		$email_order_items = array(
			'show_sku' => true,
		);

		echo SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ? wc_get_email_order_items( $order, $email_order_items ) : $order->email_order_items_table( $email_order_items );

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

<?php do_action( 'woocommerce_email_after_order_table', $order, true, false ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, true, false ); ?>

<h2><?php esc_html_e( 'Customer details', 'ultimatewoo-pro' ); ?></h2>

<?php if ( $billing_email = SV_WC_Order_Compatibility::get_prop( $order, 'billing_email' ) ) : ?>
	<p><strong><?php esc_html_e( 'Email:', 'ultimatewoo-pro' ); ?></strong> <?php echo $billing_email; ?></p>
<?php endif; ?>
<?php if ( $billing_phone = SV_WC_Order_Compatibility::get_prop( $order, 'billing_phone' ) ) : ?>
	<p><strong><?php esc_html_e( 'Tel:', 'ultimatewoo-pro' ); ?></strong> <?php echo $billing_phone; ?></p>
<?php endif; ?>

<?php wc_get_template( 'emails/email-addresses.php', array( 'order' => $order ) ); ?>

<?php do_action( 'woocommerce_email_footer' ); ?>
