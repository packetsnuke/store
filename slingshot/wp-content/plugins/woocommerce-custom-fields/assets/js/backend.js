/**
 * WooCommerce Custom Fields Plugin Backend Scripts
 */
jQuery(document).ready(function() {

    /**
     * Bulk actions in list view
     */
    if (jQuery('#posts-filter select[name="action"]').length > 0) {

        // Enable Field
        jQuery('<option>').val('wccf_enable_field').text(wccf.labels.enable_field).insertAfter('#posts-filter select[name="action"] option[value="-1"]');
        jQuery('<option>').val('wccf_enable_field').text(wccf.labels.enable_field).insertAfter('#posts-filter select[name="action2"] option[value="-1"]');

        // Disable Field
        jQuery('<option>').val('wccf_disable_field').text(wccf.labels.disable_field).insertAfter('#posts-filter select[name="action"] option[value="wccf_enable_field"]');
        jQuery('<option>').val('wccf_disable_field').text(wccf.labels.disable_field).insertAfter('#posts-filter select[name="action2"] option[value="wccf_enable_field"]');
    }

    /**
     * Duplicate field control
     */
    jQuery('a.wccf_duplicate_field').click(function(e) {

        // Prevent default action
        e.preventDefault();

        // Display field key prompt
        var field_key = prompt(wccf.confirmation.duplicating_field);

        // No value provided
        if (field_key === null || field_key === '') {
            return;
        }

        // Proceed with request
        var redirect_url = jQuery(this).prop('href') + '&wccf_field_key=' + encodeURIComponent(field_key);
        jQuery(location).attr('href', redirect_url);
    });

    /**
     * List tips
     */
    // if (typeof jQuery.fn.tipTip === 'function') {
    //     jQuery('.wccf-tip').tipTip();
    // }

    /**
     * Enable field sorting
     */
    jQuery('table.posts #the-list').sortable({
        items:          'tr',
        handle:         '.wccf_post_sort_handle',
        axis:           'y',
        containment:    jQuery('table.posts #the-list').closest('table'),
        tolerance:      'pointer',
        start: function(event, ui) {
            ui.placeholder.height(ui.helper.outerHeight());
            jQuery('table.posts #the-list').addClass('wccf_post_sorting');
            jQuery('table.posts #the-list').sortable('option', 'grid', [1, jQuery('table.posts #the-list tr').height()]);
        },
        stop: function(event, ui) {
            jQuery('table.posts #the-list').removeClass('wccf_post_sorting');
        },
        helper: function (event, ui) {
            ui.children().each(function() {
                jQuery(this).width(jQuery(this).width());
            });
            return ui;
        },
        update: function(event, ui) {
            jQuery.post(wccf.ajaxurl, {
                action:     'wccf_update_field_sort_order',
                sort_order: jQuery('table.posts #the-list').sortable('serialize')
            });
        }
    });

    /**
     * Change page title to field label
     */
    if (jQuery('#poststuff .wccf_post_settings div.wccf_post_title').length > 0) {

        var post_title = jQuery('#poststuff .wccf_post_settings div.wccf_post_title');
        var h1_elements = jQuery('#wpbody-content .wrap h1');

        if (h1_elements.length === 0) {
            post_title.show();
        }
        else {
            h1_elements.first().replaceWith(post_title.html());
            post_title.remove();
        }
    }

    /**
     * Change field key to lowercase
     */
    jQuery('#wccf_post_config_key').on('keyup change', function(e) {
        jQuery(this).val(jQuery(this).val().toLowerCase());
    });

    /**
     * Update field label and field key
     */
    jQuery.each(['label', 'key'], function(index, field) {

        // Update immediatelly
        if (jQuery('#wccf_post_config_' + field).val() !== '') {
            jQuery('.wccf_field_' + field).html(jQuery('#wccf_post_config_' + field).val());
        }

        // Get placeholder
        var placeholder = field === 'label' ? 'New Field' : 'new_field';
        placeholder = typeof wccf === 'object' && typeof wccf.placeholders[field] !== 'undefined' ? wccf.placeholders[field] : placeholder;

        // Update on change
        jQuery('#wccf_post_config_' + field).on('keyup change', function() {
            var new_value = jQuery(this).val();
            new_value = new_value !== '' ? new_value : placeholder;
            jQuery('.wccf_field_' + field).html(new_value);
        });
    });

    /**
     * Only allow letters, numbers and underscore to be typed into the key field
     */
    jQuery('#wccf_post_config_key').on('keypress', function(e) {
        return restrict_input(e, 'key');
    });

    /**
     * Ensure field key is unique
     */
    var current_unique_field_key_validation_request = null;

    jQuery('#wccf_post_config_key').each(function() {

        var last_field_key_value = null;

        jQuery(this).on('keyup', function(e) {
            last_field_key_value = jQuery(this).val();
            unique_field_key_validation();
        });
        jQuery(this).on('change', function(e) {
            if (jQuery(this).val() === last_field_key_value) {
                return;
            }
            last_field_key_value = jQuery(this).val();
            unique_field_key_validation();
        });
        unique_field_key_validation();
    });

    /**
     * Only allow numbers and dot character to be typed into the pricing value field
     */
    jQuery('#wccf_post_config_pricing_value').on('keypress', function(e) {
        return restrict_input(e, 'float');
    });

    /**
     * Only allow numbers to be typed into the character limit field
     */
    jQuery('#wccf_post_config_character_limit').on('keypress', function(e) {
        return restrict_input(e, 'int');
    });

    /**
     * Restrict input to specified characters
     */
    function restrict_input(e, allow)
    {
        if (allow === 'key') {
            var allowable_characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_';
        }
        else if (allow === 'float') {
            var allowable_characters = '1234567890.';
        }
        else if (allow === 'int') {
            var allowable_characters = '1234567890';
        }
        else {
            var allowable_characters = '';
        }

        var k = document.all ? parseInt(e.keyCode) : parseInt(e.which);

        if (k !== 13 && k !== 8 && k !== 0) {
            if (e.ctrlKey === false && e.altKey === false) {
                return (allowable_characters.indexOf(String.fromCharCode(k)) !== -1);
            }
            else {
                return true;
            }
        }
        else {
            return true;
        }
    }

    /**
     * Disable field types that current field type can't be changed to
     */
    jQuery('#wccf_post_config_original_field_type').each(function() {

        var original_type = jQuery(this).val();

        // Check if this is an existing object
        if (original_type !== '') {

            var interchangeable_with = (typeof wccf === 'object' ? wccf.interchangeable_fields[original_type] : []);

            // Iterate over all options
            jQuery('#wccf_post_config_field_type').find('option').each(function() {
                if (jQuery.inArray(jQuery(this).val(), interchangeable_with) === -1) {
                    jQuery(this).prop('disabled', 'disabled');
                }
            });
        }
    });

    /**
     * Disable sorting of meta boxes
     */
    jQuery('.meta-box-sortables').sortable({
        disabled: true
    });

    /**
     * jQuery UI Buttonsets
     */
    jQuery('#poststuff .wccf_post_buttonset').buttonset().css('display', 'block');

    /**
     * Required field change
     */
    jQuery('#wccf_post_config_required_0, #wccf_post_config_required_1').change(function() {
        toggle_min_fields_visibility();
    });

    /**
     * Conditions meta box switching
     */
    jQuery('#poststuff .wccf_post .wccf_post_config_conditional').each(function() {
        jQuery(this).change(function() {
            toggle_conditions_visibility(jQuery(this));
        });
        toggle_conditions_visibility(jQuery(this));
    });

    /**
     * Toggle conditions visibility
     */
    function toggle_conditions_visibility(field)
    {
        var meta_box = jQuery('#poststuff .wccf_field_conditions_meta_box');

        if (jQuery('#poststuff .wccf_post_config_conditional:checked').val() === '1') {
            meta_box.show();
        }
        else {
            meta_box.hide();
            clear_items('condition');
        }
    }

    /**
     * Pricing meta box switching
     */
    jQuery('#poststuff .wccf_post .wccf_post_config_pricing').each(function() {
        jQuery(this).change(function() {
            toggle_price_fields();
        });
        toggle_price_fields();
    });

    /**
     * Set up options and conditions
     */
    jQuery.each(['options', 'conditions'], function(index, type) {

        var type_singular = type.replace(/s$/, '');

        // Select corresponding list
        jQuery('#poststuff .wccf_post_' + type + '_list').each(function() {

            var list = jQuery(this);

            // Check if any items exist in config
            if (typeof wccf_fb === 'object' && typeof wccf_fb[type] === 'object' && wccf_fb[type].length > 0) {
                for (var key in wccf_fb[type]) {
                    add(type_singular, list, wccf_fb[type], key);
                }

                // Fix field ids, names and values
                fix_fields(type_singular);
                fix_field_values(type_singular, wccf_fb[type]);

                // Fix elements of conditions
                if (type === 'conditions') {
                    jQuery(this).find('.wccf_post_condition').each(function() {
                        fix_condition(jQuery(this));
                    });
                }
            }

            // Bind click action
            list.closest('.wccf_post').find('.wccf_post_add_' + type_singular + ' button').click(function() {
                add(type_singular, list, false, false);
            });
        });
    });

    /**
     * Handle Field Type change
     */
    jQuery('#poststuff .wccf_post #wccf_post_config_field_type').each(function() {
        jQuery(this).change(function() {
            type_changed(jQuery(this));
        });
        type_changed(jQuery(this));
    });

    /**
     * Handle Display As change
     */
    jQuery('#poststuff .wccf_post #wccf_post_config_display_as').each(function() {
        jQuery(this).change(function() {
            display_as_changed(jQuery(this));
        });
        display_as_changed(jQuery(this));
    });

    /**
     * Add no options, conditions etc (var type) notice
     */
    function add_no(type, list)
    {
        prepend(list, 'no_' + type);
    }

    /**
     * Remove No Options, No Conditions etc (var type) notice
     */
    function remove_no(type, list)
    {
        list.find('.wccf_post_no_' + type).remove();
    }

    /**
     * Add one option, condition etc (var type)
     */
    function add(type, list, config, key)
    {
        // Add wrapper
        add_wrapper(type, list);

        // Make sure we don't have the No Options, No Conditions etc notice
        remove_no(type + 's', list);

        // Add element
        append(list.find('.wccf_post_' + type + '_wrapper'), type, null);

        // Select last item
        var last_item = list.find('.wccf_post_' + type).last();

        // Fix field values for new items
        if (config === false) {

            // Fix field ids, names and values
            fix_fields(type);
            fix_field_values(type, config);

            // Fix elements of current condition
            if (type === 'condition') {
                fix_condition(last_item);
            }
        }

        /**
         * Restrict input on some option fields
         */
        if (type === 'option') {

            // Only allow letters, numbers and underscore to be typed into the key field
            last_item.find('.wccf_post_config_options_key').on('keypress', function(e) {
                return restrict_input(e, 'key');
            });

            // Custom validation to ensure unique option keys
            last_item.find('.wccf_post_config_options_key').on('change', function(e) {
                unique_option_key_validation();
            });

            // Only numbers and dot character to be typed into the pricing value field
            last_item.find('.wccf_post_config_pricing_value').on('keypress', function(e) {
                return restrict_input(e, 'float');
            });
        }

        // Handle delete action
        last_item.find('.wccf_post_' + type + '_remove_handle').click(function() {
            remove(type, jQuery(this).closest('.wccf_post_' + type));
        });

        // Make sure only one "Selected" item is set for Select and Radio button set fields
        last_item.find('.wccf_post_config_' + type + 's_selected').change(function() {
            if (jQuery(this).val() === '1' && jQuery.inArray(jQuery('#poststuff #wccf_post_config_field_type').val(), ['select', 'radio']) !== -1) {

                var current_id = jQuery(this).prop('id');

                jQuery(this).closest('.wccf_post_option_wrapper').find('.wccf_post_config_options_selected').each(function() {
                    if (jQuery(this).prop('id') !== current_id) {
                        clear_field_value(jQuery(this));
                    }
                });
            }
        });
    }

    /**
     * Remove one option, condition etc (var type)
     */
    function remove(type, element)
    {
        var list = element.closest('.wccf_post_' + type + 's_list');

        // Last element? Remove the entire wrapper and add No Options, No Conditions etc wrapper
        if (list.find('.wccf_post_' + type + '_wrapper').children().length < 2) {
            remove_wrapper(type, list);
            add_no(type + 's', list);
        }

        // Remove single element and fix ids
        else {
            element.remove();
            fix_fields(type);
        }
    }

    /**
     * Add wrapper for options, conditions etc (var type)
     */
    function add_wrapper(type, list)
    {
        // Make sure we don't have one yet before proceeding
        if (list.find('.wccf_post_' + type + '_wrapper').length === 0) {

            // Add wrapper
            prepend(list, type + '_wrapper', null);

            // Maybe show price fields for type "option"
            if (type === 'option') {
                toggle_price_fields();
            }

            // Make it sortable
            list.find('.wccf_post_' + type + '_wrapper').sortable({
                axis:           'y',
                handle:         '.wccf_post_' + type + '_sort_handle',
                opacity:        0.7,
                containment:    list.find('.wccf_post_' + type + '_wrapper').first(),
                tolerance:      'pointer',
                stop: function(event, ui) {

                    // Remove styles added by jQuery UI
                    jQuery(this).find('.wccf_post_' + type).each(function() {
                        jQuery(this).removeAttr('style');
                    });

                    // Fix ids, names etc
                    fix_fields(type);
                }
            });
        }
    }

    /**
     * Remove option, condition etc (var type) wrapper
     */
    function remove_wrapper(type, list)
    {
        list.find('.wccf_post_' + type + '_header').remove();
        list.find('.wccf_post_' + type + '_wrapper').remove();
    }

    /**
     * Fix field attributes
     */
    function fix_fields(type)
    {
        var i = 0;

        // Iterate over items
        jQuery('#poststuff .wccf_post .wccf_post_' + type).each(function() {

            // Iterate over all field elements of this item
            jQuery(this).find('input, select').each(function() {

                // ID
                if (typeof jQuery(this).prop('id') !== 'undefined') {
                    var new_value = jQuery(this).prop('id').replace(/(\{i\}|\d+)?$/, i);
                    jQuery(this).prop('id', new_value);
                }

                // Name
                if (typeof jQuery(this).prop('name') !== 'undefined') {
                    var new_value = jQuery(this).prop('name').replace(/^wccf_post_config\[(options|conditions)\]\[(\{i\}|\d+)\]?/, 'wccf_post_config[' + type + 's][' + i + ']');
                    jQuery(this).prop('name', new_value);
                }
            });

            // Increment item identifier
            i++;
        });
    }

    /**
     * Fix field values
     */
    function fix_field_values(type, config)
    {
        var i = 0;
        var type_plural = type + 's';

        // Iterate over items
        jQuery('#poststuff .wccf_post .wccf_post_' + type).each(function() {

            // Iterate over all field elements of this item
            jQuery(this).find('input, select').each(function() {

                // Get field key
                var field_key = jQuery(this).prop('id').replace(new RegExp('wccf_post_config_' + type_plural + '_'), '').replace(/(_\d+)?$/, '');

                // Select options in select fields
                if (jQuery(this).is('select')) {
                    if (config && typeof config[i] !== 'undefined' && typeof config[i][field_key] !== 'undefined' && config[i][field_key]) {
                        if (is_multiselect(jQuery(this))) {
                            if (typeof wccf_fb_multiselect_options !== 'undefined' && typeof wccf_fb_multiselect_options[type_plural] !== 'undefined' && typeof wccf_fb_multiselect_options[type_plural][i] !== 'undefined' && typeof wccf_fb_multiselect_options[type_plural][i][field_key] === 'object') {
                                for (var k = 0; k < wccf_fb[type_plural][i][field_key].length; k++) {
                                    var all_options = wccf_fb_multiselect_options[type_plural][i][field_key];
                                    var current_option_key = wccf_fb[type_plural][i][field_key][k];

                                    for (var l = 0; l < all_options.length; l++) {
                                        if (typeof all_options[l] !== 'undefined' && typeof all_options[l]['id'] !== 'undefined' && all_options[l]['id'] == current_option_key) {
                                            var current_option_label = all_options[l]['text'];
                                            jQuery(this).append(jQuery('<option></option>').attr('value', current_option_key).prop('selected', true).text(current_option_label));
                                        }
                                    }
                                }
                            }
                        }
                        else {
                            jQuery(this).val(config[i][field_key]);
                        }
                    }
                }

                // Check checkboxes
                else if (jQuery(this).is(':checkbox')) {
                    if (config && typeof config[i] !== 'undefined' && typeof config[i][field_key] !== 'undefined' && config[i][field_key]) {
                        jQuery(this).prop('checked', true);
                    }
                }

                // Add value for text input fields
                else {
                    if (config && typeof config[i] !== 'undefined' && typeof config[i][field_key] !== 'undefined') {
                        jQuery(this).prop('value', config[i][field_key]);
                    }
                }

                // Initialize select2
                if (jQuery(this).hasClass('wccf_post_select2')) {
                    initialize_select2(jQuery(this), field_key);
                }
            });

            // Toogle price fields for options
            toggle_price_fields();

            // Increment item identifier
            i++;
        });
    }

    /**
     * Initialize select2 on one element
     */
    function initialize_select2(element, key)
    {
        // Currently only multiselect fields are converted
        if (!is_multiselect(element)) {
            return;
        }

        // Make sure our Select2 reference is set
        if (typeof RP_Select2 === 'undefined') {
            return;
        }

        // Initialize Select2
        RP_Select2.call(element, {
            width: '100%',
            minimumInputLength: 1,
            ajax: {
                url:        wccf.ajaxurl,
                type:       'POST',
                dataType:   'json',
                delay:      250,
                data: function(params) {
                    return {
                        query:      params.term,
                        action:     'wccf_load_multiselect_items',
                        type:       key,
                        selected:   element.val()
                    };
                },
                dataFilter: function(raw_response) {
                    return parse_ajax_json_response(raw_response, true);
                },
                processResults: function(data, page) {
                    return {
                        results: data.items
                    };
                }
            }
        });
    }

    /**
     * Field type setting value changed
     */
    function type_changed(type_field)
    {
        var value = type_field.val();
        var options_meta_box = jQuery('#poststuff .wccf_field_options_meta_box');

        // Select, multiselect, checkbox and radio field types - show options
        if (jQuery.inArray(value, ['select', 'multiselect', 'checkbox', 'radio']) !== -1) {

            // Ensure that only one Selected item is selected if field type is Select or Radio buttons
            if (jQuery.inArray(value, ['select', 'radio']) !== -1) {

                var found_selected = false;

                jQuery('#poststuff .wccf_post_option .wccf_post_config_options_selected').each(function() {
                    if (jQuery(this).val() === '1') {
                        if (found_selected) {
                            clear_field_value(jQuery(this));
                        }
                        else {
                            found_selected = true;
                        }
                    }
                });
            }

            // Show options UI
            options_meta_box.show();
        }
        else {
            options_meta_box.hide();
            clear_items('option');
        }

        // Toggle default value and character limit field visibility
        jQuery('#poststuff #wccf_post_config_character_limit, #poststuff #wccf_post_config_default_value').each(function() {

            var config_field = jQuery(this).closest('.wccf_config_field');

            if (jQuery.inArray(value, ['text', 'textarea', 'password', 'email', 'number']) !== -1) {
                enable_field(jQuery(this));
                config_field.show();
            }
            else {
                config_field.hide();
                jQuery(this).val('');
                disable_field(jQuery(this));
            }

            // Change field type for default value field
            if (jQuery(this).prop('id') === 'wccf_post_config_default_value') {

                // Email
                if (value === 'email' && jQuery(this).prop('type') !== 'email') {
                    jQuery(this).prop('type', 'email');
                }
                // Number
                else if (value === 'number' && jQuery(this).prop('type') !== 'number') {
                    jQuery(this).prop('type', 'number');
                }
                // Regular input
                else if (value !== 'email' && value !== 'number' && jQuery(this).prop('type') !== 'text') {
                    jQuery(this).prop('type', 'text');
                }
            }
        });

        // Toggle min selected and min value field visibility
        toggle_min_fields_visibility();

        // Toggle max selected field visibility
        jQuery('#poststuff #wccf_post_config_max_selected').each(function() {

            var config_field = jQuery(this).closest('.wccf_config_field');

            if (jQuery.inArray(value, ['checkbox', 'multiselect']) !== -1) {
                enable_field(jQuery(this));
                config_field.show();
            }
            else {
                config_field.hide();
                jQuery(this).val('');
                disable_field(jQuery(this));
            }
        });

        // Toggle max value field visibility
        jQuery('#poststuff #wccf_post_config_max_value').each(function() {

            var config_field = jQuery(this).closest('.wccf_config_field');

            if (jQuery.inArray(value, ['number']) !== -1) {
                enable_field(jQuery(this));
                config_field.show();
            }
            else {
                config_field.hide();
                jQuery(this).val('');
                disable_field(jQuery(this));
            }
        });

        // Toggle advanced pricing option visibility
        jQuery('#poststuff .wccf_post_config_pricing_method').each(function() {

            var fee_per_character_option = jQuery(this).find('option[value="advanced_fees_fee_per_character"]');
            var fee_x_value_option = jQuery(this).find('option[value="advanced_fees_fee_x_value"]');

            // Show both
            if (value === 'number') {
                fee_per_character_option.closest('optgroup').show();
                fee_per_character_option.prop('disabled', false);
                fee_x_value_option.prop('disabled', false);
                fee_per_character_option.show();
                fee_x_value_option.show();
            }
            // Hide both
            else if (jQuery.inArray(value, ['text', 'textarea', 'password', 'email', 'number']) === -1) {

                // Reset options if selected
                if (jQuery(this).val() === 'advanced_fees_fee_per_character' || jQuery(this).val() === 'advanced_fees_fee_x_value') {
                    jQuery(this).prop('selectedIndex', 0);
                }

                fee_per_character_option.hide();
                fee_x_value_option.hide();
                fee_per_character_option.prop('disabled', true);
                fee_x_value_option.prop('disabled', true);
                fee_per_character_option.closest('optgroup').hide();
            }
            // Show fee_per_character and hide fee_x_value
            else {

                fee_per_character_option.closest('optgroup').show();
                fee_per_character_option.prop('disabled', false);
                fee_per_character_option.show();

                // Reset option if selected
                if (jQuery(this).val() === 'advanced_fees_fee_x_value') {
                    jQuery(this).prop('selectedIndex', 0);
                }

                fee_x_value_option.hide();
                fee_x_value_option.prop('disabled', true);
            }
        });

        // Toggle price fields
        toggle_price_fields();
    }

    /**
     * Toggle min selected and min value field visibility
     */
    function toggle_min_fields_visibility()
    {
        jQuery.each(['min_selected', 'min_value'], function(index, field_key) {

            var min = jQuery('#wccf_post_config_' + field_key);
            var config_field = min.closest('.wccf_config_field');

            var field_type = jQuery('#wccf_post_config_field_type').val();
            var is_required = jQuery('input#wccf_post_config_required_1').is(':checked');

            var allowed_types = (field_key === 'min_selected') ? ['checkbox', 'multiselect'] : ['number'];

            if (jQuery.inArray(field_type, allowed_types) !== -1 && is_required) {
                enable_field(min);
                config_field.show();
            }
            else {
                config_field.hide();
                min.val('');
                disable_field(min);
            }
        });
    }

    /**
     * Display As setting value changed
     */
    function display_as_changed(display_as_field)
    {
        var value = display_as_field.val();

        // Take reference of the checkout position field
        var checkout_position_field = jQuery('#poststuff .wccf_post #wccf_post_config_position');

        // Maybe reset field value
        var selected_option = checkout_position_field.val();
        var reset_value = ((value === 'billing_address' && selected_option.indexOf('_billing') === -1) || (value === 'shipping_address' && selected_option.indexOf('_shipping') === -1));

        // Iterate over checkout position options
        checkout_position_field.find('option').each(function() {

            // Get current option
            var current_option = jQuery(this).prop('value');

            // Display all options
            if (value === 'user_profile') {
                if (jQuery(this).prop('disabled')) {
                    jQuery(this).prop('disabled', false);
                    jQuery(this).show();
                    reset_value = true;
                }
            }
            // Display only billing address options
            else if (value === 'billing_address') {

                // Show
                if (current_option.indexOf('_billing') !== -1) {
                    jQuery(this).prop('disabled', false);
                    jQuery(this).show();
                }
                // Hide
                else {
                    jQuery(this).prop('disabled', true);
                    jQuery(this).hide();
                }
            }
            // Display only shipping address options
            else if (value === 'shipping_address') {

                // Show
                if (current_option.indexOf('_shipping') !== -1) {
                    jQuery(this).prop('disabled', false);
                    jQuery(this).show();
                }
                // Hide
                else {
                    jQuery(this).prop('disabled', true);
                    jQuery(this).hide();
                }
            }
        });

        // Reset field value
        if (reset_value) {
            checkout_position_field.val(checkout_position_field.find('option:enabled').first().val());
        }
    }

    /**
     * Clear items - options or conditions
     */
    function clear_items(type_singular)
    {
        var list = jQuery('#poststuff .wccf_post .wccf_post_' + type_singular + 's_list').first();

        // Check if any items exist first
        if (list.find('.wccf_post_no_' + type_singular + 's').length === 0) {
            remove_wrapper(type_singular, list);
            add_no(type_singular + 's', list);
        }
    }

    /**
     * Fix condition
     */
    function fix_condition(element)
    {
        // Condition type
        element.find('.wccf_condition_type').change(function() {
            toggle_condition_fields(element);
        });
        toggle_condition_fields(element);

        // Other custom field condition field
        element.find('.wccf_condition_other_field_id').change(function() {
            fix_other_custom_field_methods(element);
        });
        fix_other_custom_field_methods(element);

        // Other custom field condition
        element.find('.wccf_condition_method').change(function() {
            fix_other_custom_field_value(element);
        });
        fix_other_custom_field_value(element);
    }

    /**
     * Toggle visibility of condition fields
     */
    function toggle_condition_fields(element)
    {
        // Get current condition type
        var current_type = element.find('.wccf_condition_type').val();

        // Show only fields related to current type
        element.find('.wccf_post_condition_setting_fields').each(function() {

            // Show or hide fields
            var display = jQuery(this).hasClass('wccf_post_condition_setting_fields_' + current_type) ? 'block' : 'none';
            jQuery(this).css('display', display);

            // Clear values and disable fields
            if (display === 'none') {
                clear_field_values(jQuery(this));
                disable_fields(jQuery(this));
            }
            // Enable fields
            else {
                enable_fields(jQuery(this));
            }
        });

        // Fix other custom field value input
        fix_other_custom_field_value(element);
    }

    /**
     * Fix other custom field condition methods
     */
    function fix_other_custom_field_methods(element)
    {
        // Get selected field type
        var selected_field_type = element.find('.wccf_condition_other_field_id option:selected').data('wccf-condition-other-field-type');

        // Check if we can determine field type
        if (typeof selected_field_type === 'undefined' || !selected_field_type || typeof wccf.other_field_condition_methods_by_type[selected_field_type] === undefined) {
            return;
        }

        // Get supported condition methods
        var supported_methods = wccf.other_field_condition_methods_by_type[selected_field_type];

        // Check if we need to reset selection
        var reset = false;

        // Iterate over method field options
        element.find('.wccf_post_condition_setting_fields_custom_field_other_custom_field .wccf_condition_method option').each(function() {
            if (jQuery.inArray(jQuery(this).val(), supported_methods) !== -1) {
                jQuery(this).prop('disabled', false).show();
            }
            else {
                reset = reset ? reset : jQuery(this).is(':selected');
                jQuery(this).prop('disabled', true).hide();
            }
        });

        // Reset selection if needed
        if (reset) {
            clear_field_value(element.find('.wccf_condition_method'));
            fix_other_custom_field_value(element);
        }
    }

    /**
     * Fix fields of other_custom_field condition
     */
    function fix_other_custom_field_value(element)
    {
        // Get current method
        var other_custom_field = element.find('.wccf_post_condition_setting_fields_custom_field_other_custom_field');
        var current_method = other_custom_field.find('.wccf_condition_method').val();
        var text_field = other_custom_field.find('.wccf_conditions_text');

        // Proceed depending on current method
        if (jQuery.inArray(current_method, ['is_empty', 'is_not_empty', 'is_checked', 'is_not_checked']) !== -1) {
            other_custom_field.find('input, select').not('.wccf_condition_other_field_id').parent().removeClass('wccf_post_condition_setting_fields_single').addClass('wccf_post_condition_setting_fields_double');
            text_field.parent().css('display', 'none');
            clear_field_value(text_field);
            disable_field(text_field);
        }
        else {
            other_custom_field.find('input, select').parent().removeClass('wccf_post_condition_setting_fields_double').addClass('wccf_post_condition_setting_fields_single');
            text_field.parent().css('display', 'block');
            enable_field(text_field);
        }
    }

    /**
     * Toggle visibility of option price fields
     */
    function toggle_price_fields()
    {
        // Check if pricing is available for this object
        if (!jQuery('#poststuff .wccf_post_config_pricing').length || !jQuery('#poststuff .wccf_field_pricing_meta_box').length) {
            toggle_price_fields_options('hide');
            return;
        }

        var pricing_enabled = (jQuery('#poststuff .wccf_post_config_pricing:checked').val() === '1');
        var field_type = jQuery('#poststuff #wccf_post_config_field_type').val();
        var field_type_has_options = (jQuery.inArray(field_type, ['select', 'multiselect', 'checkbox', 'radio']) !== -1);

        // Show pricing in options
        if (pricing_enabled && field_type_has_options) {
            toggle_price_fields_options('show');
            toggle_price_fields_meta_box('hide');
            toggle_checkout_fee_tax_class_option('show');
        }
        // Show pricing in meta box
        else if (pricing_enabled) {
            toggle_price_fields_meta_box('show');
            toggle_price_fields_options('hide');
            toggle_checkout_fee_tax_class_option('show');
        }
        // Hide pricing in options
        else {
            toggle_price_fields_options('hide');
            toggle_price_fields_meta_box('hide');
            toggle_checkout_fee_tax_class_option('hide');
        }
    }

    /**
     * Toggle visibility of pricing fields in options
     */
    function toggle_price_fields_options(visibility)
    {
        var display = (visibility === 'show' ? 'block' : 'none');
        var meta_box = jQuery('#poststuff .wccf_field_options_meta_box');

        meta_box.find('.wccf_post_option_wrapper .wccf_post_option').each(function() {

            var option_price = jQuery(this).find('.wccf_post_option_price');

            // Show or hide pricing for this row
            option_price.css('display', display);

            // Clear values and disable fields
            if (visibility === 'hide') {
                clear_field_values(option_price);
                disable_fields(option_price);
            }
            // Enable fields
            else {
                enable_fields(option_price);
            }
        });

        // Show or hide pricing header
        meta_box.find('.wccf_post_option_content_header .wccf_post_option_price').css('display', display);

        // Resize other fields
        var new_size = visibility === 'show' ? '31%' : '46.5%';
        meta_box.find('.wccf_post_option_resize').css('width', new_size);
    }

    /**
     * Toggle visibility of pricing meta box
     */
    function toggle_price_fields_meta_box(visibility)
    {
        var display = (visibility === 'show' ? 'block' : 'none');
        var meta_box = jQuery('#poststuff .wccf_field_pricing_meta_box');

        // Show or hide pricing meta box
        meta_box.css('display', display);

        // Clear values and disable fields
        if (visibility === 'hide') {
            clear_field_values(meta_box);
            disable_fields(meta_box);
        }
        // Enable fields
        else {
            enable_fields(meta_box);
        }
    }

    /**
     * Toggle visibility of tax class option for checkout fields
     */
    function toggle_checkout_fee_tax_class_option(visibility)
    {
        var display = (visibility === 'show' ? 'block' : 'none');
        var tax_class_container = jQuery('select#wccf_post_config_tax_class').closest('.wccf_config_field');

        if (tax_class_container.length > 0) {

            // Show/hide container
            tax_class_container.css('display', display);

            // Clear value and disable field
            if (visibility === 'hide') {
                clear_field_values(tax_class_container);
                disable_fields(tax_class_container);
            }
            // Enable field
            else {
                enable_fields(tax_class_container);
            }
        }
    }

    /**
     * Custom field key validation
     */
    function unique_field_key_validation()
    {
        // Select key input field
        var input = jQuery('#wccf_post_config_key');
        var input_dom = input[0];

        // Get value
        var value = input.val();

        // Do nothing if input is empty (default error will be displayed)
        if (value === '') {
            input_dom.setCustomValidity('');
            return;
        }
        // Set error message while waiting for Ajax response
        else {
            input_dom.setCustomValidity(wccf.error_messages.field_key_must_be_unique);
        }

        // Send Ajax request
        current_unique_field_key_validation_request = jQuery.ajax({
            type:   'POST',
            url:    wccf.ajaxurl,
            data:   {
                action:     'wccf_validate_field_key',
                post_id:    input.closest('form').find('input#post_ID').val(),
                post_type:  input.closest('form').find('input#post_type').val(),
                value:      value
            },
            beforeSend: function() {
                if (current_unique_field_key_validation_request !== null) {
                    current_unique_field_key_validation_request.abort();
                }
            },
            success: function(response) {

                // Parse response
                response = parse_ajax_json_response(response);

                // Check response
                if (typeof response === 'object' && typeof response.result !== 'undefined') {

                    // Reset error message if field key is unique
                    if (response.result === 'success') {
                        input_dom.setCustomValidity('');
                    }
                    // Set custom error message if it was returned
                    else if (typeof response.message !== 'undefined') {
                        input_dom.setCustomValidity(response.message);
                    }
                }
            }
        });
    }

    /**
     * Custom option key validation
     */
    function unique_option_key_validation()
    {
        // Track option keys
        var values = [];

        // Iterate over option keys
        jQuery('#poststuff .wccf_post_option input.wccf_post_config_options_key').each(function() {

            // Get value
            var value = jQuery(this).val().toLowerCase();

            // Check if such value exists
            if (jQuery.inArray(value, values) === -1) {
                this.setCustomValidity('');
            }
            else {
                this.setCustomValidity(wccf.error_messages.option_key_must_be_unique);
            }

            // Add value to values array
            values.push(value);
        });
    }

    /**
     * Display hints in settings
     */
    jQuery('.wccf_settings_container .wccf_setting').each(function() {

        // Get hint
        var hint = jQuery(this).prop('title');

        // Check if hint is set
        if (hint) {

            // Append hint element
            jQuery(this).parent().append('<div class="wccf_settings_hint">' + hint + '</div>');
        }
    });

    /**
     * Toggle checkout field single fee label field in plugin settings
     */
    jQuery('input#wccf_display_as_single_fee').change(function() {
        toggle_checkout_single_fee_label_field();
    });
    toggle_checkout_single_fee_label_field();

    function toggle_checkout_single_fee_label_field()
    {
        jQuery('input#wccf_display_as_single_fee').each(function() {
            if (jQuery(this).is(':checked')) {
                jQuery('input#wccf_single_fee_label').closest('tr').show();
            }
            else {
                jQuery('input#wccf_single_fee_label').closest('tr').hide();
            }
        });
    }

    /**
     * Toggle max combined file size
     */
    jQuery('input[name="wccf_settings[wccf_multiple_files]"]').change(function() {
        var display = jQuery(this).is(':checked') ? 'table-row' : 'none';
        jQuery('input[name="wccf_settings[wccf_max_combined_file_size_per_field]"]').closest('tr').css('display', display);
    }).change();

    /**
     * Warn users when deleting fields
     */
    jQuery('#posts-filter #doaction, #posts-filter #doaction2').click(function(e) {

        // Get bulk action
        var action = jQuery('select[name="action"]');
        action = action.length > 0 ? action.val() : null;

        // Trash
        if (action === 'trash') {
            trash_confirmation(e, false);
        }
        // Delete
        else if (action === 'delete') {
            trash_confirmation(e, true);
        }
    });
    jQuery('.row-actions .trash .submitdelete, .wccf_post_delete .submitdelete').click(function(e) {
        var delete_permanently = jQuery(this).hasClass('wccf_delete_permanently');
        trash_confirmation(e, delete_permanently);
    });
    jQuery('.row-actions .delete .submitdelete, #posts-filter input#delete_all').click(function(e) {
        trash_confirmation(e, true);
    });
    function trash_confirmation(event, delete_permanently)
    {
        // Ask to confirm action
        if (typeof wccf !== 'undefined') {

            var message = delete_permanently ? wccf.confirmation.deleting_field : wccf.confirmation.trashing_field;

            if (!confirm(message)) {
                event.preventDefault();
            }
        }
    }

    /**
     * Warn users when archiving fields
     */
    jQuery('.wccf_post_actions button[type="submit"]').click(function(e) {

        // Get action
        var action = jQuery('.wccf_post_action_select select').val();

        // Check if action is archive
        if (action === 'archive') {
            archive_confirmation(e);
        }
    });
    jQuery('.row-actions .archive a').click(function(e) {
        archive_confirmation(e);
    });
    function archive_confirmation(event)
    {
        // Ask to confirm action
        if (typeof wccf !== 'undefined' && !confirm(wccf.confirmation.archiving_field)) {
            event.preventDefault();
        }
    }

    /**
     * Disable submitting archived field edit page
     */
    jQuery('form#post .wccf_post_action_select select option[value="object_archived"]').each(function() {

        // Disable actions meta box elements
        jQuery(this).closest('select').prop('disabled', 'disabled');
        jQuery(this).closest('.wccf_post_actions').find('button[type="submit"]').prop('disabled', 'disabled');

        // Disable form submit completely
        jQuery(this).closest('form#post').submit(function(e) {
            if (typeof wccf !== 'undefined') {
                alert(wccf.error_messages.editing_archived_field);
            }

            e.preventDefault();
        });
    });

    /**
     * Select2 for file extensions settings
     */
    jQuery('select#wccf_file_extension_whitelist, select#wccf_file_extension_blacklist').select2({
        tags: true,
        allowClear: true
    });

    /**
     * Focus on title field when creating new field
     */
    if (window.location.pathname.indexOf('post-new.php') !== -1) {
        jQuery('#wccf_post_config_label').focus();
    }

    /**
     * HELPER
     * Enable all fields contained by element
     */
    function enable_fields(element)
    {
        element.find('input, select').each(function() {
            enable_field(jQuery(this));
        });
    }

    /**
     * HELPER
     * Disable all fields contained by element
     */
    function disable_fields(element)
    {
        element.find('input, select').each(function() {
            disable_field(jQuery(this));
        });
    }

    /**
     * HELPER
     * Enable field
     */
    function enable_field(field)
    {
        field.prop('disabled', false);
    }

    /**
     * HELPER
     * Disable field
     */
    function disable_field(field)
    {
        field.prop('disabled', 'disabled');
    }

    /**
     * HELPER
     * Clear values of multiple fields contained by element
     */
    function clear_field_values(element)
    {
        element.find('input, select').each(function() {
            clear_field_value(jQuery(this));
        });
    }

    /**
     * HELPER
     * Clear field value
     */
    function clear_field_value(field)
    {
        if (field.is('select')) {
            field.prop('selectedIndex', 0);
        }
        else if (field.is(':radio, :checkbox')) {
            field.removeAttr('checked');
        }
        else {
            field.val('');
        }
    }

    /**
     * HELPER
     * Check if HTML element is multiselect field
     */
    function is_multiselect(element)
    {
        return (element.is('select') && typeof element.attr('multiple') !== 'undefined' && element.attr('multiple') !== false);
    }

    /**
     * HELPER
     * Append template with values to selected element's content
     */
    function append(selector, template, values)
    {
        var html = get_template(template, values);

        if (typeof selector === 'object') {
            selector.append(html);
        }
        else {
            jQuery(selector).append(html);
        }
    }

    /**
     * HELPER
     * Prepend template with values to selected element's content
     */
    function prepend(selector, template, values)
    {
        var html = get_template(template, values);

        if (typeof selector === 'object') {
            selector.prepend(html);
        }
        else {
            jQuery(selector).prepend(html);
        }
    }

    /**
     * HELPER
     * Get template's html code
     */
    function get_template(template, values)
    {
        return populate_template(jQuery('#wccf_template_' + template).html(), values);
    }

    /**
     * HELPER
     * Populate template with values
     */
    function populate_template(template, values)
    {
        for (var key in values) {
            template = replace_macro(template, key, values[key]);
        }

        return template;
    }

    /**
     * HELPER
     * Replace all instances of macro in string
     */
    function replace_macro(string, macro, value)
    {
        var macro = '{' + macro + '}';
        var regex = new RegExp(macro, 'g');
        return string.replace(regex, value);
    }

    /**
     * We are done by now, remove preloader
     */
    jQuery('#wccf_preloader').remove();

    /**
     * Parse Ajax JSON response
     */
    function parse_ajax_json_response(response, return_raw_data)
    {
        // Check if we need to return parsed object or potentially fixed raw data
        var return_raw_data = (typeof return_raw_data !== 'undefined') ?  return_raw_data : false;

        try {

            // Attempt to parse data
            var parsed = jQuery.parseJSON(response);

            // Return appropriate value
            return return_raw_data ? response : parsed;
        }
        catch (e) {

            // Attempt to fix malformed JSON string
            var regex = return_raw_data ? /{"result.*"}]}/ : /{"result.*"}/;
            var valid_response = response.match(regex);

            // Check if we were able to fix it
            if (valid_response !== null) {
                response = valid_response[0];
            }
        }

        // Second attempt to parse response data
        return return_raw_data ? response : jQuery.parseJSON(response);
    }

});
