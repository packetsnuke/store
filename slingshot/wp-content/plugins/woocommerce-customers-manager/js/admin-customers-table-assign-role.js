jQuery(document).ready(function()
{
	jQuery(document).on('click', '#wccm_assign_roles_button', wccm_assign_roles);
});

function wccm_assign_roles(event)
{
	event.stopImmediatePropagation();
	event.preventDefault();
	
	var user_ids = [];
	//Roles to assign
	var roles_to_assign = jQuery('#wccm_roles_to_assign_select_menu').val();
	if(roles_to_assign == null)
	{
		//UI
		jQuery('#wccm_role_result_box').html(wccm_role_empty_error);
		return false;
	}
	//User ids
	jQuery('th.check-column input').each(function(index)
	{
		if(!isNaN(jQuery(this).attr('value')) && jQuery(this).prop( "checked" ) == true)
		{
			user_ids.push(jQuery(this).attr('value'));
		}
	});
	if(user_ids.length == 0)
	{
		jQuery('#wccm_role_result_box').html(wccm_user_empty_error)
		return false;
	}
			
	//UI
	jQuery('#wccm_role_result_box').html(wccm_role_wait_message);	
	jQuery('#wccm_assign_roles_button').fadeOut();
	
	//Ajax
	var formData = new FormData();
	formData.append('action', 'wccm_customer_assign_roles');
	formData.append('roles_to_assign', roles_to_assign);
	formData.append('user_ids', user_ids);
	
	var random = Math.floor((Math.random() * 1000000) + 999);
	jQuery.ajax({
		url: ajaxurl+"?nocache="+random,
		type: 'POST',
		data: formData,
		async: true,
		success: function (data) 
		{
			//var result = jQuery.parseJSON(data);
			//UI
			jQuery('#wccm_role_result_box').html(wccm_role_reload_message);
			//Reload
			setTimeout(function(){ location.reload(true); }, 2000);
		},
		error: function (data) 
		{
			//UI
			jQuery('#wccm_role_result_box').html(wccm_role_generic_error);
			jQuery('#wccm_role_result_box').append("<br/>");
			jQuery('#wccm_role_result_box').append(data);
		},
		cache: false,
		contentType: false,
		processData: false
	});
	
	return false;
}