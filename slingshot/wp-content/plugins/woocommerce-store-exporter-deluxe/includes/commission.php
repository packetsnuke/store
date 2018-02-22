<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_commission_count' ) ) {
		function woo_ce_get_export_type_commission_count( $count = 0, $export_type = '', $args ) {

			if( $export_type <> 'commission' )
				return $count;

			$count = 0;
			$post_type = 'shop_commission';

			// Override for WordPress MultiSite
			if( woo_ce_is_network_admin() ) {
				$sites = wp_get_sites();
				foreach( $sites as $site ) {
					switch_to_blog( $site['blog_id'] );
					if( post_type_exists( $post_type ) ) {
						$count += woo_ce_count_object( wp_count_posts( $post_type ) );
					} else if( woo_ce_detect_export_plugin( 'wc_vendors' ) ) {
						// Check for WC-Vendors

						global $wpdb;

						$count += $wpdb->get_var( 'SELECT COUNT(id) FROM `' . $wpdb->prefix . 'pv_commission`' );
					}
					restore_current_blog();
				}
				return $count;
			}

			// Check if the existing Transient exists
			$cached = get_transient( WOO_CD_PREFIX . '_commission_count' );
			if( $cached == false ) {
				if( post_type_exists( $post_type ) ) {
					$count = woo_ce_count_object( wp_count_posts( $post_type ) );
				} else if( woo_ce_detect_export_plugin( 'wc_vendors' ) ) {
					// Check for WC-Vendors

					global $wpdb;

					$count = $wpdb->get_var( 'SELECT COUNT(id) FROM `' . $wpdb->prefix . 'pv_commission`' );
				}
				set_transient( WOO_CD_PREFIX . '_commission_count', $count, HOUR_IN_SECONDS );
			} else {
				$count = $cached;
			}
			return $count;

		}
		add_filter( 'woo_ce_get_export_type_count', 'woo_ce_get_export_type_commission_count', 10, 3 );
	}

	function woo_ce_commission_scheduled_export_save( $post_ID = 0 ) {

		update_post_meta( $post_ID, '_filter_commission_orderby', ( isset( $_POST['commission_filter_orderby'] ) ? sanitize_text_field( $_POST['commission_filter_orderby'] ) : false ) );

	}
	add_action( 'woo_ce_extend_scheduled_export_save', 'woo_ce_commission_scheduled_export_save' );

	// Returns date of first Commission received, any status
	function woo_ce_get_commission_first_date( $date_format = 'd/m/Y' ) {

		$output = date( $date_format, mktime( 0, 0, 0, date( 'n' ), 1 ) );
		$post_type = 'shop_commission';
		$args = array(
			'post_type' => $post_type,
			'orderby' => 'post_date',
			'order' => 'ASC',
			'numberposts' => 1
		);
		$commissions = get_posts( $args );
		if( $commissions ) {
			$commission = strtotime( $commissions[0]->post_date );
			$output = date( $date_format, $commission );
			unset( $commissions, $commission );
		}
		return $output;

	}

	// HTML template for displaying the number of each export type filter on the Archives screen
	function woo_ce_commissions_stock_status_count( $type = '' ) {

		$output = 0;
		$post_type = 'shop_commission';
		$meta_key = '_paid_status';
		$args = array(
			'post_type' => $post_type,
			'meta_key' => $meta_key,
			'meta_value' => null,
			'numberposts' => -1,
			'fields' => 'ids'
		);
		if( $type )
			$args['meta_value'] = $type;
		$commission_ids = new WP_Query( $args );
		if( !empty( $commission_ids->posts ) )
			$output = count( $commission_ids->posts );
		return $output;

	}

	function woo_ce_commission_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Commission Export Type
		if( $export_type <> 'commission' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'commission_dates_filter' => ( isset( $_POST['commission_dates_filter'] ) ? sanitize_text_field( $_POST['commission_dates_filter'] ) : false ),
			'commission_dates_from' => ( isset( $_POST['commission_dates_from'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['commission_dates_from'] ) ) : '' ),
			'commission_dates_to' => ( isset( $_POST['commission_dates_to'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['commission_dates_to'] ) ) : '' ),
			'commission_dates_filter_variable' => ( isset( $_POST['commission_dates_filter_variable'] ) ? absint( $_POST['commission_dates_filter_variable'] ) : false ),
			'commission_dates_filter_variable_length' => ( isset( $_POST['commission_dates_filter_variable_length'] ) ? sanitize_text_field( $_POST['commission_dates_filter_variable_length'] ) : false ),
			'commission_product_vendor' => ( isset( $_POST['commission_filter_product_vendor'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['commission_filter_product_vendor'] ) ) : false ),
			'commission_status' => ( isset( $_POST['commission_filter_commission_status'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', $_POST['commission_filter_commission_status'] ) ) : false ),
			'commission_orderby' => ( isset( $_POST['commission_orderby'] ) ? sanitize_text_field( $_POST['commission_orderby'] ) : false ),
			'commission_order' => ( isset( $_POST['commission_order'] ) ? sanitize_text_field( $_POST['commission_order'] ) : false )
		);
		$args = wp_parse_args( $args, $defaults );

		// Save dataset export specific options
		if( $args['commission_orderby'] <> woo_ce_get_option( 'commission_orderby' ) )
			woo_ce_update_option( 'commission_orderby', $args['commission_orderby'] );
		if( $args['commission_order'] <> woo_ce_get_option( 'commission_order' ) )
			woo_ce_update_option( 'commission_order', $args['commission_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_commission_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

function woo_ce_cron_commission_dataset_args( $args, $export_type = '', $is_scheduled = 0 ) {

	// Check if we're dealing with the Commission Export Type
	if( $export_type <> 'commission' )
		return $args;

	$commission_dates_filter = false;
	$commission_filter_dates_from = false;
	$commission_filter_dates_to = false;
	$commission_filter_date_variable = false;
	$commission_filter_date_variable_length = false;

	if( $is_scheduled ) {
		$scheduled_export = ( $is_scheduled ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );
		// Commission Date
		$commission_dates_filter = get_post_meta( $scheduled_export, '_filter_commission_date', true );
		if( !empty( $commission_dates_filter ) ) {
			switch( $commission_dates_filter ) {

				case 'manual':
					$commission_filter_dates_from = get_post_meta( $scheduled_export, '_filter_commission_dates_from', true );
					$commission_filter_dates_to = get_post_meta( $scheduled_export, '_filter_commission_date_to', true );
					break;

				case 'variable':
					$commission_filter_date_variable = get_post_meta( $scheduled_export, '_filter_commission_date_variable', true );
					$commission_filter_date_variable_length = get_post_meta( $scheduled_export, '_filter_commission_date_variable_length', true );
					break;

			}
		}
	}

	// Merge in the form data for this dataset
	$defaults = array(
		'commission_dates_filter' => $commission_dates_filter,
		'commission_dates_from' => ( !empty( $commission_filter_dates_from ) ? sanitize_text_field( $commission_filter_dates_from ) : false ),
		'commission_dates_to' => ( !empty( $commission_filter_dates_to ) ? sanitize_text_field( $commission_filter_dates_to ) : false ),
		'commission_dates_filter_variable' => ( !empty( $commission_filter_date_variable ) ? absint( $commission_filter_date_variable ) : false ),
		'commission_dates_filter_variable_length' => ( !empty( $commission_filter_date_variable_length ) ? sanitize_text_field( $commission_filter_date_variable_length ) : false )
	);
	$args = wp_parse_args( $args, $defaults );

	return $args;

}
add_filter( 'woo_ce_extend_cron_dataset_args', 'woo_ce_cron_commission_dataset_args', 10, 3 );

function woo_ce_get_commission_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'commission';

	$fields = array();
	$fields[] = array(
		'name' => 'ID',
		'label' => __( 'Commission ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_date',
		'label' => __( 'Commission Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_id',
		'label' => __( 'Product ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_name',
		'label' => __( 'Product Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_sku',
		'label' => __( 'Product SKU', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_vendor_id',
		'label' => __( 'Product Vendor ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_vendor_name',
		'label' => __( 'Product Vendor Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'commission_amount',
		'label' => __( 'Commission Amount', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'paid_status',
		'label' => __( 'Commission Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_status',
		'label' => __( 'Post Status', 'woocommerce-exporter' )
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
function woo_ce_override_commission_field_labels( $fields = array() ) {

	$export_type = 'commission';

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
add_filter( 'woo_ce_commission_fields', 'woo_ce_override_commission_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_commission_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_commission_fields();
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

// Returns a list of Commission Post IDs
function woo_ce_get_commissions( $args = array() ) {

	global $export;

	$limit_volume = -1;
	$offset = 0;

	if( $args ) {
		$product_vendors = ( isset( $args['commission_product_vendor'] ) ? $args['commission_product_vendor'] : false );
		$status = ( isset( $args['commission_status'] ) ? $args['commission_status'] : false );
		$limit_volume = ( isset( $args['limit_volume'] ) ? $args['limit_volume'] : false );
		$offset = ( isset( $args['offset'] ) ? $args['offset'] : false );
		$orderby = ( isset( $args['commission_orderby'] ) ? $args['commission_orderby'] : 'ID' );
		$order = ( isset( $args['commission_order'] ) ? $args['commission_order'] : 'ASC' );
		$commission_dates_filter = ( isset( $args['commission_dates_filter'] ) ? $args['commission_dates_filter'] : false );
		switch( $commission_dates_filter ) {

			case 'today':
				$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n' ), date( 'd' ) ) );
				$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n' ), date( 'd' ) ) );
				break;

			case 'yesterday':
				$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( '-2 days' ) ), date( 'd', strtotime( '-2 days' ) ) ) );
				$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( '-1 days' ) ), date( 'd', strtotime( '-1 days' ) ) ) );
				break;

			case 'current_week':
				$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( 'this Monday' ) ), date( 'd', strtotime( 'this Monday' ) ) ) );
				$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( 'next Sunday' ) ), date( 'd', strtotime( 'next Sunday' ) ) ) );
				break;

			case 'last_week':
				$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( 'last Monday' ) ), date( 'd', strtotime( 'last Monday' ) ) ) );
				$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( 'last Sunday' ) ), date( 'd', strtotime( 'last Sunday' ) ) ) );
				break;

			case 'current_month':
				$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n' ), 1 ) );
				$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( '+1 month' ) ), 0 ) );
				break;

			case 'last_month':
				$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( '-1 month' ) ), 1 ) );
				$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n' ), 0 ) );
				break;

			case 'last_quarter':
				break;

			case 'manual':
				$commission_dates_from = woo_ce_format_order_date( $args['commission_dates_from'] );
				$commission_dates_to = woo_ce_format_order_date( $args['commission_dates_to'] );
				break;

			case 'variable':
				$commission_filter_date_variable = ( isset( $args['commission_dates_filter_variable'] ) ? $args['commission_dates_filter_variable'] : false );
				$commission_filter_date_variable_length = ( isset( $args['commission_dates_filter_variable_length'] ) ? $args['commission_dates_filter_variable_length'] : false );
				if( $commission_filter_date_variable !== false && $commission_filter_date_variable_length !== false ) {
					$commission_filter_date_strtotime = sprintf( '-%d %s', $commission_filter_date_variable, $commission_filter_date_variable_length );
					$commission_dates_from = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n', strtotime( $commission_filter_date_strtotime ) ), date( 'd', strtotime( $commission_filter_date_strtotime ) ) ) );
					$commission_dates_to = date( 'd-m-Y', mktime( 0, 0, 0, date( 'n' ), date( 'd' ) ) );
					unset( $commission_filter_date_variable, $commission_filter_date_variable_length, $commission_filter_date_strtotime );
				}
				break;

			default:
				$commission_dates_from = false;
				$commission_dates_to = false;
				break;

		}
		if( $commission_dates_from && $commission_dates_to ) {
			$commission_dates_from = strtotime( $commission_dates_from );
			$commission_dates_to = explode( '-', $commission_dates_to );
			// Check that a valid date was provided
			if( isset( $commission_dates_to[0] ) && isset( $commission_dates_to[1] ) && isset( $commission_dates_to[2] ) )
				$commission_dates_to = strtotime( date( 'd-m-Y', mktime( 0, 0, 0, $commission_dates_to[1], $commission_dates_to[0]+1, $commission_dates_to[2] ) ) );
			else	
				$commission_dates_to = false;
		}
	}
	$post_type = 'shop_commission';
	$args = array(
		'post_type' => $post_type,
		'orderby' => $orderby,
		'order' => $order,
		'offset' => $offset,
		'posts_per_page' => $limit_volume,
		'post_status' => woo_ce_post_statuses(),
		'fields' => 'ids',
		'suppress_filters' => false
	);
	if( !empty( $product_vendors ) ) {
		$args['meta_query'][] = array(
			'key' => '_commission_vendor',
			'value' => $product_vendors,
			'compare' => 'IN'
		);
	}
	if( !empty( $status ) ) {
		$args['meta_query'][] = array(
			'key' => '_paid_status',
			'value' => $status,
			'compare' => 'IN'
		);
	}
	$commissions = array();

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_commissions_args', $args );

	// Override for Plugins that use custom tables; naughty, naughty...
	if( apply_filters( 'woo_ce_override_commission_data', false ) ) {
		$commissions = apply_filters( 'woo_ce_override_get_commissions', false );
		return $commissions;
	}

	$commission_ids = new WP_Query( $args );
	if( $commission_ids->posts ) {
		foreach( $commission_ids->posts as $commission_id ) {

			// Get Commission details
			$commission = get_post( $commission_id );

			// Filter Commission dates by dropping those outside the date range
			if( $commission_dates_from && $commission_dates_to ) {
				if( ( strtotime( $commission->post_date ) > $commission_dates_from ) && ( strtotime( $commission->post_date ) < $commission_dates_to ) ) {
					// Do nothing
				} else {
					unset( $commission );
					continue;
				}
			}

			$commissions[] = $commission_id;
		}
		unset( $commission_ids, $commission_id );
	}
	return $commissions;

}

