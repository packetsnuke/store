<?php
/*  Copyright 2011-2017  Krokedil Produktionsbyrå AB  (email : info@krokedil.se)

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
 * Check if update is from 1.4 (or below) to 1.5+
 *
 * Payment method ID changed from 2Checkout to twocheckout. Otherwise the plugin admin settings wasn't visible.
 * In this function we rename the settings from 
 */
function twocheckout_update() {
	if ( false == get_option( 'woocommerce_twocheckout_settings' ) ) {
		if ( get_option( 'woocommerce_2Checkout_settings' ) ) {
			add_option( 'woocommerce_twocheckout_settings', get_option( 'woocommerce_2Checkout_settings' ) );
		}
	}
}
add_action( 'plugins_loaded', 'twocheckout_update' );


// Init 2Checkout Gateway after WooCommerce has loaded
add_action('plugins_loaded', 'init_twocheckout_gateway', 0);

function init_twocheckout_gateway() {

	// If the WooCommerce payment gateway class is not available, do nothing
	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;


	/**
	 * Localisation
	 */
	load_plugin_textdomain('2checkout', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
	
	
	/**
	 * Include the WooCommerce Compatibility Utility class
	 * The purpose of this class is to provide a single point of compatibility functions for dealing with supporting multiple versions of WooCommerce (currently 2.0.x and 2.1)
	 */
	
	require_once 'classes/class-wc-2co-compatibility.php';


	class WC_Gateway_Twocheckout extends WC_Payment_Gateway {

		public function __construct() {
			global $woocommerce;
	        $this->id			= 'twocheckout';
	        $this->method_title = __('2Checkout', 'ultimatewoo-pro');
	        $this->icon 		= ULTIMATEWOO_MODULES_URL . "2checkout/images/2CO.png";
	        $this->has_fields 	= false;
	        $this->purchaseurl	= 'https://www.2checkout.com/checkout/purchase';
	        $this->sandboxurl	= 'https://sandbox.2checkout.com/checkout/purchase';
	        $this->log 			= WC_2co_Compatibility::new_wc_logger();

	        // Load the form fields.
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();

			// Define user set variables
	      	$this->enabled					= ( isset( $this->settings['enabled'] ) ) ? $this->settings['enabled'] : '';
			$this->title 					= ( isset( $this->settings['title'] ) ) ? $this->settings['title'] : '';
			$this->description  			= ( isset( $this->settings['description'] ) ) ? $this->settings['description'] : '';
			$this->sid						= ( isset( $this->settings['sid'] ) ) ? $this->settings['sid'] : '';
			$this->secret_word				= ( isset( $this->settings['secret_word'] ) ) ? $this->settings['secret_word'] : '';
			$this->checkout_type			= ( isset( $this->settings['checkout_type'] ) ) ? $this->settings['checkout_type'] : '';
			$this->lang						= ( isset( $this->settings['lang'] ) ) ? $this->settings['lang'] : '';
			$this->debugmode				= ( isset( $this->settings['debugmode'] ) ) ? $this->settings['debugmode'] : '';
			
			$this->send_order_total			= ( isset( $this->settings['send_order_total'] ) ) ? $this->settings['send_order_total'] : '';
			$this->send_shipping_address	= ( isset( $this->settings['send_shipping_address'] ) ) ? $this->settings['send_shipping_address'] : '';
			$this->testmode					= ( isset( $this->settings['testmode'] ) ) ? $this->settings['testmode'] : '';
			$this->sandbox					= ( isset( $this->settings['sandbox'] ) ) ? $this->settings['sandbox'] : '';

			// Actions
			add_action( 'woocommerce_api_wc_gateway_twocheckout', array($this, 'check_twocheckout_response') );
			add_action( 'valid-twocheckout-request', array($this, 'successful_request') );
			add_action( 'woocommerce_receipt_twocheckout', array($this, 'receipt_page') );
			add_action( 'wp_enqueue_scripts', array($this, 'load_scripts') );
			
			
 
			/* 2.0.0 */
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			

	    }

		/**
    	 * Initialise Gateway Settings Form Fields
    	 */
    	function init_form_fields() {

    		$this->form_fields = array(
				'enabled' => array(
								'title' => __( 'Enable/Disable', 'ultimatewoo-pro' ),
								'type' => 'checkbox',
								'label' => __( 'Enable 2Checkout', 'ultimatewoo-pro' ),
								'default' => 'yes'
							),
				'title' => array(
								'title' => __( 'Title', 'ultimatewoo-pro' ),
								'type' => 'text',
								'description' => __( 'This controls the title which the user sees during checkout.', 'ultimatewoo-pro' ),
								'default' => __( '2Checkout', 'ultimatewoo-pro' )
							),
				'description' => array(
								'title' => __( 'Description', 'ultimatewoo-pro' ),
								'type' => 'textarea',
								'description' => __( 'This controls the title which the user sees during checkout.', 'ultimatewoo-pro' ),
								'default' => __( 'Pay via Credit Card with 2Checkout secure card processing.', 'ultimatewoo-pro' ),
							),
				'sid' => array(
								'title' => __( '2Checkout account number', 'ultimatewoo-pro' ),
								'type' => 'text',
								'description' => __( 'Please enter your 2Checkout account number; this is needed in order to take payment!', 'ultimatewoo-pro' ),
								'default' => ''
							),
				'secret_word' => array(
								'title' => __( '2Checkout Secret Word', 'ultimatewoo-pro' ),
								'type' => 'text',
								'description' => __( 'Please enter your 2Checkout secret word; this is needed in order to take payment!', 'ultimatewoo-pro' ),
								'default' => ''
							),
				'checkout_type' => array(
								'title' => __( 'Checkout Type', 'ultimatewoo-pro' ),
								'type' => 'select',
								'options' => array('dynamic'=>'Dynamic Checkout', 'dynamic_no_js'=>'Dynamic Checkout - auto-redirect from pay page disabled', 'direct'=>'Direct Checkout'),
								'description' => __( '<br/>Dynamic Checkout is the standard checkout option. Direct Checkout is a iframe checkout option which displays a secure payment form as an overlay on your checkout page.', 'ultimatewoo-pro' ),
								'default' => 'dynamic'
							),
				'lang' => array(
								'title' => __( 'Checkout Language', 'ultimatewoo-pro' ),
								'type' => 'select',
								'options' => array('en'=>'English', 'es_ib'=>'Spanish (European) — Español', 'es_la'=>'Spanish (Latin) — Español', 'fr'=>'French — Français', 'ja'=>'Japanese — 日本語', 'de'=>'German — Deutsch', 'it'=>'Italian — Italiano', 'nl'=>'Dutch — Nederlands', 'pt'=>'Portuguese — Português', 'el'=>'Greek — Ελληνική', 'sv'=>'Swedish — Svenska', 'zh'=>'Chinese (Traditional) — 語言名稱', 'sl'=>'Slovenian — Slovene', 'da'=>'Danish — Dansk', 'no'=>'Norwegian — Norsk'),
								'description' => __( '<br/>Please choose the language used in the 2Checkout cart', 'ultimatewoo-pro' ),
								'default' => 'en'
							),
				'send_order_total' => array(
								'title' => __( 'Send Order Total as one item', 'ultimatewoo-pro' ),
								'type' => 'checkbox',
								'label' => __( 'Send Order Total as one single item. This can be useful when selling physical (tangible) products but still want to use the 2Checkout Single Page Checkout.', 'ultimatewoo-pro' ),
								'default' => ''
							),
				'send_shipping_address' => array(
								'title' => __( 'Send Shipping Address', 'ultimatewoo-pro' ),
								'type' => 'checkbox',
								'label' => __( 'Send Shipping Address to 2Checkout even if free shipping is available to the customer. This can be useful when selling physical products with free shipping.', 'ultimatewoo-pro' ),
								'default' => ''
							),
				'testmode' => array(
								'title' => __( 'Demo Mode', 'ultimatewoo-pro' ),
								'type' => 'checkbox',
								'label' => __( 'Enable 2Checkout Demo Mode. This is just to test your 2Checkout account information. No payment will be made.', 'ultimatewoo-pro' ),
								'default' => 'no'
							),
				'sandbox' => array(
								'title' => __( '2Checkout Sandbox', 'ultimatewoo-pro' ),
								'type' => 'checkbox',
								'label' => __('Enable 2Checkout Sandbox.', 'ultimatewoo-pro'),
								'description' => sprintf(__('With the sandbox you can test 2Checkouts features against an exact clone of the production environment. Register for a sandbox account <a href="%s" target="_blank">here</a>.', 'ultimatewoo-pro'), 'https://sandbox.2checkout.com/sandbox/signup' ),
								'default' => 'no'
							),
				'debugmode' => array(
								'title' => __( 'Debug', 'ultimatewoo-pro' ),
								'type' => 'checkbox',
								'label' =>  __( 'Enable logging (<code>woocommerce/logs/2checkout.txt</code>)', 'ultimatewoo-pro' ),
								'default' => 'no'
							)
				);

		} // End init_form_fields()


		/**
	 	* Admin Panel Options
	 	* - Options for bits like 'title' and availability on a country-by-country basis
	 	*
	 	* @since 1.0.0
	 	*/
		public function admin_options() {

	    	?>
	    	<h3><?php _e('2Checkout', 'ultimatewoo-pro'); ?></h3>

	    	<p><?php printf(__('2Checkout works by sending the user to <a href="http://www.2checkout.com">2Checkout</a> to enter their payment information. Instructions on how to set up the 2CO account settings can be found in the <a target="_blank" href="%s">documentation</a>.', 'ultimatewoo-pro'), 'http://wcdocs.woothemes.com/user-guide/extensions/2checkout/' ); ?></p>



	    	<div class="updated inline">
	    		<p><?php _e('Please note that the WooCommerce currency must match the currency that has been specified in your 2Checkout account, otherwise the customer will be charged with the wrong amount.', 'ultimatewoo-pro'); ?></p>

	    		<p><?php _e('Also note, if you offer free shipping for tangible (i.e. physical) products you need to add a New Shipping Method in your 2CO account (see the shipping section in your 2CO account). Set the pricing to Free and then choose which countries this method should apply to.', 'ultimatewoo-pro'); ?></p>
	    	</div>

    		<table class="form-table">
    		<?php
    			// Generate the HTML For the settings form.
    			$this->generate_settings_html();
    		?>
			</table><!--/.form-table-->
    		<?php
    	} // End admin_options()



	    /**
		 * There are no payment fields for 2Checkout, but we want to show the description if set.
		 **/
		function payment_fields() {
			if ($this->description) echo wpautop(wptexturize($this->description));
		}


		/**
	 	* Generate the 2Checkout button link
	 	**/
		public function generate_twocheckout_form( $order_id ) {
			global $woocommerce;

			$order = new WC_Order( $order_id );

			// Check if this is a test purchase
			if ( $this->testmode == 'yes' ) {
				$demo = 'Y';
			} else {
				$demo = 'N';
			}
			
			// Check if this is a Sandbox purchase
			if ( $this->sandbox == 'yes' ) {
				$post_url = $this->sandboxurl;
			} else {
				$post_url = $this->purchaseurl;
			}

			$shipping_name = explode(' ', $order->shipping_method);

			$twocheckout_args = array_merge(
				array(
					'sid' 					=> $this->sid,
					'mode'					=> '2CO',
//					'skip_landing' 			=> 1,
					'fixed' 				=> 'Y',
					'demo' 					=> $demo,
					'lang'					=> $this->lang,
					'x_receipt_link_url' 	=> add_query_arg ('wc-api', 'WC_Gateway_Twocheckout', $this->get_return_url( $order )),
					'return_url'			=> $order->get_cancel_order_url(),
					'id_type'				=> 1,

					// Order key
					//'cart_order_id'			=> $order_id,
					'merchant_order_id'		=> $order_id,

					// Address info
					'card_holder_name'		=> $order->billing_first_name . ' ' . $order->billing_last_name,
					'first_name'			=> $order->billing_first_name,
					'last_name'				=> $order->billing_last_name,
					'street_address'		=> $order->billing_address_1,
					'street_address2'		=> $order->billing_address_2,
					'city'					=> $order->billing_city,
					'state'					=> $order->billing_state,
					'zip'					=> $order->billing_postcode,
					'country'				=> $order->billing_country,
					'email'					=> $order->billing_email,
					'phone'					=> $order->billing_phone,

					// Payment Info
					//'total'					=> $order->order_total
				)
			);
			
			// Shipping info
			if (WC_2co_Compatibility::get_total_shipping($order)>0 || $this->send_shipping_address == 'yes') :
				$twocheckout_args['ship_name'] = $order->shipping_first_name . ' ' . $order->shipping_last_name;
				$twocheckout_args['ship_street_address'] = $order->shipping_address_1;
				$twocheckout_args['ship_street_address2'] = $order->shipping_address_2;
				$twocheckout_args['ship_city'] = $order->shipping_city;
				$twocheckout_args['ship_state'] = $order->shipping_state;
				$twocheckout_args['ship_zip'] = $order->shipping_postcode;
				$twocheckout_args['ship_country'] = $order->shipping_country;
			
			endif;

			// Pass cart as one item
			if ($this->send_order_total == 'yes') {

				// Order total
				$item_loop = 0;

				$twocheckout_args['li_'.$item_loop.'_type'] = 'product';
				$twocheckout_args['li_'.$item_loop.'_name'] = get_bloginfo('name') . ' order #' . $order_id;
				$twocheckout_args['li_'.$item_loop.'_product_id'] = '';
				$twocheckout_args['li_'.$item_loop.'_quantity'] = '1';


				$twocheckout_args['li_'.$item_loop.'_price'] = number_format($order->order_total, 2, '.', '');


				// Set order as intangible
				$twocheckout_args['li_'.$item_loop.'_tangible'] = 'N';

				$item_loop++;
				

			}

			// Pass cart items individually
			else {

				// Cart Contents
				$item_loop = 0;
				if (sizeof($order->get_items())>0) : foreach ($order->get_items() as $item) :
					
					$tmp_sku = '';
					
					if ( function_exists( 'get_product' ) ) {
					
						// Version 2.0
						$_product = $order->get_product_from_item($item);
					
						// Get SKU or product id
						if ( $_product->get_sku() ) {
							$tmp_sku = $_product->get_sku();
						} else {
							$tmp_sku = $_product->id;
						}
						
					} else {
					
						// Version 1.6.6
						$_product = new WC_Product( $item['id'] );
					
						// Get SKU or product id
						if ( $_product->get_sku() ) {
							$tmp_sku = $_product->get_sku();
						} else {
							$tmp_sku = $item['id'];
						}
						
					}
				
					if ($_product->exists() && $item['qty']) :

						// Check if product is downloadable or virtual. If so then set the product as intangible (N).
						// Otherwise set the product as tangible (Y).
						if ( $_product->is_virtual() || $_product->is_downloadable() ) :
							$tangible = "N";
						else :
							$tangible = "Y";
						endif;

						$twocheckout_args['li_'.$item_loop.'_type'] = 'product';
						$twocheckout_args['li_'.$item_loop.'_name'] = $item['name'];
						$twocheckout_args['li_'.$item_loop.'_product_id'] = $tmp_sku;
						$twocheckout_args['li_'.$item_loop.'_quantity'] = $item['qty'];

						if ($order->prices_include_tax) :
							$twocheckout_args['li_'.$item_loop.'_price'] = number_format($order->get_item_total( $item, true ), 2, '.', '');
						else :
							$twocheckout_args['li_'.$item_loop.'_price'] = number_format($order->get_item_total( $item, false ), 2, '.', '');
						endif;


						$twocheckout_args['li_'.$item_loop.'_tangible'] = $tangible;

						$item_loop++;

					endif;
				endforeach; endif;


				// Shipping Cost
				if (WC_2co_Compatibility::get_total_shipping($order)>0) :

					$twocheckout_args['li_'.$item_loop.'_type'] = 'shipping';
					$twocheckout_args['li_'.$item_loop.'_name'] = __('Shipping cost', 'ultimatewoo-pro');
					$twocheckout_args['li_'.$item_loop.'_quantity'] = 1;

					if ($order->prices_include_tax) :
						$twocheckout_args['li_'.$item_loop.'_price'] = number_format((WC_2co_Compatibility::get_total_shipping($order) + $order->order_shipping_tax), 2, '.', '');
					else :
						$twocheckout_args['li_'.$item_loop.'_price'] = number_format(WC_2co_Compatibility::get_total_shipping($order), 2, '.', '');
					endif;

					$twocheckout_args['li_'.$item_loop.'_tangible'] = 'Y';
					$item_loop++;

				endif;

				// Tax
				if (!$order->prices_include_tax && $order->get_total_tax()>0) :

					$twocheckout_args['li_'.$item_loop.'_type'] = 'tax';
					$twocheckout_args['li_'.$item_loop.'_name'] = __('Tax', 'ultimatewoo-pro');
					$twocheckout_args['li_'.$item_loop.'_quantity'] = 1;
					$twocheckout_args['li_'.$item_loop.'_price'] = $order->get_total_tax();
					$twocheckout_args['li_'.$item_loop.'_tangible'] = 'N';
					$item_loop++;
				endif;

				// Discount
				if ($order->get_order_discount()>0) :

					$twocheckout_args['li_'.$item_loop.'_type'] = 'coupon';
					$twocheckout_args['li_'.$item_loop.'_name'] = __('Discount', 'ultimatewoo-pro');
					$twocheckout_args['li_'.$item_loop.'_quantity'] = 1;
					$twocheckout_args['li_'.$item_loop.'_price'] = $order->get_order_discount();
					$twocheckout_args['li_'.$item_loop.'_tangible'] = 'N';

				endif;
			} // End if send_order_total


			// Debug
			if ($this->debugmode=='yes') :
				$message = '';
				foreach ( $twocheckout_args as $key => $value ) {
					$message .= $key . '=' . $value . "\r\n";
				}

				$message = 'Sent values to 2Checkout(' . $post_url . '): ' . "\r\n" . 'Order ID: ' . $order_id . "\r\n" . "\r\n" . $message;
				$this->log->add( '2checkout', $message );

			endif;
			
			// Prepare the form
			$twocheckout_args_array = array();
			foreach ($twocheckout_args as $key => $value) {
				$twocheckout_args_array[] = '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}
			
			// Dynamic checkout js trigger
			if($this->checkout_type == 'dynamic' ) {
				WC_2co_Compatibility::wc_enqueue_js( '
					
					$.blockUI({
						message: "' . esc_js( __( 'Thank you for your order. We are now redirecting you to 2Checkout to make payment.', 'ultimatewoo-pro' ) ) . '",
						baseZ: 99999,
						overlayCSS:
						{
							background: "#fff",
							opacity: 0.6
						},
						css: {
							padding:        "20px",
							zindex:         "9999999",
							textAlign:      "center",
							color:          "#555",
							border:         "3px solid #aaa",
							backgroundColor:"#fff",
							cursor:         "wait",
							lineHeight:		"24px",
						}
					});				

					jQuery("#submit_twocheckout_payment_form").click();
				');
			} // End if checkout_type dynamic
			
			// Direct checkout js trigger
			if($this->checkout_type == 'direct' ) {
				WC_2co_Compatibility::wc_enqueue_js( '
					jQuery(function(){
						jQuery("#submit_twocheckout_payment_form").click();
					});
				');
			} // End if checkout_type direct
			
			// The form
			return '<form action="'.$post_url.'" method="post" id="twocheckout_payment_form">
				' . implode('', $twocheckout_args_array) . '
				<input type="submit" class="button alt" id="submit_twocheckout_payment_form" value="'.__('Pay via 2Checkout', 'ultimatewoo-pro').'" /> <a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Cancel order &amp; restore cart', 'ultimatewoo-pro').'</a></form>';

		} // End function

		/**
		 * Process the payment and return the result
		 **/
		function process_payment( $order_id ) {

			$order = new WC_Order( $order_id );
			
			// Prepare redirect url
			if( WC_2co_Compatibility::is_wc_version_gte_2_1() ) {
	    		$redirect_url = $order->get_checkout_payment_url( true );
			} else {
	    		$redirect_url = add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(get_option('woocommerce_pay_page_id'))));
			}
			
			return array(
				'result' 	=> 'success',
				'redirect'	=> $redirect_url
			);

		}

		/**
		 * receipt_page
		 **/
		function receipt_page( $order ) {

			echo '<p>'.__('Thank you for your order, please click the button below to pay with 2Checkout.', 'ultimatewoo-pro').'</p>';

			echo $this->generate_twocheckout_form( $order );

		}


		/**
		 * Check for 2Checkout Response
		 **/
		function check_twocheckout_response() {
			$this->log->add( '2checkout', 'Respons header: ' . var_export($_REQUEST, true) );
			// Check for return to thank you page or 2Checkout INS-response
			if ( isset($_REQUEST["credit_card_processed"]) || isset($_REQUEST["message_type"]) ) :

				// Debug
				if ( $this->debugmode == 'yes' ) :

					$this->log->add( '2checkout', 'Receiving response from 2Checkout...' );

				endif;

				// Get values for calculating the MD5-hash
				$secret_word = $this->secret_word;
				$sid = $this->sid;

				// Call made from "Click here to finalize the order"-button
				if ( isset($_REQUEST["merchant_order_id"]) ) :
					
					// Debug
					if ( $this->debugmode == 'yes' ) :
						$this->log->add( '2checkout', 'Customer return to thank you page' );
					endif;

					// GET THE RETURN FROM 2Checkout
					$RefNr = $_REQUEST["merchant_order_id"];
					$order_number = $_REQUEST["order_number"];
					$total = $_REQUEST["total"];
					$twocheckoutMD5 = $_REQUEST["key"];

					// Calculate our specific MD5Hash so we can validate it with the one sent from 2Checkout
					// If this is a test purchase we need to change the order number to 1
					if ( $this->testmode == 'yes' ):
						$string_to_hash = $secret_word . $sid . "1" . $_REQUEST["total"];
					else :
						$string_to_hash = $secret_word . $sid . $_REQUEST["order_number"] . $_REQUEST["total"];
					endif;

					$check_key = strtoupper(md5($string_to_hash));

					// Put the variables returned from twocheckout in an array so we can pass them on
					// to the successful_request function.
					$twocheckout_return_values = array(
						"check_key" 		=> 	$check_key,
						"RefNr" 			=> $RefNr,
						"sale_id" 			=> $order_number,
						"total" 			=> $total,
						"twocheckoutMD5" 	=> $twocheckoutMD5
					);


				// 2Checkout INS-response
				elseif ( isset($_REQUEST["vendor_order_id"]) ) :
					
					// Debug
					if ( $this->debugmode == 'yes' ) :
						$this->log->add( '2checkout', 'Callback via INS' );
					endif;

					// GET THE RETURN FROM 2Checkout
					$RefNr = $_REQUEST["vendor_order_id"];
					$sale_id = $_REQUEST["sale_id"];
					$invoice_id = $_REQUEST["invoice_id"];
					$twocheckoutMD5 = $_REQUEST["md5_hash"];
					$vendor_id = $_REQUEST["vendor_id"];

					// Calculate our specific MD5Hash so we can validate it with the one sent from 2Checkout
					$string_to_hash = $sale_id . $sid . $invoice_id . $secret_word;
					$check_key = strtoupper(md5($string_to_hash));


					// Put the variables returned from twocheckout in an array so we can pass them on
					// to the successful_request function.
					$twocheckout_return_values = array(
						"check_key" 		=> $check_key,
						"RefNr" 			=> $RefNr,
						"invoice_id"		=> $invoice_id,
						"sale_id" 			=> $sale_id,
						"vendor_id" 		=> $vendor_id,
						"twocheckoutMD5" 	=> $twocheckoutMD5
					);

				endif;

				// Debug
				if ( $this->debugmode == 'yes' ) :

					$order_id = $RefNr;
					$message ='';

					foreach ( $twocheckout_return_values as $key => $value ) {
						$message .= $key . '=' . $value . "\r\n";
					}

					$message = 'Returned values from 2Checkout: ' . "\r\n" . 'Order ID: ' . $order_id . "\r\n" . "\r\n" . $message;
					$this->log->add( '2checkout', $message );

				endif;

				// COMPARE MD5-HASH. IF IT'S OK THEN THE TRANSACTION IS VALID.
				if ( isset($twocheckout_return_values['check_key']) && $check_key == $twocheckoutMD5 ) {
					
					// MD5 comparison OK
					// Debug
					if ( $this->debugmode == 'yes' ) :
						$this->log->add( '2checkout', 'MD5 comparison OK.' );
					endif;
					do_action("valid-twocheckout-request", $twocheckout_return_values);
				
				} else {
					
					// MD5 comparison failed
					// Debug
					if ( $this->debugmode == 'yes' ) :
						$this->log->add( '2checkout', 'MD5 comparison failed.' );
					endif;
					$order = new WC_Order( (int) $RefNr );
					$order->update_status( 'failed', sprintf( __( 'MD5 comparison failed. Sale ID %s.', 'ultimatewoo-pro' ), strtolower( $sale_id ) ) );
					$redirect_url = WC_2co_Compatibility::get_checkout_order_received_url($order);
					wp_redirect( $redirect_url ); 
					exit;
				}


			endif;
		}


		/**
	 	* Successful Payment!
	 	**/
		function successful_request( $twocheckout_return_values ) {
			
			// Debug
			if ( $this->debugmode == 'yes' ) :
				$this->log->add( '2checkout', 'Function successful_request() fired.' );
			endif;
					
			global $woocommerce;

		    if ( !empty($twocheckout_return_values['RefNr']) ) {

				$order_id 	  	= $twocheckout_return_values['RefNr'];
				$order 			= new WC_Order( (int) $order_id );
				//$order_key		= $order->order_key;

		    	if ($order->status !== 'completed') {

		    		// We probably get two response calls fron 2Checkout. The following is to prevent WooCommerce
		    		// from change the orderstock and post messages about completed payment more than one time.
		    		if ($order->status == 'processing') {
						// This is the second call - do nothing
					} else {
			    		$order->add_order_note( __('2Checkout payment completed. 2CO order number: ', 'ultimatewoo-pro') . $twocheckout_return_values['sale_id'] );
			        	$order->payment_complete();

			        	// Empty the Cart
						$woocommerce->cart->empty_cart();
					}
				}
				
				// Debug
				if ( $this->debugmode == 'yes' ) :
					$this->log->add( '2checkout', 'Redirect to thank you page...' );
				endif;
				
				// Prepare redirect url
				if( WC_2co_Compatibility::is_wc_version_gte_2_1() ) {
	    			$redirect_url = WC_2co_Compatibility::get_checkout_order_received_url($order);
				} else {
	    			$redirect_url = add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink(get_option('woocommerce_thanks_page_id'))));
				}
    		
				// Return to Thank you page if this is a buyer-return-to-shop callback
	            wp_redirect( $redirect_url ); 
	            exit;
		    }

		}
		
		
		/**
	 	 * Register and Enqueue 2Checkout scripts
	 	 */
		function load_scripts() {
			
			// Direct Checkout
			if ( is_checkout() && $this->checkout_type == 'direct' ) {
				wp_register_script( '2checkout-direct-js', 'https://www.2checkout.com/static/checkout/javascript/direct.min.js', '', '', true );
				wp_enqueue_script( '2checkout-direct-js' );
			}	

		} // End function


	} // Close class WC_Gateway_Twocheckout



} // Close init_twocheckout_gateway

