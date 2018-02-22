<?php

class TM_EPO_FIELDS_time extends TM_EPO_FIELDS {

	public function display_field( $element = array(), $args = array() ) {
		$tm_epo_global_datepicker_theme = !empty( TM_EPO()->tm_epo_global_datepicker_theme ) ? TM_EPO()->tm_epo_global_datepicker_theme : (isset( $element['theme'] ) ? $element['theme'] : "epo");
		$tm_epo_global_datepicker_size = !empty( TM_EPO()->tm_epo_global_datepicker_size ) ? TM_EPO()->tm_epo_global_datepicker_size : (isset( $element['theme_size'] ) ? $element['theme_size'] : "medium");
		$tm_epo_global_datepicker_position = !empty( TM_EPO()->tm_epo_global_datepicker_position ) ? TM_EPO()->tm_epo_global_datepicker_position : (isset( $element['theme_position'] ) ? $element['theme_position'] : "normal");

		return array(
			'textbeforeprice'     => isset( $element['text_before_price'] ) ? $element['text_before_price'] : "",
			'textafterprice'      => isset( $element['text_after_price'] ) ? $element['text_after_price'] : "",
			'hide_amount'         => isset( $element['hide_amount'] ) ? " " . $element['hide_amount'] : "",
			'time_format'         => !empty( $element['time_format'] ) ? $element['time_format'] : "HH:mm",
			'custom_time_format'  => !empty( $element['custom_time_format'] ) ? $element['custom_time_format'] : "",
			'min_time'            => isset( $element['min_time'] ) ? $element['min_time'] : "",
			'max_time'            => isset( $element['max_time'] ) ? $element['max_time'] : "",
			'tranlation_hour'     => !empty( $element['tranlation_hour'] ) ? $element['tranlation_hour'] : "",
			'tranlation_minute'   => !empty( $element['tranlation_minute'] ) ? $element['tranlation_minute'] : "",
			'tranlation_second'   => !empty( $element['tranlation_second'] ) ? $element['tranlation_second'] : "",
			'quantity'            => isset( $element['quantity'] ) ? $element['quantity'] : "",
			'time_theme'          => $tm_epo_global_datepicker_theme,
			'time_theme_size'     => $tm_epo_global_datepicker_size,
			'time_theme_position' => $tm_epo_global_datepicker_position,
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

}