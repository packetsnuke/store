<?php
/**
 * Created by PhpStorm.
 * User: Tien Cong
 * Date: 4/6/2015
 * Time: 1:46 PM
 */
if (!is_active_sidebar('sidebar-shop')) {
    return;
}
?>

<div id="secondary" class="widget-sidebar-shop col-sm-3" role="complementary">
    <div class="sidebar">
        <?php dynamic_sidebar('sidebar-shop'); ?>
    </div>
</div>
<div class="clear"></div>
<!-- #secondary -->
