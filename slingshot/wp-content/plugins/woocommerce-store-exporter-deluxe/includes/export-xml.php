<?php
function woo_ce_is_xml_cdata( $string = '', $export_type = '', $field = '' ) {

	if( strlen( $string ) == 0 )
		return;
	if( !empty( $string ) && seems_utf8( trim( $string ) ) == false || preg_match( '!.!u', trim( $string ) ) == false || strpos( $string, '&nbsp;' ) !== false )
		return true;
	if( !empty( $export_type ) && !empty( $field ) ) {
		// Force these fields to export as CDATA
		if( $export_type == 'product' && $field == 'category' )
			return true;
	}

}

function woo_ce_sanitize_xml_string( $string = '' ) {

	global $export;

	$string = str_replace( array( "\r", PHP_EOL ), "", $string );
	$string = preg_replace( '/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $string );
	if( function_exists( 'mb_convert_encoding' ) ) {
		$to_encoding = $export->encoding;
		$from_encoding = 'auto';
		if( !empty( $to_encoding ) )
			$string = mb_convert_encoding( trim( $string ), $to_encoding, $from_encoding );
		if( $to_encoding <> 'UTF-8' ) {
			if( function_exists( 'utf8_encode' ) )
				$string = utf8_encode( $string );
		}
	}
	return $string;

}

// Function to generate a valid XML file
function woo_ce_format_xml( $xml = null ) {

	if( isset( $xml ) && is_object( $xml ) ) {
		$dom = dom_import_simplexml( $xml )->ownerDocument;
		if( $dom !== false ) {
			$dom->formatOutput = true;
			return $dom->saveXML();
		}
	}

}

// Check that the SimpleXMLElement Class is available and that our Class has not been loaded
if( class_exists( 'SimpleXMLElement' ) && !class_exists( 'SED_SimpleXMLElement' ) ) {
	class SED_SimpleXMLElement extends SimpleXMLElement {
		public function addCData( $string ) {

			$node = dom_import_simplexml( $this );
			$no = $node->ownerDocument; 
			$node->appendChild( $no->createCDATASection( $string ) );

		}
	}
}
?>