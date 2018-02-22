<?php
/**
 * WC_CP_Query class
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
 * WP_Query wrapper for fetching component option ids.
 *
 * Supports two query types: 1) By product ID and 2) by product category ID.
 * Note that during composite product initialization, custom queries are used to fetch an unpaginated array of product ids -- @see 'WC_Composite_Product::sync_composite'.
 * This is necessary to sync prices (when 'woocommerce_composite_hide_price_html' is true) and initialize template parameters.
 * When a component is rendered, sorting / filtering / pagination are handled via 'WC_CP_Component_View::get_options()' which uses the results of the initialization query.
 * Therefore, all rendering queries are done by fetching product IDs directly.
 *
 * You can add your own custom query types by hooking into 'woocommerce_composite_component_query_types' to add the query key/description.
 * Then, implement the query itself by hooking into 'woocommerce_composite_component_options_query_args'.
 *
 * You can add you own custom sorting function by hooking into 'woocommerce_composite_component_orderby' - or you can extend/modfify the behaviour of the 'default' orderby case.
 * To implement it, hook into 'woocommerce_composite_component_options_query_args'.
 *
 * @class    WC_CP_Query
 * @version  3.9.0
 */
class WC_CP_Query {

	private $results;
	private $storage;

	/**
	 * Constructor.
	 *
	 * @param  array  $component_data
	 * @param  array  $query_args
	 */
	public function __construct( $component_data, $query_args = array() ) {

		/**
		 * Action 'woocommerce_composite_component_query_start'.
		 *
		 * @param  array  $component_data
		 * @param  array  $query_args
		 */
		do_action( 'woocommerce_composite_component_query_start', $component_data, $query_args );

		// Activate 'attribute_filter' filter type.
		add_filter( 'woocommerce_composite_component_options_query_args', array( $this, 'attribute_filters' ), 10, 3 );

		// Activate category type query.
		add_filter( 'woocommerce_composite_component_options_query_args', array( $this, 'category_ids_query' ), 10, 3 );

		// Modify 'default' ordering behaviour to use priority numbers saved in $component_data[ 'sort_priorities' ].
		add_filter( 'woocommerce_composite_component_options_query_args', array( $this, 'order_by_priority' ), 10, 3 );

		$this->query( $component_data, $query_args );

		// Remove customizations.
		remove_filter( 'woocommerce_composite_component_options_query_args', array( $this, 'attribute_filters' ), 10, 3 );
		remove_filter( 'woocommerce_composite_component_options_query_args', array( $this, 'category_ids_query' ), 10, 3 );
		remove_filter( 'woocommerce_composite_component_options_query_args', array( $this, 'order_by_priority' ), 10, 3 );

		/**
		 * Action 'woocommerce_composite_component_query_end'.
		 *
		 * @param  array  $component_data
		 * @param  array  $query_args
		 */
		do_action( 'woocommerce_composite_component_query_end', $component_data, $query_args );
	}

	/**
	 * Get queried component option IDs.
	 *
	 * @return array
	 */
	public function get_component_options() {
		return ! empty( $this->results[ 'component_options' ] ) ? $this->results[ 'component_options' ] : array();
	}

	/**
	 * Query args getter.
	 *
	 * @return array
	 */
	public function get_query_args() {
		return ! empty( $this->results[ 'query_args' ] ) ? $this->results[ 'query_args' ] : array();
	}

	/**
	 * True if the query was paged and there is more than one page to show.
	 *
	 * @return boolean
	 */
	public function has_pages() {
		return isset( $this->results[ 'pages' ] ) ? $this->results[ 'pages' ] > 1 : false;
	}

	/**
	 * Get the page number of the query.
	 *
	 * @return int
	 */
	public function get_current_page() {
		return ! empty( $this->results[ 'current_page' ] ) ? $this->results[ 'current_page' ] : array();
	}

	/**
	 * Get the total number of pages.
	 *
	 * @return int
	 */
	public function get_pages_num() {
		return isset( $this->results[ 'pages' ] ) ? $this->results[ 'pages' ] : 1;
	}

