<?php
/**
 * WC_CSP_Condition_Cart_Shipping_Class class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Conditional Shipping and Payments
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shipping Class in Cart Condition.
 *
 * @class   WC_CSP_Condition_Cart_Shipping_Class
 * @version 1.1.3
 * @author  SomewhereWarm
 */
class WC_CSP_Condition_Cart_Shipping_Class extends WC_CSP_Condition {

	public function __construct() {

		$this->id                            = 'shipping_class_in_cart';
		$this->title                         = __( 'Shipping Class', 'ultimatewoo-pro' );
		$this->supported_global_restrictions = array( 'payment_gateways' );
	}

	/**
	 * Return condition field-specific resolution message which is combined along with others into a single restriction "resolution message".
	 *
	 * @param  array  $data   condition field data
	 * @param  array  $args   optional arguments passed by restriction
	 * @return string|false
	 */
	public function get_condition_resolution( $data, $args ) {

		// Empty conditions always return false (not evaluated).
		if ( empty( $data[ 'value' ] ) ) {
			return false;
		}

		$cart_contents = WC()->cart->get_cart();

		if ( empty( $cart_contents ) ) {
			return false;
		}

		$qualifying_products = array();

		foreach ( $cart_contents as $cart_item_key => $cart_item_data ) {

			$product           = $cart_item_data[ 'data' ];
			$shipping_class_id = $product->get_shipping_class_id();

			if ( $shipping_class_id ) {
				if ( in_array( $shipping_class_id, $data[ 'value' ] ) ) {
					$qualifying_products[] = $product->get_title();
				}
			}
		}

		if ( $data[ 'modifier' ] === 'in' && ! empty( $qualifying_products ) ) {

			if ( count( $qualifying_products ) == 1 ) {

				return sprintf( __( 'remove &quot;%s&quot; from your cart', 'ultimatewoo-pro' ), current( $qualifying_products ) );

			} else {

				$string = '';

				for ( $i = 1; $i < count( $qualifying_products ) - 1; $i++ ) {

					/* translators: Used to stitch together product names */
					$string = sprintf( __( '%1$s, &quot;%2$s&quot;', 'ultimatewoo-pro' ), $string, $qualifying_products[ $i ] );
				}

				/* translators: Used to stitch together product names - last name */
				$string = sprintf( __( '%1$s, and &quot;%2$s&quot;', 'ultimatewoo-pro' ), $string, end( $qualifying_products ) );

				return sprintf( __( 'remove %s from your cart', 'ultimatewoo-pro' ), $string );
			}

		} elseif ( $data[ 'modifier' ] === 'not-in' && empty( $qualifying_products ) ) {
			return __( 'purchase a qualifying product', 'ultimatewoo-pro' );
		}

		return false;
	}

	/**
	 * Evaluate if the condition is in effect or not.
	 *
	 * @param  array  $data   condition field data
	 * @param  array  $args   optional arguments passed by restrictions
	 * @return boolean
	 */
	public function check_condition( $data, $args ) {

		// Empty conditions always apply (not evaluated).
		if ( empty( $data[ 'value' ] ) ) {
			return true;
		}

		$product_shipping_class_ids = array();

		if ( is_checkout_pay_page() ) {

			global $wp;

			if ( isset( $wp->query_vars[ 'order-pay' ] ) ) {

				$order_id = $wp->query_vars[ 'order-pay' ];
				$order    = wc_get_order( $order_id );

				if ( $order ) {

					$order_items = $order->get_items( 'line_item' );

					if ( ! empty( $order_items ) ) {

						foreach ( $order_items as $order_item ) {

							$product = $order->get_product_from_item( $order_item );

							if ( $product ) {
								$shipping_class_id = $product->get_shipping_class_id();

								if ( $shipping_class_id ) {
									$product_shipping_class_ids[] = $shipping_class_id;
								}
							}
						}
					}
				}
			}

		} else {

			$cart_contents = WC()->cart->get_cart();

			if ( ! empty( $cart_contents ) ) {
				foreach ( $cart_contents as $cart_item_key => $cart_item_data ) {

					$product           = $cart_item_data[ 'data' ];
					$shipping_class_id = $product->get_shipping_class_id();

					if ( $shipping_class_id ) {
						$product_shipping_class_ids[] = $shipping_class_id;
					}
				}
			}
		}

		$contains_qualifying_products = false;

		if ( ! empty( $product_shipping_class_ids ) ) {
			foreach ( $product_shipping_class_ids as $product_shipping_class_id ) {

				if ( in_array( $product_shipping_class_id, $data[ 'value' ] ) ) {

					$contains_qualifying_products = true;

					if ( $data[ 'modifier' ] === 'in' ) {
						break;
					}
				}
			}
		}

		if ( $data[ 'modifier' ] === 'in' && $contains_qualifying_products ) {
			return true;
		} elseif ( $data[ 'modifier' ] === 'not-in' && ! $contains_qualifying_products ) {
			return true;
		}

		return false;
	}

