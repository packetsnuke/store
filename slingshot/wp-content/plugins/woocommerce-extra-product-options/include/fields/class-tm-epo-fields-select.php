<?php

class TM_EPO_FIELDS_select extends TM_EPO_FIELDS {

	public function display_field( $element = array(), $args = array() ) {
		$changes_product_image = empty( $element['changes_product_image'] ) ? "" : $element['changes_product_image'];
		$display = array(
			'options'               => '',
			'options_array'         => array(),
			'use_url'               => isset( $element['use_url'] ) ? $element['use_url'] : "",
			'textbeforeprice'       => isset( $element['text_before_price'] ) ? $element['text_before_price'] : "",
			'textafterprice'        => isset( $element['text_after_price'] ) ? $element['text_after_price'] : "",
			'hide_amount'           => isset( $element['hide_amount'] ) ? " " . $element['hide_amount'] : "",
			'changes_product_image' => $changes_product_image,
			'quantity'              => isset( $element['quantity'] ) ? $element['quantity'] : "",
		);

		$_default_value_counter = 0;
		$display['default_value_counter'] = FALSE;
		if ( !isset( $element['placeholder'] ) || (isset( $element['placeholder'] ) && $element['placeholder']!=='') ) {
			$display['options'] .= '<option value="" data-price="" data-rules="" data-rulestype="">' .
				wptexturize( apply_filters( 'woocommerce_tm_epo_option_name', $element['placeholder'], $element, $_default_value_counter ) ) . '</option>';
		}

		$selected_value = '';
		if ( TM_EPO()->tm_epo_global_reset_options_after_add == "no" && isset( $this->post_data[ 'tmcp_' . $args['name_inc'] ] ) ) {
			$selected_value = $this->post_data[ 'tmcp_' . $args['name_inc'] ];
		} elseif ( isset( $_GET[ 'tmcp_' . $args['name_inc'] ] ) ) {
			$selected_value = $_GET[ 'tmcp_' . $args['name_inc'] ];
		} elseif ( empty( $this->post_data ) || TM_EPO()->tm_epo_global_reset_options_after_add == "yes" ) {
			$selected_value = -1;
		}

		$selected_value = apply_filters( 'wc_epo_default_value', $selected_value, $element );

		foreach ( $element['options'] as $value => $label ) {
			$default_value = isset( $element['default_value'] )
				?
				(($element['default_value'] !== "")
					? ((int) $element['default_value'] == $_default_value_counter)
					: FALSE)
				: FALSE;

			$selected = FALSE;

			if ( $selected_value == -1 ) {
				if ( (empty( $this->post_data ) || TM_EPO()->tm_epo_global_reset_options_after_add == "yes") && isset( $default_value ) ) {
					if ( $default_value ) {
						$selected = TRUE;
					}
				}
			} else {
				if ( $default_value && !empty( $element['default_value_override'] ) && isset( $element['default_value'] ) ) {
					$selected = TRUE;
				} elseif ( esc_attr( stripcslashes( $selected_value ) ) == esc_attr( ($value) ) ) {
					$selected = TRUE;
				}
			}
			if ( $selected ) {
				$display['default_value_counter'] = $value;
			}

			$data_url = isset( $element['url'][ $_default_value_counter ] ) ? $element['url'][ $_default_value_counter ] : "";

			$css_class = apply_filters( 'wc_epo_multiple_options_css_class', '', $element, $_default_value_counter );
			if ( $css_class !== '' ) {
				$css_class = ' ' . $css_class;
			}

			$image_variations = array();
			if ($changes_product_image){
				$image_link = "";
				$attachment_id = TM_EPO_HELPER()->get_attachment_id( $image_link );
				$attachment_id = ($attachment_id) ? $attachment_id : 0;
				$attachment_object = get_post( $attachment_id );
				$full_src = wp_get_attachment_image_src( $attachment_id, 'large' );
				$image_title = get_the_title( $attachment_id );
				$image_alt = trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', TRUE ) ) );
				$image_srcset = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, 'shop_single' ) : FALSE;
				$image_sizes = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, 'shop_single' ) : FALSE;
				$image_caption = $attachment_object->post_excerpt;
				$image_variations['image'] = array(
					'image_link'    => $image_link,
					'image_title'   => $image_title,
					'image_alt'     => $image_alt,
					'image_srcset'  => $image_srcset,
					'image_sizes'   => $image_sizes,
					'image_caption' => $image_caption,
					'image_id'      => $attachment_id,
					'full_src'      => $full_src[0],
					'full_src_w'    => $full_src[1],
					'full_src_h'    => $full_src[2],
				);

				$image_link = isset( $element['imagesp'][ $_default_value_counter ] ) ? $element['imagesp'][ $_default_value_counter ] : "";
				$attachment_id = TM_EPO_HELPER()->get_attachment_id( $image_link );
				$attachment_object = get_post( $attachment_id );
				$full_src = wp_get_attachment_image_src( $attachment_id, 'large' );
				$image_title = get_the_title( $attachment_id );
				$image_alt = trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', TRUE ) ) );
				$image_srcset = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, 'shop_single' ) : FALSE;
				$image_sizes = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, 'shop_single' ) : FALSE;
				$image_caption = $attachment_object->post_excerpt;
				$image_variations['imagep'] = array(
					'image_link'    => $image_link,
					'image_title'   => $image_title,
					'image_alt'     => $image_alt,
					'image_srcset'  => $image_srcset,
					'image_sizes'   => $image_sizes,
					'image_caption' => $image_caption,
					'image_id'      => $attachment_id,
					'full_src'      => $full_src[0],
					'full_src_w'    => $full_src[1],
					'full_src_h'    => $full_src[2],
				);

			}

			$option = '<option ' .
				selected( $selected, TRUE, 0 ) .
				' value="' . esc_attr( $value ) . '"' .
				' class="tc-multiple-option tc-select-option' . esc_attr( $css_class ) . '"' .
				(!empty( $data_url ) ? ' data-url="' . esc_attr( $data_url ) . '"' : '') .
				' data-imagep="' . (isset( $element['imagesp'][ $_default_value_counter ] ) ? $element['imagesp'][ $_default_value_counter ] : "") . '"' .
				' data-price="' . (isset( $element['rules_filtered'][ $value ][0] ) ? $element['rules_filtered'][ $value ][0] : 0) . '"' .
				(isset( $element['cdescription'] ) ?
					isset( $element['cdescription'][ $_default_value_counter ] ) ?
						' data-tm-tooltip-html="' . esc_attr( do_shortcode( $element['cdescription'][ $_default_value_counter ] ) ) . '"'
						: ''
					: '') .
				' data-image-variations="' . htmlspecialchars( json_encode( $image_variations ) ) . '"' .
				' data-rules="' . (isset( $element['rules_filtered'][ $value ] ) ? esc_html( json_encode( ($element['rules_filtered'][ $value ]) ) ) : '') . '"' .
				' data-original-rules="' . (isset( $element['original_rules_filtered'][ $value ] ) ? esc_html( json_encode( ($element['original_rules_filtered'][ $value ]) ) ) : '') . '"' .
				' data-rulestype="' . (isset( $element['rules_type'][ $value ] ) ? esc_html( json_encode( ($element['rules_type'][ $value ]) ) ) : '') . '">' .
				wptexturize( apply_filters( 'woocommerce_tm_epo_option_name', $label, $element, $_default_value_counter, $value, $label ) ) . '</option>';

			$option = apply_filters( 'wc_epo_select_options', $option, $element, $_default_value_counter );
			$display['options'] .= apply_filters( 'wc_epo_multiple_options', $option, $element, $_default_value_counter );

			$display['options_array'][] = array(
				"value"            => $value,
				"data_url"         => $data_url,
				"image_variations" => htmlspecialchars( json_encode( $image_variations ) ),
				"imagep"           => (isset( $element['imagesp'][ $_default_value_counter ] ) ? $element['imagesp'][ $_default_value_counter ] : ""),
				"price"            => (isset( $element['rules_filtered'][ $value ][0] ) ? $element['rules_filtered'][ $value ][0] : 0),
				"cdescription"     => (isset( $element['cdescription'] ) ? isset( $element['cdescription'][ $_default_value_counter ] ) ? $element['cdescription'][ $_default_value_counter ] : '' : ''),
				"rules"            => (isset( $element['rules_filtered'][ $value ] ) ? esc_html( json_encode( ($element['rules_filtered'][ $value ]) ) ) : ''),
				"original_rules"   => (isset( $element['original_rules_filtered'][ $value ] ) ? esc_html( json_encode( ($element['original_rules_filtered'][ $value ]) ) ) : ''),
				"rulestype"        => (isset( $element['rules_type'][ $value ] ) ? esc_html( json_encode( ($element['rules_type'][ $value ]) ) ) : ''),
				"label"            => apply_filters( 'woocommerce_tm_epo_option_name', $label, $element, $_default_value_counter, $value, $label ),
			);

			$_default_value_counter++;
		}

		return $display;
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
				$message[] = sprintf( __( 'The quantity for "%s" must be greater than %s', 'woocommerce-tm-extra-product-options' ), $this->element['options'][ $this->epo_post_fields[ $attribute ] ], $min_quantity );
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