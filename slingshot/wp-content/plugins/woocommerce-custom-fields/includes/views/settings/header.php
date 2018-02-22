<?php

/**
 * View for Settings page header (tabs)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<h2 style="padding: 0; margin: 0; height: 0;">
    <!-- Fix for WordPress notices jumping in between header and settings area -->
</h2>

<h2 class="wccf_tabs_container nav-tab-wrapper">
    <?php foreach (WCCF_Settings::get_structure() as $tab_key => $tab): ?>
        <a class="nav-tab <?php echo ($tab_key == $current_tab ? 'nav-tab-active' : ''); ?>" href="edit.php?post_type=wccf_product_field&page=wccf_settings&tab=<?php echo $tab_key; ?>"><?php echo $tab['title']; ?></a>
    <?php endforeach; ?>
</h2>
