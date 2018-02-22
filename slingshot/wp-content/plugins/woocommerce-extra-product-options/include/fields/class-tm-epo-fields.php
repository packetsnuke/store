<?php

class TM_EPO_FIELDS {

	public $product_id;
	public $element;
	public $order_saved_element;
	public $attribute;
	public $key;
	public $per_product_pricing;
	public $cpf_product_price;
	public $variation_id;
	public $post_data;
	public $holder;
	public $holder_cart_fees;
	public $holder_subscription_fees;

	public $epo_post_fields;
	public $loop;
	public $form_prefix;
	public $tmcp_attributes;
	public $tmcp_attributes_fee;
	public $tmcp_attributes_subscription_fee;
	public $field_names;

	private $setup = FALSE;

	public function __construct( $product_id = FALSE, $element = FALSE, $per_product_pricing = FALSE, $cpf_product_price = FALSE, $variation_id = FALSE, $post_data = NULL ) {
		if ( is_null( $post_data ) && isset( $_POST ) ) {
			$post_data = $_POST;
		}
		if ( empty( $post_data ) && isset( $_REQUEST['tcajax'] ) ) {
			$post_data = $_REQUEST;
		}
		$this->post_data = $post_data;
		if ( $product_id !== FALSE ) {
			$this->product_id = $product_id;
			$this->element = $element;
			$this->order_saved_element = array(
				'type'       => $element['type'],
				'rules_type' => $element['rules_type'],
				'_'          => array( 'price_type' => $element['_']['price_type'] ),
			);
			$this->per_product_pricing = $per_product_pricing;
			$this->cpf_product_price = $cpf_product_price;
			$this->variation_id = $variation_id;


			$this->holder = TM_EPO()->tm_builder_elements[ $this->element['type'] ]['type'];
			$this->holder_cart_fees = TM_EPO()->tm_builder_elements[ $this->element['type'] ]['fee_type'];
			$this->holder_subscription_fees = TM_EPO()->tm_builder_elements[ $this->element['type'] ]['subscription_fee_type'];

			$this->setup = TRUE;
		}
	}

	public function is_setup() {
		return $this->setup;
	}

	public function display_field( $element = array(), $args = array() ) {
		return array();
	}

	public function display_field_pre( $element = array(), $args = array() ) {

	}

	public final function validate_field( $epo_post_fields = FALSE, $element = FALSE, $loop = FALSE, $form_prefix = FALSE ) {
		$this->epo_post_fields = $epo_post_fields;
		$this->element = $element;
		$this->loop = $loop;
		$this->form_prefix = $form_prefix;
		$this->tmcp_attributes = TM_EPO()->translate_fields( $element['options'], $element['type'], $loop, $form_prefix );
		$this->tmcp_attributes_fee = TM_EPO()->translate_fields( $element['options'], $element['type'], $loop, $form_prefix, TM_EPO()->cart_fee_name );
		$this->tmcp_attributes_subscription_fee = TM_EPO()->translate_fields( $element['options'], $element['type'], $loop, $form_prefix, TM_EPO()->fee_name );

		if ( $this->element['is_cart_fee'] ) {
			$this->field_names = $this->tmcp_attributes_fee;
		} elseif ( $this->element['is_fee'] ) {
			$this->field_names = $this->tmcp_attributes_subscription_fee;
		} else {
			$this->field_names = $this->tmcp_attributes;
		}

		return $this->validate();
	}

	public function validate() {
		return array( 'passed' => TRUE, 'message' => FALSE );
	}

	public final function add_cart_item_data( $attribute = FALSE, $key = FALSE ) {
		if ( !$this->setup ) {
			return FALSE;
		}
		$this->attribute = $attribute;
		$this->key = $key;
		if ( $this->holder == "single" || $this->holder == "multipleallsingle" ) {
			return $this->add_cart_item_data_single();
		} elseif ( $this->holder == "multiple" || $this->holder == "multipleall" || $this->holder == "multiplesingle" ) {
			return $this->add_cart_item_data_multiple();
		}

		return FALSE;
	}

	public final function add_cart_item_data_cart_fees( $attribute = FALSE, $key = FALSE ) {
		if ( !$this->setup ) {
			return FALSE;
		}
		$this->attribute = $attribute;
		$this->key = $key;
		if ( $this->holder_cart_fees == "single" ) {
			return $this->add_cart_item_data_cart_fees_single();
		} elseif ( $this->holder_cart_fees == "multiple" ) {
			return $this->add_cart_item_data_cart_fees_multiple();
		}

		return FALSE;

	}

