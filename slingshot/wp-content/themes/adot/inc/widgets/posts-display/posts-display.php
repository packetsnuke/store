<?php

class Thim_Posts_Display_Widget extends Thim_Widget {
	function __construct() {
		parent::__construct(
			'posts-display',
			__( 'Thim: Posts Display', 'thim' ),
			array(
				'description' => __( 'Show Post', 'thim' ),
				'help'        => '',
				'panels_groups' => array('thim_widget_group')
			),
			array(),
			array(
				'title'          => array(
					'type'    => 'text',
					'label'   => __( 'Heading title', 'thim' ),
					'default' => __( "Heading title", "thim" )
				),
				'border_title'   => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show Border bottom for Title', 'thim' ),
					'default' => true
				),

				'number_posts'   => array(
					'type'    => 'text',
					'label'   => __( 'Number Posts', 'thim' ),
					'default' => __( "4", "thim" )
				),
				'column'         => array(
					"type"    => "select",
					"label"   => __( "Column", "thim" ),
					"default" => "4",
					"options" => array(
						"1" => __( "1", "thim" ),
						"2" => __( "2", "thim" ),
						"3" => __( "3", "thim" ),
						"4" => __( "4", "thim" )
					),
				),
				'excerpt_words'  => array(
					'type'    => 'text',
					'label'   => __( 'Content Length Excerpt Words', 'thim' ),
					'default' => __( "15", "thim" )
				),
				'orderby'        => array(
					"type"    => "select",
					"label"   => __( "Order by", "thim" ),
					"options" => array(
						"popular" => __( "Popular", "thim" ),
						"recent"  => __( "Recent", "thim" ),
						"title"   => __( "Title", "thim" ),
						"random"  => __( "Random", "thim" ),
					),
				),
				'order'          => array(
					"type"    => "select",
					"label"   => __( "Order by", "thim" ),
					"options" => array(
						"asc"  => __( "ASC", "thim" ),
						"desc" => __( "DESC", "thim" )
					),
				),
				'hide_read_more' => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show Read More', 'thim' ),
					'default' => true
				),
				'text_view_all'   => array(
					'type'    => 'text',
					'label'   => __( 'View All Text', 'thim' ),
					'default' => __( "View All", "thim" )
				),
				'link_view_all'   => array(
					'type'    => 'text',
					'label'   => __( 'View All Link', 'thim' ),
				),
				'show_author'    => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show Author', 'thim' ),
					'default' => true
				),
				'show_date'      => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show Date', 'thim' ),
					'default' => true
				),
				'show_comment'   => array(
					'type'    => 'checkbox',
					'label'   => __( 'Show Comment', 'thim' ),
					'default' => true
				),
				'layout'         => array(
					"type"    => "select",
					"label"   => __( "Select Layout", "thim" ),
					"options" => array(
						"layout-1" => __( "Layout 01", "thim" ),
						"layout-2" => __( "Layout 02", "thim" )
					),
				),
			),
			TP_THEME_DIR . 'inc/widgets/posts-display/'
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
}

function thim_posts_display_register_widget() {
	register_widget( 'Thim_Posts_Display_Widget' );
}

add_action( 'widgets_init', 'thim_posts_display_register_widget' );