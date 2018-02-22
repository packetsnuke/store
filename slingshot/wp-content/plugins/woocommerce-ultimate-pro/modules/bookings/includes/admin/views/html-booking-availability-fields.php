<?php
	$intervals = array();

	$intervals['months'] = array(
		'1'  => __( 'January', 'ultimatewoo-pro' ),
		'2'  => __( 'February', 'ultimatewoo-pro' ),
		'3'  => __( 'March', 'ultimatewoo-pro' ),
		'4'  => __( 'April', 'ultimatewoo-pro' ),
		'5'  => __( 'May', 'ultimatewoo-pro' ),
		'6'  => __( 'June', 'ultimatewoo-pro' ),
		'7'  => __( 'July', 'ultimatewoo-pro' ),
		'8'  => __( 'August', 'ultimatewoo-pro' ),
		'9'  => __( 'September', 'ultimatewoo-pro' ),
		'10' => __( 'October', 'ultimatewoo-pro' ),
		'11' => __( 'November', 'ultimatewoo-pro' ),
		'12' => __( 'December', 'ultimatewoo-pro' )
	);

	$intervals['days'] = array(
		'1' => __( 'Monday', 'ultimatewoo-pro' ),
		'2' => __( 'Tuesday', 'ultimatewoo-pro' ),
		'3' => __( 'Wednesday', 'ultimatewoo-pro' ),
		'4' => __( 'Thursday', 'ultimatewoo-pro' ),
		'5' => __( 'Friday', 'ultimatewoo-pro' ),
		'6' => __( 'Saturday', 'ultimatewoo-pro' ),
		'7' => __( 'Sunday', 'ultimatewoo-pro' )
	);

	for ( $i = 1; $i <= 53; $i ++ ) {
		$intervals['weeks'][ $i ] = sprintf( __( 'Week %s', 'ultimatewoo-pro' ), $i );
	}

	if ( ! isset( $availability['type'] ) ) {
		$availability['type'] = 'custom';
	}

	if ( ! isset( $availability['priority'] ) ) {
		$availability['priority'] = 10;
	}
?>
<tr>
	<td class="sort">&nbsp;</td>
	<td>
		<div class="select wc_booking_availability_type">
			<select name="wc_booking_availability_type[]">
				<option value="custom" <?php selected( $availability['type'], 'custom' ); ?>><?php _e( 'Date range', 'ultimatewoo-pro' ); ?></option>
				<option value="months" <?php selected( $availability['type'], 'months' ); ?>><?php _e( 'Range of months', 'ultimatewoo-pro' ); ?></option>
				<option value="weeks" <?php selected( $availability['type'], 'weeks' ); ?>><?php _e( 'Range of weeks', 'ultimatewoo-pro' ); ?></option>
				<option value="days" <?php selected( $availability['type'], 'days' ); ?>><?php _e( 'Range of days', 'ultimatewoo-pro' ); ?></option>
				<optgroup label="<?php _e( 'Time Ranges', 'ultimatewoo-pro' ); ?>">
					<option value="time" <?php selected( $availability['type'], 'time' ); ?>><?php _e( 'Time Range (all week)', 'ultimatewoo-pro' ); ?></option>
					<option value="time:range" <?php selected( $availability['type'], 'time:range' ); ?>><?php _e( 'Date Range with time', 'ultimatewoo-pro' ); ?></option>
					<?php foreach ( $intervals['days'] as $key => $label ) : ?>
						<option value="time:<?php echo $key; ?>" <?php selected( $availability['type'], 'time:' . $key ) ?>><?php echo $label; ?></option>
					<?php endforeach; ?>
				</optgroup>
			</select>
		</div>
	</td>
	<td style="border-right:0;">
	<div class="bookings-datetime-select-from">
		<div class="select from_day_of_week">
			<select name="wc_booking_availability_from_day_of_week[]">
				<?php foreach ( $intervals['days'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $availability['from'] ) && $availability['from'] == $key, true ) ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select from_month">
			<select name="wc_booking_availability_from_month[]">
				<?php foreach ( $intervals['months'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $availability['from'] ) && $availability['from'] == $key, true ) ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select from_week">
			<select name="wc_booking_availability_from_week[]">
				<?php foreach ( $intervals['weeks'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $availability['from'] ) && $availability['from'] == $key, true ) ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="from_date">
			<?php
			$from_date = '';
			if ( 'custom' === $availability['type'] && ! empty( $availability['from'] ) ) {
				$from_date = $availability['from'];
			} else if ( 'time:range' === $availability['type'] && ! empty( $availability['from_date'] ) ) {
				$from_date = $availability['from_date'];
			}
			?>
			<input type="text" class="date-picker" name="wc_booking_availability_from_date[]" value="<?php echo esc_attr( $from_date ); ?>" />
		</div>
		<div class="from_time">
			<input type="time" class="time-picker" name="wc_booking_availability_from_time[]" value="<?php if ( strrpos( $availability['type'], 'time' ) === 0 && ! empty( $availability['from'] ) ) echo $availability['from'] ?>" placeholder="HH:MM" />
		</div>
	</div>
	</td>
	<td style="border-right:0;" class="bookings-to-label-row">
		<p><?php _e( 'to', 'ultimatewoo-pro' ); ?></p>
		<p class="bookings-datetimerange-second-label"><?php _e( 'to', 'ultimatewoo-pro' ); ?></p>
	</td>
	<td>
	<div class='bookings-datetime-select-to'>
		<div class="select to_day_of_week">
			<select name="wc_booking_availability_to_day_of_week[]">
				<?php foreach ( $intervals['days'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $availability['to'] ) && $availability['to'] == $key, true ) ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select to_month">
			<select name="wc_booking_availability_to_month[]">
				<?php foreach ( $intervals['months'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $availability['to'] ) && $availability['to'] == $key, true ) ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select to_week">
			<select name="wc_booking_availability_to_week[]">
				<?php foreach ( $intervals['weeks'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $availability['to'] ) && $availability['to'] == $key, true ) ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="to_date">
			<?php
			$to_date = '';
			if ( 'custom' === $availability['type'] && ! empty( $availability['to'] ) ) {
				$to_date = $availability['to'];
			} else if ( 'time:range' === $availability['type'] && ! empty( $availability['to_date'] ) ) {
				$to_date = $availability['to_date'];
			}
			?>
			<input type="text" class="date-picker" name="wc_booking_availability_to_date[]" value="<?php echo esc_attr( $to_date ); ?>" />
		</div>

		<div class="to_time">
			<input type="time" class="time-picker" name="wc_booking_availability_to_time[]" value="<?php if ( strrpos( $availability['type'], 'time' ) === 0 && ! empty( $availability['to'] ) ) echo $availability['to']; ?>" placeholder="HH:MM" />
		</div>
	</div>
	</td>
	<td>
		<div class="select">
			<select name="wc_booking_availability_bookable[]">
				<option value="no" <?php selected( isset( $availability['bookable'] ) && $availability['bookable'] == 'no', true ) ?>><?php _e( 'No', 'ultimatewoo-pro' ) ;?></option>
				<option value="yes" <?php selected( isset( $availability['bookable'] ) && $availability['bookable'] == 'yes', true ) ?>><?php _e( 'Yes', 'ultimatewoo-pro' ) ;?></option>
			</select>
		</div>
	</td>
	<td>
	<div class="priority">
		<input type="number" name="wc_booking_availability_priority[]" value="<?php echo esc_attr( $availability['priority'] ); ?>" placeholder="10" />
	</div>
	</td>
	<td class="remove">&nbsp;</td>
</tr>
