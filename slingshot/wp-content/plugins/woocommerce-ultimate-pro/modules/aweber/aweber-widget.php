<?php

/**
 * AWeber Widget
 *
 * @package  WooCommerce
 * @category Widgets
 * @author  WooThemes
 */
class WooCommerce_AWeber_Widget extends WP_Widget
{

	/** Variables to setup the widget. */
	var $widget_id;
	var $widget_name;
	var $widget_description;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {

		/* Widget variable settings. */
		$this->widget_description = __( 'Display Web Forms created on AWeber.', 'ultimatewoo-pro' );
		$this->widget_id = 'woocommerce_aweber_webform';
		$this->widget_name = __( 'WooCommerce AWeber WebForm', 'ultimatewoo-pro' );

		/* Widget settings. */
		$widget_ops = array( 'description' => $this->widget_description );

		/* Create the widget. */
		parent::__construct( $this->widget_id, $this->widget_name, $widget_ops );
	} // End __construct()


	/**
	 * widget function.
	 *
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	function widget( $args, $instance ) {
		//Because it's a Web Form we do not make use of titles ect as this can be set from AWeber dashboard
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters( 'widget_title', $instance[ 'title' ], $instance, $this->id_base );

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		$html = $this->get_stored_webform( $args, $instance );

		echo $html;

		/* After widget (defined by themes). */
		echo $after_widget;
	} // End widget()

	/**
	 * update function.
	 *
	 * @access public
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array $instance
	 */
	function update( $new_instance, $old_instance ) {
		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );

		// If the form has changed, clear the transient.
		if ( $new_instance[ 'formcode' ] != $old_instance[ 'formcode' ] ) {
			delete_transient( $this->id . '_webform' );
		}

		$instance[ 'formcode' ] = stripslashes( $new_instance[ 'formcode' ] );
		return $instance;
	} // End update()

	/**
	 * form function.
	 *
	 * @access public
	 * @param array $instance
	 * @return void
	 */
	function form( $instance ) {
		global $woocommerce_aweber;

		/* Set up some default widget settings. */
		$defaults = array(
			'title' => __( 'Subscribe to our Newsletter', 'ultimatewoo-pro' ),
			'formcode' => ''
		);

		$instance = wp_parse_args( (array)$instance, $defaults );

		$admin_options = get_option( $woocommerce_aweber->adminOptionsName );
		try {

			$aweber = $woocommerce_aweber->_get_aweber_api();
			$account = $aweber->getAccount( $admin_options[ 'access_token' ], $admin_options[ 'access_secret' ] );
			$webforms = $account->getWebForms();
		} catch ( Exception $e ) {
			$account = null;
		}
		if ( $account ) {
			$s_options = '';
			foreach ( $webforms as $this_webform ) {
				$s_options .= '<option value="' . $this_webform->url . '"' . selected( $this_webform->url, $instance[ 'formcode' ], false ) . '>' . $this_webform->name . '</option>' . "\n";
			}
			?>
			<!-- Widget Title: Text Input -->
			<p>
				<label
					for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (optional):', 'ultimatewoo-pro' ); ?></label>
				<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>"
				       value="<?php echo $instance[ 'title' ]; ?>" class="widefat"
				       id="<?php echo $this->get_field_id( 'title' ); ?>"/>
			</p>
			<p>
				<label
					for="<?php echo $this->get_field_id( 'formcode' ); ?>"><?php _e( 'Choose your Web Form to use:', 'ultimatewoo-pro' ) ?></label>
				<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'formcode' ) ); ?>"
				        name="<?php echo esc_attr( $this->get_field_name( 'formcode' ) ); ?>">
					<?php echo $s_options; ?>
				</select>
			</p>
			<?php
		} else {
			_e( 'Please authorise WooCommerce to access your AWeber account from the WooCommerce settings page.', 'ultimatewoo-pro' );
		}
	} // End form()

	/**
	 * get_stored_webform function.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $args
	 * @param array $instance
	 * @return string $html
	 */
	function get_stored_webform( $args, $instance ) {
		$html = '';

		$transient_key = $this->id . '_webform';

		if ( false === ( $html = get_transient( $transient_key ) ) ) {
			$html = $this->get_webform_html( $args, $instance );

			if ( $html != '' ) {
				set_transient( $transient_key, $html, 60 * 30 ); // 30 minute transient.
			}
		}

		return $html;
	} // End get_stored_webform()

	/**
	 * get_webform_html function.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $args
	 * @param array $instance
	 * @return string $formhtml
	 */
	function get_webform_html( $args, $instance ) {
		global $woocommerce_aweber;
		$admin_options = get_option( $woocommerce_aweber->adminOptionsName );
		$formhtml = '';

		try {
			$aweber = $woocommerce_aweber->_get_aweber_api();
			$account = $aweber->getAccount( $admin_options[ 'access_token' ], $admin_options[ 'access_secret' ] );
			$webforms = $account->getWebForms();
		} catch ( Exception $e ) {
			$account = null;
		}
		if ( $account ) {
			$formcode = $instance[ 'formcode' ];
			$webform = explode( '/', $formcode );
			$form_hash = $webform[ 6 ] % 100;
			$form_hash = ( ( $form_hash < 10 ) ? '0' : '' ) . $form_hash;
			//$prefix = ($this->_isSplitTest($webform)) ? 'split_' : '';
			$url = 'https://forms.aweber.com/form/' . $form_hash . '/' . $webform[ 6 ] . '.js';
			$formhtml = '<script type="text/javascript" src="' . $url . '"></script>';
		}

		return $formhtml;
	} // End get_webform_html()
} // WooCommerce_AWeber_Widget