<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to user handling in WordPress
 *
 * @class WCCF_WP_User
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WP_User')) {

class WCCF_WP_User
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
        // Print user fields in frontend user registration page
        add_action('register_form', array($this, 'print_fields_frontend_register'));

        // Print user fields in backend user add page
        add_action('user_new_form', array($this, 'print_fields_backend_add'), 1);

        // Print user fields in backend user edit page
        add_action('show_user_profile', array($this, 'print_fields_backend_update'), 1);
        add_action('edit_user_profile', array($this, 'print_fields_backend_update'), 1);

        // Validate user field values from frontend user registration
        add_filter('registration_errors', array($this, 'validate_user_field_values'), 10, 3);

        // Save user field values for newly created user account
        add_action('user_register', array($this, 'save_user_field_values_create'));

        // Save user field values for user account updates
        add_action('profile_update', array($this, 'save_user_field_values_update'));

        // Prevent infinite loop when WC 3.0+ updates user profile on checkout
        // Note: Using too hooks because WC 3.0 has a bug - doesn't have $object_type property set to 'customer' in WC_Customer class
        add_action('woocommerce_before_data_object_save', array($this, 'prevent_profile_update_infinite_loop'));
        add_action('woocommerce_before_customer_object_save', array($this, 'prevent_profile_update_infinite_loop'));
    }

    /**
     * Print user fields in frontend user registration page
     *
     * @access public
     * @return void
     */
    public function print_fields_frontend_register()
    {
        // Workaround for issue #333
        if (did_action('woocommerce_register_form')) {
            return;
        }

        // Get fields to display
        $fields = WCCF_User_Field_Controller::get_filtered();
        $fields = WCCF_Field_Controller::filter_by_property($fields, 'display_as', 'user_profile');

        // Check if we have any fields to display
        if ($fields) {

            // Display list of fields
            WCCF_Field_Controller::print_fields($fields);
        }
    }

    /**
     * Print user fields in backend user add page
     *
     * @access public
     * @return void
     */
    public function print_fields_backend_add()
    {
        WCCF_WP_User::print_fields_backend();
    }

    /**
     * Print user fields in backend user edit page
     *
     * @access public
     * @param object $profileuser
     * @return void
     */
    public function print_fields_backend_update($profileuser)
    {
        WCCF_WP_User::print_fields_backend($profileuser->ID);
    }

    /**
     * Print user fields in backend user edit page
     *
     * @access public
     * @param int $user_id
     * @return void
     */
    public function print_fields_backend($user_id = null)
    {
        // Get fields to display
        $fields = WCCF_User_Field_Controller::get_filtered();

        // Check if we have any fields to display
        if ($fields) {

            // Display title
            echo '<h2>' . apply_filters('wccf_context_label', WCCF_Settings::get('alias_user_field'), 'user_field', 'backend') . '</h2>';

            // Open container
            echo '<table class="form-table"><tbody>';

            // Display list of fields
            WCCF_Field_Controller::print_fields($fields, $user_id);

            // Close container
            echo '</tbody></table>';
        }
    }

    /**
     * Validate user field values from frontend user registration
     *
     * @access public
     * @param object $errors
     * @param string $sanitized_user_login
     * @param string $user_email
     * @return object
     */
    public function validate_user_field_values($errors, $sanitized_user_login, $user_email)
    {
        // Validate user fields
        WCCF_Field_Controller::validate_posted_field_values('user_field', array('wp_errors' => $errors, 'user_field_type' => 'user_profile'));

        // Return errors
        return $errors;
    }

    /**
     * Save user field values for newly created user account
     *
     * @access public
     * @param int $user_id
     * @return void
     */
    public function save_user_field_values_create($user_id)
    {
        define('WCCF_BACKEND_USER_REGISTER', true);

        // Load customer if needed
        $item = RightPress_Helper::wc_version_gte('3.0') ? RightPress_Helper::wc_get_customer($user_id) : $user_id;

        // Store field values
        WCCF_Field_Controller::store_field_values($item, 'user_field', true, false, 'user_profile');

        // Save customer if needed
        if (is_object($item)) {
            $item->save();
        }
    }

    /**
     * Save user field values during user account updates
     *
     * @access public
     * @param int $user_id
     * @return void
     */
    public function save_user_field_values_update($user_id)
    {
        // Load customer if needed
        $item = RightPress_Helper::wc_version_gte('3.0') ? RightPress_Helper::wc_get_customer($user_id) : $user_id;

        // Store field values
        WCCF_Field_Controller::store_field_values($item, 'user_field', true, false, 'user_profile');

        // Save customer if needed
        if (is_object($item)) {
            $item->save();
        }
    }

    /**
     * Prevent infinite loop when WC 3.0+ updates user profile on checkout
     *
     * @access public
     * @param object $object
     * @return void
     */
    public function prevent_profile_update_infinite_loop($object)
    {
        if (is_a($object, 'WC_Customer')) {
            remove_action('profile_update', array($this, 'save_user_field_values_update'));
        }
    }



}

WCCF_WP_User::get_instance();

}
