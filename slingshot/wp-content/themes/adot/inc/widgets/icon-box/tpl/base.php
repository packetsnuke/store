<?php
/**
 * Created by PhpStorm.
 * User: Anh Tuan
 * Date: 12/3/2014
 * Time: 9:55 AM
 */

/* setup hover color */
$data_color = $boxes_icon_style = $thim_animation = "";
if ( $instance['color_group']['icon_hover_color'] <> '' ) {
	$data_color .= ' data-icon="' . $instance['color_group']['icon_hover_color'] . '"';
}

$thim_animation .= thim_getCSSAnimation( $instance['css_animation'] );

if ( $instance['color_group']['icon_border_color_hover'] <> '' ) {
	$data_color .= ' data-icon-border="' . $instance['color_group']['icon_border_color_hover'] . '"';
}
if ( $instance['color_group']['icon_bg_color_hover'] <> '' ) {
	$data_color .= ' data-icon-bg="' . $instance['color_group']['icon_bg_color_hover'] . '"';
}

//if ( $instance['read_more_group']['bg_read_more_text_hover'] <> '' ) {
//	$data_color .= ' data-btn-bg="' . $instance['read_more_group']['bg_read_more_text_hover'] . '"';
//}
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
/* end setup hover color */

// icon style
$icon_style = $boxes_icon_style = $bg_widget = $bg_widget_style = '';
$icon_style .= ( $instance['color_group']['icon_border_color'] != '' ) ? 'border: 1px solid ' . $instance['color_group']['icon_border_color'] . ';' : '';
$icon_style .= ( $instance['color_group']['icon_bg_color'] != '' ) ? 'background-color: ' . $instance['color_group']['icon_bg_color'] . ';' : '';
$icon_style .= ( $instance['color_group']['icon_color'] != '' ) ? 'color: ' . $instance['color_group']['icon_color'] . ';' : '';

$icon_style .= ( $instance['width_icon_box'] != '' ) ? 'width: ' . $instance['width_icon_box'] . 'px;height: ' . $instance['width_icon_box'] . 'px;line-height: ' . $instance['width_icon_box'] . 'px;' : '';
$bg_widget .= ( $instance['color_group']['bg_shortcode'] != '' ) ? 'background-color: ' . $instance['color_group']['bg_shortcode'] . ';' : '';

if ( $icon_style ) {
	$boxes_icon_style = 'style="' . $icon_style . '"';
}
if ( $bg_widget ) {
	$bg_widget_style = 'style="' . $bg_widget . '"';
}

 // end icon style

// read more button css
$read_more = $read_more_style = '';
$read_more .= ( $instance['read_more_group']['button_read_more_group']['border_read_more_text'] != '' ) ? 'border-color: ' . $instance['read_more_group']['button_read_more_group']['border_read_more_text'] . ';' : '';
$read_more .= ( $instance['read_more_group']['button_read_more_group']['bg_read_more_text'] != '' ) ? 'background-color: ' . $instance['read_more_group']['button_read_more_group']['bg_read_more_text'] . ';' : '';
$read_more .= ( $instance['read_more_group']['button_read_more_group']['read_more_text_color'] != '' ) ? 'color: ' . $instance['read_more_group']['button_read_more_group']['read_more_text_color'] . ';' : '';

if ( $read_more ) {
	$read_more_style = ' style="' . $read_more . '"';
}
// end
$more_link = $link_prefix = $link_sufix = '';
$prefix = '<div class="wrapper-box-icon ' . $instance['layout_group']['text_align_sc'] . ' ' . $instance['layout_group']['icon_style'] . $thim_animation . '" ' . $data_color . ' ' . $bg_widget_style . '>';
$suffix = '</div>';
//wrapper-box-icon
//// Set link to Box

if ( $instance['read_more_group']['link'] != '' ) {
	if ( $instance['read_more_group']['read_more'] == 'complete_box' ) {
		$prefix .= '<a class="icon-box-link" href="' . esc_url( $instance['read_more_group']['link'] ) . '">';
		$suffix .= '</a>';
	}
	// Display Read More
	if ( $instance['read_more_group']['read_more'] == 'more' ) {
		$more_link = '<a class="smicon-read sc-btn" href="' . $instance['read_more_group']['link'] . '" ' . $read_more_style . ' >';
		$more_link .= $instance['read_more_group']['button_read_more_group']['read_text'];
		$more_link .= '</a>';
	}
	//Box Title
	if ( $instance['read_more_group']['read_more'] == 'title' ) {
		$link_prefix .= '<a class="smicon-box-link" href="' . esc_url( $instance['read_more_group']['link'] ) . '">';
		$link_sufix .= '</a>';
	}
}
// end
$boxes_content_style = $content_style = '';
if ( $instance['layout_group']['pos'] != 'top' ) {
	$boxes_content_style .= ( $instance['width_icon_box'] != '' && $instance['font_awesome_group']['icon'] != 'none' ) ? 'width: calc( 100% - ' . $instance['width_icon_box'] . 'px - 15px);' : '';
	$boxes_content_style .= ( $instance['width_icon_box'] != '' && $instance['font_awesome_group']['icon'] != 'none' ) ? 'width: -webkit-calc( 100% - ' . $instance['width_icon_box'] . 'px - 15px);' : '';
}
if ( $boxes_content_style ) {
	$content_style = ' style="' . $boxes_content_style . '"';
}
// show title
$html_title = '';
if ( $instance['title_group']['title'] != '' ) {
 	$html_title .= '<div class="widget-title-icon-box">';
	$html_title .= '<' . $instance['title_group']['size'] . ' class = "icon-box-title" ' . $style_font_heading . '>';
	// Convert || characters into line break
	//$title = str_replace( '||', '<br />', $instance['title_group']['title'] );

	$html_title .= $link_prefix . $instance['title_group']['title'] . $link_sufix;
	$html_title .= '</' . $instance['title_group']['size'] . '></div>';
}
// end show title

