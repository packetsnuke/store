<?php
/**
 * The template for displaying the start of a section in the builder mode options
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-builder-section-start.php
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
if ( isset( $sections_type ) && $sections_type == "popup" ) {
	$sections_class .= " section_popup";
}
if ( !$haslogic ) {
	$logic = "";
}
$tm_product_id_class = "";
if ( !empty( $tm_product_id ) ) {
	$tm_product_id_class = " tm-product-id-" . $tm_product_id;
}
if ( $sections_type == "slider" ) {
	$column .= " tm-owl-slider-section";
}
?>
<div data-uniqid="<?php echo $uniqid; ?>"
     data-logic="<?php echo $logic; ?>"
     data-haslogic="<?php echo $haslogic; ?>"
     class="cpf-section tm-row tm-cell <?php echo $column; ?> <?php echo $sections_class . $tm_product_id_class; ?>">
<?php

if ( isset( $sections_type ) && $sections_type == "popup" ) {
	$_popuplinkitle = (!empty( TM_EPO()->tm_epo_additional_options_text )) ? TM_EPO()->tm_epo_additional_options_text : __( 'Additional options', 'woocommerce-tm-extra-product-options' );
	if ( !empty ( $title ) ) {
		$_popuplinkitle = $title;
	}
	$_popuplink = '<a class="tm-section-link" href="#" data-title="' . esc_attr( $_popuplinkitle ) . '" data-sectionid="' . $uniqid . '">' . $_popuplinkitle . '</a>';
	echo $_popuplink . '<div class="tm-section-pop">';
}

$icon = '';
$toggler = '';
if ( $style == "box" ) {
	echo '<div class="tm-box">';
}
if ( $style == "collapse" || $style == "collapseclosed" || $style == "accordion" ) {
	echo '<div class="tm-collapse' . ($style == "accordion" ? ' tmaccordion' : '') . '">';
	$icon = '<span class="tcfa tcfa-angle-down tm-arrow"></span>';
	$toggler = ' tm-toggle';
	if ( $title == '' ) {
		$title = '&nbsp;';
	}
}

if ( (!empty( $title ) && $title_position != "disable") || (!empty( $description ) && ($description_position == "icontooltipright" | $description_position == "icontooltipleft")) ) {
	//if ($title!=''){
	echo '<' . $title_size;
	if ( !empty( $title_color ) ) {
		echo ' style="color:' . $title_color . '"';
	}
	$class = '';
	if ( !empty( $description ) && $description_position == "tooltip" ) {
		$class = " tm-tooltip";
	}
	if ( !empty( $title_position ) ) {
		$class .= " tm-" . $title_position;
	}
	if ( !empty( $description ) && !empty( $description_position ) && $description_position == "tooltip" ) {
		echo ' data-tm-tooltip-swatch="on"';
	}
	echo ' class="tm-epo-field-label tm-section-label' . $toggler . $class . '">';
	if ( $description_position == "icontooltipleft" ) {
		echo '<i data-tm-tooltip-swatch="on" class="tm-tooltip tc-tooltip tcfa tcfa-question-circle"></i>';
	}
	if ( !empty( $title ) && $title_position != "disable" ) {
		echo $title;
	} else {
		echo "&nbsp;";
	}
	if ( $description_position == "icontooltipright" ) {
		echo '<i data-tm-tooltip-swatch="on" class="tm-tooltip tc-tooltip tcfa tcfa-question-circle"></i>';
	}
	echo $icon . '</' . $title_size . '>';
}
if ( !empty( $description ) && (empty( $description_position ) || $description_position == "tooltip" || $description_position == "icontooltipright" | $description_position == "icontooltipleft") ) {
	echo '<div';
	if ( !empty( $description_color ) ) {
		echo ' style="color:' . $description_color . '"';
	}
	echo ' class="tm-description' . ($description_position == "tooltip" || $description_position == "icontooltipright" || $description_position == "icontooltipleft" ? " tm-tip-html" : "") . '">' . do_shortcode( $description ) . '</div>';

}
echo $divider;
if ( $style == "collapse" ) {
	echo '<div class="tm-collapse-wrap">';
}
if ( $style == "collapseclosed" ) {
	echo '<div class="tm-collapse-wrap closed">';
}
if ( $style == "accordion" ) {
	echo '<div class="tm-collapse-wrap closed">';
}
?>