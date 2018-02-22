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
 * @package   WC-PDF-Product-Vouchers/Classes
 * @author    SkyVerge
 * @copyright Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * AJAX class
 *
 * @since 3.0.0
 */
class WC_PDF_Product_Vouchers_AJAX {


	/**
	 * Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		// voucher notes
		add_action( 'wp_ajax_wc_pdf_product_vouchers_add_voucher_note',              array( $this, 'add_voucher_note' ) );
		add_action( 'wp_ajax_wc_pdf_product_vouchers_delete_voucher_note',           array( $this, 'delete_voucher_note' ) );
		add_action( 'wp_ajax_wc_pdf_product_vouchers_redeem_voucher',                array( $this, 'redeem_voucher' ) );
		add_action( 'wp_ajax_wc_pdf_product_vouchers_void_voucher',                  array( $this, 'void_voucher' ) );
		add_action( 'wp_ajax_wc_pdf_product_vouchers_restore_voucher',               array( $this, 'restore_voucher' ) );
		add_action( 'wp_ajax_wc_pdf_product_vouchers_get_product_details',           array( $this, 'get_product_details' ) );
		add_action( 'wp_ajax_wc_pdf_product_vouchers_get_customer_details',          array( $this, 'get_customer_details' ) );
		add_action( 'wp_ajax_wc_pdf_product_vouchers_get_voucher_preview',           array( $this, 'get_voucher_preview' ) );
		add_action( 'wp_ajax_wc_pdf_product_vouchers_update_voucher_product',        array( $this, 'update_voucher_product' ) );
		add_action( 'wp_ajax_wc_pdf_product_vouchers_calculate_voucher_product_tax', array( $this, 'calculate_voucher_product_tax' ) );
		add_action( 'wp_ajax_wc_pdf_product_vouchers_load_voucher_redemptions',      array( $this, 'load_voucher_redemptions' ) );
		add_action( 'wp_ajax_wc_pdf_product_vouchers_save_voucher_redemptions',      array( $this, 'save_voucher_redemptions' ) );
		add_action( 'wp_ajax_wc_pdf_product_vouchers_delete_voucher_redemption',     array( $this, 'delete_voucher_redemption' ) );

		add_action( 'wp_ajax_wc_pdf_product_vouchers_list_redeem_voucher', array( $this, 'list_redeem_voucher' ) );
		add_action( 'wp_ajax_wc_pdf_product_vouchers_list_void_voucher',   array( $this, 'list_void_voucher' ) );

		// only return voucher products when appropriate
		add_filter( 'woocommerce_json_search_found_products', array( $this, 'filter_json_search_found_products' ) );
	}


	/**
	 * Adds voucher note
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function add_voucher_note() {

		check_ajax_referer( 'add-voucher-note', 'security' );

		$voucher_id = (int) $_POST['voucher_id'];
		$note_text  = wp_kses_post( trim( stripslashes( $_POST['note'] ) ) );

		if ( $voucher_id > 0 ) {

			// get variables to pass to templates
			$args = array(
				'voucher'    => wc_pdf_product_vouchers_get_voucher( $voucher_id ),
				'comment_id' => $voucher->add_note( $note_text ),
				'note'       => get_comment( $comment_id ),
			);

			/* This filter is documented in includes/admin/meta-boxes/views/html-voucher-notes.php */
			$args['note_classes'] = apply_filters( 'wc_pdf_product_vouchers_voucher_note_class', array( 'note' ), $args['note'], $args['voucher'] );

