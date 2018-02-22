<?php
if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $product, $woocommerce_loop, $theme_options_data;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) ) {
	$woocommerce_loop['loop'] = 0;
}

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) ) {
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
}

// Ensure visibility
if ( !$product || !$product->is_visible() ) {
	return;
}

// show column widget thim product
if ( $column_slider ) {
	$column_product = 12 / $column_slider;
}
$col_lg_20 = '';
if ( $column_slider == 5 ) {
	$col_lg_20 = 'col-lg-20';
}
// Extra post classes
$classes   = array();
$classes[] = 'col-md-' . $column_product . ' col-sm-6 col-xs-6 ' . $col_lg_20 . '';
if ( 0 == ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] || 1 == $woocommerce_loop['columns'] ) {
	$classes[] = 'first';
}
if ( 0 == $woocommerce_loop['loop'] % $woocommerce_loop['columns'] ) {
	$classes[] = 'last';
}
$classes[] = 'product-card';
?>
<li <?php post_class( $classes ); ?>>
	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>
	<div class="wrapper_01">
		<div class="feature-image">
			<?php
			/**
			 * woocommerce_before_shop_loop_item_title hook
			 *
			 * @hooked woocommerce_show_product_loop_sale_flash - 10
			 * @hooked woocommerce_template_loop_product_thumbnail - 10
			 */
			do_action( 'woocommerce_before_shop_loop_item_title' );
			?>
		</div>
		<div class="box-title">
			<div class="title-product">
				<a href="<?php the_permalink(); ?>" class="product_name"><?php the_title(); ?></a>
 			</div>
			<?php
			/**
			 * woocommerce_after_shop_loop_item_title hook
			 * @hooked woocommerce_template_loop_rating - 5
			 * @hooked woocommerce_template_loop_price - 10
			 */
			do_action( 'woocommerce_after_shop_loop_item_title_rating' );

			do_action( 'woocommerce_after_shop_loop_item_title' );
			?>
		</div>
		<div class="clear"></div>
</li>