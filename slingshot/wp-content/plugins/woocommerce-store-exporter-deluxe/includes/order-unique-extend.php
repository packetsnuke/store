<?php
// Order items formatting: Unique
function woo_ce_extend_order_items_unique( $order, $i = 0, $order_item = array() ) {

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	// Product Add-ons - http://www.woothemes.com/
	if( woo_ce_detect_export_plugin( 'product_addons' ) && $order->order_items ) {
		$product_addons = woo_ce_get_product_addons();
		if( !empty( $product_addons ) ) {
			foreach( $product_addons as $product_addon ) {
				if( isset( $order_item->product_addons[sanitize_key( $product_addon->post_name )] ) )
					$order->{sprintf( 'order_item_%d_product_addon_%s', $i, sanitize_key( $product_addon->post_name ) )} = $order_item->product_addons[sanitize_key( $product_addon->post_name )];
			}
			unset( $product_addons, $product_addon );
		}
	}

	// Gravity Forms - http://woothemes.com/woocommerce
	if( ( woo_ce_detect_export_plugin( 'gravity_forms' ) && woo_ce_detect_export_plugin( 'woocommerce_gravity_forms' ) ) && $order->order_items ) {
		// Check if there are any Products linked to Gravity Forms
		$gf_fields = woo_ce_get_gravity_forms_fields();
		if( !empty( $gf_fields ) ) {
			$meta_type = 'order_item';
			foreach( $order->order_items as $order_item ) {
				$order->{sprintf( 'order_item_%d_gf_form_id', $i )} = ( isset( $order_item->gf_form_id ) ? $order_item->gf_form_id : false );
				$order->{sprintf( 'order_item_%d_gf_form_label', $i )} = ( isset( $order_item->gf_form_label ) ? $order_item->gf_form_label : false );
				foreach( $gf_fields as $gf_field ) {
					// Check that we only fill export fields for forms that are actually filled
					if( isset( $order_item->gf_form_id ) ) {
						if( $gf_field['formId'] == $order_item->gf_form_id )
							$order->{sprintf( 'order_item_%d_gf_%d_%s', $i, $gf_field['formId'], $gf_field['id'] )} = get_metadata( $meta_type, $order_item->id, $gf_field['label'], true );
					}
				}
			}
		}
		unset( $gf_fields, $gf_field );
	}

	// WooCommerce Checkout Add-Ons - http://www.skyverge.com/product/woocommerce-checkout-add-ons/
	if( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
		$order->{sprintf( 'order_item_%d_checkout_addon_id', $i )} = ( isset( $order_item->checkout_addon_id ) ? $order_item->checkout_addon_id : false );
		$order->{sprintf( 'order_item_%d_checkout_addon_label', $i )} = ( isset( $order_item->checkout_addon_label ) ? $order_item->checkout_addon_label : false );
		$order->{sprintf( 'order_item_%d_checkout_addon_value', $i )} = ( isset( $order_item->checkout_addon_value ) ? $order_item->checkout_addon_value : false );
	}

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if( woo_ce_detect_product_brands() ) {
		if( isset( $order_item->brand ) )
			$order->{sprintf( 'order_item_%d_brand', $i )} = $order_item->brand;
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		if( isset( $order_item->vendor ) )
			$order->{sprintf( 'order_item_%d_vendor', $i )} = $order_item->vendor;
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/
	if( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		if( isset( $order_item->cost_of_goods ) )
			$order->{sprintf( 'order_item_%d_cost_of_goods', $i )} = $order_item->cost_of_goods;
		if( isset( $order_item->total_cost_of_goods ) )
			$order->{sprintf( 'order_item_%d_total_cost_of_goods', $i )} = $order_item->total_cost_of_goods;
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590
	if( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		if( isset( $order_item->posr ) )
			$order->{sprintf( 'order_item_%d_posr', $i )} = $order_item->posr;
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_msrp' ) ) {
		if( isset( $order_item->msrp ) )
			$order->{sprintf( 'order_item_%d_msrp', $i )} = $order_item->msrp;
	}

	// Local Pickup Plus - http://www.woothemes.com/products/local-pickup-plus/
	if( woo_ce_detect_export_plugin( 'local_pickup_plus' ) ) {
		if( isset( $order_item->pickup_location ) )
			$order->{sprintf( 'order_item_%d_pickup_location', $i )} = $order_item->pickup_location;
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	if( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		$order->{sprintf( 'order_item_%d_booking_id', $i )} = $order_item->booking_id;
		$order->{sprintf( 'order_item_%d_booking_date', $i )} = $order_item->booking_date;
		$order->{sprintf( 'order_item_%d_booking_type', $i )} = $order_item->booking_type;
		$order->{sprintf( 'order_item_%d_booking_start_date', $i )} = $order_item->booking_start_date;
		$order->{sprintf( 'order_item_%d_booking_end_date', $i )} = $order_item->booking_end_date;
		$order->{sprintf( 'order_item_%d_booking_all_day', $i )} = $order_item->booking_all_day;
		$order->{sprintf( 'order_item_%d_booking_resource_id', $i )} = $order_item->booking_resource_id;
		$order->{sprintf( 'order_item_%d_booking_resource_title', $i )} = $order_item->booking_resource_title;
		$order->{sprintf( 'order_item_%d_booking_persons', $i )} = $order_item->booking_persons;
	}

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
	if( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
		$tm_fields = woo_ce_get_extra_product_option_fields( $order_item->id );
		if( !empty( $tm_fields ) ) {
			foreach( $tm_fields as $tm_field ) {
				if( isset( $order_item->{sprintf( 'tm_%s', sanitize_key( $tm_field['name'] ) )} ) )
					$order->{sprintf( 'order_item_%d_tm_%s', $i, sanitize_key( $tm_field['name'] ) )} = woo_ce_get_extra_product_option_value( $order_item->id, $tm_field );
			}
		}
		unset( $tm_fields, $tm_field );
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
							$order->{sprintf( 'order_item_%d_wccf_%s', $i, sanitize_key( $custom_field['key'] ) )} = ( isset( $order_item->{sprintf( 'wccf_%s', sanitize_key( $custom_field['key'] ) )} ) ? $order_item->{sprintf( 'wccf_%s', sanitize_key( $custom_field['key'] ) )} : false );
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
					$order->{sprintf( 'order_item_%d_wccf_%s', $i, sanitize_key( $key ) )} = ( isset( $order_item->{sprintf( 'wccf_%s', sanitize_key( $key ) )} ) ? $order_item->{sprintf( 'wccf_%s', sanitize_key( $key ) )} : false );
				}
			}
			unset( $custom_fields, $custom_field, $key );
		}
	}

	// WooCommerce Product Custom Options Lite - https://wordpress.org/plugins/woocommerce-custom-options-lite/
	if( woo_ce_detect_export_plugin( 'wc_product_custom_options' ) ) {
		$custom_options = woo_ce_get_product_custom_options();
		if( !empty( $custom_options ) ) {
			foreach( $custom_options as $custom_option ) {
				if( isset( $order_item->{sprintf( 'pco_%s', sanitize_key( $custom_option ) )} ) )
					$order->{sprintf( 'order_item_%d_pco_%s', $i, sanitize_key( $custom_option ) )} = ( isset( $order_item->{sprintf( 'pco_%s', sanitize_key( $custom_option ) )} ) ? $order_item->{sprintf( 'pco_%s', sanitize_key( $custom_option ) )} : false );
			}
		}
	}

	// Barcodes for WooCommerce - http://www.wolkenkraft.com/produkte/barcodes-fuer-woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_barcodes' ) ) {
		$order->{sprintf( 'order_item_%d_barcode_type', $i )} = $order_item->barcode_type;
		$order->{sprintf( 'order_item_%d_barcode', $i )} = $order_item->barcode;
	}

	// WooCommerce Easy Bookings - https://wordpress.org/plugins/woocommerce-easy-booking-system/
	if( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		$order->{sprintf( 'order_item_%d_booking_start_date', $i )} = $order_item->booking_start_date;
		$order->{sprintf( 'order_item_%d_booking_end_date', $i )} = $order_item->booking_end_date;
	}

	// N-Media WooCommerce Personalized Product Meta Manager
	if( woo_ce_detect_export_plugin( 'wc_nm_personalizedproduct' ) ) {
		$custom_fields = woo_ce_get_nm_personalized_product_fields();
		if( !empty( $custom_fields ) ) {
			foreach( $custom_fields as $custom_field ) {
				if( isset( $order_item->{sprintf( 'nm_%s', $custom_field['name'] )} ) )
					$order->{sprintf( 'order_item_%d_nm_%s', $i, $custom_field['name'] )} = ( isset( $order_item->{sprintf( 'nm_%s', $custom_field['name'] )} ) ? $order_item->{sprintf( 'nm_%s', $custom_field['name'] )} : false );
			}
		}
	}

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/
	if( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		$order->{sprintf( 'order_item_%d_appointment_id', $i )} = $order_item->appointment_id;
		$order->{sprintf( 'order_item_%d_booking_start_date', $i )} = $order_item->booking_start_date;
		$order->{sprintf( 'order_item_%d_booking_start_time', $i )} = $order_item->booking_start_time;
		$order->{sprintf( 'order_item_%d_booking_end_date', $i )} = $order_item->booking_end_date;
		$order->{sprintf( 'order_item_%d_booking_end_time', $i )} = $order_item->booking_end_time;
		$order->{sprintf( 'order_item_%d_booking_all_day', $i )} = $order_item->booking_all_day;
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/
	if( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if( !empty( $wholesale_roles ) ) {
			foreach( $wholesale_roles as $key => $wholesale_role ) {
				$order->{sprintf( 'order_items_%d_%s_wholesale_price', $i, $key )} = $order_item->{sprintf( '%s_wholesale_price', $key )};
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	// Tax Rates
	$tax_rates = woo_ce_get_order_tax_rates();
	if( !empty( $tax_rates ) ) {
		foreach( $tax_rates as $tax_rate ) {
			if( isset( $order_item->{sprintf( 'tax_rate_%d', $tax_rate['rate_id'] )} ) )
				$order->{sprintf( 'order_item_%d_tax_rate_%d', $i, $tax_rate['rate_id'] )} = $order_item->{sprintf( 'tax_rate_%d', $tax_rate['rate_id'] )};
		}
		unset( $tax_rates, $tax_rate );
	}

	// Product Attributes
	$attributes = woo_ce_get_product_attributes();
	if( !empty( $attributes ) ) {
		foreach( $attributes as $attribute ) {
			if( isset( $order_item->{sprintf( 'attribute_%s', sanitize_key( $attribute->attribute_name ) )} ) )
				$order->{sprintf( 'order_item_%d_attribute_%s', $i, sanitize_key( $attribute->attribute_name ) )} = $order_item->{sprintf( 'attribute_%s', sanitize_key( $attribute->attribute_name ) )};
			if( isset( $order_item->{sprintf( 'product_attribute_%s', sanitize_key( $attribute->attribute_name ) )} ) )
				$order->{sprintf( 'order_item_%d_product_attribute_%s', $i, sanitize_key( $attribute->attribute_name ) )} = $order_item->{sprintf( 'product_attribute_%s', sanitize_key( $attribute->attribute_name ) )};
		}
		unset( $attributes, $attribute );
	}

	// Custom Order Items fields
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	if( !empty( $custom_order_items ) ) {
		foreach( $custom_order_items as $custom_order_item ) {
			if( !empty( $custom_order_item ) ) {
				if( isset( $order_item->{sanitize_key( $custom_order_item )} ) )
					$order->{sprintf( 'order_item_%d_%s', $i, sanitize_key( $custom_order_item ) )} = woo_ce_format_custom_meta( $order_item->{sanitize_key( $custom_order_item )} );
			}
		}
	}

	// Custom Order Item Product fields
	$custom_order_products = woo_ce_get_option( 'custom_order_products', '' );
	if( !empty( $custom_order_products ) ) {
		foreach( $custom_order_products as $custom_order_product ) {
			if( !empty( $custom_order_product ) ) {
				if( isset( $order_item->{sanitize_key( $custom_order_product )} ) )
					$order->{sprintf( 'order_item_%d_%s', $i, sanitize_key( $custom_order_product ) )} = woo_ce_format_custom_meta( $order_item->{sanitize_key( $custom_order_product )} );
			}
		}
	}

	// Custom Product fields
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if( !empty( $custom_products ) ) {
		foreach( $custom_products as $custom_product ) {
			if( !empty( $custom_product ) ) {
				if( isset( $order_item->{sanitize_key( $custom_product )} ) )
					$order->{sprintf( 'order_item_%d_%s', $i, sanitize_key( $custom_product ) )} = woo_ce_format_custom_meta( $order_item->{sanitize_key( $custom_product )} );
			}
		}
	}

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	return $order;

}
add_filter( 'woo_ce_order_items_unique', 'woo_ce_extend_order_items_unique', 10, 3 );

function woo_ce_extend_order_items_unique_fields_exclusion( $excluded_fields = array(), $fields = '' ) {

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	// Product Add-ons - http://www.woothemes.com/
	if( woo_ce_detect_export_plugin( 'product_addons' ) ) {
		$product_addons = woo_ce_get_product_addons();
		if( !empty( $product_addons ) ) {
			foreach( $product_addons as $product_addon ) {
				if( isset( $fields[sprintf( 'order_items_product_addon_%s', sanitize_key( $product_addon->post_name ) )] ) )
					$excluded_fields[] = sprintf( 'order_items_product_addon_%s', sanitize_key( $product_addon->post_name ) );
			}
			unset( $product_addons, $product_addon );
		}
	}

	// Gravity Forms - http://woothemes.com/woocommerce
	if( woo_ce_detect_export_plugin( 'gravity_forms' ) && woo_ce_detect_export_plugin( 'woocommerce_gravity_forms' ) ) {
		// Check if there are any Products linked to Gravity Forms
		$gf_fields = woo_ce_get_gravity_forms_fields();
		if( !empty( $gf_fields ) ) {
			if( isset( $fields['order_items_gf_form_id'] ) )
				$excluded_fields[] = 'order_items_gf_form_id';
			if( isset( $fields['order_items_gf_form_label'] ) )
				$excluded_fields[] = 'order_items_gf_form_label';
			foreach( $gf_fields as $gf_field ) {
				if( isset( $fields[sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] )] ) )
					$excluded_fields[] = sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] );
			}
		}
		unset( $gf_fields, $gf_field );
	}

	// WooCommerce Checkout Add-Ons - http://www.skyverge.com/product/woocommerce-checkout-add-ons/
	if( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
		if( isset( $fields['order_items_checkout_addon_id'] ) )
			$excluded_fields[] = 'order_items_checkout_addon_id';
		if( isset( $fields['order_items_checkout_addon_label'] ) )
			$excluded_fields[] = 'order_items_checkout_addon_label';
		if( isset( $fields['order_items_checkout_addon_value'] ) )
			$excluded_fields[] = 'order_items_checkout_addon_value';
	}

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if( woo_ce_detect_product_brands() ) {
		if( isset( $fields['order_items_brand'] ) )
			$excluded_fields[] = 'order_items_brand';
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		if( isset( $fields['order_items_vendor'] ) )
			$excluded_fields[] = 'order_items_vendor';
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/
	if( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		if( isset( $fields['order_items_cost_of_goods'] ) )
			$excluded_fields[] = 'order_items_cost_of_goods';
		if( isset( $fields['order_items_total_cost_of_goods'] ) )
			$excluded_fields[] = 'order_items_total_cost_of_goods';
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590
	if( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		if( isset( $fields['order_items_posr'] ) )
			$excluded_fields[] = 'order_items_posr';
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_msrp' ) ) {
		if( isset( $fields['order_items_msrp'] ) )
			$excluded_fields[] = 'order_items_msrp';
	}

	// Local Pickup Plus - http://www.woothemes.com/products/local-pickup-plus/
	if( woo_ce_detect_export_plugin( 'local_pickup_plus' ) ) {
		if( isset( $fields['order_items_pickup_location'] ) )
			$excluded_fields[] = 'order_items_pickup_location';
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	if( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		if( isset( $fields['order_items_booking_id'] ) )
			$excluded_fields[] = 'order_items_booking_id';
		if( isset( $fields['order_items_booking_date'] ) )
			$excluded_fields[] = 'order_items_booking_date';
		if( isset( $fields['order_items_booking_type'] ) )
			$excluded_fields[] = 'order_items_booking_type';
		if( isset( $fields['order_items_booking_start_date'] ) )
			$excluded_fields[] = 'order_items_booking_start_date';
		if( isset( $fields['order_items_booking_end_date'] ) )
			$excluded_fields[] = 'order_items_booking_end_date';
		if( isset( $fields['order_items_booking_all_day'] ) )
			$excluded_fields[] = 'order_items_booking_all_day';
		if( isset( $fields['order_items_booking_resource_id'] ) )
			$excluded_fields[] = 'order_items_booking_resource_id';
		if( isset( $fields['order_items_booking_resource_title'] ) )
			$excluded_fields[] = 'order_items_booking_resource_title';
		if( isset( $fields['order_items_booking_persons'] ) )
			$excluded_fields[] = 'order_items_booking_persons';
	}

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
	if( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
		$tm_fields = woo_ce_get_extra_product_option_fields();
		if( !empty( $tm_fields ) ) {
			foreach( $tm_fields as $tm_field ) {
				if( isset( $fields[sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) )] ) )
					$excluded_fields[] = sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) );
			}
		}
		unset( $tm_fields, $tm_field );
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
						foreach( $custom_fields as $custom_field ) {
							if( isset( $fields[sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) )] ) )
								$excluded_fields[] = sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) );
						}
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
					if( isset( $fields[sprintf( 'order_items_wccf_%s', sanitize_key( $key ) )] ) )
						$excluded_fields[] = sprintf( 'order_items_wccf_%s', sanitize_key( $key ) );
				}
			}
			unset( $custom_fields, $custom_field, $key );
		}
	}

	// WooCommerce Product Custom Options Lite - https://wordpress.org/plugins/woocommerce-custom-options-lite/
	if( woo_ce_detect_export_plugin( 'wc_product_custom_options' ) ) {
		$custom_options = woo_ce_get_product_custom_options();
		if( !empty( $custom_options ) ) {
			foreach( $custom_options as $custom_option ) {
				if( isset( $fields[sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) )] ) )
					$excluded_fields[] = sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) );
			}
		}
	}

	// Barcodes for WooCommerce - http://www.wolkenkraft.com/produkte/barcodes-fuer-woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_barcodes' ) ) {
		if( isset( $fields['order_items_barcode_type'] ) )
			$excluded_fields[] = 'order_items_barcode_type';
		if( isset( $fields['order_items_barcode'] ) )
			$excluded_fields[] = 'order_items_barcode';
	}

	// WooCommerce Easy Bookings - https://wordpress.org/plugins/woocommerce-easy-booking-system/
	if( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		if( isset( $fields['order_items_booking_start_date'] ) )
			$excluded_fields[] = 'order_items_booking_start_date';
		if( isset( $fields['order_items_booking_end_date'] ) )
			$excluded_fields[] = 'order_items_booking_end_date';
	}

	// N-Media WooCommerce Personalized Product Meta Manager
	if( woo_ce_detect_export_plugin( 'wc_nm_personalizedproduct' ) ) {
		$custom_fields = woo_ce_get_nm_personalized_product_fields();
		if( !empty( $custom_fields ) ) {
			foreach( $custom_fields as $custom_field ) {
				if( isset( $fields[sprintf( 'order_items_nm_%s', $custom_field['name'] )] ) )
					$excluded_fields[] = sprintf( 'order_items_nm_%s', $custom_field['name'] );
			}
		}
	}

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/
	if( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		if( isset( $fields['order_items_appointment_id'] ) )
			$excluded_fields[] = 'order_items_appointment_id';
		if( isset( $fields['order_items_booking_start_date'] ) )
			$excluded_fields[] = 'order_items_booking_start_date';
		if( isset( $fields['order_items_booking_start_time'] ) )
			$excluded_fields[] = 'order_items_booking_start_time';
		if( isset( $fields['order_items_booking_end_date'] ) )
			$excluded_fields[] = 'order_items_booking_end_date';
		if( isset( $fields['order_items_booking_end_time'] ) )
			$excluded_fields[] = 'order_items_booking_end_time';
		if( isset( $fields['order_items_booking_all_day'] ) )
			$excluded_fields[] = 'order_items_booking_all_day';
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/
	if( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if( !empty( $wholesale_roles ) ) {
			foreach( $wholesale_roles as $key => $wholesale_role ) {
				if( isset( $fields[sprintf( 'order_items_%s_wholesale_price', $key )] ) )
					$excluded_fields[] = sprintf( 'order_items_%s_wholesale_price', $key );
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	// Tax Rates
	$tax_rates = woo_ce_get_order_tax_rates();
	if( !empty( $tax_rates ) ) {
		foreach( $tax_rates as $tax_rate ) {
			if( isset( $fields[sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] )] ) )
				$excluded_fields[] = sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] );
		}
	}
	unset( $tax_rates, $tax_rate );

	// Attributes
	$attributes = woo_ce_get_product_attributes();
	if( !empty( $attributes ) ) {
		foreach( $attributes as $attribute ) {
			if( isset( $fields[sprintf( 'order_items_attribute_%s', sanitize_key( $attribute->attribute_name ) )] ) )
				$excluded_fields[] = sprintf( 'order_items_attribute_%s', sanitize_key( $attribute->attribute_name ) );
			if( isset( $fields[sprintf( 'order_items_product_attribute_%s', sanitize_key( $attribute->attribute_name ) )] ) )
				$excluded_fields[] = sprintf( 'order_items_product_attribute_%s', sanitize_key( $attribute->attribute_name ) );
		}
	}
	unset( $attributes, $attribute );

	// Custom Order Items fields
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	if( !empty( $custom_order_items ) ) {
		foreach( $custom_order_items as $custom_order_item ) {
			if( !empty( $custom_order_item ) ) {
				if( isset( $fields[sprintf( 'order_items_%s', $custom_order_item )] ) )
					$excluded_fields[] = sprintf( 'order_items_%s', $custom_order_item );
			}
		}
	}
	unset( $custom_order_items, $custom_order_item );

	// Custom Order Item Product fields
	$custom_order_products = woo_ce_get_option( 'custom_order_products', '' );
	if( !empty( $custom_order_products ) ) {
		foreach( $custom_order_products as $custom_order_product ) {
			if( isset( $fields[sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) )] ) )
				$excluded_fields[] = sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) );
		}
	}
	unset( $custom_order_products, $custom_order_product );

	// Custom Product fields
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if( !empty( $custom_products ) ) {
		foreach( $custom_products as $custom_product ) {
			if( isset( $fields[sprintf( 'order_items_%s', sanitize_key( $custom_product ) )] ) )
				$excluded_fields[] = sprintf( 'order_items_%s', sanitize_key( $custom_product ) );
		}
	}
	unset( $custom_products, $custom_product );

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	return $excluded_fields;

}
add_filter( 'woo_ce_add_unique_order_item_fields_exclusion', 'woo_ce_extend_order_items_unique_fields_exclusion', 10, 2 );

