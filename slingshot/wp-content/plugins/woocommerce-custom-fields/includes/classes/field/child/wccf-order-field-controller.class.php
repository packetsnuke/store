<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Order field controller class
 *
 * @class WCCF_Order_Field_Controller
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Order_Field_Controller')) {

class WCCF_Order_Field_Controller extends WCCF_Field_Controller
{
    protected $post_type        = 'wccf_order_field';
    protected $post_type_short  = 'order_field';

    protected $supports_visibility = true;

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
        return __('Order Field', 'rp_wccf');
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
                'name'               => __('Order Fields', 'rp_wccf'),
                'singular_name'      => __('Order Field', 'rp_wccf'),
                'add_new'            => __('New Field', 'rp_wccf'),
                'add_new_item'       => __('New Order Field', 'rp_wccf'),
                'edit_item'          => __('Edit Order Field', 'rp_wccf'),
                'new_item'           => __('New Field', 'rp_wccf'),
                'all_items'          => __('Order Fields', 'rp_wccf'),
                'view_item'          => __('View Field', 'rp_wccf'),
                'search_items'       => __('Search Fields', 'rp_wccf'),
                'not_found'          => __('No Order Fields Found', 'rp_wccf'),
                'not_found_in_trash' => __('No Order Fields Found In Trash', 'rp_wccf'),
                'parent_item_colon'  => '',
                'menu_name'          => __('Order Fields', 'rp_wccf'),
            ),
            'description' => __('Order Fields', 'rp_wccf'),
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
     * Get order field by id
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
        return __('About Order Fields', 'rp_wccf');
    }

    /**
     * Get admin field description content
     *
     * @access public
     * @return string
     */
    public function get_admin_field_description_content()
    {
        return __('Order fields are used by shop managers during order processing and fulfilment.', 'rp_wccf');
    }

}

WCCF_Order_Field_Controller::get_instance();

}
