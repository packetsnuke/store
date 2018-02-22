<?php
/*
 * Copyright (c) 2017 WooCommerce
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Stripe' ) ) :
	/**
	 * Required minimums and constants
	 */
	define( 'WC_STRIPE_VERSION', '4.0.1' );
	define( 'WC_STRIPE_MIN_PHP_VER', '5.6.0' );
	define( 'WC_STRIPE_MIN_WC_VER', '2.6.0' );
	define( 'WC_STRIPE_MAIN_FILE', __FILE__ );
	define( 'WC_STRIPE_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
	define( 'WC_STRIPE_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

	class WC_Stripe {

		/**
		 * @var Singleton The reference the *Singleton* instance of this class
		 */
		private static $instance;

		/**
		 * @var Reference to logging class.
		 */
		private static $log;

		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return Singleton The *Singleton* instance.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Private clone method to prevent cloning of the instance of the
		 * *Singleton* instance.
		 *
		 * @return void
		 */
		private function __clone() {}

		/**
		 * Private unserialize method to prevent unserializing of the *Singleton*
		 * instance.
		 *
		 * @return void
		 */
		private function __wakeup() {}

		/**
		 * Notices (array)
		 * @var array
		 */
		public $notices = array();

		/**
		 * Protected constructor to prevent creating a new instance of the
		 * *Singleton* via the `new` operator from outside of this class.
		 */
		private function __construct() {
			add_action( 'admin_init', array( $this, 'check_environment' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
			add_action( 'plugins_loaded', array( $this, 'init' ) );
			add_action( 'wp_loaded', array( $this, 'hide_notices' ) );
		}

		/**
		 * Init the plugin after plugins_loaded so environment variables are set.
		 *
		 * @since 1.0.0
		 * @version 4.0.0
		 */
		public function init() {
			require_once( dirname( __FILE__ ) . '/includes/class-wc-stripe-logger.php' );
			require_once( dirname( __FILE__ ) . '/includes/class-wc-stripe-helper.php' );
			include_once( dirname( __FILE__ ) . '/includes/class-wc-stripe-api.php' );

			// Don't hook anything else in the plugin if we're in an incompatible environment
			if ( self::get_environment_warning() ) {
				return;
			}

			load_plugin_textdomain( 'woocommerce-gateway-stripe', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

			require_once( dirname( __FILE__ ) . '/includes/abstracts/abstract-wc-stripe-payment-gateway.php' );
			require_once( dirname( __FILE__ ) . '/includes/class-wc-stripe-webhook-handler.php' );
			require_once( dirname( __FILE__ ) . '/includes/class-wc-stripe-sepa-payment-token.php' );
			require_once( dirname( __FILE__ ) . '/includes/class-wc-gateway-stripe.php' );
			require_once( dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-stripe-bancontact.php' );
			require_once( dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-stripe-sofort.php' );
			require_once( dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-stripe-giropay.php' );
			require_once( dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-stripe-ideal.php' );
			require_once( dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-stripe-p24.php' );
			require_once( dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-stripe-alipay.php' );
			require_once( dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-stripe-sepa.php' );
			require_once( dirname( __FILE__ ) . '/includes/payment-methods/class-wc-gateway-stripe-bitcoin.php' );
			require_once( dirname( __FILE__ ) . '/includes/payment-methods/class-wc-stripe-payment-request.php' );
			require_once( dirname( __FILE__ ) . '/includes/compat/class-wc-stripe-compat.php' );
			require_once( dirname( __FILE__ ) . '/includes/compat/class-wc-stripe-sepa-compat.php' );
			require_once( dirname( __FILE__ ) . '/includes/class-wc-stripe-order-handler.php' );
			require_once( dirname( __FILE__ ) . '/includes/class-wc-stripe-payment-tokens.php' );
			require_once( dirname( __FILE__ ) . '/includes/class-wc-stripe-customer.php' );

			// REMOVE IN THE FUTURE.
			require_once( dirname( __FILE__ ) . '/includes/deprecated/class-wc-stripe-apple-pay.php' );

			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateways' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			add_filter( 'woocommerce_get_sections_checkout', array( $this, 'filter_gateway_order_admin' ) );
		}

		/**
		 * Hides any admin notices.
		 *
		 * @since 4.0.0
		 * @version 4.0.0
		 */
		public function hide_notices() {
			if ( isset( $_GET['wc-stripe-hide-notice'] ) && isset( $_GET['_wc_stripe_notice_nonce'] ) ) {
				if ( ! wp_verify_nonce( $_GET['_wc_stripe_notice_nonce'], 'wc_stripe_hide_notices_nonce' ) ) {
					wp_die( __( 'Action failed. Please refresh the page and retry.', 'ultimatewoo-pro' ) );
				}

				if ( ! current_user_can( 'manage_woocommerce' ) ) {
					wp_die( __( 'Cheatin&#8217; huh?', 'ultimatewoo-pro' ) );
				}

				$notice = wc_clean( $_GET['wc-stripe-hide-notice'] );

				switch ( $notice ) {
					case 'ssl':
						update_option( 'wc_stripe_show_ssl_notice', 'no' );
						break;
					case 'keys':
						update_option( 'wc_stripe_show_keys_notice', 'no' );
						break;
				}
			}
		}

		/**
		 * Allow this class and other classes to add slug keyed notices (to avoid duplication).
		 *
		 * @since 1.0.0
		 * @version 4.0.0
		 */
		public function add_admin_notice( $slug, $class, $message, $dismissible = false ) {
			$this->notices[ $slug ] = array(
				'class'       => $class,
				'message'     => $message,
				'dismissible' => $dismissible,
			);
		}

		/**
		 * Display any notices we've collected thus far (e.g. for connection, disconnection).
		 *
		 * @since 1.0.0
		 * @version 4.0.0
		 */
		public function admin_notices() {
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				return;
			}

			foreach ( (array) $this->notices as $notice_key => $notice ) {
				echo '<div class="' . esc_attr( $notice['class'] ) . '" style="position:relative;">';

				if ( $notice['dismissible'] ) {
				?>
					<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wc-stripe-hide-notice', $notice_key ), 'wc_stripe_hide_notices_nonce', '_wc_stripe_notice_nonce' ) ); ?>" class="woocommerce-message-close notice-dismiss" style="position:absolute;right:1px;padding:9px;text-decoration:none;"></a>
				<?php
				}

				echo '<p>';
				echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) );
				echo '</p></div>';
			}
		}

		/**
		 * Checks the environment for compatibility problems.  Returns a string with the first incompatibility
		 * found or false if the environment has no problems.
		 *
		 * @since 1.0.0
		 * @version 4.0.0
		 */
		public function get_environment_warning() {
			if ( version_compare( phpversion(), WC_STRIPE_MIN_PHP_VER, '<' ) ) {
				/* translators: 1) int version 2) int version */
				$message = __( 'WooCommerce Stripe - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'ultimatewoo-pro' );

				return sprintf( $message, WC_STRIPE_MIN_PHP_VER, phpversion() );
			}

			if ( ! defined( 'WC_VERSION' ) ) {
				return __( 'WooCommerce Stripe requires WooCommerce to be activated to work.', 'ultimatewoo-pro' );
			}

			if ( version_compare( WC_VERSION, WC_STRIPE_MIN_WC_VER, '<' ) ) {
				/* translators: 1) int version 2) int version */
				$message = __( 'WooCommerce Stripe - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'ultimatewoo-pro' );

				return sprintf( $message, WC_STRIPE_MIN_WC_VER, WC_VERSION );
			}

			if ( ! function_exists( 'curl_init' ) ) {
				return __( 'WooCommerce Stripe - cURL is not installed.', 'ultimatewoo-pro' );
			}

			return false;
		}

		/**
		 * Get setting link.
		 *
		 * @since 1.0.0
		 *
		 * @return string Setting link
		 */
		public function get_setting_link() {
			$use_id_as_section = function_exists( 'WC' ) ? version_compare( WC()->version, '2.6', '>=' ) : false;

			$section_slug = $use_id_as_section ? 'stripe' : strtolower( 'WC_Gateway_Stripe' );

			return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $section_slug );
		}

		/**
		 * The backup sanity check, in case the plugin is activated in a weird way,
		 * or the environment changes after activation. Also handles upgrade routines.
		 *
		 * @since 1.0.0
		 * @version 4.0.0
		 */
		public function check_environment() {
			if ( ! defined( 'IFRAME_REQUEST' ) && ( WC_STRIPE_VERSION !== get_option( 'wc_stripe_version' ) ) ) {
				$this->install();

				do_action( 'woocommerce_stripe_updated' );
			}

			$environment_warning = $this->get_environment_warning();

			if ( $environment_warning && is_plugin_active( plugin_basename( __FILE__ ) ) ) {
				$this->add_admin_notice( 'bad_environment', 'error', $environment_warning );
			}

			$show_ssl_notice  = get_option( 'wc_stripe_show_ssl_notice' );
			$show_keys_notice = get_option( 'wc_stripe_show_keys_notice' );
			$options          = get_option( 'woocommerce_stripe_settings' );

			if ( isset( $options['enabled'] ) && 'yes' === $options['enabled'] && empty( $show_keys_notice ) ) {
				$secret  = WC_Stripe_API::get_secret_key();

				if ( empty( $secret ) && ! ( isset( $_GET['page'], $_GET['section'] ) && 'wc-settings' === $_GET['page'] && 'stripe' === $_GET['section'] ) ) {
					$setting_link = $this->get_setting_link();
					/* translators: 1) link */
					$this->add_admin_notice( 'keys', 'notice notice-warning', sprintf( __( 'Stripe is almost ready. To get started, <a href="%s">set your Stripe account keys</a>.', 'ultimatewoo-pro' ), $setting_link ), true );
				}
			}

			if ( empty( $show_ssl_notice ) && isset( $options['enabled'] ) && 'yes' === $options['enabled'] ) {
				// Show message if enabled and FORCE SSL is disabled and WordpressHTTPS plugin is not detected.
				if ( ( function_exists( 'wc_site_is_https' ) && ! wc_site_is_https() ) && ( 'no' === get_option( 'woocommerce_force_ssl_checkout' ) && ! class_exists( 'WordPressHTTPS' ) ) ) {
					/* translators: 1) link 2) link */
					$this->add_admin_notice( 'ssl', 'notice notice-warning', sprintf( __( 'Stripe is enabled, but the <a href="%1$s">force SSL option</a> is disabled; your checkout may not be secure! Please enable SSL and ensure your server has a valid <a href="%2$s" target="_blank">SSL certificate</a> - Stripe will only work in test mode.', 'ultimatewoo-pro' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ), 'https://en.wikipedia.org/wiki/Transport_Layer_Security' ), true );
				}
			}
		}

		/**
		 * Updates the plugin version in db
		 *
		 * @since 3.1.0
		 * @version 4.0.0
		 */
		public function update_plugin_version() {
			delete_option( 'wc_stripe_version' );
			update_option( 'wc_stripe_version', WC_STRIPE_VERSION );
		}

		/**
		 * Handles upgrade routines.
		 *
		 * @since 3.1.0
		 * @version 3.1.0
		 */
		public function install() {
			if ( ! defined( 'WC_STRIPE_INSTALLING' ) ) {
				define( 'WC_STRIPE_INSTALLING', true );
			}

			$this->update_plugin_version();
		}

		/**
		 * Adds plugin action links.
		 *
		 * @since 1.0.0
		 * @version 4.0.0
		 */
		public function plugin_action_links( $links ) {
			$plugin_links = array(
				'<a href="admin.php?page=wc-settings&tab=checkout&section=stripe">' . esc_html__( 'Settings', 'ultimatewoo-pro' ) . '</a>',
				'<a href="https://docs.woocommerce.com/document/stripe/">' . esc_html__( 'Docs', 'ultimatewoo-pro' ) . '</a>',
				'<a href="https://woocommerce.com/contact-us/">' . esc_html__( 'Support', 'ultimatewoo-pro' ) . '</a>',
			);
			return array_merge( $plugin_links, $links );
		}

		/**
		 * Add the gateways to WooCommerce.
		 *
		 * @since 1.0.0
		 * @version 4.0.0
		 */
		public function add_gateways( $methods ) {
			if ( class_exists( 'WC_Subscriptions_Order' ) && function_exists( 'wcs_create_renewal_order' ) || class_exists( 'WC_Pre_Orders_Order' ) ) {
				$methods[] = 'WC_Stripe_Compat';
				$methods[] = 'WC_Stripe_Sepa_Compat';
			} else {
				$methods[] = 'WC_Gateway_Stripe';
				$methods[] = 'WC_Gateway_Stripe_Sepa';
			}

			$methods[] = 'WC_Gateway_Stripe_Bancontact';
			$methods[] = 'WC_Gateway_Stripe_Sofort';
			$methods[] = 'WC_Gateway_Stripe_Giropay';
			$methods[] = 'WC_Gateway_Stripe_Ideal';
			$methods[] = 'WC_Gateway_Stripe_P24';
			$methods[] = 'WC_Gateway_Stripe_Alipay';
			$methods[] = 'WC_Gateway_Stripe_Bitcoin';

			return $methods;
		}

		/**
		 * Modifies the order of the gateways displayed in admin.
		 *
		 * @since 4.0.0
		 * @version 4.0.0
		 */
		public function filter_gateway_order_admin( $sections ) {
			unset( $sections['stripe'] );
			unset( $sections['stripe_bancontact'] );
			unset( $sections['stripe_sofort'] );
			unset( $sections['stripe_giropay'] );
			unset( $sections['stripe_ideal'] );
			unset( $sections['stripe_p24'] );
			unset( $sections['stripe_alipay'] );
			unset( $sections['stripe_sepa'] );
			unset( $sections['stripe_bitcoin'] );

			$sections['stripe']            = 'Stripe';
			$sections['stripe_bancontact'] = __( 'Stripe Bancontact', 'ultimatewoo-pro' );
			$sections['stripe_sofort']     = __( 'Stripe SOFORT', 'ultimatewoo-pro' );
			$sections['stripe_giropay']    = __( 'Stripe Giropay', 'ultimatewoo-pro' );
			$sections['stripe_ideal']      = __( 'Stripe iDeal', 'ultimatewoo-pro' );
			$sections['stripe_p24']        = __( 'Stripe P24', 'ultimatewoo-pro' );
			$sections['stripe_alipay']     = __( 'Stripe Alipay', 'ultimatewoo-pro' );
			$sections['stripe_sepa']       = __( 'Stripe SEPA Direct Debit', 'ultimatewoo-pro' );
			$sections['stripe_bitcoin']    = __( 'Stripe Bitcoin', 'ultimatewoo-pro' );

			return $sections;
		}
	}

	WC_Stripe::get_instance();

endif;

//4.0.1