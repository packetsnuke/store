<?php
// Display admin notice on screen load
function woo_cd_admin_notice( $message = '', $priority = 'updated', $screen = '' ) {

	if( $priority == false || $priority == '' )
		$priority = 'updated';
	if( $message <> '' ) {
		ob_start();
		woo_cd_admin_notice_html( $message, $priority, $screen );
		$output = ob_get_contents();
		ob_end_clean();
		// Check if an existing notice is already in queue
		$existing_notice = get_transient( WOO_CD_PREFIX . '_notice' );
		if( $existing_notice !== false ) {
			$existing_notice = base64_decode( $existing_notice );
			$output = $existing_notice . $output;
		}
		$response = set_transient( WOO_CD_PREFIX . '_notice', base64_encode( $output ), DAY_IN_SECONDS );
		// Check if the Transient was saved
		if( $response !== false )
			add_action( 'admin_notices', 'woo_cd_admin_notice_print' );
	}

}

// HTML template for admin notice
function woo_cd_admin_notice_html( $message = '', $priority = 'updated', $screen = '', $id = '' ) {

	// Default priority to updated
	if( empty( $priority ) )
		$priority = 'updated';
	// Display admin notice on specific screen
	if( !empty( $screen ) ) {

		global $pagenow;

		if( is_array( $screen ) ) {
			if( in_array( $pagenow, $screen ) == false )
				return;
		} else {
			if( $pagenow <> $screen )
				return;
		}

	}
	// Override for WooCommerce notice styling
	if( $priority == 'notice' )
		$priority = 'updated woocommerce-message'; ?>
<div id="<?php echo ( !empty( $id ) ? sprintf( 'message-%s', $id ) : 'message' ); ?>" class="<?php echo $priority; ?>">
	<p><?php echo $message; ?></p>
</div>
<?php

}

// Grabs the WordPress transient that holds the admin notice and prints it
function woo_cd_admin_notice_print() {

	$output = get_transient( WOO_CD_PREFIX . '_notice' );
	if( $output !== false ) {
		delete_transient( WOO_CD_PREFIX . '_notice' );
		$output = base64_decode( $output );
		echo $output;
	}

}

// HTML template header on Store Exporter screen
function woo_cd_template_header( $title = '', $icon = 'woocommerce' ) {

	if( $title )
		$output = $title;
	else
		$output = __( 'Store Export', 'woocommerce-exporter' ); ?>
<div id="woo-ce" class="wrap">
	<div id="icon-<?php echo $icon; ?>" class="icon32 icon32-woocommerce-importer"><br /></div>
	<h2>
		<?php echo $output; ?>
	</h2>
<?php

}

// HTML template footer on Store Exporter screen
function woo_cd_template_footer() { ?>
</div>
<!-- .wrap -->
<?php

}

function woo_cd_template_header_title() {

	return __( 'Store Exporter Deluxe', 'woocommerce-exporter' );

}
add_filter( 'woo_ce_template_header', 'woo_cd_template_header_title' );

function woo_ce_quick_export_in_process() {

	$notice_timeout = apply_filters( 'woo_ce_quick_export_in_process_notice_timeout', 10 );
	$message = sprintf( __( 'Your Quick Export is now running and a export download will be delivered in a moment. This notice will hide automatically in %d seconds.', 'woocommerce-exporter' ), $notice_timeout );

	// Allow Plugin/Theme authors to adjust this message
	$message = apply_filters( 'woo_ce_quick_export_in_process_message', $message );

	woo_cd_admin_notice_html( $message, false, false, 'quick_export' );

}
add_action( 'woo_ce_export_before_options', 'woo_ce_quick_export_in_process' );

function woo_ce_ajax_load_export_template() {

	$export_template = ( isset( $_POST['export_template'] ) ? absint( $_POST['export_template'] ) : false );
	if( empty( $export_template ) )
		return;

	$response = false;

	$args = array(
		'post__in' => array( $export_template )
	);
	$export_template = woo_ce_get_export_templates( $args );
	if( !empty( $export_template ) ) {
		$export_template = $export_template[0];
		$response = array();
		$export_types = woo_ce_get_export_types();
		if( !empty( $export_types ) ) {
			$export_types = array_keys( $export_types );
			foreach( $export_types as $export_type ) {
				// Fields
				$export_fields = get_post_meta( $export_template, sprintf( '_%s_fields', $export_type ), true );
				if( empty( $export_fields ) )
					$export_fields = array();
				$response[$export_type]['fields'] = $export_fields;
				// Sorting
				$export_sorting = get_post_meta( $export_template, sprintf( '_%s_sorting', $export_type ), true );
				if( empty( $export_sorting ) )
					$export_sorting = array();
				$response[$export_type]['sorting'] = $export_sorting;
				// Label
				$export_labels = get_post_meta( $export_template, sprintf( '_%s_labels', $export_type ), true );
				if( empty( $export_labels ) )
					$export_labels = array();
				$response[$export_type]['label'] = $export_labels;
			}
		}
	}

	$response = json_encode( $response );
	echo $response;
	wp_die();

}

function woo_ce_ajax_override_scheduled_export() {

	$response = false;

	$scheduled_export = ( isset( $_POST['scheduled_export'] ) ? absint( $_POST['scheduled_export'] ) : false );
	if( !empty( $scheduled_export ) ) {

		// Check if DISABLE_WP_CRON has been switched on
		$has_cron = true;
		if( defined( 'DISABLE_WP_CRON' ) ) {
			$disabled = DISABLE_WP_CRON;
			$has_cron = ( $disabled ? false : true );
		}

		if( WOO_CD_LOGGING )
			woo_ce_error_log( sprintf( 'Debug: %s', 'override_scheduled_export - has_cron: ' . absint( $has_cron ) ) );

		if( !apply_filters( 'woo_ce_ajax_override_scheduled_export_manual', $has_cron ) ) {
			// Run this export immediately

			if( WOO_CD_LOGGING )
				woo_ce_error_log( sprintf( 'Debug: %s', 'override_scheduled_export - running export' ) );

			$args = sprintf( '%d+', $scheduled_export );
			woo_ce_auto_export( $args );

			if( WOO_CD_LOGGING ) {
				woo_ce_error_log( sprintf( 'Debug: %s', 'override_scheduled_export - args: ' . print_r( $args, true ) ) );
			}
			$response = true;

		} else {
			// Create a single WP-CRON event that runs on next sweep...

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
			$response = true;
		}

	}

	$response = json_encode( $response );
	echo $response;
	wp_die();

}

// Add Export, Docs and Support links to the Plugins screen
function woo_cd_add_settings_link( $links, $file ) {

	// Manually force slug
	$this_plugin = WOO_CD_RELPATH;

	if( $file == $this_plugin ) {
		$support_url = 'http://www.visser.com.au/premium-support/';
		$support_link = sprintf( '<a href="%s" target="_blank">' . __( 'Support', 'woocommerce-exporter' ) . '</a>', $support_url );
		$docs_url = 'http://www.visser.com.au/docs/';
		$docs_link = sprintf( '<a href="%s" target="_blank">' . __( 'Docs', 'woocommerce-exporter' ) . '</a>', $docs_url );
		$export_link = sprintf( '<a href="%s">' . __( 'Export', 'woocommerce-exporter' ) . '</a>', esc_url( add_query_arg( 'page', 'woo_ce', 'admin.php' ) ) );
		array_unshift( $links, $support_link );
		array_unshift( $links, $docs_link );
		array_unshift( $links, $export_link );
	}
	return $links;

}
add_filter( 'plugin_action_links', 'woo_cd_add_settings_link', 10, 2 );

function woo_ce_admin_custom_fields_save() {

	// Save Custom Product Meta
	if( isset( $_POST['custom_products'] ) ) {
		$custom_products = $_POST['custom_products'];
		$custom_products = explode( "\n", trim( $custom_products ) );
		if( !empty( $custom_products ) ) {
			$size = count( $custom_products );
			if( !empty( $size ) ) {
				for( $i = 0; $i < $size; $i++ )
					$custom_products[$i] = sanitize_text_field( trim( stripslashes( $custom_products[$i] ) ) );
				woo_ce_update_option( 'custom_products', $custom_products );
			}
		} else {
			woo_ce_update_option( 'custom_products', '' );
		}
		unset( $custom_products );
	}

	// Save Custom Attributes
	if( isset( $_POST['custom_attributes'] ) ) {
		$custom_attributes = $_POST['custom_attributes'];
		$custom_attributes = explode( "\n", trim( $custom_attributes ) );
		if( !empty( $custom_attributes ) ) {
			$size = count( $custom_attributes );
			if( !empty( $size ) ) {
				for( $i = 0; $i < $size; $i++ )
					$custom_attributes[$i] = sanitize_text_field( trim( stripslashes( $custom_attributes[$i] ) ) );
				woo_ce_update_option( 'custom_attributes', $custom_attributes );
			}
		} else {
			woo_ce_update_option( 'custom_attributes', '' );
		}
	}

	// Save Custom Product Add-ons
	if( isset( $_POST['custom_product_addons'] ) ) {
		$custom_product_addons = $_POST['custom_product_addons'];
		$custom_product_addons = explode( "\n", trim( $custom_product_addons ) );
		if( !empty( $custom_product_addons ) ) {
			$size = count( $custom_product_addons );
			if( !empty( $size ) ) {
				for( $i = 0; $i < $size; $i++ )
					$custom_product_addons[$i] = sanitize_text_field( trim( stripslashes( $custom_product_addons[$i] ) ) );
				woo_ce_update_option( 'custom_product_addons', $custom_product_addons );
			}
		} else {
			woo_ce_update_option( 'custom_product_addons', '' );
		}
		unset( $custom_product_addons );
	}

	// Save Custom Extra Product Options
	if( isset( $_POST['custom_extra_product_options'] ) ) {
		$custom_extra_product_options = $_POST['custom_extra_product_options'];
		$custom_extra_product_options = explode( "\n", trim( $custom_extra_product_options ) );
		if( !empty( $custom_extra_product_options ) ) {
			$size = count( $custom_extra_product_options );
			if( !empty( $size ) ) {
				for( $i = 0; $i < $size; $i++ )
					$custom_extra_product_options[$i] = sanitize_text_field( trim( stripslashes( $custom_extra_product_options[$i] ) ) );
				woo_ce_update_option( 'custom_extra_product_options', $custom_extra_product_options );
			}
		} else {
			woo_ce_update_option( 'custom_extra_product_options', '' );
		}
		unset( $custom_extra_product_options );
	}

	// Save Custom Product Tabs
	if( isset( $_POST['custom_product_tabs'] ) ) {
		$custom_product_tabs = $_POST['custom_product_tabs'];
		$custom_product_tabs = explode( "\n", trim( $custom_product_tabs ) );
		if( !empty( $custom_product_tabs ) ) {
			$size = count( $custom_product_tabs );
			if( !empty( $size ) ) {
				for( $i = 0; $i < $size; $i++ )
					$custom_product_tabs[$i] = sanitize_text_field( trim( stripslashes( $custom_product_tabs[$i] ) ) );
				woo_ce_update_option( 'custom_product_tabs', $custom_product_tabs );
			}
		} else {
			woo_ce_update_option( 'custom_product_tabs', '' );
		}
		unset( $custom_product_tabs );
	}

	// Save Custom WooTabs
	if( isset( $_POST['custom_wootabs'] ) ) {
		$custom_wootabs = $_POST['custom_wootabs'];
		$custom_wootabs = explode( "\n", trim( $custom_wootabs ) );
		if( !empty( $custom_wootabs ) ) {
			$size = count( $custom_wootabs );
			if( !empty( $size ) ) {
				for( $i = 0; $i < $size; $i++ )
					$custom_wootabs[$i] = sanitize_text_field( trim( stripslashes( $custom_wootabs[$i] ) ) );
				woo_ce_update_option( 'custom_wootabs', $custom_wootabs );
			}
		} else {
			woo_ce_update_option( 'custom_wootabs', '' );
		}
		unset( $custom_wootabs );
	}

	// Save Custom Order meta
	if( isset( $_POST['custom_orders'] ) ) {
		$custom_orders = $_POST['custom_orders'];
		if( !empty( $custom_orders ) ) {
			$custom_orders = explode( "\n", trim( $custom_orders ) );
			$size = count( $custom_orders );
			if( $size ) {
				for( $i = 0; $i < $size; $i++ )
					$custom_orders[$i] = sanitize_text_field( trim( stripslashes( $custom_orders[$i] ) ) );
				woo_ce_update_option( 'custom_orders', $custom_orders );
			}
		} else {
			woo_ce_update_option( 'custom_orders', '' );
		}
		unset( $custom_orders );
	}

	// Save Custom Order Item meta
	if( isset( $_POST['custom_order_items'] ) ) {
		$custom_order_items = $_POST['custom_order_items'];
		if( !empty( $custom_order_items ) ) {
			$custom_order_items = explode( "\n", trim( $custom_order_items ) );
			$size = count( $custom_order_items );
			if( $size ) {
				for( $i = 0; $i < $size; $i++ )
					$custom_order_items[$i] = sanitize_text_field( trim( stripslashes( $custom_order_items[$i] ) ) );
				woo_ce_update_option( 'custom_order_items', $custom_order_items );
			}
		} else {
			woo_ce_update_option( 'custom_order_items', '' );
		}
		unset( $custom_order_items );
	}

	// Save Custom Product Order Item meta
	if( isset( $_POST['custom_order_products'] ) ) {
		$custom_order_products = $_POST['custom_order_products'];
		if( !empty( $custom_order_products ) ) {
			$custom_order_products = explode( "\n", trim( $custom_order_products ) );
			$size = count( $custom_order_products );
			if( $size ) {
				for( $i = 0; $i < $size; $i++ )
					$custom_order_products[$i] = sanitize_text_field( trim( stripslashes( $custom_order_products[$i] ) ) );
				woo_ce_update_option( 'custom_order_products', $custom_order_products );
			}
		} else {
			woo_ce_update_option( 'custom_order_products', '' );
		}
		unset( $custom_order_products );
	}

	// Save Custom Subscription meta
	if( isset( $_POST['custom_subscriptions'] ) ) {
		$custom_subscriptions = $_POST['custom_subscriptions'];
		if( !empty( $custom_subscriptions ) ) {
			$custom_subscriptions = explode( "\n", trim( $custom_subscriptions ) );
			$size = count( $custom_subscriptions );
			if( $size ) {
				for( $i = 0; $i < $size; $i++ )
					$custom_subscriptions[$i] = sanitize_text_field( trim( stripslashes( $custom_subscriptions[$i] ) ) );
				woo_ce_update_option( 'custom_subscriptions', $custom_subscriptions );
			}
		} else {
			woo_ce_update_option( 'custom_subscriptions', '' );
		}
		unset( $custom_subscriptions );
	}

	// Save Custom User meta
	if( isset( $_POST['custom_users'] ) ) {
		$custom_users = $_POST['custom_users'];
		if( !empty( $custom_users ) ) {
			$custom_users = explode( "\n", trim( $custom_users ) );
			$size = count( $custom_users );
			if( $size ) {
				for( $i = 0; $i < $size; $i++ )
					$custom_users[$i] = sanitize_text_field( trim( stripslashes( $custom_users[$i] ) ) );
				woo_ce_update_option( 'custom_users', $custom_users );
			}
		} else {
			woo_ce_update_option( 'custom_users', '' );
		}
		unset( $custom_users );
	}

	// Save Custom Customer meta
	if( isset( $_POST['custom_customers'] ) ) {
		$custom_customers = $_POST['custom_customers'];
		if( !empty( $custom_customers ) ) {
			$custom_customers = explode( "\n", trim( $custom_customers ) );
			$size = count( $custom_customers );
			if( $size ) {
				for( $i = 0; $i < $size; $i++ )
					$custom_customers[$i] = sanitize_text_field( trim( stripslashes( $custom_customers[$i] ) ) );
				woo_ce_update_option( 'custom_customers', $custom_customers );
			}
		} else {
			woo_ce_update_option( 'custom_customers', '' );
		}
		unset( $custom_customers );
	}

}

