<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to field pricing
 *
 * @class WCCF_Pricing
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Pricing')) {

class WCCF_Pricing
{
    private static $pricing_methods = null;

    /**
     * Define and return pricing methods
     *
     * @access public
     * @return array
     */
    public static function get_pricing_methods()
    {
        // Define pricing methods if not yet defined
        if (self::$pricing_methods === null) {
            self::$pricing_methods = array(

                // Fees
                'fees' => array(
                    'label'     => __('Fee', 'rp_wccf'),
                    'children'  => array(

                        // Fee
                        'fee' => array(
                            'label'         => __('Fee', 'rp_wccf'),
                            'context'       => array('product_field', 'product_prop', 'checkout_field'),
                            'is_options'    => true,
                        ),

                        // Percentage fee
                        'percentage_fee' => array(
                            'label'         => __('Percentage fee', 'rp_wccf'),
                            'context'       => array('product_field', 'product_prop', 'checkout_field'),
                            'is_options'    => true,
                        ),

                        // Compound percentage fee
                        'compound_percentage_fee' => array(
                            'label'         => __('Compound percentage fee', 'rp_wccf'),
                            'context'       => array('product_field', 'product_prop'),
                            'is_options'    => true,
                        ),

                    ),
                ),

                // Advanced Fees
                'advanced_fees' => array(
                    'label'     => __('Advanced Fee', 'rp_wccf'),
                    'children'  => array(

                        // Fee per character
                        'fee_per_character' => array(
                            'label'         => __('Fee &times; Character count', 'rp_wccf'),
                            'context'       => array('product_field', 'product_prop', 'checkout_field'),
                            'is_options'    => false,
                        ),

                        // Fee x Field value
                        'fee_x_value' => array(
                            'label'         => __('Fee &times; Field value', 'rp_wccf'),
                            'context'       => array('product_field', 'product_prop', 'checkout_field'),
                            'is_options'    => false,
                        ),
                    ),
                ),

                // Discounts
                'discounts' => array(
                    'label'     => __('Discount', 'rp_wccf'),
                    'children'  => array(

                        // Discount
                        'discount' => array(
                            'label'         => __('Discount', 'rp_wccf'),
                            'context'       => array('product_field', 'product_prop', 'checkout_field'),
                            'is_options'    => true,
                        ),

                        // Percentage discount
                        'percentage_discount' => array(
                            'label'         => __('Percentage discount', 'rp_wccf'),
                            'context'       => array('product_field', 'product_prop', 'checkout_field'),
                            'is_options'    => true,
                        ),

                        // Compound percentage discount
                        'compound_percentage_discount' => array(
                            'label'         => __('Compound percentage discount', 'rp_wccf'),
                            'context'       => array('product_field', 'product_prop'),
                            'is_options'    => true,
                        ),
                    ),
                ),
            );
        }

        // Return pricing methods
        return self::$pricing_methods;
    }

    /**
     * Get pricing methods list for display
     *
     * @access public
     * @param string $context
     * @param bool $is_options
     * @return array
     */
    public static function get_pricing_methods_list($context = null, $is_options = false)
    {
        $result = array();

        // Iterate over all pricing method groups
        foreach (self::get_pricing_methods() as $group_key => $group) {

            // Iterate over pricing methods
            foreach ($group['children'] as $method_key => $method) {

                // Check if current pricing method is supported in given context
                if ($context !== null && !in_array($context, $method['context'], true)) {
                    continue;
                }

                // Option based pricing only supports selected pricing methods
                if ($is_options && !$method['is_options']) {
                    continue;
                }

                // Add group if needed
                if (!isset($result[$group_key])) {
                    $result[$group_key] = array(
                        'label'     => $group['label'],
                        'options'  => array(),
                    );
                }

                // Push pricing method to group
                $result[$group_key]['options'][$method_key] = $method['label'];
            }
        }

        return $result;
    }

    /**
     * Check if pricing method exists
     *
     * @access public
     * @param string $pricing_method
     * @return bool
     */
    public static function pricing_method_exists($pricing_method)
    {
        // Remove prefix
        $pricing_method = preg_replace('/^(fees_|advanced_fees_|discounts_)/i', '', $pricing_method);

        // Iterate over pricing methods
        foreach (self::get_pricing_methods() as $group_key => $group) {
            if (isset($group['children'][$pricing_method])) {
                return true;
            }
        }

        // Pricing method not found
        return false;
    }

