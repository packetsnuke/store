<?php 
class WCCM_CheckoutPage
{
	public function __construct()
	{
		add_action('woocommerce_checkout_order_processed', array( &$this, 'check_if_guest_to_regiester_customer_has_to_be_performed' )); //After checkout
	}
	
	function check_if_guest_to_regiester_customer_has_to_be_performed( $order_id)
	{
		global $wccm_guest_to_registered_helper;
		
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
		  return $order_id;
		$order = wc_get_order($order_id);
		if($order == false)
			return $order_id;
		$automatic_conversion = WCCM_Options::get_option('automatic_conversion');
		$send_email = WCCM_Options::get_option('send_email_after_automatic_conversion');
		$send_email = isset($send_email) && $send_email != 'false' ? true : false;
		 
		if(isset($automatic_conversion) && $automatic_conversion != 'false' && $order->get_user_id( ) == 0)
		{
			try{
				$wccm_guest_to_registered_helper->convert_guest_to_registered_by_order_id( $order, $send_email, true);
			}catch(Exception $e){}
		}
	}
}
?>