<?php
/**
 * WooCommerce Intuit Payments
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Intuit Payments to newer
 * versions in the future. If you wish to customize WooCommerce Intuit Payments for your
 * needs please refer to http://docs.woothemes.com/document/intuit-qbms/
 *
 * @package   WC-Intuit-Payments/Gateway
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * The base gateway class.
 *
 * @since 2.0.0
 */
abstract class WC_Gateway_Inuit_Payments extends SV_WC_Payment_Gateway_Direct {


	/** the sandbox environment identifier */
	const ENVIRONMENT_SANDBOX = 'sandbox';

	/** the production API endpoint */
	const API_ENDPOINT = 'https://api.intuit.com';

	/** the sandbox API endpoint */
	const API_ENDPOINT_SANDBOX = 'https://sandbox.api.intuit.com';


	/** @var string the merchant's app's consumer key */
	protected $consumer_key;

	/** @var string the merchant's app's consumer secret */
	protected $consumer_secret;

	/** @var string the stored oAuth token */
	protected $oauth_token;

	/** @var string the stored oAuth token secret */
	protected $oauth_token_secret;

	/** @var string the merchant's sandbox app's consumer key */
	protected $sandbox_consumer_key;

	/** @var string the merchant's sandbox app's consumer secret */
	protected $sandbox_consumer_secret;

	/** @var string the stored sandbox oAuth token */
	protected $sandbox_oauth_token;

	/** @var string the stored sandbox oAuth token secret */
	protected $sandbox_oauth_token_secret;

	/** @var string the credential encryption key */
	private $encryption_key;

	/** @var \WC_Intuit_Payments_API|null the API instance */
	protected $api;


	/**
	 * Constructs the gateway.
	 *
	 * @since 2.0.0
	 * @param string $id the gateway ID
	 * @param array $args the gateway args
	 */
	public function __construct( $id, $args ) {

		// set the default args shared across gateways
		$args = wp_parse_args( $args, array(
			'method_description' => __( 'Intuit Payments Gateway provides a seamless and secure checkout process for your customers', 'ultimatewoo-pro' ),
			'supports'           => array(),
			'environments'       => array(
				self::ENVIRONMENT_PRODUCTION => __( 'Production', 'ultimatewoo-pro' ),
				self::ENVIRONMENT_SANDBOX    => __( 'Sandbox', 'ultimatewoo-pro' ),
			),
			'shared_settings' => array(
				'consumer_key',
				'consumer_secret',
				'sandbox_consumer_key',
				'sandbox_consumer_secret',
				'connect_button',
			),
		) );

		// add any gateway-specific supports
		$args['supports'] = array_unique( array_merge( $args['supports'], array(
			self::FEATURE_PRODUCTS,
			self::FEATURE_PAYMENT_FORM,
			self::FEATURE_REFUNDS,
			self::FEATURE_VOIDS,
			self::FEATURE_CUSTOMER_ID,
		) ) );

		parent::__construct( $id, wc_intuit_payments(), $args );

		// add a test case input to the payment form
		if ( $this->is_test_environment() ) {
			add_filter( 'wc_' . $this->get_id() . '_payment_form_description', array( $this, 'render_test_case_field' ) );
		}

		// add hidden inputs that client-side JS populates with token/last 4 of account number
		add_action( 'wc_' . $this->get_id() . '_payment_form', array( $this, 'render_hidden_inputs' ) );

		// remove card number/csc input names so they're not POSTed
		add_filter( 'wc_' . $this->get_id() . '_payment_form_default_payment_form_fields', array( $this, 'remove_payment_form_field_input_names' ) );

		// enqueue the admin scripts & styles
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );

