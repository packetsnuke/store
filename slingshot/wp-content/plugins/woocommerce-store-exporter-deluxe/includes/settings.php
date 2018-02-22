<?php
function woo_ce_export_settings_quicklinks() {

	ob_start(); ?>
<li>| <a href="#xml-settings"><?php _e( 'XML Settings', 'woocommerce-exporter' ); ?></a> |</li>
<li><a href="#rss-settings"><?php _e( 'RSS Settings', 'woocommerce-exporter' ); ?></a> |</li>
<li><a href="#scheduled-exports"><?php _e( 'Scheduled Exports', 'woocommerce-exporter' ); ?></a> |</li>
<li><a href="#cron-exports"><?php _e( 'CRON Exports', 'woocommerce-exporter' ); ?></a> |</li>
<li><a href="#orders-screen"><?php _e( 'Orders Screen', 'woocommerce-exporter' ); ?></a> |</li>
<li><a href="#export-triggers"><?php _e( 'Export Triggers', 'woocommerce-exporter' ); ?></a></li>
<?php
	ob_end_flush();

}

function woo_ce_export_settings_multisite() {

	if( is_multisite() == false || is_super_admin() == false )
		return;

	$sites = wp_get_sites();

	ob_start(); ?>
<tr>
	<th>
		<label for="multisite"><?php _e( 'Multisite', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
<?php if( !empty( $sites ) ) { ?>
	<?php foreach( $sites as $site ) { ?>
		<p>
			<?php echo $site['blog_id']; ?>: <?php echo $site['domain']; ?>
		<?php if( is_main_network( $site['blog_id'] ) ) { ?>
		 (<?php _e( 'Network Admin', 'woocommerce-exporter' ); ?>)
		<?php } ?>
		</p>
	<?php } ?>
<?php } else { ?>
		<p><?php _e( 'No sites were detected.', 'woocommerce-exporter' ); ?></p>
<?php } ?>
		<p class="description"><?php _e( 'Choose whether Store Exporter Deluxe exports from the current site or specific sites in MultiSite networks.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
	ob_end_flush();

}
// add_action( 'woo_ce_export_settings_general', 'woo_ce_export_settings_multisite' );

function woo_ce_export_settings_general() {

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

	$export_filename = woo_ce_get_option( 'export_filename', '' );
	// Strip file extension from export filename
	if( ( strpos( $export_filename, '.csv' ) !== false ) || ( strpos( $export_filename, '.xml' ) !== false ) || ( strpos( $export_filename, '.xls' ) !== false ) )
		$export_filename = str_replace( array( '.csv', '.xml', '.xls' ), '', $export_filename );
	// Default export filename
	if( $export_filename == false )
		$export_filename = '%store_name%-export_%dataset%-%date%-%time%-%random%';
	$delete_file = woo_ce_get_option( 'delete_file', 1 );
	$file_encodings = ( function_exists( 'mb_list_encodings' ) ? mb_list_encodings() : false );
	$encoding = woo_ce_get_option( 'encoding', 'UTF-8' );
	$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );
	// Reset the Date Format if corrupted
	if( $date_format == '1' || $date_format == '' || $date_format == false )
		$date_format = 'd/m/Y';
	$escape_formatting = woo_ce_get_option( 'escape_formatting', 'all' );
	$timeout = woo_ce_get_option( 'timeout', 0 );
	$header_formatting = woo_ce_get_option( 'header_formatting', 1 );
	$bom = woo_ce_get_option( 'bom', 1 );

	ob_start(); ?>
<tr valign="top">
	<th scope="row"><label for="export_filename"><?php _e( 'Export filename', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<input type="text" name="export_filename" id="export_filename" value="<?php echo esc_attr( $export_filename ); ?>" class="large-text code" />
		<p class="description"><?php _e( 'The filename of the exported export type. It is not neccesary to add the filename extension (e.g. .csv, .tsv, .xls, .xlsx, .xml, etc.) as this is added at export time. Tags can be used: ', 'woocommerce-exporter' ); ?> <code>%dataset%</code>, <code>%date%</code>, <code>%time%</code>, <code>%random%</code>, <code>%store_name%</code>.</p>
	</td>
</tr>

<tr>
	<th>
		<label for="delete_file"><?php _e( 'Enable archives', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<select id="delete_file" name="delete_file">
			<option value="0"<?php selected( $delete_file, 0 ); ?>><?php _e( 'Yes', 'woocommerce-exporter' ); ?></option>
			<option value="1"<?php selected( $delete_file, 1 ); ?>><?php _e( 'No', 'woocommerce-exporter' ); ?></option>
		</select>
<?php if( $delete_file == 1 && woo_ce_get_option( 'hide_archives_tab', 0 ) == 1 ) { ?>
		<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'restore_archives_tab', '_wpnonce' => wp_create_nonce( 'woo_ce_restore_archives_tab' ) ) ) ); ?>"><?php _e( 'Restore Archives tab', 'woocommerce-exporter' ); ?></a>
<?php } ?>
<?php if( $delete_file == 0 ) { ?>
		<p class="warning"><?php echo __( 'Warning: Saving sensitve export files (e.g. Customers, Orders, etc.) to the WordPress Media directory will make export files accessible without restriction if the WordPress Media directory is allowed to be indexed.', 'woocommerce-exporter' ). ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)'; ?></p>
<?php } ?>
		<p class="description"><?php _e( 'Save copies of exports to the WordPress Media for later downloading. By default this option is turned off.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<tr>
	<th>
		<label for="encoding"><?php _e( 'Character encoding', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
<?php if( $file_encodings ) { ?>
		<select id="encoding" name="encoding">
			<option value=""><?php _e( 'System default', 'woocommerce-exporter' ); ?></option>
	<?php foreach( $file_encodings as $key => $chr ) { ?>
			<option value="<?php echo $chr; ?>"<?php selected( $chr, $encoding ); ?>><?php echo $chr; ?></option>
	<?php } ?>
		</select>
<?php } else { ?>
	<?php if( version_compare( phpversion(), '5', '<' ) ) { ?>
		<p class="description"><?php _e( 'Character encoding options are unavailable in PHP 4, contact your hosting provider to update your site install to use PHP 5 or higher.', 'woocommerce-exporter' ); ?></p>
	<?php } else { ?>
		<p class="description"><?php _e( 'Character encoding options are unavailable as the required mb_list_encodings() function is missing, contact your hosting provider to have the mbstring extension installed.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
<?php } ?>
	</td>
</tr>

<tr>
	<th><?php _e( 'Date format', 'woocommerce-exporter' ); ?></th>
	<td>
		<ul style="margin-top:0.2em;">
			<li><label title="F j, Y"><input type="radio" name="date_format" value="F j, Y"<?php checked( $date_format, 'F j, Y' ); ?>> <span><?php echo date( 'F j, Y' ); ?></span></label></li>
			<li><label title="Y/m/d"><input type="radio" name="date_format" value="Y/m/d"<?php checked( $date_format, 'Y/m/d' ); ?>> <span><?php echo date( 'Y/m/d' ); ?></span></label></li>
			<li><label title="m/d/Y"><input type="radio" name="date_format" value="m/d/Y"<?php checked( $date_format, 'm/d/Y' ); ?>> <span><?php echo date( 'm/d/Y' ); ?></span></label></li>
			<li><label title="d/m/Y"><input type="radio" name="date_format" value="d/m/Y"<?php checked( $date_format, 'd/m/Y' ); ?>> <span><?php echo date( 'd/m/Y' ); ?></span></label></li>
			<li><label><input type="radio" name="date_format" value="custom"<?php checked( in_array( $date_format, array( 'F j, Y', 'Y/m/d', 'm/d/Y', 'd/m/Y' ) ), false ); ?>/> <?php _e( 'Custom', 'woocommerce-exporter' ); ?>: </label><input type="text" name="date_format_custom" value="<?php echo sanitize_text_field( $date_format ); ?>" class="text" /></li>
			<li><a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank"><?php _e( 'Documentation on date and time formatting', 'woocommerce-exporter' ); ?></a>.</li>
		</ul>
		<p class="description"><?php _e( 'The date format option affects how date\'s are presented within your export file. Default is set to DD/MM/YYYY.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<tr>
	<th>
		<?php _e( 'Field escape formatting', 'woocommerce-exporter' ); ?>
	</th>
	<td>
		<ul style="margin-top:0.2em;">
			<li><label><input type="radio" name="escape_formatting" value="all"<?php checked( $escape_formatting, 'all' ); ?> />&nbsp;<?php _e( 'Escape all cells', 'woocommerce-exporter' ); ?> - <span class="description"><?php _e( 'This will write field data exactly as it is saved in the database, regardless of the field type/format.', 'woocommerce-exporter' ); ?></span></label></li>
			<li><label><input type="radio" name="escape_formatting" value="excel"<?php checked( $escape_formatting, 'excel' ); ?> />&nbsp;<?php _e( 'Escape cells as Excel would', 'woocommerce-exporter' ); ?> - <span class="description"><?php _e( 'This will change field data when writing to export file, just the same way Excel changes field data when you import CSV into Excel (Caution: this may mess up phone numbers and postcodes with leading zeroes).', 'woocommerce-exporter' ); ?></span></label></li>
			<li><label><input type="radio" name="escape_formatting" value="none"<?php checked( $escape_formatting, 'none' ); ?> />&nbsp;<?php _e( 'Do not escape any cells', 'woocommerce-exporter' ); ?> - <span class="description"><?php _e( 'This will not change any field data when writing to export file (Caution: this may mess up text fields containing the delimiter character).', 'woocommerce-exporter' ); ?></span></label></li>
		</ul>
		<p class="description"><?php _e( 'Choose the field escape format that suits your spreadsheet software (e.g. Excel).', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<?php if( !ini_get( 'safe_mode' ) ) { ?>
<tr>
	<th>
		<label for="timeout"><?php _e( 'Script timeout', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<select id="timeout" name="timeout">
			<option value="600"<?php selected( $timeout, 600 ); ?>><?php printf( __( '%s minutes', 'woocommerce-exporter' ), 10 ); ?></option>
			<option value="1800"<?php selected( $timeout, 1800 ); ?>><?php printf( __( '%s minutes', 'woocommerce-exporter' ), 30 ); ?></option>
			<option value="3600"<?php selected( $timeout, 3600 ); ?>><?php printf( __( '%s hour', 'woocommerce-exporter' ), 1 ); ?></option>
			<option value="0"<?php selected( $timeout, 0 ); ?>><?php _e( 'Unlimited', 'woocommerce-exporter' ); ?></option>
		</select>
		<p class="description"><?php _e( 'Script timeout defines how long Store Exporter is \'allowed\' to process your export file, once the time limit is reached the export process halts.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<?php } ?>
<tr>
	<th>
		<?php _e( 'Header formatting', 'woocommerce-exporter' ); ?>
	</th>
	<td>
		<ul style="margin-top:0.2em;">
			<li><label><input type="radio" name="header_formatting" value="1"<?php checked( $header_formatting, '1' ); ?> />&nbsp;<?php _e( 'Include export field column headers', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="radio" name="header_formatting" value="0"<?php checked( $header_formatting, '0' ); ?> />&nbsp;<?php _e( 'Do not include export field column headers', 'woocommerce-exporter' ); ?></label></li>
		</ul>
		<p class="description"><?php _e( 'Choose the header format that suits your spreadsheet software (e.g. Excel, OpenOffice, etc.). This rule applies to CSV, TSV, XLS and XLSX export types.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<tr>
	<th>
		<label for="bom"><?php _e( 'Add BOM character', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<select id="bom" name="bom">
			<option value="1"<?php selected( $bom, 1 ); ?>><?php _e( 'Yes', 'woocommerce-exporter' ); ?></option>
			<option value="0"<?php selected( $bom, 0 ); ?>><?php _e( 'No', 'woocommerce-exporter' ); ?></option>
		</select>
		<p class="description"><?php _e( 'Mark the CSV file as UTF8 by adding a byte order mark (BOM) to the export, useful for non-English character sets.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
	ob_end_flush();

}

function woo_ce_export_settings_general_advanced_settings_extend() {

	// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
	if( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
?>
<li><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'rebuild_tm_epo_fields', '_wpnonce' => wp_create_nonce( 'woo_ce_rebuild_tm_epo_fields' ) ) ) ); ?>"><?php _e( 'Rebuild WooCommerce TM Extra Product Options fields', 'woocommerce-exporter' ); ?></a></li>
<?php
	}

}

function woo_ce_export_settings_csv() {

	$delimiter = woo_ce_get_option( 'delimiter', ',' );
	$category_separator = woo_ce_get_option( 'category_separator', '|' );
	$line_ending_formatting = woo_ce_get_option( 'line_ending_formatting', 'windows' );

	ob_start(); ?>
<tr>
	<th>
		<label for="delimiter"><?php _e( 'Field delimiter', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<input type="text" size="3" id="delimiter" name="delimiter" value="<?php echo esc_attr( $delimiter ); ?>" maxlength="5" class="text" />
		<p class="description"><?php _e( 'The field delimiter is the character separating each cell in your CSV. This is typically the \',\' (comma) character. To use the TAB character as the delimiter enter <code>TAB</code>.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<tr>
	<th>
		<label for="category_separator"><?php _e( 'Category separator', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<input type="text" size="3" id="category_separator" name="category_separator" value="<?php echo esc_attr( $category_separator ); ?>" maxlength="5" class="text" />
		<p class="description"><?php _e( 'The Product Category separator allows you to assign individual Products to multiple Product Categories/Tags/Images at a time. It is suggested to use the \'|\' (vertical pipe) character or \'LF\' for line breaks between each item. For instance: <code>Clothing|Mens|Shirts</code>.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<tr>
	<th>
		<label for="line_ending"><?php _e( 'Line ending formatting', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<select id="line_ending" name="line_ending">
			<option value="windows"<?php selected( $line_ending_formatting, 'windows' ); ?>><?php _e( 'Windows / DOS (CRLF)', 'woocommerce-exporter' ); ?></option>
			<option value="mac"<?php selected( $line_ending_formatting, 'mac' ); ?>><?php _e( 'Mac (LF)' ,'woocommerce-exporter' ); ?></option>
			<option value="unix"<?php selected( $line_ending_formatting, 'unix' ); ?>><?php _e( 'Unix (CR)', 'woocommerce-exporter' ); ?></option>
		</select>
		<p class="description"><?php _e( 'Choose the line ending formatting that suits the Operating System you plan to use the export file with (e.g. a Windows desktop, Mac laptop, etc.). Default is Windows.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<?php
	ob_end_flush();

}

// Returns the HTML template for the CRON, scheduled exports, Secret Export Key and Export Trigger options for the Settings screen
function woo_ce_export_settings_extend() {

	// XML settings
	$xml_attribute_url = woo_ce_get_option( 'xml_attribute_url', 1 );
	$xml_attribute_title = woo_ce_get_option( 'xml_attribute_title', 1 );
	$xml_attribute_date = woo_ce_get_option( 'xml_attribute_date', 1 );
	$xml_attribute_time = woo_ce_get_option( 'xml_attribute_time', 0 );
	$xml_attribute_export = woo_ce_get_option( 'xml_attribute_export', 1 );
	$xml_attribute_orderby = woo_ce_get_option( 'xml_attribute_orderby', 0 );
	$xml_attribute_order = woo_ce_get_option( 'xml_attribute_order', 0 );
	$xml_attribute_limit = woo_ce_get_option( 'xml_attribute_limit', 0 );
	$xml_attribute_offset = woo_ce_get_option( 'xml_attribute_offset', 0 );

	// RSS settings
	$rss_title = woo_ce_get_option( 'rss_title', '' );
	$rss_link = woo_ce_get_option( 'rss_link', '' );
	$rss_description = woo_ce_get_option( 'rss_description', '' );

	// Scheduled exports
	$enable_auto = woo_ce_get_option( 'enable_auto', 0 );

	// Export templates
	$args = array(
		'post_status' => 'publish'
	);
	$export_templates = woo_ce_get_export_templates( $args );

	// CRON exports
	$enable_cron = woo_ce_get_option( 'enable_cron', 0 );
	$secret_key = woo_ce_get_option( 'secret_key', '' );
	$cron_fields = woo_ce_get_option( 'cron_fields', 'all' );
	$cron_export_template = woo_ce_get_option( 'cron_export_template', 'all' );

	// Orders Screen
	$order_actions_csv = woo_ce_get_option( 'order_actions_csv', 1 );
	$order_actions_tsv = woo_ce_get_option( 'order_actions_tsv', 1 );
	$order_actions_xls = woo_ce_get_option( 'order_actions_xls', 1 );
	$order_actions_xlsx = woo_ce_get_option( 'order_actions_xlsx', 1 );
	$order_actions_xml = woo_ce_get_option( 'order_actions_xml', 0 );
	$order_actions_fields = woo_ce_get_option( 'order_actions_fields', 'all' );
	$args = array(
		'post_status' => 'publish'
	);
	$export_templates = woo_ce_get_export_templates( $args );
	$order_actions_order_items_formatting = woo_ce_get_option( 'order_actions_order_items_formatting', 'unique' );
	$order_actions_export_template = woo_ce_get_option( 'order_actions_export_template', 'all' );

	// Export Triggers
	$enable_trigger_new_order = woo_ce_get_option( 'enable_trigger_new_order', 0 );
	$export_formats = woo_ce_get_export_formats();
	$trigger_new_order_format = woo_ce_get_option( 'trigger_new_order_format', 'csv' );
	$trigger_new_order_method = woo_ce_get_option( 'trigger_new_order_method', 'archive' );
	$trigger_new_order_method_email_to = woo_ce_get_option( 'trigger_new_order_method_email_to', '' );
	$trigger_new_order_method_email_subject = woo_ce_get_option( 'trigger_new_order_method_email_subject', '' );
	$trigger_new_order_items_formatting = woo_ce_get_option( 'trigger_new_order_items_formatting', 'unique' );
	$trigger_new_order_fields = woo_ce_get_option( 'trigger_new_order_fields', 'all' );

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

	ob_start(); ?>
<tr id="xml-settings">
	<td colspan="2" style="padding:0;">
		<hr />
		<h3><div class="dashicons dashicons-media-code"></div>&nbsp;<?php _e( 'XML Settings', 'woocommerce-exporter' ); ?></h3>
	</td>
</tr>
<tr>
	<th><?php _e( 'Attribute display', 'woocommerce-exporter' ); ?></th>
	<td>
		<ul>
			<li><label><input type="checkbox" name="xml_attribute_url" value="1"<?php checked( $xml_attribute_url ); ?> /> <?php _e( 'Site Address', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="checkbox" name="xml_attribute_title" value="1"<?php checked( $xml_attribute_title ); ?> /> <?php _e( 'Site Title', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="checkbox" name="xml_attribute_date" value="1"<?php checked( $xml_attribute_date ); ?> /> <?php _e( 'Export Date', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="checkbox" name="xml_attribute_time" value="1"<?php checked( $xml_attribute_time ); ?> /> <?php _e( 'Export Time', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="checkbox" name="xml_attribute_export" value="1"<?php checked( $xml_attribute_export ); ?> /> <?php _e( 'Export Type', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="checkbox" name="xml_attribute_orderby" value="1"<?php checked( $xml_attribute_orderby ); ?> /> <?php _e( 'Export Order By', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="checkbox" name="xml_attribute_order" value="1"<?php checked( $xml_attribute_order ); ?> /> <?php _e( 'Export Order', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="checkbox" name="xml_attribute_limit" value="1"<?php checked( $xml_attribute_limit ); ?> /> <?php _e( 'Limit Volume', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="checkbox" name="xml_attribute_offset" value="1"<?php checked( $xml_attribute_offset ); ?> /> <?php _e( 'Volume Offset', 'woocommerce-exporter' ); ?></label></li>
		</ul>
		<p class="description"><?php _e( 'Control the visibility of different attributes in the XML export.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<!-- #xml-settings -->

<tr id="rss-settings">
	<td colspan="2" style="padding:0;">
		<hr />
		<h3><div class="dashicons dashicons-media-code"></div>&nbsp;<?php _e( 'RSS Settings', 'woocommerce-exporter' ); ?></h3>
	</td>
</tr>
<tr>
	<th>
		<label for="rss_title"><?php _e( 'Title element', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<input name="rss_title" type="text" id="rss_title" value="<?php echo esc_attr( $rss_title ); ?>" class="large-text" />
		<p class="description"><?php _e( 'Defines the title of the data feed (e.g. Product export for WordPress Shop).', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<tr>
	<th>
		<label for="rss_link"><?php _e( 'Link element', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<input name="rss_link" type="text" id="rss_link" value="<?php echo esc_attr( $rss_link ); ?>" class="large-text" />
		<p class="description"><?php _e( 'A link to your website, this doesn\'t have to be the location of the RSS feed.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<tr>
	<th>
		<label for="rss_description"><?php _e( 'Description element', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<input name="rss_description" type="text" id="rss_description" value="<?php echo esc_attr( $rss_description ); ?>" class="large-text" />
		<p class="description"><?php _e( 'A description of your data feed.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<!-- #rss-settings -->

<tr id="scheduled-exports">
	<td colspan="2" style="padding:0;">
		<hr />
		<h3>
			<div class="dashicons dashicons-calendar"></div>&nbsp;<?php _e( 'Scheduled Exports', 'woocommerce-exporter' ); ?>
			<a href="<?php echo esc_url( admin_url( add_query_arg( 'post_type', 'scheduled_export', 'post-new.php' ) ) ); ?>" class="add-new-h2"><?php _e( 'Add New', 'woocommerce-exporter' ); ?></a>
		</h3>
<?php if( $enable_auto == 1 ) { ?>
		<p style="font-size:0.8em;"><div class="dashicons dashicons-yes"></div>&nbsp;<strong><?php _e( 'Scheduled exports is enabled', 'woocommerce-exporter' ); ?></strong></p>
<?php } ?>
		<p class="description"><?php _e( 'Automatically generate exports and apply filters to export just what you need.<br />Adjusting options within the Scheduling sub-section will after clicking Save Changes refresh the scheduled export engine, editing filters, formats, methods, etc. will not affect the scheduling of the current scheduled export.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<tr>
	<th><label for="enable_auto"><?php _e( 'Enable scheduled exports', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<select id="enable_auto" name="enable_auto">
			<option value="1"<?php selected( $enable_auto, 1 ); ?>><?php _e( 'Yes', 'woocommerce-exporter' ); ?></option>
			<option value="0"<?php selected( $enable_auto, 0 ); ?>><?php _e( 'No', 'woocommerce-exporter' ); ?></option>
		</select>
<?php if( $enable_auto == 0 && woo_ce_get_option( 'hide_scheduled_exports_tab', 0 ) == 1 ) { ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'restore_scheduled_exports_tab', '_wpnonce' => wp_create_nonce( 'woo_ce_restore_scheduled_exports_tab' ) ) ) ); ?>"><?php _e( 'Restore Scheduled Exports tab', 'woocommerce-exporter' ); ?></a>
<?php } ?>
		<p class="description"><?php _e( 'Enabling Scheduled Exports will trigger automated exports at the intervals specified under Scheduling within each scheduled export. You can suspend individual scheduled exports by changing the Post Status.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>

<tr>
	<th>&nbsp;</th>
	<td>
		<p>
			<a href="<?php echo add_query_arg( array( 'tab' => 'scheduled_export' ) ); ?>"><?php _e( 'View Scheduled Exports', 'woocommerce-exporter' ); ?></a>
		</p>
	</td>
</tr>

<tr id="cron-exports">
	<td colspan="2" style="padding:0;">
		<hr />
		<h3><div class="dashicons dashicons-clock"></div>&nbsp;<?php _e( 'CRON Exports', 'woocommerce-exporter' ); ?></h3>
<?php if( $enable_cron == 1 ) { ?>
		<p style="font-size:0.8em;"><div class="dashicons dashicons-yes"></div>&nbsp;<strong><?php _e( 'CRON Exports is enabled', 'woocommerce-exporter' ); ?></strong></p>
<?php } ?>
		<p class="description"><?php printf( __( 'Store Exporter Deluxe supports exporting via a command line request, to do this you need to prepare a specific URL and pass it the following required inline parameters. For sample CRON requests and supported arguments consult our <a href="%s" target="_blank">online documentation</a>.', 'woocommerce-exporter' ), $troubleshooting_url ); ?></p>
	</td>
</tr>
<tr>
	<th><label for="enable_cron"><?php _e( 'Enable CRON', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<select id="enable_cron" name="enable_cron">
			<option value="1"<?php selected( $enable_cron, 1 ); ?>><?php _e( 'Yes', 'woocommerce-exporter' ); ?></option>
			<option value="0"<?php selected( $enable_cron, 0 ); ?>><?php _e( 'No', 'woocommerce-exporter' ); ?></option>
		</select>
		<p class="description"><?php _e( 'Enabling CRON allows developers to schedule automated exports and connect with Store Exporter Deluxe remotely.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<tr>
	<th>
		<label for="secret_key"><?php _e( 'Export secret key', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
		<input name="secret_key" type="text" id="secret_key" value="<?php echo esc_attr( $secret_key ); ?>" class="large-text code" />
		<p class="description"><?php _e( 'This secret key (can be left empty to allow unrestricted access) limits access to authorised developers who provide a matching key when working with Store Exporter Deluxe.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<tr>
	<th><?php _e( 'Export fields', 'woocommerce-exporter' ); ?></th>
	<td>
		<ul style="margin-top:0.2em;">
			<li>
				<label><input type="radio" name="cron_fields" value="all"<?php checked( $cron_fields, 'all' ); ?> /> <?php _e( 'Include all Export Fields for the requested Export Type', 'woocommerce-exporter' ); ?></label>
			</li>
			<li>
				<label><input type="radio" name="cron_fields" value="template"<?php checked( $cron_fields, 'template' ); ?><?php disabled( empty( $export_templates ), true ); ?> /> <?php _e( 'Use the saved fields preferences from the following Export Template for the requested Export Type', 'woocommerce-exporter' ); ?></label><br />
				<select id="export_template" name="cron_export_template"<?php disabled( empty( $export_templates ), true ); ?> class="select short">
<?php if( !empty( $export_templates ) ) { ?>
	<?php foreach( $export_templates as $template ) { ?>
					<option value="<?php echo $template; ?>"<?php selected( $cron_export_template, $template ); ?>><?php echo woo_ce_format_post_title( get_the_title( $template ) ); ?></option>
	<?php } ?>
<?php } else { ?>
					<option><?php _e( 'Choose a Export Template...', 'woocommerce-exporter' ); ?></option>
<?php } ?>
				</select>
			</li>
			<li>
				<label><input type="radio" name="cron_fields" value="saved"<?php checked( $cron_fields, 'saved' ); ?> /> <?php _e( 'Use the saved Export Fields preference set on the Quick Export screen for the requested Export Type', 'woocommerce-exporter' ); ?></label>
			</li>
		</ul>
		<p class="description"><?php _e( 'Control whether all known export fields are included or only checked fields from the Export Fields section on the Quick Export screen for each Export Type. Default is to include all export fields.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<!-- #cron-exports -->

<tr id="orders-screen">
	<td colspan="2" style="padding:0;">
		<hr />
		<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Orders Screen', 'woocommerce-exporter' ); ?></h3>
	</td>
</tr>
<tr>
	<th><?php _e( 'Actions display', 'woocommerce-exporter' ); ?></th>
	<td>
		<ul>
			<li><label><input type="checkbox" name="order_actions_csv" value="1"<?php checked( $order_actions_csv ); ?> /> <?php _e( 'Export to CSV', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="checkbox" name="order_actions_tsv" value="1"<?php checked( $order_actions_tsv ); ?> /> <?php _e( 'Export to TSV', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="checkbox" name="order_actions_xls" value="1"<?php checked( $order_actions_xls ); ?> /> <?php _e( 'Export to XLS', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="checkbox" name="order_actions_xlsx" value="1"<?php checked( $order_actions_xlsx ); ?> /> <?php _e( 'Export to XLSX', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="checkbox" name="order_actions_xml" value="1"<?php checked( $order_actions_xml ); ?> /> <?php _e( 'Export to XML', 'woocommerce-exporter' ); ?></label></li>
		</ul>
		<p class="description"><?php _e( 'Control the visibility of different Order actions on the WooCommerce &raquo; Orders screen.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<tr>
	<th><?php _e( 'Order items formatting', 'woocommerce-exporter' ); ?></th>
	<td>
		<ul>
			<li><label><input type="radio" name="order_actions_order_items" value="combined"<?php checked( $order_actions_order_items_formatting, 'combined' ); ?> />&nbsp;<?php _e( 'Place Order Items within a grouped single Order row', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="radio" name="order_actions_order_items" value="unique"<?php checked( $order_actions_order_items_formatting, 'unique' ); ?> />&nbsp;<?php _e( 'Place Order Items on individual cells within a single Order row', 'woocommerce-exporter' ); ?></label></li>
			<li><label><input type="radio" name="order_actions_order_items" value="individual"<?php checked( $order_actions_order_items_formatting, 'individual' ); ?> />&nbsp;<?php _e( 'Place each Order Item within their own Order row', 'woocommerce-exporter' ); ?></label></li>
		</ul>
		<p class="description"><?php _e( 'Choose how you would like Order Items to be presented within Orders from the WooCommerce &raquo; Orders screen.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<tr>
	<th><?php _e( 'Export fields', 'woocommerce-exporter' ); ?></th>
	<td>
		<ul style="margin-top:0.2em;">
			<li>
				<label><input type="radio" id="order_actions_fields" name="order_actions_fields" value="all"<?php checked( $order_actions_fields, 'all' ); ?> /> <?php _e( 'Include all fields for the requested Export Type', 'woocommerce-exporter' ); ?></label>
			</li>
			<li>
				<label><input type="radio" name="order_actions_fields" value="template"<?php checked( $order_actions_fields, 'template' ); ?><?php disabled( empty( $export_templates ), true ); ?> /> <?php _e( 'Use the saved fields preferences from the following Export Template for the requested Export Type', 'woocommerce-exporter' ); ?></label><br />
				<select id="export_template" name="order_actions_export_template"<?php disabled( empty( $export_templates ), true ); ?> class="select short">
<?php if( !empty( $export_templates ) ) { ?>
	<?php foreach( $export_templates as $template ) { ?>
					<option value="<?php echo $template; ?>"<?php selected( $order_actions_export_template, $template ); ?>><?php echo woo_ce_format_post_title( get_the_title( $template ) ); ?></option>
	<?php } ?>
<?php } else { ?>
					<option><?php _e( 'Choose a Export Template...', 'woocommerce-exporter' ); ?></option>
<?php } ?>
				</select>
			</li>
			<li>
				<label><input type="radio" name="order_actions_fields" value="saved"<?php checked( $order_actions_fields, 'saved' ); ?> /> <?php _e( 'Use the saved fields preference set on the Quick Export screen for the requested Export Type', 'woocommerce-exporter' ); ?></label>
			</li>
		</ul>
		<p class="description"><?php _e( 'Control whether all known export fields are included or only checked fields from the Export Fields section on the Quick Export screen for each Export Type. Default is to include all export fields.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<!-- #orders-screen -->

<tr id="export-triggers">
	<td colspan="2" style="padding:0;">
		<hr />
		<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'Export Triggers', 'woocommerce-exporter' ); ?></h3>
		<p class="description"><?php _e( 'Run exports on specific triggers within your WooCommerce store.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<!-- #export-triggers -->

<tr id="new-orders">
	<th><?php _e( 'New Order', 'woocommerce-exporter' ); ?></th>
	<td>
<?php if( $enable_trigger_new_order == 1 ) { ?>
		<p style="font-size:0.8em;"><div class="dashicons dashicons-yes"></div>&nbsp;<strong><?php _e( 'Export on New Order is enabled, this will run for each new Order received.', 'woocommerce-exporter' ); ?></strong></p>
<?php } ?>
		<p class="description"><?php _e( 'Trigger an export of each new Order that is generated after successful Checkout.', 'woocommerce-exporter' ); ?></p>
		<ul>

			<li>
				<p>
					<label for="enable_trigger_new_order"><?php _e( 'Enable trigger', 'woocommerce-exporter' ); ?></label><br />
					<select id="enable_trigger_new_order" name="enable_trigger_new_order">
						<option value="1"<?php selected( $enable_trigger_new_order, 1 ); ?>><?php _e( 'Yes', 'woocommerce-exporter' ); ?></option>
						<option value="0"<?php selected( $enable_trigger_new_order, 0 ); ?>><?php _e( 'No', 'woocommerce-exporter' ); ?></option>
					</select>
				</p>
				<hr />
			</li>

			<li>
				<p><label><?php _e( 'Export format', 'woocommerce-exporter' ); ?></label></p>
<?php if( !empty( $export_formats ) ) { ?>
				<ul style="margin-top:0.2em;">
	<?php foreach( $export_formats as $key => $export_format ) { ?>
					<li><label><input type="radio" name="trigger_new_order_format" value="<?php echo $key; ?>"<?php checked( $trigger_new_order_format, $key ); ?> /> <?php echo $export_format['title']; ?><?php if( !empty( $export_format['description'] ) ) { ?> <span class="description">(<?php echo $export_format['description']; ?>)</span><?php } ?></label></li>
	<?php } ?>
				</ul>
<?php } else { ?>
		<?php _e( 'No export formats were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
				<hr />
			</li>

			<li>
				<p><label for="trigger_new_order_method"><?php _e( 'Export method', 'woocommerce-exporter' ); ?></label></p>
				<select id="trigger_new_order_method" name="trigger_new_order_method">
					<option value="archive"<?php selected( $trigger_new_order_method, 'archive' ); ?>><?php echo woo_ce_format_export_method( 'archive' ); ?></option>
					<option value="email"<?php selected( $trigger_new_order_method, 'email' ); ?>><?php echo woo_ce_format_export_method( 'email' ); ?></option>
<!--
					<option value="post"<?php selected( $trigger_new_order_method, 'post' ); ?>><?php echo woo_ce_format_export_method( 'post' ); ?></option>
					<option value="ftp"<?php selected( $trigger_new_order_method, 'ftp' ); ?>><?php echo woo_ce_format_export_method( 'ftp' ); ?></option>
-->
				</select>
				<hr />
			</li>

			<li class="export_method_options">
				<p><label><?php _e( 'Export method options', 'woocommerce-exporter' ); ?></label></p>
				<div class="export-options email-options">
					<ul style="margin-top:0.2em;">
						<li>
							<label for="trigger_new_method_email_to">
								<?php _e( 'E-mail recipient', 'woocommerce-exporter' ); ?>
								<img class="help_tip" data-tip="<?php _e( 'Set the recipient of Order export trigger e-mails, multiple recipients can be added using the comma separator.<br /><br />Default is the Blog Administrator e-mail address set on the WordPress &raquo; Settings screen.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
							</label>
							<input type="text" id="trigger_new_method_email_to" name="trigger_new_method_email_to" value="<?php echo $trigger_new_order_method_email_to; ?>" class="large-text" placeholder="big.bird@sesamestreet.org,oscar@sesamestreet.org" />
						</li>
						<li>
							<label for="trigger_new_method_email_subject">
								<?php _e( 'E-mail subject', 'woocommerce-exporter' ); ?>
								<img class="help_tip" data-tip="<?php _e( 'Set the subject of scheduled export e-mails.<br /><br />Tags can be used: <code>%store_name%</code>, <code>%export_type%</code>, <code>%export_filename%</code>', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
							</label>
							<input type="text" id="trigger_new_method_email_subject" name="trigger_new_method_email_subject" value="<?php echo $trigger_new_order_method_email_subject; ?>" class="large-text" placeholder="<?php _e( 'Order export', 'woocommerce-exporter' ); ?>" />
						</li>
					</ul>
				</div>
				<!-- .email-options -->
				<div class="export-options save-options">
					<ul style="margin-top:0.2em;">
						<li><label><?php _e( 'Filepath', 'woocommerce-exporter' ); ?> <input type="text" id="" name="" value="<?php echo 111; ?>" class="large-text" placeholder="" /></label></li>
					</ul>
				</div>
				<!-- .save-options -->
				<div class="export-options archive-options">
					<p><?php _e( 'No export method options are available for this export method.', 'woocommerce-exporter' ); ?></p>
				</div>
				<hr />
			</li>
			<li>
				<p><label><?php _e( 'Order items formatting', 'woocommerce-exporter' ); ?></label></p>
				<ul style="margin-top:0.2em;">
					<li><label><input type="radio" name="trigger_new_order_order_items" value="combined"<?php checked( $trigger_new_order_items_formatting, 'combined' ); ?> />&nbsp;<?php _e( 'Place Order Items within a grouped single Order row', 'woocommerce-exporter' ); ?></label></li>
					<li><label><input type="radio" name="trigger_new_order_order_items" value="unique"<?php checked( $trigger_new_order_items_formatting, 'unique' ); ?> />&nbsp;<?php _e( 'Place Order Items on individual cells within a single Order row', 'woocommerce-exporter' ); ?></label></li>
					<li><label><input type="radio" name="trigger_new_order_order_items" value="individual"<?php checked( $trigger_new_order_items_formatting, 'individual' ); ?> />&nbsp;<?php _e( 'Place each Order Item within their own Order row', 'woocommerce-exporter' ); ?></label></li>
				</ul>
			</li>
			<li>
				<p><label><?php _e( 'Export fields', 'woocommerce-exporter' ); ?></label></p>
				<ul style="margin-top:0.2em;">
					<li>
						<label><input type="radio" id="trigger_new_order_fields" name="trigger_new_order_fields" value="all"<?php checked( $trigger_new_order_fields, 'all' ); ?> /> <?php _e( 'Include all Order Fields', 'woocommerce-exporter' ); ?></label>
					</li>
					<li>
						<label><input type="radio" name="trigger_new_order_fields" value="saved"<?php checked( $trigger_new_order_fields, 'saved' ); ?> /> <?php _e( 'Use the saved fields preference for Orders set on the Quick Export screen', 'woocommerce-exporter' ); ?></label>
					</li>
				</ul>
				<p class="description"><?php _e( 'Control whether all known export fields are included or only checked fields from the Export Fields section on the Quick Export screen for Orders. Default is to include all export fields.', 'woocommerce-exporter' ); ?></p>
			</li>

		</ul>
	</td>
</tr>
<!-- #new-orders -->

<?php
	ob_end_flush();

}

function woo_ce_export_settings_save() {

	// Strip file extension from export filename
	$export_filename = strip_tags( $_POST['export_filename'] );
	if( ( strpos( $export_filename, '.csv' ) !== false ) || ( strpos( $export_filename, '.xml' ) !== false ) || ( strpos( $export_filename, '.xls' ) !== false ) )
		$export_filename = str_replace( array( '.csv', '.xml', '.xls' ), '', $export_filename );
	woo_ce_update_option( 'export_filename', $export_filename );
	woo_ce_update_option( 'delete_file', absint( $_POST['delete_file'] ) );
	woo_ce_update_option( 'encoding', sanitize_text_field( $_POST['encoding'] ) );
	woo_ce_update_option( 'delimiter', sanitize_text_field( $_POST['delimiter'] ) );
	woo_ce_update_option( 'category_separator', sanitize_text_field( $_POST['category_separator'] ) );
	woo_ce_update_option( 'line_ending_formatting', sanitize_text_field( $_POST['line_ending'] ) );
	woo_ce_update_option( 'bom', absint( $_POST['bom'] ) );
	woo_ce_update_option( 'escape_formatting', sanitize_text_field( $_POST['escape_formatting'] ) );
	woo_ce_update_option( 'header_formatting', absint( $_POST['header_formatting'] ) );
	woo_ce_update_option( 'timeout', absint( $_POST['timeout'] ) );
	$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );
	if( $_POST['date_format'] == 'custom' && !empty( $_POST['date_format_custom'] ) ) {
		if( $date_format <> $_POST['date_format'] )
			woo_ce_update_option( 'date_format', sanitize_text_field( $_POST['date_format_custom'] ) );
	} else if( $date_format <> $_POST['date_format'] ) {
		// Update the date format on scheduled exports
		if( $scheduled_exports = woo_ce_get_scheduled_exports() ) {
			foreach( $scheduled_exports as $scheduled_export ) {
				$order_dates_from = get_post_meta( $scheduled_export, '_filter_order_dates_from', true );
				$order_dates_to = get_post_meta( $scheduled_export, '_filter_order_dates_to', true );
				// Format date to new format
				if( !empty( $order_dates_from ) )
					update_post_meta( $scheduled_export, '_filter_order_dates_from', date( sanitize_text_field( $_POST['date_format'] ), strtotime( $order_dates_from ) ) );
				if( !empty( $order_dates_to ) )
				update_post_meta( $scheduled_export, '_filter_order_dates_to', date( sanitize_text_field( $_POST['date_format'] ), strtotime( $order_dates_to ) ) );
			}
		}
		woo_ce_update_option( 'date_format', sanitize_text_field( $_POST['date_format'] ) );
	}

	// XML settings
	woo_ce_update_option( 'xml_attribute_url', ( isset( $_POST['xml_attribute_url'] ) ? absint( $_POST['xml_attribute_url'] ) : 0 ) );
	woo_ce_update_option( 'xml_attribute_title', ( isset( $_POST['xml_attribute_title'] ) ? absint( $_POST['xml_attribute_title'] ) : 0 ) );
	woo_ce_update_option( 'xml_attribute_date', ( isset( $_POST['xml_attribute_date'] ) ? absint( $_POST['xml_attribute_date'] ) : 0 ) );
	woo_ce_update_option( 'xml_attribute_time', ( isset( $_POST['xml_attribute_time'] ) ? absint( $_POST['xml_attribute_time'] ) : 0 ) );
	woo_ce_update_option( 'xml_attribute_export', ( isset( $_POST['xml_attribute_export'] ) ? absint( $_POST['xml_attribute_export'] ) : 0 ) );
	woo_ce_update_option( 'xml_attribute_orderby', ( isset( $_POST['xml_attribute_orderby'] ) ? absint( $_POST['xml_attribute_orderby'] ) : 0 ) );
	woo_ce_update_option( 'xml_attribute_order', ( isset( $_POST['xml_attribute_order'] ) ? absint( $_POST['xml_attribute_order'] ) : 0 ) );
	woo_ce_update_option( 'xml_attribute_limit', ( isset( $_POST['xml_attribute_limit'] ) ? absint( $_POST['xml_attribute_limit'] ) : 0 ) );
	woo_ce_update_option( 'xml_attribute_offset', ( isset( $_POST['xml_attribute_offset'] ) ? absint( $_POST['xml_attribute_offset'] ) : 0 ) );

	// RSS settings
	woo_ce_update_option( 'rss_title', ( isset( $_POST['rss_title'] ) ? sanitize_text_field( $_POST['rss_title'] ) : '' ) );
	woo_ce_update_option( 'rss_link', ( isset( $_POST['rss_link'] ) ? esc_url_raw( $_POST['rss_link'] ) : '' ) );
	woo_ce_update_option( 'rss_description', ( isset( $_POST['rss_description'] ) ? sanitize_text_field( $_POST['rss_description'] ) : '' ) );

	// Scheduled export settings
	$enable_auto = absint( $_POST['enable_auto'] );
	if( 
		woo_ce_get_option( 'enable_auto', 0 ) <> $enable_auto
	) {
		// Save these fields before we re-load the WP-CRON schedule
		woo_ce_update_option( 'enable_auto', $enable_auto );
		if( $enable_auto == 0 ) {
			woo_ce_cron_activation( true );
		}
	}

	// CRON settings
	$enable_cron = absint( $_POST['enable_cron'] );
	// Display additional notice if Enabled CRON is enabled/disabled
	if( woo_ce_get_option( 'enable_cron', 0 ) <> $enable_cron ) {
		$message = sprintf( __( 'CRON support has been %s.', 'woocommerce-exporter' ), ( ( $enable_cron == 1 ) ? __( 'enabled', 'woocommerce-exporter' ) : __( 'disabled', 'woocommerce-exporter' ) ) );
		woo_cd_admin_notice( $message );
	}
	woo_ce_update_option( 'enable_cron', $enable_cron );
	woo_ce_update_option( 'secret_key', sanitize_text_field( $_POST['secret_key'] ) );
	woo_ce_update_option( 'cron_fields', sanitize_text_field( $_POST['cron_fields'] ) );
	woo_ce_update_option( 'cron_export_template', ( isset( $_POST['cron_export_template'] ) ? sanitize_text_field( $_POST['cron_export_template'] ) : false ) );

	// Orders Screen
	woo_ce_update_option( 'order_actions_csv', ( isset( $_POST['order_actions_csv'] ) ? absint( $_POST['order_actions_csv'] ) : 0 ) );
	woo_ce_update_option( 'order_actions_tsv', ( isset( $_POST['order_actions_tsv'] ) ? absint( $_POST['order_actions_tsv'] ) : 0 ) );
	woo_ce_update_option( 'order_actions_xls', ( isset( $_POST['order_actions_xls'] ) ? absint( $_POST['order_actions_xls'] ) : 0 ) );
	woo_ce_update_option( 'order_actions_xlsx', ( isset( $_POST['order_actions_xlsx'] ) ? absint( $_POST['order_actions_xlsx'] ) : 0 ) );
	woo_ce_update_option( 'order_actions_xml', ( isset( $_POST['order_actions_xml'] ) ? absint( $_POST['order_actions_xml'] ) : 0 ) );
	woo_ce_update_option( 'order_actions_fields', sanitize_text_field( $_POST['order_actions_fields'] ) );
	woo_ce_update_option( 'order_actions_order_items_formatting', ( isset( $_POST['order_actions_order_items'] ) ? sanitize_text_field( $_POST['order_actions_order_items'] ) : false ) );
	woo_ce_update_option( 'order_actions_export_template', ( isset( $_POST['order_actions_export_template'] ) ? sanitize_text_field( $_POST['order_actions_export_template'] ) : false ) );

	// Export Triggers
	woo_ce_update_option( 'enable_trigger_new_order', ( isset( $_POST['enable_trigger_new_order'] ) ? absint( $_POST['enable_trigger_new_order'] ) : 0 ) );
	woo_ce_update_option( 'trigger_new_order_format', sanitize_text_field( $_POST['trigger_new_order_format'] ) );
	woo_ce_update_option( 'trigger_new_order_method', sanitize_text_field( $_POST['trigger_new_order_method'] ) );
	woo_ce_update_option( 'trigger_new_order_method_email_to', sanitize_text_field( $_POST['trigger_new_method_email_to'] ) );
	woo_ce_update_option( 'trigger_new_order_method_email_subject', sanitize_text_field( $_POST['trigger_new_method_email_subject'] ) );
	woo_ce_update_option( 'trigger_new_order_items_formatting', ( isset( $_POST['order_actions_order_items'] ) ? sanitize_text_field( $_POST['trigger_new_order_order_items'] ) : false ) );
	woo_ce_update_option( 'trigger_new_order_fields', sanitize_text_field( $_POST['trigger_new_order_fields'] ) );

	// Allow Plugin/Theme authors to save custom Setting options
	do_action( 'woo_ce_extend_export_settings_save' );

	$message = __( 'Changes have been saved.', 'woocommerce-exporter' );
	woo_cd_admin_notice( $message );

}
?>