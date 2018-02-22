<?php

/**
 * User Field Data Frontend Display
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<?php foreach ($fields as $field): ?>

    <tr>
        <th><?php echo $field['field']->get_label(); ?></th>
        <td><?php echo $field['display_value']; ?></td>
    </tr>

<?php endforeach; ?>
