<?php
/**
 * WC_Product_Bundle class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    1.0.0
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
	 * Array of bundle-type extended product data fields used in CRUD and runtime operations.
	 * @var array
	 */
	private $extended_data = array(
		'layout'                    => 'default',
		'editable_in_cart'          => false,
		'sold_individually_context' => 'product',
		'min_raw_price'             => '',
		'min_raw_regular_price'     => '',
		'max_raw_price'             => '',
		'max_raw_regular_price'     => ''
	);

	/**
	 * Array of bundled item data objects.
	 * @var array
	 */
	private $bundled_data_items = null;

	/**
	 * Bundled item data objects that need deleting are stored here.
	 * @var array
	 */
	private $bundled_data_items_delete_queue = array();

	/**
	 * Indicates whether bundled data items have temporary IDs (saving needed).
	 * @var array
	 */
	private $bundled_data_items_save_pending = false;

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
	private $bundled_item_quantities = array();

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
	private $contains = array();

	/**
	 * True if the bundle is in sync with bundled items.
	 * @var boolean
	 */
	private $is_synced = false;

	/**
	 * Suppress range-style price html format.
	 * @var boolean
	 */
	private $force_price_html_from = false;

	/**
	 * Constructor.
	 *
	 * @param  mixed  $bundle
	 */
	public function __construct( $bundle ) {

		// Initialize private properties.
		$this->load_defaults();

		// Define/load type-specific data.
		$this->load_extended_data();

		// Load product data.
		parent::__construct( $bundle );
	}

	/**
	 * Load property and runtime cache defaults to trigger a re-sync.
	 *
	 * @since 5.2.0
	 */
	public function load_defaults() {

		$this->bundled_item_quantities = array(
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

		$this->contains = array(
			'priced_individually'               => null,
			'shipped_individually'              => null,
			'optional'                          => false,
			'mandatory'                         => false,
			'on_backorder'                      => false,
			'subscriptions'                     => false,
			'subscriptions_priced_individually' => false,
			'multiple_subscriptions'            => false,
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

		$this->is_synced             = false;
		$this->force_price_html_from = false;
		$this->bundle_price_data     = array();
		$this->bundle_price_cache    = array();
	}

	/**
	 * Define type-specific data.
	 *
	 * @since  5.2.0
	 */
	private function load_extended_data() {

		// Back-compat.
		$this->product_type = 'bundle';

		// Define type-specific fields and let WC use our data store to read the data.
		$this->data = array_merge( $this->data, $this->extended_data );
	}

	/**
	 * Get internal type.
	 *
	 * @since  5.1.0
	 *
	 * @return string
	 */
	public function get_type() {
		return 'bundle';
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

		$min_raw_price         = $this->get_price( 'sync' );
		$min_raw_regular_price = $this->get_regular_price( 'sync' );
		$max_raw_price         = $this->get_price( 'sync' );
		$max_raw_regular_price = $this->get_regular_price( 'sync' );

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

		// Scan bundled items and sync bundle properties.
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
					$bundled_item->add_price_filters();
					if ( $bundled_item->product->get_variation_price( 'min' ) !== $bundled_item->product->get_variation_price( 'max' ) || $bundled_item->product->get_meta( '_min_variation_period', true ) !== $bundled_item->product->get_meta( '_max_variation_period', true ) || $bundled_item->product->get_meta( '_min_variation_period_interval', true ) !== $bundled_item->product->get_meta( '_max_variation_period_interval', true ) ) {
						$this->force_price_html_from = true;
					}
					$bundled_item->remove_price_filters();
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
		if ( $is_front_end && ! $this->requires_input() ) {
			$this->supports[] = 'ajax_add_to_cart';
		}

		/**
		 * 'woocommerce_bundles_synced_bundle' action.
		 *
		 * @param  WC_Product_Bundle  $this
		 */
		do_action( 'woocommerce_bundles_synced_bundle', $this );

		/*
		 * Set min/max raw (regular) prices.
		 */

		$raw_price_meta_changed = false;

		if ( $this->get_min_raw_price( 'sync' ) !== $min_raw_price || $this->get_min_raw_regular_price( 'sync' ) !== $min_raw_regular_price || $this->get_max_raw_price( 'sync' ) !== $max_raw_price || $this->get_max_raw_regular_price( 'sync' ) !== $max_raw_regular_price ) {
			$raw_price_meta_changed = true;
		}

		$this->set_min_raw_price( $min_raw_price );
		$this->set_min_raw_regular_price( $min_raw_regular_price );
		$this->set_max_raw_price( $max_raw_price );
		$this->set_max_raw_regular_price( $max_raw_regular_price );

		/**
		 * 'woocommerce_bundles_update_price_meta' filter.
		 *
		 * Use this to prevent bundle min/max raw price meta from being updated.
		 *
		 * @param  boolean            $update
		 * @param  WC_Product_Bundle  $this
		 */
		$update_raw_price_meta = apply_filters( 'woocommerce_bundles_update_price_meta', $raw_price_meta_changed, $this ) && ! defined( 'WC_PB_UPDATING' );

		if ( $update_raw_price_meta ) {
			$this->data_store->update_raw_prices( $this );
		}
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

			$base_price_incl_tax = wc_get_price_including_tax( $this, array( 'qty' => 1, 'price' => 1000 ) );
			$base_price_excl_tax = wc_get_price_excluding_tax( $this, array( 'qty' => 1, 'price' => 1000 ) );

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

				$price_incl_tax = wc_get_price_including_tax( $bundled_item->product, array( 'qty' => 1, 'price' => 1000 ) );
				$price_excl_tax = wc_get_price_excluding_tax( $bundled_item->product, array( 'qty' => 1, 'price' => 1000 ) );

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

				$raw_price_fn_name = 'get_' . $min_or_max . '_raw_price';

				if ( '' === $this->$raw_price_fn_name() || INF === $this->$raw_price_fn_name() ) {
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

			$price = $this->get_price();

			if ( $display ) {
				$price = WC_PB_Product_Prices::get_product_display_price( $this, $price );
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

				$raw_price_fn_name = 'get_' . $min_or_max . '_raw_regular_price';

				if ( '' === $this->$raw_price_fn_name() || INF === $this->$raw_price_fn_name() ) {
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

			$price = $this->get_regular_price();

			if ( $display ) {
				$price = WC_PB_Product_Prices::get_product_display_price( $this, $price );
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

				$raw_price_fn_name = 'get_' . $min_or_max . '_raw_price';

				if ( '' === $this->$raw_price_fn_name() || INF === $this->$raw_price_fn_name() ) {
					$price = '';
				} else {

					$price         = wc_get_price_including_tax( $this, array( 'qty' => $qty, 'price' => $this->get_price() ) );
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
			$price = wc_get_price_including_tax( $this, array( 'qty' => $qty, 'price' => $this->get_price() ) );
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

				$raw_price_fn_name = 'get_' . $min_or_max . '_raw_price';

				if ( '' === $this->$raw_price_fn_name() || INF === $this->$raw_price_fn_name() ) {
					$price = '';
				} else {

					$price         = wc_get_price_excluding_tax( $this, array( 'qty' => $qty, 'price' => $this->get_price() ) );
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
			$price = wc_get_price_excluding_tax( $this, array( 'qty' => $qty, 'price' => $this->get_price() ) );
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

			if ( ( $suffix = get_option( 'woocommerce_price_display_suffix' ) ) && wc_tax_enabled() ) {

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
						$product = $bundled_item->min_price_product;
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
						$bundled_item->add_price_filters();
						if ( $bundled_product->get_variation_price( 'min' ) !== $bundled_product->get_variation_price( 'max' ) || $bundled_product->get_meta( '_min_variation_period', true ) !== $bundled_product->get_meta( '_max_variation_period', true ) || $bundled_product->get_meta( '_min_variation_period_interval', true ) !== $bundled_product->get_meta( '_max_variation_period_interval', true ) ) {
							if ( $bundled_item->is_priced_individually() ) {
								$subs_details[ $sub_string ][ 'is_range' ] = true;
							}
						}
						$bundled_item->remove_price_filters();
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
								$sub_price_html = sprintf( _x( '%1$s%2$s', 'Price range: from', 'ultimatewoo-pro' ), $from_string, wc_format_sale_price( $sub_regular_price_html, $sub_price_html ) );
							} else {
								$sub_price_html = wc_format_sale_price( $sub_regular_price_html, $sub_price_html );
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
							$price = sprintf( _x( '%1$s%2$s', 'Price range: from', 'ultimatewoo-pro' ), wc_get_price_html_from_text(), wc_format_sale_price( $regular_price, $price ) . $this->get_price_suffix() );
						} else {
							$price = wc_format_sale_price( $regular_price, $price ) . $this->get_price_suffix();
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
							$price = sprintf( _x( '%1$s%2$s', 'Price range: from', 'ultimatewoo-pro' ), wc_get_price_html_from_text(), $price . $this->get_price_suffix() );
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

					$is_range = false;

					if ( $this->get_bundle_price( 'min', true ) !== $this->get_bundle_price( 'max', true ) ) {
						$price    = wc_format_price_range( $this->get_bundle_price( 'min', true ), $this->get_bundle_price( 'max', true ) );
						$is_range = true;
					} else {
						$price = wc_price( $this->get_bundle_price( 'min', true ) );
					}

					if ( $this->get_bundle_regular_price( 'max', true ) !== $this->get_bundle_price( 'max', true ) || $this->get_bundle_regular_price( 'min', true ) !== $this->get_bundle_price( 'min', true ) ) {

						if ( $this->get_bundle_regular_price( 'min', true ) !== $this->get_bundle_regular_price( 'max', true ) ) {
							$regular_price = wc_format_price_range( $this->get_bundle_regular_price( 'min', true ), $this->get_bundle_regular_price( 'max', true ) );
						} else {
							$regular_price = wc_price( $this->get_bundle_regular_price( 'min', true ) );
						}

						/** Documented above. */
						$price = apply_filters( 'woocommerce_bundle_sale_price_html', wc_format_sale_price( $regular_price, $price ) . ( $is_range ? '' : $this->get_price_suffix() ), $this );

					} elseif ( 0.0 === $this->get_bundle_price( 'min', true ) && 0.0 === $this->get_bundle_price( 'max', true ) ) {

						$free_string = apply_filters( 'woocommerce_bundle_show_free_string', false, $this ) ? __( 'Free!', 'woocommerce' ) : $price;
						$price       = apply_filters( 'woocommerce_bundle_free_price_html', $free_string, $this );

					} else {
						/** Documented above. */
						$price = apply_filters( 'woocommerce_bundle_price_html', $price . ( $is_range ? '' : $this->get_price_suffix() ), $this );
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
	 * Get the add to url used mainly in loops.
	 *
	 * @return 	string
	 */
	public function add_to_cart_url() {

		$url = esc_url( $this->is_purchasable() && $this->is_in_stock() && ! $this->requires_input() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->get_id() ) ) : get_permalink( $this->get_id() ) );

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
	 * Wrapper for get_permalink that adds bundle configuration data to the URL.
	 *
	 * @return string
	 */
	public function get_permalink() {

		$permalink     = get_permalink( $this->get_id() );
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

	/*
	|--------------------------------------------------------------------------
	| CRUD Getters
	|--------------------------------------------------------------------------
	|
	| Methods for getting data from the product object.
	*/

	/**
	 * Returns the base active price of the bundle.
	 *
	 * @since  5.2.0
	 *
	 * @param  string $context
	 * @return mixed
	 */
	public function get_price( $context = 'view' ) {
		$value = $this->get_prop( 'price', $context );
		return in_array( $context, array( 'view', 'sync' ) ) && $this->contains( 'priced_individually' ) ? (double) $value : $value;
	}

	/**
	 * Returns the base regular price of the bundle.
	 *
	 * @since  5.2.0
	 *
	 * @param  string $context
	 * @return mixed
	 */
	public function get_regular_price( $context = 'view' ) {
		$value = $this->get_prop( 'regular_price', $context );
		return in_array( $context, array( 'view', 'sync' ) ) && $this->contains( 'priced_individually' ) ? (double) $value : $value;
	}

	/**
	 * Returns the base sale price of the bundle.
	 *
	 * @since  5.2.0
	 *
	 * @param  string  $context
	 * @return mixed
	 */
	public function get_sale_price( $context = 'view' ) {
		$value = $this->get_prop( 'sale_price', $context );
		return in_array( $context, array( 'view', 'sync' ) ) && $this->contains( 'priced_individually' ) && '' !== $value ? (double) $value : $value;
	}

	/**
	 * Layout getter.
	 *
	 * @since  5.0.0
	 *
	 * @param  string  $context
	 * @return string
	 */
	public function get_layout( $context = 'any' ) {
		return $this->get_prop( 'layout', $context );
	}

	/**
	 * Editable-in-cart getter.
	 *
	 * @since  5.2.0
	 *
	 * @param  string  $context
	 * @return boolean
	 */
	public function get_editable_in_cart( $context = 'any' ) {
		return $this->get_prop( 'editable_in_cart', $context );
	}

	/**
	 * "Sold Individually" option context.
	 * Returns 'product' or 'configuration'.
	 *
	 * @since  5.0.0
	 *
	 * @param  string  $context
	 * @return string
	 */
	public function get_sold_individually_context( $context = 'any' ) {
		return $this->get_prop( 'sold_individually_context', $context );
	}

	/**
	 * Minimum raw bundle price getter.
	 *
	 * @since  5.2.0
	 *
	 * @param  string  $context
	 * @return string
	 */
	public function get_min_raw_price( $context = 'view' ) {
		$this->maybe_sync_bundle();
		$value = $this->get_prop( 'min_raw_price', $context );
		return in_array( $context, array( 'view', 'sync' ) ) && $this->contains( 'priced_individually' ) && '' !== $value ? (double) $value : $value;
	}

	/**
	 * Minimum raw regular bundle price getter.
	 *
	 * @since  5.2.0
	 *
	 * @param  string  $context
	 * @return string
	 */
	public function get_min_raw_regular_price( $context = 'view' ) {
		$this->maybe_sync_bundle();
		$value = $this->get_prop( 'min_raw_regular_price', $context );
		return in_array( $context, array( 'view', 'sync' ) ) && $this->contains( 'priced_individually' ) && '' !== $value ? (double) $value : $value;
	}

	/**
	 * Maximum raw bundle price getter.
	 * INF is 9999999999.0 in 'edit' (DB) context.
	 *
	 * @since  5.2.0
	 *
	 * @param  string  $context
	 * @return string
	 */
	public function get_max_raw_price( $context = 'view' ) {
		$this->maybe_sync_bundle();
		$value = $this->get_prop( 'max_raw_price', $context );
		$value = 'edit' !== $context && $this->contains( 'priced_individually' ) && '' !== $value && INF !== $value ? (double) $value : $value;
		$value = 'edit' === $context && INF === $value ? 9999999999.0 : $value;
		return $value;
	}

	/**
	 * Maximum raw regular bundle price getter.
	 * INF is 9999999999.0 in 'edit' (DB) context.
	 *
	 * @since  5.2.0
	 *
	 * @param  string  $context
	 * @return string
	 */
	public function get_max_raw_regular_price( $context = 'view' ) {
		$this->maybe_sync_bundle();
		$value = $this->get_prop( 'max_raw_regular_price', $context );
		$value = 'edit' !== $context && $this->contains( 'priced_individually' ) && '' !== $value && INF !== $value ? (double) $value : $value;
		$value = 'edit' === $context && INF === $value ? 9999999999.0 : $value;
		return $value;
	}

	/**
	 * Returns bundled item data objects.
	 *
	 * @since  5.1.0
	 *
	 * @param  string  $context
	 * @return array
	 */
	public function get_bundled_data_items( $context = 'view' ) {

		if ( ! is_array( $this->bundled_data_items ) ) {

			$cache_key   = WC_PB_Core_Compatibility::wc_cache_helper_get_cache_prefix( 'bundled_data_items' ) . $this->get_id();
			$cached_data = ! defined( 'WC_PB_DEBUG_OBJECT_CACHE' ) ? wp_cache_get( $cache_key, 'bundled_data_items' ) : false;

			if ( false !== $cached_data ) {
				$this->bundled_data_items = $cached_data;
			}

			if ( ! is_array( $this->bundled_data_items ) ) {

				$this->bundled_data_items = array();

				if ( $id = $this->get_id() ) {

					$args = array(
						'bundle_id' => $id,
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
	 * @param  string  $context
	 * @return array
	 */
	public function get_bundled_item_ids( $context = 'view' ) {

		$bundled_item_ids = array();

		foreach ( $this->get_bundled_data_items( $context ) as $bundled_data_item ) {
			$bundled_item_ids[] = $bundled_data_item->get_id();
		}

		/**
		 * 'woocommerce_bundled_item_ids' filter.
		 *
		 * @param  array              $ids
		 * @param  WC_Product_Bundle  $this
		 */
		return 'view' === $context ? apply_filters( 'woocommerce_bundled_item_ids', $bundled_item_ids, $this ) : $bundled_item_ids;
	}

	/**
	 * Gets all bundled items.
	 *
	 * @param  string  $context
	 * @return array
	 */
	public function get_bundled_items( $context = 'view' ) {

		$bundled_items      = array();
		$bundled_data_items = $this->get_bundled_data_items( $context );

		foreach ( $bundled_data_items as $bundled_data_item ) {

			$bundled_item = $this->get_bundled_item( $bundled_data_item, $context );

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
		return 'view' === $context ? apply_filters( 'woocommerce_bundled_items', $bundled_items, $this ) : $bundled_items;
	}

	/**
	 * Checks if a specific bundled item exists.
	 *
	 * @param  int     $bundled_item_id
	 * @param  string  $context
	 * @return boolean
	 */
	public function has_bundled_item( $bundled_item_id, $context = 'view' ) {

		$has_bundled_item = false;
		$bundled_item_ids = $this->get_bundled_item_ids( $context );

		if ( in_array( $bundled_item_id, $bundled_item_ids ) ) {
			$has_bundled_item = true;
		}

		return $has_bundled_item;
	}

	/**
	 * Gets a specific bundled item.
	 *
	 * @param  WC_Bundled_Item_Data|int  $bundled_data_item
	 * @param  string                    $context
	 * @return WC_Bundled_Item
	 */
	public function get_bundled_item( $bundled_data_item, $context = 'view' ) {

		if ( $bundled_data_item instanceof WC_Bundled_Item_Data ) {
			$bundled_item_id = $bundled_data_item->get_id();
		} else {
			$bundled_item_id = $bundled_data_item = absint( $bundled_data_item );
		}

		$bundled_item = false;

		if ( $this->has_bundled_item( $bundled_item_id, $context ) ) {

			$bundled_item = WC_PB_Helpers::cache_get( 'wc_bundled_item_' . $bundled_item_id . '_' . $this->get_id() );

			if ( $this->bundled_data_items_save_pending || defined( 'WC_PB_DEBUG_RUNTIME_CACHE' ) || null === $bundled_item ) {
				$bundled_item = new WC_Bundled_Item( $bundled_data_item, $this );
				WC_PB_Helpers::cache_set( 'wc_bundled_item_' . $bundled_item_id . '_' . $this->get_id(), $bundled_item );
			}
		}

		return $bundled_item;
	}

	/*
	|--------------------------------------------------------------------------
	| CRUD Setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting product data. These should not update anything in the
	| database itself and should only change what is stored in the class
	| object.
	*/

	/**
	 * Layout setter.
	 *
	 * @since  5.2.0
	 *
	 * @param  string  $layout
	 */
	public function set_layout( $layout ) {
		$layout = array_key_exists( $layout, self::get_supported_layouts() ) ? $layout : 'default';
		$this->set_prop( 'layout', $layout );
	}

	/**
	 * Edtiable-in-cart setter.
	 *
	 * @since  5.2.0
	 *
	 * @param  string  $editable_in_cart
	 */
	public function set_editable_in_cart( $editable_in_cart ) {

		$editable_in_cart = wc_string_to_bool( $editable_in_cart );
		$this->set_prop( 'editable_in_cart', $editable_in_cart );

		if ( $editable_in_cart ) {
			if ( ! in_array( 'edit_in_cart', $this->supports ) ) {
				$this->supports[] = 'edit_in_cart';
			}
		} else {
			foreach ( $this->supports as $key => $value ) {
				if ( 'edit_in_cart' === $value ) {
					unset( $this->supports[ $key ] );
				}
			}
		}
	}

	/**
	 * Sold-individually context setter.
	 *
	 * @since  5.2.0
	 *
	 * @param  string  $context
	 */
	public function set_sold_individually_context( $context ) {
		$context = in_array( $context, array( 'product', 'configuration' ) ) ? $context : 'product';
		$this->set_prop( 'sold_individually_context', $context );
	}

	/**
	 * Minimum raw bundle price setter.
	 *
	 * @since  5.2.0
	 *
	 * @param  mixed  $value
	 */
	public function set_min_raw_price( $value ) {
		$value = wc_format_decimal( $value );
		$this->set_prop( 'min_raw_price', $value );
	}

	/**
	 * Minimum raw regular bundle price setter.
	 *
	 * @since  5.2.0
	 *
	 * @param  mixed  $value
	 */
	public function set_min_raw_regular_price( $value ) {
		$value = wc_format_decimal( $value );
		$this->set_prop( 'min_raw_regular_price', $value );
	}

	/**
	 * Maximum raw bundle price setter.
	 * Convert 9999999999.0 to INF.
	 *
	 * @since  5.2.0
	 *
	 * @param  mixed  $value
	 */
	public function set_max_raw_price( $value ) {
		$value = INF !== $value ? wc_format_decimal( $value ) : INF;
		$value = 9999999999.0 === (double) $value ? INF : $value;
		$this->set_prop( 'max_raw_price', $value );
	}

	/**
	 * Maximum raw regular bundle price setter.
	 * Convert 9999999999.0 to INF.
	 *
	 * @since  5.2.0
	 *
	 * @param  mixed  $value
	 */
	public function set_max_raw_regular_price( $value ) {
		$value = INF !== $value ? wc_format_decimal( $value ) : INF;
		$value = 9999999999.0 === (double) $value ? INF : $value;
		$this->set_prop( 'max_raw_regular_price', $value );
	}

	/**
	 * Sets bundled item data objects.
	 * Expects each data element in array format - @see 'WC_Bundled_Item_Data::get_data()'.
	 * Until 'save_items' is called, all items get a temporary index-based ID (unit-testing only!).
	 *
	 * @since  5.2.0
	 *
	 * @param  array  $data
	 */
	public function set_bundled_data_items( $data ) {

		if ( is_array( $data ) ) {

			$existing_item_ids = array();
			$update_item_ids   = array();

			$bundled_data_items = $this->get_bundled_data_items( 'edit' );

			// Get real IDs.
			if ( ! empty( $bundled_data_items ) ) {
				if ( $this->bundled_data_items_save_pending ) {
					foreach ( $this->bundled_data_items as $bundled_data_item_key => $bundled_data_item ) {
						$existing_item_ids[] = $bundled_data_item->get_meta( 'real_id' );
					}
				} else {
					foreach ( $this->bundled_data_items as $bundled_data_item_key => $bundled_data_item ) {
						$existing_item_ids[] = $bundled_data_item->get_id();
						$bundled_data_item->update_meta( 'real_id', $bundled_data_item->get_id() );
					}
				}
			}

			// Find existing IDs to update.
			if ( ! empty( $data ) ) {
				foreach ( $data as $item_key => $item_data ) {
					// Ignore items without a valid bundled product ID.
					if ( empty( $item_data[ 'product_id' ] ) ) {
						unset( $data[ $item_key ] );
					// If an item with the same ID exists, modify it.
					} elseif ( isset( $item_data[ 'bundled_item_id' ] ) && $item_data[ 'bundled_item_id' ] > 0 && in_array( $item_data[ 'bundled_item_id' ], $existing_item_ids ) ) {
						$update_item_ids[] = $item_data[ 'bundled_item_id' ];
					// Otherwise, add a new one that will be created after saving.
					} else {
						$data[ $item_key ][ 'bundled_item_id' ] = 0;
					}
				}
			}

			// Find existing IDs to remove.
			$remove_item_ids = array_diff( $existing_item_ids, $update_item_ids );

			// Remove items and delete them later.
			if ( ! empty( $this->bundled_data_items ) ) {
				foreach ( $this->bundled_data_items as $bundled_data_item_key => $bundled_data_item ) {

					$real_item_id = $this->bundled_data_items_save_pending ? $bundled_data_item->get_meta( 'real_id' ) : $bundled_data_item->get_id();

					if ( in_array( $real_item_id, $remove_item_ids ) ) {

						unset( $this->bundled_data_items[ $bundled_data_item_key ] );
						// Put item in the delete queue if saved in the DB.
						if ( $real_item_id > 0 ) {
							// Put back real ID.
							$bundled_data_item->set_id( $real_item_id );
							$this->bundled_data_items_delete_queue[] = $bundled_data_item;
						}
					}
				}
			}

			// Modify/add items.
			if ( ! empty( $data ) ) {
				foreach ( $data as $item_data ) {

					$item_data[ 'bundle_id' ] = $this->get_id();

					// Modify existing item.
					if ( in_array( $item_data[ 'bundled_item_id' ], $update_item_ids ) ) {

						foreach ( $this->bundled_data_items as $bundled_data_item_key => $bundled_data_item ) {

							$real_item_id = $this->bundled_data_items_save_pending ? $bundled_data_item->get_meta( 'real_id' ) : $bundled_data_item->get_id();

							if ( $item_data[ 'bundled_item_id' ] === $real_item_id ) {
								$bundled_data_item->set_all( $item_data );
							}
						}

					// Add new item.
					} else {
						$new_item = new WC_Bundled_Item_Data( $item_data );
						$new_item->update_meta( 'real_id', 0 );
						$this->bundled_data_items[] = $new_item;
					}
				}
			}

			// Modify all item IDs to temp values until saved.
			$temp_id = 0;
			if ( ! empty( $this->bundled_data_items ) ) {
				foreach ( $this->bundled_data_items as $bundled_data_item_key => $bundled_data_item ) {
					$temp_id++;
					$bundled_data_item->set_id( $temp_id );
				}
			}

			$this->bundled_data_items_save_pending = true;
			$this->load_defaults();
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Conditionals
	|--------------------------------------------------------------------------
	*/

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
	 * A bundle is sold individually if it is marked as an "individually-sold" product, or if all bundled items are sold individually.
	 *
	 * @return boolean
	 */
	public function is_sold_individually() {
		$this->maybe_sync_bundle();
		return parent::is_sold_individually() || false === $this->contains( 'sold_in_multiples' );
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
		} elseif ( 'publish' !== $this->get_status() && ! current_user_can( 'edit_post', $this->get_id() ) ) {
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
	 * Override on_sale status of product bundles. If a bundled item is on sale or has a discount applied, then the bundle appears as on sale.
	 *
	 * @param  string  $context
	 * @return boolean
	 */
	public function is_on_sale( $context = 'view' ) {

		$is_on_sale = false;

		if ( 'update-price' !== $context && $this->contains( 'priced_individually' ) ) {
			$this->maybe_sync_bundle();
			$is_on_sale = parent::is_on_sale( $context ) || ( $this->contains( 'discounted_mandatory' ) && $this->get_min_raw_regular_price( $context ) > 0 );
		} else {
			$is_on_sale = parent::is_on_sale( $context );
		}

		/**
		 * 'woocommerce_product_is_on_sale' filter.
		 *
		 * @param  boolean            $is_on_sale
		 * @param  WC_Product_Bundle  $this
		 */
		return 'view' === $context ? apply_filters( 'woocommerce_product_is_on_sale', $is_on_sale, $this ) : $is_on_sale;
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
	 * @return boolean
	 */
	public function is_visible() {
		$visible = 'visible' === $this->get_catalog_visibility() || ( is_search() && 'search' === $this->get_catalog_visibility() ) || ( ! is_search() && 'catalog' === $this->get_catalog_visibility() );

		if ( 'publish' !== $this->get_status() && ! current_user_can( 'edit_post', $this->get_id() ) ) {
			$visible = false;
		}

		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $this->is_parent_in_stock() ) {
			$visible = false;
		}

		return apply_filters( 'woocommerce_product_is_visible', $visible, $this->get_id() );
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
	 * Bundle is a NYP product.
	 *
	 * @return boolean
	 */
	public function is_nyp() {

		if ( ! isset( $this->is_nyp ) ) {
			$this->is_nyp = WC_PB()->compatibility->is_nyp( $this );
		}

		return $this->is_nyp;
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
	 * A bundle on backorder requires notification if the container is defined like this, or a bundled item is on backorder and requires notification.
	 *
	 * @return boolean
	 */
	public function backorders_require_notification() {
		$this->maybe_sync_bundle();
		return parent::backorders_require_notification() || $this->contains( 'on_backorder' );
	}

	/**
	 * Returns whether or not the bundle has any attributes set. Takes into account the attributes of all bundled products.
	 *
	 * @return boolean
	 */
	public function has_attributes() {

		$has_attributes = false;

		// Check bundle for attributes.
		if ( parent::has_attributes() ) {

			$has_attributes = true;

		// Check all bundled products for attributes.
		} else {

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

					if ( $bundled_product->has_attributes() ) {
						$has_attributes = true;
						break;
					}
				}
			}
		}

		return $has_attributes;
	}

	/**
	 * A bundle requires user input if: ( is nyp ) or ( has required addons ) or ( has items with variables ).
	 *
	 * @return boolean  true if it needs configuration before adding to cart
	 */
	public function requires_input() {

		$this->maybe_sync_bundle();

		$requires_input = false;

		if ( $this->is_nyp || WC_PB()->compatibility->has_required_addons( $this->get_id() ) || $this->contains( 'options' ) ) {
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

	/*
	|--------------------------------------------------------------------------
	| Other CRUD Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Alias for 'set_props'.
	 *
	 * @since 5.2.0
	 */
	public function set( $properties ) {
		return $this->set_props( $properties );
	}

	/**
	 * Override 'save' to handle bundled items saving.
	 *
	 * @since 5.2.0
	 */
	public function save() {
		parent::save();
		$this->save_items();
		return $this->get_id();
	}

	/**
	 * Saves bundled data items.
	 *
	 * @since 5.2.0
	 */
	public function save_items() {

		if ( $this->bundled_data_items_save_pending ) {

			foreach ( $this->bundled_data_items_delete_queue as $item ) {
				$item->delete();
			}

			$bundled_data_items = $this->get_bundled_data_items();

			if ( ! empty( $bundled_data_items ) ) {
				foreach ( $bundled_data_items as $item ) {

					// Update.
					if ( $real_id = $item->get_meta( 'real_id' ) ) {
						$item->set_id( $real_id );
					// Create.
					} else {
						$item->set_id( 0 );
					}

					// Update bundle ID.
					$item->set_bundle_id( $this->get_id() );

					$item->delete_meta( 'real_id' );
					$item->save();
					$item->update_meta( 'real_id', $item->get_id() );

					// Flush stock cache.
					WC_PB_DB::flush_stock_cache( $item->get_id() );

					// Delete runtime cache.
					WC_PB_Helpers::cache_delete( 'wc_bundled_item_' . $item->get_id() . '_' . $this->get_id() );
				}

			} else {
				$this->set_status( 'draft' );
				parent::save();
			}

			$this->bundled_data_items_save_pending = false;

			$this->load_defaults();
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Static methods.
	|--------------------------------------------------------------------------
	*/

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

	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	public function get_bundle_variation_attributes() {
		_deprecated_function( __METHOD__ . '()', '5.2.0', 'WC_Bundled_Item::get_product_variation_attributes()' );

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
	public function get_selected_bundle_variation_attributes() {
		_deprecated_function( __METHOD__ . '()', '5.2.0', 'WC_Bundled_Item::get_selected_product_variation_attributes()' );

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
	public function get_available_bundle_variations() {
		_deprecated_function( __METHOD__ . '()', '5.2.0', 'WC_Bundled_Item::get_product_variations()' );

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
	public function get_base_price() {
		_deprecated_function( __METHOD__ . '()', '5.1.0', __CLASS__ . '::get_price()' );
		return $this->get_price( 'edit' );
	}
	public function get_base_regular_price() {
		_deprecated_function( __METHOD__ . '()', '5.1.0', __CLASS__ . '::get_regular_price()' );
		return $this->get_regular_price( 'edit' );
	}
	public function get_base_sale_price() {
		_deprecated_function( __METHOD__ . '()', '5.1.0', __CLASS__ . '::get_sale_price()' );
		return $this->get_sale_price( 'edit' );
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
