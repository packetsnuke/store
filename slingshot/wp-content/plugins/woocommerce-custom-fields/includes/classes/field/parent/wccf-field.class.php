<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Field object class
 *
 * @class WCCF_Field
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Field')) {

class WCCF_Field extends WCCF_Post_Object
{
    // Define shared object properties
    protected $status = 'disabled';
    protected $status_title;
    protected $field_type;
    protected $field_type_title;
    protected $key;
    protected $label;
    protected $required;
    protected $conditions;
    protected $options;
    protected $description;
    protected $default_value;
    protected $custom_css;
    protected $character_limit;
    protected $min_selected;
    protected $max_selected;
    protected $min_value;
    protected $max_value;

    // Define meta keys
    // Make sure to always keep key above label as sanitized key is used when label is not set
    protected static $meta_properties = array(
        'key'               => 'string',
        'label'             => 'string',
        'required'          => 'bool',
        'conditions'        => 'array',
        'options'           => 'array',
        'description'       => 'string',
        'default_value'     => 'string',
        'custom_css'        => 'string',
        'character_limit'   => 'int',
        'min_selected'      => 'int',
        'max_selected'      => 'int',
        'min_value'         => 'int',
        'max_value'         => 'int',
    );

    // Define WordPress term keys
    protected $term_keys = array(
        'status', 'field_type',
    );

    /**
     * Constructor class
     *
     * @access public
     * @param mixed $id
     * @return void
     */
    public function __construct($id)
    {
        // Construct parent first
        parent::__construct($id);
    }

    /**
     * Get meta properties
     *
     * @access public
     * @return array
     */
    protected function get_meta_properties()
    {
        return array_merge(parent::get_meta_properties(), self::$meta_properties);
    }

    /**
     * Get status
     *
     * @access public
     * @return string
     */
    public function get_status()
    {
        return isset($this->status) ? $this->status : null;
    }

    /**
     * Get status title
     *
     * @access public
     * @return string
     */
    public function get_status_title()
    {
        return isset($this->status_title) ? $this->status_title : null;
    }

    /**
     * Get field type
     *
     * @access public
     * @return string
     */
    public function get_field_type()
    {
        return isset($this->field_type) ? $this->field_type : null;
    }

    /**
     * Check if field type matches provided field types
     *
     * @access public
     * @param mixed $field_types
     * @return bool
     */
    public function field_type_is($field_types)
    {
        $field_types = (array) $field_types;

        // Check if field type matches provided field types
        return in_array($this->get_field_type(), $field_types);
    }

    /**
     * Get field type title
     *
     * @access public
     * @return string
     */
    public function get_field_type_title()
    {
        return isset($this->field_type_title) ? $this->field_type_title : null;
    }

    /**
     * Get field key
     *
     * @access public
     * @return string
     */
    public function get_key()
    {
        return isset($this->key) ? $this->key : null;
    }

    /**
     * Get field label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {
        return isset($this->label) ? $this->label : null;
    }

    /**
     * Get conditions
     *
     * @access public
     * @return array
     */
    public function get_conditions()
    {
        return isset($this->conditions) ? $this->conditions : array();
    }

    /**
     * Get frontend conditions
     *
     * @access public
     * @return array
     */
    public function get_frontend_conditions()
    {
        $frontend_conditions = array();

        // Iterate over all conditions
        foreach ($this->get_conditions() as $condition) {

            // Check if this is a frontend condition
            if ($condition['type'] === 'custom_field_other_custom_field') {

                // Add to frontend conditions list
                $frontend_conditions[] = $condition;
            }
        }

        return $frontend_conditions;
    }

    /**
     * Get options
     *
     * @access public
     * @return array
     */
    public function get_options()
    {
        return isset($this->options) ? $this->options : array();
    }

    /**
     * Get description
     *
     * @access public
     * @return string
     */
    public function get_description()
    {
        return (isset($this->description) && !RightPress_Helper::is_empty($this->description)) ? $this->description : null;
    }

    /**
     * Get custom css
     *
     * @access public
     * @return string
     */
    public function get_custom_css()
    {
        return isset($this->custom_css) ? $this->custom_css : null;
    }

    /**
     * Get character limit
     *
     * @access public
     * @return int
     */
    public function get_character_limit()
    {
        return isset($this->character_limit) ? $this->character_limit : null;
    }

    /**
     * Get min selected limit
     *
     * @access public
     * @return int
     */
    public function get_min_selected()
    {
        return isset($this->min_selected) ? $this->min_selected : null;
    }


    /**
     * Get max selected limit
     *
     * @access public
     * @return int
     */
    public function get_max_selected()
    {
        return isset($this->max_selected) ? $this->max_selected : null;
    }

    /**
     * Get min value limit
     *
     * @access public
     * @return int
     */
    public function get_min_value()
    {
        return isset($this->min_value) ? $this->min_value : null;
    }


    /**
     * Get max value limit
     *
     * @access public
     * @return int
     */
    public function get_max_value()
    {
        return isset($this->max_value) ? $this->max_value : null;
    }

    /**
     * Check if field is required
     *
     * @access public
     * @return bool
     */
    public function is_required()
    {
        return isset($this->required) ? $this->required : null;
    }

    /**
     * Check if field is quantity based
     *
     * @access public
     * @return bool
     */
    public function is_quantity_based()
    {
        return isset($this->quantity_based) ? $this->quantity_based : null;
    }

    /**
     * Get pricing method
     *
     * @access public
     * @return string
     */
    public function get_pricing_method()
    {
        // Return object property
        return isset($this->pricing_method) ? $this->pricing_method : null;
    }

    /**
     * Get pricing value
     *
     * @access public
     * @return float
     */
    public function get_pricing_value()
    {
        // Return object property
        return isset($this->pricing_value) ? $this->pricing_value : null;
    }

    /**
     * Check if field has pricing
     *
     * @access public
     * @return bool
     */
    public function has_pricing()
    {
        foreach ($this->get_options() as $option) {
            if (isset($option['pricing_value'])) {
                return true;
            }
        }

        return isset($this->pricing_value);
    }

