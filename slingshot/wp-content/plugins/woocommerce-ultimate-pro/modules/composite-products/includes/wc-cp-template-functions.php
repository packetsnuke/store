<?php
/**
 * Composite Product Template Functions
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Composite Products
 * @since    3.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*---------------------------------------------------------*/
/*                                                         */
/*  Composite products single product template functions.  */
/*                                                         */
/*---------------------------------------------------------*/

/**
 * Add-to-cart template for composite products.
 */
function wc_cp_add_to_cart() {

	global $product;

	// Enqueue scripts.
	wp_enqueue_script( 'wc-add-to-cart-composite' );

	// Enqueue styles.
	wp_enqueue_style( 'wc-composite-single-css' );

	// Load NYP scripts.
	if ( function_exists( 'WC_Name_Your_Price' ) ) {
		WC_Name_Your_Price()->display->nyp_scripts();
	}

	// Enqueue Bundle styles.
	if ( class_exists( 'WC_Bundles' ) ) {
		wp_enqueue_style( 'wc-bundle-css' );
	}

	$navigation_style           = $product->get_composite_layout_style();
	$navigation_style_variation = $product->get_composite_layout_style_variation();
	$components                 = $product->get_components();

	if ( ! empty( $components ) ) {
		wc_get_template( 'single-product/add-to-cart/composite.php', array(
			'navigation_style' => $navigation_style,
			'classes'          => implode( ' ', apply_filters( 'woocommerce_composite_form_classes', array( $navigation_style, $navigation_style_variation ), $product ) ),
			'components'       => $components,
			'product'          => $product
		), '', WC_CP()->plugin_path() . '/templates/' );
	}
}

/**
 * Add-to-cart button and quantity template for composite products.
 */
function wc_cp_add_to_cart_button() {

	if ( isset( $_GET[ 'update-composite' ] ) ) {
		$cart_id = wc_clean( $_GET[ 'update-composite' ] );
		echo '<input type="hidden" name="update-composite" value="' . $cart_id . '" />';
	}

	wc_get_template( 'single-product/add-to-cart/composite-quantity-input.php', array(), false, WC_CP()->plugin_path() . '/templates/' );
	wc_get_template( 'single-product/add-to-cart/composite-button.php', array(), false, WC_CP()->plugin_path() . '/templates/' );
}


/*-----------------------------------------------------------------------------*/
/*                                                                             */
/*  Composite products single product summary widget functions.                */
/*                                                                             */
/*-----------------------------------------------------------------------------*/

/**
 * Summary widget content.
 *
 * @since  3.6.0
 *
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_summary_widget_content( $components, $composite ) {

	?><div class="widget_composite_summary_elements cp_clearfix"><?php
		wc_get_template( 'single-product/composite-summary-content.php', array(
			'summary_columns'  => 1,
			'summary_elements' => count( $components ),
			'components'       => $components,
			'product'          => $composite
		), '', WC_CP()->plugin_path() . '/templates/' );
	?></div><?php
}

/**
 * Summary widget price. Empty element to be populated by the script.
 *
 * @since  3.6.0
 *
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_summary_widget_price( $components, $composite ) {

	?><div class="widget_composite_summary_price cp_clearfix">
		<div class="composite_price"></div>
	</div><?php
}

/**
 * Summary widget validation message. Empty element to be populated by the script.
 *
 * @since  3.6.0
 *
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_summary_widget_message( $components, $composite ) {

	?><div class="widget_composite_summary_error cp_clearfix">
		<div class="composite_message" style="display:none;"><ul class="msg woocommerce-info"></ul></div>
	</div><?php
}

/**
 * Summary widget product availability.
 *
 * @since  3.6.0
 *
 * @param  WC_Product_Composite  $product
 */
function wc_cp_summary_widget_availability( $components, $product ) {

	?><div class="widget_composite_summary_availability cp_clearfix">
		<div class="composite_availability"><?php
			echo WC_CP_Core_Compatibility::wc_get_stock_html( $product );
		?></div>
	</div><?php
}

/**
 * Summary widget add-to-cart button.
 *
 * @since  3.6.0
 *
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_summary_widget_button( $components, $composite ) {

	?><div class="widget_composite_summary_button cp_clearfix">
		<div class="composite_button">
			<?php do_action( 'woocommerce_composite_add_to_cart_button' ); ?>
		</div>
	</div><?php
}


/*-----------------------------------------------------------------------------*/
/*                                                                             */
/*  Composite products single product template functions - component options.  */
/*                                                                             */
/*-----------------------------------------------------------------------------*/

