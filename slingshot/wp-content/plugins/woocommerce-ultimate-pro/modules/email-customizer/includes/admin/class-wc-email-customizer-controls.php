<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Email_Customizer_Send_Email_Control extends WP_Customize_Control {

	public function render_content() {
		global $current_user;

		$output = '';
		$output .= '<div class="control-wrap">' . PHP_EOL;
		$output .= '<p><input type="text" name="send_test_email_to" value="' . esc_attr( $current_user->user_email ) . '" /></p>' . PHP_EOL;
		$output .= '<a href="#" title="' . __( 'Send Test Email', 'ultimatewoo-pro' ) . '" class="wc-email-customizer-send-email button">' . __( 'Send Test Email', 'ultimatewoo-pro' ) . '</a>' . PHP_EOL;
		$output .= '</div>' . PHP_EOL;

		echo $output;
	}
}

class WC_Email_Customizer_Controls {

	private function __construct() {}

	public static function add_controls() {
		global $wp_customize;

		/**
		 * header
		 *
		 *
		 */
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'wc_email_header_image_control', array(
			'label'      => __( 'Upload a Header', 'ultimatewoo-pro' ),
			'priority'   => 10,
			'section'    => 'wc_email_header',
			'settings'   => 'woocommerce_email_header_image',
			'context'    => 'woocommerce-email-customizer',
		) ) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wc_email_header_color_control', array(
			'label'     => __( 'Header Background Color', 'ultimatewoo-pro' ),
			'priority'  => 30,
			'section'   => 'wc_email_header',
			'settings'  => 'woocommerce_email_header_background_color',
		) ) );

		$wp_customize->add_control( 'wc_email_header_font_size_control', array(
			'type'        => 'range',
			'priority'    => 50,
			'section'     => 'wc_email_header',
			'label'       => __( 'Font Size', 'ultimatewoo-pro' ),
			'description' => __( 'Font Size', 'ultimatewoo-pro' ),
			'settings'    => 'woocommerce_email_header_font_size',
			'input_attrs' => array(
				'min'   => 15,
				'max'   => 50,
				'step'  => 1,
			),
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wc_email_header_text_color_control', array(
			'label'     => __( 'Header Text Color', 'ultimatewoo-pro' ),
			'priority'  => 40,
			'section'   => 'wc_email_header',
			'settings'  => 'woocommerce_email_header_text_color',
		) ) );

		/**
		 * body
		 *
		 *
		 */
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wc_email_bg_color_control', array(
			'label'     => __( 'Background Color', 'ultimatewoo-pro' ),
			'priority'  => 10,
			'section'   => 'wc_email_body',
			'settings'  => 'woocommerce_email_background_color',
		) ) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wc_email_body_bg_color_control', array(
			'label'     => __( 'Content Background Color', 'ultimatewoo-pro' ),
			'priority'  => 30,
			'section'   => 'wc_email_body',
			'settings'  => 'woocommerce_email_body_background_color',
		) ) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wc_email_link_color_control', array(
			'label'     => __( 'Link Color', 'ultimatewoo-pro' ),
			'priority'  => 50,
			'section'   => 'wc_email_body',
			'settings'  => 'woocommerce_email_link_color',
		) ) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wc_email_body_text_color_control', array(
			'label'     => __( 'Text Color', 'ultimatewoo-pro' ),
			'priority'  => 70,
			'section'   => 'wc_email_body',
			'settings'  => 'woocommerce_email_body_text_color',
		) ) );

		$wp_customize->add_control( 'wc_email_body_font_size_control', array(
			'type'        => 'range',
			'priority'    => 90,
			'section'     => 'wc_email_body',
			'label'       => __( 'Font Size', 'ultimatewoo-pro' ),
			'description' => __( 'Font Size', 'ultimatewoo-pro' ),
			'settings'    => 'woocommerce_email_body_font_size',
			'input_attrs' => array(
				'min'   => 10,
				'max'   => 50,
				'step'  => 1,
			),
		) );

		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'wc_email_width_control', array(
			'label'          => __( 'Email Width', 'ultimatewoo-pro' ),
			'priority'       => 130,
			'section'        => 'wc_email_body',
			'settings'       => 'woocommerce_email_width',
			'type'           => 'select',
			'choices'        => array(
				'500' => __( 'Narrow', 'ultimatewoo-pro' ),
				'600' => __( 'Default', 'ultimatewoo-pro' ),
				'700' => __( 'Wide', 'ultimatewoo-pro' )
			),
		) ) );

		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'wc_email_font_family_control', array(
			'label'          => __( 'Font Family', 'ultimatewoo-pro' ),
			'priority'       => 150,
			'section'        => 'wc_email_body',
			'settings'       => 'woocommerce_email_font_family',
			'type'           => 'select',
			'choices'        => array(
				'sans-serif' => __( 'Sans Serif', 'ultimatewoo-pro' ),
				'serif'      => __( 'Serif', 'ultimatewoo-pro' ),
			),
		) ) );

		$wp_customize->add_control( 'wc_email_rounded_corners_control', array(
			'type'        => 'range',
			'priority'    => 170,
			'section'     => 'wc_email_body',
			'label'       => __( 'Rounded Corners', 'ultimatewoo-pro' ),
			'description' => __( 'Rounded corners', 'ultimatewoo-pro' ),
			'settings'    => 'woocommerce_email_rounded_corners',
			'input_attrs' => array(
				'min'   => 0,
				'max'   => 50,
				'step'  => 1,
			),
		) );

		$wp_customize->add_control( 'wc_email_box_shadow_spread_control', array(
			'type'        => 'range',
			'priority'    => 190,
			'section'     => 'wc_email_body',
			'label'       => __( 'Shadow Spread', 'ultimatewoo-pro' ),
			'description' => __( 'Amount of shadow behind email', 'ultimatewoo-pro' ),
			'settings'    => 'woocommerce_email_box_shadow_spread',
			'input_attrs' => array(
				'min'   => -5,
				'max'   => 10,
				'step'  => 1,
			),
		) );

		/**
		 * footer
		 *
		 *
		 */
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'wc_email_footer_text_control', array(
			'label'          => __( 'Footer Text', 'ultimatewoo-pro' ),
			'priority'       => 10,
			'section'        => 'wc_email_footer',
			'settings'       => 'woocommerce_email_footer_text',
			'type'           => 'text',
		) ) );

		$wp_customize->add_control( 'wc_email_footer_font_size_control', array(
			'type'        => 'range',
			'priority'    => 30,
			'section'     => 'wc_email_footer',
			'label'       => __( 'Font Size', 'ultimatewoo-pro' ),
			'description' => __( 'Font Size', 'ultimatewoo-pro' ),
			'settings'    => 'woocommerce_email_footer_font_size',
			'input_attrs' => array(
				'min'   => 10,
				'max'   => 50,
				'step'  => 1,
			),
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'wc_email_footer_text_color_control', array(
			'label'     => __( 'Text Color', 'ultimatewoo-pro' ),
			'priority'  => 50,
			'section'   => 'wc_email_footer',
			'settings'  => 'woocommerce_email_footer_text_color',
		) ) );

		$wp_customize->add_control( new WC_Email_Customizer_Send_Email_Control( $wp_customize, 'wc_email_send_email_control', array(
			'priority'    => 10,
			'section'     => 'wc_email_send',
			'label'       => __( 'Send Test Email', 'ultimatewoo-pro' ),
			'description' => __( 'Send Test Email', 'ultimatewoo-pro' ),
			'settings'    => 'woocommerce_email_send',
		) ) );
	}
}
