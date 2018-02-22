<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce product display price override
 *
 * @class WCCF_WC_Product_Price
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WC_Product_Price')) {

class WCCF_WC_Product_Price extends WCCF_WC_Price_Cache
{
    protected $cache_prefix = 'rp_wccf';
    protected $priority     = 10;

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
        // Check if prices need to be changed
        if (WCCF_Settings::get('product_field_prices_include_default') || WCCF_Settings::get('product_property_prices_include_default')) {
            parent::__construct();
        }
    }

    /**
     * Check if price can be changed
     *
     * @access public
     * @param object $product
     * @param float $price
     * @param string $price_type
     * @return bool
     */
    public function proceed($product, $price, $price_type)
    {
        // Don't change prices for in admin ui
        if (is_admin() && !defined('DOING_AJAX')) {
            return false;
        }

        // Suppress price override flag set
        if (defined('WCCF_SUPPRESS_PRICE_OVERRIDE')) {
            return false;
        }

        // Skip pricing adjustment for product based on various conditions
        if (WCCF_WC_Product::skip_pricing($product)) {
            return false;
        }

        // Do not touch cart item products - they are handled in WCCF_WC_Cart
        if (isset($product->wccf_cart_item_product) && $product->wccf_cart_item_product === true) {
            return false;
        }

        // Check if at least one active product field or product property adjusts pricing
        if (!WCCF_WC_Product::prices_subject_to_adjustment()) {
            return false;
        }

        return true;
    }

    /**
     * Get cached price validation hash
     * Used to identify outdated cached prices
     *
     * @access public
     * @param object $product
     * @param float $price
     * @param string $price_type
     * @return string
     */
    public function get_hash($product, $price, $price_type)
    {
        // Data for hash
        $data = array(

            // Request price
            $price_type,
            (float) $price,

            // Prices set in product settings
            (float) RightPress_WC_Legacy::product_get_price($product, 'edit'),
            (float) RightPress_WC_Legacy::product_get_regular_price($product, 'edit'),
            (float) RightPress_WC_Legacy::product_get_sale_price($product, 'edit'),

            // Plugin settings hash
            $this->get_settings_hash($product),
        );

        // Return hash
        return RightPress_Helper::get_hash(false, $data);
    }

    /**
     * Get settings hash
     *
     * @access public
     * @param object $product
     * @return string
     */
    public function get_settings_hash($product)
    {
        return $this->cache_prefix . '_' . RightPress_Helper::get_hash(false, array(
            WCCF_WC_Product::skip_product_fields($product),
            WCCF_Settings::get_objects_revision(),
            WCCF_Settings::get('_all'),
        ));
    }

    /**
     * Calculate price
     *
     * @access public
     * @param object $product
     * @param float $price
     * @return foat
     */
    public function calculate_price($product, $price)
    {
        return $this->get_adjusted_price($product, $price);
    }

    /**
     * Calculate sale price
     *
     * @access public
     * @param object $product
     * @param float $price
     * @return foat
     */
    public function calculate_sale_price($product, $price)
    {
        return $price === '' ? $price : $this->get_adjusted_price($product, $price);
    }

    /**
     * Calculate regular price
     *
     * @access public
     * @param object $product
     * @param float $price
     * @return foat
     */
    public function calculate_regular_price($product, $price)
    {
        return $this->get_adjusted_price($product, $price);
    }

    /**
     * Get adjusted price
     *
     * @access public
     * @param object $product
     * @param float $price
     * @return float
     */
    public function get_adjusted_price($product, $price)
    {
        // Get ids
        if ($product->is_type('variation')) {
            $object_id = RightPress_WC_Legacy::product_variation_get_parent_id($product);
            $variation_id = RightPress_WC_Legacy::product_get_id($product);
        }
        else {
            $object_id = RightPress_WC_Legacy::product_get_id($product);
            $variation_id = null;
        }

        // Get adjusted price
        return WCCF_Pricing::get_adjusted_price($price, $object_id, $variation_id);
    }

    /**
     * Reset cached price for product
     *
     * @access public
     * @param mixed $product
     * @return void
     */
    public static function clear_cached_price($product)
    {
        // Load product
        if (!is_object($product)) {
            $product = wc_get_product($product);
        }

        // Switch to parent product for variations
        if ($product->is_type('variation')) {
            $product = RightPress_WC_Legacy::product_variation_get_parent($product);
        }

        // Clear cached prices
        $product = RightPress_Helper::wc_version_gte('3.0') ? $product : RightPress_WC_Legacy::product_get_id($product);
        RightPress_WC_Meta::product_delete_meta_data($product, 'rp_wccf_price_cache');
    }





}

WCCF_WC_Product_Price::get_instance();

}
