<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Product Page
 *
 * @class WCCF_WC_Product
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WC_Product')) {

class WCCF_WC_Product
{
    private static $prices_subject_to_adjustment = null;
    private $save_product_property_values_done = null;

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
         * PRODUCT FIELD RELATED
         */

        // Change Add To Cart link in category pages if product contains at least one custom field
        add_filter('woocommerce_loop_add_to_cart_link', array($this, 'maybe_change_add_to_cart_link'), 10, 2);

        // Display product fields
        add_action('woocommerce_before_add_to_cart_button', array($this, 'display_product_fields'));

        // Live product price update
        add_action('init', array($this, 'live_product_price_update_setup'));
        add_filter('rightpress_live_product_price_update', array($this, 'live_product_price_update_get_price'), 10, 5);
        add_filter('rightpress_live_product_price_update_custom_keys', array($this, 'live_product_price_update_custom_keys'));

        // Maybe change product quantity
        add_filter('woocommerce_quantity_input_args', array($this, 'maybe_change_product_quantity'), 99, 2);

        // Ajax product field view refresh
        add_action('wp_ajax_wccf_refresh_product_field_view', array($this, 'ajax_refresh_product_field_view'));
        add_action('wp_ajax_nopriv_wccf_refresh_product_field_view', array($this, 'ajax_refresh_product_field_view'));

        // Maybe override ajax variation threshold
        add_filter('woocommerce_ajax_variation_threshold', array($this, 'ajax_variation_threshold'), 10, 2);

        /**
         * PRODUCT PROPERTY RELATED
         */

        // Display product admin fields
        add_action('add_meta_boxes', array($this, 'add_meta_box_product_prop'), 99, 2);

        // Add enctype attribute to the product edit page form to allow file uploads
        add_action('post_edit_form_tag', array($this, 'maybe_add_enctype_attribute'));

        // Save product admin field data
        // WC31: Products will no longer be posts
        add_action('save_post', array($this, 'save_product_property_values'), 10, 2);

