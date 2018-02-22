<?php
/**
 *	Helper functions
 *	@package UltimateWoo Pro
 *	@author UltimateWoo
 */

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *	WooCommerce dependencies
 */
if ( ! class_exists( 'WC_Dependencies' ) ) {
	require_once 'class-wc-dependencies.php';
}

/**
 *	WC Detection
 *	@return (boolean) True if WooCommerce is active, else false
 */
if ( ! function_exists( 'is_woocommerce_active' ) ) {
	function is_woocommerce_active() {
		return WC_Dependencies::woocommerce_active_check();
	}
}

/**
 *	Get the UltimateWoo settings
 *	@return (array) Plugin settings or empty array
 */
function ultimatewoo_get_settings() {

	$options = get_option( 'ultimatewoo' );

	if ( ! is_array( $options )  ) {
		$options = array();
	}
	
	return $options;
}

/**
 *	Get the license settings
 *	@return (array) License settings or empty array
 */
function ultimatewoo_get_license_settings() {

	$options = ultimatewoo_get_settings();

	// Return empty array if no options
	if ( ! $options || ! isset( $options['license'] ) || ! $options['license'] ) {
		return array();
	}

	return $options['license'];
}

/**
 *	Get the modules settings
 *	@return (array) Modules settings or empty array
 */
function ultimatewoo_get_modules_settings() {

	$options = ultimatewoo_get_settings();

	if ( $options['modules'] ) {
		return $options['modules'];
	} else {
		return array();
	}
}

/**
 *	Gets the renewal URL for user's license
 *	@return (string) URL that will add user's license to cart as renewal
 */
function ultimatewoo_get_renewal_url() {

	$options = ultimatewoo_get_license_settings();

	// Exit if no license key is saved
	if ( ! $options || ! $options['license_key'] ) {
		return;
	}

	switch ( $options['license_limit'] ) {

		// 1-site
		case 1:
			$price_id = 1;
			break;

		// 5-sites
		case 5:
			$price_id = 3;
			break;

		// Unlimited
		case 0:
			$price_id = 2;
			break;
	}

	$license_key = $options['license_key'];

	$url = add_query_arg( array(
		'edd_action' => 'add_to_cart',
		'download_id' => '2577',
		urlencode( 'edd_options[price_id]' ) => $price_id,
		'renew_key' => $license_key
	), 'https://www.ultimatewoo.com/checkout' );

	return esc_url( $url );
}

/**
 *	Formatted renewal URL - does not output
 *	@return (string) Formatted HTML link
 */
function ultimatewoo_get_renewal_link( $classes = '' ) {
	return sprintf( '&nbsp;<a href="%s" class="%s" target="_blank">%s</a>', ultimatewoo_get_renewal_url(), $classes, __( 'Renew License', 'ultimatewoo-pro' ) );
}