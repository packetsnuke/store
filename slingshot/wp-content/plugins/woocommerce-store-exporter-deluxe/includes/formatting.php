<?php
// Format the raw memory data provided by PHP
function woo_ce_display_memory( $memory = 0 ) {

	$output = '-';
	if( !empty( $output ) )
		$output = sprintf( __( '%s MB', 'woocommerce-exporter' ), $memory );
	echo $output;

}

// Format the raw timestamps to something more friendly
function woo_ce_display_time_elapsed( $from, $to ) {

	$output = __( '1 second', 'woocommerce-exporter' );
	$time = $to - $from;
	$tokens = array (
		31536000 => __( 'year', 'woocommerce-exporter' ),
		2592000 => __( 'month', 'woocommerce-exporter' ),
		604800 => __( 'week', 'woocommerce-exporter' ),
		86400 => __( 'day', 'woocommerce-exporter' ),
		3600 => __( 'hour', 'woocommerce-exporter' ),
		60 => __( 'minute', 'woocommerce-exporter' ),
		1 => __( 'second', 'woocommerce-exporter' )
	);
	foreach( $tokens as $unit => $text ) {
		if( $time < $unit )
			continue;
		$numberOfUnits = floor( $time / $unit );
		$output = $numberOfUnits . ' ' . $text . ( ( $numberOfUnits > 1 ) ? 's' : '' );
	}
	return $output;

}

// Takes an array and formats it for the export
function woo_ce_escape_csv_array( $array = array(), $child = false, $escape = true ) {

	global $export;

	$output = '';
	if( !empty( $array ) ) {
		foreach( $array as $key => $value ) {
			$size = count( $value );
			if( is_array( $value ) && $size > 1 ) {
				$output .= '[' . $key . '] ';
				foreach( $value as $child_key => $child_value ) {
					$output .= $child_key . ': ' . woo_ce_escape_csv_array( $child_value, false, false ) . $export->category_separator;
				}
				$output .= $export->category_separator;
			} else {
				if( is_array( $value ) && $size == 1 ) {
					$keys = array_keys( $value );
					if( count( $keys ) == 1 )
						$value = $value[$keys[0]];
					unset( $keys );
				} else if( is_array( $value ) && empty( $value ) ) {
					$value = '';
				}
				$output .= '[' . $key . '] ' . $value . $export->category_separator;
			}
		}
		$output = substr( $output, 0, -1 );
	}
	$output = woo_ce_escape_csv_value( $output, $export->delimiter, $export->escape_formatting );
	return $output;

}

// Escape all cells in 'Excel' CSV escape formatting of a CSV file, also converts HTML entities to plain-text
function woo_ce_escape_csv_value( $string = '', $delimiter = ',', $format = 'all' ) {

	global $export;

	// Override the formatting for Debug Mode
	if( WOO_CD_DEBUG ) {
		$string = str_replace( '"', "'", $string );
		$string = str_replace( PHP_EOL, "", $string );
	} else {
		$string = str_replace( '"', '""', $string );
		$string = str_replace( PHP_EOL, "\r\n", $string );
	}
	$string = wp_specialchars_decode( $string );
	$string = str_replace( "&#8217;", "'", $string );
	// Override the escape formatting rule for XLS files
	if( $export->export_format == 'xls' )
		$format = 'all';
	switch( $format ) {

		case 'all':
			$string = '"' . $string . '"';
			break;

		case 'excel':
			if( strpos( $string, '"' ) !== false or strpos( $string, ',' ) !== false or strpos( $string, "\r" ) !== false or strpos( $string, "\n" ) !== false )
				$string = '"' . $string . '"';
			break;

	}
	return $string;

}

function woo_ce_wp_specialchars_decode( $string = '', $quote_style = 'ENT_QUOTES', $type = 'string' ) {

	if( $type == 'string' )
		$string = apply_filters( 'woo_ce_wp_specialchars_decode', trim( wp_specialchars_decode( $string, $quote_style ) ) );
	else
		$string = trim( $string );
	return $string;

}

