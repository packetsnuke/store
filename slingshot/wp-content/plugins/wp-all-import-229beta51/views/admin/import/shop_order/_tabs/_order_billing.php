<div class="panel woocommerce_options_panel" id="billing_order_data">
	<div class="options_group hide_if_grouped">																						
		<div class="input">
			<div class="form-field wpallimport-radio-field">
				<input type="radio" id="billing_source_existing" name="pmwi_order[billing_source]" value="existing" <?php echo 'existing' == $post['pmwi_order']['billing_source'] ? 'checked="checked"' : '' ?> class="switcher"/>
				<label for="billing_source_existing" style="width:auto;"><?php _e('Load details from existing customer', 'wp_all_import_plugin') ?></label>																				
				<a href="#help" class="wpallimport-help" title="<?php _e('If no customer is found the order will be skipped.', 'wp_all_import_plugin') ?>" style="position:relative; top: 3px;">?</a>
			</div>
			<span class="wpallimport-clear"></span>
			<div class="switcher-target-billing_source_existing" style="padding-left:27px;">															
				<span class="wpallimport-slide-content" style="padding-left:0;">
					<p class="form-field"><?php _e('Match user by:', 'wpai_woocommerce_addon_plugin'); ?></p>
					<!-- Match user by Username -->
					<div class="form-field wpallimport-radio-field">
						<input type="radio" id="billing_source_match_by_username" name="pmwi_order[billing_source_match_by]" value="username" <?php echo 'username' == $post['pmwi_order']['billing_source_match_by'] ? 'checked="checked"' : '' ?> class="switcher"/>
						<label for="billing_source_match_by_username"><?php _e('Username', 'wp_all_import_plugin') ?></label>		
						<span class="wpallimport-clear"></span>
						<div class="switcher-target-billing_source_match_by_username set_with_xpath">
							<span class="wpallimport-slide-content" style="padding-left:0;">
								<input type="text" class="short rad4" name="pmwi_order[billing_source_username]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_source_username']) ?>"/>			
							</span>
						</div>
					</div>
					<div class="clear"></div>
					<!-- Match user by Email -->
					<div class="form-field wpallimport-radio-field">
						<input type="radio" id="billing_source_match_by_email" name="pmwi_order[billing_source_match_by]" value="email" <?php echo 'email' == $post['pmwi_order']['billing_source_match_by'] ? 'checked="checked"' : '' ?> class="switcher"/>
						<label for="billing_source_match_by_email"><?php _e('Email', 'wp_all_import_plugin') ?></label>			
						<span class="wpallimport-clear"></span>
						<div class="switcher-target-billing_source_match_by_email set_with_xpath">
							<span class="wpallimport-slide-content" style="padding-left:0;">
								<input type="text" class="short rad4" name="pmwi_order[billing_source_email]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_source_email']) ?>"/>			
							</span>
						</div>
					</div>
					<div class="clear"></div>
					<!-- Match user by Custom Field -->
					<div class="form-field wpallimport-radio-field">
						<input type="radio" id="billing_source_match_by_cf" name="pmwi_order[billing_source_match_by]" value="cf" <?php echo 'cf' == $post['pmwi_order']['billing_source_match_by'] ? 'checked="checked"' : '' ?> class="switcher"/>
						<label for="billing_source_match_by_cf"><?php _e('Custom Field', 'wp_all_import_plugin') ?></label>			
						<span class="wpallimport-clear"></span>
						<div class="switcher-target-billing_source_match_by_cf set_with_xpath">
							<span class="wpallimport-slide-content" style="padding-left:0;">
								<p>
									<label style="line-height: 30px;"><?php _e('Name', 'wp_all_import_plugin'); ?></label>
									<input type="text" class="short rad4" name="pmwi_order[billing_source_cf_name]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_source_cf_name']) ?>"/>			
								</p>
								<p>
									<label style="line-height: 30px;"><?php _e('Value', 'wp_all_import_plugin'); ?></label>
									<input type="text" class="short rad4" name="pmwi_order[billing_source_cf_value]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_source_cf_value']) ?>"/>			
								</p>
							</span>
						</div>
					</div>
					<div class="clear"></div>
					<!-- Match user by user ID -->
					<div class="form-field wpallimport-radio-field">
						<input type="radio" id="billing_source_match_by_id" name="pmwi_order[billing_source_match_by]" value="id" <?php echo 'id' == $post['pmwi_order']['billing_source_match_by'] ? 'checked="checked"' : '' ?> class="switcher"/>
						<label for="billing_source_match_by_id"><?php _e('User ID', 'wp_all_import_plugin') ?></label>			
						<span class="wpallimport-clear"></span>
						<div class="switcher-target-billing_source_match_by_id set_with_xpath">
							<span class="wpallimport-slide-content" style="padding-left:0;">
								<input type="text" class="short rad4" name="pmwi_order[billing_source_id]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_source_id']) ?>"/>
							</span>
						</div>
					</div>
				</span>																	
			</div>
		</div>
		<div class="clear"></div>
		<div style="margin-top:0; padding-left:8px;">
			<div class="form-field wpallimport-radio-field">
				<input type="radio" id="billing_source_guest" name="pmwi_order[billing_source]" value="guest" <?php echo 'guest' == $post['pmwi_order']['billing_source'] ? 'checked="checked"' : '' ?> class="switcher"/>
				<label for="billing_source_guest" style="width:auto;"><?php _e('Use guest customer', 'wp_all_import_plugin') ?></label>	
			</div>
			<span class="wpallimport-clear"></span>
			<div class="switcher-target-billing_source_guest" style="padding-left:45px;">								
				<span class="wpallimport-slide-content" style="padding-left:0;">
					<table cellspacing="5" class="wpallimport-order-billing-fields">
						<tr>
							<td>																		
								<label><?php _e('First Name', 'wp_all_import_plugin'); ?></label>
								<div class="clear">
									<input type="text" class="rad4" name="pmwi_order[billing_first_name]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_first_name']) ?>"/>									
								</div>
							</td>
							<td>																		
								<label><?php _e('Last Name', 'wp_all_import_plugin'); ?></label>
								<div class="clear">
									<input type="text" class="rad4" name="pmwi_order[billing_last_name]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_last_name']) ?>"/>									
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2">																		
								<label><?php _e('Company', 'wp_all_import_plugin'); ?></label>
								<div class="clear">
									<input type="text" class="rad4" name="pmwi_order[billing_company]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_company']) ?>"/>
								</div>																		
							</td>
						</tr>
						<tr>
							<td>																		
								<label><?php _e('Address 1', 'wp_all_import_plugin'); ?></label>
								<div class="clear">
									<input type="text" class="rad4" name="pmwi_order[billing_address_1]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_address_1']) ?>"/>					
								</div>
							</td>
							<td>																		
								<label><?php _e('Address 2', 'wp_all_import_plugin'); ?></label>
								<div class="clear">
									<input type="text" class="rad4" name="pmwi_order[billing_address_2]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_address_2']) ?>"/>					
								</div>
							</td>
						</tr>
						<tr>
							<td>																		
								<label><?php _e('City', 'wp_all_import_plugin'); ?></label>
								<div class="clear">
									<input type="text" class="rad4" name="pmwi_order[billing_city]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_city']) ?>"/>
								</div>
							</td>
							<td>																		
								<label><?php _e('Postcode', 'wp_all_import_plugin'); ?></label>
								<div class="clear">
									<input type="text" class="rad4" name="pmwi_order[billing_postcode]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_postcode']) ?>"/>					
								</div>
							</td>
						</tr>
						<tr>
							<td>																		
								<label><?php _e('Country', 'wp_all_import_plugin'); ?></label>
								<div class="clear">
									<input type="text" class="rad4" name="pmwi_order[billing_country]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_country']) ?>"/>
								</div>
							</td>
							<td>																		
								<label><?php _e('State/Country', 'wp_all_import_plugin'); ?></label>
								<div class="clear">
									<input type="text" class="rad4" name="pmwi_order[billing_state]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_state']) ?>"/>							
								</div>
							</td>
						</tr>
						<tr>
							<td>																		
								<label><?php _e('Email', 'wp_all_import_plugin'); ?></label>
								<div class="clear">
									<input type="text" class="rad4" name="pmwi_order[billing_email]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_email']) ?>"/>							
								</div>
							</td>
							<td>																		
								<label><?php _e('Phone', 'wp_all_import_plugin'); ?></label>
								<div class="clear">
									<input type="text" class="rad4" name="pmwi_order[billing_phone]" style="" value="<?php echo esc_attr($post['pmwi_order']['billing_phone']) ?>"/>							
								</div>
							</td>
						</tr>
					</table>
				</span>
			</div>
		</div>																														
		<div class="clear"></div>
	</div>
</div>