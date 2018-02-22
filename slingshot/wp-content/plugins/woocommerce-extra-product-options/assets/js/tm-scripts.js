/*!
 * jQuery UI Touch Punch 0.2.3
 *
 * Copyright 2011–2014, Dave Furfero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Depends:
 *  jquery.ui.widget.js
 *  jquery.ui.mouse.js
 */
!function(a){function f(a,b){if(!(a.originalEvent.touches.length>1)){a.preventDefault();var c=a.originalEvent.changedTouches[0],d=document.createEvent("MouseEvents");d.initMouseEvent(b,!0,!0,window,1,c.screenX,c.screenY,c.clientX,c.clientY,!1,!1,!1,!1,0,null),a.target.dispatchEvent(d)}}if(a.support.touch="ontouchend"in document,a.support.touch){var e,b=a.ui.mouse.prototype,c=b._mouseInit,d=b._mouseDestroy;b._touchStart=function(a){var b=this;!e&&b._mouseCapture(a.originalEvent.changedTouches[0])&&(e=!0,b._touchMoved=!1,f(a,"mouseover"),f(a,"mousemove"),f(a,"mousedown"))},b._touchMove=function(a){e&&(this._touchMoved=!0,f(a,"mousemove"))},b._touchEnd=function(a){e&&(f(a,"mouseup"),f(a,"mouseout"),this._touchMoved||f(a,"click"),e=!1)},b._mouseInit=function(){var b=this;b.element.bind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),c.call(b)},b._mouseDestroy=function(){var b=this;b.element.unbind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),d.call(b)}}}(jQuery);

/*!
 * accounting.js v0.4.2, copyright 2014 Open Exchange Rates, MIT license, http://openexchangerates.github.io/accounting.js
 */
(function(p,z){function q(a){return!!(""===a||a&&a.charCodeAt&&a.substr)}function m(a){return u?u(a):"[object Array]"===v.call(a)}function r(a){return"[object Object]"===v.call(a)}function s(a,b){var d,a=a||{},b=b||{};for(d in b)b.hasOwnProperty(d)&&null==a[d]&&(a[d]=b[d]);return a}function j(a,b,d){var c=[],e,h;if(!a)return c;if(w&&a.map===w)return a.map(b,d);for(e=0,h=a.length;e<h;e++)c[e]=b.call(d,a[e],e,a);return c}function n(a,b){a=Math.round(Math.abs(a));return isNaN(a)?b:a}function x(a){var b=c.settings.currency.format;"function"===typeof a&&(a=a());return q(a)&&a.match("%v")?{pos:a,neg:a.replace("-","").replace("%v","-%v"),zero:a}:!a||!a.pos||!a.pos.match("%v")?!q(b)?b:c.settings.currency.format={pos:b,neg:b.replace("%v","-%v"),zero:b}:a}var c={version:"0.4.1",settings:{currency:{symbol:"$",format:"%s%v",decimal:".",thousand:",",precision:2,grouping:3},number:{precision:0,grouping:3,thousand:",",decimal:"."}}},w=Array.prototype.map,u=Array.isArray,v=Object.prototype.toString,o=c.unformat=c.parse=function(a,b){if(m(a))return j(a,function(a){return o(a,b)});a=a||0;if("number"===typeof a)return a;var b=b||".",c=RegExp("[^0-9-"+b+"]",["g"]),c=parseFloat((""+a).replace(/\((.*)\)/,"-$1").replace(c,"").replace(b,"."));return!isNaN(c)?c:0},y=c.toFixed=function(a,b){var b=n(b,c.settings.number.precision),d=Math.pow(10,b);return(Math.round(c.unformat(a)*d)/d).toFixed(b)},t=c.formatNumber=c.format=function(a,b,d,i){if(m(a))return j(a,function(a){return t(a,b,d,i)});var a=o(a),e=s(r(b)?b:{precision:b,thousand:d,decimal:i},c.settings.number),h=n(e.precision),f=0>a?"-":"",g=parseInt(y(Math.abs(a||0),h),10)+"",l=3<g.length?g.length%3:0;return f+(l?g.substr(0,l)+e.thousand:"")+g.substr(l).replace(/(\d{3})(?=\d)/g,"$1"+e.thousand)+(h?e.decimal+y(Math.abs(a),h).split(".")[1]:"")},A=c.formatMoney=function(a,b,d,i,e,h){if(m(a))return j(a,function(a){return A(a,b,d,i,e,h)});var a=o(a),f=s(r(b)?b:{symbol:b,precision:d,thousand:i,decimal:e,format:h},c.settings.currency),g=x(f.format);return(0<a?g.pos:0>a?g.neg:g.zero).replace("%s",f.symbol).replace("%v",t(Math.abs(a),n(f.precision),f.thousand,f.decimal))};c.formatColumn=function(a,b,d,i,e,h){if(!a)return[];var f=s(r(b)?b:{symbol:b,precision:d,thousand:i,decimal:e,format:h},c.settings.currency),g=x(f.format),l=g.pos.indexOf("%s")<g.pos.indexOf("%v")?!0:!1,k=0,a=j(a,function(a){if(m(a))return c.formatColumn(a,f);a=o(a);a=(0<a?g.pos:0>a?g.neg:g.zero).replace("%s",f.symbol).replace("%v",t(Math.abs(a),n(f.precision),f.thousand,f.decimal));if(a.length>k)k=a.length;return a});return j(a,function(a){return q(a)&&a.length<k?l?a.replace(f.symbol,f.symbol+Array(k-a.length+1).join(" ")):Array(k-a.length+1).join(" ")+a:a})};if("undefined"!==typeof exports){if("undefined"!==typeof module&&module.exports)exports=module.exports=c;exports.accounting=c}else"function"===typeof define&&define.amd?define([],function(){return c}):(c.noConflict=function(a){return function(){p.accounting=a;c.noConflict=z;return c}}(p.accounting),p.accounting=c)})(this);

/*! https://mths.be/startswith v0.2.0 by @mathias */
if ( ! String.prototype.startsWith ) {
    (function () {
        'use strict'; // needed to support `apply`/`call` with `undefined`/`null`
        var defineProperty = (function () {
            // IE 8 only supports `Object.defineProperty` on DOM elements
            try {
                var object = {};
                var $defineProperty = Object.defineProperty;
                var result = $defineProperty( object, object, object ) && $defineProperty;
            } catch ( error ) {
            }
            return result;
        }());
        var toString = {}.toString;
        var startsWith = function ( search ) {
            if ( this == null ) {
                throw TypeError();
            }
            var string = String( this );
            if ( search && toString.call( search ) == '[object RegExp]' ) {
                throw TypeError();
            }
            var stringLength = string.length;
            var searchString = String( search );
            var searchLength = searchString.length;
            var position = arguments.length > 1 ? arguments[ 1 ] : undefined;
            // `ToInteger`
            var pos = position ? Number( position ) : 0;
            if ( pos != pos ) { // better `isNaN`
                pos = 0;
            }
            var start = Math.min( Math.max( pos, 0 ), stringLength );
            // Avoid the `indexOf` call if no match is possible
            if ( searchLength + start > stringLength ) {
                return false;
            }
            var index = - 1;
            while ( ++ index < searchLength ) {
                if ( string.charCodeAt( start + index ) != searchString.charCodeAt( index ) ) {
                    return false;
                }
            }
            return true;
        };
        if ( defineProperty ) {
            defineProperty( String.prototype, 'startsWith', {
                'value': startsWith,
                'configurable': true,
                'writable': true
            } );
        } else {
            String.prototype.startsWith = startsWith;
        }
    }());
}
/*! https://mths.be/endswith v0.2.0 by @mathias */
if ( ! String.prototype.endsWith ) {
    (function () {
        'use strict'; // needed to support `apply`/`call` with `undefined`/`null`
        var defineProperty = (function () {
            // IE 8 only supports `Object.defineProperty` on DOM elements
            try {
                var object = {};
                var $defineProperty = Object.defineProperty;
                var result = $defineProperty( object, object, object ) && $defineProperty;
            } catch ( error ) {
            }
            return result;
        }());
        var toString = {}.toString;
        var endsWith = function ( search ) {
            if ( this == null ) {
                throw TypeError();
            }
            var string = String( this );
            if ( search && toString.call( search ) == '[object RegExp]' ) {
                throw TypeError();
            }
            var stringLength = string.length;
            var searchString = String( search );
            var searchLength = searchString.length;
            var pos = stringLength;
            if ( arguments.length > 1 ) {
                var position = arguments[ 1 ];
                if ( position !== undefined ) {
                    // `ToInteger`
                    pos = position ? Number( position ) : 0;
                    if ( pos != pos ) { // better `isNaN`
                        pos = 0;
                    }
                }
            }
            var end = Math.min( Math.max( pos, 0 ), stringLength );
            var start = end - searchLength;
            if ( start < 0 ) {
                return false;
            }
            var index = - 1;
            while ( ++ index < searchLength ) {
                if ( string.charCodeAt( start + index ) != searchString.charCodeAt( index ) ) {
                    return false;
                }
            }
            return true;
        };
        if ( defineProperty ) {
            defineProperty( String.prototype, 'endsWith', {
                'value': endsWith,
                'configurable': true,
                'writable': true
            } );
        } else {
            String.prototype.endsWith = endsWith;
        }
    }());
}
/*
 * http://paulirish.com/2011/requestanimationframe-for-smart-animating/
 * http://my.opera.com/emoller/blog/2011/12/20/requestanimationframe-for-smart-er-animating
 * requestAnimationFrame polyfill by Erik MΓ¶ller. fixes from Paul Irish and Tino Zijdel
 * MIT license
 */
(function() {
    "use strict";
    var lastTime = 0;var vendors = ['ms', 'moz', 'webkit', 'o'];for (var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {window.requestAnimationFrame = window[vendors[x] + 'RequestAnimationFrame'];window.cancelAnimationFrame = window[vendors[x] + 'CancelAnimationFrame'] || window[vendors[x] + 'CancelRequestAnimationFrame'];}if (!window.requestAnimationFrame)window.requestAnimationFrame = function(callback, element) {var currTime = new Date().getTime();var timeToCall = Math.max(0, 16 - (currTime - lastTime));var id = window.setTimeout(function() {callback(currTime + timeToCall);},timeToCall);lastTime = currTime + timeToCall;return id;};if (!window.cancelAnimationFrame)window.cancelAnimationFrame = function(id) {clearTimeout(id);};
}());

/*
 * jquery.resizestop (and resizestart)
 * by: Fatih Kadir Akın
 * https://github.com/f/jquery.resizestop
 * License is CC0, published to the public domain.
 */
 // Used by datapicker when position of top or bottom of the screen
(function(a){var b=Array.prototype.slice;a.extend(a.event.special,{resizestop:{add:function(d){var c=d.handler;a(this).resize(function(f){clearTimeout(c._timer);f.type="resizestop";var g=a.proxy(c,this,f);c._timer=setTimeout(g,d.data||200)})}},resizestart:{add:function(d){var c=d.handler;a(this).on("resize",function(f){clearTimeout(c._timer);if(!c._started){f.type="resizestart";c.apply(this,arguments);c._started=true}c._timer=setTimeout(a.proxy(function(){c._started=false},this),d.data||300)})}}});a.extend(a.fn,{resizestop:function(){a(this).on.apply(this,["resizestop"].concat(b.call(arguments)))},resizestart:function(){a(this).on.apply(this,["resizestart"].concat(b.call(arguments)))}})})(jQuery);

/*
 * JavaScript Cookie v2.1.0
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl
 * Released under the MIT license
 */
!function(a){if("function"==typeof define&&define.amd)define(a);else if("object"==typeof exports)module.exports=a();else{var b=window.Cookies,c=window.Cookies=a();c.noConflict=function(){return window.Cookies=b,c}}}(function(){function a(){for(var a=0,b={};a<arguments.length;a++){var c=arguments[a];for(var d in c)b[d]=c[d]}return b}function b(c){function d(b,e,f){var g;if(arguments.length>1){if(f=a({path:"/"},d.defaults,f),"number"==typeof f.expires){var h=new Date;h.setMilliseconds(h.getMilliseconds()+864e5*f.expires),f.expires=h}try{g=JSON.stringify(e),/^[\{\[]/.test(g)&&(e=g)}catch(i){}return e=c.write?c.write(e,b):encodeURIComponent(String(e)).replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g,decodeURIComponent),b=encodeURIComponent(String(b)),b=b.replace(/%(23|24|26|2B|5E|60|7C)/g,decodeURIComponent),b=b.replace(/[\(\)]/g,escape),document.cookie=[b,"=",e,f.expires&&"; expires="+f.expires.toUTCString(),f.path&&"; path="+f.path,f.domain&&"; domain="+f.domain,f.secure?"; secure":""].join("")}b||(g={});for(var j=document.cookie?document.cookie.split("; "):[],k=/(%[0-9A-Z]{2})+/g,l=0;l<j.length;l++){var m=j[l].split("="),n=m[0].replace(k,decodeURIComponent),o=m.slice(1).join("=");'"'===o.charAt(0)&&(o=o.slice(1,-1));try{if(o=c.read?c.read(o,n):c(o,n)||o.replace(k,decodeURIComponent),this.json)try{o=JSON.parse(o)}catch(i){}if(b===n){g=o;break}b||(g[n]=o)}catch(i){}}return g}return d.get=d.set=d,d.getJSON=function(){return d.apply({json:!0},[].slice.call(arguments))},d.defaults={},d.remove=function(b,c){d(b,"",a(c,{expires:-1}))},d.withConverter=b,d}return b(function(){})});

// jQuery Mask Plugin v1.14.0
// github.com/igorescobar/jQuery-Mask-Plugin
jQuery.jMaskGlobals={
    maskElements: '.tc-extra-product-options input'
};
(function(b){"function"===typeof define&&define.amd?define(["jquery"],b):"object"===typeof exports?module.exports=b(require("jquery")):b(jQuery||Zepto)})(function(b){var y=function(a,e,d){var c={invalid:[],getCaret:function(){try{var r,b=0,e=a.get(0),d=document.selection,f=e.selectionStart;if(d&&-1===navigator.appVersion.indexOf("MSIE 10"))r=d.createRange(),r.moveStart("character",-c.val().length),b=r.text.length;else if(f||"0"===f)b=f;return b}catch(g){}},setCaret:function(r){try{if(a.is(":focus")){var c,
b=a.get(0);b.setSelectionRange?(b.focus(),b.setSelectionRange(r,r)):(c=b.createTextRange(),c.collapse(!0),c.moveEnd("character",r),c.moveStart("character",r),c.select())}}catch(e){}},events:function(){a.on("keydown.mask",function(c){a.data("mask-keycode",c.keyCode||c.which)}).on(b.jMaskGlobals.useInput?"input.mask":"keyup.mask",c.behaviour).on("paste.mask drop.mask",function(){setTimeout(function(){a.keydown().keyup()},100)}).on("change.mask",function(){a.data("changed",!0)}).on("blur.mask",function(){n===
c.val()||a.data("changed")||a.trigger("change");a.data("changed",!1)}).on("blur.mask",function(){n=c.val()}).on("focus.mask",function(a){!0===d.selectOnFocus&&b(a.target).select()}).on("focusout.mask",function(){d.clearIfNotMatch&&!p.test(c.val())&&c.val("")})},getRegexMask:function(){for(var a=[],c,b,d,f,l=0;l<e.length;l++)(c=g.translation[e.charAt(l)])?(b=c.pattern.toString().replace(/.{1}$|^.{1}/g,""),d=c.optional,(c=c.recursive)?(a.push(e.charAt(l)),f={digit:e.charAt(l),pattern:b}):a.push(d||
c?b+"?":b)):a.push(e.charAt(l).replace(/[-\/\\^$*+?.()|[\]{}]/g,"\\$&"));a=a.join("");f&&(a=a.replace(new RegExp("("+f.digit+"(.*"+f.digit+")?)"),"($1)?").replace(new RegExp(f.digit,"g"),f.pattern));return new RegExp(a)},destroyEvents:function(){a.off("input keydown keyup paste drop blur focusout ".split(" ").join(".mask "))},val:function(c){var b=a.is("input")?"val":"text";if(0<arguments.length){if(a[b]()!==c)a[b](c);b=a}else b=a[b]();return b},getMCharsBeforeCount:function(a,c){for(var b=0,d=0,
f=e.length;d<f&&d<a;d++)g.translation[e.charAt(d)]||(a=c?a+1:a,b++);return b},caretPos:function(a,b,d,h){return g.translation[e.charAt(Math.min(a-1,e.length-1))]?Math.min(a+d-b-h,d):c.caretPos(a+1,b,d,h)},behaviour:function(d){d=d||window.event;c.invalid=[];var e=a.data("mask-keycode");if(-1===b.inArray(e,g.byPassKeys)){var m=c.getCaret(),h=c.val().length,f=c.getMasked(),l=f.length,k=c.getMCharsBeforeCount(l-1)-c.getMCharsBeforeCount(h-1),n=m<h;c.val(f);n&&(8!==e&&46!==e&&(m=c.caretPos(m,h,l,k)),
c.setCaret(m));return c.callbacks(d)}},getMasked:function(a,b){var m=[],h=void 0===b?c.val():b+"",f=0,l=e.length,k=0,n=h.length,q=1,p="push",u=-1,t,w;d.reverse?(p="unshift",q=-1,t=0,f=l-1,k=n-1,w=function(){return-1<f&&-1<k}):(t=l-1,w=function(){return f<l&&k<n});for(;w();){var x=e.charAt(f),v=h.charAt(k),s=g.translation[x];if(s)v.match(s.pattern)?(m[p](v),s.recursive&&(-1===u?u=f:f===t&&(f=u-q),t===u&&(f-=q)),f+=q):s.optional?(f+=q,k-=q):s.fallback?(m[p](s.fallback),f+=q,k-=q):c.invalid.push({p:k,
v:v,e:s.pattern}),k+=q;else{if(!a)m[p](x);v===x&&(k+=q);f+=q}}h=e.charAt(t);l!==n+1||g.translation[h]||m.push(h);return m.join("")},callbacks:function(b){var g=c.val(),m=g!==n,h=[g,b,a,d],f=function(a,b,c){"function"===typeof d[a]&&b&&d[a].apply(this,c)};f("onChange",!0===m,h);f("onKeyPress",!0===m,h);f("onComplete",g.length===e.length,h);f("onInvalid",0<c.invalid.length,[g,b,a,c.invalid,d])}};a=b(a);var g=this,n=c.val(),p;e="function"===typeof e?e(c.val(),void 0,a,d):e;g.mask=e;g.options=d;g.remove=
function(){var b=c.getCaret();c.destroyEvents();c.val(g.getCleanVal());c.setCaret(b-c.getMCharsBeforeCount(b));return a};g.getCleanVal=function(){return c.getMasked(!0)};g.getMaskedVal=function(a){return c.getMasked(!1,a)};g.init=function(e){e=e||!1;d=d||{};g.clearIfNotMatch=b.jMaskGlobals.clearIfNotMatch;g.byPassKeys=b.jMaskGlobals.byPassKeys;g.translation=b.extend({},b.jMaskGlobals.translation,d.translation);g=b.extend(!0,{},g,d);p=c.getRegexMask();!1===e?(d.placeholder&&a.attr("placeholder",d.placeholder),
a.data("mask")&&a.attr("autocomplete","off"),c.destroyEvents(),c.events(),e=c.getCaret(),c.val(c.getMasked()),c.setCaret(e+c.getMCharsBeforeCount(e,!0))):(c.events(),c.val(c.getMasked()))};g.init(!a.is("input"))};b.maskWatchers={};var A=function(){var a=b(this),e={},d=a.attr("data-mask");a.attr("data-mask-reverse")&&(e.reverse=!0);a.attr("data-mask-clearifnotmatch")&&(e.clearIfNotMatch=!0);"true"===a.attr("data-mask-selectonfocus")&&(e.selectOnFocus=!0);if(z(a,d,e))return a.data("mask",new y(this,
d,e))},z=function(a,e,d){d=d||{};var c=b(a).data("mask"),g=JSON.stringify;a=b(a).val()||b(a).text();try{return"function"===typeof e&&(e=e(a)),"object"!==typeof c||g(c.options)!==g(d)||c.mask!==e}catch(n){}};b.fn.mask=function(a,e){e=e||{};var d=this.selector,c=b.jMaskGlobals,g=c.watchInterval,c=e.watchInputs||c.watchInputs,n=function(){if(z(this,a,e))return b(this).data("mask",new y(this,a,e))};b(this).each(n);d&&""!==d&&c&&(clearInterval(b.maskWatchers[d]),b.maskWatchers[d]=setInterval(function(){b(document).find(d).each(n)},
g));return this};b.fn.masked=function(a){return this.data("mask").getMaskedVal(a)};b.fn.unmask=function(){clearInterval(b.maskWatchers[this.selector]);delete b.maskWatchers[this.selector];return this.each(function(){var a=b(this).data("mask");a&&a.remove().removeData("mask")})};b.fn.cleanVal=function(){return this.data("mask").getCleanVal()};b.applyDataMask=function(a){a=a||b.jMaskGlobals.maskElements;(a instanceof b?a:b(a)).filter(b.jMaskGlobals.dataMaskAttr).each(A)};var p={maskElements:"input,td,span,div",
dataMaskAttr:"*[data-mask]",dataMask:!0,watchInterval:300,watchInputs:!0,useInput:function(a){var b=document.createElement("div"),d;a="on"+a;d=a in b;d||(b.setAttribute(a,"return;"),d="function"===typeof b[a]);return d}("input"),watchDataMask:!1,byPassKeys:[9,16,17,18,36,37,38,39,40,91],translation:{0:{pattern:/\d/},9:{pattern:/\d/,optional:!0},"#":{pattern:/\d/,recursive:!0},A:{pattern:/[a-zA-Z0-9]/},S:{pattern:/[a-zA-Z]/}}};b.jMaskGlobals=b.jMaskGlobals||{};p=b.jMaskGlobals=b.extend(!0,{},p,b.jMaskGlobals);
p.dataMask&&b.applyDataMask();setInterval(function(){b.jMaskGlobals.watchDataMask&&b.applyDataMask()},p.watchInterval)});

/*! jQuery JSON plugin v2.5.1 | github.com/Krinkle/jquery-json */
!function($){"use strict";var escape=/["\\\x00-\x1f\x7f-\x9f]/g,meta={"\b":"\\b","  ":"\\t","\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"},hasOwn=Object.prototype.hasOwnProperty;$.toJSON="object"==typeof JSON&&JSON.stringify?JSON.stringify:function(a){if(null===a)return"null";var b,c,d,e,f=$.type(a);if("undefined"===f)return void 0;if("number"===f||"boolean"===f)return String(a);if("string"===f)return $.quoteString(a);if("function"==typeof a.toJSON)return $.toJSON(a.toJSON());if("date"===f){var g=a.getUTCMonth()+1,h=a.getUTCDate(),i=a.getUTCFullYear(),j=a.getUTCHours(),k=a.getUTCMinutes(),l=a.getUTCSeconds(),m=a.getUTCMilliseconds();return 10>g&&(g="0"+g),10>h&&(h="0"+h),10>j&&(j="0"+j),10>k&&(k="0"+k),10>l&&(l="0"+l),100>m&&(m="0"+m),10>m&&(m="0"+m),'"'+i+"-"+g+"-"+h+"T"+j+":"+k+":"+l+"."+m+'Z"'}if(b=[],$.isArray(a)){for(c=0;c<a.length;c++)b.push($.toJSON(a[c])||"null");return"["+b.join(",")+"]"}if("object"==typeof a){for(c in a)if(hasOwn.call(a,c)){if(f=typeof c,"number"===f)d='"'+c+'"';else{if("string"!==f)continue;d=$.quoteString(c)}f=typeof a[c],"function"!==f&&"undefined"!==f&&(e=$.toJSON(a[c]),b.push(d+":"+e))}return"{"+b.join(",")+"}"}},$.evalJSON="object"==typeof JSON&&JSON.parse?JSON.parse:function(str){return eval("("+str+")")},$.secureEvalJSON="object"==typeof JSON&&JSON.parse?JSON.parse:function(str){var filtered=str.replace(/\\["\\\/bfnrtu]/g,"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]").replace(/(?:^|:|,)(?:\s*\[)+/g,"");if(/^[\],:{}\s]*$/.test(filtered))return eval("("+str+")");throw new SyntaxError("Error parsing JSON, source is not valid.")},$.quoteString=function(a){return a.match(escape)?'"'+a.replace(escape,function(a){var b=meta[a];return"string"==typeof b?b:(b=a.charCodeAt(),"\\u00"+Math.floor(b/16).toString(16)+(b%16).toString(16))})+'"':'"'+a+'"'}}(jQuery);

