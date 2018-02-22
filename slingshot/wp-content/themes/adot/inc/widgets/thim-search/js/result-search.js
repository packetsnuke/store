jQuery(document).ready(function () {
	//jQuery('.header-search-close').html('<i class="fa fa-spinner fa-spin"></i>');
	jQuery('.ob-search-input').on('keyup', function (event) {
		clearTimeout(jQuery.data(this, 'timer'));
		if (event.which == 38) {
			if (navigator.userAgent.indexOf('Chrome') != -1 && parseFloat(navigator.userAgent.substring(navigator.userAgent.indexOf('Chrome') + 7).split(' ')[0]) >= 15) {
				var selected = jQuery(".ob_selected");
				jQuery(".ob-list-search li").removeClass("ob_selected");

				// if there is no element before the selected one, we select the last one
				if (selected.prev().length == 0) {
					selected.siblings().last().addClass("ob_selected");
				} else { // otherwise we just select the next one
					selected.prev().addClass("ob_selected");
				}
			}
			event.preventDefault();
		} else if (event.which == 40) {
			if (navigator.userAgent.indexOf('Chrome') != -1 && parseFloat(navigator.userAgent.substring(navigator.userAgent.indexOf('Chrome') + 7).split(' ')[0]) >= 15) {
				var selected = jQuery(".ob_selected");
				jQuery(".ob-list-search li").removeClass("ob_selected");

				// if there is no element before the selected one, we select the last one
				if (selected.next().length == 0) {
					selected.siblings().first().addClass("ob_selected");
				} else { // otherwise we just select the next one
					selected.next().addClass("ob_selected");
				}
			}
			event.preventDefault();
		} else if (event.which == 27) {
			jQuery('.ob-list-search').html('');
			jQuery('.ob-list-search').removeClass('active');
			jQuery(this).val('');
			jQuery(this).stop();
		} else {
			jQuery(this).data('timer', setTimeout(search, 1000));
		}
	});
	jQuery('.ob-search-input').on('keypress', function (event) {
		if (event.keyCode == 38) {
			var selected = jQuery(".ob_selected");
			jQuery(".ob-list-search li").removeClass("ob_selected");

			// if there is no element before the selected one, we select the last one
			if (selected.prev().length == 0) {
				selected.siblings().last().addClass("ob_selected");
			} else { // otherwise we just select the next one
				selected.prev().addClass("ob_selected");
			}
			event.preventDefault();
		}
		if (event.keyCode == 40) {
			var selected = jQuery(".ob_selected");
			jQuery(".ob-list-search li").removeClass("ob_selected");

			// if there is no element before the selected one, we select the last one
			if (selected.next().length == 0) {
				selected.siblings().first().addClass("ob_selected");
			} else { // otherwise we just select the next one
				selected.next().addClass("ob_selected");
			}
			event.preventDefault();
		}
	});
});

function search(waitKey) {
	keyword = jQuery('.ob-search-input').val();
	if (keyword) {
		if (!waitKey && keyword.length < 3) {
			return;
		}
		jQuery('.header-search-close').html('<i class="fa fa-spinner fa-spin"></i>');
		jQuery('.header-search-close').css({'z-index': 9999});
		jQuery('.button-search i').css({'opacity': 0});
		jQuery.ajax({
			type   : 'POST',
			data   : 'action=result_search&keyword=' + keyword,
			url    : ob_ajax_url,
			success: function (html) {
				var data_li = '';
				items = jQuery.parseJSON(html);
				if (!items.error) {
					jQuery.each(items, function (index) {
						if (index == 0) {
							data_li += '<li class="ui-menu-item' + this['id'] + ' ob_selected"><div class="ob-search-left">' + this['thumbnail'] + '</div><div class="ob-search-right"><a id="ui-id-' + this['id'] + '" class="ui-corner-all" href="' + this['guid'] + '"><i class="icon-page"></i><span class="search-title">' + this['title'] + '</span></a><p>' + this['shortdesc'] + '</p></div></li>';
						} else {
							data_li += '<li class="ui-menu-item' + this['id'] + '"><div class="ob-search-left">' + this['thumbnail'] + '</div><div class="ob-search-right"><a id="ui-id-' + this['id'] + '" class="ui-corner-all" href="' + this['guid'] + '"><i class="icon-page"></i><span class="search-title">' + this['title'] + '</span></a><p>' + this['shortdesc'] + '</p></div></li>';
						}
					});
					jQuery('.ob-list-search').html('').append(data_li);
				}
				jQuery('.header-search-close').html('');
				jQuery('.header-search-close').css({'z-index': -1});
				jQuery('.button-search i').css({'opacity': 1});
				jQuery('.ob-list-search').addClass('active');
			},
			error  : function (html) {
			}
		});
	}
}

