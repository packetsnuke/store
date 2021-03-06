<?php
/**
 * Upgrade in progress template
 *
 * @author		Prospress
 * @category	Admin
 * @package		WooCommerce Subscriptions/Admin/Upgrades
 * @version		2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$upgrade_transient_timeout = get_option( 'wc_subscriptions_is_upgrading' );

$time_until_update_allowed = $upgrade_transient_timeout - time();

@header( 'Content-Type: ' . get_option( 'html_type' ) . '; charset=' . get_option( 'blog_charset' ) ); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php echo esc_attr( get_option( 'blog_charset' ) ); ?>" />
		<title><?php esc_html_e( 'WooCommerce Subscriptions Update in Progress', 'ultimatewoo-pro' ); ?></title>
		<?php wp_admin_css( 'install', true ); ?>
		<?php wp_admin_css( 'ie', true ); ?>
	</head>
	<body class="wp-core-ui">
		<h1 id="logo"><img alt="WooCommerce Subscriptions" width="325px" height="120px" src="<?php echo esc_url( plugins_url( '/assets/images/woocommerce_subscriptions_logo.png', WC_Subscriptions::$plugin_file ) ); ?>" /></h1>
		<h2><?php esc_html_e( 'The Upgrade is in Progress', 'ultimatewoo-pro' ); ?></h2>
		<p><?php esc_html_e( 'The WooCommerce Subscriptions plugin is currently running its database upgrade routine.', 'ultimatewoo-pro' ); ?></p>
		<p><?php
			// translators: placeholder is number of seconds
			printf( esc_html__( 'If you received a server error and reloaded the page to find this notice, please refresh the page in %s seconds and the upgrade routine will recommence without issues.', 'ultimatewoo-pro' ), esc_html( $time_until_update_allowed ) ); ?>
		</p>
		<p><?php esc_html_e( 'Rest assured, although the update process may take a little while, it is coded to prevent defects, your site is safe and will be up and running again, faster than ever, shortly.', 'ultimatewoo-pro' ); ?></p>
	</body>
</html>
<?php

die();
