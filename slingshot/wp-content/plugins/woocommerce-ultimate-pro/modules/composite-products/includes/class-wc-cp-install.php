<?php
/**
 * WC_CP_Install class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    3.7.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles installation and updating tasks. Not much to see here, folks!
 *
 * @class    WC_CP_Install
 * @version  3.9.5
 */
class WC_CP_Install {

	/** @var array DB updates and callbacks that need to be run per version */
	private static $db_updates = array(
		'3.7.0' => array(
			'wc_cp_update_370_main',
			'wc_cp_update_370_delete_unused_meta'
		),
		'3.8.0' => array(
			'wc_cp_update_380_main',
			'wc_cp_update_380_delete_unused_meta'
		)
	);

	/** @var object Background update class */
	private static $background_updater;

	/** @var string Plugin version */
	private static $current_version;

	/** @var string Plugin DB version */
	private static $current_db_version;

	/**
	 * Hook in tabs.
	 */
	public static function init() {

		// Installation and DB updates handling.
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 5 );
		add_action( 'init', array( __CLASS__, 'check_updating' ) );
		add_action( 'admin_init', array( __CLASS__, 'check_version' ) );

		// Show row meta on the plugin screen.
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );

		// Adds support for the Composite type - added here instead of 'WC_CP_Meta_Box_Product_Data' as it's used in REST context.
		add_filter( 'product_type_selector', array( __CLASS__, 'add_composite_type' ) );

		// Get plugin and plugin DB versions.
		self::$current_version    = get_option( 'woocommerce_composite_products_version', null );
		self::$current_db_version = get_option( 'woocommerce_composite_products_db_version', null );

		include_once( 'class-wc-cp-background-updater.php' );
	}

	/**
	 * Adds support for the Composite type.
	 *
	 * @param  array  $types
	 * @return array
	 */
	public static function add_composite_type( $types ) {

		$types[ 'composite' ] = __( 'Composite product', 'ultimatewoo-pro' );

		return $types;
	}

	/**
	 * Init background updates.
	 */
	public static function init_background_updater() {
		self::$background_updater = new WC_CP_Background_Updater();
	}

	/**
	 * Check version and run the updater if necessary.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		if ( current_user_can( 'manage_woocommerce' ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			if ( self::$current_version !== WC_CP()->version ) {
				self::install();
			} else {
				if ( ! empty( $_GET[ 'force_wc_cp_db_update' ] ) && wp_verify_nonce( $_GET[ '_wc_cp_admin_nonce' ], 'wc_cp_force_db_update_nonce' ) ) {
					self::force_update();
				}
			}
		}
	}

	/**
	 * If the DB version is out-of-date, a DB update must be in progress: define a 'WC_CP_UPDATING' constant.
	 */
	public static function check_updating() {
		if ( is_null( self::$current_db_version ) || version_compare( self::$current_db_version, max( array_keys( self::$db_updates ) ), '<' ) ) {
			if ( ! defined( 'WC_CP_UPDATING' ) ) {
				define( 'WC_CP_UPDATING', true );
			}
		}
	}

	/**
	 * Install CP.
	 */
	public static function install() {

		// if composite type does not exist, create it.
		if ( false === $composite_term_exists = get_term_by( 'slug', 'composite', 'product_type' ) ) {
			wp_insert_term( 'composite', 'product_type' );
		}

		// Update plugin version - once set, 'check_version()' will not call 'install()' again.
		self::update_version();

		// Plugin data exists - queue upgrade tasks.
		if ( $composite_term_exists && ( is_null( self::$current_db_version ) || version_compare( self::$current_db_version, max( array_keys( self::$db_updates ) ), '<' ) ) ) {
			self::update();
		// Nothing found - this is a new install :)
		} else {
			self::update_db_version();
		}
	}

	/**
	 * Update WC CP version to current.
	 */
	private static function update_version() {
		delete_option( 'woocommerce_composite_products_version' );
		add_option( 'woocommerce_composite_products_version', WC_CP()->version );
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function update() {

		$update_queued = false;

		foreach ( self::$db_updates as $version => $update_callbacks ) {
			if ( version_compare( self::$current_db_version, $version, '<' ) ) {
				WC_CP_Core_Compatibility::log( sprintf( 'Updating to version %s.', $version ), 'info', 'wc_cp_db_updates' );
				foreach ( $update_callbacks as $update_callback ) {
					WC_CP_Core_Compatibility::log( sprintf( '- Queuing %s callback.', $update_callback ), 'info', 'wc_cp_db_updates' );
					self::$background_updater->push_to_queue( $update_callback );
					$update_queued = true;
				}
			}
		}

		if ( $update_queued ) {
			// Define 'WC_CP_UPDATING' constant.
			if ( ! defined( 'WC_CP_UPDATING' ) ) {
				define( 'WC_CP_UPDATING', true );
			}
			// Add option to keep track of time.
			delete_option( 'wc_cp_update_init' );
			add_option( 'wc_cp_update_init', gmdate( 'U' ) );
			// Add 'updating' notice and save early (saving on the 'shutdown' action will fail if a chained request arrives before the 'shutdown' hook fires).
			WC_CP_Admin_Notices::add_maintenance_notice( 'updating' );
			WC_CP_Admin_Notices::save_notices();
			// Dispatch.
			self::$background_updater->save()->dispatch();
		}
	}

	/**
	 * Force re-start the update cron if everything else fails.
	 */
	public static function force_update() {
		/**
		 * 'wp_wc_cp_updater_cron' action.
		 */
		do_action( 'wp_wc_cp_updater_cron' );
		wp_safe_redirect( admin_url() );
	}

	/**
	 * Updates plugin DB version when all updates have been processed.
	 */
	public static function update_complete() {

		WC_CP_Core_Compatibility::log( 'Data update complete.', 'info', 'wc_cp_db_updates' );
		self::update_db_version();
		delete_option( 'wc_cp_update_init' );
		wp_cache_flush();
	}

	/**
	 * True if an update is in progress.
	 *
	 * @return boolean
	 */
	public static function is_update_pending() {
		return defined( 'WC_CP_UPDATING' );
	}

	/**
	 * True if an update is in progress.
	 *
	 * @return boolean
	 */
	public static function is_update_in_progress() {
		return self::$background_updater->is_updating();
	}

	/**
	 * True if an update process is running.
	 *
	 * @return boolean
	 */
	public static function is_update_process_running() {
		return self::$background_updater->is_process_running();
	}

	/**
	 * Update DB version to current.
	 *
	 * @param  string  $version
	 */
	private static function update_db_version( $version = null ) {

		$version = is_null( $version ) ? WC_CP()->version : $version;

		delete_option( 'woocommerce_composite_products_db_version' );
		add_option( 'woocommerce_composite_products_db_version', $version );

		WC_CP_Core_Compatibility::log( sprintf( 'Database version is %s.', get_option( 'woocommerce_composite_products_db_version', 'unknown' ) ), 'info', 'wc_cp_db_updates' );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param	mixed  $links
	 * @param	mixed  $file
	 * @return	array
	 */
	public static function plugin_row_meta( $links, $file ) {

		if ( $file == WC_CP()->plugin_basename() ) {
			$row_meta = array(
				'docs'    => '<a href="https://docs.woocommerce.com/document/composite-products/">' . __( 'Documentation', 'ultimatewoo-pro' ) . '</a>',
				'support' => '<a href="https://woocommerce.com/my-account/tickets/">' . __( 'Support', 'ultimatewoo-pro' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return $links;
	}
}

WC_CP_Install::init();
