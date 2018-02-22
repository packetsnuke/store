<?php
/*
Plugin Name: WooCommerce Customers Manager
Description: Customers managment system.
Author: Lagudi Domenico
Version: 20.0
*/

/* 
Copyright: WooCommerce Customer Manager uses the ACF PRO plugin. ACF PRO files are not to be used or distributed outside of the WooCommerce Customer Manager plugin.
*/


$wccm_current_page = "";
$wccm_page = null; 
//define('WCCM_PLUGIN_PATH', WP_PLUGIN_URL."/".dirname( plugin_basename( __FILE__ ) ) );
define('WCCM_PLUGIN_PATH', rtrim(plugin_dir_url(__FILE__), "/") )  ;
define('WCCM_PLUGIN_ABS_PATH', plugin_dir_path( __FILE__ ) );

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ||
     (is_multisite() && array_key_exists( 'woocommerce/woocommerce.php', get_site_option('active_sitewide_plugins') ))	
	)
{
	include_once( "classes/com/WCCM_Acf.php");
	include_once( "classes/com/WCCM_Global.php");
	
	//admin	
	if(!class_exists('WCCM_CustomerDetails'))
		require_once('classes/admin/WCCM_CustomerDetails.php');
	if(!class_exists('WCCM_CustomerTable'))
		require_once('classes/admin/WCCM_CustomerTable.php');
	if(!class_exists('WCCM_CustomerAdd'))
		require_once('classes/admin/WCCM_CustomerAdd.php');
	if(!class_exists('WCCM_CustomerImport'))
		require_once('classes/admin/WCCM_CustomerImport.php');
	if(!class_exists('WCCM_CustomerExport'))
		require_once('classes/admin/WCCM_CustomerExport.php');
	if(!class_exists('WCCM_BulkEmail'))
		require_once('classes/admin/WCCM_BulkEmail.php');
	if(!class_exists('WCCM_Options'))
		require_once('classes/admin/WCCM_Options.php');
	if(!class_exists('WCCM_CustomerGuestList'))
		require_once('classes/admin/WCCM_CustomerGuestList.php');
	if(!class_exists('WCCM_GuestToRegistered'))
		require_once('classes/admin/WCCM_GuestToRegistered.php');
	if(!class_exists('WCCM_Discover'))
		require_once('classes/admin/WCCM_Discover.php'); 
	if(!class_exists('WCCM_BulkRoleSwitcher'))
		require_once('classes/admin/WCCM_BulkRoleSwitcher.php'); 
	if(!class_exists('WCCM_ProductsTablePage'))
		require_once('classes/admin/WCCM_ProductsTablePage.php');
	if(!class_exists('WCCM_ProductPage'))
		require_once('classes/admin/WCCM_ProductPage.php');
	if(!class_exists('WCCM_OrdersTablePage'))
		require_once('classes/admin/WCCM_OrdersTablePage.php');
	if(!class_exists('WCCM_CustomerMetadataPage'))
		require_once('classes/admin/WCCM_CustomerMetadataPage.php');
	if(!class_exists('WCCM_OrderDetailsPage'))
		require_once('classes/admin/WCCM_OrderDetailsPage.php');
	if(!class_exists('WCST_EstimatorConfigurator'))
	{
		require_once('classes/admin/WCCM_EmailTemplatesConfigurator.php');
		/* $wccm_email_templates_configurator = new WCCM_EmailTemplatesConfigurator(); */
	}
	//frontend
	if(!class_exists('WCCM_CheckoutPage'))
		require_once('classes/frontend/WCCM_CheckoutPage.php'); 
	//com
	if(!class_exists('WCCM_Email'))
		require_once('classes/com/WCCM_Email.php');
	if(!class_exists('WCCM_Country'))
		require_once('classes/com/WCCM_Country.php');
	if(!class_exists('WCCM_Order'))
		require_once('classes/com/WCCM_Order.php');
	if(!class_exists('WCCM_Customer'))
		require_once('classes/com/WCCM_Customer.php');
	if(!class_exists('WCCM_Product'))
		require_once('classes/com/WCCM_Product.php');
	if(!class_exists('WCCM_Wpml'))
		require_once('classes/com/WCCM_Wpml.php');
	if(!class_exists('WCCM_Configuration'))
		require_once('classes/com/WCCM_Configuration.php');
	if(!class_exists('WCCM_Html'))
		require_once('classes/com/WCCM_Html.php');
	if(!class_exists('WCCM_Cart'))
		require_once('classes/com/WCCM_Cart.php');
	
	//add_action('admin_init', 'load_js_and_css');
	//add_action( 'admin_enqueue_scripts',  'load_js_and_css' );
	//add_action('admin_init', 'wccm_save_custom_options');
	add_action('admin_init', 'wccm_register_settings');
    add_action('admin_menu', 'wccm_init_admin_panel');
	add_action( 'admin_head', 'wccm_adjust_admin_css_header' );
	//add_action( "load-$wccm_customer_list_page", 'wccm_add_options' );
	add_action( 'admin_head', 'wccm_add_options' );
	add_filter('set-screen-option', 'wccm_set_options', 10, 3);
	/* add_filter('screen_layout_columns', 'wccm_change_options', 10, 2); */
	add_action( 'wp_print_scripts', 'wccm_unregister_css_and_js' );
	add_action('init', 'wccm_wp_init');
	//Alternate js
	//add_action ('admin_enqueue_scripts', 'wccm_alternate_js_loading');
	//add_action( 'admin_init', 'wccm_alternate_js_loading' );
	
	//ajax
	if(isset($_POST['action']) && $_POST['action'] == 'upload_csv')
		$wccm_page = new WCCM_CustomerImport(); 
	if(isset($_POST['action']) && ($_POST['action'] == 'wccm_export_csv' || 
								   $_POST['action'] == 'wccm_export_guests_csv' || 
								   $_POST['action'] == 'wccm_export_get_max_guest_orders_iterations' || 
								   $_POST['action'] == 'wccm_export_get_max_regiesterd_users'))
		$wccm_page = new WCCM_CustomerExport();

	$wccm_guest_to_registered_helper = new WCCM_GuestToRegistered();
	$wccm_order_model = new WCCM_Order();
	$wccm_customer_model = new WCCM_Customer();
	$wccm_product_model = new WCCM_Product();
	$wccm_wpml_helper = new WCCM_Wpml();
	$wccm_configuration_model = new WCCM_Configuration();
	$wccm_checkout_page_addon = new WCCM_CheckoutPage();
	$wccm_products_list_page_addon = new WCCM_ProductsTablePage();
	$wccm_product_page_addon = new WCCM_ProductPage();
	$wccm_orders_page_addon = new WCCM_OrdersTablePage();
	$wccm_html_model = new WCCM_Html();
	$wccm_order_details_addon = new WCCM_OrderDetailsPage();
	$wccm_cart_model = new WCCM_Cart();
}
function wccm_wp_init()
{
	if ( ! function_exists( 'wp_handle_upload' ) ) 
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	
}