function woo_ce_detect_value_string( $string = false, $format = 'boolean' ) {

	global $export;

	// Force all cells to be escaped
	if( $export->escape_formatting == 'all' ) {
		if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_detect_value_string', false ) )
			woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'Cell --> %s <-- is a string, reason: Escape formatting set to all', $string ) ) );
		if( $format == 'type' )
			return PHPExcel_Cell_DataType::TYPE_STRING;
		else
			return true;
	}

	// Force all cells to be not escaped
	if( $export->escape_formatting == 'none' ) {
		if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_detect_value_string', false ) )
			woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'Cell --> %s <-- is not a string, reason: Escape formatting set to none', $string ) ) );
		if( $format == 'type' )
			return PHPExcel_Cell_DataType::TYPE_STRING;
		else
			return;
	}

	// Check the contents of the cell and decide if it is a string
	if(
		strpos( $string, '"' ) !== false or 
		strpos( $string, ',' ) !== false or 
		strpos( $string, "\r" ) !== false or 
		strpos( $string, "\n" ) !== false or 
		substr( $string, 0, 1 ) == "=" or
		substr( $string, 0, 1 ) == "0" or
		is_numeric( $string ) == false
	) {
		if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_detect_value_string', false ) )
			woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'Cell --> %s <-- is a string, reason: Passed string validation', $string ) ) );
		if( $format == 'type' )
			return PHPExcel_Cell_DataType::TYPE_STRING;
		else
			return true;
	}

	// Check the contents of the cell and decide if it is a number
	if(
		is_numeric( $string )
	) {
		if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_detect_value_string', false ) )
			woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'Cell --> %s <-- is a number, reason: Passed number validation', $string ) ) );
		if( $format == 'type' )
			return PHPExcel_Cell_DataType::TYPE_NUMERIC;
		else
			return;
	}

	if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_detect_value_string', false ) )
		woo_ce_error_log( sprintf( 'Debug: %s', sprintf( 'Cell --> %s <-- is not a string, reason: Failed all string validation', $string ) ) );
	if( $format == 'type' )
		return PHPExcel_Cell_DataType::TYPE_STRING;
	else
		return;

}

function woo_ce_filter_attribute_escape( $safe_text = '', $text = '' ) {

	if( substr( $safe_text, 0, 1 ) == '=' ) {
		$safe_text = ltrim( $safe_text, '=' );
	}
	return $safe_text;

}

function woo_ce_filter_sanitize_key( $key ) {

	// Limit length of key to 48 characters
	$key = substr( $key, 0, 48 );
	return $key;

}

// Return the element count of an object
function woo_ce_count_object( $object = 0, $exclude_post_types = array() ) {

	$count = 0;
	if( is_object( $object ) ) {
		if( !empty( $exclude_post_types ) ) {
			$size = count( $exclude_post_types );
			for( $i = 0; $i < $size; $i++ ) {
				if( isset( $object->$exclude_post_types[$i] ) )
					unset( $object->$exclude_post_types[$i] );
			}
		}
		if( !empty( $object ) ) {
			foreach( $object as $key => $item )
				$count = $item + $count;
		}
	} else {
		$count = $object;
	}
	return $count;

}

