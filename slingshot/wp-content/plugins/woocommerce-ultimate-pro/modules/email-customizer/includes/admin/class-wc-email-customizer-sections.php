<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Email_Customizer_Sections {

	private function __construct() {}

	public static function add_sections() {
		global $wp_customize;

		// add sections
		$wp_customize->add_section( 'wc_email_header', array (
			'title'      => __( 'Email Header', 'ultimatewoo-pro' ),
			'capability' => 'edit_theme_options',
			'priority'   => 10,
		) );

		$wp_customize->add_section( 'wc_email_body', array (
			'title'      => __( 'Email Body', 'ultimatewoo-pro' ),
			'capability' => 'edit_theme_options',
			'priority'   => 30,
		) );

		$wp_customize->add_section( 'wc_email_footer', array (
			'title'      => __( 'Email Footer', 'ultimatewoo-pro' ),
			'capability' => 'edit_theme_options',
			'priority'   => 50,
		) );

		$wp_customize->add_section( 'wc_email_send', array (
			'title'      => __( 'Send Test Email', 'ultimatewoo-pro' ),
			'capability' => 'edit_theme_options',
			'priority'   => 70,
		) );
	}
}