<?php
/*
  Copyright: © 2016-2017 Lucas Stark.
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( is_woocommerce_active() ) {
	require 'wc-catalog-visibility-compatibility.php';
	
	load_plugin_textdomain( 'wc_catalog_restrictions', null, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	require 'includes/class-wc-catalog-visibility-options.php';
	require 'shortcodes/shortcodes-init.php';

	//Initalize the Catalog Restrictions included plugin. 
	require 'lib/woocommerce-catalog-restrictions/woocommerce-catalog-restrictions.php';

	define( 'WC_CATALOG_VISIBILITY_OPTIONS_VERSION', '2.8.5' );

	class WC_Catalog_Visibility_Options {

		public function __construct() {


			$this->current_tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'general';
			$this->settings_tabs = array(
			    'visibility_options' => __( 'Visibility Options', 'ultimatewoo-pro' )
			);

			add_action( 'woocommerce_settings_tabs', array($this, 'on_add_tab'), 10 );

			// Run these actions when generating the settings tabs.
			foreach ( $this->settings_tabs as $name => $label ) {
				add_action( 'woocommerce_settings_tabs_' . $name, array($this, 'settings_tab_action'), 10 );
				add_action( 'woocommerce_update_options_' . $name, array($this, 'save_settings'), 10 );
			}

			// Add the settings fields to each tab.
			add_action( 'woocommerce_visibility_options_settings', array($this, 'add_settings_fields'), 10 );
			add_action( 'woocommerce_admin_field_tinyeditor', array($this, 'on_editor_field') );

			if ( !is_admin() && !defined( 'DOING_CRON' ) ) {
				$this->wc_cvo = new WC_CVO_Visibility_Options();
				add_action( 'woocommerce_init', array($this, 'on_woocommerce_init') );
			}
		}

		public function on_woocommerce_init() {

			if ( (!is_admin() || defined( 'DOING_AJAX' ) ) && !defined( 'DOING_CRON' ) ) {
				//We need to force WooCommerce to set the session cookie
				if ( $this->setting( '_wc_restrictions_locations_enabled', 'no' ) == 'yes' ) {
					if ( !WC_Catalog_Visibility_Compatibility::WC()->session->has_session() ) {
						WC_Catalog_Visibility_Compatibility::WC()->session->set_customer_session_cookie( true );
					}
				}
			}
			
		}

		/*
		 * Admin Functions
		 */

		/* ----------------------------------------------------------------------------------- */
		/* Admin Tabs */
		/* ----------------------------------------------------------------------------------- */

		function on_add_tab() {
			foreach ( $this->settings_tabs as $name => $label ) :
				$class = 'nav-tab';
				if ( $this->current_tab == $name )
					$class .= ' nav-tab-active';
				echo '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $name ) . '" class="' . $class . '">' . $label . '</a>';
			endforeach;
		}

		/**
		 * settings_tab_action()
		 *
		 * Do this when viewing our custom settings tab(s). One function for all tabs.
		 */
		function settings_tab_action() {
			global $woocommerce_settings;

			// Determine the current tab in effect.
			$current_tab = $this->get_tab_in_view( current_filter(), 'woocommerce_settings_tabs_' );

			// Hook onto this from another function to keep things clean.
			// do_action( 'woocommerce_newsletter_settings' );

			do_action( 'woocommerce_visibility_options_settings' );

			// Display settings for this tab (make sure to add the settings to the tab).
			woocommerce_admin_fields( $woocommerce_settings[$current_tab] );
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

			if ( is_array( $this->fields ) ) :
				foreach ( $this->fields as $k => $v ) :
					$woocommerce_settings[$k] = $v;
				endforeach;
			endif;
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

			// Define settings

			$v2 = apply_filters( 'woocommerce_catalog_restrictions_options_settings_fields', array(
			    array(
				'name' => __( 'Location Filter Options', 'ultimatewoo-pro' ),
				'type' => 'title',
				'desc' => '',
				'id' => 'catalog_restrictions_options'
			    ),
			    array(
				'name' => __( 'Location Filter Functionality', 'ultimatewoo-pro' ),
				'desc' => '',
				'css' => 'min-width:300px;',
				'id' => '_wc_restrictions_locations_enabled',
				'type' => 'select',
				'std' => 'no',
				'default' => 'no',
				'class' => 'chosen_select',
				'options' => array('no' => 'Disabled', 'yes' => 'Enabled')
			    ),
			    array(
				'name' => __( 'Location Selection Page', 'woocommerce' ),
				'desc' => sprintf( __( 'This sets the page where users will pick their location - page should have the [location_picker] shortcode.', 'ultimatewoo-pro' ), '<a target="_blank" href="options-permalink.php">', '</a>' ),
				'id' => 'woocommerce_choose_location_page_id',
				'type' => 'single_select_page',
				'std' => '',
				'class' => 'chosen_select_nostd',
				'css' => 'min-width:300px;',
				'desc_tip' => true,
			    ),
			    array(
				'name' => __( 'Location Selection Requirements', 'ultimatewoo-pro' ),
				'desc' => '',
				'css' => 'min-width:300px;',
				'id' => '_wc_restrictions_locations_required',
				'type' => 'select',
				'std' => 'yes',
				'class' => 'chosen_select',
				'options' => array('yes' => 'Require users to select a location', 'no' => 'Location selection is optional')
			    ),
			    array(
				'name' => __( 'Allow Changes to Location Selection?', 'ultimatewoo-pro' ),
				'desc' => '',
				'css' => 'min-width:300px;',
				'id' => '_wc_restrictions_locations_changeable',
				'type' => 'select',
				'std' => 'no',
				'default' => 'no',
				'class' => 'chosen_select',
				'options' => array('yes' => 'Allow users to change location after selection', 'no' => 'Users can not change location after initial selection')
			    ),
			    array(
				'name' => __( 'Use Geo Location ( WooCommerce > 2.3.0 only )', 'ultimatewoo-pro' ),
				'desc' => '',
				'css' => 'min-width:300px;',
				'id' => '_wc_restrictions_locations_use_geo',
				'type' => 'select',
				'std' => 'yes',
				'default' => 'yes',
				'class' => 'chosen_select',
				'options' => array('yes' => 'Use geo location', 'no' => 'Do not use geo location')
			    ),
			    array(
				'name' => __( 'Clear cart when location changes', 'ultimatewoo-pro' ),
				'desc' => '',
				'css' => 'min-width:300px;',
				'id' => '_wc_restrictions_locations_clear',
				'type' => 'select',
				'std' => 'no',
				'default' => 'no',
				'class' => 'chosen_select',
				'options' => array('yes' => 'Clear cart when location changes', 'no' => 'Do not clear cart when location changes')
			    ),
			    array('type' => 'sectionend', 'id' => 'catalog_restrictions_options'),
				)
			);


			$v1 = apply_filters( 'woocommerce_visibility_options_settings_fields', array(
			    array(
				'name' => __( 'Shopping', 'ultimatewoo-pro' ),
				'type' => 'title',
				'desc' => '',
				'id' => 'visibility_options_add-to-cart'
			    ),
			    array(
				'name' => __( 'Purchases', 'woothemes' ),
				'desc' => '',
				'id' => 'wc_cvo_atc',
				'type' => 'select',
				'std' => 'enabled',
				'class' => 'chosen_select',
				'options' => array('enabled' => 'Enabled', 'disabled' => 'Disabled', 'secured' => 'Enabled for Logged In Users')
			    ),
			    array(
				'name' => __( 'Prices', 'ultimatewoo-pro' ),
				'desc' => '',
				'id' => 'wc_cvo_prices',
				'type' => 'select',
				'std' => 'enabled',
				'class' => 'chosen_select',
				'options' => array('enabled' => 'Enabled', 'disabled' => 'Disabled', 'secured' => 'Enabled for Logged In Users')
			    ),
			    array(
				'name' => __( 'Catalog Add to Cart Button Text', 'ultimatewoo-pro' ),
				'type' => 'textarea',
				'desc' => '',
				'css' => 'min-width:500px;',
				'desc' => '',
				'id' => 'wc_cvo_atc_text'
			    ),
			    array(
				'name' => __( 'Catalog Price Text', 'ultimatewoo-pro' ),
				'type' => 'text',
				'desc' => '',
				'css' => 'min-width:500px;',
				'std' => '',
				'id' => 'wc_cvo_c_price_text'
			    ),
			    array(
				'name' => __( 'Alternate Content', 'ultimatewoo-pro' ),
				'type' => 'tinyeditor',
				'desc' => '',
				'id' => 'wc_cvo_s_price_text'
			    ),
			    array('type' => 'sectionend', 'id' => 'visibility_options_prices')
				) );

			$this->fields['visibility_options'] = array_merge( $v1, $v2 );
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

			woocommerce_update_options( $woocommerce_settings[$current_tab] );

			// This is to prevent html being stripped until the WC settings api supports custom field saving
			if ( isset( $_POST['wc_cvo_s_price_text'] ) ) {
				$data = wp_unslash( wp_kses_post( $_POST['wc_cvo_s_price_text'] ) );
				update_option( 'wc_cvo_s_price_text', $data );
			}
		}

		/** Helper functions ***************************************************** */

		/**
		 * Gets a setting
		 */
		public function setting( $key ) {
			return get_option( $key );
		}

		/**
		 * Get the custom admin field: editor
		 */
		public function on_editor_field( $value ) {
			$content = get_option( $value['id'] );
			?>
			<tr valign="top">
				<th scope="row" class="titledesc"><?php echo $value['name'] ?></th>
				<td class="forminp">
					<?php wp_editor( $content, $value['id'] ); ?>
				</td>
			</tr>
			<?php
		}


		/**
		 * @return string The path to this plugin.
		 */
		public function plugin_dir() {
			return plugin_dir_path(__FILE__);
		}

	}

	global $wc_cvo;
	$wc_cvo = new WC_Catalog_Visibility_Options();
}


//Configure default options
register_activation_hook( __FILE__, 'activate_wc_cvo' );

function activate_wc_cvo() {
	if ( !get_option( 'wc_cvo_atc' ) ) {
		update_option( 'wc_cvo_atc', 'enabled' );
	}

	if ( !get_option( 'wc_cvo_prices' ) ) {
		update_option( 'wc_cvo_prices', 'enabled' );
	}
}

function catalog_visibility_user_has_access() {
	return apply_filters( 'catalog_visibility_user_has_access', is_user_logged_in() );
}

//3.0.2