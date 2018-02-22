<?php
/**
 * WooCommerce Points and Rewards
 *
 * @package     WC-Points-Rewards/Classes
 * @author      WooThemes
 * @copyright   Copyright (c) 2013, WooThemes
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order class
 *
 * Handle adding points earned upon checkout & deducting points redeemed for discounts
 *
 * @since 1.0
 */
class WC_Points_Rewards_Order {


	/**
	 * Add hooks/filters
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_processing', array( $this, 'maybe_update_points' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'maybe_update_points' ) );
		add_action( 'woocommerce_order_status_on-hold', array( $this, 'maybe_update_points' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'log_redemption_points' ) );

		// credit points back to the user if their order is cancelled or refunded
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'handle_cancelled_refunded_order' ) );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'handle_cancelled_refunded_order' ) );
		add_action( 'woocommerce_order_status_failed', array( $this, 'handle_cancelled_refunded_order' ) );
	}

	/**
	 * Conditionally updates points.
	 *
	 * @since 1.6.0
	 * @version 1.6.7
	 * @param int $order_id
	 */
	public function maybe_update_points( $order_id ) {
		$order = wc_get_order( $order_id );

		$this->maybe_deduct_redeemed_points( $order_id );

		if ( 'on-hold' !== $order->get_status() ) {
			$this->add_points_earned( $order_id );
		}
	}

	/**
	 * Add the points earned for purchase to the customer's account upon successful payment
	 *
	 * @since 1.0
	 * @param object|int $order the WC_Order object or order ID
	 */
	public function add_points_earned( $order ) {
		global $wc_points_rewards;

		if ( ! is_object( $order ) ) {
			$order = wc_get_order( $order );
		}

		$order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();
		$order_user_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->user_id : $order->get_user_id();

		// bail for guest user
		if ( ! $order_user_id ) {
			return;
		}

		// Bail for gifted orders.
		$gift = get_post_meta( $order_id, '_wcgp_given_order', true );
		if ( 'yes' == $gift && apply_filters( 'woocommerce_points_rewards_ignore_gifted_orders', true ) ) {
			return;
		}

		// check if points have already been added for this order
		$points = get_post_meta( $order_id, '_wc_points_earned', true );

		if ( '' !== $points ) {
			return;
		}

		// get points earned
		$points = $this->get_points_earned_for_purchase( $order );

		// set order meta, regardless of whether any points were earned, just so we know the process took place
		update_post_meta( $order_id, '_wc_points_earned', $points );

		// bail if no points earned
		if ( ! $points ) {
			return;
		}

		// add points
		WC_Points_Rewards_Manager::increase_points( $order_user_id, $points, 'order-placed', null, $order_id );

		// add order note
		/* translators: 1: points 2: points label */
		$order->add_order_note( sprintf( __( 'Customer earned %1$d %2$s for purchase.', 'ultimatewoo-pro' ), $points, $wc_points_rewards->get_points_label( $points ) ) );
	}

	/**
	 * Returns the amount of points earned for the purchase, calculated by getting the points earned for each individual
	 * product purchase multiplied by the quantity being ordered
	 *
	 * @since 1.0
	 */
	private function get_points_earned_for_purchase( $order ) {

		$points_earned = 0;

		foreach ( $order->get_items() as $item_key => $item ) {

			$product = $order->get_product_from_item( $item );

			if ( ! is_object( $product ) ) {
				continue;
			}

			// If prices include tax, we include the tax in the points calculation
			if ( 'no' === get_option( 'woocommerce_prices_include_tax' ) ) {
				// Get the un-discounted price paid and adjust our product price
				$item_price = $order->get_item_subtotal( $item, false, true );
			} else {
				// Get the un-discounted price paid and adjust our product price
				$item_price = $order->get_item_subtotal( $item, true, true );
			}

			$product->set_price( $item_price );

			// Calc points earned
			$points_earned += apply_filters( 'woocommerce_points_earned_for_order_item', WC_Points_Rewards_Product::get_points_earned_for_product_purchase( $product, $order ), $product, $item_key, $item, $order ) * $item['qty'];
		}

		// reduce by any discounts.  One minor drawback: if the discount includes a discount on tax and/or shipping
		//  it will cost the customer points, but this is a better solution than granting full points for discounted orders
		$discount = $order->get_total_discount();
		$points_earned -= min( WC_Points_Rewards_Manager::calculate_points( $discount ), $points_earned );

		// check if applied coupons have a points modifier and use it to adjust the points earned
		$coupons = $order->get_used_coupons();

		if ( ! empty( $coupons ) ) {

			$points_modifier = 0;

			// get the maximum points modifier if there are multiple coupons applied, each with their own modifier
			foreach ( $coupons as $coupon_code ) {

				$coupon = new WC_Coupon( $coupon_code );
				$coupon_id = version_compare( WC_VERSION, '3.0', '<' ) ? $coupon->id : $coupon->get_id();
				$wc_points_modifier = get_post_meta( $coupon_id, '_wc_points_modifier' );

				if ( ! empty( $wc_points_modifier[0] ) && $wc_points_modifier[0] > $points_modifier ) {
					$points_modifier = $wc_points_modifier[0];
				}
			}

			if ( $points_modifier > 0 ) {
				$points_earned = round( $points_earned * ( $points_modifier / 100 ) );
			}
		}

		return apply_filters( 'wc_points_rewards_points_earned_for_purchase', $points_earned, $order );
	}

