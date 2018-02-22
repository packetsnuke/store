<?php

/**
 * View for Settings page fields
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="wccf_settings">
    <div class="wccf_settings_container">
        <input type="hidden" name="current_tab" value="<?php echo $current_tab; ?>" />
        <?php settings_fields('wccf_settings_group_' . $current_tab); ?>
        <?php do_settings_sections('wccf-admin-' . str_replace('_', '-', $current_tab)); ?>
        <div></div>
        <?php submit_button(); ?>
    </div>
</div>
