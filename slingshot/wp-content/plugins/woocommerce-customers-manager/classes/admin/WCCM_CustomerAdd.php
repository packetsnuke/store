<?php 
class WCCM_CustomerAdd
{

	var $error;
	var $messages;
	var $edit_mode;
	 public function __construct($edit_mode = false)
	 {
		$this->messages = array();
		$this->edit_mode = $edit_mode;
	 }
	 
	 private function get_user_data($id)
	 {
		 $result = array();
		 $customer_info = get_userdata($id );
		 $customer_extra_info = get_user_meta($id);
		 
		/*  var_dump( $customer_info);
		 echo "<br/><br/>";
		 var_dump( $customer_extra_info); */
		 
		 $result['user_login'] = $customer_info->user_login;
		 $result['first_name'] = $customer_info->first_name;
		 $result['last_name'] = $customer_info->last_name;
		 //$result['pass1'];
		 $result['email'] = $customer_info->user_email;
		 $result['ID'] = $id;
		 //wccm_var_dump($customer_info);
		 $result['roles'] = $customer_info->roles;
		 
		 /* foreach($customer_extra_info as $key => $value)
			 $result[$key] = $value[0]; */
		 //var_dump($customer_extra_info);	 
		 if(isset($customer_extra_info['billing_first_name']))
			$result['billing_first_name'] = str_replace('"', "'", $customer_extra_info['billing_first_name'][0]);
		 if(isset($customer_extra_info['billing_last_name']))
			$result['billing_last_name'] = str_replace('"', "'", $customer_extra_info['billing_last_name'][0]);
		 if(isset($customer_extra_info['billing_phone']))
			$result['billing_phone'] = $customer_extra_info['billing_phone'][0]; 
		if(isset($customer_extra_info['billing_email']))
			$result['billing_email'] = $customer_extra_info['billing_email'][0];
		 if(isset($customer_extra_info['billing_company']))
			$result['billing_company'] = str_replace('"', "'", $customer_extra_info['billing_company'][0]);
		if(isset($customer_extra_info['billing_eu_vat']))
			$result['billing_eu_vat'] = str_replace('"', "'", $customer_extra_info['billing_eu_vat'][0]);
		 if(isset($customer_extra_info['billing_address_1']))
			$result['billing_address_1'] = str_replace('"', "'",$customer_extra_info['billing_address_1'][0]);
		 if(isset($customer_extra_info['billing_address_2']))
			$result['billing_address_2'] =str_replace('"', "'", $customer_extra_info['billing_address_2'][0]);
		 if(isset($customer_extra_info['billing_postcode']))
			$result['billing_postcode'] = $customer_extra_info['billing_postcode'][0];
		 if(isset($customer_extra_info['billing_city']))
			$result['billing_city'] = $customer_extra_info['billing_city'][0];
		
		$result['billing_state'] = isset($customer_extra_info['billing_state']) ? $customer_extra_info['billing_state'][0] : "";
		$result['billing_country'] = isset($customer_extra_info['billing_state']) ? $customer_extra_info['billing_country'][0] : "";
		
		if(isset($customer_extra_info['shipping_first_name']))
			$result['shipping_first_name'] = str_replace('"', "'", $customer_extra_info['shipping_first_name'][0]);
		if(isset($customer_extra_info['shipping_last_name']))
			$result['shipping_last_name'] = str_replace('"', "'", $customer_extra_info['shipping_last_name'][0]);
		 if(isset($customer_extra_info['shipping_phone']))
			$result['shipping_phone'] = $customer_extra_info['shipping_phone'][0];
		 if(isset($customer_extra_info['shipping_company']))
			$result['shipping_company'] = str_replace('"', "'", $customer_extra_info['shipping_company'][0]); 
		 if(isset($customer_extra_info['shipping_address_1']))
			$result['shipping_address_1'] = str_replace('"', "'", $customer_extra_info['shipping_address_1'][0]);
		 if(isset($customer_extra_info['shipping_address_2']))
			$result['shipping_address_2'] = str_replace('"', "'", $customer_extra_info['shipping_address_2'][0]);
		 if(isset($customer_extra_info['shipping_postcode']))
			$result['shipping_postcode'] = $customer_extra_info['shipping_postcode'][0];
		 if(isset($customer_extra_info['shipping_city']))
			$result['shipping_city'] = $customer_extra_info['shipping_city'][0];
		
		 $result['shipping_country'] = isset($customer_extra_info['shipping_country']) ? $customer_extra_info['shipping_country'][0] : ""; 
		 $result['shipping_state'] = isset($customer_extra_info['shipping_country']) ? $customer_extra_info['shipping_state'][0] : "";
		 
		 return $result;
	 }
	 private function check_and_send_notification_email()
	 {
		 //Notification email
		if(isset($_POST['wccm-send-notification-email']) && $_POST['wccm-send-notification-email'] == 'yes')
		{
			$mail = new WCCM_Email();
			$subject = __('New account', 'woocommerce-customers-manager');
			$text = sprintf (__('New account has been created. Login using the following credentials.<br/>User: %s<br/>Password: %s<br/>', 'woocommerce-customers-manager'), $_REQUEST['user_login'],$_REQUEST['pass1']);
			$mail->trigger($_REQUEST[ 'email' ], $subject,$text);
		}
	 }
	 private function update_user()
	 {
		 $this->error = null;
		 $mail_exists_id = email_exists($_REQUEST[ 'email' ]);
		//var_dump($mail_exists_id." ".$_REQUEST[ 'ID' ]);
		//return;
		$user_id = 0;
		if($_REQUEST[ 'pass1' ] != $_REQUEST[ 'pass2' ])
			$this->error = new WP_Error('empty', __('Password mismatch', 'woocommerce-customers-manager'));
		else if(isset($mail_exists_id) && $mail_exists_id != false && $mail_exists_id != $_REQUEST[ 'ID' ])
			$this->error = new WP_Error('empty', __('Mail already taken', 'woocommerce-customers-manager'));
		else
		{
			
			$args = array( 'ID' => $_REQUEST[ 'ID' ], 
						  'user_email' => $_REQUEST[ 'email' ],
						  'first_name' => isset($_REQUEST['first_name']) ? $_REQUEST['first_name']:"",
					      'last_name' => isset($_REQUEST['last_name']) ? $_REQUEST['last_name']:"");
			
			$user_id = wp_update_user($args );
			
			if(!empty($_REQUEST[ 'pass1' ]))
				wp_set_password( $_REQUEST[ 'pass1' ], $_REQUEST[ 'ID' ]);
		
			if(is_wp_error( $user_id ))
			{
				 $this->error = $result->get_error_message();
				 return;
			}
			//metadata
			$this->updata_user_meta($user_id, $_REQUEST);
			
			$this->messages = array(0 => __('Customer updated!', 'woocommerce-customers-manager'));
		}
		return $user_id;
	 }
	 private function add_user()
	 {
		 $this->error = null;
		 $user_id = null;
		//var_dump($_REQUEST);
		if(empty($_REQUEST[ 'pass1' ]) || empty($_REQUEST[ 'pass2' ]) || empty($_REQUEST[ 'email' ]))
			$this->error = new WP_Error('empty', __('Email and password fields cannot be empty', 'woocommerce-customers-manager'));
		else if($_REQUEST[ 'pass1' ] != $_REQUEST[ 'pass2' ])
			$this->error = new WP_Error('empty', __('Password mismatch', 'woocommerce-customers-manager'));
		else
		{
			$user_email = isset($_REQUEST[ 'email' ]) ? $_REQUEST[ 'email' ] : "";
			$user_id = username_exists( $_REQUEST[ 'user_login' ] );
			if ( !$user_id and email_exists($user_email) == false) 
			{
				//$user_id = wp_create_user( $_REQUEST[ 'user_login' ], $_REQUEST[ 'pass1' ], $_REQUEST[ 'email' ]);
				
				$userdata = array(
					'user_login'  =>  $_REQUEST[ 'user_login' ],
					'user_pass'   =>  $_REQUEST[ 'pass1' ],
					'user_email' => $_REQUEST[ 'email' ],
					'first_name' => isset($_REQUEST['first_name']) ? $_REQUEST['first_name']:"",
					'last_name' => isset($_REQUEST['last_name']) ? $_REQUEST['last_name']:""/* ,
					'role' => isset($_REQUEST['role']) ? $_REQUEST['role'] : 'customer' */
				);
				$user_id = wp_insert_user($userdata);
				wp_set_password( $_REQUEST[ 'pass1' ], $user_id);
				$this->check_and_send_notification_email();
				
				//metadata
				$this->updata_user_meta($user_id, $_REQUEST);
			}
			else
			{
				$this->error = new WP_Error('password', __('User mail already registered', 'woocommerce-customers-manager'));
			}
			if(!isset($this->error))
			{
				if(is_wp_error( $user_id ))
				{
						$this->error = $user_id;
						$user_id = null;
				}
				else
					$this->messages = array(0 => __('Customer added!', 'woocommerce-customers-manager'));
			}
		}
		
		return $user_id;
	 }
	 
