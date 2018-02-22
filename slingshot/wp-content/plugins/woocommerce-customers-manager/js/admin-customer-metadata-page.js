var wccm_ok_color = '#d3ff8b';
var wccm_to_update_color = '#ffd486';

jQuery(document).ready(function()
{
	jQuery(document).on('click', '.wccm-add-button', wccm_on_add_metadata);
	jQuery(document).on('click', '.wccm-update-button', wccm_on_update_metadata);
	jQuery(document).on('click', '.wccm-delete-button', wccm_on_delete_metadata);
	jQuery(document).on('input propertychange', '.meta-textarea', wcccf_on_meta_value_content_change);
});
function wcccf_on_meta_value_content_change(event)
{
	jQuery(event.currentTarget).css('background', wccm_to_update_color);
}
function wccm_on_add_metadata(event)
{
	
	var user_id = jQuery(event.currentTarget).data('user-id');
	
	if(jQuery('#meta-key').val() == "")
	{
		alert(wccm.meta_key_empty_message);
		return;
	}
	//UI
	wccm_manage_visibility_add_buttons_container();
	
	var formData = new FormData();
	formData.append('action', 'wccm_add_new_user_meta');  
	formData.append('update-policy', jQuery('#update-policy').val());  
	formData.append('key', jQuery('#meta-key').val());  
	formData.append('value', jQuery('#meta-value').val());  
	formData.append('user_id', user_id);  
	
	
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		data: formData,
		async: true,
		success: function (data) 
		{
			//UI
			wccm_manage_visibility_add_buttons_container();
			location.reload();
		},
		error: function (data) 
		{
			//console.log(data);
			//alert("Error: "+data);
		},
		cache: false,
		contentType: false,
		processData: false
	}); 
}
function wccm_on_update_metadata(event)
{
	var id = jQuery(event.currentTarget).data('id');
	var user_id = jQuery(event.currentTarget).data('user-id');
	var value = jQuery('#meta-value-'+id).val();
	var key = jQuery('#meta-key-'+id).val();
	
	
	if(key == "")
	{
		alert(wccm.meta_key_empty_message);
		return;
	}
	
	//UI
	wccm_manage_visibility_update_buttons_container(id);
	
	var formData = new FormData();
	formData.append('action', 'wccm_update_user_meta');  
	formData.append('meta_id', id);  
	formData.append('value', value);  
	formData.append('user_id', user_id);  
	formData.append('key', key);  
	
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		data: formData,
		async: true,
		success: function (data) 
		{
			//UI
			wccm_manage_visibility_update_buttons_container(id);
			wcccm_set_ok_color_for_textarea(id);
			
			setTimeout(function(){ wcccm_set_standard_color_for_textarea(id); }, 1500);
		},
		error: function (data) 
		{
			//console.log(data);
			//alert("Error: "+data);
		},
		cache: false,
		contentType: false,
		processData: false
	}); 
}
function wccm_on_delete_metadata(event)
{
	var id = jQuery(event.currentTarget).data('id');
	var user_id = jQuery(event.currentTarget).data('user-id');
	
	if(!confirm(wccm.delete_message))
		return;		
	//UI
	wccm_manage_visibility_update_buttons_container(id);
	
	var formData = new FormData();
	formData.append('action', 'wccm_delete_user_meta');  
	formData.append('meta_id', id);  
	formData.append('user_id', user_id);  
	
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		data: formData,
		async: true,
		success: function (data) 
		{
			//UI
			wccm_manage_visibility_update_buttons_container(id);
			//jQuery('#meta-value-'+id).css('background', wccm_ok_color);
			jQuery("#row-"+id).remove();
		},
		error: function (data) 
		{
			//console.log(data);
			//alert("Error: "+data);
		},
		cache: false,
		contentType: false,
		processData: false
	}); 
}
function wccm_manage_visibility_update_buttons_container(id)
{
	jQuery("#wccm-update-buttons-cointaner-"+id).toggle();
	jQuery("#ajax-loader-update-"+id).toggle();
}
function wccm_manage_visibility_add_buttons_container()
{
	jQuery("#wccm-add-buttons-cointaner").toggle();
	jQuery("#ajax-loader-add").toggle();
}
function wcccm_set_ok_color_for_textarea(id)
{
	jQuery('#meta-value-'+id).css('background', wccm_ok_color);
	jQuery('#meta-key-'+id).css('background', wccm_ok_color);
}
function wcccm_set_standard_color_for_textarea(id)
{
	jQuery('#meta-value-'+id).css('background', '#ffffff');
	jQuery('#meta-key-'+id).css('background', '#ffffff'); 
	/*  jQuery('#meta-value-'+id).animate({backgroundColor:'#ffffff'}, 500);
	jQuery('#meta-key-'+id).animate({backgroundColor:'#ffffff'}, 500); */
}