<?php
/**
 * Subscribe to Newsletter Widget
 *
 * @package  WooCommerce
 * @category Widgets
 * @author   WooThemes
 */
class WC_Widget_Subscribe_To_Newsletter extends WP_Widget {

	/**
	 * Widget CSS class.
	 *
	 * @var string
	 */
	public $woo_widget_cssclass;

	/**
	 * Widget description.
	 *
	 * @var string
	 */
	public $woo_widget_description;

	/**
	 * Widget ID.
	 *
	 * @var string
	 */
	public $woo_widget_idbase;

	/**
	 * Widget name.
	 *
	 * @var string
	 */
	public $woo_widget_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->woo_widget_cssclass    = 'widget_subscribe_to_newsletter';
		$this->woo_widget_description = esc_html__( 'Allow users to subscribe to your MailChimp or Campaign Monitor lists.', 'ultimatewoo-pro' );
		$this->woo_widget_idbase      = 'woocommerce_subscribe_to_newsletter';
		$this->woo_widget_name        = esc_html__( 'WooCommerce Subscribe to Newsletter', 'ultimatewoo-pro' );

		$widget_ops = array(
			'classname'   => $this->woo_widget_cssclass,
			'description' => $this->woo_widget_description,
		);

		parent::__construct( $this->woo_widget_idbase, $this->woo_widget_name, $widget_ops );
	}

	/**
	 * Output the content of the widget.
	 *
	 * @param array $args Widget args
	 * @param array $args Widget options instance
	 */
	public function widget( $args, $instance ) {
		global $WC_Subscribe_To_Newsletter;

		if ( ! $WC_Subscribe_To_Newsletter->service ) {
			return;
		}

		extract( $args );

		$title     = isset( $instance['title'] ) ? $instance['title'] : __( 'Newsletter', 'ultimatewoo-pro' );
		$listid    = isset( $instance['list'] ) ? $instance['list'] : 'false';
		$show_name = ! empty( $instance['show_name'] );
		$title     = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		?>
		<form method="post" id="subscribeform" action="#subscribeform" class="woocommerce">
			<?php
			if ( isset( $_POST['newsletter_email'] ) ) {
				$email = wc_clean( $_POST['newsletter_email'] );
				$first = '';
				$last  = '';

				if ( isset( $_POST['newsletter_name'] ) ) {
					$name  = wc_clean( trim( $_POST['newsletter_name'] ) );
					$name  = explode( ' ', $name );
					$first = current( $name );
					$last  = end( $name );
					if ( $first == $last ) {
						$last = '';
					}
				}

				if ( ! is_email( $email ) ) {
					echo '<div class="woocommerce_error woocommerce-error">' . esc_html__( 'Please enter a valid email address.', 'ultimatewoo-pro' ) . '</div>';
				} else {
					$WC_Subscribe_To_Newsletter->service->subscribe( $first, $last, $email, $listid );
					echo '<div class="woocommerce_message woocommerce-message">' . esc_html__( 'Thanks for subscribing.', 'ultimatewoo-pro' ) . '</div>';
				}
			}
			?>
			<div>
				<?php if ( $show_name ) : ?>
					<div>
						<label class="screen-reader-text hidden" for="s"><?php esc_html_e( 'Your Name:', 'ultimatewoo-pro' ); ?></label>
						<input type="text" name="newsletter_name" id="newsletter_name" placeholder="<?php esc_attr_e( 'Your name', 'ultimatewoo-pro' ); ?>" />
					</div>
				<?php endif; ?>

				<div>
					<label class="screen-reader-text hidden" for="s"><?php esc_html_e( 'Email Address:', 'ultimatewoo-pro' ); ?></label>
					<input type="text" name="newsletter_email" id="newsletter_email" placeholder="<?php esc_attr_e( 'Your email address', 'ultimatewoo-pro' ); ?>" />
				</div>

				<input type="submit" class="button" id="newsletter_subscribe" value="<?php esc_attr_e( 'Subscribe', 'ultimatewoo-pro' ); ?>" />
			</div>
		</form>
		<?php

		echo $after_widget;
	}

	/**
	 * Update widget options on save.
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array Instance to save
	 */
	public function update( $new_instance, $old_instance ) {
		$instance['title']     = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['list']      = strip_tags( stripslashes( $new_instance['list'] ) );
		$instance['show_name'] = empty( $new_instance['show_name'] ) ? false : true;

		return $instance;
	}

	/**
	 * Outputs the options form on admin.
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		global $wpdb, $WC_Subscribe_To_Newsletter;

		if ( ! $WC_Subscribe_To_Newsletter->service ) {
			echo '<p>' . esc_html__( 'You must set up API details in WooCommerce > Settings > Newsletter before using this widget.', 'ultimatewoo-pro' ) . '</p>';
			return;
		}

		$lists = $WC_Subscribe_To_Newsletter->service->get_lists();
		?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'ultimatewoo-pro' ) ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php if ( isset( $instance['title'] ) ) { echo esc_attr( $instance['title'] ); } else { echo esc_attr__( 'Newsletter', 'ultimatewoo-pro' ); } ?>" /></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'show_name' ) ); ?>"><input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_name' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_name' ) ); ?>" <?php checked( ! empty( $instance['show_name'] ), true ); ?> /> <?php esc_html_e( 'Show Name Field?', 'ultimatewoo-pro' ) ?></label></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'list' ) ); ?>"><?php esc_html_e( 'List:', 'woothemes' ) ?></label>
				<?php
				echo '<select id="' . esc_attr( $this->get_field_id( 'list' ) ) . '" name="' . esc_attr( $this->get_field_name( 'list' ) ) . '" class="widefat">';
				if ( $lists ) {
					foreach ( $lists as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '" ' . ( $key === $instance['list'] ? 'selected="selected"' : '' ) . '>' . esc_html( $value ) . '</option>';
					}
				}
				echo '</select>';
				echo '<small>' . esc_html__( 'Choose a list to subscribe newsletter subscribers to or leave blank to use the list in your setting panel.', 'ultimatewoo-pro' ) . '</small>';
?>
			</p>
		<?php
	}
}
