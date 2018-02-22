/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Show, hide post format meta boxes
 *
 * @return void
 * @since 1.0
 */
function togglePostFormatMetaBoxes() {
	var $input = jQuery('input[name=post_format]'),
		$metaBoxes = jQuery('[id^="thim-meta-boxes-post-format-"]').hide();

	// Don't show post format meta boxes for portfolio
	if (jQuery('#post_type').val() == 'portfolio')
		return;

	$input.change(function () {
		$metaBoxes.hide();
		jQuery('#thim-meta-boxes-post-format-' + jQuery(this).val()).show();
	});
	$input.filter(':checked').trigger('change');
}

function toggleComingSoonPage() {
	jQuery('#page_template').change(function () {
		jQuery('#coming-soon-mode-options')[jQuery(this).val() == 'page-templates/comingsoon.php' ? 'show' : 'hide']();
		jQuery('#display-setting')[jQuery(this).val() == 'page-templates/comingsoon.php' ? 'hide' : 'show']();
	}).trigger('change');
}
toggleComingSoonPage();
togglePostFormatMetaBoxes();
displaySetting();
function displaySetting() {
	jQuery("[onchange*='thimShowHideSubMetaBoxOption']").each(function(index,el){
		if(el.checked){
			jQuery('tr.child_of_'+el.id).show();
		}
	});
	return;
}

function thimShowHideSubMetaBoxOptions( parent_element, status ){
	parent_id = parent_element.id;
	child_class = 'child_of_'+parent_id;
	if( status ) {
		jQuery('.'+child_class).show();
	} else {
		jQuery('.'+child_class).hide();
	}
}
