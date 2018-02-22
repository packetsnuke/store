<?php
/**
 * WooCommerce Twilio SMS Notifications
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Twilio SMS Notifications to newer
 * versions in the future. If you wish to customize WooCommerce Twilio SMS Notifications for your
 * needs please refer to http://docs.woocommerce.com/document/twilio-sms-notifications/ for more information.
 *
 * @package     WC-Twilio-SMS-Notifications/AJAX
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Twilio SMS AJAX class
 *
 * Handles all AJAX actions
 *
 * @since 1.0
 */
class WC_Twilio_SMS_AJAX {


	/**
	 * Adds required wp_ajax_* hooks
	 *
	 * @since  1.0
	 */
	public function __construct() {

		add_action( 'wp_ajax_woocommerce_twilio_sms_send_test_sms', array( $this, 'send_test_sms' ) );

		// Process 'Toggle automated updates' meta-box action
		add_action( 'wp_ajax_wc_twilio_sms_toggle_order_updates', array( $this, 'toggle_order_updates' ) );

		// Process 'Send an SMS' meta-box action
		add_action( 'wp_ajax_wc_twilio_sms_send_order_sms', array( $this, 'send_order_sms' ) );
	}

	/**
	 * Handle test SMS AJAX call
	 *
	 * @since  1.0
	 */
	public function send_test_sms() {

		$this->verify_request( $_POST['security'], 'wc_twilio_sms_send_test_sms' );

		// sanitize input
		$mobile_number = $_POST[ 'mobile_number' ];
		$message       = sanitize_text_field( $_POST[ 'message' ] );

		try {

			wc_twilio_sms()->get_api()->send( $mobile_number, $message );

			exit( __( 'Test message sent successfully', 'ultimatewoo-pro' ) );
		}
		catch ( Exception $e ) {

			die( sprintf( __( 'Error sending SMS: %s', 'ultimatewoo-pro' ), $e->getMessage() ) );
		}
	}


	/**
	 * Toggle automated SMS messages from the edit order page
	 *
	 * @since 1.6.0
	 */
	public function toggle_order_updates() {

		$this->verify_request( $_POST['security'], 'wc_twilio_sms_toggle_order_updates' );

		$order_id = ( is_numeric( $_POST['order_id'] ) ) ? absint( $_POST['order_id'] ) : null;

		if ( ! $order_id ) {
			return;
		}

		$order          = wc_get_order( $order_id );
		$current_status = SV_WC_Order_Compatibility::get_meta( $order, '_wc_twilio_sms_optin', true );

		if ( empty( $current_status ) ) {
			SV_WC_Order_Compatibility::update_meta_data( $order, '_wc_twilio_sms_optin', 1 );
		} else {
			SV_WC_Order_Compatibility::delete_meta_data( $order, '_wc_twilio_sms_optin' );
		}

		exit();
	}


	/**
	 * Send an SMS from the edit order page
	 *
	 * @since 1.1.4
	 */
	public function send_order_sms() {

		$this->verify_request( $_POST['security'], 'wc_twilio_sms_send_order_sms' );

		// sanitize message
		$message = sanitize_text_field( $_POST[ 'message' ] );

		$order_id = ( is_numeric( $_POST['order_id'] ) ) ? absint( $_POST['order_id'] ) : null;

		if ( ! $order_id ) {
			return;
		}

		$notification = new WC_Twilio_SMS_Notification( $order_id );

		// send the SMS
		$notification->send_manual_customer_notification( $message );

		exit( __( 'Message Sent', 'ultimatewoo-pro' ) );
	}


	/**
	 * Verifies AJAX request is valid
	 *
	 * @since  1.0
	 * @param string $nonce
	 * @param string $action
	 * @return void|bool
	 */
	private function verify_request( $nonce, $action ) {

		if( ! is_admin() || ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'ultimatewoo-pro' ) );
		}

		if( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_die( __( 'You have taken too long, please go back and try again.', 'ultimatewoo-pro' ) );
		}

		return true;
	}


}
