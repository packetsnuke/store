<div class="options_group">
	<?php if ( $email->type == 'storewide' ): ?>
		<p class="form-field">
			<label for="always_send">
				<?php _e('Always Send', 'ultimatewoo-pro'); ?>
			</label>
			<input type="hidden" name="always_send" id="always_send_off" value="0" />
			<input type="checkbox" class="checkbox" name="always_send" id="always_send" value="1" <?php if ($email->always_send == 1) echo 'checked'; ?> />
			<span class="description"><?php _e('Always send this email, regardless of other initial rules. Use carefully, as this could result in multiple emails being sent per order.', 'ultimatewoo-pro'); ?></span>
		</p>
	<?php else: ?>
		<input type="hidden" name="always_send" id="always_send_off" value="1" />
	<?php endif; ?>

	<?php if ( ! in_array( $email->type, array( 'signup', 'manual' ) ) ): ?>
		<p class="form-field">
			<label for="meta_one_time">
				<?php _e('Send once per customer', 'ultimatewoo-pro'); ?>
			</label>
			<input type="hidden" name="meta[one_time]" id="meta_one_time_off" value="no" />
			<input type="checkbox" class="checkbox" name="meta[one_time]" id="meta_one_time" value="yes" <?php if (isset($email->meta['one_time']) && $email->meta['one_time'] == 'yes') echo 'checked'; ?> />
			<span class="description"><?php _e('A customer will only receive this email once, even if purchased multiple times at different dates', 'ultimatewoo-pro'); ?></span>
		</p>

		<p class="form-field">
			<label for="adjust_date">
				<?php _e('Delay existing email', 'ultimatewoo-pro'); ?>
			</label>
			<input type="hidden" name="meta[adjust_date]" id="adjust_date_off" value="no" />
			<input type="checkbox" class="checkbox" name="meta[adjust_date]" id="adjust_date" value="yes" <?php if (isset($email->meta['adjust_date']) && $email->meta['adjust_date'] == 'yes') echo 'checked'; ?> />
			<span class="description"><?php _e('If the customer already has this email scheduled, it will delay that scheduled email to the new future date.', 'ultimatewoo-pro'); ?></span>
		</p>
	<?php endif; ?>

	<?php if ( 'twitter' === $email->type ): ?>
		<p class="form-field">
			<label for="require_twitter_handle">
				<?php _e( 'Require twitter handle', 'ultimatewoo-pro'); ?>
			</label>
			<input type="hidden" name="meta[require_twitter_handle]" id="require_twitter_handle_off" value="no" />
			<input type="checkbox" class="checkbox" name="meta[require_twitter_handle]" id="require_twitter_handle" value="yes" <?php if ( isset( $email->meta['require_twitter_handle'] ) && 'yes' === $email->meta['require_twitter_handle'] ) echo 'checked'; ?> />
			<span class="description"><?php _e( 'Only tweet when a customer has twitter handle.', 'ultimatewoo-pro' ); ?></span>
		</p>
	<?php endif; ?>
</div><!-- /options_group -->

<?php do_action( 'fue_email_form_settings', $email ); ?>
