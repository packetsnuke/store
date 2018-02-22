var design = {};
var fields = [];
var designer_product = [];
var designer_vectors = {};
var siteURL = '';
jQuery('body').append('<div id="dg-design-ideas"></div>');
var quick_designer = {
	lang: [],
	hide_quickview: 0,
	product:{
		mask: function(remove){
			if(remove == true)
			{
				jQuery('.mask-image').remove();
			}
			else
			{
				jQuery('.type-product .images').append('<div class="mask-image"></div>');
			}
		},
		load: function(product_id, index){
			var wp_ajaxurl	= woocommerce_params.ajax_url;
			var data = {
				action: 'tshirtecommerce_product_image_load',
				product_id: product_id,
				index: index
			};
			jQuery.ajax({
				url: wp_ajaxurl,
				method: "POST",
				dataType: "json",
				data: data
			}).done(function(response) {
				if(response.error != 'undefined' && response.error == 0)
				{
					designer_product = response.design;
					hide_quickview = response.hide_quickview
					quick_designer.product.images(designer_product, index);
				}
				quick_designer.product.mask(true);
			});
		},
		images: function(design, index){
			var url = siteURL;
			var div = jQuery('.product .images');
			if(div.length > 0)
			{
				if(div.children('.thumbnails').length == 0)
				{
					div.append('<div class="thumbnails columns-4"></div>');
				}
				var thumbnails	= jQuery('.product .images .thumbnails');
				
				var views 	= ['front', 'back', 'left', 'right'];
				var html 	= '<div class="app-designer">';
				var added 	= 0;
				jQuery.each(views, function(i, active){
					if(typeof design[active] != 'undefined')
					{
						var view = design[active];
						if(typeof view[index] != 'undefined' && view[index] != '')
						{
							var temp = (box_height * 100)/box_width;
							if(temp != 100)
								html = html + '<div class="wapper-designer" data-id="'+active+'" style="height:'+temp+'px;" onclick="quick_designer.product.view(this)" id="wapper-view-'+active+'">';
							else
								html = html + '<div class="wapper-designer" data-id="'+active+'" onclick="quick_designer.product.view(this)" id="wapper-view-'+active+'">';
							var items = eval ("(" + view[index] + ")");
							jQuery.each(items, function(i, item){
								var zoom = (100/ box_width);
								if(item.id != 'area-design')
								{
									added = 1;
									var width = item.width;
									width = width.replace('px', '');
									width = width * zoom;
									
									var height = item.height;
									height = height.replace('px', '');
									height = height * zoom;
									
									var left = item.left;
									left = left.replace('px', '');
									left = left * zoom;
									
									var top = item.top;
									top = top.replace('px', '');
									top = top * zoom;
									
									var src = item.img;
									if(src.indexOf('http') == -1)
									{
										src = url + src;
									}
									var extra_class= '';
									var style = '';
									if(typeof item.is_change_color != 'undefined' && item.is_change_color == 1)
									{
										extra_class = ' is_change_color';
										var color = jQuery('.list-colors .bg-colors.active').data('color');
										style = 'background-color:#'+color+';';
									}
									html = html + '<img class="attachment-shop_thumbnail size-shop_thumbnail thumb-product-design '+extra_class+'" src="'+src+'" style="width:'+width+'px; height:'+height+'px; top:'+top+'px; left:'+left+'px; z-index:'+item.zIndex+';'+style+'" alt="">';
								}
								else
								{
									var area = design.area[active];
									if(area != '')
									{
										var params = eval ("(" + area + ")");
										var width = params.width;
										width = width * zoom;
										
										var height = params.height;
										height = height * zoom;
										
										var left = params.left;
										left = left.replace('px', '');
										left = left * zoom;
										
										var top = params.top;
										top = top.replace('px', '');
										top = top * zoom;
										var zIndex = 1000;
										if(typeof params.zIndex != 'undefined')
										{
											zIndex = params.zIndex;
										}
										var radius = '';
										if(typeof params.radius != 'undefined')
										{
											var border = params.radius;
											border = border.replace('px', '');
											radius = 'border-radius:'+border+'px';
										}
										html = html + '<div class="area-design area-'+active+'" style="width:'+width+'px; height:'+height+'px; top:'+top+'px; left:'+left+'px;z-index:'+zIndex+';'+radius+'"></div>';
									}
								}
							});
							html = html + '</div>';
							
						}
					}
				});
				html = html + '</div>';
				thumbnails.html(html);
				
				if(added == 1)
				{
					if(jQuery('.woocommerce-main-image').length > 0)
					{
						jQuery('.woocommerce-main-image').hide();
					}
					if(jQuery('.product .images .product_design').length == 0)
					{
						jQuery('<div class="product_design"></div>').insertBefore('.product .images .thumbnails');
					}
					var div = jQuery('.app-designer .wapper-designer');
					if(typeof div[0] != 'undefined')
					{
						quick_designer.product.view(div[0]);
					}
				}
				if(jQuery('.product_design').parent().find('.woocommerce-product-gallery__wrapper').length > 0)
				{
					jQuery('.product_design').parent().find('.woocommerce-product-gallery__wrapper').hide();
					jQuery('.product_design').parent().find('.woocommerce-product-gallery__trigger').hide();
					jQuery('.product_design').parent().find('.flex-viewport').hide();
				}
			}
			quick_designer.product.mask(true);
			quick_designer.design.load();
		},
		view: function(e){
			jQuery('.app-designer .wapper-designer').removeClass('active');
			jQuery(e).addClass('active');
			var view = jQuery(e).data('id');
			var div = jQuery('.product .images .product_design');
			var html = jQuery(e).html();
			div.html('<span class="icon-zoom" onclick="quick_designer.product.zoom(false);"></span>'+html);
			var width = div.parent().width();
			quick_designer.product.items(div, width);
			quick_designer.custom.ini(quick_designer.lang);
		},
		items: function(div, width){
			var zoom = width / 100;
			div.width(width);
			var height = (box_height * width)/box_width;
			div.height(height);
			div.children('img').each(function(){
				var img 	= jQuery(this);
				var item 	= [];
				var width 	= img.css('width').replace('px', '');
				item.width 	= width * zoom;
				
				var height 	= img.css('height').replace('px', '');
				item.height 	= height * zoom;
				
				var top 	= img.css('top').replace('px', '');
				item.top 	= top * zoom;
				
				var left 	= img.css('left').replace('px', '');
				item.left 	= left * zoom;
				img.css({'width':item.width+'px', 'height':item.height+'px', 'top':item.top+'px', 'left':item.left+'px'});
			});
			var area 	= div.children('.area-design');
			var item 	= [];
			var width 	= area.css('width').replace('px', '');
			item.width 	= width * zoom;
			
			var height 	= area.css('height').replace('px', '');
			item.height = height * zoom;
			
			var top 	= area.css('top').replace('px', '');
			item.top 	= top * zoom;
			
			var left 	= area.css('left').replace('px', '');
			item.left 	= left * zoom;
			area.css({'width':item.width+'px', 'height':item.height+'px', 'top':item.top+'px', 'left':item.left+'px'});
			quick_designer.design.load();
		},
		zoom: function(out){
			var areaDesign = jQuery('.product_design').parent();
			if(jQuery('.mask-design-zoom').length == 0)
			{
				jQuery('body').append('<div class="mask-design-zoom"></div>');
			}
			var mask = jQuery('.mask-design-zoom');
			if(out == true)
			{
				mask.hide();
				areaDesign.removeClass('design-zoom');
			}
			else
			{
				mask.show();
				areaDesign.addClass('design-zoom');
				
				if(jQuery('.design-zoom .view-close').length == 0)
				{		
					jQuery('.design-zoom').append('<span class="view-close" onclick="quick_designer.product.zoom(true);"><img src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTZweCIgdmVyc2lvbj0iMS4xIiBoZWlnaHQ9IjE2cHgiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgNjQgNjQiPgogIDxnPgogICAgPHBhdGggZmlsbD0iI2NjY2NjYyIgZD0iTTI4Ljk0MSwzMS43ODZMMC42MTMsNjAuMTE0Yy0wLjc4NywwLjc4Ny0wLjc4NywyLjA2MiwwLDIuODQ5YzAuMzkzLDAuMzk0LDAuOTA5LDAuNTksMS40MjQsMC41OSAgIGMwLjUxNiwwLDEuMDMxLTAuMTk2LDEuNDI0LTAuNTlsMjguNTQxLTI4LjU0MWwyOC41NDEsMjguNTQxYzAuMzk0LDAuMzk0LDAuOTA5LDAuNTksMS40MjQsMC41OWMwLjUxNSwwLDEuMDMxLTAuMTk2LDEuNDI0LTAuNTkgICBjMC43ODctMC43ODcsMC43ODctMi4wNjIsMC0yLjg0OUwzNS4wNjQsMzEuNzg2TDYzLjQxLDMuNDM4YzAuNzg3LTAuNzg3LDAuNzg3LTIuMDYyLDAtMi44NDljLTAuNzg3LTAuNzg2LTIuMDYyLTAuNzg2LTIuODQ4LDAgICBMMzIuMDAzLDI5LjE1TDMuNDQxLDAuNTljLTAuNzg3LTAuNzg2LTIuMDYxLTAuNzg2LTIuODQ4LDBjLTAuNzg3LDAuNzg3LTAuNzg3LDIuMDYyLDAsMi44NDlMMjguOTQxLDMxLjc4NnoiLz4KICA8L2c+Cjwvc3ZnPgo=" /></span>');
				}
			}
			var div = jQuery('.app-designer .wapper-designer.active');
			if(typeof div[0] != 'undefined')
			{
				quick_designer.product.view(div[0]);
			}
		}
	},
	design:{
		load: function(){
			quick_designer.product.mask();
			if(typeof designer_vectors.front == 'undefined')
			{
				var id = jQuery('.designer_rowid').val();
				if(id != '' && id != 'blank')
				{
					var wp_ajaxurl	= woocommerce_params.ajax_url;
					var data = {
						action: 'tshirtecommerce_design_load',
						design_id: id
					};
					jQuery.ajax({
						url: wp_ajaxurl,
						method: "POST",
						dataType: "json",
						data: data
					}).done(function(response) {
						if(typeof response.vectors != 'undefined')
						{
							quick_designer.lang = response.lang;
							
							if(typeof response.fields != 'undefined')
							{
								fields = response.fields;
							}
							
							var vectors = eval ("(" + response.vectors + ")");
							jQuery.each(vectors, function(view, items){
								designer_vectors[view] = {};
								jQuery.each(items, function(i, item){								
									designer_vectors[view][item.id] = item;
								});
							});
							quick_designer.design.vectors(designer_vectors);
						}
						else
						{
							jQuery('.customize-design').remove();
						}
						setTimeout(function(){
							quick_designer.custom.ini(quick_designer.lang);
							quick_designer.product.mask(true);
						}, 1000);
					});
				}
				else
				{
					quick_designer.product.mask(true);
					jQuery('.customize-design').remove();
				}
			}
			else
			{
				quick_designer.design.vectors(designer_vectors);
				quick_designer.product.mask(true);
			}
		},
		vectors: function(vectors){
			jQuery.each(vectors, function(view, items){
				jQuery('.area-design.area-'+view).html('<div class="design-items"></div>');
				quick_designer.design.items(view, items);
			});
		},
		item: function(svg){
			var zoom = jQuery(svg).parent().data('zoom');
			var width = svg.getAttributeNS(null, 'width');
			width = width * zoom;
			svg.setAttributeNS(null, 'width', width);
			
			var height = svg.getAttributeNS(null, 'height');
			height = height * zoom;
			svg.setAttributeNS(null, 'height', height);
			
			var img = jQuery(svg).find('image');
			if (typeof img[0] != 'undefined')
			{
				var imgW 	= img[0].getAttributeNS(null, 'width') * zoom;
				var imgH 	= img[0].getAttributeNS(null, 'height') * zoom;
				img[0].setAttributeNS(null, 'width', imgW);
				img[0].setAttributeNS(null, 'height', imgH);
			}
		},
		items: function(view, items){
			var obj = jQuery('.area-design.area-'+view+' .design-items');
			for(i=0; i<obj.length; i++)
			{
				var div = jQuery(obj[i]);
				var width = div.parent().parent().width();			
				var zoom = width/box_width;
				div.html('');
				jQuery.each(items, function(j, item){
					var span = document.createElement('span');
					if (item.type == 'team')
					{
						if ( typeof item.isNumber != 'underline' && item.isNumber == 1)
							span.className = 'drag-item drag-item-number layer-item-'+item.id;
						else
							span.className = 'drag-item drag-item-name layer-item-'+item.id;
					}
					else
					{			
						span.className 	= 'drag-item layer-item-'+item.id;
					}
					span.item 			= item;
					item.id 			= item.id;
					var style 			= [];
					
					style.width 		= item.width.replace('px', '') * zoom;
					style.height 		= item.height.replace('px', '') * zoom;
					style.left 			= item.left.replace('px', '') * zoom;
					style.top 			= item.top.replace('px', '') * zoom;
					
					span.style.left 	= style.left +'px';
					span.style.top 		= style.top +'px';
					span.style.width 	= style.width +'px';
					span.style.height 	= style.height +'px';
					
					jQuery(span).data('id', item.id);
					jQuery(span).data('type', item.type);
					jQuery(span).data('zoom', zoom);
					
					if(typeof item.rotate != 'undefined' && item.rotate != 0)
					{
						var rotate = parseInt(item.rotate) * Math.PI / 180;
						jQuery(span).css('transform', 'rotate(' + rotate + 'rad)');
					}
					
					if (typeof item.file != 'undefined')
					{
						jQuery(span).data('file', item.file);
					}
					else
					{
						item.file = {};
						jQuery(span).data('file', item.file);
					}
					jQuery(span).data('width', item.width);
					jQuery(span).data('height', item.height);
					
					span.style.zIndex = item.zIndex;
					var htmlSVG = item.svg;
					jQuery(document).triggerHandler( "before.imports.item.design", [span, item]);		
					jQuery(span).append(item.svg);
					item.svg = htmlSVG;
					var svg = jQuery(span).children('svg')[0];
					div.append(span);
					quick_designer.design.item(svg);
					jQuery(document).triggerHandler( "after.imports.item.design", [span, item]);
				});
			}
		}
	},
	custom:{
		ini: function(lang){
			if(hide_quickview == 1) return false;
			var div = jQuery('.customize-design');
			if(div.length > 0 && div.html().length == 0 && jQuery('.product_design .design-items .drag-item').length > 0)
			{
				if(typeof lang.title != 'undefined')
					var title = lang.title;
				else
					var title = 'Edit this design template';
				div.append('<h3>'+title+'</h3>');
				div.show();
				this.load(div);
			}
		},
		load: function(div){
			var i = 0;
			jQuery('.product_design .design-items .drag-item').each(function(){
				var item = this.item;
				if(item.type == 'clipart' && item.upload == 1)
				{
					quick_designer.custom.addPhoto(div, item);
				}
			});
			jQuery('.product_design .design-items .drag-item').each(function(){
				var item = this.item;
				if(item.type == 'text' && item.svg.indexOf('textPath') == -1)
				{					
					quick_designer.custom.addText(i, div, item);
					i++;
				}				
			});
			setTimeout(function(){
				jQuery('.customize-design .color').spectrum({
					showInput: true,
					preferredFormat: "hex",
					change: function(color) {
						var hex = color.toHexString();
						quick_designer.custom.changeColor(this);
					}
				});
			}, 500);
		},
		clear: function(e, id){
			jQuery(e).parents('.custom-col').remove();
			jQuery('.layer-item-'+id).remove();
			quick_designer.vectors.update(id, 'remove', 'img')
		},
		addText: function(i, div, item){
			var texts = item.text.split('\n');
			if(typeof item.field != 'undefined' && item.field.id != 'undefined')
			{
				if(typeof fields[item.field.id] != 'undefined')
					var label = fields[item.field.id];
				else
					var label = item.field.name;
			}
			else
			{
				var label = quick_designer.lang.text_line+' '+i;
			}
			var html = '<div class="custom-row">'
					 + 	'<label class="custom-label">'+label+'</label>'
					 + 	'<div class="input-group">';
			html = html + '<div class="group-left">';
			
			for(i=0; i<texts.length;i++)
			{
				html = html + '<input type="text" value="'+texts[i]+'" data-index="'+i+'" id="text-item-line-'+i+'-'+item.id+'" onkeyup="quick_designer.custom.changeText(this)" class="input-edit">';
			}
			html = html + '</div>';		
			html = html +	'<input type="text" class="color" value="'+item.color+'">'
					 + 		'<button type="button" onclick="quick_designer.custom.updateText(this)" class="btn-apply">'+quick_designer.lang.apply+'</button>'
					 + 	'</div>'
					 + '</div>';
			div.append(html);
		},
		addPhoto: function(div, item){
			if(typeof item.field != 'undefined' && item.field.id != 'undefined')
			{
				if(typeof fields[item.field.id] != 'undefined')
					var label = fields[item.field.id];
				else
					var label = item.field.name;
			}
			else
			{
				var label = quick_designer.lang.image;
			}
			var html = '<div class="custom-col">'
					+  		'<label class="custom-label">'+label+'</label>'
					+ 	 	'<div class="custom-image">'
					+ 	 		'<img onclick="quick_designer.custom.changePhoto(this, '+item.id+')" src="'+item.thumb+'" width="100" alt="">'
					+ 	 	'</div>'
					+ 	 	'<div class="custom-action">'
					+ 	 		'<a onclick="quick_designer.custom.changePhoto(this, '+item.id+')" href="javascript:void(0);" title="">'+quick_designer.lang.change+'</a> | <a href="javascript:void(0);" onclick="quick_designer.custom.clear(this, '+item.id+')" title="">'+quick_designer.lang.remove+'</a>'
					+ 	 	'</div>'
					+  '</div>';
			div.append(html);
		},
		changePhoto: function(e, index){
			if(jQuery('.mask-design-zoom').length == 0)
			{
				jQuery('body').append('<div class="mask-design-zoom"></div>');
			}
			var mask = jQuery('.mask-design-zoom');
			if(jQuery('.design-upload').length > 0)
			{
				jQuery('.design-upload').remove();
			}
			jQuery('body').append('<div class="design-upload" style="display:none;"><span class="text-loading">Uploading...</span><form id="files-upload-form"><input type="file" name="myfile" id="files-upload" autocomplete="off"></form></div>');
			
			jQuery("#files-upload").change(function() {
				mask.show();
				jQuery('.design-upload').show();
				var file = this.files[0];
				var imagefile = file.type;
				var match= ["image/jpeg","image/png","image/jpg"];
				if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2])))
				{
					alert('Please upload image');
					mask.hide();
					return false;
				}
				else
				{
					var fr = jQuery('#files-upload-form');
					jQuery.ajax({
						url: siteURL + 'ajax.php?type=upload&remove=0',
						type: "POST",
						data: new FormData(fr[0]),
						contentType: false, 
						cache: false,
						processData:false,
						success: function(content)
						{
							mask.hide();
							jQuery('.design-upload').remove();
							var img = jQuery(e).parents('.custom-col').find('img');
							if(typeof img[0] != 'undefined')
							{
								var media 	= eval('('+content+')');
								if (media.status == 1)
								{
									var src = siteURL + media.src;
									var newImage = new Image();
									newImage.onload = function() {
										media.width = this.width;
										media.height = this.height;
										jQuery(img[0]).attr('src', src);
										jQuery('.layer-item-'+index).each(function(){
											var item = this.item;
											var img = jQuery(this).find('image');
											if(typeof img[0] != 'undefined')
											{
												img[0].setAttributeNS('http://www.w3.org/1999/xlink', 'href', src);
												var old_width = item.width;
												var old_width = old_width.replace('px', '');
												var old_height = item.height;
												var old_height = old_height.replace('px', '');
												var width = jQuery(this).width();
												if(width < old_width)
												{
													width = old_width;
												}
												var height = jQuery(this).height();
												if(height < old_height)
												{
													height = old_height;
												}
												if(media.width > media.height)
												{
													var newWidth = width;
													var newHeight = (media.height * width)/media.width;
												}
												else
												{
													var newHeight = height;
													var newWidth = (media.width * height)/media.height;
												}
												img[0].setAttributeNS(null, 'width', newWidth);
												img[0].setAttributeNS(null, 'height', newHeight);
												jQuery(this).css({'width':newWidth+'px', 'height':newHeight+'px'});
												jQuery(this).find('svg').attr('width', newWidth).attr('height', newHeight);
												
												if(jQuery(this).parents('.product_design').length > 0)
												{
													media.width = newWidth;
													media.height = newHeight;
												}
											}										
										});
										quick_designer.vectors.update(index, 'image', media);
									};
									newImage.src = src;
								}
							}
						}
					});
				}
			});
			
			jQuery('#files-upload').click();
		},
		changeColor: function(e){
			jQuery(e).parent().children('.btn-apply').show();
		},
		changeText: function(e){
			jQuery(e).parent().parent().children('.btn-apply').show();
		},
		updateText: function(e){
			var div = jQuery(e).parent();
			jQuery(e).hide();
			var input = div.find('.input-edit');
			var id = jQuery(input[0]).attr('id');
			var line = jQuery(input[0]).data('index');
			var index = id.replace('text-item-line-'+line+'-', '');
			var color = div.children('.color').val();
			this.text.color(index, color);
			
			var texts = '';
			div.find('.input-edit').each(function(){
				var txt = jQuery(this).val();
				line = jQuery(this).data('index');
				quick_designer.custom.text.txt(index, txt, line);
				if(texts == '')
				{
					texts = txt;
				}
				else
				{
					texts = texts + '\n' + txt;
				}
			});
			quick_designer.vectors.update(index, 'text', texts);
		},
		text:{
			color: function(index, color){
				jQuery('.layer-item-'+index).each(function(){
					var txt = jQuery(this).find('text');
					txt.attr('fill', color);
				});
				quick_designer.vectors.update(index, 'textColor', color);
			},
			txt: function(index, txt, line){
				jQuery('.layer-item-'+index).each(function(){
					jQuery(this).find('text').each(function(){
						var tspan = jQuery(this).find('tspan');
						if(typeof tspan[line] != 'undefined' && txt != '')
						{
							tspan[line].textContent = txt;
						}						
					});
					var size = quick_designer.custom.changeSize(this);
					if(jQuery(this).parents('.product_design').length > 0)
					{
						quick_designer.vectors.update(index, 'size', size);
					}
				});
			}
		},
		changeSize: function(e){
			var svg = jQuery(e).find('svg');
			var viewBox = svg[0].getAttributeNS(null, 'viewBox'),
				width = svg[0].getAttributeNS(null, 'width'),
				height = svg[0].getAttributeNS(null, 'height');
			var view = viewBox.split(' ');
			var txt = jQuery(e).find('text');
			var size = txt[0].getBoundingClientRect();
			var w = size.width;
			var h = size.height * width / w;
			var newWidth = ((size.width * view[2])/width);
			var newHeight = ((size.height * view[3])/height);
			svg[0].setAttributeNS(null, 'viewBox', view[0]+' '+view[1]+' '+ newWidth +' '+ view[3]);
			var size = {};
			size.width = width;
			size.height = height;
			return size;
		}
	},
	vectors:{
		update: function(index, lable, value){
			var view = this.getview();
			if(typeof designer_vectors[view] != 'undefined' && typeof designer_vectors[view][index] != 'undefined')
			{
				if(lable == 'textColor')
				{
					designer_vectors[view][index].color = value;
				}
				else if(lable == 'text')
				{
					designer_vectors[view][index].text = value;
				}
				else if(lable == 'size')
				{
					var areaWidth = jQuery('.product_design').width();
					var width = (box_width * value.width)/areaWidth;
					
					var areaHeight = jQuery('.product_design').height();
					var height = (box_height * value.height)/areaHeight;
					
					designer_vectors[view][index].width = width+'px';
					designer_vectors[view][index].height = height+'px';
				}
				else if(lable == 'remove')
				{
					delete designer_vectors[view][index];
				}
				else if(lable == 'image')
				{
					designer_vectors[view][index].file_name = value.item.file_name;
					designer_vectors[view][index].title = value.item.title;
					designer_vectors[view][index].thumb = siteURL + value.item.thumb;
					designer_vectors[view][index].url = siteURL + value.item.thumb;
					
					var areaWidth = jQuery('.product_design').width();
					var width = (box_width * value.width)/areaWidth;
					
					var areaHeight = jQuery('.product_design').height();
					var height = (box_height * value.height)/areaHeight;
					
					designer_vectors[view][index].width = width+'px';
					designer_vectors[view][index].height = height+'px';
				}
				var svg = jQuery('.product_design .layer-item-'+index).html();
				jQuery('body').append('<div id="svg-temp" style="display:none;">'+svg+'</div>');
				var svg = jQuery('#svg-temp').find('svg');
				if(typeof svg[0] != 'undefined')
				{
					var zoom = jQuery('.product_design .layer-item-'+index).data('zoom');
					var width = svg[0].getAttributeNS(null, 'width');
					width = width / zoom;
					svg[0].setAttributeNS(null, 'width', width);
					
					var height = svg[0].getAttributeNS(null, 'height');
					height = height / zoom;
					svg[0].setAttributeNS(null, 'height', height);
					
					if(lable == 'image')
					{
						var img = jQuery(svg[0]).find('image');
						if(typeof img[0] != 'undefined')
						{
							img[0].setAttributeNS(null, 'width', width);
							img[0].setAttributeNS(null, 'height', height);
						}
					}
					
					designer_vectors[view][index].svg = jQuery('#svg-temp').html();
					jQuery('#svg-temp').remove();
				}
			}
			quick_designer.cart();
		},
		getview: function(){
			var view = jQuery('.app-designer .wapper-designer.active').data('id');
			if(view == 'undefined')
				view = 'front';
			return view;
		}
	},
	canvas: {
		width:box_width+'px',
		height:box_height+'px',
		index: 0,
		ini: function(e){
			quick_designer.product.mask();
			var obj = {};
			obj = this.product(obj);
			obj = this.items(obj);
			var views = {};
			jQuery.each(obj, function(view, items){
				var i = 0;
				var arr = [];
				jQuery.each(items, function(id, item){
					arr[i] = item;
					i++;
				});
				arr.sort(function(obj1, obj2) {
					return obj1.zIndex - obj2.zIndex;
				});
				views[view] = arr;
			});
			var wp_ajaxurl	= woocommerce_params.ajax_url;
			var data = {
				action: 'tshirtecommerce_quick_add_cart',
				views: views,
				box_width: box_width,
				box_height: box_height,
				rowid: jQuery('.designer_rowid').val(),
				vectors: designer_vectors,
			};
			jQuery.ajax({
				url: wp_ajaxurl,
				method: "POST",
				dataType: "json",
				data: data
			}).done(function(response) {
				setTimeout(function(){
					quick_designer.product.mask(true);
					jQuery(e).data('saved', 1).trigger( "click" );
				}, 1000);
				if(JSON.stringify(response.error) == 0)
				{
					jQuery('.designer_images').val(JSON.stringify(response.thumbs));
					jQuery('.designer_rowid').val(response.rowid);
				}
			});
		},
		product: function(obj){
			var zoom = box_width/100;
			jQuery('.wapper-designer .thumb-product-design').each(function(){
				var item = {};
				item.type = 'product';
				var width = jQuery(this).css('width').replace('px', '');
				var height = jQuery(this).css('height').replace('px', '');
				var top = jQuery(this).css('top').replace('px', '');
				var left = jQuery(this).css('left').replace('px', '');
				var zindex = jQuery(this).css('z-index');
				if(zindex == 'auto')
				{
					zindex = 0;
				}
				
				item.width = width * zoom;		
				item.height = height * zoom;
				item.top = top * zoom;				
				item.left = left * zoom;				
				item.zindex = zindex;				
				item.src = jQuery(this).attr('src');
				var view = jQuery(this).parent().data('id');
				if(typeof obj[view] == 'undefined')
				{
					obj[view] = {};
				}
				obj[view][quick_designer.canvas.index] = item;
				quick_designer.canvas.index++;
			});
			return obj;
		},
		items: function(obj){
			jQuery('.wapper-designer .drag-item').each(function(){
				var item = {};
				item.type = 'item';
				var width = jQuery(this).css('width').replace('px', '');
				var height = jQuery(this).css('height').replace('px', '');
				var top = jQuery(this).css('top').replace('px', '');
				var left = jQuery(this).css('left').replace('px', '');
				var zindex = jQuery(this).css('z-index');
				if(zindex == 'auto')
				{
					zindex = 0;
				}
				var zoom = box_width/100;
				item.width = width * zoom;
				item.height = height * zoom;
				
				var areaDesign = jQuery(this).parents('.area-design');
				var areaTop = areaDesign.css('top').replace('px', '');
				var areaLeft = areaDesign.css('left').replace('px', '');
				
				item.top = (top * zoom) + (areaTop * zoom);				
				item.left = (left * zoom) + (areaLeft * zoom);				
				item.zindex = zindex;				
				var svg = jQuery(this).html();
				item.svg = quick_designer.canvas.svg(svg, item);
				var view = jQuery(this).parents('.wapper-designer').data('id');
				if(typeof obj[view] == 'undefined')
				{
					obj[view] = {};
				}
				obj[view][quick_designer.canvas.index] = item;
				quick_designer.canvas.index++;
			});
			return obj;
		},
		svg: function(svg, item){
			var checkImage = svg.indexOf('<imag');
			jQuery('body').append('<div id="svg-temp" style="display:none;">'+svg+'</div>');
			var svg = jQuery('#svg-temp').find('svg');
			if(typeof svg[0] != 'undefined')
			{
				var zoom = box_width/100;
				var width = svg[0].getAttributeNS(null, 'width');
				width = width * zoom;
				svg[0].setAttributeNS(null, 'width', width);
				
				var height = svg[0].getAttributeNS(null, 'height');
				height = height * zoom;
				svg[0].setAttributeNS(null, 'height', height);
				
				svg[0].setAttributeNS(null, 'x', item.left);
				svg[0].setAttributeNS(null, 'y', item.top);
				
				if(checkImage != -1)
				{
					var img = jQuery(svg[0]).find('image');
					if(typeof img[0] != 'undefined')
					{
						img[0].setAttributeNS(null, 'width', width);
						img[0].setAttributeNS(null, 'height', height);
					}
				}
				
				var svg = jQuery('#svg-temp').html();
				jQuery('#svg-temp').remove();
			}
			return svg;
		}
	},
	cart: function(){
		jQuery('.add_to_cart_custom_design').removeData('saved');
	}
}

