<?php 
class WCCM_Cart
{
	var $message_already_printed = false;
	public function __construct()
	{
		add_action('woocommerce_add_to_cart_validation', array(&$this, 'cart_add_to_validation'), 10, 5);
		add_action('woocommerce_update_cart_validation', array(&$this, 'cart_update_validation'), 10, 4);
		add_action('woocommerce_checkout_process', array( &$this, 'checkout_validation' ));
	}
	
	public function cart_add_to_validation( $original_result, $product_id, $quantity , $variation_id = 0, $variations = null )
	{
		$can_purchase = $this->current_user_can_purchase() ? $original_result : false;
		
		return $can_purchase;
	}
	public function cart_update_validation($original_result, $cart_item_key, $values, $quantity )
	{
		global $woocommerce;
		$can_purchase = $this->current_user_can_purchase();
		if(!$can_purchase)
			$woocommerce->cart->empty_cart();
		
		$can_purchase = $this->current_user_can_purchase() ? $original_result : false;
		return $can_purchase;
	}
	public function checkout_validation( )
	{
		global $was_product_model;
		$results = $this->current_user_can_purchase();
		
	}
	public function current_user_can_purchase()
	{
		global $woocommerce, $wccm_customer_model;
		$is_blocked = $wccm_customer_model->is_blocked_customer(get_current_user_id());
		if($is_blocked && !$this->message_already_printed)
		{
			$this->message_already_printed = true;
			wc_add_notice(  __( 'You are not authorized to purchase any item.', 'woocommerce-customers-manager') ,'error');
		}
		return !$is_blocked;
	}
}