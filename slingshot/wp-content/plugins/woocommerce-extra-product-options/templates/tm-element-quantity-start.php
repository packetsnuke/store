<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

if ( isset( $tm_element_settings ) && !empty( $quantity ) ) {

	$__min_value = $tm_element_settings['quantity_min'];
	$__max_value = $tm_element_settings['quantity_max'];
	$__step = floatval( $tm_element_settings['quantity_step'] );
	$__default_value = $tm_element_settings['quantity_default_value'];

	if ( isset( $_POST[ $name . '_quantity' ] ) ) {
		$__default_value = stripslashes( $_POST[ $name . '_quantity' ] );
	} elseif ( isset( $_GET[ $name . '_quantity' ] ) ) {
		$__default_value = stripslashes( $_GET[ $name . '_quantity' ] );
	}

	$__default_value = apply_filters( 'wc_epo_quantity_default_value', $__default_value, isset( $tm_element_settings ) ? $tm_element_settings : array(), isset( $value ) ? $value : NULL, isset( $choice_counter ) ? $choice_counter : NULL );

	if ( $__default_value == '' || !is_numeric( $__default_value ) ) {
		$__default_value = 1;
	}

	if ( $__min_value != '' ) {
		$__min_value = floatval( $__min_value );
	} else {
		$__min_value = 0;
	}
	if ( $__max_value != '' ) {
		$__max_value = floatval( $__max_value );
	}

	if ( empty( $__step ) ) {
		$__step = 'any';
	}

	if ( is_numeric( $__min_value ) && is_numeric( $__max_value ) ) {
		if ( $__min_value > $__max_value ) {
			$__max_value = $__max_value + $__step;
		}
		if ( $__default_value > $__max_value ) {
			$__default_value = $__max_value;
		}
		if ( $__default_value < $__min_value ) {
			$__default_value = $__min_value;
		}
	}
	$qty_html = '<div class="tm-quantity tm-' . esc_attr( $quantity ) . '">' .
		apply_filters( 'wc_epo_quantity_selector_before_input', '', isset( $tm_element_settings ) ? $tm_element_settings : array() ) .
		'<input type="number" step="' . esc_attr( $__step ) . '" ' .
		((is_numeric( $__min_value )) ? 'min="' . esc_attr( $__min_value ) . '"' : "") .
		((is_numeric( $__max_value )) ? 'max="' . esc_attr( $__max_value ) . '"' : "") .
		'name="' . esc_attr( $name . '_quantity' ) . '" ' .
		'value="' . esc_attr( $__default_value ) . '" ' .
		'title="' . esc_attr_x( 'Qty', 'element quantity input tooltip', 'woocommerce-tm-extra-product-options' ) . '" ' .
		'class="tm-qty tm-bsbb" size="4" />' .
		apply_filters( 'wc_epo_quantity_selector_after_input', '', isset( $tm_element_settings ) ? $tm_element_settings : array() ) .
		'</div>';
	$field_wrapper_html = '<div class="tm-field-display">';

	if ( strtolower( $quantity ) == "bottom" ) {
		echo $field_wrapper_html;
	} else {
		echo $qty_html . $field_wrapper_html;
	}
}