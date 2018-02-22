<?php
/**
 * Backward compatibility handler for WooCommerce Subscriptions
 * 
 * @package    PayPal Digital Goods
 * @subpackage Subscription
 * 
 * @license    GPLv3
 * @copyright  2015 Prospress Inc.
 * @since 3.2
 */

class WC_PPDG_Subscriptions_Deprecated extends WC_PPDG_Subscriptions {

	public function __construct( $paypal_gateway ) {

		$this->gateway = $paypal_gateway;

		add_action( 'subscription_expired_' . $this->gateway->id, array( &$this, 'cancel' ) );
		add_action( 'cancelled_subscription_' . $this->gateway->id, array( &$this, 'cancel' ) );
		add_action( 'suspended_subscription_' . $this->gateway->id, array( &$this, 'suspend' ) );
		add_action( 'reactivated_subscription_' . $this->gateway->id, array( &$this, 'activate' ) );
	}

	/**
	 * Check if an order contains a subscription with Subscriptions < v2.0
	 *
	 * @since 3.2
	 */
	public function order_contains_subscription( $order ) {

		if ( class_exists( 'WC_Subscriptions_Order' ) && WC_Subscriptions_Order::order_contains_subscription( $order ) ) {
			$order_contains_subscription = true;
		} else {
			$order_contains_subscription = false;
		}

		return $order_contains_subscription;
	}

	/**
	 * Generate a PayPal Digital Goods Subscription object for Subscriptions < v2.0
	 *
	 * @since 3.2
	 */
	public function get_paypal_object( $order ) {
		global $woocommerce;

		$recurring_amount          = WC_Subscriptions_Order::get_recurring_total( $order );
		$sign_up_fee_total         = WC_Subscriptions_Order::get_sign_up_fee( $order );
		$subscription_length       = WC_Subscriptions_Order::get_subscription_length( $order );
		$subscription_interval     = WC_Subscriptions_Order::get_subscription_interval( $order );
		$subscription_trial_length = WC_Subscriptions_Order::get_subscription_trial_length( $order );

		$is_synced_subscription    = WC_Subscriptions_Synchroniser::order_contains_synced_subscription( $order->id ) || WC_Subscriptions_Synchroniser::cart_contains_synced_subscription();

		// If the subscription is for one billing period with no free trial, just process it as a normal transaction
		if ( $subscription_length == $subscription_interval && 0 == $subscription_trial_length && false == $is_synced_subscription ) {
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
			$subscription            = WC_Subscriptions_Manager::get_subscription( WC_Subscriptions_Manager::get_subscription_key( $order->id ) );
			$id_for_calculation      = ! empty( $subscription['variation_id'] ) ? $subscription['variation_id'] : $subscription['product_id'];
			$first_payment_timestamp = WC_Subscriptions_Synchroniser::calculate_first_payment_date( $id_for_calculation, 'timestamp', $order->order_date );
			$paypal_args['start_date'] = gmdate( 'Y-m-d\TH:i:s', $first_payment_timestamp );
		}

		$order_items = $order->get_items();

		// Only one subscription allowed in the cart for PayPal
		$product = $order->get_product_from_item( array_shift( $order_items ) );

		$paypal_args['name'] = $product->get_title();

		$paypal_args['description'] = $product->get_title() . ' - ' . WC_Subscriptions_Order::get_order_subscription_string( $order );

		// Strip HTML
		$paypal_args['description'] = str_replace( array( '<span class="amount">', '</span>' ), '', $paypal_args['description'] );

		// Use real currency symbol (yes, dollar biased)
		$paypal_args['description'] = str_replace( '&#36;', '$', $paypal_args['description'] );

		// Subscription unit of duration
		$paypal_args['period'] = ucfirst( WC_Subscriptions_Order::get_subscription_period( $order ) );

		// Interval of subscription payments
		$paypal_args['frequency'] = WC_Subscriptions_Order::get_subscription_interval( $order );

		if ( ! $is_synced_subscription ) {
			$paypal_args['initial_amount'] = WC_Subscriptions_Order::get_total_initial_payment( $order, $product->id );
		} elseif ( $sign_up_fee_total > 0 ) {
			$paypal_args['initial_amount'] = $sign_up_fee_total;
		}

		if ( $subscription_trial_length > 0 ) {

			$paypal_args['trial_period']       = ucfirst( WC_Subscriptions_Order::get_subscription_trial_period( $order ) );
			$paypal_args['trial_frequency']    = 1;
			$paypal_args['trial_total_cycles'] = $subscription_trial_length;

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

		$paypal_args['add_to_next_bill'] = ( 'yes' == get_option( WC_Subscriptions_Admin::$option_prefix . '_add_outstanding_balance' ) ) ? true : false;

		$paypal_args = apply_filters( 'woocommerce_paypal_digital_goods_nvp_args', $paypal_args );

		$paypal_object = new PayPal_Subscription( $paypal_args );

		return $paypal_object;
	}

	/**
	 * Check if a subscription has a given status.
	 *
	 * Handy for checking against the different data structures used for a subscription between v1.5 and v2.0.
	 *
	 * @since 3.2
	 */
	protected function has_status( $subscription, $status ) {
		if ( isset( $subscription['status'] ) && $status == $subscription['status'] ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add a note to subscriptions purhcased in an order.
	 *
	 * Handy for recording the note against the different data structures used for a subscription between v1.5 and v2.0.
	 *
	 * @since 3.2
	 */
	protected function add_order_note( $order, $note ) {
		$order->add_order_note( $note );
	}

	/**
	 * Get the subscription for an order, if any.
	 *
	 * Handy for getting the subscription in the different data structures used for a subscription between v1.5 and v2.0.
	 *
	 * @since 3.2
	 */
	protected function get_subscription( $order ) {
		return WC_Subscriptions_Manager::get_subscription( WC_Subscriptions_Manager::get_subscription_key( $order->id ) );
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
		return get_post_meta( $order->id, 'PayPal Profile ID', true );
	}
}