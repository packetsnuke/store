<?php
/*
 * Front page displays settings: Posts page
 */
$display->addSubSection( array(
	'name'     => 'Frontpage',
	'id'       => 'display_frontpage',
	'position' => 1,
) );

$display->createOption( array(
	'name'    => 'Front Page Layout',
	'id'      => 'front_page_cate_layout',
	'type'    => 'radio-image',
	'options' => array(
		'full-content'  => $url . 'body-full.png',
		'sidebar-left'  => $url . 'sidebar-left.png',
		'sidebar-right' => $url . 'sidebar-right.png'
	),
	'default' => 'full-content'
) );

$display->createOption( array(
	'name'    => 'Hide Breadcrumbs?',
	'id'      => 'front_page_hide_breadcrumbs',
	'type'    => 'checkbox',
	"desc"    => "Check this box to hide/unhide Breadcrumbs",
	'default' => false,
) );

$display->createOption( array(
	'name'    => 'Hide Title',
	'id'      => 'front_page_hide_title',
	'type'    => 'checkbox',
	"desc"    => "Check this box to hide/unhide title",
	'default' => false,
) );

$display->createOption( array(
	'name'        => 'Top Image',
	'id'          => 'front_page_top_image',
	'type'        => 'upload',
	'desc'        => 'Enter URL or Upload an top image file for header',
	'livepreview' => ''
) );

$display->createOption( array(
	'name'        => 'Background Heading Color',
	'id'          => 'front_page_heading_bg_color',
	'type'        => 'color-opacity',
	'livepreview' => ''
) );

$display->createOption( array(
	'name'    => 'Text Color Heading',
	'id'      => 'front_page_heading_text_color',
	'type'    => 'color-opacity',
	'default' => '#fff',
) );

$display->createOption( array(
	'name'    => 'Number Category',
	'id'      => 'front_page_number_cat',
	'type'    => 'text',
	'default' => '6',
	'desc'    => 'enter the number(ex: all,1,2,3......)',
) );

$display->createOption( array(
	'name'    => 'Select Style Default',
	'id'      => 'front_page_style_layout',
	'type'    => 'select',
	'options' => array(
		'style-1' => 'Style 1',
		'style-2' => 'Style 2',
		'style-3' => 'Style 3',
		'masonry' => 'Masonry',
		'timeline' => 'Timeline',
	),
	'default' => 'style-1',
) );

$display->createOption( array(
	'name'    => 'Select Columns',
	'id'      => 'front_page_style_columns',
	'type'    => 'select',
	'desc'    => 'This config will work for masonry layout',
	'options' => array(
		'col-2' => '2 Columns',
		'col-3' => '3 Columns',
		'col-4' => '4 Columns',
	),
	'default' => 'col-3',
) );

$display->createOption( array(
	'name'    => 'Custom Title',
	'id'      => 'front_page_custom_title',
	'type'    => 'text',
	'default' => '',
) );
