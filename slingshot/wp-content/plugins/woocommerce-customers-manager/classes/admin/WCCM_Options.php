<?php 
class WCCM_Options
{
	public function __construct()
	{
	}
	static function get_option($option_name = null, $default = null)
	{
		$options = get_option( 'wccm_general_options');
		$options = isset($options) ? $options: null;
		
		$result = null;
		if($option_name)
		{
			if($option_name == 'allowed_roles')
			{
				$result = !isset($options[$option_name]) ?/*  array('customer','subscriber') */ null : $options[$option_name];
			}
			elseif($option_name == 'disable_order_total_spent_column_sort')
				$result = 'false';
			elseif($option_name == 'roles_relation')
				$result = !isset($options[$option_name]) ? 'or' : $options[$option_name];
			elseif($option_name == 'wpuef_include_fields_on_csv_export')
				$result = !isset($options[$option_name]) ? true : $options[$option_name];
			elseif($option_name == 'do_not_convert_if_email_is_already_associated')
				$result = isset($options[$option_name]) ? true : false;
			elseif($option_name == 'do_not_convert_if_existing_on_manual_conversion')
				$result = isset($options[$option_name]) ? true : false;
			elseif($option_name == 'disable_email_visual_editor')
				$result = !isset($options[$option_name]) ? 'false' : 'true';
			elseif(isset($options[$option_name]))
				$result = $options[$option_name];
			elseif(!isset($options[$option_name]) && isset($default))
				$result = $default;
			else 
				$result = 'false';
			
				
		}
		else
			$result = $options;

		return $result;
	}
	static function wpuef_include_fields_on_csv_export()
	{
		global $wccm_customer_model;
		$options = get_option( 'wccm_general_options');
		$wpuef_include_fields_on_csv_export = !$wccm_customer_model->has_customer_extra_wpuef_fields() || !isset($options['wpuef_include_fields_on_csv_export']) ? false : $options['wpuef_include_fields_on_csv_export'];
		return $wpuef_include_fields_on_csv_export;
	}
	public function render_page() 
	{
		$this->render_options_page();
	}
	private function render_options_page()
	{
		global $wp_roles,$wccm_customer_model,$wp_scripts;
		$options = get_option( 'wccm_general_options');
		$wpuef_include_fields_on_csv_export = WCCM_Options::wpuef_include_fields_on_csv_export();
		$wp_scripts->queue = array();	
		
		ob_start();
		wp_enqueue_style('wccm-common', WCCM_PLUGIN_PATH.'/css/common.css');  
		wp_enqueue_style( 'wccm-options-page', WCCM_PLUGIN_PATH.'/css/options-page.css'  );
		wp_enqueue_style( 'wccm-select2-style',  WCCM_PLUGIN_PATH.'/css/select2.min.css' ); 
		
		wp_enqueue_script( 'wccm-option-page', WCCM_PLUGIN_PATH.'/js/admin-options-page.js', array('jquery') );
		
		//wccm_var_dump($wccm_customer_model->get_user_role_list_for_sql_query());
		?>
		<script>
			jQuery.fn.select2=null;
		</script>
		<script type='text/javascript' src='<?php echo WCCM_PLUGIN_PATH.'/js/select2.min.js'; ?>'></script>
		<div class="wrap">
			<h2><?php _e('Options page', 'woocommerce-customers-manager');?></h2>
			<form method="post" action="options.php" method="post">
			<?php settings_fields('wccm_general_options_group'); ?> 
			<div id="options-container">
				<!--<h3><?php _e('General', 'woocommerce-customers-manager');?></h3>
				<p>
				<label><?php _e('Disable "Orders" and Total spent" column sorting. This should grant performace improvement for big customers databases', 'woocommerce-customers-manager');?></label>
				<input type="checkbox" name="wccm_general_options[disable_order_total_spent_column_sort]" value="true" <?php if(isset($options['disable_order_total_spent_column_sort'])) echo 'checked="checked"'; ?>></input>
				</p>-->
				
				<!-- <p>
				<label><?php _e('Hide "Total spent" column', 'woocommerce-customers-manager');?></label>
				<input type="checkbox" name="wccm_general_options[hide_total_spent_column]" value="true" <?php if(isset($options['hide_total_spent_column'])) echo 'checked="checked"'; ?>></input>
				</p> -->
				
				<h3><?php _e('Guest customer list & export pages', 'woocommerce-customers-manager');?></h3>
				<p>
					<label><?php _e('Hide guest customers who have not purchased anything (Customers who has only cancelled and/or refounded orders)', 'woocommerce-customers-manager');?></label>
					<input type="checkbox" name="wccm_general_options[hide_not_purchasing_guest_customers]" value="true" <?php if(isset($options['hide_not_purchasing_guest_customers'])) echo 'checked="checked"'; ?>></input>
				</p>
				<div class="spacer"></div>
				
				<h3><?php _e('Customer list page', 'woocommerce-customers-manager');?></h3>
				<p>
					<label><?php _e('Select which colum has to be used for default sorting', 'woocommerce-customers-manager');?></label>
					<select name="wccm_general_options[customer_list_default_sorting_column]">						
						<option value="users.user_registered" <?php if(!empty($options['customer_list_default_sorting_column']) && $options['customer_list_default_sorting_column'] =='users.user_registered') echo 'selected="selected"'; ?>><?php _e('Register date', 'woocommerce-customers-manager');?></option>
						<option value="users.ID" <?php if(!empty($options['customer_list_default_sorting_column']) && $options['customer_list_default_sorting_column'] =='users.ID') echo 'selected="selected"'; ?>><?php _e('ID', 'woocommerce-customers-manager');?></option>
						<option value="users.user_login" <?php if(!empty($options['customer_list_default_sorting_column']) && $options['customer_list_default_sorting_column'] =='users.user_login') echo 'selected="selected"'; ?>><?php _e('Login', 'woocommerce-customers-manager');?></option>
						<option value="usermeta_name.meta_value" <?php if(!empty($options['customer_list_default_sorting_column']) && $options['customer_list_default_sorting_column'] =='usermeta_name.meta_value') echo 'selected="selected"'; ?>><?php _e('Name', 'woocommerce-customers-manager');?></option>
						<option value="usermeta_surname.meta_value" <?php if(!empty($options['customer_list_default_sorting_column']) && $options['customer_list_default_sorting_column'] =='usermeta_surname.meta_value') echo 'selected="selected"'; ?>><?php _e('Surname', 'woocommerce-customers-manager');?></option>
						<option value="users.user_email" <?php if(!empty($options['customer_list_default_sorting_column']) && $options['customer_list_default_sorting_column'] =='users.user_email') echo 'selected="selected"'; ?>><?php _e('Email', 'woocommerce-customers-manager');?></option>
						<option value="count(DISTINCT posts.ID)" <?php if(!empty($options['customer_list_default_sorting_column']) && $options['customer_list_default_sorting_column'] =='count(DISTINCT posts.ID)') echo 'selected="selected"'; ?>><?php _e('Number of orders', 'woocommerce-customers-manager');?></option>
						<!-- <option value="posts.post_date" <?php if(!empty($options['customer_list_default_sorting_column']) && $options['customer_list_default_sorting_column'] =='posts.post_date') echo 'selected="selected"'; ?>><?php _e('Last order date', 'woocommerce-customers-manager');?></option> -->
					</select>
				</p>
				<p>
					<label><?php _e('Select sorting type', 'woocommerce-customers-manager');?></label>
					<select name="wccm_general_options[customer_list_sorting_type]">					
						<option value="desc" <?php if(!empty($options['customer_list_sorting_type']) && $options['customer_list_sorting_type'] =='desc') echo 'selected="selected"'; ?>><?php _e('Descendant', 'woocommerce-customers-manager');?></option>
						<option value="asc" <?php if(!empty($options['customer_list_sorting_type']) && $options['customer_list_sorting_type'] =='asc') echo 'selected="selected"'; ?>><?php _e('Ascendant', 'woocommerce-customers-manager');?></option>
					</select>
				</p>
				<p style="margin-top:30px;">
					<label><?php _e('Select which columns have to be hidden in the Customer table', 'woocommerce-customers-manager');?></label>
					<input type="checkbox" name="wccm_general_options[column_to_hide_in_customer_table][ID]" value="true" <?php if(isset($options['column_to_hide_in_customer_table']['ID'])) echo 'checked="checked"'; ?>><?php _e('ID','woocommerce-customers-manager'); ?></input>
					<input type="checkbox" name="wccm_general_options[column_to_hide_in_customer_table][login]" value="true" <?php if(isset($options['column_to_hide_in_customer_table']['login'])) echo 'checked="checked"'; ?>><?php _e('Login','woocommerce-customers-manager'); ?></input>
					<input type="checkbox" name="wccm_general_options[column_to_hide_in_customer_table][roles]" value="true" <?php if(isset($options['column_to_hide_in_customer_table']['roles'])) echo 'checked="checked"'; ?>><?php _e('Roles','woocommerce-customers-manager'); ?></input>
					<input type="checkbox" name="wccm_general_options[column_to_hide_in_customer_table][notes]" value="true" <?php if(isset($options['column_to_hide_in_customer_table']['notes'])) echo 'checked="checked"'; ?>><?php _e('Notes','woocommerce-customers-manager'); ?></input>
					<input type="checkbox" name="wccm_general_options[column_to_hide_in_customer_table][address]" value="true" <?php if(isset($options['column_to_hide_in_customer_table']['address'])) echo 'checked="checked"'; ?>><?php _e('Address (billing)','woocommerce-customers-manager'); ?></input>
					<input type="checkbox" name="wccm_general_options[column_to_hide_in_customer_table][phone]" value="true" <?php if(isset($options['column_to_hide_in_customer_table']['phone'])) echo 'checked="checked"'; ?>><?php _e('Phone','woocommerce-customers-manager'); ?></input>
					<input type="checkbox" name="wccm_general_options[column_to_hide_in_customer_table][email]" value="true" <?php if(isset($options['column_to_hide_in_customer_table']['email'])) echo 'checked="checked"'; ?>><?php _e('Email','woocommerce-customers-manager'); ?></input>
					<input type="checkbox" name="wccm_general_options[column_to_hide_in_customer_table][total_spent]" value="true" <?php if(isset($options['column_to_hide_in_customer_table']['total_spent'])) echo 'checked="checked"'; ?>><?php _e('#Orders','woocommerce-customers-manager'); ?></input>
					<input type="checkbox" name="wccm_general_options[column_to_hide_in_customer_table][orders]" value="true" <?php if(isset($options['column_to_hide_in_customer_table']['orders'])) echo 'checked="checked"'; ?>><?php _e('Total spent','woocommerce-customers-manager'); ?></input>
					<input type="checkbox" name="wccm_general_options[column_to_hide_in_customer_table][first_order_date]" value="true" <?php if(isset($options['column_to_hide_in_customer_table']['first_order_date'])) echo 'checked="checked"'; ?>><?php _e('First order date','woocommerce-customers-manager'); ?></input>
					<input type="checkbox" name="wccm_general_options[column_to_hide_in_customer_table][last_order_date]" value="true" <?php if(isset($options['column_to_hide_in_customer_table']['last_order_date'])) echo 'checked="checked"'; ?>><?php _e('Last order date','woocommerce-customers-manager'); ?></input>
					<input type="checkbox" name="wccm_general_options[column_to_hide_in_customer_table][registered]" value="true" <?php if(isset($options['column_to_hide_in_customer_table']['registered'])) echo 'checked="checked"'; ?>><?php _e('Registered','woocommerce-customers-manager'); ?></input>
					<input type="checkbox" name="wccm_general_options[column_to_hide_in_customer_table][orders_list]" value="true" <?php if(isset($options['column_to_hide_in_customer_table']['orders_list'])) echo 'checked="checked"'; ?>><?php _e('Order list','woocommerce-customers-manager'); ?></input>
				</p>
				<div class="spacer"></div>
				
				<h3><?php _e('Customer details page', 'woocommerce-customers-manager');?></h3>
				<p>
					<label><?php _e('Hide map');?></label>
					<input type="checkbox" name="wccm_general_options[hide_map_on_customer_detail_page]" value="true" <?php if(isset($options['hide_map_on_customer_detail_page'])) echo 'checked="checked"'; ?>></input>
				</p>
				<p>
					<label><?php _e('Map default zoom level','woocommerce-customers-manager');?></label>
					<input type="number" min=1 name="wccm_general_options[map_default_zoom_level]" value="<?php if(isset($options['map_default_zoom_level'])) echo $options['map_default_zoom_level']; else echo '8' ?>" ></input>
				</p>
				
				<div class="spacer"></div>
				<h3><?php _e('Email', 'woocommerce-customers-manager');?></h3>
				<p>
					<label><?php _e('Disable visual editor');?></label>
					<input type="checkbox" name="wccm_general_options[disable_email_visual_editor]" value="true" <?php if(isset($options['disable_email_visual_editor'])) echo 'checked="checked"'; ?>></input>
				</p>
				<p style="margin-top:30px;">
					<label><?php _e('Email sender name','woocommerce-customers-manager');?></label>
					<p><?php _e('Leave empty to use the Site name as sender');?></p>
					<input type="text" name="wccm_general_options[email_sender_name]" placeholder="<?php echo  __('Default:','woocommerce-customers-manager')." ".get_bloginfo('name'); ?>" value="<?php if(isset($options['email_sender_name'])) echo $options['email_sender_name']; ?>" ></input>
				</p>
				<p style="margin-top:30px;">
					<label><?php _e('Email sender email address','woocommerce-customers-manager');?></label>
					<p><?php _e('Leave empty to use the "noreply@yoursite.ext" as sender email address');?></p>
					<input type="text" name="wccm_general_options[email_sender_email]" placeholder="<?php echo __('Default:','woocommerce-customers-manager')." ".WCCM_Email::get_no_reply_address(); ?>" value="<?php if(isset($options['email_sender_email'])) echo $options['email_sender_email']; ?>" ></input>
				</p>
				<div class="spacer"></div>
				
				<h3><?php _e('Roles', 'woocommerce-customers-manager');?></h3>
				<p>
					<label><?php _e('Select which user roles you want to display on customer list and relation between roles (customers have one of selected roles (OR) or have all of the selected roles (AND))' ,'woocommerce-customers-manager');?></label>
					<strong><?php _e('The default WooCommerce user role is: CUSTOMER', 'woocommerce-customers-manager');?></strong> <br/>
					<small><strong><?php _e('NOTE:', 'woocommerce-customers-manager');?></strong> <?php _e('Leave empty to allow all roles', 'woocommerce-customers-manager');?></small>
					<br/>
					<select class="js-role-select" name="wccm_general_options[allowed_roles][]" multiple='multiple'> 
					<?php 
							$first_time = !isset($options['allowed_roles']) ? true:false;
							foreach( $wp_roles->roles as $role_code => $role_data)
							{
								$selected = '';		
								/* if(($first_time && $role_code == "customer")  || ($first_time && $role_code == "subscriber"))
									$selected = ' selected="selected" ';
								elseif(!$first_time) */
								if($role_code != 'administrator')
								{
									if(isset($options['allowed_roles']))
										foreach((array)$options['allowed_roles'] as $role)
											if($role == $role_code)
													$selected = ' selected="selected" ';
											
									echo '<option value="'.$role_code.'" '.$selected.'>'.$role_data['name'].'</option>';
								}
							}
							if(isset($options['allowed_roles']))
								foreach((array)$options['allowed_roles'] as $role)
									if($role == 'translate')
											$selected = ' selected="selected" ';
							//echo '<option value="translate" '.$selected.'>Translate</option>';
						?>
						</select>
						<select name="wccm_general_options[roles_relation]">						
							<option value="or" <?php if(!empty($options['roles_relation']) && $options['roles_relation'] =='or') echo 'selected="selected"'; ?>>OR</option>
							<option value="and" <?php if(!empty($options['roles_relation']) && $options['roles_relation'] =='and') echo 'selected="selected"'; ?>>AND</option>
						</select>
					</p>
					
				<h3><?php _e('Automatic guest conversion - Checkout page', 'woocommerce-customers-manager');?></h3>
				<p>
					<label><?php _e('Convert guest user to registered after an order has been placed?', 'woocommerce-customers-manager');?></label>
					<p><?php _e('The plugin will automatically convert guest user to registered after an order has been placed. <strong>In case	the billing email used by the guest is already associated to a register user, the guest orders will be assigned to that registered user</strong>', 'woocommerce-customers-manager');?></p>
					<input type="checkbox"id="automatic_conversion" name="wccm_general_options[automatic_conversion]" value="true" <?php if(isset($options['automatic_conversion'])) echo 'checked="checked"'; ?>></input>
					
					<div id="do_not_convert_if_email_is_already_associated_option_box">
						<label><?php _e('Do not convert guest to registered in case the email address is already associated to an account', 'woocommerce-customers-manager');?></label>
						<input type="checkbox"  id="do_not_convert_if_email_is_already_associated" name="wccm_general_options[do_not_convert_if_email_is_already_associated]" value="true" <?php if(isset($options['do_not_convert_if_email_is_already_associated'])) echo 'checked="checked"'; ?>></input>
					</div>
					
					<br/><br/>
					<label><?php _e('Send an email with login credentials after an automatic conversion?', 'woocommerce-customers-manager');?></label>
					<input type="checkbox" name="wccm_general_options[send_email_after_automatic_conversion]" value="true" <?php if(isset($options['send_email_after_automatic_conversion'])) echo 'checked="checked"'; ?>></input>
				</p>
				<h3><?php _e('Manual guest conversion - Order/Guests list page', 'woocommerce-customers-manager');?></h3>
				<p>
					<label><?php _e('Do not merge guest to registered user in case of existing email address', 'woocommerce-customers-manager');?></label>
					<p><?php _e('By default, if the guest billing email is already associated to a registered user, the guests will be merged with the registered one. Enable the following option to disable that feature.', 'woocommerce-customers-manager');?></p>
					<input type="checkbox"id="automatic_conversion" name="wccm_general_options[do_not_convert_if_existing_on_manual_conversion]" value="true" <?php if(isset($options['do_not_convert_if_existing_on_manual_conversion'])) echo 'checked="checked"'; ?>></input>	
				</p>
				
				
				<h3><?php _e('Action triggering', 'woocommerce-customers-manager');?></h3>
				<p>
					<label><?php _e('Would you like to trigger WordPress <i>profile_update</i> action on user data update?', 'woocommerce-customers-manager');?></label>
					<input type="checkbox" name="wccm_general_options[actions_profile_update]" value="true" <?php if(isset($options['actions_profile_update'])) echo 'checked="checked"'; ?>></input>
				</p>
				
				
				<p>
					<label><?php _e('Would you like to trigger WordPress <i>set_user_role</i> action when user role is changed?', 'woocommerce-customers-manager');?></label>
					<input type="checkbox" name="wccm_general_options[actions_user_role_change]" value="true" <?php if(isset($options['actions_user_role_change'])) echo 'checked="checked"'; ?>></input>
				</p>
				
				
				<h3><?php _e('WPUEF options', 'woocommerce-customers-manager');?></h3>
				<p>
				<?php if(!$wccm_customer_model->has_customer_extra_wpuef_fields()) echo '<span class="warning_message">'.__('WPUEF plugin is not installed or is not active, this option will be disabled.', 'woocommerce-customers-manager').'</span>'; ?>
				<label><?php _e('Include User Extra Fields on CSV export?', 'woocommerce-customers-manager');?></label>
					<input type="checkbox" name="wccm_general_options[wpuef_include_fields_on_csv_export]" value="true" <?php if($wpuef_include_fields_on_csv_export) echo 'checked="checked"'; ?> <?php if(!$wccm_customer_model->has_customer_extra_wpuef_fields()) echo 'disabled="disabled"'?>></input>
				</p>
				
				<p>
				<input name="Submit" type="submit" id="save_button" class="button-primary" value="<?php esc_attr_e('Save Changes', 'woocommerce-customers-manager'); ?>" />
				</p>
			</div>
			</form>
		</div>
		<?php
		echo ob_get_clean();
	}	
		
}
?>