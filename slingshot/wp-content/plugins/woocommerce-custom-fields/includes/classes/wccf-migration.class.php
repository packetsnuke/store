<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to data migration between different versions
 *
 * @class WCCF_Migration
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Migration')) {

class WCCF_Migration
{
    // Singleton instance
    protected static $instance = false;

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
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Data migration
        add_action('init', array($this, 'maybe_migrate_data'), 999);

        // Migration notices
        add_action('admin_notices', array($this, 'maybe_display_migration_notice'), 1);

        // Hide migration notices
        add_action('init', array($this, 'maybe_hide_migration_notice'));

        // Download files stored with older versions
        if (!empty($_GET['wccf_version_1_file_download'])) {
            add_action('wp_loaded', array($this, 'file_download_from_1'), 99);
        }
    }

    /**
     * Check if data migration features need to execute
     *
     * @access public
     * @param string $version
     * @return bool
     */
    public static function support_for($version)
    {
        if (in_array($version, array(1, '1', '1.0', '1.x'), true)) {
            return (bool) get_option('rp_wccf_options');
        }

        return false;
    }

    /**
     * Maybe display migration notice
     *
     * @access public
     * @return void
     */
    public function maybe_display_migration_notice()
    {
        // Migration from 1.x to 2.0+ notice
        if ($notice = get_option('wccf_migration_notice_1_to_2')) {
            printf('<div class="update-nag" style="display: block; border-left-color: #dc3232;"><h3 style="margin-top: 0.3em; margin-bottom: 0.6em;">Action Required!</h3>' . $notice . '<p><a href="%s">Contact Support</a>&nbsp;&nbsp;&nbsp;<a href="%s">Hide this notice</a></p></div>', 'http://url.rightpress.net/new-support-ticket', add_query_arg('wccf_hide_migration_notice_1_to_2', '1'));
        }
    }

    /**
     * Maybe hide migration notice
     *
     * @access public
     * @return void
     */
    public function maybe_hide_migration_notice()
    {
        // Hide migration from 1.x to 2.0+ notice
        if (!empty($_REQUEST['wccf_hide_migration_notice_1_to_2'])) {
            delete_option('wccf_migration_notice_1_to_2');
            wp_redirect(remove_query_arg('wccf_hide_migration_notice_1_to_2'));
            exit;
        }
    }

    /**
     * Maybe migrate data
     *
     * @access public
     * @return void
     */
    public function maybe_migrate_data()
    {
        // Migrate from 1.x to 2.0+
        if (version_compare(WCCF_VERSION, '2.0', '>=')) {

            // Already migrated
            if (WCCF_Migration::already_migrated_to('2.0')) {
                return;
            }

            // Load old options
            $old_options = get_option('rp_wccf_options');

            // No options
            if ($old_options === false) {
                WCCF_Migration::set_migrated_to('2.0');
                return;
            }

            // Invalid options
            if (empty($old_options) || !is_array($old_options) || empty($old_options[1]) || !is_array($old_options[1])) {
                WCCF_Migration::set_migrated_to('2.0');
                return;
            }

            // Already migrated but our flag was cleared by developer
            $migrated_to_option_key = WCCF_Migration::get_migrated_to_option_key('2.0');

            if (!empty($old_options[1][$migrated_to_option_key])) {
                WCCF_Migration::set_migrated_to('2.0');
                return;
            }

            // Acquire lock
            if (!WCCF_Migration::set_migrated_to('2.0')) {
                return;
            }

            // Add admin notice - adding error notice here, will replace with success notice at the end of the process
            $html = sprintf('<p><strong>WooCommerce Custom Fields</strong> was updated to version <strong>%s</strong> which differs a lot from the one that you were just using.</p><p>Settings and data migration was attempted automatically, however, some <span style="color: red;">unexpected problems occurred</span> during migration which may or may not affect functionality.</p><p>We ask that you check all your <a href="%s">settings</a> and <a href="%s">field configuration</a> to make sure that everything works as expected and <a href="%s">get in touch with us</a> immediately if you notice anything unusual.</p><p>If you customized functionality of this extension or styling of your fields in any way (filter hooks, CSS rules), you must check if your customizations are still working as expected.</p><p>If you wish to downgrade temporarily, you can download the previous version from <a href="%s">here</a>.</p>', WCCF_VERSION, admin_url('/edit.php?post_type=wccf_product_field&page=wccf_settings'), admin_url('/edit.php?post_type=wccf_product_field'), 'http://url.rightpress.net/new-support-ticket', 'http://url.rightpress.net/download-wccf-1-2-1');
            update_option('wccf_migration_notice_1_to_2', $html);

            // Dump options in case we need to investigate
            update_option('wccf_migrating_1_to_2_' . time(), $old_options, false);

            // Update options to include our flag
            $old_options[1][$migrated_to_option_key] = 1;
            update_option('rp_wccf_options', $old_options);

            // Make sure we don't run into any authorization problems (this script can even run on a guest user request)
            define('WCCF_IS_SYSTEM', true);

            // Migrate options from 1.x to 2.0+
            WCCF_Migration::from_1_to_2($old_options[1]);

            // Redirect user
            wp_redirect(add_query_arg('wccf_migrated', RightPress_Helper::get_hash()));
            exit;
        }
    }

    /**
     * Check if data was already migrated to specific version
     *
     * @access public
     * @param string $version
     * @return bool
     */
    public static function already_migrated_to($version)
    {
        $option_key = WCCF_Migration::get_migrated_to_option_key($version);
        return (bool) get_option($option_key);
    }

    /**
     * Set flag which indicates that data was already migrated to specific version
     *
     * Can also be used to ensure that we migrate data only once and do not
     * experience race conditions since add_option returns false if option
     * by that key already exists
     *
     * @access public
     * @param string $version
     * @return bool
     */
    public static function set_migrated_to($version)
    {
        $option_key = WCCF_Migration::get_migrated_to_option_key($version);
        return add_option($option_key, true);
    }

    /**
     * Get migrated to option key
     *
     * @access public
     * @param string $version
     * @return string
     */
    public static function get_migrated_to_option_key($version)
    {
        return 'wccf_migrated_to_' . WCCF_Migration::get_version_key($version);
    }

    /**
     * Get version key from version number
     *
     * @access public
     * @param string $version_number
     * @return string
     */
    public static function get_version_key($version)
    {
        return preg_replace('/\D/', '', $version);
    }

    /**
     * Migrate from 1.x to 2.0+
     *
     * @access public
     * @param array $config
     * @return void
     */
    public static function from_1_to_2($config)
    {
        // Reset PHP execution time limit and set it to 5 minutes from now
        @set_time_limit(300);

        $error_occurred = false;

        // Get post types
        $post_types = WCCF_Field_Controller::get_post_types();

        // General settings
        if (isset($config['rp_wccf_date_format'])) {
            WCCF_Settings::update('date_format', $config['rp_wccf_date_format']);
        }
        if (isset($config['rp_wccf_attach_new_order'])) {
            WCCF_Settings::update('attach_product_field_files_new_order', $config['rp_wccf_attach_new_order']);
            WCCF_Settings::update('attach_checkout_field_files_new_order', $config['rp_wccf_attach_new_order']);
        }
        if (isset($config['rp_wccf_prices_product_page'])) {
            WCCF_Settings::update('prices_product_page', $config['rp_wccf_prices_product_page']);
        }
        if (isset($config['rp_wccf_prices_cart_order_page'])) {
            WCCF_Settings::update('prices_cart_order_page', $config['rp_wccf_prices_cart_order_page']);
        }
        if (isset($config['rp_wccf_display_total_price'])) {
            WCCF_Settings::update('display_total_price', $config['rp_wccf_display_total_price']);
        }
        if (isset($config['rp_wccf_alias_product'])) {
            WCCF_Settings::update('alias_product_field', $config['rp_wccf_alias_product']);
        }
        if (isset($config['rp_wccf_alias_product_admin'])) {
            WCCF_Settings::update('alias_product_prop', $config['rp_wccf_alias_product_admin']);
        }
        if (isset($config['rp_wccf_alias_checkout'])) {
            WCCF_Settings::update('alias_checkout_field', $config['rp_wccf_alias_checkout']);
        }
        if (isset($config['rp_wccf_alias_order'])) {
            WCCF_Settings::update('alias_order_field', $config['rp_wccf_alias_order']);
        }

        // Define field contexts
        $contexts = array(
            'product'       => 'product_field',
            'product_admin' => 'product_prop',
            'checkout'      => 'checkout_field',
            'order'         => 'order_field',
        );

        // Pricing method mapping
        $pricing_method_map = array(
            'surcharge'                 => 'fees_fee',
            'surcharge_per_character'   => 'advanced_fees_fee_per_character',
            'discount'                  => 'discounts_discount',
        );

        // Condition mapping
        $condition_map = array(
            'customer_is_logged_in'             => 'customer_is_logged_in',
            'customer_role'                     => 'customer_role',
            'customer_capability'               => 'customer_capability',
            'product_product'                   => 'product_product',
            'product_product_category'          => 'product_product_category',
            'product_product_type'              => 'product_product_type',
            'custom_field_other_custom_field'   => 'custom_field_other_custom_field',
            'cart_subtotal'                     => 'cart_subtotal',
            'cart_products_in_cart'             => 'cart_items_products_in_cart',
            'cart_product_categories_in_cart'   => 'cart_items_product_categories_in_cart',
            'customer_order_role'               => 'customer_order_role',
            'customer_order_capability'         => 'customer_order_capability',
            'order_total'                       => 'order_total',
            'order_payment_method'              => 'order_payment_method',
            'order_shipping_method'             => 'order_shipping_method',
            'order_products_in_order'           => 'order_items_products_in_order',
            'order_product_categories_in_order' => 'order_items_product_categories_in_order',
        );

        // Iterate over field contexts
        foreach ($contexts as $old_context => $context) {

            // No fields of given context available
            if (empty($config[$old_context . '_fb_config']) || !is_array($config[$old_context . '_fb_config'])) {
                continue;
            }

            // Get post type and class name
            $post_type = 'wccf_' . $context;
            $class_name = $post_types[$post_type];

            // Get list of supported field types
            $controller = WCCF_Field_Controller::get_controller_instance_by_context($context);
            $field_types = $controller::get_field_type_list();

            // Get list of supported checkout field positions
            $checkout_positions = WCCF_WC_Checkout::get_positions();

            // Reference old fields
            $old_fields = $config[$old_context . '_fb_config'];

            // Iterate over old fields
            foreach ($old_fields as $old_field_index => $old_field) {

                // Check some basic properties
                if ((empty($old_field['key']) && $old_field['key'] !== '0') || empty($old_field['type']) || !isset($field_types[$old_field['type']])) {
                    $error_occurred = true;
                    continue;
                }

                // Get field type
                $field_type = $old_field['type'];

                // Get field key
                $field_key = (string) $controller->ensure_unique_key($old_field['key']);

                // Configure field
                $field_config = array(
                    'status'            => 'disabled',
                    'field_type'        => $field_type,
                    'key'               => $field_key,
                    'label'             => (string) ((!empty($old_field['label']) || $old_field['label'] === '0') ? $old_field['label'] : $field_key),
                    'required'          => !empty($old_field['required']),
                    'description'       => (string) (isset($old_field['description']) ? $old_field['description'] : ''),
                    'custom_css'        => (string) (isset($old_field['css']) ? $old_field['css'] : ''),
                    'character_limit'   => (isset($old_field['character_limit']) ? (int) $old_field['character_limit'] : null),
                );

                // Public
                if (in_array($context, array('order_field', 'product_prop'), true) && !empty($old_field['public'])) {
                    $field_config['public'] = true;
                }

                // Pricing method and pricing value
                if (in_array($context, array('product_field', 'product_prop', 'checkout_field'), true) && !WCCF_FB::uses_options($field_type) && !empty($old_field['price_method']) && !empty($old_field['price_value'])) {
                    if (isset($pricing_method_map[$old_field['price_method']])) {
                        $field_config['pricing_method'] = $pricing_method_map[$old_field['price_method']];
                        $field_config['pricing_value']  = (float) $old_field['price_value'];
                    }
                    else {
                        $error_occurred = true;
                    }
                }

                // Position
                if ($context === 'checkout_field' && !empty($old_field['position'])) {
                    if (isset($checkout_positions[$old_field['position']])) {
                        $field_config['position'] = $old_field['position'];
                    }
                    else {
                        $error_occurred = true;
                    }
                }

                // Insert post
                $post_id = wp_insert_post(array(
                    'post_title'        => '',
                    'post_content'      => '',
                    'post_name'         => '',
                    'post_status'       => 'draft',
                    'post_type'         => $post_type,
                    'ping_status'       => 'closed',
                    'comment_status'    => 'closed',
                ));

                // Unable to insert post
                if (is_wp_error($post_id) || empty($post_id)) {
                    $error_occurred = true;
                    continue;
                }

                // Initialize new field
                $field = new $class_name($post_id);

                // Disable for now
                $field->disable();

                // Save object configuration
                $field->save_configuration(array(
                    'post_ID'           => $post_id,
                    'wccf_post_config'  => $field_config,
                ), 'duplicate', true);

                // Take reference of field
                $old_fields[$old_field_index]['wccf_field'] = $field;
            }

            // Set up options and conditions
            foreach ($old_fields as $old_field_index => $old_field) {

                // Field was not added
                if (empty($old_field['wccf_field'])) {
                    continue;
                }

                // Take reference of field
                $field = $old_field['wccf_field'];

                // Migrate options
                $used_option_keys = array();
                $options = array();

                // Iterate over options
                if ($field->uses_options() && !empty($old_field['options']) && is_array($old_field['options'])) {
                    foreach ($old_field['options'] as $option) {

                        // Option key not set
                        if (empty($option['key']) && $option['key'] !== '0') {
                            $error_occurred = true;
                            continue;
                        }

                        // Get key
                        $option_key = strtolower(preg_replace('/[^A-Z0-9_]/i', '', (string) $option['key']));

                        // Ensure key is unique
                        $option_key = RightPress_Helper::ensure_unique_string($option_key, $used_option_keys);

                        // Add key to option keys array
                        $used_option_keys[] = $option_key;

                        // Pricing method and pricing value
                        $pricing_method = null;
                        $pricing_value = null;

                        if ($field->uses_pricing() && !empty($option['price_method']) && !empty($option['price_value'])) {
                            if (isset($pricing_method_map[$option['price_method']])) {
                                $pricing_method = $pricing_method_map[$option['price_method']];
                                $pricing_value  = (float) $option['price_value'];
                            }
                            else {
                                $error_occurred = true;
                            }
                        }

                        // Add option
                        $options[] = array(
                            'key'               => $option_key,
                            'label'             => (string) (isset($option['label']) ? $option['label'] : ''),
                            'pricing_method'    => $pricing_method,
                            'pricing_value'     => $pricing_value,
                            'selected'          => !empty($option['selected']) ? '1' : '0',
                        );
                    }
                }

                // Store options
                $field->update_field('options', $options);

                // Migrate conditions
                $conditions = array();

                // Iterate over conditions
                if (!empty($old_field['conditions']) && is_array($old_field['conditions'])) {
                    foreach ($old_field['conditions'] as $condition) {

                        $multiselect_value_set = false;

                        // Condition type or condition method is not known
                        if (!isset($condition_map[$condition['type']]) || !isset($condition[$condition['type'] . '_method'])) {
                            $error_occurred = true;
                            continue;
                        }

                        // Reference condition type
                        $condition_type = $condition_map[$condition['type']];

                        // Build new condition
                        $current_condition = array(
                            'type' => $condition_type,
                        );

                        // Add condition method
                        $current_condition[$condition_map[$condition['type']] . '_method'] = $condition[$condition['type'] . '_method'];

                        // Text value
                        if (in_array($condition_type, array('custom_field_other_custom_field'), true)) {
                            $current_condition['text'] = (!empty($condition['text']) || $condition['text'] === '0') ? (string) $condition['text'] : '';
                        }
                        // Decimal value
                        else if (in_array($condition_type, array('cart_subtotal', 'order_total'), true)) {
                            $current_condition['decimal'] = (!empty($condition['decimal']) || $condition['decimal'] === '0') ? $condition['decimal'] : '';
                        }
                        // Multiselect value
                        else if (in_array($condition_type, array('customer_role', 'customer_capability', 'customer_order_role', 'customer_order_capability', 'product_product', 'product_product_category', 'product_product_type', 'cart_items_products_in_cart', 'cart_items_product_categories_in_cart', 'order_items_products_in_order', 'order_items_product_categories_in_order', 'order_payment_method', 'order_shipping_method'), true)) {
                            foreach (array('roles', 'capabilities', 'products', 'product_categories', 'product_types', 'payment_methods', 'shipping_methods') as $multiselect_field_key) {
                                if (isset($condition[$multiselect_field_key])) {
                                    $current_condition[$multiselect_field_key] = (!empty($condition[$multiselect_field_key]) || $condition[$multiselect_field_key] === '0') ? (array) $condition[$multiselect_field_key] : array();
                                    $multiselect_value_set = true;
                                    break;
                                }
                            }
                        }

                        // Condition must either not take value for comparison at all or have value set by now
                        if ($condition_type !== 'customer_is_logged_in' && !isset($current_condition['text']) && !isset($current_condition['decimal']) && !$multiselect_value_set) {
                            $error_occurred = true;
                            continue;
                        }

                        // Other field frontend condition must have other field set identifier set
                        if ($condition_type === 'custom_field_other_custom_field') {

                            // Other field key set, let's try to find corresponding field
                            if (isset($condition['other_field_key'])) {

                                $other_field_id = null;

                                // Iterate over all fields and try to match field by its key
                                foreach ($old_fields as $old_field_index_c => $old_field_c) {

                                    // Field can't reference itself in conditions
                                    if ($old_field_index === $old_field_index_c) {
                                        continue;
                                    }

                                    // Field is set
                                    if (is_object($old_field_c['wccf_field'])) {

                                        // Reference field
                                        $field_c = $old_field_c['wccf_field'];

                                        // Compare keys
                                        if ($field_c->get_key() === $condition['other_field_key']) {
                                            $other_field_id = $field_c->get_id();
                                            break;
                                        }
                                    }
                                }

                                // Add other field id or zero to prevent it from being displayed
                                $current_condition['other_field_id'] = $other_field_id !== null ? $other_field_id : 0;
                            }
                            // Not set - condition invalid
                            else {
                                $error_occurred = true;
                                continue;
                            }
                        }

                        // Add condition
                        $conditions[] = $current_condition;
                    }
                }

                // Store conditions
                $field->update_field('conditions', $conditions);

                // Enable field (all existing fields were treated as enabled in previous version)
                $field->enable();
            }

            // Update field sort order
            $all_post_ids = array();

            foreach ($old_fields as $old_field_index => $old_field) {
                if (is_object($old_field['wccf_field'])) {
                    $field = $old_field['wccf_field'];
                    $all_post_ids[] = $field->get_id();
                }
            }

            WCCF_Field_Controller::store_field_sort_order(array('post' => $all_post_ids), $all_post_ids);
        }

        // Change error notice with success notice
        if (!$error_occurred) {
            $html = sprintf('<p><strong>WooCommerce Custom Fields</strong> was updated to version <strong>%s</strong> which differs a lot from the one that you were just using.</p><p>Your data and settings were migrated automatically but we ask that you double check all your <a href="%s">settings</a> and <a href="%s">field configuration</a> as well as make sure that fields are working fine in the frontend.</p><p>If you customized functionality of this extension or styling of your fields in any way (filter hooks, CSS rules), you must check if your customizations are still working as expected.</p>', WCCF_VERSION, admin_url('/edit.php?post_type=wccf_product_field&page=wccf_settings'), admin_url('/edit.php?post_type=wccf_product_field'));
            update_option('wccf_migration_notice_1_to_2', $html);
        }
    }

    /**
     * Migrate structure of product field values in cart from 1.x to 2.0+
     *
     * @access public
     * @param array $old
     * @return array
     */
    public static function product_fields_in_cart_from_1_to_2($old)
    {
        $new = array();

        // Iterate over values
        foreach ($old as $key => $value) {

            // Key and value must be set to migrate data
            if (!isset($value['key']) || !isset($value['value'])) {
                continue;
            }

            // Attempt to load field
            $field = WCCF_Field_Controller::get_field_by_key('product_field', $value['key'], true);

            // No such field
            if (!$field) {
                continue;
            }

            // Move files if any
            if (!empty($value['file'])) {
                if (!empty($value['file']['name']) && !empty($value['file']['type']) && !empty($value['file']['path']) && file_exists($value['file']['path'])) {

                    // Store file
                    if ($storage_data = WCCF_Files::store_file(array('tmp_name' => $value['file']['path']))) {

                        // Generate access key
                        $access_key = WCCF_Files::get_unique_file_access_key();

                        // Set value to access key
                        $value['value'] = array($access_key);

                        // Add file data
                        $files[$access_key] = array(
                            'subdirectory'  => $storage_data['subdirectory'],
                            'storage_key'   => $storage_data['storage_key'],
                            'name'          => $value['file']['name'],
                            'type'          => $value['file']['type'],
                            'field_id'      => $field->get_id(),
                        );
                    }
                    else {
                        continue;
                    }
                }
                else {
                    continue;
                }
            }
            else {
                $files = array();
            }

            // Add data in new format
            $new[$field->get_id()] = array(
                'value' => $value['value'],
                'data'  => array(),
                'files' => $files,
            );
        }

        return $new;
    }

    /**
     * Format product field data stored in order item meta in version 1.x of this extension
     *
     * @access public
     * @param array $formatted_meta
     * @param object $item_meta
     * @return array
     */
    public static function product_fields_in_order_item_from_1($formatted_meta, $item_meta)
    {
        // WC31: Update this method if it's going to be used with WC 3.0+

        global $wpdb;
        $order_item_id = null;
        $display_values = array();

        // Iterate over meta
        if (is_array($formatted_meta)) {
            foreach ($formatted_meta as $meta_key => $meta) {

                // Check if this is custom field
                if (preg_match('/^wccf_/i', $meta['key'])) {

                    // Try to match field by key
                    $field = WCCF_Field_Controller::get_field_by_key('product_field', $meta['key'], true);

                    // Unable to load field
                    if (!$field) {
                        continue;
                    }

                    // Get file download link
                    if ($field->field_type_is('file')) {

                        // Get order item id if not set yet
                        if ($order_item_id === null) {
                            $order_item_id = $wpdb->get_var($wpdb->prepare("
                                SELECT order_item_id
                                FROM {$wpdb->prefix}woocommerce_order_itemmeta
                                WHERE meta_id = %d
                            ", absint($meta_key)));
                        }

                        // Format file download link
                        $value = WCCF_Migration::file_download_link_html_from_1($order_item_id, array('key' => $meta['key'], 'value' => $meta['value']));
                    }
                    else {
                        $value = $meta['value'];
                    }

                    // Add display value
                    $display_values[$meta_key] = array(
                        'key'   => $meta['key'],
                        'label' => $field->get_label(),
                        'value' => $value,
                    );
                }
            }
        }

        return $display_values;
    }

    /**
     * Print product field values stored in version 1.x of this extension in admin zone
     *
     * @access public
     * @param int $order_item_id
     * @param array $order_item
     * @param object $product
     * @return array
     */
    public static function product_fields_in_admin_order_item_from_1($order_item_id, $order_item, $product)
    {
        global $wpdb;
        $display_values = array();

        // Iterate over meta
        if (!empty($order_item['item_meta']) && is_array($order_item['item_meta'])) {

            $unwrapped = RightPress_Helper::unwrap_post_meta($order_item['item_meta']);

            foreach ($unwrapped as $meta_key => $meta_value) {

                // Check if this is our custom field
                if (preg_match('/^wccf_/i', $meta_key)) {

                    // Try to match field by key
                    $field = WCCF_Field_Controller::get_field_by_key('product_field', $meta_key, true);

                    // Unable to load field
                    if (!$field) {
                        continue;
                    }

                    // Get file download link
                    if ($field->field_type_is('file')) {

                        // Get order item id if not set yet
                        if ($order_item_id === null) {
                            $order_item_id = $wpdb->get_var($wpdb->prepare("
                                SELECT order_item_id
                                FROM {$wpdb->prefix}woocommerce_order_itemmeta
                                WHERE meta_id = %d
                            ", absint($meta_key)));
                        }

                        // Format file download link
                        $value = WCCF_Migration::file_download_link_html_from_1($order_item_id, array('key' => $meta_key, 'value' => $meta_value));
                    }
                    else {
                        $value = $meta_value;
                    }

                    // Add display value
                    $display_values[] = array(
                        'label' => $field->get_label(),
                        'value' => $value,
                    );
                }
            }
        }

        return $display_values;
    }

    /**
     * Render file download link for file field stored in version 1.x
     *
     * @access public
     * @param int $post_id
     * @param array $field
     * @param string $prepend
     * @param string $append
     * @param string $storage_key
     * @return void
     */
    public static function file_download_link_html_from_1($post_id, $field, $prepend = '', $append = '', $storage_key = 'wccf')
    {
        global $wpdb;
        $access_granted = false;

        // Allow admin to download everything
        if (WCCF::is_admin()) {
            $access_granted = true;
        }
        // Allow public Product Properties
        else if ($storage_key === 'wccf_product_admin' && !empty($field['public'])) {
            $access_granted = true;
        }
        // Allow access to own public Order Fields
        else if ($storage_key === 'wccf_order' && !empty($field['public']) && RightPress_Helper::user_owns_wc_order(get_current_user_id(), $post_id)) {
            $access_granted = true;
        }
        // Allow access to own Checkout Fields
        else if ($storage_key === 'wccf_checkout' && RightPress_Helper::user_owns_wc_order(get_current_user_id(), $post_id)) {
            $access_granted = true;
        }
        // Allow access to own Product Fields
        else if ($storage_key === 'wccf') {

            // $post_id is order id (legacy)
            // WC31: Orders will no longer be posts
            if (get_post_type($post_id) === 'shop_order') {

                // Check if user owns order
                if (RightPress_Helper::user_owns_wc_order(get_current_user_id(), $post_id)) {
                    $access_granted = true;
                }
            }
            // $post_id is order item id
            else {

                // Get order id
                $order_id = RightPress_Helper::get_wc_order_id_from_order_item_id($post_id);

                // Check if user owns order
                if (!empty($order_id) && RightPress_Helper::user_owns_wc_order(get_current_user_id(), $order_id)) {
                    $access_granted = true;
                }
            }
        }

        // Access granted - format and return file download link
        if ($access_granted) {
            $url = home_url('/?wccf_version_1_file_download=' . $storage_key . '&post_id=' . $post_id . '&field_key=' . $field['key']);
            return $prepend . '<a href="' . $url . '">' . $field['value'] . '</a>' . $append;
        }

        return '';
    }

    /**
     * Download file uploaded via custom field in version 1.x
     *
     * @access public
     * @return void
     */
    public function file_download_from_1()
    {
        global $wpdb;

        // No data provided?
        if (empty($_GET['wccf_version_1_file_download']) || empty($_GET['post_id']) || empty($_GET['field_key'])) {
            exit;
        }

        $access_granted = false;

        // Check if current user can download uploaded files
        if (WCCF::is_admin()) {
            $access_granted = true;
        }

        // Checkout files can also be viewed by customers to whom that order belongs
        if (is_user_logged_in() && $_GET['wccf_version_1_file_download'] === 'wccf_checkout') {
            if (RightPress_Helper::user_owns_wc_order(get_current_user_id(), $_GET['post_id'])) {
                $access_granted = true;
            }
        }

        // Product files can also be viewed by customers to whom that order belongs
        if (is_user_logged_in() && $_GET['wccf_version_1_file_download'] === 'wccf') {

            // post_id is order id (legacy)
            // WC31: Orders will no longer be posts
            if (get_post_type($_GET['post_id']) === 'shop_order') {

                // Check if user owns order
                if (RightPress_Helper::user_owns_wc_order(get_current_user_id(), $_GET['post_id'])) {
                    $access_granted = true;
                }
            }
            // post_id is order item id
            else {

                // Get order id
                $order_id = RightPress_Helper::get_wc_order_id_from_order_item_id($_GET['post_id']);

                // Check if user owns order
                if (!empty($order_id) && RightPress_Helper::user_owns_wc_order(get_current_user_id(), $order_id)) {
                    $access_granted = true;
                }
            }
        }

        // Also temporary grant access for all product_admin and order files - will recheck that after we load fields
        if (in_array($_GET['wccf_version_1_file_download'], array('wccf_product_admin', 'wccf_order'))) {
            $access_granted = true;
        }

        // Access not granted?
        if (!$access_granted) {
            exit;
        }

        // Get fields for this post
        if ($_GET['wccf_version_1_file_download'] === 'wccf') {

            // Get stored fields from order item meta
            $fields = RightPress_WC_Meta::order_item_get_meta($_GET['post_id'], '_wccf', true);

            // Also look in post meta (versions earlier than 1.2 saved it in wrong location)
            if (empty($fields)) {
                $fields = RightPress_WC_Meta::order_get_meta($_GET['post_id'], '_wccf', true);
            }
        }
        else if ($_GET['wccf_version_1_file_download'] === 'wccf_product_admin') {
            $fields = RightPress_WC_Meta::product_get_meta($_GET['post_id'], '_' . $_GET['wccf_version_1_file_download'], true);
        }
        else if (in_array($_GET['wccf_version_1_file_download'], array('wccf_order', 'wccf_checkout'), true)) {
            $fields = RightPress_WC_Meta::order_get_meta($_GET['post_id'], '_' . $_GET['wccf_version_1_file_download'], true);
        }

        // No fields?
        if (empty($fields) || !is_array($fields)) {
            exit;
        }

        // Iterate over fields
        foreach ($fields as $field) {
            if ($field['key'] === $_GET['field_key'] && !empty($field['file'])) {

                // Check access for order and product admin fields
                if (in_array($_GET['wccf_version_1_file_download'], array('wccf_product_admin', 'wccf_order')) && empty($field['public']) && !WCCF::is_admin()) {
                    exit;
                }

                // Push file to browser
                if ($fp = fopen($field['file']['path'], 'rb')) {
                    header('Content-Type: ' . $field['file']['type']);
                    header('Content-Length: ' . filesize($field['file']['path']));
                    header('Content-disposition: attachment; filename="' . $field['file']['name'] . '"');
                    fpassthru($fp);
                }

                exit;
            }
        }

        exit;
    }

    /**
     * Get field value stored in version 1.x
     *
     * @access public
     * @param mixed $item
     * @param array $field
     * @return mixed
     */
    public static function get_stored_value_from_1($item, $field)
    {
        $meta_key_map = array(
            'product_prop'      => '_wccf_product_admin',
            'checkout_field'    => '_wccf_checkout',
            'order_field'       => '_wccf_order',
        );

        // Get field context
        $context = $field->get_context();

        // Field not supported by this method
        if (!isset($meta_key_map[$context])) {
            return null;
        }

        // Get old field values stored for this order
        $meta_access_key = $meta_key_map[$context];
        $values = $field->get_data($item, $meta_access_key, true);

        // Iterate over values
        if (!empty($values) && is_array($values)) {
            foreach ($values as $value_index => $value) {

                // Key or value not set
                if (!isset($value['key']) || !isset($value['value'])) {
                    continue;
                }

                // Get value
                $return_value = $value['value'];

                // Get key for comparison
                $key = preg_replace(array('/^wccf_/', '/^rp_wccf_/'), array('', ''), $value['key']);

                // Not our field
                if ($field->get_key() !== $key) {
                    continue;
                }

                // Fix files
                if ($field->field_type_is('file')) {

                    // File already moved to new location
                    if (!empty($value['new_file_access_key'])) {
                        $return_value = $value['new_file_access_key'];
                    }
                    // Move files to new location
                    else if (!empty($value['file']) && !empty($value['file']['name']) && !empty($value['file']['type']) && !empty($value['file']['path']) && file_exists($value['file']['path'])) {

                        // Store file
                        if ($storage_data = WCCF_Files::store_file(array('tmp_name' => $value['file']['path']))) {

                            // Generate access key
                            $access_key = WCCF_Files::get_unique_file_access_key();

                            // Set value to access key
                            $return_value = array($access_key);

                            // Update meta value
                            $values[$value_index]['new_file_access_key'] = $return_value;
                            $field->update_data($item, $meta_access_key, $values);

                            // Format file data
                            $file_data = array(
                                'subdirectory'  => $storage_data['subdirectory'],
                                'storage_key'   => $storage_data['storage_key'],
                                'name'          => $value['file']['name'],
                                'type'          => $value['file']['type'],
                                'field_id'      => $field->get_id(),
                            );

                            // Store file data
                            $field->update_data($item, WCCF_Field::get_file_data_access_key($access_key), $file_data);
                        }
                    }
                }

                // Return value
                return $return_value;
            }
        }

        // Nothing found
        return null;
    }








}

WCCF_Migration::get_instance();

}
