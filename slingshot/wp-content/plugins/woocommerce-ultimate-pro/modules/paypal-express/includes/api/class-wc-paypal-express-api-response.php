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
 * PayPal Express API Response Class
 *
 * Parses response string received from PayPal Express API, which is simply a URL-encoded string of parameters
 *
 * @link https://developer.paypal.com/docs/classic/api/NVPAPIOverview/#id084DN080HY4
 *
 * @since 3.0.0
 * @see SV_WC_Payment_Gateway_API_Response
 */
class WC_Paypal_Express_API_Response {


	/** @var array URL-decoded and parsed parameters */
	protected $parameters = array();

	/** @var \WC_Order optional order object if this request was associated with an order */
	protected $order;


	/**
	 * Parse the response parameters from the raw URL-encoded response string
	 *
	 * @link https://developer.paypal.com/docs/classic/api/NVPAPIOverview/#id084FBM0M0HS
	 *
	 * @since 3.0.0
	 * @param string $response the raw URL-encoded response string
	 * @param WC_Order $order the order object associated with this response
	 */
	public function __construct( $response, WC_Order $order = null ) {

		$this->order = $order;

		// URL decode the response string and parse it
		parse_str( urldecode( $response ), $this->parameters );
	}


	/**
	 * Checks if response contains an API error code
	 *
	 * @link https://developer.paypal.com/docs/classic/api/errorcodes/
	 *
	 * @since 3.0.0
	 * @return bool true if has API error, false otherwise
	 */
	public function has_api_error() {

		// assume something went wrong if ACK is missing
		if ( ! $this->has_parameter( 'ACK' ) ) {
			return true;
		}

		// any non-success ACK is considered an error, see
		// https://developer.paypal.com/docs/classic/api/NVPAPIOverview/#id09C2F0K30L7
		return ( 'Success' !== $this->get_parameter( 'ACK' ) && 'SuccessWithWarning' !== $this->get_parameter( 'ACK' ) );
	}


	/**
	 * Gets the API error code
	 *
	 * Note that PayPal can return multiple error codes, which are merged here
	 * for convenience
	 *
	 * @link https://developer.paypal.com/docs/classic/api/errorcodes/
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_api_error_code() {

		$error_codes = array();

		foreach ( range( 0, 9 ) as $index ) {

			if ( $this->has_parameter( "L_ERRORCODE{$index}" ) ) {
				$error_codes[] = $this->get_parameter( "L_ERRORCODE{$index}" );
			}
		}

		return empty( $error_codes ) ? 'N/A' : trim( implode( ', ', $error_codes ) );
	}


	/**
	 * Gets the API error message
	 *
	 * Note that PayPal can return multiple error messages, which are merged here
	 * for convenience
	 *
	 * @link https://developer.paypal.com/docs/classic/api/errorcodes/
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_api_error_message() {

		$error_messages = array();

		foreach ( range( 0, 9 ) as $index ) {

			if ( $this->has_parameter( "L_SHORTMESSAGE{$index}" ) ) {

				$error_message = sprintf( '%1$s: %2$s - %3$s',
					$this->has_parameter( "L_SEVERITYCODE{$index}" ) ? $this->get_parameter( "L_SEVERITYCODE{$index}" ) : __( 'Error', 'ultimatewoo-pro' ),
					$this->has_parameter( "L_SHORTMESSAGE{$index}" ) ? $this->get_parameter( "L_SHORTMESSAGE{$index}" ) : __( 'Unknown', 'ultimatewoo-pro' ),
					$this->has_parameter( "L_LONGMESSAGE{$index}" ) ? $this->get_parameter( "L_LONGMESSAGE{$index}" ) : __( 'Unknown error', 'ultimatewoo-pro' )
				);

				// append additional info if available
				if ( $this->has_parameter( "L_ERRORPARAMID{$index}" ) && $this->has_parameter( "L_ERRORPARAMVALUE{$index}" ) ) {
					$error_message .= sprintf( ' (%1$s - %1$s)', $this->get_parameter( "L_ERRORPARAMID{$index}" ), $this->get_parameter( "L_ERRORPARAMVALUE{$index}" ) );
				}

				$error_messages[] = $error_message;
			}
		}

		return empty( $error_messages ) ? __( 'N/A', 'ultimatewoo-pro' ) : trim( implode( ', ', $error_messages ) );
	}


	/**
	 * Returns true if the parameter is not empty
	 *
	 * @since 3.0.0
	 * @param string $name parameter name
	 * @return bool
	 */
	protected function has_parameter( $name ) {
		return ! empty( $this->parameters[ $name ] );
	}


	/**
	 * Gets the parameter value, or null if parameter is not set or empty
	 *
	 * @since 3.0.0
	 * @param string $name parameter name
	 * @return string|null
	 */
	protected function get_parameter( $name ) {
		return $this->has_parameter( $name ) ? $this->parameters[ $name ] : null;
	}


	/**
	 * Returns a message appropriate for a frontend user.  This should be used
	 * to provide enough information to a user to allow them to resolve an
	 * issue on their own, but not enough to help nefarious folks fishing for
	 * info.
	 *
	 * @link https://developer.paypal.com/docs/classic/api/errorcodes/
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API_Response_Message_Helper::get_user_message()
	 * @return string user message, if there is one
	 */
	public function get_user_message() {

		$allowed_user_error_message_codes = array(
			'10445', '10474', '12126', '13113', '13122', '13112',
		);

		return in_array( $this->get_api_error_code(), $allowed_user_error_message_codes ) ? $this->get_api_error_message() : null;
	}


	/**
	 * Returns the string representation of this response
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API_Response::to_string()
	 * @return string response
	 */
	public function to_string() {

		return print_r( $this->parameters, true );
	}


	/**
	 * Returns the string representation of this response with any and all
	 * sensitive elements masked or removed
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_API_Response::to_string_safe()
	 * @return string response safe for logging/displaying
	 */
	public function to_string_safe() {

		// no sensitive data to mask
		return $this->to_string();
	}


	/**
	 * Return the payment type for the transaction, always 'paypal'
	 *
	 * @since 3.7.0
	 * @return string
	 */
	public function get_payment_type() {

		return 'paypal';
	}


}
