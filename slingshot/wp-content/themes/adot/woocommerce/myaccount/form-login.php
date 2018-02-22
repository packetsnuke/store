<?php
/**
 * Login Form
 *
 * @author        WooThemes
 * @package       WooCommerce/Templates
 * @version       3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php wc_print_notices(); ?>

<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

<div class="col2-set" id="customer_login" xmlns="http://www.w3.org/1999/html">

	<div class="col-1">
		<?php endif; ?>

		<h2><?php _e( 'Login', 'woocommerce' ); ?></h2>

		<p><?php _e( 'Don\'t have an account?', 'thim' ) ?>
			<a class="to-register" href="javascript:;"><?php _e( 'Sign up', 'woocommerce' ) ?></a></p>

		<form method="post" class="login">

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="form-row form-row-wide">
				<input type="text" class="input-text" name="username" placeholder="<?php _e( 'Username or email address', 'woocommerce' ); ?>" id="username" value="<?php if ( ! empty( $_POST['username'] ) ) {
					echo esc_attr( $_POST['username'] );
				} ?>" />
			</p>

			<p class="form-row form-row-wide">
				<input class="input-text" type="password" placeholder="<?php _e( 'Password', 'woocommerce' ); ?>" name="password" id="password" />
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<p class="form-row">
				<?php wp_nonce_field( 'woocommerce-login' ); ?>
				<label for="rememberme" class="inline rememberme">
					<input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember me', 'woocommerce' ); ?>
				</label>
				<a class="lost_password" href="<?php echo esc_url( wc_lostpassword_url() ); ?>"><?php _e( 'Lost your password?', 'woocommerce' ); ?></a>

			</p>

			<p class="form_submit">
				<input type="submit" class="button" name="login" value="<?php _e( 'Login', 'woocommerce' ); ?>" />
					<span class="arrow"><svg xmlns="http://www.w3.org/2000/svg" width="46" height="12" viewBox="-30 0 52 12">
							<path fill="#fff" d="M22 6l-6-6v5h-46v2h46v5l6-6z"></path>
						</svg>
					</span>
			</p>
			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>

		<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

	</div>

	<div class="col-2">

		<h2><?php _e( 'Register', 'woocommerce' ); ?></h2>

		<p><?php _e( 'Already a member?', 'thim' ) ?>
			<a class="to-login" href="javascript:;"><?php _e( 'Log in', 'woocommerce' ) ?></a></p>

		<form method="post" class="register">

			<?php do_action( 'woocommerce_register_form_start' ); ?>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

				<p class="form-row form-row-wide">
					<label for="reg_username"><?php _e( 'Username', 'woocommerce' ); ?> <span class="required">*</span></label>
					<input type="text" class="input-text" name="username" id="reg_username" value="<?php if ( ! empty( $_POST['username'] ) ) {
						echo esc_attr( $_POST['username'] );
					} ?>" />
				</p>

			<?php endif; ?>

			<p class="form-row form-row-wide">
				<input type="email" placeholder="<?php _e( 'Email address', 'woocommerce' ); ?>" class="input-text" name="email" id="reg_email" value="<?php if ( ! empty( $_POST['email'] ) ) {
					echo esc_attr( $_POST['email'] );
				} ?>" />
			</p>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

				<p class="form-row form-row-wide">
					<input type="password" placeholder="<?php _e( 'Password', 'woocommerce' ); ?> " class="input-text" name="password" id="reg_password" />
				</p>

			<?php endif; ?>

			<!-- Spam Trap -->
			<div style="<?php echo( ( is_rtl() ) ? 'right' : 'left' ); ?>: -999em; position: absolute;">
				<label for="trap"><?php _e( 'Anti-spam', 'woocommerce' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" />
			</div>

			<?php do_action( 'woocommerce_register_form' ); ?>
			<?php do_action( 'register_form' ); ?>

			<p class="form-row form_register">
				<?php wp_nonce_field( 'woocommerce-register' ); ?>
				<input type="submit" class="button" name="register" value="<?php _e( 'Register', 'woocommerce' ); ?>" />
				<span class="arrow"><svg xmlns="http://www.w3.org/2000/svg" width="46" height="12" viewBox="-30 0 52 12">
						<path fill="#fff" d="M22 6l-6-6v5h-46v2h46v5l6-6z"></path>
					</svg></span>
			</p>

			<?php do_action( 'woocommerce_register_form_end' ); ?>

		</form>

	</div>

</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