    /**
     * Get adjusted product or product variation price
     *
     * @access public
     * @param float $price
     * @param int $product_id
     * @param int $variation_id
     * @param array $posted
     * @param int $quantity
     * @param bool $calculate_taxes
     * @param bool $rounded
     * @param object $product
     * @return float
     */
    public static function get_adjusted_price($price, $product_id, $variation_id = null, $posted = array(), $quantity = 1, $calculate_taxes = false, $rounded = true, $product = null)
    {
        $adjusted_price = $price;

        // Skip pricing adjustment based on various conditions
        if (WCCF_WC_Product::skip_pricing($product_id, $variation_id)) {
            return $adjusted_price;
        }

        // Adjust price by product properties
        $adjusted_price = self::get_price_adjusted_by_fields($adjusted_price, $product_id, $variation_id, 'product_prop', array(), 1, false, $rounded, $product);

        // Get adjusted price by product fields if product fields are not skipped
        if (!WCCF_WC_Product::skip_product_fields($product_id, $variation_id)) {
            $adjusted_price = self::get_price_adjusted_by_fields($adjusted_price, $product_id, $variation_id, 'product_field', $posted, $quantity, $calculate_taxes, $rounded, $product);
        }

        // Return adjusted price
        return (float) $adjusted_price;
    }

    /**
     * Get product price adjusted by either product fields or product properties
     *
     * This method will need to be updated if we add any variation-specific conditions (currently it does nothing with $variation_id). Or does it?
     *
     * @access private
     * @param float $price
     * @param int $product_id
     * @param int $variation_id
     * @param string $context
     * @param array $posted
     * @param int $quantity
     * @param bool $calculate_taxes
     * @param bool $rounded
     * @param object $product
     * @return float
     */
    private static function get_price_adjusted_by_fields($price, $product_id, $variation_id, $context, $posted = array(), $quantity = 1, $calculate_taxes = false, $rounded = true, $product = null)
    {
        // Get fields applicable to this product
        $all_fields = WCCF_Field_Controller::get_all_by_context($context);
        $fields = WCCF_Conditions::filter_fields($all_fields, array('item_id' => $product_id, 'child_id' => $variation_id));

        // Get default values for fields
        if ($context === 'product_field' && empty($posted)) {

            $default_values = array();

            foreach ($fields as $field) {
                if ($default_value = $field->get_default_value()) {
                    $default_values[$field->get_id()]['value'] = $default_value;
                }
            }
        }

        // To allow different product field configuration for multiple quantity units, we must come up with an average product price
        $adjusted_prices = array();

        // Track if quantity based fields were found
        $quantity_based_fields_found = false;

        // Iterate over quantity units which may have different configuration
        for ($i = 0; $i < $quantity; $i++) {

            $adjusted_price = $price;

            // Iterate over fields
            foreach ($fields as $field) {

                // Check product field frontend conditions if not data was provided
                // Note: this is designed to work with display price override functionality
                if ($context === 'product_field' && empty($posted)) {
                    if (!WCCF_Conditions::check_frontend_conditions($field, $fields, $default_values)) {
                        continue;
                    }
                }

                // Check if field has pricing
                if (!$field->has_pricing()) {
                    continue;
                }

                // Field is quantity based
                if ($field->is_quantity_based()) {
                    $quantity_based_fields_found = true;
                }

                // Get value to use
                if ($context === 'product_field') {
                    $value = self::get_product_field_value_for_price_adjustment($field, $product_id, $product, $posted, $i);
                }
                else if ($context === 'product_prop') {
                    $value = self::get_product_property_value_for_price_adjustment($field, $product_id, $product);
                }

                // Check if value was found
                if ($value === false) {
                    continue;
                }

                // Check if field has options
                if ($field->has_options()) {

                    // Ensure value is array
                    $value = (array) $value;

                    // Iterate over options
                    foreach ($field->get_options() as $option) {

                        // Check if current option is selected and has pricing
                        if (in_array($option['key'], $value) && isset($option['pricing_value'])) {

                            // Adjust price by current option
                            $adjusted_price = self::adjust_price($price, $adjusted_price, $product_id, $option['pricing_method'], $option['pricing_value'], $value, $calculate_taxes);
                        }
                    }
                }
                // Field does not have options but has non-empty value
                else if (!empty($value) || $value === '0') {

                    // Adjust price by field value
                    $adjusted_price = self::adjust_price($price, $adjusted_price, $product_id, $field->get_pricing_method(), $field->get_pricing_value(), $value, $calculate_taxes);
                }
            }

            // Add positive price to prices array
            $adjusted_prices[$i] = $adjusted_price ?: 0;

            // Do not continue if no quantity based fields were found
            if (!$quantity_based_fields_found) {
                break;
            }
        }

        // Come up with an average price if quantity based fields were found
        if ($quantity_based_fields_found) {
            $final_price = !empty($adjusted_prices) ? (array_sum($adjusted_prices) / count($adjusted_prices)) : 0;
        }
        // Simply use the first price otherwise
        else {
            $final_price = array_pop($adjusted_prices);
        }

        // Round price if needed
        $final_price = (float) ($rounded ? round($final_price, wc_get_price_decimals()) : $final_price);

        // Make sure price is not negative
        $final_price = $final_price > 0 ? $final_price : 0;

        return $final_price;
    }

