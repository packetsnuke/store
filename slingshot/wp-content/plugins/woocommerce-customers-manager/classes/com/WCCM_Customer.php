<?php
class WCCM_Customer
{
 public function __construct()
 {
	 if(is_admin())
		{
			add_action('wp_ajax_wccm_get_customers_list', array(&$this, 'ajax_get_customer_partial_list'));
			add_action('wp_ajax_wccm_customer_assign_roles', array(&$this, 'ajax_assign_new_roles'));
			add_action('wp_ajax_wccm_delete_user_meta', array(&$this, 'ajax_delete_user_meta'));
			add_action('wp_ajax_wccm_update_user_meta', array(&$this, 'ajax_update_user_meta'));
			add_action('wp_ajax_wccm_add_new_user_meta', array(&$this, 'ajax_add_new_user_meta'));
		}
		
	
	add_action('wp_loaded', array(&$this, 'init'));
 }
 public function init()
 {
	 $this->add_blocked_role();
 }
 public function get_blocked_role_name()
 {
	 return __( 'Blocked Customer', 'woocommerce-customers-manager');
 }
 private function add_blocked_role()
 {
	global $wp_roles;
	$exist = is_array($wp_roles->roles) && array_key_exists('blocked_customer', $wp_roles->roles);
     
	$result = !$exist ? add_role(
			'blocked_customer', 
			$this->get_blocked_role_name()
		) : true;
 }
 public function is_blocked_customer($customer_id)
 {
	if($customer_id == 0)
		return false;
	
	$customer_info = $this->get_user_data($customer_id);
	return !empty( $customer_info->roles ) && is_array( $customer_info->roles ) && in_array('blocked_customer', $customer_info->roles);
 }
 public function ajax_delete_user_meta()
 {
	 if(isset($_POST['meta_id']) && isset($_POST['user_id']))
	 {
		 $customer = new WC_Customer($_POST['user_id']);
		 $customer->delete_meta_data_by_mid($_POST['meta_id']);
		 $customer->save();
	 }
	 wp_die();
 }
 public function ajax_update_user_meta()
 {
	 if(isset($_POST['meta_id']) && isset($_POST['value']) && isset($_POST['user_id']) && isset($_POST['key']))
	 {
		 $customer = new WC_Customer($_POST['user_id']);
		 $customer->update_meta_data($_POST['key'], $_POST['value'], $_POST['meta_id']);
		 $customer->save();
	 }
	 wp_die();
 }
 public function ajax_add_new_user_meta()
 {
	 if(isset($_POST['update-policy']) && isset($_POST['value']) && isset($_POST['user_id']) && isset($_POST['key']))
	 {
		 $customer = new WC_Customer($_POST['user_id']);
		 $customer->add_meta_data( $_POST['key'], $_POST['value'], $_POST['update-policy'] == 'update' );
		 $customer->save();
	 }
	
	 wp_die();
 }
 public function ajax_get_customer_partial_list()
 {
	 $customers = $this->get_customer_list($_GET['customer']);
	 echo json_encode( $customers);
	 wp_die();
 }
 public function get_user_notes($user_id)
 {
	 $customer = new WC_Customer($user_id);
	return $customer->get_meta('wccm_customer_notes');
 }
 public function get_user_data($user_id)
 {
	 return get_userdata($user_id);
 }
 public function get_user_meta($user_id, $meta_key, $single = true)
 {
	 return get_user_meta($user_id, $meta_key, $single);
 }
 public function get_all_user_meta($user_id)
 {
	 global $wpdb;
	 $query_string = "SELECT * FROM {$wpdb->usermeta} AS usermeta WHERE usermeta.user_id = {$user_id} ";
	 $wpdb->query('SET SQL_BIG_SELECTS=1');
	$result =  $wpdb->get_results($query_string );
	return isset($result) && is_array($result) ? $result : array();
 }
 public function get_user_roles($user_id)
 {
	$customer_info = $this->get_user_data($user_id);
	return !empty( $customer_info->roles ) && is_array( $customer_info->roles ) ? $customer_info->role : array();
 }
 public function register_new_user($data_source)
{
	global $wpdb;
	$billing_email = WCCM_Order::get_billing_email($data_source);
	$user_id = email_exists($billing_email);
	$conversion_result = "";
	if(is_numeric($user_id))
	{
		$user_id = get_user_by( 'email', $billing_email );
		$user = array();
		$user['id'] = $user_id->ID;
		$user['already_exists'] = true;
		$conversion_result =  sprintf (__('Customer for %s email already existed. Customers have been merged!', 'woocommerce-customers-manager'), $billing_email);
		return array('user' => $user , 'result' =>$conversion_result);
	}
	/* do
	{
		$chars_to_remove = array("'", "\"", " ", ":", "\\", "/", "*", "&");
		$login = str_replace($chars_to_remove, "", $data_source->billing_first_name);
		$login .= rand (1,24000);
		$exists = username_exists( $login );
	} while($exists); */
	$login = $billing_email;
	$exists = username_exists( $login );
	while($exists)
	{
		$chars_to_remove = array("'", "\"", " ", ":", "\\", "/", "*", "&");
		$login = str_replace($chars_to_remove, "", $data_source->get_billing_first_name());
		$login .= rand (1,24000);
		$exists = username_exists( $login );
	}
	
	$role = 'customer';
	$password = wp_generate_password( 6, true, true );//$this->random_password(8);
	$email = $billing_email;
	$userdata = array(
					'user_login'  =>  $login,
					'user_pass'   =>  $password,
					'user_email' => $email,
					'first_name' => $data_source->get_billing_first_name(), 
					'last_name' => $data_source->get_billing_last_name(),
					'role' => $role
				);
	$user_id = wp_insert_user($userdata); 
	//wp_set_password( $user[ 'Password' ], $user_id); ;			
		
	//if(isset($data_source->get_billing_first_name() ))
		update_user_meta( $user_id, 'billing_first_name', $data_source->get_billing_first_name()  );
	//if(isset($data_source->billing_last_name()))
		update_user_meta( $user_id, 'billing_last_name', $data_source->get_billing_last_name() );
	//if(isset($email))
		update_user_meta( $user_id, 'billing_email', $email );
	//if(isset($data_source->billing_phone))
		update_user_meta( $user_id, 'billing_phone', $data_source->get_billing_phone() );
	//if(isset($data_source->billing_company))
		update_user_meta( $user_id, 'billing_company', $data_source->get_billing_company() );
	//if(isset($data_source->billing_address_1))
		update_user_meta( $user_id, 'billing_address_1', $data_source->get_billing_address_1() );
	//if(isset($data_source->billing_address_2))
		update_user_meta( $user_id, 'billing_address_2', $data_source->get_billing_address_2() );
	//if(isset($data_source->billing_postcode))
		update_user_meta( $user_id, 'billing_postcode', $data_source->get_billing_postcode() );
	//if(isset($data_source->billing_city))
		update_user_meta( $user_id, 'billing_city', $data_source->get_billing_city() );
	//if(isset($data_source->billing_state))
		update_user_meta( $user_id, 'billing_state', $data_source->get_billing_state() );
	//if(isset($data_source->billing_country))
		update_user_meta( $user_id, 'billing_country', $data_source->get_billing_country() );
	
	//if(isset($data_source->shipping_first_name))	
		update_user_meta( $user_id, 'shipping_first_name', $data_source->get_shipping_first_name() );
	//if(isset($data_source->shipping_last_name))
		update_user_meta( $user_id, 'shipping_last_name', $data_source->get_shipping_last_name() );
	
	//if(isset($data_source->shipping_company))
		update_user_meta( $user_id, 'shipping_company', $data_source->get_shipping_company() );
	//if(isset($data_source->shipping_address_1))
		update_user_meta( $user_id, 'shipping_address_1', $data_source->get_shipping_address_1() );
	//if(isset($data_source->shipping_address_2))
		update_user_meta( $user_id, 'shipping_address_2', $data_source->get_shipping_address_2 ());
	//if(isset($data_source->shipping_postcode))
		update_user_meta( $user_id, 'shipping_postcode', $data_source->get_shipping_postcode() );
	//if(isset($data_source->shipping_city))
		update_user_meta( $user_id, 'shipping_city', $data_source->get_shipping_city() );
	//if(isset($data_source->shipping_state))
		update_user_meta( $user_id, 'shipping_state', $data_source->get_shipping_state() );
	//if(isset($data_source->shipping_country))
		update_user_meta( $user_id, 'shipping_country', $data_source->get_shipping_country());
	
	update_user_meta( $user_id, "{$wpdb->prefix}capabilities", array("customer" => true) );
	//wp_update_user( array ('ID' => $user_id, 'role' => $role ) ) ;
	
	do_action('wcccm_new_user_created_from_guest', $user_id, $data_source);
	
	$user = array();
	$user['id'] = $user_id;
	$user['user']= $login;
	$user['password'] = $password;
	$user['already_exists']= false;
	return array('user' => $user , 'result' =>$conversion_result);
}
public function is_vat_field_enabled()
{
	global $wcev_customer_model;
	return isset($wcev_customer_model);
}
public function get_vat_number_field_name()
{
	global $wcev_customer_model;
	return !isset($wcev_customer_model) ? 'vat_number' : 'billing_eu_vat';
}
public function get_vat_number($user_id)
{
	global $wcev_customer_model;
	if(!isset($wcev_customer_model))
	{
		//WooTheme Vat plugin support
		$vat_number = $this->get_user_meta($user_id, '_vat_number', true);
		$vat_number = $vat_number ? $vat_number : $this->get_user_meta($user_id, 'vat_number', true);
		
		return $vat_number ? $vat_number : false;
	}

	return $wcev_customer_model->get_vat_number($user_id);
}
 public function get_customer_list($search_string = null)
 {
	 global $wpdb;
	 $vat_field_name = $this->get_vat_number_field_name();																															  //usermeta_email.meta_value as email
	 $query_string = "SELECT customers.ID as customer_id, usermeta_name.meta_value as first_name, usermeta_surname.meta_value as last_name, customers.user_email as email
						 FROM {$wpdb->users} AS customers
						 LEFT JOIN {$wpdb->usermeta} AS usermeta ON customers.ID = usermeta.user_id
						 LEFT JOIN {$wpdb->usermeta} AS usermeta_name ON customers.ID = usermeta_name.user_id AND usermeta_name.meta_key = 'first_name'". //billing_first_name  billing_last_name
						 "LEFT JOIN {$wpdb->usermeta} AS usermeta_surname ON customers.ID = usermeta_surname.user_id AND usermeta_surname.meta_key = 'last_name'
						 LEFT JOIN {$wpdb->usermeta} AS usermeta_billing_first ON customers.ID = usermeta_billing_first.user_id AND usermeta_billing_first.meta_key = 'billing_first_name'
						 LEFT JOIN {$wpdb->usermeta} AS usermeta_billing_surname ON customers.ID = usermeta_billing_surname.user_id AND usermeta_billing_surname.meta_key = 'billing_last_name'
						 LEFT JOIN {$wpdb->usermeta} AS shipping_first_name ON customers.ID = shipping_first_name.user_id AND shipping_first_name.meta_key = 'shipping_first_name'
						 LEFT JOIN {$wpdb->usermeta} AS shipping_last_surname ON customers.ID = shipping_last_surname.user_id AND shipping_last_surname.meta_key = 'shipping_last_name'
						 LEFT JOIN {$wpdb->usermeta} AS usermeta_email ON customers.ID = usermeta_email.user_id AND usermeta_email.meta_key = 'billing_email'
						 LEFT JOIN {$wpdb->usermeta} AS usermeta_phone ON customers.ID = usermeta_phone.user_id AND usermeta_phone.meta_key = 'billing_phone'
						 LEFT JOIN {$wpdb->usermeta} AS usermeta_billing_company ON customers.ID = usermeta_billing_company.user_id AND usermeta_billing_company.meta_key = 'billing_company'
						 LEFT JOIN {$wpdb->usermeta} AS billing_eu_vat ON customers.ID = billing_eu_vat.user_id AND billing_eu_vat.meta_key = '{$vat_field_name}'
						 LEFT JOIN {$wpdb->postmeta} AS postmeta ON postmeta.meta_value = customers.ID  AND   postmeta.meta_key = '_customer_user'						 
						 WHERE usermeta.meta_key = '{$wpdb->prefix}capabilities'
						 {$this->get_user_role_list_for_sql_query()} ";
						 /* AND  (usermeta.meta_value = 'a:1:{s:8:\"customer\";b:1;}'
						       OR   usermeta.meta_value = 'a:1:{s:10:\"subscriber\";b:1;}')
						"; */
						
	/* $search_strings = explode(" ",$search_string);
	if(!empty($search_strings))
		foreach($seach_strings as $current_string)
			$query_string .=  " AND (  customers.ID LIKE '%{$current_string}%' OR customers.user_login LIKE '%{$current_string}%' OR customers.user_nicename LIKE '%{$current_string}%' OR customers.display_name LIKE '%{$current_string}%' OR customers.user_email LIKE '%{$current_string}%'  OR usermeta_name.meta_value LIKE '%{$current_string}%' OR  usermeta_surname.meta_value LIKE '%{$current_string}%' OR usermeta_email.meta_value LIKE '%{$current_string}%' OR usermeta_phone.meta_value LIKE '%{$current_string}%')";
	elseif($search_string)
			$query_string .=  " AND ( customers.ID LIKE '%{$search_string}%' OR customers.user_login LIKE '%{$search_string}%' OR customers.user_nicename LIKE '%{$search_string}%' OR customers.display_name LIKE '%{$search_string}%' OR customers.user_email LIKE '%{$search_string}%'  OR usermeta_name.meta_value LIKE '%{$search_string}%' OR  usermeta_surname.meta_value LIKE '%{$search_string}%' OR usermeta_email.meta_value LIKE '%{$search_string}%' OR usermeta_phone.meta_value LIKE '%{$search_string}%')";
	 */
	 $query_string .= $this->get_search_string($search_string, 'customers', true);
	
	$query_string .=  " GROUP BY customers.ID ";
	$wpdb->query('SET SQL_BIG_SELECTS=1');
	return $wpdb->get_results($query_string );
 }
 public function get_user_role_list_for_sql_query()
 {
	 /*(usermeta.meta_value = 'a:1:{s:8:\"customer\";b:1;}'
						OR   usermeta.meta_value = 'a:1:{s:10:\"subscriber\";b:1;}')*/
						 
	/* array(1) {
	  ["subscriber"]=>
	  bool(true)
	} */
	$roles = WCCM_Options::get_option('allowed_roles');
	$roles_relation = WCCM_Options::get_option('roles_relation');
	if(!isset($roles) || empty($roles))
		//return " AND usermeta.meta_value IS NOT NULL  ";
		return " AND usermeta.meta_value NOT LIKE '%administrator%'  ";
	
    $result= "AND (";	
	$counter = 0;
	foreach($roles as $role)
	{
		/* if($counter > 0)
			$result .= " OR ";
		$result .= "usermeta.meta_value = '".serialize(array($role => true))."'";*/
		
		if($counter > 0)
			$result .= " {$roles_relation} ";
		$result .= "usermeta.meta_value LIKE '%".serialize($role).serialize(true)."%'"; 
		$counter++;
	}
	return $result .= ")";
 }
 public function ajax_assign_new_roles()
 {
	 if(isset($_POST['roles_to_assign']) && isset($_POST['user_ids']))
		 $this->bulk_assign_roles($_POST['user_ids'],$_POST['roles_to_assign']);
	 wp_die();
 }
 public function bulk_assign_roles($user_ids, $roles_to_assign)
 {
	  global $wpdb;
	 $user_ids = explode(",",$user_ids);
	 $roles_to_assign = explode(",",$roles_to_assign);
	 
	 foreach($user_ids as $user_id)
	 {
		$role_to_save = array();
		foreach($roles_to_assign as $role)
		{
			$role_to_save[$role] = true;
		}
		$this->update_user_meta($user_id, $wpdb->prefix.'capabilities', $role_to_save);
	 }
 }
 public function bulk_switch_roles($roles_array)
 {
	 global $wpdb;
	 /* format:
		array(2) {
		  ["from"]=>
		  string(13) "administrator"
		  ["to"]=>
		  string(11) "contributor"
		}
	  */
	  $query = "UPDATE {$wpdb->usermeta}
				SET meta_value='".serialize(array($roles_array['to'] => true))."'
				WHERE meta_value='".serialize(array($roles_array['from'] => true))."' ";
				
		$result = $wpdb->query($query);
		return $result ;
  }
  public function change_user_role($user_id, $roles)
  {
	    global $wpdb;
		if(isset($roles) && !empty($roles))
		{
			$role_to_save = array();
			foreach($roles as $role)
			{
				$role_to_save[$role] = true;
			}
			return $this->update_user_meta($user_id, $wpdb->prefix.'capabilities', $role_to_save/* serialize($role_to_save) */);
		}
		return false ;
  }
  
