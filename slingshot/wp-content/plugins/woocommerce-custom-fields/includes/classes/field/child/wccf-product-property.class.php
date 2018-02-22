<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product property object class
 *
 * @class WCCF_Product_Property
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Product_Property')) {

class WCCF_Product_Property extends WCCF_Field
{
    // Define post type title
    protected $post_type                = 'wccf_product_prop';
    protected $post_type_short          = 'product_prop';
    protected $post_type_abbreviation   = 'pp';

    // Define properties unique to this object type
    protected $public;
    protected $pricing_method;
    protected $pricing_value;

    // Define meta keys
    protected static $meta_properties = array(
        'public'            => 'bool',
        'pricing_method'    => 'string',
        'pricing_value'     => 'float',
    );

    /**
     * Constructor class
     *
     * @access public
     * @param mixed $id
     * @param object $trigger
     * @return void
     */
    public function __construct($id)
    {
        // Construct parent first
        parent::__construct($id);
    }

    /**
     * Get meta properties
     *
     * @access public
     * @return array
     */
    protected function get_meta_properties()
    {
        return array_merge(parent::get_meta_properties(), self::$meta_properties);
    }

    /**
     * Check if data exists in storage
     *
     * @access public
     * @param mixed $item
     * @param string $key
     * @return bool
     */
    public function data_exists($item, $key)
    {
        return RightPress_WC_Meta::product_meta_exists($item, $key);
    }

    /**
     * Get data from storage
     *
     * @access public
     * @param mixed $item
     * @param string $key
     * @param bool $single
     * @param string $context
     * @return mixed
     */
    public function get_data($item, $key, $single = true, $context = 'view')
    {
        return RightPress_WC_Meta::product_get_meta($item, $key, $single, $context);
    }

    /**
     * Add data to storage
     *
     * @access public
     * @param mixed $item
     * @param string $key
     * @param mixed $value
     * @param bool $unique
     * @return void
     */
    public function add_data($item, $key, $value, $unique = false)
    {
        RightPress_WC_Meta::product_add_meta_data($item, $key, $value, $unique);
    }

    /**
     * Update data in storage
     *
     * @access public
     * @param mixed $item
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function update_data($item, $key, $value)
    {
        RightPress_WC_Meta::product_update_meta_data($item, $key, $value);
    }

    /**
     * Delete data from storage
     *
     * @access public
     * @param mixed $item
     * @param string $key
     * @return void
     */
    public function delete_data($item, $key)
    {
        RightPress_WC_Meta::product_delete_meta_data($item, $key);
    }

    /**
     * Load item for data storage
     *
     * @access public
     * @param mixed $item
     * @return mixed
     */
    public function load_item($item)
    {
        return is_object($item) ? $item : RightPress_Helper::wc_get_product($item);
    }


}
}
