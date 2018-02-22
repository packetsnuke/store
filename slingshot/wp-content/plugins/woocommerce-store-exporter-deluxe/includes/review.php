<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_review_count' ) ) {
		function woo_ce_get_export_type_review_count() {

			$count = 0;
			$post_type = apply_filters( 'woo_ce_get_export_type_review_count_post_types', array( 'product', 'product_variation' ) );

			// Override for WordPress MultiSite
			if( woo_ce_is_network_admin() ) {
				$sites = wp_get_sites();
				foreach( $sites as $site ) {
					switch_to_blog( $site['blog_id'] );
					$args = array(
						'count' => true,
						'status' => 'all',
						'post_status' => 'publish',
						'post_type' => $post_type
					);
					$reviews = get_comments( $args );
					$count += absint( $reviews );
					restore_current_blog();
				}
				return $count;
			}

			// Check if the existing Transient exists
			$cached = get_transient( WOO_CD_PREFIX . '_review_count' );
			if( $cached == false ) {
				$args = array(
					'count' => true,
					'status' => 'all',
					'post_status' => 'publish',
					'post_type' => $post_type
				);
				$reviews = get_comments( $args );
				$count = absint( $reviews );
				set_transient( WOO_CD_PREFIX . '_review_count', $count, HOUR_IN_SECONDS );
			} else {
				$count = $cached;
			}
			return $count;

		}
	}

	function woo_ce_review_scheduled_export_save( $post_ID = 0 ) {

		$auto_review_date = sanitize_text_field( $_POST['review_dates_filter'] );
		update_post_meta( $post_ID, '_filter_review_date', $auto_review_date );
		update_post_meta( $post_ID, '_filter_review_orderby', ( isset( $_POST['review_filter_orderby'] ) ? sanitize_text_field( $_POST['review_filter_orderby'] ) : false ) );

	}
	add_action( 'woo_ce_extend_scheduled_export_save', 'woo_ce_review_scheduled_export_save' );

	function woo_ce_review_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Review Export Type
		if( $export_type <> 'review' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'review_orderby' => ( isset( $_POST['review_orderby'] ) ? sanitize_text_field( $_POST['review_orderby'] ) : false ),
			'review_order' => ( isset( $_POST['review_order'] ) ? sanitize_text_field( $_POST['review_order'] ) : false )
		);
		$args = wp_parse_args( $args, $defaults );

		// Save dataset export specific options
		if( $args['review_orderby'] <> woo_ce_get_option( 'review_orderby' ) )
			woo_ce_update_option( 'review_orderby', $args['review_orderby'] );
		if( $args['review_order'] <> woo_ce_get_option( 'review_order' ) )
			woo_ce_update_option( 'review_order', $args['review_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_review_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

function woo_ce_cron_review_dataset_args( $args, $export_type = '', $is_scheduled = 0 ) {

	// Check if we're dealing with the Review Export Type
	if( $export_type <> 'review' )
		return $args;

	$review_orderby = false;
	$review_dates_filter = false;

	if( $is_scheduled ) {
		$scheduled_export = ( $is_scheduled ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );

		$review_orderby = get_post_meta( $scheduled_export, '_filter_review_orderby', true );
		$review_dates_filter = get_post_meta( $scheduled_export, '_filter_review_date', true );
	}

	// Merge in the form data for this dataset
	$defaults = array(
		'review_orderby' => ( !empty( $review_orderby ) ? $review_orderby : false ),
		'review_dates_filter' => ( !empty( $review_dates_filter ) ? $review_dates_filter : false )
	);
	$args = wp_parse_args( $args, $defaults );

	return $args;

}
add_filter( 'woo_ce_extend_cron_dataset_args', 'woo_ce_cron_review_dataset_args', 10, 3 );

// Returns a list of Review export columns
function woo_ce_get_review_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'review';

	$fields = array();
	$fields[] = array(
		'name' => 'comment_ID',
		'label' => __( 'Review ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'comment_post_ID',
		'label' => __( 'Product ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_name',
		'label' => __( 'Product Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'comment_author',
		'label' => __( 'Reviewer', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'comment_author_email',
		'label' => __( 'E-mail', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'comment_content',
		'label' => __( 'Content', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'comment_date',
		'label' => __( 'Review Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'rating',
		'label' => __( 'Rating', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'verified',
		'label' => __( 'Verified', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'comment_author_IP',
		'label' => __( 'IP Address', 'woocommerce-exporter' )
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
function woo_ce_override_review_field_labels( $fields = array() ) {

	$export_type = 'review';

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
add_filter( 'woo_ce_review_fields', 'woo_ce_override_review_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_review_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_review_fields();
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

// Returns a list of WooCommerce Review IDs to export process
function woo_ce_get_reviews( $args = array() ) {

	global $export;

	$limit_volume = -1;
	$offset = 0;
	$orderby = 'ID';
	$order = 'ASC';
	if( $args ) {
		$limit_volume = ( isset( $args['limit_volume'] ) ? $args['limit_volume'] : false );
		$offset = ( isset( $args['offset'] ) ? $args['offset'] : false );
		if( isset( $args['review_orderby'] ) )
			$orderby = $args['review_orderby'];
		if( isset( $args['review_order'] ) )
			$order = $args['review_order'];
		$review_dates_filter = ( isset( $args['review_dates_filter'] ) ? $args['review_dates_filter'] : false );
	}
	$post_type = apply_filters( 'woo_ce_get_reviews_post_type', array( 'product' ) );

	$args = array(
		'status' => 'all',
		'post_status' => 'publish',
		'post_type' => $post_type,
		'orderby' => $orderby,
		'order' => $order,
		'fields' => 'ids'
	);

	$reviews = array();

	// Check if we are filtering Orders by Last Export
	if( $review_dates_filter == 'last_export' ) {
		$args['meta_query'][] = array(
			'key' => '_woo_cd_exported',
			'value' => 1,
			'compare' => 'NOT EXISTS'
		);
	}

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_reviews_args', $args );

	$review_ids = new WP_Comment_Query( $args );
	if( $review_ids->comments ) {
		foreach( $review_ids->comments as $review_id ) {
			if( isset( $review_id ) ) {
				$reviews[] = $review_id;
				// Mark this Review as exported if Since last export Date filter is used
				if( $review_dates_filter == 'last_export' && !empty( $review_id ) ) {
					update_comment_meta( $review_id, '_woo_cd_exported', 1 );
				}
			}
		}
	}
	return $reviews;

}

function woo_ce_get_review_data( $review_id = 0, $args = array(), $fields = array() ) {

	$review = get_comment( $review_id );

	add_filter( 'the_title', 'woo_ce_get_product_title', 10, 2 );
	$review->product_name = woo_ce_format_post_title( get_the_title( $review->comment_post_ID ) );
	remove_filter( 'the_title', 'woo_ce_get_product_title' );
	$review->comment_content = woo_ce_format_description_excerpt( $review->comment_content );
	$review->comment_date = woo_ce_format_date( $review->comment_date );
	$review->rating = get_comment_meta( $review_id, 'rating', true );
	$review->verified = get_comment_meta( $review_id, 'verified', true );

	// Allow Plugin/Theme authors to add support for additional Review columns
	$review = apply_filters( 'woo_ce_review_item', $review, $review_id );

	// Trim back the Review just to requested export fields
	if( !empty( $fields ) ) {
		$fields = array_merge( $fields, array( 'id', 'ID', 'post_parent', 'filter' ) );
		if( !empty( $review ) ) {
			foreach( $review as $key => $data ) {
				if( !in_array( $key, $fields ) )
					unset( $review->$key );
			}
		}
	}

	return $review;

}

function woo_ce_export_dataset_override_review( $output = null, $export_type = null ) {

	global $export;

	if( $reviews = woo_ce_get_reviews( $export->args ) ) {
		$export->total_rows = count( $reviews );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $reviews as $review ) {
					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_review_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					if( apply_filters( 'woo_ce_export_xml_product_review_id_attribute', true ) )
						$child->addAttribute( 'id', ( isset( $review->comment_id ) ? $review->comment_id : '' ) );
					$review = woo_ce_get_review_data( $review, $export->args, array_keys( $export->fields ) );
					foreach( array_keys( $export->fields ) as $key => $field ) {
						if( isset( $review->$field ) ) {
							if( !is_array( $field ) ) {
								if( woo_ce_is_xml_cdata( $review->$field ) )
									$child->addChild( apply_filters( 'woo_ce_export_xml_review_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $review->$field ) ) );
								else
									$child->addChild( apply_filters( 'woo_ce_export_xml_review_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $review->$field ) ) );
							}
						}
					}
				}
			}
		} else {
			// PHPExcel export
			foreach( $reviews as $key => $review )
				$reviews[$key] = woo_ce_get_review_data( $review, $export->args, array_keys( $export->fields ) );
			$output = $reviews;
		}
		unset( $reviews, $review );
	}
	return $output;

}

function woo_ce_export_dataset_multisite_override_review( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			if( $reviews = woo_ce_get_reviews( $export->args ) ) {
				$export->total_rows = count( $reviews );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $reviews as $review ) {
							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_review_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							if( apply_filters( 'woo_ce_export_xml_product_review_id_attribute', true ) )
								$child->addAttribute( 'id', ( isset( $review->comment_id ) ? $review->comment_id : '' ) );
							$review = woo_ce_get_review_data( $review, $export->args, array_keys( $export->fields ) );
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $review->$field ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $review->$field ) )
											$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $review->$field ) ) );
										else
											$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $review->$field ) ) );
									}
								}
							}
						}
					}
				} else {
					// PHPExcel export
					foreach( $reviews as $key => $review )
						$reviews[$key] = woo_ce_get_review_data( $review, $export->args, array_keys( $export->fields ) );
					if( is_null( $output ) )
						$output = $reviews;
					else
						$output = array_merge( $output, $reviews );
				}
				unset( $reviews, $review );
			}
			restore_current_blog();
		}
	}
	return $output;

}
?>