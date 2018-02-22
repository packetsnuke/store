<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	function woo_ce_scheduled_export_filters_review( $post_ID = 0 ) {

		ob_start(); ?>
<div class="export-options review-options">

	<?php do_action( 'woo_ce_scheduled_export_filters_review', $post_ID ); ?>

</div>
<!-- .review-options -->

<?php
		ob_end_flush();

	}

	// Scheduled Export filters

	// HTML template for Review Sorting filter on Edit Scheduled Export screen
	function woo_ce_scheduled_export_review_filter_orderby( $post_ID ) {

		$orderby = get_post_meta( $post_ID, '_filter_review_orderby', true );
		// Default to Title
		if( $orderby == false ) {
			$orderby = 'name';
		}

		ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field">
		<label for="review_filter_orderby"><?php _e( 'Review Sorting', 'woocommerce-exporter' ); ?></label>
		<select id="review_filter_orderby" name="review_filter_orderby">
			<option value="id"<?php selected( 'id', $orderby ); ?>><?php _e( 'Review ID', 'woocommerce-exporter' ); ?></option>
		</select>
	</p>
</div>
<!-- .options_group -->
<?php
		ob_end_flush();

	}

	function woo_ce_scheduled_export_review_filter_by_review_date( $post_ID ) {

		$types = get_post_meta( $post_ID, '_filter_review_date', true );

		ob_start(); ?>
<p class="form-field discount_type_field">
	<label for="review_dates_filter"><?php _e( 'Review date', 'woocommerce-exporter' ); ?></label>
	<input type="radio" name="review_dates_filter" value=""<?php checked( $types, false ); ?> />&nbsp;<?php _e( 'All', 'woocommerce-exporter' ); ?><br />
	<input type="radio" name="review_dates_filter" value="last_export"<?php checked( $types, 'last_export' ); ?> />&nbsp;<?php _e( 'Since last export', 'woocommerce-exporter' ); ?>
	<img class="help_tip" data-tip="<?php _e( 'Export Reviews which have not previously been included in an export. Decided by whether the <code>_woo_cd_exported</code> custom Post meta key has not been assigned to an Review.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
</p>
<?php
		ob_end_flush();

	}

	// Quick Export filters

	// HTML template for Review Sorting widget on Store Exporter screen
	function woo_ce_review_sorting() {

		$orderby = woo_ce_get_option( 'review_orderby', 'ID' );
		$order = woo_ce_get_option( 'review_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'Review Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="review_orderby">
		<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'Review ID', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="review_order">
		<option value="ASC"<?php selected( 'ASC', $order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Reviews within the exported file. By default this is set to export Review by Review ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	// Export templates

	function woo_ce_export_template_fields_review( $post_ID = 0 ) {

		$export_type = 'review';

		$fields = woo_ce_get_review_fields( 'full', $post_ID );

		$labels = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );

		// Check if labels is empty
		if( $labels == false )
			$labels = array();

		ob_start(); ?>
<div class="export-options <?php echo $export_type; ?>-options">

	<div class="options_group">
		<div class="form-field discount_type_field">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'Review fields', 'woocommerce-exporter' ); ?></label>
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
			<p><?php _e( 'No Review fields were found.', 'woocommerce-exporter' ); ?></p>
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