	/**
	 * Logs the possible points and amount for redemption.
	 * This is needed because some orders will be in pending or on-hold
	 * before it gets processed.
	 *
	 * @since 1.6.1
	 * @version 1.6.1
	 * @param int $order_id
	 */
	public function log_redemption_points( $order_id ) {
		// First check if points already logged
		$logged_points = get_post_meta( $order_id, '_wc_points_logged_redemption', true );

		if ( ! empty( $logged_points ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		$discount_code = WC_Points_Rewards_Discount::get_discount_code();

		$discount_amount = 0;

		if ( isset( WC()->cart->coupon_discount_amounts[ $discount_code ] ) ) {
			$discount_amount += WC()->cart->coupon_discount_amounts[ $discount_code ];
		}
		if ( WC()->cart->prices_include_tax && isset( WC()->cart->coupon_discount_tax_amounts[ $discount_code ] ) ) {
			$discount_amount += WC()->cart->coupon_discount_tax_amounts[ $discount_code ];
		}

		$points_redeemed = WC_Points_Rewards_Manager::calculate_points_for_discount( $discount_amount );

		update_post_meta( $order_id, '_wc_points_logged_redemption', array( 'points' => $points_redeemed, 'amount' => $discount_amount, 'discount_code' => $discount_code ) );
	}

	/**
	 * Deducts the points redeemed for a discount when the order is processed at checkout. Note that points are deducted
	 * immediately upon checkout processing to protect against abuse.
	 *
	 * @since 1.0
	 * @param int $order_id the WC_Order ID
	 */
	public function maybe_deduct_redeemed_points( $order_id ) {
		global $wc_points_rewards;

		$already_redeemed  = get_post_meta( $order_id, '_wc_points_redeemed', true );
		$logged_redemption = get_post_meta( $order_id, '_wc_points_logged_redemption', true );

		// Points has already been redeemed
		if ( ! empty( $already_redeemed ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		$order_user_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->user_id : $order->get_user_id();

		// bail for guest user
		if ( ! $order_user_id ) {
			return;
		}

		$discount_code = WC_Points_Rewards_Discount::get_discount_code();

		$order_statuses = apply_filters( 'wc_points_rewards_redeem_points_order_statuses', array(
			'processing',
			'completed',
		) );

		if ( ! empty( $logged_redemption ) ) {
			$points_redeemed = $logged_redemption['points'];
			$discount_amount = $logged_redemption['amount'];
			$discount_code   = $logged_redemption['discount_code'];
		} else {
			// Get amount of discount
			$discount_amount = 0;

			if ( isset( WC()->cart->coupon_discount_amounts[ $discount_code ] ) ) {
				$discount_amount += WC()->cart->coupon_discount_amounts[ $discount_code ];
			}
			if ( WC()->cart->prices_include_tax && isset( WC()->cart->coupon_discount_tax_amounts[ $discount_code ] ) ) {
				$discount_amount += WC()->cart->coupon_discount_tax_amounts[ $discount_code ];
			}

			$points_redeemed = WC_Points_Rewards_Manager::calculate_points_for_discount( $discount_amount );
		}

		// only deduct points if they were redeemed for a discount
		if ( ! in_array( $discount_code, $order->get_used_coupons() ) && in_array( $order->get_status(), $order_statuses ) ) {
			return;
		}

		// deduct points
		WC_Points_Rewards_Manager::decrease_points( $order_user_id, $points_redeemed, 'order-redeem', array( 'discount_code' => $discount_code, 'discount_amount' => $discount_amount ), $order_id );

		update_post_meta( $order_id, '_wc_points_redeemed', $points_redeemed );

		// add order note
		/* translators: 1: points earned 2: points label 3: discount amount */
		$order->add_order_note( sprintf( __( '%1$d %2$s redeemed for a %3$s discount.', 'ultimatewoo-pro' ), $points_redeemed, $wc_points_rewards->get_points_label( $points_redeemed ), wc_price( $discount_amount ) ) );
	}


	/**
	 * Handle an order that is cancelled or refunded by:
	 *
	 * 1) Removing any points earned for the order
	 *
	 * 2) Crediting points redeemed for a discount back to the customer's account if the order that they redeemed the points
	 * for a discount on is cancelled or refunded
	 *
	 * @since 1.0
	 * @param int $order_id the WC_Order ID
	 */
	public function handle_cancelled_refunded_order( $order_id ) {

		global $wc_points_rewards;

		$order = wc_get_order( $order_id );

		$order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();
		$order_user_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->user_id : $order->get_user_id();

		// bail for guest user
		if ( ! $order_user_id ) {
			return;
		}

		// handle removing any points earned for the order
		$points_earned = get_post_meta( $order_id, '_wc_points_earned', true );

		if ( $points_earned > 0 ) {

			// remove points
			WC_Points_Rewards_Manager::decrease_points( $order_user_id, $points_earned, 'order-cancelled', null, $order_id );

			// remove points from order
			delete_post_meta( $order_id, '_wc_points_earned' );

			// add order note
			/* translators: 1: points earned 2: points earned label */
			$order->add_order_note( sprintf( __( '%1$d %2$s removed.', 'ultimatewoo-pro' ), $points_earned, $wc_points_rewards->get_points_label( $points_earned ) ) );
		}

		// handle crediting points redeemed for a discount
		$points_redeemed = get_post_meta( $order_id, '_wc_points_redeemed', true );

		if ( $points_redeemed > 0 ) {

			// credit points
			WC_Points_Rewards_Manager::increase_points( $order_user_id, $points_redeemed, 'order-cancelled', null, $order_id );

			// remove points from order
			delete_post_meta( $order_id, '_wc_points_redeemed' );

			// add order note
			/* translators: 1: points redeemed 2: points redeemed label */
			$order->add_order_note( sprintf( __( '%1$d %2$s credited back to customer.', 'ultimatewoo-pro' ), $points_redeemed, $wc_points_rewards->get_points_label( $points_redeemed ) ) );
		}
	}

} // end \WC_Points_Rewards_Order class
