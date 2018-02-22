<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to Custom Field Conditions
 *
 * @class WCCF_Conditions
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Conditions')) {

class WCCF_Conditions
{
    // Define conditions
    private static $conditions = null;

    // Define condition methods
    private static $methods = null;

    // Define multiselect field keys
    private static $multiselect_field_keys = array(
        'roles', 'capabilities', 'products', 'product_variations',
        'product_attributes', 'product_categories', 'product_types',
        'payment_methods', 'shipping_methods', 'coupons',
    );

    /**
     * Define and return conditions
     *
     * @access public
     * @return array
     */
    public static function get_conditions()
    {
        // Define conditions
        if (empty(self::$conditions)) {
            self::$conditions = array(

                // Customer
                'customer' => array(
                    'label'     => __('Customer', 'rp_wccf'),
                    'children'  => array(

                        // Is logged in
                        'is_logged_in' => array(
                            'label'         => __('Is logged in', 'rp_wccf'),
                            'method'        => 'yes_no',
                            'display'       => array('product_field', 'checkout_field'),
                            'uses_fields'   => array(),
                        ),

                        // Role
                        'role' => array(
                            'label'         => __('Role', 'rp_wccf'),
                            'method'        => 'in_list_not_in_list',
                            'display'       => array('product_field', 'checkout_field'),
                            'uses_fields'   => array('roles'),
                        ),

                        // Capability
                        'capability' => array(
                            'label'         => __('Capability', 'rp_wccf'),
                            'method'        => 'in_list_not_in_list',
                            'display'       => array('product_field', 'checkout_field'),
                            'uses_fields'   => array('capabilities'),
                        ),

                        // Role
                        'order_role' => array(
                            'label'         => __('Role', 'rp_wccf'),
                            'method'        => 'in_list_not_in_list',
                            'display'       => array('order_field'),
                            'uses_fields'   => array('roles'),
                        ),

                        // Capability from order
                        'order_capability' => array(
                            'label'         => __('Capability', 'rp_wccf'),
                            'method'        => 'in_list_not_in_list',
                            'display'       => array('order_field'),
                            'uses_fields'   => array('capabilities'),
                        ),
                    ),
                ),

                // Product
                'product' => array(
                    'label'     => __('Product', 'rp_wccf'),
                    'children'  => array(

                        // Product
                        'product' => array(
                            'label'         => __('Product', 'rp_wccf'),
                            'method'        => 'in_list_not_in_list',
                            'display'       => array('product_field', 'product_prop'),
                            'uses_fields'   => array('products'),
                        ),

                        // Product variation
                        'product_variation' => array(
                            'label'         => __('Product variation', 'rp_wccf'),
                            'method'        => 'in_list_not_in_list',
                            'display'       => array('product_field'),
                            'uses_fields'   => array('product_variations'),
                        ),

                        // Product attributes
                        'product_attributes' => array(
                            'label'         => __('Product attributes', 'rp_wccf'),
                            'method'        => 'at_least_one_all_none',
                            'display'       => array('product_field'),
                            'uses_fields'   => array('product_attributes'),
                        ),

                        // Product category
                        'product_category' => array(
                            'label'         => __('Product category', 'rp_wccf'),
                            'method'        => 'in_list_not_in_list',
                            'display'       => array('product_field', 'product_prop'),
                            'uses_fields'   => array('product_categories'),
                        ),

                        // Product type
                        'product_type' => array(
                            'label'         => __('Product type', 'rp_wccf'),
                            'method'        => 'in_list_not_in_list',
                            'display'       => array('product_field', 'product_prop'),
                            'uses_fields'   => array('product_types'),
                        ),
                    ),
                ),

                // Cart
                'cart' => array(
                    'label'     => __('Cart', 'rp_wccf'),
                    'children'  => array(

                        // Subtotal
                        'subtotal' => array(
                            'label'         => __('Cart subtotal', 'rp_wccf'),
                            'method'        => 'at_least_less_than',
                            'display'       => array('checkout_field'),
                            'uses_fields'   => array('decimal'),
                        ),

                        // Coupons
                        'coupons' => array(
                            'label'         => __('Coupons applied', 'rp_wccf'),
                            'method'        => 'at_least_one_all_none',
                            'display'       => array('checkout_field'),
                            'uses_fields'   => array('coupons'),
                        ),
                    ),
                ),

                // Cart Items
                'cart_items' => array(
                    'label'     => __('Cart Items', 'rp_wccf'),
                    'children'  => array(

                        // Products in cart
                        'products_in_cart' => array(
                            'label'         => __('Products in cart', 'rp_wccf'),
                            'method'        => 'at_least_one_all_none',
                            'display'       => array('checkout_field'),
                            'uses_fields'   => array('products'),
                        ),

                        // Product variations in cart
                        'product_variations_in_cart' => array(
                            'label'         => __('Product variations in cart', 'rp_wccf'),
                            'method'        => 'at_least_one_all_none',
                            'display'       => array('checkout_field'),
                            'uses_fields'   => array('product_variations'),
                        ),

                        // Product attributes in cart
                        'product_attributes_in_cart' => array(
                            'label'         => __('Product attributes in cart', 'rp_wccf'),
                            'method'        => 'at_least_one_all_none',
                            'display'       => array('checkout_field'),
                            'uses_fields'   => array('product_attributes'),
                        ),

                        // Product categories in cart
                        'product_categories_in_cart' => array(
                            'label'         => __('Product categories in cart', 'rp_wccf'),
                            'method'        => 'at_least_one_all_none',
                            'display'       => array('checkout_field'),
                            'uses_fields'   => array('product_categories'),
                        ),
                    ),
                ),

                // Order
                'order' => array(
                    'label'     => __('Order', 'rp_wccf'),
                    'children'  => array(

                        // Total
                        'total' => array(
                            'label'         => __('Order total', 'rp_wccf'),
                            'method'        => 'at_least_less_than',
                            'display'       => array('order_field'),
                            'uses_fields'   => array('decimal'),
                        ),

                        // Coupons
                        'coupons' => array(
                            'label'         => __('Coupons applied', 'rp_wccf'),
                            'method'        => 'at_least_one_all_none',
                            'display'       => array('order_field'),
                            'uses_fields'   => array('coupons'),
                        ),

                        // Payment method
                        'payment_method' => array(
                            'label'         => __('Payment method', 'rp_wccf'),
                            'method'        => 'in_list_not_in_list',
                            'display'       => array('order_field'),
                            'uses_fields'   => array('payment_methods'),
                        ),

                        // Shipping method
                        'shipping_method' => array(
                            'label'         => __('Shipping method', 'rp_wccf'),
                            'method'        => 'in_list_not_in_list',
                            'display'       => array('order_field'),
                            'uses_fields'   => array('shipping_methods'),
                        ),
                    ),
                ),

                // Order Items
                'order_items' => array(
                    'label'     => __('Order Items', 'rp_wccf'),
                    'children'  => array(

                        // Products in order
                        'products_in_order' => array(
                            'label'         => __('Products in order', 'rp_wccf'),
                            'method'        => 'at_least_one_all_none',
                            'display'       => array('order_field'),
                            'uses_fields'   => array('products'),
                        ),

                        // Product variations in order
                        'product_variations_in_order' => array(
                            'label'         => __('Product variations in order', 'rp_wccf'),
                            'method'        => 'at_least_one_all_none',
                            'display'       => array('order_field'),
                            'uses_fields'   => array('product_variations'),
                        ),

                        // Product attributes in order
                        'product_attributes_in_order' => array(
                            'label'         => __('Product attributes in order', 'rp_wccf'),
                            'method'        => 'at_least_one_all_none',
                            'display'       => array('order_field'),
                            'uses_fields'   => array('product_attributes'),
                        ),

                        // Product categories in order
                        'product_categories_in_order' => array(
                            'label'         => __('Product categories in order', 'rp_wccf'),
                            'method'        => 'at_least_one_all_none',
                            'display'       => array('order_field'),
                            'uses_fields'   => array('product_categories'),
                        ),
                    ),
                ),

                // Custom Field
                'custom_field' => array(
                    'label'     => __('Custom Fields', 'rp_wccf'),
                    'children'  => array(

                        // Other custom field
                        'other_custom_field' => array(
                            'label'         => __('Other Field', 'rp_wccf'),
                            'method'        => 'other_custom_field',
                            'display'       => array('product_field', 'product_prop', 'checkout_field', 'order_field', 'user_field'),
                            'uses_fields'   => array('other_field_id', 'text'),
                        ),
                    ),
                ),
            );
        }

        // Return conditions
        return self::$conditions;
    }

