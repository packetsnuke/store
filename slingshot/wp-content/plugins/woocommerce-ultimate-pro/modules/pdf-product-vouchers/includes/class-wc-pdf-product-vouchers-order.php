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

/**
 * PDF Product Vouchers Order handler/helper class
 *
 * @since 1.2.0
 */
class WC_PDF_Product_Vouchers_Order {


	/**
	 * Returns any vouchers attached to an order
	 *
	 * @since 1.2.0
	 * @param \WC_Order $order the order object
	 * @return \WC_Voucher[]
	 */
	public static function get_vouchers( $order ) {

		$vouchers = array();

		$order_items = $order instanceof WC_Order ? $order->get_items() : array();

		if ( count( $order_items ) > 0 ) {

			foreach ( $order_items as $order_item_id => $item ) {

				$vouchers = array_merge( $vouchers, self::get_order_item_vouchers( $item ) );
			}
		}

		return $vouchers;
	}


	/**
	 * Returns any vouchers associated with an order item
	 *
	 * TODO refactor this method when dropping support for WC < 3.0 {IT 2017-03-07}
	 *
	 * @since 3.0.0
	 * @param \WC_Order_Item_Product|array $item order item
	 * @return \WC_Voucher[]
	 */
	public static function get_order_item_vouchers( $item ) {

		$vouchers = array();

		// a single order item may be associated with multiple vouchers since 3.0.0
		if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {

			$voucher_meta = $item->get_meta( '_voucher_id', false );

			if ( ! empty( $voucher_meta ) ) {
				foreach ( $voucher_meta as $meta ) {

					if ( $voucher = wc_pdf_product_vouchers_get_voucher( $meta->value ) ) {
						$vouchers[] = $voucher;
					}
				}
			}

		} else {

			if ( isset( $item['item_meta']['_voucher_id'] ) && ! empty( $item['item_meta']['_voucher_id'] ) ) {

				foreach ( $item['item_meta']['_voucher_id'] as $voucher_id ) {

					if ( $voucher = wc_pdf_product_vouchers_get_voucher( $voucher_id ) ) {
						$vouchers[] = $voucher;
					}
				}
			}
		}

		return $vouchers;
	}


	/**
	 * Returns true if an order has been marked as fully redeemed
	 *
	 * @since 1.2.0
	 * @param \WC_Order $order the order object
	 * @return boolean true if the order is marked as redeemed
	 */
	public static function vouchers_redeemed( WC_Order $order ) {
		return isset( $order->voucher_redeemed[0] ) && $order->voucher_redeemed[0];
	}


	/**
	 * Marks an order as having all vouchers redeemed
	 *
	 * @since 1.2.0
	 * @param \WC_Order $order the order object
	 * @param int $voucher_count the number of redeemed vouchers
	 */
	public static function mark_vouchers_redeemed( WC_Order $order, $voucher_count = 1 ) {

		$order->add_order_note( _n( 'Voucher redeemed.', 'All vouchers redeemed.', $voucher_count, 'ultimatewoo-pro' ) );

		SV_WC_Order_Compatibility::update_meta_data( $order, '_voucher_redeemed', true );

		/**
		 * Fires after all vouchers for an order have been marked redeemed
		 *
		 * @since 1.2.0
		 * @param \WC_Order $order the order object
		 */
		do_action( 'wc_pdf_product_vouchers_order_redeemed', $order );
	}

}
