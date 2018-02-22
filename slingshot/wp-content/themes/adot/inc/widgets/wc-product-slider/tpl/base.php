<?php
/**
 * Created by lucky boy.
 * User: dong-it
 */
ob_start();
$number_product = $column_slider = 4;
$cats           = '';
$show           = $instance['product_slider_show'];
$orderby        = $instance['orderby'];
$order          = $instance['order'];
$number_product = $instance['number_product'];
$title          = $instance['title'];
$description    = $instance['description_product'];
$column_slider  = $instance['column_slider'];
$row_slider     = $instance['row_slider'];
$cats           = $instance['product_slider_cats'];
$style_nav      = $instance['style_nav'];
$show_rating    = true;

$query_args = array(
	'posts_per_page' => $number_product,
	'post_status'    => 'publish',
	'post_type'      => 'product',
	'no_found_rows'  => 1,
	'order'          => $order == 'asc' ? 'asc' : 'desc'
);

$query_args['meta_query'] = array();

if ( empty( $instance['show_hidden'] ) ) {
	$query_args['meta_query'][] = WC()->query->visibility_meta_query();
	$query_args['post_parent']  = 0;
}

if ( !empty( $instance['hide_free'] ) ) {
	$query_args['meta_query'][] = array(
		'key'     => '_price',
		'value'   => 0,
		'compare' => '>',
		'type'    => 'DECIMAL',
	);
}

$query_args['meta_query'][] = WC()->query->stock_status_meta_query();
$query_args['meta_query']   = array_filter( $query_args['meta_query'] );
if ( $show == 'category' && $cats <> '' ) {
	$cats_id                 = explode( ',', $cats );
	$query_args['tax_query'] = array(
		array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $cats_id
		)
	);
}
switch ( $show ) {
	case 'featured' :
		$query_args['meta_query'][] = array(
			'key'   => '_featured',
			'value' => 'yes'
		);
		break;
	case 'onsale' :
		add_filter( 'posts_clauses', array( WC()->query, 'order_by_rating_post_clauses' ) );
		$product_ids_on_sale    = wc_get_product_ids_on_sale();
		$product_ids_on_sale[]  = 0;
		$query_args['post__in'] = $product_ids_on_sale;
		break;
	case 'top_rated' :
		$query_args               = array( 'posts_per_page' => $number_product, 'no_found_rows' => 1, 'post_status' => 'publish', 'post_type' => 'product' );
		$query_args['meta_query'] = WC()->query->get_meta_query();
		break;
	case 'bestsellers':
		$query_args['meta_query'][] = array(
			'meta_key'      => 'total_sales',
			'orderby'       => 'meta_value_num',
			'no_found_rows' => 1,
		);
		break;
}

switch ( $orderby ) {
	case 'price' :
		$query_args['meta_key'] = '_price';
		$query_args['orderby']  = 'meta_value_num';
		break;
	case 'rand' :
		$query_args['orderby'] = 'rand';
		break;
	case 'sales' :
		$query_args['meta_key'] = 'total_sales';
		$query_args['orderby']  = 'meta_value_num';
		break;
	default :
		$query_args['orderby'] = 'date';
}

if ( ( $style_nav == 'pagination' ) || ( $style_nav == 'all' ) ) {
	$style_control = 'yes';
} else {
	$style_control = 'no';
}
$margin_bottom = $instance['margin-bottom'];
if ( $margin_bottom == '' ) {
	$style_title = '';
} else {
	$style_title = 'style="margin-bottom:' . $margin_bottom . 'px;"';
}
$r = new WP_Query( $query_args );
if ( $r->have_posts() ) {
	echo '<div class="thim-widget-product-slider thim-product-slider woocommerce ' . $style_control . '">';
	if ( $title ) {
		echo '<div ' . $style_title . ' class="box-heading">';
		echo '<span>' . esc_attr( $title ) . '</span></div>';
	}
	if ( $description ) {
		echo '<div class="description-product">
			<p>' . $description . '</p></div>';
	}
	echo '<div class="box thim-module">';
	$col        = 12 / $column_slider;
	$col_slider = $column_slider * $row_slider;
	echo '<div class="owl-carousel owl-theme product-grid category-product-list archive_switch' . $class . '" data-column-slider="' . $column_slider . '" data-row-slider="' . $row_slider . '" data-pagination="' . $style_control . '">';
	$i = $j = 1;
	while ( $r->have_posts() ) :
		$r->the_post();
		if ( $i == 1 ) {
			echo '<ul class="showcase">';
		}
		wc_get_template( 'content-widget/content-product-slider.php', array(
			'show_rating'    => $show_rating,
			'column_slider'  => $column_slider,
			'count_product'  => $i,
			'number_product' => $number_product
		) );
		if ( ( $i % $col_slider ) == 0 || $j == $number_product ) {
			echo '</ul>';
			$i = 0;
		}
		$i ++;
		$j ++;

	endwhile;
	echo '</div>';
	if ( ( $style_nav == 'nav' ) || ( $style_nav == 'all' ) ) {
		echo '     <div class="nav">
					<span class="prev"><i class="fa fa-angle-left"></i></span>
					<span class="next"><i class="fa fa fa-angle-right"></i></span>
				</div>';
	}
	echo '</div></div>';
}
if($show =='top_rated'){
	remove_filter( 'posts_clauses', array( WC()->query, 'order_by_rating_post_clauses' ) );
}
wp_reset_postdata();
$content = ob_get_clean();
echo ent2ncr( $content );
?>