function woo_ce_admin_order_column_headers( $columns ) {

	// Check if another Plugin has registered this column
	if( !isset( $columns['woo_ce_export_status'] ) ) {
		$pos = array_search( 'order_title', array_keys( $columns ) );
		$columns = array_merge(
			array_slice( $columns, 0, $pos ),
			array( 'woo_ce_export_status' => __( 'Export Status', 'woocommerce-exporter' ) ),
			array_slice( $columns, $pos )
		);
	}
	return $columns;

}

function woo_ce_admin_order_column_content( $column ) {

	global $post;

	if( $column == 'woo_ce_export_status' ) {
		if( $is_exported = ( get_post_meta( $post->ID, '_woo_cd_exported', true ) ? true : false ) ) {
			printf( '<mark title="%s" class="%s">%s</mark>', __( 'This Order has been exported and will not be included in future exports filtered by \'Since last export\'.', 'woocommerce-exporter' ), 'csv_exported', __( 'Exported', 'woocommerce-exporter' ) );
		} else {
			printf( '<mark title="%s" class="%s">%s</mark>', __( 'This Order has not yet been exported using the \'Since last export\' Order Date filter.', 'woocommerce-exporter' ), 'csv_not_exported', __( 'Not Exported', 'woocommerce-exporter' ) );
		}

		// Allow Plugin/Theme authors to add their own content within this column
		do_action( 'woo_ce_admin_order_column_content', $post->ID );

	}

}

// Display bulk export actions on the Products and Orders screen
function woo_ce_admin_export_bulk_actions() {

	$screen = get_current_screen();
	$screen_id = $screen->id;

	// Check if this is the Orders screen
	if( $screen_id == 'edit-shop_order' ) {

		// In-line javascript
		ob_start(); ?>
<script type="text/javascript">
jQuery(function() {
<?php if( woo_ce_get_option( 'order_actions_csv', 1 ) ) { ?>
	jQuery('<option>').val('download_csv').text('<?php _e( 'Download as CSV', 'woocommerce-exporter' )?>').appendTo("select[name='action']");
	jQuery('<option>').val('download_csv').text('<?php _e( 'Download as CSV', 'woocommerce-exporter' )?>').appendTo("select[name='action2']");

<?php } ?>
<?php if( woo_ce_get_option( 'order_actions_tsv', 1 ) ) { ?>
	jQuery('<option>').val('download_tsv').text('<?php _e( 'Download as TSV', 'woocommerce-exporter' )?>').appendTo("select[name='action']");
	jQuery('<option>').val('download_tsv').text('<?php _e( 'Download as TSV', 'woocommerce-exporter' )?>').appendTo("select[name='action2']");

<?php } ?>
<?php if( woo_ce_get_option( 'order_actions_xls', 1 ) ) { ?>
	jQuery('<option>').val('download_xls').text('<?php _e( 'Download as XLS', 'woocommerce-exporter' )?>').appendTo("select[name='action']");
	jQuery('<option>').val('download_xls').text('<?php _e( 'Download as XLS', 'woocommerce-exporter' )?>').appendTo("select[name='action2']");

<?php } ?>
<?php if( woo_ce_get_option( 'order_actions_xlsx', 1 ) ) { ?>
	jQuery('<option>').val('download_xlsx').text('<?php _e( 'Download as XLSX', 'woocommerce-exporter' )?>').appendTo("select[name='action']");
	jQuery('<option>').val('download_xlsx').text('<?php _e( 'Download as XLSX', 'woocommerce-exporter' )?>').appendTo("select[name='action2']");

<?php } ?>
<?php if( woo_ce_get_option( 'order_actions_xml', 1 ) ) { ?>
	jQuery('<option>').val('download_xml').text('<?php _e( 'Download as XML', 'woocommerce-exporter' )?>').appendTo("select[name='action']");
	jQuery('<option>').val('download_xml').text('<?php _e( 'Download as XML', 'woocommerce-exporter' )?>').appendTo("select[name='action2']");

<?php } ?>
<?php if( apply_filters( 'woo_ce_admin_bulk_actions_hide_remove_export_flag', false ) == false ) { ?>
	jQuery('<option>').val('unflag_export').text('<?php _e( 'Remove export flag', 'woocommerce-exporter' )?>').appendTo("select[name='action']");
	jQuery('<option>').val('unflag_export').text('<?php _e( 'Remove export flag', 'woocommerce-exporter' )?>').appendTo("select[name='action2']");
<?php } ?>
});
</script>
<?php
		ob_end_flush();

	// Check if this is the Products screen
	} else if( $screen_id == 'edit-product' ) {

		// In-line javascript
		ob_start(); ?>
<script type="text/javascript">
jQuery(function() {
	jQuery('<option>').val('download_csv').text('<?php _e( 'Download as CSV', 'woocommerce-exporter' )?>').appendTo("select[name='action']");
	jQuery('<option>').val('download_csv').text('<?php _e( 'Download as CSV', 'woocommerce-exporter' )?>').appendTo("select[name='action2']");

	jQuery('<option>').val('download_tsv').text('<?php _e( 'Download as TSV', 'woocommerce-exporter' )?>').appendTo("select[name='action']");
	jQuery('<option>').val('download_tsv').text('<?php _e( 'Download as TSV', 'woocommerce-exporter' )?>').appendTo("select[name='action2']");

	jQuery('<option>').val('download_xls').text('<?php _e( 'Download as XLS', 'woocommerce-exporter' )?>').appendTo("select[name='action']");
	jQuery('<option>').val('download_xls').text('<?php _e( 'Download as XLS', 'woocommerce-exporter' )?>').appendTo("select[name='action2']");

	jQuery('<option>').val('download_xlsx').text('<?php _e( 'Download as XLSX', 'woocommerce-exporter' )?>').appendTo("select[name='action']");
	jQuery('<option>').val('download_xlsx').text('<?php _e( 'Download as XLSX', 'woocommerce-exporter' )?>').appendTo("select[name='action2']");

	jQuery('<option>').val('download_xml').text('<?php _e( 'Download as XML', 'woocommerce-exporter' )?>').appendTo("select[name='action']");
	jQuery('<option>').val('download_xml').text('<?php _e( 'Download as XML', 'woocommerce-exporter' )?>').appendTo("select[name='action2']");
});
</script>
<?php
		ob_end_flush();

	}

}

// Process the bulk export actions on the Orders and Products screen
function woo_ce_admin_export_process_bulk_action() {

	// Get the screen ID
	$screen = get_current_screen();
	$screen_id = $screen->id;

	// Check if we are dealing with the Orders or Products screen
	if( !in_array( $screen_id, array( 'edit-shop_order', 'edit-product' ) ) )
		return;

	$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
	$export_format = false;
	$action = $wp_list_table->current_action();
	switch( $action ) {

		case 'download_csv':
			$export_format = 'csv';
			break;

		case 'download_tsv':
			$export_format = 'tsv';
			break;

		case 'download_xls':
			$export_format = 'xls';
			break;

		case 'download_xlsx':
			$export_format = 'xlsx';
			break;

		case 'download_xml':
			$export_format = 'xml';
			break;

		case 'unflag_export':
			if( isset( $_REQUEST['post'] ) ) {
	
				// Check we are dealing with the Orders screen
				if( $screen_id <> 'edit-shop_order' )
					return;
	
				$post_ids = array_map( 'absint', (array)$_REQUEST['post'] );
				if( !empty( $post_ids ) ) {
					foreach( $post_ids as $post_ID ) {
						// Remove exported flag from Order
						delete_post_meta( $post_ID, '_woo_cd_exported' );
						$order_flag_notes = woo_ce_get_option( 'order_flag_notes', 0 );
						if( $order_flag_notes ) {
							// Add an additional Order Note
							$order = woo_ce_get_order_wc_data( $post_ID );
							$note = __( 'Order export flag was cleared.', 'woocommerce-exporter' );
							$order->add_order_note( $note );
							unset( $order );
						}
					}
				}
				unset( $post_ids );
			} else {
				$message = __( '$_REQUEST[\'post\'] was empty so we could not run the unflag_export action within woo_ce_admin_export_process_bulk_action()', 'woocommerce-exporter' );
				woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
				return;
			}
			return;
			break;

		default:
			return;
			break;

	}
	if( !empty( $export_format ) ) {
		if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_admin_export_bulk_action', false ) ) {
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_admin_export_process_bulk_action(), $action: ' . $action ) );
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_admin_export_process_bulk_action(), $export_format: ' . $export_format ) );
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_admin_export_process_bulk_action(), $_POST: ' . print_r( $_POST, true ) ) );
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_admin_export_process_bulk_action(), $_GET: ' . print_r( $_GET, true ) ) );
			woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_admin_export_process_bulk_action(), $_REQUEST: ' . print_r( $_REQUEST, true ) ) );
		}
		if( isset( $_REQUEST['post'] ) ) {

			$post_ids = array_map( 'absint', (array)$_REQUEST['post'] );

			$gui = 'download';
			switch( $screen_id ) {

				case 'edit-shop_order':
					$export_type = 'order';
					// Replace Order ID with Sequential Order ID if available
					if( !empty( $post_ids ) && ( class_exists( 'WC_Seq_Order_Number' ) || class_exists( 'WC_Seq_Order_Number_Pro' ) ) ) {
						$size = count( $post_ids );
						for( $i = 0; $i < $size; $i++ ) {
							$post_ids[$i] = get_post_meta( $post_ids[$i], ( class_exists( 'WC_Seq_Order_Number_Pro' ) ? '_order_number_formatted' : '_order_number' ), true );
						}
					}
					break;

				case 'edit-product':
					$export_type = 'product';
					if( apply_filters( 'woo_ce_admin_export_process_bulk_action_product_include_trash', false ) )
						set_transient( WOO_CD_PREFIX . '_single_export_product_status', woo_ce_post_statuses( $product_status, true ), ( MINUTE_IN_SECONDS * 10 ) );
					break;

			}

			// Set up our export
			$export_fields = woo_ce_get_option( 'order_actions_fields', 'all' );
			$order_items_formatting = woo_ce_get_option( 'order_actions_order_items_formatting', 'unique' );
			$export_template = woo_ce_get_option( 'order_actions_export_template', false );

			if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_admin_export_bulk_action', false ) ) {
				woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_admin_export_process_bulk_action(), Transient woo_ce_single_export_format, $export_format: ' . $export_format ) );
				woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_admin_export_process_bulk_action(), Transient woo_ce_single_export_post_ids, $post_ids: ' . implode( ',', $post_ids ) ) );
				woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_admin_export_process_bulk_action(), Transient woo_ce_single_export_fields, $export_fields: ' . $export_fields ) );
				woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_admin_export_process_bulk_action(), Transient woo_ce_single_export_order_items_formatting, $order_items_formatting: ' . $order_items_formatting ) );
				woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_admin_export_process_bulk_action(), Transient woo_ce_single_export_template, $export_template: ' . $export_template ) );
			}
			set_transient( WOO_CD_PREFIX . '_single_export_format', $export_format, ( MINUTE_IN_SECONDS * 10 ) );
			set_transient( WOO_CD_PREFIX . '_single_export_post_ids', implode( ',', $post_ids ), ( MINUTE_IN_SECONDS * 10 ) );
			set_transient( WOO_CD_PREFIX . '_single_export_fields', $export_fields, ( MINUTE_IN_SECONDS * 10 ) );
			set_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting', $order_items_formatting, ( MINUTE_IN_SECONDS * 10 ) );
			set_transient( WOO_CD_PREFIX . '_single_export_template', $export_template, ( MINUTE_IN_SECONDS * 10 ) );
			unset( $post_ids );

			// Run the export
			$response = woo_ce_cron_export( $gui, $export_type );

			// Clean up
			delete_transient( WOO_CD_PREFIX . '_single_export_format' );
			delete_transient( WOO_CD_PREFIX . '_single_export_post_ids' );
			delete_transient( WOO_CD_PREFIX . '_single_export_fields' );
			delete_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting' );
			delete_transient( WOO_CD_PREFIX . '_single_export_template' );
			unset( $gui, $export_type );

			if( $response ) {
				exit();
			} else {
				$message = __( 'The bulk export failed as the CRON export engine returned an error', 'woocommerce-exporter' );
				woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
			}

		} else {
			$message = __( '$_REQUEST[\'post\'] was empty so we could not run the export action within woo_ce_admin_export_process_bulk_action()', 'woocommerce-exporter' );
			woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
			return;
		}
	}

}

