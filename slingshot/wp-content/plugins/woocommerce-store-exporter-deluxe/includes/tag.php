<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_tag_count' ) ) {
		function woo_ce_get_export_type_tag_count() {

			$count = 0;
			$term_taxonomy = 'product_tag';

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
			$cached = get_transient( WOO_CD_PREFIX . '_tag_count' );
			if( $cached == false ) {
				if( taxonomy_exists( $term_taxonomy ) )
					$count = wp_count_terms( $term_taxonomy );
				set_transient( WOO_CD_PREFIX . '_tag_count', $count, HOUR_IN_SECONDS );
			} else {
				$count = $cached;
			}
			return $count;

		}
	}

	function woo_ce_tag_scheduled_export_save( $post_ID = 0 ) {

		update_post_meta( $post_ID, '_filter_tag_orderby', ( isset( $_POST['tag_filter_orderby'] ) ? sanitize_text_field( $_POST['tag_filter_orderby'] ) : false ) );

	}
	add_action( 'woo_ce_extend_scheduled_export_save', 'woo_ce_tag_scheduled_export_save' );

	function woo_ce_tag_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Tag Export Type
		if( $export_type <> 'tag' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'tag_language' => ( isset( $_POST['tag_filter_language'] ) ? array_map( 'sanitize_text_field', $_POST['tag_filter_language'] ) : false ),
			'tag_orderby' => ( isset( $_POST['tag_orderby'] ) ? sanitize_text_field( $_POST['tag_orderby'] ) : false ),
			'tag_order' => ( isset( $_POST['tag_order'] ) ? sanitize_text_field( $_POST['tag_order'] ) : false )
		);
		$args = wp_parse_args( $args, $defaults );

		// Save dataset export specific options
		// Language
		if( $args['tag_orderby'] <> woo_ce_get_option( 'tag_orderby' ) )
			woo_ce_update_option( 'tag_orderby', $args['tag_orderby'] );
		if( $args['tag_order'] <> woo_ce_get_option( 'tag_order' ) )
			woo_ce_update_option( 'tag_order', $args['tag_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_tag_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

// Returns a list of Product Tag export columns
function woo_ce_get_tag_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'tag';

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
		'name' => 'count',
		'label' => __( 'Count', 'woocommerce-exporter' )
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
function woo_ce_override_tag_field_labels( $fields = array() ) {

	$export_type = 'tag';

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
add_filter( 'woo_ce_tag_fields', 'woo_ce_override_tag_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_tag_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_tag_fields();
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			if( $fields[$i]['name'] == $name ) {
				switch( $format ) {

					case 'name':
						$output = $fields[$i]['label'];

						// Allow Plugin/Theme authors to easily override export field labels
						$output = apply_filters( 'woo_ce_get_product_tag_field_label', $output );
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

// Returns a list of WooCommerce Product Tags to export process
function woo_ce_get_product_tags( $args = array() ) {

	$term_taxonomy = 'product_tag';
	$defaults = array(
		'orderby' => 'name',
		'order' => 'ASC',
		'hide_empty' => 0
	);
	$args = wp_parse_args( $args, $defaults );

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_product_tags_args', $args );

	$tags = get_terms( $term_taxonomy, $args );
	if( !empty( $tags ) && is_wp_error( $tags ) == false ) {
		$size = count( $tags );
		for( $i = 0; $i < $size; $i++ ) {
			$tags[$i]->description = woo_ce_format_description_excerpt( $tags[$i]->description );
			$tags[$i]->disabled = 0;
			if( $tags[$i]->count == 0 )
				$tags[$i]->disabled = 1;

			// Allow Plugin/Theme authors to add support for additional Tag columns
			$tags[$i] = apply_filters( 'woo_ce_tag_item', $tags[$i] );

		}
		return $tags;
	}

}

function woo_ce_export_dataset_override_tag( $output = null, $export_type = null ) {

	global $export;

	$args = array(
		'orderby' => ( isset( $export->args['tag_orderby'] ) ? $export->args['tag_orderby'] : 'ID' ),
		'order' => ( isset( $export->args['tag_order'] ) ? $export->args['tag_order'] : 'ASC' ),
	);
	if( $tags = woo_ce_get_product_tags( $args ) ) {
		$export->total_rows = count( $tags );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $tags as $tag ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_tag_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					if( apply_filters( 'woo_ce_export_xml_tag_id_attribute', true ) )
						$child->addAttribute( 'id', ( isset( $tag->term_id ) ? $tag->term_id : '' ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $tag->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $tag->$field ) )
									$child->addChild( apply_filters( 'woo_ce_export_xml_tag_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $tag->$field ) ) );
								else
									$child->addChild( apply_filters( 'woo_ce_export_xml_tag_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $tag->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			$output = $tags;
		}
		unset( $tags, $tag );
	}
	return $output;

}

function woo_ce_export_dataset_multisite_override_tag( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			$args = array(
				'orderby' => ( isset( $export->args['tag_orderby'] ) ? $export->args['tag_orderby'] : 'ID' ),
				'order' => ( isset( $export->args['tag_order'] ) ? $export->args['tag_order'] : 'ASC' ),
			);
			if( $tags = woo_ce_get_product_tags( $args ) ) {
				$export->total_rows = count( $tags );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $tags as $tag ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_tag_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							if( apply_filters( 'woo_ce_export_xml_tag_id_attribute', true ) )
								$child->addAttribute( 'id', ( isset( $tag->term_id ) ? $tag->term_id : '' ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $tag->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $tag->$field ) )
											$child->addChild( apply_filters( 'woo_ce_export_xml_tag_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $tag->$field ) ) );
										else
											$child->addChild( apply_filters( 'woo_ce_export_xml_tag_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $tag->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					if( is_null( $output ) )
						$output = $tags;
					else
						$output = array_merge( $output, $tags );
				}
				unset( $tags, $tag );
			}
			restore_current_blog();
		}
	}
	return $output;

}
?>