// This prepares the Order columns for the 'unique' Order Item formatting selection
function woo_ce_unique_order_item_fields_on( $fields = array(), $i = 0 ) {

	// Product Add-ons - http://www.woothemes.com/
	if( woo_ce_detect_export_plugin( 'product_addons' ) ) {
		$product_addons = woo_ce_get_product_addons();
		if( !empty( $product_addons ) ) {
			foreach( $product_addons as $product_addon ) {
				if( isset( $fields[sprintf( 'order_items_product_addon_%s', sanitize_key( $product_addon->post_name ) )] ) )
					$fields[sprintf( 'order_item_%d_product_addon_%s', $i, sanitize_key( $product_addon->post_name ) )] = 'on';
			}
		}
	}

	// Gravity Forms - http://woothemes.com/woocommerce
	if( woo_ce_detect_export_plugin( 'gravity_forms' ) && woo_ce_detect_export_plugin( 'woocommerce_gravity_forms' ) ) {
		// Check if there are any Products linked to Gravity Forms
		if( isset( $fields['order_items_gf_form_id'] ) )
			$fields[sprintf( 'order_item_%d_gf_form_id', $i )] = 'on';
		if( isset( $fields['order_items_gf_form_label'] ) )
			$fields[sprintf( 'order_item_%d_gf_form_label', $i )] = 'on';
		// Check if there are any Products linked to Gravity Forms
		$gf_fields = woo_ce_get_gravity_forms_fields();
		if( !empty( $gf_fields ) ) {
			foreach( $gf_fields as $key => $gf_field ) {
				if( isset( $fields[sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] )] ) )
					$fields[sprintf( 'order_item_%d_gf_%d_%s', $i, $gf_field['formId'], $gf_field['id'] )] = 'on';
			}
			unset( $gf_fields, $gf_field );
		}
	}

	// WooCommerce Checkout Add-Ons - http://www.skyverge.com/product/woocommerce-checkout-add-ons/
	if( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
		if( isset( $fields['order_items_checkout_addon_id'] ) )
			$fields[sprintf( 'order_item_%d_checkout_addon_id', $i )] = 'on';
		if( isset( $fields['order_items_checkout_addon_label'] ) )
			$fields[sprintf( 'order_item_%d_checkout_addon_label', $i )] = 'on';
		if( isset( $fields['order_items_checkout_addon_value'] ) )
			$fields[sprintf( 'order_item_%d_checkout_addon_value', $i )] = 'on';
	}

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if( woo_ce_detect_product_brands() ) {
		if( isset( $fields['order_items_brand'] ) )
			$fields[sprintf( 'order_item_%d_brand', $i )] = 'on';
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		if( isset( $fields['order_items_vendor'] ) )
			$fields[sprintf( 'order_item_%d_vendor', $i )] = 'on';
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/
	if( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		if( isset( $fields['order_items_cost_of_goods'] ) )
			$fields[sprintf( 'order_item_%d_cost_of_goods', $i )] = 'on';
		if( isset( $fields['order_items_total_cost_of_goods'] ) )
			$fields[sprintf( 'order_item_%d_total_cost_of_goods', $i )] = 'on';
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590
	if( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		if( isset( $fields['order_items_posr'] ) )
			$fields[sprintf( 'order_item_%d_posr', $i )] = 'on';
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_msrp' ) ) {
		if( isset( $fields['order_items_msrp'] ) )
			$fields[sprintf( 'order_item_%d_msrp', $i )] = 'on';
	}

	// Local Pickup Plus - http://www.woothemes.com/products/local-pickup-plus/
	if( woo_ce_detect_export_plugin( 'local_pickup_plus' ) ) {
		if( isset( $fields['order_items_pickup_location'] ) )
			$fields[sprintf( 'order_item_%d_pickup_location', $i )] = 'on';
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	if( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		if( isset( $fields['order_items_booking_id'] ) )
			$fields[sprintf( 'order_item_%d_booking_id', $i )] = 'on';
		if( isset( $fields['order_items_booking_date'] ) )
			$fields[sprintf( 'order_item_%d_booking_date', $i )] = 'on';
		if( isset( $fields['order_items_booking_type'] ) )
			$fields[sprintf( 'order_item_%d_booking_type', $i )] = 'on';
		if( isset( $fields['order_items_booking_start_date'] ) )
			$fields[sprintf( 'order_item_%d_booking_start_date', $i )] = 'on';
		if( isset( $fields['order_items_booking_start_date'] ) )
			$fields[sprintf( 'order_item_%d_booking_start_date', $i )] = 'on';
	}

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
	if( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
		$tm_fields = woo_ce_get_extra_product_option_fields();
		if( !empty( $tm_fields ) ) {
			foreach( $tm_fields as $tm_field ) {
				if( isset( $fields[sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) )] ) )
					$fields[sprintf( 'order_item_%d_tm_%s', $i, sanitize_key( $tm_field['name'] ) )] = 'on';
			}
		}
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
	if( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		$meta_type = 'order_item';
		if( !get_option( 'wccf_migrated_to_20' ) ) {
			$options = get_option( 'rp_wccf_options' );
			if( !empty( $options ) ) {
				$options = ( isset( $options[1] ) ? $options[1] : false );
				if( !empty( $options ) ) {
					// Product Fields
					$custom_fields = ( isset( $options['product_fb_config'] ) ? $options['product_fb_config'] : false );
					if( !empty( $custom_fields ) ) {
						foreach( $custom_fields as $custom_field ) {
							if( isset( $fields[sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) )] ) )
								$fields[sprintf( 'order_item_%d_wccf_%s', $i, sanitize_key( $custom_field['key'] ) )] = 'on';
						}
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
					if( isset( $fields[sprintf( 'order_items_wccf_%s', sanitize_key( $key ) )] ) )
						$fields[sprintf( 'order_item_%d_wccf_%s', $i, sanitize_key( $key ) )] = 'on';
				}
			}
			unset( $custom_fields, $custom_field, $key );
		}
	}

	// WooCommerce Easy Bookings - https://wordpress.org/plugins/woocommerce-easy-booking-system/

	// N-Media WooCommerce Personalized Product Meta Manager

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/
	if( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		if( isset( $fields['order_items_appointment_id'] ) )
			$fields[sprintf( 'order_item_%d_appointment_id', $i )] = 'on';
		if( isset( $fields['order_items_booking_start_date'] ) )
			$fields[sprintf( 'order_item_%d_booking_start_date', $i )] = 'on';
		if( isset( $fields['order_items_booking_start_time'] ) )
			$fields[sprintf( 'order_item_%d_booking_start_time', $i )] = 'on';
		if( isset( $fields['order_items_booking_end_date'] ) )
			$fields[sprintf( 'order_item_%d_booking_end_date', $i )] = 'on';
		if( isset( $fields['order_items_booking_end_time'] ) )
			$fields[sprintf( 'order_item_%d_booking_end_time', $i )] = 'on';
		if( isset( $fields['order_items_booking_all_day'] ) )
			$fields[sprintf( 'order_item_%d_booking_all_day', $i )] = 'on';
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/
	if( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if( !empty( $wholesale_roles ) ) {
			foreach( $wholesale_roles as $key => $wholesale_role ) {
				if( isset( $fields[sprintf( 'order_items_%s_wholesale_price', $key )] ) )
					$fields[sprintf( 'order_item_%d_%s_wholesale_price', $i, $key )] = 'on';
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	// Tax Rates
	$tax_rates = woo_ce_get_order_tax_rates();
	if( !empty( $tax_rates ) ) {
		foreach( $tax_rates as $tax_rate ) {
			if( isset( $fields[sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] )] ) )
				$fields[sprintf( 'order_item_%d_tax_rate_%d', $i, $tax_rate['rate_id'] )] = 'on';
		}
	}
	unset( $tax_rates, $tax_rate );

	// Attributes
	$attributes = woo_ce_get_product_attributes();
	if( !empty( $attributes ) ) {
		foreach( $attributes as $attribute ) {
			if( isset( $fields[sprintf( 'order_items_attribute_%s', sanitize_key( $attribute->attribute_name ) )] ) )
				$fields[sprintf( 'order_item_%d_attribute_%s', $i, sanitize_key( $attribute->attribute_name ) )] = 'on';
			if( isset( $fields[sprintf( 'order_items_product_attribute_%s', sanitize_key( $attribute->attribute_name ) )] ) )
				$fields[sprintf( 'order_item_%d_product_attribute_%s', $i, sanitize_key( $attribute->attribute_name ) )] = 'on';
		}
	}

	// Custom Order Items fields
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	if( !empty( $custom_order_items ) ) {
		foreach( $custom_order_items as $custom_order_item ) {
			if( !empty( $custom_order_item ) ) {
				if( isset( $fields[sprintf( 'order_items_%s', $custom_order_item )] ) )
					$fields[sprintf( 'order_item_%d_%s', $i, $custom_order_item )] = 'on';
			}
		}
	}

	// Custom Order Item Product fields
	$custom_order_products = woo_ce_get_option( 'custom_order_products', '' );
	if( !empty( $custom_order_products ) ) {
		foreach( $custom_order_products as $custom_order_product ) {
			if( !empty( $custom_order_product ) ) {
				if( isset( $fields[sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) )] ) )
					$fields[sprintf( 'order_item_%d_%s', $i, sanitize_key( $custom_order_product ) )] = 'on';
			}
		}
	}

	// Custom Product fields
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if( !empty( $custom_products ) ) {
		foreach( $custom_products as $custom_product ) {
			if( !empty( $custom_product ) ) {
				if( isset( $fields[sprintf( 'order_items_%s', sanitize_key( $custom_product ) )] ) )
					$fields[sprintf( 'order_item_%d_%s', $i, sanitize_key( $custom_product ) )] = 'on';
			}
		}
	}

	return $fields;

}
add_filter( 'woo_ce_add_unique_order_item_fields_on', 'woo_ce_unique_order_item_fields_on', 10, 2 );

