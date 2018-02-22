<?php
$woocommerce->addSubSection( array(
	'name'     => 'Product Page',
	'id'       => 'woo_single',
	'position' => 2,
) );


$woocommerce->createOption( array(
	'name'    => 'Select Layout Default',
	'id'      => 'woo_single_layout',
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
	'id'      => 'woo_single_hide_breadcrumbs',
	'type'    => 'checkbox',
	"desc"    => "Check this box to hide/unhide Breadcrumbs",
	'default' => false,
) );

$woocommerce->createOption( array(
	'name'    => 'Hide Title',
	'id'      => 'woo_single_hide_title',
	'type'    => 'checkbox',
	"desc"    => "Check this box to hide/unhide title",
	'default' => false,
) );

$woocommerce->createOption( array(
	'name'        => 'Top Image',
	'id'          => 'woo_single_top_image',
	'type'        => 'upload',
	'desc'        => 'Enter URL or Upload an top image file for header',
	'livepreview' => ''
) );

$woocommerce->createOption( array(
	'name'        => 'Background Heading Color',
	'id'          => 'woo_single_heading_bg_color',
	'type'        => 'color-opacity',
	'livepreview' => ''
) );

$woocommerce->createOption( array(
	'name'    => 'Text Color Heading',
	'id'      => 'woo_single_heading_text_color',
	'type'    => 'color-opacity',
	'default' => '#fff',
) );