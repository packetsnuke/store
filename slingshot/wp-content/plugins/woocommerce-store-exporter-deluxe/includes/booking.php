<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_booking_count' ) ) {
		function woo_ce_get_export_type_booking_count( $count = 0, $export_type = '', $args ) {

			if( $export_type <> 'booking' )
				return $count;

			$count = 0;

			// Override for WordPress MultiSite
			if( woo_ce_is_network_admin() ) {
				$sites = wp_get_sites();
				foreach( $sites as $site ) {
					switch_to_blog( $site['blog_id'] );
					if( class_exists( 'WC_Bookings' ) ) {
						$count += woo_ce_get_booking_count();
					}
					restore_current_blog();
				}
			}

			// Check that WooCommerce Subscriptions exists
			if( class_exists( 'WC_Bookings' ) ) {
				$count = woo_ce_get_booking_count();
			}
			return $count;

		}
		add_filter( 'woo_ce_get_export_type_count', 'woo_ce_get_export_type_booking_count', 10, 3 );
	}

	function woo_ce_get_booking_count() {

		$count = 0;
		// Check if the existing Transient exists
		$cached = get_transient( WOO_CD_PREFIX . '_booking_count' );
		if( $cached == false ) {

			// Allow store owners to force the Booking count
			$count = apply_filters( 'woo_ce_get_booking_count', $count );

			if( $count == 0 ) {
				$post_type = apply_filters( 'woo_ce_booking_post_type', 'wc_booking' );
				$args = array(
					'post_type' => $post_type,
					'posts_per_page' => 1,
					'fields' => 'ids',
					'suppress_filters' => 1
				);
				$count_query = new WP_Query( $args );
				$count += $count_query->found_posts;
			}
			set_transient( WOO_CD_PREFIX . '_booking_count', $count, HOUR_IN_SECONDS );
		} else {
			$count = $cached;
		}
		return $count;

	}

	function woo_ce_booking_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Booking Export Type
		if( $export_type <> 'booking' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'booking_orderby' => ( isset( $_POST['booking_orderby'] ) ? sanitize_text_field( $_POST['booking_orderby'] ) : false ),
			'booking_order' => ( isset( $_POST['booking_order'] ) ? sanitize_text_field( $_POST['booking_order'] ) : false )
		);
		$args = wp_parse_args( $args, $defaults );

		// Save dataset export specific options
		if( $args['booking_orderby'] <> woo_ce_get_option( 'booking_orderby' ) )
			woo_ce_update_option( 'booking_orderby', $args['booking_orderby'] );
		if( $args['booking_order'] <> woo_ce_get_option( 'booking_order' ) )
			woo_ce_update_option( 'booking_order', $args['booking_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_booking_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

function woo_ce_cron_booking_dataset_args( $args, $export_type = '', $is_scheduled = 0 ) {

	// Check if we're dealing with the Booking Export Type
	if( $export_type <> 'booking' )
		return $args;

	$booking_orderby = false;

	if( $is_scheduled ) {
		$scheduled_export = ( $is_scheduled ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );

		$booking_orderby = get_post_meta( $scheduled_export, '_filter_booking_orderby', true );
	}

	// Merge in the form data for this dataset
	$defaults = array(
		'booking_orderby' => ( !empty( $booking_orderby ) ? $booking_orderby : false )
	);
	$args = wp_parse_args( $args, $defaults );

	return $args;

}
add_filter( 'woo_ce_extend_cron_dataset_args', 'woo_ce_cron_booking_dataset_args', 10, 3 );

// Returns a list of Subscription export columns
function woo_ce_get_booking_fields( $format = 'full' ) {

	$export_type = 'booking';

	$fields = array();
	$fields[] = array(
		'name' => 'booking_number',
		'label' => __( 'Booking Number', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_id',
		'label' => __( 'Order ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'date_created',
		'label' => __( 'Date Created', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'last_modified',
		'label' => __( 'Last Modified', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'booking_status',
		'label' => __( 'Booking Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_id',
		'label' => __( 'User ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_name',
		'label' => __( 'Username', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_role',
		'label' => __( 'User Role', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'customer_name',
		'label' => __( 'Customer Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'customer_email',
		'label' => __( 'Customer E-mail', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'booked_product_id',
		'label' => __( 'Booked Product ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'booked_product_sku',
		'label' => __( 'Booked Product SKU', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'booked_product_name',
		'label' => __( 'Booked Product', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'parent_booking_id',
		'label' => __( 'Parent Booking ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_item_id',
		'label' => __( 'Order Item ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'booking_start_date',
		'label' => __( 'Booking Start Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'booking_start_time',
		'label' => __( 'Booking Start Time', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'booking_end_date',
		'label' => __( 'Booking End Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'booking_end_time',
		'label' => __( 'Booking End Time', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'booking_all_day',
		'label' => __( 'All Day Booking', 'woocommerce-exporter' )
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
function woo_ce_override_booking_field_labels( $fields = array() ) {

	$export_type = 'booking';

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
add_filter( 'woo_ce_booking_fields', 'woo_ce_override_booking_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_booking_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_booking_fields();
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

// Returns a list of WooCommerce Booking IDs to export process
function woo_ce_get_bookings( $args = array() ) {

	global $export;

	$limit_volume = -1;
	$offset = 0;

	$booking_status = false;
	$orderby = 'ID';
	$order = 'ASC';
	if( $args ) {
		if( !empty( $args['booking_status'] ) )
			$booking_status = $args['booking_status'];
	}
	$post_type = apply_filters( 'woo_ce_booking_post_type', 'wc_booking' );
	$post_status = apply_filters( 'woo_ce_get_bookings_status', array( 'complete', 'paid', 'confirmed', 'unpaid', 'pending-confirmation', 'cancelled', 'in-cart', 'was-in-cart' ) );
	$args = array(
		'post_type' => $post_type,
		'orderby' => $orderby,
		'order' => $order,
		'offset' => $offset,
		'posts_per_page' => $limit_volume,
		'post_status' => woo_ce_post_statuses( $post_status, true ),
		'fields' => 'ids',
		'suppress_filters' => false
	);
	// Filter Bookings by Post Status
	if( $booking_status ) {
		$args['post_status'] = woo_ce_post_statuses( $booking_status, true );
	}
	$bookings = array();

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_bookings_args', $args );

	$booking_ids = new WP_Query( $args );

	if( $booking_ids->posts ) {
		foreach( $booking_ids->posts as $booking_id ) {

			if( isset( $booking_id ) )
				$bookings[] = $booking_id;

		}
		// Only populate the $export Global if it is an export
		if( isset( $export ) )
			$export->total_rows = count( $bookings );
		unset( $booking_ids, $booking_id );

	}
	return $bookings;

}

function woo_ce_export_dataset_override_booking( $output = null, $export_type = null ) {

	global $export;

	if( $bookings = woo_ce_get_bookings( $export->args ) ) {
		$export->total_rows = count( $bookings );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $bookings as $booking ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_booking_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					if( apply_filters( 'woo_ce_export_xml_booking_node_id_attribute', true ) )
						$child->addAttribute( 'id', ( isset( $booking->comment_id ) ? $booking->comment_id : '' ) );
					$booking = woo_ce_get_booking_data( $booking, $export->args, array_keys( $export->fields ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $booking->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $booking->$field ) )
									$child->addChild( apply_filters( 'woo_ce_export_xml_booking_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $booking->$field ) ) );
								else
									$child->addChild( apply_filters( 'woo_ce_export_xml_booking_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $booking->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			foreach( $bookings as $key => $booking )
				$bookings[$key] = woo_ce_get_booking_data( $booking, $export->args, array_keys( $export->fields ) );
			$output = $bookings;
		}
		unset( $bookings, $booking );
	}

	return $output;

}

function woo_ce_export_dataset_multisite_override_booking( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			if( $bookings = woo_ce_get_bookings( $export->args ) ) {
				$export->total_rows = count( $bookings );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $bookings as $booking ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_booking_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							if( apply_filters( 'woo_ce_export_xml_booking_node_id_attribute', true ) )
								$child->addAttribute( 'id', ( isset( $booking->comment_id ) ? $booking->comment_id : '' ) );
							$booking = woo_ce_get_booking_data( $booking, $export->args, array_keys( $export->fields ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $booking->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $booking->$field ) )
											$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $booking->$field ) ) );
										else
											$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $booking->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					if( is_null( $output ) )
						$output = $bookings;
					else
						$output = array_merge( $output, $bookings );
				}
				unset( $bookings, $booking );
			}
			restore_current_blog();
		}
	}
	return $output;

}

function woo_ce_get_booking_data( $booking_id = 0, $args = array(), $fields = array() ) {

	$booking = get_post( $booking_id );

	// Allow Plugin/Theme authors to add support for additional Booking columns
	$booking = apply_filters( 'woo_ce_booking_item', $booking, $booking_id );

	$booking->booking_number = $booking->ID;
	$booking->order_id = ( !empty( $booking->post_parent ) ? $booking->post_parent : '-' );
	$booking->date_created = woo_ce_format_date( $booking->post_date );
	$booking->last_modified = woo_ce_format_date( $booking->post_modified );
	$booking->booking_status = woo_ce_format_post_status( $booking->post_status );
	$booking->user_id = get_post_meta( $booking->ID, '_booking_customer_id', true );
	if( $booking->user_id == 0 ) {
		$booking->user_id = '';
	} else {
		$user_data = woo_ce_get_user_data( $booking->user_id );
		if( !empty( $user_data ) ) {
			$booking->user_name = woo_ce_get_username( $booking->user_id );
			$booking->user_role = woo_ce_format_user_role_label( woo_ce_get_user_role( $booking->user_id ) );
			$booking->customer_name = $user_data->full_name;
			$booking->customer_email = $user_data->email;
		}
		unset( $user_data );
	}

	$booking->booked_product_id = get_post_meta( $booking->ID, '_booking_product_id', true );
	if( $booking->booked_product_id == 0 ) {
		$booking->booked_product_id = '';
	} else {
		$product_data = woo_ce_get_product_data( $booking->booked_product_id );
		// Check if Product exists
		if( !empty( $product_data ) ) {
			$booking->booked_product_sku = $product_data->sku;
			$booking->booked_product_name = $product_data->name;
		} else {
			$booking->booked_product_sku = '-';
			$booking->booked_product_name = '-';
		}
		unset( $product_data );
	}
	$booking->parent_booking_id = get_post_meta( $booking->ID, '_booking_parent_id', true );
	$booking->order_item_id = get_post_meta( $booking->ID, '_booking_order_item_id', true );
	$booking->booking_all_day = get_post_meta( $booking->ID, '_booking_all_day', true );
	$booking_start_date = get_post_meta( $booking->ID, '_booking_start', true );
	if( $booking_start_date ) {
		$booking->booking_start_date = woo_ce_format_date( $booking_start_date );
		if( $booking->booking_all_day ) {
			$booking->booking_start_time = '-';
		} else {
			if( function_exists( 'wc_format_datetime' ) )
				$booking->booking_start_time = wc_format_datetime( $booking_start_date, get_option( 'time_format' ) );
			if( empty( $booking->booking_start_time ) )
				$booking->booking_start_time = mysql2date( 'H:i:s', $booking_start_date );
		}
	}
	$booking_end_date = get_post_meta( $booking->ID, '_booking_end', true );
	if( $booking_end_date ) {
		$booking->booking_end_date = woo_ce_format_date( $booking_end_date );
		if( $booking->booking_all_day ) {
			$booking->booking_end_time = '-';
		} else {
			if( function_exists( 'wc_format_datetime' ) )
				$booking->booking_end_time = wc_format_datetime( $booking_end_date, get_option( 'time_format' ) );
			if( empty( $booking->booking_end_time ) )
				$booking->booking_end_time = mysql2date( 'H:i:s', $booking_end_date );
		}
	}
	$booking->booking_all_day = woo_ce_format_switch( $booking->booking_all_day );

	// Trim back the Booking just to requested export fields
	if( !empty( $fields ) ) {
		$fields = array_merge( $fields, array( 'id', 'ID', 'post_parent', 'filter' ) );
		if( !empty( $booking ) ) {
			foreach( $booking as $key => $data ) {
				if( !in_array( $key, $fields ) )
					unset( $booking->$key );
			}
		}
	}

	return $booking;

}

function woo_ce_extend_booking_format_post_status( $output, $post_status ) {

	// Don't ask, this is how WooCommerce Bookings does things...
	if( function_exists( 'get_wc_booking_statuses' ) ) {
		$booking_stati = get_wc_booking_statuses();
		if( !empty( $booking_stati ) ) {
			foreach( $booking_stati as $booking_status ) {
				if( $booking_status == $post_status ) {
					$output = __( $booking_status, 'woocommerce-bookings' );
					$output = ucfirst( $output );
					break;
				}
			}
		}
	}
	return $output;

}
add_filter( 'woo_ce_format_post_status', 'woo_ce_extend_booking_format_post_status', 10, 2 );
?>