// Add Download as... buttons to Actions column on Orders screen
function woo_ce_admin_order_actions( $actions = array(), $order = false ) {

	if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
		$order_id = ( method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id );
	} else {
		$order_id = ( isset( $order->id ) ? $order->id : 0 );
	}
	// Replace Order ID with Sequential Order ID if available
	if( !empty( $order ) && ( class_exists( 'WC_Seq_Order_Number' ) || class_exists( 'WC_Seq_Order_Number_Pro' ) ) ) {
		$order_id = get_post_meta( $order_id, ( class_exists( 'WC_Seq_Order_Number_Pro' ) ? '_order_number_formatted' : '_order_number' ), true );
	}

	if( woo_ce_get_option( 'order_actions_csv', 1 ) ) {
		$export_format = 'csv';
		$url = wp_nonce_url( admin_url( add_query_arg( array( 'action' => 'woo_ce_export_order', 'format' => $export_format, 'order_ids' => $order_id ), 'admin-ajax.php' ) ), 'woo_ce_export_order' );
		$actions[] = array(
			'url' => $url,
			'name' => __( 'Download as CSV', 'woocommerce-exporter' ),
			'action' => 'download_csv'
		);
	}
	if( woo_ce_get_option( 'order_actions_tsv', 1 ) ) {
		$export_format = 'tsv';
		$url = wp_nonce_url( admin_url( add_query_arg( array( 'action' => 'woo_ce_export_order', 'format' => $export_format, 'order_ids' => $order_id ), 'admin-ajax.php' ) ), 'woo_ce_export_order' );
		$actions[] = array(
			'url' => $url,
			'name' => __( 'Download as TSV', 'woocommerce-exporter' ),
			'action' => 'download_tsv'
		);
	}
	if( woo_ce_get_option( 'order_actions_xls', 1 ) ) {
		$export_format = 'xls';
		$url = wp_nonce_url( admin_url( add_query_arg( array( 'action' => 'woo_ce_export_order', 'format' => $export_format, 'order_ids' => $order_id ), 'admin-ajax.php' ) ), 'woo_ce_export_order' );
		$actions[] = array(
			'url' => $url,
			'name' => __( 'Download as XLS', 'woocommerce-exporter' ),
			'action' => 'download_xls'
		);
	}
	if( woo_ce_get_option( 'order_actions_xlsx', 1 ) ) {
		$export_format = 'xlsx';
		$url = wp_nonce_url( admin_url( add_query_arg( array( 'action' => 'woo_ce_export_order', 'format' => $export_format, 'order_ids' => $order_id ), 'admin-ajax.php' ) ), 'woo_ce_export_order' );
		$actions[] = array(
			'url' => $url,
			'name' => __( 'Download as XLSX', 'woocommerce-exporter' ),
			'action' => 'download_xlsx'
		);
	}
	if( woo_ce_get_option( 'order_actions_xml', 0 ) ) {
		$export_format = 'xml';
		$url = wp_nonce_url( admin_url( add_query_arg( array( 'action' => 'woo_ce_export_order', 'format' => $export_format, 'order_ids' => $order_id ), 'admin-ajax.php' ) ), 'woo_ce_export_order' );
		$actions[] = array(
			'url' => $url,
			'name' => __( 'Download as XML', 'woocommerce-exporter' ),
			'action' => 'download_xml'
		);
	}

	$actions = apply_filters( 'woo_ce_admin_order_actions', $actions, $order );

	return $actions;

}

// Generate exports for Download as... button clicks
function woo_ce_ajax_export_order() {

	if( check_admin_referer( 'woo_ce_export_order' ) ) {
		$gui = 'download';
		$export_type = 'order';
		$order_ids = ( isset( $_GET['order_ids'] ) ? sanitize_text_field( $_GET['order_ids'] ) : false );
		if( $order_ids ) {
			$export_fields = woo_ce_get_option( 'order_actions_fields', 'all' );
			$order_items_formatting = woo_ce_get_option( 'order_actions_order_items_formatting', 'unique' );
			$export_template = woo_ce_get_option( 'order_actions_export_template', false );

			// Set up our export
			set_transient( WOO_CD_PREFIX . '_single_export_fields', $export_fields, ( MINUTE_IN_SECONDS * 10 ) );
			set_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting', $order_items_formatting, ( MINUTE_IN_SECONDS * 10 ) );
			set_transient( WOO_CD_PREFIX . '_single_export_template', $export_template, ( MINUTE_IN_SECONDS * 10 ) );

			// Run the export
			$response = woo_ce_cron_export( $gui, $export_type );

			// Clean up
			delete_transient( WOO_CD_PREFIX . '_single_export_fields' );
			delete_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting' );
			delete_transient( WOO_CD_PREFIX . '_single_export_template' );

			if( $response ) {
				// die();
				exit();
			} else {
				$message = __( 'The export failed as the CRON export engine returned an error', 'woocommerce-exporter' );
				woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
			}
		}
	}

}

function woo_ce_admin_order_single_export_csv( $order = false ) {

	if( $order !== false ) {

		if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
			$order_id = ( method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id );
		} else {
			$order_id = ( isset( $order->id ) ? $order->id : 0 );
		}

		// Set the export format type
		$export_format = 'csv';
		$export_fields = woo_ce_get_option( 'order_actions_fields', 'all' );
		$order_items_formatting = woo_ce_get_option( 'order_actions_order_items_formatting', 'unique' );
		$export_template = woo_ce_get_option( 'order_actions_export_template', false );

		// Replace Order ID with Sequential Order ID if available
		if( class_exists( 'WC_Seq_Order_Number' ) || class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
			$order_id = get_post_meta( $order_id, ( class_exists( 'WC_Seq_Order_Number_Pro' ) ? '_order_number_formatted' : '_order_number' ), true );
		}

		// Set up our export
		set_transient( WOO_CD_PREFIX . '_single_export_format', $export_format, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_post_ids', $order_id, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_fields', $export_fields, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting', $order_items_formatting, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_template', $export_template, ( MINUTE_IN_SECONDS * 10 ) );

		// Run the export
		$gui = 'download';
		$export_type = 'order';
		$response = woo_ce_cron_export( $gui, $export_type );

		// Clean up
		delete_transient( WOO_CD_PREFIX . '_single_export_format' );
		delete_transient( WOO_CD_PREFIX . '_single_export_post_ids' );
		delete_transient( WOO_CD_PREFIX . '_single_export_fields' );
		delete_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting' );
		delete_transient( WOO_CD_PREFIX . '_single_export_template' );

		if( $response ) {
			exit();
		} else {
			$message = __( 'The export failed as the CRON export engine returned an error', 'woocommerce-exporter' );
			woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
		}

	}

}

function woo_ce_admin_order_single_export_tsv( $order = false ) {

	if( $order !== false ) {

		if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
			$order_id = ( method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id );
		} else {
			$order_id = ( isset( $order->id ) ? $order->id : 0 );
		}

		// Set the export format type
		$export_format = 'tsv';
		$export_fields = woo_ce_get_option( 'order_actions_fields', 'all' );
		$order_items_formatting = woo_ce_get_option( 'order_actions_order_items_formatting', 'unique' );
		$export_template = woo_ce_get_option( 'order_actions_export_template', false );

		// Replace Order ID with Sequential Order ID if available
		if( class_exists( 'WC_Seq_Order_Number' ) || class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
			$order_id = get_post_meta( $order_id, ( class_exists( 'WC_Seq_Order_Number_Pro' ) ? '_order_number_formatted' : '_order_number' ), true );
		}

		// Set up our export
		set_transient( WOO_CD_PREFIX . '_single_export_format', $export_format, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_post_ids', $order_id, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_fields', $export_fields, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting', $order_items_formatting, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_template', $export_template, ( MINUTE_IN_SECONDS * 10 ) );

		// Run the export
		$gui = 'download';
		$export_type = 'order';
		$response = woo_ce_cron_export( $gui, $export_type );

		// Clean up
		delete_transient( WOO_CD_PREFIX . '_single_export_format' );
		delete_transient( WOO_CD_PREFIX . '_single_export_post_ids' );
		delete_transient( WOO_CD_PREFIX . '_single_export_fields' );
		delete_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting' );
		delete_transient( WOO_CD_PREFIX . '_single_export_template' );

		if( $response ) {
			exit();
		} else {
			$message = __( 'The export failed as the CRON export engine returned an error', 'woocommerce-exporter' );
			woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
		}

	}

}

function woo_ce_admin_order_single_export_xls( $order = false ) {

	if( $order !== false ) {

		if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
			$order_id = ( method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id );
		} else {
			$order_id = ( isset( $order->id ) ? $order->id : 0 );
		}

		// Set the export format type
		$export_format = 'xls';
		$export_fields = woo_ce_get_option( 'order_actions_fields', 'all' );
		$order_items_formatting = woo_ce_get_option( 'order_actions_order_items_formatting', 'unique' );
		$export_template = woo_ce_get_option( 'order_actions_export_template', false );

		// Replace Order ID with Sequential Order ID if available
		if( class_exists( 'WC_Seq_Order_Number' ) || class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
			$order_id = get_post_meta( $order_id, ( class_exists( 'WC_Seq_Order_Number_Pro' ) ? '_order_number_formatted' : '_order_number' ), true );
		}

		// Set up our export
		set_transient( WOO_CD_PREFIX . '_single_export_format', $export_format, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_post_ids', $order_id, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_fields', $export_fields, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting', $order_items_formatting, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_template', $export_template, ( MINUTE_IN_SECONDS * 10 ) );

		// Run the export
		$gui = 'download';
		$export_type = 'order';
		$response = woo_ce_cron_export( $gui, $export_type );

		// Clean up
		delete_transient( WOO_CD_PREFIX . '_single_export_format' );
		delete_transient( WOO_CD_PREFIX . '_single_export_post_ids' );
		delete_transient( WOO_CD_PREFIX . '_single_export_fields' );
		delete_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting' );
		delete_transient( WOO_CD_PREFIX . '_single_export_template' );

		if( $response ) {
			exit();
		} else {
			$mssage = __( 'The export failed as the CRON export engine returned an error', 'woocommerce-exporter' );
			woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
		}

	}

}

function woo_ce_admin_order_single_export_xlsx( $order = false ) {

	if( $order !== false ) {

		if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
			$order_id = ( method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id );
		} else {
			$order_id = ( isset( $order->id ) ? $order->id : 0 );
		}

		// Set the export format type
		$export_format = 'xlsx';
		$export_fields = woo_ce_get_option( 'order_actions_fields', 'all' );
		$order_items_formatting = woo_ce_get_option( 'order_actions_order_items_formatting', 'unique' );
		$export_template = woo_ce_get_option( 'order_actions_export_template', false );

		// Replace Order ID with Sequential Order ID if available
		if( class_exists( 'WC_Seq_Order_Number' ) || class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
			$order_id = get_post_meta( $order_id, ( class_exists( 'WC_Seq_Order_Number_Pro' ) ? '_order_number_formatted' : '_order_number' ), true );
		}

		// Set up our export
		set_transient( WOO_CD_PREFIX . '_single_export_format', $export_format, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_post_ids', $order_id, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_fields', $export_fields, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting', $order_items_formatting, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_template', $export_template, ( MINUTE_IN_SECONDS * 10 ) );

		// Run the export
		$gui = 'download';
		$export_type = 'order';
		$response = woo_ce_cron_export( $gui, $export_type );

		// Clean up
		delete_transient( WOO_CD_PREFIX . '_single_export_format' );
		delete_transient( WOO_CD_PREFIX . '_single_export_post_ids' );
		delete_transient( WOO_CD_PREFIX . '_single_export_fields' );
		delete_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting' );
		delete_transient( WOO_CD_PREFIX . '_single_export_template' );

		if( $response ) {
			exit();
		} else {
			$message = __( 'The export failed as the CRON export engine returned an error', 'woocommerce-exporter' );
			woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
		}

	}

}

function woo_ce_admin_order_single_export_xml( $order = false ) {

	if( $order !== false ) {

		if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
			$order_id = ( method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id );
		} else {
			$order_id = ( isset( $order->id ) ? $order->id : 0 );
		}

		// Set the export format type
		$export_format = 'xml';
		$export_fields = woo_ce_get_option( 'order_actions_fields', 'all' );
		$order_items_formatting = woo_ce_get_option( 'order_actions_order_items_formatting', 'unique' );
		$export_template = woo_ce_get_option( 'order_actions_export_template', false );

		// Replace Order ID with Sequential Order ID if available
		if( class_exists( 'WC_Seq_Order_Number' ) || class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
			$order_id = get_post_meta( $order_id, ( class_exists( 'WC_Seq_Order_Number_Pro' ) ? '_order_number_formatted' : '_order_number' ), true );
		}

		// Set up our export
		set_transient( WOO_CD_PREFIX . '_single_export_format', $export_format, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_post_ids', $order_id, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_fields', $export_fields, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting', $order_items_formatting, ( MINUTE_IN_SECONDS * 10 ) );
		set_transient( WOO_CD_PREFIX . '_single_export_template', $export_template, ( MINUTE_IN_SECONDS * 10 ) );

		// Run the export
		$gui = 'download';
		$export_type = 'order';
		$response = woo_ce_cron_export( $gui, $export_type );

		// Clean up
		delete_transient( WOO_CD_PREFIX . '_single_export_format' );
		delete_transient( WOO_CD_PREFIX . '_single_export_post_ids' );
		delete_transient( WOO_CD_PREFIX . '_single_export_fields' );
		delete_transient( WOO_CD_PREFIX . '_single_export_order_items_formatting' );
		delete_transient( WOO_CD_PREFIX . '_single_export_template' );

		if( $response ) {
			exit();
		} else {
			$message = __( 'The export failed as the CRON export engine returned an error', 'woocommerce-exporter' );
			woo_ce_error_log( sprintf( 'Warning: %s', $message ) );
		}

	}

}

function woo_ce_admin_order_single_export_unflag( $order = false ) {

	if( $order !== false ) {

		if( version_compare( woo_get_woo_version(), '2.7', '>=' ) ) {
			$order_id = ( method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id );
		} else {
			$order_id = ( isset( $order->id ) ? $order->id : 0 );
		}

		// Remove exported flag from Order
		delete_post_meta( $order_id, '_woo_cd_exported' );
		$order_flag_notes = woo_ce_get_option( 'order_flag_notes', 0 );
		if( $order_flag_notes ) {
			// Add an additional Order Note
			$order_data = woo_ce_get_order_wc_data( $order_id );
			$note = __( 'Order export flag was cleared.', 'woocommerce-exporter' );
			$order_data->add_order_note( $note );
			unset( $order_data );
		}
	}

}

function woo_ce_admin_order_single_actions( $actions ) {

	$actions['woo_ce_export_order_csv'] = __( 'Download as CSV', 'woocommerce-exporter' );
	$actions['woo_ce_export_order_tsv'] = __( 'Download as TSV', 'woocommerce-exporter' );
	$actions['woo_ce_export_order_xml'] = __( 'Download as XML', 'woocommerce-exporter' );
	$actions['woo_ce_export_order_xls'] = __( 'Download as XLS', 'woocommerce-exporter' );
	$actions['woo_ce_export_order_xlsx'] = __( 'Download as XLSX', 'woocommerce-exporter' );
	$actions['woo_ce_export_order_unflag'] = __( 'Remove export flag', 'woocommerce-exporter' );
	return $actions;

}

