<?php
/**
 * Single Product Meta
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/meta.php.
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

global $post, $product;

$cat_count = get_the_terms( $post->ID, 'product_cat' ) ;
$cat_count = is_array($cat_count) ? sizeof($cat_count) : 0;

$tag_count = get_the_terms( $post->ID, 'product_tag' );
$tag_count = is_array($tag_count) ? sizeof($tag_count) : 0;

?>
<div class="product_meta">

	<?php do_action( 'woocommerce_product_meta_start' ); ?>

	<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>

        <span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'woocommerce' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'woocommerce' ); ?></span>.</span>

	<?php endif; ?>

	<?php

    if ( $cat_count > 0 ) {
        echo _n( 'Category:', 'Categories:', $cat_count, 'yit' ).' ' . wc_get_product_category_list( $product->get_id(), ', ', '<div class="posted_in">', '.</div>' );
        echo "<div class='clear'></div>";
    }

    if($tag_count > 0){
        echo _n( 'Tag:', 'Tags:', $tag_count, 'yit' ).' '.wc_get_product_tag_list( $product->get_id(), ', ', '<div class="tagged_as">', '.</div>' );
        echo "<div class='clear'></div>";
    }

    do_action( 'woocommerce_product_meta_end' );

    ?>

</div>