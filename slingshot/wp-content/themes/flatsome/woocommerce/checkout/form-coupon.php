<?php
/**
 * Checkout coupon form
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! wc_coupons_enabled() ) {
	return;
}

$info_message = apply_filters( 'woocommerce_checkout_coupon_message', __( 'Have a coupon?', 'woocommerce' ) . ' <a href="#" class="showcoupon">' . __( 'Click here to enter your code', 'woocommerce' ) . '</a>' );
wc_print_notice( '<span class="widget-title"><i class="icon-tag"></i></span>' . $info_message, 'notice' );
?>

<form class="checkout_coupon has-border is-dashed" method="post" style="display:none">
	<div class="coupon">
		<div class="flex-row medium-flex-wrap">
			<div class="flex-col flex-grow">
				<input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" id="coupon_code" value="" />
			</div>
			<div class="flex-col">
				<button type="submit" class="button expand" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
			</div>
		</div><!-- row -->
	</div><!-- coupon -->
</form>
