<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_store_exporter {

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
		/** Store exporter deluxe support **/
		add_filter( 'woo_ce_order_item', array( $this, 'tm_woo_ce_extend_order_item' ), 9999, 2 );
	}

	/** Store exporter deluxe support **/
	public function tm_woo_ce_extend_order_item( $order_item = array(), $order_id = 0 ) {

		if ( function_exists( 'woo_ce_get_extra_product_option_fields' ) && $tm_fields = woo_ce_get_extra_product_option_fields() ) {

			foreach ( $tm_fields as $tm_field ) {
				$order_item->{sprintf( 'tm_%s', sanitize_key( $tm_field['name'] ) )} = $tm_field['value'];
			}

			unset( $tm_fields, $tm_field );
		}

		return $order_item;
	}

}


