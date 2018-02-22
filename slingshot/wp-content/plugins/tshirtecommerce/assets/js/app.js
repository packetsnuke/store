function alert_text(msg){
	var text = msg.replace(/&#39;/g, "\'");
	alert(text);
}
function confirm_text(msg){
	var text = msg.replace(/&#39;/g, "\'");
	confirm(text);
}
var design_id = 0;
var app = {
	admin:{
		ini: function(){
			jQuery('#designer-products .tab-content a.modal-link').click(function(){
				var link = jQuery(this).attr('href');
				if(jQuery(this).hasClass('add-link'))
					app.admin.add(this);
				else
					app.admin.load(link);
				return false;
			});
		},
		product: function(e, index){
			if (document.getElementById('designer-products') == null)
			{
				var div = '<div class="modal fade" id="designer-products" tabindex="-1" role="dialog" style="z-index:10520;" aria-labelledby="myModalLabel" aria-hidden="true">'
						+ '<div class="modal-dialog modal-lg" style="width: 95%;">'
						+ 	'<div class="modal-content">'
						+		'<div class="modal-header">'
						+			'<button type="button" data-dismiss="modal" class="close close-list-design">'
						+				'<span aria-hidden="true">×</span>'
						+				'<span class="sr-only">Close</span>'
						+			'</button>'
						+		'</div>'
						+ 		'<div class="modal-body">'
						+		'&#65279;<center><h3>Please wait some time. loading...</h3></center>'
						+		'</div>'
						+	'</div>'
						+ '</div></div>';
				jQuery('body').append(div);
			}
			if(index != 4)
				jQuery('#designer-products').modal('show');			
			var key = e.getAttribute('key');			
			var data = {};
			data.key	= key;
			data.action = 'designer_action';
			var link = ajaxurl.split('wp-admin');
			
			if (index == 0)	// show list product design
			{
				var url = link[0]+'tshirtecommerce/admin-blank.php?/'+design_id;
			}
			else if (index == 2)	// show list product design template
			{
				var url = link[0]+'tshirtecommerce/admin-users.php';
			}
			else if (index == 3) // show list product design template
			{
				var url = link[0]+'tshirtecommerce/admin-create.php';
			}
			else if (index == 4)	// create new design
			{
				var url = site_e_url + '/tshirtecommerce/admin/index.php?/product/viewmodal&session_id='+session_id;				
				jQuery('#add_designer_product').html('<span class="button-resize button-resize-full" onclick="resizePageDesign(this)"></span><iframe id="tshirtecommerce-designer" frameborder="0" noresize="noresize" width="100%" height="800px" src="'+url+'"></iframe>');
				return;
			}
			else
			{
				var url = link[0]+'tshirtecommerce/admin.php';
			}
			jQuery.post(url, data, function(response) {
				jQuery('#designer-products .modal-body').html(response);
				app.admin.ini();
			});
			return false; 
		},		
		load: function(link)
		{
			var data = {};
			data.key	= '1';
			data.action = 'designer_action';
			data.link = link;
			var link = ajaxurl.split('wp-admin');
			var url = link[0]+'tshirtecommerce/admin.php';
			jQuery('#designer-products .modal-body').html('&#65279;<center><h3>Please wait some time. loading...</h3></center>');
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#designer-products .modal-body').html(response);				
				app.admin.ini();
			});
			return false; 
		},
		add: function(e)
		{
			var id = jQuery(e).data('id');
			id 		= String(id);
			if (jQuery(e).hasClass('design-idea') == true)
			{
				var url 	= site_e_url + '/tshirtecommerce/admin-template.php?product='+id+'&lightbox=1&session_id='+session_id;
			}
			else
			{
				if (id.indexOf(':') == -1)
				{
					var title 	= jQuery(e).data('title');
					var img 	= jQuery(e).children('img').attr('src');
					document.getElementById('_product_id').value = id;
					document.getElementById('_product_title_img').value = title +'::'+ img;
				
					var url 	= site_e_url + '/tshirtecommerce/admin/index.php?/product/viewmodal/'+id+'&session_id='+session_id;
				}
				else
				{
					var params = id.split(':');
					var url 	= site_e_url + '/tshirtecommerce/admin-template.php?user='+params[0]+'&id='+params[1]+'&product='+params[2]+'&color='+params[3]+'&lightbox=1&session_id='+session_id;
				}
			}
			var html 	= '<span class="button-resize button-resize-full" onclick="resizePageDesign(this)"></span><iframe id="tshirtecommerce-designer" frameborder="0" noresize="noresize" width="100%" height="800px" src="'+url+'"></iframe>';
			
			jQuery('#add_designer_product').html(html);
			jQuery('#designer-products').modal('hide');
		},
		product_detail: function(){
			var product 		= {};
			product.title 		= jQuery('#title').val();
			
			if (typeof tinyMCE != 'undefined' && jQuery('#wp-content-wrap').hasClass('html-active') == false)
			{
				product.description 	= tinyMCE.get("content").getContent();
			}
			else
			{
				product.description 		= jQuery('#content').val();
			}
			
			if (typeof tinyMCE != 'undefined' && jQuery('#wp-excerpt-wrap').hasClass('html-active') == false)
			{
				product.shortdescription = tinyMCE.get("excerpt").getContent();
			}
			else
			{
				product.shortdescription 	= jQuery('#excerpt').val();
			}
			
			var img 			= jQuery('#set-post-thumbnail img');
			if (img.length > 0)
				product.thumb 	= jQuery(img[0]).attr('src');
			else
				product.thumb	= '';
			product.sku 		= jQuery('#_sku').val();
			product.price		= jQuery('#_regular_price').val();
			product.sale_price 	= jQuery('#_sale_price').val();		
			
			return product;
		},
		clear: function(){
			if (jQuery('#tshirtecommerce-designer').length > 0)
			{
				var check = confirm('You sure want clear data design of this product?');
				if (check == true)
				{
					document.getElementById('_product_id').value = '';
					document.getElementById('_product_title_img').value = '';
					jQuery('#add_designer_product').html('');
				}
			}
		},
		save: function(data, type){
			jQuery('#tshirtecommerce-wapper').hide();
			jQuery('#publish').removeClass('disabled').val('Publish');
			jQuery('#publish').parent().find('.spinner').removeClass('is-active');
			
			if (type == 'product')
			{
				document.getElementById('_product_id').value = data;
			}
			else if(type == 'idea')
			{
				var ids = data.designer_id +':'+ data.design_id +':'+ data.product_id +':'+ data.productColor;
				document.getElementById('_product_id').value = ids;
			}
			jQuery('#post').submit();
		}
	},
	cart: function(content){
		var data = {
			action: 'woocommerce_add_to_cart',
			product_id: content.product_id,
			quantity: content.quantity,
			price: content.price,
			rowid: content.rowid,
			color_hex: content.color_hex,
			color_title: content.color_title,
			teams: content.teams,
			options: content.options,
			images: content.images			
		};
		
		if (typeof product_variation != 'undefined' && product_variation > 0 && typeof product_design_id != 'undefined' && content.product_id == product_design_id)
		{
			data.variation_id = product_variation;
			data.action = 'woocommerce_add_to_cart_variable_rc';
		}
		if (typeof product_attributes != 'undefined')
		{
			data.variation = product_attributes;
		}
		jQuery.ajax({
			url: wp_ajaxurl,
			method: "POST",
			dataType: "json",
			data: data
		}).done(function(response) {
			if(response != 0) {
				var src = jQuery('#tshirtecommerce-designer').attr('src');
				if (src.indexOf('mobile.php') != -1)
				{
					auto_redirect_cart = 1;
				}
				if ( typeof auto_redirect_cart != 'undefined' && auto_redirect_cart == 1)
				{
					if(typeof e_update_cart_item != 'undefined' && e_update_cart_item != '')
					{
						jQuery.get(e_update_cart_item, function(data, status){
							window.location.href = woo_url_cart+'?update=true';
						});
					}else
					{
						window.location.href = woo_url_cart;
					}
				}
				else
				{
					if(typeof e_update_cart_item != 'undefined' && e_update_cart_item != '')
					{
						jQuery.get(e_update_cart_item);
					}
					var div = jQuery('#tshirtecommerce-designer').parent().find('.tshirtecommerce-designer-cart');
					if (div.length == 0)
					{
						jQuery('<div class="tshirtecommerce-designer-cart"></div').insertBefore('#tshirtecommerce-designer');
					}
					var div = jQuery('.tshirtecommerce-designer-cart');
					div.html('');
					if (typeof response.fragments != 'undefined' && typeof response.fragments['div.widget_shopping_cart_content'] != 'undefined')
					{
						updateCartFragment();
						
						if (typeof wc_add_to_cart_params != 'undefined' && typeof wc_add_to_cart_params.i18n_view_cart != 'undefined')
						{
							var view_cart = wc_add_to_cart_params.i18n_view_cart;
						}
						else
						{
							var view_cart = 'View cart';
						}					
						div.html('<div class="woocommerce"><div class="woocommerce-message">'+text_cart_added+' <a href="' + woo_url_cart + '" class="button wc-forward" title="' + view_cart + '">' + view_cart + '</a></div></div>');
					}
					document.getElementById("tshirtecommerce-designer").contentWindow.design.mask(false);
				}
			}
		});
	},
	design: function(){
		var data = {};
		jQuery('.dg-image-load').each(function(){
			var index = jQuery(this).data('id');
			var str = jQuery(this).find('img').data('src');
			data[index] = str;
		});
		var img_url = tshirtecommerce_url + 'images.php';
		jQuery.ajax({
			url: img_url,
			type: "post",
			dataType: "json",
			data: data,
		}).done(function(images){
			jQuery('.dg-image-load').each(function(){
				var index = jQuery(this).data('id');
				var img = jQuery(this).find('img');
				img.removeAttr('data-src');
				if(typeof images[index] != 'undefined')
				{
					img.attr('src', tshirtecommerce_url +'uploaded/cache-images/'+ images[index]);
				}
				else
				{
					img.attr('src', img_url+'/'+index+'.png');
				}
			});
		});
		return false;
	},
	removeDesign: function(e){
		var check = confirm_text(confirm_remove_text);
		if(check == true)
		{
			var key = jQuery(e).data('id');
			if(key != '')
			{
				jQuery(e).parent().hide('slow');
			}
			var url = tshirtecommerce_url+'/ajax.php?type=removeDesign&id='+key;
			jQuery.ajax({url: url,type: "get"}).done(function(images){});
		}
		return;
	}
}