// Add Store Export page to WooCommerce screen IDs
function woo_ce_wc_screen_ids( $screen_ids = array() ) {

	$screen_ids[] = 'woocommerce_page_woo_ce';
	return $screen_ids;

}
add_filter( 'woocommerce_screen_ids', 'woo_ce_wc_screen_ids', 10, 1 );

// Add Store Export to WordPress Administration menu
function woo_ce_admin_menu() {

	// Check the User has the view_woocommerce_reports capability
	$user_capability = apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' );

	$hook = add_submenu_page( 'woocommerce', __( 'Store Exporter Deluxe', 'woocommerce-exporter' ), __( 'Store Export', 'woocommerce-exporter' ), $user_capability, 'woo_ce', 'woo_cd_html_page' );
	// Load scripts and styling just for this Screen
	add_action( 'admin_print_styles-' . $hook, 'woo_ce_enqueue_scripts' );
	$tab = ( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '' );
	if( $tab == 'archive' )
		add_action( 'load-' . $hook, 'woo_ce_archives_add_options' );
	add_action( 'current_screen', 'woo_ce_admin_current_screen' );

}
add_action( 'admin_menu', 'woo_ce_admin_menu', 11 );

function woo_ce_admin_enqueue_scripts( $hook = '' ) {

	global $post, $pagenow;

	if( $post ) {

		// Check if this is the Scheduled Export or Export Template screen
		$post_types = array( 'scheduled_export', 'export_template' );
		if( in_array( get_post_type( $post->ID ), $post_types ) && ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) ) {
			// Load up default WooCommerce resources
			wp_enqueue_script( 'woocommerce_admin' );
			wp_enqueue_script( 'wc-admin-meta-boxes' );
			wp_enqueue_script( 'jquery-tiptip' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
			// Load up default exporter resources
			woo_ce_enqueue_scripts();
		}

		// Check if this is the Scheduled Export screen
		$post_type = 'scheduled_export';
		if( get_post_type( $post->ID ) == $post_type && ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) ) {
			// Time Picker Addon
			wp_enqueue_script( 'jquery-ui-timepicker', plugins_url( '/js/jquery.timepicker.js', WOO_CD_RELPATH ) );
			wp_enqueue_style( 'jquery-ui-timepicker', plugins_url( '/templates/admin/jquery-ui-timepicker.css', WOO_CD_RELPATH ) );
			// Hide the Pending Review Post Status
			add_action( 'admin_footer', 'woo_ce_admin_scheduled_export_post_status' );
		}

/*
		// Check if this is the Export Template screen
		$post_type = 'export_template';
		if( get_post_type( $post->ID ) == $post_type && ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) ) {
			
		}
*/

	}

}
add_action( 'admin_enqueue_scripts', 'woo_ce_admin_enqueue_scripts', 11 );

// Load CSS and jQuery scripts for Store Exporter Deluxe screen
function woo_ce_enqueue_scripts() {

	// Simple check that WooCommerce is activated
	if( class_exists( 'WooCommerce' ) ) {

		global $woocommerce;

		// Load WooCommerce default Admin styling
		wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css' );

	}

	// Date Picker Addon
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery-ui-datepicker', plugins_url( '/templates/admin/jquery-ui-datepicker.css', WOO_CD_RELPATH ) );

	// Chosen
	wp_enqueue_style( 'jquery-chosen', plugins_url( '/templates/admin/chosen.css', WOO_CD_RELPATH ) );
	wp_enqueue_script( 'jquery-chosen', plugins_url( '/js/jquery.chosen.js', WOO_CD_RELPATH ), array( 'jquery' ) );
	wp_enqueue_script( 'ajax-chosen', plugins_url( '/js/ajax-chosen.js', WOO_CD_RELPATH ), array( 'jquery', 'jquery-chosen' ) );

	// Common
	wp_enqueue_style( 'woo_ce_styles', plugins_url( '/templates/admin/export.css', WOO_CD_RELPATH ) );
	wp_enqueue_script( 'woo_ce_scripts', plugins_url( '/templates/admin/export.js', WOO_CD_RELPATH ), array( 'jquery', 'jquery-ui-sortable' ) );
	add_action( 'admin_footer', 'woo_ce_admin_footer_javascript' );
	wp_enqueue_style( 'dashicons' );

	// WOO_CD_DEBUG
	$debug_mode = false;
	if( defined( 'WOO_CD_DEBUG' ) ) {
		if( WOO_CD_DEBUG )
			$debug_mode = true;
	}
	if( !$debug_mode ) {
		wp_enqueue_style( 'jquery-csvToTable', plugins_url( '/templates/admin/jquery-csvtable.css', WOO_CD_RELPATH ) );
		wp_enqueue_script( 'jquery-csvToTable', plugins_url( '/js/jquery.csvToTable.js', WOO_CD_RELPATH ), array( 'jquery' ) );
	}
	wp_enqueue_style( 'woo_vm_styles', plugins_url( '/templates/admin/woocommerce-admin_dashboard_vm-plugins.css', WOO_CD_RELPATH ) );

/*
	// @mod - WordPress Admin Pointers. We'll do this once 2.4+ goes out
	// Check for WordPress 3.3+
	if( get_bloginfo( 'version' ) < '3.3' )
		return;

	// Get the screen ID
	$screen = get_current_screen();
	$screen_id = $screen->id;

	// Get pointers for this screen
	$pointers = apply_filters( 'woo_ce_admin_pointers-' . $screen_id, array() );
	if( !$pointers || !is_array( $pointers ) )
		return;

	// Get dismissed pointers
	$dismissed = explode( ',', (string)get_user_meta( get_current_user_id(), WOO_CD_PREFIX . '_dismissed_pointers', true ) );
	$valid_pointers = array();

	// Check pointers and remove dismissed ones.
	foreach( $pointers as $pointer_id => $pointer ) {

		// Sanity check
		if( in_array( $pointer_id, $dismissed ) || empty( $pointer )  || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) )
			continue;

		$pointer['pointer_id'] = $pointer_id;

		// Add the pointer to $valid_pointers array
		$valid_pointers['pointers'][] =  $pointer;

	}

	// No valid pointers? Stop here.
	if( empty( $valid_pointers ) )
		return;

	// Add pointers style to queue.
	wp_enqueue_style( 'wp-pointer' );

	// Add pointers script to queue. Add custom script.
	wp_enqueue_script( 'woo_ce_pointer', plugins_url( '/templates/admin/pointer.js', WOO_CD_RELPATH ), array( 'wp-pointer' ) );

	// Add pointer options to script.
	wp_localize_script( 'woo_ce_pointer', 'woo_ce_pointers', $valid_pointers );
*/

}

function woo_ce_admin_current_screen() {

	$screen = get_current_screen();
	switch( $screen->id ) {

		case 'woocommerce_page_woo_ce':

			$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

			$screen->add_help_tab( array(
				'id' => 'woo_ce',
				'title' => __( 'Store Exporter Deluxe', 'woocommerce-exporter' ),
				'content' => 
					'<p>' . __( 'Thank you for using Store Exporter Deluxe :) Should you need help using this Plugin please read the documentation, if an issue persists get in touch with us on Support.', 'woocommerce-exporter' ) . '</p>' .
					'<p><a href="' . $troubleshooting_url . '" target="_blank" class="button button-primary">' . __( 'Documentation', 'woocommerce-exporter' ) . '</a> <a href="' . 'http://www.visser.com.au/premium-support/' . '" target="_blank" class="button">' . __( 'Support', 'woocommerce-exporter' ) . '</a></p>'
			) );
			break;

		case 'scheduled_export':
			// Load up meta boxes for the Scheduled Export screen
			$post_type = 'scheduled_export';
			add_action( 'edit_form_top', 'woo_ce_scheduled_export_banner' );
			add_meta_box( 'woocommerce-coupon-data', __( 'Export Filters', 'woocommerce-exporter' ), 'woo_ce_scheduled_export_filters_meta_box', $post_type, 'normal', 'high' );
			add_meta_box( 'woo_ce-scheduled_exports-export_details', __( 'Scheduled Export Details', 'woocommerce-exporter' ), 'woo_ce_scheduled_export_details_meta_box', $post_type, 'normal', 'default' );
			add_meta_box( 'woo_ce-scheduled_exports-history', __( 'Scheduled Export History', 'woocommerce-exporter' ), 'woo_ce_scheduled_export_history_meta_box', $post_type, 'normal', 'default' );
			add_action( 'pre_post_update', 'woo_ce_scheduled_export_update', 10, 2 );
			add_action( 'save_post_scheduled_export', 'woo_ce_scheduled_export_save' );
			break;

		case 'export_template':
			// Load up meta boxes for the Export Template screen
			$post_type = 'export_template';
			add_action( 'edit_form_top', 'woo_ce_export_template_banner' );
			add_meta_box( 'woocommerce-coupon-data', __( 'Export Template', 'woocommerce-exporter' ), 'woo_ce_export_template_options_meta_box', $post_type, 'normal', 'high' );
			add_action( 'save_post_export_template', 'woo_ce_export_template_save' );
			break;

		case 'woocommerce_page_wc-status':
			add_action( 'woocommerce_system_status_report', 'woo_ce_extend_woocommerce_system_status_report' );
			break;

		case 'product':
		case 'shop_order':
		case 'shop_coupon':
		case 'profile':
		case 'user-edit':
			// Check if Store Toolkit is activated
			if( function_exists( 'woo_st_admin_init' ) ) {
				$action = ( isset( $_GET['woo_ce_admin_action'] ) ? sanitize_text_field( $_GET['woo_ce_admin_action'] ) : false );
				if( !empty( $action ) ) {
					switch( $action ) {

						case 'add_custom_product_meta':
							// We need to verify the nonce.
							if( !empty( $_GET ) && check_admin_referer( 'woo_add_custom_product_meta' ) ) {
								$meta_key = ( isset( $_GET['meta_key'] ) ? sanitize_text_field( $_GET['meta_key'] ) : false );
								// Check if the Product meta key is already in the saved list
								$custom_products = woo_ce_get_option( 'custom_products', '' );
								if( !empty( $custom_products ) ) {
									// Check if it's just an empty first element
									$size = count( $custom_products );
									if( $size == 1 && $custom_products[0] == '' )
										$custom_products[0] = sanitize_text_field( trim( stripslashes( $meta_key ) ) );
									else
										$custom_products[] = sanitize_text_field( trim( stripslashes( $meta_key ) ) );
									woo_ce_update_option( 'custom_products', $custom_products );
								}
								unset( $custom_products );
								$message = sprintf( __( '%s has been added as a custom Product export field in Store Exporter Deluxe.', 'woocommerce-exporter' ), esc_attr( $meta_key ) );
								woo_cd_admin_notice( $message );
								$url = add_query_arg( array( 'woo_ce_admin_action' => null, 'meta_key' => null, '_wpnonce' => null ) );
								wp_redirect( $url );
								exit();
							}
							break;

						case 'add_custom_order_meta':
							// We need to verify the nonce.
							if( !empty( $_GET ) && check_admin_referer( 'woo_add_custom_order_meta' ) ) {
								$meta_key = ( isset( $_GET['meta_key'] ) ? sanitize_text_field( $_GET['meta_key'] ) : false );
								// Check if the Order meta key is already in the saved list
								$custom_orders = woo_ce_get_option( 'custom_orders', '' );
								if( !empty( $custom_orders ) ) {
									// Check if it's just an empty first element
									$size = count( $custom_orders );
									if( $size == 1 && $custom_orders[0] == '' )
										$custom_orders[0] = sanitize_text_field( trim( stripslashes( $meta_key ) ) );
									else
										$custom_orders[] = sanitize_text_field( trim( stripslashes( $meta_key ) ) );
									woo_ce_update_option( 'custom_orders', $custom_orders );
								}
								unset( $custom_orders );
								$message = sprintf( __( '%s has been added as a custom Order export field in Store Exporter Deluxe.', 'woocommerce-exporter' ), esc_attr( $meta_key ) );
								woo_cd_admin_notice( $message );
								$url = add_query_arg( array( 'woo_ce_admin_action' => null, 'meta_key' => null, '_wpnonce' => null ) );
								wp_redirect( $url );
								exit();
							}
							break;

						case 'add_custom_order_item_meta':
							// We need to verify the nonce.
							if( !empty( $_GET ) && check_admin_referer( 'woo_add_custom_order_item_meta' ) ) {
								$meta_key = ( isset( $_GET['meta_key'] ) ? sanitize_text_field( $_GET['meta_key'] ) : false );
								// Check if the Order Item meta key is already in the saved list
								$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
								if( !empty( $custom_order_items ) ) {
									// Check if it's just an empty first element
									$size = count( $custom_order_items );
									if( $size == 1 && $custom_order_items[0] == '' )
										$custom_order_items[0] = sanitize_text_field( trim( stripslashes( $meta_key ) ) );
									else
										$custom_order_items[] = sanitize_text_field( trim( stripslashes( $meta_key ) ) );
									woo_ce_update_option( 'custom_order_items', $custom_order_items );
								}
								unset( $custom_order_items );
								$message = sprintf( __( '%s has been added as a custom Order Item export field in Store Exporter Deluxe.', 'woocommerce-exporter' ), esc_attr( $meta_key ) );
								woo_cd_admin_notice( $message );
								$url = add_query_arg( array( 'woo_ce_admin_action' => null, 'meta_key' => null, '_wpnonce' => null ) );
								wp_redirect( $url );
								exit();
							}
							break;

						case 'add_custom_coupon_meta':
							// We need to verify the nonce.
							if( !empty( $_GET ) && check_admin_referer( 'woo_add_custom_coupon_meta' ) ) {
								$meta_key = ( isset( $_GET['meta_key'] ) ? sanitize_text_field( $_GET['meta_key'] ) : false );
								// Check if the Coupon meta key is already in the saved list
								$custom_coupons = woo_ce_get_option( 'custom_coupons', '' );
								if( !empty( $custom_coupons ) ) {
									// Check if it's just an empty first element
									$size = count( $custom_coupons );
									if( $size == 1 && $custom_coupons[0] == '' )
										$custom_coupons[0] = sanitize_text_field( trim( stripslashes( $meta_key ) ) );
									else
										$custom_coupons[] = sanitize_text_field( trim( stripslashes( $meta_key ) ) );
									woo_ce_update_option( 'custom_coupons', $custom_coupons );
								}
								unset( $custom_coupons );
								$message = sprintf( __( '%s has been added as a custom Coupon export field in Store Exporter Deluxe.', 'woocommerce-exporter' ), esc_attr( $meta_key ) );
								woo_cd_admin_notice( $message );
								$url = add_query_arg( array( 'woo_ce_admin_action' => null, 'meta_key' => null, '_wpnonce' => null ) );
								wp_redirect( $url );
								exit();
							}
							break;

						case 'add_custom_user_meta':
							// We need to verify the nonce.
							if( !empty( $_GET ) && check_admin_referer( 'woo_add_custom_user_meta' ) ) {
								$meta_key = ( isset( $_GET['meta_key'] ) ? sanitize_text_field( $_GET['meta_key'] ) : false );
								// Check if the User meta key is already in the saved list
								$custom_users = woo_ce_get_option( 'custom_users', '' );
								if( !empty( $custom_users ) ) {
									// Check if it's just an empty first element
									$size = count( $custom_users );
									if( $size == 1 && $custom_users[0] == '' )
										$custom_users[0] = sanitize_text_field( trim( stripslashes( $meta_key ) ) );
									else
										$custom_users[] = sanitize_text_field( trim( stripslashes( $meta_key ) ) );
									woo_ce_update_option( 'custom_users', $custom_users );
								}
								unset( $custom_users );
								$message = sprintf( __( '%s has been added as a custom User export field in Store Exporter Deluxe.', 'woocommerce-exporter' ), esc_attr( $meta_key ) );
								woo_cd_admin_notice( $message );
								$url = add_query_arg( array( 'woo_ce_admin_action' => null, 'meta_key' => null, '_wpnonce' => null ) );
								wp_redirect( $url );
								exit();
							}
							break;

					}
				}
				add_action( 'woo_st_product_data_actions', 'woo_ce_extend_woo_st_product_data_actions', 10, 2 );
				add_action( 'woo_st_order_data_actions', 'woo_ce_extend_woo_st_order_data_actions', 10, 2 );
				add_action( 'woo_st_order_item_data_actions', 'woo_ce_extend_woo_st_order_item_data_actions', 10, 2 );
				add_action( 'woo_st_coupon_data_actions', 'woo_ce_extend_woo_st_coupon_data_actions', 10, 2 );
				add_action( 'woo_st_user_data_actions', 'woo_ce_extend_woo_st_user_data_actions', 10, 2 );

			}
			break;

	}

}

