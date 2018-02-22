<?php
/*
	Copyright: © 2009-2017 WooCommerce.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( is_woocommerce_active() ) {

	/**
	 * Localisation
	 **/
	load_plugin_textdomain( 'wc_subscribe_to_newsletter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/**
	 * woocommerce_subscribe_to_newsletter class
	 **/
	if ( ! class_exists( 'WC_Subscribe_To_Newsletter' ) ) {

		/**
		 * WC_Subscribe_To_Newsletter class.
		 */
		class WC_Subscribe_To_Newsletter {

			public $service = null;
			private $setting_tab_id = 'newsletter';

			/**
			 * Constructor
			 */
			public function __construct() {

				$this->current_tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'general';

				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
				// Load in the new settings tabs.
				add_action( 'woocommerce_settings_tabs_array', array( $this, 'add_tab' ), 50 );

				// Run these actions when generating the settings tabs.
				add_action( 'woocommerce_settings_tabs_' . $this->setting_tab_id, array( $this, 'settings_tab_action' ), 10 );
				add_action( 'woocommerce_update_options_' . $this->setting_tab_id, array( $this, 'save_settings' ), 10 );

				// Add the settings fields to each tab.
				add_action( 'woocommerce_newsletter_settings', array( $this, 'add_settings_fields' ), 10 );

				// Options
				add_option( 'woocommerce_newsletter_label', 'Subscribe to our newsletter?' );
				add_option( 'woocommerce_newsletter_checkbox_status', 'unchecked' );
				add_option( 'woocommerce_mailchimp_double_opt_in', 'yes' );

				// Widget
				add_action( 'widgets_init', array( $this, 'init_widget' ) );

				// Dashboard stats
				add_action( 'wp_dashboard_setup', array( $this, 'init_dashboard' ) );

				// Points and rewards
				add_filter( 'wc_points_rewards_action_settings', array( $this, 'pw_action_settings' ) );
				add_filter( 'wc_points_rewards_event_description', array( $this, 'pw_action_event_description' ), 10, 3 );
				add_action( 'wc_subscribed_to_newsletter',  array( $this, 'pw_action' ) );

				// Frontend
				add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'newsletter_field' ), 5 );
				add_action( 'woocommerce_ppe_checkout_order_review', array( $this, 'newsletter_field' ), 5 );
				add_action( 'woocommerce_register_form', array( $this, 'newsletter_field' ), 5 );
				add_action( 'woocommerce_checkout_order_processed', array( $this, 'process_newsletter_field' ), 5, 2 );
				add_action( 'woocommerce_ppe_do_payaction', array( $this, 'process_ppe_newsletter_field' ), 5, 1 );
				add_action( 'woocommerce_register_post', array( $this, 'process_register_form' ), 5, 3 );

				// Get settings
				$this->chosen_service    = get_option( 'woocommerce_newsletter_service' );
				$this->checkbox_status   = get_option( 'woocommerce_newsletter_checkbox_status' );

				// Init chosen service
				if ( 'mailchimp' == $this->chosen_service ) {
					$api_key = get_option( 'woocommerce_mailchimp_api_key' );
					$list    = get_option( 'woocommerce_mailchimp_list', false );

					if ( $api_key ) {
						include_once( 'includes/class-wc-mailchimp-newsletter-integration.php' );
						$this->service = new WC_Mailchimp_Newsletter_Integration( $api_key, $list );
					}
				} elseif ( 'mailpoet' == $this->chosen_service ) {
					$list = get_option( 'woocommerce_mailpoet_list', false );

					include_once( 'includes/class-wc-mailpoet-integration.php' );
					$this->service = new WC_Mailpoet_Integration( $list );
				} else {
					$api_key = get_option( 'woocommerce_cmonitor_api_key' );
					$list    = get_option( 'woocommerce_cmonitor_list', false );

					if ( $api_key ) {
						include_once( 'includes/class-wc-cm-integration.php' );
						$this->service = new WC_CM_Integration( $api_key, $list );
					}
				}

		    }

			/**
			 * Adds plugin action links.
			 *
			 * @since 2.3.5
			 *
			 * @param array $links Plugin action links
			 *
			 * @return array Plugin action links
			 */
			public function plugin_action_links( $links ) {
				$setting_link = admin_url( 'admin.php?page=wc-settings&tab=' . $this->setting_tab_id );

				$plugin_links = array(
					'<a href="' . $setting_link . '">' . __( 'Settings', 'ultimatewoo-pro' ) . '</a>',
					'<a href="https://docs.woocommerce.com/document/newsletter-subscription/">' . __( 'Docs', 'ultimatewoo-pro' ) . '</a>',
					'<a href="http://support.woothemes.com/">' . __( 'Support', 'ultimatewoo-pro' ) . '</a>',
				);

				return array_merge( $plugin_links, $links );
			}

			/**
			 * init_dashboard function.
			 *
			 * @access public
			 * @return void
			 */
			public function init_dashboard() {
				if ( current_user_can( 'manage_woocommerce' ) && $this->service && $this->service->has_list() ) {
					wp_add_dashboard_widget( 'woocommmerce_dashboard_subscribers', esc_html__( 'Newsletter subscribers', 'ultimatewoo-pro' ), array( $this->service, 'show_stats' ) );
				}
			}

			/**
			 * init_widget function.
			 *
			 * @access public
			 * @return void
			 */
			public function init_widget() {
				include_once( 'includes/class-wc-widget-subscribe-to-newsletter.php' );
				register_widget( 'WC_Widget_Subscribe_To_Newsletter' );
			}

			/**
			 * add_tab function.
			 *
			 * @param array $settings_tabs Current setting tabs
			 * @access public
			 * @return void
			 */
			public function add_tab( $settings_tabs ) {
				$settings_tabs[ $this->setting_tab_id ] = esc_html__( 'Newsletter', 'ultimatewoo-pro' );
				return $settings_tabs;
			}

			/**
			 * settings_tab_action function.
			 *
			 * @access public
			 * @return void
			 */
			public function settings_tab_action() {
				global $woocommerce_settings;

				// Determine the current tab in effect.
				$current_tab = $this->get_tab_in_view( current_filter(), 'woocommerce_settings_tabs_' );

				// Hook onto this from another function to keep things clean.
				do_action( 'woocommerce_newsletter_settings' );

				// Display settings for this tab (make sure to add the settings to the tab).
				woocommerce_admin_fields( $woocommerce_settings[ $current_tab ] );
			}

			/**
			 * add_settings_fields()
			 *
			 * Add settings fields for each tab.
			 */
			function add_settings_fields() {
				global $woocommerce_settings;

				// Load the prepared form fields.
				$this->init_form_fields();

				if ( is_array( $this->fields ) ) {
					foreach ( $this->fields as $k => $v ) {
						$woocommerce_settings[ $k ] = $v;
					}
				}
			}

			/**
			 * get_tab_in_view()
			 *
			 * Get the tab current in view/processing.
			 */
			function get_tab_in_view( $current_filter, $filter_base ) {
				return str_replace( $filter_base, '', $current_filter );
			}

			/**
			 * init_form_fields()
			 *
			 * Prepare form fields to be used in the various tabs.
			 */
			function init_form_fields() {
				include_once( 'includes/class-wc-mailchimp-newsletter-integration.php' );
				include_once( 'includes/class-wc-cm-integration.php' );
				include_once( 'includes/class-wc-mailpoet-integration.php' );

				$mailchimp = new WC_Mailchimp_Newsletter_Integration( get_option( 'woocommerce_mailchimp_api_key' ) );
				$cmonitor  = new WC_CM_Integration( get_option( 'woocommerce_cmonitor_api_key' ) );
				$mailpoet  = new WC_Mailpoet_Integration();

				$mailchimp_lists = array( '' => esc_html__( 'Enter your key and save to see your lists', 'ultimatewoo-pro' ) );

				if ( $mailchimp->has_api_key() ) {
					if ( is_array( $mailchimp->get_lists() ) ) {
						$mailchimp_lists = array_merge( array( '' => esc_html__( 'Select a list...', 'ultimatewoo-pro' ) ), $mailchimp->get_lists() );
					} else {
						$mailchimp_lists = array( '' => esc_html__( 'Please create a list in your MailChimp account', 'ultimatewoo-pro' ) );
					}
				}

				$cmonitor_lists = $cmonitor->has_api_key() ? array_merge( array( '' => esc_html__( 'Select a list...', 'ultimatewoo-pro' ) ), $cmonitor->get_lists() ) : array( '' => __( 'Enter your key and save to see your lists', 'ultimatewoo-pro' ) );

				$mailpoet_lists = array_merge( array( '0' => __( 'Select a list...', 'ultimatewoo-pro' ) ), $mailpoet->get_lists() );

				// Define settings
				$this->fields['newsletter'] = apply_filters('woocommerce_newsletter_settings_fields', array(

					array( 'name' => esc_html__( 'Newsletter Configuration', 'ultimatewoo-pro' ), 'type' => 'title', 'desc' => '', 'id' => 'newsletter' ),

					array(
						'name' => esc_html__( 'Service provider', 'ultimatewoo-pro' ),
						'desc' 		=> esc_html__( 'Choose which service is handling your subscribers.', 'ultimatewoo-pro' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_newsletter_service',
						'css' 		=> '',
						'std' 		=> 'mailchimp',
						'type' 		=> 'select',
						'options'	=> array( 'mailchimp' => 'MailChimp', 'cmonitor' => 'Campaign Monitor', 'mailpoet' => 'MailPoet' ),
					),

					array(
						'name' => esc_html__( 'Default checkbox status', 'ultimatewoo-pro' ),
						'desc' 		=> esc_html__( 'The default state of the subscribe checkbox. Be aware some countries have laws against using opt-out checkboxes.', 'ultimatewoo-pro' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_newsletter_checkbox_status',
						'css' 		=> '',
						'std' 		=> '',
						'std' 		=> 'unchecked',
						'type' 		=> 'select',
						'options'	=> array( 'checked' => esc_html__( 'Checked', 'ultimatewoo-pro' ), 'unchecked' => esc_html__( 'Un-checked', 'ultimatewoo-pro' ) ),
					),

					array(
						'name' => esc_html__( 'Subscribe checkbox label', 'ultimatewoo-pro' ),
						'desc' 		=> esc_html__( 'The text you want to display next to the "subscribe to newsletter" checkboxes.', 'ultimatewoo-pro' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_newsletter_label',
						'css' 		=> '',
						'std' 		=> '',
						'type' 		=> 'text',
					),

					array( 'type' => 'sectionend', 'id' => 'newsletter' ),

					array( 'name' => esc_html__( 'API settings', 'ultimatewoo-pro' ), 'type' => 'title', 'id' => 'newsletter_api' ),

					array(
						'name' => esc_html__( 'MailChimp API Key', 'ultimatewoo-pro' ),
						'desc' 		=> __( 'You can obtain your API key by <a href="https://us2.admin.mailchimp.com/account/api/">logging in to your MailChimp account</a>.', 'ultimatewoo-pro' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_mailchimp_api_key',
						'css' 		=> '',
						'std' 		=> '',
						'type' 		=> 'text',
					),

					array(
						'name' => esc_html__( 'MailChimp List', 'ultimatewoo-pro' ),
						'desc' 		=> esc_html__( 'Choose a list customers can subscribe to (you must save your API key first).', 'ultimatewoo-pro' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_mailchimp_list',
						'css' 		=> '',
						'std' 		=> '',
						'type' 		=> 'select',
						'options'	=> $mailchimp_lists,
					),

					array(
						'name' => esc_html__( 'Enable Double Opt-in?', 'ultimatewoo-pro' ),
						'desc' 		=> esc_html__( 'Controls whether a double opt-in confirmation message is sent, defaults to true. Abusing this may cause your account to be suspended.', 'ultimatewoo-pro' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_mailchimp_double_opt_in',
						'css' 		=> '',
						'std' 		=> 'yes',
						'type' 		=> 'checkbox',
					),

					array(
						'name' => esc_html__( 'Campaign Monitor API Key', 'ultimatewoo-pro' ),
						'desc' 		=> esc_html__( 'You can obtain your API key by logging in to your Campaign Monitor account.', 'ultimatewoo-pro' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cmonitor_api_key',
						'css' 		=> '',
						'std' 		=> '',
						'type' 		=> 'text',
					),

					array(
						'name' => esc_html__( 'Campaign Monitor List', 'ultimatewoo-pro' ),
						'desc' 		=> esc_html__( 'Choose a list customers can subscribe to (you must save your API key first).', 'ultimatewoo-pro' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_cmonitor_list',
						'css' 		=> '',
						'std' 		=> '',
						'type' 		=> 'select',
						'options'	=> $cmonitor_lists,
					),

					array(
						'name' => __( 'MailPoet List', 'ultimatewoo-pro' ),
						'desc' 		=> __( 'Choose a list customers can subscribe to. The <a href="https://www.mailpoet.com/">MailPoet</a> WordPress plugin must be installed and configured first.', 'ultimatewoo-pro' ),
						'tip' 		=> '',
						'id' 		=> 'woocommerce_mailpoet_list',
						'css' 		=> '',
						'std' 		=> '',
						'type' 		=> 'select',
						'options'	=> $mailpoet_lists,
					),

					array( 'type' => 'sectionend', 'id' => 'newsletter_api' ),

				)); // End newsletter settings

				$js = "
					jQuery('#woocommerce_newsletter_service').change(function(){

						jQuery('#mainform [id^=woocommerce_mailchimp_], #mainform [id^=woocommerce_cmonitor_], #mainform [id^=woocommerce_mailpoet_]').closest('tr').hide();

						if ( jQuery(this).val() == 'mailchimp' ) {
							jQuery('#mainform [id^=woocommerce_mailchimp_]').closest('tr').show();
						} else if (  jQuery(this).val() == 'mailpoet' ) {
							jQuery('#mainform [id^=woocommerce_mailpoet_]').closest('tr').show();
						} else {
							jQuery('#mainform [id^=woocommerce_cmonitor_]').closest('tr').show();
						}

					}).change();
				";

				wc_enqueue_js( $js );
			}

			/**
			 * save_settings()
			 *
			 * Save settings in a single field in the database for each tab's fields (one field per tab).
			 */
			function save_settings() {
				global $woocommerce_settings;

				// Make sure our settings fields are recognised.
				$this->add_settings_fields();

				$current_tab = $this->get_tab_in_view( current_filter(), 'woocommerce_update_options_' );
				woocommerce_update_options( $woocommerce_settings[ $current_tab ] );
			}

			/**
			 * newsletter_field function.
			 *
			 * @access public
			 * @param mixed $woocommerce_checkout
			 * @return void
			 */
			public function newsletter_field( $woocommerce_checkout ) {
				if ( is_user_logged_in() && get_user_meta( get_current_user_id(), '_wc_subscribed_to_newsletter', true ) ) {
					return;
				}

				if ( ! $this->service || ! $this->service->has_list() ) {
					return;
				}

				$value = 'checked' == $this->checkbox_status ? 1 : 0;

				woocommerce_form_field( 'subscribe_to_newsletter', array(
					'type' => 'checkbox',
					'class' => array('form-row-wide'),
					'label' => get_option( 'woocommerce_newsletter_label' )
				), $value );

				echo '<div class="clear"></div>';
			}

			/**
			 * process_newsletter_field function.
			 *
			 * @access public
			 * @param mixed $order_id
			 * @param mixed $posted
			 * @return void
			 */
			public function process_newsletter_field( $order_id, $posted ) {
				if ( ! $this->service || ! $this->service->has_list() ) {
					return;
				}

				if ( ! isset( $_POST['subscribe_to_newsletter'] ) ) {
					return; // They don't want to subscribe
				}

				$this->service->subscribe( $posted['billing_first_name'], $posted['billing_last_name'], $posted['billing_email'] );

				if ( is_user_logged_in() ) {
					update_user_meta( get_current_user_id(), '_wc_subscribed_to_newsletter', 1 );
				}
			}

			/**
			 * process_ppe_newsletter_field function.
			 *
			 * @access public
			 * @param mixed $order
			 * @return void
			 */
			public function process_ppe_newsletter_field( $order ) {
				if ( ! $this->service || ! $this->service->has_list() ) {
					return;
				}

				if ( ! isset( $_REQUEST['subscribe_to_newsletter'] ) ) {
					return; // They don't want to subscribe
				}

				$billing_email = version_compare( WC_VERSION, '2.7', '<' ) ? $order->billing_email : $order->get_billing_email();

				$this->service->subscribe( '', '', $billing_email );

				$order->add_order_note( esc_html__( 'User subscribed to newsletter via PayPal Express return page.', 'ultimatewoo-pro' ) );
			}

			/**
			 * process_register_form function.
			 *
			 * @access public
			 * @param mixed $sanitized_user_login
			 * @param mixed $user_email
			 * @param mixed $reg_errors
			 * @return void
			 */
			public function process_register_form( $sanitized_user_login, $user_email, $reg_errors ) {
				if ( ! $this->service || ! $this->service->has_list() ) {
					return;
				}

				if ( defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
					return; // Ship checkout
				}

				if ( ! isset( $_REQUEST['subscribe_to_newsletter'] ) ) {
					return; // They don't want to subscribe
				}

				$this->service->subscribe( '', '', $user_email );
			}

			/**
			 * Points and rewards
			 * @return array
			 */
			public function pw_action_settings( $settings ) {
				$settings[] = array(
					'title'    => esc_html__( 'Points earned for newsletter signup' ),
					'desc_tip' => esc_html__( 'Enter the amount of points earned when a customer signs up for a newsletter via the "Subscribe to Newsletter" extension.' ),
					'id'       => 'wc_points_rewards_wc_newsletter_signup',
				);

				return $settings;
			}

			/**
			 * Points and rewards description
			 *
			 * @param  [type] $event_description
			 * @param  [type] $event_type
			 * @param  [type] $event
			 * @return [type]
			 */
			public function pw_action_event_description( $event_description, $event_type, $event ) {
				$points_label = get_option( 'wc_points_rewards_points_label' );

				// set the description if we know the type
				switch ( $event_type ) {
					case 'wc-newsletter-signup':
						/* translators: 1: points label */
						$event_description = sprintf( esc_html__( '%s earned for newsletter signup' ), $points_label );
					break;
				}

				return $event_description;
			}

			/**
			 * The signup action for points and rewards
			 *
			 * @param  string $email
			 */
			public function pw_action( $email ) {
				// can't give points to a user who isn't logged in
				if ( ! is_user_logged_in() ) {
					return;
				}

				// get the points configured for this custom action
				$points = get_option( 'wc_points_rewards_wc_newsletter_signup' );

				if ( ! empty( $points ) ) {
					// arbitrary data can be passed in with the points change, this will be persisted to the points event log
					$data = array( 'email' => $email );

					WC_Points_Rewards_Manager::increase_points( get_current_user_id(), $points, 'wc-newsletter-signup', $data );
				}
			}

		}

		$GLOBALS['WC_Subscribe_To_Newsletter'] = new WC_Subscribe_To_Newsletter();
	} // End if().
} // End if().

//2.3.6