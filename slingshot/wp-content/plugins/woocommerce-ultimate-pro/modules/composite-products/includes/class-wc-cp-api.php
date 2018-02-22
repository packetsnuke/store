<?php
/**
 * WC_CP_API class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A relic containing deprecated methods that have now been moved in the right places.
 *
 * @class    WC_CP_API
 * @version  3.8.0
 */
class WC_CP_API {

	/**
	 * The single instance of the class.
	 * @var WC_CP_API
	 *
	 * @since 3.7.0
	 */
	protected static $_instance = null;

	/**
	 * Main WC_CP_API instance.
	 *
	 * Ensures only one instance of WC_CP_API is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_CP_API
	 * @since  3.7.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 3.7.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'ultimatewoo-pro' ), '3.7.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 3.7.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'ultimatewoo-pro' ), '3.7.0' );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Pure Silence.
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	public function apply_composited_product_filters( $product, $component_id, $composite ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'WC_CP_Products::add_filters()' );

		$component_option = $composite->get_component_option( $component_id, WC_CP_Core_Compatibility::get_id( $product ) );

		return WC_CP_Products::add_filters( $component_option );
	}
	public function remove_composited_product_filters() {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'WC_CP_Products::remove_filters()' );
		return WC_CP_Products::remove_filters();
	}
	public function get_layout_options() {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'WC_Product_Composite::get_layout_options()' );
		return WC_Product_Composite::get_layout_options();
	}
	public function get_layout_tooltip( $layout_id ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'WC_Product_Composite::get_layout_description()' );
		return WC_Product_Composite::get_layout_description();
	}
	public function get_selected_layout_option( $layout ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'WC_Product_Composite::get_layout_option()' );
		return WC_Product_Composite::get_layout_option();
	}
	public function get_options_styles() {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'WC_CP_Component::get_options_styles()' );
		return WC_CP_Component::get_options_styles();
	}

	public function get_options_style( $style_id ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'WC_CP_Component::get_options_style_data()' );
		return WC_CP_Component::get_options_style_data( $style_id );
	}

	public function options_style_supports( $style_id, $what ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'WC_CP_Component::options_style_supports()' );
		return WC_CP_Component::options_style_supports( $style_id, $what );
	}
	public function get_component_options( $component_data, $query_args = array() ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'WC_CP_Component::query_component_options()' );
		return WC_CP_Component::query_component_options( $component_data, $args );
	}
	public function get_composited_item_price_string_price( $price, $args = array() ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'WC_CP_Helpers::format_raw_price()' );
		return WC_CP_Helpers::format_raw_price( $price, $args = array() );
	}
	public function get_composited_product_price( $product, $price = '' ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'WC_CP_Products::get_product_display_price()' );
		return WC_CP_Products::get_product_display_price( $product, $price = '' );
	}
	public function get_composited_item_availability( $product, $quantity ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'WC_CP_Product::get_availability()' );

		$availability = $class = '';
		if ( $product->managing_stock() ) {
			if ( $product->is_in_stock() && $product->get_total_stock() > get_option( 'woocommerce_notify_no_stock_amount' ) && $product->get_total_stock() >= $quantity ) {
				switch ( get_option( 'woocommerce_stock_format' ) ) {
					case 'no_amount' :
						$availability = __( 'In stock', 'woocommerce' );
					break;
					case 'low_amount' :
						if ( $product->get_total_stock() <= get_option( 'woocommerce_notify_low_stock_amount' ) ) {
							$availability = sprintf( __( 'Only %s left in stock', 'woocommerce' ), $product->get_total_stock() );
							if ( $product->backorders_allowed() && $product->backorders_require_notification() ) {
								$availability .= ' ' . __( '(can be backordered)', 'woocommerce' );
							}
						} else {
							$availability = __( 'In stock', 'woocommerce' );
						}
					break;
					default :
						$availability = sprintf( __( '%s in stock', 'woocommerce' ), $product->get_total_stock() );
						if ( $product->backorders_allowed() && $product->backorders_require_notification() ) {
							$availability .= ' ' . __( '(can be backordered)', 'woocommerce' );
						}
					break;
				}
				$class = 'in-stock';
			} elseif ( $product->backorders_allowed() && $product->backorders_require_notification() ) {
				if ( $product->get_total_stock() >= $quantity || get_option( 'woocommerce_stock_format' ) == 'no_amount' || $product->get_total_stock() <= 0 ) {
					$availability = __( 'Available on backorder', 'woocommerce' );
				} else {
					$availability = __( 'Available on backorder', 'woocommerce' ) . ' ' . sprintf( __( '(only %s left in stock)', 'ultimatewoo-pro' ), $product->get_total_stock() );
				}
				$class = 'available-on-backorder';
			} elseif ( $product->backorders_allowed() ) {
				$availability = __( 'In stock', 'woocommerce' );
				$class        = 'in-stock';
			} else {
				if ( $product->is_in_stock() && $product->get_total_stock() > get_option( 'woocommerce_notify_no_stock_amount' ) ) {
					if ( get_option( 'woocommerce_stock_format' ) == 'no_amount' ) {
						$availability = __( 'Insufficient stock', 'ultimatewoo-pro' );
					} else {
						$availability = __( 'Insufficient stock', 'ultimatewoo-pro' ) . ' ' . sprintf( __( '(only %s left in stock)', 'ultimatewoo-pro' ), $product->get_total_stock() );
					}
					$class = 'out-of-stock';
				} else {
					$availability = __( 'Out of stock', 'woocommerce' );
					$class        = 'out-of-stock';
				}
			}

		} elseif ( ! $product->is_in_stock() ) {
			$availability = __( 'Out of stock', 'woocommerce' );
			$class        = 'out-of-stock';
		}

		return apply_filters( 'woocommerce_composited_product_availability', array( 'availability' => $availability, 'class' => $class ), $product );
	}
}
