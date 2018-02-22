<?php
/*
	Copyright 2014 Prospress Inc.  (email : freedoms@prospress.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/**
 * Check if WooCommerce is active, and if it isn't, disable gateway & show a warning.
 *
 * @since 1.0
 */
if ( ! is_woocommerce_active() ) {
	add_action( 'admin_notices', 'ppdg_woocommerce_inactive_notice' );
	return;
}

function init_paypal_digital_goods_gateway() {

	require_once( 'lib/paypal-digital-goods/paypal-purchase.class.php' );
	require_once( 'lib/paypal-digital-goods/paypal-subscription.class.php' );
	require_once( 'classes/class-wc-gateway-paypal-digital-goods.php' );

	/**
	 * Adds the PayPal Digital Goods for Express Checkout gateway to WooCommerce.
	 *
	 * @param array The existing WooCommerce payment gateways.
	 * @return array The array of all WooCommerce payment gateways, including PayPal Digital Goods.
	 **/
	function ppdg_add_paypal_digital_goods_gateway( $methods ) {
		$methods[] = 'WC_Gateway_Paypal_Digital_Goods';
		return $methods;
	}
	add_filter( 'woocommerce_payment_gateways', 'ppdg_add_paypal_digital_goods_gateway' );
}
add_action( 'plugins_loaded', 'init_paypal_digital_goods_gateway', 0 );

/**
 * Display activation, incorrect version and inactive WC notices. Needs to be outside of WC_Gateway_Paypal_Digital_Goods
 * because of WC 2.0 loads gateways only when needed.
 *
 * @since 2.2.3
 */
