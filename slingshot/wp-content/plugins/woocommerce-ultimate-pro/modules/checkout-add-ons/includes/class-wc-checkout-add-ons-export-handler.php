<?php
/**
 * WooCommerce Checkout Add-Ons
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Checkout Add-Ons to newer
 * versions in the future. If you wish to customize WooCommerce Checkout Add-Ons for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-checkout-add-ons/ for more information.
 *
 * @package     WC-Checkout-Add-Ons/Classes
 * @author      SkyVerge
 * @copyright   Copyright (c) 2014-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Checkout Add-Ons Import/Export Handler
 *
 * Adds support for:
 *
 * + Customer / Order CSV Export
 *
 * @since 1.1.0
 */
class WC_Checkout_Add_Ons_Export_Handler {


	/**
	 * Setup class
	 *
	 * @since 1.1.0
	 */
	public function __construct() {

		// Customer / Order CSV Export column headers/data
		add_filter( 'wc_customer_order_csv_export_order_headers', array( $this, 'add_checkout_add_ons_to_csv_export_column_headers' ) );
		add_filter( 'wc_customer_order_csv_export_order_row',     array( $this, 'add_checkout_add_ons_to_csv_export_column_data' ), 10, 3 );

		// Customer / Order XML Export fee items
		add_filter( 'wc_customer_order_xml_export_suite_order_data', array( $this, 'add_checkout_add_ons_to_xml_export_data' ), 10, 2 );
	}


	/**
	 * Adds support for Customer/Order CSV Export by adding a
	 * column header for each checkout add-on
	 *
	 * @since 1.1.0
	 * @param array $headers existing array of header key/names for the CSV export
	 * @return array
	 */
	public function add_checkout_add_ons_to_csv_export_column_headers( $headers ) {

		foreach ( wc_checkout_add_ons()->get_add_ons() as $add_on ) {
			$headers[ 'checkout_add_on_' . $add_on->id ]       = 'checkout_add_on:' . str_replace( '-', '_', sanitize_title( $add_on->name ) ) . '_' . $add_on->id;
			$headers[ 'checkout_add_on_total_' . $add_on->id ] = 'checkout_add_on_total:' . str_replace( '-', '_', sanitize_title( $add_on->name ) ) . '_' . $add_on->id;
		}

		return $headers;
	}


	/**
	 * Adds support for Customer/Order CSV Export by adding data for each
	 * checkout add-on column header
	 *
	 * @since 1.1.0
	 * @param array $order_data generated order data matching the column keys in the header
	 * @param WC_Order $order order being exported
	 * @param \WC_Customer_Order_CSV_Export_Generator $csv_generator instance
	 * @return array
	 */
	public function add_checkout_add_ons_to_csv_export_column_data( $order_data, $order, $csv_generator ) {

		$order_add_ons  = wc_checkout_add_ons()->get_order_add_ons( SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );
		$new_order_data = $add_on_data = array();

		foreach ( wc_checkout_add_ons()->get_add_ons() as $add_on ) {

			$value = '';
			$total = '';

			if ( isset( $order_add_ons[ $add_on->id ] ) ) {

				switch ( $add_on->type ) {

					case 'file':
						$value = wp_get_attachment_url( $order_add_ons[ $add_on->id ]['value'] );
					break;

					case 'checkbox':
						$value = '1' === $order_add_ons[ $add_on->id ]['value'] ? 'yes' : 'no';
					break;

					default:
						$value = $add_on->normalize_value( $order_add_ons[ $add_on->id ]['normalized_value'], true );
				}

				$total = wc_format_decimal( $order_add_ons[ $add_on->id ]['total'], 2 );
			}

			$add_on_data[ 'checkout_add_on_' . $add_on->id ]       = wp_strip_all_tags( $value );
			$add_on_data[ 'checkout_add_on_total_' . $add_on->id ] = $total;
		}

		// determine if the selected format is "one row per item"
		if ( version_compare( wc_customer_order_csv_export()->get_version(), '4.0.0', '<' ) ) {

			$one_row_per_item = ( 'default_one_row_per_item' === $csv_generator->order_format || 'legacy_one_row_per_item' === $csv_generator->order_format );

		// v4.0.0 - 4.0.2
		} elseif ( ! isset( $csv_generator->format_definition ) ) {

			// get the CSV Export format definition
			$format_definition = wc_customer_order_csv_export()->get_formats_instance()->get_format( $csv_generator->export_type, $csv_generator->export_format );

			$one_row_per_item = isset( $format_definition['row_type'] ) && 'item' === $format_definition['row_type'];

		// v4.0.3+
		} else {

			$one_row_per_item = 'item' === $csv_generator->format_definition['row_type'];
		}

		if ( $one_row_per_item ) {

			foreach ( $order_data as $data ) {
				$new_order_data[] = array_merge( (array) $data, $add_on_data );
			}

		} else {

			$new_order_data = array_merge( $order_data, $add_on_data );
		}

		return $new_order_data;
	}


	/**
	 * Adds support for Customer / Order XML Export by adding a dedicated <CheckoutAddOns> tag
	 *
	 * @since 1.9.0
	 * @param array $order_data order data for the XML output
	 * @param \WC_Order $order order object
	 * @return array updated order data
	 */
	public function add_checkout_add_ons_to_xml_export_data( $order_data, $order ) {

		$order_data['CheckoutAddOns'] = $this->get_checkout_add_ons_formatted( $order );

		return $order_data;
	}


	/**
	 * Creates array of checkout add-ons in format required for xml_to_array()
	 *
	 * @since 1.9.0
	 * @param \WC_Order $order order object
	 * @return array|null add-ons in array format required by array_to_xml() or null if no add-ons
	 */
	protected function get_checkout_add_ons_formatted( $order ) {

		$add_ons       = array();
		$order_add_ons = wc_checkout_add_ons()->get_order_add_ons( SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );

		foreach( wc_checkout_add_ons()->get_add_ons() as $id => $add_on ) {

			$add_on_data = array();

			if ( isset( $order_add_ons[ $add_on->id ] ) ) {

				switch( $add_on->type ) {

					case 'file':
						$add_on_value = wp_get_attachment_url( $order_add_ons[ $add_on->id ]['value'] );
					break;

					case 'checkbox':
						$add_on_value = '1' === $order_add_ons[ $add_on->id ]['value'] ? 'yes' : 'no';
					break;

					default:
						$add_on_value = is_array( $order_add_ons[ $add_on->id ]['normalized_value'] ) ? implode( ', ', $order_add_ons[ $add_on->id ]['normalized_value'] ) : $order_add_ons[ $add_on->id ]['normalized_value'];
				}

				$add_on_data['ID']    = $id;
				$add_on_data['Name']  = $order_add_ons[ $add_on->id ]['name'];
				$add_on_data['Value'] = $add_on_value;
				$add_on_data['Cost']  = wc_format_decimal( $order_add_ons[ $add_on->id ]['total'], 2 );
			}

			/**
			 * Filters the individual add-ons array format
			 *
			 * @since 1.9.0
			 * @param array $add_on_data the add-on data for the array_to_xml() format
			 * @param \WC_Order $order
			 * @param array $add_on the raw add-on data for the order
			 */
			$add_ons['CheckoutAddOn'][] = apply_filters( 'wc_checkout_add_ons_xml_add_on_data', $add_on_data, $order, $add_on );
		}

		return ! empty( $add_ons ) ? $add_ons : null;
	}


}
