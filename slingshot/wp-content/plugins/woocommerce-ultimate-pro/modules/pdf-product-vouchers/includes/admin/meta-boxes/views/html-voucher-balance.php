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
 * Voucher balance meta box admin template
 *
 * @type \WC_Voucher $voucher current voucher instance
 *
 * @since 3.0.0
 * @version 3.0.0
 */
?>

<div class="wc-voucher-balance-wrapper wc-voucher-items-editable">
	<table cellpadding="0" cellspacing="0" class="wc-voucher-balance">

		<thead>
			<tr>
				<th class="item" colspan="2"><?php esc_html_e( 'Item', 'ultimatewoo-pro' ); ?></th>
				<th class="value"><?php esc_html_e( 'Value', 'ultimatewoo-pro' ); ?></th>
				<th class="quantity"><?php esc_html_e( 'Qty', 'ultimatewoo-pro' ); ?></th>
				<th class="total"><?php esc_html_e( 'Total', 'ultimatewoo-pro' ); ?></th>
				<th class="tax"><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
				<th class="wc-voucher-item-actions" width="1%">&nbsp;</th>
			</tr>
		</thead>

		<?php include( 'html-voucher-product.php'); ?>

		<?php include( 'html-voucher-redemptions.php'); ?>

		<?php include( 'html-voucher-voided.php'); ?>

	</table>
</div>

<div class="wc-voucher-data-row wc-voucher-totals-wrapper">
	<?php include( 'html-voucher-totals.php'); ?>
</div>

<?php if ( $voucher->is_editable() ) : ?>
<div class="wc-voucher-data-row wc-voucher-redeem-wrapper wc-voucher-data-row-toggle" style="display: none;">
	<?php include( 'html-voucher-redeem.php'); ?>
</div>
<div class="wc-voucher-data-row wc-voucher-void-wrapper wc-voucher-data-row-toggle" style="display: none;">
	<?php include( 'html-voucher-void.php'); ?>
</div>
<?php endif; ?>

<div class="wc-voucher-data-row wc-voucher-balance-actions wc-voucher-data-row-toggle">
	<p class="actions">

		<?php if ( $voucher->is_editable() ) : ?>
			<button type="button" class="button js-void-action"><?php esc_html_e( 'Void remaining value', 'ultimatewoo-pro' ); ?></button>
			<button type="button" class="button js-calculate-tax-action"><?php esc_html_e( 'Calculate Taxes', 'ultimatewoo-pro' ); ?></button>
			<button type="button" class="button button-primary js-redeem-action"><?php esc_html_e( 'Redeem', 'ultimatewoo-pro' ); ?></button>
			<span class="description js-customer-changed-notice" style="display:none"><?php echo wc_help_tip( __( 'To calculate taxes or redeem this voucher, please save it first.', 'ultimatewoo-pro' ) ); ?></span>
		<?php elseif ( $voucher->has_status('redeemed') )  : ?>
			<span class="description"><?php echo wc_help_tip( __( 'To edit this voucher change the status back to "Pending"', 'ultimatewoo-pro' ) ); ?> <?php esc_html_e( 'This voucher has been fully redeemed.', 'ultimatewoo-pro' ); ?></span>
		<?php elseif ( $voucher->has_status('voided') )  : ?>
			<button type="button" class="button restore-action js-restore-action"><?php esc_html_e( 'Restore voided balance', 'ultimatewoo-pro' ); ?></button>
			<span class="description"><?php echo wc_help_tip( __( 'To edit this voucher change the status back to "Pending"', 'ultimatewoo-pro' ) ); ?> <?php esc_html_e( 'This voucher has been voided and can no longer be redeemed.', 'ultimatewoo-pro' ); ?></span>
		<?php endif; ?>

		<?php
			/**
			 * Triggered after rendering the voucher balance action buttons
			 *
			 * @since 3.0.0
			 * @param \WC_Voucher $voucher
			 */
			do_action( 'wc_pdf_product_vouchers_voucher_balance_action_buttons', $voucher );
		?>
	</p>
</div>

<div class="wc-voucher-data-row wc-voucher-edit-redemption-actions wc-voucher-data-row-toggle" style="display:none;">
	<p class="actions">
		<button type="button" class="button js-cancel-action"><?php esc_html_e( 'Cancel', 'ultimatewoo-pro' ); ?></button>
		<button type="button" class="button button-primary js-save-redemptions-action"><?php esc_html_e( 'Save', 'ultimatewoo-pro' ); ?></button>
		<?php
			/**
			 * Triggered after rendering the action buttons when editing a redemption
			 *
			 * @since 3.0.0
			 * @param \WC_Voucher $voucher
			 */
			do_action( 'wc_pdf_product_vouchers_voucher_edit_redemption_action_buttons', $voucher );
		?>
	</p>
</div>

<div class="wc-voucher-data-row wc-voucher-redeem-actions wc-voucher-data-row-toggle" style="display:none;">
	<p class="actions">
		<button type="button" class="button js-cancel-action"><?php esc_html_e( 'Cancel', 'ultimatewoo-pro' ); ?></button>
		<button type="button" class="button button-primary js-redeem-voucher-action"><?php esc_html_e( 'Redeem', 'ultimatewoo-pro' ); ?></button>
		<?php
			/**
			 * Triggered after rendering the action buttons for adding a voucher redemption
			 *
			 * @since 3.0.0
			 * @param \WC_Voucher $voucher
			 */
			do_action( 'wc_pdf_product_vouchers_voucher_redeem_action_buttons', $voucher );
		?>
	</p>
</div>

<div class="wc-voucher-data-row wc-voucher-void-actions wc-voucher-data-row-toggle" style="display:none;">
	<p class="actions">
		<button type="button" class="button js-cancel-action"><?php esc_html_e( 'Cancel', 'ultimatewoo-pro' ); ?></button>
		<button type="button" class="button button-primary js-void-voucher-action"><?php esc_html_e( 'Void', 'ultimatewoo-pro' ); ?></button>
		<?php
			/**
			 * Triggered after rendering the action buttons for voiding a voucher
			 *
			 * @since 3.0.0
			 * @param \WC_Voucher $voucher
			 */
			do_action( 'wc_pdf_product_vouchers_voucher_void_action_buttons', $voucher );
		?>
	</p>
</div>
