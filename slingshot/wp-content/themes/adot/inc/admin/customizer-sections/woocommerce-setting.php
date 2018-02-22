<?php
$woocommerce->addSubSection( array(
	'name'     => 'Setting',
	'id'       => 'woo_setting',
	'position' => 3,
) );

$woocommerce->createOption( array(
	'name'    => 'Column',
	'id'      => 'woo_product_column',
	'type'    => 'select',
	'options' => array(
		'2' => '2',// column bootstrap
		'3' => '3',
		'4' => '4',
		'5' => '5',
		'6' => '6',
	),
	'default' => '4'
) );
$woocommerce->createOption( array(
	'name'    => 'Number of Products per Page',
	'id'      => 'woo_product_per_page',
	'type'    => 'number',
	'desc'    => 'Insert the number of posts to display per page.',
	'default' => '8',
	'max'     => '100',
) );

$woocommerce->createOption( array(
	'name'    => 'Effect Single Image',
	'id'      => 'woo_set_effect',
	'type'    => 'select',
	'options' => array(
		'zoom_out'     => 'Zoom Out',
		'popup_images' => 'Popup',
	),
	'default' => 'popup_images'
) );

$woocommerce->createOption( array(
	'name'    => 'Show Wishlist in products list',
	'id'      => 'woo_set_show_wishlist',
	'type'    => 'checkbox',
	'default' => true,
) );
$woocommerce->createOption( array(
	'name'    => 'Show QuickView in products list',
	'id'      => 'woo_set_show_qv',
	'type'    => 'checkbox',
	'default' => true,
) );
