<?php
/**
 * WooCommerce PDF Product Vouchers
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce PDF Product Vouchers to newer
 * versions in the future. If you wish to customize WooCommerce PDF Product Vouchers for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-pdf-product-vouchers/ for more information.
 *
 * @package   WC-PDF-Product-Vouchers/Emails
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Voucher Recipient Email
 *
 * Voucher recipient emails are sent to any voucher recipient email addresses
 * that were provided by the customer when configuring the voucher/adding to
 * cart.
 *
 * @since 1.2.0
 */
class WC_PDF_Product_Vouchers_Email_Voucher_Recipient extends WC_Email {


	/** @var string optional voucher recipient message */
	private $message;

	/** @var string optional voucher recipient name */
	private $recipient_name;

	/** @var string heading for email containing multiple vouchers */
	private $heading_multiple;

	/** @var string subject for email containing multiple vouchers */
	private $subject_multiple;


	/**
	 * Constructor
	 *
	 * @since 1.2.0
	 */
	public function __construct() {

		$this->id          = 'wc_pdf_product_vouchers_voucher_recipient';
		$this->title       = __( 'Voucher Recipient', 'ultimatewoo-pro' );
		$this->description = __( 'Sent to a voucher recipient email address provided by the customer when adding a voucher product to the cart.', 'ultimatewoo-pro' );

		$this->heading     = $this->get_option( 'heading', __( 'You have received a voucher', 'ultimatewoo-pro' ) );
		$this->subject     = $this->get_option( 'subject', __( 'You have received a voucher from {purchaser_name}', 'ultimatewoo-pro' ) );

		$this->template_html  = 'emails/voucher-recipient.php';
		$this->template_plain = 'emails/plain/voucher-recipient.php';

		$this->template_base  = wc_pdf_product_vouchers()->get_plugin_path() . '/templates/';

		// triggers for this email
		if ( 'yes' == get_option( 'woocommerce_downloads_grant_access_after_payment' ) ) {
			add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'trigger' ) );
		}

		add_action( 'woocommerce_order_status_completed_notification', array( $this, 'trigger' ) );

		$this->heading_multiple = $this->get_option( 'heading_multiple', __( 'You have received vouchers', 'ultimatewoo-pro' ) );
		$this->subject_multiple = $this->get_option( 'subject_multiple', __( 'You have received vouchers from {billing_first_name} {billing_last_name}', 'ultimatewoo-pro' ) );

		parent::__construct();
	}


	/**
	 * Dispatches the email(s)
	 *
	 * Can be triggered either for an order or a specific voucher.
	 *
	 * In 3.0.0 changed param $order_id to $object_id
	 *
	 * @since 1.2.0
	 * @param int $object_id order or voucher identifier
	 */
	public function trigger( $object_id ) {

		// nothingtodohere
		if ( ! $object_id || ! $this->is_enabled() ) {
			return;
		}

		if ( 'shop_order' === get_post_type( $object_id ) ) {
			$this->trigger_order( $object_id );
		} else {
			$this->trigger_voucher( $object_id );
		}

	}


	/**
	 * Sends the vouchers that are attached to an order
	 *
	 * @since 3.0.0
	 * @param int $order_id order identifier
	 */
	public function trigger_order( $order_id ) {

		// only dispatch the voucher recipient email once, unless we're being called from the Voucher Recipient email order action
		if ( get_post_meta( $order_id, '_wc_pdf_product_vouchers_voucher_recipient_email_sent', true ) &&
			! ( isset( $_POST['wc_order_action'] ) && 'send_email_wc_pdf_product_vouchers_voucher_recipient' == $_POST['wc_order_action'] ) ) {
			return;
		}

		$order    = wc_get_order( $order_id );
		$vouchers = WC_PDF_Product_Vouchers_Order::get_vouchers( $order );

		if ( ! $order || empty( $vouchers ) ) {
			return;
		}

		// kept here for backwards compatibilty, use `{purchaser_name} instead`
		$this->find[]    = '{billing_first_name}';
		$this->replace[] = SV_WC_Order_Compatibility::get_prop( $order, 'billing_first_name' );

		$this->find[]    = '{billing_last_name}';
		$this->replace[] = SV_WC_Order_Compatibility::get_prop( $order, 'billing_last_name' );

		$this->find[]    = '{purchaser_name}';
		$this->replace[] = sprintf( '%s %s', SV_WC_Order_Compatibility::get_prop( $order, 'billing_first_name' ), SV_WC_Order_Compatibility::get_prop( $order, 'billing_last_name' ) );

		$purchaser_name_key = array_search( '{purchaser_name}', $this->find );

		// For each voucher item in this order, if it contains a recipient email,
		// add the voucher to those being sent to that recipient.
		// For each voucher recipient, send an email with any and all vouchers.
		$recipient_emails = array();

		foreach ( $vouchers as $voucher ) {

			if ( $voucher->get_recipient_email() && $voucher->file_exists() && $voucher->has_status( 'active' ) ) {

				if ( ! isset( $recipient_emails[ $voucher->get_recipient_email() ] ) ) {
					$recipient_emails[ $voucher->get_recipient_email() ] = array(
						'vouchers'       => array(),
						'message'        => '',
						'recipient_name' => $voucher->get_recipient_name(),
						'purchaser_name' => $voucher->get_purchaser_name(),
					);
				}

				$recipient_emails[ $voucher->get_recipient_email() ]['vouchers'][] = $voucher;

				// message to the recipient?
				if ( $voucher->get_message() ) {

					if ( '' === $recipient_emails[ $voucher->get_recipient_email() ]['message'] ) {

						$recipient_emails[ $voucher->get_recipient_email() ]['message'] = $voucher->get_message();

					} elseif ( $recipient_emails[ $voucher->get_recipient_email() ]['message'] != $voucher->get_message() ) {

						// Guard against the admitedly edge case of multiple vouchers with different messages
						// being sent to the same recipient, by just not displaying a message. Cause it would
						// probably look odd to have a bunch of different messages in the same email.
						$recipient_emails[ $voucher->get_recipient_email() ]['message'] = null;
					}

				}
			}
		}

		foreach ( $recipient_emails as $recipient_email => $data ) {

			$this->object         = array( 'order' => $order, 'recipient_email' => $recipient_email, 'voucher_count' => count( $data['vouchers'] ), 'vouchers' => $data['vouchers'] );
			$this->message        = $data['message'];
			$this->recipient_name = $data['recipient_name'];
			$this->recipient      = $recipient_email;

			// update purchaser name in replacements
			$this->replace[ $purchaser_name_key ] = $data['purchaser_name'];

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		// record the fact that the vouchers have been sent
		SV_WC_Order_Compatibility::update_meta_data( $order, '_wc_pdf_product_vouchers_voucher_recipient_email_sent', true );
	}


	/**
	 * Sends a voucher
	 *
	 * @since 3.0.0
	 * @param int $voucher_id voucher identifier
	 */
	public function trigger_voucher( $voucher_id ) {

		$voucher = wc_pdf_product_vouchers_get_voucher( $voucher_id );

		if ( ! $voucher ) {
			return;
		}

		$this->find[]    = '{purchaser_name}';
		$this->replace[] = $voucher->get_purchaser_name();

		$this->object         = array( 'order' => null, 'recipient_email' => $voucher->get_recipient_email(), 'voucher_count' => 1, 'vouchers' => array( $voucher ) );
		$this->message        = $voucher->get_message();
		$this->recipient_name = $voucher->get_recipient_name();
		$this->recipient      = $voucher->get_recipient_email();

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}


	/**
	 * Returns the email subject
	 *
	 * @since 1.2.0
	 * @see WC_Email::get_subject()
	 * @return string email subject
	 */
	public function get_subject() {
		if ( 1 == $this->object['voucher_count'] ) {
			return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $this->subject ), $this->object );
		} else {
			return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $this->subject_multiple ), $this->object );
		}
	}


	/**
	 * Returns the email heading
	 *
	 * @see WC_Email::get_heading()
	 *
	 * @since 1.2.0
	 * @return string email heading
	 */
	public function get_heading() {
		if ( 1 == $this->object['voucher_count'] ) {
			return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $this->heading ), $this->object );
		} else {
			return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $this->heading_multiple ), $this->object );
		}
	}


	/**
	 * Returns the email HTML content
	 *
	 * @since 1.2.0
	 * @return string the email HTML content
	 */
	public function get_content_html() {
		ob_start();
		wc_get_template(
			$this->template_html,
			array(
				'order'          => $this->object,
				'email_heading'  => $this->get_heading(),
				'voucher_count'  => $this->object['voucher_count'],
				'message'        => $this->message,
				'recipient_name' => $this->recipient_name,
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}


	/**
	 * Returns the email plain content
	 *
	 * @since 1.2.0
	 * @return string the email plain content
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template(
			$this->template_plain,
			array(
				'order'          => $this->object,
				'email_heading'  => $this->get_heading(),
				'voucher_count'  => $this->object['voucher_count'],
				'message'        => $this->message,
				'recipient_name' => $this->recipient_name,
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}

	/**
	 * Initializes Settings Form Fields
	 *
	 * @since 1.2.0
	 */
	public function init_form_fields() {

		$this->form_fields = array(

			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'ultimatewoo-pro' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'ultimatewoo-pro' ),
				'default' => 'yes',
			),

			'subject' => array(
				'title'       => __( 'Subject', 'ultimatewoo-pro' ),
				'type'        => 'text',
				/* translators: %s - default email subject */
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'ultimatewoo-pro' ), $this->subject ),
				'placeholder' => '',
				'default'     => '',
			),

			'subject_multiple' => array(
				'title'       => __( 'Subject Multiple', 'ultimatewoo-pro' ),
				'type'        => 'text',
				/* translators: %s - default email subject */
				'description' => sprintf( __( 'This controls the email subject line when the email contains more than one voucher. Leave blank to use the default subject: <code>%s</code>.', 'ultimatewoo-pro' ), $this->subject_multiple ),
				'placeholder' => '',
				'default'     => '',
			),

			'heading' => array(
				'title'       => __( 'Email Heading', 'ultimatewoo-pro' ),
				'type'        => 'text',
				/* translators: %s - default email heading */
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'ultimatewoo-pro' ), $this->heading ),
				'placeholder' => '',
				'default'     => '',
			),

			'heading_multiple' => array(
				'title'       => __( 'Email Heading Multiple', 'ultimatewoo-pro' ),
				'type'        => 'text',
				/* translators: %s - default email heading */
				'description' => sprintf( __( 'This controls the main heading contained within the email notification when the email contains more than one voucher. Leave blank to use the default heading: <code>%s</code>.', 'ultimatewoo-pro' ), $this->heading_multiple ),
				'placeholder' => '',
				'default'     => '',
			),

			'email_type' => array(
				'title'       => __( 'Email type', 'ultimatewoo-pro' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'ultimatewoo-pro' ),
				'default'     => 'html',
				'class'       => 'email_type',
				'options' => array(
					'plain'     => __( 'Plain text', 'ultimatewoo-pro' ),
					'html'      => __( 'HTML', 'ultimatewoo-pro' ),
					'multipart' => __( 'Multipart', 'ultimatewoo-pro' ),
				),
			),
		);
	}
}
