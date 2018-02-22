<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Deposits_Cart_Manager class.
 */
class WC_Deposits_Cart_Manager {

	/** @var object Class Instance */
	private static $instance;

	/**
	 * Get the class instance.
	 */
	public static function get_instance() {
		return null === self::$instance ? ( self::$instance = new self ) : self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'deposits_form_output' ), 99 );
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'validate_add_cart_item' ), 10, 3 );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 2 );
		add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 99, 1 );

		// Apply discounts after the cart is completely loaded.
		// Dynamic Pricing applies discounts after the cart is completely loaded
		// to account for category quantity discounts.
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'get_cart_from_session' ), 99, 1 );

		//This was the original filter, we call get_cart_item_from_session manually now in get_cart_from_session.
		//add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 99, 3 );

		// Control how coupons apply to products including a deposit or payment plan
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'clear_deferred_discounts' ) );
		add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'get_discount_amount' ), 10, 5 );

		add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_price', array( $this, 'display_item_price' ), 10, 3 );
		add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'display_item_subtotal' ), 10, 3 );
		add_action( 'woocommerce_cart_totals_before_order_total', array( $this, 'display_cart_totals_before' ), 99 );
		add_action( 'woocommerce_review_order_before_order_total', array( $this, 'display_cart_totals_before' ), 99 );
		add_action( 'woocommerce_cart_totals_after_order_total', array( $this, 'display_cart_totals_after' ), 1 );
		add_action( 'woocommerce_review_order_after_order_total', array( $this, 'display_cart_totals_after' ), 1 );
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'disable_gateways' ) );


		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			add_action( 'woocommerce_add_order_item_meta', array( $this, 'add_order_item_meta_legacy' ), 50, 2 );
		} else {
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_order_item_meta' ), 50, 3 );
		}

		// Change button/cart URLs
		add_filter( 'add_to_cart_text', array( $this, 'add_to_cart_text' ), 15 );
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'add_to_cart_text' ), 15 );
		add_filter( 'woocommerce_add_to_cart_url', array( $this, 'add_to_cart_url' ), 10, 1 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'add_to_cart_url' ), 10, 1 );
		add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'remove_add_to_cart_class' ), 10, 2 );
	}

	/**
	 * Scripts and styles.
	 */
	public function wp_enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_style( 'wc-deposits-frontend', WC_DEPOSITS_PLUGIN_URL . '/assets/css/frontend.css', null, WC_DEPOSITS_VERSION );
		wp_register_script( 'wc-deposits-frontend', WC_DEPOSITS_PLUGIN_URL . '/assets/js/frontend' . $suffix . '.js', array( 'jquery' ), WC_DEPOSITS_VERSION, true );
	}

	/**
	 * Show deposits form.
	 */
	public function deposits_form_output() {
		if ( WC_Deposits_Product_Manager::deposits_enabled( $GLOBALS['post']->ID ) ) {
			wp_enqueue_script( 'wc-deposits-frontend' );
			wc_get_template( 'deposit-form.php', array( 'post' => $GLOBALS['post'] ), 'ultimatewoo-pro', WC_DEPOSITS_TEMPLATE_PATH );
		}
	}

	/**
	 * Does the cart contain a deposit?
	 *
	 * @return boolean
	 */
	public function has_deposit() {
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( ! empty( $cart_item['is_deposit'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * See how much credit the user is giving the customer (for payment plans)
	 *
	 * @return float
	 */
	public function get_future_payments_amount() {
		return $this->get_deposit_remaining_amount() + $this->get_credit_amount() - self::get_deferred_discount_amount();
	}

	/**
	 * See whats left to pay after deposits.
	 *
	 * @return float
	 */
	public function get_deposit_remaining_amount() {
		$credit_amount = 0;

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( ! empty( $cart_item['is_deposit'] ) && empty( $cart_item['payment_plan'] ) ) {
				$_product = $cart_item['data'];
				$quantity = $cart_item['quantity'];
				if ( isset( $cart_item['full_amount'] ) ) {
					if ( 'excl' === WC()->cart->tax_display_cart ) {
						$credit_amount += $this->get_price_excluding_tax( $_product, array( 'qty' => $quantity, 'price' => ( $cart_item['full_amount'] - $cart_item['deposit_amount'] ) ) );
					} else {
						$credit_amount += $this->get_price_including_tax( $_product, array( 'qty' => $quantity, 'price' => ( $cart_item['full_amount'] - $cart_item['deposit_amount'] ) ) );
					}
				}
			}
		}

		return $credit_amount;
	}

	/**
	 * See how much credit the user is giving the customer (for payment plans).
	 *
	 * @return float
	 */
	public function get_credit_amount() {
		$credit_amount = 0;

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( ! empty( $cart_item['is_deposit'] ) && ! empty( $cart_item['payment_plan'] ) ) {
				$_product = $cart_item['data'];
				$quantity = $cart_item['quantity'];

				if ( 'excl' === WC()->cart->tax_display_cart ) {
					$credit_amount += $this->get_price_excluding_tax( $_product, array( 'qty' => $quantity, 'price' => ( $cart_item['full_amount'] - $cart_item['deposit_amount'] ) ) );
				} else {
					$credit_amount += $this->get_price_including_tax( $_product, array( 'qty' => $quantity, 'price' => ( $cart_item['full_amount'] - $cart_item['deposit_amount'] ) ) );
				}
			}
		}

		return $credit_amount;
	}

	/**
	 * When an item is added to the cart, validate it.
	 *
	 * @param  mixed $passed
	 * @param  mixed $product_id
	 * @param  mixed $qty
	 * @return bool
	 */
	public function validate_add_cart_item( $passed, $product_id, $qty ) {
		if ( ! WC_Deposits_Product_Manager::deposits_enabled( $product_id ) ) {
			return $passed;
		}

		$wc_deposit_option       = isset( $_POST['wc_deposit_option'] ) ? sanitize_text_field( $_POST['wc_deposit_option'] ) : false;
		$wc_deposit_payment_plan = isset( $_POST['wc_deposit_payment_plan'] ) ? sanitize_text_field( $_POST['wc_deposit_payment_plan'] ) : false;

		// Validate chosen plan
		if ( ( 'yes' === $wc_deposit_option || WC_Deposits_Product_Manager::deposits_forced( $product_id ) ) && 'plan' === WC_Deposits_Product_Manager::get_deposit_type( $product_id ) ) {
			if ( ! in_array( $wc_deposit_payment_plan, WC_Deposits_Plans_Manager::get_plan_ids_for_product( $product_id ) ) ) {
				wc_add_notice( __( 'Please select a valid payment plan', 'ultimatewoo-pro' ), 'error' );
				return false;
			}
		}

		return $passed;
	}

	/**
	 * Add posted data to the cart item.
	 *
	 * @param  mixed $cart_item_meta
	 * @param  mixed $product_id
	 * @return array
	 */
	public function add_cart_item_data( $cart_item_meta, $product_id ) {
		if ( ! WC_Deposits_Product_Manager::deposits_enabled( $product_id ) ) {
			return $cart_item_meta;
		}

		$wc_deposit_option       = isset( $_POST['wc_deposit_option'] ) ? sanitize_text_field( $_POST['wc_deposit_option'] ) : false;
		$wc_deposit_payment_plan = isset( $_POST['wc_deposit_payment_plan'] ) ? sanitize_text_field( $_POST['wc_deposit_payment_plan'] ) : false;

		if ( 'yes' === $wc_deposit_option || WC_Deposits_Product_Manager::deposits_forced( $product_id ) ) {
			$cart_item_meta['is_deposit'] = true;
			if ( 'plan' === WC_Deposits_Product_Manager::get_deposit_type( $product_id ) ) {
				$cart_item_meta['payment_plan'] = $wc_deposit_payment_plan;
			} else {
				$cart_item_meta['payment_plan'] = 0;
			}
		}

		return $cart_item_meta;
	}


	/**
	 * Runs though all items in the cart applying any deposit information.
	 * Needs to run on the cart_loaded_from_session hook so it runs after the cart has been fully loaded.
	 * @param WC_Cart $cart
	 */
	public function get_cart_from_session( $cart ) {
		if ( sizeof( $cart->cart_contents ) > 0 ) {
			foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
				$result                                = $this->get_cart_item_from_session( $cart_item, $cart_item, $cart_item_key );
				$cart->cart_contents[ $cart_item_key ] = $result;
			}
		}
	}

	/**
	 * Get data from the session and add to the cart item's meta.
	 *
	 * @param  mixed $cart_item
	 * @param  mixed $values
	 * @return array cart item
	 */
	public function get_cart_item_from_session( $cart_item, $values, $cart_item_key ) {
		$cart_item['is_deposit']   = ! empty( $values['is_deposit'] );
		$cart_item['payment_plan'] = ! empty( $values['payment_plan'] ) ? absint( $values['payment_plan'] ) : 0;
		return $this->add_cart_item( $cart_item );
	}

	/**
	 * Adjust the price of the product based on deposits.
	 *
	 * @param  mixed $cart_item
	 * @return array cart item
	 */
	public function add_cart_item( $cart_item ) {
		if ( ! empty( $cart_item['is_deposit'] ) ) {
			$deposit_amount = WC_Deposits_Product_Manager::get_deposit_amount( $cart_item['data'], ! empty( $cart_item['payment_plan'] ) ? $cart_item['payment_plan'] : 0, 'order' );

			if ( false !== $deposit_amount ) {
				$cart_item['deposit_amount'] = $deposit_amount;

				// Bookings support
				if ( isset( $cart_item['booking']['_persons'] ) && 'yes' === WC_Deposits_Product_Meta::get_meta( $cart_item['data']->get_id(), '_wc_deposit_multiple_cost_by_booking_persons' ) ) {
					$cart_item['deposit_amount'] = $cart_item['deposit_amount'] * absint( is_array( $cart_item['booking']['_persons'] ) ? array_sum( $cart_item['booking']['_persons'] ) : $cart_item['booking']['_persons'] );
				}

				// Work out %
				if ( ! empty( $cart_item['payment_plan'] ) ) {
					$plan                     = WC_Deposits_Plans_Manager::get_plan( $cart_item['payment_plan'] );
					$total_percent            = $plan->get_total_percent();
					$cart_item['full_amount'] = ( $cart_item['data']->get_price() / 100 ) * $total_percent;
				} else {
					$cart_item['full_amount'] = $cart_item['data']->get_price();
				}

				$cart_item['data']->set_price( $cart_item['deposit_amount'] );
			}
		}

		return $cart_item;
	}

	/**
	 * Clears all deferred discounts in the cart
	 *
	 * @since 1.1.11
	 *
	 * @return void
	 */
	public function clear_deferred_discounts() {
		WC()->session->set( 'deposits_deferred_discounts', array() );
	}

	/**
	 * Control how coupons apply to products including a deposit or payment plan
	 * Filters woocommerce_coupon_get_discount_amount (WC_Coupons get_discount_amount)
	 *
	 * @since 1.1.11
	 *
	 * @param float $discount
	 * @param float $discounting_amount Amount the coupon is being applied to
	 * @param array|null $cart_item Cart item being discounted if applicable
	 * @param boolean $single True if discounting a single qty item, false if its the line (always true in core)
	 * @param WC_Coupon coupon
	 *
	 * @return float Amount this coupon has discounted
	 */
	public function get_discount_amount( $discount, $discounting_amount, $cart_item, $single, $coupon ) {
		if ( ! empty( $cart_item['is_deposit'] ) ) {
			$old_wc = version_compare( WC_VERSION, '3.0', '<' );
			$coupon_type = $old_wc ? $coupon->type : $coupon->get_discount_type(); // fixed_cart or fixed_product or percent or percent_product
			$coupon_id = $old_wc ? $coupon->id : $coupon->get_id();

			$deposit_type = WC_Deposits_Product_Manager::get_deposit_type( $cart_item['product_id'] ); // fixed or percent or plan

			// Initialize default condition
			$present_discount_amount = floatval( $discount );
			$deferred_discount_amount = 0.0;

			// For fixed coupons (fixed_cart or fixed_product)
			// For products with payment plans, discount a proportional amount of the fixed discount now, the rest defer for later
			// For products with fixed deposits, defer the entire fixed discount
			// For products with percentage based deposits, defer the entire fixed discount
			if ( in_array( $coupon_type, array( 'fixed_cart', 'fixed_product' ) ) ) {
				if ( 'plan' === $deposit_type ) {
					$full_amount = floatval( $cart_item['full_amount'] );
					$deposit_amount = floatval( $cart_item['deposit_amount'] );

					// Core has a LB set between the discount and discounting amount.
					// See https://github.com/woocommerce/woocommerce-deposits/issues/160#issuecomment-322428071.
					if ( $deposit_amount < $discount ) {
						$discount = $coupon->get_amount();
					}

					// Calculate proportion due now, avoiding (unlikely) division by zero
					if ( $full_amount > 0 ) {
						$present_proportion = $deposit_amount / $full_amount;
					} else {
						$present_proportion = 1.0;
					}
					// Present discount amount is always for quantity 1
					$present_discount_amount = round( $discount * $present_proportion, 2 );
					// Deferred discount amount is always for the line quantity
					$deferred_discount_amount = round( $discount * $cart_item['quantity'] * ( 1 - $present_proportion ), 2 );
				} else if ( in_array( $deposit_type, array( 'percent', 'fixed' ) ) ) {
					$present_discount_amount = 0;
					$deferred_discount_amount = round( $discount * $cart_item['quantity'], 2 ); // total for (line) quantity, not just unit
				}
			}

			// For percentage based coupons (percent or percent_product)
			// For products with payment plans, pass through the provided discount AND scale and defer it for later
			// For products with fixed deposits, defer the entire discount
			// For products with percentage based deposits, pass through the provided discount AND scale and defer it for later
			if ( in_array( $coupon_type, array( 'percent', 'percent_product' ) ) ) {
				$full_amount = floatval( $cart_item['full_amount'] );
				$deposit_amount = floatval( $cart_item['deposit_amount'] );
				if ( in_array( $deposit_type, array( 'plan', 'percent' ) ) ) {
					// Applies discount toward future amounts to ensure complete discount is not lost
					if ( $deposit_amount > 0 ) {
						$deferred_scaler =  ( $full_amount - $deposit_amount ) / $deposit_amount;
						$deferred_discount_amount = round( $discount * $cart_item['quantity'] * $deferred_scaler, 2 );
					}
				} else if( 'fixed' === $deposit_type ) {
					// First, zero the present discount
					$present_discount_amount = 0;
					// Then scale and defer the entire discount
					if ( $deposit_amount > 0 ) {
						$deferred_scaler =  $full_amount / $deposit_amount;
						$deferred_discount_amount = round( $discount * $cart_item['quantity'] * $deferred_scaler, 2 );
					}
				}
			}

			if ( $deferred_discount_amount > 0 ) {
				// Save the discount to be applied toward the future amount due
				$search_key = WC()->cart->generate_cart_id( $cart_item['product_id'], $cart_item['variation_id'], $cart_item['variation'] );
				$deferred_discounts = WC()->session->get( 'deposits_deferred_discounts', array() );
				$deferred_discounts[ $search_key ][ $coupon_id ] = $deferred_discount_amount;
				WC()->session->set( 'deposits_deferred_discounts', $deferred_discounts );
			}

			// Return the discount to be applied now
			return $present_discount_amount;
		}

		// Otherwise, just pass through the original amount
		return $discount;
	}

	/**
	 * Calculates the sum of all deferred discounts in the cart for all items
	 * Totals are for (line) quantity, not just unit
	 *
	 * @since 1.2.0
	 *
	 * @return float
	 */
	public static function get_deferred_discount_amount() {
		$deferred_discount_amount = 0;
		$deferred_discounts = WC()->session->get( 'deposits_deferred_discounts', array() );
		foreach( $deferred_discounts as $item_key => $item_discounts ) {
			foreach( $item_discounts as $coupon_id => $discount ) {
				$deferred_discount_amount += $discount;
			}
		}
		return $deferred_discount_amount;
	}

	/**
	 * Put meta data into format which can be displayed.
	 *
	 * @param  mixed $other_data
	 * @param  mixed $cart_item
	 * @return array meta
	 */
	public function get_item_data( $other_data, $cart_item ) {
		if ( ! empty( $cart_item['payment_plan'] ) ) {
			$plan         = WC_Deposits_Plans_Manager::get_plan( $cart_item['payment_plan'] );
			$other_data[] = array(
				'name'    => __( 'Payment Plan', 'ultimatewoo-pro' ),
				'value'   => $plan->get_name(),
				'display' => '',
			);
		}
		return $other_data;
	}

	/**
	 * Show the correct item price.
	 */
	public function display_item_price( $output, $cart_item, $cart_item_key ) {
		if ( ! isset( $cart_item['full_amount'] ) ) {
			return $output;
		}
		if ( ! empty( $cart_item['is_deposit'] ) ) {
			$_product = $cart_item['data'];
			if ( 'excl' === WC()->cart->tax_display_cart ) {
				$amount = $this->get_price_excluding_tax( $_product, array( 'qty' => 1, 'price' => $cart_item['full_amount'] ) );
			} else {
				$amount = $this->get_price_including_tax( $_product, array( 'qty' => 1, 'price' => $cart_item['full_amount'] ) );
			}
			$output = wc_price( $amount );
		}
		return $output;
	}

	/**
	 * Adjust the subtotal display in the cart.
	 */
	public function display_item_subtotal( $output, $cart_item, $cart_item_key ) {
		if ( ! isset( $cart_item['full_amount'] ) ) {
			return $output;
		}

		if ( ! empty( $cart_item['is_deposit'] ) ) {
			$_product = $cart_item['data'];
			$quantity = $cart_item['quantity'];

			if ( 'excl' === WC()->cart->tax_display_cart ) {
				$full_amount    = $this->get_price_excluding_tax( $_product, array( 'qty' => $quantity, 'price' => $cart_item['full_amount'] ) );
				$deposit_amount = $this->get_price_excluding_tax( $_product, array( 'qty' => $quantity, 'price' => $cart_item['deposit_amount'] ) );
			} else {
				$full_amount    = $this->get_price_including_tax( $_product, array( 'qty' => $quantity, 'price' => $cart_item['full_amount'] ) );
				$deposit_amount = $this->get_price_including_tax( $_product, array( 'qty' => $quantity, 'price' => $cart_item['deposit_amount'] ) );
			}

			if ( ! empty( $cart_item['payment_plan'] ) ) {
				$plan = new WC_Deposits_Plan( $cart_item['payment_plan'] );
				$output .= '<br/><small>' . $plan->get_formatted_schedule( $full_amount ) . '</small>';
			} else {
				$output .= '<br/><small>' . sprintf( __( '%s payable in total', 'ultimatewoo-pro' ), wc_price( $full_amount ) ) . '</small>';
			}
		}

		return $output;
	}

	/**
	 * Before the main total.
	 */
	public function display_cart_totals_before() {
		if ( self::get_future_payments_amount() > 0 ) {
			ob_start();
		}
	}

	/**
	 * After the main total.
	 */
	public function display_cart_totals_after() {
		$future_payment_amount = self::get_future_payments_amount();

		$is_tax_included = wc_tax_enabled() && 'excl' != WC()->cart->tax_display_cart;
		$tax_message     = $is_tax_included ? __( '(includes tax)', 'ultimatewoo-pro' ) : __( '(excludes tax)', 'ultimatewoo-pro' );
		$tax_element     = wc_tax_enabled() ? ' <small class="tax_label">' . $tax_message . '</small>' : '';

		$deferred_discount_amount  = self::get_deferred_discount_amount();

		if ( 0 >= $future_payment_amount ) {
			return;
		}

		ob_end_clean(); ?>
		<tr class="order-total">
			<th><?php _e( 'Due Today', 'ultimatewoo-pro' ); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>
		<?php
		if ( $deferred_discount_amount > 0 ) {
			?>
			<tr class="order-total">
				<th><?php _e( 'Discount Applied Toward Future Payments', 'ultimatewoo-pro' ); ?></th>
				<td><?php echo wc_price( -$deferred_discount_amount ); ?></td>
			</tr>
			<?php
		}
		?>
		<tr class="order-total">
		<th><?php _e( 'Future Payments', 'ultimatewoo-pro' ); ?></th>
		<td><?php echo wc_price( $future_payment_amount ); ?><?php echo $tax_element; ?></td>
		</tr><?php
	}

	/**
	 * Store cart info inside new orders.
	 * Runs on 2.6 and older.
	 * Hooked on woocommerce_add_order_item_meta action
	 *
	 * @version 1.2.0
	 *
	 * @param mixed $item_id
	 * @param mixed $cart_item
	 */
	public function add_order_item_meta_legacy( $item_id, $cart_item ) {
		if ( ! empty( $cart_item['is_deposit'] ) ) {
			// Note: This code is called for the INITIAL order created from carts containing products
			// with fixed deposits, percentage based deposits or payment plans. HOWEVER, this
			// code is NOT used when WC_Deposits_Scheduled_Order_Manager::schedule_orders_for_plan
			// creates orders for the remaining payments for a payment plan product NOR when
			// the merchant invoices the customer for the remaining balance.

			// First, calculate the full amount (before deposits/payments)
			// Note that this is for the entire line quantity, not just a unit
			$full_amount_including_tax = $cart_item['data']->get_price_including_tax( $cart_item['quantity'], $cart_item['full_amount'] );
			$full_amount_excluding_tax = $cart_item['data']->get_price_excluding_tax( $cart_item['quantity'], $cart_item['full_amount'] );

			// Next, for fixed or percentage based deposits, calculate the initial deposit, prior to tax, regardless of discounts
			// so that WC_Deposits_Order_Manager::order_action_handler invoice_remaining_balance can calculate the correct amount to charge
			$deposit_amount_excluding_tax = $this->get_price_excluding_tax( $cart_item['data'], array( 'qty' => $cart_item['quantity'], 'price' => $cart_item['deposit_amount'] ) );

			// Next, add up any deferred discounts for the item
			// Note: $deferred_discount_amount is total for (line) quantity, not just unit
			$deferred_discount_amount = 0;
			$search_key = WC()->cart->generate_cart_id( $cart_item['product_id'], $cart_item['variation_id'], $cart_item['variation'] );
			$deferred_discounts = WC()->session->get( 'deposits_deferred_discounts', array() );
			if ( array_key_exists( $search_key, $deferred_discounts ) ) {
				foreach ( $deferred_discounts[ $search_key ] as $coupon_id => $discount_amount ) {
					$deferred_discount_amount += $discount_amount;
				}
			}

			// Lastly, decorate the order item with this information so we can calculate future payment(s) later
			// in WC_Deposits_Order_Manager::order_action_handler for invoice_remaining_balance
			wc_add_order_item_meta( $item_id, '_is_deposit', 'yes' );
			wc_add_order_item_meta( $item_id, '_deposit_full_amount', $full_amount_including_tax ); // line quantity, not just a unit
			wc_add_order_item_meta( $item_id, '_deposit_full_amount_ex_tax', $full_amount_excluding_tax );
			wc_add_order_item_meta( $item_id, '_deposit_deposit_amount_ex_tax', $deposit_amount_excluding_tax );
			wc_add_order_item_meta( $item_id, '_deposit_deferred_discount', $deferred_discount_amount ); // total for (line) quantity, not just unit

			if ( ! empty( $cart_item['payment_plan'] ) ) {
				wc_add_order_item_meta( $item_id, '_payment_plan', $cart_item['payment_plan'] );
			}
		}
	}

	/**
	 * Store cart info inside new orders.
	 *
	 * @param WC_Order_Item $item
	 * @param string        $cart_item_key
	 * @param array         $values
	 */
	public function add_order_item_meta( $item, $cart_item_key, $values ) {
		$cart      = WC()->cart->get_cart();
		$cart_item = $cart[ $cart_item_key ];

		if ( ! empty( $cart_item['is_deposit'] ) ) {
			// First, calculate the full amount (before deposits/payments)
			// Note that this is for the entire line quantity, not just a unit
			$full_amount_including_tax = $this->get_price_including_tax( $cart_item['data'], array( 'qty' => $cart_item['quantity'], 'price' => $cart_item['full_amount'] ) );
			$full_amount_excluding_tax = $this->get_price_excluding_tax( $cart_item['data'], array( 'qty' => $cart_item['quantity'], 'price' => $cart_item['full_amount'] ) );

			// Next, for fixed or percentage based deposits, calculate the initial deposit, prior to tax, regardless of discounts
			// so that WC_Deposits_Order_Manager::order_action_handler invoice_remaining_balance can calculate the correct amount to charge
			$deposit_amount_excluding_tax = $this->get_price_excluding_tax( $cart_item['data'], array( 'qty' => $cart_item['quantity'], 'price' => $cart_item['deposit_amount'] ) );

			// Next, add up any deferred discounts for the item
			$deferred_discount_amount = 0;
			$deferred_discounts = WC()->session->get( 'deposits_deferred_discounts', array() );

			// We cannot use the cart_item_key provided since it differs from the one we use to store the discount
			$search_key = WC()->cart->generate_cart_id( $cart_item['product_id'], $cart_item['variation_id'], $cart_item['variation'] );
			if ( array_key_exists( $search_key, $deferred_discounts ) ) {
				foreach ( $deferred_discounts[ $search_key ] as $coupon_id => $discount_amount ) {
					$deferred_discount_amount += $discount_amount; // line quantity, not just a unit
				}
			}

			$item->add_meta_data( '_is_deposit', 'yes' );
			$item->add_meta_data( '_deposit_full_amount', $full_amount_including_tax ); // line quantity, not just a unit
			$item->add_meta_data( '_deposit_full_amount_ex_tax', $full_amount_excluding_tax );
			$item->add_meta_data( '_deposit_deposit_amount_ex_tax', $deposit_amount_excluding_tax );
			if ( $deferred_discount_amount > 0 ) {
				$item->add_meta_data( '_deposit_deferred_discount', $deferred_discount_amount ); // line quantity, not just a unit
			}

			if ( ! empty( $cart_item['payment_plan'] ) ) {
				$item->add_meta_data( '_payment_plan', $cart_item['payment_plan'] );
			}
		}
	}

	/**
	 * Disable gateways when using deposits.
	 *
	 * @param  array $gateways
	 * @return array
	 */
	public function disable_gateways( $gateways = array() ) {
		if ( is_admin() ) {
			return $gateways;
		}
		$disabled = get_option( 'wc_deposits_disabled_gateways', array() );
		if ( $this->has_deposit() && ! empty( $disabled ) && is_array( $disabled ) ) {
			return array_diff_key( $gateways, array_combine( $disabled, $disabled ) );
		}

		return $gateways;
	}

	/**
	 * Add to cart text.
	 */
	public function add_to_cart_text( $text ) {
		global $product;

		if ( is_single( $product->get_id() ) ) {
			return $text;
		}

		if ( ! WC_Deposits_Product_Manager::deposits_enabled( $product->get_id() ) ) {
			return $text;
		}

		$deposit_type = WC_Deposits_Product_Manager::get_deposit_type( $product->get_id() );
		if ( WC_Deposits_Product_Manager::deposits_forced( $product->get_id() ) ) {
			if ( 'plan' !== $deposit_type ) {
				return $text;
			}
		}

		$text = apply_filters( 'woocommerce_deposits_add_to_cart_text', __( 'Select options', 'ultimatewoo-pro' ) );

		return $text;
	}

	/**
	 * Add to cart URL.
	 *
	 * @version 1.2.2
	 *
	 * @param string $url URL.
	 *
	 * @return string URL.
	 */
	public function add_to_cart_url( $url ) {
		global $product;

		$product = wc_get_product( $product );
		if ( ! is_object( $product ) ) {
			return $url;
		}

		$product_id = $product->get_id();

		if ( is_single( $product_id ) ) {
			return $url;
		}

		if ( ! WC_Deposits_Product_Manager::deposits_enabled( $product_id ) ) {
			return $url;
		}

		$deposit_type = WC_Deposits_Product_Manager::get_deposit_type( $product_id );
		if ( WC_Deposits_Product_Manager::deposits_forced( $product_id ) ) {
			if ( 'plan' !== $deposit_type ) {
				return $url;
			}
		}

		$url = apply_filters( 'woocoommerce_deposits_add_to_cart_url', get_permalink( $product_id ) );
		return $url;
	}

	/**
	 * Remove the add to cart class from deposit products.
	 *
	 * @param  string $link HTML link
	 * @param  WC_Product $product Product
	 * @return string HTML link
	 */
	public function remove_add_to_cart_class( $link, $product ) {
		if ( WC_Deposits_Product_Manager::deposits_enabled( $product->get_id() ) ) {
			$link = str_replace( 'add_to_cart_button', '', $link );
		}

		return $link;
	}

	/**
	 * Provides a way to support both 2.6 and 3.0 since get_price_including_tax
	 * gets deprecated in 3.0, and wc_get_price_including_tax gets introduced in
	 * 3.0.
	 *
	 * @since  1.2
	 * @param  WC_Product $product
	 * @param  array      $args
	 * @return float
	 */
	private function get_price_including_tax( $product, $args ) {
		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			$args = wp_parse_args( $args, array(
				'qty'   => '',
				'price' => '',
			) );
			return $product->get_price_including_tax( $args['qty'], $args['price'] );
		} else {
			return wc_get_price_including_tax( $product, $args );
		}
	}

	/**
	 * Provides a way to support both 2.6 and 3.0 since get_price_excluding_tax
	 * gets deprecated in 3.0, and wc_get_price_excluding_tax gets introduced in
	 * 3.0.
	 *
	 * @since  1.2
	 * @param  WC_Product $product
	 * @param  array      $args
	 * @return float
	 */
	private function get_price_excluding_tax( $product, $args ) {
		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			$args = wp_parse_args( $args, array(
				'qty'   => '',
				'price' => '',
			) );
			return $product->get_price_excluding_tax( $args['qty'], $args['price'] );
		} else {
			return wc_get_price_excluding_tax( $product, $args );
		}
	}
}

WC_Deposits_Cart_Manager::get_instance();
