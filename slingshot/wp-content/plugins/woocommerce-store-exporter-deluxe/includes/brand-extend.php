<?php
// Adds custom Brand columns to the Brand fields list
function woo_ce_extend_brand_fields( $fields = array() ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$fields[] = array(
			'name' => 'blog_id',
			'label' => __( 'Blog ID', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress Multisite', 'woocommerce-exporter' )
		);
	}

	// YITH WooCommerce Brands Add-On - http://yithemes.com/themes/plugins/yith-woocommerce-brands-add-on/
	if( woo_ce_detect_export_plugin( 'yith_brands_pro' ) ) {
		$fields[] = array(
			'name' => 'custom_url',
			'label' => __( 'Custom URL', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce Brands Add-On', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'banner',
			'label' => __( 'Banner', 'woocommerce-exporter' ),
			'hover' => __( 'YITH WooCommerce Brands Add-On', 'woocommerce-exporter' )
		);
	}

	return $fields;

}
add_filter( 'woo_ce_brand_fields', 'woo_ce_extend_brand_fields' );

function woo_ce_extend_brand_item( $brand ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$brand->blog_id = get_current_blog_id();
	}

	// YITH WooCommerce Brands Add-On - http://yithemes.com/themes/plugins/yith-woocommerce-brands-add-on/
	if( woo_ce_detect_export_plugin( 'yith_brands_pro' ) ) {
		$thumbnail_id = get_woocommerce_term_meta( $brand->term_id, 'thumbnail_id', true );
		if( !empty( $thumbnail_id ) ) {
			$brand->image = wp_get_attachment_url( $thumbnail_id );
		}
		$thumbnail_id = get_woocommerce_term_meta( $brand->term_id, 'banner_id', true );
		if( !empty( $thumbnail_id ) ) {
			$brand->banner = wp_get_attachment_url( $thumbnail_id );
		}
		$brand->custom_url = get_woocommerce_term_meta( $brand->term_id, 'custom_url', true );
	}

	return $brand;

}
add_filter( 'woo_ce_brand_item', 'woo_ce_extend_brand_item' );

function woo_ce_extend_brand_term_taxonomy( $term_taxonomy = '' ) {

	if( woo_ce_detect_export_plugin( 'yith_brands_pro' ) )
		$term_taxonomy = 'yith_product_brand';

	return $term_taxonomy;

}
if( woo_ce_detect_product_brands() )
	add_filter( 'woo_ce_brand_term_taxonomy', 'woo_ce_extend_brand_term_taxonomy' );
?>