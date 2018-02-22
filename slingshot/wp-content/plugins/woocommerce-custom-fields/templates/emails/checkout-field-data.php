<?php

/**
 * Checkout Field Data Email Display
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<h2><?php echo apply_filters('wccf_context_label', WCCF_Settings::get('alias_checkout_field'), 'checkout_field', 'frontend'); ?></h2>
<ul>
    <?php foreach ($fields as $field): ?>
        <li><strong><?php echo wp_kses_post($field['field']->get_label()); ?>:</strong> <span class="text"><?php echo wp_kses_post($field['display_value']); ?></span></li>
    <?php endforeach; ?>
</ul>
