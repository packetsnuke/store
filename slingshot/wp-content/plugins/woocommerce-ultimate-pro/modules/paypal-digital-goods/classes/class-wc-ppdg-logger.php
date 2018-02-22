<?php
/**
 * Log subscription related events
 * 
 * @package    PayPal Digital Goods
 * @subpackage Subscription
 * 
 * @license    GPLv3
 * @copyright  2015 Prospress Inc.
 * @since 3.2
 */

class WC_PPDG_Logger {

	static $logger;

	static $enable_logging = false;

	public static function init() {
		self::$logger = new WC_Logger();
	}

	public static function enable_logging( $enable_logging ) {
		self::$enable_logging = $enable_logging;
	}

	public static function add( $message ) {
		if ( self::$enable_logging ) {
			self::$logger->add( 'paypal-dg', $message );
		}
	}

}
WC_PPDG_Logger::init();