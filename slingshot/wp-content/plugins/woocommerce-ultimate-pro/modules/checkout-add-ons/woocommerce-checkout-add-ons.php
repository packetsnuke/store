<?php
/**
 * Copyright: (c) 2014-2017, SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Checkout-Add-Ons
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2014-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library class
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once SV_WC_FRAMEWORK_FILE;
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.6.3', __( 'WooCommerce Checkout Add-Ons', 'ultimatewoo-pro' ), __FILE__, 'init_woocommerce_checkout_add_ons', array(
	'minimum_wc_version'   => '2.5.5',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4',
) );

function init_woocommerce_checkout_add_ons() {


/**
 * # WooCommerce Checkout Add-Ons Main Plugin Class
 *
 * ## Plugin Overview
 *
 * This plugin allows merchants to define paid order add-ons, which are
 * represented by fields shown during checkout and attached to the order.  They
 * are shown in the customer's account order details, in order emails, and in
 * the order Admin.
 *
 * Checkout add-ons are implemented using the WooCommerce core fee API
 *
 * ## Features
 *
 * + Define paid order add-on fields to be displayed at checkout
 *
 * ## Frontend Considerations
 *
 * On the frontend the checkout add on fields are rendered on the checkout page.
 * The selected options/values are displayed on the "thank you" page, order
 * details, and emails (if so configured).
 *
 * ## Admin Considerations
 *
 * Adds a WooCommerce menu item named Checkout Add-Ons which displays a list
 * table of configured add-on fields, along with the ability to create/update/
 * delete them.
 *
 * ## Database
 *
 * ### Options table
 *
 * + `wc_checkout_add_ons`                - the defined checkout add-ons
 * + `wc_checkout_add_ons_next_add_on_id` - unique, sequential id generator
 *
 * ### Order Item Meta
 *
 * + `_wc_checkout_add_on_id`    - The checkout add on id
 * + `_wc_checkout_add_on_value` - The checkout add on value selected/entered by the customer
 * + `_wc_checkout_add_on_label` - The value, normalized
 *
 * @since 1.0.0
 */
class WC_Checkout_Add_Ons extends SV_WC_Plugin {


	/** plugin version number */
	const VERSION = '1.10.4';

	/** @var WC_Checkout_Add_Ons single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'checkout_add_ons';

	/** plugin meta prefix */
	const PLUGIN_PREFIX = 'wc_checkout_add_ons_';

	/** @var \WC_Checkout_Add_Ons_Admin instance */
	protected $admin;

	/** @var \WC_Checkout_Add_Ons_Frontend instance */
	protected $frontend;

	/** @var \WC_Checkout_Add_Ons_Export_Handler instance */
	protected $export_handler;

	/** @var bool if WooCommerce Subscriptions is active */
	protected $subscriptions_active;


	/**
	 * Initializes the plugin
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'text_domain'        => 'woocommerce-checkout-add-ons',
				'display_php_notice' => true,
			)
		);

		// Initialize
		add_action( 'init', array( $this, 'includes' ) );

		// custom ajax handler for AJAX search
		add_action( 'wp_ajax_wc_checkout_add_ons_json_search_field', array( $this, 'add_json_search_field' ) );

		// add checkout add-ons value column header to order items table
		add_action( 'woocommerce_admin_order_item_headers', array( $this, 'add_order_item_headers' ) );

		// add checkout add-ons value column to order items table
		add_action( 'woocommerce_admin_order_item_values', array( $this, 'add_order_item_values' ), 10, 3 );

		// save checkout add-ons value
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'process_shop_order_meta' ), 10, 2 );

		// save checkout add-ons value via ajax
		add_action( 'wp_ajax_wc_checkout_add_ons_save_order_items', array( $this, 'save_order_item_values_ajax' ) );

		// Override default select/multiselect/radio value sanitization in special cases
		add_filter( 'sanitize_title', array( $this, 'sanitize_select_field_values' ), 10, 3 );
	}


	/**
	 * Include required files
	 *
	 * @since 1.0.0
	 */
	public function includes() {

		require_once( $this->get_plugin_path() . '/includes/class-wc-checkout-add-on.php' );

		$this->frontend       = $this->load_class( '/includes/frontend/class-wc-checkout-add-ons-frontend.php', 'WC_Checkout_Add_Ons_Frontend' );
		$this->export_handler = $this->load_class( '/includes/class-wc-checkout-add-ons-export-handler.php', 'WC_Checkout_Add_Ons_Export_Handler' );

		if ( is_admin() && ! is_ajax() ) {
			$this->admin_includes();
		}
	}


