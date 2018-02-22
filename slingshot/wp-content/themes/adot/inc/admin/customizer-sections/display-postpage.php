<?php
/*
 * Post and Page Display Settings
 */
$display->addSubSection( array(
	'name'     => 'Post & Page',
	'id'       => 'display_postpage',
	'position' => 3,
) );

$display->createOption( array(
	'name'    => 'Single & Page Layout',
	'id'      => 'archive_single_layout',
	'type'    => 'radio-image',
	'options' => array(
		'full-content'  => $url . 'body-full.png',
		'sidebar-left'  => $url . 'sidebar-left.png',
		'sidebar-right' => $url . 'sidebar-right.png'
	),
	'default' => 'sidebar-left'
) );

$display->createOption( array(
	'name'    => 'Hide Breadcrumbs?',
	'id'      => 'archive_single_hide_breadcrumbs',
	'type'    => 'checkbox',
	"desc"    => "Check this box to hide/unhide Breadcrumbs",
	'default' => false,
) );

$display->createOption( array(
	'name'    => 'Hide Title',
	'id'      => 'archive_single_hide_title',
	'type'    => 'checkbox',
	"desc"    => "Check this box to hide/unhide title",
	'default' => false,
) );

$display->createOption( array(
	'name'        => 'Top Image',
	'id'          => 'archive_single_top_image',
	'type'        => 'upload',
	'desc'        => 'Enter URL or Upload an top image file for header',
	'livepreview' => ''
) );

$display->createOption( array(
	'name'        => 'Background Heading Color',
	'id'          => 'archive_single_heading_bg_color',
	'type'        => 'color-opacity',
	'livepreview' => ''
) );

$display->createOption( array(
	'name'    => 'Text Color Heading',
	'id'      => 'archive_single_heading_text_color',
	'type'    => 'color-opacity',
	'default' => '#fff',
) );

$display->createOption( array(
	'name'    => 'Show category',
	'id'      => 'single_show_category',
	'type'    => 'checkbox',
	"desc"    => "show/hidden",
	'default' => false,
) );

$display->createOption( array(
	'name'    => 'Show Date',
	'id'      => 'single_show_date',
	'type'    => 'checkbox',
	"desc"    => "show/hidden",
	'default' => true,
) );

$display->createOption( array(
	'name'    => 'Show Author',
	'id'      => 'single_show_author',
	'type'    => 'checkbox',
	"desc"    => "show/hidden",
	'default' => true,
) );
$display->createOption( array(
	'name'    => 'Show Comment',
	'id'      => 'single_show_comment',
	'type'    => 'checkbox',
	"desc"    => "show/hidden",
	'default' => true,
) );