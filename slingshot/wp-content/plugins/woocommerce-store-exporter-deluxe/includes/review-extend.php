<?php
// Adds custom Review columns to the Review fields list
function woo_ce_extend_review_fields( $fields = array() ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$fields[] = array(
			'name' => 'blog_id',
			'label' => __( 'Blog ID', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress Multisite', 'woocommerce-exporter' )
		);
	}

	return $fields;

}
add_filter( 'woo_ce_review_fields', 'woo_ce_extend_review_fields' );

function woo_ce_extend_review_item( $review ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$review->blog_id = get_current_blog_id();
	}

	return $review;

}
add_filter( 'woo_ce_review_item', 'woo_ce_extend_review_item' );
?>