<?php
/**
 * Copyright: © 2009-2017 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/** Path and URL constants **/
define( 'FUE_VERSION', '4.6.3' );
define( 'FUE_KEY', 'aHR0cDovLzc1bmluZXRlZW4uY29tL2Z1ZS5waH' );
define( 'FUE_FILE', __FILE__ );
define( 'FUE_URL', ULTIMATEWOO_MODULES_URL . 'follow-up-emails' );
define( 'FUE_DIR', ULTIMATEWOO_MODULES_DIR . 'follow-up-emails/' );
define( 'FUE_INC_DIR', FUE_DIR . 'includes' );
define( 'FUE_INC_URL', FUE_URL . '/includes' );
define( 'FUE_ADDONS_DIR', FUE_DIR . '/addons' );
define( 'FUE_ADDONS_URL', FUE_URL . '/addons' );
define( 'FUE_TEMPLATES_DIR', FUE_DIR . 'templates' );
define( 'FUE_TEMPLATES_URL', FUE_URL . '/templates' );


load_plugin_textdomain( 'follow_up_emails', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

global $fue, $wpdb;
require_once FUE_INC_DIR . '/class-follow-up-emails.php';
$fue = new Follow_Up_Emails( $wpdb );

if ( ! function_exists( 'FUE' ) ) :
	/**
	 * Returns an instance of the Follow_Up_Emails class
	 * @since 5.0
	 * @return Follow_Up_Emails
	 */
	function FUE() {
		return Follow_Up_Emails::instance();
	}
endif;

//4.6.3