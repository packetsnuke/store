<?php

function flatsome_live_search_script() {
	global $extensions_uri;
	$theme   = wp_get_theme( get_template() );
	$version = $theme->get( 'Version' );
	wp_enqueue_script( 'flatsome-live-search', $extensions_uri . '/flatsome-live-search/flatsome-live-search.js', false, $version, true );
}

add_action( 'wp_enqueue_scripts', 'flatsome_live_search_script' );

/**
 * Search for posts and pages.
 *
 * @param  array $args
 *
 * @return array
 */
function flatsome_ajax_search_posts( $args ) {
	$defaults = $args;

	$args['s']         = apply_filters( 'flatsome_ajax_search_query', $_REQUEST['query'] );
	$args['post_type'] = apply_filters( 'flatsome_ajax_search_post_type', array( 'post', 'page' ) );

	$search_query   = http_build_query( $args );
	$query_function = apply_filters( 'flatsome_ajax_search_function', 'get_posts', $search_query, $args, $defaults );

	return ( ( $query_function == 'get_posts' ) || ! function_exists( $query_function ) ) ? get_posts( $args ) : $query_function( $search_query, $args, $defaults );
}

/**
 * Search for products.
 *
 * @param  array $args
 *
 * @return array
 */
function flatsome_ajax_search_products( $args ) {
	global $woocommerce;
	$ordering_args = $woocommerce->query->get_catalog_ordering_args( 'title', 'asc' );
	$defaults      = $args;

	$args['s']          = apply_filters( 'flatsome_ajax_search_products_search_query', $_REQUEST['query'] );
	$args['post_type']  = 'product';
	$args['orderby']    = $ordering_args['orderby'];
	$args['order']      = $ordering_args['order'];
	$args['meta_query'] = WC()->query->get_meta_query();
	$args['tax_query']  = array( 'relation' => 'AND' );
	$args               = flatsome_ajax_search_catalog_visibility( $args );

	if ( isset( $_REQUEST['product_cat'] ) ) {
		$args['tax_query'][] = array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => esc_attr( $_REQUEST['product_cat'] ),
			),
		);
	}

	$search_query   = http_build_query( $args );
	$query_function = apply_filters( 'flatsome_ajax_search_function', 'get_posts', $search_query, $args, $defaults );

	return ( ( $query_function == 'get_posts' ) || ! function_exists( $query_function ) ) ? get_posts( $args ) : $query_function( $search_query, $args, $defaults );
}

/**
 * Search for products by SKU.
 *
 * @return array
 */
function flatsome_ajax_search_products_by_sku() {
	$query = apply_filters( 'flatsome_ajax_search_products_by_sku_search_query', $_REQUEST['query'] );

	$query_args = array(
		'post_status' => 'publish',
		'post_type'   => array( 'product', 'product_variation' ),
		'meta_query'  => array(
			array(
				'key'   => '_sku',
				'value' => $query,
			),
		),
		'tax_query'   => array(
			'relation' => 'AND',
		),
	);

	$query_args = flatsome_ajax_search_catalog_visibility( $query_args );
	$results    = new WP_Query( $query_args );

	return $results->get_posts();
}

/**
 * Checks product catalog visibility with custom tax_query. (only queries the exclude-from-search term) and checks WC hide out of stock option.
 *
 * @param  array $args
 *
 * @return array
 */
function flatsome_ajax_search_catalog_visibility( $args ) {
	$product_visibility_term_ids = wc_get_product_visibility_term_ids();

	// Catalog visibility
	$args['tax_query'][] = array(
		'taxonomy' => 'product_visibility',
		'field'    => 'term_taxonomy_id',
		'terms'    => $product_visibility_term_ids['exclude-from-search'],
		'operator' => 'NOT IN',
	);

	// Hide out of stock
	if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
		$args['tax_query'][] = array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $product_visibility_term_ids['outofstock'],
				'operator' => 'NOT IN',
			),
		);
	}

	return $args;
}

/**
 * Search AJAX handler.
 *
 * @return array
 */
function flatsome_ajax_search() {
	// The string from search text field.
	$query        = apply_filters( 'flatsome_ajax_search_query', $_REQUEST['query'] );
	$products     = array();
	$posts        = array();
	$sku_products = array();

	$args = array(
		's'                   => $query,
		'orderby'             => '',
		'post_type'           => array(),
		'post_status'         => 'publish',
		'posts_per_page'      => 100,
		'ignore_sticky_posts' => 1,
		'post_password'       => '',
		'suppress_filters'    => false,
	);

	if ( is_woocommerce_activated() ) {
		$products     = flatsome_ajax_search_products( $args );
		$sku_products = get_theme_mod( 'search_by_sku', 0 ) ? flatsome_ajax_search_products_by_sku() : array();
	}

	if ( get_theme_mod( 'search_result', 1 ) && ! isset( $_REQUEST['product_cat'] ) ) {
		$posts = flatsome_ajax_search_posts( $args );
	}

	$results = array_merge( $products, $sku_products, $posts );

	$suggestions = array();

	foreach ( $results as $key => $post ) {
		if (is_woocommerce_activated() && ($post->post_type === 'product' || $post->post_type === 'product_variation') ) {
			$product       = wc_get_product( $post );
			$product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ) );

			$suggestions[] = array(
				'type'  => 'Product',
				'id'    => $product->get_id(),
				'value' => $product->get_title(),
				'url'   => $product->get_permalink(),
				'img'   => $product_image[0],
				'price' => $product->get_price_html(),
			);
		} else {
			$suggestions[] = array(
				'type'  => 'Page',
				'id'    => $post->ID,
				'value' => get_the_title( $post->ID ),
				'url'   => get_the_permalink( $post->ID ),
				'img'   => get_the_post_thumbnail_url( $post->ID, 'thumbnail' ),
				'price' => '',
			);
		}
	}

	if ( empty( $results ) ) {

		$no_results = is_woocommerce_activated() ? __( 'No products found.', 'woocommerce' ) : __( 'No matches found', 'flatsome' );

		$suggestions[] = array(
			'id'    => -1,
			'value' => $no_results,
			'url'   => '',
		);
	}

	echo json_encode( array( 'suggestions' => $suggestions ) );
	die();
}

add_action( 'wp_ajax_flatsome_ajax_search_products', 'flatsome_ajax_search' );
add_action( 'wp_ajax_nopriv_flatsome_ajax_search_products', 'flatsome_ajax_search' );