	/**
	 * Runs the query.
	 *
	 * @param  array  $component_data
	 * @param  array  $query_args
	 */
	private function query( $component_data, $query_args ) {

		$defaults = array(
			// Set to false when running raw queries.
			'orderby'              => false,
			// Use false to get all results -- set to false when running raw queries or dropdown-template queries.
			'per_page'             => false,
			// Page number to load, in effect only when 'per_page' is set.
			// When set to 'selected', 'load_page' will point to the page that contains the current option, passed in 'selected_option'.
			'load_page'            => 1,
			'post_ids'             => ! empty( $component_data[ 'assigned_ids' ] ) ? $component_data[ 'assigned_ids' ] : false,
			'query_type'           => ! empty( $component_data[ 'query_type' ] ) ? $component_data[ 'query_type' ] : 'product_ids',
			// ID of selected option, used when 'load_page' is set to 'selected'.
			'selected_option'      => '',
			'disable_cache'        => false,
			// Out of stock options included in results by default. Use 'woocommerce_composite_component_options_query_args_current' filter to set to true.
			'exclude_out_of_stock' => false
		);

		$query_args = wp_parse_args( $query_args, $defaults );

		$args = array(
			'post_type'            => 'product',
			'post_status'          => 'publish',
			'ignore_sticky_posts'  => 1,
			'nopaging'             => true,
			'order'                => 'desc',
			'fields'               => 'ids',
			'meta_query'           => array(),
			'use_transients_cache' => false,
		);

		/*-----------------------------------------------------------------------------------*/
		/*  Prepare query for product ids.                                                   */
		/*-----------------------------------------------------------------------------------*/

		if ( 'product_ids' === $query_args[ 'query_type' ] ) {

			if ( $query_args[ 'post_ids' ] ) {

				$args[ 'post__in' ] = array_values( $query_args[ 'post_ids' ] );

			} else {

				$args[ 'post__in' ] = array( '0' );
			}
		}

		/*-----------------------------------------------------------------------------------*/
		/*  Sorting.                                                                         */
		/*-----------------------------------------------------------------------------------*/

		$args = $this->query_ordering( $args, $query_args );

		/*-----------------------------------------------------------------------------------*/
		/*	Remove out-of-stock results in front-end queries.
		/*-----------------------------------------------------------------------------------*/

		if ( false !== $query_args[ 'orderby' ] || false !== $query_args[ 'per_page' ] ) {
			$args = $this->stock_status( $args, $query_args );
		}

		/*-----------------------------------------------------------------------------------*/
		/*  Pagination.                                                                      */
		/*-----------------------------------------------------------------------------------*/

		$load_selected_page = false;

		// Check if we need to find the page that contains the current selection -- 'load_page' must be set to 'selected' and all relevant parameters must be provided.

		if ( 'selected' === $query_args[ 'load_page' ] ) {

			if ( $query_args[ 'per_page' ] && $query_args[ 'selected_option' ] !== '' ) {
				$load_selected_page = true;
			} else {
				$query_args[ 'load_page' ] = 1;
			}
		}

		// Otherwise, just check if we need to do a paginated query -- note that when looking for the page that contains the current selection, we are running an unpaginated query first.

		if ( $query_args[ 'per_page' ] && false === $load_selected_page ) {

			$args[ 'nopaging' ]       = false;
			$args[ 'posts_per_page' ] = $query_args[ 'per_page' ];
			$args[ 'paged' ]          = $query_args[ 'load_page' ];
		}

		/*-----------------------------------------------------------------------------------*/
		/*  Optimize 'raw' queries.                                                          */
		/*-----------------------------------------------------------------------------------*/

		if ( false === $query_args[ 'orderby' ] && false === $query_args[ 'per_page' ] ) {

			$args[ 'update_post_term_cache' ] = false;
			$args[ 'update_post_meta_cache' ] = false;
			$args[ 'cache_results' ]          = false;

			if ( false === $query_args[ 'disable_cache' ] && ! empty( $component_data[ 'component_id' ] ) && ! empty( $component_data[ 'composite_id' ] ) ) {
				$args[ 'use_transients_cache' ] = true;
			}
		}

		/*-----------------------------------------------------------------------------------*/
		/*  Modify query and apply filters by hooking at this point.                         */
		/*-----------------------------------------------------------------------------------*/

		/**
		 * Filter args passed to WP_Query.
		 *
		 * @param  array  $wp_query_args
		 * @param  array  $cp_query_args
		 * @param  array  $component_data
		 */
		$args = apply_filters( 'woocommerce_composite_component_options_query_args', $args, $query_args, $component_data );

		/*-----------------------------------------------------------------------------------*/
		/*  Go for it.                                                                       */
		/*-----------------------------------------------------------------------------------*/

		$query                = false;
		$cached_results       = false;
		$cached_results_array = false;
		$use_cache            = $args[ 'use_transients_cache' ] && ! defined( 'WC_CP_DEBUG_QUERY_TRANSIENTS' );
		$component_id         = $use_cache ? $component_data[ 'component_id' ] : '';
		$transient_name       = $use_cache ? 'wc_cp_query_results_' . $component_data[ 'composite_id' ] : '';
		$cached_results_array = $use_cache ? get_transient( $transient_name ) : false;

		if ( is_array( $cached_results_array ) && isset( $cached_results_array[ $component_id ] ) && is_array( $cached_results_array[ $component_id ] ) ) {
			if ( isset( $cached_results_array[ $component_id ][ 'version' ] ) && $cached_results_array[ $component_id ][ 'version' ] === WC_Cache_Helper::get_transient_version( 'product' ) ) {
				$cached_results = $cached_results_array[ $component_id ];
			}
		}

		if ( false === $cached_results ) {

			$query = new WP_Query( $args );

			$this->results = array(
				'query_args'        => $query_args,
				'pages'             => $query->max_num_pages,
				'current_page'      => $query->get( 'paged' ),
				'component_options' => $query->posts
			);

			if ( $use_cache ) {

				if ( is_array( $cached_results_array ) ) {
					$cached_results_array[ $component_id ] = array_merge( $this->results, array( 'version' => WC_Cache_Helper::get_transient_version( 'product' ) ) );
				} else {
					$cached_results_array = array_merge( array( $component_id => $this->results ), array( 'version' => WC_Cache_Helper::get_transient_version( 'product' ) ) );
				}

				set_transient( $transient_name, $cached_results_array, ( DAY_IN_SECONDS * 7 ) );
			}

		} else {

			$this->results = $cached_results;
		}

		/*-----------------------------------------------------------------------------------------------------------------------------------------------*/
		/*  When told to do so, use the results of the 1st query to find the page that contains the current selection.                                   */
		/*-----------------------------------------------------------------------------------------------------------------------------------------------*/

		if ( $load_selected_page && $query_args[ 'per_page' ] && $query_args[ 'per_page' ] < $query->found_posts ) {

			$results               = $this->get_component_options();
			$selected_option_index = array_search( $query_args[ 'selected_option' ], $results ) + 1;
			$selected_option_page  = ceil( $selected_option_index / $query_args[ 'per_page' ] );

			// Sorting and filtering has been done, so now just run a simple query to paginate the results.

			if ( ! empty( $results ) ) {

				$selected_args = array(
					'post_type'           => 'product',
					'post_status'         => 'publish',
					'ignore_sticky_posts' => 1,
					'nopaging'            => false,
					'posts_per_page'      => $query_args[ 'per_page' ],
					'paged'               => $selected_option_page,
					'order'               => 'desc',
					'orderby'             => 'post__in',
					'post__in'            => $results,
					'fields'              => 'ids',
				);

				$query = new WP_Query( $selected_args );

				$this->results = array(
					'query_args'        => $query_args,
					'pages'             => $query->max_num_pages,
					'current_page'      => $query->get( 'paged' ),
					'component_options' => $query->posts
				);
			}
		}
	}