function updateCartFragment() {
	$supports_html5_storage = ( 'sessionStorage' in window && window['sessionStorage'] !== null );
	$fragment_refresh = {
		url: woocommerce_params.ajax_url,
		type: 'POST',
		data: { action: 'woocommerce_get_refreshed_fragments' },
		success: function( data ) {
			if ( data && data.fragments )
			{
				jQuery.each( data.fragments, function( key, value ) {
					jQuery(key).replaceWith(value);
					
				});

				if ( $supports_html5_storage ) {
					sessionStorage.setItem( "wc_fragments", JSON.stringify( data.fragments ) );
					sessionStorage.setItem( "wc_cart_hash", data.cart_hash );
				}
				jQuery('body').trigger( 'wc_fragments_refreshed' );
			}
		}
	};
	jQuery.ajax( $fragment_refresh );
}

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
	},
	mobile: function(added){
		var div = jQuery('#tshirtecommerce-designer').parent();
		if(added == false)
		{
			jQuery('body').removeClass('tshirt-mobile');
			div.removeClass('design-mobile');
		}
		else
		{
			jQuery('body').addClass('tshirt-mobile');
			div.addClass('design-mobile');
		}
	},
	setSize: function(type){
		var div = jQuery('.row-designer-tool');
		var postion = div.offset();
		var width = jQuery(document).width();
		div.css({
			'left': '-'+postion.left+'px',
			'width': width+'px',
		});
	}
}

