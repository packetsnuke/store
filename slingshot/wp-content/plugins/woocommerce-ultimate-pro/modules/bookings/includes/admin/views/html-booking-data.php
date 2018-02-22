<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="options_group show_if_booking">
	<?php
		$duration_type = $bookable_product->get_duration_type( 'edit' );
		$duration      = $bookable_product->get_duration( 'edit' );
		$duration_unit = $bookable_product->get_duration_unit( 'edit' );
	?>
	<p class="form-field">
		<label for="_wc_booking_duration_type"><?php _e( 'Booking duration', 'ultimatewoo-pro' ); ?></label>
		<select name="_wc_booking_duration_type" id="_wc_booking_duration_type" class="" style="width: auto; margin-right: 7px;">
			<option value="fixed" <?php selected( $duration_type, 'fixed' ); ?>><?php _e( 'Fixed blocks of', 'ultimatewoo-pro' ); ?></option>
			<option value="customer" <?php selected( $duration_type, 'customer' ); ?>><?php _e( 'Customer defined blocks of', 'ultimatewoo-pro' ); ?></option>
		</select>
		<input type="number" name="_wc_booking_duration" id="_wc_booking_duration" value="<?php echo $duration; ?>" step="1" min="1" style="margin-right: 7px; width: 4em;">
		<select name="_wc_booking_duration_unit" id="_wc_booking_duration_unit" class="short" style="width: auto; margin-right: 7px;">
			<option value="month" <?php selected( $duration_unit, 'month' ); ?>><?php _e( 'Month(s)', 'ultimatewoo-pro' ); ?></option>
			<option value="day" <?php selected( $duration_unit, 'day' ); ?>><?php _e( 'Day(s)', 'ultimatewoo-pro' ); ?></option>
			<option value="hour" <?php selected( $duration_unit, 'hour' ); ?>><?php _e( 'Hour(s)', 'ultimatewoo-pro' ); ?></option>
			<option value="minute" <?php selected( $duration_unit, 'minute' ); ?>><?php _e( 'Minute(s)', 'ultimatewoo-pro' ); ?></option>
		</select>
	</p>

	<div id="min_max_duration"><?php

		woocommerce_wp_text_input( array(
			'id'                => '_wc_booking_min_duration',
			'label'             => __( 'Minimum duration', 'ultimatewoo-pro' ),
			'description'       => __( 'The minimum allowed duration the user can input.', 'ultimatewoo-pro' ),
			'value'             => $bookable_product->get_min_duration( 'edit' ),
			'desc_tip'          => true,
			'type'              => 'number',
			'custom_attributes' => array(
				'min'           => '',
				'step'          => '1',
			),
		) );

		woocommerce_wp_text_input( array(
			'id'                => '_wc_booking_max_duration',
			'label'             => __( 'Maximum duration', 'ultimatewoo-pro' ),
			'description'       => __( 'The maximum allowed duration the user can input.', 'ultimatewoo-pro' ),
			'value'             => $bookable_product->get_max_duration( 'edit' ),
			'desc_tip'          => true,
			'type'              => 'number',
			'custom_attributes' => array(
				'min'           => '1',
				'step'          => '1',
			),
		) );
		?>
		<div id="enable-range-picker"><?php
			woocommerce_wp_checkbox( array(
				'id'          => '_wc_booking_enable_range_picker',
				'value'       => $bookable_product->get_enable_range_picker( 'edit' ) ? 'yes' : 'no',
				'label'       => __( 'Enable Calendar Range Picker?', 'ultimatewoo-pro' ),
				'description' => __( 'Lets the user select a start and end date on the calendar - duration will be calculated automatically.', 'ultimatewoo-pro' ),
			) );
		?></div>
	</div>

	<?php
		woocommerce_wp_select( array(
			'id'                 => '_wc_booking_calendar_display_mode',
			'value'              => $bookable_product->get_calendar_display_mode( 'edit' ),
			'label'              => __( 'Calendar display mode', 'ultimatewoo-pro' ),
			'description'        => __( 'Choose how the calendar is displayed on the booking form.', 'ultimatewoo-pro' ),
			'options'            => array(
				''               => __( 'Display calendar on click', 'ultimatewoo-pro' ),
				'always_visible' => __( 'Calendar always visible', 'ultimatewoo-pro' ),
			),
			'desc_tip'           => true,
			'class'              => 'select',
		) );

		woocommerce_wp_checkbox( array(
			'id'          => '_wc_booking_requires_confirmation',
			'value'       => $bookable_product->get_requires_confirmation( 'edit' ) ? 'yes' : 'no',
			'label'       => __( 'Requires confirmation?', 'ultimatewoo-pro' ),
			'description' => __( 'Check this box if the booking requires admin approval/confirmation. Payment will not be taken during checkout.', 'ultimatewoo-pro' )
		) );

		woocommerce_wp_checkbox( array(
			'id'          => '_wc_booking_user_can_cancel',
			'value'       => $bookable_product->get_user_can_cancel( 'edit' ) ? 'yes' : 'no',
			'label'       => __( 'Can be cancelled?', 'ultimatewoo-pro' ),
			'description' => __( 'Check this box if the booking can be cancelled by the customer after it has been purchased. A refund will not be sent automatically.', 'ultimatewoo-pro' )
		) );

		$cancel_limit      = $bookable_product->get_cancel_limit( 'edit' );
		$cancel_limit_unit = $bookable_product->get_cancel_limit_unit( 'edit' );
	?>
	<p class="form-field booking-cancel-limit">
		<label for="_wc_booking_cancel_limit"><?php _e( 'Booking can be cancelled until', 'ultimatewoo-pro' ); ?></label>
		<input type="number" name="_wc_booking_cancel_limit" id="_wc_booking_cancel_limit" value="<?php echo $cancel_limit; ?>" step="1" min="1" style="margin-right: 7px; width: 4em;">
		<select name="_wc_booking_cancel_limit_unit" id="_wc_booking_cancel_limit_unit" class="short" style="width: auto; margin-right: 7px;">
			<option value="month" <?php selected( $cancel_limit_unit, 'month' ); ?>><?php _e( 'Month(s)', 'ultimatewoo-pro' ); ?></option>
			<option value="day" <?php selected( $cancel_limit_unit, 'day' ); ?>><?php _e( 'Day(s)', 'ultimatewoo-pro' ); ?></option>
			<option value="hour" <?php selected( $cancel_limit_unit, 'hour' ); ?>><?php _e( 'Hour(s)', 'ultimatewoo-pro' ); ?></option>
			<option value="minute" <?php selected( $cancel_limit_unit, 'minute' ); ?>><?php _e( 'Minute(s)', 'ultimatewoo-pro' ); ?></option>
		</select>
		<span class="description"><?php _e( 'before the start date.', 'ultimatewoo-pro' ); ?></span>
	</p>

	<script type="text/javascript">
		jQuery( '._tax_status_field' ).closest( '.show_if_simple' ).addClass( 'show_if_booking' );
		jQuery( 'select#_wc_booking_duration_unit, select#_wc_booking_duration_type, input#_wc_booking_duration' ).change(function(){
			if ( 'day' === jQuery('select#_wc_booking_duration_unit').val() && '1' == jQuery('input#_wc_booking_duration').val() && 'customer' === jQuery('select#_wc_booking_duration_type').val() ) {
				jQuery('p._wc_booking_enable_range_picker_field').show();
			} else {
				jQuery('p._wc_booking_enable_range_picker_field').hide();
			}
		});
		jQuery( '#_wc_booking_duration_unit' ).change();
	</script>
</div>
