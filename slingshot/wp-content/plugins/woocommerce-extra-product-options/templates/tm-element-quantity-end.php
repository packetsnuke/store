<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

if ( !empty( $quantity ) ) {

	echo '</div>';

	if ( strtolower( $quantity ) == "bottom" && isset( $qty_html ) ) {

		echo $qty_html;

	}

}