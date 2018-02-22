<?php

/**
 * Order Field Data Plain Text Email Display
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

echo strtoupper(apply_filters('wccf_context_label', WCCF_Settings::get('alias_order_field'), 'order_field', 'frontend')) . "\n\n";

foreach ($fields as $field) {
    echo wp_kses_post($field['field']->get_label()) . ': ' . wp_kses_post($field['display_value']) . "\n";
}
