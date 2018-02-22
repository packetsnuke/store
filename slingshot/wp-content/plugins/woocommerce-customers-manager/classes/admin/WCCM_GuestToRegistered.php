<?php 
class WCCM_GuestToRegistered
{
	var $conversion_result;
	public function __construct()
	{
		add_action( 'add_meta_boxes', array( &$this, 'woocommerce_metaboxes' ) );
		//add_action( 'woocommerce_process_shop_order_meta', array( &$this, 'woocommerce_process_shop_ordermeta' ), 5, 2 );
		if(is_admin())
			add_action( 'wp_ajax_wccm_convert_guest_to_registered', array( &$this, 'wccm_convert_guest_to_registered' ) );
	}
	//Posting info
	/* public function woocommerce_process_shop_ordermeta($post_id, $post)
	{
		echo "<pre>";
		var_dump($_POST['wccm-conversion']);
		echo "</pre>";
		return;
	} */
	public function wccm_convert_guest_to_registered()
	{
		$this->conversion_result =  "";
		//var_dump($_POST);
		$order = wc_get_order($_POST['order-id']);
		if($order===false)
			wp_die();
		
		$send_email = $_POST['notification-email'] == 'yes' ? true : false;
		//$merge_if_existing = $_POST['merge-users-if-existing'] == 'yes' ? true : false;
		$this->convert_guest_to_registered_by_order_id($order, $send_email, false );
		if($this->conversion_result != "")
		{
			echo $this->conversion_result;
		}
		wp_die();
	}
	public function convert_guest_to_registered_by_order_id($order, $send_email, $is_checkout = false )
	{
		global $wccm_order_model, $wccm_customer_model;
		$merge_if_existing = $is_checkout ? !WCCM_Options::get_option('do_not_convert_if_email_is_already_associated') : !WCCM_Options::get_option('do_not_convert_if_existing_on_manual_conversion');
		
		//registering
		$conversion_result = $wccm_customer_model->register_new_user($order);
		$user = $conversion_result['user'];
		$conversion_result['result'] = $user['already_exists'] && !$merge_if_existing ? __('Email is already associated to another user. Conversion was not performed.', 'woocommerce-customers-manager') : $conversion_result['result'];
		$this->conversion_result = $conversion_result['result'];
		
		//change orders users id reference
		$billing_email = WCCM_Order::get_billing_email($order);
		
		if(!$user['already_exists'] || $merge_if_existing )
		{
			$wccm_order_model->update_order_customer_id($user['id'], $billing_email, $is_checkout ? WCCM_Order::get_order_id($order) : null);
			
			//send notification email
			if($send_email && !$user['already_exists'] && isset($billing_email))
			{
				$mail = new WCCM_Email();
				$subject = __('New account', 'woocommerce-customers-manager');
				$text = sprintf (__('<br/>User: %s<br/>Password: %s<br/>', 'woocommerce-customers-manager'), $user['user'],$user['password']);
				$user_obj = get_user_by($user['id']);
				$mail->trigger($billing_email, $subject, $text, 'guest_to_restered', is_object($user_obj ) ? $user_obj  : null);
			}
		}
	}
	public function woocommerce_metaboxes()
	{
		add_meta_box( 'woocommerce-customers-manager-guest-to-registered', __('Guest to registered customer conversion', 'woocommerce-customers-manager'), array( &$this, 'woocommerce_guest_to_registered_conversion_box' ), 'shop_order', 'side', 'high');
	}
	function woocommerce_guest_to_registered_conversion_box($post) 
	{
		$order = new WC_Order($post->ID);
		$user_id = $order->get_user_id();
		if(!empty($user_id))
		{
			echo "<h3>".__('Customer already registered', 'woocommerce-customers-manager')."</h3>";
			return;
		}
		wp_enqueue_script('guest-to-registered', WCCM_PLUGIN_PATH.'/js/admin-guest-to-registered-customer.js'); 
		wp_enqueue_style('guest-to-registered', WCCM_PLUGIN_PATH.'/css/admin-guest-to-registered-customer.css');
		?>
		<div id="wccm-conversion-box">
			<input type="hidden" id="wccm-conversion-order-id" value="<?php echo $post->ID /* $order->billing_email; */ ?>"></input>
			<label for="wccm-send-notification-email"><?php _e('Send an email with login info to the customer?', 'woocommerce-customers-manager') ?></label>
			<br/>			
			<select type="checkbox" id="wccm-conversion-notification-email">
				<option value="yes"><?php _e('Yes', 'woocommerce-customers-manager') ?></option>
				<option value="no"><?php _e('No', 'woocommerce-customers-manager') ?></option>
			</select>
			<!--<br/>
			<select type="checkbox" id="wccm-merge-users-if-existing">
				<option value="no"><?php _e('No', 'woocommerce-customers-manager') ?></option>
				<option value="yes"><?php _e('Yes', 'woocommerce-customers-manager') ?></option>
			</select>-->
			<p><?php _e('The plugin will convert guest user to registered. <strong>In case the billing email used by the guest is already associated to a register user, the guest orders will be assigned to that registered user according to the merge options that can be setted in the plugin option menu.</strong>', 'woocommerce-customers-manager');?></p>
			<br/><br/>
			<input type="submit" id="wccm-conversion-submit-button" class="button" value="<?php _e('Create customer', 'woocommerce-customers-manager') ?>"></input>
		</div>
		<div id="wccm-conversion-loader">
		</div>
		<div id="wccm-conversion-result">
		<h3><?php _e('Operation performed', 'woocommerce-customers-manager') ?></h3>
		<p id="wccm-conversion-text-result"></p>
		<a class="button" href="<?php get_edit_post_link( $post->ID ); ?>"><?php _e('Reload order page to see updated data', 'woocommerce-customers-manager') ?></a>
		</div>
		<?php
	}
	private function random_password($length)
	{
		$string = '';
		$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
		for ($i = 0; $i < $length; $i++) {
			  $string .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $string;
	}
	
}
?>