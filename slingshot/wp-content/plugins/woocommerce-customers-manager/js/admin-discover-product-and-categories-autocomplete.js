jQuery(".js-data-products-ajax").select2(
{
  ajax: {
    url: ajaxurl,
    dataType: 'json',
    delay: 250,
	multiple: true,
    data: function (params) {
      return {
        product: params.term, // search term
        page: params.page,
		action: 'wccm_get_products_list'
      };
    },
    processResults: function (data, page) 
	{
   
       return {
        results: jQuery.map(data, function(obj) {
            return { id: obj.id, text: "<strong>(SKU: "+obj.product_sku+" ID: "+obj.id+")</strong> "+obj.product_name };
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

jQuery(".js-data-product-categories-ajax").select2(
{
  ajax: {
    url: ajaxurl,
    dataType: 'json',
    delay: 250,
	multiple: true,
    data: function (params) {
      return {
        product_category: params.term, // search term
        page: params.page,
		action: 'wccm_get_product_categories_list'
      };
    },
    processResults: function (data, page) 
	{
   
       return {
        results: jQuery.map(data, function(obj) {
            return { id: obj.id, text: obj.category_name };
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