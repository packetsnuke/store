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
 * Voucher edit product admin modal
 *
 * @since 3.0.0
 * @version 3.0.0
 */
?>

<script type="text/template" id="tmpl-wc-voucher-modal-edit-product">
	<div class="wc-backbone-modal wc-voucher-edit-modal">
		<div class="wc-backbone-modal-content">
			<section class="wc-backbone-modal-main" role="main">
				<header class="wc-backbone-modal-header">
					<h1><?php esc_html_e( 'Edit Voucher Product', 'ultimatewoo-pro' ); ?></h1>
					<button class="modal-close modal-close-link dashicons dashicons-no-alt">
						<span class="screen-reader-text">Close modal panel</span>
					</button>
				</header>
				<article>
					<form action="" method="post">

						<p>
							<label for="voucher_product"><?php esc_html_e( 'Product' ); ?></label>

						<?php if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) : ?>

							<select
								id="voucher_product_id"
								name="product_id"
								class="wc-product-search"
								style="width: 100%;"
								data-exclude="wc_pdf_product_vouchers_non_voucher_products"
								data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'ultimatewoo-pro' ); ?>">
								<# if ( data.product_id ) { #>
									<option value="{{{data.product_id}}}" selected>#{{{data.product_id}}} &ndash; {{{data.product_title}}}</option>
								<# } #>
							</select>

						<?php else : ?>

							<input
								type="hidden"
								id="voucher_product_id"
								name="product_id"
								class="wc-product-search"
								style="width: 100%;"
								data-exclude="wc_pdf_product_vouchers_non_voucher_products"
								data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'ultimatewoo-pro' ); ?>"
								data-selected="#{{{data.product_id}}} &ndash; {{{data.product_title}}}"
								value="{{{data.product_id}}}"
							/>

						<?php endif; ?>
						</p>

						<p>
							<label for="modal_voucher_product_price"><?php esc_html_e( 'Price' ); ?> <?php if ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) ) : ?><small class="tax_label"><?php echo WC()->countries->ex_tax_or_vat(); ?></small><?php endif; ?></label>
							<input type="text" id="modal_voucher_product_price" name="product_price" class="wc_input_price" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" style="width: 100%;" value="{{{data.product_price}}}" />
						</p>
					</form>
				</article>
				<footer>
					<div class="inner">
						<button class="button button-large modal-close"><?php esc_html_e( 'Cancel', 'ultimatewoo-pro' ); ?></button>
						<button id="btn-ok" class="button button-primary button-large"><?php esc_html_e( 'Update', 'ultimatewoo-pro' ); ?></button>
					</div>
				</footer>
			</section>
		</div>
	</div>
	<div class="wc-backbone-modal-backdrop modal-close"></div>
</script>
