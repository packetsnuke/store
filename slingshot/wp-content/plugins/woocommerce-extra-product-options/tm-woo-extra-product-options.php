<?php
/**
 * Plugin Name: WooCommerce TM Extra Product Options
 * Plugin URI: http://epo.themecomplete.com/
 * Description: A WooCommerce plugin for adding extra product options.
 * Author: themeComplete
 * Author URI: http://themecomplete.com/
 *
 * Version: 4.6.6
 *
 * Requires at least: 4.0
 * Tested up to: 4.9
 *
 * WC requires at least: 2.6
 * WC tested up to: 3.3
 *
 * @package WooCommerce TM Extra Product Options
 * @category Core
 * @author themecomplete
 */

// Prevents direct file access
if ( !defined( 'WPINC' ) ) {
	die;
}

define( 'TM_EPO_PLUGIN_SECURITY', 1 );
define( 'TM_EPO_VERSION', "4.6.6" );
define( 'TM_EPO_PLUGIN_ID', '7908619' );
define( 'TM_EPO_LOCAL_POST_TYPE', "tm_product_cp" );
define( 'TM_EPO_GLOBAL_POST_TYPE', "tm_global_cp" );
define( 'TM_EPO_GLOBAL_POST_TYPE_PAGE_HOOK', "tm-global-epo" );
define( 'TM_EPO_WPML_LANG_META', "tm_meta_lang" );
define( 'TM_EPO_WPML_PARENT_POSTID', "tm_meta_parent_post_id" );
define( 'TM_EPO_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'TM_EPO_TEMPLATE_PATH', TM_EPO_PLUGIN_PATH . '/templates/' );
define( 'TM_EPO_PLUGIN_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'TM_EPO_PLUGIN_NAME_HOOK', plugin_basename( __FILE__ ) );
define( 'TM_EPO_ADMIN_SETTINGS_ID', 'tm_extra_product_options' );
define( 'TM_EPO_DIRECTORY', dirname( plugin_basename( __FILE__ ) ) );
define( 'TM_EPO_PLUGIN_SLUG', TM_EPO_DIRECTORY . '/' . basename( __FILE__ ) );

/** Auto-load classes on demand **/
require_once(TM_EPO_PLUGIN_PATH . '/include/functions/tc-epo-autoload.php');
spl_autoload_register( 'tc_epo_autoload' );

/** Plugin functions **/
require_once(TM_EPO_PLUGIN_PATH . '/include/functions/tc-epo-functions.php');
/** Plugin post types **/
require_once(TM_EPO_PLUGIN_PATH . '/include/functions/tc-post-types.php');
/** Plugin widget **/
require_once(TM_EPO_PLUGIN_PATH . '/include/functions/tc-widget.php');
/** Plugin shortcodes **/
require_once(TM_EPO_PLUGIN_PATH . '/include/functions/tc-shortcodes.php');

/** Plugin functions **/
require_once(TM_EPO_PLUGIN_PATH . '/include/functions/tc-wc-compatibility-functions.php');

/** Initialize updater **/
TM_EPO_LICENSE()->init();
TM_EPO_UPDATER()->init();

/** Check if the plugin can run **/
register_activation_hook( __FILE__, array( 'TM_EPO_CHECK_base', 'activation_check' ) );
if ( TM_EPO_CHECK()->stop_plugin() || (function_exists( 'wp_installing' ) && wp_installing()) ) {
	return;
}

/** Init plugin **/
if ( tc_woocommerce_check() ) {

	/** Load plugin textdomain **/
	add_action( 'plugins_loaded', 'tc_epo_load_textdomain', 10 );

	/** Register post types **/
	add_action( 'init', 'tc_epo_register_post_type' );

	/** Load admin interface **/
	if ( is_admin() ) {

		/** Add settings page **/
		add_filter( 'woocommerce_get_settings_pages', 'tc_add_epo_admin_settings' );

		/** woocommerce_bundle_rate_shipping chosen fix by removing **/
		add_action( 'admin_enqueue_scripts', 'tc_fix_woocommerce_bundle_rate_shipping_scripts', 99 );

		/** Globals Admin Interface **/
		TM_EPO_ADMIN_GLOBAL()->init();

		/** Admin Interface **/
		TM_EPO_ADMIN()->init();

	}

	/** Add shortcodes **/
	add_shortcode( 'tc_epo_show', 'tc_epo_show_shortcode' );
	add_shortcode( 'tc_epo', 'tc_epo_shortcode' );
	add_shortcode( 'tc_epo_totals', 'tc_epo_totals_shortcode' );

	/** Add widget **/
	add_action( 'widgets_init', 'tc_epo_widget' );

	/** Main plugin interface **/
	TM_EPO()->init();

}
