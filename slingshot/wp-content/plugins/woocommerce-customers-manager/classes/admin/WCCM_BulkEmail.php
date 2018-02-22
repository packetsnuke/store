<?php 
class WCCM_BulkEmail
{
	var $mail_message_outcome;
	var $message_type;
	var $render_button;
	public function __construct()
	{
		$this->message_type = "updated";
	}
	
	public function send($args)
	{
		if(!$args || $args == 'none')
		{
			$this->mail_message_outcome =  __('Error: please select at least one recipient.', 'woocommerce-customers-manager');
			 $this->message_type = "error";
			return;
		}
		global $wpdb;
		//$query_string = "SELECT user_email FROM {$wpdb->users} WHERE ID IN (".$args.")";
		$query_string = "SELECT * FROM {$wpdb->users} WHERE ID IN (".$args.")";
		
		//$emails = $wpdb->get_col($query_string);
		$users = $wpdb->get_results($query_string);
		//foreach($emails as $email)
		foreach($users as $user)
		{
			$mail = new WCCM_Email();
			$mail->trigger($user->user_email, $_POST['mail_subject'], $_POST['mail_text'], "notification", $user);
		}
		 $this->mail_message_outcome =  __('Your message has been successfully sent.', 'woocommerce-customers-manager');
		 $this->message_type = "updated";
	}
	public function get_ids_and_emails($customer_ids)
	{
		global $wpdb;
		$query_string = "SELECT ID, user_email FROM {$wpdb->users} WHERE ID IN (".$customer_ids.") GROUP BY ID";
		$id_emails = $wpdb->get_results($query_string);
		return $id_emails;
	}
	public static function force_dequeue_scripts($enqueue_styles)
	{
		if ( class_exists( 'woocommerce' ) && isset($_GET['page']) && $_GET['page'] == 'wccm-bulk-email-customer') 
		{
			global $wp_scripts;
			$wp_scripts->queue = array();
			WCCM_BulkEmail::enqueue_scripts();

		} 
	}
	public static function enqueue_scripts()
	{
		if ( class_exists( 'woocommerce' ) && isset($_GET['page']) && $_GET['page'] == 'wccm-bulk-email-customer') 
		{
			wp_enqueue_script('jquery') ;
			wp_enqueue_script('jquery-ui-core') ;
			wp_enqueue_script('jquery-ui-slider') ;
			wp_enqueue_script('jquery-ui-progressbar');
			
		}
	}
	public function render_page()
	{
		wp_enqueue_style('wccm-common', WCCM_PLUGIN_PATH.'/css/common.css');   
		wp_enqueue_style('bulkemail-css', WCCM_PLUGIN_PATH.'/css/bulkemail.css'); 
		//ids set by customers list view
		$customer_ids = isset($_REQUEST["customer"]) ? implode(",", $_REQUEST["customer"]):"none";
		//come from same view
		$customer_ids = isset($_REQUEST["customer_ids"]) ? implode(",",$_REQUEST["customer_ids"]):$customer_ids;
		$this->render_button = true;
		
		$customer_ids_email = null;
		if($customer_ids != 'none')
		{
			$customer_ids_email = $this->get_ids_and_emails($customer_ids);
		}
		
		if(isset($_REQUEST["send_bulk_mail"]))
		{
			$this->send($customer_ids);
		}
		
		//Results
		if($customer_ids == "none" && ( !isset($_GET['page']) || $_GET['page'] != 'wccm-bulk-email-customer'))
		{
			$this->render_button = false;
			echo '<div id="message" class="error"><ul>';
				//foreach ( $this->mail_message_outcome as $msg )
					echo '<li>' . __('Error: please select at least one recipient', 'woocommerce-customers-manager')  . '</li>';
			echo '</ul></div>';
		}			
		else if ( ! empty( $this->mail_message_outcome ) ) 
		{
			echo '<div id="message" class="'.$this->message_type.'"><ul>';
				//foreach ( $this->mail_message_outcome as $msg )
					echo '<li>' . $this->mail_message_outcome  . '</li>';
			echo '</ul></div>';
		}
		
		//TinyMCE editor
		//wp_editor( "", 'mail_text' );
		$js_src = includes_url('js/tinymce/') . 'tinymce.min.js';
		$css_src = includes_url('css/') . 'editor.css';
		echo '<script src="' . $js_src . '" type="text/javascript"></script>';
		wp_register_style('tinymce_css', $css_src);
		wp_enqueue_style('tinymce_css');
		wp_enqueue_style( 'wccm-select2-style',  WCCM_PLUGIN_PATH.'/css/select2.min.css' ); 
		
		//wp_enqueue_script( 'wccm-select2-script', WCCM_PLUGIN_PATH.'/js/select2.min.js', array('jquery') );
		wp_enqueue_script( 'wccm-customer-ajax-autocomplete',WCCM_PLUGIN_PATH.'/js/admin-bulk-email-customer-autocomplete.js', array('jquery') );
		?>
		<script>
			jQuery.fn.select2=null;
			function ignoreerror()
			{
			   return true
			}
			window.onerror=ignoreerror();
		</script>
		
		<script type='text/javascript' src='<?php echo WCCM_PLUGIN_PATH.'/js/select2.min.js'; ?>'></script>
		<h2 id="add-new-user"> 
						<?php  _e('Bulk emails', 'woocommerce-customers-manager');?> 
			</h2>
		<div id="bulk-email-box">
				<p>
					<form name="send-mail-form" method="post" action="">
						<input type="hidden" value="true" name="send_bulk_mail"></input>
						<input type="hidden" value="wccm-bulk-email-customer" name="action"></input>
						<!--<input type="hidden" value='<?php echo $customer_ids ?>' name="customer_ids"></input>-->
						
						<h4><?php  _e('Customers (you can search typing name, last name or email)', 'woocommerce-customers-manager');?></h4>
				
						<select class="js-data-customers-ajax" name="customer_ids[]" id="customer_ids" multiple="multiple"> 
						
						<?php if(isset($customer_ids_email)) 
								foreach($customer_ids_email as $customer) 
								echo '<option value="'.$customer->ID.'" selected="selected">'.$customer->user_email.'</option>'; 
						?>
						</select>
						
						
						<h4><?php  _e('Subject', 'woocommerce-customers-manager'); ?></h4> 
						<input type="text" style="width:350px;" name="mail_subject"></input>
						<h4><?php  _e('Text (HTML code is allowed only if the visual editor has been disable in the Options menu)', 'woocommerce-customers-manager'); ?></h4> 
						<p><?php  _e('use <strong>{first_name}</strong>, <strong>{last_name}</strong>, <strong>{billing_first_name}</strong>, <strong>{billing_last_name}</strong>, <strong>{shipping_first_name}</strong>, <strong>{shipping_last_name}</strong> placeholders to render user first and last names in the email body.', 'woocommerce-customers-manager'); ?></p>
						<textarea style="clear:both" cols="70" rows="12"   name="mail_text" id="mail_text"></textarea>
						<p class="submit">
						<?php /* if($this->render_button && $this->message_type != "error" ): */?>
						<input class="button-primary" type="submit" value=" <?php _e('Send email', 'woocommerce-customers-manager' ); ?>"> </input>
						<?php //endif; ?>
						</p>
					</form>
				</p>
		</div>
		<script>
		var wccm_disable_visual_editor = <?php if(WCCM_Options::get_option('disable_email_visual_editor') === 'true') echo 'true'; else echo 'false'; ?>;
		jQuery(document).ready(function()
		{
			if(!wccm_disable_visual_editor)
				setTimeout(function(){ tinymce.init({selector:'textarea'}); }, 1500);
		});
			
		</script>
		<?php
	}
	private function var_debug($var)
	{
		echo "<pre>";
		var_dump($var);
		echo "</pre>";
	}

}
?>