/**
 * In progressive mode, wrap component options & sorting/filtering controls in a blockable div.
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $product
 */
function wc_cp_add_progressive_mode_block_wrapper_end( $component_id, $product ) {

	?></div><?php
}

/**
 * In progressive mode, wrap component options & sorting/filtering controls in a blockable div.
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $product
 */
function wc_cp_add_progressive_mode_block_wrapper_start( $component_id, $product ) {

	?><div class="component_selections_inner">
		<div class="block_component_selections_inner"></div><?php
}

/**
 * Show current selection details in non-paged modes.
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $product
 * @return void
 */
function wc_cp_add_current_selection_details( $component_id, $product ) {

	$selected_option  = $product->get_component( $component_id )->view->get_selected_option();
	$navigation_style = $product->get_composite_layout_style();

	?><div class="component_content" data-product_id="<?php echo $component_id; ?>">
		<div class="component_summary cp_clearfix"><?php

			/**
			 * Action 'woocommerce_composite_component_before_summary_content_paged'.
			 *
			 * @since  3.4.0
			 *
			 * @param  string                $component_id
			 * @param  WC_Product_Composite  $product
			 *
			 * @hooked wc_cp_component_summary_message - 10
			 */
			do_action( 'woocommerce_composite_component_before_summary_content_' . $navigation_style, $component_id, $product );

			?><div class="product content summary_content"><?php
				echo WC_CP()->display->show_composited_product( $selected_option, $component_id, $product );
			?></div>
		</div>
	</div><?php
}

/**
 * Show current selection details in paged modes -- added before component options when viewed as thumbnails.
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $product
 */
function wc_cp_add_current_selection_details_paged_top( $component_id, $product ) {

	$options_style = $product->get_component( $component_id )->get_options_style();

	if ( 'thumbnails' === $options_style ) {
		wc_cp_add_current_selection_details( $component_id, $product );
	}
}

/**
 * Show current selection details in paged modes -- added after component options when viewed as thumbnails.
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $product
 */
function wc_cp_add_current_selection_details_paged_bottom( $component_id, $product ) {

	$options_style = $product->get_component( $component_id )->get_options_style();

	if ( 'dropdowns' === $options_style || 'radios' === $options_style ) {
		wc_cp_add_current_selection_details( $component_id, $product );
	}
}

/**
 * Show component options pagination.
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $product
 */
function wc_cp_add_component_options_pagination( $component_id, $product ) {

	$component = $product->get_component( $component_id );

	if ( WC_CP_Component::options_style_supports( $component->get_options_style(), 'pagination' ) && ! $component->is_static() ) {

		// Component Options Pagination template.
		wc_get_template( 'single-product/component-options-pagination.php', array(
			'product'         => $product,
			'component_id'    => $component_id,
			'component'       => $component,
			'pagination_data' => $component->view->get_pagination_data(),
			'has_pages'       => $component->view->has_pages(),
			'append_options'  => false === $component->paginate_options()
		), '', WC_CP()->plugin_path() . '/templates/' );
	}
}

/**
 * Show component options.
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $product
 */
function wc_cp_add_component_options( $component_id, $product ) {

	$component = $product->get_component( $component_id );

	// Component Options template.
	wc_get_template( 'single-product/component-options.php', array(
		'product'                => $product,
		'component_id'           => $component_id,
		'component'              => $component,
		'options_style'          => $component->get_options_style(),
		'component_options_data' => $component->view->get_options_data()
	), '', WC_CP()->plugin_path() . '/templates/' );
}

/**
 * Show component options as a dropdown.
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $product
 */
function wc_cp_component_options_dropdown( $component_id, $product ) {

	$component = $product->get_component( $component_id );

	// Dropdown template.
	wc_get_template( 'single-product/component-options-dropdown.php', array(
		'product'       => $product,
		'component_id'  => $component_id,
		'component'     => $component,
		'hide_dropdown' => 'dropdowns' !== $component->get_options_style()
	), '', WC_CP()->plugin_path() . '/templates/' );
}

