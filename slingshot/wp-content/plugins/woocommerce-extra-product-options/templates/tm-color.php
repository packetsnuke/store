<?php
/**
 * The template for displaying the color picker element for the builder mode
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-color.php
 *
 * NOTE that we may need to update template files and you
 * (the plugin or theme developer) will need to copy the new files
 * to your theme or plugin to maintain compatibility.
 *
 * @author    themeComplete
 * @package   WooCommerce Extra Product Options/Templates
 * @version   4.0
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
$get_default_value = "";
if ( TM_EPO()->tm_epo_global_reset_options_after_add == "no" && isset( $_POST[ $name ] ) ) {
	$get_default_value = esc_attr( stripslashes( $_POST[ $name ] ) );
} elseif ( isset( $_GET[ $name ] ) ) {
	$get_default_value = esc_attr( stripslashes( $_GET[ $name ] ) );
} else {
	$get_default_value = $default_value;
}
$get_default_value = apply_filters( 'wc_epo_default_value', $get_default_value, isset( $tm_element_settings ) ? $tm_element_settings : array() );
?>
<li class="tmcp-field-wrap">
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_start.php'); ?>
    <label for="<?php echo $id; ?>"></label>
    <input<?php
	if ( isset( $placeholder ) ) {
		echo ' placeholder="' . $placeholder . '"';
	}
	?> class="<?php echo $fieldtype; ?> tm-color-picker tm-epo-field tmcp-textfield"
       name="<?php echo $name; ?>"
       data-show-input="true"
       data-show-initial="true"
       data-allow-empty="true"
       data-show-alpha="false"
       data-show-palette="false"
       data-clickout-fires-change="true"
       data-show-buttons="false"
       data-preferred-format="hex"
       data-price=""
       data-rules="<?php echo $rules; ?>"
       data-original-rules="<?php echo $original_rules; ?>"
       data-rulestype="<?php echo $rules_type; ?>"
       value="<?php echo $get_default_value; ?>"
       id="<?php echo $id; ?>"
       tabindex="<?php echo $tabindex; ?>" 
       <?php if ( !empty( $tax_obj ) ) {
      echo 'data-tax-obj="' . $tax_obj . '" ';
    } ?>
       type="text"/>
	<?php include(TM_EPO_TEMPLATE_PATH .'_price.php'); ?>
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_end.php'); ?>
	<?php do_action( 'tm_after_element', isset( $tm_element_settings ) ? $tm_element_settings : array() ); ?>
</li>