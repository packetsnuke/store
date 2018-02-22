<?php
/**
 * WC_CP_Product class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    2.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Composited Product wrapper class.
 *
 * @class    WC_CP_Product
 * @version  3.9.5
 */
class WC_CP_Product {

	/**
	 * Product instance of the associated composited product.
	 * @var WC_Product
	 */
	private $product = false;

	/**
	 * Raw meta prices used in the min/max composite price calculation.
	 * @var string
	 */
	public $min_price;
	public $max_price;
	public $min_regular_price;
	public $max_regular_price;

	/**
	 * Products corresponding to the min/max (regular) price at which the composited product can be purchased. If the product is variable, these will contain the associated variations, otherwise they are identical to the 'product' property.
	 * @var WC_Product
	 */
	public $min_price_product;
	public $max_price_product;
	public $min_regular_price_product;
	public $max_regular_price_product;

	/**
	 * Component ID of the component that this product belongs to.
	 * @var string
	 */
	private $component_id = '';

	/**
	 * Composite that this product belongs to.
	 * @var WC_Product_Composite
	 */
	private $composite = null;

	/**
	 * True if the composited product is a Name-Your-Price product.
	 * @var boolean
	 */
	private $is_nyp = null;

	/**
	 * Constructor.
	 *
	 * @param  mixed                 $product_id
	 * @param  string                $component_id
	 * @param  WC_Product_Composite  $parent
	 */
	public function __construct( $product_id, $component_id, $parent ) {

		$product_id = absint( $product_id );

		if ( $product_id > 0 && $product = wc_get_product( $product_id ) ) {

			$this->product = $product;

			if ( is_object( $parent ) && 'composite' === $parent->get_type() && $parent->has_component( $component_id ) ) {

				$this->component_id = $component_id;
				$this->composite    = $parent;

				$this->sync_prices();

			}
		}
	}

	/**
	 * True if the composited product is a valid product.
	 *
	 * @return boolean
	 */
	public function exists() {
		return false !== $this->product;
	}

	/**
	 * Get composited product.
	 *
	 * @param  array  $args
	 * @return WC_Product|false
	 */
	public function get_product( $args = array() ) {
		$product = false;

		if ( $this->exists() ) {

			$product = $this->product;

			$what   = isset( $args[ 'what' ] ) && in_array( $args[ 'what' ], array( 'min', 'max' ) ) ? $args[ 'what' ] : '';
			$having = isset( $args[ 'having' ] ) && in_array( $args[ 'having' ], array( 'price', 'regular_price' ) ) ? $args[ 'having' ] : '';
			$prop   = $having && $what ? $what . '_' . $having . '_product' : false;

			if ( $prop && isset( $this->$prop ) ) {
				$product = $this->$prop;
			}

		}

		return $product;
	}

	/**
	 * Get composited product ID.
	 *
	 * @return integer
	 */
	public function get_product_id() {
		return $this->exists() ? WC_CP_Core_Compatibility::get_id( $this->product ) : 0;
	}

	/**
	 * Get the composite product.
	 *
	 * @return WC_Product_Composite|false
	 */
	public function get_composite() {
		return $this->composite;
	}

	/**
	 * Get the composite product ID.
	 *
	 * @return integer
	 */
	public function get_composite_id() {
		return isset( $this->composite ) ? WC_CP_Core_Compatibility::get_id( $this->composite ) : 0;
	}

	/**
	 * Get component id.
	 *
	 * @return string|false
	 */
	public function get_component_id() {
		return $this->component_id;
	}

	/**
	 * Get raw component data.
	 *
	 * @return array|false
	 */
	public function get_component_data() {
		return isset( $this->composite ) ? $this->composite->get_component_data( $this->get_component_id() ) : null;
	}

	/**
	 * Get component.
	 *
	 * @since  3.7.0
	 *
	 * @return array|false
	 */
	public function get_component() {
		return isset( $this->composite ) ? $this->composite->get_component( $this->component_id ) : null;
	}

