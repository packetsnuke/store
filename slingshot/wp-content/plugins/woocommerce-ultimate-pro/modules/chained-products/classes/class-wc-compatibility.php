<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'Chained_Products_WC_Compatibility' ) ) {

	/**
	 * WooCommerce Compatibility Class for Chained Products
	 * 
	 */
	class Chained_Products_WC_Compatibility {
		
		/**
		 * Is WooCommerce 2.5 
		 * 
		 * @return boolean
		 */
        public static function is_wc_gte_25() {
			return self::is_wc_greater_than( '2.4.13' );
		} 

		/**
		 * Is WooCommerce 2.6 
		 * 
		 * @return boolean
		 */
        public static function is_wc_gte_26() {
			return self::is_wc_greater_than( '2.5.5' );
		}

		/**
		 * Is WooCommerce 3.0 
		 * 
		 * @return boolean
		 */
        public static function is_wc_gte_30() {
			return self::is_wc_greater_than( '2.6.14' );
		}

		
		/**
		 * WooCommerce Current WooCommerce Version
		 * 
		 * @return string woocommerce version
		 */
		public static function get_wc_version() {
			if (defined('WC_VERSION') && WC_VERSION)
				return WC_VERSION;
			if (defined('WOOCOMMERCE_VERSION') && WOOCOMMERCE_VERSION)
				return WOOCOMMERCE_VERSION;
			return null;
		}

		/**
		 * Compare passed version with woocommerce current version
		 * 
		 * @param string $version
		 * @return boolean
		 */
		public static function is_wc_greater_than( $version ) {
			return version_compare( self::get_wc_version(), $version, '>' );
		}
	}
}