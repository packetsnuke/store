<style type="text/css">
	#submitdiv { display:none }
</style>
<?php if ( !$email->type ): ?>
<div id="fue-email-details-notice">
	<p class="meta-box-notice"><?php _e('Please set the email type first', 'ultimatewoo-pro'); ?></p>
</div>
<?php else: ?>
<div id="fue-email-details-content" class="panel-wrap email_details" style="display: none;">
	<div class="fue-tabs-back"></div>
	<ul class="email_details_tabs fue-tabs">
		<?php
		$email_details_tabs = apply_filters( 'fue_email_details_tabs', array(
			'triggers' => array(
				'label'  => __( 'Triggers', 'ultimatewoo-pro' ),
				'icon'   => 'dashicons-admin-settings',
				'target' => 'triggers_details',
				'class'  => array(),
			),
			'settings' => array(
				'label'  => __( 'Settings', 'ultimatewoo-pro' ),
				'icon'   => 'dashicons-admin-tools',
				'target' => 'settings_details',
				'class'  => array(),
			),
			'email_settings' => array(
				'label'  => __( 'From/Reply-to', 'ultimatewoo-pro' ),
				'icon'   => 'dashicons-email',
				'target' => 'email_settings',
				'class'  => array(),
			),
			'tracking' => array(
				'label'  => __( 'Google Analytics', 'ultimatewoo-pro' ),
				'icon'   => 'dashicons-chart-area',
				'target' => 'tracking_details',
				'class'  => array(),
			)
		), $email );

		// remove the triggers tab if the email is Manual
		if ( $email->type == 'manual' ) {
			unset($email_details_tabs['triggers']);
		}

		foreach ( $email_details_tabs as $key => $tab ) {
			$icon = (isset($tab['icon'])) ? $tab['icon'] : 'dashicons-admin-generic';
			?><li class="<?php echo $key; ?>_options <?php echo $key; ?>_tab <?php echo implode( ' ' , $tab['class'] ); ?>">
			<a href="#<?php echo $tab['target']; ?>" class="dashicons-before <?php echo $icon; ?>"> <?php echo esc_html( $tab['label'] ); ?></a>
			</li><?php
		}

		do_action( 'fue_write_panel_tabs' );
		?>
	</ul>

	<div id="triggers_details" class="panel fue_panel">
		<?php include FUE_TEMPLATES_DIR .'/meta-boxes/email-triggers.php'; ?>
	</div>

	<div id="settings_details" class="panel fue_panel">
		<?php include FUE_TEMPLATES_DIR .'/meta-boxes/email-settings.php'; ?>
	</div>

	<div id="email_settings" class="panel fue_panel">
		<p class="form-field">
			<label for="email_bcc">
				<?php _e('Send a copy of this email', 'ultimatewoo-pro'); ?>
			</label>
			<input type="text" name="meta[bcc]" id="email_bcc" value="<?php echo (isset($email->meta['bcc'])) ? esc_attr($email->meta['bcc']) : ''; ?>" class="regular-text" />
			<span class="description"><?php _e('All these emails will be blind carbon copied to this address', 'ultimatewoo-pro'); ?></span>
		</p>

		<p class="form-field">
			<label for="email_from_name">
				<?php _e('From/Reply-To Name', 'ultimatewoo-pro'); ?>
			</label>
			<input type="text" name="meta[from_name]" id="email_from_name" value="<?php echo (isset($email->meta['from_name'])) ? esc_attr($email->meta['from_name']) : ''; ?>" class="regular-text" />
			<span class="description"><?php _e('The name that your emails will come from and replied to', 'ultimatewoo-pro'); ?></span>
		</p>

		<p class="form-field">
			<label for="email_from">
				<?php _e('From/Reply-To Address', 'ultimatewoo-pro'); ?>
			</label>
			<input type="text" name="meta[from_address]" id="email_from" value="<?php echo (isset($email->meta['from_address'])) ? esc_attr($email->meta['from_address']) : ''; ?>" class="regular-text" />
			<span class="description"><?php _e('The email address that your emails will come from and replied to', 'ultimatewoo-pro'); ?></span>
		</p>
	</div>

	<div id="tracking_details" class="panel fue_panel">
		<p class="form-field">
			<label for="tracking_on" class="long">
				<?php _e('Add Google Analytics tracking to links', 'ultimatewoo-pro'); ?>
			</label>
			<input type="checkbox" class="checkbox" name="tracking_on" id="tracking_on" value="1" <?php checked( 1, $email->tracking_on ); ?> />
		</p>

		<p class="form-field tracking_on" style="display: none;">
			<label for="tracking"><?php _e('Link Tracking', 'ultimatewoo-pro'); ?></label>
			<input type="text" name="tracking" id="tracking" class="test-email-field" value="<?php echo esc_attr($email->tracking); ?>" placeholder="e.g. utm_campaign=Follow-up-Emails-by-75nineteen" style="width: 75%; display: block; float: none;" />
			<span class="description"><?php _e('Appended to all URLs in the Email Body.', 'ultimatewoo-pro'); ?> <a href="https://support.google.com/analytics/answer/1033867?hl=en"><?php _e( 'Get the Tracking Link.', 'ultimatewoo-pro' ); ?></a></span>
		</p>
	</div>

	<?php do_action('fue_email_form_email_details', $email); ?>

	<div class="clear"></div>
</div>
<?php endif; ?>
