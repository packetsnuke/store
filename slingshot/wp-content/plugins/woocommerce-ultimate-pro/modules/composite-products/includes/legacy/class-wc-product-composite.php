<?php
/**
 * Legacy WC_Product_Composite class (WC <= 2.6)
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    3.9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Composite Product Class.
 *
 * @class    WC_Product_Composite
 * @version  3.11.3
 */
class WC_Product_Composite extends WC_Product {

	/**
	 * Raw meta where all component data is saved.
	 * A shamefully simple way to store/manage data that just works, but can't be used for any complex operations on the DB side.
	 * @var array
	 */
	private $composite_meta = array();

	/**
	 * IDs of the defined components.
	 * @var array
	 */
	private $component_ids = array();

	/**
	 * Layout option.
	 * @var string
	 */
	private $composite_layout;

	/**
	 * Provides context when the "Sold Individually" option is set to 'yes': 'product' or 'configuration'.
	 * @var string
	 */
	private $sold_individually_context;

	/**
	 * Prices calculated from raw price meta. Used in price filter and sorting queries.
	 * @var mixed
	 */
	private $min_raw_price;
	private $max_raw_price;

	/**
	 * "Hide Price" option.
	 * @var boolean
	 */
	private $hide_price_html;

	/**
	 * "Allow editing in cart" option.
	 * @var boolean
	 */
	private $is_editable_in_cart;

	/**
	 * Configurations with lowest/highest composite prices.
	 * Used in 'get_composite_price', 'get_composite_regular_price', 'get_composite_price_including_tax' and 'get_composite_price_excluding_tax methods'.
	 * @var array
	 */
	private $permutations = array(
		'min' => array(),
		'max' => array()
	);

	/**
	 * Array of composite price data for consumption by the front-end script.
	 * @var array
	 */
	private $composite_price_data = array();

	/**
	 * Array of cached composite prices.
	 * @var array
	 */
	private $composite_price_cache;

	/**
	 * Storage of 'contains' keys, most set during sync.
	 * @var array
	 */
	private $contains = array(
		'priced_individually'  => null,
		'shipped_individually' => null,
		'optional'             => false,
		'mandatory'            => false,
		'nyp'                  => false,
		'discounted'           => false
	);

	/**
	 * Used to suppress range-format price strings.
	 * @var boolean
	 */
	private $suppress_range_format = false;

	/**
	 * True if the composite is a Name-Your-Price product.
	 * @var boolean
	 */
	private $is_nyp = false;

	/**
	 * Indicates whether the product has been synced with component data.
	 * @var boolean
	 */
	private $is_synced = false;

	/**
	 * Constructor.
	 *
	 * @param mixed  $bundle_id
	 */
	public function __construct( $bundle_id ) {

		$this->product_type = 'composite';

		parent::__construct( $bundle_id );

		// Component data. Still serialized. I promise, this will change soon :)
		$this->composite_meta = get_post_meta( $this->id, '_bto_data', true );

		// Save Component IDs.
		if ( is_array( $this->composite_meta ) ) {
			$this->component_ids = array_keys( $this->composite_meta );
		}

		// Layout.
		$this->composite_layout = get_post_meta( $this->id, '_bto_style', true );

		// Minimum and maximum bundle prices. Obained from meta used in price filter widget and sorting results.
		$this->min_raw_price = $this->min_composite_price = get_post_meta( $this->id, '_price', true );
		$this->max_raw_price = $this->max_composite_price = get_post_meta( $this->id, '_wc_sw_max_price', true );

		$this->min_raw_price = $this->contains( 'priced_individually' ) && '' !== $this->min_raw_price ? (double) $this->min_raw_price : $this->min_raw_price;
		$this->max_raw_price = $this->contains( 'priced_individually' ) && '' !== $this->max_raw_price ? (double) $this->max_raw_price : $this->max_raw_price;

		$this->max_raw_price         = 9999999999.0 === $this->max_raw_price ? INF : $this->max_raw_price;
		$this->max_raw_regular_price = 9999999999.0 === $this->max_raw_regular_price ? INF : $this->max_raw_regular_price;

		// Is this a NYP product?
		if ( WC_CP()->compatibility->is_nyp( $this ) ) {
			$this->is_nyp = true;
		}

		// Base prices are saved separately to ensure the the original price meta always store the min bundle prices.
		$base_price         = $this->is_nyp() ? get_post_meta( $this->id, '_min_price', true ) : get_post_meta( $this->id, '_bto_base_price', true );
		$base_regular_price = $this->is_nyp() ? $base_price : get_post_meta( $this->id, '_bto_base_regular_price', true );
		$base_sale_price    = $this->is_nyp() ? '' : get_post_meta( $this->id, '_bto_base_sale_price', true );

		// Patch price properties with base prices.
		$this->price         = $this->contains( 'priced_individually' ) ? (double) $base_price : $base_price;
		$this->regular_price = $this->contains( 'priced_individually' ) ? (double) $base_regular_price : $base_regular_price;
		$this->sale_price    = $this->contains( 'priced_individually' ) && '' !== $base_sale_price ? (double) $base_sale_price : $base_sale_price;

		// Load "Hide Price" option meta.
		$this->hide_price_html = 'yes' === get_post_meta( $this->id, '_bto_hide_shop_price', true );

		// Load "Sold Individually" context meta.
		$sold_individually_context       = get_post_meta( $this->id, '_bto_sold_individually', true );
		$this->sold_individually_context = in_array( $sold_individually_context, array( 'product', 'configuration' ) ) ? $sold_individually_context : 'product';

		// Load "Alow editing in cart" meta.
		$this->is_editable_in_cart = 'yes' === get_post_meta( $this->id, '_bto_edit_in_cart', true );

		// Populate these for back-compat.
		$this->per_product_pricing  = $this->contains( 'priced_individually' );
		$this->per_product_shipping = $this->contains( 'shipped_individually' );
	}


	/**
	 * Getter of composite 'contains' properties.
	 *
	 * @since  3.7.0
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function contains( $key ) {

		if ( 'priced_individually' === $key ) {

			if ( is_null( $this->contains[ $key ] ) ) {

				$this->contains[ 'priced_individually' ] = false;

				// Any components priced individually?
				$components = $this->get_components();

				if ( ! empty( $components ) ) {

					foreach ( $components as $component ) {
						if ( $component->is_priced_individually() ) {
							$this->contains[ 'priced_individually' ] = true;
						}
					}
				}
			}

		} elseif ( 'shipped_individually' === $key ) {

			if ( is_null( $this->contains[ $key ] ) ) {

				$this->contains[ 'shipped_individually' ] = false;

				// Any components shipped individually?
				$components = $this->get_components();

				if ( ! empty( $components ) ) {

					foreach ( $components as $component ) {
						if ( $component->is_shipped_individually() ) {
							$this->contains[ 'shipped_individually' ] = true;
						}
					}
				}
			}

		} else {
			$this->maybe_sync_composite();
		}

		return isset( $this->contains[ $key ] ) ? $this->contains[ $key ] : null;
	}

	/**
	 * True if the composite is in sync with its contents.
	 *
	 * @return boolean
	 */
	public function is_synced() {
		return $this->is_synced;
	}

