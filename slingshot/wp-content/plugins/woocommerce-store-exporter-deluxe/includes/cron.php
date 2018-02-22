<?php
// Here we inform WordPress CRON of future scheduled exports
function woo_ce_cron_activation( $force_reload = false, $post_ID = 0 ) {

	if( $scheduled_exports = woo_ce_get_scheduled_exports() ) {

		// Check if we need to reload just a single scheduled export
		if( $force_reload ) {
			if( !empty( $post_ID ) ) {
				$args = array(
					'id' => $post_ID
				);
				wp_clear_scheduled_hook( 'woo_ce_auto_export_schedule_' . $post_ID, $args );
			} else {
				// Reset all scheduled exports
				foreach( $scheduled_exports as $scheduled_export ) {
					$args = array(
						'id' => $scheduled_export
					);
					wp_clear_scheduled_hook( 'woo_ce_auto_export_schedule_' . $scheduled_export, $args );
				}
			}
		}

		foreach( $scheduled_exports as $scheduled_export ) {
			$hook = 'woo_ce_auto_export_schedule_' . $scheduled_export;
			$args = array(
				'id' => $scheduled_export
			);
			// Check if this schedule already exists and that its Post Status is Publish
			if( !wp_next_scheduled( $hook, $args ) && get_post_status( $scheduled_export ) == 'publish' ) {
				$auto_schedule = sanitize_text_field( get_post_meta( $scheduled_export, '_auto_schedule', true ) );
				// Check if this is a one-time Scheduled Export and has run once
				if( $auto_schedule == 'one-time' ) {
					$total_exports = get_post_meta( $scheduled_export, '_total_exports', true );
					if( $total_exports )
						continue;
					unset( $total_exports );
				}
				if( $auto_schedule == 'manual' )
					continue;
				$auto_commence = sanitize_text_field( get_post_meta( $scheduled_export, '_auto_commence', true ) );
				switch( $auto_schedule ) {

					case 'custom':
						$recurrence = sprintf( 'woo_ce_auto_interval_%d', $scheduled_export );
						break;

					default:
						$recurrence = $auto_schedule;
						break;

				}
				switch( $auto_commence ) {

					// Start initial export immediately
					case 'now':
					default:
						$time = current_time( 'timestamp', 1 );
						break;

					// Pass on a timestamp from the future
					case 'future':
						$commence_date = sanitize_text_field( get_post_meta( $scheduled_export, '_auto_commence_date', true ) );
						// Check if date is set
						if( !empty( $commence_date ) ) {
							$now = current_time( 'timestamp', 0 );
							$timezone = ( function_exists( 'wc_timezone_string' ) ? wc_timezone_string() : date_default_timezone_get() );
							$objTimeZone = new DateTimezone( $timezone );
							$objDateTo = new DateTime( woo_ce_format_order_date( $commence_date ), $objTimeZone );
							$commence_date = $objDateTo->format( 'U' );
							$time = $commence_date;
						} else {
							$time = $now;
						}
						break;

				}
				$args = array(
					'id' => $scheduled_export
				);
				if( $auto_schedule == 'one-time' ) {
					wp_schedule_single_event( $time, $hook, $args );
				} else {
					// Check if hook still exists (as WordPress tends to ignore us)
					if( !wp_next_scheduled( $hook, $args ) ) {
						wp_schedule_event( $time, $recurrence, $hook, $args );
					} else {
						$message = __( 'Could not re-schedule scheduled export as WordPress has not cleared the existing WP-CRON task. We will try again on next screen refresh.', 'woocommerce-exporter' );
						woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
					}
				}
			}
		}

	}

}

// Here is our list of WordPress CRON schedule frequencies
function woo_ce_cron_schedules() {

	$schedules = array();

	// Check if Weekly already exists
	if( !isset( $schedules['weekly'] ) ) {
		$schedules['weekly'] = array(
			'interval' => ( 60 * 60 * 24 * 7 ),
			'display'  => __( 'Once Weekly', 'woocommerce-exporter' )
		);
	} else {
		if( apply_filters( 'woo_ce_cron_schedules_checks', false ) ) {
			// Display warning that weekly schedule is already set
			$message = __( 'The Once Weekly schedule has already been set by WordPress or another WordPress Plugin', 'woocommerce-exporter' );
			woo_ce_error_log( sprintf( 'Warning: %s' ), $message );
		}
	}

	// Check if Monthly already exists
	if( !isset( $schedules['monthly'] ) ) {
		$schedules['monthly'] = array(
			'interval' => ( date( 't' ) * 60 * 60 * 24 ),
			'display'  => __( 'Once Monthly', 'woocommerce-exporter' )
		);
	} else {
		if( apply_filters( 'woo_ce_cron_schedules_checks', false ) ) {
			// Display warning that the monthly schedule is already set
			$message = __( 'The Once Monthly schedule has already been set by WordPress or another WordPress Plugin', 'woocommerce-exporter' );
			woo_ce_error_log( sprintf( 'Warning: %s' ), $message );
		}
	}

	$args = array(
		'post_status' => 'publish'
	);
	if( $scheduled_exports = woo_ce_get_scheduled_exports( $args ) ) {
		foreach( $scheduled_exports as $scheduled_export ) {
			$schedule = sanitize_text_field( get_post_meta( $scheduled_export, '_auto_schedule', true ) );
			switch( $schedule ) {

				case 'custom':
					$interval = absint( get_post_meta( $scheduled_export, '_auto_interval', true ) );
					if( $interval ) {
						$schedules[sprintf( 'woo_ce_auto_interval_%d', $scheduled_export )] = array(
							'interval' => $interval * 60,
							'display'  => sprintf( __( 'Every %d minutes', 'woocommerce-exporter' ), $interval )
						);
					}
					break;

			}
		}
	}
	return $schedules;

}

// Runs as part of Scheduled Export tasks
function woo_ce_auto_export( $args = array() ) {

	if( WOO_CD_LOGGING )
		woo_ce_error_log( sprintf( 'Debug: %s', 'cron.php - start of woo_ce_auto_export' ) );

	if( !empty( $args ) ) {

		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'cron.php - args: %s', print_r( $args, true ) ) ) );

		$single_task = ( strpos( $args, '+' ) !== false ? true : false );

		$post_ID = absint( $args );

		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'cron.php - Post ID: %s', $post_ID ) ) );

		// Check if another Scheduled Export is currently running
		$scheduled_export = absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) );
		if( $scheduled_export ) {

			if( WOO_CD_LOGGING )
				woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'cron.php - Scheduled Export - #%s - is already running, scheduling new single event for Scheduled Export #%s', $scheduled_export, $post_ID ) ) );

			// Allow Plugin/Theme authors to override Scheduled Export conflict timeout
			$timeout = apply_filters( 'woo_ce_scheduled_export_conflict_timeout', ( MINUTE_IN_SECONDS * 10 ), $post_ID );

			$time = current_time( 'timestamp', 1 ) + $timeout;
			$hook = sprintf( 'woo_ce_auto_export_schedule_%d', $post_ID );
			$args = array(
				'id' => $post_ID
			);
			wp_schedule_single_event( $time, $hook, $args );
			return;

		}

		// Check if a draft/trash scheduled export snuck through
		if(
			!$single_task && 
			in_array( get_post_status( $post_ID ), array( 'draft', 'trash' ) )
		) {

			if( WOO_CD_LOGGING )
				woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'cron.php - Scheduled Export - #%s - is marked as a Draft or in the Trash, skipping...', $post_ID ) ) );

			// Delete any scheduled exports that were missed
			if( get_post_status( $post_ID ) == 'trash' )
				wp_delete_post( $post_ID, true );
			woo_ce_cron_activation( true, $post_ID );
			return;

		}

		// Check if the scheduling days filter is in use
		$auto_days = get_post_meta( $post_ID, '_auto_days', true );
		if( 
			!$single_task && 
			$auto_days <> false && count( $auto_days ) < 7
		) {
			if( in_array( date( 'w', current_time( 'timestamp' ) ), $auto_days ) == false ) {

				if( WOO_CD_LOGGING )
					woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'cron.php - Scheduled Export - #%s - is not scheduled to run today, skipping...', $post_ID ) ) );

				return;

			}
		}
		unset( $auto_days );

		// Set up our export
		set_transient( WOO_CD_PREFIX . '_scheduled_export_id', $post_ID, ( MINUTE_IN_SECONDS * 10 ) );

		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'cron.php - Scheduled Export Transient - %s - set with Post ID: %s, valid for the next 10 minutes', WOO_CD_PREFIX . '_scheduled_export_id', $post_ID ) ) );

		$export_type = get_post_meta( $post_ID, '_export_type', true );
		// Check an export type has been set
		if( !empty( $export_type ) ) {

			$export_method = get_post_meta( $post_ID, '_export_method', true );
			if( in_array( $export_method, array( 'archive', 'save', 'email', 'post', 'ftp' ) ) )
				woo_ce_cron_export( $export_method, $export_type, array( 'is_scheduled' => 1 ) );
			else
				woo_ce_cron_export( '', $export_type, array( 'is_scheduled' => 1 ) );

		} else {

			if( WOO_CD_LOGGING )
				woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'cron.php - Scheduled Export - #%s - is missing a Export Type, skipping...', $post_ID ) ) );

		}

		// Clean up
		delete_transient( WOO_CD_PREFIX . '_scheduled_export_id' );

	}

}

