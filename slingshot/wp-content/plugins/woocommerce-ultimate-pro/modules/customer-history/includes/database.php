<?php

/**
 * Create custom database table.
 *
 * This function runs every time the wcch_plugin_update hook fires.
 * Because it uses dbDelta(), WP will only modify the database if
 * it detects a change from the current database schema.
 *
 * See https://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table
 *
 * @since 1.2.0
 */
function wcch_setup_custom_table() {
	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$sql = "
		CREATE TABLE {$wpdb->prefix}wcch_page_history (
			user_hash varchar(23) NOT NULL,
			page_history longtext,
			last_updated timestamp NOT NULL,
			PRIMARY KEY  (user_hash)
		);
		";

	dbDelta( $sql );
}
add_action( 'wcch_plugin_update', 'wcch_setup_custom_table' );

/**
 * Establish daily schedule for garbage collection.
 *
 * Hooked to plugin activation.
 *
 * @since 1.2.0
 */
function wcch_schedule_garbage_collection() {
	if ( ! wp_next_scheduled( 'wcch_garbage_collection' ) ) {
		wp_schedule_event( time(), 'daily', 'wcch_garbage_collection' );
	}
}

/**
 * Clear scheduled garbage collection.
 * Hooked to plugin deactivation.
 *
 * @since 1.2.0
 */
function wcch_unschedule_garbage_collection() {
	wp_clear_scheduled_hook( 'wcch_garbage_collection' );
}

/**
 * Delete any stored history that is greater than 1 week old.
 *
 * @since 1.2.0
 */
function wcch_garbage_collection() {
	global $wpdb;
	$wpdb->query( $wpdb->prepare(
		"
		DELETE FROM {$wpdb->prefix}wcch_page_history
		WHERE last_updated <= %s
		",
		date( 'Y-m-d H:i:s', time( '-1 week' ) )
	) );
}
// add_action( 'wcch_garbage_collection', 'wcch_garbage_collection' );

/**
 * Get a user's page history.
 *
 * @since  1.2.0
 *
 * @param  string $user_hash User hash.
 * @return array             Page history (or empty array).
 */
function wcch_get_page_history( $user_hash = '' ) {
	global $wpdb;

	$result = $wpdb->get_var( $wpdb->prepare(
		"
		SELECT page_history
		FROM {$wpdb->prefix}wcch_page_history
		WHERE user_hash = %s
		",
		$user_hash
	) );

	$result = empty( $result ) ? array() : json_decode( $result );

	return $result;
}

/**
 * Store user's history to database.
 *
 * @since 1.2.0
 *
 * @param string $user_hash    User hash.
 * @param array  $page_history Browsing history.
 */
function wcch_set_page_history( $user_hash = '', $page_history = array() ) {
	global $wpdb;
	$wpdb->replace(
		$wpdb->prefix . 'wcch_page_history',
		array(
			'user_hash' => $user_hash,
			'page_history' => json_encode( $page_history ),
			'last_updated' => date( 'Y-m-d H:i:s' ),
		),
		array(
			'%s',
			'%s',
			'%s',
		)
	);
}

/**
 * Delete a user's page history.
 *
 * @since 1.2.0
 *
 * @param string $user_hash User hash.
 */
function wcch_delete_page_history( $user_hash = '' ) {
	global $wpdb;
	$wpdb->delete(
		$wpdb->prefix . 'wcch_page_history',
		array( 'user_hash' => $user_hash ),
		array( '%s' )
	);
}
