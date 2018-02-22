(function ($) {
	"use strict";
	$(document).ready(function () {
		$(".thim-gallery-images").each(function () {
			var $this = jQuery(this);
			var $item = $this.attr("data-column-slider");
			var $paged = $this.attr("data-show-paged");
			var $nav = $this.attr("data-show-nav");
			if ($paged == '1' || $paged == 'on') {
				$paged = true
			} else {
				$paged = false
			}
			if ($nav == '1' || $nav == 'on') {
				$nav = true
			} else {
				$nav = false
			}

			$this.owlCarousel({
				autoPlay      : 3000,
				loop          : true,
				autoHeight    : false,
				stopOnHover   : true,
				items         : $item,
				navigation    : $nav,
				navigationText: ["<i class=\'fa fa-chevron-left\'></i>", "<i class=\'fa fa-chevron-right\'></i>"]
				, pagination  : $paged
			});
		});
	});

})(jQuery);
