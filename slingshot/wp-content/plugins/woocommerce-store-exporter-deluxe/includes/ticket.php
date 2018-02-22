<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_ticket_count' ) ) {
		function woo_ce_get_export_type_ticket_count() {

			$count = 0;
			// WooCommerce Events - http://www.woocommerceevents.com/
			$post_type = apply_filters( 'woo_ce_ticket_post_type', 'event_magic_tickets' );

			// Override for WordPress MultiSite
			if( woo_ce_is_network_admin() ) {
				$sites = wp_get_sites();
				foreach( $sites as $site ) {
					switch_to_blog( $site['blog_id'] );
					$args = array(
						'post_type' => $post_type,
						'posts_per_page' => 1,
						'fields' => 'ids',
						'suppress_filters' => 1
					);
					$count_query = new WP_Query( $args );
					$count += $count_query->found_posts;
					restore_current_blog();
				}
				return $count;
			}

			// Check if the existing Transient exists
			$cached = get_transient( WOO_CD_PREFIX . '_ticket_count' );
			if( $cached == false ) {
				$args = array(
					'post_type' => $post_type,
					'posts_per_page' => 1,
					'fields' => 'ids',
					'suppress_filters' => 1
				);
				$count_query = new WP_Query( $args );
				$count = $count_query->found_posts;
				set_transient( WOO_CD_PREFIX . '_ticket_count', $count, HOUR_IN_SECONDS );
			} else {
				$count = $cached;
			}
			return $count;

		}
	}

	/* End of: WordPress Administration */

}

// Return whether the Ticket Post Type is in use
function woo_ce_detect_tickets() {

	if( 
		woo_ce_detect_export_plugin( 'tickera' ) || 
		taxonomy_exists( apply_filters( 'woo_ce_ticket_post_type', 'event_magic_tickets' ) )
	)
		return true;

}

// Returns a list of Ticket export columns
function woo_ce_get_ticket_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'ticket';

	$fields = array();
	$fields[] = array(
		'name' => 'post_id',
		'label' => __( 'Post ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'ticket_id',
		'label' => __( 'Ticket ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'status',
		'label' => __( 'Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_id',
		'label' => __( 'Product ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_id',
		'label' => __( 'Order ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_id',
		'label' => __( 'User ID', 'woocommerce-exporter' )
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
function woo_ce_override_ticket_field_labels( $fields = array() ) {

	$export_type = 'ticket';

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
add_filter( 'woo_ce_ticket_fields', 'woo_ce_override_ticket_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_ticket_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_ticket_fields();
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

// Returns a list of WooCommerce Ticket IDs to export process
function woo_ce_get_tickets( $args = array() ) {

	global $export;

	$limit_volume = -1;
	$offset = 0;

	$orderby = 'ID';
	$order = 'ASC';
	if( $args ) {
		// Do something
	}
	$post_type = apply_filters( 'woo_ce_ticket_post_type', 'event_magic_tickets' );
	$args = array(
		'post_type' => $post_type,
		'orderby' => $orderby,
		'order' => $order,
		'offset' => $offset,
		'posts_per_page' => $limit_volume,
		'fields' => 'ids',
		'suppress_filters' => false
	);
	$tickets = array();

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_tickets_args', $args );

	$ticket_ids = new WP_Query( $args );

	if( $ticket_ids->posts ) {
		foreach( $ticket_ids->posts as $ticket_id ) {

			if( isset( $ticket_id ) )
				$tickets[] = $ticket_id;

		}
		// Only populate the $export Global if it is an export
		if( isset( $export ) )
			$export->total_rows = count( $tickets );
		unset( $ticket_ids, $ticket_id );

	}
	return $tickets;

}

function woo_ce_export_dataset_override_ticket( $output = null, $export_type = null ) {

	global $export;

	if( $tickets = woo_ce_get_tickets( $export->args ) ) {
		$export->total_rows = count( $tickets );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $tickets as $ticket ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_ticket_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					if( apply_filters( 'woo_ce_export_xml_ticket_id_attribute', true ) )
						$child->addAttribute( 'id', ( isset( $ticket->comment_id ) ? $ticket->comment_id : '' ) );
					$ticket = woo_ce_get_ticket_data( $ticket, $export->args, array_keys( $export->fields ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $ticket->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $ticket->$field ) )
									$child->addChild( apply_filters( 'woo_ce_export_xml_ticket_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $ticket->$field ) ) );
								else
									$child->addChild( apply_filters( 'woo_ce_export_xml_ticket_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $ticket->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			foreach( $tickets as $key => $ticket )
				$tickets[$key] = woo_ce_get_ticket_data( $ticket, $export->args, array_keys( $export->fields ) );
			$output = $tickets;
		}
		unset( $tickets, $ticket );
	}

	return $output;

}

function woo_ce_export_dataset_multisite_override_ticket( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			if( $tickets = woo_ce_get_tickets( $export->args ) ) {
				$export->total_rows = count( $tickets );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $tickets as $ticket ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_ticket_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							if( apply_filters( 'woo_ce_export_xml_ticket_id_attribute', true ) )
								$child->addAttribute( 'id', ( isset( $ticket->comment_id ) ? $ticket->comment_id : '' ) );
							$ticket = woo_ce_get_ticket_data( $ticket, $export->args, array_keys( $export->fields ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $ticket->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $ticket->$field ) )
											$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $ticket->$field ) ) );
										else
											$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $ticket->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					if( is_null( $output ) )
						$output = $tickets;
					else
						$output = array_merge( $output, $tickets );
				}
				unset( $tickets, $ticket );
			}
			restore_current_blog();
		}
	}
	return $output;

}

function woo_ce_get_ticket_data( $ticket_id = 0, $args = array(), $fields = array() ) {

	$ticket = get_post( $ticket_id );

	$ticket->post_id = $ticket->ID;

	// Allow Plugin/Theme authors to add support for additional Ticket columns
	$ticket = apply_filters( 'woo_ce_ticket_item', $ticket, $ticket_id );

	// Trim back the Ticket just to requested export fields
	if( !empty( $fields ) ) {
		$fields = array_merge( $fields, array( 'id', 'ID', 'post_parent', 'filter' ) );
		if( !empty( $ticket ) ) {
			foreach( $ticket as $key => $data ) {
				if( !in_array( $key, $fields ) )
					unset( $ticket->$key );
			}
		}
	}

	return $ticket;

}
?>