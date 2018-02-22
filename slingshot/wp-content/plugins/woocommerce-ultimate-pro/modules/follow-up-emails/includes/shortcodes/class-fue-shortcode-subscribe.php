<?php
/**
 * Definition of the `fue_subscribe` shortcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( 'FUE_Shortcode_Subscribe' ) ):
class FUE_Shortcode_Subscribe {

	/**
	 * Constructor. Register the `fue_subscribe` shortcode
	 */
	public function __construct() {
		add_shortcode( 'fue_subscribe', 'FUE_Shortcode_Subscribe::render' );
	}

	/**
	 * Render the subscription form.
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function render( $atts ) {
		$default = array(
			'label_email'       => __( 'Email:', 'ultimatewoo-pro' ),
			'placeholder_email' => 'email@example.org',
			'label_first_name'  => __( 'First name:', 'ultimatewoo-pro' ),
			'label_last_name'   => __( 'Last name:', 'ultimatewoo-pro' ),
			'submit_text'       => __( 'Subscribe', 'ultimatewoo-pro' ),
			'success_message'   => __( 'Thank you. You are now subscribed to our list.', 'ultimatewoo-pro' ),
			'list'              => '',
		);
		$atts = shortcode_atts( $default, $atts, 'fue_subscribe' );

		ob_start();
		fue_get_template( 'subscribe.php', $atts );
		return ob_get_clean();
	}

}
endif;

return new FUE_Shortcode_Subscribe();
