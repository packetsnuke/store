<?php
/**
 * Created by PhpStorm.
 * User: Phan Long
 * Date: 4/20/2015
 * Time: 8:19 AM
 */
$title = $data_color = $title_css = $title_style = $thim_animation = $description = $read_more_position = $html = $html_image = $html_readmore = $prefix = $suffix = $html_title = $html_content = '';

$thim_animation .= thim_getCSSAnimation( $instance['css_animation'] );

// custom font heading
$ct_font_heading = $style_font_heading = '';
$ct_font_heading .= ( $instance['title_group']['color_title'] != '' ) ? 'color: ' . $instance['title_group']['color_title'] . ';' : '';
if ( $instance['title_group']['font_heading'] == 'custom' ) {
	$ct_font_heading .= ( $instance['title_group']['custom_heading']['custom_font_size'] != '' ) ? 'font-size: ' . $instance['title_group']['custom_heading']['custom_font_size'] . 'px;' : '';
	$ct_font_heading .= ( $instance['title_group']['custom_heading']['custom_font_weight'] != '' ) ? 'font-weight: ' . $instance['title_group']['custom_heading']['custom_font_weight'] . ';' : '';
	$ct_font_heading .= ( $instance['title_group']['custom_heading']['custom_mg_bt'] != '' ) ? 'margin-bottom: ' . $instance['title_group']['custom_heading']['custom_mg_bt'] . 'px;' : '';
}
if ( $ct_font_heading ) {
	$style_font_heading = 'style="' . $ct_font_heading . '"';
}

//Description
$content = '';
$content = $instance['desc_group']['content'];
if ( $instance['desc_group']['content'] != '' ) {
	$color_desc .= ( $instance['desc_group']['color_description'] ) ? 'color: ' . $instance['desc_group']['color_description'] . ';' : '';
	$color_desc .= ( $instance['desc_group']['custom_font_size_des'] ) ? 'font-size: ' . $instance['desc_group']['custom_font_size_des'] . 'px;line-height: ' . ( $instance['desc_group']['custom_font_size_des'] + 2 ) . 'px;' : '';
	$color_desc .= ( $instance['desc_group']['custom_font_weight'] ) ? 'font-weight: ' . $instance['desc_group']['custom_font_weight'] . ';' : '';
	$color_desc .= ( $instance['desc_group']['margin_bottom'] != '' ) ? 'margin-bottom: ' . $instance['desc_group']['margin_bottom'] . 'px;' : '';

	$color_desc_line = ( $instance['desc_group']['color_description'] ) ? 'background-color: ' . $instance['desc_group']['color_description'] . '' : '';
	if ( $color_desc <> '' ) {
		$style_color_desc = 'style="' . $color_desc . '"';
	}


}
//read more
$read_more  = $read_more_style = '';
$color_fill = '#fff';
$color_fill = ( $instance['read_more_group']['read_more_text_color'] != '' ) ? $instance['read_more_group']['read_more_text_color'] . '' : '#fff';
$read_more .= ( $instance['read_more_group']['bg_read_more_text'] != '' ) ? 'background-color: ' . $instance['read_more_group']['bg_read_more_text'] . ';' : '';
$read_more .= ( $instance['read_more_group']['read_more_text_color'] != '' ) ? 'color: ' . $instance['read_more_group']['read_more_text_color'] . ';' : '';
$read_more .= ( $instance['read_more_group']['border_read_more_color'] != '' ) ? 'border-color: ' . $instance['read_more_group']['border_read_more_color'] . ';' : '';
$read_more .= ( $instance['read_more_group']['read_more_font_size'] != '' ) ? 'font-size: ' . $instance['read_more_group']['read_more_font_size'] . 'px; line-height: ' . $instance['read_more_group']['read_more_font_size'] . 'px;' : '';
$read_more .= ( $instance['read_more_group']['read_more_font_weight'] != '' ) ? 'font-weight: ' . $instance['read_more_group']['read_more_font_weight'] . ';' : '';

if ( $instance['read_more_group']['bg_read_more_text'] <> '' ) {
	$data_color .= ' data-read-more-bg="' . $instance['read_more_group']['bg_read_more_text'] . '"';
}
if ( $instance['read_more_group']['read_more_text_color'] <> '' ) {
	$data_color .= ' data-text-color="' . $instance['read_more_group']['read_more_text_color'] . '"';
}

