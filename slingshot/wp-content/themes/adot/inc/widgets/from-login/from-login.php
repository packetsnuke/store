<?php
require get_template_directory() . '/inc/widgets/from-login/lib/function-from.php';

class Thim_From_Login_Widget extends Thim_Widget {
	function __construct() {
		parent::__construct(
			'from-login',
			__( 'Thim: Form Login', 'thim' ),
			array(
				'description' => __( 'Form Login', 'thim' ),
				'help'        => '',
				'panels_groups' => array('thim_widget_group')
			),
			array(),
			array(),
			TP_THEME_DIR . 'inc/widgets/from-login/'
		);
	}

	/**
	 * Initialize the CTA widget
	 */


	function get_template_name( $instance ) {
		return 'base';
	}

	function get_style_name( $instance ) {
		return false;
	}

	function enqueue_frontend_scripts() {
		wp_enqueue_script( 'thim-from-login', TP_THEME_URI . 'inc/widgets/from-login/js/from-login.js', array( 'jquery' ), '', true );
	}
}

function thim_from_login_register_widget() {
	register_widget( 'Thim_From_Login_Widget' );
}

add_action( 'widgets_init', 'thim_from_login_register_widget' );

