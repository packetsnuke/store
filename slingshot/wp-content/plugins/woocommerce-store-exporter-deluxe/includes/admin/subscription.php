<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	function woo_ce_scheduled_export_filters_subscription( $post_ID = 0 ) {

		ob_start(); ?>
<div class="export-options subscription-options">

	<?php do_action( 'woo_ce_scheduled_export_filters_subscription', $post_ID ); ?>

</div>
<!-- .subscription-options -->

<?php
		ob_end_flush();

	}

	// Scheduled Export filters

	// HTML template for Subscription Sorting filter on Edit Scheduled Export screen
	function woo_ce_scheduled_export_subscription_filter_orderby( $post_ID ) {

		$orderby = get_post_meta( $post_ID, '_filter_subscription_orderby', true );
		// Default to Title
		if( $orderby == false )
			$orderby = 'name';

		ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field">
		<label for="subscription_filter_orderby"><?php _e( 'Subscription Sorting', 'woocommerce-exporter' ); ?></label>
		<select id="subscription_filter_orderby" name="subscription_filter_orderby">
			<option value="start_date"<?php selected( 'start_date', $orderby ); ?>><?php _e( 'Start date', 'woocommerce-exporter' ); ?></option>
			<option value="expiry_date"<?php selected( 'expiry_date', $orderby ); ?>><?php _e( 'Expiry date', 'woocommerce-exporter' ); ?></option>
			<option value="end_date"<?php selected( 'end_date', $orderby ); ?>><?php _e( 'End date', 'woocommerce-exporter' ); ?></option>
			<option value="status"<?php selected( 'status', $orderby ); ?>><?php _e( 'Status', 'woocommerce-exporter' ); ?></option>
			<option value="name"<?php selected( 'name', $orderby ); ?>><?php _e( 'Name', 'woocommerce-exporter' ); ?></option>
			<option value="order_id"<?php selected( 'order_id', $orderby ); ?>><?php _e( 'Order ID', 'woocommerce-exporter' ); ?></option>
		</select>
	</p>
</div>
<!-- .options_group -->
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_subscription_filter_by_subscription_product( $post_ID ) {

		$products = woo_ce_get_subscription_products();
		$types = get_post_meta( $post_ID, '_filter_subscription_sku', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="subscription_filter_sku"><?php _e( 'Subscription Product', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $products ) ) { ?>
	<select data-placeholder="<?php _e( 'Choose a Subscription Product...', 'woocommerce-exporter' ); ?>" name="subscription_filter_sku[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $products as $product ) { ?>
		<option value="<?php echo $product; ?>"<?php selected( ( !empty( $types ) ? in_array( $product, $types ) : false ), true ); ?>><?php echo woo_ce_format_post_title( get_the_title( $product ) ); ?> (<?php printf( __( 'SKU: %s', 'woocommerce-exporter' ), get_post_meta( $product, '_sku', true ) ); ?>)</option>
	<?php } ?>
	</select>
<?php } else { ?>
	<?php _e( 'No Subscription Products were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
	<img class="help_tip" data-tip="<?php _e( 'Select the Subscription Product you want to filter exported Subscriptions by. Default is to include all Subscription Products.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
</p>
<?php
		ob_end_flush();

	}

	// Quick Export filters

	// HTML template for Filter Subscriptions by Subscription Status widget on Store Exporter screen
	function woo_ce_subscriptions_filter_by_subscription_status() {

		$subscription_statuses = woo_ce_get_subscription_statuses();

		ob_start(); ?>
<p><label><input type="checkbox" id="subscriptions-filters-status" /> <?php _e( 'Filter Subscriptions by Subscription Status', 'woocommerce-exporter' ); ?></label></p>
<div id="export-subscriptions-filters-status" class="separator">
	<ul>
		<li>
<?php if( !empty( $subscription_statuses ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Subscription Status...', 'woocommerce-exporter' ); ?>" name="subscription_filter_status[]" class="chzn-select" style="width:95%;">
				<option value=""></option>
	<?php foreach( $subscription_statuses as $key => $subscription_status ) { ?>
				<option value="<?php echo $key; ?>"><?php echo $subscription_status; ?></option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Subscription Status\'s have been found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Subscription Status options you want to filter exported Subscriptions by. Due to a limitation in WooCommerce Subscriptions you can only filter by a single Subscription Status. Default is to include all Subscription Status options.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-subscriptions-filters-status -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Subscriptions by Subscription Product widget on Store Exporter screen
	function woo_ce_subscriptions_filter_by_subscription_product() {

		$products = woo_ce_get_subscription_products();

		ob_start(); ?>
<p><label><input type="checkbox" id="subscriptions-filters-product" /> <?php _e( 'Filter Subscriptions by Subscription Product', 'woocommerce-exporter' ); ?></label></p>
<div id="export-subscriptions-filters-product" class="separator">
	<ul>
		<li>
<?php if( !empty( $products ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Subscription Product...', 'woocommerce-exporter' ); ?>" name="subscription_filter_product[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $products as $product ) { ?>
				<option value="<?php echo $product; ?>"><?php echo woo_ce_format_post_title( get_the_title( $product ) ); ?> (<?php printf( __( 'SKU: %s', 'woocommerce-exporter' ), get_post_meta( $product, '_sku', true ) ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Subscription Products were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Subscription Product you want to filter exported Subscriptions by. Default is to include all Subscription Products.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-subscriptions-filters-status -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Subscriptions by Customer widget on Store Exporter screen
	function woo_ce_subscriptions_filter_by_customer() {

		$user_count = woo_ce_get_export_type_count( 'user' );
		$list_limit = apply_filters( 'woo_ce_subscription_filter_customer_list_limit', 100, $user_count );
		if( $user_count < $list_limit )
			$customers = woo_ce_get_customers_list();

		ob_start(); ?>
<p><label><input type="checkbox" id="subscriptions-filters-customer" /> <?php _e( 'Filter Subscriptions by Customer', 'woocommerce-exporter' ); ?></label></p>
<div id="export-subscriptions-filters-customer" class="separator">
	<ul>
		<li>
<?php if( $user_count < $list_limit ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Customer...', 'woocommerce-exporter' ); ?>" id="subscription_customer" name="subscription_filter_customer[]" multiple class="chzn-select" style="width:95%;">
				<option value=""></option>
	<?php if( !empty( $customers ) ) { ?>
		<?php foreach( $customers as $customer ) { ?>
				<option value="<?php echo $customer->ID; ?>"><?php printf( '%s (#%s - %s)', $customer->display_name, $customer->ID, $customer->user_email ); ?></option>
		<?php } ?>
	<?php } ?>
			</select>
<?php } else { ?>
			<input type="text" id="subscription_customer" name="subscription_filter_customer" size="20" class="text" />
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Filter Subscriptions by Customer (unique e-mail address) to be included in the export.', 'woocommerce-exporter' ); ?><?php if( $user_count > $list_limit ) { echo ' ' . __( 'Enter a list of User ID\'s separated by a comma character.', 'woocommerce-exporter' ); } ?> <?php _e( 'Default is to include all Subscriptions.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-subscriptions-filters-customer -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Subscriptions by Source widget on Store Exporter screen
	function woo_ce_subscriptions_filter_by_source() {

		$types = false;

		ob_start(); ?>
<p><label><input type="checkbox" id="subscriptions-filters-source" /> <?php _e( 'Filter Subscriptions by Source', 'woocommerce-exporter' ); ?></label></p>
<div id="export-subscriptions-filters-source" class="separator">
	<ul>
		<li value=""><label><input type="radio" name="subscription_filter_source" value=""<?php checked( $types, false ); ?> /><?php _e( 'Include both', 'woocommerce-exporter' ); ?></label></li>
		<li value="customer"><label><input type="radio" name="subscription_filter_source" value="customer" /><?php _e( 'Customer Subscriptions', 'woocommerce-exporter' ); ?></label></li>
		<li value="manual"><label><input type="radio" name="subscription_filter_source" value="manual" /><?php _e( 'Added via WordPress Administration', 'woocommerce-exporter' ); ?></label></li>
	</ul>
	<p class="description"><?php _e( 'Select the Subscription Source you want to filter exported Subscriptions by. Default is to include all Subscription Sources.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-subscriptions-filters-source -->
<?php
		ob_end_flush();

	}

	// HTML template for Subscription Sorting widget on Store Exporter screen
	function woo_ce_subscription_sorting() {

		$orderby = woo_ce_get_option( 'subscription_orderby', 'start_date' );
		$order = woo_ce_get_option( 'subscription_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'Subscription Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="subscription_orderby">
		<option value="start_date"<?php selected( 'start_date', $orderby ); ?>><?php _e( 'Start date', 'woocommerce-exporter' ); ?></option>
		<option value="expiry_date"<?php selected( 'expiry_date', $orderby ); ?>><?php _e( 'Expiry date', 'woocommerce-exporter' ); ?></option>
		<option value="end_date"<?php selected( 'end_date', $orderby ); ?>><?php _e( 'End date', 'woocommerce-exporter' ); ?></option>
		<option value="status"<?php selected( 'status', $orderby ); ?>><?php _e( 'Status', 'woocommerce-exporter' ); ?></option>
		<option value="name"<?php selected( 'name', $orderby ); ?>><?php _e( 'Name', 'woocommerce-exporter' ); ?></option>
		<option value="order_id"<?php selected( 'order_id', $orderby ); ?>><?php _e( 'Order ID', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="subscription_order">
		<option value="ASC"<?php selected( 'ASC', $order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Subscriptions within the exported file. By default this is set to export Subscriptions by Start date in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	// HTML template for jump link to Custom Subscription Fields within Subscription Options on Store Exporter screen
	function woo_ce_subscriptions_custom_fields_link() {

		ob_start(); ?>
<div id="export-subscriptions-custom-fields-link">
	<p><a href="#export-subscriptions-custom-fields"><?php _e( 'Manage Custom Subscription Fields', 'woocommerce-exporter' ); ?></a></p>
</div>
<!-- #export-subscriptions-custom-fields-link -->
<?php
		ob_end_flush();

	}

	// HTML template for Custom Subscriptions widget on Store Exporter screen
	function woo_ce_subscriptions_custom_fields() {

		if( $custom_subscriptions = woo_ce_get_option( 'custom_subscriptions', '' ) )
			$custom_subscriptions = implode( "\n", $custom_subscriptions );

		$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

		ob_start(); ?>
<form method="post" id="export-subscriptions-custom-fields" class="export-options subscription-options">
	<div id="poststuff">

		<div class="postbox" id="export-options">
			<h3 class="hndle"><?php _e( 'Custom Subscription Fields', 'woocommerce-exporter' ); ?></h3>
			<div class="inside">
				<p class="description"><?php _e( 'To include additional custom Subscription meta in the Export Subscriptions table above fill the appropriate text box then click <em>Save Custom Fields</em>. The saved meta will appear as new export fields to be selected from the Subscription Fields list.', 'woocommerce-exporter' ); ?></p>
				<p class="description"><?php printf( __( 'For more information on exporting custom Subscription meta consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
				<table class="form-table">

					<tr>
						<th>
							<label><?php _e( 'Subscription meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_subscriptions" rows="5" cols="70"><?php echo esc_textarea( $custom_subscriptions ); ?></textarea>
							<p class="description"><?php _e( 'Include additional custom Subscription meta in your export file by adding each custom Subscription meta name to a new line above. This is case sensitive.<br />For example: <code>Customer UA</code> (new line) <code>Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>
					<?php do_action( 'woo_ce_subscriptions_custom_fields' ); ?>

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
<!-- #export-subscriptions-custom-fields -->
<?php
		ob_end_flush();

	}

	// Export templates

	function woo_ce_export_template_fields_subscription( $post_ID = 0 ) {

		$export_type = 'subscription';

		$fields = woo_ce_get_subscription_fields( 'full', $post_ID );
		$labels = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );
		// Check if labels is empty
		if( $labels == false )
			$labels = array();

		ob_start(); ?>
<div class="export-options <?php echo $export_type; ?>-options">

	<div class="options_group">
		<div class="form-field discount_type_field">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Subscription fields', 'woocommerce-exporter' ); ?></label>
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
			<p><?php _e( 'No Subscription fields were found.', 'woocommerce-exporter' ); ?></p>
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