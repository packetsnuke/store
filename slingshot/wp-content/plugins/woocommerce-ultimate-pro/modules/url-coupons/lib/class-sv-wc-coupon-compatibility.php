<?php
/**
 * WooCommerce Plugin Framework
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the plugin to newer
 * versions in the future. If you wish to customize the plugin for your
 * needs please refer to http://www.skyverge.com
 *
 * @package   SkyVerge/WooCommerce/Compatibility
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'SV_WC_Coupon_Compatibility' ) ) :

/**
 * WooCommerce coupon compatibility class.
 *
 * @since 4.6.0
 */
class SV_WC_Coupon_Compatibility extends SV_WC_Data_Compatibility {


	/** @var array mapped compatibility properties, as `$new_prop => $old_prop` */
	protected static $compat_props = array(
		'date_expires'       => 'expiry_date',
		'email_restrictions' => 'customer_email',
	);


	/**
	 * Get a coupon property.
	 *
	 * @since 4.6.0
	 * @param \WC_Coupon $coupon The coupon data object.
	 * @param string $prop The property name.
	 * @param string $context If 'view' then the value will be filtered (default 'edit', returns the raw value).
	 * @param array $compat_props Compatibility properties.
	 * @return mixed
	 */
	public static function get_prop( $coupon, $prop, $context = 'edit', $compat_props = array() ) {
		return parent::get_prop( $coupon, $prop, $context, self::$compat_props );
	}


	/**
	 * Sets a coupons's properties.
	 *
	 * Note that this does not save any data to the database.
	 *
	 * @since 4.6.0
	 * @param \WC_Coupon $object The coupon object
	 * @param array $props The new properties as $key => $value.
	 * @param array $compat_props Compatibility properties.
	 * @return \WC_Data|\WC_Coupon
	 */
	public static function set_props( $object, $props, $compat_props = array() ) {
		return parent::set_props( $object, $props, self::$compat_props );
	}


	/**
	 * Get a coupon object.
	 *
	 * @since 4.6.0
	 * @param int|\WP_Post|\WC_Coupon $coupon_id A coupon identifier or object.
	 * @return null|\WC_Coupon
	 */
	public static function get_coupon( $coupon_id ) {

		$coupon = null;

		if ( $coupon_id instanceof WC_Coupon ) {
			$coupon = $coupon_id;
		} elseif ( $coupon_id instanceof WP_Post ) {
			$coupon = SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ? new WC_Coupon( $coupon_id->ID ) : new WC_Coupon( $coupon_id->post_title );
		} elseif ( is_numeric( $coupon_id ) ) {
			if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
				$post_title = wc_get_coupon_code_by_id( $coupon_id );
				$coupon     = new WC_Coupon( $post_title );
			} elseif ( $post = get_post( $coupon_id ) ){
				$coupon = new WC_Coupon( $post->post_title );
			}
		}

		return $coupon;
	}


}

endif;
