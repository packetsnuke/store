<?php

/**
 * Plugin Name: WooCommerce Custom Fields
 * Plugin URI: http://www.rightpress.net/woocommerce-custom-fields
 * Description: Create custom fields for WooCommerce product, checkout, order and customer pages
 * Author: RightPress
 * Author URI: http://www.rightpress.net
 *
 * Text Domain: rp_wccf
 * Domain Path: /languages
 *
 * Version: 2.2.4
 *
 * Requires at least: 3.6
 * Tested up to: 4.9
 *
 * WC requires at least: 2.5
 * WC tested up to: 3.3
 *
 * @package WooCommerce Custom Fields
 * @category Core
 * @author RightPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define Constants
define('WCCF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WCCF_PLUGIN_URL', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)));
define('WCCF_VERSION', '2.2.4');
define('WCCF_SUPPORT_PHP', '5.3');
define('WCCF_SUPPORT_WP', '3.6');
define('WCCF_SUPPORT_WC', '2.5');

if (!class_exists('WCCF')) {

/**
 * Main plugin class
 *
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
class WCCF
{

    /*
        Field data storage structure since version 2.0:
            - Checkout Fields
                _wccf_cf_{key}          = {field_value}         in order meta
                _wccf_cf_id_{key}       = {field_id}            in order meta
                _wccf_cf_data_{key}     = {extra_data_array}    in order meta
                _wccf_file_{access_key} = {file_data}           in order meta
            - Order Fields
                _wccf_of_{key}          = {field_value}         in order meta
                _wccf_of_id_{key}       = {field_id}            in order meta
                _wccf_of_data_{key}     = {extra_data_array}    in order meta
                _wccf_file_{access_key} = {file_data}           in order meta
            - Product Fields
                _wccf_pf_{key}          = {field_value}         in order item meta
                _wccf_pf_id_{key}       = {field_id}            in order item meta
                _wccf_pf_data_{key}     = {extra_data_array}    in order item meta
                _wccf_file_{access_key} = {file_data}           in order item meta
            - Product Properties
                _wccf_pp_{key}          = {field_value}         in product meta
                _wccf_pp_id_{key}       = {field_id}            in product meta
                _wccf_pp_data_{key}     = {extra_data_array}    in product meta
                _wccf_file_{access_key} = {file_data}           in product meta
            - User Fields
                _wccf_uf_{key}          = {field_value}         in user meta
                _wccf_uf_id_{key}       = {field_id}            in user meta
                _wccf_uf_data_{key}     = {extra_data_array}    in user meta
                _wccf_file_{access_key} = {file_data}           in user meta
    */

    // Singleton instance
    private static $instance = false;

    /**
     * Singleton control
     */
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Load translation
        load_textdomain('rp_wccf', WP_LANG_DIR . '/woocommerce-custom-fields/rp_wccf-' . apply_filters('plugin_locale', get_locale(), 'rp_wccf') . '.mo');
        load_plugin_textdomain('rp_wccf', false, dirname(plugin_basename(__FILE__)) . '/languages/');

        // Initialize automatic updates
        require_once(plugin_dir_path(__FILE__) . 'includes/classes/libraries/rightpress-updates.class.php');
        RightPress_Updates_11332742::init(__FILE__, WCCF_VERSION);

        // Admin-only hooks
        if (is_admin() && !defined('DOING_AJAX')) {

            // Additional Plugins page links
            add_filter('plugin_action_links_'.plugin_basename(__FILE__), array($this, 'plugins_page_links'));

            // Add settings page menu link
            add_action('admin_menu', array($this, 'admin_menu'), 11);
        }

        // Load includes when plugins are loaded
        add_action('plugins_loaded', array($this, 'load_includes'), 1);
    }

    /**
     * Load required classes
     *
     * @access public
     * @return void
     */
    public function load_includes()
    {
        // Load helper class
        require_once WCCF_PLUGIN_PATH . 'rightpress/rightpress-helper.class.php';
        require_once WCCF_PLUGIN_PATH . 'rightpress/rightpress-wc-meta.class.php';
        require_once WCCF_PLUGIN_PATH . 'rightpress/rightpress-wc-legacy.class.php';

        // Check environment
        if (!WCCF::check_environment()) {
            return;
        }

        // Load includes
        foreach (glob(WCCF_PLUGIN_PATH . 'includes/*.inc.php') as $filename)
        {
            require_once $filename;
        }

        // Load abstract classes
        foreach (glob(WCCF_PLUGIN_PATH . 'includes/classes/abstract/*.class.php') as $filename) {
            require_once $filename;
        }

        // Load parent field classes
        foreach (glob(WCCF_PLUGIN_PATH . 'includes/classes/field/parent/*.class.php') as $filename) {
            require_once $filename;
        }

        // Load child field classes
        foreach (glob(WCCF_PLUGIN_PATH . 'includes/classes/field/child/*.class.php') as $filename) {
            require_once $filename;
        }

        // Load other classes
        foreach (glob(WCCF_PLUGIN_PATH . 'includes/classes/*.class.php') as $filename)
        {
            require_once $filename;
        }

        // Load integrations
        foreach (glob(WCCF_PLUGIN_PATH . 'integrations/*.class.php') as $filename) {
            require_once $filename;
        }
    }

    /**
     * Check if current user is admin or it's equivalent (shop manager etc)
     *
     * @access public
     * @param string $action
     * @param array $params
     * @return bool
     */
    public static function is_admin($action = null, $params = array())
    {
        return current_user_can(self::get_admin_capability($action, $params));
    }

    /**
     * Get admin capability
     *
     * @access public
     * @param string $action
     * @param array $params
     * @return string
     */
    public static function get_admin_capability($action = null, $params = array())
    {
        $admin_capability = apply_filters('wccf_capability', 'manage_woocommerce', $action, $params);
        $admin_capability = apply_filters('rp_wccf_capability', $admin_capability, $action, $params); // Legacy filter
        return $admin_capability;
    }

    /**
     * Check if user is authorized to do current action - proxy with filter
     *
     * @access public
     * @param string $action
     * @param array $params
     * @return bool
     */
    public static function is_authorized($action, $params = array())
    {
        // System is always authorized
        if (defined('WCCF_IS_SYSTEM') && WCCF_IS_SYSTEM) {
            return true;
        }

        if ($action === 'manage_posts') {
            $action = 'manage_fields';
        }

        return (bool) apply_filters('wccf_is_authorized', WCCF::is_authorized_check($action, $params), $action, $params);
    }

    /**
     * Check if user is authorized to do current action
     *
     * This is used to check if user is shop manager or if user is allowed to
     * do specific action when other means of authorization are not sufficient
     * (like in our own specific ajax requests)
     *
     * @access public
     * @param string $action
     * @param array $params
     * @return bool
     */
    public static function is_authorized_check($action, $params = array())
    {
        // Shop manager is allowed to do everything
        if (WCCF::is_admin($action, $params)) {
            return true;
        }

        // Actions allowed for other users
        $non_admin_actions = array('upload_file', 'edit_user_submitted_values');

        // Check if action is allowed for other users
        if (!in_array($action, $non_admin_actions, true)) {
            return false;
        }

        // Check by item id
        if (!empty($params['item_id']) && !empty($params['context'])) {

            // Get item id
            $item_id = (int) $params['item_id'];

            // Get correct capability
            $capability = $params['context'] === 'user_field' ? 'edit_users' : 'edit_posts';

            // Fix item id for order items - need to check if user has access to whole order
            if ($params['context'] === 'product_field') {

                // Get order id
                $item_id = RightPress_Helper::get_wc_order_id_from_order_item_id($item_id);

                // Faile to determine order id
                if (!$item_id) {
                    return false;
                }
            }

            // Check capability
            return current_user_can($capability, $item_id);
        }

        // Not authorized
        return false;
    }

    /**
     * Check if current request is for a plugin's settings page
     *
     * @access public
     * @return bool
     */
    public static function is_settings_page()
    {
        global $typenow;
        global $post;

        // Attempt to get post type from global $post variable
        if (!$typenow && $post && is_object($post) && isset($post->post_type) && $post->post_type) {
            $typenow = $post->post_type;
        }

        // Attempt to get post type from query var post_type
        if (!$typenow && isset($_REQUEST['post_type'])) {
            $typenow = $_REQUEST['post_type'];
        }

        // Attempt to get post type from query var post
        if (!$typenow && !empty($_REQUEST['post']) && is_numeric($_REQUEST['post'])) {
            if (function_exists('get_post_type') && ($post_type = get_post_type($_REQUEST['post']))) {
                $typenow = $post_type;
            }
        }

        // Known post types
        if ($typenow && array_key_exists($typenow, WCCF_Post_Object_Controller::get_post_types())) {
            return true;
        }

        return false;
    }

    /**
     * Check if environment meets requirements
     *
     * @access public
     * @return bool
     */
    public static function check_environment()
    {
        $is_ok = true;

        // Check PHP version
        if (!version_compare(PHP_VERSION, WCCF_SUPPORT_PHP, '>=')) {

            // Add notice
            add_action('admin_notices', array('WCCF', 'php_version_notice'));

            // Do not proceed as RightPress Helper requires PHP 5.3 for itself
            return false;
        }

        // Check WordPress version
        if (!RightPress_Helper::wp_version_gte(WCCF_SUPPORT_WP)) {
            add_action('admin_notices', array('WCCF', 'wp_version_notice'));
            $is_ok = false;
        }

        // Check if WooCommerce is enabled
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array('WCCF', 'wc_disabled_notice'));
            $is_ok = false;
        }
        else if (!RightPress_Helper::wc_version_gte(WCCF_SUPPORT_WC)) {
            add_action('admin_notices', array('WCCF', 'wc_version_notice'));
            $is_ok = false;
        }

        return $is_ok;
    }

    /**
     * Display PHP version notice
     *
     * @access public
     * @return void
     */
    public static function php_version_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce Custom Fields</strong> requires PHP %s or later. Please update PHP on your server to use this plugin.', 'rp_wccf'), WCCF_SUPPORT_PHP) . ' ' . sprintf(__('If you have any questions, please contact %s.', 'rp_wccf'), '<a href="http://url.rightpress.net/new-support-ticket">' . __('RightPress Support', 'rp_wccf') . '</a>') . '</p></div>';
    }

    /**
     * Display WP version notice
     *
     * @access public
     * @return void
     */
    public static function wp_version_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce Custom Fields</strong> requires WordPress version %s or later. Please update WordPress to use this plugin.', 'rp_wccf'), WCCF_SUPPORT_WP) . ' ' . sprintf(__('If you have any questions, please contact %s.', 'rp_wccf'), '<a href="http://url.rightpress.net/new-support-ticket">' . __('RightPress Support', 'rp_wccf') . '</a>') . '</p></div>';
    }

    /**
     * Display WC disabled notice
     *
     * @access public
     * @return void
     */
    public static function wc_disabled_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce Custom Fields</strong> requires WooCommerce to be active. You can download WooCommerce %s.', 'rp_wccf'), '<a href="http://url.rightpress.net/woocommerce-download-page">' . __('here', 'rp_wccf') . '</a>') . ' ' . sprintf(__('If you have any questions, please contact %s.', 'rp_wccf'), '<a href="http://url.rightpress.net/new-support-ticket">' . __('RightPress Support', 'rp_wccf') . '</a>') . '</p></div>';
    }

    /**
     * Display WC version notice
     *
     * @access public
     * @return void
     */
    public static function wc_version_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce Custom Fields</strong> requires WooCommerce version %s or later. Please update WooCommerce to use this plugin.', 'rp_wccf'), WCCF_SUPPORT_WC) . ' ' . sprintf(__('If you have any questions, please contact %s.', 'rp_wccf'), '<a href="http://url.rightpress.net/new-support-ticket">' . __('RightPress Support', 'rp_wccf') . '</a>') . '</p></div>';
    }

    /**
     * Add settings link on plugins page
     *
     * @access public
     * @param array $links
     * @return void
     */
    public function plugins_page_links($links)
    {
        // Add support link
        $settings_link = '<a href="http://url.rightpress.net/woocommerce-custom-fields-help" target="_blank">'.__('Support', 'rp_wccf').'</a>';
        array_unshift($links, $settings_link);

        // Add settings link
        if (self::check_environment()) {
            $settings_link = '<a href="edit.php?post_type=wccf_product_field">'.__('Settings', 'rp_wccf').'</a>';
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    /**
     * Add or remove admin menu items
     *
     * @access public
     * @return void
     */
    public function admin_menu()
    {
        global $submenu;

        // Define new menu order
        $reorder = array(
            'edit.php?post_type=wccf_product_prop'      => 52,
            'edit.php?post_type=wccf_checkout_field'    => 53,
            'edit.php?post_type=wccf_order_field'       => 54,
            'edit.php?post_type=wccf_user_field'        => 55,
        );

        // Check if our menu exists
        if (isset($submenu['edit.php?post_type=wccf_product_field'])) {

            // Iterate over submenu items
            foreach ($submenu['edit.php?post_type=wccf_product_field'] as $item_key => $item) {

                // Remove Add Field menu link
                if (in_array('post-new.php?post_type=wccf_product_field', $item)) {
                    unset($submenu['edit.php?post_type=wccf_product_field'][$item_key]);
                }

                // Rearrange other items
                foreach ($reorder as $order_key => $order) {
                    if (in_array($order_key, $item)) {
                        $submenu['edit.php?post_type=wccf_product_field'][$order] = $item;
                        unset($submenu['edit.php?post_type=wccf_product_field'][$item_key]);
                    }
                }
            }

            // Sort array by key
            ksort($submenu['edit.php?post_type=wccf_product_field']);
        }
    }

    /**
     * Include template
     *
     * @access public
     * @param string $template
     * @param array $args
     * @return string
     */
    public static function include_template($template, $args = array())
    {
        RightPress_Helper::include_template($template, WCCF_PLUGIN_PATH, 'woocommerce-custom-fields', $args);
    }


}

WCCF::get_instance();

}
