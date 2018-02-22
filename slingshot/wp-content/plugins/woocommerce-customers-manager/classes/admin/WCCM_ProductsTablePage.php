<?php class WCCM_ProductsTablePage
{
	public function __construct()
	{
		add_action( 'manage_product_posts_custom_column', array(&$this, 'manage_who_bought_column'), 10, 2 );
		add_filter( 'manage_edit-product_columns', array(&$this,'add_who_bought_column'),15 );
	}
	function manage_who_bought_column( $column, $postid ) 
	{
		if ( $column == 'who-bought' ) 
		{
			//echo get_post_meta( $postid, 'who-bought', true );
			  echo '<a class="" target="_blank" href="'.admin_url().'?page=woocommerce-customers-manager&filter-by-product='.$postid.'">'.
					'<span class="dashicons dashicons-admin-users"></span>'.
					'</a>';
		}
	}
	function add_who_bought_column($columns)
	 {

	   //remove column
	   //unset( $columns['tags'] );

	   //add column
	   $columns['who-bought'] =__('Who bought?', 'woocommerce-customers-manager'); 

	   return $columns;
	}
}
?>