/* show icon or custom icon */
$html_icon  = $icon_style = '';
$icon_style = ' ' . $instance['layout_group']['box_icon_style'];
if ( $instance['icon_type'] == 'font-awesome' ) {
	if ( $instance['font_awesome_group']['icon'] == '' ) {
		$instance['font_awesome_group']['icon'] = 'none';
	}
	if ( $instance['font_awesome_group']['icon'] != 'none' ) {
		$html_icon .= '<div class="wrapper-title-icon' . $icon_style . '" ' . $boxes_icon_style . '>';
		$class = 'fa fa-fw fa-' . $instance['font_awesome_group']['icon'];
		$style = '';
		$style .= ( $instance['color_group']['icon_color'] != '' ) ? 'color:' . $instance['color_group']['icon_color'] . ';' : '';
		$style .= ( $instance['font_awesome_group']['icon_size'] != '' ) ? ' font-size:' . $instance['font_awesome_group']['icon_size'] . 'px;' : '';
		$html_icon .= '<span class="icon" style="' . $style . '">';
		$html_icon .= '<i class="' . $class . '" ></i>';
		$html_icon .= '</span></div>';
	}
} else {
	$img = wp_get_attachment_image_src( $instance['font_image_group']['icon_img'], 'full' );
	if ( $img ) {
		$html_icon .= '<div class="wrapper-title-icon' . $icon_style . '" ' . $boxes_icon_style . '>';
		$html_icon .= '<span class="icon icon-images">';
		$style         = $img_icon_size = '';
		$img_icon_size = @getimagesize( $img[0] );
		$html_icon .= '<img ' . $style . ' src="' . $img[0] . '" ' . $img_icon_size[3] . ' alt="" />';
		$html_icon .= '</span></div>';
	}
}
/* end show icon or custom icon */

/* show CONTENT*/
$html_content = $color_desc = $style_color_desc = $color_desc_line = $style_color_desc_line = $class = $line_height = '';
if ( $instance['desc_group']['content'] != '' ) {
	if( $instance['desc_group']['custom_font_size_des'] ){
		$line_height = (int)$instance['desc_group']['custom_font_size_des'] + 7;
	}
	$color_desc .= ( $instance['desc_group']['color_description'] ) ? 'color: ' . $instance['desc_group']['color_description'] . ';' : '';
	$color_desc .= ( $instance['desc_group']['custom_font_size_des'] ) ? 'font-size: ' . $instance['desc_group']['custom_font_size_des'] . 'px;' : '';
	$color_desc .= ( $instance['desc_group']['custom_font_weight'] ) ? 'font-weight: ' . $instance['desc_group']['custom_font_weight'] . '' : '';
	$color_desc .= ( $line_height ) ? 'line-height: ' . $line_height . 'px;' : '';

	$color_desc_line = ( $instance['desc_group']['color_description'] ) ? 'background-color: ' . $instance['desc_group']['color_description'] . '' : '';
	if ( $color_desc <> '' ) {
		$style_color_desc = 'style="' . $color_desc . '"';
	}
	if ( $color_desc_line <> '' ) {
		$style_color_desc_line = 'style="' . $color_desc_line . '"';
	}
	if ( $instance['desc_group']['show_line_bottom'] == '1' || $instance['desc_group']['show_line_bottom'] == 'on' ) {
		$class = ' line-bottom';
	}
	//
	$html_content .= '<div class="desc-icon-box' . $class . '">';
	$html_content .= ( $instance['desc_group']['content'] != '' ) ? '<p ' . $style_color_desc . '>' . $instance['desc_group']['content'] . '</p>' : '';
	$html_content .= $more_link;
	if ( $instance['desc_group']['show_line_bottom'] == '1' || $instance['desc_group']['show_line_bottom'] == 'on'  ) {
		$html_content .= '<span class="line" ' . $style_color_desc_line . '></span>';
	}
	$html_content .= "</div>";
}
//var_dump($instance['desc_group']['show_line_bottom'] );
// html
//start div wrapper-box-icon
$html = $prefix;
$html .= '<div class="smicon-box icon-' . $instance['layout_group']['pos'] . '">';

//$html .= '<div class="wrapper-title-icon">';
// show icon
$html .= $html_icon;
$html .= '<div class="content-inner" ' . $content_style . '>';

// show title
$html .= $html_title;
//$html .= '</div><!--end wrapper-title-icon-->';

// show content
$html .= $html_content;
$html .= '</div>';
//end content-inner
$html .= '<div class="clear"></div>';

$html .= '</div>';
//end smicon-box
//end div wrapper-box-icon
//$html .= $box_border_bottom;
if($instance['layout_group']['icon_style'] =='layout-02'){
	$html .= '<span class="arrow-bottom-left" '.$bg_widget_style.'></span>';
	$html .= '<span class="arrow-bottom-right" '.$bg_widget_style.'></span>';
}
$html .= $suffix;

echo  ent2ncr($html);