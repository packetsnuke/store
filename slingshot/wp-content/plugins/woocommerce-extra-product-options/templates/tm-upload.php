<?php
/**
 * The template for displaying the upload element for the builder mode
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-upload.php
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
	$textafterprice = '<span class="after-amount">' . $textafterprice . '</span>';
}
if ( !empty( $class ) ) {
	$fieldtype .= " " . $class;
}
?>
<li class="tmcp-field-wrap">
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_start.php'); ?>
	<?php
	$upload_text = "";
	switch ( $style ) {
		case "":
			$style = '';
			break;
		case "button":
			$style = ' class="cpf-upload-container"';
			$upload_text = '<span>' . ((!empty( TM_EPO()->tm_epo_select_file_text )) ? TM_EPO()->tm_epo_select_file_text : __( 'Select file', 'woocommerce-tm-extra-product-options' )) . '</span>';
			break;
	}
	?>
    <label for="<?php echo $id; ?>"<?php echo $style; ?>><?php echo $upload_text; ?>
        <input type="file" class="<?php echo $fieldtype; ?> tm-epo-field tmcp-upload"
               data-price=""
               data-rules="<?php echo $rules; ?>"
               data-original-rules="<?php echo $original_rules; ?>"
               data-rulestype="<?php echo $rules_type; ?>"
               id="<?php echo $id; ?>"
               tabindex="<?php echo $tabindex; ?>"
               name="<?php echo $name; ?>"/>
    </label>
    <small><?php echo sprintf( __( '(max file size %s)', 'woocommerce-tm-extra-product-options' ), $max_size ) ?></small>
	<?php include(TM_EPO_TEMPLATE_PATH .'_price.php'); ?>
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_end.php'); ?>
	<?php do_action( 'tm_after_element', isset( $tm_element_settings ) ? $tm_element_settings : array() ); ?>
</li>