<?php
/**
 * WC_CP_Component class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    3.7.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Component abstraction. Contains data and maintains view state.
 *
 * @class    WC_CP_Component
 * @version  3.10.0
 */
class WC_CP_Component implements ArrayAccess {

	/**
	 * The view state of the component.
	 *
	 * @var WC_CP_Component_View
	 */
	public $view;

	/**
	 * The component ID.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * The component data.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * The composite product that the component belongs to.
	 *
	 * @var WC_Product_Composite
	 */
	private $composite;

	/**
	 * Constructor.
	 *
	 * @param  WC_Product_Composite  $composite
	 */
	public function __construct( $id, $composite ) {
		$this->id        = strval( $id );
		$this->composite = $composite;
		$this->view      = new WC_CP_Component_View( $this );

		$data = $composite->get_component_meta( $this->id );

		$data[ 'component_id' ] = $this->id;
		$data[ 'composite_id' ] = $this->get_composite_id();

		if ( ! isset( $data[ 'shipped_individually' ] ) ) {
			$data[ 'shipped_individually' ] = 'no';
		}

		if ( ! isset( $data[ 'priced_individually' ] ) ) {
			$data[ 'priced_individually' ] = 'no';
		}

		if ( ! isset( $data[ 'optional' ] ) ) {
			$data[ 'optional' ] = 'no';
		}

		if ( is_array( $data ) ) {
			/**
			 * Filter the raw metadata of a single component.
			 *
			 * @param  array                 $component_meta
			 * @param  string                $component_id
			 * @param  WC_Product_Composite  $product
			 */
			$this->data = apply_filters( 'woocommerce_composite_component_data', $data, $this->id, $composite );
		}
	}

	/**
	 * Composite product getter.
	 *
	 * @return WC_Product_Composite
	 */
	public function get_composite() {
		return $this->composite;
	}

	/**
	 * Composite product getter.
	 *
	 * @return WC_Product_Composite
	 */
	public function get_composite_id() {
		return WC_CP_Core_Compatibility::get_id( $this->composite );
	}

	/**
	 * Component ID getter.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Component data getter.
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Component options getter. Returns all product IDs added in this component.
	 *
	 * @return array
	 */
	public function get_options() {
		if ( ! isset( $this->options ) ) {
			$this->options = array_map( 'absint', self::query_component_options( $this->get_data() ) );
		}
		return $this->options;
	}

	/**
	 * Get the component title.
	 *
	 * @param  boolean  $formatted
	 * @return string
	 */
	public function get_title( $formatted = false ) {
		$data  = $this->get_data();
		$title = '';
		if ( ! empty( $data[ 'title' ] ) ) {
			$title = $formatted ? apply_filters( 'woocommerce_composite_component_title', esc_html( $data[ 'title' ] ), $this->id, $this->get_composite_id() ) : $data[ 'title' ];
		}
		return $title;
	}

	/**
	 * Get the component description.
	 *
	 * @param  boolean  $formatted
	 * @return string
	 */
	public function get_description( $formatted = false ) {
		$data        = $this->get_data();
		$description = '';
		if ( ! empty( $data[ 'description' ] ) ) {
			$description = $formatted ? apply_filters( 'woocommerce_composite_component_description', wpautop( do_shortcode( wp_kses_post( $data[ 'description' ] ) ) ), $this->id, $this->get_composite_id() ) : $data[ 'description' ];
		}
		return $description;
	}

	/**
	 * Get the component discount, if applicable.
	 *
	 * @return boolean
	 */
	public function get_discount() {
		$data = $this->get_data();
		return ! empty( $data[ 'discount' ] ) ? floatval( $data[ 'discount' ] ) : '';
	}

	/**
	 * Get the component min/max quantity.
	 *
	 * @param  string  $min_or_max
	 * @return boolean
	 */
	public function get_quantity( $min_or_max ) {

		$data = $this->get_data();
		$qty  = $qty_min = isset( $data[ 'quantity_min' ] ) ? $data[ 'quantity_min' ] : 1;

		if ( 'max' === $min_or_max ) {
			if ( isset( $data[ 'quantity_max' ] ) ) {
				$qty = $data[ 'quantity_max' ] !== '' ? max( $data[ 'quantity_max' ], $qty_min ) : '';
			}
		}

		return $qty !== '' ? absint( $qty ) : '';
	}