function woo_ce_extend_woo_st_product_data_actions( $post_id, $meta_key = '' ) {

	// Check if the Product meta_key is already in the saved list
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	$meta_added = false;
	if( !empty( $custom_products ) ) {
		if( in_array( $meta_key, $custom_products ) )
		 $meta_added = true;
	}
	if( !$meta_added ) {
		$url = esc_url( add_query_arg( array( 'woo_ce_admin_action' => 'add_custom_product_meta', 'meta_key' => esc_attr( $meta_key ), '_wpnonce' => wp_create_nonce( 'woo_add_custom_product_meta' ) ) ) );
		$output = '<a href="' . $url . '" title="' . sprintf( __( 'Add \'%s\' as a custom Product export field in Store Exporter Deluxe', 'woocommerce-exporter' ), esc_attr( $meta_key ) ) . '" class="button" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;">' . __( 'Add to export', 'woocommerce-exporter' ) . '</a>';
	} else {
		$output = '<input type="button" title="' . sprintf( __( '\'%s\' has already been added as a custom Product export field in Store Exporter Deluxe', 'woocommerce-exporter' ), esc_attr( $meta_key ) ) . '" class="button disabled" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;" value="' . __( 'Add to export', 'woocommerce-exporter' ) . '" />';
	}
	echo $output;

}

function woo_ce_extend_woo_st_order_data_actions( $post_id, $meta_key = '' ) {

	// Check if the Order meta_key is already in the saved list
	$custom_orders = woo_ce_get_option( 'custom_orders', '' );
	$meta_added = false;
	if( !empty( $custom_orders ) ) {
		if( in_array( $meta_key, $custom_orders ) )
		 $meta_added = true;
	}
	if( !$meta_added ) {
		$url = esc_url( add_query_arg( array( 'woo_ce_admin_action' => 'add_custom_order_meta', 'meta_key' => esc_attr( $meta_key ), '_wpnonce' => wp_create_nonce( 'woo_add_custom_order_meta' ) ) ) );
		$output = '<a href="' . $url . '" title="' . sprintf( __( 'Add \'%s\' as a custom Order export field in Store Exporter Deluxe', 'woocommerce-exporter' ), esc_attr( $meta_key ) ) . '" class="button" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;">' . __( 'Add to export', 'woocommerce-exporter' ) . '</a>';
	} else {
		$output = '<input type="button" title="' . sprintf( __( '\'%s\' has already been added as a custom Order export field in Store Exporter Deluxe', 'woocommerce-exporter' ), esc_attr( $meta_key ) ) . '" class="button disabled" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;" value="' . __( 'Add to export', 'woocommerce-exporter' ) . '" />';
	}
	echo $output;

}

function woo_ce_extend_woo_st_order_item_data_actions( $post_id, $meta_key = '' ) {

	// Check if the Order Item meta_key is already in the saved list
	$custom_order_items = woo_ce_get_option( 'custom_order_items', '' );
	$meta_added = false;
	if( !empty( $custom_order_items ) ) {
		if( in_array( $meta_key, $custom_order_items ) )
		 $meta_added = true;
	}
	if( !$meta_added ) {
		$url = esc_url( add_query_arg( array( 'woo_ce_admin_action' => 'add_custom_order_item_meta', 'meta_key' => esc_attr( $meta_key ), '_wpnonce' => wp_create_nonce( 'woo_add_custom_order_item_meta' ) ) ) );
		$output = '<a href="' . $url . '" title="' . sprintf( __( 'Add \'%s\' as a custom Order Item export field in Store Exporter Deluxe', 'woocommerce-exporter' ), esc_attr( $meta_key ) ) . '" class="button" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;">' . __( 'Add to export', 'woocommerce-exporter' ) . '</a>';
	} else {
		$output = '<input type="button" title="' . sprintf( __( '\'%s\' has already been added as a custom Order Item export field in Store Exporter Deluxe', 'woocommerce-exporter' ), esc_attr( $meta_key ) ) . '" class="button disabled" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;" value="' . __( 'Add to export', 'woocommerce-exporter' ) . '" />';
	}
	echo $output;

}

function woo_ce_extend_woo_st_coupon_data_actions( $post_id, $meta_key = '' ) {

	// Check if the Coupon meta_key is already in the saved list
	$custom_coupons = woo_ce_get_option( 'custom_coupons', '' );
	$meta_added = false;
	if( !empty( $custom_coupons ) ) {
		if( in_array( $meta_key, $custom_coupons ) )
		 $meta_added = true;
	}
	if( !$meta_added ) {
		$url = esc_url( add_query_arg( array( 'woo_ce_admin_action' => 'add_custom_coupon_meta', 'meta_key' => esc_attr( $meta_key ), '_wpnonce' => wp_create_nonce( 'woo_add_custom_coupon_meta' ) ) ) );
		$output = '<a href="' . $url . '" title="' . sprintf( __( 'Add \'%s\' as a custom Coupon export field in Store Exporter Deluxe', 'woocommerce-exporter' ), esc_attr( $meta_key ) ) . '" class="button" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;">' . __( 'Add to export', 'woocommerce-exporter' ) . '</a>';
	} else {
		$output = '<input type="button" title="' . sprintf( __( '\'%s\' has already been added as a custom Coupon export field in Store Exporter Deluxe', 'woocommerce-exporter' ), esc_attr( $meta_key ) ) . '" class="button disabled" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;" value="' . __( 'Add to export', 'woocommerce-exporter' ) . '" />';
	}
	echo $output;

}

function woo_ce_extend_woo_st_user_data_actions( $user_id, $meta_key = '' ) {

	// Check if the User meta_key is already in the saved list
	$custom_users = woo_ce_get_option( 'custom_users', '' );
	$meta_added = false;
	if( !empty( $custom_users ) ) {
		if( in_array( $meta_key, $custom_users ) )
		 $meta_added = true;
	}
	if( !$meta_added ) {
		$url = esc_url( add_query_arg( array( 'woo_ce_admin_action' => 'add_custom_user_meta', 'meta_key' => esc_attr( $meta_key ), '_wpnonce' => wp_create_nonce( 'woo_add_custom_user_meta' ) ) ) );
		$output = '<a href="' . $url . '" title="' . sprintf( __( 'Add \'%s\' as a custom User export field in Store Exporter Deluxe', 'woocommerce-exporter' ), esc_attr( $meta_key ) ) . '" class="button" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;">' . __( 'Add to export', 'woocommerce-exporter' ) . '</a>';
	} else {
		$output = '<input type="button" title="' . sprintf( __( '\'%s\' has already been added as a custom User export field in Store Exporter Deluxe', 'woocommerce-exporter' ), esc_attr( $meta_key ) ) . '" class="button disabled" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Oxygen-Sans,Ubuntu,Cantarell,\'Helvetica Neue\',sans-serif;" value="' . __( 'Add to export', 'woocommerce-exporter' ) . '" />';
	}
	echo $output;

}

function woo_ce_extend_woocommerce_system_status_report() {

	// WOO_CD_DEBUG
	$debug_mode = false;
	if( defined( 'WOO_CD_DEBUG' ) ) {
		if( WOO_CD_DEBUG )
			$debug_mode = true;
	}
	if( $debug_mode )
		$debug_mode_url = add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'settings', 'action' => 'disable_debug_mode', '_wpnonce' => wp_create_nonce( 'woo_ce_disable_debug_mode' ) ), 'admin.php' );
	else
		$debug_mode_url = add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'settings', 'action' => 'enable_debug_mode', '_wpnonce' => wp_create_nonce( 'woo_ce_enable_debug_mode' ) ), 'admin.php' );

	// WOO_CD_LOGGING
	$logging_mode = false;
	if( defined( 'WOO_CD_LOGGING' ) ) {
		if( WOO_CD_LOGGING )
			$logging_mode = true;
	}
	if( $logging_mode )
		$logging_mode_url = add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'settings', 'action' => 'disable_logging_mode', '_wpnonce' => wp_create_nonce( 'woo_ce_disable_logging_mode' ) ), 'admin.php' );
	else
		$logging_mode_url = add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'settings', 'action' => 'enable_logging_mode', '_wpnonce' => wp_create_nonce( 'woo_ce_enable_logging_mode' ) ), 'admin.php' );

	// Export to FTP
	$required_ftp_functions = array(
		'ftp_connect',
		'ftp_get_option',
		'ftp_set_option',
		'ftp_login',
		'ftp_raw',
		'ftp_pasv',
		'ftp_pwd',
		'ftp_chdir',
		'ftp_put',
		'ftp_fput',
		'ftp_size',
		'ftp_delete'
	);
	$missing_ftp_functions = array();
	foreach( $required_ftp_functions as $required_ftp_function ) {
		if( !function_exists( $required_ftp_function ) )
			$missing_ftp_functions[] = $required_ftp_function . '()';
	}

	// Export to SFTP
	$required_sftp_functions = array(
		'ssh2_connect',
		'ssh2_auth_password',
		'ssh2_sftp',
		'stream_copy_to_stream'
	);
	$missing_sftp_functions = array();
	foreach( $required_sftp_functions as $required_sftp_function ) {
		if( !function_exists( $required_sftp_function ) )
			$missing_sftp_functions[] = $required_sftp_function . '()';
	}

	ob_start();

	$template = 'woocommerce_status.php';
	if( file_exists( WOO_CD_PATH . 'templates/admin/' . $template ) ) {

		include_once( WOO_CD_PATH . 'templates/admin/' . $template );

	} else {

		$message = sprintf( __( 'We couldn\'t load the template file <code>%s</code> within <code>%s</code>, this file should be present.', 'woocommerce-exporter' ), $template, WOO_CD_PATH . 'includes/admin/...' );
?>
<p><strong><?php echo $message; ?></strong></p>
<p><?php _e( 'You can see this error for one of a few common reasons', 'woocommerce-exporter' ); ?>:</p>
<ul class="ul-disc">
	<li><?php _e( 'WordPress was unable to create this file when the Plugin was installed or updated', 'woocommerce-exporter' ); ?></li>
	<li><?php _e( 'The Plugin files have been recently changed and there has been a file conflict', 'woocommerce-exporter' ); ?></li>
	<li><?php _e( 'The Plugin file has been locked and cannot be opened by WordPress', 'woocommerce-exporter' ); ?></li>
</ul>
<p><?php _e( 'Jump onto our website and download a fresh copy of this Plugin as it might be enough to fix this issue. If this persists get in touch with us.', 'woocommerce-exporter' ); ?></p>
<?php

	}

	ob_end_flush();

}

function woo_ce_admin_plugin_row() {

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

	// Detect if another e-Commerce platform is activated
	if( !woo_is_woo_activated() && ( woo_is_jigo_activated() || woo_is_wpsc_activated() ) ) {
		$message = __( 'We have detected another e-Commerce Plugin than WooCommerce activated, please check that you are using Store Exporter Deluxe for the correct platform.', 'woocommerce-exporter' );
		$message .= sprintf( ' <a href="%s" target="_blank">%s</a>', __( 'Need help?', 'woocommerce-exporter' ), $troubleshooting_url );
		echo '</tr><tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange"><div class="update-message">' . $message . '</div></td></tr>';
	} else if( !woo_is_woo_activated() ) {
		$message = __( 'We have been unable to detect the WooCommerce Plugin activated on this WordPress site, please check that you are using Store Exporter Deluxe for the correct platform.', 'woocommerce-exporter' );
		$message .= sprintf( ' <a href="%s" target="_blank">%s</a>', $troubleshooting_url, __( 'Need help?', 'woocommerce-exporter' ) );
		echo '</tr><tr class="plugin-update-tr"><td colspan="3" class="plugin-update colspanchange"><div class="update-message">' . $message . '</div></td></tr>';
	}

}
 
