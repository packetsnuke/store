<div id="content">

	<h2 class="nav-tab-wrapper">
		<a data-tab-id="overview" class="nav-tab<?php woo_cd_admin_active_tab( 'overview' ); ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'overview' ), 'admin.php' ) ); ?>"><?php _e( 'Overview', 'woocommerce-exporter' ); ?></a>
		<a data-tab-id="export" class="nav-tab<?php woo_cd_admin_active_tab( 'export' ); ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'export' ), 'admin.php' ) ); ?>"><?php _e( 'Quick Export', 'woocommerce-exporter' ); ?></a>
<?php if( !woo_ce_get_option( 'hide_scheduled_exports_tab', 0 ) ) { ?>
		<a data-tab-id="scheduled-exports" class="nav-tab<?php woo_cd_admin_active_tab( 'scheduled_export' ); ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'scheduled_export' ), 'admin.php' ) ); ?>"><?php _e( 'Scheduled Exports', 'woocommerce-exporter' ); ?></a>
<?php } ?>
		<a data-tab-id="export-templates" class="nav-tab<?php woo_cd_admin_active_tab( 'export_template' ); ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'export_template' ), 'admin.php' ) ); ?>"><?php _e( 'Export Templates', 'woocommerce-exporter' ); ?></a>
<?php if( !woo_ce_get_option( 'hide_archives_tab', 0 ) ) { ?>
		<a data-tab-id="archive" class="nav-tab<?php woo_cd_admin_active_tab( 'archive' ); ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'archive' ), 'admin.php' ) ); ?>"><?php _e( 'Archives', 'woocommerce-exporter' ); ?></a>
<?php } ?>
		<a data-tab-id="settings" class="nav-tab<?php woo_cd_admin_active_tab( 'settings' ); ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'settings' ), 'admin.php' ) ); ?>"><?php _e( 'Settings', 'woocommerce-exporter' ); ?></a>
		<a data-tab-id="tools" class="nav-tab<?php woo_cd_admin_active_tab( 'tools' ); ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'tools' ), 'admin.php' ) ); ?>"><?php _e( 'Tools', 'woocommerce-exporter' ); ?></a>
<?php if( WOO_CD_DEBUG || apply_filters( 'woo_ce_show_debug_menu', false ) ) { ?>
		<a data-tab-id="debug" class="nav-tab<?php woo_cd_admin_active_tab( 'debug' ); ?>" href="<?php echo esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'debug' ), 'admin.php' ) ); ?>"><?php _e( 'Debug', 'woocommerce-exporter' ); ?></a>
<?php } ?>
	</h2>
	<?php woo_cd_tab_template( $tab ); ?>

</div>
<!-- #content -->