    /**
     * Get product field value for price adjustment
     *
     * @access private
     * @param object $field
     * @param int $product_id
     * @param object $product
     * @param array $posted
     * @param int $quantity_index
     * @return mixed
     */
    private static function get_product_field_value_for_price_adjustment($field, $product_id, $product = null, $posted = array(), $quantity_index = null)
    {
        // Check if any values were posted
        if (!empty($posted)) {
            return $field->get_value_from_values_array($posted, $quantity_index);
        }

        // Check if default values should be considered
        if (WCCF_Settings::get('product_field_prices_include_default') && (!is_object($product) || empty($product->wccf_cart_item_product))) {
            return $field->get_default_value();
        }

        return false;
    }

    /**
     * Get product property value for price adjustment
     *
     * @access private
     * @param object $field
     * @param int $product_id
     * @param object $product
     * @return mixed
     */
    private static function get_product_property_value_for_price_adjustment($field, $product_id, $product = null)
    {
        // Check if default values should be considered
        if (WCCF_Settings::get('product_property_prices_include_default')) {
            return $field->get_final_value($product_id);
        }

        // Otherwise return stored value only
        return $field->get_stored_value($product_id);
    }

    /**
     * Apply pricing adjustment
     *
     * This method must allow price to temporarily get below zero since there
     * may be other pricing rules that bring the price back above zero
     *
     * @access public
     * @param float $original_price
     * @param float $price
     * @param int $product_id
     * @param string $pricing_method
     * @param float $pricing_value
     * @param mixed $field_value
     * @param bool $calculate_taxes
     * @return float
     */
    public static function adjust_price($original_price, $price, $product_id, $pricing_method, $pricing_value, $field_value, $calculate_taxes)
    {
        // Load product so we can access pricing functions
        $product =  wc_get_product($product_id);

        // Maybe tax-adjust price adjustment value
        if ($calculate_taxes) {
            $tax_adjusted_pricing_value = RightPress_Helper::wc_version_gte('3.0') ? wc_get_price_to_display($product, array('price' => $pricing_value)) : $product->get_display_price($pricing_value);
        }
        else {
            $tax_adjusted_pricing_value = $pricing_value;
        }

        // Get adjustment value
        $adjustment_value = self::get_adjustment_value($original_price, $price, $pricing_method, $tax_adjusted_pricing_value, $field_value);

        // Apply adjustment and return
        return (float) ($price + $adjustment_value);
    }