function woo_ce_export_dataset_override_commission( $output = null, $export_type = null ) {

	global $export;

	if( $commissions = woo_ce_get_commissions( $export->args ) ) {
		$export->total_rows = count( $commissions );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $commissions as $commission ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_commission_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					if( apply_filters( 'woo_ce_export_xml_commission_node_id_attribute', true ) )
						$child->addAttribute( 'id', ( isset( $commission ) ? $commission : '' ) );
					$commission = woo_ce_get_commission_data( $commission, $export->args, array_keys( $export->fields ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $commission->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $commission->$field ) )
									$child->addChild( apply_filters( 'woo_ce_export_xml_commission_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $commission->$field ) ) );
								else
									$child->addChild( apply_filters( 'woo_ce_export_xml_commission_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $commission->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			foreach( $commissions as $key => $commission )
				$commissions[$key] = woo_ce_get_commission_data( $commission, $export->args, array_keys( $export->fields ) );
			$output = $commissions;
		}
		unset( $commissions, $commission );
	}
	return $output;

}

function woo_ce_export_dataset_multisite_override_commission( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			if( $commissions = woo_ce_get_commissions( $export->args ) ) {
				$export->total_rows = count( $commissions );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $commissions as $commission ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_commission_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							if( apply_filters( 'woo_ce_export_xml_commission_node_id_attribute', true ) )
								$child->addAttribute( 'id', ( isset( $commission ) ? $commission : '' ) );
							$commission = woo_ce_get_commission_data( $commission, $export->args, array_keys( $export->fields ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $commission->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $commission->$field ) )
											$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $commission->$field ) ) );
										else
											$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $commission->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					foreach( $commissions as $key => $commission )
						$commissions[$key] = woo_ce_get_commission_data( $commission, $export->args, array_keys( $export->fields ) );
					if( is_null( $output ) )
						$output = $commissions;
					else
						$output = array_merge( $output, $commissions );
				}
				unset( $commissions, $commission );
			}
			restore_current_blog();
		}
	}
	return $output;

}

