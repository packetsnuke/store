<?php
/**
 * The template for displaying the textarea element for the builder mode
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-textarea.php
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
if ( !isset( $fieldtype ) ) {
	$fieldtype = "tmcp-field";
}
if ( isset( $textbeforeprice ) && $textbeforeprice != '' ) {
	$textbeforeprice = '<span class="before-amount' . (!empty( $hide_amount ) ? " " . $hide_amount : "") . '">' . $textbeforeprice . '</span>';
}
if ( isset( $textafterprice ) && $textafterprice != '' ) {
	$textafterprice = '<span class="after-amount' . (!empty( $hide_amount ) ? " " . $hide_amount : "") . '">' . $textafterprice . '</span>';
}
if ( !empty( $class ) ) {
	$fieldtype .= " " . $class;
}
if ( !isset( $default_value ) ) {
	$default_value = "";
}
$get_default_value = "";
if ( TM_EPO()->tm_epo_global_reset_options_after_add == "no" && isset( $_POST[ $name ] ) ) {
	$get_default_value = stripslashes( $_POST[ $name ] );
} elseif ( isset( $_GET[ $name ] ) ) {
	$get_default_value = esc_attr( stripslashes( $_GET[ $name ] ) );
} else {
	$get_default_value = $default_value;
}
$get_default_value = apply_filters( 'wc_epo_default_value', $get_default_value, isset( $tm_element_settings ) ? $tm_element_settings : array() );
?>
<li class="tmcp-field-wrap">
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_start.php'); ?>
    <label for="<?php echo $id; ?>">
    <textarea<?php
	if ( isset( $placeholder ) ) {
		echo ' placeholder="' . $placeholder . '"';
	}
	if ( isset( $min_chars ) && $min_chars != '' ) {
		echo ' minlength="' . $min_chars . '"';
	}
	if ( isset( $max_chars ) && $max_chars != '' ) {
		echo ' maxlength="' . $max_chars . '"';
	}
	?> class="<?php echo $fieldtype; ?> tm-epo-field tmcp-textarea"
       name="<?php echo $name; ?>"
       data-price=""
       data-rules="<?php echo $rules; ?>"
       data-original-rules="<?php echo $original_rules; ?>"
       data-rulestype="<?php echo $rules_type; ?>"
       data-freechars="<?php echo $freechars; ?>"
       id="<?php echo $id; ?>"
       tabindex="<?php echo $tabindex; ?>"
       rows="5" 
       <?php if ( !empty( $tax_obj ) ) {
			echo 'data-tax-obj="' . $tax_obj . '" ';
		} ?>
       cols="20"><?php echo $get_default_value; ?></textarea></label>
	<?php include(TM_EPO_TEMPLATE_PATH .'_price.php'); ?>
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_end.php'); ?>
	<?php do_action( 'tm_after_element', isset( $tm_element_settings ) ? $tm_element_settings : array() ); ?>
</li>