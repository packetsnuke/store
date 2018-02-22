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

echo $email_heading . "\n\n";

if ( $email_body_text ) {
	echo "\n\n";
	echo $email_body_text . "\n\n";
}

echo "****************************************************\n\n";

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text );

/* translators: Placeholders: %s - order number */
echo sprintf( __( 'Order number: %s', 'ultimatewoo-pro' ), $order->get_order_number() ) . "\n";

/* translators: Placeholders: %s - order link */
echo sprintf( __( 'Order link: %s', 'ultimatewoo-pro' ), esc_url( admin_url( 'post.php?post=' . SV_WC_Order_Compatibility::get_prop( $order, 'id' ) . '&action=edit' ) ) ) . "\n";

$order_timestamp = SV_WC_Order_Compatibility::get_date_created( $order )->getTimestamp();

/* translators: Placeholders: %s - order date */
echo sprintf( __( 'Order date: %s', 'ultimatewoo-pro' ), date_i18n( __( 'jS F Y', 'ultimatewoo-pro' ), $order_timestamp ) ) . "\n";

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text );

echo "\n";

$email_order_items = array(
	'show_sku'   => true,
	'plain_text' => true,
);

echo SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ? wc_get_email_order_items( $order, $email_order_items ) : $order->email_order_items_table( $email_order_items );

echo "----------\n\n";

if ( $totals = $order->get_order_item_totals() ) {
	foreach ( $totals as $total ) {
		echo $total['label'] . "\t " . $total['value'] . "\n";
	}
}

echo "\n****************************************************\n\n";

do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text );

echo __( 'Customer details', 'ultimatewoo-pro' ) . "\n";

if ( $billing_email = SV_WC_Order_Compatibility::get_prop( $order, 'billing_email' ) ) {
	echo __( 'Email:', 'ultimatewoo-pro' ); echo $billing_email . "\n";
}

if ( $billing_phone = SV_WC_Order_Compatibility::get_prop( $order, 'billing_phone' ) ) {
	echo __( 'Tel:', 'ultimatewoo-pro' ); ?> <?php echo $billing_phone . "\n";
}

wc_get_template( 'emails/plain/email-addresses.php', array( 'order' => $order ) );

echo "\n****************************************************\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
