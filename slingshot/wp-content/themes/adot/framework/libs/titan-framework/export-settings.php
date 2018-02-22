<?php

ob_start();

function thim_customizer_export_theme_settings() {
	$blogname  = strtolower( str_replace( ' ', '-', get_option( 'blogname' ) ) );
	$file_name = $blogname . '-thimtheme-' . date( 'Ydm' ) . '.json';
	$options   = get_theme_mods();

	unset( $options['nav_menu_locations'] );

	foreach ( $options as $key => $value ) {
		$value              = maybe_unserialize( $value );
		$need_options[$key] = $value;
	}

	$json_file = json_encode( $need_options );

	ob_clean();

	header( 'Content-Type: text/json; charset=' . get_option( 'blog_charset' ) );
	header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );

	echo ent2ncr($json_file);

	exit();
}