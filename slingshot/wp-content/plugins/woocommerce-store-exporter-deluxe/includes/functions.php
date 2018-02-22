<?php
// Only load these resources if we are running an export, can be limited to a single export type
function woo_ce_load_export_types( $export_type = false ) {

	if( !empty( $export_type ) ) {
		if( file_exists( WOO_CD_PATH . 'includes/' . $export_type . '.php' ) )
			include_once( WOO_CD_PATH . 'includes/' . $export_type . '.php' );
		if( file_exists( WOO_CD_PATH . 'includes/' . $export_type . '-extend.php' ) )
			include_once( WOO_CD_PATH . 'includes/' . $export_type . '-extend.php' );
		if( file_exists( WOO_CD_PATH . 'includes/admin/' . $export_type . '.php' ) )
			include_once( WOO_CD_PATH . 'includes/admin/' . $export_type . '.php' );
		return;
	}

	include_once( WOO_CD_PATH . 'includes/product.php' );
	include_once( WOO_CD_PATH . 'includes/product-extend.php' );
	include_once( WOO_CD_PATH . 'includes/category.php' );
	include_once( WOO_CD_PATH . 'includes/category-extend.php' );
	include_once( WOO_CD_PATH . 'includes/tag.php' );
	include_once( WOO_CD_PATH . 'includes/tag-extend.php' );
	include_once( WOO_CD_PATH . 'includes/brand.php' );
	include_once( WOO_CD_PATH . 'includes/brand-extend.php' );
	include_once( WOO_CD_PATH . 'includes/order.php' );
	include_once( WOO_CD_PATH . 'includes/order-extend.php' );
	include_once( WOO_CD_PATH . 'includes/order-combined-extend.php' );
	include_once( WOO_CD_PATH . 'includes/order-individual.php' );
	include_once( WOO_CD_PATH . 'includes/order-individual-extend.php' );
	include_once( WOO_CD_PATH . 'includes/order-unique.php' );
	include_once( WOO_CD_PATH . 'includes/order-unique-extend.php' );
	include_once( WOO_CD_PATH . 'includes/customer.php' );
	include_once( WOO_CD_PATH . 'includes/customer-extend.php' );
	include_once( WOO_CD_PATH . 'includes/user.php' );
	include_once( WOO_CD_PATH . 'includes/user-extend.php' );
	include_once( WOO_CD_PATH . 'includes/review.php' );
	include_once( WOO_CD_PATH . 'includes/review-extend.php' );
	include_once( WOO_CD_PATH . 'includes/coupon.php' );
	include_once( WOO_CD_PATH . 'includes/coupon-extend.php' );
	include_once( WOO_CD_PATH . 'includes/subscription.php' );
	include_once( WOO_CD_PATH . 'includes/subscription-extend.php' );
	include_once( WOO_CD_PATH . 'includes/product_vendor.php' );
	include_once( WOO_CD_PATH . 'includes/product_vendor-extend.php' );
	include_once( WOO_CD_PATH . 'includes/commission.php' );
	include_once( WOO_CD_PATH . 'includes/shipping_class.php' );
	include_once( WOO_CD_PATH . 'includes/shipping_class-extend.php' );
	include_once( WOO_CD_PATH . 'includes/ticket.php' );
	include_once( WOO_CD_PATH . 'includes/ticket-extend.php' );
	include_once( WOO_CD_PATH . 'includes/attribute.php' );
	include_once( WOO_CD_PATH . 'includes/booking.php' );

	// Load the export type resources first
	include_once( WOO_CD_PATH . 'includes/admin/product.php' );
	include_once( WOO_CD_PATH . 'includes/admin/category.php' );
	include_once( WOO_CD_PATH . 'includes/admin/tag.php' );
	include_once( WOO_CD_PATH . 'includes/admin/brand.php' );
	include_once( WOO_CD_PATH . 'includes/admin/order.php' );
	include_once( WOO_CD_PATH . 'includes/admin/customer.php' );
	include_once( WOO_CD_PATH . 'includes/admin/user.php' );
	include_once( WOO_CD_PATH . 'includes/admin/review.php' );
	include_once( WOO_CD_PATH . 'includes/admin/coupon.php' );
	include_once( WOO_CD_PATH . 'includes/admin/subscription.php' );
	include_once( WOO_CD_PATH . 'includes/admin/product_vendor.php' );
	include_once( WOO_CD_PATH . 'includes/admin/commission.php' );
	include_once( WOO_CD_PATH . 'includes/admin/shipping_class.php' );
	include_once( WOO_CD_PATH . 'includes/admin/ticket.php' );
	include_once( WOO_CD_PATH . 'includes/admin/booking.php' );

}

include_once( WOO_CD_PATH . 'includes/cron.php' );

// Check if we are using PHP 5.3 and above
if( version_compare( phpversion(), '5.3' ) >= 0 )
	include_once( WOO_CD_PATH . 'includes/legacy.php' );
include_once( WOO_CD_PATH . 'includes/formatting.php' );

