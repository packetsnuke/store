<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	function woo_ce_get_export_type_product_vendor_count( $count = 0, $export_type = '', $args ) {

		if( $export_type <> 'product_vendor' )
			return $count;

		$count = 0;
		$term_taxonomy = apply_filters( 'woo_ce_product_vendor_term_taxonomy', 'wcpv_product_vendors' );

		// Override for WordPress MultiSite
		if( woo_ce_is_network_admin() ) {
			$sites = wp_get_sites();
			foreach( $sites as $site ) {
				switch_to_blog( $site['blog_id'] );
				if( taxonomy_exists( $term_taxonomy ) )
					$count += wp_count_terms( $term_taxonomy );
				restore_current_blog();
			}
			return $count;
		}

		// Check if the existing Transient exists
		$cached = get_transient( WOO_CD_PREFIX . '_product_vendor_count' );
		if( $cached == false ) {
			if( taxonomy_exists( $term_taxonomy ) )
				$count = wp_count_terms( $term_taxonomy );
			set_transient( WOO_CD_PREFIX . '_product_vendor_count', $count, HOUR_IN_SECONDS );
		} else {
			$count = $cached;
		}
		return $count;

	}
	add_filter( 'woo_ce_get_export_type_count', 'woo_ce_get_export_type_product_vendor_count', 10, 3 );

	/* End of: WordPress Administration */

}

