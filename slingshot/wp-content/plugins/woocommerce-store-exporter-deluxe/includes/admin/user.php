<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	function woo_ce_scheduled_export_filters_user( $post_ID = 0 ) {

		$user_roles = woo_ce_get_user_roles();
		$user_filter_role = get_post_meta( $post_ID, '_filter_user_role', true );

		ob_start(); ?>
<div class="export-options user-options">

	<?php do_action( 'woo_ce_scheduled_export_filters_user', $post_ID ); ?>

	<div class="options_group">
		<p class="form-field discount_type_field">
			<label for="user_filter_role"><?php _e( 'User role', 'woocommerce-exporter' ); ?></label>

<?php if( !empty( $user_roles ) ) { ?>
			<select id="user_filter_role" data-placeholder="<?php _e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="user_filter_role[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $user_roles as $key => $user_role ) { ?>
				<option value="<?php echo $key; ?>"<?php selected( ( !empty( $user_filter_role ) ? in_array( $key, $user_filter_role ) : false ), true ); ?>><?php echo ucfirst( $user_role['name'] ); ?> (<?php echo $user_role['count']; ?>)</option>
	<?php } ?>
			</select>
			<img class="help_tip" data-tip="<?php _e( 'Select the User Roles you want to filter exported Users by. Default is to include all User Roles.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
			<?php _e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</p>
	</div>
	<!-- .options_group -->

</div>
<!-- .user-options -->

<?php
		ob_end_flush();

	}

	// Scheduled Export filters

	// HTML template for User Sorting filter on Edit Scheduled Export screen
	function woo_ce_scheduled_export_user_filter_orderby( $post_ID ) {

		$orderby = get_post_meta( $post_ID, '_filter_user_orderby', true );
		// Default to ID
		if( $orderby == false ) {
			$orderby = 'ID';
		}

		ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field">
		<label for="user_filter_orderby"><?php _e( 'Order Sorting', 'woocommerce-exporter' ); ?></label>
		<select id="user_filter_orderby" name="user_filter_orderby">
			<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'User ID', 'woocommerce-exporter' ); ?></option>
			<option value="display_name"<?php selected( 'display_name', $orderby ); ?>><?php _e( 'Display Name', 'woocommerce-exporter' ); ?></option>
			<option value="user_name"<?php selected( 'user_name', $orderby ); ?>><?php _e( 'Name', 'woocommerce-exporter' ); ?></option>
			<option value="user_login"<?php selected( 'user_login', $orderby ); ?>><?php _e( 'Username', 'woocommerce-exporter' ); ?></option>
			<option value="nicename"<?php selected( 'nicename', $orderby ); ?>><?php _e( 'Nickname', 'woocommerce-exporter' ); ?></option>
			<option value="email"<?php selected( 'email', $orderby ); ?>><?php _e( 'E-mail', 'woocommerce-exporter' ); ?></option>
			<option value="url"<?php selected( 'url', $orderby ); ?>><?php _e( 'Website', 'woocommerce-exporter' ); ?></option>
			<option value="registered"<?php selected( 'registered', $orderby ); ?>><?php _e( 'Date Registered', 'woocommerce-exporter' ); ?></option>
			<option value="rand"<?php selected( 'rand', $orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
		</select>
	</p>
</div>
<!-- .options_group -->
<?php
		ob_end_flush();

	}

	// Quick Export filters

	// HTML template for Filter Users by User Role widget on Store Exporter screen
	function woo_ce_users_filter_by_user_role() {

		$user_roles = woo_ce_get_user_roles();

		ob_start(); ?>
<p><label><input type="checkbox" id="users-filters-user_role" /> <?php _e( 'Filter Users by User Role', 'woocommerce-exporter' ); ?></label></p>
<div id="export-users-filters-user_role" class="separator">
	<ul>
		<li>
<?php if( !empty( $user_roles ) ) { ?>
			<select data-placeholder="<?php _e( 'Choose a User Role...', 'woocommerce-exporter' ); ?>" name="user_filter_user_role[]" multiple class="chzn-select" style="width:95%;">
	<?php foreach( $user_roles as $key => $user_role ) { ?>
				<option value="<?php echo $key; ?>"><?php echo ucfirst( $user_role['name'] ); ?></option>
	<?php } ?>
			</select>
<?php } else { ?>
			<?php _e( 'No User Roles were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		</li>
	</ul>
	<p class="description"><?php _e( 'Select the User Roles you want to filter exported Users by. Default is to include all User Role options.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- #export-users-filters-user_role -->
<?php
		ob_end_flush();

	}

	// HTML template for Filter Users by Date Registered widget on Store Exporter screen
	function woo_ce_users_filter_by_date_registered() {

		$date_format = 'd/m/Y';
		$user_dates_from = woo_ce_get_user_first_date( $date_format );
		$user_dates_to = date( $date_format );

		ob_start(); ?>
<p><label><input type="checkbox" id="users-filters-date_registered" /> <?php _e( 'Filter Users by Date Registered', 'woocommerce-exporter' ); ?></label></p>
<div id="export-users-filters-date_registered" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="user_dates_filter" value="manual" /> <?php _e( 'Fixed date', 'woocommerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="user_dates_from" name="user_dates_from" value="<?php echo esc_attr( $user_dates_from ); ?>" class="text code datepicker user_export" /> to <input type="text" size="10" maxlength="10" id="user_dates_to" name="user_dates_to" value="<?php echo esc_attr( $user_dates_to ); ?>" class="text code datepicker user_export" />
				<p class="description"><?php _e( 'Filter the dates of Users to be included in the export. Default is the date of the first User registered to today in the date format <code>DD/MM/YYYY</code>.', 'woocommerce-exporter' ); ?></p>
			</div>
		</li>
	</ul>
</div>
<!-- #export-users-filters-date_registered -->
<?php
		ob_end_flush();

	}

	// HTML template for jump link to Store Exporter screen
	function woo_ce_users_custom_fields_link() {

		ob_start(); ?>
<div id="export-users-custom-fields-link">
	<p><a href="#export-users-custom-fields"><?php _e( 'Manage Custom User Fields', 'woocommerce-exporter' ); ?></a></p>
</div>
<!-- #export-users-custom-fields-link -->
<?php
		ob_end_flush();

	}

	// HTML template for User Sorting widget on Store Exporter screen
	function woo_ce_user_sorting() {

		$orderby = woo_ce_get_option( 'user_orderby', 'ID' );
		$order = woo_ce_get_option( 'user_order', 'ASC' );

		ob_start(); ?>
<p><label><?php _e( 'User Sorting', 'woocommerce-exporter' ); ?></label></p>
<div>
	<select name="user_orderby">
		<option value="ID"<?php selected( 'ID', $orderby ); ?>><?php _e( 'User ID', 'woocommerce-exporter' ); ?></option>
		<option value="display_name"<?php selected( 'display_name', $orderby ); ?>><?php _e( 'Display Name', 'woocommerce-exporter' ); ?></option>
		<option value="user_name"<?php selected( 'user_name', $orderby ); ?>><?php _e( 'Name', 'woocommerce-exporter' ); ?></option>
		<option value="user_login"<?php selected( 'user_login', $orderby ); ?>><?php _e( 'Username', 'woocommerce-exporter' ); ?></option>
		<option value="nicename"<?php selected( 'nicename', $orderby ); ?>><?php _e( 'Nickname', 'woocommerce-exporter' ); ?></option>
		<option value="email"<?php selected( 'email', $orderby ); ?>><?php _e( 'E-mail', 'woocommerce-exporter' ); ?></option>
		<option value="url"<?php selected( 'url', $orderby ); ?>><?php _e( 'Website', 'woocommerce-exporter' ); ?></option>
		<option value="registered"<?php selected( 'registered', $orderby ); ?>><?php _e( 'Date Registered', 'woocommerce-exporter' ); ?></option>
		<option value="rand"<?php selected( 'rand', $orderby ); ?>><?php _e( 'Random', 'woocommerce-exporter' ); ?></option>
	</select>
	<select name="user_order">
		<option value="ASC"<?php selected( 'ASC', $order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
		<option value="DESC"<?php selected( 'DESC', $order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Users within the exported file. By default this is set to export User by User ID in Desending order.', 'woocommerce-exporter' ); ?></p>
</div>
<?php
		ob_end_flush();

	}

	// HTML template for Custom Users widget on Store Exporter screen
	function woo_ce_users_custom_fields() {

		if( $custom_users = woo_ce_get_option( 'custom_users', '' ) )
			$custom_users = implode( "\n", $custom_users );

		$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

		ob_start(); ?>
<form method="post" id="export-users-custom-fields" class="export-options user-options">
	<div id="poststuff">

		<div class="postbox" id="export-options user-options">
			<h3 class="hndle"><?php _e( 'Custom User Fields', 'woocommerce-exporter' ); ?></h3>
			<div class="inside">
				<p class="description"><?php _e( 'To include additional custom User meta in the Export Users table above fill the Users text box then click Save Custom Fields. The saved meta will appear as new export fields to be selected from the User Fields list.', 'woocommerce-exporter' ); ?></p>
				<p class="description"><?php printf( __( 'For more information on exporting custom User meta consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
				<table class="form-table">

					<tr>
						<th>
							<label><?php _e( 'User meta', 'woocommerce-exporter' ); ?></label>
						</th>
						<td>
							<textarea name="custom_users" rows="5" cols="70"><?php echo esc_textarea( $custom_users ); ?></textarea>
							<p class="description"><?php _e( 'Include additional custom User meta in your export file by adding each custom User meta name to a new line above.<br />For example: <code>Customer UA (new line) Customer IP Address</code>', 'woocommerce-exporter' ); ?></p>
						</td>
					</tr>

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
<!-- #export-users-custom-fields -->
<?php
		ob_end_flush();

	}

	// Export templates

	function woo_ce_export_template_fields_user( $post_ID = 0 ) {

		$export_type = 'user';

		$fields = woo_ce_get_user_fields( 'full', $post_ID );

		$labels = get_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), true );

		// Check if labels is empty
		if( $labels == false )
			$labels = array();

		ob_start(); ?>
<div class="export-options <?php echo $export_type; ?>-options">

	<div class="options_group">
		<div class="form-field discount_type_field">
			<p class="form-field discount_type_field ">
				<label><?php _e( 'User fields', 'woocommerce-exporter' ); ?></label>
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
			<p><?php _e( 'No User fields were found.', 'woocommerce-exporter' ); ?></p>
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