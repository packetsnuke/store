<?php
/*
 * Post and Page Display Settings
 */
$display->addSubSection( array(
	'name'     => 'Archive',
	'id'       => 'display_archive',
	'position' => 2,
) );


$display->createOption( array(
	'name'    => 'Archive Layout',
	'id'      => 'archive_cate_layout',
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
	'id'      => 'archive_cate_hide_breadcrumbs',
	'type'    => 'checkbox',
	"desc"    => "Check this box to hide/unhide Breadcrumbs",
	'default' => false,
) );

$display->createOption( array(
	'name'    => 'Hide Title',
	'id'      => 'archive_cate_hide_title',
	'type'    => 'checkbox',
	"desc"    => "Check this box to hide/unhide title",
	'default' => false,
) );

$display->createOption( array(
	'name'        => 'Top Image',
	'id'          => 'archive_cate_top_image',
	'type'        => 'upload',
	'desc'        => 'Enter URL or Upload an top image file for header',
	'livepreview' => ''
) );

$display->createOption( array(
	'name'        => 'Background Heading Color',
	'id'          => 'archive_cate_heading_bg_color',
	'type'        => 'color-opacity',
	'livepreview' => ''
) );

$display->createOption( array(
	'name'    => 'Text Color Heading',
	'id'      => 'archive_cate_heading_text_color',
	'type'    => 'color-opacity',
	'default' => '#fff',
) );
$display->createOption( array(
	'name'    => 'Number Category',
	'id'      => 'archive_number_cat',
	'type'    => 'text',
	'default' => '6',
	'desc'    => 'enter the number(ex: all,1,2,3......)',
) );

$display->createOption( array(
	'name'    => 'Select Style Default',
	'id'      => 'archive_style_layout',
	'type'    => 'select',
	'options' => array(
		'style-1'  => 'Style 1',
		'style-2'  => 'Style 2',
		'style-3'  => 'Style 3',
		'masonry'  => 'Masonry',
		'timeline' => 'Timeline',
	),
	'default' => 'style-1',
) );
$display->createOption( array(
	'name'    => 'Select Columns',
	'id'      => 'archive_style_columns',
	'type'    => 'select',
	'desc'    => 'This config will work for masonry layout',
	'options' => array(
		'col-2' => '2 Columns',
		'col-3' => '3 Columns',
		'col-4' => '4 Columns',
	),
	'default' => 'col-3',
) );

//$display->createOption( array(
//	'name'        => 'Show Option Heading',
//	'id'          => 'archive_show_option',
//	'type'        => 'select',
//	'options'     => array(
//		'category'    => 'Category List',
//		'description' => 'Category Description'
//	),
//	'default'     => 'category',
//	'livepreview' => ''
//) );

$display->createOption( array(
	'name'    => 'Excerpt Length',
	'id'      => 'archive_excerpt_length',
	'type'    => 'number',
	"desc"    => "Enter the number of words you want to cut from the content to be the excerpt of search and archive and portfolio page.",
	'default' => '20',
	'max'     => '100',
	'min'     => '10',
) );


$display->createOption( array(
	'name'    => 'Show category',
	'id'      => 'show_category',
	'type'    => 'checkbox',
	"desc"    => "show/hidden",
	'default' => true,
) );

$display->createOption( array(
	'name'    => 'Show Date',
	'id'      => 'show_date',
	'type'    => 'checkbox',
	"desc"    => "show/hidden",
	'default' => true,
) );

$display->createOption( array(
	'name'    => 'Show Author',
	'id'      => 'show_author',
	'type'    => 'checkbox',
	"desc"    => "show/hidden",
	'default' => true,
) );

$display->createOption( array(
	'name'    => 'Show Comment',
	'id'      => 'show_comment',
	'type'    => 'checkbox',
	"desc"    => "show/hidden",
	'default' => true,
) );
$display->createOption( array(
	'name'    => 'Show Avata',
	'id'      => 'show_avata',
	'type'    => 'checkbox',
	"desc"    => "show/hidden",
	'default' => true,
) );
$display->createOption( array(
	'name'    => 'Show Tag',
	'id'      => 'show_tag',
	'type'    => 'checkbox',
	"desc"    => "show/hidden",
	'default' => true,
) );

$display->createOption( array(
	'name'    => 'Date Format',
	'id'      => 'date_format',
	'type'    => 'text',
	'desc'    => __( '<a href="http://codex.wordpress.org/Formatting_Date_and_Time">Formatting Date and Time</a>', 'thim' ),
	'default' => 'j M Y'
) );