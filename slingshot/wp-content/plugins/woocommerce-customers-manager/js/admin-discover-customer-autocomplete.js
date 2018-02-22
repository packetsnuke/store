jQuery(".js-data-customers-ajax").select2(
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
        results: jQuery.map(data, function(obj) {
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
}
);

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