(function ($) {
	"use strict";
	/* Product Search */
	jQuery(document).ready(function () {
		jQuery(document).on('click', 'a.ps-selector', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var $this = jQuery(this);
			$this.next("ul").slideToggle();
			return;
		});
		jQuery('.ps-option a').click(function (e) {
			e.preventDefault();
			jQuery('.ps-option a').removeClass("active");
			var $this = jQuery(this);
			$this.addClass("active");
			var cate = $this.text();
			$this.closest(".ps-option").prev('a').find("span").text(cate);
			$this.closest(".ps-option").hide();
			$this.closest(".ps-selector-container").next().find('input[name="product_cat"]').val($this.prop('rel'));
		});
		$('.ps-field').donetyping(function () {
			var $this = jQuery(this);
			var keyword = $this.val();

			if (keyword && keyword.length < 3) {
				return;
			}
			if (jQuery('.ps-option a.active').prop('rel'))
				var cate = jQuery('.ps-option a.active').prop('rel');
			else var cate = "-1";

			if ($this.closest(".product_search").hasClass("style-02")) {
				var $se = $this.parent().next().find("a");
			} else {
				var $se = $this.next();
			}
			$se.html('<i class="fa fa-spinner fa-spin fa-lg"></i>');

			$this.closest(".ps_container").addClass("searching");

			jQuery.ajax({
				type   : 'POST',
				data   : 'action=product_search&keyword=' + keyword + '&cate=' + cate,
				url    : ob_ajax_url,
				success: function (html) {
					$this.closest(".ps_container").removeClass("searching");
					$se.html('<i class="fa fa-search fa-lg"></i>');

					var items = jQuery.parseJSON(html);
					var data_li = "";
					jQuery.each(items, function (index) {
						if (this['id'] != -1) {
							if (index == 0) {
								data_li += '<li class="ui-menu-item' + this['id'] + ' ob_selected"><a id="ui-id-' + this['id'] + '" class="ui-corner-all" href="' + this['url'] + '">' + this['thumb'] + '<span class="search-title">' + this['value'] + '</span>' + this['rate'] + this['price'] + '</a></li>';
							} else {
								data_li += '<li class="ui-menu-item' + this['id'] + '"><a id="ui-id-' + this['id'] + '" class="ui-corner-all" href="' + this['url'] + '">' + this['thumb'] + '<span class="search-title">' + this['value'] + '</span>' + this['rate'] + this['price'] + '</a></li>';
							}
						} else {
							data_li += '<li class="ui-menu-item' + this['id'] + '"><a id="ui-id-' + this['id'] + '" class="ui-corner-all" href="' + this['url'] + '">' + '<span class="search-title">' + this['value'] + '</span>' + '</a></li>';
						}
					});
					jQuery('.product_results').html('').append(data_li);
					jQuery('.product_results').show();
				},
				error  : function (html) {
					$this.closest(".ps_container").removeClass("searching");
					$se.html('<i class="fa fa-search fa-lg"></i>');
				}
			});
		});
		jQuery(document).mouseup(function (e) {
			var container = jQuery(".ps-option");
			var container1 = jQuery(".ps-selector");
			var container2 = jQuery(".product_results");

			if (!container.is(e.target) // if the target of the click isn't the container...
				&& container.has(e.target).length === 0 && !container1.is(e.target) && container1.has(e.target).length === 0) // ... nor a descendant of the container
			{
				jQuery('.ps-option').hide();
			}
			if (!container2.is(e.target) // if the target of the click isn't the container...
				&& container2.has(e.target).length === 0) {
				jQuery('.product_results').hide();
			}
		});
	});
	/* End Product Search */
//The click to hide function
	$(".ps-option .icon-plus").click(function () {
		if ($(this).hasClass("current") && $(this).parent().next().queue().length === 0) {
			$(this).parent().next().slideUp();
			$(this).html('<i class="fa fa-plus"></i>');
			$(this).removeClass("current");
		} else if (!$(this).hasClass("current") && $(this).parent().next().queue().length === 0) {
			$(this).parent().next().slideDown();
			$(this).html('<i class="fa fa-minus"></i>');
			$(this).addClass("current");
		}
		var thisLi = $(this).parent().parent();
		$(".ps-option li").each(function () {
			if (!$(this).is(thisLi) && !$(this).find("li").is(thisLi)) {
				$(this).removeClass("current");
			}
		});
	});
})(jQuery);