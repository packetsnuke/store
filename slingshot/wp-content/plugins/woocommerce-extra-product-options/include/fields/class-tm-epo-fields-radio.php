<?php

class TM_EPO_FIELDS_radio extends TM_EPO_FIELDS {

	public function display_field_pre( $element = array(), $args = array() ) {
		$this->items_per_row = $element['items_per_row'];
		$this->items_per_row_r = isset( $element['items_per_row_r'] ) ? $element['items_per_row_r'] : array();
		$this->grid_break = "";
		$this->_percent = 100;
		$this->_columns = 0;
		$container_css_id = 'element_';
		if ( isset( $element['container_css_id'] ) ) {
			$container_css_id = $element['container_css_id'];
		}
		if ( !isset( $args['product_id'] ) ) {
			$args['product_id'] = '';
		}

		if ( !empty( $this->items_per_row ) ) {
			if ( $this->items_per_row == "auto" ) {
				$this->items_per_row = 0;
				$this->css_string = ".tm-product-id-" . $args['product_id'] . " ." . $container_css_id . $args['element_counter'] . $args["form_prefix"] . " li{float:" . TM_EPO()->float_direction . " !important;width:auto !important;}";
			} else {
				$this->items_per_row = (float) $element['items_per_row'];
				$this->_percent = (float) (100 / $this->items_per_row);
				$this->css_string = ".tm-product-id-" . $args['product_id'] . " ." . $container_css_id . $args['element_counter'] . $args["form_prefix"] . " li{float:" . TM_EPO()->float_direction . " !important;width:" . $this->_percent . "% !important;}";
			}

			$this->css_string = str_replace( array( "\r", "\n" ), "", $this->css_string );
			TM_EPO()->inline_styles = TM_EPO()->inline_styles . $this->css_string;
		} else {
			$this->items_per_row = (float) $element['items_per_row'];
		}

		foreach ( $this->items_per_row_r as $key => $value ) {
			$before = "";
			$after = "}";
			$disable_clear = FALSE;
			if ( !empty( $value ) ) {
				if ( $key == "desktop" ) {
					$before = "";
					$after = "";
					$this->css_string = $before . ".tm-product-id-" . $args['product_id'] . " ." . $container_css_id . $args['element_counter'] . $args["form_prefix"] . " li:nth-child(n){clear:none !important;}" . $after;
					$this->css_string .= $before . ".tm-product-id-" . $args['product_id'] . " ." . $container_css_id . $args['element_counter'] . $args["form_prefix"] . " li:nth-child(" . (intval( $value )) . "n+1){clear:both !important;}" . $after;
					$this->css_string = str_replace( array( "\r", "\n" ), "", $this->css_string );
					TM_EPO()->inline_styles = TM_EPO()->inline_styles . $this->css_string;

				} else {
					$disable_clear = TRUE;
					switch ( $key ) {
						case 'tablets_galaxy'://800-1280
							$before = "@media only screen and (min-device-width : 800px) and (max-device-width : 1280px),only screen and (min-width : 800px) and (max-width : 1280px) {";
							break;
						case 'tablets'://768-1024
							$before = "@media only screen and (min-device-width : 768px) and (max-device-width : 1024px),only screen and (min-width : 768px) and (max-width : 1024px) {";
							break;
						case 'tablets_small'://481-767
							$before = "@media only screen and (min-device-width : 481px) and (max-device-width : 767px),only screen and (min-width : 481px) and (max-width : 767px) {";
							break;
						case 'iphone6_plus'://414-736
							$before = "@media only screen and (min-device-width: 414px) and (max-device-width: 736px) and (-webkit-min-device-pixel-ratio: 2),only screen and (min-width: 414px) and (max--width: 736px) {";
							break;
						case 'iphone6'://375-667
							$before = "@media only screen and (min-device-width: 375px) and (max-device-width: 667px) and (-webkit-min-device-pixel-ratio: 2),only screen and (min-width: 375px) and (max-width: 667px) {";
							break;
						case 'galaxy'://320-640
							$before = "@media only screen and (device-width: 320px) and (device-height: 640px) and (-webkit-min-device-pixel-ratio: 2),only screen and (width: 320px) and (height: 640px) {";
							break;
						case 'iphone5'://320-568
							$before = "@media only screen and (min-device-width: 320px) and (max-device-width: 568px) and (-webkit-min-device-pixel-ratio: 2), only screen and (min-width: 320px) and (max-width: 568px) {";
							break;
						case 'smartphones'://320-480
							$before = "@media only screen and (min-device-width : 320px) and (max-device-width : 480px), only screen and (min-width : 320px) and (max-width : 480px),, only screen and (max-width : 319px){";
							break;

						default:
							# code...
							break;
					}

					$thisitems_per_row = (float) $value;
					$this_percent = (float) (100 / $thisitems_per_row);
					$this->css_string = $before . ".tm-product-id-" . $args['product_id'] . " ." . $container_css_id . $args['element_counter'] . $args["form_prefix"] . " li{float:" . TM_EPO()->float_direction . " !important;width:" . $this_percent . "% !important;}" . $after;

					$this->css_string = str_replace( array( "\r", "\n" ), "", $this->css_string );
					TM_EPO()->inline_styles = TM_EPO()->inline_styles . $this->css_string;

					if ( $disable_clear ) {
						$this->css_string = $before . ".tm-product-id-" . $args['product_id'] . " ." . $container_css_id . $args['element_counter'] . $args["form_prefix"] . " li:nth-child(n){clear:none !important;}" . $after;
						$this->css_string .= $before . ".tm-product-id-" . $args['product_id'] . " ." . $container_css_id . $args['element_counter'] . $args["form_prefix"] . " li:nth-child(" . (intval( $value )) . "n+1){clear:both !important;}" . $after;
						//$this->css_string .= $before.".tm-product-id-".$args['product_id']." .".$container_css_id.$args['element_counter'].$args["form_prefix"]." li.cpf_clear.tm-per-row{clear:none !important;}".$after;
						$this->css_string = str_replace( array( "\r", "\n" ), "", $this->css_string );
						TM_EPO()->inline_styles = TM_EPO()->inline_styles . $this->css_string;
					}
				}

			}
		}

		$this->_default_value_counter = 0;
	}

