<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	function woo_ce_scheduled_export_filters_commission( $post_ID = 0 ) {

		ob_start(); ?>
<div class="export-options commission-options">

	<?php do_action( 'woo_ce_scheduled_export_filters_commission', $post_ID ); ?>

</div>
<!-- .commission-options -->

<?php
		ob_end_flush();

	}

	// Scheduled Export filters

	// HTML template for Commission Sorting filter on Edit Scheduled Export screen
	function woo_ce_scheduled_export_commission_filter_orderby( $post_ID ) {

		$orderby = get_post_meta( $post_ID, '_filter_commission_orderby', true );
		// Default to ID
		if( $orderby == false ) {
			$orderby = 'ID';
		}

		ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field">
		<label for="commission_filter_orderby"><?php _e( 'Commission Sorting', 'woocommerce-exporter' ); ?></label>
		<select id="commission_filter_orderby" name="commission_filter_orderby">
			<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'Commission ID', 'woocommerce-exporter' ); ?></option>
			<option value="title"<?php selected( 'title', $orderby ); ?>><?php _e( 'Commission Title', 'woocommerce-exporter' ); ?></option>
			<option value="date"<?php selected( 'date', $orderby ); ?>><?php _e( 'Date Created', 'woocommerce-exporter' ); ?></option>
			<option value="modified"<?php selected( 'modified', $orderby ); ?>><?php _e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
			<option value="rand"<?php selected( 'rand', $orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
		</select>
	</p>
</div>
<!-- .options_group -->
<?php
		ob_end_flush();

	}

	// Quick Export filters

	// HTML template for Filter Commissions by Commission Date widget on Store Exporter screen
	function woo_ce_commissions_filter_by_date() {

		$today = date( 'l' );
		$yesterday = date( 'l', strtotime( '-1 days' ) );
		$current_month = date( 'F' );
		$last_month = date( 'F', mktime( 0, 0, 0, date( 'n' )-1, 1, date( 'Y' ) ) );
		$commission_dates_variable = '';
		$commission_dates_variable_length = '';
		$date_format = 'd/m/Y';
		$commission_dates_from = woo_ce_get_commission_first_date( $date_format );
		$commission_dates_to = date( $date_format );

		ob_start(); ?>
<p><label><input type="checkbox" id="commissions-filters-date" /> <?php _e( 'Filter Commissions by Commission Date', 'woocommerce-exporter' ); ?></label></p>
<div id="export-commissions-filters-date" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="today" /> <?php _e( 'Today', 'woocommerce-exporter' ); ?> (<?php echo $today; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="yesterday" /> <?php _e( 'Yesterday', 'woocommerce-exporter' ); ?> (<?php echo $yesterday; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="current_week" /> <?php _e( 'Current week', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="last_week" /> <?php _e( 'Last week', 'woocommerce-exporter' ); ?></label>
		</li>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="current_month" /> <?php _e( 'Current month', 'woocommerce-exporter' ); ?> (<?php echo $current_month; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="last_month" /> <?php _e( 'Last month', 'woocommerce-exporter' ); ?> (<?php echo $last_month; ?>)</label>
		</li>
<!--
		<li>
			<label><input type="radio" name="commission_dates_filter" value="last_quarter" /> <?php _e( 'Last quarter', 'woocommerce-exporter' ); ?> (Nov. - Jan.)</label>
		</li>
-->
		<li>
			<label><input type="radio" name="commission_dates_filter" value="variable" /> <?php _e( 'Variable date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<?php _e( 'Last', 'woocommerce-exporter' ); ?>
				<input type="text" name="commission_dates_filter_variable" class="text code" size="4" maxlength="4" value="<?php echo $commission_dates_variable; ?>" />
				<select name="commission_dates_filter_variable_length" style="vertical-align:top;">
					<option value=""<?php selected( $commission_dates_variable_length, '' ); ?>>&nbsp;</option>
					<option value="second"<?php selected( $commission_dates_variable_length, 'second' ); ?>><?php _e( 'second(s)', 'woocommerce-exporter' ); ?></option>
					<option value="minute"<?php selected( $commission_dates_variable_length, 'minute' ); ?>><?php _e( 'minute(s)', 'woocommerce-exporter' ); ?></option>
					<option value="hour"<?php selected( $commission_dates_variable_length, 'hour' ); ?>><?php _e( 'hour(s)', 'woocommerce-exporter' ); ?></option>
					<option value="day"<?php selected( $commission_dates_variable_length, 'day' ); ?>><?php _e( 'day(s)', 'woocommerce-exporter' ); ?></option>
					<option value="week"<?php selected( $commission_dates_variable_length, 'week' ); ?>><?php _e( 'week(s)', 'woocommerce-exporter' ); ?></option>
					<option value="month"<?php selected( $commission_dates_variable_length, 'month' ); ?>><?php _e( 'month(s)', 'woocommerce-exporter' ); ?></option>
					<option value="year"<?php selected( $commission_dates_variable_length, 'year' ); ?>><?php _e( 'year(s)', 'woocommerce-exporter' ); ?></option>
				</select>
			</div>
		</li>
		<li>
			<label><input type="radio" name="commission_dates_filter" value="manual" /> <?php _e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="commission_dates_from" name="commission_dates_from" value="<?php echo esc_attr( $commission_dates_from ); ?>" class="text code datepicker commission_export" /> to <input type="text" size="10" maxlength="10" id="commission_dates_to" name="commission_dates_to" value="<?php echo esc_attr( $commission_dates_to ); ?>" class="text code datepicker commission_export" />
				<p class="description"><?php _e( 'Filter the dates of Orders to be included in the export. Default is the date of the first Commission to today.', 'woocommerce-exporter' ); ?></p>
			</div>
		</li>
	</ul>
</div>
<!-- #export-commissions-filters-date -->
<?php
		ob_end_flush();

	}

	// HTML template for Commission Sorting widget on Store Exporter screen
	function woo_ce_commission_sorting() {

		$orderby = woo_ce_get_option( 'commission_orderby', 'ID' );
		$order = woo_ce_get_option( 'commission_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'Commission Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="commission_orderby">
		<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'Commission ID', 'woocommerce-exporter' ); ?></option>
		<option value="title"<?php selected( 'title', $orderby ); ?>><?php _e( 'Commission Title', 'woocommerce-exporter' ); ?></option>
		<option value="date"<?php selected( 'date', $orderby ); ?>><?php _e( 'Date Created', 'woocommerce-exporter' ); ?></option>
		<option value="modified"<?php selected( 'modified', $orderby ); ?>><?php _e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
		<option value="rand"<?php selected( 'rand', $orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="commission_order">
		<option value="ASC"<?php selected( 'ASC', $order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Commissions within the exported file. By default this is set to export Commissions by Commission ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	// HTML template for Filter Commissions by Product Vendor widget on Store Exporter screen
	function woo_ce_commissions_filter_by_product_vendor() {

		// Product Vendors - http://www.woothemes.com/products/product-vendors/
		if( woo_ce_detect_export_plugin( 'vendors' ) == false )
			return;

		$product_vendors = woo_ce_get_product_vendors( array(), 'full' );

		ob_start(); ?>
<p><label><input type="checkbox" id="commissions-filters-product_vendor" /> <?php _e( 'Filter Commissions by Product Vendors', 'woocommerce-exporter' ); ?></label></p>
<div id="export-commissions-filters-product_vendor" class="separator">
<?php if( $product_vendors ) { ?>
	<ul>
	<?php foreach( $product_vendors as $product_vendor ) { ?>
		<li>
			<label><input type="checkbox" name="commission_filter_product_vendor[<?php echo $product_vendor->term_id; ?>]" value="<?php echo $product_vendor->term_id; ?>" title="<?php printf( __( 'Term ID: %d', 'woocommerce-exporter' ), $product_vendor->term_id ); ?>"<?php disabled( $product_vendor->count, 0 ); ?> /> <?php echo $product_vendor->name; ?></label>
			<span class="description">(<?php echo $product_vendor->count; ?>)</span>
		</li>
	<?php } ?>
	</ul>
	<p class="description"><?php _e( 'Select the Product Vendors you want to filter exported Commissions by. Default is to include all Product Vendors.', 'woocommerce-exporter' ); ?></p>
<?php } else { ?>
	<p><?php _e( 'No Product Vendors were found.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
</div>
<!-- #export-commissions-filters-product_vendor -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Commissions by Commission Status widget on Store Exporter screen
	function woo_ce_commissions_filter_by_commission_status() {

		ob_start(); ?>
<p><label><input type="checkbox" id="commissions-filters-commission_status" /> <?php _e( 'Filter Commissions by Commission Status', 'woocommerce-exporter' ); ?></label></p>
<div id="export-commissions-filters-commission_status" class="separator">
	<ul>
		<li>
			<label><input type="checkbox" name="commission_filter_commission_status[]" value="unpaid"<?php disabled( woo_ce_commissions_stock_status_count( 'unpaid' ), 0 ); ?> /> <?php _e( 'Unpaid', 'woocommerce-exporter' ); ?></label>
			<span class="description">(<?php echo woo_ce_commissions_stock_status_count( 'unpaid' ); ?>)</span>
		</li>
		<li>
			<label><input type="checkbox" name="commission_filter_commission_status[]" value="paid"<?php disabled( woo_ce_commissions_stock_status_count( 'paid' ), 0 ); ?> /> <?php _e( 'Paid', 'woocommerce-exporter' ); ?></label>
			<span class="description">(<?php echo woo_ce_commissions_stock_status_count( 'paid' ); ?>)</span>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Commission Status you want to filter exported Commissions by. Default is to include all Commission Statuses.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-commissions-filters-commission_status -->
<?php
		ob_end_flush();

	}

	// Export templates

	function woo_ce_export_template_fields_commission( $post_ID = 0 ) {

		$export_type = 'commission';

		$fields = woo_ce_get_commission_fields( 'full', $post_ID );

		$labels = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );

		// Check if labels is empty
		if( $labels == false )
			$labels = array();

		ob_start(); ?>
<div class="export-options <?php echo $export_type; ?>-options">

	<div class="options_group">
		<div class="form-field discount_type_field">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Commission fields', 'woocommerce-exporter' ); ?></label>
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
			<p><?php _e( 'No Commission fields were found.', 'woocommerce-exporter' ); ?></p>
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