<?php
/**
 * The template for displaying the heading element for the builder mode
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-header.php
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
?>
<li class="tmcp-field-wrap">
	<?php echo '<' . $title_size;
	if ( !empty( $title_color ) ) {
		echo ' style="color:' . $title_color . '"';
	}
	echo ' class="tm-epo-field-label">'
		. $title;
	if ( $required ) {
		echo '<span class="tm-epo-required">*</span>';
	}
	echo '</' . $title_size . '>';
	?><?php
	if ( !empty( $description ) ) {
		echo '<div';
		if ( !empty( $description_color ) ) {
			echo ' style="color:' . $description_color . '"';
		}
		echo ' class="tm-description">' . $description . '</div>';
	} ?>
</li>