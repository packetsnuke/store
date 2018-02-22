<?php
/**
 *
 * @author   Actuality Extensions
 * @category Admin
 * @package  WC_SA/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WC_SA_Bulk' ) ) :

/**
 * WC_SA_Bulk Class
 */
class WC_SA_Bulk {

	public static function init()
	{
		add_action( 'admin_menu', array(__CLASS__, 'admin_menu') );
		add_action( 'admin_head', array( __CLASS__, 'menu_highlight' ) );
		add_action( 'admin_footer', array( __CLASS__, 'bulk_admin_footer' ), 99 );
		add_action( 'manage_posts_extra_tablenav', array( __CLASS__, 'bulk_change_status_button' ), 99 );
	}

	public static function menu_highlight()
	{
	    global $submenu_file, $parent_file, $submenu;
	    
	    if( isset($submenu['woocommerce']) ){
	    	foreach ($submenu['woocommerce'] as $key => $s_menu) {
	    		if( $s_menu[2] == 'wc_bulk_change_status' ){
	    			unset($submenu['woocommerce'][$key]);
	    		}
	    	}
	    }
	    if ( isset($_GET['page']) && $_GET['page'] == 'wc_bulk_change_status') {
			$parent_file = 'woocommerce';
			$submenu_file = 'edit.php?post_type=shop_order';
		}
	}

	public static function admin_menu()
	{
	    add_submenu_page(
	        'woocommerce',
	        __('Bulk Actions Scanner', 'wc_point_of_sale'),
	        __('Bulk Actions Scanner', 'wc_point_of_sale'),
	        'manage_woocommerce',
	        'wc_bulk_change_status',
	        array(__CLASS__, 'output')
	    );
	}
	public static function output()
	{
		$default_statuses  = wc_sa_get_default_order_statuses();
		$order_statuses    = wc_sa_get_statuses();
		?>
		<style>
			.filter-items #apply-automatically{
				margin-top: 3px;
			}
			.filter-items{
				padding: 12px 0;
			}
		</style>
		<div class="wrap" id="posts-filter">
			<h1 class="wp-heading-inline"><?php _e( 'Bulk Actions Scanner', 'wc_point_of_sale' ); ?></h1>
			<p class="description"><?php _e( 'This page is for users who want to change order statuses instantly. Simply scan the order number and the order will load below. Configure whether you want to apply your status after or automatically below.', 'wc_point_of_sale' ); ?></p>
			<ht class="wp-header-end"></ht>
			<div class="wp-filter" style="margin-bottom: 15px;">
				<div class="filter-items">
					<select id="order-status" style="margin-right: 10px;">
						<option value=""><?php _e('Bulk Actions', 'woocommerce_status_actions'); ?></option>
						<optgroup label="<?php _e('WooCommerce statuses', 'woocommerce_status_actions'); ?>">
						<?php foreach ($default_statuses as $key => $value) { ?>
							<option value="<?php echo $key; ?>"><?php echo $value; ?></option>								
						<?php } ?>
						</optgroup>
						<optgroup label="<?php _e('Custom statuses', 'woocommerce_status_actions'); ?>">
						<?php foreach ($order_statuses as $key => $value) { ?>
							<option value="wc-<?php echo $value->label; ?>"><?php echo $value->title; ?></option>								
						<?php } ?>
						</optgroup>
					</select>
					<button class="button" type="button" id="change_status" style="margin-right: 10px;"><?php _e( 'Apply', 'wc_point_of_sale' ); ?></button>
					<button class="button" type="button" id="clear-table" style="margin-right: 10px;"><?php _e( 'Clear Orders', 'wc_point_of_sale' ); ?></button>
					<label for="apply-automatically" style="margin-right: 10px;"><?php _e( 'Apply Automatically', 'wc_point_of_sale' ); ?></label>
					<input type="checkbox" id="apply-automatically">
				</div>

