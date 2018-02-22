<?php
// Adds custom Tag columns to the Tag fields list
function woo_ce_extend_tag_fields( $fields = array() ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$fields[] = array(
			'name' => 'blog_id',
			'label' => __( 'Blog ID', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress Multisite', 'woocommerce-exporter' )
		);
	}

	// WordPress SEO - http://wordpress.org/plugins/wordpress-seo/
	if( woo_ce_detect_export_plugin( 'wpseo' ) ) {
		$fields[] = array(
			'name' => 'wpseo_title',
			'label' => __( 'WordPress SEO - SEO Title', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_description',
			'label' => __( 'WordPress SEO - SEO Description', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_focuskw',
			'label' => __( 'WordPress SEO - Focus Keyword', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_canonical',
			'label' => __( 'WordPress SEO - Canonical', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_noindex',
			'label' => __( 'WordPress SEO - Noindex', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_sitemap_include',
			'label' => __( 'WordPress SEO - Sitemap include', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_opengraph_title',
			'label' => __( 'WordPress SEO - Facebook Title', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_opengraph_description',
			'label' => __( 'WordPress SEO - Facebook Description', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_opengraph_image',
			'label' => __( 'WordPress SEO - Facebook Image', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_twitter_title',
			'label' => __( 'WordPress SEO - Twitter Title', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_twitter_description',
			'label' => __( 'WordPress SEO - Twitter Description', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_twitter_image',
			'label' => __( 'WordPress SEO - Twitter Image', 'woocommerce-exporter' ),
			'hover' => __( 'WordPress SEO', 'woocommerce-exporter' )
		);
	}

	// WPML - https://wpml.org/
	// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
	if( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
		$fields[] = array(
			'name' => 'language',
			'label' => __( 'Language', 'woocommerce-exporter' ),
			'hover' => __( 'WooCommerce Multilingual', 'woocommerce-exporter' )
		);
	}

	return $fields;

}
add_filter( 'woo_ce_tag_fields', 'woo_ce_extend_tag_fields' );

function woo_ce_extend_tag_item( $tag ) {

	// WordPress MultiSite
	if( is_multisite() ) {
		$tag->blog_id = get_current_blog_id();
	}

	// WordPress SEO - http://wordpress.org/plugins/wordpress-seo/
	if( woo_ce_detect_export_plugin( 'wpseo' ) ) {
		$meta = get_option( 'wpseo_taxonomy_meta' );
		// Check if the WordPress Option is empty
		if( $meta !== false ) {
			// Check if the WordPress Option is an array
			if( is_array( $meta ) ) {
				// Check if the product_cat Taxonomy exists within the WordPress Option
				$term_taxonomy = 'product_tag';
				if( array_key_exists( $term_taxonomy, $meta ) ) {
					$meta = $meta[$term_taxonomy];
					// Check if the Term ID exists within the array
					$term_id = ( isset( $tag->term_id ) ? $tag->term_id : 0 );
					if( array_key_exists( $term_id, $meta ) ) {
						$tag->wpseo_title = ( isset( $meta[$term_id]['wpseo_title'] ) ? $meta[$term_id]['wpseo_title'] : '' );
						$tag->wpseo_description = ( isset( $meta[$term_id]['wpseo_desc'] ) ? $meta[$term_id]['wpseo_desc'] : '' );
						$tag->wpseo_canonical = ( isset( $meta[$term_id]['wpseo_canonical'] ) ? $meta[$term_id]['wpseo_canonical'] : '' );
						$tag->wpseo_noindex = ( isset( $meta[$term_id]['wpseo_noindex'] ) ? woo_ce_format_wpseo_noindex( $meta[$term_id]['wpseo_noindex'] ) : '' );
						$tag->wpseo_sitemap_include = ( isset( $meta[$term_id]['wpseo_sitemap_include'] ) ? woo_ce_format_wpseo_sitemap_include( $meta[$term_id]['wpseo_sitemap_include'] ) : '' );
						$tag->wpseo_focuskw = ( isset( $meta[$term_id]['wpseo_focuskw'] ) ? $meta[$term_id]['wpseo_focuskw'] : '' );
						$tag->wpseo_opengraph_title = ( isset( $meta[$term_id]['wpseo_opengraph-title'] ) ? $meta[$term_id]['wpseo_opengraph-title'] : '' );
						$tag->wpseo_opengraph_description = ( isset( $meta[$term_id]['wpseo_opengraph-description'] ) ? $meta[$term_id]['wpseo_opengraph-description'] : '' );
						$tag->wpseo_opengraph_image = ( isset( $meta[$term_id]['wpseo_opengraph-image'] ) ? $meta[$term_id]['wpseo_opengraph-image'] : '' );
						$tag->wpseo_twitter_title = ( isset( $meta[$term_id]['wpseo_twitter-title'] ) ? $meta[$term_id]['wpseo_twitter-title'] : '' );
						$tag->wpseo_twitter_description = ( isset( $meta[$term_id]['wpseo_twitter-description'] ) ? $meta[$term_id]['wpseo_twitter-description'] : '' );
						$tag->wpseo_twitter_image = ( isset( $meta[$term_id]['wpseo_twitter-image'] ) ? $meta[$term_id]['wpseo_twitter-image'] : '' );
					}
					unset( $term_id );
				}
			}
		}
		unset( $meta );
	}

	// WPML - https://wpml.org/
	// WooCommerce Multilingual - https://wordpress.org/plugins/woocommerce-multilingual/
	if( woo_ce_detect_wpml() && woo_ce_detect_export_plugin( 'wpml_wc' ) ) {
		$term_id = ( isset( $tag->term_id ) ? $tag->term_id : 0 );
		$term_taxonomy = 'product_tag';
		$tag->language = woo_ce_wpml_get_language_name( apply_filters( 'wpml_element_language_code', null, array( 'element_id' => $term_id, 'element_type' => $term_taxonomy ) ) );
	}

	return $tag;

}
add_filter( 'woo_ce_tag_item', 'woo_ce_extend_tag_item' );
?>