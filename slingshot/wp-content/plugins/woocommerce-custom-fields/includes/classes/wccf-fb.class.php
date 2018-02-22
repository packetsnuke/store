<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Form Builder Class
 *
 * @class WCCF_FB
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_FB')) {

class WCCF_FB
{
    // Define form field types
    private static $field_types = null;

    /**
     * Define field types and return field types array
     *
     * @access public
     * @return array
     */
    public static function get_field_types_definition()
    {
        // Define field types
        if (empty(self::$field_types)) {
            self::$field_types = array(
                'text' => array(
                    'label'                 => __('Text', 'rp_wccf'),
                    'interchangeable_with'  => array('text', 'textarea', 'password', 'email', 'number'),
                ),
                'textarea' => array(
                    'label'                 => __('Text area', 'rp_wccf'),
                    'interchangeable_with'  => array('text', 'textarea', 'password', 'email', 'number'),
                ),
                'password' => array(
                    'label'                 => __('Password', 'rp_wccf'),
                    'interchangeable_with'  => array('text', 'textarea', 'password', 'email', 'number'),
                ),
                'email' => array(
                    'label'                 => __('Email', 'rp_wccf'),
                    'interchangeable_with'  => array('text', 'textarea', 'password', 'email', 'number'),
                ),
                'number' => array(
                    'label'                 => __('Number', 'rp_wccf'),
                    'interchangeable_with'  => array('text', 'textarea', 'password', 'email', 'number'),
                ),
                'date' => array(
                    'label'                 => __('Date picker', 'rp_wccf'),
                    'interchangeable_with'  => array('date', 'text', 'textarea', 'password', 'email', 'number'),
                ),
                'select' => array(
                    'label'                 => __('Select', 'rp_wccf'),
                    'interchangeable_with'  => array('select', 'multiselect', 'checkbox', 'radio'),
                ),
                'multiselect' => array(
                    'label'                 => __('Multiselect', 'rp_wccf'),
                    'interchangeable_with'  => array('select', 'multiselect', 'checkbox', 'radio'),
                ),
                'checkbox' => array(
                    'label'                 => __('Checkboxes', 'rp_wccf'),
                    'interchangeable_with'  => array('select', 'multiselect', 'checkbox', 'radio'),
                ),
                'radio' => array(
                    'label'                 => __('Radio buttons', 'rp_wccf'),
                    'interchangeable_with'  => array('select', 'multiselect', 'checkbox', 'radio'),
                ),
                'file' => array(
                    'label'                 => __('File upload', 'rp_wccf'),
                    'interchangeable_with'  => array('file'),
                ),
            );
        }

        // Return field types
        return self::$field_types;
    }

    /**
     * Get list of field types for display in select fields
     *
     * @access public
     * @return array
     */
    public static function get_types()
    {
        $types = array();

        foreach (self::get_field_types_definition() as $type => $properties) {
            $types[$type] = $properties['label'];
        }

        return $types;
    }

    /**
     * Get list of interchangeable fields
     *
     * @access public
     * @return array
     */
    public static function get_interchangeable_fields()
    {
        $types = array();

        foreach (self::get_field_types_definition() as $type => $properties) {
            $types[$type] = $properties['interchangeable_with'];
        }

        return $types;
    }

