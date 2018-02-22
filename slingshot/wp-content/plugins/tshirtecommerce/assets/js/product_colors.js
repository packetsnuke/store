function tshirtecommerce_colors(e)
{
	if(jQuery(e).hasClass('active')) return false;

	jQuery(e).parent().find('.bg-colors').removeClass('active');
	jQuery(e).addClass('active');

	var product_id 	= jQuery(e).data('id');
	var index 		= jQuery(e).data('index');

	jQuery.ajax({
 		type: "GET",
  		url: woocommerce_params.ajax_url,
  		contentType: "json",
  		cache: true,
  		data: {action: 'load_product_image', product_id: product_id, index: index},
  		success: function(data){
  			if(typeof data.error != 'undefined' && data.error == 0)
  			{
          var li = jQuery(e).parents('.type-product')
  				var img = li.find('img');
  				img.attr('src', data.image);
  				img.attr('srcset', data.image);
          
          var color = jQuery(e).data('color');
          var a = li.find('.e-custom-product');
          if(a.length > 0)
          {
            a.data('color', color);
          }

          var a = li.children('.woocommerce-LoopProduct-link');
          if(a.length > 0)
          {
            var url = a.attr('href');
            if(url.indexOf('?') == -1)
            {
              var prefix = '?';
            }
            else
            {
              var prefix = '&';
            }
            if(url.indexOf('color=') == -1)
            {
              url = url + prefix + 'color='+ color + '&index='+index;
            }
            else
            {
              var temp = url.split('color=');
              url = temp[0] + 'color='+ color + '&index='+index;
            }
            a.attr('href', url);
          }
  			}
  		},
 	});
}