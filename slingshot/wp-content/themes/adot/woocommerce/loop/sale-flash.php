<?php
/**
 * Product loop sale flash
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $product;

?>
<div class="icon-sale">
	<?php if ( $product->is_on_sale() ) : ?>

		<?php echo apply_filters( 'woocommerce_sale_flash', '<span>' . __( 'S<br />A<br/>L<br />E', 'thim' ) . '</span>', $post, $product ); ?>

	<?php endif; ?>
	<?php
	$product_new = get_post_meta( $post->ID, 'thim_product_new', true );
	$product_hot = get_post_meta( $post->ID, 'thim_product_hot', true );
	if ( $product_new != 'no' && $product_new != '' ) {
		echo '<span class="new">' . __( 'N<br/>E<br/>W', 'thim' ) . '</span>';
	}
	if ( $product_hot != 'no' && $product_hot != '' ) {
		echo '<span class="hot">' . __( 'H<br/>O<br/>T', 'thim' ) . '</span>';
	}
	?>
	<!-- html hot new -->
</div>