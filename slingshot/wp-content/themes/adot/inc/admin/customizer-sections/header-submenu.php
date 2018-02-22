<?php
$header->addSubSection( array(
	'name'     =>  __('Sub Menu','thim'),
	'id'       => 'display_sub_menu',
	'position' => 16,
) );

$header->createOption( array( "name"    => __("Background color","thim"),
							  "desc"    => "Pick a background color for sub menu",
							  "id"      => "bg_sub_menu_color",
							  "default" => "#fff",
							  "type"    => "color-opacity",
                              'livepreview' => '$("li .sub-menu").css("background-color", value);
                                        $("ul.navbar-nav>li.menu-item-has-children>ul.sub-menu").css("border-top-color", value)'
) );


$header->createOption( array( "name"    => __("Text color","thim"),
							  "desc"    => __("Pick a text color for sub menu","thim"),
							  "id"      => "sub_menu_text_color",
							  "default" => "#636363",
							  "type"    => "color-opacity",
                              'livepreview' => '$("li .sub-menu li a").css("color", value);'
) );
$header->createOption( array( "name"    => __("Text color hover","thim"),
							  "desc"    => __("Pick a text color hover for sub menu","thim"),
							  "id"      => "sub_menu_text_color_hover",
							  "default" => "#01b888",
							  "type"    => "color-opacity"
) );