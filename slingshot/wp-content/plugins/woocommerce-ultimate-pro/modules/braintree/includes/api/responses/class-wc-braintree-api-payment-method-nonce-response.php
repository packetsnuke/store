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
 * @package   WC-Braintree/Gateway/API/Responses/Payment-Nonce
 * @author    SkyVerge
 * @copyright Copyright: (c) 2011-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Payment Method Nonce Response Class
 *
 * Handles parsing payment method nonce responses
 *
 * @since 3.0.0
 */
class WC_Braintree_API_Payment_Method_Nonce_Response extends WC_Braintree_API_Response {


	/**
	 * Get the payment method nonce
	 *
	 * @link https://developers.braintreepayments.com/reference/response/payment-method-nonce/php
	 *
	 * @since 3.0.0
	 * @return mixed
	 */
	public function get_nonce() {

		return isset( $this->response->paymentMethodNonce ) ? $this->response->paymentMethodNonce->nonce : null;
	}


	/**
	 * Returns true if the payment method has 3D Secure information present
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function has_3d_secure_info() {

		return isset( $this->response->paymentMethodNonce ) && isset( $this->response->paymentMethodNonce->threeDSecureInfo );
	}


	/**
	 * Returns the 3D secure statuses
	 *
	 * @link https://developers.braintreepayments.com/reference/response/payment-method-nonce/php#three_d_secure_info.status
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_3d_secure_status() {

		return $this->has_3d_secure_info() ? $this->response->paymentMethodNonce->threeDSecureInfo->status : null;
	}


	/**
	 * Returns true if liability was shifted for the 3D secure transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/response/payment-method-nonce/php#three_d_secure_info.liability_shifted
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function get_3d_secure_liability_shifted() {

		return $this->has_3d_secure_info() ? $this->response->paymentMethodNonce->threeDSecureInfo->liabilityShifted : null;
	}


	/**
	 * Returns true if a liability shift was possible for the 3D secure transaction
	 *
	 * @link https://developers.braintreepayments.com/reference/response/payment-method-nonce/php#three_d_secure_info.liability_shift_possible
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function get_3d_secure_liability_shift_possible() {

		return $this->has_3d_secure_info() ? $this->response->paymentMethodNonce->threeDSecureInfo->liabilityShiftPossible : null;
	}


	/**
	 * Returns true if the card was enrolled in a 3D secure program
	 *
	 * @link https://developers.braintreepayments.com/reference/response/payment-method-nonce/php#three_d_secure_info.enrolled
	 *
	 * @since 3.0.0
	 * @return bool
	 */
	public function get_3d_secure_enrollment() {

		return $this->has_3d_secure_info() && 'Y' === $this->response->paymentMethodNonce->threeDSecureInfo->enrolled;
	}


}
