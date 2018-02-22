<div class="form-wrap">
	<form id="deposit-plan-form" method="post">
		<div class="form-field">
			<label for="plan_name"><?php esc_html_e( 'Plan Name', 'ultimatewoo-pro' ); ?>:</label>
			<input type="text" name="plan_name" id="plan_name" class="input-text" placeholder="<?php _e( 'Payment Plan', 'ultimatewoo-pro' ); ?>" value="<?php echo esc_attr( $plan_name ); ?>" />
		</div>
		<div class="form-field">
			<label for="plan_name"><?php esc_html_e( 'Plan Description', 'ultimatewoo-pro' ); ?>:</label>
			<textarea name="plan_description" id="plan_description" cols="5" rows="2" placeholder="<?php esc_attr_e( 'Describe this plan to the customer', 'ultimatewoo-pro' ); ?>" class="input-text"><?php echo esc_textarea( $plan_description ); ?></textarea>
		</div>
		<div class="form-field">
			<?php
			$interval_units = '
				<option value="day">' . esc_html__( 'Days', 'ultimatewoo-pro' ) . '</option>
				<option value="week">' . esc_html__( 'Weeks', 'ultimatewoo-pro' ) . '</option>
				<option value="month">' . esc_html__( 'Months', 'ultimatewoo-pro' ) . '</option>
				<option value="year">' . esc_html__( 'Years', 'ultimatewoo-pro' ) . '</option>
				';
			$row = '<tr>
					<td class="cell-amount"><input type="number" placeholder="0" step="0.01" min="0" name="plan_amount[]" class="plan_amount" /></td>
					<td class="cell-percent">%</td>
					<td class="cell-after">' . esc_html__( 'After', 'ultimatewoo-pro' ) . '</td>
					<td class="cell-interval-amount"><input type="number" name="plan_interval_amount[]" class="plan_interval_amount" min="0" value="1" step="1" /></td>
					<td class="cell-interval-unit"><select name="plan_interval_unit[]" class="plan_interval_unit">' . $interval_units . '</select></td>
					<td class="cell-actions"><a href="#" class="button add-row">+</a><a href="#" class="button remove-row">-</a></td>
				</tr>';
			?>
			<label><?php esc_html_e( 'Payment Schedule', 'ultimatewoo-pro' ); ?>:</label>
			<table class="wc-deposits-plan" cellspacing="0" data-row="<?php echo esc_attr( $row ); ?>">
				<thead>
					<th colspan="2"><?php esc_html_e( 'Payment Amount', 'ultimatewoo-pro' ); ?> <span class="tips" data-tip="<?php esc_attr_e( 'This is the amount (in percent) based on the full product price.', 'ultimatewoo-pro' ); ?>">[?]</span></th>
					<th colspan="3"><?php esc_html_e( 'Interval', 'ultimatewoo-pro' ); ?> <span class="tips" data-tip="<?php esc_attr_e( 'This is the interval between each payment.', 'ultimatewoo-pro' ); ?>">[?]</span></th>
					<th>&nbsp;</th>
				</thead>
				<tfoot>
					<th colspan="2"><?php esc_html_e( 'Total:', 'ultimatewoo-pro' ); ?> <span class="total_percent"></span>%</th>
					<th colspan="3"><?php esc_html_e( 'Total Duration:', 'ultimatewoo-pro' ); ?> <span class="total_duration" data-days="<?php esc_attr_e( 'Days', 'ultimatewoo-pro' ); ?>" data-months="<?php esc_attr_e( 'Months', 'ultimatewoo-pro' ); ?>" data-years="<?php esc_attr_e( 'Years', 'ultimatewoo-pro' ); ?>"></span></th>
					<th></th>
				</tfoot>
				<tbody>
					<?php foreach ( $payment_schedule as $schedule ) :
						if ( ! $editing || empty( $schedule->schedule_index ) ) {
							$index = 0;
						} else {
							$index = $schedule->schedule_index;
						} ?>
						<tr>
							<td class="cell-amount"><input type="number" placeholder="0" step="0.01" min="0" name="plan_amount[]" class="plan_amount" value="<?php echo esc_attr( $schedule->amount ); ?>" /></td>
							<td class="cell-percent">%</td>
							<?php if ( 0 === $index ) : ?>
								<td colspan="3">
									<?php esc_html_e( 'Immediately', 'ultimatewoo-pro' ); ?>
									<input type="hidden" name="plan_interval_amount[]" class="plan_interval_amount" value="0" />
									<input type="hidden" name="plan_interval_unit[]" class="plan_interval_unit" value="0" />
								</td></td>
							<?php else : ?>
								<td class="cell-after"><?php esc_html_e( 'After', 'ultimatewoo-pro' ); ?></td>
								<td class="cell-interval-amount"><input type="number" name="plan_interval_amount[]" class="plan_interval_amount" min="0" value="<?php echo esc_attr( $schedule->interval_amount ); ?>" step="1" /></td>
								<td class="cell-interval-unit"><select name="plan_interval_unit[]" class="plan_interval_unit">
									<option value="day" <?php selected( 'day', $schedule->interval_unit ); ?>><?php esc_html_e( 'Days', 'ultimatewoo-pro' ); ?></option>
									<option value="week" <?php selected( 'week', $schedule->interval_unit ); ?>><?php esc_html_e( 'Weeks', 'ultimatewoo-pro' ); ?></option>
									<option value="month" <?php selected( 'month', $schedule->interval_unit ); ?>><?php esc_html_e( 'Months', 'ultimatewoo-pro' ); ?></option>
									<option value="year" <?php selected( 'year', $schedule->interval_unit ); ?>><?php esc_html_e( 'Years', 'ultimatewoo-pro' ); ?></option>
								</select></td>
							<?php endif; ?>
							<?php if ( 0 === $index ) : ?>
								<td class="cell-actions"><a href="#" class="button add-row">+</a></td>
							<?php else : ?>
								<td class="cell-actions"><a href="#" class="button add-row">+</a><a href="#" class="button remove-row">-</a></td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<p class="submit"><input type="submit" class="button button-primary" name="save_plan" value="<?php esc_attr_e( 'Save Payment Plan', 'ultimatewoo-pro' ); ?>" /></p>
		<?php wp_nonce_field( 'woocommerce_save_plan', 'woocommerce_save_plan_nonce' ); ?>
	</form>
</div>
