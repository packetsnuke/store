<?php
/**
 * Simple product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/simple.php.
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
 * @version     3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $woocommerce, $product;

if ( ! $product->is_purchasable() ) return;
?>

<?php
// Availability
$availability = $product->get_availability();

if ($availability['availability'])
    echo apply_filters( 'woocommerce_stock_html', '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>', $availability['availability'] , $product );
?>

<?php if ( $product->is_in_stock() ) : ?>

    <?php do_action('woocommerce_before_add_to_cart_form'); ?>

    <form class="cart" method="post" enctype='multipart/form-data'>
        <?php do_action('woocommerce_before_add_to_cart_button'); ?>

        <?php
        if ( ! $product->is_sold_individually() ) : ?>

            <h4 class="quantity_label"><?php _e('Quantity: ', 'yit' ) ?></h4>
            
            <?php
            /**
             * @since 3.0.0.
             */
            do_action( 'woocommerce_before_add_to_cart_quantity' );

            woocommerce_quantity_input( array(
                'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $product->get_min_purchase_quantity(),
            ) );

            /**
             * @since 3.0.0.
             */
            do_action( 'woocommerce_after_add_to_cart_quantity' );

        endif;
        ?>

        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />

        <?php
        //compatibility fix with Gravity Forms Product Addon
        if ( class_exists('WC_GFPA_Display')) {
            ?>
            <button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
        <?php
        } else {
            ?>
            <button type="submit" class="single_add_to_cart_button btn btn-alternative"><?php echo apply_filters( 'add_to_cart_text' , $product->single_add_to_cart_text() ); ?></button>
        <?php
        } ?>


        <?php do_action('woocommerce_after_add_to_cart_button'); ?>
    </form>

    <?php do_action('woocommerce_after_add_to_cart_form'); ?>

<?php endif; ?>