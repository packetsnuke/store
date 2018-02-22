<?php

function activate_woocommerce_catalog_restrictions() {
	install_woocommerce_catalog_restrictions();
}

function install_woocommerce_catalog_restrictions() {
	global $woocommerce, $wc_catalog_restrictions, $wpdb;

	if ( !WC_Catalog_Visibility_Compatibility::is_wc_version_gte_2_1() ) {
		include_once $woocommerce->plugin_path() . '/admin/woocommerce-admin-install.php';
	}

	if ( WC_Catalog_Visibility_Compatibility::use_wp_term_meta_table() ) {
		//Clean up old rules. 
		$wc_term_meta_table = $wpdb->termmeta;
	} else {
		//Clean up old rules. 
		$wc_term_meta_table = $wpdb->prefix . 'woocommerce_termmeta';
	}


	$wpdb->query( "DELETE FROM $wc_term_meta_table WHERE (meta_key = '_wc_restrictions' OR meta_key = '_wc_restrictions_allowed') AND (meta_value = '');" );
	$wpdb->query( "DELETE FROM $wc_term_meta_table WHERE (meta_key = '_wc_restrictions_location' OR meta_key = '_wc_restrictions_locations') AND (meta_value = '');" );

	//Clean up the transients
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_twccr%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_twccr%'" );

	if ( !get_option( 'woocommerce_choose_location_page_id' ) ) {

		wc_create_page( esc_sql( _x( 'choose-location', 'page_slug', 'ultimatewoo-pro' ) ), 'woocommerce_choose_location_page_id', __( 'Your Location', 'ultimatewoo-pro' ), '[location_picker /]' );
	}

	update_option( "woocommerce_catalog_restrictions_db_version", $wc_catalog_restrictions->version );
}
