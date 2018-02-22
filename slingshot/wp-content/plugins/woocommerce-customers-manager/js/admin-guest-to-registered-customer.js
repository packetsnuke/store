jQuery(document).ready(function()
{
	jQuery('#wccm-conversion-submit-button').click(wccm_guest_to_registered_customer);
});

function wccm_guest_to_registered_customer(event)
{
	event.preventDefault();
	event.stopImmediatePropagation();
	jQuery('#wccm-conversion-box').fadeOut();
	jQuery('#wccm-conversion-loader').fadeIn();
	setTimeout(wccm_delayed_conversion_call, 1000);
	
}
function wccm_delayed_conversion_call()
{
	var formData = new FormData();
		formData.append('action', 'wccm_convert_guest_to_registered'); 
		formData.append('order-id', jQuery('#wccm-conversion-order-id').val()); 
		//formData.append('merge-if-existing', jQuery('#wccm-merge-users-if-existing').val()); 
		formData.append('notification-email', jQuery('#wccm-conversion-notification-email').val()); 
		jQuery.ajax({
			url: ajaxurl, 
			type: 'POST',
			data: formData,
			async: true,
			cache: false,
			contentType: false,
			processData: false,
			success: function (data) 
			{
				jQuery('#wccm-conversion-loader').fadeOut();
				jQuery('#wccm-conversion-result').fadeIn();
				jQuery('#wccm-conversion-text-result').html(data);
			}
		});
}
