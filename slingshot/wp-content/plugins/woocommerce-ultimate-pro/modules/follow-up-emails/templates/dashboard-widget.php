<div class="main">
	<p>
		<select id="fue_dash_period">
			<option value="7d" <?php selected( $period, '7d' ); ?>><?php _e('Last 7 days', 'ultimatewoo-pro'); ?></option>
			<option value="30d" <?php selected( $period, '30d' ); ?>><?php _e('Last 30 days', 'ultimatewoo-pro'); ?></option>
			<option value="60d" <?php selected( $period, '60d' ); ?>><?php _e('Last 60 days', 'ultimatewoo-pro'); ?></option>
			<option value="90d" <?php selected( $period, '90d' ); ?>><?php _e('Last 90 days', 'ultimatewoo-pro'); ?></option>
			<option value="year" <?php selected( $period, 'year' ); ?>><?php _e('This year', 'ultimatewoo-pro'); ?></option>
			<option value="all_time" <?php selected( $period, 'all_time' ); ?>><?php _e('All time', 'ultimatewoo-pro'); ?></option>
		</select>
	</p>
	<ul id="fue-donuts">
		<li>
			<div
				id="sent_total_gauge"
				class="gauge"
				data-label="<?php _e('Emails Sent', 'ultimatewoo-pro'); ?>"
				data-value="<?php echo $stats['total_emails_sent']; ?>"
			/>
		</li>
		<li>
			<div
				id="sent_today_gauge"
				class="gauge"
				data-label="<?php _e('Sent Today', 'ultimatewoo-pro'); ?>"
				data-value="<?php echo $stats['emails_sent_today']; ?>"
			/>
		</li>
		<li>
			<div
				id="scheduled_emails_gauge"
				class="gauge"
				data-label="<?php _e('Scheduled', 'ultimatewoo-pro'); ?>"
				data-value="<?php echo $stats['emails_scheduled_total']; ?>"
			/>
		</li>
		<li>
			<div
				id="opens_gauge"
				class="gauge"
				data-label="<?php _e('Opens Percentage', 'ultimatewoo-pro'); ?>"
				data-value="<?php echo $stats['open_pct']; ?>"
				/>
		</li>
	</ul>
	<div class="clear"></div>

</div>