/* Lazy Load XT 1.1.0 | MIT License */
!function(a,b,c,d){function e(a,b){return a[b]===d?t[b]:a[b]}function f(){var a=b.pageYOffset;return a===d?r.scrollTop:a}function g(a,b){var c=t["on"+a];c&&(w(c)?c.call(b[0]):(c.addClass&&b.addClass(c.addClass),c.removeClass&&b.removeClass(c.removeClass))),b.trigger("lazy"+a,[b]),k()}function h(b){g(b.type,a(this).off(p,h))}function i(c){if(z.length){c=c||t.forceLoad,A=1/0;var d,e,i=f(),j=b.innerHeight||r.clientHeight,k=b.innerWidth||r.clientWidth;for(d=0,e=z.length;e>d;d++){var l,m=z[d],q=m[0],s=m[n],u=!1,v=c||y(q,o)<0;if(a.contains(r,q)){if(c||!s.visibleOnly||q.offsetWidth||q.offsetHeight){if(!v){var x=q.getBoundingClientRect(),B=s.edgeX,C=s.edgeY;l=x.top+i-C-j,v=i>=l&&x.bottom>-C&&x.left<=k+B&&x.right>-B}if(v){m.on(p,h),g("show",m);var D=s.srcAttr,E=w(D)?D(m):q.getAttribute(D);E&&(q.src=E),u=!0}else A>l&&(A=l)}}else u=!0;u&&(y(q,o,0),z.splice(d--,1),e--)}e||g("complete",a(r))}}function j(){B>1?(B=1,i(),setTimeout(j,t.throttle)):B=0}function k(a){z.length&&(a&&"scroll"===a.type&&a.currentTarget===b&&A>=f()||(B||setTimeout(j,0),B=2))}function l(){v.lazyLoadXT()}function m(){i(!0)}var n="lazyLoadXT",o="lazied",p="load error",q="lazy-hidden",r=c.documentElement||c.body,s=b.onscroll===d||!!b.operamini||!r.getBoundingClientRect,t={autoInit:!0,selector:"img[data-src]",blankImage:"data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7",throttle:99,forceLoad:s,loadEvent:"pageshow",updateEvent:"load orientationchange resize scroll touchmove focus",forceEvent:"lazyloadall",oninit:{removeClass:"lazy"},onshow:{addClass:q},onload:{removeClass:q,addClass:"lazy-loaded"},onerror:{removeClass:q},checkDuplicates:!0},u={srcAttr:"data-src",edgeX:0,edgeY:0,visibleOnly:!0},v=a(b),w=a.isFunction,x=a.extend,y=a.data||function(b,c){return a(b).data(c)},z=[],A=0,B=0;a[n]=x(t,u,a[n]),a.fn[n]=function(c){c=c||{};var d,f=e(c,"blankImage"),h=e(c,"checkDuplicates"),i=e(c,"scrollContainer"),j=e(c,"show"),l={};a(i).on("scroll",k);for(d in u)l[d]=e(c,d);return this.each(function(d,e){if(e===b)a(t.selector).lazyLoadXT(c);else{var i=h&&y(e,o),m=a(e).data(o,j?-1:1);if(i)return void k();f&&"IMG"===e.tagName&&!e.src&&(e.src=f),m[n]=x({},l),g("init",m),z.push(m),k()}})},a(c).ready(function(){g("start",v),v.on(t.updateEvent,k).on(t.forceEvent,m),a(c).on(t.updateEvent,k),t.autoInit&&(v.on(t.loadEvent,l),l())})}(window.jQuery||window.Zepto||window.$,window,document),function(a){var b=a.lazyLoadXT;b.selector+=",video,iframe[data-src]",b.videoPoster="data-poster",a(document).on("lazyshow","video",function(c,d){var e=d.lazyLoadXT.srcAttr,f=a.isFunction(e),g=!1;d.attr("poster",d.attr(b.videoPoster)),d.children("source,track").each(function(b,c){var d=a(c),h=f?e(d):d.attr(e);h&&(d.attr("src",h),g=!0)}),g&&this.load()})}(window.jQuery||window.Zepto||window.$);
(function($) {
	$.extend($.lazyLoadXT, {
		autoInit:  false,
		//selector: 'img.tmlazy',
		//srcAttr: 'data-original',
		//visibleOnly:false,
		updateEvent:$.lazyLoadXT.updateEvent + ' tmlazy'
	});
})(jQuery);

/*
 *  Project: nouislider (http://refreshless.com/nouislider/)
 *  Description: noUiSlider is a range slider without bloat
 *  License: http://www.wtfpl.net/about/
 */
/* wNumb.js */
(function(){'use strict';var FormatOptions=['decimals','thousand','mark','prefix','postfix','encoder','decoder','negativeBefore','negative','edit','undo'];function strReverse(a){return a.split('').reverse().join('')}function strStartsWith(input,match){return input.substring(0,match.length)===match}function strEndsWith(input,match){return input.slice(-1*match.length)===match}function throwEqualError(F,a,b){if((F[a]||F[b])&&(F[a]===F[b])){throw new Error(a);}}function isValidNumber(input){return typeof input==='number'&&isFinite(input)}function toFixed(value,decimals){var scale=Math.pow(10,decimals);return(Math.round(value*scale)/scale).toFixed(decimals)}function formatTo(decimals,thousand,mark,prefix,postfix,encoder,decoder,negativeBefore,negative,edit,undo,input){var originalInput=input,inputIsNegative,inputPieces,inputBase,inputDecimals='',output='';if(encoder){input=encoder(input)}if(!isValidNumber(input)){return false}if(decimals!==false&&parseFloat(input.toFixed(decimals))===0){input=0}if(input<0){inputIsNegative=true;input=Math.abs(input)}if(decimals!==false){input=toFixed(input,decimals)}input=input.toString();if(input.indexOf('.')!==-1){inputPieces=input.split('.');inputBase=inputPieces[0];if(mark){inputDecimals=mark+inputPieces[1]}}else{inputBase=input}if(thousand){inputBase=strReverse(inputBase).match(/.{1,3}/g);inputBase=strReverse(inputBase.join(strReverse(thousand)))}if(inputIsNegative&&negativeBefore){output+=negativeBefore}if(prefix){output+=prefix}if(inputIsNegative&&negative){output+=negative}output+=inputBase;output+=inputDecimals;if(postfix){output+=postfix}if(edit){output=edit(output,originalInput)}return output}function formatFrom(decimals,thousand,mark,prefix,postfix,encoder,decoder,negativeBefore,negative,edit,undo,input){var originalInput=input,inputIsNegative,output='';if(undo){input=undo(input)}if(!input||typeof input!=='string'){return false}if(negativeBefore&&strStartsWith(input,negativeBefore)){input=input.replace(negativeBefore,'');inputIsNegative=true}if(prefix&&strStartsWith(input,prefix)){input=input.replace(prefix,'')}if(negative&&strStartsWith(input,negative)){input=input.replace(negative,'');inputIsNegative=true}if(postfix&&strEndsWith(input,postfix)){input=input.slice(0,-1*postfix.length)}if(thousand){input=input.split(thousand).join('')}if(mark){input=input.replace(mark,'.')}if(inputIsNegative){output+='-'}output+=input;output=output.replace(/[^0-9\.\-.]/g,'');if(output===''){return false}output=Number(output);if(decoder){output=decoder(output)}if(!isValidNumber(output)){return false}return output}function validate(inputOptions){var i,optionName,optionValue,filteredOptions={};for(i=0;i<FormatOptions.length;i+=1){optionName=FormatOptions[i];optionValue=inputOptions[optionName];if(optionValue===undefined){if(optionName==='negative'&&!filteredOptions.negativeBefore){filteredOptions[optionName]='-'}else if(optionName==='mark'&&filteredOptions.thousand!=='.'){filteredOptions[optionName]='.'}else{filteredOptions[optionName]=false}}else if(optionName==='decimals'){if(optionValue>=0&&optionValue<8){filteredOptions[optionName]=optionValue}else{throw new Error(optionName);}}else if(optionName==='encoder'||optionName==='decoder'||optionName==='edit'||optionName==='undo'){if(typeof optionValue==='function'){filteredOptions[optionName]=optionValue}else{throw new Error(optionName);}}else{if(typeof optionValue==='string'){filteredOptions[optionName]=optionValue}else{throw new Error(optionName);}}}throwEqualError(filteredOptions,'mark','thousand');throwEqualError(filteredOptions,'prefix','negative');throwEqualError(filteredOptions,'prefix','negativeBefore');return filteredOptions}function passAll(options,method,input){var i,args=[];for(i=0;i<FormatOptions.length;i+=1){args.push(options[FormatOptions[i]])}args.push(input);return method.apply('',args)}function wNumb(options){if(!(this instanceof wNumb)){return new wNumb(options)}if(typeof options!=="object"){return}options=validate(options);this.to=function(input){return passAll(options,formatTo,input)};this.from=function(input){return passAll(options,formatFrom,input)}}window.wNumb=wNumb}());
/*! nouislider - 8.5.1 - 2016-04-24 16:00:29 */
!function(a){"function"==typeof define&&define.amd?define([],a):"object"==typeof exports?module.exports=a():window.noUiSlider=a()}(function(){"use strict";function a(a){return a.filter(function(a){return this[a]?!1:this[a]=!0},{})}function b(a,b){return Math.round(a/b)*b}function c(a){var b=a.getBoundingClientRect(),c=a.ownerDocument,d=c.documentElement,e=l();return/webkit.*Chrome.*Mobile/i.test(navigator.userAgent)&&(e.x=0),{top:b.top+e.y-d.clientTop,left:b.left+e.x-d.clientLeft}}function d(a){return"number"==typeof a&&!isNaN(a)&&isFinite(a)}function e(a,b,c){i(a,b),setTimeout(function(){j(a,b)},c)}function f(a){return Math.max(Math.min(a,100),0)}function g(a){return Array.isArray(a)?a:[a]}function h(a){var b=a.split(".");return b.length>1?b[1].length:0}function i(a,b){a.classList?a.classList.add(b):a.className+=" "+b}function j(a,b){a.classList?a.classList.remove(b):a.className=a.className.replace(new RegExp("(^|\\b)"+b.split(" ").join("|")+"(\\b|$)","gi")," ")}function k(a,b){return a.classList?a.classList.contains(b):new RegExp("\\b"+b+"\\b").test(a.className)}function l(){var a=void 0!==window.pageXOffset,b="CSS1Compat"===(document.compatMode||""),c=a?window.pageXOffset:b?document.documentElement.scrollLeft:document.body.scrollLeft,d=a?window.pageYOffset:b?document.documentElement.scrollTop:document.body.scrollTop;return{x:c,y:d}}function m(){return window.navigator.pointerEnabled?{start:"pointerdown",move:"pointermove",end:"pointerup"}:window.navigator.msPointerEnabled?{start:"MSPointerDown",move:"MSPointerMove",end:"MSPointerUp"}:{start:"mousedown touchstart",move:"mousemove touchmove",end:"mouseup touchend"}}function n(a,b){return 100/(b-a)}function o(a,b){return 100*b/(a[1]-a[0])}function p(a,b){return o(a,a[0]<0?b+Math.abs(a[0]):b-a[0])}function q(a,b){return b*(a[1]-a[0])/100+a[0]}function r(a,b){for(var c=1;a>=b[c];)c+=1;return c}function s(a,b,c){if(c>=a.slice(-1)[0])return 100;var d,e,f,g,h=r(c,a);return d=a[h-1],e=a[h],f=b[h-1],g=b[h],f+p([d,e],c)/n(f,g)}function t(a,b,c){if(c>=100)return a.slice(-1)[0];var d,e,f,g,h=r(c,b);return d=a[h-1],e=a[h],f=b[h-1],g=b[h],q([d,e],(c-f)*n(f,g))}function u(a,c,d,e){if(100===e)return e;var f,g,h=r(e,a);return d?(f=a[h-1],g=a[h],e-f>(g-f)/2?g:f):c[h-1]?a[h-1]+b(e-a[h-1],c[h-1]):e}function v(a,b,c){var e;if("number"==typeof b&&(b=[b]),"[object Array]"!==Object.prototype.toString.call(b))throw new Error("noUiSlider: 'range' contains invalid value.");if(e="min"===a?0:"max"===a?100:parseFloat(a),!d(e)||!d(b[0]))throw new Error("noUiSlider: 'range' value isn't numeric.");c.xPct.push(e),c.xVal.push(b[0]),e?c.xSteps.push(isNaN(b[1])?!1:b[1]):isNaN(b[1])||(c.xSteps[0]=b[1])}function w(a,b,c){return b?void(c.xSteps[a]=o([c.xVal[a],c.xVal[a+1]],b)/n(c.xPct[a],c.xPct[a+1])):!0}function x(a,b,c,d){this.xPct=[],this.xVal=[],this.xSteps=[d||!1],this.xNumSteps=[!1],this.snap=b,this.direction=c;var e,f=[];for(e in a)a.hasOwnProperty(e)&&f.push([a[e],e]);for(f.length&&"object"==typeof f[0][0]?f.sort(function(a,b){return a[0][0]-b[0][0]}):f.sort(function(a,b){return a[0]-b[0]}),e=0;e<f.length;e++)v(f[e][1],f[e][0],this);for(this.xNumSteps=this.xSteps.slice(0),e=0;e<this.xNumSteps.length;e++)w(e,this.xNumSteps[e],this)}function y(a,b){if(!d(b))throw new Error("noUiSlider: 'step' is not numeric.");a.singleStep=b}function z(a,b){if("object"!=typeof b||Array.isArray(b))throw new Error("noUiSlider: 'range' is not an object.");if(void 0===b.min||void 0===b.max)throw new Error("noUiSlider: Missing 'min' or 'max' in 'range'.");if(b.min===b.max)throw new Error("noUiSlider: 'range' 'min' and 'max' cannot be equal.");a.spectrum=new x(b,a.snap,a.dir,a.singleStep)}function A(a,b){if(b=g(b),!Array.isArray(b)||!b.length||b.length>2)throw new Error("noUiSlider: 'start' option is incorrect.");a.handles=b.length,a.start=b}function B(a,b){if(a.snap=b,"boolean"!=typeof b)throw new Error("noUiSlider: 'snap' option must be a boolean.")}function C(a,b){if(a.animate=b,"boolean"!=typeof b)throw new Error("noUiSlider: 'animate' option must be a boolean.")}function D(a,b){if(a.animationDuration=b,"number"!=typeof b)throw new Error("noUiSlider: 'animationDuration' option must be a number.")}function E(a,b){if("lower"===b&&1===a.handles)a.connect=1;else if("upper"===b&&1===a.handles)a.connect=2;else if(b===!0&&2===a.handles)a.connect=3;else{if(b!==!1)throw new Error("noUiSlider: 'connect' option doesn't match handle count.");a.connect=0}}function F(a,b){switch(b){case"horizontal":a.ort=0;break;case"vertical":a.ort=1;break;default:throw new Error("noUiSlider: 'orientation' option is invalid.")}}function G(a,b){if(!d(b))throw new Error("noUiSlider: 'margin' option must be numeric.");if(0!==b&&(a.margin=a.spectrum.getMargin(b),!a.margin))throw new Error("noUiSlider: 'margin' option is only supported on linear sliders.")}function H(a,b){if(!d(b))throw new Error("noUiSlider: 'limit' option must be numeric.");if(a.limit=a.spectrum.getMargin(b),!a.limit)throw new Error("noUiSlider: 'limit' option is only supported on linear sliders.")}function I(a,b){switch(b){case"ltr":a.dir=0;break;case"rtl":a.dir=1,a.connect=[0,2,1,3][a.connect];break;default:throw new Error("noUiSlider: 'direction' option was not recognized.")}}function J(a,b){if("string"!=typeof b)throw new Error("noUiSlider: 'behaviour' must be a string containing options.");var c=b.indexOf("tap")>=0,d=b.indexOf("drag")>=0,e=b.indexOf("fixed")>=0,f=b.indexOf("snap")>=0,g=b.indexOf("hover")>=0;if(d&&!a.connect)throw new Error("noUiSlider: 'drag' behaviour must be used with 'connect': true.");a.events={tap:c||f,drag:d,fixed:e,snap:f,hover:g}}function K(a,b){var c;if(b!==!1)if(b===!0)for(a.tooltips=[],c=0;c<a.handles;c++)a.tooltips.push(!0);else{if(a.tooltips=g(b),a.tooltips.length!==a.handles)throw new Error("noUiSlider: must pass a formatter for all handles.");a.tooltips.forEach(function(a){if("boolean"!=typeof a&&("object"!=typeof a||"function"!=typeof a.to))throw new Error("noUiSlider: 'tooltips' must be passed a formatter or 'false'.")})}}function L(a,b){if(a.format=b,"function"==typeof b.to&&"function"==typeof b.from)return!0;throw new Error("noUiSlider: 'format' requires 'to' and 'from' methods.")}function M(a,b){if(void 0!==b&&"string"!=typeof b&&b!==!1)throw new Error("noUiSlider: 'cssPrefix' must be a string or `false`.");a.cssPrefix=b}function N(a,b){if(void 0!==b&&"object"!=typeof b)throw new Error("noUiSlider: 'cssClasses' must be an object.");if("string"==typeof a.cssPrefix){a.cssClasses={};for(var c in b)b.hasOwnProperty(c)&&(a.cssClasses[c]=a.cssPrefix+b[c])}else a.cssClasses=b}function O(a){var b,c={margin:0,limit:0,animate:!0,animationDuration:300,format:R};b={step:{r:!1,t:y},start:{r:!0,t:A},connect:{r:!0,t:E},direction:{r:!0,t:I},snap:{r:!1,t:B},animate:{r:!1,t:C},animationDuration:{r:!1,t:D},range:{r:!0,t:z},orientation:{r:!1,t:F},margin:{r:!1,t:G},limit:{r:!1,t:H},behaviour:{r:!0,t:J},format:{r:!1,t:L},tooltips:{r:!1,t:K},cssPrefix:{r:!1,t:M},cssClasses:{r:!1,t:N}};var d={connect:!1,direction:"ltr",behaviour:"tap",orientation:"horizontal",cssPrefix:"noUi-",cssClasses:{target:"target",base:"base",origin:"origin",handle:"handle",handleLower:"handle-lower",handleUpper:"handle-upper",horizontal:"horizontal",vertical:"vertical",background:"background",connect:"connect",ltr:"ltr",rtl:"rtl",draggable:"draggable",drag:"state-drag",tap:"state-tap",active:"active",stacking:"stacking",tooltip:"tooltip",pips:"pips",pipsHorizontal:"pips-horizontal",pipsVertical:"pips-vertical",marker:"marker",markerHorizontal:"marker-horizontal",markerVertical:"marker-vertical",markerNormal:"marker-normal",markerLarge:"marker-large",markerSub:"marker-sub",value:"value",valueHorizontal:"value-horizontal",valueVertical:"value-vertical",valueNormal:"value-normal",valueLarge:"value-large",valueSub:"value-sub"}};return Object.keys(b).forEach(function(e){if(void 0===a[e]&&void 0===d[e]){if(b[e].r)throw new Error("noUiSlider: '"+e+"' is required.");return!0}b[e].t(c,void 0===a[e]?d[e]:a[e])}),c.pips=a.pips,c.style=c.ort?"top":"left",c}function P(b,d,n){function o(a,b,c){var d=a+b[0],e=a+b[1];return c?(0>d&&(e+=Math.abs(d)),e>100&&(d-=e-100),[f(d),f(e)]):[d,e]}function p(a,b){a.preventDefault();var c,d,e=0===a.type.indexOf("touch"),f=0===a.type.indexOf("mouse"),g=0===a.type.indexOf("pointer"),h=a;return 0===a.type.indexOf("MSPointer")&&(g=!0),e&&(c=a.changedTouches[0].pageX,d=a.changedTouches[0].pageY),b=b||l(),(f||g)&&(c=a.clientX+b.x,d=a.clientY+b.y),h.pageOffset=b,h.points=[c,d],h.cursor=f||g,h}function q(a,b){var c=document.createElement("div"),e=document.createElement("div"),f=[d.cssClasses.handleLower,d.cssClasses.handleUpper];return a&&f.reverse(),i(e,d.cssClasses.handle),i(e,f[b]),i(c,d.cssClasses.origin),c.appendChild(e),c}function r(a,b,c){switch(a){case 1:i(b,d.cssClasses.connect),i(c[0],d.cssClasses.background);break;case 3:i(c[1],d.cssClasses.background);case 2:i(c[0],d.cssClasses.connect);case 0:i(b,d.cssClasses.background)}}function s(a,b,c){var d,e=[];for(d=0;a>d;d+=1)e.push(c.appendChild(q(b,d)));return e}function t(a,b,c){i(c,d.cssClasses.target),0===a?i(c,d.cssClasses.ltr):i(c,d.cssClasses.rtl),0===b?i(c,d.cssClasses.horizontal):i(c,d.cssClasses.vertical);var e=document.createElement("div");return i(e,d.cssClasses.base),c.appendChild(e),e}function u(a,b){if(!d.tooltips[b])return!1;var c=document.createElement("div");return c.className=d.cssClasses.tooltip,a.firstChild.appendChild(c)}function v(){d.dir&&d.tooltips.reverse();var a=W.map(u);d.dir&&(a.reverse(),d.tooltips.reverse()),S("update",function(b,c,e){a[c]&&(a[c].innerHTML=d.tooltips[c]===!0?b[c]:d.tooltips[c].to(e[c]))})}function w(a,b,c){if("range"===a||"steps"===a)return _.xVal;if("count"===a){var d,e=100/(b-1),f=0;for(b=[];(d=f++*e)<=100;)b.push(d);a="positions"}return"positions"===a?b.map(function(a){return _.fromStepping(c?_.getStep(a):a)}):"values"===a?c?b.map(function(a){return _.fromStepping(_.getStep(_.toStepping(a)))}):b:void 0}function x(b,c,d){function e(a,b){return(a+b).toFixed(7)/1}var f=_.direction,g={},h=_.xVal[0],i=_.xVal[_.xVal.length-1],j=!1,k=!1,l=0;return _.direction=0,d=a(d.slice().sort(function(a,b){return a-b})),d[0]!==h&&(d.unshift(h),j=!0),d[d.length-1]!==i&&(d.push(i),k=!0),d.forEach(function(a,f){var h,i,m,n,o,p,q,r,s,t,u=a,v=d[f+1];if("steps"===c&&(h=_.xNumSteps[f]),h||(h=v-u),u!==!1&&void 0!==v)for(i=u;v>=i;i=e(i,h)){for(n=_.toStepping(i),o=n-l,r=o/b,s=Math.round(r),t=o/s,m=1;s>=m;m+=1)p=l+m*t,g[p.toFixed(5)]=["x",0];q=d.indexOf(i)>-1?1:"steps"===c?2:0,!f&&j&&(q=0),i===v&&k||(g[n.toFixed(5)]=[i,q]),l=n}}),_.direction=f,g}function y(a,b,c){function e(a,b){var c=b===d.cssClasses.value,e=c?m:n,f=c?k:l;return b+" "+e[d.ort]+" "+f[a]}function f(a,b,c){return'class="'+e(c[1],b)+'" style="'+d.style+": "+a+'%"'}function g(a,e){_.direction&&(a=100-a),e[1]=e[1]&&b?b(e[0],e[1]):e[1],j+="<div "+f(a,d.cssClasses.marker,e)+"></div>",e[1]&&(j+="<div "+f(a,d.cssClasses.value,e)+">"+c.to(e[0])+"</div>")}var h=document.createElement("div"),j="",k=[d.cssClasses.valueNormal,d.cssClasses.valueLarge,d.cssClasses.valueSub],l=[d.cssClasses.markerNormal,d.cssClasses.markerLarge,d.cssClasses.markerSub],m=[d.cssClasses.valueHorizontal,d.cssClasses.valueVertical],n=[d.cssClasses.markerHorizontal,d.cssClasses.markerVertical];return i(h,d.cssClasses.pips),i(h,0===d.ort?d.cssClasses.pipsHorizontal:d.cssClasses.pipsVertical),Object.keys(a).forEach(function(b){g(b,a[b])}),h.innerHTML=j,h}function z(a){var b=a.mode,c=a.density||1,d=a.filter||!1,e=a.values||!1,f=a.stepped||!1,g=w(b,e,f),h=x(c,b,g),i=a.format||{to:Math.round};return Z.appendChild(y(h,d,i))}function A(){var a=V.getBoundingClientRect(),b="offset"+["Width","Height"][d.ort];return 0===d.ort?a.width||V[b]:a.height||V[b]}function B(a,b,c){var e;for(e=0;e<d.handles;e++)if(-1===$[e])return;void 0!==b&&1!==d.handles&&(b=Math.abs(b-d.dir)),Object.keys(ba).forEach(function(d){var e=d.split(".")[0];a===e&&ba[d].forEach(function(a){a.call(X,g(P()),b,g(C(Array.prototype.slice.call(aa))),c||!1,$)})})}function C(a){return 1===a.length?a[0]:d.dir?a.reverse():a}function D(a,b,c,e){var f=function(b){return Z.hasAttribute("disabled")?!1:k(Z,d.cssClasses.tap)?!1:(b=p(b,e.pageOffset),a===Y.start&&void 0!==b.buttons&&b.buttons>1?!1:e.hover&&b.buttons?!1:(b.calcPoint=b.points[d.ort],void c(b,e)))},g=[];return a.split(" ").forEach(function(a){b.addEventListener(a,f,!1),g.push([a,f])}),g}function E(a,b){if(-1===navigator.appVersion.indexOf("MSIE 9")&&0===a.buttons&&0!==b.buttonsProperty)return F(a,b);var c,d,e=b.handles||W,f=!1,g=100*(a.calcPoint-b.start)/b.baseSize,h=e[0]===W[0]?0:1;if(c=o(g,b.positions,e.length>1),f=L(e[0],c[h],1===e.length),e.length>1){if(f=L(e[1],c[h?0:1],!1)||f)for(d=0;d<b.handles.length;d++)B("slide",d)}else f&&B("slide",h)}function F(a,b){var c=V.querySelector("."+d.cssClasses.active),e=b.handles[0]===W[0]?0:1;null!==c&&j(c,d.cssClasses.active),a.cursor&&(document.body.style.cursor="",document.body.removeEventListener("selectstart",document.body.noUiListener));var f=document.documentElement;f.noUiListeners.forEach(function(a){f.removeEventListener(a[0],a[1])}),j(Z,d.cssClasses.drag),B("set",e),B("change",e),void 0!==b.handleNumber&&B("end",b.handleNumber)}function G(a,b){"mouseout"===a.type&&"HTML"===a.target.nodeName&&null===a.relatedTarget&&F(a,b)}function H(a,b){var c=document.documentElement;if(1===b.handles.length){if(b.handles[0].hasAttribute("disabled"))return!1;i(b.handles[0].children[0],d.cssClasses.active)}a.preventDefault(),a.stopPropagation();var e=D(Y.move,c,E,{start:a.calcPoint,baseSize:A(),pageOffset:a.pageOffset,handles:b.handles,handleNumber:b.handleNumber,buttonsProperty:a.buttons,positions:[$[0],$[W.length-1]]}),f=D(Y.end,c,F,{handles:b.handles,handleNumber:b.handleNumber}),g=D("mouseout",c,G,{handles:b.handles,handleNumber:b.handleNumber});if(c.noUiListeners=e.concat(f,g),a.cursor){document.body.style.cursor=getComputedStyle(a.target).cursor,W.length>1&&i(Z,d.cssClasses.drag);var h=function(){return!1};document.body.noUiListener=h,document.body.addEventListener("selectstart",h,!1)}void 0!==b.handleNumber&&B("start",b.handleNumber)}function I(a){var b,f,g=a.calcPoint,h=0;return a.stopPropagation(),W.forEach(function(a){h+=c(a)[d.style]}),b=h/2>g||1===W.length?0:1,W[b].hasAttribute("disabled")&&(b=b?0:1),g-=c(V)[d.style],f=100*g/A(),d.events.snap||e(Z,d.cssClasses.tap,d.animationDuration),W[b].hasAttribute("disabled")?!1:(L(W[b],f),B("slide",b,!0),B("set",b,!0),B("change",b,!0),void(d.events.snap&&H(a,{handles:[W[b]]})))}function J(a){var b=a.calcPoint-c(V)[d.style],e=_.getStep(100*b/A()),f=_.fromStepping(e);Object.keys(ba).forEach(function(a){"hover"===a.split(".")[0]&&ba[a].forEach(function(a){a.call(X,f)})})}function K(a){if(a.fixed||W.forEach(function(a,b){D(Y.start,a.children[0],H,{handles:[a],handleNumber:b})}),a.tap&&D(Y.start,V,I,{handles:W}),a.hover&&D(Y.move,V,J,{hover:!0}),a.drag){var b=[V.querySelector("."+d.cssClasses.connect)];i(b[0],d.cssClasses.draggable),a.fixed&&b.push(W[b[0]===W[0]?1:0].children[0]),b.forEach(function(a){D(Y.start,a,H,{handles:W})})}}function L(a,b,c){var e=a!==W[0]?1:0,g=$[0]+d.margin,h=$[1]-d.margin,k=$[0]+d.limit,l=$[1]-d.limit;return W.length>1&&(b=e?Math.max(b,g):Math.min(b,h)),c!==!1&&d.limit&&W.length>1&&(b=e?Math.min(b,k):Math.max(b,l)),b=_.getStep(b),b=f(b),b===$[e]?!1:(window.requestAnimationFrame?window.requestAnimationFrame(function(){a.style[d.style]=b+"%"}):a.style[d.style]=b+"%",a.previousSibling||(j(a,d.cssClasses.stacking),b>50&&i(a,d.cssClasses.stacking)),$[e]=b,aa[e]=_.fromStepping(b),B("update",e),!0)}function M(a,b){var c,e,f;for(d.limit&&(a+=1),c=0;a>c;c+=1)e=c%2,f=b[e],null!==f&&f!==!1&&("number"==typeof f&&(f=String(f)),f=d.format.from(f),(f===!1||isNaN(f)||L(W[e],_.toStepping(f),c===3-d.dir)===!1)&&B("update",e))}function N(a,b){var c,f,h=g(a);for(b=void 0===b?!0:!!b,d.dir&&d.handles>1&&h.reverse(),d.animate&&-1!==$[0]&&e(Z,d.cssClasses.tap,d.animationDuration),c=W.length>1?3:1,1===h.length&&(c=1),M(c,h),f=0;f<W.length;f++)null!==h[f]&&b&&B("set",f)}function P(){var a,b=[];for(a=0;a<d.handles;a+=1)b[a]=d.format.to(aa[a]);return C(b)}function Q(){for(var a in d.cssClasses)d.cssClasses.hasOwnProperty(a)&&j(Z,d.cssClasses[a]);for(;Z.firstChild;)Z.removeChild(Z.firstChild);delete Z.noUiSlider}function R(){var a=$.map(function(a,b){var c=_.getApplicableStep(a),d=h(String(c[2])),e=aa[b],f=100===a?null:c[2],g=Number((e-c[2]).toFixed(d)),i=0===a?null:g>=c[1]?c[2]:c[0]||!1;return[i,f]});return C(a)}function S(a,b){ba[a]=ba[a]||[],ba[a].push(b),"update"===a.split(".")[0]&&W.forEach(function(a,b){B("update",b)})}function T(a){var b=a&&a.split(".")[0],c=b&&a.substring(b.length);Object.keys(ba).forEach(function(a){var d=a.split(".")[0],e=a.substring(d.length);b&&b!==d||c&&c!==e||delete ba[a]})}function U(a,b){var c=P(),e=O({start:[0,0],margin:a.margin,limit:a.limit,step:void 0===a.step?d.singleStep:a.step,range:a.range,animate:a.animate,snap:void 0===a.snap?d.snap:a.snap});["margin","limit","range","animate"].forEach(function(b){void 0!==a[b]&&(d[b]=a[b])}),e.spectrum.direction=_.direction,_=e.spectrum,$=[-1,-1],N(a.start||c,b)}var V,W,X,Y=m(),Z=b,$=[-1,-1],_=d.spectrum,aa=[],ba={};if(Z.noUiSlider)throw new Error("Slider was already initialized.");return V=t(d.dir,d.ort,Z),W=s(d.handles,d.dir,V),r(d.connect,Z,W),d.pips&&z(d.pips),d.tooltips&&v(),X={destroy:Q,steps:R,on:S,off:T,get:P,set:N,updateOptions:U,options:n,target:Z,pips:z},K(d.events),X}function Q(a,b){if(!a.nodeName)throw new Error("noUiSlider.create requires a single element.");var c=O(b,a),d=P(a,c,b);return d.set(c.start),a.noUiSlider=d,d}x.prototype.getMargin=function(a){return 2===this.xPct.length?o(this.xVal,a):!1},x.prototype.toStepping=function(a){return a=s(this.xVal,this.xPct,a),this.direction&&(a=100-a),a},x.prototype.fromStepping=function(a){return this.direction&&(a=100-a),t(this.xVal,this.xPct,a)},x.prototype.getStep=function(a){return this.direction&&(a=100-a),a=u(this.xPct,this.xSteps,this.snap,a),this.direction&&(a=100-a),a},x.prototype.getApplicableStep=function(a){var b=r(a,this.xPct),c=100===a?2:1;return[this.xNumSteps[b-2],this.xVal[b-c],this.xNumSteps[b-c]]},x.prototype.convert=function(a){return this.getStep(this.toStepping(a))};var R={to:function(a){return void 0!==a&&a.toFixed(2)},from:Number};return{create:Q}});

