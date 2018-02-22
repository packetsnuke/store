<?php
/**
 * WooCommerce Braintree Gateway
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Braintree Gateway to newer
 * versions in the future. If you wish to customize WooCommerce Braintree Gateway for your
 * needs please refer to http://docs.woothemes.com/document/braintree/
 *
 * @package   WC-Braintree/Gateway/API/Requests/Customer
 * @author    SkyVerge
 * @copyright Copyright: (c) 2011-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Customer Request class
 *
 * Handles creating customers and retrieving their payment methods
 *
 * @since 3.0.0
 */
class WC_Braintree_API_Customer_Request extends WC_Braintree_API_Vault_Request {


	/**
	 * Create a new customer and associated payment method
	 *
	 * @link https://developers.braintreepayments.com/reference/request/customer/create/php
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order
	 */
	public function create_customer( WC_Order $order ) {

		$this->order = $order;

		$this->set_callback( 'Braintree_Customer::create' );

		$this->request_data = array(
			'company'            => $order->billing_company,
			'email'              => $order->billing_email,
			'phone'              => SV_WC_Helper::str_truncate( preg_replace( '/[^\d-().]/', '', $order->billing_phone ),  14, '' ),
			'firstName'          => $order->billing_first_name,
			'lastName'           => $order->billing_last_name,
			'paymentMethodNonce' => $order->payment->nonce,
		);

		// add verification data for credit cards
		if ( 'credit_card' === $order->payment->type ) {
			$this->request_data['creditCard'] = array(
				'billingAddress' => $this->get_billing_address(),
				'cardholderName' => $order->get_formatted_billing_full_name(),
				'options'        => $this->get_credit_card_options(),
			);
		}

		// fraud data
		$this->add_device_data();
	}


	/**
	 * Get the payment methods for a given customer
	 *
	 * @link https://developers.braintreepayments.com/reference/request/customer/find/php
	 *
	 * @since 3.0.0
	 * @param string $customer_id Braintree customer ID
	 */
	public function get_payment_methods( $customer_id ) {

		$this->set_callback( 'Braintree_Customer::find' );

		$this->request_data = $customer_id;
	}


}
