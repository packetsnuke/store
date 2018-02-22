<?php
/**
 * Single Product Image
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       3.1.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

global $post, $woocommerce, $product;

if ( $product->get_gallery_image_ids() ) {
	$has_thumb = " has-thumb";
} else {
	$has_thumb = "";
}

global $theme_options_data;

wp_enqueue_script( 'thim-flexslider' );

// Zoom out product image
if ( isset( $theme_options_data['thim_woo_set_effect'] ) && $theme_options_data['thim_woo_set_effect'] == 'zoom_out' ) {
	wp_enqueue_script( 'thim-retina' );
}
?>
<?php
$product_thumbnail = '';
if ( has_post_thumbnail() ) {
	$image_title      = esc_attr( get_the_title( get_post_thumbnail_id() ) );
	$image_link       = wp_get_attachment_url( get_post_thumbnail_id() );
	$image            = get_the_post_thumbnail( $post->ID, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ), array(
		'title' => $image_title
	) );
	$attachment_count = count( $product->get_gallery_image_ids() );

	if ( $attachment_count > 0 ) {
		$gallery = '[product-gallery]';
	} else {
		$gallery = '';
	}

	list( $magnifier_url, $magnifier_width, $magnifier_height ) = wp_get_attachment_image_src( get_post_thumbnail_id(), "shop_single" );

	$product_variations_thumbnail = '';
	echo '<div class="images product_variations_image hide">';
	if ( isset( $theme_options_data['thim_woo_set_effect'] ) && $theme_options_data['thim_woo_set_effect'] == 'zoom_out' ) {
		$product_thumbnail            = apply_filters( 'woocommerce_single_product_image_html', sprintf( '<a href="%s" itemprop="image" class="retina" title="%s" style="">%s</a>', esc_url( $image_link ), esc_attr( $image_title ), $image ), $post->ID );
		$product_variations_thumbnail = apply_filters( 'woocommerce_single_product_image_html', sprintf( '<a href="%s" itemprop="image" title="%s" style="">%s</a>', esc_url( $image_link ), esc_attr( $image_title ), $image ), $post->ID );
	} else {
		$product_thumbnail            = apply_filters( 'woocommerce_single_product_image_html', sprintf( '<a href="%s" itemprop="image" class="woocommerce-main-image zoom" title="%s" data-rel="prettyPhoto' . $gallery . '">%s<span class="glass-wrapper"><i class="fa fa-search"></i></span></a>', esc_url( $image_link ), esc_attr( $image_title ), $image ), $post->ID );
		$product_variations_thumbnail = apply_filters( 'woocommerce_single_product_image_html', sprintf( '<a href="%s" itemprop="image" title="%s" style="">%s</a>', esc_url( $image_link ), esc_attr( $image_title ), $image ), $post->ID );
	}
	echo $product_variations_thumbnail;
	echo '</div>';
}

?>
<!-- Place somewhere in the <body> of your page -->
<div id="slider" class="flexslider">
	<ul class="slides">
		<?php
		if ( has_post_thumbnail() && $product_thumbnail ) {
			echo '<li class="main_product_thumbnai">';
			echo $product_thumbnail;
			echo '</li>';
		}

		$attachment_ids = $product->get_gallery_image_ids();
		$loop           = 0;
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
			if ( isset( $theme_options_data['thim_woo_set_effect'] ) && $theme_options_data['thim_woo_set_effect'] == 'zoom_out' ) {
				echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<a href="%s" itemprop="image" class="retina" title="%s" style="">%s</a>', esc_url( $image_link ), esc_attr( $image_title ), $image ), $post->ID );
			} else {
				echo apply_filters( 'woocommerce_single_product_image_html', sprintf( '<a href="%s" itemprop="image" class="woocommerce-main-image zoom" title="%s" data-rel="prettyPhoto' . $gallery . '">%s<span class="glass-wrapper"><i class="fa fa-search"></i></span></a>', esc_url( $image_link ), esc_attr( $image_title ), $image ), $post->ID );
			}
			echo '</li>';
			$loop ++;
		}

		?>
	</ul>
</div>

