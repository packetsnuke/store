<?php
class WCCM_CustomerImport
{
	var $errors;
	var $messages;
	public function __construct()
	{
		if ( is_admin() ) 
			add_action( 'wp_ajax_upload_csv', array( &$this, 'process_csv_upload_ajax' ) );
		
	}
	function process_csv_upload_ajax()
	{
		
		$csv_array = explode("<#>", $_POST['csv']);
		$this->process_uploaded_file($csv_array);
		
		 if ( isset($this->errors) && count($this->errors) > 0):  ?>
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
			} 
		wp_die();
	}
	private function process_uploaded_file($csv_array = null)
	{
		global $wccm_customer_model;
		$customerAdded = 0;
		$customerUpdated = 0;
		$this->errors = array();
		$this->messages = array();
		
		
		$columns_names = array("ID",
								"Name",
								"Surname",
								"Login",
								"Role",
								"Password",
								"Password hash",
								"Email",
								"Notes",
								"Billing name",
								"Billing surname",
								"Billing email",
								"Billing phone",
								"Billing company",
								"Billing address",
								"Billing address 2",
								"Billing postcode",
								"Billing city",
								"Billing state",
								"Billing country",
								"Shipping name",
								"Shipping surname",
								"Shipping phone",
								"Shipping company",
								"Shipping address",
								"Shipping address 2",
								"Shipping postcode",
								"Shipping city",
								"Shipping state",
								"Shipping country");
		$colum_index_to_name = array();
		
		
		/* if($imageFileType != "csv" ) 
		{
			array_push($this->errors , new WP_Error('empty', __('Sorry, only CSV files are allowed.', 'woocommerce-customers-manager'))); 
			$uploadOk = 0;
		}
		else  */
		{
			
			$row = 1;
			//colum detection
			//if (($handle = fopen($_FILES["fileToUpload"]["tmp_name"], "r")) !== FALSE) 
			if($csv_array != null)
			{
				//while (($data = fgetcsv($handle)) !== FALSE) 
				$wpuef_extra_fields = array();
				foreach($csv_array as $csv_row)
				{
					//wccm_var_dump($csv_row);
					$csv_row = str_replace('\"', '"', $csv_row);
					$data = str_getcsv($csv_row);
					$num = count($data);
					$user = array();
					
					//empty row
					if(empty($csv_row) || $csv_row == "" || $csv_row == '""')
						continue;
					/* if(!is_array($data) || count($data) < 2)
						continue; */
				
					for ($c=0; $c < $num; $c++) 
					{			
						if($row == 1)
						{
							foreach( $columns_names as $title)
								if($title == $data[$c])
										$colum_index_to_name[$c] = $title;
								elseif(strpos(strtolower($data[$c]), 'wpuef_') !== false)
								{
									$id = explode("_", $data[$c]);
									$wpuef_extra_fields[$c] = array('id' => $id[1], 'data'=>"");
								}
						}
						else
						{
							
							if(isset($colum_index_to_name[$c]))
							{
								//echo $c." ".$colum_index_to_name[$c].": ".$data[$c]."<br />\n";
								$user[$colum_index_to_name[$c]] = $data[$c];
							}
							elseif(isset($wpuef_extra_fields[$c]))
								$wpuef_extra_fields[$c]['data'] =  $data[$c];
						}
						
					}

					if($user != null)
					{
						$user['Email'] = isset($user[ 'Email' ]) && $user[ 'Email' ] !='' ? $user[ 'Email' ]:rand(10000000, 28000000)."@mail.com";
						$mail_exists_id = email_exists($user[ 'Email' ]);
						
						if(!isset($user[ 'Login' ]))
							$user[ 'Login' ] = rand (10000000,28000000);
						
						if( (!isset($user['Password']) || $user['Password'] == '') && ( !isset($user['Password hash']) || $user['Password hash'] == '' ))
							$user['Password'] = rand (10000000,28000000);
						
						$user_id = username_exists( $user[ 'Login' ] );
						$is_valid_id = isset($user[ 'ID' ]) ? is_numeric($user[ 'ID' ]) : true;
						
						//update if exists (not used)
						if($user_id != false)
						{
							if($mail_exists_id == false || ($mail_exists_id == $user_id))
							{
								//Update
								$user_id = wp_update_user( array( 'ID' => $user_id, 'user_email' => $user[ 'Email' ] ) );
								if(is_wp_error( $user_id ))
									array_push( $this->error, $result->get_error_message());
								else
									$wccm_customer_model->update_user_metas($user_id, $user);
								
								if(isset($user['Password']))
									wp_set_password( $user[ 'Password' ], $user_id); 
								
								//Role
								$roles = isset($user[ 'Role' ]) && $user[ 'Role' ] !='' ? $user[ 'Role' ]:'customer';
								/* $roles_temp = array();
								$roles_temp[$role] = true; */
								$wccm_customer_model->update_user_roles( $user_id, $roles);
								
								//wpuef
								$wccm_customer_model->bulk_update_wpuef_fields($user_id, $wpuef_extra_fields);
								$customerUpdated++;
								
							}
							else
								array_push($this->errors, new WP_Error('empty', __('Mail already taken for user: $user_id', 'woocommerce-customers-manager')));
						
						}						
						else
						{
							
							if ( $is_valid_id && !$user_id  and $mail_exists_id == false and ((isset($user['Password']) and $user['Password'] != '') || (isset($user['Password hash']) and $user['Password hash'] != ''))) 
							{
								
								$userdata = array(
								'user_login'  =>  $user[ 'Login' ],
								'user_pass'   =>  isset($user[ 'Password hash' ]) ? $user[ 'Password hash' ] : "none",
								'user_email' => $user[ 'Email' ],
								'first_name' => isset($user['Name']) ? $user['Name']:"",
								'last_name' => isset($user['Surname']) ? $user['Surname']:""/* ,
								'role' => $role */
								);
								/* if(isset($user[ 'ID' ]))
									$userdata['ID'] = $user[ 'ID' ]; */
								$user_id = $wccm_customer_model->wccm_custom_insert_user($userdata);
								
								if(isset($user['Password']))
									wp_set_password( $user[ 'Password' ], $user_id); 
								
								//Role
								$roles = isset($user[ 'Role' ]) && $user[ 'Role' ] !='' ? $user[ 'Role' ]:'customer';
								/* $roles_temp = array();
								$roles_temp[$role] = true; */
								$wccm_customer_model->update_user_roles( $user_id, $roles);
									
								
								//Notification email
								if(isset($_POST['send-notification-email']) && $_POST['send-notification-email'] == 'yes')
								{
									$mail = new WCCM_Email();
									$subject = __('New account', 'woocommerce-customers-manager');
									$text = sprintf (__('New account has been created. Login using the following credentials.<br/>User: %s<br/>Password: %s<br/>', 'woocommerce-customers-manager'), $user['Login'],$user['Password']);
									$mail->trigger($user[ 'Email' ], $subject,$text);
								}
								
								if(is_wp_error( $user_id ))
								{
									$this->error = array_push( $this->errors,$user_id);
								}
								else
								{
									$customerAdded++;
								
									//metadata
									$wccm_customer_model->update_user_metas($user_id, $user);
								}
								
								//wpuef import 
								$wccm_customer_model->bulk_update_wpuef_fields($user_id, $wpuef_extra_fields);
									
							}
							else
							{
								if($user_id)
									array_push( $this->errors, new WP_Error('user', sprintf(__("User %s already present.", 'woocommerce-customers-manager'), $user[ 'Login' ])));
								else if(!$is_valid_id)
									array_push( $this->errors, new WP_Error('user', sprintf(__("ID %s is not valid.", 'woocommerce-customers-manager'), isset($user[ 'ID' ]) ? $user[ 'ID' ] : "")));
								else if($mail_exists_id != false)
									array_push( $this->errors, new WP_Error('user', sprintf(__("Mail %s for user: %s already taken.", 'woocommerce-customers-manager'), $user[ 'Email' ], $user[ 'Login' ])));
								else //if(!isset($user['Password']) || $user['Password'] == '')
									array_push( $this->errors, new WP_Error('user', sprintf(__("Password for user %s is not valid.", 'woocommerce-customers-manager'), $user[ 'Login' ])));
								
							}
							
						}
					}
					$row++;
				}
				if($customerAdded > 0)
					array_push( $this->messages, sprintf(__('Added %d customers!', 'woocommerce-customers-manager'),  $customerAdded ));
				if($customerUpdated > 0)
					array_push( $this->messages, sprintf(__('Updated %d customers!', 'woocommerce-customers-manager'),  $customerUpdated ));
				//fclose($handle);
			}
		}
	}
	public function render_page()
	{
		/* if(isset($_POST["submit"])) 
			$this->process_uploaded_file(); */
		
		wp_enqueue_script (  'jquery-ui-core'  ) ;
		wp_enqueue_script( 'jquery-ui-progressbar' );
		//wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		wp_enqueue_style('jquery-style',  WCCM_PLUGIN_PATH.'/css/jquery-ui.css');
		wp_enqueue_style('wccm-common',  WCCM_PLUGIN_PATH.'/css/common.css');   
		wp_enqueue_style('customer-import-css',  WCCM_PLUGIN_PATH.'/css/customers-import.css'); 
		//wp_enqueue_script('csv-jquery-lib',  WCCM_PLUGIN_PATH.'/js/jquery.csv-0.71.min.js'); 
		wp_enqueue_script('csv-jquery-lib',  WCCM_PLUGIN_PATH.'/js/jquery.csv-0.81.js'); 
		wp_enqueue_script('ajax-csv-importer',  WCCM_PLUGIN_PATH.'/js/admin-import-ajax.js'); 
		?>
		<script>
			var ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";
		</script>
		<div id="wpbody">
		
		
			<h2 id="add-new-user"> 
						<?php  _e('Import customers', 'woocommerce-customers-manager');?> 
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
				<div id="upload-istruction-box">
					<p>
						<h3><?php _e('NOTE: The .csv file must use "," as field separator. Data will be imported only for columns with following titles (all columns can be left empty or omitted except for the Email):', 'woocommerce-customers-manager'); ?></h3>
						
					</p>
					<ul>
						<!-- <li>ID <span class="normal"><?php _e('(if not specified the plugin will generate a random ID)', 'woocommerce-customers-manager'); ?></span></li>-->
						<li>Name</li>
						<li>Surname </li>
						<li>Login <span class="normal"><?php _e('(if not specified the plugin will generate an automatic username. If the login <strong>already exists</strong> the associated user will be updated)', 'woocommerce-customers-manager'); ?></span></li> 
						<li>Role <span class="normal"><?php _e('(Example: "shop_manager","customer","subscriber",etc. If not specified the default value will be "customer". In case of multiple roles specify them separating by single space. Ex: "role_1 role_2 role_3")', 'woocommerce-customers-manager'); ?></span></li>
						<li>Password <span class="normal"><?php _e('(If not present the "Password hash" column will be used instead. If both columns are empty a random password will be generated)', 'woocommerce-customers-manager'); ?></span></li>
						<li>Password hash <span class="normal"><?php _e('(Used for importing already encrypted passwords. Usefull if you have a .csv export file generated with this plugin)', 'woocommerce-customers-manager'); ?></span></li>
						<li>Email <span class="normal"><?php _e('(This <strong>must</strong> be specified. If not, random one will be generated)', 'woocommerce-customers-manager'); ?></span></li>
						<li>Notes</li>
						<li>Billing name</li>
						<li>Billing surname</li>
						<li>Billing email</li>
						<li>Billing phone</li>
						<li>Billing company</li>
						<li>Billing address</li>
						<li>Billing address 2</li>
						<li>Billing postcode</li>
						<li>Billing city</li>
						<li>Billing country</li>
						<li>Billing state</li>
						<li>Shipping name</li>
						<li>Shipping surname</li>
						<li>Shipping phone</li>
						<li>Shipping company</li>
						<li>Shipping address</li>
						<li>Shipping address 2</li>
						<li>Shipping postcode</li>
						<li>Shipping city</li>
						<li>Shipping country</li>
						<li>Shipping state</li>
						<?php if(wccm_wpuef_plugin_installed()): ?>
						<li>wpuef_{id} <span class="normal"><?php _e('(To import wpuef user extra field the columns have to have the following format: wpuef_c13, where c13 is the field id. If you are importing a <i>Country & State</i> its content has to be in the format: "country,state" (with double quotes). For example: "United states,New York", or "Italy,Rome")', 'woocommerce-customers-manager'); ?></span></li>
						<?php endif; ?>
					<ul>
					<div style="display:block; height:25px; width:400px; "></div>
					<form class="import-form" method="post" enctype="multipart/form-data">
						<h4><?php  _e('Send an email to customer with login info? (This feature is not available if you import password using "Password Hash" column)', 'woocommerce-customers-manager');?></h4>
						<p>
							<select id="wccm-send-notification-email">
							  <option value="no">No</option>
							  <option value="yes">Yes</option>
							</select>
						</p>
						<p>
							<strong><?php  _e('Select .csv file to import', 'woocommerce-customers-manager');?> </strong>
							<input type="file" name="fileToUpload" id="fileToUpload"></input>
						</p>
						<p>
							<strong><?php  _e('NOTE', 'woocommerce-customers-manager');?>:</strong> <?php _e('You can use the follow .csv example as template to import data:', 'woocommerce-customers-manager'); ?> <a href="http://www.codecanyon.eu/images/WCCM/WCCM-template.csv"><?php _e('Example', 'woocommerce-customers-manager'); ?></a>
						</p>
						<input type="submit" class="button-primary" id="impor-submit-button" value="<?php  _e('Upload', 'woocommerce-customers-manager');?> " name="submit" accept=".csv"></input>
					</form>
				</div>
				<h3 id="ajax-progress-title"><?php  _e('Importing Progress', 'woocommerce-customers-manager');?></h3>
				<div id="ajax-progress"></div>
				<div id="progressbar"></div>
				<h3 id="ajax-response-title"><?php  _e('Importing Result', 'woocommerce-customers-manager');?></h3>				
				<div id="ajax-response"></div>
				
				<div class="clear"></div>
			</div><!-- wpbody-content -->
			<div class="clear"></div>
			<?php
	}
	
	
}
?>