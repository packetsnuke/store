<?php
/**
 * Copyright: 2009-2017 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Localisation
 */
load_plugin_textdomain( 'woocommerce-advanced-notifications', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**
 * init_advanced_notifications function.
 */
function init_advanced_notifications() {
	if ( is_woocommerce_active() ) {
		include_once( 'includes/class-wc-advanced-notifications.php' );
	}
}

add_action( 'plugins_loaded', 'init_advanced_notifications', 0 );

/**
 * Activation
 * ULTIMATEWOO fix: make this on admin_init, not activation
 */
add_action( 'admin_init', 'activate_advanced_notifications' );
function activate_advanced_notifications() {
	global $wpdb;

	$wpdb->hide_errors();

	$collate = '';
	if ( $wpdb->has_cap( 'collation' ) ) {
		if ( ! empty( $wpdb->charset ) ) {
			$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$collate .= " COLLATE $wpdb->collate";
		}
	}

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	/**
	 * Table for notifications
	 */
	$sql = "
CREATE TABLE {$wpdb->prefix}advanced_notifications (
notification_id bigint(20) NOT NULL auto_increment,
recipient_name LONGTEXT NULL,
recipient_email LONGTEXT NULL,
recipient_address LONGTEXT NULL,
recipient_phone varchar(240) NULL,
recipient_website varchar(240) NULL,
notification_type varchar(240) NULL,
notification_plain_text int(1) NOT NULL,
notification_totals int(1) NOT NULL,
notification_prices int(1) NOT NULL,
notification_sent_count bigint(20) NOT NULL default 0,
PRIMARY KEY  (notification_id)
) $collate;
";
	dbDelta( $sql );

	$sql = "
CREATE TABLE {$wpdb->prefix}advanced_notification_triggers (
notification_id bigint(20) NOT NULL,
object_id bigint(20) NOT NULL,
object_type varchar(200) NOT NULL,
PRIMARY KEY  (notification_id,object_id)
) $collate;
";
	dbDelta( $sql );
}

//1.2.7