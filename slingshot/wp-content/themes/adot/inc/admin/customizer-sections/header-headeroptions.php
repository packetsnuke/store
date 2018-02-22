<?php

// header Options
$header->addSubSection( array(
	'name'     => __( 'Sticky Menu', 'thim' ),
	'id'       => 'display_header_menu',
	'position' => 15,
) );

$header->createOption( array(
	'name' => __( 'Sticky Menu on scroll', 'thim' ),
	'desc' => __( 'Check to enable a fixed header when scrolling, uncheck to disable.', 'thim' ),
	'id'   => 'header_sticky',
	'type' => 'checkbox'
) );

$header->createOption( array( 'name'    => 'Config Sticky Menu?',
							  'desc'    => '',
							  'id'      => 'config_att_sticky',
							  'options' => array( 'sticky_same'   => 'The same with main menu',
												  'sticky_custom' => 'Custom'
							  ),
							  'type'    => 'select'
) );

$header->createOption( array( 'name'    => 'Sticky Background color',
							  'desc'    => 'Pick a background color for main menu',
							  'id'      => 'sticky_bg_main_menu_color',
							  'default' => '#222222',
							  'type'    => 'color-opacity'
) );

$header->createOption( array( 'name'    => 'Text color',
							  'desc'    => 'Pick a text color for main menu',
							  'id'      => 'sticky_main_menu_text_color',
							  'default' => '#fff',
							  'type'    => 'color-opacity'
) );

$header->createOption( array( 'name'    => 'Text Hover color',
							  'desc'    => 'Pick a text hover color for main menu',
							  'id'      => 'sticky_main_menu_text_hover_color',
							  'default' => '#01b888',
							  'type'    => 'color-opacity'
) );
$header->createOption( array(
	'name'    => 'Color Border Header',
	'id'      => 'sticky_bg_border_header_color',
	'default' => '#222',
	'type'    => 'color-opacity'
) );