    /**
     * Check if field uses pricing
     *
     * @access public
     * @return bool
     */
    public function uses_pricing()
    {
        return in_array($this->get_context(), array('product_field', 'product_prop', 'checkout_field'), true);
    }

    /**
     * Check if field is public
     *
     * @access public
     * @return bool
     */
    public function is_public()
    {
        return isset($this->public) ? $this->public : null;
    }

    /**
     * Check if field is public (alias for filter_by_property)
     *
     * @access public
     * @return bool
     */
    public function get_public()
    {
        return $this->is_public();
    }

    /**
     * Get position
     *
     * @access public
     * @return string
     */
    public function get_position()
    {
        return isset($this->position) ? $this->position : null;
    }

    /**
     * Get tax class
     *
     * @access public
     * @return string
     */
    public function get_tax_class()
    {
        return isset($this->tax_class) ? $this->tax_class : null;
    }

    /**
     * Check if field has conditions
     *
     * @access public
     * @return bool
     */
    public function has_conditions()
    {
        $conditions = $this->get_conditions();
        return !empty($conditions);
    }

    /**
     * Check if field has frontend conditions
     *
     * @access public
     * @return bool
     */
    public function has_frontend_conditions()
    {
        $frontend_conditions = $this->get_frontend_conditions();
        return !empty($frontend_conditions);
    }

    /**
     * Check if field has options
     *
     * @access public
     * @return bool
     */
    public function has_options()
    {
        $options = $this->get_options();
        return !empty($options);
    }

    /**
     * Check if field uses options
     *
     * @access public
     * @return bool
     */
    public function uses_options()
    {
        return WCCF_FB::uses_options($this->get_field_type());
    }

    /**
     * Get option pricing values
     *
     * @access public
     * @param string $option_key
     * @return mixed
     */
    public function get_option_pricing($option_key)
    {
        // Iterate over options
        foreach ($this->get_options() as $option) {
            if ($option['key'] === $option_key && isset($option['pricing_value'])) {
                return array(
                    'pricing_method'    => $option['pricing_method'],
                    'pricing_value'     => $option['pricing_value'],
                );
            }
        }

        return null;
    }

    /**
     * Check if option has pricing
     *
     * @access public
     * @param string $option_key
     * @return bool
     */
    public function option_has_pricing($option_key)
    {
        return (bool) $this->get_option_pricing($option_key);
    }

    /**
     * Get options list
     *
     * Special handling in case one or more option keys are numeric - we must
     * have array indexes as strings for later comparison and simply setting
     * array index to numeric string changes it's type to integer, therefore
     * we use a hack to enforce string index
     *
     * @access public
     * @return array
     */
    public function get_options_list()
    {
        $list = array();
        $numeric_keys_found = false;

        // Iterate over options
        foreach($this->get_options() as $option) {

            // Prepare option key and label
            $key = (string) $option['key'];
            $label = ($option['label'] !== '' ? $option['label'] : $key);

            // Check if option key is numeric
            if (is_numeric($key)) {
                $list = (object) $list;
                $numeric_keys_found = true;
            }

            // Add to list
            if ($numeric_keys_found) {
                $list->$key = $label;
            }
            else {
                $list[$key] = $label;
            }
        }

        return (array) $list;
    }

    /**
     * Alias to get short version of post type
     * Used to identify what kind of field is being printed
     *
     * @access public
     * @return string
     */
    public function get_context()
    {
        return $this->get_post_type_short();
    }

    /**
     * Check if context matches provided values
     *
     * @access public
     * @param mixed $contexts
     * @return string
     */
    public function context_is($contexts)
    {
        $contexts = (array) $contexts;

        // Check if context is one of the provided values
        return in_array($this->get_context(), $contexts);
    }

    /**
     * Get post type abbreviation
     *
     * Used to store field data as post meta so they do not collide
     * with values of fields of other types
     *
     * @access public
     * @return string
     */
    public function get_abbreviation()
    {
        return $this->post_type_abbreviation;
    }

    /**
     * Get post meta storage key based on field type abbreviation
     *
     * Used to store/retrieve field values for display
     *
     * @access public
     * @param int $quantity_index
     * @return string
     */
    public function get_value_access_key($quantity_index = null)
    {
        return '_wccf_' . $this->get_abbreviation() . '_' . $this->get_key() . ($quantity_index ? ('_' . $quantity_index) : '');
    }

    /**
     * Get post meta key for field id storage based on field type abbreviation
     *
     * Used to store and retrieve field id in post meta
     *
     * @access public
     * @param int $quantity_index
     * @return string
     */
    public function get_id_access_key($quantity_index = null)
    {
        return '_wccf_' . $this->get_abbreviation() . '_id_' . $this->get_key() . ($quantity_index ? ('_' . $quantity_index) : '');
    }

    /**
     * Get post meta key for field extra data storage based on field type abbreviation
     *
     * Used to store and retrieve additional field data in post meta
     *
     * @access public
     * @param int $quantity_index
     * @return string
     */
    public function get_extra_data_access_key($quantity_index = null)
    {
        return '_wccf_' . $this->get_abbreviation() . '_data_' . $this->get_key() . ($quantity_index ? ('_' . $quantity_index) : '');
    }

    /**
     * Get post meta key for file data storage
     *
     * Used to store and retrieve uploaded field data in post meta
     *
     * @access public
     * @param string $access_key
     * @return string
     */
    public static function get_file_data_access_key($access_key)
    {
        return '_wccf_file_' . $access_key;
    }

    /**
     * Get post meta key for temporary file data storage
     *
     * Used to temporary store file data after Ajax upload request before actual form is submitted
     *
     * @access public
     * @param string $access_key
     * @return string
     */
    public static function get_temp_file_data_access_key($access_key)
    {
        return '_wccf_temp_file_' . $access_key;
    }

    /**
     * Check if field actually accepts multiple values
     *
     * @access public
     * @return bool
     */
    public function accepts_multiple_values()
    {
        // Multiselect field always accepts multiple values
        if ($this->field_type_is('multiselect')) {
            return true;
        }

        // Checkboxes also accept multiple values but only if a set is displayed
        if ($this->field_type_is('checkbox') && count($this->get_options()) > 1) {
            return true;
        }

        // File upload with multiple values
        if ($this->field_type_is('file') && apply_filters('wccf_file_field_supports_multiple_files', (bool) WCCF_Settings::get('multiple_files'), $this)) {
            return true;
        }

        return false;
    }