function woo_ce_get_commission_data( $commission_id = 0, $args = array() ) {

	global $export;

	$commission = get_post( $commission_id );

	// Override for Plugins that use custom tables; naughty, naughty...
	if( apply_filters( 'woo_ce_override_commission_data', false ) ) {
		$commission = apply_filters( 'woo_ce_override_get_commission_data', false, $commission_id, $args );
		return $commission;
	}

	$commission->title = $commission->post_title;
	$commission->product_id = get_post_meta( $commission->ID, '_commission_product', true );
	$commission->product_name = woo_ce_format_post_title( get_the_title( $commission->product_id ) );
	$commission->product_sku = get_post_meta( $commission->product_id, '_sku', true );
	$commission->product_vendor_id = get_post_meta( $commission->ID, '_commission_vendor', true );
	$product_vendor = woo_ce_get_product_vendor_data( $commission->product_vendor_id );
	$commission->product_vendor_name = ( isset( $product_vendor->title ) ? $product_vendor->title : '' );
	unset( $product_vendor );

	$commission->commission_amount = get_post_meta( $commission->ID, '_commission_amount', true );
	// Check that a valid price has been provided
	if( isset( $commission->commission_amount ) && $commission->commission_amount != '' && function_exists( 'wc_format_localized_price' ) )
		$commission->commission_amount = woo_ce_format_price( $commission->commission_amount );
	$commission->paid_status = woo_ce_format_commission_paid_status( get_post_meta( $commission->ID, '_paid_status', true ) );
	$commission->post_date = woo_ce_format_date( $commission->post_date );
	$commission->post_status = woo_ce_format_post_status ( $commission->post_status );

	return $commission;

}

function woo_ce_extend_commission_fields( $fields = array() ) {

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) ) {
		$fields[] = array(
			'name' => 'title',
			'label' => __( 'Commission Title', 'woocommerce-exporter' )
		);
	}

	return $fields;

}
add_filter( 'woo_ce_commission_fields', 'woo_ce_extend_commission_fields' );

function woo_ce_format_commission_paid_status( $paid_status = '' ) {

	$output = $paid_status;
	switch( $output ) {

		case 'paid':
			$output = __( 'Paid', 'woocommerce-exporter' );
			break;

		default:
		case 'unpaid':
			$output = __( 'Unpaid', 'woocommerce-exporter' );
			break;

	}
	return $output;

}
?>