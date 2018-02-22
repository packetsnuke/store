jQuery(function ($) {
	admin_select();
	$('body').on('change', '.tf-radio-image input', function () {
		var $this = $(this),
			type = $this.attr('type'),
			selected = $this.is(':checked'),
			$parent = $this.parent(),
			$others = $parent.siblings();
		if (selected) {
			$parent.addClass('tf-radio-active');
			type == 'radio' && $others.removeClass('tf-radio-active');
		}
		else {
			$parent.removeClass('tf-radio-active');
		}
	});
	$('.tf-radio-image input').trigger('change');

	$('.of-radio-img-header').each(function () {
		$(this).hover(function () {
			var line_index = parseInt($(this).attr('rel-data'), 10);
			var images_preview = $(this).attr('data-image-preview');
			var top_position = ($(this).offset().top);
			$('#customize-preview').append('<div id="thim-header-preview" style="top: ' + top_position + 'px"><img src="' + images_preview + '"/></div>');
 			//var $next_image = $(this).next();
			//if ($next_image.css('z-index') != '100') {
			//	$next_image.css('z-index', 100);
			//	if ($next_image.width() > $next_image.height()) {
			//		$next_image.css('top', $(this).parent().height() + 45 + 'px');
			//		if (line_index < 3) {
			//			$next_image.css('left', 0);
			//		}
			//		else {
			//			$next_image.css('right', 0);
			//		}
			//	}
			//	else {
			//		$next_image.css('top', '0px');
			//		if (line_index < 3) {
			//			$next_image.css('left', $(this).parent().width() + 'px');
			//		}
			//		else {
			//			$next_image.css('right', $(this).parent().width() + 'px');
			//		}
			//	}
			//}

			//$(this).next('img').show();
		}, function () {
			$('#thim-header-preview').remove();
 		});
 	});

	function admin_select() {
		/* sticky config */
		$('#customize-control-thim_sticky_main_menu_text_hover_color').hide();
		$('#customize-control-thim_sticky_main_menu_text_color').hide();
		$('#customize-control-thim_sticky_bg_main_menu_color').hide();
		$('#customize-control-thim_sticky_sub_menu_bg_color').hide();
		$('#customize-control-thim_sticky_sub_menu_text_color').hide();
		$('#customize-control-thim_sticky_sub_menu_text_hover_color').hide();
		$('#customize-control-thim_sticky_bg_border_header_color').hide();
		$('#customize-control-thim_config_att_sticky select').on('change', function () {
			if ($(this).val() == "sticky_same") {
				$('#customize-control-thim_sticky_main_menu_text_hover_color').hide();
				$('#customize-control-thim_sticky_main_menu_text_color').hide();
				$('#customize-control-thim_sticky_bg_main_menu_color').hide();
				$('#customize-control-thim_sticky_sub_menu_bg_color').hide();
				$('#customize-control-thim_sticky_sub_menu_text_color').hide();
				$('#customize-control-thim_sticky_sub_menu_text_hover_color').hide();
				$('#customize-control-thim_sticky_bg_border_header_color').hide();
			} else {
				$('#customize-control-thim_sticky_main_menu_text_hover_color').show();
				$('#customize-control-thim_sticky_main_menu_text_color').show();
				$('#customize-control-thim_sticky_bg_main_menu_color').show();
				$('#customize-control-thim_sticky_sub_menu_bg_color').show();
				$('#customize-control-thim_sticky_sub_menu_text_color').show();
				$('#customize-control-thim_sticky_sub_menu_text_hover_color').show();
				$('#customize-control-thim_sticky_bg_border_header_color').show();
			}
		}).trigger('change');

		/* sticky height */
		//$('#customize-control-thim_header_height_sticky').hide();
		//$('#customize-control-thim_config_height_sticky select').on('change', function () {
		//	if ($(this).val() == "height_sticky_auto") {
		//		$('#customize-control-thim_header_height_sticky').hide();
		//	} else {
		//		$('#customize-control-thim_header_height_sticky').show();
		//	}
		//}).trigger('change');
	}
});