<?php

/**
 * This class generates custom CSS into static CSS file in uploads folder
 * and enqueue it in the frontend
 *
 * CSS is generated only when theme options is saved (changed)
 * Works with LESS (for unlimited color schemes)
 *
 *
 */
require_once( TP_FRAMEWORK_LIBS_DIR . "less/lessc.inc.php");
require_once( TP_THEME_DIR . "inc/admin/theme-options-to-css.php" );

function generate($fileout, $type, $theme_option_variations) {
	$css = "";
	$regex = array(
		"`^([\t\s]+)`ism" => '',
		"`^\/\*(.+?)\*\/`ism" => "",
		"`([\n\A;]+)\/\*(.+?)\*\/`ism" => "$1",
		"`([\n\A;\s]+)//(.+?)[\n\r]`ism" => "$1\n",
		"`(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+`ism" => "",
		"/\n/i" => ""
	);

	$compiler = new lessc;
	$compiler->setFormatter('compressed');
	$css .= $compiler->compileFile(TP_THEME_DIR . 'less/style.less');

	$css .= customcss();

	//$css = preg_replace(array_keys($regex), $regex, $css);
	$style = file_get_contents(TP_THEME_DIR . "inc/theme-info.txt");
	// Determine whether Multisite support is enabled
        $style .= $css;

	if (is_multisite()) {
		// Write Theme Info into style.css
		if (!file_put_contents($fileout . $type, $style, LOCK_EX)) {
			@chmod( $fileout.$type, 0777 );
			file_put_contents($fileout . $type, $style, LOCK_EX);
		}

		// Write the rest to specific site style-ID.css
		$fileout = $fileout . '-' . get_current_blog_id();
		if (!file_put_contents($fileout . $type, $style, LOCK_EX)) {
			@chmod( $fileout.$type, 0777 );
			file_put_contents($fileout . $type, $style, LOCK_EX);
		}
	} else {
		// If this is not multisite, we write them all in style.css file
		if (!file_put_contents($fileout . $type, $style, LOCK_EX)) {
			@chmod( $fileout.$type, 0777 );
			file_put_contents($fileout . $type, $style, LOCK_EX);
		}
	}
}

function collect_content_files($folder, $file_type) {
	$files = scandir($folder);
	$content_file = '';
	foreach ($files as $file) {
		if (strpos($file, $file_type) !== false) {
			$content_file .= file_get_contents($folder . $file);
		}
	}
	return $content_file;
}

function generate_less_to_css($less_folder, $params, $ignore_files) {
	$files = scandir($less_folder);
	$css = '';
	$content_file_options = "";
	foreach ($files as $file) {
		if (strpos($file, 'less') !== false) {
			if (exist_in_array($ignore_files, $file) == true)
				continue;
			$content_file_options = $params;
			$content_file_options .= file_get_contents($less_folder . $file);
			$compiler = new lessc;
			$compiler->setFormatter('compressed');
			$css .= $compiler->compile($content_file_options);
			$content_file_options = "";
		}
	}
	return $css;
}

function generate_scss_to_css($scss_folder, $params, $ignore_files) {
	$files = scandir($scss_folder);
	$css = '';
	$content_file_options = "";
	foreach ($files as $file) {
		if (strpos($file, 'scss') !== false) {
			if (exist_in_array($ignore_files, $file) == true)
				continue;
			$content_file_options = $params;
			$content_file_options .= file_get_contents($scss_folder . $file);
			$compiler = new scssc();
			$css .= $compiler->compile($content_file_options);
			$content_file_options = "";
		}
	}
	return $css;
}

//function compile_bootstrap($file_path) {
//	$compiler = new scssc();
//	return $compiler->compileImport($file_path);
//}

function exist_in_array($array, $string) {
	if (count($array) > 0) {
		foreach ($array as $item) {
			if ($item == $string) {
				return true;
			}
		}
	}
	return false;
}