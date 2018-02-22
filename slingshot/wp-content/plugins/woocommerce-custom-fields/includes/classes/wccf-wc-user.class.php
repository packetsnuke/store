<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to user handling in WooCommerce
 *
 * @class WCCF_WC_User
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WC_User')) {

class WCCF_WC_User
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
        /**
         * FRONTEND ACCOUNT FIELDS
         */

        // Print user fields in frontend user registration page
        add_action('woocommerce_register_form', array($this, 'print_fields_frontend_register'));

        // Validate user fields submitted from registration page
        add_filter('woocommerce_process_registration_errors', array($this, 'process_registration_errors'), 10, 4);

        // Print user fields in frontend account edit page
        add_action('woocommerce_edit_account_form', array($this, 'print_fields_frontend_account_edit'));

        // Validate user fields submitted from account details page
        add_action('woocommerce_save_account_details_errors', array($this, 'validate_user_field_values_account_details'), 10, 2);

        /**
         * FRONTEND ADDRESS FIELDS
         */

        // Print user fields in frontend address edit pages
        add_action('woocommerce_before_edit_address_form_billing', array($this, 'print_fields_frontend_billing_address_edit'));
        add_action('woocommerce_after_edit_address_form_billing', array($this, 'print_fields_frontend_billing_address_edit'));
        add_action('woocommerce_before_edit_address_form_shipping', array($this, 'print_fields_frontend_shipping_address_edit'));
        add_action('woocommerce_after_edit_address_form_shipping', array($this, 'print_fields_frontend_shipping_address_edit'));

        // Validate user fields submitted from address edit page
        // NOTE: This is a hack since there's no hook for validation, change this bit later
        add_filter('woocommerce_edit_address_slugs', array($this, 'validate_user_field_values_address'));

        // Save address related user field values on address update
        add_action('woocommerce_customer_save_address', array($this, 'save_user_field_values_address_update'), 10, 2);
    }

    /**
     * Print user fields in frontend WooCommerce account edit page
     *
     * @access public
     * @return void
     */
    public function print_fields_frontend_account_edit()
    {
        // Get user fields to print
        $fields = WCCF_User_Field_Controller::get_filtered();

        // Print only fields set to display on user profile
        $fields = WCCF_Field_Controller::filter_by_property($fields, 'display_as', 'user_profile');

        // Print user fields
        WCCF_Field_Controller::print_fields($fields, get_current_user_id());
    }

    /**
     * Validate user fields submitted from account details page
     *
     * @access public
     * @return void
     */
    public function validate_user_field_values_account_details()
    {
        // Validate only those fields that were displayed
        $fields = WCCF_User_Field_Controller::get_filtered();
        $fields = WCCF_Field_Controller::filter_by_property($fields, 'display_as', 'user_profile');

        // Validate user fields
        WCCF_Field_Controller::validate_posted_field_values('user_field', array(
            'fields'    => $fields,
            'item_id'   => get_current_user_id(),
        ));
    }

    /**
     * Validate user fields submitted from registration page
     *
     * @access public
     * @param object $validation_error
     * @param string $username
     * @param string $password
     * @param string $email
     * @return object
     */
    public function process_registration_errors($validation_error, $username, $password, $email)
    {
        // Validate only those fields that were displayed
        $fields = WCCF_User_Field_Controller::get_filtered();
        $fields = WCCF_Field_Controller::filter_by_property($fields, 'display_as', 'user_profile');

        // Validate user fields
        WCCF_Field_Controller::validate_posted_field_values('user_field', array(
            'fields'    => $fields,
            'wp_errors' => $validation_error,
        ));

        return $validation_error;
    }

    /**
     * Print user fields in frontend billing address edit page
     *
     * @access public
     * @return void
     */
    public function print_fields_frontend_billing_address_edit()
    {
        // Get corresponding checkout hook
        $checkout_hook = strpos(current_filter(), 'before') !== false ? 'woocommerce_before_checkout_billing_form' : 'woocommerce_after_checkout_billing_form';

        // Get fields to print
        $fields = WCCF_User_Field_Controller::get_filtered(null, array(), $checkout_hook);
        $fields = WCCF_Field_Controller::filter_by_property($fields, 'display_as', 'billing_address');

        // Print user fields
        WCCF_Field_Controller::print_fields($fields, get_current_user_id());
    }

    /**
     * Print user fields in frontend shipping address edit page
     *
     * @access public
     * @return void
     */
    public function print_fields_frontend_shipping_address_edit()
    {
        // Get corresponding checkout hook
        $checkout_hook = strpos(current_filter(), 'before') !== false ? 'woocommerce_before_checkout_shipping_form' : 'woocommerce_after_checkout_shipping_form';

        // Get fields to print
        $fields = WCCF_User_Field_Controller::get_filtered(null, array(), $checkout_hook);
        $fields = WCCF_Field_Controller::filter_by_property($fields, 'display_as', 'shipping_address');

        // Print user fields
        WCCF_Field_Controller::print_fields($fields, get_current_user_id());
    }

    /**
     * Validate user fields submitted from address edit page
     * NOTE: This is a hack since there's no hook for validation, change this bit later
     *
     * @access public
     * @param array $slugs
     * @return void
     */
    public function validate_user_field_values_address($slugs = array())
    {
        // Check if validation is needed
        if (doing_filter('template_redirect')) {

            global $wp;

            // Prevent infinite loop
            remove_filter('woocommerce_edit_address_slugs', array($this, 'validate_user_field_values_address'));

            // Check which address is being saved
            $address_type = isset($wp->query_vars['edit-address']) ? wc_edit_address_i18n(sanitize_title($wp->query_vars['edit-address']), true) : 'billing';

            // Validate only those fields that were displayed
            $fields = WCCF_User_Field_Controller::get_filtered();
            $fields = WCCF_Field_Controller::filter_by_property($fields, 'display_as', $address_type . '_address');

            // Validate user fields
            WCCF_Field_Controller::validate_posted_field_values('user_field', array(
                'fields'    => $fields,
                'item_id'   => get_current_user_id(),
            ));
        }

        // Return filter value
        return $slugs;
    }

    /**
     * Save address related user field values on address update
     *
     * @access public
     * @param int $user_id
     * @param string $address_type
     * @return void
     */
    public function save_user_field_values_address_update($user_id, $address_type)
    {
        // Load customer if needed
        $item = RightPress_Helper::wc_version_gte('3.0') ? RightPress_Helper::wc_get_customer($user_id) : $user_id;

        // Store posted field values
        WCCF_Field_Controller::store_field_values($item, 'user_field', true, false, ($address_type . '_address'));

        // Save customer if needed
        if (is_object($item)) {
            $item->save();
        }
    }

    /**
     * Print user fields in frontend WooCommerce customer registration page
     *
     * @access public
     * @return void
     */
    public function print_fields_frontend_register()
    {
        // Workaround for issue #333
        if (did_action('register_form')) {
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





}

WCCF_WC_User::get_instance();

}