function woo_ce_cron_export( $gui = '', $type = '', $assoc_args = array() ) {

	global $export;

	// Hide error logging during the export process
	if( function_exists( 'ini_set' ) )
		@ini_set( 'display_errors', 0 );

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

	$is_scheduled = ( !empty( $assoc_args['is_scheduled'] ) ? $assoc_args['is_scheduled'] : false );
	$is_cli = ( !empty( $assoc_args['is_cli'] ) ? $assoc_args['is_cli'] : false );

	$time_limit = false;
	if( function_exists( 'ini_get' ) )
		$time_limit = ini_get( 'max_execution_time' );

	// @mod - Add in detection of PHP form size limit and notify store owner if we go over it; currently returns the Quick Export screen with no notification, review in 2.4+
	if( WOO_CD_LOGGING ) {

		woo_ce_error_log( sprintf( 'Debug: %s', '---' ) );

		$total = false;
		if( function_exists( 'ini_get' ) ) {
			$total = ini_get( 'max_input_vars' );
		}

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
			woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'max_input_vars: %d seconds', $time_limit ) ) );
		else
			woo_ce_error_log( sprintf( 'Debug: %s', 'max_input_vars: ' . __( 'Unlimited', 'woocommerce-exporter' ) ) );

	}

	$timeout = woo_ce_get_option( 'timeout', 0 );
	$safe_mode = ( function_exists( 'safe_mode' ) ? ini_get( 'safe_mode' ) : false );
	if( !$safe_mode ) {
		if( function_exists( 'set_time_limit' ) )
			@set_time_limit( $timeout );
		if( function_exists( 'ini_set' ) )
			@ini_set( 'max_execution_time', $timeout );
	}
	if( function_exists( 'ini_set' ) )
		@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );

	// Set up the basic export options
	$export = new stdClass();
	$export->cron = ( $is_scheduled ? 0 : 1 );
	$export->scheduled_export = ( $is_scheduled ? 1 : 0 );
	$export->cli = ( $is_cli ? 1 : 0 );
	$export->start_time = time();
	$export->time_limit = ( isset( $time_limit ) ? $time_limit : 0 );
	if( WOO_CD_LOGGING ) {
		woo_ce_error_log( sprintf( 'Debug: %s', 'begin CRON export generation: ' . ( time() - $export->start_time ) ) );
	}
	$export->idle_memory_start = woo_ce_current_memory_usage();
	$export->error = '';

	// Let's prepare the export data

	$bits = '';
	$type = ( isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : $type );
	if( empty( $type ) ) {

		if( $gui == 'gui' ) {
			$output = sprintf( '<p>%s</p>', __( 'No export type was provided.', 'woocommerce-exporter' ) );
		} else {
			$message = __( 'No export type was provided', 'woocommerce-exporter' );
			woo_ce_error_log( sprintf( 'Error: %s', $message ) );
			return;
		}

	} else {

		$export_types = apply_filters( 'woo_ce_cron_allowed_export_types', array_keys( woo_ce_get_export_types() ) );
		$export->type = $type;
		// Check that export is in the list of allowed exports
		if( !in_array( $export->type, $export_types ) ) {

			if( $gui == 'gui' ) {
				$output = '<p>' . __( 'An invalid export type was provided.', 'woocommerce-exporter' ) . '</p>';
			} else {
				$message = __( 'An invalid export type was provided', 'woocommerce-exporter' );
				woo_ce_error_log( sprintf( 'Error: %s', $message ) );
				return;
			}

		} else {

			woo_ce_load_export_types();

			$export->export_format = ( isset( $_GET['format'] ) ? sanitize_text_field( $_GET['format'] ) : 'csv' );

			// Load the Post ID for scheduled exports
			if( isset( $_GET['scheduled_export'] ) ) {
				// Override this CRON export as a scheduled export
				$is_scheduled = 1;
				$export->scheduled_export = 1;
				$scheduled_export = absint( $_GET['scheduled_export'] );
				set_transient( WOO_CD_PREFIX . '_scheduled_export_id', $scheduled_export, ( MINUTE_IN_SECONDS * 10 ) );
				$export_type = get_post_meta( $scheduled_export, '_export_type', true );
				$export->type = $export_type;
				$gui = $export_method = get_post_meta( $scheduled_export, '_export_method', true );
			} else {
				$scheduled_export = ( $export->scheduled_export ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );
			}

			// Override the export format if outputting to screen in friendly design
			if( $gui == 'gui' && in_array( $export->export_format, array( 'csv', 'tsv', 'xls', 'xlsx' ) ) )
				$export->export_format = 'csv';

			// Override the export format if this is a scheduled export
			if( $export->scheduled_export )
				$export->export_format = get_post_meta( $scheduled_export, '_export_format', true );

			// Override the export format if the single order Transient is set
			$single_export_format = get_transient( WOO_CD_PREFIX . '_single_export_format' );
			if( $single_export_format != false )
				$export->export_format = $single_export_format;
			unset( $single_export_format );

			$export->order_items = ( isset( $_GET['order_items'] ) ? sanitize_text_field( $_GET['order_items'] ) : woo_ce_get_option( 'order_items_formatting', 'unique' ) );
			// Override Order Items Formatting if this is a Scheduled Export
			if( $export->scheduled_export ) {
				$scheduled_export_order_items_formatting = get_post_meta( $scheduled_export, '_filter_order_items', true );
				if( $scheduled_export_order_items_formatting != false )
					$export->order_items = $scheduled_export_order_items_formatting;
				unset( $scheduled_export_order_items_formatting );
			}
			// Override order items formatting if the single order Transient is set
			$single_export_order_items_formatting = get_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting' );
			if( $single_export_order_items_formatting != false )
				$export->order_items = $single_export_order_items_formatting;
			unset( $single_export_order_items_formatting );
			$export->delimiter = ( isset( $_GET['delimiter'] ) ? sanitize_text_field( $_GET['delimiter'] ) : woo_ce_get_option( 'delimiter', ',' ) );
			// Reset the Delimiter if corrupted
			if( $export->delimiter == '' || $export->delimiter == false ) {
				$message = __( 'Delimiter export option was corrupted, defaulted to ,', 'woocommerce-exporter' );
				woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
				$export->delimiter = ',';
				woo_ce_update_option( 'delimiter', ',' );
			} else if( $export->delimiter == 'TAB' ) {
				$export->delimiter = "\t";
			}
			$export->category_separator = ( isset( $_GET['category_separator'] ) ? sanitize_text_field( $_GET['category_separator'] ) : woo_ce_get_option( 'category_separator', '|' ) );
			// Override for line break (LF) support in Category Separator
			if( $export->category_separator == 'LF' )
				$export->category_separator = "\n";
			$export->bom = ( isset( $_GET['bom'] ) ? absint( $_GET['bom'] ) : woo_ce_get_option( 'bom', 1 ) );
			$export->encoding = ( isset( $_GET['encoding'] ) ? sanitize_text_field( $_GET['encoding'] ) : woo_ce_get_option( 'encoding', 'UTF-8' ) );
			$export->timeout = woo_ce_get_option( 'timeout', 600 );
			$export->escape_formatting = ( isset( $_GET['escape_formatting'] ) ? sanitize_text_field( $_GET['escape_formatting'] ) : woo_ce_get_option( 'escape_formatting', 'all' ) );
			$export->header_formatting = ( isset( $_GET['header_formatting'] ) ? absint( $_GET['header_formatting'] ) : woo_ce_get_option( 'header_formatting', 1 ) );
			// Override header formatting if this is a Scheduled Export
			if( $export->scheduled_export ) {
				$scheduled_export_header_formatting = get_post_meta( $scheduled_export, '_header_formatting', true );
				if( $scheduled_export_header_formatting != false )
					$export->header_formatting = $scheduled_export_header_formatting;
				unset( $scheduled_export_header_formatting );
			}
			$export->filename = woo_ce_generate_filename( $export->type );

			// Set the file extension and MIME type
			switch( $export->export_format ) {

				case 'csv':
					$php_excel_format = 'SED_CSV';
					$file_extension = 'csv';
					$post_mime_type = 'text/csv';
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

				case 'xml':
					$file_extension = 'xml';
					$post_mime_type = 'application/xml';
					break;

				case 'rss':
					$file_extension = 'xml';
					$post_mime_type = 'application/rss+xml';
					break;

				default:
					// Check if the Export Format is custom
					$export_formats = woo_ce_get_export_formats();
					$export_formats = array_keys( $export_formats );
					if( in_array( $export->export_format, $export_formats ) ) {
						$file_extension = apply_filters( 'woo_ce_cron_export_custom_file_extension', 'csv', $export->export_format );
						$post_mime_type = apply_filters( 'woo_ce_cron_export_custom_post_mime_type', 'csv', $export->export_format );
					} else {
						if( $export->scheduled_export ) {
							$message = sprintf( __( 'An invalid export format - %s was provided by Scheduled Export #%d', 'woocommerce-exporter' ), $export->export_format, $scheduled_export );
							woo_ce_error_log( sprintf( 'Error: %s', $message ) );
						} else {
							$message = sprintf( __( 'An invalid export format - %s was provided', 'woocommerce-exporter' ), $export->export_format );
							woo_ce_error_log( sprintf( 'Error: %s', $message ) );
						}
						return;
					}
					break;

			}

			// Tack on the file extension
			$export->filename = $export->filename . '.' . $file_extension;
			$export->limit_volume = ( isset( $_GET['limit'] ) ? absint( $_GET['limit'] ) : -1 );
			$export->offset = ( isset( $_GET['offset'] ) ? absint( $_GET['offset'] ) : 0 );
			// Override the Volume Limit and Offset if this is a scheduled export
			if( $export->scheduled_export ) {
				$delimiter = get_post_meta( $scheduled_export, '_delimiter', true );
				if( $delimiter != false )
					$export->delimiter = $delimiter;
				unset( $delimiter );
				$limit_volume = get_post_meta( $scheduled_export, '_limit_volume', true );
				if( $limit_volume !== false )
					$export->limit_volume = $limit_volume;
				unset( $limit_volume );
				$offset = get_post_meta( $scheduled_export, '_offset', true );
				if( $offset !== false )
					$export->offset = $offset;
				unset( $offset );
			}
			// Select all export fields for CRON export
			$export->fields = woo_ce_cron_export_fields( $export->type, $export->scheduled_export, $scheduled_export );
			// Grab to value if response is e-mail or remote POST
			if( in_array( $gui, array( 'email', 'post' ) ) ) {
				if( $gui == 'email' ) {
					$export->to = ( isset( $_GET['to'] ) ? sanitize_email( $_GET['to'] ) : get_post_meta( $scheduled_export, '_method_email_to', true ) );

					// Override the e-mail recipient if the single order Transient is set
					$single_export_method_email_to = get_transient( WOO_CD_PREFIX . '_single_export_method_email_to' );
					if( $single_export_method_email_to != false )
						$export->to = $single_export_method_email_to;
					unset( $single_export_method_email_to );

					// Default e-mail recipient to WordPress Administration e-mail
					if( empty( $export->to ) )
						$export->to = get_bloginfo( 'admin_email' );

				} else if( $gui == 'post' ) {
					$export->to = ( isset( $_GET['to'] ) ? esc_url_raw( $_GET['to'] ) : get_post_meta( $scheduled_export, '_method_post_to', true ) );
				}
			}
			$export = woo_ce_check_cron_export_arguments( $export );

			$export->args = array(
				'limit_volume' => $export->limit_volume,
				'offset' => $export->offset,
				'encoding' => $export->encoding,
				'date_format' => woo_ce_get_option( 'date_format', 'd/m/Y' ),
				'order_items' => $export->order_items,
				'order_items_types' => ( isset( $_GET['order_items_types'] ) ? sanitize_text_field( $_GET['order_items_types'] ) : woo_ce_get_option( 'order_items_types', false ) )
			);

			$orderby = ( isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : null );
			$order = ( isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : null );
			switch( $export->type ) {

				case 'product':
					$export->args['product_orderby'] = $orderby;
					$export->args['product_order'] = $order;
					break;

				case 'category':
					$export->args['category_orderby'] = $orderby;
					$export->args['category_order'] = $order;
					break;

				case 'tag':
					$export->args['tag_orderby'] = $orderby;
					$export->args['tag_order'] = $order;
					break;

				case 'order':
					$export->args['order_orderby'] = $orderby;
					$export->args['order_order'] = $order;
					$export->args['order_ids'] = ( isset( $_GET['order_ids'] ) ? sanitize_text_field( $_GET['order_ids'] ) : null );

					// Override Filter Orders by Order ID if a single order transient is set
					$single_export_order_ids = get_transient( WOO_CD_PREFIX . '_single_export_post_ids' );
					if( $single_export_order_ids != false )
						$export->args['order_ids'] = sanitize_text_field( $single_export_order_ids );
					unset( $single_export_order_ids );
					break;

				case 'customer':
					$export->args['customer_order'] = $order;
					break;

				case 'subscription':
					$export->args['subscription_orderby'] = $orderby;
					$export->args['subscription_order'] = $order;
					break;

				case 'product_vendor':
					$export->args['product_vendor_orderby'] = $orderby;
					$export->args['product_vendor_order'] = $order;
					break;

				case 'user':
					$export->args['user_orderby'] = $orderby;
					$export->args['user_order'] = $order;
					break;

				case 'commission':
					$export->args['commission_orderby'] = $orderby;
					$export->args['commission_order'] = $order;
					break;

				case 'review':
					$export->args['review_orderby'] = $orderby;
					$export->args['review_order'] = $order;
					break;

				case 'shipping_class':
					$export->args['shipping_class_orderby'] = $orderby;
					$export->args['shipping_class_order'] = $order;
					break;

				case 'booking':
					$export->args['booking_orderby'] = $orderby;
					$export->args['booking_order'] = $order;
					break;

				case 'attribute':
					$export->args['attribute_orderby'] = $orderby;
					$export->args['attribute_order'] = $order;
					break;

			}

			// Allow Plugin/Theme authors to add support for additional filters
			$export->args = apply_filters( 'woo_ce_extend_cron_dataset_args', $export->args, $export->type, $export->scheduled_export );

			$export->filename = sprintf( '%s.%s', woo_ce_generate_filename( $export->type ), $file_extension );

			// Let's spin up PHPExcel for supported export types and formats
			if( in_array( $export->export_format, apply_filters( 'woo_ce_phpexcel_supported_export_formats', array( 'csv', 'tsv', 'xls', 'xlsx' ) ) ) ) {

				$dataset = woo_ce_export_dataset( $export->type );

				if( !empty( $dataset ) ) {

					// Load up the fatal error notice if we 500, timeout or encounter a fatal PHP error
					add_action( 'shutdown', 'woo_ce_fatal_error' );

					// Check that PHPExcel is where we think it is
					if( file_exists( WOO_CD_PATH . 'classes/PHPExcel.php' ) ) {
						// Check if PHPExcel has already been loaded
						if( !class_exists( 'PHPExcel' ) ) {
							include_once( WOO_CD_PATH . 'classes/PHPExcel.php' );
						} else {
							$message = __( 'The PHPExcel library was already loaded by another WordPress Plugin, if there\'s issues with your export file you know where to look.', 'woocommerce-exporter' );
							woo_ce_error_log( sprintf( '%s: Warning: %s', $export->filename, $message ) );
						}

						// Cache control
						do_action( 'woo_ce_export_phpexcel_caching_methods' );

						// Final check incase something is blocking PHPExcel
						if( !class_exists( 'PHPExcel' ) ) {
							$message = sprintf( __( 'We couldn\'t load the PHPExcel library <code>%s</code> within <code>%s</code> even after trying workarounds, this file should be present. <a href="%s" target="_blank">Need help?</a>', 'woocommerce-exporter' ), 'PHPExcel.php', WOO_CD_PATH . 'classes/...', $troubleshooting_url );
							woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $message ) );
							return;
						}

						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'cron.php - before building PHPExcel export contents: ' . ( time() - $export->start_time ) ) );

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
											woo_ce_error_log( sprintf( '%s: Warning: %s', $export->filename, $message ) );
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
										$excel->getActiveSheet()->getCellByColumnAndRow( $col, $row )->setValueExplicit( ( isset( $data->$field ) ? woo_ce_wp_specialchars_decode( $data->$field ) : '' ), woo_ce_detect_value_string( ( isset( $data->$field ) ? $data->$field : null ), 'type' ) );
									}
								} else {
									// PHPExcel only deals with UTF-8 regardless of encoding type
									if( woo_ce_detect_value_string( ( isset( $data->$field ) ? $data->$field : null ) ) ) {
										// Treat this cell as a string
										$excel->getActiveSheet()->getCellByColumnAndRow( $col, $row )->setValueExplicit( ( isset( $data->$field ) ? utf8_encode( woo_ce_wp_specialchars_decode( $data->$field ) ) : '' ), PHPExcel_Cell_DataType::TYPE_STRING );
									} else {
										$excel->getActiveSheet()->getCellByColumnAndRow( $col, $row )->setValueExplicit( ( isset( $data->$field ) ? utf8_encode( woo_ce_wp_specialchars_decode( $data->$field ) ) : '' ), woo_ce_detect_value_string( ( isset( $data->$field ) ? $data->$field : null ), 'type' ) );
									}
								}

								$col++;

							}

							$row = apply_filters( 'woo_ce_phpexcel_row', $row, $export->type, $export->export_format );
							$row++;

						}

						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'cron.php - after building PHPExcel export contents: ' . ( time() - $export->start_time ) ) );

						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'cron.php - before building PHPExcel export file: ' . ( time() - $export->start_time ) ) );

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

						$objWriter = PHPExcel_IOFactory::createWriter( $excel, $php_excel_format );
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

						}
						if( in_array( $gui, array( 'raw' ) ) ) {
							$objWriter->save( 'php://output' );
						} else {
							// Save file to PHP tmp then pass to PHPExcel
							$temp_filename = tempnam( apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ), 'tmp' );
							// Check if we were given a temporary filename
							if( $temp_filename == false ) {
								$export->error = sprintf( __( 'We could not create a temporary export file in %s, ensure that WordPress can read and write files here and try again.', 'woocommerce-exporter' ), apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ) );
								woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
							} else {
								$objWriter->save( $temp_filename );
								$bits = file_get_contents( $temp_filename );
							}
						}

						if( WOO_CD_LOGGING )
							woo_ce_error_log( sprintf( 'Debug: %s', 'end export generation: ' . ( time() - $export->start_time ) ) );

						// Clean up PHPExcel
						$excel->disconnectWorksheets();
						unset( $objWriter, $excel );

					} else {
						$export->error = __( 'We couldn\'t load the PHPExcel library, this file should be present.', 'woocommerce-exporter' );
						woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
					}

					// Remove our fatal error notice to play nice with other Plugins
					remove_action( 'shutdown', 'woo_ce_fatal_error' );

				}

			// Run the default engine for the XML and RSS export formats
			} else if( in_array( $export->export_format, apply_filters( 'woo_ce_simplexml_supported_export_formats', array( 'xml', 'rss' ) ) ) ) {

				include_once( WOO_CD_PATH . 'includes/export-xml.php' );

				// Check if SimpleXMLElement is present
				if( class_exists( 'SED_SimpleXMLElement' ) ) {
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
						if( woo_ce_get_option( 'xml_attribute_orderby', 1 ) )
							$xml->addAttribute( 'orderby', $orderby );
						if( woo_ce_get_option( 'xml_attribute_order', 1 ) )
							$xml->addAttribute( 'order', $order );
						if( woo_ce_get_option( 'xml_attribute_limit', 1 ) )
							$xml->addAttribute( 'limit', $export->limit_volume );
						if( woo_ce_get_option( 'xml_attribute_offset', 1 ) )
							$xml->addAttribute( 'offset', $export->offset );
						$xml = apply_filters( 'woo_ce_export_xml_before_dataset', $xml );
						$xml = woo_ce_export_dataset( $export->type, $xml );
						$xml = apply_filters( 'woo_ce_export_xml_after_dataset', $xml );
					} else if( in_array( $export->export_format, array( 'rss' ) ) ) {
						$xml = new SED_SimpleXMLElement( sprintf( apply_filters( 'woo_ce_export_rss_first_line', '<?xml version="1.0" encoding="%s"?><rss version="2.0"%s/>' ), esc_attr( $export->encoding ), ' xmlns:g="http://base.google.com/ns/1.0"' ) );
						$child = $xml->addChild( apply_filters( 'woo_ce_export_rss_channel_node', 'channel' ) );
						$child->addChild( 'title', woo_ce_get_option( 'rss_title', '' ) );
						$child->addChild( 'link', woo_ce_get_option( 'rss_link', '' ) );
						$child->addChild( 'description', woo_ce_get_option( 'rss_description', '' ) );
						$xml = apply_filters( 'woo_ce_export_rss_before_dataset', $xml );
						$xml = woo_ce_export_dataset( $export->type, $child );
						$xml = apply_filters( 'woo_ce_export_rss_after_dataset', $xml );
					}
					$bits = woo_ce_format_xml( $xml );
					// Save file to PHP tmp
					$temp_filename = tempnam( apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ), 'tmp' );
					// Check if we were given a temporary filename
					if( $temp_filename == false ) {
						$export->error = sprintf( __( 'We could not create a temporary export file in %s, ensure that WordPress can read and write files here and try again.', 'woo_ce' ), apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ) );
						woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
					} else {
						// Populate the temporary file
						$handle = fopen( $temp_filename, 'w' );
						fwrite( $handle, $bits );
						fclose( $handle );
						unset( $handle );
					}

					if( WOO_CD_LOGGING )
						woo_ce_error_log( sprintf( 'Debug: %s', 'end export generation: ' . ( time() - $export->start_time ) ) );

				} else {
					$bits = false;
					$export->error = __( 'The SimpleXMLElement class does not exist for XML file generation', 'woocommerce-exporter' );
					woo_ce_error_log( sprintf( 'Error: %s', $export->error ) );
				}

			} else {

				if( apply_filters( 'woo_ce_custom_supported_export_formats', false, $export->export_format ) == false ) {
					$bits = false;
					$export->error = sprintf( __( 'The export format - %s - is not associated with a recognised file generator', 'woocommerce-exporter' ), $export->export_format );
					woo_ce_error_log( sprintf( 'Error: %s', $export->error ) );
				} else {

					// Buffer
					ob_start();

					do_action( 'woo_ce_custom_supported_export', $export, $export->export_format );

					$bits = ob_get_contents();
					ob_end_clean();

					// Save file to PHP tmp
					$temp_filename = tempnam( apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ), 'tmp' );
					// Check if we were given a temporary filename
					if( $temp_filename == false ) {
						$export->error = sprintf( __( 'We could not create a temporary export file in %s, ensure that WordPress can read and write files here and try again.', 'woo_ce' ), apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ) );
						woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
					} else {
						// Populate the temporary file
						$handle = fopen( $temp_filename, 'w' );
						fwrite( $handle, $bits );
						fclose( $handle );
						unset( $handle );
					}

				}

			}

			if( !empty( $bits ) ) {

				$output = '<p>' . __( 'Export completed successfully.', 'woocommerce-exporter' ) . '</p>';
				if( $gui == 'gui' )
					$output .= '<textarea readonly="readonly">' . esc_textarea( str_replace( '<br />', "\n", $bits ) ) . '</textarea>';

			} else {

				if( $gui == 'gui' ) {
					$output = sprintf( '<p>%s</p>', sprintf( __( 'No %s export entries were found.', 'woocommerce-exporter' ), ucfirst( $export->type ) ) );
				} else {
					if( $export->scheduled_export ) {
						$export->error = sprintf( __( 'No %s export entries were found.', 'woocommerce-exporter' ), ucfirst( $export->type ) );
						woo_ce_error_log( sprintf( '%s: Warning: %s', $export->filename, $export->error ) );
					} else {
						woo_ce_error_log( sprintf( '%s: Warning: %s', $export->filename, sprintf( __( 'No %s export entries were found', 'woocommerce-exporter' ), ucfirst( $export->type ) ) ) );
						return;
					}
				}

			}

		}
	}

	// Time to build an export file!

	// Load up the fatal error notice if we 500, timeout or encounter a fatal PHP error
	add_action( 'shutdown', 'woo_ce_fatal_error' );

	// Return raw export to browser without file headers
	if( $gui == 'raw' ) {

		if( !empty( $bits ) )
			return $bits;

	// Return export as file download to browser
	} else if( $gui == 'download' ) {

		if( !empty( $bits ) ) {
			woo_ce_generate_file_headers( $post_mime_type );
			if( defined( 'DOING_AJAX' ) || get_transient( WOO_CD_PREFIX . '_single_export_format' ) != false )
				echo $bits;
			else
				return $bits;
		}

	// HTTP Post export contents to remote URL
	} else if( $gui == 'post' ) {

		if( !empty( $bits ) ) {
			$args = apply_filters( 'woo_ce_cron_export_post_args', array(
				'method'      => 'POST',
				'timeout'     => 60,
				'redirection' => 0,
				'httpversion' => '1.0',
				'sslverify'   => false,
				'blocking'    => true,
				'headers'     => array(
					'accept'       => $post_mime_type,
					'content-type' => $post_mime_type
				),
				'body'        => $bits,
				'cookies'     => array(),
				'user-agent'  => sprintf( 'WordPress/%s', $GLOBALS['wp_version'] ),
			) );
			if( apply_filters( 'woo_ce_cron_export_post_force_unsecure', false ) )
				add_filter( 'https_ssl_verify', '__return_false' );
			$response = wp_remote_post( $export->to, $args );
			if( apply_filters( 'woo_ce_cron_export_post_force_unsecure', false ) )
				remove_filter( 'https_ssl_verify', '__return_false' );
			if( is_wp_error( $response ) ) {
				$export->error = sprintf( __( 'Could not HTTP Post using wp_remote_post(), response: %s', 'woocommerce-exporter' ), $response->get_error_message() );
				woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
				if( !$export->scheduled_export )
					return;
			} else {
				woo_ce_error_log( sprintf( '%s: Success: %s', $export->filename, sprintf( __( 'Remote POST sent to %s', 'woocommerce-exporter' ), $export->to ) ) );
			}
		}

	// Output to screen in friendly design with on-screen error responses
	} else if( $gui == 'gui' ) {

		if( file_exists( WOO_CD_PATH . 'templates/admin/cron.php' ) ) {
			include_once( WOO_CD_PATH . 'templates/admin/cron.php' );
		} else {
			$export->error = __( 'Could not load template file within /templates/admin/cron.php', 'woocommerce-exporter' );
			woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
		}
		if( isset( $output ) )
			echo $output;
		echo '
	</body>
</html>';

	// Save export file locally outside the WordPress Media
	} else if( $gui == 'save' ) {

		if( $export->filename && !empty( $bits ) ) {
			$path = get_post_meta( $scheduled_export, '_method_save_path', true );
			$filename = get_post_meta( $scheduled_export, '_method_save_filename', true );
			// Switch to fixed export filename if provided
			if( !empty( $filename ) )
				$export->filename = sprintf( '%s.%s', woo_ce_generate_filename( $export->type, $filename ), $file_extension );
			// Change directory if neccesary
			if( !empty( $path ) ) {
				if( is_dir( ABSPATH . $path ) ) {
					$directory_response = @chdir( ABSPATH . $path );
					if( $directory_response == false ) {
						$message = __( 'Could not change the current directory on this server', 'woocommerce-exporter' );
						woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
					}
				} else {
					// Attempt to create directory
					if( wp_mkdir_p( ABSPATH . $path ) ) {
						$message = sprintf( __( 'Could not detect an existing directory from the given file path so we created it, %s', 'woocommerce-exporter' ), ABSPATH . $path );
						woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
					} else {
						$message = sprintf( __( 'Could not detect or generate a directory from the given file path, %s', 'woocommerce-exporter' ), ABSPATH . $path );
						$export->error = $message;
						woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
					}
				}
			}
			if( $handle = fopen( ABSPATH . $path . $export->filename, 'w' ) ) {
				if( fwrite( $handle, $bits ) == false ) {
					$export->error = sprintf( __( 'Could not write to the open file on this server at %s', 'woocommerce-exporter' ), ABSPATH . $path . $export->filename );
					woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
				}
				$connection_response = fclose( $handle );
				if( $connection_response == false ) {
					$export->error = sprintf( __( 'Could not close an open file pointer on this server at %s', 'woocommerce-exporter' ), ABSPATH . $path . $export->filename );
					woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
				}
			} else {
				$export->error = sprintf( __( 'Could not create or open a file on this server at %s', 'woocommerce-exporter' ), ABSPATH . $path . $export->filename );
				woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
			}
			unset( $handle );
		}

	// E-mail export file to preferred address or WordPress site owner address
	} else if( $gui == 'email' ) {

		if( !empty( $bits ) ) {

			global $woocommerce;

			// Check if the required filename already exists
			if( file_exists( apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ) . '/' . $export->filename ) )
				unlink( apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ) . '/' . $export->filename );
			$rename_response = @rename( $temp_filename, apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ) . '/' . $export->filename );
			if( $rename_response == false ) {
				$export->error = sprintf( __( 'We could not rename the temporary export file in %s, ensure that WordPress can read and write files here and try again.', 'woocommerce-exporter' ), apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ) );
				woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
			} else {
				$temp_filename = apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ) . '/' . $export->filename;
				$mailer = $woocommerce->mailer();
				$subject = woo_ce_cron_email_subject( $export->type, $export->filename );
				$attachments = apply_filters( 'woo_ce_email_attachment', $temp_filename );
				// Check file path for attachment exists before sending e-mail
				if( !file_exists( $temp_filename ) ) {
					$export->error = sprintf( __( 'We could not read the temporary export file in %s to include it in the e-mail, ensure that WordPress can read and write files here and try again.', 'woocommerce-exporter' ), apply_filters( 'woo_ce_sys_get_temp_dir', sys_get_temp_dir() ) );
					woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
					$attachments = false;
				} else {
					$email_heading = sprintf( __( 'Export: %s', 'woocommerce-exporter' ), ucwords( $export->type ) );
					$recipient_name = apply_filters( 'woo_ce_email_recipient_name', __( 'there', 'woocommerce-exporter' ) );
					$email_contents = woo_ce_cron_email_contents( $export->type, $export->filename );
					if( !empty( $export->to ) ) {
						// Check that the attachments is populated
						if( !empty( $attachments ) ) {

							// Buffer
							ob_start();

							// Get mail template, preference WordPress Theme, Plugin, fallback
							if( file_exists( get_stylesheet_directory() . '/woocommerce/emails/scheduled_export.php' ) ) {
								include_once( get_stylesheet_directory() . '/woocommerce/emails/scheduled_export.php' );
							} else if( file_exists( WOO_CD_PATH . 'templates/emails/scheduled_export.php' ) ) {
								include_once( WOO_CD_PATH . 'templates/emails/scheduled_export.php' );
							} else {
								echo wpautop( sprintf( __( 'Hi %s', 'woocommerce-exporter' ), $recipient_name ) );
								echo $email_contents;
								$export->error = sprintf( __( 'Could not load template file %s within %s, defaulted to hardcoded template.', 'woocommerce-exporter' ), 'scheduled_export.php', '/templates/emails/...' );
								woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
							}

							// Get contents
							$email_message = ob_get_clean();

							// Override for debugging failed Scheduled Export e-mails, saves to the WooCommerce logs
							if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_email', false ) ) {
								woo_ce_error_log( sprintf( 'Debug: %s', sprintf( '%s: CRON e-mail debugging...', $export->filename ) ) );
								woo_ce_error_log( sprintf( 'Debug: %s', sprintf( '%s: Recipient: %s', $export->filename, $export->to ) ) );
								woo_ce_error_log( sprintf( 'Debug: %s', sprintf( '%s: Subject: %s', $export->filename, $subject ) ) );
								woo_ce_error_log( sprintf( 'Debug: %s', sprintf( '%s: Message: %s', $export->filename, $email_message ) ) );
								woo_ce_error_log( sprintf( 'Debug: %s', sprintf( '%s: Attachment: %s', $export->filename, ( is_array( $attachments ) ? print_r( $attachments, true ) : $attachments ) ) ) );
							}

							// Send the mail using WooCommerce mailer
							if( ( function_exists( 'wc_mail' ) || function_exists( 'woocommerce_mail' ) ) && apply_filters( 'woo_ce_use_wc_mailer', true ) ) {
								if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
									if( function_exists( 'wc_mail' ) ) {
										$email_response = wc_mail( $export->to, $subject, $email_message, null, $attachments );
										if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_email', false ) ) {
											$message = sprintf( '%s: Sending e-mail using wc_mail(), response: %s', $export->filename, absint( $email_response ) );
											woo_ce_error_log( sprintf( 'Debug: %s', $message ) );
										}
									} else {
										$message = __( 'We couldn\'t load the WooCommerce resource wc_mail(), check that WooCommerce is installed and active. If this persists get in touch with us.', 'woocommerce-exporter' );
										$export->error = $message;
										woo_ce_error_log( sprintf( 'Error: %s', $message ) );
									}
								} else {
									if( function_exists( 'woocommerce_mail' ) ) {
										$email_response = woocommerce_mail( $export->to, $subject, $email_message, null, $attachments );
										if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_email', false ) ) {
											$message = sprintf( '%s: Sending e-mail using woocommerce_mail(), response: %s', $export->filename, absint( $email_response ) );
											woo_ce_error_log( sprintf( 'Debug: %s', $message ) );
										}
									} else {
										$message = __( 'We couldn\'t load the WooCommerce resource woocommerce_mail(), check that WooCommerce is installed and active. If this persists get in touch with us.', 'woocommerce-exporter' );
										$export->error = $message;
										woo_oc_error_log( sprintf( 'Error: %s', $message ) );
									}
								}
							} else {
								// Default to wp_mail()
								add_filter( 'wp_mail_content_type', 'woo_ce_set_html_content_type' );
								$email_response = wp_mail( $export->to, $subject, $email_message, null, array( $attachments ) );
								remove_filter( 'wp_mail_content_type', 'woo_ce_set_html_content_type' );
								if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_email', false ) ) {
									$message = sprintf( '%s: Sending e-mail using wp_mail(), response: %s', $export->filename, absint( $email_response ) );
									woo_ce_error_log( sprintf( 'Debug: %s', $message ) );
								}
							}

							if( $email_response ) {
								woo_ce_error_log( sprintf( '%s: Success: %s', $export->filename, sprintf( __( 'Scheduled export e-mail of %s sent to %s', 'woocommerce-exporter' ), $export->filename, $export->to ) ) );
							} else {
								// Check if this notice has been dimissed
								if( !woo_ce_get_option( 'hide_wp_mail_false_prompt', 0 ) && apply_filters( 'woo_ce_cron_export_email_wp_mail_failure_notice', true ) ) {
									$export->error = sprintf( __( 'Scheduled export e-mail of %s returned false when sending to %s, check if this message was received by the recipient(s)', 'woocommerce-exporter' ), $export->filename, $export->to );
									woo_ce_error_log( sprintf( '%s: Warning: %s', $export->filename, $export->error ) );
									woo_ce_update_option( 'wp_mail_false_prompt', 1 );
								} else {
									woo_ce_error_log( sprintf( '%s: Success: %s', $export->filename, sprintf( __( 'Scheduled export e-mail of %s sent to %s', 'woocommerce-exporter' ), $export->filename, $export->to ) ) );
									if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_email', false ) )
										woo_ce_error_log( sprintf( 'Debug: %s', sprintf( '%s: Sending e-mail responded false but warning notice was supressed, ensure the e-mail was received', $export->filename ) ) );
								}
							}
							unset( $email_response );
						} else {
							$export->error = sprintf( __( 'Scheduled export e-mail of %s sent to %s failed due to no %s export entries were found', 'woocommerce-exporter' ), $export->filename, $export->to, ucfirst( $export->type ) );
							woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
						}
					} else {
						$export->error = sprintf( __( 'Scheduled export e-mail of %s failed due to the e-mail recipient field being empty', 'woocommerce-exporter' ), $export->filename );
						woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
					}
				}
				// Delete the export file regardless of whether e-mail was successful or not
				unlink( $temp_filename );
			}
			unset( $rename_response, $temp_filename );
		}

	// Save export file to WordPress Media before sending/saving/etc. action
	} else if( in_array( $gui, array( 'gui', 'archive', 'url', 'file', 'email', 'ftp' ) ) ) {

		if( $export->filename && !empty( $bits ) ) {
			$upload = false;

			// Check for Post Parent
			$parent_post_id = 0;
			if( $gui == 'archive' ) {
				if( $export->scheduled_export )
					$parent_post_id = get_post_meta( $scheduled_export, '_method_archive_parent_post', true );
				else
					$parent_post_id = ( isset( $_GET['post_parent'] ) ? absint( $_GET['post_parent'] ) : $parent_post_id );
			}

			$post_ID = woo_ce_save_file_attachment( $export->filename, $post_mime_type, $parent_post_id );
			$upload = wp_upload_bits( $export->filename, null, $bits );
			if( ( $post_ID == false ) || $upload['error'] ) {
				wp_delete_attachment( $post_ID, true );
				$export->error = sprintf( __( 'Could not upload file to WordPress Media: %s', 'woocommerce-exporter' ), $upload['error'] );
				woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
				if( !$export->scheduled_export )
					return;
			}
			if( $post_ID && file_exists( ABSPATH . 'wp-admin/includes/image.php' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/image.php' );
				$attach_data = wp_generate_attachment_metadata( $post_ID, $upload['file'] );
				wp_update_attachment_metadata( $post_ID, $attach_data );
				update_attached_file( $post_ID, $upload['file'] );
				if( !empty( $post_ID ) ) {
					woo_ce_save_file_guid( $post_ID, $export->type, $upload['url'] );
					woo_ce_save_file_details( $post_ID );
				}
			} else {
				woo_ce_error_log( sprintf( '%s: Warning: %s', $export->filename, __( 'Could not load image.php within /wp-admin/includes/image.php', 'woocommerce-exporter' ) ) );
			}

			// Return URL to export file
			if( $gui == 'url' )
				return $upload['url'];

			// Return system path to export file
			if( $gui == 'file' )
				return $upload['file'];

			// Upload export file to FTP server
			if( $gui == 'ftp' ) {
				// Load up our FTP/SFTP resources
				$host = get_post_meta( $scheduled_export, '_method_ftp_host', true );
				if( !empty( $host ) )
					$host = woo_ce_format_ftp_host( $host );
				$port = get_post_meta( $scheduled_export, '_method_ftp_port', true );
				$port = ( !empty( $port ) ? absint( $port ) : false );
				$user = get_post_meta( $scheduled_export, '_method_ftp_user', true );
				$pass = get_post_meta( $scheduled_export, '_method_ftp_pass', true );
				$path = get_post_meta( $scheduled_export, '_method_ftp_path', true );
				$filename = get_post_meta( $scheduled_export, '_method_ftp_filename', true );
				if( !empty( $filename ) ) {
					// Switch to fixed export filename if provided
					$export->filename = woo_ce_generate_filename( $export->type, $filename ) . '.' . $file_extension;
				}

				// Check what protocol are we using; FTP or SFTP?
				$protocol = get_post_meta( $scheduled_export, '_method_ftp_protocol', true );
				switch( $protocol ) {

					case 'ftp':
					default:
						$ftp_passive = get_post_meta( $scheduled_export, '_method_ftp_passive', true );
						$ftp_mode = get_post_meta( $scheduled_export, '_method_ftp_mode', true );
						$ftp_timeout = get_post_meta( $scheduled_export, '_method_ftp_timeout', true );

						// Allow Plugin/Theme authors to override Scheduled Export FTP/FTPS timeout
						$ftp_timeout = apply_filters( 'woo_ce_cron_export_ftp_timeout', $ftp_timeout, $scheduled_export );

						// ftp_connect() doesn't like an empty timeout value
						if( empty( $ftp_timeout ) )
							$ftp_timeout = 90;

						// Check if we are making an encrypted connection (FTPS)
						$ftp_encryption = get_post_meta( $scheduled_export, '_method_ftp_encryption', true );
						if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_ftp', false ) )
							woo_ce_error_log( sprintf( 'Debug: %s', 'ftp_encryption: ' . $ftp_encryption ) );
						if( in_array( $ftp_encryption, array( 'explicit', 'implicit' ) ) ) {
							switch( $ftp_encryption ) {

								case 'explicit':
									// Check if ftp_ssl_connect() is available; explicit only
									if( function_exists( 'ftp_ssl_connect' ) ) {
										if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_ftp', false ) )
											woo_ce_error_log( sprintf( 'Debug: %s', 'ftp_ssl_connect() exists, port: ' . $port ) );
										$connection = @ftp_ssl_connect( $host, $port, $ftp_timeout );
										// Check if we are defaulting to port 21, try 990
										if( !$connection && !$port ) {
											if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_ftp', false ) )
												woo_ce_error_log( sprintf( 'Debug: %s', 'could not connect via FTPS on port: ' . $port ) );
											$port = 990;
											$connection = ftp_ssl_connect( $host, $port, $ftp_timeout );
											if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_ftp', false ) )
												woo_ce_error_log( 'attempting ftps connection on port 990' );
											if( !$connection ) {
												if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_ftp', false ) )
													woo_ce_error_log( sprintf( 'Debug: %s', 'could not connect via FTPS on port 990' ) );
											}
										}
										if( !$connection ) {
											$export->error = sprintf( __( 'There was a problem connecting to %s via FTPS', 'woocommerce-exporter' ), $host );
											woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
										} else {
											if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_ftp', false ) )
												woo_ce_error_log( sprintf( 'Debug: %s', 'ftps connection works' ) );
										}
									} else {
										if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_ftp', false ) )
											woo_ce_error_log( sprintf( 'Debug: %s', 'ftp_ssl_connect() does not exist' ) );
										$export->error = __( 'The function ftp_ssl_connect() is disabled within your WordPress site, cannot upload over SSL to FTP server', 'woocommerce-exporter' );
										woo_ce_error_log( __( '%s: Error: %s', 'woocommerce-exporter' ), $export->filename, $export->error );
									}
									break;

								case 'implicit':
									$connection = curl_init();
									if( $connection ) {
										$args = array(
											CURLOPT_USERPWD => $user . ':' . $pass,
											CURLOPT_SSL_VERIFYPEER => false, // don't verify SSL
											CURLOPT_SSL_VERIFYHOST => false,
											CURLOPT_FTP_SSL => CURLFTPSSL_ALL, // require SSL For both control and data connections
											CURLOPT_FTPSSLAUTH => CURLFTPAUTH_DEFAULT, // let cURL choose the FTP authentication method (either SSL or TLS)
											CURLOPT_UPLOAD => true,
											CURLOPT_PORT => $port,
											CURLOPT_TIMEOUT => $ftp_timeout
										);
										if( !$ftp_passive )
											$options[CURLOPT_FTPPORT] = '-';
										foreach( $args as $option_name => $option_value ) {
											if( !curl_setopt( $connection, $option_name, $option_value ) ) {
												woo_ce_error_log( __( '%s: Warning: %s', 'woocommerce-exporter' ), $export->filename, sprintf( __( 'Could not set cURL option: %s', 'woocommerce-exporter' ), $option_name ) );
											}
										}
										$url = sprintf( 'ftps://%s/%s', $host, $path );
										if( !curl_setopt( $connection, CURLOPT_URL, $url . $file_name ) ) {
											$export->error = sprintf( __( 'Could not set cURL file name: %s', 'woocommerce-exporter' ), $export->filename );
											woo_ce_error_log( __( '%s: Error: %s', 'woocommerce-exporter' ), $export->filename, $export->error );
										}
										$stream = fopen( 'php://temp', 'w+' );
										if( !$stream ) {
											$export->error = __( 'Could not open php://temp for writing', 'woocommerce-exporter' );
											woo_ce_error_log( __( '%s: Error: %s', 'woocommerce-exporter' ), $export->filename, $export->error );
										}
										fwrite( $stream, $export->filename );
										rewind( $stream );
										if( !curl_setopt( $connection, CURLOPT_INFILE, $stream ) ) {
											$export->error = sprintf( __( 'Could not load file: %s', 'woocommerce-exporter' ), $export->filename );
											woo_ce_error_log( __( '%s: Error: %s', 'woocommerce-exporter' ), $export->filename, $export->error );
										}
										if( !curl_exec( $connection ) ) {
											$export->error = sprintf( 'Could not upload file. cURL Error: [%s] - %s', curl_errno( $connection ), curl_error( $connection ) );
											woo_ce_error_log( __( '%s: Error: %s', 'woocommerce-exporter' ), $export->filename, $export->error );
										}
										fclose( $stream );
										@curl_close( $connection );
									} else {
										$export->error = __( 'Could not initialize cURL, cannot upload over SSL to FTP server', 'woocommerce-exporter' );
										woo_ce_error_log( __( '%s: Error: %s', 'woocommerce-exporter' ), $export->filename, $export->error );
									}
									break;

							}
						}

						// Check if ftp_connect() is available, not used but expected for FTPS uploads
						if( function_exists( 'ftp_connect' ) ) {
							// Check that this isn't an encrypted connection (FTPS)
							if( !$ftp_encryption ) {
								$connection = @ftp_connect( $host, $port, $ftp_timeout );
								if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_ftp', false ) )
									woo_ce_error_log( sprintf( 'Debug: %s', 'defaulting to unsecured FTP connection' ) );
							}
							// Check that this isn't an implicit encrypted connection (FTPS)
							if( $connection && $ftp_encryption <> 'implicit' ) {
								if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_cron_export_ftp', false ) )
									woo_ce_error_log( sprintf( 'Debug: %s', 'FTP connection is working' ) );
								// Update the FTP timeout if available and if a timeout was provided at export
								if( function_exists( 'ftp_get_option' ) && function_exists( 'ftp_set_option' ) ) {
									$remote_timeout = @ftp_get_option( $connection, FTP_TIMEOUT_SEC );
									$ftp_timeout = absint( $ftp_timeout );
									if( $remote_timeout != false && !empty( $ftp_timeout ) ) {
										// Compare the server timeout and the timeout provided at export
										if( $remote_timeout <> $ftp_timeout ) {
											if( @ftp_set_option( $connection, FTP_TIMEOUT_SEC, $ftp_timeout ) == false )
												woo_ce_error_log( sprintf( '%s: Warning: %s', $export->filename, sprintf( __( 'Could not change the FTP server timeout on %s', 'woocommerce-exporter' ), $host ) ) );
										}
									}
									unset( $remote_timeout );
								} else {
									woo_ce_error_log( sprintf( '%s: Warning: %s', 'woocommerce-exporter', $export->filename, sprintf( __( 'We could not change the FTP server timeout on %s as the PHP functions ftp_get_option() and ftp_set_option() are unavailable to WordPress.', 'woocommerce-exporter' ), $host ) ) );
								}
								if( @ftp_login( $connection, $user, $pass ) ) {
									// Check if Transfer Mode is set to Auto/Pasive and if passive mode is available
									if( in_array( $ftp_passive, array( 'auto', 'passive' ) ) ) {
										$features = @ftp_raw( $connection, 'FEAT' );
										if( !empty( $features ) || $ftp_passive == 'passive' ) {
											if( $ftp_passive == 'passive' ) {
												if( @ftp_pasv( $connection, true ) == false )
													woo_ce_error_log( sprintf( '%s: Warning: %s', 'woocommerce-exporter', $export->filename, sprintf( __( 'Could not switch to FTP passive mode on %s', 'woocommerce-exporter' ), $host ) ) );
											} else if( in_array( 'PASV', $features ) ) {
												if( @ftp_pasv( $connection, true ) == false )
													woo_ce_error_log( sprintf( '%s: Warning: %s', 'woocommerce-exporter', $export->filename, sprintf( __( 'Could not switch to FTP passive mode on %s', 'woocommerce-exporter' ), $host ) ) );
											}
										}
										unset( $features );
									}
									unset( $ftp_passive );
									$directory_response = true;
									// Change directory if neccesary
									if( !empty( $path ) ) {
										$current_directory  = @ftp_pwd( $connection );
										if( $current_directory != false ) {
											$directory_response = @ftp_chdir( $connection, $path );
											if( $directory_response == false ) {
												$export->error = sprintf( __( 'Could not change the current directory on the FTP server to %s, check to ensure it exists', 'woocommerce-exporter' ), $path );
												woo_ce_error_log( sprintf( 'Error: %s', $export->error ) );
											}
										} else {
											$directory_response = false;
											$export->error = sprintf( __( 'Could not return the current directory name on the FTP server to %s', 'woocommerce-exporter' ), $path );
											woo_ce_error_log( sprintf( 'Error: %s', $export->error ) );
										}
										unset( $current_directory );
									}

									if( $directory_response ) {
										$connection_response = false;

										// Switch between ftp_put and ftp_fput
										$upload_method = apply_filters( 'woo_ce_cron_export_ftp_switch', 'ftp_put' );

										// Switch between FTP_ASCII or FTP_BINARY
										switch( $ftp_mode ) {

											default:
											case 'ASCII':
												$ftp_mode = FTP_ASCII;
												break;

											case 'BINARY':
												$ftp_mode = FTP_BINARY;
												break;

										}
										$upload_mode = apply_filters( 'woo_ce_cron_export_ftp_mode', $ftp_mode );

										switch( $upload_method ) {
		
											default:
											case 'ftp_put':
												// Check the filepath exists
												if( !empty( $upload['file'] ) ) {
													$connection_response = @ftp_put( $connection, $export->filename, $upload['file'], $upload_mode );
												} else {
													$export->error = sprintf( __( 'Could not upload %s to %s via FTP as the temporary export Post was not created', 'woocommerce-exporter' ), $export->filename, $path );
													woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
												}
												break;
		
											case 'ftp_fput':
												if( !empty( $bits ) ) {
													$handle = fopen( $temp_filename, 'r' );
													$connection_response = @ftp_fput( $connection, $export->filename, $handle, $upload_mode );
													fclose( $handle );
													unset( $handle );
												} else {
													$export->error = sprintf( __( 'Could not upload %s to %s via FTP as the export was empty', 'woocommerce-exporter' ), $export->filename, $path );
													woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
												}
												break;
		
										}
										unset( $upload_method, $upload_mode );

										if( $connection_response ) {
											// Check if this is an encrypted or unsecured FTP connection
											if( $ftp_encryption == 'explicit' ) {
												if( !empty( $path ) )
													woo_ce_error_log( sprintf( '%s: Success: %s', $export->filename, sprintf( __( 'Scheduled export of %s to %s via FTPS uploaded', 'woocommerce-exporter' ), $export->filename, $path ) ) );
												else
													woo_ce_error_log( sprintf( '%s: Success: %s', $export->filename, sprintf( __( 'Scheduled export of %s via FTPS uploaded', 'woocommerce-exporter' ), $export->filename ) ) );
											} else {
												if( !empty( $path ) )
													woo_ce_error_log( sprintf( '%s: Success: %s', $export->filename, sprintf( __( 'Scheduled export of %s to %s via FTP uploaded', 'woocommerce-exporter' ), $export->filename, $path ) ) );
												else
													woo_ce_error_log( sprintf( '%s: Success: %s', $export->filename, sprintf( __( 'Scheduled export of %s via FTP uploaded', 'woocommerce-exporter' ), $export->filename ) ) );
											}
										} else {
											// Check if an error has already been set
											if( !empty( $export->error ) ) {
												woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
											} else {
												if( !empty( $path ) )
													$export->error = sprintf( __( 'There was a problem uploading %s to %s via FTP, response: %s', 'woocommerce-exporter' ), $export->filename, $path, woo_ce_error_get_last_message() );
												else
													$export->error = sprintf( __( 'There was a problem uploading %s via FTP, response: %s', 'woocommerce-exporter' ), $export->filename, woo_ce_error_get_last_message() );
												woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
											}
											$connection_response = @ftp_size( $connection, $export->filename );
											if( $connection_response != -1 ) {
												$connection_response = @ftp_delete( $connection, $export->filename );
												if( $connection_response == false ) {
													if( !empty( $path ) )
														woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, sprintf( __( 'Could not delete failed FTP upload of %s from %s, response: %s', 'woocommerce-exporter' ), $export->filename, $path, woo_ce_error_get_last_message() ) ) );
													else
														woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, sprintf( __( 'Could not delete failed FTP upload of %s, response: %s', 'woocommerce-exporter' ), $export->filename, woo_ce_error_get_last_message() ) ) );
												}
											}
											unset( $connection_response );
										}
									} else {
										woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, __( 'FTP upload was terminated as to the current directory name was not returned', 'woocommerce-exporter' ) ) );
									}
								} else {
									$export->error = sprintf( __( 'Login incorrect for user %s on FTP server at %s, response: %s', 'woocommerce-exporter' ), $user, $host, woo_ce_error_get_last_message() );
									woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
								}
								@ftp_close( $connection );
							} else if( !$ftp_encryption ) {
								$export->error = sprintf( __( 'There was a problem connecting to %s via FTP', 'woocommerce-exporter' ), $host );
								woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
							}
						} else {
							$export->error = __( 'The function ftp_connect() is disabled within your WordPress site, cannot upload to FTP server', 'woocommerce-exporter' );
							woo_ce_error_log( __( '%s: Error: %s', 'woocommerce-exporter' ), $export->filename, $export->error );
						}
						break;

					case 'sftp':
						// Check if ssh2_connect() is available
						if( function_exists( 'ssh2_connect' ) ) {
							if( $connection = @ssh2_connect( $host, $port ) ) {
								if( @ssh2_auth_password( $connection, $user, $pass ) ) {
									// Initialize SFTP subsystem
									if( $session = @ssh2_sftp( $connection ) ) {
										if( $remote_handle = fopen( sprintf( 'ssh2.sftp://%s/%s/%s', $session, $path, $export->filename ), apply_filters( 'woo_ce_cron_export_fopen_mode', 'w+' ) ) ) {
											$handle = fopen( $upload['file'], 'r' );
											$connection_response = ( function_exists( 'stream_copy_to_stream' ) ? stream_copy_to_stream( $handle, $remote_handle ) : false );
											fclose( $handle );
											unset( $handle );
											if( $connection_response == false ) {
												// Check that stream_copy_to_stream() exists, PHP 5-PHP 7
												if( function_exists( 'stream_copy_to_stream' ) ) {
													if( !empty( $path ) )
														$export->error = sprintf( __( 'There was a problem uploading %s to %s via SFTP, response: %s', 'woocommerce-exporter' ), $export->filename, $path, __( 'stream_copy_to_stream() returned false copying data from one stream to another', 'woocommerce-exporter' ) );
													else
														$export->error = sprintf( __( 'There was a problem uploading %s via SFTP, response: %s', 'woocommerce-exporter' ), $export->filename, __( 'stream_copy_to_stream() returned false copying data from one stream to another', 'woocommerce-exporter' ) );
												} else {
													if( !empty( $path ) )
														$export->error = sprintf( __( 'There was a problem uploading %s to %s via SFTP, response: %s', 'woocommerce-exporter' ), $export->filename, $path, __( 'The PHP function stream_copy_to_stream() is required and does not exist', 'woocommerce-exporter' ) );
													else
														$export->error = sprintf( __( 'There was a problem uploading %s via SFTP, response: %s', 'woocommerce-exporter' ), $export->filename, __( 'The PHP function stream_copy_to_stream() is required and does not exist', 'woocommerce-exporter' ) );
												}
												woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
											} else {
												if( !empty( $path ) )
													woo_ce_error_log( sprintf( '%s: Success: %s', $export->filename, sprintf( __( 'Scheduled export of %s to %s via SFTP uploaded', 'woocommerce-exporter' ), $export->filename, $path ) ) );
												else
													woo_ce_error_log( sprintf( '%s: Success: %s', $export->filename, sprintf( __( 'Scheduled export of %s via SFTP uploaded', 'woocommerce-exporter' ), $export->filename ) ) );
											}
											unset( $connection_response );
										} else {
											if( !empty( $path ) )
												$export->error = sprintf( __( 'There was a problem uploading %s to %s via SFTP, response: %s', 'woocommerce-exporter' ), $export->filename, $path, __( 'fopen() failed to return a file pointer resource', 'woocommerce-exporter' ) );
											else
												$export->error = sprintf( __( 'There was a problem uploading %s via SFTP, response: %s', 'woocommerce-exporter' ), $export->filename, __( 'fopen() failed to return a file pointer resource', 'woocommerce-exporter' ) );
											woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
										}
										fclose( $remote_handle );
										unset( $remote_handle );
									} else {
										$export->error = sprintf( __( 'Could not initialize SFTP subsystem on SFTP server at %s', 'woocommerce-exporter' ), $host );
										woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
									}
								} else {
									$export->error = sprintf( __( 'Login incorrect for user %s on SFTP server at %s', 'woocommerce-exporter' ), $user, $host );
									woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
								}
							} else {
								$export->error = sprintf( __( 'There was a problem connecting to %s via SFTP, response: %s', 'woocommerce-exporter' ), $host, woo_ce_error_get_last_message() );
								woo_ce_error_log( sprintf( '%s: Error: %s', $export->filename, $export->error ) );
							}
						} else {
							$export->error = __( 'The function ssh2_connect() is disabled within your WordPress site, cannot upload to SFTP server', 'woocommerce-exporter' );
							woo_ce_error_log( sprintf( __( '%s: Error: %s', 'woocommerce-exporter' ), $export->filename, $export->error ) );
						}
						break;

				}
				// For FTP/SFTP uploads delete the original export file regardless of whether upload was successful or not
				if( isset( $post_ID ) && !empty( $post_ID ) )
					wp_delete_attachment( $post_ID, true );
			}
		}

	}

	// Remove our fatal error notice to play nice with other Plugins
	remove_action( 'shutdown', 'woo_ce_fatal_error' );

	// Only include scheduled exports to the Recent Scheduled Exports list
	if( $export->scheduled_export ) {

		if( !isset( $post_ID ) )
			$post_ID = 0;
		woo_ce_add_recent_scheduled_export( $scheduled_export, $gui, $post_ID );

		// Link the Attachment to the scheduled export
		if( !empty( $post_ID ) )
			update_post_meta( $post_ID, '_scheduled_id', $scheduled_export );

		// Increment the total_exports Post meta on the scheduled export
		$total_exports = absint( get_post_meta( $scheduled_export, '_total_exports', true ) );
		$total_exports++;
		update_post_meta( $scheduled_export, '_total_exports', $total_exports );
		$time = current_time( 'timestamp', 1 );
		update_post_meta( $scheduled_export, '_last_export', $time );

		// The end memory usage and time is collected at the very last opportunity prior to the CRON export process ending
		if( !empty( $post_ID ) ) {
			woo_ce_update_file_detail( $post_ID, '_woo_idle_memory_end', woo_ce_current_memory_usage() );
			woo_ce_update_file_detail( $post_ID, '_woo_end_time', time() );
		}

	}

	delete_option( WOO_CD_PREFIX . '_exported' );

	// If the CRON process gets this far, pass on the good news!
	return true;

}

