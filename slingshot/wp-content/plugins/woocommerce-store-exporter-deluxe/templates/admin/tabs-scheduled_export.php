<ul class="subsubsub">
	<li><a href="#scheduled-exports"><?php _e( 'Scheduled Exports', 'woocommerce-exporter' ); ?></a> |</li>
	<li><a href="#recent-scheduled-exports"><?php _e( 'Recent Scheduled Exports', 'woocommerce-exporter' ); ?></a></li>
	<?php do_action( 'woo_ce_scheduled_export_settings_top' ); ?>
</ul>
<!-- .subsubsub -->
<br class="clear" />

<?php do_action( 'woo_ce_before_scheduled_exports' ); ?>

<h3 id="scheduled-exports">
	<?php _e( 'Scheduled Exports', 'woocommerce-exporter' ); ?>
	<a href="<?php echo esc_url( admin_url( add_query_arg( 'post_type', 'scheduled_export', 'post-new.php' ) ) ); ?>" class="add-new-h2"><?php _e( 'Add New', 'woocommerce-exporter' ); ?></a>
</h3>

<table class="widefat page fixed striped scheduled-exports">
	<thead>

		<tr>
			<th class="manage-column"><?php _e( 'Name', 'woocommerce-exporter' ); ?></th>
			<th class="manage-column"><?php _e( 'Export Type', 'woocommerce-exporter' ); ?></th>
			<th class="manage-column"><?php _e( 'Export Format', 'woocommerce-exporter' ); ?></th>
			<th class="manage-column"><?php _e( 'Export Method', 'woocommerce-exporter' ); ?></th>
			<th class="manage-column"><?php _e( 'Status', 'woocommerce-exporter' ); ?></th>
			<th class="manage-column"><?php _e( 'Frequency', 'woocommerce-exporter' ); ?></th>
			<th class="manage-column"><?php _e( 'Next run', 'woocommerce-exporter' ); ?></th>
			<th class="manage-column"><?php _e( 'Action', 'woocommerce-exporter' ); ?></th>
		</tr>

	</thead>
	<tbody id="the-list">

