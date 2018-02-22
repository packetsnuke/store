<?php
function woo_ce_extend_order_items_individual( $order, $order_item ) {

	global $export;

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	// Product Add-ons - http://www.woothemes.com/
	if( woo_ce_detect_export_plugin( 'product_addons' ) && $order->order_items ) {
		if( isset( $order_item->product_addons_summary ) )
			$order->order_items_product_addons_summary = $order_item->product_addons_summary;
		if( $product_addons = woo_ce_get_product_addons() ) {
			foreach( $product_addons as $product_addon ) {
				if( isset( $order_item->product_addons[sanitize_key( $product_addon->post_name )] ) )
					$order->{'order_items_product_addon_' . sanitize_key( $product_addon->post_name )} = $order_item->product_addons[sanitize_key( $product_addon->post_name )];
			}
			unset( $product_addons, $product_addon );
		}
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_extend_order_items_individual() > woo_ce_get_product_addons(): ' . ( time() - $export->start_time ) ) );
	}

	// Gravity Forms - http://woothemes.com/woocommerce
	if( ( woo_ce_detect_export_plugin( 'gravity_forms' ) && woo_ce_detect_export_plugin( 'woocommerce_gravity_forms' ) ) && $order->order_items ) {
		// Check if there are any Products linked to Gravity Forms
		$gf_fields = woo_ce_get_gravity_forms_fields();
		if( !empty( $gf_fields ) ) {
			$order->order_items_gf_form_id = ( isset( $order_item->gf_form_id ) ? $order_item->gf_form_id : false ); 
			$order->order_items_gf_form_label = ( isset( $order_item->gf_form_label ) ? $order_item->gf_form_label : false );
			$meta_type = 'order_item';
			foreach( $gf_fields as $gf_field ) {
				// Check that we only fill export fields for forms that are actually filled
				if( isset( $order_item->gf_form_id ) ) {
					if( $gf_field['formId'] == $order_item->gf_form_id ) {
						$order->{sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] )} = get_metadata( $meta_type, $order_item->id, $gf_field['label'], true );
					}
				}
			}
		}
		unset( $gf_fields, $gf_field );
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_extend_order_items_individual() > woo_ce_get_gravity_forms_fields(): ' . ( time() - $export->start_time ) ) );
	}

	// WooCommerce Checkout Add-Ons - http://www.skyverge.com/product/woocommerce-checkout-add-ons/
	if( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
		$order->order_items_checkout_addon_id = ( isset( $order_item->checkout_addon_id ) ? $order_item->checkout_addon_id : false );
		$order->order_items_checkout_addon_label = ( isset( $order_item->checkout_addon_label ) ? $order_item->checkout_addon_label : false );
		$order->order_items_checkout_addon_value = ( isset( $order_item->checkout_addon_value ) ? $order_item->checkout_addon_value : false );
	}

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if( woo_ce_detect_product_brands() )
		$order->order_items_brand = ( isset( $order_item->brand ) ? $order_item->brand : false );

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) )
		$order->order_items_vendor = ( isset( $order_item->vendor ) ? $order_item->vendor : false );

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/
	if( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		$order->order_items_cost_of_goods = ( isset( $order_item->cost_of_goods ) ? $order_item->cost_of_goods : false );
		$order->order_items_total_cost_of_goods = ( isset( $order_item->total_cost_of_goods ) ? $order_item->total_cost_of_goods : false );
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590
	if( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		$order->order_items_posr = ( isset( $order_item->posr ) ? $order_item->posr : false );
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_msrp' ) ) {
		$order->order_items_msrp = ( isset( $order_item->msrp ) ? $order_item->msrp : false );
	}

	// Local Pickup Plus - http://www.woothemes.com/products/local-pickup-plus/
	if( woo_ce_detect_export_plugin( 'local_pickup_plus' ) ) {
		$meta_type = 'order_item';
		// Adding support for Local Pickup Plus 2.0...
		if( function_exists( 'wc_local_pickup_plus' ) ) {
			$class = wc_local_pickup_plus();
			if( version_compare( $class::VERSION, '2.0' ) >= 0 )
				$meta_key = '_pickup_location_name';
			else
				$meta_key = 'Pickup Location';
			unset( $class );
		} else {
			$meta_key = 'Pickup Location';
		}
		$pickup_location = get_metadata( $meta_type, $order_item->id, $meta_key, true );
		if( !empty( $pickup_location ) )
			$order->order_items_pickup_location = $pickup_location;
		unset( $pickup_location );
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	if( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		$booking_id = woo_ce_get_order_assoc_booking_id( $order->id );
		if( !empty( $booking_id ) ) {
			// @mod - Are we double querying here? Check in 2.4+
			$order->order_items_booking_id = $booking_id;
			// Booking Start Date
			$booking_start_date = get_post_meta( $booking_id, '_booking_start', true );
			if( !empty( $booking_start_date ) )
				$order->order_items_booking_start_date = woo_ce_format_date( date( 'Y-m-d', strtotime( $booking_start_date ) ) );
			unset( $booking_start_date );
			// Booking End Date
			$booking_end_date = get_post_meta( $booking_id, '_booking_end', true );
			if( !empty( $booking_end_date ) )
				$order->order_items_booking_end_date = woo_ce_format_date( date( 'Y-m-d', strtotime( $booking_end_date ) ) );
			unset( $booking_end_date );
			// All Day Booking
			$booking_all_day = woo_ce_format_switch( get_post_meta( $booking_id, '_booking_all_day', true ) );
			if( !empty( $booking_all_day ) )
				$order->order_items_booking_all_day = $booking_all_day;
			unset( $booking_all_day );
			// Booking Resource ID
			$booking_resource_id = get_post_meta( $booking_id, '_booking_resource_id', true );
			if( !empty( $booking_resource_id ) )
				$order->order_items_booking_resource_id = $booking_resource_id;
			unset( $booking_resource_id );
			// Booking Resource Name
			if( !empty( $order->order_items_booking_resource_id ) ) {
				$booking_resource_title = get_the_title( $order->order_items_booking_resource_id );
				if( !empty( $booking_resource_title ) )
					$order->order_items_booking_resource_title = $booking_resource_title;
				unset( $booking_resource_title );
			}
			// Booking # of Persons
			$booking_persons = get_post_meta( $booking_id, '_booking_persons', true );
			$order->order_items_booking_persons = ( !empty( $booking_persons ) ? $booking_persons : '-' );
			unset( $booking_persons );
		}
		unset( $booking_id );
		$meta_type = 'order_item';
		$booking_date = get_metadata( $meta_type, $order_item->id, __( 'Booking Date', 'woocommerce-bookings' ), true );
		if( !empty( $booking_date ) )
			$order->order_items_booking_date = get_metadata( $meta_type, $order_item->id, __( 'Booking Date', 'woocommerce-bookings' ), true );
		unset( $booking_date );
		$booking_type = get_metadata( $meta_type, $order_item->id, __( 'Booking Type', 'woocommerce-bookings' ), true );
		if( !empty( $booking_type ) )
			$order->order_items_booking_type = get_metadata( $meta_type, $order_item->id, __( 'Booking Type', 'woocommerce-bookings' ), true );
		unset( $booking_type );
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_extend_order_items_individual() > woo_ce_get_order_assoc_booking_id(): ' . ( time() - $export->start_time ) ) );
	}

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
	if( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
		$tm_fields = woo_ce_get_extra_product_option_fields( $order_item->id );
		if( !empty( $tm_fields ) ) {
			foreach( $tm_fields as $tm_field ) {
				if( isset( $order_item->{sprintf( 'tm_%s', sanitize_key( $tm_field['name'] ) )} ) )
					$order->{sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) )} = woo_ce_get_extra_product_option_value( $order_item->id, $tm_field );
			}
		}
		unset( $tm_fields, $tm_field );
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_extend_order_items_individual() > woo_ce_get_extra_product_option_fields(): ' . ( time() - $export->start_time ) ) );
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
	if( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		if( !get_option( 'wccf_migrated_to_20' ) ) {
			$options = get_option( 'rp_wccf_options' );
			if( !empty( $options ) ) {
				$options = ( isset( $options[1] ) ? $options[1] : false );
				if( !empty( $options ) ) {
					// Product Fields
					$custom_fields = ( isset( $options['product_fb_config'] ) ? $options['product_fb_config'] : false );
					if( !empty( $custom_fields ) ) {
						foreach( $custom_fields as $custom_field )
							$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) )} = ( isset( $order_item->{sprintf( 'wccf_%s', sanitize_key( $custom_field['key'] ) )} ) ? $order_item->{sprintf( 'wccf_%s', sanitize_key( $custom_field['key'] ) )} : false );
						unset( $custom_fields, $custom_field );
					}
				}
				unset( $options );
			}
		} else {
			// Product Fields
			$custom_fields = woo_ce_get_wccf_product_fields();
			if( !empty( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field ) {
					$key = get_post_meta( $custom_field->ID, 'key', true );
					$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $key ) )} = ( isset( $order_item->{sprintf( 'wccf_%s', sanitize_key( $key ) )} ) ? $order_item->{sprintf( 'wccf_%s', sanitize_key( $key ) )} : false );
				}
			}
			unset( $custom_fields, $custom_field );
		}
	}

	// WooCommerce Product Custom Options Lite - https://wordpress.org/plugins/woocommerce-custom-options-lite/
	if( woo_ce_detect_export_plugin( 'wc_product_custom_options' ) ) {
		$custom_options = woo_ce_get_product_custom_options();
		if( !empty( $custom_options ) ) {
			foreach( $custom_options as $custom_option ) {
				$order->{sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) )} = ( isset( $order_item->{sprintf( 'pco_%s', sanitize_key( $custom_option ) )} ) ? $order_item->{sprintf( 'pco_%s', sanitize_key( $custom_option ) )} : false );
			}
			unset( $custom_options, $custom_option );
		}
	}

	// WooCommerce Easy Bookings - https://wordpress.org/plugins/woocommerce-easy-booking-system/
	if( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		$meta_type = 'order_item';
		$order->order_items_booking_start_date = ( isset( $order_item->booking_start_date ) ? $order_item->booking_start_date : false );
		$order->order_items_booking_end_date = ( isset( $order_item->booking_end_date ) ? $order_item->booking_end_date : false );
	}

	// N-Media WooCommerce Personalized Product Meta Manager
	if( woo_ce_detect_export_plugin( 'wc_nm_personalizedproduct' ) ) {
		$meta_type = 'order_item';
		$custom_fields = woo_ce_get_nm_personalized_product_fields();
		if( !empty( $custom_fields ) ) {
			foreach( $custom_fields as $custom_field ) {
				$order->{sprintf( 'order_items_nm_%s', $custom_field['name'] )} = ( isset( $order_item->{sprintf( 'nm_%s', $custom_field['name'] )} ) ? $order_item->{sprintf( 'nm_%s', $custom_field['name'] )} : false );
			}
			unset( $custom_fields, $custom_field );
		}
	}

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/
	if( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		$meta_type = 'order_item';
		$order->order_items_appointment_id = ( isset( $order_item->appointment_id ) ? $order_item->appointment_id : false );
		$order->order_items_booking_start_date = ( isset( $order_item->booking_start_date ) ? $order_item->booking_start_date : false );
		$order->order_items_booking_start_time = ( isset( $order_item->booking_start_time ) ? $order_item->booking_start_time : false );
		$order->order_items_booking_end_date = ( isset( $order_item->booking_end_date ) ? $order_item->booking_end_date : false );
		$order->order_items_booking_end_time = ( isset( $order_item->booking_end_time ) ? $order_item->booking_end_time : false );
		$order->order_items_booking_all_day = ( isset( $order_item->booking_all_day ) ? $order_item->booking_all_day : false );
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/
	if( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$meta_type = 'order_item';
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if( !empty( $wholesale_roles ) ) {
			foreach( $wholesale_roles as $key => $wholesale_role ) {
				$order->{sprintf( 'order_items_%s_wholesale_price', $key )} = ( isset( $order_item->{sprintf( '%s_wholesale_price', $key )} ) ? $order_item->{sprintf( '%s_wholesale_price', $key )} : false );
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	// Tax Rates
	$tax_rates = woo_ce_get_order_tax_rates();
	if( !empty( $tax_rates ) ) {
		foreach( $tax_rates as $tax_rate ) {
			$order->{sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] )} = '';
			if( isset( $order_item->{sprintf( 'tax_rate_%d', $tax_rate['rate_id'] )} ) )
				$order->{sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] )} = $order_item->{sprintf( 'tax_rate_%d', $tax_rate['rate_id'] )};
		}
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_extend_order_items_individual() > woo_ce_get_order_tax_rates(): ' . ( time() - $export->start_time ) ) );
	}
	unset( $tax_rates, $tax_rate );

	// Product Attributes
	$attributes = woo_ce_get_product_attributes( 'attribute_name' );
	if( !empty( $attributes ) ) {
		foreach( $attributes as $attribute ) {
			$order->{sprintf( 'order_items_attribute_%s', sanitize_key( $attribute ) )} = '';
			if( isset( $order_item->{sprintf( 'attribute_%s', sanitize_key( $attribute ) )} ) )
				$order->{sprintf( 'order_items_attribute_%s', sanitize_key( $attribute ) )} = $order_item->{sprintf( 'attribute_%s', sanitize_key( $attribute ) )};
			if( isset( $order_item->{sprintf( 'product_attribute_%s', sanitize_key( $attribute ) )} ) )
				$order->{sprintf( 'order_items_product_attribute_%s', sanitize_key( $attribute ) )} = $order_item->{sprintf( 'product_attribute_%s', sanitize_key( $attribute ) )};
		}
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_extend_order_items_individual() > woo_ce_get_product_attributes(): ' . ( time() - $export->start_time ) ) );
	}
	unset( $attributes, $attribute );

	// WooCommerce Ship to Multiple Addresses - http://woothemes.com/woocommerce
	if( class_exists( 'WC_Ship_Multiple' ) ) {
		$shipping_packages = get_post_meta( $order->id, '_wcms_packages', true );
		if( !empty( $shipping_packages ) ) {

			// Override the Shipping address
			$order->shipping_first_name = '';
			$order->shipping_last_name = '';
			if( empty( $order->shipping_first_name ) && empty( $order->shipping_first_name ) )
				$order->shipping_full_name = '';
			else
				$order->shipping_full_name = '';
			$order->shipping_company = '';
			$order->shipping_address = '';
			$order->shipping_address_1 = '';
			$order->shipping_address_2 = '';
			$order->shipping_city = '';
			$order->shipping_postcode = '';
			$order->shipping_state = '';
			$order->shipping_country = '';
			$order->shipping_state_full = '';
			$order->shipping_country_full = '';

			// Override the shipping method
			foreach( $shipping_packages as $shipping_package ) {
				$contents = $shipping_package['contents'];
				if( !empty( $contents ) ) {
					foreach( $contents as $content ) {
						if( $content['product_id'] == $order_item->product_id ) {
							$order->shipping_first_name = $shipping_package['full_address']['first_name'];
							$order->shipping_last_name = $shipping_package['full_address']['last_name'];
							if( empty( $order->shipping_first_name ) && empty( $order->shipping_last_name ) )
								$order->shipping_full_name = '';
							else
								$order->shipping_full_name = $order->shipping_first_name . ' ' . $order->shipping_last_name;
							$order->shipping_company = $shipping_package['full_address']['company'];
							$order->shipping_address = '';
							$order->shipping_address_1 = $shipping_package['full_address']['address_1'];
							$order->shipping_address_2 = $shipping_package['full_address']['address_2'];
							if( !empty( $order->billing_address_2 ) )
								$order->shipping_address = sprintf( apply_filters( 'woo_ce_get_order_data_shipping_address', '%s %s' ), $order->shipping_address_1, $order->shipping_address_2 );
							else
								$order->shipping_address = $order->shipping_address_1;
							$order->shipping_city = $shipping_package['full_address']['city'];
							$order->shipping_postcode = $shipping_package['full_address']['postcode'];
							$order->shipping_state = $shipping_package['full_address']['state'];
							$order->shipping_country = $shipping_package['full_address']['country'];
							$order->shipping_state_full = woo_ce_expand_state_name( $order->shipping_country, $order->shipping_state );
							$order->shipping_country_full = woo_ce_expand_country_name( $order->shipping_country );
							break;
							break;
						}
					}
				}
				unset( $contents );
			}

		}
		unset( $shipping_packages );
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_extend_order_items_individual() > shipping_packages: ' . ( time() - $export->start_time ) ) );
	}

	// Custom Order Items fields
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	if( !empty( $custom_order_items ) ) {
		foreach( $custom_order_items as $custom_order_item ) {
			if( !empty( $custom_order_item ) )
				$order->{sprintf( 'order_items_%s', sanitize_key( $custom_order_item ) )} = woo_ce_format_custom_meta( $order_item->{sanitize_key( $custom_order_item )} );
		}
	}
	unset( $custom_order_items, $custom_order_item );

	// Custom Order Item Product fields
	$custom_order_products = woo_ce_get_option( 'custom_order_products', '' );
	if( !empty( $custom_order_products ) ) {
		foreach( $custom_order_products as $custom_order_product ) {
			if( !empty( $custom_order_product ) )
				$order->{sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) )} = woo_ce_format_custom_meta( $order_item->{sanitize_key( $custom_order_product )} );
		}
	}
	unset( $custom_order_products, $custom_order_product );

	// Custom Product fields
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if( !empty( $custom_products ) ) {
		foreach( $custom_products as $custom_product ) {
			if( !empty( $custom_product ) )
				$order->{sprintf( 'order_items_%s', sanitize_key( $custom_product ) )} = woo_ce_format_custom_meta( $order_item->{sanitize_key( $custom_product )} );
		}
	}
	unset( $custom_products, $custom_product );

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	return $order;

}
add_filter( 'woo_ce_order_items_individual', 'woo_ce_extend_order_items_individual', 11, 2 );
?>