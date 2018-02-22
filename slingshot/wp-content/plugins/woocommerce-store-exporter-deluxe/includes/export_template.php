<?php
function woo_ce_export_template_banner( $post ) {

	// Check the Post object exists
	if( isset( $post->post_type ) == false )
		return;

	// Limit to the Export Template Post Type
	$post_type = 'export_template';
	if( $post->post_type <> $post_type )
		return;

	if( apply_filters( 'woo_ce_export_template_banner_save_prompt', true ) )
		echo '<a href="' . esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'export_template' ), 'admin.php' ) ) . '" class="button confirm-button" data-confirm="' . __( 'The changes you made will be lost if you navigate away from this page before saving.', 'woocommerce-exporter' ) . '">' . __( 'Return to Export Templates', 'woocommerce-exporter' ) . '</a>';
	else
		echo '<a href="' . esc_url( add_query_arg( array( 'page' => 'woo_ce', 'tab' => 'export_template' ), 'admin.php' ) ) . '" class="button">' . __( 'Return to Export Templates', 'woocommerce-exporter' ) . '</a>';

}

function woo_ce_export_template_options_meta_box() {

	global $post;

	$post_ID = ( $post ? $post->ID : 0 );

	woo_ce_load_export_types();

	// General
	add_action( 'woo_ce_before_export_template_general_options', 'woo_ce_export_template_general_export_type' );

	// Filters
	if( function_exists( 'woo_ce_export_template_fields_product' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_product' );
	if( function_exists( 'woo_ce_export_template_fields_category' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_category' );
	if( function_exists( 'woo_ce_export_template_fields_tag' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_tag' );
	if( function_exists( 'woo_ce_export_template_fields_brand' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_brand' );
	if( function_exists( 'woo_ce_export_template_fields_order' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_order' );
	if( function_exists( 'woo_ce_export_template_fields_customer' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_customer' );
	if( function_exists( 'woo_ce_export_template_fields_user' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_user' );
	if( function_exists( 'woo_ce_export_template_fields_review' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_review' );
	if( function_exists( 'woo_ce_export_template_fields_coupon' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_coupon' );
	if( function_exists( 'woo_ce_export_template_fields_subscription' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_subscription' );
	if( function_exists( 'woo_ce_export_template_fields_product_vendor' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_product_vendor' );
	if( function_exists( 'woo_ce_export_template_fields_commission' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_commission' );
	if( function_exists( 'woo_ce_export_template_fields_shipping_class' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_shipping_class' );
	if( function_exists( 'woo_ce_export_template_fields_ticket' ) )
		add_action( 'woo_ce_before_export_template_fields_options', 'woo_ce_export_template_fields_ticket' );

	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';
?>
<div id="export_template_options" class="panel-wrap export_template_data">
	<div class="wc-tabs-back"></div>
	<ul class="coupon_data_tabs wc-tabs" style="display:none;">
<?php
	$coupon_data_tabs = apply_filters( 'woo_ce_export_template_data_tabs', array(
		'general' => array(
			'label'  => __( 'General', 'woocommerce' ),
			'target' => 'general_coupon_data',
			'class'  => 'general_coupon_data',
		),
		'fields' => array(
			'label'  => __( 'Fields', 'woocommerce' ),
			'target' => 'fields_coupon_data',
			'class'  => ''
		)
	) );

	foreach ( $coupon_data_tabs as $key => $tab ) { ?>
		<li class="<?php echo $key; ?>_options <?php echo $key; ?>_tab <?php echo implode( ' ' , (array) $tab['class'] ); ?>">
			<a href="#<?php echo $tab['target']; ?>"><?php echo esc_html( $tab['label'] ); ?></a>
		</li><?php
	} ?>
	</ul>
	<?php do_action( 'woo_ce_before_export_template_options', $post_ID ); ?>
	<div id="general_coupon_data" class="panel woocommerce_options_panel export_general_options">
		<?php do_action( 'woo_ce_before_export_template_general_options', $post_ID ); ?>
		<?php do_action( 'woo_ce_after_export_template_general_options', $post_ID ); ?>
	</div>
	<!-- #general_coupon_data -->

	<div id="fields_coupon_data" class="panel woocommerce_options_panel export_type_options ">
		<?php do_action( 'woo_ce_before_export_template_fields_options', $post_ID ); ?>
		<?php do_action( 'woo_ce_after_export_template_fields_options', $post_ID ); ?>
	</div>
	<!-- #fields_coupon_data -->

	<?php do_action( 'woo_ce_after_export_template_options', $post_ID ); ?>
	<div class="clear"></div>
</div>
<!-- #export_template_options -->
<?php
	wp_nonce_field( 'export_template', 'woo_ce_export' );

}

function woo_ce_export_template_general_export_type( $post_ID = 0 ) {

	$export_type = get_post_meta( $post_ID, '_export_type', true );
	$export_types = woo_ce_get_export_types();

	ob_start(); ?>
<div class="options_group">
	<p class="form-field"><?php _e( 'Select an Export type then switch to the Fields tab to select your export field preferences. You can save export field preferences for multiple Export Types. Click Publish or Update to save changes.', 'woocommerce-exporter' ); ?></p>
	<p class="form-field discount_type_field ">
		<label for="export_type"><?php _e( 'Export type', 'woocommerce-exporter' ); ?> </label>
<?php if( !empty( $export_types ) ) { ?>
		<select id="export_type" name="export_type" class="select short">
	<?php foreach( $export_types as $key => $type ) { ?>
			<option value="<?php echo $key; ?>"<?php selected( $export_type, $key ); ?>><?php echo $type; ?></option>
	<?php } ?>
		</select>
		<img class="help_tip" data-tip="<?php _e( 'Select the export type fields you want to manage.', 'woocommerce-exporter' ); ?>" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
<?php } else { ?>
		<?php _e( 'No export types were found.', 'woocommerce-exporter' ); ?>
<?php } ?>
	</p>
</div>
<!-- .options_group -->
<?php
	ob_end_flush();

}

function woo_ce_export_template_save( $post_ID = 0 ) {

	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;

	// Make sure we play nice with other WooCommerce and WordPress exporters
	if( !isset( $_POST['woo_ce_export'] ) )
		return;

	$post_type = 'export_template';
	check_admin_referer( $post_type, 'woo_ce_export' );

	// General
	$export_type = sanitize_text_field( $_POST['export_type'] );
	update_post_meta( $post_ID, '_export_type', $export_type );

	// Fields
	$export_types = woo_ce_get_export_types();
	if( !empty( $export_types ) ) {
		$export_types = array_keys( $export_types );
		foreach( $export_types as $export_type ) {
			$fields = ( isset( $_POST[sprintf( '%s_fields', $export_type )] ) ? array_map( 'sanitize_text_field', $_POST[sprintf( '%s_fields', $export_type )] ) : false );
			$sorting = ( isset( $_POST[sprintf( '%s_fields_order', $export_type )] ) ? array_map( 'absint', $_POST[sprintf( '%s_fields_order', $export_type )] ) : false );
			$labels = ( isset( $_POST[sprintf( '%s_fields_label', $export_type )] ) ? array_map( 'sanitize_text_field', $_POST[sprintf( '%s_fields_label', $export_type )] ) : false );
			if( !empty( $labels ) )
				$labels = array_filter( $labels );
			if( !empty( $fields ) ) {
				update_post_meta( $post_ID, sprintf( '_%s_fields', $export_type ), $fields );
				update_post_meta( $post_ID, sprintf( '_%s_sorting', $export_type ), $sorting );
				update_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ), $labels );
			} else {
				delete_post_meta( $post_ID, sprintf( '_%s_fields', $export_type ) );
				delete_post_meta( $post_ID, sprintf( '_%s_sorting', $export_type ) );
				delete_post_meta( $post_ID, sprintf( '_%s_labels', $export_type ) );
			}
		}
	}

}
?>