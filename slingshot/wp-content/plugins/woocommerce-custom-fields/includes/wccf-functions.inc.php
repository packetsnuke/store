<?php

/**
 * Functions to allow developer interaction
 */

if (!function_exists('is_wccf_printing_programmatically')) {

    /**
     * Check if fields or field values are being printed programmatically
     *
     * @param bool $set Optional parameter to set value, must not be used by developers
     * @return string
     */
    function is_wccf_printing_programmatically($set = null)
    {
        global $wccf_printing_programmatically;

        if ($set !== null) {
            $wccf_printing_programmatically = (bool) $set;
        }

        return (bool) $wccf_printing_programmatically;
    }
}

/**
 * PRINT FIELD OR MULTIPLE FIELDS
 *
 * Prints single field if 'key' value is string
 * Prints list of fields if 'key' value is array of strings
 * Attempts to print list of all applicable fields if 'key' is not set or it's value is empty array
 *
 * If 'item_id' is set in $params or it can be determined by the system, existing field value will be loaded into the field (e.g. current user details for user fields)
 *
 * Note: there's no method for product properties and order fields since they are not supposed to be displayed in the frontend
 */

if (!function_exists('wccf_print_product_field')) {

    /**
     * Print single product field programmatically
     *
     * @param array $params key, item_id
     * @return string
     */
    function wccf_print_product_field($params = array())
    {
        // Ensure that key is set and is string
        if (isset($params['key']) && is_string($params['key'])) {
            wccf_print_product_fields($params);
        }
    }
}

if (!function_exists('wccf_print_product_fields')) {

    /**
     * Print multiple product fields programmatically
     *
     * @param array $params keys, item_id
     * @return string
     */
    function wccf_print_product_fields($params = array())
    {
        wccf_print_fields(array_merge((array) $params, array('context' => 'product_field')));
    }
}

if (!function_exists('wccf_print_checkout_field')) {

    /**
     * Print single checkout field programmatically
     *
     * @param array $params key, item_id, checkout_position
     * @return string
     */
    function wccf_print_checkout_field($params = array())
    {
        // Ensure that key is set and is string
        if (isset($params['key']) && is_string($params['key'])) {
            wccf_print_checkout_fields($params);
        }
    }
}

if (!function_exists('wccf_print_checkout_fields')) {

    /**
     * Print multiple checkout fields programmatically
     *
     * @param array $params keys, item_id, checkout_position
     * @return string
     */
    function wccf_print_checkout_fields($params = array())
    {
        wccf_print_fields(array_merge((array) $params, array('context' => 'checkout_field')));
    }
}

if (!function_exists('wccf_print_user_field')) {

    /**
     * Print single user field programmatically
     *
     * @param array $params key, item_id
     * @return string
     */
    function wccf_print_user_field($params = array())
    {
        // Ensure that key is set and is string
        if (isset($params['key']) && is_string($params['key'])) {
            wccf_print_user_fields($params);
        }
    }
}

if (!function_exists('wccf_print_user_fields')) {

    /**
     * Print multiple user fields programmatically
     *
     * @param array $params keys, item_id
     * @return string
     */
    function wccf_print_user_fields($params = array())
    {
        wccf_print_fields(array_merge((array) $params, array('context' => 'user_field')));
    }
}

if (!function_exists('wccf_print_fields')) {

    /**
     * Print fields by context programmatically
     *
     * @param array $params context, key|keys, item_id, checkout_position
     * @return string
     */
    function wccf_print_fields($params = array())
    {
        // This is not supposed to be used in admin area
        if (is_admin() && !is_ajax()) {
            return;
        }

        // Let developers know that this is not a regular request
        is_wccf_printing_programmatically(true);

        // Context must be set
        if (empty($params['context'])) {
            return;
        }

        // Fix key property
        if (!isset($params['key'])) {
            $params['key'] = isset($params['keys']) ? $params['keys'] : null;
        }

        // Get some more properties
        $key                = (array) $params['key'];
        $item_id            = !empty($params['item_id']) ? $params['item_id'] : null;
        $child_id           = !empty($params['child_id']) ? $params['child_id'] : null;
        $checkout_position  = !empty($params['checkout_position']) ? $params['checkout_position'] : null;

        // Get fields to print
        $all_fields = WCCF_Field_Controller::get_all_by_context($params['context'], array(), $key);
        $fields = WCCF_Conditions::filter_fields($all_fields, array('item_id' => $item_id, 'child_id' => $child_id), $checkout_position);

        // Print fields
        WCCF_Field_Controller::print_fields($fields, $item_id);

        // Let developers know that this is not a regular request
        is_wccf_printing_programmatically(false);
    }
}

