<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * WC_Deposits_Plans_Table class.
 *
 * @extends WP_List_Table
 */
class WC_Deposits_Plans_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct(){
		parent::__construct( array(
			'singular' => __( 'Payment Plan', 'ultimatewoo-pro' ),
			'plural'   => __( 'Payment Plans', 'ultimatewoo-pro' ),
			'ajax'     => false,
		) );
	}

	/**
	 * Output name.
	 *
	 * @param  object $item
	 * @return string
	 */
	public function column_plan_name( $item ) {
		$name = '
			<strong>
			  <a href="' . esc_url( add_query_arg( 'plan_id', $item->get_id(), admin_url( 'edit.php?post_type=product&page=deposit_payment_plans' ) ) ) . '">' . esc_html( $item->get_name() ) . '</a>
			</strong>
			<div class="row-actions">
				<a href="' . esc_url( add_query_arg( 'plan_id', $item->get_id(), admin_url( 'edit.php?post_type=product&page=deposit_payment_plans' ) ) ) . '">' . __( 'Edit', 'ultimatewoo-pro' ) . '</a> | <a href="' . wp_nonce_url( add_query_arg( 'delete_plan', $item->get_id(), admin_url( 'edit.php?post_type=product&page=deposit_payment_plans' ) ), 'delete_plan' ) . '" class="delete_plan">' . __( 'Delete', 'ultimatewoo-pro' ) . '</a>
			</div>
		';
		return $name;
	}

	/**
	 * Output description.
	 *
	 * @param  object $item
	 * @return string
	 */
	public function column_plan_description( $item ) {
		return wpautop( $item->get_description() );
	}

	/**
	 * Output the plans schedule.
	 *
	 * @param  object $item
	 * @return string
	 */
	public function column_schedule( $item ) {
		return $item->get_formatted_schedule();
	}

	/**
	 * get_columns function.
	 *
	 * @return  array
	 */
	public function get_columns(){
		return array(
			'plan_name'        => __( 'Name', 'ultimatewoo-pro' ),
			'plan_description' => __( 'Description', 'ultimatewoo-pro' ),
			'schedule'         => __( 'Schedule', 'ultimatewoo-pro' )
		);
	}

	/**
	 * Get bulk actions.
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * Get items to display.
	 */
	public function prepare_items() {
		$this->_column_headers = array( $this->get_columns(), array(), array() );
		$this->items           = WC_Deposits_Plans_Manager::get_plans();
	}
}