  //Moved
  public function get_user_total_spent($starting_date = null, $ending_date = null, $orders = array(),  $currency_symbol = true)
	{
		global $wpdb,$woocommerce,$wccm_order_model;
		$orders_ids = array();
		foreach($orders as $order)
		{
			if(is_object($order))
				array_push($orders_ids, isset($order->ID) ? $order->ID:$order->id);
			else
				array_push($orders_ids,$order);
			
			
		}
		
		$query_addons = $wccm_order_model->get_orders_query_conditions_to_exclude_bad_orders();
		$query_string = "  SELECT SUM(meta.meta_value) AS total_sales, COUNT(posts.ID) AS total_orders 
								FROM {$wpdb->posts} AS posts								
								LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id ".$query_addons['join'].
								
								"WHERE 	meta.meta_key 		= '_order_total'
								AND 	posts.post_type 	= 'shop_order'
								AND 	posts.ID 		    IN ('" . implode( "','",$orders_ids)."') "
								.$query_addons['where']; //order status
		
		 $order_totals = $wpdb->get_row( $query_string ) ;
		if(!$currency_symbol)
			return round($order_totals->total_sales, 1);
		
		return get_woocommerce_currency_symbol().round($order_totals->total_sales, 1);
	}
	
	public function get_customers_num($ids = null)
	{
		global $wpdb;
		$query_string = "SELECT COUNT(users.ID) as total_customers
						FROM {$wpdb->users} AS users
						LEFT JOIN {$wpdb->usermeta} AS usermeta ON users.ID = usermeta.user_id
						WHERE usermeta.meta_key = '{$wpdb->prefix}capabilities'
						    {$this->get_user_role_list_for_sql_query()}";
		 
		 if(isset($ids))
			 $query_string .= " AND users.ID IN ('" . implode( "','",$ids)."') ";
		 
		 return $wpdb->get_row($query_string, OBJECT );		
	}
	
	
	public function get_all_guest_customers($current_page = null, $per_page = null, $get_last_order_id = false, $reverse_order = false, $filter_by_product = false, $filter_by_emails = false)
	{
		global $wpdb, $wccm_order_model;
		$customers = array();
		$old_order = $wccm_order_model->get_status_for_old_orders();
		$statuses = $wccm_order_model->get_orders_query_conditions_to_exclude_bad_orders();	
		
		$orders = $wccm_order_model->get_guest_users_from_orders($current_page, $per_page, $get_last_order_id , $reverse_order , $filter_by_product, $filter_by_emails);
    	//wccm_var_dump($orders);
			
			 /*Available fields: _order_key,
								_order_currency,
								_prices_include_tax,
								_customer_ip_address,
								_customer_user_agent,
								_customer_user,
								_created_via,
								_order_version,
								_order_shipping,
								_billing_country,
								_billing_first_name,
								_billing_last_name,
								_billing_company,
								_billing_address_1,
								_billing_address_2,
								_billing_city,
								_billing_state,
								_billing_postcode,
								_billing_email,
								_billing_phone,
								_shipping_country,
								_shipping_first_name,
								_shipping_last_name,
								_shipping_company,
								_shipping_address_1,
								_shipping_address_2,
								_shipping_city,
								_shipping_state,
								_shipping_postcode,
								_payment_method,
								_payment_method_title,
								_cart_discount,
								_cart_discount_tax,
								_order_tax,
								_order_shipping_tax,
								_order_total,
								_download_permissions_granted,
								_recorded_sales,_edit_lock */
			$bad_chars = array( '\'', '"', ',' , ';', '\\', "\t" ,"\n" , "\r");
			foreach($orders as $order)
			{
				//wccm_var_dump($order);
				$order_data = @array_combine (explode('-|-',$order->meta_keys),explode('-|-',$order->meta_values)); 
				//wccm_var_dump($order->ID);
				//wccm_var_dump($order_data);
				if($order_data)
				{
				    
					$user = $order_data['_billing_email'];
					if($user == "")
						continue;
					
					if(!isset($customers[$user]))
					{
						$billing_vat = str_replace($bad_chars, '', isset($order_data[$wccm_order_model->get_vat_number_field_name()]) ? $wccm_order_model->get_vat_number_field_name() : "N/A");
						$customers[$user] = array();				
						//$customers[$user]['user_data'] = $order_data;
						$customers[$user]['ID'] = "Guest user"; //0
						$customers[$user]['hash'] = "N/A";
						$customers[$user]['name'] = str_replace($bad_chars, '',$order_data['_billing_first_name']);
						$customers[$user]['surname'] = str_replace($bad_chars, '',$order_data['_billing_last_name']);
						$customers[$user]['roles_to_export'] = "N/A";
						$customers[$user]['login'] = "N/A";
						$customers[$user]['email'] =  $order_data['_billing_email'];
						$customers[$user]['notes'] = "N/A";
						$customers[$user]['registered'] =  "N/A";
						$customers[$user]['first_order_date'] = $order->post_date;
						$customers[$user]['last_order_date'] = $order->post_date; //10
						$customers[$user]['orders'] = 0;					
						$customers[$user]['total_spent'] = 0; //12
						$customers[$user]['billing_name'] =  str_replace($bad_chars, '',$order_data['_billing_first_name']); 
						$customers[$user]['billing_surname'] = str_replace($bad_chars, '', $order_data['_billing_last_name']);
						$customers[$user]['billing_email'] =  str_replace($bad_chars, '',$order_data['_billing_email']);
						$customers[$user]['billing_phone'] =  $order_data['_billing_phone']; //16
						$customers[$user]['billing_company'] =  str_replace($bad_chars, '',$order_data['_billing_company']);
						$customers[$user]['vat_number'] =  $billing_vat;
						$customers[$user]['billing_address_1'] =  str_replace($bad_chars, '',$order_data['_billing_address_1']);
						$customers[$user]['billing_address_2'] = str_replace($bad_chars, '', $order_data['_billing_address_2']);
						$customers[$user]['billing_postcode'] =  $order_data['_billing_postcode'];
						$customers[$user]['billing_city'] =  str_replace($bad_chars, '',$order_data['_billing_city']);
						$customers[$user]['billing_state'] =  str_replace($bad_chars, '',$order_data['_billing_state']);
						$customers[$user]['billing_country'] =  str_replace($bad_chars, '',$order_data['_billing_country']);
						$customers[$user]['shipping_name'] =  str_replace($bad_chars, '',$order_data['_shipping_first_name']); //25
						$customers[$user]['shipping_surname'] = str_replace($bad_chars, '', $order_data['_shipping_last_name']);
						$customers[$user]['shipping_company'] =  str_replace($bad_chars, '',$order_data['_shipping_company']);
						$customers[$user]['shipping_address_1'] =  str_replace($bad_chars, '',$order_data['_shipping_address_1']);
						$customers[$user]['shipping_address_2'] =  str_replace($bad_chars, '',$order_data['_shipping_address_2']);
						$customers[$user]['shipping_postcode'] =  $order_data['_shipping_postcode'];
						$customers[$user]['shipping_city'] =  str_replace($bad_chars, '',$order_data['_shipping_city']);
						$customers[$user]['shipping_state'] =  str_replace($bad_chars, '',$order_data['_shipping_state']);
						$customers[$user]['shipping_country'] =  str_replace($bad_chars, '',$order_data['_shipping_country']);
						if($get_last_order_id)
							$customers[$user]['last_order_id'] =  $order->ID;//34
						
					}
					
					if($order->post_date < $customers[$user]['first_order_date'])
						$customers[$user]['first_order_date'] = $order->post_date ;
					if($order->post_date > $customers[$user]['last_order_date'])
						$customers[$user]['last_order_date'] = $order->post_date ; //36
					
					//wccm_var_dump($order->post_status);
					if(($old_order === false && in_array ($order->post_status, $statuses['statuses'])) || 
					   (is_array($old_order) && in_array ($order->order_status, $statuses['statuses'])))
					{
						$customers[$user]['total_spent'] += isset($order_data ['_order_total']) && $order_data ['_order_total'] != '' ? $order_data ['_order_total'] : 0;
						$customers[$user]['orders']++; //37
					}
					
					if($get_last_order_id)
							$customers[$user]['last_order_id'] =  $order->ID;
				}
			}
			//wccm_var_dump($customers);
			
			//remove bad order 
			foreach($customers as $index => $customer)
				if($customer['orders'] == 0)
					unset($customers[$index]);
				
			return $customers; 	
	}
	
