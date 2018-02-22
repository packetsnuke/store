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
 * The credit card payment class.
 *
 * @since 2.0.0
 */
class WC_Intuit_Payments_API_Credit_Card_Request extends WC_Intuit_Payments_API_Payment_Request {


	/**
	 * Constructs the class.
	 *
	 * @since 2.0.0
	 * @param \WC_Order $order the order object
	 */
	public function __construct( WC_Order $order ) {

		$this->set_type( 'charges' );

		$this->set_tokenized_method_key( 'cardOnFile' );

		parent::__construct( $order );
	}


	/**
	 * Sets the data for an authorization payment.
	 *
	 * @since 2.0.0
	 */
	public function set_authorization_data() {

		$this->set_payment_data();

		$this->data['capture'] = false;
	}


	/**
	 * Sets the data for a charge payment.
	 *
	 * @since 2.0.0
	 */
	public function set_charge_data() {

		$this->set_payment_data();

		$this->data['capture'] = true;
	}


	/**
	 * Sets the data for a credit card transaction.
	 *
	 * @since 2.0.0
	 * @see WC_Intuit_Payments_API_Payment_Request::set_payment_data()
	 */
	protected function set_payment_data() {

		parent::set_payment_data();

		$this->data['currency'] = SV_WC_Helper::str_truncate( SV_WC_Order_Compatibility::get_prop( $this->get_order(), 'currency', 'view' ), 3, '' );
	}


	/**
	 * Sets the data to capture an authorized payment.
	 *
	 * @since 2.0.0
	 */
	public function set_capture_data() {

		$this->set_payment_id( $this->get_order()->capture->trans_id );

		$this->path .= '/capture';

		$this->data = array(
			'amount' => $this->get_order()->capture->amount,
		);
	}


}
