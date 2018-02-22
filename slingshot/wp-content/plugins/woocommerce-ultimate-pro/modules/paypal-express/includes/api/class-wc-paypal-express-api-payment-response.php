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
 * PayPal Express API Do Payment Response Class
 *
 * Parses DoExpressCheckoutPayment response
 *
 * @link https://developer.paypal.com/webapps/developer/docs/classic/api/merchant/DoExpressCheckoutPayment_API_Operation_NVP/
 *
 * @since 3.0.0
 * @see SV_WC_Payment_Gateway_API_Response
 */
class WC_Paypal_Express_API_Payment_Response extends WC_Paypal_Express_API_Response implements SV_WC_Payment_Gateway_API_Response, SV_WC_Payment_Gateway_API_Authorization_Response {


	/** approved transaction response payment status */
	const TRANSACTION_COMPLETED = 'Completed';

	/** in progress transaction response payment status */
	const TRANSACTION_INPROGRESS = 'In-Progress';

	/** in progress transaction response payment status */
	const TRANSACTION_PROCESSED = 'Processed';

	/** held for review transaction response payment status */
	const TRANSACTION_COMPLETED_HELD = 'Completed-Funds-Held';

	/** pending transaction response payment status */
	const TRANSACTION_PENDING = 'Pending';

	/** @var array URL-decoded and parsed parameters */
	protected $successful_statuses = array();


	/**
	 * Parse the payment response
	 *
	 * @since 3.0.0
	 * @see WC_PayPal_Express_API_Response::__construct()
	 * @param string $response the raw URL-encoded response string
	 * @param WC_Order $order the order object associated with this response
	 */
	public function __construct( $response, WC_Order $order = null ) {

		parent::__construct( $response, $order );

		$this->successful_statuses = array(
			self::TRANSACTION_COMPLETED,
			self::TRANSACTION_COMPLETED_HELD,
			self::TRANSACTION_PROCESSED,
			self::TRANSACTION_INPROGRESS,
		);
	}

	/**
	 * Checks if the transaction was successful
	 *
	 * @since 3.0.0
	 * @return bool true if approved, false otherwise
	 */
	public function transaction_approved() {

		return in_array( $this->get_payment_status(), $this->successful_statuses );
	}


	/**
	 * Returns true if the payment is pending, for instance if the
	 * payment was authorized, but not captured. There are many other
	 * possible reasons
	 *
	 * @link https://developer.paypal.com/docs/classic/api/merchant/DoExpressCheckoutPayment_API_Operation_NVP/#id105CAM003Y4__id116RI0UF0YK
	 *
	 * @since 3.0.0
	 * @return bool true if the transaction was held, false otherwise
	 */
	public function transaction_held() {

		return self::TRANSACTION_PENDING === $this->get_payment_status();
	}


	/**
	 * Gets the response status code, or null if there is no status code
	 * associated with this transaction.
	 *
	 * @link https://developer.paypal.com/docs/classic/api/merchant/DoExpressCheckoutPayment_API_Operation_NVP/#id105CAM003Y4__id116RI0UF0YK
	 *
	 * @since 3.0.0
	 * @return string status code
	 */
	public function get_status_code() {

		return $this->get_payment_status();
	}


	/**
	 * Gets the response status message, or null if there is no status message
	 * associated with this transaction.
	 *
	 * PayPal provides additional info only for Pending or Completed-Funds-Held
	 * transactions.
	 *
	 * @since 3.0.0
	 * @return string status message
	 */
	public function get_status_message() {

		$message = '';

		if ( $this->transaction_held() ) {

			// PayPal's "pending" is our Held
			$message = $this->get_pending_reason();

		} elseif ( self::TRANSACTION_COMPLETED_HELD == $this->get_payment_status() ) {

			// Completed-Held means the payment was successful, but the merchant needs to take action to receive the funds
			$message = $this->get_held_reason();

		} elseif ( 'echeck' == $this->get_payment_type() ) {

			// add some additional info for eCheck payments
			$message = sprintf( __( 'expected clearing date %s', 'ultimatewoo-pro' ), date_i18n( wc_date_format(), strtotime( $this->get_parameter( $this->get_payment_parameter_prefix() . 'PAYMENTINFO_n_EXPECTEDECHECKCLEARDATE' ) ) ) );
		}

		// add fraud filters
		if ( $filters = $this->get_fraud_filters() ) {

			foreach ( $filters as $filter ) {
				$message .= sprintf( ' %1$s: %2$s', $filter['name'], $filter['id'] );
			}
		}

		return $message;
	}


	/**
	 * Gets the response transaction id, or null if there is no transaction id
	 * associated with this transaction.
	 *
	 * @since 3.0.0
	 * @return string transaction id
	 */
	public function get_transaction_id() {

		return $this->get_parameter( $this->get_payment_parameter_prefix() . 'TRANSACTIONID' );
	}