function wccm_unregister_css_and_js($enqueue_styles)
{
	$url = $_SERVER['REQUEST_URI'];
	if( strpos($url, '/point-of-sale') !== false)
	{
		wp_dequeue_script('select2');
	}
	WCCM_BulkEmail::force_dequeue_scripts($enqueue_styles);
}

function wccm_alternate_js_loading() 
{
    //page=woocommerce-customers-manager&action=wccm-guests-list
    if(isset($_GET['page']) && $_GET['page'] == 'woocommerce-customers-manager' && isset($_GET['action']) && $_GET['action']=='wccm-guests-list')
    {
        global $wp_scripts, $concatenate_scripts;
		//wccm_var_dump($wp_scripts);
        if (isset($wp_scripts->registered['jquery']->ver)) 
		{
			//$wp_scripts->queue = array();
            //$jquery_version = $wp_scripts->registered['jquery']->ver;
            // wp_dequeue_script( 'jquery' );
			//wp_deregister_script( 'jquery' );
			//wp_register_script ('jquery', "http://ajax.googleapis.com/ajax/libs/jquery/{$jquery_version}/jquery.js");
            //wp_register_script ('jquery', "http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.js");
            //wp_register_script ('jquery', plugins_url('js/jquery.1.11.2.js',__FILE__));
			
			/* $wp_scripts->registered['jquery']->ver = "1.11.2";
			$wp_scripts->registered['jquery-core']->ver = "1.11.2";
			$wp_scripts->registered['jquery-core']->src = "/wp-content/plugins/woocommerce-customers-manager/js/jquery.1.11.2.js"; */

        }
    }
}


