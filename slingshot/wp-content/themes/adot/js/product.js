/* utility functions*/
var hover_product;

(function ($) {
    "use strict";
    /* Process show popup cart when hover cart info */
    var check_view_mod;
    var miniCartHover = function () {
        jQuery(document).on('mouseover', '.minicart_hover', function () {
            jQuery(this).next('.widget_shopping_cart_content').slideDown();
        }).on('mouseleave', '.minicart_hover', function () {
            jQuery(this).next('.widget_shopping_cart_content').delay(100).stop(true, false).slideUp();
        });
        jQuery(document)
            .on('mouseenter', '.widget_shopping_cart_content', function () {
                jQuery(this).stop(true, false).show()
            })
            .on('mouseleave', '.widget_shopping_cart_content', function () {
                jQuery(this).delay(100).stop(true, false).slideUp()
            });
    }

    miniCartHover();

    /* ****** PRODUCT QUICK VIEW  ******/
    var thim_quick_view = function () {
        $('.quick-view').click(function (e) {
            /* add loader  */
            $('.quick-view a').css('display', 'none');
            $(this).append('<a href="javascript:;" class="loading dark"></a>');
            var product_id = $(this).attr('data-prod');
            var data = {action: 'jck_quickview', product: product_id};
            $.post(ajaxurl, data, function (response) {
                $.magnificPopup.open({
                    mainClass: 'my-mfp-zoom-in',
                    items: {
                        src: '<div class="mfp-iframe-scaler">' + response + '</div>',
                        type: 'inline'
                    }
                });
                $('.quick-view a').css('display', 'inline-block');
                $('.loading').remove();
                $('.product-card .wrapper').removeClass('animate');
                setTimeout(function () {
                    if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                        $('.product-info .variations_form').each(function () {
                            $(this).wc_variation_form().find('.variations select:eq(0)').change();
                        });
                    }
                }, 600);
            });
            e.preventDefault();
        });
    }
    thim_quick_view();

    /* category toggle */
    var $cate = '.product-categories li.cat-parent .icon-toggle';

    if (jQuery('.product-categories > li.cat-parent > ul.children').css('display') === 'none') {
        jQuery('.product-categories > li.cat-parent >a').before('<span class="icon-toggle"><i class="fa fa-caret-right"></i></span>');
    }
    else {
        jQuery('.product-categories > li.cat-parent >a').before('<span class="icon-toggle"><i class="fa fa-caret-down"></i></span>');
    }

    jQuery($cate).click(function () {
        //alert('test');
        if (jQuery(this).parent().find('ul.children').is(':hidden')) {
            jQuery(this).parent().find('ul.children').slideDown(500, 'linear');
            jQuery(this).html('<i class="fa fa-caret-down"></i>');
        }
        else {
            jQuery(this).parent().find('ul.children').slideUp(500, 'linear');
            jQuery(this).html('<i class="fa fa-caret-right"></i>');
        }
    });

    // single product
    $(document).ready(function () {
        //Add Background color in li
        $(".filter-color ul li").each(function (index) {
            $(this).css('background', $(this).children('a').html());

        });
        if (jQuery().retina) {
            $(".retina").retina({preload: true})
        }
        if (jQuery().flexslider && jQuery(".woocommerce #carousel").length >= 1) {
            var e = 100;
            if (typeof jQuery(".woocommerce #carousel").data("flexslider") != "undefined") {
                jQuery(".woocommerce #carousel").flexslider("destroy");
                jQuery(".woocommerce #slider").flexslider("destroy")
            }
            jQuery(".woocommerce #carousel").flexslider({
                animation: "slide",
                controlNav: !1,
                directionNav: !0,
                animationLoop: !1,
                slideshow: !1,
                itemWidth: 73,
                maxItems: 3,
                itemMargin: 10,
                touch: !1,
                useCSS: !1,
                asNavFor: ".woocommerce #slider",
                smoothHeight: !1
            });
            jQuery(".woocommerce #slider").flexslider({
                animation: "slide",
                controlNav: !1,
                directionNav: !0,
                animationLoop: !1,
                slideshow: !1,
                smoothHeight: !1,
                touch: !0,
                useCSS: !1,
                sync: ".woocommerce #carousel"
            });
        }
    });
    //end single product

    // height item product & product hover
    hover_product = function () {
        $('.category-product-list .wrapper').imagesLoaded(function () {
            jQuery(".category-product-list .wrapper").each(function (index) {
                var img_height = $(this).find('.wp-post-image').height();
                var box_title_height = $(this).find('.stats-container .box-title').height();
                var stats_container_height = $(this).find('.stats-container').height() + 40;
                var product_countdown = $(this).find('.product-countdown .ob_warpper').height();
                var li_hight = img_height + box_title_height + 35 + product_countdown;
                $(this).css('height', li_hight);
                $(this).find(('.quick-view')).css('top', (li_hight - stats_container_height - 23) / 2);
                $(this).find('.stats-container').css({
                    'bottom': -(stats_container_height - box_title_height - product_countdown - 35),
                    'position': 'absolute'
                });
            });
        });
    }
    // add class countdown
    $(document).ready(function () {
        jQuery('.wrapper div.ob_warpper').parents('.wrapper').append('<span class="ob-countdown">&nbsp;</span>');
    })


    $(document).ready(function () {
        $('ul.tab-heading li').click(function () {
            hover_product();
        });
        jQuery(window).load(function () {
            hover_product();
        });
        $(window).resize(function () {
            hover_product();
        });
        // Lift card and show stats on Mouseover
        $('.product-card .wrapper').hover(function () {
            $(this).addClass('animate');
        }, function () {
            $(this).removeClass('animate');
        });
    });

    /* Load Paging Product data */
    var loadding = false;
    $(".btn_load_more_product").on('click', 'a', function (e) {
        /** Prevent Default Behaviour */
        e.preventDefault();
        if (!loadding) {
            loadding = true;
            var $this = $(this);
            var offset = $(this).attr("data-offset");
            var cat = $(this).attr("data-cat");
            var settings = $(this).data('settings');
            var ajax_url = $(this).attr("data-ajax_url");
            $this.html('Loading<span class="one">.</span><span class="two">.</span><span class="three">.</span><span class="four">.</span>');
            $.ajax({
                type: "POST",
                url: ajax_url,
                data: ({
                    action: 'button_paging',
                    offset: offset,
                    cat: cat,
                    df_offset: settings.offset_df,
                    column: settings.column,
                    order: settings.order,
                    orderby: settings.orderby,
                    hide_free: settings.hide_free,
                    show_hidden: settings.show_hidden
                })
            }).done(function (data) {
                loadding = false;
                var parent = $this.parent();
                $this.attr("data-offset", parseInt($this.attr('data-offset')) + parseInt(settings.offset_df));
                //$this.html('Load More');
                if (data['next_post'] == false) {
                    $this.remove();
                } else {
                    $this.html('Load More');
                }
                $(document).ajaxComplete(function () {
                    hover_product();
                    $(window).resize(function () {
                        hover_product();
                    });
                    // Lift card and show stats on Mouseover
                    $('.product-card .wrapper').hover(function () {
                        $(this).addClass('animate');
                    }, function () {
                        $(this).removeClass('animate');
                    });
                    thim_quick_view();
                });
                parent.prev().append(data['data']);
            });
        }
    });


    /* Product Grid, List Switch */
    var listSwitcher = function () {
        var activeClass = 'switcher-active';
        var gridClass = 'product-grid';
        var listClass = 'product-list';
        jQuery('.switchToList').click(function () {
            if (!Cookies.get('products_page') || Cookies.get('products_page') == 'grid') {
                switchToList();
            }
        });
        jQuery('.switchToGrid').click(function () {
            if (!Cookies.get('products_page') || Cookies.get('products_page') == 'list') {
                switchToGrid();
            }
        });

        function switchToList() {
            jQuery('.switchToList').addClass(activeClass);
            jQuery('.switchToGrid').removeClass(activeClass);
            jQuery('.archive_switch').fadeOut(300, function () {
                jQuery(this).removeClass(gridClass).addClass(listClass).fadeIn(300);
                Cookies.get('products_page', 'list', {expires: 3, path: '/'});
            });
        }

        function switchToGrid() {
            jQuery('.switchToGrid').addClass(activeClass);
            jQuery('.switchToList').removeClass(activeClass);
            jQuery('.archive_switch').fadeOut(300, function () {
                jQuery(this).removeClass(listClass).addClass(gridClass).fadeIn(300);
                Cookies.get('products_page', 'grid', {expires: 3, path: '/'});
            });
        }
    }

    check_view_mod = function () {
        var activeClass = 'switcher-active';
        if (Cookies.get('products_page') == 'grid') {
            jQuery('.archive_switch').removeClass('product-list').addClass('product-grid');
            jQuery('.switchToGrid').addClass(activeClass);
        } else if (Cookies.get('products_page') == 'list') {
            jQuery('.archive_switch').removeClass('product-grid').addClass('product-list');
            jQuery('.switchToList').addClass(activeClass);
        }
        else {
            jQuery('.switchToGrid').addClass(activeClass);
            jQuery('.archive_switch').removeClass('product-list').addClass('product-grid');
        }
    }

    if (jQuery("body.woocommerce").length) {
        check_view_mod();
        listSwitcher();
    }
})(jQuery);
