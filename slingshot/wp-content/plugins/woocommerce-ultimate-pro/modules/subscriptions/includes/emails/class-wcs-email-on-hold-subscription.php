<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Suspended Subscription Email
 *
 * An email sent to the admin when a subscription is expired.
 *
 * @class 	WCS_Email_On_Hold_Subscription
 * @version	2.1
 * @package	WooCommerce_Subscriptions/Classes/Emails
 * @author 	Prospress
 * @extends WC_Email
 */
class WCS_Email_On_Hold_Subscription extends WC_Email {

	/**
	 * Create an instance of the class.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {

		$this->id          = 'suspended_subscription';
		$this->title       = __( 'Suspended Subscription', 'ultimatewoo-pro' );
		$this->description = __( 'Suspended Subscription emails are sent when a customer manually suspends their subscription.', 'ultimatewoo-pro' );

		$this->heading     = __( 'Subscription Suspended', 'ultimatewoo-pro' );
		// translators: placeholder is {blogname}, a variable that will be substituted when email is sent out
		$this->subject     = sprintf( _x( '[%s] Subscription Suspended', 'default email subject for suspended emails sent to the admin', 'ultimatewoo-pro' ), '{blogname}' );

		$this->template_html  = 'emails/on-hold-subscription.php';
		$this->template_plain = 'emails/plain/on-hold-subscription.php';
		$this->template_base  = plugin_dir_path( WC_Subscriptions::$plugin_file ) . 'templates/';

		add_action( 'on-hold_subscription_notification', array( $this, 'trigger' ) );

		parent::__construct();

		$this->recipient = $this->get_option( 'recipient' );

		if ( ! $this->recipient ) {
			$this->recipient = get_option( 'admin_email' );
		}
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @return void
	 */
	function trigger( $subscription ) {
		$this->object = $subscription;

		if ( ! is_object( $subscription ) ) {
			throw new InvalidArgumentException( __( 'Subscription argument passed in is not an object.', 'ultimatewoo-pro' ) );
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
	function get_content_html() {
		ob_start();
		wc_get_template(
			$this->template_html,
			array(
				'subscription'  => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => false,
				'email'         => $this,
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		ob_start();
		wc_get_template(
			$this->template_plain,
			array(
				'subscription'  => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => true,
				'email'         => $this,
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}

	/**
	 * Initialise Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'         => _x( 'Enable/Disable', 'an email notification', 'ultimatewoo-pro' ),
				'type'          => 'checkbox',
				'label'         => __( 'Enable this email notification', 'ultimatewoo-pro' ),
				'default'       => 'no',
			),
			'recipient' => array(
				'title'         => _x( 'Recipient(s)', 'of an email', 'ultimatewoo-pro' ),
				'type'          => 'text',
				// translators: placeholder is admin email
				'description'   => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'ultimatewoo-pro' ), esc_attr( get_option( 'admin_email' ) ) ),
				'placeholder'   => '',
				'default'       => '',
			),
			'subject' => array(
				'title'         => _x( 'Subject', 'of an email', 'ultimatewoo-pro' ),
				'type'          => 'text',
				'description'   => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'ultimatewoo-pro' ), $this->subject ),
				'placeholder'   => '',
				'default'       => '',
			),
			'heading' => array(
				'title'         => _x( 'Email Heading', 'Name the setting that controls the main heading contained within the email notification', 'ultimatewoo-pro' ),
				'type'          => 'text',
				'description'   => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'ultimatewoo-pro' ), $this->heading ),
				'placeholder'   => '',
				'default'       => '',
			),
			'email_type' => array(
				'title'         => _x( 'Email type', 'text, html or multipart', 'ultimatewoo-pro' ),
				'type'          => 'select',
				'description'   => __( 'Choose which format of email to send.', 'ultimatewoo-pro' ),
				'default'       => 'html',
				'class'         => 'email_type',
				'options'       => array(
					'plain'         => _x( 'Plain text', 'email type', 'ultimatewoo-pro' ),
					'html'          => _x( 'HTML', 'email type', 'ultimatewoo-pro' ),
					'multipart'     => _x( 'Multipart', 'email type', 'ultimatewoo-pro' ),
				),
			),
		);
	}
}
