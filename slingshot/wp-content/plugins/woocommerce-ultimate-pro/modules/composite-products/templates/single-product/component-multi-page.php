<?php
/**
 * Multi-Page Component template
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/component-multi-page.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version 3.7.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><div id="component_<?php echo $component_id; ?>" class="<?php echo esc_attr( implode( ' ', $component_classes ) ); ?>" data-nav_title="<?php echo esc_attr( $component->get_title( true ) ); ?>" data-item_id="<?php echo $component_id; ?>" style="display:none;">

	<div class="component_title_wrapper"><?php

		$title = $component->get_title( true );

		wc_get_template( 'single-product/component-title.php', array(
			'title'   => apply_filters( 'woocommerce_composite_component_step_title', sprintf( __( '<span class="step_index">%d</span> <span class="step_title">%s</span>', 'ultimatewoo-pro' ), $step, $title ), $title, $step, $steps, $product ),
			'toggled' => false,
			'tag'     => 'h2'
		), '', WC_CP()->plugin_path() . '/templates/' );

	?></div>

	<div class="component_inner">
		<div class="component_description_wrapper"><?php

			if ( $component->get_description() !== '' ) {
				wc_get_template( 'single-product/component-description.php', array(
					'description' => $component->get_description( true )
				), '', WC_CP()->plugin_path() . '/templates/' );
			}

		?></div>
		<div class="component_selections"><?php

			/**
			 * Action 'woocommerce_composite_component_selections_paged'.
			 *
			 * @param  string                $component_id
			 * @param  WC_Product_Composite  $product
			 *
			 * @hooked wc_cp_add_sorting                      - 15
			 * @hooked wc_cp_add_filtering                    - 20
			 * @hooked wc_cp_add_component_options            - 25
			 * @hooked wc_cp_add_component_options_pagination - 26
			 * @hooked wc_cp_add_current_selection_details    - 30
			 */
			do_action( 'woocommerce_composite_component_selections_paged', $component_id, $product );

		?></div>
	</div>
</div>
