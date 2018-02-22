<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	// Quick Export

	// Scheduled Exports

	/* End of: WordPress Administration */

}

// Adds custom Coupon columns to the Coupon fields list
function woo_ce_extend_coupon_fields( $fields = array() ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$fields[] = array(
			'name' => 'blog_id',
			'label' => __( 'Blog ID', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress Multisite', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Smart Coupons - http://www.woothemes.com/products/smart-coupons/
	if( woo_ce_detect_export_plugin( 'wc_smart_coupons' ) ) {
		$fields[] = array(
			'name' => 'valid_for',
			'label' => __( 'Valid for', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Smart Coupons', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'is_pick_price_of_product',
			'label' => __( 'Pick Product\'s Price', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Smart Coupons', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'auto_generate_coupon',
			'label' => __( 'Auto Generate Coupon', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Smart Coupons', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'coupon_title_prefix',
			'label' => __( 'Coupon Title Prefix', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Smart Coupons', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'coupon_title_suffix',
			'label' => __( 'Coupon Title Suffix', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Smart Coupons', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'visible_storewide',
			'label' => __( 'Visible Storewide', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Smart Coupons', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'disable_email_restriction',
			'label' => __( 'Disable E-mail Restriction', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Smart Coupons', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Currency Switcher - http://dev.pathtoenlightenment.net/shop
	if( woo_ce_detect_export_plugin( 'currency_switcher' ) ) {
		$options = get_option( 'wc_aelia_currency_switcher' );
		$currencies = ( isset( $options['enabled_currencies'] ) ? $options['enabled_currencies'] : false );
		if( !empty( $currencies ) ) {
			$woocommerce_currency = get_option( 'woocommerce_currency' );
			foreach( $currencies as $currency ) {

				// Skip the WooCommerce default currency
				if( $woocommerce_currency == $currency )
					continue;

				$fields[] = array(
					'name' => sprintf( 'coupon_amount_%s', $currency ),
					'label' => sprintf( __( 'Coupon Amount (%s)', 'woocommerce-exporter' ), $currency ),
					'hover' => __( 'WooCommerce Currency Switcher', 'woocommerce-exporter' )
				);
				$fields[] = array(
					'name' => sprintf( 'minimum_amount_%s', $currency ),
					'label' => sprintf( __( 'Minimum Amount (%s)', 'woocommerce-exporter' ), $currency ),
					'hover' => __( 'WooCommerce Currency Switcher', 'woocommerce-exporter' )
				);
				$fields[] = array(
					'name' => sprintf( 'maximum_amount_%s', $currency ),
					'label' => sprintf( __( 'Maximum Amount (%s)', 'woocommerce-exporter' ), $currency ),
					'hover' => __( 'WooCommerce Currency Switcher', 'woocommerce-exporter' )
				);

			}
		}
		unset( $options );
	}

	return $fields;

}
add_filter( 'woo_ce_coupon_fields', 'woo_ce_extend_coupon_fields' );

function woo_ce_extend_coupon_item( $coupon, $coupon_id = 0 ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$coupon->blog_id = get_current_blog_id();
	}

	// WooCommerce Smart Coupons - http://www.woothemes.com/products/smart-coupons/
	if( woo_ce_detect_export_plugin( 'wc_smart_coupons' ) ) {
		$coupon->is_pick_price_of_product = woo_ce_format_switch( get_post_meta( $coupon_id, 'is_pick_price_of_product', true ) );
		$coupon->valid_for = '';
		$coupon_validity = get_post_meta( $coupon_id, 'sc_coupon_validity', true );
		$validity_suffix = get_post_meta( $coupon_id, 'validity_suffix', true );
		if( !empty( $coupon_validity ) && !empty( $validity_suffix ) )
			$coupon->valid_for = sprintf( apply_filters( 'woo_ce_coupon_smart_coupons_valid_for', __( '%s %s', 'woocommerce-exporter' ) ), absint( $coupon_validity ), ucfirst( $validity_suffix ) );
		$coupon->auto_generate_coupon = woo_ce_format_switch( get_post_meta( $coupon_id, 'auto_generate_coupon', true ) );
		$coupon->coupon_title_prefix = get_post_meta( $coupon_id, 'coupon_title_prefix', true );
		$coupon->coupon_title_suffix = get_post_meta( $coupon_id, 'coupon_title_suffix', true );
		$coupon->visible_storewide = woo_ce_format_switch( get_post_meta( $coupon_id, 'sc_is_visible_storewide', true ) );
		$coupon->disable_email_restriction = woo_ce_format_switch( get_post_meta( $coupon_id, 'sc_disable_email_restriction', true ) );
	}

	// WooCommerce Currency Switcher - http://dev.pathtoenlightenment.net/shop
	if( woo_ce_detect_export_plugin( 'currency_switcher' ) ) {
		$options = get_option( 'wc_aelia_currency_switcher' );
		$currencies = ( isset( $options['enabled_currencies'] ) ? $options['enabled_currencies'] : false );
		if( !empty( $currencies ) ) {
			$currency_data = get_post_meta( $coupon_id, '_coupon_currency_data', true );
			$woocommerce_currency = get_option( 'woocommerce_currency' );
			foreach( $currencies as $currency ) {

				// Skip the WooCommerce default currency
				if( $woocommerce_currency == $currency )
					continue;

				if( !empty( $currency_data ) ) {
					// Check if the currency key exists
					if( isset( $currency_data[$currency] ) ) {
						$coupon->{sprintf( 'coupon_amount_%s', $currency )} = ( isset( $currency_data[$currency]['coupon_amount'] ) ? $currency_data[$currency]['coupon_amount'] : false );
						$coupon->{sprintf( 'minimum_amount_%s', $currency )} = ( isset( $currency_data[$currency]['minimum_amount'] ) ? $currency_data[$currency]['minimum_amount'] : false );
						$coupon->{sprintf( 'maximum_amount_%s', $currency )} = ( isset( $currency_data[$currency]['maximum_amount'] ) ? $currency_data[$currency]['maximum_amount'] : false );
					}
				}

			}
		}
		unset( $options );
	}

	return $coupon;

}
add_filter( 'woo_ce_coupon_item', 'woo_ce_extend_coupon_item', 10, 2 );
?>