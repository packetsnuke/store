<?php class WCCM_Configuration
{
	var $options;
	public function __construct()
	{
		
	}
	public function cl_acf_set_language() 
	{
	  return acf_get_setting('default_language');
	}
	public function get_email_templates_configurations($option_name = null, $default_value = null)
	{
		add_filter('acf/settings/current_language',  array(&$this, 'cl_acf_set_language'), 100);
		$return_value = array();
	
		if(isset($option_name))
		{
			
			$return_value =  get_field($option_name, 'option');
			$return_value = isset($return_value) ? $return_value : $default_value;
		}
		else
		{
			$return_value['guest_to_registered_email_template'] = get_field('wccm_guest_to_registered_email_template', 'option') ? get_field('wccm_guest_to_registered_email_template', 'option') : '[message_body]';
			/*all : Include header and footer
			header : Include only the header
			footer : Include only the footer
			none : Do not include anything*/
			$return_value['guest_to_registered_header_footer_inlcude'] = get_field('wccm_guest_to_registered_header_footer_inlcude', 'option') ? get_field('wccm_guest_to_registered_header_footer_inlcude', 'option') : 'all';
			$return_value['customer_notification_email_template'] = get_field('wccm_customer_notification_email_template', 'option') ? get_field('wccm_customer_notification_email_template', 'option') : '[message_body]';
			$return_value['customer_notification_header_footer_inlcude'] = get_field('wccm_customer_notification_header_footer_inlcude', 'option') ? get_field('wccm_customer_notification_header_footer_inlcude', 'option') : 'all';
		}
		remove_filter('acf/settings/current_language', array(&$this,'cl_acf_set_language'), 100);
		return  $return_value;
	}
	function get_options($option_name = null, $dafault_value = null)
	{
		$this->options = !isset($this->options) ? get_option( 'wccm_general_options') : $this->options;
		
		$result = $this->options;
		if(isset($option_name))
			$result = !isset($result[$option_name]) ? $dafault_value : $result[$option_name];
		
		
		return $result;
	}
}
?>