if( is_admin() ) {

	/* Start of: WordPress Administration */

	include_once( WOO_CD_PATH . 'includes/admin.php' );

	// WordPress Admin Pointers
	include_once( WOO_CD_PATH . 'includes/admin/pointers.php' );

	include_once( WOO_CD_PATH . 'includes/admin/widgets.php' );
	include_once( WOO_CD_PATH . 'includes/admin/scheduled_export.php' );
	include_once( WOO_CD_PATH . 'includes/export_template.php' );
	include_once( WOO_CD_PATH . 'includes/archives.php' );
	include_once( WOO_CD_PATH . 'includes/settings.php' );

	function woo_ce_export_init() {

		// Process any pre-export notice confirmations
		$action = ( function_exists( 'woo_get_action' ) ? woo_get_action() : false );
		switch( $action ) {

			// Reset hidden Export Types on the Quick Export screen
			case 'reset_hidden_export_types':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_reset_hidden_export_types' ) ) {
					woo_ce_update_option( 'hidden_export_types', false );
					$message = __( 'All hidden Export Types on the Quick Export screen have been restored.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			// Switch for turning on/off debug mode
			case 'enable_debug_mode':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_enable_debug_mode' ) ) {
					woo_ce_update_option( 'debug_mode', 1 );
					$message = __( 'Debugging mode within Store Exporter Deluxe has been enabled. Confirm this by opening the WooCommerce > Status screen.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			case 'disable_debug_mode':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_disable_debug_mode' ) ) {
					woo_ce_update_option( 'debug_mode', 0 );
					$message = __( 'Debugging mode within Store Exporter Deluxe has been de-activated. Confirm this by opening the WooCommerce > Status screen.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			// Switch for turning on/off logging mode
			case 'enable_logging_mode':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_enable_logging_mode' ) ) {
					woo_ce_update_option( 'logging_mode', 1 );
					$message = __( 'Logging mode within Store Exporter Deluxe has been enabled. Confirm this by opening the WooCommerce > Status screen.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			case 'disable_logging_mode':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_disable_logging_mode' ) ) {
					woo_ce_update_option( 'logging_mode', 0 );
					$message = __( 'Logging mode within Store Exporter Deluxe has been de-activated. Confirm this by opening the WooCommerce > Status screen.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			// Rebuild known TM EPO fields
			case 'rebuild_tm_epo_fields':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_rebuild_tm_epo_fields' ) ) {
					// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
					if( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
						delete_transient( WOO_CD_PREFIX . '_extra_product_option_fields' );
						if( function_exists( 'woo_ce_get_extra_product_option_fields' ) )
							woo_ce_get_extra_product_option_fields();
					}
					$message = __( 'The list of known WooCommerce TM Extra Product Options fields has been refreshed.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			// Reset all dismissed notices within Store Exporter Deluxe
			case 'nuke_notices':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_nuke_notices' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_nuke_dismissed_notices();
					$message = __( 'All dimissed notices within Store Exporter Deluxe have been restored.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			// Delete all WordPress Options associated with Store Exporter Deluxe
			case 'nuke_options':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_nuke_options' ) ) {
					// Delete WordPress Options used by Store Exporter Deluxe (Uninstall)
					if( woo_ce_nuke_options() ) {
						$message = __( 'All Store Exporter Deluxe WordPress Options have been deleted from your WordPress site, you can now de-activate and delete Store Exporter Deluxe.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message );
					} else {
						$message = __( 'Not all Store Exporter Deluxe WordPress Options could be deleted from your WordPress site, please see the WordPress Options table for Options prefixed by <code>woo_ce_</code>.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message, 'error' );
					}
				}
				break;

			// Delete all Archives
			case 'nuke_archives':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_nuke_archives' ) ) {
					// Delete saved exports
					if( woo_ce_nuke_archive_files() ) {
						$message = __( 'All existing Archives and their export files have been deleted from your WordPress site.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message );
					} else {
						$message = __( 'There were no existing Archives to be deleted from your WordPress site.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message, 'error' );
					}
				}
				break;

			// Delete Recent Scheduled Exports
			case 'nuke_recent_scheduled_exports':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_nuke_recent_scheduled_exports' ) ) {
					// Delete recent scheduled exports
					woo_ce_update_option( 'recent_scheduled_exports', false );
					$message = __( 'The Recent Scheduled Exports list has been cleared.', 'woocommerce-exporter' );
					woo_cd_admin_notice( $message );
				}
				break;

			// Reset WP-CRON
			case 'nuke_cron':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_nuke_cron' ) ) {
					// Reset WP-CRON
					if( woo_ce_nuke_cron() ) {
						$message = __( 'The WordPress Option \'cron\' has been reset, it will be re-populated on the next screen load.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message );
					} else {
						$message = __( ' WordPress Option \'cron\' could not be reset.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message, 'error' );
					}
				}
				break;

			// Delete all Scheduled Exports
			case 'nuke_scheduled_exports':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_nuke_archives' ) ) {
					// Delete Scheduled Exports
					if( woo_ce_nuke_scheduled_exports() ) {
						$message = __( 'All existing Scheduled Exports have been deleted from your WordPress site.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message );
					} else {
						$message = __( 'There were no existing Scheduled Exports to be deleted from your WordPress site.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message, 'error' );
					}
				}
				break;

			case 'dismiss_archives_privacy_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_archives_privacy_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_archives_privacy_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'override_archives_privacy':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_override_archives_privacy' ) ) {
					// Update Post Status of unsecured saved exports
					if( woo_ce_update_archives_privacy() ) {
						$message = __( 'All archived exports have been updated.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message );
					} else {
						$message = __( 'There were no existing archived exports to be updated.', 'woocommerce-exporter' );
						woo_cd_admin_notice( $message, 'error' );
					}
				}
				break;

			// Prompt on Export screen when insufficient memory (less than 64M is allocated)
			case 'dismiss_memory_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_memory_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_memory_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			// Prompt on Export screen when memory is showing different amounts (WordPress vs PHP)
			case 'dismiss_memory_sync_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_memory_sync_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_memory_sync_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			// Prompt on Scheduled Export screen when DISABLE_WP_CRON is defined
			case 'dismiss_disable_wp_cron_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_disable_wp_cron_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_disable_wp_cron_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			// Prompt on Export screen when open_basedir is enabled and the PHP temp directory is not in the exception list
			case 'dismiss_open_basedir_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_open_basedir_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_open_basedir_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			// Prompt on Export screen when PHP configuration option max_execution_time cannot be increased
			case 'dismiss_execution_time_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_execution_time_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_execution_time_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			// Prompt on Export screen when PHP 5.2 or lower is installed
			case 'dismiss_php_legacy':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_php_legacy' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_php_legacy', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_subscription_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_subscription_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_subscription_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_checkout_addons_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_checkout_addons_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_checkout_addons_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_extra_product_options_scan_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_extra_product_options_scan_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_extra_product_options_scan_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_query_monitor_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_query_monitor_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_query_monitor_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_export_running_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_export_running_prompt' ) ) {
					// Remember that we've dismissed this notice
					delete_transient( WOO_CD_PREFIX . '_running' );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_multisite_network_admin_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_multisite_network_admin_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_multisite_network_admin_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_ziparchive_missing_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_ziparchive_missing_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_ziparchive_missing_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_checkout_field_editor_pro_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_checkout_field_editor_pro_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_checkout_field_editor_pro_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_secure_archives_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_secure_archives_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_secure_archives_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_quick_export_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_quick_export_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_quick_export_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'enable_archives':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_enable_archives' ) ) {
					woo_ce_update_option( 'delete_file', 0 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_archives_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_archives_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_archives_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'hide_archives_tab':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_hide_archives_tab' ) ) {
					// Remember to hide the Archives tab
					woo_ce_update_option( 'hide_archives_tab', 1 );
					$url = add_query_arg( array( 'tab' => 'export', 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'restore_archives_tab':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_restore_archives_tab' ) ) {
					// Remember to show the Archives tab
					woo_ce_update_option( 'hide_archives_tab', 0 );
					$url = add_query_arg( array( 'tab' => 'archive', 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_scheduled_exports_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_scheduled_exports_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_scheduled_exports_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'dismiss_wp_mail_false_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_dismiss_wp_mail_false_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_wp_mail_false_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'hide_wp_mail_false_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_hide_wp_mail_false_prompt' ) ) {
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'dismiss_wp_mail_false_prompt', 1 );
					woo_ce_update_option( 'hide_wp_mail_false_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'hide_scheduled_exports_tab':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_hide_scheduled_exports_tab' ) ) {
					// Remember to hide the Scheduled Exports tab
					woo_ce_update_option( 'hide_scheduled_exports_tab', 1 );
					$url = add_query_arg( array( 'tab' => 'export', 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'restore_scheduled_exports_tab':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_restore_scheduled_exports_tab' ) ) {
					// Remember to show the Scheduled Exports tab
					woo_ce_update_option( 'hide_scheduled_exports_tab', 0 );
					$url = add_query_arg( array( 'tab' => 'scheduled_export', 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'enable_scheduled_exports':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_enable_scheduled_exports' ) ) {
					woo_ce_update_option( 'enable_auto', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			// Process scheduled export on next screen load
			case 'override_scheduled_export':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_override_scheduled_export' ) ) {
					// Get the Scheduled Export Post ID
					$scheduled_export = ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 );
					if( !empty( $scheduled_export ) ) {
						// Create a single WP-CRON event that runs immediately

						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'override_scheduled_export - registering single event' ) );

						$time = current_time( 'timestamp', 1 );
						$hook = sprintf( 'woo_ce_auto_export_schedule_%d', $scheduled_export );
						$args = array(
							'id' => (string)$scheduled_export . '+'
						);
						wp_schedule_single_event( $time, $hook, $args );

						if( WOO_CD_LOGGING ) {
							woo_ce_error_log( sprintf( 'Debug: %s', 'override_scheduled_export - time: ' . $time ) );
							woo_ce_error_log( sprintf( 'Debug: %s', 'override_scheduled_export - hook: '. $hook ) );
							woo_ce_error_log( sprintf( 'Debug: %s', 'override_scheduled_export - args: ' . print_r( $args, true ) ) );
						}
						$url = add_query_arg( array( 'tab' => 'scheduled_export', 'action' => null, 'post' => null, 'scheduled' => 1, '_wpnonce' => null ) );
						wp_redirect( $url );
						exit();
					}
				}
				break;

			// Process scheduled export on next screen load
			case 'cancel_scheduled_export':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_cancel_scheduled_export' ) ) {
					// Get the Scheduled Export Post ID
					$scheduled_export = ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 );
					if( !empty( $scheduled_export ) ) {
						// Create a new Recent Scheduled Export entry for failed Scheduled Exports
						$message = 'Scheduled Export aborted from Scheduled Exports screen. The CRON export engine is available again.';
						woo_ce_add_recent_scheduled_export( absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ), false, false, $message );
						// Clear the Scheduled Export transient so other Scheduled Exports can run
						delete_transient( WOO_CD_PREFIX . '_scheduled_export_id' );
						$url = add_query_arg( array( 'tab' => 'scheduled_export', 'action' => null, 'post' => null, '_wpnonce' => null ) );
						wp_redirect( $url );
						exit();
					}
				}
				break;

			// Mark Scheduled Export as Draft
			case 'draft_scheduled_export':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_draft_scheduled_export' ) ) {
					// Get the Scheduled Export Post ID
					$scheduled_export = ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 );
					if( !empty( $scheduled_export ) ) {
						$args = array(
							'ID' => $scheduled_export,
							'post_status' => 'draft'
						);
						wp_update_post( $args );
						$url = add_query_arg( array( 'tab' => 'scheduled_export', 'action' => null, 'post' => null, 'draft' => 1, 'publish' => null, 'clone' => null, '_wpnonce' => null ) );
						wp_redirect( $url );
						exit();
					}
				}

				break;

			// Mark Scheduled Export as Publish
			case 'publish_scheduled_export':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_publish_scheduled_export' ) ) {
					// Get the Scheduled Export Post ID
					$scheduled_export = ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 );
					if( !empty( $scheduled_export ) ) {
						$args = array(
							'ID' => $scheduled_export,
							'post_status' => 'publish'
						);
						wp_update_post( $args );
						$url = add_query_arg( array( 'tab' => 'scheduled_export', 'action' => null, 'post' => null, 'publish' => 1, 'draft' => null, 'clone' => null, '_wpnonce' => null ) );
						wp_redirect( $url );
						exit();
					}
				}
				break;

			// Clone Scheduled Export
			case 'clone_scheduled_export':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_clone_scheduled_export' ) ) {
					// Get the Scheduled Export Post ID
					$scheduled_export = ( isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0 );
					if( !empty( $scheduled_export ) ) {
						// Get the existing Scheduled Export
						$post = get_post( $scheduled_export );
						if( !empty( $post ) ) {
							$meta_keys = get_post_custom_keys( $scheduled_export );
							// Create a new Scheduled Export with Draft Status
							$post_status = 'draft';
							$title = ( !empty( $post->post_title ) ? $post->post_title : __( 'Untitled', 'woocommerce-exporter' ) );
							$post_author = wp_get_current_user();
							$post_author_id = $post_author->ID;
							$menu_order = 0;
							$args = array(
								'post_author' => $post_author_id,
								'post_content' => $post->post_content,
								'post_content_filtered' => $post->post_content_filtered,
								'post_title' => $title,
								'post_excerpt' => $post->post_excerpt,
								'post_status' => $post_status,
								'post_type' => $post->post_type,
								'menu_order' => $menu_order,
								'comment_status' => $post->comment_status,
								'ping_status' => $post->ping_status
							);
							$new_post_id = wp_insert_post( wp_slash( $args ) );
							// Save the existing Post meta to the new Scheduled Export
							if( !empty( $meta_keys ) ) {
								foreach( $meta_keys as $meta_key ) {
									$meta_values = get_post_custom_values( $meta_key, $scheduled_export );
									if( !empty( $meta_values ) ) {
										foreach( $meta_values as $meta_value ) {

											// Skip the following
											if( in_array( $meta_key, array( '_last_export', '_total_exports' ) ) )
												continue;

											$meta_value = maybe_unserialize( $meta_value );
											add_post_meta( $new_post_id, $meta_key, wp_slash( $meta_value ) );
										}
									}
								}
							}
						}
						$url = add_query_arg( array( 'tab' => 'scheduled_export', 'action' => null, 'post' => null, 'clone' => 1, 'publish' => null, 'draft' => null, '_wpnonce' => null ) );
						wp_redirect( $url );
						exit();
					}
				}
				break;

			// Hide an export type from the Quick Export screen
			case 'hide_export_type':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_hide_export_type' ) ) {
					$export_type = ( isset( $_GET['export_type'] ) ? sanitize_text_field( $_GET['export_type'] ) : false );
					if( !empty( $export_type ) ) {
						// Do something
						$hidden_export_types = woo_ce_get_option( 'hidden_export_types', array() );
						if( $hidden_export_types == false )
							$hidden_export_types = array();
						if( !in_array( $export_type, $hidden_export_types ) )
							$hidden_export_types[] = $export_type;
						woo_ce_update_option( 'hidden_export_types', $hidden_export_types );

						// Show the response
						$message = __( 'The selected export type has been hidden. To re-show hidden export types open Settings > General Settings > View advanced settings...', 'woocommerce-exporter' );
						woo_cd_admin_notice_html( $message );
					}
				}
				break;

			// Reset the Transient counters for all export types
			case 'refresh_export_type_counts':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_refresh_export_type_counts' ) ) {
					woo_ce_load_export_types();
					$transients = array(
						'product',
						'category',
						'tag',
						'brand',
						'order',
						'customer',
						'user',
						'review',
						'coupon',
						'attribute',
						'subscription',
						'product_vendor',
						'commission',
						'shipping_class',
						'ticket',
						'booking'
					);
					foreach( $transients as $transient ) {
						// Delete the existing count Transients
						delete_transient( WOO_CD_PREFIX . sprintf( '_%s_count', $transient ) );
						// Refresh the count Transients
						woo_ce_get_export_type_count( $transient );
					}

					// Gravity Forms - http://woothemes.com/woocommerce
					if( woo_ce_detect_export_plugin( 'gravity_forms' ) ) {
						delete_transient( WOO_CD_PREFIX . '_gravity_forms_products' );
						delete_transient( WOO_CD_PREFIX . '_gravity_forms_fields' );
						if( function_exists( 'woo_ce_get_gravity_forms_fields' ) )
							woo_ce_get_gravity_forms_fields();
					}

					// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
					if( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
						delete_transient( WOO_CD_PREFIX . '_extra_product_option_fields' );
						if( function_exists( 'woo_ce_get_extra_product_option_fields' ) )
							woo_ce_get_extra_product_option_fields();
					}

					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			case 'refresh_module_counts':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_refresh_module_counts' ) ) {

					// Delete the existing count Transients
					delete_transient( WOO_CD_PREFIX . '_modules_active' );
					delete_transient( WOO_CD_PREFIX . '_modules_all_count' );
					delete_transient( WOO_CD_PREFIX . '_modules_active_count' );
					delete_transient( WOO_CD_PREFIX . '_modules_inactive_count' );

					// Refresh the count Transients
					woo_ce_modules_list();
					woo_ce_refresh_active_export_plugins();

					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			// Move legacy archives exports to the sed-exports directory within Uploads
			case 'relocate_archived_exports':

				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'woo_ce_relocate_archived_exports' ) ) {

					// Create the sed-exports directory if it hasn't been
					woo_cd_create_secure_archives_dir();

					$updated = 0;
					if( $files = woo_ce_get_archive_files() ) {
						foreach( $files as $key => $file ) {
							$filepath = get_attached_file( $file->ID );
							// Check for archived exports that have not been moved to sed-exports
							if( strpos( $filepath, 'sed-exports' ) == false ) {
								// Move the export

								// Update the Post meta key _wp_attached_file
								$attached_file = get_post_meta( $file->ID, '_wp_attached_file', true );
								if( !empty( $attached_file ) )
									$attached_file = trailingslashit( 'sed-exports' ) . basename( $attached_file );
								$updated++;
							}
						}
					}

					// Show the response
					$message = sprintf( __( 'That\'s sorted, we\'ve relocated %d export files to the newly created <code>sed-exports</code> folder within the WordPress Uploads directory. Happy exporting!', 'woocommerce-exporter' ), $updated );
					woo_cd_admin_notice_html( $message );
				}
				break;

			// Save skip overview preference
			case 'skip_overview':
				// We need to verify the nonce.
				if( !empty( $_POST ) && check_admin_referer( 'skip_overview', 'woo_ce_skip_overview' ) ) {
					$skip_overview = false;
					if( isset( $_POST['skip_overview'] ) )
						$skip_overview = 1;
					// Remember that we've dismissed this notice
					woo_ce_update_option( 'skip_overview', $skip_overview );

					if( $skip_overview == 1 ) {
						$url = add_query_arg( array( 'tab' => 'export', '_wpnonce' => null ) );
						wp_redirect( $url );
						exit();
					}
				}
				break;

		}

	}

	// Displays a HTML notice when a WordPress or Store Exporter error is encountered
	function woo_ce_admin_fail_notices() {

		$troubleshooting_url = 'https://www.visser.com.au/documentation/store-exporter-deluxe/troubleshooting/';

		// If the failed flag is set then prepare for an error notice
		if( isset( $_GET['failed'] ) ) {
			$message = '';
			if( isset( $_GET['message'] ) )
				$message = urldecode( $_GET['message'] );
			if( $message ) {
				$message = sprintf( __( 'A WordPress or server error caused the export to fail, the exporter was provided with a reason: <em>%s</em>', 'woocommerce-exporter' ), $message );
				$message .= ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
			} else {
				$message = __( 'A WordPress or server error caused the exporter to fail, no reason was provided, if this persists please get in touch so we can reproduce and resolve this with you.', 'woocommerce-exporter' );
				$message .= ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
			}
			woo_cd_admin_notice_html( $message, 'error' );
		}

		// Check if WOO_CD_DEBUG is running
		if( WOO_CD_DEBUG ) {
			$message = __( 'Store Exporter Deluxe is currently in <attr title="WOO_CD_DEBUG">debugging mode</attr>. No export file downloads will be generated from the Quick Export screen nor will Scheduled Exports or manual CRON exports generate export files until debug mode is de-activated.', 'woocommerce-exporter' );
			$message .= ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)'; 
			woo_cd_admin_notice_html( $message, 'error' );
		}

		// Displays a notice where the maximum execution time cannot be set
		if( !woo_ce_get_option( 'dismiss_execution_time_prompt', 0 ) ) {
			$max_execution_time = ini_get( 'max_execution_time' );
			// Check if max_execution is unlimited
			if( $max_execution_time == false || $max_execution_time > 0 ) {
				$response = ( function_exists( 'ini_set' ) ? @ini_set( 'max_execution_time', 120 ) : false );
				if( $response === false || ( $response != absint( $max_execution_time ) ) ) {
					$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_execution_time_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_execution_time_prompt' ) ) ) );
					if( $max_execution_time == false && $max_execution_time == false ) {
						$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . __( 'We could not detect or override the PHP configuration option <code>max_execution_time</code>, this is not an indicatative of an issue but may limit the size of large exports.', 'woocommerce-exporter' );
						$message .= __( ' If exports fail after a period of loading/inactivity then this limit has likely been reached and may need to be increased by contacting your hosting provider.', 'woocommerce-exporter' );
					} else {
						$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . sprintf( __( 'We could not override the PHP configuration option <code>max_execution_time</code> which is set at %d seconds, this is typical of most hosting providers and is not an indicatation of an issue but may limit the size of large exports.', 'woocommerce-exporter' ), $max_execution_time );
						$message .= sprintf( __( ' If exports fail after %d seconds of loading/inactivity then this limit has likely been reached and may need to be increased by contacting your hosting provider.', 'woocommerce-exporter' ), $max_execution_time );
					}
					$message .= sprintf( __( ' See: <a href="%s" target="_blank">Increasing the PHP max_execution_time configuration option</a>', 'woocommerce-exporter' ), $troubleshooting_url ); 
					woo_cd_admin_notice_html( $message, 'notice' );
				}
			}
		}

		// Displays a notice where the memory allocated to WordPress falls below 64MB
		if( !woo_ce_get_option( 'dismiss_memory_prompt', 0 ) ) {
			$memory_limit = absint( ini_get( 'memory_limit' ) );
			$minimum_memory_limit = 64;
			if( $memory_limit < $minimum_memory_limit ) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_memory_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_memory_prompt' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message .= sprintf( __( 'We recommend setting memory to at least %dMB, your site has only %dMB allocated to it.', 'woocommerce-exporter' ), $minimum_memory_limit, $memory_limit );
				$message .= __( 'See', 'woocommerce-exporter' ) . ': ' . sprintf( '<a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Increasing memory allocated to PHP', 'woocommerce-exporter' ) );
				woo_cd_admin_notice_html( $message, 'error' );
			}
		}

		// Displays a notice where the memory allocated to WordPress is above 64MB but different to the php.ini configuration option
		if( !woo_ce_get_option( 'dismiss_memory_sync_prompt', 0 ) ) {
			$memory_limit = ini_get( 'memory_limit' );
			$wp_memory_limit = WP_MEMORY_LIMIT;
			$wp_max_memory_limit = WP_MAX_MEMORY_LIMIT;
			if( $wp_memory_limit <> $wp_max_memory_limit )
				$wp_memory_limit = WP_MAX_MEMORY_LIMIT;
			if( $wp_memory_limit > $memory_limit ) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_memory_sync_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_memory_sync_prompt' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message .= sprintf( __( 'We recommend setting memory to at least %dMB, your site has the recommended %dMB allocated to it but PHP is showing a different value, this is not an indicatative of an issue but may limit the size of large exports.', 'woocommerce-exporter' ), $minimum_memory_limit, $memory_limit );
				$message .= ' ' . __( 'See', 'woocommerce-exporter' ) . ': ' . sprintf( '<a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Increasing memory allocated to PHP', 'woocommerce-exporter' ) );
				woo_cd_admin_notice_html( $message, 'error' );
			}
		}

		// Displays a notice where the PHP open_basedir restriction is enabled
		if( !woo_ce_get_option( 'dismiss_open_basedir_prompt', 0 ) ) {
			if( $open_basedir = ini_get( 'open_basedir' ) ) {
				$temp_dir = sys_get_temp_dir();
				$override_temp_dir = apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() );
				// Check if the woo_ce_sys_get_temp_dir WordPress Filter has been used
				$has_filter = false;
				$has_valid = false;
				if( $temp_dir <> $override_temp_dir ) {
					$has_filter = true;
					$temp_dir = $override_temp_dir;
				}
				// Check if this is a Windows system
				if( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
					$temp_dir = str_replace( array( 'C:\\', 'D:\\' ), '../', $temp_dir );
					$temp_dir = str_replace( '\\', '/', $temp_dir );
					$open_basedir = str_replace( array( 'C:\\', 'D:\\' ), '../', $open_basedir );
					$open_basedir = str_replace( '\\', '/', $open_basedir );
				}
				// Check if the sys_get_temp_dir() is within the open_basedir list
				$open_basedir = explode( ':', $open_basedir );
				if( is_array( $open_basedir ) ) {
					if( !empty( $open_basedir ) ) {
						foreach( $open_basedir as $path ) {
							if( strstr( $path, $temp_dir ) != false ) {
								$has_valid = true;
								break;
							}
						}
						if( $has_valid ) {
							// Show a notice to confirm that the temporary path exists and is writable
							$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_open_basedir_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_open_basedir_prompt' ) ) ) );
							$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . sprintf( __( 'The PHP open_basedir restriction is enabled for this WordPress site, ensure that you can save tempoary files to <code>%s</code>.<br /><br />If you experience corrupt exports read how to <a href="%s" target="_blank">override the default directory PHP stores temporary files for this Plugin</a>.', 'woocommerce-exporter' ), $temp_dir, $troubleshooting_url . '#General_troubleshooting' );
							woo_cd_admin_notice_html( $message, 'notice' );
						} else {
							// Show a notice explaining what PHP open_basedir is and how to resolve it
							$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_open_basedir_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_open_basedir_prompt' ) ) ) );
							$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . sprintf( __( 'The PHP open_basedir restriction is enabled for this WordPress site but the directory <code>%s</code> is not in the allowed list of directories (<code>%s</code>).<br /><br />If you experience corrupt exports read how to <a href="%s" target="_blank">override the default directory PHP stores temporary files for this Plugin</a> or contact your hosting provider to resolve this.', 'woocommerce-exporter' ), $temp_dir, implode( ', ', $open_basedir ), $troubleshooting_url . '#General_troubleshooting' );
							woo_cd_admin_notice_html( $message, 'error' );
						}
					}
				}
				unset( $open_basedir, $override_temp_dir, $has_filter, $has_valid );
			}
		}

		// Displays a notice if PHP 5.2 or lower is installed
		if( version_compare( phpversion(), '5.3', '<' ) && !woo_ce_get_option( 'dismiss_php_legacy', 0 ) ) {
			$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_php_legacy', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_php_legacy' ) ) ) );
			$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . sprintf( __( 'Your PHP version (%s) is not supported and is very much out of date, since 2010 all users are strongly encouraged to upgrade to PHP 5.3+ and above. Contact your hosting provider to make this happen. See: <a href="%s" target="_blank">Migrating from PHP 5.2 to 5.3</a>', 'woocommerce-exporter' ), phpversion(), $troubleshooting_url . '#General_troubleshooting' );
			woo_cd_admin_notice_html( $message, 'error' );
		}

		// Displays notice if there are more than 2500 Subscriptions
		if( !woo_ce_get_option( 'dismiss_subscription_prompt', 0 ) ) {
			if( class_exists( 'WC_Subscriptions' ) ) {
				$wcs_version = ( function_exists( 'woo_ce_get_wc_subscriptions_version' ) ? woo_ce_get_wc_subscriptions_version() : false );
				if( version_compare( $wcs_version, '2.0.1', '<' ) ) {
					if( method_exists( 'WC_Subscriptions', 'is_large_site' ) ) {
						// Does this store have roughly more than 3000 Subscriptions
						if( WC_Subscriptions::is_large_site() ) {
							$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_subscription_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_subscription_prompt' ) ) ) );
							$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
							$message .= __( 'We\'ve detected the <em>is_large_site</em> flag has been set within WooCommerce Subscriptions. Please get in touch if exports are incomplete as we need to spin up an alternative export process to export Subscriptions from large stores.', 'woocommerce-exporter' );
							$message .= ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
							woo_cd_admin_notice_html( $message, 'notice' );
						}
					}
				}
			}
		}

		// Displays notice if WooCommerce Checkout Add-Ons is installed
		if( !woo_ce_get_option( 'dismiss_checkout_addons_prompt', 0 ) ) {
			if( woo_ce_detect_export_plugin( 'checkout_addons' ) ) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_checkout_addons_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_checkout_addons_prompt' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message .= __( 'It looks like you have WooCommerce Checkout Add-Ons activated. To export the Checkout Add-on ID, Checkout Add-on Label and Checkout Add-on Value associated with Orders ensure the Fee Order Item Type is selected from Export Options.', 'woocommerce-exporter' );
				woo_cd_admin_notice_html( $message );
			}
		}

		// Display notice if Extra Product Options is installed
		if( !woo_ce_get_option( 'dismiss_extra_product_options_scan_prompt', 0 ) ) {
			if( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_extra_product_options_scan_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_extra_product_options_scan_prompt' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message .= __( 'It looks like you have WooCommerce Extra Product Options activated. Just a heads up there may be a significant increase in memory usage as we automatically scan existing Orders to populate the available Order export fields. More information about this functionality is available from the Docs &raquo; Store Exporter Deluxe &raquo; Usage page on our website.', 'woocommerce-exporter' );
				woo_cd_admin_notice_html( $message );
			}
		}

		// Display notice if Query Monitor is installed
		if( !woo_ce_get_option( 'dismiss_query_monitor_prompt', 0 ) ) {
			if( class_exists( 'QM_Plugin' ) ) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_query_monitor_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_query_monitor_prompt' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . __( 'It looks like you have Query Monitor activated. Just a heads up there may be a significant increase in memory usage and time to generate exports as Query Monitor logs all queries.', 'woocommerce-exporter' );
				woo_cd_admin_notice_html( $message );
			}
		}

		// If the export failed the WordPress Transient will still exist
		if( get_transient( WOO_CD_PREFIX . '_running' ) ) {
			// Check if a fatal PHP error has been detected
			if( get_transient( WOO_CD_PREFIX . '_crashed' ) ) {
				$message = __( 'A WordPress or server error caused the exporter to fail with a blank screen, this is usually isolated to a memory or timeout issue, if this persists please get in touch so we can reproduce and resolve this.', 'woocommerce-exporter' );
				$message .= ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
				woo_cd_admin_notice_html( $message, 'error' );
				delete_transient( WOO_CD_PREFIX . '_crashed' );
				delete_transient( WOO_CD_PREFIX . '_running' );
			} else {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_export_running_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_export_running_prompt' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message .= __( 'It looks like an export is currently running in the background. Unfortunately we cannot tell if the background export has completed just that it hasn\'t yet finished. If you are confident there are no background exports running click Dismiss to hide this notice.', 'woocommerce-exporter' );
				$message .= ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
				woo_cd_admin_notice_html( $message, 'notice' );
			}
		}

		// If the woo_cd_exported WordPress Option exists then an Order export failed and we should roll back changes
		if( woo_ce_get_option( 'exported', false ) ) {
			$orders = woo_ce_get_option( 'exported', false );
			if( !empty( $orders ) ) {
				foreach( $orders as $order_id ) {
					// Remove the export flag
					delete_post_meta( $order_id, '_woo_cd_exported' );
					$order_flag_notes = woo_ce_get_option( 'order_flag_notes', 0 );
					if( $order_flag_notes ) {
						// Add an additional Order Note
						$order = woo_ce_get_order_wc_data( $order_id );
						$note = __( 'Order export flag was cleared.', 'woocommerce-exporter' );
						$order->add_order_note( $note );
						unset( $order );
					}
				}
			}
			unset( $orders, $order_id );
			delete_option( WOO_CD_PREFIX . '_exported' );
			$message = __( 'It looks like a previous Orders export failed before it could complete, we have removed the exported flag assigned to those Orders so they are not excluded from your next export using <em>Filter Orders by Order Date</em> > <em>Since last export</em>.', 'woocommerce-exporter' );
			$message .= ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
			woo_cd_admin_notice_html( $message );
		}

		// Archives
		if( ( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '' ) == 'archive' ) {

			// Displays a HTML notice if Archives is disabled and the Archives tab is opened
			if(
				woo_ce_get_option( 'delete_file', '1' ) == 1
				&& ( !woo_ce_get_option( 'dismiss_archives_prompt', 0 ) )
			) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_archives_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_archives_prompt' ) ) ) );
				$override_url = esc_url( add_query_arg( array( 'action' => 'hide_archives_tab', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_archives_tab' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message .= __( 'It looks like the saving of export archives is disabled from the Enabled Archives option on the Settings tab, would you like to hide the Archives tab aswell?', 'woocommerce-exporter' ) . '<br /><br /><a href="' . $override_url . '" class="button-primary">' . __( 'Hide Archives tab', 'woocommerce-exporter' ) . '</a>';
				woo_cd_admin_notice_html( $message, 'notice' );
			}

		}

		// Scheduled Exports
		if( ( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '' ) == 'scheduled_export' ) {

			// Displays a HTML notice if Scheduled Exports are disabled and the Scheduled Exports tab is opened
			if(
				woo_ce_get_option( 'enable_auto', '0' ) == 0
				&& ( !woo_ce_get_option( 'dismiss_scheduled_exports_prompt', 0 ) )
			) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_scheduled_exports_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_scheduled_exports_prompt' ) ) ) );
				$override_url = esc_url( add_query_arg( array( 'action' => 'hide_scheduled_exports_tab', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_scheduled_exports_tab' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message .= __( 'It looks like scheduled exports are disabled from the <em>Enable scheduled exports</em> option on the Settings tab, would you like to hide the Scheduled Exports tab aswell?', 'woocommerce-exporter' ) . '<br /><br /><a href="' . $override_url . '" class="button-primary">' . __( 'Hide Scheduled Exports tab', 'woocommerce-exporter' ) . '</a>';
				woo_cd_admin_notice_html( $message, 'notice' );
			}

			// Displays a HTML notice if a Scheduled Export fails with a wp_mail() false response and the Scheduled Exports tab is opened
			if(
				woo_ce_get_option( 'wp_mail_false_prompt', '0' )
				&& ( !woo_ce_get_option( 'dismiss_wp_mail_false_prompt', 0 ) )
			) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_wp_mail_false_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_wp_mail_false_prompt' ) ) ) );
				$override_url = esc_url( add_query_arg( array( 'action' => 'hide_wp_mail_false_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_wp_mail_false_prompt' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message .= __( 'It looks like a recent scheduled export returned false when attempting to send an e-mail, you can override this response for future scheduled exports if the recipient received the e-mail as expected by clicking the <em>Override mail response</em> button.', 'woocommerce-exporter' ) . '<br /><br /><a href="' . $override_url . '" class="button-primary">' . __( 'Override mail response', 'woocommerce-exporter' ) . '</a>';
				woo_cd_admin_notice_html( $message, 'notice' );
			}

		}

		// Displays a HTML notice if Archives are detected without a Post Status of private
		if( woo_ce_get_unprotected_archives( array( 'count' => true ) ) && !woo_ce_get_option( 'dismiss_archives_privacy_prompt', 0 ) ) {
			$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_archives_privacy_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_archives_privacy_prompt' ) ) ) );
			$override_url = esc_url( add_query_arg( array( 'action' => 'override_archives_privacy', '_wpnonce' => wp_create_nonce( 'woo_ce_override_archives_privacy' ) ) ) );
			$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
			$message .= __( 'It looks like some archived exports require updating, would you like to update these archived exports now?', 'woocommerce-exporter' ) . '<br /><br /><a href="' . $override_url . '" class="button-primary">' . __( 'Update export archives', 'woocommerce-exporter' ) . '</a>';
			woo_cd_admin_notice_html( $message );
		}

		// Displays a HTML notice if this is WordPress MultiSite and the current User is the Network Admin
		if( !woo_ce_get_option( 'dismiss_multisite_network_admin_prompt', 0 ) ) {
			if( is_multisite() ) {
				if( is_main_network( get_current_blog_id() ) ) {
					$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_multisite_network_admin_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_multisite_network_admin_prompt' ) ) ) );
					$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
					$message .= __( 'It looks like you are logged in as the Network Admin within your WordPress MultiSite. The Quick Export screen may load a little slower as the Export Types Count number checks all of your WordPress Sites.', 'woocommerce-exporter' );
					woo_cd_admin_notice_html( $message );
				}
			}
		}

		// Displays a HTML notice if the ZipArchive Class is not detected
		if( !woo_ce_get_option( 'dismiss_ziparchive_missing_prompt', 0 ) ) {
			if( !class_exists('ZipArchive') ) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_ziparchive_missing_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_ziparchive_missing_prompt' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message .= sprintf( __( 'It looks like the PHP Class <code>ZipArchive</code> is missing, this means we are unable to generate Excel XLSX (Excel 97-2003) and some Excel XLS (Excel 2007-2013) files.<br /><br />Read how to <a href="%s" target="_blank">install the ZipArchive Class or use the alternate PCLZip PHP Class</a> or contact your hosting provider to resolve this.', 'woocommerce-exporter' ), $troubleshooting_url . '#General_troubleshooting' );
				woo_cd_admin_notice_html( $message, 'error' );
			}
		}

		// Displays a HTML notice if WooCommerce Checkout Field Editor Pro and export support is not enabled
		if( !woo_ce_get_option( 'dismiss_checkout_field_editor_pro_prompt', 0 ) ) {
			if(
				woo_ce_detect_export_plugin( 'checkout_field_editor_pro' ) && 
				!class_exists( 'WCFE_Checkout_Fields_Export_Handler' )
			) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_checkout_field_editor_pro_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_checkout_field_editor_pro_prompt' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
				$message .= __( 'It looks like export support is not enabled within WooCommerce Checkout Field Editor Pro. To enable export integration open the WooCommerce &raquo; Checkout Form &raquo; Advanced Settings screen and select <em>Enable CSV Export support</em> then fill the <em>CSV export columns</em> field.', 'woocommerce-exporter' );
				woo_cd_admin_notice_html( $message, 'notice' );
			}
		}

/*
		// @mod - Will work on in 2.4+, requires a mountain of testing before release
		// If the sed-exports folder within Uploads does not exist
		$upload_dir =  wp_upload_dir();
		if( !file_exists( $upload_dir['basedir'] . '/sed-exports/.htaccess' ) && woo_ce_get_option( 'dismiss_secure_archives_prompt', false ) == false ) {
			$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_secure_archives_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_secure_archives_prompt' ) ) ) );
			$action_url = esc_url( add_query_arg( array( 'action' => 'relocate_archived_exports', '_wpnonce' => wp_create_nonce( 'woo_ce_relocate_archived_exports' ) ) ) );
			$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>';
			$message .= __( 'It looks like your exports are out in the open, let\'s move them to a secure location within the WordPres Uploads directory.', 'woocommerce-exporter' ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)' . '<br /><br /><a href="' . $action_url . '" class="button-primary">' . __( 'Re-locate archived exports', 'woocommerce-exporter' ) . '</a>';
			woo_cd_admin_notice_html( $message, 'error' );
		}
*/

	}

	// Saves the state of Export fields for next export
	function woo_ce_save_fields( $export_type = '', $fields = array(), $sorting = array() ) {

		// Default fields
		if( $fields == false && !is_array( $fields ) )
			$fields = array();
		$export_types = array_keys( woo_ce_get_export_types() );
		if( in_array( $export_type, $export_types ) && !empty( $fields ) ) {
			woo_ce_update_option( $export_type . '_fields', array_map( 'sanitize_text_field', $fields ) );
			woo_ce_update_option( $export_type . '_sorting', array_map( 'absint', $sorting ) );
		}

	}

	// Returns number of an Export type prior to export, used on Store Exporter screen
	function woo_ce_get_export_type_count( $export_type = '', $args = array() ) {

		global $wpdb;

		$count_sql = null;
		$woocommerce_version = woo_get_woo_version();

		switch( $export_type ) {

			case 'product':
			case 'category':
			case 'tag':
			case 'order':
			case 'customer':
			case 'user':
			case 'review':
			case 'coupon':
			case 'shipping_class':
			case 'ticket':
			case 'attribute':
				$count = ( function_exists( 'woo_ce_get_export_type_' . $export_type . '_count' ) ? call_user_func( 'woo_ce_get_export_type_' . $export_type . '_count' ) : 0 );
				break;

			// Allow Plugin/Theme authors to populate their own custom export type counts
			default:
				$count = 0;
				$count = apply_filters( 'woo_ce_get_export_type_count', $count, $export_type, $args );
				break;

		}
		if( isset( $count ) || $count_sql ) {
			if( isset( $count ) ) {
				if( is_object( $count ) ) {
					$count = (array)$count;
					$count = absint( array_sum( $count ) );
				}
				return $count;
			} else {
				if( $count_sql )
					$count = $wpdb->get_var( $count_sql );
				else
					$count = 0;
			}
			return $count;
		} else {
			return 0;
		}

	}

	// In-line display of export file and export details when viewed via WordPress Media screen
	function woo_ce_read_export_file( $post = false ) {

		if( empty( $post ) ) {
			if( isset( $_GET['post'] ) )
				$post = get_post( $_GET['post'] );
		}

		if( $post->post_type != 'attachment' )
			return;

		// Check if the Post matches one of our Post Mime Types
		if( !in_array( $post->post_mime_type, array_values( woo_ce_get_mime_types() ) ) )
			return;

		$filepath = get_attached_file( $post->ID );

		// We can only read CSV, TSV and XML file types, the others are encoded
		if( in_array( $post->post_mime_type, array( 'text/csv', 'text/tab-separated-values', 'application/xml', 'application/rss+xml' ) ) ) {

			$contents = __( 'No export entries were found, please try again with different export filters.', 'woocommerce-exporter' );
			if( file_exists( $filepath ) ) {
				$contents = file_get_contents( $filepath );
			} else {
				// This resets the _wp_attached_file Post meta key to the correct value
				update_attached_file( $post->ID, $post->guid );
				// Try grabbing the file contents again
				$filepath = get_attached_file( $post->ID );
				if( file_exists( $filepath ) ) {
					$handle = fopen( $filepath, "r" );
					$contents = stream_get_contents( $handle );
					fclose( $handle );
				}
			}
			if( !empty( $contents ) )
				include_once( WOO_CD_PATH . 'templates/admin/media-csv_file.php' );

		}

		// We can still show the Export Details for any supported Post Mime Type
		$export_type = get_post_meta( $post->ID, '_woo_export_type', true );
		$columns = get_post_meta( $post->ID, '_woo_columns', true );
		$rows = get_post_meta( $post->ID, '_woo_rows', true );
		$scheduled_id = get_post_meta( $post->ID, '_scheduled_id', true );
		$start_time = get_post_meta( $post->ID, '_woo_start_time', true );
		$end_time = get_post_meta( $post->ID, '_woo_end_time', true );
		$idle_memory_start = get_post_meta( $post->ID, '_woo_idle_memory_start', true );
		$data_memory_start = get_post_meta( $post->ID, '_woo_data_memory_start', true );
		$data_memory_end = get_post_meta( $post->ID, '_woo_data_memory_end', true );
		$idle_memory_end = get_post_meta( $post->ID, '_woo_idle_memory_end', true );

		include_once( WOO_CD_PATH . 'templates/admin/media-export_details.php' );

	}
	add_action( 'edit_form_after_editor', 'woo_ce_read_export_file' );

	// Returns label of Export type slug used on Store Exporter screen
	function woo_ce_export_type_label( $export_type = '', $echo = false ) {

		$output = '';
		if( !empty( $export_type ) ) {
			$export_types = woo_ce_get_export_types();
			if( array_key_exists( $export_type, $export_types ) )
				$output = $export_types[$export_type];
		}
		if( $echo )
			echo $output;
		else
			return $output;

	}

	// Returns a list of archived exports
	function woo_ce_get_archive_files() {

		$post_type = 'attachment';
		$meta_key = '_woo_export_type';
		$args = array(
			'post_type' => $post_type,
			'post_mime_type' => array_values( woo_ce_get_mime_types() ),
			'meta_key' => $meta_key,
			'meta_value' => null,
			'post_status' => 'any',
			'posts_per_page' => -1
		);
		if( isset( $_GET['filter'] ) ) {
			$filter = $_GET['filter'];
			if( !empty( $filter ) )
				$args['meta_value'] = $filter;
		}
		$files = get_posts( $args );
		return $files;

	}

	function woo_ce_nuke_archive_files() {

		$post_type = 'attachment';
		$meta_key = '_woo_export_type';
		$args = array(
			'post_type' => $post_type,
			'post_mime_type' => array_values( woo_ce_get_mime_types() ),
			'meta_key' => $meta_key,
			'meta_value' => null,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'fields' => 'ids'
		);
		$post_query = new WP_Query( $args );
		if( !empty( $post_query->found_posts ) ) {
			foreach( $post_query->posts as $post_ID )
				wp_delete_attachment( $post_ID, true );
			return true;
		}

	}

	// Reset WP-CRON
	function woo_ce_nuke_cron() {

		if( update_option( 'cron', '' ) )
			return true;

	}

	// Delete all Scheduled Exports
	function woo_ce_nuke_scheduled_exports() {

		$scheduled_exports = woo_ce_get_scheduled_exports();
		if( !empty( $scheduled_exports ) ) {
			foreach( $scheduled_exports as $post_ID )
				wp_delete_post( $post_ID, true );
			return true;
		}

	}

	// Delete all WordPress Options generated by Store Exporter
	function woo_ce_nuke_options() {

		global $wpdb;

		$prefix = 'woo_ce_%';

		// Get a list of WordPress Options prefixed by woo_ce_
		$options_sql = $wpdb->prepare( "SELECT `option_name` FROM `" . $wpdb->prefix . "options` WHERE `option_name` LIKE %s", $prefix );
		$options = $wpdb->get_col( $options_sql );
		if( !empty( $options ) ) {
			$count = 0;
			// Get a count of WordPress Options to be deleted
			$size = count( $options );
			foreach( $options as $option ) {
				// Get a count of deleted WordPress Options
				if( delete_option( $option ) )
					$count++;
			}
			// Compare the count of WordPress Options vs deleted WordPress Options
			if( $count == $size )
				return true;
		}

	}

	// Reset all dismissed notices within Store Exporter
	function woo_ce_nuke_dismissed_notices() {

		global $wpdb;

		$prefix = 'woo_ce_dismiss_%';

		// Get a list of WordPress Options prefixed by woo_ce_dismiss_
		$options_sql = $wpdb->prepare( "SELECT `option_name` FROM `" . $wpdb->prefix . "options` WHERE `option_name` LIKE %s", $prefix );
		$options = $wpdb->get_col( $options_sql );
		if( !empty( $options ) ) {
			foreach( $options as $option )
				delete_option( $option );
		}

	}

	// Returns a list of Attachments which are exposed to the public
	function woo_ce_get_unprotected_archives( $postarr = array() ) {

		$post_type = 'attachment';
		$meta_key = '_woo_export_type';
		$args = array(
			'post_type' => $post_type,
			'post_mime_type' => array_values( woo_ce_get_mime_types() ),
			'meta_key' => $meta_key,
			'post_status' => 'inherit',
			'posts_per_page' => -1,
			'fields' => 'ids'
		);
		$args = wp_parse_args( $postarr, $args );
		$post_query = new WP_Query( $args );
		if( !empty( $post_query->found_posts ) ) {
			// Check if we are returning a count or list
			if( isset( $postarr['count'] ) ) {
				return $post_query->found_posts;
			}
			return $post_query->posts;
		}

	}

	function woo_ce_update_archives_privacy() {

		$attachments = woo_ce_get_unprotected_archives();
		if( !empty( $attachments ) ) {
			foreach( $attachments as $post_ID ) {
				$args = array(
					'ID' => $post_ID,
					'post_status' => 'private'
				);
				wp_update_post( $args );
			}
			return true;
		}

	}

	// Returns an archived export with additional details
	function woo_ce_get_archive_file( $file = '' ) {

		$upload_dir = wp_upload_dir();
		$file->export_type = get_post_meta( $file->ID, '_woo_export_type', true );
		$file->export_type_label = woo_ce_export_type_label( $file->export_type );
		if( empty( $file->export_type ) )
			$file->export_type = __( 'Unassigned', 'woocommerce-exporter' );
		if( empty( $file->guid ) )
			$file->guid = $upload_dir['url'] . '/' . basename( $file->post_title );
		$file->post_mime_type = get_post_mime_type( $file->ID );
		if( !$file->post_mime_type )
			$file->post_mime_type = __( 'N/A', 'woocommerce-exporter' );
		$file->media_icon = wp_get_attachment_image( $file->ID, array( 80, 60 ), true );
		if( $author_name = get_user_by( 'id', $file->post_author ) )
			$file->post_author_name = $author_name->display_name;
		$file->post_date = woo_ce_format_archive_date( $file->ID );
		unset( $author_name, $t_time, $time );
		return $file;

	}

	// HTML template for displaying the current export type filter on the Archives screen
	function woo_ce_archives_quicklink_current( $current = '' ) {

		$output = '';
		if( isset( $_GET['filter'] ) ) {
			$filter = $_GET['filter'];
			if( $filter == $current )
				$output = ' class="current"';
		} else if( $current == 'all' ) {
			$output = ' class="current"';
		}
		echo $output;

	}

	// HTML template for displaying the number of each export type filter on the Archives screen
	function woo_ce_archives_quicklink_count( $type = '' ) {

		$post_type = 'attachment';
		$meta_key = '_woo_export_type';
		$args = array(
			'post_type' => $post_type,
			'meta_key' => $meta_key,
			'meta_value' => null,
			'numberposts' => -1,
			'post_status' => 'any',
			'fields' => 'ids'
		);
		if( !empty( $type ) )
			$args['meta_value'] = $type;
		$post_query = new WP_Query( $args );
		return absint( $post_query->found_posts );

	}

	/* End of: WordPress Administration */

}

function woo_ce_raise_export_memory_limit() {

	// Check if WP_MAX_MEMORY_LIMIT is the same as the WP_MEMORY_LIMIT
	if( wp_convert_hr_to_bytes( WP_MAX_MEMORY_LIMIT ) < wp_convert_hr_to_bytes( WP_MEMORY_LIMIT ) ) {
		return WP_MEMORY_LIMIT;
	}

}

// Export process for CSV file
function woo_ce_export_dataset( $export_type = null, &$output = null ) {

	global $export;

	if( WOO_CD_LOGGING )
		woo_ce_error_log( sprintf( 'Debug: %s', 'functions.php - before woo_ce_export_dataset(): ' . ( time() - $export->start_time ) ) );

	$separator = $export->delimiter;
	$line_ending = woo_ce_get_line_ending();
	$export->columns = array();
	$export->total_rows = 0;
	$export->total_columns = 0;

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

	if( ( !$export->cron && !$export->scheduled_export ) )
		set_transient( WOO_CD_PREFIX . '_running', time(), woo_ce_get_option( 'timeout', HOUR_IN_SECONDS ) );

	// Load up the fatal error notice if we 500 Internal Server Error (memory), hit a server timeout or encounter a fatal PHP error
	add_action( 'shutdown', 'woo_ce_fatal_error' );

	// Drop in our content filters here
	add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );
	add_filter( 'attribute_escape', 'woo_ce_filter_attribute_escape', 10, 2 );

	woo_ce_load_export_types();
	switch( $export_type ) {

		// Products
		case 'product':
			$fields = woo_ce_get_product_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_product_field( $key );
			}
			if( $export->args['gallery_unique'] ) {
				$export->fields = woo_ce_unique_product_gallery_fields( $export->fields );
				$export->columns = woo_ce_unique_product_gallery_columns( $export->columns, $export->fields );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_product', 'woo_ce_export_dataset_multisite_override_product', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_product', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_product', 'woo_ce_export_dataset_multisite_override_product' );
			} else {
				add_filter( 'woo_ce_export_dataset_product', 'woo_ce_export_dataset_override_product', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_product', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_product', 'woo_ce_export_dataset_override_product' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Categories
		case 'category':
			$fields = woo_ce_get_category_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_category_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_category', 'woo_ce_export_dataset_multisite_override_category', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_category', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_category', 'woo_ce_export_dataset_multisite_override_category' );
			} else {
				add_filter( 'woo_ce_export_dataset_category', 'woo_ce_export_dataset_override_category', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_category', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_category', 'woo_ce_export_dataset_override_category' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Tags
		case 'tag':
			$fields = woo_ce_get_tag_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_tag_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_tag', 'woo_ce_export_dataset_multisite_override_tag', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_tag', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_tag', 'woo_ce_export_dataset_multisite_override_tag' );
			} else {
				add_filter( 'woo_ce_export_dataset_tag', 'woo_ce_export_dataset_override_tag', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_tag', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_tag', 'woo_ce_export_dataset_override_tag' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Brands
		case 'brand':
			$fields = woo_ce_get_brand_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_brand_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_brand', 'woo_ce_export_dataset_multisite_override_brand', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_brand', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_brand', 'woo_ce_export_dataset_multisite_override_brand' );
			} else {
				add_filter( 'woo_ce_export_dataset_brand', 'woo_ce_export_dataset_override_brand', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_brand', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_brand', 'woo_ce_export_dataset_override_brand' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Orders
		case 'order':
			$fields = woo_ce_get_order_fields( 'summary' );
			if( WOO_CD_LOGGING )
				woo_ce_error_log( sprintf( 'Debug: %s', 'functions.php - woo_ce_export_dataset() > woo_ce_get_order_fields(): ' . ( time() - $export->start_time ) ) );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				if( WOO_CD_LOGGING )
					woo_ce_error_log( sprintf( 'Debug: %s', 'array_intersect_assoc(): ' . ( time() - $export->start_time ) ) );
				// Do not apply Field Editor changes to the unique Order Items Formatting rule
				if( $export->args['order_items'] == 'unique' )
					remove_filter( 'woo_ce_order_fields', 'woo_ce_override_order_field_labels', 11 );
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_order_field( $key );
				if( WOO_CD_LOGGING )
					woo_ce_error_log( sprintf( 'Debug: %s', 'foreach $export->fields: ' . ( time() - $export->start_time ) ) );
				// Do not apply Field Editor changes to the unique Order Items Formatting rule
				if( $export->args['order_items'] == 'unique' )
					add_filter( 'woo_ce_order_fields', 'woo_ce_override_order_field_labels', 11 );
			}
			// Check this is not an XML or RSS export format
			if( $export->args['order_items'] == 'unique' && !in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {
				$export->fields = woo_ce_unique_order_item_fields( $export->fields, $export->args );
				if( WOO_CD_LOGGING )
					woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_unique_order_item_fields(): ' . ( time() - $export->start_time ) ) );
				$export->columns = woo_ce_unique_order_item_columns( $export->columns, $export->fields, $export->args );
				if( WOO_CD_LOGGING )
					woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_unique_order_item_columns(): ' . ( time() - $export->start_time ) ) );
			}
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( WOO_CD_LOGGING )
				woo_ce_error_log( sprintf( 'Debug: %s', 'before woo_ce_export_dataset_order: ' . ( time() - $export->start_time ) ) );
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_order', 'woo_ce_export_dataset_multisite_override_order', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_order', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_order', 'woo_ce_export_dataset_multisite_override_order' );
			} else {
				add_filter( 'woo_ce_export_dataset_order', 'woo_ce_export_dataset_override_order', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_order', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_order', 'woo_ce_export_dataset_override_order' );
			}
			if( WOO_CD_LOGGING )
				woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_export_dataset_order: ' . ( time() - $export->start_time ) ) );
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Customers
		case 'customer':
			$fields = woo_ce_get_customer_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_customer_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_customer', 'woo_ce_export_dataset_multisite_override_customer', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_customer', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_customer', 'woo_ce_export_dataset_multisite_override_customer' );
			} else {
				add_filter( 'woo_ce_export_dataset_customer', 'woo_ce_export_dataset_override_customer', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_customer', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_customer', 'woo_ce_export_dataset_override_customer' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Users
		case 'user':
			$fields = woo_ce_get_user_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_user_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_user', 'woo_ce_export_dataset_multisite_override_user', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_user', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_user', 'woo_ce_export_dataset_multisite_override_user' );
			} else {
				add_filter( 'woo_ce_export_dataset_user', 'woo_ce_export_dataset_override_user', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_user', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_user', 'woo_ce_export_dataset_override_user' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Reviews
		case 'review':
			$fields = woo_ce_get_review_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_review_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_review', 'woo_ce_export_dataset_multisite_override_review', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_review', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_review', 'woo_ce_export_dataset_multisite_override_review' );
			} else {
				add_filter( 'woo_ce_export_dataset_review', 'woo_ce_export_dataset_override_review', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_review', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_review', 'woo_ce_export_dataset_override_review' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Coupons
		case 'coupon':
			$fields = woo_ce_get_coupon_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_coupon_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_coupon', 'woo_ce_export_dataset_multisite_override_coupon', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_coupon', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_coupon', 'woo_ce_export_dataset_multisite_override_coupon' );
			} else {
				add_filter( 'woo_ce_export_dataset_coupon', 'woo_ce_export_dataset_override_coupon', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_coupon', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_coupon', 'woo_ce_export_dataset_override_coupon' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Subscriptions
		case 'subscription':
			$fields = woo_ce_get_subscription_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_subscription_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_subscription', 'woo_ce_export_dataset_multisite_override_subscription', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_subscription', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_subscription', 'woo_ce_export_dataset_multisite_override_subscription' );
			} else {
				if( WOO_CD_LOGGING )
					woo_ce_error_log( sprintf( 'Debug: %s', 'before woo_ce_export_dataset_subscription: ' . ( time() - $export->start_time ) ) );
				add_filter( 'woo_ce_export_dataset_subscription', 'woo_ce_export_dataset_override_subscription', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_subscription', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_subscription', 'woo_ce_export_dataset_override_subscription' );
				if( WOO_CD_LOGGING )
					woo_ce_error_log( sprintf( 'Debug: %s', 'after woo_ce_export_dataset_subscription: ' . ( time() - $export->start_time ) ) );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Product Vendors
		case 'product_vendor':
			$fields = woo_ce_get_product_vendor_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_product_vendor_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_product_vendor', 'woo_ce_export_dataset_multisite_override_product_vendor', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_product_vendor', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_product_vendor', 'woo_ce_export_dataset_multisite_override_product_vendor' );
			} else {
				add_filter( 'woo_ce_export_dataset_product_vendor', 'woo_ce_export_dataset_override_product_vendor', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_product_vendor', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_product_vendor', 'woo_ce_export_dataset_override_product_vendor' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Commissions
		case 'commission':
			$fields = woo_ce_get_commission_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_commission_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_commission', 'woo_ce_export_dataset_multisite_override_commission', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_commission', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_commission', 'woo_ce_export_dataset_multisite_override_commission' );
			} else {
				add_filter( 'woo_ce_export_dataset_commission', 'woo_ce_export_dataset_override_commission', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_commission', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_commission', 'woo_ce_export_dataset_override_commission' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Shipping Classes
		case 'shipping_class':
			$fields = woo_ce_get_shipping_class_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_shipping_class_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_shipping_class', 'woo_ce_export_dataset_multisite_override_shipping_class', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_shipping_class', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_shipping_class', 'woo_ce_export_dataset_multisite_override_shipping_class' );
			} else {
				add_filter( 'woo_ce_export_dataset_shipping_class', 'woo_ce_export_dataset_override_shipping_class', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_shipping_class', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_shipping_class', 'woo_ce_export_dataset_override_shipping_class' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Tickets
		case 'ticket':
			$fields = woo_ce_get_ticket_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_ticket_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_ticket', 'woo_ce_export_dataset_multisite_override_ticket', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_ticket', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_ticket', 'woo_ce_export_dataset_multisite_override_ticket' );
			} else {
				add_filter( 'woo_ce_export_dataset_ticket', 'woo_ce_export_dataset_override_ticket', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_ticket', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_ticket', 'woo_ce_export_dataset_override_ticket' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Bookings
		case 'booking':
			$fields = woo_ce_get_booking_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_booking_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_booking', 'woo_ce_export_dataset_multisite_override_booking', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_booking', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_booking', 'woo_ce_export_dataset_multisite_override_booking' );
			} else {
				add_filter( 'woo_ce_export_dataset_booking', 'woo_ce_export_dataset_override_booking', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_booking', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_booking', 'woo_ce_export_dataset_override_booking' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

		// Attributes
		case 'attribute':
			$fields = woo_ce_get_attribute_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( (array)$export->fields, $fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = woo_ce_get_attribute_field( $key );
			}
			$export->total_columns = count( $export->columns );
			$export->data_memory_start = woo_ce_current_memory_usage();
			if( apply_filters( 'woo_ce_export_dataset_multisite', true ) && is_multisite() && is_main_network( get_current_blog_id() ) ) {
				add_filter( 'woo_ce_export_dataset_multisite_attribute', 'woo_ce_export_dataset_multisite_override_attribute', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_multisite_attribute', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_multisite_attribute', 'woo_ce_export_dataset_multisite_override_attribute' );
			} else {
				add_filter( 'woo_ce_export_dataset_attribute', 'woo_ce_export_dataset_override_attribute', 10, 2 );
				$output = apply_filters( 'woo_ce_export_dataset_attribute', $output, $export_type );
				remove_filter( 'woo_ce_export_dataset_attribute', 'woo_ce_export_dataset_override_attribute' );
			}
			$export->data_memory_end = woo_ce_current_memory_usage();
			break;

	}

	// Remove our content filters here to play nice with other Plugins
	remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );
	remove_filter( 'attribute_escape', 'woo_ce_filter_attribute_escape' );

	// Remove our fatal error notice so not to conflict with the CRON or scheduled export engine	
	remove_action( 'shutdown', 'woo_ce_fatal_error' );

	// Export completed successfully
	if( ( !$export->cron && !$export->scheduled_export ) )
		delete_transient( WOO_CD_PREFIX . '_running' );

	if( WOO_CD_LOGGING )
		woo_ce_error_log( sprintf( 'Debug: %s', 'functions.php - after woo_ce_export_dataset(): ' . ( time() - $export->start_time ) ) );

	// Check if we're using PHPExcel or generic export engine
	if( WOO_CD_DEBUG || in_array( $export->export_format, array( 'xml', 'rss' ) ) ) {

		// Check that the export file is populated, export columns have been assigned and rows counted
		if( !empty( $output ) && $export->total_rows && $export->total_columns ) {
			if( WOO_CD_DEBUG && !in_array( $export->export_format, apply_filters( 'woo_ce_phpexcel_supported_export_formats', array( 'csv', 'tsv', 'xls', 'xlsx' ) ) ) && ( !$export->cron && !$export->scheduled_export ) ) {
				if( in_array( $export->export_format, array( 'xml', 'rss' ) ) )
					$output = woo_ce_format_xml( $output );
				$response = set_transient( WOO_CD_PREFIX . '_debug_log', base64_encode( $output ), woo_ce_get_option( 'timeout', ( MINUTE_IN_SECONDS * 10 ) ) );
				if( $response !== true ) {
					$message = __( 'The export contents were too large to store in a single WordPress transient, use the Volume offset / Limit volume options to reduce the size of your export and try again.', 'woocommerce-exporter' ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)';
					if( function_exists( 'woo_cd_admin_notice' ) ) {
						woo_cd_admin_notice( $message, 'error' );
					} else {
						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'functions.php - woo_ce_export_dataset() - %s', $message ) ) );
					}
					return;
				} else {
					return true;
				}
			} else {
				return $output;
			}
		}

	} else {
		return $output;
	}

}

function woo_ce_fatal_error() {

	global $export;

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

	$error = error_get_last();
	if( $error !== null ) {
		set_transient( WOO_CD_PREFIX . '_crashed', 1, woo_ce_get_option( 'timeout', HOUR_IN_SECONDS ) );
		$message = '';
		$notice = sprintf( __( 'Refer to the following error and if you continue to have problems see our <a href="%s" target="_blank">Usage and Troubleshooting documents</a> or contact us on <a href="http://www.visser.com.au/premium-support/" target="_blank">Support</a> for further assistance.<br /><br /><code>%s in %s on line %d</code>', 'woocommerce-exporter' ), $troubleshooting_url, $error['message'], $error['file'], $error['line'] );
		if ( substr( $error['message'], 0, 22 ) === 'Maximum execution time' ) {
			$message = __( 'The server\'s maximum execution time is too low to complete this export, use our batch export function - Limit Volume and Volume Offset under Export Options - to create smaller exports. This is commonly due to a low timeout limit set by your hosting provider or PHP Safe Mode being enabled. Consider increasing the timeout limit or reducing the size of your export.', 'woocommerce-exporter' );
		} elseif ( substr( $error['message'], 0, 19 ) === 'Allowed memory size' ) {
			$message = __( 'The server\'s maximum memory size is too low to complete this export, use our batch export function - Limit Volume and Volume Offset under Export Options - to create smaller exports. Consider increasing available memory to WordPress or reducing the size of your export.', 'woocommerce-exporter' );
		} else if( $error['type'] === E_ERROR ) {
			// Test if it's WP All Import conflicting with the PHPExcel library
			if( substr( $error['message'], 0, 33 ) == "Class 'PHPExcel_Writer_Excel2007'" && ( strstr( $error['file'], 'wp-all-import' ) !== false ) ) {
				$message = __( 'A fatal PHP error was encountered during the export process, this was due to the Plugin WP All Import pre-loading the PHPExcel library. Contact the Plugin author of WP All Import - Soflyy - for more information.', 'woocommerce-exporter' );
			// Test if ZipArchive is missing within the PHPExcel library
			} else if( substr( $error['message'], 0, 29 ) == "Class 'ZipArchive' not found" && ( strstr( $error['file'], 'Excel2007.php' ) !== false ) ) {
				$message = sprintf( __( 'A fatal PHP error was encountered during the export process, this was due to the server not loading the ZipArchive Class within the PHPExcel library. See our <a href="%s">Troubleshooting document</a> to learn how to switch to the PCLZip Class for XLS and XLSX exports.', 'woocommerce-exporter' ), $troubleshooting_url );
			// Test if it's a WooCommerce 3.0 compatibility issue
			} else if( substr( $error['message'], 0, 36 ) == "Cannot access protected property WC_" ) {
				$message = __( 'A fatal PHP error was encountered during the export process, this was due to an undiscovered compatibility issue with WooCommerce 3.0. Please open a <a href="http://www.visser.com.au/premium-support/" target="_blank">Support ticket with us</a>.', 'woocommerce-exporter' );
			} else {
				$message = __( 'A fatal PHP error was encountered during the export process, we couldn\'t detect or diagnose it further.', 'woocommerce-exporter' );
			}
		}
		if( !empty( $message ) ) {

			// Save a record to the PHP error log
			woo_ce_error_log( sprintf( __( 'Fatal error: %s - PHP response: %s in %s on line %s', 'woocommerce-exporter' ), $message, $error['message'], $error['file'], $error['line'] ) );
			error_log( sprintf( __( 'Fatal error: %s - PHP response: %s in %s on line %s', 'woocommerce-exporter' ), $message, $error['message'], $error['file'], $error['line'] ) );

			// Only display the message if this is a manual export
			if( ( !$export->cron && !$export->scheduled_export ) ) {
				$output = '<div id="message" class="error"><p>' . sprintf( __( '<strong>[store-exporter-deluxe]</strong> An unexpected error occurred. %s', 'woocommerce-exporter' ), $message . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'woocommerce-exporter' ) . '</a>)' ) . '</p><p>' . $notice . '</p></div>';
				echo $output;
			} else if( $export->scheduled_export ) {
				// Create a new Recent Scheduled Export entry for failed Scheduled Exports
				woo_ce_add_recent_scheduled_export( absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ), false, false, sprintf( '%s - PHP response: %s in %s on line %s', $message, $error['message'], $error['file'], $error['line'] ) );
				// Clear the Scheduled Export transient so other Scheduled Exports can run
				delete_transient( WOO_CD_PREFIX . '_scheduled_export_id' );
			}

		}
	}

}

// List of Export types used on Store Exporter screen
function woo_ce_get_export_types() {

	$export_types = array(
		'product' => __( 'Products', 'woocommerce-exporter' ),
		'category' => __( 'Categories', 'woocommerce-exporter' ),
		'tag' => __( 'Tags', 'woocommerce-exporter' ),
		'brand' => __( 'Brands', 'woocommerce-exporter' ),
		'order' => __( 'Orders', 'woocommerce-exporter' ),
		'customer' => __( 'Customers', 'woocommerce-exporter' ),
		'user' => __( 'Users', 'woocommerce-exporter' ),
		'review' => __( 'Reviews', 'woocommerce-exporter' ),
		'coupon' => __( 'Coupons', 'woocommerce-exporter' ),
		'subscription' => __( 'Subscriptions', 'woocommerce-exporter' ),
		'product_vendor' => __( 'Product Vendors', 'woocommerce-exporter' ),
		'commission' => __( 'Commission', 'woocommerce-exporter' ),
		'shipping_class' => __( 'Shipping Classes', 'woocommerce-exporter' ),
		'ticket' => __( 'Tickets', 'woocommerce-exporter' ),
		'booking' => __( 'Bookings', 'woocommerce-exporter' ),
		'attribute' => __( 'Attributes', 'woocommerce-exporter' )
	);
	$export_types = apply_filters( 'woo_ce_export_types', $export_types );
	return $export_types;

}

function woo_ce_get_export_type_label( $export_type = '' ) {

	$output = $export_type;
	if( !empty( $export_type ) ) {
		$export_types = woo_ce_get_export_types();
		// Check our export type exists
		$output = ( isset( $export_types[$export_type] ) ? $export_types[$export_type] : $output );
	}
	return $output;

}

function woo_ce_generate_file_headers( $post_mime_type = 'text/csv' ) {

	global $export;

	header( sprintf( 'Content-Type: %s; charset=%s', esc_attr( $post_mime_type ), esc_attr( $export->encoding ) ) );
	header( sprintf( 'Content-Disposition: attachment; filename="%s"', $export->filename ) );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );
	header( sprintf( 'Content-Encoding: %s', esc_attr( $export->encoding ) ) );

}

// Function to generate filename of export file based on the Export type
function woo_ce_generate_filename( $export_type = '', $override = '' ) {

	// Check if a fixed filename hasn't been provided
	if( !empty( $override ) ) {
		$filename = $override;
	} else {
		// Get the filename from WordPress options
		$filename = woo_ce_get_option( 'export_filename', '%store_name%-export_%dataset%-%date%-%time%-%random%' );
		// Check for empty filename
		if( empty( $filename ) )
			$filename = '%store_name%-export_%dataset%-%date%-%time%-%random%';

		// Strip file extensions if present
		$filename = str_replace( array( '.xml', '.xls', '.csv' ), '', $filename );
		if( ( strpos( $filename, '.xml' ) !== false ) || ( strpos( $filename, '.xls' ) !== false ) || ( strpos( $filename, '.csv' ) !== false ) )
			$filename = str_replace( array( '.xml', '.xls', '.csv' ), '', $filename );

	}

	// Populate the available tags
	$date = apply_filters( 'woo_ce_filename_tag_date', date( 'Y_m_d', current_time( 'timestamp' ) ) );
	$time = apply_filters( 'woo_ce_filename_tag_time', date( 'H_i_s', current_time( 'timestamp' ) ) );
	$random = mt_rand( 10000000, 99999999 );
	$store_name = sanitize_title( get_bloginfo( 'name' ) );

	// Switch out the tags for filled values
	$filename = str_replace( '%dataset%', $export_type, $filename );
	$filename = str_replace( '%date%', $date, $filename );
	$filename = str_replace( '%time%', $time, $filename );
	$filename = str_replace( '%random%', $random, $filename );
	$filename = str_replace( '%store_name%', $store_name, $filename );

	return $filename;

}

// Returns the Post object of the export file saved as an attachment to the WordPress Media library
function woo_ce_save_file_attachment( $filename = '', $post_mime_type = 'text/csv' ) {

	if( !empty( $filename ) ) {
		$post_type = 'woo-export';
		$args = array(
			'post_status' => 'private',
			'post_title' => $filename,
			'post_type' => $post_type,
			'post_mime_type' => $post_mime_type
		);
		$post_ID = wp_insert_attachment( $args, $filename );
		if( is_wp_error( $post_ID ) )
			woo_ce_error_log( sprintf( 'Warning: %s', sprintf( 'save_file_attachment() - $s: %s', $filename, $result->get_error_message() ) ) );
		else
			return $post_ID;
	}

}

// Updates the GUID of the export file attachment to match the correct file URL
function woo_ce_save_file_guid( $post_ID, $export_type, $upload_url = '' ) {

	add_post_meta( $post_ID, '_woo_export_type', $export_type );
	if( !empty( $upload_url ) ) {
		$args = array(
			'ID' => $post_ID,
			'guid' => $upload_url
		);
		wp_update_post( $args );
	}

}

// Save critical export details against the archived export
function woo_ce_save_file_details( $post_ID ) {

	global $export;

	add_post_meta( $post_ID, '_woo_start_time', $export->start_time );
	add_post_meta( $post_ID, '_woo_idle_memory_start', $export->idle_memory_start );
	add_post_meta( $post_ID, '_woo_columns', $export->total_columns );
	// Check if column headers are included
	if( $export->header_formatting && in_array( $export->export_format, apply_filters( 'woo_ce_phpexcel_supported_export_formats', array( 'csv', 'tsv', 'xls', 'xlsx' ) ) ) )
		$export->total_rows++;
	add_post_meta( $post_ID, '_woo_rows', $export->total_rows );
	add_post_meta( $post_ID, '_woo_data_memory_start', $export->data_memory_start );
	add_post_meta( $post_ID, '_woo_data_memory_end', $export->data_memory_end );

}

// Update detail of existing archived export
function woo_ce_update_file_detail( $post_ID, $detail, $value ) {

	if( strstr( $detail, '_woo_' ) !== false )
		update_post_meta( $post_ID, $detail, $value );

}

// Returns a list of allowed Export type statuses, can be overridden on a per-Export type basis
function woo_ce_post_statuses( $extra_status = array(), $override = false ) {

	$output = array(
		'publish',
		'pending',
		'draft',
		'future',
		'private',
		'trash'
	);
	if( $override && !empty( $extra_status ) ) {
		$output = $extra_status;
	} else {
		if( $extra_status )
			$output = array_merge( $output, $extra_status );
	}
	return $output;

}

function woo_ce_get_export_formats() {

	$export_formats = array(
		'csv' => array(
			'title' => __( 'CSV', 'woocommerce-exporter' ),
			'description' => __( 'Comma Separated Values', 'woocommerce-exporter' )
		),
		'tsv' => array(
			'title' => __( 'TSV', 'woocommerce-exporter' ),
			'description' => __( 'Tab Separated Values', 'woocommerce-exporter' )
		),
		'xls' => array(
			'title' => __( 'Excel (XLS)', 'woocommerce-exporter' ),
			'description' => __( 'Excel 97-2003', 'woocommerce-exporter' )
		),
		'xlsx' => array(
			'title' => __( 'Excel (XLSX)', 'woocommerce-exporter' ),
			'description' => __( 'Excel 2007-2013', 'woocommerce-exporter' )
		),
		'xml' => array(
			'title' => __( 'XML', 'woocommerce-exporter' ),
			'description' => __( 'EXtensible Markup Language', 'woocommerce-exporter' )
		),
		'rss' => array(
			'title' => __( 'RSS 2.0', 'woocommerce-exporter' ),
			'description' => sprintf( __( '<attr title="%s">XML</attr> feed in RSS 2.0 format', 'woocommerce-exporter' ), __( 'EXtensible Markup Language', 'woocommerce-exporter' ) )
		)
	);

	// Allow Plugin/Theme authors to add support for additional export formats
	$export_formats = apply_filters( 'woo_ce_get_export_formats', $export_formats );

	return $export_formats;

}

function woo_ce_get_mime_types() {

	$mime_types = array(
		'csv' => 'text/csv',
		'tsv' => 'text/tab-separated-values',
		'xls' => 'application/vnd.ms-excel',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'xml' => 'application/xml',
		'rss' => 'application/rss+xml'
	);

	// Allow Plugin/Theme authors to add support for additional export formats
	$mime_types = apply_filters( 'woo_ce_get_mime_types', $mime_types );

	return $mime_types;

}

function woo_ce_get_mime_type_extension( $mime_type, $search_by = 'extension' ) {

	$mime_types = woo_ce_get_mime_types();
	if( $search_by == 'extension' ) {
		if( isset( $mime_types[$mime_type] ) )
			return $mime_types[$mime_type];
	} else if( $search_by == 'mime_type' ) {
		if( $key = array_search( $mime_type, $mime_types ) )
			return strtoupper( $key );
	}

}

function woo_ce_add_missing_mime_type( $mime_types = array() ) {

	// Add CSV mime type if it has been removed
	if( !isset( $mime_types['csv'] ) )
		$mime_types['csv'] = 'text/csv';
	// Add TSV mime type if it has been removed
	if( !isset( $mime_types['tsv'] ) )
		$mime_types['tsv'] = 'text/tab-separated-values';
	// Add XLS mime type if it has been removed
	if( !isset( $mime_types['xls'] ) )
		$mime_types['xls'] = 'application/vnd.ms-excel';
	// Add XLSX mime type if it has been removed
	if( !isset( $mime_types['xlsx'] ) )
		$mime_types['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
	// Add XML mime type if it has been removed
	if( !isset( $mime_types['xml'] ) )
		$mime_types['xml'] = 'application/xml';
	// Add RSS mime type if it has been removed
	if( !isset( $mime_types['rss'] ) )
		$mime_types['rss'] = 'application/rss+xml';

	// Allow Plugin/Theme authors to add support for additional export formats
	$mime_types = apply_filters( 'woo_ce_add_missing_mime_type', $mime_types );

	return $mime_types;

}
add_filter( 'upload_mimes', 'woo_ce_add_missing_mime_type' );

if( !function_exists( 'woo_ce_sort_fields' ) ) {
	function woo_ce_sort_fields( $key ) {

		return $key;

	}
}

function woo_ce_register_scheduled_export_cpt() {

	$labels = array(
		'name'               => __( 'Scheduled Exports', 'woocommerce-exporter' ),
		'singular_name'      => __( 'Scheduled Export', 'woocommerce-exporter' ),
		'add_new'            => __( 'Add Scheduled Export', 'woocommerce-exporter' ),
		'add_new_item'       => __( 'Add New Scheduled Export', 'woocommerce-exporter' ),
		'edit'               => __( 'Edit', 'woocommerce-exporter' ),
		'edit_item'          => __( 'Edit Scheduled Export', 'woocommerce-exporter' ),
		'new_item'           => __( 'New Scheduled Export', 'woocommerce-exporter' ),
		'view'               => __( 'View Scheduled Export', 'woocommerce-exporter' ),
		'view_item'          => __( 'View Scheduled Export', 'woocommerce-exporter' ),
		'search_items'       => __( 'Search Scheduled Exports', 'woocommerce-exporter' ),
		'not_found'          => __( 'No Scheduled exports found', 'woocommerce-exporter' ),
		'not_found_in_trash' => __( 'No Scheduled exports found in trash', 'woocommerce-exporter' ),
		'parent'             => __( 'Parent Scheduled Exports', 'woocommerce-exporter' ),
		'menu_name'          => _x( 'Scheduled Exports', 'Scheduled Export', 'woocommerce-exporter' )
	);

	$args = array(
		'labels'              => $labels,
		'description'         => __( 'This is where Scheduled Exports for Store Exporter Deluxe are managed.', 'woocommerce-exporter' ),
		'public'              => false,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'query_var'           => true,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'has_archive'         => false,
		'hierarchical'        => false,
		'menu_position'       => null,
		'supports'            => array( 'title', 'excerpt' )
	);
	$post_type = 'scheduled_export';
	register_post_type( $post_type, $args );

}

function woo_ce_get_scheduled_exports( $args = array() ) {

	$post_type = 'scheduled_export';
	$defaults = array(
		'post_type' => $post_type,
		'posts_per_page' => -1,
		'fields' => 'ids',
		'suppress_filters' => 1
	);
	$args = wp_parse_args( $args, $defaults );

	$exports_query = new WP_Query( $args );
	if( $exports_query->posts ) {
		return $exports_query->posts;
	}

}

function woo_ce_get_next_scheduled_export( $scheduled_export = 0, $format = 'human' ) {

	// Check that WordPress has set up a wp_cron task for our export

	$args = array(
		'id' => $scheduled_export
	);
	$time = wp_next_scheduled( 'woo_ce_auto_export_schedule_' . $scheduled_export, $args );
	if( $time !== false ) {

		// Check for rogue Trash scheduled exports
		if( get_post_status( $scheduled_export ) == 'trash' )
			return;

		if( $format == 'human' )
			return human_time_diff( current_time( 'timestamp', 1 ), $time );
		else
			return $time;
	}

}

function woo_ce_add_recent_scheduled_export( $scheduled_export = 0, $gui = '', $post_ID = 0, $error = '' ) {

	global $export;

	// Get the list of existing recent scheduled exports
	$recent_exports = woo_ce_get_option( 'recent_scheduled_exports', array() );
	if( empty( $recent_exports ) )
		$recent_exports = array();

	// Check if a Scheduled Export has failed
	if( !empty( $error ) || !empty( $export->error ) ) {

		// Allow Plugin/Theme authors to add support for actions based on failed Scheduled Exports
		do_action( 'woo_ce_failed_scheduled_export', $scheduled_export, ( !empty( $error ) ? $error : $export->error ) );

	}

	$post_ID = ( isset( $post_ID ) ? $post_ID : 0 );
	$time = time();
	$recent_exports[] = array(
		'post_id' => ( empty( $export->error ) ? $post_ID : 0 ),
		'name' => ( isset( $export->filename ) ? $export->filename : false ),
		'date' => $time,
		'method' => $gui,
		'error' => ( !empty( $error ) ? $error : $export->error ),
		'scheduled_id' => $scheduled_export
	);
	woo_ce_update_option( 'recent_scheduled_exports', $recent_exports );

}

// Convert the legacy scheduled export WordPress Options to CPT
function woo_ce_legacy_scheduled_export() {

	global $user_ID;

	$post_type = 'scheduled_export';
	$args = array(
		'post_type' => $post_type,
		'post_date' => current_time( 'mysql' ),
		'post_date_gmt' => current_time( 'mysql', 1 ),
		'post_title' => __( 'My scheduled export', 'woocommerce-exporter' ),
		'post_status' => 'publish',
		'comment_status' => 'closed',
		'ping_status' => 'closed',
		'post_content' => '',
		'post_excerpt' => ''
	);
	$post_ID = wp_insert_post( $args );
	if( is_wp_error( $post_ID ) !== true ) {
		// Load WordPress Options for migration

		// General
		$export_type = woo_ce_get_option( 'auto_type', 'product' );
		update_post_meta( $post_ID, '_export_type', $export_type );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_type' );
		$export_format = woo_ce_get_option( 'auto_format', 'csv' );
		update_post_meta( $post_ID, '_export_format', $export_format );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_format' );
		$export_method = woo_ce_get_option( 'auto_method', 'archive' );
		update_post_meta( $post_ID, '_export_method', $export_method );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_method' );
		$export_fields = woo_ce_get_option( 'export_fields', 'all' );
		update_post_meta( $post_ID, '_export_fields', $export_fields );
		delete_option( WOO_CD_PREFIX . '_' . 'export_fields' );

		// Filters
		$product_filter_category = woo_ce_get_option( 'auto_product_category', false );
		update_post_meta( $post_ID, '_filter_product_category', $product_filter_category );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_product_category' );
		$product_filter_tag = woo_ce_get_option( 'auto_product_tag', false );
		update_post_meta( $post_ID, '_filter_product_tag', $product_filter_tag );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_product_tag' );
		$product_filter_status = woo_ce_get_option( 'auto_product_status', false );
		update_post_meta( $post_ID, '_filter_product_status', $product_filter_status );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_product_status' );
		$product_filter_type = woo_ce_get_option( 'auto_product_type', false );
		update_post_meta( $post_ID, '_filter_product_type', $product_filter_type );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_product_type' );
		$product_filter_stock = woo_ce_get_option( 'auto_product_stock', false );
		update_post_meta( $post_ID, '_filter_product_stock', $product_filter_stock );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_product_stock' );
		$product_filter_featured = woo_ce_get_option( 'auto_product_featured', false );
		update_post_meta( $post_ID, '_filter_product_featured', $product_filter_featured );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_product_featured' );
		$product_filter_shipping_class = woo_ce_get_option( 'auto_product_shipping_class', false );
		update_post_meta( $post_ID, '_filter_product_shipping_class', $product_filter_shipping_class );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_product_shipping_class' );

		$order_filter_date = woo_ce_get_option( 'auto_order_date', false );
		update_post_meta( $post_ID, '_filter_order_date', $order_filter_date );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_order_date' );
		$order_filter_date_variable = woo_ce_get_option( 'auto_order_date_variable', false );
		update_post_meta( $post_ID, '_filter_order_date_variable', $order_filter_date_variable );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_order_date_variable' );
		$order_filter_date_variable_length = woo_ce_get_option( 'auto_order_date_variable_length', false );
		update_post_meta( $post_ID, '_filter_order_date_variable_length', $order_filter_date_variable_length );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_order_date_variable_length' );
		$order_filter_date_from = woo_ce_get_option( 'auto_order_dates_from', false );
		update_post_meta( $post_ID, '_filter_order_dates_from', $order_filter_date_from );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_order_dates_from' );
		$order_filter_date_to = woo_ce_get_option( 'auto_order_dates_to', false );
		update_post_meta( $post_ID, '_filter_order_dates_to', $order_filter_date_to );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_order_dates_to' );
		$order_filter_status = woo_ce_get_option( 'auto_order_status', false );
		update_post_meta( $post_ID, '_filter_order_status', $order_filter_status );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_order_status' );
		$order_filter_product = woo_ce_get_option( 'auto_order_product', false );
		update_post_meta( $post_ID, '_filter_order_product', $order_filter_product );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_order_product' );
		$order_filter_billing_country = woo_ce_get_option( 'auto_order_billing_country', false );
		update_post_meta( $post_ID, '_filter_order_billing_country', $order_filter_billing_country );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_order_billing_country' );
		$order_filter_shipping_country = woo_ce_get_option( 'auto_order_shipping_country', false );
		update_post_meta( $post_ID, '_filter_order_shipping_country', $order_filter_shipping_country );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_order_shipping_country' );
		$order_filter_payment = woo_ce_get_option( 'auto_order_payment', false );
		update_post_meta( $post_ID, '_filter_order_payment', $order_filter_payment );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_order_payment' );
		$order_filter_shipping = woo_ce_get_option( 'auto_order_shipping', false );
		update_post_meta( $post_ID, '_filter_order_shipping', $order_filter_shipping );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_order_shipping' );

		// Method
		$email_to = woo_ce_get_option( 'email_to', false );
		update_post_meta( $post_ID, '_method_email_to', $email_to );
		delete_option( WOO_CD_PREFIX . '_' . 'email_to' );
		$email_subject = woo_ce_get_option( 'email_subject', false );
		update_post_meta( $post_ID, '_method_email_subject', $email_subject );
		delete_option( WOO_CD_PREFIX . '_' . 'email_subject' );

		$post_to = woo_ce_get_option( 'post_to', false );
		update_post_meta( $post_ID, '_method_post_to', $post_to );
		delete_option( WOO_CD_PREFIX . '_' . 'post_to' );

		$ftp_host = woo_ce_get_option( 'auto_ftp_method_host', false );
		update_post_meta( $post_ID, '_method_ftp_host', $ftp_host );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_ftp_method_host' );
		$ftp_port = woo_ce_get_option( 'auto_ftp_method_port', false );
		update_post_meta( $post_ID, '_method_ftp_port', $ftp_port );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_ftp_method_port' );
		$ftp_protocol = woo_ce_get_option( 'auto_ftp_method_protocol', false );
		update_post_meta( $post_ID, '_method_ftp_protocol', $ftp_protocol );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_ftp_method_protocol' );
		$ftp_user = woo_ce_get_option( 'auto_ftp_method_user', false );
		update_post_meta( $post_ID, '_method_ftp_user', $ftp_user );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_ftp_method_user' );
		$ftp_pass = woo_ce_get_option( 'auto_ftp_method_pass', false );
		update_post_meta( $post_ID, '_method_ftp_pass', $ftp_pass );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_ftp_method_pass' );
		$ftp_path = woo_ce_get_option( 'auto_ftp_method_path', false );
		update_post_meta( $post_ID, '_method_ftp_path', $ftp_path );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_ftp_method_path' );
		$ftp_filename = woo_ce_get_option( 'auto_ftp_method_filename', false );
		update_post_meta( $post_ID, '_method_ftp_filename', $ftp_filename );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_ftp_method_filename' );
		$ftp_passive = woo_ce_get_option( 'auto_ftp_method_passive', false );
		update_post_meta( $post_ID, '_method_ftp_passive', $ftp_passive );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_ftp_method_passive' );
		$ftp_timeout = woo_ce_get_option( 'auto_ftp_method_timeout', false );
		update_post_meta( $post_ID, '_method_ftp_timeout', $ftp_timeout );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_ftp_method_timeout' );

		// Scheduling
		$auto_schedule = woo_ce_get_option( 'auto_schedule', false );
		if( $auto_schedule == false )
			$auto_schedule = 'monthly';
		update_post_meta( $post_ID, '_auto_schedule', $auto_schedule );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_schedule' );
		$auto_interval = woo_ce_get_option( 'auto_interval', false );
		update_post_meta( $post_ID, '_auto_interval', $auto_interval );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_interval' );
		$auto_commence = woo_ce_get_option( 'auto_commence', false );
		update_post_meta( $post_ID, '_auto_commence', $auto_commence );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_commence' );
		$auto_commence_date = woo_ce_get_option( 'auto_commence_date', false );
		update_post_meta( $post_ID, '_auto_commence_date', $auto_commence_date );
		delete_option( WOO_CD_PREFIX . '_' . 'auto_commence_date' );

		// Delete the legacy WP-CRON
		wp_clear_scheduled_hook( 'woo_ce_auto_export_schedule' );

		return true;

	} else {
		wp_delete_post( $post_ID, true );
	}

}

function woo_ce_register_export_template_cpt() {

	$labels = array(
		'name'               => __( 'Export Templates', 'woocommerce-exporter' ),
		'singular_name'      => __( 'Export Template', 'woocommerce-exporter' ),
		'add_new'            => __( 'Add Export Template', 'woocommerce-exporter' ),
		'add_new_item'       => __( 'Add New Export Template', 'woocommerce-exporter' ),
		'edit'               => __( 'Edit', 'woocommerce-exporter' ),
		'edit_item'          => __( 'Edit Export Template', 'woocommerce-exporter' ),
		'new_item'           => __( 'New Export Template', 'woocommerce-exporter' ),
		'view'               => __( 'View Export Template', 'woocommerce-exporter' ),
		'view_item'          => __( 'View Export Template', 'woocommerce-exporter' ),
		'search_items'       => __( 'Search Export Templates', 'woocommerce-exporter' ),
		'not_found'          => __( 'No Export Templates found', 'woocommerce-exporter' ),
		'not_found_in_trash' => __( 'No Export Templates found in trash', 'woocommerce-exporter' ),
		'parent'             => __( 'Parent Export Templates', 'woocommerce-exporter' ),
		'menu_name'          => _x( 'Export Templates', 'Export Template', 'woocommerce-exporter' )
	);

	$args = array(
		'labels'              => $labels,
		'description'         => __( 'This is where Export Templates for Store Exporter Deluxe are managed.', 'woocommerce-exporter' ),
		'public'              => false,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'show_in_menu'        => false,
		'query_var'           => true,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'has_archive'         => false,
		'hierarchical'        => false,
		'menu_position'       => null,
		'supports'            => array( 'title', 'excerpt' )
	);
	$post_type = 'export_template';
	register_post_type( $post_type, $args );

}

// Add Store Export to filter types on the WordPress Media screen
function woo_ce_add_post_mime_type( $post_mime_types = array() ) {

	$post_mime_types['text/csv'] = array( __( 'Store Exports (CSV)', 'woocommerce-exporter' ), __( 'Manage Store Exports (CSV)', 'woocommerce-exporter' ), _n_noop( 'Store Export - CSV <span class="count">(%s)</span>', 'Store Exports - CSV <span class="count">(%s)</span>' ) );
	$post_mime_types['application/vnd.ms-excel'] = array( __( 'Store Exports (Excel 2003)', 'woocommerce-exporter' ), __( 'Manage Store Exports (Excel 2003)', 'woocommerce-exporter' ), _n_noop( 'Store Export - Excel 2003 <span class="count">(%s)</span>', 'Store Exports - Excel 2003 <span class="count">(%s)</span>' ) );
	$post_mime_types['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'] = array( __( 'Store Exports (Excel 2007)', 'woocommerce-exporter' ), __( 'Manage Store Exports (Excel 2007)', 'woocommerce-exporter' ), _n_noop( 'Store Export - Excel 2007 <span class="count">(%s)</span>', 'Store Exports - Excel 2007 <span class="count">(%s)</span>' ) );
	$post_mime_types['application/xml'] = array( __( 'Store Exports (XML)', 'woocommerce-exporter' ), __( 'Manage Store Exports (XML)', 'woocommerce-exporter' ), _n_noop( 'Store Export - XML <span class="count">(%s)</span>', 'Store Exports - XML <span class="count">(%s)</span>' ) );
	$post_mime_types['application/rss+xml'] = array( __( 'Store Exports (RSS)', 'woocommerce-exporter' ), __( 'Manage Store Exports (RSS)', 'woocommerce-exporter' ), _n_noop( 'Store Export - RSS <span class="count">(%s)</span>', 'Store Exports - RSS <span class="count">(%s)</span>' ) );
	return $post_mime_types;

}
add_filter( 'post_mime_types', 'woo_ce_add_post_mime_type' );

function woo_ce_current_memory_usage() {

	$output = '';
	if( function_exists( 'memory_get_usage' ) )
		$output = round( memory_get_usage( true ) / 1024 / 1024, 2 );
	return $output;

}

function woo_ce_get_start_of_week_day() {

	global $wp_locale;

	$output = 'Monday';
	$start_of_week = get_option( 'start_of_week', 0 );
	for( $day_index = 0; $day_index <= 6; $day_index++ ) {
		if( $start_of_week == $day_index ) {
			$output = $wp_locale->get_weekday( $day_index );
			break;
		}
	}
	return $output;

}

// Provided by Pippin Williamson, mentioned on WP Beginner (http://www.wpbeginner.com/wp-tutorials/how-to-display-a-users-ip-address-in-wordpress/)
function woo_ce_get_visitor_ip_address() {

	if( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return apply_filters( 'woo_ce_get_visitor_ip_address', $ip );

}

function woo_ce_get_line_ending() {

	$output = PHP_EOL;
	$line_ending_formatting = woo_ce_get_option( 'line_ending_formatting', 'windows' );
	if( $line_ending_formatting == false || $line_ending_formatting == '' ) {
		woo_ce_error_log( sprintf( 'Warning: %s', __( 'Line ending formatting export option was corrupted, defaulted to windows', 'woocommerce-exporter' ) ) );
		$line_ending_formatting = 'windows';
		woo_ce_update_option( 'line_ending_formatting', 'windows' );
	}
	switch( $line_ending_formatting ) {

		case 'windows':
		default:
			$output = "\r\n";
			break;

		case 'mac':
			$output = "\r";
			break;

		case 'unix':
			$output = "\n";
			break;

	}
	$output = apply_filters( 'woo_ce_get_line_ending', $output, $line_ending_formatting );
	return $output;

}

// A list of allowed Image Embed fields
function woo_ce_get_image_embed_allowed_fields() {

	global $export;

	$fields = array(
		'image_embed', 
		'product_gallery_embed', 
		'order_items_image_embed'
	);
	return apply_filters( 'woo_ce_override_embed_allowed_fields', $fields, $export->type );

}

function woo_ce_wpml_get_language_name( $language_code = '' ){

	global $sitepress;

	$output = $language_code;
	if( method_exists( $sitepress, 'get_language_details' ) ) {
		$details = $sitepress->get_language_details( $language_code );
		if( !empty( $details ) )
			$output = $details['english_name'];
	}

	return $output;

}

// Return whether WPML exists
function woo_ce_detect_wpml() {

	if( defined( 'ICL_LANGUAGE_CODE' ) )
		return true;

}

// List of WordPress Plugins that Store Exporter integrates with
function woo_ce_modules_list( $module_status = false ) {

	$modules = array();
	$modules[] = array(
		'name' => 'aioseop',
		'title' => __( 'All in One SEO Pack', 'woocommerce-exporter' ),
		'description' => __( 'Optimize your WooCommerce Products for Search Engines. Requires Store Toolkit for All in One SEO Pack integration.', 'woocommerce-exporter' ),
		'url' => 'http://wordpress.org/extend/plugins/all-in-one-seo-pack/',
		'slug' => 'all-in-one-seo-pack',
		'function' => 'aioseop_activate'
	);
	$modules[] = array(
		'name' => 'store_toolkit',
		'title' => __( 'Store Toolkit', 'woocommerce-exporter' ),
		'description' => __( 'Store Toolkit includes a growing set of commonly-used WooCommerce administration tools aimed at web developers and store maintainers.', 'woocommerce-exporter' ),
		'url' => 'http://wordpress.org/extend/plugins/woocommerce-store-toolkit/',
		'slug' => 'woocommerce-store-toolkit',
		'function' => 'woo_st_admin_init'
	);
	$modules[] = array(
		'name' => 'ultimate_seo',
		'title' => __( 'SEO Ultimate', 'woocommerce-exporter' ),
		'description' => __( 'This all-in-one SEO plugin gives you control over Product details.', 'woocommerce-exporter' ),
		'url' => 'http://wordpress.org/extend/plugins/seo-ultimate/',
		'slug' => 'seo-ultimate',
		'function' => 'su_wp_incompat_notice'
	);
	$modules[] = array(
		'name' => 'gpf',
		'title' => __( 'Advanced Google Product Feed', 'woocommerce-exporter' ),
		'description' => __( 'Easily configure data to be added to your Google Merchant Centre feed.', 'woocommerce-exporter' ),
		'url' => 'http://www.leewillis.co.uk/wordpress-plugins/',
		'function' => 'woocommerce_gpf_install'
	);
	$modules[] = array(
		'name' => 'wpseo',
		'title' => __( 'Yoast SEO', 'woocommerce-exporter' ),
		'description' => __( 'The first true all-in-one SEO solution for WordPress. Formally WordPress SEO.', 'woocommerce-exporter' ),
		'url' => 'http://yoast.com/wordpress/seo/#utm_source=wpadmin&utm_medium=plugin&utm_campaign=wpseoplugin',
		'slug' => 'wordpress-seo',
		'function' => 'wpseo_admin_init'
	);
	$modules[] = array(
		'name' => 'wpseo_wc',
		'title' => __( 'Yoast SEO: WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'This extension to WooCommerce and WordPress SEO by Yoast makes sure there\'s perfect communication between the two plugins.', 'woocommerce-exporter' ),
		'url' => 'https://yoast.com/wordpress/plugins/yoast-woocommerce-seo/',
		'function' => 'initialize_yoast_woocommerce_seo'
	);
	$modules[] = array(
		'name' => 'wc_msrp',
		'title' => __( 'WooCommerce MSRP Pricing', 'woocommerce-exporter' ),
		'description' => __( 'Define and display MSRP prices (Manufacturer\'s suggested retail price) to your customers.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/msrp-pricing/',
		'function' => 'woocommerce_msrp_activate'
	);
	$modules[] = array(
		'name' => 'wc_brands',
		'title' => __( 'WooCommerce Brands Addon', 'woocommerce-exporter' ),
		'description' => __( 'Create, assign and list brands for products, and allow customers to filter by brand.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/brands/',
		'class' => 'WC_Brands'
	);
	$modules[] = array(
		'name' => 'wc_cog',
		'title' => __( 'Cost of Goods', 'woocommerce-exporter' ),
		'description' => __( 'Easily track total profit and cost of goods by adding a Cost of Good field to simple and variable products.', 'woocommerce-exporter' ),
		'url' => 'http://www.skyverge.com/product/woocommerce-cost-of-goods-tracking/',
		'class' => 'WC_COG'
	);
	$modules[] = array(
		'name' => 'per_product_shipping',
		'title' => __( 'Per Product Shipping', 'woocommerce-exporter' ),
		'description' => __( 'Define separate shipping costs per product which are combined at checkout to provide a total shipping cost.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/per-product-shipping/',
		'class' => 'WC_Shipping_Per_Product_Init'
	);
	$modules[] = array(
		'name' => 'vendors',
		'title' => __( 'Product Vendors', 'woocommerce-exporter' ),
		'description' => __( 'Turn your store into a multi-vendor marketplace (such as Etsy or Creative Market).', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/product-vendors/',
		'class' => 'WC_Product_Vendors'
	);
	$modules[] = array(
		'name' => 'wc_vendors',
		'title' => __( 'WC Vendors', 'woocommerce-exporter' ),
		'description' => __( 'Allow vendors to sell their own products and receive a commission for each sale.', 'woocommerce-exporter' ),
		'url' => 'http://wcvendors.com',
		'class' => 'WC_Vendors'
	);
	$modules[] = array(
		'name' => 'acf',
		'title' => __( 'Advanced Custom Fields', 'woocommerce-exporter' ),
		'description' => __( 'Powerful fields for WordPress developers.', 'woocommerce-exporter' ),
		'url' => 'http://www.advancedcustomfields.com',
		'class' => 'acf'
	);
	$modules[] = array(
		'name' => 'product_addons',
		'title' => __( 'Product Add-ons', 'woocommerce-exporter' ),
		'description' => __( 'Allow your customers to customise your products by adding input boxes, dropdowns or a field set of checkboxes.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/product-add-ons/',
		'class' => array( 'Product_Addon_Admin', 'Product_Addon_Display' )
	);
	$modules[] = array(
		'name' => 'seq',
		'title' => __( 'WooCommerce Sequential Order Numbers', 'woocommerce-exporter' ),
		'description' => __( 'This plugin extends the WooCommerce e-commerce plugin by setting sequential order numbers for new orders.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-sequential-order-numbers/',
		'slug' => 'woocommerce-sequential-order-numbers',
		'class' => 'WC_Seq_Order_Number'
	);
	$modules[] = array(
		'name' => 'seq_pro',
		'title' => __( 'WooCommerce Sequential Order Numbers Pro', 'woocommerce-exporter' ),
		'description' => __( 'Tame your WooCommerce Order Numbers.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/sequential-order-numbers-pro/',
		'class' => 'WC_Seq_Order_Number_Pro'
	);
	$modules[] = array(
		'name' => 'print_invoice_delivery_note',
		'title' => __( 'WooCommerce Print Invoice & Delivery Note', 'woocommerce-exporter' ),
		'description' => __( 'Print invoices and delivery notes for WooCommerce orders.', 'woocommerce-exporter' ),
		'url' => 'http://wordpress.org/plugins/woocommerce-delivery-notes/',
		'slug' => 'woocommerce-delivery-notes',
		'class' => 'WooCommerce_Delivery_Notes'
	);
	$modules[] = array(
		'name' => 'pdf_invoices_packing_slips',
		'title' => __( 'WooCommerce PDF Invoices & Packing Slips', 'woocommerce-exporter' ),
		'description' => __( 'Create, print & automatically email PDF invoices & packing slips for WooCommerce orders.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/',
		'slug' => 'woocommerce-pdf-invoices-packing-slips',
		'class' => 'WooCommerce_PDF_Invoices'
	);
	$modules[] = array(
		'name' => 'pdf_invoices_packing_slips_pro',
		'title' => __( 'WooCommerce PDF Invoices & Packing Slips Professional', 'woocommerce-exporter' ),
		'description' => __( 'Extended functionality for the WooCommerce PDF Invoices & Packing Slips Plugin.', 'woocommerce-exporter' ),
		'url' => 'https://wpovernight.com/downloads/woocommerce-pdf-invoices-packing-slips-professional/',
		'class' => 'WooCommerce_PDF_IPS_Pro'
	);
	$modules[] = array(
		'name' => 'checkout_manager',
		'title' => __( 'WooCommerce Checkout Manager & WooCommerce Checkout Manager Pro', 'woocommerce-exporter' ),
		'description' => __( 'Manages the WooCommerce Checkout page and WooCommerce Checkout processes.', 'woocommerce-exporter' ),
		'url' => 'http://wordpress.org/plugins/woocommerce-checkout-manager/',
		'slug' => 'woocommerce-checkout-manager',
		'function' => array( 'wccs_install', 'wooccm_install', 'wccs_install_pro' )
	);
	$modules[] = array(
		'name' => 'wc_pgsk',
		'title' => __( 'Poor Guys Swiss Knife', 'woocommerce-exporter' ),
		'description' => __( 'A Swiss Knife for WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'http://wordpress.org/plugins/woocommerce-poor-guys-swiss-knife/',
		'slug' => 'woocommerce-poor-guys-swiss-knife',
		'function' => 'wcpgsk_init'
	);
	$modules[] = array(
		'name' => 'checkout_field_editor',
		'title' => __( 'Checkout Field Editor', 'woocommerce-exporter' ),
		'description' => __( 'Add, edit and remove fields shown on your WooCommerce checkout page.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/woocommerce-checkout-field-editor/',
		'function' => 'woocommerce_init_checkout_field_editor'
	);
	$modules[] = array(
		'name' => 'checkout_field_editor_pro',
		'title' => __( 'Checkout Field Editor Pro', 'woocommerce-exporter' ),
		'description' => __( 'Design woocommerce checkout form in your own way, customize checkout fields (Add, Edit, Delete and re arrange fields).', 'woocommerce-exporter' ),
		'url' => 'https://www.themehigh.com/product/woocommerce-checkout-field-editor-pro/',
		'class' => 'WCFE_Checkout_Field_Editor'
	);
	$modules[] = array(
		'name' => 'checkout_field_manager',
		'title' => __( 'Checkout Field Manager', 'woocommerce-exporter' ),
		'description' => __( 'Quickly and effortlessly add, remove and re-orders fields in the checkout process.', 'woocommerce-exporter' ),
		'url' => 'http://61extensions.com/shop/woocommerce-checkout-field-manager/',
		'function' => 'sod_woocommerce_checkout_manager_settings'
	);
	$modules[] = array(
		'name' => 'checkout_addons',
		'title' => __( 'WooCommerce Checkout Add-Ons', 'woocommerce-exporter' ),
		'description' => __( 'Add fields at checkout for add-on products and services while optionally setting a cost for each add-on.', 'woocommerce-exporter' ),
		'url' => 'http://www.skyverge.com/product/woocommerce-checkout-add-ons/',
		'function' => 'init_woocommerce_checkout_add_ons'
	);
	$modules[] = array(
		'name' => 'local_pickup_plus',
		'title' => __( 'Local Pickup Plus', 'woocommerce-exporter' ),
		'description' => __( 'Let customers pick up products from specific locations.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/local-pickup-plus/',
		'class' => 'WC_Local_Pickup_Plus'
	);
	$modules[] = array(
		'name' => 'gravity_forms',
		'title' => __( 'Gravity Forms', 'woocommerce-exporter' ),
		'description' => __( 'Gravity Forms is hands down the best contact form plugin for WordPress powered websites.', 'woocommerce-exporter' ),
		'url' => 'http://www.gravityforms.com/',
		'class' => 'RGForms'
	);
	$modules[] = array(
		'name' => 'woocommerce_gravity_forms',
		'title' => __( 'WooCommerce Gravity Forms Product Add-Ons', 'woocommerce-exporter' ),
		'description' => __( 'Allows you to use Gravity Forms on individual WooCommerce products.', 'woocommerce-exporter' ),
		'url' => 'https://www.woothemes.com/products/gravity-forms-add-ons/',
		'class' => array( 'woocommerce_gravityforms', 'WC_GFPA_Main' )
	);
	$modules[] = array(
		'name' => 'currency_switcher',
		'title' => __( 'WooCommerce Currency Switcher', 'woocommerce-exporter' ),
		'description' => __( 'Currency Switcher for WooCommerce allows your shop to display prices and accept payments in multiple currencies.', 'woocommerce-exporter' ),
		'url' => 'http://aelia.co/shop/currency-switcher-woocommerce/',
		'class' => 'WC_Aelia_CurrencySwitcher'
	);
	$modules[] = array(
		'name' => 'subscriptions',
		'title' => __( 'WooCommerce Subscriptions', 'woocommerce-exporter' ),
		'description' => __( 'WC Subscriptions makes it easy to create and manage products with recurring payments.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/woocommerce-subscriptions/',
		'class' => array( 'WC_Subscriptions', 'WC_Subscriptions_Manager' )
	);
	$modules[] = array(
		'name' => 'extra_product_options',
		'title' => __( 'WooCommerce Extra Product Options', 'woocommerce-exporter' ),
		'description' => __( 'Create extra price fields globally or per-Product', 'woocommerce-exporter' ),
		'url' => 'http://codecanyon.net/item/woocommerce-extra-product-options/7908619',
		'class' => 'TM_Extra_Product_Options'
	);
	$modules[] = array(
		'name' => 'woocommerce_jetpack',
		'title' => __( 'Booster for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Supercharge your WooCommerce site with these awesome powerful features (formally WooCommerce Jetpack).', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-jetpack/',
		'slug' => 'woocommerce-jetpack',
		'class' => 'WC_Jetpack'
	);
	$modules[] = array(
		'name' => 'woocommerce_jetpack_plus',
		'title' => __( 'Booster Plus', 'woocommerce-exporter' ),
		'description' => __( 'Unlock all WooCommerce Booster features and supercharge your WordPress WooCommerce site even more (formally WooCommerce Jetpack Plus).', 'woocommerce-exporter' ),
		'url' => 'http://woojetpack.com/shop/wordpress-woocommerce-jetpack-plus/',
		'class' => 'WC_Jetpack_Plus'
	);
	$modules[] = array(
		'name' => 'woocommerce_brands',
		'title' => __( 'WooCommerce Brands', 'woocommerce-exporter' ),
		'description' => __( 'Woocommerce Brands Plugin. After Install and active this plugin you\'ll have some shortcode and some widget for display your brands in fornt-end website.', 'woocommerce-exporter' ),
		'url' => 'http://proword.net/Woocommerce_Brands/',
		'class' => 'woo_brands'
	);
	$modules[] = array(
		'name' => 'woocommerce_bookings',
		'title' => __( 'WooCommerce Bookings', 'woocommerce-exporter' ),
		'description' => __( 'Setup bookable products such as for reservations, services and hires.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/woocommerce-bookings/',
		'class' => 'WC_Bookings'
	);
	$modules[] = array(
		'name' => 'eu_vat',
		'title' => __( 'WooCommerce EU VAT Number', 'woocommerce-exporter' ),
		'description' => __( 'The EU VAT Number extension lets you collect and validate EU VAT numbers during checkout to identify B2B transactions verses B2C.', 'woocommerce-exporter' ),
		'url' => 'https://www.woothemes.com/products/eu-vat-number/',
		'function' => '__wc_eu_vat_number_init'
	);
	$modules[] = array(
		'name' => 'aelia_eu_vat',
		'title' => __( 'WooCommerce EU VAT Assistant', 'woocommerce-exporter' ),
		'description' => __( 'Assists with EU VAT compliance, for the new VAT regime beginning 1st January 2015.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-eu-vat-assistant/',
		'slug' => 'woocommerce-eu-vat-assistant',
		'class' => 'Aelia_WC_RequirementsChecks'
	);
	$modules[] = array(
		'name' => 'hear_about_us',
		'title' => __( 'WooCommerce Hear About Us', 'woocommerce-exporter' ),
		'description' => __( 'Ask where your new customers come from at Checkout.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-hear-about-us/',
		'slug' => 'woocommerce-hear-about-us', // Define this if the Plugin is hosted on the WordPress repo
		'class' => 'WooCommerce_HearAboutUs'
	);
	$modules[] = array(
		'name' => 'wholesale_pricing',
		'title' => __( 'WooCommerce Wholesale Pricing', 'woocommerce-exporter' ),
		'description' => __( 'Allows you to set wholesale prices for products and variations.', 'woocommerce-exporter' ),
		'url' => 'http://ignitewoo.com/woocommerce-extensions-plugins-themes/woocommerce-wholesale-pricing/',
		'class' => 'woocommerce_wholesale_pricing'
	);
	$modules[] = array(
		'name' => 'wc_barcodes',
		'title' => __( 'Barcodes for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Allows you to add GTIN (former EAN) codes natively to your products.', 'woocommerce-exporter' ),
		'url' => 'http://www.wolkenkraft.com/produkte/barcodes-fuer-woocommerce/',
		'function' => 'wpps_requirements_met'
	);
	$modules[] = array(
		'name' => 'wc_smart_coupons',
		'title' => __( 'WooCommerce Smart Coupons', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Smart Coupons lets customers buy gift certificates, store credits or coupons easily.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/smart-coupons/',
		'class' => 'WC_Smart_Coupons'
	);
	$modules[] = array(
		'name' => 'wc_preorders',
		'title' => __( 'WooCommerce Pre-Orders', 'woocommerce-exporter' ),
		'description' => __( 'Sell pre-orders for products in your WooCommerce store.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/woocommerce-pre-orders/',
		'class' => 'WC_Pre_Orders'
	);
	$modules[] = array(
		'name' => 'order_numbers_basic',
		'title' => __( 'WooCommerce Basic Ordernumbers', 'woocommerce-exporter' ),
		'description' => __( 'Lets the user freely configure the order numbers in WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'http://open-tools.net/woocommerce/advanced-ordernumbers-for-woocommerce.html',
		'class' => 'OpenToolsOrdernumbersBasic'
	);
	$modules[] = array(
		'name' => 'admin_custom_order_fields',
		'title' => __( 'WooCommerce Admin Custom Order Fields', 'woocommerce-exporter' ),
		'description' => __( 'Easily add custom fields to your WooCommerce orders and display them in the Orders admin, the My Orders section and order emails.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/woocommerce-admin-custom-order-fields/',
		'function' => 'init_woocommerce_admin_custom_order_fields'
	);
	$modules[] = array(
		'name' => 'table_rate_shipping_plus',
		'title' => __( 'WooCommerce Table Rate Shipping Plus', 'woocommerce-exporter' ),
		'description' => __( 'Calculate shipping costs based on destination, weight and price.', 'woocommerce-exporter' ),
		'url' => 'http://mangohour.com/plugins/woocommerce-table-rate-shipping',
		'function' => 'mh_wc_table_rate_plus_init'
	);
	$modules[] = array(
		'name' => 'wc_extra_checkout_fields_brazil',
		'title' => __( 'WooCommerce Extra Checkout Fields for Brazil', 'woocommerce-exporter' ),
		'description' => __( 'Adds Brazilian checkout fields in WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/',
		'slug' => 'woocommerce-extra-checkout-fields-for-brazil',
		'class' => 'Extra_Checkout_Fields_For_Brazil'
	);
	$modules[] = array(
		'name' => 'wc_quickdonation',
		'title' => __( 'WooCommerce Quick Donation', 'woocommerce-exporter' ),
		'description' => __( 'Turns WooCommerce into online donation.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-quick-donation/',
		'slug' => 'woocommerce-quick-donation',
		'class' => 'WooCommerce_Quick_Donation'
	);
	$modules[] = array(
		'name' => 'wc_easycheckout',
		'title' => __( 'Easy Checkout Fields Editor', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Easy Checkout Fields Editor.', 'woocommerce-exporter' ),
		'url' => 'http://codecanyon.net/item/woocommerce-easy-checkout-field-editor/9799777',
		'function' => 'pcmfe_admin_form_field'
	);
	$modules[] = array(
		'name' => 'wc_productfees',
		'title' => __( 'Product Fees', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Product Fees allows you to add additional fees at checkout based on products that are in the cart.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-product-fees/',
		'slug' => 'woocommerce-product-fees',
		'class' => 'WooCommerce_Product_Fees'
	);
	$modules[] = array(
		'name' => 'wc_events',
		'title' => __( 'WooCommerce Events', 'woocommerce-exporter' ),
		'description' => __( 'Adds event and ticketing features to WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'http://www.woocommerceevents.com/',
		'class' => 'WooCommerce_Events'
	);
	$modules[] = array(
		'name' => 'wc_tabmanager',
		'title' => __( 'WooCommerce Tab Manager', 'woocommerce-exporter' ),
		'description' => __( 'A product tab manager for WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/woocommerce-tab-manager/',
		'class' => 'WC_Tab_Manager'
	);
	$modules[] = array(
		'name' => 'wc_customfields',
		'title' => __( 'WooCommerce Custom Fields', 'woocommerce-exporter' ),
		'description' => __( 'Create custom fields for WooCommerce product, checkout, order and customer pages.', 'woocommerce-exporter' ),
		'url' => 'http://www.rightpress.net/woocommerce-custom-fields',
		'class' => array( 'RP_WCCF', 'WCCF' )
	);
	$modules[] = array(
		'name' => 'barcode_isbn',
		'title' => __( 'WooCommerce Barcode & ISBN', 'woocommerce-exporter' ),
		'description' => __( 'A plugin to add a barcode & ISBN to WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-barcode-isbn/',
		'slug' => 'woocommerce-barcode-isbn',
		'function' => 'woo_add_barcode'
	);
	$modules[] = array(
		'name' => 'video_product_tab',
		'title' => __( 'WooCommerce Video Product Tab', 'woocommerce-exporter' ),
		'description' => __( 'Extends WooCommerce to allow you to add a Video to the Product page.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-video-product-tab/',
		'slug' => 'woocommerce-video-product-tab',
		'class' => 'WooCommerce_Video_Product_Tab'
	);
	$modules[] = array(
		'name' => 'external_featured_image',
		'title' => __( 'Nelio External Featured Image', 'woocommerce-exporter' ),
		'description' => __( 'Use external images from anywhere as the featured image of your pages and posts.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/external-featured-image/',
		'slug' => 'external-featured-image', // Define this if the Plugin is hosted on the WordPress repo
		'function' => '_nelioefi_url'
	);
	$modules[] = array(
		'name' => 'variation_swatches_photos',
		'title' => __( 'WooCommerce Variation Swatches and Photos', 'woocommerce-exporter' ),
		'description' => __( 'Configure colors and photos for shoppers on your site to use when picking variations.', 'woocommerce-exporter' ),
		'url' => 'https://www.woothemes.com/products/variation-swatches-and-photos/',
		'class' => 'WC_SwatchesPlugin'
	);
	$modules[] = array(
		'name' => 'wc_uploads',
		'title' => __( 'WooCommerce Uploads', 'woocommerce-exporter' ),
		'description' => __( 'Upload files in WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'https://wpfortune.com/shop/plugins/woocommerce-uploads/',
		'class' => 'WPF_Uploads'
	);
	$modules[] = array(
		'name' => 'wc_posr',
		'title' => __( 'WooCommerce Profit of Sales Report', 'woocommerce-exporter' ),
		'description' => __( 'This plugin provides Profit of Sales Report based on Cost of Goods.', 'woocommerce-exporter' ),
		'url' => 'http://codecanyon.net/item/woocommerce-profit-of-sales-report/9190590',
		'function' => 'POSRFront'
	);
	$modules[] = array(
		'name' => 'orddd_free',
		'title' => __( 'Order Delivery Date for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Allow the customers to choose an order delivery date on the checkout page for WooCommerce store owners.', 'woocommerce-exporter' ),
		'slug' => 'order-delivery-date-for-woocommerce',
		'url' => 'https://wordpress.org/plugins/order-delivery-date-for-woocommerce/',
		'class' => 'order_delivery_date_lite'
	);
	$modules[] = array(
		'name' => 'orddd',
		'title' => __( 'Order Delivery Date Pro for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Allows customers to choose their preferred Order Delivery Date & Delivery Time during checkout.', 'woocommerce-exporter' ),
		'url' => 'https://www.tychesoftwares.com/store/premium-plugins/order-delivery-date-for-woocommerce-pro-21/',
		'class' => 'order_delivery_date'
	);
	$modules[] = array(
		'name' => 'wc_eu_vat_compliance',
		'title' => __( 'WooCommerce EU VAT Compliance', 'woocommerce-exporter' ),
		'description' => __( 'Provides features to assist WooCommerce with EU VAT compliance.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-eu-vat-compliance/',
		'slug' => 'woocommerce-eu-vat-compliance',
		'class' => 'WC_EU_VAT_Compliance'
	);
	$modules[] = array(
		'name' => 'wc_eu_vat_compliance_pro',
		'title' => __( 'WooCommerce EU VAT Compliance (Premium)', 'woocommerce-exporter' ),
		'description' => __( 'Provides features to assist WooCommerce with EU VAT compliance.', 'woocommerce-exporter' ),
		'url' => 'https://www.simbahosting.co.uk/s3/product/woocommerce-eu-vat-compliance/',
		'slug' => 'woocommerce-eu-vat-compliance',
		'class' => 'WC_EU_VAT_Compliance_Premium'
	);
	$modules[] = array(
		'name' => 'yith_cm',
		'title' => __( 'YITH WooCommerce Checkout Manager', 'woocommerce-exporter' ),
		'description' => __( 'YITH WooCommerce Checkout Manager lets you add, edit or remove checkout fields.', 'woocommerce-exporter' ),
		'url' => 'https://yithemes.com/themes/plugins/yith-woocommerce-checkout-manager/',
		'function' => 'ywccp_init'
	);
	$modules[] = array(
		'name' => 'vt_dp',
		'title' => __( 'Discontinued Product for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Adds the ability to flag a product as discontinued to WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/discontinued-product-for-woocommerce/',
		'slug' => 'discontinued-product-for-woocommerce',
		'function' => 'discontinued_product_for_woocommerce_init'
	);
	$modules[] = array(
		'name' => 'yith_vendor',
		'title' => __( 'YITH WooCommerce Multi Vendor Premium', 'woocommerce-exporter' ),
		'description' => __( 'Switch your website into a platform hosting more than one shop.', 'woocommerce-exporter' ),
		'url' => 'http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/',
		'function' => 'YITH_Vendors'
	);

	$modules[] = array(
		'name' => 'wc_memberships',
		'title' => __( 'WooCommerce Memberships', 'woocommerce-exporter' ),
		'description' => __( 'Sell memberships that provide access to restricted content, products, discounts, and more!', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/woocommerce-memberships/',
		'function' => 'init_woocommerce_memberships'
	);

	$modules[] = array(
		'name' => 'wc_product_bundles',
		'title' => __( 'WooCommerce Product Bundles', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce extension for creating simple product bundles, kits and assemblies.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/product-bundles/',
		'class' => 'WC_Bundles'
	);
	$modules[] = array(
		'name' => 'wc_min_max',
		'title' => __( 'WooCommerce Min/Max Quantities', 'woocommerce-exporter' ),
		'description' => __( 'Lets you define minimum/maximum allowed quantities for products, variations and orders.', 'woocommerce-exporter' ),
		'url' => 'https://woocommerce.com/products/minmax-quantities/',
		'class' => 'WC_Min_Max_Quantities'
	);
	$modules[] = array(
		'name' => 'wc_followupemails',
		'title' => __( 'WooCommerce Follow Ups', 'woocommerce-exporter' ),
		'description' => __( 'Follow-Ups makes it easy to automate communications via email and Twitter to keep your customers engaged and spending money.', 'woocommerce-exporter' ),
		'url' => 'https://woocommerce.com/products/follow-up-emails/',
		'class' => 'FollowUpEmails'
	);
	$modules[] = array(
		'name' => 'wc_ship_multiple',
		'title' => __( 'Ship to Multiple Addresses', 'woocommerce-exporter' ),
		'description' => __( 'Allow your customers to ship individual items in a single order to multiple addresses.', 'woocommerce-exporter' ),
		'url' => 'https://woocommerce.com/products/shipping-multiple-addresses/',
		'class' => 'WC_Ship_Multiple'
	);
	$modules[] = array(
		'name' => 'awebooking',
		'title' => __( 'AweBooking', 'woocommerce-exporter' ),
		'description' => __( 'You can easily create a booking/reservation system into your WordPress website without any hassle', 'woocommerce-exporter' ),
		'url' => 'https://codecanyon.net/item/awebooking-online-hotel-booking-for-wordpress/12323878',
		'function' => 'awebooking_clean_room'
	);
	$modules[] = array(
		'name' => 'yith_delivery_pro',
		'title' => __( 'YITH WooCommerce Delivery Date Premium', 'woocommerce-exporter' ),
		'description' => __( 'Let your customers choose a delivery date for their orders', 'woocommerce-exporter' ),
		'url' => 'http://yithemes.com/themes/plugins/yith-woocommerce-delivery-date/',
		'function' => 'yith_delivery_date_init_plugin'
	);
	$modules[] = array(
		'name' => 'yith_brands_pro',
		'title' => __( 'YITH WooCommerce Brands Add-On', 'woocommerce-exporter' ),
		'description' => __( 'YITH WooCommerce Brands Add-on allows you to add brands functionality to the default WooCommerce Plugin.', 'woocommerce-exporter' ),
		'url' => 'http://yithemes.com/themes/plugins/yith-woocommerce-brands-add-on/',
		'function' => 'yith_brands_constructor'
	);
	$modules[] = array(
		'name' => 'ign_tiered',
		'title' => __( 'WooCommerce Tiered Pricing', 'woocommerce-exporter' ),
		'description' => __( 'Allows you to set price tiers for products and variations based on user roles.', 'woocommerce-exporter' ),
		'url' => 'http://ignitewoo.com/woocommerce-extensions-plugins-themes/woocommerce-tiered-pricing/',
		'function' => 'ign_tiered_init'
	);
	$modules[] = array(
		'name' => 'wc_books',
		'title' => __( 'WooCommerce BookStore', 'woocommerce-exporter' ),
		'description' => __( 'Convert your WooCommerce store to online book store. Sell books using WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'http://www.wpini.com/woocommerce-bookstore-plugin/',
		'function' => 'woo_bookstore_init'
	);
	$modules[] = array(
		'name' => 'wc_point_of_sales',
		'title' => __( 'WooCommerce Point of Sale', 'woocommerce-exporter' ),
		'description' => __( 'Extend your online WooCommerce store by adding a brick and mortar Point of Sale (POS) interface.', 'woocommerce-exporter' ),
		'url' => 'https://codecanyon.net/item/woocommerce-point-of-sale-pos/7869665',
		'function' => 'WC_POS'
	);
	$modules[] = array(
		'name' => 'wc_pdf_product_vouchers',
		'title' => __( 'WooCommerce PDF Product Vouchers', 'woocommerce-exporter' ),
		'description' => __( 'Customize and sell PDF product vouchers with WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'http://www.woothemes.com/products/pdf-product-vouchers/',
		'function' => 'init_woocommerce_pdf_product_vouchers'
	);
	$modules[] = array(
		'name' => 'wpml',
		'title' => __( 'WPML Multilingual CMS', 'woocommerce-exporter' ),
		'description' => __( 'WPML Multilingual CMS.', 'woocommerce-exporter' ),
		'url' => 'https://wpml.org/',
		'function' => 'icl_sitepress_activate'
	);
	$modules[] = array(
		'name' => 'wpml_wc',
		'title' => __( 'WooCommerce Multilingual', 'woocommerce-exporter' ),
		'description' => __( 'Allows running fully multilingual e-Commerce sites with WooCommerce and WPML.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-multilingual/',
		'slug' => 'woocommerce-multilingual',
		'class' => 'woocommerce_wpml'
	);
	$modules[] = array(
		'name' => 'wootabs',
		'title' => __( 'WooTabs', 'woocommerce-exporter' ),
		'description' => __( 'WooTabs allows you to add extra tabs (as many as you want) to the WooCommerce Product Details page.', 'woocommerce-exporter' ),
		'url' => 'https://codecanyon.net/item/wootabsadd-extra-tabs-to-woocommerce-product-page/7891253',
		'function' => 'on_woocommerce_wootabs_loaded'
	);
	$modules[] = array(
		'name' => 'wc_ean',
		'title' => __( 'WooCommerce EAN Payment Gateway', 'woocommerce-exporter' ),
		'description' => __( 'This plugin adds an EAN13 Payment Gateway for WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'http://plugins.yanco.dk/woocommerce-ean-payment-gateway',
		'function' => 'WOOCMMERCE_EAN_PAYMENT_GATEWAY'
	);
	$modules[] = array(
		'name' => 'wc_germanized',
		'title' => __( 'WooCommerce Germanized', 'woocommerce-exporter' ),
		'description' => __( 'Extends WooCommerce to become a legally compliant store for the German market.', 'woocommerce-exporter' ),
		'url' => 'https://www.vendidero.de/woocommerce-germanized',
		'function' => 'WC_germanized'
	);
	$modules[] = array(
		'name' => 'wc_germanized_pro',
		'title' => __( 'WooCommerce Germanized Pro', 'woocommerce-exporter' ),
		'description' => __( 'Extends WooCommerce Germanized with professional features such as PDF invoices, legal text generators and many more.', 'woocommerce-exporter' ),
		'url' => 'https://www.vendidero.de/woocommerce-germanized',
		'function' => 'WC_germanized_pro'
	);
	$modules[] = array(
		'name' => 'wc_umcs',
		'title' => __( 'WooCommerce Ultimate Multi Currency Suite', 'woocommerce-exporter' ),
		'description' => __( 'Multi currency e-commerce plugin for WordPress-WooCommerce systems.', 'woocommerce-exporter' ),
		'url' => 'https://codecanyon.net/item/woocommerce-ultimate-multi-currency-suite/11997014',
		'class' => 'WooCommerce_Ultimate_Multi_Currency_Suite_Main'
	);
	$modules[] = array(
		'name' => 'wc_entrada',
		'title' => __( 'Entrada', 'woocommerce-exporter' ),
		'description' => __( 'Declares a plugin that will create custom taxonomy to WooCommerce Products.', 'woocommerce-exporter' ),
		'url' => 'https://themeforest.net/item/tour-booking-adventure-tour-wordpress-theme-entrada/16867379',
		'function' => 'entrada_create_product_taxnomy'
	);
	$modules[] = array(
		'name' => 'wc_deliveryslots',
		'title' => __( 'WooCommerce Delivery Slots', 'woocommerce-exporter' ),
		'description' => __( 'Allow your customers to select a delivery slot for their order.', 'woocommerce-exporter' ),
		'url' => 'https://iconicwp.com/products/woocommerce-delivery-slots/',
		'class' => 'jckWooDeliverySlots'
	);
	$modules[] = array(
		'name' => 'wc_products_purchase_price',
		'title' => __( 'Products Purchase Price for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Plug-in for WooCommerce that allows you to insert the cost (or purchase price) of your products!', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/products-purchase-price-for-woocommerce/',
		'slug' => 'products-purchase-price-for-woocommerce',
		'function' => 'product_purchase_price_admin_scripts'
	);
	$modules[] = array(
		'name' => 'wc_product_custom_options',
		'title' => __( 'WooCommerce Product Custom Options Lite', 'woocommerce-exporter' ),
		'description' => __( 'Give your Ecommerce website the space to add customized options for your products.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-custom-options-lite/',
		'slug' => 'woocommerce-custom-options-lite',
		'class' => 'Product_Custom_Options'
	);
	$modules[] = array(
		'name' => 'wc_wholesale_prices',
		'title' => __( 'WooCommerce Wholesale Prices', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Extension to provide Wholesale Prices functionality.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-wholesale-prices/',
		'slug' => 'woocommerce-wholesale-prices',
		'function' => 'wwp_global_plugin_deactivate'
	);
	$modules[] = array(
		'name' => 'wc_show_single_variations',
		'title' => __( 'WooCommerce Show Single Variations', 'woocommerce-exporter' ),
		'description' => __( 'Show product variations in the main product loops.', 'woocommerce-exporter' ),
		'url' => 'https://codecanyon.net/item/woocommerce-show-single-variations/13523915',
		'class' => 'JCK_WSSV'
	);
	$modules[] = array(
		'name' => 'wc_deposits',
		'title' => __( 'WooCommerce Deposits', 'woocommerce-exporter' ),
		'description' => __( 'Adds deposits support to WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'https://woocommerce.com/products/woocommerce-deposits/',
		'class' => 'WC_Deposits'
	);
	$modules[] = array(
		'name' => 'wc_unitofmeasure',
		'title' => __( 'WooCommerce Unit of Measure', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Unit Of Measure allows the user to add a unit of measure after the price on WooCommerce products.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-unit-of-measure/',
		'slug' => 'woocommerce-unit-of-measure',
		'class' => 'Woo_UOM'
	);
	$modules[] = array(
		'name' => 'wc_easybooking',
		'title' => __( 'WooCommerce Easy Bookings', 'woocommerce-exporter' ),
		'description' => __( 'Easily rent or book your products with WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-easy-booking-system/',
		'slug' => 'woocommerce-easy-booking-system',
		'class' => 'Easy_booking'
	);
	$modules[] = array(
		'name' => 'wc_advanced_quantities',
		'title' => __( 'WooCommerce Advanced Product Quantities', 'woocommerce-exporter' ),
		'description' => __( 'Easily require your customers to buy a minimum/maximum/incremental amount of products to continue with their Checkout.', 'woocommerce-exporter' ),
		'url' => 'http://www.wpbackoffice.com/plugins/woocommerce-incremental-product-quantities/',
		'function' => 'IPQ'
	);
	$modules[] = array(
		'name' => 'wc_chained_products',
		'title' => __( 'WooCommerce Chained Products', 'woocommerce-exporter' ),
		'description' => __( 'Create discounted product bundles and combo packs and boost your sales. Automatically add linked / related products to order.', 'woocommerce-exporter' ),
		'url' => 'https://woocommerce.com/products/chained-products/',
		'class' => 'WC_Chained_Products'
	);
	$modules[] = array(
		'name' => 'wc_sample',
		'title' => __( 'WooCommerce Sample', 'woocommerce-exporter' ),
		'description' => __( 'Include Get Sample Button in products of your online store.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-sample/',
		'slug' => 'woocommerce-sample',
		'class' => 'WooCommerce_Sample'
	);
	$modules[] = array(
		'name' => 'wc_product_importer_deluxe',
		'title' => __( 'WooCommerce Product Importer Deluxe', 'woocommerce-exporter' ),
		'description' => __( 'Bulk import hundreds, even thousands of linked Products and Product images into your WooCommerce store.', 'woocommerce-exporter' ),
		'url' => 'https://www.visser.com.au/plugins/product-importer-deluxe/',
		'function' => 'woo_pd_i18n'
	);
	$modules[] = array(
		'name' => 'wc_ag_barcode_pro',
		'title' => __( 'AG WooCommerce Barcode / ISBN & Amazon ASIN - PRO', 'woocommerce-exporter' ),
		'description' => __( 'A plugin to add a barcode, ISBN & Amazon ASIN fields to WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'https://www.weareag.co.uk/product/woocommerce-barcodeisbn-amazon-asin-pro/',
		'function' => 'AGD_load_updater'
	);
	$modules[] = array(
		'name' => 'wc_nm_personalizedproduct',
		'title' => __( 'N-Media WooCommerce Personalized Product Meta Manager', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Personalized Product Option Plugin allows site admin to add unlimited input fields on product page.', 'woocommerce-exporter' ),
		'url' => 'http://najeebmedia.com/wordpress-plugin/woocommerce-personalized-product-option/',
		'class' => 'NM_PersonalizedProduct'
	);
	$modules[] = array(
		'name' => 'wc_appointments',
		'title' => __( 'WooCommerce Appointments', 'woocommerce-exporter' ),
		'description' => __( 'Setup appointable products for WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'http://www.bizzthemes.com/plugins/woocommerce-appointments/',
		'class' => 'WC_Appointments'
	);
	$modules[] = array(
		'name' => 'seo_squirrly',
		'title' => __( 'SEO Squirrly', 'woocommerce-exporter' ),
		'description' => __( 'SEO Squirrly is for the NON-SEO experts. Get Excellent SEO with Better Content, Ranking and Analytics. For Both Humans and Search Bots.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/squirrly-seo/',
		'slug' => 'squirrly-seo',
		'function' => 'sq_phpError'
	);
	$modules[] = array(
		'name' => 'tickera',
		'title' => __( 'Tickera', 'woocommerce-exporter' ),
		'description' => __( 'Simple event ticketing system.', 'woocommerce-exporter' ),
		'url' => 'https://tickera.com/',
		'slug' => 'tickera',
		'class' => 'TC'
	);
	$modules[] = array(
		'name' => 'wc_measurement_price_calc',
		'title' => __( 'WooCommerce Measurement Price Calculator', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce plugin to provide price and quantity calculations based on product measurements.', 'woocommerce-exporter' ),
		'url' => 'http://www.woocommerce.com/products/measurement-price-calculator/',
		'function' => 'init_woocommerce_measurement_price_calculator'
	);
	$modules[] = array(
		'name' => 'wc_stripe',
		'title' => __( 'WooCommerce Stripe Payment Gateway', 'woocommerce-exporter' ),
		'description' => __( 'Take credit card payments on your store using Stripe.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/woocommerce-gateway-stripe/',
		'slug' => 'woocommerce-gateway-stripe',
		'class' => 'WC_Stripe'
	);
	$modules[] = array(
		'name' => 'yith_ywpi',
		'title' => __( 'YITH WooCommerce PDF Invoice and Shipping List', 'woocommerce-exporter' ),
		'description' => __( 'Generate PDF invoices for WooCommerce orders. Set manual or automatic invoice generation and shipping list document.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/yith-woocommerce-pdf-invoice/',
		'slug' => 'yith-woocommerce-pdf-invoice',
		'function' => 'yith_ywpi_init'
	);
	$modules[] = array(
		'name' => 'alg_con',
		'title' => __( 'Custom Order Numbers for WooCommerce', 'woocommerce-exporter' ),
		'description' => __( 'Custom order numbers for WooCommerce.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/custom-order-numbers-for-woocommerce/',
		'slug' => 'custom-order-numbers-for-woocommerce',
		'class' => 'Alg_WC_Custom_Order_Numbers'
	);
	$modules[] = array(
		'name' => 'ign_gift_certs',
		'title' => __( 'WooCommerce Gift Certificates Pro', 'woocommerce-exporter' ),
		'description' => __( 'WooCommerce Gift Certificates Pro allows you to sell gift certificates / store credits / coupon codes as products in your store.', 'woocommerce-exporter' ),
		'url' => 'https://ignitewoo.com/woocommerce-extensions-plugins-themes/woocommerce-gift-certificates-pro/',
		'class' => 'Ignite_Gift_Certs'
	);
	$modules[] = array(
		'name' => 'wc_fields_factory',
		'title' => __( 'WC Fields Factory', 'woocommerce-exporter' ),
		'description' => __( 'Sell your products with customized, personalised options. Add custom fields or fields group to your products, your admin screens and customize everything.', 'woocommerce-exporter' ),
		'url' => 'https://wordpress.org/plugins/wc-fields-factory/',
		'slug' => 'wc-fields-factory',
		'class' => 'wcff'
	);

	// Ship to Multiple Addresses - WC_Ship_Multiple

/*
	$modules[] = array(
		'name' => '',
		'title' => __( '', 'woocommerce-exporter' ),
		'description' => __( '', 'woocommerce-exporter' ),
		'url' => '',
		'slug' => '', // Define this if the Plugin is hosted on the WordPress repo
		'function' => '' // Define this for function detection, if Class rename attribute to class
	);
*/

	$modules = apply_filters( 'woo_ce_modules_addons', $modules );

	// Check if the existing Transient exists
	$modules_all = count( $modules );
	$cached = get_transient( WOO_CD_PREFIX . '_modules_all_count' );
	if( $cached == false ) {
		set_transient( WOO_CD_PREFIX . '_modules_all_count', $modules_all, DAY_IN_SECONDS );
	}

	$modules_active = 0;
	$modules_inactive = 0;

	if( !empty( $modules ) ) {
		$user_capability = 'install_plugins';
		foreach( $modules as $key => $module ) {
			$modules[$key]['status'] = 'inactive';
			// Check if each module is activated
			if( isset( $module['function'] ) ) {
				if( is_array( $module['function'] ) ) {
					$size = count( $module['function'] );
					for( $i = 0; $i < $size; $i++ ) {
						if( function_exists( $module['function'][$i] ) ) {
							$modules[$key]['status'] = 'active';
							$modules_active++;
							break;
						}
					}
				} else {
					if( function_exists( $module['function'] ) ) {
						$modules[$key]['status'] = 'active';
						$modules_active++;
					}
				}
			} else if( isset( $module['class'] ) ) {
				if( is_array( $module['class'] ) ) {
					$size = count( $module['class'] );
					for( $i = 0; $i < $size; $i++ ) {
						if( class_exists( $module['class'][$i] ) ) {
							$modules[$key]['status'] = 'active';
							$modules_active++;
							break;
						}
					}
				} else {
					if( class_exists( $module['class'] ) ) {
						$modules[$key]['status'] = 'active';
						$modules_active++;
					}
				}
			}
			// Filter Modules by Module Status
			if( !empty( $module_status ) ) {
				switch( $module_status ) {

					case 'active':
						if( $modules[$key]['status'] == 'inactive' )
							unset( $modules[$key] );
						break;

					case 'inactive':
						if( $modules[$key]['status'] == 'active' )
							unset( $modules[$key] );
						break;

				}
			}
			// Check that we've got these resources available
			if( isset( $modules[$key] ) && function_exists( 'current_user_can' ) && did_action( 'init' ) ) {
				// Check if the Plugin has a slug and if User can install Plugins
				if( current_user_can( $user_capability ) && isset( $module['slug'] ) )
					$modules[$key]['url'] = admin_url( sprintf( 'plugin-install.php?tab=search&type=term&s=%s', $module['slug'] ) );
			}
		}
	}

	// Check if the existing Transient exists
	$cached = get_transient( WOO_CD_PREFIX . '_modules_active_count' );
	if( $cached == false ) {
		set_transient( WOO_CD_PREFIX . '_modules_active_count', $modules_active, DAY_IN_SECONDS );
	}

	// Check if the existing Transient exists
	$cached = get_transient( WOO_CD_PREFIX . '_modules_inactive_count' );
	if( $cached == false ) {
		$modules_inactive = $modules_all - $modules_active;
		set_transient( WOO_CD_PREFIX . '_modules_inactive_count', $modules_inactive, DAY_IN_SECONDS );
	}

	return $modules;

}

// Returns whether a supported export Plugin is activated
function woo_ce_detect_export_plugin( $plugin_name = '' ) {

	if( empty( $plugin_name ) )
		return;

	// Check if a cached list of active modules is available
	if ( false === ( $active_modules = get_transient( WOO_CD_PREFIX . '_modules_active' ) ) ) {
		$active_modules = woo_ce_refresh_active_export_plugins();
	}

	// Check if the requested Plugin is in the list of active export Plugins
	if( !empty( $active_modules ) ) {
		if( in_array( $plugin_name, $active_modules ) )
			return true;
	}

}

function woo_ce_refresh_active_export_plugins() {

	// Delete the existing count Transients
	delete_transient( WOO_CD_PREFIX . '_modules_all_count' );
	delete_transient( WOO_CD_PREFIX . '_modules_active_count' );
	delete_transient( WOO_CD_PREFIX . '_modules_inactive_count' );

	// Refresh the count Transients
	$modules = woo_ce_modules_list( 'active' );

	// Create a list of active export Plugins
	$active_modules = false;
	if( !empty( $modules ) ) {
		$active_modules = array();
		foreach( $modules as $module )
			$active_modules[] = $module['name'];
	}
	unset( $modules );

	// Save the list of active export Plugins
	set_transient( WOO_CD_PREFIX . '_modules_active', $active_modules, DAY_IN_SECONDS );

	return $active_modules;

}

// Return a list of Export Template ID's
function woo_ce_get_export_templates( $args = array() ) {

	$post_type = 'export_template';
	$defaults = array(
		'post_type' => $post_type,
		'posts_per_page' => -1,
		'fields' => 'ids',
		'suppress_filters' => 1
	);
	$args = wp_parse_args( $args, $defaults );

	$templates_query = new WP_Query( $args );
	if( $templates_query->posts ) {
		return $templates_query->posts;
	}

}

function woo_ce_error_log( $message = '', $level = false ) {

	if( $message == '' )
		return;

	if( class_exists( 'WC_Logger' ) && apply_filters( 'woo_ce_error_log_use_wc_logger', true ) ) {
		$logger = new WC_Logger();
		if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
			$notice_level = ( !empty( $level ) ? $level : apply_filters( 'woo_ce_error_log_default_level', WC_Log_Levels::NOTICE ) );
			// Format notice levels to match WC_Log_Levels
			switch( $notice_level ) {

				// System is unusable.
				case 'emergency':
					$notice_level = WC_Log_Levels::EMERGENCY;
					break;

				// Action must be taken immediately. Example: Entire website down, database unavailable, etc.
				case 'alert':
					$notice_level = WC_Log_Levels::ALERT;
					break;

				// Critical conditions. Example: Application component unavailable, unexpected exception.
				case 'critical':
					$notice_level = WC_Log_Levels::CRITICAL;
					break;

				// Runtime errors that do not require immediate action but should typically be logged and monitored.
				case 'error':
					$notice_level = WC_Log_Levels::ERROR;
					break;

				// Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
				case 'warning':
					$notice_level = WC_Log_Levels::WARNING;
					break;

				// Normal but significant events.
				case 'notice':
					$notice_level = WC_Log_Levels::NOTICE;
					break;

				// Interesting events.
				case 'info':
					$notice_level = WC_Log_Levels::INFO;
					break;

				// Detailed debug information.
				case 'debug':
					$notice_level = WC_Log_Levels::DEBUG;
					break;

			}
			$logger->log( $notice_level, $message, array( 'source' => WOO_CD_PREFIX ) );
		} else {
			$logger->add( WOO_CD_PREFIX, $message );
		}
		return true;
	} else {
		// Fallback where the WooCommerce logging engine is unavailable
		error_log( sprintf( '[store-exporter-deluxe] %s', $message ) );
	}

}

function woo_ce_error_get_last_message() {

	$output = '-';
	if( function_exists( 'error_get_last' ) ) {
		$last_error = error_get_last();
		if( isset( $last_error ) && isset( $last_error['message'] ) ) {
			$output = $last_error['message'];
		}
		unset( $last_error );
	}
	return $output;

}

function woo_ce_is_scheduled_export( $post_ID = false ) {

	global $export;

	$output = false;
	if( empty( $post_ID ) ) {
		// Check if the Scheduled Export flag is set
		if( $export->scheduled_export )
			$output = true;
	}

	// If a Post ID is provided then check if that Scheduled Export is running
	if( !empty( $post_ID ) ) {
		$output = false;
		$scheduled_export = ( $export->scheduled_export ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );
		if( !empty( $scheduled_export ) ) {
			// Check if the given Post ID matches the current Scheduled Export
			if( $post_ID == $scheduled_export )
				$output = true;
		}
	}
	return $output;

}

function woo_ce_is_export_template( $post_ID = false ) {

	global $export;

	$output = false;
	// Check if the Scheduled Export flag is set
	if( $export->scheduled_export )
		$output = true;

	// If a Post ID is provided then check if a Scheduled Export is running
	if( !empty( $post_ID ) ) {
		$output = false;
		$scheduled_export = ( $export->scheduled_export ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );
		if( !empty( $scheduled_export ) ) {
			// Check if a Export Template is associated to this Scheduled Export
			$export_template = get_post_meta( $scheduled_export, '_export_template', true );
			if( $export_template !== false ) {
				// Check if the given Post ID matches the Export Template associated to this Scheduled Export
				if( $post_ID == $export_template )
					$output = true;
			}
		} else {
			// Check if a temporary Transient is set for the Export Template
			$export_template = get_transient( WOO_CD_PREFIX . '_single_export_template' );
			if( $export_template !== false ) {
				// Check if the given Post ID matches the Export Template associated to this Scheduled Export
				if( $post_ID == $export_template )
					$output = true;
			}
		}
	}
	return $output;

}

function woo_ce_get_option( $option = null, $default = false, $allow_empty = false ) {

	$output = false;
	if( $option !== null ) {
		$separator = '_';
		$output = get_option( WOO_CD_PREFIX . $separator . $option, $default );
		if( $allow_empty == false && $output != 0 && ( $output == false || $output == '' ) )
			$output = $default;
	}
	return $output;

}

function woo_ce_update_option( $option = null, $value = null ) {

	$output = false;
	if( $option !== null && $value !== null ) {
		$separator = '_';
		$output = update_option( WOO_CD_PREFIX . $separator . $option, $value );
	}
	return $output;

}
?>