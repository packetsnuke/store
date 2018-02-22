<?php

/**
 * Created by PhpStorm.
 * User: Phan Long
 * Date: 4/20/2015
 * Time: 8:18 AM
 * Widget Collection
 */
class Thim_Collection_Widget extends Thim_Widget {

	function __construct() {

		parent::__construct(
			'collection',
			__( 'Thim: Collection', 'thim' ),
			array(
				'description' => __( 'Collection.', 'thim' ),
				'help'        => '',
				'panels_groups' => array('thim_widget_group')
			),
			array(),

			array(
				'image'           => array(
					'type'  => 'media',
					'label' => __( 'Image', 'thim' ),
				),
				'title_group'     => array(
					'type'   => 'section',
					'label'  => __( 'Title Options', 'thim' ),
					'hide'   => true,
					'fields' => array(
						'title'          => array(
							'type'                  => 'text',
							'label'                 => __( 'Title', 'thim' ),
							"default"               => "",
							"description"           => __( "Provide the title for this Collection.", "thim" ),
							'allow_html_formatting' => true,
						),
						'color_title'    => array(
							'type'  => 'color',
							'label' => __( 'Color Title', 'thim' ),
							"class" => "color-mini"
						),
						'size'           => array(
							"type"        => "select",
							"label"       => __( "Size Heading", "thim" ),
							"options"     => array(
								"h3" => __( "h3", "thim" ),
								"h2" => __( "h2", "thim" ),
								"h4" => __( "h4", "thim" ),
								"h5" => __( "h5", "thim" ),
								"h6" => __( "h6", "thim" )
							),
							"description" => __( "Select size heading.", "thim" )
						),
						'font_heading'   => array(
							"type"        => "select",
							"label"       => __( "Font Heading", "thim" ),
							"options"     => array(
								"default" => __( "Default", "thim" ),
								"custom"  => __( "Custom", "thim" )
							),
							"description" => __( "Select Font heading.", "thim" )
						),
						'custom_heading' => array(
							'type'   => 'section',
							'label'  => __( 'Custom Heading Option', 'thim' ),
							'hide'   => true,
							'fields' => array(
								'custom_font_size'   => array(
									"type"        => "number",
									"label"       => __( "Font Size", "thim" ),
									"suffix"      => "px",
									"default"     => "24",
									"description" => __( "custom font size", "thim" ),
									"class"       => "color-mini"
								),
								'custom_font_weight' => array(
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
								'custom_mg_bt'       => array(
									"type"   => "number",
									"class"  => "color-mini",
									"label"  => __( "Margin Bottom Value", "thim" ),
									"value"  => 0,
									"suffix" => "px",
								),
							)
						),
					),
				),
				'icon'            => array(
					"type"        => "icon",
					"class"       => "",
					"label"       => __( "Select Icon:", "thim" ),
					"description" => __( "Select the icon from the list.", "thim" ),
					"class_name"  => 'font-awesome',
				),
				'desc_group'      => array(
					'type'   => 'section',
					'label'  => __( 'Description', 'thim' ),
					'hide'   => true,
					'fields' => array(
						'content'              => array(
							"type"                  => "textarea",
							"label"                 => __( "Add description", "thim" ),
							"default"               => "",
							"description"           => __( "Provide the description for this icon box.", "thim" ),
							'allow_html_formatting' => true
						),
						'custom_font_size_des' => array(
							"type"        => "number",
							"label"       => __( "Custom Font Size", "thim" ),
							"suffix"      => "px",
							"default"     => "13",
							"description" => __( "custom font size", "thim" ),
							"class"       => "color-mini",
						),
						'margin_bottom'        => array(
							'type'   => 'number',
							'label'  => __( 'Margin Bottom: ', 'thim' ),
							"suffix" => "px",
							"class"  => "color-mini",
						),
						'custom_font_weight'   => array(
							"type"        => "select",
							"label"       => __( "Custom Font Weight", "thim" ),
							"class"       => "color-mini",
							"options"     => array(
								""     => __( "Normal", "thim" ),
								"bold" => __( "Bold", "thim" ),
								"100"  => __( "100", "thim" ),
								"200"  => __( "200", "thim" ),
								"300"  => __( "300", "thim" ),
								"400"  => __( "400", "thim" ),
								"500"  => __( "500", "thim" ),
								"600"  => __( "600", "thim" ),
								"700"  => __( "700", "thim" ),
								"800"  => __( "800", "thim" ),
								"900"  => __( "900", "thim" )
							),
							"description" => __( "Select Custom Font Weight", "thim" ),
						),
						'color_description'    => array(
							"type"  => "color",
							"label" => __( "Color Description", "thim" ),
							"class" => "color-mini",
						),
					),
				),
				'read_more_group' => array(
					'type'   => 'section',
					'label'  => __( 'Link Read More', 'thim' ),
					'hide'   => true,
					'fields' => array(
						// Add link to existing content or to another resource
						'link'                         => array(
							"type"        => "text",
							"label"       => __( "Add Link", "thim" ),
							"description" => __( "Provide the link that will be applied to this collection.", "thim" )
						),
						'read_text'                    => array(
							"type"                  => "text",
							"label"                 => __( "Read More Text", "thim" ),
							"default"               => "Read More",
							"description"           => __( "Customize the read more text.", "thim" ),
							'allow_html_formatting' => true,
						),
						'show_arrow'                   => array(
							"type"        => "checkbox",
							"label"       => __( "Show/hide arrow affter", "thim" ),
							"default"     => false,
							"description" => __( "Show/hide arrow icon affter Read More text.", "thim" ),
						),
						'read_more_font_size'          => array(
							"type"        => "number",
							"label"       => __( "Font Size Read More Text: ", "thim" ),
							"suffix"      => "px",
							"description" => __( "custom font size", "thim" ),
							"class"       => "mini",
						),
						'read_more_font_weight'        => array(
							"type"    => "select",
							"label"   => __( "Font Weight Read More Text: ", "thim" ),
							"options" => array(
								""     => __( "Normal", "thim" ),
								"bold" => __( "Bold", "thim" ),
								"100"  => __( "100", "thim" ),
								"200"  => __( "200", "thim" ),
								"300"  => __( "300", "thim" ),
								"400"  => __( "400", "thim" ),
								"500"  => __( "500", "thim" ),
								"600"  => __( "600", "thim" ),
								"700"  => __( "700", "thim" ),
								"800"  => __( "800", "thim" ),
								"900"  => __( "900", "thim" )
							),
						),
						'border_read_more_color'       => array(
							"type"  => "color",
							"class" => "color-mini",
							"label" => __( "Border Color Read More Text Hover:", "thim" ),
						),
						'border_hover_read_more_color' => array(
							"type"  => "color",
							"class" => "mini",
							"label" => __( "Border Color Read More Text:", "thim" ),
							"class" => "color-mini",
						),
						'bg_read_more_text'            => array(
							"type"  => "color",
							"class" => "mini",
							"label" => __( "Background Color Read More Text:", "thim" ),
							"class" => "color-mini",
						),
						'bg_read_more_text_hover'      => array(
							"type"  => "color",
							"label" => __( "Background Hover Color Read More Text:", "thim" ),
							"class" => "color-mini",
						),
						'read_more_text_color'         => array(
							"type"    => "color",
							"class"   => "",
							"label"   => __( "Text Color Read More Text:", "thim" ),
							"default" => "#fff",
							"class"   => "color-mini",
						),
						'read_more_text_color_hover'   => array(
							"type"    => "color",
							"class"   => "",
							"label"   => __( "Text Color Hover Read More Text :", "thim" ),
							"default" => "#fff",
						),

					),
				),
				'collection_bg'   => array(
					"type"    => "color",
					"label"   => __( "Background collection:", "thim" ),
					"default" => "#fff",
					"class"   => "color-mini",
				),
				'position'        => array(
					"type"    => "select",
					"label"   => __( "Content Position:", "thim" ),
					"options" => array(
						"default" => "Bottom",
						"overlay"  => "Center",
						"top"     => "Top"
					),
				),
				'text_align_sc'   => array(
					"type"    => "select",
					"class"   => "",
					"label"   => __( "Text Align:", "thim" ),
					"default" => 'text-left',
					"options" => array(
						"text-left"   => "Text Left",
						"text-right"  => "Text Right",
						"text-center" => "Text Center"
					)
				),
				'css_animation'   => array(
					"type"    => "select",
					"label"   => __( "CSS Animation", "thim" ),
					"options" => array(
						""              => __( "No", "thim" ),
						"top-to-bottom" => __( "Top to bottom", "thim" ),
						"bottom-to-top" => __( "Bottom to top", "thim" ),
						"left-to-right" => __( "Left to right", "thim" ),
						"right-to-left" => __( "Right to left", "thim" ),
						"appear"        => __( "Appear from center", "thim" )
					),
				)

			),
			TP_THEME_DIR . 'inc/widgets/collection/'
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
		wp_enqueue_script( 'collection-script', TP_THEME_URI . 'inc/widgets/collection/js/collection-script.js', array( 'jquery' ), '', true );
	}

	function enqueue_admin_scripts() {
		wp_enqueue_script( 'admin-collection', TP_THEME_URI . 'inc/widgets/collection/js/collection-admin.js', array( 'jquery' ), '', true );
	}
}


//
function thim_collection_widget() {
	register_widget( 'Thim_Collection_Widget' );
}

add_action( 'widgets_init', 'thim_collection_widget' );