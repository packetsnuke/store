<?php
/**
 * Shows an order item
 *
 * @var object $item The item being displayed
 * @var int $item_id The id of the item being displayed
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
    die();
}

$input_type = ( is_callable( array( $order, 'is_editable' ) ) && $order->is_editable() ) ? 'number' : 'text';

$product_link  = $_product ? admin_url( 'post.php?post=' . absint( tc_get_id($_product) ) . '&action=edit' ) : '';
$thumbnail     = '';
$tax_data      = empty( $legacy_order ) && wc_tax_enabled() ? maybe_unserialize( isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '' ) : false;
$item_total    = ( isset( $item['line_total'] ) ) ? esc_attr( wc_format_localized_price( $item['line_total'] ) ) : '';
$item_subtotal = ( isset( $item['line_subtotal'] ) ) ? esc_attr( wc_format_localized_price( $item['line_subtotal'] ) ) : '';

$currency_arg = array( 'currency' => (is_callable( array($order, 'get_currency'))?$order->get_currency():$order->get_order_currency()) );
?>
<tr class="tm-order-line-option item <?php echo apply_filters( 'woocommerce_admin_html_order_item_class', ( ! empty( $class ) ? $class : '' ), $item, $order ); ?>" data-order_item_id="<?php echo $item_id; ?>" data-tm_item_id="<?php echo esc_attr($item_id);?>" data-tm_key_id="<?php echo esc_attr($key);?>">
    <?php echo $check_box_html;?>
    <td class="thumb">
        <?php
            echo '<div class="tc-epo-wc-order-item-thumbnail">' . wp_kses_post( $thumbnail ) . '</div>';
        ?>
    </td>
    <td class="tm-c name" data-sort-value="<?php echo esc_attr( $item['name'] ); ?>">
        <div class="tm-50">
        <?php
            echo $epo_name;
        ?>
        </div>
        <div class="view">
            <div class="tm-50">
            <?php
                echo $epo_value;
            ?>
            </div>
        </div>
         <?php if ($epo_edit_value){ ?>
        <div class="edit" style="display: none;">
            <div class="tm-50">
                <textarea name="tm_epo[<?php echo $item_id; ?>][<?php echo $key;?>][value]" class="value"><?php echo$epo['value'];?></textarea>
            </div>
        </div>
        <?php } ?>
    </td>

    <?php 
    
    do_action( 'woocommerce_admin_order_item_values', $_product, $item, 0 ); 
    
    ?>

    <td class="item_cost" width="1%" data-sort-value="<?php echo esc_attr( $order->get_item_subtotal( $item, false, true ) ); ?>">
        <?php 
            if(empty($item_meta['tm_has_dpd']) && empty($item_meta['_tm_has_dpd'])){
                if ($epo['quantity'] <=0){
                    echo '<div class="view">'.wc_price( 0, $currency_arg ).'</div>';
                    if ($epo_edit_cost)
                    echo '<div class="edit" style="display: none;"><input type="'.$input_type.'" name="tm_epo['.$item_id.']['.$key.'][price]" placeholder="0" value="0" data-qty="0" size="4" class="price" /></div>';
                }else{
                    echo '<div class="view">'.
                    ($epo_is_fee
                        ?wc_price(  (float) $epo['price'], $currency_arg )
                        :wc_price( tc_order_get_price_excluding_tax( $order, $item_id, array('qty'=>1,'price'=>$epo['price']) ) / $epo['quantity'], $currency_arg )
                        )
                    
                    .'</div>';  
                    if ($epo_edit_cost)
                    echo '<div class="edit" style="display: none;"><input type="'.$input_type.'" name="tm_epo['.$item_id.']['.$key.'][price]" placeholder="0" value="'.( tc_order_get_price_excluding_tax( $order, $item_id, array('qty'=>1,'price'=>$epo['price']) )/$epo['quantity'] ).'" data-qty="'.( tc_order_get_price_excluding_tax( $order, $item_id, array('qty'=>1,'price'=>$epo['price']) )/$epo['quantity']  ).'" size="4" class="price" /></div>';
                }
            }else{
                echo '<div class="view">&nbsp;</div>';
            }
        ?>
    </td>
    <td class="quantity" width="1%">
        <div class="view">
        <?php 
            echo $epo_quantity;
        ?>
        </div>
        <?php if ($epo_edit_quantity){ ?>
        <div class="edit" style="display: none;">
            <?php $item_qty = esc_attr( $item['qty'] ); ?>
            <input type="<?php echo $input_type;?>" step="1" min="0" autocomplete="off" name="tm_epo[<?php echo absint( $item_id ); ?>][<?php echo $key; ?>][quantity]" placeholder="0" value="<?php echo $epo['quantity']; ?>" data-qty="<?php echo $epo['quantity']; ?>" size="4" class="quantity" />
            <small>&times;<?php echo (float) $item_meta['_qty'][0];?></small>
        </div>
        <?php } ?>
    </td>
    <td class="line_cost" width="1%" data-sort-value="<?php echo esc_attr( isset( $item['line_total'] ) ? $item['line_total'] : '' ); ?>">
        <div class="view">
            <?php 
                if(empty($item_meta['tm_has_dpd']) && empty($item_meta['_tm_has_dpd'])){
                    echo '<span class="amount">'.
                    ($epo_is_fee
                        ?wc_price(  (float) $epo['price'], $currency_arg )
                        :wc_price(  (float) tc_order_get_price_excluding_tax( $order, $item_id, array('qty'=>1,'price'=>$epo['price']) ) * (float) $item_meta['_qty'][0], $currency_arg )
                        )
                    
                    .'</span>';
                }else{
                    echo "&nbsp;";
                }
            ?>
        </div>
    </td>

    <?php 
        if ( ! empty( $tax_data ) ) {
            $tax_based_on       = get_option( 'woocommerce_tax_based_on' );
            if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.7.0', '<' ) ) {
                if ( 'billing' === $tax_based_on ) {
                    $country  = $order->billing_country;
                    $state    = $order->billing_state;
                    $postcode = $order->billing_postcode;
                    $city     = $order->billing_city;
                } elseif ( 'shipping' === $tax_based_on ) {
                    $country  = $order->shipping_country;
                    $state    = $order->shipping_state;
                    $postcode = $order->shipping_postcode;
                    $city     = $order->shipping_city;
                }
            }else{
                if ( 'billing' === $tax_based_on ) {
                    $country  = $order->get_billing_country();
                    $state    = $order->get_billing_state();
                    $postcode = $order->get_billing_postcode();
                    $city     = $order->get_billing_city();
                } elseif ( 'shipping' === $tax_based_on ) {
                    $country  = $order->get_shipping_country();
                    $state    = $order->get_shipping_state();
                    $postcode = $order->get_shipping_postcode();
                    $city     = $order->get_shipping_city();
                }
            }
            // Default to base
            if ( 'base' === $tax_based_on || empty( $country ) ) {
                $default  = wc_get_base_location();
                $country  = $default['country'];
                $state    = $default['state'];
                $postcode = '';
                $city     = '';
            }
            $tax_class         = $item['tax_class'];
            $tax_rates = WC_Tax::find_rates( array(
                    'country'   => $country,
                    'state'     => $state,
                    'postcode'  => $postcode,
                    'city'      => $city,
                    'tax_class' => $tax_class
                ) );
            if ( $epo_is_fee ){
                $epo_line_taxes = WC_Tax::calc_tax( (float) $epo['price'] , $tax_rates, false );
            }else{
                $epo_line_taxes = WC_Tax::calc_tax( (float) $epo['price'] * (float) $item_meta['_qty'][0], $tax_rates, tc_order_get_att($order,'prices_include_tax') );    
            }
            


            foreach ( $order_taxes as $tax_item ) {
                $tax_item_id    = $tax_item['rate_id'];
                if (is_callable( array($tax_item, 'get_rate_id'))){
                    $tax_item_id    = $tax_item->get_rate_id();    
                }
                
                //$tax_item_total = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';


               ?>
                <td class="line_tax" width="1%">
                    <div class="view">
                        <?php 
                            if (isset($epo_line_taxes[$tax_item_id])){
                                $tax_price = $epo_line_taxes[$tax_item_id]; 
                                echo wc_price( wc_round_tax_total( $tax_price ), $currency_arg );
                            }else{
                                echo '&ndash;';
                            }
                        ?>
                    </div>
                </td>
                <?php
            }
        }
    ?>
    <td class="wc-order-edit-line-item" width="1%">
        <div class="wc-order-edit-line-item-actions">
            <?php if ( $edit_buttons && $order->is_editable() ) : ?>
                <a class="edit-order-item tips" href="#" data-tip="<?php esc_attr_e( 'Edit item', 'woocommerce' ); ?>"></a><a class="tm-delete-order-item tips" href="#" data-tip="<?php esc_attr_e( 'Delete item', 'woocommerce' ); ?>"></a>
            <?php endif; ?>
        </div>
    </td>
</tr>