if(typeof box_width != 'undefined')
{
	jQuery(document).ready(function(){		
		siteURL = URL_d_home+'/tshirtecommerce/';
		
		var product_id	= jQuery("input[name='product_id']").val();
		if(typeof product_id != 'undefined')
		{
			jQuery(document).on('product.color.images', function(event, e){
				quick_designer.product.mask();
				var index = jQuery(e).data('index');
				if(typeof designer_product.front == 'undefined')
				{
					quick_designer.product.load(product_id, index);
				}
				else
				{
					quick_designer.product.images(designer_product, index);
				}

				var color = jQuery(e).data('color');
				jQuery('.is_change_color').css('background-color', '#'+color);
			});
		}
		if(jQuery('.designer_rowid').length > 0 && jQuery('.designer_rowid').val() != '')
		{
			jQuery('.single_add_to_cart_button').addClass('add_to_cart_custom_design');
			jQuery('.add_to_cart_custom_design').click(function(){
				if(jQuery('.designer_rowid').length == 0)
				{
					return true;
				}
				if(typeof jQuery(this).data('saved') == 'undefined')
				{
					quick_designer.canvas.ini(this);
					return false;
				}
			});
		}
	});

	jQuery( window ).resize(function() {
		var div = jQuery('.app-designer .wapper-designer.active');
		if(typeof div[0] != 'undefined')
		{
			quick_designer.product.view(div[0]);
		}
	});
}
else
{
	var box_width = 500;
	var box_height = 500;
	siteURL = URL_d_home+'/tshirtecommerce/';
}