function ppdg_admin_notices() {
	global $woocommerce;

	if ( ! is_woocommerce_active() ) { ?>
		<div id="message" class="error">
			<p><?php printf( __( '%sThe PayPal Digital Goods extension for WooCommerce is inactive.%s The %sWooCommerce plugin%s must be active for the PayPal Digital Goods extension to work. Please %sinstall & activate WooCommerce%s', 'ultimatewoo-pro' ), '<strong>', '</strong>', '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url( 'plugins.php' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
		</div>
	<?php } elseif ( get_transient( 'ppdg-activated' ) == 'true' ) {
		delete_transient( 'ppdg-activated' ); ?>
		<div class="updated woocommerce-message wc-connect">
			<p>
				<?php printf( __( '%sPayPal Digital Goods Installed%s &#8211; enable the gateway &amp; set your credentials on the %sGateway Settings%s page.', 'ultimatewoo-pro' ), '<strong>', '</strong>', '<a href="' . ppdg_settings_tab_url() . '">', '</a>' ); ?>
			</p>
		</div><?php
	}
}
add_action( 'admin_notices', 'ppdg_admin_notices' );

/**
 * Enqueues necessary admin styles
 *
 * @since 2.2.3
 */
function ppdg_enqueue_admin_styles() {

	if ( get_transient( 'ppdg-activated' ) == 'true' && is_woocommerce_active() ) {

		wp_enqueue_style( 'woocommerce-activation', plugins_url(  '/assets/css/activation.css', ppdg_get_woocommerce_plugin_file() ) );

		if ( ppdg_is_woocommerce_pre_2_1() ) {
			wp_enqueue_style( 'ppdg-activation', plugins_url( '/css/activation.css', __FILE__ ), array( 'woocommerce-activation' ) );
		}
	}
}
add_action( 'admin_init', 'ppdg_enqueue_admin_styles' );

/**
 * Searches through the list of active plugins to find WooCommerce. Just in case WooCommerce resides in a folder other than /woocommerce/
 *
 * @since 2.2.3
 */
function ppdg_get_woocommerce_plugin_file() {
	foreach ( get_option( 'active_plugins', array() ) as $plugin ) {
		if ( substr( $plugin, strlen( '/woocommerce.php' ) * -1 ) === '/woocommerce.php' ) {
			$woocommerce_plugin_file = $plugin;
			break;
		}
	}

	return $woocommerce_plugin_file;
}

/**
 * When a user returns from the PayPal in context payment flow, they remain in the iframe.
 *
 * This function checks if they are on an immediate return page, and if they are, closes
 * the frame and redirects them back to the main site.
 *
 * @since 2.2.3
 **/
function ppdg_paypal_return() {
	global $woocommerce, $wp;

	if ( ! isset( $_GET['ppdg'] ) ) {
		return;
	}

	$ppdg_gateway = new WC_Gateway_Paypal_Digital_Goods();

	$is_paying = ( 'paid' == $_GET['ppdg'] ) ? true : false;

	unset( $_GET['ppdg'] );

	if ( isset( $wp->query_vars['order-received'] ) ) { // WC 2.1, order received
		$order_id = $_GET['ppdg_order'] = $wp->query_vars['order-received'];
	} elseif ( isset( $_GET['order_id'] ) ) { // WC 2.1, order cancelled
		$order_id = $_GET['ppdg_order'] = $_GET['order_id'];
	} else { // WC 2.0
		$order_id = $_GET['ppdg_order'] = $_GET['order'];
	}

	$order = new WC_Order( $order_id );

	$paypal_object = $ppdg_gateway->get_paypal_object( $order->id );

	wp_register_style( 'ppdg-iframe', plugins_url( '/css/ppdg-iframe.css', __FILE__ ) );
	wp_register_script( 'ppdg-return', plugins_url( '/js/ppdg-return.js', __FILE__ ), 'jquery' );

	$ppdg_params = array(
		'ajaxUrl'     => ( ! is_ssl() ) ? str_replace( 'https', 'http', admin_url( 'admin-ajax.php' ) ) : admin_url( 'admin-ajax.php' ),
		'queryString' => http_build_query( $_GET ),
		'msgWaiting'  => __( "This won't take a minute", 'ultimatewoo-pro' ),
		'msgComplete' => __( 'Payment Processed', 'ultimatewoo-pro' ),
	);

	wp_localize_script( 'ppdg-return', 'ultimatewoo-pro', $ppdg_params );

// Return an intermediary page with a loading image ?>
<html>
<head>
<title><?php __( 'Processing...', 'ultimatewoo-pro' ); ?></title>
<?php wp_print_styles( 'ppdg-iframe' ); ?>
<?php if ( $is_paying ) : // Process Payment ?>
<?php wp_print_scripts( 'jquery' ); ?>
<?php wp_print_scripts( 'ppdg-return' ); ?>
<?php endif; ?>
<meta name="viewport" content="width=device-width">
</head>
<body>
<div id="left_frame">
<div id="right_frame">
	<p id="message">
	<?php if ( $is_paying ) { // Paid for order ?>
		<?php _e( 'Processing payment', 'ultimatewoo-pro'); ?>
		<?php $location = remove_query_arg( array( 'ppdg', 'token', 'PayerID' ) ); ?>
	<?php } else {  // Cancelling order ?>
		<?php _e( 'Cancelling Order', 'ultimatewoo-pro'); ?>
		<?php $location = html_entity_decode( $order->get_cancel_order_url() );  // We need it as an raw string not a HTML encoded string ?>
	<?php } ?>
	</p>
	<img src="https://www.paypal.com/en_US/i/icon/icon_animated_prog_42wx42h.gif" alt="Processing..." />
	<div id="right_bottom">
		<div id="left_bottom">
		</div>
	</div>
</div>
</div>
<?php if( ! $is_paying ) :  // Close iframe after a short delay ?>
<script type="text/javascript">
setTimeout('if (window!=top) {top.location.replace("<?php echo $location; ?>");}else{location.replace("<?php echo $location; ?>");}', 1500);
</script>
<?php endif; ?>
</body>
</html>
<?php
	exit();
}
add_action( 'get_header', 'ppdg_paypal_return', 11 );

/**
 * Gets the details of a given recurring payments profile with PayPal then calls process_subscription_sign_up.
 *
 * Hooked to @see 'ppdg_check_subscription_status' which is fired every every 12 hours to make sure subscriptions
 * cancelled with PayPal are also cancelled on the site. The hook is also fired every 45 seconds after a
 * subscription is ordered but pending.
 *
 * @since 2.2.3
 */
function ppdg_check_subscription_status( $order_id, $profile_id ) {
	$ppdg_gateway = new WC_Gateway_Paypal_Digital_Goods();

	$paypal_object = $ppdg_gateway->get_paypal_object( $order_id );

	$transaction_details = $paypal_object->get_details( $profile_id );

	$ppdg_gateway->process_subscription_sign_up( $transaction_details );
}
add_action( 'ppdg_check_subscription_status', 'ppdg_check_subscription_status', 10, 2 );

/**
 * Once WooCommerce has validated an IPN request, we want to check if it relates to a Digital Goods transaction.
 *
 * The default WooCommerce handler is hooked to 'valid-paypal-standard-ipn-request' at priority 10, so we
 * hook in before it fires.
 *
 * For WC 2.0+ this needs to be hooked outside of the @see 'WC_Gateway_Paypal_Digital_Goods' class.
 *
 * @since 2.2.3
 */
function ppdg_process_ipn_request( $transaction_details ) {
	$ppdg_gateway = new WC_Gateway_Paypal_Digital_Goods();

	$transaction_details = stripslashes_deep( $transaction_details );

	$ppdg_gateway->process_ipn_request( $transaction_details );
}
add_action( 'valid-paypal-standard-ipn-request', 'ppdg_process_ipn_request', 1 );

/**
 * Handles ajax requests to process express checkout payments
 *
 * @since 2.2.4
 */
function ppdg_ajax_do_express_checkout(){
	$ppdg_gateway = new WC_Gateway_Paypal_Digital_Goods();
	$ppdg_gateway->ajax_do_express_checkout();
}
add_action( 'wp_ajax_ppdg_do_express_checkout', 'ppdg_ajax_do_express_checkout' );
add_action( 'wp_ajax_nopriv_ppdg_do_express_checkout', 'ppdg_ajax_do_express_checkout' );

/**
 * Displays an admin notice advising the store manager to install & activate WooCommerce.
 *
 * Hooked to 'admin_notices' for a more graceful fallback than a fatal error or deactivating the plugin.
 *
 * @since 2.2.3
 */
function ppdg_woocommerce_inactive_notice() { ?>
	<div id="message" class="error">
		<p><?php printf( __( '%sThe PayPal Digital Goods extension for WooCommerce is inactive.%s The %sWooCommerce plugin%s must be active for the PayPal Digital Goods extension to work. Please %sinstall & activate WooCommerce%s', 'ultimatewoo-pro' ), '<strong>', '</strong>', '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url( 'plugins.php' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
	</div>
<?php
}

/**
 * Called when the plugin is activated. Inserts an activation transient so on the next
 * page load a welcome message can be displayed.
 *
 * @since 2.0
 */
function ppdg_activate_digital_goods(){
	set_transient( 'ppdg-activated', 'true', 60 * 60 );
}
register_activation_hook( __FILE__, 'ppdg_activate_digital_goods' );


/**
 * Check is the installed version of WooCommerce is 2.1 or newer.
 *
 * Only for use when we need to check version. If the code in question relys on a specific
 * WC2.1 only function or class, then it's better to check that function or class exists rather
 * than using this more generic check.
 *
 * @since 2.4
 */
function ppdg_is_woocommerce_pre_2_1() {

	if ( ! defined( 'WC_VERSION' ) ) {

		$woocommerce_is_pre_2_1 = true;

	} else {

		$woocommerce_is_pre_2_1 = false;

	}

	return $woocommerce_is_pre_2_1;
}

/**
 * Include Docs & Settings links on the Plugins administration screen
 *
 * @param mixed $links
 * @since 1.4
 */
function ppdg_action_links( $links ) {

	$plugin_links = array(
		'<a href="' . ppdg_settings_tab_url() . '">' . __( 'Settings', 'ultimatewoo-pro' ) . '</a>',
		'<a href="http://docs.woothemes.com/document/paypal-digital-goods-for-express-checkout-gateway/">' . __( 'Docs', 'ultimatewoo-pro' ) . '</a>',
		'<a href="http://support.woothemes.com">' . __( 'Support', 'ultimatewoo-pro' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ppdg_action_links' );


/**
 * Include Docs & Settings links on the Plugins administration screen
 *
 * @param mixed $links
 * @since 1.4
 */
function ppdg_settings_tab_url() {

	if ( ppdg_is_woocommerce_pre_2_1() ) {
		$payment_gateway_settings_url = admin_url( 'admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_Gateway_Paypal_Digital_Goods' );
	} else {
		$payment_gateway_settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_paypal_digital_goods' );
	}

	return $payment_gateway_settings_url;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ppdg_action_links' );


/**
 * Include Docs & Settings links on the Plugins administration screen
 *
 * @param mixed $links
 * @since 3.2
 */
function ppdg_update_paypal_details( $order_id, $transaction_details ) {

	if ( isset( $transaction_details['EMAIL'] ) ) {
		update_post_meta( $order_id, 'Payer PayPal address', $transaction_details['EMAIL'] );
	}

	if ( isset( $transaction_details['FIRSTNAME'] ) ) {
		update_post_meta( $order_id, 'Payer first name', $transaction_details['FIRSTNAME'] );
	}

	if ( isset( $transaction_details['LASTNAME'] ) ) {
		update_post_meta( $order_id, 'Payer last name', $transaction_details['LASTNAME'] );
	}

	if ( isset( $transaction_details['PAYMENTTYPE'] ) ) {
		update_post_meta( $order_id, 'Payment type', $transaction_details['PAYMENTTYPE'] );
	}

	if ( isset( $transaction_details['TRANSACTIONID'] ) ) {
		update_post_meta( $order_id, '_transaction_id', $transaction_details['TRANSACTIONID'] );
	}

	if ( isset( $transaction_details['SUBSCRIBERNAME'] ) ) {
		update_post_meta( $order_id, 'PayPay Subscriber Name', $transaction_details['SUBSCRIBERNAME'] );
	}

	if ( isset( $transaction_details['PROFILEID'] ) ) {
		update_post_meta( $order_id, 'PayPal Profile ID', $transaction_details['PROFILEID'] );
	}
}

//3.2.2