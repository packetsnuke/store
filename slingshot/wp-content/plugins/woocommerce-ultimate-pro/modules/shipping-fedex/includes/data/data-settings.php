<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$smartpost_hubs      = include( 'data-smartpost-hubs.php' );
$smartpost_hubs      = array( '' => __( 'N/A', 'ultimatewoo-pro' ) ) + $smartpost_hubs;
$shipping_class_link = version_compare( WC_VERSION, '2.6', '>=' ) ? admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) : admin_url( 'edit-tags.php?taxonomy=product_shipping_class&post_type=product' );

/**
 * Array of settings
 */
return array(
	'title'            => array(
		'title'           => __( 'Method Title', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'description'     => __( 'This controls the title which the user sees during checkout.', 'ultimatewoo-pro' ),
		'default'         => __( 'FedEx', 'ultimatewoo-pro' ),
		'desc_tip'        => true
	),
	'origin'           => array(
		'title'           => __( 'Origin Postcode', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'description'     => __( 'Enter the postcode for the <strong>sender</strong>.', 'ultimatewoo-pro' ),
		'default'         => '',
		'desc_tip'        => true
    ),
    'packing'           => array(
		'title'           => __( 'Packages', 'ultimatewoo-pro' ),
		'type'            => 'title',
		'description'     => __( 'The following settings determine how items are packed before being sent to FedEx.', 'ultimatewoo-pro' ),
    ),
	'packing_method'   => array(
		'title'           => __( 'Parcel Packing Method', 'ultimatewoo-pro' ),
		'type'            => 'select',
		'default'         => '',
		'class'           => 'packing_method',
		'options'         => array(
			'per_item'       => __( 'Default: Pack items individually', 'ultimatewoo-pro' ),
			'box_packing'    => __( 'Recommended: Pack into boxes with weights and dimensions', 'ultimatewoo-pro' ),
		),
	),
	'boxes'  => array(
		'type'            => 'box_packing'
	),
    'rates'           => array(
		'title'           => __( 'Rates and Services', 'ultimatewoo-pro' ),
		'type'            => 'title',
		'description'     => __( 'The following settings determine the rates you offer your customers.', 'ultimatewoo-pro' ),
    ),
    'residential'      => array(
		'title'           => __( 'Residential', 'ultimatewoo-pro' ),
		'label'           => __( 'Default to residential delivery', 'ultimatewoo-pro' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'description'     => __( 'Enables residential flag. If you account has Address Validation enabled, this will be turned off/on automatically.', 'ultimatewoo-pro' ),
		'desc_tip'    => true,
	),
    'insure_contents'      => array(
		'title'       => __( 'Insurance', 'ultimatewoo-pro' ),
		'label'       => __( 'Enable Insurance', 'ultimatewoo-pro' ),
		'type'        => 'checkbox',
		'default'     => 'yes',
		'desc_tip'    => true,
		'description' => __( 'Sends the package value to FedEx for insurance.', 'ultimatewoo-pro' ),
	),
	'fedex_one_rate'      => array(
		'title'       => __( 'Fedex One', 'ultimatewoo-pro' ),
		'label'       => sprintf( __( 'Enable %sFedex One Rates%s', 'ultimatewoo-pro' ), '<a href="https://www.fedex.com/us/onerate/" target="_blank">', '</a>' ),
		'type'        => 'checkbox',
		'default'     => 'no',
		'desc_tip'    => true,
		'description' => __( 'Fedex One Rates will be offered if the items are packed into a valid Fedex One box, and the origin and destination is the US.', 'ultimatewoo-pro' ),
	),
	'direct_distribution' => array(
		'title'       => __( 'International Ground Direct Distribution', 'ultimatewoo-pro' ),
		'label'       => __( 'Enable direct distribution Rates.', 'ultimatewoo-pro' ),
		'type'        => 'checkbox',
		'default'     => 'no',
		'desc_tip'    => true,
		'description' => __( 'Enable to get direct distribution rates if your account has this enabled.  For US to Canada or Canada to US shipments.', 'ultimatewoo-pro' ),
	),
	'request_type'     => array(
		'title'           => __( 'Request Type', 'ultimatewoo-pro' ),
		'type'            => 'select',
		'default'         => 'LIST',
		'class'           => '',
		'desc_tip'        => true,
		'options'         => array(
			'LIST'        => __( 'List rates', 'ultimatewoo-pro' ),
			'ACCOUNT'     => __( 'Account rates', 'ultimatewoo-pro' ),
		),
		'description'     => __( 'Choose whether to return List or Account (discounted) rates from the API.', 'ultimatewoo-pro' )
	),
	'smartpost_hub'           => array(
		'title'           => __( 'Fedex SmartPost Hub', 'ultimatewoo-pro' ),
		'type'            => 'select',
		'description'     => __( 'Only required if using SmartPost.', 'ultimatewoo-pro' ),
		'desc_tip'        => true,
		'default'         => '',
		'options'         => $smartpost_hubs
    ),
	'offer_rates'   => array(
		'title'           => __( 'Offer Rates', 'ultimatewoo-pro' ),
		'type'            => 'select',
		'description'     => '',
		'default'         => 'all',
		'options'         => array(
		    'all'         => __( 'Offer the customer all returned rates', 'ultimatewoo-pro' ),
		    'cheapest'    => __( 'Offer the customer the cheapest rate only, anonymously', 'ultimatewoo-pro' ),
		),
    ),
	'services'  => array(
		'type'            => 'services'
	),
);
