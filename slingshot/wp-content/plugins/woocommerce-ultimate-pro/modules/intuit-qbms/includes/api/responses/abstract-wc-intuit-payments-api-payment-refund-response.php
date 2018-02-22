<?php
/**
 * WooCommerce Intuit Payments
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Intuit QBMS to newer
 * versions in the future. If you wish to customize WooCommerce Intuit QBMS for your
 * needs please refer to http://docs.woothemes.com/document/intuit-qbms/
 *
 * @package   WC-Intuit-Payments/API
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * The Payments API payment refund response class.
 *
 * @since 2.0.0
 */
abstract class WC_Intuit_Payments_API_Payment_Refund_Response extends WC_Intuit_Payments_API_Payment_Response {


	/**
	 * Determines if the transaction ended as a void or refund.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_void() {

		return 'VOID' === $this->get_transaction_type();
	}


	/**
	 * Gets the transaction type.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_transaction_type() {

		return $this->type;
	}


}
