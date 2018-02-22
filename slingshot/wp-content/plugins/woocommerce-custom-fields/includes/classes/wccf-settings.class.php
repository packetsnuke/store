<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to Settings
 *
 * @class WCCF_Settings
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Settings')) {

class WCCF_Settings
{
    // Track settings structure versions
    protected static $version = '1';

    // Define settings structure
    protected static $structure = null;
    protected static $options = array();

    // Keep settings in memory
    protected $settings  = array();

    // Cache objects revision
    protected $objects_revision = null;

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
        // Load settings now
        $this->load_settings();

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // Add link to menu
        add_action('admin_menu', array($this, 'add_to_menu'), 12);

        // Display admin notices
        add_action('admin_notices', array($this, 'display_error_messages'));

        // Custom capability for settings
        add_filter('option_page_capability_wccf_settings_group_general', array($this, 'custom_settings_capability'));
        add_filter('option_page_capability_wccf_settings_group_product_fields', array($this, 'custom_settings_capability'));
        add_filter('option_page_capability_wccf_settings_group_product_properties', array($this, 'custom_settings_capability'));
        add_filter('option_page_capability_wccf_settings_group_checkout_fields', array($this, 'custom_settings_capability'));
        add_filter('option_page_capability_wccf_settings_group_order_fields', array($this, 'custom_settings_capability'));
        add_filter('option_page_capability_wccf_settings_group_user_fields', array($this, 'custom_settings_capability'));
    }

    /**
     * Get settings structure
     *
     * @access public
     * @return array
     */
    public static function get_structure()
    {
        if (self::$structure === null) {
            self::$structure = array(
                'general' => array(
                    'title' => __('General', 'rp_wccf'),
                    'children' => array(
                        'general_settings' => array(
                            'title' => __('General Settings', 'rp_wccf'),
                            'children' => array(
                                'date_format' => array(
                                    'title'     => __('Date format', 'rp_wccf'),
                                    'type'      => 'select',
                                    'default'   => '0',
                                    'required'  => true,
                                    'options'   => array(
                                        '0' => __('mm/dd/yy', 'rp_wccf'),
                                        '1' => __('mm/dd/yyyy', 'rp_wccf'),
                                        '2' => __('dd/mm/yy', 'rp_wccf'),
                                        '3' => __('dd/mm/yyyy', 'rp_wccf'),
                                        '4' => __('yy-mm-dd', 'rp_wccf'),
                                        '5' => __('yyyy-mm-dd', 'rp_wccf'),
                                        '6' => __('dd.mm.yyyy', 'rp_wccf'),
                                        '7' => __('dd-mm-yyyy', 'rp_wccf'),
                                    ),
                                ),
                            ),
                        ),
                        'general_pricing' => array(
                            'title' => __('Fees & Discounts', 'rp_wccf'),
                            'children' => array(
                                'fee_per_character_includes_spaces' => array(
                                    'title'     => __('Fee per character includes white spaces', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '1',
                                ),
                            ),
                        ),
                        'file_uploads' => array(
                            'title' => __('File Uploads', 'rp_wccf'),
                            'children' => array(
                                'multiple_files' => array(
                                    'title'     => __('Allow multiple files per field', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '0',
                                ),
                                'min_file_size' => array(
                                    'title'         => __('Min file size', 'rp_wccf'),
                                    'type'          => 'number',
                                    'default'       => '',
                                    'required'      => false,
                                    'placeholder'   => __('0', 'rp_wccf'),
                                    'hint'          => __('kilobytes', 'rp_wccf'),
                                ),
                                'max_file_size' => array(
                                    'title'         => __('Max file size', 'rp_wccf'),
                                    'type'          => 'number',
                                    'default'       => '',
                                    'required'      => false,
                                    'placeholder'   => __('unlimited', 'rp_wccf'),
                                    'hint'          => __('kilobytes', 'rp_wccf'),
                                ),
                                'max_combined_file_size_per_field' => array(
                                    'title'         => __('Max combined file size per field', 'rp_wccf'),
                                    'type'          => 'number',
                                    'default'       => '',
                                    'required'      => false,
                                    'placeholder'   => __('unlimited', 'rp_wccf'),
                                    'hint'          => __('kilobytes', 'rp_wccf'),
                                ),
                                'file_extension_whitelist' => array(
                                    'title'         => __('File extension whitelist', 'rp_wccf'),
                                    'type'          => 'multiselect',
                                    'default'       => array(),
                                    'required'      => false,
                                    'hint'          => __('Only allow specific file types to be uploaded.', 'rp_wccf'),
                                ),
                                'file_extension_blacklist' => array(
                                    'title'         => __('File extension blacklist', 'rp_wccf'),
                                    'type'          => 'multiselect',
                                    'default'       => array(),
                                    'required'      => false,
                                    'hint'          => __('Prevent specific file types from being uploaded.', 'rp_wccf'),
                                ),
                            ),
                        ),
                        'aliases' => array(
                            'title' => __('Aliases', 'rp_wccf'),
                            'children' => array(
                                'alias_product_field' => array(
                                    'title'     => __('Product Fields', 'rp_wccf'),
                                    'type'      => 'text',
                                    'default'   => __('Product Fields', 'rp_wccf'),
                                    'required'  => true,
                                ),
                                'alias_product_prop' => array(
                                    'title'     => __('Product Properties', 'rp_wccf'),
                                    'type'      => 'text',
                                    'default'   => __('Product Properties', 'rp_wccf'),
                                    'required'  => true,
                                ),
                                'alias_checkout_field' => array(
                                    'title'     => __('Checkout Fields', 'rp_wccf'),
                                    'type'      => 'text',
                                    'default'   => __('Checkout Fields', 'rp_wccf'),
                                    'required'  => true,
                                ),
                                'alias_order_field' => array(
                                    'title'     => __('Order Fields', 'rp_wccf'),
                                    'type'      => 'text',
                                    'default'   => __('Order Fields', 'rp_wccf'),
                                    'required'  => true,
                                ),
                                'alias_user_field' => array(
                                    'title'     => __('User Fields', 'rp_wccf'),
                                    'type'      => 'text',
                                    'default'   => __('User Fields', 'rp_wccf'),
                                    'required'  => true,
                                ),
                            ),
                        ),
                    ),
                ),
                'product_fields' => array(
                    'title' => __('Product Fields', 'rp_wccf'),
                    'children' => array(
                        'addon_pricing' => array(
                            'title' => __('Fees & Discounts', 'rp_wccf'),
                            'children' => array(
                                'product_field_prices_include_default' => array(
                                    'title'     => __('Adjust product prices to default values', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '1',
                                ),
                                'prices_product_page' => array(
                                    'title'     => __('Display pricing on product pages', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '1',
                                ),
                                'prices_cart_order_page' => array(
                                    'title'     => __('Display pricing on cart/order pages', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '1',
                                ),
                                'display_total_price' => array(
                                    'title'     => __('Display dynamically updated price', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '1',
                                ),
                            ),
                        ),
                        'product_field_file_uploads' => array(
                            'title' => __('File Uploads', 'rp_wccf'),
                            'children' => array(
                                'attach_product_field_files_new_order' => array(
                                    'title'     => __('Attach files to admin New Order emails', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '0',
                                ),
                            ),
                        ),
                        'add_to_cart' => array(
                            'title' => __('Add To Cart', 'rp_wccf'),
                            'children' => array(
                                'change_add_to_cart_text' => array(
                                    'title'     => __('Change list view button to View Product', 'rp_wccf'),
                                    'type'      => 'select',
                                    'default'   => '0',
                                    'required'  => true,
                                    'options'   => array(
                                        '0' => __('When any product fields are set', 'rp_wccf'),
                                        '1' => __('Only when required fields are set', 'rp_wccf'),
                                    ),
                                ),
                            ),
                        ),
                        'product_field_editing' => array(
                            'title' => __('Editing', 'rp_wccf'),
                            'children' => array(
                                'allow_product_field_editing' => array(
                                    'title'     => __('Allow admin to edit user submitted values', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '0',
                                ),
                            ),
                        ),
                    ),
                ),
                'product_properties' => array(
                    'title' => __('Product Properties', 'rp_wccf'),
                    'children' => array(
                        'frontend_display' => array(
                            'title' => __('Frontend Display', 'rp_wccf'),
                            'children' => array(
                                'display_empty_product_prop_values' => array(
                                    'title'     => __('Display empty values', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '1',
                                    'hint'      => __('Whether or not to display empty stored product property values. Value is displayed as <strong>n/a</strong>.', 'rp_wccf'),
                                ),
                                'display_default_product_prop_values' => array(
                                    'title'     => __('Display default values automatically', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '0',
                                    'hint'      => __('Default product property values will be visible to customers before shop manager submits new values via product edit page.', 'rp_wccf'),
                                ),
                            ),
                        ),
                        'property_pricing' => array(
                            'title' => __('Fees & Discounts', 'rp_wccf'),
                            'children' => array(
                                'product_property_prices_include_default' => array(
                                    'title'     => __('Adjust product prices to default values', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '0',
                                    'hint'      => __('Product prices will be adjusted throughout the shop to account for default applicable product property values before shop manager submits new values via product edit page.', 'rp_wccf'),
                                ),
                            ),
                        ),
                    ),
                ),
                'checkout_fields' => array(
                    'title' => __('Checkout Fields', 'rp_wccf'),
                    'children' => array(
                        'checkout_field_pricing' => array(
                            'title' => __('Fees & Discounts', 'rp_wccf'),
                            'children' => array(
                                'checkout_field_price_display' => array(
                                    'title'     => __('Display pricing next to fields', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '1',
                                ),
                                'display_as_single_fee' => array(
                                    'title'     => __('Display as single fee', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '0',
                                    'hint'      => __('Only use this option if all fees are not taxable or use the same tax class.', 'rp_wccf'),
                                ),
                                'single_fee_label' => array(
                                    'title'     => __('Single fee label', 'rp_wccf'),
                                    'type'      => 'text',
                                    'default'   => __('Extra Options', 'rp_wccf'),
                                    'required'  => true,
                                ),
                            ),
                        ),
                        'checkout_field_file_uploads' => array(
                            'title' => __('File Uploads', 'rp_wccf'),
                            'children' => array(
                                'attach_checkout_field_files_new_order' => array(
                                    'title'     => __('Attach files to admin New Order emails', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '0',
                                ),
                            ),
                        ),
                        'checkout_field_editing' => array(
                            'title' => __('Editing', 'rp_wccf'),
                            'children' => array(
                                'allow_checkout_field_editing' => array(
                                    'title'     => __('Allow admin to edit user submitted values', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '0',
                                ),
                            ),
                        ),
                    ),
                ),
                'order_fields' => array(
                    'title' => __('Order Fields', 'rp_wccf'),
                    'children' => array(
                        'frontend_display' => array(
                            'title' => __('Frontend Display', 'rp_wccf'),
                            'children' => array(
                                'display_empty_order_field_values' => array(
                                    'title'     => __('Display empty values', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '1',
                                    'hint'      => __('Whether or not to display empty stored order field values. Value is displayed as <strong>n/a</strong>.', 'rp_wccf'),
                                ),
                                'display_default_order_field_values' => array(
                                    'title'     => __('Display default values automatically', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '0',
                                    'hint'      => __('Default order field values will be visible to customers before shop manager submits new values via order edit page.', 'rp_wccf'),
                                ),
                            ),
                        ),
                    ),
                ),
                'user_fields' => array(
                    'title' => __('User Fields', 'rp_wccf'),
                    'children' => array(
                        'user_field_editing' => array(
                            'title' => __('Editing', 'rp_wccf'),
                            'children' => array(
                                'allow_user_field_editing' => array(
                                    'title'     => __('Allow admin to edit user submitted values', 'rp_wccf'),
                                    'type'      => 'checkbox',
                                    'default'   => '0',
                                    'hint'      => 'This affects values stored on orders, user profile field values can be edited anyway',
                                ),
                            ),
                        ),
                    ),
                ),
            );
        }

        return self::$structure;
    }

    /**
     * Load settings
     *
     * @access public
     * @return void
     */
    public function load_settings()
    {
        // Load any stored settings
        $stored = get_option('wccf_settings', array());

        // Attempt to migrate settings from older version if none were found
        if (empty($stored) || empty($stored[self::$version])) {
            $stored = $this->migrate_settings($stored);
        }

        // Get settings of current version
        $stored = (is_array($stored) && isset($stored[self::$version])) ? $stored[self::$version] : array();

        // Iterate over field structure and either assign stored value or revert to default value
        foreach (self::get_structure() as $tab_key => $tab) {
            foreach ($tab['children'] as $section_key => $section) {
                foreach ($section['children'] as $field_key => $field) {

                    // Set value
                    if (isset($stored[$field_key])) {
                        $this->settings[$field_key] = $stored[$field_key];
                    }
                    else {
                        $this->settings[$field_key] = isset($field['default']) ? $field['default'] : null;
                    }

                    // Set options
                    if (!empty($field['options'])) {
                        self::$options[$field_key] = $field['options'];
                    }
                }
            }
        }
    }

    /**
     * Get options for select fields
     *
     * @access public
     * @param string $key
     * @return array
     */
    public static function get_options($key)
    {
        return isset(self::$options[$key]) ? self::$options[$key] : array();
    }

    /**
     * Register settings with WordPress
     *
     * @access public
     * @return void
     */
    public function register_settings()
    {
        // Check if current user can manage plugin settings
        if (!WCCF::is_authorized('manage_fields')) {
            return;
        }

        // Iterate over tabs
        foreach (self::get_structure() as $tab_key => $tab) {

            // Register tab
            register_setting(
                'wccf_settings_group_' . $tab_key,
                'wccf_settings',
                array($this, 'validate_settings')
            );

            // Iterate over sections
            foreach ($tab['children'] as $section_key => $section) {

                $settings_page_id = 'wccf-admin-' . str_replace('_', '-', $tab_key);

                // Register section
                add_settings_section(
                    $section_key,
                    $section['title'],
                    array($this, 'print_section_info'),
                    $settings_page_id
                );

                // Iterate over fields
                foreach ($section['children'] as $field_key => $field) {

                    // Register field
                    add_settings_field(
                        'wccf_' . $field_key,
                        $field['title'],
                        array($this, 'print_field_' . $field['type']),
                        $settings_page_id,
                        $section_key,
                        array(
                            'field_key'     => $field_key,
                            'field'         => $field,
                            'title'         => !empty($field['hint']) ? $field['hint'] : null,
                        )
                    );
                }
            }
        }
    }

    /**
     * Get value of a single setting
     *
     * @access public
     * @param string $key
     * @param bool $actual_value
     * @param bool $is_frontend
     * @return mixed
     */
    public static function get($key, $actual_value = false, $is_frontend = false)
    {
        $instance = self::get_instance();

        // Return all settings in array
        if ($key === '_all') {
            return $instance->settings;
        }

        // Get settings value
        $value = isset($instance->settings[$key]) ? $instance->settings[$key] : null;

        // Pick actual date format
        if ($actual_value && $key === 'date_format') {

            // Define date formats
            $formats = array(
                '0' => $is_frontend ? 'm/d/y'    : 'n/j/y',
                '1' => $is_frontend ? 'm/d/yy'   : 'n/j/Y',
                '2' => $is_frontend ? 'd/m/y'    : 'j/n/y',
                '3' => $is_frontend ? 'd/m/yy'   : 'j/n/Y',
                '4' => $is_frontend ? 'y-mm-dd'  : 'y-m-d',
                '5' => $is_frontend ? 'yy-mm-dd' : 'Y-m-d',
                '6' => $is_frontend ? 'dd.mm.yy' : 'd.m.Y',
                '7' => $is_frontend ? 'dd-mm-yy' : 'd-m-Y',
            );

            // Select date format
            $value = isset($formats[$value]) ? $formats[$value] : null;
        }

        // Allow developers to override value and return it
        return apply_filters('wccf_settings_value', $value, $key, $actual_value, $is_frontend);
    }

    /*
     * Update value of a single setting
     *
     * @access public
     * @return bool
     */
    public static function update($key, $value)
    {
        // User not allowed to update settings
        if (!WCCF::is_authorized('manage_fields')) {
            return false;
        }

        $instance = self::get_instance();

        // Setting must be defined in self::$structure
        if (!isset($instance->settings[$key])) {
            return;
        }

        // Assign new value
        $instance->settings[$key] = $value;

        // Store settings
        return update_option('wccf_settings', array(self::$version => $instance->settings));
    }

    /**
     * Add Settings link to menu
     *
     * @access public
     * @return void
     */
    public function add_to_menu()
    {
        add_submenu_page(
            'edit.php?post_type=wccf_product_field',
            __('Settings', 'rp_wccf'),
            __('Settings', 'rp_wccf'),
            WCCF::get_admin_capability('manage_posts'),
            'wccf_settings',
            array('WCCF_Settings', 'print_settings_page')
        );
    }

    /**
     * Preserve error messages so that they survive potential redirect
     *
     * @access public
     * @return void
     */
    public static function preserve_error_messages()
    {
        // Get settings errors
        $settings_errors = get_settings_errors('wccf');

        // Check if any settings errors are set
        if (!empty($settings_errors)) {

            // Save errors as transient
            set_transient('wccf_settings_errors', $settings_errors, 5);
        }
    }

    /**
     * Display error messages if any
     *
     * @access public
     * @return void
     */
    public function display_error_messages()
    {
        // Check if this is our admin page
        if (!WCCF::is_settings_page()) {
            return;
        }

        // Get settings errors
        $settings_errors = get_settings_errors('wccf');

        // Check if any settings errors are set
        if (empty($settings_errors)) {

            // Attempt to load settings errors from transient
            if ($settings_errors = get_transient('wccf_settings_errors')) {

                // Register settings errors again
                foreach ($settings_errors as $settings_error) {
                    add_settings_error(
                        $settings_error['setting'],
                        $settings_error['code'],
                        $settings_error['message'],
                        $settings_error['type']
                    );
                }

                // Delete transient
                delete_transient('wccf_settings_errors');
            }
        }

        // Display errors
        settings_errors('wccf');
    }

    /**
     * Print settings page
     *
     * @access public
     * @return void
     */
    public static function print_settings_page()
    {
        // Get current tab
        $current_tab = WCCF_Settings::get_tab();

        // Open form container
        echo '<div class="wrap woocommerce"><form method="post" action="options.php" enctype="multipart/form-data">';

        // Print header
        include WCCF_PLUGIN_PATH . 'includes/views/settings/header.php';

        // Print settings page content
        include WCCF_PLUGIN_PATH . 'includes/views/settings/fields.php';

        // Print footer
        include WCCF_PLUGIN_PATH . 'includes/views/settings/footer.php';

        // Close form container
        echo '</form></div>';
    }

    /**
     * Get current settings tab
     *
     * @access public
     * @return string
     */
    public static function get_tab()
    {
        $structure = WCCF_Settings::get_structure();

        // Check if we know tab identifier
        if (isset($_GET['tab']) && isset($structure[$_GET['tab']])) {
            return $_GET['tab'];
        }
        else {
            $array_keys = array_keys($structure);
            return array_shift($array_keys);
        }
    }

    /**
     * Print section info
     *
     * @access public
     * @param array $section
     * @return void
     */
    public function print_section_info($section)
    {
    }

    /**
     * Render text field
     *
     * @access public
     * @param array $args
     * @param string $field_type
     * @return void
     */
    public function print_field_text($args = array(), $field_type = null)
    {
        // Get prefixed key
        $prefixed_key = 'wccf_' . $args['field_key'];

        // Configure field
        $config = array(
            'id'            => $prefixed_key,
            'name'          => 'wccf_settings[' . $prefixed_key . ']',
            'value'         => WCCF_Settings::get($args['field_key']),
            'class'         => 'wccf_setting wccf_field_long',
            'title'         => !empty($args['title']) ? $args['title'] : '',
            'placeholder'   => (isset($args['field']['placeholder']) && !RightPress_Helper::is_empty($args['field']['placeholder'])) ? $args['field']['placeholder'] : '',
        );

        // Check if field is required
        if (!empty($args['field']['required'])) {
            $config['required'] = 'required';
        }

        // Get field type
        $field_type = $field_type ?: 'text';

        // Print field
        WCCF_FB::$field_type($config);
    }

    /**
     * Render number field
     *
     * @access public
     * @param array $args
     * @return void
     */
    public function print_field_number($args = array())
    {
        self::print_field_text($args, 'number');
    }

    /**
     * Render checkbox field
     *
     * @access public
     * @param array $args
     * @return void
     */
    public function print_field_checkbox($args = array())
    {
        // Get prefixed key
        $prefixed_key = 'wccf_' . $args['field_key'];

        // Print field
        WCCF_FB::checkbox(array(
            'id'        => $prefixed_key,
            'name'      => 'wccf_settings[' . $prefixed_key . ']',
            'checked'   => (bool) WCCF_Settings::get($args['field_key']),
            'class'     => 'wccf_setting',
            'title'     => !empty($args['title']) ? $args['title'] : '',
        ));
    }

    /**
     * Render select field
     *
     * @access public
     * @param array $args
     * @param string $field_type
     * @return void
     */
    public function print_field_select($args = array(), $field_type = null)
    {
        // Get prefixed key
        $prefixed_key = 'wccf_' . $args['field_key'];

        // Get field type
        $field_type = $field_type ?: 'select';

        // Get value
        $value = WCCF_Settings::get($args['field_key']);

        // Get options
        $options = WCCF_Settings::get_options($args['field_key']);

        // Fix multiselect options
        // Note: this is designed to work with user-entered "tags" (file extensions) with no predefined options list
        if ($field_type === 'multiselect' && empty($options)) {
            $options = $value;
        }

        // Print field
        WCCF_FB::$field_type(array(
            'id'        => $prefixed_key,
            'name'      => 'wccf_settings[' . $prefixed_key . ']' . ($field_type === 'multiselect' ? '[]' : ''),
            'options'   => $options,
            'value'     => $value,
            'class'     => 'wccf_setting wccf_field_select wccf_field_long',
            'title'     => !empty($args['title']) ? $args['title'] : '',
        ));
    }

    /**
     * Render multiselect field
     *
     * @access public
     * @param array $args
     * @return void
     */
    public function print_field_multiselect($args = array())
    {
        self::print_field_select($args, 'multiselect');
    }

    /**
     * Validate settings
     *
     * @access public
     * @param array $input
     * @return void
     */
    public function validate_settings($input)
    {
        $instance = WCCF_Settings::get_instance();
        $structure = WCCF_Settings::get_structure();

        // Track if this is a first or a second call to this function
        // When settings are saved for the first time, WordPress calls
        // it twice and $input is different on a second call
        if (!defined('wccf_settings_validated')) {
            define('wccf_settings_validated', true);
            $settings_already_validated = false;
            $field_key_prefix = 'wccf_';
        }
        else {
            $settings_already_validated = true;
            $field_key_prefix = '';
            $input = $input[self::$version];
        }

        // Set output to current settings first
        $output = $instance->settings;
        $field_array = array();
        $errors = array();

        // Attempt to validate settings
        try {

            // Check if request came from a correct page
            if (empty($_POST['current_tab']) || !isset($structure[$_POST['current_tab']])) {
                throw new Exception(__('Unable to validate settings.', 'rp_wccf'));
            }

            // Iterate over fields and validate new values
            foreach ($structure[$_POST['current_tab']]['children'] as $section_key => $section) {
                foreach ($section['children'] as $field_key => $field) {

                    $full_key = $field_key_prefix . $field_key;

                    switch($field['type']) {

                        // Checkbox
                        case 'checkbox':
                            $output[$field_key] = empty($input[$full_key]) ? '0' : '1';
                            break;

                        // Select
                        case 'select':
                            if (isset($input[$full_key]) && isset($field['options'][$input[$full_key]])) {
                                $output[$field_key] = $input[$full_key];
                            }
                            break;

                        // Multiselect
                        // Note: this is designed to work with user-entered "tags" (file extensions) with no predefined options list
                        case 'multiselect':
                            $output[$field_key] = array();

                            if (!empty($input[$full_key]) && is_array($input[$full_key])) {
                                foreach ($input[$full_key] as $multiselect_value) {
                                    $sanitized = sanitize_key($multiselect_value);
                                    $output[$field_key][$sanitized] = $sanitized;
                                }
                            }

                            $output[$field_key] = array_unique($output[$field_key]);

                            break;

                        // Number
                        // Note: currently float values will be trimmed to int values
                        case 'number':
                            if (isset($input[$full_key]) && is_numeric($input[$full_key])) {
                                $output[$field_key] = (int) esc_attr(trim($input[$full_key]));
                            }
                            else {
                                $output[$field_key] = '';
                            }
                            break;

                        // Text input
                        default:
                            if (isset($input[$full_key])) {
                                $output[$field_key] = esc_attr(trim($input[$full_key]));
                            }
                            break;
                    }
                }
            }

            // Add notice
            if (!$settings_already_validated) {
                add_settings_error(
                    'wccf',
                    'wccf_settings_updated',
                    __('Settings updated.', 'rp_wccf'),
                    'updated'
                );
            }

        } catch (Exception $e) {

            // Add error
            add_settings_error(
                'wccf',
                'wccf_settings_validation_failed',
                $e->getMessage()
            );
        }

        // Store new settings
        return array(self::$version => $output);
    }

    /**
     * Migrate settings
     *
     * @access protected
     * @param array $stored
     * @return array
     */
    protected function migrate_settings($stored)
    {
        return $stored;
    }

    /**
     * Get objects revision identifier
     *
     * @access public
     * @return string
     */
    public static function get_objects_revision()
    {
        $instance = self::get_instance();

        // Check if we have revision in memory
        if ($instance->objects_revision === null) {

            // Get revision from database
            $instance->objects_revision = get_option('wccf_objects_revision');

            // Reset revision if not found in database
            if (!$instance->objects_revision) {
                self::reset_objects_revision();
            }
        }

        // Return revision from memory
        return $instance->objects_revision;
    }

    /**
     * Reset objects revision identifier
     *
     * @access public
     * @return string
     */
    public static function reset_objects_revision()
    {
        $instance = self::get_instance();

        // Generate revision identifier and cache in memory
        $instance->objects_revision = RightPress_Helper::get_hash();

        // Update revision in database
        update_option('wccf_objects_revision', $instance->objects_revision);

        // Return new revision identifier
        return $instance->objects_revision;
    }

    /**
     * Custom capability for settings
     *
     * @access public
     * @param string $capability
     * @return string
     */
    public function custom_settings_capability($capability)
    {
        return WCCF::get_admin_capability();
    }



}

WCCF_Settings::get_instance();

}
