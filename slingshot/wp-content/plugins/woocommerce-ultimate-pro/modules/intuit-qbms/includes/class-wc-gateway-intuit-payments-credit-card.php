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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Intuit Payments to newer
 * versions in the future. If you wish to customize WooCommerce Intuit Payments for your
 * needs please refer to http://docs.woothemes.com/document/intuit-qbms/
 *
 * @package   WC-Intuit-Payments/Gateway
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * The credit card gateway class.
 *
 * @since 2.0.0
 */
class WC_Gateway_Inuit_Payments_Credit_Card extends WC_Gateway_Inuit_Payments {


	/**
	 * Constructs the gateway.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct(
			WC_Intuit_Payments::CREDIT_CARD_ID,
			array(
				'method_title' => __( 'Intuit Payments Credit Card', 'ultimatewoo-pro' ),
				'supports'     => array(
					self::FEATURE_CARD_TYPES,
					self::FEATURE_CREDIT_CARD_CHARGE,
					self::FEATURE_CREDIT_CARD_CHARGE_VIRTUAL,
					self::FEATURE_CREDIT_CARD_AUTHORIZATION,
					self::FEATURE_CREDIT_CARD_CAPTURE,
					self::FEATURE_DETAILED_CUSTOMER_DECLINE_MESSAGES,
					self::FEATURE_TOKENIZATION,
					self::FEATURE_ADD_PAYMENT_METHOD,
					self::FEATURE_TOKEN_EDITOR,
				),
				'payment_type' => self::PAYMENT_TYPE_CREDIT_CARD,
			)
		);
	}


	/**
	 * Gets the payment form field defaults.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_payment_method_defaults() {

		$defaults = parent::get_payment_method_defaults();

		if ( $this->is_test_environment() ) {
			$defaults['account-number'] = '4111111111111111';
		}

		return $defaults;
	}


	/**
	 * Gets the credit card test case options.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_test_case_options() {

		return array(
			'emulate=10201' => __( 'Payment system error', 'ultimatewoo-pro' ),
			'emulate=10301' => __( 'Card number is invalid', 'ultimatewoo-pro' ),
			'emulate=10401' => __( 'General decline', 'ultimatewoo-pro' ),
		);
	}


	/**
	 * Removes the input names for the credit card number and CSC fields so
	 * they're not POSTed to the server.
	 *
	 * @since 2.0.0
	 * @param array $fields the payment form fields
	 * @return array
	 */
	public function remove_payment_form_field_input_names( $fields ) {

		$fields['card-number']['name'] = '';

		if ( isset( $fields['card-csc']['name'] ) ) {
			$fields['card-csc']['name'] = '';
		}

		return $fields;
	}


	/**
	 * Renders hidden inputs on the payment form for the card token & last four.
	 *
	 * These are populated by the client-side JS after successful tokenization.
	 *
	 * @since 2.0.0
	 */
	public function render_hidden_inputs() {

		parent::render_hidden_inputs();

		// card type
		printf( '<input type="hidden" id="%1$s" name="%1$s" />', 'wc-' . sanitize_html_class( $this->get_id_dasherized() ) . '-card-type' );
	}


	/**
	 * Validate the provided credit card fields.
	 *
	 * @since 2.0.0
	 * @see SV_WC_Payment_Gateway_Direct::validate_credit_card_fields()
	 * @param bool $is_valid whether the fields are valid
	 * @return bool whether the fields are valid
	 */
	protected function validate_credit_card_fields( $is_valid ) {

		$valid_card_types = array(
			SV_WC_Payment_Gateway_Helper::CARD_TYPE_AMEX,
			SV_WC_Payment_Gateway_Helper::CARD_TYPE_VISA,
			SV_WC_Payment_Gateway_Helper::CARD_TYPE_MASTERCARD,
			SV_WC_Payment_Gateway_Helper::CARD_TYPE_DISCOVER,
			SV_WC_Payment_Gateway_Helper::CARD_TYPE_DINERSCLUB,
			SV_WC_Payment_Gateway_Helper::CARD_TYPE_JCB,
		);

		// card type
		if ( ! in_array( SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-card-type' ), $valid_card_types, true ) ) {

			SV_WC_Helper::wc_add_notice( __( 'Provided card type is invalid.', 'ultimatewoo-pro' ), 'error' );
			$is_valid = false;
		}

		return $is_valid;
	}


	/**
	 * The CSC field is verified client-side and thus always valid.
	 *
	 * @since 4.0.0
	 * @param string $field
	 * @return bool
	 */
	protected function validate_csc( $field ) {

		return true;
	}


	/**
	 * Gets the order object with payment information added.
	 *
	 * @since 2.0.0
	 * @param int $order_id the order ID
	 * @return \WC_Order the order object
	 */
	public function get_order( $order_id ) {

		$order = parent::get_order( $order_id );

		if ( isset( $order->payment->js_token ) ) {

			// expiry month/year
			list( $order->payment->exp_month, $order->payment->exp_year ) = array_map( 'trim', explode( '/', SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-expiry' ) ) );

			// card data
			$order->payment->card_type = SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-card-type' );
		}

		return $order;
	}


	/**
	 * Adds an order notice to held orders that require further action.
	 *
	 * @since 2.0.0
	 * @see \SV_WC_Payment_Gateway::mark_order_as_held()
	 */
	public function mark_order_as_held( $order, $message, $response = null ) {

		parent::mark_order_as_held( $order, $message, $response );

		if ( $response && $response->get_status_message() ) {

			// if this was an authorization, mark as invalid for capture
			if ( $this->perform_credit_card_authorization( $order ) ) {
				$this->update_order_meta( $order, 'auth_can_be_captured', 'no' );
			}

			if ( $response->get_status_message() !== $message ) {
				$order->add_order_note( $response->get_status_message() );
			}
		}
	}


	/**
	 * Determines if the refund ended up being a void.
	 *
	 * @since 2.0.0
	 * @param \WC_Order $order order object
	 * @param \SV_WC_Payment_Gateway_API_Response $response refund response
	 * @return bool
	 */
	protected function maybe_void_instead_of_refund( $order, $response ) {

		return $response->is_void();
	}


}
