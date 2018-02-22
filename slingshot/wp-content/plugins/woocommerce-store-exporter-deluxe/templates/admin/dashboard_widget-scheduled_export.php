<?php if( $enable_auto == 1 ) { ?>
<p style="font-size:0.8em;">
	<div class="dashicons dashicons-yes"></div>&nbsp;
	<strong><?php _e( 'Scheduled exports is enabled', 'woocommerce-exporter' ); ?></strong>
</p>

	<?php if( !empty( $scheduled_exports ) ) { ?>
		<?php if( $next_export ) { ?>
<p><?php printf( __( 'The next scheduled export - %s - will run in %s.', 'woocommerce-exporter' ), ( current_user_can( $user_capability ) ? sprintf( '<a href="' . get_edit_post_link( $next_export ) . '" title="%s">%s</a>', sprintf( __( 'Edit %s', 'woocommerce-exporter' ), woo_ce_format_post_title( get_the_title( $next_export ) ) ), woo_ce_format_post_title( get_the_title( $next_export ) ) ) : woo_ce_format_post_title( get_the_title( $next_export ) ) ), woo_ce_get_next_scheduled_export( $next_export ) ); ?></p>
		<?php } ?>
	
<p><strong><?php _e( 'Queued scheduled exports', 'woocommerce-exporter' ); ?></strong></p>
<ol>
		<?php foreach( $scheduled_exports as $scheduled_export ) { ?>
<?php
$auto_schedule = get_post_meta( $scheduled_export, '_auto_schedule', true );
if( $auto_schedule == 'one-time' )
	continue;
$next_scheduled_export = woo_ce_get_next_scheduled_export( $scheduled_export );
?>
	<li id="export-<?php echo $scheduled_export; ?>" class="meta">
		<attr title="<?php printf( __( '%s scheduled export', 'woocommerce-exporter' ), ucwords( get_post_meta( $scheduled_export, '_auto_schedule', true ) ) ); ?>">
			<?php if( current_user_can( $user_capability ) ) { ?>
			<a href="<?php echo get_edit_post_link( $scheduled_export ); ?>" title="<?php printf( __( 'Edit %s', 'woocommerce-exporter' ), woo_ce_format_post_title( get_the_title( $scheduled_export ) ) ); ?>"><?php echo woo_ce_format_post_title( get_the_title( $scheduled_export ) ); ?></a>
			<?php } else { ?>
			<?php echo woo_ce_format_post_title( get_the_title( $scheduled_export ) ); ?>
			<?php } ?>
			<?php echo ( !empty( $next_scheduled_export ) ? 'in ' . $next_scheduled_export : '' ); ?>
		</attr>
	</li>
		<?php } ?>
</ol>
	<?php } else { ?>
<p><?php _e( 'No scheduled exports found.', 'woocommerce-exporter' ); ?></p>
	<?php } ?>
<?php } else { ?>
<p style="font-size:0.8em;"><div class="dashicons dashicons-no"></div>&nbsp;<strong><?php _e( 'Scheduled exports are disabled', 'woocommerce-exporter' ); ?></strong></p>
<?php } ?>
<?php if( current_user_can( $user_capability ) ) { ?>
<p style="text-align:right;"><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'scheduled_export' ), 'admin.php' ) ); ?>"><?php _e( 'View scheduled exports', 'woocommerce-exporter' ); ?></a></p>
<?php } ?>