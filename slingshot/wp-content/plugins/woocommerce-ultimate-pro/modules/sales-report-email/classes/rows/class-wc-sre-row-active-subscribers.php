<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WC_SRE_Row_Active_Subscribers extends WC_SRE_Report_Row {

	/**
	 * The constructor
	 *
	 * @param $date_range
	 *
	 * @access public
	 * @since  1.1.0
	 */
	public function __construct( $date_range ) {
		parent::__construct( $date_range, 'active-subscribers', __( 'Active Subscribers', 'ultimatewoo-pro' ) );
	}

	/**
	 * Prepare the data
	 *
	 * @access public
	 * @since  1.1.0
	 */
	public function prepare() {

		$subscriptions = WC_Subscriptions_Manager::get_all_users_subscriptions();

		$active_subscription_count = 0;
		
		foreach ( $subscriptions as $id => $subscribers ) {
			foreach ( $subscribers as $key => $subscription ) {
				if ( isset( $subscription[ 'status' ]  ) && 'active' === $subscription['status'] ) {
					$active_subscription_count++;
				}
			}
		}

		$this->set_value( $active_subscription_count );
	}

}