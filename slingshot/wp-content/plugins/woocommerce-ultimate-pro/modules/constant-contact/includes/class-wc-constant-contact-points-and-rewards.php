<?php
/**
 * WooCommerce Constant Contact
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Constant Contact to newer
 * versions in the future. If you wish to customize WooCommerce Constant Contact for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-constant-contact/ for more information.
 *
 * @package     WC-Constant-Contact/Classes
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Points and Rewards class
 *
 * Handles all Points and Rewards-related actions
 *
 * @since 1.1
 */
class WC_Constant_Contact_Points_and_Rewards {


	/**
	 * Add hooks
	 *
	 * @since 1.1
	 */
	public function __construct() {

		// add points when the customer subscribes
		add_action( 'wc_constant_contact_customer_subscribed', array( $this, 'add_points_to_user' ) );

		// customize the points description shown to user/admin for the sign up
		add_filter( 'wc_points_rewards_event_description', array( $this, 'render_points_description' ), 10, 3 );

		// add setting for points earned upon signup
		if ( is_admin() && ! is_ajax() ) {
			add_filter( 'wc_points_rewards_action_settings', array( $this, 'add_settings' ), 12 );
		}
	}


	/**
	 * Adds the Constant Contact actions integration settings
	 *
	 * @since 1.1
	 * @param array $settings the settings array
	 * @return array the settings array
	 */
	public function add_settings( $settings ) {

		$settings = array_merge(
			$settings,
			array(
				array(
					'title'    => __( 'Points earned for a Constant Contact sign up', 'ultimatewoo-pro' ),
					'desc_tip' => __( 'Enter the amount of points earned when a customer signs up to an email list via Constant Contact.', 'ultimatewoo-pro' ),
					'id'       => 'wc_constant_contact_points',
				)
			)
		);

		return $settings;
	}


	/**
	 * Inject the setting for the amount of points a user should earn on
	 * registration with Constant Contact
	 *
	 * @since 1.1
	 */
	public function add_points_to_user() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		$points = get_option( 'wc_constant_contact_points' );

		if ( ! empty( $points ) ) {
			WC_Points_Rewards_Manager::increase_points( get_current_user_id(), $points, 'constant-contact' );
		}
	}


	/**
	 * Render a custom description for points added for an email signup, otherwise
	 * the manage table/my points area shows a blank description
	 *
	 * @since 1.1
	 * @return string
	 */
	public function render_points_description( $description, $event_type, $event ) {
		global $wc_points_rewards;

		if ( 'constant-contact' !== $event_type ) {
			return $description;
		}

		$points_label = $wc_points_rewards->get_points_label( $event ? $event->points : null );

		return sprintf( __( '%s earned for email sign-up', 'ultimatewoo-pro' ), $points_label );
	}


} // end \WC_Constant_Contact_Points_and_Rewards class
