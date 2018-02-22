<select name="_woocommerce_gpf_data[{key}]" class="woocommerce-gpf-store-default">
	<option value="">{emptytext}</option>
	<option value="in stock" {in stock-selected}><?php _e( 'In Stock', 'ultimatewoo-pro' ); ?></option>
	<option value="available for order" {available for order-selected}><?php _e( 'Available for order', 'ultimatewoo-pro' ); ?></option>
	<option value="preorder" {preorder-selected}><?php _e( 'Pre-Order', 'ultimatewoo-pro' ); ?></option>
	<option value="out of stock" {out of stock-selected}><?php _e( 'Out of stock', 'ultimatewoo-pro' ); ?></option>
</select>