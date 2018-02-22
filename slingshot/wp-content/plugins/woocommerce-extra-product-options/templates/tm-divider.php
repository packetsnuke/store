<?php
/**
 * The template for displaying the divider element for the builder mode
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-divider.php
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
<li class="tmcp-field-wrap"><?php echo $divider; ?></li>