    /**
     * Get pricing adjustment value
     *
     * @access public
     * @param float $original_price
     * @param float $price_to_adjust
     * @param string $pricing_method
     * @param float $pricing_value
     * @param mixed $field_value
     * @return float
     */
    public static function get_adjustment_value($original_price, $price_to_adjust, $pricing_method, $pricing_value, $field_value)
    {
        // Apply any amount manipulations
        if (!self::pricing_method_is_percentage($pricing_method)) {
            $pricing_value = self::process_amount_manipulations($pricing_value);
        }

        // Allow developers to override pricing value setting, e.g. discount percentage or fixed fee
        $pricing_value = apply_filters('wccf_pricing_value', $pricing_value, $original_price, $price_to_adjust, $pricing_method, $pricing_value, $field_value);

        // Proceed depending on pricing method
        switch ($pricing_method) {

            case 'fees_fee':
                $adjustment_value = $pricing_value;
                break;

            case 'fees_percentage_fee':
                $adjustment_value = ($original_price * $pricing_value / 100);
                break;

            case 'fees_compound_percentage_fee':
                $adjustment_value = ($price_to_adjust * $pricing_value / 100);
                break;

            case 'advanced_fees_fee_per_character':
                $string_to_count = trim((string) $field_value);
                $string_to_count = WCCF_Settings::get('fee_per_character_includes_spaces') ? $string_to_count : preg_replace('/\s/', '', $string_to_count);
                $adjustment_value = ($pricing_value * strlen($string_to_count));
                break;

            case 'advanced_fees_fee_x_value':
                $adjustment_value = ($pricing_value * abs((float) $field_value));
                break;

            case 'discounts_discount':
                $adjustment_value = -$pricing_value;
                break;

            case 'discounts_percentage_discount':
                $adjustment_value = -($original_price * $pricing_value / 100);
                break;

            case 'discounts_compound_percentage_discount':
                $adjustment_value = -($price_to_adjust * $pricing_value / 100);
                break;

            default:
                $adjustment_value = 0;
                break;
        }

        // Allow developers to override calculated adjustment value, i.e. final adjustment amount that will be added or subtracted from the price
        return apply_filters('wccf_adjustment_value', (float) $adjustment_value, $original_price, $price_to_adjust, $pricing_method, $pricing_value, $field_value);
    }

    /**
     * Format and return pricing string
     *
     * @access public
     * @param string $pricing_method
     * @param float $pricing_value
     * @param bool $is_html
     * @param string $prepend
     * @param string $append
     * @return string
     */
    public static function get_pricing_string($pricing_method, $pricing_value, $is_html = false, $prepend = '', $append = '')
    {
        // Get WC config
        $price_format = get_woocommerce_price_format();
        $currency_symbol = get_woocommerce_currency_symbol();

        // Format percentage string
        if (self::pricing_method_is_percentage($pricing_method)) {
            $string = $pricing_value . '%';
        }
        else {

            // Apply any amount manipulations
            $pricing_value = self::process_amount_manipulations($pricing_value);

            // Format WC price string
            if (in_array($pricing_method, array('fees_fee', 'discounts_discount', 'advanced_fees_fee_per_character', 'advanced_fees_fee_x_value'), true)) {
                $pricing_value = number_format($pricing_value, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator());
                $string = sprintf($price_format, $currency_symbol, $pricing_value);
            }
            // Method unknown, just output value
            else {
                $string = $pricing_value;
            }
        }

        // Append operation character
        if (in_array($pricing_method, array('discounts_discount', 'discounts_percentage_discount', 'discounts_compound_percentage_discount'), true)) {
            $string = '-' . $string;
        }
        else if ($pricing_method === 'advanced_fees_fee_x_value') {
            $string = ($is_html ? '&times; ' : 'x ') . $string;
        }
        else {
            $string = '+' . $string;
        }

        // Suffix
        if ($pricing_method === 'advanced_fees_fee_per_character') {
            $string .= ' ' . __('per character', 'rp_wccf');
        }

        // Prepend and append strings
        $string = !empty($prepend) ? $prepend . $string : $string;
        $string = !empty($append) ? $string . $append : $string;

        // Wrap it up
        if ($is_html) {
            $string = ' <span class="wccf_price_label">' . $string . '</span>';
        }
        else {
            $string = ' ' . $string;
        }

        // Allow developers to override and return
        return apply_filters('wccf_addon_price', $string, $pricing_method, $pricing_value, $is_html);
    }