	public function display_field( $element = array(), $args = array() ) {
		$this->_columns++;
		$this->grid_break = "";
		$default_value = isset( $element['default_value'] ) ? (($element['default_value'] !== "") ? ((int) $element['default_value'] == $this->_default_value_counter) : FALSE) : FALSE;

		if ( (float) $this->_columns > (float) $this->items_per_row && $this->items_per_row > 0 ) {
			//$this->grid_break=" cpf_clear";
			$this->_columns = 1;
		}

		$hexclass = "";
		$li_class = "";
		$search_for_color = $args['label'];
		if ( isset( $element['color'] ) ) {
			if ( ! is_array( $element['color'] ) ){
				$search_for_color = $element['color'];	
			}else{
				if ( isset( $element['color'][ $this->_default_value_counter ] ) ){
					$search_for_color = $element['color'][ $this->_default_value_counter ];		
				}
			}
			if ( empty( $search_for_color ) ) {
				$search_for_color = 'transparent';
			}
		}
		if ( $search_for_color == 'transparent' || preg_match( '/#([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?\b/', $search_for_color ) ) {
			$tmhexcolor = 'tmhexcolor_' . $args['element_counter'] . "_" . $args['field_counter'] . "_" . $args['tabindex'] . $args['form_prefix'];
			$litmhexcolor = 'tm-li-unique-' . $args['element_counter'] . "-" . $args['field_counter'] . "-" . $args['tabindex'] . $args['form_prefix'];
			$hexclass = $tmhexcolor;
			$this->css_string = "label ." . $tmhexcolor . " + .tmhexcolorimage{background-color:" . $search_for_color . " !important;}";
			if ( !empty( $element['item_width'] ) ) {
				if ( is_numeric( $element['item_width'] ) ) {
					$element['item_width'] .= "px";
				}
				$this->css_string .= "." . $litmhexcolor . " label{display: inline-block !important;width:" . $element['item_width'] . " !important;min-width:" . $element['item_width'] . " !important;max-width:" . $element['item_width'] . " !important;}";
				$this->css_string .= "label ." . $tmhexcolor . " + img{display: inline-block !important;width:" . $element['item_width'] . " !important;min-width:" . $element['item_width'] . " !important;max-width:" . $element['item_width'] . " !important;}";
				$this->css_string .= "label ." . $tmhexcolor . " + span.tmhexcolorimage{display: inline-block !important;width:" . $element['item_width'] . " !important;min-width:" . $element['item_width'] . " !important;max-width:" . $element['item_width'] . " !important;}";
			}
			if ( !empty( $element['item_height'] ) ) {
				if ( is_numeric( $element['item_height'] ) ) {
					$element['item_height'] .= "px";
				}
				$this->css_string .= "." . $litmhexcolor . " label{display: inline-block !important;height:" . $element['item_height'] . " !important;min-height:" . $element['item_height'] . " !important;max-height:" . $element['item_height'] . " !important;}";
				$this->css_string .= "label ." . $tmhexcolor . " + img{display: inline-block !important;height:" . $element['item_height'] . " !important;min-height:" . $element['item_height'] . " !important;max-height:" . $element['item_height'] . " !important;}";
				$this->css_string .= "label ." . $tmhexcolor . " + span.tmhexcolorimage{display: inline-block !important;height:" . $element['item_height'] . " !important;min-height:" . $element['item_height'] . " !important;max-height:" . $element['item_height'] . " !important;}";
			}
			if ( !empty( $element['item_width'] ) || !empty( $element['item_height'] ) ) {
				$this->css_string .= ".tmhexcolorimage-li.tm-li-unique-" . $args['element_counter'] . "-" . $args['field_counter'] . "-" . $args['tabindex'] . $args['form_prefix'] . "{display: inline-block;width:auto !important;oveflow:hidden;}";
				$li_class .= "tmhexcolorimage-li tm-li-unique-" . $args['element_counter'] . "-" . $args['field_counter'] . "-" . $args['tabindex'] . $args['form_prefix'];
			} else {
				$li_class .= "tmhexcolorimage-li-nowh";
			}
			$this->css_string = str_replace( array( "\r", "\n" ), "", $this->css_string );
			TM_EPO()->inline_styles = TM_EPO()->inline_styles . $this->css_string;
		}

		$_css_class = !empty( $element['class'] ) ? $element['class'] . ' ' . $hexclass : "" . $hexclass;
		$css_class = apply_filters( 'wc_epo_multiple_options_css_class', '', $element, $this->_default_value_counter );
		if ( $css_class !== '' ) {
			$css_class = ' ' . $css_class;
		}
		$css_class = $_css_class . $css_class;

		if ( !empty( $element['use_colors'] ) && empty( $element['use_images'] ) ){
			$element['use_images'] = $element['use_colors'];
			if ( $element['use_images'] == 'color' ){
				$element['use_images'] = 'images';
			}
		}
		
		$display = array(
			'li_class'              => $li_class,
			'class'                 => $css_class,
			'label'                 => wptexturize( apply_filters( 'woocommerce_tm_epo_option_name', $args['label'], $element, $this->_default_value_counter ) ),
			'value'                 => esc_attr( $args['value'] ),
			'id'                    => 'tmcp_choice_' . $args['element_counter'] . "_" . $args['field_counter'] . "_" . $args['tabindex'] . $args['form_prefix'],
			'textbeforeprice'       => isset( $element['text_before_price'] ) ? $element['text_before_price'] : "",
			'textafterprice'        => isset( $element['text_after_price'] ) ? $element['text_after_price'] : "",
			'hide_amount'           => isset( $element['hide_amount'] ) ? " " . $element['hide_amount'] : "",
			'use_images'            => $element['use_images'],
			'use_colors'            => isset($element['use_colors'])?$element['use_colors']:'',
			'use_lightbox'          => isset( $element['use_lightbox'] ) ? $element['use_lightbox'] : "",
			'use_url'               => $element['use_url'],
			'grid_break'            => $this->grid_break,
			'items_per_row'         => $this->items_per_row,
			'items_per_row_r'       => $this->items_per_row_r,
			'percent'               => $this->_percent,
			'image'                 => isset( $element['images'][ $args['field_counter'] ] ) ? $element['images'][ $args['field_counter'] ] : "",
			'imagec'                => isset( $element['imagesc'][ $args['field_counter'] ] ) ? $element['imagesc'][ $args['field_counter'] ] : "",
			'imagep'                => isset( $element['imagesp'][ $args['field_counter'] ] ) ? $element['imagesp'][ $args['field_counter'] ] : "",
			'imagel'                => isset( $element['imagesl'][ $args['field_counter'] ] ) ? $element['imagesl'][ $args['field_counter'] ] : "",
			'url'                   => isset( $element['url'][ $args['field_counter'] ] ) ? $element['url'][ $args['field_counter'] ] : "",
			'limit'                 => empty( $element['limit'] ) ? "" : $element['limit'],
			'exactlimit'            => empty( $element['exactlimit'] ) ? "" : $element['exactlimit'],
			'minimumlimit'          => empty( $element['minimumlimit'] ) ? "" : $element['minimumlimit'],
			'swatchmode'            => empty( $element['swatchmode'] ) ? "" : $element['swatchmode'],
			'clear_options'         => empty( $element['clear_options'] ) ? "" : $element['clear_options'],
			'show_label'            => empty( $element['show_label'] ) ? "" : $element['show_label'],
			'tm_epo_no_lazy_load'   => TM_EPO()->tm_epo_no_lazy_load,
			'changes_product_image' => empty( $element['changes_product_image'] ) ? "" : $element['changes_product_image'],
			'default_value'         => $default_value,
			'quantity'              => isset( $element['quantity'] ) ? $element['quantity'] : "",
			'choice_counter'        => $this->_default_value_counter,
		);
		if ( isset( $element['color'] ) ) {
			$display["color"] = $element['color'];
		}

		$this->_default_value_counter++;

		return $display;

	}

