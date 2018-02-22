<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_woocommerce_germanized_pro {

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

		add_action( 'woocommerce_gzdp_invoice_item_meta_start', array( $this, 'woocommerce_gzdp_invoice_item_meta_start' ), 10, 3 );

	}

	public function woocommerce_gzdp_invoice_item_meta_start($item_id, $item, $order){

		$items = $order->get_items();

		if ( function_exists( 'wc_display_item_meta' ) && isset ($items[$item_id]) ){
			
			$item = $items[$item_id];
			echo '<br />';
			wc_display_item_meta( $item, array(
				'before'    => '',
				'after'		=> '',
				'separator'	=> '',
				'echo'		=> true,
				'autop'		=> false,
			) );

		}
	}

}


