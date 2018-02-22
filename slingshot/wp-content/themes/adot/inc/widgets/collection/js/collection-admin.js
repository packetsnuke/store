/**
 * Created by Phan Long on 4/20/2015.
 */
(function ($) {
	//// After the form is setup, add some custom stuff.
	$(document).on('sowsetupform', '.thim-widget-form[data-class="Thim_Collection_Widget"]', function () {
		var $iconboxWidgetForm = $(this);
		if (typeof $iconboxWidgetForm.data('obsetup-collection-widget') == 'undefined') {
			// custom font heading
			var $iconboxCustomFontField = $iconboxWidgetForm.find('.thim-widget-field-title_groupfont_heading');
			var updateFieldsForSelectedCustomFontType = function () {
				var selectedCustomFontType = $iconboxCustomFontField.find('select[name*="font_heading"] option:selected').val();
				$iconboxWidgetForm.data('selected-type', selectedCustomFontType);
				if (selectedCustomFontType == "custom") {
					$('.thim-widget-field-title_groupcustom_heading').slideDown(300, 'linear');
				} else {
					$('.thim-widget-field-title_groupcustom_heading').slideUp(300, 'linear');
				}
			}
			$iconboxCustomFontField.change(updateFieldsForSelectedCustomFontType);
			updateFieldsForSelectedCustomFontType();

			//var $iconboxTypeField = $iconboxWidgetForm.find('.thim-widget-field-image_type');
			//var updateFieldsForSelectedIconType = function () {
			//	var selectedType = $iconboxTypeField.find('input[type="radio"][name*="image_type"]:checked').val();
			//	$iconboxWidgetForm.data('selected-type', selectedType);
			//	if (selectedType == 'image') {
			//		$iconboxWidgetForm.find('.thim-widget-field-image').slideDown(300, 'linear');
			//		$iconboxWidgetForm.find('.thim-widget-field-video').hide();
			//	} else {
			//		$iconboxWidgetForm.find('.thim-widget-field-image').hide();
			//		$iconboxWidgetForm.find('.thim-widget-field-video').slideDown(300, 'linear');
			//	}
			//};
			//$iconboxTypeField.change(updateFieldsForSelectedIconType);
			//updateFieldsForSelectedIconType();

			$iconboxWidgetForm.data('obsetup-collection-widget', true);
		}
	});

})(jQuery);