/**
 * Show component options as thumbnails.
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $product
 */
function wc_cp_component_options_thumbnails( $component_id, $product ) {

	$component = $product->get_component( $component_id );

	// Thumbnails template.
	wc_get_template( 'single-product/component-options-thumbnails.php', array(
		'product'           => $product,
		'component_id'      => $component_id,
		'component'         => $component,
		'component_data'    => $component->get_data(),
		'quantity_min'      => $component->get_quantity( 'min' ),
		'quantity_max'      => $component->get_quantity( 'max' ),
		'thumbnail_columns' => $component->get_columns(),
		'component_options' => $component->view->get_options(),
		'selected_option'   => $component->view->get_selected_option(),
	), '', WC_CP()->plugin_path() . '/templates/' );

	wc_cp_component_options_dropdown( $component_id, $product );
}

/**
 * Show component option as radio buttons.
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $product
 */
function wc_cp_component_options_radios( $component_id, $product ) {

	$component = $product->get_component( $component_id );

	// Radio buttons template.
	wc_get_template( 'single-product/component-options-radio-buttons.php', array(
		'product'           => $product,
		'component_id'      => $component_id,
		'component'         => $component,
		'component_data'    => $component->get_data(),
		'quantity_min'      => $component->get_quantity( 'min' ),
		'quantity_max'      => $component->get_quantity( 'max' ),
		'component_options' => $component->view->get_options(),
		'selected_option'   => $component->view->get_selected_option()
	), '', WC_CP()->plugin_path() . '/templates/' );

	wc_cp_component_options_dropdown( $component_id, $product );
}

/**
 * Add sorting input.
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $product
 */
function wc_cp_add_sorting( $component_id, $product ) {

	$component_ordering_options = $product->get_component( $component_id )->get_sorting_options();

	if ( $component_ordering_options ) {

		// Component Options sorting template.
		wc_get_template( 'single-product/component-options-orderby.php', array(
			'product'                    => $product,
			'component_id'               => $component_id,
			'component_ordering_options' => $component_ordering_options,
			'orderby'                    => $product->get_component( $component_id )->get_default_sorting_order()
		), '', WC_CP()->plugin_path() . '/templates/' );
	}
}

/**
 * Add attribute filters.
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $product
 */
function wc_cp_add_filtering( $component_id, $product ) {

	$component_filtering_options = $product->get_component( $component_id )->get_filtering_options();

	if ( $component_filtering_options ) {

		// Component Options filtering template.
		wc_get_template( 'single-product/component-options-filters.php', array(
			'product'                     => $product,
			'component_id'                => $component_id,
			'component_filtering_options' => $component_filtering_options
		), '', WC_CP()->plugin_path() . '/templates/' );
	}
}


/*----------------------------------------------------------------------------------*/
/*                                                                                  */
/*  Composite products single product template functions - layout.                  */
/*                                                                                  */
/*----------------------------------------------------------------------------------*/

/**
 * Add Composite Summary on the 'woocommerce_before_add_to_cart_button' hook.
 */
function wc_cp_before_add_to_cart_button() {

	global $product;

	if ( 'composite' === $product->get_type() ) {
		wc_cp_add_summary( $product->get_components(), $product );
	}
}

/**
 * Add Review/Summary with current configuration details.
 * The Summary template must be loaded if the summary widget is active.
 *
 * @param  array                 $components
 * @param  WC_Product_Composite  $product
 */
function wc_cp_add_summary( $components, $product ) {

	$navigation_style           = $product->get_composite_layout_style();
	$navigation_style_variation = $product->get_composite_layout_style_variation();

	/**
	 * Filter to determine whether the summary will be displayed.
	 *
	 * @param  boolean               $show_summary
	 * @param  string                $layout_id
	 * @param  string                $layout_variation_id
	 * @param  WC_Product_Composite  $product
	 */
	$show_summary = apply_filters( 'woocommerce_composite_summary_display', 'paged' === $navigation_style, $navigation_style, $navigation_style_variation, $product );

	if ( $show_summary ) {
		wc_get_template( 'single-product/composite-summary.php', array(
			'product'    => $product,
			'product_id' => WC_CP_Core_Compatibility::get_id( $product ),
			'components' => $components,
			'hidden'     => false,
		), '', WC_CP()->plugin_path() . '/templates/' );
	}
}

