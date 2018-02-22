<?php 
$wccm_active_plugins = get_option('active_plugins');
$wccm_acf_pro = 'advanced-custom-fields-pro/acf.php';
$wccm_acf_pro_is_aleady_active = in_array($wccm_acf_pro, $wccm_active_plugins) || class_exists('acf') ? true : false;
if(!$wccm_acf_pro_is_aleady_active)
	include_once( WCCM_PLUGIN_ABS_PATH . '/classes/acf/acf.php' );

$wccm_hide_menu = true;

add_action('admin_init', 'wccm_acf_settings_init');
function wccm_acf_settings_init()
{
	/* if(version_compare( WC_VERSION, '2.7', '>=' ))
		acf_update_setting('select2_version', 4); */
}

if ( ! function_exists( 'is_plugin_active' ) ) 
{
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
}
/* Checks to see if the acf pro plugin is activated  */
if ( is_plugin_active('advanced-custom-fields-pro/acf.php') )  {
	$wccm_hide_menu = false;
}

/* Checks to see if the acf plugin is activated  */
if ( is_plugin_active('advanced-custom-fields/acf.php') ) 
{
	add_action('plugins_loaded', 'wccm_load_acf_standard_last', 10, 2 ); //activated_plugin
	add_action('deactivated_plugin', 'wccm_detect_plugin_deactivation', 10, 2 ); //activated_plugin
	$wccm_hide_menu = false;
}
function wccm_detect_plugin_deactivation(  $plugin, $network_activation ) { //after
   // $plugin == 'advanced-custom-fields/acf.php'
	//wccm_var_dump("wccm_detect_plugin_deactivation");
	$acf_standard = 'advanced-custom-fields/acf.php';
	if($plugin == $acf_standard)
	{
		$active_plugins = get_option('active_plugins');
		$this_plugin_key = array_keys($active_plugins, $acf_standard);
		if (!empty($this_plugin_key)) 
		{
			foreach($this_plugin_key as $index)
				unset($active_plugins[$index]);
			update_option('active_plugins', $active_plugins);
			//forcing
			deactivate_plugins( plugin_basename( WP_PLUGIN_DIR.'/advanced-custom-fields/acf.php') );
		}
	}
} 
function wccm_load_acf_standard_last($plugin, $network_activation = null) { //before
	$acf_standard = 'advanced-custom-fields/acf.php';
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_keys($active_plugins, $acf_standard);
	if (!empty($this_plugin_key)) 
	{ 
		foreach($this_plugin_key as $index)
			//array_splice($active_plugins, $index, 1);
			unset($active_plugins[$index]);
		//array_unshift($active_plugins, $acf_standard); //first
		array_push($active_plugins, $acf_standard); //last
		update_option('active_plugins', $active_plugins);
	} 
}

if(!$wccm_acf_pro_is_aleady_active)
	add_filter('acf/settings/path', 'wccm_acf_settings_path');
function wccm_acf_settings_path( $path ) 
{
 
    // update path
    $path = WCCM_PLUGIN_ABS_PATH. '/classes/acf/';
    
    // return
    return $path;
    
}
if(!$wccm_acf_pro_is_aleady_active)
	add_filter('acf/settings/dir', 'wccm_acf_settings_dir');
function wccm_acf_settings_dir( $dir ) {
 
    // update path
    $dir = WCCM_PLUGIN_PATH . '/classes/acf/';
    
    // return
    return $dir;
    
}

function wccm_acf_init() {
    
    include WCCM_PLUGIN_ABS_PATH . "/assets/fields.php";
    
}
add_action('acf/init', 'wccm_acf_init');

//hide acf menu
if($wccm_hide_menu)	
	add_filter('acf/settings/show_admin', '__return_false');



//Custom fields
function wcam_include_field_types_email_html_preview( $version ) 
{
	if(!class_exists('acf_html_email_preview_field'))
		include_once(WCCM_PLUGIN_ABS_PATH.'/classes/com/vendor/acf-email-html-preview-field/acf-email-html-preview-v5.php');
}

add_action('acf/include_field_types', 'wcam_include_field_types_email_html_preview');

//Avoid custom fields metabox removed by pages
add_filter('acf/settings/remove_wp_meta_box', '__return_false');
?>