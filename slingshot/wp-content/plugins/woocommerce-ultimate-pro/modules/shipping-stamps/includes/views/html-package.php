<p><?php _e( 'Enter the weight and dimensions for the package being shipped. Dimensions are optional, but may be required for more accurate rating.', 'ultimatewoo-pro' ); ?></p>

<table class="form-table">
	<tr>
		<th><label><?php _e( 'Package type', 'ultimatewoo-pro' ); ?></label></th>
		<td>
			<select name="stamps_package_type">
				<option value=""><?php _e( 'Any (return all options)', 'ultimatewoo-pro' ); ?></option>
				<?php
				foreach ( $this->package_types as $package_type => $package_description ) {
					echo '<option value="' . esc_attr( $package_type ) . '">' . esc_html( $package_type ) . '</option>';
				}
				?>
			</select> <span class="description"></span>
		</td>
	</tr>
	<tr>
		<th><label><?php _e( 'Ship date', 'ultimatewoo-pro' ); ?></label></th>
		<td><input type="text" value="<?php echo esc_attr( $ship_date ); ?>" name="stamps_package_date" class="stamps-date-picker" /></td>
	</tr>
	<tr>
		<th><label><?php echo __( 'Weight', 'ultimatewoo-pro' ) . ' (' . esc_html( get_option( 'woocommerce_weight_unit' ) ) . ')'; ?></label></th>
		<td><input type="text" value="<?php echo esc_attr( $total_weight ); ?>" name="stamps_package_weight" /></td>
	</tr>
	<tr>
		<th><label><?php _e( 'Value', 'ultimatewoo-pro' ); ?></label></th>
		<td><input type="text" value="<?php echo esc_attr( $total_cost ); ?>" name="stamps_package_value" /></td>
	</tr>
	<tr>
		<th><label><?php echo __( 'Length', 'ultimatewoo-pro' ) . ' (' . esc_html( get_option( 'woocommerce_dimension_unit' ) ) . ')'; ?></label></th>
		<td>
			<input type="text" name="stamps_package_length" />
		</td>
	</tr>
	<tr>
		<th><label><?php echo __( 'Width', 'ultimatewoo-pro' ) . ' (' . esc_html( get_option( 'woocommerce_dimension_unit' ) ) . ')'; ?></label></th>
		<td>
			<input type="text" name="stamps_package_width" />
		</td>
	</tr>
	<tr>
		<th><label><?php echo __( 'Height', 'ultimatewoo-pro' ) . ' (' . esc_html( get_option( 'woocommerce_dimension_unit' ) ) . ')'; ?></label></th>
		<td>
			<input type="text" name="stamps_package_height" />
		</td>
	</tr>
</table>

<p><button type="submit" class="button button-primary stamps-action" data-stamps_action="get_rates"><?php _e( 'Get rates', 'ultimatewoo-pro' ); ?></button></p>