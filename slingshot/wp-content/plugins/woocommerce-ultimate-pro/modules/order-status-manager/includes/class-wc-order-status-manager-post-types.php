<?php
/**
 * WooCommerce Order Status Manager
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Order Status Manager to newer
 * versions in the future. If you wish to customize WooCommerce Order Status Manager for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-order-status-manager/ for more information.
 *
 * @package     WC-Order-Status-Manager/Classes
 * @author      SkyVerge
 * @copyright   Copyright (c) 2015-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Order Status Manager Post Types
 *
 * @since 1.0.0
 */
class WC_Order_Status_Manager_Post_Types {


	/**
	 * Initialize and register the Order Status Manager post types
	 *
	 * @since 1.0.0
	 */
	public static function initialize() {

		self::init_user_roles();
		self::init_post_types();
		self::register_post_status();

		add_filter( 'post_updated_messages',      array( __CLASS__, 'updated_messages' ) );
		add_filter( 'bulk_post_updated_messages', array( __CLASS__, 'bulk_updated_messages' ), 10, 2 );
	}


	/**
	 * Init WooCommerce Order Status Manager user roles
	 *
	 * @since 1.0.0
	 */
	private static function init_user_roles() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'shop_manager',  'manage_woocommerce_order_status_emails' );
			$wp_roles->add_cap( 'administrator', 'manage_woocommerce_order_status_emails' );
		}
	}


	/**
	 * Init WooCommerce Order Status Manager post types
	 *
	 * @since 1.0.0
	 */
	private static function init_post_types() {

		// Register wc_order_status post type for custom order statuses
		register_post_type( 'wc_order_status', array(
			'labels' => array(
				'name'               => __( 'Order Statuses', 'ultimatewoo-pro' ),
				'singular_name'      => __( 'Order Status', 'ultimatewoo-pro' ),
				'menu_name'          => _x( 'Order Statuses', 'Admin menu name', 'ultimatewoo-pro' ),
				'add_new'            => __( 'Add Order Status', 'ultimatewoo-pro' ),
				'add_new_item'       => __( 'Add New Order Status', 'ultimatewoo-pro' ),
				'edit'               => __( 'Edit', 'ultimatewoo-pro' ),
				'edit_item'          => __( 'Edit Order Status', 'ultimatewoo-pro' ),
				'new_item'           => __( 'New Order Status', 'ultimatewoo-pro' ),
				'view'               => __( 'View Order Statuses', 'ultimatewoo-pro' ),
				'view_item'          => __( 'View Order Status', 'ultimatewoo-pro' ),
				'search_items'       => __( 'Search Order Statuses', 'ultimatewoo-pro' ),
				'not_found'          => __( 'No Order Statuses found', 'ultimatewoo-pro' ),
				'not_found_in_trash' => __( 'No Order Statuses found in trash', 'ultimatewoo-pro' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'capability_type'     => 'post',
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_menu'        => false,
			'hierarchical'        => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array(
				'title',
				'page-attributes',
			),
			'show_in_nav_menus'   => false,
		));

		// Register wc_order_email post type for custom emails
		// Note - can't use wc_order_status_email, as that is 1 character too long (max 20 chars)
		register_post_type( 'wc_order_email', array (
				'labels' => array(
					'name'               => __( 'Order Status Emails', 'ultimatewoo-pro' ),
					'singular_name'      => __( 'Order Status Email', 'ultimatewoo-pro' ),
					'menu_name'          => _x( 'Order Status Emails', 'Admin menu name', 'ultimatewoo-pro' ),
					'add_new'            => __( 'Add Order Status Email', 'ultimatewoo-pro' ),
					'add_new_item'       => __( 'Add New Order Status Email', 'ultimatewoo-pro' ),
					'edit'               => __( 'Edit', 'ultimatewoo-pro' ),
					'edit_item'          => __( 'Edit Order Status Email', 'ultimatewoo-pro' ),
					'new_item'           => __( 'New Order Status Email', 'ultimatewoo-pro' ),
					'view'               => __( 'View Order Status Emails', 'ultimatewoo-pro' ),
					'view_item'          => __( 'View Order Status Email', 'ultimatewoo-pro' ),
					'search_items'       => __( 'Search Order Status Emails', 'ultimatewoo-pro' ),
					'not_found'          => __( 'No Order Status Emails found', 'ultimatewoo-pro' ),
					'not_found_in_trash' => __( 'No Order Status Emails found in trash', 'ultimatewoo-pro' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'capability_type'     => 'post',
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_menu'        => false,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array(
					'title',
				),
				'show_in_nav_menus'   => false,
		) );
	}


	/**
	 * Customize order status & email updated messages
	 *
	 * @since 1.0.0
	 * @param array $messages Original messages
	 * @return array $messages Modified messages
	 */
	public static function updated_messages( $messages ) {

		$post             = get_post();
		$post_type        = get_post_type( $post );

		$messages['wc_order_status'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Order Status saved.', 'ultimatewoo-pro' ),
			2  => __( 'Custom field updated.', 'ultimatewoo-pro' ),
			3  => __( 'Custom field deleted.', 'ultimatewoo-pro' ),
			4  => __( 'Order Status saved.', 'ultimatewoo-pro' ),
			5  => '', // Unused for order statuses
			6  => __( 'Order Status saved.', 'ultimatewoo-pro' ), // Original: Post published
			7  => __( 'Order Status saved.', 'ultimatewoo-pro' ),
			8  => '', // Unused for order statuses
			9  => '', // Unused for order statuses
			10 => __( 'Order Status saved.', 'ultimatewoo-pro' ), // Original: Post draft updated
		);

		$customize_email_link = sprintf( ' <a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_order_status_email_' . esc_attr( $post->ID ) ) ), __( 'Customize Email', 'ultimatewoo-pro' ) );

		$messages['wc_order_email'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Order Status Email saved.', 'ultimatewoo-pro' ) . $customize_email_link,
			2  => __( 'Custom field updated.', 'ultimatewoo-pro' ),
			3  => __( 'Custom field deleted.', 'ultimatewoo-pro' ),
			4  => __( 'Order Status Email saved.', 'ultimatewoo-pro' ) . $customize_email_link,
			5  => '', // Unused for order statuses
			6  => __( 'Order Status Email saved.', 'ultimatewoo-pro' ) . $customize_email_link, // Original: Post published
			7  => __( 'Order Status Email saved.', 'ultimatewoo-pro' ) . $customize_email_link,
			8  => '', // Unused for order statuses
			9  => '', // Unused for order statuses
			10 => __( 'Order Status Email saved.', 'ultimatewoo-pro' ) . $customize_email_link, // Original: Post draft updated
		);

		return $messages;
	}


	/**
	 * Customize order status & email bulk updated messages
	 *
	 * @since 1.0.0
	 * @param array $messages Original messages
	 * @param array $bulk_counts
	 * @return array $messages Modified messages
	 */
	public static function bulk_updated_messages( $messages, $bulk_counts ) {

		$messages['wc_order_status'] = array(
			'updated'   => _n( '%s order status updated.', '%s order statuses updated.', $bulk_counts['updated'], 'ultimatewoo-pro' ),
			'locked'    => _n( '%s order status not updated, somebody is editing it.', '%s order statuses not updated, somebody is editing them.', $bulk_counts['locked'], 'ultimatewoo-pro' ),
			'deleted'   => _n( '%s order status permanently deleted.', '%s order statuses permanently deleted.', $bulk_counts['deleted'], 'ultimatewoo-pro' ),
			'trashed'   => _n( '%s order status moved to the Trash.', '%s order statuses moved to the Trash.', $bulk_counts['trashed'], 'ultimatewoo-pro' ),
			'untrashed' => _n( '%s order status restored from the Trash.', '%s order statuses restored from the Trash.', $bulk_counts['untrashed'], 'ultimatewoo-pro' ),
		);

		$messages['wc_order_email'] = array(
			'updated'   => _n( '%s order status email updated.', '%s order status emails updated.', $bulk_counts['updated'], 'ultimatewoo-pro' ),
			'locked'    => _n( '%s order status email not updated, somebody is editing it.', '%s order status emails not updated, somebody is editing them.', $bulk_counts['locked'], 'ultimatewoo-pro' ),
			'deleted'   => _n( '%s order status email permanently deleted.', '%s order status emails permanently deleted.', $bulk_counts['deleted'], 'ultimatewoo-pro' ),
			'trashed'   => _n( '%s order status email moved to the Trash.', '%s order status emails moved to the Trash.', $bulk_counts['trashed'], 'ultimatewoo-pro' ),
			'untrashed' => _n( '%s order status email restored from the Trash.', '%s order status emails restored from the Trash.', $bulk_counts['untrashed'], 'ultimatewoo-pro' ),
		);

		return $messages;
	}


	/**
	 * Register custom order statuses for orders
	 *
	 * @since 1.0.0
	 */
	public static function register_post_status() {

		foreach ( wc_get_order_statuses() as $slug => $name ) {

			// Don't register manually registered statuses
			if ( wc_order_status_manager()->get_order_statuses_instance()->is_core_status( $slug ) ) {
				continue;
			}

			register_post_status( $slug, array(
				'label'                     => $name,
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( $name . ' <span class="count">(%s)</span>', $name . ' <span class="count">(%s)</span>', 'ultimatewoo-pro' ),
			) );
		}
	}


}
