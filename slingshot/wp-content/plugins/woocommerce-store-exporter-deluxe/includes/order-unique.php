<?php
// Order items formatting: Unique
function woo_ce_unique_order_item_fields( $fields = array(), $args = array() ) {

	$max_order_items = woo_ce_get_option( 'max_order_items', 10 );
	// Check for the max_order_items override
	if( isset( $args['max_order_items'] ) && !empty( $args['max_order_items'] ) )
		$max_order_items = $args['max_order_items'];
	if( !empty( $fields ) ) {
		// Tack on a extra digit to max_order_items so we get the correct number of columns
		$max_order_items++;
		for( $i = 1; $i < $max_order_items; $i++ ) {
			if( isset( $fields['order_items_id'] ) )
				$fields[sprintf( 'order_item_%d_id', $i )] = 'on';
			if( isset( $fields['order_items_product_id'] ) )
				$fields[sprintf( 'order_item_%d_product_id', $i )] = 'on';
			if( isset( $fields['order_items_variation_id'] ) )
				$fields[sprintf( 'order_item_%d_variation_id', $i )] = 'on';
			if( isset( $fields['order_items_sku'] ) )
				$fields[sprintf( 'order_item_%d_sku', $i )] = 'on';
			if( isset( $fields['order_items_name'] ) )
				$fields[sprintf( 'order_item_%d_name', $i )] = 'on';
			if( isset( $fields['order_items_variation'] ) )
				$fields[sprintf( 'order_item_%d_variation', $i )] = 'on';
			if( isset( $fields['order_items_image_embed'] ) )
				$fields[sprintf( 'order_item_%d_image_embed', $i )] = 'on';
			if( isset( $fields['order_items_description'] ) )
				$fields[sprintf( 'order_item_%d_description', $i )] = 'on';
			if( isset( $fields['order_items_excerpt'] ) )
				$fields[sprintf( 'order_item_%d_excerpt', $i )] = 'on';
			if( isset( $fields['order_items_publish_date'] ) )
				$fields[sprintf( 'order_item_%d_publish_date', $i )] = 'on';
			if( isset( $fields['order_items_modified_date'] ) )
				$fields[sprintf( 'order_item_%d_modified_date', $i )] = 'on';
			if( isset( $fields['order_items_tax_class'] ) )
				$fields[sprintf( 'order_item_%d_tax_class', $i )] = 'on';
			if( isset( $fields['order_items_quantity'] ) )
				$fields[sprintf( 'order_item_%d_quantity', $i )] = 'on';
			if( isset( $fields['order_items_total'] ) )
				$fields[sprintf( 'order_item_%d_total', $i )] = 'on';
			if( isset( $fields['order_items_subtotal'] ) )
				$fields[sprintf( 'order_item_%d_subtotal', $i )] = 'on';
			if( isset( $fields['order_items_rrp'] ) )
				$fields[sprintf( 'order_item_%d_rrp', $i )] = 'on';
			if( isset( $fields['order_items_stock'] ) )
				$fields[sprintf( 'order_item_%d_stock', $i )] = 'on';
			if( isset( $fields['order_items_shipping_class'] ) )
				$fields[sprintf( 'order_item_%d_shipping_class', $i )] = 'on';
			if( isset( $fields['order_items_tax'] ) )
				$fields[sprintf( 'order_item_%d_tax', $i )] = 'on';
			if( isset( $fields['order_items_tax_subtotal'] ) )
				$fields[sprintf( 'order_item_%d_tax_subtotal', $i )] = 'on';
			if( isset( $fields['order_items_refund_subtotal'] ) )
				$fields[sprintf( 'order_item_%d_refund_subtotal', $i )] = 'on';
			if( isset( $fields['order_items_refund_quantity'] ) )
				$fields[sprintf( 'order_item_%d_refund_quantity', $i )] = 'on';
			if( isset( $fields['order_items_type'] ) )
				$fields[sprintf( 'order_item_%d_type', $i )] = 'on';
			if( isset( $fields['order_items_type_id'] ) )
				$fields[sprintf( 'order_item_%d_type_id', $i )] = 'on';
			if( isset( $fields['order_items_category'] ) )
				$fields[sprintf( 'order_item_%d_category', $i )] = 'on';
			if( isset( $fields['order_items_tag'] ) )
				$fields[sprintf( 'order_item_%d_tag', $i )] = 'on';
			if( isset( $fields['order_items_total_sales'] ) )
				$fields[sprintf( 'order_item_%d_total_sales', $i )] = 'on';
			if( isset( $fields['order_items_weight'] ) )
				$fields[sprintf( 'order_item_%d_weight', $i )] = 'on';
			if( isset( $fields['order_items_height'] ) )
				$fields[sprintf( 'order_item_%d_height', $i )] = 'on';
			if( isset( $fields['order_items_width'] ) )
				$fields[sprintf( 'order_item_%d_width', $i )] = 'on';
			if( isset( $fields['order_items_length'] ) )
				$fields[sprintf( 'order_item_%d_length', $i )] = 'on';
			if( isset( $fields['order_items_total_weight'] ) )
				$fields[sprintf( 'order_item_%d_total_weight', $i )] = 'on';
			$fields = apply_filters( 'woo_ce_add_unique_order_item_fields_on', $fields, $i );
		}

		// Extend the list of accepted Image Embed fields
		add_filter( 'woo_ce_override_embed_allowed_fields', 'woo_ce_unique_order_items_embed_allowed_fields', 10, 2 );

		$excluded_fields = apply_filters( 'woo_ce_add_unique_order_item_fields_exclusion', array(
			'order_items_id',
			'order_items_product_id',
			'order_items_variation_id',
			'order_items_sku',
			'order_items_name',
			'order_items_variation',
			'order_items_image_embed',
			'order_items_description',
			'order_items_excerpt',
			'order_items_publish_date',
			'order_items_modified_date',
			'order_items_tax_class',
			'order_items_quantity',
			'order_items_total',
			'order_items_subtotal',
			'order_items_rrp',
			'order_items_stock',
			'order_items_shipping_class',
			'order_items_tax',
			'order_items_tax_subtotal',
			'order_items_refund_subtotal',
			'order_items_refund_quantity',
			'order_items_type',
			'order_items_type_id',
			'order_items_category',
			'order_items_tag',
			'order_items_total_sales',
			'order_items_weight',
			'order_items_height',
			'order_items_width',
			'order_items_length',
			'order_items_total_weight'
		), $fields );
		foreach( $fields as $key => $field ) {
			if( in_array( $key, $excluded_fields ) || strpos( $field, 'order_items_' ) === true )
				unset( $fields[$key] );
		}
		if( !empty( $fields ) ) {
			foreach( $fields as $key => $field ) {
				if( in_array( $key, $excluded_fields ) || strpos( $field, 'order_items_' ) === true )
					woo_ce_error_log( sprintf( 'Warning: %s', sprintf( __( 'woo_ce_unique_order_item_fields(): %s was left behind during a Order export with the unique Order Items Formatting rule, this could break column formatting', 'woocommerce-exporter' ), $key ) ) );
			}
		}
	}
	return $fields;

}