     private function updata_user_meta($user_id, $data_source)
	 {
		  global $wpdb, $wccm_customer_model;
		/*  global $wccm_customer_model;
		 if(isset($data_source['roles']))
			$wccm_customer_model->change_user_role($user_id, $data_source['roles']); */
		//wccm_var_dump($data_source['roles']);	
	    $data_source['roles'] = !isset($data_source['roles']) ? 'customer' : $data_source['roles'];
		$roles_temp = array();
		if(!is_array($data_source['roles']))
			$roles_temp[$data_source['roles']] = true;
		else
			foreach($data_source['roles'] as $role_code)
				$roles_temp[$role_code] = true;
		
		//wccm_var_dump($data_source['last_name']);
		//wp_update_user(array( 'ID' => $user_id, 'role' => $data_source['roles'] ) );
		$wccm_customer_model->update_user_meta( $user_id, $wpdb->prefix.'capabilities', $roles_temp );
		$wccm_customer_model->update_user_meta( $user_id, 'billing_first_name', $data_source['billing_first_name'] );
		$wccm_customer_model->update_user_meta( $user_id, 'billing_last_name', $data_source['billing_last_name'] );
		$wccm_customer_model->update_user_meta( $user_id, 'billing_email', $data_source['billing_email'] );
		$wccm_customer_model->update_user_meta( $user_id, 'billing_phone', $data_source['billing_phone'] );
		$wccm_customer_model->update_user_meta( $user_id, 'billing_company', $data_source['billing_company'] );
		if(isset($data_source['billing_eu_vat']))
			$wccm_customer_model->update_user_meta( $user_id, 'billing_eu_vat', $data_source['billing_eu_vat'] );
		$wccm_customer_model->update_user_meta( $user_id, 'billing_address_1', $data_source['billing_address_1'] );
		$wccm_customer_model->update_user_meta( $user_id, 'billing_address_2', $data_source['billing_address_2'] );
		$wccm_customer_model->update_user_meta( $user_id, 'billing_postcode', $data_source['billing_postcode'] );
		$wccm_customer_model->update_user_meta( $user_id, 'billing_city', $data_source['billing_city'] );
		$wccm_customer_model->update_user_meta( $user_id, 'billing_state', $data_source['billing_state'] );
		$wccm_customer_model->update_user_meta( $user_id, 'billing_country', $data_source['billing_country'] );
		
		if(isset($data_source['shipping_as_billing']) && $data_source['shipping_as_billing'] == 'yes')	
		{
			$data_source['shipping_first_name'] = $data_source['billing_first_name'];
			$data_source['shipping_last_name'] = $data_source['billing_last_name'];
			$data_source['shipping_phone'] = $data_source['billing_phone'];
			$data_source['shipping_company'] = $data_source['billing_company'];
			$data_source['shipping_address_1'] =  $data_source['billing_address_1'];
			$data_source['shipping_address_2'] =  $data_source['billing_address_2'];
			$data_source['shipping_postcode'] =  $data_source['billing_postcode'];
			$data_source['shipping_city'] = $data_source['billing_city'];
			$data_source['shipping_state'] = $data_source['billing_state'];
			$data_source['shipping_country'] = $data_source['billing_country'];
		}
		$wccm_customer_model->update_user_meta( $user_id, 'shipping_first_name', $data_source['shipping_first_name'] );
		$wccm_customer_model->update_user_meta( $user_id, 'shipping_last_name', $data_source['shipping_last_name'] );
		$wccm_customer_model->update_user_meta( $user_id, 'shipping_phone', $data_source['shipping_phone'] );
		$wccm_customer_model->update_user_meta( $user_id, 'shipping_company', $data_source['shipping_company'] );
		$wccm_customer_model->update_user_meta( $user_id, 'shipping_address_1', $data_source['shipping_address_1'] );
		$wccm_customer_model->update_user_meta( $user_id, 'shipping_address_2', $data_source['shipping_address_2'] );
		$wccm_customer_model->update_user_meta( $user_id, 'shipping_postcode', $data_source['shipping_postcode'] );
		$wccm_customer_model->update_user_meta( $user_id, 'shipping_city', $data_source['shipping_city'] );
		$wccm_customer_model->update_user_meta( $user_id, 'shipping_state', $data_source['shipping_state'] );
		$wccm_customer_model->update_user_meta( $user_id, 'shipping_country', $data_source['shipping_country'] );
		
		if(WCCM_Options::get_option('actions_profile_update', false))
		{
			$old_user_data = get_userdata( $user_id );
			do_action('profile_update', $user_id, $old_user_data);
		}
	 }
	 