	/**
	 * Sync composite if not synced.
	 *
	 * @since 3.7.0
	 */
	public function maybe_sync_composite() {
		if ( ! $this->is_synced() ) {
			$this->sync_composite();
		}
	}

	/**
	 * Calculates min and max prices based on the composited product data.
	 *
	 * @return void
	 */
	public function sync_composite() {

		if ( $this->is_synced() ) {
			return true;
		}

		$components = $this->get_components();

		if ( empty( $components ) ) {
			return false;
		}

		// Initialize min/max raw prices.
		$min_raw_price = $max_raw_price = $this->price;

		// NYP products have infinite max price.
		if ( $this->is_nyp() ) {
			$max_raw_price = INF;
		}

		// Initialize 'contains' data.
		foreach ( $components as $component_id => $component ) {

			if ( $component->is_optional() ) {
				$this->contains[ 'optional' ] = true;
			} else {
				$this->contains[ 'mandatory' ] = true;
			}

			if ( $component->is_priced_individually() && $component->get_discount() ) {
				$this->contains[ 'discounted' ] = true;
			}

			$quantity_min = $component->get_quantity( 'min' );
			$quantity_max = $component->get_quantity( 'max' );

			if ( $quantity_min !== $quantity_max ) {
				$this->suppress_range_format = true;
			}

			// Infinite max quantity.
			if ( '' === $quantity_max ) {
				$max_raw_price = INF;
			}
		}

		// Price calculations.
		if ( $this->contains( 'priced_individually' ) ) {

			if ( false === $this->hide_price_html() ) {

				$price_data = WC_CP_Products::read_price_data( $this );

				/*
				 * Store cheapest/most expensive permutation.
				 */
				if ( ! empty( $price_data[ 'permutations' ] ) ) {
					$this->permutations[ 'min' ] = $price_data[ 'permutations' ][ 'min' ];
					$this->permutations[ 'max' ] = $price_data[ 'permutations' ][ 'max' ];
				}

				// Permutations calculated from prices obtained in FAST mode directly from meta: Calculate min/max raw prices from min/max permutations.
				if ( ! isset( $price_data[ 'raw_prices' ] ) ) {

					// Min raw price.
					foreach ( $components as $component_id => $component ) {

						if ( empty( $this->permutations[ 'min' ][ $component_id ] ) ) {
							continue;
						}

						$min_component_raw_price_option = $component->get_option( $this->permutations[ 'min' ][ $component_id ] );

						if ( $min_component_raw_price_option ) {
							$min_component_raw_price = $min_component_raw_price_option->min_price;
							$quantity_min            = $component->is_optional() || 0 === $quantity_min ? 0 : $component->get_quantity( 'min' );
							$min_raw_price          += $quantity_min * $min_component_raw_price;
						}
					}

					// Max raw price.

					// Infinite.
					if ( empty( $this->permutations[ 'max' ] ) ) {

						$max_raw_price = INF;

					// Finite.
					} elseif ( INF !== $max_raw_price ) {

						foreach ( $components as $component_id => $component ) {

							if ( empty( $this->permutations[ 'max' ][ $component_id ] ) ) {
								continue;
							}

							$max_component_raw_price_option = $component->get_option( $this->permutations[ 'max' ][ $component_id ] );

							if ( $max_component_raw_price_option ) {
								$max_component_raw_price = $max_component_raw_price_option->max_price;
								$quantity_max            = $component->get_quantity( 'max' );
								$max_raw_price          += $quantity_max * $max_component_raw_price;
							}
						}
					}

				// Permutations calculated from prices obtained in ACCURATE mode from objects: Min/max permutations may vary for different users, but we need to store a single, consistent composite min/max raw price.
				// In this case, ignore the min/max permutations data and calculate best/worst-case values ignoring scenarios.
				} else {

					foreach ( $components as $component_id => $component ) {

						if ( empty( $price_data[ 'raw_prices' ][ $component_id ] ) ) {
							continue;
						}

						$component_option_raw_prices_min = $price_data[ 'raw_prices' ][ $component_id ][ 'min' ];
						asort( $component_option_raw_prices_min );

						$component_option_raw_prices_max = $price_data[ 'raw_prices' ][ $component_id ][ 'max' ];
						asort( $component_option_raw_prices_max );

						$min_component_raw_price = current( $component_option_raw_prices_min );
						$max_component_raw_price = end( $component_option_raw_prices_max );

						$quantity_min = $component->is_optional() || 0 === $quantity_min ? 0 : $component->get_quantity( 'min' );
						$quantity_max = $component->get_quantity( 'max' );

						$min_raw_price += $quantity_min * $min_component_raw_price;

						if ( INF !== $max_raw_price ) {
							if ( INF !== $max_component_raw_price && '' !== $quantity_max ) {
								$max_raw_price = $max_raw_price + $quantity_max * $max_component_raw_price;
							} else {
								$max_raw_price = INF;
							}
						}
					}
				}
			}
		}

		// Filter raw prices.
		$min_raw_price = apply_filters( 'woocommerce_min_composite_price', $min_raw_price, $this );
		$max_raw_price = apply_filters( 'woocommerce_max_composite_price', $max_raw_price, $this );

		// Filter min/max price index.
		$this->permutations[ 'min' ] = apply_filters( 'woocommerce_min_composite_price_index', $this->permutations[ 'min' ], $this );
		$this->permutations[ 'max' ] = apply_filters( 'woocommerce_max_composite_price_index', $this->permutations[ 'max' ], $this );

		if ( INF === $max_raw_price ) {
			$this->suppress_range_format = true;
		}

		// Set synced flag.
		$this->is_synced = true;

		/**
		 * 'woocommerce_composite_synced' action.
		 *
		 * @param  WC_Product_Composite  $this
		 */
		do_action( 'woocommerce_composite_synced', $this );

		/**
		 * 'woocommerce_composite_update_price_meta' filter.
		 *
		 * Use this to prevent composite min/max raw price meta from being updated.
		 *
		 * @param  boolean               $update
		 * @param  WC_Product_Composite  $this
		 */
		$save = apply_filters( 'woocommerce_composite_update_price_meta', true, $this ) && ! defined( 'WC_CP_UPDATING' );

		/*
		 * Set min/max raw (regular) prices.
		 */
		if ( $this->min_raw_price !== $min_raw_price ) {
			if ( $save ) {
				update_post_meta( $this->id, '_price', $min_raw_price );
				if ( $this->is_on_sale() ) {
					update_post_meta( $this->id, '_sale_price', $min_raw_price );
				} else {
					update_post_meta( $this->id, '_sale_price', '' );
				}
			}
		}

		if ( $this->max_raw_price !== $max_raw_price ) {
			if ( $save ) {
				update_post_meta( $this->id, '_wc_sw_max_price', INF === $max_raw_price ? 9999999999.0 : $max_raw_price );
			}
		}

		// Update raw price props.
		$this->min_raw_price = $min_raw_price;
		$this->max_raw_price = $max_raw_price;

		// Update these for back-compat.
		$this->min_composite_price = $min_raw_price;
		$this->max_composite_price = $max_raw_price;

		return true;
	}

