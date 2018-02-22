<?php
$styling->addSubSection( array(
	'name'     => 'Layout',
	'id'       => 'styling_layout',
	'position' => 10,
) );
$styling->createOption( array(
	'name'    => 'Select a layout',
	'id'      => 'box_layout',
	'type'    => 'select',
	'options' => array(
		'boxed' => 'Boxed',
		'wide'  => 'Wide',
	),
	'default' 		=> 'wide',
) );