	/**
	 * Include required admin files
	 *
	 * @since 1.0.0
	 */
	private function admin_includes() {

		// load order list table/edit order customizations
		$this->admin = $this->load_class( '/includes/admin/class-wc-checkout-add-ons-admin.php', 'WC_Checkout_Add_Ons_Admin' );
	}


	/** Admin methods ******************************************************/


	/**
	 * Render a notice for the user to read the docs before adding add-ons
	 *
	 * @since 1.1.0
	 * @see SV_WC_Plugin::add_admin_notices()
	 */
	public function add_admin_notices() {

		// show any dependency notices
		parent::add_admin_notices();

		$this->get_admin_notice_handler()->add_admin_notice(
			/* translators: Placeholders: %1$s opening <a> link HTML tag, %2$s closing </a> link HTML tag */
			sprintf( __( 'Thanks for installing Checkout Add-Ons! Before you get started, please take a moment to %1$sread through the documentation%2$s.', 'ultimatewoo-pro' ),
				'<a href="' . $this->get_documentation_url() . '">',
				'</a>'
			),
			'read-the-docs',
			array(
				'always_show_on_settings' => false,
				'notice_class'            => 'updated',
			)
		);
	}


	/**
	 * AJAX search handler for enhanced multi-select fields.
	 * Searches for checkout add-ons and returns the results.
	 *
	 * @since 1.0.0
	 */
	public function add_json_search_field() {
		global $wpdb;

		check_ajax_referer( 'search-field', 'security' );

		// the search term
		$term = isset( $_GET['term'] ) ? urldecode( stripslashes( strip_tags( $_GET['term'] ) ) ) : '';

		// the field to search
		$id = isset( $_GET['request_data']['add_on_id'] ) ? urldecode( stripslashes( strip_tags( $_GET['request_data']['add_on_id'] ) ) ) : '';

		// required parameters
		if ( empty( $term ) || empty( $id ) ) {
			die;
		}

		$found_values = array();
		$query        = $wpdb->prepare( "
			SELECT woim_value.meta_value
			FROM {$wpdb->prefix}woocommerce_order_itemmeta woim_id
			JOIN {$wpdb->prefix}woocommerce_order_itemmeta woim_value ON woim_id.order_item_id = woim_value.order_item_id
			WHERE 1=1
				AND woim_id.meta_key = '_wc_checkout_add_on_id'
				AND woim_id.meta_value = %d
				AND woim_value.meta_key = '_wc_checkout_add_on_value'
				AND woim_value.meta_value LIKE %s
		", $id, '%' . $term . '%' );

		$results = $wpdb->get_results( $query );

		if ( $results ) {
			foreach ( $results as $result ) {
				$found_values[ $result->meta_value ] = $result->meta_value;
			}
		}

		echo json_encode( $found_values );

		exit;
	}


	/**
	 * Add checkout add-ons headers to the order items table
	 *
	 * @since 1.1.0
	 */
	public function add_order_item_headers() {
		global $post;

		echo '<th class="wc-checkout-add-ons-value">&nbsp;</th>';

		// enqueue ajax for saving add-on values
		$javascript = "
			jQuery( document.body ).on( 'items_saved', 'button.save-action', function() {
				jQuery.ajax( {
					type: 'POST',
					url: '" . admin_url( 'admin-ajax.php' ) . "',
					data: {
						action: 'wc_checkout_add_ons_save_order_items',
						security: '" . wp_create_nonce( "save-checkout-add-ons" ) . "',
						order_id: '" . ( isset( $post->ID ) ? $post->ID : '' ) . "',
						items: jQuery('table.woocommerce_order_items :input[name], .wc-order-totals-items :input[name]').serialize()
					}
				} );
			} );";

		wc_enqueue_js( $javascript );
	}


	/**
	 * Add checkout add-ons values to the order items table
	 *
	 * @since 1.1.0
	 * @param null $_ unused
	 * @param array $item
	 */
	public function add_order_item_values( $_, $item, $item_id ) {

		echo '<td class="wc-checkout-add-ons-value">';

		if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {

			$add_on_id    = $item->get_meta( '_wc_checkout_add_on_id' );
			$add_on_value = $item->get_meta( '_wc_checkout_add_on_value' );
			$add_on_label = $item->get_meta( '_wc_checkout_add_on_label' );

		} elseif ( is_array( $item ) && isset( $item['wc_checkout_add_on_id'] ) ) {

			$add_on_id    = $item['wc_checkout_add_on_id'];
			$add_on_value = $item['wc_checkout_add_on_value'];
			$add_on_label = $item['wc_checkout_add_on_label'];

		} else {

			$add_on_id = 0;
		}

		if ( $add_on_id && $add_on = $this->get_add_on( $add_on_id ) ) {

			$is_editable = in_array( $add_on->type, array( 'text', 'textarea' ), false );

			if ( 'textarea' === $add_on->type || 'file' === $add_on->type ) {
				$value = $add_on->normalize_value( $add_on_value, false );
			} else {
				$value = maybe_unserialize( $add_on_label );
				$value = esc_html( is_array( $value ) ? implode( ', ', $value ) : $value );
			}

			ob_start();
			?>

			<div class="view">
				<?php echo wp_kses_post( $value ); ?>
			</div>

			<?php if ( $is_editable ) : ?>
			<div class="edit" style="display: none;">

				<?php if ( 'textarea' === $add_on->type ) : ?>
					<textarea placeholder="<?php esc_attr_e( 'Checkout Add-on Value', 'ultimatewoo-pro' ); ?>" name="checkout_add_on_value[<?php echo esc_attr( $item_id ); ?>]"><?php echo esc_textarea( $add_on_value ); ?></textarea>
				<?php else : ?>
					<input type="text" placeholder="<?php esc_attr_e( 'Checkout Add-on Value', 'ultimatewoo-pro' ); ?>" name="checkout_add_on_value[<?php echo $item_id; ?>]" value="<?php echo $value; ?>" />
				<?php endif; ?>

				<input type="hidden" class="checkout_add_on_id" name="checkout_add_on_item_id[]" value="<?php echo $item_id; ?>" />
				<input type="hidden" class="checkout_add_on_id" name="checkout_add_on_id[<?php echo $item_id; ?>]" value="<?php echo $add_on->id; ?>" />
			</div>
			<?php endif; ?>

			<?php
			echo ob_get_clean();
		}

		echo '</td>';
	}


	/**
	 * Save checkout add-on values
	 *
	 * @since 1.2.0
	 * @param int $order_id Order ID
	 * @param array $items Order items to save
	 */
	public function save_order_item_values( $order_id, $items ) {

		if ( isset( $items['checkout_add_on_item_id'] ) ) {

			$item_ids = $items['checkout_add_on_item_id'];

			foreach ( $item_ids as $item_id ) {

				$item_id = absint( $item_id );

				if ( isset( $items['checkout_add_on_value'][ $item_id ] ) && isset( $items['checkout_add_on_id'][ $item_id ] ) ) {

					$add_on = $this->get_add_on( $items['checkout_add_on_id'][ $item_id ] );

					wc_update_order_item_meta( $item_id, '_wc_checkout_add_on_value', $items['checkout_add_on_value'][ $item_id ] );
					wc_update_order_item_meta( $item_id, '_wc_checkout_add_on_label', $add_on->normalize_value( $items['checkout_add_on_value'][ $item_id ], true ) );
				}
			}
		}
	}


	/**
	 * Process checkout add-on values when order is saved
	 *
	 * @since 1.2.0
	 * @param int $order_id Order ID
	 * @param \WP_Post $post
	 */
	public function process_shop_order_meta( $order_id, $post ) {

		$this->save_order_item_values( $order_id, $_POST );
	}


	/**
	 * Save checkout add-on values
	 *
	 * @since 1.2.0
	 */
	public static function save_order_item_values_ajax() {

		check_ajax_referer( 'save-checkout-add-ons', 'security' );

		if ( isset( $_POST['order_id'], $_POST['items'] ) ) {

			$order_id = absint( $_POST['order_id'] );

			// Parse the jQuery serialized items
			$items = array();
			parse_str( $_POST['items'], $items );

			// Save order items
			wc_checkout_add_ons()->save_order_item_values( $order_id, $items );
		}

		exit;
	}


	/**
	 * Replace some characters lost from select/multiselect/radio value sanitization.
	 *
	 * @since 1.6.1
	 * @param string $title The sanitized value.
	 * @param string $raw_title The raw value.
	 * @param string $context The context for which the title is being sanitized.
	 * @return string $title The sanitized value with special handling.
	 */
	public function sanitize_select_field_values( $title, $raw_title, $context ) {

		if ( 'wc_checkout_add_ons_sanitize' !== $context ) {
			return $title;
		}

		$title = remove_accents( $title );

		// If the value is a negative, add the leading dash back
		if ( is_numeric( $raw_title ) && SV_WC_Helper::str_starts_with( $raw_title, '-' ) ) {
			$title = '-' . $title;
		}

		return $title;
	}


	/** Helper methods ******************************************************/


	/**
	 * Main Checkout Add-Ons Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.4.0
	 * @see wc_checkout_add_ons()
	 * @return WC_Checkout_Add_Ons
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {

			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Get the Admin instance
	 *
	 * @since 1.8.0
	 * @return \WC_Checkout_Add_Ons_Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Get the Front End instance
	 *
	 * @since 1.8.0
	 * @return \WC_Checkout_Add_Ons_Frontend
	 */
	public function get_frontend_instance() {
		return $this->frontend;
	}


	/**
	 * Get the Export Handler instance
	 *
	 * @since 1.8.0
	 * @return \WC_Checkout_Add_Ons_Export_Handler
	 */
	public function get_export_handler() {
		return $this->export_handler;
	}


	/**
	 * Convenience methods for other plugins to easily get add-ons for a given
	 * order
	 *
	 * @since 1.0.0
	 * @param int $order_id WC_Order ID
	 * @return \WC_Checkout_Add_On[] array of WC_Checkout_Add_On objects
	 */
	public function get_order_add_ons( $order_id ) {

		$order = wc_get_order( $order_id );

		$add_ons = get_option( 'wc_checkout_add_ons', array() );

		$order_add_ons = array();

		foreach ( $order->get_items( 'fee' ) as $fee_id => $fee ) {

			// bail for fees that aren't add-ons or deleted add-ons
			if ( empty( $fee['wc_checkout_add_on_id'] ) || ! isset( $add_ons[ $fee['wc_checkout_add_on_id'] ] ) ) {
				continue;
			}

			$add_on = new WC_Checkout_Add_On( $fee['wc_checkout_add_on_id'], $add_ons[ $fee['wc_checkout_add_on_id'] ] );

			$order_add_ons[ $fee['wc_checkout_add_on_id'] ] = array(
				'name'             => $add_on->name,
				'checkout_label'   => $add_on->label,
				'value'            => $fee['wc_checkout_add_on_value'],
				'normalized_value' => maybe_unserialize( $fee['wc_checkout_add_on_label'] ),
				'total'            => $fee['line_total'],
				'total_tax'        => $fee['line_tax'],
				'fee_id'           => $fee_id,
			);
		}

		return $order_add_ons;
	}


	/**
	 * Returns any globally configured add-ons
	 *
	 * @since 1.0.0
	 * @return \WC_Checkout_Add_On[] Array of add-ons objects
	 */
	public function get_add_ons() {

		$add_ons = array();

		$checkout_add_ons = get_option( 'wc_checkout_add_ons', array() );

		foreach ( $checkout_add_ons as $add_on_id => $add_on ) {

			$add_on = new WC_Checkout_Add_On( $add_on_id, $add_on );
			$add_ons[ $add_on_id ] = $add_on;
		}

		return $add_ons;
	}


	/**
	 * Get a single add-on by ID
	 *
	 * @since 1.0.0
	 * @param int $id
	 * @return null|\WC_Checkout_Add_On if found, otherwise null
	 */
	public function get_add_on( $id ) {

		foreach ( $this->get_add_ons() as $add_on ) {

			// do not use ===
			if ( $id == $add_on->id ) {
				return $add_on;
			}
		}

		return null;
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce Checkout Add-Ons', 'ultimatewoo-pro' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


	/**
	 * Gets the URL to the settings page
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::is_plugin_settings()
	 * @param string|null $_ unused
	 * @return string URL to the settings page
	 */
	public function get_settings_url( $_ = null ) {
		return admin_url( 'admin.php?page=wc_checkout_add_ons' );
	}


	/**
	 * Gets the plugin documentation URL
	 *
	 * @since 1.5.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {
		return 'https://docs.woocommerce.com/document/woocommerce-checkout-add-ons/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 1.5.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'https://woocommerce.com/my-account/tickets/';
	}


	/**
	 * Returns true if on the gateway settings page
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::is_plugin_settings()
	 * @return boolean true if on the settings page
	 */
	public function is_plugin_settings() {
		return isset( $_GET['page'] ) && 'wc_checkout_add_ons' === $_GET['page'];
	}


	/**
	 * Determine if WooCommerce Subscriptions is active
	 *
	 * @since 1.7.1
	 * @return bool
	 */
	public function is_subscriptions_active() {

		if ( is_bool( $this->subscriptions_active ) ) {
			return $this->subscriptions_active;
		}

		return $this->subscriptions_active = $this->is_plugin_active( 'woocommerce-subscriptions.php' );
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Install default settings
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::install()
	 */
	protected function install() {

		add_option( 'wc_checkout_add_ons_next_add_on_id', 1 );
	}


	/**
	 * Upgrade to the current version
	 *
	 * @since 1.6.1
	 * @see SV_WC_Plugin::upgrade()
	 * @param string $installed_version
	 */
	public function upgrade( $installed_version ) {

		// upgrade to 1.6.1
		if ( version_compare( $installed_version, '1.6.1', '<' ) ) {

			// add `woocommerce_checkout_` prefix to options
			$add_ons_position = get_option( 'wc_checkout_add_ons_position', 'after_customer_details' );
			update_option( 'wc_checkout_add_ons_position', 'woocommerce_checkout_' . $add_ons_position );
		}
	}



} // end \WC_Checkout_Add_Ons class


/**
 * Returns the One True Instance of Checkout Add-Ons
 *
 * @since 1.4.0
 * @return WC_Checkout_Add_Ons
 */
function wc_checkout_add_ons() {
	return WC_Checkout_Add_Ons::instance();
}


wc_checkout_add_ons();

} // init_woocommerce_checkout_add_ons()

//1.10.4