    /**
     * Define and return condition methods
     *
     * @access public
     * @return array
     */
    public static function get_methods()
    {
        // Define conditions
        if (empty(self::$methods)) {
            self::$methods = array(

                // yes, no
                'yes_no' => array(
                    'yes'   => __('yes', 'rp_wccf'),
                    'no'    => __('no', 'rp_wccf'),
                ),

                // in list, not in list
                'in_list_not_in_list' => array(
                    'in_list'       => __('in list', 'rp_wccf'),
                    'not_in_list'   => __('not in list', 'rp_wccf'),
                ),

                // at least, less than
                'at_least_less_than' => array(
                    'at_least'  => __('at least', 'rp_wccf'),
                    'less_than' => __('less than', 'rp_wccf'),
                ),

                // at least one, all, none
                'at_least_one_all_none' => array(
                    'at_least_one'  => __('at least one of selected', 'rp_wccf'),
                    'all'           => __('all of selected', 'rp_wccf'),
                    'none'          => __('none of selected', 'rp_wccf'),
                ),

                // is empty, is not empty, contains, does not contain, equals, does not equal etc
                'other_custom_field' => array(
                    'is_empty'          => __('is empty', 'rp_wccf'),
                    'is_not_empty'      => __('is not empty', 'rp_wccf'),
                    'contains'          => __('contains', 'rp_wccf'),
                    'does_not_contain'  => __('does not contain', 'rp_wccf'),
                    'equals'            => __('equals', 'rp_wccf'),
                    'does_not_equal'    => __('does not equal', 'rp_wccf'),
                    'less_than'         => __('less than', 'rp_wccf'),
                    'less_or_equal_to'  => __('less or equal to', 'rp_wccf'),
                    'more_than'         => __('more than', 'rp_wccf'),
                    'more_or_equal'     => __('more or equal to', 'rp_wccf'),
                    'is_checked'        => __('is checked', 'rp_wccf'),
                    'is_not_checked'    => __('is not checked', 'rp_wccf'),
                ),
            );
        }

        // Return methods
        return self::$methods;
    }

    /**
     * Return conditions for display in admin ui
     *
     * @access public
     * @param string $context
     * @return array
     */
    public static function get_conditions_list($context)
    {
        $result = array();

        // Iterate over all conditions groups
        foreach (self::get_conditions() as $group_key => $group) {

            // Iterate over conditions
            foreach ($group['children'] as $condition_key => $condition) {

                // Skip current condition if it's not usable in current context
                if (!in_array($context, $condition['display'])) {
                    continue;
                }

                // Add group if needed
                if (!isset($result[$group_key])) {
                    $result[$group_key] = array(
                        'label'     => $group['label'],
                        'options'  => array(),
                    );
                }

                // Push condition to group
                $result[$group_key]['options'][$condition_key] = $condition['label'];
            }
        }

        return $result;
    }

    /**
     * Return methods of particular condition for display in admin ui
     *
     * @access public
     * @param string $group
     * @param string $condition
     * @return array
     */
    public static function get_methods_list($group, $condition)
    {
        // Get all conditions and methods
        $conditions = self::get_conditions();
        $methods = self::get_methods();

        // Get method key
        $method_key = $conditions[$group]['children'][$condition]['method'];

        // Pick methods by group and condition
        return isset($methods[$method_key]) ? $methods[$method_key] : array();
    }

    /**
     * Check if condition uses field
     *
     * @access public
     * @param string $group
     * @param string $condition
     * @param string $field
     * @return bool
     */
    public static function uses_field($group, $condition, $field)
    {
        // Get all conditions
        $conditions = self::get_conditions();

        // Check if condition uses field
        return in_array($field, $conditions[$group]['children'][$condition]['uses_fields']);
    }

    /**
     * Get field size
     *
     * @access public
     * @param string $group
     * @param string $condition
     * @return string
     */
    public static function field_size($group, $condition)
    {
        // Special case for custom_field_other_custom_field (width changed dynamically via JS)
        if ($group == 'custom_field' && $condition == 'other_custom_field') {
            return 'double';
        }

        // Get all conditions
        $conditions = self::get_conditions();

        // All other cases
        switch (count($conditions[$group]['children'][$condition]['uses_fields'])) {
            case 2:
                return 'single';
            case 1:
                return 'double';
            default:
                return 'triple';
        }
    }

    /**
     * Check if conditions field is multiselect
     *
     * @access public
     * @param string $field_key
     * @return bool
     */
    public static function field_is_multiselect($field_key)
    {
        $multiselect_fields = self::get_multiselect_field_keys();
        return in_array($field_key, $multiselect_fields);
    }

    /**
     * Get multiselect field keys
     *
     * @access public
     * @return array
     */
    public static function get_multiselect_field_keys()
    {
        return self::$multiselect_field_keys;
    }

