<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_woothemes_subscriptions {

	protected static $_instance = NULL;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'add_compatibility' ) );

	}

	public function init() {

	}

	public function add_compatibility() {
		if ( !class_exists( 'WC_Subscriptions' ) ) {
			return;
		}
		/** WooCommerce Subscriptions (woothemes) support **/
		add_filter( 'woocommerce_subscriptions_product_sign_up_fee', array( $this, 'tm_subscriptions_product_sign_up_fee' ), 10, 2 );
		add_filter( 'woocommerce_subscriptions_renewal_order_items', array( $this, 'tm_woocommerce_subscriptions_renewal_order_items' ), 10, 5 );
		add_filter( 'wcs_renewal_order_items', array( $this, 'tm_woocommerce_subscriptions_renewal_order_items' ), 10, 1 );
	}

	/** WooCommerce Subscriptions (woothemes) support **/
	public function tm_woocommerce_subscriptions_renewal_order_items( $order_items ) {
		if ( !defined( 'TM_IS_SUBSCRIPTIONS_RENEWAL' ) ) {
			define( 'TM_IS_SUBSCRIPTIONS_RENEWAL', 1 );
		}

		return $order_items;
	}

	/** WooCommerce Subscriptions (woothemes) support - Calculates the extra Subscription sign up fee **/
	public function tm_subscriptions_product_sign_up_fee( $subscription_sign_up_fee = "", $product = "" ) {
		$options_fee = 0;
		
		if ( !is_product() && WC()->cart ) {
			$cart_contents = WC()->cart->get_cart();
			foreach ( $cart_contents as $cart_key => $cart_item ) {
				foreach ( $cart_item as $key => $data ) {
					if ( $key == "tmsubscriptionfee" ) {
						$options_fee = $data;
					}
				}
			}
			$subscription_sign_up_fee += $options_fee;
		}

		return $subscription_sign_up_fee;
	}

}


