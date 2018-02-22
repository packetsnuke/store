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
 * @package   WC-PDF-Product-Vouchers/Templates
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/**
 * The template for displaying voucher templates. This isn't a page template in
 * the regular sense, instead it displays a preview of the voucher template.
 * It is used with WP Customizer to customize the voucher template as needed.
 *
 * This template provides a basic, minimal HTML frame to work with WP Customizer.
 *
 * @since 3.0.0
 * @version 3.0.0
 */

defined( 'ABSPATH' ) or exit;

$voucher_template = wc_pdf_product_vouchers_get_voucher_template();

wc_get_template( 'voucher/voucher.php', array(
	'voucher_fields'       => $voucher_template->get_sample_fields(),
	'image_url'            => $voucher_template->get_image_url(),
	'additional_image_url' => $voucher_template->get_additional_image_url(),
) );
