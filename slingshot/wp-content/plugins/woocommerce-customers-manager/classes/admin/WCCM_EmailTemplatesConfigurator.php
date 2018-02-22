<?php 
class WCCM_EmailTemplatesConfigurator
{
	public function __construct()
	{
		//add_filter('acf/init', array(&$this,'init_options_menu'));
		$this->init_options_menu();
	}
	function init_options_menu()
	{
		if( function_exists('acf_add_options_page') ) 
		{
			 acf_add_options_sub_page(array(
				'page_title' 	=> 'Email templates configurator',
				'menu_title'	=> 'Email templates configurator',
				'parent_slug'	=> 'woocommerce-customers-manager',
			));
			
			
			
			add_action( 'current_screen', array(&$this, 'cl_set_global_options_pages') );
		}
	}
	/**
	 * Force ACF to use only the default language on some options pages
	 */
	function cl_set_global_options_pages($current_screen) 
	{
	  if(!is_admin())
		  return;
	  
	  //wccm_var_dump($current_screen->id);
	  global $wccm_wpml_helper;
	  $page_ids = array(
		"customers_page_acf-options-email-templates-configurator"
	  );
	  
	  if (in_array($current_screen->id, $page_ids)) 
	  {
		$wccm_wpml_helper->switch_to_default_language();
		add_filter('acf/settings/current_language', array(&$this, 'cl_acf_set_language'), 100);
	  }
	}
	

	function cl_acf_set_language() 
	{
	  return acf_get_setting('default_language');
	}

	/**
	 * Wrapper around get_field() to get the "global" option values.
	 * This is the function you'll want to use in your templates instead of get_field() for "global" options.
	 */
	/* function get_global_option($name) 
	{
	  add_filter('acf/settings/current_language',  array(&$this, 'cl_acf_set_language'), 100);
	  $option = get_field($name, 'option');
	  remove_filter('acf/settings/current_language', array(&$this,'cl_acf_set_language'), 100);
	  return $option;
	} */
}
?>