	public final function add_cart_item_data_subscription_fees( $attribute = FALSE, $key = FALSE ) {
		if ( !$this->setup ) {
			return FALSE;
		}
		$this->attribute = $attribute;
		$this->key = $key;
		if ( $this->holder_subscription_fees == "single" ) {
			return $this->add_cart_item_data_subscription_fees_single();
		} elseif ( $this->holder_subscription_fees == "multiple" ) {
			return $this->add_cart_item_data_subscription_fees_multiple();
		}

		return FALSE;
	}

	public function fill_currencies() {
		$price_per_currencies = isset( $this->element['price_per_currencies'] ) ? $this->element['price_per_currencies'] : array();
		$price_per_currency = array();
		$current_currency = tc_get_woocommerce_currency();
		foreach ( $price_per_currencies as $currency => $price_rule ) {
			$copy_element = $this->element;
			$copy_element['price_rules'] = $price_rule;
			$currency_price = TM_EPO()->calculate_price( $this->post_data, $copy_element, $this->key, $this->attribute, $this->per_product_pricing, $this->cpf_product_price, $this->variation_id, '', $currency, $current_currency, $price_per_currencies );
			$price_per_currency[ $currency ] = $currency_price;
		}

		return $price_per_currency;
	}

	public function add_cart_item_data_single() {
		if ( !$this->setup ) {
			return FALSE;
		}
		if ( isset( $this->key ) && $this->key != '' ) {

			$_price = TM_EPO()->calculate_price( $this->post_data, $this->element, $this->key, $this->attribute, $this->per_product_pricing, $this->cpf_product_price, $this->variation_id );

			return apply_filters( 'wc_epo_add_cart_item_data_single', array(
				'mode'                => 'builder',
				'cssclass'            => esc_html( $this->element['class'] ),
				'hidelabelincart'     => esc_html( $this->element['hide_element_label_in_cart'] ),
				'hidevalueincart'     => esc_html( $this->element['hide_element_value_in_cart'] ),
				'hidelabelinorder'    => esc_html( $this->element['hide_element_label_in_order'] ),
				'hidevalueinorder'    => esc_html( $this->element['hide_element_value_in_order'] ),
				'element'             => $this->order_saved_element,
				'name'                => esc_html( $this->element['label'] ),
				'value'               => esc_html( $this->key ),
				'price'               => esc_attr( $_price ),
				'section'             => esc_html( $this->element['uniqid'] ),
				'section_label'       => esc_html( $this->element['label'] ),
				'percentcurrenttotal' => isset( $this->post_data[ $this->attribute . '_hidden' ] ) ? 1 : 0,
				'currencies'          => isset( $this->element['currencies'] ) ? $this->element['currencies'] : array(),
				'price_per_currency'  => $this->fill_currencies(),
				'quantity'            => isset( $this->post_data[ $this->attribute . '_quantity' ] ) ? $this->post_data[ $this->attribute . '_quantity' ] : 1,
			), $this );
		}

		return FALSE;
	}

	public function add_cart_item_data_multiple() {
		if ( !$this->setup ) {
			return FALSE;
		}
		/* select placeholder check */
		if ( isset( $this->element['options'][ esc_attr( $this->key ) ] ) ) {
			$_price = TM_EPO()->calculate_price( $this->post_data, $this->element, $this->key, $this->attribute, $this->per_product_pricing, $this->cpf_product_price, $this->variation_id );

			$use_images = !empty( $this->element['use_images'] ) ? $this->element['use_images'] : "";			
			if ( $use_images ) {
				$_image_key = array_search( $this->key, $this->element['option_values'] );
				if ( $_image_key === NULL || $_image_key === FALSE ) {
					$_image_key = FALSE;
				}
			} else {
				$_image_key = FALSE;
			}

			$use_colors = !empty( $this->element['use_colors'] ) ? $this->element['use_colors'] : "";
			if ( $use_colors ) {
				$_color_key = array_search( $this->key, $this->element['option_values'] );
				if ( $_color_key === NULL || $_color_key === FALSE ) {
					$_color_key = FALSE;
				}
			} else {
				$_color_key = FALSE;
			}
			
			$changes_product_image = !empty( $this->element['changes_product_image'] ) ? $this->element['changes_product_image'] : "";
			if ( $changes_product_image ) {
				$c_image_key = array_search( $this->key, $this->element['option_values'] );
				if ( $c_image_key === NULL || $c_image_key === FALSE ) {
					$c_image_key = FALSE;
				}
			} else {
				$c_image_key = FALSE;
			}

			return apply_filters( 'wc_epo_add_cart_item_data_multiple', array(
				'mode'                => 'builder',
				'cssclass'            => esc_html( $this->element['class'] ),
				'hidelabelincart'     => esc_html( $this->element['hide_element_label_in_cart'] ),
				'hidevalueincart'     => esc_html( $this->element['hide_element_value_in_cart'] ),
				'hidelabelinorder'    => esc_html( $this->element['hide_element_label_in_order'] ),
				'hidevalueinorder'    => esc_html( $this->element['hide_element_value_in_order'] ),
				'element'             => $this->order_saved_element,
				'name'                => esc_html( $this->element['label'] ),
				'value'               => esc_html( $this->element['options'][ esc_attr( $this->key ) ] ),
				'price'               => esc_attr( $_price ),
				'section'             => esc_html( $this->element['uniqid'] ),
				'section_label'       => esc_html( $this->element['label'] ),
				'percentcurrenttotal' => isset( $this->post_data[ $this->attribute . '_hidden' ] ) ? 1 : 0,
				'currencies'          => isset( $this->element['currencies'] ) ? $this->element['currencies'] : array(),
				'price_per_currency'  => $this->fill_currencies(),
				'quantity'            => isset( $this->post_data[ $this->attribute . '_quantity' ] ) ? $this->post_data[ $this->attribute . '_quantity' ] : 1,

				'multiple'              => '1',
				'key'                   => esc_attr( $this->key ),
				'use_images'            => $use_images,
				'use_colors'            => $use_colors,
				'changes_product_image' => $changes_product_image,
				'imagesp'               => ($c_image_key !== FALSE && isset( $this->element['imagesp'][ $c_image_key ] )) ? $this->element['imagesp'][ $c_image_key ] : "",
				'images'                => ($_image_key !== FALSE && isset( $this->element['images'][ $_image_key ] )) ? $this->element['images'][ $_image_key ] : "",
				'color'                 => ($_color_key !== FALSE && isset( $this->element['color'][ $_color_key ] )) ? empty($this->element['color'][ $_color_key ])?"transparent":$this->element['color'][ $_color_key ] : "",
			), $this );
		}

		return FALSE;
	}

