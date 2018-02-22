<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	function woo_ce_scheduled_export_filters_order( $post_ID = 0 ) {

		ob_start(); ?>
<div class="export-options order-options">

	<?php do_action( 'woo_ce_scheduled_export_filters_order', $post_ID ); ?>

</div>
<!-- .order-options -->

<?php
		ob_end_flush();

	}

	// Scheduled Export filters

	function woo_ce_scheduled_export_order_filter_by_product( $post_ID = 0 ) {

		$start_time = time();
		$debugging = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_products(): ' . ( time() - $start_time ) ) );
		$products = false;
		if( apply_filters( 'woo_ce_override_orders_filter_by_product', true ) ) {
			$post_status = array( 'publish', 'pending', 'future', 'private' );
			$args = array(
				'product_status' => $post_status
			);
			$products = woo_ce_get_products( $args );
		}
		$types = get_post_meta( $post_ID, '_filter_order_product', true );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $products: ' . ( time() - $start_time ) ) );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_product"><?php _e( 'Product', 'woocommerce-exporter' ); ?></label>
<?php if( wp_script_is( 'wc-enhanced-select', 'enqueued' ) && apply_filters( 'woo_ce_override_orders_filter_by_product', true ) ) { ?>
<?php
		$output = '';
		$json_ids = array();
		if( !empty( $types ) ) {
			foreach( $types as $product_id ) {
				$product = wc_get_product( $product_id );
				if( is_object( $product ) ) {
					$json_ids[$product_id] = wp_kses_post( $product->get_formatted_name() );
					$output .= '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
				}
			}
		}
?>
	<?php if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) { ?>
	<select 
				data-placeholder="<?php esc_attr_e( 'Search for a Product&hellip;', 'woocommerce' ); ?>" 
				id="order_filter_product" 
				name="order_filter_product[]" 
				multiple="multiple" 
				class="multiselect wc-product-search" 
				style="width:95%;" 
				data-action="woocommerce_json_search_products_and_variations" 
				data-selected="<?php echo esc_attr( json_encode( $json_ids ) ); ?>"
	><?php echo $output; ?></select>
	<?php } else { ?>
	<input 
				type="hidden" 
				id="order_filter_product" 
				name="order_filter_product[]" 
				class="multiselect wc-product-search" 
				data-multiple="true" 
				style="width:95%;" 
				data-placeholder="<?php _e( 'Search for a Product&hellip;', 'woocommerce-exporter' ); ?>" 
				data-action="woocommerce_json_search_products_and_variations" 
				data-action="woocommerce_json_search_products_and_variations" 
				data-selected="<?php echo esc_attr( json_encode( $json_ids ) ); ?>" 
				value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>"
	 />
	<?php } ?>
<?php } else { ?>
<?php
	add_filter( 'the_title', 'woo_ce_get_product_title_sku', 10, 2 );
?>
	<?php if( !empty( $products ) ) { ?>
	<select id="order_filter_product" data-placeholder="<?php _e( 'Choose a Product...', 'woocommerce-exporter' ); ?>" name="order_filter_product[]" multiple class="chzn-select" style="width:95%;">
		<?php foreach( $products as $product ) { ?>
		<option value="<?php echo $product; ?>"<?php selected( ( !empty( $types ) ? in_array( $product, $types ) : false ), true ); ?>><?php echo woo_ce_format_post_title( get_the_title( $product ) ); ?></option>
		<?php } ?>
	</select>
	<?php } else { ?>
	<?php _e( 'No Products were found.', 'woocommerce-exporter' ); ?>
	<?php } ?>
<?php
	remove_filter( 'the_title', 'woo_ce_get_product_title_sku' );
?>
<?php } ?>
	<img class="help_tip" data-tip="<?php _e( 'Select the Products you want to filter exported Orders by. Default is to include all Products.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_filter_by_product_category( $post_ID = 0 ) {

		$start_time = time();
		$debugging = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_product_categories(): ' . ( time() - $start_time ) ) );

		$args = array(
			'hide_empty' => 1
		);
		$product_categories = woo_ce_get_product_categories( $args );
		$types = get_post_meta( $post_ID, '_filter_order_category', true );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $product_categories: ' . ( time() - $start_time ) ) );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_category"><?php _e( 'Product category', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $product_categories ) ) { ?>
	<select id="order_filter_category" data-placeholder="<?php _e( 'Choose a Product Category...', 'woocommerce-exporter' ); ?>" name="order_filter_category[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_categories as $product_category ) { ?>
		<option value="<?php echo $product_category->term_id; ?>"<?php selected( ( !empty( $types ) ? in_array( $product_category->term_id, $types ) : false ), true ); ?><?php disabled( $product_category->count, 0 ); ?>><?php echo woo_ce_format_product_category_label( $product_category->name, $product_category->parent_name ); ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_category->term_id ); ?>)</option>
	<?php } ?>
	</select>
	<img class="help_tip" data-tip="<?php _e( 'Select the Product Categories you want to filter exported Products by. Default is to include all Products.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
	<?php _e( 'No Product Categories were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_filter_by_product_tag( $post_ID = 0 ) {

		$start_time = time();
		$debugging = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_product_tags(): ' . ( time() - $start_time ) ) );

		$args = array(
			'hide_empty' => 1
		);
		$product_tags = woo_ce_get_product_tags( $args );
		$types = get_post_meta( $post_ID, '_filter_order_tag', true );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $product_tags: ' . ( time() - $start_time ) ) );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_tag"><?php _e( 'Product tag', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $product_tags ) ) { ?>
	<select data-placeholder="<?php _e( 'Choose a Product Tag...', 'woocommerce-exporter' ); ?>" name="order_filter_tag[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_tags as $product_tag ) { ?>
		<option value="<?php echo $product_tag->term_id; ?>"<?php selected( ( !empty( $types ) ? in_array( $product_tag->term_id, $types ) : false ), true ); ?>><?php echo $product_tag->name; ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_tag->term_id ); ?>)</option>
	<?php } ?>
	</select>
