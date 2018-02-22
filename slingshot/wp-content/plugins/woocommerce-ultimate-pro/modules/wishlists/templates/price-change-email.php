<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php printf( __( "Price drop alert from %s. Items below have been reduced in price:", 'ultimatewoo-pro' ), get_option( 'blogname' ) ); ?></p>


<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #eee;" border="1" bordercolor="#eee">
    <thead>
    <tr>
        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Product', 'woocommerce' ); ?></th>
        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Was', 'woocommerce' ); ?></th>
        <th scope="col" style="text-align:left; border: 1px solid #eee;"><?php _e( 'Now', 'woocommerce' ); ?></th>
    </tr>
    </thead>
    <tbody>
    <tr>
		<?php foreach ( $changes as $change ) : ?>
            <td>
                <a href="<?php echo $change['url']; ?>"><?php echo $change['title']; ?></a>
            </td>
            <td>
				<?php echo wc_price( $change['old_price'] ); ?>
            </td>
            <td>
				<?php echo wc_price( $change['new_price'] ); ?>
            </td>
		<?php endforeach; ?>
    </tr>
    </tbody>
</table>


<?php do_action( 'woocommerce_email_footer' ); ?>