<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_shipping_class_count' ) ) {
		function woo_ce_get_export_type_shipping_class_count() {

			$count = 0;
			$term_taxonomy = 'product_shipping_class';

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
			$cached = get_transient( WOO_CD_PREFIX . '_shipping_class_count' );
			if( $cached == false ) {
				if( taxonomy_exists( $term_taxonomy ) )
					$count = wp_count_terms( $term_taxonomy );
				set_transient( WOO_CD_PREFIX . '_shipping_class_count', $count, HOUR_IN_SECONDS );
			} else {
				$count = $cached;
			}
			return $count;

		}
	}

	function woo_ce_shipping_class_scheduled_export_save( $post_ID = 0 ) {

		update_post_meta( $post_ID, '_filter_shipping_class_orderby', ( isset( $_POST['shipping_class_filter_orderby'] ) ? sanitize_text_field( $_POST['shipping_class_filter_orderby'] ) : false ) );

	}
	add_action( 'woo_ce_extend_scheduled_export_save', 'woo_ce_shipping_class_scheduled_export_save' );

	function woo_ce_shipping_class_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Shipping Class Export Type
		if( $export_type <> 'shipping_class' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'shipping_class_orderby' => ( isset( $_POST['shipping_class_orderby'] ) ? sanitize_text_field( $_POST['shipping_class_orderby'] ) : false ),
			'shipping_class_order' => ( isset( $_POST['shipping_class_order'] ) ? sanitize_text_field( $_POST['shipping_class_order'] ) : false )
		);
		$args = wp_parse_args( $args, $defaults );

		// Save dataset export specific options
		if( $args['shipping_class_orderby'] <> woo_ce_get_option( 'shipping_class_orderby' ) )
			woo_ce_update_option( 'shipping_class_orderby', $args['shipping_class_orderby'] );
		if( $args['shipping_class_order'] <> woo_ce_get_option( 'shipping_class_order' ) )
			woo_ce_update_option( 'shipping_class_order', $args['shipping_class_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_shipping_class_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

// Returns a list of Shipping Class export columns
function woo_ce_get_shipping_class_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'shipping_class';

	$fields = array();
	$fields[] = array(
		'name' => 'term_id',
		'label' => __( 'Term ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'name',
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
function woo_ce_override_shipping_class_field_labels( $fields = array() ) {

	$export_type = 'shipping_class';

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
add_filter( 'woo_ce_shipping_class_fields', 'woo_ce_override_shipping_class_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_shipping_class_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_shipping_class_fields();
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

// Returns a list of WooCommerce Shipping Classes to export process
function woo_ce_get_shipping_classes( $args = array() ) {

	$term_taxonomy = 'product_shipping_class';
	$defaults = array(
		'orderby' => 'ID',
		'order' => 'ASC',
		'hide_empty' => 0
	);
	$args = wp_parse_args( $args, $defaults );

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_shipping_clases_args', $args );

	$shipping_classes = get_terms( $term_taxonomy, $args );
	if( !empty( $shipping_classes ) && is_wp_error( $shipping_classes ) == false ) {
		$size = count( $shipping_classes );
		for( $i = 0; $i < $size; $i++ ) {
			$shipping_classes[$i]->disabled = 0;
			if( $shipping_classes[$i]->count == 0 )
				$shipping_classes[$i]->disabled = 1;
		}

		// Allow Plugin/Theme authors to add support for additional Shipping Class columns
		$shipping_classes = apply_filters( 'woo_ce_shipping_class_item', $shipping_classes );

		return $shipping_classes;
	}

}

function woo_ce_export_dataset_override_shipping_class( $output = null, $export_type = null ) {

	global $export;

	if( $shipping_classes = woo_ce_get_shipping_classes( $export->args ) ) {
		$export->total_rows = count( $shipping_classes );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $shipping_classes as $shipping_class ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_shipping_class_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					if( apply_filters( 'woo_ce_export_xml_shipping_class_id_attribute', true ) )
						$child->addAttribute( 'id', ( isset( $shipping_class->term_id ) ? $shipping_class->term_id : '' ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $shipping_class->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $shipping_class->$field ) )
									$child->addChild( apply_filters( 'woo_ce_export_xml_shipping_class_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $shipping_class->$field ) ) );
								else
									$child->addChild( apply_filters( 'woo_ce_export_xml_shipping_class_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $shipping_class->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			$output = $shipping_classes;
		}
		unset( $shipping_classes, $shipping_class );
	}
	return $output;

}

function woo_ce_export_dataset_multisite_override_shipping_class( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			if( $shipping_classes = woo_ce_get_shipping_classes( $export->args ) ) {
				$export->total_rows = count( $shipping_classes );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $shipping_classes as $shipping_class ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_shipping_class_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							if( apply_filters( 'woo_ce_export_xml_shipping_class_id_attribute', true ) )
								$child->addAttribute( 'id', ( isset( $shipping_class->term_id ) ? $shipping_class->term_id : '' ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $shipping_class->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $shipping_class->$field ) )
											$child->addChild( apply_filters( 'woo_ce_export_xml_shipping_class_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $shipping_class->$field ) ) );
										else
											$child->addChild( apply_filters( 'woo_ce_export_xml_shipping_class_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $shipping_class->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					if( is_null( $output ) )
						$output = $shipping_classes;
					else
						$output = array_merge( $output, $shipping_classes );
				}
				unset( $shipping_classes, $shipping_class );
			}
			restore_current_blog();
		}
	}
	return $output;

}
?>