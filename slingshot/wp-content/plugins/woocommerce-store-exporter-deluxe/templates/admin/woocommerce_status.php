<table class="wc_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Templates"><?php _e( 'Store Exports', 'woocommerce-exporter' ); ?><?php echo wc_help_tip( __( 'This section shows diagnostic details for Store Exporter Deluxe.', 'woocommerce-exporter' ) ); ?></th>
		</tr>
	</thead>
	<tbody>

		<tr>
			<td data-export-label="<?php _e( 'Debugging mode for Store Exporter Deluxe', 'woocommerce-exporter' ); ?>"><?php _e( 'WOO_CD_DEBUG', 'woocommerce-exporter' ); ?></td>
			<td class="help"><?php echo wc_help_tip( __( 'Displays whether or not Store Exporter Deluxe is currently in debugging mode.', 'woocommerce-exporter' ) ); ?></td>
			<td>
<?php
if( $debug_mode ) {
	echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Debugging mode is enabled. Quick Exports will not generate physical export files.', 'woocommerce-exporter' ) . '</mark>';
	echo '&nbsp;<a href="' . $debug_mode_url . '">Turn off Debugging mode</a>';
} else {
	echo '<mark class="yes"><span class="dashicons dashicons-yes"></span>No</mark>';
	echo '&nbsp;<a href="' . $debug_mode_url . '">Turn on Debugging mode</a>';
}
?>
			</td>
		</tr>

		<tr>
			<td data-export-label="<?php _e( 'Extended logging mode for Store Exporter Deluxe', 'woocommerce-exporter' ); ?>"><?php _e( 'WOO_CD_LOGGING', 'woocommerce-exporter' ); ?></td>
			<td class="help"><?php echo wc_help_tip( __( 'Displays whether or not Store Exporter Deluxe is currently in extended logging mode.', 'woocommerce' ) ); ?></td>
			<td>
<?php
if( $logging_mode ) {
	echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . __( 'Extended logging mode is enabled. Export processing times will be longer than typical exports.', 'woocommerce-exporter' ) . '</mark>';
	echo '&nbsp;<a href="' . $logging_mode_url . '">Turn off Logging mode</a>';
} else {
	echo '<mark class="yes"><span class="dashicons dashicons-yes"></span>No</mark>';
	echo '&nbsp;<a href="' . $logging_mode_url . '">Turn on Logging mode</a>';
}
?>
			</td>
		</tr>
