<?php

class WC_Bulk_Variations_Admin {

	var $settings_tabs;
	var $current_tab;
	var $fields = array();
	public $row_attribute;
	public $column_attribute;

	public function __construct() {
		add_action( 'init', array( $this, 'on_init' ), 99 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'process_meta_box' ), 1, 2 );
	}

	public function on_init() {
		global $wc_bulk_variations;
		wp_enqueue_style( 'wc_bulk_variations', $wc_bulk_variations->plugin_url() . '/assets/css/bulk-variations-admin.css' );
	}

	public function add_meta_box() {
		global $post;
		if ( $post && $post->post_type == 'product' ) {

			$product = wc_get_product( $post->ID );

			if ( empty( $product ) || ! $product->is_type( 'variable' ) ) {
				return;
			} else {
				add_meta_box( 'woocommerce-bulk-variations', __( 'Bulk Variation Input', 'ultimatewoo-pro' ), array(
					$this,
					'meta_box'
				), 'product', 'side', 'default' );
			}
		}
	}

	public function meta_box( $post ) {

		$product = wc_get_product( $post->ID );

		if ( empty( $product ) || ! $product->is_type( 'variable' ) ) {
			remove_meta_box( 'woocommerce-bulk-variations', 'product', 'side' );

			return;
		}

		$cur_product_view = wc_bv_get_post_meta( $post->ID, '_bv_type', true );
		$cur_single_view  = get_post_meta( $post->ID, '_bv_single_view', true );
		$cur_x            = wc_bv_get_post_meta( $post->ID, '_bv_x', true );
		$cur_y            = wc_bv_get_post_meta( $post->ID, '_bv_y', true );

		$axis_attributes           = $product->get_variation_attributes(); //Attributes configured on this product already.
		$available_axis_attributes = array();
		foreach ( $axis_attributes as $name => $attribute ) {
			if ( taxonomy_exists( $name ) ) {
				$tax                                = get_taxonomy( $name );
				$available_axis_attributes[ $name ] = $tax->label;
			} else {
				$available_axis_attributes[ $name ] = $name;
			}
		}
		asort( $available_axis_attributes );
		array_unshift( $available_axis_attributes, __( 'Select Variation Attribute...', 'ultimatewoo-pro' ) );
		?>
        <div id="product_views" class="panel">
            <div class="woocommerce_product_views">
				<?php if ( count( array_keys( $axis_attributes ) ) > 2 ) : ?>
                    <p>
						<?php _e( 'Bulk variation forms only support product with two variation attributes', 'ultimatewoo-pro' ); ?>
                        <input type="hidden" name="_bv_type" value="0"/>
                    </p>
				<?php elseif ( count( array_keys( $axis_attributes ) ) == 1 ) : ?>
                    <p>
						<?php _e( 'Bulk variation forms only support product with two variation attributes', 'ultimatewoo-pro' ); ?>
                        <input type="hidden" name="_bv_type" value="0"/>
                    </p>

				<?php else : ?>

                    <p>
                        <label class=""><?php _e( 'Bulk Variation Form:', 'ultimatewoo-pro' ); ?></label>
                        <select name="_bv_type">
                            <option value="0"><?php _e( 'Disabled', 'ultimatewoo-pro' ); ?></option>
                            <option <?php echo $cur_product_view == 'matrix' ? 'selected="selected"' : '' ?>
                                    value="matrix"><?php _e( 'Enabled', 'ultimatewoo-pro' ); ?></option>
                        </select>
                    </p>

                    <p>
                        <label class=""><?php _e( 'Singular Variation Form:', 'ultimatewoo-pro' ); ?></label>
                        <select name="_bv_single_view">
                            <option value="1">Enabled</option>
                            <option <?php echo $cur_single_view != 1 ? 'selected="selected"' : '' ?> value="0">Disabled</option>
                        </select>
                    </p>

                    <p>
                        <label class=""><?php _e( 'Columns:', 'ultimatewoo-pro' ); ?></label>
                        <select class="bv-select-max-width" name="_bv_x">
							<?php foreach ( $available_axis_attributes as $name => $label ): ?>

                                <option <?php echo $cur_x == $name ? 'selected="selected"' : '' ?>
                                        value="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></option>

							<?php endforeach; ?>
                        </select>
                    </p>
                    <p>
                        <label class=""><?php _e( 'Rows:', 'ultimatewoo-pro' ); ?></label>
                        <select class="bv-select-max-width" name="_bv_y">
							<?php foreach ( $available_axis_attributes as $name => $label ): ?>

                                <option <?php echo $cur_y == $name ? 'selected="selected"' : '' ?>
                                        value="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></option>

							<?php endforeach; ?>
                        </select>
                    </p>
				<?php endif; ?>
            </div>
        </div>
		<?php
	}

	function process_meta_box( $post_id, $post ) {


		if ( isset( $_POST['_bv_type'] ) && $_POST['_bv_type'] && $_POST['_bv_type'] != 'disabled' ) {
			wc_bv_update_post_meta( $post_id, '_bv_type', sanitize_text_field( $_POST['_bv_type'] ) );
			wc_bv_update_post_meta( $post_id, '_bv_single_view', $_POST['_bv_single_view'] );
			wc_bv_update_post_meta( $post_id, '_bv_x', sanitize_text_field( $_POST['_bv_x'] ) );
			wc_bv_update_post_meta( $post_id, '_bv_y', sanitize_text_field( $_POST['_bv_y'] ) );
		} else {
			wc_bv_delete_post_meta( $post_id, '_bv_type' );
			wc_bv_delete_post_meta( $post_id, '_bv_single_view' );
			wc_bv_delete_post_meta( $post_id, '_bv_x' );
			wc_bv_delete_post_meta( $post_id, '_bv_y' );
		}
	}

}