<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to shortcodes
 *
 * @class WCCF_Shortcodes
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Shortcodes')) {

class WCCF_Shortcodes
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
        // Print fields
        add_shortcode('wccf_print_product_field', array($this, 'print_product_field'));
        add_shortcode('wccf_print_product_fields', array($this, 'print_product_fields'));
        add_shortcode('wccf_print_checkout_field', array($this, 'print_checkout_field'));
        add_shortcode('wccf_print_checkout_fields', array($this, 'print_checkout_fields'));
        add_shortcode('wccf_print_user_field', array($this, 'print_user_field'));
        add_shortcode('wccf_print_user_fields', array($this, 'print_user_fields'));

        // Print field values
        add_shortcode('wccf_print_product_field_value', array($this, 'print_product_field_value'));
        add_shortcode('wccf_print_product_field_values', array($this, 'print_product_field_values'));
        add_shortcode('wccf_print_product_prop_value', array($this, 'print_product_prop_value'));
        add_shortcode('wccf_print_product_prop_values', array($this, 'print_product_prop_values'));
        add_shortcode('wccf_print_checkout_field_value', array($this, 'print_checkout_field_value'));
        add_shortcode('wccf_print_checkout_field_values', array($this, 'print_checkout_field_values'));
        add_shortcode('wccf_print_order_field_value', array($this, 'print_order_field_value'));
        add_shortcode('wccf_print_order_field_values', array($this, 'print_order_field_values'));
        add_shortcode('wccf_print_user_field_value', array($this, 'print_user_field_value'));
        add_shortcode('wccf_print_user_field_values', array($this, 'print_user_field_values'));

        // Legacy shortcodes
        add_shortcode('wccf_display_product_properties', array($this, 'display_product_properties'));
    }

    /**
     * Print single product field
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_product_field($attributes)
    {
        ob_start();
        wccf_print_product_field(self::fix_attributes($attributes, 'wccf_print_product_field'));
        return ob_get_clean();
    }

    /**
     * Print multiple product fields
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_product_fields($attributes)
    {
        ob_start();
        wccf_print_product_fields(self::fix_attributes($attributes, 'wccf_print_product_fields'));
        return ob_get_clean();
    }

    /**
     * Print single checkout field
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_checkout_field($attributes)
    {
        ob_start();
        wccf_print_checkout_field(self::fix_attributes($attributes, 'wccf_print_checkout_field'));
        return ob_get_clean();
    }

    /**
     * Print multiple checkout fields
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_checkout_fields($attributes)
    {
        ob_start();
        wccf_print_checkout_fields(self::fix_attributes($attributes, 'wccf_print_checkout_fields'));
        return ob_get_clean();
    }

    /**
     * Print single user field
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_user_field($attributes)
    {
        ob_start();
        wccf_print_user_field(self::fix_attributes($attributes, 'wccf_print_user_field'));
        return ob_get_clean();
    }

    /**
     * Print multiple user fields
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_user_fields($attributes)
    {
        ob_start();
        wccf_print_user_fields(self::fix_attributes($attributes, 'wccf_print_user_fields'));
        return ob_get_clean();
    }

    /**
     * Print single product field value
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_product_field_value($attributes)
    {
        ob_start();
        wccf_print_product_field_value(self::fix_attributes($attributes, 'wccf_print_product_field_value'));
        return ob_get_clean();
    }

    /**
     * Print multiple product field values
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_product_field_values($attributes)
    {
        ob_start();
        wccf_print_product_field_values(self::fix_attributes($attributes, 'wccf_print_product_field_values'));
        return ob_get_clean();
    }

    /**
     * Print single product property value
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_product_prop_value($attributes)
    {
        ob_start();
        wccf_print_product_prop_value(self::fix_attributes($attributes, 'wccf_print_product_prop_value'));
        return ob_get_clean();
    }

    /**
     * Print multiple product property values
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_product_prop_values($attributes)
    {
        ob_start();
        wccf_print_product_prop_values(self::fix_attributes($attributes, 'wccf_print_product_prop_values'));
        return ob_get_clean();
    }

    /**
     * Print single checkout field value
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_checkout_field_value($attributes)
    {
        ob_start();
        wccf_print_checkout_field_value(self::fix_attributes($attributes, 'wccf_print_checkout_field_value'));
        return ob_get_clean();
    }

    /**
     * Print multiple checkout field values
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_checkout_field_values($attributes)
    {
        ob_start();
        wccf_print_checkout_field_values(self::fix_attributes($attributes, 'wccf_print_checkout_field_values'));
        return ob_get_clean();
    }

    /**
     * Print single order field value
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_order_field_value($attributes)
    {
        ob_start();
        wccf_print_order_field_value(self::fix_attributes($attributes, 'wccf_print_order_field_value'));
        return ob_get_clean();
    }

    /**
     * Print multiple order field values
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_order_field_values($attributes)
    {
        ob_start();
        wccf_print_order_field_values(self::fix_attributes($attributes, 'wccf_print_order_field_values'));
        return ob_get_clean();
    }

    /**
     * Print single user field value
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_user_field_value($attributes)
    {
        ob_start();
        wccf_print_user_field_value(self::fix_attributes($attributes, 'wccf_print_user_field_value'));
        return ob_get_clean();
    }

    /**
     * Print multiple user field values
     *
     * @access public
     * @param array $attributes
     * @return string
     */
    public function print_user_field_values($attributes)
    {
        ob_start();
        wccf_print_user_field_values(self::fix_attributes($attributes, 'wccf_print_user_field_values'));
        return ob_get_clean();
    }

    /**
     * Fix shortcode attributes so they can be used in corresponding functions
     *
     * @access public
     * @param array $attributes
     * @param string $shortcode
     * @return array
     */
    public static function fix_attributes($attributes, $shortcode)
    {
        return shortcode_atts(array(
            'key'               => null,
            'keys'              => null,
            'item_id'           => null,
            'checkout_position' => null,
            'formatted'         => null,
        ), $attributes, $shortcode);
    }

    /**
     * Legacy shortcode: Print product properties anywhere via shortcode
     *
     * @access public
     * @param array $attributes

     * @return string
     */
    public function display_product_properties($attributes)
    {
        // Get shortcode attributes
        $attributes = shortcode_atts(array('product_id' => ''), $attributes);

        // Get product id from attributes
        $product_id = !empty($attributes['product_id']) ? $attributes['product_id'] : null;

        // Get content and return
        return WCCF_WC_Product::print_product_properties_function($product_id);
    }



}

WCCF_Shortcodes::get_instance();

}
