<?php
/**
 * Exit if accesses directly
 */
defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Pie_WCWL_Waitlist_Mailout' ) ) {
	/**
	 * Waitlist Mailout
	 *
	 * An email sent to the admin when a new order is received/paid for.
	 *
	 * @class    Pie_WCWL_Waitlist_Mailout
	 * @extends  WC_Email
	 */
	class Pie_WCWL_Waitlist_Mailout extends WC_Email {

		/**
		 * Hooks up the functions for Waitlist Mailout
		 *
		 * @access public
		 */
		public function __construct() {
			// Init
			$this->wcwl_setup_mailout();
			// Triggers for this email
			add_action( 'wcwl_mailout_send_email', array( $this, 'trigger' ), 10, 2 );
			// Call parent constructor
			parent::__construct();
		}

		/**
		 * Setup required variables for mailout class
		 *
		 * @access public
		 * @return void
		 */
		public function wcwl_setup_mailout() {
			$this->id             = WCWL_SLUG . '_mailout';
			$this->title          = __( 'Waitlist Mailout', 'ultimatewoo-pro' );
			$this->description    = __( 'When a product changes from being Out-of-Stock to being In-Stock, this email is sent to all users registered on the waitlist for that product.', 'ultimatewoo-pro' );
			$this->heading        = __( '{product_title} is now back in stock at {blogname}', 'ultimatewoo-pro' );
			$this->subject        = __( 'A product you are waiting for is back in stock', 'ultimatewoo-pro' );
			$this->template_base  = WooCommerce_Waitlist_Plugin::$path . 'templates/emails/';
			$this->template_html  = 'waitlist-mailout.php';
			$this->template_plain = 'plain/waitlist-mailout.php';
		}

		/**
		 * Trigger function for the mailout class
		 *
		 * @param int $user_id    ID of user to send the mail to
		 * @param int $product_id ID of product that email refers to
		 *
		 * @access public
		 * @return void
		 */
		public function trigger( $user_id, $product_id ) {
			if ( ! is_numeric( $user_id ) || ! is_numeric( $product_id ) ) {
				return;
			}
			$user            = get_user_by( 'id', $user_id );
			$this->object    = wc_get_product( $product_id );
			$this->recipient = $user->user_email;
			$this->find[]    = '{product_title}';
			$this->replace[] = $this->object->get_title();
			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}
			$result = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			if ( $result && 'yes' == get_option( 'woocommerce_waitlist_archive_on' ) ) {
				$this->add_user_to_archive( $product_id, $user->ID );
			}
		}

		/**
		 * Add a user to the archive for the current product
		 * This occurs when the user has been emailed and appends their ID to the list of users emailed today
		 *
		 * @param $product_id
		 * @param $user_id
		 */
		public function add_user_to_archive( $product_id, $user_id ) {
			$existing_archives = get_post_meta( $product_id, 'wcwl_waitlist_archive', true );
			$today             = strtotime( date( "Ymd" ) );
			if ( ! isset( $existing_archives[$today] ) ) {
				$existing_archives[$today] = array();
			}
			$existing_archives[$today][]  = $user_id;
			update_post_meta( $product_id, 'wcwl_waitlist_archive', $existing_archives );
		}

		/**
		 * Returns the html string needed to create an email to send out to user
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_html() {
			ob_start();
			Pie_WCWL_Compatibility::get_template( $this->template_html, array(
				'product_title' => $this->object->get_title(),
				'product_link'  => get_permalink( Pie_WCWL_Compatibility::get_product_id( $this->object ) ),
				'email_heading' => $this->get_heading(),
				'product_id'    => Pie_WCWL_Compatibility::get_product_id( $this->object ),
			), false, $this->template_base );

			return ob_get_clean();
		}

		/**
		 * Returns the plain text needed to create an email to send out to user
		 *
		 * @access public
		 * @return string
		 */
		public function get_content_plain() {
			ob_start();
			Pie_WCWL_Compatibility::get_template( $this->template_plain, array(
				'product_title' => $this->object->get_title(),
				'product_link'  => get_permalink( Pie_WCWL_Compatibility::get_product_id( $this->object ) ),
				'email_heading' => $this->get_heading(),
				'product_id'    => Pie_WCWL_Compatibility::get_product_id( $this->object ),
			), false, $this->template_base );

			return ob_get_clean();
		}
	}
}
return new Pie_WCWL_Waitlist_Mailout();