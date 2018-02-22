<?php
/**
 * The template for displaying the range picker element for the builder mode
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-range.php
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

if ( $min != '' && $default_value == '' ) {
	$default_value = $min;
}

$get_default_value = "";
if ( TM_EPO()->tm_epo_global_reset_options_after_add == "no" && isset( $_POST[ $name ] ) ) {
	$get_default_value = esc_attr( stripslashes( $_POST[ $name ] ) );
} elseif ( isset( $_GET[ $name ] ) ) {
	$get_default_value = esc_attr( stripslashes( $_GET[ $name ] ) );
} elseif ( isset( $default_value ) ) {
	$get_default_value = $default_value;
} else {
	echo $min;
}
$get_default_value = apply_filters( 'wc_epo_default_value', $get_default_value, isset( $tm_element_settings ) ? $tm_element_settings : array() );
?>
<li class="tmcp-field-wrap<?php if ( !empty( $show_picker_value ) ) echo " tm-show-picker-" . $show_picker_value; ?>">
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_start.php'); ?>
    <div
            class="tm-range-picker<?php if ( $pips == "yes" ) echo " pips"; ?>"
            data-min="<?php echo $min; ?>"
            data-max="<?php echo $max; ?>"
            data-step="<?php echo $step; ?>"
            data-pips="<?php echo $pips; ?>"
            data-noofpips="<?php echo $noofpips; ?>"
            data-show-picker-value="<?php echo $show_picker_value; ?>"
            data-field-id="<?php echo $id; ?>"
            data-start="<?php echo $get_default_value; ?>"
    ></div>
    <label class="tm-show-picker-value" for="<?php echo $id; ?>"></label>
    <input<?php
	if ( isset( $placeholder ) ) {
		echo ' placeholder="' . $placeholder . '"';
	}
	if ( isset( $max_chars ) && $max_chars != '' ) {
		echo ' maxlength="' . $max_chars . '"';
	}
	?> class="<?php echo $fieldtype; ?> tm-epo-field tmcp-textfield tmcp-range"
       name="<?php echo $name; ?>"
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
       type="hidden"/>
	<?php include(TM_EPO_TEMPLATE_PATH .'_price.php'); ?>
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_end.php'); ?>
	<?php do_action( 'tm_after_element', isset( $tm_element_settings ) ? $tm_element_settings : array() ); ?>
</li>