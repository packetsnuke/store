<?php
global $theme_options_data, $wp_query;
/***********custom Top Images*************/
$text_color = $custom_title = $subtitle = $bg_color = $bg_header = $class_full = $text_color_header =
$bg_image = $thim_custom_heading = $cate_top_image_src = $front_title = '';

$hide_breadcrumbs = $hide_title = 0;
// color theme options
$cat_obj = $wp_query->get_queried_object();

if ( isset( $cat_obj->term_id ) ) {
	$cat_ID = $cat_obj->term_id;
} else {
	$cat_ID = "";
}

if ( get_post_type() == "product" ) {
	$prefix = 'thim_woo';
} elseif ( get_post_type() == "portfolio" ) {
	$prefix = 'thim_portfolio';
} else {
	$prefix = 'thim_archive';
}

// single and archive
if ( is_page() || is_single() ) {
	$prefix_inner = '_single_';
	if ( get_post_type() == "portfolio" ) {
		$prefix_inner = '_cate_';
	}
} else {
	if ( is_front_page() || is_home() ) {
		$prefix       = 'thim';
		$prefix_inner = '_front_page_';
		if ( isset( $theme_options_data[ $prefix . $prefix_inner . 'custom_title' ] ) && $theme_options_data[ $prefix . $prefix_inner . 'custom_title' ] <> '' ) {
			$front_title = $theme_options_data[ $prefix . $prefix_inner . 'custom_title' ];
		}
	} else {
		$prefix_inner = '_cate_';
	}
}
// get data for theme customizer
if ( isset( $theme_options_data[ $prefix . $prefix_inner . 'heading_text_color' ] ) && $theme_options_data[ $prefix . $prefix_inner . 'heading_text_color' ] <> '' ) {
	$text_color = $theme_options_data[ $prefix . $prefix_inner . 'heading_text_color' ];
}

if ( isset( $theme_options_data[ $prefix . $prefix_inner . 'heading_bg_color' ] ) && $theme_options_data[ $prefix . $prefix_inner . 'heading_bg_color' ] <> '' ) {
	$bg_color = $theme_options_data[ $prefix . $prefix_inner . 'heading_bg_color' ];
}

if ( isset( $theme_options_data[ $prefix . $prefix_inner . 'top_image' ] ) && $theme_options_data[ $prefix . $prefix_inner . 'top_image' ] <> '' ) {
	$cate_top_image     = $theme_options_data[ $prefix . $prefix_inner . 'top_image' ];
	$cate_top_image_src = $cate_top_image;

	if ( is_numeric( $cate_top_image ) ) {
		$cate_top_attachment = wp_get_attachment_image_src( $cate_top_image, 'full' );
		$cate_top_image_src  = $cate_top_attachment[0];
	}

}
if ( isset( $theme_options_data[ $prefix . $prefix_inner . 'hide_title' ] ) ) {
	$hide_title = $theme_options_data[ $prefix . $prefix_inner . 'hide_title' ];
}

if ( isset( $theme_options_data[ $prefix . $prefix_inner . 'hide_breadcrumbs' ] ) ) {
	$hide_breadcrumbs = $theme_options_data[ $prefix . $prefix_inner . 'hide_breadcrumbs' ];
}

if ( is_page() || is_single() ) {
	$postid               = get_the_ID();
	$using_custom_heading = get_post_meta( $postid, 'thim_mtb_using_custom_heading', true );
	if ( $using_custom_heading ) {
		$text_color       = $bg_color = $cate_top_image_src = '';
		$hide_title       = get_post_meta( $postid, 'thim_mtb_hide_title_and_subtitle', true );
		$hide_breadcrumbs = get_post_meta( $postid, 'thim_mtb_hide_breadcrumbs', true );
		$custom_title     = get_post_meta( $postid, 'thim_mtb_custom_title', true );
		$subtitle         = get_post_meta( $postid, 'thim_subtitle', true );
		$text_color_1     = get_post_meta( $postid, 'thim_mtb_text_color', true );
		if ( $text_color_1 <> '' ) {
			$text_color = $text_color_1;
		}

		$bg_color_1 = get_post_meta( $postid, 'thim_mtb_bg_color', true );
		if ( $bg_color_1 <> '' ) {
			$bg_color = $bg_color_1;
		}

		$cate_top_image = get_post_meta( $postid, 'thim_mtb_top_image', true );
		if ( $cate_top_image ) {
			$post_page_top_attachment = wp_get_attachment_image_src( $cate_top_image, 'full' );
			$cate_top_image_src       = $post_page_top_attachment[0];
		}
	}
} else {
	$thim_custom_heading = get_tax_meta( $cat_ID, 'thim_custom_heading', true );
	if ( $thim_custom_heading == 'custom' ) {
		$text_color   = $bg_color = $cate_top_image_src = '';
		$text_color_1 = get_tax_meta( $cat_ID, $prefix . '_cate_heading_text_color', true );
		$bg_color_1   = get_tax_meta( $cat_ID, $prefix . '_cate_heading_bg_color', true );
		if ( $text_color_1 != '#' ) {
			$text_color = $text_color_1;
		}
		if ( $bg_color_1 != '#' ) {
			$bg_color = $bg_color_1;
		}
		$hide_breadcrumbs = get_tax_meta( $cat_ID, $prefix . '_cate_hide_breadcrumbs', true );
		$hide_title       = get_tax_meta( $cat_ID, $prefix . '_cate_hide_title', true );
		$cate_top_image   = get_tax_meta( $cat_ID, $prefix . '_top_image', true );
		if ( $cate_top_image ) {
			$cate_top_image_src = $cate_top_image['src'];
		}
	}
}

