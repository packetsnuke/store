<?php

// add product custom fields
add_action( 'woocommerce_product_options_general_product_data', 'wcplpro_product_options' );
function wcplpro_product_options() {
  
  if (isset($_GET['post'])) {
  
    $_pf = new WC_Product_Factory();  
    $product = $_pf->get_product(intval($_GET['post']));
  
    ?>
    
    <div class="options_group show_if_simple show_if_external">
    <h3 style="margin: 0 10px;">
      <?php echo __('Woocommerce Products List Product Options'); ?>
    </h3>
      
    <?php
  
  
    woocommerce_wp_select( 
    array( 
      'id'      => 'wcplpro_remove_product', 
      'label'   => __( 'Remove this product from Woocomerce Products List', 'wcplpro' ), 
      'options' => array(
        '0'   => __( 'No', 'wcplpro' ),
        '1'   => __( 'Yes', 'wcplpro' ),
        )
      )
    );
    ?>
    <p class="form-field wcplpro_override_extra_image">
      <label for="wcplpro_override_extra_image"><?php _e('Override extra image', 'wcplpro'); ?></label>
      <?php wcplpro_media_upload('wcplpro_override_extra_image', get_post_meta($product->get_id(), 'wcplpro_override_extra_image', true), 999999999); ?>
    </p>
    
    </div>
    
    <?php    
  }
}

add_action( 'save_post', 'wcplpro_save_product_option' );
function wcplpro_save_product_option( $product_id ) {
    // If this is a auto save do nothing, we only save when update button is clicked
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
  }
	if ( isset( $_POST['wcplpro_remove_product'] ) && is_numeric( $_POST['wcplpro_remove_product'] ) ) {
    update_post_meta( $product_id, 'wcplpro_remove_product', $_POST['wcplpro_remove_product'] );
	} else {
    delete_post_meta( $product_id, 'wcplpro_remove_product' );
  }
	if ( isset( $_POST['wcplpro_override_extra_image'] ) && $_POST['wcplpro_remove_product'] != '' ) {
    update_post_meta( $product_id, 'wcplpro_override_extra_image', $_POST['wcplpro_override_extra_image'] );
	} else {
    delete_post_meta( $product_id, 'wcplpro_override_extra_image' );
  }
  
  
  
}

?>