	 public function render_page()
	 {
		global $wccm_customer_model, $wpuef_htmlHelper, $wpuef_user_model, $wccm_html_model, $wccm_order_model;
		$countries = new WC_Countries();
		//var_dump(get_class_methods($countries));
		//var_dump($countries->country_dropdown_options());
		//var_dump($countries->get_countries());
		//var_dump($countries->get_states());
		//var_dump($countries->get_allowed_countries());
		
		if(!$this->edit_mode)
		{
			$data_source = $_REQUEST;
			if(isset($data_source['user_login']) == true)
			{
				$user_id = $this->add_user();
				if(isset($_POST['wccm_order_to_assign']) && isset($user_id)) //Assign order to user save process
				{
					$order_ids = $_POST['wccm_order_to_assign'];
					$additional_params = array('overwrite_billing_data' => isset($_POST['wccm_order_to_assign_overwrite_billing_data']), 'overwrite_shipping_data' => isset($_POST['wccm_order_to_assign_overwrite_shipping_data']));
				
					$wccm_order_model->assign_users($order_ids, $user_id, $additional_params);
				}
			}
		}
		else
		{
			if(isset($_REQUEST['user_login']) == true)
			{
				$user_id = $this->update_user($_REQUEST['customer']);
				$data_source = $user_id > 0 ? $this->get_user_data($user_id ) : $_REQUEST;//$_REQUEST;
			}
			else
				$data_source = $this->get_user_data($_REQUEST['customer']);
		}
		
		//wp_enqueue_script( 'password-strength-meter' );
		//wp_enqueue_script('password-strength-meter', admin_url() . '/js/password-strength-meter.min.js', array( 'jquery'), null, false );
		wp_enqueue_style('wccm-common', WCCM_PLUGIN_PATH.'/css/common.css');   
		wp_enqueue_style('customer-add-css', WCCM_PLUGIN_PATH.'/css/customer-add.css'); 
		wp_enqueue_style( 'wccm-select2-style',  WCCM_PLUGIN_PATH.'/css/select2.min.css'); 
		?>
		<!-- <script type="text/javascript" src="<?php echo admin_url(); ?>/js/password-strength-meter.js"></script> -->
		<script>
			jQuery.fn.select2=null;
		</script>
		<script type='text/javascript' src='<?php echo WCCM_PLUGIN_PATH.'/js/select2.min.js'; ?>'></script>
		<div id="wpbody">
		
			<div tabindex="0" aria-label="Main content" id="wpbody-content">
					
							
					
			<h2 id="add-new-user"> 
			<?php  if($this->edit_mode)
						_e('Edit customer', 'woocommerce-customers-manager');
					else
						_e('Add new customer', 'woocommerce-customers-manager');
			?> 
			
			<?php $url =  get_admin_url()."?page=woocommerce-customers-manager";
			if($this->edit_mode && isset($_GET['back']))
			{
				$url =  get_admin_url()."?page=woocommerce-customers-manager&customer=".$_GET['customer']."&action=customer_details";
			}
			?>
			<small class="wc-admin-breadcrumb"><a href="<?php echo $url; ?>" title="<?php _e('Go back', 'woocommerce-customers-manager') ?>">
				<img draggable="false" class="emoji" alt="Back" src="https://s.w.org/images/core/emoji/2/svg/2934.svg"></a>
			</small>
			</h2>
			<?php if ( isset($this->error) && is_wp_error( $this->error) ) : ?>
				<div class="error">
					<ul>
					<?php
						foreach ( $this->error->get_error_messages() as $err )
							echo "<li>$err</li>\n";
					?>
					</ul>
				</div>
			<?php endif;

			if ( ! empty( $this->messages ) ) {
				echo '<div id="message" class="updated"><ul>';
					foreach ( $this->messages as $msg )
						echo '<li>' . $msg . '</li>';
				echo '</ul></div>';
			} ?>

			<?php if ( isset($add_user_errors) && is_wp_error( $add_user_errors ) ) : ?>
				<div class="error">
					<?php
						foreach ( $add_user_errors->get_error_messages() as $message )
							echo "<p>$message</p>";
					?>
				</div>
			<?php endif; ?>
			<div id="ajax-response"></div>

			<form class="validate" id="createuser" name="createuser" method="post" action="">
			<!-- <input type="hidden" value="createuser" name="action">-->
			<input type="hidden" value="<?php //echo $_REQUEST['customer'] ?>" name="customer">
			<input type="hidden" value="<?php echo $data_source[ 'ID' ] ?>" name="ID">
			<?php if($this->edit_mode):?>
				<input type="hidden" value="<?php echo $data_source[ 'user_login' ] ?>" name="user_login">
			<?php endif; ?>
			
			<table class="form-table">
				<tbody>
				<tr class="form-field form-required">
					<th scope="row"><label for="user_login">Username <span class="description">(<span class="field_required">required</span>)</span></label></th>
					<td><input required="required" type="text" aria-required="true" value="<?php if(isset($data_source[ 'user_login' ])) echo $data_source[ 'user_login' ];?>" id="user_login" name="user_login" <?php if($this->edit_mode) echo 'disabled';?>></td>
				</tr>
				<tr class="form-field form-required">
					<th scope="row"><label for="email">E-mail <span class="description ">(<span class="field_required">required</span>)</span></label></th>
					<td><input required="required" type="text" value="<?php if(isset($data_source[ 'email' ])) echo $data_source[ 'email' ];?>" id="email" name="email"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="first_name"><?php _e('First name', 'woocommerce-customers-manager'); ?> </label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'first_name' ])) echo $data_source[ 'first_name' ];?>" id="first_name" name="first_name"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="last_name"><?php _e('Last name', 'woocommerce-customers-manager'); ?> </label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'last_name' ])) echo $data_source[ 'last_name' ];?>" id="last_name" name="last_name"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="pass1">Password <span class="description">(<?php if($this->edit_mode == true) _e('Leaving it blank will leave password unchanged', 'woocommerce-customers-manager'); else _e('Required', 'woocommerce-customers-manager');  ?>)</span></label></th>
					<td>
						<input value=" " class="hidden"><!-- #24364 workaround -->
						<input type="password" autocomplete="off" id="pass1" name="pass1" value="">
					</td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="pass2"><?php _e('Repeat Password', 'woocommerce-customers-manager'); if(!$this->edit_mode) echo '<span class="description"> (Required)</span>' ?> </label></th>
					<td>
					<input type="password" autocomplete="off" id="pass2" name="pass2" value="">
					<!--<br>
					<div id="pass-strength-result" style="display: block;"><?php _e('Strength indicator', 'woocommerce-customers-manager'); ?> </div>
					<p class="description indicator-hint"><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers, and symbols like ! " ? $ % ^ &amp; ).', 'woocommerce-customers-manager'); ?></p>
					-->
					</td> 
				</tr>
				<!-- <tr>
					<th scope="row"><label for="send_password">Send Password?</label></th>
					<td><label for="send_password"><input type="checkbox" value="1" id="send_password" name="send_password"> Send this password to the new user by email.</label></td>
				</tr> -->
				<?php
					/* billing_country, billing_first_name, billing_last_name, billing_company, billing_address_1, billing_address_2, billing_city
					   billing_state, billing_postcode, billing_email, billing_phone */ ?>					
				<tr class="form-field">
					<th scope="row"><label for="roles"><?php _e('Roles', 'woocommerce-customers-manager'); ?> <span class="description ">(<span class="field_required">required</span>)</span></label></th>
					<td>
					<select name="roles[]" class="js-role-select"  multiple="multiple" required="required">
					<?php 
							global $wp_roles;
							//$first_time = !isset($options['allowed_roles']) ? true:false;
							foreach( $wp_roles->roles as $role_code => $role_data)
							{
								$selected = '';		
								if($role_code != 'administrator')
								{
									if(!$this->edit_mode && $role_code == "customer")
										$selected = ' selected="selected" ';
									else if(isset($data_source[ 'roles' ]))
										 foreach($data_source[ 'roles' ] as $user_role)
											 if( $user_role == $role_code)
														$selected = ' selected="selected" ';
											
									echo '<option value="'.$role_code.'" '.$selected.'>'.$role_data['name'].'</option>';
								}
							}
							//echo '<option value="translate" '.$selected.'>Translate</option>';
						?>
						</select>
					</td>
				</tr>
				
				<tr class="form-field">
					<th scope="row"><label for="billing_first_name"><?php _e('Billing first name', 'woocommerce-customers-manager'); ?> </label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'billing_first_name' ])) echo $data_source[ 'billing_first_name' ];?>" id="billing_first_name" name="billing_first_name"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="billing_last_name"><?php _e('Billing last name', 'woocommerce-customers-manager'); ?> </label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'billing_last_name' ])) echo $data_source[ 'billing_last_name' ];?>" id="billing_last_name" name="billing_last_name"></td>
				</tr>
				
				<tr>
					<th  scope="row"><label for="billing_email"><?php _e('Billing email', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'billing_email' ])) echo $data_source[ 'billing_email' ];?>" id="billing_email" name="billing_email"></td>
				</tr>
				<tr>
					<th  scope="row"><label for="billing_phone"><?php _e('Billing phone', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'billing_phone' ])) echo $data_source[ 'billing_phone' ];?>" id="billing_phone" name="billing_phone"></td>
				</tr>
				<tr>
					<th scope="row"><label for="billing_company"><?php _e('Billing company', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'billing_company' ])) echo $data_source[ 'billing_company' ];?>" id="billing_company" name="billing_company"></td>
				</tr>
				
				<?php if($wccm_customer_model->is_vat_field_enabled()): ?>
					<tr class="form-field">
						<th scope="row"><label for="billing_eu_vat"><?php _e('Billing VAT number', 'woocommerce-customers-manager'); ?></label></th>
						<td><input type="text" value="<?php if(isset($data_source[ 'billing_eu_vat' ])) echo $data_source[ 'billing_eu_vat' ];?>" id="billing_eu_vat" name="billing_eu_vat"></td>
					</tr>
				<?php endif; ?>
				<tr class="form-field">
					<th scope="row"><label for="billing_address_1"><?php _e('Billing address', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'billing_address_1' ])) echo $data_source[ 'billing_address_1' ];?>" id="billing_address_1" name="billing_address_1"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="billing_address_2"><?php _e('Apartament, suite, unit, etc.', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'billing_address_2' ])) echo $data_source[ 'billing_address_2' ];?>" id="billing_address_2" name="billing_address_2"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="billing_postcode"><?php _e('Post code', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'billing_postcode' ])) echo $data_source[ 'billing_postcode' ];?>" id="billing_postcode" name="billing_postcode"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="billing_city"><?php _e('Billing city', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'billing_city' ])) echo $data_source[ 'billing_city' ];?>" id="billing_city" name="billing_city"></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="billing_country"><?php _e('Billing country', 'woocommerce-customers-manager'); ?></label></th>
					<td><!--<input type="text" id="billing_country" name="billing_country">-->
					<select id="billing_country" name="billing_country">
						<?php //$countries->country_dropdown_options(); ?>
					</select>

					</td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="billing_state"><?php _e('Billing state', 'woocommerce-customers-manager'); ?></label></th>
					<td id="billing_state_wrapper">
						<!-- <input type="text" id="billing_state" name="billing_state"> -->
					</td>
				</tr>
			
				<script>
				 var countries = {};
				 
				 <?php foreach($countries->get_allowed_countries() as $country_code => $country_name)
							echo 'countries["'.$country_code.'"]  = {name:"'.$country_name.'",states:{}};';
				 
				
						foreach($countries->get_allowed_countries() as $country_code => $country_name)
						{
						
							$states = $countries->get_states($country_code);
							if( $states)
								foreach ($countries->get_states($country_code) as $state_code => $state_name)
									echo 'countries["'.$country_code.'"]["states"]["'.$state_code.'"] = "'.$state_name.'";';
						}
				?>		
				jQuery(window).load(function()
				{
					var selected_shipping_country = "<?php if(isset($data_source[ 'shipping_country' ])) echo $data_source[ 'shipping_country' ];?>";
					var selected_billing_country = "<?php if(isset($data_source[ 'billing_country' ])) echo $data_source[ 'billing_country' ];?>";
					var selected_shipping_state = "<?php if(isset($data_source[ 'shipping_state' ])) echo $data_source[ 'shipping_state' ];?>";
					var selected_billing_state = "<?php if(isset($data_source[ 'billing_state' ])) echo $data_source[ 'billing_state' ];?>";
					var shipping_country_select_data = "";
					var billing_country_select_data = "";
					var state_select_data = "";
					var selected = ' selected ="selected" ';
					
					jQuery(".js-role-select").select2({'width':500});
					jQuery("#shipping_as_billing").click(wccm_toggle_shipping_info_visibility);
					
					var counter = 0;
					for(var country_code in countries)
						 if (typeof countries[country_code] !== 'function')
						 {
							/* if(selected_shipping_country == "")
								selected_shipping_country = country_code;
							if(selected_billing_country =="")
								selected_billing_country = country_code; */
							
							//None
							if(counter == 0)
								shipping_country_select_data += '<option value=""></option>';
							if(country_code == selected_shipping_country)
								shipping_country_select_data += '<option value="'+country_code+'" '+selected+'>'+countries[country_code].name+'</option>';
							else
								shipping_country_select_data += '<option value="'+country_code+'">'+countries[country_code].name+'</option>';
							
							if(counter == 0)
								billing_country_select_data += '<option value="" ></option>';
							if(country_code == selected_billing_country)
								billing_country_select_data += '<option value="'+country_code+'" '+selected+'>'+countries[country_code].name+'</option>';
							else
								billing_country_select_data += '<option value="'+country_code+'">'+countries[country_code].name+'</option>';
							
							counter++;
						 }
					jQuery('#billing_country').html(billing_country_select_data);
					jQuery('#shipping_country').html(shipping_country_select_data);
					create_stetes_input(selected_shipping_country, 'shipping_state', selected_shipping_state);
					create_stetes_input(selected_billing_country, 'billing_state', selected_billing_state);
					
					jQuery('#billing_country').change(function(event)
					{
						create_stetes_input(jQuery(event.target).val(), 'billing_state', null);
					});
					jQuery('#shipping_country').change(function(event)
					{
						create_stetes_input(jQuery(event.target).val(), 'shipping_state', null);
					});
					
					function wccm_toggle_shipping_info_visibility(event)
					{
						if(jQuery("#shipping_as_billing").prop('checked'))
							jQuery(".wccm-shipping-info").addClass("wccm_hide");
						else
							jQuery(".wccm-shipping-info").removeClass("wccm_hide");
					}
					function create_stetes_input(country_code, selector, selected_option)
					{
						state_select_data = "";
						if(typeof(countries[country_code]) !== 'undefined' && !isEmpty(countries[country_code].states))
						{
							for(var state_code in countries[country_code].states)
							{
								if(selected_option != null && selected_option == state_code)
									state_select_data += '<option value="'+state_code+'" '+selected+'>'+ countries[country_code].states[state_code]+'</option>';
								else
									state_select_data += '<option value="'+state_code+'">'+ countries[country_code].states[state_code]+'</option>';
							}
						}
						else
						{
							if(selected_option != null)
								jQuery('#'+selector+'_wrapper').html('<input type="text" id="'+selector+'" name="'+selector+'" value="'+selected_option+'">');
							else
								jQuery('#'+selector+'_wrapper').html('<input type="text" id="'+selector+'" name="'+selector+'">');
							return;
						}
						
						jQuery('#'+selector+'_wrapper').html('<select id="'+selector+'" name="'+selector+'">'+state_select_data+'</select>');
					
					}
				});
				function isEmpty(obj) {

						// null and undefined are "empty"
						if (obj == null) return true;

						// Assume if it has a length property with a non-zero value
						// that that property is correct.
						if (obj.length > 0)    return false;
						if (obj.length === 0)  return true;

						// Otherwise, does it have any properties of its own?
						// Note that this doesn't handle
						// toString and valueOf enumeration bugs in IE < 9
						for (var key in obj) {
							if (hasOwnProperty.call(obj, key)) return false;
						}

						return true;
					}
				</script>
				<?php 
				  /* shipping_country, shipping_first_name, shipping_last_name, shipping_company, shipping_address_1, shipping_address_2, shipping_city
					 shipping_state, shipping_postcode, shipping_country*/ ?>
				<tr>
					<th><h3><?php _e('Shipping info as Billing info?', 'woocommerce-customers-manager'); ?></h3></th>
					<td><input type="checkbox" name="shipping_as_billing" id="shipping_as_billing" value="yes"></input></td>
				</tr>
				<tr  class="wccm-shipping-info">
					<th><h3><?php _e('Shipping info', 'woocommerce-customers-manager'); ?></h3></th>
					<td></td>
				</tr>
				<tr  class="wccm-shipping-info">
					<th scope="row"><label for="shipping_first_name"><?php _e('Shipping first name', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'shipping_first_name' ])) echo $data_source[ 'shipping_first_name' ];?>" id="shipping_first_name" name="shipping_first_name"></td>
				</tr>
				<tr  class="wccm-shipping-info">
					<th scope="row"><label for="shipping_last_name"><?php _e('Shipping last name', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'shipping_last_name' ])) echo $data_source[ 'shipping_last_name' ];?>" id="shipping_last_name" name="shipping_last_name"></td>			
				</tr>				
				<tr  class="wccm-shipping-info">
					<th scope="row"><label for="billing_phone"><?php _e('Shipping phone', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'shipping_phone' ])) echo $data_source[ 'shipping_phone' ];?>" id="shipping_phone" name="shipping_phone"></td>
				</tr>
				<tr class="wccm-shipping-info">
					<th scope="row"><label for="shipping_company"><?php _e('Shipping company', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'shipping_company' ])) echo $data_source[ 'shipping_company' ];?>" id="shipping_company" name="shipping_company"></td>
				</tr>
				<tr class="form-field wccm-shipping-info">
					<th scope="row"><label for="shipping_address_1"><?php _e('Shipping address', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'shipping_address_1' ])) echo $data_source[ 'shipping_address_1' ];?>" id="shipping_address_1" name="shipping_address_1"></td>
				</tr>
				<tr class="form-field wccm-shipping-info">
					<th scope="row"><label for="shipping_address_2"><?php _e('Apartament, suite, unit, etc.', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'shipping_address_2' ])) echo $data_source[ 'shipping_address_2' ];?>" id="shipping_address_2" name="shipping_address_2"></td>
				</tr>
				<tr class="form-field wccm-shipping-info">
					<th scope="row"><label for="shipping_postcode"><?php _e('Post code', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'shipping_postcode' ])) echo $data_source[ 'shipping_postcode' ];?>" id="shipping_postcode" name="shipping_postcode"></td>
				</tr>
				<tr class="form-field wccm-shipping-info">
					<th scope="row"><label for="shipping_city"><?php _e('Shipping city', 'woocommerce-customers-manager'); ?></label></th>
					<td><input type="text" value="<?php if(isset($data_source[ 'shipping_city' ])) echo $data_source[ 'shipping_city' ];?>" id="shipping_city" name="shipping_city"></td>
				</tr>
				<tr class="form-field wccm-shipping-info">
					<th scope="row"><label for="shipping_country"><?php _e('Shipping country', 'woocommerce-customers-manager'); ?></label></th>
					<td><!-- <input type="text" id="shipping_country" name="shipping_country"> -->
					<select id="shipping_country" name="shipping_country">
						<?php //$countries->country_dropdown_options(); ?>
					</select>
					</td>
				</tr>
				<tr class="form-field wccm-shipping-info">
					<th scope="row"><label for="shipping_state"><?php _e('Shipping state', 'woocommerce-customers-manager'); ?></label></th>
					<td id="shipping_state_wrapper">
						<!-- <input type="text" id="shipping_state" name="shipping_state"> -->
					</td>
				</tr>
				
					<script>
				function checkPasswordStrength( $pass1,
                                $pass2,
                                $strengthResult,
                                $submitButton,
                                blacklistArray ) {
								var pass1 = $pass1.val();
								var pass2 = $pass2.val();
							 
								// Reset the form & meter
								$submitButton.attr( 'disabled', 'disabled' );
									$strengthResult.removeClass( 'short bad good strong' );
							 
								// Extend our blacklist array with those from the inputs & site data
								blacklistArray = blacklistArray.concat( wp.passwordStrength.userInputBlacklist() )
							 
								// Get the password strength
								var strength = wp.passwordStrength.meter( pass1, blacklistArray, pass2 );
							 
								// Add the strength meter results
								switch ( strength ) {
							 
									case 2:
										$strengthResult.addClass( 'bad' ).html( pwsL10n.bad );
										break;
							 
									case 3:
										$strengthResult.addClass( 'good' ).html( pwsL10n.good );
										break;
							 
									case 4:
										$strengthResult.addClass( 'strong' ).html( pwsL10n.strong );
										break;
							 
									case 5:
										$strengthResult.addClass( 'short' ).html( pwsL10n.mismatch );
										break;
							 
									default:
										$strengthResult.addClass( 'short' ).html( pwsL10n.short );
							 
								}
							 
								// The meter function returns a result even if pass2 is empty,
								// enable only the submit button if the password is strong and
								// both passwords are filled up
								if ( /* 2 === strength && */ '' !== pass2.trim() ) {
									$submitButton.removeAttr( 'disabled' );
								}
							 
								return strength;
							}
							 
							jQuery( document ).ready( function( $ ) {
								// Binding to trigger checkPasswordStrength
								/* $( 'body' ).on( 'keyup', 'input[name=pass1], input[name=pass2]',
									function( event ) {
										checkPasswordStrength(
											$('input[name=pass1]'),         // First password field
											$('input[name=pass2]'), 		// Second password field
											$('#pass-strength-result'),        // Strength meter
											$('input[type=submit]'),           // Submit button
											['black', 'listed', 'word']        // Blacklisted words
										);
									}
								); */
							});
				</script>
				
				<?php if(!$this->edit_mode): ?>
				<tr class="form-field wccm-shipping-info">
					<th scope="row"><label for=""><?php _e('Assign orders', 'woocommerce-customers-manager'); ?></label></th>
					<td id="">
						<?php $wccm_html_model->assign_orders_to_user_selector(); ?>
					</td>
				</tr>
				<?php endif; ?>
					
				<tr style="display:none;" class="form-field">
					<th scope="row"><label for="role">Role</label></th>
					<td ><select id="role" name="role">
							<option value="customer" selected="selected">Customer</option>
							<option value="shop_manager">Shop Manager</option>		
						</select>
					</td>
				</tr>
				</tbody>
				</table>
				
				<?php  
				   if(isset($wpuef_htmlHelper) && isset($wpuef_user_model)): 
				
					if(isset($_POST['wpuef_options']) && isset($data_source[ 'ID' ]) && method_exists($wpuef_user_model,'save_fields') )
						$wpuef_user_model->save_fields($_POST['wpuef_options'], $data_source[ 'ID' ] );
		
					if(!$this->edit_mode && method_exists($wpuef_htmlHelper,'render_register_form_extra_fields_wccm'))
						$wpuef_htmlHelper->render_register_form_extra_fields_wccm();
					else if( method_exists($wpuef_htmlHelper,'render_edit_table_with_extra_fields'))
						$wpuef_htmlHelper->render_edit_table_with_extra_fields($data_source[ 'ID' ]);
				endif; ?>
				
				<?php if(!$this->edit_mode): ?>
				<div class="email-notification-box">
					<h4><?php  _e('Send an email to customer with login info?', 'woocommerce-customers-manager');?></h4>
					<select name="wccm-send-notification-email">
					  <option value="no">No</option>
					  <option value="yes">Yes</option>
					</select>
				</div>
			<?php endif; ?>
			<p class="submit">
			
			<input type="submit" value="<?php 
					if(!$this->edit_mode)
						_e('Add new customer', 'woocommerce-customers-manager');
					else
						_e('Update customer', 'woocommerce-customers-manager');
				?>" class="button button-primary" id="createusersub" name="createuser"></p>
			</form>
			</div>
			<div class="clear"></div>
		</div><!-- wpbody-content -->
		<div class="clear"></div>
		 <?php
	 }
}
?>