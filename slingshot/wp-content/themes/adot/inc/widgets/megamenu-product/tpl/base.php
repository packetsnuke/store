<?php
ob_start();
$number_product = $column = 4;
$cats           = '';
$orderby        = $instance['orderby'];
$order          = $instance['order'];
$number_product = $instance['number_product'];
$column         = $instance['column'];
$cats         = $instance['cats'];


$show_rating = true;
$query_args  = array(
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

if ( $cats <> '' ) {
	$cats_id                 = explode( ',', $cats );
}
echo '<div class="row"><div class="col-sm-2">';
for($i=0; $i < count($cats_id); $i++){
	$term = get_term( $cats_id[$i], 'product_cat' );
	//echo get_term_link( $term,'product_cat' );
	echo '<div class="list-category"><a href="'. get_term_link( $term,'product_cat' ).'" data-cat="'.$cats_id[$i].'">'. $term->name.'</a></div>';
}
echo '</div>';
echo '<div class="col-sm-10">';
for($i=0; $i < count($cats_id); $i++){
	$query_args['tax_query'] = array(
		array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $cats_id[$i]
		)
	);
	switch ( $orderby ) {
		case 'price' :
			$query_args['meta_key'] = '_price';
			$query_args['orderby']  = 'meta_value_num';
			break;
		case 'rand' :
			if ( $instance['paging_load'] == 'button_paging' ) {
				$query_args['orderby'] = 'date';
			} else {
				$query_args['orderby'] = 'rand';
			}
			break;
		case 'sales' :
			$query_args['meta_key'] = 'total_sales';
			$query_args['orderby']  = 'meta_value_num';
			break;
		default :
			$query_args['orderby'] = 'date';
	}
	$r = new WP_Query( $query_args );
	if ( $r->have_posts() ) {
		echo '<div class="thim-widget-product woocommerce" data-cat="'.$cats_id[$i].'">';
		echo '<ul class="product-grid category-product-list">';
		while ( $r->have_posts() ) {
			$r->the_post();
			$column_product = 12 / $column;
			$classes = array();
			$classes[] = 'col-md-' . $column_product . ' col-sm-6 col-xs-6 ' . $col_lg_20 . '';
			?>

			<li <?php post_class( $classes ); ?>>
				<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>
				<div class="wrapper_widget">
					<div class="feature-image col-md-3 col-sm-6">
						<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
						<?php
						do_action( 'woocommerce_before_shop_loop_item_title' );
						?>
						</a>
					</div>
					<div class="stats col-sm-9">
						<div class="stats-container">
							<div class="box-title">
								<div class="title-product">
									<a href="<?php the_permalink(); ?>" class="product_name"><?php the_title(); ?></a>
									<?php
									/** overwrite
									 * woocommerce_after_shop_loop_item_title hook
									 * @hooked woocommerce_template_loop_rating - 5
									 * @hooked woocommerce_template_loop_price - 10
									 */
									do_action( 'woocommerce_after_shop_loop_item_title_rating' );
									?>
								</div>
								<?php
								/**
								 * woocommerce_after_shop_loop_item_title hook
								 * @hooked woocommerce_template_loop_rating - 5
								 * @hooked woocommerce_template_loop_price - 10
								 */
								do_action( 'woocommerce_after_shop_loop_item_title' );
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="clear"></div>
			</li>
	<?php	}
		echo '</ul>';
		echo '</div>';
	}
	wp_reset_postdata();
}
echo '</div></div>';

$content = ob_get_clean();
echo ent2ncr( $content );
?>