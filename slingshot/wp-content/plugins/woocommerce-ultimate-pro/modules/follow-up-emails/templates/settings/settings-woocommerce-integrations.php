<h3><?php _e('WooCommerce Settings', 'ultimatewoo-pro'); ?></h3>

<h4><?php _e('Remove WooCommerce Email Styles', 'ultimatewoo-pro'); ?></h4>

<p><?php _e('You can easily remove WooCommerce email styles to quickly be able to add full HTML to your emails directly in the email editor. Simply check this box, and the default WooCommerce styling will be removed from the emails you send via Follow-up Emails. Conversely, you can create your own templates and choose them instead of the default WooCommerce template.', 'ultimatewoo-pro'); ?></p>

<table class="form-table">
	<tr>
		<th>
			<label for="disable_email_wrapping">
				<input type="checkbox" name="disable_email_wrapping" id="disable_email_wrapping" value="1" <?php checked(1, get_option('fue_disable_wrapping')); ?> />
				<?php _e('Click here to disable the wrapping of styles in the WooCommerce email templates.', 'ultimatewoo-pro'); ?>
			</label>
		</th>
	</tr>
</table>

<hr>

<h3><?php _e('Abandoned Cart Settings', 'ultimatewoo-pro'); ?></h3>

<h4><?php _e('Cart Conversion Time', 'ultimatewoo-pro'); ?></h4>

<p><?php printf(__('Record cart conversions up to %s days after an email has been sent.', 'ultimatewoo-pro'), '<input type="text" size="3" name="wc_conversion_days" id="wc_conversion_days" placeholder="14" value="'. get_option('fue_wc_conversion_days', 14) .'" />' ); ?></p></table>

<h4><?php _e('Set Cart as Abandoned After', 'ultimatewoo-pro'); ?></h4>

<p><?php
	$value = get_option('fue_wc_abandoned_cart_value', 3);
	$unit  = get_option('fue_wc_abandoned_cart_unit', 'hours');
	printf(
		__('Carts older than %s %s are to be considered as abandoned.', 'ultimatewoo-pro'),
		'<input type="text" size="3" name="wc_abandoned_cart_value" id="wc_abandoned_cart_value" placeholder="1" value="'. $value .'" />',
		'<select name="wc_abandoned_cart_unit" id="wc_abandoned_cart_unit" style="vertical-align: top;">
			<option value="minutes" '. selected('minutes', $unit, false) .'>'. __('minutes', 'ultimatewoo-pro') .'</option>
			<option value="hours" '. selected('hours', $unit, false) .'>'. __('hours', 'ultimatewoo-pro') .'</option>
			<option value="days" '. selected('days', $unit, false) .'>'. __('days', 'ultimatewoo-pro') .'</option>
		</select>'
	);
	?>
</p>

<hr>