function woo_ce_format_custom_meta( $custom_meta = false ) {

	global $export;

	$output = $custom_meta;
	if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_format_custom_meta', false ) )
		woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_format_custom_meta() > $custom_meta: ' . print_r( $custom_meta, true ) ) );

	// Check for empty array or object
	if(
		empty( $custom_meta ) && 
		(
			is_array( $custom_meta ) || 
			is_object( $custom_meta )
		)
	) {
		$output = false;
	}

	if( !empty( $custom_meta ) ) {
		$output = '';
		$custom_meta = maybe_unserialize( $custom_meta );
		// Convert object to array
		if( is_object( $custom_meta ) )
			$custom_meta = (array)$custom_meta;
		if( is_array( $custom_meta ) ) {
			if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_format_custom_meta', false ) )
				woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_format_custom_meta() > $custom_meta: ' . print_r( $custom_meta, true ) ) );
			$custom_meta = array_values( $custom_meta );
			if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_format_custom_meta', false ) )
				woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_format_custom_meta() > $custom_meta: ' . print_r( $custom_meta, true ) ) );
			$size = count( $custom_meta );
			if( WOO_CD_LOGGING && apply_filters( 'woo_ce_debug_format_custom_meta', false ) )
				woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_format_custom_meta() > $size: ' . $size ) );
			$multiple_value_separator = apply_filters( 'woo_ce_format_custom_meta_multiple_value_separator', "\n" );
			for( $i = 0; $i < $size; $i++ ) {
				if( is_array( $custom_meta[$i] ) ) {
					$output .= implode( $multiple_value_separator, $custom_meta[$i] ) . $export->category_separator;
				} else {
					$output .= $custom_meta[$i] . $export->category_separator;
				}
			}
			$output = substr( $output, 0, -1 );
		} else {
			$output = $custom_meta;
		}
	}
	return $output;

}

// Takes an array or comma separated string and returns an export formatted string 
function woo_ce_convert_product_ids( $product_ids = null ) {

	global $export;

	$output = '';
	if( $product_ids !== null ) {
		if( is_array( $product_ids ) ) {
			$size = count( $product_ids );
			for( $i = 0; $i < $size; $i++ )
				$output .= $product_ids[$i] . $export->category_separator;
			$output = substr( $output, 0, -1 );
		} else if( strstr( $product_ids, ',' ) ) {
			$output = str_replace( ',', $export->category_separator, $product_ids );
		}
	}
	return $output;

}

// Format the raw post_status
function woo_ce_format_post_status( $post_status = '' ) {

	$output = $post_status;
	switch( $post_status ) {

		case 'publish':
			$output = __( 'Publish', 'woocommerce-exporter' );
			break;

		case 'draft':
			$output = __( 'Draft', 'woocommerce-exporter' );
			break;

		case 'pending':
			$output = __( 'Pending', 'woocommerce-exporter' );
			break;

		case 'private':
			$output = __( 'Private', 'woocommerce-exporter' );
			break;

		case 'trash':
			$output = __( 'Trash', 'woocommerce-exporter' );
			break;

	}
	$output = apply_filters( 'woo_ce_format_post_status', $output, $post_status );
	return $output;

}

// Format the raw comment_status
function woo_ce_format_comment_status( $comment_status ) {

	$output = $comment_status;
	switch( $comment_status ) {

		case 'open':
			$output = __( 'Open', 'woocommerce-exporter' );
			break;

		case 'closed':
			$output = __( 'Closed', 'woocommerce-exporter' );
			break;

	}
	return $output;

}

function woo_ce_format_switch( $input = '', $output_format = 'answer' ) {

	$input = strtolower( $input );
	switch( $input ) {

		case '1':
		case 'y':
		case 'yes':
		case 'on':
		case 'open':
		case 'active':
			$input = '1';
			break;

		case '0':
		case 'n':
		case 'no':
		case 'off':
		case 'closed':
		case 'inactive':
		default:
			$input = '0';
			break;

	}
	$output = '';
	switch( $output_format ) {

		case 'int':
			$output = $input;
			break;

		case 'answer':
			switch( $input ) {

				case '1':
					$output = __( 'Yes', 'woocommerce-exporter' );
					break;

				case '0':
					$output = __( 'No', 'woocommerce-exporter' );
					break;

			}
			break;

		case 'boolean':
			switch( $input ) {

				case '1':
					$output = 'on';
					break;

				case '0':
					$output = 'off';
					break;

			}
			break;

	}
	return $output;

}

