<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	if( !function_exists( 'woo_ce_get_export_type_order_count' ) ) {
		function woo_ce_get_export_type_order_count() {

			$count = 0;
			$post_type = 'shop_order';
			$woocommerce_version = woo_get_woo_version();
			// Check if this is a WooCommerce 2.2+ instance (new Post Status)
			if( version_compare( $woocommerce_version, '2.2' ) >= 0 )
				$post_status = ( function_exists( 'wc_get_order_statuses' ) ? apply_filters( 'woo_ce_order_post_status', array_keys( wc_get_order_statuses() ) ) : 'any' );
			else
				$post_status = apply_filters( 'woo_ce_order_post_status', woo_ce_post_statuses() );

			// Override for WordPress MultiSite
			if( woo_ce_is_network_admin() ) {
				$sites = wp_get_sites();
				foreach( $sites as $site ) {
					switch_to_blog( $site['blog_id'] );
					$args = array(
						'post_type' => $post_type,
						'posts_per_page' => 1,
						'post_status' => $post_status,
						'fields' => 'ids'
					);
					$count_query = new WP_Query( $args );
					$count += $count_query->found_posts;
					restore_current_blog();
				}
				return $count;
			}

			// Check if the existing Transient exists
			$cached = get_transient( WOO_CD_PREFIX . '_order_count' );
			if( $cached == false ) {
				$args = array(
					'post_type' => $post_type,
					'posts_per_page' => 1,
					'post_status' => $post_status,
					'fields' => 'ids'
				);
				$count_query = new WP_Query( $args );
				$count = $count_query->found_posts;
				set_transient( WOO_CD_PREFIX . '_order_count', $count, HOUR_IN_SECONDS );
			} else {
				$count = $cached;
			}
			return $count;

		}
	}

	function woo_ce_order_scheduled_export_save( $post_ID = 0 ) {

		$auto_order_date = sanitize_text_field( $_POST['order_dates_filter'] );
		$auto_order_dates_from = false;
		$auto_order_dates_to = false;
		$auto_order_date_variable = false;
		$auto_order_date_variable_length = false;
		if( $auto_order_date == 'variable' ) {
			$auto_order_date_variable = sanitize_text_field( $_POST['order_dates_filter_variable'] );
			$auto_order_date_variable_length = sanitize_text_field( $_POST['order_dates_filter_variable_length'] );
		} else if( $auto_order_date == 'manual' ) {
			$auto_order_dates_from = sanitize_text_field( $_POST['order_dates_from'] );
			$auto_order_dates_to = sanitize_text_field( $_POST['order_dates_to'] );
		}
		update_post_meta( $post_ID, '_filter_order_date', $auto_order_date );
		update_post_meta( $post_ID, '_filter_order_dates_from', $auto_order_dates_from );
		update_post_meta( $post_ID, '_filter_order_dates_to', $auto_order_dates_to );
		update_post_meta( $post_ID, '_filter_order_date_variable', $auto_order_date_variable );
		update_post_meta( $post_ID, '_filter_order_date_variable_length', $auto_order_date_variable_length );
		update_post_meta( $post_ID, '_filter_order_orderby', ( isset( $_POST['order_filter_orderby'] ) ? sanitize_text_field( $_POST['order_filter_orderby'] ) : false ) );
		update_post_meta( $post_ID, '_filter_order_items', ( isset( $_POST['order_items_filter'] ) ? sanitize_text_field( $_POST['order_items_filter'] ) : false ) );
		update_post_meta( $post_ID, '_filter_order_max_order_items', ( isset( $_POST['order_max_order_items'] ) ? sanitize_text_field( $_POST['order_max_order_items'] ) : 10 ) );
		update_post_meta( $post_ID, '_filter_order_flag_notes', ( isset( $_POST['order_flag_notes'] ) ? sanitize_text_field( $_POST['order_flag_notes'] ) : false ) );
		update_post_meta( $post_ID, '_filter_order_items_digital', ( isset( $_POST['order_items_digital_filter'] ) ? sanitize_text_field( $_POST['order_items_digital_filter'] ) : false ) );
		update_post_meta( $post_ID, '_filter_order_status', ( isset( $_POST['order_filter_status'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', $_POST['order_filter_status'] ) ) : false ) );
		update_post_meta( $post_ID, '_filter_order_item_types', ( isset( $_POST['order_filter_order_items_types'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', $_POST['order_filter_order_items_types'] ) ) : false ) );
		$auto_order_product = ( isset( $_POST['order_filter_product'] ) ? $_POST['order_filter_product'] : false );
		// Select2 passes us a string whereas Chosen gives us an array
		if( is_array( $auto_order_product ) && count( $auto_order_product ) == 1 )
			$auto_order_product = explode( ',', $auto_order_product[0] );
		update_post_meta( $post_ID, '_filter_order_product', ( !empty( $auto_order_product ) ? woo_ce_format_product_filters( array_map( 'absint', $auto_order_product ) ) : false ) );

		$user_count = woo_ce_get_export_type_count( 'user' );
		$user_list_limit = apply_filters( 'woo_ce_order_filter_customer_list_limit', 100, $user_count );
		if( $user_count < $user_list_limit )
			update_post_meta( $post_ID, '_filter_order_customer', ( isset( $_POST['order_filter_customer'] ) ? array_map( 'absint', $_POST['order_filter_customer'] ) : false ) );
		else
			update_post_meta( $post_ID, '_filter_order_customer', ( isset( $_POST['order_filter_customer'] ) ? sanitize_text_field( $_POST['order_filter_customer'] ) : false ) );

		update_post_meta( $post_ID, '_filter_order_billing_country', ( isset( $_POST['order_filter_billing_country'] ) ? array_map( 'sanitize_text_field', $_POST['order_filter_billing_country'] ) : false ) );
		update_post_meta( $post_ID, '_filter_order_shipping_country', ( isset( $_POST['order_filter_shipping_country'] ) ? array_map( 'sanitize_text_field', $_POST['order_filter_shipping_country'] ) : false ) );
		update_post_meta( $post_ID, '_filter_order_category', ( isset( $_POST['order_filter_category'] ) ? array_map( 'absint', $_POST['order_filter_category'] ) : false ) );
		update_post_meta( $post_ID, '_filter_order_tag', ( isset( $_POST['order_filter_tag'] ) ? array_map( 'absint', $_POST['order_filter_tag'] ) : false ) );
		update_post_meta( $post_ID, '_filter_order_user_role', ( isset( $_POST['order_filter_user_role'] ) ? array_map( 'sanitize_text_field', $_POST['order_filter_user_role'] ) : false ) );

		$coupon_count = woo_ce_get_export_type_count( 'coupon' );
		$coupon_list_limit = apply_filters( 'woo_ce_order_filter_coupon_list_limit', 100, $coupon_count );
		if( $coupon_count < $coupon_list_limit )
			update_post_meta( $post_ID, '_filter_order_coupon', ( isset( $_POST['order_filter_coupon'] ) ? array_map( 'absint', $_POST['order_filter_coupon'] ) : false ) );
		else
			update_post_meta( $post_ID, '_filter_order_coupon', ( isset( $_POST['order_filter_coupon'] ) ? sanitize_text_field( $_POST['order_filter_coupon'] ) : false ) );

		update_post_meta( $post_ID, '_filter_order_payment', ( isset( $_POST['order_filter_payment'] ) ? array_map( 'sanitize_text_field', $_POST['order_filter_payment'] ) : false ) );
		update_post_meta( $post_ID, '_filter_order_shipping', ( isset( $_POST['order_filter_shipping'] ) ? array_map( 'sanitize_text_field', $_POST['order_filter_shipping'] ) : false ) );

	}
	add_action( 'woo_ce_extend_scheduled_export_save', 'woo_ce_order_scheduled_export_save' );

	function woo_ce_order_dataset_args( $args, $export_type = '' ) {

		// Check if we're dealing with the Order Export Type
		if( $export_type <> 'order' )
			return $args;

		// Merge in the form data for this dataset
		$defaults = array(
			'order_status' => ( isset( $_POST['order_filter_status'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', $_POST['order_filter_status'] ) ) : false ),
			'order_dates_filter' => ( isset( $_POST['order_dates_filter'] ) ? sanitize_text_field( $_POST['order_dates_filter'] ) : false ),
			'order_dates_from' => ( isset( $_POST['order_dates_from'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['order_dates_from'] ) ) : '' ),
			'order_dates_to' => ( isset( $_POST['order_dates_to'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['order_dates_to'] ) ) : '' ),
			'order_dates_filter_variable' => ( isset( $_POST['order_dates_filter_variable'] ) ? absint( $_POST['order_dates_filter_variable'] ) : false ),
			'order_dates_filter_variable_length' => ( isset( $_POST['order_dates_filter_variable_length'] ) ? sanitize_text_field( $_POST['order_dates_filter_variable_length'] ) : false ),
			'order_billing_country' => ( isset( $_POST['order_filter_billing_country'] ) ? array_map( 'sanitize_text_field', $_POST['order_filter_billing_country'] ) : false ),
			'order_shipping_country' => ( isset( $_POST['order_filter_shipping_country'] ) ? array_map( 'sanitize_text_field', $_POST['order_filter_shipping_country'] ) : false ),
			'order_user_roles' => ( isset( $_POST['order_filter_user_role'] ) ? woo_ce_format_user_role_filters( array_map( 'sanitize_text_field', $_POST['order_filter_user_role'] ) ) : false ),
			'order_coupon' => ( isset( $_POST['order_filter_coupon'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['order_filter_coupon'] ) ) : false ),
			'order_product' => ( isset( $_POST['order_filter_product'] ) ? woo_ce_format_product_filters( array_map( 'sanitize_text_field', $_POST['order_filter_product'] ) ) : false ),
			'order_category' => ( isset( $_POST['order_filter_category'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['order_filter_category'] ) ) : false ),
			'order_tag' => ( isset( $_POST['order_filter_tag'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['order_filter_tag'] ) ) : false ),
			'order_ids' => ( isset( $_POST['order_filter_id'] ) ? sanitize_text_field( $_POST['order_filter_id'] ) : false ),
			'order_payment' => ( isset( $_POST['order_filter_payment_gateway'] ) ? array_map( 'sanitize_text_field', $_POST['order_filter_payment_gateway'] ) : false ),
			'order_shipping' => ( isset( $_POST['order_filter_shipping_method'] ) ? array_map( 'sanitize_text_field', $_POST['order_filter_shipping_method'] ) : false ),
			'order_items_digital' => ( isset( $_POST['order_filter_digital_products'] ) ? sanitize_text_field( $_POST['order_filter_digital_products'] ) : false ),
			'order_items' => ( isset( $_POST['order_items'] ) ? sanitize_text_field( $_POST['order_items'] ) : false ),
			'order_items_types' => ( isset( $_POST['order_items_types'] ) ? array_map( 'sanitize_text_field', $_POST['order_items_types'] ) : false ),
			'order_flag_notes' => ( isset( $_POST['order_flag_notes'] ) ? absint( $_POST['order_flag_notes'] ) : false ),
			'max_order_items' => ( isset( $_POST['max_order_items'] ) ? absint( $_POST['max_order_items'] ) : 10 ),
			'order_orderby' => ( isset( $_POST['order_orderby'] ) ? sanitize_text_field( $_POST['order_orderby'] ) : false ),
			'order_order' => ( isset( $_POST['order_order'] ) ? sanitize_text_field( $_POST['order_order'] ) : false ),
			'product_image_formatting' => woo_ce_get_option( 'product_image_formatting', 1 ),
			'gallery_formatting' => woo_ce_get_option( 'gallery_formatting', 1 )
		);
		$args = wp_parse_args( $args, $defaults );

		// Default empty values
		if( empty( $args['max_order_items'] ) )
			$args['max_order_items'] = 10;

		// Save dataset export specific options
		if( $args['order_status'] <> woo_ce_get_option( 'order_status' ) )
			woo_ce_update_option( 'order_status', $args['order_status'] );
		if( $args['order_dates_filter'] <> woo_ce_get_option( 'order_dates_filter' ) )
			woo_ce_update_option( 'order_dates_filter', $args['order_dates_filter'] );
		if( $args['order_dates_from'] <> woo_ce_get_option( 'order_dates_from' ) )
			woo_ce_update_option( 'order_dates_from', woo_ce_format_order_date( $args['order_dates_from'], 'save' ) );
		if( $args['order_dates_to'] <> woo_ce_get_option( 'order_dates_to' ) )
			woo_ce_update_option( 'order_dates_to', woo_ce_format_order_date( $args['order_dates_to'], 'save' ) );
		if( $args['order_dates_filter_variable'] <> woo_ce_get_option( 'order_dates_filter_variable' ) )
			woo_ce_update_option( 'order_dates_filter_variable', $args['order_dates_filter_variable'] );
		if( $args['order_dates_filter_variable_length'] <> woo_ce_get_option( 'order_dates_filter_variable_length' ) )
			woo_ce_update_option( 'order_dates_filter_variable_length', $args['order_dates_filter_variable_length'] );
		if( $args['order_billing_country'] <> woo_ce_get_option( 'order_billing_country' ) )
			woo_ce_update_option( 'order_billing_country', $args['order_billing_country'] );
		if( $args['order_shipping_country'] <> woo_ce_get_option( 'order_shipping_country' ) )
			woo_ce_update_option( 'order_shipping_country', $args['order_shipping_country'] );
		if( $args['order_category'] <> woo_ce_get_option( 'order_category' ) )
			woo_ce_update_option( 'order_category', $args['order_category'] );
		if( $args['order_tag'] <> woo_ce_get_option( 'order_tag' ) )
			woo_ce_update_option( 'order_tag', $args['order_tag'] );
		if( $args['order_user_roles'] <> woo_ce_get_option( 'order_user_roles' ) )
			woo_ce_update_option( 'order_user_roles', $args['order_user_roles'] );
		if( $args['order_coupon'] <> woo_ce_get_option( 'order_coupon' ) )
			woo_ce_update_option( 'order_coupon', $args['order_coupon'] );
		// Product
		// Category
		// Tag
		if( $args['order_ids'] <> woo_ce_get_option( 'order_order_ids' ) )
			woo_ce_update_option( 'order_order_ids', $args['order_ids'] );
		if( $args['order_payment'] <> woo_ce_get_option( 'order_payment_method' ) )
			woo_ce_update_option( 'order_payment_method', $args['order_payment'] );
		if( $args['order_shipping'] <> woo_ce_get_option( 'order_shipping_method' ) )
			woo_ce_update_option( 'order_shipping_method', $args['order_shipping'] );
		if( $args['order_items_digital'] <> woo_ce_get_option( 'order_digital_products' ) )
			woo_ce_update_option( 'order_digital_products', $args['order_items_digital'] );
		if( $args['order_items'] <> woo_ce_get_option( 'order_items_formatting' ) )
			woo_ce_update_option( 'order_items_formatting', $args['order_items'] );
		if( $args['order_items_types'] <> woo_ce_get_option( 'order_items_types' ) )
			woo_ce_update_option( 'order_items_types', $args['order_items_types'] );
		if( $args['order_flag_notes'] <> woo_ce_get_option( 'order_flag_notes' ) )
			woo_ce_update_option( 'order_flag_notes', $args['order_flag_notes'] );
		if( $args['max_order_items'] <> woo_ce_get_option( 'max_order_items' ) )
			woo_ce_update_option( 'max_order_items', $args['max_order_items'] );
		if( $args['order_orderby'] <> woo_ce_get_option( 'order_orderby' ) )
			woo_ce_update_option( 'order_orderby', $args['order_orderby'] );
		if( $args['order_order'] <> woo_ce_get_option( 'order_order' ) )
			woo_ce_update_option( 'order_order', $args['order_order'] );

		return $args;

	}
	add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_order_dataset_args', 10, 2 );

	/* End of: WordPress Administration */

}

function woo_ce_cron_order_dataset_args( $args, $export_type = '', $is_scheduled = 0 ) {

	// Check if we're dealing with the Order Export Type
	if( $export_type <> 'order' )
		return $args;

	$order_orderby = false;
	$order_filter_status = false;
	$order_filter_customer = false;
	$order_filter_product = false;
	$order_filter_billing_country = false;
	$order_filter_shipping_country = false;
	$order_filter_category = false;
	$order_filter_tag = false;
	$order_filter_payment = false;
	$order_filter_shipping = false;
	$order_filter_user_role = false;
	$order_filter_coupon = false;
	$order_filter_digital = false;
	$order_dates_filter = false;
	$order_filter_date_variable = false;
	$order_filter_date_variable_length = false;
	$order_filter_dates_from = false;
	$order_filter_dates_to = false;
	$order_filter_order_item_types = false;
	$max_order_items = woo_ce_get_option( 'max_order_items', 10 );

	if( $is_scheduled ) {
		$scheduled_export = ( $is_scheduled ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );

		$order_orderby = get_post_meta( $scheduled_export, '_filter_order_orderby', true );
		$order_filter_status = get_post_meta( $scheduled_export, '_filter_order_status', true );
		$order_filter_customer = get_post_meta( $scheduled_export, '_filter_order_customer', true );
		$order_filter_product = get_post_meta( $scheduled_export, '_filter_order_product', true );
		$order_filter_billing_country = get_post_meta( $scheduled_export, '_filter_order_billing_country', true );
		$order_filter_shipping_country = get_post_meta( $scheduled_export, '_filter_order_shipping_country', true );
		$order_filter_category = get_post_meta( $scheduled_export, '_filter_order_category', true );
		$order_filter_tag = get_post_meta( $scheduled_export, '_filter_order_tag', true );
		$order_filter_payment = get_post_meta( $scheduled_export, '_filter_order_payment', true );
		$order_filter_shipping = get_post_meta( $scheduled_export, '_filter_order_shipping', true );
		$order_filter_user_role = get_post_meta( $scheduled_export, '_filter_order_user_role', true );
		$order_filter_coupon = get_post_meta( $scheduled_export, '_filter_order_coupon', true );
		$order_filter_digital = get_post_meta( $scheduled_export, '_filter_order_items_digital', true );
		$order_dates_filter = get_post_meta( $scheduled_export, '_filter_order_date', true );
		if( $order_dates_filter ) {
			switch( $order_dates_filter ) {

				case 'manual':
					$order_filter_dates_from = get_post_meta( $scheduled_export, '_filter_order_dates_from', true );
					$order_filter_dates_to = get_post_meta( $scheduled_export, '_filter_order_dates_to', true );
					break;

				case 'variable':
					$order_filter_date_variable = get_post_meta( $scheduled_export, '_filter_order_date_variable', true );
					$order_filter_date_variable_length = get_post_meta( $scheduled_export, '_filter_order_date_variable_length', true );
					break;

			}
		}
		$order_filter_order_item_types = get_post_meta( $scheduled_export, '_filter_order_item_types', true );
		$max_order_items = get_post_meta( $scheduled_export, '_filter_order_max_order_items', true );
	} else {
		if( isset( $_GET['order_status'] ) ) {
			$order_filter_status = sanitize_text_field( $_GET['order_status'] );
			$order_filter_status = explode( ',', $order_filter_status );
		}
		// Customer
		if( isset( $_GET['order_product'] ) ) {
			$order_filter_product = sanitize_text_field( $_GET['order_product'] );
			$order_filter_product = explode( ',', $order_filter_product );
		}
		if( isset( $_GET['billing_country'] ) ) {
			$order_filter_billing_country = sanitize_text_field( $_GET['billing_country'] );
			$order_filter_billing_country = explode( ',', $order_filter_billing_country );
		}
		if( isset( $_GET['shipping_country'] ) ) {
			$order_filter_shipping_country = sanitize_text_field( $_GET['shipping_country'] );
			$order_filter_shipping_country = explode( ',', $order_filter_shipping_country );
		}
		if( isset( $_GET['payment_gateway'] ) ) {
			$order_filter_payment = sanitize_text_field( $_GET['order_payment'] );
			$order_filter_payment = explode( ',', $order_filter_payment );
		}
		if( isset( $_GET['shipping_method'] ) ) {
			$order_filter_shipping = sanitize_text_field( $_GET['shipping_method'] );
			$order_filter_shipping = explode( ',', $order_filter_shipping );
		}
		// User Role
		// Coupon Code
		if( isset( $_GET['order_items_digital'] ) ) {
			$order_filter_digital = sanitize_text_field( $_GET['order_items_digital'] );
		}
		if( isset( $_GET['order_date_from'] ) || isset( $_GET['order_date_to'] ) ) {
			// @mod - The CRON export engine does not support variable date filtering, yet. Check in 2.4+
			$order_dates_filter = 'manual';
			$order_filter_dates_from = ( isset( $_GET['order_date_from'] ) ? sanitize_text_field( $_GET['order_date_from'] ) : false );
			$order_filter_dates_to = ( isset( $_GET['order_date_to'] ) ?  sanitize_text_field( $_GET['order_date_to'] ) : false );
		}
		if( isset( $_GET['max_order_items'] ) ) {
			$max_order_items = absint( $_GET['max_order_items'] );
		}
	}

	// Merge in the form data for this dataset
	$defaults = array(
		'order_orderby' => ( !empty( $order_orderby ) ? $order_orderby : false ),
		'order_status' => ( !empty( $order_filter_status ) ? $order_filter_status : false ),
		'order_customer' => ( !empty( $order_filter_customer ) ? (array)$order_filter_customer : array() ),
		'order_product' => ( !empty( $order_filter_product ) ? (array)$order_filter_product : array() ),
		'order_billing_country' => ( !empty( $order_filter_billing_country ) ? array_map( 'sanitize_text_field', $order_filter_billing_country ) : false ),
		'order_shipping_country' => ( !empty( $order_filter_shipping_country ) ? $order_filter_shipping_country : false ),
		'order_category' => ( !empty( $order_filter_category ) ? $order_filter_category : false ),
		'order_tag' => ( !empty( $order_filter_tag ) ? $order_filter_tag : false ),
		'order_payment' => ( !empty( $order_filter_payment ) ? $order_filter_payment : false ),
		'order_shipping' => ( !empty( $order_filter_shipping ) ? array_map( 'sanitize_text_field', $order_filter_shipping ) : false ),
		'order_user_roles' => ( !empty( $order_filter_user_role ) ? array_map( 'sanitize_text_field', $order_filter_user_role ) : false ),
		'order_coupon' => ( !empty( $order_filter_coupon ) ? array_map( 'sanitize_text_field', (array)$order_filter_coupon ) : false ),
		'order_items_digital' => ( !empty( $order_filter_digital ) ? $order_filter_digital : false ),
		'order_dates_filter' => $order_dates_filter,
		'order_dates_filter_variable' => ( !empty( $order_filter_date_variable ) ? absint( $order_filter_date_variable ) : false ),
		'order_dates_filter_variable_length' => ( !empty( $order_filter_date_variable_length ) ? sanitize_text_field( $order_filter_date_variable_length ) : false ),
		'order_dates_from' => ( !empty( $order_filter_dates_from ) ? $order_filter_dates_from : false ),
		'order_dates_to' => ( !empty( $order_filter_dates_to ) ? $order_filter_dates_to : false ),
		'order_items_types' => ( !empty( $order_filter_order_item_types ) ? $order_filter_order_item_types : false ),
		'max_order_items' => ( !empty( $max_order_items ) ? $max_order_items : false )
	);
	$args = wp_parse_args( $args, $defaults );

	return $args;

}
add_filter( 'woo_ce_extend_cron_dataset_args', 'woo_ce_cron_order_dataset_args', 10, 3 );

// Returns a list of Order export columns
function woo_ce_get_order_fields( $format = 'full', $post_ID = 0 ) {

	$export_type = 'order';

	$fields = array();
	$fields[] = array(
		'name' => 'purchase_id',
		'label' => __( 'Order ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_id',
		'label' => __( 'Post ID', 'woocommerce-exporter' )
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
		'name' => 'order_currency',
		'label' => __( 'Order Currency', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_discount',
		'label' => __( 'Order Discount', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'coupon_code',
		'label' => __( 'Coupon Code', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'coupon_expiry_date',
		'label' => __( 'Coupon Expiry Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'coupon_description',
		'label' => __( 'Coupon Description', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_total_tax',
		'label' => __( 'Order Total Tax', 'woocommerce-exporter' )
	);
/*
	$fields[] = array(
		'name' => 'order_incl_tax',
		'label' => __( 'Order Incl. Tax', 'woocommerce-exporter' )
	);
*/
	$fields[] = array(
		'name' => 'order_subtotal_excl_tax',
		'label' => __( 'Order Subtotal Excl. Tax', 'woocommerce-exporter' )
	);
/*
	$fields[] = array(
		'name' => 'order_tax_rate',
		'label' => __( 'Order Tax Rate', 'woocommerce-exporter' )
	);
*/
	$fields[] = array(
		'name' => 'order_sales_tax',
		'label' => __( 'Sales Tax Total', 'woocommerce-exporter' )
	);
	// Tax Rates
	$tax_rates = woo_ce_get_order_tax_rates();
	if( !empty( $tax_rates ) ) {
		foreach( $tax_rates as $tax_rate ) {
			$fields[] = array(
				'name' => sprintf( 'purchase_total_tax_rate_%d', $tax_rate['rate_id'] ),
				'label' => sprintf( __( 'Order Total Tax: %s', 'woocommerce-exporter' ), $tax_rate['label'] )
			);
		}
	}
	$fields[] = array(
		'name' => 'order_shipping_tax',
		'label' => __( 'Shipping Tax Total', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_incl_tax',
		'label' => __( 'Shipping Incl. Tax', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_excl_tax',
		'label' => __( 'Shipping Excl. Tax', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'refund_total',
		'label' => __( 'Refund Total', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'refund_date',
		'label' => __( 'Refund Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_tax_percentage',
		'label' => __( 'Order Tax Percentage', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'payment_gateway_id',
		'label' => __( 'Payment Gateway ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'payment_gateway',
		'label' => __( 'Payment Gateway', 'woocommerce-exporter' )
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
		'name' => 'shipping_weight_total',
		'label' => __( 'Shipping Weight', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'payment_status',
		'label' => __( 'Order Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_status',
		'label' => __( 'Post Status', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_key',
		'label' => __( 'Order Key', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'transaction_id',
		'label' => __( 'Transaction ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'created_via',
		'label' => __( 'Created Via', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'cart_hash',
		'label' => __( 'Cart Hash', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_date',
		'label' => __( 'Order Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_time',
		'label' => __( 'Order Time', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'customer_message',
		'label' => __( 'Customer Message', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'customer_notes',
		'label' => __( 'Customer Notes', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_notes',
		'label' => __( 'Order Notes', 'woocommerce-exporter' )
	);
	// PayPal
	$fields[] = array(
		'name' => 'paypal_payer_paypal_address',
		'label' => __( 'PayPal: Payer PayPal Address', 'woocommerce-exporter' ),
		'hover' => __( 'PayPal', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'paypal_payer_first_name',
		'label' => __( 'PayPal: Payer first name', 'woocommerce-exporter' ),
		'hover' => __( 'PayPal', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'paypal_payer_last_name',
		'label' => __( 'PayPal: Payer last name', 'woocommerce-exporter' ),
		'hover' => __( 'PayPal', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'paypal_payment_type',
		'label' => __( 'PayPal: Payment type', 'woocommerce-exporter' ),
		'hover' => __( 'PayPal', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'paypal_payment_status',
		'label' => __( 'PayPal: Payment status', 'woocommerce-exporter' ),
		'hover' => __( 'PayPal', 'woocommerce-exporter' )
	);

	$fields[] = array(
		'name' => 'total_quantity',
		'label' => __( 'Total Quantity', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'total_order_items',
		'label' => __( 'Total Order Items', 'woocommerce-exporter' )
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
		'name' => 'ip_address',
		'label' => __( 'Checkout IP Address', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'browser_agent',
		'label' => __( 'Checkout Browser Agent', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'has_downloads',
		'label' => __( 'Has Downloads', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'has_downloaded',
		'label' => __( 'Has Downloaded', 'woocommerce-exporter' )
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

/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'woocommerce-exporter' )
	);
*/

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	// Allow Plugin/Theme authors to add support for additional Order columns
	$fields = apply_filters( sprintf( WOO_CD_PREFIX . '_%s_fields', $export_type ), $fields, $export_type );

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	$fields[] = array(
		'name' => 'order_items_id',
		'label' => __( 'Order Items: ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_product_id',
		'label' => __( 'Order Items: Product ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_variation_id',
		'label' => __( 'Order Items: Variation ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_sku',
		'label' => __( 'Order Items: SKU', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_name',
		'label' => __( 'Order Items: Product Name', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_variation',
		'label' => __( 'Order Items: Product Variation', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_image_embed',
		'label' => __( 'Order Items: Featured Image (Embed)', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_description',
		'label' => __( 'Order Items: Product Description', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_excerpt',
		'label' => __( 'Order Items: Product Excerpt', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_publish_date',
		'label' => __( 'Order Items: Publish Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_modified_date',
		'label' => __( 'Order Items: Modified Date', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_tax_class',
		'label' => __( 'Order Items: Tax Class', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_quantity',
		'label' => __( 'Order Items: Quantity', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_total',
		'label' => __( 'Order Items: Total', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_subtotal',
		'label' => __( 'Order Items: Subtotal', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_rrp',
		'label' => __( 'Order Items: RRP', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_stock',
		'label' => __( 'Order Items: Stock', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_shipping_class',
		'label' => __( 'Order Items: Shipping Class', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_tax',
		'label' => __( 'Order Items: Tax', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_tax_subtotal',
		'label' => __( 'Order Items: Tax Subtotal', 'woocommerce-exporter' )
	);
	// Order Item: Tax Rate - ...
	$tax_rates = woo_ce_get_order_tax_rates();
	if( !empty( $tax_rates ) ) {
		foreach( $tax_rates as $tax_rate ) {
			$fields[] = array(
				'name' => sprintf( 'order_items_tax_rate_%d', $tax_rate['rate_id'] ),
				'label' => sprintf( __( 'Order Items: Tax Rate - %s', 'woocommerce-exporter' ), $tax_rate['label'] )
			);
		}
	}
	unset( $tax_rates, $tax_rate );
	$fields[] = array(
		'name' => 'order_items_refund_subtotal',
		'label' => __( 'Order Items: Refund Subtotal', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_refund_quantity',
		'label' => __( 'Order Items: Refund Quantity', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_type',
		'label' => __( 'Order Items: Type', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_type_id',
		'label' => __( 'Order Items: Type ID', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_category',
		'label' => __( 'Order Items: Category', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_tag',
		'label' => __( 'Order Items: Tag', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_total_sales',
		'label' => __( 'Order Items: Total Sales', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_weight',
		'label' => __( 'Order Items: Weight', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_height',
		'label' => __( 'Order Items: Height', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_width',
		'label' => __( 'Order Items: Width', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_length',
		'label' => __( 'Order Items: Length', 'woocommerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_total_weight',
		'label' => __( 'Order Items: Total Weight', 'woocommerce-exporter' )
	);

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	// Allow Plugin/Theme authors to add support for additional Order Item columns
	$fields = apply_filters( sprintf( WOO_CD_PREFIX . '_%s_fields', 'order_items' ), $fields, $export_type );

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
function woo_ce_override_order_field_labels( $fields = array() ) {

	$export_type = 'order';

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

	// Allow Plugin/Theme authors to easily override export field labels
	$labels = apply_filters( 'woo_ce_override_order_field_labels', $labels );

	if( !empty( $labels ) ) {
		foreach( $fields as $key => $field ) {
			if( isset( $labels[$field['name']] ) )
				$fields[$key]['label'] = $labels[$field['name']];
		}
	}
	return $fields;

}
add_filter( 'woo_ce_order_fields', 'woo_ce_override_order_field_labels', 11 );
add_filter( 'woo_ce_order_items_fields', 'woo_ce_override_order_field_labels', 11 );

// Returns the export column header label based on an export column slug
function woo_ce_get_order_field( $name = null, $format = 'name', $order_items = false ) {

	global $export;

	$output = '';
	if( $name ) {
		$fields = woo_ce_get_order_fields();
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_field() > woo_ce_get_order_fields(): ' . ( time() - $export->start_time ) ) );
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			if( $fields[$i]['name'] == $name ) {
				switch( $format ) {

					case 'name':
						$output = $fields[$i]['label'];
						if( $order_items == 'unique' )
							$output = str_replace( __( 'Order Items: ', 'woocommerce-exporter' ), '', $output );

						// Allow Plugin/Theme authors to easily override export field labels
						$output = apply_filters( 'woo_ce_get_order_field_label', $output, $order_items );

						break;

					case 'full':
						$output = $fields[$i];
						break;

				}
				$i = $size;
			}
		}
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', 'for $fields...: ' . ( time() - $export->start_time ) ) );
	}
	return $output;

}

// Returns a list of Order IDs
function woo_ce_get_orders( $export_type = 'order', $args = array() ) {

	global $export;

	$limit_volume = -1;
	$offset = 0;

	if( $args ) {
		$order_ids = ( isset( $args['order_ids'] ) ? $args['order_ids'] : false );
		$payment = ( isset( $args['order_payment'] ) ? $args['order_payment'] : false );
		$shipping = ( isset( $args['order_shipping'] ) ? $args['order_shipping'] : false );
		$user_roles = ( isset( $args['order_user_roles'] ) ? $args['order_user_roles'] : false );
		$coupon = ( isset( $args['order_coupon'] ) ? $args['order_coupon'] : false );
		$product = ( isset( $args['order_product'] ) ? $args['order_product'] : false );
		$product_category = ( isset( $args['order_category'] ) ? $args['order_category'] : false );
		$product_tag = ( isset( $args['order_tag'] ) ? $args['order_tag'] : false );
		$product_brand = ( isset( $args['order_brand'] ) ? $args['order_brand'] : false );
		$product_vendor = ( isset( $args['order_product_vendor'] ) ? $args['order_product_vendor'] : false );
		$limit_volume = ( isset( $args['limit_volume'] ) ? $args['limit_volume'] : false );
		$offset = $args['offset'];
		$orderby = ( isset( $args['order_orderby'] ) ? $args['order_orderby'] : 'ID' );
		$order = ( isset( $args['order_order'] ) ? $args['order_order'] : 'ASC' );
		$order_dates_filter = ( isset( $args['order_dates_filter'] ) ? $args['order_dates_filter'] : false );
		if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
			woo_ce_error_log( sprintf( 'Debug: %s', 'order_dates_filter' ) );
			woo_ce_error_log( sprintf( 'Debug: %s', $order_dates_filter ) );
		}
		switch( $order_dates_filter ) {

			case 'tomorrow':
				$order_dates_from = woo_ce_get_order_date_filter( 'tomorrow', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'tomorrow', 'to' );
				break;

			case 'today':
				$order_dates_from = woo_ce_get_order_date_filter( 'today', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'today', 'to' );
				break;

			case 'yesterday':
				$order_dates_from = woo_ce_get_order_date_filter( 'yesterday', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'yesterday', 'to' );
				break;

			case 'current_week':
				$order_dates_from = woo_ce_get_order_date_filter( 'current_week', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'current_week', 'to' );
				break;

			case 'last_week':
				$order_dates_from = woo_ce_get_order_date_filter( 'last_week', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'last_week', 'to' );
				break;

			case 'current_month':
				$order_dates_from = woo_ce_get_order_date_filter( 'current_month', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'current_month', 'to' );
				break;

			case 'last_month':
				$order_dates_from = woo_ce_get_order_date_filter( 'last_month', 'from' );
				$order_dates_to = woo_ce_get_order_date_filter( 'last_month', 'to' );
				break;

			case 'manual':
				$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );

				// Populate empty from or to dates
				if( !empty( $args['order_dates_from'] ) ) {
					$order_dates_from = woo_ce_format_order_date( $args['order_dates_from'] );
				} else {
					// Default From date to the first Order
					$order_dates_from = woo_ce_get_order_first_date( $date_format );
				}
				if( !empty( $args['order_dates_to'] ) ) {
					// @mod - Testing the fix for the last date missing out, this line is the bane of 2017 getting it to play nice with all date formats
					// $order_dates_to = woo_ce_format_order_date( date( $date_format, date( strtotime( "+1 day", strtotime( woo_ce_format_order_date( $args['order_dates_to'] ) ) ) ) ) );
					$order_dates_to = woo_ce_format_order_date( $args['order_dates_to'] );
					$order_dates_to = apply_filters( 'woo_ce_get_orders_order_dates_to', $order_dates_to, $args['order_dates_to'] );
				} else {
					// Default To date to tomorrow
					$order_dates_to = woo_ce_format_order_date( woo_ce_get_order_date_filter( 'today', 'to', $date_format ) );
				}

/*
				// @mod - I don't think this is relevant since we now send order_dates_to as 23:59:59 instead of 00:00:00, confirm in 2.4+
				// Check if the same date has been provided for both order_dates_from and order_dates_to
				if( $order_dates_from == $order_dates_to ) {
					// Add a day to order_dates_to
					$order_dates_to = woo_ce_format_order_date( date( $date_format, date( strtotime( "+1 day", strtotime( $order_dates_to ) ) ) ) );
				}
*/

				// Check if the provided dates match the date format
				$validate_from = woo_ce_validate_order_date( $order_dates_from, woo_ce_format_order_date( $date_format ) );
				$validate_to = woo_ce_validate_order_date( $order_dates_to, woo_ce_format_order_date( $date_format ) );
				if(
					!$validate_from && 
					!$validate_to
				) {
					$order_dates_from = woo_ce_format_order_date( date( $date_format, strtotime( $order_dates_from, current_time( 'timestamp', 0 ) ) ) );
					$order_dates_to = woo_ce_format_order_date( date( $date_format, strtotime( $order_dates_to, current_time( 'timestamp', 0 ) ) ) );
				}

				// WP_Query only accepts D-m-Y so we must format dates to that, fun times...
				if( $date_format <> 'd/m/Y' ) {
					$date_format = woo_ce_format_order_date( $date_format );
					if( function_exists( 'date_create_from_format' ) && function_exists( 'date_format' ) ) {

						// Check if we've been passed a mixed format

						if( strpos( $order_dates_from, '-' ) !== false ) {
							if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
								woo_ce_error_log( sprintf( 'Debug: %s', 'order_dates_from' ) );
								woo_ce_error_log( sprintf( 'Debug: %s', $order_dates_from ) );
							}
							$date_check = explode( '-', $order_dates_from );
							if( checkdate( $date_check[0], $date_check[1], $date_check[2] ) ) {
								if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) )
									woo_ce_error_log( sprintf( 'Debug: %s', 'option 1' ) );
								if( $order_dates_from = date_create_from_format( 'm-d-Y', $order_dates_from ) ) {
									if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
										woo_ce_error_log( sprintf( 'Debug: %s', 'option 1: accepted' ) );
										woo_ce_error_log( sprintf( 'Debug: %s', print_r( $date_check, true ) ) );
									}
									$order_dates_from = date_format( $order_dates_from, 'd-m-Y' );
									if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) )
										woo_ce_error_log( sprintf( 'Debug: %s', $order_dates_from ) );
								}
							} else if( checkdate( $date_check[1], $date_check[0], $date_check[2] ) ) {
								if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) )
									woo_ce_error_log( sprintf( 'Debug: %s', 'option 2' ) );
								if( $order_dates_from = date_create_from_format( 'd-m-Y', $order_dates_from ) )
									$order_dates_from = date_format( $order_dates_from, 'd-m-Y' );
							} else {
								if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) )
									woo_ce_error_log( sprintf( 'Debug: %s', 'option 3' ) );
								if( $order_dates_from = date_create_from_format( $date_format, $order_dates_from ) )
									$order_dates_from = date_format( $order_dates_from, 'd-m-Y' );
							}
						} else {
							if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) )
								woo_ce_error_log( sprintf( 'Debug: %s', 'option 4' ) );
							if( $order_dates_from = date_create_from_format( $date_format, $order_dates_from ) )
								$order_dates_from = date_format( $order_dates_from, 'd-m-Y' );
						}
						unset( $date_check );

						if( strpos( $order_dates_to, '-' ) !== false ) {
							if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
								woo_ce_error_log( sprintf( 'Debug: %s', 'order_dates_to' ) );
								woo_ce_error_log( sprintf( 'Debug: %s', $order_dates_to ) );
							}
							$date_check = explode( '-', $order_dates_to );
							if( checkdate( $date_check[0], $date_check[1], $date_check[2] ) ) {
								if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) )
									woo_ce_error_log( sprintf( 'Debug: %s', 'option 1' ) );
								if( $order_dates_to = date_create_from_format( 'm-d-Y', $order_dates_to ) ) {
									if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
										woo_ce_error_log( sprintf( 'Debug: %s', 'option 1: accepted' ) );
										woo_ce_error_log( sprintf( 'Debug: %s', print_r( $date_check, true ) ) );
									}
									$order_dates_to = date_format( $order_dates_to, 'd-m-Y' );
									if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) )
										woo_ce_error_log( sprintf( 'Debug: %s', $order_dates_to ) );
								}
							} else if( checkdate( $date_check[1], $date_check[0], $date_check[2] ) ) {
								if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) )
									woo_ce_error_log( sprintf( 'Debug: %s', 'option 2' ) );
								if( $order_dates_to = date_create_from_format( 'd-m-Y', $order_dates_to ) )
									$order_dates_to = date_format( $order_dates_to, 'd-m-Y' );
							} else {
								if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) )
									woo_ce_error_log( sprintf( 'Debug: %s', 'option 3' ) );
								if( $order_dates_to = date_create_from_format( $date_format, $order_dates_to ) )
									$order_dates_to = date_format( $order_dates_to, 'd-m-Y' );
							}
						} else {
							if( $order_dates_to = date_create_from_format( $date_format, $order_dates_to ) )
								$order_dates_to = date_format( $order_dates_to, 'd-m-Y' );
						}
						unset( $date_check );

					}
				}
				break;

			case 'variable':
				$order_filter_date_variable = $args['order_dates_filter_variable'];
				$order_filter_date_variable_length = $args['order_dates_filter_variable_length'];
				if( $order_filter_date_variable !== false && $order_filter_date_variable_length !== false ) {
					$timestamp = strtotime( sprintf( '-%d %s', $order_filter_date_variable, $order_filter_date_variable_length ), current_time( 'timestamp', 0 ) );
					$order_dates_from = date( 'd-m-Y', mktime( date( 'H', $timestamp ), date( 'i', $timestamp ), date( 's', $timestamp ), date( 'n', $timestamp ), date( 'd', $timestamp ), date( 'Y', $timestamp ) ) );
					$order_dates_to = woo_ce_get_order_date_filter( 'today', 'to' );
					unset( $order_filter_date_variable, $order_filter_date_variable_length, $timestamp );
				}
				break;

			default:
				$order_dates_from = false;
				$order_dates_to = false;
				break;

		}
		if( !empty( $order_dates_from ) && !empty( $order_dates_to ) ) {
			// From
			$order_dates_from = explode( '-', $order_dates_from );
			// Check that a valid date was provided
			if( isset( $order_dates_from[0] ) && isset( $order_dates_from[1] ) && isset( $order_dates_from[2] ) ) {
				$order_dates_from = array(
					'year' => absint( $order_dates_from[2] ),
					'month' => absint( $order_dates_from[1] ),
					'day' => absint( $order_dates_from[0] ),
					'hour' => 0,
					'minute' => 0,
					'second' => 0
				);
				if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
					woo_ce_error_log( sprintf( 'Debug: %s', 'order_dates_from' ) );
					woo_ce_error_log( sprintf( 'Debug: %s', print_r( $order_dates_from, true ) ) );
				}
			} else {
				$order_dates_from = false;
			}
			// To
			$order_dates_to = explode( '-', $order_dates_to );
			// Check that a valid date was provided
			if( isset( $order_dates_to[0] ) && isset( $order_dates_to[1] ) && isset( $order_dates_to[2] ) ) {
				$order_dates_to = array(
					'year' => absint( $order_dates_to[2] ),
					'month' => absint( $order_dates_to[1] ),
					'day' => absint( $order_dates_to[0] ),
					'hour' => 23,
					'minute' => 59,
					'second' => 59
				);
				if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
					woo_ce_error_log( sprintf( 'Debug: %s', 'order_dates_to' ) );
					woo_ce_error_log( sprintf( 'Debug: %s', print_r( $order_dates_to, true ) ) );
				}
				// Check for bad values
				switch( $order_dates_filter ) {

					case 'last_month':
						if( $order_dates_from['month'] <> $order_dates_to['month'] ) {
							$order_dates_to['hour'] = 0;
							$order_dates_to['minute'] = 0;
							$order_dates_to['second'] = 0;
							if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ) {
								woo_ce_error_log( sprintf( 'Debug: %s', 'order_dates_to, last_month override' ) );
								woo_ce_error_log( sprintf( 'Debug: %s', print_r( $order_dates_to, true ) ) );
							}
						}
						break;

				}
			} else {
				$order_dates_to = false;
			}
		}
		$order_status = ( isset( $args['order_status'] ) ? $args['order_status'] : array() );
		$user_ids = ( isset( $args['order_customer'] ) ? $args['order_customer'] : false );
		$billing_country = ( isset( $args['order_billing_country'] ) ? $args['order_billing_country'] : false );
		$shipping_country = ( isset( $args['order_shipping_country'] ) ? $args['order_shipping_country'] : false );
		$order_items = $args['order_items'];
		$order_items_digital = ( isset( $args['order_items_digital'] ) ? $args['order_items_digital'] : false );
	}
	$post_type = 'shop_order';
	$args = array(
		'post_type' => $post_type,
		'orderby' => $orderby,
		'order' => $order,
		'offset' => $offset,
		'posts_per_page' => $limit_volume,
		'fields' => 'ids',
		'suppress_filters' => false
	);
	$woocommerce_version = woo_get_woo_version();
	// Check if this is a pre-WooCommerce 2.2 instance
	if( version_compare( $woocommerce_version, '2.2' ) >= 0 )
		$args['post_status'] = ( function_exists( 'wc_get_order_statuses' ) ? apply_filters( 'woo_ce_order_post_status', array_keys( wc_get_order_statuses() ) ) : 'any' );
	else
		$args['post_status'] = apply_filters( 'woo_ce_order_post_status', 'publish' );
	// Filter Orders by Post ID
	if( !empty( $order_ids ) ) {
		$has_post_id_ranges = false;
		// Check for Post ID ranges (e.g. 100-199)
		if( strpos( $order_ids, '-' ) !== false )
			$has_post_id_ranges = true;
		// Explode the Post IDs
		$order_ids = explode( ',', $order_ids );
		if( $has_post_id_ranges ) {
			foreach( $order_ids as $key => $order_id ) {
				if( strpos( $order_id, '-' ) !== false ) {
					$order_id_ranges = explode( '-', $order_id );
					if( $order_id_ranges !== false ) {
						$order_id_ranges = range( $order_id_ranges[0], $order_id_ranges[1] );
						unset( $order_ids[$key] );
						$order_ids = array_merge( $order_ids, $order_id_ranges );
					}
					unset( $order_id_ranges );
				}
			}
		}
		unset( $has_post_id_ranges );
		// Check if we're looking up a Sequential Order Number
		if( woo_ce_detect_export_plugin( 'seq' ) || woo_ce_detect_export_plugin( 'seq_pro' ) ) {
			$args['meta_query'][] = array(
				'key' => ( woo_ce_detect_export_plugin( 'seq_pro' ) ? '_order_number_formatted' : '_order_number' ),
				'value' => $order_ids
			);
		} else {
			$size = count( $order_ids );
			if( $size > 1 )
				$args['post__in'] = array_map( 'absint', $order_ids );
			else
				$args['p'] = absint( $order_ids[0] );
		}
	}
	// Filter Orders by Product
	if( !empty( $product ) ) {
		$order_ids = woo_ce_get_product_assoc_order_ids( $product );
		if( !empty( $order_ids ) ) {
			$size = count( $order_ids );
			if( $size > 1 )
				$args['post__in'] = array_map( 'absint', $order_ids );
			else
				$args['p'] = absint( $order_ids[0] );
		} else {
			// This means that no Post ID's were returned, fail the export
			$args['post__in'] = array( 0 );
		}
	}
	// Filter Orders by Payment Method
	if( !empty( $payment ) ) {
		$args['meta_query'][] = array(
			'key' => '_payment_method',
			'value' => $payment
		);
	}
	// Filter Orders by Order Status
	if( !empty( $order_status ) ) {
		// Check if this is a WooCommerce 2.2+ instance (new Post Status)
		if( version_compare( $woocommerce_version, '2.2' ) >= 0 ) {
			$args['post_status'] = $order_status;
			if( $export->cron ) {
				// Something weird is going on so we'll override WordPress on this one
				$args['post_status'] = implode( ',', $order_status );
				$args['suppress_filters'] = false;
				add_filter( 'posts_where' , 'woo_ce_wp_query_order_where_override' );
			}
		} else {
			$term_taxonomy = 'shop_order_status';
			$args['tax_query'] = array(
				array(
					'taxonomy' => $term_taxonomy,
					'field' => 'slug',
					'terms' => $order_status
				)
			);
		}
	}
	if( !empty( $user_ids ) ) {
		// Check if we're dealing with a string or list of users
		if( is_string( $user_ids ) )
			$user_ids = explode( ',', $user_ids );
		$user_emails = array();
		foreach( $user_ids as $user_id ) {
			if( $user = get_userdata( $user_id ) )
				$user_emails[] = $user->user_email;
		}
		if( !empty( $user_emails ) ) {
			$args['meta_query'][] = array(
				'key' => '_billing_email',
				'value' => $user_emails
			);
		}
		unset( $user_id, $user_emails );
	}
	// Filter Orders by Billing Country
	if( !empty( $billing_country ) ) {
		$args['meta_query'][] = array(
			'key' => '_billing_country',
			'value' => $billing_country
		);
	}
	// Filter Orders by Shipping Country
	if( !empty( $shipping_country ) ) {
		$args['meta_query'][] = array(
			'key' => '_shipping_country',
			'value' => $shipping_country
		);
	}
	// Filter Order dates
	if( !empty( $order_dates_from ) && !empty( $order_dates_to ) ) {
		$args['date_query'] = array(
			array(
				'column' => apply_filters( 'woo_ce_get_orders_filter_order_dates_column', 'post_date' ),
				'before' => $order_dates_to,
				'after' => $order_dates_from,
				'inclusive' => true
			)
		);
	}
	// Check if we are filtering Orders by Last Export
	if( $order_dates_filter == 'last_export' ) {
		$args['meta_query'][] = array(
			'key' => '_woo_cd_exported',
			'value' => 1,
			'compare' => 'NOT EXISTS'
		);
	}

	// Check if we are only filtering Orders by the Guest User Role
	if( !empty( $user_roles ) ) {
		// Check if we are only filtering Orders by the Guest User Role
		$size = count( $export->args['order_user_roles'] );
		if( $size == 1 && $export->args['order_user_roles'][0] == 'guest' ) {
			$args['meta_query'][] = array(
				'key' => '_customer_user',
				'value' => 0
			);
		}
	}

	$orders = array();

	// Allow other developers to bake in their own filters
	$args = apply_filters( 'woo_ce_get_orders_args', $args );

	if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_get_orders_args', false ) )
		woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_orders(), args: ' . print_r( $args, true ) ) );

	$order_ids = new WP_Query( $args );

	// Something weird is going on so we'll override WordPress on this one
	if( !empty( $order_status ) && $export->cron && version_compare( $woocommerce_version, '2.2' ) >= 0 )
		remove_filter( 'posts_where' , 'woo_ce_wp_query_order_where_override' );

	if( WOO_CD_LOGGING )
		woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_orders(): before $order_ids->posts loop: ' . ( time() - $export->start_time ) ) );

	if( $order_ids->posts ) {
		foreach( $order_ids->posts as $order_id ) {

			// Check that a WP_Post didn't sneak through...
			if( is_object( $order_id ) )
				$order_id = ( isset( $order_id->ID ) ? absint( $order_id->ID ) : $order_id );

			// Check if we need to spin up a Order instance
			if( 
				!empty( $user_roles ) || 
				!empty( $coupon ) || 
				!empty( $product_category ) || 
				!empty( $product_tag ) || 
				!empty( $product_brand ) || 
				!empty( $shipping ) || 
				!empty( $product_vendor ) || 
				!empty( $order_items_digital ) 
			) {

				// Get WooCommerce Order details
				$order = woo_ce_get_order_wc_data( $order_id );

			}

			// Check if we need to spin up Order Items
			if( 
				!empty( $product_category ) || 
				!empty( $product_tag ) || 
				!empty( $product_brand ) || 
				!empty( $order_items_digital ) 
			) {

				// Get a list of Order Item ID's linked to this Order
				$order_items = woo_ce_get_order_item_ids( $order_id );

			}

			// Filter Orders by User Roles
			$order_user_id = get_post_meta( $order_id, '_customer_user', true );
			if( !empty( $user_roles ) ) {
				$user_ids = array();
				$size = count( $export->args['order_user_roles'] );
				// Check if we are only filtering Orders by the Guest User Role
				if( ( $size == 1 && $export->args['order_user_roles'][0] == 'guest' ) == false ) {
					for( $i = 0; $i < $size; $i++ ) {
						$args = array(
							'role' => $export->args['order_user_roles'][$i],
							'fields' => 'ID'
						);
						$user_id = get_users( $args );
						$user_ids = array_merge( $user_ids, $user_id );
					}
					if( !in_array( $order_user_id, $user_ids ) ) {
						unset( $order );
						continue;
					}
				}
			}

			// Filter Orders by Coupons
			$order_coupon_code = woo_ce_get_order_assoc_coupon( $order_id );
			if( !empty( $coupon ) ) {
				$coupon_ids = array();
				$size = count( $export->args['order_coupon'] );
				for( $i = 0; $i < $size; $i++ )
					$coupon_ids[] = strtolower( get_the_title( $coupon[$i] ) );
				if( !in_array( strtolower( $order_coupon_code ), $coupon_ids ) ) {
					unset( $order );
					continue;
				}
			}

			// Filter Orders on Product Category
			if( !empty( $product_category ) ) {
				if( !empty( $order_items ) ) {
					$term_taxonomy = 'product_cat';
					$args = array(
						'fields' => 'ids'
					);
					$category_ids = array();
					foreach( $order_items as $order_item ) {
						if( $product_categories = wp_get_post_terms( $order_item->product_id, $term_taxonomy, $args ) ) {
							$category_ids = array_merge( $category_ids, $product_categories );
							unset( $product_categories );
						}
					}
					if( count( array_intersect( $product_category, $category_ids ) ) == 0 ) {
						unset( $order );
						continue;
					}
					unset( $category_ids );
				} else {
					// If the Order has no Order Items assigned to it we can safely remove it from the export
					unset( $order );
					continue;
				}
			}

			// Filter Orders by Product Tag
			if( !empty( $product_tag ) ) {
				if( !empty( $order_items ) ) {
					$term_taxonomy = 'product_tag';
					$args = array(
						'fields' => 'ids'
					);
					$tag_ids = array();
					foreach( $order_items as $order_item ) {
						if( $product_tags = wp_get_post_terms( $order_item->product_id, $term_taxonomy, $args ) ) {
							$tag_ids = array_merge( $tag_ids, $product_tags );
							unset( $product_tags );
						}
					}
					if( empty( $tag_ids ) || count( array_intersect( $product_tag, $tag_ids ) ) == 0 ) {
						unset( $order );
						continue;
					}
					unset( $tag_ids );
				} else {
					// If the Order has no Order Items assigned to it we can safely remove it from the export
					unset( $order );
					continue;
				}
			}

			// Filter Orders by Product Brand
			if( !empty( $product_brand ) ) {
				if( !empty( $order_items ) ) {
					$term_taxonomy = apply_filters( 'woo_ce_brand_term_taxonomy', 'product_brand' );
					$args = array(
						'fields' => 'ids'
					);
					$brand_ids = array();
					foreach( $order_items as $order_item ) {
						if( $product_brands = wp_get_post_terms( $order_item->product_id, $term_taxonomy, $args ) ) {
							$brand_ids = array_merge( $brand_ids, $product_brands );
							unset( $product_brands );
						}
					}
					if( empty( $brand_ids ) || count( array_intersect( $product_brand, $brand_ids ) ) == 0 ) {
						unset( $order );
						continue;
					}
					unset( $brand_ids );
				} else {
					// If the Order has no Order Items assigned to it we can safely remove it from the export
					unset( $order );
					continue;
				}
			}

			// Filter Orders by Shipping Method
			if( !empty( $shipping ) ) {
				$shipping_id = woo_ce_get_order_assoc_shipping_method_id( $order_id );
				// Shipping Zones add a suffix number separated by a : character
				if( strpos( $shipping_id, ':' ) !== false ) {
					foreach( $shipping as $shipping_method ) {
						if( strpos( $shipping_id, $shipping_method ) === false ) {
							unset( $order );
							break;
						}
					}
					if( isset( $order ) == false )
						continue;
				} else {
					if( !in_array( $shipping_id, $shipping ) ) {
						unset( $order );
						continue;
					}
				}
				unset( $shipping_id );
			}

			// Filter Orders by Product Vendor
			if( !empty( $product_vendor ) ) {
				// Get a list of Orders by the selected Product Vendors
				$vendor_ids = woo_ce_get_product_vendor_assoc_orders( $product_vendor );
				if( !empty( $vendor_ids ) ) {
					if( !in_array( $order_id, $vendor_ids ) ) {
						unset( $order );
						continue;
					}
				}
				unset( $vendor_ids );
			}

			// Filter Orders by Digital Products
			if( !empty( $order_items_digital ) ) {
				if( !empty( $order_items ) ) {
					switch( $order_items_digital ) {

						// Filter Orders by Digital-only Orders
						case 'include_digital':
							$exclude = false;
							foreach( $order_items as $order_item ) {
								$downloadable = get_post_meta( $order_item->product_id, '_downloadable', true );
								if( $downloadable == false ||  $downloadable == 'no' ) {
									$exclude = true;
									// Stop scanning Order Items
									break;
								}
							}
							if( $exclude ) {
								// Do not include this Order ID in the export
								unset( $order );
								continue 2;
							}
							break;

						// Exclude Orders with Digital Products from Orders export
						case 'exclude_digital':
							$exclude = false;
							foreach( $order_items as $order_item ) {
								$downloadable = get_post_meta( $order_item->product_id, '_downloadable', true );
								if( $downloadable == 'yes' ) {
									$exclude = true;
									// Stop scanning Order Items
									break;
								}
							}
							if( $exclude ) {
								// Do not include this Order ID in the export
								unset( $order );
								continue 2;
							}
							break;

						// Exclude Digital-only Orders from Orders export
						case 'exclude_digital_only':
							$exclude = array();
							foreach( $order_items as $order_item ) {
								$downloadable = get_post_meta( $order_item->product_id, '_downloadable', true );
								$exclude[] = ( $downloadable == 'yes' ? 1 : 0 );
							}
							$exclude = array_values( $exclude );
							// Check if the Order contains Digital Products but no Physical Products
							if( in_array( 1, array_values( $exclude ) ) && in_array( 0, array_values( $exclude ) ) == false ) {
								// Remove if there are no physical Products in that Order
								unset( $order );
								continue 2;
							}
							break;

					}
					unset( $exclude );
				}
			}

			// Filter Orders by Booking Start Date

/*
			// @mod - Commented out for WC 3.0 compatibility, confirm in 2.3+
			$order->id = apply_filters( 'woo_ce_get_order_id', $order->id );
			if( $order->id )
				$orders[] = $order->id;
*/

			$order_id = apply_filters( 'woo_ce_get_order_id', $order_id );
			if( $order_id )
				$orders[] = $order_id;

			// Mark this Order as exported if Since last export Date filter is used
			if( $order_dates_filter == 'last_export' && !empty( $order_id ) ) {

				update_post_meta( $order_id, '_woo_cd_exported', 1 );

				$order_flag_notes = woo_ce_get_option( 'order_flag_notes', 0 );

				// Override if this is a Scheduled Export
				$scheduled_export = ( $export->scheduled_export ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );
				if( $scheduled_export ) {
					$single_export_order_flag_notes = get_post_meta( $scheduled_export, '_filter_order_flag_notes', true );
					if( $single_export_order_flag_notes != false )
						$order_flag_notes = $single_export_order_flag_notes;
					unset( $single_export_order_flag_notes );
				}

				// Allow Plugin/Theme authors to run additional tasks (e.g. change Order Status, etc.) when marking an Order as exported
				do_action( 'woo_ce_get_orders_mark_order_exported', $order_id, $scheduled_export );

				if( $order_flag_notes ) {
					// Check if a $order instance is available
					if( !isset( $order ) ) {
						// Get WooCommerce Order details
						$order = woo_ce_get_order_wc_data( $order_id );
					}

					// Add an Order Note
					$note = __( 'Order was exported successfully.', 'woocommerce-exporter' );
					if( method_exists( $order, 'add_order_note' ) )
						$order->add_order_note( $note );
					unset( $note );

				}
			}

		}

		// Only populate the $export Global if it is an export
		if( isset( $export ) ) {
			$export->total_rows = count( $orders );
			if( !empty( $order_ids ) ) {
				// Check if we're looking up a Sequential Order Number
				if( woo_ce_detect_export_plugin( 'seq' ) || woo_ce_detect_export_plugin( 'seq_pro' ) )
					$export->order_ids_raw = $orders;
			}
		}
		unset( $order_ids, $order_id );

	}

	if( WOO_CD_LOGGING )
		woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_orders(): after $order_ids->posts loop: ' . ( time() - $export->start_time ) ) );

	switch( $export_type ) {

		case 'order':
			if( !WOO_CD_DEBUG ) {
				if( $order_dates_filter == 'last_export' ) {
					// Save the Order ID's list to a WordPress Transient incase the export fails
					woo_ce_update_option( 'exported', $orders );
				}
			}
			if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_orders', false ) )
				woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_orders(): $order_ids: ' . print_r( $orders, true ) ) );
			return $orders;
			break;

		case 'customer':
			$customers = array();
			if( !empty( $orders ) ) {
				foreach( $orders as $order_id ) {
					$order = woo_ce_get_order_data( $order_id, 'customer', $export->args );
					if( $duplicate_key = woo_ce_is_duplicate_customer( $customers, $order ) ) {
						$customers[$duplicate_key]->total_spent = $customers[$duplicate_key]->total_spent + woo_ce_format_price( get_post_meta( $order_id, '_order_total', true ) );
						$customers[$duplicate_key]->total_orders++;
						if( strtolower( $order->payment_status ) == 'completed' )
							$customers[$duplicate_key]->completed_orders++;
					} else {
						$customers[$order_id] = $order;
						$customers[$order_id]->total_spent = woo_ce_format_price( get_post_meta( $order_id, '_order_total', true ) );
						$customers[$order_id]->completed_orders = 0;
						if( strtolower( $order->payment_status ) == 'completed' )
							$customers[$order_id]->completed_orders = 1;
						$customers[$order_id]->total_orders = 1;
					}
				}
			}
			return $customers;
			break;

	}

}

function woo_ce_wp_query_order_where_override( $where ) {

	global $export, $wpdb;

	$order_status = ( isset( $export->args['order_status'] ) ? $export->args['order_status'] : false );

	// Skip this if we're dealing with stock WordPress Post Status
	if( count( array_intersect( array( 'trash', 'publish' ), $order_status ) ) )
		return $where;

	// Let's add in our custom Post Status parameters
	if( !empty( $order_status ) ) {
		foreach( $order_status as $key => $status ) {
			if( empty( $status ) ) {
				unset( $order_status[$key] );
				continue;
			}
			$order_status[$key] = " " . $wpdb->posts . ".post_status = '$status'";
		}
		if( !empty( $order_status ) )
			$where .= " AND (" . join( ' OR ', $order_status ) . ")";
	}

	return $where;

}

// Returns WooCommerce Order data associated to a specific Order
function woo_ce_get_order_wc_data( $order_id = 0 ) {

	if( !empty( $order_id ) ) {
		if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
			$order = ( class_exists( 'WC_Order' ) ? new WC_Order( $order_id ) : get_post( $order_id ) );
			$order->status = ( method_exists( $order, 'get_status' ) ? $order->get_status() : false );
			$order->post_status = ( method_exists( $order, 'get_status' ) ? $order->get_status() : false );
			$order->order_date = ( method_exists( $order, 'get_date_created' ) ? $order->get_date_created() : false );
			$order->customer_message = ( method_exists( $order, 'get_customer_note' ) ? $order->get_customer_note() : false );
		} else {
			$order = ( class_exists( 'WC_Order' ) ? new WC_Order( $order_id ) : get_post( $order_id ) );
		}
		return $order;
	}

}

function woo_ce_get_order_data( $order_id = 0, $export_type = 'order', $args = array(), $fields = array() ) {

	global $export;

	// Check if this is a pre-WooCommerce 2.2 instance
	$woocommerce_version = woo_get_woo_version();

	$defaults = array(
		'order_items' => 'combined',
		'order_items_types' => array_keys( woo_ce_get_order_items_types() )
	);
	$args = wp_parse_args( $args, $defaults );

	// Get WooCommerce Order details
	$order = woo_ce_get_order_wc_data( $order_id );

	$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );

	$order->payment_status = $order->status;

	$order->post_status = woo_ce_format_post_status( $order->post_status );
	$order->user_id = get_post_meta( $order_id, '_customer_user', true );
	if( $order->user_id == 0 ) {
		$order->user_id = '';
	} else {
		$order->user_name = woo_ce_get_username( $order->user_id );
		$order->user_role = woo_ce_format_user_role_label( woo_ce_get_user_role( $order->user_id ) );
	}
	$order->purchase_total = get_post_meta( $order_id, '_order_total', true );
	$order->refund_total = ( method_exists( $order, 'get_total_refunded' ) ? $order->get_total_refunded() : 0 );
	$order->refund_date = ( !empty( $order->refund_total ) ? woo_ce_get_order_assoc_refund_date( $order_id ) : '' );
	$order->order_currency = get_post_meta( $order_id, '_order_currency', true );

	$order->billing_first_name = get_post_meta( $order_id, '_billing_first_name', true );
	$order->billing_last_name = get_post_meta( $order_id, '_billing_last_name', true );
	if( empty( $order->billing_first_name ) && empty( $order->billing_first_name ) )
		$order->billing_full_name = '';
	else
		$order->billing_full_name = $order->billing_first_name . ' ' . $order->billing_last_name;
	$order->billing_company = get_post_meta( $order_id, '_billing_company', true );
	$order->billing_address = '';
	$order->billing_address_1 = get_post_meta( $order_id, '_billing_address_1', true );
	$order->billing_address_2 = get_post_meta( $order_id, '_billing_address_2', true );
	if( !empty( $order->billing_address_2 ) )
		$order->billing_address = sprintf( apply_filters( 'woo_ce_get_order_data_billing_address', '%s %s' ), $order->billing_address_1, $order->billing_address_2 );
	else
		$order->billing_address = $order->billing_address_1;
	$order->billing_city = get_post_meta( $order_id, '_billing_city', true );
	$order->billing_postcode = get_post_meta( $order_id, '_billing_postcode', true );
	$order->billing_state = get_post_meta( $order_id, '_billing_state', true );
	$order->billing_country = get_post_meta( $order_id, '_billing_country', true );
	$order->billing_state_full = woo_ce_expand_state_name( $order->billing_country, $order->billing_state );
	$order->billing_country_full = woo_ce_expand_country_name( $order->billing_country );
	$order->billing_phone = get_post_meta( $order_id, '_billing_phone', true );
	$order->billing_email = get_post_meta( $order_id, '_billing_email', true );
	// If the e-mail address is empty check if the Order has a User assigned to it
	if( empty( $order->billing_email ) ) {
		// Check if a User ID has been assigned
		if( !empty( $order->user_id ) ) {
			$user = woo_ce_get_user_data( $order->user_id );
			// Check if the User is valid and e-mail assigned to User
			if( isset( $user->email ) )
				$order->billing_email = $user->email;
			unset( $user );
		}
	}
	$order->shipping_first_name = get_post_meta( $order_id, '_shipping_first_name', true );
	$order->shipping_last_name = get_post_meta( $order_id, '_shipping_last_name', true );
	if( empty( $order->shipping_first_name ) && empty( $order->shipping_last_name ) )
		$order->shipping_full_name = '';
	else
		$order->shipping_full_name = $order->shipping_first_name . ' ' . $order->shipping_last_name;
	$order->shipping_company = get_post_meta( $order_id, '_shipping_company', true );
	$order->shipping_address = '';
	$order->shipping_address_1 = get_post_meta( $order_id, '_shipping_address_1', true );
	$order->shipping_address_2 = get_post_meta( $order_id, '_shipping_address_2', true );
	if( !empty( $order->billing_address_2 ) )
		$order->shipping_address = sprintf( apply_filters( 'woo_ce_get_order_data_shipping_address', '%s %s' ), $order->shipping_address_1, $order->shipping_address_2 );
	else
		$order->shipping_address = $order->shipping_address_1;
	$order->shipping_city = get_post_meta( $order_id, '_shipping_city', true );
	$order->shipping_postcode = get_post_meta( $order_id, '_shipping_postcode', true );
	$order->shipping_state = get_post_meta( $order_id, '_shipping_state', true );
	$order->shipping_country = get_post_meta( $order_id, '_shipping_country', true );
	$order->shipping_state_full = woo_ce_expand_state_name( $order->shipping_country, $order->shipping_state );
	$order->shipping_country_full = woo_ce_expand_country_name( $order->shipping_country );
	$order->shipping_phone = get_post_meta( $order_id, '_shipping_phone', true );

	if( $export_type == 'order' ) {

		$order->post_id = $order->purchase_id = $order_id;
		$order->order_discount = get_post_meta( $order_id, '_cart_discount', true );
		$order->coupon_code = woo_ce_get_order_assoc_coupon( $order_id );
		if( !empty( $order->coupon_code ) ) {
			$coupon = get_page_by_title( $order->coupon_code, OBJECT, 'shop_coupon' );
			if( $coupon !== null ) {
				$order->coupon_description = $coupon->post_excerpt;
				$order->coupon_expiry_date = woo_ce_format_date( get_post_meta( $coupon->ID, 'expiry_date', true ) );
			}
			unset( $coupon );
		}
		$order->order_sales_tax = get_post_meta( $order_id, '_order_tax', true );
		$order->order_shipping_tax = get_post_meta( $order_id, '_order_shipping_tax', true );
		$order->shipping_cost = get_post_meta( $order_id, '_order_shipping', true );
		$order->shipping_incl_tax = ( $order->shipping_cost + $order->order_shipping_tax );
		$order->shipping_excl_tax = ( $order->shipping_cost - $order->order_shipping_tax );
		$order->purchase_total_tax = ( $order->order_sales_tax + $order->order_shipping_tax );
		if( !empty( $order->purchase_total_tax ) ) {
			// Tax Rates
			$tax_rates = woo_ce_get_order_tax_rates( $order_id );
			if( !empty( $tax_rates ) ) {
				foreach( $tax_rates as $tax_rate ) {
					$order->{sprintf( 'purchase_total_tax_rate_%d', $tax_rate['rate_id'] )} = woo_ce_format_price( woo_ce_get_order_assoc_tax_rate_total( $order_id, $tax_rate['rate_id'] ), $order->order_currency );
				}
			}
			unset( $tax_rates, $tax_rate );
		}
		$order->purchase_total = $order->purchase_total - $order->refund_total;
		$order->order_subtotal_excl_tax = ( $order->purchase_total - $order->purchase_total_tax );
		$order->purchase_subtotal = $order->order_subtotal_excl_tax - $order->shipping_cost;
		// Order Tax Percentage - Order Total - Total Tax / Total Tax
		$order->order_tax_percentage = 0;
		if( !empty( $order->purchase_total_tax ) && !empty( $order->purchase_total ) ) {
			$order_tax_percentage = apply_filters( 'woo_ce_override_order_tax_percentage_format', '%d%%' );
			// Fetch the tax rates assigned to this Order
			$tax_rates = woo_ce_get_order_assoc_tax_rates( $order_id );
			if( !empty( $tax_rates ) ) {
				foreach( $tax_rates as $tax_rate ) {
					// Take the Rate ID and fetch the Rate % from the WooCommerce Tax Rates table
					$order->order_tax_percentage = sprintf( $order_tax_percentage, floatval( woo_ce_get_order_tax_percentage( $tax_rate['rate_id'] ) ) );
					break;
				}
			}
			unset( $tax_rates, $tax_rate, $order_tax_percentage );
		}
		$order->purchase_total = woo_ce_format_price( $order->purchase_total, $order->order_currency );
		$order->order_sales_tax = woo_ce_format_price( $order->order_sales_tax, $order->order_currency );
		$order->order_shipping_tax = woo_ce_format_price( $order->order_shipping_tax, $order->order_currency );
		$order->purchase_subtotal = woo_ce_format_price( $order->purchase_subtotal, $order->order_currency );
		$order->order_discount = woo_ce_format_price( $order->order_discount, $order->order_currency );
		$order->order_subtotal_excl_tax = woo_ce_format_price( $order->order_subtotal_excl_tax, $order->order_currency );
		$order->refund_total = woo_ce_format_price( $order->refund_total, $order->order_currency );
		$order->payment_status = woo_ce_format_order_status( $order->payment_status );
		$order->payment_gateway_id = get_post_meta( $order_id, '_payment_method', true );
		$order->payment_gateway = woo_ce_format_order_payment_gateway( $order->payment_gateway_id );
		// WooCommerce 2.1+ stores the shipping method in Order Items, includes fallback support
		if( version_compare( woo_get_woo_version(), '2.1', '>=' ) ) {
			$order->shipping_method_id = woo_ce_get_order_assoc_shipping_method_id( $order_id );
			$order->shipping_method = $order->get_shipping_method();
		} else {
			$order->shipping_method_id = woo_ce_get_order_assoc_shipping_method_id( $order_id );
			$order->shipping_method = '';
		}
		$order->shipping_cost = woo_ce_format_price( $order->shipping_cost, $order->order_currency );
		$order->shipping_excl_tax = woo_ce_format_price( $order->shipping_excl_tax, $order->order_currency );
		$order->purchase_total_tax = woo_ce_format_price( $order->purchase_total_tax, $order->order_currency );

		$order->shipping_weight_total = '';
		$order->order_key = get_post_meta( $order_id, '_order_key', true );
		$order->transaction_id = get_post_meta( $order_id, '_transaction_id', true );
		$order->created_via = get_post_meta( $order_id, '_created_via', true );
		$order->cart_hash = get_post_meta( $order_id, '_cart_hash', true );
		$order->purchase_date = ( function_exists( 'wc_format_datetime' ) ? wc_format_datetime( $order->order_date, $date_format ) : woo_ce_format_date( $order->order_date ) );
		$order->purchase_time = ( function_exists( 'wc_format_datetime' ) ? wc_format_datetime( $order->order_date, get_option( 'time_format' ) ) : mysql2date( 'H:i:s', $order->order_date ) );
		$order->ip_address = woo_ce_format_ip_address( get_post_meta( $order_id, '_customer_ip_address', true ) );
		$order->browser_agent = get_post_meta( $order_id, '_customer_user_agent', true );
		$order->has_downloads = 0;
		$order->has_downloaded = 0;
		// Order Downloads
		if( $order_downloads = woo_ce_get_order_assoc_downloads( $order_id ) ) {
			$order->has_downloads = 1;
			foreach( $order_downloads as $order_download ) {
				// Check if any download permissions have counts against them
				if( $order_download->download_count > 0 ) {
					$order->has_downloaded = 1;
					break;
				}
			}
		}
		unset( $order_downloads, $order_download );
		$order->has_downloads = woo_ce_format_switch( $order->has_downloads );
		$order->has_downloaded = woo_ce_format_switch( $order->has_downloaded );
		$order->customer_notes = '';
		$order->order_notes = '';
		$order->total_quantity = 0;
		$order->total_order_items = 0;

		// Order Notes
		if( $order_notes = woo_ce_get_order_assoc_notes( $order_id ) ) {
			if( WOO_CD_DEBUG )
				$order->order_notes = implode( $export->category_separator, $order_notes );
			else
				$order->order_notes = implode( "\n", $order_notes );
			unset( $order_notes );
		}

		// Customer Notes
		if( $order_notes = woo_ce_get_order_assoc_notes( $order_id, 'customer_note' ) ) {
			if( WOO_CD_DEBUG )
				$order->customer_notes = implode( $export->category_separator, $order_notes );
			else
				$order->customer_notes = implode( "\n", $order_notes );
			unset( $order_notes );
		}

		// PayPal
		$order->paypal_payer_paypal_address = get_post_meta( $order_id, 'Payer PayPal address', true );
		$order->paypal_payer_first_name = get_post_meta( $order_id, 'Payer first name', true );
		$order->paypal_payer_last_name = get_post_meta( $order_id, 'Payer last name', true );
		$order->paypal_payment_type = get_post_meta( $order_id, 'Payment type', true );
		$order->paypal_payment_status = get_post_meta( $order_id, '_paypal_status', true );

		// Stripe

		if( $order->order_items = woo_ce_get_order_items( $order_id, $args['order_items_types'] ) ) {
			$order->total_order_items = count( $order->order_items );
			if( $args['order_items'] == 'combined' ) {
				$order->order_items_id = '';
				$order->order_items_product_id = '';
				$order->order_items_variation_id = '';
				$order->order_items_sku = '';
				$order->order_items_name = '';
				$order->order_items_variation = '';
				$order->order_items_image_embed = '';
				$order->order_items_description = '';
				$order->order_items_excerpt = '';
				$order->order_items_publish_date = '';
				$order->order_items_modified_date = '';
				$order->order_items_tax_class = '';
				$order->order_items_quantity = '';
				$order->order_items_total = '';
				$order->order_items_subtotal = '';
				$order->order_items_rrp = '';
				$order->order_items_stock = '';
				$order->order_items_shipping_class = '';
				$order->order_items_tax = '';
				$order->order_items_tax_subtotal = '';
				$order->order_items_refund_subtotal = '';
				$order->order_items_refund_quantity = '';
				$order->order_items_type = '';
				$order->order_items_type_id = '';
				$order->order_items_category = '';
				$order->order_items_tag = '';
				$order->order_items_weight = '';
				$order->order_items_height = '';
				$order->order_items_width = '';
				$order->order_items_length = '';
				$order->order_items_total_sales = '';
				$order->order_items_total_weight = '';
				if( !empty( $order->order_items ) ) {
					foreach( $order->order_items as $order_item ) {
						if( empty( $order_item->sku ) )
							$order_item->sku = '';
						$order->order_items_id .= $order_item->id . $export->category_separator;
						$order->order_items_product_id .= $order_item->product_id . $export->category_separator;
						$order->order_items_variation_id .= $order_item->variation_id . $export->category_separator;
						$order->order_items_sku .= $order_item->sku . $export->category_separator;
						$order->order_items_name .= $order_item->name . $export->category_separator;
						$order->order_items_variation .= $order_item->variation . $export->category_separator;
						$order->order_items_image_embed .= $order_item->image_embed . $export->category_separator;
						$order->order_items_description .= woo_ce_format_description_excerpt( $order_item->description ) . $export->category_separator;
						$order->order_items_excerpt .= woo_ce_format_description_excerpt( $order_item->excerpt ) . $export->category_separator;
						$order->order_items_publish_date .= $order_item->publish_date . $export->category_separator;
						$order->order_items_modified_date .= $order_item->modified_date . $export->category_separator;
						$order->order_items_tax_class .= $order_item->tax_class . $export->category_separator;
						if( empty( $order_item->quantity ) && '0' != $order_item->quantity )
							$order_item->quantity = '';
						$order->order_items_quantity .= $order_item->quantity . $export->category_separator;
						$order->order_items_total .= $order_item->total . $export->category_separator;
						$order->order_items_subtotal .= $order_item->subtotal . $export->category_separator;
						$order->order_items_rrp .= $order_item->rrp . $export->category_separator;
						$order->order_items_stock .= $order_item->stock . $export->category_separator;
						$order->order_items_shipping_class .= $order_item->shipping_class . $export->category_separator;
						$order->order_items_tax .= $order_item->tax . $export->category_separator;
						$order->order_items_tax_subtotal .= $order_item->tax_subtotal . $export->category_separator;
						$order->order_items_refund_subtotal .= $order_item->refund_subtotal . $export->category_separator;
						$order->order_items_refund_quantity .= $order_item->refund_quantity . $export->category_separator;
						$order->order_items_type .= $order_item->type . $export->category_separator;
						$order->order_items_type_id .= $order_item->type_id . $export->category_separator;
						$order->order_items_category .= $order_item->category . $export->category_separator;
						$order->order_items_tag .= $order_item->tag . $export->category_separator;
						$order->order_items_weight .= $order_item->weight . $export->category_separator;
						$order->order_items_height .= $order_item->height . $export->category_separator;
						$order->order_items_width .= $order_item->width . $export->category_separator;
						$order->order_items_length .= $order_item->length . $export->category_separator;
						$order->order_items_total_sales .= $order_item->total_sales . $export->category_separator;
						$order->order_items_total_weight .= $order_item->total_weight . $export->category_separator;
						// Add Order Item weight to Shipping Weight
						if( $order_item->total_weight != '' )
							$order->shipping_weight_total += $order_item->total_weight;
					}
					$order->order_items_id = substr( $order->order_items_id, 0, -1 );
					$order->order_items_product_id = substr( $order->order_items_product_id, 0, -1 );
					$order->order_items_variation_id = substr( $order->order_items_variation_id, 0, -1 );
					$order->order_items_sku = substr( $order->order_items_sku, 0, -1 );
					$order->order_items_name = substr( $order->order_items_name, 0, -1 );
					$order->order_items_variation = substr( $order->order_items_variation, 0, -1 );
					$order->order_items_image_embed = substr( $order->order_items_image_embed, 0, -1 );
					$order->order_items_description = substr( $order->order_items_description, 0, -1 );
					$order->order_items_excerpt = substr( $order->order_items_excerpt, 0, -1 );
					$order->order_items_publish_date = substr( $order->order_items_publish_date, 0, -1 );
					$order->order_items_modified_date = substr( $order->order_items_modified_date, 0, -1 );
					$order->order_items_tax_class = substr( $order->order_items_tax_class, 0, -1 );
					$order->order_items_quantity = substr( $order->order_items_quantity, 0, -1 );
					$order->order_items_total = substr( $order->order_items_total, 0, -1 );
					$order->order_items_subtotal = substr( $order->order_items_subtotal, 0, -1 );
					$order->order_items_rrp = substr( $order->order_items_rrp, 0, -1 );
					$order->order_items_stock = substr( $order->order_items_stock, 0, -1 );
					$order->order_items_shipping_class = substr( $order->order_items_shipping_class, 0, -1 );
					$order->order_items_tax = substr( $order_item->tax, 0, -1 );
					$order->order_items_tax_subtotal = substr( $order_item->tax_subtotal, 0, -1 );
					$order->order_items_refund_subtotal = substr( $order_item->refund_subtotal, 0, -1 );
					$order->order_items_refund_quantity = substr( $order_item->refund_quantity, 0, -1 );
					$order->order_items_type = substr( $order->order_items_type, 0, -1 );
					$order->order_items_type_id = substr( $order->order_items_type_id, 0, -1 );
					$order->order_items_category = substr( $order->order_items_category, 0, -1 );
					$order->order_items_tag = substr( $order->order_items_tag, 0, -1 );
					$order->order_items_weight = substr( $order->order_items_weight, 0, -1 );
					$order->order_items_height = substr( $order->order_items_height, 0, -1 );
					$order->order_items_width = substr( $order->order_items_width, 0, -1 );
					$order->order_items_length = substr( $order->order_items_length, 0, -1 );
					$order->order_items_total_sales = substr( $order->order_items_total_sales, 0, -1 );
					$order->order_items_total_weight = substr( $order->order_items_total_weight, 0, -1 );
				}
				$order = apply_filters( 'woo_ce_order_items_combined', $order );
			} else if( $args['order_items'] == 'unique' ) {
				if( !empty( $order->order_items ) ) {
					$i = 1;
					foreach( $order->order_items as $order_item ) {
						$order->{sprintf( 'order_item_%d_id', $i )} = $order_item->id;
						$order->{sprintf( 'order_item_%d_product_id', $i )} = $order_item->product_id;
						$order->{sprintf( 'order_item_%d_variation_id', $i )} = $order_item->variation_id;
						$order->{sprintf( 'order_item_%d_sku', $i )} = ( empty( $order_item->sku ) == false ? $order_item->sku : '' );
						$order->{sprintf( 'order_item_%d_name', $i )} = $order_item->name;
						$order->{sprintf( 'order_item_%d_variation', $i )} = $order_item->variation;
						$order->{sprintf( 'order_item_%d_image_embed', $i )} = $order_item->image_embed;
						$order->{sprintf( 'order_item_%d_description', $i )} = woo_ce_format_description_excerpt( $order_item->description );
						$order->{sprintf( 'order_item_%d_excerpt', $i )} = woo_ce_format_description_excerpt( $order_item->excerpt );
						$order->{sprintf( 'order_item_%d_publish_date', $i )} = $order_item->publish_date;
						$order->{sprintf( 'order_item_%d_modified_date', $i )} = $order_item->modified_date;
						$order->{sprintf( 'order_item_%d_tax_class', $i )} = $order_item->tax_class;
						if( empty( $order_item->quantity ) && '0' != $order_item->quantity )
							$order_item->quantity = '';
						$order->{sprintf( 'order_item_%d_quantity', $i )} = $order_item->quantity;
						$order->{sprintf( 'order_item_%d_total', $i )} = $order_item->total;
						$order->{sprintf( 'order_item_%d_subtotal', $i )} = $order_item->subtotal;
						$order->{sprintf( 'order_item_%d_rrp', $i )} = $order_item->rrp;
						$order->{sprintf( 'order_item_%d_stock', $i )} = $order_item->stock;
						$order->{sprintf( 'order_item_%d_tax', $i )} = $order_item->tax;
						$order->{sprintf( 'order_item_%d_tax_subtotal', $i )} = $order_item->tax_subtotal;
						$order->{sprintf( 'order_item_%d_refund_subtotal', $i )} = $order_item->refund_subtotal;
						$order->{sprintf( 'order_item_%d_refund_quantity', $i )} = $order_item->refund_quantity;
						$order->{sprintf( 'order_item_%d_type', $i )} = $order_item->type;
						$order->{sprintf( 'order_item_%d_type_id', $i )} = $order_item->type_id;
						$order->{sprintf( 'order_item_%d_category', $i )} = $order_item->category;
						$order->{sprintf( 'order_item_%d_tag', $i )} = $order_item->tag;
						$order->{sprintf( 'order_item_%d_weight', $i )} = $order_item->weight;
						$order->{sprintf( 'order_item_%d_height', $i )} = $order_item->height;
						$order->{sprintf( 'order_item_%d_width', $i )} = $order_item->width;
						$order->{sprintf( 'order_item_%d_length', $i )} = $order_item->length;
						$order->{sprintf( 'order_item_%d_total_sales', $i )} = $order_item->total_sales;
						$order->{sprintf( 'order_item_%d_total_weight', $i )} = $order_item->total_weight;
						// Add Order Item weight to Shipping Weight
						if( $order_item->total_weight != '' )
							$order->shipping_weight_total += $order_item->total_weight;
						$order = apply_filters( 'woo_ce_order_items_unique', $order, $i, $order_item );
						$i++;
					}
				}
			}
			if( !empty( $order->order_items ) ) {
				foreach( $order->order_items as $order_item ) {
					$order->total_quantity += $order_item->quantity; 
				}
			}
		}

		// Custom Order fields
		$custom_orders = woo_ce_get_option( 'custom_orders', '' );
		if( !empty( $custom_orders ) ) {
			foreach( $custom_orders as $custom_order ) {
				if( !empty( $custom_order ) ) {
					$order->{$custom_order} = woo_ce_format_custom_meta( get_post_meta( $order_id, $custom_order, true ) );
				}
			}
		}

		// Check if the Order has a User assigned to it
		if( !empty( $order->user_id ) ) {
			// Custom User fields
			$custom_users = woo_ce_get_option( 'custom_users', '' );
			if( !empty( $custom_users ) ) {
				foreach( $custom_users as $custom_user ) {
					if( !empty( $custom_user ) && !isset( $order->{$custom_user} ) ) {
						$order->{$custom_user} = woo_ce_format_custom_meta( get_user_meta( $order->user_id, $custom_user, true ) );
					}
				}
			}
			unset( $custom_users, $custom_user );
		}

	} else if( $export_type = 'customer' ) {

		// Check if the Order has a User assigned to it
		if( !empty( $order->user_id ) ) {

			// Load up the User data as other Plugins will use it too
			$user = woo_ce_get_user_data( $order->user_id );

			// WooCommerce Follow-Up Emails - http://www.woothemes.com/products/follow-up-emails/
			if( woo_ce_detect_export_plugin( 'wc_followupemails' ) ) {

				global $wpdb;

				if( isset( $user->email ) ) {
					$followup_optout_sql = $wpdb->prepare( "SELECT `id` FROM `" . $wpdb->prefix . "followup_email_excludes` WHERE `email` = %s LIMIT 1", $user->email );
					$order->followup_optout = $wpdb->get_var( $followup_optout_sql );
				}

			}

			// Custom User fields
			$custom_users = woo_ce_get_option( 'custom_users', '' );
			if( !empty( $custom_users ) ) {
				foreach( $custom_users as $custom_user ) {
					if( !empty( $custom_user ) && !isset( $order->{$custom_user} ) ) {
						$order->{$custom_user} = woo_ce_format_custom_meta( get_user_meta( $order->user_id, $custom_user, true ) );
					}
				}
			}
			unset( $custom_users, $custom_user );

			// Clean up
			unset( $user );

		}

		// Custom Customer fields
		$custom_customers = woo_ce_get_option( 'custom_customers', '' );
		if( !empty( $custom_customers ) ) {
			foreach( $custom_customers as $custom_customer ) {
				if( !empty( $custom_customer ) )
					$order->{$custom_customer} = esc_attr( get_user_meta( $order->user_id, $custom_customer, true ) );
			}
		}

	}

	// Allow Plugin/Theme authors to add support for additional Order columns
	$order = apply_filters( 'woo_ce_order', $order, $order_id );

	// Trim back the Order just to requested export fields
	if( !empty( $fields ) ) {
		$fields[] = 'id';
		if( $args['order_items'] == 'individual' || apply_filters( 'woo_ce_get_order_data_return_order_items', false ) )
			$fields[] = 'order_items';
		if( !empty( $order ) ) {
			foreach( $order as $key => $data ) {
				if( !in_array( $key, $fields ) )
					unset( $order->$key );
			}
		}
	}

	return $order;

}

function woo_ce_export_dataset_override_order( $output = null, $export_type = null ) {

	global $export;

	if( WOO_CD_LOGGING )
		woo_ce_error_log( sprintf( 'Debug: %s', 'before woo_ce_get_orders(): ' . ( time() - $export->start_time ) ) );
	if( $orders = woo_ce_get_orders( 'order', $export->args ) ) {
		if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_orders', false ) )
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_export_dataset_override_order(): $order_ids: ' . print_r( $orders, true ) ) );
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_get_orders(): ' . ( time() - $export->start_time ) ) );
		$export->total_columns = $size = count( $export->columns );
		// XML, RSS export
		if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
			if( !empty( $export->fields ) ) {
				foreach( $orders as $order ) {

					if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_orders', false ) )
						woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_export_dataset_override_order(): $order: ' . $order ) );

					if( $export->export_format == 'xml' )
						$child = $output->addChild( apply_filters( 'woo_ce_export_xml_order_node', sanitize_key( $export_type ) ) );
					else if( $export->export_format == 'rss' )
						$child = $output->addChild( 'item' );
					if( apply_filters( 'woo_ce_export_xml_order_node_id_attribute', true ) )
						$child->addAttribute( 'id', $order );
					$args = $export->args;
					if( $export->args['order_items'] == 'unique' )
						$args['order_items'] = 'individual';
					$order = woo_ce_get_order_data( $order, 'order', $args, array_keys( $export->fields ) );
					if( WOO_CD_LOGGING )
						woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) ) );
					if( in_array( $export->args['order_items'], array( 'combined', 'unique' ) ) ) {
						if( $export->args['order_items'] == 'unique' ) {
							// Order items formatting: SPECK-IPHONE
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $order->$field ) && isset( $export->columns[$key] ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $order->$field ) )
											$child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
										else
											$child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
									}
								}
							}
							if( $export->export_format == 'xml' )
								$order_items_child = $child->addChild( apply_filters( 'woo_ce_export_xml_order_items_node', 'order_items' ) );
							else if( $export->export_format == 'rss' )
								$order_items_child = $child->addChild( 'order_items' );
							if( !empty( $order->order_items ) ) {
								foreach( $order->order_items as $order_item ) {
									if( $export->export_format == 'xml' )
										$order_item_child = $order_items_child->addChild( apply_filters( 'woo_ce_export_xml_order_item_node', 'order_item' ) );
									else if( $export->export_format == 'rss' )
										$order_item_child = $order_items_child->addChild( 'order_item' );
									foreach( array_keys( $export->fields ) as $key => $field ) {
										if( strpos( $field, 'order_items_' ) !== false ) {
											$field = str_replace( 'order_items_', '', $field );
											if( isset( $order_item->$field ) && isset( $export->columns[$key] ) ) {
												$export->columns[$key] = str_replace( __( 'Order Items: ', 'woocommerce-exporter' ), '', $export->columns[$key] );
												if( !is_array( $field ) ) {
													if( woo_ce_is_xml_cdata( $order_item->$field ) )
														$order_item_child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order_item->$field ) ) );
													else
														$order_item_child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $order_item->$field ) ) );
												}
											}
										}
									}
								}
							}
						} else {
							// Order items formatting: SPECK-IPHONE|INCASE-NANO|-
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $order->$field ) && isset( $export->columns[$key] ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $order->$field ) )
											$child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
										else
											$child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
									}
								}
/*
								if( !empty( $order->order_items ) ) {
									foreach( $order->order_items as $order_item ) {
										if( $export->export_format == 'xml' )
											$order_item_child = $child->addChild( apply_filters( 'woo_ce_export_xml_order_item_node', 'order_item' ) );
										else if( $export->export_format == 'rss' )
											$order_item_child = $child->addChild( 'order_item' );
										$order_item_child->addAttribute( 'id', $order->order_items_id );
									}
								}
*/
							}
						}
					} else if( $export->args['order_items'] == 'individual' ) {
						// Order items formatting: SPECK-IPHONE<br />INCASE-NANO<br />-
						if( !empty( $order->order_items ) ) {
							foreach( $order->order_items as $order_item ) {
								$order = apply_filters( 'woo_ce_order_items_individual', $order, $order_item );
								foreach( array_keys( $export->fields ) as $key => $field ) {
									if( isset( $order->$field ) && isset( $export->columns[$key] ) ) {
										if( !is_array( $field ) ) {
											if( woo_ce_is_xml_cdata( $order->$field ) )
												$child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
											else
												$child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
										}
									}
								}
							}
							unset( $order->order_items );
						} else {
							foreach( array_keys( $export->fields ) as $key => $field ) {
								if( isset( $order->$field ) && isset( $export->columns[$key] ) ) {
									if( !is_array( $field ) ) {
										if( woo_ce_is_xml_cdata( $order->$field ) )
											$child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
										else
											$child->addChild( apply_filters( 'woo_ce_export_xml_order_label', sanitize_key( $export->columns[$key] ), $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
									}
								}
							}
						}
					}

					// Reset the time limit count
					if( function_exists( 'set_time_limit' ) )
						@set_time_limit( $export->time_limit );

				}
			}
		} else {
			// PHPExcel export
			if( $export->args['order_items'] == 'individual' )
				$output = array();
			if( WOO_CD_LOGGING )
				woo_ce_error_log( sprintf( 'Debug: %s', 'foreach $orders...: ' . ( time() - $export->start_time ) ) );
			foreach( $orders as $order ) {

				if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_orders', false ) )
					woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_export_dataset_override_order(): $order: ' . $order ) );

				if( in_array( $export->args['order_items'], array( 'combined', 'unique' ) ) ) {
					// Order items formatting: SPECK-IPHONE|INCASE-NANO|-
					$output[] = woo_ce_get_order_data( $order, 'order', $export->args, array_keys( $export->fields ) );
					if( WOO_CD_LOGGING )
						woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) ) );
				} else if( $export->args['order_items'] == 'individual' ) {
					// Order items formatting: SPECK-IPHONE<br />INCASE-NANO<br />-
					$order = woo_ce_get_order_data( $order, 'order', $export->args, array_keys( $export->fields ) );
					if( WOO_CD_LOGGING )
						woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) ) );
					if( !empty( $order->order_items ) ) {
						foreach( $order->order_items as $order_item ) {
							$order = apply_filters( 'woo_ce_order_items_individual', $order, $order_item );
							// This fixes the Order Items for this Order Items Formatting rule
							$output[] = (object)(array)$order;
							$output = apply_filters( 'woo_ce_order_items_individual_output', $output, $order, $order_item );
						}
						// Allow Plugin/Theme authors to add in blank rows between Orders
						$output = apply_filters( 'woo_ce_order_items_individual_output_end', $output, $order );
					} else {
						$output[] = $order;
					}
				}

				// Reset the time limit count
				if( function_exists( 'set_time_limit' ) )
					@set_time_limit( $export->time_limit );

			}
		}
		unset( $orders, $order );
	}

	if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_orders', false ) )
		woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_export_dataset_override_order(): $output: ' . print_r( $output, true ) ) );

	return $output;

}

function woo_ce_export_dataset_multisite_override_order( $output = null, $export_type = null ) {

	global $export;

	$sites = wp_get_sites();
	if( !empty( $sites ) ) {
		foreach( $sites as $site ) {
			switch_to_blog( $site['blog_id'] );
			if( WOO_CD_LOGGING )
				woo_ce_error_log( sprintf( 'Debug: %s', 'before woo_ce_get_orders(): ' . ( time() - $export->start_time ) ) );
			if( $orders = woo_ce_get_orders( 'order', $export->args ) ) {
				if( WOO_CD_LOGGING )
					woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_get_orders(): ' . ( time() - $export->start_time ) ) );
				$export->total_columns = $size = count( $export->columns );
				// XML, RSS export
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
					if( !empty( $export->fields ) ) {
						foreach( $orders as $order ) {

							if( $export->export_format == 'xml' )
								$child = $output->addChild( apply_filters( 'woo_ce_export_xml_order_node', sanitize_key( $export_type ) ) );
							else if( $export->export_format == 'rss' )
								$child = $output->addChild( 'item' );
							if( apply_filters( 'woo_ce_export_xml_order_node_id_attribute', true ) )
								$child->addAttribute( 'id', $order );
							$order = woo_ce_get_order_data( $order, 'order', $export->args, array_keys( $export->fields ) );
							if( WOO_CD_LOGGING )
								woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) ) );
							if( in_array( $export->args['order_items'], array( 'combined', 'unique' ) ) ) {
								// Order items formatting: SPECK-IPHONE|INCASE-NANO|-
								foreach( array_keys( $export->fields ) as $key => $field ) {
									if( isset( $order->$field ) && isset( $export->columns[$key] ) ) {
										if( !is_array( $field ) ) {
											if( woo_ce_is_xml_cdata( $order->$field ) )
												$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
											else
												$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
										}
									}
								}
							} else if( $export->args['order_items'] == 'individual' ) {
								// Order items formatting: SPECK-IPHONE<br />INCASE-NANO<br />-
								if( !empty( $order->order_items ) ) {
									foreach( $order->order_items as $order_item ) {
										$order = apply_filters( 'woo_ce_order_items_individual', $order, $order_item );
										foreach( array_keys( $export->fields ) as $key => $field ) {
											if( isset( $order->$field ) && isset( $export->columns[$key] ) ) {
												if( !is_array( $field ) ) {
													if( woo_ce_is_xml_cdata( $order->$field ) )
														$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
													else
														$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
												}
											}
										}
									}
									unset( $order->order_items );
								} else {
									foreach( array_keys( $export->fields ) as $key => $field ) {
										if( isset( $order->$field ) && isset( $export->columns[$key] ) ) {
											if( !is_array( $field ) ) {
												if( woo_ce_is_xml_cdata( $order->$field ) )
													$child->addChild( sanitize_key( $export->columns[$key] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
												else
													$child->addChild( sanitize_key( $export->columns[$key] ), esc_html( woo_ce_sanitize_xml_string( $order->$field ) ) );
											}
										}
									}
								}
							}

						}
					}
				} else {
					// PHPExcel export
					if( $export->args['order_items'] == 'individual' && isset( $output ) == false )
						$output = array();
					foreach( $orders as $order ) {

						if( in_array( $export->args['order_items'], array( 'combined', 'unique' ) ) ) {
							// Order items formatting: SPECK-IPHONE|INCASE-NANO|-
							$output[] = woo_ce_get_order_data( $order, 'order', $export->args, array_keys( $export->fields ) );
							if( WOO_CD_LOGGING )
								woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) ) );
						} else if( $export->args['order_items'] == 'individual' ) {
							// Order items formatting: SPECK-IPHONE<br />INCASE-NANO<br />-
							$order = woo_ce_get_order_data( $order, 'order', $export->args, array_keys( $export->fields ) );
							if( WOO_CD_LOGGING )
								woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_data(): ' . ( time() - $export->start_time ) ) );
							if( !empty( $order->order_items ) ) {
								foreach( $order->order_items as $order_item ) {
									$order->order_items_id = $order_item->id;
									$order->order_items_product_id = $order_item->product_id;
									$order->order_items_variation_id = $order_item->variation_id;
									if( empty( $order_item->sku ) )
										$order_item->sku = '';
									$order->order_items_sku = $order_item->sku;
									$order->order_items_name = $order_item->name;
									$order->order_items_image_embed = $order_item->image_embed;
									$order->order_items_variation = $order_item->variation;
									$order->order_items_description = $order_item->description;
									$order->order_items_excerpt = $order_item->excerpt;
									$order->order_items_publish_date = $order_item->publish_date;
									$order->order_items_modified_date = $order_item->modified_date;
									$order->order_items_tax_class = $order_item->tax_class;
									$order->order_items_quantity = $order_item->quantity;
									$order->order_items_total = $order_item->total;
									$order->order_items_subtotal = $order_item->subtotal;
									$order->order_items_rrp = $order_item->rrp;
									$order->order_items_stock = $order_item->stock;
									$order->order_items_shipping_class = $order_item->shipping_class;
									$order->order_items_tax = $order_item->tax;
									$order->order_items_tax_subtotal = $order_item->tax_subtotal;
									$order->order_items_refund_subtotal = $order_item->refund_subtotal;
									$order->order_items_refund_quantity = $order_item->refund_quantity;
									$order->order_items_type = $order_item->type;
									$order->order_items_type_id = $order_item->type_id;
									$order->order_items_category = $order_item->category;
									$order->order_items_tag = $order_item->tag;
									$order->order_items_weight = $order_item->weight;
									$order->order_items_width = $order_item->width;
									$order->order_items_length = $order_item->length;
									$order->order_items_height = $order_item->height;
									$order->order_items_total_sales = $order_item->total_sales;
									$order->order_items_total_weight = $order_item->total_weight;
									// Add Order Item weight to Shipping Weight
									if( $order_item->total_weight != '' )
										$order->shipping_weight_total += $order_item->total_weight;
									$order = apply_filters( 'woo_ce_order_items_individual', $order, $order_item );
									// This fixes the Order Items for this Order Items Formatting rule
									$output[] = (object)(array)$order;
									$output = apply_filters( 'woo_ce_order_items_individual_output', $output, $order, $order_item );
								}
							} else {
								$output[] = (object)(array)$order;
							}
						}

					}
				}
				unset( $orders, $order );
			}
			restore_current_blog();
		}
	}
	return $output;

}

// Returns a list of WooCommerce Tax Rates based on existing Orders
function woo_ce_get_order_tax_rates( $order_id = 0 ) {

	global $wpdb;

	$order_item_type = 'tax';
	$tax_rates_sql = $wpdb->prepare( "SELECT order_items.order_item_id as item_id FROM " . $wpdb->prefix . "woocommerce_order_items as order_items WHERE order_items.order_item_type = %s", $order_item_type );
	if( !empty( $order_id ) ) {
		$tax_rates_sql .= $wpdb->prepare( " AND order_items.order_id = %d", $order_id );
	}
	$tax_rates_sql .= " GROUP BY order_items.order_item_name";
	$tax_rates = $wpdb->get_results( $tax_rates_sql, 'ARRAY_A' );
	if( !empty( $tax_rates ) ) {
		$meta_type = 'order_item';
		foreach( $tax_rates as $key => $tax_rate ) {
			$tax_rates[$key]['rate_id'] = get_metadata( $meta_type, $tax_rate['item_id'], 'rate_id', true );
			$tax_rates[$key]['label'] = get_metadata( $meta_type, $tax_rate['item_id'], 'label', true );
			if( !empty( $tax_rates[$key]['rate_id'] ) ) {
				$meta_sql = $wpdb->prepare( "SELECT `tax_rate_class` FROM `" . $wpdb->prefix . "woocommerce_tax_rates` WHERE `tax_rate_id` = %d LIMIT 1", $tax_rates[$key]['rate_id'] );
				$meta = $wpdb->get_var( $meta_sql );
				if( empty( $meta ) )
					$meta = 'Standard';
				$tax_rates[$key]['class'] = $meta;
			}
		}
		return $tax_rates;
	}

}

// Get the Order Item ID and tax rate ID of tax Order Items
function woo_ce_get_order_assoc_tax_rates( $order_id = 0 ) {

	global $wpdb;

	if( !empty( $order_id ) ) {
		$order_item_type = 'tax';
		$meta_key = 'rate_id';
		$order_items_sql = $wpdb->prepare( "SELECT order_items.order_item_id, order_itemmeta.meta_value as rate_id FROM " . $wpdb->prefix . "woocommerce_order_items as order_items, " . $wpdb->prefix . "woocommerce_order_itemmeta as order_itemmeta WHERE order_items.order_item_id = order_itemmeta.order_item_id AND order_items.order_item_type = %s AND order_items.order_id = %d AND order_itemmeta.meta_key = %s", $order_item_type, $order_id, $meta_key );
		$order_items = $wpdb->get_results( $order_items_sql, 'ARRAY_A' );
		if( !empty( $order_items ) )
			return $order_items;
	}

}

// Get the Tax Rate assigned to a given tax rate ID
function woo_ce_get_order_tax_percentage( $tax_rate_id = 0 ) {

	global $wpdb;

	if( !empty( $tax_rate_id ) ) {
		$tax_rate_sql = $wpdb->prepare( "SELECT tax_rates.tax_rate FROM " . $wpdb->prefix . "woocommerce_tax_rates as tax_rates WHERE tax_rates.tax_rate_id = %d LIMIT 1", $tax_rate_id );
		$tax_rate = $wpdb->get_var( $tax_rate_sql );
		if( !empty( $tax_rate ) )
			return $tax_rate;
	}

}

// Return the total tax applied to a specific Tax Rate for a given Order
function woo_ce_get_order_assoc_tax_rate_total( $order_id = 0, $tax_rate = 0 ) {

	global $wpdb;

	$order_item_type = 'tax';
	$meta_key = 'rate_id';
	$order_item_id_sql = $wpdb->prepare( "SELECT order_items.order_item_id FROM " . $wpdb->prefix . "woocommerce_order_items as order_items, " . $wpdb->prefix . "woocommerce_order_itemmeta as order_itemmeta WHERE order_items.order_item_id = order_itemmeta.order_item_id AND order_items.order_item_type = %s AND order_items.order_id = %d AND order_itemmeta.meta_key = %s AND order_itemmeta.meta_value = %d", $order_item_type, $order_id, $meta_key, $tax_rate );
	$order_item_id = $wpdb->get_var( $order_item_id_sql );
	if( !empty( $order_item_id ) ) {
		$amounts_sql = $wpdb->prepare( "SELECT SUM( meta_value ) FROM " . $wpdb->prefix . "woocommerce_order_itemmeta WHERE order_item_id = %d AND meta_key IN ( 'tax_amount', 'shipping_tax_amount' )", $order_item_id );
		$amounts = $wpdb->get_var( $amounts_sql );
		if( !empty( $amounts ) ) {
			return $amounts;
		}
	}

}

// Get the Order Item ID of refunded Order Items
function woo_ce_get_order_line_item_assoc_refunds( $line_item_id = 0 ) {

	global $wpdb;

	$order_item_type = 'line_item';
	$meta_key = '_refunded_item_id';
	$refund_items_sql = $wpdb->prepare( "SELECT order_itemmeta.`order_item_id` FROM `" . $wpdb->prefix . "woocommerce_order_items` as order_items, `" . $wpdb->prefix . "woocommerce_order_itemmeta` as order_itemmeta WHERE order_items.`order_item_id` = order_itemmeta.`order_item_id` AND order_items.`order_item_type` = %s AND order_itemmeta.`meta_key` = %s AND order_itemmeta.`meta_value` = %d", $order_item_type, $meta_key, $line_item_id );
	$refund_items = $wpdb->get_col( $refund_items_sql );
	return $refund_items;

}

// Return the PHP date format for the requested Order Date filter
function woo_ce_get_order_date_filter( $filter = '', $format = '', $date_format = 'd-m-Y' ) {

	$output = false;
	if( !empty( $filter ) && !empty( $format ) ) {
		switch( $filter ) {

			case 'tomorrow':
				if( $format == 'from' )
					$output = date( $date_format, strtotime( 'tomorrow' ) );
				else
					$output = date( $date_format, strtotime( 'tomorrow' ) );
				break;

			case 'today':
				if( $format == 'from' )
					$output = date( $date_format, strtotime( 'today' ) );
				else
					$output = date( $date_format, strtotime( 'tomorrow' ) );
				break;

			case 'yesterday':
				if( $format == 'from' )
					$output = date( $date_format, strtotime( 'yesterday' ) );
				else
					$output = date( $date_format, strtotime( 'yesterday' ) );
				break;

			case 'current_week':
				if( $format == 'from' )
					$output = date( $date_format, strtotime( 'last Monday' ) );
				else
					$output = date( $date_format, strtotime( 'next Monday' ) );
				break;

			case 'last_week':
				if( $format == 'from' )
					$output = date( $date_format, strtotime( '-2 weeks Monday' ) );
				else
					$output = date( $date_format, strtotime( '-1 weeks Monday' ) );
				break;

			case 'current_month':
				if( $format == 'from' )
					$output = date( $date_format, mktime( 0, 0, 0, date( 'n' ), 1 ) );
				else
					$output = date( $date_format, mktime( 0, 0, 0, ( date( 'n' ) + 1 ), 0 ) );
				break;

			case 'last_month':
				if( $format == 'from' )
					$output = date( $date_format, mktime( 0, 0, 0, date( 'n', strtotime( '-1 month' ) ), 1, date( 'Y', strtotime( '-1 month' ) ) ) );
				else
					$output = date( $date_format, mktime( 0, 0, 0, date( 'n' ), 1 ) );
				break;

/*
			case '':
				if( $format == 'from' )
					$output = ;
				else
					$output = ;
					break;
*/

			default:
				woo_ce_error_log( sprintf( 'Warning: %s', sprintf( __( 'Unknown Order Date filter %s provided, defaulted to none', 'woocommerce-exporter' ), $filter ) ) );
				break;

		}
	}
	return $output;

}

// Returns date of first Order received, any status
function woo_ce_get_order_first_date( $date_format = 'd/m/Y' ) {

	$output = date( $date_format, mktime( 0, 0, 0, date( 'n' ), 1 ) );

	$post_type = 'shop_order';
	$args = array(
		'post_type' => $post_type,
		'orderby' => 'post_date',
		'order' => 'ASC',
		'numberposts' => 1,
		'post_status' => 'any'
	);
	$orders = get_posts( $args );
	if( !empty( $orders ) ) {
		$output = date( $date_format, strtotime( $orders[0]->post_date ) );
		unset( $orders );
	}
	return $output;

}

// Returns a list of WooCommerce Order statuses
function woo_ce_get_order_statuses() {

	$terms = false;
	// Check if this is a WooCommerce 2.2+ instance (new Post Status)
	$woocommerce_version = woo_get_woo_version();
	if( version_compare( $woocommerce_version, '2.2' ) >= 0 ) {
		// Convert Order Status array into our magic sauce
		$order_statuses = ( function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : false );
		if( !empty( $order_statuses ) ) {
			$terms = array();
			$post_type = 'shop_order';
			$posts_count = wp_count_posts( $post_type );
			foreach( $order_statuses as $key => $order_status ) {
				$terms[] = (object)array(
					'name' => $order_status,
					'slug' => $key,
					'count' => ( isset( $posts_count->$key ) ? $posts_count->$key : 0 )
				);
			}
		}
	} else {
		$args = array(
			'hide_empty' => false
		);
		$terms = get_terms( 'shop_order_status', $args );
		if( empty( $terms ) || ( is_wp_error( $terms ) == true ) )
			$terms = false;
	}
	return $terms;

}

// Returns the Shipping Method ID associated to a specific Order
function woo_ce_get_order_assoc_shipping_method_id( $order_id = 0 ) {

	if( empty( $order_id ) )
		return false;

	if( class_exists( 'WC_Order' ) ) {
		$output = '';
		$order = new WC_Order( $order_id );
		if( method_exists( 'WC_Order', 'get_shipping_methods' ) ) {
			if( $shipping_methods = $order->get_shipping_methods() ) {
				foreach( $shipping_methods as $shipping_item_id => $shipping_item ) {
					if( isset( $shipping_item['method_id'] ) ) {
						$output = $shipping_item['method_id'];
					}
					if( empty( $output ) && isset( $shipping_item['item_meta'] ) ) {
						$output = ( isset( $shipping_item['item_meta']['method_id'] ) ? $shipping_item['item_meta']['method_id'] : false );
						if( is_array( $output ) )
							$output = ( isset( $output[0] ) ? $output[0] : false );
						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_assoc_shipping_method_id(), WC_Order->get_shipping_methods() returned output, output: ' . $output ) );
						break;
					}
					// Check if a value has been set
					if( !empty( $output ) )
						break;
				}
			}
			unset( $shipping_methods );
		} else {
			$output = get_post_meta( $order_id, '_shipping_method', true );
			if( is_array( $output ) )
				$output = ( isset( $output[0] ) ? $output[0] : false );
			if( WOO_CD_LOGGING )
				woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_order_assoc_shipping_method_id(), Post meta _shipping_method returned output, output: ' . $output ) );
		}
		unset( $order );
		return apply_filters( 'woo_ce_get_order_assoc_shipping_method_id', $output );
	} else {
		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Warning: %s', 'woo_ce_get_order_assoc_shipping_method_id() returned false, reason: WC_Order Class does not exist' ) );
	}

}

// Returns Download keys associated to a specified Order
function woo_ce_get_order_assoc_downloads( $order_id = 0 ) {

	global $wpdb;

	if( !empty( $order_id ) ) {
		$order_downloads_sql = $wpdb->prepare( "SELECT `download_id`, `download_count` FROM `" . $wpdb->prefix . "woocommerce_downloadable_product_permissions` WHERE `order_id` = %d", $order_id );
		$order_downloads = $wpdb->get_results( $order_downloads_sql );
		$output = array();
		if( !empty( $order_downloads ) ) {
			$output = $order_downloads;
		}
		unset( $order_downloads );
		return $output;
	}

}

// Returns Order Notes associated to a specific Order
function woo_ce_get_order_assoc_notes( $order_id = 0, $note_type = 'order_note' ) {

	global $wpdb;

	if( !empty( $order_id ) ) {
		$term_taxonomy = 'order_note';
		// @mod - The default get_comments() call is not working for returning Order Notes or Customer Notes, using database query. Check in 2.4+
		$order_notes_sql = $wpdb->prepare( "SELECT `comment_ID`, `comment_date`, `comment_content` FROM `" . $wpdb->comments . "` WHERE `comment_type` = %s AND `comment_post_ID` = %d AND `comment_agent` = 'WooCommerce' AND `comment_approved` = 1", $term_taxonomy, $order_id );
		$order_notes = $wpdb->get_results( $order_notes_sql );
		$wpdb->flush();
		$output = array();
		if( !empty( $order_notes ) ) {
			foreach( $order_notes as $order_note ) {
				// Check if we are returning an order or customer note
				$order_note->comment_date = sprintf( apply_filters( 'woo_ce_get_order_assoc_notes_date', '%s %s' ), woo_ce_format_date( $order_note->comment_date ), ( function_exists( 'wc_format_datetime' ) ? wc_format_datetime( $order_note->comment_date, get_option( 'time_format' ) ) : mysql2date( 'H:i:s', $order_note->comment_date ) ) );
				if( $note_type == 'customer_note' ) {
					// Check if the order note is a customer one
					if( absint( get_comment_meta( $order_note->comment_ID, 'is_customer_note', true ) ) == 1 )
						$output[] = sprintf( apply_filters( 'woo_ce_get_order_assoc_notes_customer', '%s: %s' ), $order_note->comment_date, $order_note->comment_content );
				} else {
					// Check if the order note is a customer one
					if( absint( get_comment_meta( $order_note->comment_ID, 'is_customer_note', true ) ) == 0 )
						$output[] = sprintf( apply_filters( 'woo_ce_get_order_assoc_notes_order', '%s: %s' ), $order_note->comment_date, $order_note->comment_content );
				}
			}
		}
		return $output;
	}

}

function woo_ce_get_order_assoc_refund_date( $order_id = 0 ) {

	if( !empty( $order_id ) ) {

		$output = '';
		$post_type = 'shop_order_refund';
		$args = array(
			'post_type' => $post_type,
			'post_status' => 'wc-completed',
			'post_parent' => $order_id,
			'posts_per_page' => -1
		);
		$args = apply_filters( 'woo_ce_get_order_assoc_refund_date', $args, $order_id );
		$refunds = new WP_Query( $args );
		if( !empty( $refunds->posts ) ) {
			foreach( $refunds->posts as $refund ) {
				if( apply_filters( 'woo_ce_override_get_order_assoc_refund_date_filter', false ) ) {
					// This will return the latest partial refund regardless of whether it is fully refunded or not
					$output = woo_ce_format_date( $refund->post_date );
				} else {
					// This will limit the refund date to only Orders fully refunded
					if( $refund->post_excerpt == __( 'Order Fully Refunded', 'woocommerce' ) ) {
						$output = woo_ce_format_date( $refund->post_date );
						break;
					}
				}
			}
		}
		return $output;
	}

}

// Returns the Coupon Code associated to a specific Order
function woo_ce_get_order_assoc_coupon( $order_id = 0 ) {

	global $export;

	if( !empty( $order_id ) ) {
		$output = '';
		$order_item_type = 'coupon';
		if( class_exists( 'WC_Order' ) ) {
			$order = new WC_Order( $order_id );
			if( method_exists( $order, 'get_used_coupons' ) ) {
				if( $coupons = $order->get_used_coupons() ) {
					$size = count( $coupons );
					// If more than a single Coupon is assigned to this order then separate them
					if( $size > 1 )
						$output = implode( $export->category_separator, $coupons );
					else
						$output = $coupons[0];
				}
			}
		}
		return $output;
	}

}

// Returns a list of Order ID's where a Coupon is associated
function woo_ce_get_orders_by_coupon( $post_name = '' ) {

	global $wpdb;

	if( !empty( $post_name ) ) {
		$order_item_type = 'coupon';
		$order_items_sql = $wpdb->prepare( "SELECT order_items.`order_id` as order_id FROM `" . $wpdb->prefix . "woocommerce_order_items` as order_items WHERE order_items.`order_item_name` = %s AND order_items.`order_item_type` = %s", $post_name, $order_item_type );
		if( $order_items = $wpdb->get_col( $order_items_sql ) ) {

			// Allow Plugin/Theme authors to extend the Order ID's returned
			$order_items = apply_filters( 'woo_ce_extend_get_orders_by_coupon', $order_items );

			return $order_items;
		}
	}

}

function woo_ce_max_order_items( $orders = array() ) {

	$output = 0;
	if( $orders ) {
		foreach( $orders as $order ) {
			if( $order->order_items )
				$output = count( $order->order_items[0]->name );
		}
	}
	return $output;

}

// Returns a list of Order Item ID's with the order_item_type of 'line item' for a specified Order
function woo_ce_get_order_item_ids( $order_id = 0 ) {

	global $wpdb;

	if( !empty( $order_id ) ) {
		$order_item_type = 'line_item';
		$order_items_sql = $wpdb->prepare( "SELECT order_items.`order_item_id` as id, order_itemmeta.`meta_value` as product_id FROM `" . $wpdb->prefix . "woocommerce_order_items` as order_items, `" . $wpdb->prefix . "woocommerce_order_itemmeta` as order_itemmeta WHERE order_items.`order_item_id` = order_itemmeta.`order_item_id` AND order_items.`order_id` = %d AND order_items.`order_item_type` = %s AND order_itemmeta.`meta_key` IN ('_product_id')", $order_id, $order_item_type );
		if( $order_items = $wpdb->get_results( $order_items_sql ) )
			return $order_items;
	}

}

// Returns a list of Order Items for a specified Order
function woo_ce_get_order_items( $order_id = 0, $order_items_types = array() ) {

	global $export, $wpdb;

	$upload_dir = wp_upload_dir();

	if( !empty( $order_id ) ) {
		$order_items_sql = $wpdb->prepare( "SELECT `order_item_id` as id, `order_item_name` as name, `order_item_type` as type FROM `" . $wpdb->prefix . "woocommerce_order_items` WHERE `order_id` = %d", $order_id );

		// Allow Plugin/Theme authors to extend the Order Items query
		$order_items_sql = apply_filters( 'woo_ce_get_order_items_sql', $order_items_sql, $order_id );

		if( $order_items = $wpdb->get_results( $order_items_sql ) ) {
			$wpdb->flush();

			// Default to Line Item for empty Order Item types
			if( empty( $order_items_types ) )
				$order_items_types = array( 'line_item' );

			// Allow Plugin/Theme authors to add support for sorting Order Items
			$order_items = apply_filters( 'woo_ce_get_order_items_pre', $order_items, $order_id );

			$attributes = array();
			if( apply_filters( 'woo_ce_enable_product_attributes', true ) )
				$attributes = woo_ce_get_product_attributes();

			foreach( $order_items as $key => $order_item ) {

				// Filter Order Item types from Orders export
				if( !in_array( $order_item->type, $order_items_types ) ) {
					unset( $order_items[$key] );
					continue;
				}

				// Fetch all meta keys linked to an Order Item
				$order_item_meta_sql = $wpdb->prepare( "SELECT `meta_key`, `meta_value` FROM `" . $wpdb->prefix . "woocommerce_order_itemmeta` WHERE `order_item_id` = %d ORDER BY meta_key ASC", $order_item->id );
				if( $order_item_meta = $wpdb->get_results( $order_item_meta_sql ) ) {
					$order_items[$key]->product_id = '';
					$order_items[$key]->variation_id = '';
					$order_items[$key]->sku = '';
					$order_items[$key]->image_embed = '';
					$order_items[$key]->description = '';
					$order_items[$key]->excerpt = '';
					$order_items[$key]->publish_date = '';
					$order_items[$key]->modified_date = '';
					$order_items[$key]->variation = '';
					$order_items[$key]->quantity = '';
					$order_items[$key]->total = '';
					$order_items[$key]->subtotal = '';
					$order_items[$key]->rrp = '';
					$order_items[$key]->stock = '';
					$order_items[$key]->shipping_class = '';
					$order_items[$key]->tax = '';
					$order_items[$key]->tax_subtotal = '';
					$order_items[$key]->tax_class = '';
					$order_items[$key]->category = '';
					$order_items[$key]->tag = '';
					$order_items[$key]->weight = '';
					$order_items[$key]->height = '';
					$order_items[$key]->width = '';
					$order_items[$key]->length = '';
					$order_items[$key]->total_sales = '';
					$order_items[$key]->total_weight = '';
					$size = count( $order_item_meta );
					for( $i = 0; $i < $size; $i++ ) {

						// Go through each Order Item meta found
						switch( $order_item_meta[$i]->meta_key ) {

							case '_qty':
								$order_items[$key]->quantity = $order_item_meta[$i]->meta_value;
								break;

							case '_product_id':
								if( $order_items[$key]->product_id = $order_item_meta[$i]->meta_value ) {
									$product = get_post( $order_items[$key]->product_id );
									if( $product !== null ) {
										$order_items[$key]->description = woo_ce_format_description_excerpt( $product->post_content );
										$order_items[$key]->excerpt = woo_ce_format_description_excerpt( $product->post_excerpt );
										$order_items[$key]->publish_date = woo_ce_format_date( $product->post_date );
										$order_items[$key]->modified_date = woo_ce_format_date( $product->post_modified );
									}
									unset( $product );

									// Populate the Featured Image thumbnail
									if( isset( $export->export_format ) && $export->export_format == 'xlsx' ) {
										$image_id = woo_ce_get_product_assoc_featured_image( $order_items[$key]->product_id, false, 'image_id' );
										if( $metadata = wp_get_attachment_metadata( $image_id ) ) {
											// Override for the image embed thumbnail size; use registered WordPress image size names
											$thumbnail_size = apply_filters( 'woo_ce_override_embed_thumbnail_size', 'shop_thumbnail' );
											if( isset( $metadata['sizes'][$thumbnail_size] ) && $metadata['sizes'][$thumbnail_size]['file'] ) {
												$image_path = pathinfo( $metadata['file'] );
												$order_items[$key]->image_embed = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'];
												// Override for using relative image embed filepath
												if( !file_exists( trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'] ) || apply_filters( 'woo_ce_override_image_embed_relative_path', false ) )
													$order_items[$key]->image_embed = trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'];
											}
										}
										unset( $image_id, $metadata, $thumbnail_size, $image_path );
									}

									$order_items[$key]->sku = get_post_meta( $order_items[$key]->product_id, '_sku', true );
									$order_items[$key]->category = woo_ce_get_product_assoc_categories( $order_items[$key]->product_id );
									$order_items[$key]->tag = woo_ce_get_product_assoc_tags( $order_items[$key]->product_id );
									$order_items[$key]->weight = get_post_meta( $order_items[$key]->product_id, '_weight', true );
									$order_items[$key]->height = get_post_meta( $order_items[$key]->product_id, '_height', true );
									$order_items[$key]->width = get_post_meta( $order_items[$key]->product_id, '_width', true );
									$order_items[$key]->length = get_post_meta( $order_items[$key]->product_id, '_length', true );
									$order_items[$key]->total_sales = get_post_meta( $order_items[$key]->product_id, 'total_sales', true );
									$order_items[$key]->rrp = get_post_meta( $order_items[$key]->product_id, '_price', true );
									if( isset( $order_items[$key]->rrp ) && $order_items[$key]->rrp != '' )
										$order_items[$key]->rrp = woo_ce_format_price( $order_items[$key]->rrp );
									$order_items[$key]->stock = get_post_meta( $order_items[$key]->product_id, '_stock', true );
									$order_items[$key]->stock = ( function_exists( 'wc_stock_amount' ) ? wc_stock_amount( $order_items[$key]->stock ) : $order_items[$key]->stock );
									$order_items[$key]->shipping_class = woo_ce_get_product_assoc_shipping_class( $order_items[$key]->product_id );

									// Populate the Order Items: %Attribute% Attribute fields
									if( !empty( $attributes ) ) {
										$product_attributes = maybe_unserialize( get_post_meta( $order_items[$key]->product_id, '_product_attributes', true ) );
										if( !empty( $product_attributes ) ) {
											// Check for Taxonomy-based attributes
											foreach( $attributes as $attribute ) {
												if( isset( $product_attributes[sprintf( 'pa_%s', sanitize_key( $attribute->attribute_name ) )] ) ) {
													$args = array(
														'attribute' => $product_attributes[sprintf( 'pa_%s', sanitize_key( $attribute->attribute_name ) )],
														'type' => 'product'
													);
													$order_items[$key]->{sprintf( 'product_attribute_%s', sanitize_key( $attribute->attribute_name ) )} = woo_ce_get_product_assoc_attributes( $order_items[$key]->product_id, $args );
												} else {
													$args = array(
														'attribute' => $attribute,
														'type' => 'global'
													);
													$order_items[$key]->{sprintf( 'product_attribute_%s', sanitize_key( $attribute->attribute_name ) )} = woo_ce_get_product_assoc_attributes( $order_items[$key]->product_id, $args );
												}
											}
											// Check for per-Product attributes (custom)
											foreach( $product_attributes as $attribute_key => $attribute ) {
												if( $attribute['is_taxonomy'] == 0 ) {
													if( !isset( $order_items[$key]->{sprintf( 'product_attribute_%s', sanitize_key( $attribute_key ) )} ) )
														$order_items[$key]->{sprintf( 'product_attribute_%s', sanitize_key( $attribute_key ) )} = $attribute['value'];
												}
											}
										}
									}

									// Override Variable Product Type with total stock quantity of all Variations
									$term_taxonomy = 'product_type';
									if( has_term( 'variable', $term_taxonomy, $order_items[$key]->product_id ) ) {
										$_product = ( function_exists( 'wc_get_product' ) ? wc_get_product( $order_items[$key]->product_id ) : false );
										if( version_compare( woo_get_woo_version(), '2.7', '>=' ) )
											$order_items[$key]->stock = ( method_exists( $_product, 'get_stock_quantity' ) ? $_product->get_stock_quantity() : $order_items[$key]->stock );
										else
											$order_items[$key]->stock = ( method_exists( $_product, 'get_total_stock' ) ? $_product->get_total_stock() : $order_items[$key]->stock );
										unset( $_product );
									}

								}
								break;

							case '_variation_id':
								$order_items[$key]->variation = '';
								if( $order_items[$key]->variation_id = $order_item_meta[$i]->meta_value ) {

									// Populate the Featured Image thumbnail
									if( isset( $export->export_format ) && $export->export_format == 'xlsx' ) {
										$image_id = woo_ce_get_product_assoc_featured_image( $order_items[$key]->variation_id, false, 'image_id' );
										if( $metadata = wp_get_attachment_metadata( $image_id ) ) {
											// Override for the image embed thumbnail size; use registered WordPress image size names
											$thumbnail_size = apply_filters( 'woo_ce_override_embed_thumbnail_size', 'shop_thumbnail' );
											if( isset( $metadata['sizes'][$thumbnail_size] ) && $metadata['sizes'][$thumbnail_size]['file'] ) {
												$image_path = pathinfo( $metadata['file'] );
												$order_items[$key]->image_embed = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'];
												// Override for using relative image embed filepath
												if( !file_exists( trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'] ) || apply_filters( 'woo_ce_override_image_embed_relative_path', false ) )
													$order_items[$key]->image_embed = trailingslashit( $image_path['dirname'] ) . $metadata['sizes'][$thumbnail_size]['file'];
											}
										}
										unset( $image_id, $metadata, $thumbnail_size, $image_path );
									}

									// Check if the Variation SKU is set and default to the Product SKU if it is empty
									$variation_sku = get_post_meta( $order_items[$key]->variation_id, '_sku', true );
									if( !empty( $variation_sku ) )
										$order_items[$key]->sku = $variation_sku;
									unset( $variation_sku );
									$order_items[$key]->weight = get_post_meta( $order_items[$key]->variation_id, '_weight', true );
									$order_items[$key]->height = get_post_meta( $order_items[$key]->variation_id, '_height', true );
									$order_items[$key]->width = get_post_meta( $order_items[$key]->variation_id, '_width', true );
									$order_items[$key]->length = get_post_meta( $order_items[$key]->variation_id, '_length', true );
									$order_items[$key]->rrp = get_post_meta( $order_items[$key]->variation_id, '_price', true );
									if( isset( $order_items[$key]->rrp ) && $order_items[$key]->rrp != '' )
										$order_items[$key]->rrp = woo_ce_format_price( $order_items[$key]->rrp );

									$variations_sql = "SELECT `meta_key` FROM `" . $wpdb->postmeta . "` WHERE `post_id` = " . $order_items[$key]->variation_id . " AND `meta_key` LIKE 'attribute_pa_%' ORDER BY `meta_key` ASC";
									if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
										woo_ce_error_log( sprintf( 'Debug: %s', 'variations_sql: ' . $variations_sql ) );
									$variations = $wpdb->get_col( $variations_sql );
									if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
										woo_ce_error_log( sprintf( 'Debug: %s', 'global attributes: ' . print_r( $variations, true ) ) );

									// Check if the Variation has a Term Taxonomy
									if( !empty( $variations ) ) {
										if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
											woo_ce_error_log( sprintf( 'Debug: %s', 'Global Attributes found' ) );
										foreach( $variations as $variation ) {
											$variation = str_replace( 'attribute_pa_', '', $variation );
											$variation_label = '';
											if( !empty( $variation ) ) {
												if( !empty( $attributes ) ) {
													foreach( $attributes as $attribute ) {
														if( $attribute->attribute_name == $variation ) {
															if( empty( $attribute->attribute_label ) )
																$attribute->attribute_label = $attribute->attribute_name;
															$variation_label = $attribute->attribute_label;
															break;
														}
													}
												}
											}
											$slug = get_post_meta( $order_items[$key]->variation_id, sprintf( 'attribute_pa_%s', $variation ), true );
											if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
												woo_ce_error_log( sprintf( 'Debug: %s', 'slug: ' . $slug ) );
											$term_taxonomy = sprintf( 'pa_%s', $variation );
											if( taxonomy_exists( $term_taxonomy ) ) {
												if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
													woo_ce_error_log( sprintf( 'Debug: %s', 'term taxonomy exists' ) );
												$term = get_term_by( 'slug', $slug, $term_taxonomy );
												if( $term && !is_wp_error( $term ) )
													$order_items[$key]->variation .= sprintf( apply_filters( 'woo_ce_get_order_items_variation_taxonomy', '%s: %s' ), apply_filters( 'woo_ce_get_order_items_variation_taxonomy_label', $variation_label, $term_taxonomy ), apply_filters( 'woo_ce_get_order_items_variation_taxonomy_term', $term->name, $term ) ) . "|";
											} else {
												if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
													woo_ce_error_log( sprintf( 'Debug: %s', 'term taxonomy does not exist' ) );
											}
										}
										$order_items[$key]->variation = substr( $order_items[$key]->variation, 0, -1 );
										unset( $variations, $variation, $variation_label, $slug, $term_taxonomy, $term );
									} else {
										if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) ) {
											woo_ce_error_log( sprintf( 'Debug: %s', 'no Global Attributes found' ) );
											woo_ce_error_log( sprintf( 'Debug: %s', 'checking for per-Product Attributes' ) );
										}
										$variations_sql = "SELECT `meta_key` FROM `" . $wpdb->postmeta . "` WHERE `post_id` = " . $order_items[$key]->variation_id . " AND `meta_key` LIKE 'attribute_%' ORDER BY `meta_key` ASC";
										if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
											woo_ce_error_log( sprintf( 'Debug: %s', 'variations_sql: ' . $variations_sql ) );
										$variations = $wpdb->get_col( $variations_sql );
										if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
											woo_ce_error_log( sprintf( 'Debug: %s', 'per-Product Attributes: ' . print_r( $variations, true ) ) );

										// Bolt-on custom Attributes set on Quick Export
										$custom_attributes = woo_ce_get_option( 'custom_attributes', '' );
										if( !empty( $custom_attributes ) ) {
											if( empty( $variations ) )
												$variations = array();
											foreach( $custom_attributes as $custom_attribute ) {
												// Check if this custom Attribute is already in the list
												if( !empty( $custom_attribute ) && !in_array( sprintf( 'attribute_%s', sanitize_key( str_replace( ' ', '-', $custom_attribute ) ) ), $variations ) )
													$variations[] = sprintf( 'attribute_%s', sanitize_key( str_replace( ' ', '-', $custom_attribute ) ) );
											}
										}
										unset( $custom_attributes );

										// Check for per-Product Attributes that are not linked to a Taxonomy
										if( !empty( $variations ) ) {
											if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
												woo_ce_error_log( sprintf( 'Debug: %s', 'per-Product Attributes found' ) );
											$product_attributes = get_post_meta( $order_items[$key]->product_id, '_product_attributes', true );
											if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
												woo_ce_error_log( sprintf( 'Debug: %s', 'product_attributes: ' . print_r( $product_attributes, true ) ) );
											foreach( $variations as $variation ) {
												$variation = str_replace( 'attribute_', '', $variation );
												if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
													woo_ce_error_log( sprintf( 'Debug: %s', 'variation: ' . $variation ) );
												$variation_label = '';
												// Generate the Variation Label
												if( !empty( $product_attributes ) ) {
													if( isset( $product_attributes[$variation] ) )
														$variation_label = $product_attributes[$variation]['name'];
												}
												if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
													woo_ce_error_log( sprintf( 'Debug: %s', 'variation_label: ' . $variation_label ) );
												$slug = get_post_meta( $order_items[$key]->variation_id, sprintf( 'attribute_%s', $variation ), true );
												// Check if the Attribute Slug is empty, default to per-Product text Attribute
												if( empty( $slug ) )
													$slug = wc_get_order_item_meta( $order_item->id, $variation );
												if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
													woo_ce_error_log( sprintf( 'Debug: %s', 'slug: ' . $slug ) );
												if( !empty( $slug ) && !empty( $variation_label ) )
													$order_items[$key]->variation .= sprintf( apply_filters( 'woo_ce_get_order_items_variation_custom', '%s: %s' ), apply_filters( 'woo_ce_get_order_items_variation_custom_label', $variation_label, $variation ), apply_filters( 'woo_ce_get_order_items_variation_custom_slug', ucwords( $slug ), $variation ) ) . "\n";
											}
											$order_items[$key]->variation = substr( $order_items[$key]->variation, 0, -1 );
											unset( $variations, $variation, $variation_label, $product_attributes, $slug );
										} else {
											if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_product_attributes', false ) )
												woo_ce_error_log( sprintf( 'Debug: %s', 'no per-Product Attributes found' ) );
										}
									}
								}
								break;

							case '_tax_class':
								$order_items[$key]->tax_class = $order_item_meta[$i]->meta_value;
								// $order_items[$key]->tax_class = woo_ce_format_order_item_tax_class( $order_item_meta[$i]->meta_value );
								break;

							case '_line_subtotal':
								$order_items[$key]->subtotal = woo_ce_format_price( $order_item_meta[$i]->meta_value );
								break;

							case '_line_subtotal_tax':
								$order_items[$key]->tax_subtotal = woo_ce_format_price( $order_item_meta[$i]->meta_value );
								break;

							case '_line_total':
								$order_items[$key]->total = woo_ce_format_price( $order_item_meta[$i]->meta_value );
								break;

							case '_line_tax':
								$order_items[$key]->tax = woo_ce_format_price( $order_item_meta[$i]->meta_value );
								break;

							// This is for Order Item tax meta, we can safely ignore
							case '_line_tax_data':
								continue;
								break;

							// This is for any custom Order Item meta
							default:
								$order_items[$key] = apply_filters( 'woo_ce_order_item_custom_meta', $order_items[$key], $order_item_meta[$i]->meta_key, $order_item_meta[$i]->meta_value );
								break;

						}
					}
				}
				unset( $order_item_meta );

				if( !empty( $order_items[$key]->tax_class ) || ( empty( $order_items[$key]->tax_class ) && !empty( $order_items[$key]->tax ) ) ) {
					// Tax Rates
					if( !empty( $order_items[$key]->tax_class ) ) {
						$tax_rates = woo_ce_get_order_tax_rates( $order_id );
					} else {
						$tax_rates = woo_ce_get_order_tax_rates();
						$order_items[$key]->tax_class = 'Standard';
					}
					if( !empty( $tax_rates ) ) {
						foreach( $tax_rates as $tax_rate ) {
							$tax_rate['class'] = ( isset( $tax_rate['class'] ) ? $tax_rate['class'] : 'Standard' );
							if( sanitize_title_with_dashes( $tax_rate['class'] ) == sanitize_title_with_dashes( $order_items[$key]->tax_class ) ) {
								$order_items[$key]->{sprintf( 'tax_rate_%d', $tax_rate['rate_id'] )} = $order_items[$key]->tax_subtotal;
								break;
							}
						}
					}
					unset( $tax_rates );
				}

				// Default the quantity to 1 for the Fee Order Item Type
				if( $order_items[$key]->type == 'fee' )
					$order_items[$key]->quantity = 1;

				$order_items[$key]->type_id = $order_items[$key]->type;

				// Check for the Refund Line Item
				$order_items[$key]->refund_subtotal = 0;
				$order_items[$key]->refund_quantity = 0;
				if( $refunds = woo_ce_get_order_line_item_assoc_refunds( $order_items[$key]->id ) ) {
					$refund_subtotal = 0;
					$refund_quantity = 0;
					foreach( $refunds as $refund ) {
						switch( $order_items[$key]->type_id ) {

							case 'shipping':
								$refund_subtotal += wc_get_order_item_meta( $refund, '_cost' );
								break;

							default:
								$refund_subtotal += wc_get_order_item_meta( $refund, '_line_total' );
								break;

						}
						$refund_quantity += wc_get_order_item_meta( $refund, '_qty' );
					}
					$order_items[$key]->refund_subtotal = woo_ce_format_price( $refund_subtotal );
					$order_items[$key]->refund_quantity = $refund_quantity;
					unset( $refund_subtotal, $refund_quantity, $refunds, $refund );
				}

				$order_items[$key] = apply_filters( 'woo_ce_order_item', $order_items[$key], $order_id );
				$order_items[$key]->type = woo_ce_format_order_item_type( $order_items[$key]->type );
				$order_items[$key]->total_weight = ( $order_items[$key]->weight <> '' ? $order_items[$key]->weight * $order_items[$key]->quantity : '' );

			}

			// Allow Plugin/Theme authors to add support for filtering Order Items
			$order_items = apply_filters( 'woo_ce_get_order_items', $order_items, $order_id );

			return $order_items;

		}
	}

}

// Returns a list of WooCommerce Order Item Types
function woo_ce_get_order_items_types() {

	$order_item_types = array(
		'line_item' => __( 'Line Item', 'woocommerce-exporter' ),
		'coupon' => __( 'Coupon', 'woocommerce-exporter' ),
		'fee' => __( 'Fee', 'woocommerce-exporter' ),
		'tax' => __( 'Tax', 'woocommerce-exporter' ),
		'shipping' => __( 'Shipping', 'woocommerce-exporter' )
	);

	// Allow Plugin/Theme authors to add support for additional Order Item types
	$order_item_types = apply_filters( 'woo_ce_order_item_types', $order_item_types );

	return $order_item_types;

}

// Return the Order Status for a specified Order
function woo_ce_get_order_status( $order_id = 0 ) {

	global $export;

	$output = '';
	// Check if this is a WooCommerce 2.2+ instance (new Post Status)
	$woocommerce_version = woo_get_woo_version();
	if( version_compare( $woocommerce_version, '2.2' ) >= 0 ) {
		$output = get_post_status( $order_id );
		$terms = ( function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array() );
		if( isset( $terms[$output] ) )
			$output = $terms[$output];
	} else {
		$term_taxonomy = 'shop_order_status';
		$status = wp_get_object_terms( $order_id, $term_taxonomy );
		if( !empty( $status ) && is_wp_error( $status ) == false ) {
			$size = count( $status );
			for( $i = 0; $i < $size; $i++ ) {
				if( $term = get_term( $status[$i]->term_id, $term_taxonomy ) ) {
					$output .= $term->name . $export->category_separator;
					unset( $term );
				}
			}
			$output = substr( $output, 0, -1 );
		}
	}
	return $output;

}

function woo_ce_get_order_payment_gateways() {

	global $woocommerce;

	$output = false;

	// Test that payment gateways exist with WooCommerce 1.6 compatibility
	if( version_compare( $woocommerce->version, '2.0.0', '<' ) ) {
		if( $woocommerce->payment_gateways )
			$output = $woocommerce->payment_gateways->payment_gateways;
	} else {
		if( $woocommerce->payment_gateways() )
			$output = $woocommerce->payment_gateways()->payment_gateways();
	}
	// Add Other to list of payment gateways
	$output['other'] = (object)array(
		'id' => 'other',
		'title' => __( 'Other', 'woocommerce-exporter' ),
		'method_title' => __( 'Other', 'woocommerce-exporter' )
	);
	return $output;

}

function woo_ce_format_order_payment_gateway( $payment_id = '' ) {

	$output = $payment_id;
	$payment_gateways = woo_ce_get_order_payment_gateways();
	if( !empty( $payment_gateways ) ) {
		foreach( $payment_gateways as $payment_gateway ) {
			if( $payment_gateway->id == $payment_id ) {
				if( method_exists( $payment_gateway, 'get_title' ) )
					$output = $payment_gateway->get_title();
				else
					$output = $payment_id;
				break;
			}
		}
		unset( $payment_gateways, $payment_gateway );
	}
	if( empty( $payment_id ) )
		$output = __( 'N/A', 'woocommerce-exporter' );
	return $output;

}

function woo_ce_get_order_payment_gateway_usage( $payment_id = '' ) {

	$output = 0;
	if( !empty( $payment_id ) ) {
		$post_type = 'shop_order';
		$args = array(
			'post_type' => $post_type,
			'numberposts' => 1,
			'post_status' => 'any',
			'meta_query' => array(
				array(
					'key' => '_payment_method',
					'value' => $payment_id
				)
			),
			'fields' => 'ids'
		);
		$order_ids = new WP_Query( $args );
		$output = absint( $order_ids->found_posts );
		unset( $order_ids );
	}
	return $output;

}

function woo_ce_get_order_shipping_methods() {

	global $woocommerce;

	$output = false;

	// Test that payment gateways exist with WooCommerce 1.6 compatibility
	if( version_compare( $woocommerce->version, '2.0.0', '<' ) ) {
		if( $woocommerce->shipping )
			$output = $woocommerce->shipping->shipping_methods;
	} else {
		if( $woocommerce->shipping() )
			$output = $woocommerce->shipping->load_shipping_methods();
	}

/*
	// @mod - Adding support for Shipping Zones, coming in 2.3+
	// woo_ce_error_log( sprintf( 'Debug: %s', 'before:' ) );
	// woo_ce_error_log( sprintf( 'Debug: %s', print_r( $output, true ) ) );

	// $output = WC()->shipping->get_shipping_methods();
	// woo_ce_error_log( sprintf( 'Debug: %s', 'after:' ) );
	// woo_ce_error_log( sprintf( 'Debug: %s', print_r( $output, true ) ) );

	// woo_ce_error_log( sprintf( 'Debug: %s', absint( $woocommerce->shipping() ) ) );
	// woo_ce_error_log( sprintf( 'Debug: %s', 'shipping_methods (legacy): ' . print_r( $woocommerce->shipping->shipping_methods, true ) ) );
	// woo_ce_error_log( sprintf( 'Debug: %s', 'load_shipping_methods: ' . print_r( $woocommerce->shipping->load_shipping_methods(), true ) ) );

	$zones = array();

	// Rest of the World zone
	$zone                                                     = new \WC_Shipping_Zone();
	$zones[ $zone->get_zone_id() ]                            = $zone->get_data();
	$zones[ $zone->get_zone_id() ]['formatted_zone_location'] = $zone->get_formatted_location();
	$zones[ $zone->get_zone_id() ]['shipping_methods']        = $zone->get_shipping_methods();

	// Add user configured zones
	$zones = array_merge( $zones, \WC_Shipping_Zones::get_zones() );
	woo_ce_error_log( sprintf( 'Debug: %s', 'zones:' ) );
	woo_ce_error_log( sprintf( 'Debug: %s', print_r( $zones, true ) ) );
*/

	// Allow Plugin/Theme authors to add support for additional Shipping Methods
	$output = apply_filters( 'woo_ce_get_order_shipping_methods', $output );

	return $output;

}

function woo_ce_format_order_shipping_method( $shipping_id = '' ) {

	global $woocommerce;

	$output = $shipping_id;
	$shipping_methods = woo_ce_get_order_shipping_methods();
	if( !empty( $shipping_methods ) ) {
		foreach( $shipping_methods as $shipping_method ) {
			if( $shipping_method->id == $shipping_id ) {
				if( method_exists( $shipping_method, 'get_title' ) )
					$output = $shipping_method->get_title();
				else if( isset( $shipping_method->title ) )
					$output = $shipping_method->title;
				else
					$output = $shipping_id;
				break;
			}
		}
		unset( $shipping_methods );
	}
	if( empty( $shipping_id ) )
		$output = __( 'N/A', 'woocommerce-exporter' );
	if( empty( $output ) )
		$output = $shipping_id;
	return $output;

}

function woo_ce_format_order_item_type( $line_type = '' ) {

	$output = $line_type;
	switch( $line_type ) {

		case 'line_item':
			$output = __( 'Product', 'woocommerce-exporter' );
			break;

		case 'fee':
			$output = __( 'Fee', 'woocommerce-exporter' );
			break;

		case 'shipping':
			$output = __( 'Shipping', 'woocommerce-exporter' );
			break;

		case 'tax':
			$output = __( 'Tax', 'woocommerce-exporter' );
			break;

		case 'coupon':
			$output = __( 'Coupon', 'woocommerce-exporter' );
			break;

	}
	return $output;

}

function woo_ce_format_order_item_tax_class( $tax_class = '' ) {

	$output = $tax_class;
	switch( $tax_class ) {

		case 'zero-rate':
			$output = __( 'Zero Rate', 'woocommerce-exporter' );
			break;

		case 'reduced-rate':
			$output = __( 'Reduced Rate', 'woocommerce-exporter' );
			break;

		case '':
			$output = __( 'Standard', 'woocommerce-exporter' );
			break;

		case '0':
			$output = __( 'N/A', 'woocommerce-exporter' );
			break;

	}
	return $output;

}

function woo_ce_format_order_status( $status_id = '' ) {

	$output = $status_id;
	// Check if an empty Order Status has been provided
	if( empty( $status_id ) )
		return $output;

	$order_statuses = woo_ce_get_order_statuses();
	if( !empty( $order_statuses ) ) {
		foreach( $order_statuses as $order_status ) {
			if( $order_status->slug == $status_id || strtolower( $order_status->name ) == $status_id || strpos( $order_status->slug, $status_id ) !== false ) {
				$output = ucfirst( $order_status->name );
				break;
			}
		}
	}
	return $output;

}
?>