<?php
/**
 * WC_PB_Meta_Box_Product_Data class
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
 * Product meta-box data for the 'Bundle' type.
 *
 * @class    WC_PB_Meta_Box_Product_Data
 * @version  5.3.0
 */
class WC_PB_Meta_Box_Product_Data {

	/**
	 * Hook in.
	 */
	public static function init() {

		// Creates the "Bundled Products" tab.
		add_action( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_data_tabs' ) );

		// Creates the panel for selecting bundled product options.
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_data_panel' ) );

		// Adds a tooltip to the Manage Stock option.
		add_action( 'woocommerce_product_options_stock', array( __CLASS__, 'stock_note' ) );

		// Add type-specific options.
		add_filter( 'product_type_options', array( __CLASS__, 'bundle_type_options' ) );

		// Processes and saves type-specific data.
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'process_bundle_data' ) );

		// Basic bundled product admin config options.
		add_action( 'woocommerce_bundled_product_admin_config_html', array( __CLASS__, 'bundled_product_admin_config_html' ), 10, 4 );

		// Advanced bundled product admin config options.
		add_action( 'woocommerce_bundled_product_admin_advanced_html', array( __CLASS__, 'bundled_product_admin_advanced_html' ), 10, 4 );

		// Bundle tab settings.
		add_action( 'woocommerce_bundled_products_admin_config', array( __CLASS__, 'bundled_products_admin_config' ) );

		// Cart editing option.
		add_action( 'woocommerce_product_options_advanced', array( __CLASS__, 'edit_in_cart_option' ) );