<?php
if( $logging_mode ) { ?>
		<tr>
			<td>&nbsp;</td>
			<td class="help">&nbsp;</td>
			<td>
<?php
// Check for Scheduled Export - E-mail debugging
echo 'woo_ce_debug_cron_export_email: ' . ( apply_filters( 'woo_ce_debug_cron_export_email', false ) ? __( 'On', 'woocommerce-exporter' ) : __( 'Off', 'woocommerce-exporter' ) ) . '<br />';

// Check for Scheduled Export - FTP debugging
echo 'woo_ce_debug_cron_export_ftp: ' . ( apply_filters( 'woo_ce_debug_cron_export_ftp', false ) ? __( 'On', 'woocommerce-exporter' ) : __( 'Off', 'woocommerce-exporter' ) ) . '<br />';

// Check for WooCommerce Subscriptions debugging
echo 'woo_ce_debug_subscriptions: ' . ( apply_filters( 'woo_ce_debug_subscriptions', false ) ? __( 'On', 'woocommerce-exporter' ) : __( 'Off', 'woocommerce-exporter' ) ) . '<br />';

// Check for Products WP_Query args debugging
echo 'woo_ce_debug_get_products_args: ' . ( apply_filters( 'woo_ce_debug_get_products_args', false ) ? __( 'On', 'woocommerce-exporter' ) : __( 'Off', 'woocommerce-exporter' ) ) . '<br />';

// Check for Product Attributes debugging
echo 'woo_ce_debug_product_attributes: ' . ( apply_filters( 'woo_ce_debug_product_attributes', false ) ? __( 'On', 'woocommerce-exporter' ) : __( 'Off', 'woocommerce-exporter' ) ) . '<br />';

// Check for Orders debugging
echo 'woo_ce_debug_orders: ' . ( apply_filters( 'woo_ce_debug_orders', false ) ? __( 'On', 'woocommerce-exporter' ) : __( 'Off', 'woocommerce-exporter' ) ) . '<br />';

// Check for Orders WP_Query args debugging
echo 'woo_ce_debug_get_orders_args: ' . ( apply_filters( 'woo_ce_debug_get_orders_args', false ) ? __( 'On', 'woocommerce-exporter' ) : __( 'Off', 'woocommerce-exporter' ) ) . '<br />';

// Check for Orders date filter debugging
echo 'woo_ce_debug_get_orders_filter_date: ' . ( apply_filters( 'woo_ce_debug_get_orders_filter_date', false ) ? __( 'On', 'woocommerce-exporter' ) : __( 'Off', 'woocommerce-exporter' ) ) . '<br />';

// Check for bulk Order export debugging
echo 'woo_ce_debug_admin_export_bulk_action: ' . ( apply_filters( 'woo_ce_debug_admin_export_bulk_action', false ) ? __( 'On', 'woocommerce-exporter' ) : __( 'Off', 'woocommerce-exporter' ) ) . '<br />';

// Check for Scheduled Export debugging
echo 'woo_ce_scheduled_export_filters_order_debugging: ' . ( apply_filters( 'woo_ce_scheduled_export_filters_order_debugging', false ) ? __( 'On', 'woocommerce-exporter' ) : __( 'Off', 'woocommerce-exporter' ) ) . '<br />';

// Check for UTF-8 string debugging
echo 'woo_ce_debug_detect_value_string: 	' . ( apply_filters( 'woo_ce_debug_detect_value_string', false ) ? __( 'On', 'woocommerce-exporter' ) : __( 'Off', 'woocommerce-exporter' ) ) . '<br />';

// Check for format custom meta debugging
echo 'woo_ce_debug_format_custom_meta: 	' . ( apply_filters( 'woo_ce_debug_format_custom_meta', false ) ? __( 'On', 'woocommerce-exporter' ) : __( 'Off', 'woocommerce-exporter' ) ) . '<br />';
?>
			</td>
		</tr>

<?php } ?>

		<tr>
			<td data-export-label="<?php _e( 'Scheduled Export to FTP', 'woocommerce-exporter' ); ?>"><?php _e( 'Export to FTP', 'woocommerce-exporter' ); ?></td>
			<td class="help"><?php echo wc_help_tip( __( 'Displays whether or not the current hosting account supports exporting to FTP.', 'woocommerce' ) ); ?></td>
			<td>
<?php
if( empty( $missing_ftp_functions ) ) {
	echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
} else {
	echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'The required FTP functions are not available; %s. Contact your hosting provider.', 'woocommerce-exporter' ), implode( ', ', $missing_ftp_functions ) ) . '</mark>';
}
?>
			</td>
		</tr>

		<tr>
			<td data-export-label="<?php _e( 'Scheduled Export to SFTP', 'woocommerce-exporter' ); ?>"><?php _e( 'Export to SFTP', 'woocommerce-exporter' ); ?></td>
			<td class="help"><?php echo wc_help_tip( __( 'Displays whether or not the current hosting account supports exporting to SFTP.', 'woocommerce' ) ); ?></td>
			<td>
<?php
if( empty( $missing_sftp_functions ) ) {
	echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
} else {
	echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf( __( 'The required SFTP functions are not available; %s. Contact your hosting provider.', 'woocommerce-exporter' ), implode( ', ', $missing_sftp_functions ) ) . '</mark>';
}
?>
			</td>
		</tr>

	</tbody>
</table>