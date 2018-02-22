<?php
/**
 * WC_CP_Meta_Box_Product_Data class
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
 * Product meta-box data for the Composite type.
 *
 * @class     WC_CP_Meta_Box_Product_Data
 * @version   3.9.5
 */
class WC_CP_Meta_Box_Product_Data {

	/**
	 * Notices to send via ajax when saving a Composite config.
	 * @var array
	 */
	public static $ajax_notices = array();

	/**
	 * Hook in.
	 */
	public static function init() {

		// Creates the admin Components and Scenarios panel tabs.
		add_action( 'woocommerce_product_data_tabs', array( __CLASS__, 'composite_product_data_tabs' ) );

		// Adds the Hide Price option.
		add_action( 'woocommerce_product_options_pricing', array( __CLASS__, 'composite_pricing_options' ) );

		// Creates the admin Components and Scenarios panels.
		add_action( 'woocommerce_product_write_panels', array( __CLASS__, 'composite_write_panel' ) );
		add_action( 'woocommerce_product_options_stock', array( __CLASS__, 'composite_stock_info' ) );

		// Allows the selection of the 'composite product' type.
		add_filter( 'product_type_options', array( __CLASS__, 'add_composite_type_options' ) );

		// Processes and saves the necessary post metas from the selections made above.
		add_action( 'woocommerce_process_product_meta_composite', array( __CLASS__, 'process_composite_meta' ) );

		/*----------------------------------*/
		/*  Composite writepanel options.   */
		/*----------------------------------*/

		add_action( 'woocommerce_composite_admin_html', array( __CLASS__, 'composite_layout_options' ), 10, 2 );
		add_action( 'woocommerce_composite_admin_html', array( __CLASS__, 'composite_component_options' ), 15, 2 );

		/*---------------------------------*/
		/*  Component meta boxes.          */
		/*---------------------------------*/

		add_action( 'woocommerce_composite_component_admin_html', array( __CLASS__, 'component_admin_html' ), 10, 4 );

		// Basic component config options
		add_action( 'woocommerce_composite_component_admin_config_html', array( __CLASS__, 'component_config_title' ), 10, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( __CLASS__, 'component_config_description' ), 15, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( __CLASS__, 'component_config_image' ), 15, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( __CLASS__, 'component_config_options' ), 20, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( __CLASS__, 'component_config_options_style' ), 20, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( __CLASS__, 'component_config_quantity_min' ), 25, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( __CLASS__, 'component_config_quantity_max' ), 30, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( __CLASS__, 'component_config_shipped_individually' ), 35, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( __CLASS__, 'component_config_priced_individually' ), 35, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( __CLASS__, 'component_config_discount' ), 35, 3 );
		add_action( 'woocommerce_composite_component_admin_config_html', array( __CLASS__, 'component_config_optional' ), 40, 3 );

		// Advanced component configuration
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( __CLASS__, 'component_config_default_option' ), 5, 3 );
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( __CLASS__, 'component_selection_details_options' ), 10, 3 );
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( __CLASS__, 'component_subtotal_visibility_options' ), 10, 3 );
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( __CLASS__, 'component_sort_filter_show_orderby' ), 15, 3 );
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( __CLASS__, 'component_sort_filter_show_filters' ), 20, 3 );
		add_action( 'woocommerce_composite_component_admin_advanced_html', array( __CLASS__, 'component_id_marker' ), 100, 3 );

		/*----------------------------*/
		/* Scenario meta boxes html   */
		/*----------------------------*/

		add_action( 'woocommerce_composite_scenario_admin_html', array( __CLASS__, 'scenario_admin_html' ), 10, 5 );

		// Scenario options.
		add_action( 'woocommerce_composite_scenario_admin_info_html', array( __CLASS__, 'scenario_info' ), 10, 4 );
		add_action( 'woocommerce_composite_scenario_admin_config_html', array( __CLASS__, 'scenario_config' ), 10, 4 );

		// "Dependency Group" action.
		add_action( 'woocommerce_composite_scenario_admin_actions_html', array( __CLASS__, 'scenario_action_compat_group' ), 10, 4 );
		// "Hide Components" action.
		add_action( 'woocommerce_composite_scenario_admin_actions_html', array( __CLASS__, 'scenario_action_hide_components' ), 15, 4 );



		/*-----------------------------*/
		/* Sold Individually Options.  */
		/*-----------------------------*/

		add_action( 'woocommerce_product_options_sold_individually', array( __CLASS__, 'sold_individually_options' ) );

		/*-----------------------------------*/
		/* Editing in Cart Option.           */
		/*-----------------------------------*/

		add_action( 'woocommerce_product_options_advanced', array( __CLASS__, 'edit_in_cart_option' ) );
	}


	/**
	 * Enables the "Edit in Cart".
	 *
	 * @return void
	 */
	public static function edit_in_cart_option() {

		global $thepostid;

		echo '<div class="options_group show_if_composite">';

		woocommerce_wp_checkbox( array(
			'id'          => '_bto_edit_in_cart',
			'label'       => __( 'Editing in cart', 'ultimatewoo-pro' ),
			'description' => __( 'Allow modifications to the configuration of this Composite after it has been added to the cart.', 'ultimatewoo-pro' ),
			'desc_tip'    => true
		) );

		echo '</div>';
	}

	/**
	 * Renders additional "Sold Individually" options.
	 *
	 * @return void
	 */
	public static function sold_individually_options() {

		global $thepostid;

		$sold_individually         = get_post_meta( $thepostid, '_sold_individually', true );
		$sold_individually_context = get_post_meta( $thepostid, '_bto_sold_individually', true );

		$value = 'no';

		if ( $sold_individually === 'yes' ) {
			if ( ! in_array( $sold_individually_context, array( 'configuration', 'product' ) ) ) {
				$value = 'product';
			} else {
				$value = $sold_individually_context;
			}
		}

		// Extend "Sold Individually" options to account for different configurations.
		woocommerce_wp_select( array(
			'id'            => '_bto_sold_individually',
			'wrapper_class' => 'show_if_composite',
			'label'         => __( 'Sold individually', 'woocommerce' ),
			'options'       => array(
				'no'            => __( 'No', 'ultimatewoo-pro' ),
				'product'       => __( 'Yes', 'ultimatewoo-pro' ),
				'configuration' => __( 'Matching configurations only', 'ultimatewoo-pro' )
			),
			'value'         => $value,
			'desc_tip'      => 'true',
			'description'   => __( 'Allow only one of this item (or only one of each unique configuration of this item) to be bought in a single order.', 'ultimatewoo-pro' )
		) );
	}

	/**
	 * Renders the composite writepanel Layout Options section before the Components section.
	 *
	 * @param  array $composite_data
	 * @param  int   $composite_id
	 * @return void
	 */
	public static function composite_layout_options( $composite_data, $composite_id ) {

		?><div class="options_group bundle_group bto_clearfix">

			<div class="bto_layouts bto_clearfix form-field">
				<label class="bundle_group_label">
					<?php _e( 'Layout', 'ultimatewoo-pro' ); echo wc_help_tip( __( 'Choose a layout for this Composite product.', 'ultimatewoo-pro' ) ); ?>
				</label>
				<ul class="bto_clearfix bto_layouts_list">
					<?php
					$layouts         = WC_Product_Composite::get_layout_options();
					$selected_layout = WC_Product_Composite::get_layout_option( get_post_meta( $composite_id, '_bto_style', true ) );

					foreach ( $layouts as $layout_id => $layout_description ) {

						/**
						 * Filter the image associated with a layout.
						 *
						 * @param  string $image_src
						 * @param  string $layout_id
						 */
						$layout_src     = apply_filters( 'woocommerce_composite_product_layout_image_src', WC_CP()->plugin_url() . '/assets/images/' . $layout_id . '.png', $layout_id );
						$layout_tooltip = WC_Product_Composite::get_layout_description( $layout_id );

						?><li><label class="bto_layout_label <?php echo $selected_layout == $layout_id ? 'selected' : ''; ?>">
							<img class="layout_img" src="<?php echo $layout_src; ?>" />
							<input <?php echo $selected_layout == $layout_id ? 'checked="checked"' : ''; ?> name="bto_style" type="radio" value="<?php echo $layout_id; ?>" />
							<span class="layout_title"><?php echo $layout_description ?></span>
							<span class="layout_tooltip"><?php echo $layout_tooltip; ?></span>
						</label></li><?php
					}

				?></ul>
			</div>

			<?php

			/**
			 * Action 'woocommerce_composite_admin_after_layout_options':
			 *
			 * @param  array   $composite_data
			 * @param  string  $composite_id
			 */
			do_action( 'woocommerce_composite_admin_after_layout_options', $composite_data, $composite_id );

		?></div><?php
	}

	/**
	 * Renders the composite writepanel Layout Options section before the Components section.
	 *
	 * @param  array $composite_data
	 * @param  int   $composite_id
	 * @return void
	 */
	public static function composite_component_options( $composite_data, $composite_id ) {

		?><div class="options_group config_group bto_clearfix">
			<p class="toolbar">
				<span class="bulk_toggle_wrapper">
					<span class="disabler"></span>
					<a href="#" class="expand_all"><?php _e( 'Expand all', 'woocommerce' ); ?></a>
					<a href="#" class="close_all"><?php _e( 'Close all', 'woocommerce' ); ?></a>
				</span>
			</p>

			<div id="bto_config_group_inner">

				<div class="bto_groups wc-metaboxes ui-sortable" data-count="">

					<?php

					if ( $composite_data ) {

						$i = 0;

						foreach ( $composite_data as $group_id => $data ) {

							/**
							 * Action 'woocommerce_composite_component_admin_html'.
							 *
							 * @param  int     $i
							 * @param  array   $data
							 * @param  string  $composite_id
							 * @param  string  $state
							 *
							 * @hooked {@see component_admin_html} - 10
							 */
							do_action( 'woocommerce_composite_component_admin_html', $i, $data, $composite_id, 'closed' );

							$i++;
						}
					}

				?></div>
			</div>

			<p class="toolbar borderless">
				<button type="button" class="button save_composition"><?php _e( 'Save Configuration', 'ultimatewoo-pro' ); ?></button>
				<button type="button" class="button button-primary add_bto_group"><?php _e( 'Add Component', 'ultimatewoo-pro' ); ?></button>
			</p>
		</div><?php
	}

	/**
	 * Add a component id watermark in the 'Advanced Configuration' tab.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_id_marker( $id, $data, $product_id ) {

		if ( ! empty( $data[ 'component_id' ] ) ) {

			?><span class="group_id">
				<?php echo '#' . esc_html( $data[ 'component_id' ] ); ?>
			</span><?php
		}
	}

	/**
	 * Handles getting component meta box tabs - @see 'component_admin_html'.
	 *
	 * @return array
	 */
	public static function get_component_tabs() {

		/**
		 * Filter the tab sections that appear in every Component metabox.
		 *
		 * @param  array  $tabs
		 */
		return apply_filters( 'woocommerce_composite_component_admin_html_tabs', array(
			'config' => array(
				'title'   => __( 'Basic Settings', 'ultimatewoo-pro' )
			),
			'advanced' => array(
				'title'   => __( 'Advanced Settings', 'ultimatewoo-pro' )
			)
		) );
	}

	/**
	 * Load component meta box in 'woocommerce_composite_component_admin_html'.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $composite_id
	 * @param  string $toggle
	 * @return void
	 */
	public static function component_admin_html( $id, $data, $composite_id, $toggle = 'closed' ) {

		$tabs = self::get_component_tabs();

		include( WC_CP()->plugin_path() . '/includes/admin/meta-boxes/views/html-component-admin.php' );
	}

	/**
	 * Load component meta box in 'woocommerce_composite_component_admin_html'.
	 *
	 * @param  int    $id
	 * @param  array  $scenario_data
	 * @param  array  $composite_data
	 * @param  int    $composite_id
	 * @param  string $toggle
	 * @return void
	 */
	public static function scenario_admin_html( $id, $scenario_data, $composite_data, $composite_id, $toggle = 'closed' ) {

		include( WC_CP()->plugin_path() . '/includes/admin/meta-boxes/views/html-scenario-admin.php' );
	}

	/**
	 * Add "Create Dependency Group" scenario action.
	 *
	 * @param  int    $id
	 * @param  array  $scenario_data
	 * @param  array  $composite_data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function scenario_action_compat_group( $id, $scenario_data, $composite_data, $product_id ) {

		$defines_compat_group = isset( $scenario_data[ 'scenario_actions' ][ 'compat_group' ][ 'is_active' ] ) ? $scenario_data[ 'scenario_actions' ][ 'compat_group' ][ 'is_active' ] : 'yes';

		?>
		<div class="scenario_action_compat_group" >
			<div class="form-field">
				<label for="scenario_action_compat_group_<?php echo $id; ?>">
					<?php echo __( 'Create Dependency Group', 'ultimatewoo-pro' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $defines_compat_group === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_scenario_data[<?php echo $id; ?>][scenario_actions][compat_group][is_active]" <?php echo ( $defines_compat_group === 'yes' ? ' value="1"' : '' ); ?> />
				<?php echo wc_help_tip( __( 'Creates a group of dependent selections from the products/variations included in this Scenario. Any selections that do not belong in this group will be disabled unless they are included in another dependency group.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add "Hide Components" scenario action.
	 *
	 * @param  int    $id
	 * @param  array  $scenario_data
	 * @param  array  $composite_data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function scenario_action_hide_components( $id, $scenario_data, $composite_data, $product_id ) {

		$hide_components   = isset( $scenario_data[ 'scenario_actions' ][ 'conditional_components' ][ 'is_active' ] ) ? $scenario_data[ 'scenario_actions' ][ 'conditional_components' ][ 'is_active' ] : 'no';
		$hidden_components = ! empty( $scenario_data[ 'scenario_actions' ][ 'conditional_components' ][ 'hidden_components' ] ) ? $scenario_data[ 'scenario_actions' ][ 'conditional_components' ][ 'hidden_components' ] : array();

		?>
		<div class="scenario_action_conditional_components_group" >
			<div class="form-field toggle_conditional_components">
				<label for="scenario_action_conditional_components_<?php echo $id; ?>">
					<?php echo __( 'Hide Components', 'ultimatewoo-pro' ); ?>
				</label>
				<input type="checkbox" class="checkbox" <?php echo ( $hide_components === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_scenario_data[<?php echo $id; ?>][scenario_actions][conditional_components][is_active]" <?php echo ( $hide_components === 'yes' ? ' value="1"' : '' ); ?> />
				<?php echo wc_help_tip( __( 'Hide one or more Components when this Scenario is active. Note that any Components added in this field will be hidden by default until the conditions for hiding or showing them can be evaluated.', 'ultimatewoo-pro' ) ); ?>
			</div>
			<div class="action_components" <?php echo ( $hide_components === 'no' ? ' style="display:none;"' : '' ); ?> >
				<select id="bto_conditional_components_ids_<?php echo $id; ?>" name="bto_scenario_data[<?php echo $id; ?>][scenario_actions][conditional_components][hidden_components][]" style="width: 75%;" class="wc-enhanced-select conditional_components_ids" multiple="multiple" data-placeholder="<?php echo __( 'Select components&hellip;', 'ultimatewoo-pro' ); ?>"><?php

					foreach ( $composite_data as $component_id => $component_data ) {

						$component_title = apply_filters( 'woocommerce_composite_component_title', $component_data[ 'title' ], $component_id, $product_id );

						$option_selected = in_array( $component_id, $hidden_components ) ? 'selected="selected"' : '';
						echo '<option ' . $option_selected . 'value="' . $component_id . '">' . $component_title . '</option>';
					}

				?></select>
			</div>
		</div>
		<?php
	}

	/**
	 * Add scenario title and description options.
	 *
	 * @param  int    $id
	 * @param  array  $scenario_data
	 * @param  array  $composite_data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function scenario_info( $id, $scenario_data, $composite_data, $product_id ) {

		$title       = isset( $scenario_data[ 'title' ] ) ? $scenario_data[ 'title' ] : '';
		$position    = isset( $scenario_data[ 'position' ] ) ? $scenario_data[ 'position' ] : $id;
		$description = isset( $scenario_data[ 'description' ] ) ? $scenario_data[ 'description' ] : '';

		?>
		<div class="scenario_title">
			<div class="form-field">
				<label>
					<?php echo __( 'Scenario Name', 'ultimatewoo-pro' ); ?>
				</label>
				<input type="text" class="scenario_title component_text_input" name="bto_scenario_data[<?php echo $id; ?>][title]" value="<?php echo $title; ?>"/>
				<input type="hidden" name="bto_scenario_data[<?php echo $id; ?>][position]" class="scenario_position" value="<?php echo $position; ?>"/>
			</div>
		</div>
		<div class="scenario_description">
			<div class="form-field">
				<label>
					<?php echo __( 'Scenario Description', 'ultimatewoo-pro' ); ?>
				</label>
				<textarea class="scenario_description" name="bto_scenario_data[<?php echo $id; ?>][description]" id="scenario_description_<?php echo $id; ?>" placeholder="" rows="2" cols="20"><?php echo esc_textarea( $description ); ?></textarea>
			</div>
		</div>
		<?php
	}

	/**
	 * Add scenario config options.
	 *
	 * @param  int    $id
	 * @param  array  $scenario_data
	 * @param  array  $composite_data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function scenario_config( $id, $scenario_data, $composite_data, $product_id ) {

		global $composite_product_object_data;

		if ( empty( $composite_product_object_data ) ) {
			$composite_product_object_data = array();
		}

		?><div class="scenario_config_group"><?php

			foreach ( $composite_data as $component_id => $component_data ) {

				$modifier = '';

				if ( isset( $scenario_data[ 'modifier' ][ $component_id ] ) ) {

					$modifier = $scenario_data[ 'modifier' ][ $component_id ];

				} else {

					$exclude = isset( $scenario_data[ 'exclude' ][ $component_id ] ) ? $scenario_data[ 'exclude' ][ $component_id ] : 'no';

					if ( $exclude === 'no' ) {
						$modifier = 'in';
					} elseif ( $exclude === 'masked' ) {
						$modifier = 'masked';
					} else {
						$modifier = 'not-in';
					}
				}

				/**
				 * Filter the component title.
				 *
				 * @param  string  $title
				 * @param  string  $component_id
				 * @param  string  $product_id
				 */
				$component_title = apply_filters( 'woocommerce_composite_component_title', $component_data[ 'title' ], $component_id, $product_id );

				?><div class="bto_scenario_selector">
					<div class="form-field">
						<label><?php
							echo  $component_title;
						?></label>
						<div class="bto_scenario_modifier_wrapper bto_scenario_exclude_wrapper">
							<select class="bto_scenario_modifier bto_scenario_exclude" name="bto_scenario_data[<?php echo $id; ?>][modifier][<?php echo $component_id; ?>]">
								<option <?php selected( $modifier, 'in', true ); ?> value="in"><?php echo __( 'selection is', 'ultimatewoo-pro' ); ?></option>
								<option <?php selected( $modifier, 'not-in', true ); ?> value="not-in"><?php echo __( 'selection is not', 'ultimatewoo-pro' ); ?></option>
								<option <?php selected( $modifier, 'masked', true ); ?> value="masked"><?php echo __( 'selection is masked', 'ultimatewoo-pro' ); ?></option>
							</select>
						</div>
						<div class="bto_scenario_selector_inner" <?php echo $modifier === 'masked' ? 'style="display:none"' : ''; ?>><?php

							$component_options_cache_key = 'component_' . $component_id . '_options';
							$component_options           = WC_CP_Helpers::cache_get( $component_options_cache_key );

							if ( null === $component_options ) {
								$component_options = WC_CP_Component::query_component_options( $component_data );
								WC_CP_Helpers::cache_set( $component_options_cache_key, $component_options );
							}

							$component_options_count = count( $component_options );
							$component_options_data  = array();

							$ajax_products_threshold = apply_filters( 'woocommerce_composite_scenario_admin_products_ajax_threshold', 30, $component_id, $product_id );
							$use_ajax                = $component_options_count >= $ajax_products_threshold || ( isset( $composite_product_object_data[ 'component_options_count' ] ) && isset( $composite_product_object_data[ 'component_options_ajax_threshold' ] ) && $composite_product_object_data[ 'component_options_count' ] >= $composite_product_object_data[ 'component_options_ajax_threshold' ] );

							if ( false === $use_ajax ) {

								foreach ( $component_options as $component_option_id ) {

									$component_option_cache_key = 'component_option_' . $component_option_id;
									$component_option           = WC_CP_Helpers::cache_get( $component_option_cache_key );

									if ( null === $component_option ) {
										$component_option = wc_get_product( $component_option_id );
										WC_CP_Helpers::cache_set( $component_option_cache_key, $component_option );
									}

									if ( ! $component_option ) {
										continue;
									}

									$component_options_data[ $component_option_id ] = $component_option;

									$variations_count         = $component_option->is_type( 'variable' ) ? sizeof( $component_option->get_children() ) : 0;
									$component_options_count += $variations_count;

									if ( isset( $composite_product_object_data[ 'component_options_count' ] ) ) {
										$composite_product_object_data[ 'component_options_count' ] += $variations_count;
									}

									if ( $component_options_count >= $ajax_products_threshold ) {
										$use_ajax = true;
										break;
									}
								}
							}

							if ( false === $use_ajax ) {

								$scenario_options    = array();
								$scenario_selections = array();

								if ( $component_data[ 'optional' ] === 'yes' ) {

									if ( isset( $scenario_data[ 'component_data' ] ) && WC_CP_Helpers::in_array_key( $scenario_data[ 'component_data' ], $component_id, -1 ) ) {
										$scenario_selections[] = -1;
									}

									$scenario_options[ -1 ] = _x( 'No selection', 'optional component property controlled in scenarios', 'ultimatewoo-pro' );
								}

								if ( isset( $scenario_data[ 'component_data' ] ) && WC_CP_Helpers::in_array_key( $scenario_data[ 'component_data' ], $component_id, 0 ) ) {
									$scenario_selections[] = 0;
								}

								$scenario_options[ 0 ] = __( 'Any Product or Variation', 'ultimatewoo-pro' );

								foreach ( $component_options_data as $option_id => $option_data ) {

									$title        = $option_data->get_title();
									$product_type = $option_data->get_type();

									if ( $product_type === 'variable' ) {
										$product_title          = WC_CP_Helpers::get_product_title( $option_data, sprintf( _x( '%s &ndash; Any Variation', 'any product variation', 'ultimatewoo-pro' ), $title ) );
										$variation_descriptions = WC_CP_Helpers::get_product_variation_descriptions( $option_data );
									} else {
										$product_title = $title;
									}

									if ( isset( $scenario_data[ 'component_data' ] ) && WC_CP_Helpers::in_array_key( $scenario_data[ 'component_data' ], $component_id, $option_id ) ) {

										$scenario_selections[] = $option_id;
									}

									$scenario_options[ $option_id ] = $product_title;

									if ( $product_type === 'variable' ) {

										if ( ! empty( $variation_descriptions ) ) {

											foreach ( $variation_descriptions as $variation_id => $description ) {

												if ( isset( $scenario_data[ 'component_data' ] ) && WC_CP_Helpers::in_array_key( $scenario_data[ 'component_data' ], $component_id, $variation_id ) ) {
													$scenario_selections[] = $variation_id;
												}

												$scenario_options[ $variation_id ] = $description;
											}
										}
									}

								}

								$no_selection = _x( 'No selection', 'optional component property controlled in scenarios', 'ultimatewoo-pro' );
								$optional_tip = $component_data[ 'optional' ] === 'yes' ? sprintf( __( '<br/><br/><strong>Advanced Tip</strong> &ndash; The <strong>%1$s</strong> option refers to a state where none of the available products is selected. You can use it in combination with product references to create selection dependencies, or even to make <strong>%2$s</strong> conditionally <strong>Optional</strong>.', 'ultimatewoo-pro' ), $no_selection, $component_title ) : '';
								$select_tip   = sprintf( __( 'Select products and variations from <strong>%1$s</strong>.<br/><br/><strong>Tip</strong> &ndash; Choose <strong>Any Product or Variation</strong> to add all <strong>%1$s</strong> products and variations in this Scenario.%2$s', 'ultimatewoo-pro' ), $component_title, $optional_tip );

								?><select id="bto_scenario_ids_<?php echo $id; ?>_<?php echo $component_id; ?>" name="bto_scenario_data[<?php echo $id; ?>][component_data][<?php echo $component_id; ?>][]" style="width: 75%;" class="wc-enhanced-select bto_scenario_ids" multiple="multiple" data-placeholder="<?php echo __( 'Select products &amp; variations&hellip;', 'ultimatewoo-pro' ); ?>"><?php

									foreach ( $scenario_options as $scenario_option_id => $scenario_option_description ) {
										$option_selected = in_array( $scenario_option_id, $scenario_selections ) ? 'selected="selected"' : '';
										echo '<option ' . $option_selected . 'value="' . $scenario_option_id . '">' . $scenario_option_description . '</option>';
									}

								?></select>
								<span class="bto_scenario_select tips" data-tip="<?php echo $select_tip; ?>"></span><?php

							} else {

								$selections_in_scenario = array();

								if ( ! empty( $scenario_data[ 'component_data' ] ) ) {

									foreach ( $scenario_data[ 'component_data' ][ $component_id ] as $product_id_in_scenario ) {

										if ( $product_id_in_scenario == -1 ) {
											if ( $component_data[ 'optional' ] === 'yes' ) {
												$selections_in_scenario[ $product_id_in_scenario ] = _x( 'No selection', 'optional component property controlled in scenarios', 'ultimatewoo-pro' );
											}
										} elseif ( $product_id_in_scenario == 0 ) {
											$selections_in_scenario[ $product_id_in_scenario ] = __( 'Any Product or Variation', 'ultimatewoo-pro' );
										} else {

											$product_in_scenario_cache_key = 'component_option_' . $product_id_in_scenario;
											$product_in_scenario           = WC_CP_Helpers::cache_get( $product_in_scenario_cache_key );

											if ( null === $product_in_scenario ) {
												$product_in_scenario = wc_get_product( $product_id_in_scenario );
												WC_CP_Helpers::cache_set( $product_in_scenario_cache_key, $product_in_scenario );
											}

											if ( ! $product_in_scenario ) {
												continue;
											}

											if ( ! in_array( $product_in_scenario->id, $component_options ) ) {
												continue;
											}

											if ( $product_in_scenario->get_type() === 'variation' ) {
												$selections_in_scenario[ $product_id_in_scenario ] = WC_CP_Helpers::get_product_variation_title( $product_in_scenario );
											} elseif ( $product_in_scenario->get_type() === 'variable' ) {
												$selections_in_scenario[ $product_id_in_scenario ] = WC_CP_Helpers::get_product_title( $product_in_scenario, sprintf( _x( '%s &ndash; Any Variation', 'any product variation', 'ultimatewoo-pro' ), $product_in_scenario->get_title() ) );
											} else {
												$selections_in_scenario[ $product_id_in_scenario ] = WC_CP_Helpers::get_product_title( $product_in_scenario );
											}
										}
									}
								}

								$no_selection = _x( 'No selection', 'optional component property controlled in scenarios', 'ultimatewoo-pro' );
								$optional_tip = $component_data[ 'optional' ] === 'yes' ? sprintf( __( '<br/><br/><strong>Advanced Tip</strong> &ndash; The <strong>%1$s</strong> option refers to a state where none of the available products is selected. You can use it in combination with product references to create selection dependencies, or even to make <strong>%2$s</strong> conditionally <strong>Optional</strong>.', 'ultimatewoo-pro' ), $no_selection, $component_title ) : '';
								$search_tip   = sprintf( __( 'Search for products and variations from <strong>%1$s</strong>.<br/><br/><strong>Tip</strong> &ndash; Choose <strong>Any Product or Variation</strong> to add all <strong>%1$s</strong> products and variations in this Scenario.%2$s', 'ultimatewoo-pro' ), $component_title, $optional_tip );

								?><input type="hidden" id="bto_scenario_ids_<?php echo $id; ?>_<?php echo $component_id; ?>" name="bto_scenario_data[<?php echo $id; ?>][component_data][<?php echo $component_id; ?>]" class="wc-component-options-search" style="width: 75%;" data-include="<?php echo esc_attr( json_encode( array( 'composite_id' => $product_id, 'component_id' => $component_id ) ) ); ?>" data-limit="100" data-component_optional="<?php echo $component_data[ 'optional' ]; ?>" data-placeholder="<?php _e( 'Search for products &amp; variations&hellip;', 'ultimatewoo-pro' ); ?>" data-action="woocommerce_json_search_products_and_variations_in_component" data-multiple="true" data-selected="<?php

									echo esc_attr( json_encode( $selections_in_scenario ) );

								?>" value="<?php echo implode( ',', array_keys( $selections_in_scenario ) ); ?>" />
								<span class="bto_scenario_search tips" data-tip="<?php echo $search_tip; ?>"></span><?php
							}

						?></div>
					</div>
				</div><?php
			}

		?></div><?php
	}

	/**
	 * Add component selection details layout options.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_selection_details_options( $id, $data, $product_id ) {

		$hide_product_title       = isset( $data[ 'hide_product_title' ] ) ? $data[ 'hide_product_title' ] : '';
		$hide_product_description = isset( $data[ 'hide_product_description' ] ) ? $data[ 'hide_product_description' ] : '';
		$hide_product_thumbnail   = isset( $data[ 'hide_product_thumbnail' ] ) ? $data[ 'hide_product_thumbnail' ] : '';
		$hide_product_price       = isset( $data[ 'hide_product_price' ] ) ? $data[ 'hide_product_price' ] : '';

		?>
		<div class="component_selection_details">
			<div class="form-field">
				<label for="component_selection_details_<?php echo $id; ?>">
					<?php echo __( 'Selected Option', 'ultimatewoo-pro' ); ?>
				</label>
				<div class="component_selection_details_option">
					<input type="checkbox" class="checkbox"<?php echo ( $hide_product_title === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][hide_product_title]" <?php echo ( $hide_product_title === 'yes' ? 'value="1"' : '' ); ?>/>
					<span><?php echo __( 'Hide Title', 'ultimatewoo-pro' ); ?>
					<?php echo wc_help_tip( __( 'Check this option to hide the selected Component Option title.', 'ultimatewoo-pro' ) ); ?>
				</div>
				<div class="component_selection_details_option">
					<input type="checkbox" class="checkbox"<?php echo ( $hide_product_description === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][hide_product_description]" <?php echo ( $hide_product_description === 'yes' ? 'value="1"' : '' ); ?>/>
					<span><?php echo __( 'Hide Description', 'ultimatewoo-pro' ); ?>
					<?php echo wc_help_tip( __( 'Check this option to hide the selected Component Option description.', 'ultimatewoo-pro' ) ); ?>
				</div>
				<div class="component_selection_details_option">
					<input type="checkbox" class="checkbox"<?php echo ( $hide_product_thumbnail === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][hide_product_thumbnail]" <?php echo ( $hide_product_thumbnail === 'yes' ? 'value="1"' : '' ); ?>/>
					<span><?php echo __( 'Hide Thumbnail', 'ultimatewoo-pro' ); ?>
					<?php echo wc_help_tip( __( 'Check this option to hide the selected Component Option thumbnail.', 'ultimatewoo-pro' ) ); ?>
				</div>
				<div class="component_selection_details_option">
					<input type="checkbox" class="checkbox"<?php echo ( $hide_product_price === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][hide_product_price]" <?php echo ( $hide_product_price === 'yes' ? 'value="1"' : '' ); ?>/>
					<span><?php echo __( 'Hide Price', 'ultimatewoo-pro' ); ?>
					<?php echo wc_help_tip( __( 'Check this option to hide the selected Component Option price.', 'ultimatewoo-pro' ) ); ?>
				</div>
				<?php

				/**
				 * Action 'woocommerce_composite_component_admin_config_filter_options':
				 * Add your own custom filter config options here.
				 *
				 * @param  string  $component_id
				 * @param  array   $component_data
				 * @param  string  $composite_id
				 */
				do_action( 'woocommerce_composite_component_admin_advanced_selection_details_options', $id, $data, $product_id );

				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component subtotal visibility options.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_subtotal_visibility_options( $id, $data, $product_id ) {

		$hide_in_product = isset( $data[ 'hide_subtotal_product' ] ) ? $data[ 'hide_subtotal_product' ] : 'no';
		$hide_in_cart    = isset( $data[ 'hide_subtotal_cart' ] ) ? $data[ 'hide_subtotal_cart' ] : 'no';
		$hide_in_orders  = isset( $data[ 'hide_subtotal_orders' ] ) ? $data[ 'hide_subtotal_orders' ] : 'no';

		?>
		<div class="component_subtotal_visibility">
			<div class="form-field">
				<label for="component_subtotal_visibility_<?php echo $id; ?>">
					<?php echo __( 'Component Price Visibility', 'ultimatewoo-pro' ); ?>
				</label>
				<div class="component_subtotal_visibility_option">
					<input type="checkbox" class="checkbox"<?php echo ( $hide_in_product === 'no' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][hide_subtotal_product]" <?php echo ( $hide_in_product === 'no' ? 'value="1"' : '' ); ?>/>
					<span><?php echo __( 'Composite', 'ultimatewoo-pro' ); ?>
					<?php echo wc_help_tip( __( 'Controls the visibility of the Component subtotal in the single-product template of the Composite.', 'ultimatewoo-pro' ) ); ?>
				</div>
				<div class="component_subtotal_visibility_option">
					<input type="checkbox" class="checkbox"<?php echo ( $hide_in_cart === 'no' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][hide_subtotal_cart]" <?php echo ( $hide_in_cart === 'no' ? 'value="1"' : '' ); ?>/>
					<span><?php echo __( 'Cart/checkout', 'ultimatewoo-pro' ); ?>
					<?php echo wc_help_tip( __( 'Controls the visibility of the Component price/subtotal in cart/checkout templates.', 'ultimatewoo-pro' ) ); ?>
				</div>
				<div class="component_subtotal_visibility_option">
					<input type="checkbox" class="checkbox"<?php echo ( $hide_in_orders === 'no' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][hide_subtotal_orders]" <?php echo ( $hide_in_orders === 'no' ? 'value="1"' : '' ); ?>/>
					<span><?php echo __( 'Order details', 'ultimatewoo-pro' ); ?>
					<?php echo wc_help_tip( __( 'Controls the visibility of the Component price/subtotal in order details &amp; e-mail templates.', 'ultimatewoo-pro' ) ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component 'show orderby' option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_sort_filter_show_orderby( $id, $data, $product_id ) {

		$show_orderby = isset( $data[ 'show_orderby' ] ) ? $data[ 'show_orderby' ] : 'no';

		?>
		<div class="component_show_orderby group_show_orderby" >
			<div class="form-field">
				<label for="group_show_orderby_<?php echo $id; ?>">
					<?php echo __( 'Options Sorting', 'ultimatewoo-pro' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $show_orderby === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][show_orderby]" <?php echo ( $show_orderby === 'yes' ? 'value="1"' : '' ); ?>/>
				<?php echo wc_help_tip( __( 'Check this option to allow sorting the available Component Options by popularity, rating, newness or price.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component 'show filters' option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_sort_filter_show_filters( $id, $data, $product_id ) {

		$show_filters         = isset( $data[ 'show_filters' ] ) ? $data[ 'show_filters' ] : 'no';
		$selected_taxonomies  = isset( $data[ 'attribute_filters' ] ) ? $data[ 'attribute_filters' ] : array();
		$attribute_taxonomies = wc_get_attribute_taxonomies();

		?>
		<div class="component_show_filters group_show_filters" >
			<div class="form-field">
				<label for="group_show_filters_<?php echo $id; ?>">
					<?php echo __( 'Options Filtering', 'ultimatewoo-pro' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $show_filters === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][show_filters]" <?php echo ( $show_filters === 'yes' ? 'value="1"' : '' ); ?>/>
				<?php echo wc_help_tip( __( 'Check this option to configure and display layered attribute filters. Useful for narrowing down Component Options more easily', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div><?php

		if ( $attribute_taxonomies ) {

			$attribute_array = array();

			foreach ( $attribute_taxonomies as $tax ) {

				if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) )
					$attribute_array[ $tax->attribute_id ] = $tax->attribute_label;
			}

			?><div class="component_filters group_filters" >
				<div class="bto_attributes_selector bto_multiselect">
					<div class="form-field">
						<label><?php echo __( 'Active Attribute Filters', 'ultimatewoo-pro' ); ?>:</label>
						<select id="bto_attribute_ids_<?php echo $id; ?>" name="bto_data[<?php echo $id; ?>][attribute_filters][]" style="width: 75%" class="multiselect wc-enhanced-select" multiple="multiple" data-placeholder="<?php echo  __( 'Select product attributes&hellip;', 'ultimatewoo-pro' ); ?>"><?php

							foreach ( $attribute_array as $attribute_taxonomy_id => $attribute_taxonomy_label )
								echo '<option value="' . $attribute_taxonomy_id . '" ' . selected( in_array( $attribute_taxonomy_id, $selected_taxonomies ), true, false ).'>' . $attribute_taxonomy_label . '</option>';

						?></select>
					</div>
				</div><?php

				/**
				 * Action 'woocommerce_composite_component_admin_config_filter_options':
				 * Add your own custom filter config options here.
				 *
				 * @param  string  $component_id
				 * @param  array   $component_data
				 * @param  string  $composite_id
				 */
				do_action( 'woocommerce_composite_component_admin_config_filter_options', $id, $data, $product_id );

			?></div><?php
		}
	}

	/**
	 * Add component config title option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_config_title( $id, $data, $product_id ) {

		$title    = isset( $data[ 'title' ] ) ? $data[ 'title' ] : '';
		$position = isset( $data[ 'position' ] ) ? $data[ 'position' ] : $id;

		?>
		<div class="component_title group_title">
			<div class="form-field">
				<label>
					<?php echo __( 'Component Name', 'ultimatewoo-pro' ); ?>
				</label>
				<input type="text" class="group_title component_text_input" name="bto_data[<?php echo $id; ?>][title]" value="<?php echo $title; ?>"/><?php echo wc_help_tip( __( 'Name or title of this Component.', 'ultimatewoo-pro' ) ); ?>
				<input type="hidden" name="bto_data[<?php echo $id; ?>][position]" class="group_position" value="<?php echo $position; ?>" />
			</div>
		</div>
		<?php
	}

	/**
	 * Add component config description option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_config_description( $id, $data, $product_id ) {

		$description = isset( $data[ 'description' ] ) ? $data[ 'description' ] : '';

		?>
		<div class="component_description group_description">
			<div class="form-field">
				<label>
					<?php echo __( 'Component Description', 'ultimatewoo-pro' ); ?>
				</label>
				<textarea class="group_description" name="bto_data[<?php echo $id; ?>][description]" id="group_description_<?php echo $id; ?>" placeholder="" rows="2" cols="20"><?php echo esc_textarea( $description ); ?></textarea><?php echo wc_help_tip( __( 'Optional short description of this Component.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component placeholder image.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_config_image( $id, $data, $product_id ) {

		$image_id = isset( $data[ 'thumbnail_id' ] ) ? $data[ 'thumbnail_id' ] : '';
		$image    = $image_id ? wp_get_attachment_thumb_url( $image_id ) : '';

		?>
		<div class="component_image group_image">
			<div class="form-field">
				<label>
					<?php echo __( 'Component Image', 'ultimatewoo-pro' ); ?>
				</label>
				<a href="#" class="upload_component_image_button <?php echo $image_id ? 'has_image': ''; ?>"><span class="prompt"><?php echo __( 'Select image', 'ultimatewoo-pro' ); ?></span><img src="<?php if ( ! empty( $image ) ) echo esc_attr( $image ); else echo esc_attr( wc_placeholder_img_src() ); ?>" /><input type="hidden" name="bto_data[<?php echo $id; ?>][thumbnail_id]" class="image" value="<?php echo $image_id; ?>" /></a>
				<?php echo wc_help_tip( __( 'Placeholder image to use in configuration summaries. When a Component Option is chosen, the placeholder image will be replaced by the image associated with the selected product. Note: Configuration summary sections are displayed when using a) the Stepped/Componentized layouts and b) the Composite Product Summary widget.', 'ultimatewoo-pro' ) ); ?>
				<a href="#" class="remove_component_image_button <?php echo $image_id ? 'has_image': ''; ?>"><?php echo __( 'Remove image', 'ultimatewoo-pro' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component config multi select products option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_config_options( $id, $data, $product_id ) {

		$query_type          = isset( $data[ 'query_type' ] ) ? $data[ 'query_type' ] : 'product_ids';
		$product_categories  = ( array ) get_terms( 'product_cat', array( 'get' => 'all' ) );
		$selected_categories = isset( $data[ 'assigned_category_ids' ] ) ? $data[ 'assigned_category_ids' ] : array();

		$select_by = array(
			'product_ids'  => __( 'Select products', 'ultimatewoo-pro' ),
			'category_ids' => __( 'Select categories', 'ultimatewoo-pro' )
		);

		/**
		 * Filter the default query types.
		 *
		 * @param  array  $select_by
		 */
		$select_by = apply_filters( 'woocommerce_composite_component_query_types', $select_by, $data, $product_id );

		?>
		<div class="component_query_type bto_query_type">
			<div class="form-field">
				<label>
					<?php echo __( 'Component Options', 'ultimatewoo-pro' ); ?>
				</label>
				<select class="bto_query_type" name="bto_data[<?php echo $id; ?>][query_type]"><?php

					foreach ( $select_by as $key => $description ) {
						?><option value="<?php echo $key; ?>" <?php selected( $query_type, $key, true ); ?>><?php echo $description; ?></option><?php
					}

				?></select>
				<?php echo wc_help_tip( __( 'Every Component includes an assortment of products to choose from - the <strong>Component Options</strong>. You can add products individually, or select a category to add all associated products.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>

		<div class="component_selector bto_selector bto_query_type_selector bto_multiselect bto_query_type_product_ids">
			<div class="form-field"><?php

				$product_id_options = array();

				if ( ! empty( $data[ 'assigned_ids' ] ) ) {

					$component_options = $data[ 'assigned_ids' ];

					foreach ( $component_options as $component_option_id ) {

						$component_option_cache_key = 'component_option_' . $component_option_id;
						$component_option           = WC_CP_Helpers::cache_get( $component_option_cache_key );

						if ( null === $component_option ) {
							$component_option = wc_get_product( $component_option_id );
							WC_CP_Helpers::cache_set( $component_option_cache_key, $component_option );
						}

						$product_title = WC_CP_Helpers::get_product_title( $component_option );

						if ( $product_title ) {
							$product_id_options[ $component_option_id ] = $product_title;
						}
					}
				}

				?><input type="hidden" id="bto_ids_<?php echo $id; ?>" name="bto_data[<?php echo $id; ?>][assigned_ids]" class="wc-product-search" style="width: 75%;" data-limit="500" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products" data-multiple="true" data-selected="<?php

					echo esc_attr( json_encode( $product_id_options ) );

				?>" value="<?php echo implode( ',', array_keys( $product_id_options ) ); ?>" /><?php

			?></div>
		</div>

		<div class="component_category_selector bto_category_selector bto_query_type_selector bto_multiselect bto_query_type_category_ids">
			<div class="form-field">

				<select id="bto_category_ids_<?php echo $id; ?>" name="bto_data[<?php echo $id; ?>][assigned_category_ids][]" style="width: 75%" class="multiselect wc-enhanced-select" multiple="multiple" data-placeholder="<?php echo  __( 'Select product categories&hellip;', 'ultimatewoo-pro' ); ?>"><?php

					foreach ( $product_categories as $product_category )
						echo '<option value="' . $product_category->term_id . '" ' . selected( in_array( $product_category->term_id, $selected_categories ), true, false ).'>' . $product_category->name . '</option>';

				?></select>
			</div>
		</div><?php

		/**
		 * Action 'woocommerce_composite_component_admin_config_query_options'.
		 * Use this hook to display additional query type options associated with a custom query type added via {@see woocommerce_composite_component_query_types}.
		 *
		 * @param  $id          int
		 * @param  $data        array
		 * @param  $product_id  string
		 */
		do_action( 'woocommerce_composite_component_admin_config_query_options', $id, $data, $product_id );
	}

	/**
	 * Add component options style option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_config_options_style( $id, $data, $product_id ) {

		?><div class="component_options_style group_options_style">
			<div class="form-field">
				<label>
					<?php _e( 'Options Style', 'ultimatewoo-pro' ); ?>
				</label>
				<select name="bto_data[<?php echo $id; ?>][selection_mode]"><?php

					if ( ! empty( $data[ 'selection_mode' ] ) ) {
						$mode = $data[ 'selection_mode' ];
					} else {

						$mode = get_post_meta( $product_id, '_bto_selection_mode', true );

						if ( empty( $mode ) ) {
							$mode = 'dropdowns';
						}
					}

					foreach ( WC_CP_Component::get_options_styles() as $style ) {
						echo '<option ' . selected( $mode, $style[ 'id' ], false ) . ' value="' . $style[ 'id' ] . '">' . $style[ 'description' ] . '</option>';
					}

				?></select>
				<?php echo wc_help_tip( __( '<strong>Thumbnails</strong> &ndash; Component Options are presented as thumbnails, paginated and arranged in columns similar to the main shop loop.</br></br><strong>Dropdown</strong> &ndash; Component Options are listed in a dropdown menu.</br></br><strong>Radio Buttons</strong> &ndash; Component Options are listed as radio buttons.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div><?php
	}

	/**
	 * Add component config default selection option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_config_default_option( $id, $data, $product_id ) {

		global $composite_product_object_data;

		if ( empty( $composite_product_object_data ) ) {
			$composite_product_object_data = array();
		}

		$component_id   = isset( $data[ 'component_id' ] ) ? $data[ 'component_id' ] : '';
		$ajax_threshold = apply_filters( 'woocommerce_composite_component_admin_defaults_ajax_threshold', 30, $component_id, $product_id );

		?><div class="component_default_selector default_selector">
			<div class="form-field">
				<label>
					<?php echo __( 'Default Option', 'ultimatewoo-pro' ); ?>
				</label><?php

				$component_options_cache_key = 'component_' . $component_id . '_options';
				$component_options           = WC_CP_Helpers::cache_get( $component_options_cache_key );

				if ( null === $component_options ) {
					$component_options = WC_CP_Component::query_component_options( $data );
					WC_CP_Helpers::cache_set( $component_options_cache_key, $component_options );
				}

				if ( ! empty( $component_options ) ) {

					// If > 30, use ajax search.
					$use_ajax = count( $component_options ) >= $ajax_threshold || ( isset( $composite_product_object_data[ 'component_options_count' ] ) && isset( $composite_product_object_data[ 'component_options_ajax_threshold' ] ) && $composite_product_object_data[ 'component_options_count' ] >= $composite_product_object_data[ 'component_options_ajax_threshold' ] );

					if ( false === $use_ajax ) {

						?><select id="group_default_<?php echo $id; ?>" name="bto_data[<?php echo $id; ?>][default_id]">
							<option value=""><?php echo __( 'No default option&hellip;', 'ultimatewoo-pro' ); ?></option><?php

							$selected_default = $data[ 'default_id' ];

							foreach ( $component_options as $component_option_id ) {

								$component_option_cache_key = 'component_option_' . $component_option_id;
								$component_option           = WC_CP_Helpers::cache_get( $component_option_cache_key );

								if ( null === $component_option ) {
									$component_option = wc_get_product( $component_option_id );
									WC_CP_Helpers::cache_set( $component_option_cache_key, $component_option );
								}

								$product_title = WC_CP_Helpers::get_product_title( $component_option );

								if ( $product_title ) {
									echo '<option value="' . $component_option_id . '" ' . selected( $selected_default, $component_option_id, false ) . '>'. $product_title . '</option>';
								}
							}

						?></select><?php

					} else {

						$selected_default = $data[ 'default_id' ];
						$product_title    = '';

						if ( $selected_default ) {
							$product_title = WC_CP_Helpers::get_product_title( $selected_default );
						}

						?><input type="hidden" id="group_default_<?php echo $id; ?>" name="bto_data[<?php echo $id; ?>][default_id]" class="wc-product-search" style="width: 75%;" data-include="<?php echo esc_attr( json_encode( array( 'composite_id' => $product_id, 'component_id' => $component_id ) ) ); ?>" data-limit="100" data-placeholder="<?php _e( 'Search for a product&hellip;', 'ultimatewoo-pro' ); ?>" data-allow_clear="true" data-action="woocommerce_json_search_products_in_component" data-multiple="false" data-selected="<?php

							echo esc_attr( $product_title ? $product_title : __( 'No default selected. Search for a product&hellip;', 'ultimatewoo-pro' ) );

						?>" value="<?php echo $product_title ? $selected_default : ''; ?>" /><?php
					}

					echo wc_help_tip( __( 'Select a product to use as the default, pre-selected Component Option.', 'ultimatewoo-pro' ) );

				} else {
					?><div class="prompt"><em><?php _e( 'To choose a Default Option, add some products in <strong>Basic Settings > Component Options</strong> and click <strong>Save Configuration</strong>&hellip;', 'ultimatewoo-pro' ); ?></em></div><?php
				}

			?></div>
		</div>
		<?php
	}

	/**
	 * Add component config min quantity option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_config_quantity_min( $id, $data, $product_id ) {

		$quantity_min = isset( $data[ 'quantity_min' ] ) ? $data[ 'quantity_min' ] : 1;

		?>
		<div class="group_quantity_min">
			<div class="form-field">
				<label for="group_quantity_min_<?php echo $id; ?>">
					<?php echo __( 'Min Quantity', 'ultimatewoo-pro' ); ?>
				</label>
				<input type="number" class="group_quantity_min" name="bto_data[<?php echo $id; ?>][quantity_min]" id="group_quantity_min_<?php echo $id; ?>" value="<?php echo $quantity_min; ?>" placeholder="" step="1" min="0" />
				<?php echo wc_help_tip( __( 'Set a minimum quantity for the selected Component Option.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component config max quantity option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_config_quantity_max( $id, $data, $product_id ) {

		$quantity_max = isset( $data[ 'quantity_max' ] ) ? $data[ 'quantity_max' ] : 1;

		?>
		<div class="group_quantity_max">
			<div class="form-field">
				<label for="group_quantity_max_<?php echo $id; ?>">
					<?php echo __( 'Max Quantity', 'ultimatewoo-pro' ); ?>
				</label>
				<input type="number" class="group_quantity_max" name="bto_data[<?php echo $id; ?>][quantity_max]" id="group_quantity_max_<?php echo $id; ?>" value="<?php echo $quantity_max; ?>" placeholder="" step="1" min="0" />
				<?php echo wc_help_tip( __( 'Set a maximum quantity for the selected Component Option. Leave the field empty to allow an unlimited maximum quantity.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component config Shipped Individually option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_config_shipped_individually( $id, $data, $product_id ) {

		$shipped_individually = isset( $data[ 'shipped_individually' ] ) ? $data[ 'shipped_individually' ] : '';

		?>
		<div class="group_shipped_individually">
			<div class="form-field">
				<label for="group_shipped_individually_<?php echo $id; ?>">
					<?php echo __( 'Shipped Individually', 'ultimatewoo-pro' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $shipped_individually === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][shipped_individually]" <?php echo ( $shipped_individually === 'yes' ? ' value="1"' : '' ); ?> />
				<?php echo wc_help_tip( __( 'Check this option if this Component is shipped separately from the Composite.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component config Priced Individually option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_config_priced_individually( $id, $data, $product_id ) {

		$priced_individually = isset( $data[ 'priced_individually' ] ) ? $data[ 'priced_individually' ] : '';

		?>
		<div class="group_priced_individually">
			<div class="form-field">
				<label for="group_priced_individually_<?php echo $id; ?>">
					<?php echo __( 'Priced Individually', 'ultimatewoo-pro' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $priced_individually === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][priced_individually]" <?php echo ( $priced_individually === 'yes' ? ' value="1"' : '' ); ?> />
				<?php echo wc_help_tip( __( 'Check this option to have the price of this Component added to the base price of the Composite.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component config discount option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_config_discount( $id, $data, $product_id ) {

		$discount = isset( $data[ 'discount' ] ) ? $data[ 'discount' ] : '';

		?>
		<div class="group_discount">
			<div class="form-field">
				<label for="group_discount_<?php echo $id; ?>">
					<?php echo __( 'Discount %', 'ultimatewoo-pro' ); ?>
				</label>
				<input type="text" class="group_discount input-text wc_input_decimal" name="bto_data[<?php echo $id; ?>][discount]" id="group_discount_<?php echo $id; ?>" value="<?php echo $discount; ?>" placeholder="" />
				<?php echo wc_help_tip( __( 'Component-level discount applied to any selected Component Option when <strong>Priced Individually</strong> is checked.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Add component config optional option.
	 *
	 * @param  int    $id
	 * @param  array  $data
	 * @param  int    $product_id
	 * @return void
	 */
	public static function component_config_optional( $id, $data, $product_id ) {

		$optional = isset( $data[ 'optional' ] ) ? $data[ 'optional' ] : '';

		?>
		<div class="group_optional" >
			<div class="form-field">
				<label for="group_optional_<?php echo $id; ?>">
					<?php echo __( 'Optional', 'ultimatewoo-pro' ); ?>
				</label>
				<input type="checkbox" class="checkbox"<?php echo ( $optional === 'yes' ? ' checked="checked"' : '' ); ?> name="bto_data[<?php echo $id; ?>][optional]" <?php echo ( $optional === 'yes' ? ' value="1"' : '' ); ?> />
				<?php echo wc_help_tip( __( 'Checking this option will allow customers to proceed without making any selection for this Component at all.', 'ultimatewoo-pro' ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Adds the Composite Product write panel tabs.
	 *
	 * @param  array  $tabs
	 * @return array
	 */
	public static function composite_product_data_tabs( $tabs ) {

		$tabs[ 'cp_components' ] = array(
			'label'  => __( 'Components', 'ultimatewoo-pro' ),
			'target' => 'bto_product_data',
			'class'  => array( 'show_if_composite', 'composite_product_options', 'bto_product_tab' )
		);

		$tabs[ 'cp_scenarios' ] = array(
			'label'  => __( 'Scenarios', 'ultimatewoo-pro' ),
			'target' => 'bto_scenario_data',
			'class'  => array( 'show_if_composite', 'composite_scenarios', 'bto_product_tab' )
		);

		$tabs[ 'inventory' ][ 'class' ][] = 'show_if_composite';

		return $tabs;
	}

	/**
	 * Adds the Hide Price option and Base Price fields.
	 *
	 * @return void
	 */
	public static function composite_pricing_options() {

		global $thepostid;

		// Hide Shop Price.
		woocommerce_wp_checkbox( array( 'id' => '_bto_hide_shop_price', 'wrapper_class' => 'composite_pricing show_if_composite', 'label' => __( 'Hide Price', 'ultimatewoo-pro' ), 'desc_tip' => true, 'description' => __( 'Disable all internal price calculations and hide the Composite price displayed in the shop catalog and product summary.', 'ultimatewoo-pro' ) ) );

		// Base Price fields to copy.
		$base_regular_price = get_post_meta( $thepostid, '_bto_base_regular_price', true );
		$base_sale_price    = get_post_meta( $thepostid, '_bto_base_sale_price', true );

		?><div class="wc_cp_price_fields" style="display:none">
			<input type="hidden" id="_wc_cp_base_regular_price" name="wc_cp_base_regular_price_flip" value="<?php echo wc_format_localized_price( $base_regular_price ); ?>"/>
			<input type="hidden" id="_wc_cp_base_sale_price" name="wc_cp_base_sale_price_flip" value="<?php echo wc_format_localized_price( $base_sale_price ); ?>"/>
		</div><?php
	}

	/**
	 * Add Composited Products stock note.
	 *
	 * @return void
	 */
	public static function composite_stock_info() {
		?><span class="composite_stock_msg show_if_composite">
				<?php echo wc_help_tip( __( 'By default, the sale of a product within a composite has the same effect on its stock as an individual sale. There are no separate inventory settings for composited items. However, managing stock at composite level can be very useful for allocating composite stock quota, or for keeping track of composited item sales.', 'ultimatewoo-pro' ) ); ?>
		</span><?php
	}

	/**
	 * Components and Scenarios write panels.
	 *
	 * @return void
	 */
	public static function composite_write_panel() {

		global $post, $composite_product_object_data;

		$composite_product_object_data = array();
		$merged_component_options      = array();

		$composite_data = get_post_meta( $post->ID, '_bto_data', true );
		$scenarios_data = get_post_meta( $post->ID, '_bto_scenario_data', true );

		if ( ! empty( $composite_data ) ) {
			foreach ( $composite_data as $component_id => $component_data ) {

				// Add the component ID here.
				if ( ! isset( $component_data[ 'component_id' ] ) ) {
					$composite_data[ $component_id ][ 'component_id' ] = $component_id;
				}

				// Add the composite ID here as well.
				if ( ! isset( $component_data[ 'composite_id' ] ) ) {
					$composite_data[ $component_id ][ 'composite_id' ] = $post->ID;
				}
			}
		}

		?>
		<div id="bto_product_data" class="bto_panel panel woocommerce_options_panel wc-metaboxes-wrapper"><?php

			if ( ! empty( $composite_data ) ) {

				$composite_product_object_data[ 'component_options_count' ] = 0;

				foreach ( $composite_data as $component_id => $component_data ) {

					$component_options_cache_key = 'component_' . $component_id . '_options';
					$component_options           = WC_CP_Helpers::cache_get( $component_options_cache_key );

					if ( null === $component_options ) {
						$component_options = WC_CP_Component::query_component_options( $component_data );
						WC_CP_Helpers::cache_set( $component_options_cache_key, $component_options );
					}

					$merged_component_options = array_unique( array_merge( $merged_component_options, $component_options ) );
				}

				$composite_product_object_data[ 'component_options_count' ]          = count( $merged_component_options );
				$composite_product_object_data[ 'component_options_ajax_threshold' ] = apply_filters( 'woocommerce_composite_admin_component_options_ajax_threshold', 200 );
			}

			/**
			 * Action 'woocommerce_composite_admin_html'.
			 *
			 * @param   array   $composite_data
			 * @param   string  $post_id
			 *
			 * @hooked {@see composite_layout_options}    - 10
			 * @hooked {@see composite_component_options} - 15
			 */
			do_action( 'woocommerce_composite_admin_html', $composite_data, $post->ID );

		?></div>
		<div id="bto_scenario_data" class="bto_panel panel woocommerce_options_panel wc-metaboxes-wrapper">
			<div class="options_group">

				<div id="bto_scenarios_inner"><?php

					if ( ! empty( $composite_data ) ) {

						?><div id="bto-scenarios-message" class="inline notice woocommerce-message">
							<span><?php
								$tip = '<a href="#" class="tips" data-tip="' . __( 'Use Scenarios to create dependencies between Component Options, or to conditionally hide Components. Developers may use the Scenarios API to define configuration conditions for triggering custom actions.', 'ultimatewoo-pro' ) . '">' . __( 'help', 'ultimatewoo-pro' ) . '</a>';
								echo sprintf( __( 'Need %s to set up <strong>Scenarios</strong> ?', 'ultimatewoo-pro' ), $tip );
							?></span>
							<span><a class="button-primary" href="<?php echo 'http://docs.woocommerce.com/document/composite-products'; ?>" target="_blank"><?php _e( 'Learn more', 'woocommerce' ); ?></a></span>
						</div>
						<p class="toolbar">
							<span class="bulk_toggle_wrapper">
								<span class="disabler"></span>
								<a href="#" class="expand_all"><?php _e( 'Expand all', 'woocommerce' ); ?></a>
								<a href="#" class="close_all"><?php _e( 'Close all', 'woocommerce' ); ?></a>
							</span>
						</p>

						<div class="bto_scenarios wc-metaboxes"><?php

							if ( ! empty( $scenarios_data ) ) {

								$i = 0;

								foreach ( $scenarios_data as $scenario_id => $scenario_data ) {

									$scenario_data[ 'scenario_id' ] = $scenario_id;

									/**
									 * Action 'woocommerce_composite_scenario_admin_html'.
									 *
									 * @param   int     $i
									 * @param   array   $scenario_data
									 * @param   array   $composite_data
									 * @param   string  $post_id
									 * @param   string  $state
									 *
									 * @hooked  {@see scenario_admin_html} - 10
									 */
									do_action( 'woocommerce_composite_scenario_admin_html', $i, $scenario_data, $composite_data, $post->ID, 'closed' );

									$i++;
								}
							}

						?></div>

						<p class="toolbar borderless">
							<button type="button" class="button button-primary add_bto_scenario"><?php _e( 'Add Scenario', 'ultimatewoo-pro' ); ?></button>
						</p><?php

					} else {

						?><div id="bto-scenarios-message" class="inline notice woocommerce-message">
							<span><?php _e( 'Scenarios can be defined only after creating and saving some Components on the <strong>Components</strong> tab.', 'ultimatewoo-pro' ); ?></span>
							<span><a class="button-primary" href="<?php echo 'http://docs.woocommerce.com/document/composite-products'; ?>" target="_blank"><?php _e( 'Learn more', 'woocommerce' ); ?></a></span>
						</div><?php
					}

				?></div>
			</div>
		</div><?php
	}

	/**
	 * Product options for post-1.6.2 product data section.
	 *
	 * @param  array $options
	 * @return array
	 */
	public static function add_composite_type_options( $options ) {

		$options[ 'virtual' ][ 'wrapper_class' ]      .= ' show_if_composite';
		$options[ 'downloadable' ][ 'wrapper_class' ] .= ' show_if_composite';

		return $options;
	}

	/**
	 * Process, verify and save composite product data.
	 *
	 * @param  int  $post_id
	 * @return void
	 */
	public static function process_composite_meta( $post_id ) {

		/*
		 * Base Prices.
		 */

		$date_from     = (string) isset( $_POST[ '_sale_price_dates_from' ] ) ? wc_clean( $_POST[ '_sale_price_dates_from' ] ) : '';
		$date_to       = (string) isset( $_POST[ '_sale_price_dates_to' ] ) ? wc_clean( $_POST[ '_sale_price_dates_to' ] )     : '';
		$regular_price = (string) isset( $_POST[ '_regular_price' ] ) ? wc_clean( $_POST[ '_regular_price' ] )                 : '';
		$sale_price    = (string) isset( $_POST[ '_sale_price' ] ) ? wc_clean( $_POST[ '_sale_price' ] )                       : '';

		update_post_meta( $post_id, '_bto_base_regular_price', '' === $regular_price ? '' : wc_format_decimal( $regular_price ) );
		update_post_meta( $post_id, '_bto_base_sale_price', '' === $sale_price ? '' : wc_format_decimal( $sale_price ) );

		if ( $date_to && ! $date_from ) {
			$date_from = date( 'Y-m-d' );
		}

		if ( '' !== $sale_price && '' === $date_to && '' === $date_from ) {
			update_post_meta( $post_id, '_bto_base_price', wc_format_decimal( $sale_price ) );
		} elseif ( '' !== $sale_price && $date_from && strtotime( $date_from ) <= strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
			update_post_meta( $post_id, '_bto_base_price', wc_format_decimal( $sale_price ) );
		} else {
			update_post_meta( $post_id, '_bto_base_price', '' === $regular_price ? '' : wc_format_decimal( $regular_price ) );
		}

		if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
			update_post_meta( $post_id, '_bto_base_price', '' === $regular_price ? '' : wc_format_decimal( $regular_price ) );
			update_post_meta( $post_id, '_bto_base_sale_price', '' );
		}

		/*
		 * Sold Individually options.
		 */

		if ( ! empty( $_POST[ '_bto_sold_individually' ] ) ) {

			$sold_individually = wc_clean( $_POST[ '_bto_sold_individually' ] );

			if ( $sold_individually === 'no' ) {
				update_post_meta( $post_id, '_sold_individually', 'no' );
				delete_post_meta( $post_id, '_bto_sold_individually' );
			} elseif ( in_array( $sold_individually, array( 'product', 'configuration' ) ) ) {
				update_post_meta( $post_id, '_sold_individually', 'yes' );
				update_post_meta( $post_id, '_bto_sold_individually', $sold_individually );
			}

		} else {
			delete_post_meta( $post_id, '_bto_sold_individually' );
		}

		/*
		 * Edit in cart option.
		 */

		if ( ! empty( $_POST[ '_bto_edit_in_cart' ] ) ) {
			update_post_meta( $post_id, '_bto_edit_in_cart', 'yes' );
		} else {
			update_post_meta( $post_id, '_bto_edit_in_cart', 'no' );
		}

		/*
		 * Hide shop price option.
		 */

		if ( ! empty( $_POST[ '_bto_hide_shop_price' ] ) ) {
			update_post_meta( $post_id, '_bto_hide_shop_price', 'yes' );
		} else {
			update_post_meta( $post_id, '_bto_hide_shop_price', 'no' );
		}

		self::save_configuration( $post_id, $_POST );
	}

	/**
	 * Save components and scenarios.
	 *
	 * @param  int    $post_id
	 * @param  array  $posted_composite_data
	 * @return array
	 */
	public static function save_configuration( $post_id, $posted_composite_data ) {

		global $wpdb;

		// Composite style.

		$composite_layout = 'single';

		if ( isset( $posted_composite_data[ 'bto_style' ] ) ) {
			$composite_layout = stripslashes( $posted_composite_data[ 'bto_style' ] );
		}

		update_post_meta( $post_id, '_bto_style', $composite_layout );

		// Process Composite Product Configuration.

		$zero_product_item_exists          = false;
		$individually_priced_options_count = 0;
		$composite_data                    = get_post_meta( $post_id, '_bto_data', true );

		if ( ! $composite_data ) {
			$composite_data = array();
		}

		if ( isset( $posted_composite_data[ 'bto_data' ] ) ) {

			/*--------------------------*/
			/*  Components.             */
			/*--------------------------*/

			$counter  = 0;
			$ordering = array();

			foreach ( $posted_composite_data[ 'bto_data' ] as $row_id => $post_data ) {

				$bto_ids     = isset( $post_data[ 'assigned_ids' ] ) ? $post_data[ 'assigned_ids' ] : '';
				$bto_cat_ids = isset( $post_data[ 'assigned_category_ids' ] ) ? $post_data[ 'assigned_category_ids' ] : '';

				$group_id    = isset ( $post_data[ 'group_id' ] ) ? stripslashes( $post_data[ 'group_id' ] ) : ( current_time( 'timestamp' ) + $counter );
				$counter++;

				$composite_data[ $group_id ] = array();

				/*
				 * Save query type.
				 */

				if ( isset( $post_data[ 'query_type' ] ) && ! empty( $post_data[ 'query_type' ] ) ) {
					$composite_data[ $group_id ][ 'query_type' ] = stripslashes( $post_data[ 'query_type' ] );
				} else {
					$composite_data[ $group_id ][ 'query_type' ] = 'product_ids';
				}

				if ( ! empty( $bto_ids ) ) {

					if ( is_array( $bto_ids ) ) {
						$bto_ids = array_map( 'intval', $post_data[ 'assigned_ids' ] );
					} else {
						$bto_ids = array_filter( array_map( 'intval', explode( ',', $post_data[ 'assigned_ids' ] ) ) );
					}

					foreach ( $bto_ids as $key => $id ) {

						// Get product type.
						$terms        = get_the_terms( $id, 'product_type' );
						$product_type = ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';

						if ( $id && $id > 0 && in_array( $product_type, apply_filters( 'woocommerce_composite_products_supported_types', array( 'simple', 'variable', 'bundle' ) ) ) && $post_id != $id ) {

							// Check that product exists
							if ( ! get_post( $id ) ) {
								continue;
							}

							$error = apply_filters( 'woocommerce_composite_products_custom_type_save_error', false, $id );

							if ( $error ) {
								self::add_notice( $error, 'error' );
								continue;
							}

							// Save assigned ids.
							$composite_data[ $group_id ][ 'assigned_ids' ][] = $id;
						}
					}

					if ( ! empty( $composite_data[ $group_id ][ 'assigned_ids' ] ) ) {
						$composite_data[ $group_id ][ 'assigned_ids' ] = array_unique( $composite_data[ $group_id ][ 'assigned_ids' ] );
					}

				}

				if ( ! empty( $bto_cat_ids ) ) {

					$bto_cat_ids = array_map( 'absint', $post_data[ 'assigned_category_ids' ] );

					$composite_data[ $group_id ][ 'assigned_category_ids' ] = array_values( $bto_cat_ids );
				}

				// True if no products were added.
				if ( ( $composite_data[ $group_id ][ 'query_type' ] === 'product_ids' && empty( $composite_data[ $group_id ][ 'assigned_ids' ] ) ) || ( $composite_data[ $group_id ][ 'query_type' ] === 'category_ids' && empty( $composite_data[ $group_id ][ 'assigned_category_ids' ] ) ) ) {

					unset( $composite_data[ $group_id ] );
					$zero_product_item_exists = true;
					continue;
				}

				// Run query to get component option ids.
				$component_options = WC_CP_Component::query_component_options( $composite_data[ $group_id ] );

				/*
				 * Save selection style.
				 */

				$component_options_style = 'dropdowns';

				if ( isset( $post_data[ 'selection_mode' ] ) ) {
					$component_options_style = stripslashes( $post_data[ 'selection_mode' ] );
				}

				$composite_data[ $group_id ][ 'selection_mode' ] = $component_options_style;

				/*
				 * Save default preferences.
				 */

				if ( ! empty( $post_data[ 'default_id' ] ) && count( $component_options ) > 0 ) {

					if ( in_array( $post_data[ 'default_id' ], $component_options ) )
						$composite_data[ $group_id ][ 'default_id' ] = stripslashes( $post_data[ 'default_id' ] );
					else {
						$composite_data[ $group_id ][ 'default_id' ] = '';
					}

				} else {

					// If the component option is only one, set it as default.
					if ( count( $component_options ) === 1 && ! isset( $post_data[ 'optional' ] ) ) {
						$composite_data[ $group_id ][ 'default_id' ] = $component_options[0];
					} else {
						$composite_data[ $group_id ][ 'default_id' ] = '';
					}
				}

				/*
				 * Save title preferences.
				 */

				if ( ! empty( $post_data[ 'title' ] ) ) {
					$composite_data[ $group_id ][ 'title' ] = strip_tags( stripslashes( $post_data[ 'title' ] ) );
				} else {

					$composite_data[ $group_id ][ 'title' ] = 'Untitled Component';
					self::add_notice( __( 'Please give a valid <strong>Name</strong> to each Component before saving.', 'ultimatewoo-pro' ), 'error' );

					if ( isset( $posted_composite_data[ 'post_status' ] ) && $posted_composite_data[ 'post_status' ] === 'publish' ) {
						global $wpdb;
						$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );
					}
				}

				/*
				 * Unpaginated selections style notice.
				 */

				if ( ! WC_CP_Component::options_style_supports( $component_options_style, 'pagination' ) ) {
					$unpaginated_options_count = count( $component_options );

					if ( $unpaginated_options_count > 30 ) {
						$dropdowns_prompt = sprintf( __( 'You have added %1$s product options to "%2$s". To reduce the load on your server, it is recommended to use the <strong>Product Thumbnails</strong> Options Style, which enables a paginated display of Component Options.', 'ultimatewoo-pro' ), $unpaginated_options_count, strip_tags( stripslashes( $post_data[ 'title' ] ) ) );
						self::add_notice( $dropdowns_prompt, 'warning' );
					}
				}

				/*
				 * Save description.
				 */

				if ( ! empty( $post_data[ 'description' ] ) ) {
					$composite_data[ $group_id ][ 'description' ] = wp_kses_post( stripslashes( $post_data[ 'description' ] ) );
				} else {
					$composite_data[ $group_id ][ 'description' ] = '';
				}

				/*
				 * Save image.
				 */

				if ( ! empty( $post_data[ 'thumbnail_id' ] ) ) {
					$composite_data[ $group_id ][ 'thumbnail_id' ] = wc_clean( $post_data[ 'thumbnail_id' ] );
				} else {
					$composite_data[ $group_id ][ 'thumbnail_id' ] = '';
				}

				/*
				 * Save min quantity data.
				 */

				if ( isset( $post_data[ 'quantity_min' ] ) && is_numeric( $post_data[ 'quantity_min' ] ) ) {

					$quantity_min = absint( $post_data[ 'quantity_min' ] );

					if ( $quantity_min >= 0 ) {
						$composite_data[ $group_id ][ 'quantity_min' ] = $quantity_min;
					} else {
						$composite_data[ $group_id ][ 'quantity_min' ] = 1;

						$error = sprintf( __( 'The <strong>Min Quantity</strong> entered for "%s" was not valid and has been reset. Please enter a non-negative integer value.', 'ultimatewoo-pro' ), strip_tags( stripslashes( $post_data[ 'title' ] ) ) );
						self::add_notice( $error, 'error' );
					}

				} else {
					// If its not there, it means the product was just added.
					$composite_data[ $group_id ][ 'quantity_min' ] = 1;

					$error = sprintf( __( 'The <strong>Min Quantity</strong> entered for "%s" was not valid and has been reset. Please enter a non-negative integer value.', 'ultimatewoo-pro' ), strip_tags( stripslashes( $post_data[ 'title' ] ) ) );
					self::add_notice( $error, 'error' );
				}

				$quantity_min = $composite_data[ $group_id ][ 'quantity_min' ];

				/*
				 * Save max quantity data.
				 */

				if ( isset( $post_data[ 'quantity_max' ] ) && ( is_numeric( $post_data[ 'quantity_max' ] ) || $post_data[ 'quantity_max' ] === '' ) ) {

					$quantity_max = $post_data[ 'quantity_max' ] !== '' ? absint( $post_data[ 'quantity_max' ] ) : '';

					if ( $quantity_max === '' || ( $quantity_max > 0 && $quantity_max >= $quantity_min ) ) {
						$composite_data[ $group_id ][ 'quantity_max' ] = $quantity_max;
					} else {
						$composite_data[ $group_id ][ 'quantity_max' ] = 1;

						$error = sprintf( __( 'The <strong>Max Quantity</strong> you entered for "%s" was not valid and has been reset. Please enter a positive integer value greater than (or equal to) <strong>Min Quantity</strong>, or leave the field empty.', 'ultimatewoo-pro' ), strip_tags( stripslashes( $post_data[ 'title' ] ) ) );
						self::add_notice( $error, 'error' );
					}

				} else {
					// If its not there, it means the product was just added.
					$composite_data[ $group_id ][ 'quantity_max' ] = 1;

					$error = sprintf( __( 'The <strong>Max Quantity</strong> you entered for "%s" was not valid and has been reset. Please enter a positive integer value greater than (or equal to) <strong>Min Quantity</strong>, or leave the field empty.', 'ultimatewoo-pro' ), strip_tags( stripslashes( $post_data[ 'title' ] ) ) );
					self::add_notice( $error, 'error' );
				}

				/*
				 * Save discount data.
				 */

				if ( isset( $post_data[ 'discount' ] ) ) {

					if ( is_numeric( $post_data[ 'discount' ] ) ) {

						$discount = wc_format_decimal( $post_data[ 'discount' ] );

						if ( $discount < 0 || $discount > 100 ) {

							$error = sprintf( __( 'The <strong>Discount</strong> value you entered for "%s" was not valid and has been reset. Please enter a positive number between 0-100.', 'ultimatewoo-pro' ), strip_tags( stripslashes( $post_data[ 'title' ] ) ) );
							self::add_notice( $error, 'error' );

							$composite_data[ $group_id ][ 'discount' ] = '';

						} else {
							$composite_data[ $group_id ][ 'discount' ] = $discount;
						}
					} else {
						$composite_data[ $group_id ][ 'discount' ] = '';
					}
				} else {
					$composite_data[ $group_id ][ 'discount' ] = '';
				}

				/*
				 * Save priced-individually data.
				 */

				if ( isset( $post_data[ 'priced_individually' ] ) ) {
					$composite_data[ $group_id ][ 'priced_individually' ] = 'yes';

					// Add up options.
					$individually_priced_options_count += count( $component_options );

				} else {
					$composite_data[ $group_id ][ 'priced_individually' ] = 'no';
				}

				/*
				 * Save priced-individually data.
				 */

				if ( isset( $post_data[ 'shipped_individually' ] ) ) {
					$composite_data[ $group_id ][ 'shipped_individually' ] = 'yes';
				} else {
					$composite_data[ $group_id ][ 'shipped_individually' ] = 'no';
				}

				/*
				 * Save optional data.
				 */

				if ( isset( $post_data[ 'optional' ] ) ) {
					$composite_data[ $group_id ][ 'optional' ] = 'yes';
				} else {
					$composite_data[ $group_id ][ 'optional' ] = 'no';
				}

				/*
				 * Save product title visiblity data.
				 */

				if ( isset( $post_data[ 'hide_product_title' ] ) ) {
					$composite_data[ $group_id ][ 'hide_product_title' ] = 'yes';
				} else {
					$composite_data[ $group_id ][ 'hide_product_title' ] = 'no';
				}

				/*
				 * Save product description visiblity data.
				 */

				if ( isset( $post_data[ 'hide_product_description' ] ) ) {
					$composite_data[ $group_id ][ 'hide_product_description' ] = 'yes';
				} else {
					$composite_data[ $group_id ][ 'hide_product_description' ] = 'no';
				}

				/*
				 * Save product thumbnail visiblity data.
				 */

				if ( isset( $post_data[ 'hide_product_thumbnail' ] ) ) {
					$composite_data[ $group_id ][ 'hide_product_thumbnail' ] = 'yes';
				} else {
					$composite_data[ $group_id ][ 'hide_product_thumbnail' ] = 'no';
				}

				/*
				 * Save product price visibility data.
				 */

				if ( isset( $post_data[ 'hide_product_price' ] ) ) {
					$composite_data[ $group_id ][ 'hide_product_price' ] = 'yes';
				} else {
					$composite_data[ $group_id ][ 'hide_product_price' ] = 'no';
				}

				/*
				 * Save component subtotal visibility data.
				 */

				if ( isset( $post_data[ 'hide_subtotal_product' ] ) ) {
					$composite_data[ $group_id ][ 'hide_subtotal_product' ] = 'no';
				} else {
					$composite_data[ $group_id ][ 'hide_subtotal_product' ] = 'yes';
				}

				/*
				 * Save component subtotal visibility data.
				 */

				if ( isset( $post_data[ 'hide_subtotal_cart' ] ) ) {
					$composite_data[ $group_id ][ 'hide_subtotal_cart' ] = 'no';
				} else {
					$composite_data[ $group_id ][ 'hide_subtotal_cart' ] = 'yes';
				}

				/*
				 * Save component subtotal visibility data.
				 */

				if ( isset( $post_data[ 'hide_subtotal_orders' ] ) ) {
					$composite_data[ $group_id ][ 'hide_subtotal_orders' ] = 'no';
				} else {
					$composite_data[ $group_id ][ 'hide_subtotal_orders' ] = 'yes';
				}

				/*
				 * Save show orderby data.
				 */

				if ( isset( $post_data[ 'show_orderby' ] ) ) {
					$composite_data[ $group_id ][ 'show_orderby' ] = 'yes';
				} else {
					$composite_data[ $group_id ][ 'show_orderby' ] = 'no';
				}

				/*
				 * Save show filters data.
				 */

				if ( isset( $post_data[ 'show_filters' ] ) ) {
					$composite_data[ $group_id ][ 'show_filters' ] = 'yes';
				} else {
					$composite_data[ $group_id ][ 'show_filters' ] = 'no';
				}

				/*
				 * Save filters.
				 */

				if ( ! empty( $post_data[ 'attribute_filters' ] ) ) {
					$attribute_filter_ids = array_map( 'absint', $post_data[ 'attribute_filters' ] );
					$composite_data[ $group_id ][ 'attribute_filters' ] = array_values( $attribute_filter_ids );
				}

				/*
				 * Prepare position data.
				 */

				if ( isset( $post_data[ 'position' ] ) ) {
					$ordering[ (int) $post_data[ 'position' ] ] = $group_id;
				} else {
					$ordering[ count( $ordering ) ] = $group_id;
				}

				/**
				 * Filter the component data before saving. Add custom errors via 'add_notice()'.
				 *
				 * @param  array   $component_data
				 * @param  array   $post_data
				 * @param  string  $component_id
				 * @param  string  $post_id
				 */
				$composite_data[ $group_id ] = apply_filters( 'woocommerce_composite_process_component_data', $composite_data[ $group_id ], $post_data, $group_id, $post_id );
			}

			ksort( $ordering );
			$ordered_composite_data = array();
			$ordering_loop          = 0;

			foreach ( $ordering as $group_id ) {
				$ordered_composite_data[ $group_id ]               = $composite_data[ $group_id ];
				$ordered_composite_data[ $group_id ][ 'position' ] = $ordering_loop;
				$ordering_loop++;
			}


			/*--------------------------*/
			/*  Scenarios.              */
			/*--------------------------*/

			// Convert posted data coming from select2 ajax inputs.
			$compat_scenario_data = array();

			if ( isset( $posted_composite_data[ 'bto_scenario_data' ] ) ) {
				foreach ( $posted_composite_data[ 'bto_scenario_data' ] as $scenario_id => $scenario_post_data ) {

					$compat_scenario_data[ $scenario_id ] = $scenario_post_data;

					if ( isset( $scenario_post_data[ 'component_data' ] ) ) {
						foreach ( $scenario_post_data[ 'component_data' ] as $component_id => $products_in_scenario ) {

							if ( ! empty( $products_in_scenario ) ) {
								if ( is_array( $products_in_scenario ) ) {
									$compat_scenario_data[ $scenario_id ][ 'component_data' ][ $component_id ] = array_unique( array_map( 'intval', $products_in_scenario ) );
								} else {
									$compat_scenario_data[ $scenario_id ][ 'component_data' ][ $component_id ] = array_unique( array_map( 'intval', explode( ',', $products_in_scenario ) ) );
								}
							} else {
								$compat_scenario_data[ $scenario_id ][ 'component_data' ][ $component_id ] = array();
							}
						}
					}
				}

				$posted_composite_data[ 'bto_scenario_data' ] = $compat_scenario_data;
			}
			// End conversion.

			// Start processing.
			$composite_scenario_data         = array();
			$ordered_composite_scenario_data = array();
			$compat_group_actions_exist      = false;
			$masked_rules_exist              = false;

			if ( isset( $posted_composite_data[ 'bto_scenario_data' ] ) ) {

				$counter = 0;
				$scenario_ordering = array();

				foreach ( $posted_composite_data[ 'bto_scenario_data' ] as $scenario_id => $scenario_post_data ) {

					$scenario_id = isset ( $scenario_post_data[ 'scenario_id' ] ) ? stripslashes( $scenario_post_data[ 'scenario_id' ] ) : ( current_time( 'timestamp' ) + $counter );
					$counter++;

					$composite_scenario_data[ $scenario_id ] = array();

					/*
					 * Save scenario title.
					 */

					if ( isset( $scenario_post_data[ 'title' ] ) && ! empty( $scenario_post_data[ 'title' ] ) ) {
						$composite_scenario_data[ $scenario_id ][ 'title' ] = strip_tags ( stripslashes( $scenario_post_data[ 'title' ] ) );
					} else {
						unset( $composite_scenario_data[ $scenario_id ] );
						self::add_notice( __( 'Please give a valid <strong>Name</strong> to all Scenarios before saving.', 'ultimatewoo-pro' ), 'error' );
						continue;
					}

					/*
					 * Save scenario description.
					 */

					if ( isset( $scenario_post_data[ 'description' ] ) && ! empty( $scenario_post_data[ 'description' ] ) ) {
						$composite_scenario_data[ $scenario_id ][ 'description' ] = wp_kses_post( stripslashes( $scenario_post_data[ 'description' ] ) );
					} else {
						$composite_scenario_data[ $scenario_id ][ 'description' ] = '';
					}

					/*
					 * Prepare position data.
					 */

					if ( isset( $scenario_post_data[ 'position' ] ) ) {
						$scenario_ordering[ ( int ) $scenario_post_data[ 'position' ] ] = $scenario_id;
					} else {
						$scenario_ordering[ count( $scenario_ordering ) ] = $scenario_id;
					}

					$composite_scenario_data[ $scenario_id ][ 'scenario_actions' ] = array();

					/*
					 * Save scenario action(s).
					 */

					// "Dependency Group" action.
					if ( isset( $scenario_post_data[ 'scenario_actions' ][ 'compat_group' ] ) ) {
						if ( ! empty( $scenario_post_data[ 'scenario_actions' ][ 'compat_group' ][ 'is_active' ] ) ) {
							$composite_scenario_data[ $scenario_id ][ 'scenario_actions' ][ 'compat_group' ][ 'is_active' ] = 'yes';
							$compat_group_actions_exist = true;
						}
					} else {
						$composite_scenario_data[ $scenario_id ][ 'scenario_actions' ][ 'compat_group' ][ 'is_active' ] = 'no';
					}

					// "Hide Components" action.
					if ( isset( $scenario_post_data[ 'scenario_actions' ][ 'conditional_components' ] ) ) {
						if ( ! empty( $scenario_post_data[ 'scenario_actions' ][ 'conditional_components' ][ 'is_active' ] ) ) {
							$composite_scenario_data[ $scenario_id ][ 'scenario_actions' ][ 'conditional_components' ][ 'is_active' ] = 'yes';
							$composite_scenario_data[ $scenario_id ][ 'scenario_actions' ][ 'conditional_components' ][ 'hidden_components' ] = $scenario_post_data[ 'scenario_actions' ][ 'conditional_components' ][ 'hidden_components' ];
						}
					} else {
						$composite_scenario_data[ $scenario_id ][ 'scenario_actions' ][ 'conditional_components' ][ 'is_active' ] = 'no';
					}

					/*
					 * Save component options in scenario.
					 */

					$composite_scenario_data[ $scenario_id ][ 'component_data' ] = array();

					foreach ( $ordered_composite_data as $group_id => $group_data ) {

						// Save modifier flag.
						if ( isset( $scenario_post_data[ 'modifier' ][ $group_id ] ) && $scenario_post_data[ 'modifier' ][ $group_id ] === 'not-in' ) {

							if ( ! empty( $scenario_post_data[ 'component_data' ][ $group_id ] ) ) {

								if ( isset( $scenario_post_data[ 'component_data' ] ) && WC_CP_Helpers::in_array_key( $scenario_post_data[ 'component_data' ], $group_id, 0 ) ) {
									$composite_scenario_data[ $scenario_id ][ 'modifier' ][ $group_id ] = 'in';
								} else {
									$composite_scenario_data[ $scenario_id ][ 'modifier' ][ $group_id ] = 'not-in';
								}
							} else {
								$composite_scenario_data[ $scenario_id ][ 'modifier' ][ $group_id ] = 'in';
							}

						} elseif ( isset( $scenario_post_data[ 'modifier' ][ $group_id ] ) && $scenario_post_data[ 'modifier' ][ $group_id ] === 'masked' ) {

							$composite_scenario_data[ $scenario_id ][ 'modifier' ][ $group_id ] = 'masked';

							$masked_rules_exist = true;

							if ( ! isset( $scenario_post_data[ 'component_data' ] ) && WC_CP_Helpers::in_array_key( $scenario_post_data[ 'component_data' ], $group_id, 0 ) ) {
								$scenario_post_data[ 'component_data' ][ $group_id ][] = 0;
							}
						} else {
							$composite_scenario_data[ $scenario_id ][ 'modifier' ][ $group_id ] = 'in';
						}


						$all_active = false;

						if ( ! empty( $scenario_post_data[ 'component_data' ][ $group_id ] ) ) {

							$composite_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ] = array();

							if ( isset( $scenario_post_data[ 'component_data' ] ) && WC_CP_Helpers::in_array_key( $scenario_post_data[ 'component_data' ], $group_id, 0 ) ) {

								$composite_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ][] = 0;
								$all_active = true;
							}

							if ( $all_active ) {
								continue;
							}

							if ( isset( $scenario_post_data[ 'component_data' ] ) && WC_CP_Helpers::in_array_key( $scenario_post_data[ 'component_data' ], $group_id, -1 ) ) {
								$composite_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ][] = -1;
							}

							// Run query to get component option ids.
							$component_options = WC_CP_Component::query_component_options( $group_data );

							foreach ( $scenario_post_data[ 'component_data' ][ $group_id ] as $item_in_scenario ) {

								if ( (int) $item_in_scenario === -1 || (int) $item_in_scenario === 0 ) {
									continue;
								}

								// Get product.
								$product_in_scenario = wc_get_product( $item_in_scenario );

								if ( $product_in_scenario->get_type() === 'variation' ) {

									$parent_id = $product_in_scenario->id;

									if ( $parent_id && in_array( $parent_id, $component_options ) && ! in_array( $parent_id, $scenario_post_data[ 'component_data' ][ $group_id ] ) ) {
										$composite_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ][] = $item_in_scenario;
									}

								} else {

									if ( in_array( $item_in_scenario, $component_options ) ) {
										$composite_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ][] = $item_in_scenario;
									}
								}
							}

						} else {

							$composite_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ]   = array();
							$composite_scenario_data[ $scenario_id ][ 'component_data' ][ $group_id ][] = 0;
						}

					}

					/**
					 * Filter the scenario data before saving. Add custom errors via 'add_notice()'.
					 *
					 * @param  array   $scenario_data
					 * @param  array   $post_data
					 * @param  string  $scenario_id
					 * @param  array   $composite_data
					 * @param  string  $post_id
					 */
					$composite_scenario_data[ $scenario_id ] = apply_filters( 'woocommerce_composite_process_scenario_data', $composite_scenario_data[ $scenario_id ], $scenario_post_data, $scenario_id, $ordered_composite_data, $post_id );
				}

				/*
				 * Re-order and save position data.
				 */

				ksort( $scenario_ordering );
				$ordering_loop = 0;
				foreach ( $scenario_ordering as $scenario_id ) {
					$ordered_composite_scenario_data[ $scenario_id ]               = $composite_scenario_data[ $scenario_id ];
					$ordered_composite_scenario_data[ $scenario_id ][ 'position' ] = $ordering_loop;
				    $ordering_loop++;
				}

			}

			/*
			 * Verify defaults.
			 */

			if ( ! empty( $ordered_composite_scenario_data ) ) {

				// Stacked layout notices.
				if ( $composite_layout === 'single' && $compat_group_actions_exist ) {
					$info = __( 'For a more streamlined user experience in applications that involve Scenarios and dependent Component Options, it is recommended to choose the <strong>Progressive</strong>, <strong>Stepped</strong> or <strong>Componentized</strong> layout.', 'ultimatewoo-pro' );
					self::add_notice( $info, 'info' );
				}

				$default_configuration = array();
				$optional_components   = array();

				foreach ( $ordered_composite_data as $group_id => $group_data ) {

					if ( '' !== $group_data[ 'default_id' ] ) {
						$default_configuration[ $group_id ] = array(
							'product_id'   => $group_data[ 'default_id' ],
							'variation_id' => 'any'
						);
					}

					if ( 'yes' === $group_data[ 'optional' ] ) {
						$optional_components[] = $group_id;
					}
				}

				$scenarios_manager = new WC_CP_Scenarios_Manager( array(
					'scenario_data'       => $ordered_composite_scenario_data,
					'optional_components' => $optional_components
				) );

				// Validate defaults.
				$validation_result = $scenarios_manager->validate_configuration( $default_configuration );

				if ( is_wp_error( $validation_result ) ) {

					$error_code = $validation_result->get_error_code();

					if ( in_array( $error_code, array( 'woocommerce_composite_configuration_selection_required', 'woocommerce_composite_configuration_selection_invalid' ) ) ) {

						$error_data = $validation_result->get_error_data( $error_code );

						if ( ! empty( $error_data[ 'component_id' ] ) ) {
							$error = sprintf( __( 'The <strong>Default Option</strong> chosen for &quot;%s&quot; was not found in any Scenario. Please double-check your preferences before saving, and always save any changes made to Component Options before choosing new defaults.', 'ultimatewoo-pro' ), strip_tags( $ordered_composite_data[ $error_data[ 'component_id' ] ][ 'title' ] ) );
							self::add_notice( $error, 'error' );
						}

					} elseif ( 'woocommerce_composite_configuration_invalid' === $error_code ) {
						$error = __( 'The chosen combination of <strong>Default Options</strong> does not match with any Scenario. Please double-check your preferences before saving, and always save any changes made to Component Options before choosing new defaults.', 'ultimatewoo-pro' );
						self::add_notice( $error, 'error' );
					}
				}
			}

			/*
			 * Save config.
			 */

			update_post_meta( $post_id, '_bto_data', $ordered_composite_data );
			update_post_meta( $post_id, '_bto_scenario_data', $ordered_composite_scenario_data );
		}

		if ( ! isset( $posted_composite_data[ 'bto_data' ] ) || count( $composite_data ) == 0 ) {

			delete_post_meta( $post_id, '_bto_data' );

			self::add_notice( __( 'Add at least one <strong>Component</strong> before saving. To add a Component, go to the <strong>Components</strong> tab and click <strong>Add Component</strong>.', 'ultimatewoo-pro' ), 'error' );

			if ( isset( $posted_composite_data[ 'post_status' ] ) && $posted_composite_data[ 'post_status' ] === 'publish' ) {
				global $wpdb;
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post_id ) );
			}

			return false;
		}

		if ( $zero_product_item_exists ) {
			self::add_notice( __( 'Add at least one valid <strong>Component Option</strong> to each Component. Component Options can be added by selecting products individually, or by choosing product categories.', 'ultimatewoo-pro' ), 'error' );
			return false;
		}

		return true;
	}

	/**
	 * Add custom save notices via filters.
	 *
	 * @param string  $content
	 * @param string  $type
	 */
	public static function add_notice( $content, $type ) {
		WC_CP_Admin_Notices::add_notice( $content, $type, true );
		self::$ajax_notices[] = strip_tags( html_entity_decode( $content ) );
	}
}

WC_CP_Meta_Box_Product_Data::init();
