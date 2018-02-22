<?php

class Thim_Gallery_Images_Widget extends Thim_Widget {

	function __construct() {

		parent::__construct(
			'gallery-images',
			__( 'Thim: Gallery Images', 'thim' ),
			array(
				'description' => __( 'Add gallery image', 'thim' ),
				'help'        => '',
				'panels_groups' => array('thim_widget_group')
			),
			array(),
			array(
				'title'               => array(
					'type'                  => 'text',
					'label'                 => __( 'Heading Text', 'thim' ),
 					'allow_html_formatting' => true
				),
				'image'         => array(
					'type'        => 'multimedia',
					'label'       => __( 'Image', 'thim' ),
					'description' => __( 'Select image from media library.', 'thim' )
				),

				'image_size'    => array(
					'type'        => 'text',
					'label'       => __( 'Image size', 'thim' ),
					'description' => __( 'Enter image size. Example: "thumbnail", "medium", "large", "full"', 'thim' )
				),
				'image_link'    => array(
					'type'        => 'textarea',
					'label'       => __( 'Image Link', 'thim' ),
					'description' => __( 'Enter URL if you want this image to have a link. (Example: #link1,#link2,#link3,...)', 'thim' )
				),
				'display_type'  => array(
					"type"    => "select",
					"label"   => __( "Display Type", "thim" ),
					"options" => array(
						"slider" => __( "Slider", "thim" ),
						"grid"   => __( "Grid", "thim" ),
					),
				),
				'number'        => array(
					'type'    => 'number',
					'default' => '4',
					'label'   => __( 'Number Image Per View', 'thim' ),
				),
				'navigation'    => array(
					"type"  => "checkbox",
					"label" => __( "Show navigation", "thim" ),
					"class" => "clear",
				),
				'pagination'    => array(
					"type"  => "checkbox",
					"label" => __( "Show Pagination", "thim" ),
					"class" => "clear",
				),
				'column'        => array(
					'type'    => 'select',
					'default' => '5',
					'label'   => __( 'Column Number ', 'thim' ),
					"options" => array(
						"2" => __( "2 column", "thim" ),
						"3" => __( "3 column", "thim" ),
						"4" => __( "4 column", "thim" ),
						"5" => __( "5 column", "thim" ),
						"6" => __( "6 column", "thim" ),
					),
				),
				'link_target'   => array(
					"type"    => "select",
					"label"   => __( "Link Target", "thim" ),
					"options" => array(
						"_self"  => __( "Same window", "thim" ),
						"_blank" => __( "New window", "thim" ),
					),
				),

				'css_animation' => array(
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
				),
			),
			TP_THEME_DIR . 'inc/widgets/gallery-images/'
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
//		wp_enqueue_script( 'thim-owl-carousel', TP_THEME_URI . '/js/owl.carousel.min.js', array( 'jquery' ), '', false );
		wp_enqueue_script( 'gallery-images', TP_THEME_URI . 'inc/widgets/gallery-images/js/gallery-images.js', array( 'jquery' ), '', false );
	}

	function enqueue_admin_scripts() {
		wp_enqueue_script( 'gallery-images', TP_THEME_URI . 'inc/widgets/gallery-images/js/gallery-images-admin.js', array( 'jquery' ), '', false );

	}
}

//
function thim_gallery_images_widget() {
	register_widget( 'Thim_Gallery_Images_Widget' );
}

add_action( 'widgets_init', 'thim_gallery_images_widget' );