/**
 * PRINT VALUE OF ONE OR MULTIPLE FIELDS
 *
 * Prints single field value if 'key' is string
 * Prints list of field values if 'key' is array of strings
 * Attempts to print list of all applicable field values if 'key' is not set or it's value is empty array
 *
 * Prints values related to specific user, product, order or order item if 'item_id' is set
 * Attempts to determine 'item_id' if not set and prints nothing if this attempt fails
 *
 * If 'formatted' is set and is true, value will be printed with label and formatting
 * If 'formatted' is set and is false, value will be printed with no label and no formatting
 * If 'formatted' is not set, formatting will be applied only if 'key' is not string
 */

if (!function_exists('wccf_print_product_field_value')) {

    /**
     * Print single product field value programmatically
     *
     * @param array $params key, item_id, formatted
     * @return string
     */
    function wccf_print_product_field_value($params = array())
    {
        // Ensure that key is set and is string
        if (isset($params['key']) && is_string($params['key'])) {
            wccf_print_product_field_values($params);
        }
    }
}

if (!function_exists('wccf_print_product_field_values')) {

    /**
     * Print multiple product field values programmatically
     *
     * @param array $params keys, item_id, formatted
     * @return string
     */
    function wccf_print_product_field_values($params = array())
    {
        wccf_print_values(array_merge((array) $params, array('context' => 'product_field')));
    }
}

if (!function_exists('wccf_print_product_prop_value')) {

    /**
     * Print single product property value programmatically
     *
     * @param array $params key, item_id, formatted
     * @return string
     */
    function wccf_print_product_prop_value($params = array())
    {
        // Ensure that key is set and is string
        if (isset($params['key']) && is_string($params['key'])) {
            wccf_print_product_prop_values($params);
        }
    }
}

if (!function_exists('wccf_print_product_prop_values')) {

    /**
     * Print multiple product property values programmatically
     *
     * @param array $params keys, item_id, formatted
     * @return string
     */
    function wccf_print_product_prop_values($params = array())
    {
        wccf_print_values(array_merge((array) $params, array('context' => 'product_prop')));
    }
}

if (!function_exists('wccf_print_checkout_field_value')) {

    /**
     * Print single checkout field value programmatically
     *
     * @param array $params key, item_id, formatted
     * @return string
     */
    function wccf_print_checkout_field_value($params = array())
    {
        // Ensure that key is set and is string
        if (isset($params['key']) && is_string($params['key'])) {
            wccf_print_checkout_field_values($params);
        }
    }
}

if (!function_exists('wccf_print_checkout_field_values')) {

    /**
     * Print multiple checkout field values programmatically
     *
     * @param array $params keys, item_id, formatted
     * @return string
     */
    function wccf_print_checkout_field_values($params = array())
    {
        wccf_print_values(array_merge((array) $params, array('context' => 'checkout_field')));
    }
}

if (!function_exists('wccf_print_order_field_value')) {

    /**
     * Print single order field value programmatically
     *
     * @param array $params key, item_id, formatted
     * @return string
     */
    function wccf_print_order_field_value($params = array())
    {
        // Ensure that key is set and is string
        if (isset($params['key']) && is_string($params['key'])) {
            wccf_print_order_field_values($params);
        }
    }
}

if (!function_exists('wccf_print_order_field_values')) {

    /**
     * Print multiple order field values programmatically
     *
     * @param array $params keys, item_id, formatted
     * @return string
     */
    function wccf_print_order_field_values($params = array())
    {
        wccf_print_values(array_merge((array) $params, array('context' => 'order_field')));
    }
}

if (!function_exists('wccf_print_user_field_value')) {

    /**
     * Print single user field value programmatically
     *
     * @param array $params key, item_id, formatted
     * @return string
     */
    function wccf_print_user_field_value($params = array())
    {
        // Ensure that key is set and is string
        if (isset($params['key']) && is_string($params['key'])) {
            wccf_print_user_field_values($params);
        }
    }
}

if (!function_exists('wccf_print_user_field_values')) {

    /**
     * Print multiple user field values programmatically
     *
     * @param array $params keys, item_id, formatted
     * @return string
     */
    function wccf_print_user_field_values($params = array())
    {
        wccf_print_values(array_merge((array) $params, array('context' => 'user_field')));
    }
}