	/**
	 * Initialize composited product price data, if needed.
	 *
	 * @return void
	 */
	public function sync_prices() {

		// Init prices.
		$this->min_price          = 0;
		$this->max_price          = 0;
		$this->min_regular_price  = 0;
		$this->max_regular_price  = 0;

		$this->min_price_incl_tax = 0;
		$this->min_price_excl_tax = 0;

		$id = $this->get_product_id();

		// Purchasable status.
		if ( ! $this->is_priced_individually() && '' === WC_CP_Core_Compatibility::get_prop( $this->product, 'price', 'edit' ) ) {
			WC_CP_Core_Compatibility::set_prop( $this->product, 'price', 0 );
		}

		// Calculate product prices.
		if ( $this->is_priced_individually() && $this->is_purchasable() ) {

			$composited_product = $this->product;
			$product_type       = $composited_product->get_type();

			/*-----------------------------------------------------------------------------------*/
			/*  Simple Products.                                                                 */
			/*-----------------------------------------------------------------------------------*/

			if ( in_array( $product_type, array( 'product', 'variation' ) ) ) {

				// Name your price support.
				if ( 'simple' === $product_type && WC_CP()->compatibility->is_nyp( $composited_product ) ) {

					$this->min_price = $this->min_regular_price = WC_Name_Your_Price_Helpers::get_minimum_price( $id ) ? WC_Name_Your_Price_Helpers::get_minimum_price( $id ) : 0;
					$this->max_price = $this->max_regular_price = INF;
					$this->is_nyp  = true;

					WC_CP_Core_Compatibility::set_prop( $this->product, 'price', $this->min_price );
					WC_CP_Core_Compatibility::set_prop( $this->product, 'regular_price', $this->min_price );

				} else {

					$this->min_price         = $this->max_price         = $this->get_raw_price();
					$this->min_regular_price = $this->max_regular_price = $this->get_raw_regular_price();
				}

			/*-----------------------------------------------------------------------------------*/
			/*  Variable Products.                                                               */
			/*-----------------------------------------------------------------------------------*/

			} elseif ( 'variable' === $product_type ) {

				$variation_prices = $composited_product->get_variation_prices();

				if ( $this->get_discount() && false === $this->is_discount_allowed_on_sale_price() ) {
					$variation_price_ids = array_keys( $variation_prices[ 'regular_price' ] );
				} else {
					$variation_price_ids = array_keys( $variation_prices[ 'price' ] );
				}

				$min_variation_price_id = current( $variation_price_ids );
				$max_variation_price_id = end( $variation_price_ids );

				$min_variation = wc_get_product( $min_variation_price_id );
				$max_variation = wc_get_product( $max_variation_price_id );

				if ( $min_variation && $max_variation ) {

					$this->min_price_product = $this->min_regular_price_product = $min_variation;
					$this->max_price_product = $this->min_regular_price_product = $max_variation;

					$this->min_price             = $this->get_raw_price( $min_variation );
					$this->max_price             = $this->get_raw_price( $max_variation );
					$min_variation_regular_price = $this->get_raw_regular_price( $min_variation );
					$max_variation_regular_price = $this->get_raw_regular_price( $max_variation );

					// The variation with the lowest price may have a higher regular price then the variation with the highest price.
					if ( $max_variation_regular_price < $min_variation_regular_price ) {
						$this->min_regular_price_product = $max_variation;
						$this->max_regular_price_product = $min_variation;
					}

					$this->min_regular_price = min( $min_variation_regular_price, $max_variation_regular_price );
					$this->max_regular_price = max( $min_variation_regular_price, $max_variation_regular_price );
				}

			/*-----------------------------------------------------------------------------------*/
			/*  Bundles.                                                                        */
			/*-----------------------------------------------------------------------------------*/

			} elseif ( 'bundle' === $product_type ) {

				$this->min_regular_price = WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ? $composited_product->get_min_raw_regular_price() : $composited_product->min_bundle_regular_price;
				$this->max_regular_price = WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ? $composited_product->get_max_raw_regular_price() : $composited_product->max_bundle_regular_price;

				if ( false === $this->is_discount_allowed_on_sale_price() ) {
					$min_regular_price = $this->min_regular_price;
					$max_regular_price = $this->max_regular_price;
				} else {
					$min_regular_price = WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ? $composited_product->get_min_raw_price() : $composited_product->min_bundle_price;
					$max_regular_price = WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ? $composited_product->get_max_raw_price() : $composited_product->max_bundle_price;
				}

				if ( $discount = $this->get_discount() ) {
					$this->min_price = empty( $min_regular_price ) ? $min_regular_price : round( (double) $min_regular_price * ( 100 - $discount ) / 100, wc_cp_price_num_decimals() );
					$this->max_price = empty( $max_regular_price ) ? $max_regular_price : round( (double) $max_regular_price * ( 100 - $discount ) / 100, wc_cp_price_num_decimals() );
				} else {
					$this->min_price = $min_regular_price;
					$this->max_price = $min_regular_price;
				}

			/*-----------------------------------------------------------------------------------*/
			/*  Other types.                                                                     */
			/*-----------------------------------------------------------------------------------*/

			} else {

				$price         = $this->get_raw_price();
				$regular_price = $this->get_raw_regular_price();

				/**
				 * Filter the raw min price.
				 *
				 * @param  string         $price
				 * @param  WC_CP_Product  $cp_product
				 */
				$this->min_price = apply_filters( 'woocommerce_composited_product_min_price', $price, $this );

				/**
				 * Filter the raw max price.
				 *
				 * @param  string         $price
				 * @param  WC_CP_Product  $cp_product
				 */
				$this->max_price = apply_filters( 'woocommerce_composited_product_max_price', $price, $this );

				/**
				 * Filter the raw min regular price.
				 *
				 * @param  string         $price
				 * @param  WC_CP_Product  $cp_product
				 */
				$this->min_regular_price = apply_filters( 'woocommerce_composited_product_min_regular_price', $regular_price, $this );

				/**
				 * Filter the raw max regular price.
				 *
				 * @param  string         $price
				 * @param  WC_CP_Product  $cp_product
				 */
				$this->max_regular_price = apply_filters( 'woocommerce_composited_product_max_regular_price', $regular_price, $this );

				/**
				 * Filter the NYP status of the product.
				 *
				 * @param  string         $price
				 * @param  WC_CP_Product  $cp_product
				 */
				$this->is_nyp = apply_filters( 'woocommerce_composited_product_is_nyp', $this->is_nyp, $this );

				if ( $this->is_nyp ) {
					WC_CP_Core_Compatibility::set_prop( $this->product, 'price', $this->min_price );
					WC_CP_Core_Compatibility::set_prop( $this->product, 'regular_price', $this->min_price );
				}
			}
		}
	}

