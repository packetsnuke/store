/**
 * Created by lucky boy.
 * User: dong-it
 */
(function ($) {
	"use strict";
	var nav_product = function () {
		var $ = jQuery;
		jQuery(".thim-widget-product-slider-id").each(function (index) {
			var img_height = $(this).find('.wp-post-image').height();
			$(this).find('.nav span').css({
				'top': (img_height / 2)
			});
		});
	}
	$(document).ready(function () {
		$(".thim-widget-product-slider-id").each(function () {
			var $this = jQuery(this);
			var owl = $this.find('.owl-theme');
			var $item = owl.attr("data-column-slider");
			var $pagination = owl.attr("data-pagination");
			var $pager;
			if ($pagination == 'yes') {
				$pager = true;
			} else {
				$pager = false;
			}
			owl.owlCarousel({
				loop          : true,
				singleItem    : true,
				autoHeight    : false,
				pagination    : $pager,
				stopOnHover   : true,
				navigationText: false,
				items         : $item
			});
			$this.find('.next').click(function () {
				owl.trigger('owl.next');
			});
			$this.find('.prev').click(function () {
				owl.trigger('owl.prev');
			});
		});

		jQuery(window).load(function () {
			nav_product();
		});

	});
})(jQuery);