    /**
     * Populate own properties
     *
     * @access protected
     * @return void
     */
    protected function populate_own_properties()
    {
        // Set properties from WP terms
        foreach ($this->term_keys as $term_key) {

            // Get post terms
            $post_terms = wp_get_post_terms($this->id, $this->get_post_type() . '_' . $term_key);

            // Set property
            $this->$term_key = (isset($post_terms[0]) && is_object($post_terms[0])) ? RightPress_Helper::clean_term_slug($post_terms[0]->slug) : null;

            // Set title property
            $title_key = $term_key . '_title';
            $this->$title_key = $this->$term_key ? $this->get_term_title_from_slug($term_key, $this->$term_key) : null;
        }
    }

    /**
     * Return default statuses
     *
     * @access public
     * @return array
     */
    public function get_status_list()
    {
        // Get controller instance
        $controller_instance = $this->get_controller_instance();

        // Get and return status list
        return $controller_instance::get_status_list();
    }

    /**
     * Return default field types
     *
     * @access public
     * @return array
     */
    public function get_field_type_list()
    {
        // Get controller instance
        $controller_instance = $this->get_controller_instance();

        // Get and return field types list
        return $controller_instance::get_field_type_list();
    }

    /**
     * Change object status
     *
     * @access public
     * @param string $new_status
     * @param bool $display_notice
     * @param string $notice
     * @return void
     */
    public function change_status($new_status, $display_notice = false, $notice = null)
    {
        // Check if field is archived
        if ($this->is_archived()) {

            // Print notice if needed
            if ($display_notice) {
                $archived_field_notice = __('Changes are not allowed to archived fields.', 'rp_wccf');
                $this->print_status_change_error($archived_field_notice);
            }

            // No status changes allowed for archived fields
            return false;
        }

        // Process status change
        $this->update_field('status', $new_status);

        // Trigger field revision update
        WCCF_Settings::reset_objects_revision();

        // Display notice
        if ($display_notice && !defined('WCCF_FIELD_STATUS_CHANGE_NOTICE_DISPLAYED')) {

            // Do not display multiple times
            define('WCCF_FIELD_STATUS_CHANGE_NOTICE_DISPLAYED', true);

            // Add notice
            add_settings_error(
                'wccf',
                'field_status_changed',
                sprintf($notice, WCCF_Post_Object_Controller::get_general_short_name($this->get_post_type())),
                'updated'
            );
        }

        // Status changed
        return true;
    }

    /**
     * Enable object
     *
     * @access public
     * @param array $posted
     * @param bool $display_notice
     * @return void
     */
    public function enable($posted = array(), $display_notice = false)
    {
        // Fields that have options (select, multiselect, checkbox, radio button) can't be enabled if no options are configured
        if (WCCF_Field_Controller::field_type_requires_options($this->get_field_type()) && !$this->has_options()) {

            // Print notice if needed
            if ($display_notice) {
                $notice = __('Error: At least one option must be configured for this field type. Field disabled.', 'rp_wccf');
                $this->print_status_change_error($notice);
            }

            // Abort enabling field
            return false;
        }

        // Enable field
        return $this->change_status('enabled', $display_notice, __('%s enabled.', 'rp_wccf'));
    }

    /**
     * Enable object
     *
     * @access public
     * @param array $posted
     * @param bool $display_notice
     * @return void
     */
    public function disable($posted = array(), $display_notice = false)
    {
        // Disable field
        return $this->change_status('disabled', $display_notice, __('%s disabled.', 'rp_wccf'));
    }

    /**
     * Archive object
     *
     * @access public
     * @param array $posted
     * @param bool $display_notice
     * @return void
     */
    public function archive($posted = array(), $display_notice = false)
    {
        // Archive field
        return $this->change_status('archived', $display_notice, __('%s archived.', 'rp_wccf'));
    }

    /**
     * Print status change error
     *
     * @access public
     * @param string $notice
     * @return void
     */
    protected function print_status_change_error($notice)
    {
        // Add admin notice
        add_settings_error(
            'wccf',
            'status_change_error',
            $notice
        );
    }

    /**
     * Check if object is enabled
     *
     * @access public
     * @return bool
     */
    public function is_enabled()
    {
        $is_enabled = $this->get_status() === 'enabled';
        return apply_filters($this->get_post_type() . '_is_enabled', $is_enabled, $this);
    }

    /**
     * Check if object is archived
     *
     * @access public
     * @return bool
     */
    public function is_archived()
    {
        return $this->get_status() === 'archived';
    }

