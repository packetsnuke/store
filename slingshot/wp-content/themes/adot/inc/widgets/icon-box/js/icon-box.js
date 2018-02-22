(function ($) {
	"use strict";
	$(function () {
		/* Icon Box // */
		$(".wrapper-box-icon").each(function () {
			var $this = $(this);
			if ($this.attr("data-icon")) {
				var $color_icon = $(".wrapper-title-icon .icon", $this).css('color');
				var $color_icon_change = $this.attr("data-icon");
			}
			if ($this.attr("data-icon-border")) {
				var $color_icon_border = $(".wrapper-title-icon", $this).css('border-color');
				var $color_icon_border_change = $this.attr("data-icon-border");
			}

			if ($this.attr("data-icon-bg")) {
				var $color_bg = $(".wrapper-title-icon", $this).css('background-color');
				var $color_bg_change = $this.attr("data-icon-bg");
			}
			if ($this.attr("data-btn-bg")) {
				var $color_btn_bg = $(".smicon-read", $this).css('background-color');
				var $color_btn_bg_change = $this.attr("data-btn-bg");

				$(".smicon-read", $this).hover(
					function () {
						/* for select style*/
						if ($("#style_selector_container").length > 0) {
							if ($(".smicon-read", $this).css("background-color") != $color_btn_bg)
								$color_btn_bg = $(".smicon-read", $this).css('background-color');
						}

						$(".smicon-read", $this).css({'background-color': $color_btn_bg_change});
					}, function () {
						$(".smicon-read", $this).css({'background-color': $color_btn_bg});
					}
				);
			}

			$(".wrapper-title-icon", $this).hover(
				function () {
					if ($this.attr("data-icon")) {
						$(".wrapper-title-icon .icon", $this).css({'color': $color_icon_change});
					}
					if ($this.attr("data-icon-bg")) {
						/* for select style*/
						if ($("#style_selector_container").length > 0) {
							if ($(".wrapper-title-icon", $this).css("background-color") != $color_bg)
								$color_bg = $(".wrapper-title-icon", $this).css('background-color');
						}

						$(".wrapper-title-icon", $this).css({'background-color': $color_bg_change});
						console.log($color_bg_change);
					}
					if ($this.attr("data-icon-border")) {
						$(".wrapper-title-icon", $this).css({'border-color': $color_icon_border_change});
					}
				}, function () {
					if ($this.attr("data-icon")) {
						$(".wrapper-title-icon .icon", $this).css({'color': $color_icon});
					}
					if ($this.attr("data-icon-bg")) {
						$(".wrapper-title-icon", $this).css({'background-color': $color_bg});
					}
					if ($this.attr("data-icon-border")) {
						$(".wrapper-title-icon", $this).css({'border-color': $color_icon_border});
					}
				}
			);
		});
		/* End Icon Box */
	});
})(jQuery);