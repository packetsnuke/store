<?php
/**
 * The template for displaying product content within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-widget-product.php
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       2.5.0
 */
global $product; ?>
<li>
	<div class="wrapper">
		<div class="thumb">
			<a href="<?php echo esc_url( get_permalink( $product->id ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>">
				<?php echo ent2ncr($product->get_image()); ?>
			</a>
			<div class="thumb__product_info">
				<div class="name">
					<a href="<?php echo esc_url( get_permalink( $product->id ) ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>"><?php echo  esc_attr($product->get_title()); ?></a>
				</div>
				<?php if ( !empty( $show_rating ) ) {
					echo ent2ncr($product->get_rating_html());
				} ?>
				<?php echo '<div class="price">' .$product->get_price_html().'</div>'; ?>
			</div>
		</div>
	</div>
</li>

