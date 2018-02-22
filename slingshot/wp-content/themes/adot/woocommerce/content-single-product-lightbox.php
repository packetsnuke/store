<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
global $product;

?>
<div id="content" class="quickview woocommerce">
<!--	<script type="text/javascript" src="--><?php //echo TP_THEME_URI; ?><!--js/variation-form.js"></script>-->
	<div itemscope itemtype="http://schema.org/Product" id="product-<?php the_ID(); ?>" <?php post_class( 'row product-info product' ); ?>>
		<?php
		global $post, $woocommerce, $product;
		$attachment_ids = $product->get_gallery_image_ids();

		$suffix               = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$frontend_script_path = WC()->plugin_url() . '/assets/js/frontend/';
		$script_path          = $frontend_script_path . 'add-to-cart' . $suffix . '.js';
		$script_path_2        = $frontend_script_path . 'add-to-cart-variation' . $suffix . '.js';
		?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				jQuery('#slider').flexslider({
					animation    : "slide",
					controlNav   : false,
					animationLoop: false,
					slideshow    : false,
					sync         : "#carousel",
					directionNav : true,//Boolean: Create navigation for previous/next navigation? (true/false)
					prevText     : "",//String: Set the text for the "previous" directionNav item
					nextText     : "",//String: Set the text for the "next" directionNav item
					start        : function (slider) {
						jQuery('body').removeClass('loading');
					}
				});
				jQuery(".woo-share").click(function (e) {
					jQuery('.share_show').slideToggle();
				});
			});
		</script>
		<div class="left col-sm-6">
			<?php
			$product_thumbnail = '';
			if ( has_post_thumbnail() ) {
				$image       = get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ) );
				$image_title = esc_attr( get_the_title( get_post_thumbnail_id() ) );
				$image_link  = wp_get_attachment_url( get_post_thumbnail_id() );


				echo '<div class="images product_variations_image hide">';
				$product_thumbnail = apply_filters( 'woocommerce_single_product_image_html', sprintf( '%s', $image ), $post->ID );
				echo $product_thumbnail;
				echo '</div>';
			}
			?>
			<div id="slider" class="flexslider">
				<ul class="slides">
					<?php
					if ( has_post_thumbnail() && $product_thumbnail ) {
						echo '<li class="main_product_thumbnai">';
						echo $product_thumbnail;
						echo '</li>';
					}
					$attachment_count = count( $product->get_gallery_image_ids() );
					$attachment_ids   = $product->get_gallery_image_ids();
					$loop             = 0;
					foreach ( $attachment_ids as $attachment_id ) {

						$image_link = wp_get_attachment_url( $attachment_id );

						if ( !$image_link ) {
							continue;
						}
						$classes[]   = 'image-' . $attachment_id;
						$image       = wp_get_attachment_image( $attachment_id, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ) );
						$image_class = esc_attr( implode( ' ', $classes ) );
						$image_title = esc_attr( get_the_title( $attachment_id ) );
						echo '<li>';
						echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '%s', $image ), $post->ID );
						echo '</li>';
						$loop ++;
					}
					?>
				</ul>
			</div>
		</div>

		<div class="right col-sm-6">
			<?php
			/**
			 * woocommerce_single_product_summary hook
			 *
			 * @hooked woocommerce_template_single_title - 5
			 * @hooked woocommerce_template_single_price - 10
			 * @hooked woocommerce_template_single_excerpt - 20
			 * @hooked woocommerce_template_single_add_to_cart - 30
			 * @hooked woocommerce_template_single_meta - 40
			 * @hooked woocommerce_template_single_sharing - 50
			 */
			do_action( 'woocommerce_single_product_summary_quick' );
			?>

		</div>
		<div class="clear"></div>
		<?php echo '<a href="' . esc_attr( get_the_permalink( $product->id ) ) . '" target="_top" class="quick-view-detail">' . __( 'View Detail', 'thim' ) . '</a><div class="clear"></div>'; ?>
		<script type="text/javascript" src="<?php echo $script_path_2; ?>"></script>
	</div>
	<!-- #product-<?php the_ID(); ?> -->
</div>