/*
 * Spectrum Colorpicker v1.8.0
 * https://github.com/bgrins/spectrum
 * Author: Brian Grinstead
 * License: MIT
 */
!function(t){"use strict";"function"==typeof define&&define.amd?define(["jquery"],t):"object"==typeof exports&&"object"==typeof module?module.exports=t(require("jquery")):t(jQuery)}(function(t,e){"use strict";function r(e,r,a,n){for(var o=[],i=0;i<e.length;i++){var s=e[i];if(s){var l=tinycolor(s),c=l.toHsl().l<.5?"sp-thumb-el sp-thumb-dark":"sp-thumb-el sp-thumb-light";c+=tinycolor.equals(r,s)?" sp-thumb-active":"";var f=l.toString(n.preferredFormat||"rgb"),u=b?"background-color:"+l.toRgbString():"filter:"+l.toFilter();o.push('<span title="'+f+'" data-color="'+l.toRgbString()+'" class="'+c+'"><span class="sp-thumb-inner" style="'+u+';" /></span>')}else{var h="sp-clear-display";o.push(t("<div />").append(t('<span data-color="" style="background-color:transparent;" class="'+h+'"></span>').attr("title",n.noColorSelectedText)).html())}}return"<div class='sp-cf "+a+"'>"+o.join("")+"</div>"}function a(){for(var t=0;t<p.length;t++)p[t]&&p[t].hide()}function n(e,r){var a=t.extend({},d,e);return a.callbacks={move:c(a.move,r),change:c(a.change,r),show:c(a.show,r),hide:c(a.hide,r),beforeShow:c(a.beforeShow,r)},a}function o(o,s){function c(){if(W.showPaletteOnly&&(W.showPalette=!0),Dt.text(W.showPaletteOnly?W.togglePaletteMoreText:W.togglePaletteLessText),W.palette){dt=W.palette.slice(0),pt=t.isArray(dt[0])?dt:[dt],gt={};for(var e=0;e<pt.length;e++)for(var r=0;r<pt[e].length;r++){var a=tinycolor(pt[e][r]).toRgbString();gt[a]=!0}}kt.toggleClass("sp-flat",X),kt.toggleClass("sp-input-disabled",!W.showInput),kt.toggleClass("sp-alpha-enabled",W.showAlpha),kt.toggleClass("sp-clear-enabled",Qt),kt.toggleClass("sp-buttons-disabled",!W.showButtons),kt.toggleClass("sp-palette-buttons-disabled",!W.togglePaletteOnly),kt.toggleClass("sp-palette-disabled",!W.showPalette),kt.toggleClass("sp-palette-only",W.showPaletteOnly),kt.toggleClass("sp-initial-disabled",!W.showInitial),kt.addClass(W.className).addClass(W.containerClassName),z()}function d(){function e(e){return e.data&&e.data.ignore?(O(t(e.target).closest(".sp-thumb-el").data("color")),j()):(O(t(e.target).closest(".sp-thumb-el").data("color")),j(),W.hideAfterPaletteSelect?(I(!0),F()):I()),!1}if(g&&kt.find("*:not(input)").attr("unselectable","on"),c(),Bt&&_t.after(Lt).hide(),Qt||jt.hide(),X)_t.after(kt).hide();else{var r="parent"===W.appendTo?_t.parent():t(W.appendTo);1!==r.length&&(r=t("body")),r.append(kt)}y(),Kt.on("click.spectrum touchstart.spectrum",function(e){xt||A(),e.stopPropagation(),t(e.target).is("input")||e.preventDefault()}),(_t.is(":disabled")||W.disabled===!0)&&V(),kt.click(l),Tt.change(P),Tt.on("paste",function(){setTimeout(P,1)}),Tt.keydown(function(t){13==t.keyCode&&P()}),Nt.text(W.cancelText),Nt.on("click.spectrum",function(t){t.stopPropagation(),t.preventDefault(),T(),F()}),jt.attr("title",W.clearText),jt.on("click.spectrum",function(t){t.stopPropagation(),t.preventDefault(),Gt=!0,j(),X&&I(!0)}),Et.text(W.chooseText),Et.on("click.spectrum",function(t){t.stopPropagation(),t.preventDefault(),g&&Tt.is(":focus")&&Tt.trigger("change"),N()&&(I(!0),F())}),Dt.text(W.showPaletteOnly?W.togglePaletteMoreText:W.togglePaletteLessText),Dt.on("click.spectrum",function(t){t.stopPropagation(),t.preventDefault(),W.showPaletteOnly=!W.showPaletteOnly,W.showPaletteOnly||X||kt.css("left","-="+(St.outerWidth(!0)+5)),c()}),f(Ht,function(t,e,r){ht=t/it,Gt=!1,r.shiftKey&&(ht=Math.round(10*ht)/10),j()},S,C),f(At,function(t,e){ct=parseFloat(e/nt),Gt=!1,W.showAlpha||(ht=1),j()},S,C),f(Ct,function(t,e,r){if(r.shiftKey){if(!yt){var a=ft*et,n=rt-ut*rt,o=Math.abs(t-a)>Math.abs(e-n);yt=o?"x":"y"}}else yt=null;var i=!yt||"x"===yt,s=!yt||"y"===yt;i&&(ft=parseFloat(t/et)),s&&(ut=parseFloat((rt-e)/rt)),Gt=!1,W.showAlpha||(ht=1),j()},S,C),$t?(O($t),E(),Xt=W.preferredFormat||tinycolor($t).format,w($t)):E(),X&&M();var a=g?"mousedown.spectrum":"click.spectrum touchstart.spectrum";Ot.on(a,".sp-thumb-el",e),qt.on(a,".sp-thumb-el:nth-child(1)",{ignore:!0},e)}function y(){if(G&&window.localStorage){try{var e=window.localStorage[G].split(",#");e.length>1&&(delete window.localStorage[G],t.each(e,function(t,e){w(e)}))}catch(r){}try{bt=window.localStorage[G].split(";")}catch(r){}}}function w(e){if(Y){var r=tinycolor(e).toRgbString();if(!gt[r]&&-1===t.inArray(r,bt))for(bt.push(r);bt.length>vt;)bt.shift();if(G&&window.localStorage)try{window.localStorage[G]=bt.join(";")}catch(a){}}}function _(){var t=[];if(W.showPalette)for(var e=0;e<bt.length;e++){var r=tinycolor(bt[e]).toRgbString();gt[r]||t.push(bt[e])}return t.reverse().slice(0,W.maxSelectionSize)}function x(){var e=q(),a=t.map(pt,function(t,a){return r(t,e,"sp-palette-row sp-palette-row-"+a,W)});y(),bt&&a.push(r(_(),e,"sp-palette-row sp-palette-row-selection",W)),Ot.html(a.join(""))}function k(){if(W.showInitial){var t=Wt,e=q();qt.html(r([t,e],e,"sp-palette-row-initial",W))}}function S(){(0>=rt||0>=et||0>=nt)&&z(),tt=!0,kt.addClass(mt),yt=null,_t.trigger("dragstart.spectrum",[q()])}function C(){tt=!1,kt.removeClass(mt),_t.trigger("dragstop.spectrum",[q()])}function P(){var t=Tt.val();if(null!==t&&""!==t||!Qt){var e=tinycolor(t);e.isValid()?(O(e),j(),I()):Tt.addClass("sp-validation-error")}else O(null),j(),I()}function A(){Z?F():M()}function M(){var e=t.Event("beforeShow.spectrum");return Z?void z():(_t.trigger(e,[q()]),void(J.beforeShow(q())===!1||e.isDefaultPrevented()||(a(),Z=!0,t(wt).on("keydown.spectrum",R),t(wt).on("click.spectrum",H),t(window).on("resize.spectrum",U),Lt.addClass("sp-active"),kt.removeClass("sp-hidden"),z(),E(),Wt=q(),k(),J.show(Wt),_t.trigger("show.spectrum",[Wt]))))}function R(t){27===t.keyCode&&F()}function H(t){2!=t.button&&(tt||(Yt?I(!0):T(),F()))}function F(){Z&&!X&&(Z=!1,t(wt).off("keydown.spectrum",R),t(wt).off("click.spectrum",H),t(window).off("resize.spectrum",U),Lt.removeClass("sp-active"),kt.addClass("sp-hidden"),J.hide(q()),_t.trigger("hide.spectrum",[q()]))}function T(){O(Wt,!0),I(!0)}function O(t,e){if(tinycolor.equals(t,q()))return void E();var r,a;!t&&Qt?Gt=!0:(Gt=!1,r=tinycolor(t),a=r.toHsv(),ct=a.h%360/360,ft=a.s,ut=a.v,ht=a.a),E(),r&&r.isValid()&&!e&&(Xt=W.preferredFormat||r.getFormat())}function q(t){return t=t||{},Qt&&Gt?null:tinycolor.fromRatio({h:ct,s:ft,v:ut,a:Math.round(1e3*ht)/1e3},{format:t.format||Xt})}function N(){return!Tt.hasClass("sp-validation-error")}function j(){E(),J.move(q()),_t.trigger("move.spectrum",[q()])}function E(){Tt.removeClass("sp-validation-error"),D();var t=tinycolor.fromRatio({h:ct,s:1,v:1});Ct.css("background-color",t.toHexString());var e=Xt;1>ht&&(0!==ht||"name"!==e)&&("hex"!==e&&"hex3"!==e&&"hex6"!==e&&"name"!==e||(e="rgb"));var r=q({format:e}),a="";if(Vt.removeClass("sp-clear-display"),Vt.css("background-color","transparent"),!r&&Qt)Vt.addClass("sp-clear-display");else{var n=r.toHexString(),o=r.toRgbString();if(b||1===r.alpha?Vt.css("background-color",o):(Vt.css("background-color","transparent"),Vt.css("filter",r.toFilter())),W.showAlpha){var i=r.toRgb();i.a=0;var s=tinycolor(i).toRgbString(),l="linear-gradient(left, "+s+", "+n+")";g?Rt.css("filter",tinycolor(s).toFilter({gradientType:1},n)):(Rt.css("background","-webkit-"+l),Rt.css("background","-moz-"+l),Rt.css("background","-ms-"+l),Rt.css("background","linear-gradient(to right, "+s+", "+n+")"))}a=r.toString(e)}W.showInput&&Tt.val(a),W.showPalette&&x(),k()}function D(){var t=ft,e=ut;if(Qt&&Gt)Ft.hide(),Mt.hide(),Pt.hide();else{Ft.show(),Mt.show(),Pt.show();var r=t*et,a=rt-e*rt;r=Math.max(-at,Math.min(et-at,r-at)),a=Math.max(-at,Math.min(rt-at,a-at)),Pt.css({top:a+"px",left:r+"px"});var n=ht*it;Ft.css({left:n-st/2+"px"});var o=ct*nt;Mt.css({top:o-lt+"px"})}}function I(t){var e=q(),r="",a=!tinycolor.equals(e,Wt);e&&(r=e.toString(Xt),w(e)),It&&_t.val(r),t&&a&&(J.change(e),_t.trigger("change",[e]))}function z(){Z&&(et=Ct.width(),rt=Ct.height(),at=Pt.height(),ot=At.width(),nt=At.height(),lt=Mt.height(),it=Ht.width(),st=Ft.width(),X||(kt.css("position","absolute"),W.offset?kt.offset(W.offset):kt.offset(i(kt,Kt))),D(),W.showPalette&&x(),_t.trigger("reflow.spectrum"))}function B(){_t.show(),Kt.off("click.spectrum touchstart.spectrum"),kt.remove(),Lt.remove(),p[Jt.id]=null}function L(r,a){return r===e?t.extend({},W):a===e?W[r]:(W[r]=a,"preferredFormat"===r&&(Xt=W.preferredFormat),void c())}function K(){xt=!1,_t.attr("disabled",!1),Kt.removeClass("sp-disabled")}function V(){F(),xt=!0,_t.attr("disabled",!0),Kt.addClass("sp-disabled")}function $(t){W.offset=t,z()}var W=n(s,o),X=W.flat,Y=W.showSelectionPalette,G=W.localStorageKey,Q=W.theme,J=W.callbacks,U=u(z,10),Z=!1,tt=!1,et=0,rt=0,at=0,nt=0,ot=0,it=0,st=0,lt=0,ct=0,ft=0,ut=0,ht=1,dt=[],pt=[],gt={},bt=W.selectionPalette.slice(0),vt=W.maxSelectionSize,mt="sp-dragging",yt=null,wt=o.ownerDocument,_t=(wt.body,t(o)),xt=!1,kt=t(m,wt).addClass(Q),St=kt.find(".sp-picker-container"),Ct=kt.find(".sp-color"),Pt=kt.find(".sp-dragger"),At=kt.find(".sp-hue"),Mt=kt.find(".sp-slider"),Rt=kt.find(".sp-alpha-inner"),Ht=kt.find(".sp-alpha"),Ft=kt.find(".sp-alpha-handle"),Tt=kt.find(".sp-input"),Ot=kt.find(".sp-palette"),qt=kt.find(".sp-initial"),Nt=kt.find(".sp-cancel"),jt=kt.find(".sp-clear"),Et=kt.find(".sp-choose"),Dt=kt.find(".sp-palette-toggle"),It=_t.is("input"),zt=It&&"color"===_t.attr("type")&&h(),Bt=It&&!X,Lt=Bt?t(v).addClass(Q).addClass(W.className).addClass(W.replacerClassName):t([]),Kt=Bt?Lt:_t,Vt=Lt.find(".sp-preview-inner"),$t=W.color||It&&_t.val(),Wt=!1,Xt=W.preferredFormat,Yt=!W.showButtons||W.clickoutFiresChange,Gt=!$t,Qt=W.allowEmpty&&!zt;d();var Jt={show:M,hide:F,toggle:A,reflow:z,option:L,enable:K,disable:V,offset:$,set:function(t){O(t),I()},get:q,destroy:B,container:kt};return Jt.id=p.push(Jt)-1,Jt}function i(e,r){var a=0,n=e.outerWidth(),o=e.outerHeight(),i=r.outerHeight(),s=e[0].ownerDocument,l=s.documentElement,c=l.clientWidth+t(s).scrollLeft(),f=l.clientHeight+t(s).scrollTop(),u=r.offset(),h=u.left,d=u.top;return d+=i,h-=Math.min(h,h+n>c&&c>n?Math.abs(h+n-c):0),d-=Math.min(d,d+o>f&&f>o?Math.abs(o+i-a):a),{top:d,bottom:u.bottom,left:h,right:u.right,width:u.width,height:u.height}}function s(){}function l(t){t.stopPropagation()}function c(t,e){var r=Array.prototype.slice,a=r.call(arguments,2);return function(){return t.apply(e,a.concat(r.call(arguments)))}}function f(e,r,a,n){function o(t){t.stopPropagation&&t.stopPropagation(),t.preventDefault&&t.preventDefault(),t.returnValue=!1}function i(t){if(f){if(g&&c.documentMode<9&&!t.button)return l();var a=t.originalEvent&&t.originalEvent.touches&&t.originalEvent.touches[0],n=a&&a.pageX||t.pageX,i=a&&a.pageY||t.pageY,s=Math.max(0,Math.min(n-u.left,d)),b=Math.max(0,Math.min(i-u.top,h));p&&o(t),r.apply(e,[s,b,t])}}function s(r){var n=r.which?3==r.which:2==r.button;n||f||a.apply(e,arguments)!==!1&&(f=!0,h=t(e).height(),d=t(e).width(),u=t(e).offset(),t(c).on(b),t(c.body).addClass("sp-dragging"),i(r),o(r))}function l(){f&&(t(c).off(b),t(c.body).removeClass("sp-dragging"),setTimeout(function(){n.apply(e,arguments)},0)),f=!1}r=r||function(){},a=a||function(){},n=n||function(){};var c=document,f=!1,u={},h=0,d=0,p="ontouchstart"in window,b={};b.selectstart=o,b.dragstart=o,b["touchmove mousemove"]=i,b["touchend mouseup"]=l,t(e).on("touchstart mousedown",s)}function u(t,e,r){var a;return function(){var n=this,o=arguments,i=function(){a=null,t.apply(n,o)};r&&clearTimeout(a),!r&&a||(a=setTimeout(i,e))}}function h(){return t.fn.spectrum.inputTypeColorSupport()}var d={beforeShow:s,move:s,change:s,show:s,hide:s,color:!1,flat:!1,showInput:!1,allowEmpty:!1,showButtons:!0,clickoutFiresChange:!0,showInitial:!1,showPalette:!1,showPaletteOnly:!1,hideAfterPaletteSelect:!1,togglePaletteOnly:!1,showSelectionPalette:!0,localStorageKey:!1,appendTo:"body",maxSelectionSize:7,cancelText:"cancel",chooseText:"choose",togglePaletteMoreText:"more",togglePaletteLessText:"less",clearText:"Clear Color Selection",noColorSelectedText:"No Color Selected",preferredFormat:!1,className:"",containerClassName:"",replacerClassName:"",showAlpha:!1,theme:"sp-light",palette:[["#ffffff","#000000","#ff0000","#ff8000","#ffff00","#008000","#0000ff","#4b0082","#9400d3"]],selectionPalette:[],disabled:!1,offset:null},p=[],g=!!/msie/i.exec(window.navigator.userAgent),b=function(){function t(t,e){return!!~(""+t).indexOf(e)}var e=document.createElement("div"),r=e.style;return r.cssText="background-color:rgba(0,0,0,.5)",t(r.backgroundColor,"rgba")||t(r.backgroundColor,"hsla")}(),v=["<div class='sp-replacer'>","<div class='sp-preview'><div class='sp-preview-inner'></div></div>","<div class='sp-dd'>&#9660;</div>","</div>"].join(""),m=function(){var t="";if(g)for(var e=1;6>=e;e++)t+="<div class='sp-"+e+"'></div>";return["<div class='sp-container sp-hidden'>","<div class='sp-palette-container'>","<div class='sp-palette sp-thumb sp-cf'></div>","<div class='sp-palette-button-container sp-cf'>","<button type='button' class='sp-palette-toggle'></button>","</div>","</div>","<div class='sp-picker-container'>","<div class='sp-top sp-cf'>","<div class='sp-fill'></div>","<div class='sp-top-inner'>","<div class='sp-color'>","<div class='sp-sat'>","<div class='sp-val'>","<div class='sp-dragger'></div>","</div>","</div>","</div>","<div class='sp-clear sp-clear-display'>","</div>","<div class='sp-hue'>","<div class='sp-slider'></div>",t,"</div>","</div>","<div class='sp-alpha'><div class='sp-alpha-inner'><div class='sp-alpha-handle'></div></div></div>","</div>","<div class='sp-input-container sp-cf'>","<input class='sp-input' type='text' spellcheck='false'  />","</div>","<div class='sp-initial sp-thumb sp-cf'></div>","<div class='sp-button-container sp-cf'>","<a class='sp-cancel' href='#'></a>","<button type='button' class='sp-choose'></button>","</div>","</div>","</div>"].join("")}(),y="spectrum.id";t.fn.spectrum=function(e){if("string"==typeof e){var r=this,a=Array.prototype.slice.call(arguments,1);return this.each(function(){var n=p[t(this).data(y)];if(n){var o=n[e];if(!o)throw new Error("Spectrum: no such method: '"+e+"'");"get"==e?r=n.get():"container"==e?r=n.container:"option"==e?r=n.option.apply(n,a):"destroy"==e?(n.destroy(),t(this).removeData(y)):o.apply(n,a)}}),r}return this.spectrum("destroy").each(function(){var r=t.extend({},t(this).data(),e),a=o(this,r);t(this).data(y,a.id)})},t.fn.spectrum.load=!0,t.fn.spectrum.loadOpts={},t.fn.spectrum.draggable=f,t.fn.spectrum.defaults=d,t.fn.spectrum.inputTypeColorSupport=function w(){if("undefined"==typeof w._cachedResult){var e=t("<input type='color'/>")[0];w._cachedResult="color"===e.type&&""!==e.value}return w._cachedResult},t.spectrum={},t.spectrum.localization={},t.spectrum.palettes={},t.fn.spectrum.processNativeColorInputs=function(){var e=t("input[type=color]");e.length&&!h()&&e.spectrum({preferredFormat:"hex6"})},function(){function t(t){var r={r:0,g:0,b:0},n=1,i=!1,s=!1;return"string"==typeof t&&(t=T(t)),"object"==typeof t&&(t.hasOwnProperty("r")&&t.hasOwnProperty("g")&&t.hasOwnProperty("b")?(r=e(t.r,t.g,t.b),i=!0,s="%"===String(t.r).substr(-1)?"prgb":"rgb"):t.hasOwnProperty("h")&&t.hasOwnProperty("s")&&t.hasOwnProperty("v")?(t.s=R(t.s),t.v=R(t.v),r=o(t.h,t.s,t.v),i=!0,s="hsv"):t.hasOwnProperty("h")&&t.hasOwnProperty("s")&&t.hasOwnProperty("l")&&(t.s=R(t.s),t.l=R(t.l),r=a(t.h,t.s,t.l),i=!0,s="hsl"),t.hasOwnProperty("a")&&(n=t.a)),n=x(n),{ok:i,format:t.format||s,r:D(255,I(r.r,0)),g:D(255,I(r.g,0)),b:D(255,I(r.b,0)),a:n}}function e(t,e,r){return{r:255*k(t,255),g:255*k(e,255),b:255*k(r,255)}}function r(t,e,r){t=k(t,255),e=k(e,255),r=k(r,255);var a,n,o=I(t,e,r),i=D(t,e,r),s=(o+i)/2;if(o==i)a=n=0;else{var l=o-i;switch(n=s>.5?l/(2-o-i):l/(o+i),o){case t:a=(e-r)/l+(r>e?6:0);break;case e:a=(r-t)/l+2;break;case r:a=(t-e)/l+4}a/=6}return{h:a,s:n,l:s}}function a(t,e,r){function a(t,e,r){return 0>r&&(r+=1),r>1&&(r-=1),1/6>r?t+6*(e-t)*r:.5>r?e:2/3>r?t+(e-t)*(2/3-r)*6:t}var n,o,i;if(t=k(t,360),e=k(e,100),r=k(r,100),0===e)n=o=i=r;else{var s=.5>r?r*(1+e):r+e-r*e,l=2*r-s;n=a(l,s,t+1/3),o=a(l,s,t),i=a(l,s,t-1/3)}return{r:255*n,g:255*o,b:255*i}}function n(t,e,r){t=k(t,255),e=k(e,255),r=k(r,255);var a,n,o=I(t,e,r),i=D(t,e,r),s=o,l=o-i;if(n=0===o?0:l/o,o==i)a=0;else{switch(o){case t:a=(e-r)/l+(r>e?6:0);break;case e:a=(r-t)/l+2;break;case r:a=(t-e)/l+4}a/=6}return{h:a,s:n,v:s}}function o(t,e,r){t=6*k(t,360),e=k(e,100),r=k(r,100);var a=j.floor(t),n=t-a,o=r*(1-e),i=r*(1-n*e),s=r*(1-(1-n)*e),l=a%6,c=[r,i,o,o,s,r][l],f=[s,r,r,i,o,o][l],u=[o,o,s,r,r,i][l];return{r:255*c,g:255*f,b:255*u}}function i(t,e,r,a){var n=[M(E(t).toString(16)),M(E(e).toString(16)),M(E(r).toString(16))];return a&&n[0].charAt(0)==n[0].charAt(1)&&n[1].charAt(0)==n[1].charAt(1)&&n[2].charAt(0)==n[2].charAt(1)?n[0].charAt(0)+n[1].charAt(0)+n[2].charAt(0):n.join("")}function s(t,e,r,a){var n=[M(H(a)),M(E(t).toString(16)),M(E(e).toString(16)),M(E(r).toString(16))];return n.join("")}function l(t,e){e=0===e?0:e||10;var r=B(t).toHsl();return r.s-=e/100,r.s=S(r.s),B(r)}function c(t,e){e=0===e?0:e||10;var r=B(t).toHsl();return r.s+=e/100,r.s=S(r.s),B(r)}function f(t){return B(t).desaturate(100)}function u(t,e){e=0===e?0:e||10;var r=B(t).toHsl();return r.l+=e/100,r.l=S(r.l),B(r)}function h(t,e){e=0===e?0:e||10;var r=B(t).toRgb();return r.r=I(0,D(255,r.r-E(255*-(e/100)))),r.g=I(0,D(255,r.g-E(255*-(e/100)))),r.b=I(0,D(255,r.b-E(255*-(e/100)))),B(r)}function d(t,e){e=0===e?0:e||10;var r=B(t).toHsl();return r.l-=e/100,r.l=S(r.l),B(r)}function p(t,e){var r=B(t).toHsl(),a=(E(r.h)+e)%360;return r.h=0>a?360+a:a,B(r)}function g(t){var e=B(t).toHsl();return e.h=(e.h+180)%360,B(e)}function b(t){var e=B(t).toHsl(),r=e.h;return[B(t),B({h:(r+120)%360,s:e.s,l:e.l}),B({h:(r+240)%360,s:e.s,l:e.l})]}function v(t){var e=B(t).toHsl(),r=e.h;return[B(t),B({h:(r+90)%360,s:e.s,l:e.l}),B({h:(r+180)%360,s:e.s,l:e.l}),B({h:(r+270)%360,s:e.s,l:e.l})]}function m(t){var e=B(t).toHsl(),r=e.h;return[B(t),B({h:(r+72)%360,s:e.s,l:e.l}),B({h:(r+216)%360,s:e.s,l:e.l})]}function y(t,e,r){e=e||6,r=r||30;var a=B(t).toHsl(),n=360/r,o=[B(t)];for(a.h=(a.h-(n*e>>1)+720)%360;--e;)a.h=(a.h+n)%360,o.push(B(a));return o}function w(t,e){e=e||6;for(var r=B(t).toHsv(),a=r.h,n=r.s,o=r.v,i=[],s=1/e;e--;)i.push(B({h:a,s:n,v:o})),o=(o+s)%1;return i}function _(t){var e={};for(var r in t)t.hasOwnProperty(r)&&(e[t[r]]=r);return e}function x(t){return t=parseFloat(t),(isNaN(t)||0>t||t>1)&&(t=1),t}function k(t,e){P(t)&&(t="100%");var r=A(t);return t=D(e,I(0,parseFloat(t))),r&&(t=parseInt(t*e,10)/100),j.abs(t-e)<1e-6?1:t%e/parseFloat(e)}function S(t){return D(1,I(0,t))}function C(t){return parseInt(t,16)}function P(t){return"string"==typeof t&&-1!=t.indexOf(".")&&1===parseFloat(t)}function A(t){return"string"==typeof t&&-1!=t.indexOf("%")}function M(t){return 1==t.length?"0"+t:""+t}function R(t){return 1>=t&&(t=100*t+"%"),t}function H(t){return Math.round(255*parseFloat(t)).toString(16)}function F(t){return C(t)/255}function T(t){t=t.replace(O,"").replace(q,"").toLowerCase();var e=!1;if(L[t])t=L[t],e=!0;else if("transparent"==t)return{r:0,g:0,b:0,a:0,format:"name"};var r;return(r=V.rgb.exec(t))?{r:r[1],g:r[2],b:r[3]}:(r=V.rgba.exec(t))?{r:r[1],g:r[2],b:r[3],a:r[4]}:(r=V.hsl.exec(t))?{h:r[1],s:r[2],l:r[3]}:(r=V.hsla.exec(t))?{h:r[1],s:r[2],l:r[3],a:r[4]}:(r=V.hsv.exec(t))?{h:r[1],s:r[2],v:r[3]}:(r=V.hsva.exec(t))?{h:r[1],s:r[2],v:r[3],a:r[4]}:(r=V.hex8.exec(t))?{a:F(r[1]),r:C(r[2]),g:C(r[3]),b:C(r[4]),format:e?"name":"hex8"}:(r=V.hex6.exec(t))?{r:C(r[1]),g:C(r[2]),b:C(r[3]),format:e?"name":"hex"}:(r=V.hex3.exec(t))?{r:C(r[1]+""+r[1]),g:C(r[2]+""+r[2]),b:C(r[3]+""+r[3]),format:e?"name":"hex"}:!1}var O=/^[\s,#]+/,q=/\s+$/,N=0,j=Math,E=j.round,D=j.min,I=j.max,z=j.random,B=function(e,r){if(e=e?e:"",r=r||{},e instanceof B)return e;if(!(this instanceof B))return new B(e,r);var a=t(e);this._originalInput=e,this._r=a.r,this._g=a.g,this._b=a.b,this._a=a.a,this._roundA=E(1e3*this._a)/1e3,this._format=r.format||a.format,this._gradientType=r.gradientType,this._r<1&&(this._r=E(this._r)),this._g<1&&(this._g=E(this._g)),this._b<1&&(this._b=E(this._b)),this._ok=a.ok,this._tc_id=N++};B.prototype={isDark:function(){return this.getBrightness()<128},isLight:function(){return!this.isDark()},isValid:function(){return this._ok},getOriginalInput:function(){return this._originalInput},getFormat:function(){return this._format},getAlpha:function(){return this._a},getBrightness:function(){var t=this.toRgb();return(299*t.r+587*t.g+114*t.b)/1e3},setAlpha:function(t){return this._a=x(t),this._roundA=E(1e3*this._a)/1e3,this},toHsv:function(){var t=n(this._r,this._g,this._b);return{h:360*t.h,s:t.s,v:t.v,a:this._a}},toHsvString:function(){var t=n(this._r,this._g,this._b),e=E(360*t.h),r=E(100*t.s),a=E(100*t.v);return 1==this._a?"hsv("+e+", "+r+"%, "+a+"%)":"hsva("+e+", "+r+"%, "+a+"%, "+this._roundA+")"},toHsl:function(){var t=r(this._r,this._g,this._b);return{h:360*t.h,s:t.s,l:t.l,a:this._a}},toHslString:function(){var t=r(this._r,this._g,this._b),e=E(360*t.h),a=E(100*t.s),n=E(100*t.l);return 1==this._a?"hsl("+e+", "+a+"%, "+n+"%)":"hsla("+e+", "+a+"%, "+n+"%, "+this._roundA+")"},toHex:function(t){return i(this._r,this._g,this._b,t)},toHexString:function(t){return"#"+this.toHex(t)},toHex8:function(){return s(this._r,this._g,this._b,this._a)},toHex8String:function(){return"#"+this.toHex8()},toRgb:function(){return{r:E(this._r),g:E(this._g),b:E(this._b),a:this._a}},toRgbString:function(){return 1==this._a?"rgb("+E(this._r)+", "+E(this._g)+", "+E(this._b)+")":"rgba("+E(this._r)+", "+E(this._g)+", "+E(this._b)+", "+this._roundA+")"},toPercentageRgb:function(){return{r:E(100*k(this._r,255))+"%",g:E(100*k(this._g,255))+"%",b:E(100*k(this._b,255))+"%",a:this._a}},toPercentageRgbString:function(){return 1==this._a?"rgb("+E(100*k(this._r,255))+"%, "+E(100*k(this._g,255))+"%, "+E(100*k(this._b,255))+"%)":"rgba("+E(100*k(this._r,255))+"%, "+E(100*k(this._g,255))+"%, "+E(100*k(this._b,255))+"%, "+this._roundA+")"},toName:function(){return 0===this._a?"transparent":this._a<1?!1:K[i(this._r,this._g,this._b,!0)]||!1},toFilter:function(t){var e="#"+s(this._r,this._g,this._b,this._a),r=e,a=this._gradientType?"GradientType = 1, ":"";if(t){var n=B(t);r=n.toHex8String()}return"progid:DXImageTransform.Microsoft.gradient("+a+"startColorstr="+e+",endColorstr="+r+")"},toString:function(t){var e=!!t;t=t||this._format;var r=!1,a=this._a<1&&this._a>=0,n=!e&&a&&("hex"===t||"hex6"===t||"hex3"===t||"name"===t);return n?"name"===t&&0===this._a?this.toName():this.toRgbString():("rgb"===t&&(r=this.toRgbString()),"prgb"===t&&(r=this.toPercentageRgbString()),"hex"!==t&&"hex6"!==t||(r=this.toHexString()),"hex3"===t&&(r=this.toHexString(!0)),"hex8"===t&&(r=this.toHex8String()),"name"===t&&(r=this.toName()),"hsl"===t&&(r=this.toHslString()),"hsv"===t&&(r=this.toHsvString()),r||this.toHexString())},_applyModification:function(t,e){var r=t.apply(null,[this].concat([].slice.call(e)));return this._r=r._r,this._g=r._g,this._b=r._b,this.setAlpha(r._a),this},lighten:function(){return this._applyModification(u,arguments)},brighten:function(){return this._applyModification(h,arguments)},darken:function(){return this._applyModification(d,arguments)},desaturate:function(){return this._applyModification(l,arguments)},saturate:function(){return this._applyModification(c,arguments)},greyscale:function(){return this._applyModification(f,arguments)},spin:function(){return this._applyModification(p,arguments)},_applyCombination:function(t,e){return t.apply(null,[this].concat([].slice.call(e)))},analogous:function(){return this._applyCombination(y,arguments)},complement:function(){return this._applyCombination(g,arguments)},monochromatic:function(){return this._applyCombination(w,arguments)},splitcomplement:function(){return this._applyCombination(m,arguments)},triad:function(){return this._applyCombination(b,arguments)},tetrad:function(){return this._applyCombination(v,arguments)}},B.fromRatio=function(t,e){if("object"==typeof t){var r={};for(var a in t)t.hasOwnProperty(a)&&("a"===a?r[a]=t[a]:r[a]=R(t[a]));t=r}return B(t,e)},B.equals=function(t,e){return t&&e?B(t).toRgbString()==B(e).toRgbString():!1},B.random=function(){return B.fromRatio({r:z(),g:z(),b:z()})},B.mix=function(t,e,r){r=0===r?0:r||50;var a,n=B(t).toRgb(),o=B(e).toRgb(),i=r/100,s=2*i-1,l=o.a-n.a;a=s*l==-1?s:(s+l)/(1+s*l),a=(a+1)/2;var c=1-a,f={r:o.r*a+n.r*c,g:o.g*a+n.g*c,b:o.b*a+n.b*c,a:o.a*i+n.a*(1-i)};return B(f)},B.readability=function(t,e){var r=B(t),a=B(e),n=r.toRgb(),o=a.toRgb(),i=r.getBrightness(),s=a.getBrightness(),l=Math.max(n.r,o.r)-Math.min(n.r,o.r)+Math.max(n.g,o.g)-Math.min(n.g,o.g)+Math.max(n.b,o.b)-Math.min(n.b,o.b);return{brightness:Math.abs(i-s),color:l}},B.isReadable=function(t,e){var r=B.readability(t,e);return r.brightness>125&&r.color>500},B.mostReadable=function(t,e){for(var r=null,a=0,n=!1,o=0;o<e.length;o++){var i=B.readability(t,e[o]),s=i.brightness>125&&i.color>500,l=3*(i.brightness/125)+i.color/500;(s&&!n||s&&n&&l>a||!s&&!n&&l>a)&&(n=s,a=l,r=B(e[o]))}return r};var L=B.names={aliceblue:"f0f8ff",antiquewhite:"faebd7",aqua:"0ff",aquamarine:"7fffd4",azure:"f0ffff",beige:"f5f5dc",bisque:"ffe4c4",black:"000",blanchedalmond:"ffebcd",blue:"00f",blueviolet:"8a2be2",brown:"a52a2a",burlywood:"deb887",burntsienna:"ea7e5d",cadetblue:"5f9ea0",chartreuse:"7fff00",chocolate:"d2691e",coral:"ff7f50",cornflowerblue:"6495ed",cornsilk:"fff8dc",crimson:"dc143c",cyan:"0ff",darkblue:"00008b",darkcyan:"008b8b",darkgoldenrod:"b8860b",darkgray:"a9a9a9",darkgreen:"006400",darkgrey:"a9a9a9",darkkhaki:"bdb76b",darkmagenta:"8b008b",darkolivegreen:"556b2f",darkorange:"ff8c00",darkorchid:"9932cc",darkred:"8b0000",darksalmon:"e9967a",darkseagreen:"8fbc8f",darkslateblue:"483d8b",darkslategray:"2f4f4f",darkslategrey:"2f4f4f",darkturquoise:"00ced1",darkviolet:"9400d3",deeppink:"ff1493",deepskyblue:"00bfff",dimgray:"696969",dimgrey:"696969",dodgerblue:"1e90ff",firebrick:"b22222",floralwhite:"fffaf0",forestgreen:"228b22",fuchsia:"f0f",gainsboro:"dcdcdc",ghostwhite:"f8f8ff",gold:"ffd700",goldenrod:"daa520",gray:"808080",green:"008000",greenyellow:"adff2f",grey:"808080",honeydew:"f0fff0",hotpink:"ff69b4",indianred:"cd5c5c",indigo:"4b0082",ivory:"fffff0",khaki:"f0e68c",lavender:"e6e6fa",lavenderblush:"fff0f5",lawngreen:"7cfc00",lemonchiffon:"fffacd",lightblue:"add8e6",lightcoral:"f08080",lightcyan:"e0ffff",lightgoldenrodyellow:"fafad2",lightgray:"d3d3d3",lightgreen:"90ee90",lightgrey:"d3d3d3",lightpink:"ffb6c1",lightsalmon:"ffa07a",lightseagreen:"20b2aa",lightskyblue:"87cefa",lightslategray:"789",lightslategrey:"789",lightsteelblue:"b0c4de",lightyellow:"ffffe0",lime:"0f0",limegreen:"32cd32",linen:"faf0e6",magenta:"f0f",maroon:"800000",mediumaquamarine:"66cdaa",mediumblue:"0000cd",mediumorchid:"ba55d3",mediumpurple:"9370db",mediumseagreen:"3cb371",mediumslateblue:"7b68ee",mediumspringgreen:"00fa9a",mediumturquoise:"48d1cc",mediumvioletred:"c71585",midnightblue:"191970",mintcream:"f5fffa",mistyrose:"ffe4e1",moccasin:"ffe4b5",navajowhite:"ffdead",navy:"000080",oldlace:"fdf5e6",olive:"808000",olivedrab:"6b8e23",orange:"ffa500",orangered:"ff4500",orchid:"da70d6",palegoldenrod:"eee8aa",palegreen:"98fb98",paleturquoise:"afeeee",palevioletred:"db7093",papayawhip:"ffefd5",peachpuff:"ffdab9",peru:"cd853f",pink:"ffc0cb",plum:"dda0dd",powderblue:"b0e0e6",purple:"800080",rebeccapurple:"663399",red:"f00",rosybrown:"bc8f8f",royalblue:"4169e1",saddlebrown:"8b4513",salmon:"fa8072",sandybrown:"f4a460",seagreen:"2e8b57",seashell:"fff5ee",sienna:"a0522d",silver:"c0c0c0",skyblue:"87ceeb",slateblue:"6a5acd",slategray:"708090",slategrey:"708090",snow:"fffafa",springgreen:"00ff7f",steelblue:"4682b4",tan:"d2b48c",teal:"008080",thistle:"d8bfd8",tomato:"ff6347",turquoise:"40e0d0",violet:"ee82ee",wheat:"f5deb3",white:"fff",whitesmoke:"f5f5f5",yellow:"ff0",yellowgreen:"9acd32"},K=B.hexNames=_(L),V=function(){var t="[-\\+]?\\d+%?",e="[-\\+]?\\d*\\.\\d+%?",r="(?:"+e+")|(?:"+t+")",a="[\\s|\\(]+("+r+")[,|\\s]+("+r+")[,|\\s]+("+r+")\\s*\\)?",n="[\\s|\\(]+("+r+")[,|\\s]+("+r+")[,|\\s]+("+r+")[,|\\s]+("+r+")\\s*\\)?";return{rgb:new RegExp("rgb"+a),rgba:new RegExp("rgba"+n),hsl:new RegExp("hsl"+a),hsla:new RegExp("hsla"+n),hsv:new RegExp("hsv"+a),hsva:new RegExp("hsva"+n),hex3:/^([0-9a-fA-F]{1})([0-9a-fA-F]{1})([0-9a-fA-F]{1})$/,hex6:/^([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/,hex8:/^([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/}}();window.tinycolor=B}(),t(function(){t.fn.spectrum.load&&t.fn.spectrum.processNativeColorInputs()})});
/*!
 * jQuery Validation Plugin v1.15.0
 *
 * http://jqueryvalidation.org/
 *
 * Copyright (c) 2016 Jörn Zaefferer
 * Released under the MIT license
 */
/*!
 * Renamed methods for compatibility
 */
!function(t){"function"==typeof define&&define.amd?define(["jquery"],t):"object"==typeof module&&module.exports?module.exports=t(require("jquery")):t(jQuery)}(function(t){t.extend(t.fn,{tc_validate:function(e){if(!this.length)return void(e&&e.debug&&window.console&&console.warn("Nothing selected, can't tc_validate, returning nothing."));var i=t.data(this[0],"tc_validator");return i?i:(this.attr("novalidate","novalidate"),i=new t.tc_validator(e,this[0]),t.data(this[0],"tc_validator",i),i.settings.onsubmit&&(this.on("click.tc_validate",":submit",function(e){i.settings.submitHandler&&(i.submitButton=e.target),t(this).hasClass("cancel")&&(i.cancelSubmit=!0),void 0!==t(this).attr("formnovalidate")&&(i.cancelSubmit=!0)}),this.on("submit.tc_validate",function(e){function s(){var s,r;return i.settings.submitHandler?(i.submitButton&&(s=t("<input type='hidden'/>").attr("name",i.submitButton.name).val(t(i.submitButton).val()).appendTo(i.currentForm)),r=i.settings.submitHandler.call(i,i.currentForm,e),i.submitButton&&s.remove(),void 0!==r?r:!1):!0}return i.settings.debug&&e.preventDefault(),i.cancelSubmit?(i.cancelSubmit=!1,s()):i.form()?i.pendingRequest?(i.formSubmitted=!0,!1):s():(i.focusInvalid(),!1)})),i)},tc_valid:function(){var e,i,s;return t(this[0]).is("form")?e=this.tc_validate().form():(s=[],e=!0,i=t(this[0].form).tc_validate(),this.each(function(){e=i.element(this)&&e,e||(s=s.concat(i.errorList))}),i.errorList=s),e},tc_rules:function(e,i){if(this.length){var s,r,n,a,o,l,h=this[0];if(e)switch(s=t.data(h.form,"tc_validator").settings,r=s.rules,n=t.tc_validator.staticRules(h),e){case"add":t.extend(n,t.tc_validator.normalizeRule(i)),delete n.messages,r[h.name]=n,i.messages&&(s.messages[h.name]=t.extend(s.messages[h.name],i.messages));break;case"remove":return i?(l={},t.each(i.split(/\s/),function(e,i){l[i]=n[i],delete n[i],"required"===i&&t(h).removeAttr("aria-required")}),l):(delete r[h.name],n)}return a=t.tc_validator.normalizeRules(t.extend({},t.tc_validator.classRules(h),t.tc_validator.attributeRules(h),t.tc_validator.dataRules(h),t.tc_validator.staticRules(h)),h),a.required&&(o=a.required,delete a.required,a=t.extend({required:o},a),t(h).attr("aria-required","true")),a.remote&&(o=a.remote,delete a.remote,a=t.extend(a,{remote:o})),a}}}),t.extend(t.expr[":"],{blank:function(e){return!t.trim(""+t(e).val())},filled:function(e){var i=t(e).val();return null!==i&&!!t.trim(""+i)},unchecked:function(e){return!t(e).prop("checked")}}),t.tc_validator=function(e,i){this.settings=t.extend(!0,{},t.tc_validator.defaults,e),this.currentForm=i,this.init()},t.tc_validator.format=function(e,i){return 1===arguments.length?function(){var i=t.makeArray(arguments);return i.unshift(e),t.tc_validator.format.apply(this,i)}:void 0===i?e:(arguments.length>2&&i.constructor!==Array&&(i=t.makeArray(arguments).slice(1)),i.constructor!==Array&&(i=[i]),t.each(i,function(t,i){e=e.replace(new RegExp("\\{"+t+"\\}","g"),function(){return i})}),e)},t.extend(t.tc_validator,{defaults:{messages:{},groups:{},rules:{},errorClass:"error",pendingClass:"pending",validClass:"valid",errorElement:"label",focusCleanup:!1,focusInvalid:!0,errorContainer:t([]),errorLabelContainer:t([]),onsubmit:!0,ignore:":hidden",ignoreTitle:!1,onfocusin:function(t){this.lastActive=t,this.settings.focusCleanup&&(this.settings.unhighlight&&this.settings.unhighlight.call(this,t,this.settings.errorClass,this.settings.validClass),this.hideThese(this.errorsFor(t)))},onfocusout:function(t){this.checkable(t)||!(t.name in this.submitted)&&this.optional(t)||this.element(t)},onkeyup:function(e,i){var s=[16,17,18,20,35,36,37,38,39,40,45,144,225];9===i.which&&""===this.elementValue(e)||-1!==t.inArray(i.keyCode,s)||(e.name in this.submitted||e.name in this.invalid)&&this.element(e)},onclick:function(t){t.name in this.submitted?this.element(t):t.parentNode.name in this.submitted&&this.element(t.parentNode)},highlight:function(e,i,s){"radio"===e.type?this.findByName(e.name).addClass(i).removeClass(s):t(e).addClass(i).removeClass(s)},unhighlight:function(e,i,s){"radio"===e.type?this.findByName(e.name).removeClass(i).addClass(s):t(e).removeClass(i).addClass(s)}},setDefaults:function(e){t.extend(t.tc_validator.defaults,e)},messages:{required:"This field is required.",remote:"Please fix this field.",email:"Please enter a valid email address.",url:"Please enter a valid URL.",date:"Please enter a valid date.",dateISO:"Please enter a valid date ( ISO ).",number:"Please enter a valid number.",digits:"Please enter only digits.",equalTo:"Please enter the same value again.",maxlength:t.tc_validator.format("Please enter no more than {0} characters."),minlength:t.tc_validator.format("Please enter at least {0} characters."),rangelength:t.tc_validator.format("Please enter a value between {0} and {1} characters long."),range:t.tc_validator.format("Please enter a value between {0} and {1}."),max:t.tc_validator.format("Please enter a value less than or equal to {0}."),min:t.tc_validator.format("Please enter a value greater than or equal to {0}."),step:t.tc_validator.format("Please enter a multiple of {0}.")},autoCreateRanges:!1,prototype:{init:function(){function e(e){var i=t.data(this.form,"tc_validator"),s="on"+e.type.replace(/^tc_validate/,""),r=i.settings;r[s]&&!t(this).is(r.ignore)&&r[s].call(i,this,e)}this.labelContainer=t(this.settings.errorLabelContainer),this.errorContext=this.labelContainer.length&&this.labelContainer||t(this.currentForm),this.containers=t(this.settings.errorContainer).add(this.settings.errorLabelContainer),this.submitted={},this.valueCache={},this.pendingRequest=0,this.pending={},this.invalid={},this.reset();var i,s=this.groups={};t.each(this.settings.groups,function(e,i){"string"==typeof i&&(i=i.split(/\s/)),t.each(i,function(t,i){s[i]=e})}),i=this.settings.rules,t.each(i,function(e,s){i[e]=t.tc_validator.normalizeRule(s)}),t(this.currentForm).on("focusin.tc_validate focusout.tc_validate keyup.tc_validate",":text, [type='password'], [type='file'], select, textarea, [type='number'], [type='search'], [type='tel'], [type='url'], [type='email'], [type='datetime'], [type='date'], [type='month'], [type='week'], [type='time'], [type='datetime-local'], [type='range'], [type='color'], [type='radio'], [type='checkbox'], [contenteditable]",e).on("click.tc_validate","select, option, [type='radio'], [type='checkbox']",e),this.settings.invalidHandler&&t(this.currentForm).on("invalid-form.tc_validate",this.settings.invalidHandler),t(this.currentForm).find("[required], [data-rule-required], .required").attr("aria-required","true")},form:function(){return this.checkForm(),t.extend(this.submitted,this.errorMap),this.invalid=t.extend({},this.errorMap),this.tc_valid()||t(this.currentForm).triggerHandler("invalid-form",[this]),this.showErrors(),this.tc_valid()},checkForm:function(){this.prepareForm();for(var t=0,e=this.currentElements=this.elements();e[t];t++)this.check(e[t]);return this.tc_valid()},element:function(e){var i,s,r=this.clean(e),n=this.validationTargetFor(r),a=this,o=!0;return void 0===n?delete this.invalid[r.name]:(this.prepareElement(n),this.currentElements=t(n),s=this.groups[n.name],s&&t.each(this.groups,function(t,e){e===s&&t!==n.name&&(r=a.validationTargetFor(a.clean(a.findByName(t))),r&&r.name in a.invalid&&(a.currentElements.push(r),o=o&&a.check(r)))}),i=this.check(n)!==!1,o=o&&i,i?this.invalid[n.name]=!1:this.invalid[n.name]=!0,this.numberOfInvalids()||(this.toHide=this.toHide.add(this.containers)),this.showErrors(),t(e).attr("aria-invalid",!i)),o},showErrors:function(e){if(e){var i=this;t.extend(this.errorMap,e),this.errorList=t.map(this.errorMap,function(t,e){return{message:t,element:i.findByName(e)[0]}}),this.successList=t.grep(this.successList,function(t){return!(t.name in e)})}this.settings.showErrors?this.settings.showErrors.call(this,this.errorMap,this.errorList):this.defaultShowErrors()},resetForm:function(){t.fn.resetForm&&t(this.currentForm).resetForm(),this.invalid={},this.submitted={},this.prepareForm(),this.hideErrors();var e=this.elements().removeData("previousValue").removeAttr("aria-invalid");this.resetElements(e)},resetElements:function(t){var e;if(this.settings.unhighlight)for(e=0;t[e];e++)this.settings.unhighlight.call(this,t[e],this.settings.errorClass,""),this.findByName(t[e].name).removeClass(this.settings.validClass);else t.removeClass(this.settings.errorClass).removeClass(this.settings.validClass)},numberOfInvalids:function(){return this.objectLength(this.invalid)},objectLength:function(t){var e,i=0;for(e in t)t[e]&&i++;return i},hideErrors:function(){this.hideThese(this.toHide)},hideThese:function(t){t.not(this.containers).text(""),this.addWrapper(t).hide()},tc_valid:function(){return 0===this.size()},size:function(){return this.errorList.length},focusInvalid:function(){if(this.settings.focusInvalid)try{t(this.findLastActive()||this.errorList.length&&this.errorList[0].element||[]).filter(":visible").focus().trigger("focusin")}catch(e){}},findLastActive:function(){var e=this.lastActive;return e&&1===t.grep(this.errorList,function(t){return t.element.name===e.name}).length&&e},elements:function(){var e=this,i={};return t(this.currentForm).find("input, select, textarea, [contenteditable]").not(":submit, :reset, :image, :disabled").not(this.settings.ignore).filter(function(){var s=this.name||t(this).attr("name");return!s&&e.settings.debug&&window.console&&console.error("%o has no name assigned",this),this.hasAttribute("contenteditable")&&(this.form=t(this).closest("form")[0]),s in i||!e.objectLength(t(this).tc_rules())?!1:(i[s]=!0,!0)})},clean:function(e){return t(e)[0]},errors:function(){var e=this.settings.errorClass.split(" ").join(".");return t(this.settings.errorElement+"."+e,this.errorContext)},resetInternals:function(){this.successList=[],this.errorList=[],this.errorMap={},this.toShow=t([]),this.toHide=t([])},reset:function(){this.resetInternals(),this.currentElements=t([])},prepareForm:function(){this.reset(),this.toHide=this.errors().add(this.containers)},prepareElement:function(t){this.reset(),this.toHide=this.errorsFor(t)},elementValue:function(e){var i,s,r=t(e),n=e.type;return"radio"===n||"checkbox"===n?this.findByName(e.name).filter(":checked").val():"number"===n&&"undefined"!=typeof e.validity?e.validity.badInput?"NaN":r.val():(i=e.hasAttribute("contenteditable")?r.text():r.val(),"file"===n?"C:\\fakepath\\"===i.substr(0,12)?i.substr(12):(s=i.lastIndexOf("/"),s>=0?i.substr(s+1):(s=i.lastIndexOf("\\"),s>=0?i.substr(s+1):i)):"string"==typeof i?i.replace(/\r/g,""):i)},check:function(e){e=this.validationTargetFor(this.clean(e));var i,s,r,n=t(e).tc_rules(),a=t.map(n,function(t,e){return e}).length,o=!1,l=this.elementValue(e);if("function"==typeof n.normalizer){if(l=n.normalizer.call(e,l),"string"!=typeof l)throw new TypeError("The normalizer should return a string value.");delete n.normalizer}for(s in n){r={method:s,parameters:n[s]};try{if(i=t.tc_validator.methods[s].call(this,l,e,r.parameters),"dependency-mismatch"===i&&1===a){o=!0;continue}if(o=!1,"pending"===i)return void(this.toHide=this.toHide.not(this.errorsFor(e)));if(!i)return this.formatAndAdd(e,r),!1}catch(h){throw this.settings.debug&&window.console&&console.log("Exception occurred when checking element "+e.id+", check the '"+r.method+"' method.",h),h instanceof TypeError&&(h.message+=".  Exception occurred when checking element "+e.id+", check the '"+r.method+"' method."),h}}return o?void 0:(this.objectLength(n)&&this.successList.push(e),!0)},customDataMessage:function(e,i){return t(e).data("msg"+i.charAt(0).toUpperCase()+i.substring(1).toLowerCase())||t(e).data("msg")},customMessage:function(t,e){var i=this.settings.messages[t];return i&&(i.constructor===String?i:i[e])},findDefined:function(){for(var t=0;t<arguments.length;t++)if(void 0!==arguments[t])return arguments[t];return void 0},defaultMessage:function(e,i){var s=this.findDefined(this.customMessage(e.name,i.method),this.customDataMessage(e,i.method),!this.settings.ignoreTitle&&e.title||void 0,t.tc_validator.messages[i.method],"<strong>Warning: No message defined for "+e.name+"</strong>"),r=/\$?\{(\d+)\}/g;return"function"==typeof s?s=s.call(this,i.parameters,e):r.test(s)&&(s=t.tc_validator.format(s.replace(r,"{$1}"),i.parameters)),s},formatAndAdd:function(t,e){var i=this.defaultMessage(t,e);this.errorList.push({message:i,element:t,method:e.method}),this.errorMap[t.name]=i,this.submitted[t.name]=i},addWrapper:function(t){return this.settings.wrapper&&(t=t.add(t.parent(this.settings.wrapper))),t},defaultShowErrors:function(){var t,e,i;for(t=0;this.errorList[t];t++)i=this.errorList[t],this.settings.highlight&&this.settings.highlight.call(this,i.element,this.settings.errorClass,this.settings.validClass),this.showLabel(i.element,i.message);if(this.errorList.length&&(this.toShow=this.toShow.add(this.containers)),this.settings.success)for(t=0;this.successList[t];t++)this.showLabel(this.successList[t]);if(this.settings.unhighlight)for(t=0,e=this.validElements();e[t];t++)this.settings.unhighlight.call(this,e[t],this.settings.errorClass,this.settings.validClass);this.toHide=this.toHide.not(this.toShow),this.hideErrors(),this.addWrapper(this.toShow).show()},validElements:function(){return this.currentElements.not(this.invalidElements())},invalidElements:function(){return t(this.errorList).map(function(){return this.element})},showLabel:function(e,i){var s,r,n,a,o=this.errorsFor(e),l=this.idOrName(e),h=t(e).attr("aria-describedby");o.length?(o.removeClass(this.settings.validClass).addClass(this.settings.errorClass),o.html(i)):(o=t("<"+this.settings.errorElement+">").attr("id",l+"-error").addClass(this.settings.errorClass).html(i||""),s=o,this.settings.wrapper&&(s=o.hide().show().wrap("<"+this.settings.wrapper+"/>").parent()),this.labelContainer.length?this.labelContainer.append(s):this.settings.errorPlacement?this.settings.errorPlacement(s,t(e)):s.insertAfter(e),o.is("label")?o.attr("for",l):0===o.parents("label[for='"+this.escapeCssMeta(l)+"']").length&&(n=o.attr("id"),h?h.match(new RegExp("\\b"+this.escapeCssMeta(n)+"\\b"))||(h+=" "+n):h=n,t(e).attr("aria-describedby",h),r=this.groups[e.name],r&&(a=this,t.each(a.groups,function(e,i){i===r&&t("[name='"+a.escapeCssMeta(e)+"']",a.currentForm).attr("aria-describedby",o.attr("id"))})))),!i&&this.settings.success&&(o.text(""),"string"==typeof this.settings.success?o.addClass(this.settings.success):this.settings.success(o,e)),this.toShow=this.toShow.add(o)},errorsFor:function(e){var i=this.escapeCssMeta(this.idOrName(e)),s=t(e).attr("aria-describedby"),r="label[for='"+i+"'], label[for='"+i+"'] *";return s&&(r=r+", #"+this.escapeCssMeta(s).replace(/\s+/g,", #")),this.errors().filter(r)},escapeCssMeta:function(t){return t.replace(/([\\!"#$%&'()*+,.\/:;<=>?@\[\]^`{|}~])/g,"\\$1")},idOrName:function(t){return this.groups[t.name]||(this.checkable(t)?t.name:t.id||t.name)},validationTargetFor:function(e){return this.checkable(e)&&(e=this.findByName(e.name)),t(e).not(this.settings.ignore)[0]},checkable:function(t){return/radio|checkbox/i.test(t.type)},findByName:function(e){return t(this.currentForm).find("[name='"+this.escapeCssMeta(e)+"']")},getLength:function(e,i){switch(i.nodeName.toLowerCase()){case"select":return t("option:selected",i).length;case"input":if(this.checkable(i))return this.findByName(i.name).filter(":checked").length}return e.length},depend:function(t,e){return this.dependTypes[typeof t]?this.dependTypes[typeof t](t,e):!0},dependTypes:{"boolean":function(t){return t},string:function(e,i){return!!t(e,i.form).length},"function":function(t,e){return t(e)}},optional:function(e){var i=this.elementValue(e);return!t.tc_validator.methods.required.call(this,i,e)&&"dependency-mismatch"},startRequest:function(e){this.pending[e.name]||(this.pendingRequest++,t(e).addClass(this.settings.pendingClass),this.pending[e.name]=!0)},stopRequest:function(e,i){this.pendingRequest--,this.pendingRequest<0&&(this.pendingRequest=0),delete this.pending[e.name],t(e).removeClass(this.settings.pendingClass),i&&0===this.pendingRequest&&this.formSubmitted&&this.form()?(t(this.currentForm).submit(),this.formSubmitted=!1):!i&&0===this.pendingRequest&&this.formSubmitted&&(t(this.currentForm).triggerHandler("invalid-form",[this]),this.formSubmitted=!1)},previousValue:function(e,i){return t.data(e,"previousValue")||t.data(e,"previousValue",{old:null,valid:!0,message:this.defaultMessage(e,{method:i})})},destroy:function(){this.resetForm(),t(this.currentForm).off(".tc_validate").removeData("tc_validator").find(".tc_validate-equalTo-blur").off(".tc_validate-equalTo").removeClass("tc_validate-equalTo-blur")}},classRuleSettings:{required:{required:!0},email:{email:!0},url:{url:!0},date:{date:!0},dateISO:{dateISO:!0},number:{number:!0},digits:{digits:!0},creditcard:{creditcard:!0}},addClassRules:function(e,i){e.constructor===String?this.classRuleSettings[e]=i:t.extend(this.classRuleSettings,e)},classRules:function(e){var i={},s=t(e).attr("class");return s&&t.each(s.split(" "),function(){this in t.tc_validator.classRuleSettings&&t.extend(i,t.tc_validator.classRuleSettings[this])}),i},normalizeAttributeRule:function(t,e,i,s){/min|max|step/.test(i)&&(null===e||/number|range|text/.test(e))&&(s=Number(s),isNaN(s)&&(s=void 0)),s||0===s?t[i]=s:e===i&&"range"!==e&&(t[i]=!0)},attributeRules:function(e){var i,s,r={},n=t(e),a=e.getAttribute("type");for(i in t.tc_validator.methods)"required"===i?(s=e.getAttribute(i),""===s&&(s=!0),s=!!s):s=n.attr(i),this.normalizeAttributeRule(r,a,i,s);return r.maxlength&&/-1|2147483647|524288/.test(r.maxlength)&&delete r.maxlength,r},dataRules:function(e){var i,s,r={},n=t(e),a=e.getAttribute("type");for(i in t.tc_validator.methods)s=n.data("rule"+i.charAt(0).toUpperCase()+i.substring(1).toLowerCase()),this.normalizeAttributeRule(r,a,i,s);return r},staticRules:function(e){var i={},s=t.data(e.form,"tc_validator");return s.settings.rules&&(i=t.tc_validator.normalizeRule(s.settings.rules[e.name])||{}),i},normalizeRules:function(e,i){return t.each(e,function(s,r){if(r===!1)return void delete e[s];if(r.param||r.depends){var n=!0;switch(typeof r.depends){case"string":n=!!t(r.depends,i.form).length;break;case"function":n=r.depends.call(i,i)}n?e[s]=void 0!==r.param?r.param:!0:(t.data(i.form,"tc_validator").resetElements(t(i)),delete e[s])}}),t.each(e,function(s,r){e[s]=t.isFunction(r)&&"normalizer"!==s?r(i):r}),t.each(["minlength","maxlength"],function(){e[this]&&(e[this]=Number(e[this]))}),t.each(["rangelength","range"],function(){var i;e[this]&&(t.isArray(e[this])?e[this]=[Number(e[this][0]),Number(e[this][1])]:"string"==typeof e[this]&&(i=e[this].replace(/[\[\]]/g,"").split(/[\s,]+/),e[this]=[Number(i[0]),Number(i[1])]))}),t.tc_validator.autoCreateRanges&&(null!=e.min&&null!=e.max&&(e.range=[e.min,e.max],delete e.min,delete e.max),null!=e.minlength&&null!=e.maxlength&&(e.rangelength=[e.minlength,e.maxlength],delete e.minlength,delete e.maxlength)),e},normalizeRule:function(e){if("string"==typeof e){var i={};t.each(e.split(/\s/),function(){i[this]=!0}),e=i}return e},addMethod:function(e,i,s){t.tc_validator.methods[e]=i,t.tc_validator.messages[e]=void 0!==s?s:t.tc_validator.messages[e],i.length<3&&t.tc_validator.addClassRules(e,t.tc_validator.normalizeRule(e))},methods:{required:function(e,i,s){if(!this.depend(s,i))return"dependency-mismatch";if("select"===i.nodeName.toLowerCase()){var r=t(i).val();return r&&r.length>0}return this.checkable(i)?this.getLength(e,i)>0:e.length>0},email:function(t,e){return this.optional(e)||/^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(t)},url:function(t,e){return this.optional(e)||/^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})).?)(?::\d{2,5})?(?:[\/?#]\S*)?$/i.test(t)},date:function(t,e){return this.optional(e)||!/Invalid|NaN/.test(new Date(t).toString())},dateISO:function(t,e){return this.optional(e)||/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/.test(t)},number:function(t,e){return this.optional(e)||/^(?:-?\d+|-?\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(t)},digits:function(t,e){return this.optional(e)||/^\d+$/.test(t)},minlength:function(e,i,s){var r=t.isArray(e)?e.length:this.getLength(e,i);return this.optional(i)||r>=s},maxlength:function(e,i,s){var r=t.isArray(e)?e.length:this.getLength(e,i);return this.optional(i)||s>=r},rangelength:function(e,i,s){var r=t.isArray(e)?e.length:this.getLength(e,i);return this.optional(i)||r>=s[0]&&r<=s[1]},min:function(t,e,i){return this.optional(e)||t>=i},max:function(t,e,i){return this.optional(e)||i>=t},range:function(t,e,i){return this.optional(e)||t>=i[0]&&t<=i[1]},step:function(e,i,s){var r=t(i).attr("type"),n="Step attribute on input type "+r+" is not supported.",a=["text","number","range"],o=new RegExp("\\b"+r+"\\b"),l=r&&!o.test(a.join());if(l)throw new Error(n);return this.optional(i)||e%s===0},equalTo:function(e,i,s){var r=t(s);return this.settings.onfocusout&&r.not(".tc_validate-equalTo-blur").length&&r.addClass("tc_validate-equalTo-blur").on("blur.tc_validate-equalTo",function(){t(i).tc_valid()}),e===r.val()},remote:function(e,i,s,r){if(this.optional(i))return"dependency-mismatch";r="string"==typeof r&&r||"remote";var n,a,o,l=this.previousValue(i,r);return this.settings.messages[i.name]||(this.settings.messages[i.name]={}),l.originalMessage=l.originalMessage||this.settings.messages[i.name][r],this.settings.messages[i.name][r]=l.message,s="string"==typeof s&&{url:s}||s,o=t.param(t.extend({data:e},s.data)),l.old===o?l.valid:(l.old=o,n=this,this.startRequest(i),a={},a[i.name]=e,t.ajax(t.extend(!0,{mode:"abort",port:"tc_validate"+i.name,dataType:"json",data:a,context:n.currentForm,success:function(t){var s,a,o,h=t===!0||"true"===t;n.settings.messages[i.name][r]=l.originalMessage,h?(o=n.formSubmitted,n.resetInternals(),n.toHide=n.errorsFor(i),n.formSubmitted=o,n.successList.push(i),n.invalid[i.name]=!1,n.showErrors()):(s={},a=t||n.defaultMessage(i,{method:r,parameters:e}),s[i.name]=l.message=a,n.invalid[i.name]=!0,n.showErrors(s)),l.valid=h,n.stopRequest(i,h)}},s)),"pending")}}});var e,i={};t.ajaxPrefilter?t.ajaxPrefilter(function(t,e,s){var r=t.port;"abort"===t.mode&&(i[r]&&i[r].abort(),i[r]=s)}):(e=t.ajax,t.ajax=function(s){var r=("mode"in s?s:t.ajaxSettings).mode,n=("port"in s?s:t.ajaxSettings).port;return"abort"===r?(i[n]&&i[n].abort(),i[n]=e.apply(this,arguments),i[n]):e.apply(this,arguments)})});

/*!
 * jQuery pagination plugin v1.2.6
 * http://esimakin.github.io/twbs-pagination/
 *
 * Copyright 2014, Eugene Simakin
 * Released under Apache 2.0 license
 * http://apache.org/licenses/LICENSE-2.0.html
 *
 * Modified version
 */
;!function(t){"use strict";var s=function(s,i){if(this.$element=t(s),this.options=t.extend({},t.fn.tcPagination.defaults,i),this.options.startPage<1||this.options.startPage>this.options.totalPages)return void console.log("Wrong startPage");if(this.options.totalPages=parseInt(this.options.totalPages),isNaN(this.options.totalPages))return void console.log("Wrong totalPages");if(this.options.visiblePages=parseInt(this.options.visiblePages),isNaN(this.options.visiblePages))return void console.log("Wrong visiblePages");this.options.totalPages<this.options.visiblePages&&(this.options.visiblePages=this.options.totalPages),this.options.onPageClick instanceof Function&&this.$element.first().on("page",this.options.onPageClick);var e="function"==typeof this.$element.prop?this.$element.prop("tagName"):this.$element.attr("tagName");return"UL"===e?this.$listContainer=this.$element:this.$listContainer=t("<ul></ul>"),"UL"!==e&&this.$element.append(this.$listContainer),this.render(this.getPages(this.options.startPage)),this.setupEvents(),this.options.initiateStartPageClick&&this.$element.trigger("page",this.options.startPage),this};s.prototype={constructor:s,destroy:function(){return this.$element.empty(),this.$element.removeData("tc-pagination"),this.$element.off("page"),this},show:function(t){return 1>t||t>this.options.totalPages?void console.log("Wrong page"):(this.render(this.getPages(t)),this.setupEvents(),this.$element.trigger("page",t),this)},buildListItems:function(s){var i=t();if(this.options.first&&(i=i.add(this.buildItem("first",1))),this.options.prev){var e=s.currentPage>1?s.currentPage-1:this.options.loop?this.options.totalPages:1;i=i.add(this.buildItem("prev",e))}for(var a=0;a<s.numeric.length;a++)i=i.add(this.buildItem("page",s.numeric[a]));if(this.options.next){var o=s.currentPage<this.options.totalPages?s.currentPage+1:this.options.loop?1:this.options.totalPages;i=i.add(this.buildItem("next",o))}return this.options.last&&(i=i.add(this.buildItem("last",this.options.totalPages))),i},buildItem:function(s,i){var e=t("<li></li>"),a=t("<a></a>"),o=null;switch(s){case"page":o=i,e.addClass(this.options.pageClass);break;case"first":o=this.options.first,e.addClass(this.options.firstClass);break;case"prev":o=this.options.prev,e.addClass(this.options.prevClass);break;case"next":o=this.options.next,e.addClass(this.options.nextClass);break;case"last":o=this.options.last,e.addClass(this.options.lastClass)}return e.data("page",i),e.data("page-type",s),e.append(a.attr("href","#").html(o)),e},getPages:function(t){var s=[],i=Math.floor(this.options.visiblePages/2),e=t-i+1-this.options.visiblePages%2,a=t+i;0>=e&&(e=1,a=this.options.visiblePages),a>this.options.totalPages&&(e=this.options.totalPages-this.options.visiblePages+1,a=this.options.totalPages);for(var o=e;a>=o;)s.push(o),o++;return{currentPage:t,numeric:s}},render:function(s){var i=this;this.$listContainer.children().remove(),this.$listContainer.append(this.buildListItems(s)),this.$listContainer.children().each(function(){var e=t(this),a=e.data("page-type");switch(a){case"page":e.data("page")===s.currentPage&&e.addClass(i.options.activeClass);break;case"first":e.toggleClass(i.options.disabledClass,1===s.currentPage);break;case"last":e.toggleClass(i.options.disabledClass,s.currentPage===i.options.totalPages);break;case"prev":e.toggleClass(i.options.disabledClass,!i.options.loop&&1===s.currentPage);break;case"next":e.toggleClass(i.options.disabledClass,!i.options.loop&&s.currentPage===i.options.totalPages)}})},setupEvents:function(){var s=this;this.$listContainer.find("li").each(function(){var i=t(this);return i.off(),i.hasClass(s.options.disabledClass)||i.hasClass(s.options.activeClass)?void i.click(function(t){t.preventDefault()}):void i.click(function(t){t.preventDefault(),s.show(parseInt(i.data("page"),10))})})}},t.fn.tcPagination=function(i){var e,a=Array.prototype.slice.call(arguments,1),o=t(this),n=o.data("tc-pagination"),l="object"==typeof i&&i;return n||o.data("tc-pagination",n=new s(this,l)),"string"==typeof i&&(e=n[i].apply(n,a)),void 0===e?o:e},t.fn.tcPagination.defaults={totalPages:0,startPage:1,visiblePages:5,initiateStartPageClick:!0,loop:!1,onPageClick:null,first:'<i class="tcfa tcfa-angle-double-left"></i>',prev:'<i class="tcfa tcfa-angle-left"></i>',next:'<i class="tcfa tcfa-angle-right"></i>',last:'<i class="tcfa tcfa-angle-double-right"></i>',nextClass:"next",prevClass:"prev",lastClass:"last",firstClass:"first",pageClass:"page",activeClass:"active",disabledClass:"disabled"},t.fn.tcPagination.Constructor=s}(jQuery);

/*
 * Toastr
 * Copyright 2012-2015
 * Authors: John Papa, Hans FjΓ¤llemark, and Tim Ferrell.
 * All Rights Reserved.
 * Use, reproduction, distribution, and modification of this code is subject to the terms and
 * conditions of the MIT license, available at http://www.opensource.org/licenses/mit-license.php
 *
 * ARIA Support: Greta Krafsig
 *
 * Project: https://github.com/CodeSeven/toastr
 */
!function(e){e(["jquery"],function(e){return function(){function t(e,t,n){return g({type:O.error,iconClass:m().iconClasses.error,message:e,optionsOverride:n,title:t})}function n(t,n){return t||(t=m()),v=e("#"+t.containerId),v.length?v:(n&&(v=d(t)),v)}function o(e,t,n){return g({type:O.info,iconClass:m().iconClasses.info,message:e,optionsOverride:n,title:t})}function s(e){C=e}function i(e,t,n){return g({type:O.success,iconClass:m().iconClasses.success,message:e,optionsOverride:n,title:t})}function a(e,t,n){return g({type:O.warning,iconClass:m().iconClasses.warning,message:e,optionsOverride:n,title:t})}function r(e,t){var o=m();v||n(o),u(e,o,t)||l(o)}function c(t){var o=m();return v||n(o),t&&0===e(":focus",t).length?void h(t):void(v.children().length&&v.remove())}function l(t){for(var n=v.children(),o=n.length-1;o>=0;o--)u(e(n[o]),t)}function u(t,n,o){var s=!(!o||!o.force)&&o.force;return!(!t||!s&&0!==e(":focus",t).length)&&(t[n.hideMethod]({duration:n.hideDuration,easing:n.hideEasing,complete:function(){h(t)}}),!0)}function d(t){return v=e("<div/>").attr("id",t.containerId).addClass(t.positionClass),v.appendTo(e(t.target)),v}function p(){return{tapToDismiss:!0,toastClass:"toast",containerId:"toast-container",debug:!1,showMethod:"fadeIn",showDuration:300,showEasing:"swing",onShown:void 0,hideMethod:"fadeOut",hideDuration:1e3,hideEasing:"swing",onHidden:void 0,closeMethod:!1,closeDuration:!1,closeEasing:!1,closeOnHover:!0,extendedTimeOut:1e3,iconClasses:{error:"toast-error",info:"toast-info",success:"toast-success",warning:"toast-warning"},iconClass:"toast-info",positionClass:"toast-top-right",timeOut:5e3,titleClass:"toast-title",messageClass:"toast-message",escapeHtml:!1,target:"body",closeHtml:'<button type="button">&times;</button>',closeClass:"toast-close-button",newestOnTop:!0,preventDuplicates:!1,progressBar:!1,progressClass:"toast-progress",rtl:!1}}function f(e){C&&C(e)}function g(t){function o(e){return null==e&&(e=""),e.replace(/&/g,"&amp;").replace(/"/g,"&quot;").replace(/'/g,"&#39;").replace(/</g,"&lt;").replace(/>/g,"&gt;")}function s(){c(),u(),d(),p(),g(),C(),l(),i()}function i(){var e="";switch(t.iconClass){case"toast-success":case"toast-info":e="polite";break;default:e="assertive"}I.attr("aria-live",e)}function a(){E.closeOnHover&&I.hover(H,D),!E.onclick&&E.tapToDismiss&&I.click(b),E.closeButton&&j&&j.click(function(e){e.stopPropagation?e.stopPropagation():void 0!==e.cancelBubble&&e.cancelBubble!==!0&&(e.cancelBubble=!0),E.onCloseClick&&E.onCloseClick(e),b(!0)}),E.onclick&&I.click(function(e){E.onclick(e),b()})}function r(){I.hide(),I[E.showMethod]({duration:E.showDuration,easing:E.showEasing,complete:E.onShown}),E.timeOut>0&&(k=setTimeout(b,E.timeOut),F.maxHideTime=parseFloat(E.timeOut),F.hideEta=(new Date).getTime()+F.maxHideTime,E.progressBar&&(F.intervalId=setInterval(x,10)))}function c(){t.iconClass&&I.addClass(E.toastClass).addClass(y)}function l(){E.newestOnTop?v.prepend(I):v.append(I)}function u(){if(t.title){var e=t.title;E.escapeHtml&&(e=o(t.title)),M.append(e).addClass(E.titleClass),I.append(M)}}function d(){if(t.message){var e=t.message;E.escapeHtml&&(e=o(t.message)),B.append(e).addClass(E.messageClass),I.append(B)}}function p(){E.closeButton&&(j.addClass(E.closeClass).attr("role","button"),I.prepend(j))}function g(){E.progressBar&&(q.addClass(E.progressClass),I.prepend(q))}function C(){E.rtl&&I.addClass("rtl")}function O(e,t){if(e.preventDuplicates){if(t.message===w)return!0;w=t.message}return!1}function b(t){var n=t&&E.closeMethod!==!1?E.closeMethod:E.hideMethod,o=t&&E.closeDuration!==!1?E.closeDuration:E.hideDuration,s=t&&E.closeEasing!==!1?E.closeEasing:E.hideEasing;if(!e(":focus",I).length||t)return clearTimeout(F.intervalId),I[n]({duration:o,easing:s,complete:function(){h(I),clearTimeout(k),E.onHidden&&"hidden"!==P.state&&E.onHidden(),P.state="hidden",P.endTime=new Date,f(P)}})}function D(){(E.timeOut>0||E.extendedTimeOut>0)&&(k=setTimeout(b,E.extendedTimeOut),F.maxHideTime=parseFloat(E.extendedTimeOut),F.hideEta=(new Date).getTime()+F.maxHideTime)}function H(){clearTimeout(k),F.hideEta=0,I.stop(!0,!0)[E.showMethod]({duration:E.showDuration,easing:E.showEasing})}function x(){var e=(F.hideEta-(new Date).getTime())/F.maxHideTime*100;q.width(e+"%")}var E=m(),y=t.iconClass||E.iconClass;if("undefined"!=typeof t.optionsOverride&&(E=e.extend(E,t.optionsOverride),y=t.optionsOverride.iconClass||y),!O(E,t)){T++,v=n(E,!0);var k=null,I=e("<div/>"),M=e("<div/>"),B=e("<div/>"),q=e("<div/>"),j=e(E.closeHtml),F={intervalId:null,hideEta:null,maxHideTime:null},P={toastId:T,state:"visible",startTime:new Date,options:E,map:t};return s(),r(),a(),f(P),E.debug&&console&&console.log(P),I}}function m(){return e.extend({},p(),b.options)}function h(e){v||(v=n()),e.is(":visible")||(e.remove(),e=null,0===v.children().length&&(v.remove(),w=void 0))}var v,C,w,T=0,O={error:"error",info:"info",success:"success",warning:"warning"},b={clear:r,remove:c,error:t,getContainer:n,info:o,options:{},subscribe:s,success:i,version:"2.1.3",warning:a};return b}()})}("function"==typeof define&&define.amd?define:function(e,t){"undefined"!=typeof module&&module.exports?module.exports=t(require("jquery")):window.toastr=t(window.jQuery)});


/** https://github.com/ruidfigueiredo/findHandlersJS **/
var findEventHandlers = function ( eventType, jqSelector ) {
	var results = [];
	var $ = jQuery;// to avoid conflict between others frameworks like Mootools

	var arrayIntersection = function ( array1, array2 ) {
		return $( array1 ).filter( function ( index, element ) {
			return $.inArray( element, $( array2 ) ) !== - 1;
		} );
	};

	var haveCommonElements = function ( array1, array2 ) {
		return arrayIntersection( array1, array2 ).length !== 0;
	};


	var addEventHandlerInfo = function ( element, event, $elementsCovered ) {
		var extendedEvent = event;
		if ( $elementsCovered !== void 0 && $elementsCovered !== null ) {
			$.extend( extendedEvent, { targets: $elementsCovered.toArray() } );
		}
		var eventInfo;
		var eventsInfo = $.grep( results, function ( evInfo, index ) {
			return element === evInfo.element;
		} );

		if ( eventsInfo.length === 0 ) {
			eventInfo = {
				element: element,
				events: [ extendedEvent ]
			};
			results.push( eventInfo );
		} else {
			eventInfo = eventsInfo[ 0 ];
			eventInfo.events.push( extendedEvent );
		}
	};


	var $elementsToWatch = $( jqSelector );
	if ( jqSelector === "*" )//* does not include document and we might be interested in handlers registered there
		$elementsToWatch = $elementsToWatch.add( document );
	var $allElements = $( "*" ).add( document );

	$.each( $allElements, function ( elementIndex, element ) {
		var allElementEvents = $._data( element, "events" );
		if ( allElementEvents !== void 0 && allElementEvents[ eventType ] !== void 0 ) {
			var eventContainer = allElementEvents[ eventType ];
			$.each( eventContainer, function ( eventIndex, event ) {
				var isDelegateEvent = event.selector !== void 0 && event.selector !== null;
				var $elementsCovered;
				if ( isDelegateEvent ) {
					$elementsCovered = $( event.selector, element ); //only look at children of the element, since those are the only ones the handler covers
				} else {
					$elementsCovered = $( element ); //just itself
				}
				if ( haveCommonElements( $elementsCovered, $elementsToWatch ) ) {
					addEventHandlerInfo( element, event, $elementsCovered );
				}
			} );
		}
	} );

	return results;
};

/*
 * TM scripts
 */

    function getSystemDecimalSeparator() {
        var n = 1.1;
        n = /^1(.+)1$/.exec( n.toLocaleString() )[ 1 ];
        return n;
    }

    //http://locutus.io/php/math/round/
    function tc_round( value, precision, mode ) {

        var m, f, isHalf, sgn; // helper variables
        // making sure precision is integer
        precision |= 0;
        m = Math.pow( 10, precision );
        value *= m;
        // sign of the number
        sgn = (value > 0) | - (value < 0);
        isHalf = value % 1 === 0.5 * sgn;
        f = Math.floor( value );

        if ( isHalf ) {
            switch ( mode ) {
                case 'PHP_ROUND_HALF_DOWN':
                    // rounds .5 toward zero
                    value = f + (sgn < 0);
                    break;
                case 'PHP_ROUND_HALF_EVEN':
                    // rouds .5 towards the next even integer
                    value = f + (f % 2 * sgn);
                    break;
                case 'PHP_ROUND_HALF_ODD':
                    // rounds .5 towards the next odd integer
                    value = f + ! (f % 2);
                    break;
                default:
                    // rounds .5 away from zero
                    value = f + (sgn > 0);
            }
        }

        return (isHalf ? value : Math.round( value )) / m;
    }


    String.prototype.tmtoFloat = function () {
        var a = this,//a=accounting.unformat(this,local_input_decimal_separator),
            n = parseFloat( a );
        if ( isNaN( n ) ) {
            return a;
        }
        return n;
    };
    Number.prototype.tmtoFloat = function () {
            var a = this,//a=accounting.unformat(this,local_input_decimal_separator),
            n = parseFloat( a );
        if ( isNaN( n ) ) {
            return a;
        }
        return n;
    };

    String.prototype.tm_isNumeric = function () {
        return ! isNaN( parseFloat( this ) ) && isFinite( this );
    };
    
(function ( $ ) {
	'use strict';


	// The deferred used on DOM ready
	var readyList;

	jQuery.fn.tcready = function ( fn ) {

		// Add the callback
		jQuery.tcready.promise().done( fn );

		return this;
	};

	jQuery.extend( {

		// Is the DOM ready to be used? Set to true once it occurs.
		tcisReady: false,

		// A counter to track how many items to wait for before
		// the ready event fires. See #6781
		tcreadyWait: 1,

		// Hold (or release) the ready event
		tcholdReady: function ( hold ) {
			if ( hold ) {
				jQuery.tcreadyWait ++;
			} else {
				jQuery.tcready( true );
			}
		},

		// Handle when the DOM is ready
		tcready: function ( wait ) {

			// Abort if there are pending holds or we're already ready
			if ( wait === true ? -- jQuery.tcreadyWait : jQuery.tcisReady ) {
				return;
			}

			// Remember that the DOM is ready
			jQuery.tcisReady = true;

			// If a normal DOM Ready event fired, decrement, and wait if need be
			if ( wait !== true && -- jQuery.tcreadyWait > 0 ) {
				return;
			}

			// If there are functions bound, to execute
			readyList.resolveWith( document, [ jQuery ] );

			// Trigger any bound ready events
			if ( jQuery.fn.triggerHandler ) {
				jQuery( document ).triggerHandler( "tcready" );
				jQuery( document ).off( "tcready" );
			}
		}
	} );

	/**
	 * Clean-up method for dom ready events
	 */
	function detach() {
		if ( document.addEventListener ) {
			document.removeEventListener( "DOMContentLoaded", completed );
			window.removeEventListener( "load", completed );

		} else {
			document.detachEvent( "onreadystatechange", completed );
			window.detachEvent( "onload", completed );
		}
	}

	/**
	 * The ready event handler and self cleanup method
	 */
	function completed() {

		// readyState === "complete" is good enough for us to call the dom ready in oldIE
		if ( document.addEventListener ||
			window.event.type === "load" ||
			document.readyState === "complete" ) {

			detach();
			jQuery.tcready();
		}
	}

	jQuery.tcready.promise = function ( obj ) {
		if ( ! readyList ) {

			readyList = jQuery.Deferred();

			// Catch cases where $(document).ready() is called
			// after the browser event has already occurred.
			// Support: IE6-10
			// Older IE sometimes signals "interactive" too soon
			if ( document.readyState === "complete" ||
				( document.readyState !== "loading" && ! document.documentElement.doScroll ) ) {

				// Handle it asynchronously to allow scripts the opportunity to delay ready
				window.setTimeout( jQuery.tcready );

				// Standards-based browsers support DOMContentLoaded
			} else if ( document.addEventListener ) {

				// Use the handy event callback
				document.addEventListener( "DOMContentLoaded", completed );

				// A fallback to window.onload, that will always work
				window.addEventListener( "load", completed );

				// If IE event model is used
			} else {

				// Ensure firing before onload, maybe late but safe also for iframes
				document.attachEvent( "onreadystatechange", completed );

				// A fallback to window.onload, that will always work
				window.attachEvent( "onload", completed );

				// If IE and not a frame
				// continually check to see if the document is ready
				var top = false;

				try {
					top = window.frameElement == null && document.documentElement;
				} catch ( e ) {
				}

				if ( top && top.doScroll ) {
					(function doScrollCheck() {
						if ( ! jQuery.tcisReady ) {

							try {

								// Use the trick by Diego Perini
								// http://javascript.nwbox.com/IEContentLoaded/
								top.doScroll( "left" );
							} catch ( e ) {
								return window.setTimeout( doScrollCheck, 50 );
							}

							// detach all dom ready events
							detach();

							// and execute any waiting functions
							jQuery.tcready();
						}
					})();
				}
			}
		}
		return readyList.promise( obj );
	};

	// Kick off the DOM ready check even if the user does not
	jQuery.tcready.promise();

})( jQuery );
/* */
(function ( $ ) {
	"use strict";

    String.prototype.tmstripslashes = function () {
          return (this + '')
            .replace(/\\(.?)/g, function (s, n1) {
                switch (n1) {
                    case '\\':
                    return '\\'
                    case '0':
                    return '\u0000'
                    case '':
                    return ''
                    default:
                    return n1;
                }
            });
    };

	String.prototype.tmparseParams = function ( decode ) {
		return this.split( "&" ).map( function ( n ) {
			if ( decode === true ) {
				n = decodeURIComponent( n );
			}
			return n = n.split( "=" ), this[ n[ 0 ] ] = n[ 1 ], this
		}.bind( {} ) )[ 0 ];
	};

	String.prototype.tmparseJSON = function tryParseJSON() {
		try {
			var o = $.parseJSON( (this+'') );
			if ( o && typeof o === "object" && o !== null ) {
				return o;
			}
		}
		catch ( e ) {
		}

		return false;
	};
	String.prototype.tmjid = function () {
		return this.replace( /(%|:|\.|\[|\]|,|=)/g, "\\$1" );
	};

	if ( ! $.tmempty ) {
		$.tmempty = function ( obj ) {
			var undef,
				key,
				i,
				len,
				emptyValues = [ undefined, null, false, 0, '', '0' ];
			for ( i = 0, len = emptyValues.length; i < len; i ++ ) {
				if ( obj === emptyValues[ i ] ) {
					return true;
				}
			}
			if ( typeof obj === 'object' ) {
				for ( key in obj ) {
					if ( obj.hasOwnProperty( key ) ) {
						return false;
					}
				}
				return true;
			}
			return false;
		};
	}

	// template wrapper
	$.fn.tm_template = function ( template, obj ) {
		var $template_html = template( obj );

		$template_html = $template_html.replace( '/*<![CDATA[*/', '' );
		$template_html = $template_html.replace( '/*]]>*/', '' );

		return $template_html;
	}

	$.fn.tc_wp_filter = {};

	if ( ! $.tc_add_filter ) {
		$.tc_add_filter = function ( $tag, $function_to_add, $priority, $accepted_args ) {
			$priority = parseInt( $priority );
			if ( isNaN( $priority ) ) {
				$priority = 10;
			}
			$accepted_args = parseInt( $accepted_args );
			if ( isNaN( $accepted_args ) ) {
				$accepted_args = 1;
			}
			var $idx = $function_to_add + "_" + $priority;
			if ( ! $.fn.tc_wp_filter[ $tag ] ) {
				$.fn.tc_wp_filter[ $tag ] = {};
			}
			if ( ! $.fn.tc_wp_filter[ $tag ][ $priority ] ) {
				$.fn.tc_wp_filter[ $tag ][ $priority ] = {};
			}
			$.fn.tc_wp_filter[ $tag ][ $priority ][ $idx ] = {
				'function': $function_to_add,
				'accepted_args': $accepted_args
			};
			return true;
		};
	}

	if ( ! $.tc_remove_filter ) {
		$.tc_remove_filter = function ( $tag, $function_to_remove, $priority ) {
			$priority = parseInt( $priority );
			if ( isNaN( $priority ) ) {
				$priority = 10;
			}
			var $idx = $function_to_remove + "_" + $priority;

			if ( $.fn.tc_wp_filter[ $tag ] && $.fn.tc_wp_filter[ $tag ][ $priority ] && $.fn.tc_wp_filter[ $tag ][ $priority ][ $idx ] ) {
				delete($.fn.tc_wp_filter[ $tag ][ $priority ][ $idx ]);
				return true;
			}

			return false;
		};
	}

	if ( ! $.tc_apply_filters ) {
		$.tc_apply_filters = function ( $tag, $value ) {
			var $args = jQuery.makeArray( arguments );
			$args.splice( 0, 1 );

			if ( ! $.fn.tc_wp_filter[ $tag ] ) {
				return $value;
			}

			var priorities = $.fn.tc_wp_filter[ $tag ];
			$.each( priorities, function ( i, el ) {
				//var priority = $(this);

				$.each( el, function ( i2, el2 ) {
					var //obj = $(this),
						func = el2[ "function" ];

					if ( func instanceof Function ) {
						$value = func.apply( null, $args );
					} else if ( window[ func ] && window[ func ] instanceof Function ) {
						$value = window[ func ].apply( null, $args );
					}

				} );

			} );
			return $value;
		};
	}

})( jQuery );

/* Image click fix not required with the use of new templates
 (function($) {
 "use strict";
 $(window).on('load',function(){
 $(document).on("click", ".tc-extra-product-options .tmcp-field-wrap label", function() {
 var $this=$(this);
 if ($this.find('.tm-epo-style')){
 return true;
 }
 var liw=$this.closest('li.tmcp-field-wrap'),
 cri=liw.find('.checkbox_image,.radio_image,.tc-checkbox-image,.tc-radio-image');
 if(cri.length){
 return false;
 }
 return true;
 });

 $(document).on("click", ".tm-epo-variation-section label img, .tc-extra-product-options label img, .tc-extra-product-options label .tmhexcolorimage, .tc-extra-product-options label img + span.tc-label:not(.use_images_container img + span.tc-label)", function(evt) {

 var $this   = $(this),
 label   = $this.closest("label"),
 tmepo   = $this.closest(".tc-extra-product-options"),
 box     = tmepo.find("#" + label.attr("for").tmjid());

 if (!box.length || box.attr('data-tm-disabled')){
 tmepo   = $this.closest(".tm-epo-variation-section"),
 box     = tmepo.find("#" + label.attr("for").tmjid());
 if (!box.length || box.attr('data-tm-disabled')){
 return;
 }
 }
 var _check=false;
 if (box.is(":checked")){
 _check = true;
 }
 if (box.is(".tmcp-field.tmcp-radio") && _check){
 return;
 }
 if (!_check){
 var boxes = tmepo.find('[name="'+box.attr("name")+'"]');
 boxes.prop("checked",false);
 box.prop("checked",true);
 }else{
 box.prop("checked",false);
 }
 box.trigger('change').trigger('tmredirect');
 });

 $(document).on("click", ".tc-extra-product-options .radio_image_label,.tc-extra-product-options .checkbox_image_label", function() {
 $(this).closest("label").find("img").trigger("click");
 });
 });
 })(jQuery);
 */
(function ( $ ) {
	"use strict";

	if ( ! $.tm_reverse ) {
		$.fn.tm_reverse = [].reverse;
	}

	if ( ! $.is_on_screen ) {
		$.fn.is_on_screen = function () {
			var win = $( window ),
				u = $.tm_getPageScroll(),
				bounds = this.offset(),
				viewport = {
					top: u[ 1 ],
					left: u[ 0 ]
				};
			viewport.right = viewport.left + win.width();
			viewport.bottom = viewport.top + win.height();

			bounds.right = bounds.left + this.outerWidth();
			bounds.bottom = bounds.top + this.outerHeight();

			return (! (viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));
		};
	}


	if ( ! $.tm_tooltip ) {
		$.tm_tooltip = function ( jobj ) {
			if ( typeof jobj === 'undefined' ) {
				jobj = $( '.tm-tooltip' );
			}
			var targets = jobj,
				target = false,
				tooltip = false;
			//title = false;
			if ( ! targets.length > 0 || targets.data( 'tm-has-tm-tip' ) ) {
				return;
			}
			targets.data( 'tm-has-tm-tip', 1 );
			targets.each( function ( i, el ) {
				var current_element = $( el ),
					is_swatch = current_element.attr( 'data-tm-tooltip-swatch' ),
					is_swatch_desc = current_element.attr( 'data-tm-tooltip-swatch-desc' ),
					is_swatch_lbl_desc = current_element.attr( 'data-tm-tooltip-swatch-lbl-desc' ),
					is_swatch_img = current_element.attr( 'data-tm-tooltip-swatch-img' ),
					is_swatch_img_lbl = current_element.attr( 'data-tm-tooltip-swatch-img-lbl' ),
					is_swatch_img_desc = current_element.attr( 'data-tm-tooltip-swatch-img-desc' ),
					is_swatch_img_lbl_desc = current_element.attr( 'data-tm-tooltip-swatch-img-lbl-desc' );

				var get_img_src = ( current_element.attr( 'data-original' ) != undefined ) 
                    ? current_element.attr( 'data-original' ) 
                    : (current_element.attr( 'src' ))?current_element.attr( 'src' ):current_element[0].src;

				if ( is_swatch ) {
					var label = current_element.closest( '.tmcp-field-wrap' );
					if ( label.length == 0 ) {
						label = current_element.closest( '.cpf_hide_element' );
					}
					if ( label.length == 0 ) {
						label = current_element.closest( '.cpf-section' );
					}
					label = label.find( '.checkbox_image_label,.radio_image_label,.tm-tip-html' );
					var tip = $( label ).html();
					current_element.data( 'tm-tip-html', tip );
					$( label ).hide();
				}
				if ( is_swatch_desc ) {
					var desc = current_element.closest( '.tmcp-field-wrap' );
					desc = desc.find( '[data-tm-tooltip-html]' );
					var tip = '<aside>' + desc.attr( 'data-tm-tooltip-html' ) + '</aside>';
					current_element.data( 'tm-tip-html', tip );
					$( label ).hide();
				}
				if ( is_swatch_lbl_desc ) {
					var label = current_element.closest( '.tmcp-field-wrap' );
					var desc = current_element.closest( '.tmcp-field-wrap' );
					if ( label.length == 0 ) {
						label = current_element.closest( '.cpf_hide_element' );
					}
					if ( label.length == 0 ) {
						label = current_element.closest( '.cpf-section' );
					}
					label = label.find( '.checkbox_image_label,.radio_image_label,.tm-tip-html' );
					desc = desc.find( '[data-tm-tooltip-html]' );
					var tip = '<aside>' + $( label ).html() + '</aside><aside>' + desc.attr( 'data-tm-tooltip-html' ) + '</aside>';
					current_element.data( 'tm-tip-html', tip );
					$( label ).hide();
				}
				if ( is_swatch_img ) {
					var tip = '<img src="' + get_img_src + '">';
					current_element.data( 'tm-tip-html', tip );
					$( label ).hide();
				}
				if ( is_swatch_img_lbl ) {
					var label = current_element.closest( '.tmcp-field-wrap' );
					if ( label.length == 0 ) {
						label = current_element.closest( '.cpf_hide_element' );
					}
					if ( label.length == 0 ) {
						label = current_element.closest( '.cpf-section' );
					}
					label = label.find( '.checkbox_image_label,.radio_image_label,.tm-tip-html' );
					var tip = '<img src="' + get_img_src + '"><aside>' + $( label ).html() + '</aside>';
					current_element.data( 'tm-tip-html', tip );
					$( label ).hide();
				}
				if ( is_swatch_img_desc ) {
					var desc = current_element.closest( '.tmcp-field-wrap' );
					desc = desc.find( '[data-tm-tooltip-html]' );
					var tip = '<img src="' + get_img_src + '"><aside>' + desc.attr( 'data-tm-tooltip-html' ) + '</aside>';
					current_element.data( 'tm-tip-html', tip );
				}
				if ( is_swatch_img_lbl_desc ) {
					var label = current_element.closest( '.tmcp-field-wrap' );
					var desc = current_element.closest( '.tmcp-field-wrap' );
					if ( label.length == 0 ) {
						label = current_element.closest( '.cpf_hide_element' );
					}
					if ( label.length == 0 ) {
						label = current_element.closest( '.cpf-section' );
					}
					label = label.find( '.checkbox_image_label,.radio_image_label,.tm-tip-html' );
					desc = desc.find( '[data-tm-tooltip-html]' );
					var tip = '<img src="' + get_img_src + '"><aside>' + $( label ).html() + '</aside><aside>' + desc.attr( 'data-tm-tooltip-html' ) + '</aside>';
					current_element.data( 'tm-tip-html', tip );
					$( label ).hide();
				}

			} );
			targets.on( 'tc-tooltip-html-changed', function () {
				var $this = $( this );
				if ( $this.attr( 'data-tm-tooltip-html' ) ) {
					$this.show();
				} else {
					$this.hide();
				}
			} );
			
            var showtooltip = function () {
                var $this = $( this );

				target = $this.is( '.tm-tooltip' )?$this:$this.find( '.tm-tooltip' );
				if ( target.data( 'is_moving' ) ) {
					return;
				}
				var tip = target.attr( 'title' ),
					tiphtml = target.attr( 'data-tm-tooltip-html' ),
					is_swatch = target.attr( 'data-tm-tooltip-swatch' ),
					is_swatch_desc = target.attr( 'data-tm-tooltip-swatch-desc' ),
					is_swatch_lbl_desc = target.attr( 'data-tm-tooltip-swatch-lbl-desc' ),
					is_swatch_img = target.attr( 'data-tm-tooltip-swatch-img' ),
					is_swatch_img_lbl = target.attr( 'data-tm-tooltip-swatch-img-lbl' ),
					is_swatch_img_desc = target.attr( 'data-tm-tooltip-swatch-img-desc' ),
					is_swatch_img_lbl_desc = target.attr( 'data-tm-tooltip-swatch-img-lbl-desc' );

				$( '#tm-tooltip' ).remove();
				tooltip = $( '<div id="tm-tooltip" class="tm-tip tm-animated"></div>' );

				if ( ! ((tip && tip != '') || is_swatch || is_swatch_desc || is_swatch_lbl_desc || is_swatch_img || is_swatch_img_lbl || is_swatch_img_desc || is_swatch_img_lbl_desc || tiphtml) ) {
					return false;
				}

				if ( target.attr( 'data-tm-tooltip-html' ) ) {
					tip = target.attr( 'data-tm-tooltip-html' );
				} else {
					tip = target.attr( 'title' );
				}
				if ( is_swatch || is_swatch_desc || is_swatch_lbl_desc || is_swatch_img || is_swatch_img_lbl || is_swatch_img_desc || is_swatch_img_lbl_desc ) {
					tip = target.data( 'tm-tip-html' );
				}
				if ( typeof jobj === 'undefined' ) {
					target.removeAttr( 'title' );
				}
				tooltip.css( 'opacity', 0 )
					.html( tip )
					.appendTo( 'body' );

				var init_tooltip = function ( nofx ) {
					if ( nofx == 1 ) {
						if ( is_swatch ) {
							tip = target.data( 'tm-tip-html' );
						} else {
							if ( target.attr( 'data-tm-tooltip-html' ) ) {
								tip = target.attr( 'data-tm-tooltip-html' );
							} else {
								tip = target.attr( 'title' );
							}
						}
						tooltip.html( tip );
					}
                    tooltip.find('aside').hide();
					if ( $( window ).width() < tooltip.outerWidth() * 1.5 ) {
						tooltip.css( 'max-width', $( window ).width() / 2 );
					} else {
						tooltip.css( 'max-width', 340 );
					}
                    tooltip.find('aside').show();
					var u = $.tm_getPageScroll(),
						pos_left = target.offset().left + (target.outerWidth() / 2) - (tooltip.outerWidth() / 2),
						pos_top = target.offset().top - tooltip.outerHeight() - 10,
						pos_from_top = target.offset().top - u[ 1 ] - tooltip.outerHeight() - 10;

					if ( pos_left < 0 ) {
						pos_left = target.offset().left + target.outerWidth() / 2 - 20;
						tooltip.addClass( 'left' );
					} else {
						tooltip.removeClass( 'left' );
					}
					if ( pos_left + tooltip.outerWidth() > $( window ).width() ) {
						pos_left = target.offset().left - tooltip.outerWidth() + target.outerWidth() / 2 + 20;
						tooltip.addClass( 'right' );
					} else {
						tooltip.removeClass( 'right' );
					}
					if ( pos_top < 0 || pos_from_top < 0 ) {
						pos_top = target.offset().top + target.outerHeight();
						tooltip.addClass( 'top' );
					} else {
						tooltip.removeClass( 'top' );
					}
					$( window ).trigger( 'tm_tooltip_show' );
					if ( nofx ) {
						tooltip.css( {
							left: pos_left,
							top: (pos_top)
						} );
						target.data( 'is_moving', false );
					} else {
						tooltip.css( {
							left: pos_left,
							top: pos_top
						} )
							.removeClass( 'fadeOutDown' ).addClass( 'fadeInUp' );
					}
				};

				init_tooltip();
				$( window ).resize( init_tooltip );
				target.data( 'is_moving', false );
				var remove_tooltip = function () {
					if ( target.data( 'is_moving' ) ) {
						return;
					}
					tooltip.removeClass( 'fadeInUp' ).addClass( 'fadeOutDown' );
					var speed = 15000;
					tooltip.animate( {
						opacity: 0
					}, speed, function () {
						$( this ).remove();
					} );

					if ( ! tiphtml && ! is_swatch && ! is_swatch_desc && ! is_swatch_lbl_desc && ! is_swatch_img && ! is_swatch_img_lbl && ! is_swatch_img_desc && ! is_swatch_img_lbl_desc ) {
						target.attr( 'title', tip );
					}
				};

				target
					.on( 'tmmovetooltip', function () {
						target.data( 'is_moving', true );
						init_tooltip( 1 );
					} )
					.on( 'mouseleave tmhidetooltip', remove_tooltip );
                target.closest('label').on( 'mouseleave tmhidetooltip', remove_tooltip );
				tooltip.on( 'click', remove_tooltip );
			};
            targets.closest('label').on( 'mouseenter tmshowtooltip', showtooltip );
            targets.on( 'mouseenter tmshowtooltip', showtooltip );
			return targets;
		}
	}

	$.fn.aserializeArray = function () {
		var rselectTextarea = /^(?:select|textarea)/i,
			rinput = /^(?:color|date|datetime|email|hidden|month|number|password|range|search|tel|text|time|url|week)$/i;
		if ( ! this.get( 0 ).elements ) {
			$( this ).wrap( '<form></form>' );
			var varretval = this.parent().map( function () {
				return this.elements ? $.makeArray( this.elements ) : this;
			} ).filter( function () {
				return this.name && ! this.disabled && (this.checked || rselectTextarea.test( this.nodeName ) || rinput.test( this.type ));
			} ).map( function ( i, elem ) {
				var val = $( this ).val();
				return val == null ? null : $.isArray( val ) ? $.map( val, function ( val, i ) {
					return {
						name: elem.name,
						value: val
					};
				} ) : {
					name: elem.name,
					value: val
				};
			} ).get();
			$( this ).unwrap();
			return varretval;
		} else {
			return this.map( function () {
				return this.elements ? $.makeArray( this.elements ) : this;
			} ).filter( function () {
				return this.name && ! this.disabled && (this.checked || rselectTextarea.test( this.nodeName ) || rinput.test( this.type ));
			} ).map( function ( i, elem ) {
				var val = $( this ).val();
				return val == null ? null : $.isArray( val ) ? $.map( val, function ( val, i ) {
					return {
						name: elem.name,
						value: val
					};
				} ) : {
					name: elem.name,
					value: val
				};
			} ).get();
		}
	}
	$.fn.tm_serializeObject = function () {
		var o = {};
		var a = this.serializeArray();
		$.each( a, function () {
			if ( o[ this.name ] !== undefined ) {
				if ( ! o[ this.name ].push ) {
					o[ this.name ] = [ o[ this.name ] ];
				}
				o[ this.name ].push( this.value || '' );
			} else {
				o[ this.name ] = this.value || '';
			}
		} );
		return o;
	}
	$.fn.tm_aserializeObject = function () {
		var o = {};
		var a = this.aserializeArray();
		$.each( a, function () {
			if ( o[ this.name ] !== undefined ) {
				if ( ! o[ this.name ].push ) {
					o[ this.name ] = [ o[ this.name ] ];
				}
				o[ this.name ].push( this.value || '' );
			} else {
				o[ this.name ] = this.value || '';
			}
		} );
		return o;
	}


	if ( ! $().on ) {
		$.fn.on = function ( types, selector, data, fn ) {
			return this.delegate( selector, types, data, fn );
		}
	}

	if ( ! $.tmType ) {
		$.tmType = function ( obj ) {
			return ({}).toString.call( obj ).match( /\s([a-zA-Z]+)/ )[ 1 ].toLowerCase();
		}
	}

    /* https://github.com/kvz/phpjs/blob/master/functions/array/array_values.js */
	if ( ! $.tm_array_values ) {
		$.tm_array_values = function ( input ) {
			var tmp_arr = [], key = '';
			for ( key in input ) {
				tmp_arr[ tmp_arr.length ] = input[ key ];
			}
			return tmp_arr;
		}
	}

    /* https://github.com/kvz/phpjs/blob/master/functions/misc/uniqid.js */
	if ( ! $.tm_uniqid ) {
		$.tm_uniqid = function ( prefix, more_entropy ) {
			if ( typeof prefix === 'undefined' ) {
				prefix = '';
			}
			var retId;
			var formatSeed = function ( seed, reqWidth ) {
				seed = parseInt( seed, 10 )
					.toString( 16 ); // to hex str
				if ( reqWidth < seed.length ) {
					// so long we split
					return seed.slice( seed.length - reqWidth );
				}
				if ( reqWidth > seed.length ) {
					// so short we pad
					return Array( 1 + (reqWidth - seed.length) )
							.join( '0' ) + seed;
				}
				return seed;
			};
			// BEGIN REDUNDANT
			if ( ! this.php_js ) {
				this.php_js = {};
			}
			// END REDUNDANT
			if ( ! this.php_js.uniqidSeed ) {
				// init seed with big random int
				this.php_js.uniqidSeed = Math.floor( Math.random() * 0x75bcd15 );
			}
			this.php_js.uniqidSeed ++;

			// start with prefix, add current milliseconds hex string
			retId = prefix;
			retId += formatSeed( parseInt( new Date()
					.getTime() / 1000, 10 ), 8 );
			// add seed hex string
			retId += formatSeed( this.php_js.uniqidSeed, 5 );
			if ( more_entropy ) {
				// for more entropy we add a float lower to 10
				retId += (Math.random() * 10)
					.toFixed( 8 )
					.toString();
			}

			return retId;
		}
	}

	/**
	 * Textarea and select clone() bug workaround | Spencer Tipping
	 * Licensed under the terms of the MIT source code license
	 * https://github.com/spencertipping/jquery.fix.clone/blob/master/jquery.fix.clone.js
	 */

	if ( ! $().tm_clone ) {
		$.fn.tm_clone = function () {
			var result = $.fn.clone.apply( this, arguments ),
				my_textareas = this.find( 'textarea' ).add( this.filter( 'textarea' ) ),
				result_textareas = result.find( 'textarea' ).add( result.filter( 'textarea' ) ),
				my_selects = this.find( 'select' ).add( this.filter( 'select' ) ),
				result_selects = result.find( 'select' ).add( result.filter( 'select' ) );
			for ( var i = 0, l = my_textareas.length; i < l; ++ i ) {
				$( result_textareas[ i ] ).val( $( my_textareas[ i ] ).val() );
			}
			for ( var i = 0, l = my_selects.length; i < l; ++ i ) {
				for ( var j = 0, m = my_selects[ i ].options.length; j < m; ++ j ) {
					if ( my_selects[ i ].options[ j ].selected === true ) {
						result_selects[ i ].options[ j ].selected = true;
					}
				}
			}
			return result;
		}
	}

	(function () {
		// based on easing equations from Robert Penner (http://www.robertpenner.com/easing)
		var baseEasings = {};
		$.each( [ "Quad", "Cubic", "Quart", "Quint", "Expo" ], function ( i, name ) {
			baseEasings[ name ] = function ( p ) {
				return Math.pow( p, i + 2 );
			};
		} );
		$.extend( baseEasings, {
			Sine: function ( p ) {
				return 1 - Math.cos( p * Math.PI / 2 );
			},
			Circ: function ( p ) {
				return 1 - Math.sqrt( 1 - p * p );
			},
			Elastic: function ( p ) {
				return p === 0 || p === 1 ? p : - Math.pow( 2, 8 * (p - 1) ) * Math.sin( ((p - 1) * 80 - 7.5) * Math.PI / 15 );
			},
			Back: function ( p ) {
				return p * p * (3 * p - 2);
			},
			Bounce: function ( p ) {
				var pow2,
					bounce = 4;

				while ( p < ((pow2 = Math.pow( 2, -- bounce )) - 1) / 11 ) {
				}
				return 1 / Math.pow( 4, 3 - bounce ) - 7.5625 * Math.pow( (pow2 * 3 - 2) / 22 - p, 2 );
			}
		} );
		$.each( baseEasings, function ( name, easeIn ) {
			$.easing[ "easeIn" + name ] = easeIn;
			$.easing[ "easeOut" + name ] = function ( p ) {
				return 1 - easeIn( 1 - p );
			};
			$.easing[ "easeInOut" + name ] = function ( p ) {
				return p < 0.5 ?
					easeIn( p * 2 ) / 2 :
					1 - easeIn( p * - 2 + 2 ) / 2;
			};
		} );
	})();

	if ( ! $().tm_getPageSize ) {
		$.tm_getPageSize = function () {
			var e, t, pageHeight, pageWidth;
			if ( window.innerHeight && window.scrollMaxY ) {
				e = window.innerWidth + window.scrollMaxX;
				t = window.innerHeight + window.scrollMaxY;
			} else if ( document.body.scrollHeight > document.body.offsetHeight ) {
				e = document.body.scrollWidth;
				t = document.body.scrollHeight;
			} else {
				e = document.body.offsetWidth;
				t = document.body.offsetHeight;
			}
			var n, r;
			if ( self.innerHeight ) {
				if ( document.documentElement.clientWidth ) {
					n = document.documentElement.clientWidth;
				} else {
					n = self.innerWidth;
				}
				r = self.innerHeight
			} else if ( document.documentElement && document.documentElement.clientHeight ) {
				n = document.documentElement.clientWidth;
				r = document.documentElement.clientHeight;
			} else if ( document.body ) {
				n = document.body.clientWidth;
				r = document.body.clientHeight;
			}
			if ( t < r ) {
				pageHeight = r;
			} else {
				pageHeight = t;
			}
			if ( e < n ) {
				pageWidth = n;
			} else {
				pageWidth = e;
			}
			return new Array( pageWidth, pageHeight, n, r, e, t );

		}
	}

	if ( ! $().tm_getPageScroll ) {
		$.tm_getPageScroll = function () {
			var e, t;
			if ( self.pageYOffset ) {
				t = self.pageYOffset;
				e = self.pageXOffset;
			} else if ( document.documentElement && document.documentElement.scrollTop ) {
				t = document.documentElement.scrollTop;
				e = document.documentElement.scrollLeft;
			} else if ( document.body ) {
				t = document.body.scrollTop;
				e = document.body.scrollLeft;
			}
			return new Array( e, t );

		}
	}

	if ( ! $().tm_floatbox ) {
		$.fn.tm_floatbox = function ( t ) {
			function s( e ) {
				if ( o( e, n ) ) {
					return n;
				} else {
					return false;
				}
			}

			function f() {
				if ( t.hideelements ) $( "embed, object, select" ).css( {
					visibility: "visible"
				} );
				if ( t.showoverlay === true ) {
					if ( t._ovl ) {
						t._ovl.unbind();
						t._ovl.remove();
					}
				}

				$( t.floatboxID ).addClass( 'tc-closing' ).removeClass( t.animateIn ).addClass( t.animateOut );
				$( t.floatboxID ).animate( {
						opacity: 0

					}, 1000, function () {
						$( t.floatboxID ).remove();
					}
				);

				var _in = $.fn.tm_floatbox.instances.length;
				if ( _in > 0 ) {
					var _t = $.fn.tm_floatbox.instances[ _in - 1 ];
					if ( t.id == _t.id ) $.fn.tm_floatbox.instances.pop();
				}

				$( window ).off( "scroll.tmfloatbox" );
			}

			function o( n, s ) {
				if ( s.length == 1 ) {
					f();
					if ( t.hideelements ) $( "embed, object, select" ).css( {
						visibility: "hidden"
					} );
					$( t.type ).attr( "id", t.id ).addClass( t.classname ).html( t.data ).appendTo( n );
					var _in = $.fn.tm_floatbox.instances.length;
					if ( _in > 0 ) {
						var _t = $.fn.tm_floatbox.instances[ _in - 1 ];
						t.zIndex = _t.zIndex + 100;
					}
					$.fn.tm_floatbox.instances.push( t );
					$( t.floatboxID ).css( {
						width: t.width,
						height: t.height
					} );
					var o = $.tm_getPageSize();
					var u = $.tm_getPageScroll();
					var l = 0;
					//var c = parseInt(u[1] + (o[3] - $(t.floatboxID).height()) / 2);
					var h = parseInt( u[ 0 ] + (o[ 2 ] - $( t.floatboxID ).width()) / 2 );
					$( t.floatboxID ).css( {
						top: l + "px",
						left: h + "px",
						"z-index": t.zIndex
					} );
					r = l;
					i = h;
					n.cancelfunc = t.cancelfunc;
					if ( t.showoverlay === true ) {
						t._ovl = $( '<div class="fl-overlay"></div>' ).css( {
							zIndex: (t.zIndex - 1),
							opacity: .8
						} );
						t._ovl.appendTo( "body" );
						if ( ! t.ismodal ) t._ovl.click( t.cancelfunc );
					}
					if ( t.showfunc ) {
						t.showfunc.call();
					}

					$( t.floatboxID ).addClass( t.animationBaseClass + " " + t.animateIn );
					if ( t.refresh == "fixed" ) {
						var top = parseInt( (o[ 3 ] - $( t.floatboxID ).height()) / 2 );

						if ( t.top !== false ) {
							top = t.top;
						} else {
							top = top + "px"
						}
						$( t.floatboxID ).css( {
							position: "fixed",
							top: top
						} );

						if ( t.left !== false ) {
							$( t.floatboxID ).css( {
								left: t.left
							} );
						}

					} else {
						a();
						$( window ).on( "scroll.tmfloatbox", doit );
					}

					$( window ).on( "resize.tmfloatbox", function () {
						doit();
					} );

					return true;
				} else {
					return false;
				}
			}

			function requestTick() {
				if ( ! ticking ) {
					if ( t.refresh ) {
						setTimeout( function () {
							requestAnimationFrame( update );
						}, t.refresh );
					} else {
						requestAnimationFrame( update );
					}

					ticking = true;
				}
			}

			function update() {
				a();
				ticking = false;
			}

			function doit() {
				requestTick();
			}

			function u( n, r ) {
				$( t.floatboxID ).css( {
					top: n + "px",
					left: r + "px",
					opacity: 1
				} );
			}

			function a() {
				var n = $.tm_getPageSize();
				var s = $.tm_getPageScroll();
				if ( t.refresh == "fixed" ) {
					s[ 0 ] = 0;
					s[ 1 ] = 0;
				}
				var o = parseInt( s[ 1 ] + (n[ 3 ] - $( t.floatboxID ).height()) / 2 );
				var a = parseInt( s[ 0 ] + (n[ 2 ] - $( t.floatboxID ).width()) / 2 );
				o = parseInt( (o - r) / t.fps );
				a = parseInt( (a - i) / t.fps );
				r += o;
				i += a;

				u( r, i );
			}

			t = jQuery.extend( {
				id: "flasho",
				classname: "flasho",
				type: "div",
				data: "",
				width: "500px",
				height: "auto",
				animationBaseClass: 'tm-animated',
				animateIn: 'fadeInDown',
				animateOut: 'fadeOutDown',
				top: false,
				left: false,
				refresh: false,
				fps: 4,
				hideelements: false,
				showoverlay: true,
				zIndex: 100100,
				ismodal: false,
				cancelfunc: f,
				showfunc: null
			}, t );
			t.floatboxID = "#" + t.id.tmjid();
			t.type = "<" + t.type + ">";
			var n = this;
			var r = 0;
			var i = 0;
			var ticking = false;

			return s( this );
		}
		$.fn.tm_floatbox.instances = [];

	}

	if ( ! $().tmtabs ) {
		$.fn.tmtabs = function ( options ) {
			var elements = this;

			if ( elements.length == 0 ) {
				return;
			}
			options = $.extend( {
				headers: ".tm-tab-headers",
				header: ".tab-header",
				addheader: ".tm-add-tab",
				classdown: "tcfa-angle-down",
				classup: "tcfa-angle-up",
				animationclass: "appear",
				dataattribute: "data-id",
				selectedtab: "auto",
				showonhover: false,
				useclasstohide: true,
				afteraddtab: function () {
					a, b
				},
				deletebutton: false,
				deletebuttonhtml: '<h4 class="tm-del-tab"><span class="tcfa tcfa-times"></span></h4>',
				deleteheader: '.tm-del-tab',
				deleteconfirm: false,
				beforedeletetab: function ( a, b ) {
				},
				afterdeletetab: function () {
				}
			}, options );

			return elements.each( function () {
				var t = $( this ),
					tc = t.attr( 'class' ),
					headers = t.find( options.headers + " " + options.header ),
					ohp = 0,
					ohpid = '';
				if ( headers.length == 0 ) {
					return;
				}
				t.data( 'tm-has-tmtabs', 1 );
				var init_open = 0,
					add_counter = 0,
					last = false,
					current = "";

				function tm_tab_add_header_events( header ) {
					header.on( "closetab.tmtabs", function ( e ) {
						var _tab = t.find( $( this ).data( "tab" ) );
						$( this ).removeClass( "closed open" ).addClass( "closed" );
						$( this ).find( ".tm-arrow" ).removeClass( options.classdown + " " + options.classup ).addClass( options.classdown );
						if ( options.useclasstohide ) {
							_tab.addClass( "tm-hide" ).removeClass( 'tm-show' );
						} else {
							_tab.hide();
						}
						_tab.removeClass( "tm-animated " + options.animationclass );
						$( window ).trigger( "tc-closetab.tmtabs", { "header": $( this ), "tab": _tab } );
					} );

					header.on( "opentab.tmtabs", function ( e ) {
						$( this ).removeClass( "closed open" ).addClass( "open" );
						$( this ).find( ".tm-arrow" ).removeClass( "tcfa-angle-down tcfa-angle-up" ).addClass( "tcfa-angle-up" );
						var _tab = t.find( $( this ).data( "tab" ) );
						if ( options.useclasstohide ) {
							_tab.removeClass( "tm-hide" ).addClass( 'tm-show' );
						} else {
							_tab.show();
						}
						_tab.removeClass( "tm-animated " + options.animationclass ).addClass( "tm-animated " + options.animationclass );
						current = $( this ).data( "tab" );
						$( window ).trigger( "tc-opentab.tmtabs", {
							"header": $( this ),
							"tab": current,
							"table": _tab
						} );
					} );
					var additional_events = "";
					if ( options.showonhover === true || typeof options.showonhover === "function" ) {
						additional_events = " mouseover";
					}
					header.on( "click.tmtabs" + additional_events, function ( e ) {
						e.preventDefault();
						if ( e.type == "mouseover" && typeof options.showonhover === "function" && ! options.showonhover.call() ) {
							return;
						}
						if ( current == $( this ).data( "tab" ) ) {
							$( window ).trigger( "tc-isopentab.tmtabs", {
								"header": $( this ),
								"tab": current,
								"table": t.find( current )
							} );
							return;
						}
						if ( last ) {
							$( last ).trigger( "closetab.tmtabs" );
						}
						$( this ).trigger( "opentab.tmtabs" );
						last = $( this );
						Cookies.set( 'tmadmintab-' + tc, $( this ).attr( options.dataattribute ), {
							expires: 7,
							path: ''
						} );
						$( window ).trigger( "tc-tmtabs-clicked", {
							'tc': tc,
							'options': options,
							"header": $( this ),
							"tab": current,
							"table": t.find( current )
						} );
					} );

					if ( options.deletebutton ) {
						header.after( options.deletebuttonhtml );
						header.closest( ".tm-box" ).find( options.deleteheader ).on( 'click.tmtabs', function ( e ) {
							if ( t.find( options.headers + " " + options.header ).length < 2 ) {
								return;
							}
							if ( options.deleteconfirm ) {
								if ( ! confirm( tm_epo_admin.i18n_builder_delete ) ) {
									return;
								}
							}
							var $t = $( this ),
								$header = $t.closest( ".tm-box" ).find( options.header ).attr( options.dataattribute ),
								$tab = t.find( "." + $t.closest( ".tm-box" ).find( options.header ).attr( options.dataattribute ) );

							options.beforedeletetab.call( t, $header, $tab );

							$tab.remove();
							$t.closest( ".tm-box" ).remove();

							options.afterdeletetab.call( t );
						} );
					}
				}

				headers.each( function ( i, header ) {

					var header = $( header ), id = "." + header.attr( options.dataattribute );
					header.data( "tab", id );
					if ( options.useclasstohide ) {
						t.find( id ).addClass( "tm-hide" ).removeClass( 'tm-show' );
					} else {
						t.find( id ).hide();
					}
					t.find( id ).data( "state", "closed" );
					if ( ! init_open && header.is( ".open" ) ) {
						header.removeClass( "closed open" ).addClass( "open" ).data( "state", "open" );
						header.find( ".tm-arrow" ).removeClass( options.classdown + " " + options.classup ).addClass( options.classup );
						if ( options.useclasstohide ) {
							t.find( id ).removeClass( "tm-hide" ).addClass( 'tm-show' );
						} else {
							t.find( id ).show();
						}
						t.find( id ).data( "state", "open" );
						init_open = 1;
						current = id;
						last = header;
					} else {
						header.removeClass( "closed open" ).addClass( "closed" ).data( "state", "closed" );
					}

					tm_tab_add_header_events( header );

				} );
				t.find( options.headers + ":not(.section_elements " + options.headers + ",.tm-settings-wrap " + options.headers + ",.builder_element_wrap " + options.headers + ")" ).sortable( {
					containment: "parent",
					cursor: "move",
					items: ".tm-box:not(.tm-add-box)",
					start: function ( e, ui ) {
						ohp = ui.item.index();
						ohpid = ui.item.find( options.header ).attr( 'data-id' );
					},
					stop: function ( e, ui ) {
						var all_headers = t.find( options.headers + " " + options.header );
						all_headers.each( function ( i, el ) {
							var $t = $( this );
							//id=$t.attr('data-id');
							$t.html( i + 1 );
						} );
						var original_item = t.find( '.tm-slider-wizard-tab.' + ohpid ),
							new_index = t.find( options.headers + " " + options.header + "[data-id='" + ohpid + "']" ).parent().index(),
							replaced_item = t.find( ".tm-slider-wizard-tab" ).eq( new_index );
						if ( new_index > ohp ) {
							replaced_item.after( original_item );
						} else if ( new_index < ohp ) {
							replaced_item.before( original_item );
						}
						$.tmEPOAdmin.builder_reorder_multiple();
					},
					cancel: ".tm-add-box",
					forcePlaceholderSize: true,
					tolerance: 'pointer'
				} );
				t.find( options.addheader ).on( "click.tmtabs", function ( e ) {
					e.preventDefault();
					var last_header = t.find( options.headers + " " + options.header ).last(),
						id = last_header.attr( options.dataattribute ),
						last_tab = t.find( "." + id ),
						new_header = last_header.tm_clone().off( "closetab.tmtabs opentab.tmtabs click.tmtabs" ),
						new_tab = last_tab.tm_clone().empty();

					add_counter ++;
					var newid = id + '-' + add_counter;
					new_header
						.html( t.find( options.headers + " " + options.header ).length + 1 )
						.removeClass( "closed open" )
						.addClass( "closed" )
						.data( "tab", "." + newid )
						.data( "state", "closed" )
						.attr( options.dataattribute, newid );
					new_tab.removeClass( id ).addClass( newid );
					if ( options.useclasstohide ) {
						new_tab.addClass( "tm-hide" ).removeClass( '.tm-show' );
					} else {
						new_tab.hide();
					}
					new_tab.removeClass( "tm-animated " + options.animationclass );

					last_header.closest( ".tm-box" ).after( new_header );

					new_header.wrap( '<div class="tm-box"></div>' );

					tm_tab_add_header_events( new_header );
					last_tab.after( new_tab );
					options.afteraddtab.call( this, new_header, new_tab );

				} );
				if ( options.selectedtab == "auto" ) {
					var _selected_tab = Cookies.get( 'tmadmintab-' + tc );
					if ( _selected_tab === undefined ) {
						_selected_tab = $( options.header ).eq( 0 ).attr( options.dataattribute );
					}
					if ( ! $( options.header + '[' + options.dataattribute + '="' + _selected_tab + '"]' ).is( ':visible' ) ) {
						_selected_tab = 0;
					}
					$( options.header + '[' + options.dataattribute + '="' + _selected_tab + '"]' ).trigger( 'click.tmtabs' );

				} else if ( options.selectedtab !== false ) {
					var _selected_tab = parseInt( options.selectedtab );
					t.find( options.header + ':eq(' + _selected_tab + ')' ).trigger( 'click.tmtabs' );
				}

			} );
		};
	}

	if ( ! $().tmtoggle ) {
		$.fn.tmtoggle = function () {
			var elements = this;

			if ( elements.length == 0 ) {
				return;
			}

			var is_one_open_for_accordion = false,
				init_done = 0;

			elements.each( function () {
				var t = $( this );
				if ( ! t.data( 'tm-toggle-init' ) ) {
					t.data( 'tm-toggle-init', 1 );
					var headers = t.find( ".tm-toggle" ),
						wrap = t.find( ".tm-collapse-wrap" ),
						wraps = $( ".tm-collapse.tmaccordion" ).find( ".tm-toggle" );
					if ( headers.length == 0 || wrap.length == 0 ) {
						return;
					}

					if ( wrap.is( ".closed" ) ) {
						$( wrap ).removeClass( "closed open" ).addClass( "closed" ).hide();
						$( headers ).find( ".tm-arrow" ).removeClass( "tcfa-angle-down tcfa-angle-up" ).addClass( "tcfa-angle-down" );
					} else {
						$( wrap ).removeClass( "closed open" ).addClass( "open" ).show();
						$( headers ).find( ".tm-arrow" ).removeClass( "tcfa-angle-down tcfa-angle-up" ).addClass( "tcfa-angle-up" );
						is_one_open_for_accordion = true;
					}

					headers.each( function ( i, header ) {

						$( header ).on( "closewrap.tmtoggle", function ( e ) {
							if ( t.is( '.tmaccordion' ) && $( wrap ).is( ".closed" ) ) {
								return;
							}
							$( wrap ).removeClass( "closed open" ).addClass( "closed" );
							$( this ).find( ".tm-arrow" ).removeClass( "tcfa-angle-down tcfa-angle-up" ).addClass( "tcfa-angle-down" );
							$( wrap ).removeClass( "tm-animated fadeInDown" );
							if ( t.is( '.tmaccordion' ) ) {
								//$(wrap).hide();
								$( wrap ).animate( { "height": "toggle" }, 100, function () {
									$( wrap ).hide();
								} );
							} else {
								$( wrap ).animate( { "height": "toggle" }, 100, function () {
									$( wrap ).hide();
								} );
							}
							$( window ).trigger( "tmlazy" );
						} );

						$( header ).on( "openwrap.tmtoggle", function ( e ) {
							if ( t.is( '.tmaccordion' ) ) {
								$( wraps ).not( $( this ) ).trigger( "closewrap.tmtoggle" );
							}
							$( wrap ).removeClass( "closed open" ).addClass( "open" );
							$( this ).find( ".tm-arrow" ).removeClass( "tcfa-angle-down tcfa-angle-up" ).addClass( "tcfa-angle-up" );
							$( wrap ).show().removeClass( "tm-animated fadeInDown" ).addClass( "tm-animated fadeInDown" );
							setTimeout( function () {
								$( window ).trigger( "tmlazy" );
							}, 200 );
							if ( init_done && t.is( '.tmaccordion' ) && ! t.is_on_screen() ) {
								$( window ).tc_scrollTo( $( header ) );
							}
						} );

						$( header ).on( "click.tmtoggle", function ( e ) {
							e.preventDefault();
							if ( $( wrap ).is( ".closed" ) ) {
								$( this ).trigger( "openwrap.tmtoggle" );
							} else {
								$( this ).trigger( "closewrap.tmtoggle" );
							}
						} );

						$( header ).find( '.tm-qty' ).closest( '.cpf_hide_element' ).find( '.tm-epo-field' ).on( 'change.cpf', function () {
							$( header ).trigger( 'openwrap.tmtoggle' );
						} );

					} );

				}
			} );
			if ( undefined === window.tc_accordion_closed_on_page_load && ! is_one_open_for_accordion && elements.filter( '.tmaccordion' ).length > 0 ) {
				elements.filter( '.tmaccordion' ).first().find( ".tm-toggle" ).trigger( 'openwrap.tmtoggle' );
			}
			init_done = 1;
			return elements;
		};
	}

	if ( ! $().tc_scrollTo ) {
		$.fn.tc_scrollTo = function ( obj, duration, offset ) {
			if ( ! duration ) {
				duration = 0;
			}
			if ( ! obj ) {
				return this;
			}
			var el = this;
			if ( this[ 0 ].self == window ) {
				el = $( 'html, body' );
			}
			if ( ! offset ) {
				offset = 0;
			}
			return el.animate( {
				scrollTop: $( obj ).offset().top + offset
			}, duration );
		};
	}
	if ( ! $().tmpoplink ) {
		$.fn.tmpoplink = function () {
			var elements = this;

			if ( elements.length == 0 ) {
				return;
			}

			var floatbox_template = function ( data ) {
				var out = $.fn.tm_template( wp.template( 'tc-cart-options-popup' ), {
					'title': data.title,
					'id': data.id,
					'html': data.html,
					'close': tm_epo_js.i18n_close
				} );

				return out;
			}

			return elements.each( function () {
				var t = $( this );
				if ( t.is( '.tc-poplink' ) ) {
					return;
				}
				t.addClass( 'tc-poplink' );
				var id = $( this ).attr( 'href' ),
					title = $( this ).attr( 'data-title' ) ? $( this ).attr( 'data-title' ) : tm_epo_js.i18n_addition_options,
					html = $( id ).html(),
					$_html = floatbox_template( {
						"id": "temp_for_floatbox_insert",
						"html": html,
						"title": title
					} ),
					clicked = false;

				t.on( "click.tmpoplink", function ( e ) {
					e.preventDefault();
					var _to = $( "body" ).tm_floatbox( {
						"fps": 1,
						"ismodal": false,
						"refresh": 100,
						"width": "80%",
						"height": "80%",
						"classname": "flasho tm_wrapper",
						"data": $_html
					} );

					$( ".details_cancel" ).click( function () {
						if ( clicked ) {
							return;
						}
						clicked = true;
						if ( _to ) {
							clicked = false;
							_to.cancelfunc();
						}
					} );
				} );


			} );
		};
	}

	$.tc_product_image = {};
	$.tc_product_image_store = {};

	// replace obj1 values with obj2 values
	$.tc_replace_object_values = function ( obj1, obj2 ) {
		for ( var x in obj1 ) {
			for ( var attr in obj1[ x ] ) {
				if ( (undefined !== obj2[ x ] && undefined !== obj2[ x ][ attr ]) && obj2[ x ].hasOwnProperty( attr ) ) {

					obj1[ x ][ attr ] = obj2[ x ][ attr ];

				}
			}
			;
		}
		;
		return obj1;
	}
	// copy obj2 values to obj1
	$.tc_maybe_copy_object_values = function ( obj1, obj2 ) {
		for ( var x in obj2 ) {
			for ( var attr in obj2[ x ] ) {
				if ( undefined !== obj2[ x ] && obj2[ x ].hasOwnProperty( attr ) && undefined !== obj2[ x ][ attr ] && (undefined === obj1[ x ] || undefined === obj1[ x ][ attr ]) ) {
					if ( undefined === obj1[ x ] ) {
						obj1[ x ] = {};
					}
					obj1[ x ][ attr ] = obj2[ x ][ attr ];

				}
			}
			;
		}
		;
		return obj1;
	}

	$.tc_populate_store = function ( img, product_element, $form ) {
		var //$product          = product_element,
			//$product_gallery  = $product.find( '.images' ),
			$gallery_img = product_element.find( '.flex-control-nav li:eq(0) img' ),
			$gallery_wrapper = product_element.find( '.woocommerce-product-gallery__wrapper ' ),
			$product_img_wrap = $gallery_wrapper.find( '.woocommerce-product-gallery__image, .woocommerce-product-gallery__image--placeholder' ).eq( 0 ),
			$product_img = img,
			$product_link = img.closest( 'a' ),
			obj = {};

		obj[ 0 ] = {};
		obj[ 1 ] = {};
		obj[ 2 ] = {};
		obj[ 3 ] = {};

		obj[ 0 ][ 'src' ] = $product_img.attr( 'src' );
		obj[ 0 ][ 'srcset' ] = $product_img.attr( 'srcset' );
		obj[ 0 ][ 'sizes' ] = $product_img.attr( 'sizes' );
		obj[ 0 ][ 'title' ] = $product_img.attr( 'title' );
		obj[ 0 ][ 'alt' ] = $product_img.attr( 'alt' );
		obj[ 0 ][ 'data-src' ] = $product_img.attr( 'data-src' );
		obj[ 0 ][ 'data-large_image' ] = $product_img.attr( 'data-large_image' );
		obj[ 0 ][ 'data-large_image_width' ] = $product_img.attr( 'data-large_image_width' );
		obj[ 0 ][ 'data-large_image_height' ] = $product_img.attr( 'data-large_image_height' );
		obj[ 1 ][ 'data-thumb' ] = $product_img_wrap.attr( 'data-thumb' );
		obj[ 2 ][ 'src' ] = $gallery_img.attr( 'src' );
		obj[ 3 ][ 'href' ] = $product_link.attr( 'href' );
		obj[ 3 ][ 'title' ] = $product_link.attr( 'title' );

		return obj;

	}

	$.tc_maybe_copy_object_values_from_img = function ( obj1, img, product_element, $form ) {
		var //$product          = product_element,
			//$product_gallery  = $product.find( '.images' ),
			$gallery_img = product_element.find( '.flex-control-nav li:eq(0) img' ),
			$gallery_wrapper = product_element.find( '.woocommerce-product-gallery__wrapper ' ),
			$product_img_wrap = $gallery_wrapper.find( '.woocommerce-product-gallery__image, .woocommerce-product-gallery__image--placeholder' ).eq( 0 ),
			$product_img = img,
			$product_link = img.closest( 'a' );

		var attrs,
			attr,
			attrs_product_img = [ 'src', 'srcset', 'sizes', 'title', 'alt', 'data-src', 'data-large_image', 'data-large_image_width', 'data-large_image_height', 'large-image' ],
			attrs_product_img_wrap = [ 'data-thumb' ],
			attrs_gallery_img = [ 'src' ],
			attrs_product_link = [ 'href', 'title' ];

		var all = [ $product_img, $product_img_wrap, $gallery_img, $product_link ],
			attrs_all = [ attrs_product_img, attrs_product_img_wrap, attrs_gallery_img, attrs_product_link ];
		for ( var i = all.length - 1; i >= 0; i -- ) {

			if ( undefined !== all[ i ] && undefined !== all[ i ][ 0 ] ) {
				attrs = all[ i ][ 0 ].attributes;

				$.each( attrs, function () {
					if ( this.specified ) {
						attr = this.name;

						if ( $.inArray( attr, attrs_all[ i ] ) !== - 1 &&
							(undefined === obj1[ i ] ||
							(undefined !== obj1[ i ] && undefined === obj1[ i ][ attr ]) )
						) {
							if ( undefined === obj1[ i ] ) {
								obj1[ i ] = {};
							}
							obj1[ i ][ attr ] = this.value;

						}
					}
				} );
			}

		}
		;
		return obj1;
	}

	/**
	 * Stores a default attribute for an element so it can be reset later
	 */
	$.fn.tc_set_attr = function ( attr, value, id ) {
        if ( undefined === id) {
            id = 0;
        }
		if ( undefined === $.tc_product_image[ id ] || (undefined !== $.tc_product_image[ id ] && undefined === $.tc_product_image[ id ][ attr ] ) ) {
			if ( undefined === $.tc_product_image[ id ] ) {
				$.tc_product_image[ id ] = {};
			}
			$.tc_product_image[ id ][ attr ] = ( ! this.attr( attr ) ) ? '' : this.attr( attr );
		}
		if ( false === value ) {
			this.removeAttr( attr );
		} else {
			this.attr( attr, value );
		}
	};

	/**
	 * Reset a default attribute for an element so it can be reset later
	 */
	$.fn.tc_reset_attr = function ( attr, id ) {
        if ( undefined === id) {
            id = 0;
        }
        if ( undefined === $.tc_product_image[ id ] ){
            return;
        }
		if ( undefined !== $.tc_product_image[ id ][ attr ] ) {
			this.attr( attr, $.tc_product_image[ id ][ attr ] );
		}
		delete $.tc_product_image[ id ][ attr ];
	};

	$.fn.tc_update_attr = function ( attr, id ) {
        if ( undefined === id) {
            id = 0;
        }
		if ( undefined !== $.tc_product_image[ id ] ) {
			$.tc_product_image[ id ][ attr ] = this.attr( attr );
		}
	};

	$.fn.tc_image_update = function ( element, image ) {
		element = $( element );
		if ( element.is( "select" ) ) {
			element = element.children( 'option:selected' );
		}
		var $form = this,
			$image = $( image ),
			epo_object = $form.data( 'epo_object' ),
			image_info = element.data( 'image-variations' ),
			product_element = epo_object[ 'main_product' ].closest( '#product-' + epo_object[ 'product_id' ] ),
			$product_element = (product_element.length > 0) ? product_element : epo_object[ 'main_product' ].closest( '.post-' + epo_object[ 'product_id' ] ),
			$product_img;

		if ( window.tm_epo_js && window.tm_epo_js.tm_epo_global_product_image_selector !== '' ) {
			$product_img = $( tm_epo_js.tm_epo_global_product_image_selector );
		} else {
			$product_img = $product_element.find( "a.woocommerce-main-image img, img.woocommerce-main-image,a img" ).not( '.thumbnails img,.product_list_widget img,img.emoji,a.woocommerce-product-gallery__trigger img' ).first();
		}
		var $product_link = $product_img.closest( "a" );

		if ( $product_img.length > 1 ) {
			$product_img = $product_img.first();
		}

		if ( element && image_info && $image.length > 0 ) {
			$image
				.removeAttr( 'data-o_src' )
				.removeAttr( 'data-o_title' )
				.removeAttr( 'data-o_alt' )
				.removeAttr( 'data-o_srcset' )
				.removeAttr( 'data-o_sizes' )
				.removeAttr( 'srcset' )
				.removeAttr( 'sizes' );

			var use_image_info = image_info[ 'imagep' ];
			if ( ! image_info[ 'imagep' ][ 'image_link' ] ) {
				use_image_info = image_info[ 'image' ];
			}

			$image.attr( 'title', use_image_info[ 'image_title' ] );
			$image.attr( 'alt', use_image_info[ 'image_alt' ] );
			if ( use_image_info[ 'image_srcset' ] ) {
				$image.attr( 'srcset', use_image_info[ 'image_srcset' ] );
			}
			if ( use_image_info[ 'image_sizes' ] ) {
				$image.attr( 'sizes', use_image_info[ 'image_sizes' ] );
			}

			//$product_img.tc_set_attr( 'src', variation.image_src );
			$product_img.tc_set_attr( 'title', use_image_info[ 'image_title' ] );
			$product_img.tc_set_attr( 'alt', use_image_info[ 'image_alt' ] );
			//$product_img.tc_set_attr( 'srcset', variation.image_srcset );
			//$product_img.tc_set_attr( 'sizes', variation.image_sizes );

			$product_img.tc_set_attr( 'data-large-image', use_image_info[ 'image_link' ] );
			if ( $product_img.data[ 'wc27_zoom_target' ] ) {
				$product_img.data[ 'wc27_zoom_target' ].tc_set_attr( 'data-thumb', use_image_info[ 'image_link' ] );
				$product_element.find( '.flex-control-nav li:eq(0) img' ).tc_set_attr( 'src', use_image_info[ 'image_link' ] );
			}

			$product_link.tc_set_attr( 'href', use_image_info[ 'image_link' ] );
			$product_link.tc_set_attr( 'title', use_image_info[ 'image_caption' ] );
		} else {
			//$product_img.tc_reset_attr( 'src' );
			$product_img.tc_reset_attr( 'title' );
			$product_img.tc_reset_attr( 'alt' );
			//$product_img.tc_reset_attr( 'srcset' );
			//$product_img.tc_reset_attr( 'sizes' );

			$product_img.tc_reset_attr( 'data-large-image' );
			if ( $product_img.data[ 'wc27_zoom_target' ] ) {
				$product_img.data[ 'wc27_zoom_target' ].tc_reset_attr( 'data-thumb' );
				$product_element.find( '.flex-control-nav li:eq(0) img' ).tc_reset_attr( 'src' );
			}

			$product_link.tc_reset_attr( 'href' );
			$product_link.tc_reset_attr( 'title' );
		}

	};

})( jQuery );