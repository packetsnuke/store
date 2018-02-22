<?php
/**
 * WC_PB_REST_API class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    5.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom REST API fields.
 *
 * @class    WC_PB_REST_API
 * @version  5.4.0
 */
class WC_PB_REST_API {

	/**
	 * Custom REST API product field names, indicating support for getting/updating.
	 * @var array
	 */
	private static $product_fields = array(
		'bundle_layout' => array( 'get', 'update' ),
		'bundled_by'    => array( 'get' ),
		'bundled_items' => array( 'get' )
	);

	/**
	 * Setup order class.
	 */
	public static function init() {

		// Creating/updating bundled items with the REST API is only possible if using WC 2.7+.
		if ( WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ) {
			self::$product_fields[ 'bundled_items' ][] = 'update';
		}

		// Register WP REST API custom product fields.
		add_action( 'rest_api_init', array( __CLASS__, 'register_product_fields' ), 0 );

		// Filter WP REST API order line item fields.
		add_action( 'rest_api_init', array( __CLASS__, 'filter_order_fields' ), 0 );

		// Hooks to add WC v1-v3 API custom order fields.
		self::add_legacy_hooks();
	}

	/**
	 * Filters REST API order responses to add custom data.
	 */
	public static function filter_order_fields() {

		// Filter WC REST API order response content to add bundle container/children references.

		// Schema.
		add_filter( 'woocommerce_rest_shop_order_schema', array( __CLASS__, 'filter_order_schema' ) );

		// Add extra data to line items.
		// v1.
		add_filter( 'woocommerce_rest_prepare_shop_order', array( __CLASS__, 'filter_order_response' ), 10, 3 );
		// v2.
		add_filter( 'woocommerce_rest_prepare_shop_order_object', array( __CLASS__, 'filter_order_response' ), 10, 3 );

		// Add bundle configuration data as meta for later post-processing.
		add_action( 'woocommerce_rest_set_order_item', array( __CLASS__, 'set_order_item' ), 10, 2 );

		// Modify order contents to include bundled items:
		// v1.
		add_filter( 'woocommerce_rest_pre_insert_shop_order', array( __CLASS__, 'add_bundle_to_order' ), 10, 2 );
		// v2.
		add_filter( 'woocommerce_rest_pre_insert_shop_order_object', array( __CLASS__, 'add_bundle_to_order' ), 10, 2 );
	}

	/*
	|--------------------------------------------------------------------------
	| Products.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Register custom REST API fields for product requests.
	 */
	public static function register_product_fields() {

		foreach ( self::$product_fields as $field_name => $field_supports ) {

			$args = array(
				'schema' => self::get_product_field_schema( $field_name )
			);

			if ( in_array( 'get', $field_supports ) ) {
				$args[ 'get_callback' ] = array( __CLASS__, 'get_product_field_value' );
			}
			if ( in_array( 'update', $field_supports ) ) {
				$args[ 'update_callback' ] = array( __CLASS__, 'update_product_field_value' );
			}

			register_rest_field( 'product', $field_name, $args );
		}
	}

