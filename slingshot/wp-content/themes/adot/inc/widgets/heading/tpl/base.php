<?php
$thim_animation = $des = $html = $css = $desc_css = $desc_css_style = $desc_line_css_style = $desc_line_css = '';
$thim_animation .= thim_getCSSAnimation( $instance['css_animation'] );

if ( $instance['textcolor'] ) {
	$css .= 'color:' . $instance['textcolor'] . ';';
}

//foreach ( $instance['custom_font_heading'] as $i => $feature ) :
if ( $instance['font_heading'] == 'custom' ) {
	if ( $instance['custom_font_heading']['custom_font_size'] <> '' ) {
		$css .= 'font-size:' . $instance['custom_font_heading']['custom_font_size'] . 'px;';
		$css .= 'line-height:' . $instance['custom_font_heading']['custom_font_size'] . 'px;';
	}
	if ( $instance['custom_font_heading']['custom_font_weight'] <> '' ) {
		$css .= 'font-weight:' . $instance['custom_font_heading']['custom_font_weight'] . ';';
	}
}
//endforeach;

if ( $css ) {
	$css = ' style="' . $css . '"';
}
$line_height='';
$desc_line_css .= ( $instance['desc_group']['des_color'] != '' ) ? 'background-color: ' . $instance['desc_group']['des_color'] . ';' : '';
if( $instance['desc_group']['des_font_size'] ){
	$line_height = (int)$instance['desc_group']['des_font_size'] + 7;
}
$desc_css .= ( $instance['desc_group']['des_color'] != '' ) ? 'color: ' . $instance['desc_group']['des_color'] . ';' : '';
$desc_css .= ( $instance['desc_group']['des_font_size'] != '' ) ? 'font-size: ' . $instance['desc_group']['des_font_size'] . 'px;' : '';
$desc_css .= ( $instance['desc_group']['des_font_weight'] != '' ) ? 'font-weight: ' . $instance['desc_group']['des_font_weight'] . ';' : '';
$desc_css .= ( $line_height != '' ) ? 'line-height: ' . $line_height . 'px;' : '';
$desc_css .= ( ! $instance['show_line'] ) ? 'padding-bottom: 0;' : '';

if ( $desc_css ) {
	$desc_css_style = ' style="' . $desc_css . '"';
}
if ( $desc_line_css ) {
	$desc_line_css_style = ' style="' . $desc_line_css . '"';
}

/*
 *
 */

$html .= '<div class="sc_heading' . $thim_animation . ' ' . $instance['text_align'] . '" >';
$html .= '<' . $instance['size'] . $css . ' class="title">' . $instance['title'] . '</' . $instance['size'] . '>';
//if ( $instance['desc_group']['des'] <> '' ) {
$html .= '<div class="heading-desc" ' . $desc_css_style . '>' . $instance['desc_group']['des'] .'';
if( $instance['show_line'] == 1 ){
	$html .= '<span class="line" ' . $desc_line_css_style . '></span>';
}
//}
$html .= '</div>';
$html .= '</div>';

echo  ent2ncr($html);