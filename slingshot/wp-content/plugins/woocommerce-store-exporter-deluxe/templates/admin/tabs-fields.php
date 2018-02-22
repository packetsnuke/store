<div id="poststuff" class="field-editor">
<?php if( apply_filters( 'woo_ce_field_editor_save_prompt', true ) ) { ?>
	<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'export' ), 'admin.php' ) ); ?>#export-<?php echo $export_type; ?>" class="button confirm-button" data-confirm="<?php _e( 'The changes you made will be lost if you navigate away from this page before saving.', 'woocommerce-exporter' ); ?>"><?php echo __( 'Return to Quick Export', 'woocommerce-exporter' ); ?></a>
<?php } else { ?>
	<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'export' ), 'admin.php' ) ); ?>#export-<?php echo $export_type; ?>" class="button"><?php echo __( 'Return to Quick Export', 'woocommerce-exporter' ); ?></a>
<?php } ?>
<?php if( $fields ) { ?>
	<form method="post" id="postform">
		<h3><?php _e( 'Field Editor', 'woocommerce-exporter' ); ?></h3>
		<p><?php _e( 'Customise the field labels for this export type by filling in the fields, an empty field label will revert to the default Store Exporter field label at export time. Setting the Hidden checkbox will hide that export field from the Quick Export screen.', 'woocommerce-exporter' ); ?></p>
		<div id="field-editor">
			<div class="inside">
				<table class="widefat">
					<thead>

						<tr>
							<th><?php _e( 'Field slug', 'woocommerce-exporter' ); ?></th>
							<th><?php _e( 'Field label', 'woocommerce-exporter' ); ?> (<a href="javascript:void(0)" class="fields-clearall"><?php _e( 'Clear All', 'woocommerce-exporter' ); ?></a>)</th>
							<th><?php _e( 'Hidden', 'woocommerce-exporter' ); ?></th>
						</tr>

					</thead>
					<tbody>

	<?php foreach( $fields as $field ) { ?>
						<tr>
							<th scope="row"><label for="<?php echo $export_type; ?>-<?php echo $field['name']; ?>"><?php echo $field['name']; ?></label></th>
							<td class="align-left">
								<input type="text" id="<?php echo $export_type; ?>-<?php echo $field['name']; ?>" name="fields[<?php echo $field['name']; ?>]" title="<?php echo $field['name']; ?>" placeholder="<?php echo $field['label']; ?>" value="<?php if( isset( $labels[$field['name']] ) ) { echo $labels[$field['name']]; } ?>" class="regular-text all-options" />
							</td>
							<td><input type="checkbox" name="hidden[<?php echo $field['name']; ?>] value="1"<?php checked( ( isset( $field['hidden'] ) ? $field['hidden'] : 0 ), 1 ); ?> /></td>
						</tr>

	<?php } ?>
					</tbody>
				</table>
				<!-- .form-table -->
				<p class="submit">
					<input type="submit" value="<?php _e( 'Save Changes', 'woocommerce-exporter' ); ?> " class="button-primary" />
				</p>
				<input type="hidden" name="action" value="save-fields" />
				<?php wp_nonce_field( 'save_fields', 'woo_ce_save_fields' ); ?>
				<input type="hidden" name="type" value="<?php echo esc_attr( $export_type ); ?>" />
			</div>
			<!-- .inside -->
		</div>
		<!-- #field-editor -->
	</form>
<?php } ?>
</div>
<!-- #poststuff -->