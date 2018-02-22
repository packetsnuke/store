<?php if ( ! $isWizard  or ! empty(PMXI_Plugin::$session->deligate) and PMXI_Plugin::$session->deligate == 'wpallexport' or $isWizard and "new" != $post['wizard_type']): ?>
<h4><?php _e('When WP All Import finds new or changed data...', 'wp_all_import_plugin'); ?></h4>
<?php else: ?>
<h4><?php _e('If this import is run again and WP All Import finds new or changed data...', 'wp_all_import_plugin'); ?></h4>
<?php endif; ?>
<div class="input">
	<input type="hidden" name="create_new_records" value="0" />
	<input type="checkbox" id="create_new_records" name="create_new_records" value="1" <?php echo $post['create_new_records'] ? 'checked="checked"' : '' ?> />
	<label for="create_new_records"><?php _e('Create new orders from records newly present in your file', 'wp_all_import_plugin') ?></label>
	<?php if ( ! empty(PMXI_Plugin::$session->deligate) and PMXI_Plugin::$session->deligate == 'wpallexport' ): ?>
	<a href="#help" class="wpallimport-help" title="<?php _e('New orders will only be created when ID column is present and value in ID column is unique.', 'wp_all_import_plugin') ?>" style="top: -1px;">?</a>
	<?php endif; ?>
</div>
<div class="switcher-target-auto_matching">
	<div class="input">
		<input type="hidden" name="is_delete_missing" value="0" />
		<input type="checkbox" id="is_delete_missing" name="is_delete_missing" value="1" <?php echo $post['is_delete_missing'] ? 'checked="checked"': '' ?> class="switcher" <?php if ( "new" != $post['wizard_type']): ?>disabled="disabled"<?php endif; ?>/>
		<label for="is_delete_missing" <?php if ( "new" != $post['wizard_type']): ?>style="color:#ccc;"<?php endif; ?>><?php _e('Delete orders that are no longer present in your file', 'wp_all_import_plugin') ?></label>
		<?php if ( "new" != $post['wizard_type']): ?>
		<a href="#help" class="wpallimport-help" title="<?php _e('Records removed from the import file can only be deleted when importing into New Items. This feature cannot be enabled when importing into Existing Items.', 'wp_all_import_plugin') ?>" style="position:relative; top: -1px;">?</a>
		<?php endif; ?>	
	</div>
	<div class="switcher-target-is_delete_missing" style="padding-left:17px;">
		<div class="input">
			<input type="hidden" name="is_keep_attachments" value="0" />
			<input type="checkbox" id="is_keep_attachments" name="is_keep_attachments" value="1" <?php echo $post['is_keep_attachments'] ? 'checked="checked"': '' ?> <?php if ( "new" != $post['wizard_type']): ?>disabled="disabled"<?php endif; ?>/>
			<label for="is_keep_attachments"><?php _e('Do not remove attachments', 'wp_all_import_plugin') ?></label>			
		</div>
		<div class="input">
			<input type="hidden" name="is_keep_imgs" value="0" />
			<input type="checkbox" id="is_keep_imgs" name="is_keep_imgs" value="1" <?php echo $post['is_keep_imgs'] ? 'checked="checked"': '' ?> <?php if ( "new" != $post['wizard_type']): ?>disabled="disabled"<?php endif; ?>/>
			<label for="is_keep_imgs"><?php _e('Do not remove images', 'wp_all_import_plugin') ?></label>			
		</div>
		<div class="input">
			<input type="hidden" name="is_update_missing_cf" value="0" />
			<input type="checkbox" id="is_update_missing_cf" name="is_update_missing_cf" value="1" <?php echo $post['is_update_missing_cf'] ? 'checked="checked"': '' ?> class="switcher" <?php if ( "new" != $post['wizard_type']): ?>disabled="disabled"<?php endif; ?>/>
			<label for="is_update_missing_cf"><?php _e('Instead of deletion, set Custom Field', 'wp_all_import_plugin') ?></label>			
			<div class="switcher-target-is_update_missing_cf" style="padding-left:17px;">
				<div class="input">
					<?php _e('Name', 'wp_all_import_plugin') ?>
					<input type="text" name="update_missing_cf_name" value="<?php echo esc_attr($post['update_missing_cf_name']) ?>" />
					<?php _e('Value', 'wp_all_import_plugin') ?>
					<input type="text" name="update_missing_cf_value" value="<?php echo esc_attr($post['update_missing_cf_value']) ?>" />									
				</div>
			</div>
		</div>
		<div class="input">
			<input type="hidden" name="set_missing_to_draft" value="0" />
			<input type="checkbox" id="set_missing_to_draft" name="set_missing_to_draft" value="1" <?php echo $post['set_missing_to_draft'] ? 'checked="checked"': '' ?> <?php if ( "new" != $post['wizard_type']): ?>disabled="disabled"<?php endif; ?>/>
			<label for="set_missing_to_draft"><?php _e('Instead of deletion, change post status to Draft', 'wp_all_import_plugin') ?></label>					
		</div>
	</div>	
