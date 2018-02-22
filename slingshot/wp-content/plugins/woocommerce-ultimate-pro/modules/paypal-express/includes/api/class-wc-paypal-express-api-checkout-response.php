<?php
/**
 * WooCommerce PayPal Express Payment Gateway
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce PayPal Express to newer
 * versions in the future. If you wish to customize WooCommerce PayPal Express for your
 * needs please refer to http://docs.woothemes.com/document/woocommerce-PayPal Express/
 *
 * @package   WC-PayPal Express/Gateway/API/Responses
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PayPal Express API Checkout Response Class
 *
 * Parses response string received from PayPal Express API, which is simply a URL-encoded string of parameters
 *
 * @link https://developer.paypal.com/webapps/developer/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
 * @link https://developer.paypal.com/webapps/developer/docs/classic/api/merchant/GetExpressCheckoutDetails_API_Operation_NVP/
 *
 * @since 3.0.0
 * @see SV_WC_Payment_Gateway_API_Response
 */
class WC_Paypal_Express_API_Checkout_Response extends WC_Paypal_Express_API_Response {


	/**
	 * Get the token which is returned after a successful SetExpressCheckout
	 * API call
	 *
	 * @since 3.0.0
	 * @return string|null
	 */
	public function get_token() {

		return $this->get_parameter( 'TOKEN' );
	}


	/**
	 * Get the shipping details from GetExpressCheckoutDetails response
	 * mapped to the WC shipping address format
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public function get_shipping_details() {

		$details = array();

		if ( $this->has_parameter( 'FIRSTNAME' ) ) {

			$details = array(
				'first_name' => $this->get_parameter( 'FIRSTNAME' ),
				'last_name'  => $this->get_parameter( 'LASTNAME' ),
				'company'    => $this->get_parameter( 'BUSINESS' ),
				'email'      => $this->get_parameter( 'EMAIL' ),
				'phone'      => $this->get_parameter( 'PHONENUM' ),
				'address_1'  => $this->get_parameter( 'SHIPTOSTREET' ),
				'address_2'  => $this->get_parameter( 'SHIPTOSTREET2' ),
				'city'       => $this->get_parameter( 'SHIPTOCITY' ),
				'postcode'   => $this->get_parameter( 'SHIPTOZIP' ),
				'country'    => $this->get_parameter( 'SHIPTOCOUNTRYCODE' ),
				'state'      => $this->get_state_code( $this->get_parameter( 'SHIPTOCOUNTRYCODE' ), $this->get_parameter( 'SHIPTOSTATE' ) ),
			);
		}

		/**
		 * Filters the shipping details from GetExpressCheckoutDetails response
		 * mapped to the WC shipping address format
		 *
		 * @since 3.7.0
		 * @param array $details
		 * @param \WC_Paypal_Express_API_Checkout_Response $this instance
		 */
		return apply_filters( 'wc_gateway_paypal_express_response_get_shipping_details', $details, $this );
	}


	/**
	 * Get the note text from checkout details
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_note_text() {

		return $this->get_parameter( 'PAYMENTREQUEST_0_NOTETEXT' );
	}


	/**
	 * Gets the payer ID from checkout details, a payer ID is a Unique PayPal
	 * Customer Account identification number
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_payer_id() {

		return $this->get_parameter( 'PAYERID' );
	}


	/**
	 * Get state code given a full state name and country code
	 *
	 * @since 3.0.0
	 * @param string $country_code country code sent by PayPal
	 * @param string $state state name or code sent by PayPal
	 * @return string state code
	 */
	private function get_state_code( $country_code, $state ) {

		// if not a US address, then convert state to abbreviation
		if ( $country_code !== 'US' && isset( WC()->countries->states[ $country_code ] ) ) {

			$local_states = WC()->countries->states[ $country_code ];

			if ( ! empty( $local_states ) && in_array( $state, $local_states ) ) {

				foreach ( $local_states as $key => $val ) {

					if ( $val === $state ) {
						return $key;
					}
				}
			}
		}

		return $state;
	}


}
