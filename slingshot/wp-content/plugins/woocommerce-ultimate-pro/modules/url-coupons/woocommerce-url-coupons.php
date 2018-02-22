<?php
/**
 * Copyright: (c) 2013-2017, SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package     WC-URL-Coupons
 * @author      SkyVerge
 * @category    Marketing
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library class
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once SV_WC_FRAMEWORK_FILE;
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.6.0', __( 'WooCommerce URL Coupons', 'ultimatewoo-pro' ), __FILE__, 'init_woocommerce_url_coupons', array(
	'minimum_wc_version'   => '2.5.5',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4',
) );

function init_woocommerce_url_coupons() {

/**
 * Main Plugin Class
 *
 * @since 1.0
 */
class WC_URL_Coupons extends SV_WC_Plugin {


	/** plugin version number */
	const VERSION = '2.5.1';

	/** @var WC_URL_Coupons single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'url_coupons';

	/** @var \WC_URL_Coupons_Frontend instance */
	protected $frontend;

	/** @var \WC_URL_Coupons_Admin instance */
	protected $admin;

	/** @var \WC_URL_Coupons_Ajax instance */
	protected $ajax;

	/** @var \WC_URL_Coupons_Import_Export_Handler instance */
	protected $import_export_handler;


	/**
	 * Bootstrap plugin
	 *
	 * @since 1.0
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'text_domain' => 'woocommerce-url-coupons',
			)
		);

		// Extend the SV Framework to account for Coupon objects.
		// TODO remove this when WC 3.0 is the minimum required version {FN 2017-02-17}
		require_once( $this->get_plugin_path() . '/lib/class-sv-wc-coupon-compatibility.php' );

		$this->includes();
	}


	/**
	 * Include required files
	 *
	 * @since 2.0.0
	 */
	public function includes() {

		if ( is_admin() ) {

			// admin
			$this->admin = $this->load_class( '/includes/admin/class-wc-url-coupons-admin.php', 'WC_URL_Coupons_Admin' );

			if ( is_ajax() ) {
				$this->ajax = $this->load_class( '/includes/class-wc-url-coupons-ajax.php', 'WC_URL_Coupons_AJAX' );
			}

		} elseif ( ! is_ajax() ) {

			// frontend
			$this->frontend = $this->load_class( '/includes/frontend/class-wc-url-coupons-frontend.php', 'WC_URL_Coupons_Frontend' );
		}

		// import/export handler
		$this->import_export_handler = $this->load_class( '/includes/class-wc-url-coupons-import-export-handler.php', 'WC_URL_Coupons_Import_Export_Handler' );
	}


	/** Helper methods ******************************************************/


	/**
	 * Main URL Coupons Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.3.0
	 * @see wc_url_coupons()
	 * @return \WC_URL_Coupons
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Get the Admin instance
	 *
	 * @since 2.3.0
	 * @return \WC_URL_Coupons_Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Get the Front End instance
	 *
	 * @since 2.3.0
	 * @return \WC_URL_Coupons_Frontend
	 */
	public function get_frontend_instance() {
		return $this->frontend;
	}


	/**
	 * Get the Ajax instance
	 *
	 * @since 2.3.0
	 * @return \WC_URL_Coupons_Ajax
	 */
	public function get_ajax_instance() {
		return $this->ajax;
	}


