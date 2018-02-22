<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Store_Credit_Plus_Admin class
 */
class WC_Store_Credit_Plus_Admin {

	private $settings;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_settings();
		add_action( 'admin_menu', array( $this, 'send_credit_admin_menu' ), 10 );
		add_action( 'woocommerce_settings_general_options_after', array( $this, 'admin_settings' ) );
		add_action( 'woocommerce_update_options_general', array( $this, 'save_admin_settings' ) );
	}

	/**
	 * init settings
	 */
	private function init_settings() {
		$this->settings = array(
			array(
				'name' => __( 'Store Credit', 'ultimatewoo-pro' ),
				'type' => 'title',
				'desc' => __( 'The following options are specific to store credit coupons.', 'ultimatewoo-pro' ),
				'id'   => 'store_credit_options'
			),
			array(
				'name'    => __( 'My Account', 'ultimatewoo-pro' ),
				'desc'    => __( 'Display store credit on the My Account page.', 'ultimatewoo-pro' ),
				'id'      => 'woocommerce_store_credit_show_my_account',
				'type'    => 'checkbox',
				'default' => 'yes'
			),
			array(
				'name'    => __( 'Delete after use', 'ultimatewoo-pro' ),
				'desc'    => __( 'When the credit is used up, delete the coupon.', 'ultimatewoo-pro' ),
				'id'      => 'woocommerce_delete_store_credit_after_usage',
				'type'    => 'checkbox',
				'default' => 'yes'
			),
			array(
				'name'          => __( 'Default coupon options', 'ultimatewoo-pro' ),
				'desc'          => __( 'Store Credit coupons applied before tax', 'ultimatewoo-pro' ),
				'id'            => 'woocommerce_store_credit_apply_before_tax',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => 'start'
			),
			array(
				'desc'          => __( 'Individual use', 'ultimatewoo-pro' ),
				'id'            => 'woocommerce_store_credit_individual_use',
				'type'          => 'checkbox',
				'default'       => 'no',
				'checkboxgroup' => ''
			),
			array(
				'type' => 'sectionend',
				'id'   => 'store_credit_options'
			),
		);
	}

	/**
	 * Add admin menu item
	 */
	public function send_credit_admin_menu() {
		$page = add_submenu_page( 'woocommerce', __( 'Send Store Credit', 'ultimatewoo-pro' ),  __( 'Send Store Credit', 'ultimatewoo-pro' ) , 'manage_woocommerce', 'send-store-credit', array( $this, 'send_credit_admin' ) );

		if ( function_exists( 'woocommerce_admin_css' ) ) {
			add_action( 'admin_print_styles-'. $page, 'woocommerce_admin_css' );
		}
	}

	/**
	 * Generate a store credit coupon
	 *
	 * @param  string $email
	 * @param  float $amount
	 * @return string new coupon code
	 */
	public function generate_store_credit( $email, $amount ) {
		$coupon_code   = uniqid( sanitize_title( $email ) );
		$new_credit_id = wp_insert_post( array(
			'post_title' => $coupon_code,
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type'  => 'shop_coupon'
		) );

		// Add meta
		update_post_meta( $new_credit_id, 'discount_type', 'store_credit' );
		update_post_meta( $new_credit_id, 'coupon_amount', $amount );
		update_post_meta( $new_credit_id, 'individual_use', get_option( 'woocommerce_store_credit_individual_use', 'no' ) );
		update_post_meta( $new_credit_id, 'product_ids', '' );
		update_post_meta( $new_credit_id, 'exclude_product_ids', '' );
		update_post_meta( $new_credit_id, 'usage_limit', '' );
		update_post_meta( $new_credit_id, 'expiry_date', '' );
		update_post_meta( $new_credit_id, 'apply_before_tax', get_option( 'woocommerce_store_credit_apply_before_tax', 'no' ) );
		update_post_meta( $new_credit_id, 'free_shipping', 'no' );

		// Meta for coupon owner
		update_post_meta( $new_credit_id, 'customer_email', array( $email ) );

		return $coupon_code;
	}

	/**
	 * Admin interface for emailing a credit
	 */
	public function send_credit_admin() {
		if ( isset( $_POST['store_credit_email_address'] ) ) {

			$email  = wc_clean( $_POST['store_credit_email_address'] );
			$amount = wc_clean( $_POST['store_credit_amount'] );

			if ( ! $email || ! is_email( $email ) ) {
				echo '<div id="message" class="error fade"><p><strong>' . __( 'Invalid email address.', 'ultimatewoo-pro' ) . '</strong></p></div>';
			} elseif ( ! $amount || !is_numeric( $amount ) ) {
				echo '<div id="message" class="error fade"><p><strong>' . __( 'Invalid amount.', 'ultimatewoo-pro' ) . '</strong></p></div>';
			} else {

				$code = $this->generate_store_credit( $email, $amount );

				$this->email_store_credit( $email, $code, $amount );

				echo '<div id="message" class="updated fade"><p><strong>' . __( 'Store credit sent.', 'ultimatewoo-pro' ) . '</strong></p></div>';
			}
		}

		include( 'views/html-admin-send-credit.php' );
	}

	/**
	 * Show admin fields
	 */
	public function admin_settings() {
		woocommerce_admin_fields( $this->settings );
	}

	/**
	 * Save admin fields
	 */
	public function save_admin_settings() {
		woocommerce_update_options( $this->settings );
	}

	/**
	 * Email the credit
	 * @param  string $email
	 * @param  string $coupon_code
	 * @param  float $amount
	 */
	public function email_store_credit( $email, $coupon_code, $amount ) {
		$mailer        = WC()->mailer();
		$blogname      = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$subject       = apply_filters( 'woocommerce_email_subject_store_credit', sprintf( '[%s] %s', $blogname, __( 'Store Credit', 'woocommerce' ) ), $email, $coupon_code, $amount );
		$email_heading =  sprintf( __( 'You have been given %s credit ', 'ultimatewoo-pro' ), wc_price( $amount ) );

		// Buffer
		ob_start();

		include apply_filters( 'woocommerce_email_template_store_credit', WC_STORE_CREDIT_PLUGIN_DIR . '/templates/customer-store-credit.php' );

		// Get contents
		$message = ob_get_clean();

		wc_mail( $email, $subject, $message );
	}
}

new WC_Store_Credit_Plus_Admin();
