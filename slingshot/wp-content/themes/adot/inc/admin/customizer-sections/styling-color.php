<?php
$styling->addSubSection( array(
	'name'     => 'Background Color & Text Color',
	'id'       => 'styling_color',
	'position' => 13,
) );


$styling->createOption( array(
	'name'        => 'Body Background Color',
	'id'          => 'body_bg_color',
	'type'        => 'color-opacity',
	'default'     => '#ffffff',
	'livepreview' => '$("body").css("background-color", value);'
) );

$styling->createOption( array(
	'name'        => 'Theme Primary Color',
	'id'          => 'body_primary_color',
	'type'        => 'color-opacity',
	'default'     => '#1dae84',
	'livepreview' => '
		$("a").css("background-color", value);
 	'
) );
$styling->createOption( array(
	'name'        => 'Theme Second Color',
	'id'          => 'body_second_color',
	'type'        => 'color-opacity',
	'default'     => '#333333',
 ) );
$styling->createOption( array(
	'name'        => 'Text Second Color',
	'id'          => 'text_second_color',
	'type'        => 'color-opacity',
	'default'     => '#999999',
) );
