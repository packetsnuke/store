(function ($) {
    "use strict";

    $(document).ready(function () {
        if (window.wceb) {
            $(window).on('tm-epo-init-events', function (evt, tc) {
                var epo_selector = '.tc-extra-product-options',
                    epo = tc.epo,
                    base_product_price = epo.totals_holder.data('price'),
                    tm_epo_final_total_box = epo.totals_holder.attr('data-tm-epo-final-total-box'),
                    current_duration = 1;

                if (tm_epo_final_total_box == 'disable') {
                    epo.epo_holder.find('.tm-epo-field').on('change.eb', function (pass) {
                        if (wceb.dateFormat === 'two' && wceb.checkIf.datesAreSet()) {
                            wceb.setPrice();
                        } else if (wceb.dateFormat === 'one' && wceb.checkIf.dateIsSet('start')) {
                            wceb.picker.set();
                        } else {
                            var formatted_total = wceb.formatPrice(wceb.get.basePrice());
                            $('.booking_price').find('.price .amount').html(formatted_total);
                        }
                    });
                }
                $("body").on('update_price', function (evt, data, response) {
                    var fragments = response.fragments,
                        errors = response.errors;

                    if (fragments && !errors && fragments.epo_base_price) {
                        var v = parseFloat(fragments.epo_base_price);

                        if (fragments.epo_duration > 0) {
                            current_duration = fragments.epo_duration;
                        }

                        epo.totals_holder.parent().find('.cpf-product-price').val(v);
                        epo.totals_holder.data('price', v);
                        epo.current_cart.trigger({
                            "type": "tm-epo-update",
                            //"norules":1
                        });
                    }
                });

                function tc_adjust_total(total, totals_holder) {
                    var options_multiplier = 0,
                        found = false;

                    if (( ( wceb.dateFormat === 'two' && wceb.checkIf.datesAreSet() ) || ( wceb.dateFormat === 'one' && wceb.checkIf.dateIsSet('start') ) ) && parseInt(tm_epo_easy_bookings.wc_booking_block_qty_multiplier) != 0) {
                        options_multiplier = options_multiplier + current_duration;
                        found = true;
                    }

                    if (found) {
                        total = total * options_multiplier;
                    }
                    return total;
                }

                $.tc_add_filter("tc_adjust_total", tc_adjust_total, 10, 2);

                // inject options data to easy bookings ajax
                $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
                    if (options.type.toLowerCase() !== "post") {
                        return;
                    }
                    if (originalOptions.data && originalOptions.data["action"] && originalOptions.data["action"] == "add_new_price" && originalOptions.data["additional_cost"]) {
                        var epos = $(epo_selector + '.tm-cart-main.tm-product-id-' + epo.product_id + '[data-epo-id="' + epo.epo_id + '"]'),
                            epos_hidden = $('.tm-totals-form-main[data-product-id="' + epo.product_id + '"]');
                        if (epos.length == 1) {
                            var form = $.extend(
                                epos.tm_aserializeObject(),
                                epos_hidden.tm_aserializeObject()
                            );
                            originalOptions.data["epo_data"] = $.param(form, false);
                            options.data = $.param(
                                $.extend(
                                    originalOptions.data,
                                    {}
                                ), false);
                        }
                    }
                });

            });
        }
    });
})(jQuery);