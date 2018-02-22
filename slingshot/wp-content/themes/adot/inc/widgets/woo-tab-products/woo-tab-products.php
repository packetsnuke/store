<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
if ( class_exists( 'WooCommerce' ) ) {
	class Woo_Products_Widget extends Thim_Widget {
		function __construct() {
			$product_categories = get_terms( 'product_cat', array( 'hide_empty' => 0 ) );
			$cate               = '';
			if ( is_array( $product_categories ) ) {
				foreach ( $product_categories as $cat ) {
					$cate[$cat->term_id] = $cat->name;
				}
			}
			parent::__construct(
				'woo-tab-products',
				__( 'Thim: Tab Products', 'thim' ),
				array(
					'description'   => __( 'Show products with options.', 'thim' ),
					'help'          => '',
					'panels_groups' => array( 'thim_widget_group' )
				),
				array(),
				array(
					'tab' => array(
						'type'      => 'repeater',
						'label'     => __( 'Add Tab', 'thim' ),
						'item_name' => __( 'Add Tab', 'thim' ),
						'fields'    => array(
							'title'          => array(
								"type"    => "text",
								"label"   => __( "Tab Title", "thim" ),
								"default" => "Tab Title",
							),
							'show'           => array(
								'type'    => 'select',
								'std'     => '',
								'label'   => __( 'Show By', 'thim' ),
								'options' => array(
									'all'            => 'All Products',
									'featured'    => __( 'Featured Products', 'thim' ),
									'onsale'      => __( 'On-sale Products', 'thim' ),
									"bestsellers" => __( "Best-Sellers Products", "thim" ),
									'category'    => __( 'Category', "thim" )
								),
								'state_emitter' => array(
									'callback' => 'select',
									'args'     => array( 'category_product_tab' )
								)
							),
							'cats'           => array(
								'type'    => 'select',
								'std'     => '',
								'label'   => __( 'Select Category', 'thim' ),
								'options' => $cate,
								'state_handler' => array(
									'category_product_tab[category]'    => array( 'show' ),
									'category_product_tab[all]'         => array( 'hide' ),
									'category_product_tab[featured]'    => array( 'hide' ),
									'category_product_tab[bestsellers]' => array( 'hide' )
								)
							),
							'orderby'        => array(
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
							'order'          => array(
								'type'    => 'select',
								'std'     => 'desc',
								'label'   => __( 'Order', 'Sorting order', 'thim' ),
								'options' => array(
									'asc'  => __( 'ASC', 'thim' ),
									'desc' => __( 'DESC', 'thim' ),
								)
							),
							'column'         => array(
								'type'    => 'select',
								'std'     => '4',
								'label'   => __( 'Columns', 'thim' ),
								'options' => array(
									'1' => __( '1', 'thim' ), // using  column bootstrap
									'2' => __( '2', 'thim' ),
									'3' => __( '3', 'thim' ),
									'4' => __( '4', 'thim' ),
									'5' => __( '5', 'thim' )
								),
								'default' => '4',
							),
							'number_product' => array(
								'type'    => 'number',
								'std'     => '5',
								'label'   => __( 'Number Product', 'thim' ),
								'default' => '8',
							),
							'type-show'      => array(
								'type'    => 'select',
								'label'   => __( 'Type of shown products when products are more than columns', 'thim' ),
								'options' => array(
									'grid'   => __( 'Grid', 'thim' ),
									'slider' => __( 'Slider', 'thim' ),
								),
								'default' => 'grid',
							),
							'hide_free'      => array(
								'type'  => 'checkbox',
								'std'   => 0,
								'label' => __( 'Hide free products', 'thim' )
							),
							'show_hidden'    => array(
								'type'  => 'checkbox',
								'std'   => 0,
								'label' => __( 'Show hidden products', 'thim' )
							)
						),
					),
				),
				TP_THEME_DIR . 'inc/widgets/woo-tab-products/'
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

//		function enqueue_admin_scripts() {
//			wp_enqueue_script( 'js-admin-product', TP_THEME_URI . 'inc/widgets/woo-tab-products/js/admin-tab-product.js', array( 'jquery' ), '', true );
//		}

		function enqueue_frontend_scripts() {
			wp_enqueue_script( 'js-admin-product-slider', TP_THEME_URI . 'inc/widgets/wc-product-slider/js/product-slider.js', array( 'jquery' ), '', true );
		}
	}

	function thim_woo_products_register_widget() {
		register_widget( 'Woo_Products_Widget' );
	}

	add_action( 'widgets_init', 'thim_woo_products_register_widget' );
}