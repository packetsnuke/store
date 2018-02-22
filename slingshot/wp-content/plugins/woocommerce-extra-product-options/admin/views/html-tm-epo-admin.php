<?php
/**
 *
 *   View for displaying single TM EPO record
 *
 *   Variables used:
 *   @required   $variations
 *   @required   $parent_data['attributes']
 *   @required   $tmcp_data
 *   @required   $tmcp_id
 *   @required   $loop
 *   @required   $tmcp_post_status
 *   @required   $tmcp_required
 *   @required   $tmcp_hide_price
 *   @required   $tmcp_limit
 *
 *   @optional   $current_stored_attributes
 *   @optional   $_regular_price
 */

// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
    die();
}

$tmcp_attribute_selected_value  = isset( $tmcp_data[ 'tmcp_attribute'  ][0] ) ? $tmcp_data[ 'tmcp_attribute'  ][0] : '';
$tmcp_type_selected_value       = isset( $tmcp_data[ 'tmcp_type'  ][0] ) ? $tmcp_data[ 'tmcp_type'  ][0] : '';

/* Current Variations */
$_field_tmcp_variation="";
$_field_tmcp_variation .= '<select class="tmcp-variation" name="tmcp_variation' .  '[' . $loop . ']"><option value="0">' . __( 'Any', 'woocommerce-tm-extra-product-options' ) . ' ' .  '&hellip;</option>';
$_variations =  (array) $variations;
foreach ( $_variations as $_variation ) {
    $_variation=(array) $_variation;
    $_field_tmcp_variation .= '<option value="' . esc_attr( sanitize_title( $_variation['ID'] ) ) . '">' . esc_html( $_variation['ID']  ) . '</option>';
}
$_field_tmcp_variation .= '</select>';