	public function add_cart_item_data_subscription_fees_single() {
		if ( !$this->setup ) {
			return FALSE;
		}
		if ( isset( $this->key ) && $this->key != '' ) {
			$_price = TM_EPO()->calculate_price( $this->post_data, $this->element, $this->key, $this->attribute, $this->per_product_pricing, $this->cpf_product_price, $this->variation_id );
			TM_EPO()->tmfee = TM_EPO()->tmfee + (float) $_price;

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
				'value'                          => esc_html( $this->key ),
				'price'                          => 0,
				'section'                        => esc_html( $this->element['uniqid'] ),
				'section_label'                  => esc_html( $this->element['label'] ),
				'percentcurrenttotal'            => 0,
				'currencies'                     => isset( $this->element['currencies'] ) ? $this->element['currencies'] : array(),
				'price_per_currency'             => $this->fill_currencies(),
				'quantity'                       => 1,

				'subscription_fees' => 'single',
			);

		}

		return FALSE;
	}

	public function add_cart_item_data_subscription_fees_multiple() {
		if ( !$this->setup ) {
			return FALSE;
		}
		/* select placeholder check */
		if ( isset( $this->element['options'][ esc_attr( $this->key ) ] ) ) {
			$_price = TM_EPO()->calculate_price( $this->post_data, $this->element, $this->key, $this->attribute, $this->per_product_pricing, $this->cpf_product_price, $this->variation_id );
			$use_images = !empty( $this->element['use_images'] ) ? $this->element['use_images'] : "";
			if ( $use_images ) {
				$_image_key = array_search( $this->key, $this->element['option_values'] );
				if ( $_image_key === NULL || $_image_key === FALSE ) {
					$_image_key = FALSE;
				}
			} else {
				$_image_key = FALSE;
			}

			$use_colors = !empty( $this->element['use_colors'] ) ? $this->element['use_colors'] : "";
			if ( $use_colors ) {
				$_color_key = array_search( $this->key, $this->element['option_values'] );
				if ( $_color_key === NULL || $_color_key === FALSE ) {
					$_color_key = FALSE;
				}
			} else {
				$_color_key = FALSE;
			}

			TM_EPO()->tmfee = TM_EPO()->tmfee + (float) $_price;

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
				'value'                          => esc_html( $this->element['options'][ esc_attr( $this->key ) ] ),
				'price'                          => 0,
				'section'                        => esc_html( $this->element['uniqid'] ),
				'section_label'                  => esc_html( $this->element['label'] ),
				'percentcurrenttotal'            => 0,
				'currencies'                     => isset( $this->element['currencies'] ) ? $this->element['currencies'] : array(),
				'price_per_currency'             => $this->fill_currencies(),
				'quantity'                       => 1,

				'subscription_fees'     => 'multiple',
				'multiple'              => '1',
				'key'                   => esc_attr( $this->key ),
				'use_images'            => $use_images,
				'use_colors'            => $use_colors,
				'color'                 => ($_color_key !== FALSE && isset( $this->element['color'][ $_color_key ] )) ? empty($this->element['color'][ $_color_key ])?"transparent":$this->element['color'][ $_color_key ] : "",
				'changes_product_image' => !empty( $this->element['changes_product_image'] ) ? $this->element['changes_product_image'] : "",
				'images'                => ($_image_key !== FALSE && isset( $this->element['images'][ $_image_key ] )) ? $this->element['images'][ $_image_key ] : "",
				'imagesp'               => ($_image_key !== FALSE && isset( $this->element['imagesp'][ $_image_key ] )) ? $this->element['imagesp'][ $_image_key ] : "",
			);

		}

		return FALSE;
	}

	public function add_cart_item_data_cart_fees_single() {
		if ( !$this->setup ) {
			return FALSE;
		}
		if ( isset( $this->key ) && $this->key != '' ) {
			$_price = TM_EPO()->calculate_price( $this->post_data, $this->element, $this->key, $this->attribute, $this->per_product_pricing, $this->cpf_product_price, $this->variation_id );

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
				'value'                          => esc_html( $this->key ),
				'price'                          => TM_EPO()->cacl_fee_price( $_price, $this->product_id, $this->element, $this->attribute ),
				'section'                        => esc_html( $this->element['uniqid'] ),
				'section_label'                  => esc_html( $this->element['label'] ),
				'percentcurrenttotal'            => 0,
				'currencies'                     => isset( $this->element['currencies'] ) ? $this->element['currencies'] : array(),
				'price_per_currency'             => $this->fill_currencies(),
				'quantity'                       => isset( $this->post_data[ $this->attribute . '_quantity' ] ) ? $this->post_data[ $this->attribute . '_quantity' ] : 1,

				'cart_fees' => 'single',
			);
		}

		return FALSE;
	}

	public function add_cart_item_data_cart_fees_multiple() {
		if ( !$this->setup ) {
			return FALSE;
		}
		if ( empty( $this->key ) ) {
			return FALSE;
		}
		/* select placeholder check */
		if ( isset( $this->element['options'][ esc_attr( $this->key ) ] ) ) {
			$_price = TM_EPO()->calculate_price( $this->post_data, $this->element, $this->key, $this->attribute, $this->per_product_pricing, $this->cpf_product_price, $this->variation_id );

			$use_images = !empty( $this->element['use_images'] ) ? $this->element['use_images'] : "";
			if ( $use_images ) {
				$_image_key = array_search( $this->key, $this->element['option_values'] );
				if ( $_image_key === NULL || $_image_key === FALSE ) {
					$_image_key = FALSE;
				}
			} else {
				$_image_key = FALSE;
			}

			$use_colors = !empty( $this->element['use_colors'] ) ? $this->element['use_colors'] : "";
			if ( $use_colors ) {
				$_color_key = array_search( $this->key, $this->element['option_values'] );
				if ( $_color_key === NULL || $_color_key === FALSE ) {
					$_color_key = FALSE;
				}
			} else {
				$_color_key = FALSE;
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
				'value'                          => esc_html( $this->element['options'][ esc_attr( $this->key ) ] ),
				'price'                          => TM_EPO()->cacl_fee_price( $_price, $this->product_id, $this->element, $this->attribute ),
				'section'                        => esc_html( $this->element['uniqid'] ),
				'section_label'                  => esc_html( $this->element['label'] ),
				'percentcurrenttotal'            => 0,
				'currencies'                     => isset( $this->element['currencies'] ) ? $this->element['currencies'] : array(),
				'price_per_currency'             => $this->fill_currencies(),
				'quantity'                       => isset( $this->post_data[ $this->attribute . '_quantity' ] ) ? $this->post_data[ $this->attribute . '_quantity' ] : 1,

				'cart_fees'             => 'multiple',
				'key'                   => esc_attr( $this->key ),
				'use_images'            => $use_images,
				'use_colors'            => $use_colors,
				'color'                 => ($_color_key !== FALSE && isset( $this->element['color'][ $_color_key ] )) ? empty($this->element['color'][ $_color_key ])?"transparent":$this->element['color'][ $_color_key ] : "",
				'changes_product_image' => !empty( $this->element['changes_product_image'] ) ? $this->element['changes_product_image'] : "",
				'images'                => ($_image_key !== FALSE && isset( $this->element['images'][ $_image_key ] )) ? $this->element['images'][ $_image_key ] : "",
				'imagesp'               => ($_image_key !== FALSE && isset( $this->element['imagesp'][ $_image_key ] )) ? $this->element['imagesp'][ $_image_key ] : "",
			);
		}

		return FALSE;
	}

}

