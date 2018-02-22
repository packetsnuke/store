<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	// Quick Export

	// HTML template for Filter Orders by Brand widget on Store Exporter screen
	function woo_ce_orders_filter_by_product_brand() {

		// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
		// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
		if( woo_ce_detect_product_brands() == false )
			return;

		$args = array(
			'hide_empty' => 1,
			'orderby' => 'term_group'
		);
		$product_brands = woo_ce_get_product_brands( $args );
		$types = woo_ce_get_option( 'order_brand', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-brand"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Product Brand', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-brand" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_brands ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Brand...', 'woocommerce-exporter' ); ?>" name="order_filter_brand[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_brands as $product_brand ) { ?>
				<option value="<?php echo $product_brand->term_id; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $product_brand->term_id, $types, false ), true ) : '' ); ?>><?php echo woo_ce_format_product_category_label( $product_brand->name, $product_brand->parent_name ); ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_brand->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Brands were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Brands you want to filter exported Orders by. Product Brands not assigned to Products are hidden from view. Default is to include all Product Brands.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-brand -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Product Vendor widget on Store Exporter screen
	function woo_ce_orders_filter_by_product_vendor() {

		// Product Vendors - http://www.woothemes.com/products/product-vendors/
		// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
		if( woo_ce_detect_export_plugin( 'vendors' ) == false && woo_ce_detect_export_plugin( 'yith_vendor' ) == false )
			return;

		$args = array(
			'hide_empty' => 1
		);
		$product_vendors = woo_ce_get_product_vendors( $args, 'full' );
		$types = woo_ce_get_option( 'order_product_vendor', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-product_vendor"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Product Vendor', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-product_vendor" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_vendors ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Vendor...', 'woocommerce-exporter' ); ?>" id="order_filter_vendor" name="order_filter_product_vendor[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_vendors as $product_vendor ) { ?>
				<option value="<?php echo $product_vendor->term_id; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $product_vendor->term_id, $types, false ), true ) : '' ); ?><?php disabled( $product_vendor->count, 0 ); ?>><?php echo $product_vendor->name; ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_vendor->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Vendors were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Filter Orders by Product Vendors to be included in the export. Default is to include all Product Vendors.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-product_vendor -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Delivery Date widget on Store Exporter screen
	function woo_ce_orders_filter_by_delivery_date() {

		// YITH WooCommerce Delivery Date Premium - http://yithemes.com/themes/plugins/yith-woocommerce-delivery-date/
		// Order Delivery Date for WooCommerce - https://wordpress.org/plugins/order-delivery-date-for-woocommerce/
		// Order Delivery Date Pro for WooCommerce - https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/
		if(
		 woo_ce_detect_export_plugin( 'yith_delivery_pro' ) == false && 
		 woo_ce_detect_export_plugin( 'orddd_free' ) == false &&
		 woo_ce_detect_export_plugin( 'orddd' ) == false
		)
			return;

		$delivery_dates_from = woo_ce_get_order_first_date();
		$delivery_dates_to = woo_ce_get_order_date_filter( 'today', 'from', 'd/m/Y' );
		$types = woo_ce_get_option( 'order_delivery_dates_filter' );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-delivery_date"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Delivery Date', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-delivery_date" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_delivery_dates_filter" value=""<?php checked( $types, false ); ?> /> <?php _e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo $delivery_dates_from; ?> - <?php echo $delivery_dates_to; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_delivery_dates_filter" value="today"<?php checked( $types, 'today' ); ?> /> <?php _e( 'Today', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_delivery_dates_filter" value="tomorrow"<?php checked( $types, 'tomorrow' ); ?> /> <?php _e( 'Tomorrow', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_delivery_dates_filter" value="manual"<?php checked( $types, 'manual' ); ?> /> <?php _e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="delivery_dates_from" name="order_delivery_dates_from" value="<?php echo esc_attr( $delivery_dates_from ); ?>" class="text code datepicker order_delivery_dates_export" /> to <input type="text" size="10" maxlength="10" id="delivery_dates_to" name="order_delivery_dates_to" value="<?php echo esc_attr( $delivery_dates_to ); ?>" class="text code datepicker order_delivery_dates_export" />
				<p class="description"><?php _e( 'Filter the dates of Orders to be included in the export. Default is the date of the first Order to today in the date format <code>DD/MM/YYYY</code>.', 'woocommerce-exporter' ); ?></p>
			</div>
		</li>
	</ul>
</div>
<!-- #export-orders-filters-delivery_date -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Booking Date widget on Store Exporter screen
	function woo_ce_orders_filter_by_booking_date() {

		// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
		if( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) == false )
			return;

		$booking_dates_from = woo_ce_get_order_first_date();
		$booking_dates_to = woo_ce_get_order_date_filter( 'today', 'from', 'd/m/Y' );
		$types = woo_ce_get_option( 'order_booking_dates_filter' );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-booking_date"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Booking Date', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-booking_date" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_booking_dates_filter" value=""<?php checked( $types, false ); ?> /> <?php _e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo $booking_dates_from; ?> - <?php echo $booking_dates_to; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_dates_filter" value="today"<?php checked( $types, 'today' ); ?> /> <?php _e( 'Today', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_booking_dates_filter" value="manual"<?php checked( $types, 'manual' ); ?> /> <?php _e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="booking_dates_from" name="order_booking_dates_from" value="<?php echo esc_attr( $booking_dates_from ); ?>" class="text code datepicker order_booking_dates_export" /> to <input type="text" size="10" maxlength="10" id="booking_dates_to" name="order_booking_dates_to" value="<?php echo esc_attr( $booking_dates_to ); ?>" class="text code datepicker order_booking_dates_export" />
				<p class="description"><?php _e( 'Filter the dates of Orders to be included in the export. Default is the date of the first Order to today in the date format <code>DD/MM/YYYY</code>.', 'woocommerce-exporter' ); ?></p>
			</div>
		</li>
	</ul>
</div>
<!-- #export-orders-filters-booking_date -->
<?php
		ob_end_flush();

	}

	function woo_ce_orders_filter_by_voucher_redeemed() {

		// WooCommerce PDF Product Vouchers - http://www.woothemes.com/products/pdf-product-vouchers/
		if( woo_ce_detect_export_plugin( 'wc_pdf_product_vouchers' ) == false )
			return;

		$types = woo_ce_get_option( 'order_voucher_redeemed' );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-voucher_redeemed"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Voucher Redeemed', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-voucher_redeemed" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_filter_voucher_redeemed" value=""<?php checked( $types, false ); ?> /> <?php _e( 'All Orders', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_voucher_redeemed" value="redeemed"<?php checked( $types, 'redeemed' ); ?> /> <?php _e( 'Orders marked as redeemed', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_voucher_redeemed" value="unredeemed"<?php checked( $types, 'unredeemed' ); ?> /> <?php _e( 'Orders marked un-redeemed', 'woocommerce-exporter' ); ?></label>
		</li>
	</ul>
</div>
<!-- #export-orders-filters-voucher_redeemed -->
<?php
		ob_end_flush();

	}

	function woo_ce_orders_filter_by_order_type() {

		// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
		if( woo_ce_detect_export_plugin( 'subscriptions' ) == false )
			return;

		$types = woo_ce_get_option( 'order_order_type' );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-order_type"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Order Type', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-order_type" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_filter_order_type" value=""<?php checked( $types, false ); ?> /> <?php _e( 'All Orders', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_order_type" value="original"<?php checked( $types, 'original' ); ?> /> <?php _e( 'Original', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_order_type" value="parent"<?php checked( $types, 'parent' ); ?> /> <?php _e( 'Subscription Parent', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_order_type" value="renewal"<?php checked( $types, 'renewal' ); ?> /> <?php _e( 'Subscription Renewal', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_order_type" value="resubscribe"<?php checked( $types, 'resubscribe' ); ?> /> <?php _e( 'Subscription Resubscribe', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_order_type" value="switch"<?php checked( $types, 'switch' ); ?> /> <?php _e( 'Subscription Switch', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_order_type" value="regular"<?php checked( $types, 'regular' ); ?> /> <?php _e( 'Non-subscription', 'woocommerce-exporter' ); ?></label>
		</li>
	</ul>
</div>
<!-- #export-orders-filters-order_type -->
<?php
		ob_end_flush();

	}

	function woo_ce_orders_filter_by_order_meta() {

		$custom_orders = woo_ce_get_option( 'custom_orders', '' );
		if( empty( $custom_orders ) )
			return;

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-order_meta"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Order meta', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-order_meta" class="separator">
	<ul>
<?php foreach( $custom_orders as $custom_order ) { ?>
		<li>
			<?php echo $custom_order; ?>:<br />
			<input type="text" id="order_filter_custom_meta-<?php echo esc_attr( $custom_order ); ?>" name="order_filter_custom_meta-<?php echo esc_attr( $custom_order ); ?>" class="text code" style="width:95%;">
		</li>
<?php } ?>
	</ul>
</div>
<!-- #export-orders-filters-order_meta -->
<?php
		ob_end_flush();

	}

	// Scheduled Exports

	function woo_ce_scheduled_export_order_filter_by_order_type( $post_ID = 0 ) {

		// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
		if( woo_ce_detect_export_plugin( 'subscriptions' ) == false )
			return;

		$types = get_post_meta( $post_ID, '_filter_order_type', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_order_type"><?php _e( 'Order type', 'woocommerce-exporter' ); ?></label>
	<input type="radio" name="order_filter_order_type" value=""<?php checked( $types, false ); ?> />&nbsp;<?php _e( 'All Orders', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_filter_order_type" value="original"<?php checked( $types, 'original' ); ?> />&nbsp;<?php _e( 'Original', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_filter_order_type" value="parent"<?php checked( $types, 'parent' ); ?> />&nbsp;<?php _e( 'Subscription Parent', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_filter_order_type" value="renewal"<?php checked( $types, 'renewal' ); ?> />&nbsp;<?php _e( 'Subscription Renewal', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_filter_order_type" value="resubscribe"<?php checked( $types, 'resubscribe' ); ?> />&nbsp;<?php _e( 'Subscription Resubscribe', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_filter_order_type" value="switch"<?php checked( $types, 'switch' ); ?> />&nbsp;<?php _e( 'Subscription Switch', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_filter_order_type" value="regular"<?php checked( $types, 'regular' ); ?> />&nbsp;<?php _e( 'Non-subscription', 'woocommerce-exporter' ); ?>
</p>

<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_filter_by_order_meta( $post_ID = 0 ) {

		$custom_orders = woo_ce_get_option( 'custom_orders', '' );
		if( empty( $custom_orders ) )
			return;

		ob_start(); ?>
<?php foreach( $custom_orders as $custom_order ) { ?>
	<?php $types = get_post_meta( $post_ID, sprintf( '_filter_order_custom_meta-%s', esc_attr( $custom_order ) ), true ); ?>
	<p class="form-field discount_type_field">
		<label for="order_filter_custom_meta-<?php echo esc_attr( $custom_order ); ?>"><?php echo esc_attr( $custom_order ); ?></label></label>
		<input type="text" id="order_filter_custom_meta-<?php echo esc_attr( $custom_order ); ?>" name="order_filter_custom_meta-<?php echo esc_attr( $custom_order ); ?>" value="<?php echo $types; ?>" size="5" class="text" />
	</p>
<?php } ?>
<?php
		ob_end_flush();

	}

	function woo_ce_orders_custom_fields_extra_product_options() {

		// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
		if( ( woo_ce_detect_export_plugin( 'extra_product_options' ) ) == false )
			return;

		if( $custom_extra_product_options = woo_ce_get_option( 'custom_extra_product_options', '' ) )
			$custom_extra_product_options = implode( "\n", $custom_extra_product_options );

		ob_start(); ?>
<tr>
	<th>
		<label><?php _e( 'Custom Extra Product Options', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<textarea name="custom_extra_product_options" rows="5" cols="70"><?php echo esc_textarea( $custom_extra_product_options ); ?></textarea>
		<p class="description"><?php _e( 'Include custom Extra Product Options linked to Order Items within in your export file by adding the Name of each Extra Product Option to a new line above.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	function woo_ce_orders_custom_fields_product_addons() {

		if( ( woo_ce_detect_export_plugin( 'product_addons' ) ) == false )
			return;

		if( $custom_product_addons = woo_ce_get_option( 'custom_product_addons', '' ) )
			$custom_product_addons = implode( "\n", $custom_product_addons );

		ob_start(); ?>
<tr>
	<th>
		<label><?php _e( 'Custom Product Add-ons', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<textarea name="custom_product_addons" rows="5" cols="70"><?php echo esc_textarea( $custom_product_addons ); ?></textarea>
		<p class="description"><?php _e( 'Include custom Product Add-ons (not Global Add-ons) linked to individual Products within in your export file by adding the Group Name of each Product Addon to a new line above.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	/* End of: WordPress Administration */

}

// Adds custom Order columns to the Order fields list
function woo_ce_extend_order_fields( $fields = array() ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$fields[] = array(
			'name' => 'blog_id',
			'label' => __( 'Blog ID', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress Multisite', 'woocommerce-exporter' )
		);
	}

	// Product Add-ons - http://www.woothemes.com/
	if( woo_ce_detect_export_plugin( 'product_addons' ) ) {
		$fields[] = array(
			'name' => 'order_items_product_addons_summary',
			'label' => __( 'Order Items: Product Add-ons', 'woocommerce-exporter' ),
			'hover' => sprintf( apply_filters( 'woo_ce_extend_order_fields_product_addons_summary', '%s' ), __( 'Product Add-ons', 'woocommerce-exporter' ) )
		);
		$product_addons = woo_ce_get_product_addons();
		if( !empty( $product_addons ) ) {
			foreach( $product_addons as $product_addon ) {
				if( !empty( $product_addon ) ) {
					$fields[] = array(
						'name' => sprintf( 'order_items_product_addon_%s', sanitize_key( $product_addon->post_name ) ),
						'label' => sprintf( __( 'Order Items: %s', 'woocommerce-exporter' ), ucfirst( $product_addon->post_title ) ),
						'hover' => sprintf( apply_filters( 'woo_ce_extend_order_fields_product_addons', '%s: %s' ), __( 'Product Add-ons', 'woocommerce-exporter' ), $product_addon->form_title )
					);
				}
			}
		}
		unset( $product_addons, $product_addon );
	}

	// WooCommerce Print Invoice & Delivery Note - https://wordpress.org/plugins/woocommerce-delivery-notes/
	if( woo_ce_detect_export_plugin( 'print_invoice_delivery_note' ) ) {
		$fields[] = array(
			'name' => 'invoice_number',
			'label' => __( 'Invoice Number', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Print Invoice & Delivery Note', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'invoice_date',
			'label' => __( 'Invoice Date', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Print Invoice & Delivery Note', 'woocommerce-exporter' )
		);
	}

	// WooCommerce PDF Invoices & Packing Slips - http://www.wpovernight.com
	if( woo_ce_detect_export_plugin( 'pdf_invoices_packing_slips' ) ) {
		$fields[] = array(
			'name' => 'pdf_invoice_number',
			'label' => __( 'PDF Invoice Number', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce PDF Invoices & Packing Slips', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'pdf_invoice_date',
			'label' => __( 'PDF Invoice Date', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce PDF Invoices & Packing Slips', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Germanized - http://www.wpovernight.com
	if( woo_ce_detect_export_plugin( 'wc_germanized_pro' ) ) {
		$fields[] = array(
			'name' => 'invoice_number',
			'label' => __( 'Invoice Number', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Germanized', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'invoice_number_formatted',
			'label' => __( 'Invoice Number (Formatted)', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Germanized', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'invoice_status',
			'label' => __( 'Invoice Status', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Germanized', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Hear About Us - https://wordpress.org/plugins/woocommerce-hear-about-us/
	if( woo_ce_detect_export_plugin( 'hear_about_us' ) ) {
		$fields[] = array(
			'name' => 'hear_about_us',
			'label' => __( 'Source', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Hear About Us', 'woocommerce-exporter' )
		);
	}

	// Order Delivery Date for WooCommerce - https://wordpress.org/plugins/order-delivery-date-for-woocommerce/
	// Order Delivery Date Pro for WooCommerce - https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/
	if( woo_ce_detect_export_plugin( 'orddd_free' ) || woo_ce_detect_export_plugin( 'orddd' ) ) {
		$fields[] = array(
			'name' => 'delivery_date',
			'label' => __( 'Delivery Date', 'woocommerce-exporter' ),
			'hover' => ( woo_ce_detect_export_plugin( 'orddd' ) ? __( 'Order Delivery Date Pro for WooCommerce', 'woocommerce-exporter' ) : __( 'Order Delivery Date for WooCommerce', 'woocommerce-exporter' ) )
		);
	}

	// WooCommerce Memberships - http://www.woothemes.com/products/woocommerce-memberships/
	if( woo_ce_detect_export_plugin( 'wc_memberships' ) ) {
		$fields[] = array(
			'name' => 'active_memberships',
			'label' => __( 'Active Memberships', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Memberships', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Uploads - https://wpfortune.com/shop/plugins/woocommerce-uploads/
	if( woo_ce_detect_export_plugin( 'wc_uploads' ) ) {
		$fields[] = array(
			'name' => 'uploaded_files',
			'label' => __( 'Uploaded Files', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Uploads', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'uploaded_files_thumbnail',
			'label' => __( 'Uploaded Files (Thumbnail)', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Uploads', 'woocommerce-exporter' )
		);
	}

	// WPML - https://wpml.org/
	// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
	if( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
		$fields[] = array(
			'name' => 'language',
			'label' => __( 'Language', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Multilingual', 'woocommerce-exporter' )
		);
	}

	// WooCommerce EAN Payment Gateway - http://plugins.yanco.dk/woocommerce-ean-payment-gateway
	if( woo_ce_detect_export_plugin( 'wc_ean' ) ) {
		$fields[] = array(
			'name' => 'ean_number',
			'label' => __( 'EAN Number', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce EAN Payment Gateway', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Checkout Manager - http://wordpress.org/plugins/woocommerce-checkout-manager/
	// WooCommerce Checkout Manager Pro - http://wordpress.org/plugins/woocommerce-checkout-manager/
	if( woo_ce_detect_export_plugin( 'checkout_manager' ) ) {

		// Checkout Manager stores its settings in mulitple suffixed wccs_settings WordPress Options

		// Load generic settings
		$options = get_option( 'wccs_settings' );
		if( isset( $options['buttons'] ) ) {
			$buttons = $options['buttons'];
			if( !empty( $buttons ) ) {
				$header = ( $buttons[0]['type'] == 'heading' ? $buttons[0]['label'] : __( 'Additional', 'woocommerce-exporter' ) );
				foreach( $buttons as $button ) {
					// Skip headings
					if( $button['type'] == 'heading' )
						continue;
					$label = ( !empty( $button['label'] ) ? $button['label'] : $button['cow'] );
					$fields[] = array(
						'name' => sprintf( 'additional_%s', $button['cow'] ),
						'label' => ( !empty( $header ) ? sprintf( apply_filters( 'woo_ce_extend_order_fields_wccs', '%s: %s' ), ucfirst( $header ), ucfirst( $label ) ) : ucfirst( $label ) ),
						'hover' => __( 'WooCommerce Checkout Manager', 'woocommerce-exporter' )
					);
				}
				unset( $buttons, $button, $header, $label );
			}
		}
		unset( $options );

		// Load Shipping settings
		$options = get_option( 'wccs_settings2' );
		if( isset( $options['shipping_buttons'] ) ) {
			$buttons = $options['shipping_buttons'];
			if( !empty( $buttons ) ) {
				$header = ( $buttons[0]['type'] == 'heading' ? $buttons[0]['label'] : __( 'Shipping', 'woocommerce-exporter' ) );
				foreach( $buttons as $button ) {
					// Skip headings
					if( $button['type'] == 'heading' )
						continue;
					$wccs_field_duplicate = false;
					// Check if this isn't a duplicate Checkout Manager Pro field
					foreach( $fields as $field ) {
						if( isset( $field['name'] ) && $field['name'] == sprintf( 'shipping_%s', $button['cow'] ) ) {
							// Duplicate exists
							$wccs_field_duplicate = true;
							break;
						}
					}
					// If it's not a duplicate go ahead and add it to the list
					if( $wccs_field_duplicate !== true ) {
						$label = ( !empty( $button['label'] ) ? $button['label'] : $button['cow'] );
						$fields[] = array(
							'name' => sprintf( 'shipping_%s', $button['cow'] ),
							'label' => ( !empty( $header ) ? sprintf( apply_filters( 'woo_ce_extend_order_fields_wccs', '%s: %s' ), ucfirst( $header ), ucfirst( $label ) ) : ucfirst( $label ) ),
							'hover' => __( 'WooCommerce Checkout Manager', 'woocommerce-exporter' )
						);
					}
					unset( $wccs_field_duplicate );
				}
				unset( $buttons, $button, $header, $label );
			}
		}
		unset( $options );

		// Load Billing settings
		$options = get_option( 'wccs_settings3' );
		if( isset( $options['billing_buttons'] ) ) {
			$buttons = $options['billing_buttons'];
			if( !empty( $buttons ) ) {
				$header = ( $buttons[0]['type'] == 'heading' ? $buttons[0]['label'] : __( 'Billing', 'woocommerce-exporter' ) );
				foreach( $buttons as $button ) {
					// Skip headings
					if( $button['type'] == 'heading' )
						continue;
					$wccs_field_duplicate = false;
					// Check if this isn't a duplicate Checkout Manager Pro field
					foreach( $fields as $field ) {
						if( isset( $field['name'] ) && $field['name'] == sprintf( 'billing_%s', $button['cow'] ) ) {
							// Duplicate exists
							$wccs_field_duplicate = true;
							break;
						}
					}
					// If it's not a duplicate go ahead and add it to the list
					if( $wccs_field_duplicate !== true ) {
						$label = ( !empty( $button['label'] ) ? $button['label'] : $button['cow'] );
						$fields[] = array(
							'name' => sprintf( 'billing_%s', $button['cow'] ),
							'label' => ( !empty( $header ) ? sprintf( apply_filters( 'woo_ce_extend_order_fields_wccs', '%s: %s' ), ucfirst( $header ), ucfirst( $label ) ) : ucfirst( $label ) ),
							'hover' => __( 'WooCommerce Checkout Manager', 'woocommerce-exporter' )
						);
					}
					unset( $wccs_field_duplicate );
				}
				unset( $buttons, $button, $header, $label );
			}
		}
		unset( $options );

	}

	// Poor Guys Swiss Knife - http://wordpress.org/plugins/woocommerce-poor-guys-swiss-knife/
	if( woo_ce_detect_export_plugin( 'wc_pgsk' ) ) {
		$options = get_option( 'wcpgsk_settings' );
		$billing_fields = ( isset( $options['woofields']['billing'] ) ? $options['woofields']['billing'] : array() );
		$shipping_fields = ( isset( $options['woofields']['shipping'] ) ? $options['woofields']['shipping'] : array() );

		// Custom billing fields
		if( !empty( $billing_fields ) ) {
			foreach( $billing_fields as $key => $billing_field ) {
				$fields[] = array(
					'name' => $key,
					'label' => $options['woofields'][sprintf( 'label_%s', $key )],
					'hover' => __( 'Poor Guys Swiss Knife', 'woocommerce-exporter' )
				);
			}
			unset( $billing_fields, $billing_field );
		}

		// Custom shipping fields
		if( !empty( $shipping_fields ) ) {
			foreach( $shipping_fields as $key => $shipping_field ) {
				$fields[] = array(
					'name' => $key,
					'label' => $options['woofields'][sprintf( 'label_%s', $key )],
					'hover' => __( 'Poor Guys Swiss Knife', 'woocommerce-exporter' )
				);
			}
			unset( $shipping_fields, $shipping_field );
		}

		unset( $options );
	}

	// Checkout Field Editor - http://woothemes.com/woocommerce/
	if( woo_ce_detect_export_plugin( 'checkout_field_editor' ) ) {
		$billing_fields = get_option( 'wc_fields_billing', array() );
		$shipping_fields = get_option( 'wc_fields_shipping', array() );
		$additional_fields = get_option( 'wc_fields_additional', array() );

		// Custom billing fields
		if( !empty( $billing_fields ) ) {
			foreach( $billing_fields as $key => $billing_field ) {
				// Only add non-default Checkout fields to export columns list
				if( isset( $billing_field['custom'] ) && $billing_field['custom'] == 1 ) {
					$fields[] = array(
						'name' => sprintf( 'wc_billing_%s', $key ),
						'label' => sprintf( __( 'Billing: %s', 'woocommerce-exporter' ), ucfirst( $billing_field['label'] ) ),
						'hover' => __( 'Checkout Field Editor', 'woocommerce-exporter' )
					);
				}
			}
		}
		unset( $billing_fields, $billing_field );

		// Custom shipping fields
		if( !empty( $shipping_fields ) ) {
			foreach( $shipping_fields as $key => $shipping_field ) {
				// Only add non-default Checkout fields to export columns list
				if( isset( $shipping_field['custom'] ) && $shipping_field['custom'] == 1 ) {
					$fields[] = array(
						'name' => sprintf( 'wc_shipping_%s', $key ),
						'label' => sprintf( __( 'Shipping: %s', 'woocommerce-exporter' ), ucfirst( $shipping_field['label'] ) ),
						'hover' => __( 'Checkout Field Editor', 'woocommerce-exporter' )
					);
				}
			}
		}
		unset( $shipping_fields, $shipping_field );

		// Additional fields
		if( !empty( $additional_fields ) ) {
			foreach( $additional_fields as $key => $additional_field ) {
				// Only add non-default Checkout fields to export columns list
				if( isset( $additional_field['custom'] ) && $additional_field['custom'] == 1 ) {
					$fields[] = array(
						'name' => sprintf( 'wc_additional_%s', $key ),
						'label' => sprintf( __( 'Additional: %s', 'woocommerce-exporter' ), ucfirst( $additional_field['label'] ) ),
						'hover' => __( 'Checkout Field Editor', 'woocommerce-exporter' )
					);
				}
			}
		}
		unset( $additional_fields, $additional_field );
	}

	// Checkout Field Editor Pro - https://www.themehigh.com/product/woocommerce-checkout-field-editor-pro/
	if( woo_ce_detect_export_plugin( 'checkout_field_editor_pro' ) ) {
		// Check if the Class is available
		if( class_exists( 'WCFE_Checkout_Fields_Export_Handler' ) ) {
			$wc_export = new WCFE_Checkout_Fields_Export_Handler();
			if( method_exists( $wc_export, 'get_export_fields' ) ) {
				$options = $wc_export->get_export_fields();
				if( !empty( $options ) ) {
					foreach( $options as $option ) {
						$fields[] = array(
							'name' => sprintf( 'cfep_%s', $option['name'] ),
							'label' => sprintf( __( 'Custom: %s', 'woocommerce-exporter' ), ucfirst( $option['title'] ) ),
							'hover' => __( 'Checkout Field Editor Pro', 'woocommerce-exporter' )
						);
					}
					unset( $options, $option );
				}
			}
			unset( $wc_export );
		}
	}

	// Checkout Field Manager - http://61extensions.com
	if( woo_ce_detect_export_plugin( 'checkout_field_manager' ) ) {
		$billing_fields = get_option( 'woocommerce_checkout_billing_fields', array() );
		$shipping_fields = get_option( 'woocommerce_checkout_shipping_fields', array() );
		$custom_fields = get_option( 'woocommerce_checkout_additional_fields', array() );

		// Custom billing fields
		if( !empty( $billing_fields ) ) {
			foreach( $billing_fields as $key => $billing_field ) {
				// Only add non-default Checkout fields to export columns list
				if( strtolower( $billing_field['default_field'] ) != 'on' ) {
					$fields[] = array(
						'name' => sprintf( 'sod_billing_%s', $billing_field['name'] ),
						'label' => sprintf( __( 'Billing: %s', 'woocommerce-exporter' ), ucfirst( $billing_field['label'] ) ),
						'hover' => __( 'Checkout Field Manager', 'woocommerce-exporter' )
					);
				}
			}
		}
		unset( $billing_fields, $billing_field );

		// Custom shipping fields
		if( !empty( $shipping_fields ) ) {
			foreach( $shipping_fields as $key => $shipping_field ) {
				// Only add non-default Checkout fields to export columns list
				if( strtolower( $shipping_field['default_field'] ) != 'on' ) {
					$fields[] = array(
						'name' => sprintf( 'sod_shipping_%s', $shipping_field['name'] ),
						'label' => sprintf( __( 'Shipping: %s', 'woocommerce-exporter' ), ucfirst( $shipping_field['label'] ) ),
						'hover' => __( 'Checkout Field Manager', 'woocommerce-exporter' )
					);
				}
			}
		}
		unset( $shipping_fields, $shipping_field );

		// Custom fields
		if( !empty( $custom_fields ) ) {
			foreach( $custom_fields as $key => $custom_field ) {
				// Only add non-default Checkout fields to export columns list
				if( strtolower( $custom_field['default_field'] ) != 'on' ) {
					$fields[] = array(
						'name' => sprintf( 'sod_additional_%s', $custom_field['name'] ),
						'label' => sprintf( __( 'Additional: %s', 'woocommerce-exporter' ), ucfirst( $custom_field['label'] ) ),
						'hover' => __( 'Checkout Field Manager', 'woocommerce-exporter' )
					);
				}
			}
		}
		unset( $custom_fields, $custom_field );
	}

	// WooCommerce Extra Checkout Fields for Brazil - https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/
	if( woo_ce_detect_export_plugin( 'wc_extra_checkout_fields_brazil' ) ) {
		$fields[] = array(
			'name' => 'billing_cpf',
			'label' => __( 'Billing: CPF', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'billing_rg',
			'label' => __( 'Billing: RG', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'billing_cnpj',
			'label' => __( 'Billing: CNPJ', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'billing_ie',
			'label' => __( 'Billing: IE', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'billing_birthdate',
			'label' => __( 'Billing: Birth Date', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'billing_sex',
			'label' => __( 'Billing: Sex', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'billing_number',
			'label' => __( 'Billing: Number', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'billing_neighborhood',
			'label' => __( 'Billing: Neighborhood', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'billing_cellphone',
			'label' => __( 'Billing: Cell Phone', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'shipping_number',
			'label' => __( 'Shipping: Number', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'shipping_neighborhood',
			'label' => __( 'Shipping: Neighborhood', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' )
		);
	}

	// YITH WooCommerce Checkout Manager - https://yithemes.com/themes/plugins/yith-woocommerce-checkout-manager/
	if( woo_ce_detect_export_plugin( 'yith_cm' ) ) {
		// YITH WooCommerce Checkout Manager stores its settings in separate Options
		$billing_options = get_option( 'ywccp_fields_billing_options' );
		$shipping_options = get_option( 'ywccp_fields_shipping_options' );
		$additional_options = get_option( 'ywccp_fields_additional_options' );

		// Custom billing fields
		if( !empty( $billing_options ) ) {
			// Only add non-default Checkout fields to export columns list
			$default_keys = ywccp_get_default_fields_key( 'billing' );
			$fields_keys = array_keys( $billing_options );
			$billing_fields = array_diff( $fields_keys, $default_keys );
			if( !empty( $billing_fields ) ) {
				foreach( $billing_fields as $billing_field ) {
					// Check that the custom Billing field exists
					if( isset( $billing_options[$billing_field] ) ) {
						// Skip headings
						if( $billing_options[$billing_field]['type'] == 'heading' )
							continue;
						$fields[] = array(
							'name' => sprintf( 'ywccp_%s', sanitize_key( $billing_field ) ),
							'label' => sprintf( __( 'Billing: %s', 'woocommerce-exporter' ), ( !empty( $billing_options[$billing_field]['label'] ) ? $billing_options[$billing_field]['label'] : str_replace( 'billing_', '', $billing_field ) ) ),
							'hover' => __( 'YITH WooCommerce Checkout Manager', 'woocommerce-exporter' )
						);
					}
				}
			}
			unset( $fields_keys, $default_keys, $billing_fields, $billing_field );
		}
		unset( $billing_options );

		// Custom shipping fields
		if( !empty( $shipping_options ) ) {
			// Only add non-default Checkout fields to export columns list
			$default_keys = ywccp_get_default_fields_key( 'shipping' );
			$fields_keys = array_keys( $shipping_options );
			$shipping_fields = array_diff( $fields_keys, $default_keys );
			if( !empty( $shipping_fields ) ) {
				foreach( $shipping_fields as $shipping_field ) {
					// Check that the custom Shipping field exists
					if( isset( $shipping_options[$shipping_field] ) ) {
						// Skip headings
						if( $shipping_options[$shipping_field]['type'] == 'heading' )
							continue;
						$fields[] = array(
							'name' => sprintf( 'ywccp_%s', sanitize_key( $shipping_field ) ),
							'label' => sprintf( __( 'Shipping: %s', 'woocommerce-exporter' ), ( !empty( $shipping_options[$shipping_field]['label'] ) ? $shipping_options[$shipping_field]['label'] : str_replace( 'shipping_', '', $shipping_field ) ) ),
							'hover' => __( 'YITH WooCommerce Checkout Manager', 'woocommerce-exporter' )
						);
					}
				}
			}
			unset( $fields_keys, $default_keys, $shipping_fields, $shipping_field );
		}
		unset( $shipping_options );

		// Custom additional fields
		if( !empty( $additional_options ) ) {
			// Only add non-default Checkout fields to export columns list
			$default_keys = ywccp_get_default_fields_key( 'additional' );
			$fields_keys = array_keys( $additional_options );
			$additional_fields = array_diff( $fields_keys, $default_keys );
			if( !empty( $additional_fields ) ) {
				foreach( $additional_fields as $additional_field ) {
					// Check that the custom Additional field exists
					if( isset( $additional_options[$additional_field] ) ) {
						// Skip headings
						if( $additional_options[$additional_field]['type'] == 'heading' )
							continue;
						$fields[] = array(
							'name' => sprintf( 'ywccp_%s', sanitize_key( $additional_field ) ),
							'label' => sprintf( __( 'Additional: %s', 'woocommerce-exporter' ), ( !empty( $additional_options[$additional_field]['label'] ) ? $additional_options[$additional_field]['label'] : str_replace( 'additional_', '', $additional_field ) ) ),
							'hover' => __( 'YITH WooCommerce Checkout Manager', 'woocommerce-exporter' )
						);
					}
				}
			}
			unset( $fields_keys, $default_keys, $additional_fields, $additional_field );
		}
		unset( $additional_options );

	}

	// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
	if( woo_ce_detect_export_plugin( 'subscriptions' ) ) {
		$fields[] = array(
			'name' => 'order_type',
			'label' => __( 'Subscription Relationship', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'subscription_renewal',
			'label' => __( 'Subscription Renewal', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'subscription_resubscribe',
			'label' => __( 'Subscription Resubscribe', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'subscription_switch',
			'label' => __( 'Subscription Switch', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Quick Donation - http://wordpress.org/plugins/woocommerce-quick-donation/
	if( woo_ce_detect_export_plugin( 'wc_quickdonation' ) ) {
		$fields[] = array(
			'name' => 'project_id',
			'label' => __( 'Project ID', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Quick Donation', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'project_name',
			'label' => __( 'Project Name', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Quick Donation', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Easy Checkout Fields Editor - http://codecanyon.net/item/woocommerce-easy-checkout-field-editor/9799777
	if( woo_ce_detect_export_plugin( 'wc_easycheckout' ) ) {
		$custom_fields = get_option( 'pcfme_additional_settings' );
		if( !empty( $custom_fields ) ) {
			foreach( $custom_fields as $key => $custom_field ) {
				$fields[] = array(
					'name' => $key,
					'label' => sprintf( __( 'Additional: %s', 'woocommerce-exporter' ), ucfirst( $custom_field['label'] ) ),
					'hover' => __( 'WooCommerce Easy Checkout Fields Editor', 'woocommerce-exporter' )
				);
			}
			unset( $custom_fields, $custom_field );
		}
	}

	// WooCommerce Events - http://www.woocommerceevents.com/
	if( woo_ce_detect_export_plugin( 'wc_events' ) ) {
		$fields[] = array(
			'name' => 'tickets_purchased',
			'label' => __( 'Tickets Purchased', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Events', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Currency Switcher - http://dev.pathtoenlightenment.net/shop
	if( woo_ce_detect_export_plugin( 'currency_switcher' ) ) {
		$fields[] = array(
			'name' => 'order_currency',
			'label' => __( 'Order Currency', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Currency Switcher', 'woocommerce-exporter' )
		);
	}

	// WooCommerce EU VAT Number - https://www.woothemes.com/products/eu-vat-number/
	if( woo_ce_detect_export_plugin( 'eu_vat' ) ) {
		$fields[] = array(
			'name' => 'eu_vat',
			'label' => __( 'VAT ID', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce EU VAT Number', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'eu_vat_validated',
			'label' => __( 'VAT ID Validated', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce EU VAT Number', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'eu_vat_b2b',
			'label' => __( 'VAT B2B Transaction', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce EU VAT Number', 'woocommerce-exporter' )
		);
	}

	// WooCommerce EU VAT Assistant - https://wordpress.org/plugins/woocommerce-eu-vat-assistant/
	if( woo_ce_detect_export_plugin( 'aelia_eu_vat' ) ) {
		$fields[] = array(
			'name' => 'eu_vat',
			'label' => __( 'VAT ID', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce EU VAT Assistant', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'eu_vat_country',
			'label' => __( 'VAT ID Country', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce EU VAT Assistant', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'eu_vat_validated',
			'label' => __( 'VAT ID Validated', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce EU VAT Assistant', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'eu_vat_b2b',
			'label' => __( 'VAT B2B Transaction', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce EU VAT Assistant', 'woocommerce-exporter' )
		);
	}

	// WooCommerce EU VAT Compliance - https://wordpress.org/plugins/woocommerce-eu-vat-compliance/
	// WooCommerce EU VAT Compliance (Premium) - https://www.simbahosting.co.uk/s3/product/woocommerce-eu-vat-compliance/
	if( woo_ce_detect_export_plugin( 'wc_eu_vat_compliance' ) || woo_ce_detect_export_plugin( 'wc_eu_vat_compliance_pro' ) ) {
		if( woo_ce_detect_export_plugin( 'wc_eu_vat_compliance_pro' ) ) {
			$fields[] = array(
				'name' => 'eu_vat',
				'label' => __( 'VAT ID', 'woocommerce-exporter' ),
				'hover' => __( 'WooCommerce EU VAT Compliance (Premium)', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'eu_vat_validated',
				'label' => __( 'VAT ID Validated', 'woocommerce-exporter' ),
				'hover' => __( 'WooCommerce EU VAT Compliance (Premium)', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'eu_vat_valid_id',
				'label' => __( 'Valid VAT ID', 'woocommerce-exporter' ),
				'hover' => __( 'WooCommerce EU VAT Compliance (Premium)', 'woocommerce-exporter' )
			);
		}
		$fields[] = array(
			'name' => 'eu_vat_country',
			'label' => __( 'VAT ID Country', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce EU VAT Compliance', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'eu_vat_country_source',
			'label' => __( 'VAT Country Source', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce EU VAT Compliance', 'woocommerce-exporter' )
		);
		if( woo_ce_detect_export_plugin( 'wc_eu_vat_compliance_pro' ) ) {
			$fields[] = array(
				'name' => 'eu_vat_b2b',
				'label' => __( 'VAT B2B Transaction', 'woocommerce-exporter' ),
				'hover' => __( 'WooCommerce EU VAT Compliance (Premium)', 'woocommerce-exporter' )
			);
		}
	}

	// WooCommerce Jetpack - https://wordpress.org/plugins/woocommerce-jetpack/
	// WooCommerce Jetpack Plus - http://woojetpack.com/shop/wordpress-woocommerce-jetpack-plus/
	if( woo_ce_detect_export_plugin( 'woocommerce_jetpack' ) || woo_ce_detect_export_plugin( 'woocommerce_jetpack_plus' ) ) {
		$fields[] = array(
			'name' => 'eu_vat',
			'label' => __( 'EU VAT Number', 'woocommerce-exporter' ),
			'hover' => __( 'Booster for WooCommerce', 'woocommerce-exporter' )
		);
	}

	// AweBooking - https://codecanyon.net/item/awebooking-online-hotel-booking-for-wordpress/12323878
	if( woo_ce_detect_export_plugin( 'awebooking' ) ) {
		$fields[] = array(
			'name' => 'arrival_date',
			'label' => __( 'Arrival Date', 'woocommerce-exporter' ),
			'hover' => __( 'AweBooking', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'departure_date',
			'label' => __( 'Departure Date', 'woocommerce-exporter' ),
			'hover' => __( 'AweBooking', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'adults',
			'label' => __( 'Adults', 'woocommerce-exporter' ),
			'hover' => __( 'AweBooking', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'children',
			'label' => __( 'Children', 'woocommerce-exporter' ),
			'hover' => __( 'AweBooking', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'room_type_id',
			'label' => __( 'Room Type ID', 'woocommerce-exporter' ),
			'hover' => __( 'AweBooking', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'room_type_name',
			'label' => __( 'Room Type Name', 'woocommerce-exporter' ),
			'hover' => __( 'AweBooking', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Custom Admin Order Fields - http://www.woothemes.com/products/woocommerce-admin-custom-order-fields/
	if( woo_ce_detect_export_plugin( 'admin_custom_order_fields' ) ) {
		$ac_fields = get_option( 'wc_admin_custom_order_fields' );
		if( !empty( $ac_fields ) ) {
			foreach( $ac_fields as $ac_key => $ac_field ) {
				$fields[] = array(
					'name' => sprintf( 'wc_acof_%d', $ac_key ),
					'label' => sprintf( __( 'Admin Custom Order Field: %s', 'woocommerce-exporter' ), $ac_field['label'] )
				);
			}
		}
	}

	// YITH WooCommerce Delivery Date Premium - http://yithemes.com/themes/plugins/yith-woocommerce-delivery-date/
	if( woo_ce_detect_export_plugin( 'yith_delivery_pro' ) ) {
		$fields[] = array(
			'name' => 'shipping_date',
			'label' => __( 'Shipping Date', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce Delivery Date Premium', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'delivery_date',
			'label' => __( 'Delivery Date', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce Delivery Date Premium', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'delivery_time_slot',
			'label' => __( 'Delivery Time Slot', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce Delivery Date Premium', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Point of Sale - https://codecanyon.net/item/woocommerce-point-of-sale-pos/7869665
	if( woo_ce_detect_export_plugin( 'wc_point_of_sales' ) ) {
		$fields[] = array(
			'name' => 'order_type',
			'label' => __( 'Order Type', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Point of Sale', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_register_id',
			'label' => __( 'Register ID', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Point of Sale', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_cashier',
			'label' => __( 'Cashier', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Point of Sale', 'woocommerce-exporter' )
		);
	}

	// WooCommerce PDF Product Vouchers - http://www.woothemes.com/products/pdf-product-vouchers/
	if( woo_ce_detect_export_plugin( 'wc_pdf_product_vouchers' ) ) {
		$fields[] = array(
			'name' => 'voucher_redeemed',
			'label' => __( 'Voucher Redeemed', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce PDF Product Vouchers', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Delivery Slots - https://iconicwp.com/products/woocommerce-delivery-slots/
	if( woo_ce_detect_export_plugin( 'wc_deliveryslots' ) ) {
		$fields[] = array(
			'name' => 'delivery_date',
			'label' => __( 'Delivery Date', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Delivery Slots', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'delivery_timeslot',
			'label' => __( 'Delivery Timeslot', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Delivery Slots', 'woocommerce-exporter' )
		);
	}
	
	// WooCommerce Ship to Multiple Addresses - http://woothemes.com/woocommerce
	if( woo_ce_detect_export_plugin( 'wc_ship_multiple' ) ) {
		$fields[] = array(
			'name' => 'wcms_number_packages',
			'label' => __( 'Number of Packages', 'woocommerce-exporter' ),
			'hover' => __( 'Ship to Multiple Addresses', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Deposits - https://woocommerce.com/products/woocommerce-deposits/
	if( woo_ce_detect_export_plugin( 'wc_deposits' ) ) {
		$fields[] = array(
			'name' => 'has_deposit',
			'label' => __( 'Has Deposit', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Deposits', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'deposit_paid',
			'label' => __( 'Deposit Paid', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Deposits', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'deposit_second_payment_paid',
			'label' => __( 'Second Payment Paid', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Deposits', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'deposit_amount',
			'label' => __( 'Deposit Amount', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Deposits', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'deposit_second_payment',
			'label' => __( 'Second Payment Amount', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Deposits', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'deposit_original_total',
			'label' => __( 'Original Total', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Deposits', 'woocommerce-exporter' )
		);
	}

	// Tickera - https://tickera.com/
	if( woo_ce_detect_export_plugin( 'tickera' ) ) {
		$fields[] = array(
			'name' => 'ticket_id',
			'label' => __( 'Ticket ID', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_code',
			'label' => __( 'Ticket Code', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_type_id',
			'label' => __( 'Ticket Type ID', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_event_id',
			'label' => __( 'Ticket Event ID', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_first_name',
			'label' => __( 'Ticket First Name', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'ticket_last_name',
			'label' => __( 'Ticket Last Name', 'woocommerce-exporter' ),
			'hover' => __( 'Tickera', 'woocommerce-exporter' )
		);
		$tickera_fields = woo_ce_get_tickera_custom_fields();
		if( !empty( $tickera_fields ) ) {
			foreach( $tickera_fields as $tickera_field ) {
				$fields[] = array(
					'name' => sprintf( 'ticket_custom_%s', sanitize_key( $tickera_field['name'] ) ),
					'label' => sprintf( __( 'Ticket: %s', 'woocommerce-exporter' ), $tickera_field['label'] ),
					'hover' => __( 'Tickera', 'woocommerce-exporter' )
				);
			}
		}
		unset( $tickera_fields );

	}

	// WooCommerce Stripe Payment Gateway - https://wordpress.org/plugins/woocommerce-gateway-stripe/
	if( woo_ce_detect_export_plugin( 'wc_stripe' ) ) {
		$fields[] = array(
			'name' => 'stripe_customer_id',
			'label' => __( 'Stripe: Customer ID', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Stripe Payment Gateway', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'stripe_card_id',
			'label' => __( 'Stripe: Card ID', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Stripe Payment Gateway', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'stripe_charge_captured',
			'label' => __( 'Stripe: Charge Captured', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Stripe Payment Gateway', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'stripe_payment_id',
			'label' => __( 'Stripe: Payment ID', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Stripe Payment Gateway', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'stripe_fee',
			'label' => __( 'Stripe: Fee', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Stripe Payment Gateway', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'stripe_net_revenue',
			'label' => __( 'Stripe: Net Revenue from Stripe', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Stripe Payment Gateway', 'woocommerce-exporter' )
		);
	}

	// YITH WooCommerce PDF Invoice and Shipping List
	if( woo_ce_detect_export_plugin( 'yith_ywpi' ) ) {
		$fields[] = array(
			'name' => 'has_invoice',
			'label' => __( 'Has Invoice', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce PDF Invoice and Shipping List', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'invoice_number',
			'label' => __( 'Invoice Number', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce PDF Invoice and Shipping List', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'invoice_number_formatted',
			'label' => __( 'Invoice Number (Formatted)', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce PDF Invoice and Shipping List', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'invoice_date',
			'label' => __( 'Invoice Date', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce PDF Invoice and Shipping List', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
	if( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		if( get_option( 'wccf_migrated_to_20' ) ) {
			// Order Fields
			$custom_fields = woo_ce_get_wccf_order_fields();
			if( !empty( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field ) {
					$label = get_post_meta( $custom_field->ID, 'label', true );
					$key = get_post_meta( $custom_field->ID, 'key', true );
					$fields[] = array(
						'name' => sprintf( 'wccf_of_%s', sanitize_key( $key ) ),
						'label' => ucfirst( $label ),
						'hover' => sprintf( '%s: %s (%s)', __( 'WooCommerce Custom Fields', 'woocommerce-exporter' ), __( 'Order Field', 'woocommerce-exporter' ), sanitize_key( $key ) )
					);
				}
			}
			unset( $custom_fields, $custom_field, $label, $key );
			// Checkout Fields
			$custom_fields = woo_ce_get_wccf_checkout_fields();
			if( !empty( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field ) {
					$label = get_post_meta( $custom_field->ID, 'label', true );
					$key = get_post_meta( $custom_field->ID, 'key', true );
					$fields[] = array(
						'name' => sprintf( 'wccf_cf_%s', sanitize_key( $key ) ),
						'label' => ucfirst( $label ),
						'hover' => sprintf( '%s: %s (%s)', __( 'WooCommerce Custom Fields', 'woocommerce-exporter' ), __( 'Checkout Field', 'woocommerce-exporter' ), sanitize_key( $key ) )
					);
				}
			}
			unset( $custom_fields, $custom_field, $label, $key );
		}
	}

	// Custom User fields
	$custom_users = woo_ce_get_option( 'custom_users', '' );
	if( !empty( $custom_users ) ) {
		foreach( $custom_users as $custom_user ) {
			if( !empty( $custom_user ) ) {
				$fields[] = array(
					'name' => $custom_user,
					'label' => woo_ce_clean_export_label( $custom_user ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_order_fields_custom_user_hover', '%s: %s' ), __( 'Custom User', 'woocommerce-exporter' ), $custom_user )
				);
			}
		}
	}
	unset( $custom_users, $custom_user );

	// Custom Order fields
	$custom_orders = woo_ce_get_option( 'custom_orders', '' );
	if( !empty( $custom_orders ) ) {
		foreach( $custom_orders as $custom_order ) {
			if( !empty( $custom_order ) ) {
				$fields[] = array(
					'name' => $custom_order,
					'label' => woo_ce_clean_export_label( $custom_order ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_order_fields_custom_order_hover', '%s: %s' ), __( 'Custom Order', 'woocommerce-exporter' ), $custom_order )
				);
			}
		}
		unset( $custom_orders, $custom_order );
	}

	// Order Items go in woo_ce_extend_order_items_fields()

	return $fields;

}
add_filter( 'woo_ce_order_fields', 'woo_ce_extend_order_fields' );

// Adds custom Order Item columns to the Order Items fields list
function woo_ce_extend_order_items_fields( $fields = array() ) {

	// WooCommerce Checkout Add-Ons - http://www.skyverge.com/product/woocommerce-checkout-add-ons/
	if( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
		$fields[] = array(
			'name' => 'order_items_checkout_addon_id',
			'label' => __( 'Order Items: Checkout Add-ons ID', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Checkout Add-Ons', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_checkout_addon_label',
			'label' => __( 'Order Items: Checkout Add-ons Label', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Checkout Add-Ons', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_checkout_addon_value',
			'label' => __( 'Order Items: Checkout Add-ons Value', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Checkout Add-Ons', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	if( woo_ce_detect_product_brands() ) {
		$fields[] = array(
			'name' => 'order_items_brand',
			'label' => __( 'Order Items: Brand', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Brands or WooCommerce Brands Addon', 'woocommerce-exporter' )
		);
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) ) {
		$fields[] = array(
			'name' => 'order_items_vendor',
			'label' => __( 'Order Items: Product Vendor', 'woocommerce-exporter' ),
			'hover' => __( 'Product Vendors', 'woocommerce-exporter' )
		);
	}

	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		$fields[] = array(
			'name' => 'order_items_vendor',
			'label' => __( 'Order Items: Product Vendor', 'woocommerce-exporter' ),
			'hover' => __( 'Product Vendors', 'woocommerce-exporter' )
		);
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/
	if( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		$fields[] = array(
			'name' => 'cost_of_goods',
			'label' => __( 'Order Total Cost of Goods', 'woocommerce-exporter' ),
			'hover' => __( 'Cost of Goods', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_cost_of_goods',
			'label' => __( 'Order Items: Cost of Goods', 'woocommerce-exporter' ),
			'hover' => __( 'Cost of Goods', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_total_cost_of_goods',
			'label' => __( 'Order Items: Total Cost of Goods', 'woocommerce-exporter' ),
			'hover' => __( 'Cost of Goods', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590
	if( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		$fields[] = array(
			'name' => 'order_items_posr',
			'label' => __( 'Order Items: Cost of Good', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Profit of Sales Report', 'woocommerce-exporter' )
		);
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_msrp' ) ) {
		$fields[] = array(
			'name' => 'order_items_msrp',
			'label' => __( 'Order Items: MSRP', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce MSRP Pricing', 'woocommerce-exporter' )
		);
	}

	// Local Pickup Plus - http://www.woothemes.com/products/local-pickup-plus/
	if( woo_ce_detect_export_plugin( 'local_pickup_plus' ) ) {
		$fields[] = array(
			'name' => 'order_items_pickup_location',
			'label' => __( 'Order Items: Pickup Location', 'woocommerce-exporter' ),
			'hover' => __( 'Local Pickup Plus', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	if( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		$fields[] = array(
			'name' => 'order_items_booking_id',
			'label' => __( 'Order Items: Booking ID', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_date',
			'label' => __( 'Order Items: Booking Date', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_type',
			'label' => __( 'Order Items: Booking Type', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_start_date',
			'label' => __( 'Order Items: Start Date', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_end_date',
			'label' => __( 'Order Items: End Date', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_all_day',
			'label' => __( 'Order Items: All Day Booking' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_resource_id',
			'label' => __( 'Order Items: Booking Resource ID', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_resource_title',
			'label' => __( 'Order Items: Booking Resource Name', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_persons',
			'label' => __( 'Order Items: Booking # of Persons', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Bookings', 'woocommerce-exporter' )
		);
	}

	// Gravity Forms - http://woothemes.com/woocommerce
	if( woo_ce_detect_export_plugin( 'gravity_forms' ) && woo_ce_detect_export_plugin( 'woocommerce_gravity_forms' ) ) {
		// Check if there are any Products linked to Gravity Forms
		$gf_fields = woo_ce_get_gravity_forms_fields();
		if( !empty( $gf_fields ) ) {
			$fields[] = array(
				'name' => 'order_items_gf_form_id',
				'label' => __( 'Order Items: Gravity Form ID', 'woocommerce-exporter' ),
				'hover' => __( 'Gravity Forms', 'woocommerce-exporter' )
			);
			$fields[] = array(
				'name' => 'order_items_gf_form_label',
				'label' => __( 'Order Items: Gravity Form Label', 'woocommerce-exporter' ),
				'hover' => __( 'Gravity Forms', 'woocommerce-exporter' )
			);
			foreach( $gf_fields as $gf_field ) {
				$gf_field_duplicate = false;
				// Check if this isn't a duplicate Gravity Forms field
				foreach( $fields as $field ) {
					if( isset( $field['name'] ) && $field['name'] == sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] ) ) {
						// Duplicate exists
						$gf_field_duplicate = true;
						break;
					}
				}
				// If it's not a duplicate go ahead and add it to the list
				if( $gf_field_duplicate !== true ) {
					$fields[] = array(
						'name' => sprintf( 'order_items_gf_%d_%s', $gf_field['formId'], $gf_field['id'] ),
						'label' => sprintf( apply_filters( 'woo_ce_extend_order_fields_gf_label', __( 'Order Items: %s - %s', 'woocommerce-exporter' ) ), ucwords( strtolower( $gf_field['formTitle'] ) ), ucfirst( strtolower( $gf_field['label'] ) ) ),
						'hover' => sprintf( apply_filters( 'woo_ce_extend_order_fields_gf_hover', '%s: %s (ID: %d)' ), __( 'Gravity Forms', 'woocommerce-exporter' ), ucwords( strtolower( $gf_field['formTitle'] ) ), $gf_field['formId'] )
					);
				}
			}
		}
		unset( $gf_fields, $gf_field );
	}

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
	if( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
		$tm_fields = woo_ce_get_extra_product_option_fields();
		if( !empty( $tm_fields ) ) {
			foreach( $tm_fields as $tm_field ) {
				if( !isset( $tm_field['section_label'] ) )
					$tm_field['section_label'] = '';
				$fields[] = array(
					'name' => sprintf( 'order_items_tm_%s', sanitize_key( $tm_field['name'] ) ),
					'label' => sprintf( __( 'Order Items: %s', 'woocommerce-exporter' ), ( !empty( $tm_field['section_label'] ) ? $tm_field['section_label'] : $tm_field['name'] ) ),
					'hover' => __( 'WooCommerce TM Extra Product Options', 'woocommerce-exporter' )
				);
			}
			unset( $tm_fields, $tm_field );
		}
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
	if( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		if( !get_option( 'wccf_migrated_to_20' ) ) {
			$options = get_option( 'rp_wccf_options' );
			if( !empty( $options ) ) {
				$options = ( isset( $options[1] ) ? $options[1] : false );
				if( !empty( $options ) ) {
					// Product Fields
					$custom_fields = ( isset( $options['product_fb_config'] ) ? $options['product_fb_config'] : false );
					if( !empty( $custom_fields ) ) {
						foreach( $custom_fields as $custom_field ) {
							$fields[] = array(
								'name' => sprintf( 'order_items_wccf_%s', sanitize_key( $custom_field['key'] ) ),
								'label' => sprintf( __( 'Order Items: %s', 'woocommerce-exporter' ), ucfirst( $custom_field['label'] ) ),
								'hover' => sprintf( '%s: %s (ID: %s)', __( 'WooCommerce Custom Fields', 'woocommerce-exporter' ), __( 'Product Field', 'woocommerce-exporter' ), sanitize_key( $custom_field['key'] ) )
							);
						}
						unset( $custom_fields, $custom_field );
					}
				}
				unset( $options );
			}
		} else {
			// Product Fields
			$custom_fields = woo_ce_get_wccf_product_fields();
			if( !empty( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field ) {
					$label = get_post_meta( $custom_field->ID, 'label', true );
					$key = get_post_meta( $custom_field->ID, 'key', true );
					$fields[] = array(
						'name' => sprintf( 'order_items_wccf_%s', sanitize_key( $key ) ),
						'label' => sprintf( __( 'Order Items: %s', 'woocommerce-exporter' ), ucfirst( $label ) ),
						'hover' => sprintf( '%s: %s (ID: %s)', __( 'WooCommerce Custom Fields', 'woocommerce-exporter' ), __( 'Product Field', 'woocommerce-exporter' ), sanitize_key( $key ) )
					);
				}
			}
			unset( $custom_fields, $custom_field, $key );
		}
	}

	// WooCommerce Product Custom Options Lite - https://wordpress.org/plugins/woocommerce-custom-options-lite/
	if( woo_ce_detect_export_plugin( 'wc_product_custom_options' ) ) {
		$custom_options = woo_ce_get_product_custom_options();
		if( !empty( $custom_options ) ) {
			foreach( $custom_options as $custom_option ) {
				$fields[] = array(
					'name' => sprintf( 'order_items_pco_%s', sanitize_key( $custom_option ) ),
					'label' => sprintf( __( 'Order Items: %s', 'woocommerce-exporter' ), ucfirst( $custom_option ) ),
					'hover' => __( 'WooCommerce Product Custom Options Lite', 'woocommerce-exporter' )
				);
			}
		}
	}

	// Barcodes for WooCommerce - http://www.wolkenkraft.com/produkte/barcodes-fuer-woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_barcodes' ) ) {
		$fields[] = array(
			'name' => 'order_items_barcode_type',
			'label' => __( 'Order Items: Barcode Type', 'woocommerce-exporter' ),
			'hover' => __( 'Barcodes for WooCommerce', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_barcode',
			'label' => __( 'Order Items: Barcode', 'woocommerce-exporter' ),
			'hover' => __( 'Barcodes for WooCommerce', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Easy Bookings - https://wordpress.org/plugins/woocommerce-easy-booking-system/
	if( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		$fields[] = array(
			'name' => 'order_items_booking_start_date',
			'label' => __( 'Order Items: Start', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Easy Bookings', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_end_date',
			'label' => __( 'Order Items: End', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Easy Bookings', 'woocommerce-exporter' )
		);
	}

	// N-Media WooCommerce Personalized Product Meta Manager
	if( woo_ce_detect_export_plugin( 'wc_nm_personalizedproduct' ) ) {
		$custom_fields = woo_ce_get_nm_personalized_product_fields();
		if( !empty( $custom_fields ) ) {
			foreach( $custom_fields as $custom_field ) {
				$fields[] = array(
					'name' => sprintf( 'order_items_nm_%s', $custom_field['name'] ),
					'label' => sprintf( __( 'Order Items: %s', 'woocommerce-exporter' ), ucfirst( $custom_field['label'] ) ),
					'hover' => __( 'N-Media WooCommerce Personalized Product Meta Manager', 'woocommerce-exporter' )
				);
			}
		}
	}

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/
	if( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		$fields[] = array(
			'name' => 'order_items_appointment_id',
			'label' => __( 'Order Items: Appointment ID', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_start_date',
			'label' => __( 'Order Items: Start Date', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_start_time',
			'label' => __( 'Order Items: Start Time', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_end_date',
			'label' => __( 'Order Items: End Date', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_end_time',
			'label' => __( 'Order Items: End Time', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'order_items_booking_all_day',
			'label' => __( 'Order Items: All Day Booking' ),
			'hover' => __( 'WooCommerce Appointments', 'woocommerce-exporter' )
		);
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/
	if( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if( !empty( $wholesale_roles ) ) {
			foreach( $wholesale_roles as $key => $wholesale_role ) {
				$fields[] = array(
					'name' => sprintf( 'order_items_%s_wholesale_price', $key ),
					'label' => sprintf( __( 'Order Items: Wholesale Price: %s', 'woocommerce-exporter' ), $wholesale_role['roleName'] ),
					'hover' => __( 'WooCommerce Wholesale Prices', 'woocommerce-exporter' )
				);
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	if( apply_filters( 'woo_ce_enable_product_attributes', true ) ) {
		// Product Attributes
		$attributes = woo_ce_get_product_attributes();
		if( !empty( $attributes ) ) {
			foreach( $attributes as $attribute ) {
				$attribute->attribute_label = trim( $attribute->attribute_label );
				if( empty( $attribute->attribute_label ) )
					$attribute->attribute_label = $attribute->attribute_name;
				// First row is to fetch the Variation Attribute linked to the Order Item
				$fields[] = array(
					'name' => sprintf( 'order_items_attribute_%s', sanitize_key( $attribute->attribute_name ) ),
					'label' => sprintf( __( 'Order Items: %s Variation', 'woocommerce-exporter' ), ucwords( $attribute->attribute_label ) ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_order_fields_attribute', '%s: %s (#%d)' ), __( 'Product Variation', 'woocommerce-exporter' ), $attribute->attribute_name, $attribute->attribute_id )
				);
				// The second row is to fetch the Product Attribute from the Order Item Product
				$fields[] = array(
					'name' => sprintf( 'order_items_product_attribute_%s', sanitize_key( $attribute->attribute_name ) ),
					'label' => sprintf( __( 'Order Items: %s Attribute', 'woocommerce-exporter' ), ucwords( $attribute->attribute_label ) ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_order_fields_product_attribute', '%s: %s (#%d)' ), __( 'Product Attribute', 'woocommerce-exporter' ), $attribute->attribute_name, $attribute->attribute_id )
				);
			}
			unset( $attributes, $attribute );
		}
	}

	// Custom Order Items fields
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	if( !empty( $custom_order_items ) ) {
		foreach( $custom_order_items as $custom_order_item ) {
			if( !empty( $custom_order_item ) ) {
				$fields[] = array(
					'name' => sprintf( 'order_items_%s', sanitize_key( $custom_order_item ) ),
					'label' => sprintf( __( 'Order Items: %s', 'woocommerce-exporter' ), woo_ce_clean_export_label( $custom_order_item ) ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_order_fields_custom_order_item_hover', '%s: %s' ), __( 'Custom Order Item', 'woocommerce-exporter' ), $custom_order_item )
				);
			}
		}
	}
	unset( $custom_order_items, $custom_order_item );

	// Custom Order Item Product fields
	$custom_order_products = woo_ce_get_option( 'custom_order_products', '' );
	if( !empty( $custom_order_products ) ) {
		foreach( $custom_order_products as $custom_order_product ) {
			if( !empty( $custom_order_product ) ) {
				$fields[] = array(
					'name' => sprintf( 'order_items_%s', sanitize_key( $custom_order_product ) ),
					'label' => sprintf( __( 'Order Items: %s', 'woocommerce-exporter' ), woo_ce_clean_export_label( $custom_order_product ) ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_order_fields_custom_order_product_hover', '%s: %s' ), __( 'Custom Order Item Product', 'woocommerce-exporter' ), $custom_order_product )
				);
			}
		}
	}
	unset( $custom_order_products, $custom_order_product );

	// Custom Product fields
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if( !empty( $custom_products ) ) {
		foreach( $custom_products as $custom_product ) {
			if( !empty( $custom_product ) ) {
				$fields[] = array(
					'name' => sprintf( 'order_items_%s', sanitize_key( $custom_product ) ),
					'label' => sprintf( __( 'Order Items: %s', 'woocommerce-exporter' ), woo_ce_clean_export_label( $custom_product ) ),
					'hover' => sprintf( apply_filters( 'woo_ce_extend_order_fields_custom_product_hover', '%s: %s' ), __( 'Custom Product', 'woocommerce-exporter' ), $custom_product )
				);
			}
		}
	}
	unset( $custom_products, $custom_product );

	return $fields;

}
add_filter( 'woo_ce_order_items_fields', 'woo_ce_extend_order_items_fields' );

// Populate Order details for export of 3rd party Plugins
function woo_ce_order_extend( $order, $order_id ) {

	global $export;

	// WordPress MultiSite
	if( is_multisite() ) {
		$order->blog_id = get_current_blog_id();
	}

	// WooCommerce Sequential Order Numbers - http://www.skyverge.com/blog/woocommerce-sequential-order-numbers/
	if( woo_ce_detect_export_plugin( 'seq' ) ) {
		// Override the Purchase ID if this Plugin exists and Post meta isn't empty
		$order_number = get_post_meta( $order_id, '_order_number', true );
		if( !empty( $order_number ) )
			$order->purchase_id = $order_number;
		else
			$order->purchase_id = $order_id;
		unset( $order_number );
	}

	// Sequential Order Numbers Pro - http://www.woothemes.com/products/sequential-order-numbers-pro/
	if( woo_ce_detect_export_plugin( 'seq_pro' ) ) {
		// Override the Purchase ID if this Plugin exists and Post meta isn't empty
		$order_number = get_post_meta( $order_id, '_order_number_formatted', true );
		if( !empty( $order_number ) ) {
			$order->purchase_id = $order_number;
		} else {
			// Fallback to the default _order_number Post meta
			$order_number = get_post_meta( $order_id, '_order_number', true );
			if( !empty( $order_number ) )
				$order->purchase_id = $order_number;
			else
				$order->purchase_id = $order_id;
		}
		unset( $order_number );
	}

	// WooCommerce Jetpack - https://wordpress.org/plugins/woocommerce-jetpack/
	// WooCommerce Jetpack Plus - http://woojetpack.com/shop/wordpress-woocommerce-jetpack-plus/
	if( woo_ce_detect_export_plugin( 'woocommerce_jetpack' ) || woo_ce_detect_export_plugin( 'woocommerce_jetpack_plus' ) ) {

		// Order Numbers
		if( class_exists( 'WCJ_Order_Numbers' ) ) {
			// Use WooCommerce Jetpack Plus's display_order_number() to handle formatting
			$order_numbers = new WCJ_Order_Numbers();
			if( method_exists( $order_numbers, 'display_order_number' ) )
				$order->purchase_id = $order_numbers->display_order_number( $order_id, $order );
			unset( $order_numbers );
		} else {
			// Fall-back to old school get_post_meta()
			$order_number = get_post_meta( $order_id, '_wcj_order_number', true );
			// Override the Purchase ID if this Plugin exists and Post meta isn't empty
			if( !empty( $order_number ) && get_option( 'wcj_order_numbers_enabled', 'no' ) !== 'no' )
				$order->purchase_id = $order_number;
			unset( $order_number );
		}

		// WCJ_EU_VAT_Number
		if( class_exists( 'WCJ_EU_VAT_Number' ) ) {
			$order->eu_vat = get_post_meta( $order_id, '_billing_eu_vat_number', true );
		}

	}

	// WooCommerce Basic Ordernumbers - http://open-tools.net/woocommerce/advanced-ordernumbers-for-woocommerce.html
	if( woo_ce_detect_export_plugin( 'order_numbers_basic' ) ) {
		$order_number = get_post_meta( $order_id, '_oton_number_ordernumber', true );
		// Override the Purchase ID if this Plugin exists and Post meta isn't empty
		if( !empty( $order_number ) && get_option( 'customize_ordernumber', 'no' ) !== 'no' )
			$order->purchase_id = $order_number;
		unset( $order_number );
	}

	// WooCommerce Checkout Manager - http://wordpress.org/plugins/woocommerce-checkout-manager/
	// WooCommerce Checkout Manager Pro - http://wordpress.org/plugins/woocommerce-checkout-manager/
	if( woo_ce_detect_export_plugin( 'checkout_manager' ) ) {

		// Load generic settings
		$options = get_option( 'wccs_settings' );
		if( isset( $options['buttons'] ) ) {
			$buttons = $options['buttons'];
			if( !empty( $buttons ) ) {
				foreach( $buttons as $button ) {
					// Skip headings
					if( $button['type'] == 'heading' )
						continue;
					$order->{sprintf( 'additional_%s', $button['cow'] )} = woo_ce_format_custom_meta( get_post_meta( $order_id, $button['cow'], true ) );
				}
				unset( $buttons, $button );
			}
		}
		unset( $options );

		// Load Shipping settings
		$options = get_option( 'wccs_settings2' );
		if( isset( $options['shipping_buttons'] ) ) {
			$buttons = $options['shipping_buttons'];
			if( !empty( $buttons ) ) {
				foreach( $buttons as $button ) {
					// Skip headings
					if( $button['type'] == 'heading' )
						continue;
					$order->{sprintf( 'shipping_%s', $button['cow'] )} = woo_ce_format_custom_meta( get_post_meta( $order_id, sprintf( '_shipping_%s', $button['cow'] ), true ) );
				}
				unset( $buttons, $button );
			}
		}
		unset( $options );

		// Load Billing settings
		$options = get_option( 'wccs_settings3' );
		if( isset( $options['billing_buttons'] ) ) {
			$buttons = $options['billing_buttons'];
			if( !empty( $buttons ) ) {
				foreach( $buttons as $button ) {
					// Skip headings
					if( $button['type'] == 'heading' )
						continue;
					$order->{sprintf( 'billing_%s', $button['cow'] )} = woo_ce_format_custom_meta( get_post_meta( $order_id, sprintf( '_billing_%s', $button['cow'] ), true ) );
				}
				unset( $buttons, $button );
			}
		}
		unset( $options );
	}

	// Poor Guys Swiss Knife - http://wordpress.org/plugins/woocommerce-poor-guys-swiss-knife/
	if( woo_ce_detect_export_plugin( 'wc_pgsk' ) ) {
		$options = get_option( 'wcpgsk_settings' );
		$billing_fields = ( isset( $options['woofields']['billing'] ) ? $options['woofields']['billing'] : array() );
		$shipping_fields = ( isset( $options['woofields']['shipping'] ) ? $options['woofields']['shipping'] : array() );
		// Custom billing fields
		if( !empty( $billing_fields ) ) {
			foreach( $billing_fields as $key => $billing_field )
				$order->$key = get_post_meta( $order_id, sprintf( '_%s', $key ), true );
			unset( $billing_fields, $billing_field );
		}
		// Custom shipping fields
		if( !empty( $shipping_fields ) ) {
			foreach( $shipping_fields as $key => $shipping_field )
				$order->$key = get_post_meta( $order_id, sprintf( '_%s', $key ), true );
			unset( $shipping_fields, $shipping_field );
		}
		unset( $options );
	}

	// Checkout Field Editor - http://woothemes.com/woocommerce/
	if( woo_ce_detect_export_plugin( 'checkout_field_editor' ) ) {
		$billing_fields = get_option( 'wc_fields_billing', array() );
		$shipping_fields = get_option( 'wc_fields_shipping', array() );
		$additional_fields = get_option( 'wc_fields_additional', array() );

		// Custom billing fields
		if( !empty( $billing_fields ) ) {
			foreach( $billing_fields as $key => $billing_field ) {
				// Only add non-default Checkout fields to export columns list
				if( $billing_field['custom'] == 1 ) {
					$billing_field['value'] = get_post_meta( $order_id, $key, true );
					if( $billing_field['value'] != '' ) {
						if( $billing_field['type'] == 'checkbox' )
							$order->{sprintf( 'wc_billing_%s', $key )} = $billing_field['value'] == '1' ? __( 'Yes', 'woocommerce-exporter' ) : __( 'No', 'woocommerce-exporter' );
						else
							$order->{sprintf( 'wc_billing_%s', $key )} = $billing_field['value'];
					}
				}
			}
		}
		unset( $billing_fields, $billing_field );

		// Custom shipping fields
		if( !empty( $shipping_fields ) ) {
			foreach( $shipping_fields as $key => $shipping_field ) {
				// Only add non-default Checkout fields to export columns list
				if( $shipping_field['custom'] == 1 ) {
					$shipping_field['value'] = get_post_meta( $order_id, $key, true );
					if( $shipping_field['value'] != '' ) {
						if( $shipping_field['type'] == 'checkbox' )
							$order->{sprintf( 'wc_shipping_%s', $key )} = $shipping_field['value'] == '1' ? __( 'Yes', 'woocommerce-exporter' ) : __( 'No', 'woocommerce-exporter' );
						else
							$order->{sprintf( 'wc_shipping_%s', $key )} = $shipping_field['value'];
					}
				}
			}
		}
		unset( $shipping_fields, $shipping_field );

		// Additional fields
		if( !empty( $additional_fields ) ) {
			foreach( $additional_fields as $key => $additional_field ) {
				// Only add non-default Checkout fields to export columns list
				if( $additional_field['custom'] == 1 ) {
					$additional_field['value'] = get_post_meta( $order_id, $key, true );
					if( $additional_field['value'] != '' ) {
						if( $additional_field['type'] == 'checkbox' )
							$order->{sprintf( 'wc_additional_%s', $key )} = $additional_field['value'] == '1' ? __( 'Yes', 'woocommerce-exporter' ) : __( 'No', 'woocommerce-exporter' );
						else
							$order->{sprintf( 'wc_additional_%s', $key )} = $additional_field['value'];
					}
				}
			}
		}
		unset( $additional_fields, $additional_field );
	}

	// Checkout Field Editor Pro - https://www.themehigh.com/product/woocommerce-checkout-field-editor-pro/
	if( woo_ce_detect_export_plugin( 'checkout_field_editor_pro' ) ) {
		// Check if the Class is available
		if( class_exists( 'WCFE_Checkout_Fields_Export_Handler' ) ) {
			$wc_export = new WCFE_Checkout_Fields_Export_Handler();
			if( method_exists( $wc_export, 'get_export_fields' ) ) {
				$options = $wc_export->get_export_fields();
				if( !empty( $options ) ) {
					foreach( $options as $key => $option )
						$order->{sprintf( 'cfep_%s', $option['name'] )} = get_post_meta( $order_id, $key, true );
					unset( $options, $option );
				}
			}
			unset( $wc_export );
		}
	}

	// Checkout Field Manager - http://61extensions.com
	if( woo_ce_detect_export_plugin( 'checkout_field_manager' ) ) {
		// Custom billing fields
		$billing_fields = get_option( 'woocommerce_checkout_billing_fields', array() );
		$shipping_fields = get_option( 'woocommerce_checkout_shipping_fields', array() );
		$custom_fields = get_option( 'woocommerce_checkout_additional_fields', array() );

		// Custom billing fields
		if( !empty( $billing_fields ) ) {
			foreach( $billing_fields as $key => $billing_field ) {
				// Only add non-default Checkout fields to export columns list
				if( strtolower( $billing_field['default_field'] ) != 'on' ) {
					$billing_field['value'] = get_post_meta( $order_id, sprintf( '_%s', $billing_field['name'] ), true );
					if( $billing_field['value'] != '' ) {
						// Override for the checkbox field type
						if( $billing_field['type'] == 'checkbox' )
							$order->{sprintf( 'sod_billing_%s', $billing_field['name'] )} = strtolower( $billing_field['value'] == 'on' ) ? __( 'Yes', 'woocommerce-exporter' ) : __( 'No', 'woocommerce-exporter' );
						else
							$order->{sprintf( 'sod_billing_%s', $billing_field['name'] )} = $billing_field['value'];
					}
				}
			}
		}
		unset( $billing_fields, $billing_field );

		// Custom shipping fields
		if( !empty( $shipping_fields ) ) {
			foreach( $shipping_fields as $key => $shipping_field ) {
				// Only add non-default Checkout fields to export columns list
				if( strtolower( $shipping_field['default_field'] ) != 'on' ) {
					$shipping_field['value'] = get_post_meta( $order_id, sprintf( '_%s', $shipping_field['name'] ), true );
					if( $shipping_field['value'] != '' ) {
						// Override for the checkbox field type
						if( $shipping_field['type'] == 'checkbox' )
							$order->{sprintf( 'sod_shipping_%s', $shipping_field['name'] )} = strtolower( $shipping_field['value'] == 'on' ) ? __( 'Yes', 'woocommerce-exporter' ) : __( 'No', 'woocommerce-exporter' );
						else
							$order->{sprintf( 'sod_shipping_%s', $shipping_field['name'] )} = $shipping_field['value'];
					}
				}
			}
		}
		unset( $shipping_fields, $shipping_field );

		// Custom fields
		if( !empty( $custom_fields ) ) {
			foreach( $custom_fields as $key => $custom_field ) {
				// Only add non-default Checkout fields to export columns list
				if( strtolower( $custom_field['default_field'] ) != 'on' ) {
					$custom_field['value'] = get_post_meta( $order_id, sprintf( '_%s', $custom_field['name'] ), true );
					if( $custom_field['value'] != '' ) {
						// Override for the checkbox field type
						if( $custom_field['type'] == 'checkbox' )
							$order->{sprintf( 'sod_additional_%s', $custom_field['name'] )} = strtolower( $custom_field['value'] == 'on' ) ? __( 'Yes', 'woocommerce-exporter' ) : __( 'No', 'woocommerce-exporter' );
						else
							$order->{sprintf( 'sod_additional_%s', $custom_field['name'] )} = $custom_field['value'];
					}
				}
			}
		}
		unset( $custom_fields, $custom_field );
	}

	// WooCommerce Print Invoice & Delivery Note - https://wordpress.org/plugins/woocommerce-delivery-notes/
	if( woo_ce_detect_export_plugin( 'print_invoice_delivery_note' ) ) {
		if( function_exists( 'wcdn_get_order_invoice_number' ) )
			$order->invoice_number = wcdn_get_order_invoice_number( $order_id );
		if( function_exists( 'wcdn_get_order_invoice_date' ) )
			$order->invoice_date = wcdn_get_order_invoice_date( $order_id );
	}

	// WooCommerce PDF Invoices & Packing Slips - http://www.wpovernight.com
	if( woo_ce_detect_export_plugin( 'pdf_invoices_packing_slips' ) ) {
		// Check if the PDF Invoice has been generated
		$invoice_exists_legacy = get_post_meta( $order_id, '_wcpdf_invoice_exists', true );
		$invoice_exists = get_post( $order_id, '_wcpdf_invoice_number_data', true );
		if( !empty( $invoice_exists_legacy ) || !empty( $invoice_exists ) ) {
			// Check if the Invoice Number formatting Class is available
			if( class_exists( 'WooCommerce_PDF_Invoices_Export' ) ) {
				$wcpdf = new WooCommerce_PDF_Invoices_Export();
				if( method_exists( $wcpdf, 'get_invoice_number' ) )
					$order->pdf_invoice_number = $wcpdf->get_invoice_number( $order_id );
				unset( $wcpdf );
			} else {
				// Default back to _wcpdf_invoice_number
				$order->pdf_invoice_number = get_post_meta( $order_id, '_wcpdf_invoice_number', true );
			}
			$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );
			$timestamp = get_post_meta( $order_id, '_wcpdf_invoice_date', true );
			if( !empty( $timestamp ) && class_exists( 'DateTime' ) ) {
				$invoice_date = new DateTime();
				$invoice_date->setTimestamp( $timestamp );
				if( !empty( $invoice_date ) )
					$order->pdf_invoice_date = $invoice_date->format( $date_format );
			}
			unset( $timestamp, $invoice_date );
		}
		unset( $invoice_exists_legacy, $invoice_exists, $invoice_date );
	}

	// WooCommerce Germanized - http://www.wpovernight.com
	if( woo_ce_detect_export_plugin( 'wc_germanized_pro' ) ) {
		// Check if the PDF Invoice has been generated
		$invoice_exists = get_post_meta( $order_id, '_invoices', true );
		if( !empty( $invoice_exists ) ) {
			// Multiple invoices can be linked to an Order
			foreach( $invoice_exists as $invoice_id ) {
				if( !empty( $invoice_id ) ) {

					// Check for discarded invoices
					$discard_invoice = get_post_meta( $invoice_id, '_invoice_exclude', true );
					if( $discard_invoice )
						continue;

					$order->invoice_number = get_post_meta( $invoice_id, '_invoice_number', true );
					$order->invoice_number_formatted = get_post_meta( $invoice_id, '_invoice_number_formatted', true );
					$order->invoice_status = woo_ce_get_order_invoice_status( $invoice_id );

				}
			}
		}
		unset( $invoice_exists, $invoice_id, $discard_invoice );
	}

	// WooCommerce Hear About Us - https://wordpress.org/plugins/woocommerce-hear-about-us/
	if( woo_ce_detect_export_plugin( 'hear_about_us' ) ) {
		$source = get_post_meta( $order_id, 'source', true );
		if( $source == '' )
			$source = __( 'N/A', 'woocommerce-exporter' );
		$order->hear_about_us = $source;
		unset( $source );
	}

	// Order Delivery Date for WooCommerce - https://wordpress.org/plugins/order-delivery-date-for-woocommerce/
	// Order Delivery Date Pro for WooCommerce - https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/
	if( woo_ce_detect_export_plugin( 'orddd_free' ) || woo_ce_detect_export_plugin( 'orddd' ) ) {
		$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );
		if( woo_ce_detect_export_plugin( 'orddd' ) )
			$timestamp = get_post_meta( $order_id, '_orddd_timestamp', true );
		else
			$timestamp = get_post_meta( $order_id, '_orddd_lite_timestamp', true );
		if( !empty( $timestamp ) && class_exists( 'DateTime' ) ) {
			$delivery_date = new DateTime();
			$delivery_date->setTimestamp( $timestamp );
			if( !empty( $delivery_date ) )
				$order->delivery_date = $delivery_date->format( $date_format );
		}
		unset( $timestamp, $delivery_date );
	}

	// WooCommerce Memberships - http://www.woothemes.com/products/woocommerce-memberships/
	if( woo_ce_detect_export_plugin( 'wc_memberships' ) ) {
		// Check if a Customer has been assigned to this Order
		if( !empty( $order->user_id ) ) {
			$user_memberships = woo_ce_get_user_assoc_user_memberships( $order->user_id );
			if( !empty( $user_memberships ) ) {
				$user_membership_plans = array();
				foreach( $user_memberships as $user_membership ) {

					// The Post Parent is the Post ID of the Membership Plan
					if( isset( $user_membership->post_parent ) )
						$user_membership_plans[] = get_the_title( $user_membership->post_parent );

				}
				$order->active_memberships = implode( $export->category_separator, $user_membership_plans );
			}
			unset( $user_memberships, $user_membership, $user_membership_plans );
		}
	}

	// WooCommerce Uploads - https://wpfortune.com/shop/plugins/woocommerce-uploads/
	if( woo_ce_detect_export_plugin( 'wc_uploads' ) ) {
		$uploaded_files = get_post_meta( $order_id, '_wpf_umf_uploads', true );
		if( !empty( $uploaded_files ) ) {
			$order->uploaded_files = '';
			$order->uploaded_files_thumbnail = '';
			foreach( $uploaded_files as $uploaded_files_product_id ) {
				if( !empty( $uploaded_files_product_id ) ) {
					foreach( $uploaded_files_product_id as $uploaded_files_product_item_number ) {
						if( !empty( $uploaded_files_product_item_number ) ) {
							foreach( $uploaded_files_product_item_number as $uploaded_files_upload_type ) {
								if( !empty( $uploaded_files_upload_type ) ) {
									foreach( $uploaded_files_upload_type as $uploaded_files_file_number ) {
										if( !empty( $uploaded_files_file_number ) ) {

											// Check we have a path to work with
											if( !empty( $uploaded_files_file_number['path'] ) ) {
												// Check the path exists
												if( file_exists( $uploaded_files_file_number['path'] ) ) {
													// Convert the file path into a URL
													$uploaded_files_file_number['path'] = str_replace( ABSPATH, '', $uploaded_files_file_number['path'] );
													$uploaded_files_file_number['path'] = home_url( $uploaded_files_file_number['path'] );
													$order->uploaded_files .= $uploaded_files_file_number['path'] . "\n";
												}
											}

											// Check we have a thumbnail to work with
											if( !empty( $uploaded_files_file_number['thumb'] ) ) {
												// Check the path exists
												if( file_exists( $uploaded_files_file_number['thumb'] ) ) {
													// Convert the file path into a URL
													$uploaded_files_file_number['thumb'] = str_replace( ABSPATH, '', $uploaded_files_file_number['thumb'] );
													$uploaded_files_file_number['thumb'] = home_url( $uploaded_files_file_number['thumb'] );
													$order->uploaded_files_thumbnail .= $uploaded_files_file_number['thumb'] . "\n";
												}
											}

										}
									}
								}
							}
						}
					}
				}
			}
			unset( $uploaded_files_product_id, $uploaded_files_product_item_number, $uploaded_files_upload_type, $uploaded_files_file_number );
		}
		unset( $uploaded_files );
	}

	// WPML - https://wpml.org/
	// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
	if( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
		$post_type = 'shop_order';
		$language = get_post_meta( $order_id, 'wpml_language', true );
		$language_wpml = woo_ce_wpml_get_language_name( apply_filters( 'wpml_element_language_code', null, array( 'element_id' => $order_id, 'element_type' => $post_type ) ) );
		// The Post meta is the most reliable response
		if( !empty( $language_wpml ) )
			$language = $language_wpml;
		else
			$language = woo_ce_wpml_get_language_name( $language );
		$order->language = $language;
		unset( $language, $language_wpml );
	}

	// WooCommerce EAN Payment Gateway - http://plugins.yanco.dk/woocommerce-ean-payment-gateway
	if( woo_ce_detect_export_plugin( 'wc_ean' ) ) {
		$order->ean_number = get_post_meta( $order_id, 'EAN-number', true );
	}

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/
	if( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		$order->cost_of_goods = woo_ce_format_price( get_post_meta( $order_id, '_wc_cog_order_total_cost', true ), $order->order_currency );
	}

	// WooCommerce Ship to Multiple Addresses - http://woothemes.com/woocommerce
	if( woo_ce_detect_export_plugin( 'wc_ship_multiple' ) ) {
		$shipping_packages = get_post_meta( $order_id, '_wcms_packages', true );
		if( !empty( $shipping_packages ) )
			$order->wcms_number_packages = count( $shipping_packages );
		unset( $shipping_packages );
	}

	// Tickera - https://tickera.com/
	if( woo_ce_detect_export_plugin( 'tickera' ) ) {
		$tickets = woo_ce_get_order_assoc_ticket_ids( $order_id );
		if( !empty( $tickets ) ) {
			$order->ticket_id = '';
			$order->ticket_type_id = '';
			$order->ticket_event_id = '';
			$order->ticket_code = '';
			$order->ticket_first_name = '';
			$order->ticket_last_name = '';
			$tickera_fields = woo_ce_get_tickera_custom_fields();
			if( !empty( $tickera_fields ) ) {
				foreach( $tickera_fields as $tickera_field )
					$order->{sprintf( 'ticket_custom_%s', sanitize_key( $tickera_field['name'] ) )} = '';
			}
			foreach( $tickets as $ticket ) {
				$order->ticket_id .= $ticket . "\n";
				$ticket_type_id = get_post_meta( $ticket, 'ticket_type_id', true );
				$order->ticket_type_id .= $ticket_type_id . "\n";
				$ticket_event_id = get_post_meta( $ticket, 'event_id', true );
				$order->ticket_event_id .= $ticket_event_id . "\n";
				$ticket_code = get_post_meta( $ticket, 'ticket_code', true );
				$order->ticket_code .= $ticket_code . "\n";
				$ticket_first_name = get_post_meta( $ticket, 'first_name', true );
				$order->ticket_first_name .= $ticket_first_name . "\n";
				$ticket_last_name = get_post_meta( $ticket, 'last_name', true );
				$order->ticket_last_name .= $ticket_last_name . "\n";
				if( !empty( $tickera_fields ) ) {
					foreach( $tickera_fields as $tickera_field )
						$order->{sprintf( 'ticket_custom_%s', sanitize_key( $tickera_field['name'] ) )} = get_post_meta( $ticket, $tickera_field['name'], true ) . "\n";
				}
			}
		}
	}

	// WooCommerce Stripe Payment Gateway - https://wordpress.org/plugins/woocommerce-gateway-stripe/
	if( woo_ce_detect_export_plugin( 'wc_stripe' ) ) {
		$order->stripe_customer_id = get_post_meta( $order_id, '_stripe_customer_id', true );
		$order->stripe_card_id = get_post_meta( $order_id, '_stripe_card_id', true );
		$order->stripe_charge_captured = get_post_meta( $order_id, '_stripe_charge_captured', true );
		$order->stripe_payment_id = get_post_meta( $order_id, 'Stripe Payment ID', true );
		$order->stripe_fee = get_post_meta( $order_id, 'Stripe Fee', true );
		$order->stripe_net_revenue = get_post_meta( $order_id, 'Net Revenue From Stripe', true );
	}

	// YITH WooCommerce PDF Invoice and Shipping List
	if( woo_ce_detect_export_plugin( 'yith_ywpi' ) ) {
		$invoice_exists = get_post_meta( $order_id, '_ywpi_invoiced', true );
		if( $invoice_exists ) {
			$order->has_invoice = woo_ce_format_switch( $invoice_exists );
			$order->invoice_number = get_post_meta( $order_id, '_ywpi_invoice_number', true );
			// Check the class and method for YITH WooCommerce PDF Invoice and Shipping List exists
			if( class_exists( 'YITH_Invoice' ) ) {
				$invoice = new YITH_Invoice( $order_id );
				if( method_exists( $invoice, 'get_formatted_invoice_number' ) )
					$order->invoice_number_formatted = $invoice->get_formatted_invoice_number();
				if( method_exists( $invoice, 'get_formatted_date' ) )
					$order->invoice_date = $invoice->get_formatted_date();
			}
		}
		unset( $invoice, $invoice_exists );
	}

	// Custom Order Numbers for WooCommerce
	if( woo_ce_detect_export_plugin( 'alg_con' ) ) {
		$order_number = get_post_meta( $order_id, '_alg_wc_custom_order_number', true );
		if( !empty( $order_number ) )
			$order->purchase_id = $order_number;
		unset( $order_number );
	}

	// WooCommerce Deposits - https://woocommerce.com/products/woocommerce-deposits/
	if( woo_ce_detect_export_plugin( 'wc_deposits' ) ) {
		$order->has_deposit = woo_ce_format_switch( get_post_meta( $order_id, '_wc_deposits_order_has_deposit', true ) );
		$order->deposit_paid = woo_ce_format_switch( get_post_meta( $order_id, '_wc_deposits_deposit_paid', true ) );
		$order->deposit_second_payment_paid = woo_ce_format_switch( get_post_meta( $order_id, '_wc_deposits_second_payment_paid', true ) );

		$order->deposit_amount = get_post_meta( $order_id, '_wc_deposits_deposit_amount', true );
		// Check if there is a value or not
		if( $order->deposit_amount <> '' )
			$order->deposit_amount = woo_ce_format_price( $order->deposit_amount );			

		$order->deposit_second_payment = get_post_meta( $order_id, '_wc_deposits_second_payment', true );
		// Check if there is a value or not
		if( $order->deposit_second_payment <> '' )
			$order->deposit_second_payment = woo_ce_format_price( $order->deposit_second_payment );

		$order->deposit_original_total = get_post_meta( $order_id, '_wc_deposits_original_total', true );
		// Check if there is a value or not
		if( $order->deposit_original_total <> '' )
			$order->deposit_original_total = woo_ce_format_price( $order->deposit_original_total );
	}

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
	if( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		if( get_option( 'wccf_migrated_to_20' ) ) {
			// Order Fields
			$custom_fields = woo_ce_get_wccf_order_fields();
			if( !empty( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field ) {
					$key = get_post_meta( $custom_field->ID, 'key', true );
					$order->{sprintf( 'wccf_of_%s', sanitize_key( $key ) )} = get_post_meta( $order_id, sprintf( '_wccf_of_%s', sanitize_key( $key ) ), true );
				}
			}
			unset( $custom_fields, $custom_field, $key );
			// Checkout Fields
			$custom_fields = woo_ce_get_wccf_checkout_fields();
			if( !empty( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field ) {
					$key = get_post_meta( $custom_field->ID, 'key', true );
					$order->{sprintf( 'wccf_cf_%s', sanitize_key( $key ) )} = get_post_meta( $order_id, sprintf( '_wccf_cf_%s', sanitize_key( $key ) ), true );
				}
			}
			unset( $custom_fields, $custom_field, $key );
		}
	}

	// WooCommerce EU VAT Number - http://woothemes.com/woocommerce
	if( woo_ce_detect_export_plugin( 'eu_vat' ) ) {
		$vat_id = get_post_meta( $order_id, '_vat_number', true );
		$order->eu_vat = $vat_id;
		$order->eu_vat_b2b = ( !empty( $vat_id ) ? __( 'Yes', 'woocommerce-exporter' ) : __( 'No', 'woocommerce-exporter' ) );
		if( !empty( $vat_id ) ) {
			if( get_post_meta( $order_id, '_vat_number_is_validated', true ) !== 'true' ) {
				$order->eu_vat_validated = __( 'Not possible', 'woocommerce-exporter' );
			} else {
				$order->eu_vat_validated = ( get_post_meta( $order_id, '_vat_number_is_valid', true ) === 'true' ? __( 'Yes', 'woocommerce-exporter' ) : __( 'No', 'woocommerce-exporter' ) );
			}
		}
		unset( $vat_id );
	}

	// WooCommerce EU VAT Assistant - https://wordpress.org/plugins/woocommerce-eu-vat-assistant/
	if( woo_ce_detect_export_plugin( 'aelia_eu_vat' ) ) {
		$vat_id = get_post_meta( $order_id, 'vat_number', true );
		$order->eu_vat = $vat_id;
		$order->eu_vat_b2b = ( !empty( $vat_id ) ? __( 'Yes', 'woocommerce-exporter' ) : __( 'No', 'woocommerce-exporter' ) );
		if( !empty( $vat_id ) ) {
			$order->eu_vat_country = get_post_meta( $order_id, '_vat_country', true );
			$order->eu_vat_validated = get_post_meta( $order_id, '_vat_number_validated', true );
		}
		unset( $vat_id );
	}

	// WooCommerce EU VAT Compliance - https://wordpress.org/plugins/woocommerce-eu-vat-compliance/
	// WooCommerce EU VAT Compliance (Premium) - https://www.simbahosting.co.uk/s3/product/woocommerce-eu-vat-compliance/
	if( woo_ce_detect_export_plugin( 'wc_eu_vat_compliance' ) ) {
		$vat_id = get_post_meta( $order_id, 'VAT Number', true );
		$order->eu_vat = $vat_id;
		$order->eu_vat_b2b = ( !empty( $vat_id ) ? __( 'Yes', 'woocommerce-exporter' ) : __( 'No', 'woocommerce-exporter' ) );
		if( !empty( $vat_id ) ) {
			$order->eu_vat_validated = ( get_post_meta( $order_id, 'VAT number validated', true ) === 'true' ? __( 'Yes', 'woocommerce-exporter' ) : __( 'No', 'woocommerce-exporter' ) );
			$order->eu_vat_valid_id = ( get_post_meta( $order_id, 'Valid EU VAT Number', true ) === 'true' ? __( 'Yes', 'woocommerce-exporter' ) : __( 'No', 'woocommerce-exporter' ) );
			$country_info = get_post_meta( $order_id, 'vat_compliance_country_info', true );
			$order->eu_vat_country = ( isset( $country_info['data'] ) ? $country_info['data'] : '' );
			$order->eu_vat_country_source = ( isset( $country_info['source'] ) ? $country_info['source'] : '' );
			unset( $country_info );
		}
		unset( $vat_id );
	}

	// AweBooking - https://codecanyon.net/item/awebooking-online-hotel-booking-for-wordpress/12323878
	if( woo_ce_detect_export_plugin( 'awebooking' ) ) {
		$booking_data = get_post_meta( $order_id, 'apb_data_order', true );
		if( !empty( $booking_data ) ) {
			$arrival_date = array();
			$departure_date = array();
			$adults = array();
			$children = array();
			$room_type_id = array();
			$room_type_name = array();
			foreach( $booking_data as $item_book ) {
				$arrival_date[] = $item_book['from'];
				$departure_date[] = $item_book['to'];
				$adults[] = $item_book['room_adult'];
				$children[] = $item_book['room_child'];
				$room_type_id[] = $item_book['order_room_id'];
				$room_type_name[] = ( !empty( $item_book['order_room_id'] ) ? get_the_title( $item_book['order_room_id'] ) : '-' );
			}
			$order->arrival_date = implode( $export->category_separator, $arrival_date );
			$order->departure_date = implode( $export->category_separator, $departure_date );
			$order->adults = implode( $export->category_separator, $adults );
			$order->children = implode( $export->category_separator, $children );
			$order->room_type_id = implode( $export->category_separator, $room_type_id );
			$order->room_type_name = implode( $export->category_separator, $room_type_name );
			unset( $arrival_date, $departure_date, $adults, $children, $room_type_id, $room_type_name );
		}
		unset( $booking_data );
	}

	// WooCommerce Custom Admin Order Fields - http://www.woothemes.com/products/woocommerce-admin-custom-order-fields/
	if( woo_ce_detect_export_plugin( 'admin_custom_order_fields' ) ) {
		$ac_fields = get_option( 'wc_admin_custom_order_fields' );
		if( !empty( $ac_fields ) ) {
			foreach( $ac_fields as $ac_key => $ac_field ) {
				$order->{sprintf( 'wc_acof_%d', $ac_key )} = get_post_meta( $order_id, sprintf( '_wc_acof_%d', $ac_key ), true );
			}
		}
	}

	// YITH WooCommerce Delivery Date Premium - http://yithemes.com/themes/plugins/yith-woocommerce-delivery-date/
	if( woo_ce_detect_export_plugin( 'yith_delivery_pro' ) ) {
		$date_format = get_option( 'date_format' );
		$shipping_date = get_post_meta( $order_id, 'ywcdd_order_shipping_date', true );
		$delivery_date = get_post_meta( $order_id, 'ywcdd_order_delivery_date', true );
		$time_from = get_post_meta( $order_id, 'ywcdd_order_slot_from', true );
		$time_to = get_post_meta( $order_id, 'ywcdd_order_slot_to', true );
		if( !empty( $shipping_date ) )
			$order->shipping_date = ( function_exists( 'ywcdd_get_date_by_format' ) ? ywcdd_get_date_by_format( $shipping_date, $date_format ) : $shipping_date );
		if( !empty( $delivery_date ) )
			$order->delivery_date = ( function_exists( 'ywcdd_get_date_by_format' ) ? ywcdd_get_date_by_format( $delivery_date, $date_format ) : $delivery_date );
		if( !empty( $time_from ) && !empty( $time_to ) )
			$order->delivery_time_slot = sprintf( '%s - %s', $time_from, $time_to );
		unset( $date_format, $shipping_date, $delivery_date, $time_from, $time_to );
	}

	// WooCommerce Point of Sale - https://codecanyon.net/item/woocommerce-point-of-sale-pos/7869665
	if( woo_ce_detect_export_plugin( 'wc_point_of_sales' ) ) {
		$created_via = get_post_meta( $order_id, '_created_via', true );
		if( $created_via == 'checkout' ) {
			$order->order_type = __( 'Website Order', 'woocommerce-exporter' );
		} else {
			$amount_change = get_post_meta( $order_id, 'wc_pos_order_type', true );
			if( $amount_change )
				$order->order_type = __( 'Point of Sale Order', 'woocommerce-exporter' );
			else
				$order->order_type = __( 'Manual Order', 'woocommerce-exporter' );
		}
		unset( $created_via, $amount_change );
		$order->order_register_id = get_post_meta( $order_id, 'wc_pos_id_register', true );
		$order->order_cashier = get_post_meta( $order_id, 'wc_pos_served_by_name', true );
	}

	// WooCommerce PDF Product Vouchers - http://www.woothemes.com/products/pdf-product-vouchers/
	if( woo_ce_detect_export_plugin( 'wc_pdf_product_vouchers' ) ) {
		$order->voucher_redeemed = get_post_meta( $order_id, '_voucher_redeemed', true );
	}

	// WooCommerce Delivery Slots - https://iconicwp.com/products/woocommerce-delivery-slots/
	if( woo_ce_detect_export_plugin( 'wc_deliveryslots' ) ) {
		$order->delivery_date = get_post_meta( $order_id, 'jckwds_date', true );
		$order->delivery_timeslot = get_post_meta( $order_id, 'jckwds_timeslot', true );
	}

	// WooCommerce Extra Checkout Fields for Brazil - https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/
	if( woo_ce_detect_export_plugin( 'wc_extra_checkout_fields_brazil' ) ) {
		$order->billing_cpf = get_post_meta( $order_id, '_billing_cpf', true );
		$order->billing_rg = get_post_meta( $order_id, '_billing_rg', true );
		$order->billing_cnpj = get_post_meta( $order_id, '_billing_cnpj', true );
		$order->billing_ie = get_post_meta( $order_id, '_billing_ie', true );
		$order->billing_birthdate = get_post_meta( $order_id, '_billing_birthdate', true );
		$order->billing_sex = get_post_meta( $order_id, '_billing_sex', true );
		$order->billing_number = get_post_meta( $order_id, '_billing_number', true );
		$order->billing_neighborhood = get_post_meta( $order_id, '_billing_neighborhood', true );
		$order->billing_cellphone = get_post_meta( $order_id, '_billing_cellphone', true );
		$order->shipping_number = get_post_meta( $order_id, '_shipping_number', true );
		$order->shipping_neighborhood = get_post_meta( $order_id, '_shipping_neighborhood', true );
	}

	// YITH WooCommerce Checkout Manager - https://yithemes.com/themes/plugins/yith-woocommerce-checkout-manager/
	if( woo_ce_detect_export_plugin( 'yith_cm' ) ) {
		// YITH WooCommerce Checkout Manager stores its settings in separate Options
		$billing_options = get_option( 'ywccp_fields_billing_options' );
		$shipping_options = get_option( 'ywccp_fields_shipping_options' );
		$additional_options = get_option( 'ywccp_fields_additional_options' );

		// Custom billing fields
		if( !empty( $billing_options ) ) {
			// Only add non-default Checkout fields to export columns list
			$default_keys = ywccp_get_default_fields_key( 'billing' );
			$fields_keys = array_keys( $billing_options );
			$billing_fields = array_diff( $fields_keys, $default_keys );
			if( !empty( $billing_fields ) ) {
				foreach( $billing_fields as $billing_field ) {
					// Check that the custom Billing field exists
					if( isset( $billing_options[$billing_field] ) ) {
						// Skip headings
						if( $billing_options[$billing_field]['type'] == 'heading' )
							continue;
						$order->{sprintf( 'ywccp_%s', sanitize_key( $billing_field ) )} = get_post_meta( $order_id, sprintf( '_%s', $billing_field ), true );
					}
				}
			}
			unset( $fields_keys, $default_keys, $billing_fields, $billing_field );
		}
		unset( $billing_options );

		// Custom shipping fields
		if( !empty( $shipping_options ) ) {
			// Only add non-default Checkout fields to export columns list
			$default_keys = ywccp_get_default_fields_key( 'shipping' );
			$fields_keys = array_keys( $shipping_options );
			$shipping_fields = array_diff( $fields_keys, $default_keys );
			if( !empty( $shipping_fields ) ) {
				foreach( $shipping_fields as $shipping_field ) {
					// Check that the custom shipping field exists
					if( isset( $shipping_options[$shipping_field] ) ) {
						// Skip headings
						if( $shipping_options[$shipping_field]['type'] == 'heading' )
							continue;
						$order->{sprintf( 'ywccp_%s', sanitize_key( $shipping_field ) )} = get_post_meta( $order_id, sprintf( '_%s', $shipping_field ), true );
					}
				}
			}
			unset( $fields_keys, $default_keys, $shipping_fields, $shipping_field );
		}
		unset( $shipping_options );

		// Custom additional fields
		if( !empty( $additional_options ) ) {
			// Only add non-default Checkout fields to export columns list
			$default_keys = ywccp_get_default_fields_key( 'additional' );
			$fields_keys = array_keys( $additional_options );
			$additional_fields = array_diff( $fields_keys, $default_keys );
			if( !empty( $additional_fields ) ) {
				foreach( $additional_fields as $additional_field ) {
					// Check that the custom additional field exists
					if( isset( $additional_options[$additional_field] ) ) {
						// Skip headings
						if( $additional_options[$additional_field]['type'] == 'heading' )
							continue;
						$order->{sprintf( 'ywccp_%s', sanitize_key( $additional_field ) )} = get_post_meta( $order_id, $additional_field, true );
					}
				}
			}
			unset( $fields_keys, $default_keys, $additional_fields, $additional_field );
		}
		unset( $additional_options );
	}

	// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
	if( woo_ce_detect_export_plugin( 'subscriptions' ) ) {
		$order->subscription_renewal = woo_ce_format_switch( metadata_exists( 'post', $order_id, '_subscription_renewal' ) );
		$order->subscription_resubscribe = woo_ce_format_switch( metadata_exists( 'post', $order_id, '_subscription_resubscribe' ) );
		$order->subscription_switch = woo_ce_format_switch( metadata_exists( 'post', $order_id, '_subscription_switch' ) );
		$order_type = __( 'Non-subscription', 'woocommerce-exporter' );
		if( function_exists( 'wcs_order_contains_subscription' ) ) {
			if( wcs_order_contains_subscription( $order_id, 'renewal' ) ) {
				$order_type = __( 'Renewal Order', 'woocommerce-exporter' );
			} elseif( wcs_order_contains_subscription( $order_id, 'resubscribe' ) ) {
				$order_type = __( 'Resubscribe Order', 'woocommerce-exporter' );
			} elseif( wcs_order_contains_subscription( $order_id, 'parent' ) ) {
				$order_type = __( 'Parent Order', 'woocommerce-exporter' );
			}
		}
		$order->order_type = $order_type;
		unset( $order_type );

	}

	// WooCommerce Quick Donation - http://wordpress.org/plugins/woocommerce-quick-donation/
	if( woo_ce_detect_export_plugin( 'wc_quickdonation' ) ) {

		global $wpdb;

		// Check the wc_quick_donation table exists
		if( $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . "wc_quick_donation'" ) ) {
			$project_id_sql = $wpdb->prepare( "SELECT `projectid` FROM `" . $wpdb->prefix . "wc_quick_donation` WHERE `donationid` = %d LIMIT 1", $order_id );
			$order->project_id = absint( $wpdb->get_var( $project_id_sql ) );
			$order->project_name = get_the_title( $order->project_id );
		}
	}

	// WooCommerce Easy Checkout Fields Editor - http://codecanyon.net/item/woocommerce-easy-checkout-field-editor/9799777
	if( woo_ce_detect_export_plugin( 'wc_easycheckout' ) ) {
		$custom_fields = get_option( 'pcfme_additional_settings' );
		if( !empty( $custom_fields ) ) {
			foreach( $custom_fields as $key => $custom_field ) {
				$order->{$key} = get_post_meta( $order_id, $key, true );
			}
		}
	}

	// WooCommerce Events - http://www.woocommerceevents.com/
	if( woo_ce_detect_export_plugin( 'wc_events' ) ) {
		$count = false;
		$tickets_purchased = get_post_meta( $order_id, 'WooCommerceEventsTicketsPurchased', true );
		if( !empty( $tickets_purchased ) ) {
			$tickets_purchased = json_decode( $tickets_purchased );
			if( !empty( $tickets_purchased ) ) {
				foreach( $tickets_purchased as $ticket_product )
					$count += $ticket_product;
			}
		}
		$order->tickets_purchased = $count;
		unset( $tickets_purchased, $count );
	}

	if( WOO_CD_LOGGING )
		woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_order_extend(): ' . ( time() - $export->start_time ) ) );

	return $order;

}
add_filter( 'woo_ce_order', 'woo_ce_order_extend', 10, 2 );

function woo_ce_extend_order_dataset_args( $args, $export_type = '' ) {

	// Check if we're dealing with the Order Export Type
	if( $export_type <> 'order' )
		return $args;

	// Merge in the form data for this dataset
	$defaults = array(
		// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
		// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
		'order_brand' => ( isset( $_POST['order_filter_brand'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['order_filter_brand'] ) ) : false ),
		// Product Vendors - http://www.woothemes.com/products/product-vendors/
		'order_product_vendor' => ( isset( $_POST['order_filter_product_vendor'] ) ? woo_ce_format_product_filters( array_map( 'absint', $_POST['order_filter_product_vendor'] ) ) : false ),
		// YITH WooCommerce Delivery Date Premium - http://yithemes.com/themes/plugins/yith-woocommerce-delivery-date/
		// Order Delivery Date for WooCommerce - https://wordpress.org/plugins/order-delivery-date-for-woocommerce/
		// Order Delivery Date Pro for WooCommerce - https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/
		'order_delivery_dates_filter' => ( isset( $_POST['order_delivery_dates_filter'] ) ? sanitize_text_field( $_POST['order_delivery_dates_filter'] ) : false ),
		'order_delivery_dates_from' => ( isset( $_POST['order_delivery_dates_from'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['order_delivery_dates_from'] ) ) : false ),
		'order_delivery_dates_to' => ( isset( $_POST['order_delivery_dates_to'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['order_delivery_dates_to'] ) ) : false ),
		// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
		'order_booking_dates_filter' => ( isset( $_POST['order_booking_dates_filter'] ) ? sanitize_text_field( $_POST['order_booking_dates_filter'] ) : false ),
		'order_booking_dates_from' => ( isset( $_POST['order_booking_dates_from'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['order_booking_dates_from'] ) ) : false ),
		'order_booking_dates_to' => ( isset( $_POST['order_booking_dates_to'] ) ? woo_ce_format_order_date( sanitize_text_field( $_POST['order_booking_dates_to'] ) ) : false ),
		// WooCommerce PDF Product Vouchers - http://www.woothemes.com/products/pdf-product-vouchers/
		'order_voucher_redeemed' => ( isset( $_POST['order_filter_voucher_redeemed'] ) ? sanitize_text_field( $_POST['order_filter_voucher_redeemed'] ) : false ),
		// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
		'order_order_type' => ( isset( $_POST['order_filter_order_type'] ) ? sanitize_text_field( $_POST['order_filter_order_type'] ) : false ),
	);
	$args = wp_parse_args( $args, $defaults );

	if( $args['order_brand'] <> woo_ce_get_option( 'order_brand' ) )
		woo_ce_update_option( 'order_brand', $args['order_brand'] );
	if( $args['order_product_vendor'] <> woo_ce_get_option( 'order_product_vendor' ) )
		woo_ce_update_option( 'order_product_vendor', $args['order_product_vendor'] );
	if( $args['order_voucher_redeemed'] <> woo_ce_get_option( 'order_voucher_redeemed' ) )
		woo_ce_update_option( 'order_voucher_redeemed', $args['order_voucher_redeemed'] );
	if( $args['order_order_type'] <> woo_ce_get_option( 'order_order_type' ) )
		woo_ce_update_option( 'order_order_type', $args['order_order_type'] );

	$user_count = woo_ce_get_export_type_count( 'user' );
	$list_limit = apply_filters( 'woo_ce_order_filter_customer_list_limit', 100, $user_count );
	if( $user_count < $list_limit )
		$args['order_customer'] = ( isset( $_POST['order_filter_customer'] ) ? array_map( 'absint', $_POST['order_filter_customer'] ) : false );
	else
		$args['order_customer'] = ( isset( $_POST['order_filter_customer'] ) ? sanitize_text_field( $_POST['order_filter_customer'] ) : false );

	$coupon_count = woo_ce_get_export_type_count( 'coupon' );
	$list_limit = apply_filters( 'woo_ce_order_filter_coupon_list_limit', 500, $coupon_count );
	if( $coupon_count < $list_limit )
		$args['order_coupon'] = ( isset( $_POST['order_filter_coupon'] ) ? array_map( 'absint', $_POST['order_filter_coupon'] ) : false );
	else
		$args['order_coupon'] = ( isset( $_POST['order_filter_coupon'] ) ? sanitize_text_field( $_POST['order_filter_coupon'] ) : false );

	// WPML - https://wpml.org/
	// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
	if( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
		if( !empty( $args['order_product'] ) ) {

			global $sitepress;

			$post_ids = $args['order_product'];
			for( $i = 0; $i < count( $args['order_product'] ); $i++ ) {
				$trid = ( method_exists( $sitepress, 'get_element_trid' ) ? $sitepress->get_element_trid( $post_ids[$i] ) : false );
				if( !empty( $trid ) ) {
					$new_post_ids = array();
					$translations = ( method_exists( $sitepress, 'get_element_translations' ) ? $sitepress->get_element_translations( $trid ) : false );
					if( !empty( $translations ) ) {
						// Loop through the translations
						foreach( $translations as $translation ) {
							$new_post_ids[] = $translation->element_id;
						}
					}
					if( !empty( $post_ids ) ) {
						unset( $post_ids[$i] );
						$post_ids = array_merge( $post_ids, $new_post_ids );
					}
					unset( $new_post_ids );
				}
			}
			$args['order_product'] = $post_ids;

		}
	}

	// Custom Order meta
	$custom_orders = woo_ce_get_option( 'custom_orders', '' );
	if( !empty( $custom_orders ) ) {
		$order_meta = array();
		foreach( $custom_orders as $custom_order )
			$order_meta[esc_attr( $custom_order )] = ( isset( $_POST[sprintf( 'order_filter_custom_meta-%s', esc_attr( $custom_order ) )] ) ? $_POST[sprintf( 'order_filter_custom_meta-%s', esc_attr( $custom_order ) )] : false );
		if( !empty( $order_meta ) )
			$args['order_custom_meta'] = $order_meta;
	}

	return $args;

}
add_filter( 'woo_ce_extend_dataset_args', 'woo_ce_extend_order_dataset_args', 10, 2 );

function woo_ce_extend_cron_order_dataset_args( $args, $export_type = '', $is_scheduled = 0 ) {

	if( $export_type <> 'order' )
		return $args;

	$order_filter_order_type = false;
	$order_filter_custom_meta = false;

	if( $is_scheduled ) {
		$scheduled_export = ( $is_scheduled ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );

		// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
		if( woo_ce_detect_export_plugin( 'subscriptions' ) ) {
			$order_filter_order_type = get_post_meta( $scheduled_export, '_filter_order_type', true );
		}
		$custom_orders = woo_ce_get_option( 'custom_orders', '' );
		if( !empty( $custom_orders ) ) {
			$order_filter_custom_meta = array();
			foreach( $custom_orders as $custom_order ) {
				$order_filter_custom_meta[esc_attr( $custom_order )] = get_post_meta( $scheduled_export, sprintf( '_filter_order_custom_meta-%s', esc_attr( $custom_order ) ), true );
			}
		}

	} else {
		if( isset( $_GET['order_type'] ) ) {
			$order_filter_order_type = sanitize_text_field( $_GET['order_type'] );
		}
	}
	$defaults = array(
		'order_order_type' => ( !empty( $order_filter_order_type ) ? $order_filter_order_type : false ),
		'order_custom_meta' => ( !empty( $order_filter_custom_meta ) ? $order_filter_custom_meta : false )
	);
	$args = wp_parse_args( $args, $defaults );

	return $args;

}
add_action( 'woo_ce_extend_cron_dataset_args', 'woo_ce_extend_cron_order_dataset_args', 10, 3 );

function woo_ce_extend_get_orders_by_coupon( $order_items ) {

	// WooCommerce Sequential Order Numbers - http://www.skyverge.com/blog/woocommerce-sequential-order-numbers/
	// Sequential Order Numbers Pro - http://www.woothemes.com/products/sequential-order-numbers-pro/
	if( woo_ce_detect_export_plugin( 'seq' ) || woo_ce_detect_export_plugin( 'seq_pro' ) ) {
		foreach( $order_items as $key => $order_id ) {

			// WooCommerce Sequential Order Numbers - http://www.skyverge.com/blog/woocommerce-sequential-order-numbers/
			if( woo_ce_detect_export_plugin( 'seq' ) ) {
				$order_number = get_post_meta( $order_id, '_order_number', true );
				if( !empty( $order_number ) )
					$order_items[$key] = $order_number;
				unset( $order_number );
			}
			// Sequential Order Numbers Pro - http://www.woothemes.com/products/sequential-order-numbers-pro/
			if( woo_ce_detect_export_plugin( 'seq_pro' ) ) {
				$order_number = get_post_meta( $order_id, '_order_number_formatted', true );
				if( !empty( $order_number ) )
					$order_items[$key] = $order_number;
				unset( $order_number );
			}

		}
	}

	return $order_items;

}
add_filter( 'woo_ce_extend_get_orders_by_coupon', 'woo_ce_extend_get_orders_by_coupon' );

function woo_ce_extend_get_orders_args( $args ) {

	global $export;

	// YITH WooCommerce Delivery Date Premium - http://yithemes.com/themes/plugins/yith-woocommerce-delivery-date/
	// Order Delivery Date for WooCommerce - https://wordpress.org/plugins/order-delivery-date-for-woocommerce/
	// Order Delivery Date Pro for WooCommerce - https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/
	$order_delivery_dates_filter = ( isset( $export->args['order_delivery_dates_filter'] ) ? $export->args['order_delivery_dates_filter'] : false );
	$order_delivery_dates_from = ( isset( $export->args['order_delivery_dates_from'] ) ? $export->args['order_delivery_dates_from'] : false );
	$order_delivery_dates_to = ( isset( $export->args['order_delivery_dates_to'] ) ? $export->args['order_delivery_dates_to'] : false );
	if( woo_ce_detect_export_plugin( 'orddd' ) ) {
		// $meta_key = '_orddd_timestamp';
		$meta_key = 'Delivery Date';
		$date_format = 'j F, Y';
	}
	if( woo_ce_detect_export_plugin( 'orddd_free' ) ) {
		$meta_key = '_orddd_lite_timestamp';
		$date_format = 'U';
	}
	if( woo_ce_detect_export_plugin( 'yith_delivery_pro' ) ) {
		$meta_key = 'ywcdd_order_delivery_date';
		$date_format = 'Y-m-d';
	}
	switch( $order_delivery_dates_filter ) {

		case 'tomorrow':
			$order_delivery_dates_from = woo_ce_get_order_date_filter( 'tomorrow', 'from', $date_format );
			$order_delivery_dates_to = woo_ce_get_order_date_filter( 'tomorrow', 'to', $date_format );
			break;

		case 'today':
			$order_delivery_dates_from = woo_ce_get_order_date_filter( 'today', 'from', $date_format );
			$order_delivery_dates_to = woo_ce_get_order_date_filter( 'today', 'to', $date_format );
			break;

		case 'manual':
			if( 
				woo_ce_detect_export_plugin( 'orddd_free' ) || 
				woo_ce_detect_export_plugin( 'orddd' )
			) {
				$order_delivery_dates_from = date( $date_format, strtotime( $order_delivery_dates_from, current_time( 'timestamp' ) ) );
				$order_delivery_dates_to = date( $date_format, strtotime( $order_delivery_dates_to, current_time( 'timestamp' ) ) );
			} else {
				$order_delivery_dates_from = woo_ce_format_order_date( $order_delivery_dates_from );
				$order_delivery_dates_to = woo_ce_format_order_date( $order_delivery_dates_to );
			}
			break;

		default:
			$order_delivery_dates_from = false;
			$order_delivery_dates_to = false;
			break;

	}
	if( !empty( $order_delivery_dates_from ) && !empty( $order_delivery_dates_to ) ) {
		// @mod - Cannot filter Orders for Order Delivery Date Pro for WooCommerce as it is not timestamp or Ymd-based. Check in 2.4+
		if( !isset( $args['meta_query'] ) )
			$args['meta_query'] = array();
		$args['meta_query'][] = array(
			'key' => $meta_key,
			'value' => $order_delivery_dates_from,
			'compare' => '>='
		);
		$args['meta_query'][] = array(
			'key' => $meta_key,
			'value' => $order_delivery_dates_to,
			'compare' => '<='
		);
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	$order_booking_dates_filter = ( isset( $export->args['order_booking_dates_filter'] ) ? $export->args['order_booking_dates_filter'] : false );
	$order_booking_dates_from = ( isset( $export->args['order_booking_dates_from'] ) ? $export->args['order_booking_dates_from'] : false );
	$order_booking_dates_to = ( isset( $export->args['order_booking_dates_to'] ) ? $export->args['order_booking_dates_to'] : false );
	// Date is stored as 20170301000000 (YmdHis)
	$date_format = 'YmdHis';
	switch( $order_booking_dates_filter ) {

		case 'today':
			$order_booking_dates_from = woo_ce_get_order_date_filter( 'today', 'from', $date_format );
			$order_booking_dates_to = woo_ce_get_order_date_filter( 'today', 'to', $date_format );
			break;

		case 'manual':
			$order_booking_dates_from = date( 'YmdHis', strtotime( $order_booking_dates_from ) );
			$order_booking_dates_to = date( 'YmdHis', strtotime( $order_booking_dates_to ) );
			break;

		default:
			$order_booking_dates_from = false;
			$order_booking_dates_to = false;
			break;

	}
	if( !empty( $order_booking_dates_from ) && !empty( $order_booking_dates_to ) ) {
		if( !isset( $args['meta_query'] ) )
			$args['meta_query'] = array();
		$args['meta_query'][] = array(
			'key' => '_booking_start',
			'value' => $order_booking_dates_from,
			'compare' => '>='
		);
		$args['meta_query'][] = array(
			'key' => '_booking_start',
			'value' => $order_booking_dates_to,
			'compare' => '<='
		);
	}

	// WooCommerce PDF Product Vouchers - http://www.woothemes.com/products/pdf-product-vouchers/
	$order_voucher_redeemed = ( isset( $export->args['order_voucher_redeemed'] ) ? $export->args['order_voucher_redeemed'] : false );
	if( !empty( $order_voucher_redeemed ) ) {
		switch( $order_voucher_redeemed ) {

			case 'redeemed':
				$order_voucher_redeemed = 1;
				break;

			case 'unredeemed':
				$order_voucher_redeemed = 0;
				break;

		}
		if( !isset( $args['meta_query'] ) )
			$args['meta_query'] = array();
		$args['meta_query'][] = array(
			'key' => '_voucher_redeemed',
			'value' => absint( $order_voucher_redeemed ),
			'compare' => '='
		);
	}

	// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
	$order_order_type = ( isset( $export->args['order_order_type'] ) ? $export->args['order_order_type'] : false );
	if( !empty( $order_order_type ) ) {
		if( !isset( $args['meta_query'] ) )
			$args['meta_query'] = array();
		switch( $order_order_type ) {

			case 'original':
			case 'regular':
				$args['meta_query']['relation'] = 'AND';
				$meta_key = '_subscription_renewal';
				$args['meta_query'][] = array(
					'key' => $meta_key,
					'compare' => 'NOT EXISTS'
				);
				$meta_key = '_subscription_switch';
				$args['meta_query'][] = array(
					'key' => $meta_key,
					'compare' => 'NOT EXISTS'
				);
				// Exclude Subscription Parent Orders for the Non-subscription filter
				if( $order_order_type == 'regular' )
					$args['post__not_in'] = ( function_exists( 'wcs_get_subscription_orders' ) ? wcs_get_subscription_orders() : false );
				break;

			case 'parent':
				$args['post__in'] = ( function_exists( 'wcs_get_subscription_orders' ) ? wcs_get_subscription_orders() : false );
				break;

			case 'renewal':
				$meta_key = '_subscription_renewal';
				$args['meta_query'][] = array(
					'key' => $meta_key,
					'compare' => 'EXISTS'
				);
				break;

			case 'resubscribe':
				$meta_key = '_subscription_resubscribe';
				$args['meta_query'][] = array(
					'key' => $meta_key,
					'compare' => 'EXISTS'
				);
				break;

			case 'switch':
				$meta_key = '_subscription_switch';
				$args['meta_query'][] = array(
					'key' => $meta_key,
					'compare' => 'EXISTS'
				);
				break;

		}
	}

	// Custom Order meta
	$order_meta = ( isset( $export->args['order_custom_meta'] ) ? $export->args['order_custom_meta'] : false );
	if( !empty( $order_meta ) ) {
		$custom_orders = woo_ce_get_option( 'custom_orders', '' );
		if( !empty( $custom_orders ) ) {
			if( !isset( $args['meta_query'] ) )
				$args['meta_query'] = array();
			foreach( $custom_orders as $custom_order ) {
				if( isset( $order_meta[esc_attr( $custom_order )] ) && !empty( $order_meta[esc_attr( $custom_order )] ) ) {
					$meta_key = $custom_order;
					$args['meta_query'][] = array(
						'key' => $meta_key,
						'value' => $order_meta[esc_attr( $custom_order )]
					);
				}
			}
		}
	}

	return $args;

}
add_filter( 'woo_ce_get_orders_args', 'woo_ce_extend_get_orders_args' );

// Gravity Forms - http://woothemes.com/woocommerce
function woo_ce_get_gravity_forms_products() {

	global $export;

	if( WOO_CD_LOGGING ) {
		if( isset( $export->start_time ) )
			woo_ce_error_log( sprintf( 'Debug: %s', 'begin woo_ce_get_gravity_forms_products(): ' . ( time() - $export->start_time ) ) );
	}

	// Can we use the existing Transient?
	if ( false === ( $products = get_transient( WOO_CD_PREFIX . '_gravity_forms_products' ) ) ) {

		global $wpdb;

		$meta_key = '_gravity_form_data';
		$post_ids_sql = $wpdb->prepare( "SELECT `post_id`, `meta_value` FROM `$wpdb->postmeta` WHERE `meta_key` = %s GROUP BY `meta_value`", $meta_key );
		$products = $wpdb->get_results( $post_ids_sql );

		// Save as Transient
		set_transient( WOO_CD_PREFIX . '_gravity_forms_products', $products, HOUR_IN_SECONDS );

	}

	if( WOO_CD_LOGGING ) {
		if( isset( $export->start_time ) )
			woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_get_gravity_forms_products(): ' . ( time() - $export->start_time ) ) );
	}

	return $products;

}

// Gravity Forms - http://woothemes.com/woocommerce
function woo_ce_get_gravity_forms_fields() {

	if( apply_filters( 'woo_ce_enable_addon_gravity_forms', true ) == false )
		return;

	global $export;

	if( WOO_CD_LOGGING ) {
		if( isset( $export->start_time ) )
			woo_ce_error_log( sprintf( 'Debug: %s', 'begin woo_ce_get_gravity_forms_fields(): ' . ( time() - $export->start_time ) ) );
	}

	// Can we use the existing Transient?
	if ( false === ( $fields = get_transient( WOO_CD_PREFIX . '_gravity_forms_fields' ) ) ) {

		$fields = array();
		$gf_products = woo_ce_get_gravity_forms_products();
		if( !empty( $gf_products ) ) {
			foreach( $gf_products as $gf_product ) {
				if( $gf_product_data = maybe_unserialize( get_post_meta( $gf_product->post_id, '_gravity_form_data', true ) ) ) {
					// Check the class and method for Gravity Forms exists
					if( class_exists( 'RGFormsModel' ) && method_exists( 'RGFormsModel', 'get_form_meta' ) ) {
						// Check the form exists
						$gf_form_meta = RGFormsModel::get_form_meta( $gf_product_data['id'] );
						if( !empty( $gf_form_meta ) ) {
							// Check that the form has fields assigned to it
							if( !empty( $gf_form_meta['fields'] ) ) {
								foreach( $gf_form_meta['fields'] as $gf_form_field ) {
									// Check for duplicate Gravity Form fields
									$gf_form_field['formTitle'] = $gf_form_meta['title'];
									// Do not include page and section breaks, hidden as exportable fields
									if( !in_array( $gf_form_field['type'], array( 'page', 'section', 'hidden' ) ) )
										$fields[] = $gf_form_field;
								}
							}
						}
						unset( $gf_form_meta );
					}
				}
				unset( $gf_product_data );
			}
			unset( $gf_products, $gf_product );
		}

		// Save as Transient
		set_transient( WOO_CD_PREFIX . '_gravity_forms_fields', $fields, HOUR_IN_SECONDS );

	}

	if( WOO_CD_LOGGING ) {
		if( isset( $export->start_time ) )
			woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_get_gravity_forms_fields(): ' . ( time() - $export->start_time ) ) );
	}

	return $fields;

}

// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
function woo_ce_get_extra_product_option_fields( $order_item = 0 ) {

	global $export;

	if( WOO_CD_LOGGING ) {
		if( isset( $export->start_time ) )
			woo_ce_error_log( sprintf( 'Debug: %s', 'begin woo_ce_get_extra_product_option_fields(): ' . ( time() - $export->start_time ) ) );
	}

	// Can we use the existing Transient?
	if ( false === ( $fields = get_transient( WOO_CD_PREFIX . '_extra_product_option_fields' ) ) || !empty( $order_item ) ) {

		// This process takes 2-3 seconds to run where the are 1000+ Orders, brace yourself. It can be manually refreshed by clicking Refresh counts from the Quick Export screen
		if( apply_filters( 'woo_ce_enable_extra_product_options_scan', true ) ) {

			global $wpdb;

			// Check if we can use the existing data assigned to Order Items
			$meta_key = '_tmcartepo_data';
			$order_item_type = 'line_item';
			$tm_fields_sql = $wpdb->prepare( "SELECT order_itemmeta.`meta_value` FROM `" . $wpdb->prefix . "woocommerce_order_items` as order_items, `" . $wpdb->prefix . "woocommerce_order_itemmeta` as order_itemmeta WHERE order_items.`order_item_id` = order_itemmeta.`order_item_id` AND order_items.`order_item_type` = %s AND order_itemmeta.`meta_key` = %s", $order_item_type, $meta_key );

			// Limit scan to single Order Item if an Order Item ID is provided
			if( !empty( $order_item ) )
				$tm_fields_sql .= sprintf( " AND order_items.`order_item_id` = %d", $order_item );

			// Limit scan of Order Items to Order IDs if provided
			if( !empty( $order_item ) && !empty( $export->order_ids ) ) {
				$order_ids = $export->order_ids;
				// Check if we're looking up a Sequential Order Number
				if( woo_ce_detect_export_plugin( 'seq' ) || woo_ce_detect_export_plugin( 'seq_pro' ) ) {
					if( isset( $export->order_ids_raw ) )
						$order_ids = $export->order_ids_raw;
				}
				// Check if it's an array
				if( is_array( $order_ids ) )
					$order_ids = implode( ',', $order_ids );
				if( !empty( $order_ids ) )
					$tm_fields_sql .= " AND order_items.`order_id` IN (" . $order_ids . ")";
				unset( $order_ids );
			}

			$tm_fields = $wpdb->get_col( $tm_fields_sql );
			$fields = array();
			if( !empty( $tm_fields ) ) {
				foreach( $tm_fields as $tm_field ) {
					$tm_field = maybe_unserialize( $tm_field );
					$size = count( $tm_field );
					for( $i = 0; $i < $size; $i++ ) {
						// Check that the name is set
						if( !empty( $tm_field[$i]['name'] ) ) {
							$tm_field[$i]['name'] = wp_specialchars_decode( $tm_field[$i]['name'], 'ENT_QUOTES' );
							// Check if we haven't already set this
							if( !array_key_exists( sanitize_key( $tm_field[$i]['name'] ), $fields ) )
								$fields[sanitize_key( $tm_field[$i]['name'] )] = $tm_field[$i];
							else
								$fields[sanitize_key( $tm_field[$i]['name'] )]['value'] = array_merge( (array)$fields[sanitize_key( $tm_field[$i]['name'] )]['value'], (array)$tm_field[$i]['value'] );
						} else {
							// Check if we're dealing with Builder fields that have no Label *head smack*
							if( $tm_field[$i]['mode'] == 'builder' ) {
								$tm_field[$i]['name'] = $tm_field[$i]['section'];
								// Append value to existing Builder field if found otherwise create a new field
								if( array_key_exists( sanitize_key( $tm_field[$i]['section'] ), $fields ) ) {
									// Convert value to array indicating multiple values
									if( is_array( $fields[sanitize_key( $tm_field[$i]['section'] )]['value'] ) )
										$fields[sanitize_key( $tm_field[$i]['section'] )]['value'][] = $tm_field[$i]['value'];
									else
										$fields[sanitize_key( $tm_field[$i]['section'] )]['value'] = array( $fields[sanitize_key( $tm_field[$i]['section'] )]['value'], $tm_field[$i]['value'] );
								} else {
									$tm_field[$i]['section_label'] = sprintf( __( 'Element ID: %s (EPO Builder field without Label) ', 'woocommerce-exporter'), $tm_field[$i]['section'] );
									$fields[sanitize_key( $tm_field[$i]['section'] )] = $tm_field[$i];
								}
							}
						}
					}
				}
			} else {
/*
				// Fallback to scanning the individual Global Extra Product Options
				$post_type = 'tm_global_cp';
				$args = array(
					'post_type' => $post_type,
					'fields' => 'ids',
					'posts_per_page' => -1
				);
				$global_ids = new WP_Query( $args );
				if( !empty( $global_ids->posts ) ) {
					foreach( $global_ids->posts as $global_id )
						$meta = get_post_meta( $global_id, 'tm_meta', true );
				}
				unset( $global_ids, $global_id );
*/
			}

		} else {
			$fields = array();
		}

		// Custom Extra Product Options
		$custom_extra_product_options = woo_ce_get_option( 'custom_extra_product_options', '' );
		if( !empty( $custom_extra_product_options ) ) {
			foreach( $custom_extra_product_options as $custom_extra_product_option ) {
				if( !empty( $custom_extra_product_option ) ) {
					$fields[sanitize_key( $custom_extra_product_option )] = array(
						'name' => $custom_extra_product_option,
						'section_label' => $custom_extra_product_option,
						'value' => ''
					);
				}
			}
		}
		unset( $custom_extra_product_options, $custom_extra_product_option );

		// Save as Transient
		if( empty( $order_item ) && empty( $export->order_ids ) ) {
			$expiration = apply_filters( 'woo_ce_get_extra_product_option_fields_expiration', HOUR_IN_SECONDS );
			set_transient( WOO_CD_PREFIX . '_extra_product_option_fields', $fields, $expiration );
		}

	}

	if( WOO_CD_LOGGING ) {
		if( isset( $export->start_time ) )
			woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_get_extra_product_option_fields(): ' . ( time() - $export->start_time ) ) );
	}

	return $fields;

}

// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
function woo_ce_get_extra_product_option_value( $order_item = 0, $tm_field = array() ) {

	global $wpdb;

	$output = '';
	if( isset( $tm_field['name'] ) ) {
		$meta_sql = $wpdb->prepare( "SELECT `meta_value` FROM `" . $wpdb->prefix . "woocommerce_order_itemmeta` WHERE `order_item_id` = %d AND `meta_key` = %s LIMIT 1", $order_item, $tm_field['name'] );
		$meta = $wpdb->get_var( $meta_sql );
		if( !empty( $meta ) ) {
			$output = $meta;
		} else {
			// Check if we are dealing with a single value or multiple
			if( is_array( $tm_field['value'] ) ) {
				$multiple_value_separator = apply_filters( 'woo_ce_get_extra_product_option_multiple_value_separator', "\n" );
				$output = apply_filters( 'woo_ce_get_extra_product_option_multiple_value_formatting', implode( $multiple_value_separator, $tm_field['value'] ), $tm_field, $order_item );
			} else {
				$output = apply_filters( 'woo_ce_get_extra_product_option_value_formatting', $tm_field['value'], $tm_field, $order_item );
			}
		}
	}
	return $output;

}

function woo_ce_get_nm_personalized_product_fields() {

	global $wpdb;

	$groups_sql = "SELECT `productmeta_name` as group_title, `the_meta` as group_meta FROM `" . $wpdb->prefix . "nm_personalized`";
	$groups = $wpdb->get_results( $groups_sql );
	if( !empty( $groups ) ) {
		$fields = array();
		$field_slugs = array();
		foreach( $groups as $group ) {
			$group_meta = json_decode( $group->group_meta );
			if( !empty( $group_meta ) ) {
				foreach( $group_meta as $field ) {
					if( !empty( $field ) ) {
						// Scan for duplicate fields
						if( !in_array( sanitize_key( $field->title ), $field_slugs ) ) {
							$field_slugs[] = sanitize_key( $field->title );
							$fields[] = array(
								'name' => sanitize_key( $field->title ),
								'label' => apply_filters( 'woo_ce_get_nm_personalized_product_fields_label', $field->title, $group->group_title, $field->title )
							);
						}
					}
				}
			}
		}
		unset( $groups, $group, $field );
		return $fields;
	}

}

// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
function woo_ce_get_wccf_product_fields() {

	$post_type = 'wccf_product_field';
	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page' => -1
	);
	$product_fields = new WP_Query( $args );
	if( !empty( $product_fields->posts ) ) {
		return $product_fields->posts;
	}

}

// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
function woo_ce_get_wccf_order_fields() {

	$post_type = 'wccf_order_field';
	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page' => -1
	);
	$order_fields = new WP_Query( $args );
	if( !empty( $order_fields->posts ) ) {
		return $order_fields->posts;
	}

}

// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
function woo_ce_get_wccf_checkout_fields() {

	$post_type = 'wccf_checkout_field';
	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'posts_per_page' => -1
	);
	$checkout_fields = new WP_Query( $args );
	if( !empty( $checkout_fields->posts ) ) {
		return $checkout_fields->posts;
	}

}

// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/
function woo_ce_get_appointment_by_order_item( $order_item_id = 0, $order_id = 0 ) {

	// Run a WP_Query to return the Post ID of the Booking
	$post_type = 'wc_appointment';
	$meta_key = '_appointment_order_item_id';
	$args = array(
		'post_type' => $post_type,
		'post_parent' => $order_id,
		'meta_query' => array(
			array(
				'key' => $meta_key,
				'value' => $order_item_id
			)
		),
		'fields' => 'ids',
		'posts_per_page' => 1
	);
	$booking_ids = new WP_Query( $args );
	if( !empty( $booking_ids->posts ) )
		return $booking_ids->posts[0];
	unset( $booking_ids );

}

// WooCommerce Product Custom Options Lite - https://wordpress.org/plugins/woocommerce-custom-options-lite/
function woo_ce_get_product_custom_options() {

	$custom_options = array(
		'Another Option Label'
	);
	return apply_filters( 'woo_ce_get_product_custom_options', $custom_options );

}

// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
function woo_ce_get_order_assoc_booking_id( $order_id ) {

	// Run a WP_Query to return the Post ID of the Booking
	$post_type = 'wc_booking';
	$args = array(
		'post_type' => $post_type,
		'post_parent' => $order_id,
		'fields' => 'ids',
		'posts_per_page' => 1
	);
	$booking_ids = new WP_Query( $args );
	if( !empty( $booking_ids->posts ) )
		return $booking_ids->posts[0];
	unset( $booking_ids );

}

// Tickera - https://tickera.com/
function woo_ce_get_order_assoc_ticket_ids( $order_id ) {

	// Run a WP_Query to return the Post ID of the Booking
	$post_type = 'tc_tickets_instances';
	$args = array(
		'post_type' => $post_type,
		'post_parent' => $order_id,
		'fields' => 'ids',
		'posts_per_page' => -1
	);
	$ticket_ids = new WP_Query( $args );
	if( !empty( $ticket_ids->posts ) )
		return $ticket_ids->posts;
	unset( $ticket_ids );

}

function woo_ce_get_tickera_custom_fields() {

	global $export;

	if( WOO_CD_LOGGING ) {
		if( isset( $export->start_time ) )
			woo_ce_error_log( sprintf( 'Debug: %s', 'begin woo_ce_get_tickera_custom_fields(): ' . ( time() - $export->start_time ) ) );
	}

	// Can we use the existing Transient?
	if ( false === ( $fields = get_transient( WOO_CD_PREFIX . '_tickera_custom_fields' ) ) ) {

		// Get the forms
		$fields = array();
		$post_type = 'tc_forms';
		$args = array(
			'post_type' => $post_type,
			'fields' => 'ids',
			'posts_per_page' => -1
		);
		$form_ids = new WP_Query( $args );
		if( !empty( $form_ids->posts ) ) {
			foreach( $form_ids->posts as $form_id ) {
				// Get the form fields associated with the forms
				$post_type = 'tc_form_fields';
				$args = array(
					'post_type' => $post_type,
					'post_parent' => $form_id,
					'posts_per_page' => -1
				);
				$form_fields = new WP_Query( $args );
				if( !empty( $form_fields->posts ) ) {
					foreach( $form_fields->posts as $form_field ) {
						$fields[] = array(
							'name' => $form_field->post_name,
							'label' => $form_field->post_title
						);
					}
				}
				unset( $form_fields, $form_field );
			}
		}
		unset( $form_ids, $form_id );

		// Save as Transient
		set_transient( WOO_CD_PREFIX . '_tickera_custom_fields', $fields, HOUR_IN_SECONDS );

	}

	if( WOO_CD_LOGGING ) {
		if( isset( $export->start_time ) )
			woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_get_tickera_custom_fields(): ' . ( time() - $export->start_time ) ) );
	}

	return $fields;

}

function woo_ce_get_order_invoice_status( $invoice_id = 0 ) {

	$output = get_post_status( $invoice_id );
	$statuses = ( function_exists( 'wc_gzdp_get_invoice_statuses' ) ? wc_gzdp_get_invoice_statuses() : array() );
	if( !empty( $statuses ) ) {
		foreach( $statuses as $key => $status ) {
			if( $key == $output ) {
				$output = $statuses[$key];
				break;
			}
		}
	}
	return $output;

}

function woo_ce_extend_get_order_items( $order_items, $order_id = 0 ) {

	if( empty( $order_items ) )
		return $order_items;

	// WooCommerce Product Bundles - http://www.woothemes.com/products/product-bundles/
	if( woo_ce_detect_export_plugin( 'wc_product_bundles' ) && apply_filters( 'woo_ce_overide_order_items_exclude_product_bundle_children', false ) ) {

		// Filter out Product Bundle children from the list of Order Items
		foreach( $order_items as $key => $order_item ) {
			if( !empty( $order_item->bundled_item_id ) )
				unset( $order_items[$key] );
		}

	}
	return $order_items;

}
add_filter( 'woo_ce_get_order_items', 'woo_ce_extend_get_order_items', 10, 2 );

function woo_ce_extend_order_item_custom_meta( $order_item, $meta_key = '', $meta_value = '' ) {

	global $export;

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	if( WOO_CD_LOGGING )
		woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_extend_order_item_custom_meta(): ' . ( time() - $export->start_time ) ) );

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
	if( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
		if( $meta_key == '_tmcartepo_data' ) {
			if( $tm_fields = woo_ce_get_extra_product_option_fields( $order_item->id ) ) {
				foreach( $tm_fields as $tm_field )
					$order_item->{sprintf( 'tm_%s', sanitize_key( $tm_field['name'] ) )} = woo_ce_get_extra_product_option_value( $order_item->id, $tm_field );
			}
			unset( $tm_fields, $tm_field );
		}
	}

	// Gravity Forms - http://woothemes.com/woocommerce
	if( woo_ce_detect_export_plugin( 'gravity_forms' ) ) {
		if( woo_ce_get_gravity_forms_products() ) {
			$meta_type = 'order_item';
			$gravity_forms_history = get_metadata( $meta_type, $order_item->id, '_gravity_forms_history', true );
			// Check that Gravity Forms Order item meta isn't empty
			if( !empty( $gravity_forms_history ) ) {
				if( isset( $gravity_forms_history['_gravity_form_data'] ) ) {
					$order_item->gf_form_id = ( isset( $gravity_forms_history['_gravity_form_data']['id'] ) ? $gravity_forms_history['_gravity_form_data']['id'] : 0 );
					if( $order_item->gf_form_id ) {
						$gravity_form = ( method_exists( 'RGFormsModel', 'get_form' ) ? RGFormsModel::get_form( $gravity_forms_history['_gravity_form_data']['id'] ) : array() );
						$order_item->gf_form_label = ( !empty( $gravity_form ) ? $gravity_form->title : '' );
					}
				}
			}
		}
	}

	// Product Add-ons - http://www.woothemes.com/
	if( woo_ce_detect_export_plugin( 'product_addons' ) ) {
		$product_addons = woo_ce_get_product_addons();
		if( !empty( $product_addons ) ) {
			foreach( $product_addons as $product_addon ) {
				if( strpos( $meta_key, $product_addon->post_name ) !== false ) {
					if( isset( $order_item->product_addons_summary ) ) {
						// Check if there is a meta value or not
						if( $meta_value <> '' )
							$order_item->product_addons_summary .= $export->category_separator . sprintf( '%s: %s', $meta_key, $meta_value );
						else
							$order_item->product_addons_summary .= $export->category_separator . $meta_key;
					} else {
						// Check if there is a meta value or not
						if( $meta_value <> '' )
							$order_item->product_addons_summary = sprintf( '%s: %s', $meta_key, $meta_value );
						else
							$order_item->product_addons_summary = $meta_key;
					}
					// Check if this Product Addon has already been set
					if( isset( $order_item->product_addons[sanitize_key( $product_addon->post_name )] ) ) {
						// Append the new result to the existing value (likely a checkbox, multiple select, etc.)
						$order_item->product_addons[sanitize_key( $product_addon->post_name )] .= $export->category_separator . $meta_value;
						// Append the option price to the new value
						$order_item->product_addons[sanitize_key( $product_addon->post_name )] .= str_replace( $product_addon->post_name, '', $meta_key );
					} else {
						// Otherwise make a new one
						$order_item->product_addons[sanitize_key( $product_addon->post_name )] = $meta_value;
						// Append the option price to the value
						$order_item->product_addons[sanitize_key( $product_addon->post_name )] .= str_replace( $product_addon->post_name, '', $meta_key );
					}
				}
			}
		}
		unset( $product_addons, $product_addon );
	}

	// WooCommerce Checkout Add-Ons - http://www.skyverge.com/product/woocommerce-checkout-add-ons/
	if( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
		$meta_type = 'fee';
		if( in_array( $meta_key, array( '_wc_checkout_add_on_label', '_wc_checkout_add_on_value' ) ) )
			$meta_value = maybe_unserialize( $meta_value );
		if( $meta_key == '_wc_checkout_add_on_id' )
			$order_item->checkout_addon_id = absint( $meta_value );
		if( $meta_key == '_wc_checkout_add_on_label' )
			$order_item->checkout_addon_label = ( is_array( $meta_value ) ? implode( $export->category_separator, $meta_value ) : $meta_value );
		if( $meta_key == '_wc_checkout_add_on_value' ) {
			$order_item->checkout_addon_value = ( is_array( $meta_value ) ? implode( $export->category_separator, $meta_value ) : $meta_value );
		}
	}

	// Local Pickup Plus - http://www.woothemes.com/products/local-pickup-plus/
	if( woo_ce_detect_export_plugin( 'local_pickup_plus' ) ) {
		$meta_type = 'order_item';
		// Adding support for Local Pickup Plus 2.0...
		if( function_exists( 'wc_local_pickup_plus' ) ) {
			$pickup_meta_key = 'Pickup Location';
			$class = wc_local_pickup_plus();
			if( version_compare( $class::VERSION, '2.0' ) >= 0 )
				$pickup_meta_key = '_pickup_location_name';
			else
			unset( $class );
		} else {
			$pickup_meta_key = 'Pickup Location';
		}
		if( $meta_key == $pickup_meta_key ) {
			$order_item->pickup_location = $meta_value;
		}
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	if( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		$meta_type = 'order_item';
		if( $meta_key == __( 'Booking ID', 'woocommerce-bookings' ) )
			$order_item->booking_id = $meta_value;
		if( $meta_key == __( 'Booking Date', 'woocommerce-bookings' ) )
			$order_item->booking_date = $meta_value;
		if( $meta_key == __( 'Booking Type', 'woocommerce-bookings' ) )
			$order_item->booking_type = $meta_value;
	}

	// WooCommerce Product Bundles - http://www.woothemes.com/products/product-bundles/
	if( woo_ce_detect_export_plugin( 'wc_product_bundles' ) && apply_filters( 'woo_ce_overide_order_items_exclude_product_bundle_children', false ) ) {
		$meta_type = 'order_item';
		if( $meta_key == '_bundled_item_id' )
			$order_item->bundled_item_id = $meta_value;
	}

	// WooCommerce Product Custom Options Lite
	if( woo_ce_detect_export_plugin( 'wc_product_custom_options' ) ) {
		$meta_type = 'order_item';
		$custom_options = woo_ce_get_product_custom_options();
		if( !empty( $custom_options ) ) {
			foreach( $custom_options as $custom_option ) {
				// Do a partial match, not pretty but effective; who stores HTML in the meta key name these days...
				if( strpos( $meta_key, $custom_option ) !== false ) {
					$order_item->{sprintf( 'pco_%s', sanitize_key( $custom_option ) )} = $meta_value;
				}
			}
		}
	}

	// WooCommerce Easy Bookings - https://wordpress.org/plugins/woocommerce-easy-booking-system/
	if( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		$meta_type = 'order_item';
		if( $meta_key == '_ebs_start_display' )
			$order_item->booking_start_date = $meta_value;
		if( $meta_key == '_ebs_end_display' )
			$order_item->booking_end_date = $meta_value;
	}

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	return $order_item;

}
add_filter( 'woo_ce_order_item_custom_meta', 'woo_ce_extend_order_item_custom_meta', 10, 3 );

function woo_ce_extend_order_item( $order_item = array(), $order_id = 0 ) {

	global $export;

	if( WOO_CD_LOGGING )
		woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_extend_order_item(): ' . ( time() - $export->start_time ) ) );

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	// YITH WooCommerce Brands Add-On - http://yithemes.com/themes/plugins/yith-woocommerce-brands-add-on/
	if( woo_ce_detect_product_brands() )
		$order_item->brand = woo_ce_get_product_assoc_brands( $order_item->product_id );

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) )
		$order_item->vendor = woo_ce_get_product_assoc_product_vendors( $order_item->product_id );

	// Cost of Goods - http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/
	if( woo_ce_detect_export_plugin( 'wc_cog' ) ) {
		$meta_type = 'order_item';
		$order_item->cost_of_goods = woo_ce_format_price( get_metadata( $meta_type, $order_item->id, '_wc_cog_item_cost', true ) );
		$order_item->total_cost_of_goods = woo_ce_format_price( get_metadata( $meta_type, $order_item->id, '_wc_cog_item_total_cost', true ) );
	}

	// WooCommerce Profit of Sales Report - http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590
	if( woo_ce_detect_export_plugin( 'wc_posr' ) ) {
		$meta_type = 'order_item';
		$order_item->posr = woo_ce_format_price( get_metadata( $meta_type, $order_item->id, '_posr_line_cog_total', true ) );
	}

	// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
	if( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
		$booking_id = woo_ce_get_order_assoc_booking_id( $order_id );
		if( !empty( $booking_id ) ) {
			$order_item->booking_id = $booking_id;
			// Booking Start Date
			$booking_start_date = get_post_meta( $booking_id, '_booking_start', true );
			if( !empty( $booking_start_date ) )
				$order_item->booking_start_date = woo_ce_format_date( date( 'Y-m-d', strtotime( $booking_start_date ) ) );
			unset( $booking_start_date );
			// Booking End Date
			$booking_end_date = get_post_meta( $booking_id, '_booking_end', true );
			if( !empty( $booking_end_date ) )
				$order_item->booking_end_date = woo_ce_format_date( date( 'Y-m-d', strtotime( $booking_end_date ) ) );
			unset( $booking_end_date );
			// All Day Booking
			$booking_all_day = woo_ce_format_switch( get_post_meta( $booking_id, '_booking_all_day', true ) );
			if( !empty( $booking_all_day ) )
				$order_item->booking_all_day = $booking_all_day;
			unset( $booking_all_day );
			// Booking Resource ID
			$booking_resource_id = get_post_meta( $booking_id, '_booking_resource_id', true );
			if( !empty( $booking_resource_id ) )
				$order_item->booking_resource_id = $booking_resource_id;
			unset( $booking_resource_id );
			// Booking Resource Name
			if( !empty( $order_item->booking_resource_id ) ) {
				$booking_resource_title = get_the_title( $order_item->booking_resource_id );
				if( !empty( $booking_resource_title ) )
					$order_item->booking_resource_title = $booking_resource_title;
				unset( $booking_resource_title );
			}
			// Booking # of Persons
			$booking_persons = get_post_meta( $booking_id, '_booking_persons', true );
			$order_item->booking_persons = ( !empty( $booking_persons ) ? $booking_persons : '-' );
			unset( $booking_persons );
		}
	}

	// WooCommerce MSRP Pricing - http://woothemes.com/woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_msrp' ) ) {
		$order_item->msrp = woo_ce_format_price( get_post_meta( $order_item->product_id, '_msrp_price', true ) );
	}

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
	if( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
		if( $tm_fields = woo_ce_get_extra_product_option_fields( $order_item->id ) ) {
			$meta_type = 'order_item';
			foreach( $tm_fields as $tm_field ) {
				// Check if we have already populated this
				if( isset( $order_item->{sprintf( 'tm_%s', sanitize_key( $tm_field['name'] ) )} ) )
					break;
				$order_item->{sprintf( 'tm_%s', sanitize_key( $tm_field['name'] ) )} = woo_ce_get_extra_product_option_value( $order_item->id, $tm_field );
			}
		}
	}
	unset( $tm_fields, $tm_field );

	// WooCommerce Custom Fields - http://www.rightpress.net/woocommerce-custom-fields
	if( woo_ce_detect_export_plugin( 'wc_customfields' ) ) {
		$meta_type = 'order_item';
		if( !get_option( 'wccf_migrated_to_20' ) ) {
			$options = get_option( 'rp_wccf_options' );
			if( !empty( $options ) ) {
				$options = ( isset( $options[1] ) ? $options[1] : false );
				if( !empty( $options ) ) {
					// Product Fields
					$custom_fields = ( isset( $options['product_fb_config'] ) ? $options['product_fb_config'] : false );
					if( !empty( $custom_fields ) ) {
						foreach( $custom_fields as $custom_field ) {
							$meta_value = get_metadata( $meta_type, $order_item->id, sprintf( 'wccf_%s', sanitize_key( $custom_field['key'] ) ), true );
							if( $meta_value !== false )
								$order_item->{sprintf( 'wccf_%s', sanitize_key( $custom_field['key'] ) )} = $meta_value;
						}
						unset( $custom_fields, $custom_field );
					}
				}
				unset( $options );
			}
		} else {
			// Product Fields
			$custom_fields = woo_ce_get_wccf_product_fields();
			if( !empty( $custom_fields ) ) {
				foreach( $custom_fields as $custom_field ) {
					$key = get_post_meta( $custom_field->ID, 'key', true );
					$meta_value = get_metadata( $meta_type, $order_item->id, sprintf( '_wccf_pf_%s', sanitize_key( $key ) ), true );
					if( $meta_value !== false )
						$order_item->{sprintf( 'wccf_%s', sanitize_key( $key ) )} = $meta_value;
				}
			}
			unset( $custom_fields, $custom_field, $key );
		}
	}

	// Barcodes for WooCommerce - http://www.wolkenkraft.com/produkte/barcodes-fuer-woocommerce/
	if( woo_ce_detect_export_plugin( 'wc_barcodes' ) ) {
		$order_item->order_items_barcode_type = get_post_meta( $order_item->product_id, '_barcode_type', true );
		$order_item->order_items_barcode = get_post_meta( $order_item->product_id, '_barcode', true );
	}

	// WooCommerce Easy Bookings - https://wordpress.org/plugins/woocommerce-easy-booking-system/
	if( woo_ce_detect_export_plugin( 'wc_easybooking' ) ) {
		$meta_type = 'order_item';
		$order_item->booking_start_date = get_metadata( $meta_type, $order_item->id, '_ebs_start_display', true );
		$order_item->booking_end_date = get_metadata( $meta_type, $order_item->id, '_ebs_end_display', true );
	}

	// N-Media WooCommerce Personalized Product Meta Manager
	if( woo_ce_detect_export_plugin( 'wc_nm_personalizedproduct' ) ) {
		$custom_fields = woo_ce_get_nm_personalized_product_fields();
		if( !empty( $custom_fields ) ) {
			foreach( $custom_fields as $custom_field ) {
				$meta_type = 'order_item';
				$meta_value = get_metadata( $meta_type, $order_item->id, $custom_field['label'], true );
				if( $meta_value !== false )
					$order_item->{sprintf( 'nm_%s', $custom_field['name'] )} = $meta_value;
			}
		}
	}

	// WooCommerce Appointments - http://www.bizzthemes.com/plugins/woocommerce-appointments/
	if( woo_ce_detect_export_plugin( 'wc_appointments' ) ) {
		$meta_type = 'order_item';
		$appointment_id = woo_ce_get_appointment_by_order_item( $order_item->id, $order_id );
		if( !empty( $appointment_id ) ) {
			$order_item->appointment_id = $appointment_id;
			// Booking Start Date
			// Booking Start Time
			$booking_start_date = get_post_meta( $appointment_id, '_appointment_start', true );
			if( !empty( $booking_start_date ) ) {
				$order_item->booking_start_date = woo_ce_format_date( date( 'Y-m-d', $booking_start_date ) );
				$order_item->booking_start_time = woo_ce_format_date( $booking_start_date, 'H:i' );
			}
			// Booking End Date
			// Booking End Time
			$booking_end_date = get_post_meta( $appointment_id, '_appointment_end', true );
			if( !empty( $booking_end_date ) ) {
				$order_item->booking_end_date = woo_ce_format_date( date( 'Y-m-d', $booking_end_date ) );
				$order_item->booking_end_time = woo_ce_format_date( $booking_end_date, 'H:i' );
			}
			unset( $booking_end_date );
			// All Day Booking
			$booking_all_day = woo_ce_format_switch( get_post_meta( $appointment_id, '_appointment_all_day', true ) );
			if( !empty( $booking_all_day ) )
				$order_item->booking_all_day = $booking_all_day;
			unset( $booking_all_day );
		}
		unset( $appointment_id );
	}

	// WooCommerce Wholesale Prices - https://wordpress.org/plugins/woocommerce-wholesale-prices/
	if( woo_ce_detect_export_plugin( 'wc_wholesale_prices' ) ) {
		$wholesale_roles = woo_ce_get_wholesale_prices_roles();
		if( !empty( $wholesale_roles ) ) {
			foreach( $wholesale_roles as $key => $wholesale_role ) {
				$order_item->{sprintf( '%s_wholesale_price', $key )} = get_post_meta( $order_item->product_id, sprintf( '%s_wholesale_price', $key ), true );
				// Check that a valid price has been provided
				if( isset( $order_item->{sprintf( '%s_wholesale_price', $key )} ) && $order_item->{sprintf( '%s_wholesale_price', $key )} != '' )
					$order_item->{sprintf( '%s_wholesale_price', $key )} = woo_ce_format_price( $order_item->{sprintf( '%s_wholesale_price', $key )} );
			}
		}
		unset( $wholesale_roles, $wholesale_role, $key );
	}

	// Product Attributes
	if( !empty( $order_item->variation_id ) ) {
		if( apply_filters( 'woo_ce_enable_product_attributes', true ) ) {
			$attributes = woo_ce_get_product_attributes();
			if( !empty( $attributes ) ) {
				$meta_type = 'order_item';
				foreach( $attributes as $attribute ) {
					// Fetch the Taxonomy Attribute value
					$meta_value = get_metadata( $meta_type, $order_item->id, sprintf( 'pa_%s', $attribute->attribute_name ), true );
					if( $meta_value == false ) {
						// Fallback to non-Taxonomy Attribute value
						$meta_value = get_metadata( $meta_type, $order_item->id, $attribute->attribute_name, true );
						if( $meta_value !== false )
							$order_item->{sprintf( 'attribute_%s', $attribute->attribute_name )} = $meta_value;
					} else {
						$term_taxonomy = sprintf( 'pa_%s', $attribute->attribute_name );
						if( taxonomy_exists( $term_taxonomy ) ) {
							$term = get_term_by( 'slug', $meta_value, $term_taxonomy );
							if( $term && !is_wp_error( $term ) )
								$order_item->{sprintf( 'attribute_%s', $attribute->attribute_name )} = $term->name;
						}
					}
				}
			}
			unset( $attributes, $attribute );
		}
	}

	// Custom Order Items fields
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	if( !empty( $custom_order_items ) ) {
		$meta_type = 'order_item';
		foreach( $custom_order_items as $custom_order_item ) {
			if( !empty( $custom_order_item ) ) {
				// Check if this Custom Order Item has already been set
				if( isset( $order_item->{sanitize_key( $custom_order_item )} ) ) {
					// Append the new result to the existing value (likely a checkbox, multiple select, etc.)
					$order_item->{sanitize_key( $custom_order_item )} .= $export->category_separator . implode( $export->category_separator, (array)get_metadata( $meta_type, $order_item->id, $custom_order_item, false ) );
				} else {
					// Otherwise make a new one
					$order_item->{sanitize_key( $custom_order_item )} = woo_ce_format_custom_meta( (array)get_metadata( $meta_type, $order_item->id, $custom_order_item, false ) );
				}
			}
		}
	}
	unset( $custom_order_items, $custom_order_item );

	// Custom Order Item Product fields
	$custom_order_products = woo_ce_get_option( 'custom_order_products', '' );
	if( !empty( $custom_order_products ) ) {
		$meta_type = 'order_item';
		foreach( $custom_order_products as $custom_order_product ) {
			if( !empty( $custom_order_product ) ) {
				// Check if this Custom Order Item Product has already been set
				if( isset( $order_item->{sanitize_key( $custom_order_product )} ) ) {
					// Check if there's a Variation ID available
					if( !empty( $order_item->variation_id ) )
						$order_item->{sanitize_key( $custom_order_product )} .= $export->category_separator . woo_ce_format_custom_meta( get_post_meta( $order_item->variation_id, $custom_order_product, true ) );
					else
						$order_item->{sanitize_key( $custom_order_product )} .= $export->category_separator . woo_ce_format_custom_meta( get_post_meta( $order_item->product_id, $custom_order_product, true ) );
				} else {
					// Check if there's a Variation ID available
					if( !empty( $order_item->variation_id ) )
						$order_item->{sanitize_key( $custom_order_product )} = woo_ce_format_custom_meta( get_post_meta( $order_item->variation_id, $custom_order_product, true ) );
					else
						$order_item->{sanitize_key( $custom_order_product )} = woo_ce_format_custom_meta( get_post_meta( $order_item->product_id, $custom_order_product, true ) );
				}
			}
		}
	}
	unset( $custom_order_products, $custom_order_product );

	// Custom Product fields
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if( !empty( $custom_products ) ) {
		$meta_type = 'order_item';
		foreach( $custom_products as $custom_product ) {
			if( !empty( $custom_product ) ) {
				// Check if there's a Variation ID available
				if( !empty( $order_item->variation_id ) )
					$order_item->{sanitize_key( $custom_product )} = woo_ce_format_custom_meta( get_post_meta( $order_item->variation_id, $custom_product, true ) );
				// If it hasn't been set then check the Product ID
				if( !isset( $order_item->{sanitize_key( $custom_product )} ) || $order_item->{sanitize_key( $custom_product )} == false )
					$order_item->{sanitize_key( $custom_product )} = woo_ce_format_custom_meta( get_post_meta( $order_item->product_id, $custom_product, true ) );
			}
		}
	}
	unset( $custom_products, $custom_product );

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

	return $order_item;

}
add_filter( 'woo_ce_order_item', 'woo_ce_extend_order_item', 10, 2 );

// Add additional shipping methods to the Filter Orders by Shipping Methods list
function woo_ce_extend_get_order_shipping_methods( $output ) {

	// WooCommerce Table Rate Shipping Plus - http://mangohour.com/plugins/woocommerce-table-rate-shipping
	if( woo_ce_detect_export_plugin( 'table_rate_shipping_plus' ) ) {
		$shipping_methods = get_option( 'mh_wc_table_rate_plus_services' );
		if( !empty( $shipping_methods ) ) {
			foreach( $shipping_methods as $shipping_method ) {
				$output[sprintf( 'mh_wc_table_rate_plus_%d', $shipping_method['id'] )] = (object)array(
					'id' => sprintf( 'mh_wc_table_rate_plus_%d', $shipping_method['id'] ),
					'title' => $shipping_method['name'],
					'method_title' => $shipping_method['name']
				);
			}
		}
	}
	// WooCommerce Table Rate Shipping Plus - http://mangohour.com/plugins/woocommerce-table-rate-shipping
	if( isset( $output['mh_wc_table_rate_plus'] ) ) {
		unset( $output['mh_wc_table_rate_plus'] );
	}
	return $output;

}
add_filter( 'woo_ce_get_order_shipping_methods', 'woo_ce_extend_get_order_shipping_methods' );
?>