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
 * @package   WC-PDF-Product-Vouchers/Admin/Meta-Boxes
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * PDF Product Vouchers Voucher Balance Meta Box
 *
 * @since 3.0.0
 */
class WC_PDF_Product_Vouchers_Meta_Box_Voucher_Balance {


	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ), 30 );
		add_action( 'wc_pdf_product_vouchers_process_voucher_meta', array( $this, 'save' ), 10, 2 );
		add_action( 'admin_footer', array( $this, 'render_modal_template' ) );
	}


	/**
	 * Adds the meta box
	 *
	 * @since 3.0.0
	 */
	public function add_meta_box() {
		add_meta_box( 'wc-pdf-product-vouchers-voucher-balance', __( 'Balance', 'ultimatewoo-pro' ), array( $this, 'output' ), 'wc_voucher', 'normal' );
	}


	/**
	 * Outputs meta box contents
	 *
	 * @since 3.0.0
	 */
	public function output() {
		global $post, $voucher;

		include( wc_pdf_product_vouchers()->get_plugin_path() . '/includes/admin/meta-boxes/views/html-voucher-balance.php' );
	}


	/**
	 * Renders voucher modal template in footer
	 *
	 * @since 3.0.0
	 */
	public function render_modal_template() {
		include( wc_pdf_product_vouchers()->get_plugin_path() . '/includes/admin/meta-boxes/views/html-voucher-edit-product-modal.php' );
	}


	/**
	 * Processs and saves meta box data
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 * @param int $post_id post identifier
	 * @param \WP_Post $post the post object
	 */
	public function save( $post_id, WP_Post $post ) {

		$product_id    = isset( $_POST['_product_id'] ) ? (int) $_POST['_product_id'] : null;
		$product_price = isset( $_POST['_product_price'] ) ? $_POST['_product_price'] : null;

		if ( $product_price ) {
			$product_price = wc_format_decimal( $product_price );
		}

		// Update product data
		update_post_meta( $post_id, '_product_id', $product_id );
		update_post_meta( $post_id, '_product_price', $product_price );
	}
}
