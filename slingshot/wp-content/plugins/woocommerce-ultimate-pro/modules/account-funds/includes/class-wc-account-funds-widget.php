<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Account_Funds_Widget
 */
class WC_Account_Funds_Widget extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'widget_account_funds', __( 'My Account Funds', 'ultimatewoo-pro' ) );
	}

	/**
	 * The widget
	 */
	public function widget( $args, $instance ) {
		if ( ! is_user_logged_in() ) {
			return;
		}

		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		?>
		<div class="woocommerce woocommerce-account-funds">
			<p><?php printf( __( 'You currently have <strong>%s</strong> worth of funds in your account.', 'ultimatewoo-pro' ), WC_Account_Funds::get_account_funds() ); ?></p>

			<p><a class="button" href="<?php echo get_permalink( wc_get_page_id( 'myaccount' ) ); ?>"><?php _e( 'Deposit Funds', 'ultimatewoo-pro' ); ?></a></p>
		</div>
		<?php
		echo $after_widget;
	}

	/**
	 * Update settings
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = wc_clean( $new_instance['title'] );
		return $instance;
	}

	/**
	 * Settings forms
	 */
	function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'My Account Funds', 'ultimatewoo-pro' );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
	}
}

register_widget( 'WC_Account_Funds_Widget' );