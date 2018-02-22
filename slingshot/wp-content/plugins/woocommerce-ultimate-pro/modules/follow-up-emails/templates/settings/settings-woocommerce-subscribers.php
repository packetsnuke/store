<hr>

<h3><?php _e('Email List Preferences', 'ultimatewoo-pro'); ?></h3>

<table class="form-table">
	<tr>
		<th colspan="2">
			<label for="enable_account_subscription">
				<input type="checkbox" name="enable_account_subscription" id="enable_account_subscription" value="1" <?php checked( 1, get_option('fue_enable_account_subscription', 0) ); ?> />
				<?php _e('Allow customers to manage their email list preferences from their account', 'ultimatewoo-pro'); ?>
			</label>
		</th>
	</tr>
	<tr class="show-if-account-subscription">
		<th scope="row">
			<label for="email_subscriptions_page_title"><?php _e('Display Title', 'ultimatewoo-pro'); ?></label>
		</th>
		<td>
			<input type="text" name="email_subscriptions_page_title" id="email_subscriptions_page_title" value="<?php echo esc_attr( get_option( 'fue_email_subscriptions_page_title', 'Email Subscriptions' ) ); ?>" />
		</td>
	</tr>
	<tr class="show-if-account-subscription">
		<th scope="row">
			<label for="email_subscriptions_button_text"><?php _e('Update Button Text', 'ultimatewoo-pro'); ?></label>
		</th>
		<td>
			<input type="text" name="email_subscriptions_button_text" id="email_subscriptions_button_text" value="<?php echo esc_attr( get_option( 'fue_email_subscriptions_button_text', 'Update Subscriptions' ) ); ?>" />
		</td>
	</tr>
</table>

<hr>

<h3><?php _e('Checkout Subscription', 'ultimatewoo-pro'); ?></h3>

<table class="form-table">
	<tr>
		<th colspan="2">
			<label for="enable_checkout_subscription">
				<input type="checkbox" name="enable_checkout_subscription" id="enable_checkout_subscription" value="1" <?php if (1 == get_option('fue_enable_checkout_subscription', 1)) echo 'checked'; ?> />
				<?php _e('Allow customers to subscribe to the newsletter on the checkout form', 'ultimatewoo-pro'); ?>
			</label>
		</th>
	</tr>
	<tr class="checkout-subscription">
		<th>
			<label for="checkout_subscription_list"><?php _e('Add subscribers to this list', 'ultimatewoo-pro'); ?></label>
		</th>
		<td>
			<select name="checkout_subscription_list" id="checkout_subscription_list">
				<?php $selected_filter = get_option('fue_checkout_subscription_list', ''); ?>
				<option value="" <?php selected( $selected_filter, '' ); ?>><?php _e('Uncategorized', 'ultimatewoo-pro'); ?></option>
				<?php foreach ( Follow_Up_Emails::instance()->newsletter->get_lists() as $list ): ?>
					<option value="<?php echo $list['id']; ?>" <?php selected( $selected_filter, $list['id'] ); ?>><?php echo $list['list_name']; ?></option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr class="checkout-subscription">
		<th>
			<label for="checkout_message">
				<?php _e('Checkout Field Label', 'ultimatewoo-pro'); ?>
			</label>
		</th>
		<td>
			<?php
			$label = get_option( 'fue_checkout_subscription_field_label', 'Send me promos and product updates.' );
			?>
			<input type="text" name="checkout_subscription_field_label" id="checkout_message" value="<?php echo esc_attr( $label ); ?>" size="50" />
		</td>
	</tr>
	<tr class="checkout-subscription">
		<th>
			<label for="checkout_subscription_default">
				<?php _e('Default Checkbox State', 'ultimatewoo-pro'); ?>
			</label>
		</th>
		<td>
			<?php
			$checked = get_option( 'fue_checkout_subscription_default', 'unchecked' );
			?>
			<select name="checkout_subscription_default" id="checkout_subscription_default">
				<option value="checked" <?php selected( $checked, 'checked' ); ?>><?php _e('Checked', 'ultimatewoo-pro'); ?></option>
				<option value="unchecked" <?php selected( $checked, 'unchecked' ); ?>><?php _e('Unchecked', 'ultimatewoo-pro'); ?></option>
			</select>
		</td>
	</tr>
</table>
<script>
	jQuery(document).ready(function($) {
		$("#enable_checkout_subscription").change(function() {
			if ( $(this).is(":checked") ) {
				$("tr.checkout-subscription").show();
			} else {
				$("tr.checkout-subscription").hide();
			}
		}).change();

		$("#enable_account_subscription").change(function() {
			if ( $(this).is(":checked") ) {
				$("tr.show-if-account-subscription").show();
			} else {
				$("tr.show-if-account-subscription").hide();
			}
		}).change();
	});
</script>