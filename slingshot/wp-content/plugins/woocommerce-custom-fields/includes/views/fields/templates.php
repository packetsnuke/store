<?php

/**
 * View for Field Templates
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div id="wccf_templates" style="display: none;">

    <!-- NO OPTIONS -->
    <div id="wccf_template_no_options">
        <div class="wccf_post_no_options"><?php _e('No options configured.', 'rp_wccf'); ?></div>
    </div>

    <!-- NO CONDITIONS -->
    <div id="wccf_template_no_conditions">
        <div class="wccf_post_no_conditions"><?php _e('No conditions configured.', 'rp_wccf'); ?></div>
    </div>

    <!-- OPTION WRAPPER -->
    <div id="wccf_template_option_wrapper">
        <div class="wccf_post_option_header">
            <div class="wccf_post_option_sort wccf_post_option_sort_header"></div>
            <div class="wccf_post_option_content wccf_post_option_content_header">
                <div class="wccf_post_option_header_item wccf_post_option_resize"><label><?php _e('Label', 'rp_wccf'); ?></label></div>
                <div class="wccf_post_option_header_item wccf_post_option_resize"><label><?php _e('Unique Key', 'rp_wccf'); ?></label></div>
                <?php if ($this->supports_pricing()): ?>
                    <div class="wccf_post_option_header_item wccf_post_option_price" style="display: none;"><label><?php _e('Pricing', 'rp_wccf'); ?></label></div>
                <?php endif; ?>
                <div class="wccf_post_option_header_item wccf_post_option_header_small_select"><label><?php _e('Selected', 'rp_wccf'); ?></label></div>
            </div>
            <div class="wccf_post_option_remove wccf_post_option_remove_header"></div>
            <div style="clear: both;"></div>
        </div>
        <div class="wccf_post_option_wrapper"></div>
    </div>

    <!-- CONDITIONS WRAPPER -->
    <div id="wccf_template_condition_wrapper">
        <div class="wccf_post_condition_wrapper"></div>
    </div>

    <!-- OPTION -->
    <div id="wccf_template_option">
        <div class="wccf_post_option">
            <div class="wccf_post_option_sort">
                <div class="wccf_post_option_sort_handle">
                    <i class="fa fa-sort"></i>
                </div>
            </div>

            <div class="wccf_post_option_content">

                <div class="wccf_post_option_setting wccf_post_option_setting_single wccf_post_option_resize">
                    <?php WCCF_FB::text(array(
                        'id'        => 'wccf_post_config_options_label_{i}',
                        'name'      => 'wccf_post_config[options][{i}][label]',
                        'required'  => 'required',
                    )); ?>
                </div>

                <div class="wccf_post_option_setting wccf_post_option_setting_single wccf_post_option_resize">
                    <?php WCCF_FB::text(array(
                        'id'        => 'wccf_post_config_options_key_{i}',
                        'name'      => 'wccf_post_config[options][{i}][key]',
                        'class'     => 'wccf_post_config_options_key',
                        'pattern'   => '[a-zA-Z0-9_]*',
                        'maxlength' => 100,
                        'required'  => 'required',
                        'style'     => 'text-transform: lowercase;',
                    )); ?>
                </div>

                <?php if ($this->supports_pricing()): ?>
                    <div class="wccf_post_option_setting wccf_post_option_setting_single wccf_post_option_price" style="display: none;">
                        <div class="wccf_post_config_pricing_method_wrapper">
                            <?php WCCF_FB::grouped_select(array(
                                'id'        => 'wccf_post_config_options_pricing_method_{i}',
                                'name'      => 'wccf_post_config[options][{i}][pricing_method]',
                                'class'     => 'wccf_post_config_pricing_method wccf_post_select2',
                                'options'   => WCCF_Pricing::get_pricing_methods_list($context, true),
                                'disabled'  => 'disabled',
                            )); ?>
                        </div>
                        <div class="wccf_post_config_pricing_value_wrapper">
                            <?php WCCF_FB::text(array(
                                'id'            => 'wccf_post_config_options_pricing_value_{i}',
                                'name'          => 'wccf_post_config[options][{i}][pricing_value]',
                                'class'         => 'wccf_post_config_pricing_value',
                                'placeholder'   => '0.00',
                                'pattern'       => '[0-9.]*',
                                'disabled'      => 'disabled',
                            )); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="wccf_post_option_setting wccf_post_option_setting_small_select">
                    <?php WCCF_FB::select(array(
                        'id'        => 'wccf_post_config_options_selected_{i}',
                        'name'      => 'wccf_post_config[options][{i}][selected]',
                        'class'     => 'wccf_post_config_options_selected',
                        'options'   => array(
                            '0' => __('No', 'rp_wccf'),
                            '1' => __('Yes', 'rp_wccf'),
                        ),
                    )); ?>
                </div>
                <div style="clear: both;"></div>
            </div>

            <div class="wccf_post_option_remove">
                <div class="wccf_post_option_remove_handle">
                    <i class="fa fa-times"></i>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

    <!-- CONDITION -->
    <div id="wccf_template_condition">
        <div class="wccf_post_condition">
            <div class="wccf_post_condition_sort">
                <div class="wccf_post_condition_sort_handle">
                    <i class="fa fa-sort"></i>
                </div>
            </div>

            <div class="wccf_post_condition_content">
                <div class="wccf_post_condition_setting wccf_post_condition_setting_single">
                    <?php WCCF_FB::grouped_select(array(
                        'id'        => 'wccf_post_config_conditions_type_{i}',
                        'name'      => 'wccf_post_config[conditions][{i}][type]',
                        'class'     => 'wccf_condition_type wccf_post_select2',
                        'options'   => WCCF_Conditions::get_conditions_list($context),
                    )); ?>
                </div>

                <?php foreach(WCCF_Conditions::get_conditions_list($context) as $group_key => $group): ?>
                    <?php foreach($group['options'] as $option_key => $option): ?>

                        <div class="wccf_post_condition_setting_fields wccf_post_condition_setting_fields_<?php echo $group_key . '_' . $option_key ?>" style="display: none;">

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'other_field_id')): ?>
                                <div class="wccf_post_condition_setting_fields_single">
                                    <?php WCCF_FB::select(array(
                                        'id'        => 'wccf_post_config_conditions_other_field_id_{i}',
                                        'name'      => 'wccf_post_config[conditions][{i}][other_field_id]',
                                        'class'     => 'wccf_condition_other_field_id wccf_post_select2',
                                        'options'   => WCCF_Field_Controller::get_all_field_list_by_context($context, array('enabled', 'disabled'), $field_id),
                                        'disabled'  => 'disabled',
                                        'required'  => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <div class="wccf_post_condition_setting_fields_<?php echo (in_array(($group_key . '_' . $option_key), array('customer_is_logged_in'), true) ? 'triple' : 'single'); ?>">
                                <?php WCCF_FB::select(array(
                                    'id'        => 'wccf_post_config_conditions_' . $group_key . '_' . $option_key . '_method_{i}',
                                    'name'      => 'wccf_post_config[conditions][{i}][' . $group_key . '_' . $option_key . '_method]',
                                    'class'     => 'wccf_condition_method wccf_post_select2',
                                    'options'   => WCCF_Conditions::get_methods_list($group_key, $option_key),
                                    'disabled'  => 'disabled',
                                )); ?>
                            </div>

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'roles')): ?>
                                <div class="wccf_post_condition_setting_fields_<?php echo WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php WCCF_FB::multiselect(array(
                                        'id'        => 'wccf_post_config_conditions_roles_{i}',
                                        'name'      => 'wccf_post_config[conditions][{i}][roles][]',
                                        'class'     => 'wccf_post_select2',
                                        'options'   => array(),
                                        'disabled'  => 'disabled',
                                        'required'  => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'capabilities')): ?>
                                <div class="wccf_post_condition_setting_fields_<?php echo WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php WCCF_FB::multiselect(array(
                                        'id'        => 'wccf_post_config_conditions_capabilities_{i}',
                                        'name'      => 'wccf_post_config[conditions][{i}][capabilities][]',
                                        'class'     => 'wccf_post_select2',
                                        'options'   => array(),
                                        'disabled'  => 'disabled',
                                        'required'  => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'products')): ?>
                                <div class="wccf_post_condition_setting_fields_<?php echo WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php WCCF_FB::multiselect(array(
                                        'id'        => 'wccf_post_config_conditions_products_{i}',
                                        'name'      => 'wccf_post_config[conditions][{i}][products][]',
                                        'class'     => 'wccf_post_select2',
                                        'options'   => array(),
                                        'disabled'  => 'disabled',
                                        'required'  => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'product_variations')): ?>
                                <div class="wccf_post_condition_setting_fields_<?php echo WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php WCCF_FB::multiselect(array(
                                        'id'        => 'wccf_post_config_conditions_product_variations_{i}',
                                        'name'      => 'wccf_post_config[conditions][{i}][product_variations][]',
                                        'class'     => 'wccf_post_select2',
                                        'options'   => array(),
                                        'disabled'  => 'disabled',
                                        'required'  => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'product_attributes')): ?>
                                <div class="wccf_post_condition_setting_fields_<?php echo WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php WCCF_FB::multiselect(array(
                                        'id'        => 'wccf_post_config_conditions_product_attributes_{i}',
                                        'name'      => 'wccf_post_config[conditions][{i}][product_attributes][]',
                                        'class'     => 'wccf_post_select2',
                                        'options'   => array(),
                                        'disabled'  => 'disabled',
                                        'required'  => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'product_categories')): ?>
                                <div class="wccf_post_condition_setting_fields_<?php echo WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php WCCF_FB::multiselect(array(
                                        'id'        => 'wccf_post_config_conditions_product_categories_{i}',
                                        'name'      => 'wccf_post_config[conditions][{i}][product_categories][]',
                                        'class'     => 'wccf_post_select2',
                                        'options'   => array(),
                                        'disabled'  => 'disabled',
                                        'required'  => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'product_types')): ?>
                                <div class="wccf_post_condition_setting_fields_<?php echo WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php WCCF_FB::multiselect(array(
                                        'id'        => 'wccf_post_config_conditions_product_types_{i}',
                                        'name'      => 'wccf_post_config[conditions][{i}][product_types][]',
                                        'class'     => 'wccf_post_select2',
                                        'options'   => array(),
                                        'disabled'  => 'disabled',
                                        'required'  => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'number')): ?>
                                <div class="wccf_post_condition_setting_fields_<?php echo WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php WCCF_FB::text(array(
                                        'id'            => 'wccf_post_config_conditions_number_{i}',
                                        'name'          => 'wccf_post_config[conditions][{i}][number]',
                                        'placeholder'   => '0',
                                        'disabled'      => 'disabled',
                                        'required'      => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'decimal')): ?>
                                <div class="wccf_post_condition_setting_fields_<?php echo WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php WCCF_FB::text(array(
                                        'id'            => 'wccf_post_config_conditions_decimal_{i}',
                                        'name'          => 'wccf_post_config[conditions][{i}][decimal]',
                                        'placeholder'   => '0.00',
                                        'disabled'      => 'disabled',
                                        'required'      => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'text')): ?>
                                <div class="wccf_post_condition_setting_fields_<?php echo WCCF_Conditions::field_size($group_key, $option_key); ?>" <?php echo (($group_key == 'custom_field' && $option_key == 'other_custom_field') ? 'style="display: none;"' : ''); ?>>
                                    <?php WCCF_FB::text(array(
                                        'id'        => 'wccf_post_config_conditions_text_{i}',
                                        'name'      => 'wccf_post_config[conditions][{i}][text]',
                                        'class'     => 'wccf_conditions_text',
                                        'disabled'  => 'disabled',
                                        'required'  => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'payment_methods')): ?>
                                <div class="wccf_post_condition_setting_fields_<?php echo WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php WCCF_FB::multiselect(array(
                                        'id'        => 'wccf_post_config_conditions_payment_methods_{i}',
                                        'name'      => 'wccf_post_config[conditions][{i}][payment_methods][]',
                                        'class'     => 'wccf_post_select2',
                                        'options'   => array(),
                                        'disabled'  => 'disabled',
                                        'required'  => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'shipping_methods')): ?>
                                <div class="wccf_post_condition_setting_fields_<?php echo WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php WCCF_FB::multiselect(array(
                                        'id'        => 'wccf_post_config_conditions_shipping_methods_{i}',
                                        'name'      => 'wccf_post_config[conditions][{i}][shipping_methods][]',
                                        'class'     => 'wccf_post_select2',
                                        'options'   => array(),
                                        'disabled'  => 'disabled',
                                        'required'  => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (WCCF_Conditions::uses_field($group_key, $option_key, 'coupons')): ?>
                                <div class="wccf_post_condition_setting_fields_<?php echo WCCF_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php WCCF_FB::multiselect(array(
                                        'id'        => 'wccf_post_config_conditions_coupons_{i}',
                                        'name'      => 'wccf_post_config[conditions][{i}][coupons][]',
                                        'class'     => 'wccf_post_select2',
                                        'options'   => array(),
                                        'disabled'  => 'disabled',
                                        'required'  => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <div style="clear: both;"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <div style="clear: both;"></div>
            </div>

            <div class="wccf_post_condition_remove">
                <div class="wccf_post_condition_remove_handle">
                    <i class="fa fa-times"></i>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>

</div>
