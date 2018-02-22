<?php
/**
 * Exit if accesses directly
 */
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'Pie_WCWL_Waitlist_Settings' ) ) {
	/**
	 * Waitlist Settings
	 *
	 * Displays settings for the waitlist
	 *
	 * @class Pie_WCWL_Waitlist_Settings
	 */
	class Pie_WCWL_Waitlist_Settings {

		/**
		 * Hooks up the functions for Waitlist Settings
		 *
		 * @access public
		 */
		public function __construct() {
			// Required for our settings tab in woocommerce versions < 2.3
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_custom_settings_tab' ), 50 );
			add_action( 'woocommerce_settings_waitlist', array( $this, 'render_settings' ) );
			add_action( 'woocommerce_update_options_waitlist', array( $this, 'save_settings' ) );
			// Required for our settings section on product tab in woocommerce version >= 2.3
			add_filter( 'woocommerce_get_sections_products', array( $this, 'add_waitlist_settings' ), 10 );
			add_filter( 'woocommerce_get_settings_products', array( $this, 'waitlist_all_settings' ), 10, 2 );
			// Required to filter the email description text on the settings page for "new accounts"
			add_action( 'woocommerce_settings_start', array( $this, 'add_filter_for_new_account_email_description' ), 10 );
			add_action( 'woocommerce_settings_start', array( $this, 'add_filter_for_new_account_old_email_description' ), 10 );
			add_action( 'woocommerce_email_settings_before', array( $this, 'remove_filter_for_email_description_text' ), 10 );
			// Hook up JS to pull in the button to update waitlist counts
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_scripts' ) );
		}

		/**
		 * Enqueue required styles and scripts
		 *
		 * @param $hook
		 */
		public function enqueue_settings_scripts( $hook ) {
			if ( 'woocommerce_page_wc-settings' == $hook && isset( $_GET['section'] ) && 'waitlist' == $_GET['section'] ) {
				wp_enqueue_script( 'wcwl_admin_settings', plugins_url() . '/woocommerce-waitlist/includes/js/wcwl_admin_settings.js' );
				$data = array(
					'get_products_nonce'      => wp_create_nonce( 'wcwl-ajax-get-products-nonce' ),
					'update_counts_nonce'     => wp_create_nonce( 'wcwl-ajax-update-counts-nonce' ),
					'update_desc'             => __( 'Update Waitlist Counts', 'ultimatewoo-pro' ),
					'update_button_text'      => __( 'Update Now', 'ultimatewoo-pro' ),
					'update_warning'          => sprintf( __( '%sWarning:%s This could take a long while depending on how many products there are. Navigating away from this page will stop the update.', 'ultimatewoo-pro' ), '<b>', '</b>' ),
					'update_message'          => sprintf( __( 'Currently updating waitlist counts for product %s', 'ultimatewoo-pro' ), '<span class="wcwl_current_update"></span>/<span class="wcwl_total_updates"></span>' ),
					'update_message_complete' => sprintf( __( 'Successfully updated waitlist counts for %s products', 'ultimatewoo-pro' ), '<span class="wcwl_total_updates"></span>' ),
				);
				wp_localize_script( 'wcwl_admin_settings', 'wcwl_settings', $data );
				wp_enqueue_style( 'wcwl_admin_settings', plugins_url() . '/woocommerce-waitlist/includes/css/wcwl_admin_settings.css' );
			}
		}

		/**
		 * Add waitlist tab to Woocommerce settings tabs array if version is 2.1 - 2.3
		 *
		 * @deprecated 2.3
		 * @deprecated woocommerce provides new functionality for adding settings pages within current tabs in 2.3
		 *
		 * @param array $tabs current settings tabs
		 *
		 * @access     public
		 * @return  array $tabs updated settings tabs
		 * @since      1.3
		 */
		public function add_custom_settings_tab( $tabs ) {
			global $woocommerce;
			if ( version_compare( $woocommerce->version, '2.1-beta-1', '>=' ) && ( ! version_compare( $woocommerce->version, '2.3.0-beta-1', '>=' ) ) ) {
				$tabs['waitlist'] = __( 'Waitlist', 'ultimatewoo-pro' );
			}

			return $tabs;
		}

		/**
		 * Save waitlist settings
		 *
		 * @deprecated 2.3
		 * @deprecated woocommerce provides new functionality for adding settings pages within current tabs in 2.3
		 * @access     public
		 * @return void
		 * @since      1.3
		 */
		public function save_settings() {
			woocommerce_update_options( $this->get_settings() );
		}

		/**
		 * Render waitlist settings page
		 *
		 * @deprecated 2.3
		 * @deprecated woocommerce provides new functionality for adding settings pages within current tabs in 2.3
		 * @access     public
		 * @return void
		 * @since      1.3
		 */
		public function render_settings() {
			woocommerce_admin_fields( $this->get_settings() );
		}

		/**
		 * Return options to be displayed on waitlist settings page
		 *
		 * @access public
		 * @return array $settings options to be rendered
		 * @since  1.3
		 */
		public function get_settings() {
			$settings = array(
				array( 'title' => __( 'Waitlist Options', 'ultimatewoo-pro' ), 'type' => 'title', 'desc' => '', 'id' => 'waitlist_options' ),
				array(
					'title'   => __( 'Waitlists require registration', 'ultimatewoo-pro' ),
					'desc'    => __( 'A user must be logged in to the site to be able to join a waitlist', 'ultimatewoo-pro' ),
					'id'      => 'woocommerce_waitlist_registration_needed',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Archive Waitlists', 'ultimatewoo-pro' ),
					'desc'    => __( 'Maintain a record of waitlists when customers are notified of products coming back into stock and removed', 'ultimatewoo-pro' ),
					'id'      => 'woocommerce_waitlist_archive_on',
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array( 'type' => 'sectionend', 'id' => 'waitlist_options' ),
			);

			return $settings;
		}

		/**
		 * Add waitlist options section to the top of the product settings page
		 *
		 * @param array $sections current woocommerce product sections
		 *
		 * @access public
		 * @return array $sections updated woocommerce product sections
		 * @since  1.3
		 */
		public function add_waitlist_settings( $sections ) {
			global $woocommerce;
			if ( ! version_compare( $woocommerce->version, '2.3.0-beta-1', '>=' ) ) {
				return $sections;
			} else {
				$sections['waitlist'] = __( 'Waitlist', 'ultimatewoo-pro' );
			}

			return $sections;
		}

		/**
		 * Output the settings for the waitlist section under the products tab
		 *
		 * A new filter was added in woocommerce version 2.3 that we need to use
		 *
		 * @param  array  $settings        current settings for this tab
		 * @param  string $current_section the settings section being accessed
		 *
		 * @access public
		 * @return array  $settings        required waitlist settings
		 */
		public function waitlist_all_settings( $settings, $current_section ) {
			if ( $current_section == 'waitlist' ) {
				$settings = $this->get_settings();
			}

			return $settings;
		}

		/**
		 * Add filter for the email description text within email settings on the "new account" tab
		 *
		 * Tab and section names changed from WC 2.1
		 */
		public function add_filter_for_new_account_email_description() {
			if ( ! isset( $_REQUEST['page'] ) || 'wc-settings' != $_REQUEST['page'] || ! isset( $_REQUEST['tab'] ) || 'email' != $_REQUEST['tab'] || ! isset( $_REQUEST['section'] ) || 'wc_email_customer_new_account' != $_REQUEST['section'] ) {
				return;
			} else {
				add_filter( 'gettext', array( $this, 'filter_new_account_email_description' ), 20, 3 );
			}
		}

		/**
		 * Add filter for the email description text within email settings on the "new account" tab
		 *
		 * Required for WC 2.0
		 */
		public function add_filter_for_new_account_old_email_description() {
			if ( ! isset( $_REQUEST['page'] ) || 'woocommerce_settings' != $_REQUEST['page'] || ! isset( $_REQUEST['tab'] ) || 'email' != $_REQUEST['tab'] || ! isset( $_REQUEST['section'] ) || 'WC_Email_Customer_New_Account' != $_REQUEST['section'] ) {
				return;
			} else {
				add_filter( 'gettext', array( $this, 'filter_new_account_email_description' ), 20, 3 );
			}
		}

		/**
		 * Modify the description text within email settings as required
		 *
		 * @param  string $translated_text translated text
		 *
		 * @return string                  modified text string
		 */
		public function filter_new_account_email_description( $translated_text ) {
			switch ( $translated_text ) {
				case 'Customer "new account" emails are sent to the customer when a customer signs up via checkout or account pages.' :
					$translated_text = __( 'Customer "new account" emails are sent to the customer when a customer signs up via checkout page, account page or when adding their email to a waitlist.', 'woocommerce' );
					break;
				case 'Customer new account emails are sent when a customer signs up via the checkout or My Account page.' :
					$translated_text = __( 'Customer "new account" emails are sent to the customer when a customer signs up via checkout page, My Account page or when adding their email to a waitlist.', 'woocommerce' );
					break;
			}

			return $translated_text;
		}

		/**
		 * Remove our filter from the gettext hook as early as possible
		 */
		public function remove_filter_for_email_description_text() {
			remove_filter( 'gettext', array( $this, 'filter_new_account_email_description' ) );
		}
	}

	new Pie_WCWL_Waitlist_Settings;
}
