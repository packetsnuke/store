<div class="overview-left">

	<h3><div class="dashicons dashicons-migrate"></div>&nbsp;<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>"><?php _e( 'Quick Export', 'woocommerce-exporter' ); ?></a></h3>
	<p><?php _e( 'Export store details out of WooCommerce into common export files (e.g. CSV, TSV, XLS, XLSX, XML, etc.).', 'woocommerce-exporter' ); ?></p>
	<ul class="ul-disc">
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-product"><?php _e( 'Export Products', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-category"><?php _e( 'Export Categories', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-tag"><?php _e( 'Export Tags', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-brand"><?php _e( 'Export Brands', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-order"><?php _e( 'Export Orders', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-customer"><?php _e( 'Export Customers', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-user"><?php _e( 'Export Users', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-user"><?php _e( 'Export Reviews', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-coupon"><?php _e( 'Export Coupons', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-subscription"><?php _e( 'Export Subscriptions', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-product_vendor"><?php _e( 'Export Product Vendors', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-commission"><?php _e( 'Export Commissions', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-shipping_class"><?php _e( 'Export Shipping Classes', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-ticket"><?php _e( 'Export Tickets', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-booking"><?php _e( 'Export Bookings', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'export' ) ); ?>#export-attribute"><?php _e( 'Export Attributes', 'woocommerce-exporter' ); ?></a>
		</li>
	</ul>

	<h3><div class="dashicons dashicons-calendar"></div>&nbsp;<a href="<?php echo esc_url( add_query_arg( 'tab', 'scheduled_export' ) ); ?>"><?php _e( 'Scheduled Exports', 'woocommerce-exporter' ); ?></a></h3>
	<p><?php _e( 'Automatically generate exports and apply filters to export just what you need.', 'woocommerce-exporter' ); ?></p>

	<h3><div class="dashicons dashicons-list-view"></div>&nbsp;<a href="<?php echo esc_url( add_query_arg( 'tab', 'export_template' ) ); ?>"><?php _e( 'Export Templates', 'woocommerce-exporter' ); ?></a></h3>
	<p><?php _e( 'Create lists of pre-defined fields which can be applied to exports.', 'woocommerce-exporter' ); ?></p>

	<h3><div class="dashicons dashicons-list-view"></div>&nbsp;<a href="<?php echo esc_url( add_query_arg( 'tab', 'archive' ) ); ?>"><?php _e( 'Archives', 'woocommerce-exporter' ); ?></a></h3>
	<p><?php _e( 'Download copies of prior store exports.', 'woocommerce-exporter' ); ?></p>

	<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;<a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>"><?php _e( 'Settings', 'woocommerce-exporter' ); ?></a></h3>
	<p><?php _e( 'Manage export options from a single detailed screen.', 'woocommerce-exporter' ); ?></p>
	<ul class="ul-disc">
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>#general-settings"><?php _e( 'General Settings', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>#csv-settings"><?php _e( 'CSV Settings', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>#xml-settings"><?php _e( 'XML Settings', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>#scheduled-exports"><?php _e( 'Scheduled Exports', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>#cron-exports"><?php _e( 'CRON Exports', 'woocommerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>#orders-screen"><?php _e( 'Orders Screen', 'woocommerce-exporter' ); ?></a>
		</li>
	</ul>

	<h3><div class="dashicons dashicons-hammer"></div>&nbsp;<a href="<?php echo esc_url( add_query_arg( 'tab', 'tools' ) ); ?>"><?php _e( 'Tools', 'woocommerce-exporter' ); ?></a></h3>
	<p><?php _e( 'Export tools for WooCommerce.', 'woocommerce-exporter' ); ?></p>

	<hr />
	<form id="skip_overview_form" method="post">
		<label><input type="checkbox" id="skip_overview" name="skip_overview"<?php checked( $skip_overview ); ?> /> <?php _e( 'Jump to Export screen in the future', 'woocommerce-exporter' ); ?></label>
		<input type="hidden" name="action" value="skip_overview" />
		<?php wp_nonce_field( 'skip_overview', 'woo_ce_skip_overview' ); ?>
	</form>

</div>
<!-- .overview-left -->