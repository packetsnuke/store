<?php

$footer->addSubSection( array(
	'name'     => __( 'Footer', 'thim' ),
	'id'       => 'display_footer',
	'position' => 10,
) );

$footer->createOption( array(
	'name'                => __( 'Footer Title Font', 'thim' ),
	'id'                  => 'footer_title_font',
	'type'                => 'font-color',
	'show_font_family'    => false,
	'show_font_weight'    => true,
	'show_font_style'     => false,
	'show_line_height'    => true,
	'show_letter_spacing' => false,
	'show_text_transform' => true,
	'show_font_variant'   => false,
	'show_text_shadow'    => false,
	'default'             => array(
		'color-opacity' => '#000',
		'line-height'   => '1em',
		'font-weight'   => '500',
		'font-size'     => '14px',
	),
) );

$footer->createOption( array(
	'name'                => __( 'Footer Text Font', 'thim' ),
	'id'                  => 'footer_text_font',
	'type'                => 'font-color',
	'show_font_family'    => false,
	'show_font_weight'    => true,
	'show_font_style'     => false,
	'show_line_height'    => true,
	'show_letter_spacing' => false,
	'show_text_transform' => false,
	'show_font_variant'   => false,
	'show_text_shadow'    => false,
	'show_preview'        => true,
	'default'             => array(
		'color-opacity' => '#666',
		'line-height'   => '1em',
		'font-weight'   => '500',
		'font-size'     => '14px',
	)
) );

$footer->createOption( array(
	'name'        => 'Background Footer Color',
	'id'          => 'footer_bg_color',
	'type'        => 'color-opacity',
	'default'     => '#f9f9f9',
	'livepreview' => '$("footer#colophon .footer").css("background-color", value);'
) );

$footer->createOption( array(
	'name'    => 'Border Color',
	'id'      => 'footer_border_color',
	'type'    => 'color-opacity',
	'default' => '#e5e5e5',
) );