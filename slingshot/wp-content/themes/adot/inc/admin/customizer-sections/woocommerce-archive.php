<?php
$woocommerce->addSubSection( array(
	'name'     => 'Category Products',
	'id'       => 'woo_archive',
	'position' => 1,
) );

$woocommerce->createOption( array(
	'name'    => 'Archive Layout',
	'id'      => 'woo_cate_layout',
	'type'    => 'radio-image',
	'options' => array(
		'full-content'  => $url . 'body-full.png',
		'sidebar-left'  => $url . 'sidebar-left.png',
		'sidebar-right' => $url . 'sidebar-right.png'
	),
	'default' => 'full-content'
) );

$woocommerce->createOption( array(
	'name'    => 'Hide Breadcrumbs?',
	'id'      => 'woo_cate_hide_breadcrumbs',
	'type'    => 'checkbox',
	"desc"    => "Check this box to hide/unhide Breadcrumbs",
	'default' => false,
) );

$woocommerce->createOption( array(
	'name'    => 'Hide Title',
	'id'      => 'woo_cate_hide_title',
	'type'    => 'checkbox',
	"desc"    => "Check this box to hide/unhide title",
	'default' => false,
) );

$woocommerce->createOption( array(
	'name'        => 'Top Image',
	'id'          => 'woo_cate_top_image',
	'type'        => 'upload',
	'desc'        => 'Enter URL or Upload an top image file for header',
	'livepreview' => ''
) );

$woocommerce->createOption( array(
	'name'        => 'Background Heading Color',
	'id'          => 'woo_cate_heading_bg_color',
	'type'        => 'color-opacity',
	'livepreview' => ''
) );

$woocommerce->createOption( array(
	'name'    => 'Text Color Heading',
	'id'      => 'woo_cate_heading_text_color',
	'type'    => 'color-opacity',
	'default' => '#fff',
) );
$woocommerce->createOption( array(
	'name'    => 'Number Category',
	'id'      => 'woo_number_cat',
	'type'    => 'text',
	'default' => 'all',
	'desc'    => 'enter the number(ex: all,1,2,3......)',
) );