/**
 * Hook layout/style-specific content on the 'woocommerce_composite_before_components' action.
 */
function wc_cp_before_components( $components, $product ) {

	$layout = $product->get_composite_layout_style();

	/**
	 * Action 'woocommerce_composite_before_components_paged':
	 *
	 * @since  3.4.0
	 *
	 * @param  array                 $components
	 * @param  WC_Product_Composite  $product
	 *
	 * @hooked wc_cp_component_transition_scroll_target    - 10
	 * @hooked wc_cp_pagination                            - 15
	 * @hooked wc_cp_navigation_top                        - 20
	 * @hooked wc_cp_navigation_movable                    - 20
	 */
	do_action( 'woocommerce_composite_before_components_' . $layout, $components, $product );
}

/**
 * Hook layout/style-specific content on the 'woocommerce_composite_after_components' action.
 *
 * @return void
 */
function wc_cp_after_components( $components, $product ) {

	$layout = $product->get_composite_layout_style();

	/**
	 * Action 'woocommerce_composite_after_components_{$layout}':
	 *
	 * @since  3.4.0
	 *
	 * @param  array                 $components
	 * @param  WC_Product_Composite  $product
	 *
	 * Action 'woocommerce_composite_after_components_single':
	 *
	 * @hooked wc_cp_add_to_cart_section - 10
	 *
	 *
	 * Action 'woocommerce_composite_after_components_progressive':
	 *
	 * @hooked wc_cp_add_to_cart_section - 10
	 * @hooked wc_cp_navigation_bottom   - 15
	 *
	 *
	 * Action 'woocommerce_composite_after_components_paged':
	 *
	 * @hooked wc_cp_add_to_cart_section                  - 10
	 * @hooked wc_cp_navigation_bottom                    - 20
	 */
	do_action( 'woocommerce_composite_after_components_' . $layout, $components, $product );
}

/**
 * Loading status message.
 */
function wc_cp_status() {
	?><div class="composite_status">
		<div class="wrapper"></div>
	</div><?php
}

/**
 * Add-to-cart section.
 *
 * @param  array                 $components
 * @param  WC_Product_Composite  $product
 */
function wc_cp_add_to_cart_section( $components, $product ) {

	wc_get_template( 'single-product/composite-add-to-cart.php', array(
		'product'                    => $product,
		'components'                 => $components,
		'product_id'                 => WC_CP_Core_Compatibility::get_id( $product ),
		'availability_html'          => WC_CP_Core_Compatibility::wc_get_stock_html( $product ),
		'navigation_style'           => $product->get_composite_layout_style(),
		'navigation_style_variation' => $product->get_composite_layout_style_variation(),
	), '', WC_CP()->plugin_path() . '/templates/' );

}

/**
 * Add previous/next navigation buttons in paged mode -- added on bottom of page under the component options section.
 *
 * @param  array                 $components
 * @param  WC_Product_Composite  $product
 */
function wc_cp_navigation_bottom( $components, $product ) {

	$position                   = 'bottom';
	$navigation_style           = $product->get_composite_layout_style();
	$navigation_style_variation = $product->get_composite_layout_style_variation();

	$classes = array( $position, $navigation_style, $navigation_style_variation );

	wc_cp_navigation( $classes, $product );
}

/**
 * Add previous/next navigation buttons in paged mode -- added on top of page under the composite pagination section when component options are viewed as thumbnails.
 *
 * @param  array                 $components
 * @param  WC_Product_Composite  $product
 */
function wc_cp_navigation_top( $components, $product ) {

	$position                   = 'top';
	$navigation_style           = 'paged';
	$navigation_style_variation = $product->get_composite_layout_style_variation();

	$classes = array( $position, $navigation_style, $navigation_style_variation );
	wc_cp_navigation( $classes, $product );
}

/**
 * Add previous/next navigation buttons in multi-page mode -- added on top of page under the composite pagination section.
 *
 * @param  array                 $components
 * @param  WC_Product_Composite  $product
 */
function wc_cp_navigation_movable( $components, $product ) {

	$position                   = 'movable hidden';
	$navigation_style           = 'paged';
	$navigation_style_variation = $product->get_composite_layout_style_variation();

	$classes = array( $position, $navigation_style, $navigation_style_variation );
	wc_cp_navigation( $classes, $product );
}

