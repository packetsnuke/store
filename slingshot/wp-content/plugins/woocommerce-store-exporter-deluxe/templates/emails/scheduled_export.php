<?php if( !defined( 'ABSPATH' ) ) exit; ?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php echo __( 'Hi', 'woocommerce-exporter' ); ?> <?php echo $recipient_name; ?>,</p>

<?php echo $email_contents; ?>

<?php do_action( 'woocommerce_email_footer' ); ?>