	/**
	 * Adds price filters to account for component discounts.
	 */
	public function add_filters() {

		$product = $this->get_product();

		if ( ! $product ) {
			return false;
		}

		WC_CP_Products::add_filters( $this );
	}

	/**
	 * Removes attached price filters.
	 */
	public function remove_filters() {
		WC_CP_Products::remove_filters();
	}

	/**
	 * True if the composited product is marked as individually-sold item.
	 *
	 * @return boolean
	 */
	public function is_sold_individually() {
		if ( ! isset( $this->sold_individually ) ) {
			$this->sold_individually = $this->exists() && $this->product->is_sold_individually();
		}
		return $this->sold_individually;
	}

	/**
	 * True if the composited product is a NYP product.
	 *
	 * @return boolean
	 */
	public function is_nyp() {
		return $this->is_nyp;
	}

	/**
	 * True if the product can be bought.
	 *
	 * @return boolean
	 */
	public function is_purchasable() {
		if ( ! isset( $this->purchasable ) ) {
			$this->purchasable = $this->exists() && $this->product->is_purchasable();
		}
		return $this->purchasable;
	}

	/**
	 * True if the composited product is priced individually.
	 *
	 * @return boolean
	 */
	public function is_priced_individually() {
		/**
		 * Last chance to filter the composited product pricing scheme.
		 *
		 * @param  boolean        $is_priced_individually
		 * @param  WC_CP_Product  $composited_product
		 */
		return isset( $this->composite ) ? apply_filters( 'woocommerce_composited_product_is_priced_individually', $this->get_component()->is_priced_individually(), $this ) : null;
	}

	/**
	 * True if the composited product is shipped individually.
	 *
	 * @return boolean
	 */
	public function is_shipped_individually( $product = false ) {

		$is_shipped_individually = isset( $this->composite ) ? $this->get_component()->is_shipped_individually() : null;

		if ( ! is_null( $is_shipped_individually ) && has_filter( 'woocommerce_composited_product_shipped_individually' ) && false !== $product ) {
			/**
			 * 'woocommerce_composited_product_shipped_individually' filter.
			 *
			 * @deprecated
			 *
			 * @param  boolean               $is_shipped_individually
			 * @param  WC_Product            $product
			 * @param  mixed                 $component_id
			 * @param  WC_Product_Composite  $composite
			 */
			$is_shipped_individually =  apply_filters( 'woocommerce_composited_product_shipped_individually', $is_shipped_individually, $product, $this->get_component_id(), $this->composite );
		}

		/**
		 * Last chance to filter the composited product pricing scheme.
		 *
		 * @param  boolean        $is_shipped_individually
		 * @param  WC_CP_Product  $composited_product
		 */
		return is_null( $is_shipped_individually ) ? null : apply_filters( 'woocommerce_composited_product_is_shipped_individually', $this->get_component()->is_shipped_individually(), $this );
	}