				<form class="search-form" id="search-order-form">
					<input placeholder="<?php _e( 'Search orders...', 'wc_point_of_sale' ); ?>" id="order-number" class="search" value="" type="search">
				</form>
			</div>
			<div id="orders-list">
				<div class="woocommerce-BlankState">
					<h2 class="woocommerce-BlankState-message"><?php _e( 'When you scan the order number, the order will appear here.', 'wc_point_of_sale' ); ?></h2>
				</div>
				<table class="wp-list-table widefat fixed striped posts">
					<thead>
						<tr>
							<th id="order_status" class="manage-column column-order_status" scope="col">
								<span class="status_head tips"><?php _e('Status', 'wc_point_of_sale'); ?></span>
							</th>
							<th scope="col" id="order_title" class="manage-column column-order_title column-primary"><?php _e( 'Order', 'wc_point_of_sale' ); ?></th>
							<th scope="col" id="order_items" class="manage-column column-order_items"><?php _e( 'Purchased', 'wc_point_of_sale' ); ?></th>
							<th scope="col" id="shipping_address" class="manage-column column-shipping_address"><?php _e( 'Ship to', 'wc_point_of_sale' ); ?> to</th>
							<th scope="col" id="order_date" class="manage-column column-order_date sortable desc"><?php _e( 'Date', 'wc_point_of_sale' ); ?></th>
							<th scope="col" id="order_total" class="manage-column column-order_total sortable desc"><?php _e( 'Total', 'wc_point_of_sale' ); ?></th>
						</tr>
					</thead>
					<tbody id="the-list">
					</tbody>
					<tfoot>
						<tr>
							<th id="order_status" class="manage-column column-order_status" scope="col">
								<span class="status_head tips"><?php _e('Status', 'wc_point_of_sale'); ?></span>
							</th>
							<th scope="col" class="manage-column column-order_title column-primary"><?php _e( 'Order', 'wc_point_of_sale' ); ?></th>
							<th scope="col" class="manage-column column-order_items"><?php _e( 'Purchased', 'wc_point_of_sale' ); ?></th>
							<th scope="col" class="manage-column column-shipping_address"><?php _e( 'Ship to', 'wc_point_of_sale' ); ?></th>
							<th scope="col" class="manage-column column-order_date sortable desc"><?php _e( 'Date', 'wc_point_of_sale' ); ?></th>
							<th scope="col" class="manage-column column-order_total sortable desc"><?php _e( 'Total', 'wc_point_of_sale' ); ?></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
		<?php
	}

	public static function display_messages()
	{
		$i = 0;
		if(isset($_GET['message']) && !empty($_GET['message']) ) $i = $_GET['message'];
		$messages = array(
			 0 => '', // Unused. Messages start at index 1.
			 1 => '<div id="message" class="updated"><p>'.  __('Barcode Template created.') . '</p></div>',
			 2 => '<div id="message" class="updated"><p>'. __('Barcode Template updated.') . '</p></div>',
		);
		return $messages[$i];
	}
	public static function bulk_admin_footer()
	{
		global $post_type;

		if ( 'shop_order' == $post_type ) {
			?>
			<script type="text/javascript" id="sa-status-bulk-actions">
			jQuery(function() {
				jQuery('.wp-header-end').before( jQuery('#bulk_change_status') );
			});
			</script>
			<?php
		}
	}

	public static function bulk_change_status_button($which)
	{
		global $post_type;
		if ( 'top' === $which && !is_singular()  && 'shop_order' == $post_type) {

			$args = array( 'page' => 'wc_bulk_change_status' );
			$admin_url = trailingslashit( get_admin_url() );
			$url = $admin_url . 'admin.php';
			$url = add_query_arg( $args, $url )
			?>
			<div class="alignleft actions">
				<a href="<?php echo $url; ?>" id="bulk_change_status" class="page-title-action"><?php _e( 'Bulk Actions Scanner', 'wc_point_of_sale' ); ?></a>
			</div>
			<?php
		}
	}

}
WC_SA_Bulk::init();
endif;