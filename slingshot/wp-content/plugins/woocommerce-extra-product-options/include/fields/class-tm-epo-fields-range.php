<?php

class TM_EPO_FIELDS_range extends TM_EPO_FIELDS {

	public function display_field( $element = array(), $args = array() ) {
		return array(
			'textbeforeprice'   => isset( $element['text_before_price'] ) ? $element['text_before_price'] : "",
			'textafterprice'    => isset( $element['text_after_price'] ) ? $element['text_after_price'] : "",
			'hide_amount'       => isset( $element['hide_amount'] ) ? " " . $element['hide_amount'] : "",
			'min'               => isset( $element['min'] ) ? $element['min'] : "",
			'max'               => isset( $element['max'] ) ? $element['max'] : "",
			'step'              => isset( $element['step'] ) ? $element['step'] : "",
			'pips'              => isset( $element['pips'] ) ? $element['pips'] : "",
			'noofpips'          => isset( $element['pips'] ) ? $element['noofpips'] : "",
			'show_picker_value' => isset( $element['show_picker_value'] ) ? $element['show_picker_value'] : "",
			'quantity'          => isset( $element['quantity'] ) ? $element['quantity'] : "",
			'default_value'     => isset( $element['default_value'] ) ? $element['default_value'] : "",
		);
	}

	public function validate() {

		$passed = TRUE;
		$message = array();

		$quantity_once = FALSE;
		$min_quantity = isset( $this->element['quantity_min'] ) ? intval( $this->element['quantity_min'] ) : 0;
		if ( $min_quantity < 0 ) {
			$min_quantity = 0;
		}
		foreach ( $this->field_names as $attribute ) {
			if ( !$quantity_once && isset( $this->epo_post_fields[ $attribute ] ) && $this->epo_post_fields[ $attribute ] !== "" && isset( $this->epo_post_fields[ $attribute . '_quantity' ] ) && !(intval( $this->epo_post_fields[ $attribute . '_quantity' ] ) >= $min_quantity) ) {
				$passed = FALSE;
				$quantity_once = TRUE;
				$message[] = sprintf( __( 'The quantity for "%s" must be greater than %s', 'woocommerce-tm-extra-product-options' ), $this->element['label'], $min_quantity );
			}
			if ( $this->element['required'] ) {
				if ( !isset( $this->epo_post_fields[ $attribute ] ) || $this->epo_post_fields[ $attribute ] == "" ) {
					$passed = FALSE;
					$message[] = 'required';
					break;
				}
			}
		}

		return array( 'passed' => $passed, 'message' => $message );
	}

	public function add_cart_item_data_single() {
		if ( !$this->is_setup() ) {
			return FALSE;
		}
		if ( !empty( $this->key ) ) {

			$_price = TM_EPO()->calculate_price( $this->post_data, $this->element, $this->key, $this->attribute, $this->per_product_pricing, $this->cpf_product_price, $this->variation_id );

			return array(
				'mode' => 'builder',

				'cssclass'         => esc_html( $this->element['class'] ),
				'hidelabelincart'  => esc_html( $this->element['hide_element_label_in_cart'] ),
				'hidevalueincart'  => esc_html( $this->element['hide_element_value_in_cart'] ),
				'hidelabelinorder' => esc_html( $this->element['hide_element_label_in_order'] ),
				'hidevalueinorder' => esc_html( $this->element['hide_element_value_in_order'] ),

				'element' => $this->order_saved_element,

				'name'                => esc_html( $this->element['label'] ),
				'value'               => esc_html( $this->key ),
				'price'               => esc_attr( $_price ),
				'section'             => esc_html( $this->element['uniqid'] ),
				'section_label'       => esc_html( $this->element['label'] ),
				'currencies'          => isset( $this->element['currencies'] ) ? $this->element['currencies'] : array(),
				'price_per_currency'  => $this->fill_currencies(),
				'percentcurrenttotal' => isset( $this->post_data[ $this->attribute . '_hidden' ] ) ? 1 : 0,
				'quantity'            => isset( $this->post_data[ $this->attribute . '_quantity' ] ) ? $this->post_data[ $this->attribute . '_quantity' ] : 1,
			);

		}

		return FALSE;
	}
}