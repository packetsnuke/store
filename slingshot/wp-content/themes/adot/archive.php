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
/* Blog Type */
$select_style = $custom_style = $custom_style_columns = $select_style_columns = '';
if ( isset( $theme_options_data['thim_archive_style_layout'] ) ) {
	$select_style = $theme_options_data['thim_archive_style_layout'];
}
if ( isset( $theme_options_data['thim_archive_style_layout'] ) ) {
	$masonry_columns = $theme_options_data['thim_archive_style_columns'];
}
$custom_style = get_tax_meta( $cat, 'thim_style_archive', true );
if ( $custom_style <> '' ) {
	$select_style = get_tax_meta( $cat, 'thim_style_archive', true );
}
if ( $select_style == "masonry" ) {
	$select_style_columns = 3;
	$custom_style_columns = get_tax_meta( $cat, 'thim_style_archive_columns', true );
	if ( $custom_style_columns <> '' ) {
		$select_style_columns = get_tax_meta( $cat, 'thim_style_archive_columns', true );
	} else {
		$select_style_columns = $masonry_columns;
	}
	$class_style = 'blog-masonry';
	wp_enqueue_script( 'thim-isotope' );
} else {
	$class_style = $select_style;
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
if ( have_posts() ) :
	echo '<div class="content-inner-page">';
	if ( $class_style == 'timeline' ) {
		get_template_part( 'inc/blog-templates/archive', $style_layout );
	} else {
		while ( have_posts() ) : the_post();
			get_template_part( 'inc/blog-templates/content', $style_layout );
		endwhile;
		thim_paging_nav();
	}
	echo '</div>';
else :
	get_template_part( 'content', 'none' );
endif;

echo '</div>';
?>