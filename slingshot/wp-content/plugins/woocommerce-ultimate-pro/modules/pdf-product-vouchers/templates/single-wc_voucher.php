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
 * The template for displaying vouchers. This isn't a page template in
 * the regular sense, instead it is used for rendering the HTML for
 * generating the PDF voucher.
 *
 * @since 3.0.0
 * @version 3.0.0
 */

defined( 'ABSPATH' ) or exit;

$voucher = wc_pdf_product_vouchers_get_voucher();

wc_get_template( 'voucher/voucher.php', array(
	'voucher_fields'       => $voucher->get_fields_formatted(),
	'image_url'            => $voucher->get_image_url(),
	'additional_image_url' => $voucher->get_additional_image_url(),
) );