function woo_ce_format_product_filters( $product_filters = array() ) {

	$output = array();
	if( !empty( $product_filters ) ) {
		$size = count( $product_filters );
		if( $size == 1 ) {
			// Check if we are dealing with an empty array or zero key
			if( isset( $product_filters[0] ) && $product_filters[0] == '' ) {
				return;
			// Check if we are dealing with Select2 Enhanced
			} else if( isset( $product_filters[0] ) && strpos( $product_filters[0], ',' ) !== false ) {
				$product_filters = explode( ',', $product_filters[0] );
			}
		}
		foreach( $product_filters as $product_filter )
			$output[] = $product_filter;
	}
	return $output;

}

function woo_ce_format_user_role_filters( $user_role_filters = array() ) {

	$output = array();
	if( !empty( $user_role_filters ) ) {
		foreach( $user_role_filters as $user_role_filter )
			$output[] = $user_role_filter;
	}
	return $output;

}

// Allow store owners to create their own woo_ce_format_price() as needed
if( !function_exists( 'woo_ce_format_price' ) ) {
	function woo_ce_format_price( $price = '', $currency = '' ) {

		// Check that a valid price has been provided and that wc_price() exists
		if( $price !== false && function_exists( 'wc_price' ) ) {
			// WooCommerce adds currency formatting to the price, let's not do that
			add_filter( 'wc_price', 'woo_ce_filter_wc_price', 10, 3 );
			add_filter( 'formatted_woocommerce_price', 'woo_ce_formatted_woocommerce_price', 10, 5 );
			add_filter( 'woocommerce_currency_symbol', 'woo_ce_woocommerce_currency_symbol', 10, 2 );
			$price = wc_price( $price, array( 'currency' => $currency ) );
			remove_filter( 'formatted_woocommerce_price', 'woo_ce_formatted_woocommerce_price' );
			remove_filter( 'wc_price', 'woo_ce_filter_wc_price' );
			remove_filter( 'woocommerce_currency_symbol', 'woo_ce_woocommerce_currency_symbol' );
		}
		return $price;

	}
}

function woo_ce_filter_wc_price( $return, $price ) {

	// Check price for negative values; weird method but neccesary
	if( strstr( $return, '<span class="woocommerce-Price-amount amount">-' ) )
		$price = '-' . $price;

	return $price;

}

function woo_ce_formatted_woocommerce_price( $return, $price, $num_decimals, $decimal_sep, $thousands_sep ) {

	$decimal_sep = apply_filters( 'woo_ce_wc_price_decimal_sep', $decimal_sep );
	$thousands_sep = apply_filters( 'woo_ce_wc_price_thousands_sep', $thousands_sep );

	$price = number_format( $price, $num_decimals, $decimal_sep, $thousands_sep );
	return $price;

}

// Strip the currency symbol from the price
function woo_ce_woocommerce_currency_symbol( $currency_symbol, $currency ) {

	$currency_symbol = apply_filters( 'woo_ce_wc_price_currency_symbol', '', $currency_symbol, $currency );
	return $currency_symbol;

}

function woo_ce_format_date( $date = '', $format = '' ) {

	$output = $date;
	$date_format = woo_ce_get_option( 'date_format', 'd/m/Y' );
	if( !empty( $format ) )
		$date_format = $format;
	if( !empty( $date ) && $date_format != '' ) {
/*
		// @mod - Add support for time formatting in 2.4+
		if( function_exists( 'wc_format_datetime' ) )
			$output = wc_format_datetime( $date, get_option( 'time_format' ) );
*/
		$output = mysql2date( $date_format, $date );
	}
	return $output;

}

// Take our pretty slashed date format and make it play nice with strtotime() and date()
function woo_ce_format_order_date( $date = '', $format = 'export' ) {

	$output = $date;
	if( !empty( $date ) ) {
		switch( $format ) {

			case 'export':
				$output = str_replace( '/', '-', $date );
				break;

			case 'save':
				$output = str_replace( '-', '/', $date );
				break;

			case 'joined':
				$output = str_replace( '-', '', $date );
				break;

		}
	}
	return $output;

}