	/**
	 * True if the component has only one option and is not optional.
	 *
	 * @return boolean
	 */
	public function is_static() {
		return count( $this->get_options() ) === 1 && ! $this->is_optional();
	}

	/**
	 * True if the component is optional.
	 *
	 * @return boolean
	 */
	public function is_optional() {
		$data = $this->get_data();
		return 'yes' === $data[ 'optional' ];
	}

	/**
	 * True if the component is priced individually.
	 *
	 * @return boolean
	 */
	public function is_priced_individually() {
		$data = $this->get_data();
		return 'yes' === $data[ 'priced_individually' ];
	}

	/**
	 * True if the component is shipped individually.
	 *
	 * @return boolean
	 */
	public function is_shipped_individually() {
		$data = $this->get_data();
		return 'yes' === $data[ 'shipped_individually' ];
	}

	/**
	 * Get the default option/product ID.
	 *
	 * @return int|''
	 */
	public function get_default_option() {

		$data    = $this->get_data();
		$options = $this->get_options();

		if ( $this->is_static() ) {
			$selected_option = $options[0];
		} elseif ( isset( $_REQUEST[ 'wccp_component_selection' ][ $this->get_id() ] ) ) {
			$selected_option = $_REQUEST[ 'wccp_component_selection' ][ $this->get_id() ];
		} else {
			$selected_option = isset( $data[ 'default_id' ] ) && in_array( $data[ 'default_id' ], $this->get_options() ) ? $data[ 'default_id' ] : '';
		}

		/**
		 * Filter the default selection.
		 *
		 * @param  string                $selected_product_id
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_component_default_option', $selected_option, $this->get_id(), $this->get_composite() );
	}

	/**
	 * Create a product wrapper object from an option/product ID.
	 *
	 * @param  int  $product_id
	 * @return WC_CP_Product|false
	 */
	public function get_option( $product_id ) {

		$option = false;

		$product_id = absint( $product_id );

		if ( $product_id > 0 ) {
			if ( isset( $this->products[ $product_id ] ) ) {
				$option = $this->products[ $product_id ];
			} else {
				$option_obj = new WC_CP_Product( $product_id, $this->id, $this->composite );
				if ( $option_obj->exists() ) {
					$this->products[ $product_id ] = $option = $option_obj;
				}
			}
		}

		/**
		 * Filter the returned object.
		 *
		 * @param  WC_CP_Product         $option
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_component_option', $option, $this->get_id(), $this->get_composite() );
	}

	/**
	 * True if add-ons are disabled in this component.
	 *
	 * @return boolean
	 */
	public function disable_addons() {
		$data = $this->get_data();
		return isset( $data[ 'disable_addons' ] ) && 'yes' === $data[ 'disable_addons' ];
	}

	/**
	 * Get the default method to order the options of the component.
	 *
	 * @return string
	 */
	public function get_default_sorting_order() {

		/**
		 * Filter the default order-by method.
		 *
		 * @param  string                $order_by_id
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_component_default_orderby', 'default', $this->id, $this->composite );
	}

	/**
	 * Get component sorting options, if enabled.
	 *
	 * @return array
	 */
	public function get_sorting_options() {

		$data = $this->get_data();

		if ( isset( $data[ 'show_orderby' ] ) && $data[ 'show_orderby' ] == 'yes' ) {

			$default_orderby      = $this->get_default_sorting_order();
			$show_default_orderby = 'default' === $default_orderby;

			/**
			 * Filter the available sorting drowdown options.
			 *
			 * @param  array                 $order_by_data
			 * @param  string                $component_id
			 * @param  WC_Product_Composite  $product
			 */
			$orderby_options = apply_filters( 'woocommerce_composite_component_orderby', array(
				'default'    => __( 'Default sorting', 'woocommerce' ),
				'popularity' => __( 'Sort by popularity', 'woocommerce' ),
				'rating'     => __( 'Sort by average rating', 'woocommerce' ),
				'date'       => __( 'Sort by newness', 'woocommerce' ),
				'price'      => __( 'Sort by price: low to high', 'woocommerce' ),
				'price-desc' => __( 'Sort by price: high to low', 'woocommerce' )
			), $this->id, $this->composite );

			if ( ! $show_default_orderby ) {
				unset( $orderby_options[ 'default' ] );
			}

			if ( 'no' === get_option( 'woocommerce_enable_review_rating' ) ) {
				unset( $orderby_options[ 'rating' ] );
			}

			if ( ! $this->is_priced_individually() ) {
				unset( $orderby_options[ 'price' ] );
				unset( $orderby_options[ 'price-desc' ] );
			}

			return $orderby_options;
		}

		return false;
	}

	/**
	 * Get component filtering options, if enabled.
	 *
	 * @return array
	 */
	public function get_filtering_options() {

		global $wc_product_attributes;

		$data = $this->get_data();

		if ( isset( $data[ 'show_filters' ] ) && $data[ 'show_filters' ] == 'yes' ) {

			$active_filters = array();

			if ( ! empty( $data[ 'attribute_filters' ] ) ) {

				foreach ( $wc_product_attributes as $attribute_taxonomy_name => $attribute_data ) {

					if ( in_array( $attribute_data->attribute_id, $data[ 'attribute_filters' ] ) && taxonomy_exists( $attribute_taxonomy_name ) ) {

						$orderby = $attribute_data->attribute_orderby;

						switch ( $orderby ) {
							case 'name' :
								$args = array( 'orderby' => 'name', 'hide_empty' => false, 'menu_order' => false );
							break;
							case 'id' :
								$args = array( 'orderby' => 'id', 'order' => 'ASC', 'menu_order' => false, 'hide_empty' => false );
							break;
							case 'menu_order' :
								$args = array( 'menu_order' => 'ASC', 'hide_empty' => false );
							break;
						}

						$taxonomy_terms = get_terms( $attribute_taxonomy_name, $args );

						if ( $taxonomy_terms ) {

							switch ( $orderby ) {
								case 'name_num' :
									usort( $taxonomy_terms, '_wc_get_product_terms_name_num_usort_callback' );
								break;
								case 'parent' :
									usort( $taxonomy_terms, '_wc_get_product_terms_parent_usort_callback' );
								break;
							}

							// Add to array
							$filter_options = array();

							foreach ( $taxonomy_terms as $term ) {
								$filter_options[ $term->term_id ] = $term->name;
							}

							// Default filter format
							$filter_data = array(
								'filter_type'    => 'attribute_filter',
								'filter_id'      => $attribute_taxonomy_name,
								'filter_name'    => $attribute_data->attribute_label,
								'filter_options' => $filter_options,
							);

							$active_filters[] = $filter_data;
						}
					}
				}
			}

			/**
			 * Filter the active filters data.
			 *
			 * @param  array                 $active_filters
			 * @param  string                $component_id
			 * @param  WC_Product_Composite  $product
			 */
			$component_filtering_options = apply_filters( 'woocommerce_composite_component_filters', $active_filters, $this->id, $this->composite );

			if ( ! empty( $component_filtering_options ) ) {
				return $component_filtering_options;
			}
		}

		return false;
	}

	/*
	|--------------------------------------------------------------------------
	| Templating methods.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Indicates whether to show an empty placeholder dropdown option. By default a placeholder is displayed when the component has no default option.
	 *
	 * @return boolean
	 */
	public function show_placeholder_option() {

		$data             = $this->get_data();
		$show_placeholder = ! isset( $data[ 'default_id' ] ) || ! in_array( $data[ 'default_id' ], $this->get_options() );

		/**
		 * @param  string                $show_placeholder
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_component_show_placeholder_option', $show_placeholder, $this->get_id(), $this->get_composite() );
	}

	/**
	 * Component options selection style.
	 *
	 * @return string
	 */
	public function get_options_style() {

		$data = $this->get_data();

		if ( isset( $data[ 'selection_mode' ] ) ) {
			$options_style = $data[ 'selection_mode' ];
		} elseif ( ! empty( $this->composite->bto_selection_mode ) ) {
			$options_style = $this->composite->bto_selection_mode;
		} else {
			$options_style = 'dropdowns';
		}

		if ( false === self::get_options_style_data( $options_style ) ) {
			$options_style = 'dropdowns';
		}

		return $options_style;
	}

	/**
	 * Thumbnail loop columns count.
	 *
	 * @return int
	 */
	public function get_columns() {

		/**
		 * Filter count of thumbnail loop columns.
		 * By default, the component options loop has 1 column less than the main shop loop.
		 *
		 * @param  int                   $columns_count
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_component_loop_columns', max( apply_filters( 'loop_shop_columns', 4 ) - 1, 1 ), $this->id, $this->composite );
	}

	/**
	 * Thumbnail loop results per page.
	 *
	 * @return int
	 */
	public function get_results_per_page() {

		$thumbnail_columns = $this->get_columns();

		/**
		 * Filter count of thumbnails loop items per page.
		 * By default displays 2 rows of options.
		 *
		 * @param  int                   $per_page_count
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_component_options_per_page', $thumbnail_columns * 2, $this->id, $this->composite );
	}

	/**
	 * Controls whether component options loaded via ajax will be appended or paginated.
	 * When incompatible component options are set to be hidden, pagination cannot be used for simplicity.
	 *
	 * @return boolean
	 */
	public function paginate_options() {

		$options_style = $this->get_options_style();

		if ( self::options_style_supports( $options_style, 'pagination' ) ) {

			/**
			 * Last chance to disable pagination and show a "Load More" button instead.
			 *
			 * @param  boolean               $paginate
			 * @param  string                $component_id
			 * @param  WC_Product_Composite  $product
			 */
			$paginate = apply_filters( 'woocommerce_component_options_paginate_results', true, $this->id, $this->composite );

		} else {
			$paginate = false;
		}

		return $paginate;
	}

	/**
	 * Component pagination data.
	 *
	 * @return array
	 */
	public function get_pagination_data() {
		return array(
			'results_per_page'     => $this->get_results_per_page(),
			'max_results'          => sizeof( $this->get_options() ),
			'append_results'       => $this->paginate_options() ? 'no' : 'yes',
			'pagination_range'     => apply_filters( 'woocommerce_component_options_pagination_range', 3, $this->id, $this->composite ),
			'pagination_range_end' => apply_filters( 'woocommerce_component_options_pagination_range_end', 1, $this->id, $this->composite )
		);
	}

	/**
	 * Controls whether disabled component options will be hidden instead of greyed-out.
	 *
	 * @return boolean
	 */
	public function hide_disabled_options() {

		/**
		 * Filter to decide whether incompatible component options will be hidden.
		 *
		 * @param  boolean               $paginate
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_component_options_hide_incompatible', false, $this->id, $this->composite );
	}

	/**
	 * Get component placeholder image data.
	 *
	 * @return array
	 */
	public function get_image_data() {

		$data = $this->get_data();

		if ( ! $data ) {
			return '';
		}

		$image_src    = '';
		$image_srcset = '';
		$image_sizes  = '';

		if ( ! empty( $data[ 'thumbnail_id' ] ) ) {
			$attachment_id  = $data[ 'thumbnail_id' ];
			$image_src_data = wp_get_attachment_image_src( $attachment_id, apply_filters( 'woocommerce_composite_component_image_size', 'shop_catalog' ) );
			$image_src      = $image_src_data ? current( $image_src_data ) : '';
			$image_srcset   = $image_src_data && function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, 'shop_catalog' ) : '';
			$image_sizes    = $image_src_data && function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, 'shop_catalog' ) : '';
			$image_srcset   = $image_srcset ? $image_srcset : '';
			$image_sizes    = $image_sizes ? $image_sizes : '';
		}

		return array(
			'image_src'    => $image_src,
			'image_srcset' => $image_srcset,
			'image_sizes'  => $image_sizes,
			'image_title'  => $this->get_title( true )
		);
	}

	/**
	 * Create an array of classes to use in the component layout templates.
	 *
	 * @return array
	 */
	public function get_classes() {

		$classes    = array();
		$layout     = $this->composite->get_composite_layout_style();
		$components = $this->composite->get_components();
		$data       = $this->get_data();
		$style      = $this->get_options_style();

		/**
		 * Filter component "toggle box" view, by default enabled when using the "Progressive" layout.
		 *
		 * @param  boolean               $is_toggled
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		$toggled    = 'paged' === $layout ? false : apply_filters( 'woocommerce_composite_component_toggled', 'progressive' === $layout ? true : false, $this->id, $this->composite );

		$classes[]  = 'component';
		$classes[]  = $layout;
		$classes[]  = 'options-style-' . $style;

		if ( self::options_style_supports( $style, 'pagination' ) ) {
			if ( $this->paginate_options() ) {
				$classes[] = 'paginate-results';
			} else {
				$classes[] = 'append-results';
			}
		}

		if ( $this->hide_disabled_options() ) {
			$classes[] = 'hide-incompatible-products';
			$classes[] = 'hide-incompatible-variations';
		}

		if ( 'paged' === $layout ) {
			$classes[] = 'multistep';
		} elseif ( 'progressive' === $layout ) {

			$classes[] = 'multistep';
			$classes[] = 'autoscrolled';

			/*
			 * To leave open in blocked state, for instance when displaying options as thumbnails, use:
			 *
			 * if ( $toggled && $style === 'thumbnails' ) {
			 *     $classes[] = 'block-open';
			 * }
			 */
		}

		if ( $toggled ) {
			$classes[] = 'toggled';
		}

		if ( array_search( $this->id, array_keys( $components ) ) === 0 ) {
			$classes[] = 'active';
			$classes[] = 'first';

			if ( $toggled ) {
				$classes[] = 'open';
			}
		} else {

			if ( 'progressive' === $layout ) {
				$classes[] = 'blocked';
			}

			if ( $toggled ) {
				$classes[] = 'closed';
			}
		}

		if ( array_search( $this->id, array_keys( $components ) ) === count( $components ) - 1 ) {
			$classes[] = 'last';
		}

		if ( $this->is_static() ) {
			$classes[] = 'static';
		}

		$hide_product_thumbnail = isset( $data[ 'hide_product_thumbnail' ] ) ? $data[ 'hide_product_thumbnail' ] : 'no';

		if ( 'yes' === $hide_product_thumbnail ) {
			$classes[] = 'selection_thumbnail_hidden';
		}

		/**
		 * Filter component classes. Used for JS app initialization.
		 *
		 * @param  array                 $classes
		 * @param  string                $component_id
		 * @param  WC_Product_Composite  $product
		 */
		return apply_filters( 'woocommerce_composite_component_classes', $classes, $this->id, $this->composite );
	}

	/**
	 * True if the selected option title is hidden.
	 *
	 * @return boolean
	 */
	public function hide_selected_option_title() {
		$data = $this->get_data();
		return isset( $data[ 'hide_product_title' ] ) && 'yes' === $data[ 'hide_product_title' ];
	}

	/**
	 * True if the selected option description is hidden.
	 *
	 * @return boolean
	 */
	public function hide_selected_option_description() {
		$data = $this->get_data();
		return isset( $data[ 'hide_product_description' ] ) && 'yes' === $data[ 'hide_product_description' ];
	}

	/**
	 * True if the selected option thumbnail is hidden.
	 *
	 * @return boolean
	 */
	public function hide_selected_option_thumbnail() {
		$data = $this->get_data();
		return isset( $data[ 'hide_product_thumbnail' ] ) && 'yes' === $data[ 'hide_product_thumbnail' ];
	}

	/**
	 * True if the selected option thumbnail is hidden.
	 *
	 * @return boolean
	 */
	public function hide_selected_option_price() {
		$data = $this->get_data();
		return isset( $data[ 'hide_product_price' ] ) && 'yes' === $data[ 'hide_product_price' ];
	}

	/**
	 * True if component option prices need to be hidden.
	 *
	 * @return boolean
	 */
	public function hide_component_option_prices() {
		return apply_filters( 'woocommerce_composite_component_option_prices_hide', false, $this );
	}

	/**
	 * Subtotal visibility in the product/cart/order templates.
	 *
	 * @return boolean
	 */
	public function is_subtotal_visible( $where = 'product' ) {
		$data = $this->get_data();
		return false === isset( $data[ 'hide_subtotal_' . $where ] ) || 'no' === $data[ 'hide_subtotal_' . $where ];
	}

	/*
	|--------------------------------------------------------------------------
	| Array access methods for back-compat in templates.
	|--------------------------------------------------------------------------
	*/

	public function offsetGet( $offset ) {
		return isset( $this->data[ $offset ] ) ? $this->data[ $offset ] : null;
	}

	public function offsetExists( $offset ) {
		return isset( $this->data[ $offset ] );
	}

	public function offsetSet( $offset, $value ) {
		if ( is_null( $offset ) ) {
			$this->data[] = $value;
		} else {
			$this->data[ $offset ] = $value;
		}
	}

	public function offsetUnset( $offset ) {
		unset( $this->data[ $offset ] );
	}

	/*
	|--------------------------------------------------------------------------
	| Static API methods.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Sets up a WP_Query wrapper object to fetch component options. The query is configured based on the data stored in the 'component_data' array.
	 * Note that the query parameters are filterable - @see 'WC_CP_Query' class for details.
	 *
	 * @param  array  $component_data
	 * @param  array  $query_args
	 * @return array
	 */
	public static function query_component_options( $component_data, $query_args = array() ) {

		$query = new WC_CP_Query( $component_data, $query_args );

		return $query->get_component_options();
	}

	/**
	 * Get composite selection styles.
	 *
	 * @return array
	 */
	public static function get_options_styles() {

		$styles = array(
			array(
				'id'          => 'dropdowns',
				'description' => __( 'Dropdown', 'ultimatewoo-pro' ),
				'supports'    => array()
			),
			array(
				'id'          => 'thumbnails',
				'description' => __( 'Thumbnails', 'ultimatewoo-pro' ),
				'supports'    => array( 'pagination' )
			),
			array(
				'id'          => 'radios',
				'description' => __( 'Radio Buttons', 'ultimatewoo-pro' ),
				'supports'    => array()
			)
		);

		/**
		 * Filter the selection styles array to add custom styles.
		 *
		 * @param  array  $styles
		 */
		return apply_filters( 'woocommerce_composite_product_options_styles', $styles );
	}

	/**
	 * Get composite selection style data.
	 *
	 * @param  string  $style_id
	 * @return array|false
	 */
	public static function get_options_style_data( $style_id ) {

		$styles = self::get_options_styles();
		$found  = false;

		foreach ( $styles as $style ) {
			if ( $style[ 'id' ] ===  $style_id ) {
				$found = $style;
				break;
			}
		}

		return $found;
	}

	/**
	 * True if a selection style supports a given functionality.
	 *
	 * @param  string  $style_id
	 * @param  string  $what
	 * @return bool
	 */
	public static function options_style_supports( $style_id, $what ) {

		$options_style_data = self::get_options_style_data( $style_id );
		$supports           = false;

		if ( $options_style_data && isset( $options_style_data[ 'supports' ] ) && is_array( $options_style_data[ 'supports' ] ) && in_array( $what, $options_style_data[ 'supports' ] ) ) {
			$supports = true;
		}

		return $supports;
	}

	/**
	 * Set/upload component thumbnail.
	 *
	 * @since  3.11.0
	 *
	 * @param  int                   $thumbnail_id
	 * @param  string                $thumbnail_src
	 * @param  WC_Product_Composite  $product
	 * @return integer|false
	 */
	public static function set_thumbnail( $thumbnail_id, $thumbnail_src, $product ) {

		if ( ! $thumbnail_id && $thumbnail_src ) {

			$upload = wc_rest_upload_image_from_url( esc_url_raw( $thumbnail_src ) );

			if ( is_wp_error( $upload ) ) {
				return false;
			}

			$thumbnail_id = wc_rest_set_uploaded_image_as_attachment( $upload, $product->get_id() );
		}

		if ( ! wp_attachment_is_image( $thumbnail_id ) ) {
			return false;
		}

		return $thumbnail_id;
	}
}
