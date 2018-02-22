<?php
function woo_ce_scheduled_export_banner( $post ) {

	// Check the Post object exists
	if( isset( $post->post_type ) == false )
		return;

	// Limit to the scheduled_export Post Type
	$post_type = 'scheduled_export';
	if( $post->post_type <> $post_type )
		return;

	if( apply_filters( 'woo_ce_scheduled_export_banner_save_prompt', true ) )
		echo '<a href="' . esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'scheduled_export' ), 'admin.php' ) ) . '" class="button confirm-button" data-confirm="' . __( 'The changes you made will be lost if you navigate away from this page before saving.', 'woocommerce-exporter' ) . '">' . __( 'Return to Scheduled Exports', 'woocommerce-exporter' ) . '</a>';
	else
		echo '<a href="' . esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'scheduled_export' ), 'admin.php' ) ) . '" class="button">' . __( 'Return to Scheduled Exports', 'woocommerce-exporter' ) . '</a>';

}

function woo_ce_scheduled_export_filters_meta_box() {

	global $post;

	$post_ID = ( $post ? $post->ID : 0 );

	// Check if the Enabled scheduled export option is disabled
	if( !woo_ce_get_option( 'enable_auto', 0 ) ) {
		$override_url = esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'scheduled_export', 'action' => 'enable_scheduled_exports', '_wpnonce' => wp_create_nonce( 'woo_ce_enable_scheduled_exports' ) ), 'admin.php' ) );
		$message = sprintf( __( 'Scheduled exports are turned off from the <em>Enable scheduled exports</em> option on the Settings tab, to enable scheduled exports globally <a href="%s">click here</a>.', 'woocommerce-exporter' ), $override_url );
		woo_cd_admin_notice_html( $message, 'error' );
	}

	woo_ce_load_export_types();

	// General
	add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_export_type' );
	add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_export_format' );
	add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_export_method' );
	add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_export_fields' );
	add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_header_formatting' );
	add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_product_image_formatting' );
	add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_order' );
	add_action( 'woo_ce_before_scheduled_export_general_options', 'woo_ce_scheduled_export_general_volume_limit_offset' );

	// Filters
	add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_product' );
	add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_category' );
	add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_tag' );
	add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_brand' );
	add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_order' );
	add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_user' );
	add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_review' );
	add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_coupon' );
	add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_subscription' );
	add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_commission' );
	add_action( 'woo_ce_before_scheduled_export_type_options', 'woo_ce_scheduled_export_filters_shipping_class' );

	// Method
	add_action( 'woo_ce_before_scheduled_export_method_options', 'woo_ce_scheduled_export_method_archive' );
	add_action( 'woo_ce_before_scheduled_export_method_options', 'woo_ce_scheduled_export_method_save' );
	add_action( 'woo_ce_before_scheduled_export_method_options', 'woo_ce_scheduled_export_method_email' );
	add_action( 'woo_ce_before_scheduled_export_method_options', 'woo_ce_scheduled_export_method_post' );
	add_action( 'woo_ce_before_scheduled_export_method_options', 'woo_ce_scheduled_export_method_ftp' );
	// add_action( 'woo_ce_before_scheduled_export_method_options', 'woo_ce_scheduled_export_method_google_sheets' );

	// Scheduling
	add_action( 'woo_ce_before_scheduled_export_frequency_options', 'woo_ce_scheduled_export_frequency_schedule' );
	add_action( 'woo_ce_before_scheduled_export_frequency_options', 'woo_ce_scheduled_export_frequency_commence' );
	add_action( 'woo_ce_before_scheduled_export_frequency_options', 'woo_ce_scheduled_export_frequency_days' );

	// Allow Plugin/Theme authors to add custom fields to the Export Filters meta box
	do_action( 'woo_ce_extend_scheduled_export_options', $post_ID );

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

?>
<div id="scheduled_export_options" class="panel-wrap scheduled_export_data">
	<div class="wc-tabs-back"></div>
	<ul class="coupon_data_tabs wc-tabs" style="display:none;">
<?php
	$coupon_data_tabs = apply_filters( 'woo_ce_scheduled_export_data_tabs', array(
		'general' => array(
			'label'  => __( 'General', 'woocommerce' ),
			'target' => 'general_coupon_data',
			'class'  => 'general_coupon_data',
		),
		'filters' => array(
			'label'  => __( 'Filters', 'woocommerce' ),
			'target' => 'usage_restriction_coupon_data',
			'class'  => '',
		),
		'method' => array(
			'label'  => __( 'Method', 'woocommerce' ),
			'target' => 'method_coupon_data',
			'class'  => '',
		),
		'scheduling' => array(
			'label'  => __( 'Scheduling', 'woocommerce' ),
			'target' => 'scheduling_coupon_data',
			'class'  => '',
		)
	) );

	foreach ( $coupon_data_tabs as $key => $tab ) { ?>
		<li class="<?php echo $key; ?>_options <?php echo $key; ?>_tab <?php echo implode( ' ' , (array) $tab['class'] ); ?>">
			<a href="#<?php echo $tab['target']; ?>"><?php echo esc_html( $tab['label'] ); ?></a>
		</li><?php
	} ?>
	</ul>
	<?php do_action( 'woo_ce_before_scheduled_export_options', $post_ID ); ?>
	<div id="general_coupon_data" class="panel woocommerce_options_panel export_general_options">
		<?php do_action( 'woo_ce_before_scheduled_export_general_options', $post_ID ); ?>
		<?php do_action( 'woo_ce_after_scheduled_export_general_options', $post_ID ); ?>
	</div>
	<!-- #general_coupon_data -->

	<div id="usage_restriction_coupon_data" class="panel woocommerce_options_panel export_type_options">
		<?php do_action( 'woo_ce_before_scheduled_export_type_options', $post_ID ); ?>
		<div class="export-options customer-options product_vendor-options ticket-options">
			<p><?php _e( 'No filter options are available for this export type.', 'woocommerce-exporter' ); ?></p>
		</div>
		<?php do_action( 'woo_ce_after_scheduled_export_type_options', $post_ID ); ?>
	</div>
	<!-- #usage_restriction_coupon_data -->

	<div id="method_coupon_data" class="panel woocommerce_options_panel export_method_options">
		<?php do_action( 'woo_ce_before_scheduled_export_method_options', $post_ID ); ?>
		<div class="export-options">
			<p><?php _e( 'No export method options are available for this export method.', 'woocommerce-exporter' ); ?></p>
		</div>
		<?php do_action( 'woo_ce_after_scheduled_export_method_options', $post_ID ); ?>
	</div>
	<!-- #method_coupon_data -->

	<div id="scheduling_coupon_data" class="panel woocommerce_options_panel export_frequency_options">
		<?php do_action( 'woo_ce_before_scheduled_export_frequency_options', $post_ID ); ?>
		<?php do_action( 'woo_ce_after_scheduled_export_frequency_options', $post_ID ); ?>
	</div>
	<!-- #scheduling_coupon_data -->

	<?php do_action( 'woo_ce_after_scheduled_export_options', $post_ID ); ?>
	<div class="clear"></div>
</div>
<!-- #scheduled_export_options -->
<?php
	wp_nonce_field( 'scheduled_export', 'woo_ce_export' );

}