			wp_send_json_success( array(
				'note_html' => $this->render_html_fragment( 'includes/admin/meta-boxes/views/html-voucher-note.php', $args ),
			) );
		}

		exit;
	}


	/**
	 * Deletes voucher note
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function delete_voucher_note() {

		check_ajax_referer( 'delete-voucher-note', 'security' );

		$note_id = (int) $_POST['note_id'];

		if ( $note_id > 0 ) {
			wp_delete_comment( $note_id );
		}

		exit;
	}


	/**
	 * Sends product details
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function get_product_details() {

		check_ajax_referer( 'get-product-details', 'security' );

		$product_id = (int) $_GET['product_id'];

		if ( $product_id > 0 ) {

			$product = wc_get_product( $product_id );

			$data = array(
				'id'    => $product_id,
				'price' => SV_WC_Product_Compatibility::wc_get_price_excluding_tax( $product ),
			);

			/**
			 * Filter the data found for a product with AJAX request
			 *
			 * @since 3.0.0
			 * @param array $data
			 * @param int $product_id
			 */
			$data = apply_filters( 'wc_pdf_product_vouchers_ajax_found_product_details', $data, $product_id );

			wp_send_json_success( $data );
		}

		die();
	}


	/**
	 * Sends customer details
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function get_customer_details() {

		check_ajax_referer( 'get-customer-details', 'security' );

		$user_id = (int) $_GET['user_id'];

		if ( $user_id > 0 ) {

			$data = array(
				'name'  => trim( sprintf( '%s %s', get_user_meta( $user_id, 'billing_first_name', true ), get_user_meta( $user_id, 'billing_last_name', true ) ) ),
				'email' => get_user_meta( $user_id, 'billing_email', true ),
			);

			/**
			 * Filter the data found for a customer with AJAX request
			 *
			 * @since 3.0.0
			 * @param array $data
			 * @param int $user_id
			 */
			$data = apply_filters( 'wc_pdf_product_vouchers_ajax_found_customer_details', $data, $user_id );

			wp_send_json_success( $data );

		}

		die();
	}


	/**
	 * Sends voucher preview
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function get_voucher_preview() {

		check_ajax_referer( 'get-voucher-preview', 'security' );

		$voucher_id = (int) $_GET['voucher_id'];
		$image_id   = (int) $_GET['image_id'];

		if ( $voucher_id > 0 && $image_id > 0 ) {

			$data = array(
				'preview_html' => $this->render_html_fragment( 'includes/admin/meta-boxes/views/html-voucher-preview.php', array(
					'voucher'         => wc_pdf_product_vouchers_get_voucher( $voucher_id ) ,
					'thumbnail_id'    => $image_id,
					'preview_image'   => wp_get_attachment_image( $image_id, 'medium_large' ),
					'unsaved_preview' => true,
				) ),
			);

			wp_send_json_success( $data );
		}

		die();
	}


	/**
	 * Updates voucher product
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function update_voucher_product() {

		check_ajax_referer( 'update-voucher-product', 'security' );

		$voucher_id    = (int) $_POST['voucher_id'];
		$product_id    = (int) $_POST['product_id'];
		$product_price = wc_format_decimal( $_POST['product_price'] );

		if ( $voucher_id > 0 && $product_id > 0 ) {

			$voucher = wc_pdf_product_vouchers_get_voucher( $voucher_id );

			if ( ! $voucher ) {
				wp_send_json_error( array( 'message' => __( 'Invalid voucher', 'ultimatewoo-pro' ) ) );;
			}

			if ( ! $voucher->is_editable() || $voucher->has_redemptions() ) {
				wp_send_json_error( array( 'message' => __( 'Could not change voucher product: voucher is not editable or has redemptions', 'ultimatewoo-pro' ) ) );;
			}

			$previous_product_id  = get_post_meta( $voucher_id, '_product_id', true );
			$previous_template_id = get_post_meta( $previous_product_id, '_voucher_template_id', true );
			$new_template_id      = get_post_meta( $product_id, '_voucher_template_id', true );
			$template_changed     = (int) $previous_template_id !== (int) $new_template_id;

			// update voucher product data
			update_post_meta( $voucher_id, '_product_id', $product_id );
			update_post_meta( $voucher_id, '_product_price', $product_price );

			// recalculate & update taxes
			$new_tax = $voucher->calculate_product_tax();

			if ( $new_tax != $voucher->get_product_tax() ) {
				update_post_meta( $voucher_id, '_product_tax', $new_tax );
			}

			$voucher->calculate_remaining_value();

			$data = array(
				'status'          => $voucher->get_status(),
				'remaining_value' => $voucher->get_remaining_value(),
				'balance_html'    => $this->render_html_fragment( 'includes/admin/meta-boxes/views/html-voucher-balance.php', array( 'voucher' => $voucher ) ),
			);

			if ( $template_changed ) {

				$voucher->set_template_id( $new_template_id );
				$voucher->set_image_id( $voucher->get_template()->get_image_id() );

				try {
					$voucher->generate_pdf();
				} catch( Exception $e ) {
					// simply log exceptions here, as PDF regeneration is not crucial for this action
					/* translators: %s - error message */
					wc_pdf_product_vouchers()->log( sprintf( __( 'Could not generate voucher PDF: %s', 'ultimatewoo-pro' ), $e->getMessage() ) );
				}

				// re-render voucher preview if template has changed
				$data['preview_html'] = $this->render_html_fragment( 'includes/admin/meta-boxes/views/html-voucher-preview.php', array(
					'voucher'         => $voucher,
					'thumbnail_id'    => $voucher->get_image_id(),
					'preview_image'   => $voucher->has_preview_image() ? $voucher->get_preview_image( 'medium_large' ) : $voucher->get_image( 'medium_large' ),
					'unsaved_preview' => false,
				) );
			}

			wp_send_json_success( $data );
		}

		exit;
	}


	/**
	 * Recalculates voucher product tax
	 *
	 * @internal
	 *
	 * @since 3.1.0
	 */
	public function calculate_voucher_product_tax() {

		check_ajax_referer( 'voucher-balance', 'security' );

		$voucher_id = (int) $_POST['voucher_id'];

		if ( $voucher_id > 0 ) {

			$voucher = wc_pdf_product_vouchers_get_voucher( $voucher_id );

			if ( ! $voucher ) {
				wp_send_json_error( array( 'message' => __( 'Invalid voucher', 'ultimatewoo-pro' ) ) );
			} elseif ( ! $voucher->is_editable() || $voucher->has_redemptions() ) {
				wp_send_json_error( array( 'message' => __( 'Could not recalculate voucher product tax: voucher is not editable or has redemptions', 'ultimatewoo-pro' ) ) );
			}

			$tax = $voucher->calculate_product_tax();

			update_post_meta( $voucher_id, '_product_tax', $tax );

			$data = array(
				'status'          => $voucher->get_status(),
				'remaining_value' => $voucher->get_remaining_value(),
				'balance_html'    => $this->render_html_fragment( 'includes/admin/meta-boxes/views/html-voucher-balance.php', array( 'voucher' => $voucher ) ),
			);

			try {
				$voucher->generate_pdf();
			} catch( Exception $e ) {
				// simply log exceptions here, as PDF regeneration is not crucial for this action
				/* translators: %s - error message */
				wc_pdf_product_vouchers()->log( sprintf( __( 'Could not generate voucher PDF: %s', 'ultimatewoo-pro' ), $e->getMessage() ) );
			}

			wp_send_json_success( $data );
		}

		exit;
	}


	/**
	 * Loads voucher redemptions
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function load_voucher_redemptions() {

		check_ajax_referer( 'voucher-balance', 'security' );

		$voucher_id = (int) $_POST['voucher_id'];

		if ( $voucher_id > 0 ) {

			// render voucher redemptions
			$args = array( 'voucher' => wc_pdf_product_vouchers_get_voucher( $voucher_id ) );

			wp_send_json_success( array(
				'redemptions_html' => $this->render_html_fragment( 'includes/admin/meta-boxes/views/html-voucher-redemptions.php', $args ),
			) );
		}

		exit;
	}


	/**
	 * Saves voucher redemptions
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function save_voucher_redemptions() {

		check_ajax_referer( 'voucher-balance', 'security' );

		$voucher_id = (int) $_POST['voucher_id'];

		if ( $voucher_id > 0 ) {

			$voucher = wc_pdf_product_vouchers_get_voucher( $voucher_id );

			if ( ! $voucher ) {
				wp_send_json_error( array( 'message' => __( 'Invalid voucher', 'ultimatewoo-pro' ) ) );
			}

			if ( ! $voucher->is_editable() ) {
				wp_send_json_error( array( 'message' => __( 'Voucher is not editable', 'ultimatewoo-pro' ) ) );
			}

			// parse the serialized string into an array
			$data = array();
			parse_str( $_POST['data'], $data );

			$redemptions = $data['_redemptions'];

			// sanitize redemptions
			foreach ( $redemptions as $i => $redemption ) {

				// use the sanitized (non-formatted) amount for storage
				$redemptions[ $i ]['amount'] = wc_format_decimal( $redemption['amount'] );
				$redemptions[ $i ]['notes']  = sanitize_text_field( $redemption['notes'] );
			}

			try {

				$voucher->set_redemptions( $redemptions );

			} catch( SV_WC_Plugin_Exception $e ) {

				wp_send_json_error( array(
					'message' => $e->getMessage(),
				) );
			}

			// send voucher balance
			$this->send_balance_json_success( $voucher );
		}

		exit;
	}


	/**
	 * Deletes a voucher redemption
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function delete_voucher_redemption() {

		check_ajax_referer( 'voucher-balance', 'security' );

		$voucher_id = (int) $_POST['voucher_id'];
		$key        = (int) $_POST['redemption_key'];

		if ( $voucher_id > 0 && $key >= 0 ) {

			$voucher = wc_pdf_product_vouchers_get_voucher( $voucher_id );

			if ( ! $voucher ) {
				wp_send_json_error( array( 'message' => __( 'Invalid voucher', 'ultimatewoo-pro' ) ) );
			}

			if ( ! $voucher->is_editable() ) {
				wp_send_json_error( array( 'message' => __( 'Voucher is not editable', 'ultimatewoo-pro' ) ) );
			}

			// remove the redemption
			$redemptions = $voucher->get_redemptions();

			unset( $redemptions[ $key ] );

			// TODO: instead of storing all redemptions in a single meta field perhaps
			// we should store each redemption in a separate field (add_post_meta), so that each redemption has
			// a meta_id, which would be a bit more reliable than just using the array key...?
			// The only drawback is that neither wp_update_meta or wp_delete_meta support
			// passing in the meta_id, which is a shame... {IT 2017-01-24}
			$redemptions = array_values( $redemptions );

			try {

				$voucher->set_redemptions( $redemptions );

			} catch( SV_WC_Plugin_Exception $e ) {

				wp_send_json_error( array(
					'message' => $e->getMessage(),
				) );
			}

			// send voucher balance
			$this->send_balance_json_success( $voucher );
		}

		exit;
	}


	/**
	 * Redeems a voucher
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function redeem_voucher() {

		check_ajax_referer( 'voucher-balance', 'security' );

		$voucher_id  = (int) $_POST['voucher_id'];

		if ( $voucher_id > 0 ) {

			$voucher = wc_pdf_product_vouchers_get_voucher( $voucher_id );

			if ( ! $voucher ) {
				wp_send_json_error( array( 'message' => __( 'Invalid voucher', 'ultimatewoo-pro' ) ) );
			}

			if ( ! $voucher->is_editable() ) {
				wp_send_json_error( array( 'message' => __( 'Voucher is not editable', 'ultimatewoo-pro' ) ) );
			}

			$amount = wc_format_decimal( $_POST['amount'] );
			$notes  = sanitize_text_field( $_POST['notes'] );

			try {

				$args = array( 'notes' => $notes, 'user_id' => get_current_user_id() );

				$voucher->redeem( $amount, $args );

				// send voucher balance
				$this->send_balance_json_success( $voucher );

			} catch ( SV_WC_Plugin_Exception $e ) {
				wp_send_json_error( array(
					/* translators: %1$s - voucher number, %2$s - error message */
					'message' => sprintf( __( 'Could not redeem voucher %1$s: %2$s', 'ultimatewoo-pro' ), $voucher->get_voucher_number(), $e->getMessage() ),
				) );
			}
		}

		exit;
	}


	/**
	 * Voids a voucher
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function void_voucher() {

		check_ajax_referer( 'voucher-balance', 'security' );

		$voucher_id  = (int) $_POST['voucher_id'];

		if ( $voucher_id > 0 ) {

			$voucher = wc_pdf_product_vouchers_get_voucher( $voucher_id );

			if ( ! $voucher ) {
				wp_send_json_error( array( 'message' => __( 'Invalid voucher', 'ultimatewoo-pro' ) ) );
			}

			if ( ! $voucher->is_editable() ) {
				wp_send_json_error( array( 'message' => __( 'Voucher is not editable', 'ultimatewoo-pro' ) ) );
			}

			$reason = sanitize_text_field( $_POST['reason'] );

			$args = array(
				'reason'  => $reason,
				'user_id' => get_current_user_id(),
			);

			$voucher->void( $args );

			// send voucher balance
			$this->send_balance_json_success( $voucher );
		}

		exit;
	}


	/**
	 * Restores a voided voucher
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function restore_voucher() {

		check_ajax_referer( 'voucher-balance', 'security' );

		$voucher_id  = (int) $_POST['voucher_id'];

		if ( $voucher_id > 0 ) {

			$voucher = wc_pdf_product_vouchers_get_voucher( $voucher_id );

			if ( ! $voucher ) {
				wp_send_json_error( array( 'message' => __( 'Invalid voucher', 'ultimatewoo-pro' ) ) );
			}

			if ( ! $voucher->has_status( 'voided' ) ) {
				wp_send_json_error( array( 'message' => __( 'Voucher is not voided', 'ultimatewoo-pro' ) ) );
			}

			$voucher->restore();

			// send voucher balance
			$this->send_balance_json_success( $voucher );
		}

		exit;
	}


	/**
	 * Redeems a voucher from the voucher list view.
	 *
	 * Not really an AJAX method, but rather a quick way to handle
	 * the list action, similar to how WC handles order actions in list view.
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function list_redeem_voucher() {

		if ( current_user_can( 'manage_woocommerce' ) && check_admin_referer( 'vouchers-list-redeem-voucher' ) ) {

			$amount     = wc_format_decimal( $_GET['amount'] );
			$notes      = isset( $_GET['notes'] ) ? sanitize_text_field( $_GET['notes'] ) : '';
			$voucher_id = absint( $_GET['voucher_id'] );

			$voucher = wc_pdf_product_vouchers_get_voucher( $voucher_id );

			if ( $voucher && $voucher->is_editable() ) {

				try {

					$args = array( 'notes' => $notes, 'user_id' => get_current_user_id() );

					$voucher->redeem( $amount, $args );

					/* translators: %s - voucher number */
					wc_pdf_product_vouchers()->get_message_handler()->add_message( sprintf( __( 'Voucher %s redeemed.', 'ultimatewoo-pro' ), $voucher->get_voucher_number() ) );

				} catch ( SV_WC_Plugin_Exception $e ) {

					/* translators: %1$s - voucher number, %2$s - error message */
					wc_pdf_product_vouchers()->get_message_handler()->add_error( sprintf( __( 'Could not redeem voucher %1$s: %2$s', 'ultimatewoo-pro' ), $voucher->get_voucher_number(), $e->getMessage() ) );
				}
			}
		}

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=wc_voucher' ) );
		exit;
	}


	/**
	 * Voids a voucher from the voucher list view.
	 *
	 * Not really an AJAX method, but rather a quick way to handle
	 * the list action, similar to how WC handles order actions in list view.
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 */
	public function list_void_voucher() {

		if ( current_user_can( 'manage_woocommerce' ) && check_admin_referer( 'vouchers-list-void-voucher' ) ) {

			$reason     = isset( $_GET['reason'] ) ? sanitize_text_field( $_GET['reason'] ) : '';
			$voucher_id = absint( $_GET['voucher_id'] );

			$voucher = wc_pdf_product_vouchers_get_voucher( $voucher_id );

			if ( $voucher && $voucher->is_editable() ) {

				$args = array(
					'reason'  => $reason,
					'user_id' => get_current_user_id(),
				);

				$voucher->void( $args );

				/* translators: %s - voucher number */
				wc_pdf_product_vouchers()->get_message_handler()->add_message( sprintf( __( 'Voucher %s voided.', 'ultimatewoo-pro' ), $voucher->get_voucher_number() ) );
			}
		}

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=wc_voucher' ) );
		exit;
	}


	/**
	 * Removes non-voucher products from json search results
	 *
	 * @since 3.0.0
	 * @param array $products
	 * @return array $products
	 */
	public function filter_json_search_found_products( $products ) {

		// remove non-voucher products
		if ( isset( $_REQUEST['exclude'] ) && 'wc_pdf_product_vouchers_non_voucher_products' === $_REQUEST['exclude'] ) {
			foreach( $products as $id => $title ) {

				if ( 'yes' !== get_post_meta( $id, '_has_voucher', true ) || ! get_post_meta( $id, '_voucher_template_id', true ) ) {
					unset( $products[ $id ] );
				}
			}
		}

		return $products;
	}


	/**
	 * Sends json success message after updating voucher balance
	 *
	 * @since 3.0.0
	 * @param \WC_Voucher $voucher the voucher object
	 */
	private function send_balance_json_success( WC_Voucher $voucher ) {

		$data = array(
			'status'          => $voucher->get_status(),
			'remaining_value' => $voucher->get_remaining_value(),
			'balance_html'    => $this->render_html_fragment( 'includes/admin/meta-boxes/views/html-voucher-balance.php', array( 'voucher' => $voucher ) ),
			'notes_html'      => $this->render_html_fragment( 'includes/admin/meta-boxes/views/html-voucher-notes.php',   array( 'voucher' => $voucher ) ),
		);

		wp_send_json_success( $data );
	}


	/**
	 * Renders a HTML fragment for the voucher admin screen
	 *
	 * @since 3.0.0
	 * @param string $path path to the HTML file to render
	 * @param array $args associative array of variables to pass to the HTML template
	 * @return string $html
	 */
	private function render_html_fragment( $path, $args ) {

		extract( $args );

		ob_start();

		include( wc_pdf_product_vouchers()->get_plugin_path() . '/' . $path );

		return ob_get_clean();
	}

}
