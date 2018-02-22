<?php
/**
 * The template for displaying archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package thim
 */
?>

<?php
if ( have_posts() ) :
	/* Blog Type */
	$masonry_columns = 'col-3';
	$select_style = $custom_style = $custom_style_columns = $select_style_columns = '';
	if ( isset( $theme_options_data['thim_front_page_style_layout'] ) ) {
		$select_style = $theme_options_data['thim_front_page_style_layout'];
	}
	if ( isset( $theme_options_data['thim_front_page_style_columns'] ) ) {
		$masonry_columns = $theme_options_data['thim_front_page_style_columns'];
	}
	if ( $select_style == "masonry" || ( isset( $_REQUEST['style'] ) && $_REQUEST['style'] == 'blog-masonry' ) ) {
		$select_style_columns = $masonry_columns;
		$class_style          = 'blog-masonry';
	} else {
		$class_style = $select_style;
	}
	$blog_layout     = "style-1";


	if ( isset( $_REQUEST['style'] ) ) {
		$blog_layout = $_REQUEST['style'];
		$class_style = $blog_layout;
	}
	if ( $class_style == 'style-1' ) {
		$style_layout = 'v1';
	} elseif ( $class_style == 'style-2' ) {
		$style_layout = 'v2';
	} elseif ( $class_style == 'style-3' ) {
		$style_layout = 'v3';
	} elseif ( $class_style == 'blog-masonry' ) {
		$style_layout = 'v4';
	} elseif ( $class_style == 'timeline' ) {
		$style_layout = 'timeline';
	}
	/* Start the Loop */
	echo '<div class="article-list clearafter ' . $class_style . ' ' . $select_style_columns . '">';
	?>

	<div class="content-inner-page">
<?php
	if ( $class_style == 'timeline' ) {
		get_template_part( 'inc/blog-templates/archive', $style_layout );
	} else {
		while ( have_posts() ) : the_post();
			get_template_part( 'inc/blog-templates/content', $style_layout );
		endwhile;
	}
	echo '</div>';
	if ( $select_style == "masonry" || ( isset( $_REQUEST['style'] ) && $_REQUEST['style'] == 'blog-masonry' ) ) {
		wp_enqueue_script( 'thim-isotope' );
	}
	if ( $class_style == 'timeline' ) {
		//thim_paging_nav();
	} else {
		thim_paging_nav();
	}
	echo '</div>';
else :
	get_template_part( 'content', 'none' );
endif;