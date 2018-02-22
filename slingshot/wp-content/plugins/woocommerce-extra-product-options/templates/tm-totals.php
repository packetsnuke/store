<?php
/**
 * The template for displaying the final totals box
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tm-totals.php
 *
 * NOTE that we may need to update template files and you
 * (the plugin or theme developer) will need to copy the new files
 * to your theme or plugin to maintain compatibility.
 *
 * @author      themeComplete
 * @package     WooCommerce Extra Product Options/Templates
 * @version     4.0
 */

// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}
?>
<div class="tc-totals-form tm-product-id-<?php echo $product_id; ?> <?php echo $classtotalform; ?>"
     data-epo-id="<?php echo $epo_internal_counter; ?>" data-product-id="<?php echo $product_id; ?>">
    <input type="hidden" value="<?php echo $price; ?>" name="cpf_product_price<?php echo $form_prefix; ?>"
           class="cpf-product-price"/>
    <input type="hidden" value="<?php echo esc_attr( $form_prefix ); ?>" name="tc_form_prefix" class="tc_form_prefix"/>
    <div id="tm-epo-totals<?php echo $form_prefix; ?>"
         class="tc-epo-totals tm-product-id-<?php echo $product_id; ?> tm-epo-totals tm-custom-prices-total<?php echo $hidden; ?> <?php echo $classcart; ?>"
         data-epo-id="<?php echo $epo_internal_counter; ?>"
         data-tm-epo-final-total-box="<?php echo $tm_epo_final_total_box; ?>"
         data-theme-name="<?php echo $theme_name; ?>"
         data-cart-id="<?php echo $forcart; ?>"
         data-is-subscription="<?php echo $is_subscription; ?>"
         data-is-sold-individually="<?php echo $is_sold_individually; ?>"
         data-type="<?php echo $type; ?>"
         data-price="<?php echo $price; ?>"
         data-product-price-rules="<?php echo $product_price_rules; ?>"
         data-fields-price-rules="<?php echo $fields_price_rules; ?>"
         data-force-quantity="<?php echo $force_quantity; ?>"
         data-price-override="<?php echo $price_override; ?>"
         data-is-vat-exempt="<?php echo $is_vat_exempt; ?>"
         data-non-base-location-prices="<?php echo $non_base_location_prices; ?>"
         data-taxable="<?php echo $taxable; ?>"
         data-tax-rate="<?php echo $tax_rate; ?>"
         data-base-tax-rate="<?php echo $base_tax_rate; ?>"
         data-base-taxes-of-one="<?php echo $base_taxes_of_one; ?>"
         data-modded-taxes-of-one="<?php echo $modded_taxes_of_one; ?>"
         data-tax-string="<?php echo $tax_string; ?>"
         data-tax-display-mode="<?php echo $tax_display_mode; ?>"
         data-prices-include-tax="<?php echo $prices_include_tax; ?>"
         data-subscription-sign-up-fee="<?php echo $subscription_sign_up_fee; ?>"
         data-variations-subscription-sign-up-fee="<?php echo $variations_subscription_sign_up_fee; ?>"
         data-subscription-period="<?php echo $subscription_period; ?>"
         data-variations-subscription-period="<?php echo $variations_subscription_period; ?>"
         data-variations="<?php echo $variations; ?>" <?php do_action( 'wc_epo_template_tm_totals', $args ); ?> ></div>
</div>
