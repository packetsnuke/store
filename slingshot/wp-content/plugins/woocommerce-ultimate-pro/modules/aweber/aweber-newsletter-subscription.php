<?php
/**
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * License: GPL v3

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

	Copyright (c) 2017 WooCommerce.
*/

if ( is_woocommerce_active() ) {
	/**
	 * Localisation
	 */
	load_plugin_textdomain( 'wc_aweber', false, dirname( plugin_basename( __FILE__ ) ) . '/' );

	/**
	 * woocommerce_aweber class
	 */
	if ( ! class_exists( 'woocommerce_aweber' ) ) {

		class woocommerce_aweber
		{
			var $adminOptionsName = 'woo_aweber_settings';
			var $consumerKey = 'AkhS3EKGcGmE4OHaJU2A7Ae5';
			var $consumerSecret = 'eKns0TZk79dgxVov0WNluqst4hltKMsygiR7sGin';

			/**
			 * __construct function.
			 *
			 * @access public
			 * @return void
			 */
			public function __construct() {
				// Add tab to woocommerce settings
				add_filter( 'woocommerce_settings_tabs_array', array( $this, 'settings_menu' ), 75 );
				add_action( 'woocommerce_settings_tabs_woo_aweber', array( $this, 'settings' ) );
				add_action( 'woocommerce_update_options_woo_aweber', array( $this, 'settings_update' ) );

				// Redirect on init and not on settings page
				add_action( 'admin_init', array( $this, 'auth_redirect' ) );

				// Add frontend field
				add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'aweber_field' ), 5 );
				add_action( 'woocommerce_checkout_order_processed', array( $this, 'process_aweber_field' ), 5, 2 );

				// Widget
				add_action( 'widgets_init', array( $this, 'init_widget' ) );

				// Dashboard Subscriber Info
				add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
			} // End __construct()

			/**
			 * settings_menu function.
			 *
			 * @access public
			 * @return void
			 */
			function settings_menu( $settings_tab ) {
				$settings_tab['woo_aweber'] = __( 'Aweber', 'ultimatewoo-pro' );

				return $settings_tab;
			} // End settings_menu()

			/**
			 * settings function.
			 *
			 * @access public
			 * @return void
			 */
			function settings() {
				$admin_options = get_option( $this->adminOptionsName );

				if ( isset( $_GET[ 'oauth_token' ], $_GET[ 'oauth_verifier' ] ) &&
					! ( isset( $admin_options[ 'oauth_verifier' ] ) && $admin_options[ 'oauth_verifier' ] == sanitize_text_field( $_GET[ 'oauth_verifier' ] ) )
				) {
					$aweber = $this->_get_aweber_api();
					$aweber->user->requestToken = sanitize_text_field( $_GET[ 'oauth_token' ] );
					$aweber->user->verifier = sanitize_text_field( $_GET[ 'oauth_verifier' ] );

					// retrieve the stored request token secret
					$aweber->user->tokenSecret = get_option( '_tmp_aweber_secret' );
					delete_option( '_tmp_aweber_secret' );

					// Exchange a request token with a verifier code for an access token.
					list( $accessTokenKey, $accessTokenSecret ) = $aweber->getAccessToken();

					$admin_options[ 'oauth_verifier' ] = $aweber->user->verifier;
					$admin_options[ 'access_token' ] = $accessTokenKey;
					$admin_options[ 'access_secret' ] = $accessTokenSecret;
					update_option( $this->adminOptionsName, $admin_options );
				}


				//Try to connect and get account details and lists
				try {
					$aweber = $this->_get_aweber_api();
					$account = $aweber->getAccount( $admin_options[ 'access_token' ], $admin_options[ 'access_secret' ] );
				} catch ( AWeberException $e ) {
					$account = null;
					$admin_options[ 'access_token' ] = null;
					$admin_options[ 'access_secret' ] = null;
					update_option( $this->adminOptionsName, $admin_options );
				}
				if ( $account ) {
					$lists = $account->lists;
				}

				if ( ! isset( $admin_options[ 'subscribe_checkout' ] ) )
					$admin_options[ 'subscribe_checkout' ] = 0;
				if ( ! isset( $admin_options[ 'subscribe_label' ] ) )
					$admin_options[ 'subscribe_label' ] = '';
				if ( ! isset( $admin_options[ 'subscribe_checked' ] ) )
					$admin_options[ 'subscribe_checked' ] = 0;
				if ( ! isset( $admin_options[ 'subscribe_id' ] ) )
					$admin_options[ 'subscribe_id' ] = -1;

				include( 'templates/settings.php' );
			} // End settings()

			/**
			 * Check for the redirect request to authorize
			 * @return void
			 */
			function auth_redirect() {
				if ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'woo_aweber' && isset( $_GET[ 'awauth' ] ) && ! isset( $_GET[ 'saved' ] ) ) {
					$admin_options = get_option( $this->adminOptionsName );
					$pageURL = admin_url( 'admin.php?page=wc-settings&tab=woo_aweber' );

					if ( $_GET[ 'awauth' ] == 'false' ) {
						$admin_options[ 'access_token' ] = null;
						$admin_options[ 'access_secret' ] = null;
						update_option( $this->adminOptionsName, $admin_options );
					} elseif ( $_GET[ 'awauth' ] == 'true' ) {
						$aweber = $this->_get_aweber_api();
						list( $key, $secret ) = $aweber->getRequestToken( $pageURL );
						// get the authorization URL
						$authorizationURL = $aweber->getAuthorizeUrl();
						// store the request token secret
						update_option( '_tmp_aweber_secret', $secret );
						// redirect user to authorization URL
						wp_redirect( $authorizationURL );
						exit();
					}
				}
			} // End auth_redirect()

			/**
			 * Get the current admin page url
			 * @return string
			 */
			function get_page_url() {
				$pageURL = 'http';
				if ( isset( $_SERVER[ "HTTPS" ] ) == "on" ) {
					$pageURL .= "s";
				}
				$pageURL .= "://";
				if ( $_SERVER[ "SERVER_PORT" ] != "80" ) {
					$pageURL .= $_SERVER[ "SERVER_NAME" ] . ":" . $_SERVER[ "SERVER_PORT" ] . $_SERVER[ "REQUEST_URI" ];
				} else {
					$pageURL .= $_SERVER[ "SERVER_NAME" ] . $_SERVER[ "REQUEST_URI" ];
				}

				// Make sure we don't have two instances of the "auth" parameter, as we're appending it later as well.
				$pageURL = str_replace( '&awauth=false', '', $pageURL );
				$pageURL = str_replace( '&awauth=true', '', $pageURL );
				return $pageURL;
			} // End get_page_url()

			/**
			 * settings_update function.
			 *
			 * @access public
			 * @return void
			 */
			function settings_update() {
				$admin_options = get_option( $this->adminOptionsName );
				if ( isset( $_POST[ 'wc_aw_subscribe_checkout' ] ) )
					$admin_options[ 'subscribe_checkout' ] = '1';
				else $admin_options[ 'subscribe_checkout' ] = '0';
				if ( isset( $_POST[ 'wc_aw_subscribe_label' ] ) )
					$admin_options[ 'subscribe_label' ] = $_POST[ 'wc_aw_subscribe_label' ];
				if ( isset( $_POST[ 'wc_aw_subscribe_checked' ] ) )
					$admin_options[ 'subscribe_checked' ] = '1';
				else $admin_options[ 'subscribe_checked' ] = '0';
				if ( isset( $_POST[ 'wc_aw_subscribe_id' ] ) )
					$admin_options[ 'subscribe_id' ] = $_POST[ 'wc_aw_subscribe_id' ];
				update_option( $this->adminOptionsName, $admin_options );
			} // End settings_update()

			/**
			 * aweber_field function.
			 *
			 * @access public
			 * @param object $woocommerce_checkout
			 * @return void
			 */
			function aweber_field( $woocommerce_checkout ) {
				$admin_options = get_option( $this->adminOptionsName );
				// Only display subscribe checkbox when aweber authorised to access account
				if ( $admin_options[ 'access_token' ] ) {
					if ( $admin_options[ 'subscribe_checkout' ] == '1' ) {
						$_POST[ 'subscribe_to_aweber' ] = 1;

						woocommerce_form_field( 'subscribe_to_aweber', array(
							'type' => 'checkbox',
							'class' => array( 'form-row-wide' ),
							'label' => $admin_options[ 'subscribe_label' ]
						), $admin_options[ 'subscribe_checked' ] );

						echo '<div class="clear"></div>';
					}
				}
			} // End aweber_field()

			/**
			 * process_aweber_field function.
			 *
			 * @access public
			 * @param int $order_id
			 * @param array $posted
			 * @return void
			 */
			function process_aweber_field( $order_id, $posted ) {
				if ( ! isset( $_POST[ 'subscribe_to_aweber' ] ) )
					return; //No Subscription

				$admin_options = get_option( $this->adminOptionsName );
				$ip = ( array_key_exists( 'X_FORWARDED_FOR', $_SERVER ) ) ? $_SERVER[ 'X_FORWARDED_FOR' ] : $_SERVER[ 'REMOTE_ADDR' ];
				$aweber_results = $this->create_subscriber( $posted[ 'billing_email' ], $ip, $admin_options[ 'subscribe_id' ], $posted[ 'billing_first_name' ] );
			} // End process_aweber_field()

			/**
			 * init_widget function.
			 *
			 * @access public
			 * @return void
			 */
			function init_widget() {
				require_once( 'aweber-widget.php' );
				register_widget( 'WooCommerce_AWeber_Widget' );
			} // End init_widget()

			/**
			 * add_dashboard_widgets function.
			 *
			 * @access public
			 * @return void
			 */
			function add_dashboard_widgets() {
				wp_add_dashboard_widget( 'woocommerce_aweber_subscriber_dashboard', __( 'AWeber Newsletter Subscribers', 'ultimatewoo-pro' ), array( $this, 'subscriber_stats' ) );
			} // End add_dashboard_widgets()

			/**
			 * subscriber_stats function.
			 *
			 * @access public
			 * @return void
			 */
			function subscriber_stats() {
				$admin_options = get_option( $this->adminOptionsName );
				if ( $admin_options[ 'access_token' ] ) {
					if ( ! $html = get_transient( 'woo_aweber_stats', 60 * 60 ) ) {
						try {
							$aweber = $this->_get_aweber_api();
							$account = $aweber->getAccount( $admin_options[ 'access_token' ], $admin_options[ 'access_secret' ] );
							$list = $account->loadFromUrl( '/accounts/' . $account->id . '/lists/' . $admin_options[ 'subscribe_id' ] );
							$html = '<ul class="woocommerce_stats">';
							$html .= '<li><strong>' . $list->total_subscribed_subscribers . '</strong> ' . __( 'Total subscribers', 'ultimatewoo-pro' ) . '</li>';
							$html .= '<li><strong>' . $list->total_unsubscribed_subscribers . '</strong> ' . __( 'Unsubscribes', 'ultimatewoo-pro' ) . '</li>';
							$html .= '<li><strong>' . $list->total_subscribers_subscribed_today . '</strong> ' . __( 'Subscribed today', 'ultimatewoo-pro' ) . '</li>';
							$html .= '<li><strong>' . $list->total_subscribers_subscribed_yesterday . '</strong> ' . __( 'Subscribed yesterday', 'ultimatewoo-pro' ) . '</li>';
							$html .= '<li><strong>' . $list->total_unconfirmed_subscribers . '</strong> ' . __( 'Unconfirmed subscribers', 'ultimatewoo-pro' ) . '</li>';
							$html .= '</ul>';
							set_transient( 'woo_aweber_stats', $html, 60 * 60 );
						} catch ( Exception $e ) {
							$admin_options[ 'access_token' ] = null;
							delete_transient( 'woo_aweber_stats' );
							echo '<div class="error inline"><p>' . __( 'Please authorise WooCommerce to access your AWeber account.', 'ultimatewoo-pro' ) . '</p></div>';
						}
					}
					echo $html;
				} else {
					echo '<div class="error inline"><p>' . __( 'Please authorise WooCommerce to access your AWeber account.', 'ultimatewoo-pro' ) . '</p></div>';
				}
			} // End subscriber_stats()

			/**
			 * _get_aweber_api function.
			 *
			 * @access private
			 * @return object
			 */
			function _get_aweber_api() {
				require_once( 'aweber_api/aweber_api.php' );
				return new AWeberAPI( $this->consumerKey, $this->consumerSecret );
			} // End _get_aweber_api()

			/**
			 * create_subscriber function.
			 *
			 * @access public
			 * @param string $email
			 * @param string $ip
			 * @param string $list_id
			 * @param string $name
			 * @return void
			 */
			function create_subscriber( $email, $ip, $list_id, $name ) {
				$admin_options = get_option( $this->adminOptionsName );
				try {
					$aweber = $this->_get_aweber_api();
					$account = $aweber->getAccount( $admin_options[ 'access_token' ], $admin_options[ 'access_secret' ] );
					$subs = $account->loadFromUrl( '/accounts/' . $account->id . '/lists/' . $list_id . '/subscribers' );
					return $subs->create( array(
						'email' => $email,
						'ip_address' => $ip,
						'name' => $name,
						'ad_tracking' => 'WooCommerce',
					) );
				} catch ( Exception $e ) {
					//List ID was not in this account
					if ( $e->type === 'NotFoundError' ) {
						//$options = get_option( $this->widgetOptionsName );
						//$options['list_id_create_subscriber'] = null;
						//update_option($this->widgetOptionsName, $options);
					}
					//Authorization is invalid
					if ( $e->type === 'UnauthorizedError' )
						$this->deauthorize();
				}
			} // End create_subscriber()
		} // End Class

		// Instantiate the class
		global $woocommerce_aweber;
		$woocommerce_aweber = new woocommerce_aweber();
	}
}

//1.10.13