if (!function_exists('wccf_print_values')) {

    /**
     * Print field values by context programmatically
     *
     * @param array $params context, key|keys, item_id, formatted
     * @return string
     */
    function wccf_print_values($params = array())
    {
        $printed = false;

        // Context must be set
        if (empty($params['context'])) {
            return false;
        }

        // Fix key property
        if (!isset($params['key'])) {
            $params['key'] = isset($params['keys']) ? $params['keys'] : null;
        }

        // Make sure key value is array
        if (isset($params['key'])) {
            $params['key'] = (array) $params['key'];
        }

        // Check if single value was requested
        $single_value_requested = (isset($params['key']) && is_string($params['key']));

        // Check if value needs to be formatted
        $formatted = isset($params['formatted']) ? !empty($params['formatted']) : !$single_value_requested;
        $formatted = apply_filters('wccf_print_values_formatted', $formatted);

        // Get data
        $values = wccf_get_values(array_merge((array) $params, array('include_meta' => true)));

        // Allow developers to change it
        $values = apply_filters('wccf_print_values_values', $values);

        // No values
        if (empty($values)) {
            return;
        }

        // Open list container
        if ($formatted) {
            $classes = apply_filters('wccf_print_values_list_classes', 'wccf_print_values wccf_print_values_' . $params['context']);
            echo apply_filters('wccf_print_values_list_open', '<table class="' . $classes . '">');
        }

        // Iterate over values
        foreach ($values as $value) {

            // Allow developers to skip this value
            if (!apply_filters('wccf_print_values_print_value', true, $value)) {
                continue;
            }

            $field = $value['field'];
            $printed = true;

            // Formatting
            if ($formatted) {

                // Open list item container
                echo apply_filters('wccf_print_values_list_item_open', '<tr>');

                // Open label container
                echo apply_filters('wccf_print_values_label_open', '<th>');

                // Display label
                echo apply_filters('wccf_print_values_label', $field->get_label());

                // Close label container
                echo apply_filters('wccf_print_values_label_close', '</th>');

                // Open value container
                echo apply_filters('wccf_print_values_value_open', '<td>');
            }

            // Display value
            echo apply_filters('wccf_print_values_value', $value['display_value']);

            // Formatting
            if ($formatted) {

                // Close value container
                echo apply_filters('wccf_print_values_value_close', '</td>');

                // Close list item container
                echo apply_filters('wccf_print_values_list_item_close', '</tr>');
            }
        }

        // Close list container
        if ($formatted) {
            echo apply_filters('wccf_print_values_list_close', '</table>');
        }

        // Ensure that stylesheets are present
        if ($printed) {
            add_action('wp_print_footer_scripts', array('WCCF_Assets', 'enqueue_frontend_stylesheets'));
        }
    }
}

/**
 * GET VALUE OF ONE OR MULTIPLE FIELDS
 *
 * Returns single field value if 'key' is string
 * Returns array of field values if 'key' is array of strings
 * Attempts to return array of all applicable field values if 'key' is not set or it's value is empty array
 * If no value is found, returns false if single field value was requested and empty array if multiple values were requested
 *
 * Returns values related to specific user, product, order or order item if 'item_id' is set
 * Attempts to determine 'item_id' if not set and returns nothing if this attempt fails
 *
 * If 'include_meta' is set and is true, value will be returned as an array with all meta data, actual value will be accessible by keys 'value' and 'display_value'
 * If 'include_meta' is not set or is false, only display value will be returned
 */

if (!function_exists('wccf_get_product_field_value')) {

    /**
     * Get single product field value programmatically
     *
     * @param array $params key, item_id, include_meta
     * @return string
     */
    function wccf_get_product_field_value($params = array())
    {
        // Ensure that key is set and is string
        if (isset($params['key']) && is_string($params['key'])) {
            wccf_get_product_field_values($params);
        }
    }
}

if (!function_exists('wccf_get_product_field_values')) {

    /**
     * Get multiple product field values programmatically
     *
     * @param array $params keys, item_id, include_meta
     * @return string
     */
    function wccf_get_product_field_values($params = array())
    {
        return wccf_get_values(array_merge((array) $params, array('context' => 'product_field')));
    }
}

if (!function_exists('wccf_get_product_prop_value')) {

    /**
     * Get single product property value programmatically
     *
     * @param array $params key, item_id, include_meta
     * @return string
     */
    function wccf_get_product_prop_value($params = array())
    {
        // Ensure that key is set and is string
        if (isset($params['key']) && is_string($params['key'])) {
            wccf_get_product_prop_values($params);
        }
    }
}

if (!function_exists('wccf_get_product_prop_values')) {

    /**
     * Get multiple product property values programmatically
     *
     * @param array $params keys, item_id, include_meta
     * @return string
     */
    function wccf_get_product_prop_values($params = array())
    {
        return wccf_get_values(array_merge((array) $params, array('context' => 'product_prop')));
    }
}

