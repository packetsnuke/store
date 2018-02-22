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
 * PDF Product Vouchers Vouchers Admin
 *
 * @since 3.0.0
 */
class WC_PDF_Product_Vouchers_Admin_Vouchers {


	/** @var bool whether meta boxes wre already saved or not */
	private $saved_meta_boxes = false;


	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		add_action( 'add_meta_boxes', array( $this, 'customize_meta_boxes' ), 30 );
		add_action( 'edit_form_top',  array( $this, 'voucher_nonce' ) );
		add_action( 'save_post',      array( $this, 'save' ), 10, 2 );
		add_filter( 'default_title',  array( $this, 'default_voucher_title' ), 10, 2 );

		add_filter( 'wp_insert_post_parent', array( $this, 'default_voucher_parent' ), 10, 4 );

		add_action( 'dbx_post_advanced', array( $this, 'load_voucher' ), 1 );
	}


	/**
	 * Customizes meta boxes on the voucher edit screen
	 *
	 * Additional meta boxes are added by their respective classes.
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function customize_meta_boxes() {

		// remove the built-in submit box div
		remove_meta_box( 'submitdiv', 'wc_voucher', 'side' );
	}


	/**
	 * Outputs the voucher nonce field in voucher edit screen
	 *
	 * @since 3.0.0
	 * @param \WP_Post $post the post object
	 */
	public function voucher_nonce( WP_Post $post ) {

		if ( ! is_object( $post ) || 'wc_voucher' !== $post->post_type ) {
			return;
		}

		wp_nonce_field( 'wc_voucher_save_data', 'wc_voucher_meta_nonce' );
	}


	/**
	 * Processes and saves voucher data
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 * @param int $post_id post identifier
	 * @param \WP_Post $post the post object
	 */
	public function save( $post_id, WP_Post $post ) {
		global $wpdb;

		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) || $this->saved_meta_boxes ) {
			return;
		}

		// dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// check the nonce
		if ( empty( $_POST['wc_voucher_meta_nonce'] ) || ! wp_verify_nonce( $_POST['wc_voucher_meta_nonce'], 'wc_voucher_save_data' ) ) {
			return;
		}

		// check the post being saved == the $post_id to prevent triggering this call for other save_post events
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// we need this save event to run once to avoid potential endless loops.
		$this->saved_meta_boxes = true;

		/**
		 * Fires when a voucher is saved/updated from admin
		 *
		 * @since 3.0.0
		 * @param int $post_id post identifier
		 * @param \WP_Post $post the post object
		 */
		do_action( 'wc_pdf_product_vouchers_process_voucher_meta', $post_id, $post );
	}


	/**
	 * Generates a random voucher number for a manually created voucher and sets it as the post title
	 *
	 * @since 3.0.0
	 * @param string $post_title post title
	 * @param \WP_Post $post the post object
	 * @return string generated voucher number
	 */
	public function default_voucher_title( $post_title, WP_Post $post ) {

		if ( 'wc_voucher' === $post->post_type ) {
			$post_title = wc_pdf_product_vouchers_generate_voucher_number();
		}

		return $post_title;
	}


	/**
	 * Sets post_parent (voucher template ID) on the auto-draft in admin
	 *
	 * Setting the post_parent on the auto-draft before it's pesisted into DB
	 * avoids conflicts where the global $the post object has post_parent set, but it's not in DB,
	 * causing wc_pdf_product_vouchers_get_voucher( $post->ID ) to return no template.
	 *
	 * Also validates required input for a new voucher.
	 *
	 * @since 3.0.0
	 * @param int $post_parent post parent identifier
	 * @param int $post_ID post identifier
	 * @param array $new_postarr array of parsed post data
	 * @param array $postarr array of sanitized, but otherwise unmodified post data
	 * @return int parent post identifier
	 */
	public function default_voucher_parent( $post_parent, $post_id, $new_postarr, $postarr ) {
		global $pagenow;

		if ( 'post-new.php' === $pagenow && 'auto-draft' === $new_postarr['post_status'] && 'wc_voucher' === $new_postarr['post_type'] ) {

			// get product details
			$product_id = ( isset( $_GET['product'] ) ? $_GET['product'] : null );
			$product    = $product_id ? wc_get_product( $product_id ) : null;

			if ( ! $product_id || ! $product ) {

				wc_pdf_product_vouchers()->get_message_handler()->add_error( __( 'Please select a product to add a voucher for.', 'ultimatewoo-pro' ) );
				wp_redirect( wp_get_referer() );
				exit;
			}

			$voucher_template_id = SV_WC_Product_Compatibility::get_meta( $product, '_voucher_template_id', true );
			$voucher_template    = $voucher_template_id ? wc_pdf_product_vouchers_get_voucher_template( $voucher_template_id ) : null;

			if ( ! $voucher_template_id || ! $voucher_template ) {

				wc_pdf_product_vouchers()->get_message_handler()->add_error( __( 'Please select a product with a voucher template to add a voucher.', 'ultimatewoo-pro' ) );
				wp_redirect( wp_get_referer() );
				exit;
			}

			$post_parent = $voucher_template_id;
		}

		return $post_parent;
	}


	/**
	 * Loads the voucher before rendering any content on the edit screen
	 *
	 * Provides a single, globally available instance of the current voucher being edited.
	 *
	 * @since 3.0.0
	 */
	public function load_voucher() {
		global $post, $voucher, $typenow, $pagenow;

		if ( 'wc_voucher' !== $typenow ) {
			return;
		}

		// load the voucher instance and make it availabel globally
		$voucher = wc_pdf_product_vouchers_get_voucher( $post );

		// set data for a new auto-draft voucher
		if ( 'post-new.php' === $pagenow ) {

			// set customer id and purchaser details, if available
			if ( ! empty( $_GET['customer'] ) ) {

				$user_id = absint( $_GET['customer'] );

				wp_update_post( array( 'ID' => $post->ID, 'post_author' => 1 ) );

				update_post_meta( $post->ID, '_customer_user', $user_id );
				update_post_meta( $post->ID, '_purchaser_name', trim( sprintf( '%s %s', get_user_meta( $user_id, 'billing_first_name', true ), get_user_meta( $user_id, 'billing_last_name', true ) ) ) );
				update_post_meta( $post->ID, '_purchaser_email', get_user_meta( $user_id, 'billing_email', true ) );
			}

			// get product details
			$product_id = ( isset( $_GET['product'] ) ? $_GET['product'] : null );
			$product    = $product_id ? wc_get_product( $product_id ) : null;

			// update the global voucher instance
			$voucher = wc_pdf_product_vouchers_get_voucher( $post->ID );

			// set meta on the auto-draft - it will be automatically cleaned up based on auto-draft settings
			update_post_meta( $post->ID, '_product_id',    $product_id );
			update_post_meta( $post->ID, '_product_price', SV_WC_Product_Compatibility::wc_get_price_excluding_tax( $product ) );
			update_post_meta( $post->ID, '_thumbnail_id',  $voucher->get_template()->get_image_id() );

			// calculate initial tax
			update_post_meta( $post->ID, '_product_tax',  $voucher->calculate_product_tax() );
		}

	}


}