// Sets the e-mail header to HTML
function woo_ce_set_html_content_type() {

	return 'text/html';

}

function woo_ce_check_cron_export_arguments( $args ) {

	$args->export_format = ( $args->export_format != '' ? $args->export_format : 'csv' );
	$args->limit_volume = ( $args->limit_volume != '' ? $args->limit_volume : -1 );
	$args->offset = ( $args->offset != '' ? $args->offset : 0 );
	if( isset( $args->date_format ) ) {
		$args->date_format = ( $args->date_format != '' ? $args->date_format : 'd/m/Y' );
		// Override for corrupt WordPress option 'date_format' from older releases
		if( $args->date_format == '1' || $args->date_format == '' || $args->date_format == false ) {
			woo_ce_error_log( sprintf( 'Warning: %s', __( 'Date Format export option was corrupted, defaulted to d/m/Y' ) ) );
			$args->date_format = 'd/m/Y';
		}
	}
	// Override for Order Item Types passed via CRON
	if( !empty( $args->order_items_types ) && !is_array( $args->order_items_types ) ) {
		$args->order_items_types = explode( ',', $args->order_items_types );
	} else if( empty( $args->order_items_types ) ) {
		// Override for empty Order Item Types
		$args->order_items_types = array( 'line_item' );
	}
	// Override for empty Export Fields
	if( empty( $args->fields ) ) {
		woo_ce_error_log( sprintf( 'Error: %s', sprintf( __( 'The CRON export validator reported no export fields were selected for Export Type \'%s\', defaulted to all.', 'woocommerce-exporter' ), $args->type ) ) );
		if( function_exists( sprintf( 'woo_ce_get_%s_fields', $args->type ) ) )
			$args->fields = call_user_func_array( 'woo_ce_get_' . $args->type . '_fields', array( 'summary' ) );
	}
	return $args;

}

