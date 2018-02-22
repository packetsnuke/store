<?php
/**
 * Exit if accesses directly
 */
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'Pie_WCWL_Compatibility' ) ) {
	/**
	 * Adds compatibility functions for ensuring Waitlist will work with different versions of WooCommerce
	 */
	class Pie_WCWL_Compatibility {

		/**
		 * Retrieves product ID based on current WooCommerce version
		 *
		 * @param \WC_Product $product product object
		 *
		 * @since 1.5.0
		 *
		 * @return string|int product ID
		 */
		public static function get_product_id( WC_Product $product ) {
			if ( self::wc_is_at_least_2_5() ) {
				return $product->get_id();
			} else {
				return $product->is_type( 'variation' ) ? $product->variation_id : $product->id;
			}
		}

		/**
		 * Retrieves an array of parent product IDs based on current WooCommerce version
		 *
		 * @param \WC_Product $product product object
		 *
		 * @since 1.5.0
		 *
		 * @return array parent IDs
		 */
		public static function get_parent_id( WC_Product $product ) {
			if ( self::wc_is_at_least_3_0() ) {
				$parent_ids = array();
				if ( $parent_id = $product->get_parent_id() ) {
					$parent_ids[] = $parent_id;
				}
				$parent_ids = array_merge( $parent_ids, self::get_grouped_parent_id( $product ) );
				return $parent_ids;
			} else {
				if ( WooCommerce_Waitlist_Plugin::is_variation( $product ) ) {
					return array( $product->parent->id );
				} else {
					return array( $product->get_parent() );
				}
			}
		}

		/**
		 * Check all grouped products to see if they have this product as a child product
		 *
		 * @param WC_Product $product
		 *
		 * @return array
		 */
		public static function get_grouped_parent_id( WC_Product $product ) {
			$parent_products  = array();
			$args             = array(
				'type'  => 'grouped',
				'limit' => - 1,
			);
			$grouped_products = wc_get_products( $args );
			foreach ( $grouped_products as $grouped_product ) {
				foreach ( $grouped_product->get_children() as $child_id ) {
					if ( $child_id == $product->get_id() ) {
						$parent_products[] = $grouped_product->get_id();
					}
				}
			}
			return $parent_products;
		}

		/**
		 * Retrieves page ID based on current WooCommerce version
		 *
		 * @param $page
		 *
		 * @since 1.5.0
		 *
		 * @return int
		 */
		public static function get_page_id( $page ) {
			if ( self::wc_is_at_least_3_0() ) {
				return wc_get_page_id( $page );
			} else {
				return woocommerce_get_page_id( $page );
			}
		}

		/**
		 * Retrieves template file based on current WooCommerce version
		 *
		 * @param        $template_name
		 * @param array  $args
		 * @param string $template_path
		 * @param string $default_path
		 *
		 * @since 1.5.0
		 */
		public static function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
			if ( self::wc_is_at_least_2_1() ) {
				wc_get_template( $template_name, $args, $template_path, $default_path );
			} else {
				woocommerce_get_template( $template_name, $args, $template_path, $default_path );
			}
		}

		/**
		 * Adds a WooCommerce notice based on current WooCommerce version
		 *
		 * @param string $message
		 * @param string $type
		 *
		 * @since 1.5.0
		 */
		public static function add_notice( $message, $type = 'success' ) {
			if ( self::wc_is_at_least_2_1() ) {
				wc_add_notice( $message, $type );
			} else {
				global $woocommerce;
				switch ( $type ) {
					case 'error':
						$woocommerce->add_error( $message );
						break;
					default:
						$woocommerce->add_message( $message );
				}
			}
		}

		/**
		 * Return the current version of WooCommerce
		 *
		 * @since 1.5.0
		 *
		 * @return string woocommerce version number/null
		 */
		protected static function get_wc_version() {
			global $woocommerce;

			return isset( $woocommerce->version ) ? $woocommerce->version : null;
		}

		/**
		 * Returns true if the current version of WooCommerce is at least 2.1
		 *
		 * @since 1.5.0
		 *
		 * @return boolean
		 */
		public static function wc_is_at_least_2_1() {
			return self::get_wc_version() && version_compare( self::get_wc_version(), '2.1', '>=' );
		}

		/**
		 * Returns true if the current version of WooCommerce is at least 2.5
		 *
		 * @since 1.5.0
		 *
		 * @return boolean
		 */
		public static function wc_is_at_least_2_5() {
			return self::get_wc_version() && version_compare( self::get_wc_version(), '2.5', '>=' );
		}

		/**
		 * Returns true if the current version of WooCommerce is at least 3.0
		 *
		 * @since 1.5.0
		 *
		 * @return boolean
		 */
		public static function wc_is_at_least_3_0() {
			return self::get_wc_version() && version_compare( self::get_wc_version(), '3.0', '>=' );
		}
	}
}