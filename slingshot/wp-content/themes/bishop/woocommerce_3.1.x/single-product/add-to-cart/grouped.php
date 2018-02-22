<?php
/**
 * Grouped product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/grouped.php.
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
 * @version     3.0.7
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product, $post;

$parent_product_post = $post;

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="cart" method="post" enctype='multipart/form-data'>
	<table cellspacing="0" class="group_table">
		<tbody>
		<?php
		foreach ( $grouped_products as $product_id ) :
			$product = wc_get_product( $product_id );
			$post    = $product->post;
			setup_postdata( $post );
			?>
			<tr>
				<td>
					<?php if ( $product->is_sold_individually() || ! $product->is_purchasable() ) : ?>
						<?php woocommerce_template_loop_add_to_cart(); ?>
					<?php else : ?>
						<?php
						$quantites_required = true;
						/**
						 * @since 3.0.0.
						 */
						do_action( 'woocommerce_before_add_to_cart_quantity' );

						woocommerce_quantity_input( array(
							'input_name'  => 'quantity[' . $grouped_product->get_id() . ']',
							'input_value' => isset( $_POST['quantity'][ $grouped_product->get_id() ] ) ? wc_stock_amount( $_POST['quantity'][ $grouped_product->get_id() ] ) : 0,
							'min_value'   => apply_filters( 'woocommerce_quantity_input_min', 0, $grouped_product ),
							'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $grouped_product->get_max_purchase_quantity(), $grouped_product ),
						) );

						/**
						 * @since 3.0.0.
						 */
						do_action( 'woocommerce_after_add_to_cart_quantity' );
						?>
					<?php endif; ?>
				</td>

				<td class="label">
					<label for="product-<?php echo $product_id; ?>">
						<?php echo $product->is_visible() ? '<a href="' . get_permalink() . '">' . get_the_title() . '</a>' : get_the_title(); ?>
					</label>
				</td>

				<?php do_action ( 'woocommerce_grouped_product_list_before_price', $product ); ?>

				<td class="price">
					<?php
					echo $product->get_price_html();

					if ( ( $availability = $product->get_availability() ) && $availability['availability'] )
						echo apply_filters( 'woocommerce_stock_html', '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>', $availability['availability'] , $product );
					?>
				</td>
			</tr>
		<?php
		endforeach;

		// Reset to parent grouped product
		$post    = $parent_product_post;
		$product = wc_get_product( $parent_product_post->ID );
		setup_postdata( $parent_product_post );
		?>
		</tbody>
	</table>

	<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />

	<?php if ( $quantites_required ) : ?>

		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<button type="submit" class="single_add_to_cart_button btn btn-alternative"><?php echo apply_filters( 'add_to_cart_text' , $product->single_add_to_cart_text() ); ?></button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<?php endif; ?>
</form>

<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
