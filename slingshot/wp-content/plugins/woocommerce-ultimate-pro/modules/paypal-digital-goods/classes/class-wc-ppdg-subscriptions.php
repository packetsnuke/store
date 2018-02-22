<?php
/**
 * A middle layer interacting with WooCommerce Subscriptions
 *
 * @package		PayPal Digital Goods
 * @author		Brent Shepherd
 * @since		1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_PPDG_Subscriptions {

	protected $gateway;

	/**
	 * When a subscriber or store manager cancel's a subscription in the store, suspend it with PayPal
	 */
	public function __construct( $paypal_gateway ) {

		$this->gateway = $paypal_gateway;

		add_action( 'woocommerce_subscription_expired_' . $this->gateway->id, array( &$this, 'cancel' ) );
		add_action( 'woocommerce_subscription_cancelled_' . $this->gateway->id, array( &$this, 'cancel' ) );
		add_action( 'woocommerce_subscription_on-hold_' . $this->gateway->id, array( &$this, 'suspend' ) );
		add_action( 'woocommerce_subscription_pending-cancel_' . $this->gateway->id, array( &$this, 'suspend' ) );
		add_action( 'woocommerce_subscription_activated_' . $this->gateway->id, array( &$this, 'activate' ) );
	}

	/**
	 * Check if an order contains a subscription
	 *
	 * @since 3.2
	 */
	public function order_contains_subscription( $order ) {

		if ( wcs_order_contains_subscription( $order, array( 'parent', 'switch' ) ) || wcs_is_subscription( $order ) ) { // Subscriptions v2.0+
			$order_contains_subscription = true;
		} else {
			$order_contains_subscription = false;
		}

		return $order_contains_subscription;
	}

	/**
	 * Generate a PayPal Digital Goods Subscription object for Subscriptions v2.0+
	 *
	 * @since 3.2
	 */
	public function process_subscription_sign_up( $order, $transaction_details ) {

		// Always store PP details on order
		ppdg_update_paypal_details( $order->id, $transaction_details );

		$subscription = $this->get_subscription( $order );

		// Subscription no longer exists
		if ( empty( $subscription ) ) {
			WC_PPDG_Logger::add( 'Subscription Sign Up Cannot Be Processed: Empty Subscription.' );
			return;
		}

		// Store the transaction details on the subscription in v2.0 as well as the order
		if ( is_object( $subscription ) ) {
			ppdg_update_paypal_details( $subscription->id, $transaction_details );
		}

		switch( strtolower( $transaction_details['STATUS'] ) ) {

			case 'active' :

				if ( $this->has_status( $subscription, 'active' ) ) {
					return;
				}

				// Activate Subscription
				WC_Subscriptions_Manager::activate_subscriptions_for_order( $order );

				// Payment completed
				$this->add_order_note( $order, __( 'Subscription Activated via PayPal Digital Goods for Express Checkout', 'ultimatewoo-pro' ) );

				$order->payment_complete();

				$cron_args = array( 'order_id' => (int)$order->id, 'profile_id' => $transaction_details['PROFILEID'] );

				// Check the subscription's status every 12 hours, just in case IPN is not active
				if ( false === wp_next_scheduled( 'ppdg_check_subscription_status', $cron_args ) ) {
					wp_schedule_event( time() + 60 * 60 * 24, 'twicedaily', 'ppdg_check_subscription_status', $cron_args );
				}

				WC_PPDG_Logger::add( 'Subscription Activated via PayPal Digital Goods.' );
				break;

			case 'pending' :
				$order->update_status( 'pending', __( 'Subscription Activation via PayPal Digital Goods Pending.', 'ultimatewoo-pro' ) );

				// Check again in 45 seconds, just in case IPN is not active
				wp_schedule_single_event( time() + 45, 'ppdg_check_subscription_status', array( 'order_id' => (int)$order->id, 'profile_id' => $transaction_details['PROFILEID'] ) );

				WC_PPDG_Logger::add( __( 'Subscription Activation via PayPal Digital Goods Pending.', 'ultimatewoo-pro' ) );
				break;

			case 'cancelled' :
				if ( $this->has_status( $subscription, 'cancelled' ) ) {
					return;
				}

				// Cancel Subscription
				WC_Subscriptions_Manager::cancel_subscriptions_for_order( $order );

				// Payment completed
				$this->add_order_note( $order, __( 'Subscription Cancelled via PayPal Digital Goods for Express Checkout', 'ultimatewoo-pro' ) );

				// Clear scheduled check of subscription's status
				wp_clear_scheduled_hook( 'ppdg_check_subscription_status', array( 'order_id' => (int)$order->id, 'profile_id' => $profile_id ) );

				WC_PPDG_Logger::add( 'Subscription Cancelled via PayPal Digital Goods.' );
				break;

			case 'suspended' :

				if ( $this->has_status( $subscription, 'cancelled' ) ) {
					break;
				}

				// Cancel Subscription
				WC_Subscriptions_Manager::put_subscription_on_hold_for_order( $order );

				// Payment completed
				$this->add_order_note( $order, __( 'Subscription Suspended via PayPal Digital Goods for Express Checkout', 'ultimatewoo-pro' ) );

				WC_PPDG_Logger::add( 'Subscription Suspended via PayPal Digital Goods.' );
				break;
			default:
				WC_PPDG_Logger::add( 'In process_subscription_sign_up() with no status action, transaction details = ' . print_r( $transaction_details, true ) );
				break;
		}
	}

	/**
	 * Generate a PayPal Digital Goods Subscription object for Subscriptions v2.0+
	 *
	 * @since 3.2
	 */
	public function process_ipn_request( $order, $request ) {

		switch( $request['txn_type'] ) {
			case 'recurring_payment':

				if ( 'completed' == strtolower( $request['payment_status'] ) ) {
					// Store PayPal Details
					$payment_transaction_ids = get_post_meta( $order->id, '_payment_transaction_ids', true );

					if ( empty( $payment_transaction_ids ) ) {
						$payment_transaction_ids = array();
					}

					$payment_transaction_ids[] = $request['txn_id'];

					update_post_meta( $order->id, '_payment_transaction_ids', $payment_transaction_ids );

					// Subscriptions will move this to the renewal order and restore the original transaction ID
					update_post_meta( $order->id, '_transaction_id', $request['txn_id'] );

					// Keep a note of the payment
					$this->add_order_note( $order, __( 'IPN subscription payment completed via PayPal Digital Goods.', 'ultimatewoo-pro' ) );

					WC_Subscriptions_Manager::process_subscription_payments_on_order( $order->id );

					WC_PPDG_Logger::add( 'IPN subscription payment completed for order ' . $order->id . ' via PayPal Digital Goods.' );

				} else {

					WC_PPDG_Logger::add( 'IPN subscription payment notification received for order ' . $order->id  . ' with status ' . $request['payment_status'] );

				}

				break;

			case 'recurring_payment_failed' :
			case 'recurring_payment_suspended_due_to_max_failed_payment' :

				$this->add_order_note( $order, __( 'IPN subscription payment failed via PayPal Digital Goods.', 'ultimatewoo-pro' ) );

				WC_Subscriptions_Manager::process_subscription_payment_failure_on_order( $order );

				WC_PPDG_Logger::add( 'IPN subscription payment failed for order ' . $order->id . ' via PayPal Digital Goods.' );
				break;

			case 'recurring_payment_profile_created' :
			case 'recurring_payment_profile_cancel' :
			case 'recurring_payment_suspended' :

				$transaction_details = array(
					'PROFILEID'        => $request['recurring_payment_id'],
					'PROFILEREFERENCE' => $order->id,
					'STATUS'           => $request['profile_status'],
					'EMAIL'            => $request['payer_email'],
					'FIRSTNAME'        => $request['first_name'],
					'LASTNAME'         => $request['last_name'],
				);

				if ( isset( $request['payment_type'] ) ) {
					$transaction_details['PAYMENTTYPE'] = $request['payment_type'];
				}

				if ( isset( $request['initial_payment_txn_id'] ) ) {
					$transaction_details['TRANSACTIONID'] = $request['initial_payment_txn_id'];
				}

				$this->process_subscription_sign_up( $order, $transaction_details );
				break;

			default :
				WC_PPDG_Logger::add( sprintf( __( 'In PayPal Digital Goods process_ipn_request with no txn_type action. Request = %s', 'ultimatewoo-pro' ), print_r( $request, true ) ) );
				break;
		}
	}

	/**
	 * Generate a PayPal Digital Goods Subscription object for Subscriptions v2.0+
	 *
	 * @since 3.2
	 */
	public function get_paypal_object( $order ) {
		global $woocommerce;

		if ( wcs_is_subscription( $order ) ) {
			$subscription = $order;
		} else {
			$subscriptions = wcs_get_subscriptions_for_order( $order );
			$subscription  = array_pop( $subscriptions ); // Only one subscription allowed per order with PayPal
		}

		$recurring_amount      = $subscription->get_total();
		$sign_up_fee_total     = $subscription->get_sign_up_fee();

		$subscription_interval = $subscription->billing_interval;
		$start_timestamp       = $subscription->get_time( 'start' );
		$trial_end_timestamp   = $subscription->get_time( 'trial_end' );
		$end_timestamp         = $subscription->get_time( 'end' );

		if ( $trial_end_timestamp > 0 ) {
			$trial_length        = wcs_estimate_periods_between( $start_timestamp, $trial_end_timestamp, $subscription->trial_period );
			$subscription_length = wcs_estimate_periods_between( $trial_end_timestamp, $subscription->get_time( 'end' ), $subscription->billing_period );
		} else {
			$trial_length        = 0;
			$subscription_length = wcs_estimate_periods_between( $start_timestamp, $subscription->get_time( 'end' ), $subscription->billing_period );
		}

		$is_synced_subscription = WC_Subscriptions_Synchroniser::subscription_contains_synced_product( $subscription->id ) || WC_Subscriptions_Synchroniser::cart_contains_synced_subscription();

		// If the subscription is for one billing period with no free trial, just process it as a normal transaction
		if ( $subscription_length == $subscription_interval && 0 == $trial_length && false == $is_synced_subscription && ( is_checkout() || 'wp_ajax_ppdg_do_express_checkout' == current_filter() || 'wp_ajax_nopriv_ppdg_do_express_checkout' == current_filter() ) ) {
			return $this->gateway->get_purchase_object( $order );
		}

		$paypal_args = array(
			// Payment Info
			'invoice_number' => $this->gateway->invoice_prefix . $order->id,
			'custom'         => $order->order_key,
			'BUTTONSOURCE'   => 'WooThemes_Cart',
			// Price
			'amount'         => $recurring_amount,
			'average_amount' => $recurring_amount,
			// Temporal Details
			'start_date'     => apply_filters( 'woocommerce_paypal_digital_goods_subscription_start_date', gmdate( 'Y-m-d\TH:i:s', gmdate( 'U' ) + ( 13 * 60 * 60 ) ), $order ),
			'frequency'      => '1',
		);

		if ( $is_synced_subscription ) {
			$paypal_args['start_date'] = gmdate( 'Y-m-d\TH:i:s', $subscription->get_time( 'next_payment' ) );
		}

		$order_items = $order->get_items();

		// Only one subscription allowed in the cart for PayPal
		$product = $order->get_product_from_item( array_shift( $order_items ) );

		$paypal_args['name']        = $product->get_title();

		$paypal_args['description'] = $product->get_title() . ' - ' . $subscription->get_formatted_order_total();
		$paypal_args['description'] = str_replace( array( '<span class="amount">', '</span>' ), '', $paypal_args['description'] ); // Strip HTML
		$paypal_args['description'] = str_replace( '&#36;', '$', $paypal_args['description'] ); // Use real currency symbol

		// Subscription unit of duration
		$paypal_args['period'] = ucfirst( $subscription->billing_period );

		// Interval of subscription payments
		$paypal_args['frequency'] = $subscription->billing_interval;

		if ( ! $is_synced_subscription ) {
			$paypal_args['initial_amount'] = $order->get_total();
		} elseif ( $sign_up_fee_total > 0 ) {
			$paypal_args['initial_amount'] = $sign_up_fee_total;
		}

		if ( $trial_length > 0 ) {

			$paypal_args['trial_period']       = ucfirst( $subscription->trial_period );
			$paypal_args['trial_frequency']    = 1;
			$paypal_args['trial_total_cycles'] = $trial_length;

		} elseif ( ! $is_synced_subscription ) {

			// We charge the first payment using an initial amount to work around PayPal's unreliable start date handling, discussed here: http://stackoverflow.com/questions/10578283/paypal-express-checkout-recurring-profile-start-date
			// Becauase of that, we need to use a free trial to account for the first payment
			$paypal_args['trial_period']       = $paypal_args['period'];
			$paypal_args['trial_frequency']    = 1;
			$paypal_args['trial_total_cycles'] = $paypal_args['frequency'];

			$subscription_length = $subscription_length - $subscription_interval;

		}

		// Number of times that subscription payments recur
		if ( $subscription_length > 0 ) {
			$paypal_args['total_cycles'] = $subscription_length / $subscription_interval;
		} else {
			$paypal_args['total_cycles'] = 0;
		}

		$paypal_args['max_failed_payments'] = 1;
		$paypal_args['add_to_next_bill']    = false;

		$paypal_args = apply_filters( 'woocommerce_paypal_digital_goods_nvp_args', $paypal_args );

		$paypal_object = new PayPal_Subscription( $paypal_args );

		return $paypal_object;
	}

	/**
	 * When a store manager or user cancels a subscription in the store, also cancel the subscription with PayPal.
	 *
	 * @since 3.2
	 */
	public function cancel( $order ) {
		$this->manage_subscription_with_paypal( $order, 'Cancel' );
		wp_clear_scheduled_hook( 'ppdg_check_subscription_status', array( 'order_id' => (int)$order->id, 'profile_id' => $this->get_profile_id( $order ) ) );
	}

	/**
	 * When a store manager or user suspends a subscription in the store, also suspend the subscription with PayPal.
	 *
	 * @since 3.2
	 */
	public function suspend( $order ) {
		$this->manage_subscription_with_paypal( $order, 'Suspend' );
	}

	/**
	 * When a store manager or user reactivates a subscription in the store, also reactivate the subscription with PayPal.
	 *
	 * How PayPal Handles suspension is discussed here: https://www.x.com/developers/paypal/forums/nvp/reactivate-recurring-profile
	 *
	 * @since 3.2
	 */
	public function activate( $order ) {
		$this->manage_subscription_with_paypal( $order, 'Reactivate' );
	}

	/**
	 * When a subscriber or store manager cancel's a subscription in the store, suspend it with PayPal
	 *
	 * @since 3.2
	 */
	public function manage_subscription_with_paypal( $order, $action ) {

		if ( defined( 'PPDG_PROCESSING_SUBSCRIPTION' ) && PPDG_PROCESSING_SUBSCRIPTION === true ) {
			return;
		}

		switch( $action ) {
			case 'Cancel' :
				$new_status = __( 'cancelled', 'ultimatewoo-pro' );
				break;
			case 'Suspend' :
				$new_status = __( 'suspended', 'ultimatewoo-pro' );
				break;
			case 'Reactivate' :
				$new_status = __( 'reactivated', 'ultimatewoo-pro' );
				break;
		}

		$paypal_object = $this->gateway->get_paypal_object( $order );
		$profile_id    = $this->get_profile_id( $order );
		$paypal_note   = sprintf( __( 'Subscription %s at %s', 'ultimatewoo-pro' ), $new_status, get_bloginfo( 'name' ) );

		if ( ! empty( $profile_id ) ) {
			$response = $paypal_object->manage_subscription_status( $profile_id, $action, $paypal_note );
		} else {
			$response = array();
		}

		if ( isset( $response['ACK'] ) && $response['ACK'] == 'Success' ) {
			$this->add_order_note( $order, sprintf( __( 'Subscription %s with PayPal (via Digital Goods for Express Checkout)', 'ultimatewoo-pro' ), $new_status ) );
		}

		return $response;
	}


	/** Utilities: these are the main methods for handling differences between Subscriptions v2.0 and v1.5 **/

	/**
	 * Check if a subscription has a given status.
	 *
	 * Handy for checking against the different data structures used for a subscription between v1.5 and v2.0.
	 *
	 * @since 3.2
	 */
	protected function has_status( $subscription, $status ) {
		return $subscription->has_status( $status );
	}

	/**
	 * Add a note to subscriptions purhcased in an order.
	 *
	 * Handy for recording the note against the different data structures used for a subscription between v1.5 and v2.0.
	 *
	 * @since 3.2
	 */
	protected function add_order_note( $order, $note ) {
		foreach ( wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'any' ) ) as $subscription ) {
			$subscription->add_order_note( $note );
		}
	}

	/**
	 * Get the subscription for an order, if any.
	 *
	 * Handy for getting the subscription in the different data structures used for a subscription between v1.5 and v2.0.
	 *
	 * @since 3.2
	 */
	protected function get_subscription( $order ) {
		$subscriptions = wcs_get_subscriptions_for_order( $order, array( 'order_type' => array( 'parent', 'switch' ) ) );

		if ( ! empty( $subscriptions ) ) {
			$subscription = array_pop( $subscriptions );
		} else {
			$subscription = false;
		}

		return $subscription;
	}

	/**
	 * Get the PayPal Subscription Profile ID being used on a subscription.
	 *
	 * Handy for getting the profile ID from the right object, which in Subscriptions v1.5 is the original order
	 * whereas in version 2.0, it's the subscription itself.
	 *
	 * @since 3.2
	 */
	protected function get_profile_id( $order ) {

		$profile_id   = '';

		if ( wcs_is_subscription( $order ) ) {
			$subscription = $order;
		} else {
			$subscription = $this->get_subscription( $order );
		}

		if ( false !== $subscription ) {
			$profile_id = get_post_meta( $subscription->id, 'PayPal Profile ID', true );
		}
		return $profile_id;
	}
}
