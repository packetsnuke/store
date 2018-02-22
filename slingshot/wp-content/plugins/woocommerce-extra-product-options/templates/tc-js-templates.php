<?php
/**
 * The javascript-based template for displayed javascript generated html code
 *
 * This template can be overridden by copying it to yourtheme/tm-extra-product-options/tc-js-templates.php
 *
 * NOTE that we may need to update template files and you
 * (the plugin or theme developer) will need to copy the new files
 * to your theme or plugin to maintain compatibility.
 *
 * @see 		https://codex.wordpress.org/Javascript_Reference/wp.template
 * @author 		themeComplete
 * @package 	WooCommerce Extra Product Options/Templates
 * @version     4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script type="text/template" id="tmpl-tc-cart-options-popup">
    <div class='header'>
        <h3>{{{ data.title }}}</h3>
    </div>
    <div id='{{{ data.id }}}' class='float_editbox'>{{{ data.html }}}</div>
    <div class='footer'>
        <div class='inner'>
            <span class='tm-button button button-secondary button-large details_cancel'>{{{ data.close }}}</span>
        </div>
    </div>
</script>
<script type="text/template" id="tmpl-tc-lightbox">
    <div class="tc-lightbox-wrap">
        <span class="tc-lightbox-button tcfa tcfa-search tc-transition tcinit"></span>
    </div>
</script>
<script type="text/template" id="tmpl-tc-lightbox-zoom">
    <span class="tc-lightbox-button-close tcfa tcfa-close"></span>
    {{{ data.img }}}
</script>
<script type="text/template" id="tmpl-tc-final-totals">
    <dl class="tm-extra-product-options-totals tm-custom-price-totals">
        <# if (data.show_unit_price==true){ #><?php echo apply_filters('wc_epo_template_before_unit_price','');?>
            <dt class="tm-unit-price">{{{ data.unit_price }}}</dt>
            <dd class="tm-unit-price">
                <span class="price amount options">{{{ data.formatted_unit_price }}}</span>
            </dd><?php echo apply_filters('wc_epo_template_after_unit_price','');?>
        <# } #>
        <# if (data.show_options_total==true){ #><?php echo apply_filters('wc_epo_template_before_option_total','');?>
            <dt class="tm-options-totals">{{{ data.options_total }}}</dt>
            <dd class="tm-options-totals">
                <span class="price amount options">{{{ data.formatted_options_total }}}</span>
            </dd><?php echo apply_filters('wc_epo_template_after_option_total','');?>
        <# } #>
        <# if (data.show_fees_total==true){ #><?php echo apply_filters('wc_epo_template_before_fee_total','');?>
            <dt class="tm-fee-totals">{{{ data.fees_total }}}</dt>
            <dd class="tm-fee-totals">
                <span class="price amount fees">{{{ data.formatted_fees_total }}}</span>
            </dd><?php echo apply_filters('wc_epo_template_after_fee_total','');?>
        <# } #>
        <# if (data.show_extra_fee==true){ #><?php echo apply_filters('wc_epo_template_before_extra_fee','');?>
            <dt class="tm-extra-fee">{{{ data.extra_fee }}}</dt>
            <dd class="tm-extra-fee">
                <span class="price amount options extra-fee">{{{ data.formatted_extra_fee }}}</span>
            </dd><?php echo apply_filters('wc_epo_template_after_extra_fee','');?>
        <# } #>
        <# if (data.show_final_total==true){ #><?php echo apply_filters('wc_epo_template_before_final_total','');?>
            <dt class="tm-final-totals">{{{ data.final_total }}}</dt>
            <dd class="tm-final-totals">
                <span class="price amount final">{{{ data.formatted_final_total }}}</span>
            </dd><?php echo apply_filters('wc_epo_template_after_final_total','');?>
        <# } #>
        <# if (data.show_sign_up_fee==true){ #><?php echo apply_filters('wc_epo_template_before_sign_up_fee','');?>
            <dt class="tm-subscription-fee">{{{ data.sign_up_fee }}}</dt>
            <dd class="tm-subscription-fee">
                <span class="price amount subscription-fee">{{{ data.formatted_subscription_fee_total }}}</span>
            </dd><?php echo apply_filters('wc_epo_template_after_sign_up_fee','');?>
        <# } #>
    </dl>
</script>
<script type="text/template" id="tmpl-tc-price">
    <span class="amount">{{{ data.price.price }}}</span>
</script>
<script type="text/template" id="tmpl-tc-sale-price">
    <del>
        <span class="tc-original-price amount">{{{ data.price.original_price }}}</span>
    </del>
    <ins>
        <span class="amount">{{{ data.price.price }}}</span>
    </ins>
</script>
<script type="text/template" id="tmpl-tc-section-pop-link">
    <div id="tm-section-pop-up" class="tm-extra-product-options flasho tm_wrapper tm-section-pop-up single tm-animated appear">
        <div class='header'><h3>{{{ data.title }}}</h3></div>
        <div class="float_editbox" id="temp_for_floatbox_insert"></div>
        <div class='footer'>
            <div class='inner'>
                <span class='tm-button button button-secondary button-large details_cancel'>{{{ data.close }}}</span>
            </div>
        </div>
    </div>
</script>
<script type="text/template" id="tmpl-tc-floating-box-nks"><# if (data.values.length) {#>
    {{{ data.html_before }}}
    <div class="tc-row tm-fb-labels">
        <span class="tc-cell tc-col-3 tm-fb-title">{{{ data.option_label }}}</span>
        <span class="tc-cell tc-col-3 tm-fb-value">{{{ data.option_value }}}</span>
        <span class="tc-cell tc-col-3 tm-fb-quantity">{{{ data.option__qty }}}</span>
        <span class="tc-cell tc-col-3 tm-fb-price">{{{ data.option_lpric }}}</span>
    </div>
    <# for (var i = 0; i < data.values.length; i++) { #>
    <# if (data.values[i].label_show=='' || data.values[i].value_show=='') {#>
        <div class="tc-row">
            <# if (data.values[i].label_show=='') {#>
                <span class="tc-cell tc-col-3 tm-fb-title">{{{ data.values[i].title }}}</span>
                <# } #>
                    <# if (data.values[i].value_show=='') {#>
                        <span class="tc-cell tc-col-3 tm-fb-value">{{{ data.values[i].value }}}</span>
                        <# } #>
                            <span class="tc-cell tc-col-3 tm-fb-quantity">{{{ data.values[i].quantity }}}</span>
                            <span class="tc-cell tc-col-3 tm-fb-price">{{{ data.values[i].price }}}</span>
        </div>
        <# } #>
            <# } #>
                {{{ data.html_after }}}
                {{{ data.totals }}}
                <# }#></script>
<script type="text/template" id="tmpl-tc-floating-box"><# if (data.values.length) {#>
    {{{ data.html_before }}}
    <dl class="tm-fb">
        <# for (var i = 0; i < data.values.length; i++) { #>
        <# if (data.values[i].label_show=='') {#>
            <dt class="tm-fb-title">{{{ data.values[i].title }}}</dt>
            <# } #>
                <# if (data.values[i].value_show=='') {#>
                    <dd class="tm-fb-value">{{{ data.values[i].value }}}</dd>
                    <# } #>
                        <# } #>
    </dl>
    {{{ data.html_after }}}
    {{{ data.totals }}}
    <# }#></script>
<script type="text/template" id="tmpl-tc-chars-remanining">
    <span class="tc-chars">
		<span class="tc-chars-remanining">{{{ data.maxlength }}}</span>
		<span class="tc-remaining"> {{{ data.characters_remaining }}}</span>
	</span>
</script>
<script class="tm-hidden" type="text/template" id="tmpl-tc-formatted-price"><?php echo $formatted_price;?></script>
<script type="text/template" id="tmpl-tc-upload-messages">
    <div class="header">
        <h3>{{{ data.title }}}</h3>
    </div>
    <div class="float_editbox" id="temp_for_floatbox_insert">
        <div class="tc-upload-messages">
            <div class="tc-upload-message">{{{ data.message }}}</div>
            <# for (var i in data.files) {
                    if (data.files.hasOwnProperty(i)) {#>
                <div class="tc-upload-files">{{{ data.files[i] }}}</div>
                <# }
                        }#>
        </div>
    </div>
    <div class="footer">
        <div class="inner">
            &nbsp;
        </div>
    </div>
</script>