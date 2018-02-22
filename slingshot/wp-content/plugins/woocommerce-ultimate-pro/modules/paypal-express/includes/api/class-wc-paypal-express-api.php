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
 * needs please refer to http://docs.woothemes.com/document/paypal-express-checkout
 *
 * @package   WC-Gateway-PayPal-Express/API
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PayPal Express API Class
 *
 * Handles sending/receiving/parsing of PayPal Express API data, this is the main API
 * class responsible for communication with the PayPal Express NVP API
 *
 * @link https://developer.paypal.com/docs/classic/express-checkout/integration-guide/ECGettingStarted/
 *
 * @since 3.0.0
 */
class WC_Paypal_Express_API extends SV_WC_API_Base implements SV_WC_Payment_Gateway_API {


	/** the production endpoint */
	const PRODUCTION_ENDPOINT = 'https://api-3t.paypal.com/nvp';

	/** the sandbox endpoint */
	const SANDBOX_ENDPOINT = 'https://api-3t.sandbox.paypal.com/nvp';

	/** NVP API version */
	const VERSION = '115';

	/** @var \WC_Order order associated with the request */
	protected $order;


	/**
	 * Constructor - setup request object and set endpoint
	 *
	 * @since 3.0.0
	 * @param string $gateway_id gateway ID for this request
	 * @param string $api_environment the API environment
	 * @param string $api_username the API username
	 * @param string $api_password the API password
	 * @param string $api_signature the API signature
	 * @return \WC_Paypal_Express_API
	 */
	public function __construct( $gateway_id, $api_environment, $api_username, $api_password, $api_signature ) {

		// tie API to gateway
		$this->gateway_id = $gateway_id;

		// request URI does not vary per-request
		$this->request_uri = ( 'production' === $api_environment ) ? self::PRODUCTION_ENDPOINT : self::SANDBOX_ENDPOINT;

		// PayPal requires HTTP 1.1
		$this->request_http_version = '1.1';

		$this->api_username  = $api_username;
		$this->api_password  = $api_password;
		$this->api_signature = $api_signature;
	}


	/**
	 * Set Express Checkout
	 *
	 * @since 3.0.0
	 * @param array $args {
	 *
	 *     @type string $return_url                URL to which the buyer's browser is returned after choosing to pay with PayPal.
	 *     @type string $cancel_url                URL to which the buyer is returned if the buyer does not approve the use of PayPal to pay.
	 *     @type string $page_style                Name of the Custom Payment Page Style for payment pages associated with this button or link.
	 *     @type bool   $use_bml                   Whether to use Bill Me Later or not, defaults to false.
	 *     @type bool   $paypal_account_optional   Whether using/having a PayPal account is optional or not.
	 *     @type string $landing_page              PayPal landing page to use, defaults to `billing`.
	 *                                             Requires $paypal_account_optional to be true to have any effect.
	 * }
	 * @throws Exception network timeouts, etc
	 * @return \WC_PayPal_Express_API_Checkout_Response response object
	 */
	public function set_express_checkout( $args ) {

		$request = $this->get_new_request();

		$request->set_express_checkout( $args );

		$this->set_response_handler( 'WC_PayPal_Express_API_Checkout_Response' );

		return $this->perform_request( $request );
	}


	/**
	 * Get Express Checkout Details
	 *
	 * @since 3.0.0
	 * @param string $token Token from set_express_checkout response
	 * @return \WC_PayPal_Express_API_Checkout_Response response object
	 * @throws Exception network timeouts, etc
	 */
	public function get_express_checkout_details( $token ) {

		$request = $this->get_new_request();

		$request->get_express_checkout_details( $token );

		$this->set_response_handler( 'WC_PayPal_Express_API_Checkout_Response' );

		return $this->perform_request( $request );
	}


	/**
	 * Perform a credit card authorization for the given order
	 *
	 * @since 3.0.0
	 * @param WC_Order $order the order
	 * @return \WC_PayPal_Express_API_Payment_Response payment response object
	 * @throws Exception network timeouts, etc
	 */
	public function credit_card_authorization( WC_Order $order ) {

		$this->order = $order;

		$request = $this->get_new_request();

		$request->do_payment_auth( $order );

		$this->set_response_handler( 'WC_PayPal_Express_API_Payment_Response' );

		return $this->perform_request( $request );
	}


	/**
	 * Perform a credit card charge for the given order
	 *
	 * @since 3.0.0
	 * @param WC_Order $order the order
	 * @return \WC_PayPal_Express_API_Payment_Response payment response object
	 * @throws Exception network timeouts, etc
	 */
	public function credit_card_charge( WC_Order $order ) {

		$this->order = $order;

		$request = $this->get_new_request();

		$request->do_payment_charge( $order );

		$this->set_response_handler( 'WC_PayPal_Express_API_Payment_Response' );

		return $this->perform_request( $request );
	}