        // Display product properties in frontend custom tab
        add_filter('woocommerce_product_tabs', array($this, 'add_product_properties_tab'));
    }

    /**
     * Change Add To Cart link in category pages if product contains at least one custom field
     *
     * @access public
     * @param string $link
     * @param object $product
     * @return string
     */
    public function maybe_change_add_to_cart_link($link, $product)
    {
        // Check if there are any fields to display for this product
        if (WCCF_WC_Product::product_has_fields_to_display($product, (WCCF_Settings::get('change_add_to_cart_text') === '1'))) {

            // Get product id
            $product_id = RightPress_WC_Legacy::product_get_id($product);

            // Format new link
            $link = sprintf('<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" data-quantity="%s" class="button %s product_type_%s">%s</a>',
                esc_url(get_permalink($product_id)),
                esc_attr($product_id),
                esc_attr($product->get_sku()),
                esc_attr(isset($quantity) ? $quantity : 1),
                '',
                esc_attr(RightPress_WC_Legacy::product_get_type($product)),
                esc_html(apply_filters('wccf_category_add_to_cart_text', __('View Product', 'rp_wccf'), $product_id))
            );
        }

        return $link;
    }

    /**
     * Check if product has fields to display
     *
     * @access public
     * @param object $product
     * @param bool $required_only
     * @return bool
     */
    public static function product_has_fields_to_display($product, $required_only = false)
    {
        // Maybe skip product fields for this product based on various conditions
        if (!WCCF_WC_Product::skip_product_fields($product)) {

            // Get applicable fields
            if ($fields = WCCF_Product_Field_Controller::get_filtered(null, array('item_id' => RightPress_WC_Legacy::product_get_id($product)))) {

                // Required only
                if ($required_only) {

                    foreach ($fields as $field) {
                        if ($field->is_required()) {
                            return true;
                        }
                    }
                }
                // Any field
                else {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Display custom product fields
     *
     * @access public
     * @return void
     */
    public function display_product_fields()
    {
        $product_id     = RightPress_Helper::get_wc_product_id();
        $product        = wc_get_product($product_id);
        $variation_id   = WCCF_WC_Product::get_variation_id(null, true);

        $conditional_product_fields = false;

        // Maybe skip product fields for this product based on various conditions
        if (is_object($product) && WCCF_WC_Product::skip_product_fields($product)) {
            return;
        }

        // Define filter params
        $filter_params = array(
            'item_id' => $product_id,
            'child_id' => $variation_id,
        );

        // Get all product fields
        $all_fields = WCCF_Product_Field_Controller::get_all();

        // Check if product is variable
        if (in_array(RightPress_WC_Legacy::product_get_type($product), array('variable', 'variable-subscription'), true)) {

            // Check if at least one field has attribute-related conditions
            foreach ($all_fields as $field) {
                if ($field->has_product_attribute_conditions()) {

                    $conditional_product_fields = true;

                    // Ensure that frontend assets are loaded
                    WCCF_Assets::enqueue_frontend_scripts();

                    break;
                }
            }

            // Attempt to determine default variation if not set
            if ($variation_id === null) {
                $variation_id = WCCF_WC_Product::get_default_variation_id($product);
            }

            // Attempt to determine preselected attributes
            if (RightPress_Helper::wc_version_gte('3.0')) {
                $preselected_attributes = apply_filters('woocommerce_product_default_attributes', array_filter((array) maybe_unserialize($product->get_default_attributes())), $product);
            }
            else {
                $preselected_attributes = $product->get_variation_default_attributes();
            }

            if ($preselected_attributes) {
                $filter_params['variation_attributes'] = $preselected_attributes;
            }
        }

        // Open container
        $uses_attribute_conditions = $conditional_product_fields ? 'data-wccf-uses-attribute-conditions="1" ' : '';
        echo '<div id="wccf_product_field_master_container" ' . $uses_attribute_conditions . '>';

        // Filter out fields for display
        $fields = WCCF_Conditions::filter_fields($all_fields, $filter_params);

        // Get quantity if printing after failed add-to-cart validation
        $quantity = (!empty($_REQUEST['add-to-cart']) && !empty($_REQUEST['quantity'])) ? (int) $_REQUEST['quantity'] : null;

        // Alternatively try to get quantity from query vars
        if ($quantity === null && $_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['wccf_quantity'])) {
            $quantity = (int) $_GET['wccf_quantity'];
        }

        // Display list of fields
        WCCF_Field_Controller::print_fields($fields, null, $quantity);

        // Close container if needed
        echo '</div>';
    }

    /**
     * Get product price to display
     *
     * @access public
     * @param float $adjustment
     * @return string
     */
    public static function get_product_price_html($adjustment = 0)
    {
        // Get adjusted product price
        $price = WCCF_WC_Product::get_product_price($adjustment);

        // Format and return price html
        return wc_price($price);
    }

    /**
     * Get product price
     *
     * @access public
     * @param float $adjustment
     * @return float
     */
    public static function get_product_price($adjustment = 0, $quantity = 1)
    {
        // Load product object
        $product_id = RightPress_Helper::get_wc_product_id();
        $product = wc_get_product($product_id);

        // Load default variation if possible for variable products
        if (in_array(RightPress_WC_Legacy::product_get_type($product), array('variable', 'variable-subscription'), true)) {

            // Get default variation id
            $variation_id = WCCF_WC_Product::get_default_variation_id($product);

            // Replace product object with product variation object if default variation id was determined
            if ($variation_id) {
                $product = wc_get_product($variation_id);
            }
        }

        // Get default product price and possibly adjust it
        $price = $product->get_price() + $adjustment;

        // Maybe add tax
        if (get_option('woocommerce_tax_display_shop') === 'incl') {
            $price = RightPress_WC_Legacy::product_get_price_including_tax($product, $quantity, $price);
        }
        else {
            $price = RightPress_WC_Legacy::product_get_price_excluding_tax($product, $quantity, $price);
        }

        // Price can't be negative
        if ($price < 0) {
            $price = 0;
        }

        // Return product price
        return (float) $price;
    }

    /**
     * Ajax product field view refresh
     *
     * @access public
     * @return void
     */
    public function ajax_refresh_product_field_view()
    {
        try {

            $response_data = array();
            $group_by_quantity_index = false;

            // Get Ajax data
            $ajax_data = WCCF_WC_Product::get_ajax_request_data();
            extract($ajax_data);

            // Get all product fields for display
            $fields = WCCF_Product_Field_Controller::get_filtered(null, array(
                'item_id'               => $product_id,
                'child_id'              => $variation_id,
                'variation_attributes'  => $attributes,
            ));

            // Iterate over fields
            foreach ($fields as $field) {
                for ($i = 0; $i < $quantity; $i++) {

                    // Start buffer
                    ob_start();

                    // Select correct quantity index
                    $quantity_index = $field->is_quantity_based() ? $i  : null;

                    // Print single field
                    WCCF_Field_Controller::print_fields(array($field), null, $quantity, $quantity_index);

                    // Get buffer contents and clean it
                    $response_data[] = array(
                        'field_id'          => $field->get_id(),
                        'element_id'        => 'wccf_' . $field->get_context() . '_' . $field->get_key() . ($quantity_index ? ('_' . $quantity_index) : ''),
                        'element_name'      => WCCF_Field_Controller::get_input_name($field, $quantity_index),
                        'field_type'        => $field->get_field_type(),
                        'html'              => ob_get_clean(),
                        'quantity_index'    => $quantity_index,
                    );

                    // Field is not quantity based - print only one field
                    if (!$field->is_quantity_based()) {
                        break;
                    }

                    // Set fields to be grouped by quantity index
                    if ($i > 0) {
                        $group_by_quantity_index = true;
                    }
                }
            }

            // Possibly group by quantity index
            if ($group_by_quantity_index) {

                $not_quantity_based = array();
                $quantity_based = array();

                foreach ($response_data as $data) {

                    // Field is not quantity based
                    if ($data['quantity_index'] === null) {
                        $not_quantity_based[] = $data;
                    }
                    // Field is quantity based
                    else {
                        $quantity_based[$data['quantity_index']][] = $data;
                    }
                }

                $response_data = array();

                foreach ($quantity_based as $quantity_index => $values) {
                    $response_data = array_merge($response_data, $values);
                }

                $response_data = array_merge($response_data, $not_quantity_based);
            }

            // Send response
            echo json_encode(array(
                'result'    => 'success',
                'fields'    => $response_data,
            ));
        }
        catch (Exception $e) {
            echo json_encode(array(
                'result' => 'error',
            ));
        }

        exit;
    }

    /**
     * Check Ajax request and get data
     *
     * @access public
     * @return array
     */
    public static function get_ajax_request_data()
    {
        // Check if data was posted
        if (empty($_POST['data'])) {
            throw new Exception(__('No data received.', 'rp_wccf'));
        }

        // Parse product data and configuration
        $data = urldecode($_POST['data']);
        parse_str($data, $data);

        // Get product id
        if (isset($data['product_id']) && is_numeric($data['product_id'])) {
            $product_id = (int) $data['product_id'];
        }
        else if (isset($data['add-to-cart']) && is_numeric($data['add-to-cart'])) {
            $product_id = (int) $data['add-to-cart'];
        }
        else if (isset($data['wccf_reference_product_id']) && is_numeric($data['wccf_reference_product_id'])) {
            $product_id = (int) $data['wccf_reference_product_id'];
        }
        else {
            throw new Exception(__('Product is not defined.', 'rp_wccf'));
        }

        // Get optional variation id
        if (isset($data['variation_id']) && is_numeric($data['variation_id'])) {
            $attributes = null;
            $variation_id = (int) $data['variation_id'];
        }
        else {
            $attributes = WCCF_WC_Product::get_attributes_array_from_data($data);
            $variation_id = RightPress_Helper::get_wc_variation_id_from_attributes($product_id, $attributes);
        }

        // Get quantity
        $quantity = !empty($data['quantity']) ? (int) $data['quantity'] : 1;

        // Return array
        return compact('data', 'product_id', 'variation_id', 'attributes', 'quantity');
    }

    /**
     * Get attributes array from data array
     *
     * @access public
     * @param array $data
     * @return array
     */
    public static function get_attributes_array_from_data($data)
    {
        $attributes = array();

        foreach ($data as $key => $value) {
            if (RightPress_Helper::string_contains_phrase($key, 'attribute_pa_')) {
                $attributes[str_replace('attribute_', '', $key)] = $value;
            }
        }

        return $attributes;
    }

    /**
     * Get WooCommerce product types
     *
     * @access public
     * @return array
     */
    public static function get_product_types()
    {
        return wc_get_product_types();
    }

    /**
     * Add meta box for product properties
     *
     * @access public
     * @param string $post_type
     * @param object $post
     * @return void
     */
    public function add_meta_box_product_prop($post_type, $post)
    {
        // WC31: Products will no longer be posts

        // Not product?
        if ($post_type !== 'product') {
            return;
        }

        // Get product
        $product = wc_get_product($post->ID);

        // Make sure product type is not grouped
        if ($product->get_type() === 'grouped') {
            return;
        }

        // Get fields to display
        $fields = WCCF_Product_Property_Controller::get_filtered(null, array(), null, true);

        // Add meta box if we have at least one field to display
        if (!empty($fields)) {
            add_meta_box(
                'wccf_product_properties',
                apply_filters('wccf_context_label', WCCF_Settings::get('alias_product_prop'), 'product_prop', 'backend'),
                array($this, 'print_meta_box_product_properties'),
                'product',
                'normal',
                'high'
            );
        }
    }

    /**
     * Print product properties meta box on product edit page
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function print_meta_box_product_properties($post)
    {
        // Get fields to display
        $fields = WCCF_Product_Property_Controller::get_filtered();

        // Print fields
        WCCF_Field_Controller::print_fields($fields, $post->ID);
    }

    /**
     * Add enctype attribute to the product edit page form to allow file uploads
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function maybe_add_enctype_attribute($post)
    {
        // Skip other post types
        // WC31: Products will no longer be posts
        if ($post->post_type !== 'product') {
            return;
        }

        // Add enctype attribute
        echo ' enctype="multipart/form-data" ';
    }

    /**
     * Process product property data on save post action
     *
     * WC31: Maybe we could listen for WC object updates instead of WP post updates? We could get a reference of $product and save memory. Same for orders.
     *
     * @access public
     * @param int $post_id
     * @param object $post
     * @return void
     */
    public function save_product_property_values($post_id, $post)
    {
        // Prevent it from running more than once
        if (isset($this->save_product_property_values_done) && $this->save_product_property_values_done) {
            return;
        }
        else {
            $this->save_product_property_values_done = true;
        }

        // Only process posts with type product
        // WC31: Products will no longer be posts
        if ($post->post_type !== 'product') {
            return;
        }

        // Load product if needed
        $item = RightPress_Helper::wc_version_gte('3.0') ? RightPress_Helper::wc_get_product($post_id) : $post_id;

        // Store posted field values
        WCCF_Field_Controller::store_field_values($item, 'product_prop');

        // Save product if needed
        if (is_object($item)) {
            $item->save();
        }
    }

    /**
     * Maybe add product properties tab in product page
     *
     * @access public
     * @param array $tabs
     * @return array
     */
    public function add_product_properties_tab($tabs)
    {
        global $post;

        // Allow developers to hide default properties tab
        if (!apply_filters('wccf_display_product_properties', true, $post->ID)) {
            return $tabs;
        }

        // Get product properties
        $fields = WCCF_Product_Property_Controller::get_filtered(null, array('item_id' => $post->ID));

        // Iterate over fields
        foreach ($fields as $field) {

            // Check if field is public
            if (!$field->is_public()) {
                continue;
            }

            // Get field value
            if (WCCF_Settings::get('display_default_product_prop_values')) {
                $field_value = $field->get_final_value($post->ID);
            }
            else {
                $field_value = $field->get_stored_value($post->ID);
            }

            // Check if field has value
            if ($field_value === false) {
                continue;
            }

            // Add tab
            $tabs = array_merge($tabs, array('wccf_product_properties' => array(
                'callback'  => array($this, 'print_product_properties_tab_content'),
                'title'     => apply_filters('wccf_context_label', WCCF_Settings::get('alias_product_prop'), 'product_prop', 'frontend'),
                'priority'  => apply_filters('wccf_product_properties_display_position', 21)
            )));

            // Break from cycle
            break;
        }

        // Return tabs
        return $tabs;
    }

    /**
     * Print product properties tab content
     *
     * @access public
     * @return void
     */
    public function print_product_properties_tab_content()
    {
        self::print_product_property_values_in_frontend();
    }

    /**
     * Display product properties anywhere via PHP function
     *
     * @access public
     * @param int $product_id
     * @return void
     */
    public static function print_product_properties_function($product_id = null)
    {
        // Get content and return
        return self::print_product_property_values_in_frontend($product_id, true);
    }

    /**
     * Print product property value list
     *
     * @access public
     * @param int $product_id
     * @param bool $return_html
     * @param bool $skip_filter
     * @return void
     */
    public static function print_product_property_values_in_frontend($product_id = null, $return_html = false, $skip_filter = false)
    {
        // Get product id if it was not passed in
        $product_id = RightPress_Helper::get_wc_product_id($product_id);

        // Check if product ID is set
        if (!$product_id) {
            return '';
        }

        // Get product properties for this product
        $fields = WCCF_Product_Property_Controller::get_filtered(null, array('item_id' => $product_id));

        // Get values to display
        $display = WCCF_Field_Controller::get_field_values_for_frontend($fields, $product_id, 'product_prop');

        // Allow developers to skip displaying frontend product property values in default position
        if (!$skip_filter && !apply_filters('wccf_frontend_display_product_property_values', true, $display, $product_id, $return_html)) {
            return;
        }

        // Include template if we have at least one public field with value
        if (!empty($display) && is_array($display)) {

            // Return instead of output?
            if ($return_html) {
                ob_start();
            }

            // Include template
            WCCF::include_template('product/product-properties-data', array(
                'fields' => $display,
            ));

            // Return instead of output?
            if ($return_html) {
                $content = ob_get_contents();
                ob_end_clean();
                return $content;
            }
        }
        else if ($return_html) {
            return '';
        }
    }

    /**
     * Check if at least one active product field or product property adjusts pricing
     *
     * @access public
     * @return bool
     */
    public static function prices_subject_to_adjustment()
    {
        // Check if we have this flag in memory
        if (self::$prices_subject_to_adjustment === null) {

            // Run query to find product fields or product properties with pricing
            $query = new WP_Query(array(
                'post_type'         => array('wccf_product_field', 'wccf_product_prop'),
                'post_status'       => 'publish',
                'fields'            => 'ids',
                'posts_per_page'    => 1,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'       => 'pricing_value',
                        'value'     => NULL,
                        'compare'   => '!=',
                    ),
                    array(
                        'key'       => 'options',
                        'value'     => '"pricing_value";d:',
                        'compare'   => 'LIKE',
                    ),
                ),
                'tax_query' => array(
                    'relation'=> 'OR',
                    array(
                        'taxonomy'  => 'wccf_product_field_status',
                        'field'     => 'slug',
                        'terms'     => 'enabled',
                    ),
                    array(
                        'taxonomy'  => 'wccf_product_prop_status',
                        'field'     => 'slug',
                        'terms'     => 'enabled',
                    ),
                ),
            ));

            // Check if at least one field has pricing and store flag in memory
            self::$prices_subject_to_adjustment = ((int) $query->found_posts) > 0;
        }

        // Return flag from memory
        return self::$prices_subject_to_adjustment;
    }

    /**
     * Maybe skip all product field display for specific product
     *
     * @access public
     * @param mixed $product
     * @param mixed $variation
     * @return bool
     */
    public static function skip_product_fields($product, $variation = null)
    {
        return WCCF_WC_Product::skip($product, $variation, 'product_fields');
    }

    /**
     * Maybe skip all pricing adjustments for specific product
     *
     * @access public
     * @param mixed $product
     * @param mixed $variation
     * @return bool
     */
    public static function skip_pricing($product, $variation = null)
    {
        return WCCF_WC_Product::skip($product, $variation, 'pricing');
    }

    /**
     * Maybe skip all pricing adjustments for specific product
     *
     * @access public
     * @param mixed $product
     * @param mixed $variation
     * @param string $subject
     * @return bool
     */
    public static function skip($product, $variation, $subject)
    {
        // Get product
        if (!is_object($product)) {
            $product = wc_get_product($product);
        }

        // Get variation
        if ($variation !== null && !is_object($variation)) {
            $variation = wc_get_product($variation);
        }

        // Check if product was loaded
        if (!is_object($product)) {
            return true;
        }

        // Get product types to skip
        if ($subject === 'product_fields') {
            $product_types = array('grouped', 'external');
        }
        else {
            $product_types = array('grouped');
        }

        // Skip specific product types
        if (in_array($product->get_type(), apply_filters('wccf_skip_' . $subject . '_for_product_types', $product_types))) {
            return true;
        }

        // Allow developers to skip specific products or variations
        if (apply_filters('wccf_skip_' . $subject . '_for_product', false, $product, $variation)) {
            return true;
        }

        return false;
    }

    /**
     * Get product attribute term ids for product
     *
     * @access public
     * @param mixed $product
     * @return array
     */
    public static function get_attribute_term_ids($product)
    {
        $ids = array();

        // Product id was passed in
        if (!is_object($product)) {

            // Load product object
            $product = wc_get_product($product);

            if (!$product) {
                return $ids;
            }
        }

        // Iterate over attributes
        foreach ((array) $product->get_attributes() as $attribute_key => $attribute) {

            // WC31: Check if this still works fine

            // Skip attributes used for variations
            if (!empty($attribute['is_variation'])) {
                continue;
            }

            // Get product terms
            $product_terms = (array) wc_get_product_terms(RightPress_WC_Legacy::product_get_id($product), $attribute_key, array('fields' => 'slugs'));

            // Iterate over product terms
            foreach ($product_terms as $term_id => $term_slug) {

                // Add attribute id to list
                if (!in_array($term_id, $ids, true)) {
                    $ids[] = (int) $term_id;
                }
            }
        }

        return $ids;
    }

    /**
     * Attempt to get variation id
     *
     * If $check_attributes_in_request is true, this will attempt to determine variation id by attributes found in posted data or query string, e.g. ?attribute_pa_color=blue&attribute_pa_size=big
     *
     * @access public
     * @param mixed $variation_id
     * @param bool $check_attributes_in_request
     * @return void
     */
    public static function get_variation_id($variation_id = null, $check_attributes_in_request = false)
    {
        // Already set
        if ($variation_id !== null) {
            return (int) $variation_id;
        }

        // Add To Cart
        // WC31: Products will no longer be posts
        if (!empty($_REQUEST['add-to-cart']) && is_numeric($_REQUEST['add-to-cart']) && RightPress_Helper::post_type_is($_REQUEST['add-to-cart'], 'product')) {
            if (!empty($_REQUEST['variation_id']) && is_numeric($_REQUEST['variation_id']) && RightPress_Helper::post_type_is($_REQUEST['variation_id'], 'product_variation')) {
                return (int) $_REQUEST['variation_id'];
            }
        }

        // Add To Cart (the other way)
        // WC31: Products will no longer be posts
        if (isset($_POST['action']) && $_POST['action'] === 'woocommerce_add_to_cart' && isset($_POST['product_id']) && is_numeric($_POST['product_id']) && RightPress_Helper::post_type_is($_POST['product_id'], 'product')) {
            if (!empty($_POST['variation_id']) && is_numeric($_POST['variation_id']) && RightPress_Helper::post_type_is($_POST['variation_id'], 'product_variation')) {
                return $_POST['product_id'];
            }
        }

        // Maybe attempt to determine variation id by looking into product attributes in request
        if ($check_attributes_in_request) {

            // Get attributes from request data
            $attributes = WCCF_WC_Product::get_attributes_array_from_data($_REQUEST);

            // Figure out product id
            $product_id = RightPress_Helper::get_wc_product_id();

            // Check if we were able to get product id
            if ($product_id && !empty($attributes)) {

                // Attempt to figure out variation id from attributes found in request
                return RightPress_Helper::get_wc_variation_id_from_attributes($product_id, $attributes);
            }
        }

        // Failed figuring out variation id
        return null;
    }

    /**
     * Attempt to get default product variation id
     *
     * @access public
     * @param mixed $product
     * @return int
     */
    public static function get_default_variation_id($product)
    {
        // Product id was passed in
        if (!is_object($product)) {

            // Load product object
            $product = wc_get_product($product);

            if (!$product) {
                return null;
            }
        }

        // Product is not variable
        if (RightPress_WC_Legacy::product_get_type($product) !== 'variable') {
            return null;
        }

        // Get all product attributes and all product variation attributes
        $all_attributes = $product->get_variation_attributes();

        if (RightPress_Helper::wc_version_gte('3.0')) {
            $default_attributes = apply_filters('woocommerce_product_default_attributes', array_filter((array) maybe_unserialize($product->get_default_attributes())), $product);
        }
        else {
            $default_attributes = $product->get_variation_default_attributes();
        }

        // Default variation is only known if all attributes have default attributes
        if (count($all_attributes) === count($default_attributes)) {

            // Get default variation id
            return (int) RightPress_Helper::get_wc_variation_id_from_attributes($product, $default_attributes);
        }

        return null;
    }

    /**
     * Maybe change product quantity
     *
     * @access public
     * @param array $args
     * @param object $product
     * @return array
     */
    public function maybe_change_product_quantity($args, $product)
    {
        // Wrong page or request type
        if (!is_product() || $_SERVER['REQUEST_METHOD'] !== 'GET') {
            return $args;
        }

        // Quantity not specified
        if (empty($_GET['wccf_quantity'])) {
            return $args;
        }

        // Change quantity
        $args['input_value'] = (int) $_GET['wccf_quantity'];

        return $args;
    }

    /**
     * Maybe override ajax variation threshold
     *
     * @access public
     * @param int $variation_threshold
     * @param object $product
     * @return int
     */
    public function ajax_variation_threshold($variation_threshold, $product)
    {
        if (WCCF_WC_Product::prices_subject_to_adjustment()) {
            return $variation_threshold > 200 ? $variation_threshold : 200;
        }

        return $variation_threshold;
    }

    /**
     * Live product price update setup
     *
     * @access public
     * @return void
     */
    public function live_product_price_update_setup()
    {
        // Check if functionality is enabled
        if (WCCF_Settings::get('display_total_price')) {

            // Load shared live product price update functionality
            require_once WCCF_PLUGIN_PATH . 'rightpress/components/rightpress-live-product-price-update/rightpress-live-product-price-update.class.php';
        }
    }

    /**
     * Live product price update - get price
     *
     * @access public
     * @param array $price_data
     * @param object $product
     * @param int $quantity
     * @param array $variation_attributes
     * @param array $data
     * @return float
     */
    public function live_product_price_update_get_price($price_data, $product, $quantity = 1, $variation_attributes = array(), $data = array())
    {
        // Check if functionality is enabled
        if (WCCF_Settings::get('display_total_price')) {

            // Get parent product in case of product variation
            if ($product->is_type('variation')) {
                $variation = $product;
                $product = RightPress_WC_Legacy::product_variation_get_parent($variation);
            }
            else {
                $variation = null;
            }

            // Check if pricing adjustments are not skipped
            if (!WCCF_WC_Product::skip_pricing($product, $variation)) {

                // Remove filter so we don't have our own pricing adjustment done twice
                define('WCCF_SUPPRESS_PRICE_OVERRIDE', true);

                // Select product object
                $object = $variation ? $variation : $product;

                // Check if this product supports fields and pricing at all
                if (!WCCF_WC_Product::skip_product_fields($product, $variation) && !WCCF_WC_Product::skip_pricing($product, $variation)) {

                    // Reconstruct configuration array
                    $wccf = array();
                    $has_pricing = false;

                    // Iterate over fields
                    if (!empty($data['wccf']) && !empty($data['wccf']['product_field']) && is_array($data['wccf']['product_field'])) {
                        foreach ($data['wccf']['product_field'] as $field_id => $field_value) {

                            // Add value
                            $wccf[$field_id] = array(
                                'value' => $field_value,
                            );

                            // Check if field adjusts price
                            if ($field = WCCF_Field_Controller::cache($field_id)) {
                                if ($field->has_pricing()) {
                                    $has_pricing = true;
                                }
                            }
                        }
                    }

                    // Check complete input list as well
                    if (!$has_pricing && !empty($data['rightpress_complete_input_list']) && is_array($data['rightpress_complete_input_list'])) {
                        foreach (array('wccf', 'wccf_ignore') as $prefix) {
                            foreach ($data['rightpress_complete_input_list'] as $input_name) {

                                $query = $prefix . '[product_field][';

                                // Check if this is our input
                                if (substr($input_name, 0, strlen($query)) === $query) {

                                    // Get field id
                                    if (preg_match('/' . preg_quote($query) . '([\d_]+)[\]\[]+/i', $input_name, $matches)) {
                                        if (!empty($matches[1])) {

                                            // Clean field id
                                            $field_id = WCCF_Field_Controller::clean_field_id($matches[1]);

                                            // Check if field adjusts price
                                            if ($field = WCCF_Field_Controller::cache($field_id)) {
                                                if ($field->has_pricing()) {
                                                    $has_pricing = true;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if ($has_pricing) {
                                break;
                            }
                        }
                    }

                    // Only proceed if at least one field adjusts the price
                    if ($has_pricing) {

                        // Get price to adjust
                        $price = ($price_data['price'] !== null) ? $price_data['price'] : RightPress_WC_Legacy::product_get_price($object);

                        // Get ids
                        $product_id = RightPress_WC_Legacy::product_get_id($product);
                        $variation_id = $variation ? RightPress_WC_Legacy::product_get_id($variation) : null;

                        // Get adjusted price
                        $adjusted_price = WCCF_Pricing::get_adjusted_price($price, $product_id, $variation_id, $wccf, $quantity, true, false);

                        // Allow more spaces to fix totals for quantity based price adjusting fields
                        $adjusted_price = WCCF_Pricing::fix_quantity_based_fields_product_price($adjusted_price, $quantity);

                        // Set price and label
                        $change = array(
                            'price' => $adjusted_price,
                            'label' => __('Price', 'rp_wccf'),
                        );

                        // Add to changeset
                        $price_data['changeset']['woocommerce-custom-fields'] = $change;

                        // Unset label if it was added by another plugin
                        if ($price_data['label'] !== null) {
                            unset($change['label']);
                        }

                        // Overwrite main properties
                        $price_data = array_merge($price_data, $change);
                    }
                }
            }
        }

        return $price_data;
    }

    /**
     * Request field data to be included in live product price update data set
     *
     * @access public
     * @param array $custom_keys
     * @return array
     */
    public function live_product_price_update_custom_keys($custom_keys)
    {
        return array_merge($custom_keys, array('wccf'));
    }









}

WCCF_WC_Product::get_instance();

}
