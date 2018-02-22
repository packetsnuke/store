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
 * @package     WC-Twilio-SMS-Notifications/Notification
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Twilio SMS Notification class
 *
 * Handle SMS sending Admin & Customer Notifications, as well as manual SMS messages from Order page
 *
 * @since 1.0
 */
class WC_Twilio_SMS_Notification  {

	/** @var \WC_Order order object for SMS sending  */
	private $order;


	/**
	 * Load new order object
	 *
	 * @since 1.0
	 * @param int $order_id the order ID
	 */
	public function __construct( $order_id ) {

		$this->order = wc_get_order( $order_id );

	}


	/**
	 * Send admin new order SMS notifications
	 *
	 * @since 1.0
	 */
	public function send_admin_notification() {

		// Check if sending admin SMS updates for new orders
		if ( 'yes' === get_option( 'wc_twilio_sms_enable_admin_sms' ) ) {

			// get message template
			$message = get_option( 'wc_twilio_sms_admin_sms_template', '' );

			// replace template variables
			$message = $this->replace_message_variables( $message );

			// shorten URLs if enabled
			if ( 'yes' === get_option( 'wc_twilio_sms_shorten_urls' ) ) {
				$message = $this->shorten_urls( $message );
			}

			// get admin phone number (s)
			$recipients = explode( ',', trim( get_option( 'wc_twilio_sms_admin_sms_recipients' ) ) );

			// send the SMS to each recipient
			if ( ! empty( $recipients ) ) {

				foreach ( $recipients as $recipient ) {

					try {

						wc_twilio_sms()->get_api()->send( $recipient, $message, false );

					} catch ( Exception $e ) {

						wc_twilio_sms()->log( $e->getMessage() );
					}
				}
			}
		}
	}


	/**
	 * Sends customer SMS notifications on order status changes
	 *
	 * @since  1.0
	 */
	public function send_automated_customer_notification() {

		// get checkbox opt-in label
		$optin = get_option( 'wc_twilio_sms_checkout_optin_checkbox_label', '' );

		// check if opt-in checkbox is enabled
		if ( ! empty( $optin ) ) {

			// get opt-in meta for order
			$optin = SV_WC_Order_Compatibility::get_meta( $this->order, '_wc_twilio_sms_optin', true );

			// check if customer has opted-in
			if ( empty( $optin ) ) {
				// no meta set, so customer has not opted in
				return;
			}
		}

		// Check if sending SMS updates for this order's status
		if ( in_array( 'wc-' . $this->order->get_status(), get_option( 'wc_twilio_sms_send_sms_order_statuses' ) ) ) {

			// get message template
			$message = get_option( 'wc_twilio_sms_' . $this->order->get_status() . '_sms_template', '' );

			// use the default template if status-specific one is blank
			if ( empty( $message ) ) {
				$message = get_option( 'wc_twilio_sms_default_sms_template' );
			}

			// allow modification of message before variable replace (add additional variables, etc)
			$message = apply_filters( 'wc_twilio_sms_customer_sms_before_variable_replace', $message, $this->order );

			// replace template variables
			$message = $this->replace_message_variables( $message );

			// allow modification of message after variable replace
			$message = apply_filters( 'wc_twilio_sms_customer_sms_after_variable_replace', $message, $this->order );

			// allow modification of the "to" phone number
			$phone = apply_filters( 'wc_twilio_sms_customer_phone', SV_WC_Order_Compatibility::get_prop( $this->order, 'billing_phone' ), $this->order );

			// shorten URLs if enabled
			if ( 'yes' === get_option( 'wc_twilio_sms_shorten_urls' ) ) {
				$message = $this->shorten_urls( $message );
			}

			// send the SMS!
			$this->send_sms( $phone, $message );
		}
	}


	/**
	 * Sends SMS to customer from 'Send an SMS' metabox on Orders page
	 *
	 * @since 1.0
	 * @param string $message message to send customer
	 */
	public function send_manual_customer_notification( $message ) {

		// shorten URLs if enabled
		if ( 'yes' === get_option( 'wc_twilio_sms_shorten_urls' ) ) {
			$message = $this->shorten_urls( $message );
		}

		// send the SMS!
		$this->send_sms( SV_WC_Order_Compatibility::get_prop( $this->order, 'billing_phone' ), $message );
	}


