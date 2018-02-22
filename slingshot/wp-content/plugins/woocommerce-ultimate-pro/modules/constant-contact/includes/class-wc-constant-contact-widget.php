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
 * A simple widget for displaying a signup for the admin-selected constant contact email list
 *
 * @since 1.0
 * @extends \WP_Widget
 */
class WC_Constant_Contact_Widget extends WP_Widget {


	/**
	 * Setup the widget options
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// set widget options
		$options = array(
			'classname'   => 'widget_wc_constant_contact',
			'description' => __( 'Allow your customers to subscribe to your Constant Contact email list.', 'ultimatewoo-pro' ),
		);

		// instantiate the widget
		parent::__construct( 'wc_constant_contact', __( 'WooCommerce Constant Contact', 'ultimatewoo-pro' ), $options );

		// add AJAX if widget is active
		if ( is_active_widget( false, false, $this->id_base ) ) {

			// enqueue js
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_widget_js' ) );

			// handle the subscribe form AJAX submit
			add_action( 'wp_ajax_wc_constant_contact_widget_subscribe',        array( $this, 'ajax_process_widget_subscribe' ) );
			add_action( 'wp_ajax_nopriv_wc_constant_contact_widget_subscribe', array( $this, 'ajax_process_widget_subscribe' ) );
		}
	}


	/**
	 * Render the widget
	 *
	 * @since 1.0
	 * @see WP_Widget::widget()
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		if ( ! wc_constant_contact()->get_api() ) {
			return;
		}

		extract( $args );

		$title   = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$list_id = $instance['list'];

		echo $before_widget;

		if ( $title ) {
			echo $before_title . wp_kses_post( $title ) . $after_title;
		}
		?>
			<form method="post" id="wc_constant_contact_subscribe_widget_form" action="#wc_constant_contact_subscribe_widget_form">
				<div>
					<label class="screen-reader-text hidden" for="s"><?php esc_html_e( 'Email Address', 'ultimatewoo-pro' ); ?>:</label>
					<input type="hidden" id="wc_constant_contact_subscribe_list_id" value="<?php echo esc_attr( $list_id ); ?>" />
					<input type="text" name="wc_constant_contact_subscribe_email" id="wc_constant_contact_subscribe_email" placeholder="<?php esc_attr_e( 'Your email address', 'ultimatewoo-pro' ); ?>" />
					<input type="submit" id="wc_constant_contact_subscribe" value="<?php esc_attr_e( 'Subscribe', 'ultimatewoo-pro' ); ?>" />
				</div>
			</form>
		<?php

		echo $after_widget;
	}


	/**
	 * Update the widget title & selected email list
	 *
	 * @since 1.0
	 * @see WP_Widget::update()
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		$instance['title'] = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['list']  = strip_tags( stripslashes( $new_instance['list'] ) );

		return $instance;
	}


	/**
	 * Render the admin form for the widget
	 *
	 * @since 1.0
	 * @see WP_Widget::form()
	 * @param array $instance
	 */
	public function form( $instance ) {

		if ( ! wc_constant_contact()->get_api() ) {
			?><p><?php esc_html_e( 'You must enter your API info in WooCommerce > Settings > Constant Contact before using this widget.', 'ultimatewoo-pro' ); ?></p><?php
			return;
		}

		try {

			$lists = array_merge( array( '' => '' ), wc_constant_contact()->get_api()->get_lists() );

		} catch ( SV_WC_API_Exception $e ) {

			$lists = array( '' => sprintf( __( 'Oops, something went wrong: %s', 'ultimatewoo-pro' ), $e->getMessage() ) );
		}

		?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'ultimatewoo-pro' ) ?>:</label>
				<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( ( isset( $instance['title'] ) ) ? $instance['title'] : __( 'Email List', 'ultimatewoo-pro' ) ); ?>" /></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'list' ) ); ?>"><?php esc_html_e( 'Email List', 'ultimatewoo-pro' ); ?></label>
				<?php
				echo '<select id="' . esc_attr( $this->get_field_id( 'list' ) ) .'" name="' . esc_attr( $this->get_field_name( 'list' ) ) .'" class="widefat">';
				if ( ! empty( $lists ) ) :
					foreach ( $lists as $list_id => $list_name ) :
						?><option value="<?php echo esc_attr( $list_id ); ?>" <?php selected( ( isset( $instance['list'] ) ) ? $instance['list'] : '', $list_id ); ?>><?php echo esc_html( $list_name ); ?></option><?php
					endforeach;
				endif;

