<?php global $wc_catalog_restrictions; ?>

<h3><?php _e( 'Catalog Visibility Location', 'ultimatewoo-pro' ) ?></h3>

<table class="form-table">

	<?php if ( current_user_can( 'administrator' ) || $can_change ) : ?>
		<tr>
			<th><label><?php _e( 'Location', 'ultimatewoo-pro' ) ?></label></th>
			<td>
				<?php woocommerce_catalog_restrictions_country_input( $location ); ?>
				<span class="description"><?php __( 'The location for the user', 'ultimatewoo-pro' ); ?>.</span>
			</td>
		</tr>
	<?php endif; ?>

	<?php if ( current_user_can( 'administrator' ) ) : ?>
		<tr>
			<th><label><?php _e( 'Allow User to Change?', 'ultimatewoo-pro' ) ?></label></th>
			<td>
				<select name="can_change">

					<option value="yes" <?php selected( $can_change, 'yes' ); ?>><?php _e( 'Yes', 'ultimatewoo-pro' ) ?></option>
					<option value="no" <?php selected( $can_change, 'no' ); ?>><?php _e( 'No', 'ultimatewoo-pro' ) ?></option>

				</select>
				<span class="description"><?php __( 'The location for the user', 'ultimatewoo-pro' ); ?>.</span>
			</td>
		</tr>
	<?php endif; ?>
</table>