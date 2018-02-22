<?php

$header->addSubSection( array(
	'name'     => __( 'Header Layout', 'thim' ),
	'id'       => 'display_header_layout',
	'position' => 13,
) );

$header->createOption( array(
	'name'    => __( 'Select a Layout', 'thim' ),
	'id'      => 'header_style',
	'type'    => 'radio-image',
	'options' => array(
		"header_v1" => get_template_directory_uri( 'template_directory' ) . "/images/admin/header/header_1.png",
		"header_v2" => get_template_directory_uri( 'template_directory' ) . "/images/admin/header/header_2.png",
		"header_v3" => get_template_directory_uri( 'template_directory' ) . "/images/admin/header/header_3.png",
	),
	'default' => 'header_v1',
) );

$header->createOption( array(
	'name'    => __( 'Header Position', 'thim' ),
	'id'      => 'header_position',
	'type'    => 'select',
	'options' => array(
		'header_default' => __( 'Default', 'thim' ),
		'header_overlay' => __( 'Overlay', 'thim' ),
	),
	'default' => 'header_overlay',
) );
$header->createOption( array(
	'name'    => __( 'Header Layout', 'thim' ),
	'id'      => 'header_layout',
	'type'    => 'select',
	'options' => array(
		'header_wide' => __( 'Wide', 'thim' ),
		'header_box'  => __( 'Box', 'thim' ),
	),
	'default' => 'header_wide',
) );

$header->createOption( array(
	'name'    => 'Header Background color',
	'desc'    => 'Pick a background color for header',
	'id'      => 'bg_header_color',
	'default' => '#ffffff',
	'type'    => 'color-opacity'
) );

$header->createOption( array(
	'name'    => 'Header Text color',
	'desc'    => 'Pick a text color for header',
	'id'      => 'header_text_color',
	'default' => '#868686',
	'type'    => 'color-opacity'
) );

$header->createOption( array(
	"name"    => __( "Header Text Hover color", "thim" ),
	"desc"    => __( "Pick a text hover color for header", "thim" ),
	"id"      => "header_text_color_hover",
	"default" => "#fff",
	"type"    => "color-opacity"
) );

$header->createOption( array(
	'name'    => 'Color Border Header',
	'id'      => 'bg_border_header_color',
	'default' => '#222',
	'type'    => 'color-opacity'
) );