				echo '</select>';

				echo '<small>' . esc_html__( 'Select an email list to subscribe customers to or leave blank to use the list defined under WooCommerce > Settings > Constant Contact.', 'ultimatewoo-pro' ) . '</small>';
				?>
			</p>
		<?php
	}


	/**
	 * Enqueue the widget JS
	 *
	 * @since 1.3
	 */
	public function enqueue_widget_js() {

		$nonce    = wp_create_nonce( 'wc_constant_contact' );

		$loader = wc_constant_contact()->get_framework_assets_url() . '/images/ajax-loader.gif';

		wc_enqueue_js( '
			/* Constant Contact AJAX Widget Subscribe */
			$( "#wc_constant_contact_subscribe_widget_form" ).submit( function( e ) {

				e.preventDefault();

				var $form = $( this );

				if ( $form.is( ".processing" ) ) return false;

				$form.addClass( "processing" ).block( { message: null, overlayCSS: { background: "#fff url(' . $loader . ') no-repeat center", backgroundSize: "16px 16px", opacity: 0.6 } } );

				var data = {
					action:   "wc_constant_contact_widget_subscribe",
					security: "' . $nonce . '",
					email:    $( "#wc_constant_contact_subscribe_email" ).val(),
					list_id:  $( "#wc_constant_contact_subscribe_list_id" ).val()
				};

				$.ajax({
					type:     "POST",
					url:      woocommerce_params.ajax_url,
					data:     data,
					dataType: "json",
					success:  function( response ) {

						$form.removeClass( "processing" ).unblock();

						// remove any previous messages
						$form.prev( ".woocommerce" ).remove();

						// show messages
						$form.before( response.data );

						if ( response.success ) {
							$form.remove();
						}
					}
				});
				return false;
			});
		' );
	}


	/**
	 * Process the widget AJAX subscribe
	 *
	 * @since 1.3
	 */
	public function ajax_process_widget_subscribe() {

		// security check
		check_ajax_referer( 'wc_constant_contact', 'security' );

		$email   = ( ! empty( $_POST['email'] ) ) ? wc_clean( $_POST['email'] ) : '';
		$list_id = ( ! empty( $_POST['list_id'] ) ) ? $_POST['list_id'] : null;

		if ( ! is_email( $email ) ) {

			$error = '<div class="woocommerce"><div class="woocommerce-error">' . __( 'Please enter a valid email address.', 'ultimatewoo-pro' ) . '</div></div>';

			wp_send_json_error( $error );

		} else {

			try {

				wc_constant_contact()->get_api()->subscribe( $email, $list_id );

				$success = '<div class="woocommerce"><div class="woocommerce-message">' . __( 'Thanks for subscribing.', 'ultimatewoo-pro' ) . '</div></div>';

				wp_send_json_success( $success );

			} catch ( SV_WC_API_Exception $e ) {

				$error = '<div class="woocommerce"><div class="woocommerce-error">' . __( 'Oops, something went wrong. Please try again later.', 'ultimatewoo-pro' ) . '</div>';

				wp_send_json_error( $error );

				wc_constant_contact()->log( sprintf( __( 'Widget Signup: %s', 'ultimatewoo-pro' ), $e->getMessage() ) );
			}
		}
	}


} // end \WC_Constant_Contact_Widget class
