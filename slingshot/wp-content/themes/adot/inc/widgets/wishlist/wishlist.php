<?php if ( class_exists( 'YITH_WCWL' ) ) :
	class Wishlist_Widget extends Thim_Widget {
		function __construct() {
			parent::__construct(
				'wishlist',
				__( 'Thim: Wishlist', 'thim' ),
				array(
					'description'   => __( 'Wishlist for site. ', 'thim' ),
					'help'          => '',
					'panels_groups' => array( 'thim_widget_group' )
				),
				array(),
				array(
					'bg_color_icon' => array(
						'type'  => 'checkbox',
						'label' => __( 'Using Primary color for Background Widget', 'thim' )
					),

					'show_number'   => array(
						'type'    => 'checkbox',
						'default' => true,
						'label'   => __( 'Show Number', 'thim' )
					),

					'bg_color'      => array(
						'type'  => 'color',
						'label' => __( 'Background Number', 'thim' )
					),
					'color_number'  => array(
						'type'  => 'color',
						'label' => __( 'Color Number', 'thim' )
					),
				),
				TP_THEME_DIR . 'inc/widgets/wishlist/'
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

	function thim_wishlist_widget() {
		register_widget( 'Wishlist_Widget' );
	}

	add_action( 'widgets_init', 'thim_wishlist_widget' );
endif;