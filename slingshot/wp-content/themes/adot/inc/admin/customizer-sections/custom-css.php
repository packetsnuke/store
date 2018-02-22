<?php
$custom_css = $titan->createThimCustomizerSection( array(
	'name'     => 'Custom CSS',
	'position' => 100,
) );

/*
 * Archive Display Settings
 */
$custom_css->createOption( array(
	'name'    => 'Custom CSS',
	'id'      => 'custom_css',
	'type'    => 'textarea',
	'desc'    => 'Put your additional CSS rules here',
	'is_code' => true,
) );
