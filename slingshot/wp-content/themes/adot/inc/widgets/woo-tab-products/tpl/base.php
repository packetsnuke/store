<?php

$rand = time() . '-1-' . rand( 0, 100 );
echo '<ul class="tab-heading" role="tablist">';
//$active = $content_active ='';
$j = $k = 1;
foreach ( $instance['tab'] as $i => $tab ) {
//	$css          = $width_header = '';
//	$width_header = 100 / count( $instance['tab'] );
//	$css          = 'style="width:' . $width_header . '%"';
	if ( $j == '1' ) {
		$active = "class=active";
	} else {
		$active = '';
	}
	echo '<li role="presentation" ' . esc_attr( $active ) . '><a href="#thimm-widget-tab-' . $j . $rand . '" data-toggle="tab">' . esc_attr( $tab['title'] ) . '</a></li>';
	$j ++;
}

echo '</ul>';

echo '<div class="tab-content">';
foreach ( $instance['tab'] as $i => $tab ) {
	count( $instance['tab'] );
	if ( $k == '1' ) {
		$content_active = " active in";
	} else {
		$content_active = '';
	}
	echo ' <div role="tabpanel" class="tab-pane' . esc_attr( $content_active ) . '" id="thimm-widget-tab-' . $k . $rand . '">';
	?>
	<?php
	ob_start();
	extract( $args );
	$number_product = $column = 4;
	$cats           = '';
	$show           = sanitize_title( $tab['show'] );
	$orderby        = sanitize_title( $tab['orderby'] );
	$order          = sanitize_title( $tab['order'] );
	$number_product = sanitize_title( $tab['number_product'] );
	$column         = sanitize_title( $tab['column'] );
	$cats           = sanitize_title( $tab['cats'] );
	$type_show      = sanitize_title( $tab['type-show'] );
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

	if ( ! empty( $instance['hide_free'] ) ) {
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
			$product_ids_on_sale    = wc_get_product_ids_on_sale();
			$product_ids_on_sale[]  = 0;
			$query_args['post__in'] = $product_ids_on_sale;
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

	$r     = new WP_Query( $query_args );
	$class = " category-product-list";
	if ( $r->have_posts() ) {
		if ( $type_show == 'grid' ) {
			echo '<div class="thim-widget-product woocommerce">
			<div class="box thim-module">';
			echo '<ul class="product-grid category-product-list archive_switch' . esc_attr( $class ) . '">';
			while ( $r->have_posts() ) {
				$r->the_post();
				wc_get_template( 'content-widget/content-product.php', array(
					'show_rating' => $show_rating,
					'column'      => $column
				) );
			}
			echo '</ul></div></div>';
		} else {
			$col        = 12 / $column;
			$col_slider = $column;
			echo '<div class="thim-widget-product-slider woocommerce">
			<div class="box thim-module">';
			echo '<div class="owl-carousel owl-theme product-grid category-product-list archive_switch' . esc_attr( $class ) . '" data-column-slider="' . $column . '">';
			$i = $j = 1;
			while ( $r->have_posts() ) :
				$r->the_post();
				if ( $i == 1 ) {
					echo '<ul class="showcase">';
				}
				wc_get_template( 'content-widget/content-product-slider.php', array(
					'show_rating'    => $show_rating,
					'column_slider'  => $column,
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
			echo '     <div class="nav">
					<span class="prev"><i class="fa fa-angle-left"></i></span>
					<span class="next"><i class="fa fa fa-angle-right"></i></span>
				</div>';
			echo '</div></div>';
		}
	}
	wp_reset_postdata();
	$content = ob_get_clean();
	echo ent2ncr( $content );
	?>
	<?php
	echo '</div>';
	$k ++;
}
echo '</div>';
?>