	/**
	 * Get composited product price after discount, price filters excluded.
	 *
	 * @return mixed
	 */
	public function get_raw_price( $product = false, $context = '' ) {

		if ( ! $product ) {
			$product = $this->product;
		}

		$price = WC_CP_Core_Compatibility::get_prop( $product, 'price', 'edit' );

		if ( '' === $price ) {
			return $price;
		}

		if ( ! $this->is_priced_individually() ) {
			return (double) 0;
		}

		if ( false === $this->is_discount_allowed_on_sale_price() ) {
			$regular_price = WC_CP_Core_Compatibility::get_prop( $product, 'regular_price', 'edit' );
		} else {
			$regular_price = $price;
		}

		if ( $discount = $this->get_discount() ) {
			$price = empty( $regular_price ) ? $regular_price : round( (double) $regular_price * ( 100 - $discount ) / 100, wc_cp_price_num_decimals() );
		}

		/**
		 * 'woocommerce_composited_product_raw_price' raw price filter.
		 *
		 * @param  mixed          $price
		 * @param  WC_Product     $product
		 * @param  mixed          $discount
		 * @param  WC_CP_Product  $this
		 */
		$price = apply_filters( 'woocommerce_composited_product_raw_price' . ( $context ? '_' . $context : '' ), $price, $product, $discount, $this );

		return $price;
	}

	/**
	 * Get composited product regular price before discounts, price filters excluded.
	 *
	 * @return mixed
	 */
	public function get_raw_regular_price( $product = false ) {

		if ( ! $product ) {
			$product = $this->product;
		}

		$regular_price = WC_CP_Core_Compatibility::get_prop( $product, 'regular_price', 'edit' );

		if ( ! $this->is_priced_individually() ) {
			return (double) 0;
		}

		if ( empty( $regular_price ) ) {
			$regular_price = WC_CP_Core_Compatibility::get_prop( $product, 'price', 'edit' );
		}

		return $regular_price;
	}

	/**
	 * Get composited product price after discount.
	 *
	 * @param  string   $min_or_max
	 * @param  boolean  $display
	 * @return double
	 */
	public function get_price( $min_or_max = 'min', $display = false ) {

		if ( ! $this->exists() ) {
			return false;
		}

		if ( 'max' === $min_or_max && $this->is_nyp() && $this->is_priced_individually() ) {
			return INF;
		}

		$prop    = $min_or_max . '_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_filters();
		$price = $product->get_price();
		$this->remove_filters();

		/**
		 * Last chance to filter the composited product price.
		 *
		 * @param  string         $price
		 * @param  string         $min_or_max
		 * @param  boolean        $display
		 * @param  WC_CP_Product  $composited_product
		 */
		return apply_filters( 'woocommerce_composited_product_get_price', $display ? WC_CP_Products::get_product_display_price( $product, $price ) : $price, $min_or_max, $display, $this );
	}

	/**
	 * Get composited product regular price after discount.
	 *
	 * @param  string   $min_or_max
	 * @param  boolean  $display
	 * @param  boolean  $strict
	 * @return double
	 */
	public function get_regular_price( $min_or_max = 'min', $display = false, $strict = false ) {

		if ( ! $this->exists() ) {
			return false;
		}

		$prop    = $strict ? $min_or_max . '_price_product' : $min_or_max . '_regular_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_filters();
		$price = $product->get_regular_price();
		$this->remove_filters();

		/**
		 * Last chance to filter the composited product regular price.
		 *
		 * @param  string         $price
		 * @param  string         $min_or_max
		 * @param  boolean        $display
		 * @param  WC_CP_Product  $composited_product
		 */
		return apply_filters( 'woocommerce_composited_product_get_regular_price', $display ? WC_CP_Products::get_product_display_price( $product, $price ) : $price, $min_or_max, $display, $this );
	}

	/**
	 * Min composited product price incl tax.
	 *
	 * @return double
	 */
	public function get_price_including_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( ! $this->exists() ) {
			return false;
		}

		$prop    = $min_or_max . '_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_filters();
		$price = $product->get_price();
		$this->remove_filters();

