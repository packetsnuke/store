<?php
function woo_ce_admin_scheduled_export_widget() {

	if( $enable_auto = woo_ce_get_option( 'enable_auto', 0 ) ) {

		$next_export = '';
		$next_time = '';

		// Get widget options
		if( !$widget_options = woo_ce_get_option( 'scheduled_export_widget_options', array() ) ) {
			$widget_options = array(
				'number' => 5
			);
		}

		// Loop through each scheduled export, only show Published
		$args = array(
			'post_status' => 'publish'
		);
		if( $scheduled_exports = woo_ce_get_scheduled_exports( $args ) ) {
			$export_times = array();
			foreach( $scheduled_exports as $key => $scheduled_export ) {

				// Only display enabled scheduled exports
				if( get_post_status( $scheduled_export ) <> 'publish' ) {
					unset( $scheduled_exports[$key] );
					continue;
				}

				// Figure out which scheduled export will run next
				if( $next_time == '' ) {
					$next_export = $scheduled_export;
					$next_time = woo_ce_get_next_scheduled_export( $scheduled_export, 'timestamp' );
				} else {
					if( $next_time > woo_ce_get_next_scheduled_export( $scheduled_export, 'timestamp' ) ) {
						$next_export = $scheduled_export;
						$next_time = woo_ce_get_next_scheduled_export( $scheduled_export, 'timestamp' );
					}
				}
				$export_times[$scheduled_export] = $next_time;

			}

			// Sort the scheduled exports by the order of next run
			if( !empty(  $export_times ) ) {
				arsort( $export_times );
				$scheduled_exports = array_keys( $export_times );
			}

			// Check if we need to limit the number of scheduled exports
			$size = count( $scheduled_exports );
			if( $size > $widget_options['number'] ) {
				$i = $size;
				// Loop through the recent exports till we get it down to our limit
				for( $i; $i > $widget_options['number']; $i-- )
					array_pop( $scheduled_exports );
			}
			unset( $next_time );

		}

	}

	// Check the User has the view_woocommerce_reports capability
	$user_capability = apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' );

	$template = 'dashboard_widget-scheduled_export.php';
	if( file_exists( WOO_CD_PATH . 'templates/admin/' . $template ) ) {
		include_once( WOO_CD_PATH . 'templates/admin/' . $template );
	} else {
		$message = sprintf( __( 'We couldn\'t load the widget template file <code>%s</code> within <code>%s</code>, this file should be present.', 'woocommerce-exporter' ), 'dashboard_widget-scheduled_export.php', WOO_CD_PATH . 'templates/admin/...' );

		ob_start(); ?>
<p><strong><?php echo $message; ?></strong></p>
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

function woo_ce_admin_recent_scheduled_export_widget() {

	$enable_auto = woo_ce_get_option( 'enable_auto', 0 );
	$recent_exports = woo_ce_get_option( 'recent_scheduled_exports', array() );
	if( empty( $recent_exports ) )
		$recent_exports = array();
	$size = count( $recent_exports );
	$recent_exports = array_reverse( $recent_exports );

	// Get widget options
	if( !$widget_options = woo_ce_get_option( 'recent_scheduled_export_widget_options', array() ) ) {
		$widget_options = array(
			'number' => 5
		);
	}

	// Check if we need to limit the number of recent exports
	if( $size > $widget_options['number'] ) {
		$i = $size;
		// Loop through the recent exports till we get it down to our limit
		for( $i; $i >= $widget_options['number']; $i-- ) {
			unset( $recent_exports[$i] );
		}
	}

	// Check the User has the view_woocommerce_reports capability
	$user_capability = apply_filters( 'woo_ce_admin_user_capability', 'view_woocommerce_reports' );

	$template = 'dashboard_widget-recent_scheduled_export.php';
	if( file_exists( WOO_CD_PATH . 'templates/admin/' . $template ) ) {
		include_once( WOO_CD_PATH . 'templates/admin/' . $template );
	} else {
		$message = sprintf( __( 'We couldn\'t load the widget template file <code>%s</code> within <code>%s</code>, this file should be present.', 'woocommerce-exporter' ), 'dashboard_widget-recent_scheduled_export.php', WOO_CD_PATH . 'templates/admin/...' );

		ob_start(); ?>
<p><strong><?php echo $message; ?></strong></p>
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

function woo_ce_admin_scheduled_export_widget_configure() {

	// Get widget options
	if( !$widget_options = woo_ce_get_option( 'scheduled_export_widget_options', array() ) ) {
		$widget_options = array(
			'number' => 5
		);
	}

	// Update widget options
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['woo_ce_scheduled_export_widget_post'] ) ) {
		$widget_options = array_map( 'sanitize_text_field', $_POST['woo_ce_scheduled_export'] );
		if( empty( $widget_options['number'] ) )
			$widget_options['number'] = 5;
		update_option( 'woo_ce_scheduled_export_widget_options', $widget_options );
	} ?>
<div>
	<label for="woo_ce_scheduled_export-number"><?php _e( 'Number of scheduled exports', 'woocommerce-exporter' ); ?>:</label><br />
	<input type="text" id="woo_ce_scheduled_export-number" name="woo_ce_scheduled_export[number]" value="<?php echo $widget_options['number']; ?>" />
	<p class="description"><?php _e( 'Control the number of scheduled exports that are shown.', 'woocommerce-exporter' ); ?></p>
</div>
<input name="woo_ce_scheduled_export_widget_post" type="hidden" value="1" />
<?php

}

function woo_ce_admin_recent_scheduled_export_widget_configure() {

	// Get widget options
	if( !$widget_options = woo_ce_get_option( 'recent_scheduled_export_widget_options', array() ) ) {
		$widget_options = array(
			'number' => 5
		);
	}

	// Update widget options
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST['woo_ce_recent_scheduled_export_widget_post'] ) ) {
		$widget_options = array_map( 'sanitize_text_field', $_POST['woo_ce_recent_scheduled_export'] );
		if( empty( $widget_options['number'] ) )
			$widget_options['number'] = 5;
		update_option( 'woo_ce_recent_scheduled_export_widget_options', $widget_options );
	} ?>
<div>
	<label for="woo_ce_recent_scheduled_export-number"><?php _e( 'Number of scheduled exports', 'woocommerce-exporter' ); ?>:</label><br />
	<input type="text" id="woo_ce_recent_scheduled_export-number" name="woo_ce_recent_scheduled_export[number]" value="<?php echo $widget_options['number']; ?>" />
	<p class="description"><?php _e( 'Control the number of recent scheduled exports that are shown.', 'woocommerce-exporter' ); ?></p>
</div>
<input name="woo_ce_recent_scheduled_export_widget_post" type="hidden" value="1" />
<?php

}
?>