<?php
/**
 * WC_CSP_Condition_Package_Category class
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
 * Category in Package Condition.
 *
 * @class   WC_CSP_Condition_Package_Category
 * @version 1.1.3
 */
class WC_CSP_Condition_Package_Category extends WC_CSP_Condition {

	public function __construct() {

		$this->id                            = 'category_in_package';
		$this->title                         = __( 'Category', 'ultimatewoo-pro' );
		$this->supported_global_restrictions = array( 'shipping_methods', 'shipping_countries' );
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

		if ( ! empty( $args[ 'package' ] ) ) {
			$package = $args[ 'package' ];
		} else {
			return false;
		}

		$package_count                = ! empty( $args[ 'package_count' ] ) ? absint( $args[ 'package_count' ] ) : 1;
		$contains_qualifying_products = false;

		foreach ( $package[ 'contents' ] as $cart_item_key => $cart_item_data ) {

			$product_category_terms = get_the_terms( $cart_item_data[ 'product_id' ], 'product_cat' );

			if ( $product_category_terms && ! is_wp_error( $product_category_terms ) ) {
				foreach( $product_category_terms as $product_category_term ) {
					if ( in_array( $product_category_term->term_id, $data[ 'value' ] ) ) {

						$contains_qualifying_products = true;

						if ( $data[ 'modifier' ] === 'in' ) {
							break 2;
						}
					}
				}
			}
		}

		if ( $data[ 'modifier' ] === 'in' && $contains_qualifying_products ) {

			$term_names = array();

			foreach ( $data[ 'value' ] as $term_id ) {

				$term = get_term_by( 'id', $term_id, 'product_cat' );

				if ( $term ) {
					$term_names[] = $term->name;
				}
			}

			if ( count( $term_names ) == 1 ) {

				return sprintf( _n( 'remove all products in the &quot;%s&quot; category from your cart', 'remove all products in the &quot;%s&quot; category from this shipment', $package_count, 'ultimatewoo-pro' ), current( $term_names ) );

			} else {

				$string = '';

				for ( $i = 1; $i < count( $term_names ) - 1; $i++ ) {

					/* translators: Used to stitch together product category names */
					$string = sprintf( __( '%1$s, &quot;%2$s&quot;', 'ultimatewoo-pro' ), $string, $term_names[ $i ] );
				}

					/* translators: Used to stitch together product category names - last name */
				$string = sprintf( __( '%1$s, and &quot;%2$s&quot;', 'ultimatewoo-pro' ), $string, end( $term_names ) );

				return sprintf( _n( 'remove all products in the %s categories from your cart', 'remove all products in the %s categories from this shipment', $package_count, 'ultimatewoo-pro' ), $string );
			}

		} elseif ( $data[ 'modifier' ] === 'not-in' && ! $contains_qualifying_products ) {
			return __( 'purchase a qualifying product', 'ultimatewoo-pro' );
		}

		return false;
	}

	/**
	 * Evaluate if a condition field is in effect or not.
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

		if ( ! empty( $args[ 'package' ] ) ) {
			$package = $args[ 'package' ];
		} else {
			return true;
		}

		$contains_qualifying_products = false;

		foreach ( $package[ 'contents' ] as $cart_item_key => $cart_item_data ) {

			$product_category_terms = get_the_terms( $cart_item_data[ 'product_id' ], 'product_cat' );

			if ( $product_category_terms && ! is_wp_error( $product_category_terms ) ) {
				foreach( $product_category_terms as $product_category_term ) {
					if ( in_array( $product_category_term->term_id, $data[ 'value' ] ) ) {

						$contains_qualifying_products = true;

						if ( $data[ 'modifier' ] === 'in' ) {
							break 2;
						}
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
	 * Get categories-in-package condition content for global restrictions.
	 *
	 * @param  int    $index
	 * @param  int    $condition_index
	 * @param  array  $condition_data
	 * @return str
	 */
	public function get_admin_fields_html( $index, $condition_index, $condition_data ) {

		$categories = array();
		$modifier   = '';

		if ( ! empty( $condition_data[ 'modifier' ] ) ) {
			$modifier = $condition_data[ 'modifier' ];
		}

		if ( ! empty( $condition_data[ 'value' ] ) ) {
			$categories = $condition_data[ 'value' ];
		}

		$product_categories = ( array ) get_terms( 'product_cat', array( 'get' => 'all' ) );

		?>
		<input type="hidden" name="restriction[<?php echo $index; ?>][conditions][<?php echo $condition_index; ?>][condition_id]" value="<?php echo $this->id; ?>" />
		<div class="condition_modifier">
			<select name="restriction[<?php echo $index; ?>][conditions][<?php echo $condition_index; ?>][modifier]">
				<option value="in" <?php selected( $modifier, 'in', true ) ?>><?php echo __( 'in package', 'ultimatewoo-pro' ); ?></option>
				<option value="not-in" <?php selected( $modifier, 'not-in', true ) ?>><?php echo __( 'not in package', 'ultimatewoo-pro' ); ?></option>
			</select>
		</div>
		<div class="condition_value">
			<select name="restriction[<?php echo $index; ?>][conditions][<?php echo $condition_index; ?>][value][]" style="width:80%;" class="multiselect <?php echo WC_CSP_Core_Compatibility::is_wc_version_gte_2_3() ? 'wc-enhanced-select' : 'chosen_select'; ?>" multiple="multiple" data-placeholder="<?php _e( 'Select categories&hellip;', 'ultimatewoo-pro' ); ?>">
				<?php
					foreach ( $product_categories as $product_category )
						echo '<option value="' . $product_category->term_id . '" ' . selected( in_array( $product_category->term_id, $categories ), true, false ).'>' . $product_category->name . '</option>';
				?>
			</select>
		</div><?php
	}

}