// save product of woocommerce
function wooSave(data, type)
{
	app.admin.save(data, type);
}

function variationProduct(e)
{
	var variation_form = jQuery(e).parents('.variations_form');
	
	var variation_id = variation_form.find('.variation_id').val();
	
	if(variation_id == '')
	{
		alert_text(txt_select_variation_product);
		return false;
	}
	
	var stock = variation_form.find('.single_add_to_cart_button').hasClass('disabled');
	if(stock)
	{
		alert_text(txt_out_of_stock_variation_product);
		return false;
	}
	var item = '';
	
	variation_form.find('select[name^=attribute]').each(function() {
		var attribute = jQuery(this).attr("name");
		var attributevalue = jQuery(this).val();
		if (item == '')
			item = '&attributes=' + attribute +'|'+ attributevalue;
		else
			item = item +';'+ attribute +'|'+ attributevalue;
	});
	var product_id = variation_form.find( 'input[name=product_id]' ).val();
	if (product_id == '')
		product_id = variation_form.data('product_id');
	if (typeof product_id == 'undefined') product_id = '';
	
	var link = jQuery('.product-design-link').val();
	if (link != '' && product_id != '')
	{
		if (link.indexOf('?') == -1)
		{
			link = link + '?product_id='+product_id+'&variation_id='+variation_id +item;
		}
		else
		{
			link = link + '&product_id='+product_id+'&variation_id='+variation_id +item;
		}
		window.location.href = link;
	}
}

