<?php
$data = $titan->createThimCustomizerSection( array(
	'name'		=> 'Import/Export Settings',
	'desc'		=> 'You can export then import settings from one theme to another conveniently without any problem.',
	'position'	=> 202,
	'id'		=> 'import_export',
	'icon'		=> 'fa-hdd-o',
) );

$data->createOption( array(
	'name'    => 'Import Settings',
	'id'      => 'import_setting',
	'type'    => 'customize-import',
	'desc'    => 'Click Upload button then choose a JSON file (.json) from your computer to import settings to this theme.',
) );

$data->createOption( array(
	'name'    => 'Export Settings',
	'id'      => 'export_setting',
	'type'    => 'customize-export',
	'desc'    => 'Simply click Download button to export all your settings to a JSON file (.json).',
) );
