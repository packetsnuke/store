<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_SA_AJAX.
 *
 * AJAX Event Handler.
 *
 * @class    WC_SA_AJAX
 * @version  1.0.0
 * @package  WC_SA/Classes
 * @category Class
 */
class WC_SA_AJAX {

	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'define_ajax' ), 0 );
		self::add_ajax_events();
	}

	/**
	 * Set WC AJAX constant and headers.
	 */
	public static function define_ajax() {
		if ( ! empty( $_GET['wc-ajax'] ) ) {
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			if ( ! defined( 'WC_DOING_AJAX' ) ) {
				define( 'WC_DOING_AJAX', true );
			}
			// Turn off display_errors during AJAX events to prevent malformed JSON
			if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
				@ini_set( 'display_errors', 0 );
			}
			$GLOBALS['wpdb']->hide_errors();
		}
	}

	/**
	 * Send headers for WC Ajax Requests
	 * @since 2.5.0
	 */
	private static function wc_ajax_headers() {
		send_origin_headers();
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		@header( 'X-Robots-Tag: noindex' );
		send_nosniff_header();
		nocache_headers();
		status_header( 200 );
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		// woocommerce_EVENT => nopriv
		$ajax_events = array(
			'mark_order_status' => true,
			'sort'              => false,
			'search_order'      => false,
			'search_order_bulk' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_wc_sa_' . $ajax_event, array( __CLASS__, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_wc_sa_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}

	public function mark_order_status()
	{
		if ( !check_admin_referer( 'wc-sa-mark-order-status' ))
            wp_die( __( 'You have taken too long. Please go back and retry.', 'woocommerce_status_actions' ) );

		do_action('before_process_custom_action');

		$slug     = sanitize_text_field( $_GET['status'] );
		$status   = wc_sa_get_status_by_name($slug);
		$order_id = absint( $_GET['order_id'] );

		if ( !current_user_can('edit_shop_orders') ){
            if($status->customer_account != 'yes')
                wp_die( __( 'You do not have sufficient permissions to access this page.', 'wc_point_of_sale' ) );
        }

		if ( wc_is_order_status( 'wc-' . $slug ) && $order_id ) {
			$order = wc_get_order( $order_id );
			$note  = apply_filters('process_custom_action_note', '', $slug, $order );
			$order->update_status( $slug, $note, true );
			do_action( 'woocommerce_order_edit_status', $order_id, $slug );
		}

		do_action('after_process_custom_action');

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=shop_order' ) );			
		die();
	}

	public function search_order_bulk(){
		global $the_order, $post;
		
		$order_rows = '';
		$orders     = $_POST['orders'];
		$status     = sanitize_text_field($_POST['status']);
		if( !empty($orders) && wc_is_order_status( $status ) ){
			foreach ($orders as $order_id) {
				$post = get_post($order_id);
		
				if( !$post || $post->post_type != 'shop_order'){
					continue;
				}

				if ( $status != $post->post_status) {
					$order = wc_get_order( $order_id );
					$note  = apply_filters('process_custom_action_note', '', $status, $order );
					$order->update_status( $status, $note, true );
					do_action( 'woocommerce_order_edit_status', $order_id, $status );
				}

				$the_order = wc_get_order( $order_id );
				ob_start();
				$columns = array('order_status', 'order_title', 'order_items', 'shipping_address', 'order_date', 'order_total');
				?>
				<tr id="order-row-<?php echo $order_id; ?>" class="iedit author-self level-0 post-<?php echo $order_id; ?> type-shop_order status-wc-completed post-password-required hentry">
					<?php 
					foreach ($columns as $column) {
						?>
						<td class="<?php echo $column; ?> column-<?php echo $column; ?>" >
						<?php if($column == 'order_title'){ ?>
							<input class="row_order_id" value="<?php echo $order_id; ?>" type="hidden">
						<?php } ?>
						<?php do_action('manage_shop_order_posts_custom_column', $column); ?>
						</td>
						<?php
					}
					?>
				</tr>
				<?php
				$order_rows .= ob_get_clean();
			}


		}
		$data = array(
			'result'    => !empty($order_rows) ? 'success' : 'failures',
			'order_rows' => $order_rows,
		);
		wp_send_json( $data );
		die();

	}
	public function search_order()
	{
		global $the_order, $post;

		$messages  = '';
		$order_row = '';

		$order_id  = absint( $_POST['order_id'] );
		$post      = get_post($order_id);
		
		if( !$post || $post->post_type != 'shop_order'){
			$messages  = sprintf(__('#%d is not valid order number', 'wc_point_of_sale'), $order_id);
		}
		if( empty( $messages ) ){

			if( isset($_POST['status'])){
				$slug     = sanitize_text_field( $_POST['status'] );
				if ( wc_is_order_status( $slug ) && $order_id && $slug != $the_order->post_status) {
					$order = wc_get_order( $order_id );
					$note  = apply_filters('process_custom_action_note', '', $slug, $order );
					$order->update_status( $slug, $note, true );
					do_action( 'woocommerce_order_edit_status', $order_id, $slug );
				}
			}

			$the_order = wc_get_order( $order_id );
			ob_start();
			$columns = array('order_status', 'order_title', 'order_items', 'shipping_address', 'order_date', 'order_total');
			?>
			<tr id="order-row-<?php echo $order_id; ?>" class="iedit author-self level-0 post-<?php echo $order_id; ?> type-shop_order status-wc-completed post-password-required hentry">
				<?php 
				foreach ($columns as $column) {
					?>
					<td class="<?php echo $column; ?> column-<?php echo $column; ?>" >
					<?php if($column == 'order_title'){ ?>
						<input class="row_order_id" value="<?php echo $order_id; ?>" type="hidden">
					<?php } ?>
					<?php do_action('manage_shop_order_posts_custom_column', $column); ?>
					</td>
					<?php
				}
				?>
			</tr>
			<?php
			$order_row = ob_get_clean();

		}	
		
		$data = array(
			'order_row' => $order_row,
			'result'    => empty( $messages ) ? 'success' : $messages,
		);

		wp_send_json( $data );
		die();
	}

	public function sort()
	{
        global $wpdb;
		// check permissions again and make sure we have what we need
		if ( ! current_user_can( 'edit_posts' ) || !isset($_POST['id']) || empty( $_POST['id'] ) ) {
			die(-1);
		}

		$id       = (int) $_POST['id'];
		$next_id  = isset( $_POST['nextid'] ) && (int) $_POST['nextid'] ? (int) $_POST['nextid'] : null;
		$post     = get_post( $id );

		if ( ! $id || ! $post ) {
			die(0);
		}
		$posttype = 'wc_custom_statuses';

		wc_sa_reorder_statuses( $post, $next_id, $posttype );
        die();
	}


}

WC_SA_AJAX::init();