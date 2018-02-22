<?php
/**
 * Single Product Up-Sells
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/up-sells.php.
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

global $product, $woocommerce, $woocommerce_loop;

$upsells = $product->get_upsell_ids();

if ( sizeof( $upsells ) == 0 ) {
    return;
}

$meta_query = array();
$meta_query[] = $woocommerce->query->visibility_meta_query();
$meta_query[] = $woocommerce->query->stock_status_meta_query();

$args = array(
    'post_type'           => 'product',
    'ignore_sticky_posts' => 1,
    'no_found_rows'       => 1,
    'posts_per_page'      => $posts_per_page,
    'orderby'             => $orderby,
    'post__in'            => $upsells,
    'post__not_in'        => array( $product->get_id() ),
    'meta_query'          => $meta_query
);

//force grid view
$woocommerce_loop['view'] = 'grid';

$products = new WP_Query( $args );

if ( $products->have_posts() ) : ?>

    <div class="clearfix upsells products">

        <h3><?php _e( 'You may also like&hellip;', 'yit' ) ?></h3>

        <?php woocommerce_product_loop_start(); ?>

            <?php while ( $products->have_posts() ) : $products->the_post(); ?>

                <?php wc_get_template_part( 'content', 'product' ); ?>

            <?php endwhile; // end of the loop. ?>

        <?php woocommerce_product_loop_end(); ?>

    </div>

    <div class="clear"></div>
    
<?php endif;

wp_reset_postdata();
