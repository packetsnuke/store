<?php
$des               = $html = $img = $css = $link = $align = $desc_css = $des_style = $title_style = $desc_line_css_style = $desc_line_css = $title = $title_css = '';
$title             = $instance['title_group']['title'];
$title_color       = $instance['title_group']['title_color'];
$title_font_size   = $instance['title_group']['title_font_size'];
$title_font_weight = $instance['title_group']['title_font_weight'];

$des             = $instance['desc_group']['des'];
$des_color       = $instance['desc_group']['des_color'];
$des_font_size   = $instance['desc_group']['des_font_size'];
$des_font_weight = $instance['desc_group']['des_font_weight'];
$align           = $instance['text_alignment'];
$src             = wp_get_attachment_image_src( $instance['image'], 'full' );
if ( $src ) {
	$image = '<img src ="' . $src['0'] . '" ' . ' alt=""/>';
}
$banner_align = '';
$banner_align = 'banner-' . $align . '';
if ( isset( $instance['title_group']['title_color'] ) && $instance['title_group']['title_color'] <> '' ) {
	$title_css .= 'color: ' . $title_color . ';';
}

if ( isset( $instance['title_group']['title_font_size'] ) && $instance['title_group']['title_font_size'] <> '' ) {
	$title_css .= 'font-size: ' . $title_font_size . 'px;';
}

if ( isset( $instance['title_group']['title_font_weight'] ) && $instance['title_group']['title_font_weight'] <> '' ) {
	$title_css .= 'font-weight: ' . $title_font_weight . ';';
}

$title_style = 'style = "' . $title_css . '"';
$des_css .= 'color: ' . $des_color . ';';
$des_css .= 'font-size: ' . $des_font_size . ';';
$des_css .= 'font-weight: ' . $des_font_weight . ';';
$des_style    = 'style = "' . $des_css . '"';
$title_border = $style_border = '';
$title_border = $instance['title_group']['title_border'];
if ( ! $title_border ) {
	$style_border = 'style="border: 0;"';

}
$before = $affter = '';
$link   = $instance['link'];
if ( $link <> '' ) {
	$before = '<a href="' . $link . '" title="' . $title . '" ' . $title_style . '>';
	$affter = '</a>';
}
?>
<div class="banner-text <?php echo esc_attr( $banner_align ); ?>">
	<?php echo ent2ncr( $image ); ?>
	<div class="banner-image-container">
		<div class="wrapper-container">
			<div class="container">
				<h2 class="banner-title" <?php echo ent2ncr( $title_style ); ?>>
					<?php echo ent2ncr( $before ); ?>
					<span <?php echo ent2ncr( $style_border ); ?>><?php echo ent2ncr( $title ) ?></span>
					<?php echo ent2ncr( $affter ); ?>
				</h2>

				<div class="banner-description">
					<p <?php echo ent2ncr( $des_style ) ?> >
						<?php echo ent2ncr( $des ) ?>
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="banner-content">
		</div>
	</div>
</div>