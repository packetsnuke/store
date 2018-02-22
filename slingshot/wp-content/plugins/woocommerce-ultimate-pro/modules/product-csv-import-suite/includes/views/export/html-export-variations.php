<div class="tool-box">

	<h3 class="title"><?php _e('Export Product Variations CSV', 'ultimatewoo-pro'); ?></h3>
	<p><?php _e('Export your product variations using this tool. This exported CSV will be in an importable format.', 'ultimatewoo-pro'); ?></p>
	<p class="description"><?php _e('Click export to save your products variations to your computer.', 'ultimatewoo-pro'); ?></p>

	<form action="<?php echo admin_url('admin.php?page=woocommerce_csv_import_suite&action=export_variations'); ?>" method="post">

		<table class="form-table">
			<tr>
				<th>
					<label for="limit"><?php _e( 'Limit', 'ultimatewoo-pro' ); ?></label>
				</th>
				<td>
					<input type="text" name="limit" id="limit" placeholder="<?php _e('Unlimited', 'ultimatewoo-pro'); ?>" class="input-text" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="offset"><?php _e( 'Offset', 'ultimatewoo-pro' ); ?></label>
				</th>
				<td>
					<input type="text" name="offset" id="offset" placeholder="<?php _e('0', 'ultimatewoo-pro'); ?>" class="input-text" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="product"><?php _e( 'Limit to parent ID(s)', 'ultimatewoo-pro' ); ?></label>
				</th>
				<td>
					<input type="text" name="product_limit" id="product_limit" placeholder="<?php _e( 'N/A', 'ultimatewoo-pro' ); ?>" class="input-text" /> <span class="description"><?php _e( 'Comma separate IDs', 'ultimatewoo-pro' ); ?></span>
				</td>
			</tr>
			<tr>
				<th>
					<label for="columns"><?php _e( 'Columns', 'ultimatewoo-pro' ); ?></label>
				</th>
				<td>
					<select id="columns" name="columns[]" data-placeholder="<?php _e('All Columns', 'ultimatewoo-pro'); ?>" class="wc-enhanced-select" multiple="multiple">
						<?php
							foreach ( $variation_columns as $key => $column ) {
								echo '<option value="'.$key.'">'.$column.'</option>';
							}
							echo '<option value="images">'.__('Images (featured and gallery)', 'ultimatewoo-pro').'</option>';
							echo '<option value="taxonomies">'.__('Taxonomies (cat/tags/shipping-class)', 'ultimatewoo-pro').'</option>';
							echo '<option value="meta">'.__('Meta (custom fields)', 'ultimatewoo-pro').'</option>';
						?>
						</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="include_hidden_meta"><?php _e( 'Include hidden meta data', 'ultimatewoo-pro' ); ?></label>
				</th>
				<td>
					<input type="checkbox" name="include_hidden_meta" id="include_hidden_meta" class="checkbox" />
				</td>
			</tr>
		</table>

		<p class="submit"><input type="submit" class="button" value="<?php _e('Export Variations', 'ultimatewoo-pro'); ?>" /></p>

	</form>
</div>