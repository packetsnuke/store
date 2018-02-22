<?php
/**
 * WooCommerce Measurement Price Calculator
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Measurement Price Calculator to newer
 * versions in the future. If you wish to customize WooCommerce Measurement Price Calculator for your
 * needs please refer to http://docs.woocommerce.com/document/measurement-price-calculator/ for more information.
 *
 * @package   WC-Measurement-Price-Calculator/Admin
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * WooCommerce Measurement Price Calculator Admin
 *
 * Main admin file which loads all Measurement Price Calculator product data
 * panels and modifications for WooCommerce general settings.
 */

add_action( 'admin_init', 'wc_measurement_price_calculator_admin_init' );

/**
 * Initialize the admin, adding actions to properly display and handle
 * the measurement price calculator custom tabs and panels
 */
function wc_measurement_price_calculator_admin_init() {
	global $pagenow;

	// on the product new/edit page
	if ( 'post-new.php' === $pagenow || 'post.php' === $pagenow || is_ajax() ) {

		include_once( wc_measurement_price_calculator()->get_plugin_path() . '/admin/post-types/writepanels/writepanels-init.php' );
	}
}


add_action( 'admin_enqueue_scripts', 'wc_measurement_price_calculator_admin_enqueue_scripts', 15 );

/**
 * Enqueue the price calculator admin scripts
 */
function wc_measurement_price_calculator_admin_enqueue_scripts() {
	global $taxnow, $post;

	// Get admin screen id
	$screen = get_current_screen();

	// on the admin product page
	if ( 'product' === $screen->id ) {

		wp_enqueue_script( 'wc-price-calculator-admin', wc_measurement_price_calculator()->get_plugin_url() . '/assets/js/admin/wc-measurement-price-calculator.min.js' );

		// Variables for JS scripts
		$wc_price_calculator_admin_params = array(
			'woocommerce_currency_symbol' => get_woocommerce_currency_symbol(),
			'woocommerce_weight_unit'     => 'no' !== get_option( 'woocommerce_enable_weight', true ) ? get_option( 'woocommerce_weight_unit' ) : '',
		);

		wp_localize_script( 'wc-price-calculator-admin', 'wc_price_calculator_admin_params', $wc_price_calculator_admin_params );
	}
}


// add additional physical property units/measurements
add_filter( 'woocommerce_products_general_settings', 'wc_measurement_price_calculator_woocommerce_catalog_settings' );


/**
 * Modify the WooCommerce > Settings > Catalog page to add additional
 * units of measurement, and physical properties to the config
 *
 * TODO: Perhaps the additional weight/dimension units should be added to the core, unless there was some reason they weren't there to begin with.  Then there's the core woocommerce_get_dimension() and woocommerce_get_dimension() functions to consider
 *
 * @param array $settings
 * @return array new settings
 */
