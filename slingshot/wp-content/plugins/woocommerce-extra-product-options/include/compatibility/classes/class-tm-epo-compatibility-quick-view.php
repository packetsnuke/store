<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_quick_view {

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

		add_filter( 'woocommerce_tm_quick_view', array( $this, 'woocommerce_tm_quick_view' ), 10, 3 );
	
	}

	public function woocommerce_tm_quick_view( $qv ) {

		$woothemes_quick_view = (isset( $_GET['wc-api'] ) && $_GET['wc-api'] == 'WC_Quick_View');
		$theme_flatsome_quick_view = (isset( $_POST['action'] ) && ($_POST['action'] == 'jck_quickview' || $_POST['action'] == 'ux_quickview' || $_POST['action'] == 'flatsome_quickview'));
		$theme_kleo_quick_view = (isset( $_POST['action'] ) && ($_POST['action'] == 'woo_quickview'));
		$yith_quick_view = ( (isset( $_POST['action'] ) && ($_POST['action'] == 'yith_load_product_quick_view')) || (isset( $_GET['action'] ) && ($_GET['action'] == 'yith_load_product_quick_view')) );
		$venedor_quick_view = (isset( $_GET['action'] ) && ($_GET['action'] == 'venedor_product_quickview'));
		$rubbez_quick_view = (isset( $_POST['action'] ) && ($_POST['action'] == 'product_quickview'));
		$jckqv_quick_view = (isset( $_POST['action'] ) && ($_POST['action'] == 'jckqv'));//http://codecanyon.net/item/woocommerce-quickview/4378284
		$themify_quick_view = (isset( $_GET['ajax'] ) && $_GET['ajax'] == 'true');
		$porto_quick_view = (isset( $_GET['action'] ) && ($_GET['action'] == 'porto_product_quickview'));
		$woocommerce_product_layouts = (isset( $_POST['action'] ) && ($_POST['action'] == 'dhvc_woo_product_quickview'));//http://codecanyon.net/item/woocommerce-products-layouts/7384574?
		$nm_getproduct = (isset( $_POST['action'] ) && ($_POST['action'] == 'nm_getproduct'));//Woo Product Quick View http://codecanyon.net/item/woocommerce-product-quick-view/11293528?
		$lightboxpro = (isset( $_POST['action'] ) && ($_POST['action'] == 'wpb_wl_quickview'));//WooCommerce LightBox PRO http://wpbean.com/
		if ( $woothemes_quick_view
			|| $theme_flatsome_quick_view
			|| $theme_kleo_quick_view
			|| $yith_quick_view
			|| $venedor_quick_view
			|| $rubbez_quick_view
			|| $jckqv_quick_view
			|| $themify_quick_view
			|| $porto_quick_view
			|| $woocommerce_product_layouts
			|| $nm_getproduct
			|| $lightboxpro
		) {
			$qv = TRUE;
		}

		return apply_filters( 'wc_epo_is_quickview', $qv );
	}


}


