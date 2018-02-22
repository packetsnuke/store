<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce product price caching
 *
 * @class WCCF_WC_Price_Cache
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WC_Price_Cache')) {

abstract class WCCF_WC_Price_Cache
{
    protected $hook_removed         = null;
    protected $store_on_shutdown    = false;
    protected $cache                = array();

    protected $observing = false;
    protected $observed  = array();

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Set up price hooks
        $this->add_all_price_hooks();

        // Invalidate outdated WooCommerce variation price sets when plugin settings change
        add_filter('woocommerce_get_variation_prices_hash', array($this, 'invalidate_outdated_variation_prices'), $this->priority, 2);
    }
    /**
     * Add all price hooks
     *
     * @access public
     * @return void
     */
    public function add_all_price_hooks()
    {
        foreach ($this->get_all_price_hooks() as $hook_data) {
            if (!isset($hook_data['wc_version']) || ($hook_data['wc_version'] === '>=3.0' && RightPress_Helper::wc_version_gte('3.0')) || ($hook_data['wc_version'] === '<3.0' && !RightPress_Helper::wc_version_gte('3.0'))) {
                $this->add_price_hook($hook_data);
            }
        }
    }

    /**
     * Remove all price hooks
     *
     * @access public
     * @return void
     */
    public function remove_all_price_hooks()
    {
        foreach ($this->get_all_price_hooks() as $hook_data) {
            if (!isset($hook_data['wc_version']) || ($hook_data['wc_version'] === '>=3.0' && RightPress_Helper::wc_version_gte('3.0')) || ($hook_data['wc_version'] === '<3.0' && !RightPress_Helper::wc_version_gte('3.0'))) {
                $this->remove_price_hook($hook_data);
            }
        }
    }

    /**
     * Add single price hook
     *
     * @access public
     * @param array $hook_data
     * @return void
     */
    public function add_price_hook($hook_data)
    {
        add_filter($hook_data['name'], $hook_data['callback'], $this->priority, $hook_data['accepted_args']);
    }

    /**
     * Remove single price hook
     *
     * @access public
     * @param array $hook_data
     * @return void
     */
    public function remove_price_hook($hook_data)
    {
        remove_filter($hook_data['name'], $hook_data['callback'], $this->priority);
    }

    /**
     * Add previously removed price hook
     *
     * @access public
     * @return void
     */
    public function add_current_price_hook()
    {
        if ($this->hook_removed) {
            $hook_data = $this->get_price_hook($this->hook_removed);
            $this->add_price_hook($hook_data);
            $this->hook_removed = null;
        }
    }

    /**
     * Remove current price hook to prevent potential infinite loop
     *
     * @access public
     * @return void
     */
    public function remove_current_price_hook()
    {
        // Get current hook name
        $current_hook = current_filter();

        // Check if this is one of our price hooks
        if ($hook_data = $this->get_price_hook($current_hook)) {
            $this->remove_price_hook($hook_data);
            $this->hook_removed = $current_hook;
        }
    }

    /**
     * Get price hook by name
     *
     * @access public
     * @param string $name
     * @return mixed
     */
    public function get_price_hook($name)
    {
        // Iterate over price hooks
        foreach ($this->get_all_price_hooks() as $hook_data) {
            if ($hook_data['name'] === $name) {
                return $hook_data;
            }
        }

        return false;
    }

    /**
     * Get all price hooks
     *
     * @access public
     * @return array
     */
    public function get_all_price_hooks()
    {
        return array(
            array(
                'name'          => 'woocommerce_product_get_price',
                'callback'      => array($this, 'maybe_change_product_price'),
                'accepted_args' => 2,
                'wc_version'    => '>=3.0',
            ),
            array(
                'name'          => 'woocommerce_product_get_sale_price',
                'callback'      => array($this, 'maybe_change_product_price'),
                'accepted_args' => 2,
                'wc_version'    => '>=3.0',
            ),
            array(
                'name'          => 'woocommerce_product_get_regular_price',
                'callback'      => array($this, 'maybe_change_product_price'),
                'accepted_args' => 2,
                'wc_version'    => '>=3.0',
            ),
            array(
                'name'          => 'woocommerce_get_price',
                'callback'      => array($this, 'maybe_change_product_price'),
                'accepted_args' => 2,
                'wc_version'    => '<3.0',
            ),
            array(
                'name'          => 'woocommerce_get_sale_price',
                'callback'      => array($this, 'maybe_change_product_price'),
                'accepted_args' => 2,
                'wc_version'    => '<3.0',
            ),
            array(
                'name'          => 'woocommerce_get_regular_price',
                'callback'      => array($this, 'maybe_change_product_price'),
                'accepted_args' => 2,
                'wc_version'    => '<3.0',
            ),
            array(
                'name'          => 'woocommerce_product_variation_get_price',
                'callback'      => array($this, 'maybe_change_product_price'),
                'accepted_args' => 2,
                'wc_version'    => '>=3.0',
            ),
            array(
                'name'          => 'woocommerce_product_variation_get_sale_price',
                'callback'      => array($this, 'maybe_change_product_price'),
                'accepted_args' => 2,
                'wc_version'    => '>=3.0',
            ),
            array(
                'name'          => 'woocommerce_product_variation_get_regular_price',
                'callback'      => array($this, 'maybe_change_product_price'),
                'accepted_args' => 2,
                'wc_version'    => '>=3.0',
            ),
            array(
                'name'          => 'woocommerce_variation_prices_price',
                'callback'      => array($this, 'maybe_change_variation_price'),
                'accepted_args' => 3,
            ),
            array(
                'name'          => 'woocommerce_variation_prices_regular_price',
                'callback'      => array($this, 'maybe_change_variation_price'),
                'accepted_args' => 3,
            ),
            array(
                'name'          => 'woocommerce_variation_prices_sale_price',
                'callback'      => array($this, 'maybe_change_variation_price'),
                'accepted_args' => 3,
            ),
        );
    }

    /**
     * Get current price type by filter hook
     *
     * @access public
     * @return string
     */
    public function get_current_price_type()
    {
        // Get current filter
        $current_filter = current_filter();

        // Get price type
        if (strstr($current_filter, 'regular')) {
            return 'regular_price';
        }
        else if (strstr($current_filter, 'sale')) {
            return 'sale_price';
        }
        else {
            return 'price';
        }
    }

    /**
     * Start price observation
     *
     * @access public
     * @return void
     */
    public function start_observation()
    {
        $this->observing = true;
    }

    /**
     * Stop price observation and clear observed price array
     *
     * @access public
     * @return void
     */
    public function stop_observation()
    {
        $this->observing = false;
        $this->observed  = array();
    }

    /**
     * Observe price
     *
     * @access public
     * @param int $product_id
     * @param float $price
     * @param string $price_type
     * @return void
     */
    public function observe($product_id, $price, $price_type)
    {
        if ($this->observing) {
            $this->observed[$product_id][$price_type] = $price;
            return true;
        }

        return false;
    }

    /**
     * Get observed prices
     *
     * @access public
     * @return array
     */
    public function get_observed()
    {
        return $this->observed;
    }

    /**
     * Maybe change product price
     *
     * @access public
     * @param float $price
     * @param object $product
     * @return float
     */
    public function maybe_change_product_price($price, $product)
    {
        return $this->maybe_change_price($price, $product);
    }

    /**
     * Maybe change variation price
     *
     * Only runs on WC >= 2.4.7, in older versions variation prices are processed through maybe_change_product_price()
     * Only runs on specific occasions, like when printing variable product price in product list view, otherwise price is retrieved via get_price()
     * Need to monitor further changes to WC_Product_Variable::get_variation_prices() just in case they start retrieving prices through get_price()
     *
     * @access public
     * @param float $price
     * @param object $variation
     * @param object $product
     * @return float
     */
    public function maybe_change_variation_price($price, $variation, $product)
    {
        return $this->maybe_change_price($price, $variation);
    }

    /**
     * Maybe change product or variation price
     *
     * @access public
     * @param float $price
     * @param object $product
     * @return float
     */
    public function maybe_change_price($price, $product)
    {
        // Get product id
        $product_id = RightPress_WC_Legacy::product_get_id($product);

        // Get price type
        $price_type = $this->get_current_price_type();

        // Observe-only request
        if ($this->observe($product_id, $price, $price_type)) {
            return $price;
        }

        // Skip variable products (that does not affect individual variations)
        if ($product->is_type('variable')) {
            return $price;
        }

        // Skip products with no price set - they can't be purchased
        if ($price_type === 'price' && $price === '') {
            return $price;
        }

        // Check if price can be changed for this product
        if (!$this->proceed($product, $price, $price_type)) {
            return $price;
        }

        // Remove current price hook to prevent potential infinite loop
        // Note: Removed this for use in WCDPD, need to check if it's still needed for other plugins
        // $this->remove_current_price_hook();

        // Get cached price hash
        $hash = $this->get_hash($product, $price, $price_type);

        // Get cached price
        $adjusted_price = $this->get_cached_price($product, $price_type, $hash);

        // Price not in cache
        if ($adjusted_price === false) {

            // Calculate price
            $method = 'calculate_' . $price_type;
            $adjusted_price = $this->$method($product, $price);

            // Store price in cache
            $this->cache_price($product, $adjusted_price, $price_type, $hash);
        }

        // Add current price hook back
        // Note: Removed this for use in WCDPD, need to check if it's still needed for other plugins
        // $this->add_current_price_hook();

        // Return adjusted price
        return $adjusted_price;
    }

    /**
     * Get valid cached price
     *
     * @access public
     * @param object $product
     * @param string $price_type
     * @param string $hash
     * @return mixed
     */
    public function get_cached_price($product, $price_type, $hash)
    {
        // Get product id
        $product_id = RightPress_WC_Legacy::product_get_id($product);

        // Product variation
        if ($product->is_type('variation')) {

            // Get variable product id
            $parent_id = RightPress_WC_Legacy::product_variation_get_parent_id($product);

            // Load cached prices from meta
            if (!isset($this->cache[$parent_id])) {

                // Get cached prices for all variations
                $cached_prices = RightPress_WC_Meta::product_get_meta($parent_id, ($this->cache_prefix . '_price_cache'), true);

                // Store all variation prices in memory
                $this->cache[$parent_id] = (is_array($cached_prices) && !empty($cached_prices)) ? $cached_prices : array();
            }

            // Search in memory
            if (isset($this->cache[$parent_id][$product_id][$price_type]) && $this->cache[$parent_id][$product_id][$price_type]['h'] === $hash) {
                return $this->cache[$parent_id][$product_id][$price_type]['p'];
            }
        }
        // Simple product
        else {

            // Load cached prices from meta
            if (!isset($this->cache[$product_id])) {

                // Get cached prices
                $cached_prices = RightPress_WC_Meta::product_get_meta($product_id, ($this->cache_prefix . '_price_cache'), true);

                // Store prices in memory
                $this->cache[$product_id] = (is_array($cached_prices) && !empty($cached_prices)) ? $cached_prices : array();
            }

            // Search in memory
            if (isset($this->cache[$product_id][$price_type]) && $this->cache[$product_id][$price_type]['h'] === $hash) {
                return $this->cache[$product_id][$price_type]['p'];
            }
        }

        // Price not cached yet
        return false;
    }

    /**
     * Cache price
     *
     * @access public
     * @param object $product
     * @param float $price
     * @param string $price_type
     * @param string $hash
     * @return void
     */
    public function cache_price($product, $price, $price_type, $hash)
    {
        // Get product id
        $product_id = RightPress_WC_Legacy::product_get_id($product);

        // Format value to store
        $value = array(
            'h' => $hash,
            'p' => $price,
        );

        // Product variation
        if ($product->is_type('variation')) {
            $parent_id = RightPress_WC_Legacy::product_variation_get_parent_id($product);
            $this->cache[$parent_id][$product_id][$price_type] = $value;
            $this->cache[$parent_id]['_update'] = true;
        }
        // Simple product
        else {
            $this->cache[$product_id][$price_type] = $value;
            $this->cache[$product_id]['_update'] = true;
        }

        // Dump cached prices to product meta on shutdown
        if ($this->store_on_shutdown === false) {
            register_shutdown_function(array($this, 'store_cached_prices'));
            $this->store_on_shutdown = true;
        }
    }

    /**
     * Store cached prices in product meta
     *
     * @access public
     * @return void
     */
    public function store_cached_prices()
    {
        // Iterate over cache entries
        foreach ($this->cache as $product_id => $values) {

            // Store updated entries only
            if (isset($values['_update']) && $values['_update']) {

                // Remove flag
                unset($values['_update']);

                // Store in meta
                RightPress_WC_Meta::product_update_meta_data($product_id, ($this->cache_prefix . '_price_cache'), $values);
            }
        }
    }

    /**
     * Invalidate outdated WooCommerce variation price sets when plugin settings change
     *
     * @access public
     * @param array $price_hash
     * @param object $product
     * @return array
     */
    public function invalidate_outdated_variation_prices($price_hash, $product)
    {
        $price_hash[] = $this->get_settings_hash($product);
        return $price_hash;
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
        return RightPress_Helper::get_hash(false, array(
            $price_type,
            (float) $price,
            $product,
        ));
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
        return $this->cache_prefix . '_' . RightPress_Helper::get_hash(false, array());
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
        return $price;
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
        return $price;
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
        return $price;
    }


}
}
