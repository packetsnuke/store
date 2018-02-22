<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_brand_count' ) ) {
		function woo_ce_get_export_type_brand_count( $count = 0, $export_type = '', $args ) {

			if( $export_type <> 'brand' )
				return $count;

			$count = 0;
			$term_taxonomy = apply_filters( 'woo_ce_brand_term_taxonomy', 'product_brand' );

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
			$cached = get_transient( WOO_CD_PREFIX . '_brand_count' );
			if( $cached == false ) {
				if( taxonomy_exists( $term_taxonomy ) )
					$count = wp_count_terms( $term_taxonomy );
				set_transient( WOO_CD_PREFIX . '_brand_count', $count, HOUR_IN_SECONDS );
			} else {
				$count = $cached;
			}
			return $count;

		}
		add_filter( 'woo_ce_get_export_type_count', 'woo_ce_get_export_type_brand_count', 10, 3 );
	}

	function woo_ce_brand_scheduled_export_save( $post_ID = 0 ) {

		update_post_meta( $post_ID, '_filter_brand_orderby', ( isset( $_POST['brand_filter_orderby'] ) ? sanitize_text_field( $_POST['brand_filter_orderby'] ) : false ) );

	}
	add_action( 'woo_ce_extend_scheduled_export_save', 'woo_ce_brand_scheduled_export_save' );

	function woo_ce_brand_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Brand Export Type
		if( $export_type <> 'brand' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'brand_orderby' => ( isset( $_POST['brand_orderby'] ) ? sanitize_text_field( $_POST['brand_orderby'] ) : false ),
			'brand_order' => ( isset( $_POST['brand_order'] ) ? sanitize_text_field( $_POST['brand_order'] ) : false )
		);
		$args = wp_parse_args( $args, $defaults );

		// Save dataset export specific options
		if( $args['brand_orderby'] <> woo_ce_get_option( 'brand_orderby' ) )
			woo_ce_update_option( 'brand_orderby', $args['brand_orderby'] );
		if( $args['brand_order'] <> woo_ce_get_option( 'brand_order' ) )
			woo_ce_update_option( 'brand_order', $args['brand_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_brand_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

// Returns a list of Brand export columns
function woo_ce_get_brand_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'brand';

	$fields = array();
	$fields[] = array(
		'name' => 'term_id',
		'label' => __( 'Term ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'name',
		'label' => __( 'Brand Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'slug',
		'label' => __( 'Brand Slug', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'parent_id',
		'label' => __( 'Parent Term ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'description',
		'label' => __( 'Brand Description', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'image',
		'label' => __( 'Brand Image', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'image_embed',
		'label' => __( 'Brand Image (Embed)', 'woocommerce-exporter' )
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
function woo_ce_override_brand_field_labels( $fields = array() ) {

	$export_type = 'brand';

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
add_filter( 'woo_ce_brand_fields', 'woo_ce_override_brand_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_brand_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_brand_fields();
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

// Returns a list of WooCommerce Product Brands to export process
function woo_ce_get_product_brands( $args = array() ) {

	global $export;

	$term_taxonomy = apply_filters( 'woo_ce_brand_term_taxonomy', 'product_brand' );
	$defaults = array(
		'orderby' => 'name',
		'order' => 'ASC',
		'hide_empty' => 0
	);
	$args = wp_parse_args( $args, $defaults );

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_product_brands_args', $args );

	$brands = get_terms( $term_taxonomy, $args );
	if( !empty( $brands ) && is_wp_error( $brands ) == false ) {
		foreach( $brands as $key => $brand ) {
			$brands[$key]->description = woo_ce_format_description_excerpt( $brand->description );
			$brands[$key]->parent_name = '';
			if( $brands[$key]->parent_id = $brand->parent ) {
				if( $parent_brand = get_term( $brands[$key]->parent_id, $term_taxonomy ) ) {
					$brands[$key]->parent_name = $parent_brand->name;
				}
				unset( $parent_brand );
			} else {
				$brands[$key]->parent_id = '';
			}
			$brands[$key]->image = ( function_exists( 'get_brand_thumbnail_url' ) ? get_brand_thumbnail_url( $brand->term_id ) : false );
			if( !empty( $brands[$key]->image ) ) {
				if( isset( $export->export_format ) && $export->export_format == 'xlsx' ) {
					// Override for the image embed thumbnail size; use registered WordPress image size names
					$thumbnail_size = apply_filters( 'woo_ce_override_embed_thumbnail_size', 'shop_thumbnail' );
					$brands[$key]->image_embed = woo_ce_get_category_thumbnail_path( $brand->term_id, $thumbnail_size );
				}
			}

			// Allow Plugin/Theme authors to add support for additional Brand columns
			$brands[$key] = apply_filters( 'woo_ce_brand_item', $brands[$key] );

		}
		return $brands;
	}

}

function woo_ce_export_dataset_override_brand( $output = null, $export_type = null ) {

	global $export;

	$args = array(
		'orderby' => ( isset( $export->args['brand_orderby'] ) ? $export->args['brand_orderby'] : 'ID' ),
		'order' => ( isset( $export->args['brand_order'] ) ? $export->args['brand_order'] : 'ASC' ),
	);
	if( $brands = woo_ce_get_product_brands( $args ) ) {
		$export->total_rows = count( $brands );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $brands as $brand ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_brand_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					if( apply_filters( 'woo_ce_export_xml_brand_node_id_attribute', true ) )
						$child->addAttribute( 'id', ( isset( $brand->term_id ) ? $brand->term_id : '' ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $brand->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $brand->$field ) )
									$child->addChild( apply_filters( 'woo_ce_export_xml_brand_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $brand->$field ) ) );
								else
									$child->addChild( apply_filters( 'woo_ce_export_xml_brand_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $brand->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			$output = $brands;
		}
		unset( $brands, $brand );
	}
	return $output;

}

function woo_ce_export_dataset_multisite_override_brand( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			$brand_args = array(
				'orderby' => ( isset( $export->args['brand_orderby'] ) ? $export->args['brand_orderby'] : 'ID' ),
				'order' => ( isset( $export->args['brand_order'] ) ? $export->args['brand_order'] : 'ASC' ),
			);
			if( $brands = woo_ce_get_product_brands( $brand_args ) ) {
				$export->total_rows = count( $brands );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $brands as $brand ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_brand_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							if( apply_filters( 'woo_ce_export_xml_brand_node_id_attribute', true ) )
								$child->addAttribute( 'id', ( isset( $brand->term_id ) ? $brand->term_id : '' ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $brand->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $brand->$field ) )
											$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $brand->$field ) ) );
										else
											$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $brand->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					if( is_null( $output ) )
						$output = $brands;
					else
						$output = array_merge( $output, $brands );
				}
				unset( $brands, $brand );
			}
			restore_current_blog();
		}
	}
	return $output;

}

// Return whether the Brands Term Taxonomy is in use
function woo_ce_detect_product_brands() {

	if( 
		woo_ce_detect_export_plugin( 'wc_brands' ) || 
		woo_ce_detect_export_plugin( 'woocommerce_brands' ) || 
		woo_ce_detect_export_plugin( 'yith_brands_pro' ) || 
		taxonomy_exists( apply_filters( 'woo_ce_brand_term_taxonomy', 'product_brand' ) )
	)
		return true;

}
?>