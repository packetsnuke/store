jQuery(document).ready(function()
{
	jQuery(document).on ('keypress', '#current-page-selector', wccm_go_to_page);
	
	jQuery(".js-role-select").select2({'width':400});
});
function wccm_go_to_page(event)
{
	if (typeof event.keyCode !== 'undefined' && event.keyCode == 13)
	{
		event.stopImmediatePropagation();
		event.preventDefault();
		jQuery( '#customer-filter').submit();
		return false;
	}
}