	public function validate() {

		$passed = TRUE;
		$message = array();

		$min_quantity = isset( $this->element['quantity_min'] ) ? intval( $this->element['quantity_min'] ) : 0;
		if ( $min_quantity < 0 ) {
			$min_quantity = 0;
		}

		foreach ( $this->tmcp_attributes as $k => $attribute ) {
			if ( isset( $this->epo_post_fields[ $attribute ] ) && isset( $this->epo_post_fields[ $attribute . '_quantity' ] ) && !(intval( $this->epo_post_fields[ $attribute . '_quantity' ] ) >= $min_quantity) ) {
				$passed = FALSE;
				$message[] = sprintf( __( 'The quantity for "%s" must be greater than %s', 'woocommerce-tm-extra-product-options' ), $this->element['options'][ $this->epo_post_fields[ $attribute ] ], $min_quantity );
				break;
			}
			if ( $this->element['required'] ) {
				$is_cart_fee = $this->element['is_cart_fee'][ $k ];
				$is_fee = $this->element['is_fee'][ $k ];
				if ( $is_cart_fee ) {
					if ( !isset( $this->epo_post_fields[ $this->tmcp_attributes_fee[ $k ] ] ) ) {
						$passed = FALSE;
						$message[] = 'required';
						break;
					}
				} elseif ( $is_fee ) {
					if ( !isset( $this->epo_post_fields[ $this->tmcp_attributes_subscription_fee[ $k ] ] ) ) {
						$passed = FALSE;
						$message[] = 'required';
						break;
					}
				} else {
					if ( !isset( $this->epo_post_fields[ $attribute ] ) ) {
						$passed = FALSE;
						$message[] = 'required';
						break;
					}
				}
			}
		}

		return array( 'passed' => $passed, 'message' => $message );
	}
}