/**
 * Add previous/next navigation buttons in multi-page mode.
 *
 * @param  array                 $classes
 * @param  WC_Product_Composite  $product
 */
function wc_cp_navigation( $classes, $composite ) {

	wc_get_template( 'single-product/composite-navigation.php', array(
		'product'                    => $composite,
		'product_id'                 => WC_CP_Core_Compatibility::get_id( $composite ),
		'navigation_style'           => $composite->get_composite_layout_style(),
		'navigation_style_variation' => $composite->get_composite_layout_style_variation(),
		'classes'                    => implode( ' ', $classes )
	), '', WC_CP()->plugin_path() . '/templates/' );
}

/**
 * Component selection notices container displayed in the component_summary container.
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_component_summary_message( $component_id, $composite ) {

	$options_style = $composite->get_component( $component_id )->get_options_style();

	if ( 'thumbnails' === $options_style ) {
		$classes = array( 'top' );
		wc_cp_component_message( $classes );
	}
}

/**
 * Component selection notices container displayed in the component_selections container (progressive layout).
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_component_selections_message_progressive( $component_id, $composite ) {

	$classes = array( 'bottom' );
	wc_cp_component_message( $classes );
}

/**
 * Component selection notices container displayed in the component_selections container (paged layout).
 *
 * @param  string                $component_id
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_component_selections_message_paged( $component_id, $composite ) {

	$options_style = $composite->get_component( $component_id )->get_options_style();

	if ( 'thumbnails' !== $options_style ) {
		$classes = array( 'bottom' );
		wc_cp_component_message( $classes );
	}
}

/**
 * Component selection notices container displayed in progressive/paged layouts.
 *
 * @param  array                 $classes
 * @param  WC_Product_Composite  $product
 */
function wc_cp_component_message( $classes ) {

	echo '<div class="component_message ' . implode( ' ', $classes ) . '" style="display:none"></div>';
}

/**
 * When changing between components in paged mode, the viewport will scroll to this div if it's not visible.
 * Adding the 'scroll_bottom' class to the element will scroll the bottom of the viewport to the target.
 *
 * @param  array                 $components
 * @param  WC_Product_Composite  $product
 */
function wc_cp_component_transition_scroll_target( $components, $product ) {

	?><div class="scroll_show_component"></div><?php
}

/**
 * Div for blocking form content during transitions.
 *
 * @param  array                 $components
 * @param  WC_Product_Composite  $product
 */
function wc_cp_component_blocker( $components, $product ) {

	?><div class="form_input_blocker"></div><?php
}

/**
 * Adds composite pagination in paged mode.
 *
 * @param  array                 $components
 * @param  WC_Product_Composite  $product
 */
function wc_cp_pagination( $components, $product ) {

	$layout_variation = $product->get_composite_layout_style_variation();

	if ( 'componentized' !== $layout_variation ) {

		wc_get_template( 'single-product/composite-pagination.php', array(
			'product'    => $product,
			'product_id' => WC_CP_Core_Compatibility::get_id( $product ),
			'components' => $components
		), '', WC_CP()->plugin_path() . '/templates/' );

	}
}

/**
 * When selecting the final step in paged mode, the viewport will scroll to this div.
 * Adding the 'scroll_bottom' class to the element will scroll the bottom of the viewport to the target.
 *
 * @param  array                 $components
 * @param  WC_Product_Composite  $product
 */
function wc_cp_final_step_scroll_target( $components, $product ) {

	$navigation_style = $product->get_composite_layout_style();

	if ( 'paged' === $navigation_style ) {

		?><div class="scroll_final_step"></div><?php
	}
}

/**
 * No js notice.
 *
 * @param  array                 $components
 * @param  WC_Product_Composite  $product
 */
function wc_cp_no_js_msg( $components, $product ) {

	?><p class="cp-no-js-msg">
		<span id="cp-no-js-msg">
			<script type="text/javascript">
				var el = document.getElementById( 'cp-no-js-msg' );
				el.innerHTML = "<?php _e( 'Loading...', 'ultimatewoo-pro' ); ?>";
			</script>
		</span>
		<noscript>
			<?php _e( 'JavaScript must be supported by your browser and needs to be enabled in order to view this page.', 'ultimatewoo-pro' ); ?>
		</noscript>
	</p><?php
}


