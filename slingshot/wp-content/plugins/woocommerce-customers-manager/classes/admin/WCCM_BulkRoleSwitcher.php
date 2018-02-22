<?php 
class WCCM_BulkRoleSwitcher
{
	public function __construct()
	{
	}
	public function render_page() 
	{
		
		$this->render_options_page();
	}
	private function render_options_page()
	{
		global $wp_roles,$wccm_customer_model;
		wp_enqueue_style('wccm-common', WCCM_PLUGIN_PATH.'/css/common.css');  
		wp_enqueue_style( 'wccm-options-page', WCCM_PLUGIN_PATH.'/css/options-page.css'  );
		
		//wccm_var_dump($_POST['wcc_roles']);
		if(isset($_POST['wcc_roles']))
			$count = $wccm_customer_model->bulk_switch_roles($_POST['wcc_roles']);
		?>
		<div class="wrap">
			<h2><?php _e('Bulk role switcher', 'woocommerce-customers-manager');?></h2>
			<?php if(isset($count)): ?>
				<div id="message" class="updated"><p><?php _e(sprintf('%d users have been switched!', $count), 'woocommerce-customers-manager');?></p></div>
			<?php endif; ?>
			<form method="post" action="" method="post">
			<div id="options-container">
			<p>
				<label><?php _e('In this page you can easily bulk switch user roles. All you have to select a "initial" role and a "final" one. WCCM will change all the users role matching the "initial" with the "final" role.');?></label>
			</p>
			
				<h3><?php _e('Choose the initial role...', 'woocommerce-customers-manager');?></h3>
				<p>
				<select class="js-role-select" name="wcc_roles[from]" required> 
				<?php 
						
						foreach( $wp_roles->roles as $role_code => $role_data)
						{
							$selected = '';		
							foreach($options['allowed_roles'] as $role)
									if($role == $role_code)
											$selected = ' selected="selected" ';
										
							echo '<option value="'.$role_code.'" '.$selected.'>'.$role_data['name'].'</option>';
						}
					?>
					</select>
				</p>
				<h3><?php _e('...and the final one.', 'woocommerce-customers-manager');?></h3>
				<p>
				
				<select class="js-role-select" name="wcc_roles[to]" required> 
				<?php 
						$first_time = !isset($options['allowed_roles']) ? true:false;
						foreach( $wp_roles->roles as $role_code => $role_data)
						{
							$selected = '';		
							if(($first_time && $role_code == "customer")  || ($first_time && $role_code == "subscriber"))
								$selected = ' selected="selected" ';
							elseif(!$first_time)
								foreach($options['allowed_roles'] as $role)
									if($role == $role_code)
											$selected = ' selected="selected" ';
										
							echo '<option value="'.$role_code.'" '.$selected.'>'.$role_data['name'].'</option>';
						}
					?>
					</select>
				</p>
				<p>
				<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Switch roles', 'woocommerce-customers-manager'); ?>" />
				</p>
			</div>
			</form>
		</div>
		<?php
	}	
		
}
?>