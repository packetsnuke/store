<?php
class WCCM_Order
{
	var $start_date;
	var $end_date;
	var $min_amount;
	var $max_amount;
	var $min_amount_total;
	var $max_amount_total;
	var $statuses;
	var $per_page;
	var $page_num;
	var $customer_ids;
	var $product_ids;
	var $category_ids;
	var $product_relationship;
	var $product_category_relationship;
	var $product_category_filters_relationship;
	
	public function __construct()
	{
		if(is_admin())
		{
			add_action('wp_ajax_wccm_get_orders_data', array(&$this, 'ajax_get_orders'));
			add_action('wp_ajax_wccm_get_orders_tot_num', array(&$this, 'ajax_get_tot_orders_num'));
			add_action('wp_ajax_wccm_get_order_list', array(&$this, 'ajax_get_order_list'));
		}
	}
	public static function get_order_id($order)
	{
		return version_compare( WC_VERSION, '2.7', '<' ) ? $order->id : $order->get_id();
	}
	public static function get_billing_email($order)
	{
		if(version_compare( WC_VERSION, '2.7', '<' ))
			return $order->billing_email;
		
		return $order->get_billing_email();
	}
	public function update_order_customer_id($user_id, $email, $order_id = null)
	{
		$additional_where = isset($order_id) ? " AND ordermeta.post_id = {$order_id} " : "";
		global $wpdb;
		$query = "UPDATE 	  {$wpdb->postmeta} as ordermeta
				  INNER JOIN  {$wpdb->postmeta} as ordermeta2 ON ordermeta2.post_id = ordermeta.post_id
				  SET    	  ordermeta.meta_value = '{$user_id}'				 
				  WHERE  	  ordermeta2.meta_key = '_billing_email'
				  AND 		  ordermeta2.meta_value = '{$email}'
				  AND 		  ordermeta.meta_key = '_customer_user' ".$additional_where;
		
		$wpdb->get_results($query);
	}
	public function get_order_months($post_type = 'shop_order' )
	{
		global $wpdb;
		 $months = $wpdb->get_results( $wpdb->prepare( "
            SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
            FROM $wpdb->posts
            WHERE post_type = %s
            ORDER BY post_date DESC
        ", $post_type ) );
		
		return $months;
	}
	public function ajax_get_order_list()
	{
		$resultCount = 50;
		$search_string = isset($_GET['search_string']) ? $_GET['search_string'] : null;
		$page = isset($_GET['page']) ? $_GET['page'] : null;
		$offset = isset($page) ? ($page - 1) * $resultCount : null;
		$orders = $this->get_order_list($search_string ,$offset, $resultCount);
		 echo json_encode( $orders);
		 wp_die();
	}
	public function ajax_get_orders()
	{
		$orders_array = array();
		$this->get_request_parameters();
		//product and product categories ----> WPML
		$customers = $this->get_customer_ids_from_orders(false);
		/* foreach($customers_id as $customer_id)
		{
			$order_temp = array();
			$order_temp['id'] = $order->id;
			//$bad_char = array('"', "'", ",");
			//$order_temp['customer_id'] = $order->customer_id;
			//$order_temp['billing_first_name'] = str_replace( $bad_char, "", $order->billing_first_name);
			//$order_temp['billing_last_name'] = str_replace($bad_char, "", $order->billing_last_name);
			//...
			
			
			array_push($orders_array, $order_temp);
		}  */
		
		//echo json_encode($customers['ids']);
		/* if(isset($this->page_num) && $this->page_num > 1)
			$customers['emails'] = ",".implode(",",$customers['emails']);
		else
			$customers['emails'] = implode(",",$customers['emails']);
		$customers['emails'] = rtrim(base64_encode(gzdeflate($customers['emails'], 9)), '='); */
		echo json_encode($customers);
		
		wp_die(); 
		/*return $orders_array;*/
	}
	public function ajax_get_tot_orders_num()
	{
		$this->get_request_parameters();
		$result =  $this->get_ordes_tot_num();
		//echo isset($result) && isset($result[0]) ? $result[0]:0;
		echo isset($result) ? $result:0;
		wp_die();
	}
	private function get_order_list($search_string = null, $offset = null, $resultCount  = null)
	{
		global $wpdb;
		$statuses = $this->get_order_statuses();
		$statuses_names = $this->get_order_statuses(false);
		$limit_query = isset($offset) && isset($resultCount) ? " LIMIT {$resultCount} OFFSET {$offset}": "";
		$additional_select = $additional_join = $additional_where = "";
		if($search_string)
		{
			$offset = null;
			$limit_query = "";
		}
		$additional_join = " LEFT JOIN {$wpdb->postmeta} AS billing_name_meta  ON billing_name_meta.post_id = orders.ID 
							 LEFT JOIN {$wpdb->postmeta} AS billing_last_name_meta  ON billing_last_name_meta.post_id = orders.ID
							 LEFT JOIN {$wpdb->postmeta} AS billing_email_meta  ON billing_email_meta.post_id = orders.ID
							 LEFT JOIN {$wpdb->postmeta} AS shipping_name_meta  ON shipping_name_meta.post_id = orders.ID
							 LEFT JOIN {$wpdb->postmeta} AS shipping_last_name_meta  ON shipping_last_name_meta.post_id = orders.ID
							 LEFT JOIN {$wpdb->postmeta} AS customer_id_meta  ON customer_id_meta.post_id = orders.ID
							 LEFT JOIN {$wpdb->postmeta} AS order_number_formatted ON order_number_formatted.post_id = orders.ID  AND (order_number_formatted.meta_key = '_order_number_formatted')
							 LEFT JOIN {$wpdb->postmeta} AS order_number ON order_number.post_id = orders.ID AND (order_number.meta_key = '_order_number')
							";
		$additional_where = " AND billing_name_meta.meta_key = '_billing_first_name' 
							  AND billing_last_name_meta.meta_key = '_billing_last_name' 
							  AND billing_email_meta.meta_key = '_billing_email' 
							  AND shipping_name_meta.meta_key = '_shipping_first_name' 
							  AND shipping_last_name_meta.meta_key = '_shipping_last_name' 
							  AND customer_id_meta.meta_key = '_customer_user' 
		";
		
		 $query_string = "SELECT orders.ID as order_id, orders.post_date as order_date, orders.post_status as order_status, order_number_formatted.meta_value as order_number_formatted, order_number.meta_value as order_number
							 FROM {$wpdb->posts} AS orders {$additional_join}
							 WHERE orders.post_status IN ('".implode("','", $statuses['statuses'])."') 
							 AND orders.post_type = 'shop_order' {$additional_where} ";
		if($search_string)
				$query_string .=  " AND ( orders.ID LIKE '%{$search_string}%' OR  
										  orders.post_date LIKE '%{$search_string}%' OR 
										  orders.post_status LIKE '%{$search_string}%' OR
										  billing_name_meta.meta_value LIKE '%{$search_string}%' OR 
										  billing_last_name_meta.meta_value LIKE '%{$search_string}%' OR 
										  billing_email_meta.meta_value LIKE '%{$search_string}%' OR 
										  shipping_name_meta.meta_value LIKE '%{$search_string}%' OR 
										  shipping_last_name_meta.meta_value LIKE '%{$search_string}%' OR 
										  customer_id_meta.meta_value LIKE '%{$search_string}%' OR
										  order_number_formatted.meta_value LIKE '%{$search_string}%' OR
										  order_number.meta_value LIKE '%{$search_string}%' 
										  )";
		
		$query_string .=  " GROUP BY orders.ID ORDER BY orders.post_date DESC ".$limit_query ;
		 $wpdb->query('SET SQL_BIG_SELECTS=1');
		$results = $wpdb->get_results($query_string );
		//wcst_var_dump($query_string);
		//wcst_var_dump($results);
		$bad_char = array('"', "'");
		foreach((array)$results as $key => $result)
		{
			$order = new WC_Order($result->order_id);
			$user = $order->get_customer_id() > 0 ? get_userdata($order->get_customer_id()) : null;
			$results[$key]->billing_name_and_last_name = str_replace($bad_char, "", $order->get_billing_first_name()." ".$order->get_billing_last_name());
			$results[$key]->shipping_name_and_last_name = str_replace($bad_char, "",$order->get_shipping_first_name()." ".$order->get_shipping_last_name());
			//$results[$key]->shipping_address = method_exists($order, 'get_formatted_shipping_address') ? str_replace($bad_char, "",$order->get_formatted_shipping_address()) : "";
			$results[$key]->user_login = isset($user) ? $user->user_login: "Guest";
			$results[$key]->user_id = $order->get_customer_id() ;
			$results[$key]->user_email = /* isset($order->get_billing_email()) ? */ $order->get_billing_email() /* : "N/A" */;
			$results[$key]->order_status = $statuses_names['statuses'][$result->order_status];
		}
		//wcst_var_dump($results);
		
		if(isset($offset) && isset($resultCount))
		{
			$query_string = "SELECT COUNT(*) as tot
							 FROM {$wpdb->posts} AS orders
							 WHERE orders.post_type = 'shop_order' ";
			$num_order = $wpdb->get_col($query_string);
			$num_order = isset($num_order[0]) ? $num_order[0] : 0;
			$endCount = $offset + $resultCount;
			$morePages = $num_order > $endCount;
			$results = array(
				  "results" => $results,
				  "pagination" => array(
					  "more" => $morePages
				  )
			  );
		}
		else
			$results = array(
				  "results" => $results,
				  "pagination" => array(
					  "more" => $false
				  )
			  );
		
		return $results;
	}
	public function get_guest_user_data_from_last_order($user_email)
	{
		global $wpdb;
		$query = "SELECT GROUP_CONCAT(order_meta.meta_key SEPARATOR '-|-' ) AS field_names, GROUP_CONCAT(COALESCE(order_meta.meta_value, ' ') SEPARATOR '-|-') AS field_values, orders.ID
				  FROM {$wpdb->posts} AS orders 
				  INNER JOIN {$wpdb->postmeta} AS order_meta ON orders.ID = order_meta.post_id
				  INNER JOIN {$wpdb->postmeta} AS user_email ON orders.ID = user_email.post_id 
				  WHERE (order_meta.meta_key LIKE '%_billing_%'
						 OR order_meta.meta_key LIKE '%_shipping_%') 
				  AND user_email.meta_value = '{$user_email}' 
				  GROUP BY orders.ID ORDER BY orders.post_date DESC LIMIT 1";
		$wpdb->query('SET group_concat_max_len=500000'); 
		$results = $wpdb->get_results($query, ARRAY_A );
		$user_data = array('customer_info' => new \stdClass(), 'customer_extra_info' => array());
		
		if(isset($results) && !empty($results))
		{
			$results = array_shift($results);
			$results["field_names"] = explode("-|-", $results["field_names"]);
			$results["field_values"] = explode("-|-", $results["field_values"]);
			$temp_results = array();
			foreach($results["field_names"] as $index => $field_name)
				$temp_results[$field_name] = $results["field_values"][$index];
			//wccm_var_dump($temp_results);	
			$user_data['customer_info']->user_email = $temp_results['_billing_email'];
			$user_data['customer_info']->user_registered = "N/A";
			$user_data['customer_extra_info']['billing_first_name'][0] = $temp_results['_billing_first_name'];
			$user_data['customer_extra_info']['billing_last_name'][0] = $temp_results['_billing_last_name'];
			$user_data['customer_extra_info']['billing_company'][0] = $temp_results['_billing_company'];
			$user_data['customer_extra_info']['billing_email'][0] = $temp_results['_billing_email'];
			$user_data['customer_extra_info']['billing_phone'][0] = $temp_results['_billing_phone'];
			$user_data['customer_extra_info']['billing_country'][0] = $temp_results['_billing_country'];
			$user_data['customer_extra_info']['billing_address_1'][0] = $temp_results['_billing_address_1'];
			$user_data['customer_extra_info']['billing_address_2'][0] = $temp_results['_billing_address_2'];
			$user_data['customer_extra_info']['billing_city'][0] = $temp_results['_billing_city'];
			$user_data['customer_extra_info']['billing_state'][0] = $temp_results['_billing_state'];
			$user_data['customer_extra_info']['billing_postcode'][0] = $temp_results['_billing_postcode'];
			$user_data['customer_extra_info']['shipping_first_name'][0] = $temp_results['_shipping_first_name'];
			$user_data['customer_extra_info']['shipping_last_name'][0] = $temp_results['_shipping_last_name'];
			$user_data['customer_extra_info']['shipping_company'][0] = $temp_results['_shipping_company'];
			$user_data['customer_extra_info']['shipping_country'][0] = $temp_results['_shipping_country'];
			$user_data['customer_extra_info']['shipping_address_1'][0] = $temp_results['_shipping_address_1'];
			$user_data['customer_extra_info']['shipping_address_2'][0] = $temp_results['_shipping_address_2'];
			$user_data['customer_extra_info']['shipping_city'][0] = $temp_results['_shipping_city'];
			$user_data['customer_extra_info']['shipping_state'][0] = $temp_results['_shipping_state'];
			$user_data['customer_extra_info']['shipping_postcode'][0] = $temp_results['_shipping_postcode'];
			$user_data['customer_extra_info']['shipping_postcode'][0] = $temp_results['_shipping_postcode'];
			$user_data['customer_extra_info']['order_shipping_tax'][0] = $temp_results['_order_shipping_tax'];
		}
		
		return $user_data;
	}
	public function get_request_parameters()
	{
		$this->start_date = isset($_POST['start_date']) ? $_POST['start_date']." 00:00:01": null;
		$this->end_date = isset($_POST['end_date']) ? $_POST['end_date']." 23:59:59": null;
		$this->min_amount = isset($_POST['min_amount']) ? $_POST['min_amount']: null;
		$this->max_amount = isset($_POST['max_amount']) ? $_POST['max_amount']: null;
		$this->min_amount_total = isset($_POST['min_amount_total']) ? $_POST['min_amount_total']: null;
		$this->max_amount_total = isset($_POST['max_amount_total']) ? $_POST['max_amount_total']: null;
		$this->statuses = isset($_POST['statuses']) ? explode(",",$_POST['statuses']): null;	
		$this->per_page = isset($_POST['per_page']) ? $_POST['per_page']: null;	
		$this->page_num = isset($_POST['page_num']) ? $_POST['page_num']: null;	
		$this->customer_ids = isset($_POST['customer_ids']) ? explode(",",$_POST['customer_ids']): null;	
		$this->product_ids = isset($_POST['product_ids']) ? explode(",",$_POST['product_ids']): null;	
		$this->category_ids = isset($_POST['category_ids']) ? explode(",",$_POST['category_ids']): null;	
		$this->product_relationship = isset($_POST['product_relationship']) ? $_POST['product_relationship']: null;	
		$this->product_category_relationship = isset($_POST['product_category_relationship']) ? $_POST['product_category_relationship']: null;	
		$this->product_category_filters_relationship = isset($_POST['product_category_filters_relationship']) ? $_POST['product_category_filters_relationship']: null;	
	}
	public function get_max_order_total_sale()
	{
		global $wpdb;
		$query_string = "  SELECT meta.meta_value as total_sale
								FROM {$wpdb->posts} AS posts								
								INNER JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
								
								WHERE 	meta.meta_key 		= '_order_total'
								AND 	posts.post_type 	= 'shop_order'
								ORDER BY meta.meta_value DESC	
									
								";
		//SQL MAX and ORDER BY total_sale have some problem.
		//var_dump(max($wpdb->get_col($query_string)));
		$result = $wpdb->get_col($query_string);
		return is_array($result) && count($result) > 0 ? max($result) : 0;	
	}
	public function get_user_total_sale($customer_email = null, $user_id = null)
	{
		 global $wpdb;
		 $customer_condition = "";
		 $meta_key = "_billing_email";
		 if($user_id )
		 {
			  $customer_condition = " AND ordermeta_customer.meta_value = '".$user_id."' ";
			  $meta_key = "_customer_user";
		 }
		 elseif($customer_email)
		 {
			 $customer_condition = " AND ordermeta_customer.meta_value = '".$customer_email."' ";
		 }
		
		//$query_addons = $this->get_orders_query_conditions_to_exclude_bad_orders();
		$query_string = "  SELECT DISTINCT GROUP_CONCAT(posts.ID), SUM(meta.meta_value) AS total_sales, ordermeta_customer.meta_value as customer_email
								FROM {$wpdb->posts} AS posts								
								INNER JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id ".//$query_addons['join'].
							  " INNER JOIN {$wpdb->postmeta} AS ordermeta_customer ON ordermeta_customer.post_id = posts.ID 
								WHERE 	meta.meta_key 		= '_order_total'
								AND 	posts.post_type 	= 'shop_order' ".$customer_condition.
							 "  AND     ordermeta_customer.meta_key = '{$meta_key}'".
							//"	AND ordermeta_customer.meta_value > 1 ".
							"	GROUP BY ordermeta_customer.meta_value	";
		  $wpdb->query('SET group_concat_max_len=500000'); 
		 $result = $wpdb->get_col($query_string, 1);
		 $result = is_array($result) && count($result) > 0 ? max($result) : 0;
		 return $result;	
	}
	/* public function get_order_statuses()
	{
		
		$result = array();
		$result['statuses'] = array();
		if(function_exists( 'wc_get_order_statuses' ))
		{
			
			$result['version'] = 2.2;
			//[slug] => name
			$temp  = wc_get_order_statuses();
			foreach($temp as $slug => $title)
					array_push($result['statuses'], $slug);
		}
		else
		{
			$args = array(
				'hide_empty'   => false, 
				'fields'            => 'id=>slug', 
			);
			$result['version'] = 2.1;
			
			$temp = get_terms('shop_order_status', $args);
			foreach($temp as $id => $slug)
					array_push($result['statuses'], $slug);
		}
		return $result;
	} */
	public function get_order_statuses($get_codes = true)
	{
		$result = array('version'=>0, 'statuses'=>array());
		if(function_exists( 'wc_get_order_statuses' ))
		{
			
			$result['version'] = 2.2;
			//[slug] => name
			if(!$get_codes)
				$result['statuses'] = wc_get_order_statuses();
			else foreach(wc_get_order_statuses() as $code => $name)
				$result['statuses'][] = $code;
		}
		else
		{
			$args = array(
				'hide_empty'   => false, 
				'fields'            => 'id=>name', 
			);
			$result['version'] = 2.1;
			//[id] => name
			$result['statuses'] =  get_terms('shop_order_status', $args);
		}
		return $result;
	}
	public function get_order_statuses_id_to_name()
	{
		$result = array();
		if(function_exists( 'wc_get_order_statuses' ))
		{
			
			$result['version'] = 2.2;
			//[slug] => name
			$result['statuses'] = wc_get_order_statuses();
		}
		else
		{
			$args = array(
				'hide_empty'   => false, 
				'fields'            => 'id=>name', 
			);
			$result['version'] = 2.1;
			//[id] => name
			$result['statuses'] =  get_terms('shop_order_status', $args);
		}
		return $result;
	}
	
	public function assign_users($order_ids, $user_id, $params = array())
	{
		if(!isset($order_ids) || !is_array($order_ids) || !isset($user_id))
			return;
		
		$billing_fields = array('billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_country', 'billing_email', 'billing_phone');
		$shipping_fields = array('shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode', 'shipping_country');
		foreach($order_ids as $order_id)
		{
			$order = wc_get_order($order_id);
			$order->set_customer_id($user_id);
			$customer = new WC_Customer($user_id);
			if($params['overwrite_billing_data'])
			{
				foreach ( $billing_fields as $key) 
				{
					if ( is_callable( array( $order, "set_{$key}" ) ) && is_callable( array( $customer, "get_{$key}" ) )  ) 
					{
						$method_name = "get_".$key;
						$order->{"set_{$key}"}( $customer->$method_name() );

					// Store custom fields prefixed with wither shipping_ or billing_. This is for backwards compatibility with 2.6.x.
					// TODO: Fix conditional to only include shipping/billing address fields in a smarter way without str(i)pos.
					} /* elseif ( ( 0 === stripos( $key, 'billing_' ) || 0 === stripos( $key, 'shipping_' ) )
						&& ! in_array( $key, array( 'shipping_method', 'shipping_total', 'shipping_tax' ) ) ) {
						$order->update_meta_data( '_' . $key, $value );
					} */
				}
			}
			if($params['overwrite_shipping_data'])
			{
				foreach ( $shipping_fields as $key ) 
				{
					if ( is_callable( array( $order, "set_{$key}" ) ) && is_callable( array( $customer, "get_{$key}" ) ) ) 
					{
						$method_name = "get_".$key;
						$order->{"set_{$key}"}( $customer->$method_name());

					}
				}
			}
			$order->save();
		}
	}
	public function get_orders_query_conditions_to_exclude_bad_orders($join_type = 'INNER')
	{
		global $wpdb;
		$statuses = $this->get_order_statuses();
		$result = array();
		$result['join'] = "";
		$result['where'] = "";
		$result['version'] = $statuses['version'];
		if($statuses['version'] > 2.1)
		{
			$result['statuses'] = $statuses['statuses'] = array_diff($statuses['statuses'], array('wc-cancelled', 'wc-refunded', 'wc-failed'));
			$result['where'] = " AND posts.post_status IN ('".implode( "','",$statuses['statuses'])."') ";
		}
		else 
		{
			$result['statuses'] = $statuses['statuses'] = array_diff($statuses['statuses'], array('cancelled', 'refunded', 'failed'));
			$result['join'] = " {$join_type} JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_id
							  {$join_type} JOIN {$wpdb->term_taxonomy} AS tax ON tax.term_taxonomy_id = rel.term_taxonomy_id
							  {$join_type} JOIN {$wpdb->terms} AS term ON term.term_id = tax.term_id ";
			$result['where'] .= " AND posts.post_status   = 'publish'
								 AND tax.taxonomy        = 'shop_order_status' 
								 AND term.slug           IN ( '" .implode( "','",$statuses['statuses']). "' )";
		}
		
		return $result;
	}
	public function get_status_for_old_orders($join_type = 'INNER')
	{
		global $wpdb;
		if(function_exists( 'wc_get_order_statuses' ))
			return false;
		$result = array();
		$result['select'] = ", term.slug as order_status";
		$result['join'] = " {$join_type} JOIN {$wpdb->term_relationships} AS rel ON posts.ID=rel.object_ID
				  {$join_type} JOIN {$wpdb->term_taxonomy} AS tax USING( term_taxonomy_id ) AND tax.taxonomy = 'shop_order_status'
				  {$join_type} JOIN {$wpdb->terms} AS term USING( term_id ) ";
				  
		return $result;
	}
	public function get_ordes_tot_num( )
	{
		
		return $this->get_customer_ids_from_orders(true);
	}
	public function get_customer_ids_from_orders($select_count = false)
	{
		global $wpdb;
		$result = array('ids'=>array(),'emails'=>array());
		$all_product_ids_from_categories = array();
		$all_product_ids = array();
		$total_sale_range = "";
		$total_sale_user_range = "";
		$date_range = "";
		$status = "";
		$status_joins = "";
		$offset_limit = "";
		$customers_ids_joins = "";
		$customers = "";
		$products_ids_joins = "";
		$products = "";
		$lang_array = null;
		$product_pre_condtions = "";
		if(isset($this->min_amount))
			$total_sale_range .= " AND ordermeta.meta_value >= ".$this->min_amount;
		if(isset($this->max_amount))
			$total_sale_range .= " AND ordermeta.meta_value <= ".$this->max_amount;
		if(isset($this->min_amount_total) && isset($this->max_amount_total))
			$total_sale_user_range .= " HAVING (SUM(ordermeta.meta_value) >= ".$this->min_amount_total." AND SUM(ordermeta.meta_value) <= ".$this->max_amount_total.") ";
		if($this->start_date)
			$date_range .= " AND orders.post_date >= '".$this->start_date."'";
		if($this->end_date)
			$date_range .= " AND orders.post_date <= '".$this->end_date."'";
		if($this->statuses)
		{
			if(function_exists( 'wc_get_order_statuses' )) //2.2 and above
				$status = " AND  orders.post_status IN ('".implode( "','",$this->statuses)."') ";
			else //2.1
			{
				$status_joins = " INNER JOIN {$wpdb->term_relationships} AS rel ON orders.ID=rel.object_ID
								  INNER JOIN {$wpdb->term_taxonomy} AS tax ON tax.term_taxonomy_id = rel.term_taxonomy_id
								  INNER JOIN {$wpdb->terms} AS term ON term.term_id = tax.term_id  ";
				$status = " AND     orders.post_status   = 'publish'
						 AND     tax.taxonomy        = 'shop_order_status'
						 AND     term.term_id           IN ( '" .implode( "','",$this->statuses). "' ) "; 
			}
		}
		if(isset($this->page_num) && isset($this->per_page))
		{
			
			$offset = ($this->page_num-1)*$this->per_page;
			$offset_limit .=  " LIMIT {$offset},{$this->per_page} ";
		}
		if($this->customer_ids)
		{
			$customers_ids_joins = " INNER JOIN {$wpdb->postmeta} AS ordermeta_customer ON ordermeta_customer.post_id = orders.ID  
									INNER JOIN {$wpdb->postmeta} AS ordermeta_customer_email ON ordermeta_customer_email.post_id = orders.ID ";
			$customers= " AND  ordermeta_customer.meta_key = '_customer_user' AND ordermeta_customer.meta_value IN ('" . implode( "','", $this->customer_ids). "' ) 
						   AND  ordermeta_customer_email.meta_key = '_billing_email' ";
		}
		else //GUEST CUSTOMERS
		{
			$customers_ids_joins = " INNER JOIN {$wpdb->postmeta} AS ordermeta_customer ON ordermeta_customer.post_id = orders.ID
									INNER JOIN {$wpdb->postmeta} AS ordermeta_customer_email ON ordermeta_customer_email.post_id = orders.ID  ";
			$customers= " AND  ordermeta_customer.meta_key = '_customer_user' AND ordermeta_customer.meta_value != 1 ". //>1 NO GUEST
						 " AND  ordermeta_customer_email.meta_key = '_billing_email'  ";
		}
		//PRODUCT AND PRODUCT CATEGORIES ---> WPML	
		$all_product_ids_from_categories = array();
		if($this->category_ids)
		{
			$all_category_ids = array();
			foreach($this->category_ids as $category_id)
				$all_category_ids[$category_id] = array($category_id);
			if (class_exists('SitePress')) 
			{
				$languages = icl_get_languages('skip_missing=0&orderby=code');
				$lang_array = array(); 
				if(!empty($languages))
					foreach($languages as $l)
						if(!$l['active']) 
						{
							$all_category_ids[$l['language_code']] = array();
							array_push($lang_array, $l['language_code']);
						}
				foreach($this->category_ids as $category_id)
					foreach($lang_array as $lang)
					{
						$translation = icl_object_id($category_id, 'product_cat', false, $lang);
						if($translation)
							array_push($all_category_ids[$category_id], $translation ); 	
					}
			} 
			//$this->var_debug($all_category_ids);
			foreach($all_category_ids as $category_ids_per_language)
			{
				$get_prdocuts_from_categories = " SELECT DISTINCT GROUP_CONCAT(products.ID) as product_ids
										 FROM {$wpdb->posts} AS products 
										 INNER JOIN {$wpdb->term_relationships} AS term_rel ON term_rel.object_id = products.ID
										 INNER JOIN {$wpdb->term_taxonomy} AS term_tax ON term_tax.term_taxonomy_id = term_rel.term_taxonomy_id 
										 INNER JOIN {$wpdb->terms} AS terms ON terms.term_id = term_tax.term_id
										 WHERE  terms.term_id IN ('" . implode( "','", $category_ids_per_language). "')  
										 AND term_tax.taxonomy = 'product_cat' GROUP BY terms.term_id ";
				$wpdb->query('SET group_concat_max_len=500000'); 
				$additional_products = $wpdb->get_results($get_prdocuts_from_categories);
				//$this->var_debug($additional_products);
				//$additional_products = $wpdb->get_col($get_prdocuts_from_categories, 0);
				//$all_product_ids = isset($additional_products) ? $additional_products:array(-1);
				$additional_products = isset($additional_products) ? $additional_products:array("-1");
				array_push($all_product_ids_from_categories, $additional_products);
			}
			//$this->var_debug($all_product_ids_from_categories);
		}
		if($this->product_ids)
		{
			/*if(!isset($all_product_ids))
				$all_product_ids = $this->product_ids;
			 else
				$all_product_ids = array_merge ($all_product_ids, $this->product_ids); //not used any more */
			foreach($this->product_ids as $product_id)
				$all_product_ids[$product_id] = array($product_id);
			if (class_exists('SitePress')) 
			{
				$lang_array = array(); 
				if(!$lang_array)
				{
					$languages = icl_get_languages('skip_missing=0&orderby=code');
					$lang_array = array();
					if(!empty($languages))
						foreach($languages as $l)
							if(!$l['active']) 
							{
							  //$all_product_ids[$l['language_code']] = array();
							  array_push($lang_array, $l['language_code']);
							}
				}
				foreach($this->product_ids as $product_id)
					foreach($lang_array as $lang)
					{
						$translation  = icl_object_id($product_id, 'product', false, $lang);
						if($translation)
							array_push($all_product_ids[$product_id], $translation); //array with current id and its translation ids
					}							
			} 
		}
		if($this->product_ids || $this->category_ids)
		{
			$products_ids_joins = " INNER JOIN {$wpdb->prefix}woocommerce_order_items AS ordermeta_products ON ordermeta_products.order_id = orders.ID  
								INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS ordermeta_product_meta0 ON ordermeta_product_meta0.order_item_id = ordermeta_products.order_item_id ";
			
			$product_pre_condtions = " AND  (ordermeta_product_meta0.meta_key = '_product_id' OR ordermeta_product_meta0.meta_key = '_variation_id' ) ";
			$products .= " AND (";
		}	
		
		//products
		$counter = $index = 0;
		if($this->product_ids)	
		{
			$products .= " (";
			foreach($all_product_ids as $products_ids_and_translated_ids)
			{
				if($counter != 0)
				{
					if($this->product_relationship == 'and')
					{
					    $products_ids_joins .= " INNER JOIN {$wpdb->prefix}woocommerce_order_items AS ordermeta_products{$counter} ON ordermeta_products{$counter}.order_id = orders.ID  
											     INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS ordermeta_product_meta{$counter} ON ordermeta_product_meta{$counter}.order_item_id = ordermeta_products{$counter}.order_item_id ";
						$product_pre_condtions .= " AND  (ordermeta_product_meta{$counter}.meta_key = '_product_id' OR ordermeta_product_meta{$counter}.meta_key = '_variation_id' ) ";
						/* for($i = 0; $i < $counter; $i++)
							$product_pre_condtions .= " AND ordermeta_product_meta{$counter}.meta_id <> ordermeta_product_meta{$i}.meta_id "; */
						$index = $counter;
					}
					$products .= " {$this->product_relationship} " ;
				}
				$products .= "  ordermeta_product_meta{$index}.meta_value IN ('" .implode( "','", $products_ids_and_translated_ids). "' ) ";
				$counter++;
			}
			$products .= " ) ";
		}
		
		//categories
		$counter2 = 0;
		if(isset($this->category_ids))
		{
			if(isset($this->product_ids))
				$products .= " {$this->product_category_filters_relationship} (";
			
			foreach($all_product_ids_from_categories as $products_per_category_per_lanuage)
			{
				if(!empty($products_per_category_per_lanuage))
				{
					if($counter != 0)
					{
						if($this->product_category_relationship == 'and')
						{
							$products_ids_joins .= " INNER JOIN {$wpdb->prefix}woocommerce_order_items AS ordermeta_products{$counter} ON ordermeta_products{$counter}.order_id = orders.ID  
													 INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS ordermeta_product_meta{$counter} ON ordermeta_product_meta{$counter}.order_item_id = ordermeta_products{$counter}.order_item_id ";
							$product_pre_condtions .= " AND  (ordermeta_product_meta{$counter}.meta_key = '_product_id' OR ordermeta_product_meta{$counter}.meta_key = '_variation_id' ) ";
							$index = $counter;
						}
						
					}
					if($counter2 != 0)
						$products .= " {$this->product_category_relationship} ";
					
					//$products .= " ( ";
					$ids_temp = array();
					foreach($products_per_category_per_lanuage as $products_per_category)
					{
						$ids_temp = array_merge($ids_temp, explode(",", $products_per_category->product_ids));
						
						/* $products_per_category = explode(",", $products_per_category->product_ids);
						if($counter2 != 0)
							$products .= " OR " ;
						$products .= "  ordermeta_product_meta{$index}.meta_value IN ('" . implode( "','", $products_per_category). "' ) "; */
						$counter2++;
					}
					$products .= "  ordermeta_product_meta{$index}.meta_value IN ('" . implode( "','", $ids_temp). "' ) ";
					
					//$counter2 = 0;
					//$products .= " ) ";
					$counter++;
				}
			}
			if(isset($this->product_ids))
				$products .= " ) ";
		}
			
		if($this->product_ids || $this->category_ids)
			$products .= " ) ";
		
		$wpdb->query('SET SQL_BIG_SELECTS=1');
		
		$select_type = "SELECT DISTINCT orders.ID, ordermeta_customer.meta_value as customer_id, ordermeta_customer_email.meta_value as billing_email, SUM(ordermeta.meta_value) as total_spent ";
		if($select_count)
			$select_type = "SELECT COUNT(DISTINCT orders.ID) AS total_orders ";
		$query = $select_type.
				 "FROM {$wpdb->posts} as orders
				  INNER JOIN {$wpdb->postmeta} AS ordermeta ON orders.ID = ordermeta.post_id".$status_joins.$customers_ids_joins.$products_ids_joins."
				  WHERE orders.post_type = 'shop_order' 
				  AND ordermeta.meta_key = '_order_total'
				  AND ordermeta.post_id  = orders.ID ".$total_sale_range.$date_range.$status.$customers.$product_pre_condtions.$products;
		if(!$select_count)
		{
			$query .="GROUP BY ordermeta_customer_email.meta_value ".$offset_limit ; //ordermeta_customer.meta_value
			$order_ids = $wpdb->get_results($query);
			//$total_sale_user_range query doesn't work. filter by code. see in next foreach
			
		  	//$this->var_debug($query);
		}
		else
		{
			$query .= "GROUP BY orders.ID ".$total_sale_user_range;
			//$order_ids = $wpdb->get_col($query, 0);
			$order_ids = count($wpdb->get_results($query));
		}
		//wccm_var_dump($order_ids);
		if($select_count)
			return $order_ids;
		if(isset($order_ids))
			foreach($order_ids as $customer_orders_data)
			{ 
				/* Magic properties inherited from WC_Abstract_Order
				$billing_address_1, $billing_address_2, $billing_city, $billing_company, $billing_country, 
				$billing_email, $billing_first_name, $billing_last_name, $billing_phone, $billing_postcode, 
				$billing_state, 
				
				$shipping_address_1, $shipping_address_2, $shipping_city, $shipping_company, $shipping_country, $shipping_first_name, 
				$shipping_last_name, $shipping_method_title, $shipping_postcode, $shipping_state,
				
				$cart_discount, $cart_discount_tax, 
				
				$customer_ip_address, $customer_user, 
				$customer_user_agent, 
				
				$order_currency, $order_discount, $order_key, $order_shipping, $order_shipping_tax, $order_tax, $order_total, 
				
				$payment_method, $payment_method_title*/
				
				//NOT NECESSARY
			   //$order = new WC_Order($order_id);
			   
			   //$total_sale_user_range query doesn't work. filter by code.
			   /* $this->var_debug($customer_orders_data);
			   $this->var_debug( $this->get_user_total_sale($customer_orders_data->customer_id)); */
			  // $this->var_debug($customer_orders_data);
			  
			 $total_spent = $customer_orders_data->customer_id != 0 ? $this->get_user_total_sale($customer_orders_data->billing_email):$this->get_user_total_sale(null, $customer_orders_data->customer_id); 
			  if((!isset($this->min_amount_total) && !isset($this->max_amount_total)) ||
			    ( (  $total_spent >= $this->min_amount_total ) && (   $total_spent <= $this->max_amount_total ) ))
				{
					array_push($result['emails'], $customer_orders_data->billing_email);
					array_push($result['ids'], $customer_orders_data->customer_id);
				}
				
			}
		return $result; 
	}	
	
	//Moved
	public function get_guest_orders_num($filter_by_product, $filter_by_emails)
	{
		global $wpdb;
		$join = $where = "";
		
		if($filter_by_emails != false)
		{
			$join = " INNER JOIN {$wpdb->prefix}postmeta AS billing_email ON billing_email.post_id = posts.ID	 ";
			$where = " AND billing_email.meta_value IN('".str_replace (",", "','", $filter_by_emails)."') ";
		}
		else if($filter_by_product != false)
		{
			$all_product_id = array();
			
			global $wpdb,$wccm_customer_model;
			$wpml_join = $wpml_where =  "";		
			if (class_exists('SitePress')) 
			{
				$languages = icl_get_languages('skip_missing=0&orderby=code');
				$lang_array = array();
				if(!empty($languages))
					foreach($languages as $l)
						if(!$l['active']) 
						  array_push($lang_array, $l['language_code']);
				
				foreach($lang_array as $lang)
					  array_push($all_product_id, icl_object_id($filter_by_product, 'product', false, $lang)); 		
			}
			array_push($all_product_id, $filter_by_product);
			
			$join = " INNER JOIN {$wpdb->prefix}woocommerce_order_items AS wc_order_items ON wc_order_items.order_id = posts.ID	
					  INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS wc_order_item_meta ON wc_order_item_meta.order_item_id = wc_order_items.order_item_id	";
					  
			$where = "  AND (wc_order_item_meta.meta_key = '_product_id' OR wc_order_item_meta.meta_key = '_variation_id' ) 
						AND wc_order_items.order_item_type IN ( 'line_item' ) 
						AND (wc_order_item_meta.meta_value IN ('".implode("','",$all_product_id)."')) ";
		}
		
		$query_string = "
			 SELECT      COUNT(posts.ID) as total_guest_orders
			 FROM        {$wpdb->posts} AS posts
			 INNER JOIN	 {$wpdb->postmeta} AS postmeta ON postmeta.post_id = posts.ID {$join}
			 WHERE     	 posts.post_type = 'shop_order'
			 AND         postmeta.meta_key = '_customer_user'
			 AND   		 postmeta.meta_value = 0 {$where}
			";  
		return $wpdb->get_row($query_string, OBJECT );
	}
	public function get_guest_users_from_orders($current_page = null, $per_page = null, $get_last_order_id = false, $reverse_order = false, $filter_by_product = false, $filter_by_emails = false)
	{
		global $wpdb;
		$select = "";
		$join = "";
		$where ="";
		$vat_field_name = $this->get_vat_number_field_name();
		if($filter_by_emails != false)
		{
			
			$join = " INNER JOIN {$wpdb->prefix}postmeta AS billing_email ON billing_email.post_id = posts.ID	 ";
			$where = " AND billing_email.meta_value IN('".str_replace (",", "','", $filter_by_emails)."') ";
		}
		elseif($filter_by_product != false)
		{
			$all_product_id = array();	
			$wpml_join = $wpml_where =  "";		
			if (class_exists('SitePress')) 
			{
				$languages = icl_get_languages('skip_missing=0&orderby=code');
				$lang_array = array();
				if(!empty($languages))
					foreach($languages as $l)
						if(!$l['active']) 
						  array_push($lang_array, $l['language_code']);
				
				foreach($lang_array as $lang)
					  array_push($all_product_id, icl_object_id($filter_by_product, 'product', false, $lang)); 		
			}
			array_push($all_product_id, $filter_by_product);
			
			$join = " INNER JOIN   {$wpdb->prefix}woocommerce_order_items AS wc_order_items ON wc_order_items.order_id = posts.ID	
					  INNER JOIN   {$wpdb->prefix}woocommerce_order_itemmeta AS wc_order_item_meta ON wc_order_item_meta.order_item_id = wc_order_items.order_item_id	";
					  
			$where = "  AND (wc_order_item_meta.meta_key = '_product_id' OR wc_order_item_meta.meta_key = '_variation_id' )  
						AND wc_order_items.order_item_type IN ( 'line_item' ) 
						AND (wc_order_item_meta.meta_value IN ('".implode("','",$all_product_id)."')) ";
		}
		
		$query_string = "
		 SELECT       posts.*, GROUP_CONCAT(postmeta2.meta_key SEPARATOR '-|-') as meta_keys, GROUP_CONCAT(COALESCE(postmeta2.meta_value, 'N/A') SEPARATOR '-|-') as meta_values ".$select."
		 FROM         {$wpdb->posts} AS posts
		 INNER JOIN   {$wpdb->postmeta} AS postmeta  ON postmeta.post_id = posts.ID	
		 LEFT JOIN   {$wpdb->postmeta} AS postmeta2 ON postmeta2.post_id = posts.ID ".$join."	
		 WHERE        posts.post_type = 'shop_order'
		 AND          postmeta.meta_key = '_customer_user'
		 AND   		  postmeta.meta_value = 0
		 ".$where." 
	     AND          postmeta2.meta_key IN ('_billing_first_name', '_billing_last_name', '_billing_email', '_billing_phone', '_billing_company', '{$vat_field_name}' ,'_billing_address_1','_billing_address_2','_billing_postcode','_billing_city','_billing_state','_billing_country','_shipping_first_name','_shipping_last_name','_shipping_company','_shipping_address_1','_shipping_address_2','_shipping_postcode','_shipping_city','_shipping_state','_shipping_country', '_order_total') 
         GROUP BY posts.ID";  
		 
		 /* if($reverse_order) */
			$query_string .=" ORDER BY posts.post_date DESC ";
		
		if($current_page && $per_page)
		{
			$offset = ($current_page-1)*$per_page;
			$query_string .=  " LIMIT {$offset},{$per_page} ";
		}	 
			
			//$wpdb->query('SET SQL_BIG_SELECTS=1'); 
			$wpdb->query('SET group_concat_max_len=500000'); 
			return $wpdb->get_results( $query_string, OBJECT );
	}
	public function get_vat_number_field_name()
	{
		global $wcev_order_model;
		return !isset($wcev_order_model) ? '_billing_vat_number' : '_billing_eu_vat';
	}
	public function get_order_details_by_id($order_id)
	{
	 
		 $order = new WC_Order( $order_id );
		$items = $order->get_items();
		return $items;
	}
	public function get_all_user_orders($user_id, $starting_date = null, $ending_date = null, $filter_by_product_id = null)
	{
		if(!isset($user_id))
			return false;
		
		$search_by_email = false; //seek for user orders using his email uses (post_meta.meta_key -> _billing_email)
		if(!is_numeric($user_id))
			$search_by_email = true;
		
		$version_and_statuses = $this->get_order_statuses();
		$tax_query = array();
		$statuses = 'publish';
		$orders=array();//order ids
		if($version_and_statuses['version'] > 2.1)
		{
			$statuses = array_keys($version_and_statuses['statuses']);
		}
		else
		{
			$tax_query = array( array(
								'taxonomy' => 'shop_order_status',
								'field'           => 'slug',
								'terms'         => $version_and_statuses['statuses']
						) );
		}
		
		if(!$search_by_email)
			$args = array(
				'numberposts'     => -1,
				'meta_key'        => '_customer_user',
				'meta_value'      => $user_id,
				'post_type'       => 'shop_order',
				//'post_status'     => 'publish',
				'post_status' => $statuses,
				'tax_query' => $tax_query
			);
		else
			$args = array(
				'numberposts'     => -1,
				'meta_key'        => '_billing_email',
				'meta_value'      => $user_id,
				'post_type'       => 'shop_order',
				'post_status' => $statuses,
				'tax_query' => $tax_query
			);
		if(isset($starting_date) && isset($ending_date) && !empty($starting_date) && !empty($starting_date))
		{
			list($starting_year, $starting_month, $starting_day) = explode ("/", $starting_date, 3);
			list($ending_year, $ending_month, $ending_day) = explode ("/", $ending_date, 3);
			
			$args['date_query']  = array( 'after' => array('year'  => $starting_year,
															'month' => $starting_month,
															'day'   => $starting_day,
															 'hour' => 00,
															 'minute' => 00,
															 'second' => 00
															),
										  'before' => array('year'  => $ending_year,
															'month' => $ending_month,
															'day'   => $ending_day,
															'hour' => 23,
															 'minute' => 59,
															 'second' => 59
															),
										  'inclusive' => true	
 										);
		}
		
		
		$orders=get_posts($args); 	
		if($filter_by_product_id != null)
		{
			$new_orders_list = array();
			foreach($orders as $order)
			{
				$order = new WC_Order( $order->ID );
				$items = $order->get_items();
				foreach($items as $product)
				{
					if( $product['item_meta']['_product_id'][0] == $filter_by_product_id)
						array_push($new_orders_list, $order);
				}
			}
			
			$orders = $new_orders_list;
		} 
		
		return $orders;
	 
	}
	public function get_user_orders_ids($user_id, $starting_date = null, $ending_date = null, $filter_by_product_id = null, $all_types=false)
	{
		global $wpdb;
		$query_string = "SELECT posts.ID 
						 FROM {$wpdb->posts} AS posts
						 LEFT JOIN {$wpdb->postmeta} AS metas ON metas.post_id = posts.ID
						 WHERE post_type = 'shop_order'
						 AND metas.meta_key = '_customer_user'
						 AND metas.meta_value = '{$user_id}'";
						 
		if(isset($starting_date) && isset($ending_date) && !empty($starting_date) && !empty($starting_date))
		{
			$query_string .= " AND posts.post_date >= '{$starting_date}'
						      and posts.post_date <= '{$ending_date}' ";
		}
		
		return $wpdb->get_results($query_string);
	}
	public function get_last_order_date($user_id)
	{
		if(!$user_id)
			return false;
		
		$version_and_statuses = $this->get_order_statuses();
		$tax_query = array();
		$statuses = 'publish';
		$orders=array();//order ids
		if($version_and_statuses['version'] > 2.1)
		{
			$statuses = array_keys($version_and_statuses['statuses']);
		}
		else
		{
			$tax_query = array( array(
								'taxonomy' => 'shop_order_status',
								'field'           => 'slug',
								'terms'         => $version_and_statuses['statuses']
						) );
		}
		$args = array(
			'numberposts'     => 1,
			'meta_key'        => '_customer_user',
			'meta_value'      => $user_id,
			'post_type'       => 'shop_order',
			'post_status' => $statuses,
			'orderby'          => 'date',
			'tax_query' => $tax_query
		);
		
		$orders=get_posts($args); 	
		if($orders && isset($orders[0]))
			return $orders[0]->post_date;
		return __('No orders', 'woocommerce-customers-manager' );
	}
	public function get_first_order_date($user_id)
	{
		if(!$user_id)
			return false;
		
		$version_and_statuses = $this->get_order_statuses();
		$tax_query = array();
		$statuses = 'publish';
		$orders=array();
		if($version_and_statuses['version'] > 2.1)
		{
			$statuses = array_keys($version_and_statuses['statuses']);
		}
		else
		{
			$tax_query = array( array(
								'taxonomy' => 'shop_order_status',
								'field'           => 'slug',
								'terms'         => $version_and_statuses['statuses']
						) );
		}
		$args = array(
			'numberposts'     => 1,
			'meta_key'        => '_customer_user',
			'meta_value'      => $user_id,
			'post_type'       => 'shop_order',
			'orderby'          => 'date',
			'order'            => 'asc',
			'post_status' => $statuses,
			'tax_query' => $tax_query
		);
		$orders=get_posts($args); 	
		if($orders && isset($orders[0]))
			return $orders[0]->post_date;
		return __('No orders', 'woocommerce-customers-manager' );
	}
	public function get_orders_num($user_id,$starting_date = null, $ending_date = null)
	{
		if(!$user_id)
			return false;
		
		$version_and_statuses = $this->get_order_statuses();
		$tax_query = array();
		$statuses = 'publish';
		$orders=array();
		if($version_and_statuses['version'] > 2.1)
		{
			$statuses = array_keys($version_and_statuses['statuses']);
		}
		else
		{
			$tax_query = array( array(
								'taxonomy' => 'shop_order_status',
								'field'           => 'slug',
								'terms'         => $version_and_statuses['statuses']
						) );
		}
		$args = array(
			'numberposts'     => 1,
			'meta_key'        => '_customer_user',
			'meta_value'      => $user_id,
			'post_type'       => 'shop_order',
			'orderby'          => 'date',
			'post_status' => $statuses,
			'tax_query' => $tax_query
		);
		if(isset($starting_date) && isset($ending_date) && !empty($starting_date) && !empty($starting_date))
		{
			list($starting_year, $starting_month, $starting_day) = explode ("/", $starting_date, 3);
			list($ending_year, $ending_month, $ending_day) = explode ("/", $ending_date, 3);
			
			$args['date_query']  = array( 'after' => array('year'  => $starting_year,
															'month' => $starting_month,
															'day'   => $starting_day,
															 'hour' => 00,
															 'minute' => 00,
															 'second' => 00
															),
										  'before' => array('year'  => $ending_year,
															'month' => $ending_month,
															'day'   => $ending_day,
															'hour' => 23,
															 'minute' => 59,
															 'second' => 59
															),
										  'inclusive' => true	
 										);
		}
		$orders=new WP_Query($args); 	
		
		return $orders->post_count;
	}
	//Used only for "Who bought" feature --> NO LONGER USED
	public function get_all_orders_filtered_by_date_and_or_product($product_id = null, $starting_date = null, $ending_date = null, $get_count = false , $per_page = null, $offset = null)
	{
		global $wpdb,$wccm_customer_model, $wccm_product_model;
		$wpml_join = $wpml_where =  "";
		$all_product_id = array();
		$is_variation = $wccm_product_model->is_variation($product_id) != 0 ? true : false;
		//$is_variation = false;
		$post_type_for_query = $is_variation ? '_variation_id' : '_product_id';
		//$post_type_for_query = '_product_id';
		if (class_exists('SitePress') && $product_id) 
		{
			$languages = icl_get_languages('skip_missing=0&orderby=code');
			$lang_array = array();
			if(!empty($languages))
				foreach($languages as $l)
					if(!$l['active']) 
					  array_push($lang_array, $l['language_code']);
			
			foreach($lang_array as $lang)
			{
				
				  //$result = icl_object_id($product_id, !$is_variation ? 'product':'product_variation', false, $lang); 
				  $result = apply_filters( 'wpml_object_id', $product_id, !$is_variation ? 'product':'product_variation', false, $lang);
				  if($result)
					array_push($all_product_id,$result);
			}
		}
		
		if(!$get_count )
			$query_string = " SELECT posts.ID, postmeta.meta_value as user_id, users.user_email, users.user_registered, users.user_pass ";
		else
			$query_string = " SELECT COUNT(DISTINCT postmeta.meta_value) as total ";
			
		$query_string .= "FROM  {$wpdb->posts} AS posts
			 INNER JOIN   {$wpdb->prefix}woocommerce_order_items AS wc_order_items ON wc_order_items.order_id = posts.ID	
			 INNER JOIN   {$wpdb->prefix}woocommerce_order_itemmeta AS wc_order_item_meta ON wc_order_item_meta.order_item_id = wc_order_items.order_item_id	
			 INNER JOIN   {$wpdb->postmeta} AS postmeta  ON postmeta.post_id = posts.ID		
			 INNER JOIN   {$wpdb->users} AS users ON users.ID = postmeta.meta_value
			 INNER JOIN   {$wpdb->usermeta} AS usermeta ON users.ID = usermeta.user_id
			 ".$wpml_join."
   			 WHERE       posts.post_type = 'shop_order'
			 AND         usermeta.meta_key = '{$wpdb->prefix}capabilities'
			     		 {$wccm_customer_model->get_user_role_list_for_sql_query()}
			 AND 		 wc_order_item_meta.meta_key = '{$post_type_for_query}' 
			 AND 		 postmeta.meta_key = '_customer_user' 
			 AND         wc_order_items.order_item_type IN ( 'line_item' ) ";
			// ORDER BY    posts.ID ";
			
			
	
		if(isset($starting_date) && isset($ending_date) && !empty($ending_date) && !empty($starting_date))
		{
			$query_string .= " AND posts.post_date >= '{$starting_date} 00:00:00'
						      AND posts.post_date <= '{$ending_date} 23:59:59' ";
		}
		if($product_id)
		{
			array_push($all_product_id, $product_id);
			
			//del empty ids
			/* if(($key = array_search('', $all_product_id)) !== false) 
				unset($all_product_id[$key]); */

			$query_string .= " AND (wc_order_item_meta.meta_value IN ('".implode("','",$all_product_id)."'))";
		} 
		
		if(!$get_count && $product_id)
			$query_string .=  " GROUP BY user_id";
		elseif(!$get_count)
			$query_string .=  " GROUP BY posts.ID";
		
		if(isset($per_page) && isset($offset) && !$get_count)
		{
			$query_string .=  " LIMIT {$offset},{$per_page} "; 
		}
		
		$wpdb->query('SET SQL_BIG_SELECTS=1');
		if($get_count)
		{
			$result = $wpdb->get_col( $query_string ); 
			//wccm_var_dump($result);
			return $result[0]; 
		}
		//wccm_var_dump($query_string);
		$line_items = $wpdb->get_results( $query_string ); 	
		/* wccm_var_dump($line_items);
		wccm_var_dump($query_string); */
		return $line_items;
	}
}	
?>