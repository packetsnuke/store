<?php 
class WCCM_CustomerGuestList
{
	var $filter_by_product_id = 0;
	var $customer_emails = 0;
	
	public function __construct()
	{
	}
	public function render_page()
	{
		
		global $wp_scripts;//useless
		$wp_scripts->queue = array();//useless
		wp_enqueue_script( 'jquery' ); //useless
		//wp_enqueue_script ('jquery-ui-core'  ) ;
		wp_enqueue_script( 'jquery-ui-progressbar' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		//wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		wp_enqueue_style('jquery-style',  WCCM_PLUGIN_PATH.'/css/jquery-ui.css');
		wp_enqueue_style('jquery-datatable',  WCCM_PLUGIN_PATH.'/css/jquery.dataTables.min.css');
		wp_enqueue_style('customer-export-css',  WCCM_PLUGIN_PATH.'/css/customer-guests-list.css');
		wp_enqueue_style('wccm-common',  WCCM_PLUGIN_PATH.'/css/common.css');   
		
		wp_enqueue_script('ajax-guests-list',  WCCM_PLUGIN_PATH.'/js/admin-guests-list-ajax.js', array( 'jquery' ), time()); 
		wp_enqueue_script('data-table',  WCCM_PLUGIN_PATH.'/js/jquery.dataTables.min.js', array( 'jquery' )); 
		
		
		$hide_not_purchasing_guest_customers = WCCM_Options::get_option('hide_not_purchasing_guest_customers');
		if(isset($_GET['filter-by-product']))
			$this->filter_by_product_id = $_GET['filter-by-product'];
		
		if(isset($_REQUEST['wccm_customers_emails']))
		{
			$this->customer_emails =gzinflate(base64_decode(strtr($_REQUEST['wccm_customers_emails'], '-_', '+/')));
			$this->customer_emails = $this->customer_emails == "" ? 'none' : $this->customer_emails;
		}
		?>
		<script>
		var hide_not_purchasing_guest_customers = <?php echo $hide_not_purchasing_guest_customers ?>;
		var wccm_filter_by_emails = "<?php echo $this->customer_emails; ?>";
		var wccm_admin_url = "<?php echo admin_url('edit.php') ?>";
		var wccm_product_filter_id = <?php echo $this->filter_by_product_id; ?>;
		</script>
		<h2 class="nav-tab-wrapper">
		<a class='nav-tab' href='?page=woocommerce-customers-manager<?php if($this->filter_by_product_id >0) echo '&filter-by-product='.$this->filter_by_product_id; if(isset($_REQUEST['wccm_customers_ids'])) echo '&wccm_customers_ids='.$_REQUEST['wccm_customers_ids'];  if(isset($_REQUEST['wccm_customers_emails'])) echo '&wccm_customers_emails='.$_REQUEST['wccm_customers_emails']; ?>'>Registered</a>
		<a class='nav-tab nav-tab-active' href='?page=woocommerce-customers-manager&action=wccm-guests-list<?php if($this->filter_by_product_id >0) echo '&filter-by-product='.$this->filter_by_product_id; if(isset($_REQUEST['wccm_customers_ids'])) echo '&wccm_customers_ids='.$_REQUEST['wccm_customers_ids']; if(isset($_REQUEST['wccm_customers_emails'])) echo '&wccm_customers_emails='.$_REQUEST['wccm_customers_emails'];?>'>Guests</a>
		</h2>
		
        <h2><?php _e('Guests List', 'woocommerce-customers-manager'); ?> </h2>
		
		<div id="progress-container">
			<h3 id="ajax-progress-title"><?php  _e('Progress', 'woocommerce-customers-manager');?></h3>
			<div id="ajax-progress"></div>
			<div id="progressbar"></div>
		</div>
		<div id="guest-customer-list">
			<button class="button button-primary" id="export-button"><?php  _e('Export selected customers', 'woocommerce-customers-manager');?></button>		
			<button class="button" id="convert-button"><?php  _e('Convert selected customers', 'woocommerce-customers-manager');?></button>		
			<p><?php _e('<strong>NOTE ON GUEST TO REGISTERED CONVERSION FEATURE:</strong> In case the billing email used by the guest is already associated to a register user, the guest orders will be assigned to that registered user', 'woocommerce-customers-manager');?></p>
			<table id="guests-table" class="display striped" cellspacing="0" width="100%" ><!--class="wp-list-table widefat fixed customers"-->
				<thead>
						<tr>
							<th scope="col" class="manage-column column-select" style=""><input type="checkbox" class="all-customers-select" ></input><?php  //_e('Select', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-conversion" style=""><?php  _e('Conversion', 'woocommerce-customers-manager');?><br/>
																							<input type="checkbox" class="all-guest-customer-email-option-toggle" ><small ><?php  _e('Toggle email sending option', 'woocommerce-customers-manager');?></small></input> </th>
							<th scope="col" class="manage-column column-name sortable desc" style=""><?php  _e('Name', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-surname sortable desc" style=""><?php  _e('Surname', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-address" style=""><?php  _e('Address (billing)', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-address" style=""><?php  _e('Address (shipping)', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-address" style=""><?php  _e('Phone', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-email sortable desc" style=""><?php  _e('Email', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-orders sortable desc" style=""><?php  _e('Orders', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-total_spent sortable desc" style=""><?php  _e('Total spent', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-first_order_date" style=""><?php  _e('First order date', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-last_order_date sortable desc" style=""><?php  _e('Last order date', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-order_list" style=""><?php  _e('Order list', 'woocommerce-customers-manager');?></th>
						</tr>
				</thead>

				<tfoot>
						<tr>
							<th scope="col" class="manage-column column-select" style=""><input type="checkbox" class="all-customers-select" ></input><?php //_e('Select', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-conversion" style=""><?php  _e('Conversion', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-name sortable desc" style=""><?php  _e('Name', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-surname sortable desc" style=""><?php  _e('Surname', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-address" style=""><?php  _e('Address (billing)', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-address" style=""><?php  _e('Address (shipping)', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-address" style=""><?php  _e('Phone', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-email sortable desc" style=""><?php  _e('Email', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-orders sortable desc" style=""><?php  _e('Orders', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-total_spent sortable desc" style=""><?php  _e('Total spent', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-first_order_date" style=""><?php  _e('First order date', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-last_order_date sortable desc" style=""><?php  _e('Last order date', 'woocommerce-customers-manager');?></th>
							<th scope="col" class="manage-column column-order_list" style=""><?php  _e('Order list', 'woocommerce-customers-manager');?></th>
						</tr>
				</tfoot>

				<tbody id="table-body">
				</tbody>
				</table>
		</div>
		<?php
	}	
}
?>