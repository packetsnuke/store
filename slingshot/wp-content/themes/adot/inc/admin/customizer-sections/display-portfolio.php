<?php
/*
 * Post and Page Display Settings
 */
if ( class_exists( 'THIM_Portfolio' ) ) {
	$display->addSubSection( array(
		'name'     => 'Portfolio',
		'id'       => 'display_portfolio',
		'position' => 12,
	) );


//	$display->createOption( array(
//		'name'    => 'Portfolio Layout',
//		'id'      => 'portfolio_cate_layout',
//		'type'    => 'radio-image',
//		'options' => array(
//			'full-content'  => $url . 'body-full.png',
//			'sidebar-left'  => $url . 'sidebar-left.png',
//			'sidebar-right' => $url . 'sidebar-right.png'
//		),
//		'default' => 'sidebar-left'
//	) );

	$display->createOption( array(
		'name'    => 'Hide Breadcrumbs?',
		'id'      => 'portfolio_cate_hide_breadcrumbs',
		'type'    => 'checkbox',
		"desc"    => "Check this box to hide/unhide Breadcrumbs",
		'default' => false,
	) );

	$display->createOption( array(
		'name'    => 'Hide Title',
		'id'      => 'portfolio_cate_hide_title',
		'type'    => 'checkbox',
		"desc"    => "Check this box to hide/unhide title",
		'default' => false,
	) );

	$display->createOption( array(
		'name'        => 'Top Image',
		'id'          => 'portfolio_cate_top_image',
		'type'        => 'upload',
		'desc'        => 'Enter URL or Upload an top image file for header',
		'livepreview' => ''
	) );

	$display->createOption( array(
		'name'        => 'Background Heading Color',
		'id'          => 'portfolio_cate_heading_bg_color',
		'type'        => 'color-opacity',
		'livepreview' => ''
	) );

	$display->createOption( array(
		'name'    => 'Text Color Heading',
		'id'      => 'portfolio_cate_heading_text_color',
		'type'    => 'color-opacity',
		'default' => '#fff',
	) );

}
