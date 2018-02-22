<?php
/**
 * Waitlist Mailout email (plain text)
 *
 * @author		Neil Pie
 * @package		WooCommerce_Waitlist/Templates/Emails/Plain
 * @version		1.2
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

echo $email_heading . "\n\n";

echo _x( "Hi There,", 'Email salutation', 'ultimatewoo-pro' ) . "\n\n";

echo sprintf( __( '%1$s is now back in stock at %2$s.', 'ultimatewoo-pro' ), $product_title, get_bloginfo( 'title' ) ) . " ";
echo __('You have been sent this email because your email address was registered on a waitlist for this product.', 'ultimatewoo-pro') . "\n\n";
echo sprintf( __( 'If you would like to purchase %1$s please visit the following link: <a href="%2$s">%3$s</a>', 'ultimatewoo-pro' ), $product_title, $product_link, $product_link  );

if ( WooCommerce_Waitlist_Plugin::persistent_waitlists_are_disabled( $product_id ) )
	echo __("\n\nYou have now been removed from the waitlist for this product.", 'ultimatewoo-pro');

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );