<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles the display of scheduled orders in My Account.
 */
class WC_Deposits_My_Account {
	/**
	 * Custom endpoint name.
	 *
	 * @var string
	 */
	public static $endpoint;

	/**
	 * Plugin actions.
	 */
	public function __construct() {
		self::$endpoint = apply_filters( 'woocoommerce_deposits_my_account_end_point', 'scheduled-orders' );

		// Actions used to insert a new endpoint in the WordPress.
		add_action( 'init', array( $this, 'add_endpoints' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// Change the My Account page title.
		add_filter( 'the_title', array( $this, 'endpoint_title' ) );

		// Insering your new tab/page into the My Account page.
		add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );
		add_action( 'woocommerce_account_' . self::$endpoint .  '_endpoint', array( $this, 'endpoint_content' ) );
	}

	/**
	 * Register new endpoint to use inside My Account page.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
	 */
	public function add_endpoints() {
		add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
	}

	/**
	 * Add new query var.
	 *
	 * @param array $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = self::$endpoint;

		return $vars;
	}

	/**
	 * Set endpoint title.
	 *
	 * @param string $title
	 * @return string
	 */
	public function endpoint_title( $title ) {
		global $wp_query;

		$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );

		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			// New page title.
			$title = __( 'Scheduled Orders', 'ultimatewoo-pro' );

			remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
		}

		return $title;
	}

	/**
	 * Insert the new endpoint into the My Account menu.
	 *
	 * @param array $items
	 * @return array
	 */
	public function new_menu_items( $items ) {
		$rebuilt_menu = array();

		// Rebuilt the array to position our menu item after orders.
		foreach ( $items as $key => $value ) {
			if ( 'orders' === $key ) {
				$rebuilt_menu[ $key ] = $value;
				$rebuilt_menu[ self::$endpoint ] = __( 'Scheduled Orders', 'ultimatewoo-pro' );
			} else {
				$rebuilt_menu[ $key ] = $value;
			}
		}

		return $rebuilt_menu;
	}

	/**
	 * Endpoint HTML content.
	 */
	public function endpoint_content() {
		$current_page    = empty( $current_page ) ? 1 : absint( $current_page );
		$customer_orders = wc_get_orders( apply_filters( 'woocommerce_deposits_my_account_query', array( 'customer' => get_current_user_id(), 'page' => $current_page, 'paginate' => true, 'post_status' =>  array( 'wc-scheduled-payment' ) ) ) );

		wc_get_template(
			'myaccount/orders.php',
			array(
				'current_page' => absint( $current_page ),
				'customer_orders' => $customer_orders,
				'has_orders' => 0 < $customer_orders->total,
			)
		);
	}

}

new WC_Deposits_My_Account();
