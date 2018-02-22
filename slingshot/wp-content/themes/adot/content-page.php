<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package thim
 */
?>
<?php
//$postid = get_the_ID();
//$hide_title_and_subtitle = '';
//if ( isset( $theme_options_data['thim_post_page_hide_title'] ) ) {
//	$hide_title_and_subtitle = $theme_options_data['thim_post_page_hide_title'];
//}
//$using_custom_heading = get_post_meta( $postid, 'thim_mtb_using_custom_heading', true );
//if ( $using_custom_heading ) {
//	$hide_title_and_subtitle = get_post_meta( $postid, 'thim_mtb_hide_title_and_subtitle', true );
//}
//if ( $hide_title_and_subtitle == '1' ) {
//
//}else{
//	 the_title( '<h2 class="page-entry-title">', '</h2>' );
//}
$class   = array();
$class[] = 'page-detail';
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $class ); ?>>
	<?php
//	$hide_title           = $custom_title = $subtitle = '';
//	$heading_style        = get_post_meta( get_the_ID(), 'thim_mtb_heading_style', true );
//	$using_custom_heading = get_post_meta( get_the_ID(), 'thim_mtb_using_custom_heading', true );
//	if ( $using_custom_heading ) {
//		$hide_title   = get_post_meta( get_the_ID(), 'thim_mtb_hide_title_and_subtitle', true );
//		$custom_title = get_post_meta( get_the_ID(), 'thim_mtb_custom_title', true );
//		$subtitle     = get_post_meta( get_the_ID(), 'thim_subtitle', true );
//	}
//
//	if ( $hide_title != '1' && ( $heading_style == 'center' || $heading_style == 'default' ) ) {
//		if ( $custom_title ) {
//			echo '<h1 class="entry-title-' . $heading_style . '"><span>' . $custom_title . '</span></h1>';
//		} else {
//			the_title( '<h1 class="entry-title-' . $heading_style . '"><span>', '</span></h1>' );
//		}
//		if ( $subtitle ) {
//			echo '<p>' . $subtitle . '</p>';
//		}
//	}

	//var_dump( $heading_style );
	?>
	<div class="entry-content">
		<?php the_content(); ?>
		<?php
		wp_link_pages( array(
			'before' => '<div class="page-links">' . __( 'Pages:', 'thim' ),
			'after'  => '</div>',
		) );
		?>
	</div>
	<!-- .entry-content -->
</article><!-- #post-## -->
