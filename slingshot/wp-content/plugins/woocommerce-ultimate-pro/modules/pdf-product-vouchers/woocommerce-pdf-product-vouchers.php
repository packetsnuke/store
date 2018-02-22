<?php
/**
 * @package   WC-PDF-Product-Vouchers
 * @author    SkyVerge
 * @category  Plugin
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */

defined( 'ABSPATH' ) or exit;

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library classss
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once SV_WC_FRAMEWORK_FILE;
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.6.4', __( 'WooCommerce PDF Product Vouchers', 'ultimatewoo-pro' ), __FILE__, 'init_woocommerce_pdf_product_vouchers', array(
	'minimum_wc_version'   => '2.6.0',
	'minimum_wp_version'   => '4.4',
	'backwards_compatible' => '4.4',
) );

function init_woocommerce_pdf_product_vouchers() {

/**
 * WooCommerce PDF Product Vouchers Main Plugin Class
 *
 * @since 1.0
 */
class WC_PDF_Product_Vouchers extends SV_WC_Plugin {


	/** version number */
	const VERSION = '3.1.5';

	/** @var WC_PDF_Product_Vouchers single instance of this plugin */
	protected static $instance;

	/** string the plugin id */
	const PLUGIN_ID = 'pdf_product_vouchers';

	/** string plugin text domain, DEPRECATED in 2.5.0 */
	const TEXT_DOMAIN = 'woocommerce-pdf-product-vouchers';

	/** Voucher image thumbnail width */
	const VOUCHER_IMAGE_THUMB_WIDTH = 100;

	/** @var WC_PDF_Product_Vouchers_AJAX ajax class */
	private $ajax;

	/** @var WC_PDF_Product_Vouchers_Product product class */
	private $product;

	/** @var WC_PDF_Product_Vouchers_Cart cart class */
	private $cart;

	/** @var WC_PDF_Product_Vouchers_Handler voucher handler/helper */
	private $voucher_handler;

	/** @var WC_PDF_Product_Vouchers_Frontend My Account handler/helper */
	private $frontend;

	/** @var WC_PDF_Product_Vouchers_Admin PDF product vouchers admin */
	private $admin;

	/** @var WC_PDF_Product_Vouchers_Admin_Customizer admin customizer handler */
	private $customizer;

	/** @var \WC_PDF_Product_Vouchers_Query instance */
	protected $query;

	/** @var \WC_PDF_Product_Vouchers_Cron instance */
	protected $cron;

	/** @var \WC_PDF_Product_Vouchers_Download_Handler instance */
	protected $download_handler;


	/**
	 * Setup main plugin class
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::__construct()
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'text_domain'  => 'woocommerce-pdf-product-vouchers',
				'dependencies' => array( 'dom', 'gd', 'mbstring' ), // https://github.com/dompdf/dompdf/wiki/Requirements
			)
		);

		// make sure 5.3+ code is loaded only if PHP version is at least 5.3.0,
		// so that we can render an admin notice to 5.2 users and not fatal
		if ( version_compare( phpversion(), '5.3.0', '>=' ) ) {

			// include required files
			add_action( 'sv_wc_framework_plugins_loaded', array( $this, 'includes' ) );

			add_action( 'init', array( $this, 'init' ), 25 );

			// generate voucher pdf, attach to emails, handle downloads
			add_filter( 'woocommerce_email_classes', array( $this, 'add_email_classes' ) );

			// make sure template files are searched for in our plugin
			add_filter( 'woocommerce_locate_template',      array( $this, 'locate_template' ), 20, 3 );
			add_filter( 'woocommerce_locate_core_template', array( $this, 'locate_template' ), 20, 3 );
		}
	}


	/**
	 * Files required by both the admin and frontend
	 *
	 * @since 1.0.0
	 */
	public function includes() {

		// load post types
		require_once( $this->get_plugin_path() . '/includes/class-wc-pdf-product-vouchers-post-types.php' );

		// load helper functions
		require_once( $this->get_plugin_path() . '/includes/functions/wc-pdf-product-vouchers-functions.php' );

		if ( is_admin() ) {
			$this->admin_includes();
		}

		require_once( $this->get_plugin_path() . '/includes/abstract-wc-voucher-base.php' );
		require_once( $this->get_plugin_path() . '/includes/class-wc-voucher.php' );
		require_once( $this->get_plugin_path() . '/includes/class-wc-voucher-template.php' );

		$this->product          = $this->load_class( '/includes/class-wc-pdf-product-vouchers-product.php', 'WC_PDF_Product_Vouchers_Product' );
		$this->cart             = $this->load_class( '/includes/class-wc-pdf-product-vouchers-cart.php', 'WC_PDF_Product_Vouchers_Cart' );
		$this->voucher_handler  = $this->load_class( '/includes/class-wc-pdf-product-vouchers-handler.php', 'WC_PDF_Product_Vouchers_Handler' );
		$this->query            = $this->load_class( '/includes/class-wc-pdf-product-vouchers-query.php','WC_PDF_Product_Vouchers_Query' );
		$this->cron             = $this->load_class( '/includes/class-wc-pdf-product-vouchers-cron.php','WC_PDF_Product_Vouchers_Cron' );
		$this->download_handler = $this->load_class( '/includes/class-wc-pdf-product-vouchers-download-handler.php', 'WC_PDF_Product_Vouchers_Download_Handler' );
		$this->frontend         = $this->load_class( '/includes/frontend/class-wc-pdf-product-vouchers-frontend.php', 'WC_PDF_Product_Vouchers_Frontend' );
		$this->customizer       = $this->load_class( '/includes/customizer/class-wc-pdf-product-vouchers-customizer.php', 'WC_PDF_Product_Vouchers_Customizer' );

		require_once( $this->get_plugin_path() . '/includes/class-wc-pdf-product-vouchers-order.php' );

		// AJAX includes
		if ( is_ajax() ) {
			$this->ajax_includes();
		}
	}


	/**
	 * Include required voucher admin files
	 *
	 * @since 1.0
	 */
	private function admin_includes() {

		require_once( $this->get_plugin_path() . '/includes/admin/class-wc-pdf-product-vouchers-admin.php' );
		$this->admin = new WC_PDF_Product_Vouchers_Admin( $this );
	}


	/**
	 * Include required AJAX classes
	 *
	 * @since 3.0.0
	 */
	private function ajax_includes() {
		$this->ajax = $this->load_class( '/includes/class-wc-pdf-product-vouchers-ajax.php', 'WC_PDF_Product_Vouchers_AJAX' );
	}


	/**
	 * Initialize
	 *
	 * @since 3.0.0
	 */
	public function init() {

		WC_PDF_Product_Vouchers_Post_Types::initialize();

		// super-simplistic test response for checking whether loopback connections are enabled on server
		if ( ! empty( $_REQUEST['wc_pdf_product_vouchers_test_voucher_generation'] ) ) {
			echo '[TEST_VOUCHER_CONTENT]';
			exit;
		}
	}


	/**
	 * Return product class instance
	 *
	 * @since 2.6.0
	 * @return \WC_PDF_Product_Vouchers_Product
	 */
	public function get_product_instance() {
		return $this->product;
	}


	/**
	 * Return cart class instance
	 *
	 * @since 2.6.0
	 * @return \WC_PDF_Product_Vouchers_Cart
	 */
	public function get_cart_instance() {
		return $this->cart;
	}


	/**
	 * Return my account class instance
	 *
	 * @since 2.6.0
	 * @return \WC_PDF_Product_Vouchers_My_Account
	 */
	public function get_my_account_instance() {
		return $this->my_account;
	}


	/**
	 * Return voucher handler class instance
	 *
	 * @since 2.6.0
	 * @return \WC_PDF_Product_Vouchers_Voucher
	 */
	public function get_voucher_handler_instance() {
		return $this->voucher_handler;
	}


	/**
	 * Returns the customizer class instance
	 *
	 * @since 3.0.0
	 * @return \WC_PDF_Product_Vouchers_Customizer
	 */
	public function get_customizer_instance() {
		return $this->customizer;
	}


	/**
	 * Returns the query class instance
	 *
	 * @since 3.0.0
	 * @return \WC_PDF_Product_Vouchers_Query
	 */
	public function get_query_instance() {
		return $this->query;
	}


	/**
	 * Returns the cron class instance
	 *
	 * @since 3.0.0
	 * @return \WC_PDF_Product_Vouchers_Cron
	 */
	public function get_cron_instance() {
		return $this->cron;
	}


	/**
	 * Return admin class instance
	 *
	 * @since 2.6.0
	 * @return \WC_PDF_Product_Vouchers_Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Return deprecated/removed hooks
	 *
	 * @since 3.0.0
	 * @see SV_WC_Plugin::get_deprecated_hooks()
	 * @return array
	 */
	protected function get_deprecated_hooks() {

		// hooks removed in 3.0.0
		$deprecated = array(
			'woocommerce_process_wc_voucher_meta' => array(
				'version' => '3.0.0',
				'removed' => true,
			),
			'wc_pdf_product_vouchers_product_name_multi_line' => array(
				'version' => '3.0.0',
				'removed' => true,
			),
			'woocommerce_voucher_number' => array(
				'version'     => '3.0.0',
				'removed'     => true,
				'map'         => true,
				'replacement' => 'wc_pdf_product_vouchers_get_voucher_number',
			),
			'wc_pdf_product_vouchers_get_expiry' => array(
				'version'     => '3.0.0',
				'removed'     => true,
				'map'         => true,
				'replacement' => 'wc_pdf_product_vouchers_get_expiry_days',
			),
			'wc_pdf_product_vouchers_voucher_field_value' => array(
				'version'     => '3.0.0',
				'removed'     => true,
				'replacement' => 'wc_pdf_product_vouchers_get_{$field}',
			),
		);

		return $deprecated;
	}


	/**
	 * Adds PDF Product Vouchers email class
	 *
	 * @since 1.2.0
	 */
	public function add_email_classes( $email_classes ) {

		$email_classes['WC_PDF_Product_Vouchers_Email_Voucher_Recipient'] = $this->load_class( '/includes/emails/class-wc-pdf-product-vouchers-email-voucher-recipient.php', 'WC_PDF_Product_Vouchers_Email_Voucher_Recipient' );

		return $email_classes;
	}


	/**
	 * Locates the WooCommerce template files from our templates directory
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 * @param string $template Already found template
	 * @param string $template_name Searchable template name
	 * @param string $template_path Template path
	 * @return string Search result for the template
	 */
	public function locate_template( $template, $template_name, $template_path ) {

		// only keep looking if no custom theme template was found
		// or if a default WooCommerce template was found
		if ( ! $template || SV_WC_Helper::str_starts_with( $template, WC()->plugin_path() ) ) {

			// set the path to our templates directory
			$plugin_path = $this->get_plugin_path() . '/templates/';

			// if a template is found, make it so
			if ( is_readable( $plugin_path . $template_name ) ) {
				$template = $plugin_path . $template_name;
			}
		}

		return $template;
	}


	/** Admin methods ******************************************************/


	/**
	 * Gets the plugin configuration URL
	 *
	 * @since 1.1.0
	 * @see SV_WC_Plugin::get_settings_url()
	 * @param string $plugin_id the plugin identifier.  Note that this can be a
	 *        sub-identifier for plugins with multiple parallel settings pages
	 *        (ie a gateway that supports both credit cards and echecks)
	 * @return string plugin settings URL
	 */
	public function get_settings_url( $plugin_id = null ) {

		// only return the URL on 5.3+, as otherwise the settigns page will not exist
		if ( version_compare( phpversion(), '5.3.0', '>=' ) ) {

			// link to the wc_voucher list table
			return admin_url( 'edit.php?post_type=wc_voucher' );
		}
	}


	/**
	 * Returns true if on the Vouchers List Table/Edit screens
	 *
	 * @since 1.1.0
	 * @see SV_WC_Plugin::is_plugin_settings()
	 * @return boolean true if on the admin gateway settings page
	 */
	public function is_plugin_settings() {
		return isset( $_GET['post_type'] ) && 'wc_voucher' == $_GET['post_type'];
	}


	/**
	 * Checks if required PHP extensions are loaded and adds an admin notice
	 * for any missing extensions.  Also plugin settings can be checked
	 * as well.
	 *
	 * @since 2.1.1
	 * @see SV_WC_Plugin::add_admin_notices()
	 */
	public function add_admin_notices() {

		parent::add_admin_notices();

		$this->add_file_permissions_notices();

		$this->add_php_version_notice();

		$screen = get_current_screen();

		// only render on plugins or settings screen
		if ( 'plugins' === $screen->id || $this->is_plugin_settings() ) {

			if ( 'yes' === get_option( 'wc_pdf_product_vouchers_upgraded_to_3_0_0' ) ) {

				// display a notice for installations that are upgrading
				$message_id  = 'wc_pdf_product_vouchers_upgrade_install';

				/* translators: Placeholders: %1$s - this plugin name, %2$s - opening HTML <a> anchor tag, %3$s - closing HTML </a> tag */
				$message_content = sprintf( __( 'Hi there! It looks like you have upgraded %1$s from an older version. We have added lots of new features, please %2$scheck out the documentation%3$s for an overview!', 'ultimatewoo-pro' ), $this->get_plugin_name(), '<a target="_blank" href="' . $this->get_documentation_url() . '">', '</a>' );

			} else {

				// Display a notice for fresh installs
				$message_id = 'wc_pdf_product_vouchers_fresh_install';

				/* translators: Placeholders: %1$s - the plugin name, %2$s - opening HTML <a> anchor tag, %3$s closing HTML </a> tag */
				$message_content = sprintf( __( 'Thanks for installing %1$s! To get started, please take a minute to %2$sread the documentation%3$s :)', 'ultimatewoo-pro' ), $this->get_plugin_name(), '<a href="' . $this->get_documentation_url()  . '" target="_blank">', '</a>' );

			}

			// Add notice
			$this->get_admin_notice_handler()->add_admin_notice( $message_content, $message_id, array(
				'always_show_on_settings' => false,
				'notice_class'            => 'updated',
			) );

		}
	}


	/**
	 * Render an admin error if there's a directory permission that will prevent
	 * voucher files from being written
	 *
	 * @since 2.1.1
	 */
	private function add_file_permissions_notices() {

		/* translators: Placeholders: %1$s - plugin name, %2$s - uploads path, %3$s - <code> tag or empty string, %4$s - </code> tag or empty string */
		$message    = __( '%1$s: non-writable path %3$s%2$s%4$s detected, please fix directory permissions or voucher files may not be able to be generated.', 'ultimatewoo-pro' );
		$message_id = null;
		$upload_dir = wp_upload_dir();

		// check for file permission errors
		if ( ! is_writable( $upload_dir['basedir'] ) ) {
			$message = sprintf( $message, $this->get_plugin_name(), $upload_dir['basedir'], '', '' );
			$message_id = 'bad-perms-1';
		} elseif ( ! is_writable( self::get_woocommerce_uploads_path() ) ) {
			$message = sprintf( $message, $this->get_plugin_name(), self::get_woocommerce_uploads_path(), '<code>', '</code>' );
			$message_id = 'bad-perms-2';
		} elseif ( file_exists( self::get_uploads_path() ) && ! is_writable( self::get_uploads_path() ) ) {
			$message = sprintf( $message, $this->get_plugin_name(), self::get_uploads_path(), '', '' );
			$message_id = 'bad-perms-3';
		}

		if ( $message_id ) {
			$this->get_admin_notice_handler()->add_admin_notice( $message, $message_id );
		}
	}


	/**
	 * Render an admin error if PHP version is < 5.3
	 *
	 * @since 3.0.0
	 */
	private function add_php_version_notice() {

		if ( version_compare( phpversion(), '5.3.0', '<' ) ) {

			$message = sprintf(
				/* translators: Placeholders: %1$s - plugin name, %2$s - the required PHP version */
				__( '%1$s requires PHP version at least %2$s to function. Your server is currently using version %3$s. Contact your host or server administrator to upgrade PHP.', 'ultimatewoo-pro' ),
				$this->get_plugin_name(),
				'<strong>5.3.0</strong>',
				phpversion()
			);

			$this->get_admin_notice_handler()->add_admin_notice( $message, 'php-version', array(
				'notice_class' => 'error',
			) );
		}
	}


	/** Helper methods ******************************************************/


	/**
	 * Main PDF Product Vouchers Instance, ensures only one instance is/can be loaded
	 *
	 * @since 2.2.0
	 * @see wc_pdf_product_vouchers()
	 * @return WC_PDF_Product_Vouchers
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce PDF Product Vouchers', 'ultimatewoo-pro' );
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
	 * Returns the uploads path, which is used to store the generated PDF
	 * product voucher files
	 *
	 * @since 1.0.0
	 * @return string upload path for this plugin
	 */
	public static function get_uploads_path() {
		return self::get_woocommerce_uploads_path() . '/woocommerce_pdf_product_vouchers';
	}


	/**
	 * Returns the voucher helper/handler class
	 *
	 * @TODO Remove this as part of WC 3.1 compat {BR 2017-03-21}, originally {TZ 2016-05-26}
	 *
	 * @deprecated since 2.6.0
	 *
	 * @since 1.2.0
	 * @return \WC_PDF_Product_Vouchers_Voucher voucher helper/handler class
	 */
	public function get_voucher_handler() {

		/* @deprecated since 2.6.0 */
		_deprecated_function( 'wc_pdf_product_vouchers()->get_voucher_handler()', '2.6.0', 'wc_pdf_product_vouchers()->get_voucher_handler_instance()' );

		return $this->get_voucher_handler_instance();
	}


	/**
	 * Returns the admin message handler instance
	 *
	 * TODO: remove this when the method gets fixed in framework {IT 2017-01-12}
	 *
	 * @since 3.0.0
	 */
	public function get_message_handler() {

		require_once( $this->get_framework_path() . '/class-sv-wp-admin-message-handler.php' );

		return parent::get_message_handler();
	}


	/**
	 * Gets the plugin documentation url
	 *
	 * @since 2.4.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string documentation URL
	 */
	public function get_documentation_url() {
		return 'https://docs.woocommerce.com/document/woocommerce-pdf-product-vouchers/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 2.4.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'https://woocommerce.com/my-account/marketplace-ticket-form/';
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Plugin install method
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::install()
	 */
	protected function install() {

		require_once( $this->get_plugin_path() . '/includes/class-wc-pdf-product-vouchers-upgrade.php' );
		WC_PDF_Product_Vouchers_Upgrade::run_install_scripts();

		// make custom endpoints available
		flush_rewrite_rules();
	}


	/**
	 * Plugin upgrade method
	 *
	 * @since 2.0.4
	 * @see SV_WC_Plugin::upgrade()
	 * @param string $installed version the currently installed version we are upgrading from
	 */
	protected function upgrade( $installed_version ) {

		require_once( $this->get_plugin_path() . '/includes/class-wc-pdf-product-vouchers-upgrade.php' );
		WC_PDF_Product_Vouchers_Upgrade::run_update_scripts( $installed_version );

		// make custom endpoints available
		flush_rewrite_rules();
	}
}


/**
 * Returns the One True Instance of PDF Product Vouchers
 *
 * @since 2.2.0
 * @return WC_PDF_Product_Vouchers
 */
function wc_pdf_product_vouchers() {
	return WC_PDF_Product_Vouchers::instance();
}


// fire it up!
wc_pdf_product_vouchers();

} // init_woocommerce_pdf_product_vouchers()

//3.1.5