<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	function woo_ce_scheduled_export_filters_coupon( $post_ID = 0 ) {

		ob_start(); ?>
<div class="export-options coupon-options">

	<?php do_action( 'woo_ce_scheduled_export_filters_coupon', $post_ID ); ?>

</div>
<!-- .coupon-options -->

<?php
		ob_end_flush();

	}

	// Scheduled Export filters

	// HTML template for Coupon Sorting filter on Edit Scheduled Export screen
	function woo_ce_scheduled_export_coupon_filter_orderby( $post_ID ) {

		$orderby = get_post_meta( $post_ID, '_filter_coupon_orderby', true );
		// Default to ID
		if( $orderby == false )
			$orderby = 'ID';

		ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field">
		<label for="coupon_filter_orderby"><?php _e( 'Coupon Sorting', 'woocommerce-exporter' ); ?></label>
		<select id="coupon_filter_orderby" name="coupon_filter_orderby">
			<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'Coupon ID', 'woocommerce-exporter' ); ?></option>
			<option value="title"<?php selected( 'title', $orderby ); ?>><?php _e( 'Coupon Code', 'woocommerce-exporter' ); ?></option>
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

	// HTML template for Filter Coupons by Discount Type widget on Scheduled Export screen
	function woo_ce_scheduled_export_coupon_filter_by_discount_type( $post_ID ) {

		$discount_types = woo_ce_get_coupon_discount_types();
		$types = get_post_meta( $post_ID, '_filter_coupon_discount_type', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="coupon_filter_discount_type"><?php _e( 'Discount type', 'woocommerce-exporter' ); ?></label>
<?php if( !empty( $discount_types ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Discount Type...', 'woocommerce-exporter' ); ?>" name="coupon_filter_discount_type[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $discount_types as $key => $discount_type ) { ?>
				<option value="<?php echo $key; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo $discount_type; ?> (<?php printf( __( 'Post meta name: %s', 'woocommerce-exporter' ), $key ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Discount Types were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
</p>

<?php
		ob_end_flush();

	}

	// Quick Export filters

	// HTML template for Filter Coupons by Discount Type on Store Exporter screen
	function woo_ce_coupons_filter_by_discount_type() {

		$discount_types = woo_ce_get_coupon_discount_types();
		$types = woo_ce_get_option( 'coupon_discount_type', array() );

		ob_start(); ?>
<p><label><input type="checkbox" id="coupons-filters-discount_types"<?php checked( !empty( $types ), true ); ?> /> <?php _e( 'Filter Coupons by Discount Type', 'woocommerce-exporter' ); ?></label></p>
<div id="export-coupons-filters-discount_types" class="separator">
	<ul>
		<li>
<?php if( !empty( $discount_types ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a Discount Type...', 'woocommerce-exporter' ); ?>" name="coupon_filter_discount_type[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $discount_types as $key => $discount_type ) { ?>
				<option value="<?php echo $key; ?>"<?php echo ( is_array( $types ) ? selected( in_array( $key, $types, false ), true ) : '' ); ?>><?php echo $discount_type; ?> (<?php printf( __( 'Post meta name: %s', 'woocommerce-exporter' ), $key ); ?>)</option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No Discount Types were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the Discount Types you want to filter exported Coupons by. Default is to include all Coupons.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-products-filters-discount_types -->

<?php
		ob_end_flush();

	}

	// HTML template for Coupon Sorting widget on Store Exporter screen
	function woo_ce_coupon_sorting() {

		$orderby = woo_ce_get_option( 'coupon_orderby', 'ID' );
		$order = woo_ce_get_option( 'coupon_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'Coupon Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="coupon_orderby">
		<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'Coupon ID', 'woocommerce-exporter' ); ?></option>
		<option value="title"<?php selected( 'title', $orderby ); ?>><?php _e( 'Coupon Code', 'woocommerce-exporter' ); ?></option>
		<option value="date"<?php selected( 'date', $orderby ); ?>><?php _e( 'Date Created', 'woocommerce-exporter' ); ?></option>
		<option value="modified"<?php selected( 'modified', $orderby ); ?>><?php _e( 'Date Modified', 'woocommerce-exporter' ); ?></option>
		<option value="rand"<?php selected( 'rand', $orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="coupon_order">
		<option value="ASC"<?php selected( 'ASC', $order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Coupons within the exported file. By default this is set to export Coupons by Coupon ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	// Export templates

	function woo_ce_export_template_fields_coupon( $post_ID = 0 ) {

		$export_type = 'coupon';

		$fields = woo_ce_get_coupon_fields( 'full', $post_ID );

		$labels = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );

		// Check if labels is empty
		if( $labels == false )
			$labels = array();

		ob_start(); ?>
<div class="export-options <?php echo $export_type; ?>-options">

	<div class="options_group">
		<div class="form-field discount_type_field">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Coupon fields', 'woocommerce-exporter' ); ?></label>
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
			<p><?php _e( 'No Coupon fields were found.', 'woocommerce-exporter' ); ?></p>
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