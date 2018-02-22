<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Order Item
 *
 * @class WCCF_WC_Order_Item
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WC_Order_Item')) {

class WCCF_WC_Order_Item
{
    protected static $hidden_order_item_meta_key_cache = array();

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
        // Hide order item meta
        add_filter('woocommerce_hidden_order_itemmeta', array($this, 'hidden_order_item_meta'));

        // Send product field files as attachments
        add_filter('woocommerce_email_attachments', array($this, 'attach_product_field_files'), 99, 3);

        // Format order item meta for frontend display
        if (RightPress_Helper::wc_version_gte('3.0.4')) {
            add_filter('woocommerce_order_item_get_formatted_meta_data', array($this, 'get_formatted_meta_data'), 10, 2);
        }
        else if (RightPress_Helper::wc_version_gte('3.0')) {
            add_filter('woocommerce_display_item_meta', array($this, 'display_item_meta'), 10, 3);
        }
        else {
            add_filter('woocommerce_order_items_meta_get_formatted', array($this, 'format_order_item_meta'), 10, 2);
        }

        // Display order item meta in backend
        add_filter('woocommerce_after_order_itemmeta', array($this, 'display_backend_order_item_meta'), 10, 3);
    }

    /**
     * Hide order item meta (raw values and meta for internal use)
     *
     * @access public
     * @param array $hidden_keys
     * @return array
     */
    public function hidden_order_item_meta($hidden_keys)
    {
        // Support for pre WC 3.0
        if (!RightPress_Helper::wc_version_gte('3.0')) {
            return $this->hidden_order_item_meta_legacy($hidden_keys);
        }

        // Check if order id can be determined
        if ($order_id = RightPress_Helper::get_wc_order_id()) {

            // Check if we have hidden keys already for this order
            if (!isset(self::$hidden_order_item_meta_key_cache[$order_id])) {

                self::$hidden_order_item_meta_key_cache[$order_id] = array();

                // Load order object
                $order = RightPress_Helper::wc_get_order($order_id);

                // Iterate over order items
                foreach ($order->get_items() as $order_item_key => $order_item) {

                    // Iterate over order item meta
                    foreach ($order_item->get_meta_data() as $meta) {

                        // Check if this is our internal meta key; also match data stored with 1.x versions of this extension
                        if (preg_match('/^_wccf_/i', $meta->key) || preg_match('/^wccf_/i', $meta->key)) {

                            // Check if it already exists in hidden keys array
                            if (!in_array($meta->key, self::$hidden_order_item_meta_key_cache[$order_id])) {

                                // Add key to hidden keys array
                                self::$hidden_order_item_meta_key_cache[$order_id][] = $meta->key;
                            }
                        }
                    }
                }
            }

            // Add our hidden keys to the main hidden keys array
            $hidden_keys = array_merge($hidden_keys, self::$hidden_order_item_meta_key_cache[$order_id]);
        }

        return $hidden_keys;
    }

    /**
     * Hide order item meta (raw values and meta for internal use)
     * Pre WC 3.0 compatibility
     *
     * @access public
     * @param array $hidden_keys
     * @return array
     */
    public function hidden_order_item_meta_legacy($hidden_keys)
    {
        // Check if order id can be determined
        if ($order_id = RightPress_Helper::get_wc_order_id()) {

            // Check if we have hidden keys already for this order
            if (!isset(self::$hidden_order_item_meta_key_cache[$order_id])) {

                self::$hidden_order_item_meta_key_cache[$order_id] = array();

                // Load order object
                $order = RightPress_Helper::wc_get_order($order_id);

                // Iterate over order items
                foreach ($order->get_items() as $order_item_key => $order_item) {

                    // Iterate over order item meta
                    foreach ($order_item['item_meta'] as $meta_key => $meta) {

                        // Check if this is our internal meta key; also match data stored with 1.x versions of this extension
                        if (preg_match('/^_wccf_/i', $meta_key) || preg_match('/^wccf_/i', $meta_key)) {

                            // Check if it already exists in hidden keys array
                            if (!in_array($meta_key, self::$hidden_order_item_meta_key_cache[$order_id])) {

                                // Add key to hidden keys array
                                self::$hidden_order_item_meta_key_cache[$order_id][] = $meta_key;
                            }
                        }
                    }
                }
            }

            // Add our hidden keys to the main hidden keys array
            $hidden_keys = array_merge($hidden_keys, self::$hidden_order_item_meta_key_cache[$order_id]);
        }

        return $hidden_keys;
    }

    /**
     * Attach product field files to new order email
     *
     * @access public
     * @param array $attachments
     * @param string $email_id
     * @param object $order
     * @return array
     */
    public function attach_product_field_files($attachments, $email_id, $order)
    {
        // Object is not order
        if (!is_a($order, 'WC_Order')) {
            return $attachments;
        }

        // Check if files need to be attached
        if (!WCCF_WC_Order::attachments_allowed($email_id, 'product_field')) {
            return $attachments;
        }

        $attachments = (array) $attachments;

        // Iterate over order items
        foreach ($order->get_items() as $order_item_key => $order_item) {

            // WC31: Update this method to use item meta objects instead of array access in case of WC 3.0+

            // Get order item meta
            $order_item_meta = RightPress_Helper::wc_version_gte('3.0') ? $order_item['item_meta'] : RightPress_Helper::unwrap_post_meta($order_item['item_meta']);

            // Get quantity purchased
            $quantity = !empty($order_item_meta['_qty']) ? (int) $order_item_meta['_qty'] : 1;

            // Track which fields were already processed
            $processed_fields = array();

            // Iterate over order item meta
            foreach ($order_item_meta as $meta_key => $meta_value) {

                // Check if this is our field
                if (!preg_match('/^_wccf_pf_id_/i', $meta_key)) {
                    continue;
                }

                // Field already processed
                if (in_array($meta_value, $processed_fields, true)) {
                    continue;
                }
                else {
                    $processed_fields[] = $meta_value;
                }

                // Load field
                $field = WCCF_Field_Controller::get($meta_value);

                // Check if field was loaded and is file upload
                if (!$field || !$field->field_type_is('file')) {
                    continue;
                }

                // Handle quantity based fields
                for ($i = 0; $i < $quantity; $i++) {

                    // Get file access keys
                    $access_keys = (array) $field->get_stored_value($order_item_key, $i);

                    // Check if file was uploaded for this field
                    if (empty($access_keys)) {
                        continue;
                    }

                    // Iterate over files
                    foreach ($access_keys as $access_key) {

                        // Get file data
                        $file_data = WCCF_Files::get_data_by_access_key($access_key, $order_item_key);

                        // Get temporary file path
                        if ($temporary_file_path = WCCF_Files::get_temporary_file($file_data)) {
                            $attachments[] = $temporary_file_path;
                        }
                    }
                }
            }
        }

        return array_unique($attachments);
    }

    /**
     * Format order item meta for display
     *
     * Starting from WC 3.0.4
     *
     * @access public
     * @param array $formatted_meta
     * @param object $order_item
     * @return array
     */
    public function get_formatted_meta_data($formatted_meta, $order_item)
    {
        // Only products are of interest
        if (!is_a($order_item, 'WC_Order_Item_Product')) {
            return $formatted_meta;
        }

        // Do not add to the backend order item meta editing interface
        if (did_action('woocommerce_before_order_itemmeta') && !did_action('woocommerce_admin_order_items_after_line_items')) {
            return $formatted_meta;
        }

        // Get display values
        // WC31: Fix this to no longer use item_meta and legacy method
        $display_values = $this->get_display_values_from_order_item_meta($order_item['item_meta'], $order_item->get_product());

        // Track repetitive keys (quantity based fields)
        $added_keys = array();

        // Iterate over display values
        foreach ($display_values as $display_value) {

            // Add key to keys array
            $key = $display_value['key'];
            $added_keys[] = $key;

            // Count identical keys
            $key_count = array_count_values($added_keys);

            // Append quantity index
            if ($key_count[$key] > 1) {
                $key .= '_' . ($key_count[$key] - 1);
            }

            // Add to formatted meta array
            $formatted_meta[($order_item->get_id() . '_' . $key)] = (object) array(
                'key'           => $key,
                'value'         => $display_value['value'],
                'display_key'   => $display_value['label'],
                'display_value' => $display_value['value'],
            );
        }

        return $formatted_meta;
    }

    /**
     * Display item meta
     *
     * Temporary solution for WC versions 3.0.0-3.0.3
     *
     * @access public
     * @param string $html
     * @param object $item
     * @param array $args
     * @return string
     */
    public function display_item_meta($html, $item, $args)
    {
        // Get display values
        $display_values = $this->get_display_values_from_order_item_meta($item['item_meta'], $item->get_product());

        $strings = array();
        $our_html    = '';

        foreach ($display_values as $meta) {
            $value = $args['autop'] ? wp_kses_post(wpautop(make_clickable($meta['value']))) : wp_kses_post(make_clickable($meta['value']));
            $strings[] = '<strong class="wc-item-meta-label">' . wp_kses_post($meta['label']) . ':</strong> ' . $value;
        }

        if ($strings) {
            $our_html = $args['before'] . implode($args['separator'], $strings) . $args['after'];
        }

        return $html . ' ' . $our_html;
    }

    /**
     * Format order item meta for display
     *
     * Before WC 3.0
     *
     * @access public
     * @param array $formatted_meta
     * @param object $item_meta
     * @return array
     */
    public function format_order_item_meta($formatted_meta, $item_meta)
    {
        // Get unprocessed meta
        $unprocessed_meta = RightPress_Helper::unwrap_post_meta($item_meta->meta);

        // Get display values
        $display_values = $this->get_display_values_from_order_item_meta($unprocessed_meta, $item_meta->product);

        // Iterate over display values
        foreach ($display_values as $display_value) {

            // Add to formatted meta array
            $formatted_meta[] = $display_value;
        }

        // Get values stored with version 1.x of this extension
        if (WCCF_Migration::support_for('1')) {
            foreach (WCCF_Migration::product_fields_in_order_item_from_1($formatted_meta, $item_meta) as $meta_key => $display_value) {
                $formatted_meta[$meta_key] = $display_value;
            }
        }

        return $formatted_meta;
    }

    /**
     * Display order item meta in order edit view
     *
     * @access public
     * @param int $order_item_id
     * @param array $order_item
     * @param object $product
     * @return void
     */
    public function display_backend_order_item_meta($order_item_id, $order_item, $product)
    {
        // WC31: Fix this to use new order item meta format

        // Get unprocessed order item meta
        $unprocessed_meta = RightPress_Helper::unwrap_post_meta(get_metadata('order_item', $order_item_id));

        // Get fields and display values
        $fields = $this->get_fields_from_order_item_meta($unprocessed_meta, $product);
        $display_values = $this->get_display_values_from_order_item_meta($unprocessed_meta, $product, $fields, $order_item_id);

        // Maybe attempt to load value stored in version 1.x
        if (empty($display_values) && WCCF_Migration::support_for('1')) {
            // WC31: Fix this to use new order item meta format
            $display_values = WCCF_Migration::product_fields_in_admin_order_item_from_1($order_item_id, $order_item, $product);
        }

        // Check if there are any values to display
        if (empty($display_values)) {
            return;
        }

        // Open table
        echo '<div class="wccf_order_item_meta_container"><table cellspacing="0" class="display_meta">';

        // Iterate over display values
        foreach ($display_values as $display_value) {

            // Print meta
            echo '<tr><th>' . $display_value['label'] . ':</th><td><p>' . $display_value['value'] . '</p></td></tr>';
        }

        // Close table
        echo '</table></div>';
    }

    /**
     * Get fields from unprocessed order item meta
     *
     * @access public
     * @param array $unprocessed_meta
     * @param object $product
     * @return array
     */
    public function get_fields_from_order_item_meta($unprocessed_meta, $product)
    {
        // WC31: Fix this to use new order item format

        $fields = array();

        // Iterate over unprocessed item meta
        foreach ($unprocessed_meta as $meta_key => $meta_value) {

            // Check if current meta is for product field id
            if (!preg_match('/^_wccf_pf_id_/i', $meta_key)) {
                continue;
            }

            // Get field key from meta
            $field_key_from_meta = preg_replace('/^_wccf_pf_id_/i', '', $meta_key);

            // Load field
            $field = WCCF_Field_Controller::get($meta_value, 'wccf_product_field');

            // Check if field was loaded and is not trashed
            if (!$field || RightPress_Helper::post_is_trashed($field->get_id())) {
                continue;
            }

            // Add to fields list
            $fields[$field->get_id()] = $field;
        }

        return $fields;
    }

    /**
     * Get field values for display from unprocessed order item meta
     *
     * @access public
     * @param array $unprocessed_meta
     * @param object $product
     * @param array $fields
     * @param int $order_item_id
     * @return array
     */
    public function get_display_values_from_order_item_meta($unprocessed_meta, $product, $fields = null, $order_item_id = null)
    {
        // WC31: after https://github.com/woocommerce/woocommerce/issues/14200 is fixed and we are back to fixing meta handling, implement new method that uses new item meta objects
        return $this->get_display_values_from_order_item_meta_legacy($unprocessed_meta, $product, $fields, $order_item_id);
    }

    /**
     * Get field values for display from unprocessed order item meta
     * Pre WC 3.0 compatibility
     *
     * @access public
     * @param array $unprocessed_meta
     * @param object $product
     * @param array $fields
     * @param int $order_item_id
     * @return array
     */
    public function get_display_values_from_order_item_meta_legacy($unprocessed_meta, $product, $fields = null, $order_item_id = null)
    {
        $display_values = array();

        // Get fields
        $fields = $fields ?: $this->get_fields_from_order_item_meta($unprocessed_meta, $product);

        // Check if pricing can be displayed for this product
        $display_pricing = null;

        // Iterate over fields
        foreach ($fields as $field) {

            // Iterate over values
            foreach ($unprocessed_meta as $meta_key => $meta_value) {
                if (RightPress_Helper::string_contains_phrase($meta_key, $field->get_value_access_key())) {

                    // Get quantity index
                    if ($meta_key !== $field->get_value_access_key()) {

                        // Get potential quantity index to check
                        $quantity_index = str_replace(($field->get_value_access_key() . '_'), '', $meta_key);

                        // Quantity index must be numeric and can't be zero
                        if (!is_numeric($quantity_index) || (int) $quantity_index === 0) {
                            continue;
                        }

                        // Attempt to get extra data
                        $extra_data = RightPress_Helper::array_value_or_false($unprocessed_meta, $field->get_extra_data_access_key($quantity_index));

                        // Extra data not found or quantity index is not in the extra data array
                        if (empty($extra_data) || !is_array($extra_data) || !isset($extra_data['quantity_index']) || (int) $extra_data['quantity_index'] !== (int) $quantity_index) {
                            continue;
                        }

                        // Quantity index validation passed
                        $quantity_index = (int) $quantity_index;
                    }
                    else {
                        $quantity_index = null;
                    }

                    // Get stored value from meta
                    $field_value = RightPress_Helper::array_value_or_false($unprocessed_meta, $field->get_value_access_key($quantity_index));

                    // Check if value was found
                    if ($field_value !== false) {

                        // Check if pricing can be displayed for this product
                        if ($display_pricing === null) {
                            $display_pricing = !WCCF_WC_Product::skip_pricing($product);
                        }

                        // Enrich with extra data
                        $field_value = array(
                            'value' => $field_value,
                            'data'  => RightPress_Helper::array_value_or_false($unprocessed_meta, $field->get_extra_data_access_key($quantity_index)),
                        );

                        // Get display value
                        $display_value = $field->format_display_value($field_value, $display_pricing);

                        // Display empty notice if field is empty
                        if (RightPress_Helper::is_empty($display_value)) {
                            $display_value = '<span style="font-style: italic; color: #999;">' . __('empty', 'rp_wccf') . '</span>';
                        }

                        // Allow field value editing
                        if (!empty($order_item_id) && WCCF_Field_Controller::field_value_editing_allowed('product_field', $field, $field_value, $order_item_id)) {
                            $attributes = 'data-wccf-backend-editing="1" data-wccf-field-id="' . $field->get_id() . '" data-wccf-item-id="' . $order_item_id . '" data-wccf-quantity-index="' . $quantity_index . '" ';
                            $display_value = '<span class="wccf_backend_editing_value" ' . $attributes . '>' . $display_value . '</span>';
                        }

                        // Get field label
                        $field_label = $field->get_label();

                        if ($quantity_index) {
                            $field_label = WCCF_Field_Controller::get_quantity_adjusted_field_label($field_label, $quantity_index);
                        }

                        // Format meta
                        $display_values[] = array(
                            'key'   => 'wccf_pf_' . $field->get_key(),
                            'label' => $field_label,
                            'value' => $display_value,
                        );
                    }
                }
            }
        }

        return $display_values;
    }





}

WCCF_WC_Order_Item::get_instance();

}
