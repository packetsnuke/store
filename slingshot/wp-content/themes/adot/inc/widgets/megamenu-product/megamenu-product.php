<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
if ( class_exists( 'WooCommerce' ) ) {
	class Thim_Megamenu_Product_Widget extends Thim_Widget {
		function __construct() {
			parent::__construct(
				'megamenu-product',
				__( 'Thim Mega Menu Product', 'thim' ),
				array(
					'description' => __( 'Mega Menu product', 'thim' ),
					'help'        => '',
					'panels_groups' => array('thim_widget_group')
				),
				array(),
				array(
 					'cats'                => array(
						'type'          => 'text',
						'default'           => '',
						'label'         => __( 'ID Category', 'thim' ),
						'description' => __( 'Enter ID category product. (Example: 10,20,25,...)', 'thim' )

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
						'default'     => 'desc',
						'label'   => _x( 'Order', 'Sorting order', 'thim' ),
						'options' => array(
							'asc'  => __( 'ASC', 'thim' ),
							'desc' => __( 'DESC', 'thim' ),
						)
					),
					'column'              => array(
						'type'    => 'select',
						'label'   => __( 'Columns', 'thim' ),
						'options' => array(
							'1' => __( '1', 'thim' ),
							'2' => __( '2', 'thim' ),
							'3' => __( '3', 'thim' ),
							'4' => __( '4', 'thim' ),
 						),
						'default' => '4',
					),
					'number_product'      => array(
						'type'    => 'number',
 						'label'   => __( 'Number Product', 'thim' ),
						'default' => '3',
					),
					'hide_free'           => array(
						'type'  => 'checkbox',
						'default'   => 0,
						'label' => __( 'Hide free products', 'thim' )
					),
					'show_hidden'         => array(
						'type'  => 'checkbox',
						'default'   => 0,
						'label' => __( 'Show hidden products', 'thim' )
					)
				),
				TP_THEME_DIR . 'inc/widgets/megamenu-product/'
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

	function thim_megamenu_product_widget_register() {
		register_widget( 'Thim_Megamenu_Product_Widget' );
	}

	add_action( 'widgets_init', 'thim_megamenu_product_widget_register' );
}
