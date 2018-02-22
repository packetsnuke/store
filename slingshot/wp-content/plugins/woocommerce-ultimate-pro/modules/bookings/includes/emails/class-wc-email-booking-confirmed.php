<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking is confirmed
 *
 * An email sent to the user when a booking is confirmed.
 *
 * @class 		WC_Email_Booking_Confirmed
 * @extends 	WC_Email
 */
class WC_Email_Booking_Confirmed extends WC_Email {

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->id 				= 'booking_confirmed';
		$this->title 			= __( 'Booking Confirmed', 'ultimatewoo-pro' );
		$this->description		= __( 'Booking confirmed emails are sent when the status of a booking goes to confirmed.', 'ultimatewoo-pro' );
		$this->heading 			= __( 'Booking Confirmed', 'ultimatewoo-pro' );
		$this->subject      	= __( '[{blogname}] Your booking of "{product_title}" has been confirmed (Order {order_number}) - {order_date}', 'ultimatewoo-pro' );
		$this->customer_email   = true;
		$this->template_html 	= 'emails/customer-booking-confirmed.php';
		$this->template_plain 	= 'emails/plain/customer-booking-confirmed.php';

		// Triggers for this email
		add_action( 'woocommerce_booking_confirmed_notification', array( $this, 'trigger' ) );

		// `schedule_trigger` function must run after `trigger` as we must ensure that when trigger runs the next time
		// it will find the value set in the schedule `trigger` function. This is to allow for cases
		// where the dates are change and emails are sent before the new data is saved.
		add_action( 'woocommerce_booking_confirmed_notification', array( $this, 'schedule_trigger' ), 80 );

		// Call parent constructor
		parent::__construct();

		// Other settings
		$this->template_base = WC_BOOKINGS_TEMPLATE_PATH;
	}

	/**
	 * @param    $booking_id
	 * @since    1.9.13 introduced
	 * @version  1.10.7
	 */
	public function schedule_trigger( $booking_id ) {
		$ids_pending_confirmation_email = get_transient( 'wc_booking_confirmation_email_send_ids' );
		$ids_pending_confirmation_email = is_array( $ids_pending_confirmation_email ) ? $ids_pending_confirmation_email : array();
		// if id is in array it means were currently processing it in WC_Booking_Email_Manager::trigger_confirmation_email
		if ( ! in_array( $booking_id, $ids_pending_confirmation_email ) ) {
			$ids_pending_confirmation_email[] = $booking_id;
			set_transient( 'wc_booking_confirmation_email_send_ids', $ids_pending_confirmation_email, 0 );
		}
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	public function trigger( $booking_id ) {

		$booking_ids = get_transient( 'wc_booking_confirmation_email_send_ids' );
		if ( ! in_array( $booking_id, (array) $booking_ids ) ) {
			return;
		}

		if ( $booking_id ) {
			$this->object = get_wc_booking( $booking_id );

			if ( ! is_object( $this->object ) ) {
				return;
			}

			foreach ( array( '{product_title}', '{order_date}', '{order_number}' ) as $key ) {
				$key = array_search( $key, $this->find );
				if ( false !== $key ) {
					unset( $this->find[ $key ] );
					unset( $this->replace[ $key ] );
				}
			}

			$this->find[]    = '{product_title}';
			$this->replace[] = $this->object->get_product()->get_title();

			if ( $this->object->get_order() ) {
				if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
					$billing_email = $this->object->get_order()->billing_email;
					$order_date = $this->object->get_order()->order_date;
				} else {
					$billing_email = $this->object->get_order()->get_billing_email();
					$order_date = $this->object->get_order()->get_date_created() ? $this->object->get_order()->get_date_created()->date( 'Y-m-d H:i:s' ) : '';
				}

				$this->find[]    = '{order_date}';
				$this->replace[] = date_i18n( wc_date_format(), strtotime( $order_date ) );

				$this->find[]    = '{order_number}';
				$this->replace[] = $this->object->get_order()->get_order_number();

				$this->recipient = $billing_email;
			} else {
				$this->find[]    = '{order_date}';
				$this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->booking_date ) );

				$this->find[]    = '{order_number}';
				$this->replace[] = __( 'N/A', 'ultimatewoo-pro' );

				if ( $this->object->customer_id && ( $customer = get_user_by( 'id', $this->object->customer_id ) ) ) {
					$this->recipient = $customer->user_email;
				}
			}
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		wc_get_template( $this->template_html, array(
			'booking' 		=> $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => false
		), 'woocommerce-bookings/', $this->template_base );
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template( $this->template_plain, array(
			'booking' 		=> $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => true
		), 'woocommerce-bookings/', $this->template_base );
		return ob_get_clean();
	}

    /**
     * Initialise Settings Form Fields
     *
     * @access public
     * @return void
     */
    public function init_form_fields() {
    	$this->form_fields = array(
			'enabled' => array(
				'title' 		=> __( 'Enable/Disable', 'ultimatewoo-pro' ),
				'type' 			=> 'checkbox',
				'label' 		=> __( 'Enable this email notification', 'ultimatewoo-pro' ),
				'default' 		=> 'yes'
			),
			'subject' => array(
				'title' 		=> __( 'Subject', 'ultimatewoo-pro' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'ultimatewoo-pro' ), $this->subject ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'heading' => array(
				'title' 		=> __( 'Email Heading', 'ultimatewoo-pro' ),
				'type' 			=> 'text',
				'description' 	=> sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'ultimatewoo-pro' ), $this->heading ),
				'placeholder' 	=> '',
				'default' 		=> ''
			),
			'email_type' => array(
				'title' 		=> __( 'Email type', 'ultimatewoo-pro' ),
				'type' 			=> 'select',
				'description' 	=> __( 'Choose which format of email to send.', 'ultimatewoo-pro' ),
				'default' 		=> 'html',
				'class'			=> 'email_type',
				'options'		=> array(
					'plain'		 	=> __( 'Plain text', 'ultimatewoo-pro' ),
					'html' 			=> __( 'HTML', 'ultimatewoo-pro' ),
					'multipart' 	=> __( 'Multipart', 'ultimatewoo-pro' ),
				)
			)
		);
    }
}

return new WC_Email_Booking_Confirmed();