function woo_ce_cron_export_fields( $export_type = '', $is_scheduled = 0, $scheduled_export = 0 ) {

	global $export;

	$export_fields = 'all';

	// Override the export fields if the single order Transient is set
	$single_export_fields = get_transient( WOO_CD_PREFIX . '_single_export_fields' );
	if( $single_export_fields != false ) {
		$export_fields = $single_export_fields;
	} else {
		if( $is_scheduled == '0' ) {
			$export_fields = woo_ce_get_option( 'cron_fields', 'all' );
			// Override for Export Template in CRON exports
			if( isset( $_GET['export_template'] ) ? absint( $_GET['export_template'] ) : false ) {
				$export_fields = 'template';
				set_transient( WOO_CD_PREFIX . '_single_export_template', $export_template, ( MINUTE_IN_SECONDS * 10 ) );
			}
		} else if( $is_scheduled == '1' ) {
			$export_fields = get_post_meta( $scheduled_export, '_export_fields', true );
		}
	}
	unset( $single_export_fields );

	$fields = array();

	// Default is to show all export fields
	if( function_exists( sprintf( 'woo_ce_get_%s_fields', $export_type ) ) )
		$fields = call_user_func_array( 'woo_ce_get_' . $export_type . '_fields', array( 'summary' ) );
	switch( $export_fields ) {

		case 'saved':
			// Get stored export field preference for that export type from the Quick Export screen
			$meta_value = woo_ce_get_option( $export_type . '_fields', array() );
			if( $meta_value != false )
				$fields = $meta_value;
			else
				woo_ce_error_log( sprintf( 'Warning: %s', sprintf( __( 'No default export fields were returned from the Quick Export screen for Export Type \'%s\', defaulting to all export fields.', 'woocommerce-exporter' ), $export_type ) ) );
			unset( $meta_value );
			break;

		case 'template':
			if( $is_scheduled == '0' ) {
				$export_template = woo_ce_get_option( 'cron_export_template', false );
				// Override the export fields if the single order Transient is set
				if( get_transient( WOO_CD_PREFIX . '_single_export_template' ) != false ) {
					$export_template = get_transient( WOO_CD_PREFIX . '_single_export_template' );
					delete_transient( WOO_CD_PREFIX . '_single_export_template' );
				}
				if( $export_template != false ) {
					// Fetch the export field preference for that export type from the Export Template
					$meta_value = get_post_meta( $export_template, '_' . $export_type . '_fields', true );
					if( $meta_value == false || $meta_value == '' )
						woo_ce_error_log( sprintf( 'Warning: %s', sprintf( __( 'No saved export fields were returned for Export Type \'%s\' from the Export Template with Post ID #%d, defaulting to all export fields.', 'woocommerce-exporter' ), $export_type, $export_template ) ) );
					else
						$fields = $meta_value;
					unset( $meta_value );
				} else {
					woo_ce_error_log( sprintf( 'Warning: %s', __( 'No Export Template option was set for the Orders screen export action, defaulting to all export fields.', 'woocommerce-exporter' ) ) );
				}
			} else if( $is_scheduled == '1' ) {
				// Check if a Export Template has been assigned to this Scheduled Export
				$export_template = get_post_meta( $scheduled_export, '_export_template', true );
				if( $export_template != false ) {
					// Fetch the export field preference for that export type from the Export Template
					$meta_value = get_post_meta( $export_template, '_' . $export_type . '_fields', true );
					if( $meta_value == false || $meta_value == '' )
						woo_ce_error_log( sprintf( 'Warning: %s', sprintf( __( 'No saved export fields were returned for Export Type \'%s\' from the Export Template with Post ID #%d, defaulting to all export fields.', 'woocommerce-exporter' ), $export_type, $export_template ) ) );
					else
						$fields = $meta_value;
					unset( $meta_value );
				} else {
					woo_ce_error_log( sprintf( 'Warning: %s', sprintf( __( 'No Export Template option was set for the Scheduled Export with Post ID #%d, defaulting to all export fields.', 'woocommerce-exporter' ), $scheduled_export ) ) );
				}
			}
			unset( $export_template );
			break;

	}

	return $fields;

}