if (!function_exists('wccf_get_checkout_field_value')) {

    /**
     * Get single checkout field value programmatically
     *
     * @param array $params key, item_id, include_meta
     * @return string
     */
    function wccf_get_checkout_field_value($params = array())
    {
        // Ensure that key is set and is string
        if (isset($params['key']) && is_string($params['key'])) {
            wccf_get_checkout_field_values($params);
        }
    }
}

if (!function_exists('wccf_get_checkout_field_values')) {

    /**
     * Get multiple checkout field values programmatically
     *
     * @param array $params keys, item_id, include_meta
     * @return string
     */
    function wccf_get_checkout_field_values($params = array())
    {
        return wccf_get_values(array_merge((array) $params, array('context' => 'checkout_field')));
    }
}

if (!function_exists('wccf_get_order_field_value')) {

    /**
     * Get single order field value programmatically
     *
     * @param array $params key, item_id, include_meta
     * @return string
     */
    function wccf_get_order_field_value($params = array())
    {
        // Ensure that key is set and is string
        if (isset($params['key']) && is_string($params['key'])) {
            wccf_get_order_field_values($params);
        }
    }
}

if (!function_exists('wccf_get_order_field_values')) {

    /**
     * Get multiple order field values programmatically
     *
     * @param array $params keys, item_id, include_meta
     * @return string
     */
    function wccf_get_order_field_values($params = array())
    {
        return wccf_get_values(array_merge((array) $params, array('context' => 'order_field')));
    }
}

if (!function_exists('wccf_get_user_field_value')) {

    /**
     * Get single user field value programmatically
     *
     * @param array $params key, item_id, include_meta
     * @return string
     */
    function wccf_get_user_field_value($params = array())
    {
        // Ensure that key is set and is string
        if (isset($params['key']) && is_string($params['key'])) {
            wccf_get_user_field_values($params);
        }
    }
}

if (!function_exists('wccf_get_user_field_values')) {

    /**
     * Get multiple user field values programmatically
     *
     * @param array $params keys, item_id, include_meta
     * @return string
     */
    function wccf_get_user_field_values($params = array())
    {
        return wccf_get_values(array_merge((array) $params, array('context' => 'user_field')));
    }
}

if (!function_exists('wccf_get_values')) {

    /**
     * Get field values by context programmatically
     *
     * @param array $params context, key|keys, item_id, include_meta
     * @return string
     */
    function wccf_get_values($params = array())
    {
        // Context must be set
        if (empty($params['context'])) {
            return false;
        }

        // Fix key property
        if (!isset($params['key'])) {
            $params['key'] = isset($params['keys']) ? $params['keys'] : null;
        }

        // Check if value needs to be returned directly or inside a values array
        $return_as_array = (!isset($params['key']) || !is_string($params['key']));

        // Get properties
        $context        = $params['context'];
        $key            = isset($params['key']) ? (array) $params['key'] : array();
        $item_id        = !empty($params['item_id']) ? $params['item_id'] : null;
        $include_meta   = !empty($params['include_meta']);

        // Attempt to figure out item id if it was not set
        // This does not support product fields since product field values are stored as order item meta and we don't normally have an order item context on pages
        if (!$item_id) {

            // Product id
            if ($context === 'product_prop') {
                $item_id = RightPress_Helper::get_wc_product_id();
            }
            // Order id
            else if (in_array($context, array('checkout_field', 'order_field'), true)) {
                $item_id = RightPress_Helper::get_wc_order_id();
            }
            // User id
            else if ($context === 'user_field' && is_user_logged_in()) {
                $item_id = get_current_user_id();
            }
        }

        // Get fields to get values for
        $fields = WCCF_Field_Controller::get_all_by_context($context, array(), $key);

        // Get values to display
        $values = WCCF_Field_Controller::get_field_values_for_frontend($fields, $item_id, $context);

        // No values found
        if (empty($values)) {
            return $return_as_array ? array() : false;
        }

        // Leave display value only if meta was not requested
        if (!$include_meta) {
            foreach ($values as $values_key => $value) {
                $values[$values_key] = $value['display_value'];
            }
        }

        // Return as values array
        if ($return_as_array) {
            return $values;
        }
        // Return single value
        else {
            return array_pop($values);
        }
    }
}

/**
 * LEGACY METHODS
 */

if (!function_exists('wccf_display_product_properties')) {

    /**
     * Display product properties in frontend
     *
     * @param int $product_id
     * @return string
     */
    function wccf_display_product_properties($product_id = null)
    {
        echo WCCF_WC_Product::print_product_properties_function($product_id);
    }
}
