<h1 class="wp-heading-inline"><?php _e( 'Debugging', 'woocommerce-exporter' ); ?></h1>
<?php
// Gravity Forms - http://woothemes.com/woocommerce
if( woo_ce_detect_export_plugin( 'gravity_forms' ) ) {
	echo '<h2 class="wp-heading-inline"">';
	echo __( 'Gravity Forms', 'woocommerce-exporter' );
	echo '<a href="' . esc_url( add_query_arg( array( 'action' => 'refresh_export_type_counts', '_wpnonce' => wp_create_nonce( 'woo_ce_refresh_export_type_counts' ) ) ) ) . '" class="page-title-action">' . __( 'Refresh', 'woocommerce-exporter' ) . '</a>';
	echo '</h2>';

	echo '<h3>' . __( 'Products', 'woocommerce-exporter' ) . '</h3>';
	$products = get_transient( WOO_CD_PREFIX . '_gravity_forms_products' );
	if( $products !== false ) {
		print_r( $products );
	} else {
		printf( __( 'The Gravity Forms Products Transient %s is not populated.', 'woocommerce-exporter' ), '<code>' . WOO_CD_PREFIX . '_gravity_forms_products' . '</code>' );
	}

	echo '<h3>' . __( 'Fields', 'woocommerce-exporter' ) . '</h3>';
	$fields = get_transient( WOO_CD_PREFIX . '_gravity_forms_fields' );
	if( $fields !== false ) {
		print_r( $fields );
	} else {
		printf( __( 'The Gravity Forms fields Transient %s is not populated.', 'woocommerce-exporter' ), '<code>' . WOO_CD_PREFIX . '_gravity_forms_fields' . '</code>' );
	}
	echo '<hr />';
}

// WooCommerce TM Extra Product Options - http://codecanyon.net/item/woocommerce-extra-product-options/7908619
if( woo_ce_detect_export_plugin( 'extra_product_options' ) ) {
	echo '<h2 class="wp-heading-inline"">';
	echo __( 'Extra Product Options', 'woocommerce-exporter' );
	echo '<a href="' . esc_url( add_query_arg( array( 'action' => 'refresh_export_type_counts', '_wpnonce' => wp_create_nonce( 'woo_ce_refresh_export_type_counts' ) ) ) ) . '" class="page-title-action">' . __( 'Refresh', 'woocommerce-exporter' ) . '</a>';
	echo '</h2>';

	echo '<h3>' . __( 'Fields', 'woocommerce-exporter' ) . '</h3>';
	$fields = get_transient( WOO_CD_PREFIX . '_extra_product_option_fields' );
	if( $fields !== false ) {
		print_r( $fields );
	} else {
		printf( __( 'The EPO Transient %s is not populated.', 'woocommerce-exporter' ), '<code>' . WOO_CD_PREFIX . '_extra_product_option_fields' . '</code>' );
	}
	echo '<hr />';
}
?>