function woo_ce_cron_email_subject( $type = '', $filename = '' ) {

	global $export;

	$scheduled_export = ( $export->scheduled_export ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );

	$subject = '';
	if( !empty( $scheduled_export ) ) {
		$subject = get_post_meta( $scheduled_export, '_method_email_subject', true );
		// Default subject
		if( empty( $subject ) )
			$subject = apply_filters( 'woo_ce_default_email_subject', __( '[%store_name%] Export: %export_type% (%export_filename%)', 'woocommerce-exporter' ), $scheduled_export );
	} else {
		// Override the e-mail subject if the single order Transient is set
		$single_export_method_email_subject = get_transient( WOO_CD_PREFIX . '_single_export_method_email_subject' );
		if( $single_export_method_email_subject != false )
			$subject = $single_export_method_email_subject;
		unset( $single_export_method_email_subject );
		// Default subject
		if( empty( $subject ) )
			$subject = apply_filters( 'woo_ce_default_email_subject', __( '[%store_name%] Export: %export_type% (%export_filename%)', 'woocommerce-exporter' ) );
	}
	$subject = str_replace( '%store_name%', sanitize_title( get_bloginfo( 'name' ) ), $subject );
	$subject = str_replace( '%export_type%', ucwords( $type ), $subject );
	$subject = str_replace( '%export_filename%', $filename, $subject );

	return $subject;

}

