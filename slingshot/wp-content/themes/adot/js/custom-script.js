/* utility functions*/
var woof_js_after_ajax_done;
(function ($) {
	"use strict";
	$.avia_utilities = $.avia_utilities || {};
	$.avia_utilities.supported = {};
	$.avia_utilities.supports = (function () {
		var div = document.createElement('div'),
			vendors = ['Khtml', 'Ms', 'Moz', 'Webkit', 'O'];  // vendors   = ['Khtml', 'Ms','Moz','Webkit','O'];  exclude opera for the moment. stil to buggy
		return function (prop, vendor_overwrite) {
			if (div.style.prop !== undefined) {
				return "";
			}
			if (vendor_overwrite !== undefined) {
				vendors = vendor_overwrite;
			}
			prop = prop.replace(/^[a-z]/, function (val) {
				return val.toUpperCase();
			});
			var len = vendors.length;
			while (len--) {
				if (div.style[vendors[len] + prop] !== undefined) {
					return "-" + vendors[len].toLowerCase() + "-";
				}
			}
			return false;
		};
	}());
	(function ($) {
		$.fn.extend({
			donetyping: function (callback, timeout) {
				timeout = timeout || 1e3; // 1 second default timeout
				var timeoutReference,
					doneTyping = function (el) {
						if (!timeoutReference) return;
						timeoutReference = null;
						callback.call(el);
					};
				return this.each(function (i, el) {
					var $el = $(el);
					// Chrome Fix (Use keyup over keypress to detect backspace)
					// thank you @palerdot
					$el.is(':input') && $el.on('keyup keypress', function (e) {
						// This catches the backspace button in chrome, but also prevents
						// the event from triggering too premptively. Without this line,
						// using tab/shift+tab will make the focused element fire the callback.
						if (e.type == 'keyup' && e.keyCode != 8) return;

						// Check if timeout has been set. If it has, "reset" the clock and
						// start over again.
						if (timeoutReference) clearTimeout(timeoutReference);
						timeoutReference = setTimeout(function () {
							// if we made it here, our timeout has elapsed. Fire the
							// callback
							doneTyping(el);
						}, timeout);
					}).on('blur', function () {
						// If we can, fire the event since we're leaving the field
						doneTyping(el);
					});
				});
			}
		});
	})(jQuery);
	/* audio post */
	/* ****** jp-jplayer  ******/
	var post_audio = function () {
		$('.jp-jplayer').each(function () {
			var $this = $(this),
				url = $this.data('audio'),
				type = url.substr(url.lastIndexOf('.') + 1),
				player = '#' + $this.data('player'),
				audio = {};
			audio[type] = url;

			$this.jPlayer({
				ready              : function () {
					$this.jPlayer('setMedia', audio);
				},
				swfPath            : 'jplayer/',
				cssSelectorAncestor: player
			});
		});
	}

	var post_gallery = function () {
		$('article.format-gallery .flexslider').imagesLoaded(function () {
			$('.flexslider').flexslider({
				slideshow     : true,
				animation     : 'fade',
				pauseOnHover  : true,
				animationSpeed: 400,
				smoothHeight  : true,
				directionNav  : true,
				controlNav    : false,
				prevText      : "<i class='fa fa-angle-left'></i>",
				nextText      : "<i class='fa fa-angle-right'></i>"
			});
		});
	}

	$(function () {
		$(document).ready(function () {
			post_audio();
			post_gallery();
			var $blog = $('.blog-masonry .content-inner-page');
			if ($('.blog-masonry .content-inner-page').length) {
				$blog.imagesLoaded(function () {
					$blog.isotope({
						itemSelector: '.type-post'
					});

					if ($(".page-content-inner").hasClass("scroll")) {
						$blog.infinitescroll({
								navSelector  : '.loop-pagination', // selector for the paged navigation
								nextSelector : '.loop-pagination a:first', // selector for the NEXT link (to page 2)
								extraScrollPx: 120,
								itemSelector : 'article.type-post', // selector for all items you
								animate      : true,
								bufferPx     : 40,
								errorCallback: function () {
								},
								infid        : 0, //Instance ID
								loading      : {
									finished   : undefined,
									finishedMsg: 'No more pages to load.',
									img        : "http://i.imgur.com/qkKy8.gif",
									msgText    : "<em>Loading the next set of posts...</em>",
									speed      : 'fast',
									start      : undefined
								}
							},
							function (newElements) {
								$blog.isotope('appended', jQuery(newElements));
								post_gallery();
								post_audio();
								$blog.imagesLoaded(function () {
									$blog.isotope('layout');
								});
							});
					}
				});
			}
		});
	});

	jQuery(function ($) {
		if (jQuery().flexslider) {
			$('article.portfolio-format-sidebar-slider .flexslider').flexslider({
				animation : "slide",
				prevText  : "<i class='fa fa-angle-left'></i>",
				nextText  : "<i class='fa fa-angle-right'></i>",
				controlNav: false
			});
		}
	});
	var sticky_calc = function () {
		if ($(".height_sticky_auto").length) {
			$('.navigation').affix({
				offset: {
					top: $('#masthead').offset().top
				}
			});
		}
	}
	//Scroll To top
	var scrollToTop = function () {
		jQuery(window).scroll(function () {
			if (jQuery(this).scrollTop() > 100) {
				jQuery('#topcontrol').css({bottom: "25px"});
			} else {
				jQuery('#topcontrol').css({bottom: "-100px"});
			}
		});
		jQuery('#topcontrol').click(function () {
			jQuery('html, body').animate({scrollTop: '0px'}, 800);
			return false;
		});

	}


	// DOMReady event
	$(function () {
		sticky_calc();
		scrollToTop();

		if (typeof jQuery.fn.waypoint !== 'undefined') {
			jQuery('.wpb_animate_when_almost_visible:not(.wpb_start_animation)').waypoint(function () {
				jQuery(this).addClass('wpb_start_animation');
			}, {offset: '85%'});
		}
	});

	jQuery('#wrapper-container').click(function () {
		jQuery('.slider_sidebar').removeClass('opened');
		jQuery('html,body').removeClass('slider-bar-opened');
	});
	jQuery(document).keyup(function (e) {
		if (e.keyCode === 27) {
			jQuery('.slider_sidebar').removeClass('opened');
			jQuery('html,body').removeClass('slider-bar-opened');
		}
	});

	jQuery('[data-toggle=offcanvas]').click(function (e) {
		e.stopPropagation();
		jQuery('.menu-mobile').toggleClass('opened');
		jQuery('html,body').toggleClass('menu-opened');
	});


	var MenuClick = 0;
	jQuery('.nav-link-menu').click(function (e) {
		e.stopPropagation();
		jQuery('body').toggleClass('nav-menu-open');
		jQuery('.nav-link-menu').toggleClass('active');
		//menu header v2
		if (MenuClick == 0) {
			MenuClick = 1;
			$("#nav-menu").stop(true, false).slideDown(250);
		} else {
			MenuClick = 0;
			$("#nav-menu").stop(true, false).slideUp(250);
		}
	});

	/********************************
	 Menu Sidebar
	 ********************************/
	jQuery('.sliderbar-menu-controller').click(function (e) {
		e.stopPropagation();
		jQuery('.slider_sidebar').toggleClass('opened');
		jQuery('html,body').toggleClass('slider-bar-opened');
	});

	/*************************************
	 * FAQs
	 *
	 */
	$('.faq-content .widget_black-studio-tinymce').hide();
	$('.faq-content h3').first().addClass('active');
	$('.faq-content h3.active').closest('.panel-grid-cell').find('.widget_black-studio-tinymce').show();
	$('.faq-content h3.active').closest('.panel-grid-cell').find('.widget_black-studio-tinymce .panel-widget-style').css('border-color', 'transparent');
	jQuery('.faq-content h3').click(function () {
		if ($(this).hasClass('active')) {
			$(this).closest('.panel-grid-cell').find('.widget_black-studio-tinymce').slideUp();
			$(this).closest('.panel-grid-cell').find('.widget_black-studio-tinymce .panel-widget-style').css('border-color', '#eee');
			$(this).removeClass('active');
		} else {
			$(this).closest('.panel-grid-cell').find('.widget_black-studio-tinymce').slideDown();
			$(this).closest('.panel-grid-cell').find('.widget_black-studio-tinymce .panel-widget-style').css('border-color', 'transparent');
			$(this).addClass('active');
		}
	});

	/*************
	 * form login, register
	 */
	$('.to-register').click(function () {
		$(this).closest('.col-1').animate({
			opacity: 0
		}, 100, "linear", function () {
			$(this).closest('.col-1').fadeOut(200);
		});
		var register = $(this).closest('.col2-set');
		register.find('.col-2').animate({
			opacity: 1
		}, 400, "linear", function () {
			register.find('.col-2').fadeIn(400);
		});
	});

	$('.to-login').click(function () {
		$(this).closest('.col-2').animate({
			opacity: 0
		}, 100, "linear", function () {
			$(this).closest('.col-2').fadeOut(200);
		});
		var register = $(this).closest('.col2-set');
		register.find('.col-1').animate({
			opacity: 1
		}, 400, "linear", function () {
			register.find('.col-1').fadeIn(400);
		});
	});

	/*
	 * Posts display
	 * */
	$(document).ready(function () {
		setTimeout(function () {
			$('.ui-link').removeClass('ui-link');
		}, 300);
	});
	/*************************************
	 * Show Share Click
	 ************************************/
	jQuery(".woo-share").click(function (e) {
		jQuery('.share_show').slideToggle();
	});
	/*************************************
	 * hover box shadow
	 ************************************/
	$(".content_portfolio.style08 li").each(function () {
		var color_hover = $(this).attr("data-color");
		//alert(color_hover);
		$(this).hover(
			function () {
				$(this).find('.portfolio-hover').css(
					'box-shadow', '0 0 0 10px ' + color_hover
				);
			}
		);
	});
	/*************************************
	 *
	 ************************************/

	$(document).ready(function () {
		var height_header = $('.header_overlay .site-header').height();
		var height_header_mobile = $('#masthead').height();
		$('.content-area').find('.top-site-no-image').css({"padding-top": height_header + 200 + 'px'});
		$('.content-area').find('.top-site-no-image-custom').css({"padding-top": height_header + 'px'});
		$('.header_overlay').find('.top-site-no-image-custom').css({"padding-top": height_header + 'px'});
		if ($(window).width() < 768) {
			$('.header_overlay').find('.page-title-wrapper').css({"padding-top": height_header_mobile + 'px'});
		}

	});

	// widgets wishlist
	function thim_refresh_dynamic_contents() {
		$.ajax({
			url    : thim_wishlist_ajaxurl,
			type   : "POST",
			data   : {
				'action': 'thim_refresh_dynamic_contents'
			},
			success: function (data) {
				$(".wishlist_items_number").html(data['wishlist_count_products']);
			}
		});
	}

	thim_refresh_dynamic_contents();
	$("#yith-wcwl-form").on("click", ".product-remove a", function () {
		setTimeout(function () {
			thim_refresh_dynamic_contents();
		}, 1000);
	});
	//wishlist
	$("html").on('added_to_wishlist', 'body', function (e) {
		thim_refresh_dynamic_contents();
	});

	//// stick header
	$(document).ready(function () {
		$('.header_default .sticky-header .navigation').imagesLoaded(function () {
			var height_sticky_header = $('.header_default .sticky-header .navigation').innerHeight();
			$('.header_default #wrapper-container .content-pusher').css({"padding-top": height_sticky_header + 'px'});
			$(window).resize(function () {
				var height_sticky_header = $('.header_default .sticky-header .navigation').innerHeight();
				$('.header_default #wrapper-container .content-pusher').css({"padding-top": height_sticky_header + 'px'});
			});
		});
	});
	$(window).scroll(function () {
		if ($(this).scrollTop() > 2) {
			$('#masthead.sticky-header .navigation').addClass('affix');
			$('#masthead.sticky-header .navigation').removeClass('affix-top');
		} else {
			$('#masthead.sticky-header .navigation').removeClass('affix');
			$('#masthead.sticky-header .navigation').addClass('affix-top');
		}
	});

	// sub menu full width
	var sub_menu_full = function () {
		var $width_screen = ($(window).width());
		if ($width_screen > 1200) {
			var $menu_left = ($width_screen - 1150) / 2
			$('.dropdown_full_width >.megacol,.dropdown_full_width > .submenu-widget').css({
				"left" : $menu_left + 'px',
				"right": $menu_left + 'px'
			})
		}
	}
	$(document).ready(function () {
		sub_menu_full();
		$(window).resize(function () {
			sub_menu_full();
		});
	});

	$('.parallax_slider').each(function () {
		$(this).wrapAll('<div class="wrapper-parrallax"></div>')
		var $bgobj = $(this); // assigning the object
		$(window).scroll(function () {
			var yPos = $(window).scrollTop() / 2;
			var coords = yPos + 'px';
			$bgobj.css({top: coords});
		});
		// window scroll Ends

	});

	$('.only-icon .button-search').hover(function (e) {
		e.stopPropagation();
		$('#header-search-form-input #s').focus();
	});


	// time line
	$(".date-time li").first().find('a').addClass('active');
	var Datescroll = function () {
		$(document).on('click', '.date-scoll', function (event) {
			event.preventDefault();
			var $headerHeight = 0;
			var t = $(this);
			if ($('.navigation').hasClass('affix')) {
				$headerHeight = parseInt(jQuery('.navigation').css('height'), 10);
			}
			var target = "#" + this.getAttribute('data-target');

			$('html, body').animate({
				scrollTop: $(target).offset().top - 50 - $headerHeight
			}, 1000, function () {
				$('.date-time li a').removeClass('active');
				t.addClass('active');
			});
		});
	}

	var thimtl_layout = function () {
		var thimtl = $('.box-time-line');
		var thimtl_half = thimtl.find('.time-line');
		var left_Col = 0,
			right_Col = 0;
		thimtl_half.imagesLoaded(function () {
			thimtl_half.each(function (index, el) {
				if ($(el).hasClass('normal')) {
					if (left_Col <= right_Col) {
						$(el).removeClass('time-line-right').addClass('time-line-left');
						left_Col += $(el).outerHeight();
					} else {
						$(el).removeClass('time-line-left').addClass('time-line-right');
						right_Col += $(el).outerHeight();
					}
				} else if ($(el).hasClass('full')) {
					left_Col = 0;
					right_Col = 0;
				}
			});
		});
		$('.time-line').css({'opacity': 1});
	}

	$(document).ready(function () {

		$('.navigation').on('click touchstart', '.menu-mobile-effect', function (e) {
			e.preventDefault();
			$('.wrapper-container').toggleClass('mobile-menu-open');
		});

		$(document).on('click touchstart', '#main-content > section, #main-content > footer, #main-content > div', function (e) {
			if ($('.wrapper-container').hasClass('mobile-menu-open')) {
				$('.wrapper-container').removeClass('mobile-menu-open');
			}
		});


		thimtl_layout();
		Datescroll();
	});

	$(window).resize(function () {
		thimtl_layout();
	});


	/* Load Posts data */
	$(".month-year").each(function () {
		$('a[data-target="' + this.id + '"]').show(500);
	});
	var loadding = false;
	$(".btn_time_line_load_more").on('click', 'a', function (e) {
		/** Prevent Default Behaviour */
		e.preventDefault();
		if (!loadding) {
			loadding = true;
			var $this = $(this);
			var offset = $(this).attr("data-offset");
			var cat = $(this).attr("data-cat");
			var date_post = $(this).attr("data-post-date");
			//var post_date = '';
			var size = $(this).attr("data-size");
			var ajax_url = $(this).attr("data-ajax_url");
			$this.html('Loading<span class="one">.</span><span class="two">.</span><span class="three">.</span>');
			$.ajax({
				type: "POST",
				url : ajax_url,
				data: ({action: 'button_paging', offset: offset, cat: cat, size: size, post_date: date_post})
			}).done(function (data) {
				loadding = false;
				var parent = $this.parent();
				$this.attr("data-post-date", data['date_post']);
				$this.attr("data-offset", parseInt($this.attr('data-offset')) + parseInt(data['offset']));
				if (!data['next_post']) {
					$this.remove();
				} else {
					$this.html('Load More');
				}
				parent.prev().append(data['data']);
				post_gallery();
				thimtl_layout();
				$(".month-year").each(function () {
					$('a[data-target="' + this.id + '"]').show(500);
				});
			});
		}
	});

	var sticky_sidebar = function () {
		if ($(".date-time").length > 0) {
			//	var $page_content = ".wrapper-time-line";
			var $sidebar = $(".date-time"), $window = jQuery(window);
			var offset = $sidebar.offset();
			var $scrollOffset = $(".wrapper-time-line").offset();
			var mgb = 0;
			$window.scroll(function () {
				var $scrollHeight = $(".wrapper-time-line").height(), $headerHeight = 0;
				if ($('.navigation').hasClass('affix')) {
					$headerHeight = parseInt($('.navigation').css('height'), 10);
				} else {
					$headerHeight = 0;
				}
				if ($window.width() > 1200) {
					//console.log(offset.top);
					if ($window.scrollTop() + $headerHeight + 3 > offset.top) {
						if ($window.scrollTop() + $headerHeight + $sidebar.height() + mgb < $scrollOffset.top + $scrollHeight) {
							$sidebar.stop().animate({
								marginTop: $window.scrollTop() - offset.top + $headerHeight + 40
							});
						} else {
						}
					} else {
						$sidebar.stop().animate({
							marginTop: 0
						});
					}
				} else {
					$sidebar.css('margin-top', 0);
				}
			});
		}
	}
	sticky_sidebar();
	// end time line
	// mobile menu
	jQuery(function ($) {
		$('#masthead.header_v1 .navbar-nav >li,#masthead.header_v1 .navbar-nav li.standard,#masthead.header_v1 .navbar-nav li.standard ul li,#masthead.header_v2 .navbar-nav >li,#masthead.header_v2 .navbar-nav li.standard,#masthead.header_v2 .navbar-nav li.standard ul li').hover(
			function () {
				$(this).children('.sub-menu').stop(true, false).slideDown(250);
			},
			function () {
				$(this).children('.sub-menu').stop(true, false).slideUp(250);
			}
		);
	});

	// perload
	jQuery(document).ready(function ($) {
		$(window).load(function () {
			$('#preload').delay(100).fadeOut(500, function () {
				$(this).remove();
			});
		});
	});
	/* Menu Sidebar */


	// jQuery('.menu-mobile-effect').click(function (e) {
	// 	// e.stopPropagation();
	// 	jQuery('.wrapper-container').toggleClass('mobile-menu-open');
	// });
	// jQuery('#main-content').click(function () {
	// 	jQuery('.wrapper-container').removeClass('mobile-menu-open');
	// });
	function mobilecheck() {
		var check = false;
		(function (a) {
			if (/(android|ipad|playbook|silk|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0, 4)))check = true
		})(navigator.userAgent || navigator.vendor || window.opera);
		return check;
	}

	if (mobilecheck()) {
		// window.addEventListener('load', function () { // on page load
		// 	document.getElementById('main-content').addEventListener("touchstart", function (e) {
		// 		jQuery('.wrapper-container').removeClass('mobile-menu-open');
		// 	});
		// }, false)
	}

	/* mobile menu */
	jQuery('.navbar-nav>li.menu-item-has-children >a,.navbar-nav>li.menu-item-has-children >span').after('<span class="icon-toggle"><i class="fa fa-angle-down"></i></span>');
	jQuery('.navbar-nav > li.menu-item-has-children .icon-toggle').click(function () {

		jQuery(this).find('i').toggleClass('fa-angle-down').toggleClass('fa-angle-up');
		jQuery(this).next('ul.sub-menu').slideToggle();

		// if (jQuery(this).next('ul.sub-menu').is(':hidden')) {
		// 	jQuery(this).next('ul.sub-menu').slideDown(500, 'linear');
		// 	jQuery(this).html('<i class="fa fa-angle-up"></i>');
		// }
		// else {
		// 	jQuery(this).next('ul.sub-menu').slideUp(500, 'linear');
		// 	jQuery(this).html('<i class="fa fa-angle-down"></i>');
		// }
	});
	jQuery('.thim-widget-megamenu-product .list-category a').hover(function ($) {
		jQuery('.thim-widget-megamenu-product .list-category a').removeClass('select');
		jQuery(this).addClass('select');
		var t = jQuery('div.thim-widget-product[data-cat="' + jQuery(this).attr('data-cat') + '"]');
		t.show();
		t.siblings().hide();
	})

// single product image
	jQuery('.variations_form').on('woocommerce_variation_has_changed', function () {
		if (jQuery('.product .product_variations_image img').attr('src') === jQuery('.product li.main_product_thumbnai a img').attr('src')) {
			jQuery('.product .product_variations_image').hide();
			jQuery('.product #slider').show();
			jQuery('.product #carousel').show();
		} else {
			jQuery('.product #slider').hide();
			jQuery('.product #carousel').hide();
			jQuery('.product .product_variations_image').removeClass('hide');
			jQuery('.product .product_variations_image').show();
		}
	});
	$('input.woof_checkbox_term[data-tax="pa_color"]').on('ifCreated', function(event){
			$(this).parent().css( 'background-color', $( this ).attr('name') );
	});

	woof_js_after_ajax_done = function(){
		$( 'input.woof_checkbox_term[data-tax="pa_color"]' ).each(function() {
			$(this ).parent().css( 'background-color', $( this ).attr('name') );
		});
		
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
	}
})(jQuery);