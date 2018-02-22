<?php
/**
	* widget empty space
**/
class Thim_Empty_Space_Widget extends Thim_Widget {

	function __construct() {

		parent::__construct(
			'empty-space',
			__('Thim: Empty Space', 'thim'),
			array(
				'description' => __('Empty space.', 'thim'),
				'help' => '',
				'panels_groups' => array('thim_widget_group')
			),
			array(),

			array(

				'height' => array(
					'type' => 'number',
					'label' => __('Height', 'thim'),
					'suffix'	=> 'px',
					'default'	=> 20,
					'min'		=> 0,
					'max'		=> 500,
				),

                'else_class' => array(
					'type' => 'text',
					'label' => __('Extract class name', 'thim'),
					'description' => __('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'thim'),
				),

			),
			TP_THEME_DIR . 'inc/widgets/empty-space/'
		);
	}

	/**
	 * Initialize the CTA widget
	 */
	

	function get_template_name($instance) {
		return 'base';
	}

	function get_style_name($instance) {
		return false;
	}
}

//
function thim_empty_space_widget() {
	register_widget( 'Thim_Empty_Space_Widget' );
}

add_action( 'widgets_init', 'thim_empty_space_widget' );