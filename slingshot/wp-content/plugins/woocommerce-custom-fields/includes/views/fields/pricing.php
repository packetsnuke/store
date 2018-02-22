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
    <?php if (!$object || !$object->has_pricing()): ?>
        #poststuff .wccf_field_pricing_meta_box {
            display: none;
        }
    <?php endif; ?>
</style>

<div class="wccf_post wccf_post_pricing">

    <div class="wccf_config_field wccf_config_field_half">
        <div>
        <?php WCCF_FB::grouped_select(array(
            'id'        => 'wccf_post_config_pricing_method',
            'name'      => 'wccf_post_config[pricing_method]',
            'class'     => 'wccf_post_config_pricing_method wccf_post_select2',
            'value'     => ($object ? $object->get_pricing_method() : ''),
            'options'   => WCCF_Pricing::get_pricing_methods_list($context),
        )); ?>
        </div>
    </div>

    <div class="wccf_config_field wccf_config_field_half">
        <div>
        <?php WCCF_FB::text(array(
            'id'            => 'wccf_post_config_pricing_value',
            'name'          => 'wccf_post_config[pricing_value]',
            'placeholder'   => '0.00',
            'value'         => ($object ? $object->get_pricing_value() : ''),
            'pattern'       => '[0-9.]*',
            'required'      => 'required',
        )); ?>
        </div>
    </div>

    <div style="clear: both;"></div>

</div>
