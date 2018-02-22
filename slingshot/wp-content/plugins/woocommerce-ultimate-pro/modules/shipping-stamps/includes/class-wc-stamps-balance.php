<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Stamps_Balance class
 */
class WC_Stamps_Balance {

	private static $logger;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Cron jobs
		add_action( 'wc_stamps_do_top_up', array( $this, 'top_up' ), 10, 3 );
		add_action( 'wc_stamps_check_payment_status', array( $this, 'check_payment_status' ), 10, 4 );

		// Admin only hooks
		if ( current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 999 );
			add_action( 'admin_menu', array( $this, 'admin_menus') );
			add_action( 'admin_head', array( $this, 'admin_head' ) );
			add_action( 'admin_init', array( $this, 'stamps_redirect' ) );
		}
	}

	/**
	 * Add log entry
	 */
	public static function log( $message ) {
		if ( ! self::$logger ) {
			self::$logger = new WC_Logger();
		}
		self::$logger->add( 'stamps-balance', $message );
	}

	/**
	 * Show balance on admin bar
	 */
	public function admin_bar() {
		global $wp_admin_bar;

		if ( ! is_admin() || ! is_admin_bar_showing() || ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$balance = $this->get_current_balance( isset( $_GET['wc-stamps-refresh'] ) );

		if ( false === $balance ) {
			$balance_string     = __( 'API Error', 'ultimatewoo-pro' );
			$balance_node_class = 'error-api';
		} else {
			$balance_string     = '$' . number_format( $balance, 2, '.', ',' );
			$balance_node_class = '';
		}

		$wp_admin_bar->add_node( array(
			'id'     => 'stamps-com',
			'parent' => 'top-secondary',
			'title'  => '<span class="ab-icon"></span> Stamps: ' . $balance_string,
			'href'   => add_query_arg( 'wc-stamps-refresh', 'true' ),
			'meta'   => array( 'class' =>  $balance_node_class ),
		) );

		$wp_admin_bar->add_menu( array(
			'parent' => 'stamps-com',
			'id'     => 'stamps-com-topup',
			'title'  => __( 'Top-up Balance', 'ultimatewoo-pro' ),
			'href'   => admin_url( 'index.php?page=wc-stamps-topup' ),
			'meta'   => false
		) );

		$menu_links = array(
			'StoreMyProfile'         => __( 'My profile', 'ultimatewoo-pro' ),
			'StorePaymentMethods'    => __( 'Stamps payment methods', 'ultimatewoo-pro' ),
			'OnlineReportingClaim'   => __( 'Online claim form', 'ultimatewoo-pro' ),
			'OnlineReportingSCAN'    => __( 'Online SCAN form', 'ultimatewoo-pro' ),
			'OnlineReportingPickup'  => __( 'Schedule a pickup', 'ultimatewoo-pro' ),
			'OnlineReportingRefund'  => __( 'Refunds', 'ultimatewoo-pro' ),
			'OnlineReportingHistory' => __( 'History', 'ultimatewoo-pro' ),
		);

		foreach ( $menu_links as $key => $value ) {
			$wp_admin_bar->add_menu( array(
				'parent' => 'stamps-com',
				'id'     => 'stamps-com-' . sanitize_title( $key ),
				'title'  => $value,
				'href'   => add_query_arg( 'stamps_redirect', $key, admin_url() ),
				'meta'   => false
			) );
		}
	}

	/**
	 * Add admin menus/screens
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menus() {
		if ( empty( $_GET['page'] ) ) {
			return;
		}
		switch ( $_GET['page'] ) {
			case 'wc-stamps-topup' :
				$page = add_dashboard_page( __( 'Stamps.com balance top-up', 'ultimatewoo-pro' ), __( 'Stamps.com top-up', 'ultimatewoo-pro' ), 'manage_options', 'wc-stamps-topup', array( $this, 'topup_screen' ) );
			break;
		}
	}

	/**
	 * Remove dashboard page links.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'wc-stamps-topup' );
	}

	/**
	 * Redirect to stamps.com
	 */
	public function stamps_redirect() {
		if ( ! empty( $_GET['stamps_redirect'] ) ) {
			$url = WC_Stamps_API::get_url( sanitize_text_field( $_GET['stamps_redirect'] ) );

			if ( $url ) {
				wp_redirect( $url );
				exit;
			}
		}
	}

	/**
	 * Get the current balance for the user
	 *
	 * @param bool $force_update default false.
	 * @return mixed. False in API error OR float from API.
	 */
	public static function get_current_balance( $force_update = false ) {
		$wc_stamps_balance = get_transient( 'wc_stamps_balance' );

		if ( false === $wc_stamps_balance || $force_update ) {
			$info = WC_Stamps_API::get_account_info();

			if ( isset( $info->AccountInfo ) && isset( $info->AccountInfo->PostageBalance ) ) {
				$wc_stamps_balance = $info->AccountInfo->PostageBalance->AvailablePostage;
				set_transient( 'wc_stamps_balance', $info->AccountInfo->PostageBalance->AvailablePostage, DAY_IN_SECONDS );
				set_transient( 'wc_stamps_control_total', $info->AccountInfo->PostageBalance->ControlTotal, DAY_IN_SECONDS );
			} else {
				$wc_stamps_balance = 'API_ERROR';
				set_transient( 'wc_stamps_balance', $wc_stamps_balance , DAY_IN_SECONDS );
				set_transient( 'wc_stamps_control_total', $wc_stamps_balance, DAY_IN_SECONDS );
			}
		}

		// functions calling this one expects bool or float
		if ( 'API_ERROR' === $wc_stamps_balance ) {
			$wc_stamps_balance = false;
		}

		return $wc_stamps_balance;
	}

	/**
	 * Get current control total
	 * @param  boolean $force_update
	 * @return float
	 */
	public static function get_current_control_total( $force_update = false ) {
		if ( ( false === ( $wc_stamps_control_total = get_transient( 'wc_stamps_control_total' ) ) ) || $force_update ) {
			$info = WC_Stamps_API::get_account_info();

			if ( isset( $info->AccountInfo ) && isset( $info->AccountInfo->PostageBalance ) ) {
				$wc_stamps_control_total = $info->AccountInfo->PostageBalance->ControlTotal;
				set_transient( 'wc_stamps_balance', $info->AccountInfo->PostageBalance->AvailablePostage, DAY_IN_SECONDS );
				set_transient( 'wc_stamps_control_total', $info->AccountInfo->PostageBalance->ControlTotal, DAY_IN_SECONDS );
			} else {
				$wc_stamps_control_total = false;
			}
		}
		return $wc_stamps_control_total;
	}

	/**
	 * Top up user balance by X
	 * @param  array $args
	 * @return bool|WP_Error
	 */
	public function top_up( $amount, $control_total, $attempt ) {

		self::log( 'Topping up: ' . $amount . ' - Control: ' . $control_total );

		wp_clear_scheduled_hook( 'wc_stamps_do_top_up', array( $amount, $control_total ) );

		// Check control total for event matches stored control total before proceeding
		if ( $control_total != $this->get_current_control_total() ) {
			self::log( 'Control total does not match current balance (' . $control_total . '!=' . $this->get_current_control_total() . ')' );
			return;
		}

		$result = WC_Stamps_API::purchase_postage( $amount, $control_total );

		if ( is_wp_error( $result ) ) {
			// If there was an error, either the token was invalid or the control total was wrong. Refresh total and reschedule.
			if ( 3 >= $attempt ) {
				self::schedule_top_up( $amount, $this->get_current_control_total( true ),false, $attempt );
				self::log( 'Topping up error: ' . $result->get_error_message() );
			} else {
				$notice = 'Hi, '
						.'Stamps.com plugin top up attempts have failed. There may be problem on your server. '
						.'If you are unable to see your account balance on the WordPress admin contact Stamps.com for more
						 information on the connection issue.';
				self::log( $notice );
				wp_mail( get_bloginfo( 'admin_email' ), get_bloginfo( 'name' ) .': Stamps.com top up Failed', $notice );
			}
		} else {
			switch( $result->PurchaseStatus ) {
				case "Pending" :
				case "Processing" :
					wp_schedule_single_event( time() + 8, 'wc_stamps_check_payment_status', array( $amount, $control_total, $result->TransactionID, 1 ) );
					self::log( 'Top up pending' );
				break;
				case "Rejected" :
					wp_mail( get_option( 'admin_email' ), __( 'Stamps.com top-up failure', 'ultimatewoo-pro' ), $result->RejectionReason );
					self::log( 'Top up rejected:' . $result->RejectionReason );
				break;
				case "Success" :
					self::log( 'Top up successful' );
				break;
			}
		}
	}

	/**
	 * Check status of a top up
	 * @param  array $args
	 */
	public function check_payment_status( $amount, $control_total, $transaction_id, $attempt ) {
		self::log( 'Checking payment status: ' . $amount . '. Attempt #' . $attempt );

		wp_clear_scheduled_hook( 'wc_stamps_check_payment_status', array( $amount, $control_total, $transaction_id, $attempt ) );

		$result = WC_Stamps_API::get_purchase_status( $transaction_id );

		if ( ! is_wp_error( $result ) ) {
			switch( $result->PurchaseStatus ) {
				case "Pending" :
				case "Processing" :
					if ( $attempt < 5 ) {
						wp_schedule_single_event( time() + ( min( 8 * $attempt, 32 ) ), 'wc_stamps_check_payment_status', array( $amount, $control_total, $transaction_id, $attempt + 1 ) );
						self::log( 'Top up still pending' );
					} else {
						self::log( 'Top up payment status check failed' );
					}
				break;
				case "Rejected" :
					wp_mail( get_option( 'admin_email' ), __( 'Stamps.com top-up failure', 'ultimatewoo-pro' ), $result->RejectionReason );
					self::log( 'Top up rejected:' . $result->RejectionReason );
				break;
				case "Success" :
					self::log( 'Top up successful' );
				break;
			}
		}
	}

	/**
	 * See if we need to top up soon
	 * @param  float $balance
	 */
	public static function check_balance( $balance ) {
		if ( is_numeric( $balance ) && get_option( 'wc_settings_stamps_top_up_threshold' ) && $balance < get_option( 'wc_settings_stamps_top_up_threshold' ) && get_option( 'wc_settings_stamps_purchase_amount' ) > 0 ) {
			self::schedule_top_up( absint( get_option( 'wc_settings_stamps_purchase_amount' ) ), self::get_current_control_total() );
		}
	}

	/**
	 * Schedule events for topping up
	 */
	public static function schedule_top_up( $amount, $control_total, $force = false, $attempt = 0 ) {

		++$attempt;
		if ( ! wp_next_scheduled( 'wc_stamps_do_top_up', array( $amount, $control_total, $attempt ) ) || $force ) {
			// Schedule top up
			wp_schedule_single_event( time() + 8, 'wc_stamps_do_top_up', array( $amount, $control_total, $attempt ) );

			self::log( 'Top up scheduled (' . $amount . ')' );

		} else {

			self::log( 'Top up already scheduled (' . $amount . ')' );

		}
	}

	/**
	 * Screen for adding stamps balance manually
	 */
	public function topup_screen() {
		if ( ! empty( $_POST['stamps_topup_amount'] ) ) {
			check_admin_referer( 'woocommerce-stamps-topup' );

			self::schedule_top_up( absint( $_POST['stamps_topup_amount'] ), $this->get_current_control_total(), true );

			echo '<div class="updated"><p>' . __( 'Top-up request sent. Your balance should appear shortly if successful.', 'ultimatewoo-pro' ) . '</p></div>';
		}
		?>
		<div class="wrap">
			<h2><?php _e( 'Add Stamps.com Balance', 'ultimatewoo-pro' ); ?></h2>
			<p><?php _e( 'Enter the amount of postage (in dollars) you wish to purchase. It can take a few minutes for this postage to show up in your account.', 'ultimatewoo-pro' ); ?></p>

			<form method="POST">
				<table class="form-table">
					<tr>
						<th><label for="stamps_topup_amount"><?php _e( 'Amount', 'ultimatewoo-pro' ); ?></label></th>
						<td>
							<input name="stamps_topup_amount" id="stamps_topup_amount" type="number" pattern="\d*" placeholder="<?php echo wc_format_localized_price( 0 ); ?>" min="10" value="10" />
							<p class="description"><?php _e( 'How much balance you wish to purchase in whole dollars e.g. <code>100</code>.', 'ultimatewoo-pro' ); ?></p>
						</td>
					</tr>
				</table>
				<p class="submit">
					<?php wp_nonce_field( 'woocommerce-stamps-topup' ); ?>
					<button type="submit" class="button button-primary"><?php _e( 'Purchase postage', 'ultimatewoo-pro' ); ?></button>
				</p>
			</form>
		</div>
		<?php
	}
}
new WC_Stamps_Balance();