// Returns a list of Product Vendor export columns
function woo_ce_get_product_vendor_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'product_vendor';

	$fields = array();
	$fields[] = array(
		'name' => 'ID',
		'label' => __( 'Product Vendor ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'title',
		'label' => __( 'Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'slug',
		'label' => __( 'Slug', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'description',
		'label' => __( 'Description', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'url',
		'label' => __( 'Product Vendor URL', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'commission',
		'label' => __( 'Commission', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'paypal_email',
		'label' => __( 'PayPal E-mail Address', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_name',
		'label' => __( 'Vendor Username', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_id',
		'label' => __( 'Vendor User ID', 'woocommerce-exporter' )
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
function woo_ce_override_product_vendor_field_labels( $fields = array() ) {

	$export_type = 'product_vendor';

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
add_filter( 'woo_ce_product_vendor_fields', 'woo_ce_override_product_vendor_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_product_vendor_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_product_vendor_fields();
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

// Returns a list of Product Vendor Term IDs
function woo_ce_get_product_vendors( $args = array(), $output = 'term_id' ) {

	global $export;

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	$term_taxonomy = apply_filters( 'woo_ce_product_vendor_term_taxonomy', 'wcpv_product_vendors' );
	$defaults = array(
		'orderby' => 'name',
		'order' => 'ASC',
		'hide_empty' => 0
	);
	$args = wp_parse_args( $args, $defaults );

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_product_vendors_args', $args );

	$product_vendors = get_terms( $term_taxonomy, $args );
	if( !empty( $product_vendors ) && is_wp_error( $product_vendors ) == false ) {
		if( $output == 'term_id' ) {
			$vendor_ids = array();
			foreach( $product_vendors as $key => $product_vendor )
				$vendor_ids[] = $product_vendor->term_id;
			// Only populate the $export Global if it is an export
			if( isset( $export ) )
				$export->total_rows = count( $vendor_ids );
			unset( $product_vendors, $product_vendor );
			return $vendor_ids;
		} else if( $output == 'full' ) {
			return $product_vendors;
		}
	}

}

function woo_ce_export_dataset_override_product_vendor( $output = null, $export_type = null ) {

	global $export;

	if( $product_vendors = woo_ce_get_product_vendors( $export->args ) ) {
		$export->total_rows = count( $product_vendors );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $product_vendors as $product_vendor ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_product_vendor_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					if( apply_filters( 'woo_ce_export_xml_product_vendor_node_id_attribute', true ) )
						$child->addAttribute( 'id', ( isset( $product_vendor ) ? $product_vendor : '' ) );
					$product_vendor = woo_ce_get_product_vendor_data( $product_vendor, $export->args, array_keys( $export->fields ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $product_vendor->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $product_vendor->$field ) )
									$child->addChild( apply_filters( 'woo_ce_export_xml_product_vendor_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $product_vendor->$field ) ) );
								else
									$child->addChild( apply_filters( 'woo_ce_export_xml_product_vendor_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $product_vendor->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			foreach( $product_vendors as $key => $product_vendor )
				$product_vendors[$key] = woo_ce_get_product_vendor_data( $product_vendor, $export->args, array_keys( $export->fields ) );
			$output = $product_vendors;
		}
		unset( $product_vendors, $product_vendor );
	}
	return $output;

}

function woo_ce_export_dataset_multisite_override_product_vendor( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			if( $product_vendors = woo_ce_get_product_vendors( $export->args ) ) {
				$export->total_rows = count( $product_vendors );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $product_vendors as $product_vendor ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_product_vendor_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							if( apply_filters( 'woo_ce_export_xml_product_vendor_node_id_attribute', true ) )
								$child->addAttribute( 'id', ( isset( $product_vendor ) ? $product_vendor : '' ) );
							$product_vendor = woo_ce_get_product_vendor_data( $product_vendor, $export->args, array_keys( $export->fields ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $product_vendor->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $product_vendor->$field ) )
											$child->addChild( apply_filters( 'woo_ce_export_xml_product_vendor_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $product_vendor->$field ) ) );
										else
											$child->addChild( apply_filters( 'woo_ce_export_xml_product_vendor_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $product_vendor->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					foreach( $product_vendors as $key => $product_vendor )
						$product_vendors[$key] = woo_ce_get_product_vendor_data( $product_vendor, $export->args, array_keys( $export->fields ) );
					if( is_null( $output ) )
						$output = $product_vendors;
					else
						$output = array_merge( $output, $product_vendors );
				}
				unset( $product_vendors, $product_vendor );
			}
			restore_current_blog();
		}
	}
	return $output;

}

function woo_ce_get_product_vendor_data( $vendor_id = 0, $args = array() ) {

	$defaults = array();
	$args = wp_parse_args( $args, $defaults );

	$product_vendor = new stdClass;

	// Allow Plugin/Theme authors to add support for additional Product columns
	$product_vendor = apply_filters( 'woo_ce_product_vendor', $product_vendor, $vendor_id );

	return $product_vendor;

}

function woo_ce_get_product_assoc_product_vendors( $product_id = 0, $parent_id = 0, $return = 'name' ) {

	global $export;

	$output = '';

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	$term_taxonomy = apply_filters( 'woo_ce_product_vendor_term_taxonomy', 'wcpv_product_vendors' );

	// Return Product Vendors of Parent if this is a Variation
	if( $parent_id )
		$product_id = $parent_id;
	if( $product_id )
		$vendors = wp_get_object_terms( $product_id, $term_taxonomy );
	if( !empty( $vendors ) && is_wp_error( $vendors ) == false ) {
		$size = count( $vendors );
		for( $i = 0; $i < $size; $i++ ) {
			if( $return == 'term_id' ) {
				$output .= $vendors[$i]->term_id . $export->category_separator;
			} else if( $return == 'name' ) {
				if( $vendor = get_term( $vendors[$i]->term_id, $term_taxonomy ) )
					$output .= $vendor->name . $export->category_separator;
			}
		}
		unset( $vendors, $vendor );
		$output = substr( $output, 0, -1 );
	}
	return $output;

}

function woo_ce_get_product_assoc_product_vendor_commission( $product_id = 0, $vendor_ids = array() ) {

	global $export;

	$output = '';
	$product_commission = get_post_meta( $product_id, '_wcpv_product_commission', true );
	if( !empty( $product_commission ) ) {
		$output = $product_commission;
	} else {
		if( !empty( $vendor_ids ) ) {
			// Loop through each Vendor
			$size = count( $vendor_ids );
			if( $size == 1 )
				$vendor_ids = array( $vendor_ids );
			for( $i = 0; $i < $size; $i++ ) {
				// Use get_commission_parent() as default and use Post meta as fall-back
				$output .= ( function_exists( 'get_commission_percent' ) ? get_commission_percent( $product_id, $vendor_ids[$i] ) : get_post_meta( $product_id, '_product_vendors_commission', true ) ) . $export->category_separator;
			}
			$output = substr( $output, 0, -1 );
		}
	}
	return $output;

}

function woo_ce_format_product_vendor_users( $users = null, $return = 'user_login' ) {

	global $export;

	$output = '';
	if( !function_exists( 'get_vendor' ) ) {
		$users = explode( ',', $users );
		if( !empty( $users ) ) {
			foreach( $users as $key => $user ) {
				$user_data = get_userdata( $user );
				if( $user_data !== false )
					$users[$key] = $user_data->data;
			}
		}
	}

	if( !empty( $users ) ) {
		foreach( $users as $user ) {
			if( is_object( $user ) ) {
				if( $return == 'ID' )
					$output .= $user->ID . $export->category_separator;
				else if( $return == 'user_login' )
					$output .= $user->user_login . $export->category_separator;
			}
		}
		$output = substr( $output, 0, -1 );
	}
	return $output;

}
?>