function woo_ce_admin_override_scheduled_export_notice() {

	global $post_type, $pagenow;

	$page = ( isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '' );

	if( $pagenow == 'admin.php' && $page == 'woo_ce' ) {
		// Check if we are overriding the Scheduled Export or duplicating it
		if( isset( $_REQUEST['scheduled'] ) && absint( $_REQUEST['scheduled'] ) ) {
			$refresh_timeout = apply_filters( 'woo_ce_admin_scheduled_export_refresh_timeout', 30, absint( $_REQUEST['scheduled'] ) );
			$message = sprintf( __( 'The requested scheduled export will run momentarily. This screen will refresh automatically in %d seconds.', 'woocommerce-exporter' ), $refresh_timeout );
			woo_cd_admin_notice_html( $message );

			// Refresh the screen after 30 seconds
			echo '
<script type="text/javascript">
window.setTimeout(
	function(){
		window.location.replace("' . add_query_arg( "scheduled", null ) . '");
	}, ' . ( $refresh_timeout * 1000 ) . '
);
</script>';
		} else if( isset( $_REQUEST['clone'] ) && absint( $_REQUEST['clone'] ) ) {
			$message = __( 'The requested scheduled export has been duplicated with a Status of Draft.', 'woocommerce-exporter' );
			woo_cd_admin_notice_html( $message );
		} else if( isset( $_REQUEST['draft'] ) && absint( $_REQUEST['draft'] ) ) {
			$message = __( 'The requested scheduled export has been updated with a Status of Draft.', 'woocommerce-exporter' );
			woo_cd_admin_notice_html( $message );
		} else if( isset( $_REQUEST['publish'] ) && absint( $_REQUEST['publish'] ) ) {
			$message = __( 'The requested scheduled export has been updated with a Status of Published.', 'woocommerce-exporter' );
			woo_cd_admin_notice_html( $message );
		}
	}

}
add_action( 'admin_notices', 'woo_ce_admin_override_scheduled_export_notice' );

// HTML active class for the currently selected tab on the Store Exporter screen
function woo_cd_admin_active_tab( $tab_name = null, $tab = null ) {

	if( isset( $_GET['tab'] ) && !$tab )
		$tab = $_GET['tab'];
	else if( !isset( $_GET['tab'] ) && woo_ce_get_option( 'skip_overview', false ) )
		$tab = 'export';
	else
		$tab = 'overview';

	$output = '';
	if( isset( $tab_name ) && $tab_name ) {
		if( $tab_name == $tab )
			$output = ' nav-tab-active';
	}
	echo $output;

}

// HTML template for each tab on the Store Exporter screen
function woo_cd_tab_template( $tab = '' ) {

	if( !$tab )
		$tab = 'overview';

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

	switch( $tab ) {

		case 'overview':
			$skip_overview = woo_ce_get_option( 'skip_overview', false );
			break;

		case 'export':
			// Welcome notice for Quick Export screen
			if( !woo_ce_get_option( 'dismiss_quick_export_prompt', 0 ) ) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_quick_export_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_dismiss_quick_export_prompt' ) ) ) );
				$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . '<strong>' . __( 'This is where the magic happens.', 'woocommerce-exporter' ) . '</strong> ' . __( 'Select an export type from the list below to expand the list of fields and filters. Once you have selected an export type you can select the fields you would like to export and filters available for each export type. When you click the Export button below, Store Exporter Deluxe will create an export file for you to save to your computer.', 'woocommerce-exporter' );
				woo_cd_admin_notice_html( $message, 'notice' );
			}

			woo_ce_load_export_types();

			$hidden_types = woo_ce_get_option( 'hidden_export_types', array() );
			if( $hidden_types == false )
				$hidden_types = array();

			$product = woo_ce_get_export_type_count( 'product' );
			$category = woo_ce_get_export_type_count( 'category' );
			$tag = woo_ce_get_export_type_count( 'tag' );
			$brand = woo_ce_get_export_type_count( 'brand' );
			$order = woo_ce_get_export_type_count( 'order' );
			$customer = woo_ce_get_export_type_count( 'customer' );
			$user = woo_ce_get_export_type_count( 'user' );
			$review = woo_ce_get_export_type_count( 'review' );
			$coupon = woo_ce_get_export_type_count( 'coupon' );
			$attribute = woo_ce_get_export_type_count( 'attribute' );
			$subscription = woo_ce_get_export_type_count( 'subscription' );
			$product_vendor = woo_ce_get_export_type_count( 'product_vendor' );
			$commission = woo_ce_get_export_type_count( 'commission' );
			$shipping_class = woo_ce_get_export_type_count( 'shipping_class' );
			$ticket = woo_ce_get_export_type_count( 'ticket' );
			$booking = woo_ce_get_export_type_count( 'booking' );

			$product_fields = false;
			$category_fields = false;
			$tag_fields = false;
			$brand_fields = false;
			$order_fields = false;
			$customer_fields = false;
			$user_fields = false;
			$review_fields = false;
			$coupon_fields = false;
			$attribute_fields = false;
			$subscription_fields = false;
			$product_vendor_fields = false;
			$commission_fields = false;
			$shipping_class_fields = false;
			$ticket_fields = false;
			$booking_fields = false;

			// Start loading the Quick Export screen

			add_action( 'woo_ce_export_before_options', 'woo_ce_export_export_types' );
			add_action( 'woo_ce_export_after_options', 'woo_ce_export_export_options' );

			// Products
			if( !in_array( 'product', $hidden_types ) ) {
				if( $product_fields = ( function_exists( 'woo_ce_get_product_fields' ) ? woo_ce_get_product_fields() : false ) ) {
					if( $product ) {
						foreach( $product_fields as $key => $product_field )
							$product_fields[$key]['disabled'] = ( isset( $product_field['disabled'] ) ? $product_field['disabled'] : 0 );
						add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_category' );
						add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_tag' );
						if( function_exists( 'woo_ce_products_filter_by_product_brand' ) )
							add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_brand' );
						if( function_exists( 'woo_ce_products_filter_by_product_vendor' ) )
							add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_vendor' );
						add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_status' );
						add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_type' );
						add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_sku' );
						add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_user_role' );
						add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_stock_status' );
						add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_featured' );
						add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_shipping_class' );
						add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_featured_image' );
						add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_gallery' );
						if( function_exists( 'woo_ce_products_filter_by_language' ) )
							add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_language' );
						add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_date_modified' );
						if( function_exists( 'woo_ce_products_filter_by_product_meta' ) )
							add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_filter_by_product_meta' );
						add_action( 'woo_ce_export_product_options_before_table', 'woo_ce_products_custom_fields_link' );
						add_action( 'woo_ce_export_product_options_after_table', 'woo_ce_product_sorting' );
						add_action( 'woo_ce_export_options', 'woo_ce_products_upsells_formatting' );
						add_action( 'woo_ce_export_options', 'woo_ce_products_crosssells_formatting' );
						add_action( 'woo_ce_export_options', 'woo_ce_products_variation_formatting' );
						add_action( 'woo_ce_export_options', 'woo_ce_products_description_excerpt_formatting' );
						add_action( 'woo_ce_export_options', 'woo_ce_export_options_featured_image_formatting' );
						add_action( 'woo_ce_export_options', 'woo_ce_export_options_product_gallery_formatting' );
						add_action( 'woo_ce_export_after_form', 'woo_ce_products_custom_fields' );
						if( function_exists( 'woo_ce_products_custom_fields_tab_manager' ) )
							add_action( 'woo_ce_products_custom_fields', 'woo_ce_products_custom_fields_tab_manager' );
						if( function_exists( 'woo_ce_products_custom_fields_wootabs' ) )
							add_action( 'woo_ce_products_custom_fields', 'woo_ce_products_custom_fields_wootabs' );
					}
				}
			}

			// Categories
			if( !in_array( 'category', $hidden_types ) ) {
				if( $category_fields = ( function_exists( 'woo_ce_get_category_fields' ) ? woo_ce_get_category_fields() : false ) ) {
					if( $category ) {
						foreach( $category_fields as $key => $category_field )
							$category_fields[$key]['disabled'] = ( isset( $category_field['disabled'] ) ? $category_field['disabled'] : 0 );
						if( function_exists( 'woo_ce_categories_filter_by_language' ) )
							add_action( 'woo_ce_export_category_options_before_table', 'woo_ce_categories_filter_by_language' );
						add_action( 'woo_ce_export_category_options_after_table', 'woo_ce_category_sorting' );
					}
				}
			}

			// Product Tags
			if( !in_array( 'tag', $hidden_types ) ) {
				if( $tag_fields = ( function_exists( 'woo_ce_get_tag_fields' ) ? woo_ce_get_tag_fields() : false ) ) {
					if( $tag ) {
						foreach( $tag_fields as $key => $tag_field )
							$tag_fields[$key]['disabled'] = ( isset( $tag_field['disabled'] ) ? $tag_field['disabled'] : 0 );
						if( function_exists( 'woo_ce_tags_filter_by_language' ) )
							add_action( 'woo_ce_export_tag_options_before_table', 'woo_ce_tags_filter_by_language' );
						add_action( 'woo_ce_export_tag_options_after_table', 'woo_ce_tag_sorting' );
					}
				}
			}

			// Brands
			if( !in_array( 'brand', $hidden_types ) ) {
				if( $brand_fields = ( function_exists( 'woo_ce_get_brand_fields' ) ? woo_ce_get_brand_fields() : false ) ) {
					if( $brand ) {
						foreach( $brand_fields as $key => $brand_field )
							$brand_fields[$key]['disabled'] = ( isset( $brand_field['disabled'] ) ? $brand_field['disabled'] : 0 );
						add_action( 'woo_ce_export_brand_options_before_table', 'woo_ce_brand_sorting' );
					}
				}
			}

			// Orders
			if( !in_array( 'order', $hidden_types ) ) {
				if( $order_fields = ( function_exists( 'woo_ce_get_order_fields' ) ? woo_ce_get_order_fields() : false ) ) {
					if( $order ) {
						foreach( $order_fields as $key => $order_field ) {
							$order_fields[$key]['disabled'] = ( isset( $order_field['disabled'] ) ? $order_field['disabled'] : 0 );
							if( isset( $order_field['hidden'] ) && $order_field['hidden'] )
								unset( $order_fields[$key] );
						}
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_date' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_status' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_customer' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_billing_country' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_shipping_country' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_user_role' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_coupon' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_product' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_product_category' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_product_tag' );
						if( function_exists( 'woo_ce_orders_filter_by_product_brand' ) )
							add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_product_brand' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_order_id' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_payment_gateway' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_shipping_method' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_digital_products' );
						if( function_exists( 'woo_ce_orders_filter_by_product_vendor' ) )
							add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_product_vendor' );
						if( function_exists( 'woo_ce_orders_filter_by_delivery_date' ) )
							add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_delivery_date' );
						if( function_exists( 'woo_ce_orders_filter_by_booking_date' ) )
							add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_booking_date' );
						if( function_exists( 'woo_ce_orders_filter_by_voucher_redeemed' ) )
							add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_voucher_redeemed' );
						if( function_exists( 'woo_ce_orders_filter_by_order_type' ) )
							add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_order_type' );
						if( function_exists( 'woo_ce_orders_filter_by_order_meta' ) )
							add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_filter_by_order_meta' );
						add_action( 'woo_ce_export_order_options_before_table', 'woo_ce_orders_custom_fields_link', 11 );
						add_action( 'woo_ce_export_order_options_after_table', 'woo_ce_order_sorting' );
						add_action( 'woo_ce_export_options', 'woo_ce_orders_items_formatting' );
						add_action( 'woo_ce_export_options', 'woo_ce_orders_max_order_items' );
						add_action( 'woo_ce_export_options', 'woo_ce_orders_items_types' );
						add_action( 'woo_ce_export_options', 'woo_ce_orders_flag_notes' );
						if( function_exists( 'woo_ce_orders_custom_fields_extra_product_options' ) )
							add_action( 'woo_ce_orders_custom_fields', 'woo_ce_orders_custom_fields_extra_product_options' );
						if( function_exists( 'woo_ce_orders_custom_fields_product_addons' ) )
							add_action( 'woo_ce_orders_custom_fields', 'woo_ce_orders_custom_fields_product_addons' );
						add_action( 'woo_ce_export_after_form', 'woo_ce_orders_custom_fields' );
					}
				}
			}

			// Customers
			if( !in_array( 'customer', $hidden_types ) ) {
				if( $customer_fields = ( function_exists( 'woo_ce_get_customer_fields' ) ? woo_ce_get_customer_fields() : false ) ) {
					if( $customer ) {
						foreach( $customer_fields as $key => $customer_field )
							$customer_fields[$key]['disabled'] = ( isset( $customer_field['disabled'] ) ? $customer_field['disabled'] : 0 );
						add_action( 'woo_ce_export_customer_options_before_table', 'woo_ce_customers_filter_by_status' );
						add_action( 'woo_ce_export_customer_options_before_table', 'woo_ce_customers_filter_by_user_role' );
						add_action( 'woo_ce_export_customer_options_before_table', 'woo_ce_customers_custom_fields_link' );
						add_action( 'woo_ce_export_customer_options_after_table', 'woo_ce_customer_sorting' );
						add_action( 'woo_ce_export_after_form', 'woo_ce_customers_custom_fields' );
					}
				}
			}

			// Users
			if( !in_array( 'user', $hidden_types ) ) {
				if( $user_fields = ( function_exists( 'woo_ce_get_user_fields' ) ? woo_ce_get_user_fields() : false ) ) {
					if( $user ) {
						foreach( $user_fields as $key => $user_field )
							$user_fields[$key]['disabled'] = ( isset( $user_field['disabled'] ) ? $user_field['disabled'] : 0 );
						add_action( 'woo_ce_export_user_options_before_table', 'woo_ce_users_filter_by_user_role' );
						add_action( 'woo_ce_export_user_options_before_table', 'woo_ce_users_filter_by_date_registered' );
						add_action( 'woo_ce_export_user_options_after_table', 'woo_ce_user_sorting' );
						add_action( 'woo_ce_export_after_form', 'woo_ce_users_custom_fields' );
					}
				}
			}

			// Reviews
			if( !in_array( 'review', $hidden_types ) ) {
				if( $review_fields = ( function_exists( 'woo_ce_get_review_fields' ) ? woo_ce_get_review_fields() : false ) ) {
					if( $review ) {
						foreach( $review_fields as $key => $review_field )
							$review_fields[$key]['disabled'] = ( isset( $review_field['disabled'] ) ? $review_field['disabled'] : 0 );
						add_action( 'woo_ce_export_review_options_after_table', 'woo_ce_review_sorting' );
					}
				}
			}

			// Coupons
			if( !in_array( 'coupon', $hidden_types ) ) {
				if( $coupon_fields = ( function_exists( 'woo_ce_get_coupon_fields' ) ? woo_ce_get_coupon_fields() : false ) ) {
					if( $coupon ) {
						foreach( $coupon_fields as $key => $coupon_field )
							$coupon_fields[$key]['disabled'] = ( isset( $coupon_field['disabled'] ) ? $coupon_field['disabled'] : 0 );
						add_action( 'woo_ce_export_coupon_options_before_table', 'woo_ce_coupons_filter_by_discount_type' );
						add_action( 'woo_ce_export_coupon_options_before_table', 'woo_ce_coupon_sorting' );
					}
				}
			}

			// Subscriptions
			if( !in_array( 'subscription', $hidden_types ) ) {
				if( $subscription_fields = ( function_exists( 'woo_ce_get_subscription_fields' ) ? woo_ce_get_subscription_fields() : false ) ) {
					if( $subscription ) {
						foreach( $subscription_fields as $key => $subscription_field )
							$subscription_fields[$key]['disabled'] = ( isset( $subscription_field['disabled'] ) ? $subscription_field['disabled'] : 0 );
						add_action( 'woo_ce_export_subscription_options_before_table', 'woo_ce_subscriptions_filter_by_subscription_status' );
						add_action( 'woo_ce_export_subscription_options_before_table', 'woo_ce_subscriptions_filter_by_subscription_product' );
						add_action( 'woo_ce_export_subscription_options_before_table', 'woo_ce_subscriptions_filter_by_customer' );
						add_action( 'woo_ce_export_subscription_options_before_table', 'woo_ce_subscriptions_filter_by_source' );
						add_action( 'woo_ce_export_subscription_options_before_table', 'woo_ce_subscriptions_custom_fields_link' );
						add_action( 'woo_ce_export_subscription_options_before_table', 'woo_ce_subscription_sorting' );
						add_action( 'woo_ce_export_after_form', 'woo_ce_subscriptions_custom_fields' );
					}
				}
			}

			// Product Vendors
			if( !in_array( 'product_vendor', $hidden_types ) ) {
				if( $product_vendor_fields = ( function_exists( 'woo_ce_get_product_vendor_fields' ) ? woo_ce_get_product_vendor_fields() : false ) ) {
					if( $product_vendor ) {
						foreach( $product_vendor_fields as $key => $product_vendor_field )
							$product_vendor_fields[$key]['disabled'] = ( isset( $product_vendor_field['disabled'] ) ? $product_vendor_field['disabled'] : 0 );
					}
				}
			}

			// Commissions
			if( !in_array( 'commission', $hidden_types ) ) {
				if( $commission_fields = ( function_exists( 'woo_ce_get_commission_fields' ) ? woo_ce_get_commission_fields() : false ) ) {
					if( $commission ) {
						foreach( $commission_fields as $key => $commission_field )
							$commission_fields[$key]['disabled'] = ( isset( $commission_field['disabled'] ) ? $commission_field['disabled'] : 0 );
						add_action( 'woo_ce_export_commission_options_before_table', 'woo_ce_commissions_filter_by_date' );
						add_action( 'woo_ce_export_commission_options_before_table', 'woo_ce_commissions_filter_by_product_vendor' );
						add_action( 'woo_ce_export_commission_options_before_table', 'woo_ce_commissions_filter_by_commission_status' );
						add_action( 'woo_ce_export_commission_options_before_table', 'woo_ce_commission_sorting' );
					}
				}
			}

			// Shipping Classes
			if( !in_array( 'shipping_class', $hidden_types ) ) {
				if( $shipping_class_fields = ( function_exists( 'woo_ce_get_shipping_class_fields' ) ? woo_ce_get_shipping_class_fields() : false ) ) {
					if( $shipping_class ) {
						foreach( $shipping_class_fields as $key => $shipping_class_field )
							$shipping_class_fields[$key]['disabled'] = ( isset( $shipping_class_field['disabled'] ) ? $shipping_class_field['disabled'] : 0 );
						add_action( 'woo_ce_export_shipping_class_options_after_table', 'woo_ce_shipping_class_sorting' );
					}
				}
			}

			// Tickets
			if( !in_array( 'ticket', $hidden_types ) ) {
				if( $ticket_fields = ( function_exists( 'woo_ce_get_ticket_fields' ) ? woo_ce_get_ticket_fields() : false ) ) {
					if( $ticket ) {
						foreach( $ticket_fields as $key => $ticket_field )
							$ticket_fields[$key]['disabled'] = ( isset( $ticket_field['disabled'] ) ? $ticket_field['disabled'] : 0 );
					}
				}
			}

			// Bookings
			if( !in_array( 'booking', $hidden_types ) ) {
				if( $booking_fields = ( function_exists( 'woo_ce_get_booking_fields' ) ? woo_ce_get_booking_fields() : false ) ) {
					if( $booking ) {
						foreach( $booking_fields as $key => $booking_field )
							$booking_fields[$key]['disabled'] = ( isset( $booking_field['disabled'] ) ? $booking_field['disabled'] : 0 );
						add_action( 'woo_ce_export_booking_options_after_table', 'woo_ce_booking_sorting' );
					}
				}
			}

			// Attributes
			if( !in_array( 'attribute', $hidden_types ) ) {
				if( $attribute_fields = ( function_exists( 'woo_ce_get_attribute_fields' ) ? woo_ce_get_attribute_fields() : false ) ) {
					if( $attribute ) {
						foreach( $attribute_fields as $key => $attribute_field )
							$attribute_fields[$key]['disabled'] = ( isset( $attribute_field['disabled'] ) ? $attribute_field['disabled'] : 0 );
					}
				}
			}
			break;

		case 'fields':
			woo_ce_load_export_types();
			$export_type = ( isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '' );
			$export_types = array_keys( woo_ce_get_export_types() );
			$fields = array();
			if( in_array( $export_type, $export_types ) ) {
				if( has_filter( 'woo_ce_' . $export_type . '_fields', 'woo_ce_override_' . $export_type . '_field_labels' ) )
					remove_filter( 'woo_ce_' . $export_type . '_fields', 'woo_ce_override_' . $export_type . '_field_labels', 11 );
				if( function_exists( sprintf( 'woo_ce_get_%s_fields', $export_type ) ) )
					$fields = call_user_func( 'woo_ce_get_' . $export_type . '_fields' );
				$labels = woo_ce_get_option( $export_type . '_labels', array() );
			}
			break;

		case 'scheduled_export':
			// Show notice if Scheduled Exports is disabled
			$enable_auto = woo_ce_get_option( 'enable_auto', 0 );
			if( !$enable_auto ) {
				$override_url = esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'scheduled_export', 'action' => 'enable_scheduled_exports', '_wpnonce' => wp_create_nonce( 'woo_ce_enable_scheduled_exports' ) ), 'admin.php' ) );
				$message = sprintf( __( 'Scheduled exports are turned off from the <em>Enable scheduled exports</em> option on the Settings tab, to enable scheduled exports globally <a href="%s">click here</a>.', 'woocommerce-exporter' ), $override_url );
				woo_cd_admin_notice_html( $message, 'notice' );
			}
			// Show notice if DISABLE_WP_CRON is defined
			if( !woo_ce_get_option( 'dismiss_disable_wp_cron_prompt', 0 ) ) {
				if( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
					// Check if DISABLE_WP_CRON is set to true
					$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_disable_wp_cron_prompt', '_wpnonce' => wp_create_nonce( 'woo_ce_disable_wp_cron_prompt' ) ) ) );
					$message = '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'woocommerce-exporter' ) . '</a></span>' . __( 'It looks like WP-CRON has been disabled by setting the DISABLE_WP_CRON Constant within your wp-config.php file. If this has been done intentionally please ensure a manual CRON job triggers WP-CRON as Scheduled Exports will otherwise not run.', 'woocommerce-exporter' );
					woo_cd_admin_notice_html( $message, 'notice' );
				}
			}
			$args = array(
				'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private', 'trash' )
			);
			$scheduled_exports = woo_ce_get_scheduled_exports( $args );

			// Allow Plugin/Theme authors to adjust the length of the export method label
			$export_method_label_limit = apply_filters( 'woo_ce_scheduled_export_export_method_label_limit', 25 );

			$running = get_transient( WOO_CD_PREFIX . '_scheduled_export_id' );
			break;

		case 'export_template':
			$export_templates = woo_ce_get_export_templates();
			break;

		case 'archive':
			if( isset( $_POST['archive'] ) || isset( $_GET['trashed'] ) ) {
				if( isset( $_POST['archive'] ) ) {
					$post_ID = count( $_POST['archive'] );
				} else if( isset( $_GET['trashed'] ) ) {
					$post_ID = count( $_GET['ids'] );
				}
				$message = _n( 'Archived export has been deleted.', 'Archived exports has been deleted.', $post_ID, 'woocommerce-exporter' );
				woo_cd_admin_notice_html( $message );
			}

			if( woo_ce_get_option( 'delete_file', '1' ) ) {
				$override_url = esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'archive', 'action' => 'enable_archives', '_wpnonce' => wp_create_nonce( 'woo_ce_enable_archives' ) ), 'admin.php' ) );
				$message = sprintf( __( 'New exports generated from the Quick Export screen will not be archived here as the saving of export archives is disabled from the <em>Enable archives</em> option on the Settings tab, to enable the archives globally <a href="%s">click here</a>.', 'woocommerce-exporter' ), $override_url );
				woo_cd_admin_notice_html( $message, 'error' );
			}

			global $archives_table;

			$archives_table->prepare_items();

			$count = woo_ce_archives_quicklink_count();

			break;

		case 'settings':
			add_action( 'woo_ce_export_settings_top', 'woo_ce_export_settings_quicklinks' );
			add_action( 'woo_ce_export_settings_general', 'woo_ce_export_settings_general' );
			add_action( 'woo_ce_export_settings_after', 'woo_ce_export_settings_csv' );
			add_action( 'woo_ce_export_settings_after', 'woo_ce_export_settings_extend' );
			add_action( 'woo_ce_export_settings_general_advanced_settings_after', 'woo_ce_export_settings_general_advanced_settings_extend' );

			$debug_mode = woo_ce_get_option( 'debug_mode', 0 );
			$logging_mode = woo_ce_get_option( 'logging_mode', 0 );
			break;

		case 'tools':
			// Product Importer Deluxe
			$woo_pd_url = 'http://www.visser.com.au/woocommerce/plugins/product-importer-deluxe/';
			$woo_pd_target = ' target="_blank"';
			if( function_exists( 'woo_pd_init' ) ) {
				$woo_pd_url = esc_url( add_query_arg( array( 'page' => 'woo_pd', 'tab' => null ) ) );
				$woo_pd_target = false;
			}

			// Store Toolkit
			$woo_st_url = 'http://www.visser.com.au/woocommerce/plugins/store-toolkit/';
			$woo_st_target = ' target="_blank"';
			if( function_exists( 'woo_st_admin_init' ) ) {
				$woo_st_url = esc_url( add_query_arg( array( 'page' => 'woo_st', 'tab' => null ) ) );
				$woo_st_target = false;
			}

			// Export modules
			$module_status = ( isset( $_GET['module_status'] ) ? sanitize_text_field( $_GET['module_status'] ) : false );
			$modules = woo_ce_modules_list( $module_status );
			$modules_all = get_transient( WOO_CD_PREFIX . '_modules_all_count' );
			$modules_active = get_transient( WOO_CD_PREFIX . '_modules_active_count' );
			$modules_inactive = get_transient( WOO_CD_PREFIX . '_modules_inactive_count' );
			break;

	}
	if( $tab ) {
		if( file_exists( WOO_CD_PATH . 'templates/admin/tabs-' . $tab . '.php' ) ) {
			include_once( WOO_CD_PATH . 'templates/admin/tabs-' . $tab . '.php' );
		} else {
			$message = sprintf( __( 'We couldn\'t load the export template file <code>%s</code> within <code>%s</code>, this file should be present.', 'woocommerce-exporter' ), 'tabs-' . $tab . '.php', WOO_CD_PATH . 'templates/admin/...' );
			woo_cd_admin_notice_html( $message, 'error' );
			ob_start(); ?>
<p><?php _e( 'You can see this error for one of a few common reasons', 'woocommerce-exporter' ); ?>:</p>
<ul class="ul-disc">
	<li><?php _e( 'WordPress was unable to create this file when the Plugin was installed or updated', 'woocommerce-exporter' ); ?></li>
	<li><?php _e( 'The Plugin files have been recently changed and there has been a file conflict', 'woocommerce-exporter' ); ?></li>
	<li><?php _e( 'The Plugin file has been locked and cannot be opened by WordPress', 'woocommerce-exporter' ); ?></li>
</ul>
<p><?php _e( 'Jump onto our website and download a fresh copy of this Plugin as it might be enough to fix this issue. If this persists get in touch with us.', 'woocommerce-exporter' ); ?></p>
<?php
			ob_end_flush();
		}
	}

}

