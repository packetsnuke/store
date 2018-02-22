/* find all gallery in description of product */
function designer_gallery_js()
{
	var data = {};
	var i = 0;
	jQuery('.design-gallery').each(function(){
		var id = jQuery(this).data('id');
		var product_id = jQuery(this).data('index');
		data[id] = product_id;
		jQuery(this).addClass('design-gallery-'+id);
		i++;
	});
	if(i == 0)
	{
		return false;
	}
	return data;
}

function designer_gallery_add(canvas, item)
{
	var div = jQuery('.design-gallery-'+item.id);
	if(div.length > 0)
	{
		div.html('');
		div.append(canvas);
		if(item.type == 'simple')
		{
			div.append('<div class="product-gallery-map"></div>');
			design_gallery.map.elem = div;
			design_gallery.map.add(item);
		}
	}
	
}

var design_gallery = {
	viewDesign: function(e){
		var url = jQuery('#tshirtecommerce-designer').attr('src');
		var id = jQuery(e).data('id');
		var option = id.split('::');
		url = url.replace(/product=([0-9]+)\&/g, 'product='+option[1]+'&');
		url = url.replace(/parent=([0-9]+)\&/g, 'parent='+option[0]+'&');
		jQuery('#tshirtecommerce-designer').attr('src', url);
		design_gallery.map.close(e);
	},
	map: {
		elem: {},
		add: function(item){
			if(typeof item.layers == 'undefined') return;
			var map = this;
			var layers = item.layers;
			var css = '';
			jQuery.each(layers, function(i, layer){

				if(layer.type == 'img')
				{
					var style = layer.style;
					if(typeof style.btn != 'undefined' && style.btn.show == 1)
					{
						map.node(style.btn, layer.id);
						map.view(style.btn, layer.id);
						var str = map.style(style.btn, layer.id);
						css = css + str;
					}
				}
			});
			if(jQuery('head').find('.map-css').length == 0)
			{
				jQuery('head').append('<style type="text/css" class="map-css"></style>');
			}
			jQuery('.map-css').html(css);
			setTimeout(function(){
				map.move(0);
				jQuery('.layer-tooltip').dg_tooltip();
				jQuery(window).resize(function(){
					map.move(1);
				});
			}, 600);
		},
		move: function(update){
			var canvas = this.elem.children('canvas');
			if(typeof canvas[0] == 'undefined') return;
			var position = canvas.position();
			var width = canvas.width();
			var height = canvas.height();

			var div = this.elem.children('.product-gallery-map');
			div.css({
				'top': position.top+'px',
				'left': position.left+'px',
				'width': width+'px',
				'height': height+'px',
			});

			var max_width = jQuery(canvas[0]).attr('width');
			var max_height = jQuery(canvas[0]).attr('height');

			var zoom = width / max_width;

			div.find('a.btn-layer-action').each(function(){
				var e = jQuery(this);
				var top = e.data('top');
				var left = e.data('left');

				var w_icon = zoom * e.width();
				w_icon = (e.width() - w_icon)/2;
				var h_icon = zoom * e.height();
				h_icon = (e.height() - h_icon)/2;

				var new_top = (top * zoom) - h_icon;
				var new_left = (left * zoom) - w_icon;
				e.css({
					'top': new_top+'px',
					'left': new_left+'px',
				});
			});
		},
		node: function(data, index){
			var a = document.createElement('a');
			a.className = 'btn-layer-action layer-tooltip btn-layer-'+index+' btn-layer-'+data.btn_size+' btn-layer-style-'+data.btn_style;
			a.setAttribute('href', 'javascript:void(0);');
			a.setAttribute('onclick', 'design_gallery.map.show(this);');
			a.setAttribute('data-id', index);
			a.setAttribute('data-top', data.btn_top);
			a.setAttribute('data-left', data.btn_left);
			a.setAttribute('data-original-title', data.popup_title);
			a.innerHTML = '<i class="'+data.icon+'"></i>';
			this.elem.children('.product-gallery-map').append(a);
		},
		show: function(e){
			var id = jQuery(e).data('id');
			jQuery('.btn-layer-view').hide();
			var position = jQuery(e).position();
			var arrow 	= 'left', left = 0, top = 0;

			var box = jQuery(e).parent();
			var max_width = box.width();
			var max_height = box.height();

			var e_width = jQuery(e).width();
			var e_height = jQuery(e).height();
			var div = jQuery('.btn-layer-view-'+id);
			var div_width = div.width();
			var div_height = div.height();

			left = position.left + e_width + 15;
			var css = {};

			var temp = position.left + e_width + div_width + 15;
			top = position.top - (div_height / 2) + (e_height/2);
			if(temp < max_width && top > 0)
			{
				arrow = 'left';
				left = position.left + e_width + 15;
				css.top = (div_height / 2)-15;
				css.top = css.top + 'px';
			}
			else
			{
				var temp = position.left - e_width - div_width - 15;
				top = position.top - (div_height / 2) + (e_height/2);
				if(temp > 0 && top > 0)
				{
					arrow = 'right';
					left = position.left - div_width - 15;
					css.top = (div_height / 2)-15;
					css.top = css.top + 'px';
				}
				else
				{
					var temp = position.top + e_height + div_height + 15;
					left = position.left - (div_width/2) + (e_width/2);
					if(temp < max_height && left > 0)
					{
						arrow = 'top';
						top = position.top + e_height + 15;
						css.top = '-20px';
						css.left = (div_width/2) - 10;
						css.left = css.left + 'px';
					}
					else
					{
						left = position.left - (div_width/2) + (e_width/2);
						top = position.top - div_height - 15;
						if(left > 0 && top < max_height)
						{
							arrow = 'bottom';
						}
						else
						{
							arrow = 'left';
							left = position.left + e_width + 15;
							top = position.top;
						}
					}
				}
			}
			if((left + div_width) > max_width)
			{
				left =  (max_width - div_width)/2;
				if(left < 0) left = 0;
			}

			div.find('.layer-map-arrow').attr('class', 'layer-map-arrow map-arrow-'+arrow).css(css);
			div.css({
				'left': left+'px',
				'top': top+'px',
			});
			jQuery('.product-gallery-map').css('background-color', 'rgba(0, 0, 0, 0.4)');
			jQuery('.btn-layer-view-'+id).show('slow');
		},
		close: function(e){
			jQuery(e).parents('.btn-layer-view').hide();
			jQuery('.product-gallery-map').css('background-color', 'transparent');
		},
		view: function(data, index){
			var div = document.createElement('div');
			div.className = 'btn-layer-view btn-layer-view-'+index;
			var html = '<div class="layer-map-arrow"></div><div class="btn-layer-view-head">'+data.popup_title+' <span onclick="design_gallery.map.close(this);" class="close">&times;</span></div>'
			html = html + '<div class="btn-layer-view-content">';
			if(data.img != '')
			{
				html = html + '<div class="btn-layer-view-left"><img src="'+data.img+'" alt="'+data.popup_title+'"></div>';
				html = html + '<div class="btn-layer-view-right">'+data.popup_des+'</div>';
			}
			else
			{
				html = html + '<div class="btn-layer-view-full">'+data.popup_des+'</div>';
			}
			html = html + '</div>';
			if(typeof data.product_id != 'undefined' && data.product_id != '')
			{
				html = html + '<div class="btn-layer-view-footer">'
					+ 	'<a href="javascript:void(0);" data-id="'+data.product_id+'" onclick="design_gallery.viewDesign(this)" class="btn btn-default pull-left">View Design</a>'
					'</div>';
			}
			div.innerHTML = html;
			jQuery('.product-gallery-map').append(div);
		},
		style: function(data, index){
			var str = '.product-gallery-map .btn-layer-'+index+'{'
				 + 'left:'+data.btn_left+'px;'
				 + 'top:'+data.btn_top+'px;'
				 + 'color:#'+data.text_color+';'
				 + 'background-color:#'+data.btn_color+';'
				 + 'border:'+data.border_size+'px '+data.border_style+' #'+data.border_color+';'
				+ '}';
			str = str + '.product-gallery-map .btn-layer-'+index+' i{'
					+ 'font-size:'+data.icon_size+';'
					+ 'color:#'+data.icon_color+';'
					+ '}';
			str = str + '.product-gallery-map .btn-layer-'+index+':hover{'
					+ 'color:#'+data.text_hover_color+';'
					+ 'background-color:#'+data.btn_hover_color+';'
					+ '}';
			str = str + '.product-gallery-map .btn-layer-'+index+':hover i{'
					+ 'color:#'+data.icon_hover_color+';'
					+ '}';
			return str;
		}
	}
};