    /**
     * Render text field
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    public static function text($params, $field = null)
    {
        self::input('text', $params, array('value', 'maxlength', 'placeholder'), $field);
    }

    /**
     * Render text area field
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    public static function textarea($params, $field = null)
    {
        // Get attributes
        $attributes = self::attributes($params, array('value', 'maxlength', 'placeholder'), 'textarea', $field);

        // Get value
        if (isset($params['value'])) {
            $value = $params['value'];
        }
        // Get default field value
        else if ($default_value = WCCF_FB::get_default_value($field)) {
            $value = $default_value;
        }
        // No value
        else {
            $value = '';
        }

        // Generate field html
        $field_html = '<textarea ' . $attributes . '>' . htmlspecialchars($value) . '</textarea>';

        // Render field
        self::output($params, $field_html, $field, 'textarea');
    }

    /**
     * Render password field
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    public static function password($params, $field = null)
    {
        $params['autocomplete'] = 'off';
        self::input('password', $params, array('value', 'maxlength', 'placeholder'), $field);
    }

    /**
     * Render email field
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    public static function email($params, $field = null)
    {
        // Display as regular text field in the frontend, will do our own validation
        $input_type = WCCF_FB::is_backend() ? 'email' : 'text';

        // Print field
        self::input($input_type, $params, array('value', 'maxlength', 'placeholder'), $field);
    }

    /**
     * Render number field
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    public static function number($params, $field = null)
    {
        // Display as regular text field in the frontend, will do our own validation
        $input_type = WCCF_FB::is_backend() ? 'number' : 'text';

        // Define supported attributes
        $attributes = array('value', 'maxlength', 'placeholder');

        // Add min and max attributes in backend
        if (WCCF_FB::is_backend()) {
            $attributes[] = 'min';
            $attributes[] = 'max';
        }

        // Print field
        self::input($input_type, $params, $attributes, $field);
    }

    /**
     * Render date field
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    public static function date($params, $field = null)
    {
        // Disable autocomplete
        $params['autocomplete'] = 'off';

        // Display as regular text field, will initialize jQuery UI Datepicker based on object's class
        self::input('text', $params, array('value'), $field, true);
    }

    /**
     * Render select field
     *
     * @access public
     * @param array $params
     * @param object $field
     * @param bool $is_multiple
     * @param bool $is_grouped
     * @return void
     */
    public static function select($params, $field = null, $is_multiple = false, $is_grouped = false)
    {
        // Add empty option - check if we need one
        if (!WCCF::is_settings_page() && !$is_multiple && empty($params['value']) && (!isset($field) || !$field->has_default_value())) {

            // Also skip select fields in product level form builder
            if (empty($params['name']) || !preg_match('/^wccf_/i', $params['name'])) {

                // If no options are selected, we need to add a blank option at the very beginning of options
                $params['options'] = array('' => '') + $params['options'];
            }
        }

        // Get attributes
        $attributes = self::attributes($params, array(), 'select', $field);

        // Get options
        $options = self::options($params, $field, $is_grouped);

        // Check if it's multiselect
        $multiple_html = $is_multiple ? 'multiple' : '';

        // Generate field html
        $field_html = '<select ' . $multiple_html . ' ' . $attributes . '>' . $options . '</select>';

        // Render field
        $field_type = $is_multiple ? 'multiselect' : ($is_grouped ? 'grouped_select' : 'select');
        self::output($params, $field_html, $field, $field_type, $is_multiple);
    }

    /**
     * Render grouped select field (for internal use only)
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    public static function grouped_select($params, $field = null)
    {
        self::select($params, $field, false, true);
    }

    /**
     * Render multiselect field
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    public static function multiselect($params, $field = null)
    {
        self::select($params, $field, true);
    }

    /**
     * Render checkbox field
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    public static function checkbox($params, $field = null)
    {
        self::checkbox_or_radio('checkbox', $params, $field);
    }

    /**
     * Render radio field
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    public static function radio($params, $field = null)
    {
        self::checkbox_or_radio('radio', $params, $field);
    }

    /**
     * Render checkbox or radio field
     *
     * @access public
     * @param string $type
     * @param array $params
     * @param object $field
     * @return void
     */
    public static function checkbox_or_radio($type, $params, $field = null)
    {
        $field_html = '';

        // Make sure we have at least one option configured
        if (!empty($params['options'])) {

            // Get list of items that are checked by default if value is not present
            if (!isset($params['value']) && $default_value = WCCF_FB::get_default_value($field)) {
                $params['value'] = $default_value;
            }

            // Open list
            $user_field_styles = WCCF_FB::is_backend_user_profile($field) ? 'style="margin: 1px;"' : '';
            $field_html .= '<ul ' . $user_field_styles .  ' >';

            // Iterate over field options and display as individual items
            foreach ($params['options'] as $option_key => $label) {

                // Show pricing information
                $price_html = '';

                // Check if field is set
                if (isset($field)) {

                    // Check if pricing needs to be displayed
                    if (($field->context_is('product_field') && WCCF_Settings::get('prices_product_page')) || ($field->context_is('checkout_field') && WCCF_Settings::get('checkout_field_price_display'))) {

                        // Check if current option has pricing
                        if ($option_pricing = $field->get_option_pricing($option_key)) {

                            // Get pricing string
                            $price_html = WCCF_Pricing::get_pricing_string($option_pricing['pricing_method'], $option_pricing['pricing_value'], true);
                        }
                    }
                }

                // Customize params
                $custom_params = $params;
                $custom_params['id'] = $custom_params['id'] . '_' . $option_key;

                // Get attributes
                $attributes = self::attributes($custom_params, array(), $type, $field);

                // Check if this item needs to be checked
                if (isset($params['value'])) {
                    $values = (array) $params['value'];
                    $checked = in_array($option_key, $values) ? 'checked="checked"' : '';
                }
                // Item is not checked
                else {
                    $checked = '';
                }

                // Generate HTML
                $user_field_styles = WCCF_FB::is_backend_user_profile($field) ? 'style="margin: 0px;"' : '';
                $field_html .= '<li ' . $user_field_styles . ' ><input type="' . $type . '" value="' . $option_key . '" ' . $checked . ' ' . $attributes . '><label for="' . $custom_params['id'] . '">' . (!empty($label) ? ' ' . $label : '') . $price_html . '</label></li>';
            }

            // Close list
            $field_html .= '</ul>';
        }

        // Allow direct no-option calls for internal use
        else if (!isset($field)) {
            $attributes = self::attributes($params, array('value', 'checked'), $type, $field);
            $field_html .= '<input type="' . $type . '" ' . $attributes . '>';
        }

        // Render field
        self::output($params, $field_html, $field, $type, true);
    }

