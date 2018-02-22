var d_design = {
	init: function(){
		if(typeof product_gallery == 'undefined') return;
		var str = Base64.decode(product_gallery);
		if(str != '')
		{
			var data = jQuery.parseJSON(str);
			this.items(data);
		}
		else
		{
			jQuery('.store-ideas img').show();
		}
	},
	items: function(data){
		jQuery('.store-idea').each(function(){
			var type_id = jQuery(this).data('type');
			if(typeof type_id != 'undefined' && type_id != '' && type_id != 0 && typeof data[type_id] != 'undefined')
			{
				d_design.loadDesign(this, data[type_id]);
			}
			else
			{
				jQuery(this).find('img').show();
			}
		});
	},
	loadDesign: function(e, str){
		try {
			var data = eval ("(" + str + ")");
			var img = jQuery(e).find('img');
			var src = img.attr('src');
			jQuery.each(data, function(key, item){
				d_design.layers.init(e, key, src, item);
			});
		}
		catch(err) {
			jQuery(e).find('img').show();
		}
	},
	addCanvas: function(e, canvas){
		if(jQuery(e).hasClass('item-slideshow') == false)
		{
			jQuery(e).addClass('item-slideshow');
		}
		var a = jQuery(e).find('img').parent();
		a.prepend(canvas);
		var div = a.children();
		var n = div.length;
		jQuery(e).find('.item-carousel').remove();
		if(n > 1)
		{
			if(n == 2){
				var index = 0;
			}else{
				var index = this.slideActive(0, n-2);
			}
			jQuery(e).append('<a onclick="dgslide(this)" class="item-carousel item-control  item-control-left"><i class="fa fa-angle-left"></i></a>');
			jQuery(e).append('<a onclick="dgslide(this)" class="item-carousel item-control item-control-right"><i class="fa fa-angle-right"></i></a>');
			var html = '<ol class="item-carousel item-indicators">';
			for(var i=0; i<n; i++)
			{
				if(i == index)
					html = html + '<li onclick="dgslide(this)" data-slide="'+i+'" class="active"></li>';
				else
					html = html + '<li onclick="dgslide(this)" data-slide="'+i+'"></li>';
			}
			html = html + '</ol>';
			jQuery(e).append(html);
			div.removeClass('active');
			jQuery(div[index]).addClass('active');
		}
	},
	slideActive: function(min, max)
	{
	    return Math.floor(Math.random()*(max-min+1)+min);
	},
	layers: {
		init: function(e, key, src, item){
			if(typeof item.layers == 'undefined') return false;
			if(typeof item.hide != 'undefined' && item.hide == 1) return false;
			if(item.type != 'simple') return false;
			var image = new Image();
			image.onload = function(){
				var canvas = document.createElement("canvas");
				canvas.width = item.width;
				canvas.height = item.height;
				var ctx = canvas.getContext("2d");

				var obj = item.layers;
				obj.sort(function(obj1, obj2) {
					return obj1.zIndex - obj2.zIndex;
				});
				gallery_items(0, obj);

				function gallery_items(i, data){
					if(typeof data[i] == 'undefined')
					{
						d_design.addCanvas(e, canvas);
						return;
					}
					var layer = data[i];
					i++;

					if(typeof layer.style == 'undefined')
					{
						layer.style = {};
					}
					if(typeof layer.style.top == 'undefined')
					{
						layer.style.top = 0;
					}
					if(typeof layer.style.left == 'undefined')
					{
						layer.style.left = 0;
					}

					if(layer.type == 'img')
					{
						var img = new Image();
						img.onload = function(){
							if(typeof layer.style.width == 'undefined')
							{
								layer.style.width = img.width;
							}
							if(typeof layer.style.height == 'undefined')
							{
								layer.style.height = img.height;
							}
							ctx.drawImage(img, 0, 0, img.width, img.height, layer.style.left, layer.style.top, layer.style.width, layer.style.height);
							gallery_items(i, data);
						}
						img.src = layer.img;
					}
					else
					{
						ctx = d_design.layers.canvas(image, ctx, layer, e);
						gallery_items(i, data);
					}
				}
			};
			image.src = src;
		},
		canvas: function(image, ctx, layer, e){
			if(typeof layer.style.crop != 'undefined' && typeof layer.style.crop.old != 'undefined')
			{
				var canvas = d_design.layers.crop(image, layer.style);
			}
			else
			{
				var canvas = image;
			}

			if(typeof layer.style.is_bg != 'undefined' && layer.style.is_bg == 1)
			{
				var color = jQuery(e).data('color');
				var canvas = d_design.layers.addBackground(canvas, color);
			}
			if(typeof layer.style.warp != 'undefined')
			{
				var canvas = d_design.layers.warp(canvas, layer);
			}
			if(typeof layer.style.curve != 'undefined' && typeof layer.style.curve != 0)
			{
				var canvas = d_design.layers.curve(canvas, layer);
			}
			
			var new_h = (canvas.height * layer.style.width)/canvas.width;
			var new_top = (layer.style.height - new_h)/2;
			var new_canvas = document.createElement('canvas');
			new_canvas.width = layer.style.width;
			new_canvas.height = layer.style.height;
			var new_ctx = new_canvas.getContext('2d');
			if(typeof layer.style.is_bg != 'undefined' && layer.style.is_bg == 1)
			{
				new_ctx.fillStyle = color;
				new_ctx.fillRect(0, 0, new_canvas.width, new_canvas.height);
			}
			new_ctx.drawImage(canvas, 0, 0, canvas.width, canvas.height, 0, new_top, new_canvas.width, new_h);

			ctx.drawImage(new_canvas, 0, 0, new_canvas.width, new_canvas.height, layer.style.left, layer.style.top, layer.style.width, layer.style.height);

			return ctx;
		},
		crop: function(image, layer){
			var canvas = document.createElement('canvas');
				canvas.width = layer.crop.old.width;
				canvas.height = layer.crop.old.height;
			if(image.width > image.height)
			{
				var new_w = canvas.width;
				var new_h = (image.height * canvas.width)/image.width;
			}
			else
			{
				var new_h = canvas.height;
				var new_w = (image.width * canvas.height)/image.height;
			}
			if(new_w > canvas.width)
			{
				var new_h = (new_h * canvas.width)/new_w;
				var new_w = canvas.width;
			}
			if(new_h > canvas.height)
			{
				var new_w = (new_w * canvas.height)/new_h;
				var new_h = canvas.height;
			}
			var top = (canvas.height - new_h)/2;
			var left = (canvas.width - new_w)/2;
			var ctx = canvas.getContext('2d');
			ctx.drawImage(image, 0, 0, image.width, image.height, left, top, new_w, new_h);

			var canvas1 = document.createElement("canvas");
			canvas1.width = layer.crop.data.width;
			canvas1.height = layer.crop.data.height;
			var ctx1 = canvas1.getContext("2d");
			var max_left = layer.crop.data.left + canvas1.width;
			var max_top = layer.crop.data.top + canvas1.height;
			if(max_left > canvas.width)
			{
				layer.crop.data.left = (canvas.width - canvas1.width);
			}
			if(max_top > canvas.height)
			{
				layer.crop.data.top = (canvas.height - canvas1.height);
			}
			ctx1.drawImage(canvas, layer.crop.data.left, layer.crop.data.top, canvas1.width, canvas1.height, 0, 0, canvas1.width, canvas1.height);

			return canvas1;
		},
		addBackground: function(canvas, color){
			var canvas1 = document.createElement("canvas");
			canvas1.width = canvas.width;
			canvas1.height = canvas.height;
			var ctx1 = canvas1.getContext("2d");

			ctx1.fillStyle = color;
			ctx1.fillRect(0, 0, canvas1.width, canvas1.height);
			ctx1.drawImage(canvas, 0, 0, canvas1.width, canvas1.height);

			return canvas1;
		},
		warp: function(canvas, layer){
			var tempCanvas = document.createElement("canvas"),
 			tCtx = tempCanvas.getContext("2d");
 			tempCanvas.width = layer.style.warp_width;
 			tempCanvas.height = layer.style.warp_height;

 			var points = layer.style.warp;
 			var p = new Perspective(tCtx, canvas);
			p.draw(points);

			return tempCanvas;
		},
		curve: function(canvas, layer){
			var width = canvas.width;
			var height = canvas.height;

			var tempCanvas = document.createElement("canvas"),
 			tCtx = tempCanvas.getContext("2d");
 			tempCanvas.width = width;
 			var curve = layer.style.curve;

 			if(curve > 0)
 			{
 				var new_height = height + curve;
 			}
 			else
 			{
 				var move = curve * (-1);
 				var new_height = height + move;
 			}
 			var new_canvas = document.createElement('canvas');
			new_canvas.width = width;
			new_canvas.height = new_height;
			var new_ctx = new_canvas.getContext("2d");
			new_ctx.drawImage(canvas, 0, 0, width, height, 0, 0, width, height);
			var canvas = new_canvas;

 			tempCanvas.height = new_height;

 			var x1 = width / 2;
			var x2 = width;
			var y1 = curve;
			var y2 = 0;

			var eb = (y2*x1*x1 - y1*x2*x2) / (x2*x1*x1 - x1*x2*x2);
			var ea = (y1 - eb*x1) / (x1*x1);

			var currentYOffset;

			if(curve > 0)
			{
				for(var x = 0; x < width; x++) 
				{
				    currentYOffset = (ea * x * x) + eb * x;
				    tCtx.drawImage(canvas,x,0,1,height, x,currentYOffset,1,height);
				}
			}
			else
			{
				var n = curve * -1;
				for(var x = 0; x < width; x++) 
				{
				    currentYOffset = (ea * x * x) + eb * x;
				    currentYOffset = currentYOffset + n;
				    tCtx.drawImage(canvas,x,0,1,height, x,currentYOffset,1,height);
				}	
			}

			return tempCanvas;
		}
	}
};

