<?php

class Thim_Banner_Text_Widget extends Thim_Widget {

	function __construct() {

		parent::__construct(
			'banner-text',
			__( 'Thim: Banner Text', 'thim' ),
			array(
				'description' => __( 'Add heading text', 'thim' ),
				'help'        => '',
				'panels_groups' => array('thim_widget_group')
			),
			array(),
			array(
				'image'          => array(
					'type'        => 'media',
					'label'       => __( 'Image', 'thim' ),
					'description' => __( 'Select image from media library', 'thim' )
				),


				'title_group'    => array(
					'type'   => 'section',
					'label'  => __( 'Custom Title', 'thim' ),
					'hide'   => true,
					'fields' => array(
						'title'             => array(
							'type'        => 'text',
							'label'       => __( 'Title', 'thim' ),
							'description' => __( 'Enter title','thim' )
						),
						'title_color'       => array(
							'type'    => 'color',
							'label'   => __( 'Title color', 'thim' ),
							'default' => '#fff',
						),
						'title_border'      => array(
							'type'    => 'checkbox',
							'label'   => __( 'Show/Hide Line Bottom', 'thim' ),
							'default' => true,
						),
						'title_font_size'   => array(
							"type"        => "number",
							"label"       => __( "Font Size", "thim" ),
							"suffix"      => "px",
							"default"     => "60",
							"description" => __( "custom font size", "thim" ),
						),

						'title_font_weight' => array(
							"type"        => "select",
							"label"       => __( "Custom Font Weight", "thim" ),
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
					),
				),
				'desc_group'     => array(
					'type'   => 'section',
					'label'  => __( 'Description', 'thim' ),
					'hide'   => true,
					'fields' => array(
						'des'             => array(
							'type'                  => 'textarea',
							'label'                 => __( 'Descriptions', 'thim' ),
							"description"           => __( "descriptions", "thim" ),
							'allow_html_formatting' => true
						),
						'des_color'       => array(
							'type'    => 'color',
							'label'   => __( 'Description color', 'thim' ),
							'default' => '#fff',
						),
						'des_font_size'   => array(
							"type"        => "number",
							"label"       => __( "Font Size", "thim" ),
							"suffix"      => "px",
							"default"     => "16",
							"description" => __( "custom font size", "thim" ),
						),

						'des_font_weight' => array(
							"type"        => "select",
							"label"       => __( "Custom Font Weight", "thim" ),
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
					),
				),
				'link'           => array(
					"type"        => "text",
					"label"       => __( "Add Link", "thim" ),
					"description" => __( "Provide the link that will be applied to title.", "thim" )
				),
				'text_alignment' => array(
					"type"        => "select",
					"label"       => __( "Text alignment", "thim" ),
					"description" => "Select Text alignment.",
					"options"     => array(
						"left"   => __( "Align Left", "thim" ),
						"right"  => __( "Align Right", "thim" ),
						"center" => __( "Align Center", "thim" )
					),
					'default'     => 'center'
				),
			),
			TP_THEME_DIR . 'inc/widgets/banner-text/'
		);
	}

	/**
	 * Initialize the CTA widget
	 */


	function get_template_name( $instance ) {
		return 'base';
	}

	function get_style_name( $instance ) {
		return 'basic';
	}
}

function thim_banner_text_register_widget() {
	register_widget( 'Thim_Banner_Text_Widget' );
}

add_action( 'widgets_init', 'thim_banner_text_register_widget' );