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
 * @package   WC-Braintree/Gateway/API/Responses/Vault
 * @author    SkyVerge
 * @copyright Copyright: (c) 2011-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Vault Response Class
 *
 * Handles common methods for parsing vault responses (customers/payment methods)
 *
 * @since 3.0.0
 */
abstract class WC_Braintree_API_Vault_Response extends WC_Braintree_API_Response {


	/**
	 * Get the payment token data from the given payment method
	 *
	 * @since 3.0.0
	 * @param \Braintree_CreditCard|\Braintree_PayPalAccount $payment_method payment method object
	 * @return array
	 */
	protected function get_payment_token_data( $payment_method ) {

		if ( 'Braintree_CreditCard' === get_class( $payment_method ) ) {

			// credit card
			return array(
				'default'            => false,
				'type'               => WC_Braintree_Payment_Method::CREDIT_CARD_TYPE,
				'last_four'          => $payment_method->last4,
				'card_type'          => SV_WC_Payment_Gateway_Helper::card_type_from_account_number( $payment_method->bin ),
				'exp_month'          => $payment_method->expirationMonth,
				'exp_year'           => $payment_method->expirationYear,
				'billing_address_id' => ( isset( $payment_method->billingAddress ) && ! empty( $payment_method->billingAddress->id ) ) ? $payment_method->billingAddress->id : null,
			);

		} else {

			// PayPal account
			return array(
				'default'     => false,
				'type'        => WC_Braintree_Payment_Method::PAYPAL_TYPE,
				'payer_email' => $payment_method->email,
				'payer_id'    => null, // not available when added outside of a transaction
			);
		}
	}


}
