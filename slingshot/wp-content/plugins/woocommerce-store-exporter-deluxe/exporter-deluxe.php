<?php
/*
Plugin Name: WooCommerce - Store Exporter Deluxe
Plugin URI: http://www.visser.com.au/woocommerce/plugins/exporter-deluxe/
Description: Unlocks business focused e-commerce features within Store Exporter for WooCommerce. This Pro ugprade will de-activate the basic Store Exporter Plugin on activation.
Version: 2.5.4
Author: Visser Labs
Author URI: http://www.visser.com.au/about/
License: GPL2

Text Domain: woocommerce-exporter
Domain Path: /languages/

WC requires at least: 2.3
WC tested up to: 3.3
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'WOO_CD_DIRNAME', basename( dirname( __FILE__ ) ) );
define( 'WOO_CD_RELPATH', basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );
define( 'WOO_CD_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_CD_PREFIX', 'woo_ce' );
define( 'WOO_CD_PLUGINPATH', WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) ) );

if( !function_exists( 'is_plugin_active' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

// Check if multiple instances of SED are installed and activated
if( is_plugin_active( WOO_CD_DIRNAME . '/exporter-deluxe.php' ) && function_exists( 'woo_cd_i18n' ) ) {

	function woo_ce_admin_duplicate_plugin() {

		ob_start(); ?>
<div class="error">
	<p><?php printf( __( 'Please de-activate any other instances of <em>WooCommerce - Store Exporter Deluxe</em> before re-activating this Plugin.', 'woocommerce-exporter' ) ); ?></p>
</div>
<?php
		ob_end_flush();

		deactivate_plugins( str_replace( '\\', '/', dirname( __FILE__ ) ) . '/exporter-deluxe.php' );

	}
	add_action( 'admin_notices', 'woo_ce_admin_duplicate_plugin' );

} else {

	// Disable basic Store Exporter if it is activated
	include_once( WOO_CD_PATH . 'common/common.php' );
	if( defined( 'WOO_CE_PREFIX' ) == true ) {
		// Detect Store Exporter and other platform versions
		include_once( WOO_CD_PATH . 'includes/install.php' );
		woo_cd_detect_ce();
	} else {
		include_once( WOO_CD_PATH . 'includes/functions.php' );
		if( defined( 'WP_CLI' ) && WP_CLI )
			include_once( WOO_CD_PATH . 'includes/wp-cli.php' );
	}

	// Plugin language support
	function woo_cd_i18n() {

		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-exporter' );
		load_textdomain( 'woocommerce-exporter', WP_LANG_DIR . '/woocommerce-exporter/woocommerce-exporter-' . $locale . '.mo' );
		load_plugin_textdomain( 'woocommerce-exporter', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	}
	add_action( 'init', 'woo_cd_i18n', 11 );

	if( is_admin() ) {

		/* Start of: WordPress Administration */

		// Register our install script for first time install
		include_once( WOO_CD_PATH . 'includes/install.php' );
		register_activation_hook( __FILE__, 'woo_cd_install' );
		register_deactivation_hook( __FILE__, 'woo_cd_uninstall' );

		// Initial scripts and export process
		function woo_cd_admin_init() {

			global $export, $wp_roles;

			$action = ( function_exists( 'woo_get_action' ) ? woo_get_action() : false );

			$troubleshooting_url = 'https://www.visser.com.au/documentation/store-exporter-deluxe/troubleshooting/';

			// An effort to reduce the memory load at export time
			if( $action <> 'export' ) {

				// Check the User has the activate_plugins capability
				$user_capability = 'activate_plugins';
				if( current_user_can( $user_capability ) ) {

					// Time to tell the store owner if we were unable to disable the basic Store Exporter
					if( defined( 'WOO_CE_PREFIX' ) ) {
						// Display notice if we were unable to de-activate basic Store Exporter
						if( ( is_plugin_active( 'woocommerce-exporter/exporter.php' ) || is_plugin_active( 'woocommerce-store-exporter/exporter.php' ) ) ) {
							$plugins_url = esc_url( add_query_arg( '', '', 'plugins.php' ) );
							$message = sprintf( __( 'We did our best to de-activate Store Exporter for you but may have failed, please check that the basic Store Exporter is de-activated from the <a href="%s">Plugins screen</a>.', 'woocommerce-exporter' ), $plugins_url );
							woo_cd_admin_notice( $message, 'error', array( 'plugins.php', 'update-core.php' ) );
						}
					}

					// Detect if another e-Commerce platform is activated
					if( !woo_is_woo_activated() && ( woo_is_jigo_activated() || woo_is_wpsc_activated() ) ) {
						$message = __( 'We have detected another e-Commerce Plugin than WooCommerce activated, please check that you are using Store Exporter Deluxe for the correct platform.', 'woocommerce-exporter' );
						$message .= sprintf( ' <a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
						woo_cd_admin_notice( $message, 'error', 'plugins.php' );
					} else if( !woo_is_woo_activated() ) {
						$message = __( 'We have been unable to detect the WooCommerce Plugin activated on this WordPress site, please check that you are using Store Exporter Deluxe for the correct platform.', 'woocommerce-exporter' );
						$message .= sprintf( ' <a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
						woo_cd_admin_notice( $message, 'error', 'plugins.php' );
					}

					// Detect if any known conflict Plugins are activated

					// WooCommerce Subscriptions Exporter - http://codecanyon.net/item/woocommerce-subscription-exporter/6569668
					if( function_exists( 'wc_subs_exporter_admin_init' ) ) {
						$message = __( 'We have detected an activated Plugin for WooCommerce that is known to conflict with Store Exporter Deluxe, please de-activate WooCommerce Subscriptions Exporter to resolve export issues within Store Exporter Deluxe.', 'woocommerce-exporter' );
						$message .= sprintf( '<a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
						woo_cd_admin_notice( $message, 'error', array( 'plugins.php', 'admin.php' ) );
					}

					// WP Easy Events Professional - https://emdplugins.com/plugins/wp-easy-events-professional/
					if( class_exists( 'WP_Easy_Events_Professional' ) ) {
						$message = __( 'We have detected an activated Plugin that is known to conflict with Store Exporter Deluxe, please de-activate WP Easy Events Professional to resolve export issues within Store Exporter Deluxe.', 'woocommerce-exporter' );
						$message .= sprintf( '<a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
						woo_cd_admin_notice( $message, 'error', array( 'plugins.php', 'admin.php' ) );
					}

					// Plugin row notices for the Plugins screen
					add_action( 'after_plugin_row_' . WOO_CD_RELPATH, 'woo_ce_admin_plugin_row' );

				}

				// Load Dashboard widget for Scheduled Exports
				add_action( 'wp_dashboard_setup', 'woo_ce_admin_dashboard_setup' );

				// Check the User has the view_woocommerce_reports capability
				$user_capability = apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' );
				if( current_user_can( $user_capability ) == false )
					return;

				// Migrate scheduled export to CPT
				if( woo_ce_get_option( 'auto_format', false ) !== false ) {
					if( woo_ce_legacy_scheduled_export() ) {
						$message = __( 'We have detected Scheduled Exports from an earlier release of Store Exporter Deluxe, they have been updated it to work with the new multiple scheduled export engine in Store Exporter Deluxe. Please open WooCommerce &raquo; Store Export &raquo; Settings &raquo; Scheduled Exports to see what\'s available.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message );
					}
				}

				// Add an Export Status column to the Orders screen
				add_filter( 'manage_edit-shop_order_columns', 'woo_ce_admin_order_column_headers', 20 );
				add_action( 'manage_shop_order_posts_custom_column', 'woo_ce_admin_order_column_content' );

				// Load Download button styling to the Orders screen
				wp_enqueue_style( 'dashicons' );
				wp_enqueue_style( 'woo_ce_styles', plugins_url( '/templates/admin/export.css', WOO_CD_RELPATH ) );

				// Add our export to CSV, XML, XLS, XLSX action buttons to the Orders screen
				add_filter( 'woocommerce_admin_order_actions', 'woo_ce_admin_order_actions', 10, 2 );
				add_action( 'wp_ajax_woo_ce_export_order', 'woo_ce_ajax_export_order' );
				add_action( 'wp_ajax_woo_ce_export_load_export_template', 'woo_ce_ajax_load_export_template' );
				add_action( 'wp_ajax_woo_ce_export_override_scheduled_export', 'woo_ce_ajax_override_scheduled_export' );

				// Add Download as... bulk export options on the Orders and Products screen
				add_action( 'admin_footer', 'woo_ce_admin_export_bulk_actions' );
				add_action( 'load-edit.php', 'woo_ce_admin_export_process_bulk_action' );

				// Add Download as... action options to the Edit Orders screen
				add_action( 'woocommerce_order_actions', 'woo_ce_admin_order_single_actions' );
				add_action( 'woocommerce_order_action_woo_ce_export_order_csv', 'woo_ce_admin_order_single_export_csv' );
				add_action( 'woocommerce_order_action_woo_ce_export_order_tsv', 'woo_ce_admin_order_single_export_tsv' );
				add_action( 'woocommerce_order_action_woo_ce_export_order_xls', 'woo_ce_admin_order_single_export_xls' );
				add_action( 'woocommerce_order_action_woo_ce_export_order_xlsx', 'woo_ce_admin_order_single_export_xlsx' );
				add_action( 'woocommerce_order_action_woo_ce_export_order_xml', 'woo_ce_admin_order_single_export_xml' );
				add_action( 'woocommerce_order_action_woo_ce_export_order_unflag', 'woo_ce_admin_order_single_export_unflag' );

				// Add Download as... action options to the Bookings screen
				if( woo_ce_detect_export_plugin( 'woocommerce_bookings' ) ) {
					woo_ce_load_export_types( 'booking' );
					add_filter( 'woocommerce_admin_booking_actions', 'woo_ce_extend_woocommerce_admin_booking_actions', 10, 2 );
				}

				// Check that we are on the Store Exporter screen
				$page = ( isset($_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : false );
				if( $page != strtolower( WOO_CD_PREFIX ) )
					return;

				// Add memory usage to the screen footer of the WooCommerce > Store Export screen
				add_filter( 'admin_footer_text', 'woo_ce_admin_footer_text' );

				woo_ce_export_init();

			}

			// Process any pre-export notice confirmations
			switch( $action ) {

				// This is where the magic happens
				case 'export':

					// Make sure we play nice with other WooCommerce and WordPress exporters
					if( !isset( $_POST['woo_ce_export'] ) )
						return;

					check_admin_referer( 'manual_export', 'woo_ce_export' );

					// Hide error logging during the export process
					if( function_exists( 'ini_set' ) )
						@ini_set( 'display_errors', 0 );

					$time_limit = false;
					if( function_exists( 'ini_get' ) )
						$time_limit = ini_get( 'max_execution_time' );

					// @mod - Add in detection of PHP form size limit and notify store owner if we go over it; currently returns the Quick Export screen with no notification, review in 2.4+
					if( WOO_CD_LOGGING ) {

						woo_ce_error_log( sprintf( 'Debug: %s', '---' ) );

						$total = false;
						if( function_exists( 'ini_get' ) )
							$total = ini_get( 'max_input_vars' );

						$size = count( $_POST );
						if( !empty( $total ) )
							woo_ce_error_log( sprintf( 'Debug: %s', sprintf( '$_POST elements: %d used of %d available', $size, $total ) ) );
						else
							woo_ce_error_log( sprintf( 'Debug: %s', '$_POST elements: ' . $size ) );

						$size = count( $_GET );
						if( !empty( $total ) )
							woo_ce_error_log( sprintf( 'Debug: %s', sprintf( '$_GET elements: %d used of %d available', $size, $total ) ) );
						else
							woo_ce_error_log( sprintf( 'Debug: %s', '$_GET elements: ' . $size ) );

						if( !empty( $time_limit ) )
							woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'max_execution_time: %d seconds', $time_limit ) ) );
						else
							woo_ce_error_log( sprintf( 'Debug: %s', 'max_execution_time: ' . __( 'Unlimited', 'woocommerce-exporter' ) ) );

					}

					// Welcome in the age of GZIP compression and Object caching
					if( !defined( 'DONOTCACHEPAGE' ) )
						define( 'DONOTCACHEPAGE', true );
					if( !defined( 'DONOTCACHCEOBJECT' ) )
						define( 'DONOTCACHCEOBJECT', true );

					// Set artificially high because PHPExcel builds its export in memory
					if( function_exists( 'wp_raise_memory_limit' ) ) {
						add_filter( 'export_memory_limit', 'woo_ce_raise_export_memory_limit' );
						wp_raise_memory_limit( 'export' );
					}

					$timeout = woo_ce_get_option( 'timeout', 0 );
					$safe_mode = ( function_exists( 'safe_mode' ) ? ini_get( 'safe_mode' ) : false );
					if( !$safe_mode ) {
						// Double up, why not.
						if( function_exists( 'set_time_limit' ) )
							@set_time_limit( $timeout );
						if( function_exists( 'ini_set' ) )
							@ini_set( 'max_execution_time', $timeout );
					}
					if( function_exists( 'ini_set' ) )
						@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );

					// Set up the basic export options
					$export = new stdClass();
					$export->cron = 0;
					$export->scheduled_export = 0;
					$export->start_time = time();
					$export->time_limit = ( isset( $time_limit ) ? $time_limit : 0 );
					if( WOO_CD_LOGGING ) {
						woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - begin export generation: ' . ( time() - $export->start_time ) ) );
					}
					$export->idle_memory_start = woo_ce_current_memory_usage();
					$export->encoding = woo_ce_get_option( 'encoding', get_option( 'blog_charset', 'UTF-8' ) );
					// Reset the Encoding if corrupted
					if( $export->encoding == '' || $export->encoding == false || $export->encoding == 'System default' ) {
						$message = __( 'Encoding export option was corrupted, defaulted to UTF-8', 'woocommerce-exporter' );
						woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
						$export->encoding = 'UTF-8';
						woo_ce_update_option( 'encoding', 'UTF-8' );
					}

					$export->delimiter = woo_ce_get_option( 'delimiter', ',' );
					// Reset the Delimiter if corrupted
					if( $export->delimiter == '' || $export->delimiter == false ) {
						$message = __( 'Delimiter export option was corrupted, defaulted to ,', 'woocommerce-exporter' );
						woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
						$export->delimiter = ',';
						woo_ce_update_option( 'delimiter', ',' );
					} else if( $export->delimiter == 'TAB' ) {
						$export->delimiter = "\t";
					}
					$export->category_separator = woo_ce_get_option( 'category_separator', '|' );
					// Reset the Category Separator if corrupted
					if( $export->category_separator == '' || $export->category_separator == false ) {
						$message = __( 'Category Separator export option was corrupted, defaulted to |', 'woocommerce-exporter' );
						woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
						$export->category_separator = '|';
						woo_ce_update_option( 'category_separator', '|' );
					}
					// Override for line break (LF) support in Category Separator
					if( $export->category_separator == 'LF' )
						$export->category_separator = "\n";
					$export->bom = woo_ce_get_option( 'bom', 1 );
					$export->escape_formatting = woo_ce_get_option( 'escape_formatting', 'all' );
					// Reset the Escape Formatting if corrupted
					if( $export->escape_formatting == '' || $export->escape_formatting == false ) {
						$message = __( 'Escape Formatting export option was corrupted, defaulted to all.', 'woocommerce-exporter' );
						woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
						$export->escape_formatting = 'all';
						woo_ce_update_option( 'escape_formatting', 'all' );
					}
					$export->header_formatting = woo_ce_get_option( 'header_formatting', 1 );
					$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );
					// Reset the Date Format if corrupted
					if( $date_format == '1' || $date_format == '' || $date_format == false ) {
						$message = __( 'Date Format export option was corrupted, defaulted to d/m/Y', 'woocommerce-exporter' );
						woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
						$date_format = 'd/m/Y';
						woo_ce_update_option( 'date_format', $date_format );
					}

					// Save export option changes made on the Export screen
					$export->limit_volume = ( isset( $_POST['limit_volume'] ) ? sanitize_text_field( $_POST['limit_volume'] ) : '' );
					woo_ce_update_option( 'limit_volume', $export->limit_volume );
					if( in_array( $export->limit_volume, array( '', '0', '-1' ) ) ) {
						woo_ce_update_option( 'limit_volume', '' );
						$export->limit_volume = -1;
					}
					$export->offset = ( isset( $_POST['offset'] ) ? sanitize_text_field( $_POST['offset'] ) : '' );
					woo_ce_update_option( 'offset', $export->offset );
					if( in_array( $export->offset, array( '', '0' ) ) ) {
						woo_ce_update_option( 'offset', '' );
						$export->offset = 0;
					}
					$export->type = ( isset( $_POST['dataset'] ) ? sanitize_text_field( $_POST['dataset'] ) : false );
					if( in_array( $export->type, array( 'product', 'category', 'tag', 'brand', 'order' ) ) ) {
						$export->description_excerpt_formatting = ( isset( $_POST['description_excerpt_formatting'] ) ? absint( $_POST['description_excerpt_formatting'] ) : false );
						if( $export->description_excerpt_formatting <> woo_ce_get_option( 'description_excerpt_formatting' ) )
							woo_ce_update_option( 'description_excerpt_formatting', $export->description_excerpt_formatting );
					}
					if( isset( $_POST['export_format'] ) )
						woo_ce_update_option( 'export_format', sanitize_text_field( $_POST['export_format'] ) );

					// Set default values for all export options to be later passed onto the export process
					$export->fields = array();
					$export->fields_order = false;
					$export->export_format = woo_ce_get_option( 'export_format', 'csv' );
		
					// Export field sorting
					if( !empty( $export->type ) ) {
						$export->fields = ( isset( $_POST[$export->type . '_fields'] ) ? array_map( 'sanitize_text_field', $_POST[$export->type . '_fields'] ) : false );
						$export->fields_order = ( isset( $_POST[$export->type . '_fields_order'] ) ? array_map( 'absint', $_POST[$export->type . '_fields_order'] ) : false );
						woo_ce_update_option( 'last_export', $export->type );
					}
					$export = apply_filters( 'woo_ce_setup_dataset_options', $export, $export->type );
					if( empty( $export->type ) ) {
						$message = __( 'No export type was selected, please try again with an export type selected.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message, 'error' );
						return;
					}

					woo_ce_load_export_types();

					$export->args = array(
						'limit_volume' => $export->limit_volume,
						'offset' => $export->offset,
						'encoding' => $export->encoding,
						'date_format' => $date_format
					);
					$export->args = apply_filters( 'woo_ce_extend_dataset_args', $export->args, $export->type );

					if( empty( $export->fields ) ) {
						if( function_exists( sprintf( 'woo_ce_get_%s_fields', $export->type ) ) ) {
							$export->fields = call_user_func_array( 'woo_ce_get_' . $export->type . '_fields', array( 'summary' ) );
							$message = __( 'No export fields were selected, defaulted to include all fields for this export type.', 'woocommerce-exporter' );
							woo_cd_admin_notice( $message, 'updated' );
						} else {
							$message = __( 'No export fields were selected and we could not default to all fields, please try again with at least a single export field.', 'woocommerce-exporter' );
							woo_cd_admin_notice( $message, 'error' );
							return;
						}
					}
					woo_ce_save_fields( $export->type, $export->fields, $export->fields_order );
					unset( $export->fields_order );

					$export->filename = woo_ce_generate_filename( $export->type );

					$export->idle_memory_end = woo_ce_current_memory_usage();
					$export->end_time = time();

					// Let's spin up PHPExcel for supported export types and formats
					if( in_array( $export->export_format, apply_filters( 'woo_ce_phpexcel_supported_export_formats', array( 'csv', 'tsv', 'xls', 'xlsx' ) ) ) ) {

						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - before woo_ce_export_dataset(): ' . ( time() - $export->start_time ) ) );
						$dataset = woo_ce_export_dataset( $export->type );
						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - after woo_ce_export_dataset(): ' . ( time() - $export->start_time ) ) );

						// Check if we have data to export
						if( empty( $dataset ) ) {
							$message = __( 'No export entries were found, please try again with different export filters.', 'woocommerce-exporter' );
							if( $export->offset )
								$message .= ' ' . __( 'Try clearing the value set for the Volume Offset under Export Options.', 'woocommerce-exporter' );
							woo_cd_admin_notice( $message, 'error' );
							// Reset the count Transient for this export type in case it is out of date
							delete_transient( WOO_CD_PREFIX . '_' . $export->type . '_count' );
							return;
						}

						// Load up the fatal error notice if we 500, timeout or encounter a fatal PHP error
						add_action( 'shutdown', 'woo_ce_fatal_error' );

						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - before loading PHPExcel: ' . ( time() - $export->start_time ) ) );

						// Check that PHPExcel is where we think it is
						if( file_exists( WOO_CD_PATH . 'classes/PHPExcel.php' ) ) {
							// Check if PHPExcel has already been loaded
							if( !class_exists( 'PHPExcel' ) ) {
								include_once( WOO_CD_PATH . 'classes/PHPExcel.php' );
							} else {
								// Let's try to locate the filepath of the already registered PHPExcel Class
								if( class_exists( 'ReflectionClass' ) ) {
									$reflector = new ReflectionClass( 'PHPExcel' );
									$message = sprintf( __( 'The required PHPExcel library was already loaded by another WordPress Plugin located at %s. If there\'s issues with your export file contact the Plugin author of the mentioned Plugin.', 'woocommerce-exporter' ), $reflector->getFileName() );
									$message .= sprintf( '<a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
									woo_cd_admin_notice( $message, 'error' );
									unset( $reflector );
								// Nope, we couldn't detect the filepath so display default notice
								} else {
									$message = __( 'The required PHPExcel library was already loaded by another WordPress Plugin, unfortunately however we cannot automatically detect which Plugin. If there\'s issues with your export file you now know where to start looking.', 'woocommerce-exporter' );
									$message .= sprintf( '<a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
									woo_cd_admin_notice( $message, 'error' );
								}
							}
						} else {
							$message = sprintf( __( 'We couldn\'t load the PHPExcel library <code>%s</code> within <code>%s</code>, this file should be present. <a href="%s" target="_blank">Need help?</a>', 'woocommerce-exporter' ), 'PHPExcel.php', WOO_CD_PATH . 'classes/...', $troubleshooting_url );
							woo_cd_admin_notice( $message, 'error' );
							return;
						}

						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - after loading PHPExcel: ' . ( time() - $export->start_time ) ) );

						// Cache control
						do_action( 'woo_ce_export_phpexcel_caching_methods' );

						// Final check incase something is blocking PHPExcel
						if( !class_exists( 'PHPExcel' ) ) {
							$message = sprintf( __( 'We couldn\'t load the PHPExcel library <code>%s</code> within <code>%s</code> even after trying workarounds, this file should be present. <a href="%s" target="_blank">Need help?</a>', 'woocommerce-exporter' ), 'PHPExcel.php', WOO_CD_PATH . 'classes/...', $troubleshooting_url );
							woo_cd_admin_notice( $message, 'error' );
							return;
						}

						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - before building PHPExcel export contents: ' . ( time() - $export->start_time ) ) );

						$excel = new PHPExcel();
						$excel->setActiveSheetIndex( 0 );
						$excel->getActiveSheet()->setTitle( ucfirst( $export->type ) );

						// Check if we are forcing use of an alternate temp directory
						if( apply_filters( 'woo_ce_phpexcel_force_temp_dir', false ) )
							PHPExcel_Shared_File::setUseUploadTempDirectory( true );

						$row = 1;
						// Skip headers if Heading Formatting is turned off
						if( $export->header_formatting ) {
							$col = 0;
							foreach( $export->columns as $column ) {
								$excel->getActiveSheet()->setCellValueByColumnAndRow( $col, $row, woo_ce_wp_specialchars_decode( $column ) );
								$excel->getActiveSheet()->getCellByColumnAndRow( $col, $row )->getStyle()->getFont()->setBold( true );
								$excel->getActiveSheet()->getColumnDimensionByColumn( $col )->setAutoSize( true );
								$col++;
							}
							$row = 2;
						}
						$col = 0;
						// Start iterating through the export data
						foreach( $dataset as $data ) {
							$col = 0;
							foreach( array_keys( $export->fields ) as $field ) {

								// Embed Image paths as thumbnails exclusively within the XLSX export type
								if( $export->export_format == 'xlsx' && in_array( $field, woo_ce_get_image_embed_allowed_fields() ) ) {
									if( !empty( $data->$field ) ) {

										// Check that the Image path has been filled
										if( $data->$field == false ) {
											$col++;
											continue;
										}

										// Check if PHPExcel_Worksheet_Drawing is present
										if( class_exists( 'PHPExcel_Worksheet_Drawing' ) ) {

											// Check for the Category separator character
											$image_paths = explode( $export->category_separator, $data->$field );
											if( !empty( $image_paths ) ) {
												$i = 0;
												foreach( $image_paths as $image_path ) {

													// Check the image path exists
													if( file_exists( $image_path ) == false )
														continue;

													$objDrawing = new PHPExcel_Worksheet_Drawing();
													$objDrawing->setName( '' );
													$objDrawing->setDescription( '' );
													$objDrawing->setPath( $image_path );
													$objDrawing->setCoordinates( PHPExcel_Cell::stringFromColumnIndex( $col ) . $row );
													$shop_thumbnail = apply_filters( 'woo_ce_override_embed_shop_thumbnail', false, $export->type );
													if( $shop_thumbnail == false ) {
														// Override for the image embed thumbnail size; use registered WordPress image size names
														$thumbnail_size = apply_filters( 'woo_ce_override_embed_thumbnail_size', 'shop_thumbnail', $export->type );
														$shop_thumbnail = ( function_exists( 'wc_get_image_size' ) ? wc_get_image_size( $thumbnail_size ) : array( 'height' => 100 ) );
													}
													$objDrawing->setHeight( ( isset( $shop_thumbnail['height'] ) ? absint( $shop_thumbnail['height'] ) : 100 ) );
													$objDrawing->setWorksheet( $excel->getActiveSheet() );
													$excel->getActiveSheet()->getRowDimension( $row )->setRowHeight( ( isset( $shop_thumbnail['height'] ) ? $shop_thumbnail['height'] : 100 ) );
													// Adjust the offset for multiple Images
													if( !empty( $i ) )
														$objDrawing->setOffsetX( ( isset( $shop_thumbnail['height'] ) ? $shop_thumbnail['height'] : 100 ) * $i );
													unset( $objDrawing );
													$i++;

												}
											}

										} else {
											$message = __( 'We couldn\'t load the PHPExcel_Worksheet_Drawing class attached to PHPExcel, the PHPExcel_Worksheet_Drawing class is required for embedding images within XLSX exports.', 'woocommerce-exporter' );
											$message .= sprintf( '<a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
											woo_cd_admin_notice( $message, 'error' );
											return;
										}

										$col++;
										continue;

									}
								}

								$excel->getActiveSheet()->getCellByColumnAndRow( $col, $row )->getStyle()->getFont()->setBold( false );

								if( $export->encoding == 'UTF-8' ) {
									if( woo_ce_detect_value_string( ( isset( $data->$field ) ? $data->$field : null ) ) ) {
										// Treat this cell as a string
										$excel->getActiveSheet()->getCellByColumnAndRow( $col, $row )->setValueExplicit( ( isset( $data->$field ) ? woo_ce_wp_specialchars_decode( $data->$field ) : '' ), PHPExcel_Cell_DataType::TYPE_STRING );
									} else {
										// Detect the cell type or default to PHPExcel
										$type = woo_ce_detect_value_string( ( isset( $data->$field ) ? $data->$field : null ), 'type' );
										if( !is_null( $type ) )
											$excel->getActiveSheet()->getCellByColumnAndRow( $col, $row )->setValueExplicit( ( isset( $data->$field ) ? woo_ce_wp_specialchars_decode( $data->$field, null, $type ) : '' ), $type );
										else
											$excel->getActiveSheet()->getCellByColumnAndRow( $col, $row )->setValue( ( isset( $data->$field ) ? woo_ce_wp_specialchars_decode( $data->$field ) : '' ) );
									}
								} else {
									// PHPExcel only deals with UTF-8 regardless of encoding type
									if( woo_ce_detect_value_string( ( isset( $data->$field ) ? $data->$field : null ) ) ) {
										// Treat this cell as a string
										$excel->getActiveSheet()->getCellByColumnAndRow( $col, $row )->setValueExplicit( ( isset( $data->$field ) ? utf8_encode( woo_ce_wp_specialchars_decode( $data->$field ) ) : '' ), PHPExcel_Cell_DataType::TYPE_STRING );
									} else {
										// Detect the cell type or default to PHPExcel
										$type = woo_ce_detect_value_string( ( isset( $data->$field ) ? $data->$field : null ), 'type' );
										if( !is_null( $type ) )
											$excel->getActiveSheet()->getCellByColumnAndRow( $col, $row )->setValueExplicit( ( isset( $data->$field ) ? utf8_encode( woo_ce_wp_specialchars_decode( $data->$field, null, $type ) ) : '' ), $type );
										else
											$excel->getActiveSheet()->getCellByColumnAndRow( $col, $row )->setValue( ( isset( $data->$field ) ? utf8_encode( woo_ce_wp_specialchars_decode( $data->$field ) ) : '' ) );
										unset( $type );
									}
								}

								$col++;

							}

							// Allow Plugin/Theme authors to add in blank rows
							$excel = apply_filters( 'woo_ce_phpexcel_sheet', $excel, $row, $export->type, $export->export_format );

							// Allow Plugin/Theme authors to control the row counter
							$row = apply_filters( 'woo_ce_phpexcel_row', $row, $export->type, $export->export_format );
							$row++;

						}

						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - after building PHPExcel export contents: ' . ( time() - $export->start_time ) ) );

						// Override the export format to CSV if debug mode is enabled
						if( WOO_CD_DEBUG )
							$export->export_format = 'csv';

						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - before building PHPExcel export file: ' . ( time() - $export->start_time ) ) );

						// Load our custom Writer for the CSV and TSV file types
						if( in_array( $export->export_format, apply_filters( 'woo_ce_phpexcel_csv_writer_export_formats', array( 'csv', 'tsv' ) ) ) ) {
							include_once( WOO_CD_PATH . 'includes/export-csv.php' );
							// We need to load this after the PHPExcel Class has been created
							woo_cd_load_phpexcel_sed_csv_writer();
						} else if( in_array( $export->export_format, array( 'xlsx', 'xls' ) ) ) {
							// Use this switch to toggle the legacy PCLZip Class instead of ZipArchive Class within PHPExcel
							if( apply_filters( 'woo_ce_export_phpexcel_ziparchive_legacy', false, $export->export_format ) )
								PHPExcel_Settings::setZipClass( PHPExcel_Settings::PCLZIP );
						}

						// Set the file extension and MIME type
						switch( $export->export_format ) {

							// Defaults to CSV
							default:
							case 'csv':

								// Allow Plugin/Theme authors to add support for additional export formats

								// Only load SED_CSV PHPExcel Writer if it has been loaded
								if( class_exists( 'PHPExcel_Writer_SED_CSV' ) )
									$php_excel_format = apply_filters( 'woo_ce_phpexcel_export_format_writer', 'SED_CSV', $export->export_format );
								else
									$php_excel_format = apply_filters( 'woo_ce_phpexcel_export_format_writer', 'CSV', $export->export_format );
								$file_extension = apply_filters( 'woo_ce_phpexcel_export_format_file_extension', 'csv', $export->export_format );
								$post_mime_type = apply_filters( 'woo_ce_phpexcel_export_format_mime_type', 'text/csv', $export->export_format );
								break;

							case 'tsv':
								$php_excel_format = 'SED_CSV';
								$file_extension = 'tsv';
								$post_mime_type = 'text/tab-separated-values';
								break;

							case 'xls':
								$php_excel_format = 'Excel5';
								$file_extension = 'xls';
								$post_mime_type = 'application/vnd.ms-excel';
								break;

							case 'xlsx':
								$php_excel_format = 'Excel2007';
								$file_extension = 'xlsx';
								$post_mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
								break;

						}

						// Tack on the file extension
						$export->filename = $export->filename . '.' . $file_extension;

						// Send the export to the factory
						$objWriter = PHPExcel_IOFactory::createWriter( $excel, $php_excel_format );

						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - after building PHPExcel export file: ' . ( time() - $export->start_time ) ) );

						// Only write headers if we're not in debug mode
						if( !WOO_CD_DEBUG ) {

							// Print to browser

							// Check if we are printing file headers
							if( apply_filters( 'woo_ce_export_print_to_browser', true ) )
								woo_ce_generate_file_headers( $post_mime_type );

							switch( $export->export_format ) {

								case 'csv':
									if( $export->bom )
										$objWriter->setUseBOM( true );
									// Check if we're using a non-standard delimiter
									if( $export->delimiter != ',' )
										$objWriter->setDelimiter( $export->delimiter );
									break;

								case 'tsv':
									if( $export->bom )
										$objWriter->setUseBOM( true );
									$objWriter->setDelimiter( "\t" );
									break;

								case 'xlsx':
									$objWriter->setPreCalculateFormulas( false );
									break;

								default:
									// Allow Plugin/Theme authors to add support for additional export formats
									$objWriter = apply_filters( 'woo_ce_phpexcel_export_format_writer_options', $objWriter, $export->export_format );
									break;

							}
							// Print directly to browser, do not save to the WordPress Media
							if( woo_ce_get_option( 'delete_file', 1 ) ) {

								// The end memory usage and time is collected at the very last opportunity prior to the file header being rendered to the screen
								delete_option( WOO_CD_PREFIX . '_exported' );
								$objWriter->save( 'php://output' );

							} else {

								if( WOO_CD_LOGGING )
									woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - before saving export file to Archives: ' . ( time() - $export->start_time ) ) );

								// Save to file and insert to WordPress Media
								$temp_filename = tempnam( apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ), 'tmp' );
								// Check if we were given a temporary filename
								if( $temp_filename == false ) {

									$message = sprintf( __( 'We could not create a temporary export file in <code>%s</code>, ensure that WordPress can read and write files to this directory and try again.', 'woocommerce-exporter' ), apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ) );
									woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $message ) );
									woo_cd_admin_notice( $message, 'error' );
									$url = add_query_arg( array( 'failed' => true, 'message' => urlencode( $message ) ) );
									wp_redirect( $url );
									exit();

								} else {
									$objWriter->save( $temp_filename );
									$bits = file_get_contents( $temp_filename );
								}
								unlink( $temp_filename );

								$post_ID = woo_ce_save_file_attachment( $export->filename, $post_mime_type );
								$upload = wp_upload_bits( $export->filename, null, $bits );
								// Check if the upload succeeded otherwise delete Post and return error notice
								if( ( $post_ID == false ) || $upload['error'] ) {
									wp_delete_attachment( $post_ID, true );
									if( isset( $upload['error'] ) ) {
										woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $upload['error'] ) );
										$url = add_query_arg( array( 'failed' => true, 'message' => urlencode( $upload['error'] ) ) );
										wp_redirect( $url );
									} else {
										$url = add_query_arg( array( 'failed' => true ) );
										wp_redirect( $url );
									}
									return;
								}

								if( WOO_CD_LOGGING )
									woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - after saving export file to Archives: ' . ( time() - $export->start_time ) ) );

								if( WOO_CD_LOGGING )
									woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - before updating archive file details: ' . ( time() - $export->start_time ) ) );

								// Load the WordPress Media API resources
								if( file_exists( ABSPATH . 'wp-admin/includes/image.php' ) ) {
									$attach_data = wp_generate_attachment_metadata( $post_ID, $upload['file'] );
									wp_update_attachment_metadata( $post_ID, $attach_data );
									update_attached_file( $post_ID, $upload['file'] );
									if( !empty( $post_ID ) ) {
										woo_ce_save_file_guid( $post_ID, $export->type, $upload['url'] );
										woo_ce_save_file_details( $post_ID );
									}
								} else {
									$message = __( 'Could not load image.php within /wp-admin/includes/image.php', 'woocommerce-exporter' );
									woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $message ) );
								}

								// The end memory usage and time is collected at the very last opportunity prior to the file header being rendered to the screen
								woo_ce_update_file_detail( $post_ID, '_woo_idle_memory_end', woo_ce_current_memory_usage() );
								woo_ce_update_file_detail( $post_ID, '_woo_end_time', time() );

								if( WOO_CD_LOGGING )
									woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - after updating archive file details: ' . ( time() - $export->start_time ) ) );

								delete_option( WOO_CD_PREFIX . '_exported' );

								// Check if we are returning the export file to the browser
								if( apply_filters( 'woo_ce_export_print_to_browser', true ) ) {
									$objWriter->save( 'php://output' );
								} else {
									$message = __( 'The quick export completed successfully, open the Archives tab to download your export file.', 'woocommerce-exporter' );
									woo_cd_admin_notice( $message );
									$url = add_query_arg( array( 'success' => true ) );
									wp_redirect( $url );
								}

							}

							if( WOO_CD_LOGGING )
								woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - end export generation: ' . ( time() - $export->start_time ) ) );

							// Clean up PHPExcel
							$excel->disconnectWorksheets();
							unset( $objWriter, $excel );
							exit();

						} else {

							// Save to temporary file then dump into export log screen
							if( $export->bom )
								$objWriter->setUseBOM( true );
							$temp_filename = tempnam( apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ), 'tmp' );
							// Check if we were given a temporary filename
							if( $temp_filename == false ) {
								$message = sprintf( __( 'We could not create a temporary export file in <code>%s</code>, ensure that WordPress can read and write files to this directory and try again.', 'woocommerce-exporter' ), apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ) );
								woo_cd_admin_notice( $message, 'error' );
							} else {
								$objWriter->save( $temp_filename );
								$bits = file_get_contents( $temp_filename );
							}
							unlink( $temp_filename );

							if( WOO_CD_LOGGING )
								woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - end export generation: ' . ( time() - $export->start_time ) ) );

							// Clean up PHPExcel
							$excel->disconnectWorksheets();
							unset( $objWriter, $excel );

							// Save the export contents to the WordPress Transient, base64 encode it to get around Transient storage formatting issues 
							$response = set_transient( WOO_CD_PREFIX . '_debug_log', base64_encode( $bits ), woo_ce_get_option( 'timeout', ( MINUTE_IN_SECONDS * 10 ) ) );
							if( $response !== true ) {
								$message = __( 'The export contents were too large to store in a single WordPress transient, use the Volume offset / Limit volume options to reduce the size of your export and try again.', 'woocommerce-exporter' );
								$message .= sprintf( ' (<a href="%s" target="_blank">%s</a>)', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
								woo_cd_admin_notice( $message, 'error' );
							}

						}

						// Remove our fatal error notice to play nice with other Plugins
						remove_action( 'shutdown', 'woo_ce_fatal_error' );

					// Run the default engine for the XML and RSS export formats
					} else if( in_array( $export->export_format, apply_filters( 'woo_ce_simplexml_supported_export_formats', array( 'xml', 'rss' ) ) ) ) {

						include_once( WOO_CD_PATH . 'includes/export-xml.php' );

						// Check if SimpleXMLElement is present
						if( !class_exists( 'SED_SimpleXMLElement' ) ) {
							$message = __( 'We couldn\'t load the SimpleXMLElement class, the SimpleXMLElement class is required for XML and RSS feed generation.', 'woocommerce-exporter' );
							$message .= sprintf( '<a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
							woo_cd_admin_notice( $message, 'error' );
							return;
						}

						// Set the file extension and MIME type
						switch( $export->export_format ) {

							// Defaults to XML
							default:
							case 'xml':
								// Allow Plugin/Theme authors to add support for additional export formats
								$file_extension = apply_filters( 'woo_ce_simplexml_export_format_file_extension', 'xml', $export->export_format );
								$post_mime_type = apply_filters( 'woo_ce_simplexml_export_format_mime_type', 'application/xml', $export->export_format );
								break;

							case 'rss':
								$file_extension = 'xml';
								$post_mime_type = 'application/rss+xml';
								break;

						}

						// Tack on the file extension
						$export->filename = $export->filename . '.' . $file_extension;

						if( in_array( $export->export_format, apply_filters( 'woo_ce_simplexml_xml_export_format', array( 'xml' ) ) ) ) {
							$xml = new SED_SimpleXMLElement( sprintf( apply_filters( 'woo_ce_export_xml_first_line', '<?xml version="1.0" encoding="%s"?><%s/>' ), esc_attr( $export->encoding ), esc_attr( apply_filters( 'woo_ce_export_xml_store_node', 'store' ) ) ) );
							if( woo_ce_get_option( 'xml_attribute_url', 1 ) )
								$xml->addAttribute( 'url', get_site_url() );
							if( woo_ce_get_option( 'xml_attribute_date', 1 ) )
								$xml->addAttribute( 'date', date( 'Y-m-d', current_time( 'timestamp' ) ) );
							if( woo_ce_get_option( 'xml_attribute_time', 0 ) )
								$xml->addAttribute( 'time', date( 'H:i:s', current_time( 'timestamp' ) ) );
							if( woo_ce_get_option( 'xml_attribute_title', 1 ) )
								$xml->addAttribute( 'name', htmlspecialchars( get_bloginfo( 'name' ) ) );
							if( woo_ce_get_option( 'xml_attribute_export', 1 ) )
								$xml->addAttribute( 'export', htmlspecialchars( $export->type ) );
							if( woo_ce_get_option( 'xml_attribute_orderby', 1 ) && isset( $export->{$export->type . '_orderby'} ) )
								$xml->addAttribute( 'orderby', $export->{$export->type . '_orderby'} );
							if( woo_ce_get_option( 'xml_attribute_order', 1 ) && isset( $export->{$export->type . '_order'} ) )
								$xml->addAttribute( 'order', $export->{$export->type . '_order'} );
							if( woo_ce_get_option( 'xml_attribute_limit', 1 ) )
								$xml->addAttribute( 'limit', $export->limit_volume );
							if( woo_ce_get_option( 'xml_attribute_offset', 1 ) )
								$xml->addAttribute( 'offset', $export->offset );
							$xml = apply_filters( 'woo_ce_export_xml_before_dataset', $xml );
							$bits = woo_ce_export_dataset( $export->type, $xml );
							$bits = apply_filters( 'woo_ce_export_xml_after_dataset', $bits );
						} else if( $export->export_format == 'rss' ) {
							$xml = new SED_SimpleXMLElement( sprintf( apply_filters( 'woo_ce_export_rss_first_line', '<?xml version="1.0" encoding="%s"?><rss version="2.0"%s/>' ), esc_attr( $export->encoding ), ' xmlns:g="http://base.google.com/ns/1.0"' ) );
							$child = $xml->addChild( apply_filters( 'woo_ce_export_rss_channel_node', 'channel' ) );
							$child->addChild( 'title', woo_ce_get_option( 'rss_title', '' ) );
							$child->addChild( 'link', woo_ce_get_option( 'rss_link', '' ) );
							$child->addChild( 'description', woo_ce_get_option( 'rss_description', '' ) );
							$xml = apply_filters( 'woo_ce_export_rss_before_dataset', $xml );
							$bits = woo_ce_export_dataset( $export->type, $child );
							$bits = apply_filters( 'woo_ce_export_rss_after_dataset', $bits );
						}

						// Check if we have data to export
						if( empty( $bits ) ) {
							$message = __( 'No export entries were found, please try again with different export filters.', 'woocommerce-exporter' );
							woo_cd_admin_notice( $message, 'error' );
							return;
						}

						if( !WOO_CD_DEBUG ) {
							// Print directly to browser, do not save to the WordPress Media
							if( woo_ce_get_option( 'delete_file', 1 ) ) {

								// Print directly to browser
								woo_ce_generate_file_headers( $post_mime_type );
								if( $bits = woo_ce_format_xml( $bits ) )
									echo $bits;

								if( WOO_CD_LOGGING )
									woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - end export generation: ' . ( time() - $export->start_time ) ) );

								exit();

							} else {

								// Save to file and insert to WordPress Media
								if( $export->filename && $bits ) {
									$post_ID = woo_ce_save_file_attachment( $export->filename, $post_mime_type );
									$bits = woo_ce_format_xml( $bits );
									$upload = wp_upload_bits( $export->filename, null, $bits );
									// Check for issues saving to WordPress Media
									if( ( $post_ID == false ) || !empty( $upload['error'] ) ) {
										wp_delete_attachment( $post_ID, true );
										if( isset( $upload['error'] ) ) {
											woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $upload['error'] ) );
											$url = add_query_arg( array( 'failed' => true, 'message' => urlencode( $upload['error'] ) ) );
											wp_redirect( $url );
										} else {
											$url = add_query_arg( array( 'failed' => true ) );
											wp_redirect( $url );
										}
										return;
									}
									$attach_data = wp_generate_attachment_metadata( $post_ID, $upload['file'] );
									wp_update_attachment_metadata( $post_ID, $attach_data );
									update_attached_file( $post_ID, $upload['file'] );
									if( $post_ID ) {
										woo_ce_save_file_guid( $post_ID, $export->type, $upload['url'] );
										woo_ce_save_file_details( $post_ID );
									}
									$export_type = $export->type;
									unset( $export );

									// The end memory usage and time is collected at the very last opportunity prior to the XML header being rendered to the screen
									woo_ce_update_file_detail( $post_ID, '_woo_idle_memory_end', woo_ce_current_memory_usage() );
									woo_ce_update_file_detail( $post_ID, '_woo_end_time', time() );
									delete_option( WOO_CD_PREFIX . '_exported' );

									// Generate XML header

									// Check if we are returning the export file to the browser
									if( apply_filters( 'woo_ce_export_print_to_browser', true ) )
										woo_ce_generate_file_headers( $post_mime_type );
									unset( $export_type );

									// Print file contents to screen
									if( !empty( $upload['file'] ) ) {

										// Check if we are returning the export file to the browser
										if( apply_filters( 'woo_ce_export_print_to_browser', true ) ) {

											// Check if readfile() is disabled on this host
											$disabled = explode( ',', ini_get( 'disable_functions' ) );
											if( !in_array( 'readfile', $disabled ) ) {
												readfile( $upload['file'] );
											} else {
												// Workaround for disabled readfile on some hosts
												$fp = fopen( $upload['file'], 'rb' );
												fpassthru( $fp );
												fclose( $fp );
												unset( $fp );
											}
											unset( $disabled );

										} else {
											$message = __( 'The quick export completed successfully, open the Archives tab to download your export file.', 'woocommerce-exporter' );
											woo_cd_admin_notice( $message );
										}

										if( WOO_CD_LOGGING )
											woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - end export generation: ' . ( time() - $export->start_time ) ) );

									} else {
										$url = add_query_arg( 'failed', true );
										wp_redirect( $url );
									}
									unset( $upload );
								} else {
									$url = add_query_arg( 'failed', true );
									wp_redirect( $url );
								}

								// Check if we are returning the export file to the browser
								if( apply_filters( 'woo_ce_export_print_to_browser', true ) )
									exit();

							}
						}

					} else {

						if( apply_filters( 'woo_ce_custom_supported_export_formats', false, $export->export_format ) == false ) {
							$message = sprintf( __( 'The export format - %s - is not assocated with a recognised file generator', 'woocommerce-exporter' ), $export->export_format );
							$url = add_query_arg( array( 'failed' => true, 'message' => urlencode( $message ) ) );
							wp_redirect( $url );
						} else {
							do_action( 'woo_ce_custom_supported_export', $export, $export->export_format );

							if( WOO_CD_LOGGING )
								woo_ce_error_log( sprintf( 'Debug: %s', 'exporter-deluxe.php - end export generation: ' . ( time() - $export->start_time ) ) );

						}
						exit();

					}
					break;

				// Save changes on Settings screen
				case 'save-settings':
					// We need to verify the nonce.
					if( !empty( $_POST ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'woo_ce_save_settings' ) ) {
						if( check_admin_referer( 'woo_ce_save_settings' ) )
							woo_ce_export_settings_save();
					}
					break;

				// Save changes on Field Editor screen
				case 'save-fields':
					// We need to verify the nonce.
					if( !empty( $_POST ) && check_admin_referer( 'save_fields', 'woo_ce_save_fields' ) ) {
						$fields = ( isset( $_POST['fields'] ) ? array_filter( $_POST['fields'] ) : array() );
						$fields = array_map( 'stripslashes', $fields );
						$hidden = ( isset( $_POST['hidden'] ) ? array_filter( $_POST['hidden'] ) : array() );
						$export_type = ( isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '' );
						$export_types = array_keys( woo_ce_get_export_types() );
						// Check we are saving against a valid export type
						if( in_array( $export_type, $export_types ) ) {
							woo_ce_update_option( $export_type . '_labels', $fields );
							woo_ce_update_option( $export_type . '_hidden', $hidden );
							$message = __( 'Field labels have been saved.', 'woocommerce-exporter' );
							woo_cd_admin_notice( $message );
						} else {
							$message = __( 'Changes could not be saved as we could not detect a valid export type. Raise this as a Support issue and include what export type you were editing.', 'woocommerce-exporter' );
							woo_cd_admin_notice( $message, 'error' );
						}
					}
					break;

			}

		}
		add_action( 'admin_init', 'woo_cd_admin_init', 11 );

		// HTML templates and form processor for Store Exporter Deluxe screen
		function woo_cd_html_page() {

			// Check the User has the view_woocommerce_reports capability
			$user_capability = apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' );
			if( current_user_can( $user_capability ) == false )
				return;

			global $wpdb, $export;

			$title = apply_filters( 'woo_ce_template_header', __( 'Store Exporter Deluxe', 'woocommerce-exporter' ) );
			woo_cd_template_header( $title );
			$action = ( function_exists( 'woo_get_action' ) ? woo_get_action() : false );
			switch( $action ) {

				case 'export':
					if( WOO_CD_DEBUG ) {
						if( false === ( $export_log = get_transient( WOO_CD_PREFIX . '_debug_log' ) ) ) {
							$export_log = __( 'No export entries were found within the debug Transient, please try again with different export filters.', 'woocommerce-exporter' );
						} else {
							// We take the contents of our WordPress Transient and de-base64 it back to CSV format
							$export_log = base64_decode( $export_log );
						}
						delete_transient( WOO_CD_PREFIX . '_debug_log' );
						$output = '
	<h3>' . sprintf( __( 'Export Details: %s', 'woocommerce-exporter' ), esc_attr( $export->filename ) ) . '</h3>
	<p>' . __( 'This prints the $export global that contains the different export options and filters to help reproduce this on another instance of WordPress. Very useful for debugging blank or unexpected exports.', 'woocommerce-exporter' ) . '</p>
	<textarea id="export_log">' . esc_textarea( print_r( $export, true ) ) . '</textarea>
	<hr />';
						if( in_array( $export->export_format, array( 'csv', 'tsv', 'xls' ) ) ) {
							$output .= '
	<script type="text/javascript">
		$j(function() {
			$j(\'#export_sheet\').CSVToTable(\'\', {
				startLine: 0';
							if( in_array( $export->export_format, array( 'tsv', 'xls', 'xlsx' ) ) ) {
								$output .= ',
				separator: "\t"';
							}
							$output .= '
			});
		});
	</script>
	<h3>' . __( 'Export', 'woocommerce-exporter' ) . '</h3>
	<p>' . __( 'We use the <a href="http://code.google.com/p/jquerycsvtotable/" target="_blank"><em>CSV to Table plugin</em></a> to see first hand formatting errors or unexpected values within the export file.', 'woocommerce-exporter' ) . '</p>
	<div id="export_sheet">' . esc_textarea( $export_log ) . '</div>
	<p class="description">' . __( 'This jQuery plugin can fail with <code>\'Item count (#) does not match header count\'</code> notices which simply mean the number of headers detected does not match the number of cell contents.', 'woocommerce-exporter' ) . '</p>
	<hr />';
						}
						$output .= '
	<h3>' . __( 'Export Log', 'woocommerce-exporter' ) . '</h3>
	<p>' . __( 'This prints the raw export contents and is helpful when the jQuery plugin above fails due to major formatting errors.', 'woocommerce-exporter' ) . '</p>
	<textarea id="export_log" wrap="off">' . esc_textarea( $export_log ) . '</textarea>
	<hr />
	';
						echo $output;
					}

					woo_cd_manage_form();
					break;

				case 'update':
					woo_ce_admin_custom_fields_save();

					$message = __( 'Custom Fields saved. You can now select those additional fields from the Export Fields list.', 'woocommerce-exporter' );
					woo_cd_admin_notice_html( $message );
					woo_cd_manage_form();
					break;

				default:
					woo_cd_manage_form();
					break;

			}
			woo_cd_template_footer();

		}

		// HTML template for Export screen
		function woo_cd_manage_form() {

			$tab = ( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : false );
			// If Skip Overview is set then jump to Export screen
			if( $tab == false && woo_ce_get_option( 'skip_overview', false ) )
				$tab = 'export';

			// Check that WC() is available
			if( !function_exists( 'WC' ) ) {
				$message = __( 'We couldn\'t load the WooCommerce resource WC(), check that WooCommerce is installed and active. If this persists get in touch with us.', 'woocommerce-exporter' );
				woo_cd_admin_notice_html( $message, 'error' );
				return;
			}

			woo_ce_load_export_types();
			woo_ce_admin_fail_notices();

			include_once( WOO_CD_PATH . 'templates/admin/tabs.php' );

		}

		/* End of: WordPress Administration */

	} else {

		/* Start of: Storefront */

		function woo_ce_cron() {

			$action = ( function_exists( 'woo_get_action' ) ? woo_get_action() : false );
			// This is where the CRON export magic happens
			if( $action <> 'woo_ce-cron' )
				return;
	
			// Check that Store Exporter is installed and activated or jump out
			if( !function_exists( 'woo_ce_get_option' ) )
				return;

			// Return silent response and record to error log if CRON support is disabled, bad secret key provided or IP whitelist is in effect
			if( woo_ce_get_option( 'enable_cron', 0 ) == 0 ) {
				$message = __( 'Failed CRON access, CRON is disabled', 'woocommerce-exporter' );
				woo_ce_error_log( sprintf( 'Error: %s', $message ) );
				return;
			}

			$key = ( isset( $_GET['key'] ) ? sanitize_text_field( $_GET['key'] ) : '' );
			if( $key <> woo_ce_get_option( 'secret_key', '' ) ) {
				$ip_address = woo_ce_get_visitor_ip_address();
				$message = __( 'Failed CRON attempt from %s, incorrect secret key', 'woocommerce-exporter' );
				woo_ce_error_log( sprintf( 'Error: %s', sprintf( $message, $ip_address ) ) );
				return;
			}
			if( $ip_whitelist = apply_filters( 'woo_ce_cron_ip_whitelist', false ) ) {
				$ip_address = woo_ce_get_visitor_ip_address();
				if( !in_array( $ip_address, $ip_whitelist ) ) {
					$message = __( 'Failed CRON attempt from %s, did not match IP whitelist', 'woocommerce-exporter' );
					woo_ce_error_log( sprintf( 'Error: %s', sprintf( $message, $ip_address ) ) );
					return;
				}
				unset( $ip_whitelist );
			}

			$gui = ( isset( $_GET['gui'] ) ? absint( $_GET['gui'] ) : 0 );
			$response = ( isset( $_GET['response'] ) ? sanitize_text_field( $_GET['response'] ) : '' );
			// Output to screen in friendly design with on-screen error responses
			if( $gui == 1 ) {
				woo_ce_cron_export( 'gui' );
			// Return export download to browser in different expected formats, uses error_log() for error responses
			} else if( $gui == 0 && in_array( $response, array( 'download', 'raw', 'url', 'file', 'email', 'post', 'ftp' ) ) ) {
				switch( $response ) {

					case 'download':
					case 'raw':
					case 'url':
					case 'file':
					case 'email':
					case 'post':
					case 'ftp':
						echo woo_ce_cron_export( $response );
						break;

				}
			} else {
				// Return simple binary response
				echo absint( woo_ce_cron_export() );
			}
			exit();

		}
		add_action( 'init', 'woo_ce_cron', 12 );

		/* End of: Storefront */

	}

	// Run this function within the WordPress Administration and storefront to ensure Scheduled Exports happen
	function woo_ce_init() {

		include_once( WOO_CD_PATH . 'includes/functions.php' );
		if( function_exists( 'woo_ce_register_scheduled_export_cpt' ) )
			woo_ce_register_scheduled_export_cpt();

		if( function_exists( 'woo_ce_register_export_template_cpt' ) )
			woo_ce_register_export_template_cpt();

		// Check that Store Exporter Deluxe is installed and activated or jump out
		if( !function_exists( 'woo_ce_get_option' ) )
			return;

		// Check that WooCommerce is installed and activated or jump out
		if( !woo_is_woo_activated() )
			return;

		// Set the Plugin debug and logging levels if not already set
		if( !defined( 'WOO_CD_DEBUG' ) ) {
			$debug_mode = woo_ce_get_option( 'debug_mode', 0 );
			define( 'WOO_CD_DEBUG', $debug_mode );
		}

		if( !WOO_CD_DEBUG && !defined( 'WOO_CD_LOGGING' ) ) {
			// This should be off by default in production environments
			$logging_mode = woo_ce_get_option( 'logging_mode', 0 );
			define( 'WOO_CD_LOGGING', $logging_mode );
		} else if( WOO_CD_DEBUG && !defined( 'WOO_CD_LOGGING' ) ) {
			// Default to turn on logging mode when debug mode is enabled
			$logging_mode = woo_ce_get_option( 'logging_mode', 1 );
			define( 'WOO_CD_LOGGING', $logging_mode );
		}

		// Check if Scheduled Exports is enabled
		if( woo_ce_get_option( 'enable_auto', 0 ) == 1 ) {

			// Add custom schedule for automated exports
			add_filter( 'cron_schedules', 'woo_ce_cron_schedules' );

			if( function_exists( 'woo_ce_cron_activation' ) )
				woo_ce_cron_activation();

		}

		// Check if trigger export on New Order is enabled
		if( woo_ce_get_option( 'enable_trigger_new_order', 0 ) == 1 ) {

			// There are other WordPress Actions (woocommerce_new_order, woocommerce_api_create_order) we can hook into but woocommerce_checkout_update_order_meta is the only one where we can access the Order Items
			add_action( 'woocommerce_checkout_update_order_meta', 'woo_ce_trigger_new_order_export', 10, 1 );

		}

		if( defined( 'DOING_CRON' ) ) {
			// Every x minutes WP-CRON will run the automated export
			// Check for the legacy as well as new scheduled exports
			if( $scheduled_exports = woo_ce_get_scheduled_exports() ) {
				foreach( $scheduled_exports as $scheduled_export )
					add_action( 'woo_ce_auto_export_schedule_' . $scheduled_export, 'woo_ce_auto_export', 10, 1 );
			}
		}

	}
	add_action( 'init', 'woo_ce_init', 11 );

}
?>