/* All Attributes */
$_field_attribute="";
foreach ( $parent_data['attributes'] as $attribute ) {
    // Get only attributes that are not variations
    if (  $attribute['is_variation'] || sanitize_title($attribute['name'])!=$tmcp_attribute_selected_value ) {
        continue;
    }
    $_field_attribute .= '<select data-tm-attr="'.esc_attr(sanitize_title( $attribute['name'] )) .'" class="tmcp_att tmcp_attribute_'.sanitize_title( $attribute['name'] ) .'" name="attribute_' . sanitize_title( $attribute['name'] ) . '[' . $loop . ']"><option value="0">' . __( 'Any', 'woocommerce-tm-extra-product-options' ) . ' ' . esc_html( wc_attribute_label( $attribute['name'] ) ) . '&hellip;</option>';
    // Get terms for attribute taxonomy or value if its a custom attribute
    if ( $attribute['is_taxonomy'] ) {
        $all_terms = get_terms( $attribute['name'], 'orderby=name&hide_empty=0' );
            if ( $all_terms ) {
            foreach ( $all_terms as $term ) {
                $has_term = has_term( (int) $term->term_id, $attribute['name'], $parent_data['id'] ) ? 1 : 0;
                if ($has_term ){
                    $_field_attribute .= '<option value="' . esc_attr( $term->slug ) . '" >' . apply_filters( 'woocommerce_tm_epo_option_name', esc_html( $term->name ), null, null ) . '</option>';
                }
            }
        }        
    } else {
        $options = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );
        foreach ( $options as $option ) {
            $_field_attribute .= '<option value="' . esc_attr( sanitize_title( $option ) ) . '">' . esc_html( apply_filters( 'woocommerce_tm_epo_option_name', $option ), null, null ) . '</option>';
        }
    }
    $_field_attribute .= '</select>';
}
if(!empty($_field_attribute)){
?>
<div data-epo-attr="<?php echo esc_attr( sanitize_title( $tmcp_attribute_selected_value ) ); ?>" class="woocommerce_tm_epo wc-metabox closed">
    <h3>
        <div class="tmicon tcfa tcfa-times delete remove_tm_epo" rel="<?php echo esc_attr( $tmcp_id ); ?>"></div>
        <div class="tmicon tcfa tcfa-caret-up fold tip" title="<?php _e( 'Click to toggle', 'woocommerce-tm-extra-product-options' ); ?>"></div>
        <div class="tmicon tcfa tcfa-sort move"></div>
        <span class="tm-att-id">#<?php echo esc_html( $tmcp_id ); ?></span>
        <span class="tm-att-label"><?php _e( 'Attribute:', 'woocommerce-tm-extra-product-options' ); ?></span>
        <span class="tm-att-value"><?php echo esc_html( wc_attribute_label( urldecode($tmcp_attribute_selected_value) ) ); ?></span>
        <input type="hidden" value="<?php echo esc_attr( sanitize_title( $tmcp_attribute_selected_value ) ); ?>" class="tmcp_attribute" name="tmcp_attribute[<?php echo $loop; ?>]">
        <input type="hidden" class="tmcp_loop" name="tmcp_loop[<?php echo $loop; ?>]" value="<?php echo esc_attr( $loop ); ?>" />
        <input type="hidden" name="tmcp_post_id[<?php echo $loop; ?>]" value="<?php echo esc_attr( $tmcp_id ); ?>" />
        <input type="hidden" class="tm_epo_menu_order" name="tmcp_menu_order[<?php echo $loop; ?>]" value="<?php echo $loop; ?>" />
    </h3>
    <div class="woocommerce_tmcp_attributes wc-metabox-content">
        <div class="data tc-clearfix">
            <p class="form-row form-row-first tmcp-main-choices">
                <label><?php _e( 'Type:', 'woocommerce-tm-extra-product-options' ); ?></label>
                <select class="tm-type" name="tmcp_type[<?php echo $loop; ?>]">
                    <option <?php selected(  $tmcp_type_selected_value , 'radio' ) ?> value="radio"><?php _e( 'Radio buttons', 'woocommerce-tm-extra-product-options' ); ?></option>
                    <option <?php selected(  $tmcp_type_selected_value , 'checkbox' ) ?> value="checkbox"><?php _e( 'Checkbox', 'woocommerce-tm-extra-product-options' ); ?></option>
                    <option <?php selected(  $tmcp_type_selected_value , 'select' ) ?> value="select"><?php _e( 'Select', 'woocommerce-tm-extra-product-options' ); ?></option>
                </select>
            </p>
            <p class="form-row form-row-last">
                <span class="tm-options">
                    <label><?php _e( 'Settings:', 'woocommerce-tm-extra-product-options' ); ?></label>
                    <span class="tm-hide-price">
                        <label><input type="checkbox" class="checkbox" name="tmcp_hide_price[<?php echo $loop; ?>]" <?php checked( $tmcp_hide_price, 1 ); ?> value="1" /> <?php _e( 'Hide price', 'woocommerce-tm-extra-product-options' ); ?></label>
                    </span>
                    <span class="tm-required">
                        <label><input type="checkbox" class="checkbox" name="tmcp_required[<?php echo $loop; ?>]" <?php checked( $tmcp_required, 1 ); ?> value="1" /> <?php _e( 'Required', 'woocommerce-tm-extra-product-options' ); ?></label>
                    </span>
                    <span class="tm-enabled">
                        <label><input type="checkbox" class="checkbox" name="tmcp_enabled[<?php echo $loop; ?>]" <?php checked( $tmcp_post_status, 'publish' ); ?> /> <?php _e( 'Enabled', 'woocommerce-tm-extra-product-options' ); ?></label>
                    </span>
                </span> 
                <span class="tmcp_choices<?php if ($tmcp_type_selected_value!="checkbox"){echo " tm-hidden";} ?>">
                    <?php          
                        echo '<span class="tm-hide-price"><label>'.__( 'Limit selection', 'woocommerce-tm-extra-product-options' ).': <input step="1" min="0" max="" name="tmcp_limit['.$loop.']" value="'.$tmcp_limit.'" title="Qty" size="4" pattern="[0-9]*" inputmode="numeric" type="number" /></label></span>';
                    ?>
                </span>       
            </p>
            <p class="form-row form-row-full tmcp_variation show_if_variable">
                <label><?php _e( 'Variation:', 'woocommerce-tm-extra-product-options' ); ?></label>
                <?php echo $_field_tmcp_variation; ?>
            </p>
            <p class="form-row form-row-first tmcp_attribute">
                <label><?php _e( 'Attribute:', 'woocommerce-tm-extra-product-options' ); ?></label>
                <?php echo $_field_attribute; ?>
            </p>
            <p class="form-row form-row-last tmcp_pricing">
                <label><?php echo __( 'Price:', 'woocommerce-tm-extra-product-options' ) . ' (' . get_woocommerce_currency_symbol() . ')';?></label>
                <?php 
                    if ( isset( $_regular_price ) && is_array($_regular_price) ) {
                        /*
                        * $key_attribute = attirbute
                        * $key_variation = variation
                        * $price = price
                        */
                        foreach ( $_regular_price as $key_attribute=>$value ) {
                            foreach ( $value as $key_variation=>$price ) {
                                if (!isset($_regular_price_type[$key_attribute][$key_variation])){
                                    $_regular_price_type[$key_attribute][$key_variation]="";
                                }
                    ?>
                                <input type="text" size="5" name="tmcp_regular_price[<?php echo $loop; ?>][<?php echo esc_attr( $key_attribute ); ?>][<?php echo $key_variation; ?>]" value="<?php echo esc_attr( $price ); ?>" class="wc_input_price tmcp-price-input tmcp-price-input-variation-<?php echo $key_variation; ?>" data-price-input-attribute="<?php echo esc_attr( $key_attribute ); ?>" placeholder="<?php _e( 'Custom price (required)', 'woocommerce-tm-extra-product-options' ); ?>" />
                                <select class="tmcp-price-input-type tmcp-price-input-variation-<?php echo $key_variation; ?>" data-price-input-attribute="<?php echo esc_attr( $key_attribute ); ?>" name="tmcp_regular_price_type[<?php echo $loop; ?>][<?php echo esc_attr( $key_attribute ); ?>][<?php echo $key_variation; ?>]">
                                    <option <?php selected(  $_regular_price_type[$key_attribute][$key_variation] , '' ) ?> value=""><?php _e( 'Fixed amount', 'woocommerce-tm-extra-product-options' ); ?></option>
                                    <option <?php selected(  $_regular_price_type[$key_attribute][$key_variation] , 'percent' ) ?> value="percent"><?php _e( 'Percent of the original price', 'woocommerce-tm-extra-product-options' ); ?></option>
                                </select>
                    <?php
                            }
                        }
                    }else { 
                    ?>
                    <input type="text" size="5" name="tmcp_regular_price[<?php echo $loop; ?>][0][0]" value="" class="wc_input_price tmcp-price-input tmcp-price-input-variation-0 tmcp-price-input-attribute-0" data-price-input-attribute="0" placeholder="<?php _e( 'Custom price', 'woocommerce-tm-extra-product-options' ); ?>" />
                    <select class="tmcp-price-input-type tmcp-price-input-variation-0 tmcp-price-input-attribute-0" data-price-input-attribute="0" name="tmcp_regular_price_type[<?php echo $loop; ?>][0][0]">
                        <option value=""><?php _e( 'Fixed amount', 'woocommerce-tm-extra-product-options' ); ?></option>
                        <option value="percent"><?php _e( 'Percent of the original price', 'woocommerce-tm-extra-product-options' ); ?></option>
                    </select>
                    <?php
                    }
                    ?>
            </p>
        </div>
    </div>

</div>
<?php
}else{
?>

<div data-epo-attr="<?php echo esc_attr( sanitize_title( $tmcp_attribute_selected_value ) ); ?>" class="missing woocommerce_tm_epo wc-metabox closed">
    <h3>
        <div class="tmicon tcfa tcfa-times delete remove_tm_epo" rel="<?php echo esc_attr( $tmcp_id ); ?>"></div>
            <span class="tm-att-id">#<?php echo esc_html( $tmcp_id ); ?> &mdash; </span>
            <span class="tm-att-label"><?php _e( 'Attribute:', 'woocommerce-tm-extra-product-options' ); ?></span>
            <span class="tm-att-value"><?php echo esc_html( wc_attribute_label( $tmcp_attribute_selected_value ) ); ?></span>
            <input type="hidden" value="<?php echo esc_attr( sanitize_title( $tmcp_attribute_selected_value ) ); ?>" class="tmcp_attribute" name="tmcp_attribute[<?php echo $loop; ?>]">
            <?php _e( 'Attributes missing. Please DELETE this extra option:', 'woocommerce-tm-extra-product-options' );
            ?>
            
            <input type="hidden" class="checkbox" name="tmcp_type[<?php echo $loop; ?>]"  value="<?php echo tmcp_type_selected_value;?>" />
            <input type="hidden" class="checkbox" name="tmcp_hide_price[<?php echo $loop; ?>]" <?php checked( $tmcp_hide_price, 1 ); ?> value="1" />
            
            
            <input type="hidden" class="tmcp_loop" name="tmcp_loop[<?php echo $loop; ?>]" value="<?php echo esc_attr( $loop ); ?>" />
            <input type="hidden" name="tmcp_post_id[<?php echo $loop; ?>]" value="<?php echo esc_attr( $tmcp_id ); ?>" />
            <input type="hidden" class="tm_epo_menu_order" name="tmcp_menu_order[<?php echo $loop; ?>]" value="<?php echo $loop; ?>" />
    </h3>
    <div class="woocommerce_tmcp_attributes wc-metabox-content">
        <div class="data tc-clearfix">
            <?php
                echo '<label><input type="hidden" name="tmcp_limit['.$loop.']" value="'.$tmcp_limit.'" /></label>';
                echo $_field_tmcp_variation;
                if ( isset( $_regular_price ) && is_array($_regular_price) ) {
                    /*
                    * $key_attribute = attirbute
                    * $key_variation = variation
                    * $price = price
                    */
                    foreach ( $_regular_price as $key_attribute=>$value ) {
                        foreach ( $value as $key_variation=>$price ) {
                            if (!isset($_regular_price_type[$key_attribute][$key_variation])){
                                $_regular_price_type[$key_attribute][$key_variation]="";
                            }
                ?>
                <input type="text" size="5" name="tmcp_regular_price[<?php echo $loop; ?>][<?php echo esc_attr( $key_attribute ); ?>][<?php echo $key_variation; ?>]" value="<?php echo esc_attr( $price ); ?>" class="wc_input_price tmcp-price-input tmcp-price-input-variation-<?php echo $key_variation; ?>" data-price-input-attribute="<?php echo esc_attr( $key_attribute ); ?>" placeholder="<?php _e( 'Custom price (required)', 'woocommerce-tm-extra-product-options' ); ?>" />
                <select class="tmcp-price-input-type tmcp-price-input-variation-<?php echo $key_variation; ?>" data-price-input-attribute="<?php echo esc_attr( $key_attribute ); ?>" name="tmcp_regular_price_type[<?php echo $loop; ?>][<?php echo esc_attr( $key_attribute ); ?>][<?php echo $key_variation; ?>]">
                    <option <?php selected(  $_regular_price_type[$key_attribute][$key_variation] , '' ) ?> value=""><?php _e( 'Fixed amount', 'woocommerce-tm-extra-product-options' ); ?></option>
                    <option <?php selected(  $_regular_price_type[$key_attribute][$key_variation] , 'percent' ) ?> value="percent"><?php _e( 'Percent of the orignal price', 'woocommerce-tm-extra-product-options' ); ?></option>
                </select>
                <?php
                        }
                    }
                }else {
                ?>
                <input type="text" size="5" name="tmcp_regular_price[<?php echo $loop; ?>][0][0]" value="" class="wc_input_price tmcp-price-input tmcp-price-input-variation-0 tmcp-price-input-attribute-0" data-price-input-attribute="0" placeholder="<?php _e( 'Custom price', 'woocommerce-tm-extra-product-options' ); ?>" />
                <select class="tmcp-price-input-type tmcp-price-input-variation-0 tmcp-price-input-attribute-0" data-price-input-attribute="0" name="tmcp_regular_price_type[<?php echo $loop; ?>][0][0]">
                    <option value=""><?php _e( 'Fixed amount', 'woocommerce-tm-extra-product-options' ); ?></option>
                    <option value="percent"><?php _e( 'Percent of the orignal price', 'woocommerce-tm-extra-product-options' ); ?></option>
                </select>
                <?php
                }
                ?>
        </div>
    </div>
</div>
<?php    
}
?>