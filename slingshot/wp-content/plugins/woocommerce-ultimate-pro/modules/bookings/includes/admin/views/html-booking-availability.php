<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="bookings_availability" class="panel woocommerce_options_panel">
	<div class="options_group"><?php
		$min_date      = $bookable_product->get_min_date_value( 'edit' );
		$min_date_unit = $bookable_product->get_min_date_unit( 'edit' );
		$max_date      = $bookable_product->get_max_date_value( 'edit' );
		$max_date_unit = $bookable_product->get_max_date_unit( 'edit' );

		woocommerce_wp_text_input( array(
			'id'                => '_wc_booking_qty',
			'label'             => __( 'Max bookings per block', 'ultimatewoo-pro' ),
			'description'       => __( 'The maximum bookings allowed for each block. Can be overridden at resource level.', 'ultimatewoo-pro' ),
			'value'             => $bookable_product->get_qty( 'edit' ),
			'desc_tip'          => true,
			'type'              => 'number',
			'custom_attributes' => array(
				'min'           => '',
				'step' 	        => '1',
			),
		) );

		?>
		<p class="form-field">
			<label for="_wc_booking_min_date"><?php _e( 'Minimum block bookable', 'ultimatewoo-pro' ); ?></label>
			<input type="number" name="_wc_booking_min_date" id="_wc_booking_min_date" value="<?php echo $min_date; ?>" step="1" min="0" style="margin-right: 7px; width: 4em;">
			<select name="_wc_booking_min_date_unit" id="_wc_booking_min_date_unit" class="short" style="margin-right: 7px;">
				<option value="month" <?php selected( $min_date_unit, 'month' ); ?>><?php _e( 'Month(s)', 'ultimatewoo-pro' ); ?></option>
				<option value="week" <?php selected( $min_date_unit, 'week' ); ?>><?php _e( 'Week(s)', 'ultimatewoo-pro' ); ?></option>
				<option value="day" <?php selected( $min_date_unit, 'day' ); ?>><?php _e( 'Day(s)', 'ultimatewoo-pro' ); ?></option>
				<option value="hour" <?php selected( $min_date_unit, 'hour' ); ?>><?php _e( 'Hour(s)', 'ultimatewoo-pro' ); ?></option>
			</select> <?php _e( 'into the future', 'ultimatewoo-pro' ); ?>
		</p>
		<p class="form-field">
			<label for="_wc_booking_max_date"><?php _e( 'Maximum block bookable', 'ultimatewoo-pro' ); ?></label>
			<input type="number" name="_wc_booking_max_date" id="_wc_booking_max_date" value="<?php echo $max_date; ?>" step="1" min="1" style="margin-right: 7px; width: 4em;">
			<select name="_wc_booking_max_date_unit" id="_wc_booking_max_date_unit" class="short" style="margin-right: 7px;">
				<option value="month" <?php selected( $max_date_unit, 'month' ); ?>><?php _e( 'Month(s)', 'ultimatewoo-pro' ); ?></option>
				<option value="week" <?php selected( $max_date_unit, 'week' ); ?>><?php _e( 'Week(s)', 'ultimatewoo-pro' ); ?></option>
				<option value="day" <?php selected( $max_date_unit, 'day' ); ?>><?php _e( 'Day(s)', 'ultimatewoo-pro' ); ?></option>
				<option value="hour" <?php selected( $max_date_unit, 'hour' ); ?>><?php _e( 'Hour(s)', 'ultimatewoo-pro' ); ?></option>
			</select> <?php _e( 'into the future', 'ultimatewoo-pro' ); ?>
		</p>
		<p class="form-field _wc_booking_buffer_period">
			<label for="_wc_booking_buffer_period"><?php _e( 'Require a buffer period of', 'ultimatewoo-pro' ); ?></label>
			<input type="number" name="_wc_booking_buffer_period" id="_wc_booking_buffer_period" value="<?php echo esc_attr( $bookable_product->get_buffer_period( 'edit' ) ); ?>" step="1" min="0" style="margin-right: 7px; width: 4em;">
			<span class='_wc_booking_buffer_period_unit'></span>
			<?php _e( 'between bookings', 'ultimatewoo-pro' ); ?>
		</p>
		<?php

		woocommerce_wp_checkbox(
			array(
				'id'          => '_wc_booking_apply_adjacent_buffer',
				'value'       => $bookable_product->get_apply_adjacent_buffer( 'edit' ) ? 'yes' : 'no',
				'label'       => __( 'Adjacent Buffering?', 'ultimatewoo-pro' ),
				'description' => __( 'By default buffer period applies forward into the future of a booking. Enabling this option will apply adjacently (before and after Bookings).', 'ultimatewoo-pro' ),
			)
		);

		woocommerce_wp_select(
			array(
				'id'                => '_wc_booking_default_date_availability',
				'label'             => __( 'All dates are...', 'ultimatewoo-pro' ),
				'description'       => '',
				'value'             => $bookable_product->get_default_date_availability( 'edit' ),
				'options'           => array(
					'available'     => __( 'available by default', 'ultimatewoo-pro' ),
					'non-available' => __( 'not-available by default', 'ultimatewoo-pro' ),
				),
				'description'       => __( 'This option affects how you use the rules below.', 'ultimatewoo-pro' )
			)
		);

		woocommerce_wp_select(
			array(
				'id'          => '_wc_booking_check_availability_against',
				'label'       => __( 'Check rules against...', 'ultimatewoo-pro' ),
				'description' => '',
				'value'       => $bookable_product->get_check_start_block_only( 'edit' ) ? 'start' : '',
				'options'     => array(
					''        => __( 'All blocks being booked', 'ultimatewoo-pro' ),
					'start'   => __( 'The starting block only', 'ultimatewoo-pro' ),
				),
				'description' => __( 'This option affects how bookings are checked for availability.', 'ultimatewoo-pro' )
			)
		);
		?>
		<p class="form-field _wc_booking_first_block_time_field">
			<label for="_wc_booking_first_block_time"><?php _e( 'First block starts at...', 'ultimatewoo-pro' ); ?></label>
			<input type="time" name="_wc_booking_first_block_time" id="_wc_booking_first_block_time" value="<?php echo $bookable_product->get_first_block_time( 'edit' ); ?>" placeholder="HH:MM" />
		</p>

		<?php
		woocommerce_wp_checkbox(
			array(
				'id'          => '_wc_booking_has_restricted_days',
				'value'       => $bookable_product->has_restricted_days( 'edit' ) ? 'yes' : 'no',
				'label'       => __( 'Restrict start days?', 'ultimatewoo-pro' ),
				'description' => __( 'Restrict bookings so that they can only start on certain days of the week. Does not affect availability.', 'ultimatewoo-pro' ),
			)
		);
		?>

		<div class="booking-day-restriction">
			<table class="widefat">
				<tbody>
					<tr>
						<td>&nbsp;</td>

			<?php
				$weekdays = array(
					__( 'Sunday', 'ultimatewoo-pro' ),
					__( 'Monday', 'ultimatewoo-pro' ),
					__( 'Tuesday', 'ultimatewoo-pro' ),
					__( 'Wednesday', 'ultimatewoo-pro' ),
					__( 'Thursday', 'ultimatewoo-pro' ),
					__( 'Friday', 'ultimatewoo-pro' ),
					__( 'Saturday', 'ultimatewoo-pro' ),
					);

				for ( $i=0;  $i < 7;  $i++) { 
					?>
						<td>
							<label class="checkbox" for="_wc_booking_restricted_days[<?php echo $i; ?>]"><?php echo $weekdays[ $i ]; ?>&nbsp;</label>
							<input type="checkbox" class="checkbox" name="_wc_booking_restricted_days[<?php echo $i; ?>]" id="_wc_booking_restricted_days[<?php echo $i; ?>]" value="<?php echo $i; ?>" <?php checked( $restricted_days[ $i ], $i ); ?>>
						</td>
					<?php
				}
			?>
						<td>&nbsp;</td>
					</tr>
				</tbody>
			</table>
		</div>

	</div>
	<div class="options_group">
		<div class="table_grid">
			<table class="widefat">
				<thead>
					<tr>
						<th class="sort" width="1%">&nbsp;</th>
						<th><?php esc_html_e( 'Range type', 'ultimatewoo-pro' ); ?></th>
						<th><?php esc_html_e( 'Range', 'ultimatewoo-pro' ); ?></th>
						<th></th>
						<th></th>
						<th><?php esc_html_e( 'Bookable', 'ultimatewoo-pro' ); ?>&nbsp;<a class="tips" data-tip="<?php _e( 'If not bookable, users won\'t be able to choose this block for their booking.', 'ultimatewoo-pro' ); ?>">[?]</a></th>
						<th><?php esc_html_e( 'Priority', 'ultimatewoo-pro' ); ?>&nbsp;<a class="tips" data-tip="<?php echo esc_attr( get_wc_booking_priority_explanation() ); ?>">[?]</a></th>
						<th class="remove" width="1%">&nbsp;</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="6">
							<a href="#" class="button add_row" data-row="<?php
								ob_start();
								include( 'html-booking-availability-fields.php' );
								$html = ob_get_clean();
								echo esc_attr( $html );
							?>"><?php _e( 'Add Range', 'ultimatewoo-pro' ); ?></a>
							<span class="description"><?php echo esc_html( get_wc_booking_rules_explanation() ); ?></span>
						</th>
					</tr>
				</tfoot>
				<tbody id="availability_rows">
					<?php
						$values = $bookable_product->get_availability( 'edit' );
						if ( ! empty( $values ) && is_array( $values ) ) {
							foreach ( $values as $availability ) {
								include( 'html-booking-availability-fields.php' );
							}
						}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
