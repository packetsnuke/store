<?php
//$url = TP_THEME_URI . 'images/admin/layout/';
$postMetaBox = $titan->createMetaBox( array(
	'name'      => 'Portfolio Background Color Hover Effect',
	'post_type' => array( 'portfolio' ),
) );
$postMetaBox->createOption( array(
	'name'    => 'Portfolio Background Color Effect',
	'id'      => 'portfolio_bg_color_ef',
	'type'    => 'color-opacity',
	'default' => ''
) );
//
//$heading_page = $titan->createMetaBox( array(
//	'name'      => 'Heading Layout',
//	'post_type' => array( 'page' ),
//) );
//
//$heading_page->createOption( array(
//	'name'    => __( 'select layout', 'thim' ),
//	'type'    => 'radio-image',
//	'id'      => 'mtb_heading_style',
//	'options' => array(
//		'top_banner' => $url . 'top-heading.jpg',
//		'center'     => $url . 'center.jpg',
//		'default'    => $url . 'default.jpg'
//	),
//	'default' => 'top_banner',
//) );