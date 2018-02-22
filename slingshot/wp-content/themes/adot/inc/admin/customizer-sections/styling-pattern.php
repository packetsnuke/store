<?php
$styling->addSubSection( array(
	'name'     => 'Pattern',
	'id'       => 'styling_pattern',
	'position' => 11,
) );


$styling->createOption( array(
	'name'    => 'Background Pattern',
	'id'      => 'user_bg_pattern',
	'type'    => 'checkbox',
	"desc"    => "Check the box to display a pattern in the background. If checked, select the pattern from below.",
	'default' => false,
) );

$styling->createOption( array(
	'name'    => 'Select a Background Pattern',
	'id'      => 'bg_pattern',
	'type'    => 'radio-image',
	'options' => array(
		get_template_directory_uri() . "/images/patterns/pattern1.png"  => get_template_directory_uri() . "/images/patterns/pattern1.png",
		get_template_directory_uri() . "/images/patterns/pattern2.png"  => get_template_directory_uri() . "/images/patterns/pattern2.png",
		get_template_directory_uri() . "/images/patterns/pattern3.png"  => get_template_directory_uri() . "/images/patterns/pattern3.png",
		get_template_directory_uri() . "/images/patterns/pattern4.png"  => get_template_directory_uri() . "/images/patterns/pattern4.png",
		get_template_directory_uri() . "/images/patterns/pattern5.png"  => get_template_directory_uri() . "/images/patterns/pattern5.png",
		get_template_directory_uri() . "/images/patterns/pattern6.png"  => get_template_directory_uri() . "/images/patterns/pattern6.png",
		get_template_directory_uri() . "/images/patterns/pattern7.png"  => get_template_directory_uri() . "/images/patterns/pattern7.png",
		get_template_directory_uri() . "/images/patterns/pattern8.png"  => get_template_directory_uri() . "/images/patterns/pattern8.png",
		get_template_directory_uri() . "/images/patterns/pattern9.png"  => get_template_directory_uri() . "/images/patterns/pattern9.png",
		get_template_directory_uri() . "/images/patterns/pattern10.png" => get_template_directory_uri() . "/images/patterns/pattern10.png",
	)
    //,'livepreview' => '$("body").css("background-images", value);'
) );

$styling->createOption( array(
	'name'        => 'Upload Background',
	'id'          => 'bg_pattern_upload',
	'type'        => 'upload',
	'desc'        => 'Upload your background',
	'livepreview' => ''
) );

$styling->createOption( array(
	'name'    => 'Background Repeat',
	'id'      => 'bg_repeat',
	'type'    => 'select',
	'options' => array(
		'repeat'    => 'repeat',
		'repeat-x'  => 'repeat-x',
		'repeat-y'  => 'repeat-y',
		'no-repeat' => 'no-repeat'
	),
	'default' => 'no-repeat'
) );

$styling->createOption( array(
	'name'    => 'Background Position',
	'id'      => 'bg_position',
	'type'    => 'select',
	'options' => array(
		'left top'      => 'Left Top',
		'left center'   => 'Left Center',
		'left bottom'   => 'Left Bottom',
		'right top'     => 'Right Top',
		'right center'  => 'Right Center',
		'right bottom'  => 'Right Bottom',
		'center top'    => 'Center Top',
		'center center' => 'Center Center',
		'center bottom' => 'Center Bottom'
	),
	'default' => 'center center'
) );

$styling->createOption( array(
	'name'    => 'Background Attachment',
	'id'      => 'bg_attachment',
	'type'    => 'select',
	'options' => array(
		'scroll'  => 'scroll',
		'fixed'   => 'fixed',
		'local'   => 'local',
		'initial' => 'initial',
		'inherit' => 'inherit'
	),
	'default' => 'inherit'
) );

$styling->createOption( array(
	'name'    => 'Background Size',
	'id'      => 'bg_size',
	'type'    => 'select',
	'options' => array(
		'100% 100%' => '100% 100%',
		'contain'   => 'contain',
		'cover'     => 'cover',
		'inherit'   => 'inherit',
		'initial'   => 'initial'
	),
	'default' => 'inherit'
) );