<?php } else { ?>
	<?php _e( 'No Product Tags were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_filter_by_customer( $post_ID = 0 ) {

		$start_time = time();
		$debugging = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_customers(): ' . ( time() - $start_time ) ) );

		$user_count = woo_ce_get_export_type_count( 'user' );
		$user_list_limit = apply_filters( 'woo_ce_orders_filter_customer_list_limit', 100, $user_count );
		if( $user_count < $user_list_limit )
			$customers = woo_ce_get_customers_list();
		$types = get_post_meta( $post_ID, '_filter_order_customer', true );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $customers: ' . ( time() - $start_time ) ) );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_customer"><?php _e( 'Customer', 'woocommerce-exporter' ); ?></label>
<?php if( $user_count < $user_list_limit ) { ?>
	<select id="order_filter_customer" data-placeholder="<?php _e( 'Choose a Customer...', 'woocommerce-exporter' ); ?>" name="order_filter_customer[]" multiple class="chzn-select" style="width:95%;">
		<option value=""><?php _e( 'Show all customers', 'woocommerce-exporter' ); ?></option>
	<?php if( !empty( $customers ) ) { ?>
		<?php foreach( $customers as $customer ) { ?>
		<option value="<?php echo $customer->ID; ?>"<?php selected( ( !empty( $types ) ? in_array( $customer->ID, $types ) : false ), true ); ?>><?php printf( '%s (#%s - %s)', $customer->display_name, $customer->ID, $customer->user_email ); ?></option>
		<?php } ?>
	<?php } ?>
	</select>
<?php } else { ?>
	<input type="text" id="order_customer" name="order_filter_customer" value="<?php echo ( !empty( $types ) ? implode( ',', $types ) : '' ); ?>" size="20" class="text" />
<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_filter_by_order_status( $post_ID = 0 ) {

		$start_time = time();
		$debugging = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_order_statuses(): ' . ( time() - $start_time ) ) );

		$order_statuses = woo_ce_get_order_statuses();
		$types = get_post_meta( $post_ID, '_filter_order_status', true );
		if( empty( $types ) )
			$types = array();

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $order_statuses: ' . ( time() - $start_time ) ) );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_status"><?php _e( 'Order status', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $order_statuses ) ) { ?>
	<select id="order_filter_status" data-placeholder="<?php _e( 'Choose a Order Status...', 'woocommerce-exporter' ); ?>" name="order_filter_status[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $order_statuses as $order_status ) { ?>
		<option value="<?php echo $order_status->slug; ?>"<?php selected( ( !empty( $types ) ? in_array( $order_status->slug, $types ) : false ), true ); ?>><?php echo ucfirst( $order_status->name ); ?> (<?php echo $order_status->count; ?>)</option>
	<?php } ?>
	</select>
	<img class="help_tip" data-tip="<?php _e( 'Select the Order Status you want to filter exported Orders by. Default is to include all Order Status options.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
	<?php _e( 'No Order Status were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_filter_by_billing_country( $post_ID = 0 ) {

		$start_time = time();
		$debugging = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before allowed_countries(): ' . ( time() - $start_time ) ) );

		$countries = woo_ce_allowed_countries();
		$types = get_post_meta( $post_ID, '_filter_order_billing_country', true );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $billing_countries: ' . ( time() - $start_time ) ) );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_billing_country"><?php _e( 'Billing country', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $countries ) ) { ?>
	<select id="order_filter_billing_country" data-placeholder="<?php _e( 'Choose a Billing Country...', 'woocommerce-exporter' ); ?>" name="order_filter_billing_country[]" multiple class="chzn-select" style="width:95%;">
		<option value=""><?php _e( 'Show all Countries', 'woocommerce-exporter' ); ?></option>
	<?php foreach( $countries as $country_prefix => $country ) { ?>
		<option value="<?php echo $country_prefix; ?>"<?php selected( ( !empty( $types ) ? in_array( $country_prefix, $types ) : false ), true ); ?>><?php printf( '%s (%s)', $country, $country_prefix ); ?></option>
	<?php } ?>
	</select>
	<img class="help_tip" data-tip="<?php _e( 'Filter Orders by Billing Country to be included in the export. Default is to include all Countries.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
	<?php _e( 'No Countries were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_filter_by_shipping_country( $post_ID = 0 ) {

		$start_time = time();
		$debugging = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before allowed_countries(): ' . ( time() - $start_time ) ) );

		$countries = woo_ce_allowed_countries();
		$types = get_post_meta( $post_ID, '_filter_order_shipping_country', true );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $shipping_countries: ' . ( time() - $start_time ) ) );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_shipping_country"><?php _e( 'Shipping country', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $countries ) ) { ?>
	<select id="order_filter_shipping_country" data-placeholder="<?php _e( 'Choose a Shipping Country...', 'woocommerce-exporter' ); ?>" name="order_filter_shipping_country[]" multiple class="chzn-select" style="width:95%;">
		<option value=""><?php _e( 'Show all Countries', 'woocommerce-exporter' ); ?></option>
	<?php foreach( $countries as $country_prefix => $country ) { ?>
		<option value="<?php echo $country_prefix; ?>"<?php selected( ( !empty( $types ) ? in_array( $country_prefix, $types ) : false ), true ); ?>><?php printf( '%s (%s)', $country, $country_prefix ); ?></option>
	<?php } ?>
	</select>
	<img class="help_tip" data-tip="<?php _e( 'Filter Orders by Shipping Country to be included in the export. Default is to include all Countries.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
	<?php _e( 'No Countries were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_filter_by_order_date( $post_ID = 0 ) {

		$types = get_post_meta( $post_ID, '_filter_order_date', true );
		$order_filter_dates_from = get_post_meta( $post_ID, '_filter_order_dates_from', true );
		$order_filter_dates_to = get_post_meta( $post_ID, '_filter_order_dates_to', true );
		$order_filter_date_variable = get_post_meta( $post_ID, '_filter_order_date_variable', true );
		$order_filter_date_variable_length = get_post_meta( $post_ID, '_filter_order_date_variable_length', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_dates_filter"><?php _e( 'Order date', 'woocommerce-exporter' ); ?></label>
	<input type="radio" name="order_dates_filter" value=""<?php checked( $types, false ); ?> />&nbsp;<?php _e( 'All', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_dates_filter" value="tomorrow"<?php checked( $types, 'tomorrow' ); ?> />&nbsp;<?php _e( 'Tomorrow', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_dates_filter" value="today"<?php checked( $types, 'today' ); ?> />&nbsp;<?php _e( 'Today', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_dates_filter" value="yesterday"<?php checked( $types, 'yesterday' ); ?> />&nbsp;<?php _e( 'Yesterday', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_dates_filter" value="current_week"<?php checked( $types, 'current_week' ); ?> />&nbsp;<?php _e( 'Current week', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_dates_filter" value="last_week"<?php checked( $types, 'last_week' ); ?> />&nbsp;<?php _e( 'Last week', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_dates_filter" value="current_month"<?php checked( $types, 'current_month' ); ?> />&nbsp;<?php _e( 'Current month', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_dates_filter" value="last_month"<?php checked( $types, 'last_month' ); ?> />&nbsp;<?php _e( 'Last month', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_dates_filter" value="variable"<?php checked( $types, 'variable' ); ?> />&nbsp;<?php _e( 'Variable date', 'woocommerce-exporter' ); ?><br />
	<span style="float:left; margin-right:6px;"><?php _e( 'Last', 'woocommerce-exporter' ); ?></span>
	<input type="text" name="order_dates_filter_variable" class="sized" size="4" value="<?php echo $order_filter_date_variable; ?>" />
	<select name="order_dates_filter_variable_length">
		<option value=""<?php selected( $order_filter_date_variable_length, '' ); ?>>&nbsp;</option>
		<option value="second"<?php selected( $order_filter_date_variable_length, 'second' ); ?>><?php _e( 'second(s)', 'woocommerce-exporter' ); ?></option>
		<option value="minute"<?php selected( $order_filter_date_variable_length, 'minute' ); ?>><?php _e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
		<option value="hour"<?php selected( $order_filter_date_variable_length, 'hour' ); ?>><?php _e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
		<option value="day"<?php selected( $order_filter_date_variable_length, 'day' ); ?>><?php _e( 'day(s)', 'woocommerce-exporter' ); ?></option>
		<option value="week"<?php selected( $order_filter_date_variable_length, 'week' ); ?>><?php _e( 'week(s)', 'woocommerce-exporter' ); ?></option>
		<option value="month"<?php selected( $order_filter_date_variable_length, 'month' ); ?>><?php _e( 'month(s)', 'woocommerce-exporter' ); ?></option>
		<option value="year"<?php selected( $order_filter_date_variable_length, 'year' ); ?>><?php _e( 'year(s)', 'woocommerce-exporter' ); ?></option>
	</select><br class="clear" />
	<input type="radio" name="order_dates_filter" value="manual"<?php checked( $types, 'manual' ); ?> />&nbsp;<?php _e( 'Fixed date', 'woocommerce-exporter' ); ?><br />
	<input type="text" name="order_dates_from" value="<?php echo $order_filter_dates_from; ?>" size="10" maxlength="10" class="sized datepicker order_export" /> <span style="float:left; margin-right:6px;"><?php _e( 'to', 'woocommerce-exporter' ); ?></span> <input type="text" name="order_dates_to" value="<?php echo $order_filter_dates_to; ?>" size="10" maxlength="10" class="sized datepicker order_export" /><br class="clear" />
	<input type="radio" name="order_dates_filter" value="last_export"<?php checked( $types, 'last_export' ); ?> />&nbsp;<?php _e( 'Since last export', 'woocommerce-exporter' ); ?>
	<img class="help_tip" data-tip="<?php _e( 'Export Orders which have not previously been included in an export. Decided by whether the <code>_woo_cd_exported</code> custom Post meta key has not been assigned to an Order.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_filter_by_user_role( $post_ID = 0 ) {

		$start_time = time();
		$debugging = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_user_roles(): ' . ( time() - $start_time ) ) );

		$user_roles = woo_ce_get_user_roles();
		// Add Guest Role to the User Roles list
		if( !empty( $user_roles ) ) {
			$user_roles['guest'] = array(
				'name' => __( 'Guest', 'woocommerce-exporter' ),
				'count' => 1
			);
		}
		$types = get_post_meta( $post_ID, '_filter_order_user_role', true );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $user_roles: ' . ( time() - $start_time ) ) );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_user_role"><?php _e( 'User role', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $user_roles ) ) { ?>
	<select id="order_filter_user_role" data-placeholder="<?php _e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="order_filter_user_role[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $user_roles as $key => $user_role ) { ?>
		<option value="<?php echo $key; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo ucfirst( $user_role['name'] ); ?></option>
	<?php } ?>
	</select>
<?php } else { ?>
	<?php _e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_filter_by_coupon( $post_ID = 0 ) {

		$start_time = time();
		$debugging = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_coupons(): ' . ( time() - $start_time ) ) );

		$coupons = false;
		if( apply_filters( 'woo_ce_override_orders_filter_by_coupon', true ) ) {

			$coupon_count = woo_ce_get_export_type_count( 'coupon' );
			$coupon_list_limit = apply_filters( 'woo_ce_order_filter_coupon_list_limit', 500, $coupon_count );
			if( $coupon_count < $coupon_list_limit ) {
				$args = array(
					'coupon_orderby' => 'ID',
					'coupon_order' => 'DESC'
				);
				$coupons = woo_ce_get_coupons( $args );
			}

			$types = get_post_meta( $post_ID, '_filter_order_coupon', true );
		}

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $coupon_codes: ' . ( time() - $start_time ) ) );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_coupon"><?php _e( 'Coupon code', 'woocommerce-exporter' ); ?></label>
<?php if( $coupon_count < $coupon_list_limit ) { ?>
	<?php if( !empty( $coupons ) ) { ?>
	<select id="order_filter_coupon" data-placeholder="<?php _e( 'Choose a Coupon...', 'woocommerce-exporter' ); ?>" name="order_filter_coupon[]" multiple class="chzn-select" style="width:95%;">
		<?php foreach( $coupons as $coupon ) { ?>
		<option value="<?php echo $coupon; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $coupon, $types, false ), true ) : '' ); ?><?php disabled( 0, woo_ce_get_coupon_code_usage( get_the_title( $coupon ) ) ); ?>><?php echo get_the_title( $coupon ); ?> (<?php echo woo_ce_get_coupon_code_usage( get_the_title( $coupon ) ); ?>)</option>
		<?php } ?>
	</select>
	<?php } else { ?>
	<?php _e( 'No Coupons were found.', 'woocommerce-exporter' ); ?>
	<?php } ?>
<?php } else { ?>
	<input type="text" id="order_coupon" name="order_filter_coupon" size="20" class="text" value="<?php echo ( is_array( $types ) ? implode( ',', $types ) : $types ); ?>" />
<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_filter_by_payment_gateway( $post_ID = 0 ) {

		$start_time = time();
		$debugging = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_order_payment_gateways(): ' . ( time() - $start_time ) ) );

		$payment_gateways = woo_ce_get_order_payment_gateways();
		$types = get_post_meta( $post_ID, '_filter_order_payment', true );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $payment_gateways: ' . ( time() - $start_time ) ) );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_payment"><?php _e( 'Payment gateway', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $payment_gateways ) ) { ?>
	<select id="order_filter_payment" data-placeholder="<?php _e( 'Choose a Payment Gateway...', 'woocommerce-exporter' ); ?>" name="order_filter_payment[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $payment_gateways as $payment_gateway ) { ?>
		<option value="<?php echo $payment_gateway->id; ?>"<?php selected( ( !empty( $types ) ? in_array( $payment_gateway->id, $types ) : false ), true ); ?>><?php echo ucfirst( woo_ce_format_order_payment_gateway( $payment_gateway->id ) ); ?></option>
	<?php } ?>
	</select>
	<img class="help_tip" data-tip="<?php _e( 'Select the Payment Gateways you want to filter exported Orders by. Default is to include all Payment Gateways.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
	<?php _e( 'No Payment Gateways were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_filter_by_shipping_method( $post_ID = 0 ) {

		$start_time = time();
		$debugging = apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before get_order_shipping_methods(): ' . ( time() - $start_time ) ) );

		$shipping_methods = woo_ce_get_order_shipping_methods();
		$types = get_post_meta( $post_ID, '_filter_order_shipping', true );

		if( WOO_CD_LOGGING && $debugging )
			woo_ce_error_log( sprintf( 'Debug: %s', 'scheduled_export.php - woo_ce_scheduled_export_filters_order() - before rendering $shipping_methods: ' . ( time() - $start_time ) ) );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_shipping"><?php _e( 'Shipping method', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $shipping_methods ) ) { ?>
	<select id="order_filter_shipping" data-placeholder="<?php _e( 'Choose a Shipping Method...', 'woocommerce-exporter' ); ?>" name="order_filter_shipping[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $shipping_methods as $shipping_method ) { ?>
		<option value="<?php echo $shipping_method->id; ?>"<?php selected( ( !empty( $types ) ? in_array( $shipping_method->id, $types ) : false ), true ); ?>><?php echo ucfirst( woo_ce_format_order_shipping_method( $shipping_method->id ) ); ?></option>
	<?php } ?>
	</select>
	<img class="help_tip" data-tip="<?php _e( 'Select the Shipping Methods you want to filter exported Orders by. Default is to include all Shipping Methods.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
	<?php _e( 'No Shipping Methods were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_items_formatting( $post_ID = 0 ) {

		$types = get_post_meta( $post_ID, '_filter_order_items', true );
		// Default to Quick Export > Order items formatting
		if( empty( $types ) )
			$types = woo_ce_get_option( 'order_items_formatting' );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_dates_filter"><?php _e( 'Order items formatting', 'woocommerce-exporter' ); ?></label>
	<input type="radio" name="order_items_filter" value="combined"<?php checked( $types, 'combined' ); ?> />&nbsp;<?php _e( 'Place Order Items within a grouped single Order row', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_items_filter" value="unique"<?php checked( $types, 'unique' ); ?> />&nbsp;<?php _e( 'Place Order Items on individual cells within a single Order row', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_items_filter" value="individual"<?php checked( $types, 'individual' ); ?> />&nbsp;<?php _e( 'Place each Order Item within their own Order row', 'woocommerce-exporter' ); ?>
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_max_order_items( $post_ID = 0 ) {

		$types = get_post_meta( $post_ID, '_filter_order_max_order_items', true );
		// Default to Quick Export > Max unique Order items
		if( empty( $types ) )
			$types = woo_ce_get_option( 'max_order_items', 10 );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="max_order_items"><?php _e( 'Max unique Order items', 'woocommerce-exporter' ); ?></label>
	<input type="text" id="max_order_items" name="order_max_order_items" size="4" class="sized" value="<?php echo sanitize_text_field( $types ); ?>" style="margin-right:6px;" />
	<img class="help_tip" data-tip="<?php _e( 'Manage the number of Order Item colums displayed when the \'Place Order Items on individual cells within a single Order row\' Order items formatting option is selected.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_export_order_notes( $post_ID = 0 ) {

		$types = absint( get_post_meta( $post_ID, '_filter_order_flag_notes', true ) );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_filter_flag_notes"><?php _e( 'Exported order notes', 'woocommerce-exporter' ); ?></label>
	<input type="radio" name="order_flag_notes" value="0"<?php checked( $types, 0 ); ?>>&nbsp;<?php _e( 'Do not add private Order notes', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_flag_notes" value="1"<?php checked( $types, 1 ); ?>>&nbsp;<?php _e( 'Add private Order notes', 'woocommerce-exporter' ); ?>
	<img class="help_tip" data-tip="<?php _e( 'Choose whether Order notes - e.g. Order was exported successfully or Order export flag was cleared - are assigned to exported Orders when using the Since last export Order Filter. Default is not to add Order notes.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
</p>
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_order_filter_by_digital_products( $post_ID = 0 ) {

		$types = get_post_meta( $post_ID, '_filter_order_items_digital', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="order_dates_filter"><?php _e( 'Digital products', 'woocommerce-exporter' ); ?></label>
	<input type="radio" name="order_items_digital_filter" value=""<?php checked( $types, false ); ?> />&nbsp;<?php _e( 'Export Orders containing both Digital and Physical Products', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_items_digital_filter" value="include_digital"<?php checked( $types, 'include_digital' ); ?> />&nbsp;<?php _e( 'Export Orders containing only Digital Products', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_items_digital_filter" value="exclude_digital"<?php checked( $types, 'exclude_digital' ); ?> />&nbsp;<?php _e( 'Exclude Orders containing any Digital Products', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="order_items_digital_filter" value="exclude_digital_only"<?php checked( $types, 'exclude_digital_only' ); ?> />&nbsp;<?php _e( 'Exclude Orders containing only Digital Products', 'woocommerce-exporter' ); ?><br />
</p>
<?php
		ob_end_flush();

	}

	// HTML template for Order Item Types filter on Edit Scheduled Export screen
	function woo_ce_scheduled_export_order_order_item_types( $post_ID ) {

		$types = woo_ce_get_order_items_types();
		$order_item_types = get_post_meta( $post_ID, '_filter_order_item_types', true );
		// Default to ID
		if( $order_item_types == false ) {
			$order_item_types = array( 'line_item' );
			// Check if WooCommerce Checkout Add-ons is activated
			if( woo_ce_detect_export_plugin( 'checkout_addons' ) )
				$order_item_types = array( 'line_item', 'fee' );
		}

		ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field">
		<label for="order_filter_orderby"><?php _e( 'Order item types', 'woocommerce-exporter' ); ?></label>
<?php foreach( $types as $key => $type ) { ?>
		<input type="checkbox" name="order_filter_order_items_types[<?php echo $key; ?>]" value="<?php echo $key; ?>"<?php checked( in_array( $key, $order_item_types ), true ); ?> />&nbsp;<?php echo ucfirst( $type ); ?><br />
<?php } ?>
	</p>
</div>
<!-- .options_group -->
<?php
		ob_end_flush();

	}

	// Quick Export filters

	// HTML template for Filter Orders by Order Date widget on Store Exporter screen
	function woo_ce_orders_filter_by_date() {

		$tomorrow = date( 'l', strtotime( 'tomorrow', current_time( 'timestamp' ) ) );
		$today = date( 'l', current_time( 'timestamp' ) );
		$yesterday = date( 'l', strtotime( '-1 days', current_time( 'timestamp' ) ) );
		$current_month = date( 'F', current_time( 'timestamp' ) );
		$last_month = date( 'F', mktime( 0, 0, 0, date( 'n', current_time( 'timestamp' ) )-1, 1, date( 'Y', current_time( 'timestamp' ) ) ) );
		$order_dates_variable = woo_ce_get_option( 'order_dates_filter_variable', '' );
		$order_dates_variable_length = woo_ce_get_option( 'order_dates_filter_variable_length', '' );
		$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );
		$order_dates_first_order = woo_ce_get_order_first_date( $date_format );
		$order_dates_last_order = woo_ce_get_order_date_filter( 'today', 'from', $date_format );
		$types = woo_ce_get_option( 'order_dates_filter' );
		$order_dates_from = woo_ce_get_option( 'order_dates_from' );
		$order_dates_to = woo_ce_get_option( 'order_dates_to' );
		// Check if the Order Date To/From have been saved
		if( empty( $order_dates_from ) || empty( $order_dates_to ) ) {
			if( empty( $order_dates_from ) )
				$order_dates_from = $order_dates_first_order;
			if( empty( $order_dates_to ) )
				$order_dates_to = $order_dates_last_order;
		}

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-date"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Order Date', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-date" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_dates_filter" value=""<?php checked( $types, false ); ?> /> <?php _e( 'All dates', 'woocommerce-exporter' ); ?> (<?php echo $order_dates_from; ?> - <?php echo $order_dates_to; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="tomorrow"<?php checked( $types, 'tomorrow' ); ?> /> <?php _e( 'Tomorrow', 'woocommerce-exporter' ); ?> (<?php echo $tomorrow; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="today"<?php checked( $types, 'today' ); ?> /> <?php _e( 'Today', 'woocommerce-exporter' ); ?> (<?php echo $today; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="yesterday"<?php checked( $types, 'yesterday' ); ?> /> <?php _e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo $yesterday; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="current_week"<?php checked( $types, 'current_week' ); ?> /> <?php _e( 'Current week', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="last_week"<?php checked( $types, 'last_week' ); ?> /> <?php _e( 'Last week', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="current_month"<?php checked( $types, 'current_month' ); ?> /> <?php _e( 'Current month', 'woocommerce-exporter' ); ?> (<?php echo $current_month; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="last_month"<?php checked( $types, 'last_month' ); ?> /> <?php _e( 'Last month', 'woocommerce-exporter' ); ?> (<?php echo $last_month; ?>)</label>
		</li>
<!--
		<li>
			<label><input type="radio" name="order_dates_filter" value="last_quarter" /> <?php _e( 'Last quarter', 'woocommerce-exporter' ); ?> (Nov. - Jan.)</label>
		</li>
-->
		<li>
			<label><input type="radio" name="order_dates_filter" value="variable"<?php checked( $types, 'variable' ); ?> /> <?php _e( 'Variable date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<?php _e( 'Last', 'woocommerce-exporter' ); ?>
				<input type="text" name="order_dates_filter_variable" class="text code" size="4" maxlength="4" value="<?php echo $order_dates_variable; ?>" />
				<select name="order_dates_filter_variable_length" style="vertical-align:top;">
					<option value=""<?php selected( $order_dates_variable_length, '' ); ?>>&nbsp;</option>
					<option value="second"<?php selected( $order_dates_variable_length, 'second' ); ?>><?php _e( 'second(s)', 'woocommerce-exporter' ); ?></option>
					<option value="minute"<?php selected( $order_dates_variable_length, 'minute' ); ?>><?php _e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
					<option value="hour"<?php selected( $order_dates_variable_length, 'hour' ); ?>><?php _e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
					<option value="day"<?php selected( $order_dates_variable_length, 'day' ); ?>><?php _e( 'day(s)', 'woocommerce-exporter' ); ?></option>
					<option value="week"<?php selected( $order_dates_variable_length, 'week' ); ?>><?php _e( 'week(s)', 'woocommerce-exporter' ); ?></option>
					<option value="month"<?php selected( $order_dates_variable_length, 'month' ); ?>><?php _e( 'month(s)', 'woocommerce-exporter' ); ?></option>
					<option value="year"<?php selected( $order_dates_variable_length, 'year' ); ?>><?php _e( 'year(s)', 'woocommerce-exporter' ); ?></option>
				</select>
			</div>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="manual"<?php checked( $types, 'manual' ); ?> /> <?php _e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="order_dates_from" name="order_dates_from" value="<?php echo ( $types == 'manual' ? esc_attr( $order_dates_from ) : esc_attr( $order_dates_first_order ) ); ?>" class="text code datepicker order_export" /> <?php _e( 'to', 'woocommerce-exporter' ); ?> <input type="text" size="10" maxlength="10" id="order_dates_to" name="order_dates_to" value="<?php echo ( $types == 'manual' ? esc_attr( $order_dates_to ) : esc_attr( $order_dates_last_order ) ); ?>" class="text code datepicker order_export" />
				<p class="description"><?php _e( 'Filter the dates of Orders to be included in the export. Default is the date of the first Order to today.', 'woocommerce-exporter' ); ?></p>
			</div>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="last_export"<?php checked( $types, 'last_export' ); ?>s /> <?php _e( 'Since last export', 'woocommerce-exporter' ); ?></label>
			<p class="description"><?php _e( 'Export Orders which have not previously been included in an export. Decided by whether the <code>_woo_cd_exported</code> custom Post meta key has not been assigned to an Order.', 'woocommerce-exporter' ); ?></p>
		</li>
	</ul>
</div>
<!-- #export-orders-filters-date -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Customer widget on Store Exporter screen
	function woo_ce_orders_filter_by_customer() {

		if( apply_filters( 'woo_ce_override_orders_filter_by_customer', true ) == false )
			return;

		$user_count = woo_ce_get_export_type_count( 'user' );
		$list_limit = apply_filters( 'woo_ce_order_filter_customer_list_limit', 100, $user_count );
		if( $user_count < $list_limit ) {
			$customers = woo_ce_get_customers_list();
		}

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-customer" /> <?php _e( 'Filter Orders by Customer', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-customer" class="separator">
	<ul>
		<li>
<?php if( $user_count < $list_limit ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Customer...', 'woocommerce-exporter' ); ?>" id="order_customer" name="order_filter_customer[]" multiple class="chzn-select" style="width:95%;">
				<option value=""><?php _e( 'Show all customers', 'woocommerce-exporter' ); ?></option>
	<?php if( !empty( $customers ) ) { ?>
		<?php foreach( $customers as $customer ) { ?>
				<option value="<?php echo $customer->ID; ?>"><?php printf( '%s (#%s - %s)', $customer->display_name, $customer->ID, $customer->user_email ); ?></option>
		<?php } ?>
	<?php } ?>
			</select>
<?php } else { ?>
			<input type="text" id="order_customer" name="order_filter_customer" size="20" class="text" />
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Filter Orders by Customer (unique e-mail address) to be included in the export.', 'woocommerce-exporter' ); ?><?php if( $user_count > $list_limit ) { echo ' ' . __( 'Enter a list of User ID\'s separated by a comma character.', 'woocommerce-exporter' ); } ?> <?php _e( 'Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-customer -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Billing Country widget on Store Exporter screen
	function woo_ce_orders_filter_by_billing_country() {

		$countries = woo_ce_allowed_countries();
		$types = woo_ce_get_option( 'order_billing_country', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-billing_country"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Billing Country', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-billing_country" class="separator">
	<ul>
		<li>
<?php if( !empty( $countries ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Billing Country...', 'woocommerce-exporter' ); ?>" id="order_billing_country" name="order_filter_billing_country[]" multiple class="chzn-select" style="width:95%;">
				<option value=""><?php _e( 'Show all Countries', 'woocommerce-exporter' ); ?></option>
	<?php if( $countries ) { ?>
		<?php foreach( $countries as $country_prefix => $country ) { ?>
				<option value="<?php echo $country_prefix; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $country_prefix, $types, false ), true ) : '' ); ?>><?php printf( '%s (%s)', $country, $country_prefix ); ?></option>
		<?php } ?>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Countries were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Filter Orders by Billing Country to be included in the export. Default is to include all Countries.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-customer -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Shipping Country widget on Store Exporter screen
	function woo_ce_orders_filter_by_shipping_country() {

		$countries = woo_ce_allowed_countries();
		$types = woo_ce_get_option( 'order_shipping_country', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-shipping_country"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Shipping Country', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-shipping_country" class="separator">
	<ul>
		<li>
<?php if( !empty( $countries ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Shipping Country...', 'woocommerce-exporter' ); ?>" id="order_shipping_country" name="order_filter_shipping_country[]" multiple class="chzn-select" style="width:95%;">
				<option value=""><?php _e( 'Show all Countries', 'woocommerce-exporter' ); ?></option>
	<?php foreach( $countries as $country_prefix => $country ) { ?>
				<option value="<?php echo $country_prefix; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $country_prefix, $types, false ), true ) : '' ); ?>><?php printf( '%s (%s)', $country, $country_prefix ); ?></option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Countries were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Filter Orders by Shipping Country to be included in the export. Default is to include all Countries.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-customer -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by User Role widget on Store Exporter screen
	function woo_ce_orders_filter_by_user_role() {

		$user_roles = woo_ce_get_user_roles();
		// Add Guest Role to the User Roles list
		if( !empty( $user_roles ) ) {
			$user_roles['guest'] = array(
				'name' => __( 'Guest', 'woocommerce-exporter' ),
				'count' => 1
			);
		}
		$types = woo_ce_get_option( 'order_user_roles', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-user_role"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by User Role', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-user_role" class="separator">
	<ul>
		<li>
<?php if( !empty( $user_roles ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="order_filter_user_role[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $user_roles as $key => $user_role ) { ?>
				<option value="<?php echo $key; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo ucfirst( $user_role['name'] ); ?></option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the User Roles you want to filter exported Orders by. Default is to include all User Role options.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-user_role -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Order ID widget on Store Exporter screen
	function woo_ce_orders_filter_by_order_id() {

		$types = woo_ce_get_option( 'order_order_ids' );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-id"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Order ID', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-id" class="separator">
	<ul>
		<li>
			<input type="text" id="order_filter_id" name="order_filter_id" placeholder="1000,1001,1002,1000-1002" value="<?php echo ( !empty( $types ) ? $types : '' ); ?>" class="text code" style="width:95%;" />
		</li>
	</ul>
	<p class="description"><?php _e( 'Enter the Order ID\'s you want to filter exported Orders by. Multiple Order ID\'s can be entered separated by the \',\' (comma) character, Order ID ranges can be entered separated by the \'-\' (dash) character. Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-user_role -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Coupon Code widget on Store Exporter screen
	function woo_ce_orders_filter_by_coupon() {

		if( apply_filters( 'woo_ce_override_orders_filter_by_coupon', true ) == false )
			return;

		$coupon_count = woo_ce_get_export_type_count( 'coupon' );
		$list_limit = apply_filters( 'woo_ce_order_filter_coupon_list_limit', 500, $coupon_count );
		if( $coupon_count < $list_limit ) {

			$args = array(
				'coupon_orderby' => 'ID',
				'coupon_order' => 'DESC'
			);

			// Allow other developers to bake in their own filters
			$args = apply_filters( 'woo_ce_orders_filter_by_coupon_args', $args );

			$coupons = woo_ce_get_coupons( $args );

		}
		$types = woo_ce_get_option( 'order_coupon', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-coupon"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Coupon Code', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-coupon" class="separator">
	<ul>
		<li>
<?php if( !empty( $coupons ) ) { ?>
	<?php if( $coupon_count < $list_limit ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Coupon...', 'woocommerce-exporter' ); ?>" name="order_filter_coupon[]" multiple class="chzn-select" style="width:95%;">
		<?php foreach( $coupons as $coupon ) { ?>
				<option value="<?php echo $coupon; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $coupon, $types, false ), true ) : '' ); ?><?php disabled( 0, woo_ce_get_coupon_code_usage( get_the_title( $coupon ) ) ); ?>><?php echo get_the_title( $coupon ); ?> (<?php echo woo_ce_get_coupon_code_usage( get_the_title( $coupon ) ); ?>)</option>
		<?php } ?>
			</select>
	<?php } else { ?>
			<input type="text" id="order_coupon" name="order_filter_coupon" size="20" class="text" />
	<?php } ?>
<?php } else { ?>
			<?php _e( 'No Coupons were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Coupon Codes you want to filter exported Orders by. Default is to include all Orders with and without assigned Coupon Codes.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-coupon -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Payment Gateway widget on Store Exporter screen
	function woo_ce_orders_filter_by_payment_gateway() {

		$payment_gateways = woo_ce_get_order_payment_gateways();
		$types = woo_ce_get_option( 'order_payment_method', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-payment_gateway"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Payment Gateway', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-payment_gateway" class="separator">
	<ul>
		<li>
<?php if( !empty( $payment_gateways ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Payment Gateway...', 'woocommerce-exporter' ); ?>" name="order_filter_payment_gateway[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $payment_gateways as $payment_gateway ) { ?>
				<option value="<?php echo $payment_gateway->id; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $payment_gateway->id, $types, false ), true ) : '' ); ?><?php disabled( 0, woo_ce_get_order_payment_gateway_usage( $payment_gateway->id ) ); ?>><?php echo ucfirst( woo_ce_format_order_payment_gateway( $payment_gateway->id ) ); ?> (<?php echo woo_ce_get_order_payment_gateway_usage( $payment_gateway->id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Payment Gateways were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Payment Gateways you want to filter exported Orders by. Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-payment_gateway -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Shipping Gateway widget on Store Exporter screen
	function woo_ce_orders_filter_by_shipping_method() {

		$shipping_methods = woo_ce_get_order_shipping_methods();
		$types = woo_ce_get_option( 'order_shipping_method', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-shipping_method"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Shipping Method', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-shipping_method" class="separator">
	<ul>
		<li>
<?php if( !empty( $shipping_methods ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Shipping Method...', 'woocommerce-exporter' ); ?>" name="order_filter_shipping_method[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $shipping_methods as $shipping_method ) { ?>
				<option value="<?php echo $shipping_method->id; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $shipping_method->id, $types, false ), true ) : '' ); ?>><?php echo woo_ce_format_order_shipping_method( $shipping_method->id ); ?></option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Shipping Methods were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Shipping Methods you want to filter exported Orders by. Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-shipping_method -->
<?php
		ob_end_flush();

	}

	// HTML template for Digital Products on Store Exporter screen
	function woo_ce_orders_filter_by_digital_products() {

		$order_digital_products = woo_ce_get_option( 'order_digital_products', false );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-digital_products"<?php checked( !empty( $order_digital_products ), true ); ?> /> <?php _e( 'Filter Orders by Digital Products', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-digital_products" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_filter_digital_products" value=""<?php checked( $order_digital_products, false ); ?> /> <?php _e( 'Export Orders containing both Digital and Physical Products', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_digital_products" value="include_digital"<?php checked( $order_digital_products, 'include_digital' ); ?> /> <?php _e( 'Export Orders containing only Digital Products', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_digital_products" value="exclude_digital"<?php checked( $order_digital_products, 'exclude_digital' ); ?> /> <?php _e( 'Exclude Orders containing any Digital Products', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="order_filter_digital_products" value="exclude_digital_only"<?php checked( $order_digital_products, 'exclude_digital_only' ); ?> /> <?php _e( 'Exclude Orders containing only Digital Products', 'woocommerce-exporter' ); ?></label>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Digital Products you want to filter exported Orders by. Default is to include all Orders.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-date -->

<?php
		ob_end_flush();

	}

	// HTML template for Order Items Formatting on Store Exporter screen
	function woo_ce_orders_items_formatting() {

		$order_items_formatting = woo_ce_get_option( 'order_items_formatting', 'unique' );

		ob_start(); ?>
<tr class="export-options order-options">
	<th><label for="order_items"><?php _e( 'Order items formatting', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<ul>
			<li>
				<label><input type="radio" name="order_items" value="combined"<?php checked( $order_items_formatting, 'combined' ); ?> />&nbsp;<?php _e( 'Place Order Items within a grouped single Order row', 'woocommerce-exporter' ); ?></label>
				<p class="description"><?php _e( 'For example: <code>Order Items: SKU</code> cell might contain <code>SPECK-IPHONE|INCASE-NANO|-</code> for 3 Order items within an Order', 'woocommerce-exporter' ); ?></p>
			</li>
			<li>
				<label><input type="radio" name="order_items" value="unique"<?php checked( $order_items_formatting, 'unique' ); ?> />&nbsp;<?php _e( 'Place Order Items on individual cells within a single Order row', 'woocommerce-exporter' ); ?></label>
				<p class="description"><?php _e( 'For example: <code>Order Items: SKU</code> would become <code>Order Item #1: SKU</code> with <codeSPECK-IPHONE</code> for the first Order item within an Order', 'woocommerce-exporter' ); ?></p>
			</li>
			<li>
				<label><input type="radio" name="order_items" value="individual"<?php checked( $order_items_formatting, 'individual' ); ?> />&nbsp;<?php _e( 'Place each Order Item within their own Order row', 'woocommerce-exporter' ); ?></label>
				<p class="description"><?php _e( 'For example: An Order with 3 Order items will display a single Order item on each row', 'woocommerce-exporter' ); ?></p>
			</li>
		</ul>
		<p class="description"><?php _e( 'Choose how you would like Order Items to be presented within Orders.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// HTML template for Max Order Items widget on Store Exporter screen
	function woo_ce_orders_max_order_items() {

		$max_order_items = woo_ce_get_option( 'max_order_items', 10 );
		// Default to 10 if empty
		if( empty( $max_order_items ) )
			$max_order_items = 10;

		ob_start(); ?>
<tr id="max_order_items_option" class="export-options order-options">
	<th>
		<label for="max_order_items"><?php _e( 'Max unique Order items', 'woocommerce-exporter' ); ?>: </label>
	</th>
	<td>
		<input type="text" id="max_order_items" name="max_order_items" size="3" class="text" value="<?php echo esc_attr( $max_order_items ); ?>" />
		<p class="description"><?php _e( 'Manage the number of Order Item colums displayed when the \'Place Order Items on individual cells within a single Order row\' Order items formatting option is selected.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// HTML template for Order Items Types on Store Exporter screen
	function woo_ce_orders_items_types() {

		$types = woo_ce_get_order_items_types();
		$order_items_types = woo_ce_get_option( 'order_items_types', array() );

		// Default to Line Item if not set
		if( empty( $order_items_types ) ) {
			$order_items_types = array( 'line_item' );
			// Check if WooCommerce Checkout Add-ons is activated
			if( woo_ce_detect_export_plugin( 'checkout_addons' ) )
				$order_items_types = array( 'line_item', 'fee' );
		}

		ob_start(); ?>
<tr class="export-options order-options">
	<th><label><?php _e( 'Order item types', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<ul>
<?php foreach( $types as $key => $type ) { ?>
			<li><label><input type="checkbox" name="order_items_types[<?php echo $key; ?>]" value="<?php echo $key; ?>"<?php checked( in_array( $key, $order_items_types ), true ); ?> /> <?php echo ucfirst( $type ); ?></label></li>
<?php } ?>
		</ul>
		<p class="description"><?php _e( 'Choose what Order Item types are included within the Orders export. Default is to include all Order Item types.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// HTML template for Add note for exported Order flag widget on Store Exporter screen
	function woo_ce_orders_flag_notes() {

		$order_flag_notes = woo_ce_get_option( 'order_flag_notes', 0 );

		ob_start(); ?>
<tr class="export-options order-options">
	<th><label><?php _e( 'Exported Order notes', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<label><input type="radio" name="order_flag_notes" value="0"<?php checked( $order_flag_notes, 0 ); ?>>&nbsp;<?php _e( 'Do not add private Order notes', 'woocommerce-exporter' ); ?></label><br />
		<label><input type="radio" name="order_flag_notes" value="1"<?php checked( $order_flag_notes, 1 ); ?>>&nbsp;<?php _e( 'Add private Order notes', 'woocommerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Choose whether Order notes - e.g. Order was exported successfully or Order export flag was cleared - are assigned to exported Orders when using the Since last export Order Filter. Default is not to add Order notes.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Order Status widget on Store Exporter screen
	function woo_ce_orders_filter_by_status() {

		$order_statuses = woo_ce_get_order_statuses();
		$types = woo_ce_get_option( 'order_status', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-status"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Order Status', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-status" class="separator">
	<ul>
		<li>
<?php if( !empty( $order_statuses ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Order Status...', 'woocommerce-exporter' ); ?>" name="order_filter_status[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $order_statuses as $order_status ) { ?>
				<option value="<?php echo $order_status->slug; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $order_status->slug, $types, false ), true ) : '' ); ?><?php disabled( 0, $order_status->count ); ?>><?php echo ucfirst( $order_status->name ); ?> (<?php echo $order_status->count; ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Order Status\'s were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Order Status you want to filter exported Orders by. Default is to include all Order Status options.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-status -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Product widget on Store Exporter screen
	function woo_ce_orders_filter_by_product() {

		if( apply_filters( 'woo_ce_override_orders_filter_by_product', true ) == false )
			return;

/*
		// @mod - Removed as the meta_query args are returning empty results. Marked for re-inclusion after re-work in 2.4+
		$product_types = woo_ce_get_product_types();
		// Remove the Product Variation type
		unset( $product_types['variation'] );
		$args = array(
			'product_type' => array_keys( $product_types )
		);
*/
		$args = array();

		// Allow other developers to bake in their own filters
		$args = apply_filters( 'woo_ce_orders_filter_by_product_args', $args );

		$products = woo_ce_get_products( $args );
		add_filter( 'the_title', 'woo_ce_get_product_title_sku', 10, 2 );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-product" /> <?php _e( 'Filter Orders by Product', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-product" class="separator">
	<ul>
		<li>
<?php if( wp_script_is( 'wc-enhanced-select', 'enqueued' ) ) { ?>
			<p>
	<?php if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) { ?>
				<select 
					data-placeholder="<?php esc_attr_e( 'Search for a Product&hellip;', 'woocommerce' ); ?>" 
					id="order_filter_product" 
					name="order_filter_product[]" 
					multiple="multiple" 
					class="multiselect wc-product-search" 
					style="width:95%;" 
					data-action="woocommerce_json_search_products_and_variations"
				></select>
	<?php } else { ?>
				<input 
					data-placeholder="<?php _e( 'Search for a Product&hellip;', 'woocommerce-exporter' ); ?>" 
					type="hidden" 
					id="order_filter_product" name="order_filter_product[]" 
					class="multiselect wc-product-search" 
					data-multiple="true" 
					style="width:95;" 
					data-action="woocommerce_json_search_products_and_variations"
				 />
	<?php } ?>
			</p>
<?php } else { ?>
	<?php if( !empty( $products ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product...', 'woocommerce-exporter' ); ?>" name="order_filter_product[]" multiple class="chzn-select" style="width:95%;">
		<?php foreach( $products as $product ) { ?>
				<option value="<?php echo $product; ?>"><?php echo woo_ce_format_post_title( get_the_title( $product ) ); ?></option>
		<?php } ?>
			</select>
	<?php } else { ?>
			<?php _e( 'No Products were found.', 'woocommerce-exporter' ); ?>
	<?php } ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Products you want to filter exported Orders by. Default is to include all Products.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-product -->
<?php
		ob_end_flush();
		remove_filter( 'the_title', 'woo_ce_get_product_title_sku' );

	}

	// HTML template for Filter Orders by Product Category widget on Store Exporter screen
	function woo_ce_orders_filter_by_product_category() {

		if( apply_filters( 'woo_ce_override_orders_filter_by_product_category', true ) == false )
			return;

		$args = array(
			'hide_empty' => 1
		);

		// Allow other developers to bake in their own filters
		$args = apply_filters( 'woo_ce_orders_filter_by_product_category_args', $args );

		$product_categories = woo_ce_get_product_categories( $args );
		$types = woo_ce_get_option( 'order_category', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-category"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Product Category', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-category" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_categories ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Category...', 'woocommerce-exporter' ); ?>" name="order_filter_category[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_categories as $product_category ) { ?>
				<option value="<?php echo $product_category->term_id; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $product_category->term_id, $types, false ), true ) : '' ); ?>><?php echo woo_ce_format_product_category_label( $product_category->name, $product_category->parent_name ); ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_category->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Categories were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Categories you want to filter exported Orders by. Product Categories not assigned to Products are hidden from view. Default is to include all Product Categories.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-category -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Orders by Product Tag widget on Store Exporter screen
	function woo_ce_orders_filter_by_product_tag() {

		if( apply_filters( 'woo_ce_override_orders_filter_by_product_tag', true ) == false )
			return;

		$args = array(
			'hide_empty' => 1
		);

		// Allow other developers to bake in their own filters
		$args = apply_filters( 'woo_ce_orders_filter_by_product_tag_args', $args );

		$product_tags = woo_ce_get_product_tags( $args );
		$types = woo_ce_get_option( 'order_tag', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-tag"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Orders by Product Tag', 'woocommerce-exporter' ); ?></label></p>
<div id="export-orders-filters-tag" class="separator">
	<ul>
		<li>
<?php if( !empty( $product_tags ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Product Tag...', 'woocommerce-exporter' ); ?>" name="order_filter_tag[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $product_tags as $product_tag ) { ?>
				<option value="<?php echo $product_tag->term_id; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $product_tag->term_id, $types, false ), true ) : '' ); ?>><?php echo $product_tag->name; ?> (<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_tag->term_id ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Product Tags were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Product Tags you want to filter exported Orders by. Product Tags not assigned to Products are hidden from view. Default is to include all Product Tags.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-tag -->
<?php
		ob_end_flush();

	}

	// HTML template for Order Sorting widget on Store Exporter screen
	function woo_ce_order_sorting() {

		$orderby = woo_ce_get_option( 'order_orderby', 'ID' );
		$order = woo_ce_get_option( 'order_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'Order Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="order_orderby">
		<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'Order ID', 'woocommerce-exporter' ); ?></option>
		<option value="title"<?php selected( 'title', $orderby ); ?>><?php _e( 'Order Name', 'woocommerce-exporter' ); ?></option>
		<option value="date"<?php selected( 'date', $orderby ); ?>><?php _e( 'Date Created', 'woocommerce-exporter' ); ?></option>
		<option value="modified"<?php selected( 'modified', $orderby ); ?>><?php _e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
		<option value="rand"<?php selected( 'rand', $orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="order_order">
		<option value="ASC"<?php selected( 'ASC', $order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Orders within the exported file. By default this is set to export Orders by Product ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	// HTML template for jump link to Custom Order Fields within Order Options on Store Exporter screen
	function woo_ce_orders_custom_fields_link() {

		ob_start(); ?>
<div id="export-orders-custom-fields-link">
	<p><a href="#export-orders-custom-fields"><?php _e( 'Manage Custom Order Fields', 'woocommerce-exporter' ); ?></a></p>
</div>
<!-- #export-orders-custom-fields-link -->
<?php
		ob_end_flush();

	}

	// HTML template for Custom Orders widget on Store Exporter screen
	function woo_ce_orders_custom_fields() {

		if( $custom_orders = woo_ce_get_option( 'custom_orders', '' ) )
			$custom_orders = implode( "\n", $custom_orders );
		if( $custom_order_items = woo_ce_get_option( 'custom_order_items', '' ) )
			$custom_order_items = implode( "\n", $custom_order_items );
		if( $custom_order_products = woo_ce_get_option( 'custom_order_products', '' ) )
			$custom_order_products = implode( "\n", $custom_order_products );

		$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

		ob_start(); ?>
<form method="post" id="export-orders-custom-fields" class="export-options order-options">
	<div id="poststuff">

		<div class="postbox" id="export-options">
			<h3 class="hndle"><?php _e( 'Custom Order Fields', 'woocommerce-exporter' ); ?></h3>
			<div class="inside">
				<p class="description"><?php _e( 'To include additional custom Order, Order Item or Product meta associated to Order Items in the Export Orders table above fill the appropriate text box then click <em>Save Custom Fields</em>. The saved meta will appear as new export fields to be selected from the Order Fields list.', 'woocommerce-exporter' ); ?></p>
				<p class="description"><?php printf( __( 'For more information on exporting custom Order and Order Item meta consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
				<table class="form-table">

					<tr>
						<th>
							<label><?php _e( 'Order meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_orders" rows="5" cols="70"><?php echo esc_textarea( $custom_orders ); ?></textarea>
							<p class="description"><?php _e( 'Include additional custom Order meta in your export file by adding each custom Order meta name to a new line above. This is case sensitive.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

					<tr>
						<th>
							<label><?php _e( 'Order Item meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_order_items" rows="5" cols="70"><?php echo esc_textarea( $custom_order_items ); ?></textarea>
							<p class="description"><?php _e( 'Include additional custom Order Item meta in your export file by adding each custom Order Item meta name to a new line above. This is case sensitive.<br />For example: <code>Personalized Message</code> (new line) <code>_line_total</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

					<tr>
						<th>
							<label><?php _e( 'Order Item Product meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_order_products" rows="5" cols="70"><?php echo esc_textarea( $custom_order_products ); ?></textarea>
							<p class="description"><?php _e( 'Include additional custom Order Item Product meta in your export file by adding each custom Product meta name associated to Order Items to a new line above. This is case sensitive.<br />For example: <code>_sold_individually</code> (new line) <code>_manage_stock</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

					<?php do_action( 'woo_ce_orders_custom_fields' ); ?>

				</table>
				<p class="submit">
					<input type="submit" value="<?php _e( 'Save Custom Fields', 'woocommerce-exporter' ); ?>" class="button" />
				</p>
			</div>
			<!-- .inside -->
		</div>
		<!-- .postbox -->

	</div>
	<!-- #poststuff -->
	<input type="hidden" name="action" value="update" />
</form>
<!-- #export-orders-custom-fields -->
<?php
		ob_end_flush();

	}

	// Export templates

	function woo_ce_export_template_fields_order( $post_ID = 0 ) {

		$export_type = 'order';

		$fields = woo_ce_get_order_fields( 'full', $post_ID );

		$labels = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );

		// Check if labels is empty
		if( $labels == false )
			$labels = array();

		ob_start(); ?>
<div class="export-options <?php echo $export_type; ?>-options">

	<div class="options_group">
		<div class="form-field discount_type_field">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Order fields', 'woocommerce-exporter' ); ?></label>
			</p>
<?php if( !empty( $fields ) ) { ?>
			<table id="<?php echo $export_type; ?>-fields" class="ui-sortable">
				<tbody>
	<?php foreach( $fields as $field ) { ?>
					<tr id="<?php echo $export_type; ?>-<?php echo $field['reset']; ?>">
						<td>
							<label<?php if( isset( $field['hover'] ) ) { ?> title="<?php echo $field['hover']; ?>"<?php } ?>>
								<input type="checkbox" name="<?php echo $export_type; ?>_fields[<?php echo $field['name']; ?>]" class="<?php echo $export_type; ?>_field"<?php ( isset( $field['default'] ) ? checked( $field['default'], 1 ) : '' ); ?> /> <?php echo $field['label']; ?>
							</label>
							<input type="text" name="<?php echo $export_type; ?>_fields_label[<?php echo $field['name']; ?>]" class="text" placeholder="<?php echo $field['label']; ?>" value="<?php echo ( array_key_exists( $field['name'], $labels ) ? $labels[$field['name']] : '' ); ?>" />
							<input type="hidden" name="<?php echo $export_type; ?>_fields_order[<?php echo $field['name']; ?>]" class="field_order" value="<?php echo $field['order']; ?>" />
						</td>
					</tr>
	<?php } ?>
				</tbody>
			</table>
			<!-- #<?php echo $export_type; ?>-fields -->
<?php } else { ?>
			<p><?php _e( 'No Order fields were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		</div>
		<!-- .form-field -->
	</div>
	<!-- .options_group -->

</div>
<!-- .export-options -->
<?php
		ob_end_flush();

	}

	/* End of: WordPress Administration */

}
?>