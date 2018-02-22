<?php 
class WCCM_OrdersTablePage
{
	public function __construct()
	{
		add_action( 'manage_shop_order_posts_custom_column', array($this, 'manage_user_details_page_link_column'), 10, 2 );
		add_filter( 'manage_edit-shop_order_columns', array($this, 'add_user_details_page_link'),15 ); 
	}
	public function add_user_details_page_link($columns)
	 {
		
	   //remove column
	   //unset( $columns['tags'] );

	   //add column
	   $columns['wccm-user-details-page'] =__('Customer details', 'woocommerce-customers-manager'); 

	   return $columns;
	}
	public function manage_user_details_page_link_column( $column, $orderid ) 
	{
		if ( $column == 'wccm-user-details-page' ) 
		{
			$order = new WC_Order($orderid);
			if($order->get_user_id( ) !=0)
				  echo '<a target="_blank" class="" href="'.admin_url().'?page=woocommerce-customers-manager&customer='.$order->get_user_id( ).'&action=customer_details">'.
					'<span class="dashicons dashicons-admin-users"></span>'.
					'</a>';
			else echo '<a target="_blank" href="'.admin_url().'?page=woocommerce-customers-manager&customer_email='.WCCM_Order::get_billing_email($order).'&action=customer_details"><span class="dashicons dashicons-admin-users"></span></a>';
		}
		
		
	}
}
?>