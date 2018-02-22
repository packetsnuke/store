(function ($) {
	// After the form is setup, add some custom stuff.
	$(document).on('sowsetupform', '.thim-widget-form[data-class="Thim_Gallery_Images_Widget"]', function () {
		var $gallryWidgetForm = $(this);
		if (typeof $gallryWidgetForm.data('obsetup-galleryimages-widget') == 'undefined') {
			// custom font heading
			var $galleryCustomField = $gallryWidgetForm.find('.thim-widget-field-display_type');
			var updateFieldsForSelectedCustomFontHeadingType = function () {
				var selectedgalleryType = $galleryCustomField.find('select[name*="display_type"] option:selected').val();
				$gallryWidgetForm.data('selected-type', selectedgalleryType);
				if (selectedgalleryType == "slider") {
					$('.thim-widget-field-number').show();
					$('.thim-widget-field-navigation').show();
					$('.thim-widget-field-pagination').show();
					$('.thim-widget-field-column').hide();
				} else {
					$('.thim-widget-field-number').hide();
					$('.thim-widget-field-navigation').hide();
					$('.thim-widget-field-pagination').hide();
					$('.thim-widget-field-column').show();
				}
			}
			$galleryCustomField.change(updateFieldsForSelectedCustomFontHeadingType);

			updateFieldsForSelectedCustomFontHeadingType();

			$gallryWidgetForm.data('obsetup-galleryimages-widget', true);
		}
	});

})(jQuery);