	/**
	 * Get the import/export handler instance
	 *
	 * @since 2.4.0
	 * @return \WC_URL_Coupons_Import_Export_Handler
	 */
	public function get_import_export_handler_instance() {
		return $this->import_export_handler;
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.2
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce URL Coupons', 'ultimatewoo-pro' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.2
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


	/**
	 * Gets the plugin documentation URL
	 *
	 * @since 2.1.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {
	    return 'https://docs.woocommerce.com/document/url-coupons/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 2.1.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'https://woocommerce.com/my-account/tickets/';
	}


	/**
	 * Gets the plugin configuration URL
	 *
	 * @since 2.3.1
	 * @see \SV_WC_Plugin::get_settings_link()
	 * @param string $plugin_id optional plugin identifier.  Note that this can be a
	 *        sub-identifier for plugins with multiple parallel settings pages
	 *        (ie a gateway that supports both credit cards and echecks)
	 * @return string plugin settings URL
	 */
	public function get_settings_url( $plugin_id = null ) {
		return admin_url( 'admin.php?page=wc-settings&tab=checkout' );
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Upgrade to the current version
	 *
	 * @since 1.0
	 * @see SV_WC_Plugin::upgrade()
	 * @param string $installed_version
	 */
	public function upgrade( $installed_version ) {

		// upgrade to 1.0.2
		if ( version_compare( $installed_version, '1.0.2', '<' ) ) {

			// Prior versions had a bug where any coupons trashed would not remove
			// the associated unique URL from the active list, resulting in
			// "coupon does not exist" errors when the unique URLs were visited.
			// This wasn't a very visible problem with very unique URLs, but becomes
			// a serious problem when someone uses "/checkout/" as the unique URL

			// load active coupon list
			$coupons = (array) get_option( 'wc_url_coupons_active_urls' );

			// iterate through post IDs
			foreach ( $coupons as $coupon_id => $coupon_data ) {

				// if coupon doesn't exist or is not published, remove from active list
				if ( 'publish' !== get_post_status( $coupon_id ) ) {
					unset( $coupons[ $coupon_id ] );
				}
			}

			// update active list
			update_option( 'wc_url_coupons_active_urls', $coupons );

			// clear transient
			delete_transient( 'wc_url_coupons_active_urls' );
		}

		// upgrade to 2.0.0
		if ( version_compare( $installed_version, '2.0.0', '<' ) ) {

			$coupons = (array) get_option( 'wc_url_coupons_active_urls' );

			// two changes to the coupon data:
			// 1) "force apply" is now called "defer apply"
			// 2) prior versions didn't support the redirect page type and while
			// we can (and do) use `page` as the default, it's nicer to have
			// the redirect page type set properly
			foreach ( $coupons as $coupon_id => $data ) {

				// force => defer
				$coupons[ $coupon_id ]['defer'] = isset( $coupons[ $coupon_id ]['force'] ) ? $coupons[ $coupon_id ]['force'] : false;

				if ( $coupons[ $coupon_id ]['defer'] ) {

					if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
						if ( $coupon = SV_WC_Coupon_Compatibility::get_coupon( $coupon_id ) ) {
							SV_WC_Coupon_Compatibility::update_meta_data( $coupon, '_wc_url_coupons_defer_apply', 'yes' );
						}
					} else {
						update_post_meta( $coupon_id, '_wc_url_coupons_defer_apply', 'yes' );
					}
				}

				// remove force
				unset( $coupons[ $coupon_id ]['force'] );

				if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
					if ( $coupon = SV_WC_Coupon_Compatibility::get_coupon( $coupon_id ) ) {
						SV_WC_Coupon_Compatibility::delete_meta_data( $coupon, '_wc_url_coupons_force_apply' );
					}
				} else {
					delete_post_meta( $coupon_id, '_wc_url_coupons_force_apply' );
				}

				// update redirect page type
				if ( empty( $data['redirect'] ) ) {
					continue;
				}

				$post_type = get_post_type( $data['redirect'] );

				// no existing redirects should be set to these post types, but just in case
				if ( ! $post_type ) {
					$post_type = 'page';
				} elseif ( 'product_variation' === $post_type ) {
					$post_type = 'product';
				}

				$coupons[ $coupon_id ]['redirect_page_type'] = $post_type;
			}

			// update active list
			update_option( 'wc_url_coupons_active_urls', $coupons );

			// clear transient
			delete_transient( 'wc_url_coupons_active_urls' );
		}

		// upgrade to 2.1.1
		if ( version_compare( $installed_version, '2.1.1', '<' ) ) {

			$coupons = (array) get_option( 'wc_url_coupons_active_urls' );

			// prior versions didn't update the redirect post type coupon meta
			foreach ( $coupons as $coupon_id => $data ) {

				// update redirect page type
				if ( empty( $data['redirect'] ) ) {
					continue;
				}

				if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
					if ( $coupon = SV_WC_Coupon_Compatibility::get_coupon( $coupon_id ) ) {
						$redirect_page_type = SV_WC_Coupon_Compatibility::get_meta( $coupon, '_wc_url_coupons_redirect_page_type', true );
						if ( ! empty( $redirect_page_type ) ) {
							continue;
						}
					}
				} else {
					if ( get_post_meta( $coupon_id, '_wc_url_coupons_redirect_page_type', true ) ) {
						continue;
					}
				}

				$post_type = get_post_type( $data['redirect'] );

				// no existing redirects should be set to these post types, but just in case
				if ( ! $post_type ) {
					$post_type = 'page';
				} elseif ( 'product_variation' === $post_type ) {
					$post_type = 'product';
				}

				if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
					if ( $coupon = SV_WC_Coupon_Compatibility::get_coupon( $coupon_id ) ) {
						SV_WC_Coupon_Compatibility::update_meta_data( $coupon, '_wc_url_coupons_redirect_page_type', $post_type );
					}
				} else {
					update_post_meta( $coupon_id, '_wc_url_coupons_redirect_page_type', $post_type );
				}
			}
		}

		// upgrade to 2.5.1, only from 2.5.0 and if running WC 3.0+
		// some data was incorrectly set for WC 3.0+ using v2.5.0 due to select2 upgrade
		if ( version_compare( $installed_version, '2.5.0', '=' ) && '2.5.1' === self::VERSION && SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {

			$coupons  = (array) get_option( 'wc_url_coupons_active_urls' );
			$new_data = array();

			foreach ( $coupons as $coupon_id => $data ) {

				$new_data[ $coupon_id ] = $data;

				// prior versions didn't properly save the redirect page ID
				if ( 0 == $data['redirect'] ) {

					// good news! coupon meta wasn't updated as as result of this error since checks failed, so we can still get it
					$coupon                             = SV_WC_Coupon_Compatibility::get_coupon( $coupon_id );
					$new_data[ $coupon_id ]['redirect'] = SV_WC_Coupon_Compatibility::get_meta( $coupon, '_wc_url_coupons_redirect_page' );
				}
			}

			update_option( 'wc_url_coupons_active_urls', $new_data );
			delete_transient( 'wc_url_coupons_active_urls' );
		}
	}


}


/**
 * Returns the One True Instance of URL Coupons
 *
 * @since 1.3.0
 * @return \WC_URL_Coupons
 */
function wc_url_coupons() {
	return WC_URL_Coupons::instance();
}

// fire ze missiles!
wc_url_coupons();

} // init_woocommerce_url_coupons()

//2.5.1