function woo_ce_cron_email_contents( $type = '', $filename = '' ) {

	global $export;

	// Set the default e-mail contents
	$contents = '';

	$scheduled_export = ( $export->scheduled_export ? absint( get_transient( WOO_CD_PREFIX . '_scheduled_export_id' ) ) : 0 );
	if( $scheduled_export ) {
		$contents = get_post_meta( $scheduled_export, '_method_email_contents', true );
		// Default e-mail contents
		if( empty( $contents ) )
			$contents = apply_filters( 'woo_ce_default_email_contents', wpautop( __( 'Please find attached your export ready to review.', 'woocommerce-exporter' ) ), $scheduled_export );
	} else {
		// Override the e-mail contents if the single order Transient is set
		$single_export_method_email_contents = get_transient( WOO_CD_PREFIX . '_single_export_method_email_contents' );
		if( $single_export_method_email_contents != false )
			$contents = $single_export_method_email_contents;
		unset( $single_export_method_email_contents );
		// Default e-mail contents
		if( empty( $contents ) )
			$contents = apply_filters( 'woo_ce_default_email_contents', wpautop( __( 'Please find attached your export ready to review.', 'woocommerce-exporter' ) ) );
	}
	$contents = str_replace( '%store_name%', sanitize_title( get_bloginfo( 'name' ) ), $contents );
	$contents = str_replace( '%export_type%', ucwords( $type ), $contents );
	$contents = str_replace( '%export_filename%', $filename, $contents );
	$contents = apply_filters( 'woo_ce_email_contents', $contents );

	return $contents;

}