	/**
	 * Create and send SMS message
	 *
	 * @since 1.0
	 * @param string $to
	 * @param string $message
	 * @param bool $customer_notification order note is added if true
	 */
	private function send_sms( $to, $message, $customer_notification = true ) {

		// Default status for SMS message, on error this is replaced with error message
		$status = __( 'Sent', 'ultimatewoo-pro' );

		// Timestamp of SMS is current time
		$sent_timestamp =  time();

		// error flag
		$error = false;

		try {

			// send the SMS via API
			$response = wc_twilio_sms()->get_api()->send( $to, $message, SV_WC_Order_Compatibility::get_prop( $this->order, 'billing_country' ) );

			// use the timestamp from twilio if available
			$sent_timestamp = ( isset( $response['date_created'] ) ) ? strtotime( $response['date_created'] ) : $sent_timestamp;

			// use twilio formatted number if available
			$to = ( isset( $response['to'] ) ) ? $response['to'] : $to;

		} catch ( Exception $e ) {

			// Set status to error message
			$status = $e->getMessage();

			// set error flag
			$error = true;

			// log to PHP error log
			wc_twilio_sms()->log( $e->getMessage() );
		}

		// Add formatted order note
		if ( $customer_notification ) {
			$this->order->add_order_note( $this->format_order_note( $to, $sent_timestamp, $message, $status, $error ) );
		}
	}

	/**
	 * Extract URLs from SMS message and replace them with shorten URLs via callback
	 *
	 * @since  1.0
	 * @param string $message SMS message
	 * @return string SMS message with URLs shortened
	 */
	private function shorten_urls( $message ) {

		// regex pattern source : http://daringfireball.net/2010/07/improved_regex_for_matching_urls
		$pattern = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";

		// find each URL and replacing using callback
		$message = preg_replace_callback( $pattern, array( $this, 'shorten_url' ), $message );

		// return message with shortened URLs
		return $message;
	}

	/**
	 * Callback for shorten_urls() preg_replace
	 * By default, uses Google URL Shortener
	 *
	 * @since 1.0
	 * @param array $matches matches found via preg_replace
	 * @return string shortened url
	 */
	private function shorten_url( $matches ) {

		// get first match
		$url = reset( $matches );

		$api_key = get_option( 'wc_twilio_sms_shortener_api_key', '' );

		if ( $api_key ) {
			$shortened_url = $this->google_shorten_url( $url, $api_key );
		} else {
			$shortened_url = $url;
		}

		/**
		 * Filters the a shortened URL.
		 *
		 * @since 1.8.2
		 * @param string $shortened_url the shortened URL
		 * @param string $url the original URL
		 */
		return apply_filters( 'wc_twilio_sms_shorten_url', $shortened_url, $url );
	}


