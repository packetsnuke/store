<?php
$woocommerce->addSubSection( array(
	'name'     => __( 'Sharing', 'thim' ),
	'id'       => 'woo_share',
	'position' => 3,
) );


$woocommerce->createOption( array(
	'name'    => __( 'Facebook', 'thim' ),
	'id'      => 'woo_sharing_facebook',
	'type'    => 'checkbox',
	"desc"    => "Show the facebook sharing option in product.",
	'default' => true,
) );

$woocommerce->createOption( array(
	'name'    => __( 'Twitter', 'thim' ),
	'id'      => 'woo_sharing_twitter',
	'type'    => 'checkbox',
	"desc"    => "Show the twitter sharing option in product.",
	'default' => true,
) );


$woocommerce->createOption( array(
	'name'    => __( 'Google Plus', 'thim' ),
	'id'      => 'woo_sharing_google',
	'type'    => 'checkbox',
	"desc"    => "Show the g+ sharing option in product.",
	'default' => true,
) );

$woocommerce->createOption( array(
	'name'    => __( 'Pinterest', 'thim' ),
	'id'      => 'woo_sharing_pinterest',
	'type'    => 'checkbox',
	"desc"    => "Show the pinterest sharing option in product.",
	'default' => true,
) );