function woo_ce_extend_order_items_unique_columns( $fields = array(), $i = 0, $original_columns = array() ) {

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	// Product Add-ons - http://www.woothemes.com/
	if( woo_ce_detect_export_plugin( 'product_addons' ) ) {
		$product_addons = woo_ce_get_product_addons();
		if( !empty( $product_addons ) ) {
			foreach( $product_addons as $product_addon ) {
				if( isset( $original_columns[sprintf( 'order_item_%d_product_addon_%s', $i, sanitize_key( $product_addon->post_name ) )] ) )
					$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, sanitize_key( $product_addon->post_title ) );
			}
		}
	}

	// WooCommerce Checkout Add-Ons - http://www.skyverge.com/product/woocommerce-checkout-add-ons/
	if( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
		if( isset( $original_columns[sprintf( 'order_item_%d_checkout_addon_id', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_checkout_addon_id', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_checkout_addon_label', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_checkout_addon_label', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_checkout_addon_value', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_checkout_addon_value', 'name', 'unique' ) );
	}

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if( woo_ce_detect_product_brands() ) {
		if( isset( $original_columns[sprintf( 'order_item_%d_brand', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_brand', 'name', 'unique' ) );
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		if( isset( $original_columns[sprintf( 'order_item_%d_vendor', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_vendor', 'name', 'unique' ) );
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/
	if( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		if( isset( $original_columns[sprintf( 'order_item_%d_cost_of_goods', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_cost_of_goods', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_total_cost_of_goods', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_total_cost_of_goods', 'name', 'unique' ) );
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590
	if( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		if( isset( $original_columns[sprintf( 'order_item_%d_posr', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_posr', 'name', 'unique' ) );
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_msrp' ) ) {
		if( isset( $original_columns[sprintf( 'order_item_%d_msrp', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_msrp', 'name', 'unique' ) );
	}

	// Gravity Forms - http://woothemes.com/woocommerce
	if( woo_ce_detect_export_plugin( 'gravity_forms' ) && woo_ce_detect_export_plugin( 'woocommerce_gravity_forms' ) ) {
		if( isset( $original_columns[sprintf( 'order_item_%d_gf_form_id', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_gf_form_id', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_gf_form_label', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_gf_form_label', 'name', 'unique' ) );
		// Check if there are any Products linked to Gravity Forms
		$gf_fields = woo_ce_get_gravity_forms_fields();
		if( !empty( $gf_fields ) ) {
			foreach( $gf_fields as $key => $gf_field ) {
				if( isset( $original_columns[sprintf( 'order_item_%d_gf_%d_%s', $i, $gf_field['formId'], $gf_field['id'] )] ) )
					$fields[] = sprintf( apply_filters( 'woo_ce_extend_order_items_unique_columns_gf_fields', __( 'Order Item #%d: %s - %s', 'woocommerce-exporter' ) ), $i, $gf_field['formTitle'], $gf_field['label'] );
			}
			unset( $gf_fields, $gf_field );
		}
	}

	// Local Pickup Plus - http://www.woothemes.com/products/local-pickup-plus/
	if( woo_ce_detect_export_plugin( 'local_pickup_plus' ) ) {
		if( isset( $original_columns[sprintf( 'order_item_%d_pickup_location', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_pickup_location', 'name', 'unique' ) );
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	if( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_id', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_id', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_date', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_date', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_type', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_type', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_start_date', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_start_date', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_end_date', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_end_date', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_all_day', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_all_day', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_resource_id', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_resource_id', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_resource_title', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_resource_title', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_persons', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_persons', 'name', 'unique' ) );
	}

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
	if( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
		$tm_fields = woo_ce_get_extra_product_option_fields();
		if( !empty( $tm_fields ) ) {
			foreach( $tm_fields as $tm_field ) {
				if( isset( $original_columns[sprintf( 'order_item_%d_tm_%s', $i, sanitize_key( $tm_field['name'] ) )] ) )
					$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, ( !empty( $tm_field['section_label'] ) ? $tm_field['section_label'] : $tm_field['name'] ) );
			}
		}
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
	if( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		$meta_type = 'order_item';
		if( !get_option( 'wccf_migrated_to_20' ) ) {
			$options = get_option( 'rp_wccf_options' );
			if( !empty( $options ) ) {
				$options = ( isset( $options[1] ) ? $options[1] : false );
				if( !empty( $options ) ) {
					// Product Fields
					$custom_fields = ( isset( $options['product_fb_config'] ) ? $options['product_fb_config'] : false );
					if( !empty( $custom_fields ) ) {
						foreach( $custom_fields as $custom_field ) {
							if( isset( $original_columns[sprintf( 'order_item_%d_wccf_%s', $i, sanitize_key( $custom_field['key'] ) )] ) )
								$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, ucfirst( $custom_field['label'] ) );
						}
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
					$label = get_post_meta( $custom_field->ID, 'label', true );
					$key = get_post_meta( $custom_field->ID, 'key', true );
					if( isset( $original_columns[sprintf( 'order_item_%d_wccf_%s', $i, sanitize_key( $key ) )] ) )
						$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, ucfirst( $label ) );
				}
			}
			unset( $custom_fields, $custom_field, $label, $key );
		}
	}

	// WooCommerce Easy Bookings - https://wordpress.org/plugins/woocommerce-easy-booking-system/
	if( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_start_date', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: Start Date', 'woocommerce-exporter' ), $i );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_end_date', $i, $tax_rate['rate_id'] )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: End Date', 'woocommerce-exporter' ), $i );
	}

	// N-Media WooCommerce Personalized Product Meta Manager
	if( woo_ce_detect_export_plugin( 'wc_nm_personalizedproduct' ) ) {
		$custom_fields = woo_ce_get_nm_personalized_product_fields();
		if( !empty( $custom_fieds ) ) {
			foreach( $custom_fields as $custom_field ) {
				if( isset( $original_columns[sprintf( 'order_item_%d_tm_%s', $i, $custom_field['name'] )] ) )
					$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, $custom_field['name'] );
			}
		}
	}

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/
	if( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		if( isset( $original_columns[sprintf( 'order_item_%d_appointment_id', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_appointment_id', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_start_date', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_start_date', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_start_time', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_start_time', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_end_date', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_end_date', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_end_time', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_end_time', 'name', 'unique' ) );
		if( isset( $original_columns[sprintf( 'order_item_%d_booking_all_day', $i )] ) )
			$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_booking_all_day', 'name', 'unique' ) );
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/
	if( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if( !empty( $wholesale_roles ) ) {
			foreach( $wholesale_roles as $key => $wholesale_role ) {
				if( isset( $original_columns[sprintf( 'order_item_%d_%s_wholesale_price', $i, $key )] ) )
					$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( sprintf( 'order_items_%s_wholesale_price', $key ), 'name', 'unique' ) );
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	// Tax Rates
	$tax_rates = woo_ce_get_order_tax_rates();
	if( !empty( $tax_rates ) ) {
		foreach( $tax_rates as $tax_rate ) {
			if( isset( $original_columns[sprintf( 'order_item_%d_tax_rate_%d', $i, $tax_rate['rate_id'] )] ) )
				$fields[] = sprintf( __( 'Order Item #%d: Tax Rate - %s', 'woocommerce-exporter' ), $i, $tax_rate['label'] );
		}
	}
	unset( $tax_rates, $tax_rate );

	// Attributes
	$attributes = woo_ce_get_product_attributes();
	if( !empty( $attributes ) ) {
		foreach( $attributes as $attribute ) {
			if( isset( $original_columns[sprintf( 'order_item_%d_attribute_%s', $i, sanitize_key( $attribute->attribute_name ) )] ) ) {
				if( empty( $attribute->attribute_label ) )
					$attribute->attribute_label = $attribute->attribute_name;
				$fields[] = sprintf( __( 'Order Item #%d: %s Variation', 'woocommerce-exporter' ), $i, $attribute->attribute_label );
			}
			if( isset( $original_columns[sprintf( 'order_item_%d_product_attribute_%s', $i, sanitize_key( $attribute->attribute_name ) )] ) ) {
				if( empty( $attribute->attribute_label ) )
					$attribute->attribute_label = $attribute->attribute_name;
				$fields[] = sprintf( __( 'Order Item #%d: %s Attribute', 'woocommerce-exporter' ), $i, $attribute->attribute_label );
			}
		}
	}

	// Custom Order Items fields
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	if( !empty( $custom_order_items ) ) {
		foreach( $custom_order_items as $custom_order_item ) {
			if( !empty( $custom_order_item ) ) {
				if( isset( $original_columns[sprintf( 'order_item_%d_%s', $i, $custom_order_item )] ) )
					$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, $custom_order_item );
			}
		}
	}

	// Custom Order Item Product fields
	$custom_products = woo_ce_get_option( 'custom_order_products', '' );
	if( !empty( $custom_order_products ) ) {
		foreach( $custom_order_products as $custom_order_product ) {
			if( !empty( $custom_order_product ) ) {
				if( isset( $original_columns[sprintf( 'order_item_%d_%s', $i, sanitize_key( $custom_order_product ) )] ) )
					$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, sanitize_key( $custom_order_product ) );
			}
		}
	}

	// Custom Product fields
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if( !empty( $custom_products ) ) {
		foreach( $custom_products as $custom_product ) {
			if( !empty( $custom_product ) ) {
				if( isset( $original_columns[sprintf( 'order_item_%d_%s', $i, sanitize_key( $custom_product ) )] ) )
					$fields[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, sanitize_key( $custom_product ) );
			}
		}
	}

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	return $fields;

}
add_filter( 'woo_ce_unique_order_item_columns', 'woo_ce_extend_order_items_unique_columns', 10, 3 );
?>