		// Extended "Sold Individually" option.
		add_action( 'woocommerce_product_options_sold_individually', array( __CLASS__, 'sold_individually_option' ) );
	}

	/**
	 * Renders extended "Sold Individually" option.
	 *
	 * @return void
	 */
	public static function sold_individually_option() {

		global $product_bundle_object;

		$sold_individually         = $product_bundle_object->get_sold_individually( 'edit' );
		$sold_individually_context = $product_bundle_object->get_sold_individually_context( 'edit' );

		$value = 'no';

		if ( $sold_individually ) {
			if ( ! in_array( $sold_individually_context, array( 'configuration', 'product' ) ) ) {
				$value = 'product';
			} else {
				$value = $sold_individually_context;
			}
		}

		// Provide context to the "Sold Individually" option.
		woocommerce_wp_select( array(
			'id'            => '_wc_pb_sold_individually',
			'wrapper_class' => 'show_if_bundle',
			'label'         => __( 'Sold individually', 'woocommerce' ),
			'options'       => array(
				'no'            => __( 'No', 'ultimatewoo-pro' ),
				'product'       => __( 'Yes', 'ultimatewoo-pro' ),
				'configuration' => __( 'Matching configurations only', 'ultimatewoo-pro' )
			),
			'value'         => $value,
			'desc_tip'      => 'true',
			'description'   => __( 'Allow only one of this bundle to be bought in a single order. Choose the <strong>Matching configurations only</strong> option to only prevent <strong>identically configured</strong> bundles from being purchased together.', 'ultimatewoo-pro' )
		) );
	}

	/**
	 * Renders the "Edit in Cart" option.
	 *
	 * @return void
	 */
	public static function edit_in_cart_option() {

		global $product_bundle_object;

		echo '<div class="options_group bundle_edit_in_cart_options show_if_bundle">';

		woocommerce_wp_checkbox( array(
			'id'          => '_wc_pb_edit_in_cart',
			'label'       => __( 'Editing in cart', 'ultimatewoo-pro' ),
			'value'       => $product_bundle_object->get_editable_in_cart( 'edit' ) ? 'yes' : 'no',
			'description' => __( 'Allow modifications to the configuration of this Bundle after it has been added to the cart.', 'ultimatewoo-pro' ),
			'desc_tip'    => true
		) );

		echo '</div>';
	}

	/**
	 * Add the "Bundled Products" panel tab.
	 */
	public static function product_data_tabs( $tabs ) {

		global $post, $product_object, $product_bundle_object;

		/*
		 * Create a global bundle-type object to use for populating fields.
		 */

		$post_id = $post->ID;

		if ( empty( $product_object ) || false === $product_object->is_type( 'bundle' ) ) {
			$product_bundle_object = $post_id ? new WC_Product_Bundle( $post_id ) : new WC_Product_Bundle();
		} else {
			$product_bundle_object = $product_object;
		}

		$tabs[ 'bundled_products' ] = array(
			'label'  => __( 'Bundled Products', 'ultimatewoo-pro' ),
			'target' => 'bundled_product_data',
			'class'  => array( 'show_if_bundle', 'wc_gte_26', 'bundled_product_options', 'bundled_product_tab' )
		);

		$tabs[ 'inventory' ][ 'class' ][] = 'show_if_bundle';

		return $tabs;
	}

	/**
	 * Data panels for Product Bundles.
	 */
	public static function product_data_panel() {

		?><div id="bundled_product_data" class="panel woocommerce_options_panel wc_gte_30">
			<?php
			/**
			 * 'woocommerce_bundled_products_admin_config' action.
			 */
			do_action( 'woocommerce_bundled_products_admin_config' );
			?>
		</div><?php
	}

	/**
	 * Add Bundled Products stock note.
	 */
	public static function stock_note() {

		global $post;

		?><span class="bundle_stock_msg show_if_bundle">
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'By default, the sale of a product within a bundle has the same effect on its stock as an individual sale. There are no separate inventory settings for bundled items. However, managing stock at bundle level can be very useful for allocating bundle stock quota, or for keeping track of bundled item sales.', 'ultimatewoo-pro' ) ); ?>
		</span><?php
	}

	/**
	 * Product bundle type-specific options.
	 *
	 * @param  array  $options
	 * @return array
	 */
	public static function bundle_type_options( $options ) {

		$options[ 'downloadable' ][ 'wrapper_class' ] .= ' show_if_bundle';
		$options[ 'virtual' ][ 'wrapper_class' ]      .= ' show_if_bundle';

		return $options;
	}

	/**
	 * Process, verify and save bundle type product data.
	 *
	 * @param  WC_Product  $product
	 * @return void
	 */
	public static function process_bundle_data( $product ) {

		if ( $product->is_type( 'bundle' ) ) {

			$props = array(
				'layout'                    => 'default',
				'editable_in_cart'          => false,
				'sold_individually'         => false,
				'sold_individually_context' => 'product'
			);

			/*
			 * Layout.
			 */

			if ( ! empty( $_POST[ '_wc_pb_layout_style' ] ) ) {
				$props[ 'layout' ] = wc_clean( $_POST[ '_wc_pb_layout_style' ] );
			}

			/*
			 * Cart editing option.
			 */

			if ( ! empty( $_POST[ '_wc_pb_edit_in_cart' ] ) ) {
				$props[ 'editable_in_cart' ] = true;
			}

			/*
			 * Extended "Sold Individually" option.
			 */

			if ( ! empty( $_POST[ '_wc_pb_sold_individually' ] ) ) {

				$sold_individually_context = wc_clean( $_POST[ '_wc_pb_sold_individually' ] );

				if ( in_array( $sold_individually_context, array( 'product', 'configuration' ) ) ) {
					$props[ 'sold_individually' ]         = true;
					$props[ 'sold_individually_context' ] = $sold_individually_context;
				}
			}

			if ( ! defined( 'WC_PB_UPDATING' ) ) {

				$posted_bundle_data    = isset( $_POST[ 'bundle_data' ] ) ? $_POST[ 'bundle_data' ] : false;
				$processed_bundle_data = self::process_posted_bundle_data( $posted_bundle_data, $product->get_id() );

				if ( empty( $processed_bundle_data ) ) {

					self::add_admin_error( __( 'Please add at least one product to the bundle before publishing. To add products, click on the <strong>Bundled Products</strong> tab.', 'ultimatewoo-pro' ) );
					$props[ 'bundled_data_items' ] = array();

				} else {

					foreach ( $processed_bundle_data as $key => $data ) {
						$processed_bundle_data[ $key ] = array(
							'bundled_item_id' => $data[ 'item_id' ],
							'bundle_id'       => $product->get_id(),
							'product_id'      => $data[ 'product_id' ],
							'menu_order'      => $data[ 'menu_order' ],
							'meta_data'       => array_diff_key( $data, array( 'item_id' => 1, 'product_id' => 1, 'menu_order' => 1 ) )
						);
					}

					$props[ 'bundled_data_items' ] = $processed_bundle_data;
				}

				$product->set( $props );

			} else {
				self::add_admin_error( __( 'Your changes have not been saved &ndash; please wait for the <strong>WooCommerce Product Bundles Data Update</strong> routine to complete before creating new bundles or making changes to existing ones.', 'ultimatewoo-pro' ) );
			}
		}
	}

	/**
	 * Sort by menu order callback.
	 *
	 * @param  array  $a
	 * @param  array  $b
	 * @return int
	 */
	public static function menu_order_sort( $a, $b ) {
		if ( isset( $a[ 'menu_order' ] ) && isset( $b[ 'menu_order' ] ) ) {
			return $a[ 'menu_order' ] - $b[ 'menu_order' ];
		} else {
			return isset( $a[ 'menu_order' ] ) ? 1 : -1;
		}
	}

	/**
	 * Process posted bundled item data.
	 *
	 * @param  array  $posted_bundle_data
	 * @param  mixed  $post_id
	 * @return mixed
	 */
	public static function process_posted_bundle_data( $posted_bundle_data, $post_id ) {

		$bundle_data = array();

		if ( ! empty( $posted_bundle_data ) ) {

			$sold_individually_notices = array();
			$times                     = array();
			$loop                      = 0;

			// Sort posted data by menu order.
			usort( $posted_bundle_data, array( __CLASS__, 'menu_order_sort' ) );

			foreach ( $posted_bundle_data as $data ) {

				$product_id = isset( $data[ 'product_id' ] ) ? absint( $data[ 'product_id' ] ) : false;
				$item_id    = isset( $data[ 'item_id' ] ) ? absint( $data[ 'item_id' ] ) : false;

				$product = wc_get_product( $product_id );

				if ( ! $product ) {
					continue;
				}

				$product_type  = $product->get_type();
				$product_title = $product->get_title();
				$is_sub        = in_array( $product_type, array( 'subscription', 'variable-subscription' ) );

				if ( in_array( $product_type, array( 'simple', 'variable', 'subscription', 'variable-subscription' ) ) && ( $post_id != $product_id ) && ! isset( $sold_individually_notices[ $product_id ] ) ) {

					// Bundling subscription products requires Subs v2.0+.
					if ( $is_sub ) {
						if ( ! class_exists( 'WC_Subscriptions' ) || version_compare( WC_Subscriptions::$version, '2.0.0', '<' ) ) {
							self::add_admin_error( sprintf( __( '<strong>%s</strong> was not saved. WooCommerce Subscriptions version 2.0 or higher is required in order to bundle Subscription products.', 'ultimatewoo-pro' ), $product_title ) );
							continue;
						}
					}

					// Only allow bundling multiple instances of non-sold-individually items.
					if ( ! isset( $times[ $product_id ] ) ) {
						$times[ $product_id ] = 1;
					} else {
						if ( $product->is_sold_individually() ) {
							self::add_admin_error( sprintf( __( '<strong>%s</strong> is sold individually and cannot be bundled more than once.', 'ultimatewoo-pro' ), $product_title ) );
							// Make sure we only display the notice once for every id.
							$sold_individually_notices[ $product_id ] = 'yes';
							continue;
						}
						$times[ $product_id ] += 1;
					}

					// Now start processing the posted data.
					$loop++;

					$item_data  = array();
					$item_title = $product_title;

					$item_data[ 'product_id' ] = $product_id;
					$item_data[ 'item_id' ]    = $item_id;

					// Save thumbnail preferences first.
					if ( isset( $data[ 'hide_thumbnail' ] ) ) {
						$item_data[ 'hide_thumbnail' ] = 'yes';
					} else {
						$item_data[ 'hide_thumbnail' ] = 'no';
					}

					// Save title preferences.
					if ( isset( $data[ 'override_title' ] ) ) {
						$item_data[ 'override_title' ] = 'yes';
						$item_data[ 'title' ]          = isset( $data[ 'title' ] ) ? stripslashes( $data[ 'title' ] ) : '';
					} else {
						$item_data[ 'override_title' ] = 'no';
					}

					// Save description preferences.
					if ( isset( $data[ 'override_description' ] ) ) {
						$item_data[ 'override_description' ] = 'yes';
						$item_data[ 'description' ] = isset( $data[ 'description' ] ) ? wp_kses_post( stripslashes( $data[ 'description' ] ) ) : '';
					} else {
						$item_data[ 'override_description' ] = 'no';
					}

					// Save optional.
					if ( isset( $data[ 'optional' ] ) ) {
						$item_data[ 'optional' ] = 'yes';
					} else {
						$item_data[ 'optional' ] = 'no';
					}

					// Save item pricing scheme.
					if ( isset( $data[ 'priced_individually' ] ) ) {
						$item_data[ 'priced_individually' ] = 'yes';
					} else {
						$item_data[ 'priced_individually' ] = 'no';
					}

					// Save item shipping scheme.
					if ( isset( $data[ 'shipped_individually' ] ) ) {
						$item_data[ 'shipped_individually' ] = 'yes';
					} else {
						$item_data[ 'shipped_individually' ] = 'no';
					}

					// Save quantity data.
					if ( isset( $data[ 'quantity_min' ] ) ) {

						if ( is_numeric( $data[ 'quantity_min' ] ) ) {

							$quantity = absint( $data[ 'quantity_min' ] );

							if ( $quantity >= 0 && $data[ 'quantity_min' ] - $quantity == 0 ) {

								if ( $quantity !== 1 && $product->is_sold_individually() ) {
									self::add_admin_error( sprintf( __( 'Item <strong>#%1$s: %2$s</strong> is sold individually &ndash; its minimum quantity cannot be higher than 1.', 'ultimatewoo-pro' ), $loop, $item_title ) );
									$item_data[ 'quantity_min' ] = 1;
								} else {
									$item_data[ 'quantity_min' ] = $quantity;
								}

							} else {
								self::add_admin_error( sprintf( __( 'The minimum quantity of item <strong>#%1$s: %2$s</strong> was not valid and has been reset. Please enter a non-negative integer value.', 'ultimatewoo-pro' ), $loop, $item_title ) );
								$item_data[ 'quantity_min' ] = 1;
							}
						}

					} else {
						$item_data[ 'quantity_min' ] = 1;
					}

					$quantity_min = $item_data[ 'quantity_min' ];

					// Save max quantity data.
					if ( isset( $data[ 'quantity_max' ] ) && ( is_numeric( $data[ 'quantity_max' ] ) || '' === $data[ 'quantity_max' ] ) ) {

						$quantity = '' !== $data[ 'quantity_max' ] ? absint( $data[ 'quantity_max' ] ) : '';

						if ( '' === $quantity || ( $quantity > 0 && $quantity >= $quantity_min && $data[ 'quantity_max' ] - $quantity == 0 ) ) {

							if ( $quantity !== 1 && $product->is_sold_individually() ) {
								self::add_admin_error( sprintf( __( 'Item <strong>#%1$s: %2$s</strong> is sold individually &ndash; its maximum quantity cannot be higher than 1.', 'ultimatewoo-pro' ), $loop, $item_title ) );
								$item_data[ 'quantity_max' ] = 1;
							} else {
								$item_data[ 'quantity_max' ] = $quantity;
							}

						} else {
							self::add_admin_error( sprintf( __( 'The maximum quantity of item <strong>#%1$s: %2$s</strong> was not valid and has been reset. Please enter a positive integer value, at least as high as the minimum quantity. Otherwise, leave the field empty for an unlimited maximum quantity.', 'ultimatewoo-pro' ), $loop, $item_title ) );
							$item_data[ 'quantity_max' ] = $quantity_min;
						}

					} else {
						$item_data[ 'quantity_max' ] = max( $quantity_min, 1 );
					}

					// Save sale price data.
					if ( isset( $data[ 'discount' ] ) ) {

						if ( 'yes' === $item_data[ 'priced_individually' ] && is_numeric( $data[ 'discount' ] ) ) {

							$discount = wc_format_decimal( $data[ 'discount' ] );

							if ( $discount < 0 || $discount > 100 ) {
								self::add_admin_error( sprintf( __( 'The discount value of item <strong>#%1$s: %2$s</strong> was not valid and has been reset. Please enter a positive number between 0-100.', 'ultimatewoo-pro' ), $loop, $item_title ) );
								$item_data[ 'discount' ] = '';
							} else {
								$item_data[ 'discount' ] = $discount;
							}
						} else {
							$item_data[ 'discount' ] = '';
						}
					} else {
						$item_data[ 'discount' ] = '';
					}

					// Save data related to variable items.
					if ( in_array( $product_type, array( 'variable', 'variable-subscription' ) ) ) {

						$allowed_variations = array();

						// Save variation filtering options.
						if ( isset( $data[ 'override_variations' ] ) ) {

							if ( isset( $data[ 'allowed_variations' ] ) ) {

								if ( is_array( $data[ 'allowed_variations' ] ) ) {
									$allowed_variations = array_map( 'intval', $data[ 'allowed_variations' ] );
								} else {
									$allowed_variations = array_filter( array_map( 'intval', explode( ',', $data[ 'allowed_variations' ] ) ) );
								}

								if ( count( $allowed_variations ) > 0 ) {

									$item_data[ 'override_variations' ] = 'yes';

									$item_data[ 'allowed_variations' ] = $allowed_variations;

									if ( isset( $data[ 'hide_filtered_variations' ] ) ) {
										$item_data[ 'hide_filtered_variations' ] = 'yes';
									} else {
										$item_data[ 'hide_filtered_variations' ] = 'no';
									}
								}
							} else {
								$item_data[ 'override_variations' ] = 'no';
								self::add_admin_error( sprintf( __( 'Please activate at least one variation of item <strong>#%1$s: %2$s</strong>.', 'ultimatewoo-pro' ), $loop, $item_title ) );
							}
						} else {
							$item_data[ 'override_variations' ] = 'no';
						}

						// Save defaults.
						if ( isset( $data[ 'override_default_variation_attributes' ] ) ) {

							if ( isset( $data[ 'default_variation_attributes' ] ) ) {

								// If filters are set, check that the selections are valid.
								if ( isset( $data[ 'override_variations' ] ) && ! empty( $allowed_variations ) ) {

									// The array to store all valid attribute options of the iterated product.
									$filtered_attributes = array();

									// Populate array with valid attributes.
									foreach ( $allowed_variations as $variation ) {

										$variation_data = array();

										// Get variation attributes.
										$variation_data = wc_get_product_variation_attributes( $variation );

										foreach ( $variation_data as $name => $value ) {

											$attribute_name  = substr( $name, strlen( 'attribute_' ) );
											$attribute_value = $value;

											// Populate array.
											if ( ! isset( $filtered_attributes[ $attribute_name ] ) ) {
												$filtered_attributes[ $attribute_name ][] = $attribute_value;
											} elseif ( ! in_array( $attribute_value, $filtered_attributes[ $attribute_name ] ) ) {
												$filtered_attributes[ $attribute_name ][] = $attribute_value;
											}
										}

									}

									// Check validity.
									foreach ( $data[ 'default_variation_attributes' ] as $name => $value ) {

										if ( '' === $value ) {
											continue;
										}

										if ( ! in_array( stripslashes( $value ), $filtered_attributes[ $name ] ) && ! in_array( '', $filtered_attributes[ $name ] ) ) {
											// Set option to "Any".
											$data[ 'default_variation_attributes' ][ $name ] = '';
											// Show an error.
											self::add_admin_error( sprintf( __( 'The attribute defaults of item <strong>#%1$s: %2$s</strong> are inconsistent with the set of active variations and have been reset.', 'ultimatewoo-pro' ), $loop, $item_title ) );
											continue;
										}
									}
								}

								// Save.
								foreach ( $data[ 'default_variation_attributes' ] as $name => $value ) {
									$item_data[ 'default_variation_attributes' ][ $name ] = stripslashes( $value );
								}

								$item_data[ 'override_default_variation_attributes' ] = 'yes';
							}

						} else {
							$item_data[ 'override_default_variation_attributes' ] = 'no';
						}
					}

					// Save item visibility preferences.
					$visibility = array(
						'product' => isset( $data[ 'single_product_visibility' ] ) ? 'visible' : 'hidden',
						'cart'    => isset( $data[ 'cart_visibility' ] ) ? 'visible' : 'hidden',
						'order'   => isset( $data[ 'order_visibility' ] ) ? 'visible' : 'hidden'
					);

					if ( 'hidden' === $visibility[ 'product' ] ) {

						if ( in_array( $product_type, array( 'variable', 'variable-subscription' ) ) ) {

							if ( 'yes' === $item_data[ 'override_default_variation_attributes' ] ) {

								if ( ! empty( $data[ 'default_variation_attributes' ] ) ) {

									foreach ( $data[ 'default_variation_attributes' ] as $default_name => $default_value ) {
										if ( ! $default_value ) {
											$visibility[ 'product' ] = 'visible';
											self::add_admin_error( sprintf( __( 'To hide item <strong>#%1$s: %2$s</strong> from the single-product template, please define defaults for its variation attributes.', 'ultimatewoo-pro' ), $loop, $item_title ) );
											break;
										}
									}

								} else {
									$visibility[ 'product' ] = 'visible';
								}

							} else {
								self::add_admin_error( sprintf( __( 'To hide item <strong>#%1$s: %2$s</strong> from the single-product template, please define defaults for its variation attributes.', 'ultimatewoo-pro' ), $loop, $item_title ) );
								$visibility[ 'product' ] = 'visible';
							}
						}
					}

					$item_data[ 'single_product_visibility' ] = $visibility[ 'product' ];
					$item_data[ 'cart_visibility' ]           = $visibility[ 'cart' ];
					$item_data[ 'order_visibility' ]          = $visibility[ 'order' ];

					// Save price visibility preferences.

					$item_data[ 'single_product_price_visibility' ] = isset( $data[ 'single_product_price_visibility' ] ) ? 'visible' : 'hidden';
					$item_data[ 'cart_price_visibility' ]           = isset( $data[ 'cart_price_visibility' ] ) ? 'visible' : 'hidden';
					$item_data[ 'order_price_visibility' ]          = isset( $data[ 'order_price_visibility' ] ) ? 'visible' : 'hidden';

					// Save position data.
					$item_data[ 'menu_order' ] = absint( $data[ 'menu_order' ] );

					/**
					 * Filter processed data before saving/updating WC_Bundled_Item_Data objects.
					 *
					 * @param  array  $item_data
					 * @param  array  $data
					 * @param  mixed  $item_id
					 * @param  mixed  $post_id
					 */
					$bundle_data[] = apply_filters( 'woocommerce_bundles_process_bundled_item_admin_data', $item_data, $data, $item_id, $post_id );
				}
			}
		}

		return $bundle_data;
	}

	/**
	 * Add bundled product "Basic" tab content.
	 *
	 * @param  int    $loop
	 * @param  int    $product_id
	 * @param  array  $item_data
	 * @param  int    $post_id
	 * @return void
	 */
	public static function bundled_product_admin_config_html( $loop, $product_id, $item_data, $post_id ) {

		$bundled_product = isset( $item_data[ 'bundled_item' ] ) ? $item_data[ 'bundled_item' ]->product : wc_get_product( $product_id );

		if ( in_array( $bundled_product->get_type(), array( 'variable', 'variable-subscription' ) ) ) {

			$allowed_variations  = isset( $item_data[ 'allowed_variations' ] ) ? $item_data[ 'allowed_variations' ] : '';
			$default_attributes  = isset( $item_data[ 'default_variation_attributes' ] ) ? $item_data[ 'default_variation_attributes' ] : '';

			$override_variations = isset( $item_data[ 'override_variations' ] ) && 'yes' === $item_data[ 'override_variations' ] ? 'yes' : '';
			$override_defaults   = isset( $item_data[ 'override_default_variation_attributes' ] ) && 'yes' === $item_data[ 'override_default_variation_attributes' ] ? 'yes' : '';

			?><div class="override_variations">
				<div class="form-field">
					<label for="override_variations">
						<?php echo __( 'Filter Variations', 'ultimatewoo-pro' ); ?>
					</label>
					<input type="checkbox" class="checkbox"<?php echo ( 'yes' === $override_variations ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][override_variations]" <?php echo ( 'yes' === $override_variations ? 'value="1"' : '' ); ?>/>
					<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Check to enable only a subset of the available variations.', 'ultimatewoo-pro' ) ); ?>
				</div>
			</div>


			<div class="allowed_variations" <?php echo 'yes' === $override_variations ? '' : 'style="display:none;"'; ?>>
				<div class="form-field"><?php

					$variations = $bundled_product->get_children();
					$attributes = $bundled_product->get_attributes();

					if ( sizeof( $variations ) < 100 ) {

						?><select multiple="multiple" name="bundle_data[<?php echo $loop; ?>][allowed_variations][]" style="width: 95%;" data-placeholder="<?php _e( 'Choose variations&hellip;', 'ultimatewoo-pro' ); ?>" class="wc-enhanced-select" > <?php

							foreach ( $variations as $variation_id ) {

								if ( is_array( $allowed_variations ) && in_array( $variation_id, $allowed_variations ) ) {
									$selected = 'selected="selected"';
								} else {
									$selected = '';
								}

								$variation_description = WC_PB_Helpers::get_product_variation_title( $variation_id );

								if ( ! $variation_description ) {
									continue;
								}

								echo '<option value="' . $variation_id . '" ' . $selected . '>' . $variation_description . '</option>';
							}

						?></select><?php

					} else {

						$allowed_variations_descriptions = array();

						if ( ! empty( $allowed_variations ) ) {

							foreach ( $allowed_variations as $allowed_variation_id ) {

								$variation_description = WC_PB_Helpers::get_product_variation_title( $allowed_variation_id, true );

								if ( ! $variation_description ) {
									continue;
								}

								$allowed_variations_descriptions[ $allowed_variation_id ] = $variation_description;
							}
						}

						?><select class="wc-product-search" multiple="multiple" style="width: 95%;" name="bundle_data[<?php echo $loop; ?>][allowed_variations][]" data-placeholder="<?php _e( 'Search for variations&hellip;', 'ultimatewoo-pro' ); ?>" data-action="woocommerce_search_bundled_variations" data-limit="1000" data-include="<?php echo $product_id; ?>"><?php
							foreach ( $allowed_variations_descriptions as $allowed_variation_id => $allowed_variation_description ) {
								echo '<option value="' . esc_attr( $allowed_variation_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $allowed_variation_description ) . '</option>';
							}
						?></select><?php
					}

				?></div>
			</div>

			<div class="override_default_variation_attributes">
				<div class="form-field">
					<label for="override_default_variation_attributes"><?php echo __( 'Override Default Selections', 'ultimatewoo-pro' ) ?></label>
					<input type="checkbox" class="checkbox"<?php echo ( 'yes' === $override_defaults ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][override_default_variation_attributes]" <?php echo ( 'yes' === $override_defaults ? 'value="1"' : '' ); ?>/>
					<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'In effect for this bundle only. The available options are in sync with the filtering settings above. Always save any changes made above before configuring this section.', 'ultimatewoo-pro' ) ); ?>
				</div>
			</div>

			<div class="default_variation_attributes" <?php echo 'yes' === $override_defaults ? '' : 'style="display:none;"'; ?>>
				<div class="form-field"><?php

					foreach ( $attributes as $attribute ) {

						if ( ! $attribute->get_variation() ) {
							continue;
						}

						$selected_value = isset( $default_attributes[ sanitize_title( $attribute->get_name() ) ] ) ? $default_attributes[ sanitize_title( $attribute->get_name() ) ] : '';

						?><select name="bundle_data[<?php echo $loop; ?>][default_variation_attributes][<?php echo sanitize_title( $attribute->get_name() ); ?>]" data-current="<?php echo esc_attr( $selected_value ); ?>">

							<option value=""><?php echo esc_html( sprintf( __( 'No default %s&hellip;', 'woocommerce' ), wc_attribute_label( $attribute->get_name() ) ) ); ?></option><?php

							if ( $attribute->is_taxonomy() ) {
								foreach ( $attribute->get_terms() as $option ) {
									?><option <?php selected( $selected_value, $option->slug ); ?> value="<?php echo esc_attr( $option->slug ); ?>"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option->name ) ); ?></option><?php
								}
							} else {
								foreach ( $attribute->get_options() as $option ) {
									?><option <?php selected( $selected_value, $option ); ?> value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ); ?></option><?php
								}
							}

						?></select><?php
					}

				?></div>
			</div><?php
		}

		$item_quantity     = isset( $item_data[ 'quantity_min' ] ) ? absint( $item_data[ 'quantity_min' ] ) : 1;
		$item_quantity_max = $item_quantity;

		if ( isset( $item_data[ 'quantity_max' ] ) ) {
			if ( '' !== $item_data[ 'quantity_max' ] ) {
				$item_quantity_max = absint( $item_data[ 'quantity_max' ] );
			} else {
				$item_quantity_max = '';
			}
		}

		$is_priced_individually  = isset( $item_data[ 'priced_individually' ] ) && 'yes' === $item_data[ 'priced_individually' ] ? 'yes' : '';
		$is_shipped_individually = isset( $item_data[ 'shipped_individually' ] ) && 'yes' === $item_data[ 'shipped_individually' ] ? 'yes' : '';
		$item_discount           = isset( $item_data[ 'discount' ] ) && (double) $item_data[ 'discount' ] > 0 ? $item_data[ 'discount' ] : '';
		$is_optional             = isset( $item_data[ 'optional' ] ) ? $item_data[ 'optional' ] : '';

		// When adding a subscription-type product for the first time, enable "Priced Individually" by default.
		if ( did_action( 'wp_ajax_woocommerce_add_bundled_product' ) && $bundled_product->is_type( array( 'subscription', 'variable-subscription' ) ) && ! isset( $item_data[ 'priced_individually' ] ) ) {
			$is_priced_individually = 'yes';
		}

		?><div class="optional">
			<div class="form-field optional">
				<label for="optional"><?php echo __( 'Optional', 'ultimatewoo-pro' ) ?></label>
				<input type="checkbox" class="checkbox"<?php echo ( 'yes' === $is_optional ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][optional]" <?php echo ( 'yes' === $is_optional ? 'value="1"' : '' ); ?>/>
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Check this option to mark the bundled product as optional.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>

		<div class="quantity_min">
			<div class="form-field">
				<label><?php echo __( 'Quantity Min', 'woocommerce' ); ?></label>
				<input type="number" class="item_quantity" size="6" name="bundle_data[<?php echo $loop; ?>][quantity_min]" value="<?php echo $item_quantity; ?>" step="any" min="0" />
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'The minimum/default quantity of this bundled product.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>

		<div class="quantity_max">
			<div class="form-field">
				<label><?php echo __( 'Quantity Max', 'ultimatewoo-pro' ); ?></label>
				<input type="number" class="item_quantity" size="6" name="bundle_data[<?php echo $loop; ?>][quantity_max]" value="<?php echo $item_quantity_max; ?>" step="any" min="0" />
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'The maximum quantity of this bundled product. Leave the field empty for an unlimited maximum quantity.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>

		<div class="shipped_individually">
			<div class="form-field">
				<label><?php echo __( 'Shipped Individually', 'woocommerce' ); ?></label>
				<input type="checkbox" class="checkbox"<?php echo ( 'yes' === $is_shipped_individually ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][shipped_individually]" <?php echo ( 'yes' === $is_shipped_individually ? 'value="1"' : '' ); ?>/>
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Check this option if this bundled item is shipped separately from the bundle.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>

		<div class="priced_individually">
			<div class="form-field">
				<label><?php echo __( 'Priced Individually', 'woocommerce' ); ?></label>
				<input type="checkbox" class="checkbox"<?php echo ( 'yes' === $is_priced_individually ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][priced_individually]" <?php echo ( 'yes' === $is_priced_individually ? 'value="1"' : '' ); ?>/>
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Check this option to have the price of this bundled item added to the base price of the bundle.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>

		<div class="discount" <?php echo 'yes' === $is_priced_individually ? '' : 'style="display:none;"'; ?>>
			<div class="form-field">
				<label><?php echo __( 'Discount %', 'woocommerce' ); ?></label>
				<input type="text" class="input-text item_discount wc_input_decimal" size="5" name="bundle_data[<?php echo $loop; ?>][discount]" value="<?php echo $item_discount; ?>" />
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Discount applied to the regular price of this bundled product when Priced Individually is checked. If a Discount is applied to a bundled product which has a sale price defined, the sale price will be overridden.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div><?php
	}

	/**
	 * Add bundled product "Advanced" tab content.
	 *
	 * @param  int    $loop
	 * @param  int    $product_id
	 * @param  array  $item_data
	 * @param  int    $post_id
	 * @return void
	 */
	public static function bundled_product_admin_advanced_html( $loop, $product_id, $item_data, $post_id ) {

		$is_priced_individually = isset( $item_data[ 'priced_individually' ] ) && 'yes' === $item_data[ 'priced_individually' ];
		$hide_thumbnail         = isset( $item_data[ 'hide_thumbnail' ] ) ? $item_data[ 'hide_thumbnail' ] : '';
		$override_title         = isset( $item_data[ 'override_title' ] ) ? $item_data[ 'override_title' ] : '';
		$override_description   = isset( $item_data[ 'override_description' ] ) ? $item_data[ 'override_description' ] : '';
		$visibility             = array(
			'product' => ! empty( $item_data[ 'single_product_visibility' ] ) && 'hidden' === $item_data[ 'single_product_visibility' ] ? 'hidden' : 'visible',
			'cart'    => ! empty( $item_data[ 'cart_visibility' ] ) && 'hidden' === $item_data[ 'cart_visibility' ] ? 'hidden' : 'visible',
			'order'   => ! empty( $item_data[ 'order_visibility' ] ) && 'hidden' === $item_data[ 'order_visibility' ] ? 'hidden' : 'visible',
		);
		$price_visibility       = array(
			'product' => ! empty( $item_data[ 'single_product_price_visibility' ] ) && 'hidden' === $item_data[ 'single_product_price_visibility' ] ? 'hidden' : 'visible',
			'cart'    => ! empty( $item_data[ 'cart_price_visibility' ] ) && 'hidden' === $item_data[ 'cart_price_visibility' ] ? 'hidden' : 'visible',
			'order'   => ! empty( $item_data[ 'order_price_visibility' ] ) && 'hidden' === $item_data[ 'order_price_visibility' ] ? 'hidden' : 'visible',
		);

		?><div class="item_visibility">
			<div class="form-field">
				<label for="item_visibility"><?php _e( 'Visibility', 'ultimatewoo-pro' ); ?></label>
				<div>
					<input type="checkbox" class="checkbox visibility_product"<?php echo ( 'visible' === $visibility[ 'product' ] ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][single_product_visibility]" <?php echo ( 'visible' === $visibility[ 'product' ] ? 'value="1"' : '' ); ?>/>
					<span class="labelspan"><?php _e( 'Product details', 'ultimatewoo-pro' ); ?></span>
					<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Controls the visibility of the bundled item in the single-product template of this bundle.', 'ultimatewoo-pro' ) ); ?>
				</div>
				<div>
					<input type="checkbox" class="checkbox visibility_cart"<?php echo ( 'visible' === $visibility[ 'cart' ] ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][cart_visibility]" <?php echo ( 'visible' === $visibility[ 'cart' ] ? 'value="1"' : '' ); ?>/>
					<span class="labelspan"><?php _e( 'Cart/checkout', 'ultimatewoo-pro' ); ?></span>
					<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Controls the visibility of the bundled item in cart/checkout templates.', 'ultimatewoo-pro' ) ); ?>
				</div>
				<div>
					<input type="checkbox" class="checkbox visibility_order"<?php echo ( 'visible' === $visibility[ 'order' ] ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][order_visibility]" <?php echo ( 'visible' === $visibility[ 'order' ] ? 'value="1"' : '' ); ?>/>
					<span class="labelspan"><?php _e( 'Order details', 'ultimatewoo-pro' ); ?></span>
					<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Controls the visibility of the bundled item in order details &amp; e-mail templates.', 'ultimatewoo-pro' ) ); ?>
				</div>
			</div>
		</div>

		<div class="price_visibility" <?php echo $is_priced_individually ? '' : 'style="display:none;"'; ?>>
			<div class="form-field">
				<label for="price_visibility"><?php _e( 'Price Visibility', 'ultimatewoo-pro' ); ?></label>
				<div class="price_visibility_product_wrapper">
					<input type="checkbox" class="checkbox price_visibility_product"<?php echo ( 'visible' === $price_visibility[ 'product' ] ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][single_product_price_visibility]" <?php echo ( 'visible' === $price_visibility[ 'product' ] ? 'value="1"' : '' ); ?>/>
					<span class="labelspan"><?php _e( 'Product details', 'ultimatewoo-pro' ); ?></span>
					<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Controls the visibility of the bundled-item price in the single-product template of this bundle.', 'ultimatewoo-pro' ) ); ?>
				</div>
				<div class="price_visibility_cart_wrapper">
					<input type="checkbox" class="checkbox price_visibility_cart"<?php echo ( 'visible' === $price_visibility[ 'cart' ] ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][cart_price_visibility]" <?php echo ( 'visible' === $price_visibility[ 'cart' ] ? 'value="1"' : '' ); ?>/>
					<span class="labelspan"><?php _e( 'Cart/checkout', 'ultimatewoo-pro' ); ?></span>
					<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Controls the visibility of the bundled-item price in cart/checkout templates.', 'ultimatewoo-pro' ) ); ?>
				</div>
				<div class="price_visibility_order_wrapper">
					<input type="checkbox" class="checkbox price_visibility_order"<?php echo ( 'visible' === $price_visibility[ 'order' ] ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][order_price_visibility]" <?php echo ( 'visible' === $price_visibility[ 'order' ] ? 'value="1"' : '' ); ?>/>
					<span class="labelspan"><?php _e( 'Order details', 'ultimatewoo-pro' ); ?></span>
					<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Controls the visibility of the bundled-item price in order details &amp; e-mail templates.', 'ultimatewoo-pro' ) ); ?>
				</div>
			</div>
		</div>

		<div class="hide_thumbnail">
			<div class="form-field">
				<label for="hide_thumbnail"><?php echo __( 'Hide Thumbnail', 'ultimatewoo-pro' ) ?></label>
				<input type="checkbox" class="checkbox"<?php echo ( 'yes' === $hide_thumbnail ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][hide_thumbnail]" <?php echo ( 'yes' === $hide_thumbnail ? 'value="1"' : '' ); ?>/>
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Check this option to hide the thumbnail image of this bundled product.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>

		<div class="override_title">
			<div class="form-field override_title">
				<label for="override_title"><?php echo __( 'Override Title', 'ultimatewoo-pro' ) ?></label>
				<input type="checkbox" class="checkbox"<?php echo ( 'yes' === $override_title ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][override_title]" <?php echo ( 'yes' === $override_title ? 'value="1"' : '' ); ?>/>
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Check this option to override the default product title.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>

		<div class="custom_title">
			<div class="form-field item_title"><?php

				$title = isset( $item_data[ 'title' ] ) ? $item_data[ 'title' ] : '';

				?><textarea name="bundle_data[<?php echo $loop; ?>][title]" placeholder="" rows="2" cols="20"><?php echo esc_textarea( $title ); ?></textarea>
			</div>
		</div>

		<div class="override_description">
			<div class="form-field">
				<label for="override_description"><?php echo __( 'Override Short Description', 'ultimatewoo-pro' ) ?></label>
				<input type="checkbox" class="checkbox"<?php echo ( 'yes' === $override_description ? ' checked="checked"' : '' ); ?> name="bundle_data[<?php echo $loop; ?>][override_description]" <?php echo ( 'yes' === $override_description ? 'value="1"' : '' ); ?>/>
				<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Check this option to override the default short product description.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>

		<div class="custom_description">
			<div class="form-field item_description"><?php

				$description = isset( $item_data[ 'description' ] ) ? $item_data[ 'description' ] : '';

				?><textarea name="bundle_data[<?php echo $loop; ?>][description]" placeholder="" rows="2" cols="20"><?php echo esc_textarea( $description ); ?></textarea>
			</div>
		</div><?php
	}

	/**
	 * Render main settings in 'woocommerce_bundled_products_admin_config' action.
	 */
	public static function bundled_products_admin_config() {

		global $product_bundle_object;

		$post_id = WC_PB_Core_Compatibility::get_id( $product_bundle_object );

		/*
		 * Layout options.
		 */

		?><div class="options_group"><?php
			woocommerce_wp_select( array(
				'id'          => '_wc_pb_layout_style',
				'value'       => $product_bundle_object->get_layout( 'edit' ),
				'label'       => __( 'Layout', 'ultimatewoo-pro' ),
				'description' => __( 'Select the <strong>Tabular</strong> option to have the thumbnails, descriptions and quantities of bundled products arranged in a table. Recommended for displaying multiple bundled products with configurable quantities.', 'ultimatewoo-pro' ),
				'desc_tip'    => true,
				'options'     => WC_Product_Bundle::get_supported_layouts()
			) );
		?></div><?php

		/*
		 * Bundled products options.
		 */

		$bundled_items = $product_bundle_object->get_bundled_items( 'edit' );
		$tabs          = self::get_bundled_product_tabs();
		$toggle        = 'closed';

		?><div class="options_group wc-metaboxes-wrapper wc-bundle-metaboxes-wrapper">

			<div id="wc-bundle-metaboxes-wrapper-inner">

				<p class="toolbar">
					<span class="disabler"></span>
					<a href="#" class="close_all"><?php _e( 'Close all', 'woocommerce' ); ?></a>
					<a href="#" class="expand_all"><?php _e( 'Expand all', 'woocommerce' ); ?></a>
				</p>

				<div class="wc-bundled-items wc-metaboxes"><?php

					if ( ! empty( $bundled_items ) ) {

						$loop = 0;

						foreach ( $bundled_items as $item_id => $item ) {

							$item_availability           = '';
							$item_data                   = $item->get_data();
							$item_data[ 'bundled_item' ] = $item;

							if ( false === $item->is_in_stock() ) {
								if ( $item->product->is_in_stock() ) {
									$item_availability = '<mark class="outofstock insufficient_stock">' . __( 'Insufficient stock', 'ultimatewoo-pro' ) . '</mark>';
								} else {
									$item_availability = '<mark class="outofstock">' . __( 'Out of stock', 'woocommerce' ) . '</mark>';
								}
							}

							$product_id = $item->product_id;
							$title      = $item->product->get_title();
							$sku        = $item->product->get_sku();
							$title      = WC_PB_Helpers::format_product_title( $title, $sku, '', true );
							$title      = sprintf( _x( '#%1$s: %2$s', 'bundled product admin title', 'ultimatewoo-pro' ), $product_id, $title );

							include( 'views/html-bundled-product-admin.php' );

							$loop++;
						}
					}
				?></div>
			</div>
		</div>
		<div class="add_bundled_product form-field">
			<span class="add_prompt"></span>
			<select class="wc-product-search" id="bundled_product" style="width: 250px;" name="bundled_product" data-placeholder="<?php _e( 'Add a bundled product&hellip;', 'ultimatewoo-pro' ); ?>" data-action="woocommerce_json_search_products" multiple="multiple" data-limit="1000">
				<option></option>
			</select>
			<?php echo WC_PB_Core_Compatibility::wc_help_tip( __( 'Search for a product and add it to this bundle by clicking its name in the results list.', 'ultimatewoo-pro' ) ); ?>
		</div><?php
	}

	/**
	 * Handles getting bundled product meta box tabs - @see bundled_product_admin_html.
	 *
	 * @return array
	 */
	public static function get_bundled_product_tabs() {

		/**
		 * 'woocommerce_bundled_product_admin_html_tabs' filter.
		 * Use this to add bundled product admin settings tabs
		 *
		 * @param  array  $tab_data
		 */
		return apply_filters( 'woocommerce_bundled_product_admin_html_tabs', array(
			array(
				'id'    => 'config',
				'title' => __( 'Basic Settings', 'ultimatewoo-pro' ),
			),
			array(
				'id'    => 'advanced',
				'title' => __( 'Advanced Settings', 'ultimatewoo-pro' ),
			)
		) );
	}

	/**
	 * Add admin notices.
	 *
	 * @param  string  $content
	 * @param  string  $type
	 */
	public static function add_admin_notice( $content, $type ) {

		WC_PB_Admin_Notices::add_notice( $content, $type, true );
	}

	/**
	 * Add admin errors.
	 *
	 * @param  string  $error
	 * @return string
	 */
	public static function add_admin_error( $error ) {

		self::add_admin_notice( $error, 'error' );
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	public static function build_bundle_config( $post_id, $posted_bundle_data ) {
		_deprecated_function( __METHOD__ . '()', '4.11.7', __CLASS__ . '::process_posted_bundle_data()' );
		return self::process_posted_bundle_data( $posted_bundle_data, $post_id );
	}
}

WC_PB_Meta_Box_Product_Data::init();
