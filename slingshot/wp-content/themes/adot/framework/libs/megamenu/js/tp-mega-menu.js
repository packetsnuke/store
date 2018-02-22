function onChangeSubmenuType(el) {
    var el_value = el.value;
    var el_id = el.id;
    var menu_id = 'menu-item-' + el_id.substring(31);
    if (el_value == 'widget_area') {
        jQuery("#" + menu_id + " .el_multicolumn").show();
        jQuery("#" + menu_id + " .el_widget_area").show();
        jQuery("#" + menu_id + " .el_fullwidth").show();
    } else if (el_value == 'multicolumn') {
        jQuery("#" + menu_id + " .el_widget_area").hide();
        jQuery("#" + menu_id + " .el_multicolumn").show();
        jQuery("#" + menu_id + " .el_fullwidth").show();
    } else if (el_value == 'standard') {
        jQuery("#" + menu_id + " .el_widget_area").hide();
        jQuery("#" + menu_id + " .el_multicolumn").hide();
        jQuery("#" + menu_id + " .el_fullwidth").hide();
    }
}
(function ($) {
//// Set up any color fields
	$('.megamenu-colorpicker').wpColorPicker()
})(jQuery);
