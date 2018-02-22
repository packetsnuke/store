<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update settings for 1.2.1.
 *
 * @since  1.2.1
 *
 * @param  string $old_version Previous plugin version.
 * @param  string $new_version Current plugin version.
 */
function wcch_plugin_update_1_2_1( $old_version, $new_version ) {

	if ( '1.2.1' === $new_version ) {
		$settings = get_option( 'woocommerce_wcch_settings', null );
		if ( isset( $settings['wcch_admin_email_enabled'] ) && 'yes' === $settings['wcch_admin_email_enabled'] ) {
			$settings['wcch_admin_email_browsing_enabled'] = 'yes';
			$settings['wcch_admin_email_purchase_enabled'] = 'yes';
			unset( $settings['wcch_admin_email_enabled'] );
			update_option( 'woocommerce_wcch_settings', $settings );
		}
	}

}
add_action( 'wcch_plugin_update', 'wcch_plugin_update_1_2_1', 10, 2 );
