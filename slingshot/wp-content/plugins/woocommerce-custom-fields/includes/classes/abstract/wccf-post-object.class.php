<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract post object class
 *
 * @class WCCF_Post_Object
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Post_Object')) {

class WCCF_Post_Object
{
    // Define shared object properties
    protected $id;

    // Define meta keys in key => type combinations
    // Enforcable data types: string, int, float, bool, array
    protected static $meta_properties = array();

    /**
     * Constructor class
     *
     * @access public
     * @param int $id
     * @return void
     */
    public function __construct($id)
    {
        $this->id = (int) $id;
        $this->populate();
    }

    /**
     * Get post type
     *
     * @access public
     * @return string
     */
    public function get_post_type()
    {
        return $this->post_type;
    }

    /**
     * Get short form of post type
     *
     * @access protected
     * @return string
     */
    public function get_post_type_short()
    {
        return $this->post_type_short;
    }

    /**
     * Get ID
     *
     * @access public
     * @return int
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Check if post type is configurable
     *
     * @access public
     * @return bool
     */
    public function is_configurable()
    {
        // Get controller instance
        $controller_instance = $this->get_controller_instance();

        // Check if object is configurable
        return $controller_instance->is_configurable();
    }

    /**
     * Popuplate existing object with properties
     *
     * @access public
     * @return void
     */
    public function populate()
    {
        // Get post meta
        $post_meta = RightPress_Helper::unwrap_post_meta(get_post_meta($this->id));

        // Set properties from meta
        foreach ($this->get_meta_properties() as $key => $type) {

            // Check if such field exists
            if (isset($post_meta[$key])) {
                $this->$key = RightPress_Helper::cast_to($type, maybe_unserialize($post_meta[$key]));
            }
            else {
                $this->$key = null;
            }
        }

        // Populate child-specific properties
        $this->populate_own_properties();
    }

    /**
     * Get meta properties
     *
     * @access public
     * @return array
     */
    protected function get_meta_properties()
    {
        return self::$meta_properties;
    }

    /**
     * Populate own properties
     *
     * @access protected
     * @return void
     */
    protected function populate_own_properties()
    {
    }

    /**
     * Save post object field values
     *
     * @access public
     * @param array $posted
     * @param string $action
     * @param bool $silent
     * @return void
     */
    public function save_configuration($posted = array(), $action = 'save', $silent = false)
    {
        // Action whitelist
        $whitelist = array('save', 'duplicate', 'enable', 'disable', 'archive');

        // Attempt to save configuration
        try {

            // Archived objects can't be edited
            if (method_exists($this, 'is_archived') && $this->is_archived()) {
                throw new Exception(__('Changes are not allowed to archived fields.', 'rp_wccf'));
            }

            // Proceed depending on action
            switch ($action) {

                // Save or duplicate
                case 'save':
                case 'duplicate':

                    // Prevent infinite loop
                    $controller_instance = $this->get_controller_instance();
                    remove_action('save_post', array($controller_instance, 'save_post'), 9, 2);

                    // Check if configuration was passed in
                    if (empty($posted) || !isset($posted['wccf_post_config']) || !is_array($posted['wccf_post_config'])) {
                        throw new Exception(__('Configuration values not present.', 'rp_wccf'));
                    }

                    // Get our own data
                    $data = $posted['wccf_post_config'];

                    // Check if this request is for a new post
                    $is_new_post = RightPress_Helper::post_status_is($this->get_id(), 'draft');

                    // New post?
                    if ($is_new_post) {

                        // Publish post
                        wp_publish_post($this->get_id());

                        // Change title
                        wp_update_post(array(
                            'ID'            => $this->get_id(),
                            'post_title'    => __('Custom Field', 'rp_wccf'),
                        ));

                        // New post published
                        do_action('wccf_new_post_object', $this->get_id());
                    }

                    // Allow child classes to save custom properties
                    $this->save_own_configuration($data);

                    // Save meta fields
                    foreach ($this->get_meta_properties() as $key => $type) {

                        // Allow child classes to do their own sanitization
                        if (method_exists($this, 'sanitize_' . $key . '_value')) {
                            $method = 'sanitize_' . $key . '_value';
                            $value = $this->$method($data);
                        }
                        else {
                            $value = isset($data[$key]) ? $data[$key] : RightPress_Helper::get_empty_value_by_type($type);
                        }

                        // Save field
                        $this->update_field($key, $value);
                    }

                    // Restore callback after we are done
                    add_action('save_post', array($controller_instance, 'save_post'), 9, 2);

                    // Add notice
                    if ($is_new_post && $action === 'duplicate') {
                        $notice = __('%s duplicated, status set to disabled.', 'rp_wccf');
                    }
                    else if ($is_new_post) {
                        $notice = __('%s created, status set to disabled.', 'rp_wccf');
                    }
                    else {
                        $notice = __('%s updated.', 'rp_wccf');
                    }

                    // Add notice
                    if (!$silent) {
                        add_settings_error(
                            'wccf',
                            'post_updated',
                            sprintf($notice, WCCF_Post_Object_Controller::get_general_short_name($this->get_post_type())),
                            'updated'
                        );
                    }

                    break;

                // Let child classes deal with their own actions
                default:
                    if (in_array($action, $whitelist) && method_exists($this, $action)) {
                        $this->$action($posted, true);
                    }
                    break;
            }

        } catch (Exception $e) {

            // Add notice
            if (!$silent) {
                add_settings_error(
                    'wccf',
                    'no_configuration',
                    $e->getMessage()
                );
            }
        }

        // Preserve error messages so they survive potential redirect
        if (!$silent) {
            WCCF_Settings::preserve_error_messages();
        }

        // Trigger revision update
        WCCF_Settings::reset_objects_revision();
    }

    /**
     * Save own configuration
     *
     * @access public
     * @param array $data
     * @return void
     */
    public function save_own_configuration($data = array())
    {
    }

    /**
     * Update single field
     *
     * @access public
     * @return void
     */
    public function update_field($field, $value)
    {
        // Allow child classes to update their own fields first and fall back to default handler if that does not work
        if (!$this->update_own_field($field, $value)) {

            // Get meta properties
            $meta_properties = $this->get_meta_properties();

            // Check if such meta property exists
            if (array_key_exists($field, $meta_properties)) {
                $this->$field = $value !== null ? RightPress_Helper::cast_to($meta_properties[$field], $value) : null;
                update_post_meta($this->id, $field, $this->$field);
            }
        }
    }

    /**
     * Update own field
     *
     * @access public
     * @param string $field
     * @param mixed $value
     * @return bool
     */
    protected function update_own_field($field, $value)
    {
        return false;
    }

    /**
     * Get term title from slug
     *
     * @access public
     * @param string $taxonomy
     * @param string $slug
     * @return string
     */
    public function get_term_title_from_slug($taxonomy, $slug)
    {
        $method = 'get_' . $taxonomy . '_list';
        $list = $this->$method();
        return isset($list[$slug]) ? $list[$slug]['title'] : '';
    }

    /**
     * Get corresponding controller instance
     *
     * @access protected
     * @return object
     */
    protected function get_controller_instance()
    {
        return WCCF_Post_Object_Controller::get_controller_instance_by_post_type($this->get_post_type());
    }


}
}
