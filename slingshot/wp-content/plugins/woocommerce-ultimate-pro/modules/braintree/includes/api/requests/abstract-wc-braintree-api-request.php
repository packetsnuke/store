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
 * @package   WC-Braintree/Gateway/API/Request
 * @author    SkyVerge
 * @copyright Copyright: (c) 2011-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Braintree API Abstract Request Class
 *
 * Provides functionality common to all requests
 *
 * @since 3.0.0
 */
abstract class WC_Braintree_API_Request implements SV_WC_Payment_Gateway_API_Request {


	/** @var string static callback for the request, e.g. `Braintree_ClientToken::generate` */
	protected $callback;

	/** @var array request data passed to the static callback */
	protected $request_data = array();

	/** @var \WC_Order order associated with the request, if any */
	protected $order;


	/**
	 * Setup request
	 *
	 * @since 3.0.0
	 * @param \WC_Order|null $order order if available
	 */
	public function __construct( $order = null ) {

		$this->order = $order;
	}


	/**
	 * Set the static callback for the request
	 *
	 * @since 3.0.0
	 * @param string $callback, e.g. `Braintree_ClientToken::generate`
	 */
	protected function set_callback( $callback ) {

		$this->callback = $callback;
	}


	/**
	 * Get the static callback for the request
	 *
	 * @since 3.0.0
	 * @return string static callback
	 */
	public function get_callback() {

		return $this->callback;
	}


	/**
	 * Get the callback parameters for the request
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public function get_callback_params() {

		switch ( $this->get_callback() ) {

			// these API calls use 2 callback parameters
			case 'Braintree_Transaction::submitForSettlement':
			case 'Braintree_Transaction::refund':
			case 'Braintree_PaymentMethod::update':
				return $this->get_request_data();

			// all others use a single callback param
			default:
				return array( $this->get_request_data() );
		}
	}


	/**
	 * Return the string representation of the request
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function to_string() {

		return print_r( $this->get_request_data(), true );
	}


	/**
	 * Return the string representation of the request, stripped of any
	 * confidential information
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function to_string_safe() {

		// no confidential info to mask...yet
		return $this->to_string();
	}


	/**
	 * Get the request data which is the 1st parameter passed to the static callback
	 * set
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public function get_request_data() {

		/**
		 * Braintree API Request Data.
		 *
		 * Allow actors to modify the request data before it's sent to Braintree
		 *
		 * @since 3.0.0
		 * @param array|mixed $data request data to be filtered
		 * @param \WC_Order $order order instance
		 * @param \WC_Braintree_API_Request $this, API request class instance
		 */
		$this->request_data = apply_filters( 'wc_braintree_api_request_data', $this->request_data, $this );

		$this->remove_empty_data();

		return $this->request_data;
	}


	/**
	 * Remove null or blank string values from the request data (up to 2 levels deep)
	 *
	 * @TODO: this can be improved to traverse deeper and be simpler @MR 2015-10-23
	 *
	 * @since 3.0.0
	 */
	protected function remove_empty_data() {

		foreach ( (array) $this->request_data as $key => $value ) {

			if ( is_array( $value ) ) {

				if ( empty( $value ) ) {

					unset( $this->request_data[ $key ] );

				} else {

					foreach ( $value as $inner_key => $inner_value ) {

						if ( is_null( $inner_value ) || '' === $inner_value ) {
							unset( $this->request_data[ $key ][ $inner_key ] );
						}
					}
				}

			} else {

				if ( is_null( $value ) || '' === $value ) {
					unset( $this->request_data[ $key ] );
				}
			}
		}
	}


	/**
	 * Get the order associated with the request, if any
	 *
	 * @since 3.0.0
	 * @return \WC_Order|null
	 */
	public function get_order() {

		return $this->order;
	}


	/**
	 * Braintree requests do not require a method per request
	 *
	 * @since 3.0.0
	 * @return null
	 */
	public function get_method() { }


	/**
	 * Braintree requests do not require a path per request
	 *
	 * @since 3.0.0
	 * @return null
	 */
	public function get_path() { }


}
