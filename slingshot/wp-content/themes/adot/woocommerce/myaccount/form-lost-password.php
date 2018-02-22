<?php
/**
 * Lost password form
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php wc_print_notices(); ?>
<div class="reset-password">
	<h2><?php _e( 'Reset password', 'woocommerce' ); ?></h2>
	<p><?php _e( 'Do you already have an account?', 'woocommerce' ) ?>
		<a href="<?php echo get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ); ?>"> <?php _e( 'Sign in', 'woocommerce' ) ?></a>
	</p>
	<form method="post" class="lost_reset_password">

		<?php if ( 'lost_password' == $args['form'] ) : ?>

			<p class="form-row form-row-first">
				<input class="input-text" placeholder="<?php _e( 'Username or email', 'woocommerce' ); ?>" type="text" name="user_login" id="user_login" />
			</p>

		<?php else : ?>

			<p><?php echo apply_filters( 'woocommerce_reset_password_message', __( 'Enter a new password below.', 'woocommerce' ) ); ?></p>

			<p class="form-row form-row-first">
				<input type="password" class="input-text" placeholder="<?php _e( 'New password', 'woocommerce' ); ?>" name="password_1" id="password_1" />
			</p>
			<p class="form-row form-row-last">
				<input type="password" placeholder="<?php _e( 'Re-enter new password', 'woocommerce' ); ?>" class="input-text" name="password_2" id="password_2" />
			</p>
			<input type="hidden" name="reset_key" value="<?php echo isset( $args['key'] ) ? $args['key'] : ''; ?>" />
			<input type="hidden" name="reset_login" value="<?php echo isset( $args['login'] ) ? $args['login'] : ''; ?>" />

		<?php endif; ?>

		<div class="clear"></div>

		<p class="form-row form_submit">
			<input type="hidden" name="wc_reset_password" value="true" />
		<span>
			<input type="submit" class="button" value="<?php echo 'lost_password' == $args['form'] ? __( 'Send', 'woocommerce' ) : __( 'Save', 'woocommerce' ); ?>" />
			<span class="arrow"><svg xmlns="http://www.w3.org/2000/svg" width="46" height="12" viewBox="-30 0 52 12">
					<path fill="#fff" d="M22 6l-6-6v5h-46v2h46v5l6-6z"></path>
				</svg></span>
		</span>
		</p>

		<?php wp_nonce_field( $args['form'] ); ?>

	</form>
</div>
