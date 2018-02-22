<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Cart
 *
 * @class WCCF_WC_Cart
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WC_Cart')) {

class WCCF_WC_Cart
{
    private $custom_woocommerce_price_num_decimals;

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
        // Add to cart validation
        add_filter('woocommerce_add_to_cart_validation', array($this, 'validate_cart_item_product_field_values'), 10, 6);
        add_action('wp_loaded', array($this, 'maybe_redirect_to_product_page_after_failed_validation'), 20);

        // Add field values to cart item meta data
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_product_field_values'), 10, 3);

        // Adjust cart item pricing
        add_filter('woocommerce_add_cart_item', array($this, 'adjust_cart_item_pricing'), 12);

        // Cart loaded from session
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'get_cart_item_from_session'), 12, 3);

        // Print more decimals in cart item price if needed
        add_filter('woocommerce_cart_item_price', array($this, 'print_more_decimals_in_price'), 1, 3);

        // Get values for display in cart
        add_filter('woocommerce_get_item_data', array($this, 'get_values_for_display'), 12, 2);

        // Add configuration query vars to product link
        add_filter('woocommerce_cart_item_permalink', array($this, 'add_query_vars_to_cart_item_link'), 99, 3);

        // Disable quantity change in cart if at least one field that has value in cart item meta is quantity based
        add_filter('woocommerce_cart_item_quantity', array($this, 'maybe_disable_quantity_change'), 99, 2);

        // Copy product field values from order item meta to cart item meta on Order Again
        add_filter('woocommerce_order_again_cart_item_data', array($this, 'move_product_field_values_on_order_again'), 10, 3);
    }

    /**
     * Validate product field values on add to cart
     *
     * @access public
     * @param bool $is_valid
     * @param int $product_id
     * @param int $quantity
     * @param int $variation_id
     * @param array $variation
     * @param array $cart_item_data
     * @return bool
     */
    public function validate_cart_item_product_field_values($is_valid, $product_id, $quantity, $variation_id = null, $variation = null, $cart_item_data = null)
    {
        // Maybe skip product fields for this product based on various conditions
        if (WCCF_WC_Product::skip_product_fields($product_id, $variation_id)) {
            return $is_valid;
        }

        // Get fields for validation
        $fields = WCCF_Product_Field_Controller::get_filtered(null, array('item_id' => $product_id, 'child_id' => $variation_id));

        // Validate all fields
        // Note - we will need to pass $variation_id here somehow if we ever implement variation-level conditions
        $validation_result = WCCF_Field_Controller::validate_posted_field_values('product_field', array(
            'object_id' => $product_id,
            'fields'    => $fields,
            'quantity'  => $quantity,
            'values'    => (is_array($cart_item_data) && !empty($cart_item_data['wccf'])) ? $cart_item_data['wccf'] : null,
        ));

        if (!$validation_result) {
            define('WCCF_ADD_TO_CART_VALIDATION_FAILED', true);
            return false;
        }

        return $is_valid;
    }

    /**
     * Maybe redirect to product page if add to cart action was initiated via
     * URL and its validation failed and URL does not include product URL
     *
     * @access public
     * @return void
     */
    public function maybe_redirect_to_product_page_after_failed_validation()
    {
        // Our validation failed
        if (defined('WCCF_ADD_TO_CART_VALIDATION_FAILED') && WCCF_ADD_TO_CART_VALIDATION_FAILED) {

            // Add to cart was from link as opposed to regular add to cart when data is posted
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['add-to-cart'])) {

                // Get product
                $product = RightPress_Helper::wc_get_product($_GET['add-to-cart']);

                // Product was not loaded
                if (!$product) {
                    return;
                }

                // Get urls to compare
                $request_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $product_url = untrailingslashit(get_permalink(RightPress_WC_Legacy::product_get_id($product)));

                // Current request url does not contain product url
                if (strpos($request_url, str_replace(array('http://', 'https://'), array('', ''), $product_url)) === false) {

                    // Add query string to product url
                    if (strpos($product_url, '?') === false) {
                        $redirect_url = $product_url . $_SERVER['REQUEST_URI'];
                    }
                    else {
                        $redirect_url = $product_url . str_replace('?', '&', $_SERVER['REQUEST_URI']);
                    }

                    // Unset notices since we will repeat the same exact process and all notices will be added again
                    wc_clear_notices();

                    // Redirect to product page
                    wp_redirect($redirect_url);
                    exit;
                }
            }
        }
    }

    /**
     * Add product field values to cart item meta
     *
     * @access public
     * @param array $cart_item_data
     * @param int $product_id
     * @param int $variation_id
     * @return array
     */
    public function add_cart_item_product_field_values($cart_item_data, $product_id, $variation_id)
    {
        // Only do this for first add to cart event during one request (issue #384)
        if (defined('WCCF_ADD_TO_CART_PROCESSED')) {
            return $cart_item_data;
        }
        else {
            define('WCCF_ADD_TO_CART_PROCESSED', true);
        }

        // Allow developers to skip adding product field values to cart item
        if (!apply_filters('wccf_add_cart_item_product_field_values', true, $cart_item_data, $product_id, $variation_id)) {
            return $cart_item_data;
        }

        // Maybe skip product fields for this product based on various conditions
        if (WCCF_WC_Product::skip_product_fields($product_id, $variation_id)) {
            return $cart_item_data;
        }

        // Get fields to save values for
        $fields = WCCF_Product_Field_Controller::get_filtered(null, array('item_id' => $product_id, 'child_id' => $variation_id));

        // Get quantity
        $quantity = empty($_REQUEST['quantity']) ? 1 : wc_stock_amount($_REQUEST['quantity']);

        // Sanitize field values
        // Note - we will need to pass $variation_id here somehow if we ever implement variation-level conditions
        $values = WCCF_Field_Controller::sanitize_posted_field_values('product_field', array(
            'object_id'         => $product_id,
            'fields'            => $fields,
            'quantity'          => $quantity,
        ));

        // Check if we have any values to store
        if ($values) {

            // Store values
            $cart_item_data['wccf'] = $values;

            // Check if we have any quantity based fields added to values (may need to display more decimals so that the total adds up correctly)
            foreach ($values as $field_id => $value) {
                if (isset($fields[$field_id]) && $fields[$field_id]->is_quantity_based()) {
                    $cart_item_data['wccf_quantity_based_hash'] = RightPress_Helper::get_hash();
                    break;
                }
            }
        }

        return $cart_item_data;
    }

    /**
     * Adjust cart item pricing
     *
     * @access public
     * @param array $cart_item
     * @return
     */
    public function adjust_cart_item_pricing($cart_item)
    {
        // Flag cart item product so that other methods do not apply pricing rules
        $cart_item['data']->wccf_cart_item_product = true;

        // Allow developers to skip pricing adjustment
        if (apply_filters('wccf_skip_pricing_for_cart_item', false, $cart_item)) {
            return $cart_item;
        }

        // Get quantity
        $quantity = !empty($cart_item['quantity']) ? (float) $cart_item['quantity'] : 1;

        // Get variation id
        $variation_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : null;

        // Get product price
        $price = RightPress_WC_Legacy::product_get_price($cart_item['data']);

        // Get cart item data
        $cart_item_data = !empty($cart_item['wccf']) ? $cart_item['wccf'] : array();

        // Get adjusted price
        $adjusted_price = WCCF_Pricing::get_adjusted_price($price, $cart_item['product_id'], $variation_id, $cart_item_data, $quantity, false, false, $cart_item['data']);

        // Allow more spaces to fix totals for quantity based price adjusting fields
        // Issue https://github.com/RightPress/woocommerce-custom-fields/issues/403
        // $adjusted_price = WCCF_Pricing::fix_quantity_based_fields_product_price($adjusted_price, $quantity);

        // Check if price was actually adjusted
        if ($adjusted_price !== (float) $price) {

            // Set new price
            $cart_item['data']->set_price($adjusted_price);
        }

        // Return item
        return $cart_item;
    }

    /**
     * Cart loaded from session
     *
     * @access public
     * @param array $cart_item
     * @param array $values
     * @param string $key
     * @return array
     */
    public function get_cart_item_from_session($cart_item, $values, $key)
    {
        // Check if we have any product field data stored in cart
        if (!empty($values['wccf'])) {

            // Migrate data if needed
            if (WCCF_Migration::support_for('1')) {
                foreach ($values['wccf'] as $key => $value) {
                    if (isset($value['key']) && !isset($value['data'])) {
                        $values['wccf'] = WCCF_Migration::product_fields_in_cart_from_1_to_2($values['wccf']);
                        break;
                    }
                }
            }

            // Set field values
            $cart_item['wccf'] = $values['wccf'];
        }

        // Maybe adjust pricing
        $cart_item = $this->adjust_cart_item_pricing($cart_item);

        // Return item
        return $cart_item;
    }

    /**
     * Print more decimals in cart item price if needed
     *
     * @access public
     * @param string $price_html
     * @param array $cart_item
     * @param string $cart_item_key
     * @return string
     */
    public function print_more_decimals_in_price($price_html, $cart_item, $cart_item_key)
    {
        if (!empty($cart_item['wccf_quantity_based_hash'])) {

            // Get quantity
            $quantity = !empty($cart_item['quantity']) ? $cart_item['quantity'] : 1;

            // Check how many decimals we need to display
            $decimals = WCCF_Pricing::get_required_decimals_to_fix_price(RightPress_WC_Legacy::product_get_price($cart_item['data']), $quantity);

            // Check if we need to display more decimals
            if ($decimals > (int) wc_get_price_decimals()) {

                // Get product from cart
                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

                // Add temporary filter to display more decimals
                $this->custom_woocommerce_price_num_decimals = $decimals;
                add_filter('option_woocommerce_price_num_decimals', array($this, 'change_woocommerce_decimals'), 99);

                // Get product price
                $price_html = WC()->cart->get_product_price($_product);

                // Remove temporary filter
                $this->custom_woocommerce_price_num_decimals = null;
                remove_filter('option_woocommerce_price_num_decimals', array($this, 'change_woocommerce_decimals'), 99);
            }
        }

        return $price_html;
    }

    /**
     * Maybe change WooCommerce price decimals value
     *
     * @access public
     * @param int $decimals
     * @return int
     */
    public function change_woocommerce_decimals($decimals)
    {
        if (!RightPress_Helper::is_empty($this->custom_woocommerce_price_num_decimals)) {
            return $this->custom_woocommerce_price_num_decimals;
        }

        return $decimals;
    }

    /**
     * Get product field values to display in cart
     *
     * @access public
     * @param array $data
     * @param array $cart_item
     * @return array
     */
    public function get_values_for_display($data, $cart_item)
    {
        if (!empty($cart_item['wccf'])) {
            foreach ($cart_item['wccf'] as $field_id => $field_value) {

                // Get quantity index
                $quantity_index = WCCF_Field_Controller::get_quantity_index_from_field_id($field_id);

                // Get clean field id
                if ($quantity_index) {
                    $field_id = WCCF_Field_Controller::clean_field_id($field_id);
                }

                // Get field
                $field = WCCF_Field_Controller::get($field_id, 'wccf_product_field');

                // Make sure this field exists
                if (!$field) {
                    continue;
                }

                // Check if pricing can be displayed for this product
                $product_id = $cart_item['data']->is_type('variation') ? RightPress_WC_Legacy::product_variation_get_parent_id($cart_item['data']) : RightPress_WC_Legacy::product_get_id($cart_item['data']);
                $variation_id = $cart_item['data']->is_type('variation') ? RightPress_WC_Legacy::product_get_id($cart_item['data']) : null;
                $display_pricing = !WCCF_WC_Product::skip_pricing($product_id, $variation_id);

                // Get display value
                $display_value = $field->format_display_value($field_value, $display_pricing, true);

                // Get field label
                $field_label = $field->get_label();

                // Field label treatment for quantity based product fields
                if ($quantity_index) {
                    $field_label = WCCF_Field_Controller::get_quantity_adjusted_field_label($field_label, $quantity_index);
                }

                // Add to data array
                $data[] = array(
                    'name'      => $field_label,
                    'value'     => $display_value,
                    'display'   => $display_value,
                );
            }
        }

        return $data;
    }

    /**
     * Add configuration query vars to product link
     *
     * @access public
     * @param string $link
     * @param array $cart_item
     * @param string $cart_item_key
     * @return string
     */
    public function add_query_vars_to_cart_item_link($link, $cart_item, $cart_item_key)
    {
        // No link provided
        if (empty($link)) {
            return $link;
        }

        // Do not add query vars
        if (!apply_filters('wccf_preconfigured_cart_item_product_link', true, $link, $cart_item, $cart_item_key)) {
            return $link;
        }

        $new_link = $link;
        $quantity_based_field_found = false;

        // Add a flag to indicate that this link is cart item link to product
        $new_link = add_query_arg('wccf_qv_conf', 1, $new_link);

        // Cart item does not have custom fields
        if (empty($cart_item['wccf'])) {
            return $new_link;
        }

        // Iterate over field values
        foreach ($cart_item['wccf'] as $field_id => $field_value) {

            // Load field
            $field = WCCF_Field_Controller::cache(WCCF_Field_Controller::clean_field_id($field_id));

            // Unable to load field - if we can't get full configuration, don't add anything at all
            if (!$field) {
                return $link;
            }

            // Check if field is quantity based
            $quantity_based_field_found = $quantity_based_field_found ?: $field->is_quantity_based();

            // Get quantity index
            $quantity_index = WCCF_Field_Controller::get_quantity_index_from_field_id($field_id);

            // Get query var key
            $query_var_key = 'wccf_' . $field->get_context() . '_' . $field->get_id() . ($quantity_index ? ('_' . $quantity_index) : '');

            // Handle array values
            if (is_array($field_value['value'])) {

                // Fix query var key
                $query_var_key .= '[]';

                $is_first = true;

                foreach ($field_value['value'] as $single_value) {

                    // Encode current value
                    $current_value = rawurlencode($single_value);

                    // Handle first value
                    if ($is_first) {

                        // Add query var
                        $new_link = add_query_arg($query_var_key, $current_value, $new_link);

                        // Check if query var was added
                        if (strpos($new_link, $query_var_key) !== false) {
                            $is_first = false;
                        }
                    }
                    // Handle subsequent values - add_query_arg does not allow duplicate query vars
                    else {

                        if ($frag = strstr($new_link, '#')) {
                            $new_link = substr($new_link, 0, -strlen($frag));
                        }

                        $new_link .= '&' . $query_var_key . '=' . $current_value;

                        if ($frag) {
                            $new_link .= $frag;
                        }
                    }

                }
            }
            else {
                $new_link = add_query_arg($query_var_key, rawurlencode($field_value['value']), $new_link);
            }
        }

        // Add quantity
        if ($quantity_based_field_found && strpos($new_link, 'wccf_') !== false && !empty($cart_item['quantity']) && $cart_item['quantity'] > 1) {
            $new_link .= '&wccf_quantity=' . $cart_item['quantity'];
        }

        // Bail if our URL is longer than URL length limit of 2000
        if (strlen($new_link) > 2000) {
            return $link;
        }

        // Return new link
        return $new_link;
    }

    /**
     * Disable quantity change in cart if at least one field that has value in cart item meta is quantity based
     *
     * @access public
     * @param string $html
     * @param string $cart_item_key
     * @return string
     */
    public function maybe_disable_quantity_change($html, $cart_item_key)
    {
        // Get cart item
        $cart = WC()->cart->get_cart();
        $cart_item = $cart[$cart_item_key];

        // Check if cart item has any custom field values
        if (!empty($cart_item['wccf'])) {

            // Iterate over custom field values
            foreach ($cart_item['wccf'] as $field_id => $field_value) {

                // Load field
                $field = WCCF_Field_Controller::cache($field_id);

                // Field is quantity based
                if ($field && $field->is_quantity_based()) {

                    // Disable quantity change
                    $quantity = !empty($cart_item['quantity']) ? $cart_item['quantity'] : 1;
                    return sprintf('%d <input type="hidden" name="cart[%s][qty]" value="%d" />', $quantity, $cart_item_key, $quantity);
                }
            }
        }

        return $html;
    }

    /**
     * Copy product field values from order item meta to cart item meta on Order Again
     *
     * @access public
     * @param array $cart_item_data
     * @param object|array $order_item
     * @param object $order
     * @return array
     */
    public function move_product_field_values_on_order_again($cart_item_data, $order_item, $order)
    {
        // Get order item meta
        $order_item_meta = RightPress_Helper::wc_version_gte('3.0') ? $order_item['item_meta'] : $order_item['meta_data'];

        // Iterate over order item meta
        foreach ($order_item_meta as $key => $value) {

            // Check if this is our field id entry
            if (RightPress_Helper::string_begins_with_substring($key, '_wccf_pf_id_')) {

                // Attempt to load field
                if ($field = WCCF_Field_Controller::cache($value)) {

                    $current = array();

                    // Field is disabled
                    if (!$field->is_enabled()) {
                        continue;
                    }

                    // Get field key
                    $field_key = $field->get_key();

                    // Quantity index
                    $quantity_index = null;

                    // Attempt to get quantity index from meta entry
                    if ($key !== ('_wccf_pf_id_' . $field_key)) {

                        $quantity_index = str_replace(('_wccf_pf_id_' . $field_key . '_'), '', $key);
                        $extra_data_access_key = $field->get_extra_data_access_key($quantity_index);

                        // Result is not numeric
                        if (!is_numeric($quantity_index)) {
                            continue;
                        }

                        // Unable to validate quantity index
                        if (!isset($order_item_meta[$extra_data_access_key]['quantity_index']) || ((string) $order_item_meta[$extra_data_access_key]['quantity_index'] !== (string) $quantity_index)) {
                            continue;
                        }
                    }

                    // Get access keys
                    $value_access_key = $field->get_value_access_key($quantity_index);
                    $extra_data_access_key = $field->get_extra_data_access_key($quantity_index);

                    // Value or extra data entry is not present
                    if (!isset($order_item_meta[$value_access_key]) || !isset($order_item_meta[$extra_data_access_key])) {
                        continue;
                    }

                    // Reference value
                    $current_value = $order_item_meta[$value_access_key];

                    // Remove no longer existent options
                    if ($field->uses_options()) {

                        // Get options
                        $options = $field->get_options_list();

                        // Field can have multiple values
                        if ($field->accepts_multiple_values()) {

                            // Value is not array
                            if (!is_array($current_value)) {
                                continue;
                            }

                            // Value is not empty
                            if (!empty($current_value)) {

                                // Unset non existent options
                                foreach ($current_value as $index => $option_key) {
                                    if (!isset($options[(string) $option_key])) {
                                        unset($current_value[$index]);
                                    }
                                }

                                // No remaining values
                                if (empty($current_value)) {
                                    continue;
                                }
                            }
                        }
                        // Field always has one value
                        else {

                            // Option no longer exists
                            if (!isset($options[(string) $current_value])) {
                                continue;
                            }
                        }
                    }

                    // Remove no longer existent files and prepare file data array
                    if ($field->field_type_is('file')) {

                        $all_file_data = array();

                        // Value is not array
                        if (!is_array($current_value)) {
                            continue;
                        }

                        // Value is not empty
                        if (!empty($current_value)) {

                            // Unset non existent files
                            foreach ($current_value as $index => $access_key) {

                                $file_data_access_key = $field->get_file_data_access_key($access_key);

                                // File data not present in meta
                                if (!isset($order_item_meta[$file_data_access_key])) {
                                    unset($current_value[$index]);
                                    continue;
                                }

                                // Reference file data
                                $file_data = $order_item_meta[$file_data_access_key];

                                // File not available
                                if (!WCCF_Files::locate_file($file_data['subdirectory'], $file_data['storage_key'])) {
                                    unset($current_value[$index]);
                                    continue;
                                }

                                // Add to file data array
                                $all_file_data[$access_key] = $file_data;
                            }

                            // No remaining values
                            if (empty($current_value)) {
                                continue;
                            }
                        }
                    }

                    // Add value
                    $current['value'] = $current_value;

                    // Add extra data
                    $current['data'] = array();

                    // Add files
                    $current['files'] = $field->field_type_is('file') ? $all_file_data : array();

                    // Add to main array
                    $id = $field->get_id() . ($quantity_index ? ('_' . $quantity_index) : '');
                    $cart_item_data['wccf'][$id] = $current;
                }
            }
        }

        return $cart_item_data;
    }





}

WCCF_WC_Cart::get_instance();

}
