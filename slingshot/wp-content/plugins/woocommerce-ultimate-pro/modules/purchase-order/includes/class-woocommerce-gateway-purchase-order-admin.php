<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Returns the main instance of Woocommerce_Gateway_Purchase_Order_Admin to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Woocommerce_Gateway_Purchase_Order_Admin
 */
function Woocommerce_Gateway_Purchase_Order_Admin() {
	return Woocommerce_Gateway_Purchase_Order_Admin::instance();
} // End Woocommerce_Gateway_Purchase_Order_Admin()

/**
 * Main Woocommerce_Gateway_Purchase_Order_Admin Class
 *
 * @class Woocommerce_Gateway_Purchase_Order_Admin
 * @version	1.0.0
 * @since 1.0.0
 * @package	Woocommerce_Gateway_Purchase_Order_Admin
 * @author Matty
 */
final class Woocommerce_Gateway_Purchase_Order_Admin {
	/**
	 * Woocommerce_Gateway_Purchase_Order_Admin The single instance of Woocommerce_Gateway_Purchase_Order_Admin.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct () {
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'display_purchase_order_number' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'display_purchase_order_number' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'display_purchase_order_number' ) );

	} // End __construct()

	/**
	* Purchase order HTML output.
	* @access public
	* @since 1.0.0
	* @param $order
	* @return void
	*/
	public function display_purchase_order_number ( $order ) {
		$payment_method = version_compare( WC_VERSION, '3.0', '<' ) ? $order->payment_method : $order->get_payment_method();
		$order_id = version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id();

		if ( 'woocommerce_gateway_purchase_order' === $payment_method ) {
			$po_number = get_post_meta( $order_id, '_po_number', true );
			if ( '' != $po_number ) {
				echo '<p class="form-field form-field-wide"><label>' . __( 'Purchase Order Number:', 'ultimatewoo-pro' ) . '</label><h2>' . $po_number . '</h2></p>' . "\n";
			}
		}
	} // End display_purchase_order_number()

	/**
	 * Main Woocommerce_Gateway_Purchase_Order_Admin Instance
	 *
	 * Ensures only one instance of Woocommerce_Gateway_Purchase_Order_Admin is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Woocommerce_Gateway_Purchase_Order_Admin()
	 * @return Main Woocommerce_Gateway_Purchase_Order_Admin instance
	 */
	public static function instance () {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	} // End instance()
} // End Class
?>
