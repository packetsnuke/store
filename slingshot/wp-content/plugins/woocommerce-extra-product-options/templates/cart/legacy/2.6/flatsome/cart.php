<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $flatsome_opt;

wc_print_notices();
?>

<?php do_action( 'woocommerce_before_cart' ); ?>

<form action="<?php echo esc_url( (function_exists('wc_get_cart_url')?wc_get_cart_url():WC()->cart->get_cart_url()) ); ?>" method="post">


<div class="row">
<div class="large-8 small-12 columns">

<?php do_action( 'woocommerce_before_cart_table' ); ?>
<?php do_action( 'woocommerce_before_cart_contents' ); ?>

<div class="cart-wrapper">
	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
	<thead>
		<tr>
			<th class="product-name" colspan="3"><?php esc_attr_e( 'Product', 'woocommerce' ); ?></th>
			<th class="product-price"><?php esc_attr_e( 'Price', 'woocommerce' ); ?></th>
			<th class="product-quantity"><?php esc_attr_e( 'Quantity', 'woocommerce' ); ?></th>
			<th class="product-subtotal"><?php esc_attr_e( 'Total', 'woocommerce' ); ?></th>
			<?php
				do_action( 'tm_woocommerce_cart_after_column_header');
			?>
		</tr>
	</thead>

	<tbody>

		<?php
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
			$variation_id = $cart_item['variation_id'];
			if (empty($variation_id)){
				$variation_id = $product_id;
			}
			$original_product = wc_get_product($variation_id);
			
			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				?>
					<tr class="tm-epo-cart-row-product woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

						<td class="product-remove">
							<?php
								echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
									'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
									esc_url( WC()->cart->get_remove_url( $cart_item_key ) ),
									esc_attr__( 'Remove this item', 'woocommerce' ),
									esc_attr( $product_id ),
									esc_attr( $_product->get_sku() )
								), $cart_item_key );
							?>
						</td>

						<td class="product-thumbnail">
							<?php
								$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

								if ( ! $product_permalink ) {
									echo $thumbnail;
								} else {
									printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
								}
							?>
						</td>

						<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
							<?php
								if ( ! $product_permalink ) {
									echo apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ) . '&nbsp;';
								} else {
									echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_title() ), $cart_item, $cart_item_key );
								}

								// Meta data
								echo WC()->cart->get_item_data( $cart_item );

								// Backorder notification
								if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
									echo '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>';
								}
							?>
						</td>

						<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
						<?php
							if (TM_EPO()->tm_epo_hide_options_in_cart=="normal"){
								echo apply_filters( 'wc_tm_epo_ac_product_price', 
									apply_filters( 'woocommerce_cart_item_price', 
										TM_EPO()->get_price_for_cart( $cart_item['tm_epo_product_original_price'], $cart_item, ""), 
										$cart_item, 
										$cart_item_key 
									), 
									$cart_item_key, 
									$cart_item, 
									$original_product, 
									$product_id );
							}else{
								echo apply_filters( 'wc_tm_epo_ac_product_price', 
										apply_filters( 'woocommerce_cart_item_price', 
											WC()->cart->get_product_price( $_product ), 
											$cart_item, 
											$cart_item_key 
										), 
										$cart_item_key, 
										$cart_item, 
										$_product, 
										$product_id );
							}							
						?>
					</td>

						<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
						<?php 
							if ( empty($cart_item["tmcartepo"]) && empty($cart_item["tmsubscriptionfee"]) ){//tmcartfee
								if ( $_product->is_sold_individually() ) {
									$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
								} else {
									$product_quantity = woocommerce_quantity_input( array(
										'input_name'  => "cart[{$cart_item_key}][qty]",
										'input_value' => $cart_item['quantity'],
										'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
										'min_value'   => '0',
									), $_product, false );
								}

							echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
							}else{
								if ( $_product->is_sold_individually() ) {
									echo apply_filters( 'wc_tm_epo_ac_product_qty', '1', $cart_item_key, $cart_item, $_product, $product_id ) ;
								}else{
									echo apply_filters( 'wc_tm_epo_ac_product_qty', $cart_item['quantity'], $cart_item_key, $cart_item, $_product, $product_id ) ;
								}
							}
						?>
					</td>

						<td class="product-subtotal" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
						<?php 
							if (TM_EPO()->tm_epo_cart_field_display=="advanced"){
								if (TM_EPO()->tm_epo_hide_options_in_cart=="normal"){
									if (isset($cart_item['tm_epo_product_after_adjustment']) && TM_EPO()->tm_epo_dpd_enable=="no"){
										$price = $cart_item['tm_epo_product_after_adjustment'];
									}else{
										$price=apply_filters('wc_epo_discounted_price', $cart_item['tm_epo_product_original_price'], wc_get_product(tc_get_id($cart_item['data'])), $cart_item_key);
									}
									$price=$price*$cart_item['quantity'];
									echo apply_filters( 'woocommerce_cart_item_subtotal', 
											TM_EPO()->get_price_for_cart( $price,$cart_item,""), 
											$cart_item, 
											$cart_item_key );
								}else{
									echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
								}
							}else{
								echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $original_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
							}
						?>
					</td>
					<?php 
					do_action( 'tm_woocommerce_cart_after_column', $cart_item_key, $cart_item, $_product, $product_id );
					?>
				</tr>
				<?php 
				do_action( 'tm_woocommerce_cart_after_row', $cart_item_key, $cart_item, $_product, $product_id );
			}
		}

		do_action( 'woocommerce_cart_contents' );
		?>

	</tbody>

</table>
<?php do_action( 'woocommerce_after_cart_contents' ); ?>

<?php do_action('woocommerce_cart_collaterals'); ?>


</div><!-- .cart-wrapper -->
</div><!-- .large-8 -->



<div class="large-4 small-12 columns">
	<div class="cart-sidebar actions">

		<?php woocommerce_cart_totals(); ?>

		<input type="submit" class="button expand" name="update_cart" value="<?php esc_attr_e( 'Update Cart', 'woocommerce' ); ?>" /> 
		<input type="submit" class="checkout-button secondary expand button" name="proceed" value="<?php _e( 'Proceed to Checkout', 'woocommerce' ); ?>" />

		<?php wp_nonce_field( 'woocommerce-cart' ); ?>

		<?php if ( WC()->cart->coupons_enabled() ) { ?>
		<div class="coupon">
			<h3 class="widget-title"><?php esc_attr_e( 'Coupon', 'woocommerce' ); ?></h3>
			<input type="text" name="coupon_code"  id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Enter Coupon', 'flatsome' ); ?>"/> 
			<input type="submit" class="button small expand" name="apply_coupon" value="<?php esc_attr_e( 'Apply Coupon', 'woocommerce' ); ?>" />
			<?php do_action('woocommerce_cart_coupon'); ?>

		</div>
		<?php } ?>


	</div><!-- .cart-sidebar -->

</div><!-- .large-4 -->
</div><!-- .row -->

<?php do_action( 'woocommerce_after_cart_table' ); ?>

</form>

<?php do_action( 'woocommerce_after_cart' ); ?>