		// handle OAuth 2 events
		add_action( 'woocommerce_api_' . strtolower( get_class( $this->get_plugin() ) ) . '_auth_begin',  array( $this, 'begin_oauth' ) );
		add_action( 'woocommerce_api_' . strtolower( get_class( $this->get_plugin() ) ) . '_auth',        array( $this, 'oauth_authorize' ) );
		add_action( 'woocommerce_api_' . strtolower( get_class( $this->get_plugin() ) ) . '_auth_legacy', array( $this, 'oauth_authorize_legacy' ) );
	}


	/**
	 * Gets the JS script params to localize for the gateway-specific JS.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_gateway_js_localized_script_params() {

		$helper = new SV_WC_Payment_Gateway_API_Response_Message_Helper();

		return array(
			'api_url'        => $this->get_api_endpoint() . '/quickbooks/v4/payments/tokens',
			'ajax_log'       => $this->debug_log(),
			'ajax_log_nonce' => wp_create_nonce( 'wc_' . $this->get_plugin()->get_id() . '_log_js_data' ),
			'ajax_url'       => admin_url( 'admin-ajax.php' ),
			'id_dasherized'  => $this->get_id_dasherized(),
			'generic_error'  => $helper->get_user_message( 'error' ),
		);
	}


	/**
	 * Gets the form fields specific for this gateway.
	 *
	 * @since 2.0.0
	 * @see SV_WC_Payment_Gateway::get_method_form_fields()
	 * @return array
	 */
	protected function get_method_form_fields() {

		return array(

			'consumer_key' => array(
				'title'    => '1.0' === $this->get_oauth_version() ? __( 'Consumer Key', 'ultimatewoo-pro' ) : __( 'Client ID', 'ultimatewoo-pro' ),
				'type'     => 'text',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'Your Intuit Developer App consumer key.', 'ultimatewoo-pro' ),
			),

			'consumer_secret' => array(
				'title'    => '1.0' === $this->get_oauth_version() ? __( 'Consumer Secret', 'ultimatewoo-pro' ) : __( 'Client Secret', 'ultimatewoo-pro' ),
				'type'     => 'password',
				'class'    => 'environment-field production-field',
				'desc_tip' => __( 'Your Intuit Developer App consumer secret.', 'ultimatewoo-pro' ),
			),

			'sandbox_consumer_key' => array(
				'title'    => '1.0' === $this->get_oauth_version() ? __( 'Consumer Key', 'ultimatewoo-pro' ) : __( 'Client ID', 'ultimatewoo-pro' ),
				'type'     => 'text',
				'class'    => 'environment-field sandbox-field',
				'desc_tip' => __( 'Your Intuit Developer App consumer key.', 'ultimatewoo-pro' ),
			),

			'sandbox_consumer_secret' => array(
				'title'    => '1.0' === $this->get_oauth_version() ? __( 'Consumer Secret', 'ultimatewoo-pro' ) : __( 'Client Secret', 'ultimatewoo-pro' ),
				'type'     => 'password',
				'class'    => 'environment-field sandbox-field',
				'desc_tip' => __( 'Your Intuit Developer App consumer secret.', 'ultimatewoo-pro' ),
			),

			'connect_button' => array(
				'title' => __( 'Payments Account', 'ultimatewoo-pro' ),
				'type'  => 'connect_button',
			),
		);
	}


	/**
	 * Enqueues the admin scripts & styles.
	 *
	 * @since 2.0.0
	 */
	public function load_admin_scripts() {

		wp_register_script( 'wc-intuit-payments-connect', 'https://js.appcenter.intuit.com/Content/IA/intuit.ipp.anywhere-1.3.3.js', array(), $this->get_plugin()->get_version() );

		if ( $this->get_plugin()->is_plugin_settings() && $this->get_id() === SV_WC_Helper::get_request( 'section' ) ) {

			wp_enqueue_script( 'wc-intuit-payments-admin', $this->get_plugin()->get_plugin_url() . '/assets/js/admin/wc-intuit-payments-admin.min.js', array( 'jquery', 'wc-intuit-payments-connect' ), $this->get_plugin()->get_version() );

			wp_localize_script( 'wc-intuit-payments-admin', 'wc_intuit_payments', array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'connect_url'      => add_query_arg( 'wc-api', strtolower( get_class( $this->get_plugin() ) ) . '_auth_begin', home_url() ),
				'reconnect_nonce'  => wp_create_nonce( 'wc-intuit-payments-reconnect' ),
				'disconnect_nonce' => wp_create_nonce( 'wc-intuit-payments-disconnect' ),
				'i18n' => array(
					'ays_disconnect' => esc_html__( 'Are you sure you wish to disconnect from your QuickBooks account?', 'ultimatewoo-pro' ),
				),
			) );

			wp_enqueue_style( 'wc-intuit-payments-admin', $this->get_plugin()->get_plugin_url() . '/assets/css/admin/wc-intuit-payments-admin.min.css', $this->get_plugin()->get_version() );
		}
	}


	/**
	 * Generates a "Connect to QuickBooks button" to begin the oAuth flow.
	 *
	 * @since 2.0.0
	 * @param string $key the field key
	 * @param array $data the field params
	 */
	public function generate_connect_button_html( $key, $data ) {

		$data = wp_parse_args( $data, array(
			'title'       => '',
			'class'       => '',
			'description' => '',
		) );

		// load the settings so we can accurately check config
		$this->load_settings();

		ob_start();

		?>

		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
			</th>
			<td class="forminp <?php echo esc_attr( $data['class'] ); ?>">

				<?php if ( $this->is_connected() ) : ?>

					<?php // if the current token can be refreshed, show the admin button
					if ( $this->can_reconnect() ) : ?>
						<a href="#" class="js-wc-intuit-payments-reconnect button" data-gateway-id="<?php echo esc_attr( $this->get_id() ); ?>"><?php esc_html_e( 'Reconnect', 'ultimatewoo-pro' ); ?></a>
					<?php endif; ?>

					<a href="#" class="js-wc-intuit-payments-disconnect button" data-gateway-id="<?php echo esc_attr( $this->get_id() ); ?>"><?php esc_html_e( 'Disconnect from QuickBooks', 'ultimatewoo-pro' ); ?></a>

				<?php elseif ( ! $this->get_consumer_key() || ! $this->get_consumer_secret() ) : ?>

					<button class="button" disabled="disabled">Connect with Quickbooks</button>
					<p class="description"><?php esc_html_e( 'Please save your Consumer Key &amp; Secret before connecting with QuickBooks.', 'ultimatewoo-pro' ); ?></p>

				<?php else : ?>

					<a href="#" class="js-wc-intuit-payments-connect"><?php esc_html_e( 'Connect with QuickBooks', 'ultimatewoo-pro' ); ?></a>

				<?php endif; ?>

			</td>
		</tr>

		<?php

		return ob_get_clean();
	}


	/**
	 * Determine if the gateway is properly configured to perform transactions.
	 *
	 * @since 2.0.0
	 * @see SV_WC_Payment_Gateway::is_configured()
	 * @return bool
	 */
	protected function is_configured() {

		return parent::is_configured() && $this->get_consumer_key() && $this->get_consumer_secret() && $this->is_connected();
	}


	/**
	 * Determines if the merchant has gone through the oAuth flow.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function is_connected() {

		return ( $this->get_oauth_token() && $this->get_oauth_token_secret() && time() < $this->get_oauth_token_expiry() ) || $this->get_access_token();
	}


	/**
	 * Initiates the oAuth process.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 */
	public function begin_oauth() {

		try {

			if ( '1.0' === $this->get_oauth_version() ) {

				$response = $this->get_api()->oauth_get_request_token( add_query_arg( 'wc-api', strtolower( get_class( $this->get_plugin() ) ) . '_auth_legacy', home_url() ) );

				if ( setcookie( 'wc_' . $this->get_id() . '_oauth_token_secret', $response->get_token_secret(), 0, '/' ) ) {
					$url = add_query_arg( 'oauth_token', $response->get_token(), 'https://appcenter.intuit.com/Connect/Begin' );
				} else {
					throw new SV_WC_Plugin_Exception( 'Could not set token secret cookie.' );
				}

			} else {

				$url = add_query_arg( array(
					'client_id'     => $this->get_consumer_key(),
					'scope'         => 'com.intuit.quickbooks.payment',
					'redirect_uri'  => urlencode( add_query_arg( 'wc-api', strtolower( get_class( $this->get_plugin() ) ) . '_auth', home_url() ) ),
					'response_type' => 'code',
					'state'         => hash_hmac( 'sha256', md5( wp_salt(), true ), $this->get_consumer_secret() ),
				), 'https://appcenter.intuit.com/connect/oauth2' );
			}

			wp_redirect( $url );
			exit;

		} catch ( SV_WC_Plugin_Exception $e ) {

			$this->handle_oauth_connect_error( $e );
		}
	}


	/**
	 * Handles the OAuth 2 response.
	 *
	 * @internal
	 *
	 * @since 2.1.0
	 */
	public function oauth_authorize() {

		try {

			if ( $error = SV_WC_Helper::get_request( 'error' ) ) {

				switch ( $error ) {

					case 'access_denied':
						$message = 'The user did not authorize the request.';
					break;

					case 'invalid_scope':
						$message = 'An invalid scope string was sent in the request.';
					break;

					default:
						$message = "An unknown error occured: {$error}";
				}

				throw new SV_WC_API_Exception( $message );
			}

			$state = SV_WC_Helper::get_request( 'state' );
			$code  = SV_WC_Helper::get_request( 'code' );

			if ( ! hash_equals( $state, hash_hmac( 'sha256', md5( wp_salt(), true ), $this->get_consumer_secret() ) ) ) {
				throw new SV_WC_API_Exception( 'Connection state is invalid.' );
			}

			if ( ! $code ) {
				throw new SV_WC_API_Exception( 'Authorization code is missing.' );
			}

			$response = $this->get_api()->get_oauth_tokens( $code, add_query_arg( 'wc-api', strtolower( get_class( $this->get_plugin() ) ) . '_auth', home_url() ) );

			$this->handle_oauth_connect_success( $response );

		} catch ( SV_WC_Plugin_Exception $e ) {

			$this->handle_oauth_connect_error( $e );
		}
	}


	/**
	 * Handles the legacy OAuth 1 authorization response.
	 *
	 * @internal
	 *
	 * @since 2.1.0
	 */
	public function oauth_authorize_legacy() {

		try {

			$token    = SV_WC_Helper::get_request( 'oauth_token' );
			$secret   = $_COOKIE[ 'wc_' . $this->get_id() . '_oauth_token_secret' ];
			$verifier = SV_WC_Helper::get_request( 'oauth_verifier' );

			if ( ! $token ) {
				throw new SV_WC_API_Exception( 'Access token missing.' );
			}

			if ( ! $secret ) {
				throw new SV_WC_API_Exception( 'Could not find token secret cookie.' );
			}

			if ( ! $verifier ) {
				throw new SV_WC_API_Exception( 'Access verifier missing.' );
			}

			$response = $this->get_api()->oauth_get_access_token( $token, $secret, $verifier );

			$this->handle_oauth_connect_success( $response );

		} catch ( SV_WC_Plugin_Exception $e ) {

			$this->handle_oauth_connect_error( $e );
		}
	}


	/**
	 * Handles the auth window after successful auth.
	 *
	 * @since 2.1.0
	 */
	protected function handle_oauth_connect_success( $response ) {

		$this->store_oauth_data( $response );

		echo '<script>window.opener.location.reload();window.close();</script>';
		exit();
	}


	/**
	 * Handles oAuth errors.
	 *
	 * @since 2.0.0
	 * @param \SV_WC_API_Exception $e the exception
	 */
	protected function handle_oauth_connect_error( SV_WC_API_Exception $e ) {

		$message = 'Could not authenticate. ' . $e->getMessage();

		$this->get_plugin()->log( $message, $this->get_id() );

		echo '<script>alert( "' . esc_js( $message ) . '" );window.close();</script>';
		exit();
	}


	/**
	 * Reconnects the current oAuth account.
	 *
	 * This regenerates the oAuth tokens and re-schedules the cron event.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 * @return bool whether the reconnection was successful
	 */
	public function oauth_reconnect() {

		try {

			if ( '1.0' === $this->get_oauth_version() ) {
				$response = $this->get_api()->oauth_reconnect();
			} else {
				$response = $this->get_api()->refresh_oauth_tokens();
			}

			$this->store_oauth_data( $response );

			return true;

		} catch ( SV_WC_API_Exception $e ) {

			$this->get_plugin()->log( 'Could not reconnect. ' . $e->getMessage(), $this->get_id() );

			return false;
		}
	}


	/**
	 * Disconnects the current oAuth account.
	 *
	 * @internal
	 *
	 * @since 2.0.0
	 */
	public function oauth_disconnect() {

		try {

			if ( '1.0' === $this->get_oauth_version() ) {

				$this->get_api()->oauth_disconnect();

			} else {

				// TODO: there is no documented revoke request for oAuth 2.0.......yet
			}

		} catch ( SV_WC_API_Exception $e ) {

			$this->get_plugin()->log( 'Could not disconnect. ' . $e->getMessage(), $this->get_id() );
		}

		// clean auth data no matter what
		$this->store_oauth_data();
	}


	/**
	 * Stores oAuth data on connect or reconnect.
	 *
	 * Stores the token expiration date & schedules a cron event to auto-reconnect
	 * in the future.
	 *
	 * @since 2.0.0
	 *
	 * @param \WC_Intuit_Payments_API_OAuth2_Response|WC_Intuit_Payments_API_oAuth_Response $response response object
	 */
	public function store_oauth_data( $response = null ) {

		if ( '1.0' === $this->get_oauth_version() ) {

			if ( $response ) {

				// store the encrypted tokens
				update_option( $this->get_oauth_token_option_name(),             $this->encrypt_credential( $response->get_token() ) );
				update_option( $this->get_oauth_token_option_name() . '_secret', $this->encrypt_credential( $response->get_token_secret() ) );

				$expiry_date    = time() + $this->get_oauth_token_max_age();
				$reconnect_date = $expiry_date - $this->get_oauth_token_reconnect_window();

				// store the token expiration date
				update_option( $this->get_oauth_token_option_name() . '_expiry', $expiry_date );

				$this->reset_reconnect_cron_event( $reconnect_date );

			} else {

				delete_option( $this->get_oauth_token_option_name() );
				delete_option( $this->get_oauth_token_option_name() . '_secret' );
				delete_option( $this->get_oauth_token_option_name() . '_expiry' );

				wp_clear_scheduled_hook( 'wc_' . $this->get_plugin()->get_id() . '_cron_reconnect', array( $this->get_id() ) );
			}

		} else {

			if ( $response ) {

				$this->set_access_token( $response->get_access_token() );
				$this->set_refresh_token( $response->get_refresh_token() );
				$this->set_access_token_expiry( $response->get_access_token_expiry() );

			} else {

				$this->set_access_token( '' );
				$this->set_refresh_token( '' );
				$this->set_access_token_expiry( '' );
			}
		}
	}


	/**
	 * Resets the scheduled reconnection cron event.
	 *
	 * @since 2.0.0
	 * @param int $time the unix time
	 */
	public function reset_reconnect_cron_event( $time ) {

		wp_clear_scheduled_hook( 'wc_' . $this->get_plugin()->get_id() . '_cron_reconnect', array( $this->get_id() ) );

		wp_schedule_single_event( $time, 'wc_' . $this->get_plugin()->get_id() . '_cron_reconnect', array( $this->get_id() ) );
	}


	/**
	 * Adds a test case field to the payment form.
	 *
	 * @link https://developer.intuit.com/docs/0100_quickbooks_online/0200_dev_guides/payments/testing
	 *
	 * @since 2.0.0
	 * @param string $desc payment form description HTML
	 * @return string
	 */
	public function render_test_case_field( $desc ) {

		// Bail if adding a new payment method, as these test cases have no effect
		if ( is_add_payment_method_page() ) {
			return $desc;
		}

		$options = $this->get_test_case_options();

		if ( ! empty( $options ) ) {

			ob_start();

			echo '<p>' . esc_html__( 'Error Test Case', 'ultimatewoo-pro' ) . '</p>';

			echo '<select name="wc-' . sanitize_html_class( $this->get_id_dasherized() ) . '-test-case">';

				echo '<option value="">' . esc_html__( 'None', 'ultimatewoo-pro' ) . '</option>';

				foreach ( $options as $key => $value ) {
					echo '<option value="' . $key . '">' . esc_html( $value ) . '</option>';
				}

			echo '</select>';

			$desc .= ob_get_clean();
		}

		return $desc;
	}


	/**
	 * Gets the gateway test case options.
	 *
	 * Gateways can override this with their own test case values.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_test_case_options() {

		return array();
	}


	/**
	 * Removes the input names for sensitive payment form fields so they're not
	 * POSTed to the server.
	 *
	 * Concrete gateways need to override this to specify which inputs.
	 *
	 * @since 2.0.0
	 * @param array $fields the payment form fields
	 */
	abstract public function remove_payment_form_field_input_names( $fields );


	/**
	 * Renders hidden inputs on the payment form for the JS token & last four.
	 *
	 * These are populated by the client-side JS after successful tokenization.
	 *
	 * @since 2.0.0
	 */
	public function render_hidden_inputs() {

		// token
		printf( '<input type="hidden" id="%1$s" name="%1$s" />', 'wc-' . sanitize_html_class( $this->get_id_dasherized() ) . '-js-token' );

		// account last four
		printf( '<input type="hidden" id="%1$s" name="%1$s" />', 'wc-' . sanitize_html_class( $this->get_id_dasherized() ) . '-last-four' );

		// If adding a new payment method, add some first & last name fields
		if ( is_add_payment_method_page() ) {

			$user = get_userdata( get_current_user_id() );

			// first name
			printf( '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" />', 'billing_first_name', $user->billing_first_name );

			// last name
			printf( '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" />', 'billing_last_name', $user->billing_last_name );
		}
	}


	/**
	 * Validate the provided payment fields.
	 *
	 * This primarily ensures the data is safe to set on the order object in
	 * get_order() below.
	 *
	 * @since 2.0.0
	 * @see SV_WC_Payment_Gateway_Direct::validate_fields()
	 * @return bool whether the fields are valid
	 */
	public function validate_fields() {

		$is_valid = parent::validate_fields();

		// when using a saved method, there is no further validation required
		if ( SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-payment-token' ) ) {
			return $is_valid;
		}

		// last four
		if ( preg_match( '/\D/', SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-last-four' ) ) ) {

			SV_WC_Helper::wc_add_notice( __( 'Provided last four is invalid.', 'ultimatewoo-pro' ), 'error' );
			$is_valid = false;
		}

		// token
		if ( ! SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-js-token' ) ) {

			SV_WC_Helper::wc_add_notice( __( 'Provided token is invalid.', 'ultimatewoo-pro' ), 'error' );
			$is_valid = false;
		}

		return $is_valid;
	}


	/**
	 * Gets the order object with payment information added.
	 *
	 * @since 2.0.0
	 * @param int $order_id the order ID
	 * @return \WC_Order the order object
	 */
	public function get_order( $order_id ) {

		$order = parent::get_order( $order_id );

		// set the JS-generated token if this is a new payment method
		// neither gateway needs to post any sensitive payment details
		if ( ! isset( $order->payment->token ) ) {

			$order->payment->js_token = SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-js-token' );

			$order->payment->account_number = $order->payment->last_four = SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-last-four' );
		}

		// if a test case was set
		if ( $this->is_test_environment() ) {
			$order->payment->test_case = SV_WC_Helper::get_post( 'wc-' . $this->get_id_dasherized() . '-test-case' );
		}

		return $order;
	}


	/**
	 * Determines if payment methods should be tokenized before payment.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function tokenize_before_sale() {

		return true;
	}


	/**
	 * Gets the API class instance.
	 *
	 * @since 2.0.0
	 * @see \SV_WC_Payment_Gateway::get_api()
	 * @return \WC_Intuit_Payments_API
	 */
	public function get_api() {

		if ( $this->api instanceof WC_Intuit_Payments_API ) {
			return $this->api;
		}

		$path = wc_intuit_payments()->get_plugin_path() . '/includes/api/';

		$files = array(

			// base
			'class-wc-intuit-payments-api',

			// requests
			'requests/abstract-wc-intuit-payments-api-request',
			'requests/abstract-wc-intuit-payments-api-payment-request',
			'requests/class-wc-intuit-payments-api-credit-card-request',
			'requests/class-wc-intuit-payments-api-echeck-request',
			'requests/class-wc-intuit-payments-api-oauth-request',
			'requests/class-wc-intuit-payments-api-oauth2-request',
			'requests/class-wc-intuit-payments-api-payment-method-request',

			// responses
			'responses/abstract-wc-intuit-payments-api-response',
			'responses/abstract-wc-intuit-payments-api-payment-response',
			'responses/abstract-wc-intuit-payments-api-payment-refund-response',
			'responses/class-wc-intuit-payments-api-credit-card-response',
			'responses/class-wc-intuit-payments-api-credit-card-refund-response',
			'responses/class-wc-intuit-payments-api-echeck-response',
			'responses/class-wc-intuit-payments-api-echeck-refund-response',
			'responses/class-wc-intuit-payments-api-oauth-response',
			'responses/class-wc-intuit-payments-api-oauth2-response',
			'responses/class-wc-intuit-payments-api-oauth-management-response',
			'responses/class-wc-intuit-payments-api-payment-method-response',
			'responses/class-wc-intuit-payments-api-get-payment-methods-response',
		);

		foreach ( $files as $file ) {
			require_once( $path . $file . '.php' );
		}

		return $this->api = new WC_Intuit_Payments_API( $this );
	}


	/**
	 * Gets the environment API endpoint.
	 *
	 * @since 2.0.0
	 * @param string $environment_id Optional. One of 'sandbox' or 'production'. Defaults to current configured environment
	 * @return string
	 */
	public function get_api_endpoint( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return $this->is_test_environment( $environment_id ) ? self::API_ENDPOINT_SANDBOX : self::API_ENDPOINT;
	}


	/**
	 * Determines if the current gateway environment is configured to 'sandbox'.
	 *
	 * @since 2.0.0
	 * @see SV_WC_Payment_Gateway::is_test_environment()
	 * @param string $environment_id optional. the environment ID to check, otherwise defaults to the gateway current environment
	 * @return bool
	 */
	public function is_test_environment( $environment_id = null ) {

		// if an environment is passed in, check that
		if ( ! is_null( $environment_id ) ) {
			return self::ENVIRONMENT_SANDBOX === $environment_id;
		}

		// otherwise default to checking the current environment
		return $this->is_environment( self::ENVIRONMENT_SANDBOX );
	}


	/**
	 * Gets the oAuth version to use when connecting to the Intuit API.
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	public function get_oauth_version() {

		$version = get_option( 'wc_' . $this->get_plugin()->get_id() . '_oauth_version', '2.0' );

		/**
		 * Filters the OAuth version used to connect to the Intuit API.
		 *
		 * @since 2.1.0
		 *
		 * @param string $version OAuth version
		 * @param \WC_Gateway_Inuit_Payments $gateway gateway object
		 */
		return apply_filters( 'wc_' . $this->get_plugin()->get_id() . '_oauth_version', $version, $this );
	}


	/**
	 * Gets the merchant's app's consumer key.
	 *
	 * @since 2.0.0
	 * @param string $environment_id Optional. One of 'sandbox' or 'production'. Defaults to current configured environment
	 * @return string
	 */
	public function get_consumer_key( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return $this->is_test_environment( $environment_id ) ? $this->sandbox_consumer_key : $this->consumer_key;
	}


	/**
	 * Gets the merchant's app's consumer secret.
	 *
	 * @since 2.0.0
	 * @param string $environment_id Optional. One of 'sandbox' or 'production'. Defaults to current configured environment
	 * @return string
	 */
	public function get_consumer_secret( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		return $this->is_test_environment( $environment_id ) ? $this->sandbox_consumer_secret : $this->consumer_secret;
	}


	/** oAuth 2.0 methods *****************************************************/

	/**
	 * Gets the Intuit API access token.
	 *
	 * @since 2.1.0
	 *
	 * @param string $environment_id one of 'sandbox' or 'production'. Defaults to current configured environment
	 * @return string
	 */
	public function get_access_token( $environment_id = null ) {

		return get_option( $this->get_token_option_name( $environment_id ) . '_access_token', '' );
	}


	/**
	 * Gets the Intuit API refresh token.
	 *
	 * @since 2.1.0
	 *
	 * @param string $environment_id one of 'sandbox' or 'production'. Defaults to current configured environment
	 * @return string
	 */
	public function get_refresh_token( $environment_id = null ) {

		return get_option( $this->get_token_option_name( $environment_id ) . '_refresh_token', '' );
	}


	/**
	 * Gets the Intuit API refresh token.
	 *
	 * @since 2.1.0
	 *
	 * @param string $environment_id one of 'sandbox' or 'production'. Defaults to current configured environment
	 * @return string
	 */
	public function get_access_token_expiry( $environment_id = null ) {

		return get_option( $this->get_token_option_name( $environment_id ) . '_access_token_expiry', '' );
	}


	/**
	 * Sets the Intuit API access token.
	 *
	 * @since 2.1.0
	 *
	 * @param string $environment_id one of 'sandbox' or 'production'. Defaults to current configured environment
	 * @return string
	 */
	public function set_access_token( $token, $environment_id = null ) {

		return update_option( $this->get_token_option_name( $environment_id ) . '_access_token', $token );
	}


	/**
	 * Gets the Intuit API refresh token.
	 *
	 * @since 2.1.0
	 *
	 * @param string $environment_id one of 'sandbox' or 'production'. Defaults to current configured environment
	 * @return string
	 */
	public function set_refresh_token( $token, $environment_id = null ) {

		return update_option( $this->get_token_option_name( $environment_id ) . '_refresh_token', $token );
	}


	/**
	 * Sets the Intuit API refresh token.
	 *
	 * @since 2.1.0
	 *
	 * @param string $environment_id one of 'sandbox' or 'production'. Defaults to current configured environment
	 * @return string
	 */
	public function set_access_token_expiry( $expiry, $environment_id = null ) {

		return update_option( $this->get_token_option_name( $environment_id ) . '_access_token_expiry', $expiry );
	}


	/**
	 * Gets the option name prefix for the oAuth 2.0 tokens.
	 *
	 * @since 2.1.0
	 *
	 * @param string $environment_id one of 'sandbox' or 'production'. Defaults to current configured environment
	 * @return string
	 */
	public function get_token_option_name( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		$option_name = 'wc_' . $this->get_plugin()->get_id();

		return $this->is_test_environment( $environment_id ) ? "{$option_name}_{$environment_id}" : $option_name;
	}


	/** oAuth 1.0 methods *****************************************************/


	/**
	 * Gets the oAuth token.
	 *
	 * @since 2.0.0
	 * @param string $environment_id Optional. One of 'sandbox' or 'production'. Defaults to current configured environment
	 * @return string
	 */
	public function get_oauth_token( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		$token = get_option( $this->get_oauth_token_option_name(), '' );

		return $this->decrypt_credential( $token );
	}


	/**
	 * Gets the oAuth token secret.
	 *
	 * @since 2.0.0
	 * @param string $environment_id Optional. One of 'sandbox' or 'production'. Defaults to current configured environment
	 * @return string
	 */
	public function get_oauth_token_secret( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		$secret = get_option( $this->get_oauth_token_option_name() . '_secret', '' );

		return $this->decrypt_credential( $secret );
	}


	/**
	 * Determines if the currently connected account is able to reconnect.
	 *
	 * This checks the current date/time against the expiration window.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function can_reconnect() {

		return time() > ( $this->get_oauth_token_expiry() - $this->get_oauth_token_reconnect_window() );
	}


	/**
	 * Gets the stored token expiration date.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	protected function get_oauth_token_expiry() {

		return get_option( $this->get_oauth_token_option_name() . '_expiry', 0 );
	}


	/**
	 * Gets the max valid token age, in seconds.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	protected function get_oauth_token_max_age() {

		/**
		 * Filters the oAuth token expiration age, in seconds.
		 *
		 * Currently, the Payments API specifies 180 days.
		 *
		 * @since 2.0.0
		 * @param int $age the oAuth token expiration age, in seconds. Default: 15552000
		 * @param \WC_Gateway_Inuit_Payments $gateway the gateway object
		 */
		return apply_filters( 'wc_intuit_payments_oauth_token_max_age', 180 * DAY_IN_SECONDS, $this );
	}


	/**
	 * Gets the window in which tokens can be regenerated, in seconds.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	protected function get_oauth_token_reconnect_window() {

		/**
		 * Filters the window in which tokens can be regenerated, in seconds.
		 *
		 * Currently, the Payments API specifies 30 days. We default to 29 as
		 * recommended to be safe.
		 *
		 * @since 2.0.0
		 * @param int $window the window in which tokens can be regenerated, in seconds. Default: 2505600
		 * @param \WC_Gateway_Inuit_Payments $gateway the gateway object
		 */
		return apply_filters( 'wc_intuit_payments_oauth_token_reconnect_window', 29 * DAY_IN_SECONDS, $this );
	}


	/**
	 * Gets the option name for token storage.
	 *
	 * @since 2.0.0
	 * @param string $environment_id Optional. One of 'sandbox' or 'production'. Defaults to current configured environment
	 * @return string
	 */
	protected function get_oauth_token_option_name( $environment_id = null ) {

		if ( is_null( $environment_id ) ) {
			$environment_id = $this->get_environment();
		}

		if ( $this->inherit_settings() ) {
			$gateway_id = current( array_diff( $this->get_plugin()->get_gateway_ids(), array( $this->get_id() ) ) );
		} else {
			$gateway_id = $this->get_id();
		}

		$name = 'wc_' . $gateway_id;

		$name .= 'production' === $environment_id ? '_oauth_token' : '_sandbox_oauth_token';

		return $name;
	}


	/**
	 * Encrypts a connection credential for storage.
	 *
	 * @since 4.6.0-dev
	 * @param string $data the credential value
	 * @return string
	 */
	protected function encrypt_credential( $data ) {

		$data = trim( $data );

		$iv = mcrypt_create_iv( 16, MCRYPT_RAND );

		$encrypted = mcrypt_encrypt( MCRYPT_RIJNDAEL_128, $this->get_encryption_key(), $data, MCRYPT_MODE_CBC, $iv );

		$encrypted = $iv . $encrypted;

		return base64_encode( $encrypted );
	}


	/**
	 * Decrypts a connection credential for use.
	 *
	 * @since 4.6.0-dev
	 * @param string $data the encrypted credential value
	 * @return string
	 */
	protected function decrypt_credential( $data ) {

		if ( empty( $data ) ) {
			return '';
		}

		$decrypted = base64_decode( $data );

		$iv = substr( $decrypted, 0, 16 );

		$decrypted = substr( $decrypted, 16 );

		return trim( mcrypt_decrypt( MCRYPT_RIJNDAEL_128, $this->get_encryption_key(), $decrypted, MCRYPT_MODE_CBC, $iv ) );
	}


	/**
	 * Gets the key used to encrypt the connection credentials.
	 *
	 * @return string
	 */
	private function get_encryption_key() {

		if ( ! $this->encryption_key ) {

			$this->encryption_key = md5( wp_salt(), true );
		}

		return $this->encryption_key;
	}


}
