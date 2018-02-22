<?php
// Order items formatting: Combined
function woo_ce_extend_order_items_combined( $order ) {

	global $export;

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	// Product Add-ons - http://www.woothemes.com/
	if( woo_ce_detect_export_plugin( 'product_addons' ) && $order->order_items ) {
		foreach( $order->order_items as $order_item ) {
			if( isset( $order_item->product_addons_summary ) )
				$order->order_items_product_addons_summary .= $order_item->product_addons_summary . $export->category_separator;
		}
		if( isset( $order->order_items_product_addons_summary ) )
			$order->order_items_product_addons_summary = substr( $order->order_items_product_addons_summary, 0, -1 );
		if( $product_addons = woo_ce_get_product_addons() ) {
			foreach( $product_addons as $product_addon ) {
				foreach( $order->order_items as $order_item ) {
					if( isset( $order_item->product_addons[sanitize_key( $product_addon->post_name )] ) )
						$order->{sprintf( 'order_items_product_addon_%s', sanitize_key( $product_addon->post_name ) )} .= $order_item->product_addons[sanitize_key( $product_addon->post_name )] . $export->category_separator;
				}
				if( isset( $order->{sprintf( 'order_items_product_addon_%s', sanitize_key( $product_addon->post_name ) )} ) )
					$order->{sprintf( 'order_items_product_addon_%s', sanitize_key( $product_addon->post_name ) )} = substr( $order->{sprintf( 'order_items_product_addon_%s', sanitize_key( $product_addon->post_name ) )}, 0, -1 );
			}
			unset( $product_addons, $product_addon );
		}
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_product_addons(): ' . ( time() - $export->start_time ) ) );
	}

	// Gravity Forms - http://woothemes.com/woocommerce
	if( ( woo_ce_detect_export_plugin( 'gravity_forms' ) && woo_ce_detect_export_plugin( 'woocommerce_gravity_forms' ) ) && $order->order_items ) {
		// Check if there are any Products linked to Gravity Forms
		$gf_fields = woo_ce_get_gravity_form_fields();
		if( !empty( $gf_fields ) ) {
			$meta_type = 'order_item';
			$order->order_items_gf_form_id = '';
			$order->order_items_gf_form_label = '';
			foreach( $order->order_items as $order_item ) {
				$gravity_forms_history = get_metadata( $meta_type, $order_item->id, '_gravity_forms_history', true );
				// Check that Gravity Forms Order item meta isn't empty
				if( !empty( $gravity_forms_history ) ) {
					if( isset( $gravity_forms_history['_gravity_form_data'] ) ) {
						$order->order_items_gf_form_id .= $gravity_forms_history['_gravity_form_data']['id'] . $export->category_separator;
						$gravity_form = ( method_exists( 'RGFormsModel', 'get_form' ) ? RGFormsModel::get_form( $gravity_forms_history['_gravity_form_data']['id'] ) : array() );
						$order->order_items_gf_form_label .= ( !empty( $gravity_form ) ? $gravity_form->title : '' ) . $export->category_separator;
						unset( $gravity_form );
						foreach( $gf_fields as $gf_field ) {
							// Check that we only fill export fields for forms that are actually filled
							if( $gf_field['formId'] == $gravity_forms_history['_gravity_form_data']['id'] )
								$order->{sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] )} .= get_metadata( $meta_type, $order_item->id, $gf_field['label'], true ) . $export->category_separator;
						}
					}
				}
				unset( $gravity_forms_history );
			}
			if( isset( $order->order_items_gf_form_id ) )
				$order->order_items_gf_form_id = substr( $order->order_items_gf_form_id, 0, -1 );
			if( isset( $order->order_items_gf_form_label ) )
				$order->order_items_gf_form_label = substr( $order->order_items_gf_form_label, 0, -1 );
			if( isset( $order->{sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] )} ) )
				$order->{sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] )} = substr( $order->{sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] )}, 0, -1 );
		}
		unset( $gf_fields, $gf_field );
	}

	// WooCommerce Checkout Add-Ons - http://www.skyverge.com/product/woocommerce-checkout-add-ons/
	if( woo_ce_detect_export_plugin( 'checkout_addons' ) && $order->order_items ) {
		$meta_type = 'order_item';
		foreach( $order->order_items as $order_item ) {
			$order->order_items_checkout_addon_id .= $order_item->checkout_addon_id . $export->category_separator;
			$order->order_items_checkout_addon_label .= $order_item->checkout_addon_label . $export->category_separator;
			$order->order_items_checkout_addon_value .= $order_item->checkout_addon_value . $export->category_separator;
		}
		if( isset( $order->order_items_checkout_addon_id ) )
			$order->order_items_checkout_addon_id = substr( $order->order_items_checkout_addon_id, 0, -1 );
		if( isset( $order->order_items_checkout_addon_label ) )
			$order->order_items_checkout_addon_label = substr( $order->order_items_checkout_addon_label, 0, -1 );
		if( isset( $order->order_items_checkout_addon_value ) )
			$order->order_items_checkout_addon_value = substr( $order->order_items_checkout_addon_value, 0, -1 );
	}

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if( woo_ce_detect_product_brands() && $order->order_items ) {
		$meta_type = 'order_item';
		foreach( $order->order_items as $order_item )
			$order->order_items_brand .= woo_ce_get_product_assoc_brands( $order_item->product_id ) . $export->category_separator;
		if( isset( $order->order_items_brand ) )
			$order->order_items_brand = substr( $order->order_items_brand, 0, -1 );
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( ( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) ) && $order->order_items ) {
		$meta_type = 'order_item';
		foreach( $order->order_items as $order_item )
			$order->order_items_vendor = woo_ce_get_product_assoc_product_vendors( $order_item->product_id ) . $export->category_separator;
		if( isset( $order->order_items_vendor ) )
			$order->order_items_vendor = substr( $order->order_items_vendor, 0, -1 );
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/
	if( woo_ce_detect_export_plugin( 'wc_cog' ) && $order->order_items ) {
		$meta_type = 'order_item';
		foreach( $order->order_items as $order_item ) {
			$order->order_items_cost_of_goods .= woo_ce_format_price( get_metadata( $meta_type, $order_item->id, '_wc_cog_item_cost', true ), $order->order_currency ) . $export->category_separator;
			$order->order_items_total_cost_of_goods .= woo_ce_format_price( get_metadata( $meta_type, $order_item->id, '_wc_cog_item_total_cost', true ), $order->order_currency ) . $export->category_separator;
		}
		if( isset( $order->order_items_cost_of_goods ) )
			$order->order_items_cost_of_goods = substr( $order->order_items_cost_of_goods, 0, -1 );
		if( isset( $order->order_items_total_cost_of_goods ) )
			$order->order_items_total_cost_of_goods = substr( $order->order_items_total_cost_of_goods, 0, -1 );
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590
	if( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		$meta_type = 'order_item';
		foreach( $order->order_items as $order_item ) {
			$order->order_items_posr .= woo_ce_format_price( get_metadata( $meta_type, $order_item->id, '_posr_line_cog_total', true ), $order->order_currency ) . $export->category_separator;
		}
		if( isset( $order->order_items_posr ) )
			$order->order_items_posr = substr( $order->order_items_posr, 0, -1 );
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_msrp' ) && $order->order_items ) {
		foreach( $order->order_items as $order_item ) {
			$order->order_items_msrp .= woo_ce_format_price( get_post_meta( $order_item->product_id, '_msrp_price', true ) ) . $export->category_separator;
		}
		if( isset( $order->order_items_msrp ) )
			$order->order_items_msrp = substr( $order->order_items_msrp, 0, -1 );
	}

	// Local Pickup Plus - http://www.woothemes.com/products/local-pickup-plus/
	if( woo_ce_detect_export_plugin( 'local_pickup_plus' ) && $order->order_items ) {
		$meta_type = 'order_item';
		$order->order_items_pickup_location = '';
		foreach( $order->order_items as $order_item ) {
			$pickup_location = get_metadata( $meta_type, $order_item->id, 'Pickup Location', true );
			if( !empty( $pickup_location ) )
				$order->order_items_pickup_location .= get_metadata( $meta_type, $order_item->id, 'Pickup Location', true ) . $export->category_separator;
			unset( $pickup_location );
		}
		if( isset( $order->order_items_pickup_location ) )
			$order->order_items_pickup_location = substr( $order->order_items_pickup_location, 0, -1 );
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	if( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) && $order->order_items ) {
		$meta_type = 'order_item';
		$order->order_items_booking_id = '';
		$order->order_items_booking_date = '';
		$order->order_items_booking_type = '';
		$order->order_items_booking_start_date = '';
		$order->order_items_booking_end_date = '';
		$order->order_items_booking_all_day = '';
		$order->order_items_booking_resource_id = '';
		$order->order_items_booking_resource_title = '';
		$order->order_items_booking_persons = '';
		foreach( $order->order_items as $order_item ) {
			$booking_id = woo_ce_get_order_assoc_booking_id( $order->id );
			if( !empty( $booking_id ) ) {
				// @mod - Are we double querying here? Check in 2.4+
				$order->order_items_booking_id .= $booking_id . $export->category_separator;
				$booking_start_date = get_post_meta( $booking_id, '_booking_start', true );
				if( !empty( $booking_start_date ) )
					$order->order_items_start_date .= woo_ce_format_date( date( 'Y-m-d', strtotime( $booking_start_date ) ) ) . $export->category_separator;
				unset( $booking_start_date );
				$booking_end_date = get_post_meta( $booking_id, '_booking_end', true );
				if( !empty( $booking_end_date ) )
					$order->order_items_booking_end_date .= woo_ce_format_date( date( 'Y-m-d', strtotime( $booking_end_date ) ) ) . $export->category_separator;
				unset( $booking_end_date );
				// All Day Booking
				$booking_all_day = woo_ce_format_switch( get_post_meta( $booking_id, '_booking_all_day', true ) );
				if( !empty( $booking_all_day ) )
					$order->order_items_booking_all_day .= $booking_all_day;
				unset( $booking_all_day );
				// Booking Resource ID
				$booking_resource_id = get_post_meta( $booking_id, '_booking_resource_id', true );
				if( !empty( $booking_resource_id ) )
					$order->order_items_booking_resource_id .= $booking_resource_id;
				unset( $booking_resource_id );
				// Booking Resource Name
				if( !empty( $order->order_items_booking_resource_id ) ) {
					$booking_resource_title = get_the_title( $order->order_items_booking_resource_id );
					if( !empty( $booking_resource_title ) )
						$order->order_items_booking_resource_title .= $booking_resource_title;
					unset( $booking_resource_title );
				}
				// Booking # of Persons
				$booking_persons = get_post_meta( $booking_id, '_booking_persons', true );
				$order->order_items_booking_persons .= ( !empty( $booking_persons ) ? $booking_persons : '-' );
				unset( $booking_persons );
			}
			unset( $booking_id );
			$booking_date = get_metadata( $meta_type, $order_item->id, __( 'Booking Date', 'woocommerce-bookings' ), true );
			if( !empty( $booking_date ) )
				$order->order_items_booking_date .= get_metadata( $meta_type, $order_item->id, __( 'Booking Date', 'woocommerce-bookings' ), true ) . $export->category_separator;
			unset( $booking_date );
			$booking_type = get_metadata( $meta_type, $order_item->id, __( 'Booking Type', 'woocommerce-bookings' ), true );
			if( !empty( $booking_type ) )
				$order->order_items_booking_type .= get_metadata( $meta_type, $order_item->id, __( 'Booking Type', 'woocommerce-bookings' ), true ) . $export->category_separator;
			unset( $booking_type );
		}
		if( isset( $order->order_items_booking_id ) )
			$order->order_items_booking_id = substr( $order->order_items_booking_id, 0, -1 );
		if( isset( $order->order_items_booking_date ) )
			$order->order_items_booking_date = substr( $order->order_items_booking_date, 0, -1 );
		if( isset( $order->order_items_booking_type ) )
			$order->order_items_booking_type = substr( $order->order_items_booking_type, 0, -1 );
		if( isset( $order->order_items_booking_start_date ) )
			$order->order_items_booking_start_date = substr( $order->order_items_booking_start_date, 0, -1 );
		if( isset( $order->order_items_booking_end_date ) )
			$order->order_items_booking_end_date = substr( $order->order_items_booking_end_date, 0, -1 );
	}

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
	if( woo_ce_detect_export_plugin( 'extra_product_options' ) && $order->order_items ) {
		if( $tm_fields = woo_ce_get_extra_product_option_fields() ) {
			foreach( $tm_fields as $tm_field )
				$order->{sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) )} = '';
		}
		foreach( $order->order_items as $order_item ) {
			if( $tm_fields = woo_ce_get_extra_product_option_fields( $order_item->id ) ) {
				foreach( $tm_fields as $tm_field ) {
					if( isset( $order_item->{sprintf( 'tm_%s', sanitize_key( $tm_field['name'] ) )} ) )
						$order->{sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) )} .= woo_ce_get_extra_product_option_value( $order_item->id, $tm_field ) . $export->category_separator;
				}
			}
		}
		if( $tm_fields = woo_ce_get_extra_product_option_fields() ) {
			foreach( $tm_fields as $tm_field ) {
				if( isset( $order->{sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) )} ) )
					$order->{sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) )} = substr( $order->{sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) )}, 0, -1 );
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
							$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) )} = '';
						}
						foreach( $order->order_items as $order_item ) {
							foreach( $custom_fields as $custom_field ) {
								if( isset( $order_item->{sprintf( 'wccf_%s', sanitize_key( $custom_field['key'] ) )} ) )
									$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) )} .= $order_item->{sprintf( 'wccf_%s', sanitize_key( $custom_field['key'] ) )} . $export->category_separator;
							}
						}
						foreach( $custom_fields as $custom_field ) {
							if( isset( $order->{sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) )} ) )
								$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) )} = substr( $order->{sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) )}, 0, -1 );
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
					$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $key ) )} = '';
				}
				foreach( $order->order_items as $order_item ) {
					foreach( $custom_fields as $custom_field ) {
						$key = get_post_meta( $custom_field->ID, 'key', true );
						if( isset( $order_item->{sprintf( 'wccf_%s', sanitize_key( $key ) )} ) )
							$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $key ) )} .= $order_item->{sprintf( 'wccf_%s', sanitize_key( $key ) )} . $export->category_separator;
					}
				}
				foreach( $custom_fields as $custom_field ) {
					$key = get_post_meta( $custom_field->ID, 'key', true );
					if( isset( $order->{sprintf( 'order_items_wccf_%s', sanitize_key( $key ) )} ) )
						$order->{sprintf( 'order_items_wccf_%s', sanitize_key( $key ) )} = substr( $order->{sprintf( 'order_items_wccf_%s', sanitize_key( $key ) )}, 0, -1 );
				}
			}
			unset( $custom_fields, $custom_field, $key );
		}
	}

	// WooCommerce Product Custom Options Lite - https://wordpress.org/plugins/woocommerce-custom-options-lite/
	if( woo_ce_detect_export_plugin( 'wc_product_custom_options' ) ) {
		if( $custom_options = woo_ce_get_product_custom_options() ) {
			foreach( $custom_options as $custom_option ) {
				$order->{sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) )} = '';
			}
			foreach( $order->order_items as $order_item ) {
				foreach( $custom_options as $custom_option ) {
					if( isset( $order_item->{sprintf( 'pco_%s', sanitize_key( $custom_option ) )} ) )
						$order->{sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) )} = $order_item->{sprintf( 'pco_%s', sanitize_key( $custom_option ) )} . $export->category_separator;
				}
			}
			foreach( $custom_options as $custom_option ) {
				if( isset( $order->{sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) )} ) )
					$order->{sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) )} = substr( $order->{sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) )}, 0, -1 );
			}
		}
	}

	// Barcodes for WooCommerce - http://www.wolkenkraft.com/produkte/barcodes-fuer-woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_barcodes' ) ) {
		$order->order_items_barcode_type = '';
		$order->order_items_barcode = '';
		foreach( $order->order_items as $order_item ) {
			$order->order_items_barcode_type .= get_post_meta( $order_item->product_id, '_barcode_type', true ) . $export->category_separator;
			$order->order_items_barcode .= get_post_meta( $order_item->product_id, '_barcode', true ) . $export->category_separator;
		}
		if( isset( $order->order_items_barcode_type ) )
			$order->order_items_barcode_type = substr( $order->order_items_barcode_type, 0, -1 );
		if( isset( $order->order_items_barcode ) )
			$order->order_items_barcode = substr( $order->order_items_barcode, 0, -1 );
	}

	// Tax Rates
	$tax_rates = woo_ce_get_order_tax_rates();
	if( !empty( $tax_rates ) ) {
		foreach( $tax_rates as $tax_rate )
			$order->{sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] )} = '';
		foreach( $order->order_items as $order_item ) {
			foreach( $tax_rates as $tax_rate ) {
				if( isset( $order_item->{sprintf( 'tax_rate_%d', $tax_rate['rate_id'] )} ) )
					$order->{sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] )} = $order_item->{sprintf( 'tax_rate_%d', $tax_rate['rate_id'] )};
			}
		}
		foreach( $tax_rates as $tax_rate ) {
			if( isset( $order->{sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] )} ) )
				$order->{sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] )} = substr( $order->{sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] )}, 0, -1 );
		}
	}
	unset( $tax_rates, $tax_rate );

	// Attributes
	$attributes = woo_ce_get_product_attributes();
	if( $attributes && $order->order_items ) {
		foreach( $attributes as $attribute ) {
			$order->{sprintf( 'order_items_attribute_%s', sanitize_key( $attribute->attribute_name ) )} = '';
			$order->{sprintf( 'order_items_product_attribute_%s', sanitize_key( $attribute->attribute_name ) )} = '';
		}
		foreach( $order->order_items as $order_item ) {
			foreach( $attributes as $attribute ) {
				if( isset( $order_item->{sprintf( 'attribute_%s', sanitize_key( $attribute->attribute_name ) )} ) )
					$order->{sprintf( 'order_items_attribute_%s', sanitize_key( $attribute->attribute_name ) )} .= woo_ce_format_custom_meta( $order_item->{sprintf( 'attribute_%s', sanitize_key( $attribute->attribute_name ) )} ) . $export->category_separator;
				if( isset( $order_item->{sprintf( 'product_attribute_%s', sanitize_key( $attribute->attribute_name ) )} ) )
					$order->{sprintf( 'order_items_product_attribute_%s', sanitize_key( $attribute->attribute_name ) )} .= woo_ce_format_custom_meta( $order_item->{sprintf( 'product_attribute_%s', sanitize_key( $attribute->attribute_name ) )} ) . $export->category_separator;
			}
		}
		foreach( $attributes as $attribute ) {
			if( isset( $order->{sprintf( 'order_items_attribute_%s', sanitize_key( $attribute->attribute_name ) )} ) )
				$order->{sprintf( 'order_items_attribute_%s', sanitize_key( $attribute->attribute_name ) )} = substr( $order->{sprintf( 'order_items_attribute_%s', sanitize_key( $attribute->attribute_name ) )}, 0, -1 );
			if( isset( $order->{sprintf( 'order_items_product_attribute_%s', sanitize_key( $attribute->attribute_name ) )} ) )
				$order->{sprintf( 'order_items_product_attribute_%s', sanitize_key( $attribute->attribute_name ) )} = substr( $order->{sprintf( 'order_items_product_attribute_%s', sanitize_key( $attribute->attribute_name ) )}, 0, -1 );
		}
	}
	unset( $attributes, $attribute );

	// Custom Order Items fields
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	if( !empty( $custom_order_items ) && $order->order_items ) {
		foreach( $custom_order_items as $custom_order_item )
			$order->{sprintf( 'order_items_%s', $custom_order_item )} = '';
		foreach( $order->order_items as $order_item ) {
			foreach( $custom_order_items as $custom_order_item ) {
				if( !empty( $custom_order_item ) )
					$order->{sprintf( 'order_items_%s', $custom_order_item )} .= woo_ce_format_custom_meta( $order_item->{$custom_order_item} ) . $export->category_separator;
			}
		}
		foreach( $custom_order_items as $custom_order_item ) {
			if( isset( $order->{sprintf( 'order_items_%s', $custom_order_item )} ) )
				$order->{sprintf( 'order_items_%s', $custom_order_item )} = substr( $order->{sprintf( 'order_items_%s', $custom_order_item )}, 0, -1 );
		}
	}
	unset( $custom_order_items, $custom_order_item );

	// Custom Order Item Product fields
	$custom_order_products = woo_ce_get_option( 'custom_order_products', '' );
	if( !empty( $custom_order_products ) && $order->order_items ) {
		foreach( $custom_order_products as $custom_order_product )
			$order->{sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) )} = '';
		foreach( $order->order_items as $order_item ) {
			foreach( $custom_order_products as $custom_order_product ) {
				if( !empty( $custom_order_product ) )
					$order->{sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) )} .= woo_ce_format_custom_meta( $order_item->{sanitize_key( $custom_order_product )} ) . $export->category_separator;
			}
		}
		foreach( $custom_order_products as $custom_order_product ) {
			if( isset( $order->{sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) )} ) )
				$order->{sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) )} = substr( $order->{sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) )}, 0, -1 );
		}
	}
	unset( $custom_order_products, $custom_order_product );

	// Custom Product fields
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if( !empty( $custom_products ) ) {
		foreach( $custom_products as $custom_product )
			$order->{sprintf( 'order_items_%s', sanitize_key( $custom_product ) )} = '';
		foreach( $order->order_items as $order_item ) {
			foreach( $custom_products as $custom_product ) {
				if( !empty( $custom_product ) )
					$order->{sprintf( 'order_items_%s', sanitize_key( $custom_product ) )} .= $order_item->{sanitize_key( $custom_product )} . $export->category_separator;
			}
		}
		foreach( $custom_products as $custom_product ) {
			if( isset( $order->{sprintf( 'order_items_%s', sanitize_key( $custom_product ) )} ) )
				$order->{sprintf( 'order_items_%s', sanitize_key( $custom_product ) )} = substr( $order->{sprintf( 'order_items_%s', sanitize_key( $custom_product ) )}, 0, -1 );
		}
	}
	unset( $custom_products, $custom_product );

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	return $order;

}
add_filter( 'woo_ce_order_items_combined', 'woo_ce_extend_order_items_combined' );
?>