	/**
	 * Shortens a given URL via Google URL Shortener
	 *
	 * @link : https://developers.google.com/url-shortener/v1/getting_started
	 * @since  1.0
	 * @param string $url URL to shorten
	 * @param string $api_key the API key
	 * @return string shortened URL
	 */
	private function google_shorten_url( $url, $api_key ) {

		$api_url = add_query_arg( 'key', $api_key, 'https://www.googleapis.com/urlshortener/v1/url' );

		// set wp_safe_remote_post arguments
		$args = array(
			'method'      => 'POST',
			'timeout'     => '10',
			'redirection' => 0,
			'httpversion' => '1.0',
			'sslverify'   => true,
			'headers'     => array(
				'content-type' => 'application/json',
			),
			'body' => json_encode( array(
				'longUrl' => $url,
			) ),
		);

		// perform POST request
		$response = wp_safe_remote_post( $api_url, $args );

		$error_message = __( 'URL Shortener error', 'ultimatewoo-pro' );

		// check for WP Error and bail
		if ( is_wp_error( $response ) ) {

			// log the error
			wc_twilio_sms()->log( $error_message . ': ' . $response->get_error_message() );

			return $url;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		// check for an API error
		if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {

			if ( ! empty( $data['error']['errors'] ) ) {

				foreach ( $data['error']['errors'] as $error ) {

					// append an error message if it was provided
					if ( ! empty( $error['message'] ) ) {
						$error_message .= ': ' . $error['message'];
					}

					// append the reason code if it was provided
					if ( ! empty( $error['reason'] ) ) {
						$error_message .= ' (' . $error['reason'] . ')';
					}

					wc_twilio_sms()->log( $error_message );
				}

			} else {

				wc_twilio_sms()->log( $error_message );
			}

			return $url;
		}

		// if short url was decoded successfully, use it
		if ( ! empty( $data['id'] ) ) {
			$url = $data['id'];
		}

		return $url;
	}


	/**
	 * Replaces template variables in SMS message
	 *
	 * @since 1.0
	 * @param string $message raw SMS message to replace with variable info
	 * @return string message with variables replaced with indicated values
	 */
	private function replace_message_variables( $message ) {

		$replacements = array(
			'%shop_name%'       => SV_WC_Helper::get_site_name(),
			'%order_id%'        => $this->order->get_order_number(),
			'%order_count%'     => $this->order->get_item_count(),
			'%order_amount%'    => $this->order->get_total(),
			'%order_status%'    => ucfirst( $this->order->get_status() ),
			'%billing_name%'    => $this->order->get_formatted_billing_full_name(),
			'%shipping_name%'   => $this->order->get_formatted_shipping_full_name(),
			'%shipping_method%' => $this->order->get_shipping_method(),
		);

		/**
		 * Filter the notification placeholders and replacements.
		 *
		 * @since 1.6.0
		 * @param array $replacements {
		 *     The replacements in 'placeholder' => 'replacement' format.
		 *
		 *     @type string %shop_name%       The site name.
		 *     @type int    %order_id%        The order ID.
		 *     @type int    %order_count%     The total number of items ordered.
		 *     @type string %order_amount%    The order total.
		 *     @type string %order_status%    The order status.
		 *     @type string %billing_name%    The billing first and last name.
		 *     @type string %shipping_name%   The shipping first and last name.
		 *     @type string %shipping_method% The shipping method name.
	 	 * }
		 * @param WC_Twilio_SMS_Notification $notification The notification object.
		 */
		$replacements = apply_filters( 'wc_twilio_sms_message_replacements', $replacements, $this );

		return str_replace( array_keys( $replacements ), $replacements, $message );
	}


	/**
	 * Formats order note
	 *
	 * @since  1.0
	 * @param string $to number SMS message was sent to
	 * @param int $sent_timestamp integer timestamp for when message was sent
	 * @param string $message SMS message sent
	 * @param string $status order status
	 * @param bool $error true if there was an error sending SMS, false otherwise
	 * @return string HTML-formatted order note
	 */
	private function format_order_note( $to, $sent_timestamp, $message, $status, $error ) {

		try {

			// get datetime object from unix timestamp
			$datetime = new DateTime( "@{$sent_timestamp}", new DateTimeZone( 'UTC' ) );

			// change timezone to site timezone
			$datetime->setTimezone( new DateTimeZone( wc_timezone_string() ) );

			// return datetime localized to site date/time settings
			$formatted_datetime = date_i18n( wc_date_format() . ' ' . wc_time_format(), $sent_timestamp + $datetime->getOffset() );

		} catch ( Exception $e ) {

			// log error and set datetime for SMS to 'N/A'
			wc_twilio_sms()->log( $e->getMessage() );
			$formatted_datetime = __( 'N/A', 'ultimatewoo-pro' );
		}

		ob_start();
		?>
		<p><strong><?php esc_html_e( 'SMS Notification', 'ultimatewoo-pro' ); ?></strong></p>
		<p><strong><?php esc_html_e( 'To', 'ultimatewoo-pro' ); ?>: </strong><?php echo esc_html( $to ); ?></p>
		<p><strong><?php esc_html_e( 'Date Sent', 'ultimatewoo-pro' ); ?>: </strong><?php echo esc_html( $formatted_datetime ); ?></p>
		<p><strong><?php esc_html_e( 'Message', 'ultimatewoo-pro' ); ?>: </strong><?php echo esc_html( $message ); ?></p>
		<p><strong><?php esc_html_e( 'Status', 'ultimatewoo-pro' ); ?>: <span style="<?php echo ( $error ) ? 'color: red;' : 'color: green;'; ?>"><?php echo esc_html( $status ); ?></span></strong></p>
		<?php

		return ob_get_clean();
	}


}