	/**
	 * Stores composite pricing strategy data that is passed to JS.
	 *
	 * @return void
	 */
	public function load_price_data() {

		$this->composite_price_data[ 'is_purchasable' ]         = $this->is_purchasable() ? 'yes' : 'no';
		$this->composite_price_data[ 'has_price_range' ]        = $this->contains( 'priced_individually' ) && $this->get_composite_price( 'min', true ) !== $this->get_composite_price( 'max', true ) ? 'yes' : 'no';
		$this->composite_price_data[ 'show_free_string' ]       = ( $this->contains( 'priced_individually' ) ? apply_filters( 'woocommerce_composite_show_free_string', false, $this ) : true ) ? 'yes' : 'no';

		$this->composite_price_data[ 'is_priced_individually' ] = array();

		$components = $this->get_components();

		if ( ! empty( $components ) ) {
			foreach ( $components as $component_id => $component ) {
				$this->composite_price_data[ 'is_priced_individually' ][ $component_id ] = $component->is_priced_individually() ? 'yes' : 'no';
			}
		}

		$this->composite_price_data[ 'prices' ]         = new stdClass;
		$this->composite_price_data[ 'regular_prices' ] = new stdClass;
		$this->composite_price_data[ 'prices_tax' ]     = new stdClass;
		$this->composite_price_data[ 'addons_prices' ]  = new stdClass;
		$this->composite_price_data[ 'quantities' ]     = new stdClass;

		WC_CP_Helpers::extend_price_display_precision();

		$base_price_incl_tax = $this->get_price_including_tax( 1, 1000 );
		$base_price_excl_tax = $this->get_price_excluding_tax( 1, 1000 );

		WC_CP_Helpers::reset_price_display_precision();

		$this->composite_price_data[ 'base_price' ]         = $this->get_price();
		$this->composite_price_data[ 'base_regular_price' ] = $this->get_regular_price();
		$this->composite_price_data[ 'base_price_tax' ]     = $base_price_incl_tax / $base_price_excl_tax;

		$this->composite_price_data[ 'total' ]              = 0.0;
		$this->composite_price_data[ 'regular_total' ]      = 0.0;
		$this->composite_price_data[ 'total_incl_tax' ]     = 0.0;
		$this->composite_price_data[ 'total_excl_tax' ]     = 0.0;
	}

	/**
	 * Get min/max composite price.
	 *
	 * @param  string  $min_or_max
	 * @param  boolean $display
	 * @return mixed
	 */
	public function get_composite_price( $min_or_max = 'min', $display = false ) {

		if ( $this->contains( 'priced_individually' ) ) {

			$this->maybe_sync_composite();

			$cache_key = md5( json_encode( apply_filters( 'woocommerce_composite_prices_hash', array(
				'type'       => 'price',
				'display'    => $display,
				'min_or_max' => $min_or_max
			), $this ) ) );

			if ( isset( $this->composite_price_cache[ $cache_key ] ) ) {
				$price = $this->composite_price_cache[ $cache_key ];
			} else {

				$prop = $min_or_max . '_raw_price';

				if ( '' === $this->$prop || INF === $this->$prop ) {
					$price = '';
				} else {

					$price = $display ? WC_CP_Products::get_product_display_price( $this, $this->get_price() ) : $this->get_price();

					foreach ( $this->permutations[ $min_or_max ] as $component_id => $product_id ) {

						if ( ! $product_id ) {
							continue;
						}

						$component = $this->get_component( $component_id );
						$item_qty  = $component->get_quantity( $min_or_max );

						if ( $item_qty ) {
							$composited_product  = $this->get_component_option( $component_id, $product_id );
							$price              += $item_qty * $composited_product->get_price( $min_or_max, $display );
						}
					}
				}

				$this->composite_price_cache[ $cache_key ] = $price;
			}

		} else {

			$price = $this->get_price();

			if ( $display ) {
				$price = WC_CP_Products::get_product_display_price( $this, $price );
			}
		}

		return $price;
	}

	/**
	 * Get min/max composite regular price.
	 *
	 * @param  string   $min_or_max
	 * @param  boolean  $display
	 * @return mixed
	 */
	public function get_composite_regular_price( $min_or_max = 'min', $display = false ) {

		if ( $this->contains( 'priced_individually' ) ) {

			$this->maybe_sync_composite();

			$cache_key = md5( json_encode( apply_filters( 'woocommerce_composite_prices_hash', array(
				'type'       => 'regular_price',
				'display'    => $display,
				'min_or_max' => $min_or_max
			), $this ) ) );

			if ( isset( $this->composite_price_cache[ $cache_key ] ) ) {
				$price = $this->composite_price_cache[ $cache_key ];
			} else {

				$prop = $min_or_max . '_raw_price';

				if ( '' === $this->$prop || INF === $this->$prop ) {
					$price = '';
				} else {

					$price = $display ? WC_CP_Products::get_product_display_price( $this, $this->get_regular_price() ) : $this->get_regular_price();

					foreach ( $this->permutations[ $min_or_max ] as $component_id => $product_id ) {

						if ( ! $product_id ) {
							continue;
						}

						$component = $this->get_component( $component_id );
						$item_qty  = $component->get_quantity( $min_or_max );

						if ( $item_qty ) {
							$composited_product  = $this->get_component_option( $component_id, $product_id );
							$price              += $item_qty * $composited_product->get_regular_price( $min_or_max, $display, true );
						}
					}
				}

				$this->composite_price_cache[ $cache_key ] = $price;
			}

		} else {

			$price = $this->get_regular_price();

			if ( $display ) {
				$price = WC_CP_Products::get_product_display_price( $this, $price );
			}
		}

		return $price;
	}

	/**
	 * Get min/max composite price including tax.
	 *
	 * @return mixed
	 */
	public function get_composite_price_including_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( $this->contains( 'priced_individually' ) ) {

			$this->maybe_sync_composite();

			$cache_key = md5( json_encode( apply_filters( 'woocommerce_composite_prices_hash', array(
				'type'       => 'price_incl_tax',
				'qty'        => $qty,
				'min_or_max' => $min_or_max
			), $this ) ) );

			if ( isset( $this->composite_price_cache[ $cache_key ] ) ) {
				$price = $this->composite_price_cache[ $cache_key ];
			} else {

				$prop = $min_or_max . '_raw_price';

				if ( '' === $this->$prop || INF === $this->$prop ) {
					$price = '';
				} else {

					$price = $this->get_price_including_tax( $qty, $this->get_price() );

					foreach ( $this->permutations[ $min_or_max ] as $component_id => $product_id ) {

						if ( ! $product_id ) {
							continue;
						}

						$component = $this->get_component( $component_id );
						$item_qty  = $component->get_quantity( $min_or_max );

						if ( $item_qty ) {
							$composited_product  = $this->get_component_option( $component_id, $product_id );
							$price              += $composited_product->get_price_including_tax( $min_or_max, $item_qty * $qty );
						}
					}
				}

				$this->composite_price_cache[ $cache_key ] = $price;
			}

		} else {
			$price = $this->get_price_including_tax( $qty, $this->get_price() );
		}

