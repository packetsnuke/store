<?php 
class WCCM_Discover
{
	public function __construct()
	{
	}
	public function render_page()
	{
		global $wccm_order_model/* , $wccm_customer_model */;
		$max_total_sale = $wccm_order_model->get_max_order_total_sale();
		$max_total_sale = isset($max_total_sale) ? $max_total_sale : 0;
		
		$max_user_total_sale = $wccm_order_model->get_user_total_sale();
		$max_user_total_sale = isset($max_user_total_sale) ? $max_user_total_sale : 0;
		
		global $wp_scripts;
		$wp_scripts->queue = array();	
		
		//wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		wp_enqueue_style('jquery-style',  WCCM_PLUGIN_PATH.'/css/jquery-ui.css');
		wp_enqueue_style('datepicker-classic',  WCCM_PLUGIN_PATH.'/css/datepicker/classic.css');   
		wp_enqueue_style('datepicker-date-classic',  WCCM_PLUGIN_PATH.'/css/datepicker/classic.date.css');   
		wp_enqueue_style('datepicker-time-classic',  WCCM_PLUGIN_PATH.'/css/datepicker/classic.time.css');  
		wp_enqueue_style( 'wccm-select2-style',   WCCM_PLUGIN_PATH.'/css/select2.min.css' ); 		
		wp_enqueue_style('wccm-common',  WCCM_PLUGIN_PATH.'/css/common.css');  
		wp_enqueue_style('wccm-discover',  WCCM_PLUGIN_PATH.'/css/admin-discover.css');  
				
		wp_enqueue_script (  'jquery-ui-core'  ) ;
		wp_enqueue_script (  'jquery-ui-slider'  ) ;
		wp_enqueue_script( 'jquery-ui-progressbar' );
		wp_enqueue_script('ui-picker',  WCCM_PLUGIN_PATH.'/js/picker.js');
		wp_enqueue_script('ui-timepicker',  WCCM_PLUGIN_PATH.'/js/picker.date.js');
		//wp_enqueue_script( 'wccm-select2-script',  WCCM_PLUGIN_PATH.'/js/select2.min.js', array('jquery') );
		wp_enqueue_script('wccm-discover',  WCCM_PLUGIN_PATH.'/js/admin-discover.js');
		wp_enqueue_script('wccm-customer-autocomplete', WCCM_PLUGIN_PATH.'/js/admin-discover-customer-autocomplete.js');
		wp_enqueue_script('wccm-products-categories-autocompplete',  WCCM_PLUGIN_PATH.'/js/admin-discover-product-and-categories-autocomplete.js');
		?>
		<script>
			jQuery.fn.select2=null;
		</script>
		<script type='text/javascript' src='<?php echo WCCM_PLUGIN_PATH.'/js/select2.min.js'; ?>'></script>
		<div id = "white-box"> 
			<div id="progress-container">
				<h2 id="ajax-progress-title"><?php  _e('Progress', 'woocommerce-customers-manager');?></h2>
				<div id="ajax-progress"></div>
				<div id="progressbar"></div>
				<form action="admin.php?page=woocommerce-customers-manager" method="post">
					<input type="hidden" value="" name="wccm_customers_ids" id="wccm_customers_ids"></input>
					<input type="hidden" value="" name="wccm_customers_emails" id="wccm_customers_emails"></input>
					<input type="hidden" value="" name="wccm_start_date" id="wccm_start_date"></input>
					<input type="hidden" value="" name="wccm_end_date" id="wccm_end_date"></input>
					<button class="button-primary" id="view_results_button"><?php  _e('Click here to view results', 'woocommerce-customers-manager');?> </button>
				</form>
			</div>
			
			<div id="option-box">
			<h2><?php  _e('Discover by orders', 'woocommerce-customers-manager');?></h2>
			<p><?php  _e('You can find customers who bought a particular set of products, have spent a certain amout, made order in a time range, .... ', 'woocommerce-customers-manager');?></p>
			
				<h4><?php  _e('Orders status', 'woocommerce-customers-manager');?></h4>
				<?php $statuses = $wccm_order_model->get_order_statuses_id_to_name(); ?>
				<div id="status-checkbox-box">
					<input type="hidden" id="statuses-version" value="<?php echo $statuses['version']; ?>"></input>
					<?php foreach($statuses['statuses'] as $key_or_slug => $status):
						echo ' <input type="checkbox" class="status-checkbox" value="'.$key_or_slug.'" checked/>'.$status.'<br />';
					endforeach;?>
				</div>
				
				<div style="display:none">
					<h4><?php  _e('Customers (you can search typing name, last name or email)', 'woocommerce-customers-manager');?></h4>
				
					<select class="js-data-customers-ajax" id="customer_ids" multiple='multiple'> 
					</select>
					
					<select class="" id="customer_relationship"> 
						<option value="or">OR</option>
						<option value="and">AND</option>
					</select>
				</div>
				
				<h4><?php  _e('Products (you can search typing product name, id or sku code)', 'woocommerce-customers-manager');?></h4>
			
				<select class="js-data-products-ajax" id="product_ids" multiple='multiple'> 
				</select>
				<select class="" id="product_relationship"> 
					<option value="or">OR</option>
					<option value="and">AND</option>
				</select>
				
				<!--<div class="spacer"></div>-->
				<h4><?php  _e('Relationship between products and categories filters', 'woocommerce-customers-manager');?></h4>
				<select class="" id="product_category_filters_relationship"> 
					<option value="or">OR</option>
					<option value="and">AND</option>
				</select>
				
				<h4><?php  _e('Products categories', 'woocommerce-customers-manager');?></h4>
				
				<select class="js-data-product-categories-ajax" id="category_ids" multiple='multiple'> 
				</select>
				<select class="" id="product_category_relationship"> 
						<option value="or">OR</option>
						<option value="and">AND</option>
				</select>
				
				<h4><?php  _e('Date', 'woocommerce-customers-manager');?></h4>
				<input class="range_datepicker" type="text" id="picker_start_date" name="start_date" value="" placeholder="<?php _e('Starting date', 'woocommerce-customers-manager' ); ?>" />
				<input class="range_datepicker" type="text" id="picker_end_date" name="end_date" value="" placeholder="<?php _e('Ending date', 'woocommerce-customers-manager' ); ?>" />
			
				<h4><?php  _e('Amount spent per single order', 'woocommerce-customers-manager');?></h4>
				<p>
				  <label for="amount"<?php  _e('Range', 'woocommerce-customers-manager');?></label>
				  <input type="text" id="amount" readonly style="border:0; color:#f6931f; font-weight:bold;">
				</p>
				<div id="slider-range"></div>
				
				<h4><?php  _e('Total amount spent (sum of all orders per customer)', 'woocommerce-customers-manager');?></h4>
				<p>
				  <label for="amount-total"<?php  _e('Range', 'woocommerce-customers-manager');?></label>
				  <input type="text" id="amount-total" readonly style="border:0; color:#f6931f; font-weight:bold;">
				</p>
				<div id="slider-range-total"></div>
				
			<button class="button-primary" id="start-export-button"><?php  _e('Discover', 'woocommerce-customers-manager');?></button>
			</div>
			
		</div>
		<script>
		var max_total_sale = <?php echo $max_total_sale ?>;
		var max_user_total_sale = <?php echo $max_user_total_sale ?>;
		var date_error = "<?php  _e('Starting date cannot be greater than ending date', 'woocommerce-customers-manager');?>";
		var csv_error = "<?php  _e('Field separator or Line breaker fields cannot be empty', 'woocommerce-customers-manager');?>";
		var statuses_error = "<?php  _e('Select at least one status', 'woocommerce-customers-manager');?>";
		</script>
		<?php
	}
}
?>