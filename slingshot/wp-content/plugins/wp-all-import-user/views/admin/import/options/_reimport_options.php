<h4><?php _e('When WP All Import finds new or changed data...', 'pmxi_plugin'); ?></h4>
<div class="input">
	<input type="hidden" name="create_new_records" value="0" />
	<input type="checkbox" id="create_new_records" name="create_new_records" value="1" <?php echo $post['create_new_records'] ? 'checked="checked"' : '' ?> />
	<label for="create_new_records"><?php _e('Create new users from records newly present in your file', 'pmxi_plugin') ?></label>
</div>
<div class="input">
	<input type="hidden" name="is_delete_missing" value="0" />
	<input type="checkbox" id="is_delete_missing" name="is_delete_missing" value="1" <?php echo $post['is_delete_missing'] ? 'checked="checked"': '' ?> class="switcher"/>
	<label for="is_delete_missing"><?php _e('Delete users that are no longer present in your file', 'pmxi_plugin') ?></label>
	<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want to delete users from the previous import operation which are not found among newly imported set.', 'pmxi_plugin') ?>">?</a>
</div>
<div class="switcher-target-is_delete_missing" style="padding-left:17px;">				
	<div class="input">
		<input type="hidden" name="is_update_missing_cf" value="0" />
		<input type="checkbox" id="is_update_missing_cf" name="is_update_missing_cf" value="1" <?php echo $post['is_update_missing_cf'] ? 'checked="checked"': '' ?> class="switcher"/>
		<label for="is_update_missing_cf"><?php _e('Instead of deletion, set Meta Field', 'pmxi_plugin') ?></label>
		<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want to update users meta fields from the previous import operation which are not found among newly imported set.', 'pmxi_plugin') ?>">?</a>			
		<div class="switcher-target-is_update_missing_cf" style="padding-left:17px;">
			<div class="input">
				<?php _e('Name', 'pmxi_plugin') ?>
				<input type="text" name="update_missing_cf_name" value="<?php echo esc_attr($post['update_missing_cf_name']) ?>" />
				<?php _e('Value', 'pmxi_plugin') ?>
				<input type="text" name="update_missing_cf_value" value="<?php echo esc_attr($post['update_missing_cf_value']) ?>" />									
			</div>
		</div>
	</div>				
