<?php
function woo_ce_export_options_export_format() {

	$export_formats = woo_ce_get_export_formats();
	$type = woo_ce_get_option( 'export_format', 'csv' );

	ob_start(); ?>
<tr>
	<th>
		<label><?php _e( 'Export format', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
<?php if( !empty( $export_formats ) ) { ?>
	<?php foreach( $export_formats as $key => $export_format ) { ?>
		<label><input type="radio" name="export_format" value="<?php echo $key; ?>"<?php checked( $type, $key ); ?> /> <?php echo $export_format['title']; ?><?php if( !empty( $export_format['description'] ) ) { ?> <span class="description">(<?php echo $export_format['description']; ?>)</span><?php } ?></label><br />
	<?php } ?>
<?php } else { ?>
		<?php _e( 'No export formats were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		<p class="description"><?php _e( 'Adjust the export format to generate different export file formats.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
	ob_end_flush();

}

function woo_ce_export_options_export_template() {

	$args = array(
		'post_status' => 'publish'
	);
	$export_templates = woo_ce_get_export_templates( $args );

	ob_start(); ?>
<tr id="export-template">
	<th>
		<label><?php _e( 'Export template', 'woocommerce-exporter' ); ?></label>
	</th>
	<td>
<?php if( !empty( $export_templates ) ) { ?>
		<select id="export_template" name="export_template"<?php disabled( empty( $export_templates ), true ); ?> class="select short">
			<option><?php _e( 'Choose a Export Template...', 'woocommerce-exporter' ); ?></option>
	<?php foreach( $export_templates as $template ) { ?>
			<option value="<?php echo $template; ?>"><?php echo woo_ce_format_post_title( get_the_title( $template ) ); ?></option>
	<?php } ?>
		</select>
		<img src="<?php echo WOO_CD_PLUGINPATH; ?>/templates/admin/images/loading.gif" class="loading" />
<?php } else { ?>
		<?php printf( __( 'No export templates were found, <a href="%s">create an export template</a>.', 'woocommerce-exporter' ), add_query_arg( array( 'tab' => 'export_template' ) ) ); ?>
<?php } ?>
	</td>
</tr>
<?php
	ob_end_flush();

}

function woo_ce_export_options_troubleshooting() {

	ob_start(); ?>
<tr>
	<th>&nbsp;</th>
	<td>
		<p class="description">
			<?php _e( 'Having difficulty downloading your exports in one go? Use our batch export function - Limit Volume and Volume Offset - to create smaller exports.', 'woocommerce-exporter' ); ?><br />
			<?php _e( 'Set the first text field (Volume limit) to the number of records to export each batch (e.g. 200), set the second field (Volume offset) to the starting record (e.g. 0). After each successful export increment only the Volume offset field (e.g. 201, 401, 601, 801, etc.) to export the next batch of records.', 'woocommerce-exporter' ); ?>
		</p>
	</td>
</tr>
<?php
	ob_end_flush();

}



function woo_ce_export_options_limit_volume() {

	$limit_volume = woo_ce_get_option( 'limit_volume' );

	ob_start(); ?>
<tr>
	<th><label for="limit_volume"><?php _e( 'Limit volume', 'woocommerce-exporter' ); ?></label></th>
	<td>
		<input type="text" size="3" id="limit_volume" name="limit_volume" value="<?php echo esc_attr( $limit_volume ); ?>" size="5" class="text" title="<?php _e( 'Limit volume', 'woocommerce-exporter' ); ?>" />
		<p class="description"><?php _e( 'Limit the number of records to be exported. By default this is not used and is left empty.', 'woocommerce-exporter' ); ?></p>
	</td>
</tr>
<?php
	ob_end_flush();

}

function woo_ce_export_options_volume_offset() {

	$offset = woo_ce_get_option( 'offset' );

	ob_start(); ?>
			<tr>
				<th><label for="offset"><?php _e( 'Volume offset', 'woocommerce-exporter' ); ?></label></th>
				<td>
					<input type="text" size="3" id="offset" name="offset" value="<?php echo esc_attr( $offset ); ?>" size="5" class="text" title="<?php _e( 'Volume offset', 'woocommerce-exporter' ); ?>" />
					<p class="description"><?php _e( 'Set the number of records to be skipped in this export. By default this is not used and is left empty.', 'woocommerce-exporter' ); ?></p>
				</td>
			</tr>
<?php
	ob_end_flush();

}
?>