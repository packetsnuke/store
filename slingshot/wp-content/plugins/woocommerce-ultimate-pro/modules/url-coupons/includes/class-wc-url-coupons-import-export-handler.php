<?php
/**
 * WooCommerce URL Coupons
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce URL Coupons to newer
 * versions in the future. If you wish to customize WooCommerce URL Coupons for your
 * needs please refer to http://docs.woocommerce.com/document/url-coupons/ for more information.
 *
 * @package     WC-URL-Coupons-Import-Export-Handler
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * URL Coupons Import/Export Handler
 *
 * Adds support for:
 *
 * + Customer/Order/Coupon CSV Import Suite
 * + Smart Coupons
 *
 * @since 2.4.0
 */
class WC_URL_Coupons_Import_Export_Handler {


	/**
	 * Handle compatibility for coupon import/export plugins.
	 *
	 * @since 2.4.0
	 */
	public function __construct() {

		// Customer/Order/Coupon CSV Import Suite compatibility.
		add_filter( 'wc_csv_import_suite_column_mapping_options', array( $this, 'csv_import_suite_url_coupons_column_mapping_options' ), 10, 2 );
		add_filter( 'wc_csv_import_suite_parsed_coupon_data',     array( $this, 'csv_import_suite_url_coupons_parsed_coupon_data' ), 10, 2 );
		add_action( 'wc_csv_import_suite_save_coupon_data',       array( $this, 'csv_import_suite_url_coupons_update_coupon_data' ), 10, 2 );

		// Smart Coupons.
		add_filter( 'wc_smart_coupons_export_headers', array( $this, 'smart_coupons_set_export_headers' ) );
	}


	/** Customer/Order/Coupon CSV Import Suite compatibility ******************/


	/**
	 * Add URL Coupon data to the CSV Import Suite columns mapping options.
	 *
	 * @since 2.4.0
	 * @param array $mapping_options Associative array of column mapping options.
	 * @param string $importer The importer id.
	 * @return array The column mapping options array.
	 */
	public function csv_import_suite_url_coupons_column_mapping_options( $mapping_options, $importer ) {

		if ( 'woocommerce_coupon_csv' === $importer ) {

			$url_coupons_mapping_options = array(

				'URL Coupons' => array(
					'url_coupons_unique_url'         => __( 'Unique URL', 'ultimatewoo-pro' ),
					'url_coupons_product_ids'        => __( 'Products to Add to Cart', 'ultimatewoo-pro' ),
					'url_coupons_redirect_page'      => __( 'Page Redirect', 'ultimatewoo-pro' ),
					'url_coupons_redirect_page_type' => __( 'Page Redirect Type', 'ultimatewoo-pro' ),
					'url_coupons_defer_apply'        => __( 'Defer Apply', 'ultimatewoo-pro' ),
				)
			);

			$mapping_options = array_merge( $mapping_options, $url_coupons_mapping_options );
		}

		return $mapping_options;
	}


	/**
	 * Add URL Coupon data to the parsed raw coupon data in CSV Import Suite.
	 *
	 * @since 2.4.0
	 * @param array $coupon_data Parsed coupon data.
	 * @param array $item The coupon item.
	 * @return array
	 */
	public function csv_import_suite_url_coupons_parsed_coupon_data( $coupon_data, $item ) {

		$coupon_data['url_coupons'] = array(
			'_wc_url_coupons_unique_url'         => ! empty( $item['url_coupons_unique_url'] )         ? $item['url_coupons_unique_url']         : '',
			'_wc_url_coupons_product_ids'        => ! empty( $item['url_coupons_product_ids'] )        ? $item['url_coupons_product_ids']        : '',
			'_wc_url_coupons_redirect_page'      => ! empty( $item['url_coupons_redirect_page'] )      ? $item['url_coupons_redirect_page']      : '',
			'_wc_url_coupons_redirect_page_type' => ! empty( $item['url_coupons_redirect_page_type'] ) ? $item['url_coupons_redirect_page_type'] : 'page',
			'_wc_url_coupons_defer_apply'        => ! empty( $item['url_coupons_defer_apply'] )        ? $item['url_coupons_defer_apply']        : '',
		);

		return $coupon_data;
	}


	/**
	 * Update URL Coupon data when coupons are imported by CSV Import Suite.
	 *
	 * @since 2.4.0
	 * @param int $coupon_id Coupon ID.
	 * @param array $coupon_data Coupon data.
	 */
	public function csv_import_suite_url_coupons_update_coupon_data( $coupon_id, $coupon_data ) {

		$coupon      = SV_WC_Coupon_Compatibility::get_coupon( $coupon_id );
		$url_coupons = isset( $coupon_data['url_coupons'] ) ? $coupon_data['url_coupons'] : null;

		if ( $coupon && is_array( $url_coupons ) ) {

			$coupon_options = array(
				'coupon_id' => $coupon_id,
			);

			foreach ( $url_coupons as $meta_key => $value ) {

				// Explode product IDs to an array.
				if ( '_wc_url_coupons_product_ids' === $meta_key ) {
					$value = array_filter( array_map( 'trim', explode( ',', $value ) ) );
				}

				// Update Coupon meta.
				SV_WC_Coupon_Compatibility::update_meta_data( $coupon, $meta_key, $value );

				// Add to coupon options.
				$coupon_options[ str_replace( '_wc_url_coupons_', '', $meta_key ) ] = $value;
			}

			// Update active coupon array options.
			wc_url_coupons()->get_admin_instance()->update_coupons( $coupon_options );
		}
	}


	/** Smart Coupons compatibility *******************************************/


	/**
	 * Rename headers in Smart Coupons export.
	 *
	 * @since 2.4.0
	 * @param array $coupon_postmeta_headers Associative-array of meta keys and their associated titles to be included as column headers in the Smart Coupons export file.
	 * @return array Filtered associative array of meta keys and their associated titles to be included as column headers.
	 */
	public function smart_coupons_set_export_headers( $coupon_postmeta_headers ) {

		$wc_url_coupons_headers = array(
			'_wc_url_coupons_unique_url'    => __( 'Unique URL', 'ultimatewoo-pro' ),
			'_wc_url_coupons_product_ids'   => __( 'Products to Add to Cart', 'ultimatewoo-pro' ),
			'_wc_url_coupons_redirect_page' => __( 'Page Redirect', 'ultimatewoo-pro' ),
			'_wc_url_coupons_defer_apply'   => __( 'Defer Apply', 'ultimatewoo-pro' )
		);

		return array_merge( $coupon_postmeta_headers, $wc_url_coupons_headers );
	}


}
