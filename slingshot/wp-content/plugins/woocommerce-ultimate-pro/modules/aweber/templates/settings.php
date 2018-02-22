<h3><?php _e( 'AWeber Newsletter Subscription Options', 'ultimatewoo-pro' ); ?></h3>

<table class="form-table">
	<tbody>
	<?php if ( $account ) { ?>
		<tr valign="top">
			<th class="titledesc" scope="row"><?php _e( 'Subscribe At Checkout', 'ultimatewoo-pro' ); ?></th>
			<td class="forminp">
				<input id="wc_aw_subscribe_checkout"
				       type="checkbox" <?php checked( $admin_options[ 'subscribe_checkout' ], '1' ); ?>
				       name="wc_aw_subscribe_checkout"/>
				<span
					class="description"><?php _e( 'Check this box if you want to present customers with a subscribe to newsletter option at checkout.', 'ultimatewoo-pro' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th class="titledesc" scope="row"><?php _e( 'Subscribe Label', 'ultimatewoo-pro' ); ?></th>
			<td class="forminp">
				<input id="wc_aw_subscribe_label" type="text" value="<?php echo $admin_options[ 'subscribe_label' ]; ?>"
				       name="wc_aw_subscribe_label"/>
				<span
					class="description"><?php _e( 'The label to display next to the subscribe checkbox at checkout.', 'ultimatewoo-pro' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th class="titledesc" scope="row"><?php _e( 'Subscribe Default Checked', 'ultimatewoo-pro' ); ?></th>
			<td class="forminp">
				<input id="wc_aw_subscribe_checked"
				       type="checkbox" <?php checked( $admin_options[ 'subscribe_checked' ], '1' ); ?>
				       name="wc_aw_subscribe_checked"/>
				<span
					class="description"><?php _e( 'Check this box if you want the subscribe checkbox at checkout to be checked by default.', 'ultimatewoo-pro' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th class="titledesc" scope="row"><?php _e( 'Subscription List', 'ultimatewoo-pro' ); ?></th>
			<td class="forminp">
				<select id="wc_aw_subscribe_id" value="<?php echo $admin_options[ 'subscribe_id' ]; ?>"
				        name="wc_aw_subscribe_id">
					<?php foreach ( $lists as $this_list ) { ?>
						<option
							value="<?php echo esc_attr( $this_list->id ); ?>"<?php selected( $this_list->id, $admin_options[ 'subscribe_id' ] ); ?>><?php echo $this_list->name; ?></option>
					<?php } ?>
				</select>
				<span
					class="description"><?php _e( 'Select the list you would like customers to subscribe to.', 'ultimatewoo-pro' ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th class="titledesc" scope="row"><?php _e( 'Deauthorize', 'ultimatewoo-pro' ); ?></th>
			<td class="forminp">
				<a href="<?php echo esc_url( add_query_arg( array( 'awauth' => 'false' ) ) ); ?>"
				   id="wc_aw_subscribe_id" class="button"><?php _e( 'Deauthorize', 'ultimatewoo-pro' ); ?></a>
				<span
					class="description"><?php _e( 'Revoke WooCommerce access to your AWeber account.', 'ultimatewoo-pro' ); ?></span>
			</td>
		</tr>
	<?php } else { ?>
		<tr valign="top">
			<th class="titledesc" scope="row"><?php _e( 'Authorize', 'ultimatewoo-pro' ); ?></th>
			<td class="forminp">
				<a href="<?php echo esc_url( add_query_arg( array( 'awauth' => 'true' ) ) ); ?>" id="wc_aw_subscribe_id"
				   class="button"><?php _e( 'Authorize', 'ultimatewoo-pro' ); ?></a>
				<span
					class="description"><?php _e( 'Authorize WooCommerce to access your AWeber account.', 'ultimatewoo-pro' ); ?></span>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>