function woo_ce_validate_order_date( $date, $format = 'd/m/Y' ) {

	$d = DateTime::createFromFormat( $format, $date );
	return $d && $d->format( $format ) == $date;

}

function woo_ce_format_archive_date( $post_ID = 0, $time = false ) {

	if( $time == false )
		$time = get_post_time( 'G', true, $post_ID, false );
	if( ( abs( $t_diff = time() - $time ) ) < 86400 )
		$post_date = sprintf( __( '%s ago' ), human_time_diff( $time ) );
	else
		$post_date = mysql2date( get_option( 'date_format', 'd/m/Y' ), date( 'Y/m/d', $time ) );
	unset( $time );
	return $post_date;

}

function woo_ce_format_ip_address( $ip = '' ) {

	// Check if the IP Address is just a loopback
	if( in_array( $ip, array( '::1', '127.0.0.1', 'localhost' ) ) )
		$ip = '';
	return $ip;

}

function woo_ce_format_export_method( $export_method = '' ) {

	$output = '';
	switch( $export_method ) {

		case 'archive':
		default:
			$output = __( 'Archive to WordPress Media', 'woocommerce-exporter' );
			break;

		case 'save':
			$output = __( 'Save to this server', 'woocommerce-exporter' );
			break;

		case 'email':
			$output = __( 'Send as e-mail', 'woocommerce-exporter' );
			break;

		case 'post':
			$output = __( 'POST to remote URL', 'woocommerce-exporter' );
			break;

		case 'ftp':
			$output = __( 'Upload to remote FTP/SFTP', 'woocommerce-exporter' );
			break;

		case 'google_sheets':
			$output = __( 'Save to Google Sheets', 'woocommerce-exporter' );
			break;

	}
	return $output;

}

function woo_ce_format_archive_method( $export_method = '' ) {

	$output = '';
	switch( $export_method ) {

		case 'archive':
			$output = __( 'saved to WordPress Media', 'woocommerce-exporter' );
			break;

		case 'download':
		case 'file':
		case 'url':
		case 'raw':
		case 'gui':
			$output = __( 'downloaded via CRON', 'woocommerce-exporter' );
			break;

		case 'email':
			$output = sprintf( __( 'sent via %s', 'woocommerce-exporter' ), 'e-mail' );
			break;

		default:
			if( in_array( $export_method, array( 'ftp', 'post' ) ) )
				$export_method = strtoupper( $export_method );
			$output = sprintf( __( 'exported to %s', 'woocommerce-exporter' ), $export_method );
			if( $export_method == 'save' )
				$output = __( 'saved to this server', 'woocommerce-exporter' );
			break;

	}
	return $output;

}

function woo_ce_format_product_category_label( $product_category = '', $parent_category = '' ) {

	$output = $product_category;
	if( !empty( $parent_category ) )
		$output .= ' &raquo; ' . $parent_category;
	return $output;

}

function woo_ce_clean_export_label( $label = '' ) {

	// If the first character is an underscore remove it
	if( $label[0] === '_' )
		$label = substr( $label, 1 );
	// Replace any underscores with spaces
	$label = str_replace( '_', ' ', $label );
	// Auto-capitalise label
	$label = ucfirst( $label );
	return $label;

}

if( !function_exists( 'woo_ce_expand_state_name' ) ) {
	function woo_ce_expand_state_name( $country_prefix = '', $state_prefix = '' ) {

		global $woocommerce;

		$output = $state_prefix;
		if( $output ) {
			if( isset( $woocommerce->countries ) ) {
				if( $states = $woocommerce->countries->get_states( $country_prefix ) ) {
					if( isset( $states[$state_prefix] ) )
						$output = $states[$state_prefix];
				}
				unset( $states );
			}
		}
		return $output;

	}
}

