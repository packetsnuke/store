<?php
/**
 * Legacy WC_Product_Bundle class (WC <= 2.6)
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    5.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Bundle Class.
 *
 * @class    WC_Product_Bundle
 * @version  5.4.3
 */
class WC_Product_Bundle extends WC_Product {

	/**
	 * Array of bundled item data objects.
	 * @var array
	 */
	private $bundled_data_items = null;

	/**
	 * Prices calculated from raw price meta. Used in price filter and sorting queries.
	 * @var mixed
	 */
	private $min_raw_price;
	private $min_raw_regular_price;
	private $max_raw_price;
	private $max_raw_regular_price;

	/**
	 * Index of min/max bundled item quantities for use by PB plugins associated with configuration constraints.
	 *
	 * - Reference: Default min/max item quantities.
	 * - Optimal: Price-optimized quantities that satisfy one or more external constraints (min/max weight, min/max items count, etc) in addition to min/max item quantity.
	 * - Worst: Price worst-case quantities that satisfy one or more external constraints (min/max weight, min/max items count, etc) in addition to min/max item quantity.
	 * - Required: Price-optimized quantities that satisfy one or more external constraints (min/max weight, min/max items count, etc) in addition to min/max item quantity, with max item quantities capped by availability.
	 *
	 * Bundle min/max price calculations rely on optimal-min and worst-max quantities, while bundle availability calculations rely on required-min quantities.
	 * Optimal-max, worst-min and required-max are unused by default - however, bundle price methods such as 'get_bundle_price' and 'get_bundle_regular_price' accept a $calc_type argument.
	 *
	 * Note: Max quantities are always assumed to be a superset of their Min counterparts, a safe assumption for most non-compound constraint problems related to quantities (min/max item count, min/max weight, min/max from categories).
	 * Some constraint problems might not follow this pattern - for instance, compound constraint problems.
	 *
	 * @var array
	 */
	private $bundled_item_quantities = array(
		'reference' => array(
			'min' => array(),
			'max' => array()
		),
		'optimal'   => array(
			'min' => array(),
			'max' => array()
		),
		'worst'     => array(
			'min' => array(),
			'max' => array()
		),
		'required'  => array(
			'min' => array(),
			'max' => array()
		)
	);

	/**
	 * Array of bundle price data for consumption by the front-end script.
	 * @var array
	 */
	private $bundle_price_data = array();

	/**
	 * Runtime cache for bundle prices.
	 * @var array
	 */
	private $bundle_price_cache = array();

	/**
	 * Storage of 'contains' keys, most set during sync.
	 * @var array
	 */
	private $contains = array(
		'priced_individually'               => null,
		'shipped_individually'              => null,
		'optional'                          => false,
		'mandatory'                         => false,
		'on_backorder'                      => false,
		'subscriptions'                     => false,
		'subscriptions_priced_individually' => false,
		'nyp'                               => false,
		'hidden'                            => false,
		'non_purchasable'                   => false,
		'options'                           => false,
		'out_of_stock'                      => false, // Not including optional and zero min qty items (bundle can still be purchased).
		'out_of_stock_strict'               => false, // Including optional and zero min qty items (admin needs to be aware).
		'sold_in_multiples'                 => false,
		'sold_individually'                 => false,
		'discounted'                        => false,
		'discounted_mandatory'              => false
	);

	/**
	 * True if the bundle is in sync with bundled items.
	 * @var boolean
	 */
	private $is_synced = false;

	/**
	 * True if the bundle is a Name-Your-Price product.
	 * @var boolean
	 */
	private $is_nyp = false;

	/**
	 * Suppress range-style price html format.
	 * @var boolean
	 */
	private $force_price_html_from = false;

	/**
	 * Bundled products layout.
	 * @var string
	 */
	private $layout = 'default';

	/**
	 * Provides context when the "Sold Individually" option is set to 'yes': 'product' or 'configuration'.
	 * @var string
	 */
	private $sold_individually_context;

	/**
	 * Constructor.
	 *
	 * @param  mixed  $bundle
	 */
	public function __construct( $bundle ) {

		$this->product_type = 'bundle';

		parent::__construct( $bundle );

		// Single-product template layout.
		$this->layout = get_post_meta( $this->id, '_wc_pb_layout_style', true );

		// Minimum and maximum bundle prices. Obained from meta used in price filter widget and sorting results.
		$this->min_raw_price         = $this->min_bundle_price         = get_post_meta( $this->id, '_price', true );
		$this->min_raw_regular_price = $this->min_bundle_regular_price = get_post_meta( $this->id, '_regular_price', true );
		$this->max_raw_price         = $this->max_bundle_price         = get_post_meta( $this->id, '_wc_sw_max_price', true );
		$this->max_raw_regular_price = $this->max_bundle_regular_price = get_post_meta( $this->id, '_wc_sw_max_regular_price', true );

		$this->min_raw_price         = $this->contains( 'priced_individually' ) && '' !== $this->min_raw_price ? (double) $this->min_raw_price : $this->min_raw_price;
		$this->min_raw_regular_price = $this->contains( 'priced_individually' ) && '' !== $this->min_raw_regular_price ? (double) $this->min_raw_regular_price : $this->min_raw_regular_price;
		$this->max_raw_price         = $this->contains( 'priced_individually' ) && '' !== $this->max_raw_price ? (double) $this->max_raw_price : $this->max_raw_price;
		$this->max_raw_regular_price = $this->contains( 'priced_individually' ) && '' !== $this->max_raw_regular_price ? (double) $this->max_raw_regular_price : $this->max_raw_regular_price;

		$this->max_raw_price         = 9999999999.0 === $this->max_raw_price ? INF : $this->max_raw_price;
		$this->max_raw_regular_price = 9999999999.0 === $this->max_raw_regular_price ? INF : $this->max_raw_regular_price;

		// Is this a NYP product?
		if ( WC_PB()->compatibility->is_nyp( $this ) ) {
			$this->is_nyp = true;
		}

		// Base prices are saved separately to ensure the the original price meta always store the min bundle prices.
		$base_price         = $this->is_nyp() ? get_post_meta( $this->id, '_min_price', true ) : get_post_meta( $this->id, '_wc_pb_base_price', true );
		$base_regular_price = $this->is_nyp() ? $base_price : get_post_meta( $this->id, '_wc_pb_base_regular_price', true );
		$base_sale_price    = $this->is_nyp() ? '' : get_post_meta( $this->id, '_wc_pb_base_sale_price', true );

		// Patch price properties with base prices.
		$this->price         = $this->contains( 'priced_individually' ) ? (double) $base_price : $base_price;
		$this->regular_price = $this->contains( 'priced_individually' ) ? (double) $base_regular_price : $base_regular_price;
		$this->sale_price    = $this->contains( 'priced_individually' ) && '' !== $base_sale_price ? (double) $base_sale_price : $base_sale_price;

		// Property available since WC 2.5.
		if ( isset( $this->supports ) && is_array( $this->supports ) && 'yes' === get_post_meta( $this->id, '_wc_pb_edit_in_cart', true ) ) {
			$this->supports[] = 'edit_in_cart';
		}

		// "Sold Individually" option context.
		$sold_individually_context       = get_post_meta( $this->id, '_wc_pb_sold_individually_context', true );
		$this->sold_individually_context = in_array( $sold_individually_context, array( 'product', 'configuration' ) ) ? $sold_individually_context : 'product';

		// Populate these for back-compat.
		$this->per_product_pricing  = $this->contains( 'priced_individually' );
		$this->per_product_shipping = $this->contains( 'shipped_individually' );
	}

