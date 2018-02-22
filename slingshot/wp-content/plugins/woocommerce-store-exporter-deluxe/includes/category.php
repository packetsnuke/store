<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_category_count' ) ) {
		function woo_ce_get_export_type_category_count() {

			$count = 0;
			$term_taxonomy = 'product_cat';

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
			$cached = get_transient( WOO_CD_PREFIX . '_category_count' );
			if( $cached == false ) {
				if( taxonomy_exists( $term_taxonomy ) )
					$count = wp_count_terms( $term_taxonomy );
				set_transient( WOO_CD_PREFIX . '_category_count', $count, HOUR_IN_SECONDS );
			} else {
				$count = $cached;
			}
			return $count;

		}
	}

	function woo_ce_category_scheduled_export_save( $post_ID = 0 ) {

		update_post_meta( $post_ID, '_filter_category_orderby', ( isset( $_POST['category_filter_orderby'] ) ? sanitize_text_field( $_POST['category_filter_orderby'] ) : false ) );

	}
	add_action( 'woo_ce_extend_scheduled_export_save', 'woo_ce_category_scheduled_export_save' );

	function woo_ce_category_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Category Export Type
		if( $export_type <> 'category' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'category_language' => ( isset( $_POST['category_filter_language'] ) ? array_map( 'sanitize_text_field', $_POST['category_filter_language'] ) : false ),
			'category_orderby' => ( isset( $_POST['category_orderby'] ) ? sanitize_text_field( $_POST['category_orderby'] ) : false ),
			'category_order' => ( isset( $_POST['category_order'] ) ? sanitize_text_field( $_POST['category_order'] ) : false )
		);
		$args = wp_parse_args( $args, $defaults );

		// Save dataset export specific options
		// Language
		if( $args['category_orderby'] <> woo_ce_get_option( 'category_orderby' ) )
			woo_ce_update_option( 'category_orderby', $args['category_orderby'] );
		if( $args['category_order'] <> woo_ce_get_option( 'category_order' ) )
			woo_ce_update_option( 'category_order', $args['category_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_category_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

// Returns a list of Category export columns
function woo_ce_get_category_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'category';

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
		'name' => 'parent_id',
		'label' => __( 'Parent Term ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category_level_1',
		'label' => __( 'Category: Level 1', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category_level_2',
		'label' => __( 'Category: Level 2', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category_level_3',
		'label' => __( 'Category: Level 3', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category_level_4',
		'label' => __( 'Category: Level 4', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category_level_5',
		'label' => __( 'Category: Level 5', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category_level_6',
		'label' => __( 'Category: Level 6', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category_level_7',
		'label' => __( 'Category: Level 7', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'description',
		'label' => __( 'Description', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'display_type',
		'label' => __( 'Display Type', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'image',
		'label' => __( 'Image', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'image_embed',
		'label' => __( 'Image (Embed)', 'woocommerce-exporter' )
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
function woo_ce_override_category_field_labels( $fields = array() ) {

	$export_type = 'category';

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
add_filter( 'woo_ce_category_fields', 'woo_ce_override_category_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_category_field( $name = null, $format = 'name' ) {

	global $export;

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_category_fields();
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_category_field() > woo_ce_get_category_fields(): ' . ( time() - $export->start_time ) ) );
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			if( $fields[$i]['name'] == $name ) {
				switch( $format ) {

					case 'name':
						$output = $fields[$i]['label'];

						// Allow Plugin/Theme authors to easily override export field labels
						$output = apply_filters( 'woo_ce_get_category_field_label', $output );
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

// Returns a list of WooCommerce Product Categories to export process
function woo_ce_get_product_categories( $args = array() ) {

	global $export;

	$term_taxonomy = 'product_cat';
	$defaults = array(
		'orderby' => 'name',
		'order' => 'ASC',
		'hide_empty' => 0
	);
	$args = wp_parse_args( $args, $defaults );

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_product_categories_args', $args );

	$categories = get_terms( $term_taxonomy, $args );
	if( !empty( $categories ) && is_wp_error( $categories ) == false ) {
		foreach( $categories as $key => $category ) {
			$categories[$key]->description = woo_ce_format_description_excerpt( $category->description );

			// Category heirachy
			$categories[$key]->parent_name = '';
			// Term
			$categories[$key]->category_level_1 = $category->name;
			if( $categories[$key]->parent_id = $category->parent ) {
				$parent_category = get_term( $categories[$key]->parent_id, $term_taxonomy );
				if( !empty( $parent_category ) && is_wp_error( $parent_category ) == false ) {
					$categories[$key]->parent_name = $parent_category->name;
					// Term > Term
					$categories[$key]->category_level_2 = $parent_category->name;
					$parent_category = get_term( $parent_category->parent, $term_taxonomy );
					if( !empty( $parent_category ) && is_wp_error( $parent_category ) == false ) {
						// Term > Term > Term
						$categories[$key]->category_level_3 = $parent_category->name;
						$parent_category = get_term( $parent_category->parent, $term_taxonomy );
						if( !empty( $parent_category ) && is_wp_error( $parent_category ) == false ) {
							// Term > Term > Term > Term
							$categories[$key]->category_level_4 = $parent_category->name;
							$parent_category = get_term( $parent_category->parent, $term_taxonomy );
							if( !empty( $parent_category ) && is_wp_error( $parent_category ) == false ) {
								// Term > Term > Term > Term > Term
								$categories[$key]->category_level_5 = $parent_category->name;
								$parent_category = get_term( $parent_category->parent, $term_taxonomy );
								if( !empty( $parent_category ) && is_wp_error( $parent_category ) == false ) {
									// Term > Term > Term > Term > Term > Term
									$categories[$key]->category_level_6 = $parent_category->name;
									$parent_category = get_term( $parent_category->parent, $term_taxonomy );
									if( !empty( $parent_category ) && is_wp_error( $parent_category ) == false ) {
										// Term > Term > Term > Term > Term > Term > Term
										$categories[$key]->category_level_7 = $parent_category->name;
									}
								}
							}
						}
					}
				}
				unset( $parent_category );
			} else {
				$categories[$key]->parent_id = '';
			}

			$categories[$key]->image = woo_ce_get_category_thumbnail_url( $category->term_id );
			if( !empty( $categories[$key]->image ) ) {
				if( isset( $export->export_format ) && $export->export_format == 'xlsx' ) {
					// Override for the image embed thumbnail size; use registered WordPress image size names
					$thumbnail_size = apply_filters( 'woo_ce_override_embed_thumbnail_size', 'shop_thumbnail' );
					$categories[$key]->image_embed = woo_ce_get_category_thumbnail_path( $category->term_id, $thumbnail_size );
				}
			}
			$categories[$key]->display_type = woo_ce_format_category_display_type( get_woocommerce_term_meta( $category->term_id, 'display_type', true ) );

			// Allow Plugin/Theme authors to add support for additional Category columns
			$categories[$key] = apply_filters( 'woo_ce_category_item', $categories[$key] );

		}
		return $categories;
	}

}

function woo_ce_get_category_data( $term_id = 0 ) {

	// Do something

}

function woo_ce_export_dataset_override_category( $output = null, $export_type = null ) {

	global $export;

	$args = array(
		'orderby' => ( isset( $export->args['category_orderby'] ) ? $export->args['category_orderby'] : 'ID' ),
		'order' => ( isset( $export->args['category_order'] ) ? $export->args['category_order'] : 'ASC' ),
	);
	if( $categories = woo_ce_get_product_categories( $args ) ) {
		$export->total_rows = count( $categories );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $categories as $category ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_category_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					if( apply_filters( 'woo_ce_export_xml_category_node_id_attribute', true ) )
						$child->addAttribute( 'id', ( isset( $category->term_id ) ? $category->term_id : '' ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $category->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $category->$field ) )
									$child->addChild( apply_filters( 'woo_ce_export_xml_brand_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $category->$field ) ) );
								else
									$child->addChild( apply_filters( 'woo_ce_export_xml_brand_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $category->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			$output = $categories;
		}
		unset( $categories, $category );
	}
	return $output;

}

function woo_ce_export_dataset_multisite_override_category( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			$args = array(
				'orderby' => ( isset( $export->args['category_orderby'] ) ? $export->args['category_orderby'] : 'ID' ),
				'order' => ( isset( $export->args['category_order'] ) ? $export->args['category_order'] : 'ASC' ),
			);
			if( $categories = woo_ce_get_product_categories( $args ) ) {
				$export->total_rows = count( $categories );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $categories as $category ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_category_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							if( apply_filters( 'woo_ce_export_xml_category_node_id_attribute', true ) )
								$child->addAttribute( 'id', ( isset( $category->term_id ) ? $category->term_id : '' ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $category->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $category->$field ) )
											$child->addChild( apply_filters( 'woo_ce_export_xml_category_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $category->$field ) ) );
										else
											$child->addChild( apply_filters( 'woo_ce_export_xml_category_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $category->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					if( is_null( $output ) )
						$output = $categories;
					else
						$output = array_merge( $output, $categories );
				}
				unset( $categories, $category );
			}
			restore_current_blog();
		}
	}
	return $output;

}

function woo_ce_get_category_thumbnail_url( $category_id = 0, $size = 'full' ) {

	if( $thumbnail_id = get_woocommerce_term_meta( $category_id, 'thumbnail_id', true ) ) {
		$image_attributes = wp_get_attachment_image_src( $thumbnail_id, $size );
		if( is_array( $image_attributes ) )
			return current( $image_attributes );
	}

}

function woo_ce_get_category_thumbnail_path( $category_id = 0, $thumbnail_size = 'full' ) {

	if( $image_id = get_woocommerce_term_meta( $category_id, 'thumbnail_id', true ) ) {
		if( $thumbnail_size <> 'full' ) {
			$upload_dir = wp_upload_dir();
			if( $metadata = wp_get_attachment_metadata( $image_id ) ) {
				if( isset( $metadata['sizes'][$thumbnail_size] ) && $metadata['sizes'][$thumbnail_size]['file'] ) {
					$image_path = pathinfo( $metadata['file'] );
					// Override for using relative image embed filepath
					if( !file_exists( trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'] ) || apply_filters( 'woo_ce_override_image_embed_relative_path', false ) )
						return trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'];
					else
						return trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'];
				}
			}
			unset( $image_id, $metadata, $thumbnail_size, $image_path );
		} else {
			return get_attached_file( $image_id );
		}
	}

}

function woo_ce_format_category_display_type( $display_type = '' ) {

	$output = $display_type;
	if( !empty( $display_type ) ) {
		$output = ucfirst( $display_type );
	}
	return $output;

}
?>