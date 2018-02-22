<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @author      WooThemes
 * @package     WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product, $woocommerce_loop;

$related = wc_get_related_products( $product->get_id() );

if ( sizeof( $related ) == 0 ) {
    return;
}

$args = apply_filters( 'woocommerce_related_products_args', array(
    'post_type'           => 'product',
    'ignore_sticky_posts' => 1,
    'no_found_rows'       => 1,
    'posts_per_page'      => $posts_per_page,
    'orderby'             => $orderby,
    'post__in'            => $related,
    'post__not_in'        => array( $product->get_id() )
) );

$products = new WP_Query( $args );

//force grid view
$woocommerce_loop['view'] = 'grid';

if ( $products->have_posts() ) : ?>

    <div class="clearfix related products">

        <h3><?php _e( 'Related Products', 'yit' ); ?></h3>

        <?php woocommerce_product_loop_start(); ?>

        <?php while ( $products->have_posts() ) : $products->the_post(); ?>

            <?php wc_get_template_part( 'content', 'product' ); ?>

        <?php endwhile; // end of the loop. ?>

        <?php woocommerce_product_loop_end(); ?>

    </div>

<?php endif;

wp_reset_postdata();
