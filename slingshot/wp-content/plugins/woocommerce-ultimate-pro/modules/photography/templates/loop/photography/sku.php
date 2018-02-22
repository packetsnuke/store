<?php
/**
 * Photography loop SKU.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $product;

?>
<p class="photography-sku">
	<?php if ( wc_product_sku_enabled() && $product->get_sku() ) : ?>

		<span class="sku_wrapper"><?php _e( 'SKU:', 'ultimatewoo-pro' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : __( 'N/A', 'ultimatewoo-pro' ); ?></span></span>

	<?php endif; ?>
</p>
