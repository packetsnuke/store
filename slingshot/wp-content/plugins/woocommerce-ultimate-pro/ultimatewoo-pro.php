<?php
/**
 * Plugin Name: UltimateWoo Pro
 * Plugin URI: https://www.ultimatewoo.com/
 * Description: Add dozens of various modules to extend the power of your WooCommerce-powered website, all with just one plugin.
 * Version: 1.5.3
 * Author: UltimateWoo
 * Author URI: https://www.ultimatewoo.com/
 * Text Domain: ultimatewoo-pro
 * Domain Path: /languages/
 *
 * WC tested up to: 3.3
 *
 * License: GPL 2.0+
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 *
 * For a list of credits, please visit https://www.ultimatewoo.com/credit-attributions/
 */

 /*

	Copyright 2014  UltimateWoo  (email : mail@ultimatewoo.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	Permission is hereby granted, free of charge, to any person obtaining a copy of this
	software and associated documentation files (the "Software"), to deal in the Software
	without restriction, including without limitation the rights to use, copy, modify, merge,
	publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons
	to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or
	substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.

*/

//* Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'UltimateWoo_Pro' ) ) :

class UltimateWoo_Pro {

	private static $instance;

	public $licenses;

	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof UltimateWoo_Pro ) ) {
			
			self::$instance = new UltimateWoo_Pro;

			self::$instance->constants();
			self::$instance->includes();
			self::$instance->hooks();

			self::$instance->licenses = new UltimateWoo_Licenses;
		}

		return self::$instance;
	}

	/**
	 *	Constants
	 */
	public function constants() {

		// Plugin version
		if ( ! defined( 'ULTIMATEWOO_PRO_VERSION' ) ) {
			define( 'ULTIMATEWOO_PRO_VERSION', '1.5.3' );
		}

		// Database version
		if ( ! defined( 'ULTIMATEWOO_PRO_DATABASE_VERSION' ) ) {
			define( 'ULTIMATEWOO_PRO_DATABASE_VERSION', '1.0.0' );
		}

		// Plugin file
		if ( ! defined( 'ULTIMATEWOO_PLUGIN_FILE' ) ) {
			define( 'ULTIMATEWOO_PLUGIN_FILE', __FILE__ );
		}

		// Plugin basename
		if ( ! defined( 'ULTIMATEWOO_PLUGIN_BASENAME' ) ) {
			define( 'ULTIMATEWOO_PLUGIN_BASENAME', plugin_basename( ULTIMATEWOO_PLUGIN_FILE ) );
		}

		// Plugin directory path
		if ( ! defined( 'ULTIMATEWOO_PLUGIN_DIR_PATH' ) ) {
			define( 'ULTIMATEWOO_PLUGIN_DIR_PATH', trailingslashit( plugin_dir_path( ULTIMATEWOO_PLUGIN_FILE )  ) );
		}

		// Plugin directory URL
		if ( ! defined( 'ULTIMATEWOO_PLUGIN_DIR_URL' ) ) {
			define( 'ULTIMATEWOO_PLUGIN_DIR_URL', trailingslashit( plugin_dir_url( ULTIMATEWOO_PLUGIN_FILE )  ) );
		}

		// Settings page URL
		if ( ! defined( 'ULTIMATEWOO_SETTINGS_PAGE_URL' ) ) {
			define( 'ULTIMATEWOO_SETTINGS_PAGE_URL', add_query_arg( 'page', 'ultimatewoo', admin_url( 'admin.php' ) ) );
		}

		// Admin settings directory path
		if ( ! defined( 'ULTIMATEWOO_SETTINGS_DIR' ) ) {
			define( 'ULTIMATEWOO_SETTINGS_DIR', ULTIMATEWOO_PLUGIN_DIR_PATH . 'includes/admin/' );
		}

		// Modules directory URL
		if ( ! defined( 'ULTIMATEWOO_MODULES_URL' ) ) {
			define( 'ULTIMATEWOO_MODULES_URL', ULTIMATEWOO_PLUGIN_DIR_URL . 'modules/' );
		}

		// Modules directory path
		if ( ! defined( 'ULTIMATEWOO_MODULES_DIR' ) ) {
			define( 'ULTIMATEWOO_MODULES_DIR', ULTIMATEWOO_PLUGIN_DIR_PATH . 'modules/' );
		}

		// SV framework file
		if ( ! defined( 'SV_WC_FRAMEWORK_FILE' ) ) {
			define( 'SV_WC_FRAMEWORK_FILE', ULTIMATEWOO_MODULES_DIR . 'woocommerce/class-sv-wc-framework-bootstrap.php' );
		}

		// UltimateWoo website - modules page
		if ( ! defined( 'UW_MODULES_WEBSITE_PAGE' ) ) {
			define( 'UW_MODULES_WEBSITE_PAGE', 'https://www.ultimatewoo.com/woocommerce-modules/' );
		}
	}

	/**
	 *	Include PHP files
	 */
	public function includes() {

		// Admin includes
		include_once ULTIMATEWOO_SETTINGS_DIR . 'admin-page.php';
		include_once ULTIMATEWOO_SETTINGS_DIR . 'admin-notices.php';

		// Helper functions
		include_once ULTIMATEWOO_PLUGIN_DIR_PATH . 'includes/helper-functions.php';

		// Updater
		include_once ULTIMATEWOO_PLUGIN_DIR_PATH . 'includes/updater/licenses.php';

		// Database update
		include_once ULTIMATEWOO_PLUGIN_DIR_PATH . 'includes/class-database-update.php';

		$options = ultimatewoo_get_settings();

		// Exit if no options
		if ( ! $options || ! isset( $options['modules'] ) ) {
			return;
		}

		// Module files
		foreach ( ultimatewoo_get_module_sections() as $section ) {

			foreach ( $section['section_modules'] as $module ) {

				// Check if module is in enabled modules array
				$key = array_key_exists( $module['key'], $options['modules'] ) ? intval( $options['modules'][$module['key']] ) : '';

				if ( $key === 1 ) {
					include_once ULTIMATEWOO_MODULES_DIR . $module['include_path'];
				}
			}
		}
	}

	/**
	 *	Action/filter hooks
	 */
	public function hooks() {

		register_activation_hook( ULTIMATEWOO_PLUGIN_FILE, array( $this, 'activate' ) );

		add_action( 'plugins_loaded', array( $this, 'loaded' ) );

		add_filter( 'plugin_action_links_' . ULTIMATEWOO_PLUGIN_BASENAME, array( $this, 'action_links' ) );
		
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_links' ), 10, 2 );
	}

	/**
	 *	Check to see if WooCommerce is active, and initialize the options in database
	 */
	public function activate() {

		// Deactivate and die if WooCommerce is not active
		if ( ! is_woocommerce_active() ) {
			deactivate_plugins( ULTIMATEWOO_PLUGIN_BASENAME );
			wp_die( __( 'Whoops! UltimateWoo requires you to install and activate WooCommerce first.', 'ultimatewoo-pro' ) );
		}

		// Current plugin settings, and default settings for new installs
		$options = ultimatewoo_get_settings();
		$options = is_array( $options ) ? $options : array();
		$initial_options = array( 'db_version' => ULTIMATEWOO_PRO_DATABASE_VERSION );

		// Add option with initial data for fresh installs
		if ( ! isset( $options['db_version'] ) && ! get_option( 'ultimatewoo_license_status' ) ) {
			update_option( 'ultimatewoo', array_merge( $options, $initial_options ) );
		}
	}

	/**
	 *	Load plugin text domain
	 */
	public function loaded() {

		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'ultimatewoo-pro' );
		
		unload_textdomain( 'ultimatewoo-pro' );
		load_textdomain( 'ultimatewoo-pro', WP_LANG_DIR . '/ultimatewoo-pro/ultimatewoo-pro-' . $locale . '.mo' );
		load_plugin_textdomain( 'ultimatewoo-pro', false, dirname( __FILE__ ) . '/languages' );
	}

	/**
	 *	Plugin action links
	 */
	public function action_links( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', ULTIMATEWOO_SETTINGS_PAGE_URL, __( 'Settings', 'ultimatewoo-pro' ) );
		return $links;
	}

	/**
	 *	Plugin info row links
	 */
	public function plugin_row_links( $links, $file ) {

		if ( $file == ULTIMATEWOO_PLUGIN_BASENAME ) {

			$links[] = sprintf( '<a href="https://www.ultimatewoo.com/account" target="_blank">%s</a>', __( 'Support', 'ultimatewoo-pro' ) );
		}

		return $links;
	}
}

endif;

/**
 *	Main function
 *	@return object UltimateWoo_Pro instance
 */
function UltimateWoo_Pro() {
	return UltimateWoo_Pro::instance();
}

UltimateWoo_Pro();