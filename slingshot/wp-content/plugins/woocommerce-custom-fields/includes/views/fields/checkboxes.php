<?php

/**
 * View for field edit page Checkboxes block
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="wccf_post wccf_post_checkboxes">

    <div class="wccf_post_buttonset" style="display: none;">
        <label for="wccf_post_config_required_1"><?php _e('Required', 'rp_wccf'); ?></label>
        <input type="radio" value="1" id="wccf_post_config_required_1" name="wccf_post_config[required]" <?php checked(($object ? $object->is_required() : false)); ?>>
        <label for="wccf_post_config_required_0"><?php _e('Optional', 'rp_wccf'); ?></label>
        <input type="radio" value="0" id="wccf_post_config_required_0" name="wccf_post_config[required]" <?php checked(($object ? $object->is_required() : false), false); ?>>
    </div>

    <?php if ($this->supports_quantity()): ?>
        <div class="wccf_post_buttonset" style="display: none;">
            <label for="wccf_post_config_quantity_based_1"><?php _e('Quantity Based', 'rp_wccf'); ?></label>
            <input type="radio" value="1" id="wccf_post_config_quantity_based_1" name="wccf_post_config[quantity_based]" class="wccf_post_config_quantity_based" <?php checked(($object ? $object->is_quantity_based() : false)); ?>>
            <label for="wccf_post_config_quantity_based_0"><?php _e('Single Field', 'rp_wccf'); ?></label>
            <input type="radio" value="0" id="wccf_post_config_quantity_based_0" name="wccf_post_config[quantity_based]" class="wccf_post_config_quantity_based" <?php checked(($object ? $object->is_quantity_based() : false), false); ?>>
        </div>
    <?php endif; ?>

    <?php if ($this->supports_visibility()): ?>
        <div class="wccf_post_buttonset" style="display: none;">
            <label for="wccf_post_config_public_1"><?php _e('Public', 'rp_wccf'); ?></label>
            <input type="radio" value="1" id="wccf_post_config_public_1" name="wccf_post_config[public]" <?php checked(($object ? $object->is_public() : false)); ?>>
            <label for="wccf_post_config_public_0"><?php _e('Private', 'rp_wccf'); ?></label>
            <input type="radio" value="0" id="wccf_post_config_public_0" name="wccf_post_config[public]" <?php checked(($object ? $object->is_public() : false), false); ?>>
        </div>
    <?php endif; ?>

    <?php if ($this->supports_pricing()): ?>
        <div class="wccf_post_buttonset" style="display: none;">
            <label for="wccf_post_config_pricing_1"><?php _e('Pricing', 'rp_wccf'); ?></label>
            <input type="radio" value="1" id="wccf_post_config_pricing_1" name="wccf_post_config[pricing]" class="wccf_post_config_pricing" <?php checked(($object ? $object->has_pricing() : false)); ?>>
            <label for="wccf_post_config_pricing_0"><?php _e('No Pricing', 'rp_wccf'); ?></label>
            <input type="radio" value="0" id="wccf_post_config_pricing_0" name="wccf_post_config[pricing]" class="wccf_post_config_pricing" <?php checked(($object ? $object->has_pricing() : false), false); ?>>
        </div>
    <?php endif; ?>

    <div class="wccf_post_buttonset" style="display: none;">
        <label for="wccf_post_config_conditional_1"><?php _e('Conditions', 'rp_wccf'); ?></label>
        <input type="radio" value="1" id="wccf_post_config_conditional_1" name="wccf_post_config[conditional]" class="wccf_post_config_conditional" <?php checked(($object ? $object->has_conditions() : false)); ?>>
        <label for="wccf_post_config_conditional_0"><?php _e('No Conditions', 'rp_wccf'); ?></label>
        <input type="radio" value="0" id="wccf_post_config_conditional_0" name="wccf_post_config[conditional]" class="wccf_post_config_conditional" <?php checked(($object ? $object->has_conditions() : false), false); ?>>
    </div>

</div>
