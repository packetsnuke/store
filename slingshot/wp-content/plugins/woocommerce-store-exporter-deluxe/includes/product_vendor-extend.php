<?php
function woo_ce_extend_product_vendor_term_taxonomy( $term_taxonomy = '' ) {

	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		$term_taxonomy = 'yith_shop_vendor';
	}
	return $term_taxonomy;

}
add_filter( 'woo_ce_product_vendor_term_taxonomy', 'woo_ce_extend_product_vendor_term_taxonomy' );

function woo_ce_extend_product_vendor_fields( $fields ) {

	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		$fields[] = array(
			'name' => 'location',
			'label' => __( 'Location', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce Multi Vendor Premium', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'store_email',
			'label' => __( 'Store E-mail', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce Multi Vendor Premium', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'telephone',
			'label' => __( 'Telephone', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce Multi Vendor Premium', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'vat',
			'label' => __( 'VAT', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce Multi Vendor Premium', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'legal_notes',
			'label' => __( 'Legal Notes', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce Multi Vendor Premium', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'bank_account',
			'label' => __( 'Bank Account', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce Multi Vendor Premium', 'woocommerce-exporter' )
		);
	}
	return $fields;

}
add_filter( 'woo_ce_product_vendor_fields', 'woo_ce_extend_product_vendor_fields' );

function woo_ce_extend_product_vendor( $product_vendor, $term_id ) {

	// WooCommerce Product Vendors - http://www.woothemes.com/products/product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) ) {
		// Get Product Vendor details
		if( function_exists( 'get_vendor' ) ) {
			$product_vendor_data = ( function_exists( 'get_vendor' ) ? get_vendor( $vendor_id ) : array() );
			if( $product_vendor_data !== false ) {
				$product_vendor = $product_vendor_data;
				$product_vendor->user_name = ( isset( $product_vendor->admins ) ? woo_ce_format_product_vendor_users( $product_vendor->admins, 'user_login' ) : false );
				$product_vendor->user_id = ( isset( $product_vendor->admins ) ? woo_ce_format_product_vendor_users( $product_vendor->admins, 'ID' ) : false );
			}
		} else {
			$term_taxonomy = 'wcpv_product_vendors';
			$product_vendor_data = get_term( $term_id, $term_taxonomy );
			if( $product_vendor_data !== false ) {
				$product_vendor = $product_vendor_data;
				$product_vendor->ID = $product_vendor_data->term_id;
				$product_vendor->title = $product_vendor_data->name;
				$vendor_meta = get_term_meta( $term_id, 'vendor_data', true );
				if( $vendor_meta !== false ) {
					$product_vendor->description = ( isset( $vendor_meta['notes'] ) ? $vendor_meta['notes'] : false );
					$product_vendor->paypal_email = ( isset( $vendor_meta['paypal'] ) ? $vendor_meta['paypal'] : false );
					$product_vendor->commission = ( isset( $vendor_meta['commission'] ) ? $vendor_meta['commission'] : false );
					if( !empty( $product_vendor->commission ) && isset( $vendor_meta['commission_type'] ) )
						$product_vendor->commission .= ( $vendor_meta['commission_type'] == 'percentage' ? '%' : '' );
					$product_vendor->url = get_term_link( $term_id, $term_taxonomy );
					$product_vendor->user_name = ( isset( $vendor_meta['admins'] ) ? woo_ce_format_product_vendor_users( $vendor_meta['admins'], 'user_login' ) : false );
					$product_vendor->user_id = ( isset( $vendor_meta['admins'] ) ? woo_ce_format_product_vendor_users( $vendor_meta['admins'], 'ID' ) : false );
				}
			}
		}
	}

	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		$term_taxonomy = 'yith_shop_vendor';
		if( !empty( $term_id ) ) {
			$product_vendor_data = get_term( $term_id, $term_taxonomy );
			if( $product_vendor_data !== false ) {
				$product_vendor = $product_vendor_data;
				$product_vendor->ID = $product_vendor_data->term_id;
				$product_vendor->title = $product_vendor_data->name;
				// YITH WooCommerce Multi Vendor Premium stores its Vendor data in woocommerce_termmeta (now deprecated)
				$product_vendor->location = get_woocommerce_term_meta( $term_id, 'location', true );
				$product_vendor->store_email = get_woocommerce_term_meta( $term_id, 'store_email', true );
				$product_vendor->telephone = get_woocommerce_term_meta( $term_id, 'telephone', true );
				$product_vendor->vat = get_woocommerce_term_meta( $term_id, 'vat', true );
				$product_vendor->legal_notes = get_woocommerce_term_meta( $term_id, 'legal_notes', true );
				$product_vendor->commission = get_woocommerce_term_meta( $term_id, 'commission', true );
				$product_vendor->bank_account = get_woocommerce_term_meta( $term_id, 'bank_account', true );
				$product_vendor->paypal_email = get_woocommerce_term_meta( $term_id, 'paypal_email', true );
			}
		}
	}
	return $product_vendor;

}
add_filter( 'woo_ce_product_vendor', 'woo_ce_extend_product_vendor', 10, 2 );

function woo_ce_get_product_vendor_assoc_orders( $vendor_ids = array() ) {

	$output = false;

	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'yith_vendor' ) ) {

		global $wpdb;

		$order_ids_sql = $wpdb->prepare( "SELECT `order_id` FROM `" . $wpdb->prefix . "yith_vendors_commissions` WHERE `vendor_id` IN (%s) LIMIT 1", implode( ',', $vendor_ids ) );
		$order_ids = $wpdb->get_col( $order_ids_sql );
		if( !empty( $order_ids ) )
			$output = $order_ids;

	}

	return $output;

}
?>