	/**
	 * Validate, process and return condition fields.
	 *
	 * @param  array  $posted_condition_data
	 * @return array
	 */
	public function process_admin_fields( $posted_condition_data ) {

		$processed_condition_data = array();

		if ( ! empty( $posted_condition_data[ 'value' ] ) ) {
			$processed_condition_data[ 'condition_id' ] = $this->id;
			$processed_condition_data[ 'value' ]        = array_map( 'intval', $posted_condition_data[ 'value' ] );
			$processed_condition_data[ 'modifier' ]     = stripslashes( $posted_condition_data[ 'modifier' ] );

			return $processed_condition_data;
		}

		return false;
	}

	/**
	 * Get shipping-class-in-cart condition content for global restrictions.
	 *
	 * @param  int    $index
	 * @param  int    $condition_index
	 * @param  array  $condition_data
	 * @return str
	 */
	public function get_admin_fields_html( $index, $condition_index, $condition_data ) {

		$modifier         = '';
		$shipping_classes = array();

		if ( ! empty( $condition_data[ 'modifier' ] ) ) {
			$modifier = $condition_data[ 'modifier' ];
		}

		if ( ! empty( $condition_data[ 'value' ] ) ) {
			$shipping_classes = $condition_data[ 'value' ];
		}

		$product_shipping_classes = ( array ) get_terms( 'product_shipping_class', array( 'get' => 'all' ) );

		?>
		<input type="hidden" name="restriction[<?php echo $index; ?>][conditions][<?php echo $condition_index; ?>][condition_id]" value="<?php echo $this->id; ?>" />
		<div class="condition_modifier">
			<select name="restriction[<?php echo $index; ?>][conditions][<?php echo $condition_index; ?>][modifier]">
				<option value="in" <?php selected( $modifier, 'in', true ) ?>><?php echo __( 'in cart', 'ultimatewoo-pro' ); ?></option>
				<option value="not-in" <?php selected( $modifier, 'not-in', true ) ?>><?php echo __( 'not in cart', 'ultimatewoo-pro' ); ?></option>
			</select>
		</div>
		<div class="condition_value">
			<select name="restriction[<?php echo $index; ?>][conditions][<?php echo $condition_index; ?>][value][]" style="80%;" class="multiselect <?php echo WC_CSP_Core_Compatibility::is_wc_version_gte_2_3() ? 'wc-enhanced-select' : 'chosen_select'; ?>" multiple="multiple" data-placeholder="<?php _e( 'Select shipping classes&hellip;', 'ultimatewoo-pro' ); ?>">
				<?php
					foreach ( $product_shipping_classes as $shipping_class ) {
						echo '<option value="' . $shipping_class->term_id . '" ' . selected( in_array( $shipping_class->term_id, $shipping_classes ), true, false ).'>' . $shipping_class->name . '</option>';
					}
				?>
			</select>
		</div>
		<?php
	}
}
