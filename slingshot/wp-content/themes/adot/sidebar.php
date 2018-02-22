<?php
/**
 * The sidebar containing the main widget area.
 *
 * @package thim
 */
if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>
<div id="sidebar" class="widget-sidebar-shop col-sm-3" role="complementary">
    <div class="sidebar">
        <?php dynamic_sidebar('sidebar-1'); ?>
    </div>
</div>

<div class="clear"></div>
<!-- #secondary -->