    /**
     * Change object field type
     *
     * @access public
     * @param string $new_field_type
     * @return void
     */
    public function change_field_type($new_field_type)
    {
        $this->update_field('field_type', $new_field_type);
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
        // Save status
        if (isset($data['status'])) {

            // Get list of known statuses
            $statuses = self::get_status_list();

            if (isset($statuses[$data['status']])) {
                $this->change_status($data['status']);
            }
        }

        // Save field type
        if (isset($data['field_type'])) {

            // Get list of known field types
            $field_types = self::get_field_type_list();

            // Check if field type is set
            if (isset($field_types[$data['field_type']])) {

                // Always change field type for new fields
                if (empty($this->field_type)) {
                    $this->change_field_type($data['field_type']);
                }

                // Get interchangeable field types
                $interchangeable_fields = WCCF_FB::get_interchangeable_fields();

                // Only change field type for existing field if field types are interchangeable
                if (isset($interchangeable_fields[$this->field_type]) && in_array($data['field_type'], $interchangeable_fields[$this->field_type])) {
                    $this->change_field_type($data['field_type']);
                }
                else {
                    $error_message = __('Error:', 'rp_wccf') . ' ' . __('New and old field types are not interchangeable.', 'rp_wccf') . ' ' . __('Field type change aborted.', 'rp_wccf');
                    add_settings_error(
                        'wccf',
                        'condition_validation_error',
                        $error_message
                    );
                }
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
        // Update status
        if ($field === 'status') {

            // Get list of known statuses
            $statuses = self::get_status_list();

            if (isset($statuses[$value])) {
                $this->status = $value;
                $this->status_title = $statuses[$value]['title'];
                wp_set_object_terms($this->id, $value, $this->get_post_type() . '_status');
            }

            return true;
        }

        // Update field type
        if ($field === 'field_type') {

            // Get list of known field types
            $field_types = self::get_field_type_list();

            if (isset($field_types[$value])) {
                $this->field_type = $value;
                $this->field_type_title = $field_types[$value]['title'];
                wp_set_object_terms($this->id, $value, $this->get_post_type() . '_field_type');
            }

            return true;
        }

        return false;
    }

    /**
     * Get values for fields in duplicate object
     *
     * @access public
     * @return array
     */
    public function get_duplicate_values()
    {
        $values = array();

        // Set field type
        $values['field_type'] = $this->field_type;

        // Iterate over meta properties
        foreach ($this->get_meta_properties() as $key => $type) {

            // Ensure field key is unique
            if ($key === 'key') {
                $controller = $this->get_controller_instance();
                $values[$key] = $controller->ensure_unique_key($this->$key);
            }
            // Prepend "Duplicate of" to field label
            else if ($key === 'label' && $this->$key !== '') {
                $values[$key] = __('Duplicate of', 'rp_wccf') . ' ' . $this->$key;
            }
            // Handle all other properties
            else {
                $values[$key] = isset($this->$key) ? $this->$key : null;
            }
        }

        return $values;
    }

    /**
     * Sanitize key value
     *
     * @access protected
     * @param array $data
     * @return mixed
     */
    protected function sanitize_key_value($data)
    {
        // Key can't be updated for existing fields
        $existing_key = $this->get_key();

        if ($existing_key !== null) {
            return $existing_key;
        }

        // Key must be set
        $key = isset($data['key']) ? (string) $data['key'] : '';

        // Leave only allowed characters
        $key = WCCF_Field::filter_key_value_characters($key);

        // Skip further validation if new key matches old key
        if ($key === $this->get_key()) {
            return $key;
        }

        // Use random string if key is empty
        $key = $key !== '' ? $key : RightPress_Helper::get_hash();

        // Ensure that key is unique
        $controller = $this->get_controller_instance();

        $key = $controller->ensure_unique_key($key);

        return $key;
    }

    /**
     * Filter out invalid field key characters
     *
     * @access public
     * @param string $key
     * @return string
     */
    public static function filter_key_value_characters($key)
    {
        return preg_replace('/[^A-Z0-9_]+/i', '', strtolower($key));
    }

    /**
     * Sanitize label value
     *
     * @access protected
     * @param array $data
     * @return mixed
     */
    protected function sanitize_label_value($data)
    {
        // Get label
        $label = isset($data['label']) ? (string) $data['label'] : '';

        // Use key if label is not set
        if ($label === '' && isset($this->key)) {
            $label = $this->key;
        }

        return $label;
    }

    /**
     * Sanitize character limit value
     *
     * @access protected
     * @param array $data
     * @return mixed
     */
    protected function sanitize_character_limit_value($data)
    {
        return !empty($data['character_limit']) ? $data['character_limit'] : null;
    }

    /**
     * Sanitize min selected value
     *
     * @access protected
     * @param array $data
     * @return mixed
     */
    protected function sanitize_min_selected_value($data)
    {
        return !empty($data['min_selected']) ? $data['min_selected'] : null;
    }

    /**
     * Sanitize max selected value
     *
     * @access protected
     * @param array $data
     * @return mixed
     */
    protected function sanitize_max_selected_value($data)
    {
        return !empty($data['max_selected']) ? $data['max_selected'] : null;
    }

    /**
     * Sanitize min value value
     *
     * @access protected
     * @param array $data
     * @return mixed
     */
    protected function sanitize_min_value_value($data)
    {
        return !empty($data['min_value']) ? $data['min_value'] : null;
    }

    /**
     * Sanitize max value value
     *
     * @access protected
     * @param array $data
     * @return mixed
     */
    protected function sanitize_max_value_value($data)
    {
        return !empty($data['max_value']) ? $data['max_value'] : null;
    }

    /**
     * Sanitize options value
     *
     * @access protected
     * @param array $data
     * @return mixed
     */
    protected function sanitize_options_value($data)
    {
        $sanitized_options = array();

        // No options required
        if (!WCCF_Field_Controller::field_type_requires_options($this->get_field_type())) {
            return $sanitized_options;
        }

        // Check if at least one option is defined
        if (!empty($data['options']) && is_array($data['options'])) {

            // Iterate over options and sanitize them
            foreach ($data['options'] as $option) {
                if ($sanitized_option = $this->sanitize_single_option($option, $data['options'])) {
                    $sanitized_options[] = $sanitized_option;
                }
            }
        }

        // No options set but field is active
        if (empty($sanitized_options) && $this->get_status() === 'enabled') {

            // Disable field
            $this->disable();

            // Add admin notice
            add_settings_error(
                'wccf',
                'field_requires_options',
                __('Error: At least one option must be configured for this field type. Field disabled.', 'rp_wccf')
            );
        }

        return $sanitized_options;
    }

    /**
     * Sanitize singe option
     *
     * @access protected
     * @param array $option
     * @param array $all_options
     * @return mixed
     */
    protected function sanitize_single_option($option, $all_options)
    {
        $sanitized_option = array();

        try {

            // Option must be array and must not be empty
            if (!is_array($option) || empty($option)) {
                $error_message = __('Error:', 'rp_wccf') . ' ' . __('Invalid option discarded.', 'rp_wccf');
                throw new Exception($error_message);
            }

            // Key must be set and must be unique across all options
            if (isset($option['key']) && preg_match('/^[A-Z0-9_]+$/i', $option['key']) && self::option_key_is_unique($option['key'], $all_options)) {
                $sanitized_option['key'] = strtolower($option['key']);
            }
            else {
                $error_message = __('Error:', 'rp_wccf') . ' ' . __('Option key is invalid or duplicate keys were found.', 'rp_wccf') . ' ' . __('Invalid option discarded.', 'rp_wccf');
                throw new Exception($error_message);
            }

            // Label
            $sanitized_option['label'] = !empty($option['label']) ? (string) $option['label'] : $sanitized_option['key'];

            // Pricing method
            $sanitized_option['pricing_method'] = $this->sanitize_pricing_method_value($option);

            // Pricing value
            $sanitized_option['pricing_value'] = $this->sanitize_pricing_value_value($option);

            // Selected
            $sanitized_option['selected'] = (isset($option['selected']) && $option['selected']) ? 1 : 0;

        } catch (Exception $e) {

            // Add admin notice
            add_settings_error(
                'wccf',
                'option_validation_error',
                $e->getMessage()
            );

            return false;
        }

        return $sanitized_option;
    }

    /**
     * Check if option key is unique across all options
     *
     * @access private
     * @param string $key
     * @param array $options
     * @return bool
     */
    private static function option_key_is_unique($key, $options)
    {
        $count = 0;

        foreach ($options as $option) {
            if (isset($option['key']) && strtolower($option['key']) === strtolower($key)) {
                $count++;

                if ($count > 1) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Sanitize conditions value
     *
     * @access protected
     * @param array $data
     * @return mixed
     */
    protected function sanitize_conditions_value($data)
    {
        $sanitized_conditions = array();

        // Check if at least one condition is defined
        if (!empty($data['conditions']) && is_array($data['conditions'])) {

            // Iterate over conditions and sanitize them
            foreach ($data['conditions'] as $condition) {
                if ($sanitized_condition = $this->sanitize_single_condition($condition, $data['conditions'])) {
                    $sanitized_conditions[] = $sanitized_condition;
                }
            }
        }

        return $sanitized_conditions;
    }

    /**
     * Sanitize singe condition
     *
     * @access protected
     * @param array $condition
     * @param array $all_conditions
     * @return mixed
     */
    protected function sanitize_single_condition($condition, $all_conditions)
    {
        $sanitized_condition = array();

        // Load some data for validation
        $condition_types = WCCF_Conditions::get_conditions_list($this->get_context());

        try {

            // Type
            if (isset($condition['type']) && ($group_option = WCCF_Conditions::extract_group_and_option($condition['type']))) {

                // Extract group key and option key
                list($group_key, $option_key) = $group_option;

                // Check if such keys exist
                if (isset($condition_types[$group_key]) && isset($condition_types[$group_key]['options'][$option_key])) {
                    $sanitized_condition['type'] = $condition['type'];
                }
                else {
                    $error_message = __('Error:', 'rp_wccf') . ' ' . __('Condition type not set.', 'rp_wccf') . ' ' . __('Invalid condition discarded.', 'rp_wccf');
                    throw new Exception($error_message);
                }
            }
            else {
                $error_message = __('Error:', 'rp_wccf') . ' ' . __('Condition type not set.', 'rp_wccf') . ' ' . __('Invalid condition discarded.', 'rp_wccf');
                throw new Exception($error_message);
            }

            // Method
            $method_key = $sanitized_condition['type'] . '_method';

            if (isset($condition[$method_key])) {

                // Get all condition methods for current condition
                $condition_methods = WCCF_Conditions::get_methods_list($group_key, $option_key);

                // Check if selected condition method exists
                if (isset($condition_methods[$condition[$method_key]])) {
                    $sanitized_condition[$method_key] = $condition[$method_key];
                }
                else {
                    $error_message = __('Error:', 'rp_wccf') . ' ' . __('Condition method not set.', 'rp_wccf') . ' ' . __('Invalid condition discarded.', 'rp_wccf');
                    throw new Exception($error_message);
                }
            }
            else {
                $error_message = __('Error:', 'rp_wccf') . ' ' . __('Condition method not set.', 'rp_wccf') . ' ' . __('Invalid condition discarded.', 'rp_wccf');
                throw new Exception($error_message);
            }

            // Sanitize multiselect values
            foreach (WCCF_Conditions::get_multiselect_field_keys() as $key) {
                if (WCCF_Conditions::uses_field($group_key, $option_key, $key)) {
                    $sanitized_condition[$key] = isset($condition[$key]) ? (array) $condition[$key] : array();
                }
            }

            // Other field id
            if (WCCF_Conditions::uses_field($group_key, $option_key, 'other_field_id')) {
                if (!empty($condition['other_field_id']) && RightPress_Helper::post_type_is($condition['other_field_id'], $this->get_post_type())) {
                    $sanitized_condition['other_field_id'] = (int) $condition['other_field_id'];
                }
                else {
                    $error_message = __('Error:', 'rp_wccf') . ' ' . __('Invalid field selected in Other Field condition settings.', 'rp_wccf') . ' ' . __('Invalid condition discarded.', 'rp_wccf');
                    throw new Exception($error_message);
                }
            }

            // Text
            if (WCCF_Conditions::uses_field($group_key, $option_key, 'text')) {
                $sanitized_condition['text'] = !empty($condition['text']) ? (string) $condition['text'] : '';
            }

            // Number
            if (WCCF_Conditions::uses_field($group_key, $option_key, 'number')) {
                if (isset($condition['number']) && preg_match('/^[0-9]+$/i', $condition['number'])) {
                    $sanitized_condition['number'] = $condition['number'];
                }
                else {
                    $error_message = __('Error:', 'rp_wccf') . ' ' . __('Invalid numeric value in field conditions.', 'rp_wccf') . ' ' . __('Invalid condition discarded.', 'rp_wccf');
                    throw new Exception($error_message);
                }
            }

            // Decimal
            if (WCCF_Conditions::uses_field($group_key, $option_key, 'decimal')) {
                if (isset($condition['decimal']) && preg_match('/^[0-9\.]+$/i', $condition['decimal'])) {
                    $sanitized_condition['decimal'] = $condition['decimal'];
                }
                else {
                    $error_message = __('Error:', 'rp_wccf') . ' ' . __('Invalid decimal value in field conditions.', 'rp_wccf') . ' ' . __('Invalid condition discarded.', 'rp_wccf');
                    throw new Exception($error_message);
                }
            }

        } catch (Exception $e) {

            // Add admin notice
            add_settings_error(
                'wccf',
                'condition_validation_error',
                $e->getMessage()
            );

            return false;
        }

        return $sanitized_condition;
    }

    /**
     * Sanitize pricing method value
     *
     * @access protected
     * @param array $data
     * @return mixed
     */
    protected function sanitize_pricing_method_value($data)
    {
        $pricing_method = isset($data['pricing_method']) ? $data['pricing_method'] : null;
        $pricing_value = isset($data['pricing_value']) ? $data['pricing_value'] : null;

        return self::validate_pricing($pricing_method, $pricing_value) ? $pricing_method : null;
    }

    /**
     * Sanitize pricing value value
     *
     * @access protected
     * @param array $data
     * @return mixed
     */
    protected function sanitize_pricing_value_value($data)
    {
        $pricing_method = isset($data['pricing_method']) ? $data['pricing_method'] : null;
        $pricing_value = isset($data['pricing_value']) ? $data['pricing_value'] : null;

        return self::validate_pricing($pricing_method, $pricing_value) ? (float) $pricing_value : null;
    }

    /**
     * Check if pricing method and pricing value pair is valid
     *
     * @access protected
     * @param string $pricing_method
     * @param float $pricing_value
     * @return bool
     */
    protected static function validate_pricing($pricing_method, $pricing_value)
    {
        // Check if pricing method is valid
        if (!WCCF_Pricing::pricing_method_exists($pricing_method)) {
            return false;
        }

        // Check if pricing value is provided
        if (!((float) $pricing_value)) {
            return false;
        }

        return true;
    }

    /**
     * Get default value (e.g. preselected options or default text value)
     *
     * @access public
     * @return mixed
     */
    public function get_default_value()
    {
        // Fields with options
        if ($this->has_options()) {

            $value = array();

            // Iterate over options
            foreach ($this->get_options() as $option) {

                // Check if option is selected by default
                if ($option['selected']) {
                    $value[] = $option['key'];
                }
            }

            if (!empty($value)) {
                return $value;
            }
        }
        // Fields with no options
        else if (isset($this->default_value) && !RightPress_Helper::is_empty($this->default_value)) {
            return $this->default_value;
        }

        return false;
    }

    /**
     * Check if field has default value
     *
     * @access public
     * @return bool
     */
    public function has_default_value()
    {
        return $this->get_default_value() !== false;
    }

    /**
     * Get option labels from option keys
     *
     * @access public
     * @param array $option_keys
     * @param array $extra_data
     * @return array
     */
    public function get_option_labels_from_keys($option_keys, $extra_data = array())
    {
        $option_keys = (array) $option_keys;
        $option_labels = array();

        // Iterate over options
        foreach ($this->get_options() as $option) {
            if (in_array($option['key'], $option_keys)) {
                $option_labels[$option['key']] = $option['label'];
            }
        }

        // Check if missing labels can be taken from stored data
        if (count($option_labels) !== count($option_keys) && !empty($extra_data['labels'])) {

            // Iterate over option keys
            foreach ($option_keys as $option_key) {
                if (!isset($option_labels[$option_key]) && isset($extra_data['labels'][$option_key])) {
                    $option_labels[$option_key] = $extra_data['labels'][$option_key];
                }
            }
        }

        return $option_labels;
    }

    /**
     * Format display value with optional pricing value
     *
     * @access public
     * @param mixed $value
     * @param bool $display_pricing
     * @param bool $is_cart
     * @return string
     */
    public function format_display_value($value, $display_pricing = false, $is_cart = false)
    {
        // Check if pricing needs to be displayed
        $display_pricing = ($display_pricing && WCCF_Settings::get('prices_cart_order_page'));

        // Frontend empty value display
        if (RightPress_Helper::is_empty($value['value']) && ($this->context_is('product_prop') || $this->context_is('order_field')) && !is_admin()) {
            return '<span class="wccf_no_value">' . __('n/a', 'rp_wccf') . '</span>';
        }

        // Field with options
        if ($this->has_options()) {

            // Get option labels
            $option_labels = $this->get_option_labels_from_keys($value['value'], $value['data']);

            // Check if pricing needs to be displayed
            if ($display_pricing) {

                // Iterate over option labels
                foreach ($option_labels as $option_key => $option_label) {

                    // Get pricing data for this option
                    if ($pricing_data = $this->get_final_option_pricing($option_key, $value['data'], $is_cart)) {

                        // Get pricing string
                        $pricing_string = WCCF_Pricing::get_pricing_string($pricing_data['pricing_method'], $pricing_data['pricing_value'], false, '(', ')');

                        // Append pricing string to option label
                        $option_labels[$option_key] = $option_label . $pricing_string;
                    }
                }
            }

            // Glue options labels together and return string
            $glue = apply_filters('wccf_option_labels_glue', ', ');
            return implode($glue, $option_labels);
        }
        // Field with text value
        else {

            // Format pricing string
            if ($display_pricing && $this->get_final_pricing_value($value['data'], $is_cart) !== null) {
                $pricing_string = WCCF_Pricing::get_pricing_string($this->get_final_pricing_method($value['data'], $is_cart), $this->get_final_pricing_value($value['data'], $is_cart), false, '(', ')');
            }
            else {
                $pricing_string = '';
            }

            // Handle file upload value
            if ($this->field_type_is('file')) {

                $display_values = array();

                // Iterate over files (multiple files per field are supported
                foreach ((array) $value['value'] as $access_key) {

                    // Format and return file download link
                    $file_data = $is_cart ? $value['files'][$access_key] : array();
                    $display_values[] = WCCF_Files::get_file_download_link_html($access_key, $file_data);
                }

                // Glue file names together and return string
                $glue = apply_filters('wccf_file_names_glue', '<br>');
                return implode($glue, $display_values) . $pricing_string;
            }
            else {

                // Append pricing string if needed and return
                return stripslashes((string) $value['value']) . $pricing_string;
            }
        }
    }

    /**
     * Get stored field value
     * Allows to define custom storage where to load data from (e.g. user field data may be stored as order meta in some cases)
     *
     * Returns false if value was not found or there's field id mismatch
     *
     * @access public
     * @param mixed $item
     * @param int $quantity_index
     * @param string $custom_storage
     * @param bool $is_frontend_display
     * @return mixed
     */
    public function get_stored_value($item, $quantity_index = null, $custom_storage = null, $is_frontend_display = false)
    {
        // Validate stored value by field id
        if ($this->validate_stored_entry($item, $quantity_index)) {

            // Get value access key
            $value_access_key = $this->get_value_access_key($quantity_index);

            // Get stored value
            $stored_value = $this->get_stored_data($item, $value_access_key);

            // Maybe hide empty values in frontend
            if (RightPress_Helper::is_empty($stored_value) && $is_frontend_display) {
                if (($this->context_is('product_prop') && !WCCF_Settings::get('display_empty_product_prop_values')) || ($this->context_is('order_field') && !WCCF_Settings::get('display_empty_order_field_values'))) {
                    return false;
                }
            }

            // Return stored value
            if ($stored_value !== null) {
                return $stored_value;
            }
        }

        // Check if we have stored value saved in version 1.x
        if (WCCF_Migration::support_for('1')) {

            // Get value stored in version 1.x
            $item = (RightPress_Helper::wc_version_gte('3.0') && !is_object($item)) ? $this->load_item($item) : $item;
            $stored_value = WCCF_Migration::get_stored_value_from_1($item, $this);

            // Value found
            if ($stored_value !== null) {
                return $stored_value;
            }
        }

        return false;
    }

    /**
     * Get stored extra data from meta
     *
     * Returns false if data was not found or there's field id mismatch
     *
     * @access public
     * @param int $item_id
     * @param int $quantity_index
     * @return mixed
     */
    public function get_stored_extra_data($item_id, $quantity_index = null)
    {
        // Validate stored data by field id
        if (!$this->validate_stored_entry($item_id, $quantity_index)) {
            return false;
        }

        // Get data access key
        $data_access_key = $this->get_extra_data_access_key($quantity_index);

        // Get stored extra data
        $stored_data = $this->get_stored_data($item_id, $data_access_key);

        // Return stored data
        return !empty($stored_data) ? (array) $stored_data : false;
    }

    /**
     * Get stored data
     *
     * @access public
     * @param mixed $item
     * @param string $access_key
     * @return mixed
     */
    public function get_stored_data($item, $access_key)
    {
        if ($this->data_exists($item, $access_key)) {
            return $this->get_data($item, $access_key, true);
        }
    }

    /**
     * Get final value for a given item - either stored or default if set
     *
     * Returns null if field has no value for a given item
     *
     * @access public
     * @param int $item_id
     * @param bool $is_frontend_display
     * @return mixed
     */
    public function get_final_value($item_id, $is_frontend_display = false)
    {
        // Get stored value
        $stored_value = $this->get_stored_value($item_id, null, null, $is_frontend_display);

        // Check if valid stored value was found
        if ($stored_value !== false) {
            return $stored_value;
        }

        // Get default value
        $default_value = $this->get_default_value();

        // Check if default value is set
        if ($default_value !== false) {
            return $default_value;
        }

        // Field does not have any value for a given item
        return false;
    }

    /**
     * Get value from predefined values array
     *
     * @access public
     * @param array $values
     * @param int $quantity_index
     * @return mixed
     */
    public function get_value_from_values_array($values, $quantity_index = null)
    {
        // Field id treatment for quantity based product fields
        $field_id = $this->get_id();
        $field_id_for_name = $quantity_index ? ($field_id . '_' . $quantity_index) : $field_id;

        // Check if values array is valid
        if (empty($values) || !is_array($values)) {
            return false;
        }

        // Check if any data is set for this field
        if (!isset($values[$field_id_for_name])) {
            return false;
        }

        // Check if value is set for this field
        if (!isset($values[$field_id_for_name]['value'])) {
            return false;
        }

        // Return value from predefined values array
        return $values[$field_id_for_name]['value'];
    }

    /**
     * Check if stored meta data actually belongs to current field
     *
     * Field key can't be trusted because it is possible to delete field and
     * create a new one with the same key
     *
     * @access protected
     * @param mixed $item
     * @param int $quantity_index
     * @return bool
     */
    public function validate_stored_entry($item, $quantity_index = null)
    {
        // Get field id access key
        $field_id_access_key = $this->get_id_access_key($quantity_index);

        // Get stored field id
        $stored_id = $this->get_stored_data($item, $field_id_access_key);

        // Check for field if mismatch
        return ((int) $stored_id === $this->get_id());
    }

    /**
     * Store field value
     *
     * Accepts standard $value array, real value is under $value['value']
     *
     * @access public
     * @param mixed $item
     * @param array $value
     * @param int $quantity_index
     * @return void
     */
    public function store_value($item, $value, $quantity_index = null)
    {
        // Make sure all file access keys are still unique for product fields (since product field values can be in cart for weeks)
        if ($this->context_is('product_field') && $this->field_type_is('file')) {

            // Iterate over access keys
            foreach ($value['value'] as $index => $access_key) {

                // Get unique key (returns the same one if still unique)
                $unique_key = WCCF_Files::get_unique_file_access_key($access_key);

                // Check if key has been changed
                if ($access_key !== $unique_key) {

                    // Change access key in value
                    $value['value'][$index] = $unique_key;

                    // Add new item to files array
                    $value['files'][$unique_key] = $value['files'][$access_key];

                    // Delete previous item from files array
                    unset($value['files'][$access_key]);
                }
            }
        }

        // Delete files that are no longer present or not allowed to be present
        if ($this->field_type_is('file')) {

            // Get currently stored value
            $stored_value = $this->get_stored_value($item);

            // Check if we have files stored for this field/item
            if (!empty($stored_value) && is_array($stored_value)) {

                // Check if we need to delete all files
                $delete_all_files = (RightPress_Helper::is_empty($value['value']) && ($this->context_is('order_field') || $this->context_is('product_prop') || $this->context_is('user_field')));

                // Check if we need to delete previously stored files that are no longer present
                $delete_removed_files = ($this->context_is('order_field') || $this->context_is('product_prop') || $this->context_is('user_field'));

                // Check if any delete operation potentially needs to be executed
                if ($delete_all_files || $delete_removed_files) {

                    // Iterate over currently stored files
                    foreach ($stored_value as $access_key) {

                        // Figure out if current file needs to be deleted
                        $delete_current_file = $delete_all_files;

                        if (!$delete_current_file) {

                            $match_found = false;

                            foreach ($value['value'] as $new_access_key) {
                                if ($new_access_key === $access_key) {
                                    $match_found = true;
                                    break;
                                }
                            }

                            $delete_current_file = !$match_found;
                        }

                        // Delete previously stored file if needed
                        if ($delete_current_file) {
                            WCCF_Files::delete_by_access_key($access_key, $item, $this);
                        }
                    }
                }
            }
        }

        // Do not store empty order fields and product properties - clear any stored value instead
        // Note: the following block was commented out to fix #226
        /*if (RightPress_Helper::is_empty($value['value']) && ($this->context_is('order_field') || $this->context_is('product_prop'))) {

            // Delete any previously stored field data
            $this->delete_stored_value($item);

            // Do not proceed saving empty field value
            return;
        }*/

        // Store field value as hidden meta
        $this->update_data($item, $this->get_value_access_key($quantity_index), $value['value']);

        // Store field id as hidden meta
        $this->update_data($item, $this->get_id_access_key($quantity_index), $this->get_id());

        // Store pricing data in extra data if needed
        if ($pricing_data = $this->get_pricing_data_to_store($value['value'])) {
            $value['data']['pricing'] = $pricing_data;
        }

        // Store option labels in extra data if needed
        if ($this->has_options()) {
            $value['data']['labels'] = $this->get_option_labels_from_keys($value['value']);
        }

        // Store quantity index
        if ($quantity_index) {
            $value['data']['quantity_index'] = $quantity_index;
        }

        // Store extra field data as hidden meta
        // We store empty arrays too to override any previous values
        $this->update_data($item, $this->get_extra_data_access_key($quantity_index), $value['data']);

        // Store file data
        foreach ($value['files'] as $access_key => $file_data) {

            // Store file in meta
            $this->update_data($item, WCCF_Field::get_file_data_access_key($access_key), $file_data);

            // Remove temporary file from meta, if any
            $this->delete_data($item, WCCF_Field::get_temp_file_data_access_key($access_key));

            // Move files from temporary directory to permanent one
            WCCF_Files::move_to_permanent($file_data['subdirectory'], $file_data['storage_key']);
        }
    }

    /**
     * Delete any previously stored field data
     * File data entry must be deleted separately since it requires traversing over file access keys
     *
     * @access protected
     * @param mixed $item
     * @return void
     */
    protected function delete_stored_value($item)
    {
        // Delete value entry
        $this->delete_data($item, $this->get_value_access_key());

        // Delete id entry
        $this->delete_data($item, $this->get_id_access_key());

        // Delete extra data entry
        $this->delete_data($item, $this->get_extra_data_access_key());
    }

    /**
     * Get pricing data to store
     *
     * @access protected
     * @param mixed $value
     * @return mixed
     */
    protected function get_pricing_data_to_store($value)
    {
        $data = array();

        // Check if pricing data needs to be stored
        if ($this->context_is(array('product_field', 'checkout_field')) && $this->has_pricing()) {

            // Check if field has options
            if ($this->has_options()) {

                // Iterate over option keys
                foreach ((array) $value as $option_key) {

                    // Check if this option has pricing set
                    if ($this->option_has_pricing($option_key)) {

                        // Get option pricing data
                        $option_pricing = $this->get_option_pricing($option_key);

                        // Store pricing data for this option
                        $data[$option_key] = array(
                            'pricing_method' => $option_pricing['pricing_method'],
                            'pricing_value'  => $option_pricing['pricing_value'],
                        );
                    }
                }
            }
            else {

                // Store pricing data
                $data = array(
                    'pricing_method' => $this->get_pricing_method(),
                    'pricing_value'  => $this->get_pricing_value(),
                );
            }
        }

        // Return pricing data
        return $data ?: false;
    }

    /**
     * Get final pricing method to use
     *
     * @access public
     * @param array $extra_data
     * @param bool $is_cart
     * @return string
     */
    public function get_final_pricing_method($extra_data, $is_cart)
    {
        // Use stored data only
        if ($this->stored_pricing_only($is_cart)) {
            return !empty($extra_data['pricing']) ? $extra_data['pricing']['pricing_method'] : null;
        }

        // Return regular object property
        return $this->get_pricing_method();
    }

    /**
     * Get final pricing value to use
     *
     * @access public
     * @param array $extra_data
     * @param bool $is_cart
     * @return float
     */
    public function get_final_pricing_value($extra_data, $is_cart)
    {
        // Use stored data only
        if ($this->stored_pricing_only($is_cart)) {
            return !empty($extra_data['pricing']) ? $extra_data['pricing']['pricing_value'] : null;
        }

        // Return regular object property
        return $this->get_pricing_value();
    }

    /**
     * Get final option pricing values
     *
     * @access public
     * @param string $option_key
     * @param array $extra_data
     * @param bool $is_cart
     * @return mixed
     */
    public function get_final_option_pricing($option_key, $extra_data, $is_cart)
    {
        // Use stored data only
        if ($this->stored_pricing_only($is_cart)) {
            return !empty($extra_data['pricing'][$option_key]) ? $extra_data['pricing'][$option_key] : null;
        }

        // Regular option pricing
        return $this->get_option_pricing($option_key);
    }

    /**
     * Check if only stored pricing data can be used
     *
     * @access public
     * @param bool $is_cart
     * @return bool
     */
    public function stored_pricing_only($is_cart)
    {
        return ($this->context_is('checkout_field') || ($this->context_is('product_field') && !$is_cart));
    }

    /**
     * Check if field has attribute-related conditions (product variation or product attributes)
     *
     * @access public
     * @return bool
     */
    public function has_product_attribute_conditions()
    {
        // Iterate over conditions
        foreach ($this->get_conditions() as $condition) {

            // Check if condition is attribute-related
            if (in_array($condition['type'], array('product_product_variation', 'product_product_attributes'), true)) {
                return true;
            }
        }

        // No attribute-related conditions found
        return false;
    }


}
}
