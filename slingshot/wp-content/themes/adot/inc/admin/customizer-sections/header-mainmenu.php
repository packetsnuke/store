<?php
// main menu
$header->addSubSection(array(
    'name' => __('Main Menu', 'thim'),
    'id' => 'display_main_menu',
    'position' => 14,
));

$header->createOption(array("name" => __("Border Color active", "thim"),
    "desc" => __("Pick a border color active for main menu", "thim"),
    "id" => "main_menu_border_color_active",
    "default" => "#fff",
    "type" => "color-opacity"
));

$header->createOption(array("name" => __("Font Size", "thim"),
    "desc" => "Default is 13",
    "id" => "font_size_main_menu",
    "default" => "13px",
    "type" => "select",
    "options" => $font_sizes
));

$header->createOption(array("name" => __("Font Weight", "thim"),
    "desc" => "Default bold",
    "id" => "font_weight_main_menu",
    "default" => "600",
    "type" => "select",
    "options" => array('bold' => 'Bold', 'normal' => 'Normal', '100' => '100', '200' => '200', '300' => '300', '400' => '400', '500' => '500', '600' => '600', '700' => '700', '800' => '800', '900' => '900'),
));
