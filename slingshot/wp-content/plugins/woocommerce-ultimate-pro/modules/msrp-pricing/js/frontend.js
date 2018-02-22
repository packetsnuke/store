/**
 * Provides support for variable products for WooCommerce 2.4+
 */
jQuery(document).ready(function($) {

    function msrp_get_variation_data_from_array(all_variations, variation_id) {
        var matching = [];
        for (var i = 0; i < all_variations.length; i++) {
            var this_variation = all_variations[i];
            var this_variation_id = this_variation.variation_id;
            if ( this_variation_id == variation_id ) {
                matching = this_variation;
                break;
            }
        }
        return matching;
    }

    function show_msrp ( e, msrp_html ) {
        var msrp_container = jQuery(e).find('div.single_variation .woocommerce_msrp');
        if ( msrp_container.length < 1 ) {
            jQuery(e).find('div.single_variation .woocommerce-variation-price').before('<div class="woocommerce_msrp">'+msrp_description+': <span class="woocommerce_msrp_price">'+msrp_html+'</span></div>');
        } else {
            msrp_container.find('span.woocomerce_msrp_price').html(msrp_html);
        }
    }

    function hide_msrp (e) {
        var msrp_container = jQuery(e).find('div.single_variation .woocommerce_msrp');
        if ( msrp_container.length > 0 ) {
            msrp_container.remove();
        }
    }

    jQuery( document ).on( 'show_variation', '.variations_form', function( e, variation ) {
        var variation_id = jQuery(this).find('input[name=variation_id]').val();
        var product_id   = jQuery(this).attr('data-product_id');
        var msrp = variation.msrp;
        var msrp_html = variation.msrp_html;
        var non_msrp_price = variation.non_msrp_price;
		// If we have an MSRP price ...
        if ( msrp > 0 ) {
        	// Display it if the user has chosen to have it always displayed.
            if ( msrp_status == 'always' ) {
                show_msrp ( this, msrp_html );
            }  else if ( msrp_status == 'different' ) {
            	// Optionally display it if it's different to the price.
                if ( msrp != non_msrp_price ) {
                    show_msrp( this, msrp_html );
                } else {
                    hide_msrp( this );
                }
            }
        }
    });
});