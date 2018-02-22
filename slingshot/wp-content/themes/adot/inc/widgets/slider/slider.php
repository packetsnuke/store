<?php

class Thim_Slider_Widget extends Thim_Widget {
	function __construct() {
		parent::__construct(
			'slider',
			__( 'Thim: Slider', 'thim' ),
			array(
				'description' => __( 'Thim Slider', 'thim' ),
				'help'        => '',
				'panels_groups' => array('thim_widget_group')
			),
			array(),
			array(
				'thim_slider_frames'   => array(
					'type'      => 'repeater',
					'label'     => __( 'Slider Frames', 'thim' ),
					'item_name' => __( 'Frame', 'thim' ),
					'fields'    => array(
						'thim_slider_background_image'      => array(
							'type'    => 'media',
							'library' => 'image',
							'label'   => __( 'Background Image', 'thim' ),
						),
						'thim_slider_background_image_type' => array(
							'type'    => 'select',
							'default' => 'cover',
							'label'   => __( 'Background Image Type', 'thim' ),
							'options' => array(
								'cover' => __( 'Cover', 'thim' ),
								'tile'  => __( 'Tile', 'thim' ),
							),
						),
						'content'                           => array(
							'type'   => 'section',
							'label'  => 'Content Slider',
							'hide'   => true,
							'fields' => array(
								'thim_slider_title'       => array(
									'type'                  => 'text',
									'label'                 => __( 'Heading Slider', 'thim' ),
									'allow_html_formatting' => true
								),
								'size'                    => array(
									'type'  => 'text',
									"label" => __( "Custom Font Size Title", "thim" ),
									'desc'  => 'input custom font size: ex: 30'
								),
								'custom_font_weight'      => array(
									"type"        => "select",
									"label"       => __( "Custom Font Weight", "thim" ),
									"class"       => "color-mini",
									"options"     => array(
										"normal" => __( "Normal", "thim" ),
										"bold"   => __( "Bold", "thim" ),
										"100"    => __( "100", "thim" ),
										"200"    => __( "200", "thim" ),
										"300"    => __( "300", "thim" ),
										"400"    => __( "400", "thim" ),
										"500"    => __( "500", "thim" ),
										"600"    => __( "600", "thim" ),
										"700"    => __( "700", "thim" ),
										"800"    => __( "800", "thim" ),
										"900"    => __( "900", "thim" )
									),
									"description" => __( "Select Custom Font Weight", "thim" ),
								),
								'thim_color_title'        => array(
									'type'  => 'color',
									'label' => __( 'Heading Color Title', 'thim' )
								),
								'thim_slider_description' => array(
									'type'                  => 'textarea',
									'label'                 => __( 'Description', 'thim' ),
									'allow_html_formatting' => true
								),
								'thim_color_des'          => array(
									'type'  => 'color',
									'label' => __( 'Description Color', 'thim' )
								),
								'button_text'             => array(
									'type'  => 'text',
									"label" => __( "Button Text", "thim" )
								),
								'button_link'             => array(
									'type'  => 'text',
									"label" => __( "Link Button", "thim" )
								),
								'thim_color_border'       => array(
									'type'  => 'color',
									'label' => __( 'Border Button Color', 'thim' )
								),
								'thim_color_bk_border'    => array(
									'type'  => 'color',
									'label' => __( 'Button Background Color', 'thim' )
								),
								'thim_color_button'       => array(
									'type'  => 'color',
									'label' => __( 'Button Color', 'thim' )
								),
								'thim_slider_align'       => array(
									"type"    => "select",
									"label"   => __( "Content Align:", "thim" ),
									"options" => array(
										"left"   => __( "Content at Left", "thim" ),
										"right"  => __( "Content at Right", "thim" ),
										"center" => __( "Content at Center", "thim" )
									),
								),
							),
						),
					),
				),
				'thim_slider_speed'    => array(
					'type'        => 'number',
					'label'       => __( 'Animation Speed', 'thim' ),
					'description' => __( 'Animation speed in milliseconds.', 'thim' ),
					'default'     => 800,
				),
				'thim_slider_timeout'  => array(
					'type'        => 'number',
					'label'       => __( 'Timeout', 'thim' ),
					'description' => __( 'How long each slide is displayed for in milliseconds.', 'thim' ),
					'default'     => 8000,
				),
				'thim_color_badge'     => array(
					'type'  => 'color',
					'label' => __( 'Badge Color', 'thim' )
				),
				'thim_slider_position' => array(
					'type'    => 'select',
					"label"   => __( "Select the position of Badge:", "thim" ),
					"options" => array(
						"center" => __( "Center", "thim" ),
						"bottom" => __( "Bottom", "thim" ),
					),
					'default' => 'center'
				),
				'slider_full_screen'   => array(
					'type'    => 'checkbox',
					'label'   => __( 'Full Screen', 'thim' ),
					'default' => true),
				'show_icon_scroll'   => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show Icon Scroll', 'thim' ),
					'default' => false),
	),
		TP_THEME_DIR . 'inc/widgets/slider/'
		);
	}

	function get_template_name( $instance ) {
		return 'base';
	}

	function get_style_name( $instance ) {
		return false;
	}

	/**
	 * Enqueue the slider scripts
	 */
	function enqueue_frontend_scripts() {
		wp_enqueue_script( 'thim-jquery-cycle', TP_THEME_URI . 'inc/widgets/slider/js/jquery.cycle.min.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'thim-cycle.swipe', TP_THEME_URI . 'inc/widgets/slider/js/jquery.cycle.swipe.min.js', array( 'jquery' ), '', true );
		wp_enqueue_script( 'thim-slider', TP_THEME_URI . 'inc/widgets/slider/js/slider.js', array( 'jquery' ), '', true );
	}
}

function thim_slider_register_widget() {
	register_widget( 'Thim_Slider_Widget' );
}

add_action( 'widgets_init', 'thim_slider_register_widget' );