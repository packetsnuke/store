<?php

/**
 * Product Properties Data Frontend Display In Custom Tab
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<h2><?php echo apply_filters('wccf_context_label', WCCF_Settings::get('alias_product_prop'), 'product_prop', 'frontend'); ?></h2>

<?php do_action('wccf_before_product_properties'); ?>

<table class="shop_attributes">
    <tbody>

        <?php $class = ''; ?>
        <?php foreach ($fields as $field): ?>

            <tr class="<?php echo $class; ?>">
                <th><?php echo $field['field']->get_label(); ?></th>
                <td><p><?php echo $field['display_value']; ?></p></td>
            </tr>

            <?php $class = $class === '' ? 'alt' : ''; ?>

        <?php endforeach; ?>

    </tbody>
</table>

<?php do_action('wccf_after_product_properties'); ?>
