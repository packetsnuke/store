<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

/**
 * Main plugin class responsible for displaying the Extra Product Options on the frontend
 */
final class TM_Extra_Product_Options {

	public $version = TM_EPO_VERSION;
	private $_namespace = 'tm-extra-product-options/';

	/** Holds the current post id **/
	private $postid_pre = FALSE;

	/** Helper for determining various conditionals **/
	private $wc_vars = array(
		"is_product"          => FALSE,
		"is_shop"             => FALSE,
		"is_product_category" => FALSE,
		"is_product_tag"      => FALSE,
		"is_cart"             => FALSE,
		"is_checkout"         => FALSE,
		"is_account_page"     => FALSE,
		"is_ajax"             => FALSE,
		"is_page"             => FALSE,
	);

	/** Product custom settings **/
	public $tm_meta_cpf = array();

	/** Product custom settings options **/
	public $meta_fields = array(
		'exclude'                  => '',
		'price_override'           => '',
		'override_display'         => '',
		'override_final_total_box' => '',
		'override_enabled_roles'   => '',
		'override_disabled_roles'  => '',
	);

	/** Cache for all the extra options **/
	private $cpf = array();

	/** Holds the upload directory for the upload element **/
	public $upload_dir = "/extra_product_options/";
	/** Holds the upload files objects **/
	private $upload_object = array();

	/** Replacement name for Subscription sign up fee fields **/
	public $fee_name = "tmfee_";
	public $fee_name_class = "tmcp-sub-fee-field";

	/** Holds the total fee added by Subscription sign up fee fields **/
	public $tmfee = 0;

	/** Replacement name for cart fee fields **/
	public $cart_fee_name = "tmcartfee_";
	public $cart_fee_class = "tmcp-fee-field";

	/** Array of element types that get posted **/
	private $element_post_types = array();

	/** Holds builder element attributes **/
	private $tm_original_builder_elements = array();

	/** Holds modified builder element attributes **/
	public $tm_builder_elements = array();

	/** Inline styles **/
	public $inline_styles;
	public $inline_styles_head;

	/** Edit option in cart helper **/
	private $new_add_to_cart_key = FALSE;
	/** Holds the cart key when editing a product in the cart **/
	public $cart_edit_key = NULL;

	/* Containes current option features */
	public $current_option_features = array();

	/* Contains file to be defered */
	public $defered_files = array();

	/** Holds all of the plugin settings **/
	private $tm_plugin_settings = array();

	/** Prevent option duplication for bad coded themes **/
	private $tm_options_have_been_displayed = FALSE;
	private $tm_options_single_have_been_displayed = FALSE;
	private $tm_options_totals_have_been_displayed = FALSE;

	/** Enable/disable flag for outputing plugin specific classes to the post_class filter  **/
	private $tm_related_products_output = TRUE;

	/** Enable/disable flag for outputing plugin specific classes to the post_class filter  **/
	private $in_related_upsells = FALSE;

	/** Set of variables to ensure that the correct options are displayed on complex layouts **/
	private $epo_id = 0;
	private $epo_internal_counter = 0;
	private $epo_internal_counter_check = array();
	private $current_product_id_to_be_displayed = 0;
	private $current_product_id_to_be_displayed_check = array();

	/** Float direction for radio and checkboxes image replacements **/
	public $float_direction = "left";
	public $float_direction_opposite = "right";
	private $is_get_from_session = FALSE;

	/** Cart edit key**/
	public $cart_edit_key_var = 'tm_cart_item_key';
	public $cart_edit_key_var_alt = 'tc_cart_edit_key';

	/** Contains min/man product infomation **/
	public $product_minmax = array();

	/** Current free text replacement **/
	public $current_free_text = '';

	private $is_in_woocommerce_admin_order_page = FALSE;
	private $is_about_to_sent_email = FALSE;

	/** TM_Extra_Product_Options single instance **/
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

	/** Dummy function **/
	public function init() {
		return;
	}

	public function get_namespace(){
		return $this->_namespace;
	}

	/** Initializes the plugin **/
	public function __construct() {
		$this->inline_styles = '';
		$this->inline_styles_head = '';
		$this->is_bto = FALSE;
		$this->noactiondisplay = FALSE;

		$this->cart_edit_key_var = apply_filters( 'wc_epo_cart_edit_key_var', 'tm_cart_item_key' );
		$this->cart_edit_key_var_alt = apply_filters( 'wc_epo_cart_edit_key_var_alt', 'tc_cart_edit_key' );

		$this->cart_edit_key = isset( $_REQUEST[ $this->cart_edit_key_var ] )
			?
			$_REQUEST[ $this->cart_edit_key_var ]
			:
			(
			(isset( $_REQUEST[ $this->cart_edit_key_var_alt ] ))
				?
				$_REQUEST[ $this->cart_edit_key_var_alt ]
				:
				
				(isset( $_REQUEST[ 'update-composite' ] ))
					?
					$_REQUEST[ 'update-composite' ]
					:
					NULL



			);

		/** Add compatibility actions and filters with other plugins and themes **/
		TM_EPO_COMPATIBILITY()->init();

		add_action( 'plugins_loaded', array( $this, 'plugin_loaded' ), 3 );
		add_action( 'plugins_loaded', array( $this, 'tm_epo_add_elements' ), 12 );

	}

	public function plugin_loaded() {
		$this->tm_plugin_settings = TM_EPO_SETTINGS()->plugin_settings();
		$this->get_plugin_settings();
		$this->get_override_settings();
		$this->add_plugin_actions();
	}

	/** Gets all of the plugin settings **/
	public function get_plugin_settings() {
		foreach ( apply_filters( 'wc_epo_get_settings', $this->tm_plugin_settings ) as $key => $value ) {
			if ( is_array( $value ) ) {
				$method = $value[2];
				$classname = $value[1];
				if ( call_user_func( array( $classname, $method ) ) ) {
					$this->$key = get_option( $key );
					if ($this->$key===false){
						$this->$key = $value[0];
					}
				} else {
					$this->$key = $value[0];
				}
			} else {
				$this->$key = get_option( $key );
				if ( $this->$key === FALSE ) {
					$this->$key = $value;
				}
			}
		}

		if ( $this->tm_epo_options_placement == "custom" ) {
			$this->tm_epo_options_placement = $this->tm_epo_options_placement_custom_hook;
		}

		if ( $this->tm_epo_totals_box_placement == "custom" ) {
			$this->tm_epo_totals_box_placement = $this->tm_epo_totals_box_placement_custom_hook;
		}

		$this->upload_dir = $this->tm_epo_upload_folder;
		$this->upload_dir = str_replace( "/", "", $this->upload_dir );
		$this->upload_dir = sanitize_file_name( $this->upload_dir );
		$this->upload_dir = "/" . $this->upload_dir . "/";

	}

	/** Gets custom settings for the current product **/
	public function get_override_settings() {
		foreach ( $this->meta_fields as $key => $value ) {
			$this->tm_meta_cpf[ $key ] = $value;
		}
	}

	/** Add required actions and filters **/
	public function add_plugin_actions() {

		/** Initialize custom product settings **/
		if ( $this->is_enabled_shortcodes() && !$this->is_quick_view() ) {
			add_action( 'init', array( $this, 'init_settings_pre' ) );
		} else {
			if ( $this->is_quick_view() ) {
				add_action( 'init', array( $this, 'init_settings' ) );
			} else {
				add_action( 'template_redirect', array( $this, 'init_settings' ) );
			}
		}
		add_action( 'template_redirect', array( $this, 'init_vars' ), 1 );
		/** Add custom inline css **/
		add_action( 'template_redirect', array( $this, 'tm_variation_css_check' ), 9999 );

		/** Load js,css files **/
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ), 5 );
		add_action( 'woocommerce_tm_custom_price_fields_enqueue_scripts', array( $this, 'custom_frontend_scripts' ) );
		add_action( 'woocommerce_tm_epo_enqueue_scripts', array( $this, 'custom_frontend_scripts' ) );

		/** Custom optional dequeue_scripts **/
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_scripts' ), 9999 );

		/** Ensures the correct display order of options when multiple prodcuts are displayed **/
		add_action( 'woocommerce_before_single_product', array( $this, 'tm_woocommerce_before_single_product' ), 1 );
		add_action( 'woocommerce_after_single_product', array( $this, 'tm_woocommerce_after_single_product' ), 9999 );

		/** Change quantity value when editing a cart item **/
		add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'tm_woocommerce_before_add_to_cart_form' ), 1 );
		add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'tm_woocommerce_after_add_to_cart_form' ), 9999 );

		/** Display in frontend **/
		add_action( 'woocommerce_tm_epo', array( $this, 'frontend_display' ), 10, 3 );
		add_action( 'woocommerce_tm_epo_fields', array( $this, 'tm_epo_fields' ), 10, 4 );
		add_action( 'woocommerce_tm_epo_totals', array( $this, 'tm_epo_totals' ), 10, 3 );

		/** Compatibility for older plugin versions  **/
		add_action( 'woocommerce_tm_custom_price_fields', array( $this, 'frontend_display' ) );
		add_action( 'woocommerce_tm_custom_price_fields_only', array( $this, 'tm_epo_fields' ) );
		add_action( 'woocommerce_tm_custom_price_fields_totals', array( $this, 'tm_epo_totals' ) );

		/** Cart manipulation **/
		// Modifies the cart item
		add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 9999, 1 );
		// Load cart data on every page load
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 9999, 2 );
		// Gets cart item to display in the frontend
		add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 50, 2 );
		// Add item data to the cart
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 50, 3 );
		// Add meta to order
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7.0', '<' ) ) {
			add_action( 'woocommerce_add_order_item_meta', array( $this, 'order_item_meta' ), 50, 2 );
		} else {
			add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'order_line_item' ), 50, 3 );
		}
		// Validate upon adding to cart
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 50, 6 );
		// Gets saved option when using the order again function
		add_filter( 'woocommerce_order_again_cart_item_data', array( $this, 'order_again_cart_item_data' ), 50, 3 );
		// Alter the product thumbnail in cart
		add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'tm_woocommerce_cart_item_thumbnail' ), 50, 3 );
		// Alter the product thumbnail in order
		add_filter( 'woocommerce_admin_order_item_thumbnail', array( $this, 'tm_woocommerce_admin_order_item_thumbnail' ), 50, 3 );
		// Ensures correct price is shown on minicart
		add_action( 'woocommerce_before_mini_cart', array( $this, 'tm_recalculate_total' ) );

		// Cart edit key
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'cart_loaded_from_session_1' ), 0 );
		// Calculate totals on remove from cart/update
		add_action( 'woocommerce_update_cart_action_cart_updated', array( $this, 'tm_woocommerce_update_cart_action_cart_updated' ), 9999, 1 );

		/** Empty cart button **/
		if ( $this->tm_epo_clear_cart_button == "show" ) {
			add_action( 'woocommerce_cart_actions', array( $this, 'add_empty_cart_button' ) );
			// check for empty-cart get param to clear the cart
			add_action( 'init', array( $this, 'clear_cart' ) );
		}

		/** Force Select Options **/
		add_filter( 'woocommerce_add_to_cart_url', array( $this, 'add_to_cart_url' ), 50, 1 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'add_to_cart_url' ), 50, 1 );
		add_action( 'woocommerce_product_add_to_cart_text', array( $this, 'add_to_cart_text' ), 10, 1 );
		add_filter( 'woocommerce_cart_redirect_after_error', array( $this, 'woocommerce_cart_redirect_after_error' ), 50, 2 );

		/** Enable shortcodes for element labels **/
		add_filter( 'woocommerce_tm_epo_option_name', array( $this, 'tm_epo_option_name' ), 10, 5 );

		/** Hides uploaded file path **/
		add_filter( 'woocommerce_order_item_display_meta_value', array( $this, 'tm_order_item_display_meta_value' ), 10, 1 );

		/** Support for fee price types **/
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'tm_calculate_cart_fee' ) );

		/** Adds options to the array of items/products of an order **/
		add_filter( 'woocommerce_order_get_items', array( $this, 'tm_woocommerce_order_get_items' ), 10, 2 );
		/** WC 2.7x only **/
		add_filter( 'woocommerce_admin_order_item_types', array( $this, 'woocommerce_admin_order_item_types' ), 10, 2 );
		// helper to include options in the order items - used for payment gateways
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'woocommerce_checkout_order_processed' ) );

		/** Cart advanced template system **/
		// Override templates
		if ( apply_filters( 'tm_get_template', TRUE ) ) {
			add_filter( 'wc_get_template', array( $this, 'tm_wc_get_template' ), 10, 5 );
		}
		// Custom actions running for advanced template system
		add_action( 'tm_woocommerce_cart_after_row', array( $this, 'tm_woocommerce_cart_after_row' ), 10, 4 );
		add_action( 'tm_woocommerce_checkout_after_row', array( $this, 'tm_woocommerce_checkout_after_row' ), 10, 4 );

		/* Edit cart item */
		// Alters add to cart text when editing a product
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'tm_woocommerce_before_add_to_cart_button' ) );
		// Adds edit link on product title in cart
		add_filter( 'woocommerce_cart_item_name', array( $this, 'tm_woocommerce_cart_item_name' ), 50, 3 );
		// Alters the cart item key when editing a product
		add_action( 'woocommerce_add_to_cart', array( $this, 'tm_woocommerce_add_to_cart' ), 10, 6 );
		// Redirect to cart when updating information for a cart item
		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'tm_woocommerce_add_to_cart_redirect' ), 9999, 1 );
		// Remove product from cart when editing a product
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'tm_remove_previous_product_from_cart' ), 99999, 6 );

		/** Add custom class to product div used to initialize the plugin JavaScript **/
		add_filter( 'post_class', array( $this, 'tm_post_class' ) );
		//add_filter( 'woocommerce_related_products_args', array( $this, 'tm_woocommerce_related_products_args' ), 10, 1 );
		add_filter( 'woocommerce_related_products_columns', array( $this, 'tm_woocommerce_related_products_args' ), 10, 1 );
		add_action( 'woocommerce_before_single_product', array( $this, 'tm_enable_post_class' ), 1 );
		add_action( 'woocommerce_after_single_product', array( $this, 'tm_enable_post_class' ), 1 );
		add_action( 'woocommerce_upsells_orderby', array( $this, 'tm_woocommerce_related_products_args' ), 10, 1 );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'tm_woocommerce_after_single_product_summary' ), 99999 );

		/** Image filter **/
		add_filter( 'tm_image_url', array( $this, 'tm_image_url' ) );

		/** Alter product display price to include possible option pricing **/
		if ( $this->tm_epo_include_possible_option_pricing == "yes" ) {
			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7.0', '<' ) ) {
				add_filter( 'woocommerce_get_price', array( $this, 'tm_woocommerce_get_price' ), 1, 2 );
			} else {
				add_filter( 'woocommerce_product_get_price', array( $this, 'tm_woocommerce_get_price' ), 1, 2 );
			}
		}
		if ( $this->tm_epo_use_from_on_price == "yes" ) {
			add_filter( 'woocommerce_show_variation_price', array( $this, 'tm_woocommerce_show_variation_price' ), 50, 3 );
			if ( $this->tm_epo_include_possible_option_pricing == "no" ) {
				add_filter( 'woocommerce_get_variation_price_html', array( $this, 'tm_get_price_html' ), 1, 2 );
				add_filter( 'woocommerce_get_price_html', array( $this, 'tm_get_price_html' ), 1, 2 );
			}
		}

		/** Override the minimum characters of text fields globally **/
		add_filter( 'wc_epo_global_min_chars', array( $this, 'wc_epo_global_min_chars' ), 10, 1 );
		/** Override the maimum characters of text fields globally **/
		add_filter( 'wc_epo_global_max_chars', array( $this, 'wc_epo_global_max_chars' ), 10, 1 );

		/** Custom CSS/JS support **/
		add_action( 'wp_head', array( $this, 'print_extra_css_js' ), 99999 );

		/** Attach upload files to emails **/
		if ( $this->tm_epo_global_attach_uploaded_to_emails == "yes" ) {
			add_filter( 'woocommerce_email_attachments', array( $this, 'woocommerce_email_attachments' ), 10, 3 );
		}

		/* Disables persistent cart **/
		if ( $this->tm_epo_turn_off_persi_cart == "yes" ) {

			add_filter( 'get_user_metadata', array( $this, 'turn_off_persi_cart' ), 10, 3 );
			add_filter( 'update_user_metadata', array( $this, 'turn_off_persi_cart' ), 10, 3 );
			add_filter( 'add_user_metadata', array( $this, 'turn_off_persi_cart' ), 10, 3 );

		}

		add_filter( 'wc_tm_epo_ac_product_price', array( $this, 'wc_tm_epo_ac_product_price' ), 10, 5 );
		add_filter( 'wc_tm_epo_ac_subtotal_price', array( $this, 'wc_tm_epo_ac_product_price' ), 10, 5 );

		add_filter( 'woocommerce_available_variation', array( $this, 'woocommerce_available_variation' ), 10, 3 );

		if ( $this->tm_epo_global_no_upload_to_png === 'yes' ){
			add_filter( 'wc_epo_no_upload_to_png', '__return_false' );
		}

		if ( $this->tm_epo_disable_options_on_order_status === 'yes' ){
			$email_actions = apply_filters( 'woocommerce_email_actions', array(
				'woocommerce_order_status_pending_to_processing',
				'woocommerce_order_status_pending_to_completed',
				'woocommerce_order_status_processing_to_cancelled',
				'woocommerce_order_status_pending_to_failed',
				'woocommerce_order_status_pending_to_on-hold',
				'woocommerce_order_status_failed_to_processing',
				'woocommerce_order_status_failed_to_completed',
				'woocommerce_order_status_failed_to_on-hold',
				'woocommerce_order_status_on-hold_to_processing',
				'woocommerce_order_status_on-hold_to_cancelled',
				'woocommerce_order_status_on-hold_to_failed',
				'woocommerce_order_status_completed',
				'woocommerce_order_fully_refunded',
				'woocommerce_order_partially_refunded',
			) );

			foreach ( $email_actions as $action ) {
				add_action( $action, array( $this, 'change_is_about_to_sent_email' ) );
			}
		}

	}

	/** Helper to determine when the email is about to be sent  **/
	public function change_is_about_to_sent_email(){
		$this->is_about_to_sent_email = TRUE;

	}

	/** Advanced template product price fix for override price **/
	public function wc_tm_epo_ac_product_price($price, $cart_item_key, $cart_item, $_product, $product_id){
		$flag = FALSE;
		if ( $this->tm_epo_global_override_product_price == "yes" ){
			$flag = TRUE;
		}elseif ( $this->tm_epo_global_override_product_price == "" ){
			$tm_meta_cpf = tc_get_post_meta( $product_id, 'tm_meta_cpf', TRUE );
			if ( !is_array( $tm_meta_cpf ) ) {
				$tm_meta_cpf = array();
			}

			if (!empty($tm_meta_cpf['price_override'])){
				$flag = TRUE;
			}
		}

		if (isset($cart_item['tm_epo_options_prices']) && floatval( $cart_item['tm_epo_options_prices'])>0 ){
			$display_price = $price;

			if ($flag){
				$display_price = '';
			}
			return apply_filters('wc_epo_ac_override_price', $display_price, $price, $cart_item_key, $cart_item, $_product, $product_id);
		}

		return $price;

	}

	/**
	 * Disables persistent cart
	 *
	 * @param $value
	 * @param $id
	 * @param $key
	 * @return bool
	 */
	public function turn_off_persi_cart( $value, $id, $key ) {
		if ( $key == '_woocommerce_persistent_cart' ) {
			return FALSE;
		}

		return $value;
	}

	/** Attach upload files to emails
	 *
	 * @param $attachments
	 * @param $emailmethodid
	 * @param $order
	 * @return array
	 */
	public function woocommerce_email_attachments( $attachments, $emailmethodid, $order ) {
		if ( $order && is_callable( array( $order, "get_items" ) ) ) {

			$items = $order->get_items();
			if ( !is_array( $items ) ) {
				return $attachments;
			}

			$upload_dir = get_option( 'tm_epo_upload_folder' );
			$upload_dir = str_replace( "/", "", $upload_dir );
			$upload_dir = sanitize_file_name( $upload_dir );
			$upload_dir = "/" . $upload_dir . "/";
			$main_path = $upload_dir;
			$todir = '';
			$subdir = $main_path . $todir;
			$param = wp_upload_dir();
			if ( empty( $param['subdir'] ) ) {
				$base_url = $param['url'] . $main_path;
				$param['path'] = $param['path'] . $subdir;
				$param['url'] = $param['url'] . $subdir;
				$param['subdir'] = $subdir;
			} else {
				$param['path'] = str_replace( $param['subdir'], $subdir, $param['path'] );
				$param['url'] = str_replace( $param['subdir'], $subdir, $param['url'] );
				$param['subdir'] = str_replace( $param['subdir'], $subdir, $param['subdir'] );
				$base_url = str_replace( $param['subdir'], $main_path, $param['url'] );

			}
			foreach ( $items as $item_id => $item ) {

				$item_meta = function_exists( 'wc_get_order_item_meta' ) ? wc_get_order_item_meta( $item_id, '', FALSE ) : $order->get_item_meta( $item_id );

				$has_epo = is_array( $item_meta ) && isset( $item_meta['_tmcartepo_data'] ) && isset( $item_meta['_tmcartepo_data'][0] );

				if ( $has_epo ) {
					$epos = maybe_unserialize( $item_meta['_tmcartepo_data'][0] );
					if ( is_array( $epos ) ) {
						foreach ( $epos as $key => $epo ) {
							if ( $epo && is_array( $epo ) && isset( $epo['section'] ) ) {

								if ( isset( $epo['element'] ) && isset( $epo['element']['type'] ) && $epo['element']['type'] == "upload" ) {

									$attachments[] = $param['path'] . str_replace( $base_url, "", $epo['value'] );
								}

							}
						}
					}
				}
			}
		}

		return $attachments;
	}

	/** Custom CSS/JS support **/
	public function print_extra_css_js() {
		$before = PHP_EOL . '<!-- start EPO Custom CSS and JS -->' . PHP_EOL;
		$after = '<!-- end EPO Custom CSS and JS -->' . PHP_EOL;
		$before_css = $before . '<style type="text/css">' . PHP_EOL;
		$after_css = '</style>' . PHP_EOL . $after;
		$before_js = $before . '<script type="text/javascript">' . PHP_EOL;
		$after_js = '</script>' . PHP_EOL . $after;
		$css = $this->tm_epo_css_code;
		$js = $this->tm_epo_js_code;

		if ( !empty( $css ) ) {
			echo $before_css . $css . $after_css;
		}
		if ( !empty( $js ) ) {
			echo $before_js . $js . $after_js;
		}
	}

	/** Override the minimum characters of text fields globally **/
	public function wc_epo_global_min_chars( $min = "" ) {
		if ( $this->tm_epo_global_min_chars !== '' && $min === '' ) {
			$min = $this->tm_epo_global_min_chars;
		}

		return $min;
	}

	/** Override the maximum characters of text fields globally **/
	public function wc_epo_global_max_chars( $max = "" ) {
		if ( $this->tm_epo_global_max_chars !== '' && $max === '' ) {
			$max = $this->tm_epo_global_max_chars;
		}

		return $max;
	}

	/** Cart edit key **/
	public function cart_loaded_from_session_1() {

		$cart_contents = WC()->cart->cart_contents;

		if ( is_array( $cart_contents ) ) {
			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
				WC()->cart->cart_contents[ $cart_item_key ][ TM_EPO()->cart_edit_key_var ] = $cart_item_key;
			}
		}

	}

	/** Calculate totals on remove from cart/update **/
	public function tm_woocommerce_update_cart_action_cart_updated( $cart_updated = FALSE ) {

		//if ( is_user_logged_in() ) {
			$cart_contents = WC()->cart->cart_contents;
			if ( is_array( $cart_contents ) ) {
				foreach ( $cart_contents as $cart_item_key => $cart_item ) {
					if ( isset( $cart_item['tm_epo_options_prices'] ) ) {
						$cart_updated = TRUE;
					}
				}
			}
		//}

		return $cart_updated;

	}

	/** Initialize custom product settings **/
	public function init_settings_pre() {

		$postid = FALSE;
		if ( function_exists( 'ux_builder_is_iframe' ) && ux_builder_is_iframe() ) {
			if ( isset( $_GET['post_id'] ) ) {
				$postid = $_GET['post_id'];
			}
		} else {
			$url = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
			$postid = TM_EPO_HELPER()->get_url_to_postid( $url );
		}

		$this->postid_pre = $postid;
		$product = wc_get_product( $postid );

		$check1 = ($postid == 0);
		$check2 = ($product
			&& is_object( $product )
			&& property_exists( $product, 'post' )
			&& property_exists( $product->post, 'post_type' )
			&& (in_array( $product->post->post_type, array( 'product', 'product_variation' ) )));
		$check3 = ($product
			&& is_object( $product )
			&& property_exists( $product, 'post_type' )
			&& (in_array( $product->post_type, array( 'product', 'product_variation' ) )));

		if ( $check1 || $check2 || $check3 ) {
			add_action( 'template_redirect', array( $this, 'init_settings' ) );
		} else {
			$this->init_settings();
		}

	}

	public function init_vars() {
		$this->wc_vars = array(
			"is_product"          => is_product(),
			"is_shop"             => is_shop(),
			"is_product_category" => is_product_category(),
			"is_product_tag"      => is_product_tag(),
			"is_cart"             => is_cart(),
			"is_checkout"         => is_checkout(),
			"is_account_page"     => is_account_page(),
			"is_ajax"             => is_ajax(),
			"is_page"             => is_page(),
		);
	}

	/** Initialize custom product settings **/
	public function init_settings() {

		if ( is_admin() && !$this->is_quick_view() ) {
			return;
		}


		// Re populate options for WPML
		if ( TM_EPO_WPML()->is_active() ) {
			//todo:Find another place to re init settings for WPML
			$this->get_plugin_settings();
		}

		if ( class_exists( 'WOOCS' ) ) {
			global $WOOCS;
			remove_filter( 'woocommerce_order_amount_total', array( $WOOCS, 'woocommerce_order_amount_total' ), 999 );
		}

		$postMax = ini_get( 'post_max_size' );

		// post_max_size debug
		if ( empty( $_FILES )
			&& empty( $_POST )
			&& isset( $_SERVER['REQUEST_METHOD'] )
			&& strtolower( $_SERVER['REQUEST_METHOD'] ) == 'post'
			&& isset( $_SERVER['CONTENT_LENGTH'] )
			&& (float) $_SERVER['CONTENT_LENGTH'] > $postMax
		) {

			wc_add_notice( sprintf( __( 'Trying to upload files larger than %s is not allowed!', 'woocommerce-tm-extra-product-options' ), $postMax ), 'error' );

		}

		global $post, $product;
		$this->set_tm_meta();
		$this->init_settings_after();

	}

	/** Initialize custom product settings **/
	public function init_settings_after() {

		global $post, $product;
		// Check if the plugin is active for the user
		if ( $this->check_enable() ) {
			if ( ($this->is_enabled_shortcodes() || is_product() || $this->is_quick_view())
				&& ($this->tm_epo_display == 'normal' || $this->tm_meta_cpf['override_display'] == 'normal')
				&& $this->tm_meta_cpf['override_display'] != 'action'
			) {
				$this->noactiondisplay = TRUE;
				// Add options to the page
				$this->tm_epo_options_placement_hook_priority = floatval( $this->tm_epo_options_placement_hook_priority );
				if ( !is_numeric( $this->tm_epo_options_placement_hook_priority ) ) {
					$this->tm_epo_options_placement_hook_priority = 50;
				}
				$this->tm_epo_totals_box_placement_hook_priority = floatval( $this->tm_epo_totals_box_placement_hook_priority );
				if ( !is_numeric( $this->tm_epo_totals_box_placement_hook_priority ) ) {
					$this->tm_epo_totals_box_placement_hook_priority = 50;
				}

				add_action( $this->tm_epo_options_placement, array( $this, 'tm_epo_fields' ), $this->tm_epo_options_placement_hook_priority );
				add_action( $this->tm_epo_options_placement, array( $this, 'tm_add_inline_style' ), $this->tm_epo_options_placement_hook_priority + 99999 );
				add_action( $this->tm_epo_totals_box_placement, array( $this, 'tm_epo_totals' ), $this->tm_epo_totals_box_placement_hook_priority );
			}
		}

		if ( $this->tm_epo_enable_in_shop == "yes" && (is_shop() || is_product_category() || is_product_tag()) ) {
			add_action( 'woocommerce_after_shop_loop_item', array( $this, 'tm_woocommerce_after_shop_loop_item' ), 9 );
		}

		$this->current_free_text = esc_attr__( 'Free!', 'woocommerce' );
		if ( $this->tm_epo_remove_free_price_label == 'yes' && $this->tm_epo_include_possible_option_pricing == "no" ) {
			if ( $post || $this->postid_pre ) {

				if ( $post ) {
					$thiscpf = $this->get_product_tm_epos( $post->ID );
				}

				if ( is_product() && is_array( $thiscpf ) && (!empty( $thiscpf['global'] ) || !empty( $thiscpf['local'] )) ) {
					if ( $product &&
						(is_object( $product ) && !is_callable( array( $product, "get_price" ) )) ||
						(!is_object( $product ))
					) {
						$product = wc_get_product( $post->ID );
					}
					if ( $product &&
						is_object( $product ) && is_callable( array( $product, "get_price" ) )
					) {

						if ( !(float) $product->get_price() > 0 ) {
							if ( $this->tm_epo_replacement_free_price_text ) {
								$this->current_free_text = $this->tm_epo_replacement_free_price_text;
								add_filter( 'woocommerce_get_price_html', array( $this, 'get_price_html' ), 10, 2 );
							} else {
								if ( $this->tm_epo_use_from_on_price == "no" ) {
									$this->current_free_text = '';
									remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
								}
							}
						}

						add_filter( 'woocommerce_get_price_html', array( $this, 'related_get_price_html' ), 10, 2 );

					}
				} else {
					if ( is_shop() || is_product_category() || is_product_tag() ) {
						add_filter( 'woocommerce_get_price_html', array( $this, 'get_price_html_shop' ), 10, 2 );
					} elseif ( !is_product() && $this->is_enabled_shortcodes() ) {
						if ( $this->tm_epo_replacement_free_price_text ) {
							$this->current_free_text = $this->tm_epo_replacement_free_price_text;
							add_filter( 'woocommerce_get_price_html', array( $this, 'get_price_html' ), 10, 2 );
						} else {
							if ( $this->tm_epo_use_from_on_price == "no" ) {
								$this->current_free_text = '';
								remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
							}
							add_filter( 'woocommerce_get_price_html', array( $this, 'get_price_html' ), 10, 2 );
						}
					} elseif ( is_product() ) {
						add_filter( 'woocommerce_get_price_html', array( $this, 'related_get_price_html2' ), 10, 2 );
					}
				}
			} else {
				if ( $this->is_quick_view() ) {
					if ( $this->tm_epo_replacement_free_price_text ) {
						$this->current_free_text = $this->tm_epo_replacement_free_price_text;
						add_filter( 'woocommerce_get_price_html', array( $this, 'get_price_html' ), 10, 2 );
					} else {
						add_filter( 'woocommerce_get_price_html', array( $this, 'get_price_html' ), 10, 2 );
						if ( $this->tm_epo_use_from_on_price == "no" ) {
							$this->current_free_text = '';
							remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
						}
					}
				}
			}
		} elseif ( $this->tm_epo_replacement_free_price_text ) {
			$this->current_free_text = $this->tm_epo_replacement_free_price_text;
			add_filter( 'woocommerce_get_price_html', array( $this, 'get_price_html' ), 10, 2 );
		}

		if ( $this->tm_epo_use_from_on_price == "yes" && is_product() && $post ) {
			if ( $product &&
				(is_object( $product ) && !is_callable( array( $product, "get_price" ) )) ||
				(!is_object( $product ))
			) {
				$product = wc_get_product( $post->ID );
			}
			if ( $product && is_object( $product ) && is_callable( array( $product, "get_price" ) ) ) {
				$this->current_free_text = $this->tm_get_price_html( $product->get_price(), $product );
			}
		}

	}

	/** Add custom inline css **/
	public function tm_variation_css_check( $echo = 0, $product_id = 0 ) {

		if ( is_rtl() ) {
			$this->float_direction = "right";
			$this->float_direction_opposite = "left";
		}

		$post_id = get_the_ID();

		if ( $product_id && $product_id !== $post_id ) {
			$post_id = $product_id;
		}

		$has_epo = TM_EPO_API()->has_options( $post_id );

		if ( $has_epo !== FALSE && is_array( $has_epo ) && isset( $has_epo['variations'] ) == TRUE ) {
			if ( $product_id ) {
				$css_string = "#product-" . $product_id . " form .variations,.post-" . $product_id . " form .variations {display:none;}";
			} else {
				$css_string = "form .variations{display:none;}";
			}

			$this->inline_styles_head = $this->inline_styles_head . $css_string;
			if ( $echo ) {
				$this->tm_variation_css_check_do();
			} else {
				add_action( 'wp_head', array( $this, 'tm_variation_css_check_do' ) );
			}
		}

	}

	/** Print inline css **/
	public function tm_variation_css_check_do() {

		if ( !empty( $this->inline_styles_head ) ) {
			echo '<style type="text/css">';
			echo $this->inline_styles_head;
			echo '</style>';
		}

	}

	/** Adds additional builder elements from 3rd party plugins. **/
	public final function tm_epo_add_elements() {

		do_action( 'tm_epo_register_addons' );
		do_action( 'tm_epo_register_extra_multiple_choices' );

		$this->tm_original_builder_elements = TM_EPO_BUILDER()->get_elements();

		if ( is_array( $this->tm_original_builder_elements ) ) {
			foreach ( $this->tm_original_builder_elements as $key => $value ) {

				if ( $value["is_post"] == "post" ) {
					$this->element_post_types[] = $value["post_name_prefix"];
				}

				if ( $value["is_post"] == "post" || $value["is_post"] == "display" ) {
					$this->tm_builder_elements[ $value["post_name_prefix"] ] = $value;
				}

			}
		}

	}

	/** Load js,css files **/
	public function frontend_scripts() {

		global $product;
		if (
			((class_exists( 'WC_Quick_View' ) || $this->is_supported_quick_view()) && (is_shop() || is_product_category() || is_product_tag()))
			|| $this->is_enabled_shortcodes()
			|| is_product()
			|| is_cart()
			|| is_checkout()
			|| is_order_received_page()
			|| ($this->tm_epo_enable_in_shop == "yes" && (is_shop() || is_product_category() || is_product_tag()))
		) {

			do_action( 'wc_epo_enqueue_scripts_before' );

			$this->custom_frontend_scripts();

			do_action( 'wc_epo_enqueue_scripts_after' );

		} else {
			return;
		}

	}

	/** Custom optional dequeue_scripts **/
	public function dequeue_scripts() {

		if (
			((class_exists( 'WC_Quick_View' ) || $this->is_supported_quick_view()) && (is_shop() || is_product_category() || is_product_tag()))
			|| $this->is_enabled_shortcodes()
			|| is_product()
			|| is_cart()
			|| is_checkout()
			|| is_order_received_page()
			|| ($this->tm_epo_enable_in_shop == "yes" && (is_shop() || is_product_category() || is_product_tag()))
		) {

			do_action( 'wc_epo_dequeue_scripts' );

		}

	}

	/** Displays options in shop page **/
	public function tm_woocommerce_after_shop_loop_item() {

		$post_id = get_the_ID();
		$has_epo = TM_EPO_API()->has_options( $post_id );
		if ( TM_EPO_API()->is_valid_options( $has_epo ) ) {
			echo '<div class="tm-has-options"><form class="cart">';
			$this->frontend_display( $post_id, "tc_" . $post_id, FALSE );
			echo '</form></div>';
		}

	}

	/** Generate min/max prices for the $product **/
	public function add_product_tc_prices( $product = FALSE ) {

		if ( $product ) {
			$id = tc_get_id( $product );
			$epos = $this->get_product_tm_epos( $id );

			if ( is_array( $epos ) && (!empty( $epos['global'] ) || !empty( $epos['local'] )) ) {
				if ( !empty( $epos['price'] ) ) {

					$minmax = TM_EPO_HELPER()->sum_array_values( $epos, TRUE );

					if ( !isset( $minmax['min'] ) ) {
						$minmax['min'] = 0;
					}
					if ( !isset( $minmax['max'] ) ) {
						$minmax['max'] = 0;
					}
					$min = $minmax['min'];
					$max = $minmax['max'];
					$minmax['tc_min_price'] = $min;
					$minmax['tc_max_price'] = $max;

					$minmax['tc_min_variable'] = $min;
					$minmax['tc_max_variable'] = $max;

					$minmax['tc_min_max'] = TRUE;
					$this->product_minmax[ $id ] = array(
						'tc_min_price' => $min,
						'tc_max_price' => $max,

						'tc_min_variable' => $min,
						'tc_max_variable' => $max,

						'tc_min_max' => TRUE,
					);

					if ( is_array( $min ) && is_array( $max ) ) {
						$this->product_minmax[ $id ] = array(
							'tc_min_price'    => min( $min ),
							'tc_max_price'    => max( $max ),
							'tc_min_variable' => $min,
							'tc_max_variable' => $max,
							'tc_min_max'      => TRUE,
						);
						$minmax['tc_min_price'] = min( $min );
						$minmax['tc_max_price'] = max( $max );
						$minmax['tc_min_variable'] = $min;
						$minmax['tc_max_variable'] = $max;
					}

					return $minmax;
				}
			}

		}

		return FALSE;

	}

	/** Alter product display price to include possible option pricing **/
	public function tm_woocommerce_get_price( $price = 0, $product = FALSE ) {

		if ( $minmax = $this->add_product_tc_prices( $product ) ) {

			add_filter( 'woocommerce_get_price_html', array( $this, 'tm_get_price_html' ), 1, 2 );
			add_filter( 'woocommerce_get_variation_price_html', array( $this, 'tm_get_price_html' ), 1, 2 );

			if ( $price === '' ) {
				$price = 0;
				if ( !empty( $max ) ) {
					if ( is_callable( array( $product, 'get_composite_price' ) ) ) {
						$price = $product->get_composite_price( 'min' );
					}
				}
			}
		}

		return $price;

	}

	/** Alter product display price to include possible option pricing **/
	public function tm_woocommerce_show_variation_price( $show = TRUE, $product = FALSE, $variation = FALSE ) {

		if ( $product && $variation ) {
			$epos = $this->get_product_tm_epos( tc_get_id( $product ) );
			if ( is_array( $epos ) && (!empty( $epos['global'] ) || !empty( $epos['local'] )) ) {
				if ( !empty( $epos['price'] ) ) {
					$minmax = TM_EPO_HELPER()->sum_array_values( $epos );
					if ( !empty( $minmax['max'] ) ) {
						$show = TRUE;
					}
				}
			}
		}

		return $show;

	}

	/** Returns the product's active price **/
	public function tc_get_price( $product = FALSE ) {

		$tc_min_price = 0;
		$id = tc_get_id( $product );
		if ( isset( $this->product_minmax[ $id ] ) ) {
			$tc_min_price = $this->product_minmax[ $id ]['tc_min_price'];
		}
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7.0', '<' ) ) {
			return apply_filters( 'tc_woocommerce_get_price', (float) apply_filters( 'wc_epo_product_price', $product->get_price(), "", FALSE ) + (float) $tc_min_price, $product );
		} else {
			return apply_filters( 'tc_woocommerce_product_get_price', (float) apply_filters( 'wc_epo_product_price', $product->get_price(), "", FALSE ) + (float) $tc_min_price, $product );
		}

	}

	/** Returns the price including or excluding tax, based on the 'woocommerce_tax_display_shop' setting. **/
	public function tc_get_display_price( $product = FALSE, $price = '', $qty = 1 ) {

		if ( $price === '' ) {
			$price = $this->tc_get_price( $product );
		}

		$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
		$display_price = $tax_display_mode == 'incl' ? tc_get_price_including_tax( $product, array( 'qty' => $qty, 'price' => $price ) ) : tc_get_price_excluding_tax( $product, array( 'qty' => $qty, 'price' => $price ) );

		return $display_price;

	}

	/** Returns the product's regular price. **/
	public function tc_get_regular_price( $product = FALSE ) {

		$tc_min_price = 0;
		$id = tc_get_id( $product );
		if ( isset( $this->product_minmax[ $id ] ) ) {
			$tc_min_price = $this->product_minmax[ $id ]['tc_min_price'];
		}
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7.0', '<' ) ) {
			return apply_filters( 'tc_woocommerce_get_regular_price', (float) apply_filters( 'wc_epo_product_price', $product->get_regular_price(), "", FALSE ) + (float) $tc_min_price, $product );
		} else {
			return apply_filters( 'tc_woocommerce_product_get_regular_price', (float) apply_filters( 'wc_epo_product_price', $product->get_regular_price(), "", FALSE ) + (float) $tc_min_price, $product );
		}


	}

	/** Alter product display price to include possible option pricing **/
	public function tm_get_price_html( $price = '', $product = FALSE ) {

		$original_price = $price;
		$id = tc_get_id( $product );

		if ( !isset( $this->product_minmax[ $id ] ) ) {
			$this->product_minmax[ $id ] = $this->add_product_tc_prices( $product );
		}
		$tc_min_price = 0;
		$tc_max_price = 0;
		if ( isset( $this->product_minmax[ $id ] ) && isset( $this->product_minmax[ $id ]['tc_min_price'] ) && isset( $this->product_minmax[ $id ]['tc_max_price'] ) ) {
			$tc_min_price = $this->product_minmax[ $id ]['tc_min_price'];
			$tc_max_price = $this->product_minmax[ $id ]['tc_max_price'];
		}
		$type = tc_get_product_type( $product );

		$override_id = floatval( TM_EPO_WPML()->get_original_id( $id, 'product' ) );
		$tm_meta_cpf = tc_get_post_meta( $override_id, 'tm_meta_cpf', TRUE );

		$price_override = ($this->tm_epo_global_override_product_price == 'no')
						? 0
						: (($this->tm_epo_global_override_product_price == 'yes')
							? 1
							: !empty( $tm_meta_cpf['price_override'] ) ? 1 : 0);

		$use_from = ($this->tm_epo_use_from_on_price == "yes");
		$free_text = ($this->tm_epo_remove_free_price_label == 'yes')
			?
			($this->tm_epo_replacement_free_price_text)
				? $this->tm_epo_replacement_free_price_text
				: ''
			: esc_attr__( 'Free!', 'woocommerce' );
		if ( $type == 'variable' || $type == 'variable-subscription' ) {
			$prices = $product->get_variation_prices( TRUE );

			$min_price = current( $prices['price'] );
			$tc_min_variable = isset( $this->product_minmax[ $id ]['tc_min_variable'][ key( $prices['price'] ) ] )
				? $this->product_minmax[ $id ]['tc_min_variable'][ key( $prices['price'] ) ] :
				(isset( $this->product_minmax[ $id ]['tc_min_variable'] )
					? $this->product_minmax[ $id ]['tc_min_variable']
					: 0);
			if ( is_array( $tc_min_variable ) ) {
				$tc_min_variable = min( $tc_min_variable );
			}
			$min = floatval( apply_filters( 'wc_epo_options_min_price', $tc_min_variable, $product, $price ) );
			$min_price = $min_price + $min;

			if ( $price_override ){
				if ( !empty($min) ){
					$min_price = $min;	
				}else{
					$minmax_new = TM_EPO_HELPER()->sum_array_values( $this->get_product_tm_epos( $id ), TRUE, 'minall' );
					$min_price = $minmax_new['minall'];
				}
				
			}			

			$copy_prices = $prices['price'];
			foreach ( $copy_prices as $vkey => $vprice ) {
				$copy_prices[ $vkey ] = $vprice + floatval( apply_filters( 'wc_epo_options_max_price', isset( $this->product_minmax[ $id ]['tc_max_variable'][ $vkey ] ) ? $this->product_minmax[ $id ]['tc_max_variable'][ $vkey ] : 0, $product, $price ) );
			}
			asort( $copy_prices );
			$max_price = end( $copy_prices );
			$tc_max_variable = isset( $this->product_minmax[ $id ]['tc_max_variable'][ key( $prices['price'] ) ] )
				? $this->product_minmax[ $id ]['tc_max_variable'][ key( $prices['price'] ) ] :
				(isset( $this->product_minmax[ $id ]['tc_max_variable'] )
					? $this->product_minmax[ $id ]['tc_max_variable']
					: 0);
			if ( is_array( $tc_max_variable ) ) {
				$tc_max_variable = max( $tc_max_variable );
			}
			$max = floatval( apply_filters( 'wc_epo_options_max_price', $tc_max_variable, $product, $price ) );
			$original_max = $max_price;
			$max_price = $max_price + $max;
			
			if ( $price_override && empty($min) ){
				if ( $max > $original_max ){
					$max_price = $max;
				}else{
					$max_price = $original_max;
				}				
			}

			$price = $min_price !== $max_price
				? !$use_from
					? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), tc_price( $min_price ), tc_price( $max_price ) )
					: (function_exists( 'wc_get_price_html_from_text' ) ? wc_get_price_html_from_text() : $product->get_price_html_from_text()) . tc_price( $min_price )
				: tc_price( $min_price );
			$is_free = $min_price == 0 && $max_price == 0;

			if ( $product->is_on_sale() ) {
				$min_regular_price = current( $prices['regular_price'] ) + $min;
				$max_regular_price = end( $prices['regular_price'] ) + $max;
				$regular_price = $min_regular_price !== $max_regular_price
					? !$use_from
						? sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), tc_price( $min_regular_price ), tc_price( $max_regular_price ) )
						: (function_exists( 'wc_get_price_html_from_text' ) ? wc_get_price_html_from_text() : $product->get_price_html_from_text()) . tc_price( $min_regular_price )
					: tc_price( $min_regular_price );
				$regular_price = '<del>'.$regular_price.'</del>';
				/**$price = (!$use_from 
					? (function_exists( 'wc_format_price_range' ) 
						? wc_format_price_range( $regular_price, $price ) 
						: $product->get_price_html_from_to( $regular_price, $price )) 
					: $price) 
					. $product->get_price_suffix();*/
				$price = (!$use_from 
					? ($regular_price . ' ' .$price) 
					: $price) 
					. $product->get_price_suffix();


			} elseif ( $is_free ) {
				$price = apply_filters( 'woocommerce_variable_free_price_html', $free_text, $product );
			} else {
				$price = $price . $product->get_price_suffix();
			}
		} else {			

			$min = floatval( apply_filters( 'wc_epo_options_min_price', $this->product_minmax[ $id ]['tc_min_price'], $product, $price ) );
			$max = floatval( apply_filters( 'wc_epo_options_max_price', $this->product_minmax[ $id ]['tc_max_price'], $product, $price ) );

			if ( $price_override ){
				$new_min = $min - floatval( $product->get_price() );
				$min = $new_min;
				$this->product_minmax[ $id ]['tc_min_price'] = $min;
			}

			$display_price = $this->tc_get_display_price( $product );
			$display_regular_price = $this->tc_get_display_price( $product, $this->tc_get_regular_price( $product ) );

			if( $price_override && $min<=0){
				$display_price = $display_regular_price;
			}

			$price = '';
			if ( $this->tc_get_price( $product ) > 0 ) {

				if ( $product->is_on_sale() && $this->tc_get_regular_price( $product ) ) {
					if ( $use_from && ($max > 0 || $max > $min) ) {
						$price .= (function_exists( 'wc_get_price_html_from_text' ) ? wc_get_price_html_from_text() : $product->get_price_html_from_text()) . tc_price( $display_price );
					} else {
						$price .= $original_price;//(function_exists( 'wc_format_price_range' ) ? wc_format_price_range( $display_regular_price, $display_price ) : $product->get_price_html_from_to( $display_regular_price, $display_price ));
					}
					$price .= $product->get_price_suffix();

				} else {
					if ( $use_from && ($max > 0 || $max > $min) ) {
						$price .= (function_exists( 'wc_get_price_html_from_text' ) ? wc_get_price_html_from_text() : $product->get_price_html_from_text());
					}
					$price .= tc_price( $display_price ) . $product->get_price_suffix();

				}
			} elseif ( $this->tc_get_price( $product ) === '' ) {

				$price = apply_filters( 'woocommerce_empty_price_html', '', $product );

			} elseif ( $this->tc_get_price( $product ) == 0 ) {
				if ( $product->is_on_sale() && $this->tc_get_regular_price( $product ) ) {
					if ( $use_from && ($max > 0 || $max > $min) ) {
						$price .= (function_exists( 'wc_get_price_html_from_text' ) ? wc_get_price_html_from_text() : $product->get_price_html_from_text()) . tc_price( ($min > 0) ? $min : 0 );
					} else {

						$price .= $original_price;//(function_exists( 'wc_format_price_range' ) ? wc_format_price_range( $display_regular_price, __( 'Free!', 'woocommerce' ) ) : $product->get_price_html_from_to( $display_regular_price, __( 'Free!', 'woocommerce' ) ));

						$price = apply_filters( 'woocommerce_free_sale_price_html', $price, $product );
					}

				} else {
					if ( $use_from && ($max > 0 || $max > $min) ) {
						$price .= (function_exists( 'wc_get_price_html_from_text' ) ? wc_get_price_html_from_text() : $product->get_price_html_from_text()) . tc_price( ($min > 0) ? $min : 0 );
					} else {

						$price = '<span class="amount">' . $free_text . '</span>';

						$price = apply_filters( 'woocommerce_free_price_html', $price, $product );
					}

				}
			}
		}

		return apply_filters( 'wc_epo_get_price_html', $price, $product );

	}

	/** Ensures correct price is shown on minicart **/
	public function tm_recalculate_total() {

		WC()->cart->calculate_totals();

	}

	/** Image filter **/
	public function tm_image_url( $url = "" ) {

		// WP Rocket cdn
		if ( defined( 'WP_ROCKET_VERSION' ) && function_exists( 'get_rocket_cdn_cnames' ) && function_exists( 'get_rocket_cdn_url' ) ) {
			$zone = array( 'all', 'images' );
			if ( is_array( $url ) ) {
				foreach ( $url as $key => $value ) {
					$ext = pathinfo( $value, PATHINFO_EXTENSION );
					if ( is_admin() && $ext != 'php' ) {
						continue;
					}
					if ( $cnames = get_rocket_cdn_cnames( $zone ) ) {
						$url[ $key ] = get_rocket_cdn_url( $value, $zone );
					}
				}

			} else {
				$ext = pathinfo( $url, PATHINFO_EXTENSION );
				if ( is_admin() && $ext != 'php' ) {
					//skip
				} else {
					if ( $cnames = get_rocket_cdn_cnames( $zone ) ) {
						$url = get_rocket_cdn_url( $url, $zone );
					}
				}
			}

		}
		// SSL support
		if ( is_ssl() ) {
			$url = preg_replace( "/^http:/i", "https:", $url );
		}

		return $url;

	}

	/** Add custom class to product div **/
	public function tm_enable_post_class() {

		$this->tm_related_products_output = TRUE;

	}

	/** Add custom class to product div **/
	public function tm_disable_post_class() {

		$this->tm_related_products_output = FALSE;

	}

	/** Add custom class to product div **/
	public function tm_woocommerce_related_products_args( $args ) {

		$this->tm_disable_post_class();
		$this->in_related_upsells = TRUE;

		return $args;

	}

	public function tm_woocommerce_after_single_product_summary() {

		$this->in_related_upsells = FALSE;

	}

	/** Add custom class to product div used to initialize the plugin JavaScript **/
	public function tm_post_class( $classes = "" ) {

		$post_id = get_the_ID();

		if (
			// disable in admin interface
			is_admin() ||

			// disable if not in the product div
			!$this->tm_related_products_output ||

			// disable if not in a product page, shop or product archive page
			!(
				'product' == get_post_type( $post_id ) ||
				$this->wc_vars['is_product'] ||
				$this->wc_vars['is_shop'] ||
				$this->wc_vars['is_product_category'] ||
				$this->wc_vars['is_product_tag']
			) ||

			// disable if options are not visible in shop/archive pages
			((
					$this->wc_vars['is_shop'] ||
					$this->wc_vars['is_product_category'] ||
					$this->wc_vars['is_product_tag']
				)
				&&
				$this->tm_epo_enable_in_shop == "no"
			)

		) {
			return $classes;
		}

		// enabling this here will cause issues on certain Visual composer shortcodes.
		//global $post;

		if ( $post_id && ($this->wc_vars['is_product'] || 'product' == get_post_type( $post_id )) ) {

			$has_options = $this->get_product_tm_epos( $post_id );

			// Product has extra options
			if ( is_array( $has_options ) && (!empty( $has_options['global'] ) || !empty( $has_options['local'] )) ) {

				$classes[] = 'tm-has-options';

				// Product doens't have extra options but the final total box is enabled for all products
			} elseif ( $this->tm_epo_enable_final_total_box_all == "yes" ) {

				$classes[] = 'tm-no-options-pxq';

				// Search for composite products extra options
			} else {

				$terms = get_the_terms( $post_id, 'product_type' );
				$product_type = !empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

				if ( ($product_type == 'bto' || $product_type == 'composite')
					&& !(is_array( $has_options ) && (!empty( $has_options['global'] ) || !empty( $has_options['local'] )))
					&& $this->tm_epo_enable_final_total_box_all != "yes"
				) {

					// search components for options
					$product = wc_get_product( $post_id );
					if ( is_callable( array( $product, 'get_composite_data' ) ) ) {
						$composite_data = $product->get_composite_data();

						foreach ( $composite_data as $component_id => $component_data ) {

							$component_options = array();

							if ( class_exists( 'WC_CP_Component' ) && method_exists( 'WC_CP_Component', 'query_component_options' ) ) {
								$component_options = WC_CP_Component::query_component_options( $component_data );
							} elseif ( function_exists( 'WC_CP' ) ) {
								$component_options = WC_CP()->api->get_component_options( $component_data );
							} else {
								global $woocommerce_composite_products;
								if ( is_object( $woocommerce_composite_products ) && function_exists( 'WC_CP' ) ) {
									$component_options = WC_CP()->api->get_component_options( $component_data );
								} else {
									if ( isset( $component_data['assigned_ids'] ) && is_array( $component_data['assigned_ids'] ) ) {
										$component_options = $component_data['assigned_ids'];
									}
								}
							}

							foreach ( $component_options as $key => $pid ) {
								$has_options = $this->get_product_tm_epos( $pid );
								if ( is_array( $has_options ) && (!empty( $has_options['global'] ) || !empty( $has_options['local'] )) ) {
									$classes[] = 'tm-no-options-composite';

									return $classes;
								}
							}

						}
					}

				}

				$classes[] = 'tm-no-options';

			}
		}

		return $classes;

	}

	public function is_edit_mode() {

		return !empty( $this->cart_edit_key ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'tm-edit' );

	}

	/** Alters add to cart text when editing a product **/
	public function tm_woocommerce_before_add_to_cart_button() {

		if ( $this->is_edit_mode() ) {
			add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'tm_woocommerce_product_single_add_to_cart_text' ), 9999 );
			echo '<input type="hidden" name="' . $this->cart_edit_key_var_alt . '" value="' . esc_attr( $this->cart_edit_key ) . '" />';
		}

		$this->epo_id++;
		echo '<input type="hidden" class="tm-epo-counter" name="tm-epo-counter" value="' . esc_attr( $this->epo_id ) . '" />';

		global $product;
		$pid = tc_get_id( $product );
		if ( !empty( $pid ) ) {
			echo '<input type="hidden" class="tc-add-to-cart" name="tcaddtocart" value="' . esc_attr( $pid ) . '" />';
		}

	}

	/** Adds edit link on product title in cart **/
	public function tm_woocommerce_cart_item_name( $title = "", $cart_item = array(), $cart_item_key = "" ) {

		if ( apply_filters( 'wc_epo_no_edit_options', FALSE, $title, $cart_item, $cart_item_key ) ) {
			return $title;
		}
		if ( !isset( $cart_item['data'] ) || !isset( $cart_item['tmhasepo'] ) ) {
			return $title;
		}
		if ( apply_filters( 'wc_epo_override_edit_options', TRUE, $title, $cart_item, $cart_item_key ) ) {
			if ( !(is_cart() || is_checkout()) || isset( $cart_item['composite_item'] ) || isset( $cart_item['composite_data'] ) ) {
				return $title;
			}
			// Chained products cannot be edited
			if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['chained_item_of'] ) ) {
				return $title;
			}
			// Cannot function with TLS eDocBuidler
			if ( isset( $cart_item['eDocBuilderID'] ) ) {
				return $title;
			}
		}
		$product = $cart_item['data'];

		$link = $product->get_permalink( $cart_item );
		$link = add_query_arg(
			array(
				$this->cart_edit_key_var => $cart_item_key,
				'cart_item_key'          => $cart_item_key,
			)
			, $link );
		//wp_nonce_url escapes the url
		$link = wp_nonce_url( $link, 'tm-edit' );
		$title .= '<a href="' . $link . '" class="tm-cart-edit-options">' . ((!empty( $this->tm_epo_edit_options_text )) ? $this->tm_epo_edit_options_text : __( 'Edit options', 'woocommerce-tm-extra-product-options' )) . '</a>';

		return apply_filters( 'wc_epo_edit_options_link', $title, $cart_item, $cart_item_key );

	}

	/** Alters the cart item key when editing a product **/
	public function tm_woocommerce_add_to_cart( $cart_item_key = "", $product_id = "", $quantity = "", $variation_id = "", $variation = "", $cart_item_data = "" ) {

		if ( $this->cart_edit_key ) {
			$this->new_add_to_cart_key = $cart_item_key;
		} else {
			if ( is_array( $cart_item_data ) && isset( $cart_item_data['tmhasepo'] ) ) {

				$cart_contents = WC()->cart->cart_contents;

				if ( is_array( $cart_contents ) && isset( $cart_contents[ $cart_item_key ] ) && !empty( $cart_contents[ $cart_item_key ] ) && !isset( $cart_contents[ $cart_item_key ][ $this->cart_edit_key_var ] ) ) {
					WC()->cart->cart_contents[ $cart_item_key ][ $this->cart_edit_key_var ] = $cart_item_key;
				}

			}
		}

	}

	/** Redirect to cart when updating information for a cart item **/
	public function tm_woocommerce_add_to_cart_redirect( $url = "" ) {

		if ( empty( $_REQUEST['add-to-cart'] ) || !is_numeric( $_REQUEST['add-to-cart'] ) ) {
			return $url;
		}
		if ( $this->cart_edit_key ) {
			if ( !TM_EPO_HELPER()->is_ajax_request() ) {
				$url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : WC()->cart->get_cart_url();
			}
		}

		return $url;

	}

	/** Remove product from cart when editing a product **/
	public function tm_remove_previous_product_from_cart( $passed, $product_id, $qty, $variation_id = '', $variations = array(), $cart_item_data = array() ) {

		if ( $this->cart_edit_key ) {
			$cart_item_key = $this->cart_edit_key;
			if ( isset( $this->new_add_to_cart_key ) ) {
				if ( $this->new_add_to_cart_key == $cart_item_key && isset( $_POST['quantity'] ) ) {
					WC()->cart->set_quantity( $this->new_add_to_cart_key, $_POST['quantity'], TRUE );
				} else {
					WC()->cart->remove_cart_item( $cart_item_key );
					unset( WC()->cart->removed_cart_contents[ $cart_item_key ] );
				}
			}
		}

		return $passed;

	}

	/** Change quantity value when editing a cart item **/
	public function tm_woocommerce_quantity_input_args( $args = "", $product = "" ) {

		if ( $this->cart_edit_key ) {
			$cart_item_key = $this->cart_edit_key;
			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			if ( isset( $cart_item["quantity"] ) ) {
				$args["input_value"] = $cart_item["quantity"];
			}
		}

		return $args;

	}

	public function woocommerce_admin_order_item_types( $type ) {

		$this->is_in_woocommerce_admin_order_page = TRUE;

		return $type;

	}

	public function woocommerce_is_attribute_in_product_name( $is_in_name, $attribute, $name ) {

		return false;
	}

	public function woocommerce_order_items_table() {

		remove_filter( 'woocommerce_is_attribute_in_product_name', array( $this, 'woocommerce_is_attribute_in_product_name'), 10, 3 );

	}

	public function woocommerce_checkout_order_processed() {

		define( 'TM_CHECKOUT_ORDER_PROCESSED', 1);

	}

	/** Adds options to the array of items/products of an order **/
	public function tm_woocommerce_order_get_items( $items = array(), $order = FALSE ) {

		if ( apply_filters( 'wc_epo_no_order_get_items', false ) || 
			!is_array( $items ) ||
			defined( 'TM_IS_SUBSCRIPTIONS_RENEWAL' ) ||
			( defined( 'WOOCOMMERCE_CHECKOUT' ) && ! $this->is_about_to_sent_email && ! defined( 'TM_CHECKOUT_ORDER_PROCESSED' ) )||
			('yes' == $this->tm_epo_global_prevent_options_from_emails) ||
			(isset( $_POST["action"] ) && $_POST["action"] == "woocommerce_calc_line_taxes")
		) {
			return $items;
		}

		add_filter( 'woocommerce_is_attribute_in_product_name', array( $this, 'woocommerce_is_attribute_in_product_name'), 10, 3 );
		add_action( 'woocommerce_order_items_table', array( $this, 'woocommerce_order_items_table'), 10 );

		$order_currency = is_callable( array( $order, 'get_currency' ) ) ? $order->get_currency() : $order->get_order_currency();
		$currency_arg = array( 'currency' => $order_currency );
		$mt_prefix = $order_currency;

		$return_items = array();

		foreach ( $items as $item_id => $item ) {

			$item_meta = function_exists( 'wc_get_order_item_meta' ) ? wc_get_order_item_meta( $item_id, '', FALSE ) : $order->get_item_meta( $item_id );

			$has_epo = is_array( $item_meta ) && isset( $item_meta['_tmcartepo_data'] ) && isset( $item_meta['_tmcartepo_data'][0] );

			if ( $has_epo ) {
				$epos = maybe_unserialize( $item_meta['_tmcartepo_data'][0] );
				if ( !is_array( $epos ) ) {
					return $items;
				}
				$current_product_id = $item['product_id'];
				$original_product_id = floatval( TM_EPO_WPML()->get_original_id( $current_product_id, 'product' ) );
				if ( TM_EPO_WPML()->get_lang() == TM_EPO_WPML()->get_default_lang() && $original_product_id != $current_product_id ) {
					$current_product_id = $original_product_id;
				}
				$wpml_translation_by_id = TM_EPO_WPML()->get_wpml_translation_by_id( $current_product_id );
				$_unique_elements_added = array();
				$_items_to_add = array();
				foreach ( $epos as $key => $epo ) {
					if ( $epo && is_array( $epo ) && isset( $epo['section'] ) ) {
						if ( !isset( $epo['quantity'] ) ) {
							$epo['quantity'] = 1;
						}
						if ( $epo['quantity'] < 1 ) {
							$epo['quantity'] = 1;
						}
						if ( isset( $wpml_translation_by_id[ $epo['section'] ] ) ) {
							$epo['name'] = $wpml_translation_by_id[ $epo['section'] ];
						}
						if ( !empty( $epo['multiple'] ) && !empty( $epo['key'] ) ) {
							$pos = strrpos( $epo['key'], '_' );
							if ( $pos !== FALSE ) {
								if ( isset( $wpml_translation_by_id[ "options_" . $epo['section'] ] ) && is_array( $wpml_translation_by_id[ "options_" . $epo['section'] ] ) ) {
									$av = array_values( $wpml_translation_by_id[ "options_" . $epo['section'] ] );
									if ( isset( $av[ substr( $epo['key'], $pos + 1 ) ] ) ) {
										$epo['value'] = $av[ substr( $epo['key'], $pos + 1 ) ];
									}
								}
							}
						}
						$original_value = $epo['value'];
						$epo['value'] = $this->tm_order_item_display_meta_value( $epo['value'] );

						$new_currency = FALSE;
						if ( isset( $epo['price_per_currency'] ) ) {
							$_current_currency_prices = $epo['price_per_currency'];
							if ( $mt_prefix !== ''
								&& $_current_currency_prices !== ''
								&& is_array( $_current_currency_prices )
								&& isset( $_current_currency_prices[ $mt_prefix ] )
								&& $_current_currency_prices[ $mt_prefix ] != ''
							) {

								$new_currency = TRUE;
								$epo['price'] = $_current_currency_prices[ $mt_prefix ];

							}
						}
						if ( !$new_currency ) {
							$epo['price'] = apply_filters( 'wc_epo_get_current_currency_price', $epo['price'] );
						}

						if ( !empty( $epo['multiple_values'] ) ) {
							$display_value_array = explode( $epo['multiple_values'], $epo['value'] );
							$display_value = "";
							foreach ( $display_value_array as $d => $dv ) {
								$display_value .= '<span class="cpf-data-on-cart">' . $dv . '</span>';
							}
							$epo['value'] = $display_value;
						}

						$epovalue = '';
						if ( $this->tm_epo_hide_options_prices_in_cart == "normal" && !empty( $epo['price'] ) ) {
							$epovalue .= ' ' . ((!empty( $item_meta['tm_has_dpd'] ) || !empty( $item_meta['_tm_has_dpd'] )) ? '' : (wc_price( (float) $epo['price'] / (float) $epo['quantity'], $currency_arg )));
						}
						if ( $epo['quantity'] > 1 ) {
							$epovalue .= ' &times; ' . $epo['quantity'];
						}

						$epovalue = apply_filters( 'wc_epo_value_in_order', $epovalue, $epo['price'], $epo, $item, $item_id, $order);

						if ( $epovalue !== '' && !is_array( $epo['value'] ) && ((!empty( $epo['hidevalueinorder'] ) && $epo['hidevalueinorder'] == 'price') || empty( $epo['hidevalueinorder'] )) ) {
							$epo['value'] .= '<small>' . $epovalue . '</small>';
						}

						if ( is_array( $epo['value'] ) ) {
							$epo['value'] = array_map( array( TM_EPO_HELPER(), 'html_entity_decode' ), $epo['value'] );

						} else {
							$epo['value'] = TM_EPO_HELPER()->html_entity_decode( $epo['value'] );
						}

						if ( $this->tm_epo_strip_html_from_emails == "yes" ) {
							$epo['value'] = strip_tags( $epo['value'] );
						} else {
							if ( !empty( $epo['images'] ) && $this->tm_epo_show_image_replacement == "yes" ) {
								$display_value = '<span class="cpf-img-on-cart"><img alt="" class="attachment-shop_thumbnail wp-post-image epo-option-image" src="' .
									apply_filters( "tm_image_url", $epo['images'] ) . '" /></span>';
								$epo['value'] = $display_value . $epo['value'];
							}

							if ( $this->tm_epo_show_upload_image_replacement == "yes" && isset( $epo['element'] ) && isset( $epo['element']['type'] ) && $epo['element']['type'] == "upload" ) {
								$check = wp_check_filetype( $epo['value'] );
								if ( !empty( $check['ext'] ) ) {
									$image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );
									if ( in_array( $check['ext'], $image_exts ) ) {
										$display_value = '<span class="cpf-img-on-cart"><img alt="" class="attachment-shop_thumbnail wp-post-image epo-option-image epo-upload-image" src="' .
											apply_filters( "tm_image_url", $original_value ) . '" /><span class="cpf-data-on-cart"><a download href="' . esc_attr( $original_value ) . '">' . $epo['value'] . '</a></span></span>';
										$epo['value'] = $display_value;
									}
								}
							}

						}

						if ( isset($epo['element']) && $epo['element']['type']==='textarea' ){
							$epo_value = trim( $epo['value'] );

							$epo_value = str_replace( array( "\r\n", "\r" ), "\n", $epo_value );

							$epo_value = preg_replace( "/\n\n+/", "\n\n", $epo_value );

							$epo_value = array_map( 'wc_clean', explode( "\n", $epo_value ) );

							$epo_value = implode( "\n", $epo_value );

							$epo_value = wpautop( $epo_value );

							$epo['value'] = $epo_value;
						}
						if ( empty( $epo['hidelabelinorder'] ) || empty( $epo['hidevalueinorder'] ) ) {
							$_label = empty( $epo['hidelabelinorder'] )?$epo['name']:'';
							
							$_value = $epo['value'];

							if (isset($epo['hidevalueinorder'])){
								switch ($epo['hidevalueinorder']) {
									case 'price':
										$_value = $epo['price'];
										break;
									case 'hidden':
										$_value = '';
										break;
									default:
										$_value = $epo['value'];
										break;
								}
							}
							if ( isset( $_unique_elements_added[ $epo['section'] ] ) && isset( $_items_to_add[ $epo['section'] ] ) ) {
								$_ta = $_items_to_add[ $epo['section'] ];
								$_ta[ $_label ][] = $_value;
								$_items_to_add[ $epo['section'] ] = $_ta;
							} else {
								$_ta = array();
								$_ta[ $_label ] = array( 	$_value );
								$_items_to_add[ $epo['section'] ] = $_ta;
							}
							$_unique_elements_added[ $epo['section'] ] = $epo['section'];
						}
					}
				}

				$current_meta_key = 0;
				$added = FALSE;
				$current_meta = array();
				if ( $this->is_in_woocommerce_admin_order_page === FALSE && is_object( $item ) ) {
					$current_meta_key = 99999;

					$current_product = wc_get_product( $current_product_id ); 

					if ( tc_get_product_type( $current_product ) !== 'variable'){
						//$current_meta = unserialize( serialize( $item->get_meta_data() ) );
						//$current_meta = array();

						foreach ( $item->get_meta_data() as $item_meta ) {
							if ( isset( $item_meta->key, $item_meta->value, $item_meta->id ) ) {
								$current_meta[] = array(
									'key'   => $item_meta->key,
									'value' => $item_meta->value,
									'id'    => $item_meta->id
								);
							}
						}

						$cloned_item = clone $item;
						$cloned_item_meta_data = $cloned_item->get_meta_data();
						foreach ( $cloned_item_meta_data as $cloned_item_meta ) {
							$cloned_item->delete_meta_data( $cloned_item_meta->key );
						}
						$cloned_item->set_meta_data( $current_meta );
						$item = $cloned_item;

					}
				}

				foreach ( $_items_to_add as $uniquid => $element ) {
					foreach ( $element as $key => $value ) {
						if ( is_array( $value ) ) {
							$value = implode( ", ", $value );
						}
						if ( $value == '' ) {
							$value = ' ';
						}
						if ( is_array( $items[ $item_id ] ) ) {
							$item['item_meta'][ $key ][] = $value;
							$item['item_meta_array'][ count( $item['item_meta_array'] ) ] = (object) array( 'key' => $key, 'value' => $value );
						} elseif ( $current_meta_key > 0 && is_object( $items[ $item_id ] ) ) {

							$added = TRUE;
							$current_meta_key++;
							if ( !isset( $current_meta[ $current_meta_key ] ) ){
								$current_meta[] = (object) array(
									'id'    => $current_meta_key,
									'key'   => $key,
									'value' => $value,
								);
							}

						}

					}
				}

				if ( $current_meta_key > 0 && $added ) {
					$item->set_meta_data( $current_meta );
				}

			}
			$return_items[ $item_id ] = $item;
		}

		return $return_items;

	}

	/** Alter the product thumbnail in order **/
	public function tm_woocommerce_admin_order_item_thumbnail( $image = "", $item_id = "", $item = "" ) {

		$order = TM_EPO_HELPER()->tm_get_order_object();
		$item_meta = function_exists( 'wc_get_order_item_meta' ) ? wc_get_order_item_meta( $item_id, '', FALSE ) : $order->get_item_meta( $item_id );

		$_image = array();
		$_alt = array();

		$has_epo = is_array( $item_meta ) && isset( $item_meta['_tmcartepo_data'] ) && isset( $item_meta['_tmcartepo_data'][0] );
		$has_epo_fee = isset( $item_meta ) && isset( $item_meta['_tmcartfee_data'] ) && isset( $item_meta['_tmcartfee_data'][0] );

		if ( $has_epo ) {
			$epos = maybe_unserialize( $item_meta['_tmcartepo_data'][0] );
			if ( !is_array( $epos ) ) {
				return $image;
			}

			if ( $epos ) {
				foreach ( $epos as $key => $value ) {
					if ( !empty( $value['changes_product_image'] ) ) {
						if ( $value['changes_product_image'] == 'images' ) {
							if ( isset( $value['use_images'] ) && $value['use_images'] == 'images' && isset( $value['images'] ) ) {
								$_image[] = $value['images'];
								$_alt[] = $value['value'];
							}
						} elseif ( $value['changes_product_image'] == 'custom' ) {
							if ( isset( $value['imagesp'] ) ) {
								$_image[] = $value['imagesp'];
								$_alt[] = $value['value'];
							}
						}
					}
				}
			}
		}

		if ( count( $_image ) == 0 ) {
			if ( $has_epo_fee ) {
				$epos = maybe_unserialize( $item_meta['_tmcartfee_data'][0] );
				if ( !is_array( $epos ) ) {
					return $image;
				}

				if ( $epos ) {
					foreach ( $epos as $key => $value ) {
						if ( !empty( $value['changes_product_image'] ) ) {
							if ( $value['changes_product_image'] == 'images' ) {
								if ( isset( $value['use_images'] ) && $value['use_images'] == 'images' && isset( $value['images'] ) ) {
									$_image[] = $value['images'];
									$_alt[] = $value['value'];
								}
							} elseif ( $value['changes_product_image'] == 'custom' ) {
								if ( isset( $value['imagesp'] ) ) {
									$_image[] = $value['imagesp'];
									$_alt[] = $value['value'];
								}
							}
						}
					}
				}
			}
		}

		if ( count( $_image ) > 0 ) {
			$current = 0;
			for ( $i = count( $_image ); $i > 0; $i-- ) {
				if ( !empty( $_image[ $i ] ) ) {
					$current = $i;
				}
			}
			$size = 'shop_thumbnail';
			$dimensions = wc_get_image_size( $size );
			$image = apply_filters( 'tm_woocommerce_img',
				'<img src="' . apply_filters( 'tm_woocommerce_img_src', $_image[ $current ] )
				. '" alt="'
				. esc_attr( $_alt[ $current ] )
				. '" width="' . esc_attr( $dimensions['width'] )
				. '" class="woocommerce-placeholder wp-post-image" height="'
				. esc_attr( $dimensions['height'] )
				. '" />', $size, $dimensions );
		}

		return $image;

	}

	/** Alter the product thumbnail in cart **/
	public function tm_woocommerce_cart_item_thumbnail( $image = "", $cart_item = array(), $cart_item_key = "" ) {

		$_image = array();
		$_alt = array();
		if ( isset( $cart_item['tmcartepo'] ) && is_array( $cart_item['tmcartepo'] ) ) {
			foreach ( $cart_item['tmcartepo'] as $key => $value ) {
				if ( !empty( $value['changes_product_image'] ) ) {
					if ( $value['changes_product_image'] == 'images' ) {
						if ( isset( $value['use_images'] ) && $value['use_images'] == 'images' && isset( $value['images'] ) ) {
							$_image[] = $value['images'];
							$_alt[] = $value['value'];
						}
					} elseif ( $value['changes_product_image'] == 'custom' ) {
						if ( isset( $value['imagesp'] ) ) {
							$_image[] = $value['imagesp'];
							$_alt[] = $value['value'];
						}
					}
				}
			}
		}
		if ( count( $_image ) == 0 ) {
			if ( isset( $cart_item['tmcartfee'] ) && is_array( $cart_item['tmcartfee'] ) ) {
				foreach ( $cart_item['tmcartfee'] as $key => $value ) {
					if ( !empty( $value['changes_product_image'] ) ) {
						if ( $value['changes_product_image'] == 'images' ) {
							if ( isset( $value['use_images'] ) && $value['use_images'] == 'images' && isset( $value['images'] ) ) {
								$_image[] = $value['images'];
								$_alt[] = $value['value'];
							}
						} elseif ( $value['changes_product_image'] == 'custom' ) {
							if ( isset( $value['imagesp'] ) ) {
								$_image[] = $value['imagesp'];
								$_alt[] = $value['value'];
							}
						}
					}
				}
			}
		}
		if ( count( $_image ) > 0 ) {
			$current = 0;
			for ( $i = count( $_image ); $i > 0; $i-- ) {
				if ( !empty( $_image[ $i ] ) ) {
					$current = $i;
				}
			}
			if (!empty($_image[ $current ])){
				$size = 'shop_thumbnail';
				$dimensions = wc_get_image_size( $size );
				$image = apply_filters( 'tm_woocommerce_img',
					'<img src="' . apply_filters( 'tm_woocommerce_img_src', $_image[ $current ] )
					. '" alt="'
					. esc_attr( $_alt[ $current ] )
					. '" width="' . esc_attr( $dimensions['width'] )
					. '" class="tc-thumbnail woocommerce-placeholder wp-post-image" height="'
					. esc_attr( $dimensions['height'] )
					. '" />', $size, $dimensions );
			}
		}

		return $image;

	}

	/** Custom actions running for advanced template system **/
	public function tm_woocommerce_checkout_after_row( $cart_item_key = "", $cart_item = "", $_product = "", $product_id = "" ) {

		$out = '';
		$other_data = array();
		if ( $this->tm_epo_hide_options_in_cart == "normal" ) {
			$other_data = $this->get_item_data_array( array(), $cart_item );
		}
		$odd = 1;
		foreach ( $other_data as $key => $value ) {
			$zebra_class = "odd ";
			if ( !$odd ) {
				$zebra_class = "even ";
				$odd = 2;
			}
			$out .= '<tr class="tm-epo-cart-row ' . $zebra_class . esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ) . '">';

			$name = '<div class="tm-epo-cart-option-value tm-epo-cart-no-label">' . $value['tm_value'] . ' <strong class="product-quantity">' . sprintf( '&times; %s', $value['tm_quantity'] * $cart_item['quantity'] ) . '</strong>' . '</div>';
			if ( !empty( $value['tm_label'] ) ) {
				$name = '<div class="tm-epo-cart-option-label">' . $value['tm_label'] . ' <strong class="product-quantity">'
					. apply_filters( 'wc_tm_epo_ac_qty', sprintf( '&times; %s', $value['tm_quantity'] * $cart_item['quantity'] ), $cart_item_key, $cart_item, $value, $_product, $product_id )
					. '</strong>' . '</div>' . '<div class="tm-epo-cart-option-value">' . $value['tm_value'] . '</div>';
			}
			$out .= '<td class="product-name">' . $name . '</td>';

			$out .= '<td class="product-subtotal">' . $value['tm_total_price'] . '</td>';
			$out .= '</tr>';
			$odd--;
		}

		echo $out;

	}

	/** Custom actions running for advanced template system **/
	public function tm_woocommerce_cart_after_row( $cart_item_key = "", $cart_item = "", $_product = "", $product_id = "" ) {

		$out = '';
		$other_data = array();
		if ( $this->tm_epo_hide_options_in_cart == "normal" ) {
			$other_data = $this->get_item_data_array( array(), $cart_item );
		}
		$odd = 1;
		foreach ( $other_data as $key => $value ) {
			$zebra_class = "odd ";
			if ( !$odd ) {
				$zebra_class = "even ";
				$odd = 2;
			}
			$out .= '<tr class="tm-epo-cart-row ' . $zebra_class . esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ) . '">';
			$out .= '<td class="product-remove">&nbsp;</td>';
			$thumbnail = '&nbsp;';

			$out .= '<td class="product-thumbnail">' . $thumbnail . '</td>';
			$name = '<div class="tm-epo-cart-option-value tm-epo-cart-no-label">' . $value['tm_value'] . '</div>';
			if ( !empty( $value['tm_label'] ) ) {
				$name = '<div class="tm-epo-cart-option-label">' . $value['tm_label'] . '</div>' . '<div class="tm-epo-cart-option-value">' . $value['tm_value'] . '</div>';
			}
			$out .= '<td class="product-name">' . $name . '</td>';
			$out .= '<td class="product-price">' . $value['tm_price'] . '</td>';
			$out .= '<td class="product-quantity">' . apply_filters( 'wc_tm_epo_ac_qty', $value['tm_quantity'] * $cart_item['quantity'], $cart_item_key, $cart_item, $value, $_product, $product_id ) . '</td>';
			$out .= '<td class="product-subtotal">' . $value['tm_total_price'] . '</td>';
			$out .= '</tr>';
			$odd--;
		}
		if ( is_array( $other_data ) && count( $other_data ) > 0 ) {
			$out .= '<tr class="tm-epo-cart-row tm-epo-cart-row-total ' . esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ) . '">';
			$out .= '<td class="product-remove">&nbsp;</td>';
			$out .= '<td class="product-thumbnail">&nbsp;</td>';
			$out .= '<td class="product-name">&nbsp;</td>';
			$out .= '<td class="product-price">&nbsp;</td>';
			if ( $_product->is_sold_individually() ) {
				$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
			} else {
				$product_quantity = woocommerce_quantity_input( array(
					'input_name'  => "cart[{$cart_item_key}][qty]",
					'input_value' => $cart_item['quantity'],
					'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
					'min_value'   => '0',
				), $_product, FALSE );
			}
			$out .= '<td class="product-quantity">' . apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key ) . '</td>';
			$out .= '<td class="product-subtotal">' . apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) . '</td>';
			$out .= '</tr>';
		}

		echo $out;

	}

	/** Override templates for Cart advanced template system **/
	public function tm_wc_get_template( $located = "", $template_name = "", $args = "", $template_path = "", $default_path = "" ) {

		$templates = array( 'cart/cart-item-data.php' );
		if ( $this->tm_epo_cart_field_display == "advanced" ) {

			$templates = array_merge( $templates, array( 'cart/cart.php', 'checkout/review-order.php' ) );

		}

		if ( in_array( $template_name, $templates ) ) {
			$_located = wc_locate_template( $template_name, $this->_namespace, TM_EPO_TEMPLATE_PATH );
			if ( file_exists( $_located ) ) {
				$located = $_located;
			}
		}

		return $located;

	}

	/** Calculates the fee price **/
	public function cacl_fee_price( $price = "", $product_id = "", $element = FALSE, $attribute = "" ) {

		global $woocommerce;
		$product = wc_get_product( $product_id );
		if ( !$product ) {
			return $price;
		}

		$taxable = $product->is_taxable();
		$tax_class = tc_get_tax_class( $product );

		if ( $element ) {
			if ( isset( $element['include_tax_for_fee_price_type'] ) ) {
				if ( $element['include_tax_for_fee_price_type'] == "no" ) {
					$taxable = FALSE;
				}
				if ( $element['include_tax_for_fee_price_type'] == "yes" ) {
					$taxable = TRUE;
				}
			}
			if ( isset( $element['tax_class_for_fee_price_type'] ) ) {
				$tax_class = $element['tax_class_for_fee_price_type'];
			}
		}

		// Taxable
		if ( $taxable ) {

			if ( get_option( 'woocommerce_prices_include_tax' ) === 'yes' ) {
				$tax_rates = WC_Tax::get_base_tax_rates( $tax_class );
				$taxes = WC_Tax::calc_tax( $price, $tax_rates, TRUE );
				$price = WC_Tax::round( $price - array_sum( $taxes ) );
			}

			return $price;

		}

		return $price;

	}

	/** Support for fee price types **/
	public function tm_calculate_cart_fee( $cart_object = array() ) {

		if ( is_array( $cart_object->cart_contents ) ) {

			$to_currency = tc_get_woocommerce_currency();

			foreach ( $cart_object->cart_contents as $key => $value ) {
				$tax_class = tc_get_tax_class( $value["data"] );
				$get_tax_status = is_callable( array( $value["data"], 'get_tax_status' ) ) ? $value["data"]->get_tax_status() : $value["data"]->tax_status;
				if ( get_option( 'woocommerce_calc_taxes' ) == "yes" && $get_tax_status == "taxable" ) {
					$tax = TRUE;
				} else {
					$tax = FALSE;
				}

				$tmcartfee = isset( $value['tmcartfee'] ) ? $value['tmcartfee'] : FALSE;
				if ( $tmcartfee && is_array( $tmcartfee ) ) {
					foreach ( $tmcartfee as $cartfee ) {
						$new_price = $cartfee["price"];

						if ( isset( $cartfee['price_per_currency'] ) && isset( $cartfee['price_per_currency'][ $to_currency ] ) && $cartfee['price_per_currency'][ $to_currency ] != '' ) {
							$new_price = (float) wc_format_decimal( $cartfee['price_per_currency'][ $to_currency ], FALSE, TRUE );
						} else {
							$new_price = apply_filters( 'wc_epo_get_current_currency_price', apply_filters( 'woocommerce_tm_epo_price_on_cart', $new_price ) );
						}

						//if ( $cart_object->prices_include_tax ) {
						if ( wc_prices_include_tax() ){

							$this_element = FALSE;
							$builder = $this->get_product_tm_epos( tc_get_id( $value["data"] ) );
							foreach ( $builder['global'] as $priority => $priorities ) {
								foreach ( $priorities as $pid => $field ) {
									if ( isset( $field['sections'] ) ) {
										foreach ( $field['sections'] as $section_id => $section ) {
											if ( isset( $section['elements'] ) ) {
												foreach ( $section['elements'] as $element ) {
													if ( $element['uniqid'] == $cartfee['section'] ) {
														$this_element = $element;
														break 4;
													}
												}
											}
										}
									}
								}
							}
							$new_price = $this->cacl_fee_price( $new_price, tc_get_id( $value["data"] ), $this_element );
						}

						$new_name = $cartfee["name"];
						if ( empty( $new_name ) ) {
							$new_name = __( "Extra fee", 'woocommerce-tm-extra-product-options' );
						}
						$new_name .= apply_filters( 'wc_epo_fee_quantity_separator', " - " ) ;
						if (isset($cartfee["display"])){
							$new_name .= $cartfee["display"];
						}else{
							$new_name .= $cartfee["value"];
						}
						
						if ( floatval( $cartfee["quantity"] ) > 1 ) {
							$new_name .= apply_filters( 'wc_epo_fee_quantity_times', " &times; " ) . $cartfee["quantity"];
						}
						$canbadded = TRUE;

						$fees = array();
						if ( is_object( $cart_object ) && is_callable( array( $cart_object, "get_fees" ) ) ) {
							$fees = $cart_object->get_fees();
						}else{
							$fees = $cart_object->fees;
						}
						if ( is_array( $fees ) ) {
							foreach ( $fees as $fee ) {
								if ( $fee->id == sanitize_title( $new_name ) ) {
									if ( apply_filters( 'wc_epo_add_same_fee', TRUE, $new_price, $fee->amount) ) {
										$fee->amount = $fee->amount + (float) $new_price;
									}
									$canbadded = FALSE;
									break;
								}
							}
						}
						if ( $canbadded ) {

							$current_tax = $tax;
							$current_tax_class = $tax_class;
							if ( isset( $cartfee["include_tax_for_fee_price_type"] ) && $cartfee["include_tax_for_fee_price_type"] !== '' ) {
								if ( $cartfee["include_tax_for_fee_price_type"] == "yes" ) {
									$current_tax = TRUE;
								} elseif ( $cartfee["include_tax_for_fee_price_type"] == "no" ) {
									$current_tax = FALSE;
								}
							}
							if ( isset( $cartfee["tax_class_for_fee_price_type"] ) && $cartfee["tax_class_for_fee_price_type"] !== '' ) {
								$current_tax_class = $cartfee["tax_class_for_fee_price_type"];
								if ($cartfee["tax_class_for_fee_price_type"]==='@'){
									$current_tax_class = '';
								}
							}
							$cart_object->add_fee( $new_name, $new_price, $current_tax, $current_tax_class );
						}
					}
				}
			}
		}
		
	}

	/** Check if the plugin is active for the user **/
	public function check_enable() {

		$enable = FALSE;
		$enabled_roles = $this->tm_epo_roles_enabled;
		$disabled_roles = $this->tm_epo_roles_disabled;

		if ( isset( $this->tm_meta_cpf['override_enabled_roles'] ) && $this->tm_meta_cpf['override_enabled_roles'] !== "" ) {
			$enabled_roles = $this->tm_meta_cpf['override_enabled_roles'];
		}
		if ( isset( $this->tm_meta_cpf['override_disabled_roles'] ) && $this->tm_meta_cpf['override_disabled_roles'] !== "" ) {
			$disabled_roles = $this->tm_meta_cpf['override_disabled_roles'];
		}
		// Get all roles
		$current_user = wp_get_current_user();

		if ( !is_array( $enabled_roles ) ) {
			$enabled_roles = array( $enabled_roles );
		}
		if ( !is_array( $disabled_roles ) ) {
			$disabled_roles = array( $disabled_roles );
		}

		//Check if plugin is enabled for everyone
		foreach ( $enabled_roles as $key => $value ) {
			if ( $value == "@everyone" ) {
				$enable = TRUE;
			}
			if ( $value == "@loggedin" && is_user_logged_in() ) {
				$enable = TRUE;
			}
		}

		if ( $current_user instanceof WP_User ) {
			$roles = $current_user->roles;
			// Check if plugin is enabled for current user
			if ( is_array( $roles ) ) {

				foreach ( $roles as $key => $value ) {
					if ( in_array( $value, $enabled_roles ) ) {
						$enable = TRUE;
						break;
					}
				}

				foreach ( $roles as $key => $value ) {
					if ( in_array( $value, $disabled_roles ) ) {
						$enable = FALSE;
						break;
					}
				}

			}
		}

		return $enable;

	}

	/** Check if we are on a supported quickview mode **/
	public function is_quick_view() {

		return apply_filters( 'woocommerce_tm_quick_view', FALSE );

	}

	/** Check if the setting "Enable plugin for WooCommerce shortcodes" is active **/
	public function is_enabled_shortcodes() {

		return ($this->tm_epo_enable_shortcodes == "yes");

	}

	/**
	 * @param string $price
	 * @param string $type
	 * @return mixed|void
	 */
	public function tm_epo_price_filtered( $price = "", $type = "" ) {

		return apply_filters( 'wc_epo_get_current_currency_price', $price, $type );

	}

	/** For hiding uploaded file path **/
	public function tm_order_item_display_meta_value( $value = "", $override = 0 ) {

		$original_value = $value;

		if ( is_array( $value ) ) {
			$new_value = array();
			foreach ( $value as $k => $v ) {
				if ( is_array( $v ) ) {
					foreach ( $v as $k2 => $v2 ) {
						$original_value = $v2;
						$found = (strpos( $v2, $this->upload_dir ) !== FALSE);
						if ( ($found && empty( $override )) || !empty( $override ) ) {
							if ( $this->tm_epo_hide_upload_file_path != 'no' && filter_var( filter_var( $v2, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH ), FILTER_VALIDATE_URL ) ) {
								$v2 = mb_basename( $v2 );
							}
						}
						if ( !empty( $override ) ) {
							$v2 = '<a href="' . $original_value . '">' . $v2 . '</a>';
						}
						$v[ $k2 ] = $v2;
					}
				} else {

					$original_value = $v;
					$found = (strpos( $v, $this->upload_dir ) !== FALSE);
					if ( ($found && empty( $override )) || !empty( $override ) ) {
						if ( $this->tm_epo_hide_upload_file_path != 'no' && filter_var( filter_var( $v, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH ), FILTER_VALIDATE_URL ) ) {
							$v = mb_basename( $v );
						}
					}
					if ( !empty( $override ) ) {
						$v = '<a href="' . $original_value . '">' . $v . '</a>';
					}

				}
				$new_value[ $k ] = $v;
			}
			$value = $new_value;
		} else {
			$found = (strpos( $value, $this->upload_dir ) !== FALSE);
			if ( ($found && empty( $override )) || !empty( $override ) ) {
				if ( $this->tm_epo_hide_upload_file_path != 'no' && filter_var( filter_var( $value, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH ), FILTER_VALIDATE_URL ) ) {
					$value = mb_basename( $value );
				}
			}
			if ( !empty( $override ) ) {
				$value = '<a href="' . $original_value . '">' . $value . '</a>';
			}
		}
		if ( is_array( $value ) ) {
			$value = implode( ",", $value );
		}

		return $value;

	}

	/** Enable shortcodes for labels **/
	public function tm_epo_option_name( $label = "", $args = NULL, $counter = NULL, $value = NULL, $vlabel = NULL ) {

		if ( $this->tm_epo_show_price_inside_option == 'yes' &&
			$value !== NULL &&
			$vlabel !== NULL &&
			isset( $args['rules_type'] ) &&
			isset( $args['rules_type'][ $value ] ) &&
			isset( $args['rules_type'][ $value ][0] ) &&
			empty( $args['rules_type'][ $value ][0] )
		) {
			$display_price = (isset( $args['rules_filtered'][ $value ][0] )) ? $args['rules_filtered'][ $value ][0] : '';

			//if ( $display_price !== '' || $this->tm_epo_auto_hide_price_if_zero == 'no' ) {
			if ( ($this->tm_epo_auto_hide_price_if_zero == "yes" && !empty($display_price)) || ($this->tm_epo_auto_hide_price_if_zero != "yes" && $display_price !== '' ) ) {
				$symbol = '';
				if ( $this->tm_epo_global_price_sign == '' ) {
					$symbol = apply_filters( 'wc_epo_price_in_dropdown_plus_sign', "+" );
				}
				if ( floatval( $display_price ) < 0 ) {
					$symbol = apply_filters( 'wc_epo_price_in_dropdown_minus_sign', "-" );
				}
				$display_price = apply_filters( 'wc_epo_price_in_dropdown', ' (' . $symbol . wc_price( abs( $display_price ) ) . ')', $display_price );


			}
			$label .= $display_price;
		}

		return do_shortcode( $label );

	}

	/** Alters the Free label html **/
	public function get_price_html( $price = "", $product = "" ) {

		if ( $product && is_object( $product ) && is_callable( array( $product, "get_price" ) ) ) {
			if ( (float) $product->get_price() > 0 ) {
				return $price;
			} else {
				return sprintf( $this->tm_epo_replacement_free_price_text, $price );
			}
		} else {
			return sprintf( $this->tm_epo_replacement_free_price_text, $price );
		}

	}

	/** Fix for related products when replacing free label **/
	public function related_get_price_html( $price = "", $product = "" ) {

		if ( $product && is_object( $product ) && is_callable( array( $product, "get_price" ) ) ) {
			if ( (float) $product->get_price() > 0 ) {
				return $price;
			} else {
				if ( $this->tm_epo_replacement_free_price_text ) {
					return sprintf( $this->tm_epo_replacement_free_price_text, $price );
				} else {
					$price = '';
				}
			}
		} else {
			if ( $this->tm_epo_replacement_free_price_text ) {
				return sprintf( $this->tm_epo_replacement_free_price_text, $price );
			} else {
				$price = '';
			}
		}

		return $price;

	}

	/** Fix for related products when replacing free label **/
	public function related_get_price_html2( $price = "", $product = "" ) {

		if ( $product && is_object( $product ) && is_callable( array( $product, "get_price" ) ) ) {

			if ( (float) $product->get_price() > 0 ) {
				return $price;
			} else {

				$thiscpf = $this->get_product_tm_epos( tc_get_id( $product ) );

				if ( is_array( $thiscpf ) && (!empty( $thiscpf['global'] ) || !empty( $thiscpf['local'] )) ) {
					if ( $this->tm_epo_replacement_free_price_text ) {
						return sprintf( $this->tm_epo_replacement_free_price_text, $price );
					} else {
						$price = '';
					}
				}
			}
		}

		return $price;

	}

	/** Free label text replacement **/
	public function get_price_html_shop( $price = "", $product = "" ) {

		if ( $product &&
			is_object( $product ) && is_callable( array( $product, "get_price" ) )
			&& !(float) $product->get_price() > 0
		) {

			if ( $this->tm_epo_replacement_free_price_text ) {
				$price = sprintf( $this->tm_epo_replacement_free_price_text, $price );
			} else {
				$price = '';
			}
		}

		return $price;

	}

	/** Replaces add to cart text when the force select setting is enabled **/
	public function add_to_cart_text( $text = "" ) {

		global $product;

		if ( is_product() && !$this->in_related_upsells ) {
			return $text;
		}
		if ( $this->tm_epo_enable_in_shop == "no"
			&& $this->tm_epo_force_select_options == "display"
			&& is_object( $product )
			&& property_exists( $product, 'id' )
		) {
			$has_epo = TM_EPO_API()->has_options( tc_get_id( $product ) );
			if ( TM_EPO_API()->is_valid_options( $has_epo ) ) {
				$text = (!empty( $this->tm_epo_force_select_text )) ? $this->tm_epo_force_select_text : __( 'Select options', 'woocommerce-tm-extra-product-options' );
			}
		}
		if ( $this->tm_epo_enable_in_shop == "yes" && !$this->in_related_upsells ) {
			$text = esc_attr__( 'Add to cart', 'woocommerce' );
		}

		return $text;

	}

	/** Prevenets ajax add to cart when product has extra options and the force select setting is enabled **/
	public function add_to_cart_url( $url = "" ) {

		global $product;

		if ( !is_product()
			&& $this->tm_epo_force_select_options == "display"
			&& is_object( $product )
			&& property_exists( $product, 'id' )
		) {
			$has_epo = TM_EPO_API()->has_options( tc_get_id( $product ) );
			if ( TM_EPO_API()->is_valid_options( $has_epo ) ) {
				$url = get_permalink( tc_get_id( $product ) );
			}
		}

		return $url;

	}

	public function woocommerce_cart_redirect_after_error( $url = "", $product_id="" ) {

		$product = wc_get_product($product_id);
		
		if ( $this->tm_epo_force_select_options == "display"
			&& is_object( $product )
			&& property_exists( $product, 'id' )
		) {
			$has_epo = TM_EPO_API()->has_options( tc_get_id( $product ) );
			if ( TM_EPO_API()->is_valid_options( $has_epo ) ) {
				$url = get_permalink( tc_get_id( $product ) );
			}
		}

		return $url;

	}

	/** Empties the cart **/
	public function tm_empty_cart() {

		if ( !isset( WC()->cart ) || WC()->cart == '' ) {
			WC()->cart = new WC_Cart();
		}
		WC()->cart->empty_cart( TRUE );

	}

	/** Empties the cart **/
	public function clear_cart() {

		if ( isset( $_POST['tm_empty_cart'] ) ) {
			$this->tm_empty_cart();
		}

	}

	/** Adds the Empty cart button **/
	public function add_empty_cart_button() {
		//todo:move this to a template
		$text = (!empty( $this->tm_epo_empty_cart_text )) ? $this->tm_epo_empty_cart_text : __( 'Empty cart', 'woocommerce-tm-extra-product-options' );
		echo '<input type="submit" class="tm-clear-cart-button button" name="tm_empty_cart" value="' . $text . '" />';

	}

	/** Sets current product settings **/
	public function set_tm_meta( $override_id = 0 ) {

		if ( empty( $override_id ) ) {
			if ( isset( $_REQUEST['add-to-cart'] ) ) {
				$override_id = $_REQUEST['add-to-cart'];
			} else {
				global $post;
				if ( !is_null( $post ) && property_exists( $post, 'ID' ) && property_exists( $post, 'post_type' ) ) {
					if ( $post->post_type != "product" ) {
						return;
					}
					$override_id = $post->ID;
				}
			}
		}
		if ( empty( $override_id ) ) {
			return;
		}

		// translated products inherit original product meta overrides
		$override_id = floatval( TM_EPO_WPML()->get_original_id( $override_id, 'product' ) );

		$this->tm_meta_cpf = tc_get_post_meta( $override_id, 'tm_meta_cpf', TRUE );
		if ( !is_array( $this->tm_meta_cpf ) ) {
			$this->tm_meta_cpf = array();
		}
		foreach ( $this->meta_fields as $key => $value ) {
			$this->tm_meta_cpf[ $key ] = isset( $this->tm_meta_cpf[ $key ] ) ? $this->tm_meta_cpf[ $key ] : $value;
		}
		$this->tm_meta_cpf['metainit'] = 1;

	}

	/**
	 * @param $tmcp
	 * @return string
	 */
	public function get_element_price_type( $tmcp ) {
		$price_type = "";
		$key = isset( $tmcp['key'] ) ? $tmcp['key'] : 0;

		if ( !isset( $tmcp['element']['rules_type'][ $key ] ) ) {// field price rule
			if ( isset( $tmcp['element']['rules_type'][0][0] ) ) {// general rule
				$price_type = $tmcp['element']['rules_type'][0][0];
			}
		} else {
			if ( isset( $tmcp['element']['rules_type'][ $key ][0] ) ) {// general field variation rule
				$price_type = $tmcp['element']['rules_type'][ $key ][0];
			} elseif ( isset( $tmcp['element']['rules_type'][0][0] ) ) {// general rule
				$price_type = $tmcp['element']['rules_type'][0][0];
			}
		}

		return $price_type;
	}

	/** Modifies the cart item. **/
	public function add_cart_item( $cart_item = array() ) {
		/*
		* The following logic ensures that the correct price is being calculated
		* when currency or product price is being changed from various
		* 3rd part plugins.
		*/
		$cart_item['tm_epo_product_original_price'] = apply_filters( 'wc_epo_add_cart_item_original_price', $cart_item['data']->get_price(), $cart_item );

		$cart_item['tm_epo_options_prices'] = 0;
		$cart_item['tm_epo_product_price_with_options'] = $cart_item['tm_epo_product_original_price'];

		if ( !empty( $cart_item['tmcartepo'] ) ) {

			$tmcp_prices = 0;
			$tmcp_static_prices = 0;
			$tmcp_variable_prices = 0; // percentcurrenttotal
			$tmcp_variable_prices2 = 0; // percent

			$to_currency = tc_get_woocommerce_currency();
			if ( is_array( $cart_item['tmcartepo'] ) ) {
				foreach ( $cart_item['tmcartepo'] as $tmcp ) {
					if ( isset( $tmcp['subscription_fees'] ) ) {
						continue;
					}
					$_price_type = $this->get_element_price_type( $tmcp );

					if ( isset( $tmcp['price_per_currency'] ) && isset( $tmcp['price_per_currency'][ $to_currency ] ) && $tmcp['price_per_currency'][ $to_currency ] != '' ) {
						$tmcp['price'] = apply_filters( 'woocommerce_tm_epo_price_per_currency_diff', (float) wc_format_decimal( $tmcp['price_per_currency'][ $to_currency ], FALSE, TRUE ), $to_currency );
						$tmcp_prices += $tmcp['price'];
						if ( $_price_type == "percentcurrenttotal" ) {
							$tmcp_variable_prices += $tmcp['price'];
						} elseif ( $_price_type == "percent" ) {
							$tmcp_variable_prices2 += $tmcp['price'];
						} else {
							$tmcp_static_prices += $tmcp['price'];
						}
					} else {
						$tmcp['price'] = (float) wc_format_decimal( $tmcp['price'], FALSE, TRUE );
						$tmcp_prices += apply_filters( 'woocommerce_tm_epo_price_add_on_cart', $tmcp['price'], $_price_type );
						if ( $_price_type == "percentcurrenttotal" ) {
							$tmcp_variable_prices += $tmcp['price'];
						} elseif ( $_price_type == "percent" ) {
							$tmcp_variable_prices2 += $tmcp['price'];
						} else {
							$tmcp_static_prices += apply_filters( 'woocommerce_tm_epo_price_add_on_cart', $tmcp['price'], $_price_type );
						}
					}
				}
			}

			$cart_item['tm_epo_options_static_prices'] = $tmcp_static_prices;

			if ( !empty( $cart_item['tmpost_data'] ) && tc_get_product_type( $cart_item['data'] ) !== "composite" ) {
				$post_data = $cart_item['tmpost_data'];
				if ( isset( $cart_item['tm_epo_options_static_prices'] ) ) {
					$post_data['tm_epo_options_static_prices'] = $cart_item['tm_epo_options_static_prices'];
				}
				// todo:check for a better alternative
				if (!isset($post_data['cpf_product_price'])){
					$post_data['cpf_product_price'] = $cart_item['tm_epo_product_original_price'];
				}
				$post_data['cpf_product_price'] = apply_filters( 'wc_epo_add_cart_item_original_price', $post_data['cpf_product_price'], $cart_item );

				$cart_item = $this->repopulatecart( $cart_item, tc_get_id( $cart_item['data'] ), $post_data );
				if ( $cart_item === FALSE ) {
					return array();
				}
				$cart_item = apply_filters( 'tm_cart_contents', $cart_item, array() );
			}

			if ( is_array( $cart_item['tmcartepo'] ) ) {
				$tmcp_variable_prices = 0;
				$tmcp_variable_prices2 = 0;
				foreach ( $cart_item['tmcartepo'] as $tmcp ) {
					if ( isset( $tmcp['subscription_fees'] ) ) {
						continue;
					}
					$_price_type = $this->get_element_price_type( $tmcp );

					if ( $_price_type == "percentcurrenttotal" ) {
						$tmcp_variable_prices += $tmcp['price'];
					}
					if ( $_price_type == "percent" ) {
						$tmcp_variable_prices2 += $tmcp['price'];
					}
				}
			}

			$tmcp_prices = apply_filters( 'wc_epo_cart_options_prices', $tmcp_static_prices + $tmcp_variable_prices + $tmcp_variable_prices2, $cart_item );

			$cart_item['tm_epo_options_prices'] = $tmcp_prices;

			$price1 = wc_format_decimal( apply_filters( 'wc_epo_option_price_correction', $tmcp_prices, $cart_item ) );
			$price2 = (float) wc_format_decimal(
				apply_filters( 'wc_epo_product_price_correction',
					wc_format_decimal( $cart_item['tm_epo_product_original_price'] ),
					$cart_item ) )
				+ (float) $price1;

			$price1 = wc_format_decimal( apply_filters( 'wc_epo_add_cart_item_calculated_price1', $price1, $cart_item ) );
			$price2 = wc_format_decimal( apply_filters( 'wc_epo_add_cart_item_calculated_price2', $price2, $cart_item ) );

			do_action( 'wc_epo_currency_actions', $price1, $price2, $cart_item );

			if ( apply_filters( 'wc_epo_adjust_price', TRUE, $cart_item ) ) {
				if ( !empty( $cart_item['epo_price_override'] ) && $tmcp_prices > 0 ) {
					$cart_item['data']->set_price( $price1 );
					$cart_item = apply_filters( 'wc_epo_cart_set_price', $cart_item, $price1 );
				} else {
					if ( ! empty( $price1 ) ){
						$cart_item['data']->set_price( $price2 );	
					}
					$cart_item = apply_filters( 'wc_epo_cart_set_price', $cart_item, $price2 );
				}
			}
			$cart_item['tm_epo_product_price_with_options'] = $cart_item['data']->get_price();

		}

		if ( floatval( apply_filters( 'tm_epo_cart_options_prices', $cart_item['tm_epo_product_price_with_options'], $cart_item ) ) < 0 ) {
			if ( $this->tm_epo_no_negative_priced_products == "yes" ) {
				throw new Exception( __( "You cannot add negative priced products to the cart.", 'woocommerce-tm-extra-product-options' ) );
			}
		}

		if ( floatval( apply_filters( 'tm_epo_no_zero_priced_products', $cart_item['tm_epo_product_price_with_options'], $cart_item ) ) == 0 ) {
			if ( $this->tm_epo_no_zero_priced_products == "yes" ) {
				throw new Exception( __( "You cannot add zero priced products to the cart.", 'woocommerce-tm-extra-product-options' ) );
			}
		}

		// variation slug-to-name-for order again
		if ( isset( $cart_item["variation"] ) && is_array( $cart_item["variation"] ) ) {
			$_variation_name_fix = array();
			$_temp = array();
			foreach ( $cart_item["variation"] as $meta_name => $meta_value ) {
				if ( strpos( $meta_name, "attribute_" ) !== 0 ) {
					$_variation_name_fix[ "attribute_" . $meta_name ] = $meta_value;
					$_temp[ $meta_name ] = $meta_value;
				}
			}
			$cart_item["variation"] = array_diff_key( $cart_item["variation"], $_temp );
			$cart_item["variation"] = array_merge( $cart_item["variation"], $_variation_name_fix );
		}

		return apply_filters( "wc_epo_adjust_cart_item", $cart_item );

	}

	/** Gets the cart from session. **/
	public function get_cart_item_from_session( $cart_item = array(), $values = array() ) {

		$this->is_get_from_session = TRUE;
		if ( !empty( $values['tmcartepo'] ) ) {
			$cart_item['tmcartepo'] = $values['tmcartepo'];
			$cart_item = $this->add_cart_item( $cart_item );
			if ( empty( $cart_item['addons'] ) && !empty( $cart_item['tm_epo_options_prices'] ) ) {
				$cart_item['addons'] = array( "epo" => TRUE, 'price' => 0 );
			}
		}
		if ( !empty( $values['tmcartepo_bto'] ) ) {
			$cart_item['tmcartepo_bto'] = $values['tmcartepo_bto'];
		}
		if ( !empty( $values['tmsubscriptionfee'] ) ) {
			$cart_item['tmsubscriptionfee'] = $values['tmsubscriptionfee'];
		}
		if ( !empty( $values['tmcartfee'] ) ) {
			$cart_item['tmcartfee'] = $values['tmcartfee'];
		}

		if ( !empty( $values['tmpost_data'] ) ) {
			$cart_item['tmpost_data'] = $values['tmpost_data'];
		}

		return apply_filters( 'tm_cart_contents', $cart_item, $values );

	}

	/** Returns correct formated price for the cart table **/
	public function get_price_for_cart( $price = 0, $cart_item = array(), $symbol = FALSE, $currencies = NULL, $quantity_divide = 0, $quantity = 0, $price_type = "" ) {

		global $woocommerce;
		$product = $cart_item['data'];
		$cart = $woocommerce->cart;
		$taxable = $product->is_taxable();
		$tax_display_cart = $cart->tax_display_cart;
		$tax_string = "";

		if ( $price === FALSE ) {
			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7.0', '<' ) ) {
				if ( is_object( $product ) && property_exists( $product, "price" ) ) {
					$price = $cart_item['data']->price;
				} else {
					$price = $product->price;
				}
			} else {
				$price = $product->get_price();
			}
		}
		if ( is_array( $price_type ) ) {
			$price_type = array_values( $price_type );
			$price_type = $price_type[0];
		}
		$price = apply_filters( 'woocommerce_tm_epo_price_on_cart', $price );

		// Taxable
		if ( $taxable ) {

			if ( $tax_display_cart == 'excl' ) {

				//if ( $cart->tax_total > 0 && $cart->prices_include_tax ) {
				if ( $cart->tax_total > 0 && wc_prices_include_tax() ) {
					$tax_string = ' <small>' . apply_filters( 'wc_epo_ex_tax_or_vat_string', WC()->countries->ex_tax_or_vat() ) . '</small>';
				}
				if ( floatval( $price ) != 0 ) {
					$price = tc_get_price_excluding_tax( $product, array( 'qty' => 10000, 'price' => $price ) ) / 10000;
				}

			} else {

				//if ( $cart->tax_total > 0 && !$cart->prices_include_tax ) {
				if ( $cart->tax_total > 0 && !wc_prices_include_tax() ) {
					$tax_string = ' <small>' . apply_filters( 'inc_tax_or_vat', WC()->countries->inc_tax_or_vat() ) . '</small>';
				}
				if ( floatval( $price ) != 0 ) {
					$price = tc_get_price_including_tax( $product, array( 'qty' => 10000, 'price' => $price ) ) / 10000;
				}
			}

		}
		if ( $symbol === FALSE ) {
			if ( $this->tm_epo_global_price_sign == '' && $this->tm_epo_cart_field_display != "advanced" ) {
				$symbol = apply_filters( 'wc_epo_get_price_for_cart_plus_sign', "<span class='tc-plus-sign'>+</span>" );
			}
			if ( floatval( $price ) < 0 ) {
				$symbol = apply_filters( 'wc_epo_get_price_for_cart_minus_sign', "<span class='tc-minus-sign'>-</span>" );
			}
		}

		if ( !empty( $quantity ) ) {
			$price = floatval( $price ) * floatval( $quantity );
		}

		if ( floatval( $price ) == 0 ) {
			$symbol = apply_filters( 'wc_epo_get_price_for_cart_price_empty', '', $price, $tax_string, $cart_item, $symbol, $currencies, $quantity_divide, $quantity, $price_type );
		} else {
			$price = apply_filters( 'wc_epo_get_price_for_cart_price', ' <span>' . (tc_price( abs( $price ) )) . '</span>', $price = 0, $cart_item, $symbol, $currencies, $quantity_divide, $quantity, $price_type );
			$symbol = apply_filters( 'wc_epo_get_price_for_cart_symbol', " $symbol" . $price . $tax_string, $symbol, $price, $tax_string, $cart_item, $symbol, $currencies, $quantity_divide, $quantity, $price_type );

			if ( $this->tm_epo_strip_html_from_emails == "yes" ) {
				$symbol = strip_tags( $symbol );
			}
		}

		return apply_filters( 'wc_epo_get_price_for_cart', $symbol, $price, $cart_item, $symbol, $currencies, $quantity_divide, $quantity, $price_type );

	}

	/** Helper function for filtered_get_item_data **/
	private function filtered_get_item_data_get_array_data( $tmcp = array() ) {

		return array(
			'label'               => $tmcp['section_label'],
			'type'                => isset( $tmcp['element'] ) && isset( $tmcp['element']['type'] ) ? $tmcp['element']['type'] : '',
			'other_data'          => array(
				array(
					'name'                    => $tmcp['name'],
					'value'                   => $tmcp['value'],
					'price_type'              => isset( $tmcp['element'] ) ? (isset( $tmcp['key'] ) ? $tmcp['element']['rules_type'][ $tmcp['key'] ][0] : $tmcp['element']['rules_type'][0]) : '',
					'unit_price'              => $tmcp['price'],
					'unit_price_per_currency' => (isset( $tmcp['price_per_currency'] )) ? $tmcp['price_per_currency'] : array(),
					'display'                 => isset( $tmcp['display'] ) ? $tmcp['display'] : '',
					'images'                  => isset( $tmcp['images'] ) ? $tmcp['images'] : '',
					'color'                   => isset( $tmcp['color'] ) ? $tmcp['color'] : '',
					'quantity'                => isset( $tmcp['quantity'] ) ? $tmcp['quantity'] : 1,
				) ),
			'price'               => $tmcp['price'],
			'currencies'          => isset( $tmcp['currencies'] ) ? $tmcp['currencies'] : array(),
			'price_per_currency'  => isset( $tmcp['price_per_currency'] ) ? $tmcp['price_per_currency'] : array(),
			'quantity'            => isset( $tmcp['quantity'] ) ? $tmcp['quantity'] : 1,
			'percentcurrenttotal' => isset( $tmcp['percentcurrenttotal'] ) ? $tmcp['percentcurrenttotal'] : 0,
			'items'               => 1,
			'multiple_values'     => isset( $tmcp['multiple_values'] ) ? $tmcp['multiple_values'] : '',
			'hidelabelincart'     => isset( $tmcp['hidelabelincart'] ) ? $tmcp['hidelabelincart'] : '',
			'hidevalueincart'     => isset( $tmcp['hidevalueincart'] ) ? $tmcp['hidevalueincart'] : '',
		);

	}

	/** Filters our cart items. **/
	private function filtered_get_item_data( $cart_item = array() ) {

		$to_currency = tc_get_woocommerce_currency();
		$filtered_array = array();
		if ( isset( $cart_item['tmcartepo'] ) && is_array( $cart_item['tmcartepo'] ) ) {
			foreach ( $cart_item['tmcartepo'] as $tmcp ) {
				if ( $tmcp ) {

					if ( isset( $tmcp['price_per_currency'] ) && isset( $tmcp['price_per_currency'][ $to_currency ] ) && $tmcp['price_per_currency'][ $to_currency ] !== '' ) {
						$tmcp['price'] = (float) wc_format_decimal( $tmcp['price_per_currency'][ $to_currency ], FALSE, TRUE );
					} else {
						$tmcp['price'] = (float) wc_format_decimal( $tmcp['price'], FALSE, TRUE );
						$tmcp['price'] = apply_filters( 'wc_epo_get_current_currency_price', $tmcp['price'], isset( $tmcp['element'] ) ? $tmcp['element']['rules_type'][ isset( $tmcp['key'] ) ? $tmcp['key'] : 0 ][0] : '' );
					}

					if ( !isset( $filtered_array[ $tmcp['section'] ] ) ) {
						$filtered_array[ $tmcp['section'] ] = $this->filtered_get_item_data_get_array_data( $tmcp );
					} else {
						if ( $this->tm_epo_cart_field_display == "advanced" || $this->tm_epo_cart_field_display == "link" ) {
							$filtered_array[ $tmcp['section'] . "_" . TM_EPO_HELPER()->tm_uniqid() ] = $this->filtered_get_item_data_get_array_data( $tmcp );
						} else {
							$filtered_array[ $tmcp['section'] ]['items'] += 1;
							$filtered_array[ $tmcp['section'] ]['price'] += $tmcp['price'];

							if ( isset( $tmcp['price_per_currency'] ) ) {
								$filtered_array[ $tmcp['section'] ]['price_per_currency'] = TM_EPO_HELPER()->add_array_values( $filtered_array[ $tmcp['section'] ]['price_per_currency'], $tmcp['price_per_currency'] );
							}

							$filtered_array[ $tmcp['section'] ]['quantity'] += isset( $tmcp['quantity'] ) ? $tmcp['quantity'] : 1;
							$filtered_array[ $tmcp['section'] ]['other_data'][] = array(
								'name'                    => $tmcp['name'],
								'value'                   => $tmcp['value'],
								'price_type'              => isset( $tmcp['element'] ) ? (isset( $tmcp['key'] ) ? $tmcp['element']['rules_type'][ $tmcp['key'] ][0] : $tmcp['element']['rules_type'][0]) : '',
								'unit_price'              => $tmcp['price'],
								'unit_price_per_currency' => (isset( $tmcp['price_per_currency'] )) ? $tmcp['price_per_currency'] : array(),
								'display'                 => isset( $tmcp['display'] ) ? $tmcp['display'] : '',
								'images'                  => isset( $tmcp['images'] ) ? $tmcp['images'] : '',
								'color'                   => isset( $tmcp['color'] ) ? $tmcp['color'] : '',
								'quantity'                => isset( $tmcp['quantity'] ) ? $tmcp['quantity'] : 1,
							);
						}
					}
				}
			}
		}

		return $filtered_array;

	}

	/** Return formatted cart items **/
	public function get_item_data_array( $other_data, $cart_item = array() ) {

		$filtered_array = $this->filtered_get_item_data( $cart_item );
		$price = 0;
		$link_data = array();
		$quantity = $cart_item['quantity'];
		if ( is_array( $filtered_array ) ) {
			foreach ( $filtered_array as $section ) {
				$value = array();
				$quantity_string_shown = FALSE;
				$format_price_shown = FALSE;
				$do_unique_values = FALSE;
				$prev_unit_price = FALSE;
				$prev_unit_quantity = FALSE;
				$dont_show_mass_quantity = FALSE;
				$format_price = "";
				if ( isset( $section['other_data'] ) && is_array( $section['other_data'] ) ) {
					foreach ( $section['other_data'] as $key => $data ) {
						if ( empty( $data['quantity'] ) ) {
							continue;
						}
						$display_value = !empty( $data['display'] ) ? $data['display'] : $data['value'];

						if ( !empty( $data['images'] ) && $this->tm_epo_show_image_replacement == "yes" ) {
							if ( $this->tm_epo_hide_options_prices_in_cart == "normal" ) {
								$original_price = $data['unit_price'] / $data['quantity'];
								$new_price = apply_filters( 'wc_epo_discounted_price', $data['unit_price'], $cart_item['data'], $cart_item[ $this->cart_edit_key_var ] );
								$after_price = $new_price / $data['quantity'];
								$format_price = $this->get_price_for_cart( $after_price, $cart_item, FALSE, $data['unit_price_per_currency'], $data['quantity'], 0, $data['price_type'] );

								if ( $original_price != $after_price ) {
									$original_price = $this->get_price_for_cart( $original_price, $cart_item, FALSE, $data['unit_price_per_currency'], $data['quantity'], 0, $data['price_type'] );
									$format_price = '<span class="rp_wcdpd_cart_price"><del>' . $original_price . '</del> <ins>' . $format_price . '</ins></span>';
								}
								$format_price_shown = TRUE;
							} else {
								$format_price = '';
							}
							$quantity_string = ($data['quantity'] > 1) ? ' &times; ' . $data['quantity'] : '';
							$display_value = '<span class="cpf-img-on-cart"><img alt="" class="attachment-shop_thumbnail wp-post-image epo-option-image" src="' .
								apply_filters( "tm_image_url", $data['images'] ) . '" />' . $display_value . '<small>' . $format_price . $quantity_string . '</small></span>';
							$quantity_string_shown = TRUE;
						} elseif ( !empty( $data['color'] ) && $this->tm_epo_show_image_replacement == "yes" ) {
							if ( $this->tm_epo_hide_options_prices_in_cart == "normal" ) {
								$original_price = $data['unit_price'] / $data['quantity'];
								$new_price = apply_filters( 'wc_epo_discounted_price', $data['unit_price'], $cart_item['data'], $cart_item[ $this->cart_edit_key_var ] );
								$after_price = $new_price / $data['quantity'];
								$format_price = $this->get_price_for_cart( $after_price, $cart_item, FALSE, $data['unit_price_per_currency'], $data['quantity'], 0, $data['price_type'] );

								if ( $original_price != $after_price ) {
									$original_price = $this->get_price_for_cart( $original_price, $cart_item, FALSE, $data['unit_price_per_currency'], $data['quantity'], 0, $data['price_type'] );
									$format_price = '<span class="rp_wcdpd_cart_price"><del>' . $original_price . '</del> <ins>' . $format_price . '</ins></span>';
								}
								$format_price_shown = TRUE;
							} else {
								$format_price = '';
							}
							$quantity_string = ($data['quantity'] > 1) ? ' &times; ' . $data['quantity'] : '';
							$display_value = '<span class="cpf-colors-on-cart"><span class="cpf-color-on-cart" style="background-color:' . $data['color'] . ';"></span> ' . $display_value . '<small>' . $format_price . $quantity_string . '</small></span>';
							$quantity_string_shown = TRUE;
						} else {
							if ( $prev_unit_quantity === FALSE ) {
								$prev_unit_quantity = $data['quantity'];
							}
							if ( $prev_unit_price === FALSE ) {
								$prev_unit_price = $data['unit_price'];								
							} elseif ( $prev_unit_price !== $data['unit_price'] || $prev_unit_quantity != $data['quantity'] || $data['quantity'] > 1 ) {
								$do_unique_values = TRUE;
								if ( $this->tm_epo_hide_options_prices_in_cart == "normal" ) {
									break;
								} else {
									$dont_show_mass_quantity = TRUE;
								}
							}
							$prev_unit_price = $data['unit_price'];
							$prev_unit_quantity = $data['quantity'];

						}
						if ( $this->tm_epo_show_upload_image_replacement == "yes" && $section['type'] == "upload" ) {
							$check = wp_check_filetype( $data['value'] );
							if ( !empty( $check['ext'] ) ) {
								$image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );
								if ( in_array( $check['ext'], $image_exts ) ) {
									$display_value = '<span class="cpf-img-on-cart"><img alt="" class="attachment-shop_thumbnail wp-post-image epo-option-image epo-upload-image" src="' .
										apply_filters( "tm_image_url", $data['value'] ) . '" /><span>';
								}
							}
						}
						$value[] = $display_value;
					}

					if ( !empty( $section['multiple_values'] ) ) {
						$do_unique_values = TRUE;
					}

					if ($this->tm_epo_always_unique_values === 'yes' && $section['type'] === 'checkbox' ){
						$do_unique_values = TRUE;
					}	

					if ( $do_unique_values ) {
						$value = array();
						foreach ( $section['other_data'] as $key => $data ) {
							if ( empty( $data['quantity'] ) ) {
								continue;
							}
							$display_value = !empty( $data['display'] ) ? $data['display'] : $data['value'];
							$original_price = $data['unit_price'] / $data['quantity'];
							$new_price = apply_filters( 'wc_epo_discounted_price', $data['unit_price'], $cart_item['data'], $cart_item[ $this->cart_edit_key_var ] );
							$after_price = $new_price / $data['quantity'];
							$format_price = $this->get_price_for_cart( $after_price, $cart_item, FALSE, $data['unit_price_per_currency'], $data['quantity'], 0, $data['price_type'] );

							if ( $original_price != $after_price ) {
								$original_price = $this->get_price_for_cart( $original_price, $cart_item, FALSE, $data['unit_price_per_currency'], $data['quantity'], 0, $data['price_type'] );
								$format_price = '<span class="rp_wcdpd_cart_price"><del>' . $original_price . '</del> <ins>' . $format_price . '</ins></span>';
							}
							$format_price_shown = TRUE;
							$quantity_string = ($data['quantity'] > 1) ? ' &times; ' . $data['quantity'] : '';
							if ( $this->tm_epo_hide_options_prices_in_cart != "normal" ) {
								$format_price = '';
							}
							if ( !empty( $section['multiple_values'] ) ) {
								$display_value_array = explode( $section['multiple_values'], $display_value );
								$display_value = "";
								foreach ( $display_value_array as $d => $dv ) {
									$display_value .= '<span class="cpf-data-on-cart">' . $dv . '</span>';
								}
								$display_value .= ' <small>' . $format_price . $quantity_string . '</small>';
							} else {
								$display_value = '<span class="cpf-data-on-cart">' . $display_value . ' <small>' . $format_price . $quantity_string . '</small></span>';
							}
							$quantity_string_shown = TRUE;
							$value[] = $display_value;
						}
					}
				}

				if ( !empty( $value ) && count( $value ) > 0 ) {
					if ( $quantity_string_shown ) {
						if ( is_array( $value[0] ) ) {
							$temp = '';
							foreach ( $value as $k => $v ) {
								$temp .= implode( "", $v );
							}
							$value = $temp;
						} else {
							$value = implode( "", $value );
						}
					} else {
						if ( is_array( $value[0] ) ) {
							$temp = '';
							foreach ( $value as $k => $v ) {
								$temp .= implode( " , ", $v );
							}
							$value = $temp;
						} else {
							if ( !empty( $section['multiple_values'] ) ) {

								$value = implode( " , ", $value );

							} else {
								$value = implode( " , ", $value );
							}
						}

					}
				} else {
					$value = "";
				}

				if ( empty( $section['quantity'] ) ) {
					$section['quantity'] = 1;
				}

				// WooCommerce Dynamic Pricing & Discounts
				$original_price = $section['price'] / $section['quantity'];
				$original_price_q = $original_price * $quantity * $section['quantity'];

				$section['price'] = apply_filters( 'wc_epo_discounted_price', $section['price'], $cart_item['data'], $cart_item[ $this->cart_edit_key_var ] );
				$after_price = $section['price'] / $section['quantity'];

				$price = $price + (float) $section['price'];
				$section['price_type'] = "";
				if ( $this->tm_epo_hide_options_prices_in_cart == "normal" ) {
					$format_price = $this->get_price_for_cart( $after_price, $cart_item, FALSE, $section['price_per_currency'], $section['quantity'], 0, $section['price_type'] );
					$format_price_total = $this->get_price_for_cart( $section['price'], $cart_item, FALSE, $section['price_per_currency'], 0, $quantity, $section['price_type'] );
					$format_price_total2 = $this->get_price_for_cart( $section['price'], $cart_item, FALSE, $section['price_per_currency'], 0, 0, $section['price_type'] );
					if ( $original_price != $after_price ) {
						$original_price = $this->get_price_for_cart( $original_price, $cart_item, FALSE, $section['price_per_currency'], 0, 0, $section['price_type'] );
						$original_price_total = $this->get_price_for_cart( $original_price_q, $cart_item, FALSE, $section['price_per_currency'], 0, 0, $section['price_type'] );
						$format_price = '<span class="rp_wcdpd_cart_price"><del>' . $original_price . '</del> <ins>' . $format_price . '</ins></span>';
					}
				} else {
					$format_price = '';
					$format_price_total = '';
					$format_price_total2 = '';
				}
				$single_price = $this->get_price_for_cart( (float) $section['price'] / $section['quantity'], $cart_item, FALSE, $section['price_per_currency'], 0, 0, $section['price_type'] );
				$quantity_string = ($section['quantity'] > 1) ? ' &times; ' . $section['quantity'] : '';
				if ( $format_price==="" || $quantity_string_shown || $dont_show_mass_quantity ) {
					$quantity_string = "";
				}

				if ( $this->tm_epo_cart_field_display != "link" ) {
					if ( empty( $section['hidelabelincart'] ) || empty( $section['hidevalueincart'] ) ) {
						$value_to_show = (empty( $section['hidevalueincart'] )) ? do_shortcode( TM_EPO_HELPER()->html_entity_decode( $value ) ) : '';

						$other_data[] = array(
							'name'           => empty( $section['hidelabelincart'] ) ? $section['label'] : '',
							'value'          => (empty( $section['hidevalueincart'] ) || $section['hidevalueincart'] === 'price')
								?
								$value_to_show .
								((!$format_price_shown && $format_price && $quantity_string)
									? '<small>' . $format_price . $quantity_string . '</small>'
									:
									(
									($format_price)
										? ($do_unique_values)
										? (
										($this->tm_epo_hide_cart_average_price == 'no')
											? '<span class="tc-average-price">' . $format_price . '</span>'
											: ''
										)
										: '<span class="tc-av-price">' . $format_price . '</span>'
										: (($quantity_string) ? '<small>' . $quantity_string . '</small>' : '')
									)
								)
								: '',
							'tm_label'       => $section['label'],
							'tm_value'       => do_shortcode( TM_EPO_HELPER()->html_entity_decode( $value ) ),
							'tm_price'       => $format_price,
							'tm_total_price' => $format_price_total,
							'tm_quantity'    => $section['quantity'],
							'tm_image'       => $section['other_data'][0]['images'],
						);
					}
				}
				if ( empty( $section['hidelabelincart'] ) || empty( $section['hidevalueincart'] ) ) {
					$link_data[] = array(
						'name'            => empty( $section['hidelabelincart'] ) ? $section['label'] : '',
						'value'           => empty( $section['hidevalueincart'] ) ? $value : '',
						'price'           => $format_price,
						'tm_price'        => $single_price,
						'tm_total_price'  => $format_price_total,
						'tm_quantity'     => $section['quantity'],
						'tm_total_price2' => $format_price_total2,
					);
				}
			}
		}

		if ( $this->tm_epo_cart_field_display == "link" ) {
			if ( empty( $price ) || $this->tm_epo_hide_options_prices_in_cart != "normal" ) {
				$price = '';
			} else {
				$price = $this->get_price_for_cart( $price, $cart_item, FALSE, NULL, 0, 0, $section['price_type'] );
			}
			$uni = uniqid( '' );
			$data = '<div class="tm-extra-product-options">';
			$data .= '<div class="tm-row tm-cart-row">'
				. '<div class="tm-cell col-4 cpf-name">&nbsp;</div>'
				. '<div class="tm-cell col-4 cpf-value">&nbsp;</div>'
				. '<div class="tm-cell col-2 cpf-price">' . esc_attr__( 'Price', 'woocommerce' ) . '</div>'
				. '<div class="tm-cell col-1 cpf-quantity">' . esc_attr__( 'Quantity', 'woocommerce' ) . '</div>'
				. '<div class="tm-cell col-1 cpf-total-price">' . esc_attr__( 'Total', 'woocommerce' ) . '</div>'
				. '</div>';
			foreach ( $link_data as $link ) {
				$data .= '<div class="tm-row tm-cart-row">'
					. '<div class="tm-cell col-4 cpf-name">' . $link['name'] . '</div>'
					. '<div class="tm-cell col-4 cpf-value">' . do_shortcode( TM_EPO_HELPER()->html_entity_decode( $link['value'] ) ) . '</div>'
					. '<div class="tm-cell col-2 cpf-price">' . $link['tm_price'] . '</div>'
					. '<div class="tm-cell col-1 cpf-quantity">' . (($link['tm_price'] == '') ? '' : $link['tm_quantity']) . '</div>'
					. '<div class="tm-cell col-1 cpf-total-price">' . $link['tm_total_price2'] . '</div>'
					. '</div>';

			}
			$data .= '</div>';
			$other_data[] = array(

				'name'  => '<a href="#tm-cart-link-data-' . $uni . '" class="tm-cart-link">' . ((!empty( $this->tm_epo_additional_options_text )) ? $this->tm_epo_additional_options_text : __( 'Additional options', 'woocommerce-tm-extra-product-options' )) . '</a>',
				'value' => $price . '<div id="tm-cart-link-data-' . $uni . '" class="tm-cart-link-data tm-hidden">' . $data . '</div>',

			);
		}

		return $other_data;

	}

	/** Gets cart item to display in the frontend. **/
	public function get_item_data( $other_data, $cart_item ) {

		if ( $this->tm_epo_hide_options_in_cart == "normal" && $this->tm_epo_cart_field_display != "advanced" && !empty( $cart_item['tmcartepo'] ) ) {

			$other_data = $this->get_item_data_array( $other_data, $cart_item );

		}

		return $other_data;

	}

	/** Calculates the correct option price **/
	public function calculate_price( $post_data = NULL, $element, $key, $attribute, $per_product_pricing, $cpf_product_price = FALSE, $variation_id, $price_default_value = 0, $currency = FALSE, $current_currency = FALSE, $price_per_currencies = NULL ) {

		if ( is_null( $post_data ) && isset( $_POST ) ) {
			$post_data = $_POST;
		}
		if ( empty( $post_data ) && isset( $_REQUEST['tcajax'] ) ) {
			$post_data = $_REQUEST;
		}
		$_price = $price_default_value;
		$_price_type = "";
		// this currently happens for multiple file uploads
		if ( is_array( $key ) ) {
			$key = 0;
		}
		$key = esc_attr( $key );
		if ( $per_product_pricing ) {

			if ( !isset( $element['price_rules'][ $key ] ) ) {// field price rule
				if ( $variation_id && isset( $element['price_rules'][0][ $variation_id ] ) ) {// general variation rule
					$_price = $element['price_rules'][0][ $variation_id ];
				} elseif ( isset( $element['price_rules'][0][0] ) ) {// general rule
					$_price = $element['price_rules'][0][0];
				}
			} else {
				if ( $variation_id && isset( $element['price_rules'][ $key ][ $variation_id ] ) ) {// field price rule
					$_price = $element['price_rules'][ $key ][ $variation_id ];
				} elseif ( isset( $element['price_rules'][ $key ][0] ) ) {// general field variation rule
					$_price = $element['price_rules'][ $key ][0];
				} elseif ( $variation_id && isset( $element['price_rules'][0][ $variation_id ] ) ) {// general variation rule
					$_price = $element['price_rules'][0][ $variation_id ];
				} elseif ( isset( $element['price_rules'][0][0] ) ) {// general rule
					$_price = $element['price_rules'][0][0];
				}
			}

			if ( !isset( $element['price_rules_type'][ $key ] ) ) {// field price rule
				if ( $variation_id && isset( $element['price_rules_type'][0][ $variation_id ] ) ) {// general variation rule
					$_price_type = $element['price_rules_type'][0][ $variation_id ];
				} elseif ( isset( $element['price_rules_type'][0][0] ) ) {// general rule
					$_price_type = $element['price_rules_type'][0][0];
				}
			} else {
				if ( $variation_id && isset( $element['price_rules_type'][ $key ][ $variation_id ] ) ) {// field price rule
					$_price_type = $element['price_rules_type'][ $key ][ $variation_id ];
				} elseif ( isset( $element['price_rules_type'][ $key ][0] ) ) {// general field variation rule
					$_price_type = $element['price_rules_type'][ $key ][0];
				} elseif ( $variation_id && isset( $element['price_rules_type'][0][ $variation_id ] ) ) {// general variation rule
					$_price_type = $element['price_rules_type'][0][ $variation_id ];
				} elseif ( isset( $element['price_rules_type'][0][0] ) ) {// general rule
					$_price_type = $element['price_rules_type'][0][0];
				}
			}

			$_price = floatval( wc_format_decimal( $_price, FALSE, TRUE ) );

			switch ( $_price_type ) {
				case 'percent':
					if ( $cpf_product_price !== FALSE ) {

						if ( $currency ) {
							$cpf_product_price = apply_filters( 'wc_epo_get_currency_price', $cpf_product_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
						}
						$_price = ($_price / 100) * floatval( $cpf_product_price );
					}
					break;
				case 'percentcurrenttotal':
					$_original_price = $_price;
					if ( $_price != '' && isset( $post_data[ $attribute . '_hidden' ] ) ) {
						$_price = floatval( $post_data[ $attribute . '_hidden' ] );

						if ( isset( $post_data['tm_epo_options_static_prices'] ) ) {
							$_price = (floatval( $post_data['tm_epo_options_static_prices'] ) + floatval( $cpf_product_price )) * ($_original_price / 100);
							if ( isset( $post_data[ $attribute . '_quantity' ] ) && $post_data[ $attribute . '_quantity' ] > 0 ) {
								$_price = $_price * floatval( $post_data[ $attribute . '_quantity' ] );
							}
						}

						if ( $currency ) {
							$_price = apply_filters( 'wc_epo_get_currency_price', $_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
						}

						if ( isset( $post_data[ $attribute . '_quantity' ] ) && $post_data[ $attribute . '_quantity' ] > 0 ) {
							$_price = $_price / floatval( $post_data[ $attribute . '_quantity' ] );
						}
					}
					break;
				case 'char':
					$_price = floatval( $_price * strlen( stripcslashes( utf8_decode( $post_data[ $attribute ] ) ) ) );
					if ( $currency ) {
						$_price = apply_filters( 'wc_epo_get_currency_price', $_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
					}
					break;
				case 'charpercent':
					if ( $cpf_product_price !== FALSE ) {
						if ( $currency ) {
							$cpf_product_price = apply_filters( 'wc_epo_get_currency_price', $cpf_product_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
						}
						$_price = floatval( strlen( stripcslashes( utf8_decode( $post_data[ $attribute ] ) ) ) ) * (($_price / 100) * floatval( $cpf_product_price ));
					}
					break;
				case 'charnofirst':
					$_textlength = floatval( strlen( stripcslashes( utf8_decode( $post_data[ $attribute ] ) ) ) ) - 1;
					if ( $_textlength < 0 ) {
						$_textlength = 0;
					}
					$_price = floatval( $_price * $_textlength );
					if ( $currency ) {
						$_price = apply_filters( 'wc_epo_get_currency_price', $_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
					}
					break;

				case 'charnon':
					$freechars = absint( $element['freechars'] );
					$_textlength = floatval( strlen( stripcslashes( utf8_decode( $post_data[ $attribute ] ) ) ) ) - $freechars;
					if ( $_textlength < 0 ) {
						$_textlength = 0;
					}
					$_price = floatval( $_price * $_textlength );
					if ( $currency ) {
						$_price = apply_filters( 'wc_epo_get_currency_price', $_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
					}
					break;
				case 'charpercentnon':
					if ( $cpf_product_price !== FALSE ) {
						$freechars = absint( $element['freechars'] );
						if ( $currency ) {
							$cpf_product_price = apply_filters( 'wc_epo_get_currency_price', $cpf_product_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
						}
						$_textlength = floatval( strlen( stripcslashes( utf8_decode( $post_data[ $attribute ] ) ) ) ) - $freechars;
						if ( $_textlength < 0 ) {
							$_textlength = 0;
						}
						$_price = floatval( $_textlength ) * (($_price / 100) * floatval( $cpf_product_price ));
					}
					break;
				case 'charnonnospaces':
					$freechars = absint( $element['freechars'] );
					$_textlength = floatval( strlen( preg_replace( "/\s+/", "", stripcslashes( utf8_decode( $post_data[ $attribute ] ) ) ) ) ) - $freechars;
					if ( $_textlength < 0 ) {
						$_textlength = 0;
					}
					$_price = floatval( $_price * $_textlength );
					if ( $currency ) {
						$_price = apply_filters( 'wc_epo_get_currency_price', $_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
					}
					break;
				case 'charpercentnonnospaces':
					if ( $cpf_product_price !== FALSE ) {
						$freechars = absint( $element['freechars'] );
						if ( $currency ) {
							$cpf_product_price = apply_filters( 'wc_epo_get_currency_price', $cpf_product_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
						}
						$_textlength = floatval( strlen( preg_replace( "/\s+/", "", stripcslashes( utf8_decode( $post_data[ $attribute ] ) ) ) ) ) - $freechars;
						if ( $_textlength < 0 ) {
							$_textlength = 0;
						}
						$_price = floatval( $_textlength ) * (($_price / 100) * floatval( $cpf_product_price ));
					}
					break;

				case 'charnospaces':
					$_price = floatval( $_price * strlen( preg_replace( "/\s+/", "", stripcslashes( utf8_decode( $post_data[ $attribute ] ) ) ) ) );
					if ( $currency ) {
						$_price = apply_filters( 'wc_epo_get_currency_price', $_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
					}
					break;
				case 'charpercentnofirst':
					if ( $cpf_product_price !== FALSE ) {
						if ( $currency ) {
							$cpf_product_price = apply_filters( 'wc_epo_get_currency_price', $cpf_product_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
						}
						$_textlength = floatval( strlen( stripcslashes( utf8_decode( $post_data[ $attribute ] ) ) ) ) - 1;
						if ( $_textlength < 0 ) {
							$_textlength = 0;
						}
						$_price = floatval( $_textlength ) * (($_price / 100) * floatval( $cpf_product_price ));
					}
					break;
				case 'step':
				case 'stepfee':
					$_price = floatval( $_price * floatval( stripcslashes( $post_data[ $attribute ] ) ) );
					if ( $currency ) {
						$_price = apply_filters( 'wc_epo_get_currency_price', $_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
					}
					break;
				case 'currentstep':
				case 'currentstepfee':
					$_price = floatval( stripcslashes( $post_data[ $attribute ] ) );
					if ( $currency ) {
						$_price = apply_filters( 'wc_epo_get_currency_price', $_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
					}
					break;
				case 'intervalstep':
					if ( isset( $element["min"] ) ) {
						$_min = floatval( $element["min"] );
						$_price = floatval( $_price * (floatval( stripcslashes( $post_data[ $attribute ] ) ) - $_min) );
						if ( $currency ) {
							$_price = apply_filters( 'wc_epo_get_currency_price', $_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
						}
					}
					break;
				case 'row':
					$_price = floatval( $_price * (substr_count( stripcslashes( utf8_decode( $post_data[ $attribute ] ) ), "\r\n" ) + 1) );
					if ( $currency ) {
						$_price = apply_filters( 'wc_epo_get_currency_price', $_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
					}
					break;
				default:
					// fixed price
					if ( $currency ) {
						$_price = apply_filters( 'wc_epo_get_currency_price', $_price, $currency, $_price_type, FALSE, $current_currency, $price_per_currencies, $key, $attribute );
					}
					break;
			}

			// quantity button
			if ( isset( $post_data[ $attribute . '_quantity' ] ) ) {
				$_price = $_price * floatval( $post_data[ $attribute . '_quantity' ] );
			}

			if ( $price_default_value === '' && $_price == 0 ) {
				$_price = '';
			}

		}

		$_price = apply_filters( 'wc_epo_calculate_price', $_price, $post_data, $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id, $price_default_value, $currency, $current_currency, $price_per_currencies );

		return apply_filters( 'tm_wcml_raw_price_amount', $_price );

	}

	/** Adds meta data to the order - WC < 2.7 **/
	public function order_item_meta( $item_id, $values ) {
		do_action( 'wc_epo_order_item_meta_before', $item_id, $values );
		if ( !empty( $values['tmcartepo'] ) ) {
			wc_add_order_item_meta( $item_id, '_tmcartepo_data', $values['tmcartepo'] );
			wc_add_order_item_meta( $item_id, '_tm_epo_product_original_price', array( $values['tm_epo_product_original_price'] ) );
			wc_add_order_item_meta( $item_id, '_tm_epo', array( 1 ) );
		}
		if ( !empty( $values['tmsubscriptionfee'] ) ) {
			wc_add_order_item_meta( $item_id, '_tmsubscriptionfee_data', array( $values['tmsubscriptionfee'] ) );
			wc_add_order_item_meta( $item_id, __( "Options Subscription fee", 'woocommerce-tm-extra-product-options' ), $values['tmsubscriptionfee'] );
		}
		if ( !empty( $values['tmcartfee'] ) ) {
			wc_add_order_item_meta( $item_id, '_tmcartfee_data', array( $values['tmcartfee'] ) );
		}
		do_action( 'wc_epo_order_item_meta', $item_id, $values );

	}

	/** Adds meta data to the order - WC >= 2.7 (crud) **/
	public function order_line_item( $item, $cart_item_key, $values ) {
		do_action( 'wc_epo_order_item_meta_before', $item, $cart_item_key, $values );
		if ( !empty( $values['tmcartepo'] ) ) {
			$item->add_meta_data( '_tmcartepo_data', $values['tmcartepo'] );
			$item->add_meta_data( '_tm_epo_product_original_price', array( $values['tm_epo_product_original_price'] ) );
			$item->add_meta_data( '_tm_epo', array( 1 ) );
		}
		if ( !empty( $values['tmsubscriptionfee'] ) ) {
			$item->add_meta_data( '_tmsubscriptionfee_data', array( $values['tmsubscriptionfee'] ) );
			$item->add_meta_data( __( "Options Subscription fee", 'woocommerce-tm-extra-product-options' ), $values['tmsubscriptionfee'] );
		}
		if ( !empty( $values['tmcartfee'] ) ) {
			$item->add_meta_data( '_tmcartfee_data', array( $values['tmcartfee'] ) );
		}

		do_action( 'wc_epo_order_item_meta', $item, $cart_item_key, $values );
	}

	/** Validates the cart data. **/
	public function add_to_cart_validation( $passed, $product_id, $qty, $variation_id = '', $variations = array(), $cart_item_data = array() ) {

		// disables add_to_cart_button class on shop page
		if ( is_ajax() && $this->tm_epo_force_select_options == "display" && !isset( $_REQUEST['tcaddtocart'] ) ) {

			$has_epo = TM_EPO_API()->has_options( $product_id );
			if ( TM_EPO_API()->is_valid_options( $has_epo ) ) {
				return FALSE;
			}

		}

		$is_validate = TRUE;

		// Get product type
		$terms = get_the_terms( $product_id, 'product_type' );
		$product_type = !empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';
		if ( $product_type == 'bto' || $product_type == 'composite' ) {

			$bto_data = maybe_unserialize( get_post_meta( $product_id, '_bto_data', TRUE ) );
			$valid_ids = array();
			if ( is_array( $bto_data ) ) {
				$valid_ids = array_keys( $bto_data );
			}
			foreach ( $valid_ids as $bundled_item_id ) {

				if ( isset( $_REQUEST['add-product-to-cart'][ $bundled_item_id ] ) && $_REQUEST['add-product-to-cart'][ $bundled_item_id ] !== '' ) {
					$bundled_product_id = $_REQUEST['add-product-to-cart'][ $bundled_item_id ];
				} elseif ( isset( $cart_item_data['composite_data'][ $bundled_item_id ]['product_id'] ) && isset( $_GET['order_again'] ) ) {
					$bundled_product_id = $cart_item_data['composite_data'][ $bundled_item_id ]['product_id'];
				} elseif ( isset( $_REQUEST['add-product-to-cart'][ $bundled_item_id ] ) && $_REQUEST['add-product-to-cart'][ $bundled_item_id ] !== '' ) {
					$bundled_product_id = $_REQUEST['wccp_component_selection'][ $bundled_item_id ];
				}elseif ( isset( $_REQUEST['wccp_component_selection'] ) && isset( $_REQUEST['wccp_component_selection'][ $bundled_item_id ] ) ){
					$bundled_product_id = $_REQUEST['wccp_component_selection'][ $bundled_item_id ];
				}

				if ( isset( $bundled_product_id ) && !empty( $bundled_product_id ) ) {

					$_passed = TRUE;

					if ( isset( $_REQUEST['item_quantity'][ $bundled_item_id ] ) && is_numeric( $_REQUEST['item_quantity'][ $bundled_item_id ] ) ) {
						$item_quantity = absint( $_REQUEST['item_quantity'][ $bundled_item_id ] );
					} elseif ( isset( $cart_item_data['composite_data'][ $bundled_item_id ]['quantity'] ) && isset( $_GET['order_again'] ) ) {
						$item_quantity = $cart_item_data['composite_data'][ $bundled_item_id ]['quantity'];
					} elseif ( isset( $_REQUEST['wccp_component_quantity'][ $bundled_item_id ] ) && is_numeric( $_REQUEST['wccp_component_quantity'][ $bundled_item_id ] ) ) {
						$item_quantity = absint( $_REQUEST['wccp_component_quantity'][ $bundled_item_id ] );
					}
					if ( !empty( $item_quantity ) ) {
						$item_quantity = absint( $item_quantity );

						$_passed = $this->validate_product_id( $bundled_product_id, $item_quantity, $bundled_item_id );
					}

					if ( !$_passed ) {
						$is_validate = FALSE;
					}

				}
			}
		}

		$tc_form_prefix = "";
		if ( isset( $_REQUEST['tc_form_prefix'] ) ) {
			$tc_form_prefix = $_REQUEST['tc_form_prefix'];
		}
		if ( !$this->validate_product_id( $product_id, $qty, $tc_form_prefix ) ) {
			$passed = FALSE;
		}

		// Try to validate uploads before they happen
		$files = array();
		foreach ( $_FILES as $k => $file ) {
			if ( !empty( $file['name'] ) ) {
				$file_name = $file['name'];
				if ( !empty( $file['error'] ) ) {
					$file_error = $file['error'];

					// Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
					$upload_error_strings = array( FALSE,
						__( "The uploaded file exceeds the upload_max_filesize directive in php.ini.", 'woocommerce-tm-extra-product-options' ),
						__( "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.", 'woocommerce-tm-extra-product-options' ),
						__( "The uploaded file was only partially uploaded.", 'woocommerce-tm-extra-product-options' ),
						__( "No file was uploaded.", 'woocommerce-tm-extra-product-options' ),
						'',
						__( "Missing a temporary folder.", 'woocommerce-tm-extra-product-options' ),
						__( "Failed to write file to disk.", 'woocommerce-tm-extra-product-options' ),
						__( "File upload stopped by extension.", 'woocommerce-tm-extra-product-options' ) );

					if ( is_array( $file_error ) ) {
						foreach ( $file_error as $key => $value ) {
							if ( !empty( $value ) && !empty( $file_name[ $key ] ) ) {
								$passed = FALSE;
								if ( isset( $upload_error_strings[ $value ] ) ) {
									wc_add_notice( $upload_error_strings[ $value ], 'error' );
								}
							}
						}
					} else {
						$passed = FALSE;
						if ( isset( $upload_error_strings[ $file_error ] ) ) {
							wc_add_notice( $upload_error_strings[ $file_error ], 'error' );
						}
					}

				}
				add_filter( 'upload_mimes', array( $this, 'upload_mimes_trick' ) );
				if ( is_array( $file_name ) ) {
					foreach ( $file_name as $key => $value ) {
						if ( !empty( $value ) ) {
							$check_filetype = wp_check_filetype( $value );
							$check_filetype = $check_filetype['ext'];
							if ( !$check_filetype && !empty( $file['name'] ) ) {
								$passed = FALSE;
								wc_add_notice( __( "Sorry, this file type is not permitted for security reasons.", 'woocommerce-tm-extra-product-options' ) . ' (' . pathinfo( $value, PATHINFO_EXTENSION ) . ')', 'error' );
							}
						}
					}
				} else {
					$check_filetype = wp_check_filetype( $file['name'] );
					$check_filetype = $check_filetype['ext'];

					if ( !$check_filetype && !empty( $file['name'] ) ) {
						$passed = FALSE;
						wc_add_notice( __( "Sorry, this file type is not permitted for security reasons.", 'woocommerce-tm-extra-product-options' ) . ' (' . pathinfo( $file['name'], PATHINFO_EXTENSION ) . ')', 'error' );
					}
				}
				remove_filter( 'upload_mimes', array( $this, 'upload_mimes_trick' ) );

			}

		}

		if ( !$is_validate ) {
			$passed = FALSE;
		}

		return apply_filters( 'tm_add_to_cart_validation', $passed );

	}

	/** Conditional logic (checks if an element is visible) **/
	public function is_visible( $element = array(), $section = array(), $sections = array(), $form_prefix = "" ) {

		// Element
		$logic = FALSE;
		if ( isset( $element['section'] ) ) {
			if ( !$this->is_visible( $section, array(), $sections, $form_prefix ) ) {
				return FALSE;
			}
			if ( !isset( $element['logic'] ) || empty( $element['logic'] ) ) {
				return TRUE;
			}
			$logic = (array) json_decode( $element['clogic'] );
			// Section
		} else {
			if ( !isset( $element['sections_logic'] ) || empty( $element['sections_logic'] ) ) {
				return TRUE;
			}
			$logic = (array) json_decode( $element['sections_clogic'] );
		}

		if ( $logic ) {
			$rule_toggle = $logic['toggle'];
			$rule_what = $logic['what'];
			$matches = 0;
			$checked = 0;
			$show = TRUE;
			switch ( $rule_toggle ) {
				case "show":
					$show = FALSE;
					break;
				case "hide":
					$show = TRUE;
					break;
			}

			foreach ( $logic['rules'] as $key => $rule ) {
				$matches++;

				if ( $this->tm_check_field_match( $rule, $sections, $form_prefix ) ) {
					$checked++;
				}

			}

			if ( $rule_what == "all" ) {
				if ( $checked == $matches ) {
					$show = !$show;
				}
			} else {
				if ( $checked > 0 ) {
					$show = !$show;
				}
			}

			return $show;

		}

		return FALSE;
	}

	/** Conditional logic **/
	public function tm_check_section_match( $element_id, $operator, $rule = FALSE, $sections = FALSE, $form_prefix = "" ) {

		$all_checked = TRUE;
		$section_id = $element_id;
		if ( isset( $sections[ $section_id ] ) && isset( $sections[ $section_id ]['elements'] ) ) {
			foreach ( $sections[ $section_id ]['elements'] as $id => $element ) {
				if ( $this->is_visible( $element, $sections[ $section_id ], $sections, $form_prefix ) ) {
					$element_to_check = $sections[ $section_id ]['elements'][ $id ]['name_inc'];
					$element_type = $sections[ $section_id ]['elements'][ $id ]['type'];
					$posted_value = NULL;

					switch ( $element_type ) {
						case "radio":
							$radio_checked_length = 0;
							$element_to_check = array_unique( $element_to_check );

							$element_to_check = $element_to_check[0] . $form_prefix;

							if ( isset( $_POST[ $element_to_check ] ) ) {
								$radio_checked_length++;
								$posted_value = $_POST[ $element_to_check ];
								$posted_value = stripslashes( $posted_value );
								$posted_value = TM_EPO_HELPER()->encodeURIComponent( $posted_value );
								$posted_value = TM_EPO_HELPER()->reverse_strrchr( $posted_value, "_" );
							}
							if ( $operator == 'isnotempty' ) {
								$all_checked = $all_checked && $radio_checked_length > 0;
							} elseif ( $operator == 'isempty' ) {
								$all_checked = $all_checked && $radio_checked_length == 0;
							}
							break;
						case "checkbox":
							$checkbox_checked_length = 0;

							$element_to_check = array_unique( $element_to_check );
							foreach ( $element_to_check as $key => $name_value ) {
								$element_to_check[ $key ] = $name_value . $form_prefix;
								if ( isset( $_POST[ $element_to_check[ $key ] ] ) ) {
									$checkbox_checked_length++;
									$posted_value = $_POST[ $element_to_check[ $key ] ];
									$posted_value = stripslashes( $posted_value );
									$posted_value = TM_EPO_HELPER()->encodeURIComponent( $posted_value );
									$posted_value = TM_EPO_HELPER()->reverse_strrchr( $posted_value, "_" );
								}

							}
							if ( $operator == 'isnotempty' ) {
								$all_checked = $all_checked && $checkbox_checked_length > 0;
							} elseif ( $operator == 'isempty' ) {
								$all_checked = $all_checked && $checkbox_checked_length == 0;
							}
							break;
						case "select":
						case "textarea":
						case "textfield":
						case "color":
							$element_to_check .= $form_prefix;
							if ( isset( $_POST[ $element_to_check ] ) ) {
								$posted_value = $_POST[ $element_to_check ];
								$posted_value = stripslashes( $posted_value );
								if ( $element_type == "select" ) {
									$posted_value = TM_EPO_HELPER()->encodeURIComponent( $posted_value );
									$posted_value = TM_EPO_HELPER()->reverse_strrchr( $posted_value, "_" );
								}
							}
							break;
					}
					$all_checked = $all_checked && $this->tm_check_match( $posted_value, '', $operator );
				}
			}
		}

		return $all_checked;

	}

	/** Conditional logic **/
	public function tm_check_field_match( $rule = FALSE, $sections = FALSE, $form_prefix = "" ) {

		if ( empty( $rule ) || empty( $sections ) ) {
			return FALSE;
		}

		$section_id = $rule->section;
		$element_id = $rule->element;
		$operator = $rule->operator;
		$value = $rule->value;

		if ( $section_id == $element_id ) {
			return $this->tm_check_section_match( $element_id, $operator, $rule, $sections, $form_prefix );
		}
		if ( !isset( $sections[ $section_id ] )
			|| !isset( $sections[ $section_id ]['elements'] )
			|| !isset( $sections[ $section_id ]['elements'][ $element_id ] )
			|| !isset( $sections[ $section_id ]['elements'][ $element_id ]['type'] )
		) {
			return FALSE;
		}

		// variations logic
		if ( $sections[ $section_id ]['elements'][ $element_id ]['type'] == "variations" ) {
			return $this->tm_variation_check_match( $form_prefix, $value, $operator );
		}

		if ( !isset( $sections[ $section_id ]['elements'][ $element_id ]['name_inc'] ) ) {
			return FALSE;
		}

		// element array cannot hold the form_prefix for bto support, so we append manually
		$element_to_check = $sections[ $section_id ]['elements'][ $element_id ]['name_inc'];
		$element_type = $sections[ $section_id ]['elements'][ $element_id ]['type'];
		$posted_value = NULL;

		switch ( $element_type ) {
			case "radio":
				$radio_checked_length = 0;
				$element_to_check = array_unique( $element_to_check );

				$element_to_check = $element_to_check[0] . $form_prefix;

				if ( isset( $_POST[ $element_to_check ] ) ) {
					$radio_checked_length++;
					$posted_value = $_POST[ $element_to_check ];
					$posted_value = stripslashes( $posted_value );
					$posted_value = TM_EPO_HELPER()->encodeURIComponent( $posted_value );
					$posted_value = TM_EPO_HELPER()->reverse_strrchr( $posted_value, "_" );
				}
				if ( $operator == 'is' || $operator == 'isnot' ) {
					if ( $radio_checked_length == 0 ) {
						return FALSE;
					}
				} elseif ( $operator == 'isnotempty' ) {
					return $radio_checked_length > 0;
				} elseif ( $operator == 'isempty' ) {
					return $radio_checked_length == 0;
				}
				break;
			case "checkbox":
				$checkbox_checked_length = 0;
				$ret = FALSE;
				$element_to_check = array_unique( $element_to_check );
				foreach ( $element_to_check as $key => $name_value ) {
					$element_to_check[ $key ] = $name_value . $form_prefix;
					if ( isset( $_POST[ $element_to_check[ $key ] ] ) ) {
						$checkbox_checked_length++;
						$posted_value = $_POST[ $element_to_check[ $key ] ];
						$posted_value = stripslashes( $posted_value );
						$posted_value = TM_EPO_HELPER()->encodeURIComponent( $posted_value );
						$posted_value = TM_EPO_HELPER()->reverse_strrchr( $posted_value, "_" );
					}
					if ( $this->tm_check_match( $posted_value, $value, $operator ) ) {
						$ret = TRUE;
					}
				}
				if ( $operator == 'is' || $operator == 'isnot' ) {
					if ( $checkbox_checked_length == 0 ) {
						return FALSE;
					}

					return $ret;
				} elseif ( $operator == 'isnotempty' ) {
					return $checkbox_checked_length > 0;
				} elseif ( $operator == 'isempty' ) {
					return $checkbox_checked_length == 0;
				}
				break;
			case "select":
			case "textarea":
			case "textfield":
			case "color":
			case "range":
				$element_to_check .= $form_prefix;
				if ( isset( $_POST[ $element_to_check ] ) ) {
					$posted_value = $_POST[ $element_to_check ];
					$posted_value = stripslashes( $posted_value );
					if ( $element_type == "select" ) {
						$posted_value = TM_EPO_HELPER()->encodeURIComponent( $posted_value );
						$posted_value = TM_EPO_HELPER()->reverse_strrchr( $posted_value, "_" );
					}
				}
				break;
		}

		return $this->tm_check_match( $posted_value, $value, $operator );

	}

	/** Conditional logic **/
	public function tm_variation_check_match( $form_prefix, $value, $operator ) {

		$posted_value = $this->get_posted_variation_id( $form_prefix );

		return $this->tm_check_match( $posted_value, $value, $operator, TRUE );

	}

	/** Conditional logic **/
	public function tm_check_match( $posted_value, $value, $operator, $include_zero = FALSE ) {

		$posted_value = rawurlencode( apply_filters( 'tm_translate', rawurldecode( $posted_value ) ) );
		$value = rawurlencode( apply_filters( 'tm_translate', rawurldecode( $value ) ) );
		switch ( $operator ) {
			case "is":
				return ($posted_value !== NULL && $value == $posted_value);
				break;
			case "isnot":
				return ($posted_value !== NULL && $value != $posted_value);
				break;
			case "isempty":
				if ( $include_zero ) {
					return (!(($posted_value !== NULL && $posted_value !== '' && $posted_value !== '0' && $posted_value !== 0)));
				}

				return (!(($posted_value !== NULL && $posted_value !== '')));
				break;
			case "isnotempty":
				if ( $include_zero ) {
					return (($posted_value !== NULL && $posted_value !== '' && $posted_value !== '0' && $posted_value !== 0));
				}

				return (($posted_value !== NULL && $posted_value !== ''));
				break;
			case "startswith" :
				return TM_EPO_HELPER()->str_startswith( $posted_value, $value );
				break;
			case "endswith" :
				return TM_EPO_HELPER()->str_endsswith( $posted_value, $value );
				break;
			case "greaterthan" :
				return floatval( $posted_value ) > floatval( $value );
				break;
			case "lessthan" :
				return floatval( $posted_value ) < floatval( $value );
				break;
		}

		return FALSE;

	}

	/** Gets the stored card data for the order again functionality. **/
	public function order_again_cart_item_data( $cart_item_meta, $product, $order ) {

		global $woocommerce;

		// Disable validation
		remove_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 50, 6 );

		$_backup_cart = isset( $product['item_meta']['tmcartepo_data'] ) ? $product['item_meta']['tmcartepo_data'] : FALSE;
		if ( !$_backup_cart ) {
			$_backup_cart = isset( $product['item_meta']['_tmcartepo_data'] ) ? $product['item_meta']['_tmcartepo_data'] : FALSE;
		}
		if ( $_backup_cart && is_array( $_backup_cart ) && isset( $_backup_cart[0] ) ) {
			if ( is_string( $_backup_cart[0] ) ) {
				$_backup_cart = maybe_unserialize( $_backup_cart[0] );
			}
			$cart_item_meta['tmcartepo'] = $_backup_cart;
		}

		$_backup_cart = isset( $product['item_meta']['tmsubscriptionfee_data'] ) ? $product['item_meta']['tmsubscriptionfee_data'] : FALSE;
		if ( !$_backup_cart ) {
			$_backup_cart = isset( $product['item_meta']['_tmsubscriptionfee_data'] ) ? $product['item_meta']['_tmsubscriptionfee_data'] : FALSE;
		}
		if ( $_backup_cart && is_array( $_backup_cart ) && isset( $_backup_cart[0] ) ) {
			if ( is_string( $_backup_cart[0] ) ) {
				$_backup_cart = maybe_unserialize( $_backup_cart[0] );
			}
			$cart_item_meta['tmsubscriptionfee'] = $_backup_cart[0];
		}

		$_backup_cart = isset( $product['item_meta']['tmcartfee_data'] ) ? $product['item_meta']['tmcartfee_data'] : FALSE;
		if ( !$_backup_cart ) {
			$_backup_cart = isset( $product['item_meta']['_tmcartfee_data'] ) ? $product['item_meta']['_tmcartfee_data'] : FALSE;
		}
		if ( $_backup_cart && is_array( $_backup_cart ) && isset( $_backup_cart[0] ) ) {
			if ( is_string( $_backup_cart[0] ) ) {
				$_backup_cart = maybe_unserialize( $_backup_cart[0] );
			}
			$cart_item_meta['tmcartfee'] = $_backup_cart[0];
		}

		return $cart_item_meta;

	}

	/**
	 * Handles the display of all the extra options on the product page.
	 *
	 * IMPORTANT:
	 * We do not support plugins that pollute the global $woocommerce.
	 *
	 */
	public function frontend_display( $product_id = 0, $form_prefix = "", $dummy_prefix = FALSE ) {

		global $product, $woocommerce;
		if ( !property_exists( $woocommerce, 'product_factory' )
			|| $woocommerce->product_factory === NULL
			|| ($this->tm_options_have_been_displayed && (!($this->is_bto || ($this->is_enabled_shortcodes() && !is_product()) || ((is_shop() || is_product_category() || is_product_tag()) && $this->tm_epo_enable_in_shop == "yes"))))
		) {
			return;// bad function call
		}

		$this->tm_epo_fields( $product_id, $form_prefix, FALSE, $dummy_prefix );
		$this->tm_add_inline_style();
		$this->tm_epo_totals( $product_id, $form_prefix );
		if ( !$this->is_bto ) {
			$this->tm_options_have_been_displayed = TRUE;
		}

	}

	/**
	 * @param int $product_id
	 * @param string $form_prefix
	 * @param bool $is_from_shortcode
	 */
	public function tm_epo_totals( $product_id = 0, $form_prefix = "", $is_from_shortcode = FALSE ) {

		global $product, $woocommerce;
		if ( !property_exists( $woocommerce, 'product_factory' )
			|| $woocommerce->product_factory === NULL
			|| ($this->tm_options_totals_have_been_displayed && (!($this->is_bto || (($this->is_enabled_shortcodes() && !$is_from_shortcode) && !is_product()) || ((is_shop() || is_product_category() || is_product_tag()) && $this->tm_epo_enable_in_shop == "yes"))))
		) {
			return;// bad function call
		}
		//if ( !$form_prefix && is_page() ) {
		//	$form_prefix = 'tcform' . $this->epo_internal_counter;
		//}
		$this->print_price_fields( $product_id, $form_prefix, $is_from_shortcode );
		if ( !$this->is_bto && !$is_from_shortcode ) {
			$this->tm_options_totals_have_been_displayed = TRUE;
		}

	}

	public function tm_woocommerce_before_single_product() {

		global $woocommerce;
		if ( !property_exists( $woocommerce, 'product_factory' )
			|| $woocommerce->product_factory === NULL
		) {
			return;// bad function call
		}
		global $product;
		if ( $product ) {
			if ( !is_product() ) {
				$this->tm_variation_css_check( 1, tc_get_id( $product ) );
			}
			$this->current_product_id_to_be_displayed = tc_get_id( $product );
			$this->current_product_id_to_be_displayed_check[ "tc" . "-" . count( $this->current_product_id_to_be_displayed_check ) . "-" . $this->current_product_id_to_be_displayed ] = $this->current_product_id_to_be_displayed;
		}

	}

	public function tm_woocommerce_after_single_product() {

		$this->current_product_id_to_be_displayed = 0;
		$this->tm_options_totals_have_been_displayed = FALSE;

	}

	// Change quantity value when editing a cart item
	public function tm_woocommerce_before_add_to_cart_form() {

		add_filter( 'woocommerce_quantity_input_args', array( $this, 'tm_woocommerce_quantity_input_args' ), 9999, 2 );

	}

	// Remove filter for change quantity value when editing a cart item
	public function tm_woocommerce_after_add_to_cart_form() {

		remove_filter( 'woocommerce_quantity_input_args', array( $this, 'tm_woocommerce_quantity_input_args' ), 9999, 2 );

	}

	/**
	 * @param string $form_prefix
	 * @param bool $dummy_prefix
	 */
	private function tm_epo_fields_batch( $form_prefix = "", $dummy_prefix = FALSE ) {

		foreach ( $this->current_product_id_to_be_displayed_check as $key => $product_id ) {
			if ( !empty( $product_id ) ) {
				$this->inline_styles = '';
				$this->inline_styles_head = '';

				$this->tm_variation_css_check( 1, $product_id );

				$this->tm_epo_fields( $product_id, $form_prefix, FALSE, $dummy_prefix );
				$this->tm_add_inline_style();

				if ( $this->tm_epo_options_placement == $this->tm_epo_totals_box_placement ) {
					$this->tm_epo_totals( $product_id, $form_prefix );
				} else {
					if ( !$this->is_bto ) {
						unset( $this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] );
					}
				}
			}
		}
		if ( !$this->is_bto ) {
			if ( $this->tm_epo_options_placement != $this->tm_epo_totals_box_placement ) {
				$this->epo_internal_counter = 0;
				$this->epo_internal_counter_check = array();
			}
		}

	}

	/**
	 * @param int $product_id
	 * @param string $form_prefix
	 * @param bool $is_from_shortcode
	 * @param bool $dummy_prefix
	 */
	public function tm_epo_fields( $product_id = 0, $form_prefix = "", $is_from_shortcode = FALSE, $dummy_prefix = FALSE ) {

		global $woocommerce;

		if ( !property_exists( $woocommerce, 'product_factory' )
			|| $woocommerce->product_factory === NULL
			|| ($this->tm_options_have_been_displayed && (!($this->is_bto || (($this->is_enabled_shortcodes() && !$is_from_shortcode) && !is_product()) || ((is_shop() || is_product_category() || is_product_tag()) && $this->tm_epo_enable_in_shop == "yes"))))
		) {
			return;// bad function call
		}
		if ( !$product_id ) {
			global $product;
			if ( $product ) {
				$product_id = tc_get_id( $product );
			}
		} else {
			$product = wc_get_product( $product_id );
		}
		if ( !$product_id || empty( $product ) ) {
			if ( !empty( $this->current_product_id_to_be_displayed ) ) {
				$product_id = $this->current_product_id_to_be_displayed;
				$product = wc_get_product( $product_id );
			} else {
				$this->tm_epo_fields_batch( $form_prefix, $dummy_prefix );

				return;
			}
		}
		if ( !$product_id || empty( $product ) ) {
			return;
		}

		// always dispay composite hidden fields if product is composite
		if ( $form_prefix ) {
			$_bto_id = $form_prefix;
			$form_prefix = "_" . $form_prefix;
			echo '<input type="hidden" class="cpf-bto-id" name="cpf_bto_id[]" value="' . $form_prefix . '" />';
			echo '<input type="hidden" value="" name="cpf_bto_price[' . $_bto_id . ']" class="cpf-bto-price" />';
			echo '<input type="hidden" value="0" name="cpf_bto_optionsprice[]" class="cpf-bto-optionsprice" />';
		}

		$post_id = $product_id;

		$cpf_price_array = $this->get_product_tm_epos( $post_id );

		if ( !$cpf_price_array ) {
			return;
		}
		$global_price_array = $cpf_price_array['global'];
		$local_price_array = $cpf_price_array['local'];
		if ( empty( $global_price_array ) && empty( $local_price_array ) ) {
			if ( !$this->is_bto ) {
				if ( empty( $this->epo_internal_counter ) || !isset( $this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] ) ) {
					// First time displaying the fields and totals havenn't been displayed
					$this->epo_internal_counter++;
					$this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] = $this->epo_internal_counter;
				} else {
					// Totals have already been displayed
					unset( $this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] );

					$this->current_product_id_to_be_displayed = 0;
				}
				$_epo_internal_counter = $this->epo_internal_counter;
			} else {
				$_epo_internal_counter = 0;
			}

			return;
		}

		$global_prices = array( 'before' => array(), 'after' => array() );
		foreach ( $global_price_array as $priority => $priorities ) {
			foreach ( $priorities as $pid => $field ) {
				if ( isset( $field['sections'] ) && is_array( $field['sections'] ) ) {
					foreach ( $field['sections'] as $section_id => $section ) {
						if ( isset( $section['sections_placement'] ) ) {
							$global_prices[ $section['sections_placement'] ][ $priority ][ $pid ]['sections'][ $section_id ] = $section;
						}
					}
				}
			}
		}

		$tabindex = 0;
		$_currency = get_woocommerce_currency_symbol();
		$unit_counter = 0;
		$field_counter = 0;
		$element_counter = 0;

		if ( !$this->is_bto ) {
			if ( empty( $this->epo_internal_counter ) || !isset( $this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] ) ) {
				// First time displaying the fields and totals havenn't been displayed
				$this->epo_internal_counter++;
				$this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] = $this->epo_internal_counter;
			} else {
				// Totals have already been displayed
				unset( $this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] );

				$this->current_product_id_to_be_displayed = 0;
			}
			$_epo_internal_counter = $this->epo_internal_counter;
		} else {
			$_epo_internal_counter = 0;
		}

		if ( !$form_prefix ) {
			if ( $this->wc_vars["is_page"] ) {
				$form_prefix = '_' . 'tcform' . $this->epo_internal_counter;
			}
		}

		$forcart = "main";
		$classcart = "tm-cart-main";
		if ( !empty( $form_prefix ) ) {
			$forcart = $form_prefix;
			$classcart = "tm-cart-" . str_replace( "_", "", $form_prefix );
		}
		$isfromshortcode = "";
		if ( !empty( $is_from_shortcode ) ) {
			$isfromshortcode = " tc-shortcode";
		}
		wc_get_template(
			'tm-start.php',
			array(
				'isfromshortcode'      => $isfromshortcode,
				'classcart'            => $classcart,
				'forcart'              => $forcart,
				'form_prefix'          => str_replace( "_", "", $form_prefix ),
				'product_id'           => $product_id,
				'epo_internal_counter' => $_epo_internal_counter,
				'is_from_shortcode'    => $is_from_shortcode,
			),
			$this->_namespace,
			TM_EPO_TEMPLATE_PATH
		);

		// global options before local
		foreach ( $global_prices['before'] as $priorities ) {
			foreach ( $priorities as $field ) {
				$args = array(
					'tabindex'        => $tabindex,
					'unit_counter'    => $unit_counter,
					'field_counter'   => $field_counter,
					'element_counter' => $element_counter,
					'_currency'       => $_currency,
					'product_id'      => $product_id,
				);
				$_return = $this->get_builder_display( $field, 'before', $args, $form_prefix, $product_id, $dummy_prefix );
				extract( $_return, EXTR_OVERWRITE );
			}
		}

		// local options
		if ( is_array( $local_price_array ) && sizeof( $local_price_array ) > 0 ) {

			$attributes = tc_get_attributes( floatval( TM_EPO_WPML()->get_original_id( $post_id ) ) );
			$wpml_attributes = tc_get_attributes( $post_id );

			if ( is_array( $attributes ) && count( $attributes ) > 0 ) {
				foreach ( $local_price_array as $field ) {
					if ( isset( $field['name'] ) && isset( $attributes[ $field['name'] ] ) && !$attributes[ $field['name'] ]['is_variation'] ) {

						$attribute = $attributes[ $field['name'] ];
						$wpml_attribute = isset( $wpml_attributes[ $field['name'] ] ) ? $wpml_attributes[ $field['name'] ] : array();

						$empty_rules = "";
						if ( isset( $field['rules_filtered'][0] ) ) {
							$empty_rules = esc_html( json_encode( ($field['rules_filtered'][0]) ) );
						}
						$empty_rules_type = "";
						if ( isset( $field['rules_type'][0] ) ) {
							$empty_rules_type = esc_html( json_encode( ($field['rules_type'][0]) ) );
						}

						$args = array(
							'title'      => (!$attribute['is_taxonomy'] && isset( $attributes[ $field['name'] ]["name"] ))
								? esc_html( wc_attribute_label( $attributes[ $field['name'] ]["name"] ) )
								: esc_html( wc_attribute_label( $field['name'] ) ),
							'required'   => esc_html( wc_attribute_label( $field['required'] ) ),
							'field_id'   => 'tm-epo-field-' . $unit_counter,
							'type'       => $field['type'],
							'rules'      => $empty_rules,
							'rules_type' => $empty_rules_type,
						);
						wc_get_template(
							'tm-field-start.php',
							$args,
							$this->_namespace,
							TM_EPO_TEMPLATE_PATH
						);

						$name_inc = "";
						$field_counter = 0;
						if ( $attribute['is_taxonomy'] ) {

							// Terms in current lang
							$_current_terms = TM_EPO_WPML()->get_terms( TM_EPO_WPML()->get_lang(), $attribute['name'], 'orderby=name&hide_empty=0' );
							$_current_terms2 = get_terms( $attribute['name'], 'orderby=name&hide_empty=0' );
							$_current_terms = TM_EPO_WPML()->order_terms( $_current_terms, $_current_terms2 );

							$current_language = apply_filters( 'wpml_current_language', FALSE );
							$default_language = apply_filters( 'wpml_default_language', FALSE );
							do_action( 'wpml_switch_language', $default_language );

							// Terms in default WPML lang
							$_default_terms = TM_EPO_WPML()->get_terms( TM_EPO_WPML()->get_lang(), $attribute['name'], 'orderby=name&hide_empty=0' );
							$_default_terms2 = get_terms( $attribute['name'], 'orderby=name&hide_empty=0' );
							$_default_terms = TM_EPO_WPML()->order_terms( $_default_terms, $_default_terms2 );

							do_action( 'wpml_switch_language', $current_language );

							$_tems_to_use = TM_EPO_WPML()->merge_terms( $_current_terms, $_default_terms );

							$slugs = TM_EPO_WPML()->merge_terms_slugs( $_current_terms, $_default_terms );

							switch ( $field['type'] ) {

								case "select":
									$name_inc = "select_" . $element_counter;
									$tabindex++;

									$args = array(
										'options'         => '',
										'textafterprice'  => '',
										'id'              => 'tmcp_select_' . $tabindex . $form_prefix,
										'name'            => 'tmcp_' . $name_inc . ($dummy_prefix ? "" : $form_prefix),
										'amount'          => '0 ' . $_currency,
										'original_amount' => '0 ' . $_currency,
										'hide_amount'     => !empty( $field['hide_price'] ) ? " hidden" : "",
										'tabindex'        => $tabindex,
									);
									if ( $_tems_to_use && is_array( $_tems_to_use ) ) {
										foreach ( $_tems_to_use as $trid => $term ) {
											if ( !isset( $slugs[ $term->slug ] ) ) {
												$slugs[ $term->slug ] = $term->slug;
											}
											$has_term = has_term( (int) $term->term_id, $attribute['name'], floatval( TM_EPO_WPML()->get_original_id( $post_id ) ) ) ? 1 : 0;

											if ( $has_term ) {
												$wpml_term_id = TM_EPO_WPML()->is_active() ? icl_object_id( $term->term_id, $attribute['name'], FALSE ) : FALSE;
												if ( $wpml_term_id ) {
													$wpml_term = get_term( $wpml_term_id, $attribute['name'] );
												} else {
													$wpml_term = $term;
												}
												
												$args['options'] .= '<option ' . 
													(isset( $_POST[ 'tmcp_' . $name_inc . $form_prefix ] ) ? selected( $_POST[ 'tmcp_' . $name_inc . $form_prefix ], esc_attr( sanitize_title( $term->slug ) ), 0 ) : "") . 
													' value="' . sanitize_title( $term->slug ) . 
													'" data-price="" data-rules="' . 
													(isset( $field['rules_filtered'][ $slugs[ $term->slug ] ] ) 
														? esc_html( json_encode( ($field['rules_filtered'][ $slugs[ $term->slug ] ]) ) ) 
														: ( isset( $field['rules_filtered'][ $term->slug ] ) ?
															esc_html( json_encode($field['rules_filtered'][ $term->slug ])):'') ) . 
													'" data-rulestype="' . 
													(isset( $field['rules_type'][ $slugs[ $term->slug ] ] ) 
														? esc_html( json_encode( ($field['rules_type'][ $slugs[ $term->slug ] ]) ) ) 
														: ( isset( $field['rules_type'][ $term->slug ] ) ?
															esc_html( json_encode($field['rules_type'][ $term->slug ])):'') ) . 
													'">' . wptexturize( $wpml_term->name ) . '</option>';
											}
										}
									}

									wc_get_template(
										'tm-' . $field['type'] . '.php',
										$args,
										$this->_namespace,
										TM_EPO_TEMPLATE_PATH
									);
									$element_counter++;
									break;

								case "radio":
								case "checkbox":
									if ( $_tems_to_use && is_array( $_tems_to_use ) ) {
										foreach ( $_tems_to_use as $trid => $term ) {
											if ( !isset( $slugs[ $term->slug ] ) ) {
												$slugs[ $term->slug ] = $term->slug;
											}

											$has_term = has_term( (int) $term->term_id, $attribute['name'], floatval( TM_EPO_WPML()->get_original_id( $post_id ) ) ) ? 1 : 0;

											if ( $has_term ) {

												$wpml_term_id = TM_EPO_WPML()->is_active() ? icl_object_id( $term->term_id, $attribute['name'], FALSE ) : FALSE;

												if ( $wpml_term_id ) {
													$wpml_term = get_term( $wpml_term_id, $attribute['name'] );
												} else {
													;
													$wpml_term = $term;
												}

												$tabindex++;

												if ( $field['type'] == 'radio' ) {
													$name_inc = "radio_" . $element_counter;
												}
												if ( $field['type'] == 'checkbox' ) {
													$name_inc = "checkbox_" . $element_counter . "_" . $field_counter;
												}

												$args = array(
													'label'           => wptexturize( $wpml_term->name ),
													'textafterprice'  => '',
													'value'           => sanitize_title( $term->slug ),
													'rules'           => (isset( $field['rules_filtered'][ $slugs[ $term->slug ] ] ) 
														? esc_html( json_encode( ($field['rules_filtered'][ $slugs[ $term->slug ] ]) ) ) 
														: ( isset( $field['rules_filtered'][ $term->slug ] ) ?
															esc_html( json_encode($field['rules_filtered'][ $term->slug ])):'') ),
													'rules_type'      => (isset( $field['rules_type'][ $slugs[ $term->slug ] ] ) 
														? esc_html( json_encode( ($field['rules_type'][ $slugs[ $term->slug ] ]) ) ) 
														: ( isset( $field['rules_type'][ $term->slug ] ) ?
															esc_html( json_encode($field['rules_type'][ $term->slug ])):'') ),
													'id'              => 'tmcp_choice_' . $element_counter . "_" . $field_counter . "_" . $tabindex . $form_prefix,
													'name'            => 'tmcp_' . $name_inc . ($dummy_prefix ? "" : $form_prefix),
													'amount'          => '0 ' . $_currency,
													'original_amount' => '0 ' . $_currency,
													'hide_amount'     => !empty( $field['hide_price'] ) ? " hidden" : "",
													'tabindex'        => $tabindex,
													'use_images'      => "",
													'grid_break'      => "",
													'percent'         => "",
													'limit'           => empty( $field['limit'] ) ? "" : $field['limit'],
												);
												wc_get_template(
													'tm-' . $field['type'] . '.php',
													$args,
													$this->_namespace,
													TM_EPO_TEMPLATE_PATH
												);

												$field_counter++;
											}
										}
									}

									$element_counter++;
									break;

							}
						} else {

							$options = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );
							$wpml_options = isset( $wpml_attribute['value'] ) ? array_map( 'trim', explode( WC_DELIMITER, $wpml_attribute['value'] ) ) : $options;

							switch ( $field['type'] ) {

								case "select":
									$name_inc = "select_" . $element_counter;
									$tabindex++;

									$args = array(
										'options'         => '',
										'textafterprice'  => '',
										'id'              => 'tmcp_select_' . $tabindex . $form_prefix,
										'name'            => 'tmcp_' . $name_inc . ($dummy_prefix ? "" : $form_prefix),
										'amount'          => '0 ' . $_currency,
										'original_amount' => '0 ' . $_currency,
										'hide_amount'     => !empty( $field['hide_price'] ) ? " hidden" : "",
										'tabindex'        => $tabindex,
									);
									foreach ( $options as $k => $option ) {
										$args['options'] .= '<option ' . (isset( $_POST[ 'tmcp_' . $name_inc . $form_prefix ] ) ? selected( $_POST[ 'tmcp_' . $name_inc . $form_prefix ], esc_attr( sanitize_title( $option ) ), 0 ) : "") . ' value="' . esc_attr( sanitize_title( $option ) ) . '" data-price="" data-rules="' . (isset( $field['rules_filtered'][ esc_attr( sanitize_title( $option ) ) ] ) ? esc_html( json_encode( ($field['rules_filtered'][ esc_attr( sanitize_title( $option ) ) ]) ) ) : '') . '" data-rulestype="' . (isset( $field['rules_type'][ esc_attr( sanitize_title( $option ) ) ] ) ? esc_html( json_encode( ($field['rules_type'][ esc_attr( sanitize_title( $option ) ) ]) ) ) : '') . '">' . wptexturize( apply_filters( 'woocommerce_tm_epo_option_name', isset( $wpml_options[ $k ] ) ? $wpml_options[ $k ] : $option, NULL, NULL ) ) . '</option>';
									}
									wc_get_template(
										'tm-' . $field['type'] . '.php',
										$args,
										$this->_namespace,
										TM_EPO_TEMPLATE_PATH
									);
									$element_counter++;
									break;

								case "radio":
								case "checkbox":
									foreach ( $options as $k => $option ) {
										$tabindex++;

										if ( $field['type'] == 'radio' ) {
											$name_inc = "radio_" . $element_counter;
										}
										if ( $field['type'] == 'checkbox' ) {
											$name_inc = "checkbox_" . $element_counter . "_" . $field_counter;
										}

										$args = array(
											'label'           => wptexturize( apply_filters( 'woocommerce_tm_epo_option_name', isset( $wpml_options[ $k ] ) ? $wpml_options[ $k ] : $option, NULL, NULL ) ),
											'textafterprice'  => '',
											'value'           => esc_attr( sanitize_title( $option ) ),
											'rules'           => isset( $field['rules_filtered'][ sanitize_title( $option ) ] ) ? esc_html( json_encode( ($field['rules_filtered'][ sanitize_title( $option ) ]) ) ) : '',
											'rules_type'      => isset( $field['rules_type'][ sanitize_title( $option ) ] ) ? esc_html( json_encode( ($field['rules_type'][ sanitize_title( $option ) ]) ) ) : '',
											'id'              => 'tmcp_choice_' . $element_counter . "_" . $field_counter . "_" . $tabindex . $form_prefix,
											'name'            => 'tmcp_' . $name_inc . ($dummy_prefix ? "" : $form_prefix),
											'amount'          => '0 ' . $_currency,
											'original_amount' => '0 ' . $_currency,
											'hide_amount'     => !empty( $field['hide_price'] ) ? " hidden" : "",
											'tabindex'        => $tabindex,
											'use_images'      => "",
											'grid_break'      => "",
											'percent'         => "",
											'limit'           => empty( $field['limit'] ) ? "" : $field['limit'],
										);
										wc_get_template(
											'tm-' . $field['type'] . '.php',
											$args,
											$this->_namespace,
											TM_EPO_TEMPLATE_PATH
										);
										$field_counter++;
									}
									$element_counter++;
									break;

							}
						}

						wc_get_template(
							'tm-field-end.php',
							array(),
							$this->_namespace,
							TM_EPO_TEMPLATE_PATH
						);

						$unit_counter++;
					}
				}
			}
		}

		// global options after local
		foreach ( $global_prices['after'] as $priorities ) {
			foreach ( $priorities as $field ) {
				$args = array(
					'tabindex'        => $tabindex,
					'unit_counter'    => $unit_counter,
					'field_counter'   => $field_counter,
					'element_counter' => $element_counter,
					'_currency'       => $_currency,
					'product_id'      => $product_id,
				);
				$_return = $this->get_builder_display( $field, 'after', $args, $form_prefix, $product_id, $dummy_prefix );
				extract( $_return, EXTR_OVERWRITE );
			}
		}

		wc_get_template(
			'tm-end.php',
			array(),
			$this->_namespace,
			TM_EPO_TEMPLATE_PATH
		);

		$this->tm_options_single_have_been_displayed = TRUE;

	}

	/**
	 * @return bool
	 */
	public function is_supported_quick_view() {

		$theme = $this->get_theme( 'Name' );
		if ( $theme == 'Flatsome' || $theme == "Kleo" || $theme == "Venedor" || $theme == "Elise" || $theme = "Minshop" || $theme = "Porto" ) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * @return array
	 */
	public function css_array() {

		$ext = ".min";
		if ( $this->tm_epo_global_js_css_mode == "dev" ) {
			$ext = "";
		}
		if ( $this->tm_epo_global_js_css_mode == "multiple" || $this->tm_epo_global_js_css_mode == "dev" ) {
			$css_array = array(
				'tc-font-awesome'    => array(
					'src'     => TM_EPO_PLUGIN_URL . '/assets/css/font-awesome' . $ext . '.css',
					'deps'    => FALSE,
					'version' => '4.7',
					'media'   => 'screen',
				),
				'tc-epo-animate-css' => array(
					'src'     => TM_EPO_PLUGIN_URL . '/assets/css/animate' . $ext . '.css',
					'deps'    => FALSE,
					'version' => $this->version,
					'media'   => 'all',
				),
				'tc-epo-css'         => array(
					'src'     => TM_EPO_PLUGIN_URL . '/assets/css/tm-epo' . $ext . '.css',
					'deps'    => FALSE,
					'version' => $this->version,
					'media'   => 'all',
				),
				'tc-spectrum-css'    => array(
					'src'     => TM_EPO_PLUGIN_URL . '/assets/css/tm-spectrum' . $ext . '.css',
					'deps'    => FALSE,
					'version' => '1.7.1',
					'media'   => 'screen',
				),
			);
			if ( !is_product() || in_array( "slider", $this->current_option_features ) ) {
				$css_array['tc-owl-carousel-css'] = array(
					'src'     => TM_EPO_PLUGIN_URL . '/assets/css/owl.carousel' . $ext . '.css',
					'deps'    => FALSE,
					'version' => '2.2',
					'media'   => 'all',
				);
				$css_array['tc-owl-carousel-theme-css'] = array(
					'src'     => TM_EPO_PLUGIN_URL . '/assets/css/owl.theme.default' . $ext . '.css',
					'deps'    => FALSE,
					'version' => '2.2',
					'media'   => 'all',
				);
			}
		} else {
			$css_array = array(
				'tc-epo-css' => array(
					'src'     => TM_EPO_PLUGIN_URL . '/assets/css/epo.min.css',
					'deps'    => FALSE,
					'version' => $this->version,
					'media'   => 'all',
				),
			);
		}

		return $css_array;

	}

	public function custom_frontend_scripts() {

		$this->defered_files = array();
		$ext = ".min";
		if ( $this->tm_epo_global_js_css_mode == "dev" ) {
			$ext = "";
		}
		do_action( 'tm_epo_register_addons_scripts' );
		if ( apply_filters( 'wc_epo_register_addons_scripts', FALSE ) ) {
			return;
		}
		$product = wc_get_product();

		if ( $enqueue_styles = apply_filters( 'tm_epo_enqueue_styles', $this->css_array() ) ) {
			foreach ( $enqueue_styles as $handle => $args ) {
				wp_enqueue_style( $handle, $args['src'], $args['deps'], $args['version'], $args['media'] );
			}
			if ( is_rtl() ) {
				wp_enqueue_style( 'tm-epo-css-rtl', TM_EPO_PLUGIN_URL . '/assets/css/tm-epo-rtl' . $ext . '.css', FALSE, $this->version, 'all' );
			}
		}

		add_filter( 'woocommerce_price_trim_zeros', '__return_true' );
		$zero_price = wc_price( 123456789, array(
			'currency'           => '',
			'decimal_separator'  => '.',
			'thousand_separator' => ',',
			'decimals'           => 0,
		) );
		remove_filter( 'woocommerce_price_trim_zeros', '__return_true' );
		$formatted_price = str_replace( '123,456,789', '{{{ data.price }}}', $zero_price );
		$suffix = '';
		if ( $product ) {
			$suffix = $product->get_price_suffix();
			$formatted_price .= $suffix;
		}
		wc_get_template( 'tc-js-templates.php', array( 'formatted_price' => $formatted_price ), $this->_namespace, TM_EPO_TEMPLATE_PATH );

		$dependencies = array();
		$dependencies[] = 'jquery-ui-slider';
		$dependencies[] = 'wp-util';
		$dependencies[] = 'jquery';
		if ( $this->tm_epo_global_js_css_mode == "multiple" || $this->tm_epo_global_js_css_mode == "dev" ) {

			$dependencies[] = 'tm-scripts';
			wp_register_script( 'tm-scripts', TM_EPO_PLUGIN_URL . '/assets/js/tm-scripts' . $ext . '.js', '', $this->version, TRUE );

			if ( !is_product() || (in_array( "date", $this->current_option_features ) || in_array( "time", $this->current_option_features )) ) {
				$dependencies[] = 'jquery-ui-core';
				$dependencies[] = 'tm-datepicker';
				$this->defered_files[] = TM_EPO_PLUGIN_URL . '/assets/js/tm-datepicker' . $ext . '.js';
				wp_deregister_script( 'tm-datepicker' );
				wp_register_script( 'tm-datepicker', TM_EPO_PLUGIN_URL . '/assets/js/tm-datepicker' . $ext . '.js', array( 'jquery', 'jquery-ui-core' ), $this->version, TRUE );
				wp_enqueue_script( 'tm-datepicker' );
			}
			if ( !is_product() || in_array( "time", $this->current_option_features ) ) {
				$dependencies[] = 'jquery-ui-core';
				$dependencies[] = 'tm-datepicker';
				$dependencies[] = 'tm-timepicker';
				$this->defered_files[] = TM_EPO_PLUGIN_URL . '/assets/js/tm-timepicker' . $ext . '.js';
				wp_deregister_script( 'tm-timepicker' );
				wp_register_script( 'tm-timepicker', TM_EPO_PLUGIN_URL . '/assets/js/tm-timepicker' . $ext . '.js', array( 'jquery', 'jquery-ui-core', 'tm-datepicker' ), $this->version, TRUE );
				wp_enqueue_script( 'tm-timepicker' );
			}
			if ( !is_product() || in_array( "slider", $this->current_option_features ) ) {
				$dependencies[] = 'tm-owl-carousel';
				$this->defered_files[] = TM_EPO_PLUGIN_URL . '/assets/js/owl.carousel' . $ext . '.js';
				wp_deregister_script( 'tm-owl-carousel' );
				wp_register_script( 'tm-owl-carousel', TM_EPO_PLUGIN_URL . '/assets/js/owl.carousel' . $ext . '.js', array( 'jquery' ), $this->version, TRUE );
				wp_enqueue_script( 'tm-owl-carousel' );
			}

			$dependencies = array_unique( $dependencies );
			$this->defered_files[] = TM_EPO_PLUGIN_URL . '/assets/js/tm-epo' . $ext . '.js';
			wp_deregister_script( 'tm-epo' );
			wp_register_script( 'tm-epo', TM_EPO_PLUGIN_URL . '/assets/js/tm-epo' . $ext . '.js', $dependencies, $this->version, TRUE );
			wp_enqueue_script( 'tm-epo' );

		} else {
			$dependencies[] = 'jquery-ui-core';
			$this->defered_files[] = TM_EPO_PLUGIN_URL . '/assets/js/epo.min.js';
			wp_deregister_script( 'tm-epo' );
			wp_register_script( 'tm-epo', TM_EPO_PLUGIN_URL . '/assets/js/epo.min.js', $dependencies, $this->version, TRUE );
			wp_enqueue_script( 'tm-epo' );
		}

		$extra_fee = 0;
		global $wp_locale;
		$args = array(
			'ajax_url'                  => admin_url( 'admin-ajax' ) . '.php',//WPML 3.3.3 fix
			'extra_fee'                 => apply_filters( 'woocommerce_tm_final_price_extra_fee', $extra_fee, $product ),
			'i18n_extra_fee'            => __( 'Extra fee', 'woocommerce-tm-extra-product-options' ),
			'i18n_unit_price'        	=> (!empty( $this->tm_epo_options_unit_price_text )) ? $this->tm_epo_options_unit_price_text : __( 'Unit price', 'woocommerce-tm-extra-product-options' ),
			'i18n_options_total'        => (!empty( $this->tm_epo_options_total_text )) ? $this->tm_epo_options_total_text : __( 'Options amount', 'woocommerce-tm-extra-product-options' ),
			'i18n_fees_total'        	=> (!empty( $this->tm_epo_fees_total_text )) ? $this->tm_epo_fees_total_text : __( 'Fees amount', 'woocommerce-tm-extra-product-options' ),
			'i18n_final_total'          => (!empty( $this->tm_epo_final_total_text )) ? $this->tm_epo_final_total_text : __( 'Final total', 'woocommerce-tm-extra-product-options' ),
			'i18n_prev_text'            => (!empty( $this->tm_epo_slider_prev_text )) ? $this->tm_epo_slider_prev_text : __( 'Prev', 'woocommerce-tm-extra-product-options' ),
			'i18n_next_text'            => (!empty( $this->tm_epo_slider_next_text )) ? $this->tm_epo_slider_next_text : __( 'Next', 'woocommerce-tm-extra-product-options' ),
			'i18n_sign_up_fee'          => (!empty( $this->tm_epo_subscription_fee_text )) ? $this->tm_epo_subscription_fee_text : __( 'Sign up fee', 'woocommerce-tm-extra-product-options' ),
			'i18n_cancel'               => __( 'Cancel', 'woocommerce-tm-extra-product-options' ),
			'i18n_close'                => (!empty( $this->tm_epo_close_button_text )) ? $this->tm_epo_close_button_text : __( 'Close', 'woocommerce-tm-extra-product-options' ),
			'i18n_addition_options'     => (!empty( $this->tm_epo_additional_options_text )) ? $this->tm_epo_additional_options_text : __( 'Additional options', 'woocommerce-tm-extra-product-options' ),
			'i18n_characters_remaining' => (!empty( $this->tm_epo_characters_remaining_text )) ? $this->tm_epo_characters_remaining_text : __( 'characters remaining', 'woocommerce-tm-extra-product-options' ),

			'i18n_option_label' => __( 'Label', 'woocommerce-tm-extra-product-options' ),
			'i18n_option_value' => __( 'Value', 'woocommerce-tm-extra-product-options' ),
			'i18n_option_qty'   => __( 'Qty', 'woocommerce-tm-extra-product-options' ),
			'i18n_option_price' => __( 'Price', 'woocommerce-tm-extra-product-options' ),

			'i18n_uploading_files'   => (!empty( $this->tm_epo_uploading_files_text )) ? $this->tm_epo_uploading_files_text : __( 'Uploading files', 'woocommerce-tm-extra-product-options' ),
			'i18n_uploading_message' => (!empty( $this->tm_epo_uploading_message_text )) ? $this->tm_epo_uploading_message_text : __( 'Your files are being uploaded', 'woocommerce-tm-extra-product-options' ),

			'currency_format_num_decimals'              => apply_filters( 'wc_epo_price_decimals', wc_get_price_decimals()),
			'currency_format_symbol'                    => get_woocommerce_currency_symbol(),
			'currency_format_decimal_sep'               => esc_attr( stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ) ),
			'currency_format_thousand_sep'              => esc_attr( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ) ),
			'currency_format'                           => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ),
			'css_styles'                                => $this->tm_epo_css_styles,
			'css_styles_style'                          => $this->tm_epo_css_styles_style,
			'tm_epo_options_placement'                  => $this->tm_epo_options_placement,
			'tm_epo_totals_box_placement'               => $this->tm_epo_totals_box_placement,
			'tm_epo_no_lazy_load'                       => $this->tm_epo_no_lazy_load,
			'tm_epo_show_only_active_quantities'        => $this->tm_epo_show_only_active_quantities,
			'tm_epo_hide_add_cart_button'               => $this->tm_epo_hide_add_cart_button,
			'tm_epo_auto_hide_price_if_zero'            => $this->tm_epo_auto_hide_price_if_zero,
			"tm_epo_global_enable_validation"           => $this->tm_epo_global_enable_validation,
			"tm_epo_global_input_decimal_separator"     => $this->tm_epo_global_input_decimal_separator,
			"tm_epo_global_displayed_decimal_separator" => $this->tm_epo_global_displayed_decimal_separator,
			"tm_epo_remove_free_price_label"            => $this->tm_epo_remove_free_price_label,
			"tm_epo_global_product_image_selector"      => $this->tm_epo_global_product_image_selector,
			"tm_epo_global_product_image_mode"          => $this->tm_epo_global_product_image_mode,
			"tm_epo_global_move_out_of_stock"           => $this->tm_epo_global_move_out_of_stock,
			"tm_epo_progressive_display"           		=> $this->tm_epo_progressive_display,
			"tm_epo_animation_delay"           			=> $this->tm_epo_animation_delay,
			"tm_epo_start_animation_delay"          	=> $this->tm_epo_start_animation_delay,

			"tm_epo_global_validator_messages" => array(
				"required"  => (!empty( $this->tm_epo_this_field_is_required_text )) ? $this->tm_epo_this_field_is_required_text : __( "This field is required.", 'woocommerce-tm-extra-product-options' ),
				"email"     => __( "Please enter a valid email address.", 'woocommerce-tm-extra-product-options' ),
				"url"       => __( "Please enter a valid URL.", 'woocommerce-tm-extra-product-options' ),
				"number"    => __( "Please enter a valid number.", 'woocommerce-tm-extra-product-options' ),
				"digits"    => __( "Please enter only digits.", 'woocommerce-tm-extra-product-options' ),
				"maxlength" => __( "Please enter no more than {0} characters.", 'woocommerce-tm-extra-product-options' ),
				"minlength" => __( "Please enter at least {0} characters.", 'woocommerce-tm-extra-product-options' ),
				"max"       => __( "Please enter a value less than or equal to {0}.", 'woocommerce-tm-extra-product-options' ),
				"min"       => __( "Please enter a value greater than or equal to {0}.", 'woocommerce-tm-extra-product-options' ),

				"epolimit" => __( "Please select up to {0} choices.", 'woocommerce-tm-extra-product-options' ),
				"epoexact" => __( "Please select exactly {0} choices.", 'woocommerce-tm-extra-product-options' ),
				"epomin"   => __( "Please select at least {0} choices.", 'woocommerce-tm-extra-product-options' ),

				"step"   => __( "Please enter a multiple of {0}.", 'woocommerce-tm-extra-product-options' ),
				"lettersonly"   => __( "Please enter only letters.", 'woocommerce-tm-extra-product-options' ),
				"lettersspaceonly"   => __( "Please enter only letters or spaces.", 'woocommerce-tm-extra-product-options' ),
				"alphanumeric"   => __( "Letters, numbers, and underscores only please", 'woocommerce-tm-extra-product-options' ),
			),

			'first_day'       => intval( get_option( 'start_of_week' ) ),
			'monthNames'      => $this->strip_array_indices( $wp_locale->month ),
			'monthNamesShort' => $this->strip_array_indices( $wp_locale->month_abbrev ),
			'dayNames'        => $this->strip_array_indices( $wp_locale->weekday ),
			'dayNamesShort'   => $this->strip_array_indices( $wp_locale->weekday_abbrev ),
			'dayNamesMin'     => $this->strip_array_indices( $wp_locale->weekday_initial ),
			'isRTL'           => $wp_locale->text_direction == 'rtl',
			'text_direction'  => $wp_locale->text_direction,
			'is_rtl'          => is_rtl(),
			'closeText'       => (!empty( $this->tm_epo_closeText )) ? $this->tm_epo_closeText : __( 'Done', 'woocommerce-tm-extra-product-options' ),
			'currentText'     => (!empty( $this->tm_epo_currentText )) ? $this->tm_epo_currentText : __( 'Today', 'woocommerce-tm-extra-product-options' ),

			'hourText'   => __( 'Hour', 'woocommerce-tm-extra-product-options' ),
			'minuteText' => __( 'Minute', 'woocommerce-tm-extra-product-options' ),
			'secondText' => __( 'Second', 'woocommerce-tm-extra-product-options' ),

			'floating_totals_box'               => $this->tm_epo_floating_totals_box,
			'floating_totals_box_visibility'    => $this->tm_epo_floating_totals_box_visibility,
			'floating_totals_box_add_button'    => $this->tm_epo_floating_totals_box_add_button,
			'floating_totals_box_html_before'   => apply_filters( 'floating_totals_box_html_before', '' ),
			'floating_totals_box_html_after'    => apply_filters( 'floating_totals_box_html_after', '' ),
			'tm_epo_show_unit_price' 			=> $this->tm_epo_show_unit_price,
			'tm_epo_fees_on_unit_price' 		=> $this->tm_epo_fees_on_unit_price,
			'tm_epo_enable_final_total_box_all' => $this->tm_epo_enable_final_total_box_all,
			'tm_epo_change_original_price'      => $this->tm_epo_change_original_price,
			'tm_epo_change_variation_price'     => $this->tm_epo_change_variation_price,
			'tm_epo_enable_in_shop'             => $this->tm_epo_enable_in_shop,
			'tm_epo_disable_error_scroll' 		=> $this->tm_epo_disable_error_scroll,

			'tm_epo_upload_popup' => $this->tm_epo_upload_popup,

			'current_free_text' => $this->current_free_text,
			'quickview_container' => esc_html( json_encode( apply_filters( 'wc_epo_js_quickview_container', array( ) ) ) ),

			'wc_booking_person_qty_multiplier' => isset( $this->tm_epo_bookings_person ) && ($this->tm_epo_bookings_person == "yes") ? 1 : 0,
			'wc_booking_block_qty_multiplier'  => isset( $this->tm_epo_bookings_block ) && ($this->tm_epo_bookings_block == "yes") ? 1 : 0,


		);
	
		$args = apply_filters( 'wc_epo_script_args', $args, $this );

		wp_localize_script( 'tm-epo', 'tm_epo_js', $args );

	}

	/**
	 * Format array for the datepicker
	 *
	 * WordPress stores the locale information in an array with a alphanumeric index, and
	 * the datepicker wants a numerical index. This function replaces the index with a number
	 */
	private function strip_array_indices( $ArrayToStrip = array() ) {

		$NewArray = array();
		foreach ( $ArrayToStrip as $objArrayItem ) {
			$NewArray[] = $objArrayItem;
		}

		return ($NewArray);

	}

	/**
	 * @param string $form_prefix
	 */
	private function tm_epo_totals_batch( $form_prefix = "" ) {

		foreach ( $this->current_product_id_to_be_displayed_check as $key => $product_id ) {
			if ( !empty( $product_id ) ) {
				$this->print_price_fields( $product_id, $form_prefix );
				if ( $this->tm_epo_options_placement != $this->tm_epo_totals_box_placement ) {
					if ( !$this->is_bto ) {
						unset( $this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] );
					}
				}
			}
		}
		if ( !$this->is_bto ) {
			if ( $this->tm_epo_options_placement != $this->tm_epo_totals_box_placement ) {
				$this->epo_internal_counter = 0;
				$this->epo_internal_counter_check = array();
			}
		}

	}

	/**
	 * @param $classes
	 * @return int
	 */
	public function get_tax_rate( $classes ) {

		$tax_rate = 0;
		if ( class_exists( 'WC_Tax' ) ) {
			$_tax = new WC_Tax();
			$taxrates = $_tax->get_rates( $classes );
			unset( $_tax );
			$tax_rate = 0;
			foreach ( $taxrates as $key => $value ) {
				$tax_rate = $tax_rate + floatval( $value['rate'] );
			}
		}

		return $tax_rate;

	}

	/**
	 * @param int $product_id
	 * @param string $form_prefix
	 * @param bool $is_from_shortcode
	 */
	private function print_price_fields( $product_id = 0, $form_prefix = "", $is_from_shortcode = FALSE ) {

		if ( !$product_id ) {
			global $product;
			if ( $product ) {
				$product_id = tc_get_id( $product );
			}
		} else {
			$product = wc_get_product( $product_id );
		}
		if ( !$product_id || empty( $product ) ) {
			if ( !empty( $this->current_product_id_to_be_displayed ) ) {
				$product_id = $this->current_product_id_to_be_displayed;
				$product = wc_get_product( $product_id );
			} else {
				$this->tm_epo_totals_batch( $form_prefix );

				return;
			}
		}
		if ( !$product_id || empty( $product ) ) {
			return;
		}

		$cpf_price_array = $this->get_product_tm_epos( $product_id );
		if ( !$cpf_price_array ) {
			return;
		}

		if ( $cpf_price_array && $this->tm_epo_enable_final_total_box_all == "no" ) {
			$global_price_array = $cpf_price_array['global'];
			$local_price_array = $cpf_price_array['local'];
			if ( empty( $global_price_array ) && empty( $local_price_array ) ) {
				if ( !$this->is_bto ) {
					if ( empty( $this->epo_internal_counter ) || !isset( $this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] ) ) {
						// First time displaying totals and fields haven't been displayed
						$this->epo_internal_counter++;
						$this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] = $this->epo_internal_counter;
					} else {
						// Fields have already been displayed
						unset( $this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] );
						$this->current_product_id_to_be_displayed = 0;
					}
					$_epo_internal_counter = $this->epo_internal_counter;
				} else {
					$_epo_internal_counter = 0;
				}

				return;
			}
		}
		if ( !$cpf_price_array && $this->tm_epo_enable_final_total_box_all == "no" ) {
			return;
		}

		$force_quantity = 0;
		if ( $this->cart_edit_key ) {
			$cart_item_key = $this->cart_edit_key;
			$cart_item = WC()->cart->get_cart_item( $cart_item_key );

			if ( isset( $cart_item["quantity"] ) ) {
				$force_quantity = $cart_item["quantity"];
			}
		}
		if ( !$this->is_bto ) {
			if ( empty( $this->epo_internal_counter ) || !isset( $this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] ) ) {
				// First time displaying totals and fields haven't been displayed
				$this->epo_internal_counter++;
				$this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] = $this->epo_internal_counter;
			} else {
				// Fields have already been displayed
				unset( $this->epo_internal_counter_check[ "tc" . $this->epo_internal_counter ] );
				$this->current_product_id_to_be_displayed = 0;
			}
			$_epo_internal_counter = $this->epo_internal_counter;
		} else {
			$_epo_internal_counter = 0;
		}
		if ( !$form_prefix && $this->wc_vars["is_page"] ) {
			$form_prefix = 'tcform' . $this->epo_internal_counter;
		}

		if ( $form_prefix ) {
			$form_prefix = "_" . $form_prefix;
		}

		if ( function_exists( 'WC_CP' ) && version_compare( WC_CP()->version, "3.8", "<" ) && tc_get_product_type( $product ) == "composite" && is_callable( array( $product, 'get_base_price' ) ) ) {
			$_price = apply_filters( 'woocommerce_tm_epo_price_compatibility', $product->get_base_price(), $product );
		} else {
			$_price = apply_filters( 'woocommerce_tm_epo_price_compatibility', $product->get_price(), $product );
		}

		$price = array();
		$price['product'] = array(); // product price rules
		$price['price'] = apply_filters( 'wc_epo_product_price', $_price, "", FALSE ); // product price

		$price = apply_filters( 'wc_epo_product_price_rules', $price, $product );

		// Woothemes Dynamic Pricing (not yet fully compatible)
		if ( class_exists( 'WC_Dynamic_Pricing' ) ) {
			$id = isset( $product->variation_id ) ? $product->variation_id : tc_get_id( $product );
			$dp = WC_Dynamic_Pricing::instance();
			if ( $dp &&
				is_object( $dp ) && property_exists( $dp, "discounted_products" )
				&& isset( $dp->discounted_products[ $id ] )
			) {
				$_price = $dp->discounted_products[ $id ];
			} else {
				$_price = $product->get_price();
			}
			$price['price'] = apply_filters( 'wc_epo_product_price', $_price, "", FALSE ); // product price
		}

		$variations = array();
		$variations_subscription_period = array();

		$variations_subscription_sign_up_fee = array();
		
		if ( $this->tm_epo_no_variation_prices_array !== 'yes' ){
			$all=get_posts( apply_filters( 'woocommerce_variable_children_args', array(
					'post_parent' => tc_get_id($product),
					'post_type'   => 'product_variation',
					'orderby'     => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
					//'fields'      => 'ids',
					'post_status' => 'publish',
					'numberposts' => -1,
				), $product, false ) );
			
			$all_f=array();
			foreach ( $all as $child ) {
				$all_f[$child->ID] = $child;
			}

			//foreach ( $product->get_children() as $child_id ) {
			foreach ( $all_f as $child_id=>$variation ) {

				/*if (is_object($child_id)){
					$variation = $child_id;
					$child_id = tc_get_id($child_id);
				}else{
					$variation = wc_get_product( $child_id );
				}*/
				$variation = wc_get_product( $child_id );
				/*if ( !$variation || !$variation->exists() ) {
					continue;
				}*/
				if ( class_exists( 'WC_Subscriptions_Product' ) ) {

					$variations_subscription_period[ $child_id ] = WC_Subscriptions_Product::get_price_string(
						$variation,
						array(
							'subscription_price' => FALSE,
							'sign_up_fee'        => FALSE,
							'trial_length'       => FALSE,
							'price'       		 => NULL,
						)
					);
					if ( is_callable( array( 'WC_Subscriptions_Product', 'get_sign_up_fee' ) ) ) {
						$variations_subscription_sign_up_fee[ $child_id ] = WC_Subscriptions_Product::get_sign_up_fee( $variation );
					} else {
						$variations_subscription_sign_up_fee[ $child_id ] = $variation->subscription_sign_up_fee;
					}
				} else {
					$variations_subscription_period[ $child_id ] = '';
					$variations_subscription_sign_up_fee[ $child_id ] = '';
				}

				$variations[ $child_id ] = apply_filters( 'woocommerce_tm_epo_price_compatibility', apply_filters( 'wc_epo_product_price', $variation->get_price(), "", FALSE ), $variation, $child_id );

			}
		}

		$is_subscription = FALSE;
		$subscription_period = '';

		$subscription_sign_up_fee = 0;
		if ( class_exists( 'WC_Subscriptions_Product' ) ) {
			if ( WC_Subscriptions_Product::is_subscription( $product ) ) {
				$is_subscription = TRUE;
				$subscription_period = WC_Subscriptions_Product::get_price_string(
					$product,
					array(
						'subscription_price' => FALSE,
						'sign_up_fee'        => FALSE,
						'trial_length'       => FALSE,
						'price'       		 => NULL,
					)
				);

				$subscription_sign_up_fee = WC_Subscriptions_Product::get_sign_up_fee( $product );
			}
		}

		global $woocommerce;
		$cart = $woocommerce->cart;

		$tax_rate = $this->get_tax_rate( tc_get_tax_class( $product ) );

		$taxable = $product->is_taxable();
		$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
		$tax_string = "";
		if ( $taxable && $this->tm_epo_global_tax_string_suffix == "yes" ) {

			if ( $tax_display_mode == 'excl' ) {

				$tax_string = ' <small>' . apply_filters( 'wc_epo_ex_tax_or_vat_string', WC()->countries->ex_tax_or_vat() ) . '</small>';

			} else {

				$tax_string = ' <small>' . apply_filters( 'inc_tax_or_vat', WC()->countries->inc_tax_or_vat() ) . '</small>';

			}

		}

		$base_taxes_of_one = 0;
		$modded_taxes_of_one = 0;

		$is_vat_exempt = -1;
		$non_base_location_prices = -1;
		$base_tax_rate = $tax_rate;
		if ( class_exists( 'WC_Tax' ) && version_compare( get_option( 'woocommerce_version' ), '2.4', '>=' ) ) {
			$tax_rates = WC_Tax::get_rates( tc_get_tax_class( $product ) );
			$base_tax_rates = WC_Tax::get_base_tax_rates( tc_get_tax_class( $product, 'unfiltered' ) );
			$base_tax_rate = 0;
			foreach ( $base_tax_rates as $key => $value ) {
				$base_tax_rate = $base_tax_rate + floatval( $value['rate'] );
			}
			$is_vat_exempt = (!empty( WC()->customer ) && WC()->customer->is_vat_exempt()) == TRUE ? 1 : 0;
			$non_base_location_prices = ($tax_rates !== $base_tax_rates && apply_filters( 'woocommerce_adjust_non_base_location_prices', TRUE )) == TRUE ? 1 : 0;

			$precision = wc_get_rounding_precision();
			$price_of_one = 1 * (pow( 10, $precision ));

			$base_taxes_of_one = array_sum( WC_Tax::calc_tax( $price_of_one, $base_tax_rates, TRUE ) );
			$modded_taxes_of_one = array_sum( WC_Tax::calc_tax( $price_of_one - $base_taxes_of_one, $tax_rates, FALSE ) );

			$base_taxes_of_one = $base_taxes_of_one / (pow( 10, $precision ));
			$modded_taxes_of_one = $modded_taxes_of_one / (pow( 10, $precision ));

		}

		$forcart = "main";
		$classcart = "tm-cart-main";
		$classtotalform = "tm-totals-form-main";
		$form_prefix_id = str_replace( "_", "", $form_prefix );
		if ( !empty( $form_prefix ) ) {
			$forcart = $form_prefix_id;
			$classcart = "tm-cart-" . $form_prefix_id;
			$classtotalform = "tm-totals-form-" . $form_prefix_id;
		}

		do_action( "wc_epo_before_totals_box", array( 'product_id' => $product_id, 'form_prefix' => $form_prefix, 'is_from_shortcode' => $is_from_shortcode ) );

		wc_get_template(
			'tm-totals.php',
			apply_filters( 'wc_epo_template_args_tm_totals',
				array(

					'classcart'      => $classcart,
					'forcart'        => $forcart,
					'classtotalform' => $classtotalform,

					'theme_name'                          => $this->get_theme( 'Name' ),
					'variations'                          => esc_html( json_encode( (array) $variations ) ),
					'variations_subscription_period'      => esc_html( json_encode( (array) $variations_subscription_period ) ),
					'variations_subscription_sign_up_fee' => esc_html( json_encode( (array) $variations_subscription_sign_up_fee ) ),
					'subscription_period'                 => $subscription_period,
					'subscription_sign_up_fee'            => $subscription_sign_up_fee,
					'is_subscription'                     => $is_subscription,
					'is_sold_individually'                => $product->is_sold_individually(),
					'hidden'                              => ($this->tm_meta_cpf['override_final_total_box']) ? (($this->tm_epo_final_total_box == 'hide' || $this->tm_epo_final_total_box == 'disable' || $this->tm_epo_final_total_box == 'disable_change') ? ' hidden' : '') : (($this->tm_meta_cpf['override_final_total_box'] == 'hide' || $this->tm_meta_cpf['override_final_total_box'] == 'disable' || $this->tm_meta_cpf['override_final_total_box'] == 'disable_change') ? ' hidden' : ''),
					'price_override'                      => ($this->tm_epo_global_override_product_price == 'no')
						? 0
						: (($this->tm_epo_global_override_product_price == 'yes')
							? 1
							: !empty( $this->tm_meta_cpf['price_override'] ) ? 1 : 0),
					'form_prefix'                         => $form_prefix_id,
					'type'                                => esc_html( tc_get_product_type( $product ) ),
					'price'                               => esc_html( (is_object( $product ) ? apply_filters( 'woocommerce_tm_final_price', $price['price'], $product ) : '') ),
					'is_vat_exempt'                       => $is_vat_exempt,
					'non_base_location_prices'            => $non_base_location_prices,
					'taxable'                             => $taxable,
					'tax_display_mode'                    => $tax_display_mode,
					'prices_include_tax'                  => wc_prices_include_tax(),
					'tax_rate'                            => $tax_rate,
					'base_tax_rate'                       => $base_tax_rate,
					'base_taxes_of_one'                   => $base_taxes_of_one,
					'modded_taxes_of_one'                 => $modded_taxes_of_one,
					'tax_string'                          => $tax_string,
					'product_price_rules'                 => esc_html( json_encode( (array) $price['product'] ) ),
					'fields_price_rules'                  => 0,
					'force_quantity'                      => $force_quantity,
					'product_id'                          => $product_id,
					'epo_internal_counter'                => $_epo_internal_counter,
					'is_from_shortcode'                   => $is_from_shortcode,
					'tm_epo_final_total_box'              => (empty( $this->tm_meta_cpf['override_final_total_box'] )) ? $this->tm_epo_final_total_box : $this->tm_meta_cpf['override_final_total_box'],

				) ),
			$this->_namespace,
			TM_EPO_TEMPLATE_PATH
		);

		do_action( "wc_epo_after_totals_box", array( 'product_id' => $product_id, 'form_prefix' => $form_prefix, 'is_from_shortcode' => $is_from_shortcode ) );

	}

	public function woocommerce_available_variation( $array, $class, $variation ){
		
		if ( is_array( $array ) ){

			$tax_rate = $this->get_tax_rate( tc_get_tax_class( $variation ) );

			$base_taxes_of_one = 0;
			$modded_taxes_of_one = 0;

			
			$non_base_location_prices = -1;
			$base_tax_rate = $tax_rate;

			if ( class_exists( 'WC_Tax' ) && version_compare( get_option( 'woocommerce_version' ), '2.4', '>=' ) ) {
				$tax_rates = WC_Tax::get_rates( tc_get_tax_class( $variation ) );
				$base_tax_rates = WC_Tax::get_base_tax_rates( tc_get_tax_class( $variation ) );
				$base_tax_rate = 0;
				foreach ( $base_tax_rates as $key => $value ) {
					$base_tax_rate = $base_tax_rate + floatval( $value['rate'] );
				}
				
				$non_base_location_prices = ($tax_rates !== $base_tax_rates && apply_filters( 'woocommerce_adjust_non_base_location_prices', TRUE )) == TRUE ? 1 : 0;

				$precision = wc_get_rounding_precision();
				$price_of_one = 1 * (pow( 10, $precision ));

				$base_taxes_of_one = array_sum( WC_Tax::calc_tax( $price_of_one, $base_tax_rates, TRUE ) );
				$modded_taxes_of_one = array_sum( WC_Tax::calc_tax( $price_of_one - $base_taxes_of_one, $tax_rates, FALSE ) );

				$base_taxes_of_one = $base_taxes_of_one / (pow( 10, $precision ));
				$modded_taxes_of_one = $modded_taxes_of_one / (pow( 10, $precision ));

			}


			$array["tc_tax_rate"] = $tax_rate;
			$array["tc_is_taxable"] = $variation->is_taxable();
			$array["tc_base_tax_rate"] = $base_tax_rate;
			$array["tc_base_taxes_of_one"] = $base_taxes_of_one;
			$array["tc_modded_taxes_of_one"] = $modded_taxes_of_one;
			$array["tc_non_base_location_prices"] = $non_base_location_prices;
		}

		return $array;

	}

	/**
	 * @param string $var
	 * @return false|string
	 */
	public function get_theme( $var = '' ) {

		$out = '';
		if ( function_exists( 'wp_get_theme' ) ) {
			$theme = wp_get_theme();
			if ( $theme ) {
				$out = $theme->get( $var );
			}
		}

		return $out;

	}

	public function tm_add_inline_style_qv() {

		if ( !empty( $this->inline_styles ) ) {
			echo '<style type="text/css">';
			echo $this->inline_styles;
			echo '</style>';
		}

	}

	/**
	 *
	 */
	public function tm_add_inline_style() {

		if ( !empty( $this->inline_styles ) ) {
			if ( $this->is_quick_view() || $this->is_bto || $this->tm_epo_global_load_generated_styles_inline == "yes" ) {
				$this->tm_add_inline_style_qv();
			} else {
				echo '<script type="text/javascript">';
				echo 'var data="' . $this->inline_styles . '";';
				echo 'jQuery("<style type=\"text/css\">" + data + "</style>").appendTo(document.head);';
				echo '</script>';
			}
		}

	}

	/**
	 * @param $file
	 * @return array|mixed
	 */
	public function upload_file( $file ) {
		if ( is_array( $file ) && !empty( $file['tmp_name'] ) && isset( $this->upload_object[ $file['tmp_name'] ] ) ) {
			$this->upload_object[ $file['tmp_name'] ]['tc'] = TRUE;

			return $this->upload_object[ $file['tmp_name'] ];
		}
		if ( !defined( 'ALLOW_UNFILTERED_UPLOADS' ) ) {
			define( 'ALLOW_UNFILTERED_UPLOADS', TRUE );
		}
		include_once(ABSPATH . 'wp-admin/includes/file.php');
		include_once(ABSPATH . 'wp-admin/includes/media.php');
		add_filter( 'upload_dir', array( $this, 'upload_dir_trick' ) );
		add_filter( 'upload_mimes', array( $this, 'upload_mimes_trick' ) );
		$upload = wp_handle_upload( $file, array( 'test_form' => FALSE ) );
		remove_filter( 'upload_dir', array( $this, 'upload_dir_trick' ) );
		remove_filter( 'upload_mimes', array( $this, 'upload_mimes_trick' ) );

		if ( is_array( $file ) && !empty( $file['tmp_name'] ) ) {
			$this->upload_object[ $file['tmp_name'] ] = $upload;
		}

		return $upload;

	}

	/**
	 * @param array $existing_mimes
	 * @return mixed|void
	 */
	public function upload_mimes_trick( $existing_mimes = array() ) {

		$mimes = array();

		$tm_epo_custom_file_types = $this->tm_epo_custom_file_types;
		$tm_epo_allowed_file_types = $this->tm_epo_allowed_file_types;

		$tm_epo_custom_file_types = explode( ",", $tm_epo_custom_file_types );
		if ( !is_array( $tm_epo_custom_file_types ) ) {
			$tm_epo_custom_file_types = array();
		}
		if ( !is_array( $tm_epo_allowed_file_types ) ) {
			$tm_epo_allowed_file_types = array( "@" );
		}
		$tm_epo_allowed_file_types = array_merge( $tm_epo_allowed_file_types, $tm_epo_custom_file_types );
		$tm_epo_allowed_file_types = array_unique( $tm_epo_allowed_file_types );

		$wp_get_ext_types = wp_get_ext_types();
		$wp_get_mime_types = wp_get_mime_types();

		foreach ( $tm_epo_allowed_file_types as $key => $value ) {
			if ( $value == "@" ) {
				$mimes = $existing_mimes;
			} else {
				$value = ltrim( $value, "@" );
				switch ( $value ) {
					case 'image':
					case 'audio':
					case 'video':
					case 'document':
					case 'spreadsheet':
					case 'interactive':
					case 'text':
					case 'archive':
					case 'code':
						if ( isset( $wp_get_ext_types[ $value ] ) && is_array( $wp_get_ext_types[ $value ] ) ) {
							foreach ( $wp_get_ext_types[ $value ] as $k => $extension ) {
								$type = FALSE;
								foreach ( $wp_get_mime_types as $exts => $_mime ) {
									if ( preg_match( '!^(' . $exts . ')$!i', $extension ) ) {
										$type = $_mime;
										break;
									}
								}
								if ( $type ) {
									$mimes[ $extension ] = $type;
								}
							}
						}
						break;

					default:
						$type = FALSE;
						foreach ( $wp_get_mime_types as $exts => $_mime ) {
							if ( preg_match( '!^(' . $exts . ')$!i', $value ) ) {
								$type = $_mime;
								break;
							}
						}
						if ( $type ) {
							$mimes[ $value ] = $type;
						} else {
							$mimes[ $value ] = "application/octet-stream";
						}
						break;
				}
			}
		}

		return apply_filters( 'wc_epo_upload_mimes', $mimes );

	}

	/**
	 * @param $param
	 * @return mixed
	 */
	public function upload_dir_trick( $param ) {

		global $woocommerce;
		$this->unique_dir = apply_filters( 'wc_epo_upload_unique_dir', md5( $woocommerce->session->get_customer_id() ) );
		$subdir = $this->upload_dir . $this->unique_dir;
		if ( empty( $param['subdir'] ) ) {
			$param['path'] = $param['path'] . $subdir;
			$param['url'] = $param['url'] . $subdir;
			$param['subdir'] = $subdir;
		} else {
			$param['path'] = str_replace( $param['subdir'], $subdir, $param['path'] );
			$param['url'] = str_replace( $param['subdir'], $subdir, $param['url'] );
			$param['subdir'] = str_replace( $param['subdir'], $subdir, $param['subdir'] );
		}

		return $param;

	}

	/**
	 * Append name_inc functions (required for condition logic to check if an element is visible)
	 *
	 * @param int $post_id
	 * @param array $global_epos
	 * @param array $product_epos
	 * @param string $form_prefix
	 * @param string $add_identifier
	 * @return array
	 */
	public function tm_fill_element_names( $post_id = 0, $global_epos = array(), $product_epos = array(), $form_prefix = "", $add_identifier = "" ) {

		$global_price_array = $global_epos;
		$local_price_array = $product_epos;

		$global_prices = array( 'before' => array(), 'after' => array() );
		foreach ( $global_price_array as $priority => $priorities ) {
			foreach ( $priorities as $pid => $field ) {
				if ( isset( $field['sections'] ) && is_array( $field['sections'] ) ) {
					foreach ( $field['sections'] as $section_id => $section ) {
						if ( isset( $section['sections_placement'] ) ) {
							$global_prices[ $section['sections_placement'] ][ $priority ][ $pid ]['sections'][ $section_id ] = $section;
						}
					}
				}
			}
		}
		$unit_counter = 0;
		$field_counter = 0;
		$element_counter = 0;
		// global options before local
		foreach ( $global_prices['before'] as $priority => $priorities ) {
			foreach ( $priorities as $pid => $field ) {
				$args = array(
					'priority'        => $priority,
					'pid'             => $pid,
					'unit_counter'    => $unit_counter,
					'field_counter'   => $field_counter,
					'element_counter' => $element_counter,
				);
				$_return = $this->fill_builder_display( $global_epos, $field, 'before', $args, $form_prefix, $add_identifier );
				extract( $_return, EXTR_OVERWRITE );
			}
		}
		// normal (local) options
		if ( is_array( $local_price_array ) && sizeof( $local_price_array ) > 0 ) {
			$attributes = tc_get_attributes( $post_id );
			if ( is_array( $attributes ) && count( $attributes ) > 0 ) {
				foreach ( $local_price_array as $field ) {
					if ( isset( $field['name'] ) && isset( $attributes[ $field['name'] ] ) && !$attributes[ $field['name'] ]['is_variation'] ) {
						$attribute = $attributes[ $field['name'] ];
						$name_inc = "";
						$field_counter = 0;
						if ( $attribute['is_taxonomy'] ) {
							switch ( $field['type'] ) {
								case "select":
									$element_counter++;
									break;
								case "radio":
								case "checkbox":
									$element_counter++;
									break;
							}
						} else {
							switch ( $field['type'] ) {
								case "select":
									$element_counter++;
									break;
								case "radio":
								case "checkbox":
									$element_counter++;
									break;
							}
						}
						$unit_counter++;
					}
				}
			}
		}
		// global options after normal (local)
		foreach ( $global_prices['after'] as $priority => $priorities ) {
			foreach ( $priorities as $pid => $field ) {
				$args = array(
					'priority'        => $priority,
					'pid'             => $pid,
					'unit_counter'    => $unit_counter,
					'field_counter'   => $field_counter,
					'element_counter' => $element_counter,
				);
				$_return = $this->fill_builder_display( $global_epos, $field, 'after', $args, $form_prefix, $add_identifier );
				extract( $_return, EXTR_OVERWRITE );
			}
		}

		return $global_epos;

	}

	/**
	 * @param string $value
	 * @param string $filter
	 * @return mixed|string|void
	 */
	private function tm_apply_filter( $value = "", $filter = "", $element = "", $element_uniqueid = "" ) {

		if ( !empty( $filter ) ) {
			$value = apply_filters( $filter, $value );
		}

		return apply_filters( "wc_epo_setting", apply_filters( 'tm_translate', $value ), $element, $element_uniqueid );

	}

	/**
	 * @param $element
	 * @param $builder
	 * @param $current_builder
	 * @param bool $index
	 * @param string $alt
	 * @param array $wpml_section_fields
	 * @param string $identifier
	 * @param string $apply_filters
	 * @return mixed|string|void
	 */
	public function get_builder_element( $element, $builder, $current_builder, $index = FALSE, $alt = "", $wpml_section_fields = array(), $identifier = "sections", $apply_filters = "", $element_uniqueid = "" ) {

		$use_wpml = FALSE;
		$use_original_builder = FALSE;
		if ( TM_EPO_WPML()->is_active() && $index !== FALSE ) {
			if ( isset( $current_builder[ $identifier . "_uniqid" ] )
				&& isset( $builder[ $identifier . "_uniqid" ] )
				&& isset( $builder[ $identifier . "_uniqid" ][ $index ] )
			) {
				// get index of element id in internal array
				$get_current_builder_uniqid_index = array_search( $builder[ $identifier . "_uniqid" ][ $index ], $current_builder[ $identifier . "_uniqid" ] );
				if ( $get_current_builder_uniqid_index !== NULL && $get_current_builder_uniqid_index !== FALSE ) {
					$index = $get_current_builder_uniqid_index;
					$use_wpml = TRUE;
				} else {
					$use_original_builder = TRUE;
				}
			}
		}

		if ( isset( $builder[ $element ] ) ) {
			if ( !$use_original_builder && $use_wpml && ((is_array( $wpml_section_fields ) && in_array( $element, $wpml_section_fields )) || $wpml_section_fields === TRUE) ) {
				if ( isset( $current_builder[ $element ] ) ) {
					if ( $index !== FALSE ) {
						if ( isset( $current_builder[ $element ][ $index ] ) ) {
							return $this->tm_apply_filter( TM_EPO_HELPER()->build_array( $current_builder[ $element ][ $index ], $builder[ $element ][ $index ] ), $apply_filters, $element, $element_uniqueid );
						} else {
							return $this->tm_apply_filter( $alt, $apply_filters, $element, $element_uniqueid );
						}
					} else {
						return $this->tm_apply_filter( TM_EPO_HELPER()->build_array( $current_builder[ $element ], $builder[ $element ] ), $apply_filters, $element, $element_uniqueid );
					}
				}
			}
			if ( $index !== FALSE ) {
				if ( isset( $builder[ $element ][ $index ] ) ) {
					return $this->tm_apply_filter( $builder[ $element ][ $index ], $apply_filters, $element, $element_uniqueid );
				} else {
					return $this->tm_apply_filter( $alt, $apply_filters, $element, $element_uniqueid );
				}
			} else {
				return $this->tm_apply_filter( $builder[ $element ], $apply_filters, $element, $element_uniqueid );
			}
		} else {
			return $this->tm_apply_filter( $alt, $apply_filters, $element, $element_uniqueid );
		}

	}

	/**
	 * Gets a list of all the Extra Product Options (normal and global)
	 * for the specific $post_id.
	 */
	public function get_product_tm_epos( $post_id = 0, $form_prefix = "" ) {

		if ( empty( $post_id ) || apply_filters( 'wc_epo_disable', FALSE, $post_id ) || !$this->check_enable() ) {
			return array();
		}

		$post_type = get_post_type( $post_id );

		if ( $post_type !== 'product' ) {
			return array();
		}

		if ( !empty( $this->cpf[ $post_id ] ) ) {
			return $this->cpf[ $post_id ];
		}

		$this->current_option_features = array();

		$this->set_tm_meta( $post_id );

		$in_cat = array();

		$tmglobalprices = array();

		$terms = get_the_terms( $post_id, 'product_cat' );
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$in_cat[] = $term->term_id;
			}
		}

		// get all categories (no matter the language)
		$_all_categories = TM_EPO_WPML()->get_terms( NULL, 'product_cat', array( 'fields' => "ids", 'hide_empty' => FALSE ) );

		if ( !$_all_categories ) {
			$_all_categories = array();
		}

		/* Get Normal (Local) options */
		$args = array(
			'post_type'   => TM_EPO_LOCAL_POST_TYPE,
			'post_status' => array( 'publish' ), // get only enabled extra options
			'numberposts' => -1,
			'orderby'     => 'menu_order',
			'order'       => 'asc', 'suppress_filters' => TRUE,
			'post_parent' => floatval( TM_EPO_WPML()->get_original_id( $post_id ) ),
		);
		TM_EPO_WPML()->remove_sql_filter();
		$tmlocalprices = get_posts( $args );
		TM_EPO_WPML()->restore_sql_filter();

		$tm_meta_cpf_global_forms = (isset( $this->tm_meta_cpf['global_forms'] ) && is_array( $this->tm_meta_cpf['global_forms'] )) ? $this->tm_meta_cpf['global_forms'] : array();
		foreach ( $tm_meta_cpf_global_forms as $key => $value ) {
			$tm_meta_cpf_global_forms[ $key ] = absint( $value );
		}
		$tm_meta_cpf_global_forms_added = array();
		if ( !$this->tm_meta_cpf['exclude'] ) {

			$meta_array = TM_EPO_HELPER()->build_meta_query( 'OR', 'tm_meta_disable_categories', 1, '!=', 'NOT EXISTS' );

			$args = array(
				'post_type'   => TM_EPO_GLOBAL_POST_TYPE,
				'post_status' => array( 'publish' ), // get only enabled global extra options
				'numberposts' => -1,
				'orderby'     => 'date',
				'order'       => 'asc',
				'meta_query'  => $meta_array,
			);

			$args['tax_query'] = array(
				'relation' => 'OR',
				/* Get Global options that belong to the product categories */
				array(
					'taxonomy'         => 'product_cat',
					'field'            => 'term_id',
					'terms'            => $in_cat,
					'operator'         => 'IN',
					'include_children' => FALSE,
				),
				/* Get Global options that have no catergory set (they apply to all products) */
				array(
					'taxonomy'         => 'product_cat',
					'field'            => 'term_id',
					'terms'            => $_all_categories,
					'operator'         => 'NOT IN',
					'include_children' => FALSE,
				),
				array(
					'taxonomy'         => 'product_cat',
					'field'            => 'term_id',
					'operator'         => 'NOT EXISTS',
					'include_children' => FALSE,
				),
			);
			TM_EPO_WPML()->remove_sql_filter();
			TM_EPO_WPML()->remove_term_filters();
			$tmp_tmglobalprices = get_posts( $args );
			TM_EPO_WPML()->restore_term_filters();
			TM_EPO_WPML()->restore_sql_filter();

			if ( $tmp_tmglobalprices ) {
				$wpml_tmp_tmglobalprices = array();
				$wpml_tmp_tmglobalprices_added = array();
				foreach ( $tmp_tmglobalprices as $price ) {

					if ( TM_EPO_WPML()->is_active() ) {
						$price_meta_lang = get_post_meta( $price->ID, TM_EPO_WPML_LANG_META, TRUE );
						$original_product_id = floatval( TM_EPO_WPML()->get_original_id( $price->ID, $price->post_type ) );
						$double_check_disable_categories = get_post_meta( $original_product_id, "tm_meta_disable_categories", TRUE );
						if ( !$double_check_disable_categories ) {
							if ( $price_meta_lang == TM_EPO_WPML()->get_lang()
								|| ($price_meta_lang == '' && TM_EPO_WPML()->get_lang() == TM_EPO_WPML()->get_default_lang())
							) {
								$tmglobalprices[] = $price;
								$tm_meta_cpf_global_forms_added[] = $price->ID;
								if ( $price_meta_lang != TM_EPO_WPML()->get_default_lang() && $price_meta_lang != '' ) {
									$wpml_tmp_tmglobalprices_added[ $original_product_id ] = $price;
								}
							} else {
								if ( $price_meta_lang == TM_EPO_WPML()->get_default_lang() || $price_meta_lang == '' ) {
									$wpml_tmp_tmglobalprices[ $original_product_id ] = $price;
								}
							}
						}
					} else {
						$tmglobalprices[] = $price;
						$tm_meta_cpf_global_forms_added[] = $price->ID;
					}

				}
				// replace missing translation with original
				if ( TM_EPO_WPML()->is_active() ) {
					$wpml_gp_keys = array_keys( $wpml_tmp_tmglobalprices );
					foreach ( $wpml_gp_keys as $key => $value ) {
						if ( !isset( $wpml_tmp_tmglobalprices_added[ $value ] ) ) {
							$tmglobalprices[] = $wpml_tmp_tmglobalprices[ $value ];
							$tm_meta_cpf_global_forms_added[] = $price->ID;
						}
					}
				}

			}

			/* Get Global options that apply to the product */
			$args = array(
				'post_type'   => TM_EPO_GLOBAL_POST_TYPE,
				'post_status' => array( 'publish' ), // get only enabled global extra options
				'numberposts' => -1,
				'orderby'     => 'date',
				'order'       => 'asc',
				'meta_query'  => array(
					array(
						'key'     => 'tm_meta_product_ids',
						'value'   => '"' . $post_id . '";',
						'compare' => 'LIKE',
					),
				),
			);
			$tmglobalprices_products = get_posts( $args );

			/* Merge Global options */
			if ( $tmglobalprices_products ) {
				$global_id_array = array();
				if ( isset( $tmglobalprices ) ) {
					foreach ( $tmglobalprices as $price ) {
						$global_id_array[] = $price->ID;
					}
				} else {
					$tmglobalprices = array();
				}
				foreach ( $tmglobalprices_products as $price ) {
					if ( !in_array( $price->ID, $global_id_array ) ) {
						$tmglobalprices[] = $price;
						$tm_meta_cpf_global_forms_added[] = $price->ID;
					}
				}
			}
		}
		$tm_meta_cpf_global_forms_added = array_unique( $tm_meta_cpf_global_forms_added );
		foreach ( $tm_meta_cpf_global_forms as $key => $value ) {
			if ( !in_array( $value, $tm_meta_cpf_global_forms_added ) ) {
				if ( TM_EPO_WPML()->is_active() ) {

					$tm_meta_lang = get_post_meta( $value, TM_EPO_WPML_LANG_META, TRUE );
					if ( empty( $tm_meta_lang ) ) {
						$tm_meta_lang = TM_EPO_WPML()->get_default_lang();
					}
					$meta_query = TM_EPO_HELPER()->build_meta_query( 'AND', TM_EPO_WPML_LANG_META, TM_EPO_WPML()->get_lang(), '=', 'EXISTS' );
					$meta_query[] = array(
						'key'     => TM_EPO_WPML_PARENT_POSTID,
						'value'   => $value,
						'compare' => '=',
					);
					$query = new WP_Query(
						array(
							'post_type'   => TM_EPO_GLOBAL_POST_TYPE,
							'post_status' => array( 'publish' ),
							'numberposts' => -1,
							'orderby'     => 'date',
							'order'       => 'asc',
							'meta_query'  => $meta_query,
						) );
					if ( !empty( $query->posts ) ) {
						$tmglobalprices[] = get_post( $query->post->ID );
					} elseif ( empty( $query->posts ) ) {
						$tmglobalprices[] = get_post( $value );
					}
				} else {
					$ispostactive = get_post( $value );
					if ( $ispostactive && $ispostactive->post_status == 'publish' ) {
						$tmglobalprices[] = get_post( $value );
					}
				}
			}
		}

		// Add current product to Global options array (has to be last to not conflict)
		$tmglobalprices[] = get_post( $post_id );

		// End of DB init

		$epos = $this->generate_global_epos( $tmglobalprices, $post_id, $this->tm_original_builder_elements );
		$global_epos = $epos['global'];
		$epos_prices = $epos['price'];
		$variation_element_id = $epos['variation_element_id'];
		$variation_section_id = $epos['variation_section_id'];

		if ( is_array( $global_epos ) ) {
			ksort( $global_epos );
		}

		$product_epos = $this->generate_local_epos( $tmlocalprices, $post_id, $this->tm_original_builder_elements );

		$global_epos = $this->tm_fill_element_names( $post_id, $global_epos, $product_epos, $form_prefix, "epo" );

		$epos = array(
			'global'               => $global_epos,
			'local'                => $product_epos,
			'price'                => $epos_prices,
			'variation_element_id' => $variation_element_id,
			'variation_section_id' => $variation_section_id,
		);

		$this->cpf[ $post_id ] = $epos;

		return $epos;

	}

	/**
	 * @param $tmlocalprices
	 * @param $post_id
	 * @return array
	 */
	public function generate_local_epos( $tmlocalprices, $post_id ) {
		$product_epos = array();
		if ( $tmlocalprices ) {
			TM_EPO_WPML()->remove_sql_filter();
			$attributes = tc_get_attributes( floatval( TM_EPO_WPML()->get_original_id( $post_id ) ) );
			$wpml_attributes = tc_get_attributes( $post_id );

			foreach ( $tmlocalprices as $price ) {

				$tmcp_id = absint( $price->ID );

				$n = get_post_meta( $tmcp_id, 'tmcp_attribute', TRUE );
				if ( ! isset($attributes[ $n ]) ){
					continue;
				}
				$att = $attributes[ $n ];
				if ( $att['is_variation'] || sanitize_title( $att['name'] ) != $n ) {
					continue;
				}

				$tmcp_required = get_post_meta( $tmcp_id, 'tmcp_required', TRUE );
				$tmcp_hide_price = get_post_meta( $tmcp_id, 'tmcp_hide_price', TRUE );
				$tmcp_limit = get_post_meta( $tmcp_id, 'tmcp_limit', TRUE );
				$product_epos[ $tmcp_id ]['is_form'] = 0;
				$product_epos[ $tmcp_id ]['required'] = empty( $tmcp_required ) ? 0 : 1;
				$product_epos[ $tmcp_id ]['hide_price'] = empty( $tmcp_hide_price ) ? 0 : 1;
				$product_epos[ $tmcp_id ]['limit'] = empty( $tmcp_limit ) ? "" : $tmcp_limit;
				$product_epos[ $tmcp_id ]['name'] = get_post_meta( $tmcp_id, 'tmcp_attribute', TRUE );
				$product_epos[ $tmcp_id ]['is_taxonomy'] = get_post_meta( $tmcp_id, 'tmcp_attribute_is_taxonomy', TRUE );
				$product_epos[ $tmcp_id ]['label'] = wc_attribute_label( $product_epos[ $tmcp_id ]['name'] );
				$product_epos[ $tmcp_id ]['type'] = get_post_meta( $tmcp_id, 'tmcp_type', TRUE );

				// Retrieve attributes
				$product_epos[ $tmcp_id ]['attributes'] = array();
				$product_epos[ $tmcp_id ]['attributes_wpml'] = array();
				if ( $product_epos[ $tmcp_id ]['is_taxonomy'] ) {
					if ( !($attributes[ $product_epos[ $tmcp_id ]['name'] ]['is_variation']) ) {
						$all_terms = TM_EPO_WPML()->get_terms( NULL, $attributes[ $product_epos[ $tmcp_id ]['name'] ]['name'], 'orderby=name&hide_empty=0' );
						if ( $all_terms ) {
							foreach ( $all_terms as $term ) {
								$has_term = has_term( (int) $term->term_id, $attributes[ $product_epos[ $tmcp_id ]['name'] ]['name'], floatval( TM_EPO_WPML()->get_original_id( $post_id ) ) ) ? 1 : 0;
								$wpml_term_id = TM_EPO_WPML()->is_active() ? icl_object_id( $term->term_id, $attributes[ $product_epos[ $tmcp_id ]['name'] ]['name'], FALSE ) : FALSE;
								if ( $has_term ) {
									$product_epos[ $tmcp_id ]['attributes'][ esc_attr( $term->slug ) ] = apply_filters( 'woocommerce_tm_epo_option_name', esc_html( $term->name ), NULL, NULL );
									if ( $wpml_term_id ) {
										$wpml_term = get_term( $wpml_term_id, $attributes[ $product_epos[ $tmcp_id ]['name'] ]['name'] );
										$product_epos[ $tmcp_id ]['attributes_wpml'][ esc_attr( $term->slug ) ] = apply_filters( 'woocommerce_tm_epo_option_name', esc_html( $wpml_term->name ), NULL, NULL );
									} else {
										;
										$product_epos[ $tmcp_id ]['attributes_wpml'][ esc_attr( $term->slug ) ] = $product_epos[ $tmcp_id ]['attributes'][ esc_attr( $term->slug ) ];
									}
								}
							}
						}

					}
				} else {
					if ( isset( $attributes[ $product_epos[ $tmcp_id ]['name'] ] ) ) {
						$options = array_map( 'trim', explode( WC_DELIMITER, $attributes[ $product_epos[ $tmcp_id ]['name'] ]['value'] ) );
						$wpml_options = isset( $wpml_attributes[ $product_epos[ $tmcp_id ]['name'] ]['value'] ) ? array_map( 'trim', explode( WC_DELIMITER, $wpml_attributes[ $product_epos[ $tmcp_id ]['name'] ]['value'] ) ) : $options;
						foreach ( $options as $k => $option ) {
							$product_epos[ $tmcp_id ]['attributes'][ esc_attr( sanitize_title( $option ) ) ] = esc_html( apply_filters( 'woocommerce_tm_epo_option_name', $option, NULL, NULL ) );
							$product_epos[ $tmcp_id ]['attributes_wpml'][ esc_attr( sanitize_title( $option ) ) ] = esc_html( apply_filters( 'woocommerce_tm_epo_option_name', isset( $wpml_options[ $k ] ) ? $wpml_options[ $k ] : $option, NULL, NULL ) );
						}
					}
				}

				// Retrieve price rules
				$_regular_price = get_post_meta( $tmcp_id, '_regular_price', TRUE );
				$_regular_price_type = get_post_meta( $tmcp_id, '_regular_price_type', TRUE );
				$product_epos[ $tmcp_id ]['rules'] = $_regular_price;

				$_regular_price_filtered = TM_EPO_HELPER()->array_map_deep( $_regular_price, $_regular_price_type, array( $this, 'tm_epo_price_filtered' ) );
				$product_epos[ $tmcp_id ]['rules_filtered'] = $_regular_price_filtered;

				$product_epos[ $tmcp_id ]['rules_type'] = $_regular_price_type;
				if ( !is_array( $_regular_price ) ) {
					$_regular_price = array();
				}
				if ( !is_array( $_regular_price_type ) ) {
					$_regular_price_type = array();
				}
				foreach ( $_regular_price as $key => $value ) {
					foreach ( $value as $k => $v ) {
						$_regular_price[ $key ][ $k ] = wc_format_localized_price( $v );
					}
				}
				foreach ( $_regular_price_type as $key => $value ) {
					foreach ( $value as $k => $v ) {
						$_regular_price_type[ $key ][ $k ] = $v;
					}
				}
				$product_epos[ $tmcp_id ]['price_rules'] = $_regular_price;
				$product_epos[ $tmcp_id ]['price_rules_filtered'] = $_regular_price_filtered;
				$product_epos[ $tmcp_id ]['price_rules_type'] = $_regular_price_type;
			}
			TM_EPO_WPML()->restore_sql_filter();
		}

		return $product_epos;
	}

	/**
	 * @param $tmglobalprices
	 * @param $post_id
	 * @param $tm_original_builder_elements
	 * @return array
	 */
	public function generate_global_epos( $tmglobalprices, $post_id, $tm_original_builder_elements ) {
		$global_epos = array();
		$epos_prices = array();
		$variation_element_id = FALSE;
		$variation_section_id = FALSE;
		if ( $tmglobalprices ) {
			$wpml_section_fields = array();
			foreach ( TM_EPO_BUILDER()->_section_elements as $key => $value ) {
				if ( isset( $value['id'] ) && empty( $value['wpmldisable'] ) ) {
					$wpml_section_fields[ $value['id'] ] = $value['id'];
				}
			}

			foreach ( $tmglobalprices as $price ) {
				if ( !is_object( $price ) ) {
					continue;
				}

				$original_product_id = $price->ID;
				if ( TM_EPO_WPML()->is_active() ) {
					$wpml_is_original_product = TM_EPO_WPML()->is_original_product( $price->ID, $price->post_type );
					if ( !$wpml_is_original_product ) {
						$original_product_id = floatval( TM_EPO_WPML()->get_original_id( $price->ID, $price->post_type ) );
					}
				}

				$tmcp_id = absint( $original_product_id );
				$tmcp_meta = tc_get_post_meta( $tmcp_id, 'tm_meta', TRUE );
				$enabled_roles = tc_get_post_meta( $tmcp_id, 'tm_meta_enabled_roles', TRUE );
				$disabled_roles = tc_get_post_meta( $tmcp_id, 'tm_meta_disabled_roles', TRUE );

				if ( !empty( $enabled_roles ) || !empty( $disabled_roles ) ) {
					$enable = FALSE;
					if ( !is_array( $enabled_roles ) ) {
						$enabled_roles = array( $enabled_roles );
					}
					if ( !is_array( $disabled_roles ) ) {
						$disabled_roles = array( $disabled_roles );
					}
					// Get all roles
					$current_user = wp_get_current_user();

					foreach ( $enabled_roles as $key => $value ) {
						if ( $value == "@everyone" ) {
							$enable = TRUE;
						}
						if ( $value == "@loggedin" && is_user_logged_in() ) {
							$enable = TRUE;
						}
					}

					if ( $current_user instanceof WP_User ) {
						$roles = $current_user->roles;

						if ( is_array( $roles ) ) {

							foreach ( $roles as $key => $value ) {
								if ( in_array( $value, $enabled_roles ) ) {
									$enable = TRUE;
									break;
								}
							}

							foreach ( $roles as $key => $value ) {
								if ( in_array( $value, $disabled_roles ) ) {
									$enable = FALSE;
									break;
								}
							}

						}

					}

					if ( !$enable ) {
						continue;
					}
				}

				$current_builder = tc_get_post_meta( $price->ID, 'tm_meta_wpml', TRUE );
				if ( !$current_builder ) {
					$current_builder = array();
				} else {
					if ( !isset( $current_builder['tmfbuilder'] ) ) {
						$current_builder['tmfbuilder'] = array();
					}
					$current_builder = $current_builder['tmfbuilder'];
				}

				$priority = isset( $tmcp_meta['priority'] ) ? absint( $tmcp_meta['priority'] ) : 1000;

				if ( isset( $tmcp_meta['tmfbuilder'] ) ) {

					$global_epos[ $priority ][ $tmcp_id ]['is_form'] = 1;
					$global_epos[ $priority ][ $tmcp_id ]['is_taxonomy'] = 0;
					$global_epos[ $priority ][ $tmcp_id ]['name'] = $price->post_title;
					$global_epos[ $priority ][ $tmcp_id ]['description'] = $price->post_excerpt;
					$global_epos[ $priority ][ $tmcp_id ]['sections'] = array();

					$builder = $tmcp_meta['tmfbuilder'];
					if ( is_array( $builder ) && count( $builder ) > 0 && isset( $builder['element_type'] ) && is_array( $builder['element_type'] ) && count( $builder['element_type'] ) > 0 ) {
						// All the elements
						$_elements = $builder['element_type'];
						// All element sizes
						$_div_size = $builder['div_size'];

						// All sections (holds element count for each section)
						$_sections = $builder['sections'];
						// All section sizes
						$_sections_size = $builder['sections_size'];
						// All section styles
						$_sections_style = $builder['sections_style'];
						// All section placements
						$_sections_placement = $builder['sections_placement'];

						$_sections_slides = isset( $builder['sections_slides'] ) ? $builder['sections_slides'] : '';

						if ( !is_array( $_sections ) ) {
							$_sections = array( count( $_elements ) );
						}
						if ( !is_array( $_sections_size ) ) {
							$_sections_size = array_fill( 0, count( $_sections ), "w100" );
						}
						if ( !is_array( $_sections_style ) ) {
							$_sections_style = array_fill( 0, count( $_sections ), "" );
						}
						if ( !is_array( $_sections_placement ) ) {
							$_sections_placement = array_fill( 0, count( $_sections ), "before" );
						}

						if ( !is_array( $_sections_slides ) ) {
							$_sections_slides = array_fill( 0, count( $_sections ), "" );
						}

						$_helper_counter = 0;
						$_counter = array();

						for ( $_s = 0; $_s < count( $_sections ); $_s++ ) {
							$_sections_uniqid = $this->get_builder_element( 'sections_uniqid', $builder, $current_builder, $_s, TM_EPO_HELPER()->tm_temp_uniqid( count( $_sections ) ), $wpml_section_fields );

							$global_epos[ $priority ][ $tmcp_id ]['sections'][ $_s ] = array(
								'total_elements'     => $_sections[ $_s ],
								'sections_size'      => $_sections_size[ $_s ],
								'sections_slides'    => isset( $_sections_slides[ $_s ] ) ? $_sections_slides[ $_s ] : "",
								'sections_style'     => $_sections_style[ $_s ],
								'sections_placement' => $_sections_placement[ $_s ],
								'sections_uniqid'    => $_sections_uniqid,
								'sections_clogic'    => $this->get_builder_element( 'sections_clogic', $builder, $current_builder, $_s, FALSE, $wpml_section_fields, "sections", "", $_sections_uniqid ),
								'sections_logic'     => $this->get_builder_element( 'sections_logic', $builder, $current_builder, $_s, "", $wpml_section_fields, "sections", "", $_sections_uniqid ),
								'sections_class'     => $this->get_builder_element( 'sections_class', $builder, $current_builder, $_s, "", $wpml_section_fields, "sections", "", $_sections_uniqid ),
								'sections_type'      => $this->get_builder_element( 'sections_type', $builder, $current_builder, $_s, "", $wpml_section_fields, "sections", "", $_sections_uniqid ),

								'label_size'           => $this->get_builder_element( 'section_header_size', $builder, $current_builder, $_s, "", $wpml_section_fields, "sections", "", $_sections_uniqid ),
								'label'                => $this->get_builder_element( 'section_header_title', $builder, $current_builder, $_s, "", $wpml_section_fields, "sections", "", $_sections_uniqid ),
								'label_color'          => $this->get_builder_element( 'section_header_title_color', $builder, $current_builder, $_s, "", $wpml_section_fields, "sections", "", $_sections_uniqid ),
								'label_position'       => $this->get_builder_element( 'section_header_title_position', $builder, $current_builder, $_s, "", $wpml_section_fields, "sections", "", $_sections_uniqid ),
								'description'          => $this->get_builder_element( 'section_header_subtitle', $builder, $current_builder, $_s, "", $wpml_section_fields, "sections", "", $_sections_uniqid ),
								'description_position' => $this->get_builder_element( 'section_header_subtitle_position', $builder, $current_builder, $_s, "", $wpml_section_fields, "sections", "", $_sections_uniqid ),
								'description_color'    => $this->get_builder_element( 'section_header_subtitle_color', $builder, $current_builder, $_s, "", $wpml_section_fields, "sections", "", $_sections_uniqid ),
								'divider_type'         => $this->get_builder_element( 'section_divider_type', $builder, $current_builder, $_s, "", $wpml_section_fields, "sections", "", $_sections_uniqid ),
							);

							$this->current_option_features[] = $this->get_builder_element( 'sections_type', $builder, $current_builder, $_s, "", $wpml_section_fields, "sections", "", $_sections_uniqid );
							
							$element_no_in_section = -1;

							for ( $k0 = $_helper_counter; $k0 < intval( $_helper_counter + intval( $_sections[ $_s ] ) ); $k0++ ) {
								if ( !isset( $_elements[ $k0 ] ) ) {
									continue;
								}
								$element_no_in_section ++;
								$current_element = $_elements[ $k0 ];

								// Delete logic for variations section - not applicable
								if ( $current_element == "variations" ) {
									$global_epos[ $priority ][ $tmcp_id ]['sections'][ $_s ]["sections_logic"] = "";
									$global_epos[ $priority ][ $tmcp_id ]['sections'][ $_s ]["sections_clogic"] = "";
								}

								$wpml_element_fields = array();
								if ( isset( TM_EPO_BUILDER()->elements_array[ $current_element ] ) ) {
									foreach ( TM_EPO_BUILDER()->elements_array[ $current_element ] as $key => $value ) {
										if ( isset( $value['id'] ) && empty( $value['wpmldisable'] ) ) {
											$wpml_element_fields[ $value['id'] ] = $value['id'];
										}
									}
								}

								if ( isset( $current_element ) && isset( $tm_original_builder_elements[ $current_element ] ) ) {
									if ( !isset( $_counter[ $current_element ] ) ) {
										$_counter[ $current_element ] = 0;
									} else {
										$_counter[ $current_element ]++;
									}
									$current_counter = $_counter[ $current_element ];

									$_options = array();
									$_regular_price = array();
									$_regular_price_filtered = array();
									$_original_regular_price_filtered = array();
									$_regular_price_type = array();
									$_new_type = $current_element;
									$_prefix = "";
									$_min_price0 = '';
									$_min_price10 = '';
									$_min_price = '';
									$_max_price = '';
									$_regular_currencies = array();
									$price_per_currencies = array();
									$_description = FALSE;
									$_extra_multiple_choices = FALSE;
									$_use_lightbox = '';

									if ( $tm_original_builder_elements[ $current_element ] ) {
										if ( $tm_original_builder_elements[ $current_element ]["_is_addon"] == TRUE && $tm_original_builder_elements[ $current_element ]["is_post"] == "display" ) {
											$_prefix = $current_element . "_";
										}

										if ( $tm_original_builder_elements[ $current_element ]["type"] == "single" || $tm_original_builder_elements[ $current_element ]["type"] == "multipleallsingle" ) {
											$_prefix = $current_element . "_";
										} elseif ( $tm_original_builder_elements[ $current_element ]["type"] == "multiple" || $tm_original_builder_elements[ $current_element ]["type"] == "multipleall" || $tm_original_builder_elements[ $current_element ]["type"] == "multiplesingle" ) {
											$_prefix = $current_element . "_";
										}

										$element_uniqueid = $this->get_builder_element( $_prefix . 'uniqid', $builder, $current_builder, $current_counter, TM_EPO_HELPER()->tm_uniqid(), $wpml_element_fields, $current_element );

										$is_enabled = $this->get_builder_element( $_prefix . 'enabled', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );
										$is_required = $this->get_builder_element( $_prefix . 'required', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );
										if ( $is_enabled==="0" ){
											continue;
										}
										if ( $tm_original_builder_elements[ $current_element ]["type"] == "single" || $tm_original_builder_elements[ $current_element ]["type"] == "multipleallsingle" ) {
											$_prefix = $current_element . "_";

											//$element_uniqueid = $this->get_builder_element( $_prefix . 'uniqid', $builder, $current_builder, $current_counter, TM_EPO_HELPER()->tm_uniqid(), $wpml_element_fields, $current_element );


											$_is_field_required = $this->get_builder_element( $_prefix . 'required', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );
											$_changes_product_image = $this->get_builder_element( $_prefix . 'changes_product_image', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );
											$_use_images = $this->get_builder_element( $_prefix . 'use_images', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );
											$_use_colors = $this->get_builder_element( $_prefix . 'use_colors', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );

											$new_currency = FALSE;
											$mt_prefix = TM_EPO_HELPER()->get_currency_price_prefix();

											$_price = $builder[ $current_element . '_price' ][ $current_counter ];
											$_price = $this->get_builder_element( $_prefix . 'price', $builder, $current_builder, $current_counter, $_price, $wpml_element_fields, $current_element, "", $element_uniqueid );
											$_original_regular_price_filtered = $_price;
											if ( isset( $builder[ $current_element . '_sale_price' ][ $current_counter ] ) && $builder[ $current_element . '_sale_price' ][ $current_counter ] !== '' ) {
												$_price = $builder[ $current_element . '_sale_price' ][ $current_counter ];
												$_price = $this->get_builder_element( $_prefix . 'sale_price', $builder, $current_builder, $current_counter, $_price, $wpml_element_fields, $current_element, "", $element_uniqueid );
											}

											$_current_currency_price = isset( $builder[ $current_element . '_price' . $mt_prefix ] ) && isset( $builder[ $current_element . '_price' . $mt_prefix ][ $current_counter ] ) ? $builder[ $current_element . '_price' . $mt_prefix ][ $current_counter ] : '';
											$_current_currency_sale_price = isset( $builder[ $current_element . '_sale_price' . $mt_prefix ] ) && isset( $builder[ $current_element . '_sale_price' . $mt_prefix ][ $current_counter ] ) ? $builder[ $current_element . '_sale_price' . $mt_prefix ][ $current_counter ] : '';
											if ( $mt_prefix !== '' && $_current_currency_price && $_current_currency_price !== '' ) {
												$_price = $_current_currency_price;
												$_original_regular_price_filtered = $_price;
												if ( $_current_currency_sale_price && $_current_currency_sale_price !== '' ) {
													$_price = $_current_currency_sale_price;
												}
												$_regular_currencies = array( tc_get_woocommerce_currency() );
												$new_currency = TRUE;
											}
											foreach ( TM_EPO_HELPER()->get_currencies() as $currency ) {
												$mt_prefix = TM_EPO_HELPER()->get_currency_price_prefix( $currency );
												$_current_currency_price = isset( $builder[ $current_element . '_price' . $mt_prefix ][ $current_counter ] ) ? $builder[ $current_element . '_price' . $mt_prefix ][ $current_counter ] : '';
												$_current_currency_sale_price = isset( $builder[ $current_element . '_sale_price' . $mt_prefix ][ $current_counter ] ) ? $builder[ $current_element . '_sale_price' . $mt_prefix ][ $current_counter ] : '';

												if ( $_current_currency_sale_price && $_current_currency_sale_price !== '' ) {
													$_current_currency_price = $_current_currency_sale_price;
												}
												if ( $_current_currency_price !== '' ) {
													$price_per_currencies[ $currency ] = array( array( wc_format_decimal( $_current_currency_price, FALSE, TRUE ) ) );
												}
											}

											$_regular_price = array( array( wc_format_decimal( $_price, FALSE, TRUE ) ) );

											$_regular_price_type = isset( $builder[ $current_element . '_price_type' ][ $current_counter ] )
												? array( array( ($builder[ $current_element . '_price_type' ][ $current_counter ]) ) )
												: array();

											$_for_filter_price_type = isset( $builder[ $current_element . '_price_type' ][ $current_counter ] )
												? $builder[ $current_element . '_price_type' ][ $current_counter ]
												: "";

											if ( !$new_currency ) {
												$_price = apply_filters( 'wc_epo_get_current_currency_price', $_price, $_for_filter_price_type );
												$_original_regular_price_filtered = apply_filters( 'wc_epo_get_current_currency_price', $_original_regular_price_filtered, $_for_filter_price_type );
											}

											$_price = apply_filters( 'wc_epo_price', $_price, $_for_filter_price_type, $post_id );
											$_original_regular_price_filtered = apply_filters( 'wc_epo_price', $_original_regular_price_filtered, $_for_filter_price_type, $post_id );

											if ( $_price !== '' && isset( $builder[ $current_element . '_price_type' ][ $current_counter ] ) && $builder[ $current_element . '_price_type' ][ $current_counter ] == '' ) {
												$_min_price = $_max_price = wc_format_decimal( $_price, FALSE, TRUE );
												if ( $_is_field_required ) {
													$_min_price0 = $_min_price;
												} else {
													$_min_price0 = 0;
													$_min_price10 = $_min_price;
												}
											} else {
												$_min_price = $_max_price = FALSE;
												$_min_price0 = 0;
											}

											$_regular_price_filtered = array( array( wc_format_decimal( $_price, FALSE, TRUE ) ) );
											$_original_regular_price_filtered = array( array( wc_format_decimal( $_original_regular_price_filtered, FALSE, TRUE ) ) );

										} elseif ( $tm_original_builder_elements[ $current_element ]["type"] == "multiple" || $tm_original_builder_elements[ $current_element ]["type"] == "multipleall" || $tm_original_builder_elements[ $current_element ]["type"] == "multiplesingle" ) {
											$_prefix = $current_element . "_";

											//$element_uniqueid = $this->get_builder_element( $_prefix . 'uniqid', $builder, $current_builder, $current_counter, TM_EPO_HELPER()->tm_uniqid(), $wpml_element_fields, $current_element );

											$_is_field_required = $this->get_builder_element( $_prefix . 'required', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );
											$_changes_product_image = $this->get_builder_element( $_prefix . 'changes_product_image', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );
											$_use_images = $this->get_builder_element( $_prefix . 'use_images', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );
											$_use_colors = $this->get_builder_element( $_prefix . 'use_colors', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );
											$_use_lightbox = $this->get_builder_element( $_prefix . 'use_lightbox', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );

											if ( isset( $builder[ 'multiple_' . $current_element . '_options_price' ][ $current_counter ] ) ) {

												$_prices = $builder[ 'multiple_' . $current_element . '_options_price' ][ $current_counter ];												
												$_prices = $this->get_builder_element( 'multiple_' . $current_element . '_options_price', $builder, $current_builder, $current_counter, $_prices, TRUE, $current_element, "", $element_uniqueid );
												
												$_original_prices = $_prices;
												$_sale_prices = $_prices;
												if ( isset( $builder[ 'multiple_' . $current_element . '_options_sale_price' ][ $current_counter ] ) ) {
													$_sale_prices = $builder[ 'multiple_' . $current_element . '_options_sale_price' ][ $current_counter ];													
													$_sale_prices = $this->get_builder_element( 'multiple_' . $current_element . '_sale_prices', $builder, $current_builder, $current_counter, $_sale_prices, TRUE, $current_element, "", $element_uniqueid );
												}
												$_prices = TM_EPO_HELPER()->merge_price_array( $_prices, $_sale_prices );

												$mt_prefix = TM_EPO_HELPER()->get_currency_price_prefix();
												$_current_currency_prices = isset( $builder[ 'multiple_' . $current_element . '_options_price' . $mt_prefix ][ $current_counter ] ) ? $builder[ 'multiple_' . $current_element . '_options_price' . $mt_prefix ][ $current_counter ] : '';
												$_original_current_currency_prices = $_current_currency_prices;
												$_current_currency_sale_prices = isset( $builder[ 'multiple_' . $current_element . '_options_sale_price' . $mt_prefix ][ $current_counter ] ) ? $builder[ 'multiple_' . $current_element . '_options_sale_price' . $mt_prefix ][ $current_counter ] : '';
												$_current_currency_prices = TM_EPO_HELPER()->merge_price_array( $_current_currency_prices, $_current_currency_sale_prices );

												$_values = $this->get_builder_element( 'multiple_' . $current_element . '_options_value', $builder, $current_builder, $current_counter, "", TRUE, $current_element, "", $element_uniqueid );
												$_titles = $this->get_builder_element( 'multiple_' . $current_element . '_options_title', $builder, $current_builder, $current_counter, "", TRUE, $current_element, "", $element_uniqueid);
												$_images = $this->get_builder_element( 'multiple_' . $current_element . '_options_image', $builder, $current_builder, $current_counter, array(), TRUE, $current_element, 'tm_image_url', $element_uniqueid );
												$_imagesc = $this->get_builder_element( 'multiple_' . $current_element . '_options_imagec', $builder, $current_builder, $current_counter, array(), TRUE, $current_element, 'tm_image_url', $element_uniqueid );
												$_imagesp = $this->get_builder_element( 'multiple_' . $current_element . '_options_imagep', $builder, $current_builder, $current_counter, array(), TRUE, $current_element, 'tm_image_url', $element_uniqueid );
												$_imagesl = $this->get_builder_element( 'multiple_' . $current_element . '_options_imagel', $builder, $current_builder, $current_counter, array(), TRUE, $current_element, 'tm_image_url', $element_uniqueid );
												$_color = $this->get_builder_element( 'multiple_' . $current_element . '_options_color', $builder, $current_builder, $current_counter, array(), TRUE, $current_element, "", $element_uniqueid );

												foreach ( TM_EPO_HELPER()->get_currencies() as $currency ) {
													$mt_prefix = TM_EPO_HELPER()->get_currency_price_prefix( $currency );
													$_current_currency_price = isset( $builder[ 'multiple_' . $current_element . '_options_price' . $mt_prefix ][ $current_counter ] )
														? $builder[ 'multiple_' . $current_element . '_options_price' . $mt_prefix ][ $current_counter ]
														: '';
													$_current_currency_sale_price = isset( $builder[ 'multiple_' . $current_element . '_options_sale_price' . $mt_prefix ][ $current_counter ] )
														? $builder[ 'multiple_' . $current_element . '_options_sale_price' . $mt_prefix ][ $current_counter ]
														: '';

													$_current_currency_price = TM_EPO_HELPER()->merge_price_array( $_current_currency_price, $_current_currency_sale_price );

													$price_per_currencies[ $currency ] = $_current_currency_price;
													if ( !is_array( $price_per_currencies[ $currency ] ) ) {
														$price_per_currencies[ $currency ] = array();
													}
													foreach ( $_prices as $_n => $_price ) {
														$to_price = '';
														if ( is_array( $_current_currency_price ) && isset( $_current_currency_price[ $_n ] ) ) {
															$to_price = $_current_currency_price[ $_n ];
														}
														$price_per_currencies[ $currency ][ esc_attr( ($_values[ $_n ]) ) . "_" . $_n ] = array( wc_format_decimal( $to_price, FALSE, TRUE ) );
													}
												}

												if ( $_changes_product_image == "images" && $_use_images == "" ) {
													$_imagesp = $_images;
													$_images = array();
													$_imagesc = array();
													$_changes_product_image = "custom";
												}
												if ( $_use_images == "" ) {
													$_use_lightbox = "";
												}

												$_url = $this->get_builder_element( 'multiple_' . $current_element . '_options_url', $builder, $current_builder, $current_counter, array(), TRUE, $current_element, "", $element_uniqueid );
												$_description = $this->get_builder_element( 'multiple_' . $current_element . '_options_description', $builder, $current_builder, $current_counter, array(), TRUE, $current_element, "", $element_uniqueid );

												foreach ( TM_EPO_BUILDER()->extra_multiple_options as $__key => $__name ) {
													$_extra_name = $__name["name"];
													$_extra_multiple_choices[ $_extra_name ] = $this->get_builder_element( 'multiple_' . $current_element . '_options_' . $_extra_name, $builder, $current_builder, $current_counter, array(), TRUE, $current_element, "", $element_uniqueid );
												}

												$_prices_type = $this->get_builder_element( 'multiple_' . $current_element . '_options_price_type', $builder, $current_builder, $current_counter, array(), TRUE, $current_element, "", $element_uniqueid );
												$_values_c = $_values;
												$mt_prefix = TM_EPO_HELPER()->get_currency_price_prefix();
												foreach ( $_prices as $_n => $_price ) {
													$new_currency = FALSE;
													if ( $mt_prefix !== ''
														&& $_current_currency_prices !== ''
														&& is_array( $_current_currency_prices )
														&& isset( $_current_currency_prices[ $_n ] )
														&& $_current_currency_prices[ $_n ] != ''
													) {
														$new_currency = TRUE;
														$_price = $_current_currency_prices[ $_n ];
														$_original_prices[ $_n ] = $_original_current_currency_prices[ $_n ];
														$_regular_currencies[ esc_attr( ($_values[ $_n ]) ) . "_" . $_n ] = array( tc_get_woocommerce_currency() );
													}
													$_f_price = wc_format_decimal( $_price, FALSE, TRUE );
													$_regular_price[ esc_attr( ($_values[ $_n ]) ) . "_" . $_n ] = array( $_f_price );
													$_for_filter_price_type = isset( $_prices_type[ $_n ] ) ? $_prices_type[ $_n ] : "";

													if ( !$new_currency ) {
														$_price = apply_filters( 'wc_epo_get_current_currency_price', $_price, $_for_filter_price_type );
														$_original_prices[ $_n ] = apply_filters( 'wc_epo_get_current_currency_price', $_original_prices[ $_n ], $_for_filter_price_type );
													} else {

													}
													$_price = apply_filters( 'wc_epo_price', $_price, $_for_filter_price_type, $post_id );
													$_original_prices[ $_n ] = apply_filters( 'wc_epo_price', $_original_prices[ $_n ], $_for_filter_price_type, $post_id );
													$_f_price = wc_format_decimal( $_price, FALSE, TRUE );

													$_regular_price_filtered[ esc_attr( ($_values[ $_n ]) ) . "_" . $_n ] = array( wc_format_decimal( $_price, FALSE, TRUE ) );
													$_original_regular_price_filtered [ esc_attr( ($_values[ $_n ]) ) . "_" . $_n ] = array( wc_format_decimal( $_original_prices[ $_n ], FALSE, TRUE ) );
													$_regular_price_type[ esc_attr( ($_values[ $_n ]) ) . "_" . $_n ] = isset( $_prices_type[ $_n ] ) ? array( ($_prices_type[ $_n ]) ) : array( '' );
													$_options[ esc_attr( ($_values[ $_n ]) ) . "_" . $_n ] = $_titles[ $_n ];
													$_values_c[ $_n ] = $_values[ $_n ] . "_" . $_n;

													if ( isset( $_prices_type[ $_n ] ) && $_prices_type[ $_n ] == '' && ((isset( $builder[ $current_element . '_price_type' ][ $current_counter ] ) && $builder[ $current_element . '_price_type' ][ $current_counter ] == '') || !isset( $builder[ $current_element . '_price_type' ][ $current_counter ] )) ) {
														if ( $_min_price !== FALSE && $_price !== '' ) {
															if ( $_min_price === '' ) {
																$_min_price = $_f_price;
															} else {
																if ( $_min_price > $_f_price ) {
																	$_min_price = $_f_price;
																}
															}
															if ( $_min_price0 === '' ) {
																if ( $_is_field_required ) {
																	$_min_price0 = floatval( $_min_price );
																} else {
																	$_min_price0 = 0;
																}
															} else {
																if ( $_is_field_required && $_min_price0 > floatval( $_min_price ) ) {
																	$_min_price0 = floatval( $_min_price );
																}																
															}
															if ( $_min_price10 === '' ) {
																$_min_price10 = floatval( $_min_price );
															} else {
																if ( $_min_price10 > floatval( $_min_price ) ) {
																	$_min_price10 = floatval( $_min_price );
																}																
															}
															if ( $_max_price === '' ) {
																$_max_price = $_f_price;
															} else {
																if ( $_max_price < $_f_price ) {
																	$_max_price = $_f_price;
																}
															}
														} else {
															if ( $_price === '' ) {
																$_min_price0 = 0;
																$_min_price10 = 0;
															}
														}
													} else {
														$_min_price = $_max_price = FALSE;
														if ( $_min_price0 === '' ) {
															$_min_price0 = 0;
														} else {
															if ( $_min_price0 > floatval( $_min_price ) ) {
																$_min_price0 = floatval( $_min_price );
															}
														}
														if ( $_min_price10 === '' ) {
															$_min_price10 = 0;
														} else {
															if ( $_min_price10 > floatval( $_min_price ) ) {
																$_min_price10 = floatval( $_min_price );
															}
														}
													}
												}
											}
										}
									}
									$default_value = "";
									if ( isset( $builder[ 'multiple_' . $current_element . '_options_default_value' ][ $current_counter ] ) ) {
										$default_value = $builder[ 'multiple_' . $current_element . '_options_default_value' ][ $current_counter ];
									} elseif ( isset( $builder[ $_prefix . 'default_value' ] ) && isset( $builder[ $_prefix . 'default_value' ][ $current_counter ] ) ) {
										$default_value = $builder[ $_prefix . 'default_value' ][ $current_counter ];
									}
									$selectbox_fee = FALSE;
									$selectbox_cart_fee = FALSE;
									switch ( $current_element ) {

										case "selectbox":
											$_new_type = "select";
											$selectbox_fee = isset( $builder[ $current_element . '_price_type' ][ $current_counter ] ) ? array( array( ($builder[ $current_element . '_price_type' ][ $current_counter ]) ) ) : FALSE;
											$selectbox_cart_fee = isset( $builder[ $current_element . '_price_type' ][ $current_counter ] ) ? array( array( ($builder[ $current_element . '_price_type' ][ $current_counter ]) ) ) : FALSE;
											break;

										case "radiobuttons":
											$_new_type = "radio";
											break;

										case "checkboxes":
											$_new_type = "checkbox";
											break;

									}

									$_rules = $_regular_price;
									$_rules_filtered = $_regular_price_filtered;
									foreach ( $_regular_price as $key => $value ) {
										foreach ( $value as $k => $v ) {
											$_regular_price[ $key ][ $k ] = wc_format_localized_price( $v );
											$_regular_price_filtered[ $key ][ $k ] = wc_format_localized_price( $v );
										}
									}
									$_rules_type = $_regular_price_type;
									foreach ( $_regular_price_type as $key => $value ) {
										foreach ( $value as $k => $v ) {
											$_regular_price_type[ $key ][ $k ] = $v;
										}
									}
									if ( $current_element != 'variations' ) {
										$epos_prices[] = array(
											"uniqueid" 		 => $element_uniqueid,
											"required" 		 => $is_required,
											"element" 		 => $element_no_in_section,
											'section_uniqueid' => $_sections_uniqid,
											"minall" 		 => floatval( $_min_price10 ),
											"min"            => floatval( $_min_price0 ),
											"max"            => floatval( $_max_price ),
											"clogic"         => $this->get_builder_element( $_prefix . 'clogic', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											"section_clogic" => $global_epos[ $priority ][ $tmcp_id ]['sections'][ $_s ]['sections_clogic'],
											"logic"          => $this->get_builder_element( $_prefix . 'logic', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											"section_logic"  => $global_epos[ $priority ][ $tmcp_id ]['sections'][ $_s ]['sections_logic'],
										);
									}
									if ( $_min_price !== FALSE ) {
										$_min_price = wc_format_localized_price( $_min_price );
									}
									if ( $_max_price !== FALSE ) {
										$_max_price = wc_format_localized_price( $_max_price );
									}

									/** Fix for getting right results for dates even if the users enters wrong format **/
									$format = $this->get_builder_element( $_prefix . 'format', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );
									switch ( $format ) {
										case "0":
											$date_format = 'd/m/Y';
											$sep = "/";
											break;
										case "1":
											$date_format = 'm/d/Y';
											$sep = "/";
											break;
										case "2":
											$date_format = 'd.m.Y';
											$sep = ".";
											break;
										case "3":
											$date_format = 'm.d.Y';
											$sep = ".";
											break;
										case "4":
											$date_format = 'd-m-Y';
											$sep = "-";
											break;
										case "5":
											$date_format = 'm-d-Y';
											$sep = "-";
											break;
									}
									$disabled_dates = $this->get_builder_element( $_prefix . 'disabled_dates', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );
									if ( $disabled_dates ) {
										$disabled_dates = explode( ",", $disabled_dates );
										foreach ( $disabled_dates as $key => $value ) {
											if ( !$value ) {
												continue;
											}
											$value = str_replace( ".", "-", $value );
											$value = str_replace( "/", "-", $value );
											$value = explode( "-", $value );
											switch ( $format ) {
												case "0":
												case "2":
												case "4":
													$value = $value[2] . "-" . $value[1] . "-" . $value[0];
													break;
												case "1":
												case "3":
												case "5":
													$value = $value[2] . "-" . $value[0] . "-" . $value[1];
													break;
											}
											$value_to_date = date_create( $value );
											if ( !$value_to_date ) {
												continue;
											}
											$value = date_format( $value_to_date, $date_format );
											$disabled_dates[ $key ] = $value;
										}
										$disabled_dates = implode( ",", $disabled_dates );

									}
									$enabled_only_dates = $this->get_builder_element( $_prefix . 'enabled_only_dates', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid );
									if ( $enabled_only_dates ) {
										$enabled_only_dates = explode( ",", $enabled_only_dates );
										foreach ( $enabled_only_dates as $key => $value ) {
											if ( !$value ) {
												continue;
											}
											$value = str_replace( ".", "-", $value );
											$value = str_replace( "/", "-", $value );
											$value = explode( "-", $value );
											switch ( $format ) {
												case "0":
												case "2":
												case "4":
													$value = $value[2] . "-" . $value[1] . "-" . $value[0];
													break;
												case "1":
												case "3":
												case "5":
													$value = $value[2] . "-" . $value[0] . "-" . $value[1];
													break;
											}
											$value_to_date = date_create( $value );
											if ( !$value_to_date ) {
												continue;
											}
											$value = date_format( $value_to_date, $date_format );
											$enabled_only_dates[ $key ] = $value;
										}
										$enabled_only_dates = implode( ",", $enabled_only_dates );
									}

									$this->current_option_features[] = $current_element;									

									if ( $current_element != "header" && $current_element != "divider" ) {
										if ( $current_element == "variations" ) {
											$variation_element_id = $this->get_builder_element( $_prefix . 'uniqid', $builder, $current_builder, $current_counter, TM_EPO_HELPER()->tm_uniqid(), $wpml_element_fields, $current_element, "", $element_uniqueid );
											$variation_section_id = $global_epos[ $priority ][ $tmcp_id ]['sections'][ $_s ]['sections_uniqid'];
										}

										$_extra_multiple_choices = ($_extra_multiple_choices !== FALSE) ? $_extra_multiple_choices : array();

										$global_epos[ $priority ][ $tmcp_id ]['sections'][ $_s ]['elements'][] =
											array_merge(
												TM_EPO_BUILDER()->get_custom_properties( $builder, $_prefix, $_counter, $_elements, $k0, $current_builder, $current_counter, $wpml_element_fields, $current_element ),
												$_extra_multiple_choices,
												array(
													'_'             => TM_EPO_BUILDER()->get_default_properties( $builder, $_prefix, $_counter, $_elements, $k0 ),
													'internal_name' => $this->get_builder_element( $_prefix . 'internal_name', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'builder'       => (isset( $wpml_is_original_product ) && empty( $wpml_is_original_product )) ? $current_builder : $builder,
													'section'       => $_sections_uniqid,
													'type'          => $_new_type,
													'size'          => $_div_size[ $k0 ],

													'include_tax_for_fee_price_type' => $this->get_builder_element( $_prefix . 'include_tax_for_fee_price_type', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'tax_class_for_fee_price_type'   => $this->get_builder_element( $_prefix . 'tax_class_for_fee_price_type', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),

													'hide_element_label_in_cart'     => $this->get_builder_element( $_prefix . 'hide_element_label_in_cart', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'hide_element_value_in_cart'     => $this->get_builder_element( $_prefix . 'hide_element_value_in_cart', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'hide_element_label_in_order'    => $this->get_builder_element( $_prefix . 'hide_element_label_in_order', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'hide_element_value_in_order'    => $this->get_builder_element( $_prefix . 'hide_element_value_in_order', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'hide_element_label_in_floatbox' => $this->get_builder_element( $_prefix . 'hide_element_label_in_floatbox', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'hide_element_value_in_floatbox' => $this->get_builder_element( $_prefix . 'hide_element_value_in_floatbox', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),

													'enabled'       => $is_enabled,
													'required'      => $is_required,
													'use_images'    => isset( $_use_images ) ? $_use_images : "",
													'use_colors'    => isset( $_use_colors ) ? $_use_colors : "",
													'use_lightbox'  => $_use_lightbox,
													'use_url'       => $this->get_builder_element( $_prefix . 'use_url', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'items_per_row' => $this->get_builder_element( $_prefix . 'items_per_row', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),

													'items_per_row_r' => array(
														"desktop"        => $this->get_builder_element( $_prefix . 'items_per_row', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
														"tablets_galaxy" => $this->get_builder_element( $_prefix . 'items_per_row_tablets_galaxy', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
														"tablets"        => $this->get_builder_element( $_prefix . 'items_per_row_tablets', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
														"tablets_small"  => $this->get_builder_element( $_prefix . 'items_per_row_tablets_small', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
														"iphone6_plus"   => $this->get_builder_element( $_prefix . 'items_per_row_iphone6_plus', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
														"iphone6"        => $this->get_builder_element( $_prefix . 'items_per_row_iphone6', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
														"galaxy"         => $this->get_builder_element( $_prefix . 'items_per_row_samsung_galaxy', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
														"iphone5"        => $this->get_builder_element( $_prefix . 'items_per_row_iphone5', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
														"smartphones"    => $this->get_builder_element( $_prefix . 'items_per_row_smartphones', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													),

													'label_size'              => $this->get_builder_element( $_prefix . 'header_size', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'label'                   => $this->get_builder_element( $_prefix . 'header_title', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'label_position'          => $this->get_builder_element( $_prefix . 'header_title_position', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'label_color'             => $this->get_builder_element( $_prefix . 'header_title_color', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'description'             => $this->get_builder_element( $_prefix . 'header_subtitle', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'description_position'    => $this->get_builder_element( $_prefix . 'header_subtitle_position', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'description_color'       => $this->get_builder_element( $_prefix . 'header_subtitle_color', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'divider_type'            => $this->get_builder_element( $_prefix . 'divider_type', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'placeholder'             => $this->get_builder_element( $_prefix . 'placeholder', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'min_chars'               => $this->get_builder_element( $_prefix . 'min_chars', $builder, $current_builder, $current_counter, FALSE, $wpml_element_fields, $current_element, "wc_epo_global_min_chars", $element_uniqueid ),
													'max_chars'               => $this->get_builder_element( $_prefix . 'max_chars', $builder, $current_builder, $current_counter, FALSE, $wpml_element_fields, $current_element, "wc_epo_global_max_chars", $element_uniqueid ),
													'hide_amount'             => $this->get_builder_element( $_prefix . 'hide_amount', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'text_before_price'       => $this->get_builder_element( $_prefix . 'text_before_price', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'text_after_price'        => $this->get_builder_element( $_prefix . 'text_after_price', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'options'                 => $_options,
													'min_price'               => $_min_price,
													'max_price'               => $_max_price,
													'rules'                   => $_rules,
													'price_rules'             => $_regular_price,
													'rules_filtered'          => $_rules_filtered,
													'price_rules_filtered'    => $_regular_price_filtered,
													'original_rules_filtered' => $_original_regular_price_filtered,
													'price_rules_type'        => $_regular_price_type,
													'rules_type'              => $_rules_type,
													'currencies'              => $_regular_currencies,
													'price_per_currencies'    => $price_per_currencies,
													'images'                  => isset( $_images ) ? $_images : "",
													'imagesc'                 => isset( $_imagesc ) ? $_imagesc : "",
													'imagesp'                 => isset( $_imagesp ) ? $_imagesp : "",
													'imagesl'                 => isset( $_imagesl ) ? $_imagesl : "",
													'color'                   => isset( $_color ) ? $_color : "",
													'url'                     => isset( $_url ) ? $_url : "",
													'cdescription'            => ($_description !== FALSE) ? $_description : "",
													'extra_multiple_choices'  => ($_extra_multiple_choices !== FALSE) ? $_extra_multiple_choices : array(),
													'limit'                   => $this->get_builder_element( $_prefix . 'limit_choices', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'exactlimit'              => $this->get_builder_element( $_prefix . 'exactlimit_choices', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'minimumlimit'            => $this->get_builder_element( $_prefix . 'minimumlimit_choices', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'clear_options'           => $this->get_builder_element( $_prefix . 'clear_options', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'option_values'           => isset( $_values_c ) ? $_values_c : array(),
													'button_type'             => $this->get_builder_element( $_prefix . 'button_type', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'uniqid'                  => $element_uniqueid,
													'clogic'                  => $this->get_builder_element( $_prefix . 'clogic', $builder, $current_builder, $current_counter, FALSE, $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'logic'                   => $this->get_builder_element( $_prefix . 'logic', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'format'                  => $format,
													'start_year'              => $this->get_builder_element( $_prefix . 'start_year', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'end_year'                => $this->get_builder_element( $_prefix . 'end_year', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'min_date'                => $this->get_builder_element( $_prefix . 'min_date', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'max_date'                => $this->get_builder_element( $_prefix . 'max_date', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'disabled_dates'          => $disabled_dates,
													'enabled_only_dates'      => $enabled_only_dates,
													'disabled_weekdays'       => $this->get_builder_element( $_prefix . 'disabled_weekdays', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),

													'time_format'       => $this->get_builder_element( $_prefix . 'time_format', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'custom_time_format'       => $this->get_builder_element( $_prefix . 'custom_time_format', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'min_time'          => $this->get_builder_element( $_prefix . 'min_time', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'max_time'          => $this->get_builder_element( $_prefix . 'max_time', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'showhour'          => $this->get_builder_element( $_prefix . 'showhour', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'showminute'        => $this->get_builder_element( $_prefix . 'showminute', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'showsecond'        => $this->get_builder_element( $_prefix . 'showsecond', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'tranlation_hour'   => $this->get_builder_element( $_prefix . 'tranlation_hour', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'tranlation_minute' => $this->get_builder_element( $_prefix . 'tranlation_minute', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'tranlation_second' => $this->get_builder_element( $_prefix . 'tranlation_second', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),

													'theme'          => $this->get_builder_element( $_prefix . 'theme', $builder, $current_builder, $current_counter, "epo", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'theme_size'     => $this->get_builder_element( $_prefix . 'theme_size', $builder, $current_builder, $current_counter, "medium", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'theme_position' => $this->get_builder_element( $_prefix . 'theme_position', $builder, $current_builder, $current_counter, "normal", $wpml_element_fields, $current_element, "", $element_uniqueid ),

													'tranlation_day'        => $this->get_builder_element( $_prefix . 'tranlation_day', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'tranlation_month'      => $this->get_builder_element( $_prefix . 'tranlation_month', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'tranlation_year'       => $this->get_builder_element( $_prefix . 'tranlation_year', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													"default_value"         => $default_value,
													'text_after_price'      => $this->get_builder_element( $_prefix . 'text_before_price', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'text_after_price'      => $this->get_builder_element( $_prefix . 'text_after_price', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'selectbox_fee'         => $selectbox_fee,
													'selectbox_cart_fee'    => $selectbox_cart_fee,
													'class'                 => $this->get_builder_element( $_prefix . 'class', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'container_id'          => $this->get_builder_element( $_prefix . 'container_id', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'swatchmode'            => $this->get_builder_element( $_prefix . 'swatchmode', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'changes_product_image' => isset( $_changes_product_image ) ? $_changes_product_image : "",
													'min'                   => $this->get_builder_element( $_prefix . 'min', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'max'                   => $this->get_builder_element( $_prefix . 'max', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'freechars'             => $this->get_builder_element( $_prefix . 'freechars', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'step'                  => $this->get_builder_element( $_prefix . 'step', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'pips'                  => $this->get_builder_element( $_prefix . 'pips', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'noofpips'              => $this->get_builder_element( $_prefix . 'noofpips', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'show_picker_value'     => $this->get_builder_element( $_prefix . 'show_picker_value', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),

													'quantity'               => $this->get_builder_element( $_prefix . 'quantity', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'quantity_min'           => $this->get_builder_element( $_prefix . 'quantity_min', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'quantity_max'           => $this->get_builder_element( $_prefix . 'quantity_max', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'quantity_step'          => $this->get_builder_element( $_prefix . 'quantity_step', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
													'quantity_default_value' => $this->get_builder_element( $_prefix . 'quantity_default_value', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),

													'validation1' => $this->get_builder_element( $_prefix . 'validation1', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
												) );

									} elseif ( $current_element == "header" ) {

										$global_epos[ $priority ][ $tmcp_id ]['sections'][ $_s ]['elements'][] = array(
											'internal_name'         => $this->get_builder_element( $_prefix . 'internal_name', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'section'               => $_sections_uniqid,
											'type'                  => $_new_type,
											'size'                  => $_div_size[ $k0 ],
											'required'              => "",
											'enabled'       		=> $is_enabled,
											'use_images'            => "",
											'use_colors'            => "",
											'use_url'               => "",
											'items_per_row'         => "",
											'label_size'            => $this->get_builder_element( $_prefix . 'header_size', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'label'                 => $this->get_builder_element( $_prefix . 'header_title', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'label_position'        => $this->get_builder_element( $_prefix . 'header_title_position', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'label_color'           => $this->get_builder_element( $_prefix . 'header_title_color', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'description'           => $this->get_builder_element( $_prefix . 'header_subtitle', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'description_color'     => $this->get_builder_element( $_prefix . 'header_subtitle_color', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'description_position'  => $this->get_builder_element( $_prefix . 'header_subtitle_position', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'divider_type'          => "",
											'placeholder'           => "",
											'max_chars'             => "",
											'hide_amount'           => "",
											"options"               => $_options,
											'min_price'             => $_min_price,
											'max_price'             => $_max_price,
											'rules'                 => $_rules,
											'price_rules'           => $_regular_price,
											'rules_filtered'        => $_rules_filtered,
											'price_rules_filtered'  => $_regular_price_filtered,
											'price_rules_type'      => $_regular_price_type,
											'rules_type'            => $_rules_type,
											'images'                => "",
											'limit'                 => "",
											'exactlimit'            => "",
											'minimumlimit'          => "",
											'option_values'         => array(),
											'button_type'           => '',
											'class'                 => $this->get_builder_element( 'header_class', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'uniqid'                => $this->get_builder_element( 'header_uniqid', $builder, $current_builder, $current_counter, TM_EPO_HELPER()->tm_uniqid(), $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'clogic'                => $this->get_builder_element( 'header_clogic', $builder, $current_builder, $current_counter, FALSE, $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'logic'                 => $this->get_builder_element( 'header_logic', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'format'                => '',
											'start_year'            => '',
											'end_year'              => '',
											'tranlation_day'        => '',
											'tranlation_month'      => '',
											'tranlation_year'       => '',
											'swatchmode'            => "",
											'changes_product_image' => "",
											'min'                   => "",
											'max'                   => "",
											'step'                  => "",
											'pips'                  => "",

										);

									} elseif ( $current_element == "divider" ) {

										$global_epos[ $priority ][ $tmcp_id ]['sections'][ $_s ]['elements'][] = array(
											'internal_name'         => $this->get_builder_element( $_prefix . 'internal_name', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'section'               => $_sections_uniqid,
											'type'                  => $_new_type,
											'size'                  => $_div_size[ $k0 ],
											'required'              => "",
											'enabled'       		=> $is_enabled,
											'use_images'            => "",
											'use_colors'            => "",
											'use_url'               => "",
											'items_per_row'         => "",
											'label_size'            => "",
											'label'                 => "",
											'label_color'           => "",
											'label_position'        => "",
											'description'           => "",
											'description_color'     => "",
											'divider_type'          => $this->get_builder_element( $_prefix . 'divider_type', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'placeholder'           => "",
											'max_chars'             => "",
											'hide_amount'           => "",
											"options"               => $_options,
											'min_price'             => $_min_price,
											'max_price'             => $_max_price,
											'rules'                 => $_rules,
											'price_rules'           => $_regular_price,
											'rules_filtered'        => $_rules_filtered,
											'price_rules_filtered'  => $_regular_price_filtered,
											'price_rules_type'      => $_regular_price_type,
											'rules_type'            => $_rules_type,
											'images'                => "",
											'limit'                 => "",
											'exactlimit'            => "",
											'minimumlimit'          => "",
											'option_values'         => array(),
											'button_type'           => '',
											'class'                 => $this->get_builder_element( 'divider_class', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'uniqid'                => $this->get_builder_element( 'divider_uniqid', $builder, $current_builder, $current_counter, TM_EPO_HELPER()->tm_uniqid(), $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'clogic'                => $this->get_builder_element( 'divider_clogic', $builder, $current_builder, $current_counter, FALSE, $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'logic'                 => $this->get_builder_element( 'divider_logic', $builder, $current_builder, $current_counter, "", $wpml_element_fields, $current_element, "", $element_uniqueid ),
											'format'                => '',
											'start_year'            => '',
											'end_year'              => '',
											'tranlation_day'        => '',
											'tranlation_month'      => '',
											'tranlation_year'       => '',
											'swatchmode'            => "",
											'changes_product_image' => "",
											'min'                   => "",
											'max'                   => "",
											'step'                  => "",
											'pips'                  => "",
										);

									}
								}
							}

							$_helper_counter = intval( $_helper_counter + intval( $_sections[ $_s ] ) );

						}
					}
				}
			}
		}

		return array(
			'global'               => $global_epos,
			'price'                => $epos_prices,
			'variation_element_id' => $variation_element_id,
			'variation_section_id' => $variation_section_id,
		);
	}

	/**
	 * Translate $attributes to post names.
	 */
	public function translate_fields( $attributes, $type, $section, $form_prefix = "", $name_prefix = "" ) {

		$fields = array();
		$loop = 0;

		/* $form_prefix should be passed with _ if not empty */
		if ( !empty( $attributes ) ) {

			foreach ( $attributes as $key => $attribute ) {
				$name_inc = "";
				if ( !empty( $this->tm_builder_elements[ $type ]["post_name_prefix"] ) ) {
					if ( $this->tm_builder_elements[ $type ]["type"] == "multiple" || $this->tm_builder_elements[ $type ]["type"] == "multiplesingle" ) {
						$name_inc = "tmcp_" . $name_prefix . $this->tm_builder_elements[ $type ]["post_name_prefix"] . "_" . $section . $form_prefix;
					} elseif ( $this->tm_builder_elements[ $type ]["type"] == "multipleall" ) {
						$name_inc = "tmcp_" . $name_prefix . $this->tm_builder_elements[ $type ]["post_name_prefix"] . "_" . $section . "_" . $loop . $form_prefix;
					}
				}
				$fields[] = $name_inc;
				$loop++;
			}

		} else {
			if ( !empty( $this->tm_builder_elements[ $type ]["type"] ) && !empty( $this->tm_builder_elements[ $type ]["post_name_prefix"] ) ) {
				$name_inc = "tmcp_" . $name_prefix . $this->tm_builder_elements[ $type ]["post_name_prefix"] . "_" . $section . $form_prefix;
			}

			if ( !empty( $name_inc ) ) {
				$fields[] = $name_inc;
			}

		}

		return $fields;

	}

	/**
	 * @param $global_prices
	 * @param $where
	 * @param $cart_item_meta
	 * @param $tmcp_post_fields
	 * @param $product_id
	 * @param $per_product_pricing
	 * @param $cpf_product_price
	 * @param $variation_id
	 * @param $field_loop
	 * @param $loop
	 * @param $form_prefix
	 * @param $post_data
	 * @return array
	 */
	public function add_cart_item_data_loop( $global_prices, $where, $cart_item_meta, $tmcp_post_fields, $product_id, $per_product_pricing, $cpf_product_price, $variation_id, $field_loop, $loop, $form_prefix, $post_data ) {

		foreach ( $global_prices[ $where ] as $priorities ) {
			foreach ( $priorities as $field ) {
				foreach ( $field['sections'] as $section_id => $section ) {
					if ( isset( $section['elements'] ) ) {
						foreach ( $section['elements'] as $element ) {

							$init_class = "TM_EPO_FIELDS_" . $element['type'];
							if ( !class_exists( $init_class ) && !empty( $this->tm_builder_elements[ $element['type'] ]["_is_addon"] ) ) {
								$init_class = "TM_EPO_FIELDS";
							}
							if ( class_exists( $init_class ) ) {
								$field_obj = new $init_class( $product_id, $element, $per_product_pricing, $cpf_product_price, $variation_id, $post_data );

								/* Cart fees */
								$current_tmcp_post_fields = array_intersect_key( $tmcp_post_fields, array_flip( $this->translate_fields( $element['options'], $element['type'], $field_loop, $form_prefix, $this->cart_fee_name ) ) );
								foreach ( $current_tmcp_post_fields as $attribute => $key ) {
									if ( !empty( $field_obj->holder_cart_fees ) ) {
										if ( isset( $tmcp_post_fields[ $attribute . '_quantity' ] ) ) {
											if ( empty( $tmcp_post_fields[ $attribute . '_quantity' ] ) ) {
												continue;
											}
										}
										$meta = $field_obj->add_cart_item_data_cart_fees( $attribute, $key );
										if ( is_array( $meta ) ) {
											if ( isset( $meta[0] ) && is_array( $meta[0] ) ) {
												foreach ( $meta as $k => $value ) {
													$cart_item_meta['tmcartfee'][] = $value;
													$cart_item_meta['tmdata']['tmcartfee_data'][] = array( 'key' => $key, 'attribute' => $attribute );
												}
											} else {
												$cart_item_meta['tmcartfee'][] = $meta;
												$cart_item_meta['tmdata']['tmcartfee_data'][] = array( 'key' => $key, 'attribute' => $attribute );
											}
										}
									}
								}

								/* Subscription sign up fees */
								$current_tmcp_post_fields = array_intersect_key( $tmcp_post_fields, array_flip( $this->translate_fields( $element['options'], $element['type'], $field_loop, $form_prefix, $this->fee_name ) ) );
								foreach ( $current_tmcp_post_fields as $attribute => $key ) {
									;
									if ( !empty( $field_obj->holder_subscription_fees ) ) {
										if ( isset( $tmcp_post_fields[ $attribute . '_quantity' ] ) ) {
											if ( empty( $tmcp_post_fields[ $attribute . '_quantity' ] ) ) {
												continue;
											}
										}
										$meta = $field_obj->add_cart_item_data_subscription_fees( $attribute, $key );
										if ( is_array( $meta ) ) {
											if ( isset( $meta[0] ) && is_array( $meta[0] ) ) {
												foreach ( $meta as $k => $value ) {
													$cart_item_meta['tmcartepo'][] = $value;
													$cart_item_meta['tmdata']['tmcartepo_data'][] = array( 'key' => $key, 'attribute' => $attribute );
												}
											} else {
												$cart_item_meta['tmcartepo'][] = $meta;
												$cart_item_meta['tmdata']['tmcartepo_data'][] = array( 'key' => $key, 'attribute' => $attribute );
											}
										}
									}
									$cart_item_meta['tmsubscriptionfee'] = $this->tmfee;
								}

								/* Normal fields */
								$current_tmcp_post_fields = array_intersect_key( $tmcp_post_fields, array_flip( $this->translate_fields( $element['options'], $element['type'], $field_loop, $form_prefix, "" ) ) );

								foreach ( $current_tmcp_post_fields as $attribute => $key ) {
									if ( !empty( $field_obj->holder ) ) {
										if ( isset( $tmcp_post_fields[ $attribute . '_quantity' ] ) ) {
											if ( empty( $tmcp_post_fields[ $attribute . '_quantity' ] ) ) {
												continue;
											}
										}
										$meta = $field_obj->add_cart_item_data( $attribute, $key );
										if ( is_array( $meta ) ) {
											if ( isset( $meta[0] ) && is_array( $meta[0] ) ) {
												foreach ( $meta as $k => $value ) {
													$cart_item_meta['tmcartepo'][] = $value;
													$cart_item_meta['tmdata']['tmcartepo_data'][] = array( 'key' => $key, 'attribute' => $attribute );
												}
											} else {
												$cart_item_meta['tmcartepo'][] = $meta;
												$cart_item_meta['tmdata']['tmcartepo_data'][] = array( 'key' => $key, 'attribute' => $attribute );
											}
										}
									}
								}

								unset( $field_obj ); // clear memory
							}

							if ( in_array( $element['type'], $this->element_post_types ) ) {
								$field_loop++;
							}
							$loop++;

						}
					}
				}
			}
		}

		return array( 'loop' => $loop, 'field_loop' => $field_loop, 'cart_item_meta' => $cart_item_meta );

	}

	/**
	 * NORMAL FIELDS (to be deprecated)
	 *
	 * @param $local_price_array
	 * @param $cart_item_meta
	 * @param $tmcp_post_fields
	 * @param $product_id
	 * @param $per_product_pricing
	 * @param $cpf_product_price
	 * @param $variation_id
	 * @param $field_loop
	 * @param $loop
	 * @param $form_prefix
	 * @param $post_data
	 * @return array
	 */
	public function add_cart_item_data_loop_local( $local_price_array, $cart_item_meta, $tmcp_post_fields, $product_id, $per_product_pricing, $cpf_product_price, $variation_id, $field_loop, $loop, $form_prefix, $post_data ) {

		if ( !empty( $local_price_array ) && is_array( $local_price_array ) && count( $local_price_array ) > 0 ) {

			if ( is_array( $tmcp_post_fields ) ) {

				$getproduct = wc_get_product( $product_id );

				foreach ( $local_price_array as $tmcp ) {
					if ( empty( $tmcp['type'] ) ) {
						continue;
					}

					$current_tmcp_post_fields = array_intersect_key( $tmcp_post_fields, array_flip( $this->translate_fields( $tmcp['attributes'], $tmcp['type'], $field_loop, $form_prefix ) ) );

					foreach ( $current_tmcp_post_fields as $attribute => $key ) {

						switch ( $tmcp['type'] ) {

							case "checkbox" :
							case "radio" :
							case "select" :
								$_price = $this->calculate_price( $_POST, $tmcp, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );

								$cart_item_meta['tmcartepo'][] = array(
									'mode'                => 'local',
									'key'                 => $key,
									'is_taxonomy'         => $tmcp['is_taxonomy'],
									'name'                => esc_html( $tmcp['name'] ),
									'value'               => esc_html( wc_attribute_label( $tmcp['attributes_wpml'][ $key ], $getproduct ) ),
									'price'               => esc_attr( $_price ),
									'section'             => esc_html( $tmcp['name'] ),
									'section_label'       => esc_html( wc_attribute_label( urldecode( $tmcp['label'] ), $getproduct ) ),
									'percentcurrenttotal' => isset( $post_data[ $attribute . '_hidden' ] ) ? 1 : 0,
									'quantity'            => 1,
								);
								$cart_item_meta['tmdata']['tmcartepo_data'][] = array( 'key' => $key, 'attribute' => $attribute );
								break;

						}
					}
					if ( in_array( $tmcp['type'], $this->element_post_types ) ) {
						$field_loop++;
					}
					$loop++;

				}
			}
		}

		return array( 'loop' => $loop, 'field_loop' => $field_loop, 'cart_item_meta' => $cart_item_meta );

	}

	/**
	 * @param string $form_prefix
	 * @return null
	 */
	public function get_posted_variation_id( $form_prefix = "" ) {

		$variation_id = NULL;
		if ( isset( $_POST[ 'variation_id' . $form_prefix ] ) ) {
			$variation_id = $_POST[ 'variation_id' . $form_prefix ];
		}

		return $variation_id;

	}

	/**
	 * Adds data to the cart.
	 *
	 * @param $cart_item_meta
	 * @param $product_id
	 * @param null $post_data
	 * @return mixed
	 */
	public function tm_add_cart_item_data( $cart_item_meta, $product_id, $post_data = NULL ) {

		return $this->add_cart_item_data_helper( $cart_item_meta, $product_id, $post_data );

	}

	/**
	 * @param $cart_item_meta
	 * @param $product_id
	 * @return mixed
	 */
	public function add_cart_item_data( $cart_item_meta, $product_id ) {

		return $this->add_cart_item_data_helper( $cart_item_meta, $product_id, $_POST );

	}

	/**
	 * @param $cart_item_meta
	 * @param $product_id
	 * @param null $post_data
	 * @return mixed
	 */
	public function add_cart_item_data_helper( $cart_item_meta, $product_id, $post_data = NULL ) {

		if ( is_null( $post_data ) && isset( $_POST ) ) {
			$post_data = $_POST;
		}
		if ( empty( $post_data ) && isset( $_REQUEST['tcajax'] ) ) {
			$post_data = $_REQUEST;
		}
		/* Workaround to get unique items in cart for bto */
		$terms = get_the_terms( $product_id, 'product_type' );
		$product_type = !empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';
		if ( ($product_type == 'bto' || $product_type == 'composite') &&
			(isset( $post_data['add-product-to-cart'] ) && is_array( $post_data['add-product-to-cart'] )) ||
			(isset( $post_data['wccp_component_selection'] ) && is_array( $post_data['wccp_component_selection'] ))
		) {
			$copy = array();
			$enum = array();
			if ( isset( $post_data['add-product-to-cart'] ) ) {
				$enum = $post_data['add-product-to-cart'];
			} elseif ( isset( $post_data['wccp_component_selection'] ) ) {
				$enum = $post_data['wccp_component_selection'];
			}
			foreach ( $enum as $bundled_item_id => $bundled_product_id ) {
				$copy = array_merge( $copy, TM_EPO_HELPER()->array_filter_key( $post_data, $bundled_item_id, "end" ) );
			}
			$copy = TM_EPO_HELPER()->array_filter_key( $copy );
			$cart_item_meta['tmcartepo_bto'] = $copy;
		}

		$form_prefix = "";
		$variation_id = FALSE;
		$cpf_product_price = FALSE;
		$per_product_pricing = TRUE;

		if ( isset( $cart_item_meta['composite_item'] ) ) {
			global $woocommerce;
			$cart_contents = $woocommerce->cart->get_cart();

			if ( isset( $cart_item_meta['composite_parent'] ) && !empty( $cart_item_meta['composite_parent'] ) ) {
				$parent_cart_key = $cart_item_meta['composite_parent'];

				if ( $cart_contents[ $parent_cart_key ]['data'] && is_callable( array( $cart_contents[ $parent_cart_key ]['data'], "contains" ) ) ) {
					$per_product_pricing = $cart_contents[ $parent_cart_key ]['data']->contains("priced_individually");
				}else{
					$per_product_pricing = $cart_contents[ $parent_cart_key ]['data']->per_product_pricing;
				}

				if ( $per_product_pricing === 'no' ) {
					$per_product_pricing = FALSE;
				}
			}

			$form_prefix = "_" . $cart_item_meta['composite_item'];
			$bundled_item_id = $cart_item_meta['composite_item'];
			if ( isset( $post_data['bto_variation_id'][ $bundled_item_id ] ) ) {
				$variation_id = $post_data['bto_variation_id'][ $bundled_item_id ];
			} elseif ( isset( $post_data['wccp_variation_id'][ $bundled_item_id ] ) ) {
				$variation_id = $post_data['wccp_variation_id'][ $bundled_item_id ];
			}
			if ( isset( $post_data['cpf_bto_price'][ $bundled_item_id ] ) ) {
				$cpf_product_price = $post_data['cpf_bto_price'][ $bundled_item_id ];
			}
		} else {
			if ( !empty( $post_data['tc_form_prefix'] ) ) {
				$form_prefix = $post_data['tc_form_prefix'];
				$form_prefix = str_replace( "_", "", $form_prefix );
				$form_prefix = "_" . $form_prefix;
			}
			if ( isset( $post_data['variation_id'] ) ) {
				$variation_id = $post_data['variation_id'];
			}
			if ( isset( $post_data['cpf_product_price'] ) ) {
				$cpf_product_price = $post_data['cpf_product_price'];
			}
		}

		$cpf_price_array = $this->get_product_tm_epos( $product_id, $form_prefix );
		if ( empty( $cpf_price_array ) ) {
			return $cart_item_meta;
		}
		$global_price_array = $cpf_price_array['global'];
		$local_price_array = $cpf_price_array['local'];

		if ( empty( $global_price_array ) && empty( $local_price_array ) ) {
			return $cart_item_meta;
		}

		// if the following key doens't extist the edit cart link is not being displayed.
		if ( in_array( $product_type, array( "simple", "variable", "subscription", "variable-subscription" ) ) ) {
			$cart_item_meta['tmhasepo'] = 1;
		}

		$tm_meta_cpf = tc_get_post_meta( $product_id, 'tm_meta_cpf', TRUE );
		if ( !is_array( $tm_meta_cpf ) ) {
			$tm_meta_cpf = array();
		}
		foreach ( $this->meta_fields as $key => $value ) {
			$tm_meta_cpf[ $key ] = isset( $tm_meta_cpf[ $key ] ) ? $tm_meta_cpf[ $key ] : $value;
		}

		$price_override = ($this->tm_epo_global_override_product_price == 'no')
			? 0
			: (($this->tm_epo_global_override_product_price == 'yes')
				? 1
				: !empty( $this->tm_meta_cpf['price_override'] ) ? 1 : 0);

		if ( !empty( $price_override ) ) {
			$cart_item_meta['epo_price_override'] = 1;
		}

		$global_prices = array( 'before' => array(), 'after' => array() );
		foreach ( $global_price_array as $priority => $priorities ) {
			foreach ( $priorities as $pid => $field ) {
				if ( isset( $field['sections'] ) ) {
					foreach ( $field['sections'] as $section_id => $section ) {
						if ( isset( $section['sections_placement'] ) ) {
							$global_prices[ $section['sections_placement'] ][ $priority ][ $pid ]['sections'][ $section_id ] = $section;
						}
					}
				}
			}
		}

		$files = array();
		foreach ( $_FILES as $k => $file ) {
			if ( !empty( $file['name'] ) ) {
				$files[ $k ] = $file['name'];
			}
		}

		$tmcp_post_fields = array_merge( TM_EPO_HELPER()->array_filter_key( $post_data ), TM_EPO_HELPER()->array_filter_key( $files ) );
		if ( is_array( $tmcp_post_fields ) ) {
			$tmcp_post_fields = array_map( 'stripslashes_deep', $tmcp_post_fields );
		}

		if ( empty( $cart_item_meta['tmcartepo'] ) ) {
			$cart_item_meta['tmcartepo'] = array();
		}
		if ( empty( $cart_item_meta['tmsubscriptionfee'] ) ) {
			$cart_item_meta['tmsubscriptionfee'] = 0;
		}
		if ( empty( $cart_item_meta['tmcartfee'] ) ) {
			$cart_item_meta['tmcartfee'] = array();
		}
		if ( empty( $cart_item_meta['tmpost_data'] ) ) {
			$cart_item_meta['tmpost_data'] = $post_data;
		}

		$cart_item_meta['tmdata'] = array(
			'tmcp_post_fields'    => $tmcp_post_fields,
			'product_id'          => $product_id,
			'per_product_pricing' => $per_product_pricing,
			'cpf_product_price'   => $cpf_product_price,
			'variation_id'        => $variation_id,
			'form_prefix'         => $form_prefix,
		);

		$loop = 0;
		$field_loop = 0;

		$_return = $this->add_cart_item_data_loop( $global_prices, 'before', $cart_item_meta, $tmcp_post_fields, $product_id, $per_product_pricing, $cpf_product_price, $variation_id, $field_loop, $loop, $form_prefix, $post_data );
		extract( $_return, EXTR_OVERWRITE );

		/* NORMAL FIELDS (to be deprecated) */
		$_return = $this->add_cart_item_data_loop_local( $local_price_array, $cart_item_meta, $tmcp_post_fields, $product_id, $per_product_pricing, $cpf_product_price, $variation_id, $field_loop, $loop, $form_prefix, $post_data );
		extract( $_return, EXTR_OVERWRITE );

		$_return = $this->add_cart_item_data_loop( $global_prices, 'after', $cart_item_meta, $tmcp_post_fields, $product_id, $per_product_pricing, $cpf_product_price, $variation_id, $field_loop, $loop, $form_prefix, $post_data );
		extract( $_return, EXTR_OVERWRITE );

		return $cart_item_meta;

	}

	/**
	 * @param $cart_item_meta
	 * @param $product_id
	 * @param null $post_data
	 * @return mixed
	 */
	public function repopulatecart( $cart_item_meta, $product_id, $post_data = NULL ) {
		
		$cpf_product_price = $post_data['cpf_product_price'];

		$global_prices = array( 'before' => array(), 'after' => array() );
		$cpf_price_array = $this->get_product_tm_epos( $product_id, $cart_item_meta["tmdata"]["form_prefix"] );
		if ( !empty( $cpf_price_array ) ) {
			$global_price_array = $cpf_price_array['global'];

			if ( !empty( $global_price_array ) ) {
				foreach ( $global_price_array as $priority => $priorities ) {
					foreach ( $priorities as $pid => $field ) {
						if ( isset( $field['sections'] ) ) {
							foreach ( $field['sections'] as $section_id => $section ) {
								if ( isset( $section['sections_placement'] ) ) {
									$global_prices[ $section['sections_placement'] ][ $priority ][ $pid ]['sections'][ $section_id ] = $section;
								}
							}
						}
					}
				}
			}

		}

		$element_object = array();
		$pl = array( "before", "after" );
		foreach ( $pl as $where ) {
			foreach ( $global_prices[ $where ] as $priorities ) {
				foreach ( $priorities as $field ) {
					foreach ( $field['sections'] as $section_id => $section ) {
						if ( isset( $section['elements'] ) ) {
							foreach ( $section['elements'] as $element ) {
								$element_object[ $element['uniqid'] ] = $element;
							}
						}
					}
				}
			}
		}

		if ( isset( $cart_item_meta['tmcartepo'] ) ) {
			$current_currency = tc_get_woocommerce_currency();

			$percentcurrenttotal = array();

			foreach ( $cart_item_meta['tmcartepo'] as $key => $value ) {
				if ( !isset( $element_object[ $value['section'] ] ) ) {
					continue;
				}
				if ( $value["mode"] == "builder" ) {

					$new_key = FALSE;
					$wpml_translation_by_id = TM_EPO_WPML()->get_wpml_translation_by_id( $product_id, TRUE );
					if ( !empty( $value['multiple'] ) && !empty( $value['key'] ) ) {
						$pos = strrpos( $value['key'], '_' );
						if ( $pos !== FALSE && isset( $wpml_translation_by_id[ "options_" . $value['section'] ] ) && is_array( $wpml_translation_by_id[ "options_" . $value['section'] ] ) ) {
							$av = array_values( $wpml_translation_by_id[ "options_" . $value['section'] ] );
							$ak = array_keys( $wpml_translation_by_id[ "options_" . $value['section'] ] );
							if ( isset( $av[ substr( $value['key'], $pos + 1 ) ] ) ) {
								$new_key = $ak[ substr( $value['key'], $pos + 1 ) ];
							}
						}
					}

					$price_per_currencies = isset( $element_object[ $value['section'] ]['price_per_currencies'] ) ? $element_object[ $value['section'] ]['price_per_currencies'] : array();
					$price_per_currency = array();
					$_price_type = $this->get_element_price_type( $value );

					if ( $_price_type == "percentcurrenttotal" ) {
						$percentcurrenttotal[] = $key;
					} else {

						foreach ( $price_per_currencies as $currency => $price_rule ) {
							$copy_element = $element_object[ $value['section'] ];
							$copy_element['price_rules'] = $price_rule;
							
							$currency_price = $this->calculate_price( $post_data,
								$copy_element,
								($new_key !== FALSE) ? $new_key : $cart_item_meta['tmdata']['tmcartepo_data'][ $key ]['key'],
								$cart_item_meta['tmdata']['tmcartepo_data'][ $key ]['attribute'],
								$cart_item_meta["tmdata"]["per_product_pricing"],
								$cpf_product_price,
								$cart_item_meta["tmdata"]["variation_id"],
								'',
								$currency,
								$current_currency,
								$price_per_currencies );

							$price_per_currency[ $currency ] = $currency_price;
						}

						$_price = $this->calculate_price( $post_data,
							$element_object[ $value['section'] ],
							($new_key !== FALSE) ? $new_key : $cart_item_meta['tmdata']['tmcartepo_data'][ $key ]['key'],
							$cart_item_meta['tmdata']['tmcartepo_data'][ $key ]['attribute'],
							$cart_item_meta["tmdata"]["per_product_pricing"],
							$cpf_product_price,
							$cart_item_meta["tmdata"]["variation_id"] );

						$cart_item_meta['tmcartepo'][ $key ]['price'] = $_price;
						$cart_item_meta['tmcartepo'][ $key ]['price_per_currency'] = $price_per_currency;

						if ( $_price_type == "percent" ) {
							$post_data['tm_epo_options_static_prices'] = $post_data['tm_epo_options_static_prices'] + $_price;
						}

					}

				}
			}

			foreach ( $percentcurrenttotal as $key ) {
				$value = $cart_item_meta['tmcartepo'][ $key ];

				if ( !isset( $element_object[ $value['section'] ] ) ) {
					continue;
				}

				if ( $value["mode"] == "builder" ) {

					$new_key = FALSE;
					$wpml_translation_by_id = TM_EPO_WPML()->get_wpml_translation_by_id( $product_id, TRUE );
					if ( !empty( $value['multiple'] ) && !empty( $value['key'] ) ) {
						$pos = strrpos( $value['key'], '_' );
						if ( $pos !== FALSE && isset( $wpml_translation_by_id[ "options_" . $value['section'] ] ) && is_array( $wpml_translation_by_id[ "options_" . $value['section'] ] ) ) {
							$av = array_values( $wpml_translation_by_id[ "options_" . $value['section'] ] );
							$ak = array_keys( $wpml_translation_by_id[ "options_" . $value['section'] ] );
							if ( isset( $av[ substr( $value['key'], $pos + 1 ) ] ) ) {
								$new_key = $ak[ substr( $value['key'], $pos + 1 ) ];
							}
						}
					}

					$price_per_currencies = isset( $element_object[ $value['section'] ]['price_per_currencies'] ) ? $element_object[ $value['section'] ]['price_per_currencies'] : array();
					$price_per_currency = array();
					$_price_type = $this->get_element_price_type( $value );

					foreach ( $price_per_currencies as $currency => $price_rule ) {
						$copy_element = $element_object[ $value['section'] ];
						$copy_element['price_rules'] = $price_rule;
						$currency_price = $this->calculate_price( $post_data,
							$copy_element,
							($new_key !== FALSE) ? $new_key : $cart_item_meta['tmdata']['tmcartepo_data'][ $key ]['key'],
							$cart_item_meta['tmdata']['tmcartepo_data'][ $key ]['attribute'],
							$cart_item_meta["tmdata"]["per_product_pricing"],
							$cpf_product_price,
							$cart_item_meta["tmdata"]["variation_id"],
							'',
							$currency,
							$current_currency,
							$price_per_currencies );

						$price_per_currency[ $currency ] = $currency_price;
					}

					$_price = $this->calculate_price( $post_data,
						$element_object[ $value['section'] ],
						($new_key !== FALSE) ? $new_key : $cart_item_meta['tmdata']['tmcartepo_data'][ $key ]['key'],
						$cart_item_meta['tmdata']['tmcartepo_data'][ $key ]['attribute'],
						$cart_item_meta["tmdata"]["per_product_pricing"],
						$cpf_product_price,
						$cart_item_meta["tmdata"]["variation_id"] );

					$cart_item_meta['tmcartepo'][ $key ]['price'] = $_price;
					$cart_item_meta['tmcartepo'][ $key ]['price_per_currency'] = $price_per_currency;

				}

			}

		}

		return $cart_item_meta;
	}

	/**
	 * @param $global_sections
	 * @param $global_prices
	 * @param $where
	 * @param $tmcp_post_fields
	 * @param $passed
	 * @param $loop
	 * @param $form_prefix
	 * @return array
	 */
	public function validate_product_id_loop( $global_sections, $global_prices, $where, $tmcp_post_fields, $passed, $loop, $form_prefix ) {

		foreach ( $global_prices[ $where ] as $priorities ) {
			foreach ( $priorities as $field ) {
				foreach ( $field['sections'] as $section_id => $section ) {
					if ( isset( $section['elements'] ) ) {
						foreach ( $section['elements'] as $element ) {

							if ( in_array( $element['type'], $this->element_post_types ) ) {
								$loop++;
							}

							if ( isset( $this->tm_builder_elements[ $element['type'] ] )
								&& isset( $this->tm_builder_elements[ $element['type'] ] )
								&& $this->tm_builder_elements[ $element['type'] ]["is_post"] != "display"
								&& $this->is_visible( $element, $section, $global_sections, $form_prefix )
							) {

								$_passed = TRUE;
								$_message = FALSE;

								$init_class = "TM_EPO_FIELDS_" . $element['type'];
								if ( !class_exists( $init_class ) && !empty( $this->tm_builder_elements[ $element['type'] ]["_is_addon"] ) ) {
									$init_class = "TM_EPO_FIELDS";
								}
								if ( class_exists( $init_class ) ) {
									$field_obj = new $init_class();
									$_passed = $field_obj->validate_field( $tmcp_post_fields, $element, $loop, $form_prefix );
									$_message = isset( $_passed["message"] ) ? $_passed["message"] : FALSE;
									$_passed = isset( $_passed["passed"] ) ? $_passed["passed"] : FALSE;
									unset( $field_obj ); // clear memory
								}

								if ( !$_passed ) {

									$passed = FALSE;
									if ( $_message !== FALSE && is_array( $_message ) ) {
										foreach ( $_message as $key => $value ) {
											if ( $value == 'required' ) {
												wc_add_notice( sprintf( __( '"%s" is a required field.', 'woocommerce-tm-extra-product-options' ), $element['label'] ), 'error' );
											} else {
												wc_add_notice( $value, 'error' );
											}
										}
									}

								}
							}

						}
					}
				}
			}
		}

		return array( 'loop' => $loop, 'passed' => $passed );

	}

	/**
	 * @param $product_id
	 * @param $qty
	 * @param string $form_prefix
	 * @return bool
	 */
	public function validate_product_id( $product_id, $qty, $form_prefix = "" ) {

		$passed = TRUE;

		if ( $form_prefix ) {
			$form_prefix = "_" . $form_prefix;
		}
		$cpf_price_array = $this->get_product_tm_epos( $product_id );
		if ( empty( $cpf_price_array ) ) {
			return $passed;
		}
		$global_price_array = $cpf_price_array['global'];
		$local_price_array = $cpf_price_array['local'];
		if ( empty( $global_price_array ) && empty( $local_price_array ) ) {
			return $passed;
		}
		$global_prices = array( 'before' => array(), 'after' => array() );
		$global_sections = array();
		foreach ( $global_price_array as $priority => $priorities ) {
			foreach ( $priorities as $pid => $field ) {
				if ( isset( $field['sections'] ) ) {
					foreach ( $field['sections'] as $section_id => $section ) {
						if ( isset( $section['sections_placement'] ) ) {
							$global_prices[ $section['sections_placement'] ][ $priority ][ $pid ]['sections'][ $section_id ] = $section;
							$global_sections[ $section['sections_uniqid'] ] = $section;
						}
					}
				}
			}
		}

		if ( (!empty( $global_price_array ) && is_array( $global_price_array ) && count( $global_price_array ) > 0) || (!empty( $local_price_array ) && is_array( $local_price_array ) && count( $local_price_array ) > 0) ) {
			$tmcp_post_fields = TM_EPO_HELPER()->array_filter_key( $_REQUEST );
			if ( is_array( $tmcp_post_fields ) && !empty( $tmcp_post_fields ) && count( $tmcp_post_fields ) > 0 ) {
				$tmcp_post_fields = array_map( 'stripslashes_deep', $tmcp_post_fields );
			}


			$loop = -1;

			$_return = $this->validate_product_id_loop( $global_sections, $global_prices, 'before', $tmcp_post_fields, $passed, $loop, $form_prefix );
			extract( $_return, EXTR_OVERWRITE );

			// todo: move this code to a function
			if ( !empty( $local_price_array ) && is_array( $local_price_array ) && count( $local_price_array ) > 0 ) {

				foreach ( $local_price_array as $tmcp ) {

					if ( in_array( $tmcp['type'], $this->element_post_types ) ) {
						$loop++;
					}
					if ( empty( $tmcp['type'] ) || empty( $tmcp['required'] ) ) {
						continue;
					}

					if ( $tmcp['required'] ) {

						$tmcp_attributes = $this->translate_fields( $tmcp['attributes'], $tmcp['type'], $loop, $form_prefix );
						$_passed = TRUE;

						switch ( $tmcp['type'] ) {

							case "checkbox" :
								$_check = array_intersect( $tmcp_attributes, array_keys( $tmcp_post_fields ) );
								if ( empty( $_check ) || count( $_check ) == 0 ) {
									$_passed = FALSE;
								}
								break;

							case "radio" :
								foreach ( $tmcp_attributes as $attribute ) {
									if ( !isset( $tmcp_post_fields[ $attribute ] ) ) {
										$_passed = FALSE;
									}
								}
								break;

							case "select" :
								foreach ( $tmcp_attributes as $attribute ) {
									if ( !isset( $tmcp_post_fields[ $attribute ] ) || $tmcp_post_fields[ $attribute ] == "" ) {
										$_passed = FALSE;
									}
								}
								break;

						}

						if ( !$_passed ) {
							$passed = FALSE;
							wc_add_notice( sprintf( __( '"%s" is a required field.', 'woocommerce-tm-extra-product-options' ), $tmcp['label'] ), 'error' );

						}
					}
				}

			}

			$_return = $this->validate_product_id_loop( $global_sections, $global_prices, 'after', $tmcp_post_fields, $passed, $loop, $form_prefix );
			extract( $_return, EXTR_OVERWRITE );

		}

		return $passed;

	}

	/**
	 * @return string|void
	 */
	public function tm_woocommerce_product_single_add_to_cart_text() {
		
		return (!empty( $this->tm_epo_update_cart_text )) ? $this->tm_epo_update_cart_text : esc_attr__( 'Update cart', 'woocommerce' );

	}

	/**
	 * @param $element
	 * @return array
	 */
	public function get_tm_validation_rules( $element ) {

		$rules = array();
		if ( $element['required'] ) {
			$rules['required'] = TRUE;
		}
		if ( isset( $element['min_chars'] ) && $element['min_chars'] !== '' && $element['min_chars'] !== FALSE ) {
			$rules['minlength'] = absint( $element['min_chars'] );
		}
		if ( isset( $element['max_chars'] ) && $element['max_chars'] !== '' && $element['max_chars'] !== FALSE ) {
			$rules['maxlength'] = absint( $element['max_chars'] );
		}
		if ( isset( $element['min'] ) && $element['min'] !== '' ) {
			$rules['min'] = floatval( $element['min'] );
		}
		if ( isset( $element['max'] ) && $element['max'] !== '' ) {
			$rules['max'] = floatval( $element['max'] );
		}
		if ( !empty( $element['validation1'] ) ) {
			$rules[ $element['validation1'] ] = TRUE;
		}

		return $rules;

	}

	/**
	 * Handles the display of builder sections.
	 */
	public function get_builder_display( $field, $where, $args, $form_prefix = "", $product_id = 0, $dummy_prefix = FALSE ) {

		/* $form_prefix	shoud be passed with _ if not empty */

		$columns = array(
			"w25"  => array( "col-3", 25 ),
			"w33"  => array( "col-4", 33 ),
			"w50"  => array( "col-6", 50 ),
			"w66"  => array( "col-8", 66 ),
			"w75"  => array( "col-9", 75 ),
			"w100" => array( "col-12", 100 ),
		);

		$tabindex = $args['tabindex'];
		$unit_counter = $args['unit_counter'];
		$field_counter = $args['field_counter'];
		$element_counter = $args['element_counter'];
		$_currency = $args['_currency'];
		$product_id = $args['product_id'];


		if ( isset( $field['sections'] ) && is_array( $field['sections'] ) ) {

			$args = array(
				'field_id' => 'tm-epo-field-' . $unit_counter,
			);
			wc_get_template(
				'tm-builder-start.php',
				$args,
				$this->_namespace,
				TM_EPO_TEMPLATE_PATH
			);

			$_section_totals = 0;

			foreach ( $field['sections'] as $section ) {
				if ( !isset( $section['sections_placement'] ) || $section['sections_placement'] != $where ) {
					continue;
				}
				if ( isset( $section['sections_size'] ) && isset( $columns[ $section['sections_size'] ] ) ) {
					$size = $columns[ $section['sections_size'] ][0];
				} else {
					$size = "col-12";
				}

				$_section_totals = $_section_totals + $columns[ $section['sections_size'] ][1];
				if ( $_section_totals > 100 ) {
					$_section_totals = $columns[ $section['sections_size'] ][1];
					echo '<div class="cpfclear"></div>';
				}

				$divider = "";
				if ( isset( $section['divider_type'] ) ) {
					switch ( $section['divider_type'] ) {
						case "hr":
							$divider = '<hr>';
							break;
						case "divider":
							$divider = '<div class="tm_divider"></div>';
							break;
						case "padding":
							$divider = '<div class="tm_padding"></div>';
							break;
					}
				}
				$label_size = 'h3';
				if ( !empty( $section['label_size'] ) ) {
					switch ( $section['label_size'] ) {
						case "1":
							$label_size = 'h1';
							break;
						case "2":
							$label_size = 'h2';
							break;
						case "3":
							$label_size = 'h3';
							break;
						case "4":
							$label_size = 'h4';
							break;
						case "5":
							$label_size = 'h5';
							break;
						case "6":
							$label_size = 'h6';
							break;
						case "7":
							$label_size = 'p';
							break;
						case "8":
							$label_size = 'div';
							break;
						case "9":
							$label_size = 'span';
							break;
					}
				}

				$args = array(
					'column'               => $size,
					'style'                => $section['sections_style'],
					'uniqid'               => $section['sections_uniqid'],
					'logic'                => esc_html( json_encode( (array) json_decode( stripslashes_deep( $section['sections_clogic'] ) ) ) ),
					'haslogic'             => $section['sections_logic'],
					'sections_class'       => $section['sections_class'],
					'sections_type'        => $section['sections_type'],
					'title_size'           => $label_size,
					'title'                => !empty( $section['label'] ) ? $section['label'] : "",
					'title_color'          => !empty( $section['label_color'] ) ? $section['label_color'] : "",
					'title_position'       => !empty( $section['label_position'] ) ? $section['label_position'] : "",
					'description'          => !empty( $section['description'] ) ? $section['description'] : "",
					'description_color'    => !empty( $section['description_color'] ) ? $section['description_color'] : "",
					'description_position' => !empty( $section['description_position'] ) ? $section['description_position'] : "",
					'divider'              => $divider,
				);
				// custom variations check
				if (
					isset( $section['elements'] )
					&& is_array( $section['elements'] )
					&& isset( $section['elements'][0] )
					&& is_array( $section['elements'][0] )
					&& isset( $section['elements'][0]['type'] )
					&& $section['elements'][0]['type'] == 'variations'
				) {
					$args['sections_class'] = $args['sections_class'] . " tm-epo-variation-section";
				}
				wc_get_template(
					'tm-builder-section-start.php',
					$args,
					$this->_namespace,
					TM_EPO_TEMPLATE_PATH
				);

				if ( isset( $section['elements'] ) && is_array( $section['elements'] ) ) {
					$totals = 0;

					$slide_counter = 0;
					$use_slides = FALSE;
					$doing_slides = FALSE;
					if ( $section['sections_slides'] !== "" && $section['sections_type'] == "slider" ) {
						$sections_slides = explode( ",", $section['sections_slides'] );
						$use_slides = TRUE;
					}

					foreach ( $section['elements'] as $element ) {

						$empty_rules = "";
						if ( isset( $element['rules_filtered'] ) ) {
							$empty_rules = esc_html( json_encode( ($element['rules_filtered']) ) );
						}
						$empty_original_rules = "";
						if ( isset( $element['original_rules_filtered'] ) ) {
							$empty_original_rules = esc_html( json_encode( ($element['original_rules_filtered']) ) );
						}
						$empty_rules_type = "";
						if ( isset( $element['rules_type'] ) ) {
							$empty_rules_type = esc_html( json_encode( ($element['rules_type']) ) );
						}
						if ( isset( $element['size'] ) && isset( $columns[ $element['size'] ] ) ) {
							$size = $columns[ $element['size'] ][0];
						} else {
							$size = "col-12";
						}
						$test_for_first_slide = FALSE;
						if ( $use_slides && isset( $sections_slides[ $slide_counter ] ) ) {
							$sections_slides[ $slide_counter ] = intval( $sections_slides[ $slide_counter ] );

							if ( $sections_slides[ $slide_counter ] > 0 && !$doing_slides ) {
								echo '<div class="tm-slide">';
								$doing_slides = TRUE;
								$test_for_first_slide = TRUE;
							}
						}

						$fee_name = $this->fee_name;
						$cart_fee_name = $this->cart_fee_name;
						$totals = $totals + $columns[ $element['size'] ][1];
						if ( $totals > 100 && !$test_for_first_slide ) {
							$totals = $columns[ $element['size'] ][1];
							echo '<div class="cpfclear"></div>';
						}

						$divider = "";
						if ( isset( $element['divider_type'] ) ) {
							$divider_class = "";
							if ( $element['type'] == 'divider' && !empty( $element['class'] ) ) {
								$divider_class = " " . $element['class'];
							}
							switch ( $element['divider_type'] ) {
								case "hr":
									$divider = '<hr' . $divider_class . '>';
									break;
								case "divider":
									$divider = '<div class="tm_divider' . $divider_class . '"></div>';
									break;
								case "padding":
									$divider = '<div class="tm_padding' . $divider_class . '"></div>';
									break;
							}
						}
						$label_size = 'h3';
						if ( !empty( $element['label_size'] ) ) {
							switch ( $element['label_size'] ) {
								case "1":
									$label_size = 'h1';
									break;
								case "2":
									$label_size = 'h2';
									break;
								case "3":
									$label_size = 'h3';
									break;
								case "4":
									$label_size = 'h4';
									break;
								case "5":
									$label_size = 'h5';
									break;
								case "6":
									$label_size = 'h6';
									break;
								case "7":
									$label_size = 'p';
									break;
								case "8":
									$label_size = 'div';
									break;
								case "9":
									$label_size = 'span';
									break;
								case "10":
									$label_size = 'label';
									break;
							}
						}

						$variations_builder_element_start_args = array();
						$tm_validation = $this->get_tm_validation_rules( $element );
						$args = array(
							'tm_element_settings'  => $element,
							'column'               => $size,
							'class'                => !empty( $element['class'] ) ? $element['class'] : "",
							'container_id'         => !empty( $element['container_id'] ) ? $element['container_id'] : "",
							'title_size'           => $label_size,
							'title'                => !empty( $element['label'] ) ? $element['label'] : "",
							'title_position'       => !empty( $element['label_position'] ) ? $element['label_position'] : "",
							'title_color'          => !empty( $element['label_color'] ) ? $element['label_color'] : "",
							'description'          => !empty( $element['description'] ) ? $element['description'] : "",
							'description_color'    => !empty( $element['description_color'] ) ? $element['description_color'] : "",
							'description_position' => !empty( $element['description_position'] ) ? $element['description_position'] : "",
							'divider'              => $divider,
							'required'             => $element['required'],
							'type'                 => $element['type'],
							'use_images'           => $element['use_images'],
							'use_colors'           => $element['use_colors'],
							'use_url'              => $element['use_url'],
							'rules'                => $empty_rules,
							'original_rules'       => $empty_original_rules,
							'rules_type'           => $empty_rules_type,
							'element'              => $element['type'],
							'class_id'             => "tm-element-ul-" . $element['type'] . " element_" . $element_counter . $form_prefix,// this goes on ul
							'uniqid'               => $element['uniqid'],
							'logic'                => esc_html( json_encode( (array) json_decode( stripslashes_deep($element['clogic']) ) ) ),
							'haslogic'             => $element['logic'],
							'clear_options'        => empty( $element['clear_options'] ) ? "" : $element['clear_options'],
							'exactlimit'           => empty( $element['exactlimit'] ) ? "" : 'tm-exactlimit',
							'minimumlimit'         => empty( $element['minimumlimit'] ) ? "" : 'tm-minimumlimit',
							'tm_validation'        => esc_html( json_encode( ($tm_validation) ) ),
						);
						if ( $element['type'] != "variations" ) {
							wc_get_template(
								'tm-builder-element-start.php',
								$args,
								$this->_namespace,
								TM_EPO_TEMPLATE_PATH
							);
						} else {
							$variations_builder_element_start_args = $args;
						}
						$field_counter = 0;

						$init_class = "TM_EPO_FIELDS_" . $element['type'];
						if ( !class_exists( $init_class ) && !empty( $this->tm_builder_elements[ $element['type'] ]["_is_addon"] ) ) {
							$init_class = "TM_EPO_FIELDS";
						}

						if ( isset( $this->tm_builder_elements[ $element['type'] ] )
							&& ($this->tm_builder_elements[ $element['type'] ]["is_post"] == "post" || $this->tm_builder_elements[ $element['type'] ]["is_post"] == "display")
							&& class_exists( $init_class )
						) {

							$field_obj = new $init_class();

							if ( $this->tm_builder_elements[ $element['type'] ]["is_post"] == "post" ) {

								if ( $this->tm_builder_elements[ $element['type'] ]["type"] == "single" || $this->tm_builder_elements[ $element['type'] ]["type"] == "multipleallsingle" || $this->tm_builder_elements[ $element['type'] ]["type"] == "multiplesingle" ) {

									$tabindex++;
									$name_inc = $this->tm_builder_elements[ $element['type'] ]["post_name_prefix"] . "_" . $element_counter . ($dummy_prefix ? "" : (($form_prefix !== "") ? "_" . str_replace( "_", "", $form_prefix ) : ""));
									if ( $this->tm_builder_elements[ $element['type'] ]["type"] == "single" || $this->tm_builder_elements[ $element['type'] ]["type"] == "multipleallsingle" ) {
										$is_fee = (!empty( $element['rules_type'] ) && $element['rules_type'][0][0] == "subscriptionfee");
										$is_cart_fee = (!empty( $element['rules_type'] ) && isset( $element['rules_type'][0] ) && isset( $element['rules_type'][0][0] ) && in_array( $element['rules_type'][0][0], array( "fee", "stepfee", "currentstepfee" ) ));
									} elseif ( $this->tm_builder_elements[ $element['type'] ]["type"] == "multiplesingle" ) {
										$is_fee = (!empty( $element['selectbox_fee'] ) && $element['selectbox_fee'][0][0] == "subscriptionfee");
										$is_cart_fee = (!empty( $element['selectbox_cart_fee'] ) && isset( $element['selectbox_cart_fee'][0] ) && isset( $element['selectbox_cart_fee'][0][0] ) && in_array( $element['selectbox_cart_fee'][0][0], array( "fee", "stepfee", "currentstepfee" ) ));
									}
									if ( $is_fee ) {
										$name_inc = $fee_name . $name_inc;
									} elseif ( $is_cart_fee ) {
										$name_inc = $cart_fee_name . $name_inc;
									}


									if ( isset ( $_GET['switch-subscription'] ) && (function_exists( 'wcs_get_subscription' ) || (class_exists( 'WC_Subscriptions_Manager' ) && class_exists( 'WC_Subscriptions_Order' ))) ) {
										$item = FALSE;
										if ( function_exists( 'wcs_get_subscription' ) ) {
											$subscription = wcs_get_subscription( $_GET['switch-subscription'] );
											if ( $subscription instanceof WC_Subscription ) {
												$original_order = new WC_Order( $subscription->order->id );
												$item = WC_Subscriptions_Order::get_item_by_product_id( $original_order, $subscription->id );
											}
										} else {
											$subscription = WC_Subscriptions_Manager::get_subscription( $_GET['switch-subscription'] );
											$original_order = new WC_Order( $subscription['order_id'] );
											$item = WC_Subscriptions_Order::get_item_by_product_id( $original_order, $subscription['product_id'] );
										}

										if ( $item ) {//need fix after new subscriptions for 2.7 (item_meta)
											$saved_data = maybe_unserialize( $item["item_meta"]["_tmcartepo_data"][0] );
											foreach ( $saved_data as $key => $val ) {
												if ( isset( $val["key"] ) ) {
													if ( $element['uniqid'] == $val["section"] ) {
														$_GET[ 'tmcp_' . $name_inc ] = $val["key"];
														if ( isset( $val['quantity'] ) ) {
															$_GET[ 'tmcp_' . $name_inc . '_quantity' ] = $val['quantity'];
														}
													}
												} else {
													if ( $element['uniqid'] == $val["section"] ) {
														$_GET[ 'tmcp_' . $name_inc ] = $val["value"];
														if ( isset( $val['quantity'] ) ) {
															$_GET[ 'tmcp_' . $name_inc . '_quantity' ] = $val['quantity'];
														}
													}
												}
											}
										}

									} elseif ( ( !empty( $this->cart_edit_key ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'tm-edit' ) ) || (!empty( $this->cart_edit_key ) && isset( $_REQUEST['update-composite'] )  ) ) {
										$_cart = WC()->cart;
										if ( isset( $_cart->cart_contents ) && isset( $_cart->cart_contents[ $this->cart_edit_key ] ) ) {
											if ( !empty( $_cart->cart_contents[ $this->cart_edit_key ]['tmcartepo'] ) ) {
												$saved_epos = $_cart->cart_contents[ $this->cart_edit_key ]['tmcartepo'];
												foreach ( $saved_epos as $key => $val ) {
													if ( isset( $val["key"] ) ) {
														if ( $element['uniqid'] == $val["section"] ) {
															$_GET[ 'tmcp_' . $name_inc ] = $val["key"];
															if ( isset( $val['quantity'] ) ) {
																$_GET[ 'tmcp_' . $name_inc . '_quantity' ] = $val['quantity'];
															}
														}
													} else {
														if ( $element['uniqid'] == $val["section"] ) {
															$_GET[ 'tmcp_' . $name_inc ] = $val["value"];
															if ( isset( $val['quantity'] ) ) {
																$_GET[ 'tmcp_' . $name_inc . '_quantity' ] = $val['quantity'];
															}
														}
													}
												}
											}
											if ( !empty( $_cart->cart_contents[ $this->cart_edit_key ]['tmcartfee'] ) ) {
												$saved_fees = $_cart->cart_contents[ $this->cart_edit_key ]['tmcartfee'];
												foreach ( $saved_fees as $key => $val ) {
													if ( isset( $val["key"] ) ) {
														if ( $element['uniqid'] == $val["section"] ) {
															$_GET[ 'tmcp_' . $name_inc ] = $val["key"];
															if ( isset( $val['quantity'] ) ) {
																$_GET[ 'tmcp_' . $name_inc . '_quantity' ] = $val['quantity'];
															}
														}
													} else {
														if ( $element['uniqid'] == $val["section"] ) {
															$_GET[ 'tmcp_' . $name_inc ] = $val["value"];
															if ( isset( $val['quantity'] ) ) {
																$_GET[ 'tmcp_' . $name_inc . '_quantity' ] = $val['quantity'];
															}
														}
													}
												}
											}
										}
									}

									$display = $field_obj->display_field( $element, array(
										'name'            => 'tmcp_' . $name_inc,
										'name_inc'        => $name_inc,
										'element_counter' => $element_counter,
										'tabindex'        => $tabindex,
										'form_prefix'     => $form_prefix,
										'field_counter'   => $field_counter ) );

									if ( is_array( $display ) ) {

										$original_amount = "";
										if ( isset( $element['original_rules_filtered'][0] ) && isset( $element['original_rules_filtered'][0][0] ) ) {
											$original_amount = $element['original_rules_filtered'][0][0];
										} else {
											$original_amount = $element['original_rules_filtered'][ esc_attr( reset( $element['options'] ) ) . "_0" ];
											if ( isset( $original_amount[0] ) ) {
												$original_amount = $original_amount[0];
											} else {
												$original_amount = "";
											}
										}
										if ( isset( $display["default_value_counter"] ) && $display["default_value_counter"] !== FALSE ) {
											$original_amount = $element['original_rules_filtered'][ $display['default_value_counter'] ][0];
										}

										$amount = "";
										if ( isset( $element['rules_filtered'][0] ) && isset( $element['rules_filtered'][0][0] ) ) {
											$amount = $element['rules_filtered'][0][0];
										} else {
											$amount = $element['rules_filtered'][ esc_attr( reset( $element['options'] ) ) . "_0" ];
											if ( isset( $amount[0] ) ) {
												$amount = $amount[0];
											} else {
												$amount = "";
											}
										}
										if ( isset( $display["default_value_counter"] ) && $display["default_value_counter"] !== FALSE ) {
											$amount = $element['rules_filtered'][ $display['default_value_counter'] ][0];
										}
										$args = array(
											'tm_element_settings' => $element,
											'id'                  => 'tmcp_' . $this->tm_builder_elements[ $element['type'] ]["post_name_prefix"] . '_' . $tabindex . $form_prefix,
											'name'                => 'tmcp_' . $name_inc,
											'class'               => !empty( $element['class'] ) ? $element['class'] : "",
											'tabindex'            => $tabindex,
											'rules'               => isset( $element['rules_filtered'] ) ? esc_html( json_encode( ($element['rules_filtered']) ) ) : '',
											'original_rules'      => isset( $element['original_rules_filtered'] ) ? esc_html( json_encode( ($element['original_rules_filtered']) ) ) : '',
											'rules_type'          => isset( $element['rules_type'] ) ? esc_html( json_encode( ($element['rules_type']) ) ) : '',
											'amount'              => $amount . ' ' . $_currency,
											'original_amount'     => $original_amount . ' ' . $_currency,
											'fieldtype'           => $is_fee ? $this->fee_name_class : ($is_cart_fee ? $this->cart_fee_class : "tmcp-field"),
											'field_counter'       => $field_counter,
											'tax_obj'             => !($is_fee || $is_cart_fee) ? FALSE : esc_html( json_encode( (array(
												'is_fee'    => $is_fee || $is_cart_fee,
												'has_fee'   => isset( $element['include_tax_for_fee_price_type'] ) ? $element['include_tax_for_fee_price_type'] : '',
												'tax_class' => isset( $element['tax_class_for_fee_price_type'] ) ? $element['tax_class_for_fee_price_type'] : '',
												'tax_rate'  => $this->get_tax_rate( isset( $element['tax_class_for_fee_price_type'] ) ? $element['tax_class_for_fee_price_type'] : '' ),
											)) ) ),
										);

										$args = array_merge( $args, $display );

										if ( $this->tm_builder_elements[ $element['type'] ]["_is_addon"] ) {
											do_action( "tm_epo_display_addons", $element, $args, array(
												'name_inc'        => $name_inc,
												'element_counter' => $element_counter,
												'tabindex'        => $tabindex,
												'form_prefix'     => $form_prefix,
												'field_counter'   => $field_counter ), $this->tm_builder_elements[ $element['type'] ]["namespace"] );
										} elseif ( is_readable( apply_filters( 'wc_epo_template_path_element', TM_EPO_TEMPLATE_PATH, $element['type'], $element ) . apply_filters( 'wc_epo_template_element', 'tm-' . $element['type'] . '.php', $element['type'], $element ) ) ) {
											wc_get_template(
												apply_filters( 'wc_epo_template_element', 'tm-' . $element['type'] . '.php', $element['type'], $element ),
												$args,
												$this->_namespace,
												apply_filters( 'wc_epo_template_path_element', TM_EPO_TEMPLATE_PATH, $element['type'], $element )
											);
										}
									}

								} elseif ( $this->tm_builder_elements[ $element['type'] ]["type"] == "multipleall" || $this->tm_builder_elements[ $element['type'] ]["type"] == "multiple" ) {

									$field_obj->display_field_pre( $element, array(
										'element_counter' => $element_counter,
										'tabindex'        => $tabindex,
										'form_prefix'     => $form_prefix,
										'field_counter'   => $field_counter,
										'product_id'      => isset( $product_id ) ? $product_id : 0,
									) );

									foreach ( $element['options'] as $value => $label ) {

										$tabindex++;
										if ( $this->tm_builder_elements[ $element['type'] ]["type"] == "multipleall" ) {
											$name_inc = $this->tm_builder_elements[ $element['type'] ]["post_name_prefix"] . "_" . $element_counter . "_" . $field_counter . ($dummy_prefix ? "" : (($form_prefix !== "") ? "_" . str_replace( "_", "", $form_prefix ) : ""));
										} else {
											$name_inc = $this->tm_builder_elements[ $element['type'] ]["post_name_prefix"] . "_" . $element_counter . ($dummy_prefix ? "" : (($form_prefix !== "") ? "_" . str_replace( "_", "", $form_prefix ) : ""));
										}

										$is_fee = (isset( $element['rules_type'][ $value ] ) && $element['rules_type'][ $value ][0] == "subscriptionfee");
										$is_cart_fee = (isset( $element['rules_type'][ $value ] ) && in_array( $element['rules_type'][ $value ][0], array( "fee", "stepfee", "currentstepfee" ) ));
										if ( $is_fee ) {
											$name_inc = $fee_name . $name_inc;
										} elseif ( $is_cart_fee ) {
											$name_inc = $cart_fee_name . $name_inc;
										}
										if ( isset ( $_GET['switch-subscription'] ) && (function_exists( 'wcs_get_subscription' ) || (class_exists( 'WC_Subscriptions_Manager' ) && class_exists( 'WC_Subscriptions_Order' ))) ) {
											$item = FALSE;
											if ( function_exists( 'wcs_get_subscription' ) ) {
												$subscription = wcs_get_subscription( $_GET['switch-subscription'] );
												if ( $subscription instanceof WC_Subscription ) {
													$original_order = new WC_Order( $subscription->order->id );
													$item = WC_Subscriptions_Order::get_item_by_product_id( $original_order, $subscription->id );
												}
											} else {
												$subscription = WC_Subscriptions_Manager::get_subscription( $_GET['switch-subscription'] );
												$original_order = new WC_Order( $subscription['order_id'] );
												$item = WC_Subscriptions_Order::get_item_by_product_id( $original_order, $subscription['product_id'] );
											}


											if ( $item ) {//need fix after new subscriptions for 2.7 (item_meta)
												$saved_data = maybe_unserialize( $item["item_meta"]["_tmcartepo_data"][0] );
												foreach ( $saved_data as $key => $val ) {
													if ( $element['uniqid'] == $val["section"] && $value == $val["key"] ) {
														$_GET[ 'tmcp_' . $name_inc ] = $val["key"];
														if ( isset( $val['quantity'] ) ) {
															$_GET[ 'tmcp_' . $name_inc . '_quantity' ] = $val['quantity'];
														}
													}
												}
											}
										} elseif ( !empty( $this->cart_edit_key ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'tm-edit' ) ) {
											$_cart = WC()->cart;
											if ( isset( $_cart->cart_contents ) && isset( $_cart->cart_contents[ $this->cart_edit_key ] ) ) {
												if ( !empty( $_cart->cart_contents[ $this->cart_edit_key ]['tmcartepo'] ) ) {
													$saved_epos = $_cart->cart_contents[ $this->cart_edit_key ]['tmcartepo'];
													foreach ( $saved_epos as $key => $val ) {
														if ( $element['uniqid'] == $val["section"] && $value == $val["key"] ) {
															$_GET[ 'tmcp_' . $name_inc ] = $val["key"];
															if ( isset( $val['quantity'] ) ) {
																$_GET[ 'tmcp_' . $name_inc . '_quantity' ] = $val['quantity'];
															}
														}
													}
												}
												if ( !empty( $_cart->cart_contents[ $this->cart_edit_key ]['tmcartfee'] ) ) {
													$saved_fees = $_cart->cart_contents[ $this->cart_edit_key ]['tmcartfee'];
													foreach ( $saved_fees as $key => $val ) {
														if ( $element['uniqid'] == $val["section"] && $value == $val["key"] ) {
															$_GET[ 'tmcp_' . $name_inc ] = $val["key"];
															if ( isset( $val['quantity'] ) ) {
																$_GET[ 'tmcp_' . $name_inc . '_quantity' ] = $val['quantity'];
															}
														}
													}
												}
											}
										}

										$display = $field_obj->display_field( $element, array(
											'name'            => 'tmcp_' . $name_inc,
											'name_inc'        => $name_inc,
											'value'           => $value,
											'label'           => $label,
											'element_counter' => $element_counter,
											'tabindex'        => $tabindex,
											'form_prefix'     => $form_prefix,
											'field_counter'   => $field_counter ) );

										if ( is_array( $display ) ) {

											$original_amount = $element['original_rules_filtered'][ $value ][0];

											$amount = $element['rules_filtered'][ $value ][0];

											$args = array(
												'tm_element_settings' => $element,
												'id'                  => 'tmcp_' . $this->tm_builder_elements[ $element['type'] ]["post_name_prefix"] . '_' . $element_counter . "_" . $field_counter . "_" . $tabindex . $form_prefix,
												'name'                => 'tmcp_' . $name_inc,
												'class'               => !empty( $element['class'] ) ? $element['class'] : "",
												'tabindex'            => $tabindex,
												'rules'               => isset( $element['rules_filtered'][ $value ] ) ? esc_html( json_encode( ($element['rules_filtered'][ $value ]) ) ) : '',
												'original_rules'      => isset( $element['original_rules_filtered'][ $value ] ) ? esc_html( json_encode( ($element['original_rules_filtered'][ $value ]) ) ) : '',
												'rules_type'          => isset( $element['rules_type'][ $value ] ) ? esc_html( json_encode( ($element['rules_type'][ $value ]) ) ) : '',
												'amount'              => $amount . ' ' . $_currency,
												'original_amount'     => $original_amount . ' ' . $_currency,
												'fieldtype'           => $is_fee ? $this->fee_name_class : ($is_cart_fee ? $this->cart_fee_class : "tmcp-field"),
												'border_type'         => $this->tm_epo_css_selected_border,
												'field_counter'       => $field_counter,
												'tax_obj'             => !($is_fee || $is_cart_fee) ? FALSE : esc_html( json_encode( (array(
													'is_fee'    => $is_fee || $is_cart_fee,
													'has_fee'   => isset( $element['include_tax_for_fee_price_type'] ) ? $element['include_tax_for_fee_price_type'] : '',
													'tax_class' => isset( $element['tax_class_for_fee_price_type'] ) ? $element['tax_class_for_fee_price_type'] : '',
													'tax_rate'  => $this->get_tax_rate( isset( $element['tax_class_for_fee_price_type'] ) ? $element['tax_class_for_fee_price_type'] : '' ),
												)) ) ),
											);

											$args = array_merge( $args, $display );

											if ( $this->tm_builder_elements[ $element['type'] ]["_is_addon"] ) {
												do_action( "tm_epo_display_addons", $element, $args, array(
													'name_inc'        => $name_inc,
													'element_counter' => $element_counter,
													'tabindex'        => $tabindex,
													'form_prefix'     => $form_prefix,
													'field_counter'   => $field_counter,
													'border_type'     => $this->tm_epo_css_selected_border ), $this->tm_builder_elements[ $element['type'] ]["namespace"] );
											} elseif ( is_readable( apply_filters( 'wc_epo_template_path_element', TM_EPO_TEMPLATE_PATH, $element['type'], $element ) . apply_filters( 'wc_epo_template_element', 'tm-' . $element['type'] . '.php', $element['type'], $element ) ) ) {
												wc_get_template(
													apply_filters( 'wc_epo_template_element', 'tm-' . $element['type'] . '.php', $element['type'], $element ),
													$args,
													$this->_namespace,
													apply_filters( 'wc_epo_template_path_element', TM_EPO_TEMPLATE_PATH, $element['type'], $element )
												);
											}
										}

										$field_counter++;

									}

								}

								$element_counter++;

							} elseif ( $this->tm_builder_elements[ $element['type'] ]["is_post"] == "display" ) {

								$display = $field_obj->display_field( $element, array(
									'product_id'      => $product_id,
									'element_counter' => $element_counter,
									'tabindex'        => $tabindex,
									'form_prefix'     => $form_prefix,
									'field_counter'   => $field_counter ) );

								if ( is_array( $display ) ) {
									$args = array(
										'tm_element_settings' => $element,
										'class'               => !empty( $element['class'] ) ? $element['class'] : "",
										'form_prefix'         => $form_prefix,
										'field_counter'       => $field_counter,
										'tm_element'          => $element,
										'tm__namespace'       => $this->_namespace,
										'tm_template_path'    => TM_EPO_TEMPLATE_PATH,
										'tm_product_id'       => $product_id,
									);

									if ( $element['type'] == "variations" ) {
										$args["variations_builder_element_start_args"] = $variations_builder_element_start_args;
										$args["variations_builder_element_end_args"] = array(
											'tm_element_settings'  => $element,
											'element'              => $element['type'],
											'description'          => !empty( $element['description'] ) ? $element['description'] : "",
											'description_color'    => !empty( $element['description_color'] ) ? $element['description_color'] : "",
											'description_position' => !empty( $element['description_position'] ) ? $element['description_position'] : "",
										);
									}

									$args = array_merge( $args, $display );

									if ( $this->tm_builder_elements[ $element['type'] ]["_is_addon"] ) {
										do_action( "tm_epo_display_addons", $element, $args, array(
											'name_inc'        => '',
											'element_counter' => $element_counter,
											'tabindex'        => $tabindex,
											'form_prefix'     => $form_prefix,
											'field_counter'   => $field_counter ), $this->tm_builder_elements[ $element['type'] ]["namespace"] );
									} elseif ( is_readable( apply_filters( 'wc_epo_template_path_element', TM_EPO_TEMPLATE_PATH, $element['type'], $element ) . apply_filters( 'wc_epo_template_element', 'tm-' . $element['type'] . '.php', $element['type'], $element ) ) ) {
										wc_get_template(
											apply_filters( 'wc_epo_template_element', 'tm-' . $element['type'] . '.php', $element['type'], $element ),
											$args,
											$this->_namespace,
											apply_filters( 'wc_epo_template_path_element', TM_EPO_TEMPLATE_PATH, $element['type'], $element )
										);
									}
								}
							}

							unset( $field_obj ); // clear memory
						}

						if ( $element['type'] != "variations" ) {
							wc_get_template(
								'tm-builder-element-end.php',
								array(
									'tm_element_settings'  => $element,
									'element'              => $element['type'],
									'description'          => !empty( $element['description'] ) ? $element['description'] : "",
									'description_color'    => !empty( $element['description_color'] ) ? $element['description_color'] : "",
									'description_position' => !empty( $element['description_position'] ) ? $element['description_position'] : "",
								),
								$this->_namespace,
								TM_EPO_TEMPLATE_PATH
							);
						}

						if ( $use_slides && isset( $sections_slides[ $slide_counter ] ) ) {
							$sections_slides[ $slide_counter ] = $sections_slides[ $slide_counter ] - 1;

							if ( $sections_slides[ $slide_counter ] <= 0 ) {
								echo '</div>';
								$slide_counter++;
								$doing_slides = FALSE;
							}
						}

					}
				}
				$args = array(
					'column'               => $size,
					'style'                => $section['sections_style'],
					'sections_type'        => $section['sections_type'],
					'title_size'           => $label_size,
					'title'                => !empty( $section['label'] ) ? $section['label'] : "",
					'title_color'          => !empty( $section['label_color'] ) ? $section['label_color'] : "",
					'description'          => !empty( $section['description'] ) ? $section['description'] : "",
					'description_color'    => !empty( $section['description_color'] ) ? $section['description_color'] : "",
					'description_position' => !empty( $section['description_position'] ) ? $section['description_position'] : "",
				);
				wc_get_template(
					'tm-builder-section-end.php',
					$args,
					$this->_namespace,
					TM_EPO_TEMPLATE_PATH
				);

			}

			wc_get_template(
				'tm-builder-end.php',
				array(),
				$this->_namespace,
				TM_EPO_TEMPLATE_PATH
			);

			$unit_counter++;

		}

		return array(
			'tabindex'        => $tabindex,
			'unit_counter'    => $unit_counter,
			'field_counter'   => $field_counter,
			'element_counter' => $element_counter,
			'_currency'       => $_currency,
		);

	}

	/**
	 * @param $global_epos
	 * @param $field
	 * @param $where
	 * @param $args
	 * @param string $form_prefix
	 * @param string $add_identifier
	 * @return array
	 */
	public function fill_builder_display( $global_epos, $field, $where, $args, $form_prefix = "", $add_identifier = "" ) {

		/* $form_prefix	shoud be passed with _ if not empty */

		$priority = $args['priority'];
		$pid = $args['pid'];
		$unit_counter = $args['unit_counter'];
		$field_counter = $args['field_counter'];
		$element_counter = $args['element_counter'];

		if ( isset( $field['sections'] ) && is_array( $field['sections'] ) ) {
			foreach ( $field['sections'] as $_s => $section ) {
				if ( !isset( $section['sections_placement'] ) || $section['sections_placement'] != $where ) {
					continue;
				}
				if ( isset( $section['elements'] ) && is_array( $section['elements'] ) ) {
					foreach ( $section['elements'] as $arr_element_counter => $element ) {
						$fee_name = $this->fee_name;
						$cart_fee_name = $this->cart_fee_name;
						$field_counter = 0;

						if ( !empty( $add_identifier ) ) {
							$global_epos[ $priority ][ $pid ]['sections'][ $_s ]['elements'][ $arr_element_counter ]['add_identifier'] = $add_identifier;
						}
						if ( isset( $this->tm_builder_elements[ $element['type'] ] ) && $this->tm_builder_elements[ $element['type'] ]["is_post"] == "post" ) {

							if ( $this->tm_builder_elements[ $element['type'] ]["type"] == "multipleall" || $this->tm_builder_elements[ $element['type'] ]["type"] == "multiple" ) {

								foreach ( $element['options'] as $value => $label ) {

									if ( $this->tm_builder_elements[ $element['type'] ]["type"] == "multipleall" ) {
										$name_inc = $this->tm_builder_elements[ $element['type'] ]["post_name_prefix"] . "_" . $element_counter . "_" . $field_counter . (($form_prefix !== "") ? "_" . str_replace( "_", "", $form_prefix ) : "");
									} else {
										$name_inc = $this->tm_builder_elements[ $element['type'] ]["post_name_prefix"] . "_" . $element_counter . (($form_prefix !== "") ? "_" . str_replace( "_", "", $form_prefix ) : "");
									}

									$is_fee = (!empty( $element['rules_type'][ $value ] ) && $element['rules_type'][ $value ][0] == "subscriptionfee");
									$is_cart_fee = (!empty( $element['rules_type'][ $value ] ) && in_array( $element['rules_type'][ $value ][0], array( "fee", "stepfee", "currentstepfee" ) ));
									if ( $is_fee ) {
										$name_inc = $fee_name . $name_inc;
									} elseif ( $is_cart_fee ) {
										$name_inc = $cart_fee_name . $name_inc;
									}
									$name_inc = 'tmcp_' . $name_inc . (($form_prefix !== "") ? "_" . str_replace( "_", "", $form_prefix ) : "");
									$global_epos[ $priority ][ $pid ]['sections'][ $_s ]['elements'][ $arr_element_counter ]['name_inc'][] = $name_inc;
									$global_epos[ $priority ][ $pid ]['sections'][ $_s ]['elements'][ $arr_element_counter ]['is_fee'][] = $is_fee;
									$global_epos[ $priority ][ $pid ]['sections'][ $_s ]['elements'][ $arr_element_counter ]['is_cart_fee'][] = $is_cart_fee;

									$field_counter++;

								}

							} elseif ( $this->tm_builder_elements[ $element['type'] ]["type"] == "single" || $this->tm_builder_elements[ $element['type'] ]["type"] == "multipleallsingle" || $this->tm_builder_elements[ $element['type'] ]["type"] == "multiplesingle" ) {

								$name_inc = $this->tm_builder_elements[ $element['type'] ]["post_name_prefix"] . "_" . $element_counter . (($form_prefix !== "") ? "_" . str_replace( "_", "", $form_prefix ) : "");
								if ( $this->tm_builder_elements[ $element['type'] ]["type"] == "single" || $this->tm_builder_elements[ $element['type'] ]["type"] == "multipleallsingle" ) {
									$is_fee = (!empty( $element['rules_type'] ) && $element['rules_type'][0][0] == "subscriptionfee");
									$is_cart_fee = (!empty( $element['rules_type'] ) && isset( $element['rules_type'][0] ) && isset( $element['rules_type'][0][0] ) && in_array( $element['rules_type'][0][0], array( "fee", "stepfee", "currentstepfee" ) ));
								} elseif ( $this->tm_builder_elements[ $element['type'] ]["type"] == "multiplesingle" ) {
									$is_fee = (!empty( $element['selectbox_fee'] ) && $element['selectbox_fee'][0][0] == "subscriptionfee");
									$is_cart_fee = (!empty( $element['selectbox_cart_fee'] ) && in_array( $element['selectbox_cart_fee'][0][0], array( "fee", "stepfee", "currentstepfee" ) ));
								}
								if ( $is_fee ) {
									$name_inc = $fee_name . $name_inc;
								} elseif ( $is_cart_fee ) {
									$name_inc = $cart_fee_name . $name_inc;
								}
								$name_inc = 'tmcp_' . $name_inc . (($form_prefix !== "") ? "_" . str_replace( "_", "", $form_prefix ) : "");
								$global_epos[ $priority ][ $pid ]['sections'][ $_s ]['elements'][ $arr_element_counter ]['name_inc'] = $name_inc;
								$global_epos[ $priority ][ $pid ]['sections'][ $_s ]['elements'][ $arr_element_counter ]['is_fee'] = $is_fee;
								$global_epos[ $priority ][ $pid ]['sections'][ $_s ]['elements'][ $arr_element_counter ]['is_cart_fee'] = $is_cart_fee;

							}
							$element_counter++;
						}

					}
				}
			}
			$unit_counter++;
		}

		return array(
			'global_epos'     => $global_epos,
			'unit_counter'    => $unit_counter,
			'field_counter'   => $field_counter,
			'element_counter' => $element_counter,
		);

	}

}

define( 'TM_EPO_INCLUDED', 1 );
