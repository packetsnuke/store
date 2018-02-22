<?php
/**
 * WC_PB_Core_Compatibility class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    4.7.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Functions for WC core back-compatibility.
 *
 * @class    WC_PB_Core_Compatibility
 * @version  5.3.0
 */
class WC_PB_Core_Compatibility {

	/**
	 * Cache 'gte' comparison results.
	 * @var array
	 */
	private static $is_wc_version_gte = array();

	/**
	 * Cache 'gt' comparison results.
	 * @var array
	 */
	private static $is_wc_version_gt = array();

	/**
	 * Helper method to get the version of the currently installed WooCommerce.
	 *
	 * @since  4.7.6
	 *
	 * @return string
	 */
	private static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Returns true if the installed version of WooCommerce is 3.1 or greater.
	 *
	 * @since  5.4.0
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_3_1() {
		return self::is_wc_version_gte( '3.1' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.7 or greater.
	 *
	 * @since  5.0.0
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_7() {
		return self::is_wc_version_gte( '2.7' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.6 or greater.
	 *
	 * @since  5.0.0
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_6() {
		return self::is_wc_version_gte( '2.6' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.5 or greater.
	 *
	 * @since  4.10.2
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_5() {
		return self::is_wc_version_gte( '2.5' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.4 or greater.
	 *
	 * @since  4.10.2
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_4() {
		return self::is_wc_version_gte( '2.4' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.3 or greater.
	 *
	 * @since  4.7.6
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_3() {
		return self::is_wc_version_gte( '2.3' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.2 or greater.
	 *
	 * @since  4.7.6
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_2() {
		return self::is_wc_version_gte( '2.2' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @since  5.2.0
	 *
	 * @param  string  $version the version to compare
	 * @return boolean true if the installed version of WooCommerce is > $version
	 */
	public static function is_wc_version_gte( $version ) {
		if ( ! isset( self::$is_wc_version_gte[ $version ] ) ) {
			self::$is_wc_version_gte[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>=' );
		}
		return self::$is_wc_version_gte[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than $version.
	 *
	 * @since  4.7.6
	 *
	 * @param  string  $version the version to compare
	 * @return boolean true if the installed version of WooCommerce is > $version
	 */
	public static function is_wc_version_gt( $version ) {
		if ( ! isset( self::$is_wc_version_gt[ $version ] ) ) {
			self::$is_wc_version_gt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
		}
		return self::$is_wc_version_gt[ $version ];
	}

	/**
	 * Get the WC Product instance for a given product ID or post.
	 *
	 * get_product() is soft-deprecated in WC 2.2
	 *
	 * @since 4.7.6
	 *
	 * @param  bool|int|string|WP_Post  $the_product
	 * @param  array                    $args
	 * @return WC_Product
	 */
	public static function wc_get_product( $the_product = false, $args = array() ) {
		if ( self::is_wc_version_gte_2_2() ) {
			return wc_get_product( $the_product, $args );
		} else {
			return get_product( $the_product, $args );
		}
	}

	/**
	 * Get all product cats for a product by ID, including hierarchy.
	 *
	 * @since  4.13.1
	 *
	 * @param  int  $product_id
	 * @return array
	 */
	public static function wc_get_product_cat_ids( $product_id ) {
		if ( self::is_wc_version_gte_2_5() ) {
			$product_cats = wc_get_product_cat_ids( $product_id );
		} else {

			$product_cats = wp_get_post_terms( $product_id, 'product_cat', array( "fields" => "ids" ) );

			foreach ( $product_cats as $product_cat ) {
				$product_cats = array_merge( $product_cats, get_ancestors( $product_cat, 'product_cat' ) );
			}
		}

		return $product_cats;
	}

	/**
	 * Wrapper for wp_get_post_terms which supports ordering by parent.
	 *
	 * @since  4.13.1
	 *
	 * @param  int     $product_id
	 * @param  string  $taxonomy
	 * @param  array   $args
	 * @return array
	 */
	public static function wc_get_product_terms( $product_id, $attribute_name, $args ) {
		if ( self::is_wc_version_gte_2_3() ) {
			return wc_get_product_terms( $product_id, $attribute_name, $args );
		} else {

			$orderby = wc_attribute_orderby( sanitize_title( $attribute_name ) );

			switch ( $orderby ) {
				case 'name' :
					$args = array( 'orderby' => 'name', 'hide_empty' => false, 'menu_order' => false );
				break;
				case 'id' :
					$args = array( 'orderby' => 'id', 'order' => 'ASC', 'menu_order' => false );
				break;
				case 'menu_order' :
					$args = array( 'menu_order' => 'ASC' );
				break;
			}

			$terms = get_terms( sanitize_title( $attribute_name ), $args );

			return $terms;
		}
	}

	/**
	 * Get rounding precision.
	 *
	 * @since  4.14.6
	 *
	 * @return int
	 */
	public static function wc_get_rounding_precision( $price_decimals = false ) {
		if ( false === $price_decimals ) {
			$price_decimals = wc_get_price_decimals();
		}
		return absint( $price_decimals ) + 2;
	}

	/**
	 * Return the number of decimals after the decimal point.
	 *
	 * @since  4.13.1
	 *
	 * @return int
	 */
	public static function wc_get_price_decimals() {
		if ( self::is_wc_version_gte_2_3() ) {
			return wc_get_price_decimals();
		} else {
			return absint( get_option( 'woocommerce_price_num_decimals', 2 ) );
		}
	}

	/**
	 * Output a list of variation attributes for use in the cart forms.
	 *
	 * @since 4.13.1
	 *
	 * @param array  $args
	 */
	public static function wc_dropdown_variation_attribute_options( $args = array() ) {
		return wc_dropdown_variation_attribute_options( $args );
	}

	/**
	 * Display a WooCommerce help tip.
	 *
	 * @since  4.14.0
	 *
	 * @param  string  $tip
	 * @return string
	 */
	public static function wc_help_tip( $tip ) {

		if ( self::is_wc_version_gte_2_5() ) {
			return wc_help_tip( $tip );
		} else {
			return '<img class="help_tip woocommerce-help-tip" data-tip="' . $tip . '" src="' . WC()->plugin_url() . '/assets/images/help.png" />';
		}
	}

	/**
	 * Back-compat wrapper for 'wc_variation_attribute_name'.
	 *
	 * @since  5.0.2
	 *
	 * @param  string  $attribute_name
	 * @return string
	 */
	public static function wc_variation_attribute_name( $attribute_name ) {
		if ( self::is_wc_version_gte_2_6() ) {
			return wc_variation_attribute_name( $attribute_name );
		} else {
			return 'attribute_' . sanitize_title( $attribute_name );
		}
	}

	/**
	 * Back-compat wrapper for 'WC_Product_Factory::get_product_type'.
	 *
	 * @since  5.2.0
	 *
	 * @param  mixed  $product_id
	 * @return mixed
	 */
	public static function get_product_type( $product_id ) {
		$product_type = false;
		if ( $product_id ) {
			if ( self::is_wc_version_gte_2_7() ) {
				$product_type = WC_Product_Factory::get_product_type( $product_id );
			} else {
				$terms        = get_the_terms( $product_id, 'product_type' );
				$product_type = ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';
			}
		}
		return $product_type;
	}

	/**
	 * Back-compat wrapper for 'get_parent_id'.
	 *
	 * @since  5.1.0
	 *
	 * @param  WC_Product  $product
	 * @return mixed
	 */
	public static function get_parent_id( $product ) {
		if ( self::is_wc_version_gte_2_7() ) {
			return $product->get_parent_id();
		} else {
			return $product->is_type( 'variation' ) ? absint( $product->id ) : 0;
		}
	}

	/**
	 * Back-compat wrapper for 'get_id'.
	 *
	 * @since  5.1.0
	 *
	 * @param  WC_Product  $product
	 * @return mixed
	 */
	public static function get_id( $product ) {
		if ( self::is_wc_version_gte_2_7() ) {
			return $product->get_id();
		} else {
			return $product->is_type( 'variation' ) ? absint( $product->variation_id ) : absint( $product->id );
		}
	}

	/**
	 * Back-compat wrapper for getting CRUD object props directly.
	 *
	 * @since  5.1.0
	 *
	 * @param  WC_Data  $obj
	 * @param  string   $name
	 * @param  string   $context
	 * @return mixed
	 */
	public static function get_prop( $obj, $name, $context = 'edit' ) {
		if ( self::is_wc_version_gte_2_7() ) {
			$get_fn = 'get_' . $name;
			return is_callable( array( $obj, $get_fn ) ) ? $obj->$get_fn( $context ) : $obj->get_meta( '_wc_pb_' . $name, true );
		} else {

			if ( 'status' === $name ) {
				$value = isset( $obj->post->post_status ) ? $obj->post->post_status : null;
			} elseif ( 'short_description' === $name ) {
				$value = isset( $obj->post->post_excerpt ) ? $obj->post->post_excerpt : null;
			} elseif ( 'name' === $name ) {
				$value = isset( $obj->post->post_title ) ? $obj->post->post_title : null;
			} else {
				$value = isset( $obj->$name ) ? $obj->$name : '';
			}

			return $value;
		}
	}

	/**
	 * Back-compat wrapper for setting CRUD object props directly.
	 *
	 * @since  5.1.0
	 *
	 * @param  WC_Data  $obj
	 * @param  string   $name
	 * @param  mixed    $value
	 * @return void
	 */
	public static function set_prop( $obj, $name, $value ) {
		if ( self::is_wc_version_gte_2_7() ) {
			$set_fn = 'set_' . $name;
			if ( is_callable( array( $obj, $set_fn ) ) ) {
				$obj->$set_fn( $value );
			} else {
				$obj->add_meta_data( '_wc_pb_' . $name, $value, true );
			}
		} else {
			if ( 'name' === $name ) {
				if ( isset( $obj->post->post_title ) ) {
					$obj->post->post_title = $value;
				}
			} else {
				$obj->$name = $value;
			}
		}
	}

	/**
	 * Back-compat wrapper for checking if a CRUD object props exists.
	 *
	 * @since  5.3.0
	 *
	 * @param  object  $obj
	 * @param  string  $name
	 * @return mixed
	 */
	public static function prop_exists( $obj, $name ) {
		if ( self::is_wc_version_gte_2_7() ) {
			$get_fn = 'get_' . $name;
			return is_callable( array( $obj, $get_fn ) ) ? true : $obj->meta_exists( '_wc_pb_' . $name );
		} else {
			return isset( $obj->$name ) || ( isset( $obj->post ) && isset( $obj->post->$name ) );
		}
	}

	/**
	 * Back-compat wrapper for getting CRUD object meta.
	 *
	 * @since  5.2.0
	 *
	 * @param  WC_Data  $obj
	 * @param  string   $key
	 * @return mixed
	 */
	public static function get_meta( $obj, $key ) {
		if ( self::is_wc_version_gte_2_7() ) {
			return $obj->get_meta( $key, true );
		} else {
			return get_post_meta( $obj->id, $key, true );
		}
	}

	/**
	 * Back-compat wrapper for 'wc_get_price_including_tax'.
	 *
	 * @since  5.2.0
	 *
	 * @param  WC_Product  $product
	 * @param  array       $args
	 * @return mixed
	 */
	public static function wc_get_price_including_tax( $product, $args ) {
		if ( self::is_wc_version_gte_2_7() ) {
			return wc_get_price_including_tax( $product, $args );
		} else {

			$qty   = isset( $args[ 'qty' ] ) ? $args[ 'qty' ] : 1;
			$price = isset( $args[ 'price' ] ) ? $args[ 'price' ] : '';

			return $product->get_price_including_tax( $qty, $price );
		}
	}

	/**
	 * Back-compat wrapper for 'wc_get_price_excluding_tax'.
	 *
	 * @since  5.2.0
	 *
	 * @param  WC_Product  $product
	 * @param  array       $args
	 * @return mixed
	 */
	public static function wc_get_price_excluding_tax( $product, $args ) {
		if ( self::is_wc_version_gte_2_7() ) {
			return wc_get_price_excluding_tax( $product, $args );
		} else {

			$qty   = isset( $args[ 'qty' ] ) ? $args[ 'qty' ] : 1;
			$price = isset( $args[ 'price' ] ) ? $args[ 'price' ] : '';

			return $product->get_price_excluding_tax( $qty, $price );
		}
	}

	/**
	 * Back-compat wrapper for 'get_default_attributes'.
	 *
	 * @since  5.2.0
	 *
	 * @param  WC_Product  $product
	 * @return mixed
	 */
	public static function get_default_attributes( $product, $context = 'view' ) {
		if ( self::is_wc_version_gte_2_7() ) {
			return $product->get_default_attributes( $context );
		} else {
			return $product->get_variation_default_attributes();
		}
	}

	/**
	 * Back-compat wrapper for 'wc_get_stock_html'.
	 *
	 * @since  5.2.0
	 *
	 * @param  WC_Product  $product
	 * @return mixed
	 */
	public static function wc_get_stock_html( $product ) {
		if ( self::is_wc_version_gte_2_7() ) {
			$html = wc_get_stock_html( $product );
		} else {
			$availability      = $product->get_availability();
			$availability_html = empty( $availability[ 'availability' ] ) ? '' : '<p class="stock ' . esc_attr( $availability[ 'class' ] ) . '">' . esc_html( $availability[ 'availability' ] ) . '</p>';
			$html              = apply_filters( 'woocommerce_stock_html', $availability_html, $availability[ 'availability' ], $product );
		}
		return $html;
	}

	/**
	 * Back-compat wrapper for 'wc_get_formatted_variation'.
	 *
	 * @since  5.1.0
	 *
	 * @param  WC_Product_Variation  $variation
	 * @param  boolean               $flat
	 * @return string
	 */
	public static function wc_get_formatted_variation( $variation, $flat ) {
		if ( self::is_wc_version_gte_2_7() ) {
			return wc_get_formatted_variation( $variation, $flat );
		} elseif ( self::is_wc_version_gte_2_5() ) {
			return $variation->get_formatted_variation_attributes( $flat );
		} else {
			return wc_get_formatted_variation( $variation->get_variation_attributes(), $flat );
		}
	}

	/**
	 * Get prefix for use with wp_cache_set. Allows all cache in a group to be invalidated at once..
	 *
	 * @since  5.0.0
	 *
	 * @param  string  $group
	 * @return string
	 */
	public static function wc_cache_helper_get_cache_prefix( $group ) {
		if ( self::is_wc_version_gte_2_5() ) {
			return WC_Cache_Helper::get_cache_prefix( $group );
		} else {
			// Get cache key - uses cache key wc_orders_cache_prefix to invalidate when needed
			$prefix = wp_cache_get( 'wc_' . $group . '_cache_prefix', $group );

			if ( false === $prefix ) {
				$prefix = 1;
				wp_cache_set( 'wc_' . $group . '_cache_prefix', $prefix, $group );
			}

			return 'wc_cache_' . $prefix . '_';
		}
	}

	/**
	 * Increment group cache prefix (invalidates cache).
	 *
	 * @since  5.0.0
	 *
	 * @param  string  $group
	 */
	public static function wc_cache_helper_incr_cache_prefix( $group ) {
		if ( self::is_wc_version_gte_2_5() ) {
			WC_Cache_Helper::incr_cache_prefix( $group );
		} else {
			wp_cache_incr( 'wc_' . $group . '_cache_prefix', 1, $group );
		}
	}

	/**
	 * Backwards compatible logging using 'WC_Logger' class.
	 *
	 * @since  5.2.0
	 *
	 * @param  string  $message
	 * @param  string  $level
	 * @param  string  $context
	 */
	public static function log( $message, $level, $context ) {
		if ( self::is_wc_version_gte_2_7() ) {
			$logger = wc_get_logger();
			$logger->log( $level, $message, array( 'source' => $context ) );
		} else {
			$logger = new WC_Logger();
			$logger->add( $context, $message );
		}
	}
}