    /**
     * Render file field
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    public static function file($params, $field = null)
    {
        // Define custom attributes
        $custom_attributes = array('accept');

        // Modify field name so that visible file upload field value is not checked during regular form submit
        $params['name'] = str_replace('wccf[', 'wccf_ignore[', $params['name']);

        // Optionally allow multiple file uploads
        if ($field && $field->accepts_multiple_values()) {
            $params['multiple'] = 'multiple';
            $custom_attributes[] = 'multiple';
        }

        // Print field
        self::input('file', $params, $custom_attributes, $field);
    }

    /**
     * Render hidden field
     * For internal use only
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    public static function hidden($params, $field = null)
    {
        self::input('hidden', $params, array('value'), $field);
    }

    /**
     * Render generic input field
     *
     * @access public
     * @param string $type
     * @param array $params
     * @param array $custom_attributes
     * @param object $field
     * @param bool $is_date
     * @return void
     */
    private static function input($type, $params, $custom_attributes = array(), $field = null, $is_date = false)
    {
        // Get default field value if not set
        if (!isset($params['value']) && $default_value = WCCF_FB::get_default_value($field)) {
            $params['value'] = $default_value;
        }

        // Get attributes
        $attributes = self::attributes($params, $custom_attributes, $type, $field);

        // Generate field html
        $field_html = '<input type="' . $type . '" ' . $attributes . '>';

        // Render field
        self::output($params, $field_html, $field, $type, false, $is_date);
    }

    /**
     * Render attributes
     *
     * @access public
     * @param array $params
     * @param array $custom
     * @param string $type
     * @param object $field
     * @return void
     */
    private static function attributes($params, $custom = array(), $type = 'text', $field = null)
    {
        $html = '';

        // Get full list of attributes
        $attributes = array_merge(array('type', 'name', 'id', 'class', 'autocomplete', 'style', 'pattern', 'disabled', 'title'), $custom);

        // Additional attributes for admin ui
        if (WCCF_FB::is_backend()) {
            $attributes[] = 'required';
        }

        // Allow developers to add custom attributes (e.g. placeholder)
        $attributes = apply_filters('wccf_field_attributes', $attributes, $type, $field);

        // Allow developers to add custom attribute values (e.g. placeholder string)
        $params = apply_filters('wccf_field_attribute_values', $params, $type, $field);

        // Extract attributes and append to html string
        foreach ($attributes as $attribute) {
            if (isset($params[$attribute]) && !RightPress_Helper::is_empty($params[$attribute]) && !is_array($params[$attribute])) {
                $html .= $attribute . '="' . htmlspecialchars($params[$attribute]) . '" ';
            }
        }

        // Add a flag indicating that field uses pricing
        if ($field && $field->has_pricing()) {
            $html .= $field->context_is('checkout_field') ? 'data-wccf-checkout-pricing="1" ' : 'data-wccf-pricing="1" ';
        }

        // Add a flag indicating that field is quantity based
        if ($field && $field->is_quantity_based()) {
            $html .= 'data-wccf-quantity-based="1" ';
        }

        // Add field id data attribute
        if ($field) {
            $html .= 'data-wccf-field-id="' . $field->get_id() . '" ';
        }

        // Add min selected data attribute
        if ($field && $field->get_min_selected()) {
            $html .= 'data-wccf-min-selected="' . $field->get_min_selected() . '" ';
        }

        // Add max selected data attribute
        if ($field && $field->get_max_selected()) {
            $html .= 'data-wccf-max-selected="' . $field->get_max_selected() . '" ';
        }

        return $html;
    }

