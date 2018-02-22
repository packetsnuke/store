<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( class_exists( 'THIM_Portfolio' ) ) {
	class Thim_Portfolio_Widget extends Thim_Widget {

		function __construct() {
			$portfolio_category = get_terms( 'portfolio_category', array(
				'hide_empty' => 0,
				'orderby'    => 'ASC',
				'parent'     => 0
			) );
			$cate               = '';
			$cate[0]        = 'All';
			if ( is_array( $portfolio_category ) ) {
				foreach ( $portfolio_category as $cat ) {
					$cate[ $cat->term_id ] = $cat->name;
				}
			}

			parent::__construct(
				'portfolio',
				__( 'Thim: Portfolio', 'thim' ),
				array(
					'description' => __( 'Thim Widget Portfolio By thimpress.com', 'thim' ),
					'help'        => '',
					'panels_groups' => array('thim_widget_group')
				),
				array(),
				array(

					'portfolio_category' => array(
						'type'    => 'select',
						'label'   => __( 'Select a category', 'thim' ),
						'default' => 'All',
						'options' => $cate
					),
					'filter_hiden'       => array(
						'type'    => 'checkbox',
						'label'   => __( 'Hide Filters?', 'thim' ),
						'default' => false,
					),
					'filter_position'    => array(
						'type'    => 'select',
						'label'   => __( 'Select a filter position', 'thim' ),
						'default' => 'center',
						'options' => array(
							'left'   => 'Left',
							'center' => 'Center',
							'right'  => 'Right',
						)
					),
					'column'             => array(
						'type'    => 'select',
						'label'   => __( 'Select a column', 'thim' ),
						'default' => 'center',
						'options' => array(
							'one'   => 'One',
							'two'   => 'Two',
							'three' => 'Three',
							'four'  => 'Four',
							'five'  => 'Five',
						)
					),
					'gutter'             => array(
						'type'    => 'checkbox',
						'label'   => __( 'Gutter?', 'thim' ),
						'default' => false
					),
					'item_size'          => array(
						'type'    => 'select',
						'label'   => __( 'Select a item size', 'thim' ),
						'default' => 'center',
						'options' => array(
							'multigrid' => 'Multigrid',
							'masonry'   => 'Masonry',
							'same'      => 'Same size',
						)
					),
					'paging'             => array(
						'type'    => 'select',
						'label'   => __( 'Select a paging', 'thim' ),
						'default' => 'center',
						'options' => array(
							'all'             => 'Show All',
							'limit'           => 'Limit Items',
							'paging'          => 'Paging',
							'infinite_scroll' => 'Infinite Scroll',
						)
					),
					'style-item'         => array(
						'type'    => 'select',
						'label'   => __( 'Select style items', 'thim' ),
						'default' => 'style-01',
						'options' => array(
							'style01' => 'Caption Hover Effects 01',
							'style02' => 'Caption Hover Effects 02',
							'style03' => 'Caption Hover Effects 03',
							'style04' => 'Caption Hover Effects 04',
							'style05' => 'Caption Hover Effects 05',
							'style06' => 'Caption Hover Effects 06',
							'style07' => 'Caption Hover Effects 07',
							'style08' => 'Caption Hover Effects 08',
						)
					),
					'num_per_view'       => array(
						'type'  => 'text',
						'label' => __( 'Enter a number view', 'thim' ),
					),
					'show_readmore'       => array(
						'type'  => 'checkbox',
						'label'   => __( 'Show Read More?', 'thim' ),
						'default' => false
					)
				),
				TP_THEME_DIR . 'inc/widgets/portfolio/'
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

	function thim_portfolio_register_widget() {
		register_widget( 'Thim_Portfolio_Widget' );
	}

	add_action( 'widgets_init', 'thim_portfolio_register_widget' );
}