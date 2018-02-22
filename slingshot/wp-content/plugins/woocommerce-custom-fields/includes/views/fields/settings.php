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

<div class="wccf_post wccf_post_settings">

    <div class="wccf_post_title" style="display: none;">

        <h1>
            <?php if ($object): ?>
                <span class="wccf_field_label"><?php echo $object->get_label(); ?></span>
                <span class="wccf_edit_page_status wccf_status_<?php echo $object->get_status(); ?>"><?php echo $object->get_status_title(); ?></span>
            <?php else: ?>
                <span class="wccf_field_label"><?php _e('New Field', 'rp_wccf'); ?></span>
                <span class="wccf_edit_page_status wccf_status_disabled"><?php _e('disabled', 'rp_wccf'); ?></span>
            <?php endif; ?>

            <p class="wccf_field_key">
                <?php echo ($object ? $object->get_key() : __('new_field', 'rp_wccf')); ?>
            </p>
        </h1>

    </div>

    <div class="wccf_config_field wccf_config_field_half">
        <?php WCCF_FB::text(array(
            'id'            => 'wccf_post_config_label',
            'name'          => 'wccf_post_config[label]',
            'value'         => ($object ? $object->get_label() : ''),
            'label'         => __('Field Label', 'rp_wccf'),
            'placeholder'   => __('e.g. New Field', 'rp_wccf'),
            'required'      => 'required',
        )); ?>
    </div>

    <div class="wccf_config_field wccf_config_field_half">
        <?php

            // Define field config
            $key_field_config = array(
                'id'            => 'wccf_post_config_key',
                'name'          => 'wccf_post_config[key]',
                'value'         => ($object ? $object->get_key() : ''),
                'label'         => __('Unique Key', 'rp_wccf'),
                'placeholder'   => __('e.g. new_field', 'rp_wccf'),
                'pattern'       => '[a-zA-Z0-9_\x37]*',
                'maxlength'     => 100,
                'required'      => 'required',
                'style'         => 'text-transform: lowercase;',
            );

            // Disable existing field
            if ($object) {
                $key_field_config['disabled'] = 'disabled';
                $key_field_config['label'] .= '<span class="wccf_post_config_lable_hint">' . __('can no longer be changed', 'rp_wccf') . '</span>';
            }

            WCCF_FB::text($key_field_config);
        ?>
    </div>

    <div class="wccf_config_field <?php echo ($context === 'user_field' ? 'wccf_config_field_half' : ''); ?>">
        <?php WCCF_FB::select(array(
            'id'        => 'wccf_post_config_field_type',
            'name'      => 'wccf_post_config[field_type]',
            'class'     => 'wccf_post_select2',
            'value'     => ($object ? $object->get_field_type() : ''),
            'options'   => WCCF_FB::get_types(),
            'label'     => __('Field Type', 'rp_wccf'),
        )); ?>
        <?php WCCF_FB::hidden(array(
            'id'        => 'wccf_post_config_original_field_type',
            'name'      => 'wccf_post_config_original_field_type',
            'value'     => ($object ? $object->get_field_type() : ''),
        )); ?>
    </div>

    <?php if ($context === 'user_field'): ?>
        <div class="wccf_config_field wccf_config_field_half">
            <?php WCCF_FB::select(array(
                'id'        => 'wccf_post_config_display_as',
                'name'      => 'wccf_post_config[display_as]',
                'class'     => 'wccf_post_select2',
                'value'     => ($object ? $object->get_display_as() : ''),
                'options'   => WCCF_User_Field_Controller::get_display_as_options(),
                'label'     => __('Display As', 'rp_wccf'),
            )); ?>
        </div>
    <?php endif; ?>

    <div style="clear: both;"></div>

</div>
