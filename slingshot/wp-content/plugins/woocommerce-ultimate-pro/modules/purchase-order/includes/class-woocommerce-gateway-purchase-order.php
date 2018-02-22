<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Main Woocommerce_Gateway_Purchase_Order Class
 *
 * @class Woocommerce_Gateway_Purchase_Order
 * @version	1.0.0
 * @since 1.0.0
 * @package	Woocommerce_Gateway_Purchase_Order
 * @author Matty
 */
final class Woocommerce_Gateway_Purchase_Order extends WC_Payment_Gateway {
	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $token;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $version;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct () {
		$this->token 			= 'woocommerce-gateway-purchase-order';
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );
		$this->version 			= '1.1.5';

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		$this->id = 'woocommerce_gateway_purchase_order';
		$this->method_title = __( 'Purchase Order', 'ultimatewoo-pro' );
		$this->has_fields = true;

		$this->init_form_fields();
		$this->init_settings();

		$this->title = $this->settings['title'];
		$this->description = $this->settings['description'];
		$this->instructions = $this->settings['instructions'];


		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thank_you' ) );
	} // End __construct()

    /**
	 * Register the gateway's fields.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function init_form_fields () {
	   $this->form_fields = array(
	            'enabled' => array(
	                'title' => __( 'Enable/Disable', 'ultimatewoo-pro' ),
	                'type' => 'checkbox',
	                'label' => __( 'Enable Purchase Orders.', 'ultimatewoo-pro' ),
	                'default' => 'no' ),
	            'title' => array(
	                'title' => __( 'Title:', 'ultimatewoo-pro' ),
	                'type'=> 'text',
	                'description' => __( 'This controls the title which the user sees during checkout.', 'ultimatewoo-pro' ),
	                'default' => __( 'Purchase Order', 'ultimatewoo-pro' ) ),
	            'description' => array(
	                'title' => __( 'Description:', 'ultimatewoo-pro' ),
	                'type' => 'textarea',
	                'description' => __( 'This controls the description which the user sees during checkout.', 'ultimatewoo-pro' ),
	                'default' => __( 'Please add your P.O. Number to the purchase order field.', 'ultimatewoo-pro' ) ),
				 'instructions' => array(
	                'title' => __('Thank You note:', 'ultimatewoo-pro'),
	                'type' => 'textarea',
	                'instructions' => __( 'Instructions that will be added to the thank you page.', 'ultimatewoo-pro' ),
	                'default' => '' )

	        );
	} // End init_form_fields()

	/**
	 * Register the gateway's admin screen.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function admin_options () {
		echo '<h3>'.__( 'Purchase Order Payment Gateway', 'ultimatewoo-pro' ) . '</h3>';
		echo '<table class="form-table">';
		// Generate the HTML For the settings form.
		$this->generate_settings_html();
		echo '</table>';
	} // End admin_options()

	/**
	 * Register the gateway's payment fields.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
    public function payment_fields () {
        if( $this->description ) echo wpautop( wptexturize( $this->description ) );

        // In case of an AJAX refresh of the page, check the form post data to see if we can repopulate an previously entered PO
		$po_number = '';
		if ( isset( $_REQUEST[ 'post_data' ] ) ) {
			parse_str( $_REQUEST[ 'post_data' ], $post_data );
	        if ( isset( $post_data[ 'po_number_field' ] ) ) {
				$po_number = $post_data[ 'po_number_field' ];
	        }
		}
?>
		<fieldset>
			<p class="form-row form-row-first">
				<label for="poorder"><?php _e( 'Purchase Order', 'ultimatewoo-pro' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text" value="<?php echo esc_attr( $po_number ); ?>" id="po_number_field" name="po_number_field" />
			</p>
		</fieldset>
<?php
    } // End payment_fields()

    /**
	 * Process the payment.
	 * @access public
	 * @since  1.0.0
	 * @return array An array containing the result text and a redirect URL.
	 */
	public function process_payment( $order_id ) {
		$order = new WC_Order( $order_id );

		$poorder = $this->get_post( 'po_number_field' );
		if ( isset( $poorder ) ) update_post_meta( $order_id, '_po_number', esc_attr( $poorder ) );

		$order->update_status( 'on-hold', __( 'Waiting to be processed', 'ultimatewoo-pro' ) );

		// Reduce stock levels
		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			$order->reduce_order_stock();
		} else {
			wc_reduce_stock_levels( $order->get_id() );
		}

		// Remove cart
		WC()->cart->empty_cart();

		// Return thankyou redirect
		return array(
		'result' 	=> 'success',
		'redirect'	=> $this->get_return_url( $order )
		);
	} // End process_payment()

	/**
	 * Display thank you instructions.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function thank_you () {
        echo $this->instructions != '' ? wpautop( $this->instructions ) : '';
    } // End thankyou()

	/**
	 * Retrieve a posted value, if it exists.
	 * @access public
	 * @since  1.0.0
	 * @return string/null
	 */
	private function get_post ( $name ) {
		if( isset( $_POST[$name] ) ) {
			return $_POST[$name];
		} else {
			return NULL;
		}
	} // End get_post()

	public function validate_fields () {
		$poorder = $this->get_post( 'po_number_field' );
		if( ! $poorder ) {
			if ( function_exists ( 'wc_add_notice' ) ) {
				// Replace deprecated $woocommerce_add_error() function.
				wc_add_notice ( __ ( 'Please enter your PO Number.', 'ultimatewoo-pro' ), 'error' );
			} else {
				WC()->add_error( __( 'Please enter your PO Number.', 'ultimatewoo-pro' ) );
			}
			return false;
		} else {
			return true;
		}
	} // End validate_fields()

	/**
	 * Load the localisation file.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-gateway-purchase-order', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	} // End load_plugin_textdomain()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __wakeup()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install()

	/**
	 * Log the plugin version number.
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		// Log the version number.
		update_option( $this->token . '-version', $this->version );
	} // End _log_version_number()
} // End Class
?>
