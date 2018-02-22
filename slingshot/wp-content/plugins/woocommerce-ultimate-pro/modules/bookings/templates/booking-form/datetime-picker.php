<?php
/**
 * The template for displaying the booking form and calendar with time blocks to customers. 
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-bookings/booking-form/datetime-picker.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/bookings-templates/
 * @author  Automattic
 * @version 1.10.8
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

wp_enqueue_script( 'wc-bookings-date-picker' );
wp_enqueue_script( 'wc-bookings-time-picker' );
extract( $field );

$month_before_day = strpos( __( 'F j, Y' ), 'F' ) < strpos( __( 'F j, Y' ), 'j' );
?>
<fieldset class="wc-bookings-date-picker <?php echo implode( ' ', $class ); ?>">
	<legend>
		<span class="label"><?php echo $label; ?></span>: <small class="wc-bookings-date-picker-choose-date"><?php _e( 'Choose...', 'ultimatewoo-pro' ); ?></small>
	</legend>
	<div class="picker" data-display="<?php echo $display; ?>" data-availability="<?php echo esc_attr( json_encode( $availability_rules ) ); ?>" data-default-availability="<?php echo $default_availability ? 'true' : 'false'; ?>" data-fully-booked-days="<?php echo esc_attr( json_encode( $fully_booked_days ) ); ?>" data-unavailable-days="<?php echo esc_attr( json_encode( $unavailable_days ) ); ?>" data-partially-booked-days="<?php echo esc_attr( json_encode( $partially_booked_days ) ); ?>" data-restricted-days="<?php echo esc_attr( json_encode( $restricted_days ) ); ?>" data-min_date="<?php echo ! empty( $min_date_js ) ? $min_date_js : 0; ?>" data-max_date="<?php echo $max_date_js; ?>" data-default_date="<?php echo esc_attr( $default_date ); ?>"></div>
	<div class="wc-bookings-date-picker-date-fields">
		<?php 
		// woocommerce_bookings_mdy_format filter to choose between month/day/year and day/month/year format
		if ( $month_before_day && apply_filters( 'woocommerce_bookings_mdy_format', true ) ) : ?>
		<label>
			<input type="text" name="<?php echo $name; ?>_month" placeholder="<?php _e( 'mm', 'ultimatewoo-pro' ); ?>" size="2" class="required_for_calculation booking_date_month" />
			<span><?php _e( 'Month', 'ultimatewoo-pro' ); ?></span>
		</label> / <label>
			<input type="text" name="<?php echo $name; ?>_day" placeholder="<?php _e( 'dd', 'ultimatewoo-pro' ); ?>" size="2" class="required_for_calculation booking_date_day" />
			<span><?php _e( 'Day', 'ultimatewoo-pro' ); ?></span>
		</label>
		<?php else : ?>
		<label>
			<input type="text" name="<?php echo $name; ?>_day" placeholder="<?php _e( 'dd', 'ultimatewoo-pro' ); ?>" size="2" class="required_for_calculation booking_date_day" />
			<span><?php _e( 'Day', 'ultimatewoo-pro' ); ?></span>
		</label> / <label>
			<input type="text" name="<?php echo $name; ?>_month" placeholder="<?php _e( 'mm', 'ultimatewoo-pro' ); ?>" size="2" class="required_for_calculation booking_date_month" />
			<span><?php _e( 'Month', 'ultimatewoo-pro' ); ?></span>
		</label>
		<?php endif; ?>
		 / <label>
			<input type="text" value="<?php echo date( 'Y' ); ?>" name="<?php echo $name; ?>_year" placeholder="<?php _e( 'YYYY', 'ultimatewoo-pro' ); ?>" size="4" class="required_for_calculation booking_date_year" />
			<span><?php _e( 'Year', 'ultimatewoo-pro' ); ?></span>
		</label>
	</div>
</fieldset>
<div class="form-field form-field-wide">
	<label for="<?php echo $name; ?>"><?php _e( 'Time', 'ultimatewoo-pro' ); ?>:</label>
	<ul class="block-picker">
		<li><?php _e( 'Choose a date above to see available times.', 'ultimatewoo-pro' ); ?></li>
	</ul>
	<input type="hidden" class="required_for_calculation" name="<?php echo $name; ?>_time" id="<?php echo $name; ?>" />
</div>
