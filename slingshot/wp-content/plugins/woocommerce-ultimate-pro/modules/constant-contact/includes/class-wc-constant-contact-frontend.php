<?php
/**
 * WooCommerce Constant Contact
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Constant Contact to newer
 * versions in the future. If you wish to customize WooCommerce Constant Contact for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-constant-contact/ for more information.
 *
 * @package     WC-Constant-Contact/Classes
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Frontend class
 *
 * Handles all frontend-related actions
 *
 * @since 1.0
 */
class WC_Constant_Contact_Frontend {


	/**
	 * Add hooks
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// add subscribe checkbox to checkout
		add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'render_subscribe_checkbox' ) );

		// process subscribe checkbox after order is processed
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'process_subscribe_checkbox' ) );

		// add subscribe section on order received page for customers that have missed the checkbox on the checkout page
		add_action( 'woocommerce_thankyou', array( $this, 'render_order_received_subscribe_section' ) );

		// handle the subscribe section AJAX submit on the order received page
		add_action( 'wp_ajax_wc_constant_contact_order_received_subscribe',        array( $this, 'ajax_process_order_received_subscribe' ) );
		add_action( 'wp_ajax_nopriv_wc_constant_contact_order_received_subscribe', array( $this, 'ajax_process_order_received_subscribe' ) );
	}


	/**
	 * Adds "Subscribe to Newsletter?" checkbox to checkout page
	 *
	 * @since 1.0
	 */
	public function render_subscribe_checkbox() {

		// bail if API not available or customer has already subscribed to email list
		if ( ! wc_constant_contact()->get_api() || wc_constant_contact()->get_api()->customer_has_already_subscribed() ) {
			return;
		}

		// use previous value or default value when loading checkout page
		if ( ! empty( $_POST['wc_constant_contact_subscribe'] ) ) {
			$value = ( 'yes' === $_POST['wc_constant_contact_subscribe'] ) ? 1 : 0;
		} else {
			$value = ( 'checked' === get_option( 'wc_constant_contact_subscribe_checkbox_default', 'unchecked' ) ) ? 1 : 0;
		}

		// output checkbox
		woocommerce_form_field( 'wc_constant_contact_subscribe', array(
			'type'  => 'checkbox',
			'class' => array( 'form-row-wide' ),
			'label' => get_option( 'wc_constant_contact_subscribe_checkbox_label' )
		), $value );
	}


	/**
	 * Add the customer to the email list if they've selected the checkbox
	 *
	 * @since 1.0
	 * @param int $order_id order ID for order being processed
	 */
	public function process_subscribe_checkbox( $order_id ) {

		// bail if not set
		if ( empty( $_POST['wc_constant_contact_subscribe'] ) ) {
			return;
		}

		try {

			wc_constant_contact()->get_api()->subscribe_customer( $order_id );

		} catch ( SV_WC_API_Exception $e ) {

			$order = wc_get_order( $order_id );

			$order->add_order_note( sprintf( __( 'Failed to subscribe customer to email list: %s', 'ultimatewoo-pro' ), $e->getMessage() ) );
		}
	}


	/**
	 * Render a subscribe section on the order received page, if the customer hasn't already subscribed
	 *
	 * @since 1.0
	 * @param int $order_id order ID for order being processed
	 */
	public function render_order_received_subscribe_section( $order_id ) {

		// bail if API not available or customer has already subscribed to email list
		if ( ! wc_constant_contact()->get_api() || wc_constant_contact()->get_api()->customer_has_already_subscribed() ) {
			return;
		}

		// use checkout label as message
		$message = get_option( 'wc_constant_contact_subscribe_checkbox_label' );

		// add a call to action
		$message .= '<a href="#" id="wc_constant_contact_subscribe" class="button">' . apply_filters( 'wc_constant_contact_order_received_button_text', __( 'Subscribe Now', 'ultimatewoo-pro' ) ) . '</a>';

		// wrap with info div
		$message = '<div class="woocommerce-info wc_constant_contact_order_received_subscribe_section">' . $message . '</div>';

		echo wp_kses_post( apply_filters( 'wc_constant_contact_order_received_subscribe_message', $message, $order_id ) );

		$loader = wc_constant_contact()->get_framework_assets_url() . '/images/ajax-loader.gif';

		// add AJAX
		wc_enqueue_js( '
			/* Constant Contact AJAX Order Received Subscribe */
			$( "#wc_constant_contact_subscribe" ).click( function( e ) {

				e.preventDefault();

				var $section = $( "div.wc_constant_contact_order_received_subscribe_section" );

				if ( $section.is( ".processing" ) ) return false;

				$section.addClass( "processing" ).block({message: null, overlayCSS: {background: "#fff url(' . $loader . ') no-repeat center", backgroundSize: "16px 16px", opacity: 0.6}});

				var data = {
					action:    "wc_constant_contact_order_received_subscribe",
					security:  wc_checkout_params.update_order_review_nonce,
					order_id:  "' . esc_js( $order_id ) . '"
				};

				$.ajax({
					type:     "POST",
					url:      woocommerce_params.ajax_url,
					data:     data,
					success:  function( response ) {

						$section.removeClass( "processing" ).unblock();

						if ( response ) {

							$section.before( response );
							$section.remove();
						}
					},
					dataType: "html"
				});
				return false;
			});
		' );
	}


	/**
	 * Process the AJAX subscribe on the order received page
	 *
	 * @since 1.0
	 */
	public function ajax_process_order_received_subscribe() {

		// security check
		check_ajax_referer( 'update-order-review', 'security' );

		$order_id = ( ! empty( $_POST['order_id'] ) ) ? absint( $_POST['order_id'] ) : '';

		if ( ! $order_id ) {

			wc_add_notice( apply_filters( 'wc_constant_contact_order_received_subscribe_failure_message', __( 'Oops, something went wrong. Please contact us to subscribe.', 'ultimatewoo-pro' ) ), 'error' );
			wc_print_notices();

			wc_constant_contact()->log( __( 'Order Received Subscribe: No Order ID', 'ultimatewoo-pro' ) );

			die;
		}

		try {

			wc_constant_contact()->get_api()->subscribe_customer( $order_id );

			wc_add_notice( apply_filters( 'wc_constant_contact_order_received_subscribe_success_message', __( 'Thanks for subscribing!', 'ultimatewoo-pro' ) ) );

		} catch ( SV_WC_API_Exception $e ) {

			wc_add_notice( apply_filters( 'wc_constant_contact_order_received_subscribe_failure_message', __( 'Oops, something went wrong. Please contact us to subscribe.', 'ultimatewoo-pro' ) ), 'error' );

			$order = wc_get_order( $order_id );

			$order->add_order_note( sprintf( __( 'Failed to subscribe customer to email list: %s', 'ultimatewoo-pro' ), $e->getMessage() ) );

			wc_constant_contact()->log( sprintf( __( 'Order Received Subscribe: %s', 'ultimatewoo-pro' ), $e->getMessage() ) );
		}

		wc_print_notices();

		die;
	}


} // end \WC_Constant_Contact_Frontend class
