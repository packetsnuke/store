<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_easy_bookings {

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
		if ( class_exists( 'Easy_booking' ) ) {
			$settings["tm_epo_easy_bookings_block"] = "yes";
		}

		return $settings;
	}

	public function add_compatibility() {
		/** WooCommerce Bookings  (woothemes) support **/
		if ( !class_exists( 'Easy_booking' ) ) {
			return;
		}
		add_filter( 'wc_epo_get_settings', array( $this, 'wc_epo_get_settings' ), 10, 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 4 );
		add_filter( 'wc_epo_cart_options_prices', array( $this, 'wc_epo_cart_options_prices' ), 10, 2 );
		add_filter( 'easy_booking_set_booking_price', array( $this, 'easy_booking_set_booking_price' ), 10, 2 );
		add_filter( 'wc_epo_adjust_cart_item', array( $this, 'wc_epo_adjust_cart_item' ), 10, 1 );
		add_filter( 'tm_epo_settings_headers', array( $this, 'tm_epo_settings_headers' ), 10, 1 );
		add_filter( 'tm_epo_settings_settings', array( $this, 'tm_epo_settings_settings' ), 10, 1 );

		add_filter( 'easy_booking_get_new_item_price', array( $this, 'easy_booking_get_new_item_price' ), 10, 4 );
		add_filter( 'easy_booking_fragments', array( $this, 'easy_booking_fragments' ), 10, 1 );

		// >=2.09
		add_filter( 'easy_booking_booking_price_details', array( $this, 'easy_booking_booking_price_details' ), 10, 3 );

	}

	public function easy_booking_fragments( $fragments ) {
		$epo_price = floatval( TM_EPO()->easy_bookings_epo_price );
		$booking_price = $fragments['booking_price'] + $epo_price;
		$fragments['booking_price'] = $booking_price;
		$fragments['epo_price'] = $epo_price;
		$fragments['epo_duration'] = TM_EPO()->easy_bookings_duration;
		$fragments['epo_base_price'] = $booking_price;
		if ( TM_EPO()->tm_epo_final_total_box == 'disable' ) {
			$fragments['epo_base_price'] = $booking_price - $epo_price;
		}

		return $fragments;
	}

	public function easy_booking_booking_price_details( $details, $product, $booking_data ) {
		$extra_price = 0;
		if ( TM_EPO()->tm_epo_final_total_box == 'disable' ) {
			$posted = array();
			parse_str( $_POST['epo_data'], $posted );
			$epos = TM_EPO()->tm_add_cart_item_data( array(), tc_get_id( $_product ), $posted, TRUE );

			$wc_booking_block_qty_multiplier = (TM_EPO()->tm_epo_easy_bookings_block == "yes") ? 1 : 0;
			if ( !empty( $epos ) && !empty( $epos['tmcartepo'] ) ) {
				foreach ( $epos['tmcartepo'] as $key => $value ) {
					if ( !empty( $value['price'] ) ) {

						$price = floatval( $value['price'] );
						$option_price = 0;

						if ( !empty( $wc_booking_block_qty_multiplier ) && !empty( $duration ) ) {
							$option_price += $price * $duration;
						}
						if ( !$option_price ) {
							$option_price += $price;
						}
						$extra_price += $option_price;
					}
				}

			}
		}

		TM_EPO()->easy_bookings_duration = $booking_data['duration'];
		TM_EPO()->easy_bookings_epo_price = $extra_price;

		return $details;
	}

	public function easy_booking_get_new_item_price( $booking_price, $product, $_product, $duration ) {
		$extra_price = 0;
		if ( TM_EPO()->tm_epo_final_total_box == 'disable' ) {
			$posted = array();
			parse_str( $_POST['epo_data'], $posted );
			$epos = TM_EPO()->tm_add_cart_item_data( array(), tc_get_id( $_product ), $posted, TRUE );

			$wc_booking_block_qty_multiplier = (TM_EPO()->tm_epo_easy_bookings_block == "yes") ? 1 : 0;
			if ( !empty( $epos ) && !empty( $epos['tmcartepo'] ) ) {
				foreach ( $epos['tmcartepo'] as $key => $value ) {
					if ( !empty( $value['price'] ) ) {

						$price = floatval( $value['price'] );
						$option_price = 0;

						if ( !empty( $wc_booking_block_qty_multiplier ) && !empty( $duration ) ) {
							$option_price += $price * $duration;
						}
						if ( !$option_price ) {
							$option_price += $price;
						}
						$extra_price += $option_price;
					}
				}

			}
		}

		TM_EPO()->easy_bookings_duration = $duration;
		TM_EPO()->easy_bookings_epo_price = $extra_price;

		return $booking_price;
	}

	public function wp_enqueue_scripts() {
		wp_enqueue_script( 'tc-comp-easy-bookings', TM_EPO_PLUGIN_URL . '/include/compatibility/assets/js/easy-bookings.js', array( 'jquery' ), TM_EPO()->version, TRUE );
		$args = array(
			'wc_booking_block_qty_multiplier' => isset( TM_EPO()->tm_epo_easy_bookings_block ) && (TM_EPO()->tm_epo_easy_bookings_block == "yes") ? 1 : 0,
		);
		wp_localize_script( 'tc-comp-easy-bookings', 'tm_epo_easy_bookings', $args );
	}

	/** Admin settings **/
	public function tm_epo_settings_headers( $headers = array() ) {
		$headers["easybookings"] = __( 'WooCommerce Easy Bookings', 'woocommerce-tm-extra-product-options' );

		return $headers;
	}

	/** Admin settings **/
	public function tm_epo_settings_settings( $settings = array() ) {
		$label = __( 'WooCommerce Easy Bookings', 'woocommerce-tm-extra-product-options' );
		$settings["easybookings"] = array(
			array(
				'type'  => 'tm_title',
				'id'    => 'epo_page_options',
				'title' => $label,
			),
			array(
				'title'    => __( 'Multiply cost by block count', 'woocommerce-tm-extra-product-options' ),
				'desc'     => '<span>' . __( 'Enabling this will multiply the options price by the block count.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_easy_bookings_block',
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

	/** Sets custom price to the cart item **/
	public function easy_booking_set_booking_price( $booking_price, $cart_item ) {
		if ( !empty( $cart_item['tmcartepo'] ) && isset( $cart_item['tm_epo_options_prices'] ) ) {
			$booking_price = $booking_price + $cart_item['tm_epo_options_prices'];
		}

		return $booking_price;
	}

	public function wc_epo_adjust_cart_item( $cart_item ) {
		if (
			isset( $cart_item['data'] )
			&& is_object( $cart_item['data'] )
			&& property_exists( $cart_item['data'], "id" )
			&& tc_get_id( $cart_item['data'] )
		) {
			if ( isset( $cart_data['_booking_price'] ) && isset( $cart_data['_booking_duration'] ) ) {

				if ( !empty( $cart_item['tmcartepo'] ) ) {
					$cart_item['tm_epo_product_original_price'] = $cart_item['tm_epo_product_original_price'] - $cart_item['tm_epo_options_prices'];
				}

			}
		}

		return $cart_item;
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
		$wc_booking_block_qty_multiplier = (TM_EPO()->tm_epo_easy_bookings_block == "yes") ? 1 : 0;

		if (
			!$wc_booking_block_qty_multiplier
			|| !(isset( $cart_data['_booking_price'] ) && isset( $cart_data['_booking_duration'] ))
			|| !isset( $cart_data['data'] )
			|| !is_object( $cart_data['data'] )
			|| !property_exists( $cart_data['data'], "id" )
			|| !tc_get_id( $cart_data['data'] )
		) {
			return $price;
		}

		$duration = !empty( $cart_data['_booking_duration'] ) ? $cart_data['_booking_duration'] : 0;

		$c = $duration;

		$price = $c * $price;

		return $price;

	}

}


