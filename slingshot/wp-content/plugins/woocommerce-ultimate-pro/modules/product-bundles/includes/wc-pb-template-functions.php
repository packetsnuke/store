<?php
/**
 * Product Bundles template functions
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    4.11.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*--------------------------------------------------------*/
/*  Product Bundles single product template functions     */
/*--------------------------------------------------------*/

/**
 * Add-to-cart template for Product Bundles.
 */
function wc_pb_template_add_to_cart() {

	global $product, $post;

	// Enqueue variation scripts.
	wp_enqueue_script( 'wc-add-to-cart-bundle' );

	wp_enqueue_style( 'wc-bundle-css' );

	$bundled_items = $product->get_bundled_items();

	if ( ! empty( $bundled_items ) ) {
		wc_get_template( 'single-product/add-to-cart/bundle.php', array(
			'availability_html' => WC_PB_Core_Compatibility::wc_get_stock_html( $product ),
			'bundle_price_data' => $product->get_bundle_price_data(),
			'bundled_items'     => $bundled_items,
			'product'           => $product,
			'product_id'        => WC_PB_Core_Compatibility::get_id( $product )
		), false, WC_PB()->plugin_path() . '/templates/' );
	}
}

/**
 * Add-to-cart fields for Product Bundles.
 */
function wc_pb_template_add_to_cart_button() {

	if ( isset( $_GET[ 'update-bundle' ] ) ) {
		$updating_cart_key = wc_clean( $_GET[ 'update-bundle' ] );
		if ( isset( WC()->cart->cart_contents[ $updating_cart_key ] ) ) {
			echo '<input type="hidden" name="update-bundle" value="' . $updating_cart_key . '" />';
		}
	}

	wc_get_template( 'single-product/add-to-cart/bundle-quantity-input.php', array(), false, WC_PB()->plugin_path() . '/templates/' );
	wc_get_template( 'single-product/add-to-cart/bundle-button.php', array(), false, WC_PB()->plugin_path() . '/templates/' );
}

/**
 * Load the bundled item title template.
 *
 * @param  WC_Bundled_Item    $bundled_item
 * @param  WC_Product_Bundle  $bundle
 */
function wc_pb_template_bundled_item_title( $bundled_item, $bundle ) {

	$min_qty = $bundled_item->get_quantity();
	$max_qty = $bundled_item->get_quantity( 'max' );

	$qty     = $min_qty > 1 && $min_qty === $max_qty ? $min_qty : '';

	wc_get_template( 'single-product/bundled-item-title.php', array(
		'quantity'     => $qty,
		'title'        => $bundled_item->get_title(),
		'optional'     => $bundled_item->is_optional(),
		'bundled_item' => $bundled_item,
		'bundle'       => $bundle
	), false, WC_PB()->plugin_path() . '/templates/' );
}

/**
 * Load the bundled item thumbnail template.
 *
 * @param  WC_Bundled_Item    $bundled_item
 * @param  WC_Product_Bundle  $bundle
 */
function wc_pb_template_bundled_item_thumbnail( $bundled_item, $bundle ) {

	$layout     = $bundle->get_layout();
	$product_id = $bundled_item->product_id;

	if ( 'tabular' === $layout ) {
		echo '<td class="bundled_item_col bundled_item_images_col">';
	}

	if ( $bundled_item->is_visible() ) {
		if ( $bundled_item->is_thumbnail_visible() ) {

			/**
			 * 'woocommerce_bundled_product_gallery_classes' filter.
			 *
			 * @param  array            $classes
			 * @param  WC_Bundled_Item  $bundled_item
			 */
			$gallery_classes = apply_filters( 'woocommerce_bundled_product_gallery_classes', array( 'bundled_product_images' ), $bundled_item );

			wc_get_template( 'single-product/bundled-item-image.php', array(
				'post_id'         => $product_id,
				'product_id'      => $product_id,
				'bundled_item'    => $bundled_item,
				'gallery_classes' => $gallery_classes,
				'image_rel'       => WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ? 'photoSwipe' : 'prettyPhoto'
			), false, WC_PB()->plugin_path() . '/templates/' );
		}
	}

	if ( 'tabular' === $layout ) {
		echo '</td>';
	}
}

/**
 * Load the bundled item short description template.
 *
 * @param  WC_Bundled_Item    $bundled_item
 * @param  WC_Product_Bundle  $bundle
 */