/*--------------------------------------------------------*/
/*                                                        */
/*  Composited products template functions.               */
/*                                                        */
/*--------------------------------------------------------*/

/**
 * Composited product title template.
 *
 * @param  WC_Product            $product
 * @param  string                $component_id
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_composited_product_title( $product, $component_id, $composite ) {

	$component = $composite->get_component( $component_id );

	?>
	<div class="composited_product_title_wrapper" data-show_title="<?php echo false === $component->hide_selected_option_title() ? 'yes' : 'no'; ?>"></div>
	<?php
}

/**
 * Composited product details wrapper open.
 *
 * @param  WC_Product            $product
 * @param  string                $component_id
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_composited_product_details_wrapper_open( $product, $component_id, $composite ) {

	echo '<div class="composited_product_details_wrapper">';
}

/**
 * Composited product thimbnail template.
 *
 * @param  WC_Product            $product
 * @param  string                $component_id
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_composited_product_thumbnail( $product, $component_id, $composite ) {

	$component  = $composite->get_component( $component_id );
	$product_id = WC_CP_Core_Compatibility::get_id( $product );

	if ( ! $component->hide_selected_option_thumbnail() ) {
		wc_get_template( 'composited-product/image.php', array(
			'product_id' => $product_id,
			'image_rel'  => WC_CP_Core_Compatibility::is_wc_version_gte_2_7() && current_theme_supports( 'wc-product-gallery-lightbox' ) ? 'photoSwipe' : 'prettyPhoto',
			'component'  => $component
		), '', WC_CP()->plugin_path() . '/templates/' );
	}
}

/**
 * Composited product details template.
 *
 * @param  WC_Product            $product
 * @param  string                $component_id
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_composited_product_details( $product, $component_id, $composite ) {

	/**
	 * Action 'woocommerce_composite_show_composited_product_{$product_type}'.
	 * Composited product details template (type-specific).
	 *
	 * @param  WC_Product            $product
	 * @param  string                $component_id
	 * @param  WC_Product_Composite  $composite
	 *
	 * @hooked wc_cp_composited_product_details_variable - 10
	 * @hooked wc_cp_composited_product_details_simple   - 10
	 */
	do_action( 'woocommerce_composite_show_composited_product_' . $product->get_type(), $product, $component_id, $composite );
}

/**
 * Composited product details wrapper close.
 *
 * @param  WC_Product            $product
 * @param  string                $component_id
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_composited_product_details_wrapper_close( $product, $component_id, $composite ) {

	echo '</div>';
}

/**
 * Composited Simple product details template.
 *
 * @param  WC_Product            $product
 * @param  string                $component_id
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_composited_product_details_simple( $product, $component_id, $composite ) {

	$product_id         = WC_CP_Core_Compatibility::get_id( $product );
	$composite_id       = WC_CP_Core_Compatibility::get_id( $composite );
	$component          = $composite->get_component( $component_id );
	$composited_product = $component->get_option( $product_id );
	$quantity_min       = $composited_product->get_quantity_min();
	$quantity_max       = $composited_product->get_quantity_max( true );

	WC_CP_Helpers::extend_price_display_precision();

	$price_incl_tax = WC_CP_Core_Compatibility::wc_get_price_including_tax( $product, array( 'qty' => 1, 'price' => 1000 ) );
	$price_excl_tax = WC_CP_Core_Compatibility::wc_get_price_excluding_tax( $product, array( 'qty' => 1, 'price' => 1000 ) );
	$tax_ratio      = $price_incl_tax / $price_excl_tax;

	WC_CP_Helpers::reset_price_display_precision();

	/**
	 * Filter custom data passed to the template. Data added here can be accessed from the JS app.
	 *
	 * @param  array                 $custom_data
	 * @param  WC_Product            $displayed_product
	 * @param  string                $component_id
	 * @param  WC_CP_Component       $component
	 * @param  WC_Product_Composite  $composite_product
	 */
	$custom_data = apply_filters( 'woocommerce_composited_product_custom_data', array( 'price_tax' => $tax_ratio, 'image_data' => $composited_product->get_image_data() ), $product, $component_id, $component, $composite );

	wc_get_template( 'composited-product/simple-product.php', array(
		'product'            => $product,
		'price'              => $product->get_price(),
		'regular_price'      => $product->get_regular_price(),
		'custom_data'        => $custom_data,
		'composite_id'       => $composite_id,
		'component_id'       => $component_id,
		'quantity_min'       => $quantity_min,
		'quantity_max'       => $quantity_max,
		'composited_product' => $composited_product,
		'composite_product'  => $composite
	), '', WC_CP()->plugin_path() . '/templates/' );
}