var Base64 = {
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	encode: function(input){
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input = this._utf8_encode(input);

		while (i < input.length) {

			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);

			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;

			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}

			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

		}
		return output;
	},
	decode: function(input){
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;

		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

		while (i < input.length) {

			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));

			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;

			output = output + String.fromCharCode(chr1);

			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}

		}
		output = this._utf8_decode(output);
		return output;
	},
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	},
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;

		while ( i < utftext.length ) {

			c = utftext.charCodeAt(i);

			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}

		}
		return string;
	}
}
function dgslide(e){
	var e = jQuery(e);
	var child = e.parents('.item-slideshow').find('img').parent().children();
	var li = e.parents('.item-slideshow').find('.item-indicators').children();
	var elm = e.parents('.item-slideshow').find('.item-indicators').find('.active');
	var index_active = li.index(elm);
	child.removeClass('active');
	li.removeClass('active');
	if(e.hasClass('item-control-left'))
	{
		index_active = index_active - 1;
		if(index_active < 0) index_active = li.length - 1;
	}
	else if(e.hasClass('item-control-right'))
	{
		index_active = index_active + 1;
		if(index_active == li.length) index_active = 0;
	}
	else
	{
		var index_active = li.index(e);
	}
	jQuery(child[index_active]).addClass('active');
	jQuery(li[index_active]).addClass('active');
}
jQuery(document).ready(function() {
	d_design.init();
});
!function(a){if("object"==typeof exports&&"undefined"!=typeof module)module.exports=a();else if("function"==typeof define&&define.amd)define([],a);else{var b;b="undefined"!=typeof window?window:"undefined"!=typeof global?global:"undefined"!=typeof self?self:this,b.Perspective=a()}}(function(){return function a(b,c,d){function e(g,h){if(!c[g]){if(!b[g]){var i="function"==typeof require&&require;if(!h&&i)return i(g,!0);if(f)return f(g,!0);var j=new Error("Cannot find module '"+g+"'");throw j.code="MODULE_NOT_FOUND",j}var k=c[g]={exports:{}};b[g][0].call(k.exports,function(a){var c=b[g][1][a];return e(c?c:a)},k,k.exports,a,b,c,d)}return c[g].exports}for(var f="function"==typeof require&&require,g=0;g<d.length;g++)e(d[g]);return e}({1:[function(a,b,c){var d=window.html5jp||{};!function(){d.perspective=function(a,b){if(a&&a.strokeStyle&&b&&b.width&&b.height){var c=document.createElement("canvas");c.width=parseInt(b.width),c.height=parseInt(b.height);var d=c.getContext("2d");d.drawImage(b,0,0,c.width,c.height);var e=document.createElement("canvas");e.width=a.canvas.width,e.height=a.canvas.height;var f=e.getContext("2d");this.p={ctxd:a,cvso:c,ctxo:d,ctxt:f}}};var a=d.perspective.prototype;a.draw=function(a){for(var b=a[0][0],c=a[0][1],d=a[1][0],e=a[1][1],f=a[2][0],g=a[2][1],h=a[3][0],i=a[3][1],j=[Math.sqrt(Math.pow(b-d,2)+Math.pow(c-e,2)),Math.sqrt(Math.pow(d-f,2)+Math.pow(e-g,2)),Math.sqrt(Math.pow(f-h,2)+Math.pow(g-i,2)),Math.sqrt(Math.pow(h-b,2)+Math.pow(i-c,2))],k=this.p.cvso.width,l=this.p.cvso.height,m=0,n=0,o=0,p=0;4>p;p++){var q=0;q=p%2?j[p]/k:j[p]/l,q>n&&(m=p,n=q),0==j[p]&&o++}if(!(o>1)){var r=2,s=5*r,t=this.p.ctxo,u=this.p.ctxt;if(u.clearRect(0,0,u.canvas.width,u.canvas.height),m%2==0){var v=this.create_canvas_context(k,s);v.globalCompositeOperation="copy";for(var w=v.canvas,x=0;l>x;x+=r){var y=x/l,z=b+(h-b)*y,A=c+(i-c)*y,B=d+(f-d)*y,C=e+(g-e)*y,D=Math.atan((C-A)/(B-z)),E=Math.sqrt(Math.pow(B-z,2)+Math.pow(C-A,2))/k;v.setTransform(1,0,0,1,0,-x),v.drawImage(t.canvas,0,0),u.translate(z,A),u.rotate(D),u.scale(E,E),u.drawImage(w,0,0),u.setTransform(1,0,0,1,0,0)}}else if(m%2==1){var v=this.create_canvas_context(s,l);v.globalCompositeOperation="copy";for(var w=v.canvas,F=0;k>F;F+=r){var y=F/k,z=b+(d-b)*y,A=c+(e-c)*y,B=h+(f-h)*y,C=i+(g-i)*y,D=Math.atan((z-B)/(C-A)),E=Math.sqrt(Math.pow(B-z,2)+Math.pow(C-A,2))/l;v.setTransform(1,0,0,1,-F,0),v.drawImage(t.canvas,0,0),u.translate(z,A),u.rotate(D),u.scale(E,E),u.drawImage(w,0,0),u.setTransform(1,0,0,1,0,0)}}this.p.ctxd.save(),this.p.ctxd.drawImage(u.canvas,0,0),this._applyMask(this.p.ctxd,[[b,c],[d,e],[f,g],[h,i]]),this.p.ctxd.restore()}},a.create_canvas_context=function(a,b){var c=document.createElement("canvas");c.width=a,c.height=b;var d=c.getContext("2d");return d},a._applyMask=function(a,b){a.beginPath(),a.moveTo(b[0][0],b[0][1]);for(var c=1;c<b.length;c++)a.lineTo(b[c][0],b[c][1]);a.closePath(),a.globalCompositeOperation="destination-in",a.fill(),a.globalCompositeOperation="source-over"}}(),b.exports=d.perspective},{}]},{},[1])(1)});