</div>			
<div class="input">
	<input type="hidden" id="is_keep_former_posts" name="is_keep_former_posts" value="yes" />				
	<input type="checkbox" id="is_not_keep_former_posts" name="is_keep_former_posts" value="no" <?php echo "yes" != $post['is_keep_former_posts'] ? 'checked="checked"': '' ?> class="switcher" />
	<label for="is_not_keep_former_posts"><?php _e('Update existing users with changed data in your file', 'pmxi_plugin') ?></label>

	<div class="switcher-target-is_not_keep_former_posts" style="padding-left:17px;">
		<input type="radio" id="update_all_data" class="switcher" name="update_all_data" value="yes" <?php echo 'no' != $post['update_all_data'] ? 'checked="checked"': '' ?>/>
		<label for="update_all_data"><?php _e('Update all data', 'pmxi_plugin' )?></label><br>
		
		<input type="radio" id="update_choosen_data" class="switcher" name="update_all_data" value="no" <?php echo 'no' == $post['update_all_data'] ? 'checked="checked"': '' ?>/>
		<label for="update_choosen_data"><?php _e('Choose which data to update', 'pmxi_plugin' )?></label><br>
		<div class="switcher-target-update_choosen_data"  style="padding-left:17px;">
			<div class="input">
				<input type="hidden" name="is_update_first_name" value="0" />
				<input type="checkbox" id="is_update_first_name" name="is_update_first_name" value="1" <?php echo $post['is_update_first_name'] ? 'checked="checked"': '' ?> />
				<label for="is_update_first_name"><?php _e('First Name', 'pmxi_plugin') ?></label>
				<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want previously imported users to change their first name.', 'pmxi_plugin') ?>">?</a>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_last_name" value="0" />
				<input type="checkbox" id="is_update_last_name" name="is_update_last_name" value="1" <?php echo $post['is_update_last_name'] ? 'checked="checked"': '' ?> />
				<label for="is_update_last_name"><?php _e('Last Name', 'pmxi_plugin') ?></label>
				<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want previously imported users to change their last name.', 'pmxi_plugin') ?>">?</a>
			</div>						
			<div class="input">
				<input type="hidden" name="is_update_role" value="0" />
				<input type="checkbox" id="is_update_role" name="is_update_role" value="1" <?php echo $post['is_update_role'] ? 'checked="checked"': '' ?> />
				<label for="is_update_role"><?php _e('Role', 'pmxi_plugin') ?></label>
				<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want previously imported users to change their role.', 'pmxi_plugin') ?>">?</a>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_nickname" value="0" />
				<input type="checkbox" id="is_update_nickname" name="is_update_nickname" value="1" <?php echo $post['is_update_nickname'] ? 'checked="checked"': '' ?> />
				<label for="is_update_nickname"><?php _e('Nickname', 'pmxi_plugin') ?></label>
				<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want previously imported users to change their nickname.', 'pmxi_plugin') ?>">?</a>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_description" value="0" />
				<input type="checkbox" id="is_update_description" name="is_update_description" value="1" <?php echo $post['is_update_description'] ? 'checked="checked"': '' ?> />
				<label for="is_update_description"><?php _e('Description', 'pmxi_plugin') ?></label>
				<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want previously imported users to change their description.', 'pmxi_plugin') ?>">?</a>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_login" value="0" />
				<input type="checkbox" id="is_update_login" name="is_update_login" value="1" <?php echo $post['is_update_login'] ? 'checked="checked"': '' ?> />
				<label for="is_update_login"><?php _e('Login', 'pmxi_plugin') ?></label>
				<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want previously imported users to change their login.', 'pmxi_plugin') ?>">?</a>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_password" value="0" />
				<input type="checkbox" id="is_update_password" name="is_update_password" value="1" <?php echo $post['is_update_password'] ? 'checked="checked"': '' ?> />
				<label for="is_update_password"><?php _e('Password', 'pmxi_plugin') ?></label>
				<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want previously imported users to change their password.', 'pmxi_plugin') ?>">?</a>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_nicename" value="0" />
				<input type="checkbox" id="is_update_nicename" name="is_update_nicename" value="1" <?php echo $post['is_update_nicename'] ? 'checked="checked"': '' ?> />
				<label for="is_update_nicename"><?php _e('Nicename', 'pmxi_plugin') ?></label>
				<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want previously imported users to change their nicename.', 'pmxi_plugin') ?>">?</a>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_email" value="0" />
				<input type="checkbox" id="is_update_email" name="is_update_email" value="1" <?php echo $post['is_update_email'] ? 'checked="checked"': '' ?> />
				<label for="is_update_email"><?php _e('Email', 'pmxi_plugin') ?></label>
				<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want previously imported users to change their email.', 'pmxi_plugin') ?>">?</a>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_registered" value="0" />
				<input type="checkbox" id="is_update_registered" name="is_update_registered" value="1" <?php echo $post['is_update_registered'] ? 'checked="checked"': '' ?> />
				<label for="is_update_registered"><?php _e('Registered Date', 'pmxi_plugin') ?></label>
				<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want previously imported users to change their registered date.', 'pmxi_plugin') ?>">?</a>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_display_name" value="0" />
				<input type="checkbox" id="is_update_display_name" name="is_update_display_name" value="1" <?php echo $post['is_update_display_name'] ? 'checked="checked"': '' ?> />
				<label for="is_update_display_name"><?php _e('Display Name', 'pmxi_plugin') ?></label>
				<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want previously imported users to change their display name.', 'pmxi_plugin') ?>">?</a>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_url" value="0" />
				<input type="checkbox" id="is_update_url" name="is_update_url" value="1" <?php echo $post['is_update_url'] ? 'checked="checked"': '' ?> />
				<label for="is_update_url"><?php _e('URL', 'pmxi_plugin') ?></label>
				<a href="#help" class="wpallimport-help" title="<?php _e('Check this option if you want previously imported users to change their URL.', 'pmxi_plugin') ?>">?</a>
			</div>
			<div class="input">							
				<input type="hidden" name="custom_fields_list" value="0" />			
				<input type="hidden" name="is_update_custom_fields" value="0" />
				<input type="checkbox" id="is_update_custom_fields" name="is_update_custom_fields" value="1" <?php echo $post['is_update_custom_fields'] ? 'checked="checked"': '' ?>  class="switcher"/>
				<label for="is_update_custom_fields"><?php _e('Meta Fields', 'pmxi_plugin') ?></label>
				<!--a href="#help" class="wpallimport-help" title="<?php _e('If Keep Custom Fields box is checked, it will keep all Custom Fields, and add any new Custom Fields specified in Custom Fields section, as long as they do not overwrite existing fields. If \'Only keep this Custom Fields\' is specified, it will only keep the specified fields.', 'pmxi_plugin') ?>">?</a-->
				<div class="switcher-target-is_update_custom_fields" style="padding-left:17px;">
					<div class="input">
						<input type="radio" id="update_custom_fields_logic_full_update" name="update_custom_fields_logic" value="full_update" <?php echo ( "full_update" == $post['update_custom_fields_logic'] ) ? 'checked="checked"': '' ?> class="switcher"/>
						<label for="update_custom_fields_logic_full_update"><?php _e('Update all Meta Fields', 'pmxi_plugin') ?></label>								
					</div>		
					<div class="input">
						<input type="radio" id="update_custom_fields_logic_only" name="update_custom_fields_logic" value="only" <?php echo ( "only" == $post['update_custom_fields_logic'] ) ? 'checked="checked"': '' ?> class="switcher"/>
						<label for="update_custom_fields_logic_only"><?php _e('Update only these Meta Fields, leave the rest alone', 'pmxi_plugin') ?></label>								
						<div class="switcher-target-update_custom_fields_logic_only pmxi_choosen" style="padding-left:17px;">
								
							<span class="hidden choosen_values"><?php if (!empty($existing_meta_keys)) echo esc_html(implode(',', $existing_meta_keys));?></span>
							<input class="choosen_input" value="<?php if (!empty($post['custom_fields_list']) and "only" == $post['update_custom_fields_logic']) echo esc_html(implode(',', $post['custom_fields_list'])); ?>" type="hidden" name="custom_fields_only_list"/>										
						</div>
					</div>
					<div class="input">
						<input type="radio" id="update_custom_fields_logic_all_except" name="update_custom_fields_logic" value="all_except" <?php echo ( "all_except" == $post['update_custom_fields_logic'] ) ? 'checked="checked"': '' ?> class="switcher"/>
						<label for="update_custom_fields_logic_all_except"><?php _e('Leave these fields alone, update all other Meta Fields', 'pmxi_plugin') ?></label>								
						<div class="switcher-target-update_custom_fields_logic_all_except pmxi_choosen" style="padding-left:17px;">
							
							<span class="hidden choosen_values"><?php if (!empty($existing_meta_keys)) echo esc_html(implode(',', $existing_meta_keys));?></span>
							<input class="choosen_input" value="<?php if (!empty($post['custom_fields_list']) and "all_except" == $post['update_custom_fields_logic']) echo esc_html(implode(',', $post['custom_fields_list'])); ?>" type="hidden" name="custom_fields_except_list"/>																				
						</div>
					</div>
				</div>
			</div>							
		</div>
	</div>
</div>						