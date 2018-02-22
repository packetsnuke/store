<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

/** Epo Widget (Used for for echoing a custom action) **/
class TC_EPO_Widget extends WP_Widget {

	/**
	 * TC_EPO_Widget constructor.
	 */
	function __construct() {
		$widget_ops = array( 'classname' => 'tc_epo_show_widget', 'description' => __( 'Echo a custom action', 'woocommerce-tm-extra-product-options' ) );

		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'tc_epo_show_widget' );

		parent::__construct( 'tc_epo_show_widget', __( 'EPO custom action', 'woocommerce-tm-extra-product-options' ), $widget_ops, $control_ops );
	}

	/**
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {

		$cache = wp_cache_get( 'widget_recent_posts', 'widget' );

		if ( !is_array( $cache ) ) {
			$cache = array();
		}

		if ( !isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];

			return;
		}

		ob_start();
		extract( $args );

		$title = empty( $instance['title'] ) ? '' : $instance['title'];
		$action = empty( $instance['action'] ) ? 'tc_show_epo' : $instance['action'];

		echo $before_widget;
		echo $title;
		do_action( $action );

		echo $after_widget;

		$cache[ $args['widget_id'] ] = ob_get_flush();
		wp_cache_set( 'widget_recent_posts', $cache, 'widget' );
	}

	/**
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['action'] = strip_tags( $new_instance['action'] );

		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_recent_entries'] ) ) {
			delete_option( 'widget_recent_entries' );
		}

		return $instance;
	}

	/**
	 *
	 */
	function flush_widget_cache() {
		wp_cache_delete( 'widget_recent_posts', 'widget' );
	}

	/**
	 * @param array $instance
	 */
	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$action = isset( $instance['action'] ) ? esc_attr( $instance['action'] ) : '';

		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'woocommerce-tm-extra-product-options' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'action' ); ?>"><?php _e( 'Custom action:', 'woocommerce-tm-extra-product-options' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'action' ); ?>"
                   name="<?php echo $this->get_field_name( 'action' ); ?>" type="text" value="<?php echo $action; ?>"/>
        </p>

		<?php
	}
}

/**
 *
 */
function tc_epo_widget() {
	register_widget( 'TC_EPO_Widget' );
}