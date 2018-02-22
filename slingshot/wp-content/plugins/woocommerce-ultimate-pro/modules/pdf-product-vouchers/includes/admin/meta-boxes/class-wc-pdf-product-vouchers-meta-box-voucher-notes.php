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
 * PDF Product Vouchers Voucher Data Meta Box
 *
 * @since 3.0.0
 */
class WC_PDF_Product_Vouchers_Meta_Box_Voucher_Notes {


	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	}


	/**
	 * Adds the meta box
	 *
	 * @since 3.0.0
	 */
	public function add_meta_box() {
		add_meta_box( 'wc-pdf-product-vouchers-voucher-notes', __( 'Voucher Notes', 'ultimatewoo-pro' ), array( $this, 'output' ), 'wc_voucher', 'side' );
	}


	/**
	 * Outputs meta box contents
	 *
	 * @since 3.0.0
	 */
	public function output() {
		global $post, $voucher;

		include( wc_pdf_product_vouchers()->get_plugin_path() . '/includes/admin/meta-boxes/views/html-voucher-notes.php' );

		?>
		<div class="add-note">
			<h4><?php esc_html_e( 'Add note', 'ultimatewoo-pro' ); ?> <?php echo wc_help_tip( __( 'Add a note for your reference.', 'ultimatewoo-pro' ) ); ?></h4>
			<p>
				<textarea type="text" name="order_note" id="voucher-note" class="input-text" cols="20" rows="5"></textarea>
			</p>
			<p><a href="#" class="js-add-note button"><?php esc_html_e( 'Add Note', 'ultimatewoo-pro' ); ?></a></p>
		</div>
		<?php
	}

}
