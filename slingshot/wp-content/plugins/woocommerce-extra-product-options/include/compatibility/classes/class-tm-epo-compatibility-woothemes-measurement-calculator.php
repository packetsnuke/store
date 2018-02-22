<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_woothemes_measurement_calculator {

	protected static $_instance = NULL;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'add_compatibility' ) );
		add_action( 'init', array( $this, 'template_redirect' ), 11 );
		add_action( 'template_redirect', array( $this, 'template_redirect' ), 11 );
	}

	public function init() {

	}

	public function add_compatibility() {
		if ( !class_exists( 'WC_Measurement_Price_Calculator' ) ) {
			return;
		}

		add_filter( 'wc_epo_add_cart_item_original_price', array( $this, 'wc_epo_add_cart_item_original_price' ), 10, 2 );
		add_filter( 'wc_epo_add_cart_item_calculated_price1', array( $this, 'wc_epo_add_cart_item_calculated_price' ), 10, 2 );
		add_filter( 'wc_epo_add_cart_item_calculated_price2', array( $this, 'wc_epo_add_cart_item_calculated_price' ), 10, 2 );
		
	}

	public function template_redirect(){
		// Disable EPO price filters
		remove_filter( 'woocommerce_get_price_html', array( TM_EPO(), 'get_price_html' ), 10, 2 );
		remove_filter( 'woocommerce_product_get_price', array( $this, 'tm_woocommerce_get_price' ), 1, 2 );
	}

	public function wc_epo_add_cart_item_calculated_price( $price = "", $cart_item = "" ) {

		if ( class_exists('WC_Price_Calculator_Settings') && class_exists('WC_Price_Calculator_Product') && class_exists('SV_WC_Product_Compatibility') ){
		
			$product  = isset( $cart_item['variation_id'] ) && $cart_item['variation_id'] ? wc_get_product( $cart_item['product_id'] ) : $cart_item['data'];
			$settings = new WC_Price_Calculator_Settings( $product );

			if ( isset( $cart_item['pricing_item_meta_data']['_price'] ) && ! WC_Price_Calculator_Product::pricing_calculator_inventory_enabled( $product ) ) {

				// pricing inventory management *not* enabled so the item price = item unit price (ie 1 item 10 ft long at $1/foot, the price is $10)
				$cart_item['data']->set_price( (float) $cart_item['pricing_item_meta_data']['_price'] );

			} elseif ( WC_Price_Calculator_Product::pricing_calculator_inventory_enabled( $product ) ) {

				if ( $settings->pricing_rules_enabled() ) {
					// a calculated inventory product with pricing rules enabled will have no configured price, so set it based on the measurement
					$measurement = new WC_Price_Calculator_Measurement( $cart_item['pricing_item_meta_data']['_measurement_needed_unit'], $cart_item['pricing_item_meta_data']['_measurement_needed'] );
					$cart_item['data']->set_price( $settings->get_pricing_rules_price( $measurement ) );
				}

				// is there a minimum price to use?
				$min_price = SV_WC_Product_Compatibility::get_meta( $product, '_wc_measurement_price_calculator_min_price', true );

				if ( is_numeric( $min_price ) && $min_price > $cart_item['data']->get_price() * ( $cart_item['quantity'] / $cart_item['pricing_item_meta_data']['_quantity'] ) ) {

					$cart_item['data']->set_price( $min_price / ( $cart_item['quantity'] / $cart_item['pricing_item_meta_data']['_quantity'] ) );
				}
			}

			 

		}

		return $price;

	}

	public function wc_epo_add_cart_item_original_price( $price = "", $cart_item = "" ) {
		if ( isset( $cart_item['pricing_item_meta_data'] ) && isset( $cart_item['pricing_item_meta_data']['_price'] ) ) {
			$price = $cart_item['pricing_item_meta_data']['_price'];
		}

		return $price;
	}

	public function cart_item_price( $item_price = "", $cart_item = "", $cart_item_key = "" ) {

		if (
			!empty( $cart_item['tmcartepo'] ) &&
			isset( $cart_item['tm_epo_product_price_with_options'] ) &&
			isset( $cart_item['pricing_item_meta_data'] ) &&
			!empty( $cart_item['pricing_item_meta_data']['_quantity'] ) &&
			!empty( $cart_item['quantity'] )
		) {
			$item_price = wc_price( (float) $cart_item['data']->get_price() * floatval( $cart_item['quantity'] ) );
		}

		return $item_price;
	}

}


