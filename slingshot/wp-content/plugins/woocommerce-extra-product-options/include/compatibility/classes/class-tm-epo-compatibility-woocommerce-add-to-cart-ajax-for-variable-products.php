<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_woocommerce_add_to_cart_ajax_for_variable_products {

	protected static $_instance = NULL;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		add_action( 'init', array( $this, 'add_compatibility' ) );
	}

	public function init() {

	}

	public function add_compatibility() {
		/** Woocommerce Add to cart Ajax for variable products fix **/
		if ( TM_EPO()->cart_edit_key && function_exists( 'woocommerce_add_to_cart_variable_rc_callback' ) ) {
			remove_action( 'wp_enqueue_scripts', 'ajax_add_to_cart_script', 99 );
		}
	}

}