	/**
	 * Gets extended (unprefixed) schema properties for products.
	 *
	 * @return array
	 */
	private static function get_extended_product_schema() {

		return array(
			'bundle_layout'              => array(
				'description' => __( 'Single-product details page layout. Applicable for bundle-type products only.', 'ultimatewoo-pro' ),
				'type'        => 'string',
				'enum'        => array( 'default', 'tabular' ),
				'context'     => array( 'view', 'edit' )
			),
			'bundled_by'                 => array(
				'description' => __( 'List of product bundle IDs that contain this product.', 'ultimatewoo-pro' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true
			),
			'bundled_items'              => array(
				'description' => __( 'List of bundled items contained in this product.', 'ultimatewoo-pro' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'                                    => array(
							'description' => __( 'Bundled item ID.', 'ultimatewoo-pro' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true
						),
						'delete'                                => array(
							'description' => __( 'Set to true to delete the bundled item with the specified ID.', 'ultimatewoo-pro' ),
							'type'        => 'boolean',
							'context'     => array( 'edit' )
						),
						'product_id'                            => array(
							'description' => __( 'Bundled product ID.', 'ultimatewoo-pro' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' )
						),
						'menu_order'                            => array(
							'description' => __( 'Bundled item menu order.', 'ultimatewoo-pro' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' )
						),
						'quantity_min'                          => array(
							'description' => __( 'Minimum bundled item quantity.', 'ultimatewoo-pro' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' )
						),
						'quantity_max'                          => array(
							'description' => __( 'Maximum bundled item quantity.', 'ultimatewoo-pro' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' )
						),
						'priced_individually'                   => array(
							'description' => __( 'Indicates whether the price of this bundled item is added to the base price of the bundle.', 'ultimatewoo-pro' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' )
						),
						'shipped_individually'                  => array(
							'description' => __( 'Indicates whether the bundled product is shipped separately from the bundle.', 'ultimatewoo-pro' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' )
						),
						'override_title'                        => array(
							'description' => __( 'Indicates whether the title of the bundled product is overridden in front-end and e-mail templates.', 'ultimatewoo-pro' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' )
						),
						'title'                                 => array(
							'description' => __( 'Title of the bundled product to display instead of the original product title, if overridden.', 'ultimatewoo-pro' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' )
						),
						'override_description'                  => array(
							'description' => __( 'Indicates whether the short description of the bundled product is overridden in front-end templates.', 'ultimatewoo-pro' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' )
						),
						'description'                           => array(
							'description' => __( 'Short description of the bundled product to display instead of the original product short description, if overridden.', 'ultimatewoo-pro' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' )
						),
						'optional'                              => array(
							'description' => __( 'Indicates whether the bundled item is optional.', 'ultimatewoo-pro' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' )
						),
						'hide_thumbnail'                        => array(
							'description' => __( 'Indicates whether the bundled product thumbnail is hidden in the single-product template.', 'ultimatewoo-pro' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' )
						),
						'discount'                              => array(
							'description' => __( 'Discount applied to the bundled product, applicable when the Priced Individually option is enabled.', 'ultimatewoo-pro' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' )
						),
						'override_variations'                   => array(
							'description' => __( 'Indicates whether variations filtering is active, applicable for variable bundled products only.', 'ultimatewoo-pro' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' )
						),
						'allowed_variations'                    => array(
							'description' => __( 'List of enabled variation IDs, applicable when variations filtering is active.', 'ultimatewoo-pro' ),
							'type'        => 'array',
							'context'     => array( 'view', 'edit' )
						),
						'override_default_variation_attributes' => array(
							'description' => __( 'Indicates whether the default variation attribute values are overridden, applicable for variable bundled products only.', 'ultimatewoo-pro' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' )
						),
						'default_variation_attributes'          => array(
							'description' => __( 'Overridden default variation attribute values, if applicable.', 'ultimatewoo-pro' ),
							'type'        => 'array',
							'context'     => array( 'view', 'edit' ),
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'id' => array(
										'description' => __( 'Attribute ID.', 'woocommerce' ),
										'type'        => 'integer',
										'context'     => array( 'view', 'edit' )
									),
									'name' => array(
										'description' => __( 'Attribute name.', 'woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit' )
									),
									'option' => array(
										'description' => __( 'Selected attribute term name.', 'woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit' )
									)
								)
							)
						),
						'single_product_visibility'             => array(
							'description' => __( 'Indicates whether the bundled product is visible in the single-product template.', 'ultimatewoo-pro' ),
							'type'        => 'string',
							'enum'        => array( 'visible', 'hidden' ),
							'context'     => array( 'view', 'edit' )
						),
						'cart_visibility'                       => array(
							'description' => __( 'Indicates whether the bundled product is visible in cart templates.', 'ultimatewoo-pro' ),
							'type'        => 'string',
							'enum'        => array( 'visible', 'hidden' ),
							'context'     => array( 'view', 'edit' )
						),
						'order_visibility'                      => array(
							'description' => __( 'Indicates whether the bundled product is visible in order/e-mail templates.', 'ultimatewoo-pro' ),
							'type'        => 'string',
							'enum'        => array( 'visible', 'hidden' ),
							'context'     => array( 'view', 'edit' )
						),
						'single_product_price_visibility'       => array(
							'description' => __( 'Indicates whether the bundled product price is visible in the single-product template, applicable when the Priced Individually option is enabled.', 'ultimatewoo-pro' ),
							'type'        => 'string',
							'enum'        => array( 'visible', 'hidden' ),
							'context'     => array( 'view', 'edit' )
						),
						'cart_price_visibility'                 => array(
							'description' => __( 'Indicates whether the bundled product price is visible in cart templates, applicable when the Priced Individually option is enabled.', 'ultimatewoo-pro' ),
							'type'        => 'string',
							'enum'        => array( 'visible', 'hidden' ),
							'context'     => array( 'view', 'edit' )
						),
						'order_price_visibility'                => array(
							'description' => __( 'Indicates whether the bundled product price is visible in order/e-mail templates, applicable when the Priced Individually option is enabled.', 'ultimatewoo-pro' ),
							'type'        => 'string',
							'enum'        => array( 'visible', 'hidden' ),
							'context'     => array( 'view', 'edit' )
						),
						'stock_status'                          => array(
							'description' => __( 'Stock status of the bundled item, taking minimum quantity into account.', 'ultimatewoo-pro' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'enum'        => array( 'in_stock', 'on_backorder', 'out_of_stock' ),
							'readonly'    => true
						)
					)
				)
			)
		);
	}

	/**
	 * Gets schema properties for PB product fields.
	 *
	 * @param  string  $field_name
	 * @return array
	 */
	public static function get_product_field_schema( $field_name ) {

		$extended_schema = self::get_extended_product_schema();
		$field_schema    = isset( $extended_schema[ $field_name ] ) ? $extended_schema[ $field_name ] : null;

		return $field_schema;
	}

	/**
	 * Gets values for PB product fields.
	 *
	 * @param  array            $response
	 * @param  string           $field_name
	 * @param  WP_REST_Request  $request
	 * @return array
	 */
	public static function get_product_field_value( $response, $field_name, $request ) {

		$data = null;

		if ( isset( $response[ 'id' ] ) ) {
			$product = wc_get_product( $response[ 'id' ] );
			$data    = self::get_product_field( $field_name, $product );
		}

		return $data;
	}

	/**
	 * Updates values for PB product fields.
	 *
	 * @param  mixed   $value
	 * @param  mixed   $response
	 * @param  string  $field_name
	 * @return boolean
	 */
	public static function update_product_field_value( $field_value, $response, $field_name ) {

		$product_id = false;

		if ( $response instanceof WP_Post ) {
			$product_id   = absint( $response->ID );
			$product      = wc_get_product( $product_id );
			$product_type = $product->get_type();
		} elseif ( $response instanceof WC_Product ) {
			$product_id   = WC_PB_Core_Compatibility::get_id( $response );
			$product      = $response;
			$product_type = $response->get_type();
		}

		// Only possible to set fields of 'bundle' type products.
		if ( $product_id && 'bundle' === $product_type ) {

			// Set bundle layout.
			if ( 'bundle_layout' === $field_name ) {

				if ( WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ) {

					$product->set_layout( $field_value );
					$product->save();

				} else {

					$field_value = in_array( $field_value, array( 'default', 'tabular' ) ) ? $field_value : 'default';
					update_post_meta( $product_id, '_wc_pb_layout_style', strval( $field_value ) );
				}

			// Set bundled items (WC 2.7+ only).
			} elseif ( 'bundled_items' === $field_name ) {

				// Updating shouldn't be registered for WC < 2.7, but oh well.
				if ( ! WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ) {
					throw new WC_REST_Exception( 'woocommerce_rest_unsupported_bundle_field', __( 'WooCommerce 2.7+ is required to create/update bundled items.', 'ultimatewoo-pro' ), 400 );
				}

				$new     = array();
				$updated = array();
				$deleted = array();

				if ( ! empty( $field_value ) && is_array( $field_value ) ) {
					foreach ( $field_value as $data ) {

						$action             = '';
						$bundled_item_id    = isset( $data[ 'bundled_item_id' ] ) ? absint( $data[ 'bundled_item_id' ] ) : 0;
						$bundled_product_id = isset( $data[ 'product_id' ] ) ? absint( $data[ 'product_id' ] ) : false;
						$bundled_product    = $bundled_product_id > 0 ? wc_get_product( $bundled_product_id ) : false;

						// Updating/deleting item.
						if ( $bundled_item_id > 0 ) {

							$action = 'update';

							if ( ! $product->has_bundled_item( $bundled_item_id ) ) {
								throw new WC_REST_Exception( 'woocommerce_rest_invalid_bundled_item_id', sprintf( __( 'Bundled item ID #%s does not exist in bundle.', 'ultimatewoo-pro' ), $bundled_item_id ), 400 );
							}

							if ( false === $bundled_product_id ) {
								throw new WC_REST_Exception( 'woocommerce_rest_required_bundled_product_reference', __( 'Bundled product ID undefined.', 'ultimatewoo-pro' ), 400 );
							}

							if ( 0 === $bundled_product_id || ( isset( $data[ 'delete' ] ) && true === $data[ 'delete' ] ) ) {
								$action = 'delete';
							}

						// Creating item.
						} elseif ( $bundled_item_id === 0 ) {
							$action = 'create';
						}

						// Add item to 'deleted' array.
						if ( 'delete' === $action ) {
							$deleted[] = $bundled_item_id;
							continue;
						}

						// Ensure product exists when updating/creating.
						if ( false === $bundled_product ) {
							throw new WC_REST_Exception( 'woocommerce_rest_invalid_bundled_product_id', sprintf( __( 'Product ID #%s is invalid.', 'ultimatewoo-pro' ), $bundled_product_id ), 400 );
						}

						// Parse default variation attributes options.
						if ( isset( $data[ 'default_variation_attributes' ] ) ) {
							$data[ 'default_variation_attributes' ] = self::process_default_attributes( $bundled_product, $data[ 'default_variation_attributes' ] );
						}

						// Stock status and max stock are read only.
						if ( isset( $data[ 'stock_status' ] ) ) {
							unset( $data[ 'stock_status' ] );
						}

						if ( isset( $data[ 'max_stock' ] ) ) {
							unset( $data[ 'max_stock' ] );
						}

						// Not stored in meta if defined and other than true.
						if ( isset( $data[ 'delete' ] ) ) {
							unset( $data[ 'delete' ] );
						}

						$bundled_data_item_array = array(
							'product_id' => $bundled_product_id,
							'meta_data'  => array_diff_key( $data, array( 'bundled_item_id' => 1, 'product_id' => 1, 'menu_order' => 1 ) )
						);

						if ( isset( $data[ 'menu_order' ] ) ) {
							$bundled_data_item_array[ 'menu_order' ] = absint( $data[ 'menu_order' ] );
						}

						// Add item to 'updated' array.
						if ( 'update' === $action ) {
							$bundled_data_item_array[ 'bundled_item_id' ] = $bundled_item_id;
							$updated[ $bundled_item_id ] = $bundled_data_item_array;
						// Add item to 'new' array.
						} else {
							$new[] = $bundled_data_item_array;
						}
					}

					$bundled_data_items       = $product->get_bundled_data_items( 'edit' );
					$bundled_data_items_array = array();

					if ( ! empty( $bundled_data_items ) ) {
						foreach ( $bundled_data_items as $bundled_data_item ) {

							$bundled_data_item_id = $bundled_data_item->get_id();

							// Omit item data if item deleted.
							if ( in_array( $bundled_data_item_id, $deleted ) ) {
								continue;
							// Add modified item data if item updated.
							} elseif ( isset( $updated[ $bundled_data_item_id ] ) ) {
								$bundled_data_items_array[] = $updated[ $bundled_data_item_id ];
							// Preserve item unless updated/deleted.
							} else {
								$bundled_data_items_array[] = array(
									'bundled_item_id' => $bundled_data_item->get_id(),
									'product_id'      => $bundled_data_item->get_product_id()
								);
							}
						}
					}

					// Add new items.
					$bundled_data_items_array = array_merge( $bundled_data_items_array, $new );
				}

				// Set bundled items on object. Neat.
				$product->set_bundled_data_items( $bundled_data_items_array );
				$product->save();
			}
		}

		return true;
	}

	/**
	 * Gets bundle-specific product data.
	 *
	 * @since  5.0.0
	 *
	 * @param  string      $key
	 * @param  WC_Product  $product
	 * @return array
	 */
	private static function get_product_field( $key, $product ) {

		$product_type = $product->get_type();
		$product_id   = WC_PB_Core_Compatibility::get_id( $product );

		switch ( $key ) {

			case 'bundle_layout' :

				$value = '';

				if ( 'bundle' === $product_type ) {
					$value = WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ? $product->get_layout( 'edit' ) : get_post_meta( $product_id, '_wc_pb_layout_style', true );
				}

			break;
			case 'bundled_by' :

				$value = array();

				if ( 'bundle' !== $product_type ) {
					$bundle_ids = array_values( wc_pb_get_bundled_product_map( $product_id ) );
					$value      = ! empty( $bundle_ids ) ? $bundle_ids : array();
				}

			break;
			case 'bundled_items' :

				$value = array();

				if ( 'bundle' === $product_type ) {

					$args = array(
						'bundle_id' => $product_id,
						'return'    => 'objects',
						'order_by'  => array( 'menu_order' => 'ASC' )
					);

					$data_items = WC_PB_DB::query_bundled_items( $args );

					if ( ! empty( $data_items ) ) {
						foreach ( $data_items as $data_item ) {
							$value[] = array(
								'bundled_item_id'                       => $data_item->get_id(),
								'product_id'                            => $data_item->get_product_id(),
								'menu_order'                            => $data_item->get_menu_order(),
								'quantity_min'                          => $data_item->get_meta( 'quantity_min' ),
								'quantity_max'                          => $data_item->get_meta( 'quantity_max' ),
								'priced_individually'                   => 'yes' === $data_item->get_meta( 'priced_individually' ),
								'shipped_individually'                  => 'yes' === $data_item->get_meta( 'shipped_individually' ),
								'override_title'                        => 'yes' === $data_item->get_meta( 'override_title' ),
								'title'                                 => $data_item->get_meta( 'title' ),
								'override_description'                  => 'yes' === $data_item->get_meta( 'override_description' ),
								'description'                           => $data_item->get_meta( 'description' ),
								'optional'                              => 'yes' === $data_item->get_meta( 'optional' ),
								'hide_thumbnail'                        => 'yes' === $data_item->get_meta( 'hide_thumbnail' ),
								'discount'                              => $data_item->get_meta( 'discount' ),
								'override_variations'                   => 'yes' === $data_item->get_meta( 'override_variations' ),
								'allowed_variations'                    => (array) $data_item->get_meta( 'allowed_variations' ),
								'override_default_variation_attributes' => 'yes' === $data_item->get_meta( 'override_default_variation_attributes' ),
								'default_variation_attributes'          => self::get_bundled_item_attribute_defaults( $data_item ),
								'single_product_visibility'             => $data_item->get_meta( 'single_product_visibility' ),
								'cart_visibility'                       => $data_item->get_meta( 'cart_visibility' ),
								'order_visibility'                      => $data_item->get_meta( 'order_visibility' ),
								'single_product_price_visibility'       => $data_item->get_meta( 'single_product_price_visibility' ),
								'cart_price_visibility'                 => $data_item->get_meta( 'cart_price_visibility' ),
								'order_price_visibility'                => $data_item->get_meta( 'order_price_visibility' ),
								'stock_status'                          => self::get_bundled_item_stock_status( $data_item, $product )
							);
						}
					}
				}

			break;
		}

		return $value;
	}

	/**
	 * Get default bundled variable product attributes - @see 'WC_REST_Products_Controller::get_default_attributes'.
	 *
	 * @param  WC_Bundled_Item_Data  $bundled_item_data
	 * @return array
	 */
	private static function get_bundled_item_attribute_defaults( $bundled_item_data ) {

		$default = array();
		$product = wc_get_product( $bundled_item_data->get_product_id() );

		if ( $product && $product->is_type( 'variable' ) ) {
			foreach ( array_filter( (array) $bundled_item_data->get_meta( 'default_variation_attributes' ), 'strlen' ) as $key => $value ) {
				if ( 0 === strpos( $key, 'pa_' ) ) {
					$default[] = array(
						'id'     => wc_attribute_taxonomy_id_by_name( $key ),
						'name'   => self::get_attribute_taxonomy_name( $key, $product ),
						'option' => $value,
					);
				} else {
					$default[] = array(
						'id'     => 0,
						'name'   => self::get_attribute_taxonomy_name( $key, $product ),
						'option' => $value,
					);
				}
			}
		}

		return $default;
	}

	/**
	 * Get product attribute taxonomy name - @see 'WC_REST_Products_Controller::get_attribute_taxonomy_name'.
	 *
	 * @since  5.2.0
	 *
	 * @param  string      $slug
	 * @param  WC_Product  $product
	 * @return string
	 */
	private static function get_attribute_taxonomy_name( $slug, $product ) {
		$attributes = $product->get_attributes();

		if ( ! isset( $attributes[ $slug ] ) ) {
			return str_replace( 'pa_', '', $slug );
		}

		$attribute = $attributes[ $slug ];

		// Taxonomy attribute name.
		if ( $attribute->is_taxonomy() ) {
			$taxonomy = $attribute->get_taxonomy_object();
			return $taxonomy->attribute_label;
		}

		// Custom product attribute name.
		return $attribute->get_name();
	}

	/**
	 * Save default bundled product attributes - @see 'WC_REST_Products_Controller::save_default_attributes'.
	 *
	 * @since 5.2.0
	 *
	 * @param  WC_Product  $bundled_product
	 * @param  array       $default_variation_attributes
	 * @return array
	 */
	private static function process_default_attributes( $bundled_product, $default_variation_attributes ) {

		$default_attributes = array();

		if ( is_array( $default_variation_attributes ) ) {

			$attributes = $bundled_product->get_attributes();

			foreach ( $default_variation_attributes as $attribute ) {

				$attribute_id   = 0;
				$attribute_name = '';

				// Check ID for global attributes or name for product attributes.
				if ( ! empty( $attribute[ 'id' ] ) ) {
					$attribute_id   = absint( $attribute[ 'id' ] );
					$attribute_name = wc_attribute_taxonomy_name_by_id( $attribute_id );
				} elseif ( ! empty( $attribute[ 'name' ] ) ) {
					$attribute_name = sanitize_title( $attribute[ 'name' ] );
				}

				if ( ! $attribute_id && ! $attribute_name ) {
					continue;
				}

				if ( isset( $attributes[ $attribute_name ] ) ) {
					$_attribute = $attributes[ $attribute_name ];

					if ( $_attribute[ 'is_variation' ] ) {
						$value = isset( $attribute[ 'option' ] ) ? wc_clean( stripslashes( $attribute[ 'option' ] ) ) : '';

						if ( ! empty( $_attribute[ 'is_taxonomy' ] ) ) {
							// If dealing with a taxonomy, we need to get the slug from the name posted to the API.
							$term = get_term_by( 'name', $value, $attribute_name );

							if ( $term && ! is_wp_error( $term ) ) {
								$value = $term->slug;
							} else {
								$value = sanitize_title( $value );
							}
						}

						if ( $value ) {
							$default_attributes[ $attribute_name ] = $value;
						}
					}
				}
			}
		}

		return $default_attributes;
	}

	/**
	 * Get bundled item stock status, taking min quantity into account.
	 *
	 * @param  WC_Bundled_Item_Data  $bundled_item_data
	 * @param  WC_Product_Bundle     $bundle
	 * @return string
	 */
	private static function get_bundled_item_stock_status( $bundled_item_data, $bundle ) {

		$bundled_item = wc_pb_get_bundled_item( $bundled_item_data, $bundle );
		$stock_status = 'in_stock';

		if ( $bundled_item ) {
			if ( false === $bundled_item->is_in_stock() ) {
				$stock_status = 'out_of_stock';
			} elseif ( $bundled_item->is_on_backorder() ) {
				$stock_status = 'on_backorder';
			}
		}

		return $stock_status;
	}


	/*
	|--------------------------------------------------------------------------
	| Orders.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Gets extended (unprefixed) schema properties for order line items.
	 *
	 * @return array
	 */
	private static function get_extended_order_line_item_schema() {

		return array(
			'bundled_by'     => array(
				'description' => __( 'Item ID of parent line item, applicable if the item is part of a Bundle.', 'ultimatewoo-pro' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true
			),
			'bundled_items' => array(
				'description' => __( 'Item IDs of bundled line items, applicable if the item is a Bundle container.', 'ultimatewoo-pro' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true
			),
			'bundled_item_title' => array(
				'description' => __( 'Title of the bundled product to display instead of the original product title.', 'ultimatewoo-pro' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true
			),
			'bundle_configuration' => array(
				'description' => __( 'Bundle configuration array. Must be defined when adding a bundle-type line item to an order, to ensure bundled line items are added to the order as well.', 'ultimatewoo-pro' ),
				'type'        => 'array',
				'context'     => array( 'edit' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'bundled_item_id'   => array(
							'description' => __( 'Bundled item ID.', 'ultimatewoo-pro' ),
							'type'        => 'integer',
							'context'     => array( 'edit' )
						),
						'product_id'        => array(
							'description' => __( 'Bundled product ID.', 'ultimatewoo-pro' ),
							'type'        => 'integer',
							'context'     => array( 'edit' )
						),
						'quantity'          => array(
							'description' => __( 'Chosen bundled item quantity.', 'ultimatewoo-pro' ),
							'type'        => 'integer',
							'context'     => array( 'edit' )
						),
						'title'             => array(
							'description' => __( 'Title of the bundled product to display instead of the original product title, if overridden.', 'ultimatewoo-pro' ),
							'type'        => 'string',
							'context'     => array( 'edit' )
						),
						'optional_selected' => array(
							'description' => __( 'Indicates whether the bundled product is selected. Applicable to optional bundled items.', 'ultimatewoo-pro' ),
							'type'        => 'boolean',
							'context'     => array( 'edit' )
						),
						'variation_id'      => array(
							'description' => __( 'Chosen variation ID, if applicable.', 'ultimatewoo-pro' ),
							'type'        => 'integer',
							'context'     => array( 'edit' )
						),
						'attributes'        => array(
							'description' => __( 'Chosen variation data to pass into \'WC_Order::add_product\', if applicable.', 'ultimatewoo-pro' ),
							'type'        => 'object',
							'context'     => array( 'edit' )
						),
						'args'              => array(
							'description' => __( 'Additional arguments to pass into \'WC_Order::add_product\', if applicable.', 'ultimatewoo-pro' ),
							'type'        => 'object',
							'context'     => array( 'edit' )
						)
					)
				)
			)
		);
	}

	/**
	 * Modify order contents to include bundled items.
	 *
	 * @param  WC_Order  $order
	 * @param  array     $request
	 */
	public static function add_bundle_to_order( $order, $request ) {

		$items_to_remove = array();

		foreach ( $order->get_items( 'line_item' ) as $item_id => $item ) {
			if ( $apply_configuration = $item->get_meta( '_bundle_configuration', true ) ) {

				$bundle   = $item->get_product();
				$quantity = $item->get_quantity();

				$items_to_remove[] = $item;

				WC_PB()->order->add_bundle_to_order( $bundle, $order, $quantity, array( 'configuration' => $apply_configuration ) );
				$item->delete_meta_data( '_bundle_configuration' );
			}
		}

		$order->save();

		foreach ( $items_to_remove as $remove_item ) {
			$order->remove_item( $remove_item->get_id() );
			$remove_item->delete();
		}

		return $order;
	}

	/**
	 * Save bundle configuration data on item for later processing.
	 *
	 * @param  WC_Order_Item  $item
	 * @param  array          $posted_item_data
	 */
	public static function set_order_item( $item, $posted_item_data ) {

		$action = ! empty( $posted_item_data[ 'id' ] ) ? 'update' : 'create';

		if ( 'create' === $action && ! empty( $posted_item_data[ 'bundle_configuration' ] ) ) {

			$bundle        = $item->get_product();
			$quantity      = $item->get_quantity();
			$configuration = array();

			if ( $bundle && $bundle->is_type( 'bundle' ) ) {

				foreach ( $posted_item_data[ 'bundle_configuration' ] as $bundled_item_configuration ) {

					// 'WC_PB_Cart::validate_bundle_configuration' expects the array to be indexed by bundled item ID.
					if ( ! empty( $bundled_item_configuration[ 'bundled_item_id' ] ) ) {
						$bundled_item_id                   = absint( $bundled_item_configuration[ 'bundled_item_id' ] );
						$configuration[ $bundled_item_id ] = array_diff_key( $bundled_item_configuration, array( 'bundled_item_id' => 1, 'optional_selected' => 1, 'attributes' => 1 ) );
					} else {
						continue;
					}

					// 'WC_PB_Cart::validate_bundle_configuration expects' 'optional_selected' to be 'yes'|'no', not boolean.
					if ( ! empty( $bundled_item_configuration[ 'optional_selected' ] ) ) {
						$configuration[ $bundled_item_id ][ 'optional_selected' ] = true === $bundled_item_configuration[ 'optional_selected' ] ? 'yes' : 'no';
					}

					// 'WC_PB_Cart::validate_bundle_configuration' expects posted attributes in 'WC_Cart::add_to_cart' format.
					if ( ! empty( $bundled_item_configuration[ 'attributes' ] ) && is_array( $bundled_item_configuration[ 'attributes' ] ) ) {

						if ( empty( $bundled_item_configuration[ 'bundled_item_id' ] ) ) {
							continue;
						}

						$bundled_item = $bundle->get_bundled_item( absint( $bundled_item_configuration[ 'bundled_item_id' ] ) );

						if ( ! $bundled_item ) {
							continue;
						}

						$posted_attributes = $bundled_item_configuration[ 'attributes' ];
						$cart_attributes   = array();
						$attributes        = $bundled_item->product->get_attributes();

						foreach ( $attributes as $attribute ) {

							if ( ! $attribute->get_variation() ) {
								continue;
							}

							$attribute_label = wc_attribute_label( $attribute->get_name() );
							$attribute_name  = $attribute->get_name();

							$variation_attribute_name = wc_variation_attribute_name( $attribute_name );

							foreach ( $posted_attributes as $posted_attribute ) {

								$found_value = false;

								// Find key.
								if ( isset( $posted_attribute[ 'name' ] ) ) {
									if ( stripslashes( $posted_attribute[ 'name' ] ) === $attribute_label ) {
										$found_value = true;
									} elseif ( wc_sanitize_taxonomy_name( stripslashes( $posted_attribute[ 'name' ] ) ) === str_replace( 'pa_', '', wc_sanitize_taxonomy_name( $attribute_name ) ) ) {
										$found_value = true;
									}
								}

								if ( $found_value ) {
									// Get value.
									if ( $attribute->is_taxonomy() ) {
										$value = sanitize_title( stripslashes( $posted_attribute[ 'option' ] ) );
									} else {
										$value = wc_clean( stripslashes( $posted_attribute[ 'option' ] ) );
									}

									$cart_attributes[ $variation_attribute_name ] = $value;
									break;
								}
							}
						}

						$configuration[ $bundled_item_id ][ 'attributes' ] = $cart_attributes;
					}
				}

				if ( WC_PB()->cart->validate_bundle_configuration( $bundle, $quantity, $configuration, 'add-to-order' ) ) {

					$item->update_meta_data( '_bundle_configuration', $configuration );

				} else {

					wc_clear_notices();
					$message = __( 'The submitted bundle configuration could not be added to this order.', 'ultimatewoo-pro' );
					throw new WC_REST_Exception( 'woocommerce_rest_invalid_bundle_configuration', $message, 400 );
				}

			} else {
				$message = __( 'A bundle with this ID does not exist.', 'ultimatewoo-pro' );
				throw new WC_REST_Exception( 'woocommerce_rest_invalid_bundle', $message, 400 );
			}
		}
	}

	/**
	 * Adds 'bundled_by' and 'bundled_items' schema properties to line items.
	 *
	 * @param  array  $schema
	 * @return array
	 */
	public static function filter_order_schema( $schema ) {

		foreach ( self::get_extended_order_line_item_schema() as $field_name => $field_content ) {
			$schema[ 'line_items' ][ 'properties' ][ $field_name ] = $field_content;
		}

		return $schema;
	}

	/**
	 * Filters WC REST API order responses to add references between bundle container/children items. Also modifies expanded product data based on the pricing and shipping settings.
	 *
	 * @since  5.0.0
	 *
	 * @param  WP_REST_Response   $response
	 * @param  WP_Post | WC_Data  $object
	 * @param  WP_REST_Request    $request
	 * @return WP_REST_Response
	 */
	public static function filter_order_response( $response, $object, $request ) {

		if ( $response instanceof WP_HTTP_Response ) {

			if ( $object instanceof WP_Post ) {
				$object = wc_get_order( $object );
			}

			$order_data = $response->get_data();
			$order_data = self::get_extended_order_data( $order_data, $object );

			$response->set_data( $order_data );
		}

		return $response;
	}

	/**
	 * Append bundled items data to order data.
	 *
	 * @param  array     $order_data
	 * @param  WC_Order  $order
	 * @return array
	 */
	private static function get_extended_order_data( $order_data, $order ) {

		if ( ! empty( $order_data[ 'line_items' ] ) ) {

			$order_items = $order->get_items();

			foreach ( $order_data[ 'line_items' ] as $order_data_item_index => $order_data_item ) {

				// Default values.
				$order_data[ 'line_items' ][ $order_data_item_index ][ 'bundled_by' ]         = '';
				$order_data[ 'line_items' ][ $order_data_item_index ][ 'bundled_item_title' ] = '';
				$order_data[ 'line_items' ][ $order_data_item_index ][ 'bundled_items' ]      = array();

				$order_data_item_id = $order_data_item[ 'id' ];

				// Add relationship references.
				if ( ! isset( $order_items[ $order_data_item_id ] ) ) {
					continue;
				}

				$parent_id    = wc_pb_get_bundled_order_item_container( $order_items[ $order_data_item_id ], $order, true );
				$children_ids = wc_pb_get_bundled_order_items( $order_items[ $order_data_item_id ], $order, true );

				if ( false !== $parent_id ) {
					$order_data[ 'line_items' ][ $order_data_item_index ][ 'bundled_by' ] = $parent_id;

					// Add overridden title.
					if ( isset( $order_items[ $order_data_item_id ][ 'bundled_item_title' ] ) ) {
						$order_data[ 'line_items' ][ $order_data_item_index ][ 'bundled_item_title' ] = $order_items[ $order_data_item_id ][ 'bundled_item_title' ];
					}

				} elseif ( ! empty( $children_ids ) ) {
					$order_data[ 'line_items' ][ $order_data_item_index ][ 'bundled_items' ] = $children_ids;
				} else {
					continue;
				}

				// Modify product data.
				if ( ! isset( $order_data_item[ 'product_data' ] ) ) {
					continue;
				}

				add_filter( 'woocommerce_get_product_from_item', array( WC_PB()->order, 'get_product_from_item' ) );
				$product = $order->get_product_from_item( $order_items[ $order_data_item_id ] );
				remove_filter( 'woocommerce_get_product_from_item', array( WC_PB()->order, 'get_product_from_item' ) );

				$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'price' ]                  = $product->get_price();
				$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'sale_price' ]             = $product->get_sale_price() ? $product->get_sale_price() : null;
				$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'regular_price' ]          = $product->get_regular_price();

				$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'shipping_required' ]      = $product->needs_shipping();

				$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'weight' ]                 = $product->get_weight() ? $product->get_weight() : null;
				$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'dimensions' ][ 'length' ] = $product->length;
				$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'dimensions' ][ 'width' ]  = $product->width;
				$order_data[ 'line_items' ][ $order_data_item_index ][ 'product_data' ][ 'dimensions' ][ 'height' ] = $product->height;
			}
		}

		return $order_data;
	}

	/**
	 * Filters WC v1-v3 REST API order response content to add bundle container/children item references.
	 */
	private static function add_legacy_hooks() {
		add_filter( 'woocommerce_api_order_response', array( __CLASS__, 'legacy_order_response' ), 10, 4 );
	}

	/**
	 * Filters WC v1-v3 REST API order responses to add references between bundle container/children items. Also modifies expanded product data based on the pricing and shipping settings.
	 *
	 * @param  array          $order_data
	 * @param  WC_Order       $order
	 * @param  array          $fields
	 * @param  WC_API_Server  $server
	 * @return array
	 */
	public static function legacy_order_response( $order_data, $order, $fields, $server ) {

		$order_data = self::get_extended_order_data( $order_data, $order );

		return $order_data;
	}
}

WC_PB_REST_API::init();