	/**
	 * Getter of bundle 'contains' properties.
	 *
	 * @since  5.0.0
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function contains( $key ) {

		if ( 'priced_individually' === $key ) {

			if ( is_null( $this->contains[ $key ] ) ) {

				$priced_items_exist = false;

				// Any items priced individually?
				$bundled_data_items = $this->get_bundled_data_items();

				if ( ! empty( $bundled_data_items ) ) {
					foreach ( $bundled_data_items as $bundled_data_item ) {
						if ( 'yes' === $bundled_data_item->get_meta( 'priced_individually' ) ) {
							$priced_items_exist = true;
						}
					}
				}

				/**
				 * 'woocommerce_bundle_contains_priced_items' filter.
				 *
				 * @param  boolean            $priced_items_exist
				 * @param  WC_Product_Bundle  $this
				 */
				$this->contains[ 'priced_individually' ] = apply_filters( 'woocommerce_bundle_contains_priced_items', $priced_items_exist, $this );
			}

		} elseif ( 'shipped_individually' === $key ) {

			if ( is_null( $this->contains[ $key ] ) ) {

				$shipped_items_exist = false;

				// Any items shipped individually?
				$bundled_data_items = $this->get_bundled_data_items();

				if ( ! empty( $bundled_data_items ) ) {
					foreach ( $bundled_data_items as $bundled_data_item ) {
						if ( 'yes' === $bundled_data_item->get_meta( 'shipped_individually' ) ) {
							$priced_items_exist = true;
						}
					}
				}

				/**
				 * 'woocommerce_bundle_contains_shipped_items' filter.
				 *
				 * @param  boolean            $shipped_items_exist
				 * @param  WC_Product_Bundle  $this
				 */
				$this->contains[ 'shipped_individually' ] = apply_filters( 'woocommerce_bundle_contains_shipped_items', $shipped_items_exist, $this );
			}

		} else {
			$this->maybe_sync_bundle();
		}

		return isset( $this->contains[ $key ] ) ? $this->contains[ $key ] : null;
	}

	/**
	 * Indicates if the bundle has been synced with its contents.
	 *
	 * @return boolean
	 */
	public function is_synced() {
		return $this->is_synced;
	}

	/**
	 * Sync bundle if not synced.
	 *
	 * @since 5.0.0
	 */
	public function maybe_sync_bundle() {
		if ( ! $this->is_synced() ) {
			$this->sync_bundle();
		}
	}

	/**
	 * Initialize bundled item data used for min/max price and availability calculations.
	 *
	 * @since 4.2.0
	 */
	public function sync_bundle() {

		$bundled_items = $this->get_bundled_items();

		if ( empty( $bundled_items ) ) {
			return;
		}

		$min_raw_price         = $this->price;
		$min_raw_regular_price = $this->regular_price;
		$max_raw_price         = $this->price;
		$max_raw_regular_price = $this->regular_price;

		if ( $this->is_nyp() ) {
			$max_raw_price = $max_raw_regular_price = INF;
		}

		$is_front_end = WC_PB_Helpers::is_front_end();

		// Initialize quantities for min/max pricing and availability calculations.
		foreach ( $bundled_items as $bundled_item ) {

			$min_qty = $bundled_item->is_optional() ? 0 : $bundled_item->get_quantity( 'min' );
			$max_qty = $bundled_item->get_quantity( 'max' );

			$this->bundled_item_quantities[ 'reference' ][ 'min' ][ $bundled_item->item_id ] = $min_qty;
			$this->bundled_item_quantities[ 'optimal' ][ 'min' ][ $bundled_item->item_id ]   = $min_qty;
			$this->bundled_item_quantities[ 'worst' ][ 'min' ][ $bundled_item->item_id ]     = $min_qty;
			$this->bundled_item_quantities[ 'required' ][ 'min' ][ $bundled_item->item_id ]  = $min_qty;
			$this->bundled_item_quantities[ 'reference' ][ 'max' ][ $bundled_item->item_id ] = $max_qty;
			$this->bundled_item_quantities[ 'optimal' ][ 'max' ][ $bundled_item->item_id ]   = $max_qty;
			$this->bundled_item_quantities[ 'worst' ][ 'max' ][ $bundled_item->item_id ]     = $max_qty;
			$this->bundled_item_quantities[ 'required' ][ 'max' ][ $bundled_item->item_id ]  = $max_qty;
		}

		/**
		 * 'woocommerce_bundled_item_optimal_price_quantities' filter.
		 *
		 * Price-optimized quantities that best satisfy all existing constraints (min/max item quantity, min/max weight, min/max items count, etc).
		 *
		 * @param  array              $quantities
		 * @param  WC_Product_Bundle  $this
		 */
		$this->bundled_item_quantities[ 'optimal' ] = apply_filters( 'woocommerce_bundled_item_optimal_price_quantities', $this->bundled_item_quantities[ 'reference' ], $this );

		/**
		 * 'woocommerce_bundled_item_worst_price_quantities' filter.
		 *
		 * Worst-price quantities that best satisfy all existing constraints (min/max item quantity, min/max weight, min/max items count, etc).
		 *
		 * @param  array              $quantities
		 * @param  WC_Product_Bundle  $this
		 */
		$this->bundled_item_quantities[ 'worst' ] = apply_filters( 'woocommerce_bundled_item_worst_price_quantities', $this->bundled_item_quantities[ 'reference' ], $this );


		/**
		 * 'woocommerce_bundled_item_required_quantities' filter.
		 *
		 * Price-optimized quantities that best satisfy all existing constraints (min/max item quantity, min/max weight, min/max items count, etc), including availability.
		 *
		 * @param  array              $quantities
		 * @param  WC_Product_Bundle  $this
		 */
		$this->bundled_item_quantities[ 'required' ] = apply_filters( 'woocommerce_bundled_item_required_quantities', $this->bundled_item_quantities[ 'reference' ], $this );


		// Analyze bundled items.
		foreach ( $bundled_items as $bundled_item ) {

			$min_quantity = $this->bundled_item_quantities[ 'required' ][ 'min' ][ $bundled_item->item_id ];
			$max_quantity = $this->bundled_item_quantities[ 'required' ][ 'max' ][ $bundled_item->item_id ];

			if ( $bundled_item->is_sold_individually() ) {
				$this->contains[ 'sold_individually' ] = true;
			} else {
				$this->contains[ 'sold_in_multiples' ] = true;
			}

			if ( $bundled_item->is_optional() ) {
				$this->contains[ 'optional' ] = true;
				$this->force_price_html_from  = true;
			} else {
				$this->contains[ 'mandatory' ] = true;
			}

			if ( false === $bundled_item->has_enough_stock( $min_quantity ) ) {
				$this->contains[ 'out_of_stock_strict' ] = true;
				if ( false === $bundled_item->is_optional() && $min_quantity !== 0 ) {
					$this->contains[ 'out_of_stock' ] = true;
				}
			}

			if ( $bundled_item->is_on_backorder() && $bundled_item->product->backorders_require_notification() && false === $bundled_item->is_optional() && $min_quantity !== 0 ) {
				$this->contains[ 'on_backorder' ] = true;
			}

			if ( false === $bundled_item->is_purchasable() && false === $bundled_item->is_optional() && $min_quantity !== 0 ) {
				$this->contains[ 'non_purchasable' ] = true;
			}

			if ( $bundled_item->get_discount() > 0 ) {
				$this->contains[ 'discounted' ] = true;
				if ( false === $bundled_item->is_optional() && $min_quantity !== 0 ) {
					$this->contains[ 'discounted_mandatory' ] = true;
				}
			}

			if ( $bundled_item->is_nyp() ) {
				$this->contains[ 'nyp' ] = true;
			}

			if ( $bundled_item->is_subscription() ) {

				if ( $this->contains[ 'subscriptions' ] ) {
					$this->contains[ 'multiple_subscriptions' ] = true;
				}

				$this->contains[ 'subscriptions' ] = true;

				if ( $bundled_item->is_priced_individually() ) {
					$this->contains[ 'subscriptions_priced_individually' ] = true;
				}

				// If it's a variable sub with a variable price, show 'From:' string before Bundle price.
				if ( $bundled_item->is_variable_subscription() ) {
					if ( $bundled_item->product->min_variation_price !== $bundled_item->product->max_variation_price || $bundled_item->product->subscription_period !== $bundled_item->product->max_variation_period || $bundled_item->product->subscription_period_interval !== $bundled_item->product->max_variation_period_interval ) {
						$this->force_price_html_from = true;
					}
				}
			}

			// Significant cost due to get_product_addons - skip this in the admin area since it is only used to modify add to cart button behaviour.
			if ( $is_front_end && false === $bundled_item->is_optional() && $bundled_item->requires_input() ) {
				$this->contains[ 'options' ] = true;
			}

			if ( false === $bundled_item->is_visible() ) {
				$this->contains[ 'hidden' ] = true;
			}
		}

		// Sync min/max prices.
		foreach ( $bundled_items as $bundled_item ) {
			if ( $bundled_item->is_priced_individually() ) {

				$bundled_item_qty_min = $this->bundled_item_quantities[ 'optimal' ][ 'min' ][ $bundled_item->item_id ];
				$bundled_item_qty_max = $this->bundled_item_quantities[ 'worst' ][ 'max' ][ $bundled_item->item_id ];

				if ( $bundled_item_qty_min !== $bundled_item_qty_max ) {
					$this->force_price_html_from = true;
				}

				$min_raw_price         += $bundled_item_qty_min * (double) $bundled_item->min_price;
				$min_raw_regular_price += $bundled_item_qty_min * (double) $bundled_item->min_regular_price;

				if ( ! $bundled_item_qty_max ) {
					$max_raw_price = $max_raw_regular_price = INF;
				}

				$item_max_raw_price         = INF !== $bundled_item->max_price ? (double) $bundled_item->max_price : INF;
				$item_max_raw_regular_price = INF !== $bundled_item->max_regular_price ? (double) $bundled_item->max_regular_price : INF;

				if ( INF !== $max_raw_price ) {
					if ( INF !== $item_max_raw_price ) {
						$max_raw_price         += $bundled_item_qty_max * $item_max_raw_price;
						$max_raw_regular_price += $bundled_item_qty_max * $item_max_raw_regular_price;
					} else {
						$this->force_price_html_from = true;
						$max_raw_price = $max_raw_regular_price = INF;
					}
				}
			}
		}

		$this->is_synced = true;

		// Allow adding to cart via ajax if no user input is required.
		if ( isset( $this->supports ) && is_array( $this->supports ) && $is_front_end && ! $this->requires_input() ) {
			$this->supports[] = 'ajax_add_to_cart';
		}

		/**
		 * 'woocommerce_bundles_synced_bundle' action.
		 *
		 * @param  WC_Product_Bundle  $this
		 */
		do_action( 'woocommerce_bundles_synced_bundle', $this );

		/**
		 * 'woocommerce_bundles_update_price_meta' filter.
		 *
		 * Use this to prevent bundle min/max raw price meta from being updated.
		 *
		 * @param  boolean            $update
		 * @param  WC_Product_Bundle  $this
		 */
		$save = apply_filters( 'woocommerce_bundles_update_price_meta', true, $this ) && ! defined( 'WC_PB_UPDATING' );

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

		if ( $this->min_raw_regular_price !== $min_raw_regular_price ) {
			if ( $save ) {
				update_post_meta( $this->id, '_regular_price', $min_raw_regular_price );
			}
		}

		if ( $this->max_raw_price !== $max_raw_price ) {
			if ( $save ) {
				update_post_meta( $this->id, '_wc_sw_max_price', INF === $max_raw_price ? 9999999999.0 : $max_raw_price );
			}
		}

		if ( $this->max_raw_regular_price !== $max_raw_regular_price ) {
			if ( $save ) {
				update_post_meta( $this->id, '_wc_sw_max_regular_price', $max_raw_regular_price ? 9999999999.0 : $max_raw_regular_price );
			}
		}

		// Update raw price props.
		$this->min_raw_price            = $min_raw_price;
		$this->min_raw_regular_price    = $min_raw_regular_price;
		$this->max_raw_regular_price    = $max_raw_regular_price;
		$this->max_raw_price            = $max_raw_price;

		// Update these for back-compat.
		$this->min_bundle_price         = $this->min_raw_price;
		$this->min_bundle_regular_price = $this->min_raw_regular_price;
		$this->max_bundle_price         = $this->max_raw_price;
		$this->max_bundle_regular_price = $this->max_raw_regular_price;
	}

	/**
	 * Stores bundle pricing data used by the front-end script.
	 *
	 * @since 4.7.0
	 */
	private function load_price_data() {

		if ( empty( $this->bundle_price_data ) ) {

			$bundle_price_data = array();

			$raw_bundle_price_min = $this->get_bundle_price( 'min' );
			$raw_bundle_price_max = $this->get_bundle_price( 'max' );

			$bundle_price_data[ 'raw_bundle_price_min' ]     = (double) $raw_bundle_price_min;
			$bundle_price_data[ 'raw_bundle_price_max' ]     = '' === $raw_bundle_price_max ? '' : (double) $raw_bundle_price_max;

			$bundle_price_data[ 'is_purchasable' ]           = $this->is_purchasable() ? 'yes' : 'no';
			$bundle_price_data[ 'show_free_string' ]         = ( $this->contains( 'priced_individually' ) ? apply_filters( 'woocommerce_bundle_show_free_string', false, $this ) : true ) ? 'yes' : 'no';

			$bundle_price_data[ 'prices' ]                   = array();
			$bundle_price_data[ 'regular_prices' ]           = array();

			$bundle_price_data[ 'prices_tax' ]               = array();

			$bundle_price_data[ 'addons_prices' ]            = array();

			$bundle_price_data[ 'quantities' ]               = array();

			$bundle_price_data[ 'product_ids' ]              = array();

			$bundle_price_data[ 'is_sold_individually' ]     = array();

			$bundle_price_data[ 'recurring_prices' ]         = array();
			$bundle_price_data[ 'regular_recurring_prices' ] = array();

			$bundle_price_data[ 'recurring_html' ]           = array();
			$bundle_price_data[ 'recurring_keys' ]           = array();

			WC_PB_Product_Prices::extend_price_display_precision();

			$base_price_incl_tax = $this->get_price_including_tax( 1, 1000 );
			$base_price_excl_tax = $this->get_price_excluding_tax( 1, 1000 );

			WC_PB_Product_Prices::reset_price_display_precision();

			$bundle_price_data[ 'base_price' ]         = $this->get_price();
			$bundle_price_data[ 'base_regular_price' ] = $this->get_regular_price();
			$bundle_price_data[ 'base_price_tax' ]     = $base_price_incl_tax / $base_price_excl_tax;

			$totals = new stdClass;

			$totals->price          = 0.0;
			$totals->regular_price  = 0.0;
			$totals->price_incl_tax = 0.0;
			$totals->price_excl_tax = 0.0;

			$bundle_price_data[ 'total' ]             = 0.0;
			$bundle_price_data[ 'regular_total' ]     = 0.0;
			$bundle_price_data[ 'total_incl_tax' ]    = 0.0;
			$bundle_price_data[ 'total_excl_tax' ]    = 0.0;

			$bundle_price_data[ 'base_price_totals' ] = $totals;
			$bundle_price_data[ 'totals' ]            = $totals;
			$bundle_price_data[ 'recurring_totals' ]  = $totals;

			$bundled_items = $this->get_bundled_items();

			if ( empty( $bundled_items ) ) {
				return;
			}

			foreach ( $bundled_items as $bundled_item ) {

				if ( ! $bundled_item->is_purchasable() ) {
					continue;
				}

				WC_PB_Product_Prices::extend_price_display_precision();

				$price_incl_tax = $bundled_item->product->get_price_including_tax( 1, 1000 );
				$price_excl_tax = $bundled_item->product->get_price_excluding_tax( 1, 1000 );

				WC_PB_Product_Prices::reset_price_display_precision();

				$bundle_price_data[ 'is_nyp' ][ $bundled_item->item_id ]                             = $bundled_item->is_nyp() ? 'yes' : 'no';

				$bundle_price_data[ 'product_ids' ][ $bundled_item->item_id ]                        = $bundled_item->product_id;

				$bundle_price_data[ 'is_sold_individually' ][ $bundled_item->item_id ]               = $bundled_item->is_sold_individually() ? 'yes' : 'no';
				$bundle_price_data[ 'is_priced_individually' ][ $bundled_item->item_id ]             = $bundled_item->is_priced_individually() ? 'yes' : 'no';

				$bundle_price_data[ 'prices' ][ $bundled_item->item_id ]                             = $bundled_item->get_price( 'min' );
				$bundle_price_data[ 'regular_prices' ][ $bundled_item->item_id ]                     = $bundled_item->get_regular_price( 'min' );

				$bundle_price_data[ 'prices_tax' ][ $bundled_item->item_id ]                         = $price_incl_tax / $price_excl_tax;

				$bundle_price_data[ 'addons_prices' ][ $bundled_item->item_id ]                      = '';

				$bundle_price_data[ 'bundled_item_' . $bundled_item->item_id . '_totals' ]           = $totals;
				$bundle_price_data[ 'bundled_item_' . $bundled_item->item_id . '_recurring_totals' ] = $totals;

				$bundle_price_data[ 'quantities' ][ $bundled_item->item_id ]                         = '';

				$bundle_price_data[ 'recurring_prices' ][ $bundled_item->item_id ]                   = '';
				$bundle_price_data[ 'regular_recurring_prices' ][ $bundled_item->item_id ]           = '';

				// Store sub recurring key for summation (variable sub keys are stored in variations data).
				$bundle_price_data[ 'recurring_html' ][ $bundled_item->item_id ]                     = '';
				$bundle_price_data[ 'recurring_keys' ][ $bundled_item->item_id ]                     = '';

				if ( $bundled_item->is_subscription() && ! $bundled_item->is_variable_subscription() ) {

					$bundle_price_data[ 'recurring_prices' ][ $bundled_item->item_id ]               = $bundled_item->get_recurring_price( 'min' );
					$bundle_price_data[ 'regular_recurring_prices' ][ $bundled_item->item_id ]       = $bundled_item->get_regular_recurring_price( 'min' );

					$bundle_price_data[ 'recurring_keys' ][ $bundled_item->item_id ]                 = str_replace( '_synced', '', WC_Subscriptions_Cart::get_recurring_cart_key( array( 'data' => $bundled_item->product ), ' ' ) );
					$bundle_price_data[ 'recurring_html' ][ $bundled_item->item_id ]                 = WC_PB_Product_Prices::get_recurring_price_html_component( $bundled_item->product );
				}
			}

			if ( $this->contains( 'subscriptions_priced_individually' ) ) {
				if ( $this->get_bundle_regular_price( 'min' ) != 0 ) {
					$bundle_price_data[ 'price_string' ] = sprintf( _x( '%1$s<span class="bundled_subscriptions_price_html" style="display:none"> now,</br>then %2$s</span>', 'subscription price html suffix', 'ultimatewoo-pro' ), '%s', '%r' );
				} else {
					$bundle_price_data[ 'price_string' ] = '<span class="bundled_subscriptions_price_html">%r</span>';
				}
			} else {
				$bundle_price_data[ 'price_string' ] = '%s';
			}

			/**
			 * 'woocommerce_bundle_price_data' filter.
			 *
			 * Filter price data - to be encoded and passed to JS.
			 *
			 * @param  array              $bundle_price_data
			 * @param  WC_Product_Bundle  $this
			 */
			$this->bundle_price_data = apply_filters( 'woocommerce_bundle_price_data', $bundle_price_data, $this );
		}
	}

	/**
	 * Supported layouts.
	 *
	 * @return array
	 */
	public static function get_supported_layouts() {
		return apply_filters( 'woocommerce_bundles_supported_layouts', array(
			'default' => __( 'Standard', 'ultimatewoo-pro' ),
			'tabular' => __( 'Tabular', 'ultimatewoo-pro' ),
		) );
	}

	/**
	 * Layout getter.
	 *
	 * @return string
	 */
	public function get_layout() {
		return array_key_exists( $this->layout, self::get_supported_layouts() ) ? $this->layout : 'default';
	}

	/**
	 * Gets price data array. Contains localized strings and price data passed to JS.
	 *
	 * @return array
	 */
	public function get_bundle_price_data() {

		$this->maybe_sync_bundle();
		$this->load_price_data();

		return $this->bundle_price_data;
	}

	/**
	 * Bundle is a NYP product.
	 *
	 * @return boolean
	 */
	public function is_nyp() {
		return $this->is_nyp;
	}

	/**
	 * Get min/max bundle price.
	 *
	 * @param  string   $min_or_max
	 * @param  boolean  $display
	 * @param  string   $calc_type
	 * @return double
	 */
	public function get_bundle_price( $min_or_max = 'min', $display = false, $calc_type = '' ) {

		if ( $this->contains( 'priced_individually' ) ) {

			$this->maybe_sync_bundle();

			$min_or_max      = in_array( $min_or_max, array( 'min', 'max' ) ) ? $min_or_max : 'min';
			$price_calc_type = '' !== $calc_type && in_array( $calc_type, array( 'optimal', 'worst' ) ) ? $calc_type : ( 'min' === $min_or_max ? 'optimal' : 'worst' );
			$cache_key       = md5( json_encode( apply_filters( 'woocommerce_bundle_prices_hash', array(
				'type'       => 'price',
				'calc'       => $price_calc_type,
				'display'    => $display,
				'min_or_max' => $min_or_max
			), $this ) ) );

			if ( isset( $this->bundle_price_cache[ $cache_key ] ) ) {
				$price = $this->bundle_price_cache[ $cache_key ];
			} else {

				$prop = $min_or_max . '_raw_price';

				if ( '' === $this->$prop || INF === $this->$prop ) {
					$price = '';
				} else {

					$price         = $display ? WC_PB_Product_Prices::get_product_display_price( $this, $this->get_price() ) : $this->get_price();
					$bundled_items = $this->get_bundled_items();

					if ( ! empty( $bundled_items ) ) {
						foreach ( $bundled_items as $bundled_item ) {

							$bundled_item_qty = $this->bundled_item_quantities[ $price_calc_type ][ $min_or_max ][ $bundled_item->item_id ];

							if ( $bundled_item_qty ) {
								$price += $bundled_item_qty * $bundled_item->get_price( $min_or_max, $display );
							}
						}
					}
				}

				$this->bundle_price_cache[ $cache_key ] = $price;
			}

		} else {

			$price = parent::get_price();

			if ( $display ) {
				$price = parent::get_display_price( $price );
			}
		}

		return $price;
	}

	/**
	 * Get min/max bundle regular price.
	 *
	 * @param  string   $min_or_max
	 * @param  boolean  $display
	 * @param  string   $calc_type
	 * @return double
	 */
	public function get_bundle_regular_price( $min_or_max = 'min', $display = false, $calc_type = '' ) {

		if ( $this->contains( 'priced_individually' ) ) {

			$this->maybe_sync_bundle();

			$min_or_max      = in_array( $min_or_max, array( 'min', 'max' ) ) ? $min_or_max : 'min';
			$price_calc_type = '' !== $calc_type && in_array( $calc_type, array( 'optimal', 'worst' ) ) ? $calc_type : ( 'min' === $min_or_max ? 'optimal' : 'worst' );
			$cache_key       = md5( json_encode( apply_filters( 'woocommerce_bundle_prices_hash', array(
				'type'       => 'regular_price',
				'calc'       => $price_calc_type,
				'display'    => $display,
				'min_or_max' => $min_or_max
			), $this ) ) );

			if ( isset( $this->bundle_price_cache[ $cache_key ] ) ) {
				$price = $this->bundle_price_cache[ $cache_key ];
			} else {

				$prop = $min_or_max . '_raw_regular_price';

				if ( '' === $this->$prop || INF === $this->$prop ) {
					$price = '';
				} else {

					$price         = $display ? WC_PB_Product_Prices::get_product_display_price( $this, $this->get_regular_price() ) : $this->get_regular_price();
					$bundled_items = $this->get_bundled_items();

					if ( ! empty( $bundled_items ) ) {
						foreach ( $bundled_items as $bundled_item ) {

							$bundled_item_qty = $this->bundled_item_quantities[ $price_calc_type ][ $min_or_max ][ $bundled_item->item_id ];

							if ( $bundled_item_qty ) {
								$price += $bundled_item_qty * $bundled_item->get_regular_price( $min_or_max, $display, true );
							}
						}
					}
				}

				$this->bundle_price_cache[ $cache_key ] = $price;
			}

		} else {

			$price = parent::get_regular_price();

			if ( $display ) {
				$price = parent::get_display_price( $price );
			}
		}

		return $price;
	}

	/**
	 * Bundle price including tax.
	 *
	 * @param  string   $min_or_max
	 * @param  integer  $qty
	 * @param  string   $calc_type
	 * @return double
	 */
	public function get_bundle_price_including_tax( $min_or_max = 'min', $qty = 1, $calc_type = '' ) {

		if ( $this->contains( 'priced_individually' ) ) {

			$this->maybe_sync_bundle();

			$min_or_max      = in_array( $min_or_max, array( 'min', 'max' ) ) ? $min_or_max : 'min';
			$price_calc_type = '' !== $calc_type && in_array( $calc_type, array( 'optimal', 'worst' ) ) ? $calc_type : ( 'min' === $min_or_max ? 'optimal' : 'worst' );
			$cache_key       = md5( json_encode( apply_filters( 'woocommerce_bundle_prices_hash', array(
				'type'       => 'price_incl_tax',
				'calc'       => $price_calc_type,
				'min_or_max' => $min_or_max,
				'qty'        => $qty
			), $this ) ) );

			if ( isset( $this->bundle_price_cache[ $cache_key ] ) ) {
				$price = $this->bundle_price_cache[ $cache_key ];
			} else {

				$prop = $min_or_max . '_raw_price';

				if ( '' === $this->$prop || INF === $this->$prop ) {
					$price = '';
				} else {

					$price         = $this->get_price_including_tax( $qty, $this->get_price() );
					$bundled_items = $this->get_bundled_items();

					if ( ! empty( $bundled_items ) ) {
						foreach ( $bundled_items as $bundled_item ) {

							$bundled_item_qty = $qty * $this->bundled_item_quantities[ $price_calc_type ][ $min_or_max ][ $bundled_item->item_id ];

							if ( $bundled_item_qty ) {
								$price += $bundled_item->get_price_including_tax( $min_or_max, $bundled_item_qty );
							}
						}
					}
				}

				$this->bundle_price_cache[ $cache_key ] = $price;
			}

		} else {

			$price = parent::get_price_including_tax( $qty, parent::get_price() );
		}

		return $price;
	}

	/**
	 * Min/max bundle price excl tax.
	 *
	 * @param  string   $min_or_max
	 * @param  integer  $qty
	 * @param  string   $calc_type
	 * @return double
	 */
	public function get_bundle_price_excluding_tax( $min_or_max = 'min', $qty = 1, $calc_type = '' ) {

		if ( $this->contains( 'priced_individually' ) ) {

			$this->maybe_sync_bundle();

			$min_or_max      = in_array( $min_or_max, array( 'min', 'max' ) ) ? $min_or_max : 'min';
			$price_calc_type = '' !== $calc_type && in_array( $calc_type, array( 'optimal', 'worst' ) ) ? $calc_type : ( 'min' === $min_or_max ? 'optimal' : 'worst' );
			$cache_key       = md5( json_encode( apply_filters( 'woocommerce_bundle_prices_hash', array(
				'type'       => 'price_excl_tax',
				'calc'       => $price_calc_type,
				'min_or_max' => $min_or_max,
				'qty'        => $qty
			), $this ) ) );

			if ( isset( $this->bundle_price_cache[ $cache_key ] ) ) {
				$price = $this->bundle_price_cache[ $cache_key ];
			} else {

				$prop = $min_or_max . '_raw_price';

				if ( '' === $this->$prop || INF === $this->$prop ) {
					$price = '';
				} else {

					$price         = $this->get_price_excluding_tax( $qty, $this->get_price() );
					$bundled_items = $this->get_bundled_items();

					if ( ! empty( $bundled_items ) ) {
						foreach ( $bundled_items as $bundled_item ) {

							$bundled_item_qty = $qty * $this->bundled_item_quantities[ $price_calc_type ][ $min_or_max ][ $bundled_item->item_id ];

							if ( $bundled_item_qty ) {
								$price += $bundled_item->get_price_excluding_tax( $min_or_max, $bundled_item_qty );
							}
						}
					}
				}

				$this->bundle_price_cache[ $cache_key ] = $price;
			}

		} else {

			$price = parent::get_price_excluding_tax( $qty, parent::get_price() );
		}

		return $price;
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
					'{price_including_tax}' => wc_price( $this->get_bundle_price_including_tax( 'min', $qty ) ),
					'{price_excluding_tax}' => wc_price( $this->get_bundle_price_excluding_tax( 'min', $qty ) )
				);

				$price_suffix = str_replace( array_keys( $replacements ), array_values( $replacements ), ' <small class="woocommerce-price-suffix">' . wp_kses_post( $suffix ) . '</small>' );
			}

			/**
			 * 'woocommerce_get_price_suffix' filter.
			 *
			 * @param  string             $price_suffix
			 * @param  WC_Product_Bundle  $this
			 */
			return apply_filters( 'woocommerce_get_price_suffix', $price_suffix, $this );

		} else {
			return parent::get_price_suffix();
		}
	}

	/**
	 * Calculate subscriptions price html component by breaking up bundled subs into recurring scheme groups and adding up all prices in each group.
	 *
	 * @return string
	 */
	public function apply_subs_price_html( $price ) {

		$bundled_items = $this->get_bundled_items();

		if ( ! empty( $bundled_items ) ) {

			$subs_details            = array();
			$subs_details_html       = array();
			$non_optional_subs_exist = false;

			foreach ( $bundled_items as $bundled_item_id => $bundled_item ) {

				if ( $bundled_item->is_subscription() ) {

					$bundled_product    = $bundled_item->product;
					$bundled_product_id = $bundled_item->product_id;

					if ( $bundled_item->is_variable_subscription() ) {
						$product_id = get_post_meta( $bundled_product_id, '_min_price_variation_id', true );
						$product    = wc_get_product( $product_id );
					} else {
						$product = $bundled_product;
					}

					$sub_string = str_replace( '_synced', '', WC_Subscriptions_Cart::get_recurring_cart_key( array( 'data' => $product ), ' ' ) );

					if ( ! isset( $subs_details[ $sub_string ][ 'bundled_items' ] ) ) {
						$subs_details[ $sub_string ][ 'bundled_items' ] = array();
					}

					if ( ! isset( $subs_details[ $sub_string ][ 'price' ] ) ) {
						$subs_details[ $sub_string ][ 'price' ]         = 0;
						$subs_details[ $sub_string ][ 'regular_price' ] = 0;
						$subs_details[ $sub_string ][ 'is_range' ]      = false;
					}

					$subs_details[ $sub_string ][ 'bundled_items' ][] = $bundled_item_id;

					$subs_details[ $sub_string ][ 'price' ]         += $this->bundled_item_quantities[ 'optimal' ][ 'min' ][ $bundled_item_id ] * WC_PB_Product_Prices::get_product_display_price( $product, $bundled_item->min_recurring_price );
					$subs_details[ $sub_string ][ 'regular_price' ] += $this->bundled_item_quantities[ 'optimal' ][ 'min' ][ $bundled_item_id ] * WC_PB_Product_Prices::get_product_display_price( $product, $bundled_item->min_regular_recurring_price );

					if ( $bundled_item->is_variable_subscription() ) {
						if ( $bundled_product->min_variation_price !== $bundled_product->max_variation_price || $bundled_product->subscription_period !== $bundled_product->max_variation_period || $bundled_product->subscription_period_interval !== $bundled_product->max_variation_period_interval ) {
							if ( $bundled_item->is_priced_individually() ) {
								$subs_details[ $sub_string ][ 'is_range' ] = true;
							}
						}
					}

					if ( ! isset( $subs_details[ $sub_string ][ 'price_html' ] ) ) {
						$subs_details[ $sub_string ][ 'price_html' ] = WC_PB_Product_Prices::get_recurring_price_html_component( $product );
					}
				}
			}

			if ( ! empty( $subs_details ) ) {

				$from_string = $this->get_bundle_regular_price( 'min' ) != 0 ? _x( '<span class="from">from </span>', 'min-price', 'ultimatewoo-pro' ) : _x( '<span class="from">From: </span>', 'min-price', 'ultimatewoo-pro' );

				foreach ( $subs_details as $sub_details ) {
					if ( $sub_details[ 'price' ] > 0 ) {

						$sub_price_html = wc_price( $sub_details[ 'price' ] );

						if ( $sub_details[ 'price' ] !== $sub_details[ 'regular_price' ] ) {

							$sub_regular_price_html = wc_price( $sub_details[ 'regular_price' ] );

							if ( $sub_details[ 'is_range' ] ) {
								$sub_price_html = sprintf( _x( '%1$s%2$s', 'Price range: from', 'ultimatewoo-pro' ), $from_string, $this->get_price_html_from_to( $sub_regular_price_html, $sub_price_html ) );
							} else {
								$sub_price_html = $this->get_price_html_from_to( $sub_regular_price_html, $sub_price_html );
							}

						} elseif ( $sub_details[ 'price' ] == 0 && ! $sub_details[ 'is_range' ] ) {
							$sub_price_html = __( 'Free!', 'woocommerce' );
						} else {
							if ( $sub_details[ 'is_range' ] ) {
								$sub_price_html = sprintf( _x( '%1$s%2$s', 'Price range: from', 'ultimatewoo-pro' ), $from_string, $sub_price_html );
							}
						}

						$sub_price_details_html = sprintf( $sub_details[ 'price_html' ], $sub_price_html );
						$subs_details_html[]    = '<span class="bundled_sub_price_html">' . $sub_price_details_html . '</span>';
					}
				}

				$price_html        = implode( '<span class="plus"> + </span>', $subs_details_html );
				$has_multiple_subs = sizeof( $subs_details_html ) > 1;
				$show_now          = ( $has_multiple_subs || $sub_price_html !== $price ) && $this->get_bundle_regular_price( 'min' ) != 0;

				if ( $show_now ) {
					$price = sprintf( _x( '%1$s<span class="bundled_subscriptions_price_html" %2$s> now,</br>then %3$s</span>', 'subscription price html suffix', 'ultimatewoo-pro' ), $price, ! empty( $subs_details_html ) ? '' : 'style="display:none"', $price_html );
				} else {
					$price = '<span class="bundled_subscriptions_price_html">' . $price_html . '</span>';
				}
			}
		}

		return $price;
	}

	/**
	 * Returns range style html price string without min and max.
	 *
	 * @param  mixed  $price
	 * @return string
	 */
	public function get_price_html( $price = '' ) {

		if ( ! $this->is_purchasable() ) {
			/**
			 * 'woocommerce_bundle_empty_price_html' filter.
			 *
			 * @param  string             $price_html
			 * @param  WC_Product_Bundle  $this
			 */
			return apply_filters( 'woocommerce_bundle_empty_price_html', '', $this );
		}

		if ( $this->contains( 'priced_individually' ) ) {

			$this->maybe_sync_bundle();

			// Get the price.
			if ( '' === $this->get_bundle_price( 'min' ) ) {
				$price = apply_filters( 'woocommerce_bundle_empty_price_html', '', $this );
			} else {

				/**
				 * 'woocommerce_bundle_force_old_style_price_html' filter.
				 *
				 * Used to suppress the range-style display of bundle price html strings.
				 *
				 * @param  boolean            $force_suppress_range_format
				 * @param  WC_Product_Bundle  $this
				 */
				$suppress_range_price_html = $this->force_price_html_from || apply_filters( 'woocommerce_bundle_force_old_style_price_html', false, $this );

				if ( $suppress_range_price_html ) {

					$price = wc_price( $this->get_bundle_price( 'min', true ) );

					if ( $this->get_bundle_regular_price( 'min', true ) !== $this->get_bundle_price( 'min', true ) ) {

						$regular_price = wc_price( $this->get_bundle_regular_price( 'min', true ) );

						if ( $this->get_bundle_price( 'min', true ) !== $this->get_bundle_price( 'max', true ) ) {
							$price = sprintf( _x( '%1$s%2$s', 'Price range: from', 'ultimatewoo-pro' ), $this->get_price_html_from_text(), $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix() );
						} else {
							$price = $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix();
						}

						/**
						 * 'woocommerce_bundle_sale_price_html' filter.
						 *
						 * @param  string             $sale_price_html
						 * @param  WC_Product_Bundle  $this
						 */
						$price = apply_filters( 'woocommerce_bundle_sale_price_html', $price, $this );

					} elseif ( 0.0 === $this->get_bundle_price( 'min', true ) && 0.0 === $this->get_bundle_price( 'max', true ) ) {

						$free_string = apply_filters( 'woocommerce_bundle_show_free_string', false, $this ) ? __( 'Free!', 'woocommerce' ) : $price;
						$price       = apply_filters( 'woocommerce_bundle_free_price_html', $free_string, $this );

					} else {

						if ( $this->get_bundle_price( 'min', true ) !== $this->get_bundle_price( 'max', true ) || $this->force_price_html_from ) {
							$price = sprintf( _x( '%1$s%2$s', 'Price range: from', 'ultimatewoo-pro' ), $this->get_price_html_from_text(), $price . $this->get_price_suffix() );
						} else {
							$price = $price . $this->get_price_suffix();
						}

						/**
						 * 'woocommerce_bundle_price_html' filter.
						 *
						 * @param  string             $price_html
						 * @param  WC_Product_Bundle  $this
						 */
						$price = apply_filters( 'woocommerce_bundle_price_html', $price, $this );
					}

				} else {

					if ( $this->get_bundle_price( 'min', true ) !== $this->get_bundle_price( 'max', true ) ) {
						$price = sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $this->get_bundle_price( 'min', true ) ), wc_price( $this->get_bundle_price( 'max', true ) ) );
					} else {
						$price = wc_price( $this->get_bundle_price( 'min', true ) );
					}

					if ( $this->get_bundle_regular_price( 'max', true ) !== $this->get_bundle_price( 'max', true ) || $this->get_bundle_regular_price( 'min', true ) !== $this->get_bundle_price( 'min', true ) ) {

						if ( $this->get_bundle_regular_price( 'min', true ) !== $this->get_bundle_regular_price( 'max', true ) ) {
							$regular_price = sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), wc_price( $this->get_bundle_regular_price( 'min', true ) ), wc_price( $this->get_bundle_regular_price( 'max', true ) ) );
						} else {
							$regular_price = wc_price( $this->get_bundle_regular_price( 'min', true ) );
						}

						/** Documented above. */
						$price = apply_filters( 'woocommerce_bundle_sale_price_html', $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix(), $this );

					} elseif ( 0.0 === $this->get_bundle_price( 'min', true ) && 0.0 === $this->get_bundle_price( 'max', true ) ) {

						$free_string = apply_filters( 'woocommerce_bundle_show_free_string', false, $this ) ? __( 'Free!', 'woocommerce' ) : $price;
						$price       = apply_filters( 'woocommerce_bundle_free_price_html', $free_string, $this );

					} else {
						/** Documented above. */
						$price = apply_filters( 'woocommerce_bundle_price_html', $price . $this->get_price_suffix(), $this );
					}
				}
			}

			/**
			 * 'woocommerce_get_bundle_price_html' filter.
			 *
			 * @param  string             $price_html
			 * @param  WC_Product_Bundle  $this
			 */
			$price = apply_filters( 'woocommerce_get_bundle_price_html', $price, $this );

			if ( $this->contains( 'subscriptions' ) ) {
				$price = $this->apply_subs_price_html( $price );
			}

			/** WC core filter. */
			return apply_filters( 'woocommerce_get_price_html', $price, $this );

		} else {

			return parent::get_price_html();
		}
	}

	/**
	 * Override on_sale status of product bundles. If a bundled item is on sale or has a discount applied, then the bundle appears as on sale.
	 *
	 * @return boolean
	 */
	public function is_on_sale() {

		$is_on_sale = false;

		if ( $this->contains( 'priced_individually' ) ) {
			$this->maybe_sync_bundle();
			$is_on_sale = parent::is_on_sale() || ( $this->contains( 'discounted_mandatory' ) && $this->get_bundle_regular_price( 'min' ) > 0 );
		} else {
			$is_on_sale = parent::is_on_sale();
		}

		/**
		 * 'woocommerce_product_is_on_sale' filter.
		 *
		 * @param  boolean            $is_on_sale
		 * @param  WC_Product_Bundle  $this
		 */
		return apply_filters( 'woocommerce_product_is_on_sale', $is_on_sale, $this );
	}

	/**
	 * A bundle is sold individually if it is marked as an "individually-sold" product, or if all bundled items are sold individually.
	 *
	 * @return boolean
	 */
	public function is_sold_individually() {
		$this->maybe_sync_bundle();
		return parent::is_sold_individually() || false === $this->contains( 'sold_in_multiples' );
	}

	/**
	 * "Sold Individually" option context.
	 * Returns 'product' or 'configuration'.
	 *
	 * @return string
	 */
	public function get_sold_individually_context() {
		return $this->sold_individually_context;
	}

	/**
	 * A bundle is purchasable if it contains (purchasable) bundled items.
	 *
	 * @return boolean
	 */
	public function is_purchasable() {

		$this->maybe_sync_bundle();

		$purchasable   = true;
		$bundled_items = $this->get_bundled_items();

		// Not purchasable while updating DB.
		if ( defined( 'WC_PB_UPDATING' ) ) {
			$purchasable = false;
		// Products must exist of course.
		} if ( ! $this->exists() ) {
			$purchasable = false;
		// When priced statically a price needs to be set.
		} elseif ( false === $this->contains( 'priced_individually' ) && '' === $this->get_price() ) {
			$purchasable = false;
		// Check the product is published.
		} elseif ( 'publish' !== WC_PB_Core_Compatibility::get_prop( $this, 'status' ) && ! current_user_can( 'edit_post', $this->id ) ) {
			$purchasable = false;
		// Check if the product contains anything.
		} elseif ( empty( $bundled_items ) ) {
			$purchasable = false;
		// Check if all non-optional contents are purchasable.
		} elseif ( $this->contains( 'non_purchasable' ) ) {
			$purchasable = false;
		// Only purchasable if "Mixed Checkout" is enabled for WCS.
		} elseif ( $this->contains( 'subscriptions' ) && class_exists( 'WC_Subscriptions_Admin' ) && 'yes' !== get_option( WC_Subscriptions_Admin::$option_prefix . '_multiple_purchase', 'no' ) ) {
			$purchasable = false;
		}

		/** WC core filter. */
		return apply_filters( 'woocommerce_is_purchasable', $purchasable, $this );
	}

	/**
	 * A bundle appears "on backorder" if the container is on backorder, or if a bundled item is on backorder (and requires notification).
	 *
	 * @return boolean
	 */
	public function is_on_backorder( $qty_in_cart = 0 ) {
		$this->maybe_sync_bundle();
		return parent::is_on_backorder() || $this->contains( 'on_backorder' );
	}

	/**
	 * A bundle on backorder requires notification if the container is defined like this, or a bundled item is on backorder and requires notification.
	 *
	 * @return boolean
	 */
	public function backorders_require_notification() {
		$this->maybe_sync_bundle();
		return parent::backorders_require_notification() || $this->contains( 'on_backorder' );
	}

	/**
	 * Availability of bundle based on bundle-level stock and bundled-items-level stock.
	 *
	 * @return array
	 */
	public function get_availability() {

		$availability = parent::get_availability();

		$this->maybe_sync_bundle();

		if ( parent::is_in_stock() && $this->contains( 'out_of_stock' ) ) {

			$availability[ 'availability' ] = __( 'Insufficient stock', 'ultimatewoo-pro' );
			$availability[ 'class' ]        = 'out-of-stock';

		} elseif ( parent::is_in_stock() && $this->contains( 'on_backorder' ) ) {

			$availability[ 'availability' ] = __( 'Available on backorder', 'woocommerce' );
			$availability[ 'class' ]        = 'available-on-backorder';
		}

		return apply_filters( 'woocommerce_get_bundle_availability', $availability, $this );
	}

	/**
	 * True if the product container is in stock.
	 *
	 * @return boolean
	 */
	public function is_parent_in_stock() {
		return parent::is_in_stock();
	}

	/**
	 * True if the product is in stock and all bundled items are in stock.
	 *
	 * @return boolean
	 */
	public function is_in_stock() {

		$is_in_stock = parent::is_in_stock();

		if ( $is_in_stock ) {
			$this->maybe_sync_bundle();
			if ( $this->contains( 'out_of_stock' ) ) {
				$is_in_stock = false;
			}
		}

		return $is_in_stock;
	}

	/**
	 * Returns whether or not the product is visible in the catalog.
	 *
	 * @return bool
	 */
	public function is_visible() {

		if ( ! $this->post ) {
			$visible = false;

		// Published/private.
		} elseif ( $this->post->post_status !== 'publish' && ! current_user_can( 'edit_post', $this->id ) ) {
			$visible = false;

		// Out of stock visibility.
		} elseif ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $this->is_parent_in_stock() ) {
			$visible = false;

		// Visibility setting.
		} elseif ( 'hidden' === $this->visibility ) {
			$visible = false;
		} elseif ( 'visible' === $this->visibility ) {
			$visible = true;

		// Visibility in loop.
		} elseif ( is_search() ) {
			$visible = 'search' === $this->visibility;
		} else {
			$visible = 'catalog' === $this->visibility;
		}

		return apply_filters( 'woocommerce_product_is_visible', $visible, $this->id );
	}

	/**
	 * Returns bundled item quantities.
	 *
	 * @since  5.0.0
	 *
	 * @return array
	 */
	public function get_bundled_item_quantities( $context = 'reference', $min_or_max = '' ) {

		$context    = in_array( $context, array( 'reference', 'optimal', 'worst', 'required' ) ) ? $context : 'reference';
		$min_or_max = in_array( $min_or_max, array( 'min', 'max', '' ) ) ? $min_or_max : '';

		if ( empty( $this->bundled_item_quantities[ 'reference' ][ 'min' ] ) ) {
			$this->maybe_sync_bundle();
		}

		return '' === $min_or_max ? $this->bundled_item_quantities[ $context ] : $this->bundled_item_quantities[ $context ][ $min_or_max ];
	}

	/**
	 * Returns bundled item data objects.
	 *
	 * @since  5.1.0
	 *
	 * @return array
	 */
	public function get_bundled_data_items( $context = 'view' ) {

		if ( ! is_array( $this->bundled_data_items ) ) {

			$cache_key   = WC_PB_Core_Compatibility::wc_cache_helper_get_cache_prefix( 'bundled_data_items' ) . $this->id;
			$cached_data = ! defined( 'WC_PB_DEBUG_OBJECT_CACHE' ) ? wp_cache_get( $cache_key, 'bundled_data_items' ) : false;

			if ( false !== $cached_data ) {
				$this->bundled_data_items = $cached_data;
			}

			if ( ! is_array( $this->bundled_data_items ) ) {

				$this->bundled_data_items = array();

				if ( $this->id ) {

					$args = array(
						'bundle_id' => $this->id,
						'return'    => 'objects',
						'order_by'  => array( 'menu_order' => 'ASC' )
					);

					$this->bundled_data_items = WC_PB_DB::query_bundled_items( $args );

					wp_cache_set( $cache_key, $this->bundled_data_items, 'bundled_data_items' );
				}
			}
		}

		return 'view' === $context ? apply_filters( 'woocommerce_bundled_data_items', $this->bundled_data_items, $this ) : $this->bundled_data_items;
	}

	/**
	 * Returns bundled item ids.
	 *
	 * @since  5.0.0
	 *
	 * @return array
	 */
	public function get_bundled_item_ids() {

		$bundled_item_ids = array();

		foreach ( $this->get_bundled_data_items() as $bundled_data_item ) {
			$bundled_item_ids[] = $bundled_data_item->get_id();
		}

		/**
		 * 'woocommerce_bundled_item_ids' filter.
		 *
		 * @param  array              $ids
		 * @param  WC_Product_Bundle  $this
		 */
		return apply_filters( 'woocommerce_bundled_item_ids', $bundled_item_ids, $this );
	}

	/**
	 * Gets all bundled items.
	 *
	 * @return array
	 */
	public function get_bundled_items() {

		$bundled_items      = array();
		$bundled_data_items = $this->get_bundled_data_items();

		foreach ( $this->get_bundled_data_items() as $bundled_data_item ) {

			$bundled_item = $this->get_bundled_item( $bundled_data_item );

			if ( $bundled_item && $bundled_item->exists() ) {
				$bundled_items[ $bundled_data_item->get_id() ] = $bundled_item;
			}
		}

		/**
		 * 'woocommerce_bundled_items' filter.
		 *
		 * @param  array              $bundled_items
		 * @param  WC_Product_Bundle  $this
		 */
		return apply_filters( 'woocommerce_bundled_items', $bundled_items, $this );
	}

	/**
	 * Checks if a specific bundled item exists.
	 *
	 * @param  $bundled_item_id
	 * @return boolean
	 */
	public function has_bundled_item( $bundled_item_id ) {

		$has_bundled_item = false;
		$bundled_item_ids = $this->get_bundled_item_ids();

		if ( in_array( $bundled_item_id, $bundled_item_ids ) ) {
			$has_bundled_item = true;
		}

		return $has_bundled_item;
	}

	/**
	 * Gets a specific bundled item.
	 *
	 * @param  WC_Bundled_Item_Data|int  $bundled_data_item
	 * @return WC_Bundled_Item
	 */
	public function get_bundled_item( $bundled_data_item ) {

		if ( $bundled_data_item instanceof WC_Bundled_Item_Data ) {
			$bundled_item_id = $bundled_data_item->get_id();
		} else {
			$bundled_item_id = $bundled_data_item = absint( $bundled_data_item );
		}

		$bundled_item = false;

		if ( $this->has_bundled_item( $bundled_item_id ) ) {

			$bundled_item = WC_PB_Helpers::cache_get( 'wc_bundled_item_' . $bundled_item_id . '_' . $this->id );

			if ( defined( 'WC_PB_DEBUG_RUNTIME_CACHE' ) || null === $bundled_item ) {
				$bundled_item = new WC_Bundled_Item( $bundled_data_item, $this );
				WC_PB_Helpers::cache_set( 'wc_bundled_item_' . $bundled_item_id . '_' . $this->id, $bundled_item );
			}
		}

		return $bundled_item;
	}

	/**
	 * Returns whether or not the bundle has any attributes set. Takes into account the attributes of all bundled products.
	 *
	 * @return boolean
	 */
	public function has_attributes() {

		// Check bundle for attributes.
		if ( sizeof( $this->get_attributes() ) > 0 ) {

			foreach ( $this->get_attributes() as $attribute ) {

				if ( isset( $attribute[ 'is_visible' ] ) && $attribute[ 'is_visible' ] ) {
					return true;
				}
			}
		}

		// Check all bundled products for attributes.
		$bundled_items = $this->get_bundled_items();

		if ( ! empty( $bundled_items ) ) {

			foreach ( $bundled_items as $bundled_item ) {

				/**
				 * 'woocommerce_bundle_show_bundled_product_attributes' filter.
				 *
				 * @param  boolean            $show_attributes
				 * @param  WC_Product_Bundle  $this
				 */
				$show_bundled_product_attributes = apply_filters( 'woocommerce_bundle_show_bundled_product_attributes', $bundled_item->is_visible(), $this, $bundled_item );

				if ( ! $show_bundled_product_attributes ) {
					continue;
				}

				$bundled_product = $bundled_item->product;

				if ( sizeof( $bundled_product->get_attributes() ) > 0 ) {

					foreach ( $bundled_product->get_attributes() as $attribute ) {

						if ( isset( $attribute[ 'is_visible' ] ) && $attribute[ 'is_visible' ] ) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Lists a table of attributes for the bundle page.
	 */
	public function list_attributes() {

		// show attributes attached to the bundle only
		wc_get_template( 'single-product/product-attributes.php', array(
			'product' => $this
		), '', '' );

		$bundled_items = $this->get_bundled_items();

		if ( ! empty( $bundled_items ) ) {

			foreach ( $bundled_items as $bundled_item ) {

				/** Documented in method 'has_attributes'. */
				$show_bundled_product_attributes = apply_filters( 'woocommerce_bundle_show_bundled_product_attributes', $bundled_item->is_visible(), $this, $bundled_item );

				if ( ! $show_bundled_product_attributes ) {
					continue;
				}

				$bundled_product = $bundled_item->product;

				if ( false === $bundled_item->is_shipped_individually() ) {
					$bundled_product->length = $bundled_product->width = $bundled_product->height = $bundled_product->weight = '';
				}

				if ( $bundled_product->has_attributes() ) {

					// Filter bundled item attributes based on active variation filters.
					add_filter( 'woocommerce_attribute',  array( $bundled_item, 'filter_bundled_item_attribute' ), 10, 3 );

					wc_get_template( 'single-product/bundled-item-attributes.php', array(
						'title'              => $bundled_item->get_title(),
						'product'            => $bundled_product,
						'attributes'         => '',
						'display_dimensions' => ''
					), false, WC_PB()->plugin_path() . '/templates/' );

					remove_filter( 'woocommerce_attribute',  array( $bundled_item, 'filter_bundled_item_attribute' ), 10, 3 );
				}
			}
		}
	}

	/**
	 * Get the add to url used mainly in loops.
	 *
	 * @return 	string
	 */
	public function add_to_cart_url() {

		$url = esc_url( $this->is_purchasable() && $this->is_in_stock() && ! $this->requires_input() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id ) );

		/** WC core filter. */
		return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
	}

	/**
	 * Get the add to cart button text.
	 *
	 * @return 	string
	 */
	public function add_to_cart_text() {

		$text = __( 'Read more', 'woocommerce' );

		if ( $this->is_purchasable() && $this->is_in_stock() ) {

			if ( $this->requires_input() ) {

				if ( $this->contains( 'hidden' ) ) {
					$text =  __( 'View contents', 'ultimatewoo-pro' );
				} else {
					$text =  __( 'Select options', 'woocommerce' );
				}

			} else {
				$text =  __( 'Add to cart', 'woocommerce' );
			}
		}

		/** WC core filter. */
		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}

	/**
	 * Get the add to cart button text for the single page.
	 *
	 * @return string
	 */
	public function single_add_to_cart_text() {

		$text = __( 'Add to cart', 'woocommerce' );

		if ( isset( $_GET[ 'update-bundle' ] ) ) {

			$updating_cart_key = wc_clean( $_GET[ 'update-bundle' ] );

			if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {
				$text = __( 'Update Cart', 'ultimatewoo-pro' );
			}
		}

		/** WC core filter. */
		return apply_filters( 'woocommerce_product_single_add_to_cart_text', $text, $this );
	}

	/**
	 * A bundle requires user input if: ( is nyp ) or ( has required addons ) or ( has items with variables ).
	 *
	 * @return boolean  true if it needs configuration before adding to cart
	 */
	public function requires_input() {

		$this->maybe_sync_bundle();

		$requires_input = false;

		if ( $this->is_nyp || WC_PB()->compatibility->has_required_addons( $this->id ) || $this->contains( 'options' ) ) {
			$requires_input = true;
		}

		/**
		 * 'woocommerce_bundle_requires_input' filter.
		 *
		 * @param  boolean            $requires_input
		 * @param  WC_Product_Bundle  $this
		 */
		return apply_filters( 'woocommerce_bundle_requires_input', $requires_input, $this );
	}

	/**
	 * Indicates whether the product configuration can be edited in the cart.
	 * Optionally pass a cart item array to check.
	 *
	 * @param  array   $cart_item
	 * @return boolean
	 */
	public function is_editable_in_cart( $cart_item = false ) {
		/**
		 * 'woocommerce_bundle_is_editable_in_cart' filter.
		 *
		 * @param  boolean            $is
		 * @param  WC_Product_Bundle  $this
		 * @param  array              $cart_item
		 */
		return apply_filters( 'woocommerce_bundle_is_editable_in_cart', method_exists( $this, 'supports' ) && $this->supports( 'edit_in_cart' ), $this, $cart_item );
	}

	/**
	 * Wrapper for get_permalink that adds bundle configuration data to the URL.
	 *
	 * @return string
	 */
	public function get_permalink() {

		$permalink     = get_permalink( $this->id );
		$config_data   = false;
		$fn_args_count = func_num_args();

		if ( 1 === $fn_args_count ) {

			$cart_item = func_get_arg( 0 );

			if ( is_array( $cart_item ) && isset( $cart_item[ 'stamp' ] ) && is_array( $cart_item[ 'stamp' ] ) ) {

				$config_data = $cart_item[ 'stamp' ];
				$args        = array();

				foreach ( $config_data as $item_id => $item_config_data ) {

					if ( isset( $item_config_data[ 'optional_selected' ] ) ) {
						if ( 'yes' === $item_config_data[ 'optional_selected' ] ) {
							$args[ 'bundle_selected_optional_' . $item_id ] = $item_config_data[ 'optional_selected' ];
						} else {
							continue;
						}
					}

					if ( isset( $item_config_data[ 'quantity' ] ) ) {
						$args[ 'bundle_quantity_' . $item_id ] = $item_config_data[ 'quantity' ];
					}

					if ( isset( $item_config_data[ 'variation_id' ] ) ) {
						$args[ 'bundle_variation_id_' . $item_id ] = $item_config_data[ 'variation_id' ];
					}

					if ( isset( $item_config_data[ 'attributes' ] ) && is_array( $item_config_data[ 'attributes' ] ) ) {
						foreach ( $item_config_data[ 'attributes' ] as $tax => $val ) {
							$args[ 'bundle_' . $tax . '_' . $item_id ] = sanitize_title( $val );
						}
					}
				}

				if ( $this->is_editable_in_cart( $cart_item ) ) {

					// Find the cart id we are updating.

					$cart_id = '';

					foreach ( WC()->cart->cart_contents as $item_key => $item_values ) {
						if ( isset( $item_values[ 'bundled_items' ] ) && $item_values[ 'stamp' ] === $cart_item[ 'stamp' ] ) {
							$cart_id = $item_key;
						}
					}

					if ( $cart_id ) {
						$args[ 'update-bundle' ] = $cart_id;
					}
				}

				$args = apply_filters( 'woocommerce_bundle_cart_permalink_args', $args, $cart_item, $this );

				if ( ! empty( $args ) ) {
					$permalink = esc_url( add_query_arg( $args, $permalink ) );
				}
			}
		}

		return $permalink;
	}

	/**
	 * Gets the attributes of all variable bundled items (legacy).
	 *
	 * @return array
	 */
	public function get_bundle_variation_attributes() {

		$this->maybe_sync_bundle();

		$bundled_items = $this->get_bundled_items();

		if ( empty( $bundled_items ) ) {
			return array();
		}

		$bundle_attributes = array();

		foreach ( $bundled_items as $bundled_item ) {
			$bundle_attributes[ $bundled_item->item_id ] = $bundled_item->get_product_variation_attributes();
		}

		return $bundle_attributes;
	}

	/**
	 * Gets default (overriden) selections for variable product attributes (legacy).
	 *
	 * @return array
	 */
	public function get_selected_bundle_variation_attributes() {

		$this->maybe_sync_bundle();

		$bundled_items = $this->get_bundled_items();

		if ( empty( $bundled_items ) ) {
			return array();
		}

		$seleted_bundle_attributes = array();

		foreach ( $bundled_items as $bundled_item ) {
			$seleted_bundle_attributes[ $bundled_item->item_id ] = $bundled_item->get_selected_product_variation_attributes();
		}

		return $seleted_bundle_attributes;
	}

	/**
	 * Gets all product variation data (legacy).
	 *
	 * @return array
	 */
	public function get_available_bundle_variations() {

		$this->maybe_sync_bundle();

		$bundled_items = $this->get_bundled_items();

		if ( empty( $bundled_items ) ) {
			return array();
		}

		$bundle_variations = array();

		foreach ( $bundled_items as $bundled_item ) {
			$bundle_variations[ $bundled_item->item_id ] = $bundled_item->get_product_variations();
		}

		return $bundle_variations;
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	public function get_base_price() {
		_deprecated_function( __METHOD__ . '()', '5.1.0', __CLASS__ . '::get_price()' );
		return $this->price;
	}
	public function get_base_regular_price() {
		_deprecated_function( __METHOD__ . '()', '5.1.0', __CLASS__ . '::get_regular_price()' );
		return $this->regular_price;
	}
	public function get_base_sale_price() {
		_deprecated_function( __METHOD__ . '()', '5.1.0', __CLASS__ . '::get_sale_price()' );
		return $this->sale_price;
	}
	public function is_priced_per_product() {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::contains()' );
		return $this->contains( 'priced_individually' );
	}
	public function is_shipped_per_product() {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::contains()' );
		return $this->contains( 'shipped_individually' );
	}
	public function all_items_in_stock() {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::contains()' );
		return false === $this->contains( 'out_of_stock' );
	}
	public function contains_sub() {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::contains()' );
		return $this->contains( 'subscriptions' );
	}
	public function contains_nyp() {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::contains()' );
		return $this->contains( 'nyp' );
	}
	public function contains_optional( $exclusively = false ) {
		_deprecated_function( __METHOD__ . '()', '5.0.0', __CLASS__ . '::contains()' );
		if ( $exclusively ) {
			return false === $this->contains( 'mandatory' ) && $this->contains( 'optional' );
		}
		return $this->contains( 'optional' );
	}
	public function has_variables() {
		_deprecated_function( __METHOD__ . '()', '4.11.7', __CLASS__ . '::requires_input()' );
		return $this->requires_input();
	}
	public function get_max_bundle_regular_price() {
		_deprecated_function( __METHOD__ . '()', '4.11.4', __CLASS__ . '::get_bundle_regular_price()' );
		return $this->get_bundle_regular_price( 'max', true );
	}
	public function get_min_bundle_regular_price() {
		_deprecated_function( __METHOD__ . '()', '4.11.4', __CLASS__ . '::get_bundle_regular_price()' );
		return $this->get_bundle_regular_price( 'min', true );
	}
	public function get_max_bundle_price() {
		_deprecated_function( __METHOD__ . '()', '4.11.4', __CLASS__ . '::get_bundle_price()' );
		return $this->get_bundle_price( 'max', true );
	}
	public function get_min_bundle_price() {
		_deprecated_function( __METHOD__ . '()', '4.11.4', __CLASS__ . '::get_bundle_price()' );
		return $this->get_bundle_price( 'min', true );
	}
	public function get_min_bundle_price_incl_tax() {
		_deprecated_function( __METHOD__ . '()', '4.11.4', __CLASS__ . '::get_bundle_price_including_tax()' );
		return $this->get_bundle_price_including_tax( 'min' );
	}
	public function get_min_bundle_price_excl_tax() {
		_deprecated_function( __METHOD__ . '()', '4.11.4', __CLASS__ . '::get_bundle_price_excluding_tax()' );
		return $this->get_bundle_price_excluding_tax( 'min' );
	}
}
