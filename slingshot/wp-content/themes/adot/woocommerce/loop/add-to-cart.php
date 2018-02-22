<?php
/**
 * Loop Add to Cart
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       3.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

global $product, $post, $theme_options_data;
echo '<div class="product-options">';
echo apply_filters( 'woocommerce_loop_add_to_cart_link',
	sprintf( '<div class="cart"><a rel="nofollow" href="%s" data-quantity="%s" data-product_id="%s" data-product_sku="%s" class="%s">%s</a></div>',
		esc_url( $product->add_to_cart_url() ),
		esc_attr( isset( $quantity ) ? $quantity : 1 ),
		esc_attr( $product->get_id() ),
		esc_attr( $product->get_sku() ),
		esc_attr( isset( $class ) ? $class : 'button' ),
		esc_html( $product->add_to_cart_text() )
	),
	$product );

if ( isset( $theme_options_data['thim_woo_set_show_wishlist'] ) && $theme_options_data['thim_woo_set_show_wishlist'] == '1' ) {
	if ( class_exists( 'YITH_WCWL' ) ) {
		echo '<div class="wishlist">' . do_shortcode( '[yith_wcwl_add_to_wishlist]' ) . '</div>';
	}
}
echo '</div>';
?>