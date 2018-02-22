<?php

/**
 * ThimPress Framework
 *
 * A flexible WordPress Framework, created by TuanNV
 *
 * @author		TuanNV
 * @copyright	ThimPress.com
 * @link		http://thimpress.com
 * @since		2014
 * @package 	TPFramework
 * @version 	1.0
 */

/**
 * Put data to a file with WP_Filesystem
 *
 * @param $file
 * @param $data
 *
 * @return bool
 */
function thim_file_put_contents( $file, $data ) {

	WP_Filesystem();
	global $wp_filesystem;
	return $wp_filesystem->put_contents( $file, $data, FS_CHMOD_FILE );
}

/**
 * Get data from a file with WP_Filesystem
 *
 * @param $file
 *
 * @return bool
 */
function thim_file_get_contents( $file ) {

	WP_Filesystem();
	global $wp_filesystem;
	return $wp_filesystem->get_contents( $file );
}

/**
 * Export meta data for front page displays settings
 */
function thim_export_front_page_displays_settings() {

	$page_for_posts = get_option( 'page_for_posts' );
	$page_on_front  = get_option( 'page_on_front' );

	delete_post_meta_by_key( 'thim_page_for_posts' );
	delete_post_meta_by_key( 'thim_page_on_front' );

	if ( $page_for_posts ) {
		update_post_meta( $page_for_posts, 'thim_page_for_posts', 1 );
	}
	if ( $page_on_front ) {
		update_post_meta( $page_on_front, 'thim_page_on_front', 1 );
	}
}

add_action( 'export_wp', 'thim_export_front_page_displays_settings' );

//add script for admin
function custom_framework_enqueue( ) {
	wp_enqueue_style( 'thim-admin-custom-framework', TP_THEME_FRAMEWORK_URI . 'css/custom-framework.css' );
	wp_enqueue_script( 'thim-admin-custom-framewor', TP_THEME_FRAMEWORK_URI . 'js/custom-framework.js', array( 'jquery' ), '1.0', true );
}
add_action('admin_enqueue_scripts', 'custom_framework_enqueue');

//add script for frontend
function frontend_framework_enqueue( ) {
	//jsplayer
	wp_deregister_script( 'thim-jplayer' );
	wp_register_script( 'thim-jplayer', TP_THEME_FRAMEWORK_URI . 'js/jplayer/jquery.jplayer.min.js', array( 'jquery' ), '', true );
	wp_deregister_style( 'thim-pixel-industry' );
	wp_register_style( 'thim-pixel-industry', TP_THEME_FRAMEWORK_URI . 'js/jplayer/skin/pixel-industry/pixel-industry.css' );

	wp_enqueue_script( 'framework-bootstrap', TP_THEME_FRAMEWORK_URI . 'js/bootstrap.min.js', array( 'jquery' ), false, true );
	wp_enqueue_style( 'thim-awesome', TP_THEME_FRAMEWORK_URI . 'css/font-awesome.min.css', array() );
	wp_enqueue_style( 'thim-7-stroke', TP_THEME_FRAMEWORK_URI . 'css/pe-icon-7-stroke.css', array() );

}
add_action('wp_enqueue_scripts', 'frontend_framework_enqueue');

define('TP_FRAMEWORK_VERSION', "1.0");
include 'libs/tp-config.php';