function woo_ce_trigger_new_order_export( $order_id = 0 ) {

	global $export;

	if( !empty( $order_id ) ) {
		$is_scheduled = false;
		$export_format = apply_filters( 'woo_ce_trigger_new_order_export_format', woo_ce_get_option( 'trigger_new_order_format', 'csv' ) );
		$export_method = apply_filters( 'woo_ce_trigger_new_order_export_method', woo_ce_get_option( 'trigger_new_order_method', 'archive' ) );
		$order_items_formatting = apply_filters( 'woo_ce_trigger_new_order_items_formatting', woo_ce_get_option( 'trigger_new_order_items_formatting', 'unique' ) );
		$export_fields = woo_ce_get_option( 'trigger_new_order_fields', 'all' );
		set_transient( WOO_CD_PREFIX . '_single_export_post_ids', absint( $order_id ), ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_format', sanitize_text_field( $export_format ), ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_method', sanitize_text_field( $export_method ), ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting', sanitize_text_field( $order_items_formatting ), ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_fields', sanitize_text_field( $export_fields ), ( MINUTE_IN_SECONDS * 10 ) );
		switch( $export_method ) {

			case 'ftp':
				// Override to force the Export Trigger to use a Scheduled Export's FTP rules
				$scheduled_export = apply_filters( 'woo_ce_trigger_new_order_export_scheduled_export', false );
				if( !empty( $scheduled_export ) ) {
					$is_scheduled = true;
					$export->scheduled_export = 1;
					set_transient( WOO_CD_PREFIX . '_scheduled_export_id', $scheduled_export, ( MINUTE_IN_SECONDS * 10 ) );
				}
				break;

			case 'email':
				$export_method_email_to = woo_ce_get_option( 'trigger_new_order_method_email_to', 'archive' );
				$export_method_email_subject = woo_ce_get_option( 'trigger_new_order_method_email_subject', 'archive' );
				set_transient( WOO_CD_PREFIX . '_single_export_method_email_to', sanitize_text_field( $export_method_email_to ), ( MINUTE_IN_SECONDS * 10 ) );
				set_transient( WOO_CD_PREFIX . '_single_export_method_email_subject', sanitize_text_field( $export_method_email_subject ), ( MINUTE_IN_SECONDS * 10 ) );
				break;

		}
		$export_type = 'order';
		if( woo_ce_cron_export( $export_method, $export_type, array( 'is_scheduled' => $is_scheduled ) ) ) {
			switch( $export_method ) {

				case 'archive':
					woo_ce_error_log( sprintf( '%s: Success: %s', $export->filename, sprintf( __( 'New Order #%d export saved to WordPress Media', 'woocommerce-exporter' ), $order_id ) ) );
					break;

				case 'email':
					woo_ce_error_log( sprintf( '%s: Success: %s', $export->filename, sprintf( __( 'New Order #%d export sent via e-mail', 'woocommerce-exporter' ), $order_id ) ) );
					break;

			}
		}
		delete_transient( WOO_CD_PREFIX . '_single_export_post_ids' );
		delete_transient( WOO_CD_PREFIX . '_single_export_format' );
		delete_transient( WOO_CD_PREFIX . '_single_export_method' );
		delete_transient( WOO_CD_PREFIX . '_single_export_method_email_to' );
		delete_transient( WOO_CD_PREFIX . '_single_export_method_email_subjec' );
		delete_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting' );
		delete_transient( WOO_CD_PREFIX . '_single_export_fields' );
	}

}
?>