function woo_ce_export_export_types() {

	$export_type = sanitize_text_field( ( isset( $_POST['dataset'] ) ? $_POST['dataset'] : woo_ce_get_option( 'last_export', 'product' ) ) );
	$export_types = array_keys( woo_ce_get_export_types() );

	// Check if the default export type exists
	if( !in_array( $export_type, $export_types ) )
		$export_type = 'product';

	// Check if the default export type is now empty
	$default_export_type = woo_ce_get_export_type_count( $export_type );
	if( empty( $default_export_type ) )
		$export_type = 'product';

	$product = woo_ce_get_export_type_count( 'product' );
	$category = woo_ce_get_export_type_count( 'category' );
	$tag = woo_ce_get_export_type_count( 'tag' );
	$brand = woo_ce_get_export_type_count( 'brand' );
	$order = woo_ce_get_export_type_count( 'order' );
	$customer = woo_ce_get_export_type_count( 'customer' );
	$user = woo_ce_get_export_type_count( 'user' );
	$review = woo_ce_get_export_type_count( 'review' );
	$coupon = woo_ce_get_export_type_count( 'coupon' );
	$attribute = woo_ce_get_export_type_count( 'attribute' );
	$subscription = woo_ce_get_export_type_count( 'subscription' );
	$product_vendor = woo_ce_get_export_type_count( 'product_vendor' );
	$commission = woo_ce_get_export_type_count( 'commission' );
	$shipping_class = woo_ce_get_export_type_count( 'shipping_class' );
	$ticket = woo_ce_get_export_type_count( 'ticket' );
	$booking = woo_ce_get_export_type_count( 'booking' );

	$hidden_types = woo_ce_get_option( 'hidden_export_types', array() );
	if( $hidden_types == false )
		$hidden_types = array();

	ob_start();
?>
<div id="export-type">
	<h3>
		<?php _e( 'Export Types', 'woocommerce-exporter' ); ?>
		<img class="help_tip" data-tip="<?php _e( 'Select the data type you want to export. Export Type counts are refreshed hourly and can be manually refreshed by clicking the <em>Refresh counts</em> link.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	</h3>
	<div class="inside">
		<table class="form-table widefat striped">
			<thead>

				<tr>
					<th width="1%">&nbsp;</th>
					<th class="column_export-type"><?php _e( 'Export Type', 'woocommerce-exporter' ); ?></th>
					<th class="column_records">
						<?php _e( 'Records', 'woocommerce-exporter' ); ?>
						(<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'refresh_export_type_counts', '_wpnonce' => wp_create_nonce( 'woo_ce_refresh_export_type_counts' ) ) ) ); ?>"><?php _e( 'Refresh counts', 'woocommerce-exporter' ); ?></a>)
					</th>
					<th width="1%"><attr title="<?php _e( 'Actions', 'woocommerce-exporter' ); ?>">...</attr></th>
				</tr>

			</thead>
			<tbody>

