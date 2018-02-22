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
 * @package   WC-PDF-Product-Vouchers/Classes
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

// load helper functions
require_once( wc_pdf_product_vouchers()->get_plugin_path() . '/includes/functions/wc-pdf-product-vouchers-functions-dates.php' );
require_once( wc_pdf_product_vouchers()->get_plugin_path() . '/includes/functions/wc-pdf-product-vouchers-functions-misc.php' );
require_once( wc_pdf_product_vouchers()->get_plugin_path() . '/includes/functions/wc-pdf-product-vouchers-functions-vouchers.php' );

// load frontend-only functions
if ( ! is_admin() ) {
	require_once( wc_pdf_product_vouchers()->get_plugin_path() . '/includes/functions/wc-pdf-product-vouchers-functions-template.php' );
}
