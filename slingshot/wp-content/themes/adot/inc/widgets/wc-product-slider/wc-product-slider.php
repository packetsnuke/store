<?php
/**
 * Created by lucky boy.
 * User: dong-it
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
if ( class_exists( 'WooCommerce' ) ) {
	class Thim_Wc_Product_Slider_Widget extends Thim_Widget {
		function __construct() {
			$product_categories = get_terms( 'product_cat', array( 'hide_empty' => 0, 'orderby' => 'ASC' ) );
			$cate               = '';
			if ( is_array( $product_categories ) ) {
				foreach ( $product_categories as $cat ) {
					$cate[$cat->term_id] = $cat->name;
				}
			}

			parent::__construct(
				'wc-product-slider',
				__( 'Thim Product Slider', 'thim' ),
				array(
					'description'   => __( 'Show product Slider', 'thim' ),
					'help'          => '',
					'panels_groups' => array( 'thim_widget_group' )
				),
				array(),
				array(
					'title'               => array(
						'type'  => 'text',
						'std'   => '',
						'label' => __( 'Title Widget', 'thim' )
					),
					'margin-bottom'       => array(
						'type'  => 'number',
						'label' => __( 'input number margin bottom title', 'thim' ),
						'min'   => '0',
						'max'   => '100'
					),
					'description_product' => array(
						'type'                  => 'textarea',
						'label'                 => __( 'Type a text description product widget', 'thim' ),
						'default'               => '',
						'allow_html_formatting' => true,
						'rows'                  => 3
					),
					'product_slider_show' => array(
						'type'          => 'select',
						'std'           => '',
						'label'         => __( 'Show Type', 'thim' ),
						'options'       => array(
							'all'         => 'All Products',
							'featured'    => __( 'Featured Products', 'thim' ),
							'onsale'      => __( 'On-sale Products', 'thim' ),
							"bestsellers" => __( "Best-Sellers Products", "thim" ),
							"top_rated"   => __( "Top Rated Products", "thim" ),
							'category'    => __( 'Category', "thim" )
						),
						'state_emitter' => array(
							'callback' => 'select',
							'args'     => array( 'category_product_sl' )
						)
					),
					'product_slider_cats' => array(
						'type'          => 'select',
						'std'           => '',
						'label'         => __( 'Select Category', 'thim' ),
						'options'       => $cate,
						'state_handler' => array(
							'category_product_sl[category]'    => array( 'show' ),
							'category_product_sl[bestsellers]' => array( 'hide' ),
							'category_product_sl[onsale]'      => array( 'hide' ),
							'category_product_sl[featured]'    => array( 'hide' ),
							'category_product_sl[top_rated]'    => array( 'hide' ),
							'category_product_sl[all]'         => array( 'hide' ),
						)
					),
					'column_slider'       => array(
						'type'    => 'select',
						'std'     => '4',
						'label'   => __( 'Columns', 'thim' ),
						'options' => array(
							'1' => __( '1', 'thim' ),
							'2' => __( '2', 'thim' ),
							'3' => __( '3', 'thim' ),
							'4' => __( '4', 'thim' ),
							'5' => __( '5', 'thim' )
						),
						'default' => '4',
					),
					'row_slider'          => array(
						'type'    => 'select',
						'label'   => __( 'Rows', 'thim' ),
						'options' => array(
							'1' => __( '1', 'thim' ),
							'2' => __( '2', 'thim' ),
							'3' => __( '3', 'thim' ),
							'4' => __( '4', 'thim' ),
						),
						'default' => '1',
					),
					'orderby'             => array(
						'type'    => 'select',
						'std'     => 'date',
						'label'   => __( 'Order by', 'thim' ),
						'options' => array(
							'date'  => __( 'Date', 'thim' ),
							'price' => __( 'Price', 'thim' ),
							'rand'  => __( 'Random', 'thim' ),
							'sales' => __( 'Sales', 'thim' ),
						)
					),
					'order'               => array(
						'type'    => 'select',
						'std'     => 'desc',
						'label'   => _x( 'Order', 'Sorting order', 'thim' ),
						'options' => array(
							'asc'  => __( 'ASC', 'thim' ),
							'desc' => __( 'DESC', 'thim' ),
						)
					),
					'style_nav'           => array(
						'type'    => 'select',
						'std'     => 'desc',
						'label'   => _x( 'Style Nav Slider', 'Style Nav Slider', 'thim' ),
						'options' => array(
							'none'       => __( 'None', 'thim' ),
							'all'        => __( 'Show All', 'thim' ),
							'nav'        => __( 'Navigation', 'thim' ),
							'pagination' => __( 'Pagination', 'thim' ),
						)
					),
					'number_product'      => array(
						'type'    => 'text',
						'std'     => '12',
						'label'   => __( 'Number Product', 'thim' ),
						'default' => '8',
					),
					'hide_free'           => array(
						'type'  => 'checkbox',
						'std'   => 0,
						'label' => __( 'Hide free products', 'thim' )
					),
					'show_hidden'         => array(
						'type'  => 'checkbox',
						'std'   => 0,
						'label' => __( 'Show hidden products', 'thim' )
					),
					'style'          => array(
						"type"    => "select",
						"label"   => __( "Style", "thim" ),
						"default" => "style-01",
						"options" => array(
							""   => __( "Style 01", "thim" ),
							"style-02"  => __( "Style 02", "thim" ),
 						),
					)
				),
				TP_THEME_DIR . 'inc/widgets/wc-product-slider/'
			);
		}

		/**
		 * Initialize the CTA widget
		 */

		function get_template_name( $instance ) {
			if($instance['style'] == 'style-02'){
				return 'layout-02';
			}else{
				return 'base';
			}
 		}

		function get_style_name( $instance ) {
			return false;
		}

		function enqueue_frontend_scripts() {
			wp_enqueue_script( 'js-product-slider', TP_THEME_URI . 'inc/widgets/wc-product-slider/js/product-slider.js', array( 'jquery' ), '', true );
		}
	}

	function thim_wc_product_slider_widget_register() {
		register_widget( 'Thim_Wc_Product_Slider_Widget' );
	}

	add_action( 'widgets_init', 'thim_wc_product_slider_widget_register' );
}
