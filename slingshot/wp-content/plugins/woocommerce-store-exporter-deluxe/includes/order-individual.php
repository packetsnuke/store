<?php
// Order items formatting: Individual
function woo_ce_order_items_individual( $order, $order_item ) {

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	// Cycle through all $order->order_items... and clear them
	if( !empty( $order ) ) {
		foreach( $order as $key => $column ) {
			if( strpos( $key, 'order_items_' ) !== false && is_string( $order->$key ) )
				$order->$key = '';
		}
		unset( $key, $column );
	}

	$order->order_items_id = $order_item->id;
	$order->order_items_product_id = $order_item->product_id;
	$order->order_items_variation_id = $order_item->variation_id;
	if( empty( $order_item->sku ) )
		$order_item->sku = '';
	$order->order_items_sku = $order_item->sku;
	$order->order_items_name = $order_item->name;
	$order->order_items_variation = $order_item->variation;
	$order->order_items_image_embed = $order_item->image_embed;
	$order->order_items_description = woo_ce_format_description_excerpt( $order_item->description );
	$order->order_items_excerpt = woo_ce_format_description_excerpt( $order_item->excerpt );
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
	$order->order_items_height = $order_item->height;
	$order->order_items_width = $order_item->width;
	$order->order_items_length = $order_item->length;
	$order->order_items_total_sales = $order_item->total_sales;
	$order->order_items_total_weight = $order_item->total_weight;

	// Add Order Item weight to Shipping Weight
	if( version_compare( woo_get_woo_version(), '2.7', '<' ) ) {
		if( $order_item->total_weight != '' )
			$order->shipping_weight_total += $order_item->total_weight;
	}

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	return $order;

}
add_filter( 'woo_ce_order_items_individual', 'woo_ce_order_items_individual', 10, 2 );
?>