function setHeigh(height){
	height = height + 10;
	document.getElementById('tshirtecommerce-designer').setAttribute('height', height + 'px');
	
	height = height + 20;
	jQuery('#modal-designer').parents('body').css({'height':height+'px', 'max-height':height+'px'});
}

function getWidth()
{
	var width = jQuery(window).width();
	var sizeZoom = width/500;
	if (sizeZoom < 1)
	{
		jQuery('meta[name*="viewport"]').attr('content', 'width=device-width, initial-scale='+sizeZoom+', maximum-scale=1');
	}
}

// active link color
function loadProductDesign(e)
{
	var href = jQuery(e).attr('href');
	
	if (typeof jQuery(e).data('color') != 'undefined')
	{
		var color = jQuery(e).data('color');
		href = href + '&color='+color;
	}
	if(jQuery('.product-attributes .product-fields').length > 0)
	{
		var options = '';
		jQuery(".product-attributes .product-fields input[name^='attribute']").each(function(){
			var value = jQuery(this).val();
			var type = jQuery(this).attr('type');
			if(type != 'text' && jQuery(this).is(':checked') == false)
			{
				value = '';
			}
			if(value != '')
			{
				var temp = jQuery(this).attr('name');
				var id = temp.replace('attribute', '');
				id = id.split('[').join("");					
				id = id.split(']').join("");
				if(options == '')
				{
					options = id +'_'+ value;
				}
				else
				{
					options = options +'-'+ id +'_'+ value;
				}
			}
		});		
		if(options != '')
		{
			href = href + '&options='+options;
		}
	}	
	window.location.href = href;
	return false;
}

// click change color in page product detail
function e_productColor(e)
{
	var parent = jQuery(e).parent();
	parent.children('.bg-colors').removeClass('active');
	
	// add data
	var elm = jQuery(e);
	
	jQuery('.designer_color_index').attr('name', 'colors['+elm.data('index')+']').val(elm.data('color'));
	jQuery('.designer_color_hex').val(elm.data('color'));
	jQuery('.designer_color_title').val(elm.attr('title'));
	
	jQuery('.e-custom-product').data('color', elm.data('color'));
	
	elm.addClass('active');
	
	jQuery(document).triggerHandler( "product.color.images", e);
}