    /**
     * Get checkout extra fees from checkout fields
     *
     * @access public
     * @param float $cart_total
     * @param array $fields
     * @param array $posted
     * @return array
     */
    public static function get_checkout_fees($cart_total, $fields = array(), $posted = array())
    {
        $fees = array();

        // Iterate over fields
        foreach ($fields as $field) {

            // Check if field has pricing
            if (!$field->has_pricing()) {
                continue;
            }

            // Get posted field value
            $value = $field->get_value_from_values_array($posted);

            // Check if value was found
            if ($value === false) {
                continue;
            }

            // Allow developers to skip adding this fee
            if (apply_filters('wccf_skip_checkout_fee', false, $field, $value)) {
                continue;
            }

            // Reset fee amount
            $fee_amount = 0;

            // Check if field has options
            if ($field->has_options()) {

                // Ensure value is array
                $value = (array) $value;

                // Iterate over options
                foreach ($field->get_options() as $option) {

                    // Check if current option is selected and has pricing
                    if (in_array($option['key'], $value, true) && isset($option['pricing_value'])) {

                        // Add fees of all selected options
                        $fee_amount += self::get_adjustment_value($cart_total, $cart_total, $option['pricing_method'], $option['pricing_value'], $value);
                    }
                }
            }
            // Field does not have options but has non-empty value
            else if (!RightPress_Helper::is_empty($value)) {

                // Get fee amount
                $fee_amount = self::get_adjustment_value($cart_total, $cart_total, $field->get_pricing_method(), $field->get_pricing_value(), $value);
            }

            // Add fee to fees array
            if ((float) $fee_amount !== (float) 0) {

                // Get fee tax class
                $tax_class = $field->get_tax_class();
                $tax_class = ($tax_class !== 'wccf_not_taxable' ? $tax_class : null);

                // Add to fees array
                $fees[] = array(
                    'label'     => apply_filters('wccf_checkout_fee_label', $field->get_label(), $field, $value, $fee_amount),
                    'amount'    => apply_filters('wccf_checkout_fee_amount', $fee_amount, $field, $value),
                    'tax_class' => apply_filters('wccf_checkout_fee_tax_class', $tax_class, $field, $value),
                    'field_id'  => $field->get_id(),
                );
            }
        }

        // Optionally display one fee for all selections
        if (!empty($fees) && WCCF_Settings::get('display_as_single_fee')) {

            // Reset fee amount
            $fee_amount = 0;

            // Get tax class of first fee (multiple tax classes are not supported in this case)
            $tax_class = false;

            // Iterate over fees
            foreach ($fees as $fee) {

                // Get tax class
                if ($tax_class === false) {
                    $tax_class = $fee['tax_class'];
                }

                // Add fee amount
                $fee_amount += $fee['amount'];
            }

            // Reset fees array
            $fees = array(array(
                'label'     => apply_filters('wccf_checkout_fee_label', WCCF_Settings::get('single_fee_label'), null, null, $fee_amount),
                'amount'    => apply_filters('wccf_checkout_fee_amount', $fee_amount, null, null),
                'tax_class' => apply_filters('wccf_checkout_fee_tax_class', $tax_class, null, null),
            ));
        }

        return $fees;
    }

    /**
     * Process any amount manipulations
     *
     * @access public
     * @param float $amount
     * @return float
     */
    public static function process_amount_manipulations($amount)
    {
        // Allow currency switcher extensions to convert amount to a different currency
        $amount = RightPress_Helper::get_amount_in_currency($amount);

        // Allow developers to convert amount programmatically
        $amount = apply_filters('wccf_pricing_amount', $amount);

        // Return possibly manipulated amount
        return (float) $amount;
    }

    /**
     * Check if pricing method is percentage
     *
     * @access public
     * @param string $pricing_method
     * @return bool
     */
    public static function pricing_method_is_percentage($pricing_method)
    {
        return in_array($pricing_method, array('fees_percentage_fee', 'fees_compound_percentage_fee', 'discounts_percentage_discount', 'discounts_compound_percentage_discount'), true);
    }

    /**
     * Allow more spaces to fix totals for quantity based price adjusting fields
     *
     * @access public
     * @param float $price
     * @param int $quantity
     * @return float
     */
    public static function fix_quantity_based_fields_product_price($price, $quantity = 1)
    {
        $price = (float) $price;

        // Check how many decimals we need to print
        $decimals = WCCF_Pricing::get_required_decimals_to_fix_price($price, $quantity);

        // Round and return
        return round($price, $decimals);
    }

    /**
     * Get required decimals to fix price
     *
     * @access public
     * @param float $price
     * @param int $quantity
     * @return int
     */
    public static function get_required_decimals_to_fix_price($price, $quantity = 1)
    {
        $price = (float) $price;

        // Get WooCommerce decimals value
        $wc_price_decimals = (int) wc_get_price_decimals();

        // No decimal part, keep default value
        if ($price === round($price)) {
            return $wc_price_decimals;
        }

        // Check how many decimals there are in price
        $decimals_in_price = strlen((string) ($price - floor($price))) - 2;

        // Whole decimals part fits under WooCommerce decimals value, keep default value
        if ($decimals_in_price <= $wc_price_decimals) {
            return $wc_price_decimals;
        }

        // Check how many decimals we need to display
        $quantity_length = strlen((string) $quantity);
        $decimals = ($quantity_length > $wc_price_decimals ? $quantity_length : $wc_price_decimals) + 1;

        return (int) $decimals;
    }



}
}