function wc_measurement_price_calculator_woocommerce_catalog_settings( $settings ) {
	$new_settings = array();
	foreach ( $settings as &$setting ) {

		// safely add metric ton and english ton units to the weight units, in the correct order
		if ( 'woocommerce_weight_unit' === $setting['id'] ) {
			$options = array();
			if ( ! isset( $setting['options']['t'] ) ) $options['t'] = _x( 't', 'metric ton', 'ultimatewoo-pro' );  // metric ton
			foreach ( $setting['options'] as $key => $value ) {
				if ( 'lbs' === $key ) {
					if ( ! isset( $setting['options']['tn'] ) ) $options['tn'] = _x( 'tn', 'english ton', 'ultimatewoo-pro' );  // english ton
					$options[ $key ] = $value;
				} else {
					if ( ! isset( $options[ $key ] ) ) $options[ $key ] = $value;
				}
			}
			$setting['options'] = $options;
		}

		// safely add kilometer, foot, mile to the dimensions units, in the correct order
		if ( 'woocommerce_dimension_unit' === $setting['id'] ) {
			$options = array();
			if ( ! isset( $setting['options']['km'] ) ) $options['km'] = _x( 'km', 'kilometer', 'ultimatewoo-pro' );  // kilometer
			foreach ( $setting['options'] as $key => $value ) {
				if ( 'in' === $key ) {
					$options[ $key ] = $value;
					if ( ! isset( $setting['options']['ft'] ) ) $options['ft'] = _x( 'ft', 'foot', 'ultimatewoo-pro' );  // foot
					if ( ! isset( $options['yd'] ) ) $options['yd'] = _x( 'yd', 'yard', 'ultimatewoo-pro' );  // yard (correct order)
					if ( ! isset( $setting['options']['mi'] ) ) $options['mi'] = _x( 'mi', 'mile', 'ultimatewoo-pro' );  // mile
				} else {
					if ( ! isset( $options[ $key ] ) ) $options[ $key ] = $value;
				}
			}
			$setting['options'] = $options;
		}

		// add the setting into our new set of settings
		$new_settings[] = $setting;

		// add our area and volume units
		if ( 'woocommerce_dimension_unit' === $setting['id'] ) {

			$new_settings[] = array(
				'name'    => __( 'Area Unit', 'ultimatewoo-pro' ),
				'desc'    => __( 'This controls what unit you can define areas in for the Measurements Price Calculator.', 'ultimatewoo-pro' ),
				'id'      => 'woocommerce_area_unit',
				'css'     => 'min-width:300px;',
				'std'     => 'sq cm',
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'options' => array(
					'ha'      => _x( 'ha',      'hectare',           'woocommerce-measurement-price-calculator' ),
					'sq km'   => _x( 'sq km',   'square kilometer',  'woocommerce-measurement-price-calculator' ),
					'sq m'    => _x( 'sq m',    'square meter',      'woocommerce-measurement-price-calculator' ),
					'sq cm'   => _x( 'sq cm',   'square centimeter', 'ultimatewoo-pro' ),
					'sq mm'   => _x( 'sq mm',   'square millimeter', 'ultimatewoo-pro' ),
					'acs'     => _x( 'acs',     'acre',              'woocommerce-measurement-price-calculator' ),
					'sq. mi.' => _x( 'sq. mi.', 'square mile',       'woocommerce-measurement-price-calculator' ),
					'sq. yd.' => _x( 'sq. yd.', 'square yard',       'woocommerce-measurement-price-calculator' ),
					'sq. ft.' => _x( 'sq. ft.', 'square foot',       'woocommerce-measurement-price-calculator' ),
					'sq. in.' => _x( 'sq. in.', 'square inch',       'woocommerce-measurement-price-calculator' ),
				),
				'desc_tip'	=>  true,
			);

			// Note: 'cu mm' and 'cu km' are left out because they aren't really all that useful
			$new_settings[] = array(
				'name'    => __( 'Volume Unit', 'ultimatewoo-pro' ),
				'desc'    => __( 'This controls what unit you can define volumes in for the Measurements Price Calculator.', 'ultimatewoo-pro' ),
				'id'      => 'woocommerce_volume_unit',
				'css'     => 'min-width:300px;',
				'std'     => 'ml',
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'options' => array(
					'cu m'    => _x( 'cu m',    'cubic meter', 'ultimatewoo-pro' ),
					'l'       => _x( 'l',       'liter',       'woocommerce-measurement-price-calculator' ),
					'ml'      => _x( 'ml',      'milliliter',  'woocommerce-measurement-price-calculator' ),  // aka 'cu cm'
					'gal'     => _x( 'gal',     'gallon',      'woocommerce-measurement-price-calculator' ),
					'qt'      => _x( 'qt',      'quart',       'woocommerce-measurement-price-calculator' ),
					'pt'      => _x( 'pt',      'pint',        'woocommerce-measurement-price-calculator' ),
					'cup'     => __( 'cup',     'woocommerce-measurement-price-calculator' ),
					'fl. oz.' => _x( 'fl. oz.', 'fluid ounce', 'ultimatewoo-pro' ),
					'cu. yd.' => _x( 'cu. yd.', 'cubic yard',  'woocommerce-measurement-price-calculator' ),
					'cu. ft.' => _x( 'cu. ft.', 'cubic foot',  'woocommerce-measurement-price-calculator' ),
					'cu. in.' => _x( 'cu. in.', 'cubic inch',  'woocommerce-measurement-price-calculator' ),
				),
				'desc_tip' => true,
			);
		}
	}

	return $new_settings;
}
