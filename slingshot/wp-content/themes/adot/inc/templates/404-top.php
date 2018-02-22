<?php
global $theme_options_data;
$bg_color = $cate_top_image_src = '';
if ( isset( $theme_options_data['thim_404_heading_bg_color'] ) && $theme_options_data['thim_404_heading_bg_color'] <> '' ) {
	$bg_color = $theme_options_data['thim_404_heading_bg_color'];
}

if ( isset( $theme_options_data['thim_404_top_image'] ) && $theme_options_data['thim_404_top_image'] <> '' ) {
	$cate_top_image     = $theme_options_data['thim_404_top_image'];
	$cate_top_image_src = $cate_top_image;

	if ( is_numeric( $cate_top_image ) ) {
		$cate_top_attachment = wp_get_attachment_image_src( $cate_top_image, 'full' );
		$cate_top_image_src  = $cate_top_attachment[0];
	}

}
$bg_color = ( $bg_color == '#' ) ? '' : $bg_color;
// css
$c_css_style = '';
$c_css_style .= ( $bg_color != '' ) ? 'background-color: ' . $bg_color . ';' : '';
//css background and color
$c_css = ( $c_css_style != '' ) ? 'style="' . $c_css_style . '"' : '';


$top_images = ( $cate_top_image_src != '' ) ? '<img src="' . $cate_top_image_src . '" /><span class="overlay-top-header" ' . $c_css . '></span>' : '';
// show title and category
 if ( isset( $theme_options_data['thim_header_position'] ) && $theme_options_data['thim_header_position'] == 'header_overlay' ) {
	?>
	<div class="top_site_main<?php if ( $top_images == '' ) {
		echo ' top-site-no-image-custom';
	} ?>" <?php echo ent2ncr( $c_css ); ?>><?php echo ent2ncr( $top_images ); ?></div>
<?php } ?>