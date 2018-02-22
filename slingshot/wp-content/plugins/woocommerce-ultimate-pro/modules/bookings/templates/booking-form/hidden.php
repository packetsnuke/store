<?php
/**
 * The template used for hidden fields in the booking form. 
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-bookings/booking-form/hidden.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/bookings-templates/
 * @author  Automattic
 * @version 1.8.0
 * @since   1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

extract( $field );
?>
<p class="form-field form-field-wide <?php echo implode( ' ', $class ); ?>" style="display: none;">
	<label for="<?php echo $name; ?>"><?php echo $label; ?>:</label>
	<input
		type="hidden"
		value="<?php echo ( ! empty( $min ) ) ? $min : 0; ?>"
		step="<?php echo ( isset( $step ) ) ? $step : ''; ?>"
		min="<?php echo ( isset( $min ) ) ? $min : ''; ?>"
		max="<?php echo ( isset( $max ) ) ? $max : ''; ?>"
		name="<?php echo $name; ?>"
		id="<?php echo $name; ?>"
		/> <?php echo ( ! empty( $after ) ) ? $after : ''; ?>
</p>
