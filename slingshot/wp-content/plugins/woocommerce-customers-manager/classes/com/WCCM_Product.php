<?php class WCCM_Product
{
	public function __construct()
	{
		if(is_admin())
		{
			add_action('wp_ajax_wccm_get_products_list', array(&$this, 'ajax_get_products_partial_list'));
			add_action('wp_ajax_wccm_get_product_categories_list', array(&$this, 'ajax_get_product_categories_partial_list'));
		}
	}
	 public function ajax_get_products_partial_list()
	 {
		 $products = $this->get_product_list($_GET['product']);
		 echo json_encode( $products);
		 wp_die();
	 }
	  public function ajax_get_product_categories_partial_list()
	 {
		 $product_categories = $this->get_product_category_list($_GET['product_category']);
		 echo json_encode( $product_categories);
		 wp_die();
	 }
	 
	 public function get_product_list($search_string = null)
	 {
		global $wpdb, $wccm_wpml_helper;
		 $query_string = "SELECT products.ID as id, products.post_title as product_name, product_meta.meta_value as product_sku, products.post_type as type
							 FROM {$wpdb->posts} AS products
							 LEFT JOIN {$wpdb->postmeta} AS product_meta ON product_meta.post_id = products.ID AND product_meta.meta_key = '_sku'
							 WHERE (products.post_type = 'product' OR products.post_type = 'product_variation') ";
		if($search_string)
				$query_string .=  " AND ( products.post_title LIKE '%{$search_string}%' OR product_meta.meta_value LIKE '%{$search_string}%' OR products.ID LIKE '%{$search_string}%' ) ";
		
		$query_string .=  " GROUP BY products.ID ";
		$result = $wpdb->get_results($query_string ) ;
		
		//WPML
		if($wccm_wpml_helper->wpml_is_active())
		{
			$result = $wccm_wpml_helper->remove_translated_id($result);
		}
		
		return $result;
	 }
	 
	 public function get_product_category_list($search_string = null)
	 {
		 global $wpdb, $wccm_wpml_helper;
		  $query_string = "SELECT product_categories.term_id as id, product_categories.name as category_name
							 FROM {$wpdb->terms} AS product_categories
							 LEFT JOIN {$wpdb->term_taxonomy} AS tax ON tax.term_id = product_categories.term_id 							 						 	 
							 WHERE tax.taxonomy = 'product_cat' 
							 AND product_categories.slug <> 'uncategorized' 
							";
		 if($search_string)
					$query_string .=  " AND ( product_categories.name LIKE '%{$search_string}%' )";
			
		$query_string .=  " GROUP BY product_categories.term_id ";
		$result = $wpdb->get_results($query_string ) ;
		
		//WPML
		if($wccm_wpml_helper->wpml_is_active())
		{
			$result = $wccm_wpml_helper->remove_translated_id($result, 'product_cat');
		} 
		
		return $result;
	 }
	 public function get_variation_complete_name($variation_id)
	{
		$product_name = "N/A (id:".$variation_id.")";
		try
		{
			$variation = new WC_Product_Variation($variation_id);
			
			$product_name = $variation->get_title()." - ";	
			if($product_name == " - ")
				return false;
			$attributes_counter = 0;
			foreach($variation->get_variation_attributes( ) as $attribute_name => $value)
			{
				
				if($attributes_counter > 0)
					$product_name .= ", ";
				$meta_key = urldecode( str_replace( 'attribute_', '', $attribute_name ) ); 
				
				$product_name .= " ".wc_attribute_label($meta_key).": ".$value;
				$attributes_counter++;
			}
		}catch(Exception $e){}
		return $product_name;
	}
	 public static function get_variations($product_id)
	 {
		global $wpdb, $wccm_wpml_helper;
		
		if($wccm_wpml_helper->wpml_is_active())
			$product_id = $wccm_wpml_helper->get_original_id($product_id);
		
		 $query = "SELECT products.ID, product_price.meta_value as price
		           FROM {$wpdb->posts} AS products 
		           INNER JOIN {$wpdb->postmeta} AS product_price ON product_price.post_id = products.ID
				   WHERE product_price.meta_key = '_price' 
				   AND	 products.post_parent = {$product_id} AND products.post_type = 'product_variation' "; //_regular_price
		 $result =  $wpdb->get_results($query); 
		 //wcpst_var_dump($result);
		 return isset($result) ? $result : null;		 
	 }
	 public function is_variation($product_id)
	 {
		 if(!isset($product_id))
			 return 0;
		 global $wpdb, $wccm_wpml_helper;
		
		 if($wccm_wpml_helper->wpml_is_active())
			$product_id = $wccm_wpml_helper->get_original_id($product_id, 'product_variation');
		
		$query = "SELECT products.post_parent as product_parent 
				  FROM {$wpdb->posts} AS products 
				  WHERE  products.ID = {$product_id} ";
				  
		 $result =  $wpdb->get_results($query); 
		 return isset($result) && isset($result[0]) && $result[0] != "" ? $result[0]->product_parent : 0;	
	 }
}
?>