<?php 
class WCCM_Html
{
	public function __construct()
	{
		
	}
	
	public function assign_orders_to_user_selector()
	{
		global $wccm_order_model;
		
		
		/* wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'select2'); */
		wp_enqueue_style('select2', WCCM_PLUGIN_PATH.'/css/select2.min.css'); 
		wp_enqueue_style('wccm-order-assigner', WCCM_PLUGIN_PATH.'/css/admin-order-assigner.css'); 
		
		wp_enqueue_script( 'select2', WCCM_PLUGIN_PATH.'/js/select2.min.js', array('jquery'));	
		wp_register_script( 'wccm-order-assigner', WCCM_PLUGIN_PATH.'/js/admin-order-assigner.js', array('jquery'));
							$js_options = array(
												'select2_placeholder' => __( 'Type an order id or select one from the list', 'woocommerce-customers-manager' )
								);
		wp_localize_script('wccm-order-assigner', "wccm_order_assigner", $js_options);
		wp_enqueue_script('wccm-order-assigner');
		
		?>
		<select class="wccm-order-assign-select2" 
			name="wccm_order_to_assign[]"  
			multiple="multiple" >
		</select>
		<label class="wccm_order_assign_checkbox_label"><input type="checkbox" name="wccm_order_to_assign_overwrite_billing_data"></input><?php _e('Overwrite order(s) billing data with user billing data', 'woocommerce-customers-manager'); ?></label>
		<label class="wccm_order_assign_checkbox_label"><input type="checkbox" name="wccm_order_to_assign_overwrite_shipping_data"></input><?php _e('Overwrite order(s) shipping data with user shipping data', 'woocommerce-customers-manager'); ?></label>
		
		<?php 
	}
}