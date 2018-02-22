<?php 
class WCCM_OrderDetailsPage
{
	public function __construct()
	{
		add_action( 'add_meta_boxes', array( &$this, 'add_meta_boxes' ) );
	}
	public function add_meta_boxes()
	{
		add_meta_box( 'woocommerce-customers-manager-user-note', __('User notes', 'woocommerce-customers-manager'), array( &$this, 'add_user_note_meta_box' ), 'shop_order', 'side', 'high');
		
	}
	public function add_user_note_meta_box($post)
	{
		global $wccm_customer_model;
		$order = wc_get_order($post->ID);
		$user_id = $order->get_customer_id();
		
		if($user_id == 0)
			return;
		
		?>
			<p><?php echo $wccm_customer_model->get_user_notes($user_id); ?></p>
			<a class="button-primary" target="_blank" href="<?php echo get_admin_url()."admin.php?page=woocommerce-customers-manager&customer={$user_id}&action=customer_details"; ?>"> <?php _e('Edit', 'woocommerce-customers-manager' ); ?> </a>
		<?php
	}
}
?>