	//Normal flow
	public function get_all_users_with_orders_ids($current_page, $per_page, $customers_ids_to_use_as_filter = null, $get_total = false, $ids = null, $starting_date = null, $ending_date = null, $filter_by_product_id = null)
	{
		global $wpdb, $wccm_order_model,$wccm_wpml_helper;
		$search_string = null;
		if(isset($_GET['s']))
			$search_string = isset($_GET['s']) ? $_GET['s']:null;
		
		$vat_field_name = $this->get_vat_number_field_name();	
		$customers_ids_joins = "";
		$customers = "";
		$search_additional_join = "";
		$order_total_select = ", SUM(postmeta_for_total_sales.meta_value) AS total_sales ";
		$order_total_join = " LEFT JOIN {$wpdb->postmeta} AS postmeta ON postmeta.meta_value = users.ID  AND   postmeta.meta_key = '_customer_user'
							  LEFT JOIN {$wpdb->posts} as posts ON posts.ID = postmeta.post_id   AND   posts.post_type 	= 'shop_order'  
		                      LEFT JOIN {$wpdb->postmeta} AS postmeta_for_total_sales ON posts.ID = postmeta_for_total_sales.post_id AND postmeta_for_total_sales.meta_key = '_order_total' "; //billing_first_name billing_last_name
		$search_additional_join_temp = " LEFT JOIN {$wpdb->usermeta} AS usermeta_name ON users.ID = usermeta_name.user_id AND usermeta_name.meta_key = 'first_name'
										 LEFT JOIN {$wpdb->usermeta} AS usermeta_surname ON users.ID = usermeta_surname.user_id AND usermeta_surname.meta_key = 'last_name'
										 LEFT JOIN {$wpdb->usermeta} AS usermeta_billing_first ON users.ID = usermeta_billing_first.user_id AND usermeta_billing_first.meta_key = 'billing_first_name'
										 LEFT JOIN {$wpdb->usermeta} AS usermeta_billing_surname ON users.ID = usermeta_billing_surname.user_id AND usermeta_billing_surname.meta_key = 'billing_last_name'
										 LEFT JOIN {$wpdb->usermeta} AS usermeta_billing_company ON users.ID = usermeta_billing_company.user_id AND usermeta_billing_company.meta_key = 'billing_company'
										 LEFT JOIN {$wpdb->usermeta} AS billing_eu_vat ON users.ID = billing_eu_vat.user_id AND billing_eu_vat.meta_key = '{$vat_field_name}'
										 LEFT JOIN {$wpdb->usermeta} AS shipping_first_name ON users.ID = shipping_first_name.user_id AND shipping_first_name.meta_key = 'shipping_first_name'
										 LEFT JOIN {$wpdb->usermeta} AS shipping_last_surname ON users.ID = shipping_last_surname.user_id AND shipping_last_surname.meta_key = 'shipping_last_name'
										 LEFT JOIN {$wpdb->usermeta} AS usermeta_phone ON users.ID = usermeta_phone.user_id AND usermeta_phone.meta_key = 'billing_phone'
										 LEFT JOIN {$wpdb->usermeta} AS usermeta_email ON users.ID = usermeta_email.user_id AND usermeta_email.meta_key = 'billing_email' ";
		
		if(isset($_GET['orderby']) && isset($_GET['order']))
		{
			$order =  $_GET['order'];
			$orderby = ($_GET['orderby'] == 'total_spent') ? 'total_sales':$_GET['orderby'] ;
			$orderby = ($_GET['orderby'] == 'orders') ? 'count(DISTINCT posts.ID)':$orderby ;
			$orderby = ($_GET['orderby'] == 'registered') ? 'users.user_registered':$orderby ;
			$orderby = ($_GET['orderby'] == 'ID') ? 'users.ID':$orderby ;
			$orderby = ($_GET['orderby'] == 'name') ? 'usermeta_name.meta_value':$orderby ;
			$orderby = ($_GET['orderby'] == 'surname') ? 'usermeta_surname.meta_value':$orderby ;
			$orderby = ($_GET['orderby'] == 'email') ? 'users.user_email':$orderby ;
			$orderby = ($_GET['orderby'] == 'last_order_date') ? 'users.user_registered' /* 'posts.post_date' */:$orderby ;
			$orderby = ($_GET['orderby'] == 'login') ? 'users.user_login':$orderby ;
			
			$search_additional_join = $search_additional_join_temp;
			
		}
		else
		{
			$orderby = WCCM_Options::get_option('customer_list_default_sorting_column', 'users.user_registered');
			$order =  WCCM_Options::get_option('customer_list_sorting_type', 'desc');
			$orderby = $orderby == 'posts.post_date' ? 'users.user_registered' : $orderby; //forced. Order by posted date is no longer used
			$orderby = $orderby == 'count(posts.ID)' ? 'count(DISTINCT posts.ID)' : $orderby; 
			
			//Doing in this way, the WP_List_Table component will read $_GET variable and automatically set the sort class to column
			switch($orderby)
			{
				case 'total_sales': $_GET['orderby'] = 'total_spent'; break;
				case 'count(DISTINCT posts.ID)':  $_GET['orderby'] = 'orders'; break;
				case 'users.user_registered': $_GET['orderby'] = 'registered'; break;
				case 'users.ID': $_GET['orderby'] = 'ID'; break;
				case 'usermeta_name.meta_value': $_GET['orderby'] = 'name'; break;
				case 'usermeta_surname.meta_value': $_GET['orderby'] = 'surname'; break;
				case 'users.user_email': $_GET['orderby'] = 'email'; break;
				case 'posts.post_date': $_GET['orderby'] = 'users.user_registered' /* 'last_order_date' */;  break;
				case 'users.user_login': $_GET['orderby'] = 'login';  break;
			}
			$_GET['order'] = $order;
			
			if($orderby != 'users.user_registered')
			{
				$search_additional_join = $search_additional_join_temp;
			}
		}
		$offset = ($current_page-1)*$per_page;
		
		
		if($customers_ids_to_use_as_filter) 
		{
			$customers_ids_joins = " INNER JOIN {$wpdb->postmeta} AS ordermeta_customer ON ordermeta_customer.post_id = posts.ID  ";
			$customers = " AND  ordermeta_customer.meta_key = '_customer_user' AND ordermeta_customer.meta_value IN ('" . implode( "','", $customers_ids_to_use_as_filter). "' ) AND ordermeta_customer.meta_value > 1";
			
		}
		if(isset($filter_by_product_id)) //Who bought
		{
			$all_filer_product_ids = array($filter_by_product_id);
			$translated_product_ids = $wccm_wpml_helper->get_all_product_id_translations($filter_by_product_id);
			 if(!empty($translated_product_ids))
				$all_filer_product_ids = array_merge($all_filer_product_ids,$translated_product_ids);
			
			
			$customers .= " AND (wc_order_item_meta.meta_value IN ('".implode("','",$all_filer_product_ids)."'))";
			$order_total_join .=   " INNER JOIN   {$wpdb->prefix}woocommerce_order_items AS wc_order_items ON wc_order_items.order_id = posts.ID	
								     INNER JOIN   {$wpdb->prefix}woocommerce_order_itemmeta AS wc_order_item_meta ON wc_order_item_meta.order_item_id = wc_order_items.order_item_id ";
		}
		$orders_statuses = $wccm_order_model->get_orders_query_conditions_to_exclude_bad_orders();
		
		//Get users id to process
		if(isset($search_string))
			$search_additional_join = $search_additional_join_temp;
		if(!$get_total)
		{
			$ids = array();
			$user_ids_query =  " SELECT users.ID  ";
			if($orderby == 'total_sales' || $orderby == 'count(DISTINCT posts.ID)' || isset($customers_ids_to_use_as_filter))
				$user_ids_query .= $order_total_select;
			$user_ids_query .=	"FROM {$wpdb->users} AS users ";
			
			if($orderby == 'total_sales' || isset($filter_by_product_id) || $orderby == 'count(DISTINCT posts.ID)' || isset($customers_ids_to_use_as_filter) ||
				(isset($starting_date) && isset($ending_date) && !empty($ending_date) && !empty($starting_date)))
			{
				$user_ids_query .= $order_total_join;
				if(isset($starting_date) && isset($ending_date) && !empty($ending_date) && !empty($starting_date))
				{
					$customers .= " AND posts.post_date >= '{$starting_date} 00:00:00'
									AND posts.post_date <= '{$ending_date} 23:59:59' ";
				}
			}
			
			$user_ids_query .= $customers_ids_joins;
			$user_ids_query .= " LEFT JOIN {$wpdb->usermeta} AS usermeta ON users.ID = usermeta.user_id ".$search_additional_join. "
						WHERE usermeta.meta_key = '{$wpdb->prefix}capabilities' ".$customers." ".$this->get_user_role_list_for_sql_query();
			$user_ids_query.= $this->get_search_string($search_string, 'users', isset($search_string));
			$user_ids_query.= "GROUP BY users.ID ORDER BY {$orderby} {$order} LIMIT {$offset},{$per_page} "; 
			
			$wpdb->query('SET SQL_BIG_SELECTS=1');
			$wpdb->query('SET group_concat_max_len=500000'); 
			$user_ids_to_process = $wpdb->get_results($user_ids_query, OBJECT_K );
			
			if(isset($user_ids_to_process))
				foreach((array)$user_ids_to_process as $ids_to_process)
					$ids[] = $ids_to_process->ID;
		}
		else
		{
			$user_ids_query =  " SELECT COUNT(DISTINCT users.id) as total ";
					if($orderby == 'total_sales' || $orderby == 'count(DISTINCT posts.ID)' || $customers_ids_to_use_as_filter )
				$user_ids_query .= $order_total_select;
			$user_ids_query .=	"FROM {$wpdb->users} AS users ";
			if($orderby == 'total_sales' || isset($filter_by_product_id) || $orderby == 'count(DISTINCT posts.ID)' || $customers_ids_to_use_as_filter ||
			   (isset($starting_date) && isset($ending_date) && !empty($ending_date) && !empty($starting_date)) )
			{
				$user_ids_query .= $order_total_join;
				if(isset($starting_date) && isset($ending_date) && !empty($ending_date) && !empty($starting_date))
				{
					$customers .= " AND posts.post_date >= '{$starting_date} 00:00:00'
									AND posts.post_date <= '{$ending_date} 23:59:59' ";
				}
			}
			$user_ids_query .= $customers_ids_joins;
			$user_ids_query .= " LEFT JOIN {$wpdb->usermeta} AS usermeta ON users.ID = usermeta.user_id ".$search_additional_join. "
						WHERE usermeta.meta_key = '{$wpdb->prefix}capabilities' ".$customers." ".$this->get_user_role_list_for_sql_query(); 
						
			 $user_ids_query.= $this->get_search_string($search_string, 'users', isset($search_string));
			 
			 $wpdb->query('SET SQL_BIG_SELECTS=1');
			 $wpdb->query('SET group_concat_max_len=500000'); 
			 $result = $wpdb->get_col( $user_ids_query ); 
			 return  $result[0];
		}
		
		if($orders_statuses['version'] > 2.1)
		{
			if(!$get_total)
				$query_string = "SELECT users.*, GROUP_CONCAT(DISTINCT posts.ID) as order_ids, posts.post_date, CAST(count(*) AS SIGNED) AS num_orders ".$order_total_select;
			else
				$query_string = "SELECT COUNT(DISTINCT users.id) as total ";
				$query_string .= "FROM {$wpdb->users} AS users
						 LEFT JOIN {$wpdb->usermeta} AS usermeta ON users.ID = usermeta.user_id ".$search_additional_join.
						 $order_total_join. 
						//Filter by status
						"AND posts.post_status IN ('".implode( "','",$orders_statuses['statuses'])."')".
						 $customers_ids_joins." 
						 WHERE usermeta.meta_key = '{$wpdb->prefix}capabilities' ".$customers." 
						    {$this->get_user_role_list_for_sql_query()} ";
		}
		else //OLD WC VERSIONS NO LONGER USED
		{
			if(!$get_total)
						$query_string = "SELECT MAX_STATEMENT_TIME = 120000 users.*, GROUP_CONCAT(posts.ID) as order_ids, posts.post_date, count(*) as num_orders, SUM(postmeta_for_total_sales.meta_value) AS total_sales ";
			else	
				$query_string = "SELECT COUNT(DISTINCT users.id) as total ";
				
			$query_string .=  "FROM {$wpdb->users} AS users
							 LEFT JOIN {$wpdb->usermeta} AS usermeta ON users.ID = usermeta.user_id
							 LEFT JOIN {$wpdb->usermeta} AS usermeta_name ON users.ID = usermeta_name.user_id AND usermeta_name.meta_key = 'first_name'
							 LEFT JOIN {$wpdb->usermeta} AS usermeta_surname ON users.ID = usermeta_surname.user_id AND usermeta_surname.meta_key = 'last_name'
							 LEFT JOIN {$wpdb->usermeta} AS usermeta_phone ON users.ID = usermeta_phone.user_id AND usermeta_phone.meta_key = 'billing_phone'
							 LEFT JOIN {$wpdb->usermeta} AS usermeta_email ON users.ID = usermeta_email.user_id AND usermeta_email.meta_key = 'billing_email'
							 LEFT JOIN {$wpdb->usermeta} AS usermeta_billing_company ON users.ID = usermeta_billing_company.user_id AND usermeta_billing_company.meta_key = 'billing_company'
							 LEFT JOIN {$wpdb->usermeta} AS billing_eu_vat ON users.ID = billing_eu_vat.user_id AND billing_eu_vat.meta_key = '{$vat_field_name}'
							 LEFT JOIN {$wpdb->postmeta} AS postmeta ON postmeta.meta_value = users.ID  AND   postmeta.meta_key = '_customer_user'
							 LEFT JOIN {$wpdb->posts} as posts ON posts.ID = postmeta.post_id   AND   posts.post_type 	= 'shop_order' AND posts.post_status = 'publish' ".
							//Filter by status
							// "LEFT JOIN {$wpdb->term_relationships} AS rel ON posts.ID = rel.object_id
							// LEFT JOIN {$wpdb->term_taxonomy} AS tax ON tax.term_taxonomy_id = rel.term_taxonomy_id AND tax.taxonomy = 'shop_order_status' 
							// LEFT JOIN {$wpdb->terms} AS term ON term.term_id =tax.term_id AND term.slug IN ( '" .implode( "','",$orders_statuses['statuses']). "' ) "
							 $customers_ids_joins.		
							 " LEFT JOIN {$wpdb->postmeta} AS postmeta_for_total_sales ON posts.ID = postmeta_for_total_sales.post_id AND postmeta_for_total_sales.meta_key = '_order_total'
							 WHERE usermeta.meta_key = '{$wpdb->prefix}capabilities' ".$customers."
								{$this->get_user_role_list_for_sql_query()} ";
								
			
		}
			
		$query_string .= $this->get_search_string($search_string, 'users', isset($search_string));
		
		 if(isset($ids))
			 $query_string .= " AND users.ID IN ('" . implode( "','",$ids)."') ";
		 
		/* if(!$get_total)
			$query_string .=  " GROUP BY users.ID ORDER BY {$orderby} {$order} LIMIT {$offset},{$per_page} ";  */
		if(!$get_total)
			$query_string .=  " GROUP BY users.ID ORDER BY {$orderby} {$order}  "; 
		
		$wpdb->query('SET SQL_BIG_SELECTS=1');
		$wpdb->query('SET group_concat_max_len=500000'); 
		
		//wccm_var_dump($query_string);
        return $wpdb->get_results($query_string, OBJECT_K );
	}
	private function get_search_string($search_string, $user_table_name = 'users', $include_billing_shipping_first_and_last_name = false)
	{
		$query_string = "";
		$search_strings = explode(" ",$search_string);
		if(!empty($search_strings))
			foreach($search_strings as $current_string)
			{
				if($current_string != "")
				{
					$query_string .=  " AND ( {$user_table_name}.ID LIKE '%{$current_string}%' OR {$user_table_name}.user_login LIKE '%{$current_string}%' OR {$user_table_name}.user_nicename LIKE '%{$current_string}%' OR {$user_table_name}.display_name LIKE '%{$current_string}%' OR {$user_table_name}.user_email LIKE '%{$current_string}%' OR usermeta_billing_company.meta_value LIKE '%{$search_string}%' OR usermeta_phone.meta_value LIKE '%{$current_string}%' OR usermeta_name.meta_value LIKE '%{$current_string}%' OR  usermeta_surname.meta_value LIKE '%{$current_string}%' OR usermeta_email.meta_value LIKE '%{$current_string}%' OR billing_eu_vat.meta_value LIKE '%{$current_string}%' ";
					if($include_billing_shipping_first_and_last_name)
						$query_string .= " OR usermeta_billing_company.meta_value LIKE '%{$search_string}%' OR usermeta_billing_first.meta_value LIKE '%{$search_string}%' OR usermeta_billing_surname.meta_value LIKE '%{$search_string}%' OR shipping_first_name.meta_value LIKE '%{$search_string}%' OR shipping_last_surname.meta_value LIKE '%{$search_string}%'";
					$query_string .= " ) ";
				}
			}
		else
		{
			if($search_string)
			{
				$query_string .=  " AND ({$user_table_name}.ID LIKE '%{$search_string}%' OR {$user_table_name}.user_login LIKE '%{$search_string}%' OR {$user_table_name}.user_nicename LIKE '%{$search_string}%' OR {$user_table_name}.display_name LIKE '%{$search_string}%' OR {$user_table_name}.user_email LIKE '%{$search_string}%' usermeta_billing_company LIKE '%{$search_string}%' OR usermeta_phone.meta_value LIKE '%{$search_string}%' OR usermeta_name.meta_value LIKE '%{$search_string}%' OR  usermeta_surname.meta_value LIKE '%{$search_string}%' OR usermeta_email.meta_value LIKE '%{$search_string}%' OR billing_eu_vat.meta_value LIKE '%{$current_string}%' ";
				if($include_billing_shipping_first_and_last_name)
					$query_string .= "OR usermeta_billing_company.meta_value LIKE '%{$search_string}%' OR shipping_first_name.meta_value LIKE '%{$search_string}%' OR usermeta_billing_first.meta_value LIKE '%{$search_string}%' OR usermeta_billing_surname.meta_value LIKE '%{$search_string}%' OR shipping_first_name.meta_value LIKE '%{$search_string}%' OR shipping_last_surname.meta_value LIKE '%{$search_string}%' ";
				$query_string .= " ) ";
			}
		}
		
		return $query_string;
	}
	public function bulk_update_wpuef_fields($user_id, $wpuef_extra_fields)
	{
		if(!empty($wpuef_extra_fields))
			foreach($wpuef_extra_fields as $extra_field)
			{
				//wccm_var_dump($extra_field['id']." ".$extra_field['data']." ".$user_id);
				if(!empty($extra_field['data']))
					$this->set_wpuef_field_content($extra_field['id'], $extra_field['data'], $user_id);
			}  
	}
	public function has_customer_extra_wpuef_fields()
	{
		global $wpuef_option_model;
		return isset($wpuef_option_model);
	}
	public function get_wpuef_field_content($user_id, $field_id)
	{
		global $wpuef_shortcodes;
		$result = "";
		$bad_chars = array( '\'', '"', /* ',' , */ ';', '\\', "\t" ,"\n" , "\r");
		if(isset($wpuef_shortcodes))
		{
			$result = $wpuef_shortcodes->wpuef_show_field_value(array('field_id'=>$field_id, 'user_id'=>$user_id));
			$result = str_replace($bad_chars, "", $result);
		}
		//return '\"'.$result.'\"';
		return $result;
	}
	public function set_wpuef_field_content($field_id, $value, $user_id )
	{
		global $wpuef_shortcodes, $wccm_wpml_helper;
		$result = "";
		$country_helper = new WCCM_Country();
		
		if(function_exists('wpuef_set_field'))
		{
			$result = wpuef_get_field($field_id, $user_id);
			if(isset($result->field_type) && ($result->field_type == "country_and_state"))
			{
				$values = empty(explode(",", $value)) ? array($value) : explode(",", $value);
				$country_and_state = array();
				$country_and_state['country'] = $country_helper->get_country_code_by_name(trim($values[0]));
				$country_and_state['state'] = isset($values[1]) ? $country_helper->get_state_code_by_name($country_and_state['country'], trim($values[1])) : "";
				//wpuef_var_dump($country_and_state);
				wpuef_set_field($field_id, $country_and_state, $user_id);
			}				
			else if(isset($result->field_type) && ($result->field_type == "dropdown" || $result->field_type == "checkboxes" || $result->field_type == "radio")) 
			{
				//wccm_var_dump($result->field_type);
				$values_to_import = empty(explode(",", $value)) ? array($value) : explode(",", $value);
				//wccm_var_dump($values_to_import);
				
				if(isset($result->field_options->options))
				{
					$temp_value_to_import = array();
					foreach($result->field_options->options as $option_value => $option) 
					{
					   $all_options_translations = $wccm_wpml_helper->wpuef_get_option_translations_by_original_language_string_id($field_id, $option_value, $option->label);
					   foreach($values_to_import as $value_to_import)
					   {
						   //wccm_var_dump($option->label." ".trim($value_to_import)." ".$option_value);
						   if($option->label === trim($value_to_import))
						   {
							  // wccm_var_dump("OK: ".$option->label." ".trim($value_to_import)." ".$option_value);
							  /*  wccm_var_dump($option->label." ".trim($value_to_import) );
							   wccm_var_dump("value to import: ".trim($option_value)); */
							   $temp_value_to_import[$option_value] = $option_value;
						   }
						    else //WPML
							{
								/*  wccm_var_dump("translated value to import: ".$value_to_import);
								wccm_var_dump($all_options_translations);  */
								foreach((array)$all_options_translations as $temp_translation)
										if($temp_translation === trim($value_to_import))
										{
											//wccm_var_dump("importing translation: ".$option_value); 
											$temp_value_to_import[$option_value] = $option_value;
										}
								
							}	 							
								
					   }
					}
					
					if(count($temp_value_to_import) == 1)
						wpuef_set_field($field_id, reset($temp_value_to_import), $user_id);
					elseif(!empty($temp_value_to_import))
						wpuef_set_field($field_id, $temp_value_to_import, $user_id);
				}
				
			}
			else if(isset($result->field_type) && $result->field_type == "date" ) 
			{
				$date = "";
				$date = DateTime::createFromFormat(get_option( 'date_format' ), $value );
				wpuef_set_field($field_id, is_object($date) ? $date->format("Y/m/d") : sprintf(__('Invalid date format. Use the %s format.','woocommerce-customers-manager'), get_option( 'date_format' )), $user_id);
			}
			else if(isset($result->field_type)) //to avoid not defined fields
				wpuef_set_field($field_id, trim($value), $user_id);
		}
	}
	public function get_wpuef_field_names_and_ids($escape_column_titles = false)
	{
		global $wpuef_option_model;
		$result = array();
		$bad_chars = array( '\'', '"', ',' , ';', '\\', "\t" ,"\n" , "\r");
		if(isset($wpuef_option_model))
		{
			$extra_fields = $wpuef_option_model->get_option('json_fields_string');
			if(isset($extra_fields) && isset($extra_fields->fields) && is_array($extra_fields->fields))
				foreach($extra_fields->fields as $extra_field)
				{
					$title = !$escape_column_titles ? $extra_field->label : str_replace($bad_chars, "", $extra_field->label);
					$result[] = array('title' => $title, 'id'=>$extra_field->cid);
				}
		}
		return $result;
	}
	public function get_wpuef_field_names()
	{
		$wpuef_field_names_and_ids = $this->get_wpuef_field_names_and_ids(true);
		$wpuef_column_titles = array();
		//$bad_chars = array( '\'', '"', ',' , ';', '\\', "\t" ,"\n" , "\r");
		
		foreach((array)$wpuef_field_names_and_ids as $wpuef_temp)
			$wpuef_column_titles[] = $wpuef_temp['title']; //str_replace($bad_chars, "", $wpuef_temp['title']);
			
		return $wpuef_column_titles;
	}
	public function update_user_roles($user_id, $roles_temp)
	{
		global $wpdb;
		
		$roles = explode(" ",$roles_temp);
		if($roles == false || empty($roles))
			$roles = array($roles_temp);
		
		$role_to_assign = array();
		foreach($roles as $role)
			$role_to_assign[$role] = true;
			
		if(!empty($role_to_assign))
			$this->update_user_meta( $user_id, "{$wpdb->prefix}capabilities", $role_to_assign );
	}
	public function update_user_meta($user_id, $key, $value, $prev_value = '')
	{
		global $wpdb, $wccm_configuration_model;
		//error_log($wccm_configuration_model->get_options('actions_user_role_change', false) ? 'true' : 'false');
		if($wpdb->prefix.'capabilities' == $key && $wccm_configuration_model->get_options('actions_user_role_change', false))
		{
			$user_info = get_userdata($user_id);
			do_action('set_user_role', $user_id, $value, $user_info->roles );
			/* wccm_var_dump($user_id);
			wccm_var_dump($value);
			wccm_var_dump($user_info->roles); */
			
		}
		update_user_meta( $user_id, $key, $value, $prev_value  );
	}
	public function update_user_metas( $user_id, $data_source)
	{
		//State & country code managmnet
		$country_helper = new WCCM_Country();
		$data_source['Billing country'] = isset($data_source['Billing country']) ? $country_helper->get_country_code_by_name($data_source['Billing country']) : "";
		$data_source['Billing state'] = isset($data_source['Billing state']) ? $country_helper->get_state_code_by_name($data_source['Billing country'], $data_source['Billing state']) : "";
		$data_source['Shipping country'] = isset($data_source['Shipping country']) ? $country_helper->get_country_code_by_name($data_source['Shipping country']) : "";
		$data_source['Shipping state'] = isset($data_source['Shipping state']) ? $country_helper->get_state_code_by_name($data_source['Shipping country'], $data_source['Shipping state']) : "";
		
		if(isset($data_source['Name']))
			$this->update_user_meta( $user_id, 'first_name', $data_source['Name'] );
		if(isset($data_source['Surname']))
			$this->update_user_meta( $user_id, 'last_name', $data_source['Surname'] );
		if(isset($data_source['Billing email']))
			$this->update_user_meta( $user_id, 'billing_email', $data_source['Billing email'] );
		if(isset($data_source['Billing name']))
			$this->update_user_meta( $user_id, 'billing_first_name', $data_source['Billing name'] );
		if(isset($data_source['Billing surname']))
			$this->update_user_meta( $user_id, 'billing_last_name', $data_source['Billing surname'] );
		if(isset($data_source['Billing phone']))
			$this->update_user_meta( $user_id, 'billing_phone', $data_source['Billing phone'] );
		if(isset($data_source['Billing company']))
			$this->update_user_meta( $user_id, 'billing_company', $data_source['Billing company'] );
		if(isset($data_source['Billing address']))
			$this->update_user_meta( $user_id, 'billing_address_1', $data_source['Billing address'] );
		if(isset($data_source['Billing address 2']))
			$this->update_user_meta( $user_id, 'billing_address_2', $data_source['Billing address 2'] );
		if(isset($data_source['Billing city']))
			$this->update_user_meta( $user_id, 'billing_city', $data_source['Billing city'] );
		if(isset($data_source['Billing state']))
			$this->update_user_meta( $user_id, 'billing_state', $data_source['Billing state'] );
		if(isset($data_source['Billing country']))
			$this->update_user_meta( $user_id, 'billing_country', $data_source['Billing country'] );
		if(isset($data_source['Billing postcode']))
			$this->update_user_meta( $user_id, 'billing_postcode', $data_source['Billing postcode'] );
		
		if(isset($data_source['Shipping name']))	
			$this->update_user_meta( $user_id, 'shipping_first_name', $data_source['Shipping name'] );
		if(isset($data_source['Shipping surname']))
			$this->update_user_meta( $user_id, 'shipping_last_name', $data_source['Shipping surname'] );
		if(isset($data_source['Shipping phone']))
			$this->update_user_meta( $user_id, 'shipping_phone', $data_source['Shipping phone'] );
		if(isset($data_source['Shipping company']))
			$this->update_user_meta( $user_id, 'shipping_company', $data_source['Shipping company'] );
		if(isset($data_source['Shipping address']))
			$this->update_user_meta( $user_id, 'shipping_address_1', $data_source['Shipping address'] );
		if(isset($data_source['Shipping address 2']))
			$this->update_user_meta( $user_id, 'shipping_address_2', $data_source['Shipping address 2'] );
		if(isset($data_source['Shipping postcode']))
			$this->update_user_meta( $user_id, 'shipping_postcode', $data_source['Shipping postcode'] );
		if(isset($data_source['Shipping city']))
			$this->update_user_meta( $user_id, 'shipping_city', $data_source['Shipping city'] );
		if(isset($data_source['Shipping state']))
			$this->update_user_meta( $user_id, 'shipping_state', $data_source['Shipping state'] );
		if(isset($data_source['Shipping country']))
			$this->update_user_meta( $user_id, 'shipping_country', $data_source['Shipping country'] );
		if(isset($data_source['Notes']))
			$this->update_user_meta( $user_id, 'wccm_customer_notes', $data_source['Notes'] );
	}
	public function wccm_custom_insert_user( $userdata ) 
	{
		global $wpdb;

		if ( is_a( $userdata, 'stdClass' ) )
			$userdata = get_object_vars( $userdata );
		elseif ( is_a( $userdata, 'WP_User' ) )
			$userdata = $userdata->to_array();

		extract( $userdata, EXTR_SKIP );

		// Are we updating or creating?
		if ( !empty($ID) ) {
			$ID = (int) $ID;
			$update = true;
			$old_user_data = WP_User::get_data_by( 'id', $ID );
		} else {
			$update = false;
		}

		$user_login = sanitize_user($user_login, true);
		$user_login = apply_filters('pre_user_login', $user_login);

		//Remove any non-printable chars from the login string to see if we have ended up with an empty username
		$user_login = trim($user_login);

		if ( empty($user_login) )
			return new WP_Error('empty_user_login', __('Cannot create a user with an empty login name.') );

		if ( !$update && username_exists( $user_login ) )
			return new WP_Error( 'existing_user_login', __( 'Sorry, that username already exists!' ) );

		if ( empty($user_nicename) )
			$user_nicename = sanitize_title( $user_login );
		$user_nicename = apply_filters('pre_user_nicename', $user_nicename);

		if ( empty($user_url) )
			$user_url = '';
		$user_url = apply_filters('pre_user_url', $user_url);

		if ( empty($user_email) )
			$user_email = '';
		$user_email = apply_filters('pre_user_email', $user_email);

		if ( !$update && ! defined( 'WP_IMPORTING' ) && email_exists($user_email) )
			return new WP_Error( 'existing_user_email', __( 'Sorry, that email address is already used!' ) );

		if ( empty($nickname) )
			$nickname = $user_login;
		$nickname = apply_filters('pre_user_nickname', $nickname);

		if ( empty($first_name) )
			$first_name = '';
		$first_name = apply_filters('pre_user_first_name', $first_name);

		if ( empty($last_name) )
			$last_name = '';
		$last_name = apply_filters('pre_user_last_name', $last_name);

		if ( empty( $display_name ) ) {
			if ( $update )
				$display_name = $user_login;
			elseif ( $first_name && $last_name )
				/* translators: 1: first name, 2: last name */
				$display_name = sprintf( _x( '%1$s %2$s', 'Display name based on first name and last name' ), $first_name, $last_name );
			elseif ( $first_name )
				$display_name = $first_name;
			elseif ( $last_name )
				$display_name = $last_name;
			else
				$display_name = $user_login;
		}
		$display_name = apply_filters( 'pre_user_display_name', $display_name );

		if ( empty($description) )
			$description = '';
		$description = apply_filters('pre_user_description', $description);

		if ( empty($rich_editing) )
			$rich_editing = 'true';

		if ( empty($comment_shortcuts) )
			$comment_shortcuts = 'false';

		if ( empty($admin_color) )
			$admin_color = 'fresh';
		$admin_color = preg_replace('|[^a-z0-9 _.\-@]|i', '', $admin_color);

		if ( empty($use_ssl) )
			$use_ssl = 0;

		if ( empty($user_registered) )
			$user_registered = gmdate('Y-m-d H:i:s');

		if ( empty($show_admin_bar_front) )
			$show_admin_bar_front = 'true';

		$user_nicename_check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_nicename = %s AND user_login != %s LIMIT 1" , $user_nicename, $user_login));

		if ( $user_nicename_check ) {
			$suffix = 2;
			while ($user_nicename_check) {
				$alt_user_nicename = $user_nicename . "-$suffix";
				$user_nicename_check = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_nicename = %s AND user_login != %s LIMIT 1" , $alt_user_nicename, $user_login));
				$suffix++;
			}
			$user_nicename = $alt_user_nicename;
		}

		$data = compact( 'user_pass', 'user_email', 'user_url', 'user_nicename', 'display_name', 'user_registered' );
		$data = wp_unslash( $data );

		if ( $update ) {
			$wpdb->update( $wpdb->users, $data, compact( 'ID' ) );
			$user_id = (int) $ID;
		} else {
			$wpdb->insert( $wpdb->users, $data + compact( 'user_login' ) );
			$user_id = (int) $wpdb->insert_id;
		}

		$user = new WP_User( $user_id );

		foreach ( _get_additional_user_keys( $user ) as $key ) {
			if ( isset( $$key ) )
				$this->update_user_meta( $user_id, $key, $$key );
		}

		if ( isset($role) )
			$user->set_role($role);
		elseif ( !$update )
			$user->set_role(get_option('default_role'));

		wp_cache_delete($user_id, 'users');
		wp_cache_delete($user_login, 'userlogins');

		if ( $update )
			do_action('profile_update', $user_id, $old_user_data);
		else
			do_action('user_register', $user_id);

		return $user_id;
	}
}
?>