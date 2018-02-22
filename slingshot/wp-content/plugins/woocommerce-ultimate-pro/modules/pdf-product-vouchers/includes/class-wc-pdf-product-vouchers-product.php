<?php
/**
 * WooCommerce PDF Product Vouchers
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce PDF Product Vouchers to newer
 * versions in the future. If you wish to customize WooCommerce PDF Product Vouchers for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-pdf-product-vouchers/ for more information.
 *
 * @package   WC-PDF-Product-Vouchers/Classes
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;


/**
 * PDF Product Vouchers Product helper class. Provides product utility methods
 * and handles aspectes of the plugin related to products.
 *
 * @since 1.2.0
 */
class WC_PDF_Product_Vouchers_Product {


	/**
	 * PDF Product Vouchers Product constructor
	 *
	 * @since 1.2.0
	 */
	public function __construct() {

		// add product page voucher options
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'render_product_voucher_options' ) );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'loop_add_to_cart_link' ), 10, 2 );
	}


	/**
	 * Renders any user-input voucher fields, and any voucher layout image options,
	 * if this is a product with an attached voucher
	 *
	 * @since 1.2.0
	 */
	public function render_product_voucher_options() {

		global $product;

		if ( self::has_voucher_template( $product ) ) {
			wc_pdf_product_vouchers_render_product_voucher_fields( $product );
		}
	}


	/**
	 * Modifies the loop 'add to cart' button class for simple voucher products
	 * with required input fields to link directly to the product page like a
	 * variable product.
	 *
	 * @since 1.2.0
	 * @param string $tag the 'add to cart' button tag html
	 * @param \WC_Product $product the product
	 * @return string the add to cart tag
	 */
	public function loop_add_to_cart_link( $tag, $product ) {

		if ( $product && $product->is_type( 'simple' ) && self::has_voucher_template( $product ) ) {

			$voucher_template = self::get_voucher_template( $product );

			if ( $voucher_template->has_required_input_fields() ) {

				// otherwise, for simple type products, the page javascript would take over and
				// try to do an ajax add-to-cart, when really we need the customer to visit the
				// product page to supply whatever input fields they require
				$tag = sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button add_to_cart_button product_type_%s">%s</a>',
					get_permalink( $product->get_id() ),
					esc_attr( $product->get_id() ),
					esc_attr( $product->get_sku() ),
					'variable',
					__( 'Select options', 'ultimatewoo-pro' )
				);
			}
		}

		return $tag;
	}


	/** Utility/Helper methods ******************************************************/


	/**
	 * Returns true if the given product has an attached voucher template
	 *
	 * @deprecated since 3.0.0
	 *
	 * @since 1.2.0
	 * @param \WC_Product $product the product to check for a voucher
	 * @return boolean true if $product has a voucher, false otherwise
	 */
	public static function has_voucher( $product ) {

		/* @deprecated since 3.0.0 */
		_deprecated_function( 'WC_PDF_Product_Vouchers_Product::has_voucher()', '3.0.0', 'WC_PDF_Product_Vouchers_Product::has_voucher_template()' );

		return self::has_voucher_template( $product );
	}


	/**
	 * Returns true if the given product has an attached voucher template
	 *
	 * @since 3.0.0
	 * @param \WC_Product $product the product to check for a voucher
	 * @return boolean true if $product has a voucher, false otherwise
	 */
	public static function has_voucher_template( WC_Product $product ) {

		if ( ! $product->exists() ) {
			return false;
		}

		return null !== self::get_voucher_template( $product );
	}


	/**
	 * Returns the voucher template attached to $product
	 *
	 * @deprecated since 3.0.0
	 *
	 * @since 1.2.0
	 * @param \WC_Product $product the voucher product
	 * @return \WC_Voucher_Template the voucher template attached to $product
	 */
	public static function get_voucher( WC_Product $product ) {

		/* @deprecated since 3.0.0 */
		_deprecated_function( 'WC_PDF_Product_Vouchers_Product::get_voucher()', '3.0.0', 'WC_PDF_Product_Vouchers_Product::get_voucher_template()' );

		return self::get_voucher_template( $product );
	}


	/**
	 * Returns the voucher template attached to $product
	 *
	 * @since 3.0.0
	 * @param \WC_Product $product the voucher product
	 * @return \WC_Voucher_Template the voucher template attached to $product
	 */
	public static function get_voucher_template( WC_Product $product ) {

		if ( $product->is_type( 'variable' ) ) {

			foreach ( $product->get_children() as $variation_product_id ) {

				$variation_product = wc_get_product( $variation_product_id );
				$has_voucher       = 'yes' === SV_WC_Product_Compatibility::get_meta( $variation_product, '_has_voucher' );

				if ( $has_voucher && ( $template_id = SV_WC_Product_Compatibility::get_meta( $variation_product, '_voucher_template_id' ) ) ) {

					// Note: this assumes that there is only one voucher attached to any variations for a product,
					// which probably isn't a great assumption, but simplifies the frontend for now
					return wc_pdf_product_vouchers_get_voucher_template( $template_id );
				}
			}
		} elseif ( 'yes' === SV_WC_Product_Compatibility::get_meta( $product, '_has_voucher' ) && SV_WC_Product_Compatibility::get_meta( $product, '_voucher_template_id' ) ) {
			// simple product or product variation
			return wc_pdf_product_vouchers_get_voucher_template( SV_WC_Product_Compatibility::get_meta( $product, '_voucher_template_id' ) );
		}

		// aw, no voucher
		return null;
	}


	/**
	 * Returns the voucher id of the voucher template attached to $product, if any
	 *
	 * @deprecated since 3.0.0
	 *
	 * @since 1.2.0
	 * @param \WC_Product $product the product
	 * @return int voucher id of attached voucher, if any
	 */
	public static function get_voucher_id( WC_Product $product ) {

		/* @deprecated since 3.0.0 */
		_deprecated_function( 'WC_PDF_Product_Vouchers_Product::get_voucher_id()', '3.0.0', 'WC_PDF_Product_Vouchers_Product::get_voucher_template_id()' );

		return self::get_voucher_template_id( $product );
	}


	/**
	 * Returns the voucher id of the voucher template attached to $product, if any
	 *
	 * @since 3.0.0
	 * @param \WC_Product $product the product
	 * @return int voucher id of attached voucher, if any
	 */
	public static function get_voucher_template_id( WC_Product $product ) {
		return SV_WC_Product_Compatibility::get_meta( $product, 'voucher_template_id', true, 'view' );
	}

}
