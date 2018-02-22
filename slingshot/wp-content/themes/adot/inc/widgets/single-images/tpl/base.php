<?php
$link_before = $after_link = $image = $thim_animation = $images_size ='';
$src         = wp_get_attachment_image_src( $instance['image'], $instance['image_size'] );

$thim_animation .= thim_getCSSAnimation( $instance['css_animation'] );
if ( $src ) {
	$images_size = @getimagesize( $src['0'] );
	$image       = '<img src ="' . $src['0'] . '" ' . $images_size['3'] . ' alt=""/>';
}
if ( $instance['image_link'] ) {
	$link_before = '<a href="' . $instance['image_link'] . '">';
	$after_link  = "</a>";
}
//
echo '<div class="single-image ' . $instance['image_alignment'] . $thim_animation . '">' . $link_before . $image . $after_link . '</div>';