if( !function_exists( 'woo_ce_expand_country_name' ) ) {
	function woo_ce_expand_country_name( $country_prefix = '' ) {

		$output = $country_prefix;
		if( !empty( $output ) && class_exists( 'WC_Countries' ) ) {
			if( $countries = woo_ce_allowed_countries() ) {
				if( isset( $countries[$country_prefix] ) )
					$output = $countries[$country_prefix];
			}
			unset( $countries );
		}
		return $output;

	}
}

function woo_ce_allowed_countries() {

	if( apply_filters( 'woo_ce_override_wc_countries', true ) ) {
		if( class_exists( 'WC_Countries' ) ) {
			$countries = new WC_Countries();
			if( method_exists( $countries, 'get_allowed_countries' ) ) {
				$countries = $countries->get_allowed_countries();
				return $countries;
			}
		}
	}

}

function woo_ce_format_description_excerpt( $string = '' ) {

	if( $description_excerpt_formatting = woo_ce_get_option( 'description_excerpt_formatting', 0 ) ) {
		$string = wp_kses( $string, apply_filters( 'woo_ce_format_description_excerpt_allowed_html', array() ), apply_filters( 'woo_ce_format_description_excerpt_allowed_protocols', array() ) );
	}
	return apply_filters( 'woo_ce_format_description_excerpt', $string );

}

function woo_ce_format_ftp_host( $host = '' ) {

	// Strip out the ftp:// or other protocols that may be entered
	$host = str_replace( array( 'ftp://', 'ftps://', 'http://', 'https://' ), '', $host );
	return $host;

}

function woo_ce_format_email_contents_allowed_html() {

	$output = array(
		'a' => array(
			'href' => array(),
			'title' => array()
		),
		'br' => array(),
		'em' => array(),
		'strong' => array(),
	);
	$output = apply_filters( 'woo_ce_format_email_contents_allowed_html', $output );
	return $output;

}

function woo_ce_format_email_contents_allowed_protocols() {

	$output = false;
	$output = apply_filters( 'woo_ce_format_email_contents_allowed_protocols', $output );
	return $output;

}

function woo_ce_format_post_title( $title = '' ) {
	
	if( empty( $title ) )
		$title = __( '(no title)', 'woocommerce-exporter' );
	return $title;

}

function woo_ce_format_wpseo_noindex( $noindex = '' ) {

	$output = $noindex;
	if( !empty( $noindex ) && $noindex !== '0' ) {
		switch( $noindex ) {

			case '0':
			case 'default':
			default:
				$output = __( 'Default', 'woocommerce-exporter' );
				break;

			case '2':
			case 'index':
				$output = __( 'Always index', 'woocommerce-exporter' );
				break;

			case '1':
			case 'noindex':
				$output = __( 'Always noindex', 'woocommerce-exporter' );
				break;

		}
	}
	return $output;

}

function woo_ce_format_wpseo_sitemap_include( $sitemap_include = '' ) {

	$output = $sitemap_include;
	if( !empty( $sitemap_include ) ) {
		switch( $sitemap_include ) {

			case '-':
			default:
				$output = __( 'Auto-detect', 'woocommerce-exporter' );
				break;

			case 'always':
				$output = __( 'Always include', 'woocommerce-exporter' );
				break;

			case 'never':
				$output = __( 'Never include', 'woocommerce-exporter' );
				break;

		}
	}
	return $output;

}

function woo_ce_format_wpseo_follow( $follow = '' ) {

	$output = $follow;
	if( !empty( $follow ) && $follow !== '0' ) {
		switch( $follow ) {

			case '0':
			default:
				$output = __( 'follow', 'woocommerce-exporter' );
				break;

			case '1':
				$output = __( 'nofollow', 'woocommerce-exporter' );
				break;

		}
	}
	return $output;

}
?>