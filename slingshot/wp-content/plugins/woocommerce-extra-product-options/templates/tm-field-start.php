<?php
/**
 * The template for displaying the start of an element for the local mode
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-field-start.php
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
<li id="<?php echo $field_id; ?>" class="cpf_hide_element tm-extra-product-options-field<?php if ( $required ) {
	echo ' tm-epo-has-required';
} ?>">
    <span class="tm-epo-field-label"><?php echo $title; ?><?php if ( $required ) { ?><span
                class="tm-required">*</span><?php } ?></span>
    <div class="tm-extra-product-options-container">
        <ul data-original-rules="" data-rules="<?php echo $rules; ?>" data-rulestype="<?php echo $rules_type; ?>"
            class="tmcp-ul-wrap tmcp-attributes tm-extra-product-options-<?php echo $type; ?>">