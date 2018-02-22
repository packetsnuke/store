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
 * PayPal Express API Capture Payment Response
 *
 * Parses capture payment response
 *
 * @link https://developer.paypal.com/webapps/developer/docs/classic/api/merchant/DoCapture_API_Operation_NVP/
 *
 * @since 3.0.0
 */
class WC_PayPal_Express_API_Capture_Response extends WC_Paypal_Express_API_Payment_Response {


	/**
	 * DoCapture API responses have no prefix for the payment
	 *
	 * @since 3.0.0
	 * @see WC_PayPal_Express_API_Payment_Response::get_payment_parameter_prefix()
	 * @return string
	 */
	protected function get_payment_parameter_prefix() {
		return '';
	}


}
