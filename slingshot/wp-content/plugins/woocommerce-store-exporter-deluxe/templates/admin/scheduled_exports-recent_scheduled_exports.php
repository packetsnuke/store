
<div id="poststuff" class="recent-scheduled-exports">

	<p class="pagination">
		<span class="displaying-num"><?php printf( __( '%d items', 'woocommerce-exporter' ), $size ); ?></span>
		<span class="pagination-links"><?php echo $pagination_links; ?></span>
	</p>

	<div id="recent-scheduled-exports" class="postbox">
		<h3 class="hndle"><?php _e( 'Recent Scheduled Exports' ); ?></h3>
		<div class="inside">

<?php if( !empty( $recent_exports ) ) { ?>
			<ol>
	<?php foreach( $recent_exports as $key => $recent_export ) { ?>
				<li id="recent-scheduled-export-<?php echo $key; ?>" class="recent-scheduled-export scheduled-export-<?php echo ( !empty( $recent_export['error'] ) ? 'error' : 'success' ); ?>">
					<p><?php echo $recent_export['name']; ?>
		<?php if( !empty( $recent_export['post_id'] ) && get_post_status( $recent_export['post_id'] ) !== false ) { ?>
						 - 
						<a href="<?php echo wp_get_attachment_url( $recent_export['post_id'] ); ?>"><?php _e( 'Download', 'woocommerce-exporter' ); ?></a> | 
						<a href="<?php echo get_edit_post_link( $recent_export['post_id'] ); ?>"><?php _e( 'Export Details', 'woocommerce-exporter' ); ?></a>
		<?php } ?>
					</p>
					<p><?php echo ( isset( $recent_export['scheduled_id'] ) ? sprintf( '<a href="' . get_edit_post_link( $recent_export['scheduled_id'] ) . '">%s</a> - ', woo_ce_format_post_title( get_the_title( $recent_export['scheduled_id'] ) ) ) : '' ); ?><span title="<?php echo date( 'd/m/Y h:i:s', $recent_export['date'] ); ?>"><?php echo woo_ce_format_archive_date( $recent_export['post_id'], $recent_export['date'] ); ?></span>, <?php echo ( !empty( $recent_export['error'] ) ? __( 'error', 'woocommerce-exporter' ) . ': <span class="error">' . $recent_export['error'] . '</span>' : woo_ce_format_archive_method( $recent_export['method'] ) . '.' ); ?></p>
				</li>

	<?php } ?>
			</ol>
			<hr />

			<p class="pagination">
				<span class="displaying-num"><?php printf( __( '%d items', 'woocommerce-exporter' ), $size ); ?></span>
				<span class="pagination-links"><?php echo $pagination_links; ?></span>
			</p>

			<p style="text-align:right;">
				<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'nuke_recent_scheduled_exports', '_wpnonce' => wp_create_nonce( 'woo_ce_nuke_recent_scheduled_exports' ) ) ) ); ?>" class="button action confirm-button" data-confirm="<?php _e( 'This will permanently clear the contents of the Recent Scheduled Exports list. Are you sure you want to proceed?', 'woocommerce-exporter' ); ?>"><?php _e( 'Delete All', 'woocommerce-exporter' ); ?></a>
			</p>
<?php } else { ?>
	<?php if( $enable_auto == 1 ) { ?>
			<p><?php _e( 'Ready for your first scheduled export, shouldn\'t be long now.', 'woocommerce-exporter' ); ?>  <strong>:)</strong></p>
	<?php } else { ?>
			<p style="font-size:0.8em;"><div class="dashicons dashicons-no"></div>&nbsp;<strong><?php _e( 'Scheduled exports are disabled', 'woocommerce-exporter' ); ?></strong></p>
	<?php } ?>
<?php } ?>

		</div>
		<!-- .inside -->
		<br class="clear" />
	</div>
	<!-- #recent-scheduled-exports -->

</div>
<!-- #poststuff -->