function woo_ce_extend_scheduled_export_options( $post_ID = 0 ) {

	// Product
	if( function_exists( 'woo_ce_scheduled_export_products_filter_orderby' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_orderby' );
	if( function_exists( 'woo_ce_scheduled_export_products_filter_by_product_category' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_by_product_category' );
	if( function_exists( 'woo_ce_scheduled_export_products_filter_by_product_tag' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_by_product_tag' );
	if( function_exists( 'woo_ce_scheduled_export_products_filter_by_product_status' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_by_product_status' );
	if( function_exists( 'woo_ce_scheduled_export_products_filter_by_product_type' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_by_product_type' );
	if( function_exists( 'woo_ce_scheduled_export_products_filter_by_product' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_by_product' );
	if( function_exists( 'woo_ce_scheduled_export_product_filter_by_user_role' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_product_filter_by_user_role' );
	if( function_exists( 'woo_ce_scheduled_export_products_filter_by_shipping_class' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_by_shipping_class' );
	if( function_exists( 'woo_ce_scheduled_export_products_filter_by_date_modified' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_by_date_modified' );
	if( function_exists( 'woo_ce_scheduled_export_products_filter_by_stock_status' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_by_stock_status' );
	if( function_exists( 'woo_ce_scheduled_export_products_filter_by_featured' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_by_featured' );
	if( function_exists( 'woo_ce_scheduled_export_products_filter_by_product_brand' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_by_product_brand' );
	if( function_exists( 'woo_ce_scheduled_export_products_filter_by_language' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_by_language' );
	if( function_exists( 'woo_ce_scheduled_export_products_filter_by_product_vendor' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_by_product_vendor' );
	if( function_exists( 'woo_ce_scheduled_export_products_filter_by_product_meta' ) )
		add_action( 'woo_ce_scheduled_export_filters_product', 'woo_ce_scheduled_export_products_filter_by_product_meta' );

	// Category
	if( function_exists( 'woo_ce_scheduled_export_category_filter_orderby' ) )
		add_action( 'woo_ce_scheduled_export_filters_category', 'woo_ce_scheduled_export_category_filter_orderby' );

	// Tag
	if( function_exists( 'woo_ce_scheduled_export_tag_filter_orderby' ) )
		add_action( 'woo_ce_scheduled_export_filters_tag', 'woo_ce_scheduled_export_tag_filter_orderby' );

	// Brand
	if( function_exists( 'woo_ce_scheduled_export_brand_filter_orderby' ) )
		add_action( 'woo_ce_scheduled_export_filters_brand', 'woo_ce_scheduled_export_brand_filter_orderby' );

	// Order
	if( function_exists( 'woo_ce_scheduled_export_order_filter_orderby' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_orderby' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_order_status' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_order_status' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_billing_country' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_billing_country' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_shipping_country' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_shipping_country' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_order_date' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_order_date' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_product' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_product' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_product_category' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_product_category' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_product_tag' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_product_tag' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_user_role' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_user_role' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_customer' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_customer' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_coupon' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_coupon' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_payment_gateway' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_payment_gateway' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_shipping_method' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_shipping_method' );
	if( function_exists( 'woo_ce_scheduled_export_order_items_formatting' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_items_formatting' );
	if( function_exists( 'woo_ce_scheduled_export_order_max_order_items' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_max_order_items' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_digital_products' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_digital_products' );
	if( function_exists( 'woo_ce_scheduled_export_order_export_order_notes' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_export_order_notes' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_order_type' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_order_type' );
	if( function_exists( 'woo_ce_scheduled_export_order_order_item_types' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_order_item_types' );
	if( function_exists( 'woo_ce_scheduled_export_order_filter_by_order_meta' ) )
		add_action( 'woo_ce_scheduled_export_filters_order', 'woo_ce_scheduled_export_order_filter_by_order_meta' );

	// User
	if( function_exists( 'woo_ce_scheduled_export_user_filter_orderby' ) )
		add_action( 'woo_ce_scheduled_export_filters_user', 'woo_ce_scheduled_export_user_filter_orderby' );

	// Review
	if( function_exists( 'woo_ce_scheduled_export_review_filter_orderby' ) )
		add_action( 'woo_ce_scheduled_export_filters_review', 'woo_ce_scheduled_export_review_filter_orderby' );
	if( function_exists( 'woo_ce_scheduled_export_review_filter_by_review_date' ) )
		add_action( 'woo_ce_scheduled_export_filters_review', 'woo_ce_scheduled_export_review_filter_by_review_date' );

	// Coupon
	if( function_exists( 'woo_ce_scheduled_export_coupon_filter_orderby' ) )
		add_action( 'woo_ce_scheduled_export_filters_coupon', 'woo_ce_scheduled_export_coupon_filter_orderby' );
	if( function_exists( 'woo_ce_scheduled_export_coupon_filter_by_discount_type' ) )
		add_action( 'woo_ce_scheduled_export_filters_coupon', 'woo_ce_scheduled_export_coupon_filter_by_discount_type' );

	// Subscription
	if( function_exists( 'woo_ce_scheduled_export_subscription_filter_orderby' ) )
		add_action( 'woo_ce_scheduled_export_filters_subscription', 'woo_ce_scheduled_export_subscription_filter_orderby' );
	if( function_exists( 'woo_ce_scheduled_export_subscription_filter_by_subscription_product' ) )
		add_action( 'woo_ce_scheduled_export_filters_subscription', 'woo_ce_scheduled_export_subscription_filter_by_subscription_product' );

	// Commission
	if( function_exists( 'woo_ce_scheduled_export_commission_filter_orderby' ) )
		add_action( 'woo_ce_scheduled_export_filters_commission', 'woo_ce_scheduled_export_commission_filter_orderby' );

	// Shipping Class
	if( function_exists( 'woo_ce_scheduled_export_shipping_class_filter_orderby' ) )
		add_action( 'woo_ce_scheduled_export_filters_shipping_class', 'woo_ce_scheduled_export_shipping_class_filter_orderby' );

}
add_action( 'woo_ce_extend_scheduled_export_options', 'woo_ce_extend_scheduled_export_options' );

function woo_ce_scheduled_export_general_export_type( $post_ID = 0 ) {

	$export_type = get_post_meta( $post_ID, '_export_type', true );
	$export_types = woo_ce_get_export_types();

	ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field ">
		<label for="export_type"><?php _e( 'Export type', 'woocommerce-exporter' ); ?> </label>
<?php if( !empty( $export_types ) ) { ?>
		<select id="export_type" name="export_type" class="select short">
	<?php foreach( $export_types as $key => $type ) { ?>
			<option value="<?php echo $key; ?>"<?php selected( $export_type, $key ); ?>><?php echo $type; ?></option>
	<?php } ?>
		</select>
		<img class="help_tip" data-tip="<?php _e( 'Select the export type you want to export.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
		<?php _e( 'No export types were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
	</p>
</div>
<!-- .options_group -->

<?php
	ob_end_flush();

}

function woo_ce_scheduled_export_general_export_format( $post_ID = 0 ) {

	$export_formats = woo_ce_get_export_formats();
	$type = get_post_meta( $post_ID, '_export_format', true );

	ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field ">
		<label for="export_format"><?php _e( 'Export format', 'woocommerce-exporter' ); ?> </label>
<?php if( !empty( $export_formats ) ) { ?>
		<select id="export_format" name="export_format" class="select short">
	<?php foreach( $export_formats as $key => $export_format ) { ?>
			<option value="<?php echo $key; ?>"<?php selected( $type, $key ); ?>><?php echo $export_format['title']; ?><?php if( !empty( $export_format['description'] ) ) { ?> - <?php echo $export_format['description']; ?><?php } ?></option>
	<?php } ?>
		</select>
<?php } else { ?>
		<?php _e( 'No export formats were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
		<img class="help_tip" data-tip="<?php _e( 'Adjust the export format to generate different export file formats. Default is CSV.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	</p>
</div>
<!-- .options_group -->

<?php
		ob_end_flush();

}

function woo_ce_scheduled_export_general_export_method( $post_ID = 0 ) {

	$export_method = get_post_meta( $post_ID, '_export_method', true );

	ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field ">
		<label for="export_method"><?php _e( 'Export method', 'woocommerce-exporter' ); ?> </label>
		<select id="export_method" name="export_method" class="select short">
			<option value="archive"<?php selected( $export_method, 'archive' ); ?>><?php echo woo_ce_format_export_method( 'archive' ); ?></option>
			<option value="save"<?php selected( $export_method, 'save' ); ?>><?php echo woo_ce_format_export_method( 'save' ); ?></option>
			<option value="email"<?php selected( $export_method, 'email' ); ?>><?php echo woo_ce_format_export_method( 'email' ); ?></option>
			<option value="post"<?php selected( $export_method, 'post' ); ?>><?php echo woo_ce_format_export_method( 'post' ); ?></option>
			<option value="ftp"<?php selected( $export_method, 'ftp' ); ?>><?php echo woo_ce_format_export_method( 'ftp' 	); ?></option>
			<!-- <option value="google_sheets"<?php selected( $export_method, 'google_sheets' ); ?>><?php echo woo_ce_format_export_method( 'google_sheets' 	); ?></option> -->
		</select>
		<img class="help_tip" data-tip="<?php _e( 'Choose what Store Exporter Deluxe does with the generated export. Default is to archive the export to the WordPress Media for archival purposes.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	</p>
</div>
<!-- .options_group -->

<?php
		ob_end_flush();

}

function woo_ce_scheduled_export_general_export_fields( $post_ID = 0 ) {

	$export_fields = get_post_meta( $post_ID, '_export_fields', true );
	$args = array(
		'post_status' => 'publish'
	);
	$export_templates = woo_ce_get_export_templates( $args );
	$export_template = get_post_meta( $post_ID, '_export_template', true );

	ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field">
		<label for="export_fields"><?php _e( 'Export fields', 'woocommerce-exporter' ); ?></label>
		<input type="radio" name="export_fields" value="all"<?php checked( in_array( $export_fields, array( false, 'all' ) ), true ); ?> />&nbsp;<?php _e( 'Include all Export Fields for the requested Export Type', 'woocommerce-exporter' ); ?><br />
		<input type="radio" name="export_fields" value="template"<?php checked( $export_fields, 'template' ); ?><?php disabled( empty( $export_templates ), true ); ?> />&nbsp;<?php _e( 'Use the saved Export Fields preference from the following Export Template for the requested Export Type', 'woocommerce-exporter' ); ?><br />
		<select id="export_template" name="export_template"<?php disabled( empty( $export_templates ), true ); ?> class="select short">
<?php if( !empty( $export_templates ) ) { ?>
	<?php foreach( $export_templates as $template ) { ?>
			<option value="<?php echo $template; ?>"<?php selected( $export_template, $template ); ?>><?php echo woo_ce_format_post_title( get_the_title( $template ) ); ?></option>
	<?php } ?>
<?php } else { ?>
			<option><?php _e( 'Choose a Export Template...', 'woocommerce-exporter' ); ?></option>
<?php } ?>
		</select>
		<br class="clear" />
		<input type="radio" name="export_fields" value="saved"<?php checked( $export_fields, 'saved' ); ?> />&nbsp;<?php _e( 'Use the saved Export Fields preference set on the Quick Export screen for the requested Export Type', 'woocommerce-exporter' ); ?>
	</p>
	<p class="description"><?php _e( 'Control whether all known export fields are included, field preferences from a specific Export Template or only checked fields from the Export Fields section on the Quick Export screen. Default is to include all export fields.', 'woocommerce-exporter' ); ?></p>
</div>
<!-- .options_group -->

<?php
		ob_end_flush();

}

function woo_ce_scheduled_export_general_header_formatting( $post_ID = 0 ) {

	$header_formatting = get_post_meta( $post_ID, '_header_formatting', true );

	ob_start(); ?>
<div class="options_group">
	<p class="form-field discount_type_field">
		<label for="header_formatting"><?php _e( 'Header formatting', 'woocommerce-exporter' ); ?></label>
		<input type="radio" name="header_formatting" value="1"<?php checked( in_array( $header_formatting, array( false, '1' ) ), true ); ?> />&nbsp;<?php _e( 'Include export field column headers', 'woocommerce-exporter' ); ?><br />
		<input type="radio" name="header_formatting" value="0"<?php checked( $header_formatting, '0' ); ?> />&nbsp;<?php _e( 'Do not include export field column headers', 'woocommerce-exporter' ); ?><br />
	</p>
	<p class="description"><?php _e( 'Choose the header format that suits your spreadsheet software (e.g. Excel, OpenOffice, etc.). This rule applies to CSV, TSV, XLS and XLSX export types.', 'woocommerce-exporter' ); ?></p>
</div>

<?php
		ob_end_flush();

}

function woo_ce_scheduled_export_general_order( $post_ID = 0 ) {

	$order = get_post_meta( $post_ID, '_order', true );
	// Default to Ascending
	if( $order == false )
		$order = 'ASC';

	ob_start(); ?>
<div class="options_group">

	<p class="form-field discount_type_field">
		<label for="order"><?php _e( 'Order', 'woocommerce-exporter' ); ?></label>
		<select id="order" name="order">
			<option value="ASC"<?php selected( 'ASC', $order ); ?>><?php _e( 'Ascending', 'woocommerce-exporter' ); ?></option>
			<option value="DESC"<?php selected( 'DESC', $order ); ?>><?php _e( 'Descending', 'woocommerce-exporter' ); ?></option>
		</select>
		<img class="help_tip" data-tip="<?php _e( 'Select the sorting of records within the exported file.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	</p>

</div>
<!-- .options_group -->

<?php
		ob_end_flush();

}

function woo_ce_scheduled_export_general_volume_limit_offset( $post_ID = 0 ) {

	$delimiter = get_post_meta( $post_ID, '_delimiter', true );
	$limit_volume = get_post_meta( $post_ID, '_limit_volume', true );
	$offset = get_post_meta( $post_ID, '_offset', true );

	ob_start(); ?>
<div class="options_group">

	<p class="form-field discount_type_field">
		<label for="delimiter"><?php _e( 'Delimiter', 'woocommerce-exporter' ); ?></label>
		<input type="text" size="3" id="delimiter" name="delimiter" value="<?php echo esc_attr( $delimiter ); ?>" maxlength="5" class="text sized" />
	</p>
	<p class="form-field discount_type_field">
		<label for="limit_volume"><?php _e( 'Limit volume', 'woocommerce-exporter' ); ?></label>
		<input type="text" size="3" id="limit_volume" name="limit_volume" value="<?php echo esc_attr( $limit_volume ); ?>" size="5" class="text sized" title="<?php _e( 'Limit volume', 'woocommerce-exporter' ); ?>" />
		<img class="help_tip" data-tip="<?php _e( 'Limit the number of records to be exported. By default this is not used and is left empty.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	</p>
	<p class="form-field discount_type_field">
		<label for="offset"><?php _e( 'Volume offset', 'woocommerce-exporter' ); ?></label>
		<input type="text" size="3" id="offset" name="offset" value="<?php echo esc_attr( $offset ); ?>" size="5" class="text sized" title="<?php _e( 'Volume offset', 'woocommerce-exporter' ); ?>" />
		<img class="help_tip" data-tip="<?php _e( 'Set the number of records to be skipped in this export. By default this is not used and is left empty.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	</p>
	<p class="description"><?php _e( 'Having difficulty downloading your exports in one go? Use our batch export function - Limit Volume and Volume Offset - to create smaller exports.', 'woocommerce-exporter' ); ?></p>

</div>
<!-- .options_group -->

<?php
		ob_end_flush();

}

function woo_ce_scheduled_export_general_product_image_formatting( $post_ID = 0 ) {

	$product_image_formatting = get_post_meta( $post_ID, '_product_image_formatting', true );
	$gallery_formatting = get_post_meta( $post_ID, '_gallery_formatting', true );

	if( $product_image_formatting == false )
		$product_image_formatting = 0;
	if( $gallery_formatting == false )
		$gallery_formatting = 0;

	ob_start(); ?>
<div class="options_group">

	<p class="form-field discount_type_field">
		<label for="export_fields"><?php _e( 'Product image formatting', 'woocommerce-exporter' ); ?></label>
		<input type="radio" name="product_image_formatting" value="0"<?php checked( $product_image_formatting, 0 ); ?> />&nbsp;<?php _e( 'Export Product Image as Attachment ID', 'woocommerce-exporter' ); ?><br />
		<input type="radio" name="product_image_formatting" value="1"<?php checked( $product_image_formatting, 1 ); ?> />&nbsp;<?php _e( 'Export Product Image as Image URL', 'woocommerce-exporter' ); ?><br />
		<input type="radio" name="product_image_formatting" value="2"<?php checked( $product_image_formatting, 2 ); ?> />&nbsp;<?php _e( 'Export Product Image as Image filepath', 'woocommerce-exporter' ); ?>
	</p>
	<p class="description"><?php _e( 'Choose the featured image formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>

	<p class="form-field discount_type_field">
		<label for="export_fields"><?php _e( 'Product gallery formatting', 'woocommerce-exporter' ); ?></label>
		<input type="radio" name="gallery_formatting" value="0"<?php checked( $gallery_formatting, 0 ); ?> />&nbsp;<?php _e( 'Export Product Gallery as Attachment ID', 'woocommerce-exporter' ); ?><br />
		<input type="radio" name="gallery_formatting" value="1"<?php checked( $gallery_formatting, 1 ); ?> />&nbsp;<?php _e( 'Export Product Gallery as Image URL', 'woocommerce-exporter' ); ?><br />
		<input type="radio" name="gallery_formatting" value="2"<?php checked( $gallery_formatting, 2 ); ?> />&nbsp;<?php _e( 'Export Product Gallery as Image filepath', 'woocommerce-exporter' ); ?>
	</p>
	<p class="description"><?php _e( 'Choose the product gallery formatting that is accepted by your WooCommerce import Plugin (e.g. Product Importer Deluxe, Product Import Suite, etc.).', 'woocommerce-exporter' ); ?></p>

</div>
<!-- .options_group -->
<?php
		ob_end_flush();

}

function woo_ce_scheduled_export_method_archive( $post_ID = 0 ) {

	$parent_post_id = get_post_meta( $post_ID, '_method_archive_parent_post', true );

	ob_start(); ?>
<div class="export-options archive-options">

	<div class="options_group">
		<p class="form-field discount_type_field">
			<label for="archive_method_parent_post"><?php _e( 'Parent post', 'woocommerce-exporter' ); ?></label> <input type="text" id="archive_method_parent_post" name="archive_method_parent_post" size="5" class="short code" value="<?php echo $parent_post_id; ?>" style="float:none;" />
			<img class="help_tip" data-tip="<?php _e( 'The Parent Post ID that Scheduled Export files should be associated with.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<!-- .options_group -->

</div>
<!-- .archive-options -->

<?php
	ob_end_flush();

}

function woo_ce_scheduled_export_method_save( $post_ID = 0 ) {

	$save_path = get_post_meta( $post_ID, '_method_save_path', true );
	$save_filename = get_post_meta( $post_ID, '_method_save_filename', true );

	ob_start(); ?>
<div class="export-options save-options">

	<div class="options_group">
		<p class="form-field discount_type_field">
			<label for="save_method_file_path"><?php _e( 'File path', 'woocommerce-exporter' ); ?></label> <code><?php echo get_home_path(); ?></code> <input type="text" id="save_method_file_path" name="save_method_path" size="25" class="short code" value="<?php echo sanitize_text_field( $save_path ); ?>" style="float:none;" />
			<img class="help_tip" data-tip="<?php _e( 'Do not provide the filename within File path as it will be generated for you or rely on the fixed filename entered below.<br /><br />For file path example: <code>wp-content/uploads/exports/</code>', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<!-- .options_group -->

	<div class="options_group">
		<p class="form-field discount_type_field">
			<label for="save_method_filename"><?php _e( 'Fixed filename', 'woocommerce-exporter' ); ?></label> <input type="text" id="save_method_filename" name="save_method_filename" size="25" class="short code" value="<?php echo esc_attr( $save_filename ); ?>" />
			<img class="help_tip" data-tip="<?php _e( 'The export filename can be set within the Fixed filename field otherwise it defaults to the Export filename provided within General Settings above.<br /><br />Tags can be used: ', 'woocommerce-exporter' ); ?> <code>%dataset%</code>, <code>%date%</code>, <code>%time%</code>, <code>%random%</code>, <code>%store_name%</code>." src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<!-- .options_group -->

</div>
<!-- .save-options -->

<?php
	ob_end_flush();

}

function woo_ce_scheduled_export_method_email( $post_ID = 0 ) {

	ob_start(); ?>
<div class="export-options email-options">

<?php
echo '<div class="options_group">';
woocommerce_wp_text_input(
	array(
		'id' => '_method_email_to', 
		'label' => __( 'E-mail recipient', 'woocommerce' ), 
		'desc_tip' => 'true', 
		'description' => __( 'Set the recipient of scheduled export e-mails, multiple recipients can be added using the <code><attr title="comma">,</attr></code> separator.<br /><br />Default is the Blog Administrator e-mail address set on the WordPress &raquo; Settings screen.', 'woocommerce-exporter' ), 
		'placeholder' => 'big.bird@sesamestreet.org,oscar@sesamestreet.org' 
	) 
);
echo '</div><div class="options_group">';
woocommerce_wp_text_input( 
	array( 
		'id' => '_method_email_subject', 
		'label' => __( 'E-mail subject', 'woocommerce' ), 
		'desc_tip' => 'true', 
		'description' => __( 'Set the subject of scheduled export e-mails.<br /><br />Tags can be used: <code>%store_name%</code>, <code>%export_type%</code>, <code>%export_filename%</code>.', 'woocommerce-exporter' ), 
		'placeholder' => __( 'Daily Product stock levels', 'woocommerce-exporter' ) 
	) 
);
echo '</div><div class="options_group">';
woocommerce_wp_textarea_input( 
	array( 
		'id' => '_method_email_contents', 
		'label' => __( 'E-mail contents', 'woocommerce-exporter' ), 
		'desc_tip' => 'true', 
		'description' => __( 'Set the e-mail contents of scheduled export e-mails.<br /><br />Tags can be used: <code>%store_name%</code>, <code>%export_type%</code>, <code>%export_filename%</code>.', 'woocommerce-exporter' ), 
		'placeholder' => __( 'Please find attached your export ready to review.', 'woocommerce-exporter' ), 
		'style' => apply_filters( 'woo_ce_scheduled_export_method_email_contents_style', 'height:10em;' ) 
	) 
);
echo '</div>';
?>

</div>
<!-- .email-options -->

<?php
		ob_end_flush();

}

function woo_ce_scheduled_export_method_post( $post_ID = 0 ) {

	ob_start(); ?>
<div class="export-options post-options">

<?php
echo '<div class="options_group">';
woocommerce_wp_text_input( 
	array( 
		'id' => '_method_post_to', 
		'label' => __( 'Remote POST URL', 'woocommerce' ), 
		'desc_tip' => 'true', 
		'description' => __( 'Set the remote POST address for scheduled exports, this is for integration with web applications that accept a remote form POST. Default is empty.', 'woocommerce-exporter' ) 
	) 
);
echo '</div>';
?>

</div>
<!-- .post-options -->
<?php
		ob_end_flush();

}

function woo_ce_scheduled_export_method_ftp( $post_ID = 0 ) {

	$ftp_host = get_post_meta( $post_ID, '_method_ftp_host', true );
	$ftp_port = get_post_meta( $post_ID, '_method_ftp_port', true );
	$ftp_protocol = get_post_meta( $post_ID, '_method_ftp_protocol', true );
	$ftp_encryption = get_post_meta( $post_ID, '_method_ftp_encryption', true );
	$ftp_user = get_post_meta( $post_ID, '_method_ftp_user', true );
	$ftp_pass = get_post_meta( $post_ID, '_method_ftp_pass', true );
	$ftp_path = get_post_meta( $post_ID, '_method_ftp_path', true );
	$ftp_filename = get_post_meta( $post_ID, '_method_ftp_filename', true );
	$ftp_passive = get_post_meta( $post_ID, '_method_ftp_passive', true );
	$ftp_mode = get_post_meta( $post_ID, '_method_ftp_mode', true );
	$ftp_timeout = get_post_meta( $post_ID, '_method_ftp_timeout', true );

	ob_start(); ?>
<div class="export-options ftp-options">

	<div class="options_group">
		<p class="form-field coupon_amount_field ">
			<label for="ftp_method_host"><?php _e( 'Host', 'woocommerce-exporter' ); ?></label>
			<input type="text" id="ftp_method_host" name="ftp_method_host" size="15" class="short code" value="<?php echo sanitize_text_field( $ftp_host ); ?>" style="margin-right:6px;" />
			<img class="help_tip" data-tip="<?php _e( 'Enter the Host minus <code>ftp://</code> or <code>ftps://</code>', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
			<span style="float:left; margin-right:6px;"><?php _e( 'Port', 'woocommerce-exporter' ); ?></span>
			<input type="text" id="ftp_method_port" name="ftp_method_port" size="5" class="short code sized" value="<?php echo sanitize_text_field( $ftp_port ); ?>" maxlength="5" />
		</p>

		<p class="form-field coupon_amount_field ">
			<label for="ftp_method_protocol"><?php _e( 'Protocol', 'woocommerce-exporter' ); ?></label>
			<select id="ftp_method_protocol" name="ftp_method_protocol" class="select short">
				<option value="ftp"<?php selected( $ftp_protocol, 'ftp' ); ?>><?php _e( 'FTP - File Transfer Protocol', 'woocommerce-exporter' ); ?></option>
				<option value="sftp"<?php selected( $ftp_protocol, 'sftp' ); ?><?php disabled( ( !function_exists( 'ssh2_connect' ) ? true : false ), true ); ?>><?php _e( 'SFTP - SSH File Transfer Protocol', 'woocommerce-exporter' ); ?></option>
			</select>
<?php if( !function_exists( 'ssh2_connect' ) ) { ?>
			<img class="help_tip" data-tip="<?php _e( 'The SFTP - SSH File Transfer Protocol option is not available as the required function ssh2_connect() is disabled within your WordPress site.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } ?>
		</p>

		<p class="form-field coupon_amount_field ">
			<label for="ftp_method_ftp_encryption"><?php _e( 'Encryption', 'woocommerce-exporter' ); ?></label>
			<select id="ftp_method_encryption" name="ftp_method_encryption" class="select short">
				<option value=""<?php selected( $ftp_encryption, false ); ?>><?php _e( 'Only use plain FTP (insecure)', 'woocommerce-exporter' ); ?></option>
				<option value="implicit"<?php selected( $ftp_encryption, 'implicit' ); ?>><?php _e( 'Require implicit FTP over TLS', 'woocommerce-exporter' ); ?></option>
				<option value="explicit"<?php selected( $ftp_encryption, 'explicit' ); ?>><?php _e( 'Require explicit FTP over TLS', 'woocommerce-exporter' ); ?></option>
			</select>
		</p>

		<p class="form-field coupon_amount_field ">
			<label for="ftp_method_user"><?php _e( 'Username', 'woocommerce-exporter' ); ?></label>
			<input type="text" id="ftp_method_user" name="ftp_method_user" size="15" class="short code" value="<?php echo sanitize_text_field( $ftp_user ); ?>" />
		</p>

		<p class="form-field coupon_amount_field ">
			<label for="ftp_method_pass"><?php _e( 'Password', 'woocommerce-exporter' ); ?></label> <input type="password" id="ftp_method_pass" name="ftp_method_pass" size="15" class="short code" value="" placeholder="<?php echo str_repeat( '*', strlen( $ftp_pass ) ); ?>" /><?php if( !empty( $ftp_pass ) ) { echo ' ' . __( '(password is saved, fill this field to change it)', 'woocommerce-exporter' ); } ?><br />
		</p>

		<p class="form-field coupon_amount_field ">
			<label for="ftp_method_file_path"><?php _e( 'File path', 'woocommerce-exporter' ); ?></label> <input type="text" id="ftp_method_file_path" name="ftp_method_path" size="25" class="short code" value="<?php echo sanitize_text_field( $ftp_path ); ?>" />
			<img class="help_tip" data-tip="<?php _e( 'Do not provide the filename within File path as it will be generated for you or rely on the fixed filename entered below.<br /><br />For file path example: <code>wp-content/uploads/exports/</code>', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
		</p>

		<p class="form-field coupon_amount_field ">
			<label for="ftp_method_filename"><?php _e( 'Fixed filename', 'woocommerce-exporter' ); ?></label> <input type="text" id="ftp_method_filename" name="ftp_method_filename" size="25" class="short code" value="<?php echo esc_attr( $ftp_filename ); ?>" />
			<img class="help_tip" data-tip="<?php _e( 'The export filename can be set within the Fixed filename field otherwise it defaults to the Export filename provided within General Settings above.<br /><br />Tags can be used: ', 'woocommerce-exporter' ); ?> <code>%dataset%</code>, <code>%date%</code>, <code>%time%</code>, <code>%random%</code>, <code>%store_name%</code>." src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
		</p>

	</div>

	<div class="options_group">
		<p class="form-field coupon_amount_field ">
			<label for="ftp_method_passive"><?php _e( 'Connection mode', 'woocommerce-exporter' ); ?></label> 
			<select id="ftp_method_passive" name="ftp_method_passive" class="select short">
				<option value="auto"<?php selected( $ftp_passive, '' ); ?>><?php _e( 'Auto', 'woocommerce-exporter' ); ?></option>
				<option value="active"<?php selected( $ftp_passive, 'active' ); ?>><?php _e( 'Active', 'woocommerce-exporter' ); ?></option>
				<option value="passive"<?php selected( $ftp_passive, 'passive' ); ?>><?php _e( 'Passive', 'woocommerce-exporter' ); ?></option>
			</select>
			<img class="help_tip" data-tip="<?php _e( 'Adjust the Connection mode where your FTP server requires an explicit Active or Passive connection mode.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
		</p>

		<p class="form-field coupon_amount_field ">
			<label for="ftp_method_mode"><?php _e( 'Transfer mode', 'woocommerce-exporter' ); ?></label> 
			<select id="ftp_method_mode" name="ftp_method_mode" class="select short">
				<option value="ASCII"<?php selected( $ftp_mode, 'ASCII' ); ?>><?php _e( 'ASCII', 'woocommerce-exporter' ); ?></option>
				<option value="BINARY"<?php selected( $ftp_mode, 'BINARY' ); ?>><?php _e( 'BINARY', 'woocommerce-exporter' ); ?></option>
			</select>
			<img class="help_tip" data-tip="<?php _e( 'Adjust the Transfer mode where your FTP server requires an explicit FTP_ASCII or FTP_BINARY transfer mode.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
		</p>

		<p class="form-field coupon_amount_field ">
			<label for="ftp_method_timeout"><?php _e( 'Timeout', 'woocommerce-exporter' ); ?></label> <input type="text" id="ftp_method_timeout" name="ftp_method_timeout" size="5" class="sized code" value="<?php echo sanitize_text_field( $ftp_timeout ); ?>" />
		</p>

	</div>

</div>
<!-- .ftp-options -->
<?php
		ob_end_flush();

}

function woo_ce_scheduled_export_method_google_sheets( $post_ID = 0 ) {

	ob_start(); ?>
<div class="export-options google_sheets-options">

	<div class="options_group">
<?php
	$client_id = get_post_meta( $post_ID, '_method_google_sheets_client_id', true );
	if( $client_id == false ) {
		woocommerce_wp_text_input(
			array(
				'id' => '_method_google_sheets_client_id', 
				'label' => __( 'Client ID', 'woocommerce' ), 
				'desc_tip' => 'true', 
				'description' => sprintf( __( 'Your Client ID can be retrieved from your project in the <a href="%s" target="_blank">Google Developer Console</a>', 'woocommerce-exporter' ), 'https://console.developers.google.com' )
			)
		);
	} else {
?>
	</div>

	<div id="google-sheets-authorize-div" class="options_group" style="display:none">
		<p class="form-field discount_type_field">
			<label><?php _e( 'Google Sheets Access', 'woocommerce-exporter' ); ?></label>
			<?php _e( '<strong>Store Exporter Deluxe does not have permission</strong> to save Scheduled Exports to Google Sheets.', 'woocommerce-exporter' ); ?>
			<a id="google-sheets-change-device-id" href="#" style="float:right;"><?php _e( 'Change Client ID', 'woocommerce-exporter' ); ?></a>
		</p>
		<p id="authorize-field" class="form-field discount_type_field">
			<button id="authorize-button" onclick="handleAuthClick(event)" class="button"><?php _e( 'Authorize', 'woocommerce-exporter' ); ?></button>
		</p>
		<p class="description"><?php _e( 'For Store Exporter Deluxe to save Scheduled Exports to Google Sheets, you will first need to <strong>give Store Exporter Deluxe permission</strong>.', 'woocommerce-exporter' ); ?></p>
		<p class="description"><?php _e( 'Clicking the Authorize button above will open an OAuth 2.0 dialog linking Store Exporter Deluxe to Google Sheets, you can remoke permission at any time from Google > My Account > Sign-in & security.', 'woocommerce-exporter' ); ?></p>
	</div>
	<div id="google-sheets-authorized-div" class="options_group">
		<p class="form-field discount_type_field">
			<label><?php _e( 'Google Sheets Access', 'woocommerce-exporter' ); ?></label>
			<?php _e( '<strong>Store Exporter Deluxe has permission</strong> to save Scheduled Exports to Google Sheets.', 'woocommerce-exporter' ); ?>
		</p>
<?php
		woocommerce_wp_text_input(
			array(
				'id' => '_method_google_sheets_title', 
				'label' => __( 'Spreadsheet Title', 'woocommerce' ), 
				'desc_tip' => 'true', 
				'description' => __( 'The Title of your Spreadsheet', 'woocommerce-exporter' )
			)
		);
?>
	</div>

	<script type="text/javascript">
		// Your Client ID can be retrieved from your project in the Google
		// Developer Console, https://console.developers.google.com
		var CLIENT_ID = '<?php echo $client_id; ?>';
		var SCOPES = ["https://www.googleapis.com/auth/spreadsheets"];

		/**
		 * Check if current user has authorized this application.
		 */
		function checkAuth() {
		  gapi.auth.authorize(
		    {
		      'client_id': CLIENT_ID,
		      'scope': SCOPES.join(' '),
		      'immediate': true
		    }, handleAuthResult);
		}

		/**
		 * Handle response from authorization server.
		 *
		 * @param {Object} authResult Authorization result.
		 */
		function handleAuthResult(authResult) {
		  var authorizeDiv = document.getElementById('google-sheets-authorize-div');
		  var authorizedDiv = document.getElementById('google-sheets-authorized-div');
		  if (authResult && !authResult.error) {
		    // Hide auth UI, then load client library.
		    authorizeDiv.style.display = 'none';
		    authorizedDiv.style.display = 'inline';
		    loadSheetsApi();
		  } else {
		    // Show auth UI, allowing the user to initiate authorization by
		    // clicking authorize button.
		    authorizeDiv.style.display = 'inline';
		    authorizedDiv.style.display = 'none';
		  }
		}

		/**
		 * Initiate auth flow in response to user clicking authorize button.
		 *
		 * @param {Event} event Button click event.
		 */
		function handleAuthClick(event) {
		  event.preventDefault();
		  gapi.auth.authorize(
		    {client_id: CLIENT_ID, scope: SCOPES, immediate: false},
		    handleAuthResult);
		  return false;
		}

		/**
		 * Load Sheets API client library.
		 */
		function loadSheetsApi() {
		  var discoveryUrl = 'https://sheets.googleapis.com/$discovery/rest?version=v4';
		}
	</script>
	<script src="https://apis.google.com/js/client.js?onload=checkAuth"></script>
<?php
	} ?>
</div>
<!-- .save-options -->

<?php
	ob_end_flush();

}

function woo_ce_scheduled_export_frequency_schedule( $post_ID = 0 ) {

	$auto_schedule = get_post_meta( $post_ID, '_auto_schedule', true );
	if( $auto_schedule == false )
		$auto_schedule = 'daily';
	$auto_interval = get_post_meta( $post_ID, '_auto_interval', true );

	ob_start(); ?>
<div class="options_group">
	<p class="form-field coupon_amount_field ">
		<label for="auto_schedule"><?php _e( 'Frequency', 'woocommerce-exporter' ); ?></label>
		<input type="radio" name="auto_schedule" value="hourly"<?php checked( $auto_schedule, 'hourly' ); ?> /> <?php _e( 'Hourly', 'woocommerce-exporter' ); ?><br />
		<input type="radio" name="auto_schedule" value="daily"<?php checked( $auto_schedule, 'daily' ); ?> /> <?php _e( 'Daily', 'woocommerce-exporter' ); ?><br />
		<input type="radio" name="auto_schedule" value="twicedaily"<?php checked( $auto_schedule, 'twicedaily' ); ?> /> <?php _e( 'Twice Daily', 'woocommerce-exporter' ); ?><br />
		<input type="radio" name="auto_schedule" value="weekly"<?php checked( $auto_schedule, 'weekly' ); ?> /> <?php _e( 'Weekly', 'woocommerce-exporter' ); ?><br />
		<input type="radio" name="auto_schedule" value="monthly"<?php checked( $auto_schedule, 'monthly' ); ?> /> <?php _e( 'Monthly', 'woocommerce-exporter' ); ?><br />
		<span style="float:left; margin-right:6px;"><input type="radio" name="auto_schedule" value="custom"<?php checked( $auto_schedule, 'custom' ); ?> />&nbsp;<?php _e( 'Every ', 'woocommerce-exporter' ); ?></span>
		<input name="auto_interval" type="text" id="auto_interval" value="<?php echo esc_attr( $auto_interval ); ?>" size="6" maxlength="6" class="text sized" />
		<span style="float:left; margin-right:6px;"><?php _e( 'minutes', 'woocommerce-exporter' ); ?></span><br class="clear" />
		<input type="radio" name="auto_schedule" value="one-time"<?php checked( $auto_schedule, 'one-time' ); ?> /> <?php _e( 'One time', 'woocommerce-exporter' ); ?><br class="clear" />
		<input type="radio" name="auto_schedule" value="manual"<?php checked( $auto_schedule, 'manual' ); ?> /> <?php _e( 'Run manually only', 'woocommerce-exporter' ); ?>
	</p>
</div>
<!-- .options_group -->
<?php
		ob_end_flush();

}

function woo_ce_scheduled_export_frequency_days( $post_ID = 0 ) {

	$auto_days = get_post_meta( $post_ID, '_auto_days', true );
	// Default to all days
	if( empty( $auto_days ) )
		$auto_days = array( 0, 1, 2, 3, 4, 5, 6 );

	ob_start(); ?>
<div class="options_group">
	<p class="form-field coupon_amount_field ">
		<label for="auto_days"><?php _e( 'Days', 'woocommerce-exporter' ); ?></label>
		<input type="checkbox" name="auto_days[]" value="1"<?php checked( in_array( 1, $auto_days ), true ); ?> /> <?php _e( 'Monday', 'woocommerce-exporter' ); ?><br />
		<input type="checkbox" name="auto_days[]" value="2"<?php checked( in_array( 2, $auto_days ), true ); ?> /> <?php _e( 'Tuesday', 'woocommerce-exporter' ); ?><br />
		<input type="checkbox" name="auto_days[]" value="3"<?php checked( in_array( 3, $auto_days ), true ); ?> /> <?php _e( 'Wednesday', 'woocommerce-exporter' ); ?><br />
		<input type="checkbox" name="auto_days[]" value="4"<?php checked( in_array( 4, $auto_days ), true ); ?> /> <?php _e( 'Thursday', 'woocommerce-exporter' ); ?><br />
		<input type="checkbox" name="auto_days[]" value="5"<?php checked( in_array( 5, $auto_days ), true ); ?> /> <?php _e( 'Friday', 'woocommerce-exporter' ); ?><br />
		<input type="checkbox" name="auto_days[]" value="6"<?php checked( in_array( 6, $auto_days ), true ); ?> /> <?php _e( 'Saturday', 'woocommerce-exporter' ); ?><br />
		<input type="checkbox" name="auto_days[]" value="0"<?php checked( in_array( 0, $auto_days ), true ); ?> /> <?php _e( 'Sunday', 'woocommerce-exporter' ); ?>
	</p>
</div>
<!-- .options_group -->
<?php

}

function woo_ce_scheduled_export_frequency_commence( $post_ID = 0 ) {

	$auto_commence = get_post_meta( $post_ID, '_auto_commence', true );
	$auto_commence_date = get_post_meta( $post_ID, '_auto_commence_date', true );
	$timezone_format = _x( 'Y-m-d H:i:s', 'timezone date format' );

	ob_start(); ?>
<div class="options_group">
	<p class="form-field coupon_amount_field ">
		<label for="auto_commence"><?php _e( 'Commence', 'woocommerce-exporter' ); ?></label>
		<input type="radio" name="auto_commence" value="now"<?php checked( ( $auto_commence == false ? 'now' : $auto_commence ), 'now' ); ?> /> <?php _e( 'From now', 'woocommerce-exporter' ); ?><br />
		<span style="float:left; margin-right:6px;"><input type="radio" name="auto_commence" value="future"<?php checked( $auto_commence, 'future' ); ?> /> <?php _e( 'From', 'woocommerce-exporter' ); ?></span><input type="text" name="auto_commence_date" size="20" maxlength="20" class="sized datetimepicker" value="<?php echo $auto_commence_date; ?>" /><!--, <?php _e( 'at this time', 'woocommerce-exporter' ); ?>: <input type="text" name="auto_interval_time" size="10" maxlength="10" class="text timepicker" />-->
		<span style="float:left; margin-right:6px;"><?php printf( __( 'Local time is: <code>%s</code>', 'woocommerce-exporter' ), date_i18n( $timezone_format ) ); ?></span>
	</p>
</div>
<!-- .options_group -->
<?php
		ob_end_flush();

}

function woo_ce_scheduled_export_details_meta_box() {

	global $post;

	$post_ID = ( $post ? $post->ID : 0 );

	$exports = get_post_meta( $post_ID, '_total_exports', true );
	$exports = absint( $exports );
	$last_export = get_post_meta( $post_ID, '_last_export', true );
	$last_export = ( $last_export == false ? 'No exports yet' : woo_ce_format_archive_date( 0, $last_export ) );

	$template = 'scheduled_export-export_details.php';
	include_once( WOO_CD_PATH . 'templates/admin/' . $template );

}

function woo_ce_scheduled_export_history_meta_box() {

	global $post;

	$post_ID = ( $post ? $post->ID : 0 );

	$enable_auto = woo_ce_get_option( 'enable_auto', 0 );
	$recent_exports = woo_ce_get_option( 'recent_scheduled_exports', array() );
	if( empty( $recent_exports ) )
		$recent_exports = array();
	$size = count( $recent_exports );
	$recent_exports = array_reverse( $recent_exports );

	// Strip out history not linked to this Post ID
	foreach( $recent_exports as $key => $recent_export ) {
		if( $recent_export['scheduled_id'] <> $post_ID )
			unset( $recent_exports[$key] );
	}

	$template = 'scheduled_export-history.php';
	include_once( WOO_CD_PATH . 'templates/admin/' . $template );

}

function woo_ce_admin_scheduled_export_post_status() {

	// In-line javascript
	ob_start(); ?>
<script type="text/javascript">
jQuery(document).ready( function($) {
	/* Hide Post Status */
	jQuery('#post_status option[value="pending"]').remove();
	jQuery('#post_status option[value="private"]').remove();
	jQuery('.misc-pub-curtime').hide();
});
</script>
<?php
	ob_end_flush();

}

function woo_ce_scheduled_export_update( $post_ID = '', $post = array() ) {

	$post_type = 'scheduled_export';
	if( $post['post_type'] <> $post_type )
		return;

	if(
		( get_post_status( $post_ID ) == 'publish' && $post['post_status'] == 'draft' ) || 
		$post['post_status'] == 'trash'
	) {
		woo_ce_cron_activation( true, $post_ID );
	}

}

function woo_ce_scheduled_export_save( $post_ID = 0 ) {

	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	// Make sure we play nice with other WooCommerce and WordPress exporters
	if( !isset( $_POST['woo_ce_export'] ) )
		return;

	$post_type = 'scheduled_export';
	check_admin_referer( $post_type, 'woo_ce_export' );

	woo_ce_load_export_types();

	// General
	update_post_meta( $post_ID, '_export_type', sanitize_text_field( $_POST['export_type'] ) );
	update_post_meta( $post_ID, '_export_format', sanitize_text_field( $_POST['export_format'] ) );
	update_post_meta( $post_ID, '_export_method', sanitize_text_field( $_POST['export_method'] ) );
	update_post_meta( $post_ID, '_export_fields', sanitize_text_field( $_POST['export_fields'] ) );
	update_post_meta( $post_ID, '_header_formatting', sanitize_text_field( $_POST['header_formatting'] ) );
	update_post_meta( $post_ID, '_export_template', sanitize_text_field( $_POST['export_template'] ) );
	update_post_meta( $post_ID, '_order', sanitize_text_field( $_POST['order'] ) );
	update_post_meta( $post_ID, '_delimiter', sanitize_text_field( $_POST['delimiter'] ) );
	update_post_meta( $post_ID, '_product_image_formatting', sanitize_text_field( $_POST['product_image_formatting'] ) );
	update_post_meta( $post_ID, '_gallery_formatting', sanitize_text_field( $_POST['gallery_formatting'] ) );
	update_post_meta( $post_ID, '_limit_volume', sanitize_text_field( $_POST['limit_volume'] ) );
	update_post_meta( $post_ID, '_offset', sanitize_text_field( $_POST['offset'] ) );

	// Allow Plugin/Theme authors to save custom fields from the Export Filters meta box
	do_action( 'woo_ce_extend_scheduled_export_save', $post_ID );

	// Method

	// Archive to WordPress Media
	update_post_meta( $post_ID, '_method_archive_parent_post', sanitize_text_field( $_POST['archive_method_parent_post'] ) );

	// Save to this server
	update_post_meta( $post_ID, '_method_save_path', sanitize_text_field( $_POST['save_method_path'] ) );

	// Send as e-mail
	update_post_meta( $post_ID, '_method_email_to', sanitize_text_field( $_POST['_method_email_to'] ) );
	update_post_meta( $post_ID, '_method_email_subject', sanitize_text_field( $_POST['_method_email_subject'] ) );
	update_post_meta( $post_ID, '_method_email_contents', wp_kses( $_POST['_method_email_contents'], woo_ce_format_email_contents_allowed_html(), woo_ce_format_email_contents_allowed_protocols() ) );

	// Post to remote URL
	update_post_meta( $post_ID, '_method_post_to', sanitize_text_field( $_POST['_method_post_to'] ) );

	// Upload to remote FTP/SFTP server 
	update_post_meta( $post_ID, '_method_ftp_host', ( isset( $_POST['ftp_method_host'] ) ? woo_ce_format_ftp_host( sanitize_text_field( $_POST['ftp_method_host'] ) ) : '' ) );
	update_post_meta( $post_ID, '_method_ftp_user', sanitize_text_field( $_POST['ftp_method_user'] ) );
	if( isset( $_POST['_method_google_sheets_client_id'] ) )
		update_post_meta( $post_ID, '_method_google_sheets_client_id', sanitize_text_field( $_POST['_method_google_sheets_client_id'] ) );
	if( isset( $_POST['_method_google_sheets_title'] ) )
		update_post_meta( $post_ID, '_method_google_sheets_title', sanitize_text_field( $_POST['_method_google_sheets_title'] ) );
	// Update FTP password only if it is filled in
	if( !empty( $_POST['ftp_method_pass'] ) )
		update_post_meta( $post_ID, '_method_ftp_pass', sanitize_text_field( $_POST['ftp_method_pass'] ) );
	update_post_meta( $post_ID, '_method_ftp_port', sanitize_text_field( $_POST['ftp_method_port'] ) );
	update_post_meta( $post_ID, '_method_ftp_protocol', sanitize_text_field( $_POST['ftp_method_protocol'] ) );
	update_post_meta( $post_ID, '_method_ftp_encryption', sanitize_text_field( $_POST['ftp_method_encryption'] ) );
	update_post_meta( $post_ID, '_method_ftp_path', sanitize_text_field( $_POST['ftp_method_path'] ) );
	update_post_meta( $post_ID, '_method_ftp_passive', sanitize_text_field( $_POST['ftp_method_passive'] ) );
	update_post_meta( $post_ID, '_method_ftp_mode', sanitize_text_field( $_POST['ftp_method_mode'] ) );
	update_post_meta( $post_ID, '_method_ftp_timeout', sanitize_text_field( $_POST['ftp_method_timeout'] ) );
	// Strip file extension from export filename
	$ftp_filename = ( isset( $_POST['ftp_method_filename'] ) ? strip_tags( $_POST['ftp_method_filename'] ) : '' );
	if( ( strpos( $ftp_filename, '.csv' ) !== false ) || ( strpos( $ftp_filename, '.tsv' ) !== false ) || ( strpos( $ftp_filename, '.xml' ) !== false ) || ( strpos( $ftp_filename, '.xls' ) !== false ) || ( strpos( $ftp_filename, '.xlsx' ) !== false ) )
		$ftp_filename = str_replace( array( '.csv', '.tsv', '.xml', '.xls', '.xlsx' ), '', $ftp_filename );
	update_post_meta( $post_ID, '_method_ftp_filename', $ftp_filename );
	unset( $ftp_filename );
	$save_filename = ( isset( $_POST['save_method_filename'] ) ? strip_tags( $_POST['save_method_filename'] ) : '' );
	if( ( strpos( $save_filename, '.csv' ) !== false ) || ( strpos( $save_filename, '.tsv' ) !== false ) || ( strpos( $save_filename, '.xml' ) !== false ) || ( strpos( $save_filename, '.xls' ) !== false ) || ( strpos( $save_filename, '.xlsx' ) !== false ) )
		$save_filename = str_replace( array( '.csv', '.tsv', '.xml', '.xls', '.xlsx' ), '', $save_filename );
	update_post_meta( $post_ID, '_method_save_filename', $save_filename );
	unset( $save_filename );

	// Scheduling
	$auto_schedule = ( isset( $_POST['auto_schedule'] ) ? sanitize_text_field( $_POST['auto_schedule'] ) : 'daily' );
	$auto_interval = ( $auto_schedule == 'custom' ? absint( $_POST['auto_interval'] ) : false );
	$auto_commence = ( isset( $_POST['auto_commence'] ) ? sanitize_text_field( $_POST['auto_commence'] ) : 'now' );
	$auto_commence_date = ( $auto_commence == 'future' ? sanitize_text_field( $_POST['auto_commence_date'] ) : false );
	$post_status = ( isset( $_POST['post_status'] ) ? $_POST['post_status'] : 'publish' );
	$auto_days = ( isset( $_POST['auto_days'] ) ? array_map( 'sanitize_text_field', $_POST['auto_days'] ) : false );
	update_post_meta( $post_ID, '_auto_days', $auto_days );

	// Check if scheduling options have been modified
	if(
		get_post_meta( $post_ID, '_auto_schedule', true ) <> $auto_schedule || 
		get_post_meta( $post_ID, '_auto_interval', true ) <> $auto_interval || 
		get_post_meta( $post_ID, '_auto_commence', true ) <> $auto_commence || 
		get_post_meta( $post_ID, '_auto_commence_date', true ) <> $auto_commence_date ||
		$post_status <> get_post_status( $post_ID )
	) {
		// Update the WP_CRON task as the Scheduled Export has changed
		update_post_meta( $post_ID, '_auto_schedule', $auto_schedule );
		update_post_meta( $post_ID, '_auto_interval', $auto_interval );
		update_post_meta( $post_ID, '_auto_commence', $auto_commence );
		update_post_meta( $post_ID, '_auto_commence_date', $auto_commence_date );
		woo_ce_cron_activation( true, $post_ID );
	}

}

function woo_ce_scheduled_export_delete( $post_ID = false ) {

	global $post_type;

	if( $post_type != 'scheduled_export' )
		return;

	// Remove any recent entries linked to this Scheduled Export
	$recent_exports = woo_ce_get_option( 'recent_scheduled_exports', array() );
	if( !empty( $recent_exports ) ) {
		$updated = false;
		foreach( $recent_exports as $key => $recent_export ) {
			if( isset( $recent_export['scheduled_id'] ) ) {
				if( $recent_export['scheduled_id'] == $post_ID ) {
					unset( $recent_exports[$key] );
					$updated = true;
				}
			}
		}
		if( $updated )
			woo_ce_update_option( 'recent_scheduled_exports', $recent_exports );
	}

}
add_action( 'before_delete_post', 'woo_ce_scheduled_export_delete' );

function woo_ce_extend_scheduled_export_save( $post_ID = 0 ) {

	// Filters

	// WooCommerce Brands Addon - http://woothemes.com/woocommerce/
	// WooCommerce Brands - http://proword.net/Woocommerce_Brands/
	if( woo_ce_detect_product_brands() ) {
		update_post_meta( $post_ID, '_filter_product_brand', ( isset( $_POST['product_filter_brand'] ) ? array_map( 'absint', $_POST['product_filter_brand'] ) : false ) );
	}

	// Product Vendors - http://www.woothemes.com/products/product-vendors/
	// WC Vendors - http://wcvendors.com
	// YITH WooCommerce Multi Vendor Premium - http://yithemes.com/themes/plugins/yith-woocommerce-product-vendors/
	if( woo_ce_detect_export_plugin( 'vendors' ) || woo_ce_detect_export_plugin( 'yith_vendor' ) ) {
		update_post_meta( $post_ID, '_filter_product_vendor', ( isset( $_POST['product_filter_vendor'] ) ? array_map( 'absint', $_POST['product_filter_vendor'] ) : false ) );
	}

	// WPML - https://wpml.org/
	// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
	if( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
		update_post_meta( $post_ID, '_filter_product_language', ( isset( $_POST['product_filter_language'] ) ? array_map( 'sanitize_text_field', $_POST['product_filter_language'] ) : false ) );
	}

	// WooCommerce Subscriptions - http://www.woothemes.com/products/woocommerce-subscriptions/
	if( woo_ce_detect_export_plugin( 'subscriptions' ) ) {
		update_post_meta( $post_ID, '_filter_order_type', ( isset( $_POST['order_filter_order_type'] ) ? sanitize_text_field( $_POST['order_filter_order_type'] ) : false ) );
	}

	// Product meta
	$custom_products = woo_ce_get_option( 'custom_products', '' );
	if( !empty( $custom_products ) ) {
		foreach( $custom_products as $custom_product ) {
			update_post_meta( $post_ID, sprintf( '_filter_product_custom_meta-%s', esc_attr( $custom_product ) ), ( isset( $_POST[sprintf( 'product_filter_custom_meta-%s', esc_attr( $custom_product ) )] ) ? sanitize_text_field( $_POST[sprintf( 'product_filter_custom_meta-%s', esc_attr( $custom_product ) )] ) : false ) );
		}
	}

	// Order meta
	$custom_orders = woo_ce_get_option( 'custom_orders', '' );
	if( !empty( $custom_orders ) ) {
		foreach( $custom_orders as $custom_order ) {
			update_post_meta( $post_ID, sprintf( '_filter_order_custom_meta-%s', esc_attr( $custom_order ) ), ( isset( $_POST[sprintf( 'order_filter_custom_meta-%s', esc_attr( $custom_order ) )] ) ? sanitize_text_field( $_POST[sprintf( 'order_filter_custom_meta-%s', esc_attr( $custom_order ) )] ) : false ) );
		}
	}

}
add_action( 'woo_ce_extend_scheduled_export_save', 'woo_ce_extend_scheduled_export_save' );

function woo_ce_admin_scheduled_exports_recent_scheduled_exports() {

	$enable_auto = woo_ce_get_option( 'enable_auto', 0 );
	$recent_exports = woo_ce_get_option( 'recent_scheduled_exports', array() );
	if( empty( $recent_exports ) )
		$recent_exports = array();
	$size = count( $recent_exports );
	$recent_exports = array_reverse( $recent_exports );

	// Pagination time!
	$per_page = apply_filters( 'woo_ce_admin_scheduled_exports_recent_scheduled_exports_per_page', 20 );
	$offset = ( isset( $_GET['paged'] ) ? ( absint( $_GET['paged'] ) * $per_page ) : 0 );
	$pagination_links = '';
	if( $size > $per_page ) {
		$pages = absint( $size / $per_page );
		$recent_exports = array_slice( $recent_exports, $offset, $per_page );

		if( function_exists( 'paginate_links' ) ) {
			$paginations = paginate_links( array(
				'base' => '?paged=%#%',
				'format' => '?paged=%#%',
				'type' => 'array',
				'current' => max( 1, ( isset( $_GET['paged'] ) ? ( absint( $_GET['paged'] ) ) : false ) ),
				'total' => $pages,
				// 'mid_size' => 0,
				// 'end_size' => 0,
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;'
			) );
			if( !empty( $paginations ) ) {
				foreach( $paginations as $pagination ) {
					$pagination_output = $pagination;
					if( strpos( $pagination, '<a class' ) !== false )
						$pagination_output = str_replace( 'page-numbers', 'button', $pagination_output );
					// $pagination_output = str_replace( array( __( 'Previous' ), __( 'Next' ) ), '', $pagination_output );
					$pagination_links .= $pagination_output;
				}
			}
			unset( $paginations, $pagination );
		}

	}

	$template = 'scheduled_exports-recent_scheduled_exports.php';
	if( file_exists( WOO_CD_PATH . 'templates/admin/' . $template ) ) {
		include_once( WOO_CD_PATH . 'templates/admin/' . $template );
	} else {
		$message = sprintf( __( 'We couldn\'t load the widget template file <code>%s</code> within <code>%s</code>, this file should be present.', 'woocommerce-exporter' ), $template, WOO_CD_PATH . 'templates/admin/...' );

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
add_action( 'woo_ce_after_scheduled_exports', 'woo_ce_admin_scheduled_exports_recent_scheduled_exports' );
?>