function tshirt_attributes(e, index)
{
	var elm = jQuery(e);
	var type = elm.attr('type');
	
	var obj = elm.parent().children('.attribute_'+index);
	if (typeof type == 'undefined')
	{
		var value = elm.find('option:selected').data('id');
		obj.val(value);
	}
	else if (type == 'checkbox' || type == 'radio')
	{
		if (elm.is(':checked') == true)
		{
			obj.prop('checked', true);
		}
		else
		{
			obj.prop('checked', false);
		}
	}
	else
	{
		obj.val(elm.val());
	}
}

function viewBoxdesign(){
	var width = jQuery(document).width();
	var height = jQuery(document).height();
	if (width < 510 || height < 510)
	{
		var url = urlDesignload.replace('index.php', 'mobile.php');
		if (disable_mobile_layout == 1)
		{
			jQuery('.row-designer-tool').html('<iframe id="tshirtecommerce-designer" scrolling="no" frameborder="0" noresize="noresize" width="100%" height="100%" src="'+url+'"></iframe>');
			jQuery('#tshirtecommerce-designer').css('min-height', '560px');
		}
		else
		{
			jQuery('body').append('<div id="modal-design-bg"></div><div id="modal-designer"><a href="'+urlBack+'" class="btn btn-dange btn-xs">Close</a><iframe id="tshirtecommerce-designer" scrolling="no" frameborder="0" width="100%" height="100%" src="'+url+'"></iframe></div>');
			jQuery('body').addClass('tshirt-mobile');
		}
	}
	else
	{
		jQuery('.row-designer-tool').html('<iframe id="tshirtecommerce-designer" scrolling="no" frameborder="0" noresize="noresize" width="100%" height="100%" src="'+urlDesignload+'"></iframe>');
	}
	
	var url_option = urlDesignload.split('tshirtecommerce/');
	var mainURL = url_option[0];
	
	if (logo_loading.indexOf('http') == - 1)
	{
		logo_loading = mainURL + logo_loading;
	}
	
	jQuery('.row-designer-tool').append('<div class="mask-loading">'
									+ '<div class="mask-main-loading">'
									+	'<img class="mask-icon-loading" src="'+mainURL+'tshirtecommerce/assets/images/logo-loading.gif" alt="">'
									+	'<img class="mask-logo-loading" src="'+logo_loading+'" alt="">'
									+ '</div>'
									+ '<p>'+text_loading+'</p>'
									+ '</div>');
	
	jQuery("#tshirtecommerce-designer").load( function() {
		setTimeout(function(){
			jQuery('.row-designer-tool .mask-loading').remove();
		}, 1000);
	});
}

