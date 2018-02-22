(function ($) {
    "use strict";

    function maybe_alter_pricing_table(o){
        
        var totals = o.totals_holder,
            object = o.epo,
            epo_object = o.data.epo_object;

        var pt = epo_object.main_product.find('.rp_wcdpd_pricing_table').find('td > .amount'),
            enable_pricing_table = totals.attr( 'data-tm-epo-dpd-enable-pricing-table' );
        if (enable_pricing_table !== "yes" || !pt.length){
            return;
        }
        var local_input_decimal_separator = (tm_epo_js.tm_epo_global_input_decimal_separator === "") ? tm_epo_js.currency_format_decimal_sep : getSystemDecimalSeparator(),
            local_decimal_separator = (tm_epo_js.tm_epo_global_displayed_decimal_separator === "") ? tm_epo_js.currency_format_decimal_sep : getSystemDecimalSeparator(),
            local_thousand_separator = (tm_epo_js.tm_epo_global_displayed_decimal_separator === "") ? tm_epo_js.currency_format_thousand_sep : (getSystemDecimalSeparator() == "," ? "." : ",");
        
        var rules = totals.data( 'product-price-rules' ),
            $cart = totals.data( 'tm_for_cart' ),
            apply_dpd = totals.data( 'fields-price-rules' ),
            product_price = parseFloat( totals.data( 'price' ) );
            product_price = accounting.unformat( o.data.tm_set_price(product_price,totals,false), tm_epo_js.currency_format_decimal_sep ); 
            
        var ot = accounting.unformat( object.options_price_per_unit, tm_epo_js.currency_format_decimal_sep ); 
        
        if ( rules && $cart ) {
                
            var variation_id_selector = 'input[name^="variation_id"]';
            if ( $cart.find( 'input.variation_id' ).length > 0 ) {
                variation_id_selector = 'input.variation_id';
            }
            var current_variation = $cart.find( variation_id_selector ).val();

            if ( ! current_variation ) {
                current_variation = 0;
            }

            if ( (rules[ current_variation ] && current_variation !== 0) || rules[ 0 ] ) {
                if ( ! rules[ current_variation ] ) {
                    current_variation = 0;
                }

                $( rules[ current_variation ] ).each( function ( id, rule ) {
                
                    var discount = tm_get_discount_obj(rules, current_variation, object.qty),
                        dprice = ot,
                        value = discount[0],
                        type = discount[1],
                        _dc = parseInt( tm_epo_js.currency_format_num_decimals ),
                        price = dprice;

                    switch ( type ) {
                        case "percentage":
                        case "discount__percentage":
                            price = price / (1 - value / 100);
                            price = Math.ceil( price * Math.pow( 10, _dc ) - 0.5 ) * Math.pow( 10, - _dc );
                            price = tc_round( price, _dc );
                            if ( price < 0 ) {
                                price = 0;
                            }
                            break;        

                        case "price":
                        case "discount__amount":
                            price = price + (value*object.qty);
                            price = Math.ceil( price * Math.pow( 10, _dc ) - 0.5 ) * Math.pow( 10, - _dc );
                            price = tc_round( price, _dc );
                            if ( price < 0 ) {
                                price = 0;
                            }
                            break;        

                        case "fixed":
                        case 'fixed__price':
                            /*price = value;
                            price = Math.ceil( price * Math.pow( 10, _dc ) - 0.5 ) * Math.pow( 10, - _dc );
                            price = tc_round( price, _dc );
                            if ( price < 0 ) {
                                price = 0;
                            }*/
                            // not supported
                            break;        
                    }

                    var min = parseFloat( rule[ 'min' ] ),
                        max = parseFloat( rule[ 'max' ] ),
                        type = rule[ 'type' ],
                        value = parseFloat( rule[ 'value' ] ),
                       // price = ot,
                        new_product_price=product_price;

                    switch ( type ) {
                        case "percentage":
                        case "discount__percentage":
                            if (apply_dpd){
                                price = price * (1 - value / 100);
                            }
                            new_product_price = new_product_price * (1 - value / 100);
                            break;

                        case "price":
                        case "discount__amount":
                            if (apply_dpd){
                                price = price - value;
                            }
                            new_product_price = new_product_price - value;
                            break;

                        case "fixed":
                        case 'fixed__price':
                            if (apply_dpd){
                                price = value;
                            }
                            new_product_price = value;
                            break;

                    }
                    var table_price = accounting.formatMoney( new_product_price + price, {
                        symbol: tm_epo_js.currency_format_symbol,
                        decimal: local_decimal_separator,
                        thousand: local_thousand_separator,
                        precision: tm_epo_js.currency_format_num_decimals,
                        format: tm_epo_js.currency_format
                    } );

                    $(pt[id]).html(table_price);

                } );
            }

        }

    }

    function tm_get_dpd( totals, epo_object, apply ) {
        if ( apply != 1 ) {
            return false;
        }
        var price = [ false, false ],
            rules = totals.data( 'product-price-rules' ),
            $cart = totals.data( 'tm_for_cart' );

        if ( ! rules || ! $cart ) {
            return false;
        } else {
            var variation_id_selector = 'input[name^="variation_id"]';
            if ( $cart.find( 'input.variation_id' ).length > 0 ) {
                variation_id_selector = 'input.variation_id';
            }
            var qty_element = totals.data( 'qty_element' ),
                qty = parseFloat( qty_element.val() ),
                current_variation = $cart.find( variation_id_selector ).val();

            if ( ! current_variation ) {
                current_variation = 0;
            }
            if ( isNaN( qty ) ) {
                if ( totals.attr( "data-is-sold-individually" ) || qty_element.length === 0 ) {
                    qty = 1;
                }
            }
            if ( (rules[ current_variation ] && current_variation !== 0) || rules[ 0 ] ) {
                if ( ! rules[ current_variation ] ) {
                    current_variation = 0;
                }
                price = tm_get_discount_obj(rules, current_variation, qty);
            }

        }

        return price;

    }

    function tm_get_discount_obj(rules, current_variation, qty, force){
        var discount = [ false, false ];
        $( rules[ current_variation ] ).each( function ( id, rule ) {
            var min = parseFloat( rule[ 'min' ] ),
                max = parseFloat( rule[ 'max' ] ),
                type = rule[ 'type' ],
                value = parseFloat( rule[ 'value' ] );

            if ( force || (isNaN(max) && min <= qty) || (!isNaN(max)  && min <= qty && qty <= max) ) {                
                discount = [ value, type ];
                return false;

            }
        } );

        return discount;

    }

    function multFloats(a,b){
      var atens = Math.pow(10,String(a).length - String(a).indexOf('.') - 1), 
          btens = Math.pow(10,String(b).length - String(b).indexOf('.') - 1); 
      return (a * atens) * (b * btens) / (atens * btens); 
    }

    function tc_calculate_product_price( price, totals ) {
        var rules = totals.data( 'product-price-rules' ),
            $cart = totals.data( 'tm_for_cart' );

        if ( ! rules || ! $cart || $.isEmptyObject(rules) || $.isEmptyObject($cart) ) {
            return price;
        } else {

            var qty_element = totals.data( 'qty_element' ),
                qty = parseFloat( qty_element.val() ),
                variation_id_selector = totals.data( 'variation_id_selector' ),
                current_variation = parseFloat( variation_id_selector.val() );

            if ( variation_id_selector.length > 0 && (! current_variation || current_variation === 0) ) {
                return false;
            }
            if ( ! current_variation ) {
                current_variation = 0;
            }
            if ( ! rules[ current_variation ] ) {
                current_variation = 0;
            }
            if ( isNaN( qty ) ) {
                if ( totals.attr( "data-is-sold-individually" ) || qty_element.length === 0 ) {
                    qty = 1;
                }
            }
            if ( (rules[ current_variation ] && current_variation !== 0) || rules[ 0 ] ) {
                if ( ! rules[ current_variation ] ) {
                    current_variation = 0;
                }

                var discount = tm_get_discount_obj(rules, current_variation, qty),
                    value = discount[0],
                    type = discount[1],
                    _dc = parseInt( tm_epo_js.currency_format_num_decimals );

                switch ( type ) {
                    case "percentage":
                    case "discount__percentage":
                        price = price - (Math.ceil( multFloats(price,(value / 100)) * Math.pow( 10, _dc ) - 0.5 ) * Math.pow( 10, - _dc ));
                        price = tc_round( price, _dc );
                        if ( price < 0 ) {
                            price = 0;
                        }
                        break;        

                    case "price":
                    case "discount__amount":
                        price = price - value;
                        //price = Math.ceil( price * Math.pow( 10, _dc ) - 0.5 ) * Math.pow( 10, - _dc );
                        //price = tc_round( price, _dc );
                        if ( price < 0 ) {
                            price = 0;
                        }
                        break;        

                    case "fixed":
                    case 'fixed__price':
                        price = value;
                        //price = Math.ceil( price * Math.pow( 10, _dc ) - 0.5 ) * Math.pow( 10, - _dc );
                        //price = tc_round( price, _dc );
                        if ( price < 0 ) {
                            price = 0;
                        }
                        break;        
                }

            }

        }
        return price;
    }

    function tc_apply_dpd( price, totals, apply, force ) {
        if ( typeof(price) == "object" ) {
            price = price[ 0 ];
            if ( isNaN( parseFloat( price ) ) ) {
                price = 0;
            }
        }
        if ( apply != 1 ) {
            return price;
        }

        var rules = totals.data( 'product-price-rules' ),
            $cart = totals.data( 'tm_for_cart' );

        if ( ! rules || ! $cart ) {
            return price;
        } else {
            var variation_id_selector = 'input[name^="variation_id"]';
            if ( $cart.find( 'input.variation_id' ).length > 0 ) {
                variation_id_selector = 'input.variation_id';
            }
            var qty_element = totals.data( 'qty_element' ),
                qty = parseFloat( qty_element.val() ),
                current_variation = $cart.find( variation_id_selector ).val();

            if ( ! current_variation ) {
                current_variation = 0;
            }
            if ( isNaN( qty ) ) {
                if ( totals.attr( "data-is-sold-individually" ) || qty_element.length === 0 ) {
                    qty = 1;
                }
            }
            if ( (rules[ current_variation ] && current_variation !== 0) || rules[ 0 ] ) {
                if ( ! rules[ current_variation ] ) {
                    current_variation = 0;
                }
                var discount = tm_get_discount_obj(rules, current_variation, qty, force),
                    value = discount[0],
                    type = discount[1];
                if ( price === undefined ) {
                    price = 0;
                }
                switch ( type ) {
                    case "percentage":
                    case "discount__percentage":
                        price = price * (1 - value / 100);
                        break;
                    case "price":
                    case "discount__amount":
                        price = price - value;
                        break;
                    case "fixed":
                    case 'fixed__price':
                        price = value;
                        break;
                }
                
            }

        }

        return price;

    }

    $(document).ready(function () {
        
        $.tc_add_filter("tc_calculate_product_price", tc_calculate_product_price, 10, 2);
        $.tc_add_filter("tc_apply_dpd", tc_apply_dpd, 10, 4);

        $.fn.tm_get_discount_obj = tm_get_discount_obj;
        
        /** Enable alteration of pricing table **/
        $(window).on("tc-epo-after-update", function(e,o){
            if (o && o.data && o.epo && o.totals_holder){
                maybe_alter_pricing_table(o);
            }
        });

        /** Prefix label and Suffix label **/
        $(window).on("tc-totals-container", function(e,o){
            if (o && o.data && o.totals_holder){
                var $totals_holder = o.totals_holder,
                    epo_object = o.data.epo_object,
                    tm_set_price = o.data.tm_set_price,
                    qty = o.data.qty;
                var apply_dpd = $totals_holder.data( 'fields-price-rules' ),
                    dpd_prefix = $totals_holder.data( 'tm-epo-dpd-prefix' ),
                    dpd_suffix = $totals_holder.data( 'tm-epo-dpd-suffix' );

                if ( apply_dpd == 1 ) {
                    var dpd_discount = tm_get_dpd( $totals_holder, epo_object, apply_dpd ),
                        dpd_string = '';
                    if ( dpd_discount[ 0 ] && dpd_discount[ 1 ] && (dpd_prefix || dpd_suffix) ) {
                        var dpd_discount_type = dpd_discount[ 1 ],
                            dpd_discount_string = '';
                        switch ( dpd_discount_type ) {
                            case 'percentage':
                            case "discount__percentage":
                                dpd_discount_string = dpd_discount[ 0 ] + '%';
                                break;
                            case 'price':
                            case "discount__amount":
                                dpd_discount_string = tm_set_price( dpd_discount[ 0 ] * qty, $totals_holder, false, false );
                                break;
                            case 'fixed':
                            case 'fixed__price':
                                dpd_discount_string = tm_set_price( (parseFloat( $totals_holder.data( 'price' ) ) - dpd_discount[ 0 ]) * qty, $totals_holder, false, false );
                                break;
                        }
                        dpd_string = dpd_prefix + ' ' + dpd_discount_string + ' ' + dpd_suffix;
                    }
                    if ( dpd_string ) {
                        $totals_holder.find( '.tm-final-totals .amount.final' ).after( '<span class="tm_dpd_label">' + dpd_string + '</span>' );
                    }
                }
            }
        });

        /** Enable original final total display **/
        $(window).on("tc-epo-after-update", function(e,o){
            if (o && o.data && o.epo && o.totals_holder){
                var $totals_holder = o.totals_holder,
                    tc_totals_ob = o.epo,
                    epo_object = o.data.epo_object;

                var do_oft = $totals_holder.data( 'tm-epo-dpd-original-final-total' );

                if ( do_oft !="yes"){
                    return;
                }

                var rules = $totals_holder.data( 'product-price-rules' ),
                    $cart = $totals_holder.data( 'tm_for_cart' ),
                    variation_id_selector = 'input[name^="variation_id"]';
                if ( $cart.find( 'input.variation_id' ).length > 0 ) {
                    variation_id_selector = 'input.variation_id';
                }
                var qty_element = $totals_holder.data( 'qty_element' ),
                    qty = parseFloat( qty_element.val() ),
                    current_variation = $cart.find( variation_id_selector ).val();

                if ( ! current_variation ) {
                    current_variation = 0;
                }
                if ( isNaN( qty ) ) {
                    if ( $totals_holder.attr( "data-is-sold-individually" ) || qty_element.length === 0 ) {
                        qty = 1;
                    }
                }
                var discount = tm_get_discount_obj(rules, current_variation, qty),
                    value = discount[0],
                    type = discount[1],
                    _dc = parseInt( tm_epo_js.currency_format_num_decimals ),
                    original_price = tc_totals_ob.product_total_price_without_options+tc_totals_ob.options_total_price,
                    price = original_price;

                // need to ssuport for Enable discounts on extra options = disable
                if ( o.totals_holder.attr("data-tm-epo-dpd-price-override")!=="1" ){
                    switch ( type ) {
                        case "percentage":
                        case "discount__percentage":                            
                            price =  ( Math.ceil( ( price / ( 1 - ( value / 100) ) ) * Math.pow( 10, _dc ) - 0.5 ) * Math.pow( 10, - _dc ))*1;
                            price = tc_round( price, _dc );
                            if ( price < 0 ) {
                                price = 0;
                            }
                            break;        

                        case "price":
                        case "discount__amount":
                            price = price + value;
                            price = Math.ceil( price * Math.pow( 10, _dc ) - 0.5 ) * Math.pow( 10, - _dc );
                            price = tc_round( price, _dc );
                            if ( price < 0 ) {
                                price = 0;
                            }
                            break;        
           
                    }
                }
                
                if (original_price == price){
                    return;
                }

                price = price + tc_totals_ob.cart_fee_options_total_price;

                $('.tm-final-totals').last().find('.price.amount.final').after('<div class="price amount original"><del>'+o.data.tm_set_price(price)+'</del></div>');

            }
        });

    });
})(jQuery);