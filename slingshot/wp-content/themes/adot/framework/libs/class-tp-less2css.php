<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
defined( 'ABSPATH' ) or die();
/**
 * Description of customizer-options
 *
 * @author Tuannv
 */
require_once( TP_FRAMEWORK_LIBS_DIR . "less/lessc.inc.php");

class Thim_Less2Css {
    
	function __construct() {
        	add_action('customize_save_after', array($this, 'generate_to_css'));
	}

	function generate_to_css() {
		$options 		= get_theme_mods();
		$less_variables = thim_get_theme_option_variables($options);
		thim_generate_less2css( get_template_directory() . DIRECTORY_SEPARATOR . 'style', '.css', $less_variables );
	}
}


function thim_generate_less2css( $fileout, $type, $less_variables=array(), $compile_file='' ) {
	if(!$compile_file){
		$compile_file = TP_THEME_DIR . 'less'.DIRECTORY_SEPARATOR.'theme-options.less';
	}

	$css = "";

	WP_Filesystem();
	global $wp_filesystem;
	
	$compiler = new lessc;
	$compiler->setFormatter('compressed');
	
	// set less varivalbles
	$compiler->setVariables($less_variables);
	
	// chose file less to compile
	$css .= $compiler->compileFile($compile_file);

	// get customcss
	$css .= thim_get_customcss();
	
	// minifile css
	$regex = array(
		"`^([\t\s]+)`ism" => '',
		"`^\/\*(.+?)\*\/`ism" => "",
		"`([\n\A;]+)\/\*(.+?)\*\/`ism" => "$1",
		"`([\n\A;\s]+)//(.+?)[\n\r]`ism" => "$1\n",
		"`(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+`ism" => "",
		"/\n/i" => ""
	);
	$css = preg_replace(array_keys($regex), $regex, $css);
	
	
	/***********************************
	 * 		Create style.css file
	 ***********************************/
	$style = thim_file_get_contents(TP_THEME_DIR . "inc/theme-info.txt");
	// Determine whether Multisite support is enabled
	if (is_multisite()) {
		// Write Theme Info into style.css
		if (!file_put_contents($fileout . $type, $style, LOCK_EX)) {
			@chmod( $fileout.$type, 0777 );
			file_put_contents($fileout . $type, $style, LOCK_EX);
		}

		// Write the rest to specific site style-ID.css
		$fileout = $fileout . '-' . get_current_blog_id();
		if (!file_put_contents($fileout . $type, $css, FILE_APPEND)) {
			@chmod( $fileout.$type, 0777 );
			file_put_contents($fileout . $type, $css, FILE_APPEND);
		}
	} else {
		// If this is not multisite, we write them all in style.css file
		$style .= $css;
		if (!file_put_contents($fileout . $type, $style, LOCK_EX)) {
			@chmod( $fileout.$type, 0777 );
			file_put_contents($fileout . $type, $style, LOCK_EX);
		}
	}
}

function thim_get_customcss() {
	$theme_custom_css_path = get_template_directory().DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'theme-custom-css.php';
	$custom_css = '';
	ob_start();
	if( is_file( $theme_custom_css_path ) ) {
		include $theme_custom_css_path;
	}
	$custom_css = ob_get_contents();
	ob_clean();
	return $custom_css;
}

function thim_get_theme_option_variables( $options ) {
	$less_variables = array();
	$theme_options_variation = array();
	$theme_options_to_css_path = get_template_directory().DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'theme-option-variables.php';
	if( is_file( $theme_options_to_css_path ) ) {
		require_once( $theme_options_to_css_path );
	}
	foreach ( $theme_options_variation AS $key ) {
		if(!isset($options[$key])){
			continue;
		}
		$option_data = $options[$key];
		//$options[$key] is serialize
		if ( is_serialized( $option_data ) || is_array( $option_data ) ) {
			$font_variables = thim_get_font_variables( $option_data, $key );
			$less_variables = array_merge($less_variables, $font_variables);
		} else {
			$less_variables[$key] = $option_data;
		}
	}
	return $less_variables;
}

function thim_get_font_variables( $data, $key ) {
	//is_serialized
	$value = '';
	$less_variables = array();
	if ( is_serialized( $data ) ) {
		$data = unserialize( $data );
	}
	if ( isset( $data['font-family'] ) ) {
		$less_variables[$key.'_font_family'] = $data['font-family'];
	}
	if ( isset( $data['color-opacity'] ) ) {
		$less_variables[$key.'_color'] = $data['color-opacity'];
	}
	if ( isset( $data['font-weight'] ) ) {
		$less_variables[$key.'_font_weight'] = $data['font-weight'];
	}
	if ( isset( $data['font-style'] ) ) {
		$less_variables[$key.'_font_style'] = $data['font-style'];
	}
	if ( isset( $data['text-transform'] ) ) {
		$less_variables[$key.'_text_transform'] = $data['text-transform'];
	}
	if ( isset( $data['font-size'] ) ) {
		$less_variables[$key.'_font_size'] = $data['font-size'];
	}
	if ( isset( $data['line-height'] ) ) {
		$less_variables[$key.'_line_height'] = $data['line-height'];
	}
	return $less_variables;
}

new Thim_Less2Css();
// get data customizer
global $theme_options_data;
$theme_options_data = get_theme_mods();
