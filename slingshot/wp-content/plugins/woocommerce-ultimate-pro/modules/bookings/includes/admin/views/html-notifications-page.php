<div class="wrap woocommerce">
	<h2><?php _e( 'Send Notification', 'ultimatewoo-pro' ); ?></h2>

	<p><?php echo sprintf( __( 'You may send an email notification to all customers who have a %sfuture%s booking for a particular product. This will use the default template specified under %sWooCommerce > Settings > Emails%s.', 'ultimatewoo-pro' ), '<strong>', '</strong>', '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=email' ) ) . '">', '</a>' ); ?></p>

	<form method="POST">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="notification_product_id"><?php _e( 'Booking Product', 'ultimatewoo-pro' ); ?></label>
					</th>
					<td>
						<select id="notification_product_id" name="notification_product_id">
							<option value=""><?php _e( 'Select a booking product...', 'ultimatewoo-pro' ); ?></option>
							<?php foreach ( $booking_products as $product ) : ?>
								<option value="<?php echo $product->get_id(); ?>"><?php echo $product->get_title(); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="notification_subject"><?php _e( 'Subject', 'ultimatewoo-pro' ); ?></label>
					</th>
					<td>
						<input type="text" placeholder="<?php _e( 'Email subject', 'ultimatewoo-pro' ); ?>" name="notification_subject" id="notification_subject" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="notification_message"><?php _e( 'Message', 'ultimatewoo-pro' ); ?></label>
					</th>
					<td>
						<textarea id="notification_message" name="notification_message" class="large-text code" placeholder="<?php _e( 'The message you wish to send', 'ultimatewoo-pro' ); ?>"></textarea>
						<span class="description"><?php _e( 'The following tags can be inserted in your message/subject and will be replaced dynamically' , 'ultimatewoo-pro' ); ?>: <code>{product_title} {order_date} {order_number} {customer_name} {customer_first_name} {customer_last_name}</code></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'Attachment', 'ultimatewoo-pro' ); ?>
					</th>
					<td>
						<label><input type="checkbox" name="notification_ics" id="notification_ics" /> <?php _e( 'Attach <code>.ics</code> file', 'ultimatewoo-pro' ); ?></label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">&nbsp;</th>
					<td>
						<input type="submit" name="send" class="button-primary" value="<?php _e( 'Send Notification', 'ultimatewoo-pro' ); ?>" />
						<?php wp_nonce_field( 'send_booking_notification' ); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>
