<?php
/**
 * Checkout Form
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

global $woocommerce;

wc_print_notices();

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout
if ( ! $checkout->enable_signup && ! $checkout->enable_guest_checkout && ! is_user_logged_in() ) {
    echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'yit' ) );
    return;
}

// filter hook for include new pages inside the payment method
$get_checkout_url = apply_filters( 'woocommerce_get_checkout_url', wc_get_checkout_url() ); ?>

    <form name="checkout" method="post" class="woocommerce-checkout checkout" action="<?php echo esc_url( $get_checkout_url ); ?>">

        <?php if ( sizeof( $checkout->checkout_fields ) > 0 ) : ?>

            <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

            <div class="row" id="customer_details">

                <?php if(is_rtl()): ?>
                    <div class="col-sm-5 border">

                            <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

                            <div id="order_review">
                                <h3 id="order_review_heading"><?php _e( 'Your order', 'yit' ); ?></h3>

                                <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                            </div>

                            <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

                    </div>
                <?php endif ?>


                <div class="col-sm-7 details">

                    <div class="col-1">

                        <?php do_action( 'woocommerce_checkout_billing' ); ?>

                    </div>

                    <div class="col-2">

                        <?php do_action( 'woocommerce_checkout_shipping' ); ?>

                    </div>

                </div>

                <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>


                <?php if(!is_rtl()): ?>
                    <div class="col-sm-5 border">

                            <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

                            <div id="order_review">
                                <h3 id="order_review_heading"><?php _e( 'Your order', 'yit' ); ?></h3>

                                <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                            </div>

                            <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

                    </div>
                <?php endif ?>



            </div>

        <?php endif; ?>

    </form>


<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>