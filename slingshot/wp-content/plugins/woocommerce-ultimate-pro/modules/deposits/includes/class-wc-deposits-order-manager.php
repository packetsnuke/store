<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Deposits_Order_Manager class.
 */
class WC_Deposits_Order_Manager {

	/** @var object Class Instance */
	private static $instance;

	/**
	 * Get the class instance.
	 */
	public static function get_instance() {
		return null === self::$instance ? ( self::$instance = new self ) : self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_status' ), 9 );
		add_filter( 'wc_order_statuses', array( $this, 'add_order_statuses' ) );
		add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', array( $this, 'woocommerce_valid_order_statuses_for_payment_complete' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'woocommerce_payment_complete_order_status' ), 10, 2 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'process_deposits_in_order' ), 10, 1 );
		add_action( 'woocommerce_order_status_completed', array( $this, 'process_deposits_in_order' ), 10, 1 );
		add_action( 'woocommerce_order_status_on-hold', array( $this, 'process_deposits_in_order' ), 10, 1 );
		add_action( 'woocommerce_order_status_partial-payment', array( $this, 'process_deposits_in_order' ), 10, 1 );
		add_filter( 'woocommerce_attribute_label', array( $this, 'woocommerce_attribute_label' ), 10, 2 );
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'woocommerce_hidden_order_itemmeta' ) );
		add_action( 'woocommerce_before_order_itemmeta', array( $this, 'woocommerce_before_order_itemmeta' ), 10, 3 );
		add_action( 'woocommerce_after_order_itemmeta', array( $this, 'woocommerce_after_order_itemmeta' ), 10, 3 );
		add_action( 'admin_init', array( $this, 'order_action_handler' ) );
		add_action( 'woocommerce_payment_complete', array( $this, 'payment_complete_handler' ) );

		// View orders
		add_filter( 'woocommerce_my_account_my_orders_query', array( $this, 'woocommerce_my_account_my_orders_query' ) );
		add_filter( 'woocommerce_order_item_name', array( $this, 'woocommerce_order_item_name' ), 10, 2 );
		add_action( 'woocommerce_order_item_meta_end', array( $this, 'woocommerce_order_item_meta_end' ), 10, 3 );
		add_filter( 'woocommerce_get_order_item_totals', array( $this, 'woocommerce_get_order_item_totals' ), 10, 2 );
		add_filter( 'request', array( $this, 'request_query' ) );
		add_action( 'woocommerce_ajax_add_order_item_meta', array( $this, 'ajax_add_order_item_meta' ), 10, 2 );
		add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'display_item_total_payable' ), 10, 3 );

		// Stock management
		add_filter( 'woocommerce_payment_complete_reduce_order_stock', array( $this, 'allow_reduce_order_stock' ), 10, 2 );
		add_filter( 'woocommerce_can_reduce_order_stock', array( $this, 'allow_reduce_order_stock' ), 10, 2 );

		// Downloads manager
		add_filter( 'woocommerce_order_is_download_permitted', array( $this, 'maybe_alter_if_download_permitted' ), 20, 2 );

		// Add pending deposit payment to needs payment functions.
		add_filter( 'woocommerce_valid_order_statuses_for_payment', array( $this, 'add_status_to_needs_payment' ) );
		add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', array( $this, 'add_status_to_needs_payment' ) );

		// Add partial payment as is paid status to WC
		add_filter( 'woocommerce_order_is_paid_statuses', array( $this, 'add_is_paid_status' ) );
	}

	/**
	 * Does the order contain a deposit?
	 *
	 * @version 1.2.1
	 *
	 * @return boolean
	 */
	public static function has_deposit( $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( ! $order ) {
			return false;
		}

		foreach( $order->get_items() as $item ) {
			if ( 'line_item' === $item['type'] && ! empty( $item['is_deposit'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Register our custom post statuses, used for order status.
	 */
	public function register_post_status() {
		register_post_status( 'wc-partial-payment', array(
			'label'                     => _x( 'Partially Paid', 'Order status', 'ultimatewoo-pro' ),
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Partially Paid <span class="count">(%s)</span>', 'Partially Paid <span class="count">(%s)</span>', 'ultimatewoo-pro' ),
		) );
		register_post_status( 'wc-scheduled-payment', array(
			'label'                     => _x( 'Scheduled', 'Order status', 'ultimatewoo-pro' ),
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Scheduled <span class="count">(%s)</span>', 'Scheduled <span class="count">(%s)</span>', 'ultimatewoo-pro' ),
		) );
		register_post_status( 'wc-pending-deposit', array(
			'label'                     => _x( 'Pending Deposit Payment', 'Order status', 'ultimatewoo-pro' ),
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Pending Deposit Payment <span class="count">(%s)</span>', 'Pending Deposits Payment <span class="count">(%s)</span>', 'ultimatewoo-pro' ),
		) );
	}

	/**
	 * Add order statusus to WooCommmerce.
	 *
	 * @param  array $order_statuses
	 * @return array
	 */
	public function add_order_statuses( $order_statuses ) {
		$order_statuses['wc-partial-payment']   = _x( 'Partially Paid', 'Order status', 'ultimatewoo-pro' );
		$order_statuses['wc-scheduled-payment'] = _x( 'Scheduled', 'Order status', 'ultimatewoo-pro' );
		$order_statuses['wc-pending-deposit']   = _x( 'Pending Deposit Payment', 'Order status', 'ultimatewoo-pro' );
		return $order_statuses;
	}

	/**
	 * Statuses that can be completed.
	 *
	 * @param  array $statuses
	 * @return array
	 */
	public function woocommerce_valid_order_statuses_for_payment_complete( $statuses ) {
		$statuses = array_merge( $statuses, array( 'partial-payment', 'scheduled-payment' ) );
		return $statuses;
	}

	/**
	 * Filters the order's status so that we can indicate if this order only has a partial payment
	 * @param string Order status (processing|completed)
	 * @param int Order ID
	 * @return string Order status (processing|completed|partial-payment)
	 */
	public function woocommerce_payment_complete_order_status( $status, $order_id ) {
		if ( empty( $order_id ) ) {
			// Not yet an order in the system - e.g. can happen during order creation when invoicing the remaining balance
			return $status;
		}

		$order = wc_get_order( $order_id );

		// We want to skip status change for these (manual payment) methods because no payment actually occurred.
		$methods_skip_status = array(
			'bacs',
			'cheque',
			'cod',
		);

		$methods_skip_status = apply_filters( 'woocommerce_deposits_methods_skip_status', $methods_skip_status );

		if ( is_object( $order ) && self::has_deposit( $order_id ) && ! in_array( $order->get_payment_method(), $methods_skip_status ) ) {
			$status = 'partial-payment';
		}

		return $status;
	}

	/**
	 * hide scheduled orders from account [age]
	 * @param  array $query
	 */
	public function woocommerce_my_account_my_orders_query( $query ) {
		if ( version_compare( WC_VERSION, '2.6.0', '>=' ) ) {
			$statuses = wc_get_order_statuses();
			unset( $statuses['wc-scheduled-payment'] );
			$query['status'] = array_keys( $statuses );
		} else {
			$query['post_status'] = array_diff( $query['post_status'], array( 'wc-scheduled-payment' ) );
		}

		return $query;
	}

	/**
	 * Process deposits in an order after payment.
	 */
	public function process_deposits_in_order( $order_id ) {
		$order     = wc_get_order( $order_id );
		$parent_id = wp_get_post_parent_id( $order_id );

		// Check if any items need scheduling
		foreach ( $order->get_items() as $order_item_id => $item ) {
			if ( 'line_item' === $item['type'] && ! empty( $item['payment_plan'] ) && empty( $item['payment_plan_scheduled'] ) ) {
				$payment_plan = new WC_Deposits_Plan( absint( $item['payment_plan'] ) );
				$deferred_discount = ( empty( $item['deposit_deferred_discount'] ) ) ? 0 : $item['deposit_deferred_discount'];
				WC_Deposits_Scheduled_Order_Manager::schedule_orders_for_plan( $payment_plan, $order_id, array(
					'product'                   => $order->get_product_from_item( $item ),
					'qty'                       => $item['qty'],
					'price_excluding_tax'       => $item['deposit_full_amount_ex_tax'],
					'deposit_deferred_discount' => $deferred_discount,
				) );
				wc_add_order_item_meta( $order_item_id, '_payment_plan_scheduled', 'yes' );
			}
		}

		// Has parent? See if partially paid
		if ( $parent_id ) {
			$parent_order = wc_get_order( $parent_id );
			if ( $parent_order && $parent_order->has_status( 'partial-payment' ) ) {
				$paid = true;
				foreach ( $parent_order->get_items() as $order_item_id => $item ) {

					if ( WC_Deposits_Order_Item_Manager::is_deposit( $item )
					     && ! WC_Deposits_Order_Item_Manager::is_fully_paid( $item, $parent_order) ) {

						$paid = false;
						break;

					}

				}

				if ( $paid ) {
					// Update the parent order
					$parent_order->update_status( 'completed', __( 'All deposit items fully paid', 'ultimatewoo-pro' ) );
				}

			}
		}
	}

	/**
	 * Create a scheduled order.
	 *
	 * @param  string $payment_date
	 * @param  int    $original_order_id
	 * @param  int    $payment_number
	 * @param  array  $item
	 * @param  string $status
	 * @return id
	 */
	public static function create_order( $payment_date, $original_order_id, $payment_number, $item, $status = '' ) {
		// Handle backwards compat.
		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			return self::create_order_legacy( $payment_date, $original_order_id, $payment_number, $item, $status );
		}

		$original_order = wc_get_order( $original_order_id );

		try {
			$new_order = new WC_Order;
			$new_order->set_props( array(
				'status'              => $status,
				'customer_id'         => $original_order->get_user_id(),
				'customer_note'       => $original_order->get_customer_note(),
				'created_via'         => 'wc_deposits',
				'billing_first_name'  => $original_order->get_billing_first_name(),
				'billing_last_name'   => $original_order->get_billing_last_name(),
				'billing_company'     => $original_order->get_billing_company(),
				'billing_address_1'   => $original_order->get_billing_address_1(),
				'billing_address_2'   => $original_order->get_billing_address_2(),
				'billing_city'        => $original_order->get_billing_city(),
				'billing_state'       => $original_order->get_billing_state(),
				'billing_postcode'    => $original_order->get_billing_postcode(),
				'billing_country'     => $original_order->get_billing_country(),
				'billing_email'       => $original_order->get_billing_email(),
				'billing_phone'       => $original_order->get_billing_phone(),
				'shipping_first_name' => $original_order->get_shipping_first_name(),
				'shipping_last_name'  => $original_order->get_shipping_last_name(),
				'shipping_company'    => $original_order->get_shipping_company(),
				'shipping_address_1'  => $original_order->get_shipping_address_1(),
				'shipping_address_2'  => $original_order->get_shipping_address_2(),
				'shipping_city'       => $original_order->get_shipping_city(),
				'shipping_state'      => $original_order->get_shipping_state(),
				'shipping_postcode'   => $original_order->get_shipping_postcode(),
				'shipping_country'    => $original_order->get_shipping_country(),
			) );
			$new_order->save();
		} catch ( Exception $e ) {
			$original_order->add_order_note( sprintf( __( 'Error: Unable to create follow up payment (%s)', 'ultimatewoo-pro' ), $e->getMessage() ) );
			return;
		}

		// Handle items
		$item_id = $new_order->add_product( $item['product'], $item['qty'], array(
			'totals' => array(
				'subtotal'     => $item['subtotal'], // cost before discount (for line quantity, not just unit)
				'total'        => $item['total'], // item cost (after discount) (for line quantity, not just unit)
				'subtotal_tax' => 0, // calculated within (WC_Abstract_Order) $new_order->calculate_totals
				'tax'          => 0, // calculated within (WC_Abstract_Order) $new_order->calculate_totals
			)
		) );

		$new_order->set_parent_id( $original_order_id );
		$new_order->set_date_created( date( 'Y-m-d H:i:s', $payment_date ) );

		// (WC_Abstract_Order) Calculate totals by looking at the contents of the order. Stores the totals and returns the orders final total.
		$new_order->calculate_totals( wc_tax_enabled() );
		$new_order->save();

		wc_add_order_item_meta( $item_id, '_original_order_id', $original_order_id );

		/* translators: Payment number for product's title */
		wc_update_order_item( $item_id, array( 'order_item_name' => sprintf( __( 'Payment #%d for %s', 'ultimatewoo-pro' ), $payment_number, $item['product']->get_title() ) ) );

		do_action( 'woocommerce_deposits_create_order', $new_order->get_id() );
		return $new_order->get_id();
	}

	/**
	 * Create a scheduled order (for WC 2.6 and below).
	 *
	 * @param  string $payment_date
	 * @param  int    $original_order_id
	 * @param  int    $payment_number
	 * @param  array  $item
	 * @param  string $status
	 * @return id
	 */
	public static function create_order_legacy( $payment_date, $original_order_id, $payment_number, $item, $status = '' ) {
		$original_order = wc_get_order( $original_order_id );
		$new_order      = wc_create_order( array(
			'status'        => $status,
			'customer_id'   => $original_order->get_user_id(),
			'customer_note' => $original_order->customer_note,
			'created_via'   => 'wc_deposits',
		) );
		if ( is_wp_error( $new_order ) ) {
			$original_order->add_order_note( sprintf( __( 'Error: Unable to create follow up payment (%s)', 'ultimatewoo-pro' ), $scheduled_order->get_error_message() ) );
		} else {
			$new_order->set_address( array(
				'first_name' => $original_order->billing_first_name,
				'last_name'  => $original_order->billing_last_name,
				'company'    => $original_order->billing_company,
				'address_1'  => $original_order->billing_address_1,
				'address_2'  => $original_order->billing_address_2,
				'city'       => $original_order->billing_city,
				'state'      => $original_order->billing_state,
				'postcode'   => $original_order->billing_postcode,
				'country'    => $original_order->billing_country,
				'email'      => $original_order->billing_email,
				'phone'      => $original_order->billing_phone,
			), 'billing' );
			$new_order->set_address( array(
				'first_name' => $original_order->shipping_first_name,
				'last_name'  => $original_order->shipping_last_name,
				'company'    => $original_order->shipping_company,
				'address_1'  => $original_order->shipping_address_1,
				'address_2'  => $original_order->shipping_address_2,
				'city'       => $original_order->shipping_city,
				'state'      => $original_order->shipping_state,
				'postcode'   => $original_order->shipping_postcode,
				'country'    => $original_order->shipping_country,
			), 'shipping' );

			// Handle items
			$item_id = $new_order->add_product( $item['product'], $item['qty'], array(
				'totals' => array(
					'subtotal'     => $item['subtotal'], // cost before discount (for line quantity, not just unit)
					'total'        => $item['total'], // item cost (after discount) (for line quantity, not just unit)
					'subtotal_tax' => 0, // calculated within (WC_Abstract_Order) $new_order->calculate_totals
					'tax'          => 0, // calculated within (WC_Abstract_Order) $new_order->calculate_totals
				)
			) );
			wc_add_order_item_meta( $item_id, '_original_order_id', $original_order_id );

			/* translators: Payment number for product's title */
			wc_update_order_item( $item_id, array( 'order_item_name' => sprintf( __( 'Payment #%d for %s', 'ultimatewoo-pro' ), $payment_number, $item['product']->get_title() ) ) );

			// (WC_Abstract_Order) Calculate totals by looking at the contents of the order. Stores the totals and returns the orders final total.
			$new_order->calculate_totals( wc_tax_enabled() );

			// Set future date and parent
			$new_order_post = array(
				'ID'          => $new_order->id,
				'post_date'   => date( 'Y-m-d H:i:s', $payment_date ),
				'post_parent' => $original_order_id,
			);
			wp_update_post( $new_order_post );

			do_action( 'woocommerce_deposits_create_order', $new_order->id );
			return $new_order->id;
		}
	}

	/**
	 * Rename meta key for attribute labels.
	 *
	 * @param  string $label
	 * @param  string $meta_key
	 * @return string
	 */
	public function woocommerce_attribute_label( $label, $meta_key ) {
		switch ( $meta_key ) {
			case '_deposit_full_amount' :
				$label = __( 'Full Amount', 'ultimatewoo-pro' );
				break;
			case '_deposit_full_amount_ex_tax' :
				$label = __( 'Full Amount (excl. tax)', 'ultimatewoo-pro' );
				break;
			case '_deposit_deferred_discount' :
				$label = __( 'Deferred Discount', 'ultimatewoo-pro' );
				break;
			case '_deposit_deposit_amount_ex_tax' :
				$label = __( 'Deposit Amount (excl. tax)', 'ultimatewoo-pro' );
				break;
		}
		return $label;
	}

	/**
	 * Hide meta data.
	 *
	 * @param  array
	 * @return array
	 */
	public function woocommerce_hidden_order_itemmeta( $meta_keys ) {
		$meta_keys[] = '_is_deposit';
		$meta_keys[] = '_remaining_balance_order_id';
		$meta_keys[] = '_remaining_balance_paid';
		$meta_keys[] = '_original_order_id';
		$meta_keys[] = '_payment_plan_scheduled';
		$meta_keys[] = '_payment_plan';
		return $meta_keys;
	}

	/**
	 * Show info before order item meta.
	 */
	public function woocommerce_before_order_itemmeta( $item_id, $item, $_product ) {
		if ( ! WC_Deposits_Order_Item_Manager::is_deposit( $item ) ) {
			return;
		}

		if ( $payment_plan = WC_Deposits_Order_Item_Manager::get_payment_plan( $item ) ) {
			echo ' (' . $payment_plan->get_name() . ')';
		} else {
			echo ' (' . __( 'Deposit', 'ultimatewoo-pro' ) . ')';
		}
	}

	/**
	 * Show info after order item meta.
	 */
	public function woocommerce_after_order_itemmeta( $item_id, $item, $_product ) {
		if ( WC_Deposits_Order_Item_Manager::is_deposit( $item ) ) {

			if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
				global $wpdb;
				$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = %d", $item_id ) );
				$order    = wc_get_order( $order_id );
				$currency = $order->get_order_currency();
			} else {
				$order_id = $item->get_order_id();
				$order    = wc_get_order( $order_id );
				$currency = $order->get_currency();
			}

			// Plans
			if ( $payment_plan = WC_Deposits_Order_Item_Manager::get_payment_plan( $item ) ) {
				echo '<a href="' . esc_url( admin_url( 'edit.php?post_status=wc-scheduled-payment&post_type=shop_order&post_parent=' . $order_id ) ) . '" target="_blank" class="button button-small">' . __( 'View Scheduled Payments', 'ultimatewoo-pro' ) . '</a>';

			// Regular deposits
			} else {
				$remaining                  = $item['deposit_full_amount'] - $order->get_line_total( $item, true );
				$remaining_balance_order_id = ! empty( $item['remaining_balance_order_id'] ) ? absint( $item['remaining_balance_order_id'] ) : 0;
				$remaining_balance_paid     = ! empty( $item['remaining_balance_paid'] );

				if ( $remaining_balance_order_id && ( $remaining_balance_order = wc_get_order( $remaining_balance_order_id ) ) ) {
					echo '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $remaining_balance_order_id ) . '&action=edit' ) ) . '" target="_blank" class="button button-small">' . sprintf( __( 'Remainder - Invoice #%1$s', 'ultimatewoo-pro' ), $remaining_balance_order->get_order_number() ) . '</a>';
				} elseif( $remaining_balance_paid ) {
					printf( __( 'The remaining balance of %s (plus tax) for this item was paid offline.', 'ultimatewoo-pro' ), wc_price( $remaining, array( 'currency' => $currency ) ) );
					echo ' <a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'mark_deposit_unpaid' => $item_id ) ), 'mark_deposit_unpaid', 'mark_deposit_unpaid_nonce' ) ) . '" class="button button-small">' . sprintf( __( 'Unmark as Paid', 'ultimatewoo-pro' ) ) . '</a>';
				} else {
					?>
					<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'invoice_remaining_balance' => $item_id ), admin_url( 'post.php?post=' . $order_id . '&action=edit' ) ), 'invoice_remaining_balance', 'invoice_remaining_balance_nonce' ) ); ?>" class="button button-small"><?php _e( 'Invoice Remaining Balance', 'ultimatewoo-pro' ); ?></a>
					<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'mark_deposit_fully_paid' => $item_id ), admin_url( 'post.php?post=' . $order_id . '&action=edit' ) ), 'mark_deposit_fully_paid', 'mark_deposit_fully_paid_nonce' ) ); ?>" class="button button-small"><?php printf( __( 'Mark Paid (offline)', 'ultimatewoo-pro' ) ); ?></a>
					<?php
				}
			}
		} elseif ( ! empty( $item['original_order_id'] ) ) {
			echo '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $item['original_order_id'] ) . '&action=edit' ) ) . '" target="_blank" class="button button-small">' . __( 'View Original Order', 'ultimatewoo-pro' ) . '</a>';
		}
	}

	/**
	 * Create and redirect to an invoice.
	 */
	public function order_action_handler() {
		global $wpdb;

		$action  = false;
		$item_id = false;

		if ( ! empty( $_GET['mark_deposit_unpaid'] ) && isset( $_GET['mark_deposit_unpaid_nonce'] ) && wp_verify_nonce( $_GET['mark_deposit_unpaid_nonce'], 'mark_deposit_unpaid' ) ) {
			$action  = 'mark_deposit_unpaid';
			$item_id = absint( $_GET['mark_deposit_unpaid'] );
		}

		if ( ! empty( $_GET['mark_deposit_fully_paid'] ) && isset( $_GET['mark_deposit_fully_paid_nonce'] ) && wp_verify_nonce( $_GET['mark_deposit_fully_paid_nonce'], 'mark_deposit_fully_paid' ) ) {
			$action  = 'mark_deposit_fully_paid';
			$item_id = absint( $_GET['mark_deposit_fully_paid'] );
		}

		if ( ! empty( $_GET['invoice_remaining_balance'] ) && isset( $_GET['invoice_remaining_balance_nonce'] ) && wp_verify_nonce( $_GET['invoice_remaining_balance_nonce'], 'invoice_remaining_balance' ) ) {
			$action  = 'invoice_remaining_balance';
			$item_id  = absint( $_GET['invoice_remaining_balance'] );
		}

		if ( ! $item_id ) {
			return;
		}

		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = %d", $item_id ) );
		} else {
			$order_id = wc_get_order_id_by_order_item_id( $item_id );
		}

		$order_id = $wpdb->get_var( $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = %d", $item_id ) );
		$order    = wc_get_order( $order_id );
		$item     = false;

		foreach ( $order->get_items() as $order_item_id => $order_item ) {
			if ( $item_id === $order_item_id ) {
				$item = $order_item;
			}
		}

		if ( ! $item || empty( $item['is_deposit'] ) ) {
			return;
		}

		switch ( $action ) {
			case 'mark_deposit_unpaid' :
				wc_delete_order_item_meta( $item_id, '_remaining_balance_paid', 1, true );
				wp_redirect( admin_url( 'post.php?post=' . absint( $order_id ) . '&action=edit' ) );
				exit;
			case 'mark_deposit_fully_paid' :
				wc_add_order_item_meta( $item_id, '_remaining_balance_paid', 1 );
				wp_redirect( admin_url( 'post.php?post=' . absint( $order_id ) . '&action=edit' ) );
				exit;
			case 'invoice_remaining_balance' :
				// Used for products with fixed deposits or percentage based deposits. Not used for payment plan products
				// See WC_Deposits_Schedule_Order_Manager::schedule_orders_for_plan for creating orders for products with payment plans

				// First, get the deposit_full_amount_ex_tax - this contains the full amount for the item excluding tax - see
				// WC_Deposits_Cart_Manager::add_order_item_meta_legacy or add_order_item_meta for where we set this amount
				// Note that this is for the line quantity, not necessarily just for quantity 1
				$full_amount_excl_tax = floatval( $item['deposit_full_amount_ex_tax'] );

				// Next, get the initial deposit already paid, excluding tax
				$amount_already_paid = floatval( $item['deposit_deposit_amount_ex_tax'] );

				// Then, set the item subtotal that will be used in create order to the full amount less the amount already paid
				$subtotal = $full_amount_excl_tax - $amount_already_paid;

				// Lastly, subtract the deferred discount from the subtotal to get the total to be used to create the order
				$discount = floatval( $item['deposit_deferred_discount'] );
				$total = empty( $discount ) ? $subtotal : $subtotal - $discount;

				// And then create an order with this item
				$create_item = array(
					'product'   => $order->get_product_from_item( $item ),
					'qty'       => $item['qty'],
					'subtotal'  => $subtotal,
					'total'     => $total
				);

				$new_order_id = $this->create_order( current_time( 'timestamp' ), $order_id, 2, $create_item, 'pending-deposit' );

				wc_add_order_item_meta( $item_id, '_remaining_balance_order_id', $new_order_id );

				// Email invoice
				$emails = WC_Emails::instance();
				$emails->customer_invoice( wc_get_order( $new_order_id ) );

				wp_redirect( admin_url( 'post.php?post=' . absint( $new_order_id ) . '&action=edit' ) );
				exit;
		}
	}

	/**
	 * Sends an email when a partial payment is made.
	 */
	public function payment_complete_handler( $order_id ) {
		$order = wc_get_order( $order_id );

		$post_status = 'wc-' . $order->get_status();
		if ( 'wc-partial-payment' !== $post_status ) {
			return;
		}

		$wc_emails      = WC_Emails::instance();
		$customer_email = $wc_emails->emails['WC_Email_Customer_Processing_Order'];
		$admin_email    = $wc_emails->emails['WC_Email_New_Order'];
		$customer_email->trigger( $order );
		$admin_email->trigger( $order );
	}

	/**
	 * Adds partial payment as is paid status.
	 *
	 * @param array $order_statuses
	 */
	public function add_is_paid_status( $order_statuses ) {
		$order_statuses[] = 'partial-payment';
		return $order_statuses;
	}

	/**
	 * Append text to item names when viewing an order.
	 */
	public function woocommerce_order_item_name( $item_name, $item ) {
		if ( WC_Deposits_Order_Item_Manager::is_deposit( $item ) ) {
			if ( $payment_plan = WC_Deposits_Order_Item_Manager::get_payment_plan( $item ) ) {
				$item_name .= ' (' . $payment_plan->get_name() . ')';
			} else {
				$item_name .= ' (' . __( 'Deposit', 'ultimatewoo-pro' ) . ')';
			}
		}
		return $item_name;
	}

	/**
	 * Add info about a deposit when viewing an order.
	 */
	public function woocommerce_order_item_meta_end( $item_id, $item, $order ) {
		if ( WC_Deposits_Order_Item_Manager::is_deposit( $item ) && ! WC_Deposits_Order_Item_Manager::get_payment_plan( $item ) ) {
			$remaining                  = $item['deposit_full_amount'] - $order->get_line_total( $item, true );
			$remaining_balance_order_id = ! empty( $item['remaining_balance_order_id'] ) ? absint( $item['remaining_balance_order_id'] ) : 0;
			$remaining_balance_paid     = ! empty( $item['remaining_balance_paid'] );
			$currency                   = is_callable( array( $order, 'get_currency' ) ) ? $order->get_currency() : $order->get_order_currency();

			if ( $remaining_balance_order_id && ( $remaining_balance_order = wc_get_order( $remaining_balance_order_id ) ) ) {
				echo '<p class="wc-deposits-order-item-description"><a href="' . esc_url( $remaining_balance_order->get_view_order_url() ) . '">' . sprintf( __( 'Remainder - Invoice #%1$s', 'ultimatewoo-pro' ), $remaining_balance_order->get_order_number() ) . '</a></p>';
			} elseif ( $remaining_balance_paid ) {
				printf( '<p class="wc-deposits-order-item-description">' . __( 'The remaining balance of %s for this item was paid offline.', 'ultimatewoo-pro' ) . '</p>', wc_price( $remaining, array( 'currency' => $currency ) ) );
			}
		}
	}

	/**
	 * Adjust totals display.
	 *
	 * @param  array $total_rows
	 * @param  WC_Order $order
	 * @return array
	 */
	public function woocommerce_get_order_item_totals( $total_rows, $order ) {
		if ( $this->has_deposit( $order ) ) {
			$remaining       = 0;
			$paid            = 0;
			$total_payable   = 0;
			$is_tax_included = wc_tax_enabled() && 'excl' !== get_option( 'woocommerce_tax_display_cart' );

			foreach( $order->get_items() as $item ) {
				if ( WC_Deposits_Order_Item_Manager::is_deposit( $item ) ) {
					$total_payable += $item['deposit_full_amount_ex_tax'];

					if ( ! WC_Deposits_Order_Item_Manager::get_payment_plan( $item ) ) {
						$remaining_balance_order_id = ! empty( $item['remaining_balance_order_id'] ) ? absint( $item['remaining_balance_order_id'] ) : 0;
						$remaining_balance_paid     = ! empty( $item['remaining_balance_paid'] );

						if ( empty( $remaining_balance_order_id ) && ! $remaining_balance_paid ) {

							if ( $is_tax_included ) {
								// do not show tax if included
								$excluded_tax_amount = $is_tax_included ? 0 : $item['line_tax'];
								$item_remaining      = $item['deposit_full_amount'] - ( $order->get_line_subtotal( $item, true ) + $excluded_tax_amount );
							} else {
								$item_remaining = $item['deposit_full_amount_ex_tax'] - $order->get_line_subtotal( $item, false );
							}

							$remaining += $item_remaining;
						}
					}
				}
			}

			// PAID scheduled orders
			$order_id       = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
			$related_orders = WC_Deposits_Scheduled_Order_Manager::get_related_orders( $order_id );

			foreach ( $related_orders as $related_order_id ) {
				$related_order     = wc_get_order( $related_order_id );
				$total_with_tax    = $related_order->get_total();
				$total_without_tax = $total_with_tax - $related_order->get_total_tax();

				if ( $related_order->has_status( 'processing', 'completed' ) ) {
					$paid      += $is_tax_included ? $total_with_tax : $total_without_tax;
				} else {
					$remaining += $is_tax_included ? $total_with_tax : $total_without_tax;
				}
			}

			$tax_message = $is_tax_included ? __( '(includes tax)', 'ultimatewoo-pro' ) : __( '(excludes tax)', 'ultimatewoo-pro' ) ;
			$tax_element = wc_tax_enabled() ? ' <small class="tax_label">' . $tax_message . '</small>' : '';

			if ( $remaining && $paid ) {
				$total_rows['future'] = array(
					'label' => __( 'Future Payments', 'ultimatewoo-pro' ),
					'value'	=> '<del>' . wc_price( $remaining + $paid ) . '</del> <ins>' . wc_price( $remaining ) . $tax_element . '</ins>'
				);
			} elseif ( $remaining ) {
				$total_rows['future'] = array(
					'label' => __( 'Future Payments', 'ultimatewoo-pro' ),
					'value'	=> wc_price( $remaining ) . $tax_element
				);
			}

			// Rebuild the order total rows to include our own information and
			// makes the order intact.
			foreach ( $total_rows as $row => $value ) {
				if ( 'order_total' === $row ) {
					// change the total label
					$value['label'] = __( 'Total Due Today:', 'ultimatewoo-pro' );
					$new_total_rows[ $row ] = $value;
				} else {
					$new_total_rows[ $row ] = $value;
				}
			}

			$total_rows = $new_total_rows;
		}

		return $total_rows;
	}

	/**
	 * Admin filters.
	 */
	public function request_query( $vars ) {
		global $typenow, $wp_query, $wp_post_statuses;

		if ( in_array( $typenow, wc_get_order_types( 'order-meta-boxes' ) ) ) {
			if ( isset( $_GET['post_parent'] ) && $_GET['post_parent'] > 0 ) {
				$vars['post_parent'] = absint( $_GET['post_parent'] );
			}
		}

		return $vars;
	}

	/**
	 * Triggered when adding an item in the backend.
	 * If deposits are forced, set all meta data.
	 */
	public function ajax_add_order_item_meta( $item_id, $item ) {
		if ( WC_Deposits_Product_Manager::deposits_forced( $item['product_id'] ) ) {
			$product = wc_get_product( absint( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] ) );
			wc_add_order_item_meta( $item_id, '_is_deposit', 'yes' );
			wc_add_order_item_meta( $item_id, '_deposit_full_amount', $item['line_total'] );
			wc_add_order_item_meta( $item_id, '_deposit_full_amount_ex_tax', $item['line_total'] );

			if ( 'plan' === WC_Deposits_Product_Manager::get_deposit_type( $item['product_id'] ) ) {
				$plan_id = current( WC_Deposits_Plans_Manager::get_plan_ids_for_product( $item['product_id'] ) );
				wc_add_order_item_meta( $item_id, '_payment_plan', $plan_id );
			} else {
				$plan_id = 0;
			}

			// Change line item costs
			$deposit_amount = WC_Deposits_Product_Manager::get_deposit_amount( $product, $plan_id, 'order', $item['line_total'] );
			wc_update_order_item_meta( $item_id, '_line_total', $deposit_amount );
			wc_update_order_item_meta( $item_id, '_line_subtotal', $deposit_amount );
		}
	}

	/**
	 * Display total payable of deposit item in order item details.
	 *
	 * @since 1.1.10
	 * @param float    $subtotal Line subtotal
	 * @param array    $item     Order item
	 * @param WC_Order $order    Order object
	 * @return string Formatted subtotal
	 */
	public function display_item_total_payable( $subtotal, $item, $order ) {
		if ( ! isset( $item['deposit_full_amount'] ) ) {
			return $subtotal;
		}

		if ( ! empty( $item['is_deposit'] ) ) {
			$_product    = wc_get_product( $item['product_id'] );
			$quantity    = $item['qty'];
			$full_amount = 'excl' === get_option( 'woocommerce_tax_display_cart' ) ? $item['deposit_full_amount_ex_tax'] : $item['deposit_full_amount'];

			if ( ! empty( $item['payment_plan'] ) ) {
				$plan = new WC_Deposits_Plan( $item['payment_plan'] );
				$subtotal .= '<br/><small>' . $plan->get_formatted_schedule( $full_amount ) . '</small>';
			} else {
				$subtotal .= '<br/><small>' . sprintf( __( '%s payable in total', 'ultimatewoo-pro' ), wc_price( $full_amount ) ) . '</small>';
			}
		}

		return $subtotal;
	}

	/**
	 * Should the order stock be reduced?
	 *
	 * @return bool
	 */
	public function allow_reduce_order_stock( $allowed, $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		// Don't reduce stock on follow up orders
		$created_via = is_callable( array( $order, 'get_created_via' ) ) ? $order->get_created_via() : $order->created_via;
		if ( 'wc_deposits' === $created_via ) {
			$allowed = false;
		}

		return $allowed;
	}

	/**
	 * Alter if a download is permitted.
	 *
	 * Downloads should only be permitted for deposit plans if all
	 * orders are paid.
	 *
	 * @since  1.1.7
	 * @param  bool $permitted
	 * @param  WC_Order $order
	 * @return bool @permitted
	 */
	public function maybe_alter_if_download_permitted( $permitted, $order ) {
		// get the parent order which is the main item
		foreach ( $order->get_items() as $item  ) {
			if ( ! isset( $item['original_order_id'] ) ) {
				continue;
			}

			$parent_order_id = $item['original_order_id'];
			$parent_order    = wc_get_order( $parent_order_id );

			if ( ! $this->has_deposit( $parent_order ) ) {
				continue;
			}

			return WC_Deposits_Plans_Manager::is_order_plan_fully_paid( $parent_order );
		}

		return $permitted;
	}

	/**
	 * Adds pending-deposit order status for inclusion for payment processes.
	 *
	 * @since  1.1.8
	 * @param  array $statuses
	 * @return array $statuses
	 */
	public function add_status_to_needs_payment( $statuses ) {
		$statuses[] = 'pending-deposit';
		return $statuses;
	}
}

WC_Deposits_Order_Manager::get_instance();