function tshirt_close(){
	var href = jQuery('#modal-designer a').attr('href');
	window.location.href = href;
}
jQuery(document).ready(function(){
	design_id = jQuery('#_product_id').val();
	if(design_id == '')
		design_id = 0;
	var product_type = jQuery('#product-type').val();
	if(product_type == 'variable')
	{
		jQuery('.variations_options').children('a').trigger('click');
		jQuery('#tshirtecommerce_product a').trigger('click');
	}else
	{
		jQuery('#tshirtecommerce_product a').trigger('click');
	}
	
	if (jQuery('.row-designer-tool').length > 0)
	{
		viewBoxdesign();
	}
	
	// active product color
	if (jQuery('.designer-attributes .list-colors .bg-colors').length > 0)
	{
		if (jQuery('.designer-attributes .list-colors .bg-colors.active').length == 0)
		{
			var a = jQuery('.designer-attributes .list-colors .bg-colors');
			e_productColor(a[0]);
		}
		else
		{
			var a = jQuery('.designer-attributes .list-colors .bg-colors.active');
			e_productColor(a[0]);
		}
	}
	
	// product size
	if (typeof min_order != 'undefined' && jQuery('.quantity .input-text.qty').length > 0)
	{		
		// check add to cart
		jQuery( document ).on( 'click', '.single_add_to_cart_button', function() {
			var value = jQuery('.quantity .input-text.qty').val();
			if (value < min_order)
			{
				alert_text(txt_min_order + ' '+min_order);
				return false;
			}
		});
	}
	
	// change size
	jQuery('.p-color-sizes .size-number').on('change', function(){
		var value = jQuery(this).val();
		filter = /^[0-9]+$/;
		if (filter.test(value))
		{
			if (value.indexOf('0') == 0)
				jQuery(this).val(0);
		}
		else
		{
			jQuery(this).val(0);
		}
		
		var quantity = 0;
		jQuery('.p-color-sizes .size-number').each(function(){
			quantity = quantity + Math.round(jQuery(this).val());
		});
		jQuery('.quantity .input-text.qty').val(quantity);
	});
	
	// save product in wooommerce
	jQuery('input#publish').click(function(){
		if (jQuery('#tshirtecommerce-designer').length > 0)
		{
			var iframe = document.getElementById("tshirtecommerce-designer");
			
			if (typeof iframe.contentWindow.productCategory !== 'undefined' && jQuery.isFunction(iframe.contentWindow.productCategory))
			{
				// add product categories.
				function categories(evt, category, cate_id, parent_id, i, j){
					evt.children('li').each(function(){
						var text = jQuery(this).children('.selectit').text();
						var checked = jQuery(this).children('.selectit').children('input').is(':checked');
						var val = jQuery(this).children('.selectit').children('input').val();
						
						category[i] = {id:val, parent_id: parent_id, title:text};
						if(checked)
						{
							cate_id[j] = val;
							j++;
						}
						i++;
						
						if(jQuery(this).children('ul').hasClass('children'))
							categories(jQuery(this).children('.children'), category, cate_id, val, i, j);
					});
				};
				
				var cate_id = [],
				category = [];
				categories(jQuery('#product_catchecklist'), category, cate_id, 0, 0, 0);
				iframe.contentWindow.productCategory(cate_id, category);
			};
			
			// save products.
			var name = jQuery('#publish').attr('name');
			var value = jQuery('#publish').val();
			jQuery('#publishing-action').append('<input type="hidden" value="'+value+'" name="'+name+'"/>');
			
			var product_type = jQuery('#product-type').val();
			if(jQuery('#_regular_price').length > 0)
			{
				var price = jQuery('#_regular_price').val();
				if(price == '' && product_type == 'simple')
				{
					alert('Please add price of product');
					jQuery('.general_options').children('a').trigger('click');
					return false;
				}
				else if(product_type == 'variable')
				{
					if(jQuery('.variable_pricing .wc_input_price').length == 0)
					{
						alert('Please add variations of this product');
						jQuery('.variations_options').children('a').trigger('click');
						return false;
					}
					else
					{
						var input = jQuery('.variable_pricing .wc_input_price');
						var price = jQuery(input[0]).val();
						jQuery('#_regular_price').val(price);
						var prices = '';
						jQuery('.variable_pricing').each(function(){
							var input = jQuery(this).find('.wc_input_price');
							var price = 0;
							if(typeof input[0] != 'undefined')
							{
								price = jQuery(input[0]).val();
							}
							if(typeof input[1] != 'undefined' && jQuery(input[1]).val() != '')
							{
								price = jQuery(input[1]).val();
							}
							if(price != 0)
							{
								var index = jQuery(this).parents('.woocommerce_variation').find("input[name^='variable_post_id']").val();
								if(prices == '')
									prices = '"'+index +'":"'+ price +'"';
								else
									prices = prices +','+ '"'+ index +'":"'+ price +'"';								
							}							
						});
					}
				}
			}
			if(jQuery('#title').length > 0)
			{
				var title = jQuery('#title').val();
				if(title == '')
				{
					alert('Please add product name.');
					return false;
				}
			}
			var product = app.admin.product_detail();
			if(typeof prices != 'undefined' && prices != '')
			{
				product.prices	= '{'+prices+'}';
			}
			var check_validate = iframe.contentWindow.productInfo(product);
			if(check_validate)
			{
				jQuery('#tshirtecommerce_product a').trigger('click');
				jQuery(this).addClass('disabled');
				jQuery(this).parent().find('.spinner').addClass('is-active');
				jQuery(this).val('Saving...');
			}else
			{
				jQuery('#tshirtecommerce_product').children('a').trigger('click');
			}
			return false;
		}
	});
	
	// add box of product design
	if(jQuery('#_product_id').length > 0 && jQuery('#_disabled_product_design').val() != 1)
	{
		var id = jQuery('#_product_id').val();
		if (id != '')
		{
			var url = site_e_url + '/tshirtecommerce/admin/index.php?/product/viewmodal';
			if (id.indexOf(':') == -1)
			{
				url = url + '/' + id +'&session_id='+session_id;
			}
			else
			{
				var params = id.split(':');
				var url = site_e_url + '/tshirtecommerce/admin-template.php?user='+params[0]+'&id='+params[1]+'&product='+params[2]+'&color='+params[3]+'&lightbox=1&session_id='+session_id;
			}
			
			jQuery('#add_designer_product').html('<span class="button-resize button-resize-full" onclick="resizePageDesign(this)"></span><iframe id="tshirtecommerce-designer" frameborder="0" noresize="noresize" width="100%" height="800px" src="'+url+'"></iframe>');
		}
	}
});

