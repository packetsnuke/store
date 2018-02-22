<?php
$current_user = wp_get_current_user();
?>
<?php do_action( 'woocommerce_wishlists_before_wrapper' ); ?>
<div id="wl-wrapper" class="woocommerce">
	<?php if ( function_exists( 'wc_print_messages' ) ) : ?>
		<?php wc_print_messages(); ?>
	<?php else : ?>
		<?php WC_Wishlist_Compatibility::wc_print_notices(); ?>
	<?php endif; ?>
    <div class="wl-form">
        <form action="" enctype="multipart/form-data" method="post">
            <input type="hidden" name="wl_return_to" value="<?php echo( isset( $_GET['wl_return_to'] ) ? $_GET['wl_return_to'] : '' ); ?>"/>
			<?php echo WC_Wishlists_Plugin::action_field( 'create-list' ); ?>
			<?php echo WC_Wishlists_Plugin::nonce_field( 'create-list' ); ?>

            <p class="form-row form-row-wide">
                <label for="wishlist_title"><?php _e( 'Name your list', 'ultimatewoo-pro' ); ?>
                    <abbr class="required" title="required">*</abbr></label>
                <input type="text" name="wishlist_title" id="wishlist_title" class="input-text" value=""/>
            </p>
            <p class="form-row form-row-wide">
                <label for="wishlist_description"><?php _e( 'Describe your list', 'ultimatewoo-pro' ); ?></label>
                <textarea name="wishlist_description" id="wishlist_description"></textarea>
            </p>
            <hr/>
            <p class="form-row">
                <strong><?php _e( 'Privacy Settings', 'ultimatewoo-pro' ); ?>
                    <abbr class="required" title="required">*</abbr></strong>
            <table class="wl-rad-table">
                <tr>
                    <td><input type="radio" name="wishlist_sharing" id="rad_pub" value="Public" checked="checked"></td>
                    <td><label for="rad_pub"><?php _e( 'Public', 'ultimatewoo-pro' ); ?>
                            <span class="wl-small">- <?php _e( 'Anyone can search for and see this list. You can also share using a link.', 'ultimatewoo-pro' ); ?></span></label>
                    </td>
                </tr>
                <tr>
                    <td><input type="radio" name="wishlist_sharing" id="rad_shared" value="Shared"></td>
                    <td><label for="rad_shared"><?php _e( 'Shared', 'ultimatewoo-pro' ); ?>
                            <span class="wl-small">- <?php _e( 'Only people with the link can see this list. It will not appear in public search results.', 'ultimatewoo-pro' ); ?></span></label>
                    </td>
                </tr>
                <tr>
                    <td><input type="radio" name="wishlist_sharing" id="rad_priv" value="Private"></td>
                    <td><label for="rad_priv"><?php _e( 'Private', 'ultimatewoo-pro' ); ?>
                            <span class="wl-small">- <?php _e( 'Only you can see this list.', 'ultimatewoo-pro' ); ?></span></label>
                    </td>
                </tr>
            </table>
            </p>
            <p><?php _e( 'Enter a name you would like associated with this list.  If your list is public, users can find it by searching for this name.', 'ultimatewoo-pro' ); ?></p>
            <p class="form-row form-row-first">
                <label for="wishlist_first_name"><?php _e( 'First Name', 'ultimatewoo-pro' ); ?></label>
				<?php if ( is_user_logged_in() ) : ?>
                    <input type="text" name="wishlist_first_name" id="wishlist_first_name" class="input-text" value="<?php echo esc_attr( $current_user->user_firstname ); ?>"/>
				<?php else : ?>
                    <input type="text" name="wishlist_first_name" id="wishlist_first_name" class="input-text" value=""/>
				<?php endif; ?>
            </p>
            <p class="form-row form-row-last">
                <label for="wishlist_first_name"><?php _e( 'Last Name', 'ultimatewoo-pro' ); ?></label>
				<?php if ( is_user_logged_in() ) : ?>
                    <input type="text" name="wishlist_last_name" id="wishlist_last_name" class="input-text" value="<?php echo esc_attr( $current_user->user_lastname ); ?>"/>

				<?php else : ?>
                    <input type="text" name="wishlist_last_name" id="wishlist_last_name" class="input-text" value=""/>
				<?php endif; ?>
            </p>
            <p class="form-row form-row-first">
                <label for="wishlist_owner_email"><?php _e( 'Email Associated with the List', 'ultimatewoo-pro' ); ?></label>
                <input type="text" name="wishlist_owner_email" id="wishlist_owner_email" value="<?php echo( is_user_logged_in() ? $current_user->user_email : '' ); ?>" class="input-text"/>
            </p>


			<?php if ( WC_Wishlists_Settings::get_setting( 'wc_wishlist_notifications_enabled', 'disabled' ) == 'enabled' ): ?>
                <p><?php _e( 'Email Notifications', 'ultimatewoo-pro' ); ?></p>
                <p class="form-row">
                <table class="wl-rad-table">
                    <tr>
                        <td>
                            <input type="radio" id="rad_notification_yes" name="wishlist_owner_notifications" value="yes" <?php checked( true ); ?>>
                        </td>
                        <td><label for="rad_notification_yes"><?php _e( 'Yes', 'ultimatewoo-pro' ); ?>
                                <span class="wl-small">- <?php _e( 'Send me an email if a price reduction occurs.', 'ultimatewoo-pro' ); ?></span></label>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="radio" id="rad_notification_no" name="wishlist_owner_notifications" value="no">
                        </td>
                        <td><label for="rad_notification_no"><?php _e( 'No', 'ultimatewoo-pro' ); ?>
                                <span class="wl-small">- <?php _e( 'Do not send me an email if a price reduction occurs.', 'ultimatewoo-pro' ); ?></span></label>
                        </td>
                    </tr>
                </table>
                </p>
			<?php endif; ?>

            <div class="wl-clear"></div>

            <p class="form-row">
				<?php if ( function_exists( 'gglcptch_display' ) ) {
					echo gglcptch_display();
				}; ?>
            </p>

            <p class="form-row">
                <input type="submit" class="button alt" name="update_wishlist" value="<?php _e( 'Create List', 'ultimatewoo-pro' ); ?>">
            </p>


        </form>
    </div><!-- /wl form -->
</div><!-- /wishlist-wrapper -->
<?php do_action( 'woocommerce_wishlists_after_wrapper' ); ?>
