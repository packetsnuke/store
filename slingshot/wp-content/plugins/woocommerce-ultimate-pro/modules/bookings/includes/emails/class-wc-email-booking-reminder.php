<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking reminder
 *
 * An email sent to the user when a new booking is upcoming.
 *
 * @class 		WC_Email_Booking_Reminder
 * @extends 	WC_Email
 */
class WC_Email_Booking_Reminder extends WC_Email {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id 				= 'booking_reminder';
		$this->title 			= __( 'Booking Reminder', 'ultimatewoo-pro' );
		$this->description		= __( 'Booking reminders are sent to the customer to remind them of an upcoming booking.', 'ultimatewoo-pro' );

		$this->heading 			= __( 'Booking Reminder', 'ultimatewoo-pro' );
		$this->subject      	= __( '[{blogname}] A reminder about your booking of "{product_title}"', 'ultimatewoo-pro' );
		$this->customer_email   = true;
		$this->template_html 	= 'emails/customer-booking-reminder.php';
		$this->template_plain 	= 'emails/plain/customer-booking-reminder.php';

		// Call parent constructor
		parent::__construct();

		// Other settings
		$this->template_base = WC_BOOKINGS_TEMPLATE_PATH;
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	public function trigger( $booking_id ) {
		if ( $booking_id  ) {
			$this->object = get_wc_booking( $booking_id );

			if ( ! is_object( $this->object ) || ! $this->object->get_order() ) {
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

		if ( ! $this->is_enabled() || ! $this->get_recipient() )
			return;

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
			'email_heading' => $this->get_heading()
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
			'email_heading' => $this->get_heading()
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

return new WC_Email_Booking_Reminder();