	/**
	 * Return true if the response has a payment type other than `none`
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function has_payment_type() {

		return 'none' !== $this->get_payment_type();
	}


	/**
	 * Get the PayPal payment type, either `none`, `echeck`, or `instant`
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_payment_type() {

		return $this->get_parameter( $this->get_payment_parameter_prefix() . 'PAYMENTTYPE' );
	}


	/**
	 * Gets payment status
	 *
	 * @since 3.0.0
	 * @return string
	 */
	private function get_payment_status() {

		return $this->has_parameter( $this->get_payment_parameter_prefix() . 'PAYMENTSTATUS' ) ? $this->get_parameter( $this->get_payment_parameter_prefix() . 'PAYMENTSTATUS' ) : 'N/A';
	}


	/**
	 * Gets the pending reason
	 *
	 * @since 3.0.0
	 * @return string
	 */
	private function get_pending_reason() {

		return $this->has_parameter( $this->get_payment_parameter_prefix() . 'PENDINGREASON' ) ? $this->get_parameter( $this->get_payment_parameter_prefix() . 'PENDINGREASON' ) : 'N/A';
	}


	/**
	 * Gets the held reason
	 *
	 * @since 3.0.0
	 * @return string
	 */
	private function get_held_reason() {

		return $this->has_parameter( $this->get_payment_parameter_prefix() . 'HOLDDECISION' ) ? $this->get_parameter( $this->get_payment_parameter_prefix() . 'HOLDDECISION' ) : 'N/A';
	}


	/**
	 * DoExpressCheckoutPayment API responses have a prefix for the payment
	 * parameters. Parallels payments are not used, so the numeric portion of
	 * the prefix is always '0'
	 *
	 * @since 3.0.0
	 * @see WC_PayPal_Express_API_Payment_Response::get_payment_parameter_prefix()
	 * @return string
	 */
	protected function get_payment_parameter_prefix() {
		return 'PAYMENTINFO_0_';
	}


	/** AVS/CSC Methods *******************************************************/


	/**
	 * PayPal Express does not return an authorization code
	 *
	 * @since 3.0.0
	 * @return string credit card authorization code
	 */
	public function get_authorization_code() {
		return false;
	}


	/**
	 * Returns the result of the AVS check
	 *
	 * @since 3.0.0
	 * @return string result of the AVS check, if any
	 */
	public function get_avs_result() {

		if ( $filters = $this->get_fraud_filters() ) {

			foreach ( $filters as $filter ) {

				if ( in_array( $filter['id'], range( 1, 3 ) ) ) {

					return $filter['id'];
				}
			}
		}

		return null;
	}


	/**
	 * Returns the result of the CSC check
	 *
	 * @since 3.0.0
	 * @return string result of CSC check
	 */
	public function get_csc_result() {

		if ( $filters = $this->get_fraud_filters() ) {

			foreach ( $filters as $filter ) {

				if ( '4' == $filter['id'] ) {

					return $filter['id'];
				}
			}
		}

		return null;
	}


	/**
	 * Returns true if the CSC check was successful
	 *
	 * @since 3.0.0
	 * @return boolean true if the CSC check was successful
	 */
	public function csc_match() {

		return is_null( $this->get_csc_result() );
	}


	/**
	 * Return any fraud management data available. This data is explicitly
	 * enabled in the request, but PayPal recommends checking certain error
	 * conditions prior to accessing this data.
	 *
	 * This data provides additional context for why a transaction was held for
	 * review or declined.
	 *
	 * @link https://developer.paypal.com/webapps/developer/docs/classic/fmf/integration-guide/FMFProgramming/#id091UNG0065Z
	 * @link https://developer.paypal.com/webapps/developer/docs/classic/api/merchant/DoExpressCheckoutPayment_API_Operation_NVP/ (RiskFilterList Type Fields)
	 *
	 * @since 3.0.0
	 * @return array $filters {
	 *   @type string $id filter ID, integer from 1-17
	 *   @type string name filter name, short description for filter
	 * }
	 */
	private function get_fraud_filters() {

		$filters = array();

		if ( '11610' == $this->get_api_error_code() ) {

			$type = 'PENDING';

		} elseif ( '11611' == $this->get_api_error_code() ) {

			$type = 'DENY';

		} else {

			// not supporting REPORT type yet
			return $filters;
		}

		foreach ( range( 0, 9 ) as $index ) {

			if ( $this->has_parameter( "L_PAYMENTINFO_0_FMF{$type}ID{$index}" ) && $this->has_parameter( "L_PAYMENTINFO_0_FMF{$type}NAME{$index}" ) ) {
				$filters[] = array(
					'id'   => $this->get_parameter( "L_PAYMENTINFO_0_FMF{$type}ID{$index}" ),
					'name' => $this->get_parameter( "L_PAYMENTINFO_0_FMF{$type}NAME{$index}" ),
				);
			}
		}

		return $filters;
	}


}
