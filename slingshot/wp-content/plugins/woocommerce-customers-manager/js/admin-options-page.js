jQuery(document).ready(function()
{
	jQuery(".js-role-select").select2({'width':700});
	jQuery(document).on('change', '#automatic_conversion', wccm_show_do_not_convert_option);
	
	wccm_show_do_not_convert_option(null);
});
function wccm_show_do_not_convert_option(event)
{
	if(document.getElementById('automatic_conversion').checked)
		jQuery('#do_not_convert_if_email_is_already_associated_option_box').fadeIn();
	else 
		jQuery('#do_not_convert_if_email_is_already_associated_option_box').fadeOut();
}