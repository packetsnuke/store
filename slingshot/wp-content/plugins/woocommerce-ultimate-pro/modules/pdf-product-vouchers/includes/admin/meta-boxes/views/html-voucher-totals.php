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
 * @package   WC-PDF-Product-Vouchers/Admin/Meta-Boxes/Views
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Voucher totals admin template
 *
 * @type \WC_Voucher $voucher current voucher instance
 *
 * @since 3.0.0
 * @version 3.0.0
 */
?>

<input type="hidden" name="remaining_value" id="voucher_remaining_value" value="<?php echo esc_attr( $voucher->get_remaining_value() ); ?>" />
<input type="hidden" id="voucher_remaining_value_for_display" value="<?php echo esc_attr( $voucher->get_remaining_value_for_display() ); ?>" />
<input type="hidden" id="voucher_remaining_value_incl_tax" value="<?php echo esc_attr( $voucher->get_remaining_value_incl_tax() ); ?>" />
<input type="hidden" id="voucher_value" value="<?php echo esc_attr( $voucher->get_voucher_value() ); ?>" />
<input type="hidden" id="voucher_tax" value="<?php echo esc_attr( $voucher->get_voucher_tax() ); ?>" />

<table class="wc-voucher-totals">

	<tr>
		<td class="label original-value"><?php echo wc_help_tip( __( 'This is the original voucher value, which is the total of the product price and its quantity.', 'ultimatewoo-pro' ) ); ?> <?php esc_html_e( 'Original Value', 'ultimatewoo-pro' ); ?>:</td>
		<td width="1%"></td>
		<td class="total original-value"><?php echo wc_price( $voucher->get_voucher_value(), array( 'currency' => $voucher->get_voucher_currency() ) ); ?></td>
	</tr>

	<?php
		/**
		 * Fires after the voucher original value is rendered in voucher edit screen.
		 *
		 * @since 3.0.0
		 * @param \WC_Voucher $voucher
		 */
		do_action( 'wc_pdf_product_vouchers_admin_after_original_value', $voucher );
	?>

	<tr>
		<td class="label total-tax"><?php echo wc_help_tip( __( 'This is the total tax amount charged on the voucher.', 'ultimatewoo-pro' ) ); ?> <?php echo esc_html( WC()->countries->tax_or_vat() ); ?>:</td>
		<td width="1%"></td>
		<td class="total total-tax"><?php echo wc_price( $voucher->get_voucher_tax(), array( 'currency' => $voucher->get_voucher_currency() ) ); ?></td>
	</tr>

	<?php
		/**
		 * Fires after the voucher total tax is rendered in voucher edit screen.
		 *
		 * @since 3.1.0
		 * @param \WC_Voucher $voucher
		 */
		do_action( 'wc_pdf_product_vouchers_admin_after_total_tax', $voucher );
	?>

	<tr>
		<td class="label redeemed-total"><?php echo wc_help_tip( __( 'This is the total redeemed value.', 'ultimatewoo-pro' ) ); ?> <?php esc_html_e( 'Redeemed', 'ultimatewoo-pro' ); ?>:</td>
		<td width="1%"></td>
		<td class="total redeemed-total">-<?php echo wc_price( $voucher->get_total_redeemed_for_display(), array( 'currency' => $voucher->get_voucher_currency() ) ); ?></td>
	</tr>

	<?php
		/**
		 * Fires after the voucher redeemed value is rendered in voucher edit screen.
		 *
		 * @since 3.0.0
		 * @param \WC_Voucher $voucher
		 */
		do_action( 'wc_pdf_product_vouchers_admin_after_redeemed_value', $voucher );
	?>

	<?php if ( $voucher->has_status( 'voided' ) ) : ?>
	<tr>
		<td class="label voided-value"><?php echo wc_help_tip( __( 'This is the voided voucher value, which cannot be redeemed.', 'ultimatewoo-pro' ) ); ?> <?php esc_html_e( 'Voided', 'ultimatewoo-pro' ); ?>:</td>
		<td width="1%"></td>
		<td class="total voided-value">-<?php echo wc_price( $voucher->get_remaining_value_for_display(), array( 'currency' => $voucher->get_voucher_currency() ) ); ?></td>
	</tr>

	<?php
		/**
		 * Fires after the voucher remaining value is rendered in voucher edit screen.
		 *
		 * @since 3.0.0
		 * @param \WC_Voucher $voucher
		 */
		do_action( 'wc_pdf_product_vouchers_admin_after_voided_value', $voucher );
	?>
	<?php endif; ?>

	<tr>
		<td class="label remaining-value"><?php echo wc_help_tip( __( 'This is the remaining voucher value.', 'ultimatewoo-pro' ) ); ?> <?php esc_html_e( 'Remaining Value', 'ultimatewoo-pro' ); ?>:</td>
		<td width="1%"></td>
		<td class="total remaining-value"><?php echo wc_price( $voucher->get_remaining_value_for_display( false ), array( 'currency' => $voucher->get_voucher_currency() ) ); ?></td>
	</tr>

	<?php
		/**
		 * Fires after the voucher remaining value is rendered in voucher edit screen.
		 *
		 * @since 3.0.0
		 * @param \WC_Voucher $voucher
		 */
		do_action( 'wc_pdf_product_vouchers_admin_after_remaining_value', $voucher );
	?>

</table>

<div class="clear"></div>

