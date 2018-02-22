/**
 * Created by lucky boy.
 * User: dong-it
 */
(function ($) {
	//// After the form is setup, add some custom stuff.
	$(document).on('sowsetupform', '.thim-widget-form[data-class="Woo_Products_Widget"]', function () {
		var $iconboxWidgetForm = $(this);
		if (typeof $iconboxWidgetForm.data('obsetup-product-tab-widget') == 'undefined') {
			// custom font heading
			var $iconboxCustomFontField = $iconboxWidgetForm.find('.thim-widget-field-show');
			var updateFieldsForSelectedCustomFontType = function () {
				var selectedCustomFontType = $iconboxCustomFontField.find('select[name*="show"] option:selected').val();
				$iconboxWidgetForm.data('selected-type', selectedCustomFontType);
				if (selectedCustomFontType == "category") {
					$('.thim-widget-field-cats').slideDown(300, 'linear');
				} else {
					$('.thim-widget-field-cats').slideUp(300, 'linear');
				}
			}
			$iconboxCustomFontField.change(updateFieldsForSelectedCustomFontType);
			updateFieldsForSelectedCustomFontType();

			$iconboxWidgetForm.data('obsetup-product-tab-widget', true);
		}
	});

})(jQuery);