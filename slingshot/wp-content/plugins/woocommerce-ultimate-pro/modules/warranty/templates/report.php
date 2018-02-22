<?php
$tab_status = (isset($_GET['status'])) ? $_GET['status'] : '';
?>
<div class="wrap woocommerce">
    <h2><?php _e('Reports', 'ultimatewoo-pro'); ?></h2>

    <div class="icon32"><img src="<?php echo ULTIMATEWOO_MODULES_URL . '/warranty/assets/images/icon.png'; ?>" /><br></div>
    <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
        <a href="admin.php?page=warranties-reports" class="nav-tab <?php echo ($tab_status == '') ? 'nav-tab-active' : ''; ?>"><?php _e('Active', 'ultimatewoo-pro'); ?></a>
        <a href="admin.php?page=warranties-reports&status=completed" class="nav-tab <?php echo ($tab_status == 'completed') ? 'nav-tab-active' : ''; ?>"><?php _e('Completed', 'ultimatewoo-pro'); ?></a>
    </h2>
<?php

if ( empty($tab_status) )
    include WooCommerce_Warranty::$includes_path .'/class.warranty_active_reports_list_table.php';
else
    include WooCommerce_Warranty::$includes_path .'/class.warranty_completed_reports_list_table.php';