function wc_pb_template_bundled_item_description( $bundled_item, $bundle ) {

	wc_get_template( 'single-product/bundled-item-description.php', array(
		'description' => $bundled_item->get_description()
	), false, WC_PB()->plugin_path() . '/templates/' );
}

/**
 * Adds the 'bundled_product' container div.
 *
 * @param  WC_Bundled_Item    $bundled_item
 * @param  WC_Product_Bundle  $bundle
 */
function wc_pb_template_bundled_item_details_wrapper_open( $bundled_item, $bundle ) {

	$layout = $bundle->get_layout();

	if ( 'default' === $layout ) {
		$el = 'div';
	} elseif ( 'tabular' === $layout ) {
		$el = 'tr';
	}

	$classes = $bundled_item->get_classes();
	$style   = $bundled_item->is_visible() ? '' : ' style="display:none;"';

	echo '<' . $el . ' class="bundled_product bundled_product_summary product ' . $classes . '"' . $style . ' >';
}

/**
 * Adds a qty input column when using the tabular template.
 *
 * @param  WC_Bundled_Item    $bundled_item
 * @param  WC_Product_Bundle  $bundle
 */
function wc_pb_template_tabular_bundled_item_qty( $bundled_item, $bundle ) {

	$layout = $bundle->get_layout();

	if ( 'tabular' === $layout ) {

		/** Documented in 'WC_PB_Cart::get_posted_bundle_configuration'. */
		$bundle_fields_prefix = apply_filters( 'woocommerce_product_bundle_field_prefix', '', WC_PB_Core_Compatibility::get_id( $bundle ) );

		$quantity_min = $bundled_item->get_quantity();
		$quantity_max = $bundled_item->get_quantity( 'max', true );
		$input_name   = $bundle_fields_prefix . 'bundle_quantity_' . $bundled_item->item_id;
		$hide_input   = $quantity_min === $quantity_max || false === $bundled_item->is_in_stock();

		echo '<td class="bundled_item_col bundled_item_qty_col">';

		wc_get_template( 'single-product/bundled-item-quantity.php', array(
			'bundled_item'         => $bundled_item,
			'quantity_min'         => $quantity_min,
			'quantity_max'         => $quantity_max,
			'input_name'           => $input_name,
			'layout'               => $layout,
			'hide_input'           => $hide_input,
			'bundle_fields_prefix' => $bundle_fields_prefix
		), false, WC_PB()->plugin_path() . '/templates/' );
		echo '</td>';
	}
}

/**
 * Adds a qty input column when using the default template.
 *
 * @param  WC_Bundled_Item  $bundled_item
 */
function wc_pb_template_default_bundled_item_qty( $bundled_item ) {

	$bundle = $bundled_item->get_bundle();
	$layout = $bundle->get_layout();

	if ( 'default' === $layout ) {

		/** Documented in 'WC_PB_Cart::get_posted_bundle_configuration'. */
		$bundle_fields_prefix = apply_filters( 'woocommerce_product_bundle_field_prefix', '', WC_PB_Core_Compatibility::get_id( $bundle ) );

		$quantity_min = $bundled_item->get_quantity();
		$quantity_max = $bundled_item->get_quantity( 'max', true );
		$input_name   = $bundle_fields_prefix . 'bundle_quantity_' . $bundled_item->item_id;
		$hide_input   = $quantity_min === $quantity_max || false === $bundled_item->is_in_stock();

		wc_get_template( 'single-product/bundled-item-quantity.php', array(
			'bundled_item'         => $bundled_item,
			'quantity_min'         => $quantity_min,
			'quantity_max'         => $quantity_max,
			'input_name'           => $input_name,
			'layout'               => $layout,
			'hide_input'           => $hide_input,
			'bundle_fields_prefix' => $bundle_fields_prefix
		), false, WC_PB()->plugin_path() . '/templates/' );
	}
}


/**
 * Close the 'bundled_product' container div.
 *
 * @param  WC_Bundled_Item    $bundled_item
 * @param  WC_Product_Bundle  $bundle
 */
function wc_pb_template_bundled_item_details_wrapper_close( $bundled_item, $bundle ) {

	$layout = $bundle->get_layout();

	if ( 'default' === $layout ) {
		$el = 'div';
	} elseif ( 'tabular' === $layout ) {
		$el = 'tr';
	}

	echo '</' . $el . '>';
}

