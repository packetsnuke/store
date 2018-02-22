<?php
/*
 * Settings class.
 */

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( class_exists( 'WC_Settings_Page' ) ) {

	//TM_EPO_ADMIN_GLOBAL()->tm_load_scripts();

	class TM_EPO_ADMIN_SETTINGS extends WC_Settings_Page {

		var $other_settings = 0;
		var $settings_options = array();
		var $settings_array = array();

		protected static $_instance = NULL;

		/**
		 * Returns the instance of the plugin.
		 *
		 * @return TM_Extra_Product_Options.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id = TM_EPO_ADMIN_SETTINGS_ID;
			$this->label = __( 'Extra Product Options', 'woocommerce-tm-extra-product-options' );
			$this->tab_count = 0;
			$this->settings_options = TM_EPO_SETTINGS()->settings_options();

			foreach ( $this->settings_options as $key => $value ) {
				$this->settings_array[ $key ] = TM_EPO_SETTINGS()->get_setting_array( $key, $value );
			}

			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );

			add_action( 'woocommerce_admin_field_tm_tabs_header', array( $this, 'tm_tabs_header_setting' ) );
			add_action( 'woocommerce_admin_field_tm_title', array( $this, 'tm_title_setting' ) );
			add_action( 'woocommerce_admin_field_tm_html', array( $this, 'tm_html_setting' ) );
			add_action( 'woocommerce_admin_field_tm_sectionend', array( $this, 'tm_sectionend_setting' ) );

			add_action( 'tm_woocommerce_settings_' . 'epo_page_options', array( $this, 'tm_settings_hook' ) );
			add_action( 'tm_woocommerce_settings_' . 'epo_page_options' . '_end', array( $this, 'tm_settings_hook_end' ) );

			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'tm_settings_hook_all_end' ) );

			add_action( 'woocommerce_admin_settings_sanitize_option_tm_epo_css_code', array( $this, 'tm_return_raw' ), 10, 3 );
			add_action( 'woocommerce_admin_settings_sanitize_option_tm_epo_js_code', array( $this, 'tm_return_raw' ), 10, 3 );
		}

		public function tm_return_raw( $value, $option, $raw_value ) {

			return $raw_value;

		}

		public function tm_echo_header( $counter = 0, $label = "" ) {
			echo '<div class="tm-box">'
				. '<a class="tab-header ' . ($counter == 1 ? 'open' : 'closed') . '" data-id="tmsettings' . $counter . '-tab">'
				. $label
				. '<span class="tcfa tm-arrow2 tcfa-angle-down2"></span></a>'
				. '</div>';
		}

		public function tm_title_setting( $value ) {
			if ( !empty( $value['id'] ) ) {
				do_action( 'tm_woocommerce_settings_' . sanitize_title( $value['id'] ) );
			}
			if ( !empty( $value['title'] ) ) {
				echo '<h4 class="tm-section-title">' . esc_html( $value['title'] ) . '</h4>';
			}
			if ( !empty( $value['desc'] ) ) {
				echo '<div class="tm-section-desc">' . $value['desc'] . '</div>';
			}
			echo '<div class="tm-table-wrap">';
			echo '<table class="form-table">' . "\n\n";
		}

		public function tm_html_setting( $value ) {
			if ( !isset( $value['id'] ) ) {
				$value['id'] = '';
			}
			if ( !isset( $value['title'] ) ) {
				$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
			}

			if ( !empty( $value['id'] ) ) {
				do_action( 'tm_woocommerce_settings_' . sanitize_title( $value['id'] ) );
			} ?>
            <tr valign="top">
                <td colspan="2" class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
					<?php
					if ( !empty( $value['html'] ) ) {
						echo $value['html'];
					}
					?>
                </td>
            </tr>
			<?php
		}

		public function tm_sectionend_setting( $value ) {
			echo '</table>';
			echo '</div>'; // .tm-table-wrap
			if ( !empty( $value['id'] ) ) {
				do_action( 'tm_woocommerce_settings_' . sanitize_title( $value['id'] ) . '_end' );
			}
		}

		public function tm_tabs_header_setting() {

			echo '<div class="tm-settings-wrap tm_wrapper">';

			echo '<div class="transition tm-tabs">';

			echo '<div class="transition tm-tab-headers tmsettings-tab">';

			$counter = 1;
			foreach ( $this->settings_options as $key => $label ) {
				if ( $key == "other" ) {
					$_other_settings = TM_EPO_SETTINGS()->get_other_settings_headers();
					foreach ( $_other_settings as $h_key => $h_label ) {
						$this->tm_echo_header( $counter, $h_label );
						$counter++;
					}
				} else {
					$this->tm_echo_header( $counter, $label );
					$counter++;
				}
			}

			echo '</div>';
			echo '<div class="tm-tabs-wrapper">';
			echo '<div class="header"><h3>' . __( 'Extra Product Options Settings', 'woocommerce-tm-extra-product-options' ) . '</h3></div>';

		}

		public function tm_settings_hook() {
			$this->tab_count++;
			echo '<div class="transition tm-tab tmsettings' . $this->tab_count . '-tab">';
		}

		public function tm_settings_hook_end() {
			echo '</div>';
		}

		public function tm_settings_hook_all_end() {
			echo '</div>'; // .tm-tabs-wrapper
			echo '</div>'; // .transition.tm-tabs
			echo '<div class="tm-footer"><button type="submit" class="tc tc-button tc-save-button" type="submit">' . __( 'Save changes', 'woocommerce-tm-extra-product-options' ) . '</button></div>';
			echo '</div>'; //.tm-settings-wrap
		}

		/**
		 * Get settings array
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = array();
			$settings = array_merge( $settings, array( array( 'type' => 'tm_tabs_header' ) ) );

			foreach ( $this->settings_array as $key => $value ) {
				$settings = array_merge( $settings, $value );
			}

			return apply_filters( 'tm_' . $this->id . '_settings',
				$settings
			); // End pages settings
		}
	}

}