	/**
	 * Modify the query args when doing a category ids query.
	 *
	 * @param  array  $type
	 * @param  array  $query_args
	 * @param  array  $component_data
	 * @return array
	 */
	public function category_ids_query( $args, $query_args, $component_data ) {

		if ( 'category_ids' === $query_args[ 'query_type' ] ) {

			$args[ 'tax_query' ][] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'product_cat',
					'terms'    => ! empty( $component_data[ 'assigned_category_ids' ] ) ? array_values( $component_data[ 'assigned_category_ids' ] ) : array( '0' ),
					'operator' => 'IN'
				),
				array(
					'taxonomy' => 'product_type',
					'field'    => 'name',
					'terms'    => apply_filters( 'woocommerce_composite_products_supported_types', array( 'simple', 'variable', 'bundle' ) ),
					'operator' => 'IN'
				)
			);

		}

		return $args;
	}

	/**
	 * Omit out-of-stock results depending on the 'woocommerce_hide_out_of_stock_items' option state.
	 *
	 * @param  array  $type
	 * @return array
	 */
	public function stock_status( $args, $query_args = array() ) {

		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) || ( isset( $query_args[ 'exclude_out_of_stock' ] ) && $query_args[ 'exclude_out_of_stock' ] ) ) {

			if ( WC_CP_Core_Compatibility::is_wc_version_gte_2_7() ) {

				$product_visibility_terms = wc_get_product_visibility_term_ids();

				$args[ 'tax_query' ][] = array(
					'taxonomy'      => 'product_visibility',
					'field'         => 'term_taxonomy_id',
					'terms'         => $product_visibility_terms[ 'outofstock' ],
					'operator'      => 'NOT IN'
				);

			} else {

				$args[ 'meta_query' ][] = array(
					'relation' => 'OR',
					array(
						'key' 		=> '_stock_status',
						'value' 	=> 'instock',
						'compare' 	=> '='
					),
					array(
						'key' 		=> '_stock_status',
						'compare' 	=> 'NOT EXISTS'
					)
				);
			}
		}

		return $args;
	}

	/**
	 * Query ordering. You can add your own custom sorting function into 'woocommerce_composite_component_orderby' and then implement it by hooking into 'woocommerce_composite_component_options_query_args'.
	 *
	 * @param  array  $type
	 * @param  array  $query_args
	 * @return array
	 */
	public function query_ordering( $args, $query_args ) {

		$orderby = $query_args[ 'orderby' ];

		if ( $orderby ) {

			$orderby_value = explode( '-', $orderby );
			$orderby       = esc_attr( $orderby_value[0] );
			$order         = ! empty( $orderby_value[1] ) ? $orderby_value[1] : '';

			switch ( $orderby ) {

				case 'default' :
					if ( 'product_ids' === $query_args[ 'query_type' ] ) {
						$args[ 'orderby' ] = 'post__in';
					}
				break;

				case 'menu_order' :
					if ( 'product_ids' === $query_args[ 'query_type' ] ) {
						$args[ 'orderby' ] = 'menu_order title';
						$args[ 'order' ]   = $order == 'desc' ? 'desc' : 'asc';
					}
				break;

				case 'rand' :
					$args[ 'orderby' ]  = 'rand';
				break;

				case 'date' :
					$args[ 'orderby' ]  = 'date';
				break;

				case 'price' :
					$args[ 'orderby' ]  = 'meta_value_num';
					$args[ 'meta_key' ] = '_price';
					$args[ 'order' ]    = $order == 'desc' ? 'desc' : 'asc';
				break;

				case 'popularity' :
					$args[ 'orderby' ]  = 'meta_value_num';
					$args[ 'meta_key' ] = 'total_sales';
				break;

				case 'rating' :
					// Sorting handled later though a hook
					add_filter( 'posts_clauses', array( $this, 'order_by_rating_post_clauses' ) );
				break;

				case 'title' :
					$args[ 'orderby' ] = 'title';
					$args[ 'order' ]   = $order == 'desc' ? 'desc' : 'asc';
				break;

			}

		// In effect for back-end queries and queries carried out during sync_composite().
		} else {

			// Make ids appear in the sequence they are saved
			if ( 'product_ids' === $query_args[ 'query_type' ] ) {
				$args[ 'orderby' ] = 'post__in';
			}
		}

		return $args;
	}

	/**
	 * order_by_rating_post_clauses function.
	 *
	 * @access public
	 * @param array $args
	 * @return array
	 */
	public function order_by_rating_post_clauses( $args ) {

		global $wpdb;

		$args[ 'fields' ] .= ", AVG( $wpdb->commentmeta.meta_value ) as average_rating ";
		$args[ 'where' ]  .= " AND ( $wpdb->commentmeta.meta_key = 'rating' OR $wpdb->commentmeta.meta_key IS null ) ";
		$args[ 'join' ]   .= "
			LEFT OUTER JOIN $wpdb->comments ON($wpdb->posts.ID = $wpdb->comments.comment_post_ID)
			LEFT JOIN $wpdb->commentmeta ON($wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id)
		";

		$args[ 'orderby' ] = "average_rating DESC, $wpdb->posts.post_date DESC";
		$args[ 'groupby' ] = "$wpdb->posts.ID";

		remove_filter( 'posts_clauses', array( $this, 'order_by_rating_post_clauses' ) );

		return $args;
	}

	/**
	 * Order results by priority numbers saved in $component_data[ 'sort_priorities' ]. Modifies the 'default' ordering behaviour.
	 *
	 * @param  array  $args
	 * @param  array  $query_args
	 * @param  array  $component_data
	 * @return array
	 */
	public function order_by_priority( $args, $query_args, $component_data ) {

		global $wpdb;

		if ( $query_args[ 'orderby' ] == 'default' && $query_args[ 'query_type' ] == 'product_ids' && ! empty( $component_data[ 'assigned_ids' ] ) && ! empty( $component_data[ 'sort_priorities' ] ) ) {

			$sorted_options    = array();
			$unsorted_options  = array();
			$custom_sort_order = array_map( 'wc_clean', explode( WC_DELIMITER, $component_data[ 'sort_priorities' ] ) );

			foreach ( array_values( $component_data[ 'assigned_ids' ] ) as $loop => $product_id ) {

				if ( isset( $custom_sort_order[ $loop ] ) ) {
					$sorted_options[ $custom_sort_order[ $loop ] ] = $product_id;
				}
			}

			krsort( $sorted_options );

			$this->storage[ 'sort_priority' ] = $sorted_options;

			add_filter( 'posts_clauses', array( $this, 'order_by_priority_post_clauses' ) );
		}

		return $args;
	}

	/**
	 * Modify query to order results by an arbitrary priority sequence.
	 *
	 * @param  array  $args
	 * @return array
	 */
	public function order_by_priority_post_clauses( $args ) {

		global $wpdb;

		$args[ 'orderby' ] = "FIELD($wpdb->posts.ID," . implode( ',', $this->storage[ 'sort_priority' ] ) . ") DESC";

		remove_filter( 'posts_clauses', array( $this, 'order_by_priority_post_clauses' ) );

		return $args;
	}

	/**
	 * Activate attribute filters.
	 *
	 * @param  array  $args
	 * @param  array  $query_args
	 * @param  array  $component_data
	 * @return array
	 */
	public function attribute_filters( $args, $query_args, $component_data ) {

		if ( ! empty( $query_args[ 'filters' ] ) && ! empty( $query_args[ 'filters' ][ 'attribute_filter' ] ) ) {

			$attribute_filters = $query_args[ 'filters' ][ 'attribute_filter' ];

			$args[ 'tax_query' ][ 'relation' ] = 'AND';

			foreach ( $attribute_filters as $taxonomy_attribute_name => $selected_attribute_values ) {

				$args[ 'tax_query' ][] = array(
					'taxonomy' => $taxonomy_attribute_name,
					'terms'    => $selected_attribute_values,
					'operator' => 'IN'
				);
			}
		}

		return $args;
	}
}
