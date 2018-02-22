<?php
/**
 * The template for displaying the start of the local mode
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-start.php
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
$showoptionsoverride='';
if (TM_EPO()->tm_epo_progressive_display==='no'){
	$showoptionsoverride=' tc-show-override';
}
?>
<div data-epo-id="<?php echo $epo_internal_counter; ?>"
     data-cart-id="<?php echo $forcart; ?>"
     data-product-id="<?php echo $product_id; ?>"
     class="tc-extra-product-options tm-extra-product-options tm-custom-prices tc-clearfix tm-product-id-<?php echo $product_id; ?> <?php echo $classcart; ?><?php echo $isfromshortcode; ?><?php echo $showoptionsoverride; ?>"
     id="tm-extra-product-options<?php echo $form_prefix; ?>">
    <div class="tm-extra-product-options-inner">
        <ul id="tm-extra-product-options-fields" class="tm-extra-product-options-fields">                            