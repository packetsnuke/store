<?php

class Social_Widget extends Thim_Widget {

	function __construct() {

		parent::__construct(
			'social',
			__('Thim: Social Links', 'thim'),
			array(
				'description' => __('Social Links', 'thim'),
				'help' => '',
				'panels_groups' => array('thim_widget_group')
			),
			array(),
 			array(
 				'title' => array(
					'type' => 'text',
					'label' => __('Title', 'thim')
 				),
				'link_face' => array(
					'type' => 'text',
					'label' => __('Facebook Url', 'thim')
				),
				'link_twitter' => array(
					'type' => 'text',
					'label' => __('Twitter Url', 'thim')
				),
				'link_google' => array(
					'type' => 'text',
					'label' => __('Google Url', 'thim')
				),
				'link_instagram' => array(
					'type' => 'text',
					'label' => __('Instagram Url', 'thim')
				),
				'link_pinterest' => array(
					'type' => 'text',
					'label' => __('Pinterest Url', 'thim')
				),
				'link_youtube' => array(
					'type' => 'text',
					'label' => __('Youtube Url', 'thim')
				),
				'link_target'   => array(
					"type"    => "select",
					"label"   => __( "Link Target", "thim" ),
					"options" => array(
						"_self"  => __( "Same window", "thim" ),
						"_blank" => __( "New window", "thim" ),
					),
				),

			),
			TP_THEME_DIR . 'inc/widgets/social/'
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

function thim_social_widget() {
	register_widget( 'Social_Widget' );
}

add_action( 'widgets_init', 'thim_social_widget' );