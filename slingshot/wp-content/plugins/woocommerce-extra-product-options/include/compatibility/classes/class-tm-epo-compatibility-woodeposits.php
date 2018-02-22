<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_woodeposits {

	protected static $_instance = NULL;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		add_action( 'wc_epo_add_compatibility', array( $this, 'add_compatibility' ) );

	}

	public function init() {

	}

	public function add_compatibility() {
		/** WooDeposits support **/
		add_action( 'wpp_add_product_to_cart_holder', array( $this, 'tm_wpp_add_product_to_cart_holder' ), 10, 2 );
	}

	/** WooDeposits support **/
	public function tm_wpp_add_product_to_cart_holder( $additional_data, $product ) {

		$epo_data = array(
			"tmhasepo",
			"tmcartepo",
			"tmsubscriptionfee",
			"tmcartfee",
			"tm_epo_product_original_price",
			"tm_epo_options_prices",
			"tm_epo_product_price_with_options" );

		foreach ( $epo_data as $key => $value ) {
			if ( isset( $product[ $value ] ) ) {
				$additional_data[ $value ] = $product[ $value ];
			}
		}

		return $additional_data;
	}
}