/**
 * Add a 'details' container div.
 *
 * @param  WC_Bundled_Item    $bundled_item
 * @param  WC_Product_Bundle  $bundle
 */
function wc_pb_template_bundled_item_details_open( $bundled_item, $bundle ) {

	$layout = $bundle->get_layout();

	if ( 'tabular' === $layout ) {
		echo '<td class="bundled_item_col bundled_item_details_col">';
	}

	echo '<div class="details">';
}

/**
 * Close the 'details' container div.
 *
 * @param  WC_Bundled_Item    $bundled_item
 * @param  WC_Product_Bundle  $bundle
 */
function wc_pb_template_bundled_item_details_close( $bundled_item, $bundle ) {

	$layout = $bundle->get_layout();

	echo '</div>';

	if ( 'tabular' === $layout ) {
		echo '</td>';
	}
}

/**
 * Display bundled product details templates.
 *
 * @param  WC_Bundled_Item    $bundled_item
 * @param  WC_Product_Bundle  $bundle
 */
function wc_pb_template_bundled_item_product_details( $bundled_item, $bundle ) {

	if ( $bundled_item->is_purchasable() ) {

		$bundle_id          = WC_PB_Core_Compatibility::get_id( $bundle );
		$bundled_product    = $bundled_item->product;
		$bundled_product_id = WC_PB_Core_Compatibility::get_id( $bundled_product );
		$availability       = $bundled_item->get_availability();

		/** Documented in 'WC_PB_Cart::get_posted_bundle_configuration'. */
		$bundle_fields_prefix = apply_filters( 'woocommerce_product_bundle_field_prefix', '', $bundle_id );

		$bundled_item->add_price_filters();

		if ( $bundled_item->is_optional() ) {

			// Optional checkbox template.
			wc_get_template( 'single-product/bundled-item-optional.php', array(
				'quantity'             => $bundled_item->get_quantity(),
				'bundled_item'         => $bundled_item,
				'bundle_fields_prefix' => $bundle_fields_prefix
			), false, WC_PB()->plugin_path() . '/templates/' );
		}

		if ( $bundled_product->get_type() === 'simple' || $bundled_product->get_type() === 'subscription' ) {

			// Simple Product template.
			wc_get_template( 'single-product/bundled-product-simple.php', array(
				'bundled_product_id'   => $bundled_product_id,
				'bundled_product'      => $bundled_product,
				'bundled_item'         => $bundled_item,
				'bundle_id'            => $bundle_id,
				'bundle'               => $bundle,
				'bundle_fields_prefix' => $bundle_fields_prefix,
				'availability'         => $availability
			), false, WC_PB()->plugin_path() . '/templates/' );

		} elseif ( $bundled_product->get_type() === 'variable' || $bundled_product->get_type() === 'variable-subscription' ) {

			$do_ajax                       = $bundled_item->use_ajax_for_product_variations();
			$variations                    = $do_ajax ? false : $bundled_item->get_product_variations();
			$variation_attributes          = $bundled_item->get_product_variation_attributes();
			$selected_variation_attributes = $bundled_item->get_selected_product_variation_attributes();

			if ( ! $do_ajax && empty( $variations ) ) {
				echo '<p class="bundled_item_unavailable">' . __( 'This item is not available at the moment.', 'ultimatewoo-pro' ) . '</p>';
			} else {

				// Variable Product template.
				wc_get_template( 'single-product/bundled-product-variable.php', array(
					'bundled_product_id'                  => $bundled_product_id,
					'bundled_product'                     => $bundled_product,
					'bundled_item'                        => $bundled_item,
					'bundle_id'                           => $bundle_id,
					'bundle'                              => $bundle,
					'bundle_fields_prefix'                => $bundle_fields_prefix,
					'availability'                        => $availability,
					'bundled_product_attributes'          => $variation_attributes,
					'bundled_product_variations'          => $variations,
					'bundled_product_selected_attributes' => $selected_variation_attributes,
					'custom_product_data'                 => array(
						'bundle_id'       => $bundle_id,
						'bundled_item_id' => $bundled_item->item_id
					)
				), false, WC_PB()->plugin_path() . '/templates/' );
			}
		}

		$bundled_item->remove_price_filters();

	} else {
		echo __( 'This item is not available at the moment.', 'ultimatewoo-pro' );
	}
}

/**
 * Bundled variation template.
 *
 * @param  int              $product_id
 * @param  WC_Bundled_Item  $bundled_item
 */
