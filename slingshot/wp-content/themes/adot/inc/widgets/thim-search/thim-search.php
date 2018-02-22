<?php
if ( class_exists( 'WooCommerce' ) ) {
	require get_template_directory() . '/inc/widgets/thim-search/lib/function-search.php';

	class Thim_Search_Widget extends Thim_Widget {

		function __construct() {
			parent::__construct(
				'thim-search',
				__( 'Thim: Search Products', 'thim' ),
				array(
					'description' => __( 'A search product form my site.', 'thim' ),
					'help'        => '',
					'panels_groups' => array('thim_widget_group')
				),
				array(),
				array(
					'title'               => array(
						'type'  => 'text',
						'std'   => '',
						'label' => __( 'Title Widget', 'thim' )
					),
					'type'     => array(
						'type'    => 'select',
						'label'   => 'Style',
						'options' => array( '' => __('Default','thim'), 'by-cats' => __('Search By Category','thim') ),
					),

				),
				TP_THEME_DIR . 'inc/widgets/thim-search/'
			);
		}

		/**
		 * Initialize the CTA widget
		 */

		function get_template_name( $instance ) {
			//var_dump($instance['layout']);
 			if($instance['type'] == 'by-cats'){
 				return 'by-category';
			}else{
				return 'base';
			}

		}

		function get_style_name( $instance ) {
			return false;
		}

	}

	function thim_search_register_widget() {
		register_widget( 'Thim_Search_Widget' );
	}

	add_action( 'widgets_init', 'thim_search_register_widget' );
}
