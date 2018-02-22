<?php
if ( !function_exists( 'thim_wrapper_layout' ) ) :
	function thim_wrapper_layout() {
		global $theme_options_data, $wp_query;
		$using_custom_layout = $wrapper_layout = $cat_ID = '';
		$class_col           = 'col-sm-9 alignright';

		if ( get_post_type() == "product" ) {
			$prefix = 'thim_woo';
		} else {
			if ( is_front_page() || is_home() ) {
				$prefix = 'thim_front_page';
			} else {
				$prefix = 'thim_archive';
			}
		}
		// get id category
		$cat_obj = $wp_query->get_queried_object();
		if ( isset( $cat_obj->term_id ) ) {
			$cat_ID = $cat_obj->term_id;
		}
		// get layout
		if ( is_page() || is_single() ) {
			$postid = get_the_ID();
			if ( isset( $theme_options_data[$prefix . '_single_layout'] ) ) {
				$wrapper_layout = $theme_options_data[$prefix . '_single_layout'];
			}
			/***********custom layout*************/
			$using_custom_layout = get_post_meta( $postid, 'thim_mtb_custom_layout', true );
			if ( $using_custom_layout ) {
				$wrapper_layout = get_post_meta( $postid, 'thim_mtb_layout', true );
			}
		} else {
			if ( isset( $theme_options_data[$prefix . '_cate_layout'] ) ) {
				$wrapper_layout = $theme_options_data[$prefix . '_cate_layout'];
			}
			/***********custom layout*************/
			$using_custom_layout = get_tax_meta( $cat_ID, 'thim_layout', true );
			if ( $using_custom_layout <> '' ) {
				$wrapper_layout = get_tax_meta( $cat_ID, 'thim_layout', true );
			}
		}
		if ( $wrapper_layout == 'full-content' ) {
			$class_col = "col-sm-12 full-width";
		}
		if ( $wrapper_layout == 'sidebar-right' ) {
			$class_col = "col-sm-9 alignleft";
		}
		if ( $wrapper_layout == 'sidebar-left' ) {
			$class_col = 'col-sm-9 alignright';
		}
                if(isset($_REQUEST['style'])){
                        if($_REQUEST['style']=='style-2'|| $_REQUEST['style']=='style-3')
                                $class_col = "col-sm-9 alignleft";
                }
		return $class_col;
	}
endif;
//
add_action( 'thim_wrapper_loop_start', 'thim_wrapper_loop_start' );
if ( !function_exists( 'thim_wrapper_loop_start' ) ) :
	function thim_wrapper_loop_start() {
		$class_col     = thim_wrapper_layout();
		if ( is_404() ) {
			$class_col = 'col-sm-12 full-width';
		}
		$sidebar_class = '';
		if ( $class_col == "col-sm-9 alignleft" ) {
			$sidebar_class = ' sidebar-right';
		}
		if ( $class_col == "col-sm-9 alignright" ) {
			$sidebar_class = ' sidebar-left';
		}
		echo '<div class="container site-content' . $sidebar_class . '"><div class="row"><main id="main" class="site-main ' . $class_col . '" role="main">';
	}
endif;
//
add_action( 'thim_wrapper_loop_end', 'thim_wrapper_loop_end' );
if ( !function_exists( 'thim_wrapper_loop_end' ) ) :
	function thim_wrapper_loop_end() {
		$class_col = thim_wrapper_layout();
		if ( is_404() ) {
			$class_col = 'col-sm-12 full-width';
		}
		echo '</main>';
		if ( $class_col != 'col-sm-12 full-width' ) {
			if ( get_post_type() == "product" ) {
				get_sidebar( 'shop' );
			} elseif ( is_page() ) {
				get_sidebar( 'page' );
			} else {
				get_sidebar();
			}
		}
		echo '</div></div>';
	}
endif;