function resizePageDesign(e){
	var check = jQuery(e).hasClass('button-resize-full');
	if(check)
	{
		jQuery(e).removeClass('button-resize-full');
		jQuery(e).addClass('button-resize-small');
		jQuery(e).parent('#add_designer_product').addClass('e-full-screen');
		var height = jQuery('#add_designer_product').height();
		jQuery(e).parent('#add_designer_product').find('#tshirtecommerce-designer').attr('height', height+'px');
		jQuery('body').css('overflow', 'hidden');
	}
	else
	{
		jQuery('body').css('overflow', 'auto');
		jQuery(e).removeClass('button-resize-small');
		jQuery(e).addClass('button-resize-full');
		jQuery(e).parent('#add_designer_product').removeClass('e-full-screen');
		jQuery(e).parent('#add_designer_product').attr('height', height);
		jQuery(e).parent('#add_designer_product').find('#tshirtecommerce-designer').attr('height', '800px');
	}
};

function getfullWidth() {
	if(jQuery('#modal-designer').length > 0)
	{
		var width = jQuery('#modal-designer').width();
	}
	else
	{
		var width = jQuery('#tshirtecommerce-designer').width();
	}	
	
	return width;
}

function dg_full_screen()
{
	if(jQuery('body').hasClass('dg_screen'))
	{
		jQuery('body').removeClass('dg_screen');
		return 0;
	}
	else
	{
		jQuery('body').addClass('dg_screen');
		return 1;
	}
}

function dgLoadImg()
{
	jQuery(window).scroll(function(){
		jQuery('.dg-image-load').each(function(){
			var img = jQuery(this).find('img');
			if ( img.attr('data-src') && jQuery(this).offset().top < (jQuery(window).scrollTop() + jQuery(window).height() + 50) )
			{
				var source = img.data('src');
				img.attr('src', source);
				img.removeAttr('data-src');
				img.removeClass('loading');
			}
		});
	});
}

jQuery(document).ready(function(){
	if(typeof e_remove_cart_item != 'undefined' && e_remove_cart_item == true)
		jQuery('.woocommerce-message').hide();
	
	jQuery('.e_tshirt_add').click(function(){
		jQuery(this).children('.dropdown-menu').toggle();
	});
	dgLoadImg();
});