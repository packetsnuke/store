<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('HOME_URL', trailingslashit(home_url()));
define('TP_THEME_DIR', trailingslashit(get_template_directory()));
define('TP_THEME_URI', trailingslashit(get_template_directory_uri()));
define('TP_CHILD_THEME_DIR', trailingslashit( get_stylesheet_directory() ) );
define('TP_CHILD_THEME_URI', trailingslashit( get_stylesheet_directory_uri() ) );
define('TP_THEME_FRAMEWORK_DIR', TP_THEME_DIR . 'framework/');
define('TP_THEME_FRAMEWORK_URI', TP_THEME_URI . 'framework/');
define('TP_FRAMEWORK_LIBS_DIR', TP_THEME_FRAMEWORK_DIR . 'libs/');
define('TP_FRAMEWORK_LIBS_URI', TP_THEME_FRAMEWORK_URI . 'libs/');
define('TP_FRAMEWORK_LESS_DIR', TP_THEME_FRAMEWORK_DIR . 'less/');
define('TP_FRAMEWORK_LESS_URI', TP_THEME_FRAMEWORK_URI . 'less/');
define('TP_FRAMEWORK_SCSS_DIR', TP_THEME_FRAMEWORK_DIR . 'scss/');

define('TP_THEME_LESS_DIR', TP_THEME_DIR . 'less/');

require_once( TP_FRAMEWORK_LIBS_DIR . 'titan-framework/titan-framework.php' );
require_once( TP_FRAMEWORK_LIBS_DIR . 'class-tp-themeoption-metabox.php' );

require ( TP_FRAMEWORK_LIBS_DIR . 'megamenu/class-megamenu.php');
require ( TP_FRAMEWORK_LIBS_DIR . 'class-tp-shortcodes.php');
require ( TP_FRAMEWORK_LIBS_DIR . 'class-tp-widgets.php');
include ( TP_FRAMEWORK_LIBS_DIR . 'theme-wrapper.php');

require TP_FRAMEWORK_LIBS_DIR . 'post-format/post-formats.php';

if( is_admin() ) {
	add_action( 'admin_enqueue_scripts', 'thim_admin_script_meta_box' );
	function thim_admin_script_meta_box() {
		wp_enqueue_script( 'thim-meta-boxes', TP_THEME_FRAMEWORK_URI . 'js/admin/meta-boxes.js', array( 'jquery' ), '', true );
	}
}
