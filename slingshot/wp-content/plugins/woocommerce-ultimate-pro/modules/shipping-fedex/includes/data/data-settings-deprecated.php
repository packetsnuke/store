<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$freight_classes     = include( 'data-freight-classes.php' );
$smartpost_hubs      = include( 'data-smartpost-hubs.php' );
$smartpost_hubs      = array( '' => __( 'N/A', 'ultimatewoo-pro' ) ) + $smartpost_hubs;
$shipping_class_link = version_compare( WC_VERSION, '2.6', '>=' ) ? admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) : admin_url( 'edit-tags.php?taxonomy=product_shipping_class&post_type=product' );

/**
 * Array of settings
 */
return array(
	'enabled'          => array(
		'title'           => __( 'Enable FedEx', 'ultimatewoo-pro' ),
		'type'            => 'checkbox',
		'label'           => __( 'Enable this shipping method', 'ultimatewoo-pro' ),
		'default'         => 'no'
	),
	'debug'      => array(
		'title'           => __( 'Debug Mode', 'ultimatewoo-pro' ),
		'label'           => __( 'Enable debug mode', 'ultimatewoo-pro' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'desc_tip'    => true,
		'description'     => __( 'Enable debug mode to show debugging information on the cart/checkout.', 'ultimatewoo-pro' )
	),
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
    'availability'  => array(
		'title'           => __( 'Method Availability', 'ultimatewoo-pro' ),
		'type'            => 'select',
		'default'         => 'all',
		'class'           => 'availability',
		'options'         => array(
			'all'            => __( 'All Countries', 'ultimatewoo-pro' ),
			'specific'       => __( 'Specific Countries', 'ultimatewoo-pro' ),
		),
	),
	'countries'        => array(
		'title'           => __( 'Specific Countries', 'ultimatewoo-pro' ),
		'type'            => 'multiselect',
		'class'           => 'chosen_select',
		'css'             => 'width: 450px;',
		'default'         => '',
		'options'         => WC()->countries->get_allowed_countries(),
	),
    'api'              => array(
		'title'           => __( 'API Settings', 'ultimatewoo-pro' ),
		'type'            => 'title',
		'description'     => __( 'Your API access details are obtained from the FedEx website. After signup, get a <a href="https://www.fedex.com/us/developer/web-services/process.html?tab=tab2">developer key here</a>. After testing you can get a <a href="https://www.fedex.com/us/developer/web-services/process.html?tab=tab4">production key here</a>.', 'ultimatewoo-pro' ),
    ),
    'account_number'           => array(
		'title'           => __( 'FedEx Account Number', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'description'     => '',
		'default'         => ''
    ),
    'meter_number'           => array(
		'title'           => __( 'Fedex Meter Number', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'description'     => '',
		'default'         => ''
    ),
    'api_key'           => array(
		'title'           => __( 'Web Services Key', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'description'     => '',
		'default'         => '',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
    'api_pass'           => array(
		'title'           => __( 'Web Services Password', 'ultimatewoo-pro' ),
		'type'            => 'password',
		'description'     => '',
		'default'         => '',
		'custom_attributes' => array(
			'autocomplete' => 'off'
		)
    ),
    'production'      => array(
		'title'           => __( 'Production Key', 'ultimatewoo-pro' ),
		'label'           => __( 'This is a production key', 'ultimatewoo-pro' ),
		'type'            => 'checkbox',
		'default'         => 'no',
		'desc_tip'    => true,
		'description'     => __( 'If this is a production API key and not a developer key, check this box.', 'ultimatewoo-pro' )
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
	'freight'           => array(
		'title'           => __( 'FedEx LTL Freight', 'ultimatewoo-pro' ),
		'type'            => 'title',
		'description'     => __( 'If your account supports Freight, we need some additional details to get LTL rates. Note: These rates require the customers CITY so won\'t display until checkout.', 'ultimatewoo-pro' ),
    ),
    'freight_enabled'      => array(
		'title'           => __( 'Enable', 'ultimatewoo-pro' ),
		'label'           => __( 'Enable Freight', 'ultimatewoo-pro' ),
		'type'            => 'checkbox',
		'default'         => 'no'
	),
	'freight_number' => array(
		'title'       => __( 'FedEx Freight Account Number', 'ultimatewoo-pro' ),
		'type'        => 'text',
		'description' => '',
		'default'     => '',
		'placeholder' => __( 'Defaults to your main account number', 'ultimatewoo-pro' )
	),
	'freight_billing_street'           => array(
		'title'           => __( 'Billing Street Address', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_billing_street_2'           => array(
		'title'           => __( 'Billing Street Address', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_billing_city'           => array(
		'title'           => __( 'Billing City', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_billing_state'           => array(
		'title'           => __( 'Billing State Code', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_billing_postcode'           => array(
		'title'           => __( 'Billing ZIP / Postcode', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_billing_country'           => array(
		'title'           => __( 'Billing Country Code', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_shipper_street'           => array(
		'title'           => __( 'Shipper Street Address', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_shipper_street_2'           => array(
		'title'           => __( 'Shipper Street Address 2', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_shipper_city'           => array(
		'title'           => __( 'Shipper City', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'default'         => ''
    ),
    'freight_shipper_state'           => array(
		'title'           => __( 'Shipper State Code', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_shipper_postcode'           => array(
		'title'           => __( 'Shipper ZIP / Postcode', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_shipper_country'           => array(
		'title'           => __( 'Shipper Country Code', 'ultimatewoo-pro' ),
		'type'            => 'text',
		'default'         => '',
    ),
    'freight_shipper_residential'           => array(
    	'title'           => __( 'Residential', 'ultimatewoo-pro' ),
		'label'           => __( 'Shipper Address is Residential?', 'ultimatewoo-pro' ),
		'type'            => 'checkbox',
		'default'         => 'no'
    ),
    'freight_class'           => array(
		'title'           => __( 'Default Freight Class', 'ultimatewoo-pro' ),
		'description'     => sprintf( __( 'This is the default freight class for shipments. This can be overridden using <a href="%s">shipping classes</a>', 'ultimatewoo-pro' ), $shipping_class_link ),
		'type'            => 'select',
		'default'         => '50',
		'options'         => $freight_classes
    ),
);
