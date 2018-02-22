<?php
/**
 * WooCommerce PDF Product Vouchers
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce PDF Product Vouchers to newer
 * versions in the future. If you wish to customize WooCommerce PDF Product Vouchers for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-pdf-product-vouchers/ for more information.
 *
 * @package   WC-PDF-Product-Vouchers/Admin
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * PDF Vouhcer Download Handler
 *
 * Based on WC_Download_Handler - allows admins and customers to
 * download otherwise inaccessible generated PDF vouhcers.
 *
 * @since 3.0.0
 */
class WC_PDF_Product_Vouchers_Download_Handler {


	/**
	 * Initializes the download handler class
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		if ( ! empty( $_GET['download_wc_voucher_pdf'] ) ) {
			add_action( 'init', array( $this, 'download_voucher_pdf' ) );
		}

		add_filter( 'user_has_cap', array( $this, 'user_has_download_voucher_cap' ), 10, 3 );
	}


	/**
	 * Downloads a generated PDF file
	 *
	 * @since 3.0.0
	 */
	public function download_voucher_pdf() {

		$voucher = wc_pdf_product_vouchers_get_voucher( $_GET['download_wc_voucher_pdf'] );

		if ( ! $voucher || empty( $_GET['key'] ) || $_GET['key'] !== $voucher->get_voucher_key() ) {
			$this->download_error( __( 'Invalid voucher download link.', 'ultimatewoo-pro' ) );
		}

		// check if current user can download the voucher
		if ( $voucher->get_customer_id() && 'yes' === get_option( 'woocommerce_downloads_require_login' ) ) {

			if ( ! is_user_logged_in() ) {

				if ( wc_get_page_id( 'myaccount' ) ) {
					wp_safe_redirect( add_query_arg( 'wc_error', urlencode( __( 'You must be logged in to download vouchers.', 'ultimatewoo-pro' ) ), wc_get_page_permalink( 'myaccount' ) ) );
					exit;
				} else {
					$this->download_error( __( 'You must be logged in to download vouchers.', 'ultimatewoo-pro' ) . ' <a href="' . esc_url( wp_login_url( wc_get_page_permalink( 'myaccount' ) ) ) . '" class="wc-forward">' . __( 'Login', 'ultimatewoo-pro' ) . '</a>', __( 'Log in to Download Vouchers', 'ultimatewoo-pro' ), 403 );
				}

			} elseif ( ! current_user_can( 'download_voucher', $voucher ) ) {
				$this->download_error( __( 'This is not your download link.', 'ultimatewoo-pro' ), '', 403 );
			}
		}

		$file_path = $voucher->get_voucher_full_filename();
		$file_url  = wc_pdf_product_vouchers_convert_path_to_url( $file_path );
		$filename  = basename( $file_path );

		if ( false !== strpos( $filename, '?' ) ) {
			$filename = current( explode( '?', $filename ) );
		}

		$file_download_method = get_option( 'woocommerce_file_download_method', 'force' );

		// count downloads, unless an admin is downloading the pdf from admin backend
		if ( ! is_admin() || is_admin() && ! current_user_can( 'manage_woocommerce' ) ) {
			$voucher->count_download();
		}

		// add action to prevent issues in IE
		add_action( 'nocache_headers', array( 'WC_Download_Handler', 'ie_nocache_headers_fix' ) );

		// trigger download via one of the methods. WC_Download_Handler will take over from here
		do_action( 'woocommerce_download_file_' . $file_download_method, $file_url, $filename );
	}


	/**
	 * Dies with an error message if the download fails
	 *
	 * @since 3.0.0
	 * @param string $message error message
	 * @param string $title (optional) error message title to use
	 * @param integer $status (optional) http status code to use, defaults to 404
	 */
	private function download_error( $message, $title = '', $status = 404 ) {
		wp_die( $message, $title, array( 'response' => $status ) );
	}


	/**
	 * Checks if a user has a certain capability.
	 *
	 * @param array $allcaps
	 * @param array $caps
	 * @param array $args
	 * @return bool
	 */
	public function user_has_download_voucher_cap( $allcaps, $caps, $args ) {

		if ( isset( $caps[0] ) ) {
			switch ( $caps[0] ) {
				case 'download_voucher':
					$user_id = $args[1];
					$voucher = $args[2];

					if ( $user_id == $voucher->get_customer_id() || current_user_can( 'manage_woocommerce' ) ) {
						$allcaps['download_voucher'] = true;
					}
				break;
			}
		}

		return $allcaps;
	}
}
