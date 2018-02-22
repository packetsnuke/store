<?php

/* Import/Export Customizer Setting */
if ( !function_exists( 'thim_cusomizer_upload_settings' ) ) :
	function thim_cusomizer_upload_settings() {
		WP_Filesystem();
		global $wp_filesystem;
		$file_name = $_FILES['thim-customizer-settings-upload']['name'];
		$file_ext  = pathinfo( $file_name, PATHINFO_EXTENSION );
		if ( $file_ext == 'json' ) {
			$encode_options = $wp_filesystem->get_contents( $_FILES['thim-customizer-settings-upload']['tmp_name'] );
			if ( !empty( $encode_options ) ) {
 				exit($encode_options);
			}
		}
		exit( '-1' );
	}
endif;
add_action( 'wp_ajax_thim_cusomizer_upload_settings', 'thim_cusomizer_upload_settings' );

if ( !function_exists( 'thim_ajax_get_attachment_url' ) ) :
	function thim_ajax_get_attachment_url() {
		check_ajax_referer( 'thim_customize_attachment', 'nonce' );

		if ( !isset( $_POST['attachment_id'] ) ) {
			exit();
		}
		$attachment_id = $_POST['attachment_id'];
		echo wp_get_attachment_url( $attachment_id );
		exit();
	}
endif;
add_action( 'wp_ajax_thim_ajax_get_attachment_url', 'thim_ajax_get_attachment_url' );
add_action( 'wp_ajax_nopriv_thim_ajax_get_attachment_url', 'thim_ajax_get_attachment_url' );

// Add Thim-Customizer Menu
if ( !function_exists( 'thim_add_customizer_menu' ) ) :
	function thim_add_customizer_menu() {
		add_submenu_page( 'options.php', '', '', 'edit_theme_options', 'export_settings', 'thim_customizer_export_theme_settings' );
	}
endif;
add_action( 'admin_menu', 'thim_add_customizer_menu' );

if ( !function_exists( 'thim_ajax_url' ) ) :
	function thim_ajax_url() {
		echo '<script type="text/javascript">
        var ajax_url ="' . get_site_url() . '/wp-admin/admin-ajax.php";
        var export_url = "' . get_site_url() . '/wp-admin/options.php?page=export_settings";
        </script>';
	}
endif;
add_action( 'wp_print_scripts', 'thim_ajax_url' );
/* End Import/Export Customizer Setting */