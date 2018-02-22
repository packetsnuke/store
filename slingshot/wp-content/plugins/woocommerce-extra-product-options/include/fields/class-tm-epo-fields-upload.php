<?php

class TM_EPO_FIELDS_upload extends TM_EPO_FIELDS {
 
	public function display_field( $element = array(), $args = array() ) {
		return array(
			'max_size'        => size_format( wp_max_upload_size() ),
			'style'           => isset( $element['button_type'] ) ? $element['button_type'] : "",
			'textbeforeprice' => isset( $element['text_before_price'] ) ? $element['text_before_price'] : "",
			'textafterprice'  => isset( $element['text_after_price'] ) ? $element['text_after_price'] : "",
			'hide_amount'     => isset( $element['hide_amount'] ) ? " " . $element['hide_amount'] : "",
			'quantity'        => isset( $element['quantity'] ) ? $element['quantity'] : "",
		);
	}

	public function validate() {

		$passed = TRUE;
		$message = array();

		if ( $this->element['required'] ) {
			foreach ( $this->field_names as $attribute ) {
				if ( empty( $_FILES[ $attribute ] ) || empty( $_FILES[ $attribute ]['name'] ) ) {
					$passed = FALSE;
					$message[] = 'required';
					break;
				} else {
					$ext = strtolower( pathinfo( $_FILES[ $attribute ]['name'], PATHINFO_EXTENSION ) );
					$check = TRUE;
					if ( apply_filters( 'wc_epo_no_upload_to_png', true ) &&  in_array( $ext, array( 'jpg', 'png', 'gif' ) ) ) {
						$check = TM_EPO_HELPER()->upload_to_png( $_FILES[ $attribute ]['tmp_name'], $_FILES[ $attribute ]['tmp_name'] );

					}
					if ( $check === FALSE ) {
						$passed = FALSE;
						$message[] = sprintf( __( "%s is not a valid image file!", 'woocommerce-tm-extra-product-options' ), $_FILES[ $attribute ]['name'] );
						break;
					}

				}
			}
		}

		return array( 'passed' => $passed, 'message' => $message );
	}

	public function add_cart_item_data_single() {
		$_price = TM_EPO()->calculate_price( $this->post_data, $this->element, $this->key, $this->attribute, $this->per_product_pricing, $this->cpf_product_price, $this->variation_id );
		if ( empty( $this->key ) ) {
			$_price = 0;
		}
		if ( !empty( $_FILES[ $this->attribute ] ) && !empty( $_FILES[ $this->attribute ]['name'] ) ) {
			$upload = TM_EPO()->upload_file( $_FILES[ $this->attribute ] );

			if ( empty( $upload['error'] ) && !empty( $upload['file'] ) ) {
				$value = wc_clean( $upload['url'] );
				if ( empty( $upload['tc'] ) ) {
					wc_add_notice( __( "Upload successful", 'woocommerce-tm-extra-product-options' ), 'success' );
				}

				return array(
					'mode' => 'builder',

					'cssclass'         => esc_html( $this->element['class'] ),
					'hidelabelincart'  => esc_html( $this->element['hide_element_label_in_cart'] ),
					'hidevalueincart'  => esc_html( $this->element['hide_element_value_in_cart'] ),
					'hidelabelinorder' => esc_html( $this->element['hide_element_label_in_order'] ),
					'hidevalueinorder' => esc_html( $this->element['hide_element_value_in_order'] ),
					'element'          => $this->order_saved_element,

					'name'                => esc_html( $this->element['label'] ),
					'value'               => esc_html( $value ),
					'display'             => esc_html( TM_EPO()->tm_order_item_display_meta_value( $value, 1 ) ),
					'price'               => esc_attr( $_price ),
					'section'             => esc_html( $this->element['uniqid'] ),
					'section_label'       => esc_html( $this->element['label'] ),
					'percentcurrenttotal' => isset( $this->post_data[ $this->attribute . '_hidden' ] ) ? 1 : 0,
					'currencies'          => isset( $this->element['currencies'] ) ? $this->element['currencies'] : array(),
					'price_per_currency'  => $this->fill_currencies(),
					'quantity'            => 1,
				);
			} else {
				wc_add_notice( $upload['error'], 'error' );
			}
		}

		return FALSE;
	}

	public function add_cart_item_data_cart_fees_single() {

		if ( !empty( $_FILES[ $this->attribute ] ) && !empty( $_FILES[ $this->attribute ]['name'] ) ) {
			$upload = TM_EPO()->upload_file( $_FILES[ $this->attribute ] );
			if ( empty( $upload['error'] ) && !empty( $upload['file'] ) ) {
				$value = wc_clean( $upload['url'] );
				if ( empty( $upload['tc'] ) ) {
					wc_add_notice( __( "Upload successful", 'woocommerce-tm-extra-product-options' ), 'success' );
				}
				$_price = TM_EPO()->calculate_price( $this->post_data, $this->element, $this->key, $this->attribute, $this->per_product_pricing, $this->cpf_product_price, $this->variation_id );
				if ( empty( $this->key ) ) {
					$_price = 0;
				}
				return array(
					'mode'                           => 'builder',
					'cssclass'                       => esc_html( $this->element['class'] ),
					'include_tax_for_fee_price_type' => esc_html( $this->element['include_tax_for_fee_price_type'] ),
					'tax_class_for_fee_price_type'   => esc_html( $this->element['tax_class_for_fee_price_type'] ),
					'hidelabelincart'                => esc_html( $this->element['hide_element_label_in_cart'] ),
					'hidevalueincart'                => esc_html( $this->element['hide_element_value_in_cart'] ),
					'hidelabelinorder'               => esc_html( $this->element['hide_element_label_in_order'] ),
					'hidevalueinorder'               => esc_html( $this->element['hide_element_value_in_order'] ),
					'element'                        => $this->order_saved_element,
					'name'                           => esc_html( $this->element['label'] ),
					'value'                          => esc_html( $value ),
					'display' 						 => esc_html( TM_EPO()->tm_order_item_display_meta_value( $value, 0 ) ),
					'price'                          => TM_EPO()->cacl_fee_price( $_price, $this->product_id, $this->element, $this->attribute ),
					'section'                        => esc_html( $this->element['uniqid'] ),
					'section_label'                  => esc_html( $this->element['label'] ),
					'percentcurrenttotal'            => 0,
					'currencies'                     => isset( $this->element['currencies'] ) ? $this->element['currencies'] : array(),
					'price_per_currency'             => $this->fill_currencies(),
					'quantity'                       => 1,

					'cart_fees' => 'single',
				);
			}
		}

		return FALSE;
	}

}