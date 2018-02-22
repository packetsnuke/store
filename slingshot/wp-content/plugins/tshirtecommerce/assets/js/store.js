var designer = {
	show: function(elm)
	{
		var div = jQuery(elm);
		
		var check = div.data('active');
		if ( typeof check != 'undefined' && check == 1)
		{
			div.hide('slow');
			div.data('active', 0);
		}
		else
		{
			div.show('slow');
			div.data('active', 1);
		}
	}
}
var payment = {
	key: function(api, ids, design_id){
		if(jQuery('#arts-store-mask').length == 0)
		{
			jQuery('body').append('<div id="arts-store-mask"><span>Creating File Output...</span></div>');
		}
		jQuery('body').css('overflow', 'hidden');
		jQuery('#arts-store-mask').show();		
		var url = ajaxurl + '?action=store_ajax_key&api_key='+api+'&arts='+ids+'&order_id='+design_id;
		jQuery.ajax({
			url: url,
		}).done(function(response) {
			if(response != '')
			{
				var data = eval ("(" + response + ")");
				if(typeof data.error != 'undefined' && data.error == 1)
				{
					if(typeof data.reload != 'undefined' && data.reload != 0)
					{
						location.reload();
						return false;
					}
					
					alert(data.msg);
					jQuery('.arts-store').parents('td').find('a').each(function(){
						var href = jQuery(this).attr('href');
						if(href.indexOf(design_id) != -1)
						{
							jQuery(this).remove();
						}
					});
				}
			}
			jQuery('#arts-store-mask').hide();
			jQuery('body').css('overflow', 'visible');
		});
	},
	removeLink: function(design_id){
		jQuery('.arts-store').parents('td').find('a').each(function(){
			var href = jQuery(this).attr('href');
			if(href.indexOf(design_id) != -1)
			{
				jQuery(this).remove();
			}
		});
	},
	load: function(e){
		var id = jQuery(e).data('id');
		jQuery('.arts-store-payment').html('<a href="javascript:void(0);" onclick="payment.cancel()"></a><iframe id="store-art-payment" scrolling="no" frameborder="0" noresize="noresize" width="100%" height="600px" src="http://store.9file.net/api/index/'+id+'"></iframe>');
		if(jQuery('#arts-store-mask').length == 0)
		{
			jQuery('body').append('<div id="arts-store-mask"></div>');
		}
		jQuery('body').css('overflow', 'hidden');
		jQuery('#arts-store-mask').show();
		jQuery('.arts-store-payment').show('slow');	
		jQuery('#arts-store-mask').css('display', 'none');
	},
	cancel: function(){
		jQuery('#arts-store-mask').hide();
		jQuery('.arts-store-payment').hide('slow');
		jQuery('body').css('overflow', 'auto');
		jQuery('.arts-store-payment').html('');
	}
}
window.addEventListener("message", function(event) {
    var txt = event.data;
	window.location.href = txt;
});