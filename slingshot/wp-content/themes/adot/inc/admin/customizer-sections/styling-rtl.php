<?php
$styling->addSubSection( array(
	'name'     => __('Theme Support','thim'),
	'id'       => 'styling_rtl',
	'position' => 14,
) );

$styling->createOption( array(
	'name'    => __('RTL Support','thim'),
	'id'      => 'rtl_support',
	'type'    => 'checkbox',
	"desc"    => "Enable/Disable",
	'default' => false,
) );
//
//$styling->createOption( array(
//	'name'    => __('Disable Responsive','thim'),
//	'id'      => 'disable_responsive',
//	'type'    => 'checkbox',
//	"desc"    => "Disable",
//	'default' => false,
//) );

$styling->createOption( array(
	'name'    => __('Preload','thim'),
	'id'      => 'preload',
	'type'    => 'checkbox',
	"desc"    => "Enable/Disable",
	'default' => false,
) );