(function ( $ ) {
	$.fn.dg_tooltip = function() {
		var base = this;

		this.each(function() {
			$(this).mouseover(function(){
				var div = base.html(this);
				base.text(div, this);
			});

			$(this).mouseout(function(){
				base.hide(this);
			});
		});

		base.html = function(e){
			if($(e).parent().find('.dg_tooltip').length == 0){
				$(e).parent().append('<div class="dg_tooltip"><div class="dg_tooltip-content"></div></div>');
			}
			var div = $(e).parent().children('.dg_tooltip');

			return div;
		}

		base.text = function(div, e){
			var text = $(e).data('original-title');
			if(text == 'undefined') text = '';
			if(text == '') return;

			div.children('.dg_tooltip-content').html(text);
			base.show(div);
			base.css(e, div);
		}

		base.css = function(e, div){
			var position = $(e).position();
			var left = position.left;
			var top = position.top;

			var div_width = div.outerWidth();
			var div_height = div.outerHeight();
			var e_width = $(e).outerWidth();
			left = left - (div_width/2) + (e_width/2);
			top = parseInt(top) - div_height - 5;
			div.css({
				'left': left+'px',
				'top': top+'px',
			});
		}

		base.show = function(div){
			div.show();
		}

		base.hide = function(e){
			$(e).parent().children('.dg_tooltip').hide();
		}
	};
}( jQuery ));