/**
 * Add the gateway to WooCommerce
 **/
function add_twocheckout_gateway( $methods ) {
	$methods[] = 'WC_Gateway_Twocheckout'; 
	return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_twocheckout_gateway' );

// WC 2.0 Update notice
class WC_Gateway_Twocheckout_Update_Notice {
	
	public function __construct() {
		
		// Add admin notice about the callback change
		//add_action('admin_notices', array(&$this, 'krokedil_admin_notice'));
		//add_action('admin_init', array(&$this, 'krokedil_nag_ignore'));
	}
	
	/* Display a notice about the changes to the Invoice fee handling */
	function krokedil_admin_notice() {
	
		global $current_user ;
		$user_id = $current_user->ID;
		
		/* Check that the user hasn't already clicked to ignore the message */
		if ( ! get_user_meta($user_id, 'twocheckout_callback_change_notice') && current_user_can( 'manage_options' ) ) {
			echo '<div class="updated fade"><p class="alignleft">';
			printf(__('The 2Checkout callback URL has changed. You will need to change this in your 2Checkout merchant account settings. Please visit <a target="_blank" href="%1$s"> the payment gateway documentation</a> for more info.', 'ultimatewoo-pro'), 'http://wcdocs.woothemes.com/user-guide/extensions/2checkout/#section-10');
			echo '</p><p class="alignright">';
			printf(__('<a class="submitdelete" href="%1$s"> Hide this message</a>', 'ultimatewoo-pro'), '?twocheckout_nag_ignore=0');
			echo '</p><br class="clear">';
			echo '</div>';
		}
		
	}

	/* Hide the notice about the changes to the Invoice fee handling if ignore link has been clicked */
	function krokedil_nag_ignore() {
		global $current_user;
		$user_id = $current_user->ID;
		/* If user clicks to ignore the notice, add that to their user meta */
		if ( isset($_GET['twocheckout_nag_ignore']) && '0' == $_GET['twocheckout_nag_ignore'] ) {
			add_user_meta($user_id, 'twocheckout_callback_change_notice', 'true', true);
		}
	}
}
$wc_twocheckout_update_notice = new WC_Gateway_Twocheckout_Update_Notice;
//1.5.1
?>