    /**
     * Get options for select field
     *
     * @access public
     * @param array $params
     * @param object $field
     * @param bool $is_grouped
     * @return string
     */
    private static function options($params, $field = null, $is_grouped = false)
    {
        $html = '';
        $selected = array();

        // Get selected option(s)
        if (isset($params['value'])) {
            $selected = (array) $params['value'];
        }
        else if ($default_value = WCCF_FB::get_default_value($field)) {
            $selected = $default_value;
        }

        // Extract options and append to html string
        if (!empty($params['options']) && is_array($params['options'])) {

            // Fix array depth if options are not grouped
            if (!$is_grouped) {
                $params['options'] = array(
                    'wccf_not_grouped' => array(
                        'options' => $params['options'],
                    ),
                );
            }

            // Iterate over option groups
            foreach ($params['options'] as $group_key => $group) {

                // Option group start
                if ($is_grouped) {
                    $html .= '<optgroup label="' . $group['label'] . '">';
                }

                // Iterate over options
                foreach ($group['options'] as $option_key => $option) {

                    // Show pricing information
                    $price_html = '';

                    // Check if field is set
                    if (isset($field)) {

                        // Check if pricing needs to be displayed
                        if (($field->context_is('product_field') && WCCF_Settings::get('prices_product_page')) || ($field->context_is('checkout_field') && WCCF_Settings::get('checkout_field_price_display'))) {

                            // Check if current option has pricing
                            if ($option_pricing = $field->get_option_pricing($option_key)) {

                                // Get pricing string
                                $price_html = WCCF_Pricing::get_pricing_string($option_pricing['pricing_method'], $option_pricing['pricing_value']);
                            }
                        }
                    }

                    // Get option key
                    $option_key = ($is_grouped ? $group_key . '_' . $option_key : $option_key);

                    // Check if option is selected
                    $selected_html = in_array($option_key, $selected) ? 'selected="selected"' : '';

                    // Data attribute
                    $option_data = '';

                    // Special handling of backend field conditions other custom field id options
                    if (WCCF_FB::is_backend() && RightPress_Helper::string_contains_phrase($params['id'], 'wccf_post_config_conditions_other_field_id_')) {

                        // Get field
                        $condition_field = WCCF_Field_Controller::cache($option_key);

                        // Get field type
                        if ($condition_field) {
                            $option_data .= ' data-wccf-condition-other-field-type="' . $condition_field->get_field_type() . '"';
                        }
                    }

                    // Format option html
                    $html .= '<option value="' . $option_key . '" ' . $selected_html . ' ' . $option_data . '>' . $option . $price_html . '</option>';
                }

                // Option group end
                if ($is_grouped) {
                    $html .= '</optgroup>';
                }
            }
        }

        return $html;
    }

    /**
     * Render field label
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return string
     */
    private static function label($params, $field = null)
    {
        echo self::label_html($params, $field);
    }

    /**
     * Get field label html
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return string
     */
    private static function label_html($params, $field = null)
    {
        // Check if label needs to be displayed
        if (!empty($params['id']) && !empty($params['label'])) {

            // Field is required
            $required_html = !empty($params['required']) ? ' <abbr class="required" title="' . __('required', 'rp_wccf') . '">*</abbr>' : '';

            // Display pricing information
            $price_html = '';

            // Check if field has pricing but does not have options
            if (isset($field) && !self::uses_options($field->get_field_type()) && $field->has_pricing()) {

                // Check if pricing information needs to be displayed
                if (($field->context_is('product_field') && WCCF_Settings::get('prices_product_page')) || ($field->context_is('checkout_field') && WCCF_Settings::get('checkout_field_price_display'))) {
                    $price_html = WCCF_Pricing::get_pricing_string($field->get_pricing_method(), $field->get_pricing_value(), true);
                }
            }

            // Build label html
            $html = '<label for="' . $params['id'] . '"><span class="wccf_label">' . $params['label'] . '</span>' . $required_html . $price_html . self::min_max($params, $field) . '</label>';

            // User field labels need special treatment
            if (WCCF_FB::is_backend_user_profile($field)) {
                $html = '<th>' . $html . '</th>';
            }

            // Return label html
            return $html;
        }

        return '';
    }

    /**
     * Maybe display character limit information
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    private static function character_limit($params, $field = null)
    {
        if (!empty($params['maxlength']) || (isset($params['maxlength']) && $params['maxlength'] === '0')) {
            if (apply_filters('wccf_display_character_limit', true, $params, $field)) {
                echo '<small class="wccf_character_limit" style="display: none;"><span class="wccf_characters_remaining">' . $params['maxlength'] . '</span> ' . __('characters remaining', 'rp_wccf') . '</small>';
            }
        }
    }

    /**
     * Maybe print min/max selected and min/max value information
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    private static function min_max($params, $field = null)
    {
        if ($field) {

            $parts = array();

            // Get min selected
            if ($field->get_min_selected() && apply_filters('wccf_display_min_selected', true, $params, $field)) {
                $parts[] = __('min', 'rp_wccf') . ' ' . $field->get_min_selected();
            }

            // Get max selected
            if ($field->get_max_selected() && apply_filters('wccf_display_max_selected', true, $params, $field)) {
                $parts[] = __('max', 'rp_wccf') . ' ' . $field->get_max_selected();
            }

            // Get min value
            if ($field->get_min_value() && apply_filters('wccf_display_min_value', true, $params, $field)) {
                $parts[] = __('min', 'rp_wccf') . ' ' . $field->get_min_value();
            }

            // Get max value
            if ($field->get_max_value() && apply_filters('wccf_display_max_value', true, $params, $field)) {
                $parts[] = __('max', 'rp_wccf') . ' ' . $field->get_max_value();
            }

            // Display min/max limits
            if (!empty($parts)) {
                return '<small class="wccf_min_max_limit">' . join(', ', $parts) . '</small>';
            }
        }
    }

    /**
     * Render field description
     *
     * @access public
     * @param array $params
     * @param object $field
     * @param string $where
     * @return void
     */
    private static function description($params, $field, $where)
    {
        // Determine position
        if ($where === 'before' && apply_filters('wccf_description_before_field', false, $params, $field)) {
            $display = true;
        }
        else if ($where === 'after' && !apply_filters('wccf_description_before_field', false, $params, $field)) {
            $display = true;
        }
        else {
            $display = false;
        }

        // Display description
        if ($display) {
            echo self::description_html($params, $field);
        }
    }