</div>	
<div class="input">
	<input type="hidden" id="is_keep_former_posts" name="is_keep_former_posts" value="yes" />				
	<input type="checkbox" id="is_not_keep_former_posts" name="is_keep_former_posts" value="no" <?php echo "yes" != $post['is_keep_former_posts'] ? 'checked="checked"': '' ?> class="switcher" />
	<label for="is_not_keep_former_posts"><?php _e('Update existing orders with changed data in your file', 'wp_all_import_plugin') ?></label>
	<?php if ( $isWizard and "new" == $post['wizard_type'] and empty(PMXI_Plugin::$session->deligate)): ?>
	<a href="#help" class="wpallimport-help" style="position: relative; top: -2px;" title="<?php _e('These options will only be used if you run this import again later. All data is imported the first time you run an import.', 'wp_all_import_plugin') ?>">?</a>	
	<?php endif; ?>
	<div class="switcher-target-is_not_keep_former_posts" style="padding-left:17px;">
		<input type="radio" id="update_all_data" class="switcher" name="update_all_data" value="yes" <?php echo 'no' != $post['update_all_data'] ? 'checked="checked"': '' ?>/>
		<label for="update_all_data"><?php _e('Update all data', 'wp_all_import_plugin' )?></label><br>
		
		<input type="radio" id="update_choosen_data" class="switcher" name="update_all_data" value="no" <?php echo 'no' == $post['update_all_data'] ? 'checked="checked"': '' ?>/>
		<label for="update_choosen_data"><?php _e('Choose which data to update', 'wp_all_import_plugin' )?></label><br>
		<div class="switcher-target-update_choosen_data"  style="padding-left:27px;">
			<div class="input">
				<h4 class="wpallimport-trigger-options wpallimport-select-all" rel="<?php _e("Unselect All", "wp_all_import_plugin"); ?>"><?php _e("Select All", "wp_all_import_plugin"); ?></h4>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_status" value="0" />
				<input type="checkbox" id="is_update_status" name="is_update_status" value="1" <?php echo $post['is_update_status'] ? 'checked="checked"': '' ?> />
				<label for="is_update_status"><?php _e('Order status', 'wp_all_import_plugin') ?></label>
				<a href="#help" class="wpallimport-help" style="position: relative; top: -2px;" title="<?php _e('Hint: uncheck this box to keep trashed orders in the trash.', 'wp_all_import_plugin') ?>">?</a>
			</div>						
			<div class="input">
				<input type="hidden" name="is_update_excerpt" value="0" />
				<input type="checkbox" id="is_update_excerpt" name="is_update_excerpt" value="1" <?php echo $post['is_update_excerpt'] ? 'checked="checked"': '' ?> />
				<label for="is_update_excerpt"><?php _e('Customer Note', 'wp_all_import_plugin') ?></label>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_dates" value="0" />
				<input type="checkbox" id="is_update_dates" name="is_update_dates" value="1" <?php echo $post['is_update_dates'] ? 'checked="checked"': '' ?> />
				<label for="is_update_dates"><?php _e('Dates', 'wp_all_import_plugin') ?></label>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_billing_details" value="0" />
				<input type="checkbox" id="is_update_billing_details_<?php echo $post_type; ?>" name="is_update_billing_details" value="1" <?php echo $post['is_update_billing_details'] ? 'checked="checked"': '' ?>  class="switcher"/>
				<label for="is_update_billing_details_<?php echo $post_type; ?>"><?php _e('Billing Details', 'wpai_woocommerce_addon_plugin') ?></label>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_shipping_details" value="0" />
				<input type="checkbox" id="is_update_shipping_details_<?php echo $post_type; ?>" name="is_update_shipping_details" value="1" <?php echo $post['is_update_shipping_details'] ? 'checked="checked"': '' ?>  class="switcher"/>
				<label for="is_update_shipping_details_<?php echo $post_type; ?>"><?php _e('Shipping Details', 'wpai_woocommerce_addon_plugin') ?></label>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_payment" value="0" />
				<input type="checkbox" id="is_update_payment_<?php echo $post_type; ?>" name="is_update_payment" value="1" <?php echo $post['is_update_payment'] ? 'checked="checked"': '' ?>  class="switcher"/>
				<label for="is_update_payment_<?php echo $post_type; ?>"><?php _e('Payment Details', 'wpai_woocommerce_addon_plugin') ?></label>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_notes" value="0" />
				<input type="checkbox" id="is_update_notes_<?php echo $post_type; ?>" name="is_update_notes" value="1" <?php echo $post['is_update_notes'] ? 'checked="checked"': '' ?>  class="switcher"/>
				<label for="is_update_notes_<?php echo $post_type; ?>"><?php _e('Order Notes', 'wpai_woocommerce_addon_plugin') ?></label>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_products" value="0" />
				<input type="checkbox" id="is_update_products_<?php echo $post_type; ?>" name="is_update_products" value="1" <?php echo $post['is_update_products'] ? 'checked="checked"': '' ?>  class="switcher"/>
				<label for="is_update_products_<?php echo $post_type; ?>"><?php _e('Product Items', 'wpai_woocommerce_addon_plugin') ?></label>
			</div>			
			<div class="input">
				<input type="hidden" name="is_update_fees" value="0" />
				<input type="checkbox" id="is_update_fees_<?php echo $post_type; ?>" name="is_update_fees" value="1" <?php echo $post['is_update_fees'] ? 'checked="checked"': '' ?>  class="switcher"/>
				<label for="is_update_fees_<?php echo $post_type; ?>"><?php _e('Fees Items', 'wpai_woocommerce_addon_plugin') ?></label>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_coupons" value="0" />
				<input type="checkbox" id="is_update_coupons_<?php echo $post_type; ?>" name="is_update_coupons" value="1" <?php echo $post['is_update_coupons'] ? 'checked="checked"': '' ?>  class="switcher"/>
				<label for="is_update_coupons_<?php echo $post_type; ?>"><?php _e('Coupon Items', 'wpai_woocommerce_addon_plugin') ?></label>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_shipping" value="0" />
				<input type="checkbox" id="is_update_shipping_<?php echo $post_type; ?>" name="is_update_shipping" value="1" <?php echo $post['is_update_shipping'] ? 'checked="checked"': '' ?>  class="switcher"/>
				<label for="is_update_shipping_<?php echo $post_type; ?>"><?php _e('Shipping Items', 'wpai_woocommerce_addon_plugin') ?></label>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_taxes" value="0" />
				<input type="checkbox" id="is_update_taxes_<?php echo $post_type; ?>" name="is_update_taxes" value="1" <?php echo $post['is_update_taxes'] ? 'checked="checked"': '' ?>  class="switcher"/>
				<label for="is_update_taxes_<?php echo $post_type; ?>"><?php _e('Tax Items', 'wpai_woocommerce_addon_plugin') ?></label>
			</div>			
			<div class="input">
				<input type="hidden" name="is_update_refunds" value="0" />
				<input type="checkbox" id="is_update_refunds_<?php echo $post_type; ?>" name="is_update_refunds" value="1" <?php echo $post['is_update_refunds'] ? 'checked="checked"': '' ?>  class="switcher"/>
				<label for="is_update_refunds_<?php echo $post_type; ?>"><?php _e('Refunds', 'wpai_woocommerce_addon_plugin') ?></label>
			</div>
			<div class="input">
				<input type="hidden" name="is_update_total" value="0" />
				<input type="checkbox" id="is_update_total_<?php echo $post_type; ?>" name="is_update_total" value="1" <?php echo $post['is_update_total'] ? 'checked="checked"': '' ?>  class="switcher"/>
				<label for="is_update_total_<?php echo $post_type; ?>"><?php _e('Order Total', 'wpai_woocommerce_addon_plugin') ?></label>
			</div>
			<!-- Do not update order custom fields -->
			<input type="hidden" name="is_update_custom_fields" value="0" />
		</div>
	</div>
</div>	