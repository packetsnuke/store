<?php

/**
 * Checkout Field Data Frontend Display
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<header>
    <h2><?php echo apply_filters('wccf_context_label', WCCF_Settings::get('alias_checkout_field'), 'checkout_field', 'frontend'); ?></h2>
</header>

<?php do_action('wccf_before_checkout_fields'); ?>

<table class="shop_table shop_table_responsive">
    <tbody>

        <?php foreach ($fields as $field): ?>

            <tr>
                <th><?php echo $field['field']->get_label(); ?></th>
                <td><?php echo $field['display_value']; ?></td>
            </tr>

        <?php endforeach; ?>

    </tbody>
</table>

<?php do_action('wccf_after_checkout_fields'); ?>
