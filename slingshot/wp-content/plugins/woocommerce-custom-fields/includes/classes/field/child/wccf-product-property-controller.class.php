<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product property controller class
 *
 * @class WCCF_Product_Property_Controller
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Product_Property_Controller')) {

class WCCF_Product_Property_Controller extends WCCF_Field_Controller
{
    // Using wccf_product_prop instead of wccf_product_property due to custom post type name length limitations
    protected $post_type        = 'wccf_product_prop';
    protected $post_type_short  = 'product_prop';

    protected $supports_pricing     = true;
    protected $supports_visibility  = true;

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
        parent::__construct();
    }

    /**
     * Get object type title
     *
     * @access protected
     * @return string
     */
    protected function get_object_type_title()
    {
        return __('Product Property', 'rp_wccf');
    }

    /**
     * Get post type labels
     *
     * @access public
     * @return array
     */
    public function get_post_type_labels()
    {
        return array(
            'labels' => array(
                'name'               => __('Product Properties', 'rp_wccf'),
                'singular_name'      => __('Product Property', 'rp_wccf'),
                'add_new'            => __('New Property', 'rp_wccf'),
                'add_new_item'       => __('New Product Property', 'rp_wccf'),
                'edit_item'          => __('Edit Product Property', 'rp_wccf'),
                'new_item'           => __('New Property', 'rp_wccf'),
                'all_items'          => __('Product Properties', 'rp_wccf'),
                'view_item'          => __('View Property', 'rp_wccf'),
                'search_items'       => __('Search Properties', 'rp_wccf'),
                'not_found'          => __('No Product Properties Found', 'rp_wccf'),
                'not_found_in_trash' => __('No Product Properties Found In Trash', 'rp_wccf'),
                'parent_item_colon'  => '',
                'menu_name'          => __('Product Properties', 'rp_wccf'),
            ),
            'description' => __('Product Properties', 'rp_wccf'),
        );
    }

    /**
     * Get all fields of this type
     *
     * @access public
     * @param array $status
     * @param array $key
     * @return array
     */
    public static function get_all($status = array(), $key = array())
    {
        $instance = self::get_instance();
        return self::get_all_fields($instance->get_post_type(), $status, $key);
    }

    /**
     * Get all fields of this type filtered by conditions
     *
     * @access public
     * @param array $status
     * @param array $params
     * @param mixed $checkout_position
     * @param bool $first_only
     * @return array
     */
    public static function get_filtered($status = null, $params = array(), $checkout_position = null, $first_only = false)
    {
        $all_fields = self::get_all((array) $status);
        return WCCF_Conditions::filter_fields($all_fields, $params, $checkout_position, $first_only);
    }

    /**
     * Get product property by id
     *
     * @access public
     * @param int $field_id
     * @param string $post_type
     * @return object
     */
    public static function get($field_id, $post_type = null)
    {
        $instance = self::get_instance();
        return parent::get($field_id, $instance->get_post_type());
    }

    /**
     * Get admin field description header
     *
     * @access public
     * @return string
     */
    public function get_admin_field_description_header()
    {
        return __('About Product Properties', 'rp_wccf');
    }

    /**
     * Get admin field description content
     *
     * @access public
     * @return string
     */
    public function get_admin_field_description_content()
    {
        return __('Product properties are used to provide additional product related information to your customers. They are available on product edit pages for shop managers to fill in.', 'rp_wccf');
    }

}

WCCF_Product_Property_Controller::get_instance();

}
