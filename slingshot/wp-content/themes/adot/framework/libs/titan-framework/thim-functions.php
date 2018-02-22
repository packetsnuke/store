<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of thim-functions
 *
 * @author Tuannv
 */
require_once( TF_PATH . 'thim-class-meta-box.php' );
require_once( TF_PATH . 'thim-class-customizer-section.php' );
require_once( TF_PATH . 'class-option-import.php' );

add_action( 'wp_ajax_tp_make_site', 'tp_make_site_callback' );
function tp_make_site_callback() {
	if ( current_user_can( 'manage_options' ) ) {
		require_once TP_FRAMEWORK_LIBS_DIR . 'import/tp-import.php';
		die;
	}
}