	/**
	 * Perform a credit card capture for a given authorized order
	 *
	 * @since 3.0.0
	 * @param WC_Order $order the order
	 * @return \WC_PayPal_Express_API_Capture_Response capture payment response object
	 * @throws Exception network timeouts, etc
	 */
	public function credit_card_capture( WC_Order $order ) {

		$this->order = $order;

		$request = $this->get_new_request();

		$request->do_capture( $order );

		$this->set_response_handler( 'WC_PayPal_Express_API_Capture_Response' );

		return $this->perform_request( $request );
	}


	/**
	 * Perform a refund for the given order
	 *
	 * If the gateway does not support refunds, this method can be a no-op.
	 *
	 * @since 3.3.0
	 * @see SV_WC_Payment_Gateway_API::refund()
	 * @param WC_Order $order order object
	 * @return SV_WC_Payment_Gateway_API_Response refund response
	 * @throws SV_WC_Payment_Gateway_Exception network timeouts, etc
	 */
	public function refund( WC_Order $order ) {
		// TODO
	}


	/**
	 * Perform a void for the given order
	 *
	 * If the gateway does not support voids, this method can be a no-op.
	 *
	 * @since 3.3.0
	 * @see SV_WC_Payment_Gateway_API::void()
	 * @param WC_Order $order order object
	 * @return SV_WC_Payment_Gateway_API_Response void response
	 * @throws SV_WC_Payment_Gateway_Exception network timeouts, etc
	 */
	public function void( WC_Order $order ) {
		// TODO
	}


	/**
	 * Check if the response has any errors
	 *
	 * @since 3.0.0
	 * @see \SV_WC_API_Base::do_post_parse_response_validation()
	 * @throws \SV_WC_API_Exception if response has API error
	 */
	protected function do_post_parse_response_validation() {

		if ( $this->get_response()->has_api_error() ) {

			$message = sprintf( __( 'Code: %1$s, %2$s', 'ultimatewoo-pro' ), $this->get_response()->get_api_error_code(), $this->get_response()->get_api_error_message() );

			throw new SV_WC_API_Exception( $message );
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Builds and returns a new API request object
	 *
	 * @since 3.0.0
	 * @see \SV_WC_API_Base::get_new_request()
	 * @param array $type unused
	 * @return \WC_PayPal_Express_API_Request API request object
	 */
	protected function get_new_request( $type = array() ) {

		return new WC_PayPal_Express_API_Request( $this->api_username, $this->api_password, $this->api_signature, self::VERSION );
	}


	/**
	 * Returns the order associated with the request, if any
	 *
	 * @since 3.5.1
	 * @return \WC_Order|null
	 */
	public function get_order() {

		return $this->order;
	}


	/**
	 * Get the gateway ID for this request
	 *
	 * @since 3.0.0
	 * @see \SV_WC_API_Base::get_api_id()
	 * @return string
	 */
	protected function get_api_id() {
		return $this->gateway_id;
	}


	/**
	 * Returns the main plugin class
	 *
	 * @since 3.0.0
	 * @see \SV_WC_API_Base::get_plugin()
	 * @return object
	 */
	protected function get_plugin() {
		return wc_paypal_express();
	}


	/**
	 * Returns false, as PayPal Express does not support getting tokenized payment methods
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::supports_get_tokenized_payment_methods()
	 * @return bool false
	 */
	public function supports_get_tokenized_payment_methods() {

		return false;
	}


	/**
	 * Returns false, as PayPal Express does not support deleting tokenized payment methods
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::supports_remove_tokenized_payment_method()
	 * @return boolean false
	 */
	public function supports_remove_tokenized_payment_method() {

		return false;
	}


	/** No-op methods ******************************************************/


	/**
	 * PayPal Express does not support check debits
	 *
	 * No-op
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::check_debit()
	 * @param WC_Order $order the order
	 * @return SV_WC_Payment_Gateway_API_Response check debit response
	 * @throws Exception network timeouts, etc
	 */
	public function check_debit( WC_Order $order ) { }


	/**
	 * PayPal Express tokenizes the payment method during the sale
	 *
	 * No-op
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::tokenize_payment_method()
	 * @param \WC_Order $order the order with associated payment and customer info
	 * @return void
	 * @throws Exception network timeouts, etc
	 */
	public function tokenize_payment_method( WC_Order $order ) { }


	/**
	 * PayPal Express does not support getting tokenized payment methods
	 *
	 * No-op
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::get_tokenized_payment_methods()
	 * @param string $customer_id
	 * @return bool false
	 */
	public function get_tokenized_payment_methods( $customer_id ) { }


	/**
	 * PayPal Express does not support deleting tokenized payment methods
	 *
	 * No-op
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API::remove_tokenized_payment_method()
	 * @param string $token
	 * @param string $customer_id optional unique customer id for gateways that support it
	 * @return boolean false
	 */
	public function remove_tokenized_payment_method( $token, $customer_id ) { }


}
