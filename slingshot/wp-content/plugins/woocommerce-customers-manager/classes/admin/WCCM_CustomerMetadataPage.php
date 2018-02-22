<?php 
class WCCM_CustomerMetadataPage
{
	var $error;
	var $messages;
	public function __construct()
	{
		
	}
	public function render_page()
	{
		global $wccm_customer_model;
		
		wp_enqueue_style('wccm-common', WCCM_PLUGIN_PATH.'/css/common.css');  
		wp_enqueue_style('wccm-customer-metadata', WCCM_PLUGIN_PATH.'/css/customer-metadata.css');  
		
		wp_register_script('wccm-admin-customer-metadata-page', WCCM_PLUGIN_PATH.'/js/admin-customer-metadata-page.js', array('jquery'));
		wp_localize_script( 'wccm-admin-customer-metadata-page', 'wccm', array('delete_message' => __( 'Are you sure?', 'woocommerce-customers-manager' ),
																				'meta_key_empty_message' => __( 'Meta name cannot be empty', 'woocommerce-customers-manager' )));
		wp_enqueue_script('wccm-admin-customer-metadata-page');
		
		$user_id = $_GET['customer'];
		$wc_user = new WC_Customer($user_id);
		//$meta_data = $wc_user->get_meta_data();
		$meta_data = $wccm_customer_model->get_all_user_meta($user_id);
		?>
		<div id="wpbody">
			<!-- Title -->
			<h2 id=""> 
				<?php  _e('Edit / View meta', 'woocommerce-customers-manager');  
				
				$url =  get_admin_url()."?page=woocommerce-customers-manager";
				if(isset($_GET['back']))
				{
					$url =  get_admin_url()."?page=woocommerce-customers-manager&customer=".$user_id."&action=customer_details";
				}
				?>
				<small class="wc-admin-breadcrumb"><a href="<?php echo $url; ?>" title="<?php _e('Go back', 'woocommerce-customers-manager') ?>">
					<img draggable="false" class="emoji" alt="Back" src="https://s.w.org/images/core/emoji/2/svg/2934.svg"></a>
				</small>
			</h2>
			<!-- Errors/Messages display -->
			<?php 
				$notice_class = isset($this->error) ? "error" : "updated"; // error || updated
				$notice_messages = isset($this->error) ? $this->error : $this->messages;
				if ( isset($notice_messages) ) : ?>
				<div class="<?php echo $notice_class; ?>">
					<ul>
					<?php
						foreach ( $this->notice_messages as $message )
							echo "<li>{$message}</li>\n";
					?>
					</ul>
				</div>
			<?php endif; ?>
			
			<!-- Body -->
			<div class="postbox">
			
			<h2><?php _e('Add new meta', 'woocommerce-customers-manager');?></h2>
			<table class="widefat">
				<thead>
				  <tr>
					 <th><?php _e('Name', 'woocommerce-customers-manager');?></th>
					 <th><?php _e('Value', 'woocommerce-customers-manager');?></th>
					 <th><?php _e('Update policy', 'woocommerce-customers-manager');?></th>
					 <th><?php _e('Action', 'woocommerce-customers-manager');?></th>
				  </tr>
				 </thead>
				<tbody>
					<tr class="table-row">
						<td><textarea required="required" id="meta-key"></textarea></td>
						<td><textarea class="" id="meta-value" ></textarea></td>
						<td>
							<select id="update-policy">
								<option value="update"><?php _e('Update if already existing', 'woocommerce-customers-manager');?></option>
								<option value="create-new"><?php _e('Create new if already existing', 'woocommerce-customers-manager');?></option>
							</select>
						</td>
						<td class="actions-column">
							<div id="wccm-add-buttons-cointaner">
								<button class="button-primary wccm-add-button" data-user-id="<?php echo $user_id; ?>"><?php _e('Add', 'woocommerce-customers-manager');?></button>
							</div>
							<div class="ajax-loader" id="ajax-loader-add">
								<img src="<?php echo WCCM_PLUGIN_PATH;?>/images/ajax-loader.gif"></img>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			
			<h2><?php _e('Edit existing meta', 'woocommerce-customers-manager');?></h2>
			<table class="wp-list-table widefat striped">
				<thead>
				  <tr>
					 <th><?php _e('ID', 'woocommerce-customers-manager');?></th>
					 <th><?php _e('Name', 'woocommerce-customers-manager');?></th>
					 <th><?php _e('Value', 'woocommerce-customers-manager');?></th>
					 <th><?php _e('Actions', 'woocommerce-customers-manager');?></th>
				  </tr>
				 </thead>
				<tbody>
				<?php //wccm_var_dump($meta_data); 
					/* format 
					object(stdClass)#3231 (4) {
						["umeta_id"]=>
						string(6) "271333"
						["user_id"]=>
						string(4) "8014"
						["meta_key"]=>
						string(10) "first_name"
						["meta_value"]=>
						string(5) "John"
					  }
					  */
					foreach((array)$meta_data as $tmp_meta_data): ?>
						<tr class="table-row" id="row-<?php echo $tmp_meta_data->umeta_id;?>">
							<td><?php echo $tmp_meta_data->umeta_id;?></th>
							<td>
								<textarea class="meta-textarea" id="meta-key-<?php echo $tmp_meta_data->umeta_id;?>" ><?php echo $tmp_meta_data->meta_key;?></textarea>
							</th>
							<td class="value-column">
								<textarea class="meta-textarea" id="meta-value-<?php echo $tmp_meta_data->umeta_id;?>" ><?php echo $tmp_meta_data->meta_value;?></textarea>
							</td>
							<td class="actions-column">
								<div id="wccm-update-buttons-cointaner-<?php echo $tmp_meta_data->umeta_id; ?>">
									<button class="button-primary wccm-update-button" data-id="<?php echo $tmp_meta_data->umeta_id; ?>"  data-user-id="<?php echo $user_id; ?>" ><?php _e('Update', 'woocommerce-customers-manager');?></button> 
									<button class="button-delete wccm-delete-button" data-id="<?php echo $tmp_meta_data->umeta_id; ?>"  data-user-id="<?php echo $user_id; ?>"><?php _e('Delete', 'woocommerce-customers-manager');?></button>
								</div>
								<div class="ajax-loader" id="ajax-loader-update-<?php echo $tmp_meta_data->umeta_id; ?>">
									<img src="<?php echo WCCM_PLUGIN_PATH;?>/images/ajax-loader.gif"></img>
								</div>
							</td>
						</tr>
				<?php endforeach; ?>
					<tbody>
				</table>
			</div>
		</div>
		<?php 
	}
}
?>