function wccm_adjust_admin_css_header() 
{
	$wccm_page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
	  if( 'woocommerce-customers-manager' != $wccm_page )
		return;  

	  echo '<style type="text/css">';
	  echo '.wp-list-table .column-ID { width: 60px; }';
	  echo 'table.wp-list-table .column-name { width: 6%; }';	  
	  //echo 'table.wp-list-table .column-phone { width: 5%; }';	  
	  /* echo '.wp-list-table .column-notes { width: 5%; }'; */
	  echo '.wp-list-table .column-surname { width: 8%; }';
	  echo '.wp-list-table .column-login { width: 8%; }';
	  echo '.wp-list-table .column-orders { width: 4%; }';
	  echo '.wp-list-table .column-total_spent { width: 7%; }';
	  echo '</style>';
}

function wccm_init_admin_panel()
{ 
	global $wccm_customer_list_page,$wccm_current_page;
	//default
    if(!isset($_REQUEST['action']))
		$wccm_current_page = "CustomerTable";
	
	$place = wccm_get_free_menu_position(54 , 0.1);
    $wccm_customer_list_page =  add_menu_page( 'WooCommerce Customers Manager Page', __('Customers', 'woocommerce-customers-manager'), 'manage_woocommerce', 'woocommerce-customers-manager', 'wccm_load_view',  'dashicons-id' , (string)$place);
	load_plugin_textdomain('woocommerce-customers-manager', false, basename( dirname( __FILE__ ) ) . '/languages' );
		
	//var_dump($_REQUEST['action']);
	 
	 add_submenu_page('woocommerce-customers-manager', __('Add new','woocommerce-customers-manager'), __('Add New','woocommerce-customers-manager'), 'manage_woocommerce', 'wccm-add-new-customer', 'wccm_load_add_new_customer_view');
	 add_submenu_page('woocommerce-customers-manager', __('Discover by orders','woocommerce-customers-manager'), __('Discover by orders','woocommerce-customers-manager'), 'manage_woocommerce', 'wccm-discover-customer', 'wccm_load_discover_customer_view');
	 add_submenu_page('woocommerce-customers-manager', __('Bulk emails','woocommerce-customers-manager'), __('Bulk emails','woocommerce-customers-manager'), 'manage_woocommerce', 'wccm-bulk-email-customer', 'wccm_load_bulk_email_view');
	 add_submenu_page('woocommerce-customers-manager', __('Import customers','woocommerce-customers-manager'), __('Import customers','woocommerce-customers-manager'), 'manage_woocommerce', 'wccm-import-customers', 'wccm_load_import_customers_view');
	 add_submenu_page('woocommerce-customers-manager', __('Export customers','woocommerce-customers-manager'), __('Export customers','woocommerce-customers-manager'), 'manage_woocommerce', 'wccm-export-customers', 'wccm_load_export_customers_view');
	 //add_submenu_page('woocommerce-customers-manager', __('Bulk role switcher','woocommerce-customers-manager'), __('Bulk role switcher','woocommerce-customers-manager'), 'manage_woocommerce', 'wccm-bulk-role-switcher', 'wccm_load_bulk_role_switcher_view');
	 add_submenu_page('woocommerce-customers-manager', __('Options','woocommerce-customers-manager'), __('Options','woocommerce-customers-manager'), 'manage_woocommerce', 'wccm-options-page', 'wccm_render_wppas_option_page');
	
	$wccm_email_templates_configurator = new WCCM_EmailTemplatesConfigurator();
}
 function wccm_get_free_menu_position($start, $increment = 0.1)
    {
        foreach ($GLOBALS['menu'] as $key => $menu) {
            $menus_positions[] = $key;
        }
	
        if (!in_array($start, $menus_positions)) return $start;
 
        /* the position is already reserved find the closet one */
        while (in_array($start, $menus_positions)) {
            $start += $increment;
        }
        return $start;
    }
