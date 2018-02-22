<?php
/*
Author: Lee Willis
Author URI: http://plugins.leewillis.co.uk/
License: GPLv3
*/

if ( is_woocommerce_active() ) {
	if ( is_admin() ) {
		require_once( 'woocommerce-msrp-admin.php' );
	}
	require_once( 'woocommerce-msrp-frontend.php' );
}

register_activation_hook( __FILE__, 'woocommerce_msrp_activate' );

/**
 * Add default option settings on plugin activation
 */
function woocommerce_msrp_activate() {
	add_option( 'woocommerce_msrp_status', 'always', '', true );
	add_option( 'woocommerce_msrp_description', 'MSRP', '', true );
}

//2.5.1