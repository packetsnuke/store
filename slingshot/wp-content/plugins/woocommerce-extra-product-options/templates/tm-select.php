<?php
/**
 * The template for displaying the select element for the builder/local modes
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-select.php
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
} else {
	$textbeforeprice = '';
}
if ( isset( $textafterprice ) && $textafterprice != '' ) {
	$textafterprice = '<span class="after-amount' . (!empty( $hide_amount ) ? " " . $hide_amount : "") . '">' . $textafterprice . '</span>';
}
if ( !empty( $class ) ) {
	$fieldtype .= " " . $class;
}
if ( !empty( $changes_product_image ) ) {
	$fieldtype .= " tm-product-image";
}
$li_class = "";
if ( !empty( $li_class ) ) {
	$li_class = " " . $li_class;
}
$element_data_attr_html = array();
if ( !empty( $element_data_attr ) && is_array( $element_data_attr ) ) {
	foreach ( $element_data_attr as $k => $v ) {
		$element_data_attr_html[] = $k . '="' . esc_attr( $v ) . '"';
	}
}
if ( !empty( $element_data_attr_html ) ) {
	$element_data_attr_html = " " . implode( " ", $element_data_attr_html ) . " ";
} else {
	$element_data_attr_html = "";
}
if (TM_EPO()->tm_epo_select_fullwidth === 'yes'){
	$class_label = 'class="fullwidth" ';
}else{
	$class_label = '';
}
?>
<li class="tmcp-field-wrap<?php echo $li_class; ?>">
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_start.php'); ?>
    <label <?php echo $class_label;?>for="<?php echo $id; ?>">
        <select class="<?php echo $fieldtype; ?> tm-epo-field tmcp-select"
                name="<?php echo $name; ?>"
                data-price=""
                data-rules="" data-original-rules="" <?php echo $element_data_attr_html; ?>
                id="<?php echo $id; ?>"
                tabindex="<?php echo $tabindex; ?>">
			<?php echo $options; ?>
        </select></label>
	<?php include(TM_EPO_TEMPLATE_PATH .'_price.php'); ?>
	<?php include(TM_EPO_TEMPLATE_PATH .'_quantity_end.php'); ?>
	<?php do_action( 'tm_after_element', isset( $tm_element_settings ) ? $tm_element_settings : array() ); ?>
</li>