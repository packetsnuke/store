<div class="options_group">
	<p class="form-field">
		<label for="import_orders" class="inline">
			<?php _e('Import Existing Orders', 'ultimatewoo-pro'); ?>
		</label>
		<input type="hidden" name="meta[import_orders]" value="no" />
		<input type="checkbox" name="meta[import_orders]" id="import_orders" value="yes" <?php if (isset($email->meta['import_orders']) && $email->meta['import_orders'] == 'yes') echo 'checked'; ?> />
		<span class="description"><?php _e('Import existing orders that match this email criteria', 'ultimatewoo-pro'); ?></span>
	</p>
</div>
