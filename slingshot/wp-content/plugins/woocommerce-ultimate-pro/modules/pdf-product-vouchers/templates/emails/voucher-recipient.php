<?php
/**
 * WooCommerce PDF Product Vouchers
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce PDF Product Vouchers to newer
 * versions in the future. If you wish to customize WooCommerce PDF Product Vouchers for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-pdf-product-vouchers/ for more information.
 *
 * @package   WC-PDF-Product-Vouchers/Emails
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/**
 * Voucher recipient html email
 *
 * @type \WC_Order $order the order object associated with this email
 * @type string $email_heading the configurable email heading
 * @type int $voucher_count the number of vouchers being attached
 * @type string $message optional customer-supplied message to display
 * @type string $recipient_name optional customer-supplied recipient name
 *
 * @version 1.2
 * @since 1.2
 */

defined( 'ABSPATH' ) or exit; ?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php printf( _n( "Hi there. You've been sent a voucher!", "Hi there. You've been sent %d vouchers!", $voucher_count, 'ultimatewoo-pro' ), $voucher_count ); ?></p>

<?php if ( $message ) : ?>
	<p>&ldquo;<?php echo esc_html( $message ); ?>&rdquo;</p>
<?php endif; ?>

<p><?php echo _n( 'You can find your voucher attached to this email', 'You can find your vouchers attached to this email', $voucher_count, 'ultimatewoo-pro' ); ?></p>

<?php do_action( 'woocommerce_email_footer' ); ?>
