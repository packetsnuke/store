(function ( $ ) {
	"use strict";
	/**
	 * Duplicated from q-translate-x
	 */
	String.prototype.tm_xsplit = function ( _regEx ) {
		// Most browsers can do this properly, so let them work, they'll do it faster
		if ( 'a~b'.split( /(~)/ ).length === 3 ) {
			return this.split( _regEx );
		}

		if ( ! _regEx.global ) {
			_regEx = new RegExp( _regEx.source, 'g' + (_regEx.ignoreCase ? 'i' : '') );
		}

		// IE (and any other browser that can't capture the delimiter)
		// will, unfortunately, have to be slowed down
		var start = 0, arr = [];
		var result;
		while ( (result = _regEx.exec( this )) != null ) {
			arr.push( this.slice( start, result.index ) );
			if ( result.length > 1 ) arr.push( result[ 1 ] );
			start = _regEx.lastIndex;
		}
		if ( start < this.length ) {
			arr.push( this.slice( start ) );
		}
		if ( start == this.length ) {
			arr.push( '' );
		}
		return arr;
	};

	$.qtranxj_split = function ( text ) {
		var blocks = qtranxj_get_split_blocks( text );
		return qtranxj_split_blocks( blocks );
	}

	var qtranxj_get_split_blocks = function ( text ) {
		var split_regex = /(<!--:[a-z]{2}-->|<!--:-->|\[:[a-z]{2}\]|\[:\])/gi;
		return text.tm_xsplit( split_regex );
	}

	var qtranxj_split_blocks = function ( blocks ) {
		var result = new Object;
		for ( var i = 0; i < tm_epo_q_translate_x_clogic_js.enabled_languages.length; ++ i ) {
			var lang = tm_epo_q_translate_x_clogic_js.enabled_languages[ i ];
			result[ lang ] = '';
		}

		if ( ! blocks || ! blocks.length ) {
			return result;
		}
		if ( blocks.length == 1 ) {//no language separator found, enter it to all languages
			var b = blocks[ 0 ];
			for ( var j = 0; j < tm_epo_q_translate_x_clogic_js.enabled_languages.length; ++ j ) {
				var lang = tm_epo_q_translate_x_clogic_js.enabled_languages[ j ];
				result[ lang ] += b;
			}
			return result;
		}
		var clang_regex = /<!--:([a-z]{2})-->/gi;
		var blang_regex = /\[:([a-z]{2})\]/gi;

		var lang = false;
		var matches;
		for ( var i = 0; i < blocks.length; ++ i ) {
			var b = blocks[ i ];

			if ( ! b.length ) {
				continue;
			}
			matches = clang_regex.exec( b );
			clang_regex.lastIndex = 0;
			if ( matches != null ) {
				lang = matches[ 1 ];
				continue;
			}
			matches = blang_regex.exec( b );
			blang_regex.lastIndex = 0;
			if ( matches != null ) {
				lang = matches[ 1 ];
				continue;
			}
			if ( b == '<!--:-->' || b == '[:]' ) {// || b == '{:}' ){
				lang = false;
				continue;
			}
			if ( lang ) {
				result[ lang ] += b;
				lang = false;
			} else {//keep neutral text
				for ( var key in result ) {
					result[ key ] += b;
				}
			}
		}
		return result;
	}
})( jQuery );