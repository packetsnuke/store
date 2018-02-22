<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_SETTINGS_base {

	protected static $_instance = NULL;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	function __construct() {

	}

	function init() {

	}

	public function get_setting_array( $setting, $label ) {
		$method = "_get_setting_" . $setting;
		if ( is_callable( array( $this, $method ) ) ) {
			//todo: fix undefined function wp_get_current_user() when this is without @ on some configurations
			return @$this->$method( $setting, $label );
		}

		return array();

	}

	public function settings_options() {
		$settings_options = array(
			"general" => __( 'General', 'woocommerce-tm-extra-product-options' ),
			"display" => __( 'Display', 'woocommerce-tm-extra-product-options' ),
			"cart"    => __( 'Cart', 'woocommerce-tm-extra-product-options' ),
			"string"  => __( 'Strings', 'woocommerce-tm-extra-product-options' ),
			"style"   => __( 'Style', 'woocommerce-tm-extra-product-options' ),
			"global"  => __( 'Global', 'woocommerce-tm-extra-product-options' ),
			"other"   => "other",
			"license" => __( 'License', 'woocommerce-tm-extra-product-options' ),
			"upload"  => __( 'Upload manager', 'woocommerce-tm-extra-product-options' ),
			"code"    => __( 'Custom code', 'woocommerce-tm-extra-product-options' ),
		);

		return $settings_options;
	}

	public function plugin_settings() {
		$settings = array();
		$o = $this->settings_options();
		$ids = array();
		foreach ( $o as $key => $value ) {
			$settings[ $key ] = $this->get_setting_array( $key, $value );
		}

		foreach ( $settings as $key => $value ) {
			foreach ( $value as $key2 => $value2 ) {
				if ( isset( $value2['id'] ) && isset( $value2['default'] ) && $value2['id'] !== 'epo_page_options' ) {
					$ids[ $value2['id'] ] = $value2['default'];
				}
			}
		}

		return $ids;
	}

	public function get_other_settings_headers() {
		$headers = array();

		return apply_filters( 'tm_epo_settings_headers', $headers );
	}

	public function get_other_settings() {
		$settings = array();

		return apply_filters( 'tm_epo_settings_settings', $settings );
	}

	public function _get_setting_general( $setting, $label ) {
		return array(
			array(
				'type'  => 'tm_title',
				'id'    => 'epo_page_options',
				'desc'  => '<span data-menu="tcinit" class="tm-section-menu-item">' . __( 'Initialization', 'woocommerce-tm-extra-product-options' ) . '</span>' .
					'<span data-menu="tcftb" class="tm-section-menu-item">' . __( 'Final total box', 'woocommerce-tm-extra-product-options' ) . '</span>' .
					'<span data-menu="tcvarious" class="tm-section-menu-item">' . __( 'Various', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'title' => $label,
			),
			array(
				'title'    => __( 'Enable front-end for roles', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => __( 'Select the roles that will have access to the extra options.', 'woocommerce-tm-extra-product-options' ),
				'id'       => 'tm_epo_roles_enabled',
				'class'    => 'tcinit chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => '@everyone',
				'type'     => 'multiselect',
				'options'  => tc_get_roles(),
			),
			array(
				'title'    => __( 'Disable front-end for roles', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => __( 'Select the roles that will not have access to the extra options.', 'woocommerce-tm-extra-product-options' ),
				'id'       => 'tm_epo_roles_disabled',
				'class'    => 'tcinit chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'type'     => 'multiselect',
				'options'  => tc_get_roles(),
			),
			array(
				'title'    => __( 'Final total box', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => __( 'Select when to show the final total box', 'woocommerce-tm-extra-product-options' ),
				'id'       => 'tm_epo_final_total_box',
				'class'    => 'tcftb chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'normal',
				'type'     => 'select',
				'options'  => array(
					'normal'              => __( 'Show Both Final and Options total box', 'woocommerce-tm-extra-product-options' ),
					'final'               => __( 'Show only Final total', 'woocommerce-tm-extra-product-options' ),
					'hideoptionsifzero'   => __( 'Show Final total and hide Options total if zero', 'woocommerce-tm-extra-product-options' ),
					'hideifoptionsiszero' => __( 'Hide Final total box if Options total is zero', 'woocommerce-tm-extra-product-options' ),
					'hide'                => __( 'Hide Final total box', 'woocommerce-tm-extra-product-options' ),
					'pxq'                 => __( 'Always show only Final total (Price x Quantity)', 'woocommerce-tm-extra-product-options' ),
					'disable_change'      => __( 'Disable but change product prices', 'woocommerce-tm-extra-product-options' ),
					'disable'             => __( 'Disable', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'   => __( 'Enable Final total box for all products', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check to enable Final total box even when product has no extra options', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_enable_final_total_box_all',
				'class'   => 'tcftb',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Show Unit price on totals box', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check to enable the display of the unit price when the totals box is visible', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_show_unit_price',
				'class'   => 'tcftb',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Include Fees on unit price', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '',
				'id'      => 'tm_epo_fees_on_unit_price',
				'class'   => 'tcftb',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Strip html from emails', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check to strip the html tags from emails', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_strip_html_from_emails',
				'default' => 'yes',
				'class'   => 'tcvarious',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Disable lazy load images', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check to disable lazy loading images.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_no_lazy_load',
				'default' => 'yes',
				'class'   => 'tcvarious',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Enable plugin for WooCommerce shortcodes', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Enabling this will load the plugin files to all WordPress pages. Use with caution.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_enable_shortcodes',
				'default' => 'no',
				'class'   => 'tcvarious',
				'type'    => 'checkbox',
			),
			array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),
		);
	}

	public function _get_setting_display( $setting, $label ) {
		return array(
			array(
				'type' => 'tm_title',
				'id'   => 'epo_page_options',
				'desc' => '<span data-menu="tcdisplay" class="tm-section-menu-item">' . __( 'Display', 'woocommerce-tm-extra-product-options' ) . '</span>' .
					'<span data-menu="tcplacement" class="tm-section-menu-item">' . __( 'Placement', 'woocommerce-tm-extra-product-options' ) . '</span>' .
					'<span data-menu="tcprice" class="tm-section-menu-item">' . __( 'Price', 'woocommerce-tm-extra-product-options' ) . '</span>' .
					'<span data-menu="tcftbox" class="tm-section-menu-item">' . __( 'Floating Totals box', 'woocommerce-tm-extra-product-options' ) . '</span>' .
					'<span data-menu="tcanimation" class="tm-section-menu-item">' . __( 'Animation', 'woocommerce-tm-extra-product-options' ) . '</span>'.
					'<span data-menu="tcvarious2" class="tm-section-menu-item">' . __( 'Various', 'woocommerce-tm-extra-product-options' ) . '</span>',

				'title' => $label,
			),
			array(
				'title'    => __( 'Display', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => __( 'This controls how your fields are displayed on the front-end.<br />If you choose "Show using action hooks" you have to manually write the code to your theme or plugin to display the fields and the placement settings below will not work. <br />If you use Composite Products extension you must leave this setting to "Normal" otherwise the extra options cannot be displayed on the composite product bundles.<br />See more at the documentation.', 'woocommerce-tm-extra-product-options' ),
				'id'       => 'tm_epo_display',
				'class'    => 'tcdisplay chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'normal',
				'type'     => 'select',
				'options'  => array(
					'normal' => __( 'Normal', 'woocommerce-tm-extra-product-options' ),
					'action' => __( 'Show using action hooks', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'    => __( 'Extra Options placement', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => __( 'Select where you want the extra options to appear.', 'woocommerce-tm-extra-product-options' ),
				'id'       => 'tm_epo_options_placement',
				'class'    => 'tcplacement chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'woocommerce_before_add_to_cart_button',
				'type'     => 'select',
				'options'  => array(
					'woocommerce_before_add_to_cart_button' => __( 'Before add to cart button', 'woocommerce-tm-extra-product-options' ),
					'woocommerce_after_add_to_cart_button'  => __( 'After add to cart button', 'woocommerce-tm-extra-product-options' ),

					'woocommerce_before_add_to_cart_form' => __( 'Before cart form', 'woocommerce-tm-extra-product-options' ),
					'woocommerce_after_add_to_cart_form'  => __( 'After cart form', 'woocommerce-tm-extra-product-options' ),

					'woocommerce_before_single_product' => __( 'Before product', 'woocommerce-tm-extra-product-options' ),
					'woocommerce_after_single_product'  => __( 'After product', 'woocommerce-tm-extra-product-options' ),

					'woocommerce_before_single_product_summary' => __( 'Before product summary', 'woocommerce-tm-extra-product-options' ),
					'woocommerce_after_single_product_summary'  => __( 'After product summary', 'woocommerce-tm-extra-product-options' ),

					'woocommerce_product_thumbnails' => __( 'After product image', 'woocommerce-tm-extra-product-options' ),

					'custom' => __( 'Custom hook', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'    => __( 'Extra Options placement custom hook', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '',
				'class'    => 'tcplacement',
				'id'       => 'tm_epo_options_placement_custom_hook',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Extra Options placement hook priority', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '',
				'class'    => 'tcplacement',
				'id'       => 'tm_epo_options_placement_hook_priority',
				'default'  => '50',
				'type'     => 'number',
			),
			array(
				'title'    => __( 'Totals box placement', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => __( 'Select where you want the Totals box to appear.', 'woocommerce-tm-extra-product-options' ),
				'id'       => 'tm_epo_totals_box_placement',
				'class'    => 'tcplacement chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'woocommerce_before_add_to_cart_button',
				'type'     => 'select',
				'options'  => array(
					'woocommerce_before_add_to_cart_button' => __( 'Before add to cart button', 'woocommerce-tm-extra-product-options' ),
					'woocommerce_after_add_to_cart_button'  => __( 'After add to cart button', 'woocommerce-tm-extra-product-options' ),

					'woocommerce_before_add_to_cart_form' => __( 'Before cart form', 'woocommerce-tm-extra-product-options' ),
					'woocommerce_after_add_to_cart_form'  => __( 'After cart form', 'woocommerce-tm-extra-product-options' ),

					'woocommerce_before_single_product' => __( 'Before product', 'woocommerce-tm-extra-product-options' ),
					'woocommerce_after_single_product'  => __( 'After product', 'woocommerce-tm-extra-product-options' ),

					'woocommerce_before_single_product_summary' => __( 'Before product summary', 'woocommerce-tm-extra-product-options' ),
					'woocommerce_after_single_product_summary'  => __( 'After product summary', 'woocommerce-tm-extra-product-options' ),

					'woocommerce_product_thumbnails' => __( 'After product image', 'woocommerce-tm-extra-product-options' ),

					'custom' => __( 'Custom hook', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'    => __( 'Totals box placement custom hook', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '',
				'class'    => 'tcplacement',
				'id'       => 'tm_epo_totals_box_placement_custom_hook',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Totals box placement hook priority', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '',
				'class'    => 'tcplacement',
				'id'       => 'tm_epo_totals_box_placement_hook_priority',
				'default'  => '50',
				'type'     => 'number',
			),
			array(
				'title'    => __( 'Floating Totals box', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => __( 'This will enable a floating box to display your totals box.', 'woocommerce-tm-extra-product-options' ),
				'id'       => 'tm_epo_floating_totals_box',
				'class'    => 'tcftbox chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'disable',
				'type'     => 'select',
				'options'  => array(
					'disable'      => __( 'Disable', 'woocommerce-tm-extra-product-options' ),
					'bottom right' => __( 'Bottom right', 'woocommerce-tm-extra-product-options' ),
					'bottom left'  => __( 'Bottom left', 'woocommerce-tm-extra-product-options' ),
					'top right'    => __( 'Top right', 'woocommerce-tm-extra-product-options' ),
					'top left'     => __( 'Top left', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'    => __( 'Floating Totals box visibility', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => __( 'This determine the floating totals box visibility.', 'woocommerce-tm-extra-product-options' ),
				'id'       => 'tm_epo_floating_totals_box_visibility',
				'class'    => 'tcftbox chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'always',
				'type'     => 'select',
				'options'  => array(
					'always'      => __( 'Always visible', 'woocommerce-tm-extra-product-options' ),
					'afterscroll' => __( 'Visble after scrolling the page', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'   => __( 'Add to cart button on floating totals box', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Display the add to cart button on floating box.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_floating_totals_box_add_button',
				'default' => 'no',
				'class'   => 'tcftbox',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Change original product price', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check to overwrite the original product price when the price is changing.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_change_original_price',
				'default' => 'no',
				'class'   => 'tcprice',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Change variation price', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check to overwrite the variation price when the price is changing.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_change_variation_price',
				'default' => 'no',
				'class'   => 'tcprice',
				'type'    => 'checkbox',
			),
			array(
				'title'    => __( 'Force Select Options', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => __( 'This changes the add to cart button on shop and archive pages to display select options when the product has extra product options.<br />Enabling this will remove the ajax functionality.', 'woocommerce-tm-extra-product-options' ),
				'id'       => 'tm_epo_force_select_options',
				'class'    => 'tcdisplay chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'normal',
				'type'     => 'select',
				'options'  => array(
					'normal'  => __( 'Disable', 'woocommerce-tm-extra-product-options' ),
					'display' => __( 'Enable', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'   => __( 'Enable extra options in shop and category view', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check to enable the display of extra options on the shop page and category view. This setting is theme dependent and some aspects may not work as expected.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_enable_in_shop',
				'default' => 'no',
				'class'   => 'tcdisplay',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Remove Free price label', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check to remove Free price label when product has extra options', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_remove_free_price_label',
				'class'   => 'tcprice',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Hide uploaded file path', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check to hide the uploaded file path from users (in the Order).', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_hide_upload_file_path',
				'class'   => 'tcvarious2',
				'default' => 'yes',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Use progressive display on options', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Enabling this will hide the options on the product page until JavaScript is initialized. This is a fail-safe setting and we recommend to be active.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_progressive_display',
				'class'   => 'tcanimation',
				'default' => 'yes',
				'type'    => 'checkbox',
			),
			array(
				'title'    => __( 'Animation delay', 'woocommerce-tm-extra-product-options' ),
				'id'       => 'tm_epo_animation_delay',
				'class'   => 'tcanimation',
				'default'  => '500',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Start Animation delay', 'woocommerce-tm-extra-product-options' ),
				'id'       => 'tm_epo_start_animation_delay',
				'class'   => 'tcanimation',
				'default'  => '500',
				'type'     => 'text',
			),
			array(
				'title'   => __( 'Show quantity selector only for elements with a value', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check show quantity selector only for elements with a value.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_show_only_active_quantities',
				'class'   => 'tcdisplay',
				'default' => 'yes',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Hide add-to-cart button until an option is chosen', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check this to show the add to cart button only when at least one option is filled.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_hide_add_cart_button',
				'class'   => 'tcdisplay',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Show full width label for select boxes.', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check this to force select boxes to be full width.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_select_fullwidth',
				'class'   => 'tcdisplay',
				'default' => 'yes',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Show description for radio buttons and checkboxes inline.', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check this to disable showing descirption as a tooltip and show it inline instead.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_description_inline',
				'class'   => 'tcdisplay',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Auto hide price if zero', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check this to globally hide the price display if it is zero.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_auto_hide_price_if_zero',
				'class'   => 'tcprice',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Show prices inside select box choices', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check this to show the price of the select box options if the price type is fixed.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_show_price_inside_option',
				'class'   => 'tcprice',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			(TM_EPO_WPML()->is_active())
				?
				array(
					'title'   => __( 'Use translated values when possible on admin Order', 'woocommerce-tm-extra-product-options' ),
					'desc'    => __( 'Please note that if the options on the Order change or get deleted you will get wrong results by enabling this!', 'woocommerce-tm-extra-product-options' ),
					'id'      => 'tm_epo_wpml_order_translate',
					'class'   => 'tcdisplay',
					'default' => 'no',
					'type'    => 'checkbox',

				)
				: array(),
			array(
				'title'   => __( 'Include option pricing in product price', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check this to include the pricing of the options to the product price.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_include_possible_option_pricing',
				'class'   => 'tcprice',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Use the "From" string on displayed product prices', 'woocommerce-tm-extra-product-options' ),
				'desc'    => __( 'Check this to alter the price display of a product when it has extra options with prices.', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_use_from_on_price',
				'class'   => 'tcvarious2',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),
		);
	}

	public function _get_setting_cart( $setting, $label ) {
		return array(
			array(
				'type'  => 'tm_title',
				'id'    => 'epo_page_options',
				'title' => $label,
			),
			array(
				'title'   => __( 'Turn off persistent cart', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'Enable this if the product has a lot of options.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_turn_off_persi_cart',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'    => __( 'Clear cart button', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enables or disables the clear cart button', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_clear_cart_button',
				'class'    => 'chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'normal',
				'type'     => 'select',
				'options'  => array(
					'normal' => __( 'Hide', 'woocommerce-tm-extra-product-options' ),
					'show'   => __( 'Show', 'woocommerce-tm-extra-product-options' ),

				),
			),
			array(
				'title'    => __( 'Cart Field Display', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Select how to display your fields in the cart', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_cart_field_display',
				'class'    => 'chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'normal',
				'type'     => 'select',
				'options'  => array(
					'normal'   => __( 'Normal display', 'woocommerce-tm-extra-product-options' ),
					'link'     => __( 'Display a pop-up link', 'woocommerce-tm-extra-product-options' ),
					'advanced' => __( 'Advanced display', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'    => __( 'Hide extra options in cart', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enables or disables the display of options in the cart.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_hide_options_in_cart',
				'class'    => 'chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'normal',
				'type'     => 'select',
				'options'  => array(
					'normal' => __( 'Show', 'woocommerce-tm-extra-product-options' ),
					'hide'   => __( 'Hide', 'woocommerce-tm-extra-product-options' ),

				),
			),
			array(
				'title'    => __( 'Hide extra options prices in cart', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enables or disables the display of prices of options in the cart.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_hide_options_prices_in_cart',
				'class'    => 'chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'normal',
				'type'     => 'select',
				'options'  => array(
					'normal' => __( 'Show', 'woocommerce-tm-extra-product-options' ),
					'hide'   => __( 'Hide', 'woocommerce-tm-extra-product-options' ),

				),
			),
			version_compare( get_option( 'woocommerce_db_version' ), '2.3', '<' ) ?
				array() :
				array(
					'title'   => __( 'Prevent negative priced products', 'woocommerce-tm-extra-product-options' ),
					'desc'    => '<span>' . __( 'Prevent adding to the cart negative priced products.', 'woocommerce-tm-extra-product-options' ) . '</span>',
					'id'      => 'tm_epo_no_negative_priced_products',
					'default' => 'no',
					'type'    => 'checkbox',
				),
			array(
				'title'   => __( 'Prevent zero priced products', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'Prevent adding to the cart zero priced products.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_no_zero_priced_products',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Hide checkbox element average price', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'This will hide the average price display on the cart for checkboxes.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_hide_cart_average_price',
				'default' => 'yes',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Show image replacement in cart and checkout', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'Enabling this will show the images of elements that have an image replacement.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_show_image_replacement',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Show uploaded image in cart and checkout', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'Enabling this will show the uploaded images in cart and checkout.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_show_upload_image_replacement',
				'default' => 'yes',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Always use unique values on cart for elements.', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '',
				'id'      => 'tm_epo_always_unique_values',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),

		);
	}

	public function _get_setting_string( $setting, $label ) {
		return array(
			array(
				'type'  => 'tm_title',
				'id'    => 'epo_page_options',
				'title' => $label,
			),
			array(
				'title'    => __( 'Cart field/value separator', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter the field/value separator for the cart.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_separator_cart_text',
				'default'  => ':',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Update cart text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter the Update cart text when you edit a product.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_update_cart_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Final total text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter the Final total text or leave blank for default.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_final_total_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Unit price text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter the Unit price text or leave blank for default.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_options_unit_price_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Options total text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter the Options total text or leave blank for default.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_options_total_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Fees total text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter the Fees total text or leave blank for default.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_fees_total_text',
				'default'  => '',
				'type'     => 'text',
			),

			(tc_woocommerce_subscriptions_check()) ?
				array(
					'title'    => __( 'Subscription sign up fee text', 'woocommerce-tm-extra-product-options' ),
					'desc_tip' => '<span>' . __( 'Enter the Subscription sign up fee text or leave blank for default.', 'woocommerce-tm-extra-product-options' ) . '</span>',
					'id'       => 'tm_epo_subscription_fee_text',
					'default'  => '',
					'type'     => 'text',
				) :
				array(),

			array(
				'title'    => __( 'Free Price text replacement', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text to replace the Free price label when product has extra options.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_replacement_free_price_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Reset Options text replacement', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text to replace the Reset options text when using custom variations.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_reset_variation_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Edit Options text replacement', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text to replace the Edit options text on the cart.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_edit_options_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Additional Options text replacement', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text to replace the Additional options text when using the pop up setting on the cart.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_additional_options_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Close button text replacement', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text to replace the Close button text when using the pop up setting on the cart.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_close_button_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Calendar close button text replacement', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text to replace the Close button text on the calendar.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_closetext',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Calendar today button text replacement', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text to replace the Today button text on the calendar.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_currenttext',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Slider previous text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text to replace the previous button text for slider.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_slider_prev_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Slider next text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text to replace the next button text for slider.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_slider_next_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Force Select options text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text to replace the add to cart button text when using the Force select option.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_force_select_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Empty cart text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text to replace the empty cart button text.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_empty_cart_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'This field is required text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text indicate that a field is required.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_this_field_is_required_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Characters remaining text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text to replace the Characters remaining text when using maximum characters on a text field or a textarea.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_characters_remaining_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Uploading files text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text to replace the Uploading files text used in the pop-up after clicking the add to cart button  when there are upload fields.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_uploading_files_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Uploading message text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a message to be used in the pop-up after clicking the add to cart button when there are upload fields.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_uploading_message_text',
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Select file text', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a text replace the Select file text used in the styled upload button.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_select_file_text',
				'default'  => '',
				'type'     => 'text',
			),

			array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),
		);
	}

	public function _get_setting_style( $setting, $label ) {
		return array(
			array(
				'type'  => 'tm_title',
				'id'    => 'epo_page_options',
				'title' => $label,
			),

			array(
				'title'    => __( 'Enable checkbox and radio styles', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enables or disables extra styling for checkboxes and radio buttons.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_css_styles',
				'class'    => 'chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'type'     => 'select',
				'options'  => array(
					''   => __( 'Disable', 'woocommerce-tm-extra-product-options' ),
					'on' => __( 'Enable', 'woocommerce-tm-extra-product-options' ),

				),
			),
			array(
				'title'    => __( 'Style', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Select a style.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_css_styles_style',
				'class'    => 'chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'round',
				'type'     => 'select',
				'options'  => array(
					'round'   => __( 'Round', 'woocommerce-tm-extra-product-options' ),
					'round2'  => __( 'Round 2', 'woocommerce-tm-extra-product-options' ),
					'square'  => __( 'Square', 'woocommerce-tm-extra-product-options' ),
					'square2' => __( 'Square 2', 'woocommerce-tm-extra-product-options' ),

				),
			),
			array(
				'title'    => __( 'Select item border type', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Select a style for the selected border when using image replacements or swatches.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_css_selected_border',
				'class'    => 'chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'type'     => 'select',
				'options'  => array(
					''         => __( 'Default', 'woocommerce-tm-extra-product-options' ),
					'square'   => __( 'Square', 'woocommerce-tm-extra-product-options' ),
					'round'    => __( 'Round', 'woocommerce-tm-extra-product-options' ),
					'shadow'   => __( 'Shadow', 'woocommerce-tm-extra-product-options' ),
					'thinline' => __( 'Thin line', 'woocommerce-tm-extra-product-options' ),
				),
			),

			array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),

		);
	}

	public function _get_setting_global( $setting, $label ) {
		return array(
			array(
				'type'  => 'tm_title',
				'id'    => 'epo_page_options',
				'desc'  => '<span data-menu="tcglobal1" class="tm-section-menu-item">' . __( 'General', 'woocommerce-tm-extra-product-options' ) . '</span>' .
					'<span data-menu="tcglobal2" class="tm-section-menu-item">' . __( 'Visual', 'woocommerce-tm-extra-product-options' ) . '</span>' .
					'<span data-menu="tcglobal3" class="tm-section-menu-item">' . __( 'Product page', 'woocommerce-tm-extra-product-options' ) . '</span>' .
					'<span data-menu="tcglobal4" class="tm-section-menu-item">' . __( 'Elements', 'woocommerce-tm-extra-product-options' ) . '</span>' .
					'<span data-menu="tcglobal5" class="tm-section-menu-item">' . __( 'Locale', 'woocommerce-tm-extra-product-options' ) . '</span>' .
					'<span data-menu="tcglobal6" class="tm-section-menu-item">' . __( 'Pricing', 'woocommerce-tm-extra-product-options' ) . '</span>' .
					'<span data-menu="tcglobal7" class="tm-section-menu-item">' . __( 'Strings', 'woocommerce-tm-extra-product-options' ) . '</span>' .
					'<span data-menu="tcglobal8" class="tm-section-menu-item">' . __( 'Various', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'title' => $label,
			),
			array(
				'title'   => __( 'Enable validation', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'Check to enable validation feature for builder elements', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_global_enable_validation',
				'default' => 'yes',
				'class'   => 'tcglobal1',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Disable error scrolling', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'Check to disable scrolling to the element with an error', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_disable_error_scroll',
				'default' => 'no',
				'class'   => 'tcglobal1',
				'type'    => 'checkbox',
			),
			array(
				'title'    => __( 'Javascript and CSS inclusion mode', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Select how to include JS and CSS files', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_global_js_css_mode',
				'class'    => 'tcglobal1 chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'type'     => 'select',
				'options'  => array(
					''         => __( 'Single minified file', 'woocommerce-tm-extra-product-options' ),
					'multiple' => __( 'Multiple minified files', 'woocommerce-tm-extra-product-options' ),
					'dev'      => __( 'DEV - multiple files', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'   => __( 'Prevent options from being sent to emails', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'Check to disable options from being sent to emails.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_global_prevent_options_from_emails',
				'default' => 'no',
				'class'   => 'tcglobal8',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Attach upload files to emails', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'Check to Attach upload files to emails.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_global_attach_uploaded_to_emails',
				'default' => 'yes',
				'class'   => 'tcglobal8',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Disable PNG convert security', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'Check to disable the convertion to png for image uploads.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_global_no_upload_to_png',
				'default' => 'no',
				'class'   => 'tcglobal8',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Disable Options on Order status change', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'Check this only if you are getting server errors on checkout.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_disable_options_on_order_status',
				'default' => 'no',
				'class'   => 'tcglobal8',
				'type'    => 'checkbox',
			),
			array(
				'title'    => __( 'Override product price', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'This will globally override the product price with the price from the options if the total options price is greater then zero.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_global_override_product_price',
				'class'    => 'tcglobal6 chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'type'     => 'select',
				'options'  => array(
					''    => __( 'Use setting on each product', 'woocommerce-tm-extra-product-options' ),
					'no'  => __( 'No', 'woocommerce-tm-extra-product-options' ),
					'yes' => __( 'Yes', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'   => __( 'Reset option values after the product is added to the cart', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '',
				'id'      => 'tm_epo_global_reset_options_after_add',
				'default' => 'no',
				'class'   => 'tcglobal3',
				'type'    => 'checkbox',
			),
			array(
				'title'    => __( 'Use plus and minus signs on prices in cart and checkout', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( ' Choose how you want the sign of options prices to be displayed.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_global_price_sign',
				'class'    => 'tcglobal8 chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'type'     => 'select',
				'options'  => array(
					''      => __( 'Display both signs', 'woocommerce-tm-extra-product-options' ),
					'minus' => __( 'Display only minus sign', 'woocommerce-tm-extra-product-options' ),

				),
			),
			array(
				'title'    => __( 'Input decimal separator', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( ' Choose how to determine the decimal separator for user inputs', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_global_input_decimal_separator',
				'class'    => 'tcglobal5 chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'type'     => 'select',
				'options'  => array(
					''        => __( 'Use WooCommerce value', 'woocommerce-tm-extra-product-options' ),
					'browser' => __( 'Determine by browser local', 'woocommerce-tm-extra-product-options' ),

				),
			),
			array(
				'title'    => __( 'Displayed decimal separator', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( ' Choose which decimal separator to display on currency prices', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_global_displayed_decimal_separator',
				'class'    => 'tcglobal5 chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'type'     => 'select',
				'options'  => array(
					''        => __( 'Use WooCommerce value', 'woocommerce-tm-extra-product-options' ),
					'browser' => __( 'Determine by browser local', 'woocommerce-tm-extra-product-options' ),

				),
			),

			array(
				'title'   => __( 'Radio button undo button', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_global_radio_undo_button',
				'class'   => 'tcglobal4 chosen_select',
				'css'     => 'min-width:300px;',
				'default' => '',
				'type'    => 'select',
				'options' => array(
					''        => __( 'Use field value', 'woocommerce-tm-extra-product-options' ),
					'enable'  => __( 'Enable', 'woocommerce-tm-extra-product-options' ),
					'disable' => __( 'Disable', 'woocommerce-tm-extra-product-options' ),

				),
			),
			array(
				'title'    => __( 'Required state indicator', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a string to indicate the required state of a field.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_global_required_indicator',
				'default'  => '*',
				'class'    => 'tcglobal7',
				'type'     => 'text',
			),
			array(
				'title'   => __( 'Required state indicator position', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_global_required_indicator_position',
				'class'   => 'tcglobal7 chosen_select',
				'css'     => 'min-width:300px;',
				'default' => 'left',
				'type'    => 'select',
				'options' => array(
					'left'  => __( 'Left of the label', 'woocommerce-tm-extra-product-options' ),
					'right' => __( 'Right of the label', 'woocommerce-tm-extra-product-options' ),

				),
			),
			array(
				'title'   => __( 'Include tax string suffix on totals box', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_global_tax_string_suffix',
				'default' => 'no',
				'class'   => 'tcglobal3',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Load generated styles inline', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'This will prevent some load flickering but it will produce invalid html.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_global_load_generated_styles_inline',
				'default' => 'yes',
				'class'   => 'tcglobal1',
				'type'    => 'checkbox',
			),
			array(
				'title'    => __( 'Datepicker theme', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Select the theme for the datepicker.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_global_datepicker_theme',
				'class'    => 'tcglobal4 chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'type'     => 'select',
				'options'  => array(
					''          => __( 'Use field value', 'woocommerce-tm-extra-product-options' ),
					'epo'       => __( 'Epo White', 'woocommerce-tm-extra-product-options' ),
					'epo-black' => __( 'Epo Black', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'    => __( 'Datepicker size', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Select the size of the datepicker.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_global_datepicker_size',
				'class'    => 'tcglobal4 chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'type'     => 'select',
				'options'  => array(
					''       => __( 'Use field value', 'woocommerce-tm-extra-product-options' ),
					'small'  => __( 'Small', 'woocommerce-tm-extra-product-options' ),
					'medium' => __( 'Medium', 'woocommerce-tm-extra-product-options' ),
					'large'  => __( 'Large', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'    => __( 'Datepicker position', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Select the position of the datepicker.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_global_datepicker_position',
				'class'    => 'tcglobal4 chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => '',
				'type'     => 'select',
				'options'  => array(
					''       => __( 'Use field value', 'woocommerce-tm-extra-product-options' ),
					'normal' => __( 'Normal', 'woocommerce-tm-extra-product-options' ),
					'top'    => __( 'Top of screen', 'woocommerce-tm-extra-product-options' ),
					'bottom' => __( 'Bottom of screen', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'    => __( 'Minimum characters for text-field and text-areas', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a value for the minimum characters the user must enter.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_global_min_chars',
				'default'  => '',
				'class'    => 'tcglobal4',
				'type'     => 'number',
			),
			array(
				'title'    => __( 'Maximum characters for text-field and text-areas', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Enter a value for the minimum characters the user must enter.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_global_max_chars',
				'default'  => '',
				'class'    => 'tcglobal4',
				'type'     => 'number',
			),
			array(
				'title'    => __( 'jQuery selector for main product image', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'This is used to change the product image.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_global_product_image_selector',
				'default'  => '',
				'class'    => 'tcglobal3',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Product image replacement mode', 'woocommerce-tm-extra-product-options' ),
				'desc_tip' => '<span>' . __( 'Self mode replaces the actual image and Inline appends new image elements', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'       => 'tm_epo_global_product_image_mode',
				'class'    => 'tcglobal3 chosen_select',
				'css'      => 'min-width:300px;',
				'default'  => 'self',
				'type'     => 'select',
				'options'  => array(
					'self'   => __( 'Self mode', 'woocommerce-tm-extra-product-options' ),
					'inline' => __( 'Inline mode', 'woocommerce-tm-extra-product-options' ),
				),
			),
			array(
				'title'   => __( 'Move out of stock message', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'This is moves the out of stok message when styled variations are used just below them.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_global_move_out_of_stock',
				'default' => 'no',
				'class'   => 'tcglobal3',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Use internal variation price', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '<span>' . __( 'Use this if your variable products have a lot of options to improve performance. Note that this may cause issues with discount or currency plugins.', 'woocommerce-tm-extra-product-options' ) . '</span>',
				'id'      => 'tm_epo_no_variation_prices_array',
				'default' => 'no',
				'class'   => 'tcglobal3',
				'type'    => 'checkbox',
			),

			array(
				'title'   => __( 'Hide override settings on products', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '',
				'id'      => 'tm_epo_global_hide_product_settings',
				'default' => 'no',
				'class'   => 'tcglobal2',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Hide Builder mode on products', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '',
				'id'      => 'tm_epo_global_hide_product_builder_mode',
				'default' => 'no',
				'class'   => 'tcglobal2',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Hide Normal mode on products', 'woocommerce-tm-extra-product-options' ),
				'desc'    => '',
				'id'      => 'tm_epo_global_hide_product_normal_mode',
				'default' => 'no',
				'class'   => 'tcglobal2',
				'type'    => 'checkbox',
			),

			array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),

		);
	}

	public function _get_setting_other( $setting, $label ) {
		$settings = array();
		$other = $this->get_other_settings();
		foreach ( $other as $key => $setting ) {
			$settings = array_merge( $settings, $setting );
		}

		return $settings;
	}

	public function _get_setting_license( $setting, $label ) {
		$is_active = TM_EPO_LICENSE()->get_license();
		$is_hidden = $is_active || defined('TC_CLIENT_MODE');
		$_license_settings = (!defined( 'TM_DISABLE_LICENSE' )) ?
			array(
				array(
					'type'  => 'tm_title',
					'id'    => 'epo_page_options',
					'title' => $label,
				),
				array(
					'title'    => __( 'Username', 'woocommerce-tm-extra-product-options' ),
					'desc_tip' => '<span>' . __( 'Your Envato username.', 'woocommerce-tm-extra-product-options' ) . '</span>',
					'id'       => 'tm_epo_envato_username',
					'default'  => '',
					'class'    => ($is_hidden?'hidden':''), 
					'type'     => ($is_hidden?'password':'text'),
				),
				array(
					'title'    => __( 'Envato API Key', 'woocommerce-tm-extra-product-options' ),
					'desc_tip' => '<span>' . __( 'You can find your API key by visiting your Account page then clicking the My Settings tab. At the bottom of the page you\'ll find your account\'s API key and a button to regenerate it as needed.', 'woocommerce-tm-extra-product-options' ) . '</span>',
					'id'       => 'tm_epo_envato_apikey',
					'default'  => '',
					'class'    => ($is_hidden?'hidden':''), 
					'type'     => ($is_hidden?'password':'text'),
				),
				array(
					'title'   => __( 'Purchase code', 'woocommerce-tm-extra-product-options' ),
					'desc'    => '<span><p>' . __( 'Please enter your <strong>CodeCanyon WooCommerce Extra Product Options purchase code</strong>.', 'woocommerce-tm-extra-product-options' ) . '</p><p>' . __( 'To access your Purchase Code for an item:', 'woocommerce-tm-extra-product-options' ) . '</p>'
						. '<ol>'
						. '<li>' . __( 'Log into your Marketplace account', 'woocommerce-tm-extra-product-options' ) . '</li>'
						. '<li>' . __( 'From your account dropdown links, select "Downloads"', 'woocommerce-tm-extra-product-options' ) . '</li>'
						. '<li>' . __( 'Click the "Download" button that corresponds with your purchase', 'woocommerce-tm-extra-product-options' ) . '</li>'
						. '<li>' . __( 'Select the "License certificate &amp; purchase code" download link. Your Purchase Code will be displayed within the License Certificate.', 'woocommerce-tm-extra-product-options' ) . '</li>'
						. '</ol>'
						. '<p><img alt="Purchase Code Location" src="' . TM_EPO_PLUGIN_URL . '/assets/images/download_button.gif" title="Purchase Code Location" style="vertical-align: middle;"></p>'
						. '<span class="tm-license-button">'

						. '<button type="button" class="' . (TM_EPO_LICENSE()->get_license() ? "" : "tm-hidden ") . 'tc tc-button tm-deactivate-license" id="tm_deactivate_license">' . __( 'Deactivate License', 'woocommerce-tm-extra-product-options' ) . '</button>'
						. '<button type="button" class="' . (TM_EPO_LICENSE()->get_license() ? "tm-hidden " : "") . 'tc tc-button tm-activate-license" id="tm_activate_license">' . __( 'Activate License', 'woocommerce-tm-extra-product-options' ) . '</button>'

						. '</span>'
						. '<span class="tm-license-result">'
						. ((TM_EPO_LICENSE()->get_license()) ?
							"<span class='activated'><p>" . __( "License activated.", 'woocommerce-tm-extra-product-options' ) . "</p></span>"
							: ""
						)
						. '</span>'
						. '</span>',
					'id'      => 'tm_epo_envato_purchasecode',
					'default' => '',
					'class'    => ($is_hidden?'hidden':''), 
					'type'    => ($is_hidden?'password':'text'),
				),
				array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),
			) : array();

		return $_license_settings;
	}

	public function get_allowed_types() {
		$types = array();
		$wp_get_ext_types = wp_get_ext_types();
		$types["@"] = __( 'Use allowed file types from WordPress', 'woocommerce-tm-extra-product-options' );
		foreach ( $wp_get_ext_types as $key => $value ) {
			$types[ "@" . $key ] = $key . " " . __( 'files', 'woocommerce-tm-extra-product-options' );
			foreach ( $value as $key2 => $value2 ) {
				$types[ $value2 ] = $value2;
			}
		}

		return $types;
	}

	public function _get_setting_upload( $setting, $label ) {
		$upload_dir = get_option( 'tm_epo_upload_folder' );
		$upload_dir = str_replace( "/", "", $upload_dir );
		$upload_dir = sanitize_file_name( $upload_dir );
		$upload_dir = "/" . $upload_dir . "/";

		$html = TM_EPO_HELPER()->file_manager( $upload_dir, '' );

		$_upload_settings =
			array(
				array(
					'type'  => 'tm_title',
					'id'    => 'epo_page_options',
					'title' => $label,
				),
				array(
					'title'    => __( 'Upload folder', 'woocommerce-tm-extra-product-options' ),
					'desc_tip' => '<span>' . __( 'Changing this will only affect future uploads.', 'woocommerce-tm-extra-product-options' ) . '</span>',
					'id'       => 'tm_epo_upload_folder',
					'default'  => 'extra_product_options',
					'type'     => 'text',
				),
				array(
					'title'   => __( 'Enable pop-up message on uploads', 'woocommerce-tm-extra-product-options' ),
					'desc'    => '<span>' . __( 'Enables a pop-up when uploads are made.', 'woocommerce-tm-extra-product-options' ) . '</span>',
					'id'      => 'tm_epo_upload_popup',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'             => __( 'Allowed file types', 'woocommerce-tm-extra-product-options' ),
					'desc_tip'          => '<span>' . __( 'Select which file types the user will be alloed to upload.', 'woocommerce-tm-extra-product-options' ) . '</span>',
					'id'                => 'tm_epo_allowed_file_types',
					'type'              => 'multiselect',
					'class'             => 'wc-enhanced-select',
					'css'               => 'width: 450px;',
					'default'           => '@',
					'options'           => $this->get_allowed_types(),
					'desc_tip'          => FALSE,
					'custom_attributes' => array(
						'data-placeholder' => __( 'Select file types', 'woocommerce-tm-extra-product-options' ),
					),
				),
				array(
					'title'    => __( 'Custom types', 'woocommerce-tm-extra-product-options' ),
					'desc_tip' => '<span>' . __( 'Select custom file types the user will be alloed to upload separated by commas.', 'woocommerce-tm-extra-product-options' ) . '</span>',
					'id'       => 'tm_epo_custom_file_types',
					'default'  => '',
					'type'     => 'text',
				),
				array(
					'type'  => 'tm_html',
					'id'    => 'epo_page_options_html',
					'title' => __( 'File manager', 'woocommerce-tm-extra-product-options' ),
					'html'  => $html,
				),
				array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),
			);

		return $_upload_settings;
	}

	public function _get_setting_code( $setting, $label ) {
		return array(
			array(
				'type'  => 'tm_title',
				'id'    => 'epo_page_options',
				'title' => $label,
			),
			array(
				'title'   => __( 'CSS code', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_css_code',
				'default' => '',
				'type'    => 'textarea',
				'class'   => 'tc-admin-textarea',
			),
			array(
				'title'   => __( 'JavaScript code', 'woocommerce-tm-extra-product-options' ),
				'id'      => 'tm_epo_js_code',
				'default' => '',
				'type'    => 'textarea',
				'class'   => 'tc-admin-textarea',
			),
			array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),
		);
	}
}

