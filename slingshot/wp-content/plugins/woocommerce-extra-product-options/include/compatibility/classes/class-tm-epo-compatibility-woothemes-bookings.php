<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_woothemes_bookings {

	protected static $_instance = NULL;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'add_compatibility' ), 2 );

	}

	public function init() {

	}

	public function wc_epo_get_settings( $settings = array() ) {
		if ( class_exists( 'WC_Bookings' ) ) {
			$settings["tm_epo_bookings_person"] = "yes";
			$settings["tm_epo_bookings_block"] = "yes";
		}

		return $settings;
	}

	public function add_compatibility() {
		/** WooCommerce Bookings  (woothemes) support **/
		if ( !class_exists( 'WC_Bookings' ) ) {
			return;
		}
		add_filter( 'wc_epo_get_settings', array( $this, 'wc_epo_get_settings' ), 10, 1 );
		add_filter( 'booking_form_calculated_booking_cost', array( $this, 'adjust_booking_cost' ), 10, 3 );
		add_filter( 'wc_epo_cart_options_prices', array( $this, 'wc_epo_cart_options_prices' ), 10, 2 );
		add_filter( 'wc_epo_adjust_price', array( $this, 'wc_epo_adjust_price' ), 10, 2 );

		add_action( 'wp_ajax_tc_epo_bookings_calculate_costs', array( $this, 'tc_epo_bookings_calculate_costs' ) );
		add_action( 'wp_ajax_nopriv_tc_epo_bookings_calculate_costs', array( $this, 'tc_epo_bookings_calculate_costs' ) );
		add_action( 'init', array( $this, 'tc_bookings_init' ), 10 );

		add_filter( 'wc_epo_adjust_cart_item', array( $this, 'wc_epo_adjust_cart_item' ), 10, 1 );

		add_filter( 'tm_epo_settings_headers', array( $this, 'tm_epo_settings_headers' ), 10, 1 );
		add_filter( 'tm_epo_settings_settings', array( $this, 'tm_epo_settings_settings' ), 10, 1 );

		add_filter( 'wcml_cart_contents_not_changed', array( $this, 'filter_bundled_product_in_cart_contents' ), 9999, 3 );
	}

	public function filter_bundled_product_in_cart_contents( $cart_item, $key, $current_language ){
		global $woocommerce_wpml;

		if ( defined('WCML_MULTI_CURRENCIES_INDEPENDENT') && $cart_item['data'] instanceof WC_Product_Booking && isset( $cart_item['booking'] ) ) {
			
			$current_id      = apply_filters( 'translate_object_id', $cart_item['product_id'], 'product', true, $current_language );
			$cart_product_id = $cart_item['product_id'];

			if ( $woocommerce_wpml->settings['enable_multi_currency'] == WCML_MULTI_CURRENCIES_INDEPENDENT || $current_id != $cart_product_id ) {

				$tm_epo_options_prices = floatval( $cart_item['tm_epo_options_prices'] );
				$current_cost = floatval( $cart_item['data']->get_price() );
				
				$cart_item['data']->set_price( $current_cost + $tm_epo_options_prices );

			}

		}

		return $cart_item;
	}

	public function tc_bookings_init() {
		global $woocommerce_wpml;
		if ( TM_EPO_WPML()->is_active() && $woocommerce_wpml && property_exists( $woocommerce_wpml, 'compatibility' ) && $woocommerce_wpml->compatibility && $woocommerce_wpml->compatibility->bookings ) {
			if ( !is_admin() || isset( $_POST['action'] ) && $_POST['action'] == 'tc_epo_bookings_calculate_costs' ) {
				add_filter( 'get_post_metadata', array( $woocommerce_wpml->compatibility->bookings, 'filter_wc_booking_cost' ), 10, 4 );
			}
		}
	}

	/** Admin settings **/
	public function tm_epo_settings_headers( $headers = array() ) {
		$headers["bookings"] = __( 'WooCommerce Bookings', 'woocommerce-tm-extra-product-options' );

		return $headers;
	}

	/** Admin settings **/
	public function tm_epo_settings_settings( $settings = array() ) {
		$label = __( 'WooCommerce Bookings', 'woocommerce-tm-extra-product-options' );
		$settings["bookings"] = array(
			array(
				'type'  => 'tm_title',
				'id'    => 'epo_page_options',
				'title' => $label,
			),
			array(
				'title'    => __( 'Multiply cost by person count', 'woocommerce-tm-extra-product-options' ),
				'desc'     => '<span>' . __( 'Enabling this will multiply the options price by the person count.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_bookings_person',
				'class'    => 'chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'yes',
				'type'     => 'select',
				'options'  => array(
					'no'  => __( 'Disable', 'woocommerce-tm-extra-product-options' ),
					'yes' => __( 'Enable', 'woocommerce-tm-extra-product-options' ),
				),
				'desc_tip' => FALSE,
			),
			array(
				'title'    => __( 'Multiply cost by block count', 'woocommerce-tm-extra-product-options' ),
				'desc'     => '<span>' . __( 'Enabling this will multiply the options price by the block count.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_bookings_block',
				'class'    => 'chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'yes',
				'type'     => 'select',
				'options'  => array(
					'no'  => __( 'Disable', 'woocommerce-tm-extra-product-options' ),
					'yes' => __( 'Enable', 'woocommerce-tm-extra-product-options' ),
				),
				'desc_tip' => FALSE,
			),
			array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),
		);

		return $settings;
	}

	public function wc_epo_adjust_cart_item( $cart_item ) {
		if (
			isset( $cart_item['data'] )
			&& is_object( $cart_item['data'] )
			&& property_exists( $cart_item['data'], "id" )
			&& tc_get_id( $cart_item['data'] )
		) {
			if ( $cart_item['data']->is_type( 'booking' ) ) {

				if ( !empty( $cart_item['tmcartepo'] ) ) {
					$cart_item['tm_epo_product_original_price'] = $cart_item['tm_epo_product_original_price'] - $cart_item['tm_epo_options_prices'];
				}

			}
		}

		return $cart_item;
	}

	public function wc_epo_adjust_price( $adjust, $cart_item ) {
		if (
			isset( $cart_item['data'] )
			&& is_object( $cart_item['data'] )
			&& property_exists( $cart_item['data'], "id" )
			&& tc_get_id( $cart_item['data'] )
		) {
			if ( $cart_item['data']->is_type( 'booking' ) ) {
				return FALSE;
			}
		}

		return $adjust;
	}

	public function tc_epo_bookings_calculate_costs() {
		$posted = array();
		remove_filter( 'booking_form_calculated_booking_cost', array( $this, 'adjust_booking_cost' ), 10, 3 );
		parse_str( $_POST['form'], $posted );

		$booking_id = $posted['add-to-cart'];
		$product = get_product( $booking_id );

		if ( !$product ) {
			die( json_encode( array(
				'result'        => 'ERROR',
				'html'          => '<span class="booking-error">' . __( 'This booking is unavailable.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'product_price' => 0,
			) ) );
		}

		$booking_form = new WC_Booking_Form( $product );
		$cost = $booking_form->calculate_booking_cost( $posted );

		if ( is_wp_error( $cost ) ) {
			die( json_encode( array(
				'result'        => 'ERROR',
				'html'          => '<span class="booking-error">' . $cost->get_error_message() . '</span>',
				'product_price' => 0,
			) ) );
		}

		$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
		$display_price = $tax_display_mode == 'incl' ? tc_get_price_including_tax( $product, array( 'qty' => 1, 'price' => $cost ) ) : tc_get_price_excluding_tax( $product, array( 'qty' => 1, 'price' => $cost ) );

		die( json_encode( array(
			'result'        => 'SUCCESS',
			'product_price' => $display_price,
		) ) );
	}

	/** Adjust options when adding to cart */
	public function wc_epo_cart_options_prices( $price, $cart_data ) {
		$wc_booking_person_qty_multiplier = (TM_EPO()->tm_epo_bookings_person == "yes") ? 1 : 0;
		$wc_booking_block_qty_multiplier = (TM_EPO()->tm_epo_bookings_block == "yes") ? 1 : 0;

		if (
			(!$wc_booking_person_qty_multiplier && !$wc_booking_block_qty_multiplier)
			|| !isset( $cart_data['booking'] )
			|| !isset( $cart_data['data'] )
			|| !is_object( $cart_data['data'] )
			|| !property_exists( $cart_data['data'], "id" )
			|| !tc_get_id( $cart_data['data'] )
		) {
			return $price;
		}

		$person = (!empty( $cart_data['booking']['_persons'] ) && array_sum( $cart_data['booking']['_persons'] )) ? array_sum( $cart_data['booking']['_persons'] ) : 0;
		$duration = !empty( $cart_data['booking']['_duration'] ) ? $cart_data['booking']['_duration'] : 0;

		$c = $person + $duration;

		$price = $c * $price;

		return $price;

	}

	/** Adjust the final booking cost */
	public function adjust_booking_cost( $booking_cost, $booking_form, $posted ) {
		$epos = TM_EPO()->tm_add_cart_item_data( array(), tc_get_id( $booking_form->product ), $posted, TRUE );
		$extra_price = 0;
		$booking_data = $booking_form->get_posted_data( $posted );

		$wc_booking_person_qty_multiplier = (TM_EPO()->tm_epo_bookings_person == "yes") ? 1 : 0;
		$wc_booking_block_qty_multiplier = (TM_EPO()->tm_epo_bookings_block == "yes") ? 1 : 0;
		if ( !empty( $epos ) && !empty( $epos['tmcartepo'] ) ) {
			foreach ( $epos['tmcartepo'] as $key => $value ) {
				if ( !empty( $value['price'] ) ) {

					$price = floatval( $value['price'] );
					$option_price = 0;

					if ( !empty( $wc_booking_person_qty_multiplier ) && !empty( $booking_data['_persons'] ) && array_sum( $booking_data['_persons'] ) ) {
						$option_price += $price * array_sum( $booking_data['_persons'] );
					}
					if ( !empty( $wc_booking_block_qty_multiplier ) && !empty( $booking_data['_duration'] ) ) {
						$option_price += $price * $booking_data['_duration'];
					}
					if ( !$option_price ) {
						$option_price += $price;
					}
					$extra_price += $option_price;
				}
			}

		}

		return $booking_cost + $extra_price;
	}
}


