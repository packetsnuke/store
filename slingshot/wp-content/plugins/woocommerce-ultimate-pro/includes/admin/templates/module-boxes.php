<?php
/**
 *	Form for enabling UltimateWoo modules
 *
 *	@package UltimateWoo Pro
 *	@author UltimateWoo
 *	@since 1.0
 */

$count = 0;

do_action( "ultimatewoo_settings_mb_start_{$args['args']['key']}" );

?>

<p class="description"><?php echo $args['args']['section']['section_description']; ?></p>
<?php // Make sure the modules array is always posted, even when none are selected; this is unset when processing ?>
<input type="hidden" name="ultimatewoo[modules][triggered]" value="1">

<?php

/**
 *	Output a checkbox for each module
 */
foreach ( $args['args']['section']['section_modules'] as $module ) {

		// Assemble the key for proper saving
		$key = 'ultimatewoo[modules][' . $module['key'] . ']';

		// Whether option is enabled
		if ( isset( $this->settings['modules'][$module['key']] ) && intval( $this->settings['modules'][$module['key']] ) === 1 ) {
			$value = 1;
		} else {
			$value = 0;
		}

		// CSS classes
		if ( $count === 0 || 0 === $count % 3 ) {
			$classes = 'one-third first';
		} else {
			$classes = 'one-third';
		}
	?>

	<p id="<?php echo $module['key']; ?>" class="<?php echo $classes; ?>">
		<label class="module-label" for="<?php echo $key; ?>"><?php echo $module['title']; ?></label>
		<input type="checkbox" class="uw-settings-checkbox" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="1" <?php checked( intval( $value ), 1 ); ?> />
	</p>

	<?php

	$count++;

	// Clear after every three and last
	if ( 0 === $count % 3 || $count == sizeof( $args['args']['section']['section_modules'] ) ) {
		echo '<br class="clear">';
	}
}

do_action( "ultimatewoo_settings_mb_end_{$args['args']['key']}" );

include 'save-button.php';