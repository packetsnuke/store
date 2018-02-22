<?php
/**
 * The template for displaying the end of a section in the builder mode options
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-builder-section-end.php
 *
 * NOTE that we may need to update template files and you
 * (the plugin or theme developer) will need to copy the new files
 * to your theme or plugin to maintain compatibility.
 *
 * @author 		themeComplete
 * @package 	WooCommerce Extra Product Options/Templates
 * @version 	4.0
 */

// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}
if ( $style == "box" ) {
	echo '</div>';
}
if ( $style == "collapse" || $style == "collapseclosed" || $style == "accordion" ) {
	echo '</div></div>';
}
if ( isset( $sections_type ) && $sections_type == "popup" ) {
	echo '</div>';
}
if ( !empty( $description ) && !empty( $description_position ) && $description_position == "below" ) {
	echo '<div';
	if ( !empty( $description_color ) ) {
		echo ' style="color:' . $description_color . '"';
	}
	echo ' class="tm-description">' . do_shortcode( $description ) . '</div>';
}
?>
</div>