function wccm_render_wppas_option_page()
{
	$wccm_options_page = new WCCM_Options();
	$wccm_options_page->render_page();
}
function wccm_load_add_new_customer_view()
{
	wccm_load_view("CustomerAdd");
}
function wccm_load_import_customers_view()
{
	wccm_load_view("CustomerImport");
}
function wccm_load_export_customers_view() 
{
	wccm_load_view("CustomerExport");
}
function wccm_load_bulk_role_switcher_view() 
{
	wccm_load_view("BulkRoleSwitcher");
}
function wccm_load_discover_customer_view()
{
	wccm_load_view("DiscoverCustomer");
}
function wccm_load_bulk_email_view()
{
	wccm_load_view("BulkEmail");
}
function wccm_load_view($view = "CustomerTable")
{
	global $wccm_customer_list_page,$wccm_current_page,$wccm_page;
	$wccm_current_page = $view;
	
	
	if(isset($_REQUEST['action']))
	{
		if($_REQUEST['action'] == 'customer_details')
			$wccm_current_page = "CustomerDetails";
		else if($_REQUEST['action'] == 'wccm-customer-add')
			$wccm_current_page = "CustomerAdd";
		else if($_REQUEST['action'] == 'wccm-customer-import')
			$wccm_current_page = "CustomerImport";
		else if($_REQUEST['action'] == 'wccm-customer-export')
			$wccm_current_page = "CustomerExport";
		else if($_REQUEST['action'] == 'wccm-bulk-email-customer')
			$wccm_current_page = "BulkEmail";
		else if($_REQUEST['action'] == 'wccm-guests-list')
			$wccm_current_page = "CustomerGuestList";
		else if($_REQUEST['action'] == 'wccm-discover-customer')
			$wccm_current_page = "DiscoverCustomer";
		else if($_REQUEST['action'] == 'wccm-customer-metadata')
			$wccm_current_page = "CustomerMetadata";
	}
	
	
	
	if($wccm_current_page == "CustomerDetails")
	{
		$wccm_page = new WCCM_CustomerDetails();
	}
	else if($wccm_current_page == "CustomerMetadata")
	{
		$wccm_page = new WCCM_CustomerMetadataPage();
	}
	else if($wccm_current_page == "DiscoverCustomer")
	{
		$wccm_page = new WCCM_Discover(); 
	}
	else if($wccm_current_page == "BulkEmail")
	{
		$wccm_page = new WCCM_BulkEmail(); 
	}
	else if($wccm_current_page == "CustomerExport")
	{
		$wccm_page = new WCCM_CustomerExport(); 
	}
	else if($wccm_current_page == "CustomerImport")
	{
		$wccm_page = new WCCM_CustomerImport(); 
	}
	else if($wccm_current_page == "CustomerAdd")
	{
		if(isset($_REQUEST['edit']) && $_REQUEST['edit'] == '1')
			$wccm_page = new WCCM_CustomerAdd(true);
		else
			$wccm_page = new WCCM_CustomerAdd(false);
	}
	else if($wccm_current_page == "CustomerGuestList")
	{
		$wccm_page = new WCCM_CustomerGuestList();
	}
	else if($wccm_current_page == "BulkRoleSwitcher")
	{
		$wccm_page = new WCCM_BulkRoleSwitcher();
	}
	else
	{
		$wccm_page = new WCCM_CustomerTable(); 
		$wccm_page->prepare_items();	
	}
	wccm_render_page($wccm_page);
}	
function wccm_add_options() 
{
	global $wccm_current_page,$wccm_customer_list_page,$wccm_page;
	/* if($wccm_current_page != "CustomerTable")
		return;  */
	
	$screen = get_current_screen();
	if(!is_object($screen) || $screen->id != $wccm_customer_list_page)
		return;
	
	$option = 'per_page';
	$args = array(
			 'label' => __('Customers', 'woocommerce-customers-manager'),
			 'default' => 20 ,
			 'option' => 'wccm-customers-options_per_page'
			 );
	add_screen_option( $option, $args );
	
	
	//add_filter('screen_layout_columns', 'wccm_display_my_option'); //Add our custom HTML to the screen options panel.
	$screen->add_option('wcc_columns_option', ''); 
	
}
function wccm_register_settings()
{
	//old settings
	$hide_total_spent_column = get_user_meta(get_current_user_id(), 'wccm-hide-total-spent-column', true);
	$hide_order_column = get_user_meta(get_current_user_id(), 'wccm-hide-orders-column', true);
	$options = array();
	if($hide_total_spent_column || $hide_order_column)
		$options['disable_order_total_spent_column_sort'] = true;
	if(!empty($options))
	{
		update_option( 'wccm_general_options', $options );
		delete_user_meta(get_current_user_id(), 'wccm-hide-total-spent-column');
		delete_user_meta(get_current_user_id(), 'wccm-hide-orders-column');
	}
	//
	
	register_setting('wccm_general_options_group','wccm_general_options');
}
//NO
function wccm_display_my_option()
{
	global $wccm_current_page;
	if(!isset($wccm_current_page) || $wccm_current_page != "CustomerTable")
			return; 
	
$hide_total_spent_column = get_user_meta(get_current_user_id(), 'wccm-hide-total-spent-column', true);
$hide_total_spent_column = isset($hide_total_spent_column) ? $hide_total_spent_column:false;
$hide_order_column = get_user_meta(get_current_user_id(), 'wccm-hide-orders-column', true);
$hide_order_column = isset($hide_order_column) ? $hide_order_column:false;
?>
<div id="custom-options-box" style="margin: 0; padding: 8px 20px 12px;" >
	<h4 style="margin-bottom:5px;"><?php _e('Hide colums (this will improve performance)', 'woocommerce-customers-manager'); ?></h4>
	<form  name="my_option_form" method="post">
		<input type="hidden" name="my_option_submit" value="1"></input>
		<label><?php _e('Hide Orders column?', 'woocommerce-customers-manager'); ?></label>
		<input type="checkbox" name="wccm-hide-orders-column" value="yes" <?php if($hide_order_column) echo 'checked="checked"'?></input>
		<br/>
		<label><?php _e('Hide Total spent column?', 'woocommerce-customers-manager'); ?></label>
		<input type="checkbox" name="wccm-hide-total-spent-column" value="yes" <?php if($hide_total_spent_column) echo 'checked="checked"'?>></input>
		<input class="button" style="display:block;"type="submit" value="Save"></input>
	</form>
</div>
<?php

}

/* function wccm_save_custom_options(){
	if(isset($_POST['my_option_submit']) AND $_POST['my_option_submit'] == 1)
	{
		//var_dump($_POST);
		if(isset($_POST['wccm-hide-total-spent-column']))
			update_user_meta(  get_current_user_id(), 'wccm-hide-total-spent-column', true );
		else
			update_user_meta(  get_current_user_id(), 'wccm-hide-total-spent-column', false );
		if(isset($_POST['wccm-hide-orders-column']))
			update_user_meta(  get_current_user_id(), 'wccm-hide-orders-column', true );
		else
			update_user_meta(  get_current_user_id(), 'wccm-hide-orders-column', false );
	}
} */
function wccm_wpuef_plugin_installed()
{
	return function_exists('wpuef_set_field');
}
function wccm_var_dump($var)
{
	echo "<pre>";
	var_dump($var);
	echo "</pre>";
}
function wccm_set_options($status, $option, $value) 
{
	update_user_meta(  get_current_user_id(), 'wccm-customers-options_per_page', $value );
	return $value;
}
function wccm_render_page($wccm_page)
{
    ?>
      <div class="wrap">
		<?php $wccm_page->render_page(); ?>
     </div>
    <?php
}
 
?>