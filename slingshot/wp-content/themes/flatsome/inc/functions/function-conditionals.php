<?php
/**
 * Flatsome Conditional Functions
 *
 * @author   UX Themes
 * @package  Flatsome/Functions
 */

if ( ! function_exists( 'is_nextend_facebook_login' ) ) {
	/**
	 * Returns true if Nextend Facebook Connect plugin is activated
	 *
	 * @return bool
	 */
	function is_nextend_facebook_login() {
		return in_array( 'nextend-facebook-connect/nextend-facebook-connect.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}
}

if ( ! function_exists( 'is_nextend_google_login' ) ) {
	/**
	 * Returns true if Nextend Google Connect plugin is activated
	 *
	 * @return bool
	 */
	function is_nextend_google_login() {
		return in_array( 'nextend-google-connect/nextend-google-connect.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}
}

if ( ! function_exists( 'is_woocommerce_activated' ) ) {
	/**
	 * Returns true if WooCommerce plugin is activated
	 *
	 * @return bool
	 */
	function is_woocommerce_activated() {
		return class_exists( 'woocommerce' );
	}
}

if ( ! function_exists( 'is_portfolio_activated' ) ) {
	/**
	 * Returns "1" if Flatsome Portfolio option is enabled
	 *
	 * @return string
	 */
	function is_portfolio_activated() {
		return get_theme_mod( 'fl_portfolio', 1 );
	}
}

if ( ! function_exists( 'is_extension_activated' ) ) {
	/**
	 * Returns true if extension is activated
	 *
	 * @param string $extension Extension Class name.
	 * @return bool
	 */
	function is_extension_activated( $extension ) {
		return class_exists( $extension );
	}
}