<?php if( !in_array( 'product', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $product ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="product" name="dataset" value="product"<?php disabled( $product, 0 ); ?><?php checked( ( empty( $product ) ? '' : $export_type ), 'product' ); ?> />
					</td>
					<td class="name">
						<label for="product"><?php _e( 'Products', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $product; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'product', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'category', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $category ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="category" name="dataset" value="category"<?php disabled( $category, 0 ); ?><?php checked( ( empty( $category ) ? '' : $export_type ), 'category' ); ?> />
					</td>
					<td class="name">
						<label for="category"><?php _e( 'Categories', 'woocommerce-exporter' ); ?></label>
					</td>
					<td class="count">
						<?php echo $category; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'category', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'tag', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $tag ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="tag" name="dataset" value="tag"<?php disabled( $tag, 0 ); ?><?php checked( ( empty( $tag ) ? '' : $export_type ), 'tag' ); ?> />
					</td>
					<td class="name">
						<label for="tag"><?php _e( 'Tags', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $tag; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'tag', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'brand', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $brand ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="brand" name="dataset" value="brand"<?php disabled( $brand, 0 ); ?><?php checked( ( empty( $brand ) ? '' : $export_type ), 'brand' ); ?> />
					</td>
					<td class="name">
						<label for="brand"><?php _e( 'Brands', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $brand; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'brand', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'order', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $order ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="order" name="dataset" value="order"<?php disabled( $order, 0 ); ?><?php checked( ( empty( $order ) ? '' : $export_type ), 'order' ); ?>/>
					</td>
					<td class="name">
						<label for="order"><?php _e( 'Orders', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $order; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'order', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'customer', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $customer ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="customer" name="dataset" value="customer"<?php disabled( $customer, 0 ); ?><?php checked( ( empty( $customer ) ? '' : $export_type ), 'customer' ); ?>/>
					</td>
					<td class="name">
						<label for="customer"><?php _e( 'Customers', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $customer; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'customer', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'user', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $user ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="user" name="dataset" value="user"<?php disabled( $user, 0 ); ?><?php checked( ( empty( $user ) ? '' : $export_type ), 'user' ); ?>/>
					</td>
					<td class="name">
						<label for="user"><?php _e( 'Users', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $user; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'user', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'review', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $review ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="review" name="dataset" value="review"<?php disabled( $review, 0 ); ?><?php checked( ( empty( $review ) ? '' : $export_type ), 'review' ); ?>/>
					</td>
					<td class="name">
						<label for="review"><?php _e( 'Reviews', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $review; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'review', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'coupon', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $coupon ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="coupon" name="dataset" value="coupon"<?php disabled( $coupon, 0 ); ?><?php checked( ( empty( $coupon ) ? '' : $export_type ), 'coupon' ); ?> />
					</td>
					<td class="name">
						<label for="coupon"><?php _e( 'Coupons', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $coupon; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'coupon', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'subscription', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $subscription ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="subscription" name="dataset" value="subscription"<?php disabled( $subscription, 0 ); ?><?php checked( ( empty( $subscription ) ? '' : $export_type ), 'subscription' ); ?> />
					</td>
					<td class="name">
						<label for="subscription"><?php _e( 'Subscriptions', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $subscription; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'subscription', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'product_vendor', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $product_vendor ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="product_vendor" name="dataset" value="product_vendor"<?php disabled( $product_vendor, 0 ); ?><?php checked( ( empty( $product_vendor ) ? '' : $export_type ), 'product_vendor' ); ?> />
					</td>
					<td class="name">
						<label for="product_vendor"><?php _e( 'Product Vendors', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $product_vendor; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'product_vendor', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'commission', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $commission ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="commission" name="dataset" value="commission"<?php disabled( $commission, 0 ); ?><?php checked( ( empty( $commission ) ? '' : $export_type ), 'commission' ); ?> />
					</td>
					<td class="name">
						<label for="commission"><?php _e( 'Commissions', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $commission; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'commission', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'shipping_class', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $shipping_class ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="shipping_class" name="dataset" value="shipping_class"<?php disabled( $shipping_class, 0 ); ?><?php checked( ( empty( $shipping_class ) ? '' : $export_type ), 'shipping_class' ); ?> />
					</td>
					<td class="name">
						<label for="shipping_class"><?php _e( 'Shipping Classes', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $shipping_class; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'shipping_class', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'ticket', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $ticket ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="ticket" name="dataset" value="ticket"<?php disabled( $ticket, 0 ); ?><?php checked( ( empty( $ticket ) ? '' : $export_type ), 'ticket' ); ?> />
					</td>
					<td class="name">
						<label for="ticket"><?php _e( 'Tickets', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $ticket; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'ticket', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'booking', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $booking ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="booking" name="dataset" value="booking"<?php disabled( $booking, 0 ); ?><?php checked( ( empty( $booking ) ? '' : $export_type ), 'booking' ); ?> />
					</td>
					<td class="name">
						<label for="booking"><?php _e( 'Bookings', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $booking; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'booking', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

<?php if( !in_array( 'attribute', $hidden_types ) ) { ?>
				<tr class="<?php echo ( empty( $attribute ) ? 'type-disabled' : '' ); ?>">
					<td width="1%" class="sort">
						<input type="radio" id="attribute" name="dataset" value="attribute"<?php disabled( $attribute, 0 ); ?><?php checked( ( empty( $attribute ) ? '' : $export_type ), 'attribute' ); ?> />
					</td>
					<td class="name">
						<label for="attribute"><?php _e( 'Attributes', 'woocommerce-exporter' ); ?></label>
					</td>
					<td>
						<?php echo $attribute; ?>
					</td>
					<td width="1%" class="actions"><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'hide_export_type', 'export_type' => 'attribute', '_wpnonce' => wp_create_nonce( 'woo_ce_hide_export_type' ) ) ) ); ?>" title="<?php _e( 'Hide this export type', 'woocommerce-exporter' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></td>
				</tr>
<?php } ?>

			</tbody>
		</table>
		<!-- .form-table -->
		<p>
			<input id="quick_export" type="button" value="<?php _e( 'Quick Export', 'woocommerce-exporter' ); ?>" class="button" />
		</p>
	</div>
	<!-- .inside -->
</div>
<!-- .postbox -->

<hr />

<?php
	ob_end_flush();

}

function woo_ce_export_export_options() {

	ob_start();

	$template = 'quick_export.php';
	if( file_exists( WOO_CD_PATH . 'includes/admin/' . $template ) ) {

		include_once( WOO_CD_PATH . 'includes/admin/' . $template );

		add_action( 'woo_ce_export_options', 'woo_ce_export_options_export_format' );
		add_action( 'woo_ce_export_options', 'woo_ce_export_options_export_template' );
		add_action( 'woo_ce_export_options', 'woo_ce_export_options_troubleshooting' );
		add_action( 'woo_ce_export_options', 'woo_ce_export_options_limit_volume' );
		add_action( 'woo_ce_export_options', 'woo_ce_export_options_volume_offset' );

	} else {

		$message = sprintf( __( 'We couldn\'t load the template file <code>%s</code> within <code>%s</code>, this file should be present.', 'woocommerce-exporter' ), $template, WOO_CD_PATH . 'includes/admin/...' );
?>
<p><strong><?php echo $message; ?></strong></p>
<p><?php _e( 'You can see this error for one of a few common reasons', 'woocommerce-exporter' ); ?>:</p>
<ul class="ul-disc">
	<li><?php _e( 'WordPress was unable to create this file when the Plugin was installed or updated', 'woocommerce-exporter' ); ?></li>
	<li><?php _e( 'The Plugin files have been recently changed and there has been a file conflict', 'woocommerce-exporter' ); ?></li>
	<li><?php _e( 'The Plugin file has been locked and cannot be opened by WordPress', 'woocommerce-exporter' ); ?></li>
</ul>
<p><?php _e( 'Jump onto our website and download a fresh copy of this Plugin as it might be enough to fix this issue. If this persists get in touch with us.', 'woocommerce-exporter' ); ?></p>
<?php

	}
?>
<div class="postbox" id="export-options">
	<h3 class="hndle"><?php _e( 'Export Options', 'woocommerce-exporter' ); ?></h3>
	<div class="inside">
		<p class="description"><?php _e( 'You can find additional export options under the Settings tab at the top of this screen.', 'woocommerce-exporter' ); ?></p>

		<?php do_action( 'woo_ce_export_options_before' ); ?>

		<table class="form-table">

			<?php do_action( 'woo_ce_export_options' ); ?>

			<?php do_action( 'woo_ce_export_options_table_after' ); ?>

		</table>
		<p><?php _e( 'Click the Export button above to apply these changes and generate your export file.', 'woocommerce-exporter' ); ?></p>

		<?php do_action( 'woo_ce_export_options_after' ); ?>

	</div>
</div>
<!-- .postbox -->

<?php
	ob_end_flush();

}

function woo_ce_admin_footer_javascript() {

	$notice_timeout = apply_filters( 'woo_ce_quick_export_in_process_notice_timeout', 10 );
	$notice_timeout = ( !empty( $notice_timeout ) ? $notice_timeout * 1000 : 0 );

	$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );

	// Check if we need to run date formatting for DatePicker
	if( $date_format <> 'd/m/Y' ) {

		// Convert the PHP date format to be DatePicker compatible
		$php_date_formats = array( 'Y', 'm', 'd' );
		$js_date_formats = array( 'yy', 'mm', 'dd' );

		// Exception for 'F j, Y'
		if( $date_format == 'F j, Y' )
			$date_format = 'd/m/Y';

		$date_format = str_replace( $php_date_formats, $js_date_formats, $date_format );

	} else {
		$date_format = 'dd/mm/yy';
	}

	// In-line javascript
	ob_start(); ?>
<script type="text/javascript">
jQuery(document).ready( function($) {

	var $j = jQuery.noConflict();
	// Date Picker
	if( $j.isFunction($j.fn.datepicker) ) {
		$j('.datepicker').datepicker({
			dateFormat: '<?php echo $date_format; ?>'
		}).on('change', function() {
			// Products
			if( $j(this).hasClass('product_export') )
				$j('input:radio[name="product_dates_filter"][value="manual"]').prop( 'checked', true );
			// Users
			if( $j(this).hasClass('user_export') )
				$j('input:radio[name="user_dates_filter"][value="manual"]').prop( 'checked', true );
			// Orders 
			if( $j(this).hasClass('order_export') )
				$j('input:radio[name="order_dates_filter"][value="manual"]').prop( 'checked', true );
			// YITH WooCommerce Delivery Date Premium - http://yithemes.com/themes/plugins/yith-woocommerce-delivery-date/
			if( $j(this).hasClass('order_delivery_dates_export') )
				$j('input:radio[name="order_delivery_dates_filter"][value="manual"]').prop( 'checked', true );
			// WooCommerce Bookings - http://www.woothemes.com/products/woocommerce-bookings/
			if( $j(this).hasClass('order_booking_dates_export') )
				$j('input:radio[name="order_booking_dates_filter"][value="manual"]').prop( 'checked', true );
		});
	}

	// This shows the Quick export in progress... notice
<?php if( !empty( $notice_timeout ) ) { ?>
	$j("#postform").on("submit", function(){
		$j('#message.error').fadeOut('slow');
		$j('#message-quick_export').fadeIn().delay(<?php echo $notice_timeout; ?>).fadeOut('slow');
		scroll(0,0);
	});
<?php } ?>

});
</script>
<?php
	ob_end_flush();

}

// Display the memory usage in the screen footer
function woo_ce_admin_footer_text( $footer_text = '' ) {

	$current_screen = get_current_screen();
	$pages = array(
		'woocommerce_page_woo_ce'
	);
	// Check to make sure we're on the Export screen
	if ( isset( $current_screen->id ) && apply_filters( 'woo_ce_display_admin_footer_text', in_array( $current_screen->id, $pages ) ) ) {
		$memory_usage = woo_ce_current_memory_usage( false );
		$memory_limit = absint( ini_get( 'memory_limit' ) );
		$memory_percent = absint( $memory_usage / $memory_limit * 100 );
		$memory_color = 'font-weight:normal;';
		if( $memory_percent > 75 )
			$memory_color = 'font-weight:bold; color:orange;';
		if( $memory_percent > 90 )
			$memory_color = 'font-weight:bold; color:red;';
		$footer_text .= ' | ' . sprintf( __( 'Memory: %s of %s MB (%s)', 'woocommerce-exporter' ), $memory_usage, $memory_limit, sprintf( '<span style="%s">%s</span>', $memory_color, $memory_percent . '%' ) );
		$footer_text .= ' | ' . sprintf( __( 'Stopwatch: %s seconds', 'woocommerce-exporter' ), timer_stop(0, 3) );
	}
	return $footer_text;

}

function woo_ce_modules_status_class( $status = 'inactive' ) {

	$output = '';
	switch( $status ) {

		case 'active':
			$output = 'green';
			break;

		case 'inactive':
			$output = 'yellow';
			break;

	}
	echo $output;

}

function woo_ce_modules_status_label( $status = 'inactive' ) {

	$output = '';
	switch( $status ) {

		case 'active':
			$output = __( 'OK', 'woocommerce-exporter' );
			break;

		case 'inactive':
			$output = __( 'Install', 'woocommerce-exporter' );
			break;

	}
	echo $output;

}

function woo_ce_is_network_admin() {

	// Check if this is a WordPress MultiSite setup
	if( is_multisite() ) {
		// Check for the Network Admin
		if( is_main_network( get_current_blog_id() ) ) {
			$sites = wp_get_sites();
			if( !empty( $sites ) )
				return true;
		}
	}

}

function woo_ce_admin_dashboard_setup() {

	// Check that the User has permission to view the Dashboard widgets
	$user_capability = apply_filters( 'woo_ce_admin_dashboard_user_capability', 'view_woocommerce_reports' );
	if( current_user_can( $user_capability ) ) {
		wp_add_dashboard_widget( 'woo_ce_scheduled_export_widget', __( 'Scheduled Exports', 'woocommerce-exporter' ), 'woo_ce_admin_scheduled_export_widget', 'woo_ce_admin_scheduled_export_widget_configure' );
		wp_add_dashboard_widget( 'woo_ce_recent_scheduled_export_widget', __( 'Recent Scheduled Exports', 'woocommerce-exporter' ), 'woo_ce_admin_recent_scheduled_export_widget', 'woo_ce_admin_recent_scheduled_export_widget_configure' );
	}

}
?>