    /**
     * Get field description html
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return string
     */
    private static function description_html($params, $field)
    {
        if (isset($field)) {

            // Get description
            $description = $field->get_description();

            // Check if description is set
            if ($description !== null) {

                // User fields have special handling
                if (WCCF_FB::is_backend_user_profile($field)) {
                    return ' <span class="description">' . $description . '</span>';
                }
                else {
                    return ' <small>' . $description . '</small>';
                }
            }
        }

        return '';
    }

    /**
     * Output frontend conditions
     *
     * @access public
     * @param array $params
     * @param object $field
     * @return void
     */
    private static function frontend_conditions($params, $field)
    {
        // Check if field object is set
        if (!$field) {
            return;
        }

        // Get frontend conditions for this field
        $frontend_conditions = $field->get_frontend_conditions();

        // Check if we have any frontend conditions
        if (!empty($frontend_conditions)) {

            // Get field DOM element id
            $id = $params['id'];

            // Fix field DOM element id for checkbox and radio button fields
            if ($field->field_type_is(array('checkbox', 'radio'))) {
                $option_keys = array_keys($params['options']);
                $id .= '_' . array_shift($option_keys);
            }

            // Fix other custom field conditions for quantity based product fields
            if ($field->context_is('product_field')) {

                // Iterate over frontend conditions
                foreach ($frontend_conditions as $frontend_condition_key => $frontend_condition) {

                    // Only fix other custom field conditions
                    if ($frontend_condition['type'] !== 'custom_field_other_custom_field') {
                        continue;
                    }

                    // Load other field
                    $other_field_id = !empty($frontend_condition['other_field_id']) ? (int) $frontend_condition['other_field_id'] : 0;
                    $other_field = WCCF_Field_Controller::cache($other_field_id);

                    // Unable to load field
                    // Note: ideally we should react to this somehow, e.g. not print the field that we are printing now or so
                    if (!$other_field) {
                        continue;
                    }

                    // Both master and slave fields are quantity based and the field that is being printed is not the first one
                    if ($field->is_quantity_based() && $other_field->is_quantity_based() && !empty($params['quantity_index'])) {
                        $frontend_conditions[$frontend_condition_key]['other_field_id'] = $other_field_id . '_' . $params['quantity_index'];
                    }
                }
            }

            // Pass both conditions and context string
            $data = array(
                'context'       => $field->get_context(),
                'conditions'    => $frontend_conditions,
            );

            // Output script element
            echo '<script type="text/javascript" style="display: none;">var wccf_conditions_' . $id . ' = ' . json_encode($data) . ';</script>';
        }
    }