<?php if( !empty( $scheduled_exports ) ) { ?>
	<?php foreach( $scheduled_exports as $scheduled_export ) { ?>
<?php
	$export_method = get_post_meta( $scheduled_export, '_export_method', true );
	// Generate an export method label if available
	$export_method_label = '';
	switch( $export_method ) {

		// Upload to remote FTP/SFTP
		case 'ftp':
			$export_method_label_raw = get_post_meta( $scheduled_export, '_method_ftp_host', true );
			$export_method_label = $export_method_label_raw;
			break;

		// Save to this server
		case 'save':
			$export_method_label_raw = get_post_meta( $scheduled_export, '_method_save_path', true );
			$export_method_label_raw .= get_post_meta( $scheduled_export, '_method_save_filename', true );
			$export_method_label = $export_method_label_raw;
			break;

		// Send as e-mail
		case 'email':
			$export_method_label_raw = get_post_meta( $scheduled_export, '_method_email_to', true );
			$export_method_label = $export_method_label_raw;
			break;

	}
	// Limit length of export method label
	if( strlen( $export_method_label ) > $export_method_label_limit )
		$export_method_label = substr( $export_method_label, 0, $export_method_label_limit ) . '...';
	$post_status = get_post_status( $scheduled_export );
	$refresh_timeout = apply_filters( 'woo_ce_admin_scheduled_export_refresh_timeout', 30, $scheduled_export );
?>
		<tr id="post-<?php echo $scheduled_export; ?>"<?php echo ( $post_status == 'draft' ? ' class="scheduled-export-draft"' : '' ); ?>>
			<td class="post-title column-title">
		<?php if( $post_status == 'trash' ) { ?>
				<strong><?php echo woo_ce_format_post_title( get_the_title( $scheduled_export ) ); ?></strong>
		<?php } else { ?>
				<strong><a href="<?php echo get_edit_post_link( $scheduled_export ); ?>" title="<?php _e( 'Edit scheduled export', 'woocommerce-exporter' ); ?>"><?php echo woo_ce_format_post_title( get_the_title( $scheduled_export ) ); ?></a></strong>
		<?php } ?>
				<div class="row-actions">
		<?php if( $post_status == 'trash' ) { ?>
					<a href="<?php echo wp_nonce_url( admin_url( add_query_arg( array( 'post' => $scheduled_export, 'action' => 'untrash' ), 'edit.php' ) ), 'untrash-post_' . $scheduled_export ); ?>"><?php _e( 'Restore', 'woocommerce-exporter' ); ?></a> | 
		<?php } else { ?>
					<a href="<?php echo get_edit_post_link( $scheduled_export ); ?>" title="<?php _e( 'Edit this scheduled export', 'woocommerce-exporter' ); ?>"><?php _e( 'Edit', 'woocommerce-exporter' ); ?></a> | 
		<?php } ?>
					<a href="<?php echo add_query_arg( array( 'action' => 'clone_scheduled_export', 'post' => $scheduled_export, '_wpnonce' => wp_create_nonce( 'woo_ce_clone_scheduled_export' ) ) ); ?>" title="<?php _e( 'Duplicate this Scheduled Export', 'woocommerce-exporter' ); ?>"><?php _e( 'Clone', 'woocommerce-exporter' ); ?></a> | 
		<?php if( $post_status == 'draft' ) { ?>
					<a href="<?php echo add_query_arg( array( 'action' => 'publish_scheduled_export', 'post' => $scheduled_export, '_wpnonce' => wp_create_nonce( 'woo_ce_publish_scheduled_export' ) ) ); ?>" title="<?php _e( 'Pushlish this Scheduled Export', 'woocommerce-exporter' ); ?>"><?php _e( 'Publish', 'woocommerce-exporter' ); ?></a> | 
		<?php } else if( $post_status == 'publish' ) { ?>
					<a href="<?php echo add_query_arg( array( 'action' => 'draft_scheduled_export', 'post' => $scheduled_export, '_wpnonce' => wp_create_nonce( 'woo_ce_draft_scheduled_export' ) ) ); ?>" title="<?php _e( 'Draft this Scheduled Export', 'woocommerce-exporter' ); ?>"><?php _e( 'Draft', 'woocommerce-exporter' ); ?></a> | 
		<?php } ?>
					<span class="trash"><a href="<?php echo get_delete_post_link( $scheduled_export, null, true ); ?>" class="submitdelete" title="<?php _e( 'Delete this scheduled export', 'woocommerce-exporter' ); ?>"><?php _e( 'Delete', 'woocommerce-exporter' ); ?></a></span>
				</div>
				<!-- .row-actions -->
			</td>
			<td><?php echo ucfirst( get_post_meta( $scheduled_export, '_export_type', true ) ); ?></td>
			<td><?php echo strtoupper( get_post_meta( $scheduled_export, '_export_format', true ) ); ?></td>
			<td>
				<?php echo woo_ce_format_export_method( $export_method ); ?>
				<?php echo ( !empty( $export_method_label ) ? '<br /><span class="meta" title="' . $export_method_label_raw . '">' . $export_method_label . '</span>' : '' ); ?>
			</td>
			<td><?php echo ucfirst( $post_status ); ?></td>
			<td>
				<?php echo ucfirst( get_post_meta( $scheduled_export, '_auto_schedule', true ) == 'custom' ? sprintf( __( 'Every %d minutes', 'woocommerce-exporter' ), get_post_meta( $scheduled_export, '_auto_interval', true ) ) : get_post_meta( $scheduled_export, '_auto_schedule', true ) ); ?>
			</td>
			<td class="next_run">
		<?php if( woo_ce_get_next_scheduled_export( $scheduled_export ) != false ) { ?>
			<?php if( $running == $scheduled_export ) { ?>
				<?php _e( 'Exporting in background...', 'woocommerce-exporter' ); ?>
			<?php } else { ?>
				<?php printf( __( 'Scheduled to run in %s', 'woocommerce-exporter' ), woo_ce_get_next_scheduled_export( $scheduled_export ) ); ?>
			<?php } ?>
		<?php } else { ?>
				<?php _e( 'Not scheduled', 'woocommerce-exporter' ); ?>
		<?php } ?>
			</td>
			<td>
		<?php if( $running == $scheduled_export ) { ?>
				<a href="<?php echo add_query_arg( array( 'action' => 'cancel_scheduled_export', 'post' => $scheduled_export, '_wpnonce' => wp_create_nonce( 'woo_ce_cancel_scheduled_export' ) ) ); ?>" class="button"><?php _e( 'Abort', 'woocommerce-exporter' ); ?></a>
		<?php } else { ?>
				<a href="<?php echo add_query_arg( array( 'action' => 'override_scheduled_export', 'post' => $scheduled_export, '_wpnonce' => wp_create_nonce( 'woo_ce_override_scheduled_export' ) ) ); ?>" title="<?php echo ( ( in_array( $post_status, array( 'draft', 'trash' ) ) || $enable_auto == false ) ? __( 'Scheduled exports are turned off or the Post Status for this Scheduled export is set to Draft or been deleted.', 'woocommerce-exporter' ) : __( 'Run this scheduled export now', 'woocommerce-exporter' ) ); ?>" class="button<?php echo( ( in_array( $post_status, array( 'draft', 'trash' ) ) || $enable_auto == false ) ? ' disabled' : '' ); ?> execute_now" data-scheduled-id="<?php echo $scheduled_export; ?>" data-refresh-timeout="<?php echo $refresh_timeout; ?>"><?php _e( 'Execute', 'woocommerce-exporter' ); ?></a>
		<?php } ?>
			</td>
		</tr>

	<?php } ?>
<?php } else { ?>
		<tr>
			<td class="colspanchange" colspan="6"><?php _e( 'No scheduled exports found.', 'woocommerce-exporter' ); ?></td>
		</tr>
<?php } ?>

	</tbody>

</table>
<!-- .scheduled-exports -->

<?php if( !empty( $scheduled_exports ) ) { ?>
<p style="text-align:right;"><?php printf( __( '%d items', 'woocommerce-exporter' ), count( $scheduled_exports ) ); ?></p>
<?php } ?>

<hr />

<?php do_action( 'woo_ce_after_scheduled_exports' ); ?>