if ( $instance['read_more_group']['border_read_more_color'] <> '' ) {
	$data_color .= ' data-text-border="' . $instance['read_more_group']['border_read_more_color'] . '"';
}


if ( $read_more ) {
	$read_more_style = ' style="' . $read_more . '"';
}
//background
$bg = $style_bg = '';
$bg .= ( $instance['collection_bg'] != '' ) ? 'background-color: ' . $instance['collection_bg'] . ';' : '';
if ( $bg ) {
	$style_bg = ' style="' . $bg . '"';
}
$image      = $image_type = '';
$image_type = $instance['image_type'];
$read_more_position .= ( $instance['position'] != '' ) ? $instance['position'] . '' : '';
$align = $instance['text_align_sc'];
$prefix .= '<div class="wrap-box-collection ' . $read_more_position . ' ' . $thim_animation . ' ' . $align . ' " ' . $style_bg . ' >';
$suffix .= '</div>';
//image

$image = wp_get_attachment_image_src( $instance['image'], 'full' );
if ( $image ) {
	$html_image .= '<div class="wrapper-image">';
	$img_icon_size = @getimagesize( $image[0] );
	$html_image .= '<img ' . $style . ' src="' . $image[0] . '" ' . $img_icon_size[3] . ' alt="" />';
	$html_image .= '</div>';

	$title = $instance['title_group']['title'];
	$icon  = $instance['icon'];
	if ( $icon ) {
		$html_icon .= '<div class="icon">';
		$html_icon .= '<i class="fa fa-' . $icon . '"></i>';
		$html_icon .= '</div>';
	}
	if ( $title ) {
		$html_title .= '<div class="widget-title-collection">';
		$html_title .= '<' . $instance['title_group']['size'] . ' class = "collection-title" ' . $style_font_heading . '>';
		$html_title .= $link_prefix . $instance['title_group']['title'] . $link_sufix;
		$html_title .= '</' . $instance['title_group']['size'] . '></div>';
	}

	$read_more_link  = $html_read_more_before = $html_read_more_link_affter = $html_read_more_text = '';
	$read_more_link  = $instance['read_more_group']['link'];
	$read_more_arrow = $instance['read_more_group']['show_arrow'];
	if ( $read_more_link ) {
		$html_read_more_before .= '<a  href="' . $instance['read_more_group']['link'] . '" ' . $read_more_style . ' ' . $data_color . ' >';
		if ( $read_more_arrow ) {
			$button_arrow = ' button-arrow';
		}
		$html_read_more_text .= '<span class="read-more-button sc-btn' . $button_arrow . '" ' . $read_more_style . ' ' . $data_color . '>' . $instance['read_more_group']['read_text'] . '';
		if ( $read_more_arrow ) {
			$html_read_more_text .= '<span class="arrow">
				<svg xmlns="http://www.w3.org/2000/svg" width="40" height="12" viewBox="-30 0 52 12"><path fill="' . $color_fill . ' " d="M22 6l-6-6v5h-46v2h46v5l6-6z"></path></svg></span>';
		}
		$html_read_more_text .= '</span>';
		$html_read_more_link_affter .= '</a>';
	}
	if ( $content != '' ) {
		$html_content .= '<div class="desc-collection' . $class . '">';
		$html_content .= ( $instance['desc_group']['content'] != '' ) ? '<p ' . $style_color_desc . '>' . $instance['desc_group']['content'] . '</p>' : '';
		$html_content .= "</div>";
	}
}


$content_prefix = $content_suffix = '';

//if( $read_more_position == 'overay' || $read_more_position == 'top' ){
$content_prefix .= '<div class="collection-hover">';
$content_prefix .= '<div class="collection-main-content">';
$content_prefix .= '<div class="wrap-content-collection">';
$content_suffix .= '</div></div></div>';

//}else{
//	$content_prefix .= '<div class="wrap-content-collection">';
//	$content_suffix .= '</div>';
//}
$html_container = '';
$html_container .= $content_prefix;
$html_container .= $html_icon;
$html_container .= $html_title;
$html_container .= $html_content;
$html_container .= $html_read_more_text;
$html_container .= $content_suffix;

$html .= $prefix;
$html .= $html_read_more_before;
$html .= $html_image;
if ( $read_more_link == '' && $title == '' && $content == '' ) {
} else {
	$html .= $html_container;
}
$html .= $html_read_more_link_affter;
$html .= $suffix;

echo ent2ncr( $html );
