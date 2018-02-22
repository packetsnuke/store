<?php
/**
 * The widget class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Products_Compare_Widget extends WP_Widget {

	/**
	 * Init
	 *
	 * @access public
	 * @since 1.0.0
	 * @return bool
	 */
	public function __construct() {

		$widget_ops = array( 'classname' => 'woocommerce woocommerce-products-compare-widget', 'description' => __( 'Displays a running list of compared products.', 'ultimatewoo-pro' ) );

		parent::__construct( 'compared_products', __( 'WooCommerce Products Compare', 'ultimatewoo-pro' ), $widget_ops );
	}

	public function widget( $args, $instance ) {

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Compared Products', 'ultimatewoo-pro' ) : $instance['title'], $instance, $this->id_base );

		$html = '';

		$html .= $args['before_widget'];

		if ( $title ) {
			$html .= $args['before_title'] . $title . $args['after_title'];
		}

		$products = WC_Products_Compare_Frontend::get_compared_products();

		$endpoint = WC_Products_Compare_Frontend::get_endpoint();

		if ( $products ) {
			
			$html .= '<ul>' . PHP_EOL;

			foreach ( $products as $product ) {
				$product = wc_get_product( $product );

				if ( ! WC_Products_Compare::is_product( $product ) ) {
					continue;
				}

				$post = get_post( $product->get_id() );

				$html .= '<li data-product-id="' . esc_attr( $product->get_id() ) . '">' . PHP_EOL;

				$html .= '<a href="' . get_permalink( $product->get_id() ) . '" title="' . esc_attr( $post->post_title ) . '" class="product-link">' . PHP_EOL;
										
				$html .= $product->get_image( 'shop_thumbnail' ) . PHP_EOL;

				$html .= '<h3>' . $post->post_title . '</h3>' . PHP_EOL;

				$html .= '</a>' . PHP_EOL;

				$html .= '<a href="#" title="' . esc_attr( 'Remove Product', 'ultimatewoo-pro' ) . '" class="remove-compare-product" data-remove-id="' . esc_attr( $product->get_id() ) . '">' . __( 'Remove Product', 'ultimatewoo-pro' ) . '</a>' . PHP_EOL;

				$html .= '</li>' . PHP_EOL;
			}

			$html .= '</ul>' . PHP_EOL;
		} else {
			$html .= '<p class="no-products">' . __( 'Add some products to compare.', 'ultimatewoo-pro' ) . '</p>' . PHP_EOL;

		}

		$html .= '<a href="' . esc_url( site_url() . '/' . $endpoint  ) . '" title="' . esc_attr( 'Compare Products', 'ultimatewoo-pro' ) . '" class="button woocommerce-products-compare-widget-compare-button">' . __( 'Compare Products', 'ultimatewoo-pro' ) . '</a>' . PHP_EOL;

		$html .= $args['after_widget'];

		echo $html;

		return true;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	public function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );

		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Compare Products', 'ultimatewoo-pro' );
	?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'ultimatewoo-pro' ); ?></label>

			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />

		</p>
	<?php
		return true;
	}
}