function wc_pb_template_single_variation( $product_id, $bundled_item ) {

	wc_get_template( 'single-product/bundled-variation.php', array(
		'bundled_item'         => $bundled_item,
		'bundle_fields_prefix' => apply_filters( 'woocommerce_product_bundle_field_prefix', '', $bundled_item->bundle_id ) // Filter documented in 'WC_PB_Cart::get_posted_bundle_configuration'.
	), false, WC_PB()->plugin_path() . '/templates/' );
}

/**
 * Echo opening tabular markup if necessary.
 *
 * @param  WC_Product_Bundle  $bundle
 */
function wc_pb_template_before_bundled_items( $bundle ) {

	$layout = $bundle->get_layout();

	if ( 'tabular' === $layout ) {

		?><table cellspacing="0" class="bundled_products">
			<thead>
				<th class="bundled_item_col bundled_item_images_head"></th>
				<th class="bundled_item_col bundled_item_details_head"><?php _e( 'Product', 'ultimatewoo-pro' ); ?></th>
				<th class="bundled_item_col bundled_item_qty_head"><?php _e( 'Quantity', 'ultimatewoo-pro' ); ?></th>
			</thead>
			<tbody><?php
	}
}

/**
 * Echo closing tabular markup if necessary.
 *
 * @param  WC_Product_Bundle  $bundle
 */
function wc_pb_template_after_bundled_items( $bundle ) {

	$layout = $bundle->get_layout();

	if ( 'tabular' === $layout ) {
		echo '</tbody></table>';
	}
}

/**
 * Display bundled product attributes.
 *
 * @param  WC_Product  $product
 */
function wc_pb_template_bundled_item_attributes( $product ) {

	if ( $product->is_type( 'bundle' ) ) {

		$bundled_items = $product->get_bundled_items();

		if ( ! empty( $bundled_items ) ) {

			foreach ( $bundled_items as $bundled_item ) {

				/** Documented in 'WC_Product_Bundle::has_attributes()'. */
				$show_bundled_product_attributes = apply_filters( 'woocommerce_bundle_show_bundled_product_attributes', $bundled_item->is_visible(), $product, $bundled_item );

				if ( ! $show_bundled_product_attributes ) {
					continue;
				}

				$bundled_product = $bundled_item->product;

				if ( $bundled_product->has_attributes() ) {

					// Filter bundled item attributes based on active variation filters.
					add_filter( 'woocommerce_attribute', array( $bundled_item, 'filter_bundled_item_attribute' ), 10, 3 );

					wc_get_template( 'single-product/bundled-item-attributes.php', array(
						'title'              => $bundled_item->get_title(),
						'product'            => $bundled_product,
						'attributes'         => array_filter( $bundled_product->get_attributes(), 'wc_attributes_array_filter_visible' ),
						'display_dimensions' => $bundled_item->is_shipped_individually() && apply_filters( 'wc_product_enable_dimensions_display', $product->has_weight() || $product->has_dimensions() )
					), false, WC_PB()->plugin_path() . '/templates/' );

					remove_filter( 'woocommerce_attribute', array( $bundled_item, 'filter_bundled_item_attribute' ), 10, 3 );
				}
			}
		}
	}
}

/**
 * Variation attribute options for bundled items. If:
 *
 * - only a single variation is active,
 * - all attributes have a defined value, and
 * - the single values are actually selected as defaults,
 *
 * ...then wrap the dropdown in a hidden div and show the single attribute value description before it.
 *
 * @param  array  $args
 */
