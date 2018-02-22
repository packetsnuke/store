<?php
/**
 *	Form for managing license
 *
 *	@package UltimateWoo Pro
 *	@author UltimateWoo
 *	@since 1.0
 */
?>

<?php if ( $site_status != 'valid' ) : ?>
	<h4><?php _e( 'Instructions on Activating Your License', 'ultimatewoo-pro' ); ?></h4>
<?php else : ?>
	<h4><?php _e( 'Instructions on Deactivating Your License', 'ultimatewoo-pro' ); ?></h4>
<?php endif; ?>

<ol>
	<?php if ( $site_status != 'valid' ) : ?>
		<li><?php _e( 'Retrieve and enter your license key in the field below.', 'ultimatewoo-pro' ); ?></li>
		<li><?php _e( 'Click "Activate License" to save and activate your license key.', 'ultimatewoo-pro' ); ?></li>
	<?php else : ?>
		<li><?php _e( 'Simply click the "Deactivate License" button.', 'ultimatewoo-pro' ); ?></li>
	<?php endif; ?>
</ol>

<input type="text" class="regular-text" name="ultimatewoo[license][license_key]" id="ultimatewoo[license][license_key]" value="<?php echo $license_key; ?>" <?php if ( $site_status == 'valid' ) echo 'readonly'; ?>>
<input type="hidden" name="ultimatewoo[license][site_status]" id="ultimatewoo[license][site_status]" value="<?php echo $site_status; ?>">
<input type="hidden" name="ultimatewoo[license][license_exp_date]" id="ultimatewoo[license][license_exp_date]" value="<?php echo $license_exp_date; ?>">
<input type="hidden" name="ultimatewoo[license][license_limit]" id="ultimatewoo[license][license_limit]" value="<?php echo $license_limit; ?>">
<input type="hidden" name="ultimatewoo[license][activations_left]" id="ultimatewoo[license][activations_left]" value="<?php echo $activations_left; ?>">

<?php // Data for selecting action to take on license ?>
<?php if ( $site_status != 'valid' ) : ?>
	<input type="hidden" name="activate-license" id="activate-license" value="1">
<?php else : ?>
	<input type="hidden" name="deactivate-license" id="deactivate-license" value="1">
<?php endif; ?>

<?php wp_nonce_field( 'ultimatewoo_license_nonce', 'ultimatewoo_license_nonce' ); ?>

<?php // Site is not active, so output activate button ?>
<?php if ( $site_status != 'valid' ) : ?>
	<input type="submit" class="button-primary ultimatewoo-button" value="<?php _e( 'Activate License', 'ultimatewoo-pro' ); ?>">
<?php else : ?>
	<input type="submit" class="button-secondary ultimatewoo-button deactivate" value="<?php _e( 'Deactivate License', 'ultimatewoo-pro' ); ?>">
<?php endif; ?>

<?php do_action( 'ultimatewoo_settings_page_after_license_form' ); ?>