/**
 * Composited Variable product details template.
 *
 * @param  WC_Product            $product
 * @param  string                $component_id
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_composited_product_details_variable( $product, $component_id, $composite ) {

	$product_variations = $product->get_available_variations();

	if ( ! $product_variations ) {

		?><div class="component_data" data-component_set="false" data-price="0" data-regular_price="0" data-product_type="unavailable-product">
			<?php _e( 'This item is currently unavailable.', 'ultimatewoo-pro' ); ?>
		</div><?php

	} else {

		$product_id         = WC_CP_Core_Compatibility::get_id( $product );
		$component          = $composite->get_component( $component_id );
		$composited_product = $component->get_option( $product_id );

		/** Documented in {@see wc_cp_composited_product_details_simple} */
		$custom_data = apply_filters( 'woocommerce_composited_product_custom_data', array( 'price_tax' => 1.0, 'image_data' => $composited_product->get_image_data() ), $product, $component_id, $component, $composite );

		$attributes     = $product->get_variation_attributes();
		$attribute_keys = array_keys( $attributes );

		$quantity_min = $composited_product->get_quantity_min();
		$quantity_max = $composited_product->get_quantity_max();

		wc_get_template( 'composited-product/variable-product.php', array(
			'product'            => $product,
			'product_variations' => $product_variations,
			'attributes'         => $attributes,
			'attribute_keys'     => $attribute_keys,
			'custom_data'        => $custom_data,
			'component_id'       => $component_id,
			'quantity_min'       => $quantity_min,
			'quantity_max'       => $quantity_max,
			'composited_product' => $composited_product,
			'composite_product'  => $composite
		), '', WC_CP()->plugin_path() . '/templates/' );
	}
}

/**
 * Composited product excerpt.
 *
 * @param  WC_Product            $product
 * @param  string                $component_id
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_composited_product_excerpt( $product, $component_id, $composite ) {

	$product_id = WC_CP_Core_Compatibility::get_id( $product );
	$component  = $composite->get_component( $component_id );

	if ( ! $component->hide_selected_option_description() ) {
		wc_get_template( 'composited-product/excerpt.php', array(
			'product_description' => WC_CP_Core_Compatibility::get_prop( $product, 'short_description' ),
			'product_id'          => $product_id,
			'component_id'        => $component_id,
			'composite'           => $composite,
		), '', WC_CP()->plugin_path() . '/templates/' );
	}
}

/**
 * Composited Simple product price.
 *
 * @param  WC_Product            $product
 * @param  string                $component_id
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_composited_product_price( $product, $component_id, $composite ) {

	if ( 'simple' === $product->get_type() ) {

		$product_id         = WC_CP_Core_Compatibility::get_id( $product );
		$component          = $composite->get_component( $component_id );
		$composited_product = $component->get_option( $product_id );

		if ( $composited_product->is_priced_individually() && false === $component->hide_selected_option_price()  && '' !== $product->get_price() ) {
			wc_get_template( 'composited-product/price.php', array(
				'product' => $product
			), '', WC_CP()->plugin_path() . '/templates/' );
		}
	}
}

/**
 * Composited single variation template.
 *
 * @param  WC_Product_Variable   $product
 * @param  string                $component_id
 * @param  WC_Product_Composite  $composite
 */
function wc_cp_composited_single_variation( $product, $component_id, $composite ) {

	$product_id         = WC_CP_Core_Compatibility::get_id( $product );
	$composited_product = $composite->get_component( $component_id )->get_option( $product_id );

	$quantity_min = $composited_product->get_quantity_min();
	$quantity_max = $composited_product->get_quantity_max();

	wc_get_template( 'composited-product/variation.php', array(
		'quantity_min'      => $quantity_min,
		'quantity_max'      => $quantity_max,
		'component_id'      => $component_id,
		'product'           => $product,
		'composite_product' => $composite
	), '', WC_CP()->plugin_path() . '/templates/' );
}
