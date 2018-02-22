<div class="wc-stamps-customs-item">
	<table class="form-table">
		<tr>
			<th><label><?php _e( 'Description', 'ultimatewoo-pro' ); ?></label></th>
			<td><input type="text" name="stamps_customs_item_description[]" value="<?php echo esc_attr( empty( $description ) ? '' : $description ); ?>" maxlength="60" /></td>
		</tr>
		<tr>
			<th><label><?php _e( 'Quantity', 'ultimatewoo-pro' ); ?></label></th>
			<td><input type="number" name="stamps_customs_item_quantity[]" step="1" min="1" value="<?php echo esc_attr( empty( $qty ) ? '1' : $qty ); ?>" /></td>
		</tr>
		<tr>
			<th><label><?php _e( 'Value ($)', 'ultimatewoo-pro' ); ?></label></th>
			<td><input type="number" name="stamps_customs_item_value[]" step="0.01" min="0.01" value="<?php echo esc_attr( empty( $value ) ? '0' : $value ); ?>" /></td>
		</tr>
		<tr>
			<th><label><?php _e( 'Weight (lbs)', 'ultimatewoo-pro' ); ?></label></th>
			<td><input type="number" name="stamps_customs_item_weight[]" step="0.01" min="0.01" value="<?php echo esc_attr( empty( $weight ) ? '0' : $weight ); ?>" /></td>
		</tr>
		<tr>
			<th><label><?php _e( 'HS Tariff', 'ultimatewoo-pro' ); ?></label></th>
			<td><input type="text" name="stamps_customs_item_hs_tariff[]" placeholder="<?php _e( 'optional', 'ultimatewoo-pro' ); ?>" />
		</tr>
		<tr>
			<th><label><?php _e( 'Country (code) of origin', 'ultimatewoo-pro' ); ?></label></th>
			<td><input type="text" name="stamps_customs_item_origin[]" size="2" maxlength="2" placeholder="<?php _e( 'optional', 'ultimatewoo-pro' ); ?>"  />
		</tr>
	</table>
	<a href="#" class="wc-stamps-customs-remove-line"><?php _e( 'Remove line', 'ultimatewoo-pro' ); ?></a>
</div>