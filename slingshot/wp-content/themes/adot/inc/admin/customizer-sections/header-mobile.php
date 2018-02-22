<?php

// main menu

$header->addSubSection( array(
	'name'     =>  __('Mobile Menu','thim'),
	'id'       => 'display_mobile_menu',
	'position' => 16,
) );


$header->createOption( array( "name"    => __("Background color","thim"),
							  "desc"    => "Pick a background color for main menu",
							  "id"      => "bg_mobile_menu_color",
							  "default" => "#222",
							  "type"    => "color-opacity"
) );


$header->createOption( array( "name"    => __("Text color","thim"),
							  "desc"    => __("Pick a text color for main menu","thim"),
							  "id"      => "mobile_menu_text_color",
							  "default" => "#d6d6d6",
							  "type"    => "color-opacity"
) );
$header->createOption( array( "name"    => __("Text Hover color","thim"),
							  "desc"    => __("Pick a text hover color for main menu","thim"),
							  "id"      => "mobile_menu_text_hover_color",
							  "default" => "#01b888",
							  "type"    => "color-opacity"
) );


$header->createOption( array( "name"    => __("Font Size","thim"),
							  "desc"    => "Default is 13",
							  "id"      => "font_size_mobile_menu",
							  "default" => "13px",
							  "type"    => "select",
							  "options" => $font_sizes
) );
