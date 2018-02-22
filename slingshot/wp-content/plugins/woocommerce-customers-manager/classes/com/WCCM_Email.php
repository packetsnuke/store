<?php
class WCCM_Email  
{
	 public function __construct() 
	 {
	 
		
	}
	public function trigger( /* $order_id */ $recipient, $subject, $content, $email_type = "notification", $user = null) 
	{
	 
		global $wccm_configuration_model,$wccm_customer_model;
		$email_configuration = $wccm_configuration_model->get_email_templates_configurations();
		$template_to_used = $email_type == 'guest_to_restered' ? $email_configuration['guest_to_registered_email_template'] : $email_configuration['customer_notification_email_template'];
		$use_footer_and_or_header = $email_type == 'guest_to_restered' ? $email_configuration['guest_to_registered_header_footer_inlcude'] : $email_configuration['customer_notification_header_footer_inlcude'];
		
		
		//$mail = new WC_Emails();
		$mail =  WC()->mailer();
		$email_heading = get_bloginfo('name');
		
		ob_start();
		if($use_footer_and_or_header == 'all' || $use_footer_and_or_header == 'header')
			$mail->email_header($email_heading );
        //$message =  ob_get_clean();		
		$message = str_replace('[message_body]', $content, $template_to_used);
		if(isset($user))
		{
			$user_data = $wccm_customer_model->get_user_data($user->ID);
			$billing_first_name = $wccm_customer_model->get_user_meta($user->ID, 'billing_first_name');
			$billing_last_name = $wccm_customer_model->get_user_meta($user->ID, 'billing_last_name');
			$shipping_first_name = $wccm_customer_model->get_user_meta($user->ID, 'shipping_first_name');
			$shipping_last_name = $wccm_customer_model->get_user_meta($user->ID, 'shipping_last_name');
			
			$message = isset($user_data->first_name) ? str_replace('{first_name}', $user_data->first_name, $message) : str_replace('{first_name}', "", $message);
			$message = isset($user_data->last_name) ? str_replace('{last_name}', $user_data->last_name, $message) : str_replace('{last_name}', "", $message);
			$message = $billing_first_name ? str_replace('{billing_first_name}', $billing_first_name, $message) : str_replace('{billing_first_name}', "", $message);
			$message = $billing_last_name ? str_replace('{billing_last_name}', $billing_last_name, $message) : str_replace('{billing_last_name}', "", $message);
			$message = $shipping_first_name ? str_replace('{shipping_first_name}', $shipping_first_name, $message) : str_replace('{shipping_first_name}', "", $message);
			$message = $shipping_last_name ? str_replace('{shipping_last_name}', $shipping_last_name, $message) : str_replace('{shipping_last_name}', "", $message);
		}
		echo $message;
		//ob_start();
		if($use_footer_and_or_header == 'all' || $use_footer_and_or_header == 'footer')
			$mail->email_footer();
        //$message .=  ob_get_clean();
        $message =  ob_get_contents();
       ob_end_clean();

		add_filter('wp_mail_from_name',array(&$this, 'wp_mail_from_name'), 99, 1);
		add_filter('wp_mail_from', array(&$this, 'wp_mail_from'));
		$mail->send( $recipient, stripslashes($subject), stripslashes($message));
		remove_filter('wp_mail_from_name',array(&$this, 'wp_mail_from_name'));
		remove_filter('wp_mail_from',array(&$this, 'wp_mail_from'));
				
	}
	public function wp_mail_from_name($name) 
	{
		$sender_name = WCCM_Options::get_option('email_sender_name', '');
		return $sender_name == '' ? get_bloginfo('name'): $sender_name;
	}
	public function wp_mail_from($content_type) 
	{
		$email_sender_email = WCCM_Options::get_option('email_sender_email', '');
		return $email_sender_email == '' ? WCCM_Email::get_no_reply_address() : $email_sender_email;
	}
	public static function get_no_reply_address()
	{
		$server_headers = apache_request_headers();
		$domain = $server_headers['Host'] ;
		$domain = str_replace("www.", "", $domain);
		return 'noreply@'.$domain ;
	}
} 