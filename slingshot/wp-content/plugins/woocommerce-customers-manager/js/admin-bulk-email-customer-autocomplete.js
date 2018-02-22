jQuery(document).ready(function()
{
	jQuery("#customer_ids").select2(
	{
	  ajax: {
		url: ajaxurl,
		dataType: 'json',
		delay: 250,
		multiple: true,
		data: function (params) {
		  return {
			customer: params.term, // search term
			page: params.page,
			action: 'wccm_get_customers_list'
		  };
		},
		processResults: function (data, page) 
		{
	   
		   return {
			results: jQuery.map(data, function(obj) 
			{
				return { id: obj.customer_id, text: obj.first_name+" "+obj.last_name+" ("+obj.email+")" };
			})
			};
		},
		cache: true
	  },
	  escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
	  minimumInputLength: 3,
	  templateResult: wccm_formatRepo, 
	  templateSelection: wccm_formatRepoSelection  
	});
	/* for(var i = 0; i<ids_emails_array.length; i++)
		jQuery('#customer_ids').append('<option value="' + ids_emails_array[i].id+ '" selected="selected">'+ids_emails_array[i].email+'</option>');  */
} );

function wccm_formatRepo (repo) 
{
	if (repo.loading) return repo.text;
	
	var markup = '<div class="clearfix">' +
			'<div class="col-sm-12">' + repo.text + '</div>';
    markup += '</div>'; 
	
    return markup;
  }

  function wccm_formatRepoSelection (repo) 
  {
	  return repo.full_name || repo.text;
  }