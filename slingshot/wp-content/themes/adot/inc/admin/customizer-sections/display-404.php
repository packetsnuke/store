<?php
/*
 * Post and Page Display Settings
 */
$display->addSubSection( array(
	'name'     => 'Page 404',
	'id'       => 'display_page_404',
	'position' => 6,
	'desc'     => 'it just works with header option: Overlay'
) );

$display->createOption( array(
	'name'        => 'Top Image',
	'id'          => '404_top_image',
	'type'        => 'upload',
	'desc'        => 'Enter URL or Upload an top image file for header',
	'livepreview' => ''
) );

$display->createOption( array(
	'name'        => 'Background Top Heading Color',
	'id'          => '404_heading_bg_color',
	'type'        => 'color-opacity',
	'default'     => '#181818',
	'livepreview' => ''
) );
