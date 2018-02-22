<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Integration
 * Plugin: WooCommerce Product Bundles
 * Author: WooCommerce
 *
 * @class WCCF_Integration_WC_Product_Bundles
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Integration_WC_Product_Bundles')) {

class WCCF_Integration_WC_Product_Bundles
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
        // Flag bundled products
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'flag_bundled_products'), 11, 3);

        // Check if product field values can be added to cart item
        add_filter('wccf_add_cart_item_product_field_values', array($this, 'add_product_field_values'), 10, 2);

        // Do not adjust prices of bundled products in cart
        add_filter('wccf_skip_pricing_for_product', array($this, 'skip_pricing_for_product'), 10, 2);
        add_filter('wccf_skip_pricing_for_cart_item', array($this, 'skip_pricing_for_cart_item'), 10, 2);
    }

    /**
     * Flag bundled products
     *
     * @access public
     * @param array $cart_item
     * @param array $values
     * @param string $key
     * @return array
     */
    public function flag_bundled_products($cart_item, $values, $key)
    {
        if ($this->cart_item_is_bundled_product($cart_item)) {
            $cart_item['data']->wccf_wc_product_bundles_bundled_product = true;
        }

        return $cart_item;
    }

    /**
     * Check if cart item represents a bundled product, that is child product
     * that belongs to a bundle
     *
     * @access public
     * @param array $cart_item
     * @return bool
     */
    public function cart_item_is_bundled_product($cart_item)
    {
        return !empty($cart_item['bundled_by']) && !empty($cart_item['bundled_item_id']);
    }

    /**
     * Check if product field values can be added to cart item
     *
     * @access public
     * @param bool $add
     * @param array $cart_item_data
     * @return bool
     */
    public function add_product_field_values($add, $cart_item_data)
    {
        if ($this->cart_item_is_bundled_product($cart_item_data)) {
            return false;
        }

        return $add;
    }

    /**
     * Do not adjust prices of bundled products in cart
     *
     * @access public
     * @param bool $skip
     * @param object $product
     * @return bool
     */
    public function skip_pricing_for_product($skip, $product)
    {
        if (isset($product->wccf_wc_product_bundles_bundled_product) && $product->wccf_wc_product_bundles_bundled_product === true) {
            return true;
        }

        return $skip;
    }

    /**
     * Do not adjust prices of bundled products in cart
     *
     * @access public
     * @param bool $skip
     * @param array $cart_item
     * @return bool
     */
    public function skip_pricing_for_cart_item($skip, $cart_item)
    {
        return $this->skip_pricing_for_product($skip, $cart_item['data']);
    }






}

WCCF_Integration_WC_Product_Bundles::get_instance();

}