    /**
     * Output field based on context
     *
     * @access public
     * @param array $params
     * @param string $field_html
     * @param object $field
     * @param string $type
     * @param bool $print_placeholder_input
     * @param bool $is_date
     * @return void
     */
    private static function output($params, $field_html, $field, $type, $print_placeholder_input = false, $is_date = false)
    {
        // Open container
        self::output_begin($params, $field, $type, $is_date);

        // Print frontend conditions
        self::frontend_conditions($params, $field);

        // Print label
        self::label($params, $field);

        // User field treatment
        if (WCCF_FB::is_backend_user_profile($field)) {
            echo '<td class="wccf_user_profile_field_td">';
        }

        // Print description before field
        self::description($params, $field, 'before');

        // Treat file upload fields
        if ($type === 'file' && isset($field)) {

            // Print current file download link
            if (!empty($params['value'])) {

                // Maybe display left border
                $left_border = count($params['value']) > 1 ? 'wccf_file_upload_left_border' : '';

                // Open container
                echo '<div class="wccf_file_upload_list ' . $left_border . '">';

                // Iterate over files
                foreach ($params['value'] as $access_key) {

                    // Open single file container
                    echo '<small class="wccf_file_upload_item">';

                    // Print file name with download link
                    WCCF_Files::print_file_download_link_html($access_key);

                    // Print delete icon
                    echo ' <span class="wccf_file_upload_delete">[x]</span>';

                    // Print hidden field with existing file data
                    $hidden_field_name = str_replace('wccf_ignore', 'wccf', $params['name']);
                    $hidden_field_name .= preg_match('/\[\]$/i', $hidden_field_name) ? '' : '[]';
                    echo '<input type="hidden" class="_' . $params['id'] . '" name="' . $hidden_field_name . '" value="' . $access_key . '" data-wccf-file-access-key="' . $access_key . '">';

                    // Close single file container
                    echo '</small>';
                }

                // Close container
                echo '</div>';
            }
        }

        // Maybe print hidden placeholder input so that empty fields of all types are always passed to server in $_POST
        if ($field && $print_placeholder_input) {
            self::print_placeholder_input($params);
        }

        // Print field
        echo $field_html;

        // Print character limit information
        self::character_limit($params, $field);

        // Print description after field
        self::description($params, $field, 'after');

        // User field treatment
        if (WCCF_FB::is_backend_user_profile($field)) {
            echo '</td>';
        }

        // Close container
        self::output_end($field, $type);
    }

    /**
     * Output container begin
     *
     * @access public
     * @param array $params
     * @param object $field
     * @param string $type
     * @param bool $is_date
     * @return void
     */
    private static function output_begin($params, $field, $type, $is_date = false)
    {
        $date_class = $is_date ? 'wccf_date_container' : '';

        // Hide fields that depend on other fields (have frontend conditions)
        $display_none_html = ($field && $field->has_frontend_conditions()) ? ' style="display: none;" ' : '';

        // Get field context
        $context = isset($field) ? $field->get_context() : false;

        // Product Fields, Checkout Fields, User Fields in frontend
        if (in_array($context, array('product_field', 'checkout_field')) || ($context === 'user_field' && !WCCF_FB::is_backend())) {
            echo '<div class="wccf_field_container wccf_field_container_' . $context . ' wccf_field_container_' . $type . ' wccf_field_container_' . $context . '_' . $type . ' ' . $date_class . '"' . $display_none_html . '>';
        }
        // Product Properties, Order Fields, User Fields in backend but not on user edit page
        else if (in_array($context, array('product_prop', 'order_field')) || ($context === 'user_field' && !WCCF_FB::is_backend_user_profile($field))) {
            echo '<div class="wccf_meta_box_field_container wccf_' . $context . '_field_container ' . $date_class . '"' . $display_none_html . '>';
        }
        // User Fields on user edit page
        else if (WCCF_FB::is_backend_user_profile($field)) {
            echo '<tr class="user-' . $params['id'] . '-wrap wccf_user_profile_field_container wccf_' . $context . '_field_container ' . $date_class . '"' . $display_none_html . '">';
        }
    }

    /**
     * Output container end
     *
     * @access public
     * @param object $field
     * @param string $type
     * @return void
     */
    private static function output_end($field, $type)
    {
        if (isset($field)) {

            // User field
            if (WCCF_FB::is_backend_user_profile($field)) {
                echo '</tr>';
            }
            // All other fields
            else {
                echo '</div>';
            }
        }
    }

    /**
     * Print hidden placeholder input so that empty fields of all types are always passed to server in $_POST
     * By default if value for multiselect, checkbox or radio button field is not selected, field name can't be found in $_POST
     *
     * @access protected
     * @param array $params
     * @return void
     */
    public static function print_placeholder_input($params)
    {
        echo '<input type="hidden" name="' . $params['name'] . '" id="_' . $params['id'] . '" value="">';
    }

    /**
     * Check if field type uses options
     *
     * @access public
     * @param string $type
     * @return bool
     */
    public static function uses_options($type)
    {
        return in_array($type, array('select', 'multiselect', 'checkbox', 'radio'), true);
    }

    /**
     * Validate text field value
     *
     * Throws exception if validation fails
     *
     * @access public
     * @param string $value
     * @return bool
     */
    public static function validate_text($value)
    {
        // Value must be string
        if (gettype($value) !== 'string') {
            throw new Exception(__('must be a valid text string', 'rp_wccf'));
        }

        return true;
    }

    /**
     * Validate textarea field value
     *
     * Throws exception if validation fails
     *
     * @access public
     * @param string $value
     * @return bool
     */
    public static function validate_textarea($value)
    {
        // Value must be valid text value
        self::validate_text($value);

        return true;
    }