function woo_ce_unique_order_items_embed_allowed_fields( $fields = array(), $args = array() ) {

	// Check for the max_order_items override
	if( isset( $args['max_order_items'] ) && !empty( $args['max_order_items'] ) )
		$max_order_items = $args['max_order_items'];
	$max_order_items = woo_ce_get_option( 'max_order_items', 10 );
	if( !empty( $fields ) ) {
		// Tack on a extra digit to max_order_items so we get the correct number of columns
		$max_order_items++;
		for( $i = 1; $i < $max_order_items; $i++ )
			$fields[] = sprintf( 'order_item_%d_image_embed', $i );
	}
	return $fields;

}

function woo_ce_unique_order_item_columns( $columns = array(), $fields = array(), $args = array() ) {

	// Check for the max_order_items override
	if( isset( $args['max_order_items'] ) && !empty( $args['max_order_items'] ) )
		$max_order_items = $args['max_order_items'];
	$max_order_items = woo_ce_get_option( 'max_order_items', 10 );
	if( !empty( $columns ) ) {
		// Strip out any remaining Order Items columns
		foreach( $columns as $key => $column ) {
			if( strpos( $column, __( 'Order Items: ', 'woocommerce-exporter' ) ) !== false )
				unset( $columns[$key] );
		}
		// Tack on a extra digit to max_order_items so we get the correct number of columns
		$max_order_items++;
		// Replace the removed columns with new ones
		for( $i = 1; $i < $max_order_items; $i++ ) {
			if( isset( $fields[sprintf( 'order_item_%d_id', $i )] ) )
				$columns[] = sprintf( apply_filters( 'woo_ce_unique_order_item_column_id', __( 'Order Item #%d: %s', 'woocommerce-exporter' ) ), $i, woo_ce_get_order_field( 'order_items_id', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_product_id', $i )] ) )
				$columns[] = sprintf( apply_filters( 'woo_ce_unique_order_item_column_product_id', __( 'Order Item #%d: %s', 'woocommerce-exporter' ) ), $i, woo_ce_get_order_field( 'order_items_product_id', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_variation_id', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_variation_id', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_sku', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_sku', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_name', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_name', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_variation', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_variation', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_image_embed', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_image_embed', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_description', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_description', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_excerpt', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_excerpt', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_publish_date', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_publish_date', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_modified_date', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_modified_date', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_tax_class', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_tax_class', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_quantity', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_quantity', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_total', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_total', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_subtotal', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_subtotal', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_rrp', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_rrp', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_stock', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_stock', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_shipping_class', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_shipping_class', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_tax', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_tax', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_tax_subtotal', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_tax_subtotal', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_refund_subtotal', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_refund_subtotal', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_refund_quantity', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_refund_quantity', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_type', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_type', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_type_id', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_type_id', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_category', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_category', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_tag', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_tag', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_total_sales', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_total_sales', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_weight', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_weight', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_height', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_height', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_width', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_width', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_length', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_length', 'name', 'unique' ) );
			if( isset( $fields[sprintf( 'order_item_%d_total_weight', $i )] ) )
				$columns[] = sprintf( __( 'Order Item #%d: %s', 'woocommerce-exporter' ), $i, woo_ce_get_order_field( 'order_items_total_weight', 'name', 'unique' ) );
			$columns = apply_filters( 'woo_ce_unique_order_item_columns', $columns, $i, $fields );
		}
	}
	return $columns;

}
?>