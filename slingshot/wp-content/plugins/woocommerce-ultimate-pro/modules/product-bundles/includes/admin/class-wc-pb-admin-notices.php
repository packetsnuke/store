<?php
/**
 * WC_PB_Admin_Notices class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    5.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin notices handling.
 *
 * @class    WC_PB_Admin_Notices
 * @version  5.0.0
 */
class WC_PB_Admin_Notices {

	public static $meta_box_notices    = array();
	public static $admin_notices       = array();
	public static $maintenance_notices = array();

	/**
	 * Array of maintenance notice types - name => callback.
	 * @var array
	 */
	private static $maintenance_notice_types = array(
		'updating' => 'updating_notice'
	);


	/**
	 * Constructor.
	 */
	public static function init() {

		self::$maintenance_notices = get_option( 'wc_pb_maintenance_notices', array() );

		// Show meta box notices.
		add_action( 'admin_notices', array( __CLASS__, 'output_notices' ) );
		// Save meta box notices.
		add_action( 'shutdown', array( __CLASS__, 'save_notices' ) );
		// Show maintenance notices.
		add_action( 'admin_print_styles', array( __CLASS__, 'hook_maintenance_notices' ) );
		// Act upon clicking on a 'dismiss notice' link.
		add_action( 'wp_loaded', array( __CLASS__, 'dismiss_notice_handler' ) );
	}

	/**
	 * Add a notice/error.
	 *
	 * @param  string   $text
	 * @param  mixed    $args
	 * @param  boolean  $save_notice
	 */
	public static function add_notice( $text, $args, $save_notice = false ) {

		if ( is_array( $args ) ) {
			$type          = $args[ 'type' ];
			$dismiss_class = isset( $args[ 'dismiss_class' ] ) ? $args[ 'dismiss_class' ] : false;
		} else {
			$type          = $args;
			$dismiss_class = false;
		}

		$notice = array(
			'type'          => $type,
			'content'       => $text,
			'dismiss_class' => $dismiss_class
		);

		if ( $save_notice ) {
			self::$meta_box_notices[] = $notice;
		} else {
			self::$admin_notices[] = $notice;
		}
	}

	/**
	 * Save errors to an option.
	 */
	public static function save_notices() {
		update_option( 'wc_pb_meta_box_notices', self::$meta_box_notices );
		update_option( 'wc_pb_maintenance_notices', self::$maintenance_notices );
	}

	/**
	 * Show any stored error messages.
	 */
	public static function output_notices() {

		$saved_notices = maybe_unserialize( get_option( 'wc_pb_meta_box_notices', array() ) );
		$notices       = $saved_notices + self::$admin_notices;

		if ( ! empty( $notices ) ) {

			foreach ( $notices as $notice ) {

				$dismiss_class = $notice[ 'dismiss_class' ] ? $notice[ 'dismiss_class' ] . ' is-persistent' : 'is-dismissible';

				echo '<div class="wc_pb_notice notice-' . $notice[ 'type' ] . ' notice ' . $dismiss_class . '">';

				if ( $notice[ 'dismiss_class' ] ) {
					$dismiss_url = esc_url( wp_nonce_url( add_query_arg( 'dismiss_wc_pb_notice', $notice[ 'dismiss_class' ] ), 'wc_pb_dismiss_notice_nonce', '_wc_pb_admin_nonce' ) );
					echo '<a class="wc-pb-dismiss-notice notice-dismiss" href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce' ) . '</a>';
				}

				echo '<p>' . wp_kses_post( $notice[ 'content' ] ) . '</p>';
				echo '</div>';
			}

			delete_option( 'wc_pb_meta_box_notices' );
		}
	}

	/**
	 * Show maintenance notices.
	 */
	public static function hook_maintenance_notices() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		foreach ( self::$maintenance_notice_types as $type => $callback ) {
			if ( in_array( $type, self::$maintenance_notices ) ) {
				call_user_func( array( __CLASS__, $callback ) );
			}
		}
	}

	/**
	 * Add a maintenance notice to be displayed.
	 */
	public static function add_maintenance_notice( $notice_name ) {
		self::$maintenance_notices = array_unique( array_merge( self::$maintenance_notices, array( $notice_name ) ) );
	}

	/**
	 * Remove a maintenance notice.
	 */
	public static function remove_maintenance_notice( $notice_name ) {
		self::$maintenance_notices = array_diff( self::$maintenance_notices, array( $notice_name ) );
	}

	/**
	 * Add 'updating' maintenance notice.
	 */
	public static function updating_notice() {

		if ( ! class_exists( 'WC_PB_Install' ) ) {
			return;
		}

		// Show notice to indicate that an update is in progress.
		if ( WC_PB_Install::is_update_pending() ) {

			$fallback = '';
			// Do not check within 5 seconds after starting.
			if ( gmdate( 'U' ) - get_option( 'wc_pb_update_init', 0 ) > 5 ) {
				// Check if the update process is running or not - if not, perhaps it failed to start.
				$fallback_url    = esc_url( wp_nonce_url( add_query_arg( 'force_wc_pb_db_update', true, admin_url() ), 'wc_pb_force_db_update_nonce', '_wc_pb_admin_nonce' ) );
				$fallback_prompt = '<a href="' . $fallback_url . '">' . __( 'run the update process manually', 'ultimatewoo-pro' ) . '</a>';
				$fallback        = '<br/><em>' . sprintf( __( '&hellip;Taking a while? You may need to %s.', 'ultimatewoo-pro' ), $fallback_prompt ) . '</em>';
				$fallback        = WC_PB_Install::is_update_process_running() ? '' : $fallback;
			}
			$notice = '<strong>' . __( 'WooCommerce Product Bundles Data Update', 'ultimatewoo-pro' ) . '</strong> &#8211; ' .  __( 'Your database is being updated in the background.', 'ultimatewoo-pro' ) . $fallback;
			self::add_notice( $notice, 'info' );

		// Show persistent notice to indicate that the updating process is complete.
		} else {
			$notice = __( 'WooCommerce Product Bundles data update complete.', 'ultimatewoo-pro' );
			self::add_notice( $notice, array( 'type' => 'info', 'dismiss_class' => 'updating' ) );
		}
	}

	/**
	 * Act upon clicking on a 'dismiss notice' link.
	 */
	public static function dismiss_notice_handler() {
		if ( isset( $_GET[ 'dismiss_wc_pb_notice' ] ) && isset( $_GET[ '_wc_pb_admin_nonce' ] ) ) {
			if ( ! wp_verify_nonce( $_GET[ '_wc_pb_admin_nonce' ], 'wc_pb_dismiss_notice_nonce' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'woocommerce' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'woocommerce' ) );
			}

			$notice = sanitize_text_field( $_GET[ 'dismiss_wc_pb_notice' ] );
			self::remove_maintenance_notice( $notice );
		}
	}
}

WC_PB_Admin_Notices::init();