		return $price;
	}

	/**
	 * Get min/max composite price excluding tax.
	 *
	 * @return double
	 */
	public function get_composite_price_excluding_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( $this->contains( 'priced_individually' ) ) {

			$this->maybe_sync_composite();

			$cache_key = md5( json_encode( apply_filters( 'woocommerce_composite_prices_hash', array(
				'type'       => 'price_excl_tax',
				'qty'        => $qty,
				'min_or_max' => $min_or_max
			), $this ) ) );

			if ( isset( $this->composite_price_cache[ $cache_key ] ) ) {
				$price = $this->composite_price_cache[ $cache_key ];
			} else {

				$prop = $min_or_max . '_raw_price';

				if ( '' === $this->$prop || INF === $this->$prop ) {
					$price = '';
				} else {

					$price = $this->get_price_excluding_tax( $qty, $this->get_price() );

					foreach ( $this->permutations[ $min_or_max ] as $component_id => $product_id ) {

						if ( ! $product_id ) {
							continue;
						}

						$component = $this->get_component( $component_id );
						$item_qty  = $component->get_quantity( $min_or_max );

						if ( $item_qty ) {
							$composited_product  = $this->get_component_option( $component_id, $product_id );
							$price              += $composited_product->get_price_excluding_tax( $min_or_max, $item_qty * $qty );
						}
					}
				}

				$this->composite_price_cache[ $cache_key ] = $price;
			}

		} else {

			$price = $this->get_price_excluding_tax( $qty, $this->get_price() );
		}

		return $price;
	}

	/**
	 * Bypass pricing calculations.
	 *
	 * @return boolean
	 */
	public function hide_price_html() {
		return apply_filters( 'woocommerce_composite_hide_price_html', $this->hide_price_html, $this );
	}

	/**
	 * Returns range style html price string without min and max.
	 *
	 * @param  mixed  $price
	 * @return string
	 */
	public function get_price_html( $price = '' ) {

		$this->maybe_sync_composite();

		$components = $this->get_components();

		if ( $this->contains( 'priced_individually' ) && ! empty( $components ) ) {

			// Get the price.
			if ( $this->hide_price_html() || '' === $this->get_composite_price( 'min' ) ) {

				$price = apply_filters( 'woocommerce_composite_empty_price_html', '', $this );

			} else {

				$suppress_range_format = $this->suppress_range_format || apply_filters( 'woocommerce_composite_force_old_style_price_html', false, $this );

				if ( $suppress_range_format ) {

					$price = wc_price( $this->get_composite_price( 'min', true ) );

					if ( $this->get_composite_regular_price( 'min', true ) !== $this->get_composite_price( 'min', true ) ) {

						$regular_price = wc_price( $this->get_composite_regular_price( 'min', true ) );

						if ( $this->get_composite_price( 'min', true ) !== $this->get_composite_price( 'max', true ) ) {
							$price = sprintf( _x( '%1$s%2$s', 'Price range: from', 'ultimatewoo-pro' ), $this->get_price_html_from_text(), $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix() );
						} else {
							$price = $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix();
						}

						$price = apply_filters( 'woocommerce_composite_sale_price_html', $price, $this );

					} elseif ( 0.0 === $this->get_composite_price( 'min', true ) && 0.0 === $this->get_composite_price( 'max', true ) ) {

						$free_string = apply_filters( 'woocommerce_composite_show_free_string', false, $this ) ? __( 'Free!', 'woocommerce' ) : $price;
						$price       = apply_filters( 'woocommerce_composite_free_price_html', $free_string, $this );

					} else {

						if ( $this->get_composite_price( 'min', true ) !== $this->get_composite_price( 'max', true ) ) {
							$price = sprintf( _x( '%1$s%2$s', 'Price range: from', 'ultimatewoo-pro' ), $this->get_price_html_from_text(), $price . $this->get_price_suffix() );
						} else {
							$price = $price . $this->get_price_suffix();
						}

						$price = apply_filters( 'woocommerce_composite_price_html', $price, $this );
					}

				} else {

					if ( $this->get_composite_price( 'min', true ) !== $this->get_composite_price( 'max', true ) ) {
						$price = sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $this->get_composite_price( 'min', true ) ), wc_price( $this->get_composite_price( 'max', true ) ) );
					} else {
						$price = wc_price( $this->get_composite_price( 'min', true ) );
					}

					if ( $this->get_composite_regular_price( 'min', true ) !== $this->get_composite_price( 'min', true ) || $this->get_composite_regular_price( 'max', true ) > $this->get_composite_price( 'max', true ) ) {

						if ( $this->get_composite_regular_price( 'min', true ) !== $this->get_composite_regular_price( 'max', true ) ) {
							$regular_price = sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $this->get_composite_regular_price( 'min', true ) ), wc_price( $this->get_composite_regular_price( 'max', true ) ) );
						} else {
							$regular_price = wc_price( $this->get_composite_regular_price( 'min', true ) );
						}

						$price = apply_filters( 'woocommerce_composite_sale_price_html', $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix(), $this );

					} elseif ( 0.0 === $this->get_composite_price( 'min', true ) && 0.0 === $this->get_composite_price( 'max', true ) ) {

						$free_string = apply_filters( 'woocommerce_composite_show_free_string', false, $this ) ? __( 'Free!', 'woocommerce' ) : $price;
						$price       = apply_filters( 'woocommerce_composite_free_price_html', $free_string, $this );

					} else {
						$price = apply_filters( 'woocommerce_composite_price_html', $price . $this->get_price_suffix(), $this );
					}
				}
			}

			return apply_filters( 'woocommerce_get_price_html', $price, $this );

		} else {

			return parent::get_price_html();
		}
	}

	/**
	 * Prices incl. or excl. tax are calculated based on the bundled products prices, so get_price_suffix() must be overridden when individually-priced items exist.
	 *
	 * @return string
	 */
	public function get_price_suffix( $price = '', $qty = 1 ) {

		if ( $this->contains( 'priced_individually' ) ) {

			$price_suffix = '';
			$calc_taxes   = get_option( 'woocommerce_calc_taxes', 'no' );

			if ( ( $suffix = get_option( 'woocommerce_price_display_suffix' ) ) && 'yes' === $calc_taxes ) {

				$replacements = array(
					'{price_including_tax}' => wc_price( $this->get_composite_price_including_tax( 'min', $qty ) ),
					'{price_excluding_tax}' => wc_price( $this->get_composite_price_excluding_tax( 'min', $qty ) )
				);

				$price_suffix = str_replace( array_keys( $replacements ), array_values( $replacements ), ' <small class="woocommerce-price-suffix">' . wp_kses_post( $suffix ) . '</small>' );
			}

			/**
			 * 'woocommerce_get_price_suffix' filter.
			 *
			 * @param  string                $price_suffix
			 * @param  WC_Product_Composite  $this
			 */
			return apply_filters( 'woocommerce_get_price_suffix', $price_suffix, $this );

		} else {
			return parent::get_price_suffix();
		}
	}

	/**
	 * Gets price data array. Contains localized strings and price data passed to JS.
	 *
	 * @return array
	 */
	public function get_composite_price_data() {

		$this->maybe_sync_composite();
		$this->load_price_data();

		return $this->composite_price_data;
	}

	/**
	 * Composite is a NYP product.
	 *
	 * @since  3.8.0
	 *
	 * @return boolean
	 */
	public function is_nyp() {
		return $this->is_nyp;
	}

	/**
	 * True if a one of the composited products has a component discount, or if there is a base sale price defined.
	 *
	 * @return boolean
	 */
	public function is_on_sale() {

		if ( $this->contains( 'priced_individually' ) ) {
			$this->maybe_sync_composite();
			$composite_on_sale = parent::is_on_sale() || ( $this->contains( 'discounted' ) && $this->get_composite_regular_price( 'min' ) > 0 );
		} else {
			$composite_on_sale = parent::is_on_sale();
		}

		/**
		 * Filter composite on sale status.
		 *
		 * @param   boolean               $composite_on_sale
		 * @param   WC_Product_Composite  $this
		 */
		return apply_filters( 'woocommerce_product_is_on_sale', $composite_on_sale, $this );
	}

	/**
	 * Override purchasable method to account for empty price meta being allowed when individually-priced components exist.
	 *
	 * @return boolean
	 */
	public function is_purchasable() {

		$purchasable = true;

		// Products must exist of course.
		if ( ! $this->exists() ) {
			$purchasable = false;

		// When priced statically a price needs to be set.
		} elseif ( ! $this->contains( 'priced_individually' ) && '' === $this->get_price() ) {
			$purchasable = false;

		// Check the product is published.
		} elseif ( 'publish' !== WC_CP_Core_Compatibility::get_prop( $this, 'status' ) && ! current_user_can( 'edit_post', $this->id ) ) {
			$purchasable = false;
		}

		/**
		 * Filter composite purchasable status.
		 *
		 * @param   boolean               $is_purchasable
		 * @param   WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_is_purchasable', $purchasable, $this );
	}

	/**
	 * True if the composite is editable in cart.
	 *
	 * @return boolean
	 */
	public function is_editable_in_cart() {
		return $this->is_editable_in_cart;
	}

	/**
	 * Wrapper for get_permalink that adds composite configuration data to the URL.
	 *
	 * @return string
	 */
	public function get_permalink() {

		$permalink             = get_permalink( $this->id );
		$composite_config_data = false;
		$fn_args_count         = func_num_args();

		if ( 1 === $fn_args_count ) {

			$cart_item = func_get_arg( 0 );

			if ( isset( $cart_item[ 'composite_data' ] ) && is_array( $cart_item[ 'composite_data' ] ) ) {

				$composite_config_data = $cart_item[ 'composite_data' ];
				$args                  = array();

				foreach ( $composite_config_data as $component_id => $component_config_data ) {

					if ( isset( $component_config_data[ 'product_id' ] ) ) {
						$args[ 'wccp_component_selection' ][ $component_id ] = $component_config_data[ 'product_id' ];
					}

					if ( isset( $component_config_data[ 'quantity' ] ) ) {
						$args[ 'wccp_component_quantity' ][ $component_id ] = $component_config_data[ 'quantity' ];
					}

					if ( isset( $component_config_data[ 'variation_id' ] ) ) {
						$args[ 'wccp_variation_id' ][ $component_id ] = $component_config_data[ 'variation_id' ];
					}

					if ( isset( $component_config_data[ 'attributes' ] ) && is_array( $component_config_data[ 'attributes' ] ) ) {
						foreach ( $component_config_data[ 'attributes' ] as $tax => $val ) {
							$args[ 'wccp_' . $tax ][ $component_id ] = sanitize_title( $val );
						}
					}
				}

				if ( $this->is_editable_in_cart() ) {

					// Find the cart id we are updating.

					$cart_id = '';

					foreach ( WC()->cart->cart_contents as $item_key => $item_values ) {
						if ( wc_cp_is_composite_container_cart_item( $item_values ) && $item_values[ 'composite_data' ] === $cart_item[ 'composite_data' ] ) {
							$cart_id = $item_key;
						}
					}

					if ( $cart_id ) {
						$args[ 'update-composite' ] = $cart_id;
					}
				}

				$args = apply_filters( 'woocommerce_composite_cart_permalink_args', $args, $cart_item, $this );

				if ( ! empty( $args ) ) {
					$permalink = esc_url( add_query_arg( $args, $permalink ) );
				}
			}
		}

		return $permalink;
	}

	/**
	 * Get the add to cart button text.
	 *
	 * @return  string
	 */
	public function add_to_cart_text() {

		$text = $this->is_purchasable() && $this->is_in_stock() ? __( 'Select options', 'woocommerce' ) : __( 'Read More', 'woocommerce' );

		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}

	/**
	 * Get the add to cart button text for the single page.
	 *
	 * @return string
	 */
	public function single_add_to_cart_text() {

		$text = __( 'Add to cart', 'woocommerce' );

		if ( isset( $_GET[ 'update-composite' ] ) ) {
			$updating_cart_key = wc_clean( $_GET[ 'update-composite' ] );

			if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {
				$text = __( 'Update Cart', 'ultimatewoo-pro' );
			}
		}

		return apply_filters( 'woocommerce_product_single_add_to_cart_text', $text, $this );
	}

	/**
	 * Get composite-specific add to cart form settings.
	 *
	 * @return  string
	 */
	public function add_to_cart_form_settings() {

		$image_data               = array();
		$pagination_data          = array();
		$placeholder_option       = array();
		$product_price_visibility = array();
		$subtotal_visibility      = array();

		$components = $this->get_components();

		if ( ! empty( $components ) ) {
			foreach ( $components as $component_id => $component ) {
				$image_data[ $component_id ]               = $component->get_image_data();
				$pagination_data[ $component_id ]          = $component->get_pagination_data();
				$placeholder_option[ $component_id ]       = $component->show_placeholder_option() ? 'yes' : 'no';
				$product_price_visibility[ $component_id ] = $component->hide_selected_option_price() ? 'no' : 'yes';
				$subtotal_visibility[ $component_id ]      = $component->is_subtotal_visible() ? 'yes' : 'no';
			}
		}

		$settings = array(
			// Apply a sequential configuration process when using the 'componentized' layout.
			// When set to 'yes', a component can be configured only if all previous components have been configured.
			'sequential_componentized_progress'      => apply_filters( 'woocommerce_composite_sequential_comp_progress', 'no', $this ), /* yes | no */
			// Hide or disable the add-to-cart button if the composite has any components pending user input.
			'button_behaviour'                       => apply_filters( 'woocommerce_composite_button_behaviour', 'new', $this ), /* new | old */
			'layout'                                 => $this->get_composite_layout_style(),
			'layout_variation'                       => $this->get_composite_layout_style_variation(),
			'update_browser_history'                 => $this->get_composite_layout_style() !== 'single' ? 'yes' : 'no',
			'show_placeholder_option'                => $placeholder_option,
			'slugs'                                  => $this->get_component_slugs(),
			'image_data'                             => $image_data,
			'pagination_data'                        => $pagination_data,
			'selected_product_price_visibility_data' => $product_price_visibility,
			'subtotal_visibility_data'               => $subtotal_visibility,
		);

		/**
		 * Filter composite-level JS app settings.
		 *
		 * @param  array                 $settings
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_add_to_cart_form_settings', $settings, $this );
	}

	/**
	 * Sold individually extended options for Composite Products, used to add context to the 'sold_individually' option.
	 * Returns 'product' or 'configuration', depending on the 'sold_individually' context.
	 *
	 * @return  string
	 */
	public function get_sold_individually_context() {
		return $this->sold_individually_context;
	}

	/**
	 * Generate component slugs based on component titles. Used to generate routes.
	 *
	 * @return array
	 */
	private function get_component_slugs() {

		$components = $this->get_components();
		$slugs      = array();

		if ( ! empty( $components ) ) {
			foreach ( $components as $component_id => $component ) {

				$sanitized_title = sanitize_title( $component->get_title( true ) );
				$component_slug  = $sanitized_title;
				$loop            = 0;

				while ( in_array( $component_slug, $slugs ) ) {
					$loop++;
					$component_slug = $sanitized_title . '-' . $loop;
				}

				$slugs[ $component_id ] = $component_slug;
			}

			$review_slug       = 'componentized' === $this->get_composite_layout_style_variation() ? __( 'configuration', 'ultimatewoo-pro' ) : __( 'review', 'ultimatewoo-pro' );
			$slugs[ 'review' ] = sanitize_title( $review_slug );
		}

		return $slugs;
	}


	/*
	|--------------------------------------------------------------------------
	| Layout.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Composite base layout.
	 *
	 * @return string
	 */
	public function get_composite_layout_style() {

		if ( isset( $this->base_layout ) ) {
			return $this->base_layout;
		}

		$composite_layout = self::get_layout_option( $this->composite_layout );
		$layout           = explode( '-', $composite_layout, 2 );

		$this->base_layout = $layout[0];

		return $this->base_layout;
	}

	/**
	 * Composite base layout variation.
	 *
	 * @return string
	 */
	public function get_composite_layout_style_variation() {

		if ( isset( $this->base_layout_variation ) ) {
			return $this->base_layout_variation;
		}

		$composite_layout = self::get_layout_option( $this->composite_layout );

		$layout = explode( '-', $composite_layout, 2 );

		if ( ! empty( $layout[1] ) ) {
			$this->base_layout_variation = $layout[1];
		} else {
			$this->base_layout_variation = 'standard';
		}

		return $this->base_layout_variation;
	}

	/**
	 * Get composite layout options.
	 *
	 * @return array
	 */
	public static function get_layout_options() {

		$sanitized_custom_layouts = array();

		$base_layouts = array(
			'single'              => __( 'Stacked', 'ultimatewoo-pro' ),
			'progressive'         => __( 'Progressive', 'ultimatewoo-pro' ),
			'paged'               => __( 'Stepped', 'ultimatewoo-pro' ),
		);

		$custom_layouts = array(
			'paged-componentized' => __( 'Componentized', 'ultimatewoo-pro' ),
		);

		/**
		 * Filter layout variations array to add custom layout variations.
		 *
		 * @param  array  $custom_layouts
		 */
		$custom_layouts = apply_filters( 'woocommerce_composite_product_layout_variations', $custom_layouts );

		foreach ( $custom_layouts as $layout_id => $layout_description ) {

			$sanitized_layout_id = esc_attr( sanitize_title( $layout_id ) );

			if ( array_key_exists( $sanitized_layout_id, $base_layouts ) ) {
				continue;
			}

			$sanitized_layout_id_parts = explode( '-', $sanitized_layout_id, 2 );

			if ( ! empty( $sanitized_layout_id_parts[0] ) && array_key_exists( $sanitized_layout_id_parts[0], $base_layouts ) ) {
				$sanitized_custom_layouts[ $sanitized_layout_id ] = $layout_description;
			}
		}

		return array_merge( $base_layouts, $sanitized_custom_layouts );
	}

	/**
	 * Get composite layout descriptions.
	 *
	 * @param  string  $layout_id
	 * @return string
	 */
	public static function get_layout_description( $layout_id ) {

		$tooltips = array(
			'single'              => __( 'Components are vertically stacked, with the add-to-cart button located at the bottom.', 'ultimatewoo-pro' ),
			'progressive'         => __( 'Components are vertically stacked and wrapped in toggle-boxes. They must be configured in sequence. Only one Component is visible at a time.', 'ultimatewoo-pro' ),
			'paged'               => __( 'Components are viewed individually and configured in a step-by-step manner. Selections are summarized in a final Review step.', 'ultimatewoo-pro' ),
			'paged-componentized' => __( 'Components are viewed individually and can be configured in any sequence. A variation of the Stepped layout that begins with a configuration Summary.', 'ultimatewoo-pro' ),
		);

		if ( ! isset( $tooltips[ $layout_id ] ) ) {
			return '';
		}

		return WC_CP_Core_Compatibility::wc_help_tip( $tooltips[ $layout_id ] );
	}

	/**
	 * Get selected layout option.
	 *
	 * @param  string  $layout
	 * @return string
	 */
	public static function get_layout_option( $layout ) {

		if ( ! $layout ) {
			return 'single';
		}

		$layouts         = self::get_layout_options();
		$layout_id_parts = explode( '-', $layout, 2 );

		if ( array_key_exists( $layout, $layouts ) ) {
			return $layout;
		} elseif ( array_key_exists( $layout_id_parts[0], $layouts ) ) {
			return $layout_id_parts[0];
		}

		return 'single';
	}


	/*
	|--------------------------------------------------------------------------
	| Scenarios.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Container of scenarios-related functionality - @see WC_CP_Scenarios_Manager.
	 *
	 * @param  string  $context
	 * @return WC_CP_Scenarios_Manager
	 */
	public function scenarios( $context = 'view' ) {

		$prop = 'scenarios_manager_' . $context;

		if ( ! isset( $this->$prop ) ) {
			$this->$prop = new WC_CP_Scenarios_Manager( $this, $context );
		}

		return $this->$prop;
	}

	/**
	 * Get raw scenario metadata.
	 *
	 * @param  string  $context
	 * @return array
	 */
	public function get_scenario_data( $context = 'view' ) {
		return $this->get_scenario_meta( $context );
	}

	/**
	 * Get raw scenario metadata.
	 *
	 * @param  string  $context
	 * @return array
	 */
	public function get_scenario_meta( $context = 'view' ) {

		$scenario_meta = get_post_meta( $this->id, '_bto_scenario_data', true );

		if ( empty( $scenario_meta ) ) {
			$scenario_meta = array();
		}

		if ( 'rest' === $context ) {

			$rest_api_scenario_meta = array();

			if ( ! empty( $scenario_meta ) ) {
				foreach ( $scenario_meta as $id => $data ) {

					$configuration = array();
					$actions       = array();

					if ( ! empty( $data[ 'component_data' ] ) && is_array( $data[ 'component_data' ] ) ) {
						foreach ( $data[ 'component_data' ] as $component_id => $component_data ) {
							$configuration[] = array(
								'component_id'      => strval( $component_id ),
								'component_options' => $component_data,
								'options_modifier'  => isset( $data[ 'modifier' ][ $component_id ] ) ? $data[ 'modifier' ][ $component_id ] : 'in'
							);
						}
					}

					if ( ! empty( $data[ 'scenario_actions' ] ) && is_array( $data[ 'scenario_actions' ] ) ) {
						foreach ( $data[ 'scenario_actions' ] as $action_id => $action_data ) {
							$actions[] = array(
								'action_id'   => strval( $action_id ),
								'is_active'   => isset( $action_data[ 'is_active' ] ) && 'yes' === $action_data[ 'is_active' ],
								'action_data' => array_diff_key( $action_data, array( 'is_active' => 1 ) )
							);
						}
					}

					$rest_api_scenario_meta[ $id ] = array(
						'id'            => (string) $id,
						'name'          => $data[ 'title' ],
						'description'   => $data[ 'description' ],
						'configuration' => $configuration,
						'actions'       => $actions
					);
				}
			}

			$scenario_meta = $rest_api_scenario_meta;
		}

		/**
		 * Filter raw scenario metadata.
		 *
		 * @param  array                 $scenario_meta
		 * @param  WC_Product_Composite  $product
		 */
		return 'view' === $context ? apply_filters( 'woocommerce_composite_scenario_meta', $scenario_meta, $this ) : $scenario_meta;
	}

	/**
	 * Build scenario data arrays for specific components, adapted to the data present in the current component options queries.
	 * Make sure this is always called after component options queries have run, otherwise component options queries will be populated with results for the initial composite state.
	 *
	 * @param  array    $component_ids
	 * @param  boolean  $use_current_query
	 * @return array
	 */
	public function get_current_scenario_data( $component_ids = array() ) {

		$component_options_subset = array();

		foreach ( $this->get_components() as $component_id => $component ) {

			if ( empty( $component_ids ) || in_array( $component_id, $component_ids ) ) {

				$current_component_options = $this->get_current_component_options( $component_id );
				$default_option            = $this->get_current_component_selection( $component_id );

				if ( $default_option && ! in_array( $default_option, $current_component_options ) ) {
					$current_component_options[] = $default_option;
				}

				$component_options_subset[ $component_id ] = $current_component_options;
			}
		}

		$this->composite_scenario_data = $this->scenarios()->get_data( $component_options_subset );

		return $this->composite_scenario_data;
	}

	/*
	|--------------------------------------------------------------------------
	| Component methods: Instantiation and data.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get component raw meta array by component id.
	 * All component data is currently lumped in a single meta field, which should hopefully change at some point.
	 *
	 * @param  string  $component_id
	 * @return array
	 */
	public function get_component_meta( $component_id ) {

		if ( ! isset( $this->composite_meta[ $component_id ] ) ) {
			return false;
		}

		return $this->composite_meta[ $component_id ];
	}

	/**
	 * Component object getter.
	 *
	 * @param  string  $component_id
	 * @return WC_CP_Component
	 */
	public function get_component( $component_id ) {

		$component = false;

		if ( $this->has_component( $component_id ) ) {

			$component = WC_CP_Helpers::cache_get( 'wc_cp_component_' . $component_id . '_' . $this->id );

			if ( defined( 'WC_CP_DEBUG_RUNTIME_CACHE' ) || null === $component ) {
				$component = new WC_CP_Component( $component_id, $this );
				WC_CP_Helpers::cache_set( 'wc_cp_component_' . $component_id . '_' . $this->id, $component );
			}
		}

		return $component;
	}

	/**
	 * Checks if a specific component ID exists.
	 *
	 * @param  string  $component_id
	 * @return boolean
	 */
	public function has_component( $component_id ) {

		$has_component = false;
		$component_ids = $this->get_component_ids();

		if ( in_array( $component_id, $component_ids ) ) {
			$has_component = true;
		}

		return $has_component;
	}

	/**
	 * Get all component ids.
	 *
	 * @return array
	 */
	public function get_component_ids() {
		return $this->component_ids;
	}

	/**
	 * Gets all components.
	 *
	 * @return array
	 */
	public function get_components() {

		$components    = array();
		$component_ids = $this->get_component_ids();

		foreach ( $component_ids as $component_id ) {
			if ( $component = $this->get_component( $component_id ) ) {
				$components[ $component_id ] = $component;
			}
		}

		return $components;
	}

	/**
	 * Get component data array by component id.
	 *
	 * @param  string  $component_id
	 * @return array
	 */
	public function get_component_data( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->get_data() : false;
	}

	/**
	 * Get metadata of all Components.
	 *
	 * @return array
	 */
	public function get_composite_data() {

		$components = $this->get_components();

		if ( empty( $components ) ) {
			return false;
		}

		$composite_data = array();

		foreach ( $components as $component_id => $component ) {

			if ( 'rest' === $context ) {

				$thumbnail_id  = '';
				$thumbnail_src = '';

				if ( ! empty( $component[ 'thumbnail_id' ] ) ) {

					$thumbnail_id = absint( $component[ 'thumbnail_id' ] );
					$image        = wp_get_attachment_image_src( $thumbnail_id, 'full' );

					if ( $image ) {
						$thumbnail_src = $image[0];
					}
				}

				$composite_data[ $component_id ] = array(
					'id'                   => (string) $component->get_id(),
					'title'                => $component->get_title(),
					'description'          => $component->get_description(),
					'query_type'           => isset( $component[ 'query_type' ] ) ? $component[ 'query_type' ] : 'product_ids',
					'query_ids'            => 'category_ids' === $component[ 'query_type' ] ? (array) $component[ 'assigned_category_ids' ] : (array) $component[ 'assigned_ids' ],
					'default_option_id'    => $component->get_default_option(),
					'thumbnail_id'         => $thumbnail_id,
					'thumbnail_src'        => $thumbnail_src,
					'quantity_min'         => $component->get_quantity( 'min' ),
					'quantity_max'         => $component->get_quantity( 'max' ),
					'priced_individually'  => $component->is_priced_individually(),
					'shipped_individually' => $component->is_shipped_individually(),
					'optional'             => $component->is_optional(),
					'discount'             => $component->get_discount(),
					'options_style'        => $component->get_options_style()
				);

			} else {
				$composite_data[ $component_id ] = $component->get_data();
			}
		}

		return $composite_data;
	}

	/*
	|--------------------------------------------------------------------------
	| Component methods: Options and properties.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get all component options (product IDs) available in a component.
	 *
	 * @param  string  $component_id
	 * @return array|null
	 */
	public function get_component_options( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->get_options() : null;
	}

	/**
	 * Get composited product.
	 *
	 * @param  string  $component_id
	 * @param  int     $product_id
	 * @return WC_CP_Product|null
	 */
	public function get_component_option( $component_id, $product_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->get_option( $product_id ) : null;
	}

	/**
	 * Grab component discount by component id.
	 *
	 * @param  string  $component_id
	 * @return string|null
	 */
	public function get_component_discount( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->get_discount() : null;
	}

	/**
	 * True if a component has only one option and is not optional.
	 *
	 * @param  string  $component_id
	 * @return boolean|null
	 */
	public function is_component_static( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->is_static() : null;
	}

	/**
	 * True if a component is optional.
	 *
	 * @param  string  $component_id
	 * @return boolean|null
	 */
	public function is_component_optional( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->is_optional() : null;
	}

	/**
	 * Get the default method to sort the options of a component.
	 *
	 * @param  int  $component_id
	 * @return string|null
	 */
	public function get_component_default_sorting_order( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->get_default_sorting_order() : null;
	}

	/**
	 * Get component sorting options, if enabled.
	 *
	 * @param  int  $component_id
	 * @return array|null
	 */
	public function get_component_sorting_options( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->get_sorting_options() : null;
	}

	/**
	 * Get component filtering options, if enabled.
	 *
	 * @param  int  $component_id
	 * @return array|null
	 */
	public function get_component_filtering_options( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->get_filtering_options() : null;
	}

	/*
	|--------------------------------------------------------------------------
	| Component methods: Templating.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Component options selection style.
	 *
	 * @param  string  $component_id
	 * @return string|null
	 */
	public function get_component_options_style( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->get_options_style() : null;
	}

	/**
	 * Thumbnail loop columns count.
	 *
	 * @param  string  $component_id
	 * @return int|null
	 */
	public function get_component_columns( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->get_columns() : null;
	}

	/**
	 * Thumbnail loop results per page.
	 *
	 * @param  string  $component_id
	 * @return int|null
	 */
	public function get_component_results_per_page( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->get_results_per_page() : null;
	}

	/**
	 * Controls whether component options loaded via ajax will be appended or paginated.
	 * When incompatible component options are set to be hidden, pagination cannot be used since results are filtered via js on the client side.
	 *
	 * @param  string  $component_id
	 * @return boolean
	 */
	public function paginate_component_options( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->paginate_options() : null;
	}

	/**
	 * Controls whether disabled component options will be hidden instead of greyed-out.
	 *
	 * @param  string  $component_id
	 * @return boolean|null
	 */
	public function hide_disabled_component_options( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->hide_disabled_options() : null;
	}

	/**
	 * Create an array of classes to use in the component layout templates.
	 *
	 * @param  string  $component_id
	 * @return array|null
	 */
	public function get_component_classes( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->get_classes() : null;
	}

	/*
	|--------------------------------------------------------------------------
	| Component methods: View state.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get the current query object that was used to build the component options view of a component.
	 * Should be called after 'WC_CP_Component_View::get_options()' has been used to set its view state.
	 *
	 * @param  int  $component_id
	 * @return WC_CP_Query|null|false
	 */
	public function get_current_component_options_query( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->view->get_options_query() : null;
	}

	/**
	 * Get component options to display. Fetched using a WP Query wrapper to allow advanced component options filtering / ordering / pagination.
	 *
	 * @param  string $component_id
	 * @param  array  $args
	 * @return array|null
	 */
	public function get_current_component_options( $component_id, $args = array() ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->view->get_options( $args ) : null;
	}

	/**
	 * Get the currently selected option (product id) for a component.
	 *
	 * @since  3.6.0
	 *
	 * @param  string $component_id
	 * @return int
	 */
	public function get_current_component_selection( $component_id ) {
		return $this->has_component( $component_id ) ? $this->get_component( $component_id )->view->get_selected_option() : null;
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	public function get_base_price() {
		_deprecated_function( __METHOD__ . '()', '3.8.0', __CLASS__ . '::get_price()' );
		return $this->price;
	}
	public function get_base_regular_price() {
		_deprecated_function( __METHOD__ . '()', '3.8.0', __CLASS__ . '::get_regular_price()' );
		return $this->regular_price;
	}
	public function get_base_sale_price() {
		_deprecated_function( __METHOD__ . '()', '3.8.0', __CLASS__ . '::get_sale_price()' );
		return $this->sale_price;
	}
	public function is_shipped_per_product() {
		_deprecated_function( __METHOD__ . '()', '3.7.0', __CLASS__ . '::contains()' );
		return $this->contains( 'shipped_individually' );
	}
	public function is_priced_per_product() {
		_deprecated_function( __METHOD__ . '()', '3.7.0', __CLASS__ . '::contains()' );
		return $this->contains( 'priced_individually' );
	}
	public function get_component_ordering_options( $component_id ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', __CLASS__ . '::get_component_sorting_options()' );
		return $this->get_component_sorting_options( $component_id );
	}
	public function get_component_default_ordering_option( $component_id ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', __CLASS__ . '::get_component_default_sorting_order()' );
		return $this->get_component_default_sorting_order( $component_id );
	}
	public function get_composited_product( $component_id, $product_id ) {
		_deprecated_function( __METHOD__ . '()', '3.7.0', __CLASS__ . '::get_component_option()' );
		return $this->get_component_option( $component_id, $product_id );
	}
	public function get_composite_selections_style() {
		_deprecated_function( __METHOD__ . '()', '3.6.0', __CLASS__ . '::get_component_options_style()' );

		$selections_style = $this->bto_selection_mode;

		if ( empty( $selections_style ) ) {
			$selections_style = 'dropdowns';
		}

		return $selections_style;
	}
	public function get_component_default_option( $component_id ) {
		_deprecated_function( __METHOD__ . '()', '3.6.0', __CLASS__ . '::get_current_component_selection()' );
		return $this->get_current_component_selection( $component_id );
	}
	public function get_current_component_scenarios( $component_id, $current_component_options ) {
		_deprecated_function( __METHOD__ . '()', '3.6.0', __CLASS__ . '::get_current_scenario_data()' );
		return $this->get_current_scenario_data( array( $component_id ) );
	}
	public function get_composite_scenario_data() {
		_deprecated_function( __METHOD__ . '()', '3.6.0', __CLASS__ . '::get_current_scenario_data()' );
		return $this->get_current_scenario_data();
	}
	public function get_bto_scenario_data() {
		_deprecated_function( __METHOD__ . '()', '2.5.0', __CLASS__ . '::get_composite_scenario_data()' );
		return $this->get_composite_scenario_data();
	}
	public function get_bto_data() {
		_deprecated_function( __METHOD__ . '()', '2.5.0', __CLASS__ . '::get_composite_data()' );
		return $this->get_composite_data();
	}
	public function get_bto_price_data() {
		_deprecated_function( __METHOD__ . '()', '2.5.0', __CLASS__ . '::get_composite_price_data()' );
		return $this->get_composite_price_data();
	}
}
