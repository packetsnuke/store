<?php
/**
 * WC_CP_Admin class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    2.2.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup admin hooks.
 *
 * @class    WC_CP_Admin
 * @version  3.9.0
 */
class WC_CP_Admin {

	/**
	 * Setup admin hooks.
	 */
	public static function init() {

		add_action( 'init', array( __CLASS__, 'admin_init' ) );

		// Admin jQuery.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'composite_admin_scripts' ) );

		// Template override scan path.
		add_filter( 'woocommerce_template_overrides_scan_paths', array( __CLASS__, 'composite_template_scan_path' ) );
	}

	/**
	 * Admin init.
	 */
	public static function admin_init() {
		self::includes();
	}

	/**
	 * Include classes.
	 */
	public static function includes() {

		// Product Import/Export.
		if ( WC_CP_Core_Compatibility::is_wc_version_gte_3_1() ) {
			require_once( 'export/class-wc-cp-product-export.php' );
			require_once( 'import/class-wc-cp-product-import.php' );
		}

		// Metaboxes.
		if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ) {
			require_once( 'meta-boxes/class-wc-cp-meta-box-product-data.php' );
		} else {
			require_once( 'meta-boxes/legacy/class-wc-cp-meta-box-product-data.php' );
		}

		// Admin AJAX.
		require_once( 'class-wc-cp-admin-ajax.php' );
	}

	/**
	 * Include scripts.
	 */
	public static function composite_admin_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'wc-composite-writepanel', WC_CP()->plugin_url() . '/assets/js/wc-composite-write-panels' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'wc-admin-meta-boxes' ), WC_CP()->version );

		wp_register_style( 'wc-composite-admin-css', WC_CP()->plugin_url() . '/assets/css/wc-composite-admin.css', array(), WC_CP()->version );
		wp_style_add_data( 'wc-composite-admin-css', 'rtl', 'replace' );

		wp_register_style( 'wc-composite-writepanel-css', WC_CP()->plugin_url() . '/assets/css/wc-composite-write-panels.css', array( 'woocommerce_admin_styles' ), WC_CP()->version );
		wp_style_add_data( 'wc-composite-writepanel-css', 'rtl', 'replace' );

		wp_register_style( 'wc-composite-edit-order-css', WC_CP()->plugin_url() . '/assets/css/wc-composite-edit-order.css', array( 'woocommerce_admin_styles' ), WC_CP()->version );
		wp_style_add_data( 'wc-composite-edit-order-css', 'rtl', 'replace' );

		wp_enqueue_style( 'wc-composite-admin-css' );

		// Get admin screen id.
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// WooCommerce admin pages.
		if ( in_array( $screen_id, array( 'product' ) ) ) {

			wp_enqueue_script( 'wc-composite-writepanel' );

			$params = array(
				'save_composite_nonce'        => wp_create_nonce( 'wc_bto_save_composite' ),
				'add_component_nonce'         => wp_create_nonce( 'wc_bto_add_component' ),
				'add_scenario_nonce'          => wp_create_nonce( 'wc_bto_add_scenario' ),
				'i18n_no_default'             => __( 'No default option&hellip;', 'ultimatewoo-pro' ),
				'i18n_all'                    => __( 'Any Product or Variation', 'ultimatewoo-pro' ),
				'i18n_none'                   => _x( 'No selection', 'optional component property controlled in scenarios', 'ultimatewoo-pro' ),
				'i18n_matches_1'              => _x( 'One result is available, press enter to select it.', 'enhanced select', 'woocommerce' ),
				'i18n_matches_n'              => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'woocommerce' ),
				'i18n_no_matches'             => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
				'i18n_ajax_error'             => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_1'      => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_short_n'      => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_1'       => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
				'i18n_input_too_long_n'       => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_1'   => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
				'i18n_selection_too_long_n'   => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
				'i18n_load_more'              => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
				'i18n_searching'              => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
				'i18n_choose_component_image' => __( 'Choose a Component Image', 'ultimatewoo-pro' ),
				'i18n_set_component_image'    => __( 'Set Component Image', 'ultimatewoo-pro' ),
				'wc_placeholder_img_src'      => wc_placeholder_img_src(),
				'is_wc_version_gte_2_3'       => WC_CP_Core_Compatibility::is_wc_version_gte_2_3() ? 'yes' : 'no',
				'is_wc_version_gte_2_7'       => WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ? 'yes' : 'no'
			);

			wp_localize_script( 'wc-composite-writepanel', 'wc_composite_admin_params', $params );
		}

		if ( in_array( $screen_id, array( 'edit-product', 'product' ) ) ) {
			wp_enqueue_style( 'wc-composite-writepanel-css' );
		}

		if ( in_array( $screen_id, array( 'shop_order', 'edit-shop_order' ) ) ) {
			wp_enqueue_style( 'wc-composite-edit-order-css' );
		}
	}

	/**
	 * Support scanning for template overrides in extension.
	 *
	 * @param  array  $paths
	 * @return array
	 */
	public static function composite_template_scan_path( $paths ) {
		$paths[ 'WooCommerce Composite Products' ] = WC_CP()->plugin_path() . '/templates/';
		return $paths;
	}
}

WC_CP_Admin::init();