    /**
     * Get condition group and option from group_option string
     *
     * @access public
     * @param string $group_and_option
     * @return mixed
     */
    public static function extract_group_and_option($group_and_option)
    {
        $group_key = null;

        foreach (self::get_conditions() as $potential_group_key => $potential_group) {
            if (strpos($group_and_option, $potential_group_key) === 0) {
                $group_key = $potential_group_key;
            }
        }

        if ($group_key === null) {
            return false;
        }

        $option_key = preg_replace('/^' . $group_key . '_/i', '', $group_and_option);

        return array($group_key, $option_key);
    }

    /**
     * Load items for multiselect fields based on search criteria and item type
     *
     * @access public
     * @param string $type
     * @param string $query
     * @param array $selected
     * @return array
     */
    public static function get_items($type, $query, $selected)
    {
        $items = array();

        // Get items by type
        $method = 'get_' . $type;
        $all_items = self::$method($selected);

        // Iterate over returned items
        foreach ($all_items as $item_key => $item) {

            // Filter items that match search criteria
            if (RightPress_Helper::string_contains_phrase($item['text'], $query)) {

                // Filter items that are not yet selected
                if (empty($selected) || !in_array($item['id'], $selected)) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * Load already selected multiselect field items by their ids
     *
     * @access public
     * @param string $type
     * @param array $ids
     * @return array
     */
    public static function get_items_by_ids($type, $ids = array())
    {
        $method = 'get_' . $type;
        return self::$method(array(), $ids);
    }

    /**
     * Load roles for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public static function get_roles($selected, $ids = array())
    {
        $items = array();

        // Get roles
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        // Iterate over roles and format results array
        foreach ($wp_roles->get_names() as $role_key => $role) {

            // Skip this item if we don't need it
            if (!empty($ids) && !in_array($role_key, $ids)) {
                continue;
            }

            // Add item
            $items[] = array(
                'id'    => $role_key,
                'text'  => $role . ' (' . $role_key . ')',
            );
        }

        return $items;
    }

    /**
     * Load capabilities for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public static function get_capabilities($selected, $ids = array())
    {
        $items = array();

        // Groups plugin active?
        if (class_exists('Groups_User') && class_exists('Groups_Wordpress') && function_exists('_groups_get_tablename')) {

            global $wpdb;
            $capability_table = _groups_get_tablename('capability');
            $all_capabilities = $wpdb->get_results('SELECT capability FROM ' . $capability_table);

            if ($all_capabilities) {
                foreach ($all_capabilities as $capability) {

                    // Skip this item if we don't need it
                    if (!empty($ids) && !in_array($capability, $ids)) {
                        continue;
                    }

                    // Add item
                    $items[] = array(
                        'id'    => $capability->capability,
                        'text'  => $capability->capability
                    );
                }
            }
        }

        // Get standard WP capabilities
        else {
            global $wp_roles;

            if (!isset($wp_roles)) {
                get_role('administrator');
            }

            $roles = $wp_roles->roles;

            $already_added = array();

            if (is_array($roles)) {
                foreach ($roles as $rolename => $atts) {
                    if (isset($atts['capabilities']) && is_array($atts['capabilities'])) {
                        foreach ($atts['capabilities'] as $capability => $value) {
                            if (!in_array($capability, $already_added)) {

                                // Skip this item if we don't need it
                                if (!empty($ids) && !in_array($capability, $ids)) {
                                    continue;
                                }

                                // Add item
                                $items[] = array(
                                    'id'    => $capability,
                                    'text'  => $capability
                                );
                                $already_added[] = $capability;
                            }
                        }
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Load products for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public static function get_products($selected, $ids = array())
    {
        // WC31: Products will no longer be posts

        $items = array();

        // Get all product ids
        $args = array(
            'posts_per_page'    => -1,
            'post_type'         => 'product',
            'post_status'       => array('publish', 'pending', 'draft', 'future', 'private', 'inherit'),
            'fields'            => 'ids',
        );

        if (!empty($ids)) {
            $args['post__in'] = $ids;
        }

        $posts_raw = get_posts($args);

        // Format results array
        foreach ($posts_raw as $post_id) {
            $items[] = array(
                'id'    => $post_id,
                'text'  => '#' . $post_id . ' ' . get_the_title($post_id)
            );
        }

        return $items;
    }

    /**
     * Load product variations for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public static function get_product_variations($selected, $ids = array())
    {
        // WC31: Products will no longer be posts

        $items = array();

        // Get all product variation ids
        $args = array(
            'posts_per_page'    => -1,
            'post_type'         => 'product_variation',
            'post_status'       => array('publish', 'pending', 'draft', 'future', 'private', 'inherit'),
            'fields'            => 'ids',
        );

        if (!empty($ids)) {
            $args['post__in'] = $ids;
        }

        $posts_raw = get_posts($args);

        // Format results array
        foreach ($posts_raw as $post_id) {

            // Check parent
            // WC31: products will no longer be posts
            if ($parent_id = wp_get_post_parent_id($post_id)) {
                if (RightPress_Helper::post_exists($parent_id)) {

                    // Load product variation
                    $product = wc_get_product($post_id);

                    // Get list of variation attributes
                    $attributes = $product->get_variation_attributes();

                    // Change empty values
                    foreach ($attributes as $attribute_key => $attribute) {
                        if ($attribute === '') {
                            $attributes[$attribute_key] = __('Any', 'rp_wccf') . ' ' .  wc_attribute_label(str_replace('attribute_', '', $attribute_key));
                        }
                    }

                    // Join attributes
                    $attributes = join(', ', $attributes);
                    $attributes = RightPress_Helper::shorten_text($attributes, 25);

                    // Add variation
                    $items[] = array(
                        'id'    => (string) $post_id,
                        'text'  => '#' . $post_id . ' ' . get_the_title($parent_id) . ' (' . $attributes . ')',
                    );
                }
            }
        }

        return $items;
    }

    /**
     * Load product attributes for multiselect fields based on search criteria
     *
     * WC31: Check if this still works correctly after WC products are no longer WP posts
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public static function get_product_attributes($selected, $ids = array())
    {
        $items = array();
        global $wc_product_attributes;

        // Iterate over product attributes
        foreach ($wc_product_attributes as $attribute_key => $attribute) {

            $attribute_name = !empty($attribute->attribute_label) ? $attribute->attribute_label : $attribute->attribute_name;

            $subitems = array();

            $children_raw = get_terms(array($attribute_key), array('hide_empty' => 0));
            $children_raw_count = count($children_raw);

            foreach ($children_raw as $child_key => $child) {
                $child_name = $child->name;

                if ($child->parent) {
                    $parent_id = $child->parent;
                    $has_parent = true;

                    // Make sure we don't have an infinite loop here
                    $found = false;
                    $i = 0;

                    while ($has_parent && ($i < $children_raw_count || $found)) {

                        // Reset each time
                        $found = false;
                        $i = 0;

                        foreach ($children_raw as $parent_child_key => $parent_child) {

                            $i++;

                            if ($parent_child->term_id == $parent_id) {
                                $child_name = $parent_child->name . ' → ' . $child_name;
                                $found = true;

                                if ($parent_child->parent) {
                                    $parent_id = $parent_child->parent;
                                }
                                else {
                                    $has_parent = false;
                                }

                                break;
                            }
                        }
                    }
                }

                // Skip this item if we don't need it
                if (!empty($ids) && !in_array($child->term_id, $ids)) {
                    continue;
                }

                // Add item
                $subitems[] = array(
                    'id'    => $child->term_id,
                    'text'  => $child_name
                );
            }

            // Iterate over subitems and make a list of item/subitem pairs
            foreach ($subitems as $subitem) {
                $items[] = array(
                    'id'    => $subitem['id'],
                    'text'  => $attribute_name . ': ' . $subitem['text'],
                );
            }
        }

        return $items;
    }

    /**
     * Load product categories for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public static function get_product_categories($selected, $ids = array())
    {
        // WC31: Check if product categories are still post terms

        $items = array();

        $post_categories_raw = get_terms(array('product_cat'), array('hide_empty' => 0));
        $post_categories_raw_count = count($post_categories_raw);

        foreach ($post_categories_raw as $post_cat_key => $post_cat) {
            $category_name = $post_cat->name;

            if ($post_cat->parent) {
                $parent_id = $post_cat->parent;
                $has_parent = true;

                // Make sure we don't have an infinite loop here (happens with some kind of "ghost" categories)
                $found = false;
                $i = 0;

                while ($has_parent && ($i < $post_categories_raw_count || $found)) {

                    // Reset each time
                    $found = false;
                    $i = 0;

                    foreach ($post_categories_raw as $parent_post_cat_key => $parent_post_cat) {

                        $i++;

                        if ($parent_post_cat->term_id == $parent_id) {
                            $category_name = $parent_post_cat->name . ' → ' . $category_name;
                            $found = true;

                            if ($parent_post_cat->parent) {
                                $parent_id = $parent_post_cat->parent;
                            }
                            else {
                                $has_parent = false;
                            }

                            break;
                        }
                    }
                }
            }

            // Skip this item if we don't need it
            if (!empty($ids) && !in_array($post_cat->term_id, $ids)) {
                continue;
            }

            // Add item
            $items[] = array(
                'id'    => $post_cat->term_id,
                'text'  => $category_name
            );
        }

        return $items;
    }

    /**
     * Load product types for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public static function get_product_types($selected, $ids = array())
    {
        $items = array();

        // Fetch data
        foreach (WCCF_WC_Product::get_product_types() as $type_key => $type) {

            // Skip this item if we don't need it
            if (!empty($ids) && !in_array($type_key, $ids)) {
                continue;
            }

            // Add item
            $items[] = array(
                'id'    => $type_key,
                'text'  => $type . ' (' . $type_key . ')',
            );
        }

        return $items;
    }

    /**
     * Load payment methods for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public static function get_payment_methods($selected, $ids = array())
    {
        $items = array();

        // Fetch data
        foreach (WC()->payment_gateways->payment_gateways() as $gateway) {

            // Skip this item if we don't need it
            if (!empty($ids) && !in_array($gateway->id, $ids)) {
                continue;
            }

            // Add item
            $items[] = array(
                'id'    => $gateway->id,
                'text'  => $gateway->get_title() . ' (' . $gateway->id . ')',
            );
        }

        return $items;
    }

    /**
     * Load shipping methods for multiselect fields based on search criteria
     *
     * @access public
     * @param array $selected
     * @param array $ids
     * @return array
     */
    public static function get_shipping_methods($selected, $ids = array())
    {
        $items = array();

        // Fetch data
        foreach (WC()->shipping->load_shipping_methods() as $method) {

            // Skip this item if we don't need it
            if (!empty($ids) && !in_array($method->id, $ids)) {
                continue;
            }

            // Add item
            $items[] = array(
                'id'    => $method->id,
                'text'  => RightPress_WC_Legacy::shipping_method_get_method_title($method) . ' (' . $method->id . ')',
            );
        }

        return $items;
    }

    /**
     * Load coupons for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @return array
     */
    public static function get_coupons($selected, $ids = array())
    {
        $items = array();

        // WC31: Coupons will no longer be posts

        // Get all coupon ids
        $args = array(
            'posts_per_page'    => -1,
            'post_type'         => 'shop_coupon',
            'post_status'       => array('publish'),
            'fields'            => 'ids',
        );

        // Specific coupons requested
        if (!empty($ids)) {
            $args['post__in'] = $ids;
        }

        $posts_raw = get_posts($args);

        // Format results array
        foreach ($posts_raw as $post_id) {
            $items[] = array(
                'id'    => $post_id,
                'text'  => get_the_title($post_id)
            );
        }

        return $items;
    }

    /**
     * Filter out fields that do not match conditions
     * Also determines conditions that need to be passed to Javascript
     *
     * @access public
     * @param array $all_fields
     * @param array $params
     * @param mixed $checkout_position
     * @param bool $first_only
     * @return array
     */
    public static function filter_fields($all_fields, $params = array(), $checkout_position = null, $first_only = false)
    {
        $fields = array();

        // Iterate over passed fields
        foreach ($all_fields as $field_id => $field) {

            // Check if we are on Checkout page
            if ($checkout_position !== null) {

                // Check if this is a correct spot for this Checkout field
                if ($checkout_position !== $field->get_position()) {
                    continue;
                }
            }

            // Track if we need to add this field
            $is_ok = true;

            // Iterate over conditions
            foreach ($field->get_conditions() as $condition_key => $condition) {

                // Skip frontend conditions
                if ($condition['type'] === 'custom_field_other_custom_field') {
                    continue;
                }

                // Check if condition is matched
                if (!WCCF_Conditions::condition_is_matched(array_merge($params, array('condition' => $condition)))) {
                    $is_ok = false;
                    break;
                }
            }

            // Maybe add this field to a set of fields for return
            if ($is_ok) {

                // Add to fields array
                $fields[$field_id] = $field;

                // Return first matched field
                if ($first_only) {
                    return $fields;
                }
            }
        }

        return $fields;
    }

    /**
     * Check frontend conditions from submitted field data
     *
     * @access public
     * @param object $field
     * @param array $fields
     * @param array $values
     * @return bool
     */
    public static function check_frontend_conditions($field, $fields, $values)
    {
        // Iterate over conditions
        foreach($field->get_conditions() as $condition) {

            // Other custom field
            if ($condition['type'] === 'custom_field_other_custom_field') {
                if (!self::check_frontend_condition_other_custom_field($condition, $fields, $values)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if frontend condition Other Custom Field is matched
     *
     * @access public
     * @param array $condition
     * @param array $fields
     * @param array $values
     * @return bool
     */
    public static function check_frontend_condition_other_custom_field($condition, $fields, $values)
    {
        // Get condition method
        $condition_method = $condition['custom_field_other_custom_field_method'];

        // Iterate over fields
        foreach ($fields as $field) {

            // Do we need to check condition against current field
            if ((int) $condition['other_field_id'] !== $field->get_id()) {
                continue;
            }

            // Get field value
            if ($value = $field->get_value_from_values_array($values)) {

                // Proceed depending on condition method
                switch ($condition_method) {

                    // Is Empty
                    case 'is_empty':
                        return self::is_empty($value);

                    // Is Not Empty
                    case 'is_not_empty':
                        return !self::is_empty($value);

                    // Contains
                    case 'contains':
                        return self::contains($value, $condition['text']);

                    // Does Not Contain
                    case 'does_not_contain':
                        return !self::contains($value, $condition['text']);

                    // Equals
                    case 'equals':
                        return self::equals($value, $condition['text']);

                    // Does Not Equal
                    case 'does_not_equal':
                        return !self::equals($value, $condition['text']);

                    // Less Than
                    case 'less_than':
                        return self::less_than($value, $condition['text']);

                    // Less Or Equal To
                    case 'less_or_equal_to':
                        return !self::more_than($value, $condition['text']);

                    // More Than
                    case 'more_than':
                        return self::more_than($value, $condition['text']);

                    // More Or Equal
                    case 'more_or_equal':
                        return !self::less_than($value, $condition['text']);

                    // Is Checked
                    case 'is_checked':
                        return self::is_checked($value);

                    // Is Not Checked
                    case 'is_not_checked':
                        return !self::is_checked($value);

                    default:
                        return false;
                }
            }
            else {
                break;
            }
        }

        // Target field or its value does not exist - return value depends on whether condition method is positive or negative
        return in_array($condition_method, array('is_empty', 'does_not_contain', 'does_not_equal', 'more_than', 'is_not_checked'));
    }

    /**
     * Check if a single condition is matched
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_is_matched($params)
    {
        $method = 'condition_check_' . $params['condition']['type'];
        return self::$method($params);
    }

    /**
     * Condition check: Customer is logged in
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_customer_is_logged_in($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition')));
        return $condition['customer_is_logged_in_method'] === 'no' ? !is_user_logged_in() : is_user_logged_in();
    }

    /**
     * Condition check: Customer role
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_customer_role($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition')));
        return self::compare_in_list_not_in_list($condition['customer_role_method'], RightPress_Helper::current_user_roles(), $condition['roles']);
    }

    /**
     * Condition check: Customer capability
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_customer_capability($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition')));
        return self::compare_in_list_not_in_list($condition['customer_capability_method'], RightPress_Helper::current_user_capabilities(), $condition['capabilities']);
    }

    /**
     * Condition check: Product product
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_product_product($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id')));

        // Get product id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_product_id();

        // Check if product id is set
        if (!$item_id) {
            return false;
        }

        // Check condition
        return self::compare_in_list_not_in_list($condition['product_product_method'], $item_id, $condition['products']);
    }

    /**
     * Condition check: Product product variation
     *
     * Variation id must be passed here as there's no way to figure it out by ourselves
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_product_product_variation($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id', 'child_id')));

        // Check if variation id is set
        if (empty($child_id)) {
            return false;
        }

        // Check condition
        return self::compare_in_list_not_in_list($condition['product_product_variation_method'], $child_id, $condition['product_variations']);
    }

    /**
     * Condition check: Product product attributes
     *
     * WC31: Check if this still works correctly
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_product_product_attributes($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id', 'child_id', 'variation_attributes')));

        $attributes = array();

        // Get attributes from selected variation
        if (!empty($child_id)) {

            // Get variation
            $variation = wc_get_product($child_id);

            // Check if variation object was loaded
            if ($variation) {

                // Iterate over variation attributes
                foreach ($variation->get_variation_attributes() as $attribute_key => $attribute_slug) {

                    // Get clean attribute key
                    $clean_attribute_key = str_replace('attribute_', '', $attribute_key);

                    // Check maybe we have value in params for this attribute
                    if ($attribute_slug === '' && !empty($variation_attributes) && is_array($variation_attributes) && !empty($variation_attributes[$clean_attribute_key])) {
                        $attribute_slug = $variation_attributes[$clean_attribute_key];
                    }

                    // Attribute value is selected
                    if ($attribute_slug !== '') {

                        // Get attribute term
                        if ($term = get_term_by('slug', $attribute_slug, $clean_attribute_key)) {

                            // Add term id to list
                            if (!in_array($term->term_id, $attributes, true)) {
                                $attributes[] = $term->term_id;
                            }
                        }
                    }
                    // If $attribute_slug is empty string, then this means "any term" and we need to load all terms for a given attribute
                    else {

                        // Get all variation attributes of this variable product
                        $variation_parent = RightPress_WC_Legacy::product_variation_get_parent($variation);
                        $all_variation_attributes = $variation_parent->get_variation_attributes();

                        // Add all terms under current attribute
                        if (!empty($all_variation_attributes[$clean_attribute_key])) {
                            foreach ($all_variation_attributes[$clean_attribute_key] as $current_slug) {

                                // Get attribute term
                                if ($term = get_term_by('slug', $current_slug, $clean_attribute_key)) {

                                    // Add term id to list
                                    if (!in_array($term->term_id, $attributes, true)) {
                                        $attributes[] = $term->term_id;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Get product id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_product_id();

        // Get product attributes
        if ($item_id) {
            $attributes = array_merge($attributes, WCCF_WC_Product::get_attribute_term_ids($item_id));
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['product_product_attributes_method'], $attributes, $condition['product_attributes']);
    }

    /**
     * Condition check: Product product category
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_product_product_category($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id')));

        // Get product id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_product_id();

        // Check if product id is set
        if (!$item_id) {
            return false;
        }

        // Store categories of current product
        $categories = array();

        // Get product categories
        $product_categories = get_the_terms($item_id, 'product_cat');

        if (!empty($product_categories) && is_array($product_categories)) {
            foreach ($product_categories as $category) {
                $categories[] = $category->term_id;
            }
        }

        // Check condition
        return self::compare_in_list_not_in_list($condition['product_product_category_method'], $categories, $condition['product_categories']);
    }

    /**
     * Condition check: Product product type
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_product_product_type($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id')));

        // Get product id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_product_id();

        // Check if product id is set
        if (!$item_id) {
            return false;
        }

        // Get product type
        $product = wc_get_product($item_id);
        $product_type = RightPress_WC_Legacy::product_get_type($product);

        // Check condition
        return self::compare_in_list_not_in_list($condition['product_product_type_method'], $product_type, $condition['product_types']);
    }

    /**
     * Condition check: Cart subtotal
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_cart_subtotal($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition')));

        global $woocommerce;
        $subtotal = $woocommerce->cart->tax_display_cart === 'excl' ? $woocommerce->cart->subtotal_ex_tax : $woocommerce->cart->subtotal;

        // Allow developers to override
        $subtotal = apply_filters('wccf_condition_check_cart_subtotal_value', $subtotal);

        // Check condition
        return self::compare_at_least_less_than($condition['cart_subtotal_method'], $subtotal, $condition['decimal']);
    }

    /**
     * Condition check: Cart coupons applied
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_cart_coupons($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition')));

        global $woocommerce;

        // Get applied coupon ids
        $cart_coupons = array();

        if (isset($woocommerce->cart->applied_coupons) && is_array($woocommerce->cart->applied_coupons)) {
            foreach ($woocommerce->cart->applied_coupons as $applied_coupon) {
                $cart_coupons[] = RightPress_Helper::get_wc_coupon_id_from_code($applied_coupon);
            }
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['cart_coupons_method'], $cart_coupons, $condition['coupons']);
    }

    /**
     * Condition check: Cart products in cart
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_cart_items_products_in_cart($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition')));

        global $woocommerce;
        $products_in_cart = array();

        foreach ($woocommerce->cart->cart_contents as $cart_item) {
            $products_in_cart[] = $cart_item['data']->is_type('variation') ? RightPress_WC_Legacy::product_variation_get_parent_id($cart_item['data']) : RightPress_WC_Legacy::product_get_id($cart_item['data']);
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['cart_items_products_in_cart_method'], $products_in_cart, $condition['products']);
    }

    /**
     * Condition check: Cart product variations in cart
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_cart_items_product_variations_in_cart($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition')));

        global $woocommerce;
        $variations_in_cart = array();

        // Iterate over cart items
        foreach ($woocommerce->cart->cart_contents as $cart_item) {

            // Check if current cart item is variation
            if (!empty($cart_item['variation_id']) && !in_array($cart_item['variation_id'], $variations_in_cart, true)) {

                // Add variation to list
                $variations_in_cart[] = $cart_item['variation_id'];
            }
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['cart_items_product_variations_in_cart_method'], $variations_in_cart, $condition['product_variations']);
    }

    /**
     * Condition check: Cart product attributes in cart
     *
     * WC31: Check if this still works
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_cart_items_product_attributes_in_cart($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition')));

        global $woocommerce;
        $attributes_in_cart = array();

        // Iterate over cart items
        foreach ($woocommerce->cart->cart_contents as $cart_item) {

            // Get variation attributes
            if (!empty($cart_item['variation_id']) && !empty($cart_item['variation'])) {

                // Iterate over variation attributes
                foreach ($cart_item['variation'] as $attribute_key => $attribute_slug) {

                    // Check if this looks like a regular variation attribute
                    if (RightPress_Helper::string_contains_phrase($attribute_key, 'attribute_')) {

                        // Get attribute term
                        if ($term = get_term_by('slug', $attribute_slug, str_replace('attribute_', '', $attribute_key))) {

                            // Add term id to list
                            if (!in_array($term->term_id, $attributes_in_cart, true)) {
                                $attributes_in_cart[] = $term->term_id;
                            }
                        }
                    }
                }
            }

            // Get product attributes
            $attributes_in_cart = array_merge($attributes_in_cart, WCCF_WC_Product::get_attribute_term_ids($cart_item['data']));
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['cart_items_product_attributes_in_cart_method'], $attributes_in_cart, $condition['product_attributes']);
    }

    /**
     * Condition check: Cart product categories in cart
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_cart_items_product_categories_in_cart($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition')));

        global $woocommerce;
        $product_categories_in_cart = array();
        $condition_categories_split = array();
        $condition_categories = array();

        foreach ($woocommerce->cart->cart_contents as $cart_item) {

            $product_id = $cart_item['data']->is_type('variation') ? RightPress_WC_Legacy::product_variation_get_parent_id($cart_item['data']) : RightPress_WC_Legacy::product_get_id($cart_item['data']);

            // WC31: Check if product categories are still post terms
            $item_categories = wp_get_post_terms($product_id, 'product_cat');

            if (!empty($item_categories) && is_array($item_categories)) {
                foreach ($item_categories as $category) {
                    if (!in_array($category->term_id, $product_categories_in_cart)) {
                        $product_categories_in_cart[] = $category->term_id;
                    }
                }
            }
        }

        // Check if condition categories are set
        if (!empty($condition['product_categories']) && is_array($condition['product_categories'])) {

            // Get condition product categories including child categories split by parent
            foreach ($condition['product_categories'] as $category_id) {
                $condition_categories_split[$category_id] = RightPress_Helper::get_term_with_children($category_id, 'product_cat');
            }

            // Get condition product categories
            $condition_categories = self::merge_all_children($condition_categories_split);
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['cart_items_product_categories_in_cart_method'], $product_categories_in_cart, $condition_categories, $condition_categories_split);
    }

    /**
     * Condition check: Customer role from order
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_customer_order_role($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id')));

        // Get order id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_order_id();

        // Check if order id is set
        if (!$item_id) {
            return false;
        }

        // WC31: Orders will no longer be posts
        $order = RightPress_Helper::wc_get_order($item_id);
        $user = get_userdata(RightPress_WC_Legacy::order_get_customer_id($order));
        $roles = $user ? (array) $user->roles : array();

        // Check condition
        return self::compare_in_list_not_in_list($condition['customer_order_role_method'], $roles, $condition['roles']);
    }

    /**
     * Condition check: Customer capability from order
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_customer_order_capability($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id')));

        // Get order id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_order_id();

        // Check if order id is set
        if (!$item_id) {
            return false;
        }

        // WC31: Orders will no longer be posts
        $order = RightPress_Helper::wc_get_order($item_id);
        $user = get_userdata(RightPress_WC_Legacy::order_get_customer_id($order));
        $capabilities = array();

        if ($user) {
            foreach ($user->allcaps as $capability => $status) {
                if ($status) {
                    $capabilities[] = $capability;
                }
            }
        }

        // Check condition
        return self::compare_in_list_not_in_list($condition['customer_order_capability_method'], $capabilities, $condition['capabilities']);
    }

    /**
     * Condition check: Order total
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_order_total($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id')));

        // Get order id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_order_id();

        // Check if order id is set
        if (!$item_id) {
            return false;
        }

        $order = RightPress_Helper::wc_get_order($item_id);
        $total = $order->get_total();

        // Check condition
        return self::compare_at_least_less_than($condition['order_total_method'], $total, $condition['decimal']);
    }

    /**
     * Condition check: Order coupons applied
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_order_coupons($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id')));

        // Get order id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_order_id();

        // Check if order id is set
        if (!$item_id) {
            return false;
        }

        // Load order
        $order = RightPress_Helper::wc_get_order($item_id);

        // Get order coupons
        $order_coupons = $order->get_used_coupons();

        // Get coupon ids
        foreach ($order_coupons as $order_coupon_index => $order_coupon) {
            $order_coupons[$order_coupon_index] = RightPress_Helper::get_wc_coupon_id_from_code($order_coupon);
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['order_coupons_method'], $order_coupons, $condition['coupons']);
    }

    /**
     * Condition check: Order products in order
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_order_items_products_in_order($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id')));

        // Get order id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_order_id();

        // Check if order id is set
        if (!$item_id) {
            return false;
        }

        $order = RightPress_Helper::wc_get_order($item_id);
        $products_in_order = array();

        foreach ($order->get_items() as $order_item) {

            $product_id = (int) RightPress_WC_Legacy::order_item_get_product_id($order_item);

            if (!in_array($product_id, $products_in_order)) {
                $products_in_order[] = $product_id;
            }
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['order_items_products_in_order_method'], $products_in_order, $condition['products']);
    }

    /**
     * Condition check: Order product variations in order
     *
     * WC31: Check if this works correctly
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_order_items_product_variations_in_order($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id')));

        // Get order id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_order_id();

        // Check if order id is set
        if (!$item_id) {
            return false;
        }

        $order = RightPress_Helper::wc_get_order($item_id);
        $variations_in_order = array();

        foreach ($order->get_items() as $order_item) {

            $variation_id = (int) RightPress_WC_Legacy::order_item_get_variation_id($order_item);

            if (!empty($variation_id) && !in_array($variation_id, $variations_in_order, true)) {
                $variations_in_order[] = $variation_id;
            }
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['order_items_product_variations_in_order_method'], $variations_in_order, $condition['product_variations']);
    }

    /**
     * Condition check: Order product attributes in order
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_order_items_product_attributes_in_order($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id')));

        // Get order id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_order_id();

        // Check if order id is set
        if (!$item_id) {
            return false;
        }

        $order = RightPress_Helper::wc_get_order($item_id);
        $attributes_in_order = array();

        foreach ($order->get_items() as $order_item) {

            $variation_id = RightPress_WC_Legacy::order_item_get_variation_id($order_item);

            // Get variation attributes
            if (!empty($variation_id)) {

                $meta_data = RightPress_Helper::wc_version_gte('3.0') ? $order_item['item_meta'] : $order_item['meta_data'];

                // Iterate over order item data
                if (is_array($meta_data) && !empty($meta_data)) {
                    foreach ($meta_data as $meta_key => $meta_value) {

                        // Check if key looks like product attribute key
                        if (RightPress_Helper::string_contains_phrase($meta_key, 'pa_')) {

                            // Get attribute term
                            if ($term = get_term_by('slug', $meta_value, $meta_key)) {

                                // Add term id to list
                                if (!in_array($term->term_id, $attributes_in_order, true)) {
                                    $attributes_in_order[] = $term->term_id;
                                }
                            }
                        }
                    }
                }
            }

            // Get product attributes
            $attributes_in_order = array_merge($attributes_in_order, WCCF_WC_Product::get_attribute_term_ids($order_item['product_id']));
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['order_items_product_attributes_in_order_method'], $attributes_in_order, $condition['product_attributes']);
    }

    /**
     * Condition check: Order product categories in order
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_order_items_product_categories_in_order($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id')));

        // Get order id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_order_id();

        // Check if order id is set
        if (!$item_id) {
            return false;
        }

        $order = RightPress_Helper::wc_get_order($item_id);
        $product_categories_in_order = array();
        $condition_categories_split = array();
        $condition_categories = array();

        foreach ($order->get_items() as $order_item) {

            // WC31: Product categories may no longer be post terms
            $item_categories = wp_get_post_terms(RightPress_WC_Legacy::order_item_get_product_id($order_item), 'product_cat');

            if (!empty($item_categories) && is_array($item_categories)) {
                foreach ($item_categories as $category) {
                    if (!in_array($category->term_id, $product_categories_in_order)) {
                        $product_categories_in_order[] = $category->term_id;
                    }
                }
            }
        }

        // Check if condition categories are set
        if (!empty($condition['product_categories']) && is_array($condition['product_categories'])) {

            // Get condition product categories including child categories split by parent
            foreach ($condition['product_categories'] as $category_id) {
                // WC31: Product categories may no longer be post terms
                $condition_categories_split[$category_id] = RightPress_Helper::get_term_with_children($category_id, 'product_cat');
            }

            // Get condition product categories
            $condition_categories = self::merge_all_children($condition_categories_split);
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['order_items_product_categories_in_order_method'], $product_categories_in_order, $condition_categories, $condition_categories_split);
    }

    /**
     * Condition check: Order payment method
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_order_payment_method($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id')));

        // Get order id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_order_id();

        // Check if order id is set
        if (!$item_id) {
            return false;
        }

        $order = RightPress_Helper::wc_get_order($item_id);
        $payment_method = RightPress_WC_Legacy::order_get_payment_method($order);

        // Check condition
        return self::compare_in_list_not_in_list($condition['order_payment_method_method'], $payment_method, $condition['payment_methods']);
    }

    /**
     * Condition check: Order shipping method
     *
     * @access public
     * @param array $params
     * @return bool
     */
    public static function condition_check_order_shipping_method($params)
    {
        extract(RightPress_Helper::filter_by_keys($params, array('condition', 'item_id')));

        // Get order id
        $item_id = !empty($item_id) ? $item_id : RightPress_Helper::get_wc_order_id();

        // Check if order id is set
        if (!$item_id) {
            return false;
        }

        $order = RightPress_Helper::wc_get_order($item_id);
        $shipping_methods = array();

        // Get shipping method
        if (RightPress_Helper::wc_version_gte('3.0')) {
            foreach ($order->get_shipping_methods() as $shipping_method) {
                $shipping_methods[] = preg_replace('/\:.+/', '', $shipping_method->get_method_id());
            }
        }
        else {
            $shipping_methods[] = $order->shipping_method;
        }

        // Since WC 3.0 order can have multiple shipping methods, however, this functionality is not something that would be used widely so we stick with the in list / not in list selection options in the UI
        $condition_method = ($condition['order_shipping_method_method'] === 'in_list' ? 'at_least_one' : 'none');

        // Check condition
        return self::compare_at_least_one_all_none($condition_method, $shipping_methods, $condition['shipping_methods']);
    }

    /**
     * Check if item is in list of items
     *
     * @access public
     * @param string $method
     * @param mixed $items
     * @param array $condition_items
     * @return bool
     */
    public static function compare_in_list_not_in_list($method, $items, $condition_items)
    {
        // Make sure items was passed as array
        $items = (array) $items;

        // Proceed depending on method
        if ($method === 'not_in_list') {
            if (count(array_intersect($items, $condition_items)) == 0) {
                return true;
            }
        }
        else if (count(array_intersect($items, $condition_items)) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Compare list of items with list of elements in conditions
     *
     * @access public
     * @param string $method
     * @param array $items
     * @param array $condition_items
     * @param array $condition_items_split
     * @return bool
     */
    public static function compare_at_least_one_all_none($method, $items, $condition_items, $condition_items_split = array())
    {
        // Make sure items was passed as array
        $items = (array) $items;

        // None
        if ($method === 'none') {
            if (count(array_intersect($items, $condition_items)) == 0) {
                return true;
            }
        }

        // All - regular check
        else if ($method === 'all' && empty($condition_items_split)) {
            if (count(array_intersect($items, $condition_items)) == count($condition_items)) {
                return true;
            }
        }

        // All - special case
        // Check with respect to parent items (e.g. parent categories)
        // This is a special case - we can't simply compare against
        // $condition_items which include child items since this would
        // require for them to also be present in $items
        else if ($method === 'all') {

            // Iterate over all condition items split by parent
            foreach ($condition_items_split as $parent_with_children) {

                // At least one item must match at least one item in parent/children array
                if (count(array_intersect($items, $parent_with_children)) == 0) {
                    return false;
                }
            }

            return true;
        }

        // At least one
        else if (count(array_intersect($items, $condition_items)) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Compare number with another number
     *
     * @access public
     * @param string $method
     * @param int $number
     * @param int $condition_number
     * @return bool
     */
    public static function compare_at_least_less_than($method, $number, $condition_number)
    {
        if ($method === 'less_than') {
            if ($number < $condition_number) {
                return true;
            }
        }
        else if ($number >= $condition_number) {
            return true;
        }

        return false;
    }

    /**
     * Merge all child taxonomy terms from a list split by parent
     *
     * @access public
     * @param array $items_split
     * @return array
     */
    public static function merge_all_children($items_split)
    {
        $items = array();

        // Iterate over parents
        foreach ($items_split as $parent_id => $children) {

            // Add parent to children array
            $children[] = (int) $parent_id;

           // Add unique parent/children to main array
            $items = array_merge($items, $children);
            $items = array_unique($items);
        }

        return $items;
    }

    /**
     * Text comparison
     *
     * @access public
     * @param string $method
     * @return bool
     */
    public static function compare_text_comparison($method, $text, $condition_text)
    {
        // Text must be set, otherwise there's nothing to compare against
        if (empty($text)) {
            return false;
        }

        // No text set in conditions
        if (empty($condition_text)) {
            return in_array($method, array('equals', 'does_not_contain')) ? false : true;
        }

        // Proceed depending on condition method
        switch ($method) {

            // Equals
            case 'equals':
                return self::equals($text, $condition_text);

            // Does Not Equal
            case 'does_not_equal':
                return !self::equals($text, $condition_text);

            // Contains
            case 'contains':
                return self::contains($text, $condition_text);

            // Does Not Contain
            case 'does_not_contain':
                return !self::contains($text, $condition_text);

            // Begins with
            case 'begins_with':
                return self::begins_with($text, $condition_text);

            // Ends with
            case 'ends_with':
                return self::ends_with($text, $condition_text);

            default:
                return true;
        }
    }

    /**
     * Check if value is empty (but not zero)
     *
     * @access public
     * @param mixed $value
     * @return bool
     */
    public static function is_empty($value)
    {
        return RightPress_Helper::is_empty($value);
    }

    /**
     * Check if value contains string
     *
     * @access public
     * @param mixed $value
     * @param string $string
     * @return bool
     */
    public static function contains($value, $string)
    {
        if (gettype($value) === 'array') {
            return in_array($string, $value);
        }
        else {
            return (strpos($value, $string) !== false);
        }

        return false;
    }

    /**
     * Check if value equals string
     *
     * @access public
     * @param mixed $value
     * @param string $string
     * @return bool
     */
    public static function equals($value, $string)
    {
        if (gettype($value) === 'array') {
            foreach ($value as $single_value) {
                if ($single_value === $string) {
                    return true;
                }
            }
        }
        else {
            return ($value === $string);
        }

        return false;
    }

    /**
     * Check if value is less than number
     *
     * @access public
     * @param mixed $value
     * @param string $number
     * @return bool
     */
    public static function less_than($value, $number)
    {
        if (gettype($value) === 'array') {
            foreach ($value as $single_value) {
                if ($single_value < $number) {
                    return true;
                }
            }
        }
        else {
            return ($value < $number);
        }

        return false;
    }

    /**
     * Check if value is more than number
     *
     * @access public
     * @param mixed $value
     * @param string $number
     * @return bool
     */
    public static function more_than($value, $number)
    {
        if (gettype($value) === 'array') {
            foreach ($value as $single_value) {
                if ($single_value > $number) {
                    return true;
                }
            }
        }
        else {
            return ($value > $number);
        }

        return false;
    }

    /**
     * Check if value represents field being checked
     *
     * @access public
     * @param mixed $value
     * @return bool
     */
    public static function is_checked($value)
    {
        if (gettype($value) === 'array') {
            foreach ($value as $single_value) {
                if ($single_value) {
                    return true;
                }
            }
        }
        else if ($value) {
            return true;
        }

        return false;
    }

    /**
     * Get supported other field condition methods by field types
     *
     * @access public
     * @return array
     */
    public static function get_other_field_condition_methods_by_field_types()
    {
        $methods = array();

        // Iterate over field types
        foreach (WCCF_Field_Controller::get_field_type_list() as $field_type => $properties) {
            $methods[$field_type] = $properties['other_field_condition_methods'];
        }

        return $methods;
    }

}
}