function wc_pb_template_bundled_variation_attribute_options( $args ) {

	$bundled_item                = $args[ 'bundled_item' ];
	$variation_attribute_name    = $args[ 'attribute' ];
	$variation_attribute_options = $args[ 'options' ];

	/** Documented in 'WC_PB_Cart::get_posted_bundle_configuration'. */
	$bundle_fields_prefix = apply_filters( 'woocommerce_product_bundle_field_prefix', '', $bundled_item->bundle_id );

	// The currently selected attribute option.
	$selected_option = isset( $_REQUEST[ $bundle_fields_prefix . 'bundle_attribute_' . sanitize_title( $variation_attribute_name ) . '_' . $bundled_item->item_id ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ $bundle_fields_prefix . 'bundle_attribute_' . sanitize_title( $variation_attribute_name ) . '_' . $bundled_item->item_id ] ) ) ) : $bundled_item->get_selected_product_variation_attribute( $variation_attribute_name );

	$variation_attributes      = $bundled_item->get_product_variation_attributes();
	$variation_attribute_value = '';
	$hide_dropdowns            = false;
	$html                      = '';

	// Find if all attributes only have a single attribute option to display - if yes, hide the dropdown of the current attribute and show a simple label.
	if ( $bundled_item->has_filtered_variations() ) {

		$variations = $bundled_item->get_children();

		// Hide the dropdown only when a single variation is active.
		if ( 1 === sizeof( $variations ) ) {

			$variation_id   = current( $variations );
			$variation      = wc_get_product( $variation_id );
			$variation_data = $variation->get_variation_attributes();

			if ( isset( $variation_data[ WC_PB_Core_Compatibility::wc_variation_attribute_name( $variation_attribute_name ) ] ) ) {
				$variation_attribute_value = $variation_data[ WC_PB_Core_Compatibility::wc_variation_attribute_name( $variation_attribute_name ) ];
			}

			$hide_dropdowns = true;

			// Make sure all attributes of the single active variation have a value.
			foreach ( $variation_attributes as $attribute_name => $options ) {
				if ( isset( $variation_data[ WC_PB_Core_Compatibility::wc_variation_attribute_name( $attribute_name ) ] ) ) {
					$value = $variation_data[ WC_PB_Core_Compatibility::wc_variation_attribute_name( $attribute_name ) ];
					if ( '' === $value || '' === $bundled_item->get_selected_product_variation_attribute( $attribute_name ) ) {
						$hide_dropdowns = false;
						break;
					}
				}
			}
		}
	}

	// Fill required args.
	$args[ 'selected' ] = $selected_option;
	$args[ 'name' ]     = $bundle_fields_prefix . 'bundle_attribute_' . sanitize_title( $variation_attribute_name ) . '_' . $bundled_item->item_id;
	$args[ 'product' ]  = $bundled_item->product;

	/**
	 * 'woocommerce_force_show_bundled_dropdown_variation_attribute_options' filter.
	 *
	 * @param  boolean  $force_show
	 * @param  array    $args
	 */
	$force_show_dropdown = apply_filters( 'woocommerce_force_show_bundled_dropdown_variation_attribute_options', false, $args );

	// Render everything.
	if ( $hide_dropdowns && false === $force_show_dropdown ) {

		// Get the singular option description.
		if ( taxonomy_exists( $variation_attribute_name ) ) {

			// Get terms if this is a taxonomy.
			$terms = wc_get_product_terms( $bundled_item->product_id, $variation_attribute_name, array( 'fields' => 'all' ) );

			foreach ( $terms as $term ) {
				if ( $term->slug === $variation_attribute_value ) {
					// Found: Add it to the html and break.
					$html .= esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) );
					break;
				}
			}
		} else {
			foreach ( $variation_attribute_options as $option ) {

				if ( sanitize_title( $variation_attribute_value ) === $variation_attribute_value ) {
					$singular_found = $variation_attribute_value === sanitize_title( $option );
				} else {
					$singular_found = $variation_attribute_value === $option;
				}

				if ( $singular_found ) {
					// Found: Add it to the html and break.
					$html .= esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) );
					break;
				}
			}
		}

		// See https://github.com/woothemes/woocommerce/pull/11944 .
		if ( WC_PB_Core_Compatibility::is_wc_version_gte_2_7() ) {
			$args[ 'show_option_none' ] = false;
		}

		// Get the dropdowns markup.
		ob_start();
		wc_dropdown_variation_attribute_options( $args );
		$attribute_options = ob_get_clean();

		// Add the dropdown (hidden).
		$html .= '<div class="bundled_variation_attribute_options_wrapper" style="display:none;">' . $attribute_options . '</div>';

	} else {

		// Get the dropdowns markup.
		ob_start();
		wc_dropdown_variation_attribute_options( $args );
		$attribute_options = ob_get_clean();

		// Just render the dropdown.
		$html .= $attribute_options;

		$variation_attribute_keys = array_keys( $variation_attributes );

		// ...and add the reset-variations link.
		if ( end( $variation_attribute_keys ) === $variation_attribute_name ) {
			$html .= apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . __( 'Clear', 'woocommerce' ) . '</a>' );
		}
	}

	return $html;
}
