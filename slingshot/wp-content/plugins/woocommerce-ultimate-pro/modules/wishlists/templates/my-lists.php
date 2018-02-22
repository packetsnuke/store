<?php do_action( 'woocommerce_wishlists_before_wrapper' ); ?>
<?php $lists = WC_Wishlists_User::get_wishlists(); ?>
<div id="wl-wrapper" class="woocommerce">

	<?php if ( function_exists( 'wc_print_messages' ) ) : ?>
		<?php wc_print_messages(); ?>
	<?php else : ?>
		<?php WC_Wishlist_Compatibility::wc_print_notices(); ?>
	<?php endif; ?>

    <div class="wl-row">
        <a href="<?php echo WC_Wishlists_Pages::get_url_for( 'create-a-list' ); ?>" class="button alt wl-create-new"><?php _e( 'Create a New List', 'ultimatewoo-pro' ); ?></a>
    </div>

	<?php if ( $lists && count( $lists ) ) : ?>
        <form method="post">

			<?php echo WC_Wishlists_Plugin::nonce_field( 'edit-lists' ); ?>
			<?php echo WC_Wishlists_Plugin::action_field( 'edit-lists' ); ?>
			<?php $lists = WC_Wishlists_User::get_wishlists(); ?>


            <table class="shop_table cart wl-table wl-manage" cellspacing="0">
                <thead>
                <tr>
                    <th class="product-name"><?php _e( 'List Name', 'ultimatewoo-pro' ); ?></th>
                    <th class="wl-date-added"><?php _e( 'Date Added', 'ultimatewoo-pro' ); ?></th>
                    <th class="wl-privacy-col"><?php _e( 'Privacy Settings', 'ultimatewoo-pro' ); ?></th>
                </tr>
                </thead>
                <tbody>

				<?php foreach ( $lists as $list ) : ?>
					<?php
					$sharing = $list->get_wishlist_sharing();
					?>

                    <tr class="cart_table_item">
                        <td class="product-name">
                            <strong><a href="<?php $list->the_url_edit(); ?>"><?php $list->the_title(); ?></a></strong>
                            <div class="row-actions">
									<span class="edit">
										<small><a href="<?php $list->the_url_edit(); ?>"><?php _e( 'Manage this list', 'ultimatewoo-pro' ); ?></a></small>
									</span>
                                |
                                <span class="trash">
										<small><a class="ico-delete wlconfirm" data-message="<?php _e( 'Are you sure you want to delete this list?', 'ultimatewoo-pro' ); ?>" href="<?php $list->the_url_delete(); ?>"><?php _e( 'Delete', 'ultimatewoo-pro' ); ?></a></small>
									</span>
								<?php if ( $sharing == 'Public' || $sharing == 'Shared' ) : ?>
                                    |
                                    <span class="view">
											<small><a href="<?php $list->the_url_view(); ?>&preview=true"><?php _e( 'Preview', 'ultimatewoo-pro' ); ?></a></small>
										</span>
								<?php endif; ?>
                            </div>
							<?php if ( $sharing == 'Public' || $sharing == 'Shared' ) : ?>
								<?php woocommerce_wishlists_get_template( 'wishlist-sharing-menu.php', array( 'id' => $list->id ) ); ?>
							<?php endif; ?>
                        </td>
                        <td class="wl-date-added"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $list->post->post_date ) ); ?></td>
                        <td class="wl-privacy-col">
                            <select class="wl-priv-sel" name="sharing[<?php echo $list->id; ?>]">
                                <option <?php selected( $sharing, 'Public' ); ?> value="Public"><?php _e( 'Public', 'ultimatewoo-pro' ); ?></option>
                                <option <?php selected( $sharing, 'Shared' ); ?> value="Shared"><?php _e( 'Shared', 'ultimatewoo-pro' ); ?></option>
                                <option <?php selected( $sharing, 'Private' ); ?> value="Private"><?php _e( 'Private', 'ultimatewoo-pro' ); ?></option>
                            </select>

                        </td>
                    </tr>
				<?php endforeach; ?>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td class="actions">
                        <input type="submit" class="button wl-but" name="update_wishlists" value="<?php _e( 'Save Changes', 'ultimatewoo-pro' ); ?>"/>
                    </td>
                </tr>

                </tbody>
            </table>
        </form>
	<?php else : ?>
		<?php $shop_url = get_permalink( WC_Wishlist_Compatibility::wc_get_page_id( 'shop' ) ); ?>
		<?php _e( 'You have not created any lists yet.', 'ultimatewoo-pro' ); ?>
        <a href="<?php echo $shop_url; ?>"><?php _e( 'Go shopping to create one.', 'ultimatewoo-pro' ); ?></a>
	<?php endif; ?>

	<?php
	if ( $lists && count( $lists ) ) :
		foreach ( $lists as $list ) :
			$sharing = $list->get_wishlist_sharing();
			if ( $sharing == 'Public' || $sharing == 'Shared' ) :
				woocommerce_wishlists_get_template( 'wishlist-email-form.php', array( 'wishlist' => $list ) );
			endif;
		endforeach;
	endif;
	?>
</div><!-- /wishlist-wrapper -->
<?php do_action( 'woocommerce_wishlists_after_wrapper' ); ?>
