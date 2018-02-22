<?php
// Adds custom Customer columns to the Customer fields list
function woo_ce_extend_customer_fields( $fields = array() ) {

	// WooCommerce Follow-Up Emails
	if( woo_ce_detect_export_plugin( 'wc_followupemails' ) ) {
		$fields[] = array(
			'name' => 'followup_optout',
			'label' => __( 'Follow-Up Emails: Opted Out', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Hear About Us - https://wordpress.org/plugins/woocommerce-hear-about-us/
	if( woo_ce_detect_export_plugin( 'hear_about_us' ) ) {
		$fields[] = array(
			'name' => 'hear_about_us',
			'label' => __( 'Source', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Hear About Us', 'woocommerce-exporter' )
		);
	}

	// Custom Customer fields
	$custom_customers = woo_ce_get_option( 'custom_customers', '' );
	if( !empty( $custom_customers ) ) {
		foreach( $custom_customers as $custom_customer ) {
			if( !empty( $custom_customer ) ) {
				$fields[] = array(
					'name' => $custom_customer,
					'label' => woo_ce_clean_export_label( $custom_customer ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_customer_fields_custom_customer_hover', '%s: %s' ), __( 'Custom Customer', 'woocommerce-exporter' ), $custom_customer )
				);
			}
		}
		unset( $custom_customers, $custom_customer );
	}

	// Custom User fields
	$custom_users = woo_ce_get_option( 'custom_users', '' );
	if( !empty( $custom_users ) ) {
		foreach( $custom_users as $custom_user ) {
			if( !empty( $custom_user ) ) {
				$fields[] = array(
					'name' => $custom_user,
					'label' => woo_ce_clean_export_label( $custom_user ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_customer_fields_custom_user_hover', '%s: %s' ), __( 'Custom User', 'woocommerce-exporter' ), $custom_user )
				);
			}
		}
	}
	unset( $custom_users, $custom_user );

	return $fields;

}
add_filter( 'woo_ce_customer_fields', 'woo_ce_extend_customer_fields' );
?>