// reset default
$postid               = get_the_ID();
$using_custom_heading = get_post_meta( $postid, 'thim_mtb_using_custom_heading', true );
//$text_color       = ( $text_color == '#' ) ? '' : $text_color;
//$bg_color         = ( $bg_color == '#' ) ? '' : $bg_color;
$hide_title           = ( $hide_title == 'on' ) ? '1' : $hide_title;
$hide_breadcrumbs     = ( $hide_breadcrumbs == 'on' ) ? '1' : $hide_breadcrumbs;
$using_custom_heading = ( $using_custom_heading == 'on' ) ? '1' : $using_custom_heading;
// css
$c_css_style = $css_line = '';
$c_css_style .= ( $text_color != '' ) ? 'color: ' . $text_color . ';' : '';
$c_css_style .= ( $bg_color != '' ) ? 'background-color: ' . $bg_color . ';' : '';
$css_line .= ( $text_color != '' ) ? 'background-color: ' . $text_color . ';' : '';

//css background and color
$c_css = ( $c_css_style != '' ) ? 'style="' . $c_css_style . '"' : '';

$c_css_1 = ( $bg_color != '' ) ? 'style="background-color:' . $bg_color . '"' : '';
// css inline line
$c_css_line = ( $css_line != '' ) ? 'style="' . $css_line . '"' : '';


$top_images = ( $cate_top_image_src != '' ) ? '<img alt="" src="' . $cate_top_image_src . '" /><span class="overlay-top-header" ' . $c_css . '></span>' : '';
// show title and category

?>
<?php if ( $hide_title != '1' ) { ?>
	<div class="top_site_main<?php if ( $top_images == '' ) {
		echo ' top-site-no-image';
	} ?>" <?php echo ent2ncr( $c_css ); ?>>
		<?php echo ent2ncr( $top_images ); ?>
		<div class="page-title-wrapper">
			<div class="banner-wrapper container">
				<?php
				if ( is_single() ) {
					$typography = 'h2';
				} else {
					$typography = 'h1';
				}
				if ( ( is_page() || is_single() ) && get_post_type() != 'product' ) {
					if ( is_single() ) {
						if ( get_post_type() == "portfolio" ) {
							echo '<' . $typography . '>' . __( 'Portfolio', 'thim' ) . '</' . $typography . '>';
						} else {
							$category    = get_the_category();
							$category_id = get_cat_ID( $category[0]->cat_name );
							echo ' <' . $typography . '>' . get_category_parents( $category_id, false, " " );
							echo '</' . $typography . '>';
							thim_list_category();
						}
					} else {
						echo '<' . $typography . '>';
						echo ( $custom_title != '' ) ? $custom_title : get_the_title( get_the_ID() );
						echo '</' . $typography . '>';
						echo ( $subtitle != '' ) ? '<div class="banner-description"><p>' . $subtitle . '</p></div>' : '';
					}
				} elseif ( get_post_type() == 'product' ) {
					echo '<' . $typography . '>';
					woocommerce_page_title();
					echo '</' . $typography . '>';
					thim_list_category_product();
				} elseif ( get_post_type() == "portfolio" ) {
					echo '<' . $typography . '>' . __( 'Portfolio', 'thim' ) . '</' . $typography . '>';
				} elseif ( is_front_page() || is_home() ) {
					echo '<' . $typography . '>';
					echo ( $front_title != '' ) ? $front_title : 'Blog';
					echo '</' . $typography . '>';
					thim_list_category();
				} else {
					$catTitle = single_cat_title( "", false );
					echo '<' . $typography . '>';
					if ( is_search() ) {
						printf( __( 'Search Results for: %s', 'thim' ), '<span>' . get_search_query() . '</span>' );
					} else {
						echo get_cat_name( get_cat_ID( $catTitle ) );
					}
					echo '</' . $typography . '>';
					//thim_list_category();
				}
				echo '<style>
						.list-category li:hover,.list-category .current-cat{border-color:' . $text_color . '}
					</style>';
				?>
			</div>
		</div>
	</div>
<?php } elseif ( $hide_title == '1' && $using_custom_heading == '1' && $theme_options_data['thim_header_position'] != 'header_overlay' ) { ?>
	<div class="top_site_main">
		<?php if ( $top_images != '' ) {
			echo ent2ncr( $top_images );
		} ?>
	</div>
<?php } ?>

<?php if ( $hide_title == '1' && $theme_options_data['thim_header_position'] == 'header_overlay' && $c_css_1 != '' ) { ?>
	<div class="top_site_main<?php if ( $top_images == '' ) {
		echo ' top-site-no-image-custom';
	} ?>" <?php echo ent2ncr( $c_css_1 ); ?>>
		<?php echo ent2ncr( $top_images ); ?>
	</div>
<?php } ?>


<?php
// show breadcrumbs
if ( $hide_breadcrumbs != '1' ) { ?>
	<div class="breadcrumbs-wrapper container">
		<?php
		if ( get_post_type() == 'product' ) {
			woocommerce_breadcrumb();
		} else {
			thim_breadcrumbs();
		}
		?>
	</div>
<?php } ?>

