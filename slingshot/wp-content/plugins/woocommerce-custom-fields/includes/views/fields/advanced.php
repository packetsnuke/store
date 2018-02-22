<?php

/**
 * View for field settings panel
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<style type="text/css">
    #submitdiv {
        display: none;
    }
</style>

<div class="wccf_post wccf_post_advanced">

        <div class="wccf_config_field wccf_config_field_half">
            <?php WCCF_FB::text(array(
                'id'    => 'wccf_post_config_description',
                'name'  => 'wccf_post_config[description]',
                'value' => ($object ? $object->get_description() : ''),
                'label' => __('Field Description', 'rp_wccf'),
            )); ?>
        </div>

        <div class="wccf_config_field wccf_config_field_half">
            <?php WCCF_FB::text(array(
                'id'    => 'wccf_post_config_default_value',
                'name'  => 'wccf_post_config[default_value]',
                'value' => ($object ? $object->get_default_value() : ''),
                'label' => __('Default Value', 'rp_wccf'),
            )); ?>
        </div>

        <div class="wccf_config_field wccf_config_field_half">
            <?php WCCF_FB::number(array(
                'id'            => 'wccf_post_config_character_limit',
                'name'          => 'wccf_post_config[character_limit]',
                'placeholder'   => __('No limit', 'rp_wccf'),
                'value'         => ($object ? $object->get_character_limit() : ''),
                'label'         => __('Character Limit', 'rp_wccf'),
                'pattern'       => '[0-9]*',
            )); ?>
        </div>

        <div class="wccf_config_field wccf_config_field_half">
            <?php WCCF_FB::text(array(
                'id'            => 'wccf_post_config_custom_css',
                'name'          => 'wccf_post_config[custom_css]',
                'placeholder'   => 'e.g. width: 50%;',
                'value'         => ($object ? $object->get_custom_css() : ''),
                'label'         => __('Custom CSS Rules', 'rp_wccf'),
            )); ?>
        </div>

        <div class="wccf_config_field wccf_config_field_half">
            <?php WCCF_FB::number(array(
                'id'            => 'wccf_post_config_min_selected',
                'name'          => 'wccf_post_config[min_selected]',
                'placeholder'   => __('No limit', 'rp_wccf'),
                'value'         => ($object ? $object->get_min_selected() : ''),
                'label'         => __('Min Selected', 'rp_wccf'),
                'pattern'       => '[0-9]*',
            )); ?>
        </div>

        <div class="wccf_config_field wccf_config_field_half">
            <?php WCCF_FB::number(array(
                'id'            => 'wccf_post_config_max_selected',
                'name'          => 'wccf_post_config[max_selected]',
                'placeholder'   => __('No limit', 'rp_wccf'),
                'value'         => ($object ? $object->get_max_selected() : ''),
                'label'         => __('Max Selected', 'rp_wccf'),
                'pattern'       => '[0-9]*',
            )); ?>
        </div>

        <div class="wccf_config_field wccf_config_field_half">
            <?php WCCF_FB::number(array(
                'id'            => 'wccf_post_config_min_value',
                'name'          => 'wccf_post_config[min_value]',
                'placeholder'   => __('No limit', 'rp_wccf'),
                'value'         => ($object ? $object->get_min_value() : ''),
                'label'         => __('Min Value', 'rp_wccf'),
                'pattern'       => '[0-9]*',
            )); ?>
        </div>

        <div class="wccf_config_field wccf_config_field_half">
            <?php WCCF_FB::number(array(
                'id'            => 'wccf_post_config_max_value',
                'name'          => 'wccf_post_config[max_value]',
                'placeholder'   => __('No limit', 'rp_wccf'),
                'value'         => ($object ? $object->get_max_value() : ''),
                'label'         => __('Max Value', 'rp_wccf'),
                'pattern'       => '[0-9]*',
            )); ?>
        </div>

        <?php if ($this->supports_position()): ?>
            <div class="wccf_config_field wccf_config_field_half">
                <?php WCCF_FB::select(array(
                    'id'        => 'wccf_post_config_position',
                    'name'      => 'wccf_post_config[position]',
                    'class'     => 'wccf_post_select2',
                    'value'     => ($object ? $object->get_position() : ''),
                    'options'   => WCCF_WC_Checkout::get_positions(),
                    'label'     => __('Checkout Position', 'rp_wccf'),
                )); ?>
            </div>
        <?php endif; ?>

        <?php if ($this->get_context() === 'checkout_field' && wc_tax_enabled()): ?>
            <div class="wccf_config_field wccf_config_field_half">
                <?php WCCF_FB::select(array(
                    'id'        => 'wccf_post_config_tax_class',
                    'name'      => 'wccf_post_config[tax_class]',
                    'class'     => 'wccf_post_select2',
                    'value'     => ($object ? $object->get_tax_class() : ''),
                    'options'   => RightPress_Helper::get_wc_tax_class_list(array('wccf_not_taxable' => __('Not Taxable', 'rp_wccf'))),
                    'label'     => __('Tax Class', 'rp_wccf'),
                )); ?>
            </div>
        <?php endif; ?>

        <div style="clear: both;"></div>

</div>
