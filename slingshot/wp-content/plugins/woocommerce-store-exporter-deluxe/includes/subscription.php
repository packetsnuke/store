<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_subscription_count' ) ) {
		function woo_ce_get_export_type_subscription_count( $count = 0, $export_type = '', $args ) {

			if( $export_type <> 'subscription' )
				return $count;

			$count = 0;

			// Override for WordPress MultiSite
			if( woo_ce_is_network_admin() ) {
				$sites = wp_get_sites();
				foreach( $sites as $site ) {
					switch_to_blog( $site['blog_id'] );
					if( class_exists( 'WC_Subscriptions' ) ) {
						$count += woo_ce_get_subscription_count();
					}
					restore_current_blog();
				}
			}

			// Check that WooCommerce Subscriptions exists
			if( class_exists( 'WC_Subscriptions' ) ) {
				$count = woo_ce_get_subscription_count();
			}
			return $count;

		}
		add_filter( 'woo_ce_get_export_type_count', 'woo_ce_get_export_type_subscription_count', 10, 3 );
	}

	function woo_ce_get_subscription_count() {

		$count = 0;
		// Check if the existing Transient exists
		$cached = get_transient( WOO_CD_PREFIX . '_subscription_count' );
		if( $cached == false ) {

			// Allow store owners to force the Subscription count
			$count = apply_filters( 'woo_ce_get_subscription_count', $count );

			if( $count == 0 ) {
				$wcs_version = woo_ce_get_wc_subscriptions_version();
				if( version_compare( $wcs_version, '2.0.1', '<' ) ) {
					if( method_exists( 'WC_Subscriptions', 'is_large_site' ) ) {
						// Does this store have roughly more than 3000 Subscriptions
						if( false === WC_Subscriptions::is_large_site() ) {
							if( class_exists( 'WC_Subscriptions_Manager' ) ) {
								// Check that the get_all_users_subscriptions() function exists
								if( method_exists( 'WC_Subscriptions_Manager', 'get_all_users_subscriptions' ) ) {
									if( $subscriptions = WC_Subscriptions_Manager::get_all_users_subscriptions() ) {
										if( version_compare( $wcs_version, '2.0.1', '<' ) ) {
											foreach( $subscriptions as $key => $user_subscription ) {
												if( !empty( $user_subscription ) ) {
													foreach( $user_subscription as $subscription )
														$count++;
												}
											}
											unset( $subscriptions, $subscription, $user_subscription );
										}
									}
								}
							}
						} else {
							if( method_exists( 'WC_Subscriptions', 'get_total_subscription_count' ) )
								$count = WC_Subscriptions::get_total_subscription_count();
							else
								$count = "~2500";
						}
					} else {
						if( method_exists( 'WC_Subscriptions', 'get_subscription_count' ) )
							$count = WC_Subscriptions::get_subscription_count();
					}
				} else {
					if( function_exists( 'wcs_get_subscriptions' ) ) {
						$args = array(
							'subscriptions_per_page' => -1,
							'subscription_status' => 'trash'
						);
						$count += count( wcs_get_subscriptions( $args ) );
						$args['subscription_status'] = 'any';
						$count += count( wcs_get_subscriptions( $args ) );
					}
				}
			}
			set_transient( WOO_CD_PREFIX . '_subscription_count', $count, HOUR_IN_SECONDS );
		} else {
			$count = $cached;
		}
		return $count;

	}

	function woo_ce_subscription_scheduled_export_save( $post_ID = 0 ) {

		update_post_meta( $post_ID, '_filter_subscription_orderby', ( isset( $_POST['subscription_filter_orderby'] ) ? sanitize_text_field( $_POST['subscription_filter_orderby'] ) : false ) );
		// Select2 passes us a string whereas Chosen gives us an array
		$auto_subscription_sku = ( isset( $_POST['subscription_filter_sku'] ) ? $_POST['subscription_filter_sku'] : false );
		if( is_array( $auto_subscription_sku ) && count( $auto_subscription_sku ) == 1 )
			$auto_subscription_sku = explode( ',', $auto_subscription_sku[0] );
		update_post_meta( $post_ID, '_filter_subscription_sku', ( !empty( $auto_subscription_sku ) ? woo_ce_format_product_filters( array_map( 'absint', $auto_subscription_sku ) ) : false ) );

	}
	add_action( 'woo_ce_extend_scheduled_export_save', 'woo_ce_subscription_scheduled_export_save' );

	function woo_ce_subscription_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Subscription Export Type
		if( $export_type <> 'subscription' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'subscription_status' => ( isset( $_POST['subscription_filter_status'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', $_POST['subscription_filter_status'] ) ) : false ),
			'subscription_product' => ( isset( $_POST['subscription_filter_product'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['subscription_filter_product'] ) ) : false ),
			'subscription_source' => ( isset( $_POST['subscription_filter_source'] ) ? sanitize_text_field( $_POST['subscription_filter_source'] ) : false ),
			'subscription_orderby' => ( isset( $_POST['subscription_orderby'] ) ? sanitize_text_field( $_POST['subscription_orderby'] ) : false ),
			'subscription_order' => ( isset( $_POST['subscription_order'] ) ? sanitize_text_field( $_POST['subscription_order'] ) : false )
		);
		$args = wp_parse_args( $args, $defaults );

		// Save dataset export specific options
		// Status
		// Product
		// Source
		if( $args['subscription_orderby'] <> woo_ce_get_option( 'subscription_orderby' ) )
			woo_ce_update_option( 'subscription_orderby', $args['subscription_orderby'] );
		if( $args['subscription_order'] <> woo_ce_get_option( 'subscription_order' ) )
			woo_ce_update_option( 'subscription_order', $args['subscription_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_subscription_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

function woo_ce_cron_subscription_dataset_args( $args, $export_type = '', $is_scheduled = 0 ) {

	// Check if we're dealing with the Subscription Export Type
	if( $export_type <> 'subscription' )
		return $args;

	$subscription_orderby = false;
	$subscription_filter_sku = false;

	if( $is_scheduled ) {
		$scheduled_export = ( $is_scheduled ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );

		$subscription_orderby = get_post_meta( $scheduled_export, '_filter_subscription_orderby', true );
		$subscription_filter_sku = get_post_meta( $scheduled_export, '_filter_subscription_sku', true );
	}

	// Merge in the form data for this dataset
	$defaults = array(
		'subscription_orderby' => ( !empty( $subscription_orderby ) ? $subscription_orderby : false ),
		'subscription_product' => ( !empty( $subscription_filter_sku ) ? (array)$subscription_filter_sku : array() )
	);

	$args = wp_parse_args( $args, $defaults );

	return $args;

}
add_filter( 'woo_ce_extend_cron_dataset_args', 'woo_ce_cron_subscription_dataset_args', 10, 3 );

// Returns a list of Subscription export columns
function woo_ce_get_subscription_fields( $format = 'full' ) {

	$export_type = 'subscription';

	$fields = array();
	$fields[] = array(
		'name' => 'subscription_id',
		'label' => __( 'Subscription ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_id',
		'label' => __( 'Order ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'status',
		'label' => __( 'Subscription Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'recurring',
		'label' => __( 'Recurring', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user',
		'label' => __( 'User', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_id',
		'label' => __( 'User ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_status',
		'label' => __( 'Order Status', 'woocommerce-exporter' )
	);
	// Check if this is a pre-WooCommerce 2.2 instance
	$woocommerce_version = woo_get_woo_version();
	if( version_compare( $woocommerce_version, '2.2', '<' ) ) {
		$fields[] = array(
			'name' => 'post_status',
			'label' => __( 'Post Status', 'woocommerce-exporter' )
		);
	}
	$fields[] = array(
		'name' => 'transaction_id',
		'label' => __( 'Transaction ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'start_date',
		'label' => __( 'Start Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'end_date',
		'label' => __( 'End Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'trial_end_date',
		'label' => __( 'Trial End Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'last_payment',
		'label' => __( 'Last Payment', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'next_payment',
		'label' => __( 'Next Payment', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'payment_method',
		'label' => __( 'Payment Method', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_total',
		'label' => __( 'Order Total', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_subtotal',
		'label' => __( 'Order Subtotal', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'recurring_total',
		'label' => __( 'Recurring Total', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_method_id',
		'label' => __( 'Shipping Method ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_method',
		'label' => __( 'Shipping Method', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_cost',
		'label' => __( 'Shipping Cost', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'sign_up_fee',
		'label' => __( 'Sign-up Fee', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'trial_length',
		'label' => __( 'Trial Length', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'trial_period',
		'label' => __( 'Trial Period', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'coupon',
		'label' => __( 'Coupon Code', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'related_orders',
		'label' => __( 'Related Orders', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_full_name',
		'label' => __( 'Billing: Full Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_first_name',
		'label' => __( 'Billing: First Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_last_name',
		'label' => __( 'Billing: Last Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_company',
		'label' => __( 'Billing: Company', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_address',
		'label' => __( 'Billing: Street Address (Full)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_address_1',
		'label' => __( 'Billing: Street Address 1', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_address_2',
		'label' => __( 'Billing: Street Address 2', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_city',
		'label' => __( 'Billing: City', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_postcode',
		'label' => __( 'Billing: ZIP Code', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_state',
		'label' => __( 'Billing: State (prefix)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_state_full',
		'label' => __( 'Billing: State', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_country',
		'label' => __( 'Billing: Country (prefix)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_country_full',
		'label' => __( 'Billing: Country', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_phone',
		'label' => __( 'Billing: Phone Number', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_email',
		'label' => __( 'Billing: E-mail Address', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_full_name',
		'label' => __( 'Shipping: Full Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_first_name',
		'label' => __( 'Shipping: First Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_last_name',
		'label' => __( 'Shipping: Last Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_company',
		'label' => __( 'Shipping: Company', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_address',
		'label' => __( 'Shipping: Street Address (Full)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_address_1',
		'label' => __( 'Shipping: Street Address 1', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_address_2',
		'label' => __( 'Shipping: Street Address 2', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_city',
		'label' => __( 'Shipping: City', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_postcode',
		'label' => __( 'Shipping: ZIP Code', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_state',
		'label' => __( 'Shipping: State (prefix)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_state_full',
		'label' => __( 'Shipping: State', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_country',
		'label' => __( 'Shipping: Country (prefix)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_country_full',
		'label' => __( 'Shipping: Country', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_product_id',
		'label' => __( 'Subscription Items: Product ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_variation_id',
		'label' => __( 'Subscription Items: Variation ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_sku',
		'label' => __( 'Subscription Items: Product SKU', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_name',
		'label' => __( 'Subscription Items: Product Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_variation',
		'label' => __( 'Subscription Items: Product Variation', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_quantity',
		'label' => __( 'Subscription Items: Quantity', 'woocommerce-exporter' )
	);

/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'woocommerce-exporter' )
	);
*/

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	// Allow Plugin/Theme authors to add support for additional columns
	$fields = apply_filters( sprintf( WOO_CD_PREFIX . '_%s_fields', $export_type ), $fields, $export_type );

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	// Check if we're dealing with an Export Template
	$sorting = false;
	if( !empty( $post_ID ) ) {
		$remember = get_post_meta( $post_ID, sprintf( '_%s_fields', $export_type ), true );
		$hidden = get_post_meta( $post_ID, sprintf( '_%s_hidden', $export_type ), false );
		$sorting = get_post_meta( $post_ID, sprintf( '_%s_sorting', $export_type ), true );
	} else {
		$remember = woo_ce_get_option( $export_type . '_fields', array() );
		$hidden = woo_ce_get_option( $export_type . '_hidden', array() );
	}
	if( !empty( $remember ) ) {
		$remember = maybe_unserialize( $remember );
		$hidden = maybe_unserialize( $hidden );
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			$fields[$i]['disabled'] = ( isset( $fields[$i]['disabled'] ) ? $fields[$i]['disabled'] : 0 );
			$fields[$i]['hidden'] = ( isset( $fields[$i]['hidden'] ) ? $fields[$i]['hidden'] : 0 );
			$fields[$i]['default'] = 1;
			if( isset( $fields[$i]['name'] ) ) {
				// If not found turn off default
				if( !array_key_exists( $fields[$i]['name'], $remember ) )
					$fields[$i]['default'] = 0;
				// Remove the field from exports if found
				if( array_key_exists( $fields[$i]['name'], $hidden ) )
					$fields[$i]['hidden'] = 1;
			}
		}
	}

	switch( $format ) {

		case 'summary':
			$output = array();
			$size = count( $fields );
			for( $i = 0; $i < $size; $i++ ) {
				if( isset( $fields[$i] ) )
					$output[$fields[$i]['name']] = 'on';
			}
			return $output;
			break;

		case 'full':
		default:
			// Load the default sorting
			if( empty( $sorting ) )
				$sorting = woo_ce_get_option( sprintf( '%s_sorting', $export_type ), array() );
			$size = count( $fields );
			for( $i = 0; $i < $size; $i++ ) {
				if( !isset( $fields[$i]['name'] ) ) {
					unset( $fields[$i] );
					continue;
				}
				$fields[$i]['reset'] = $i;
				$fields[$i]['order'] = ( isset( $sorting[$fields[$i]['name']] ) ? $sorting[$fields[$i]['name']] : $i );
			}
			// Check if we are using PHP 5.3 and above
			if( version_compare( phpversion(), '5.3' ) >= 0 )
				usort( $fields, woo_ce_sort_fields( 'order' ) );
			return $fields;
			break;

	}

}

// Check if we should override field labels from the Field Editor
function woo_ce_override_subscription_field_labels( $fields = array() ) {

	$export_type = 'subscription';

	$labels = false;

	// Check if this is a Scheduled Export
	$scheduled_export = absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) );
	if( $scheduled_export ) {
		$export_fields = get_post_meta( $scheduled_export, '_export_fields', true );
		if( $export_fields == 'template' ) {
			$export_template = get_post_meta( $scheduled_export, '_export_template', true );
			if( !empty( $export_template ) )
				$labels = get_post_meta( $export_template, sprintf( '_%s_labels', $export_type ), true );
		}
	}

	// Default to Quick Export labels
	if( empty( $labels ) )
		$labels = woo_ce_get_option( sprintf( '%s_labels', $export_type ), array() );

	if( !empty( $labels ) ) {
		foreach( $fields as $key => $field ) {
			if( isset( $labels[$field['name']] ) )
				$fields[$key]['label'] = $labels[$field['name']];
		}
	}
	return $fields;

}
add_filter( 'woo_ce_subscription_fields', 'woo_ce_override_subscription_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_subscription_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_subscription_fields();
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			if( $fields[$i]['name'] == $name ) {
				switch( $format ) {

					case 'name':
						$output = $fields[$i]['label'];
						break;

					case 'full':
						$output = $fields[$i];
						break;

				}
				$i = $size;
			}
		}
	}
	return $output;

}

// Returns a list of Subscription IDs
function woo_ce_get_subscriptions( $args = array() ) {

	global $export;

	$limit_volume = -1;
	$offset = 0;
	$subscription_status = false;
	$subscription_product = false;
	$orderby = 'start_date';
	$order = 'DESC';
	if( $args ) {
		$limit_volume = ( isset( $args['limit_volume'] ) ? $args['limit_volume'] : -1 );
		$offset = $args['offset'];
		$orderby = ( isset( $args['subscription_orderby'] ) ? $args['subscription_orderby'] : 'start_date' );
		$order = ( isset( $args['subscription_order'] ) ? $args['subscription_order'] : 'DESC' );
		$subscription_status = ( isset( $args['subscription_status'] ) ? $args['subscription_status'] : array() );
		$subscription_product = ( isset( $args['subscription_product'] ) ? $args['subscription_product'] : array() );
		$user_ids = ( isset( $args['subscription_customer'] ) ? $args['subscription_customer'] : false );
		$source = ( isset( $args['subscription_source'] ) ? $args['subscription_source'] : false );
	}

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

	$output = array();

	// Check that WooCommerce Subscriptions exists
	if( !class_exists( 'WC_Subscriptions' ) || !class_exists( 'WC_Subscriptions_Manager' ) ) {
		$message = __( 'The WooCommerce Subscriptions class <code>WC_Subscriptions</code> or <code>WC_Subscriptions_Manager</code> could not be found, this is required to export Subscriptions.', 'woocommerce-exporter' ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Warning: %s', $message . ': ' . ( time() - $export->start_time ) ) );
		return;
	} else {
		// Check that the get_all_users_subscriptions() function exists
		if( !method_exists( 'WC_Subscriptions_Manager', 'get_all_users_subscriptions' ) ) {
			$message = __( 'The WooCommerce Subscriptions method <code>WC_Subscriptions_Manager->get_all_users_subscriptions()</code> could not be found, this is required to export Subscriptions.', 'woocommerce-exporter' ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
			if( WOO_CD_LOGGING )
				woo_ce_error_log( sprintf( 'Warning: %s', $message . ': ' . ( time() - $export->start_time ) ) );
			return;
		}
	}

	if( class_exists( 'WC_Subscriptions' ) ) {
		if( function_exists( 'wcs_get_subscriptions' ) ) {

			$args = array(
				'subscriptions_per_page' => $limit_volume,
				'offset' => $offset,
				'orderby' => $orderby,
				'order' => $order,
				'fields' => 'ids'
			);

			// Filter Subscriptions by Subscription Status
			if( $subscription_status ) {
				if( count( $subscription_status ) == 1 )
					$args['subscription_status'] = $subscription_status[0];
				else
					$args['subscription_status'] = $subscription_status;
			}
			// Filter Subscriptions by Customer
			if( !empty( $user_ids ) ) {
				// Check if we're dealing with a string or list of users
				if( is_string( $user_ids ) )
					$user_ids = explode( ',', $user_ids );
			}

			// Allow other developers to bake in their own filters
			$args = apply_filters( 'woo_ce_get_subscriptions_args', $args );

			if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
				woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_subscriptions( $args ): ' . ( time() - $export->start_time ) ) );
				woo_ce_error_log( sprintf( 'Debug: %s', '$args: ' . print_r( $args, true ) ) );
			}

			// Let's add some special sauce to override wcs_get_subscriptions() and only return the Post IDs
			add_filter( 'woocommerce_get_subscriptions_query_args', 'woo_ce_woocommerce_get_subscriptions_query_args' );
			add_filter( 'woocommerce_got_subscriptions', 'woo_ce_woocommerce_got_subscriptions' );
			$subscription_ids = wcs_get_subscriptions( $args );

			if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
				woo_ce_error_log( sprintf( 'Debug: %s', 'subscription_ids( $args ): ' . ( time() - $export->start_time ) ) );
				woo_ce_error_log( sprintf( 'Debug: %s', '$subscription_ids: ' . print_r( $subscription_ids, true ) ) );
			}

			$args = get_transient( WOO_CD_PREFIX . '_subscription_wcs_get_subscriptions' );
			if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
				woo_ce_error_log( sprintf( 'Debug: %s', '$args (loaded from a Transient): ' . ( time() - $export->start_time ) ) );
				woo_ce_error_log( sprintf( 'Debug: %s', '$args: ' . print_r( $args, true ) ) );
			}

			if( !empty( $args ) ) {
				$subscription_ids = array();
				if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
					woo_ce_error_log( sprintf( 'Debug: %s', 'before WP_Query( $args ): ' . ( time() - $export->start_time ) ) );
					woo_ce_error_log( sprintf( 'Debug: %s', '$args: ' . print_r( $args, true ) ) );
				}
				$subscriptions = new WP_Query( $args );
				if( $subscriptions->posts ) {
					if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
						woo_ce_error_log( sprintf( 'Debug: %s', 'after WP_Query( $args ): ' . ( time() - $export->start_time ) ) );
						woo_ce_error_log( sprintf( 'Debug: %s', '$subscriptions->posts: ' . print_r( $subscriptions->posts, true ) ) );
					}
					foreach( $subscriptions->posts as $subscription_id )
						$subscription_ids[] = $subscription_id;
				} else {
					if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
						$message = 'No Posts were returned by WP_Query: ' . ( time() - $export->start_time );
						woo_ce_error_log( sprintf( 'Debug: %s', $message ) );
					}
				}
				unset( $subscriptions );
			} else {
				if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
					$message = 'No $args detected, skipping WP_Query for Subscriptions: ' . ( time() - $export->start_time );
					woo_ce_error_log( sprintf( 'Debug: %s', $message ) );
				}
			}
			remove_filter( 'woocommerce_got_subscriptions', 'woo_ce_woocommerce_got_subscriptions' );
			remove_filter( 'woocommerce_get_subscriptions_query_args', 'woo_ce_woocommerce_get_subscriptions_query_args' );
			$subscriptions = array();

			if( !empty( $subscription_ids ) ) {
				foreach( $subscription_ids as $subscription_id ) {
					// Filter Subscriptions by Subscription Product
					if( $subscription_product ) {
						$order_id = wp_get_post_parent_id( $subscription_id );
						if( !empty( $order_id ) ) {
							$order_ids = woo_ce_get_product_assoc_order_ids( $subscription_product );
							if( in_array( $order_id, $order_ids ) == false ) {
								unset( $subscription_id );
							}
							unset( $order_ids );
						}
						unset( $order_id );
					}
					// Filter Subscriptions by Customer
					if( !empty( $user_ids ) ) {
						$user_id = get_post_meta( $subscription_id, '_customer_user', true );
						if( !in_array( $user_id, $user_ids ) ) {
							unset( $subscription_id );
						}
					}
					// Filter Subscriptions by Source
					if( !empty( $source ) ) {
						$order_id = wp_get_post_parent_id( $subscription_id );
						switch( $source ) {

							case 'customer':
								if( empty( $order_id ) )
									unset( $subscription_id );
								break;

							case 'manual':
								if( !empty( $order_id ) )
									unset( $subscription_id );
								break;

						}
						unset( $order_id );
					}

					if( isset( $subscription_id ) )
						$subscriptions[] = $subscription_id;

				}
				unset( $subscription_ids, $subscription_id );
			}

		} else {
			if( WOO_CD_LOGGING ) {
				$message = 'The function wcs_get_subscriptions() does not exist: ' . ( time() - $export->start_time );
				woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
			}
		}
	} else {
		if( WOO_CD_LOGGING ) {
			$message = 'The Class WC_Subscriptions does not exist: ' . ( time() - $export->start_time );
			woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
		}
	}
	return $subscriptions;

}

function woo_ce_woocommerce_get_subscriptions_query_args( $args ) {

	set_transient( WOO_CD_PREFIX . '_subscription_wcs_get_subscriptions', $args, HOUR_IN_SECONDS );
	return false;

}

// Override wcs_get_subscriptions() to only return the Subscription Post ID
function woo_ce_woocommerce_got_subscriptions( $subscriptions ) {

	if( !empty( $subscriptions ) ) {
		$subscriptions = array_keys( $subscriptions );
	}
	return $subscriptions;

}

function woo_ce_export_dataset_override_subscription( $output = null, $export_type = null ) {

	global $export;

	if( WOO_CD_LOGGING )
		woo_ce_error_log( sprintf( 'Debug: %s', 'before woo_ce_export_dataset_override_subscription(): ' . ( time() - $export->start_time ) ) );

	if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
		woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_subscriptions( $export->args ): ' . ( time() - $export->start_time ) ) );
		woo_ce_error_log( sprintf( 'Debug: %s', '$export->args: ' . print_r( $export->args, true ) ) );
	}

	if( $subscriptions = woo_ce_get_subscriptions( $export->args ) ) {

		$export->total_rows = count( $subscriptions );

		if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) )
			woo_ce_error_log( sprintf( 'Debug: %s', sprintf( '%d subscriptions detected: ' . ( time() - $export->start_time ), $export->total_rows ) ) );

		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $subscriptions as $subscription ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_subscription_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					$subscription = woo_ce_get_subscription_data( $subscription, $export->args, array_keys( $export->fields ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $subscription->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $subscription->$field ) )
									$child->addChild( apply_filters( 'woo_ce_export_xml_subscription_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $subscription->$field ) ) );
								else
									$child->addChild( apply_filters( 'woo_ce_export_xml_subscription_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $subscription->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			foreach( $subscriptions as $key => $subscription ) {

				if( WOO_CD_LOGGING )
					woo_ce_error_log( sprintf( 'Debug: %s', 'before woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );

				$subscriptions[$key] = woo_ce_get_subscription_data( $subscription, $export->args, array_keys( $export->fields ) );

				if( WOO_CD_LOGGING )
					woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_get_subscription_data(): ' . ( time() - $export->start_time ) ) );

			}
			$output = $subscriptions;
		}
		unset( $subscriptions, $subscription );
	} else {

		if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_subscriptions', false ) ) {
			$message = 'No subscriptions to export returned by woo_ce_get_subscriptions(): ' . ( time() - $export->start_time );
			woo_ce_error_log( sprintf( 'Debug: %s', $message ) );
		}

	}

	if( WOO_CD_LOGGING )
		woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_export_dataset_override_subscription(): ' . ( time() - $export->start_time ) ) );

	return $output;

}

function woo_ce_export_dataset_multisite_override_subscription( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			if( $subscriptions = woo_ce_get_subscriptions( $export->args ) ) {
				$export->total_rows = count( $subscriptions );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $subscriptions as $subscription ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_subscription_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							$subscription = woo_ce_get_subscription_data( $subscription, $export->args, array_keys( $export->fields ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $subscription->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $subscription->$field ) )
											$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $subscription->$field ) ) );
										else
											$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $subscription->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					foreach( $subscriptions as $key => $subscription )
						$subscriptions[$key] = woo_ce_get_subscription_data( $subscription, $export->args, array_keys( $export->fields ) );
					if( is_null( $output ) )
						$output = $subscriptions;
					else
						$output = array_merge( $output, $subscriptions );
				}
				unset( $subscriptions, $subscription );
			}
			restore_current_blog();
		}
	}
	return $output;

}

function woo_ce_get_subscription_data( $subscription_id, $args = array(), $fields = array() ) {

	$subscription_statuses = woo_ce_get_subscription_statuses();

	$subscription = get_post( $subscription_id );

	// Check we are only dealing with shop_subscription
	$post_type = 'shop_subscription';
	if( $subscription->post_type <> $post_type )
		return;

	$wcs_subscription = ( function_exists( 'wcs_get_subscription' ) ? wcs_get_subscription( $subscription_id ) : false );
	$order_status = false;
	if( !empty( $wcs_subscription ) ) {
		// Check if an Order has been assigned to this Subscription
		if( !empty( $wcs_subscription->order ) )
			$order_status = $wcs_subscription->order->post_status;
	} else {
		if( !empty( $subscription->post_parent ) ) {
			$order = get_post( $subscription->post_parent );
			if( !empty( $order ) )
				$order_status = $order->post_status;
			unset( $order );
		}
	}

	$subscription->order_id = ( !empty( $subscription->post_parent ) ? $subscription->post_parent : 0 );
	$subscription->subscription_id = $subscription_id;
	if( function_exists( 'wcs_get_subscription_status_name' ) ) {
		if( method_exists( $wcs_subscription, 'get_status' ) ) {
			$subscription->status = wcs_get_subscription_status_name( $wcs_subscription->get_status() );
		} else {
			if( WOO_CD_LOGGING ) {
				$message = 'The method $wcs_subscription->get_status() does not exist, defaulting to Post Status';
				woo_ce_error_log( sprintf( 'Warning: %s', $message . ': ' . ( time() - $export->start_time ) ) );
			}
			$subscription->status = ( isset( $subscription_statuses[$subscription->post_status] ) ? $subscription_statuses[$subscription->post_status] : false );
		}
	} else {
		if( WOO_CD_LOGGING ) {
			$message = 'The function wcs_get_subscription_status_name() does not exist, defaulting to Post Status';
			woo_ce_error_log( sprintf( 'Warning: %s', $message . ': ' . ( time() - $export->start_time ) ) );
		}
		$subscription->status = ( isset( $subscription_statuses[$subscription->post_status] ) ? $subscription_statuses[$subscription->post_status] : false );
	}
	$subscription->status = woo_ce_format_subscription_status( $subscription->status );
	$subscription->user_id = get_post_meta( $subscription_id, '_customer_user', true );
	$subscription->user = woo_ce_get_username( $subscription->user_id );
	$subscription->order_status = ( !empty( $order_status ) ? woo_ce_format_order_status( $order_status ) : '-' );
	$subscription->transaction_id = ( !empty( $subscription->order_id ) ? get_post_meta( $subscription->order_id, '_transaction_id', true ) : false );
	$subscription->coupon = woo_ce_get_order_assoc_coupon( $subscription->order_id );
	$subscription->payment_method = ( method_exists( $wcs_subscription, 'get_payment_method_to_display' ) ? $wcs_subscription->get_payment_method_to_display() : false );
	if( function_exists( 'wcs_get_subscription_period_interval_strings' ) && function_exists( 'wcs_get_subscription_period_strings' ) )
		$subscription->recurring = sprintf( '%s %s', wcs_get_subscription_period_interval_strings( $wcs_subscription->billing_interval ), wcs_get_subscription_period_strings( 1, $wcs_subscription->billing_period ) );
	if( method_exists( $wcs_subscription, 'get_time' ) ) {
		$subscription->start_date = ( ( 0 < $wcs_subscription->get_time( 'start' ) ) ? woo_ce_format_date( $wcs_subscription->get_date( 'start' ) ) : '-' );
		$subscription->end_date = ( ( 0 < $wcs_subscription->get_time( 'end' ) ) ? woo_ce_format_date( $wcs_subscription->get_date( 'end', 'site' ) ) : '-' );
		$subscription->trial_end_date = ( ( 0 < $wcs_subscription->get_time( 'trial_end' ) ) ? woo_ce_format_date( $wcs_subscription->get_date( 'trial_end' ) ) : '-' );
		$subscription->next_payment = ( ( 0 < $wcs_subscription->get_time( 'next_payment' ) ) ? woo_ce_format_date( $wcs_subscription->get_date( 'next_payment' ) ) : '-' );
		$subscription->last_payment = ( ( 0 < $wcs_subscription->get_time( 'last_payment' ) ) ? woo_ce_format_date( $wcs_subscription->get_date( 'last_payment' ) ) : '-' );
	}
	$subscription->related_orders = ( method_exists( $wcs_subscription, 'get_related_orders' ) ? count( $wcs_subscription->get_related_orders() ) : 0 );

	if( method_exists( $wcs_subscription, 'get_formatted_order_total' ) ) {
		add_filter( 'wc_price', 'woo_ce_filter_wc_price', 10, 3 );
		add_filter( 'formatted_woocommerce_price', 'woo_ce_formatted_woocommerce_price', 10, 5 );
		add_filter( 'woocommerce_currency_symbol', 'woo_ce_woocommerce_currency_symbol', 10, 2 );
		$subscription->recurring_total =  $wcs_subscription->get_formatted_order_total();
		$subscription->recurring_total = str_replace( array( '<span class="amount">', '</span>' ), '', $subscription->recurring_total );
		remove_filter( 'formatted_woocommerce_price', 'woo_ce_formatted_woocommerce_price' );
		remove_filter( 'wc_price', 'woo_ce_filter_wc_price' );
		remove_filter( 'woocommerce_currency_symbol', 'woo_ce_woocommerce_currency_symbol' );
	}

	// Merge in our Order details
	$order_args = array(
		'order_items' => 'combined',
		'order_items_types' => woo_ce_get_option( 'order_items_types', array() )
	);
	$order = woo_ce_get_order_data( $subscription_id, 'order', $order_args, false );
	if( !empty( $order ) )
		$subscription = (object)array_merge( (array) $subscription, (array)$order );

/*
	$order = woo_ce_get_order_wc_data( $subscription['order_id'] );
	$order_item = woo_ce_get_subscription_order_item( $subscription['order_id'], $subscription['product_id'] );
	$product = woo_ce_get_subscription_product( $order, $order_item );
	$subscription['key'] = woo_ce_get_subscription_key( $subscription['order_id'], $subscription['product_id'] );
	$subscription['name'] = $order_item['name'];
	if( isset( $product->variation_data ) )
		$subscription['name'] = ( function_exists( 'woocommerce_get_formatted_variation' ) ? woocommerce_get_formatted_variation( $product->variation_data, true ) : $subscription['name'] );
	$subscription['variation_id'] = ( !empty( $order_item['variation_id'] ) ? $order_item['variation_id'] : '' );
	$subscription['quantity'] = ( !empty( $order_item['qty'] ) ? $order_item['qty'] : '' );
	$subscription['recurring'] = ( !empty( $subscription['interval'] ) ? sprintf( '%s %s', woo_ce_format_product_subscription_period_interval( $subscription['interval'] ), $subscription['period'] ) : '' );
	$subscription['order_status'] = woo_ce_format_order_status( $order->status );
	$subscription['post_status'] = ucwords( $order->post_status );
	$user = woo_ce_get_user_data( $subscription['user_id'] );
	$subscription['email'] = ( isset( $user->email ) ? $user->email : '' );
	unset( $user );
	$subscription['status'] = ( isset( $subscription_statuses[$subscription['status']] ) ? $subscription_statuses[$subscription['status']] : $subscription['status'] );
	$subscription['start_date'] = ( isset( $order_item['subscription_start_date'] ) ? date_i18n( woocommerce_date_format(), strtotime( $order_item['subscription_start_date'] ) ) : '' );
	$subscription['expiration'] = ( !empty( $subscription['expiry_date'] ) ? woo_ce_format_subscription_date( $subscription['expiry_date'] ) : __( 'Never', 'woocommerce-subscriptions' ) );
	$subscription['end_date'] = ( !empty( $order_item['subscription_expiry_date'] ) ? date_i18n( woocommerce_date_format(), strtotime( $order_item['subscription_expiry_date'] ) ) : __( 'Not yet ended', 'woocommerce-subscriptions' ) );
	$subscription['trial_end_date'] = ( !empty( $order_item['subscription_trial_expiry_date'] ) ? date_i18n( woocommerce_date_format(), strtotime( $order_item['subscription_trial_expiry_date'] ) ) : '-' );
	$subscription['last_payment'] = ( !empty( $subscription['last_payment_date'] ) ? woo_ce_format_subscription_date( $subscription['last_payment_date'] ) : '-' );
	$subscription['next_payment'] = woo_ce_get_subscription_next_payment( $subscription['key'], $subscription['user_id'] );
	$subscription['renewals'] = woo_ce_get_subscription_renewals( $subscription['order_id'] );
	if( method_exists( $product, 'get_sku' ) )
		$subscription['product_sku'] = $product->get_sku();
	$subscription['sign_up_fee'] = get_post_meta( $subscription['product_id'], '_subscription_sign_up_fee', true );
	$subscription['trial_length'] = get_post_meta( $subscription['product_id'], '_subscription_trial_length', true );
	$subscription['trial_period'] = get_post_meta( $subscription['product_id'], '_subscription_trial_period', true );
*/

	// Allow Plugin/Theme authors to add support for additional Product columns
	$subscription = apply_filters( 'woo_ce_subscription', $subscription, $subscription_id );

	// Trim back the Subscription just to requested export fields
	if( !empty( $fields ) ) {
		$fields = array_merge( $fields, array( 'id', 'ID', 'post_parent', 'filter' ) );
		if( !empty( $subscription ) ) {
			foreach( $subscription as $key => $data ) {
				if( !in_array( $key, $fields ) )
					unset( $subscription->$key );
			}
		}
	}

	return $subscription;

}

function woo_ce_get_subscription_statuses() {

	if( function_exists( 'wcs_get_subscription_statuses' ) ) {
		$subscription_statuses = wcs_get_subscription_statuses();
	} else {
		$subscription_statuses = array(
			'active'    => __( 'Active', 'woocommerce-subscriptions' ),
			'cancelled' => __( 'Cancelled', 'woocommerce-subscriptions' ),
			'suspended' => __( 'Suspended', 'woocommerce-subscriptions' ),
			'expired'   => __( 'Expired', 'woocommerce-subscriptions' ),
			'pending'   => __( 'Pending', 'woocommerce-subscriptions' ),
			'failed'    => __( 'Failed', 'woocommerce-subscriptions' ),
			'on-hold'   => __( 'On-hold', 'woocommerce-subscriptions' ),
			'trash'     => __( 'Deleted', 'woocommerce-exporter' ),
		);
	}
	return apply_filters( 'woo_ce_subscription_statuses', $subscription_statuses );

}

function woo_ce_get_wc_subscriptions_version() {

	if( class_exists( 'WC_Subscriptions' ) ) {
		return WC_Subscriptions::$version;
	}

}

function woo_ce_get_subscription_order_item( $order_id = 0, $product_id = 0 ) {

	if( method_exists( 'WC_Subscriptions_Order', 'get_item_by_product_id' ) )
		$order_item = WC_Subscriptions_Order::get_item_by_product_id( $order_id, $product_id );
	return $order_item;

}

function woo_ce_get_subscription_product( $order = false, $order_item = false ) {

	// Check that get_product_from_item() exists within the WC_Order class
	if( method_exists( 'WC_Order', 'get_product_from_item' ) ) {
		// Check that $order and $order_item aren't empty
		if( !empty( $order ) && !empty( $order_item ) )
			$product = $order->get_product_from_item( $order_item );
	}
	return $product;

}

function woo_ce_format_subscription_date( $end_date = '' ) {

	// Date formatting is provided by WooCommerce Subscriptions
	$current_gmt_time = gmdate( 'U' );
	$end_date_timestamp = strtotime( $end_date );
	$time_diff = $current_gmt_time - $end_date_timestamp;
	if ( $time_diff > 0 && $time_diff < 7 * 24 * 60 * 60 )
		$end_date = sprintf( __( '%s ago', 'woocommerce-subscriptions' ), human_time_diff( $end_date_timestamp, $current_gmt_time ) );
	else
		$end_date = date_i18n( woocommerce_date_format(), $end_date_timestamp + get_option( 'gmt_offset' ) * 3600 );
	return $end_date;

}

function woo_ce_get_subscription_products() {

	$term_taxonomy = 'product_type';
	$args = array(
		'post_type' => array( 'product', 'product_variation' ),
		'posts_per_page' => -1,
		'fields' => 'ids',
		'suppress_filters' => false,
		'tax_query' => array(
			array(
				'taxonomy' => $term_taxonomy,
				'field' => 'slug',
				'terms' => array( 'subscription', 'variable-subscription' )
			)
		)
	);
	$products = array();
	$product_ids = new WP_Query( $args );
	if( $product_ids->posts ) {
		foreach( $product_ids->posts as $product_id )
			$products[] = $product_id;
	}
	return $products;

}

function woo_ce_format_subscription_status( $subscription_status = '' ) {
	
	$output = $subscription_status;
	switch( $subscription_status ) {

		case 'active':
			$output = __( 'Active', 'woocommerce-exporter' );
			break;

		case 'switched':
			$output = __( 'Switched', 'woocommerce-exporter' );
			break;

		case 'on-hold':
			$output = __( 'On hold', 'woocommerce-exporter' );
			break;

		case 'pending':
			$output = __( 'Pending Payment', 'woocommerce-exporter' );
			break;

		case 'pending-cancel':
			$output = __( 'Pending Cancellation', 'woocommerce-exporter' );
			break;

		case 'cancelled':
			$output = __( 'Cancelled', 'woocommerce-exporter' );
			break;

		case 'expired':
			$output = __( 'Expired', 'woocommerce-exporter' );
			break;

		case 'trash':
			$output = __( 'Trash', 'woocommerce-exporter' );
			break;

	}
	$output = apply_filters( 'woo_ce_format_subscription_status', $output, $subscription_status );
	return $output;

}

function woo_ce_format_product_subscription_period_interval( $interval ) {

	$output = $interval;
	if( !empty( $interval ) ) {
		switch( $interval ) {

			case '1':
				$output = __( 'per', 'woocommerce-exporter' );
				break;

			case '2':
				$output = __( 'every 2nd', 'woocommerce-exporter' );
				break;

			case '3':
				$output = __( 'every 3rd', 'woocommerce-exporter' );
				break;

			case '4':
				$output = __( 'every 4th', 'woocommerce-exporter' );
				break;

			case '5':
				$output = __( 'every 5th', 'woocommerce-exporter' );
				break;

			case '6':
				$output = __( 'every 6th', 'woocommerce-exporter' );
				break;

		}
	}
	return $output;

}

function woo_ce_format_product_subscripion_length( $length, $period = '' ) {

	$output = $length;
	if( $length == '0' ) {
		$output = __( 'all time', 'woocommerce-exporter' );
	}
	return $output;

}

function woo_ce_format_product_subscription_limit( $limit ) {

	$output = $limit;
	if( !empty( $limit ) ) {
		$limit = strtolower( $limit );
		switch( $limit ) {

			case 'active':
				$output = __( 'Active Subscription', 'woocommerce-exporter' );
				break;

			case 'any':
				$output = __( 'Any Subscription', 'woocommerce-exporter' );
				break;

			case 'no':
				$output = __( 'Do not limit', 'woocommerce-exporter' );
				break;

		}
	}
	return $output;

}
?>