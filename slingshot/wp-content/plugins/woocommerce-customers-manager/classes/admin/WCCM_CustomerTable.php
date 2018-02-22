<?php 

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
 
class WCCM_CustomerTable extends WP_List_Table {
    
	var $ordes_starting_date;
	var $ordes_ending_date;
	var $filter_by_product_id;
	var $all_users;
	/* var $hide_total_spent_column;
	var $hide_order_column; */
	var $disable_orders_and_totalspent_column_sort;
	var $data_filter_or_product_filter_enabled;
	var $customers_ids_to_use_as_filter;
	var $customers_emails;
	var $skip_pagination;
	
	function __construct()
	{
        global $status, $page; 
	    /* $this->hide_total_spent_column = get_user_meta(get_current_user_id(), 'wccm-hide-total-spent-column', true);
	   $this->hide_total_spent_column = isset($this->hide_total_spent_column) ? $this->hide_total_spent_column:false;
	   $this->hide_order_column = get_user_meta(get_current_user_id(), 'wccm-hide-orders-column', true);
	   $this->hide_order_column = isset($this->hide_order_column) ? $this->hide_order_column:false; */
	   
	  /*  $this->hide_total_spent_column = WCCM_Options::get_option('hide_total_spent_column');
	   $this->hide_order_column = WCCM_Options::get_option('hide_orders_column'); */
	   
	   $this->disable_orders_and_totalspent_column_sort = WCCM_Options::get_option('disable_order_total_spent_column_sort');
	   $this->disable_orders_and_totalspent_column_sort = $this->disable_orders_and_totalspent_column_sort === 'false' ? false : true;
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'customer',     
            'plural'    => 'customers',    
            'ajax'      => false        
        ) );
        
    }


    function column_default($item, $column_name){
        switch($column_name){
            case 'ID':
            case 'name':
            case 'surname':
            case 'roles':
            case 'login':
            case 'notes':
            case 'address':
            case 'phone':
			case 'email':
			case 'orders':
			case 'total_spent':
			case 'registered':
			case 'first_order_date':
			case 'last_order_date':
			case 'orders_list':
                return $item[$column_name];
            default:
			    $result = apply_filters('manage_customers_custom_column', null, $column_name, $item["ID"] );
                return isset($result) ? $result : __('N/A', 'woocommerce-customers-manager');//print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }



    function column_login($item){
        
        //Build row actions
         $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&customer=%s&edit=1">'.__('Edit', 'woocommerce-customers-manager').'</a>', $_REQUEST['page'], 'wccm-customer-add', $item['ID']),
            'delete'    => sprintf('<a style="color:red;" href="?page=%s&action=%s&customer=%s" onclick="return confirm(\''.__('Are you sure?','woocommerce-customers-manager').'\')" >'.__('Delete', 'woocommerce-customers-manager').'</a>',$_REQUEST['page'], 'delete' ,$item['ID']),
        );
        //Return the title contents
       /*  return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            $item['login'],
            $item['ID'],
            $this->row_actions($actions));
        ); */
		/* return sprintf('%1$s %2$s',
            $item['login'],
            $this->row_actions($actions));
        ); */
		
		//return '<a href="?page='.$_REQUEST['page'].'&customer='.$item['ID'].'&action=customer_details">'.$item['login'].'</a>';
		return @sprintf( '<a href="?page='.$_REQUEST['page'].'&customer='.$item['ID'].'&action=customer_details">'.get_avatar($item['ID'], 32, "", false, array('class'=>'wccm_avatar_img')).$item['login'].'</a>%1$s',
						$this->row_actions($actions));
    }



    function column_cb($item){
        return sprintf(
            '<input type="checkbox"  name="%1$s[]" value="%2$s" />', 
             $this->_args['singular'],  
             $item['ID']               
        );
    }


   
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', 
			'ID'     => 'ID',
			'login'     => __('Login', 'woocommerce-customers-manager'),
            'name'     => __('Name', 'woocommerce-customers-manager'),
            'surname'     => __('Surname', 'woocommerce-customers-manager'),
            'roles'     => __('Role(s)', 'woocommerce-customers-manager'),
            'notes'     => __('Notes', 'woocommerce-customers-manager'),
            'address'    => __('Billing address', 'woocommerce-customers-manager'),
            'phone'    => __('Phone', 'woocommerce-customers-manager'),
            'email'  => __('Email', 'woocommerce-customers-manager'),
			'total_spent'  => __('Total spent', 'woocommerce-customers-manager'),
            'orders'  => __('#Orders', 'woocommerce-customers-manager'), 
            'first_order_date'  => __('First order date', 'woocommerce-customers-manager'), 
            'last_order_date'  => __('Last order date', 'woocommerce-customers-manager'), 
			'registered' => __('Registered', 'woocommerce-customers-manager'),
			'orders_list' => __('Orders list', 'woocommerce-customers-manager')
        );
		
		$options = get_option( 'wccm_general_options');
		$columns_to_hide = isset($options['column_to_hide_in_customer_table']) ? $options['column_to_hide_in_customer_table'] : array();
		foreach((array) $columns_to_hide as $column_to_hide => $value)
			unset($columns[$column_to_hide]);
		
		/* 
		if($this->disable_orders_and_totalspent_column_sort)
		{
			unset($columns['orders']);
			unset($columns['total_spent']);
			unset($columns['last_order_date']);
		} */
		$columns = apply_filters('manage_customers_columns', $columns);
        return $columns;
    }


    
    function get_sortable_columns() {
        $sortable_columns = array(
			'ID'     => array('ID',false), 
            'name'     => array('name',false),     
            'surname'     => array('surname',false),    
            'login'     => array('login',false),    
            'registered' => array('registered',false),
			'orders'  => array('orders',false),
            'email'  => array('email',false),
            //'last_order_date'  => array('last_order_date',false),
			'total_spent' => array('total_spent', false)      
        );
		
		if($this->disable_orders_and_totalspent_column_sort)
		{
			unset($sortable_columns['total_spent']);
			unset($sortable_columns['orders']);
			unset($sortable_columns['last_order_date']);
		}
		/* if(isset($_REQUEST['wccm_customers_ids']))
		{
			unset($sortable_columns['ID']);
			unset($sortable_columns['name']);
			unset($sortable_columns['surname']);
			unset($sortable_columns['email']);
			unset($sortable_columns['login']);
			unset($sortable_columns['registered']);
			unset($sortable_columns['total_spent']);
			unset($sortable_columns['orders']);
			unset($sortable_columns['last_order_date']);
		} */
        return $sortable_columns;
    }


   
    function get_bulk_actions() {
         $actions = array(
			'delete-customers' =>  __('Delete', 'woocommerce-customers-manager'),
			'wccm-bulk-email-customer' =>  __('Email', 'woocommerce-customers-manager')
            //'export-customers'    => __('Export', 'woocommerce-customers-manager'),
        );
        return $actions; 
    }

	private function render_roles_switcher_dropdown()
	{
		global $wp_roles;
		$options = get_option( 'wccm_general_options');
		$first_time = !isset($options['allowed_roles']) ? true:false;
		?>
		
		<div id="wccm_role_switcher_select_box">
			
				<?php _e( 'To assign new roles to your customers:<ol><li>Select one or more from roles the following menu</li><li>Select the customers from list</li><li>Click the Assign button</li></ol><p><strong><i>NOTE: Assigning new roles will overwrite old roles.</i></strong></p.', 'woocommerce-customers-manager'); ?>
			
			<br/>
			<select class="js-role-select" id="wccm_roles_to_assign_select_menu" multiple='multiple'> 
			<?php
			foreach( $wp_roles->roles as $role_code => $role_data)
			{
				$selected = '';		
				/* if(($first_time && $role_code == "customer")  || ($first_time && $role_code == "subscriber"))
					$selected = ' selected="selected" ';
				elseif(!$first_time) */
				if($role_code != 'administrator')
				{
					/* foreach($options['allowed_roles'] as $role)
						if($role == $role_code)
								$selected = ' selected="selected" '; */
							
					echo '<option value="'.$role_code.'" '.$selected.'>'.$role_data['name'].'</option>';
				}
			}
			?>
			</select>
			<br/>
			<div id="wccm_role_result_box"></div>
			<button class="button-primary" id="wccm_assign_roles_button" ><?php _e('Assign', 'woocommerce-customers-manager' ); ?></button>
		</div>
		<?php
	}
   function months_and_roles_dropdown( $post_type = 'shop_order' ) 
   {
	  global  $wp_locale, $wccm_order_model;
 
       
		$months = $wccm_order_model->get_order_months($post_type);
        $months = apply_filters( 'months_dropdown_results', $months, $post_type );
 
        $month_count = count( $months );
 
        if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
            return;
 
		$m = isset( $_REQUEST['m'] ) ? (int) $_REQUEST['m'] : 0; 
		?>
		<?php if(current_user_can('edit_users')): ?>
			<div id="wcc-role-managment-container">
				<div id="wccm-warning-roles-box">
				<?php 
					$roles = WCCM_Options::get_option('allowed_roles');
					if(!isset($roles) || empty($roles)):
						echo __('Currently all user are listed independently from their role.','woocommerce-customers-manager')."<br/><br/>"; 
					else:
						_e('Currently are listed users with following roles:','woocommerce-customers-manager'); 
						$selected_roles = !empty($roles) ? $roles : array(0 => "customer");
						echo "<ol>";
						foreach($selected_roles as $role_to_display)
							echo "<li>".$role_to_display."</li>";
						echo "</ol>";
					endif; ?>
				<a href="<?php echo get_admin_url(); ?>admin.php?page=wccm-options-page" target="_blank"><?php _e('To change displayed roles, go to Options menu','woocommerce-customers-manager'); ?></a>
				</div>
			<?php 
				$this->render_roles_switcher_dropdown();
			?>
			</div>
		<?php endif; ?>
		<div id="wccm_date_filter_box">
			<label for="filter-by-date" class=" ">
				<?php _e( 'Filter by date: by this filter you can discover which customers made at least an order in the selected period, how many orders and total amount spent.', 'woocommerce-customers-manager'); ?>
			</label>		
			<br/>
			<select name="m" id="filter-by-date">
				<option <?php selected( $m, 0 ); ?> value="0"><?php _e( 'All dates' , 'woocommerce-customers-manager'); ?></option>
			<?php
			$selected_date = substr(str_replace("/", "", $this->ordes_starting_date), 0, 6);
			if($selected_date == null || empty($selected_date))
				$selected_date = 0;
			foreach ( $months as $arc_row ) 
			{
				if ( 0 == $arc_row->year )
					continue;
	 
				$month = zeroise( $arc_row->month, 2 );
				$year = $arc_row->year;
				$value_for_current_option =  esc_attr( $arc_row->year . $month );
	 
				printf( "<option %s value='%s' >%s</option>\n",
					selected( $selected_date, $year . $month, false ),
					$value_for_current_option,
					/* translators: 1: month name, 2: 4-digit year */
					
					sprintf( '%1$s %2$d', $wp_locale->get_month( $month ), $year )
				);
			}
			?>
			</select>
			<input class="button-primary" type="submit" value="<?php _e('Filter', 'woocommerce-customers-manager' ); ?>" />
		</div>
	<?php
    }
	
    function process_bulk_action() {
        
       
        if( 'delete'===$this->current_action() ) {
            //wp_die('Items deleted (or they would be if we had items to delete)!');
			wp_delete_user($_REQUEST["customer"] );
        } 
		if( 'export-customers'===$this->current_action() ) 
		{
            
        }
		if( 'wccm-bulk-email-customer'=== $this->current_action() ) 
		{
            //WCCM_Email
        }
		if('delete-customers' ===$this->current_action() ) 
		{
			if(isset($_REQUEST["customer"]))
				foreach($_REQUEST["customer"] as $customer_id)
					wp_delete_user( $customer_id );
		}
        
    }
	
	
    function prepare_items() 
	{
		global $wpdb, $wccm_customer_model; 
	   $user = get_current_user_id();
	   $per_page = get_user_meta($user, 'wccm-customers-options_per_page', true);
	   
	   /* $result = gzinflate(base64_decode($string));
	   wccm_var_dump(explode(",",$result)); */
	   
	   if(isset($_REQUEST['wccm_customers_ids']))
			$this->customers_ids_to_use_as_filter = explode(",", $_REQUEST['wccm_customers_ids']);
		if(isset($_REQUEST['wccm_customers_emails']))
		{
			$this->customers_emails = rtrim(strtr(base64_encode(gzdeflate($_REQUEST['wccm_customers_emails'], 9)), '+/', '-_'), '=');

		}
	   if($per_page == null || $per_page < 1)
		  $per_page = 20;
		
		$this->skip_pagination = false;
		/* $user = get_current_user_id();
		$screen = get_current_screen();
		$option = $screen->get_option('per_page', 'option');
		$per_page = get_user_meta($user, $option, true);	
		
		if ( empty ( $per_page) || $per_page < 1 ) 
			$per_page = $screen->get_option( 'per_page', 'default' );
         */
		$columns = $this->get_columns();
        $hidden = array();
		$sortable = array();
		
        /**** Filters ****/
		//Filter by date	
		if(isset( $_REQUEST['m'] ))
		{
			if($_REQUEST['m'] == 0)
			{
				$this->ordes_starting_date = null;
				$this->ordes_ending_date = null;
			}
			else
			{
				$this->ordes_starting_date = substr($_REQUEST['m'], 0,4)."-".substr($_REQUEST['m'], 4,2)."-01" ;
				$this->ordes_ending_date = substr($_REQUEST['m'], 0,4)."-".substr($_REQUEST['m'], 4,2)."-".date("t", strtotime(substr($_REQUEST['m'], 4,2)));
			}
			//We store result because on colum sorting date $_REQUEST variables are lost
			$wccm_customer_model->update_user_meta( $user, 'wccm-ordes_starting_date', $this->ordes_starting_date );
			$wccm_customer_model->update_user_meta( $user, 'wccm-ordes_ending_date', $this->ordes_ending_date );
		}
		else if(isset($_REQUEST['wccm_start_date']) || isset($_REQUEST['wccm_end_date'])) //Setted only by discover by order feature
		{
			$this->ordes_starting_date = isset($_REQUEST['wccm_start_date']) && $_REQUEST['wccm_start_date'] != "" ? $_REQUEST['wccm_start_date'] : null;
			$this->ordes_ending_date = isset($_REQUEST['wccm_end_date']) && $_REQUEST['wccm_end_date'] != "" ? $_REQUEST['wccm_end_date'] : null;
			
			$wccm_customer_model->update_user_meta( $user, 'wccm-ordes_starting_date', $this->ordes_starting_date );
			$wccm_customer_model->update_user_meta( $user, 'wccm-ordes_ending_date', $this->ordes_ending_date );
		}
		else if((!empty($_REQUEST['orderby']))) //If we sort colums, we read dates from meta fields (because date $_REQUEST are resetted on column sorting)
		{
			$this->ordes_starting_date = get_user_meta($user, 'wccm-ordes_starting_date', true);
			$this->ordes_ending_date = get_user_meta($user, 'wccm-ordes_ending_date', true);
		}
		else
		{
			$this->ordes_starting_date = null;
			$this->ordes_ending_date = null;
			$wccm_customer_model->update_user_meta( $user, 'wccm-ordes_starting_date', null );
			$wccm_customer_model->update_user_meta( $user, 'wccm-ordes_ending_date', null );
		}
		//Filter by product
		$this->filter_by_product_id = null;
		$this->data_filter_or_product_filter_enabled = false; //OLD: Always false
		if(isset( $_REQUEST['filter-by-product']))
			$this->filter_by_product_id = $_REQUEST['filter-by-product'];
		/* End filters */
		
		//Test
		/* echo "<pre>";
		echo var_dump(WCCM_CustomerDetails::get_all_guest_customers());
		echo var_dump(WCCM_CustomerDetails::get_guest_orders_num());
		echo "</pre>"; */
		
		//Force full customer retrieve flag --> OLD
		/* if($this->filter_by_product_id != null )
			$this->data_filter_or_product_filter_enabled = true; */
		
		//if(!isset($this->customers_ids_to_use_as_filter) && !isset($this->customers_emails))
		//if($this->filter_by_product_id == null)
		{
			  $sortable = $this->get_sortable_columns();
		}
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->process_bulk_action();
		
		//Who bought search --> No longer used
		if($this->data_filter_or_product_filter_enabled)
		{
			//wccm_var_dump("order");
			$orders = WCCM_CustomerDetails::get_all_orders_filtered_by_date_and_or_product($this->filter_by_product_id, $this->ordes_starting_date, $this->ordes_ending_date, false, $per_page, ($this->get_pagenum()-1)*$per_page);
			$customers = array();
			foreach($orders as $order)
			{
				if($order->user_email != null) //$customers[$order->user_id]->user_email == null => guest user
				{
					$tmp = new stdClass();
					if(!isset($customers[$order->user_id]))
					{
						$customers[$order->user_id]	=  new stdClass();
						$customers[$order->user_id]->ID = $order->user_id;
						$customers[$order->user_id]->user_email = $order->user_email;
						$customers[$order->user_id]->user_pass = $order->user_pass;
						$customers[$order->user_id]->user_registered = $order->user_registered;
						$customers[$order->user_id]->orders = array();
					}
					$tmp_order = new stdClass();
					$tmp_order->ID = $order->ID;
					array_push($customers[$order->user_id]->orders, $tmp_order);
				}
			}
			
			$total_items = WCCM_CustomerDetails::get_all_orders_filtered_by_date_and_or_product($this->filter_by_product_id, $this->ordes_starting_date, $this->ordes_ending_date, true);
			//$this->skip_pagination = true;
		}
		//Not used anymore
		else if($this->disable_orders_and_totalspent_column_sort ) //restrieve only a subset of users
		{
			$args = array("role" => "customer", "number" => $per_page, "offset" => ($this->get_pagenum()-1)*$per_page);
		
			if(isset($_GET['s']))
			{
				$args['search'] = "*".$_GET['s']."*";
				$args['search_columns'] = array('user_login','user_nicename','display_name', 'user_email'/* , 'user_pass' */);
			}
			if(isset($_GET['orderby']) && isset($_GET['order']))
			{
				$args['orderby'] = $_GET['orderby'];
				$args['order'] = $_GET['order'];
			}
			else
			{
				$args['orderby'] = 'user_registered';
				$args['order'] = 'desc';
			}
			
			$customers = get_users( $args );
			$roles = WCCM_Options::get_option('allowed_roles');
			
			$args = array(
				'role' => 'customer',//substitute your role here as needed
				//'role' => !isset($roles) ? $roles : '',
				'fields' => 'ID',
			);
			if(isset($_GET['s']))
			{
				$args['search'] = "*".$_GET['s']."*";
				$args['search_columns'] = array('user_login','user_nicename','display_name', 'user_email');
			}
			$customer_temp = get_users( $args );
			$total_items = count( $customer_temp );
		}
		else //Retrieve ALL customers. Normal flow.
		{
			//wccm_var_dump("normal flow");
			$customers = WCCM_CustomerDetails::get_all_users_with_orders_ids($this->get_pagenum(), $per_page, $this->customers_ids_to_use_as_filter,false, null,$this->ordes_starting_date, $this->ordes_ending_date, $this->filter_by_product_id);
			$total_items = WCCM_CustomerDetails::get_all_users_with_orders_ids($this->get_pagenum(), $per_page, $this->customers_ids_to_use_as_filter, true,null,$this->ordes_starting_date, $this->ordes_ending_date, $this->filter_by_product_id);
		}
		
		$print = true;
		$data = array();
		foreach ( $customers as $customer ) 
		{
			//Can be extracted returning $data
			$orders = array();
			 if($this->disable_orders_and_totalspent_column_sort)
				$orders = WCCM_CustomerDetails::get_user_orders_ids( $customer->ID, $this->ordes_starting_date, $this->ordes_ending_date);
			else if(!$this->data_filter_or_product_filter_enabled ) //useless, to use: $customer->num_orders
				$orders = isset($customer->order_ids) ? explode(",",$customer->order_ids) : array(); //normal flow
			else
				$orders = $customer->orders;
			
			$orders_num = count($orders);
			if($this->filter_by_product_id == null || !empty($orders) )
			{
				//$total_amount_spent = 0;
				if($this->data_filter_or_product_filter_enabled || $this->disable_orders_and_totalspent_column_sort){
					//to improve
					$total_amount_spent = WCCM_CustomerDetails::get_user_total_spent( $this->ordes_starting_date, $this->ordes_ending_date, $orders, false);
				}
				else
					//Si è deciso di visualizzare il numero di ordini (tenendo anche conto di quelli "bad"). Pertanto, per calcolare l'ammontare della somma spesa
					// bisogna calcolarla in un secondo passaggio escludendo pero quelli "bad". In caso si voglia ripristinare, modificare "get_orders_query_conditions_to_exclude_bad_orders" 
					//dentro CustomerDetails decommentando le rige dopo "Filter by status"  e decommentare la seguente linea e commennatere la succesiva
					
					//$total_amount_spent = isset($customer->total_sales) ? round($customer->total_sales, 1) : 0;
					$total_amount_spent = WCCM_CustomerDetails::get_user_total_spent( $this->ordes_starting_date, $this->ordes_ending_date, $orders, false);
				
				$can_print_user = true;
				/* Hide not spending customers
				if($this->ordes_starting_date != null && $this->ordes_ending_date != null && $total_amount_spent == 0)
					$can_print_user = false; 
				*/
				if($can_print_user)
				{
					$customer_info = $wccm_customer_model->get_user_data($customer->ID); //get_userdata( $customer->ID );
					$customer_extra_info = get_user_meta($customer->ID);
					
					$first_order_date = "N/A";
					$last_order_date = "N/A";
					//if(!$this->hide_order_column )
					{
						$last_order_date = WCCM_CustomerDetails::get_last_order_date( $customer->ID);
						$first_order_date = WCCM_CustomerDetails::get_first_order_date( $customer->ID);
					}
					$countries_obj   = new WC_Countries();
					global $wp_roles;
					
					$billing_state_full_name = isset($customer_extra_info['billing_state']) && $customer_extra_info['billing_state'][0] !=null ? $customer_extra_info['billing_state'][0]:"";
					$billing_country_full_name = isset($customer_extra_info['billing_country']) && $customer_extra_info['billing_country'][0] !=null ? country_code_to_country($customer_extra_info['billing_country'][0]) : "";
					if($billing_country_full_name != "" && $countries_obj->get_states($customer_extra_info['billing_country'][0] ))
					{
						$billing_states_list = $countries_obj->get_states($customer_extra_info['billing_country'][0] );
						if($billing_state_full_name != "" && isset($billing_states_list[$billing_state_full_name]))
							$billing_state_full_name = $billing_states_list[$billing_state_full_name];
					}
					
					$shipping_state_full_name = isset($customer_extra_info['shipping_state']) && $customer_extra_info['shipping_state'][0] !=null ? $customer_extra_info['shipping_state'][0] : "";
					$shipping_country_full_name = isset($customer_extra_info['shipping_country']) && $customer_extra_info['shipping_country'][0] !=null ? country_code_to_country($customer_extra_info['shipping_country'][0]) : "";
					if($shipping_country_full_name != "" && $countries_obj->get_states($customer_extra_info['shipping_country'][0] ))
					{
						$shipping_states_list = $countries_obj->get_states($customer_extra_info['shipping_country'][0] );
						if($shipping_state_full_name != "" && isset($shipping_states_list[$shipping_state_full_name]))
							$shipping_state_full_name = $shipping_states_list[$shipping_state_full_name];
					}
					
					/* billing_country, billing_first_name, billing_last_name, billing_company, billing_address_1, billing_address_2, billing_city
					   billing_state, billing_postcode, billing_email, billing_phone */
					   
					 /* shipping_country, shipping_first_name, shipping_last_name, shipping_company, shipping_address_1, shipping_address_2, shipping_city
					 shipping_state, shipping_postcode, shipping_country*/
					   
					 $address = isset($customer_extra_info['billing_first_name']) && $customer_extra_info['billing_first_name'][0] !== " "? $customer_extra_info['billing_first_name'][0]." ":'';
					 $address .= isset($customer_extra_info['billing_last_name']) && $customer_extra_info['billing_last_name'][0] !== "" ? $customer_extra_info['billing_last_name'][0]:'';
					 $address .= isset($customer_extra_info['billing_company']) && $customer_extra_info['billing_company'][0] !== "" ? "<br/>".$customer_extra_info['billing_company'][0]:'';
					 $address .= "<br/><br/>";
					 $address .= isset($customer_extra_info['billing_address_1']) && $customer_extra_info['billing_address_1'][0] !=null && $customer_extra_info['billing_address_1'][0] !== " " ? $customer_extra_info['billing_address_1'][0].", ":'';
					 $address .=  isset($customer_extra_info['billing_postcode']) && $customer_extra_info['billing_postcode'][0] !=null && $customer_extra_info['billing_postcode'][0] !== " " ? $customer_extra_info['billing_postcode'][0].", ":'';
					 $address .=  isset($customer_extra_info['billing_city']) && $customer_extra_info['billing_city'][0] !=null && $customer_extra_info['billing_city'][0] !== " " ? $customer_extra_info['billing_city'][0].", ":'';
					 //$address .=  isset($customer_extra_info['billing_state']) && $customer_extra_info['billing_state'][0] !=null ? $customer_extra_info['billing_state'][0].",":'';
					 $address .=  $billing_state_full_name ? $billing_state_full_name.", ":'';
					 //$address .=  isset($customer_extra_info['billing_country']) && $customer_extra_info['billing_country'][0] !=null ? $customer_extra_info['billing_country'][0]:'';
					 $address .=  $billing_country_full_name ? $billing_country_full_name:'';
					
					 if( $vat_number = $wccm_customer_model->get_vat_number($customer->ID))
						 $address .= "<br/><br/><strong>".__('VAT:','woocommerce-customers-manager')."</strong> ".$vat_number;
					 
					 //For csv export
					 $address_billing = isset($customer_extra_info['billing_address_1']) && $customer_extra_info['billing_address_1'][0] !=null ? $customer_extra_info['billing_address_1'][0].",":' ,';
					 $address_billing .= isset($customer_extra_info['billing_address_2']) && $customer_extra_info['billing_address_2'][0] !=null ? $customer_extra_info['billing_address_2'][0].",":' ,';
					 $address_billing .=  isset($customer_extra_info['billing_postcode']) && $customer_extra_info['billing_postcode'][0] !=null ? $customer_extra_info['billing_postcode'][0].",":' ,';
					 $address_billing .=  isset($customer_extra_info['billing_city']) && $customer_extra_info['billing_city'][0] !=null ? $customer_extra_info['billing_city'][0].",":' ,';
					 //$address_billing .=  isset($customer_extra_info['billing_state']) && $customer_extra_info['billing_state'][0] !=null ? $customer_extra_info['billing_state'][0].",":' ,';
					 $address_billing .=  $billing_state_full_name ? $billing_state_full_name.",":',';
					 //$address_billing .=  isset($customer_extra_info['billing_country']) && $customer_extra_info['billing_country'][0] !=null ? $customer_extra_info['billing_country'][0]:' ';
					 $address_billing .=  $billing_country_full_name ? $billing_country_full_name.",":',';
					
					 $address_shipping = isset($customer_extra_info['shipping_address_1']) && $customer_extra_info['shipping_address_1'][0] !=null ? $customer_extra_info['shipping_address_1'][0].",":' ,';
					 $address_shipping .= isset($customer_extra_info['shipping_address_2']) && $customer_extra_info['shipping_address_2'][0] !=null ? $customer_extra_info['shipping_address_2'][0].",":' ,';
					 $address_shipping .=  isset($customer_extra_info['shipping_postcode']) && $customer_extra_info['shipping_postcode'][0] !=null ? $customer_extra_info['shipping_postcode'][0].",":' ,';
					 $address_shipping .=  isset($customer_extra_info['shipping_city']) && $customer_extra_info['shipping_city'][0] !=null ? $customer_extra_info['shipping_city'][0].",":' ,';
					 //$address_shipping .=  isset($customer_extra_info['shipping_state']) && $customer_extra_info['shipping_state'][0] !=null ? $customer_extra_info['shipping_state'][0].",":' ,';
					 $address_shipping .=  $shipping_state_full_name ? $shipping_state_full_name.",":',';
					 //$address_shipping .=  isset($customer_extra_info['shipping_country']) && $customer_extra_info['shipping_country'][0] !=null ? $customer_extra_info['shipping_country'][0]:' ';
					 $address_shipping .=  $shipping_country_full_name ? $shipping_country_full_name.",":',';
					
					//Roles
					$roles = "";
					$roles_to_export = "";
					$counter_roles_temp = 0;
					if ( !empty( $customer_info->roles ) && is_array( $customer_info->roles ) ) 
					{
						if( $wccm_customer_model->is_blocked_customer($customer->ID))
							$roles .= "<span class='blocked_customer_role_text'>".$wccm_customer_model->get_blocked_role_name()."</span>";
						
						foreach ( $customer_info->roles as $role_code )
						{
							if($role_code == 'blocked_customer')
								continue;
							
							$role_html = "<span class='customer_role_text'>".$wp_roles->roles[$role_code]["name"]."</span>";
							$roles .= $counter_roles_temp > 0 ? ",<br/>".$role_html : $role_html;
							$roles_to_export .= $counter_roles_temp > 0 ? " ".$role_code : $role_code;
							$counter_roles_temp++;
						}
					}
					
					//if($can_print_user)
						array_push($data, array( 'ID' => $customer->ID,
												'name' => isset($customer_info->first_name) ? $customer_info->first_name:'',//$customer_info->first_name,
												'surname' =>isset($customer_info->last_name) ? $customer_info->last_name:'',//$customer_info->last_name,
												'roles' =>  $roles,
												'notes' =>  isset($customer_extra_info['wccm_customer_notes']) ? $customer_extra_info['wccm_customer_notes'][0]:'',
												'login' =>$customer_info->user_login,
												'email' =>$customer->user_email,
												'registered' =>$customer->user_registered,
												'orders_list' => /* '<a class="" target="_blank" href="'.admin_url('edit.php?s='.$customer->user_email.'&post_status=all&post_type=shop_order').'">'.
																	'<span class="dashicons dashicons-list-view"></span>'.
																	'</a>', */
																 '<a class="" target="_blank" href="'.admin_url('edit.php?s&post_status=all&post_type=shop_order&action=-1&_customer_user='.$customer->ID.'&filter_action=Filter').'">'.
																 '<span class="dashicons dashicons-list-view"></span>'.
																	'</a>',
												'address' => $address,						
												'phone' => isset($customer_extra_info['billing_phone']) ? $customer_extra_info['billing_phone'][0]:'',
												//Used only for csv export data
												'billing_name' => isset($customer_extra_info['billing_first_name']) ? $customer_extra_info['billing_first_name'][0]:'',
												'billing_surname' => isset($customer_extra_info['billing_last_name']) ? $customer_extra_info['billing_last_name'][0]:'',
												'address_billing' => $address_billing,
												'address_shipping' => $address_shipping,
												'shipping_name' => isset($customer_extra_info['shipping_first_name']) ? $customer_extra_info['shipping_first_name'][0]:'',
												'shipping_last_name' => isset($customer_extra_info['shipping_last_name']) ? $customer_extra_info['shipping_last_name'][0]:'',
												'shipping_phone' => isset($customer_extra_info['shipping_phone']) ? $customer_extra_info['shipping_phone'][0]:'',
												'shipping_company' => isset($customer_extra_info['shipping_company']) ? $customer_extra_info['shipping_company'][0]:'',
												'billing_phone' => isset($customer_extra_info['billing_phone']) ? $customer_extra_info['billing_phone'][0]:'',
												'billing_email' => isset($customer_extra_info['billing_email']) ? $customer_extra_info['billing_email'][0]:'',
												'billing_company' => isset($customer_extra_info['billing_company']) ? $customer_extra_info['billing_company'][0]:'', //str_replace(array("'", "\""), "", $customer_extra_info['billing_company'][0]),
												'vat_number' => $vat_number ? $vat_number:'', //str_replace(array("'", "\""), "", $customer_extra_info['billing_company'][0]),
												'roles_to_export' => $roles_to_export,
												//
												'orders' => $orders_num,
												'last_order_date' => $last_order_date,
												'first_order_date' => $first_order_date,
												'total_spent' => /* get_woocommerce_currency_symbol(). */$total_amount_spent,
												'total_spent_without_currency' => $total_amount_spent,
												'password_hash'=> $customer->user_pass
												));
				
				}
				//END: Can be extracted returning $data
			}
		}
      
		function compare_counts($a, $b) 
		{
		  return $a  - $b;
		}
        function usort_reorder($a,$b)
		{
			$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'registered'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc
			if($orderby == 'total_spent' || $orderby == 'orders')
				$result = compare_counts($a[$orderby], $b[$orderby]);
			else
				$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
       
      /*  if($this->data_filter_or_product_filter_enabled )
		    usort($data, 'usort_reorder'); */
		
	   $current_page = $this->get_pagenum();
	  
	   //if($this->disable_orders_and_totalspent_column_sort && !$this->data_filter_or_product_filter_enabled)
	   {
		   $this->all_users = $data;
		   /* $args = array(
			'role' => 'customer',//substitute your role here as needed
			'fields' => 'ID',
			);
			if(isset($_GET['s']))
			{
				$args['search'] = "*".$_GET['s']."*";
				$args['search_columns'] = array('user_login','user_nicename','display_name', 'user_email');
			}
			$customer_temp = get_users( $args );
			$total_items = count( $customer_temp ); */
	   }
	  /*  else //normal
	   {
		 usort($data, 'usort_reorder');
		 $this->all_users = $data;
         $total_items = count($data);
         $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
		} */
		$this->items = $data;
		if(!$this->skip_pagination)
			$this->set_pagination_args( array(
				'total_items' => $total_items,                  //total number of items
				'per_page'    => $per_page,                     //items to show on a page
				'total_pages' => ceil($total_items/$per_page)   //total number of pages
			) );
	   
    }
	
	
	function render_page()
	{
		global $wccm_customer_model;
		$wpuef_column_titles = $wccm_customer_model->get_wpuef_field_names();
		$wpuef_column_titles_and_ids = $wccm_customer_model->get_wpuef_field_names_and_ids();
		
		wp_enqueue_style( 'wccm-select2-style',  WCCM_PLUGIN_PATH.'/css/select2.min.css' );
		wp_enqueue_style('wccm-common', WCCM_PLUGIN_PATH.'/css/common.css');   
		wp_enqueue_style('customer-table-css', WCCM_PLUGIN_PATH.'/css/customers-table.css');  
		
		wp_enqueue_script('wccm-customers-table-assign-role', WCCM_PLUGIN_PATH.'/js/admin-customers-table-assign-role.js', array('jquery'));
		wp_enqueue_script('wccm-customers-table', WCCM_PLUGIN_PATH.'/js/admin-customers-table.js', array('jquery'));
		?> 
		
		<?php if(/* !$this->data_filter_or_product_filter_enabled &&  !$this->customers_ids_to_use_as_filter*/true): ?>
		
		<script>
			jQuery.fn.select2=null;
		</script>
		<script type='text/javascript' src='<?php echo WCCM_PLUGIN_PATH.'/js/select2.min.js'; ?>'></script>
		
		<div id="icon-users" class="icon32"><br/></div> 
		<h2 class="nav-tab-wrapper">
			<a class='nav-tab nav-tab-active' href='?page=woocommerce-customers-manager<?php if(isset($this->filter_by_product_id)) echo '&filter-by-product='.$this->filter_by_product_id; if(isset($_REQUEST['wccm_customers_ids'])) echo '&wccm_customers_ids='.$_REQUEST['wccm_customers_ids']; if(isset($this->customers_emails)) echo '&wccm_customers_emails='.$this->customers_emails; if(isset($this->ordes_starting_date)) echo '&wccm_start_date='.$this->ordes_starting_date; if(isset($this->ordes_ending_date)) echo '&wccm_end_date='.$this->ordes_ending_date; ?>'>Registered</a>
			<a class='nav-tab' href='?page=woocommerce-customers-manager&action=wccm-guests-list<?php if(isset($this->filter_by_product_id)) echo '&filter-by-product='.$this->filter_by_product_id; if(isset($_REQUEST['wccm_customers_ids'])) echo '&wccm_customers_ids='.$_REQUEST['wccm_customers_ids']; if(isset($this->customers_emails)) echo '&wccm_customers_emails='.$this->customers_emails; if(isset($this->ordes_starting_date)) echo '&wccm_start_date='.$this->ordes_starting_date; if(isset($this->ordes_ending_date)) echo '&wccm_end_date='.$this->ordes_ending_date; ?>'>Guests</a>
		</h2>
		<?php endif ?>
		
        <h2><?php _e('Customers List', 'woocommerce-customers-manager'); ?> 
			<a class="add-new-h2" href="<?php echo admin_url()."admin.php?page=wccm-add-new-customer"; ?>"><?php _e('Add customer', 'woocommerce-customers-manager'); ?> </a>
		</h2>
		<?php if ($this->filter_by_product_id != null): 
		
			//$wc_product = new WC_Product( $this->filter_by_product_id );
			$wc_product = wc_get_product( $this->filter_by_product_id );

			?>
			<h4>
				<?php echo sprintf( __('List will display only customers who made orders that include product: <i>%1$s</i>. Total amount spent is calculated using only these orders. Orders number is calculated using only orders that include selected product.', 'woocommerce-customers-manager'),  $wc_product->get_title( ));  ?><br/>	
			</h4>
		<?php endif; ?>
		<form method="GET">
			  <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
			  <?php  if(!isset($this->customers_ids_to_use_as_filter)): ?>
			  <p class="search-box">
				<input type="search" id="search_id-search-input" name="s" value="<?php _admin_search_query(); ?>" />
				<?php submit_button( __('search', 'woocommerce-customers-manager'), 'button', '', false, array('id' => 'search-submit') ); ?>
			  </p>
			  <?php endif; ?>
		</form>
		<input id="csv-button-customer-table" class="button-primary" name="" value="<?php _e('Download selected customers CSV', 'woocommerce-customers-manager'); ?>" />
		<?php if(isset($this->customers_ids_to_use_as_filter)): ?>
		<form action="admin.php?page=wccm-export-customers" method="post">
			<input  type="hidden" name="wccm_customers_ids" value="<?php echo $_REQUEST['wccm_customers_ids'] ?>" ></input>
			<button  id="csv-button-complete-download" class="button"><?php _e('Download complete customers CSV', 'woocommerce-customers-manager'); ?></button>
		</form>
		<?php endif; ?>
		<p>
			<!--<strong>
			<?php _e("NOTE: If a Customer has #Orders column value more thant 0 and Total spent column equal to 0, is because he has only cancelled order.", 'woocommerce-customers-manager'); ?><br/>
			<?php _e("In general, in Total spent value computation cancelled orders are not condisidered.", 'woocommerce-customers-manager'); ?>
			</strong>-->
		</p>
		<form id="customer-filter" method="post">
            <input type="hidden" name="page" value="<?php echo /* plugin_dir_url( __FILE__ )."ExportCSV.php";*/ $_REQUEST['page'] ?>" />
		   <?php 
		   if(!isset($this->customers_ids_to_use_as_filter))
		   {
				$this->months_and_roles_dropdown(); 
		   }?> 
			
			<?php $this->display(); ?>
        </form>
		
		<script>
		
		var wccm_role_empty_error = "<?php _e('You have to select at least one role','woocommerce-customers-manager'); ?>";		
		var wccm_user_empty_error = "<?php _e('You have to select at least one user','woocommerce-customers-manager'); ?>";		
		var wccm_role_generic_error = "<?php _e('An error has occurred!','woocommerce-customers-manager'); ?>";		
		var wccm_role_wait_message = "<?php _e('Assigning values, please wait...','woocommerce-customers-manager'); ?>";		
		var wccm_role_reload_message = "<?php _e('Assignment complete! Reloading page...','woocommerce-customers-manager'); ?>";		
		var data = [["ID", "Password hash",
		   "<?php _e('Name', 'woocommerce-customers-manager'); ?>", 
		   "<?php _e('Surname', 'woocommerce-customers-manager'); ?>",
		   "<?php _e('Role', 'woocommerce-customers-manager'); ?>",
				     "Login",
				     "Email",
		   "<?php _e('Notes', 'woocommerce-customers-manager'); ?>", 
		   "<?php _e('Registration date', 'woocommerce-customers-manager'); ?>",
		   "<?php _e('First order date', 'woocommerce-customers-manager'); ?>",
		   "<?php _e('Last date', 'woocommerce-customers-manager'); ?>",
		   "<?php _e('# Orders', 'woocommerce-customers-manager'); ?>",
		   "<?php _e('Total amount spent', 'woocommerce-customers-manager'); ?>",
		   "<?php _e('Billing name', 'woocommerce-customers-manager'); ?>",
		   "<?php _e('Billing surname', 'woocommerce-customers-manager'); ?>",		   
		   "<?php _e('Billing email', 'woocommerce-customers-manager'); ?>",
		   "<?php _e('Billing phone', 'woocommerce-customers-manager'); ?>",
		   "<?php _e('Billing company', 'woocommerce-customers-manager'); ?>",
		   "<?php _e('VAT number', 'woocommerce-customers-manager'); ?>",
		   //"\"<?php _e('Adrress (billing)', 'woocommerce-customers-manager'); ?>\"",
		    "<?php _e('Billing address', 'woocommerce-customers-manager'); ?>",
		    "<?php _e('Billing address 2', 'woocommerce-customers-manager'); ?>",
		    "<?php _e('Billing postcode', 'woocommerce-customers-manager'); ?>",
		    "<?php _e('Billing city', 'woocommerce-customers-manager'); ?>",
		    "<?php _e('Billing state', 'woocommerce-customers-manager'); ?>",
		    "<?php _e('Billing country', 'woocommerce-customers-manager'); ?>",
		   //"\"<?php _e('Address (shipping)', 'woocommerce-customers-manager'); ?>\"",
		    "<?php _e('Shipping name', 'woocommerce-customers-manager'); ?>",
			"<?php _e('Shipping surname', 'woocommerce-customers-manager'); ?>",
			"<?php _e('Shipping phone', 'woocommerce-customers-manager'); ?>",
			"<?php _e('Shipping company', 'woocommerce-customers-manager'); ?>",
		    "<?php _e('Shipping address', 'woocommerce-customers-manager'); ?>",
		    "<?php _e('Shipping address 2', 'woocommerce-customers-manager'); ?>",
		    "<?php _e('Shipping postcode', 'woocommerce-customers-manager'); ?>",
		    "<?php _e('Shipping city', 'woocommerce-customers-manager'); ?>",
		    "<?php _e('Shipping state', 'woocommerce-customers-manager'); ?>",
		    "<?php _e('Shipping country', 'woocommerce-customers-manager'); ?>"
			<?php if( WCCM_Options::wpuef_include_fields_on_csv_export() && !empty($wpuef_column_titles)) echo ',"\"'.implode('\"","\"',$wpuef_column_titles).'\""'; else echo "" ?>
			] 
					<?php 
						foreach($this->all_users as $customer)
						{
							list($billing_address1,$billing_address2, $billing_postcode,$billing_city,$billing_state,$billing_country ) =  explode(",", $customer['address_billing']);
							list($shipping_address1,$shipping_address2, $shipping_postcode,$shipping_city,$shipping_state,$shipping_country ) =  explode(",", $customer['address_shipping']);
							echo  ',';
							$bad_chars = array( '\'', '"', ',' , ';', '\\', "\t" ,"\n" , "\r");
							$row = '["'.$customer['ID'].'","'
									 .$customer['password_hash'].'","'
									 .str_replace($bad_chars, '', $customer['name']).'","'
									 .str_replace($bad_chars, '', $customer['surname']).'","'
									 .$customer['roles_to_export'].'","'
									 .$customer['login'].'","'
									 .$customer['email'].'","'
									 .str_replace( $bad_chars, '', $customer['notes']).'","'
									 .$customer['registered'].'","'
									 .$customer['first_order_date'].'","'
									 .$customer['last_order_date'].'","'
									 .$customer['orders'].'","'
									 .$customer['total_spent_without_currency'].'","'
									 .str_replace($bad_chars, '',$customer['billing_name']).'","'
									 .str_replace($bad_chars, '',$customer['billing_surname']).'","'
									 .$customer['billing_email'].'","'
									 .$customer['billing_phone'].'","'
									 //.$customer['billing_company'].'","'
									 .str_replace($bad_chars, '', $customer['billing_company']).'","'
									 .str_replace($bad_chars, '', $customer['vat_number']).'","'
									 //.$customer['address'].'\"","\"'
									 //.$customer['address_shipping'].'\"","'
									 .str_replace($bad_chars, '', $billing_address1).'","'
									 .str_replace($bad_chars, '', $billing_address2).'","'
									 .$billing_postcode.'","'
									 .str_replace($bad_chars, '',$billing_city).'","'
									 .str_replace($bad_chars, '',$billing_state).'","'
									 .str_replace($bad_chars, '',$billing_country).'","'
									 .str_replace($bad_chars, '', $customer['shipping_name']).'","'
									 .str_replace($bad_chars, '', $customer['shipping_last_name']).'","'
									 .$customer['shipping_phone'].'","'
									 .str_replace($bad_chars, '', $customer['shipping_company']).'","'
									 .str_replace($bad_chars, '', $shipping_address1).'","'
									 .str_replace($bad_chars, '', $shipping_address2).'","'
									 .$shipping_postcode.'","'
									 .str_replace($bad_chars, '',$shipping_city).'","'
									 .str_replace($bad_chars, '',$shipping_state).'","'
									 .str_replace($bad_chars, '',$shipping_country).'"';
							     
								 
							if( WCCM_Options::wpuef_include_fields_on_csv_export() && !empty($wpuef_column_titles_and_ids))
							{
								foreach($wpuef_column_titles_and_ids as $wpuef_colum)
									$row .= ',"\"'.$wccm_customer_model->get_wpuef_field_content($customer['ID'], $wpuef_colum['id']).'\""';
							}	 
							echo $row.']';
						}
					?>];
					
		
		if(jQuery('#search_id-search-input').val() != "" && typeof jQuery('#search_id-search-input').val()!=='undefined')
		{
			jQuery(".first-page").attr('href', jQuery(".first-page").attr('href')+"&s="+jQuery('#search_id-search-input').val());
			jQuery(".prev-page").attr('href', jQuery(".prev-page").attr('href')+"&s="+jQuery('#search_id-search-input').val());
			jQuery(".next-page").attr('href', jQuery(".next-page").attr('href')+"&s="+jQuery('#search_id-search-input').val());
			jQuery(".last-page").attr('href', jQuery(".last-page").attr('href')+"&s="+jQuery('#search_id-search-input').val());
		}
		<?php 
				$get_string = "";
				if(isset($this->customers_ids_to_use_as_filter))
					$get_string .= '&wccm_customers_ids='.$_REQUEST['wccm_customers_ids'];//implode(",", $this->customers_ids_to_use_as_filter);
				if(isset($this->customers_emails))
					$get_string .= '&wccm_customers_emails='.$this->customers_emails;
				if(isset($this->ordes_starting_date)) 
					$get_string .= '&wccm_start_date='.$this->ordes_starting_date; 
				if(isset($this->ordes_ending_date)) 
					$get_string .= '&wccm_end_date='.$this->ordes_ending_date;
				if(isset($_REQUEST['m']))
					$get_string .= '&m='.$_REQUEST['m'];
				?>
				
				
		jQuery(".first-page").attr('href', jQuery(".first-page").attr('href')+"<?php echo $get_string; ?>");
		jQuery(".prev-page").attr('href', jQuery(".prev-page").attr('href')+"<?php echo $get_string; ?>");
		jQuery(".next-page").attr('href', jQuery(".next-page").attr('href')+"<?php echo $get_string; ?>");
		jQuery(".last-page").attr('href', jQuery(".last-page").attr('href')+"<?php echo $get_string; ?>"); 
		jQuery('th a').each(function(index,value)
		{
			//console.log(jQuery(this).attr('href'));
			jQuery(this).attr('href', jQuery(this).attr('href')+"<?php echo $get_string; ?>"); 
		});
		
		jQuery('#csv-button-customer-table').click(function()
		{
			var ids_to_export = [];
			var csvRows = [];
			
			jQuery('th.check-column input').each(function(index)
			{
				if(!isNaN(jQuery(this).attr('value')) && jQuery(this).prop( "checked" ) == true)
				{
					ids_to_export.push(jQuery(this).attr('value'));
				}
			});
			
			
			for(var i=0,l=data.length; i<l; ++i)
			{
				if(ids_to_export.length == 0) 
					csvRows.push(data[i].join(','));  
				else
				{
					if(i == 0) //Title column
							csvRows.push(data[0].join(',')); 
					else
						for(var j=0; j<ids_to_export.length;j++)
						{
							if(ids_to_export[j] == data[i][0])
									csvRows.push(data[i].join(','));
						}
					
				}
			}
			
			
			var csvString = csvRows.join("\r\n");
			var a         = document.createElement('a');
			a.href        = 'data:attachment/csv,' + encodeURIComponent(csvString);
			a.target      = '_blank';
			a.download    = 'WCCM-customers_list.csv';

			document.body.appendChild(a);
			a.click();
		});
		
		
		</script>
		<?php
	}


}
?>