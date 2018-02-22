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
 * The Intuit Payments AJAX class.
 *
 * @since 2.0.0
 */
class WC_Intuit_Payments_AJAX {


	/**
	 * Constructs the class.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		add_action( 'wc_' . wc_intuit_payments()->get_id() . '_cron_reconnect', array( $this, 'cron_reconnect' ) );

		add_action( 'wp_ajax_wc_' . wc_intuit_payments()->get_id() . '_reconnect', array( $this, 'reconnect' ) );
		add_action( 'wp_ajax_wc_' . wc_intuit_payments()->get_id() . '_disconnect', array( $this, 'disconnect' ) );

		add_action( 'wp_ajax_wc_' . wc_intuit_payments()->get_id() . '_log_js_data',        array( $this, 'log_js_data' ) );
		add_action( 'wp_ajax_nopriv_wc_' . wc_intuit_payments()->get_id() . '_log_js_data', array( $this, 'log_js_data' ) );
	}


	/**
	 * Handles the cron reconnect event.
	 *
	 * @since 2.0.0
	 * @param string $gateway_id The gateway ID to reconnect.
	 */
	public function cron_reconnect( $gateway_id ) {

		if ( $gateway = wc_intuit_payments()->get_gateway( $gateway_id ) ) {

			if ( ! $gateway->oauth_reconnect() ) {
				$gateway->reset_reconnect_cron_event( time() + DAY_IN_SECONDS );
			}
		}
	}


	/**
	 * Reconnects the merchant account via AJAX.
	 *
	 * @since 2.0.0
	 */
	public function reconnect() {

		check_ajax_referer( 'wc-intuit-payments-reconnect', 'nonce' );

		$gateway_id = SV_WC_Helper::get_request( 'gateway_id' );

		if ( $gateway = wc_intuit_payments()->get_gateway( $gateway_id ) ) {

			$gateway->oauth_reconnect();
		}

		wp_die();
	}


	/**
	 * Disconnects the merchant account via AJAX.
	 *
	 * @since 2.0.0
	 */
	public function disconnect() {

		check_ajax_referer( 'wc-intuit-payments-disconnect', 'nonce' );

		$gateway_id = SV_WC_Helper::get_request( 'gateway_id' );

		if ( $gateway = wc_intuit_payments()->get_gateway( $gateway_id ) ) {

			$gateway->oauth_disconnect();
		}

		wp_die();
	}


	/**
	 * Writes card tokenization JS request/response data to the standard debug log.
	 *
	 * @since 2.0.0
	 */
	public function log_js_data() {

		$gateway_id = SV_WC_Helper::get_request( 'gateway_id' );

		if ( $gateway = wc_intuit_payments()->get_gateway( $gateway_id ) ) {

			check_ajax_referer( 'wc_' . wc_intuit_payments()->get_id() . '_log_js_data', 'security' );

			if ( ! empty( $_REQUEST['data'] ) ) {

				$message = sprintf( "Token %1\$s\n%1\$s Body: ", ! empty( $_REQUEST['type'] ) ? ucfirst( $_REQUEST['type'] ) : 'Request' );

				// add the data
				$message .= print_r( $_REQUEST['data'], true );

				$gateway->add_debug_message( $message );
			}

			wp_send_json_success();
		}
	}


}
