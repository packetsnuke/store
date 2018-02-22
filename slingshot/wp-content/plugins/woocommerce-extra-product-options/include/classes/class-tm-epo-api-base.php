<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

/**
 * HTML creation class.
 */
final class TM_EPO_API_base {

	private $cpf = array();

	protected static $_instance = NULL;

	/**
	 * @return unique class instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * TM_EPO_API_base constructor.
	 * @param array $args
	 */
	function __construct( $args = array() ) {
	}

	/** Checks if the product with id=$product_id has options *
	 * @param int $product_id
	 * @return array|bool
	 */
	public function has_options( $product_id = 0 ) {

		$post_id = get_the_ID();

		if ( $product_id && $product_id !== $post_id ) {
			$post_id = $product_id;
		}
		if ( !empty( $this->cpf[ $post_id ] ) ) {
			return $this->cpf[ $post_id ];
		}

		$has_epo = FALSE;
		$cpf_price_array = TM_EPO()->get_product_tm_epos( $post_id );

		if ( $cpf_price_array ) {
			$global_price_array = $cpf_price_array['global'];
			$local_price_array = $cpf_price_array['local'];
			if ( empty( $global_price_array ) && empty( $local_price_array ) ) {
				return FALSE;
			}

			$has_epo = array();
			if ( !empty( $global_price_array ) ) {
				$has_epo['global'] = TRUE;
			}
			if ( !empty( $global_price_array ) ) {
				$has_epo['local'] = TRUE;
			}
			$found = FALSE;
			foreach ( $global_price_array as $priority => $priorities ) {
				foreach ( $priorities as $pid => $field ) {
					if ( isset( $field['sections'] ) && is_array( $field['sections'] ) ) {
						foreach ( $field['sections'] as $section_id => $section ) {
							if ( isset( $section['elements'] ) ) {
								foreach ( $section['elements'] as $elid => $el ) {
									if ( isset( $el['builder'] ) ) {
										if ( isset( $el['builder']['element_type'] ) ) {
											foreach ( $el['builder']['element_type'] as $bid => $b ) {
												if ( strtolower( $b ) == "variations" ) {
													$found = TRUE;
													break 5;
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
			if ( $found ) {
				$has_epo['variations'] = TRUE;
			}

		}
		$this->cpf[ $post_id ] = $has_epo;

		return $has_epo;

	}

	/** Checks if the array for has_options has options *
	 * @param bool $array
	 * @return bool
	 */
	public function is_valid_options( $array = FALSE ) {
		if ( $array !== FALSE && is_array( $array ) && (isset( $array_global ) || isset( $array['local'] )) ) {
			return TRUE;
		}

		return FALSE;
	}

	/** Returns all saved options (this must be used after the 'woocommerce_init' hook) *
	 * @param $order_id
	 * @return array|bool|mixed
	 */
	public function get_all_options( $order_id ) {

		return $this->get_option( $order_id, 'all' );
	}

	/** Returns a saved option (this must be used after the 'woocommerce_init' hook) *
	 * @param $order_id
	 * @param string $option_id
	 * @return array|bool|mixed
	 */
	public function get_option( $order_id, $option_id = '' ) {

		$order = wc_get_order( $order_id );
		if ( !$order ) {
			return FALSE;
		}
		$order_currency = is_callable( array( $order, 'get_currency' ) ) ? $order->get_currency() : $order->get_order_currency();
		$mt_prefix = $order_currency;

		$line_items = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );

		$all_epos = array();
		foreach ( $line_items as $item_id => $item ) {

			//$_product = $order->get_product_from_item( $item );
			$_product = tc_get_product_from_item( $item, $order );
			$item_meta = function_exists( 'wc_get_order_item_meta' ) ? wc_get_order_item_meta( $item_id, '', FALSE ) : $order->get_item_meta( $item_id );
			$order_taxes = $order->get_taxes();

			$check_box_html = (version_compare( WC()->version, '2.6', '>=' )) ? '' : '<td class="check-column">&nbsp;</td>';

			$has_epo = is_array( $item_meta )
				&& isset( $item_meta['_tmcartepo_data'] )
				&& isset( $item_meta['_tmcartepo_data'][0] )
				&& isset( $item_meta['_tm_epo'] );

			$has_fee = is_array( $item_meta )
				&& isset( $item_meta['_tmcartfee_data'] )
				&& isset( $item_meta['_tmcartfee_data'][0] );

			if ( $has_epo || $has_fee ) {
				$current_product_id = $item['product_id'];
				$original_product_id = floatval( TM_EPO_WPML()->get_original_id( $current_product_id, 'product' ) );
				if ( TM_EPO_WPML()->get_lang() == TM_EPO_WPML()->get_default_lang() && $original_product_id != $current_product_id ) {
					$current_product_id = $original_product_id;
				}
				$wpml_translation_by_id = TM_EPO_WPML()->get_wpml_translation_by_id( $current_product_id );
			}

			if ( $has_epo ) {
				$epos = maybe_unserialize( $item_meta['_tmcartepo_data'][0] );

				if ( $epos && is_array( $epos ) ) {

					foreach ( $epos as $key => $epo ) {
						if ( $epo && is_array( $epo ) ) {
							if ( $epo['section'] != $option_id && $option_id !== 'all' ) {
								continue;
							}

							$new_currency = FALSE;
							if ( isset( $epo['price_per_currency'] ) ) {
								$_current_currency_prices = $epo['price_per_currency'];
								if ( $mt_prefix !== ''
									&& $_current_currency_prices !== ''
									&& is_array( $_current_currency_prices )
									&& isset( $_current_currency_prices[ $mt_prefix ] )
									&& $_current_currency_prices[ $mt_prefix ] != ''
								) {

									$new_currency = TRUE;
									$epo['price'] = $_current_currency_prices[ $mt_prefix ];

								}
							}
							if ( !$new_currency ) {
								$type = "";
								if ( isset( $epo['element'] ) && isset( $epo['element']['_'] ) && isset( $epo['element']['_']['price_type'] ) ) {
									$type = $epo['element']["_"]['price_type'];
								}
								$epo['price'] = apply_filters( 'wc_epo_get_current_currency_price', $epo['price'], $type );
							}

							if ( !isset( $epo['quantity'] ) ) {
								$epo['quantity'] = 1;
							}
							if ( isset( $wpml_translation_by_id[ $epo['section'] ] ) ) {
								$epo['name'] = $wpml_translation_by_id[ $epo['section'] ];
							}
							// normal (local) mode
							if ( !isset( $epo['price_per_currency'] ) && taxonomy_exists( $epo['name'] ) ) {
								$epo['name'] = wc_attribute_label( $epo['name'] );
							}
							if ( isset( $wpml_translation_by_id[ "options_" . $epo['section'] ] )
								&& is_array( $wpml_translation_by_id[ "options_" . $epo['section'] ] )
								&& !empty( $epo['multiple'] )
								&& !empty( $epo['key'] )
							) {

								$pos = strrpos( $epo['key'], '_' );

								if ( $pos !== FALSE ) {

									$av = array_values( $wpml_translation_by_id[ "options_" . $epo['section'] ] );

									if ( isset( $av[ substr( $epo['key'], $pos + 1 ) ] ) ) {

										$epo['value'] = $av[ substr( $epo['key'], $pos + 1 ) ];

									}

								}

							}
							$display_value = $epo['value'];
							if ( is_array( $epo['value'] ) ) {
								$display_value = array_map( 'html_entity_decode', $display_value, version_compare( phpversion(), '5.4', '<' ) ? ENT_COMPAT : (ENT_COMPAT | ENT_HTML401), 'UTF-8' );
							} else {
								$display_value = html_entity_decode( $display_value, version_compare( phpversion(), '5.4', '<' ) ? ENT_COMPAT : (ENT_COMPAT | ENT_HTML401), 'UTF-8' );
							}

							if ( TM_EPO()->tm_epo_show_image_replacement == "yes" && !empty( $epo['use_images'] ) && !empty( $epo['images'] ) && $epo['use_images'] == "images" ) {
								$display_value = '<div class="cpf-img-on-cart"><img alt="" class="attachment-shop_thumbnail wp-post-image epo-option-image" src="' . apply_filters( "tm_image_url", $epo['images'] ) . '" /></div>' . esc_attr( $display_value );
							}

							$display_value = apply_filters( 'tm_translate', $display_value );

							if ( TM_EPO()->tm_epo_show_upload_image_replacement == "yes" && isset( $epo['element'] ) && isset( $epo['element']['type'] ) && $epo['element']['type'] == 'upload' ) {
								$check = wp_check_filetype( $epo['value'] );
								if ( !empty( $check['ext'] ) ) {
									$image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );
									if ( in_array( $check['ext'], $image_exts ) ) {
										$display_value = '<a target="_blank" href="' . $display_value . '"><span class="cpf-img-on-cart"><img alt="" class="attachment-shop_thumbnail wp-post-image epo-option-image epo-upload-image" src="' .
											apply_filters( "tm_image_url", $epo['value'] ) . '" /></span></a>';
									}
								}
							}

							if ( !empty( $epo['multiple_values'] ) ) {
								$display_value_array = explode( $epo['multiple_values'], $display_value );
								$display_value = "";
								foreach ( $display_value_array as $d => $dv ) {
									$display_value .= '<span class="cpf-data-on-cart">' . $dv . '</span>';
								}
							}

							$epo_name = apply_filters( 'tm_translate', $epo['name'] );
							$epo_value = make_clickable( $display_value );
							if ( $epo['element']['type']==='textarea' ){
								$epo_value = trim( $epo_value );

								$epo_value = str_replace( array( "\r\n", "\r" ), "\n", $epo_value );

								$epo_value = preg_replace( "/\n\n+/", "\n\n", $epo_value );

								$epo_value = array_map( 'wc_clean', explode( "\n", $epo_value ) );

								$epo_value = implode( "\n", $epo_value );

								$epo_value = wpautop( $epo_value );
							}
							$epo_quantity = ($epo['quantity'] * (float) $item_meta['_qty'][0]) . ' <small>(' . $epo['quantity'] . '&times;' . (float) $item_meta['_qty'][0] . ')</small>';
							$epo_edit_value = TRUE;
							$edit_buttons = TRUE;
							$epo_edit_cost = TRUE;
							$epo_edit_quantity = TRUE;
							$epo_is_fee = FALSE;
							$epo['price'] = floatval( $epo['price'] );

							$all_epos[$item_id][$key] = $epo;
						}
					}

				}
			}

			if ( $has_fee ) {
				$epos = maybe_unserialize( $item_meta['_tmcartfee_data'][0] );
				if ( isset( $epos[0] ) ) {
					$epos = $epos[0];
				} else {
					$epos = FALSE;
				}

				if ( $epos && is_array( $epos ) && !empty( $epos[0] ) ) {

					foreach ( $epos as $key => $epo ) {
						if ( $epo && is_array( $epo ) ) {
							if ( $epo['section'] != $option_id && $option_id !== 'all' ) {
								continue;
							}
							if ( !isset( $epo['quantity'] ) ) {
								$epo['quantity'] = 1;
							}
							if ( isset( $wpml_translation_by_id[ $epo['section'] ] ) ) {
								$epo['name'] = $wpml_translation_by_id[ $epo['section'] ];
							}
							if ( isset( $wpml_translation_by_id[ "options_" . $epo['section'] ] ) && is_array( $wpml_translation_by_id[ "options_" . $epo['section'] ] ) && !empty( $epo['multiple'] ) && !empty( $epo['key'] ) ) {
								$pos = strrpos( $epo['key'], '_' );
								if ( $pos !== FALSE ) {
									$av = array_values( $wpml_translation_by_id[ "options_" . $epo['section'] ] );
									if ( isset( $av[ substr( $epo['key'], $pos + 1 ) ] ) ) {
										$epo['value'] = $av[ substr( $epo['key'], $pos + 1 ) ];
										if ( !empty( $epo['use_images'] ) && !empty( $epo['images'] ) && $epo['use_images'] == "images" ) {
											$epo['value'] = '<div class="cpf-img-on-cart"><img alt="" class="attachment-shop_thumbnail wp-post-image epo-option-image" src="' . apply_filters( "tm_image_url", $epo['images'] ) . '" /></div>' . $epo['value'];
										}
									}
								}
							}

							$epo_name = apply_filters( 'tm_translate', $epo['name'] );
							$epo_value = apply_filters( 'tm_translate', $epo['value'] );
							$epo_quantity = ($epo['quantity'] * (float) $item_meta['_qty'][0]) . ' <small>(' . $epo['quantity'] . '&times;' . (float) $item_meta['_qty'][0] . ')</small>';
							$epo_edit_value = FALSE;
							$edit_buttons = FALSE;
							$epo_edit_cost = FALSE;
							$epo_edit_quantity = FALSE;
							$epo_is_fee = TRUE;
							$epo['price'] = floatval( $epo['price'] );

							$all_epos[$item_id][$key] = $epo;
						}
					}
				}
			}

		}

		return $all_epos;

	}

}
