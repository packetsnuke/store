<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

/** Shortcode tc_epo_show (Used for echoing a custom action) **/
function tc_epo_show_shortcode( $atts, $content = NULL ) {
	extract( shortcode_atts( array(
		'action' => '',
	), $atts ) );

	ob_start();
	do_action( $action );

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

/** Shortcode tc_epo (Used for echoing options) **/
function tc_epo_shortcode( $atts, $content = NULL ) {
	extract( shortcode_atts( array(
		'id'     => '',
		'prefix' => '',
	), $atts ) );

	ob_start();

	if ( $id ) {
		TM_EPO()->tm_epo_fields( $id, $prefix, TRUE );
		TM_EPO()->tm_add_inline_style();
	}

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

/** Shortcode tc_epo_totals (Used for echoing options totals) **/
function tc_epo_totals_shortcode( $atts, $content = NULL ) {
	extract( shortcode_atts( array(
		'id'     => '',
		'prefix' => '',
	), $atts ) );

	ob_start();

	if ( $id ) {
		TM_EPO()->tm_epo_totals( $id, $prefix, TRUE );
	}

	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}

