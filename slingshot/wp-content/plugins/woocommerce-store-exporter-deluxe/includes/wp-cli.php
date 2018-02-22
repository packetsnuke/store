<?php
/**
 * Manage Quick Exports and Scheduled Exports within WooCommerce - Store Exporter Deluxe.
 *
 * @package woo_cd
 * @subpackage commands/community
 * @maintainer Visser Labs
 */
class Store_Export_Command extends WP_CLI_Command {

  /**
   * List Scheduled Exports.
   *
   * ## OPTIONS
   *
   * [--export_type=<type>]
   * : Accepted values: product, category, tag, order, user, etc. Default: all
   *
   * [--export_format=<format>]
   * : Accepted values: csv, tsv, xls, xlsx, xml, rss. Default: all
   *
   * [--export_method=<method>]
   * : Accepted values: archive, save, email, post, ftp, raw. Default: all
   *
   * ## EXAMPLES
   *
   *     wp store-export list --export_format=csv
   *     wp store-export list --export_format=csv --export_type=product
   *     wp store-export list --export_format=csv --export_type=product --export_method=email
   *
   * @subcommand list
   */
	function _list( $args, $assoc_args ) {

		$filter_export_type = ( !empty( $assoc_args['export_type'] ) ? $assoc_args['export_type'] : false );
		$filter_export_format = ( !empty( $assoc_args['export_format'] ) ? $assoc_args['export_format'] : false );
		$filter_export_method = ( !empty( $assoc_args['export_method'] ) ? $assoc_args['export_method'] : false );

		$args = array();
		$args['meta_query'] = array();
		if( !empty( $filter_export_type ) ) {
			$args['meta_query'][] = array(
				'key' => '_export_type',
				'value' => $filter_export_type
			);
		}
		if( !empty( $filter_export_format ) ) {
			$args['meta_query'][] = array(
				'key' => '_export_format',
				'value' => $filter_export_format
			);
		}
		if( !empty( $filter_export_method ) ) {
			$args['meta_query'][] = array(
				'key' => '_export_method',
				'value' => $filter_export_method
			);
		}

		$scheduled_exports = woo_ce_get_scheduled_exports( $args );
		if( !empty( $scheduled_exports ) ) {
			$format = 'table';
			$items = array();
			foreach( $scheduled_exports as $scheduled_export ) {
				$items[] = array(
					'post_id' => $scheduled_export,
					'post_title' => get_the_title( $scheduled_export ),
					'export_type' => get_post_meta( $scheduled_export, '_export_type', true ),
					'export_format' => get_post_meta( $scheduled_export, '_export_format', true ),
					'export_method' => get_post_meta( $scheduled_export, '_export_method', true )
				);
			}
			$fields = array( 'post_id', 'post_title', 'export_type', 'export_format', 'export_method' );
			WP_CLI\Utils\format_items( $format, $items, $fields );
			exit();
		} else {
			WP_CLI::error( 'No Scheduled Exports were found...' );
			exit();
		}

	}

  /**
   * Manually execute a Scheduled Export.
   *
   * ## OPTIONS
   *
   * [--scheduled_id=<post_id>]
   * : Accepted values: post_id. Default: empty
   *
   * ## EXAMPLES
   *
   *     wp store-export scheduled_export --scheduled_id=1000
   *
   * @subcommand scheduled_export
   */
	function scheduled_export( $args, $assoc_args ) {

		$scheduled_export = ( !empty( $assoc_args['scheduled_id'] ) ? $assoc_args['scheduled_id'] : false );
		// Check if a Post ID has been provided
		if( empty( $scheduled_export ) ) {
			WP_CLI::error( 'No Post ID specifying the Scheduled Export was provided...' );
			exit();
		}

		// Check that the Post ID is a Scheduled Export
		$post_type = 'scheduled_export';
		if( get_post_type( $scheduled_export ) <> $post_type ) {
			WP_CLI::error( 'The specified Post ID was not a Scheduled Export...' );
			exit();
		}

		$title = get_the_title( $scheduled_export );

		WP_CLI::line( sprintf( 'Running Scheduled Export "%s"...', $title ) );

		$start_time = time();

		$args = sprintf( '%d+', $scheduled_export );
		woo_ce_auto_export( $args );

		$end_time = time();
		$time_taken = ( $end_time - $start_time );

		WP_CLI::success( sprintf( 'Scheduled Export of "%s" has completed. Time taken: %s second(s)', $title, $time_taken ) );
		exit();

	}

  /**
   * Manually execute a Quick Export.
   *
   * ## OPTIONS
   *
   * [--export_type=<type>]
   * : Accepted values: product, category, tag, order, user, etc. Default: all
   *
   * [--export_format=<type>]
   * : Accepted values: csv, tsv, xls, xlsx, xml, rss. Default: csv
   *
   * ## EXAMPLES
   *
   *     wp store-export quick_export
   *     wp store-export quick_export --export_type=product
   *
   * @subcommand quick_export
   */
	function quick_export( $args, $assoc_args ) {

		$export_type = ( !empty( $assoc_args['export_type'] ) ? $assoc_args['export_type'] : false );
		// Check if a Export Type has been provided
		if( empty( $export_type ) ) {
			WP_CLI::error( 'No Export Type was provided...' );
			exit();
		}

		$export_format = ( !empty( $assoc_args['export_format'] ) ? $assoc_args['export_format'] : false );
		// Check if a Export Type has been provided
		if( empty( $export_format ) ) {
			$export_format = woo_ce_get_option( 'export_format', 'csv' );
			WP_CLI::warning( sprintf( 'No Export Type was provided, defaulting to %s', $export_format ) );
		}

		WP_CLI::line( 'Running Quick Export...' );

		$start_time = time();

		// Set up our export
		$gui = 'archive';
		$args = array(
			'is_cli' => 1
		);
		set_transient( WOO_CD_PREFIX . '_single_export_format', $export_format, ( MINUTE_IN_SECONDS * 10 ) );
		$response = woo_ce_cron_export( $gui, $export_type, $args );

		// Clean up
		delete_transient( WOO_CD_PREFIX . '_single_export_format' );

		$end_time = time();
		$time_taken = ( $end_time - $start_time );

		WP_CLI::success( sprintf( 'Quick Export has completed. Time taken: %s second(s)', $time_taken ) );
		exit();

	}

}
WP_CLI::add_command( 'store-export', 'Store_Export_Command' );
?>