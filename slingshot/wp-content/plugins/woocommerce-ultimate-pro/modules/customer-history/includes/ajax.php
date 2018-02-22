<?php

/**
 * AJAX Helper to track user history.
 *
 * @since 1.2.0
 */
function wcch_ajax_track_history() {

	$page_url = isset( $_REQUEST['page_url'] ) ? esc_url( urldecode( $_REQUEST['page_url'] ) ) : false;
	$referrer = isset( $_REQUEST['referrer'] ) ? esc_url( urldecode( $_REQUEST['referrer'] ) ) : false;

	if ( $page_url ) {
		do_action( 'wcch_visited_url', $page_url, time(), $referrer );
	}

	wp_send_json_success( array( 'page_url' => $page_url ) );
}
add_action( 'wp_ajax_wcch_track_history', 'wcch_ajax_track_history' );
add_action( 'wp_ajax_nopriv_wcch_track_history', 'wcch_ajax_track_history' );
