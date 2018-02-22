<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_WPML {

	protected static $_instance = NULL;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		add_action( 'wc_epo_add_compatibility', array( $this, 'add_compatibility' ) );
		add_action( 'init', array( $this, 'tm_remove_wcml' ), 3 );

	}

	public function init() {

	}

	public function add_compatibility() {
		/** WPML support **/
		if ( TM_EPO_WPML()->is_active() ) {
			add_filter( 'tm_cart_contents', array( $this, 'tm_cart_contents' ), 10, 2 );
			add_filter( 'wcml_exception_duplicate_products_in_cart', array( $this, 'tm_wcml_exception_duplicate_products_in_cart' ), 99999, 2 );
			add_filter( 'wcml_filter_cart_item_data', array( $this, 'wcml_filter_cart_item_data' ), 10, 1 );
		}
	}

	/** Remove conflictiong filters used by WooCommerce Multilingual**/
	public final function tm_remove_wcml() {
		global $woocommerce_wpml;
		if ( TM_EPO_WPML()->is_active() && $woocommerce_wpml && property_exists( $woocommerce_wpml, 'compatibility' ) && $woocommerce_wpml->compatibility && $woocommerce_wpml->compatibility->extra_product_options ) {
			remove_filter( 'get_tm_product_terms', array( $woocommerce_wpml->compatibility->extra_product_options, 'filter_product_terms' ) );
			remove_filter( 'get_post_metadata', array( $woocommerce_wpml->compatibility->extra_product_options, 'product_options_filter' ), 100, 4 );
			remove_action( 'updated_post_meta', array( $woocommerce_wpml->compatibility->extra_product_options, 'register_options_strings' ), 10, 4 );
			unset( $woocommerce_wpml->compatibility->extra_product_options );
		}
	}

	/** WPML support **/
	public function tm_wcml_exception_duplicate_products_in_cart( $flag, $cart_item ) {
		if ( isset( $cart_item['tmcartepo'] ) ) {
			return TRUE;
		}

		return $flag;
	}

	/** WooCommerce Multilingual **/
	public function wcml_filter_cart_item_data( $cart_contents = array() ) {
		unset( $cart_contents[ TM_EPO()->cart_edit_key_var ] );

		return $cart_contents;
	}

	/** WPML support **/
	public function tm_cart_contents( $cart = array(), $values = "" ) {
		if ( !TM_EPO_WPML()->is_active() ) {
			return $cart;
		}

		if ( isset( $cart['tmcartepo'] ) && is_array( $cart['tmcartepo'] ) ) {
			$current_product_id = $cart["product_id"];
			$wpml_translation_by_id = TM_EPO_WPML()->get_wpml_translation_by_id( $current_product_id );

			foreach ( $cart['tmcartepo'] as $k => $epo ) {
				if ( isset( $epo['mode'] ) && $epo['mode'] == 'local' ) {
					if ( isset( $epo['is_taxonomy'] ) ) {
						if ( $epo['is_taxonomy'] == "1" ) {
							$term = get_term_by( "slug", $epo["key"], $epo['section'] );
							$value_label = "";
							if ( $term ) {
								$wpml_term_id = icl_object_id( $term->term_id, $epo['section'], FALSE );
								if ( $wpml_term_id ) {
									$wpml_term = get_term( $wpml_term_id, $epo['section'] );
								} else {
									$wpml_term = $term;
								}
								$value_label = $wpml_term->name;
							}
							$cart['tmcartepo'][ $k ]['section_label'] = esc_html( urldecode( wc_attribute_label( $epo['section'] ) ) );
							$cart['tmcartepo'][ $k ]['value'] = esc_html( wc_attribute_label( $value_label ) );
						} elseif ( $epo['is_taxonomy'] == "0" ) {
							$attributes = tc_get_attributes( floatval( TM_EPO_WPML()->get_original_id( $cart['product_id'] ) ) );
							$$wpml_attributes = tc_get_attributes( floatval( TM_EPO_WPML()->get_current_id( $cart['product_id'] ) ) );

							$options = array_map( 'trim', explode( WC_DELIMITER, $attributes[ $epo['section'] ]['value'] ) );
							$options = array_map( 'sanitize_title', explode( WC_DELIMITER, $attributes[ $epo['section'] ]['value'] ) );
							$wpml_options = array_map( 'trim', explode( WC_DELIMITER, $wpml_attributes[ $epo['section'] ]['value'] ) );

							$cart['tmcartepo'][ $k ]['section_label'] = esc_html( urldecode( wc_attribute_label( $epo['section'] ) ) );
							$cart['tmcartepo'][ $k ]['value'] =
								esc_html(
									wc_attribute_label(
										array_search( $epo['key'], $options ) !== FALSE &&
										isset(
											$wpml_options[ array_search( $epo['key'], $options ) ]
										)
											? $wpml_options[ array_search( $epo['key'], $options ) ]
											: $epo['value']
									)
								);
						}
					}
				} elseif ( isset( $epo['mode'] ) && $epo['mode'] == 'builder' ) {
					
					if ( isset( $wpml_translation_by_id[ $epo['section'] ] ) ) {
						$cart['tmcartepo'][ $k ]['section_label'] = $wpml_translation_by_id[ $epo['section'] ];
						if ( !empty( $epo['multiple'] ) && !empty( $epo['key'] ) ) {
							$pos = strrpos( $epo['key'], '_' );
							if ( $pos !== FALSE && isset( $wpml_translation_by_id[ "options_" . $epo['section'] ] ) && is_array( $wpml_translation_by_id[ "options_" . $epo['section'] ] ) ) {
								$av = array_values( $wpml_translation_by_id[ "options_" . $epo['section'] ] );
								if ( isset( $av[ substr( $epo['key'], $pos + 1 ) ] ) ) {
									$cart['tmcartepo'][ $k ]['value'] = $av[ substr( $epo['key'], $pos + 1 ) ];
								}
							}
						}
					}
				}
			}
		}

		return $cart;
	}

}


