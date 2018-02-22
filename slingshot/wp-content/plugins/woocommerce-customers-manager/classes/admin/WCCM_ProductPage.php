<?php 
class WCCM_ProductPage
{
	public function __construct()
	{
		add_action('woocommerce_variation_options',array(&$this,'add_who_bought_feature_to_variation'),1,3);
		add_action('woocommerce_product_options_pricing',array(&$this,'add_who_bought_feature_to_simple_product'));
	}
	public function add_who_bought_feature_to_variation($loop, $variation_data, $variation )
	{
		//wccm_var_dump($variation);
		 echo '<a target="_blank" class="" href="'.admin_url().'?page=woocommerce-customers-manager&filter-by-product='.$variation->ID.'" style="text-decoration:none;" >'.
					'<span class="dashicons dashicons-admin-users" style="margin-top:5px;"></span><span style="color:black; ">'.__('Who bought?', 'woocommerce-customers-manager').'</span>'.
					'</a>';
	}
	public function add_who_bought_feature_to_simple_product()
	{
		global $post;
		echo '<p class="form-field _sale_price_field ">';
		echo '<label >' . __( 'Who bought?', 'woocommerce-customers-manager' ) . '</label>';
		echo   '<a  target="_blank" class="" href="'.admin_url().'?page=woocommerce-customers-manager&filter-by-product='.$post->ID.'">'.
					'<span class="dashicons dashicons-admin-users"></span>'.
				'</a>';
		echo '</p>';
	}
}
?>