    /**
     * Validate password field value
     *
     * Throws exception if validation fails
     *
     * @access public
     * @param string $value
     * @return bool
     */
    public static function validate_password($value)
    {
        // Value must be valid text value
        self::validate_text($value);

        return true;
    }

    /**
     * Validate email field value
     *
     * Throws exception if validation fails
     *
     * @access public
     * @param string $value
     * @return bool
     */
    public static function validate_email($value)
    {
        // Value must be valid text value
        self::validate_text($value);

        // Value must be valid email
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new Exception(__('must be a valid email address', 'rp_wccf'));
        }

        return true;
    }

    /**
     * Validate number field value
     *
     * Throws exception if validation fails
     *
     * @access public
     * @param string $value
     * @return bool
     */
    public static function validate_number($value)
    {
        // Value must be either string, integer or float and must be numeric
        if (!in_array(gettype($value), array('string', 'integer', 'double')) || !is_numeric($value)) {
            throw new Exception(__('must be a valid numeric value', 'rp_wccf'));
        }

        return true;
    }

    /**
     * Validate date field value
     *
     * Throws exception if validation fails
     *
     * @access public
     * @param string $value
     * @return bool
     */
    public static function validate_date($value)
    {
        // Value must be date
        if (!RightPress_Helper::is_date($value, WCCF_Settings::get('date_format', true))) {
            throw new Exception(__('must be a valid date', 'rp_wccf'));
        }

        return true;
    }

    /**
     * Validate select field value
     *
     * Throws exception if validation fails
     *
     * @access public
     * @param string $value
     * @param object $field
     * @return bool
     */
    public static function validate_select($value, $field)
    {
        return self::validate_select_or_radio($value, $field);
    }

    /**
     * Validate radio field value
     *
     * Throws exception if validation fails
     *
     * @access public
     * @param string $value
     * @param object $field
     * @return bool
     */
    public static function validate_radio($value, $field)
    {
        return self::validate_select_or_radio($value, $field);
    }

    /**
     * Validate select or radio field value
     *
     * Throws exception if validation fails
     *
     * @access public
     * @param string $value
     * @param object $field
     * @return bool
     */
    public static function validate_select_or_radio($value, $field)
    {
        // Value must be a string and must validate against predefined options
        if (gettype($value) !== 'string' || !self::validate_value_against_options($value, $field->get_options_list())) {
            throw new Exception(__('is not one of the provided values', 'rp_wccf'));
        }

        return true;
    }

    /**
     * Validate multiselect field value
     *
     * Throws exception if validation fails
     *
     * @access public
     * @param mixed $value
     * @param object $field
     * @return bool
     */
    public static function validate_multiselect($value, $field)
    {
        return self::validate_multiselect_or_checkbox($value, $field);
    }

    /**
     * Validate checkbox field value
     *
     * Throws exception if validation fails
     *
     * @access public
     * @param mixed $value
     * @param object $field
     * @return bool
     */
    public static function validate_checkbox($value, $field)
    {
        return self::validate_multiselect_or_checkbox($value, $field);
    }

    /**
     * Validate multiselect or checkbox field value
     *
     * Throws exception if validation fails
     *
     * @access public
     * @param mixed $value
     * @param object $field
     * @return bool
     */
    public static function validate_multiselect_or_checkbox($value, $field)
    {
        // Cast value to array
        $values = (array) $value;

        // Track validation of each value
        $validation_passed = true;

        // Iterate over values
        foreach ($values as $value) {

            // Each value must be string
            if (gettype($value) !== 'string') {
                $validation_passed = false;
                break;
            }

            // Validate value against options
            if (!self::validate_value_against_options($value, $field->get_options_list())) {
                $validation_passed = false;
                break;
            }
        }

        if (!$validation_passed) {
            throw new Exception(__('is not one of the provided values', 'rp_wccf'));
        }

        return true;
    }

    /**
     * Validate field value against options
     *
     * @access public
     * @param string $value
     * @param array $options
     * @return bool
     */
    public static function validate_value_against_options($value, $options)
    {
        // Field must have options defined
        if (empty($options) || !is_array($options)) {
            return false;
        }

        // Track if match is found in field options
        $match_found = false;

        // Iterate over field options
        foreach ($options as $option_key => $option_label) {

            // Check if value matches option key
            if ($value === $option_key) {
                $match_found = true;
                break;
            }
        }

        if (!$match_found) {
            return false;
        }

        return true;
    }

    /**
     * Validate file field value
     *
     * Throws exception if validation fails
     *
     * @access public
     * @param string $value
     * @param object $field
     * @param int $quantity_index
     * @param mixed $item
     * @return bool
     */
    public static function validate_file($value, $field, $quantity_index = null, $item = null)
    {
        // Get file extension
        $file_extension = strtolower(pathinfo($value['name'], PATHINFO_EXTENSION));

        // Get extension lists
        $extension_whitelist = apply_filters('wccf_file_extension_whitelist', WCCF_Settings::get('file_extension_whitelist'));
        $extension_blacklist = apply_filters('wccf_file_extension_blacklist', WCCF_Settings::get('file_extension_blacklist'));

        // Check against whitelist
        if (!empty($extension_whitelist) && !in_array($file_extension, $extension_whitelist, true)) {
            throw new Exception(__('contains a file that is not allowed', 'rp_wccf'));
        }

        // Check against blacklist
        if (in_array($file_extension, $extension_blacklist, true)) {
            throw new Exception(__('contains a file that is not allowed', 'rp_wccf'));
        }

        // Get file size
        $file_size = filesize($value['tmp_name']);

        // Get min file size
        $min_file_size = WCCF_Settings::get('min_file_size') ?: 0;
        $min_file_size = apply_filters('wccf_min_file_size', $min_file_size) * 1000;

        // Get max file size
        $max_file_size = WCCF_Settings::get('max_file_size') ?: null;
        $max_file_size = apply_filters('wccf_max_file_size', $max_file_size);
        $max_file_size = $max_file_size !== null ? ($max_file_size * 1000) : null;

        // Get max combined file size for field
        $max_combined_file_size = WCCF_Settings::get('max_combined_file_size_per_field') ?: null;
        $max_combined_file_size = apply_filters('wccf_max_combined_file_size_per_field', $max_combined_file_size);
        $max_combined_file_size = $max_combined_file_size !== null ? ($max_combined_file_size * 1000) : null;

        // Check file size
        if ($file_size < $min_file_size) {
            throw new Exception(__('contains a file that is too small', 'rp_wccf'));
        }
        else if ($max_file_size !== null && $file_size > $max_file_size) {
            throw new Exception(__('contains a file that is too large', 'rp_wccf'));
        }

        // Check combined file size per field
        if ($max_combined_file_size !== null && $field->accepts_multiple_values()) {

            // Add current file size
            $combined_size = $file_size;

            // Load session object
            $session = WCCF_WC_Session::initialize_session();

            // Parse form data
            parse_str(urldecode($_POST['form_data']), $form_data);

            // Get field properties
            $field_context = $field->get_context();
            $field_id_for_name = $quantity_index ? ($field->get_id() . '_' . $quantity_index) : $field->get_id();

            // Get already stored values
            $stored = $item ? $field->get_stored_value($item) : array();

            // Add sizes of previously uploaded files
            if (!empty($form_data['wccf'][$field_context][$field_id_for_name])) {
                foreach ($form_data['wccf'][$field_context][$field_id_for_name] as $file_access_key) {

                    $file_data = false;

                    // Get permanently stored file data from meta
                    if (is_array($stored) && in_array($file_access_key, $stored, true)) {
                        $file_data = maybe_unserialize($field->get_data($item, WCCF_Field::get_file_data_access_key($file_access_key), true));
                    }

                    // Get temporarily stored file data from meta
                    if (!$file_data && $item) {
                        $file_data = maybe_unserialize($field->get_data($item, WCCF_Field::get_temp_file_data_access_key($file_access_key), true));
                    }

                    // Get file data from session
                    if (!$file_data && $session) {
                        $file_data = $session->get(WCCF_Field::get_temp_file_data_access_key($file_access_key), false);
                    }

                    // Check if file data was found
                    if ($file_data) {

                        // Get file path
                        if ($file_path = WCCF_Files::locate_file($file_data['subdirectory'], $file_data['storage_key'])) {

                            // Add file size
                            $combined_size += filesize($file_path);
                        }
                    }
                }
            }

            // Check file size
            if ($combined_size > $max_combined_file_size) {
                throw new Exception(__('combined file size is too large', 'rp_wccf'));
            }
        }

        return true;
    }

    /**
     * Check if fields are being printed in backend
     *
     * @access public
     * @return bool
     */
    public static function is_backend()
    {
        return (is_admin() && (!is_ajax() || (isset($_POST['action']) && $_POST['action'] === 'wccf_get_backend_editing_field')));
    }

    /**
     * Check if fields are being printed in backend new user or user edit page
     *
     * @access public
     * @param object $field
     * @return bool
     */
    public static function is_backend_user_profile($field = null)
    {
        return (is_object($field) && $field->context_is('user_field') && (RightPress_Helper::is_wp_backend_user_edit_page() || RightPress_Helper::is_wp_backend_new_user_page()));
    }

    /**
     * Get default field value
     *
     * @access public
     * @param object $field
     * @return mixed
     */
    public static function get_default_value($field)
    {
        if (!($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['wccf_qv_conf'])) && $field && $default_value = $field->get_default_value()) {
            return $default_value;
        }

        return null;
    }


}
}
