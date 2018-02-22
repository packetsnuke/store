<?php
/**
 * Function Sub word
 *
 * @param        $str     String
 * @param        $txt_len Number is cut
 * @param string $end_txt string at the end paragraph
 *
 * @return string
 */
function substr_words( $str, $txt_len, $end_txt = '...' ) {
	$words   = explode( ' ', $str );
	$new_str = '';
	foreach ( $words as $k => $val ) {
		if ( $k < $txt_len ) {
			$new_str .= $val . ' ';
		}
	}
	$new_str = rtrim( $new_str, ' ,.;:' );
	$new_str .= $end_txt;

	return $new_str;
}

function result_search_callback() {
	ob_start();
	function thim_search_title_filter( $where, &$wp_query ) {
		global $wpdb;
		if ( $keyword = $wp_query->get( 'search_prod_title' ) ) {
			$where .= ' AND ' .  esc_sql($wpdb->posts) . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $keyword ) ) . '%\'';
		}

		return $where;
	}

	$keyword = $_REQUEST['keyword'];

	if ( $keyword ) {
		$search_query = array(
			'search_prod_title' => $keyword,
			'order'             => 'DESC',
			'orderby'           => 'date',
			'post_status'       => 'publish',
			'post_type'         => array(
				'post', 'page', 'product'
			),
		);
		add_filter( 'posts_where', 'thim_search_title_filter', 10, 2 );
		$search = new WP_Query( $search_query );
		remove_filter( 'posts_where', 'thim_search_title_filter', 10, 2 );

		$newdata = array();
		if ( $search ) {
			foreach ( $search->posts as $post ) {
				$newdata[] = array(
					'id'        => esc_attr($post->ID),
					'title'     => esc_attr($post->post_title),
					'guid'      => esc_url(get_permalink( $post->ID )),
					'date'      => mysql2date( 'M d Y', $post->post_date ),
					'thumbnail' => get_the_post_thumbnail( $post->ID, 'thumbnail' ),
					'shortdesc' => $post->post_content ? substr_words( strip_shortcodes( $post->post_content ), 20, '...' ) : ''
				);
			}
		}

		ob_end_clean();
		echo json_encode( $newdata );
	}
	die(); // this is required to return a proper result
}

function ob_ajax_url() {
	echo '<script type="text/javascript">
		var ob_ajax_url ="' . get_site_url() . '/wp-admin/admin-ajax.php";
		</script>';
}

add_action( 'wp_ajax_nopriv_result_search', 'result_search_callback' );
add_action( 'wp_ajax_result_search', 'result_search_callback' );
add_action( 'wp_print_scripts', 'ob_ajax_url' );



// search category product
/* Product Search */
function aloxo_product_search_callback() {
	$search_keyword = $_REQUEST['keyword'];
	$cate = $_REQUEST['cate'];

	if ($cate && $cate != "-1") {
		$category = explode( ',', $cate );
	}else $category = "";

	$data = array();
	$data['success'] = true;

	global $woocommerce;

	$ordering_args = $woocommerce->query->get_catalog_ordering_args( 'title', 'asc' );
	$products = array();

	$args = array(
		's'                     => $search_keyword,
		'post_type'             => 'product',
		'post_status'           => 'publish',
		'ignore_sticky_posts'   => 1,
		'orderby'               => $ordering_args['orderby'],
		'order'                 => $ordering_args['order'],
		'posts_per_page'        => -1,
		'meta_query'            => array(
			array(
				'key'           => '_visibility',
				'value'         => array('catalog', 'visible'),
				'compare'       => 'IN'
			)
		)
	);

	if( isset( $category) && $category ){
		$args['tax_query'] = array(
			//'relation' => 'AND',
			array(
				'taxonomy' => 'product_cat',
				'field' => 'slug',
				'terms' => $category
			));
	}

	$products_query = new WP_Query( $args );
	if ( $products_query->have_posts() ) {
		while ( $products_query->have_posts() ) {
			$products_query->the_post();

			//display product thumbnail
			if (has_post_thumbnail()) {
				$image_src = wp_get_attachment_image_src( get_post_thumbnail_id(),'thumbnail' );
				$thumb = '<img src="' . $image_src[0] . '" width="140" alt="" />';
			}
			else {
				$thumb = '<img src="/images/defaul_image.jpg" width="140" alt=" />';
			}
			//add this code bellow, inside loop
			ob_start();
			woocommerce_get_template( 'loop/price.php' );
			$price = ob_get_clean();

			ob_start();
			woocommerce_get_template( 'loop/rating.php' );
			$rate = ob_get_clean();


			$products[] = array(
				'id' => get_the_ID(),
				'value' => get_the_title(),
				'url' => get_permalink(),
				'thumb'=> $thumb,
				'price'=> $price,
				'rate'=> $rate,
			);
		}
	} else {
		$products[] = array(
			'id' => -1,
			'value' => __('No results', 'yit'),
			'url' => ''
		);
	}
	wp_reset_postdata();

	echo json_encode( $products );
	die();
}
add_action( 'wp_ajax_nopriv_product_search', 'aloxo_product_search_callback' );
add_action( 'wp_ajax_product_search', 'aloxo_product_search_callback' );
/* End Product Search */

add_action( 'wp_enqueue_scripts', 'thim_search_scripts' );
function thim_search_scripts() {
	wp_enqueue_script( 'search', TP_THEME_URI . 'inc/widgets/thim-search/js/search.js', array( 'jquery' ), '', true );
	wp_enqueue_script( 'result-search', TP_THEME_URI . 'inc/widgets/thim-search/js/result-search.js', array( 'jquery' ), '', true );

}