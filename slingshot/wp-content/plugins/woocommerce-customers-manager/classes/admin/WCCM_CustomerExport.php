<?php 
class WCCM_CustomerExport
{
	public function __construct()
	{
		if ( is_admin() ) 
		{
			add_action( 'wp_ajax_wccm_export_csv', array( &$this, 'process_export_csv_chunk' ) );
			add_action( 'wp_ajax_wccm_export_guests_csv', array( &$this, 'get_guest_customers' ) );
			add_action( 'wp_ajax_wccm_export_get_max_regiesterd_users', array( &$this, 'get_max_regiesterd_users' ) );
			add_action( 'wp_ajax_wccm_export_get_max_guest_orders_iterations', array( &$this, 'get_max_guest_orders_iterations' ) );
		}
	}
	public function get_max_regiesterd_users()
	{
		$ids_to_export = isset($_POST['ids_to_export']) ? explode(",", $_POST['ids_to_export']) : null;
		$result =  WCCM_CustomerDetails::get_customers_num($ids_to_export);
		echo $result->total_customers;
		wp_die();
	}
	public function get_max_guest_orders_iterations()
	{
		global $wccm_order_model;
		$filter_by_product = isset($_POST['filter_by_product']) && $_POST['filter_by_product'] != 0 ? $_POST['filter_by_product']:false;
		$filter_by_emails = isset($_POST['customer_emails']) && $_POST['customer_emails'] != '0' ? $_POST['customer_emails']:false;
		$result = $wccm_order_model->get_guest_orders_num($filter_by_product, $filter_by_emails);
		echo $result->total_guest_orders;
		wp_die();
	}
	public function get_guest_customers()
	{
		$per_page = $_POST['per_page'];
		$page_num = $_POST['page_num'];
		$reverse_order = isset($_POST['reverse_order']) ? true : false;
		$get_last_order_id = isset($_POST['get_last_order_id']) && $_POST['get_last_order_id'] == 'yes' ? true:false;
		$filter_by_product = isset($_POST['filter_by_product']) && $_POST['filter_by_product'] != 0 ? $_POST['filter_by_product']:false;
		$filter_by_emails = isset($_POST['customer_emails']) && $_POST['customer_emails'] != '0' ? $_POST['customer_emails']:false;
		
		global $wccm_customer_model;
		$guest_customer_list = $wccm_customer_model->get_all_guest_customers($page_num, $per_page, $get_last_order_id,$reverse_order, $filter_by_product, $filter_by_emails);
		
		//wpuef		
		$wpuef_column_titles_and_ids = $wccm_customer_model->get_wpuef_field_names_and_ids();					
		if( WCCM_Options::wpuef_include_fields_on_csv_export() && !empty($wpuef_column_titles_and_ids))
		{
			foreach($guest_customer_list as $guest_index => $guest_value)
				foreach($wpuef_column_titles_and_ids as $wpuef_colum)
					$guest_customer_list[$guest_index][$wpuef_colum['title']] = "";
		}
		
		echo json_encode($guest_customer_list);
		wp_die();
	}
	public function process_export_csv_chunk() //Registerd customers
	{
		global $wccm_customer_model;
		$data = array();
		$per_page = $_POST['per_page'];
		$page_num = $_POST['page_num'];
		$ids_to_export = isset($_POST['ids_to_export']) ? explode(",", $_POST['ids_to_export']) : null;
		$customers = WCCM_CustomerDetails::get_all_users_with_orders_ids($page_num , $per_page,$ids_to_export, false, $ids_to_export);
		$bad_chars = array( '\'', '"', ',' , ';', '\\', "\t" ,"\n" , "\r");
		$wpuef_column_titles_and_ids = $wccm_customer_model->get_wpuef_field_names_and_ids();
		foreach($customers as $customer)
		{
			$customer_info = $wccm_customer_model->get_user_data($customer->ID);//get_userdata( $customer->ID );
			$customer_extra_info = get_user_meta($customer->ID);
				
			$last_order_date = WCCM_CustomerDetails::get_last_order_date( $customer->ID);
			$first_order_date = WCCM_CustomerDetails::get_first_order_date( $customer->ID);
			$orders_num = $customer->num_orders;
			$total_amount_spent = isset($customer->total_sales) ? round($customer->total_sales, 1) : 0;
			$vat_number = $wccm_customer_model->get_vat_number($customer->ID);
			//Roles
			$roles_to_export = "";
			$counter_roles_temp = 0;
			if ( !empty( $customer_info->roles ) && is_array( $customer_info->roles ) ) 
				foreach ( $customer_info->roles as $role_code )
				{
					$roles_to_export .= $counter_roles_temp > 0 ? " ".$role_code : $role_code;
					$counter_roles_temp++;
				}
						
			$temp_user_data = array( 'ID' => $customer->ID,
										'password_hash'=> $customer->user_pass,
										'name' => isset($customer_info->first_name) ? str_replace($bad_chars, '',$customer_info->first_name):'',//$customer_info->first_name,
										'surname' =>isset($customer_info->last_name) ? str_replace($bad_chars, '',$customer_info->last_name):'',//$customer->last_name,
										'roles_to_export' => $roles_to_export,
										'login' =>$customer_info->user_login,
										'email' =>$customer->user_email,
										'notes' =>  isset($customer_extra_info['wccm_customer_notes']) ? str_replace( $bad_chars, '',$customer_extra_info['wccm_customer_notes'][0]):'',
										'registered' =>$customer->user_registered,
										'first_order_date' => $first_order_date,
										'last_order_date' => $last_order_date,
										'orders' => $orders_num,
										'total_spent' => /* get_woocommerce_currency_symbol(). */$total_amount_spent,
										//'address' => $address,
										'billing_name' => isset($customer_extra_info['billing_first_name']) ? str_replace($bad_chars, '',$customer_extra_info['billing_first_name'][0]):'',
										'billing_surname' =>isset($customer_extra_info['billing_last_name']) ? str_replace($bad_chars, '',$customer_extra_info['billing_last_name'][0]):'',
										'billing_email' => isset($customer_extra_info['billing_email']) ? $customer_extra_info['billing_email'][0]:'',
										'billing_phone' => isset($customer_extra_info['billing_phone']) ? $customer_extra_info['billing_phone'][0]:'',
										'billing_company' => isset($customer_extra_info['billing_company']) ? str_replace($bad_chars, '', $customer_extra_info['billing_company'][0]):'',
										'vat_number' => $vat_number ? $vat_number :'',
										'billing_address_1' => isset($customer_extra_info['billing_address_1']) ? str_replace($bad_chars, '', $customer_extra_info['billing_address_1'][0]):'',
										'billing_address_2' => isset($customer_extra_info['billing_address_2']) ? str_replace($bad_chars, '', $customer_extra_info['billing_address_2'][0]):'',
										'billing_postcode' => isset($customer_extra_info['billing_postcode']) ? $customer_extra_info['billing_postcode'][0]:'',
										'billing_city' => isset($customer_extra_info['billing_city']) ? str_replace($bad_chars, '',$customer_extra_info['billing_city'][0]):'',
										'billing_state' => isset($customer_extra_info['billing_state']) ? str_replace($bad_chars, '',$customer_extra_info['billing_state'][0]):'',
										'billing_country' => isset($customer_extra_info['billing_country']) ? str_replace($bad_chars, '',$customer_extra_info['billing_country'][0]):'',
										
										'shipping_name' => isset($customer_extra_info['shipping_first_name']) ? str_replace($bad_chars, '', $customer_extra_info['shipping_first_name'][0]):'',
										'shipping_surname' => isset($customer_extra_info['shipping_last_name']) ? str_replace($bad_chars, '', $customer_extra_info['shipping_last_name'][0]):'',
										'shipping_company' => isset($customer_extra_info['shipping_company']) ? str_replace($bad_chars, '', $customer_extra_info['shipping_company'][0]):'',
										'shipping_address_1' => isset($customer_extra_info['shipping_address_1']) ? str_replace($bad_chars, '', $customer_extra_info['shipping_address_1'][0]):'',
										'shipping_address_2' => isset($customer_extra_info['shipping_address_2']) ? str_replace($bad_chars, '', $customer_extra_info['shipping_address_2'][0]):'',
										'shipping_postcode' => isset($customer_extra_info['shipping_postcode']) ? $customer_extra_info['shipping_postcode'][0]:'',
										'shipping_city' => isset($customer_extra_info['shipping_city']) ? str_replace($bad_chars, '',$customer_extra_info['shipping_city'][0] ):'',
										'shipping_state' => isset($customer_extra_info['shipping_state']) ? str_replace($bad_chars, '',$customer_extra_info['shipping_state'][0] ):'',
										'shipping_country' => isset($customer_extra_info['shipping_country']) ? str_replace($bad_chars, '',$customer_extra_info['shipping_country'][0] ):''
										);
										
			if( WCCM_Options::wpuef_include_fields_on_csv_export() && !empty($wpuef_column_titles_and_ids))
			{
				foreach($wpuef_column_titles_and_ids as $wpuef_colum)
					$temp_user_data[$wpuef_colum['title']] = '"'.$wccm_customer_model->get_wpuef_field_content($customer->ID, $wpuef_colum['id']).'"';
			}
			array_push($data, $temp_user_data);
		}
		echo json_encode($data);
		wp_die();
	}
	public function render_page()
	{
		global $wccm_customer_model;
		$wpuef_column_titles = $wccm_customer_model->get_wpuef_field_names();
		
		wp_enqueue_script (  'jquery-ui-core'  ) ;
		wp_enqueue_script( 'jquery-ui-progressbar' );
		//wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		wp_enqueue_style('jquery-style', WCCM_PLUGIN_PATH.'/css/jquery-ui.css');
		wp_enqueue_style('wccm-common', WCCM_PLUGIN_PATH.'/css/common.css');  
		wp_enqueue_style('customer-export-css', WCCM_PLUGIN_PATH.'/css/customers-export.css'); 
		wp_enqueue_script('csv-jquery-lib', WCCM_PLUGIN_PATH.'/js/jquery.csv-0.71.min.js'); 
		wp_enqueue_script('ajax-csv-export', WCCM_PLUGIN_PATH.'/js/admin-export-ajax.js'); 
		$hide_not_purchasing_guest_customers = WCCM_Options::get_option('hide_not_purchasing_guest_customers');
		?>
		<script>
			var ids_to_export = "<?php  if(isset($_REQUEST['wccm_customers_ids'])) echo $_REQUEST['wccm_customers_ids']; ?>";
			var ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";
			var hide_not_purchasing_guest_customers = "<?php echo $hide_not_purchasing_guest_customers ?>";
			var wpuef_addition_columns = [<?php if( WCCM_Options::wpuef_include_fields_on_csv_export() && !empty($wpuef_column_titles)) echo '"\"'.implode('\"","\"',$wpuef_column_titles).'\""'; else echo "" ?>];
		</script>
		<div id="wpbody">
		
		
			<h2 id="add-new-user"> 
						<?php  _e('Export full customers list', 'woocommerce-customers-manager');?> 
			</h2>
			
			<?php if ( isset($this->errors) && count($this->errors) > 0):  ?>
			<div class="error">
					<ul>
				<?php foreach($this->errors as $error)
				
			 if ( is_wp_error($error) ) : ?>
				
					<?php
						foreach ( $error->get_error_messages() as $err )
							echo "<li>$err</li>\n";
					endif; ?>
					</ul>
				</div>
			<?php endif;

			if ( ! empty( $this->messages ) ) {
				foreach ( $this->messages as $msg )
					echo '<div id="message" class="updated"><p>' . $msg . '</p></div>';
			} ?>

			<?php if ( isset($add_user_errors) && is_wp_error( $add_user_errors ) ) : ?>
				<div class="error">
					<?php
						foreach ( $add_user_errors->get_error_messages() as $message )
							echo "<p>$message</p>";
					?>
				</div>
			<?php endif; ?>
			
			
			<div tabindex="0" aria-label="Main content" id="import-box-content">
			<p><?php  _e('To export single or group of customer(s), you can do it directly from "Customers" menu voice by selecting customers and then click on "Download selected customer CSV" button. By this section you can instead export the <strong>full customer CSV list</strong>.', 'woocommerce-customers-manager');?></p>
				<div id="upload-istruction-box">
				<div style="display:block; height:50px; width:200px; "></div>
					<form method="post" enctype="multipart/form-data">						
						<h4><?php  _e('Would you export guest customers too? (This may cause export process to freeze)', 'woocommerce-customers-manager');?></h4>
						<select id="export-guest-user-select-box">
						  <option value="no">No</option>
						  <option value="yes">Yes</option>
						</select>
						<br/><br/>
						<input class="button-primary" type="submit" id="export-start-button" value="<?php  _e('Generate csv', 'woocommerce-customers-manager');?>" name="submit" accept=".csv"></input>
					</form>
				</div>
				<h3 id="ajax-progress-title"><?php  _e('Export Progress', 'woocommerce-customers-manager');?></h3>
				<div id="ajax-progress"></div>
				<div id="progressbar"></div>
				<h3 id="ajax-response-title"><?php  _e('Export Result', 'woocommerce-customers-manager');?></h3>				
				<div id="ajax-response"></div>
				
				<div class="clear"></div>
			</div><!-- wpbody-content -->
			<div class="clear"></div>
			<?php
		}
}
?>