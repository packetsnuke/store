<ul class="subsubsub">
	<li><a href="#general-settings"><?php _e( 'General Settings', 'woocommerce-exporter' ); ?></a> |</li>
	<li><a href="#csv-settings"><?php _e( 'CSV Settings', 'woocommerce-exporter' ); ?></a></li>
	<?php do_action( 'woo_ce_export_settings_top' ); ?>
</ul>
<!-- .subsubsub -->
<br class="clear" />

<form method="post">
	<table class="form-table">
		<tbody>

			<?php do_action( 'woo_ce_export_settings_before' ); ?>

			<tr id="general-settings">
				<td colspan="2" style="padding:0;">
					<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<?php _e( 'General Settings', 'woocommerce-exporter' ); ?></h3>
					<p class="description"><?php _e( 'Manage export options across Store Exporter Deluxe from this screen. Options are broken into sections for different export formats and methods. Click Save Changes to apply changes.', 'woocommerce-exporter' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'woo_ce_export_settings_general' ); ?>

			<tr>
				<th>&nbsp;</th>
				<td style="vertical-align:top;">
					<p><a href="#" id="advanced-settings"><?php _e( 'View advanced settings', 'woocommerce-exporter' ); ?></a></p>
					<div class="advanced-settings">
						<ul>
							<li><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'reset_hidden_export_types', '_wpnonce' => wp_create_nonce( 'woo_ce_reset_hidden_export_types' ) ) ) ); ?>"><?php _e( 'Reset hidden Export Types', 'woocommerce-exporter' ); ?></a></li>
<?php if( $debug_mode ) { ?>
							<li><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'disable_debug_mode', '_wpnonce' => wp_create_nonce( 'woo_ce_disable_debug_mode' ) ) ) ); ?>"><?php _e( 'Disable debugging mode', 'woocommerce-exporter' ); ?></a></li>
<?php } else { ?>
							<li><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'enable_debug_mode', '_wpnonce' => wp_create_nonce( 'woo_ce_enable_debug_mode' ) ) ) ); ?>"><?php _e( 'Enable debugging mode', 'woocommerce-exporter' ); ?></a></li>
<?php } ?>
<?php if( $logging_mode ) { ?>
							<li><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'disable_logging_mode', '_wpnonce' => wp_create_nonce( 'woo_ce_disable_logging_mode' ) ) ) ); ?>"><?php _e( 'Disable logging mode', 'woocommerce-exporter' ); ?></a></li>
<?php } else { ?>
							<li><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'enable_logging_mode', '_wpnonce' => wp_create_nonce( 'woo_ce_enable_logging_mode' ) ) ) ); ?>"><?php _e( 'Enable logging mode', 'woocommerce-exporter' ); ?></a></li>
<?php } ?>
							<li><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'nuke_notices', '_wpnonce' => wp_create_nonce( 'woo_ce_nuke_notices' ) ) ) ); ?>" class="delete" data-confirm="<?php _e( 'This will restore all dismissed notices associated with Store Exporter Deluxe. Are you sure you want to proceed?', 'woocommerce-exporter' ); ?>"><?php _e( 'Reset dismissed Store Export Deluxe notices', 'woocommerce-exporter' ); ?></a></li>
							<li><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'nuke_options', '_wpnonce' => wp_create_nonce( 'woo_ce_nuke_options' ) ) ) ); ?>" class="delete" data-confirm="<?php _e( 'This will permanently delete all WordPress Options associated with Store Exporter Deluxe. Are you sure you want to proceed?', 'woocommerce-exporter' ); ?>"><?php _e( 'Delete Store Exporter Deluxe WordPress Options', 'woocommerce-exporter' ); ?></a></li>
							<li><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'nuke_archives', '_wpnonce' => wp_create_nonce( 'woo_ce_nuke_archives' ) ) ) ); ?>" class="delete" data-confirm="<?php _e( 'This will permanently delete all saved exports listed within the Archives screen of Store Exporter Deluxe. Are you sure you want to proceed?', 'woocommerce-exporter' ); ?>"><?php _e( 'Delete archived exports', 'woocommerce-exporter' ); ?></a></li>
							<li><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'nuke_scheduled_exports', '_wpnonce' => wp_create_nonce( 'woo_ce_nuke_scheduled_exports' ) ) ) ); ?>" class="delete" data-confirm="<?php _e( 'This will permanently delete all Scheduled Exports associated with Store Exporter Deluxe. Are you sure you want to proceed?', 'woocommerce-exporter' ); ?>"><?php _e( 'Delete Scheduled Exports', 'woocommerce-exporter' ); ?></a></li>
							<li><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'nuke_cron', '_wpnonce' => wp_create_nonce( 'woo_ce_nuke_cron' ) ) ) ); ?>" class="delete" data-confirm="<?php _e( 'This will permanently clear the WordPress Option \'cron\', don\'t worry though as it will be refreshed on the next screen load. Are you sure you want to proceed?', 'woocommerce-exporter' ); ?>"><?php _e( 'Reset WP-CRON', 'woocommerce-exporter' ); ?></a></li>
							<?php do_action( 'woo_ce_export_settings_general_advanced_settings_after' ); ?>
						</ul>
					</div>
					<!-- .advanced-settings -->
				</td>
			</tr>

			<tr id="csv-settings">
				<td colspan="2" style="padding:0;">
					<hr />
					<h3><div class="dashicons dashicons-media-spreadsheet"></div>&nbsp;<?php _e( 'CSV Settings', 'woocommerce-exporter' ); ?></h3>
				</td>
			</tr>

			<?php do_action( 'woo_ce_export_settings_after' ); ?>

		</tbody>
	</table>
	<!-- .form-table -->
	<p class="submit">
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'woocommerce-exporter' ); ?>" />
	</p>
	<input type="hidden" name="action" value="save-settings" />
	<?php wp_nonce_field( 'woo_ce_save_settings' ); ?>
</form>
<?php do_action( 'woo_ce_export_settings_bottom' ); ?>