		if ( $price && 'yes' === wc_cp_calc_taxes() && 'yes' !== wc_cp_prices_include_tax() ) {
			$price = WC_CP_Core_Compatibility::wc_get_price_including_tax( $product, array( 'qty' => $qty, 'price' => $price ) );
		} else {
			$price = $price * $qty;
		}

		/**
		 * Last chance to filter the composited product price incl tax.
		 *
		 * @param  string         $price
		 * @param  string         $min_or_max
		 * @param  WC_CP_Product  $composited_product
		 * @param  string         $qty
		 */
		return apply_filters( 'woocommerce_composited_product_get_price_including_tax', $price, $min_or_max, $this, $qty );
	}

	/**
	 * Min composited product price excl tax.
	 *
	 * @return double
	 */
	public function get_price_excluding_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( ! $this->exists() ) {
			return false;
		}

		$prop    = $min_or_max . '_price_product';
		$product = ! empty( $this->$prop ) ? $this->$prop : $this->product;

		$this->add_filters();
		$price = $product->get_price();
		$this->remove_filters();

		if ( $price && 'yes' === wc_cp_calc_taxes() && 'yes' === wc_cp_prices_include_tax() ) {
			$price = WC_CP_Core_Compatibility::wc_get_price_excluding_tax( $product, array( 'qty' => $qty, 'price' => $price ) );
		} else {
			$price = $price * $qty;
		}

		/**
		 * Last chance to filter the composited product price excl tax.
		 *
		 * @param  string         $price
		 * @param  string         $min_or_max
		 * @param  WC_CP_Product  $composited_product
		 * @param  string         $qty
		 */
		return apply_filters( 'woocommerce_composited_product_get_price_excluding_tax', $price, $min_or_max, $this, $qty );
	}

	/**
	 * Wrapper for 'get_price_html()' that applies price filters.
	 *
	 * @return string
	 */
	public function get_price_html() {

		$price_html = '';

		if ( $this->is_purchasable() ) {
			$this->add_filters();
			$price_html = $this->get_product()->get_price_html();
			$this->remove_filters();
		}

		return $price_html;
	}

	/**
	 * Generated dropdown price string for composited products priced individually.
	 *
	 * @return string
	 */
	public function get_price_string() {

		if ( ! $this->exists() ) {
			return false;
		}

		$price_string = '';
		$component_id = $this->get_component_id();
		$product_id   = $this->get_product_id();

		if ( $this->is_priced_individually() && $this->is_purchasable() && false === $this->get_component()->hide_component_option_prices() ) {

			$discount        = $sale = '';
			$discount_amount = $this->get_discount();

			$has_multiple = $this->get_quantity_min() > 1;

			$ref_price = $this->get_regular_price( 'min', true, true );
			$price     = $this->get_price( 'min', true );
			$is_nyp    = $this->is_nyp;
			$is_range  = $price < $this->get_price( 'max', true );

			if ( $discount_amount && $ref_price > 0 && ! $is_nyp && $this->get_product() && 'bundle' !== $this->get_product()->get_type() ) {
				$discount = sprintf( __( '(%s%% off)', 'ultimatewoo-pro' ), round( $discount_amount, 1 ) );
			}

			if ( ! $discount && $ref_price > $price && $ref_price > 0 && ! $is_nyp ) {
				$sale = sprintf( __( '(%s%% off)', 'ultimatewoo-pro' ), round( 100 * ( $ref_price - $price ) / $ref_price, 1 ) );
			}

			$pct_off = $discount . $sale;

			/**
			 * Filter the composited product price string suffix.
			 *
			 * @param  string         $percent_off
			 * @param  string         $component_id
			 * @param  string         $product_id
			 * @param  string         $price
			 * @param  string         $ref_price
			 * @param  boolean        $is_nyp
			 * @param  boolean        $is_range
			 * @param  WC_CP_Product  $composited_product
			 */
			$suffix       = apply_filters( 'woocommerce_composited_product_price_suffix', $pct_off, $component_id, $product_id, $price, $ref_price, $is_nyp, $is_range, $this ) ;
			$show_free    = $price == 0 && ! $is_range && ! WC_CP_Core_Compatibility::is_wc_version_gte_2_7();
			$price_string = $show_free ? __( 'Free!', 'woocommerce' ) : WC_CP_Helpers::format_raw_price( $price );
			$qty_suffix   = $has_multiple && ! $show_free ? __( '/ pc.', 'ultimatewoo-pro' ) : '';

			/**
			 * Filter the composited product price string (before applying prefix).
			 *
			 * @param  string         $price_string
			 * @param  string         $formatted_price
			 * @param  string         $formatted_qty
			 * @param  string         $percent_off_suffix
			 * @param  string         $price
			 * @param  boolean        $is_range
			 * @param  boolean        $has_multiple
			 * @param  string         $product_id
			 * @param  string         $component_id
			 * @param  WC_CP_Product  $composited_product
			 */
			$price_string = apply_filters( 'woocommerce_composited_product_price_string_inner', sprintf( _x( '%1$s %2$s %3$s', 'dropdown price followed by per unit suffix and discount suffix', 'ultimatewoo-pro' ), $price_string, $qty_suffix, $suffix ), $price_string, $qty_suffix, $suffix, $price, $is_range, $has_multiple, $product_id, $component_id, $this );

			$from_text    = WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ? wc_get_price_html_from_text() : $this->get_product()->get_price_html_from_text();
			$price_string = $is_range || $is_nyp ? sprintf( _x( '%1$s%2$s', 'Price range: from', 'ultimatewoo-pro' ), $from_text, $price_string ) : $price_string;
		}

		/**
		 * Last chance to filter the entire price string.
		 *
		 * @param  string         $price_string
		 * @param  string         $product_id
		 * @param  string         $component_id
		 * @param  WC_CP_Product  $composited_product
		 */
		return apply_filters( 'woocommerce_composited_product_price_string', $price_string, $product_id, $component_id, $this );
	}

	/**
	 * Generated title string for composited products.
	 *
	 * @param  string  $title
	 * @param  string  $qty
	 * @param  string  $price
	 * @return string
	 */
	public static function get_title_string( $title, $qty = '', $price = '' ) {

		$quantity_string = '';
		$price_string    = '';

		if ( $qty ) {
			$quantity_string = sprintf( _x( ' &times; %s', 'qty string', 'ultimatewoo-pro' ), $qty );
		}

		if ( $price ) {
			$price_string = sprintf( _x( ' &ndash; %s', 'price suffix', 'ultimatewoo-pro' ), $price );
		}

		$title_string = sprintf( _x( '%1$s%2$s%3$s', 'title quantity price', 'ultimatewoo-pro' ), $title, $quantity_string, $price_string );

		return $title_string;
	}

	/**
	 * Component discount getter.
	 *
	 * @return mixed
	 */
	public function get_discount() {

		if ( $component = $this->get_component() ) {
			$discount = $component->get_discount();
		}

		return $discount;
	}

	/**
	 * Indicates whether discounts can be applied on sale prices.
	 */
	public function is_discount_allowed_on_sale_price() {
		/**
		 * Filter to control whether component-level discounts are applied on the regular price, ignoring any defined sale price.
		 *
		 * @param  boolean  $discount_from_regular
		 * @param  string   $component_id
		 * @param  string   $composite_id
		 */
		return false === apply_filters( 'woocommerce_composited_product_discount_from_regular', true, $this->get_component_id(), $this->get_composite_id() );
	}

	/**
	 * Composited product min quantity.
	 *
	 * @return mixed
	 */
	public function get_quantity_min() {

		$qty       = 1;
		$component = $this->get_component();

		if ( $component ) {
			$qty_min = $component->get_quantity( 'min' );
			$qty     = ( $qty_min > 1 && $this->is_sold_individually() ) ? 1 : $qty_min;
		}

		return $qty;
	}

	/**
	 * Composited product max quantity.
	 *
	 * @param  string                $min_or_max
	 * @param  boolean               $bound_by_stock
	 * @param  WC_Product_Variation  $variation
	 * @return mixed
	 */
	public function get_quantity_max( $bound_by_stock = false, $variation = false ) {

		$qty_max   = $qty_min = $this->get_quantity_min();
		$component = $this->get_component();

		if ( $variation ) {
			$product = $variation;
		} else {
			$product = $this->get_product();
		}

		if ( $component ) {
			$qty_max = $component->get_quantity( 'max' );
			$qty_max = '' !== $qty_max ? max( $qty_max, $qty_min ) : '';
		}

		$qty_max = $this->is_sold_individually() ? 1 : $qty_max;

		// Variations min/max quantity attributes handled via JS.
		if ( $bound_by_stock && ! in_array( $product->get_type(), array( 'variable', 'variable-subscription' ) ) ) {

			$qty_max_bound = '';

			if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
				$qty_max_bound = $product->get_stock_quantity();
			}

			// Max product quantity can't be greater than the Max Quantity setting.
			if ( $qty_max > 0 ) {
				$qty_max_bound = '' !== $qty_max_bound ? min( $qty_max, $qty_max_bound ) : $qty_max;
			}

			// Max product quantity can't be lower than the min product quantity - if it is, then the product is not in stock.
			if ( '' !== $qty_max_bound ) {
				if ( $qty_min > $qty_max_bound ) {
					$qty_max_bound = $qty_min;
				}
			}

			$qty_max = $qty_max_bound;
		}

		return '' !== $qty_max ? absint( $qty_max ) : '';
	}

	/**
	 * Get composited product stock html.
	 *
	 * @since  3.9.0
	 *
	 * @param  WC_Product|false  $product
	 * @return string
	 */
	public function get_availability_html( $product = false ) {

		$availability = $this->get_availability( $product );

		if ( ! $product ) {
			$product = $this->product;
		}

		if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ) {

			if ( ! empty( $availability[ 'availability' ] ) ) {

				ob_start();

				wc_get_template( 'single-product/stock.php', array(
					'product'      => $product,
					'class'        => $availability[ 'class' ],
					'availability' => $availability[ 'availability' ],
				) );

				$availability_html = ob_get_clean();

			} else {
				$availability_html = '';
			}

		} else {
			$availability_html = empty( $availability[ 'availability' ] ) ? '' : '<p class="stock ' . esc_attr( $availability[ 'class' ] ) . '">' . esc_html( $availability[ 'availability' ] ) . '</p>';
		}

		/**
		 * 'woocommerce_composited_product_stock_html' filter.
		 *
		 * Availability html that takes min_quantity into account.
		 *
		 * @param  string           $availability_html
		 * @param  array            $availability
		 * @param  WC_Bundled_Item  $this
		 */
		return apply_filters( 'woocommerce_composited_product_stock_html', $availability_html, $availability, $this, $product );
	}


	/**
	 * Composited product availability that takes min_quantity > 1 into account.
	 *
	 * @since  3.7.0
	 *
	 * @return array
	 */
	public function get_availability( $product = false ) {

		if ( ! $product ) {
			$product = $this->get_product();
		}

		/**
		 * 'woocommerce_composited_product_availability' filter.
		 *
		 * Item availability needs to take min_quantity into account, hence the filter name change.
		 *
		 * @param  array          $availability
		 * @param  WC_Product     $product
		 * @param  WC_CP_Product  $this
		 */
		return apply_filters( 'woocommerce_composited_product_availability', array(
			'availability' => $this->get_availability_text( $product ),
			'class'        => $this->get_availability_class( $product ),
		), $product, $this );
	}

	/**
	 * Get availability text based on stock status.
	 *
	 * @since  3.7.0
	 *
	 * @return string
	 */
	private function get_availability_text( $product ) {

		$total_stock = $product->get_stock_quantity();
		$quantity    = $this->get_quantity_min();

		if ( ! $product->is_in_stock() ) {
			$availability = __( 'Out of stock', 'woocommerce' );
		} elseif ( $product->managing_stock() && $product->is_on_backorder( $quantity ) ) {

			if ( $product->backorders_require_notification() ) {
				switch ( get_option( 'woocommerce_stock_format' ) ) {
					case 'no_amount' :
						$availability = __( 'Available on backorder', 'woocommerce' );
					break;
					default :
						$availability = __( 'Available on backorder', 'woocommerce' );

						if ( $total_stock > 0 ) {
							$availability .= ' ' . sprintf( __( '(only %s left in stock)', 'ultimatewoo-pro' ), $total_stock );
						}
					break;
				}
			} else {
				$availability = __( 'In stock', 'woocommerce' );
			}

		} elseif ( $product->managing_stock() ) {

			if ( $total_stock >= $quantity ) {

				switch ( get_option( 'woocommerce_stock_format' ) ) {
					case 'no_amount' :
						$availability = __( 'In stock', 'woocommerce' );
					break;
					case 'low_amount' :
						if ( $total_stock <= get_option( 'woocommerce_notify_low_stock_amount' ) ) {
							$availability = sprintf( __( 'Only %s left in stock', 'woocommerce' ), $total_stock );

							if ( $product->backorders_allowed() && $product->backorders_require_notification() ) {
								$availability .= ' ' . __( '(also available on backorder)', 'woocommerce' );
							}
						} else {
							$availability = __( 'In stock', 'woocommerce' );
						}
					break;
					default :
						$availability = sprintf( __( '%s in stock', 'woocommerce' ), $total_stock );

						if ( $product->backorders_allowed() && $product->backorders_require_notification() ) {
							$availability .= ' ' . __( '(also available on backorder)', 'woocommerce' );
						}
					break;
				}

			} else {

				switch ( get_option( 'woocommerce_stock_format' ) ) {
					case 'no_amount' :
						$availability = __( 'Insufficient stock', 'ultimatewoo-pro' );
					break;
					default :
						$availability = __( 'Insufficient stock', 'ultimatewoo-pro' );
						$availability .= ' ' . sprintf( __( '(only %s left in stock)', 'ultimatewoo-pro' ), $total_stock );
					break;
				}
			}
		} else {
			$availability = '';
		}

		/**
		 * 'woocommerce_composited_product_availability_text' filter - refer to {@see get_availability}.
		 *
		 * @param  string         $availability
		 * @param  WC_CP_Product  $this
		 */
		return apply_filters( 'woocommerce_composited_product_availability_text', $availability, $this );
	}

	/**
	 * Get availability classname based on stock status.
	 *
	 * @since  3.7.0
	 *
	 * @return string
	 */
	private function get_availability_class( $product ) {

		$quantity = $this->get_quantity_min();

		if ( ! $product->is_in_stock() ) {
			$class = 'out-of-stock';
		} elseif ( $product->managing_stock() && $product->is_on_backorder( $quantity ) && $product->backorders_require_notification() ) {
			$class = 'available-on-backorder';
		} else {
			if ( ! $product->has_enough_stock( $quantity ) ) {
				$class = 'out-of-stock';
			} else {
				$class = 'in-stock';
			}
		}

		/**
		 * 'woocommerce_composited_product_availability_class' filter - refer to {@see get_availability}.
		 *
		 * @param  string         $availability_class
		 * @param  WC_CP_Product  $this
		 */
		return apply_filters( 'woocommerce_composited_product_availability_class', $class, $this );
	}

	/**
	 * Get product image data.
	 *
	 * @return array
	 */
	public function get_image_data() {

		$image_data = false;

		if ( $this->exists() ) {
			if ( has_post_thumbnail( $this->get_product_id() ) ) {
				$attachment_id = get_post_thumbnail_id( $this->get_product_id() );
				$attachment    = wp_get_attachment_image_src( $attachment_id, apply_filters( 'woocommerce_composite_component_option_image_size', 'shop_catalog' ) );
				$image_src     = $attachment ? current( $attachment ) : '';
				$image_srcset  = $image_src && function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, 'shop_catalog' ) : '';
				$image_sizes   = $image_src && function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, 'shop_catalog' ) : '';
				$image_srcset  = $image_srcset ? $image_srcset : '';
				$image_sizes   = $image_sizes ? $image_sizes : '';
			} else {
				$image_src    = wc_placeholder_img_src();
				$image_src    = '';
				$image_srcset = '';
				$image_sizes  = '';
			}

			$image_data = array(
				'image_src'    => $image_src,
				'image_srcset' => $image_srcset,
				'image_sizes'  => $image_sizes,
				'image_title'  => $this->product->get_title()
			);
		}

		return $image_data;
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	public function init() {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'sync_prices()' );
		return $this->sync_prices();
	}
	public function is_priced_per_product() {
		_deprecated_function( __METHOD__ . '()', '3.7.0', 'is_priced_individually()' );
		return $this->is_priced_individually();
	}
	public function get_min_price() {
		_deprecated_function( __METHOD__ . '()', '3.2.3', 'get_price()' );
		return $this->min_price;
	}
	public function get_min_regular_price() {
		_deprecated_function( __METHOD__ . '()', '3.2.3', 'get_regular_price()' );
		return $this->min_regular_price;
	}
	public function get_max_price() {
		_deprecated_function( __METHOD__ . '()', '3.2.3', 'get_price()' );
		return $this->max_price;
	}
	public function get_max_regular_price() {
		_deprecated_function( __METHOD__ . '()', '3.2.3', 'get_regular_price()' );
		return $this->max_regular_price;
	}
	public function get_min_price_incl_tax() {
		_deprecated_function( __METHOD__ . '()', '3.2.3', 'get_price_including_tax()' );
		return $this->get_price_including_tax( 'min' );
	}
	public function get_min_price_excl_tax() {
		_deprecated_function( __METHOD__ . '()', '3.2.3', 'get_price_excluding_tax()' );
		return $this->get_price_excluding_tax( 'min' );
	}
}
