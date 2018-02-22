<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product field controller class
 *
 * @class WCCF_Product_Field_Controller
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Product_Field_Controller')) {

class WCCF_Product_Field_Controller extends WCCF_Field_Controller
{
    protected $post_type        = 'wccf_product_field';
    protected $post_type_short  = 'product_field';

    protected $supports_pricing     = true;
    protected $supports_quantity    = true;

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

        // Highlight menu link on add post page
        add_filter('submenu_file', array($this, 'highlight_menu_link'), 10, 2);
    }

    /**
     * Get object type title
     *
     * @access protected
     * @return string
     */
    protected function get_object_type_title()
    {
        return __('Product Field', 'rp_wccf');
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
                'name'               => __('Product Fields', 'rp_wccf'),
                'singular_name'      => __('Product Field', 'rp_wccf'),
                'add_new'            => __('New Field', 'rp_wccf'),
                'add_new_item'       => __('New Product Field', 'rp_wccf'),
                'edit_item'          => __('Edit Product Field', 'rp_wccf'),
                'new_item'           => __('New Field', 'rp_wccf'),
                'all_items'          => __('Product Fields', 'rp_wccf'),
                'view_item'          => __('View Field', 'rp_wccf'),
                'search_items'       => __('Search Fields', 'rp_wccf'),
                'not_found'          => __('No Product Fields Found', 'rp_wccf'),
                'not_found_in_trash' => __('No Product Fields Found In Trash', 'rp_wccf'),
                'parent_item_colon'  => '',
                'menu_name'          => __('Custom Fields', 'rp_wccf'),
            ),
            'description' => __('Product Fields', 'rp_wccf'),
        );
    }

    /**
     * Highlight menu link
     *
     * @access public
     * @param string $submenu_file
     * @param string $parent_file
     * @return array
     */
    public function highlight_menu_link($submenu_file, $parent_file)
    {
        if ($parent_file === 'edit.php?post_type=wccf_product_field' && $submenu_file === 'post-new.php?post_type=wccf_product_field') {
            $submenu_file = 'edit.php?post_type=wccf_product_field';
        }

        return $submenu_file;
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
     * Get product field by id
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
        return __('About Product Fields', 'rp_wccf');
    }

    /**
     * Get admin field description content
     *
     * @access public
     * @return string
     */
    public function get_admin_field_description_content()
    {
        return __('Product fields are used to sell configurable products, product add-ons or just gather additional information from customers on product pages.', 'rp_wccf');
    }


}

WCCF_Product_Field_Controller::get_instance();

}
