<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php echo sprintf( __( "To redeem your store credit use the following code during checkout:", 'ultimatewoo-pro' ), $blogname ); ?></p>

<strong style="margin: 10px 0; font-size: 4em; line-height: 1.2em; font-weight: bold; display: block; text-align: center;"><?php echo $coupon_code; ?></strong>

<div style="clear:both;"></div>

<?php do_action( 'woocommerce_email_footer' ); ?>