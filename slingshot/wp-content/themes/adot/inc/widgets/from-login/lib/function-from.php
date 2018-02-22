<?php
function thim_ob_ajax_url() {
	echo '<script type="text/javascript">
		var thim_ob_ajax_url ="' . get_site_url() . '/wp-admin/admin-ajax.php";
		</script>';
}

add_action( 'wp_print_scripts', 'thim_ob_ajax_url' );

function thim_social_login_callback() {
	$html = '<div id="thim-popup-login-wrapper">
                <div class="thim-popup-login-bg"></div>
                <div class="thim-popup-login-container">
                    <div class="thim-popup-login-container-inner">
                        <div class="thim-popup-login">
                            <button class="thim-popup-login-close" type="button" title="Close (Esc)">x</button>
                            <div class="col-sm-6 right">
                              <h2>' . __( "Login", "thim" ) . '</h2>
                                <form id="thim-popup-login-form">
                                <input type="hidden" name="nonce" value="' . wp_create_nonce( 'thim-login-nonce' ) . '" />
                                    <div class="thim-popup-login-content">
                                    	<p class="login-message">' . __( "I am a returning customer", 'thim' ) . '</p>
                                         <p>
                                        <label for="user_login">
                                            <input id="user_login" type="text" name="username" required="required">
                                        </label>
                                        </p>
                                        <label for="user_pass">
                                            <input id="user_pass" type="password" name="password" required="required">
                                        </label>
                                        <label><input type="checkbox" name="remember"/> ' . __( "Remember password", "thim" ) . '</label>
                                        <br>
                                        <input type="hidden" name="action" value="thim_login_ajax"/>
                                        <div class="form_submit">
                                            <input type="submit" value="Log In" class="sc-btn thim-popup-login-button" id="wp-submit" name="submit">
                                            <span class="arrow">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="12" viewBox="-30 0 52 12">
                                                    <path d="M22 6l-6-6v5h-46v2h46v5l6-6z" fill="#fff"></path>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </form>
    ';
	$html .= '</div>';
	$html .= '<div class="col-sm-6 left">
                <h2>' . __( "Register Account", "thim" ) . '</h2>
                        <div class="thim-popup-login-content">
                            <p>' . __( "By creating an account you will be able to shop faster, be up to date on an order status, and keep track of the orders you have previously made.", "thim" ) . '</p>
                            <a class="sc-btn darkblue" href="' . esc_url( get_site_url() . '/wp-login.php?action=register' ) . '">' . __( "Continue", "thim" ) . '</a>
                        </div>
    ';
	$html .= '</div>';
	$html .= '<div style="clear: both"></div>';

	$html .= '</div>'; // thim-popup-login
	$html .= '</div>'; // thim-popup-login-container-inner
	$html .= '</div>'; // thim-popup-login-container
	$html .= '</div>'; // thim-popup-login-wrapper
	echo ent2ncr( $html );
}

add_action( 'wp_ajax_nopriv_thim_social_login', 'thim_social_login_callback' );
add_action( 'wp_ajax_thim_social_login', 'thim_social_login_callback' );

function thim_login_ajax_callback() {
	
	$nonce = $_REQUEST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'thim-login-nonce' ) ) {
		exit( 'Nonce is invalid' );
	}
	
	ob_start();
	global $wpdb;
	
	

	//We shall SQL prepare all inputs to avoid sql injection.
	$username = $wpdb->escape($_REQUEST['username']);
	$password = $wpdb->escape($_REQUEST['password']);
	$remember = $wpdb->escape($_REQUEST['rememberme']);
	
	

	if ( $remember ) {
		$remember = "true";
	} else {
		$remember = "false";
	}

	$login_data                  = array();
	$login_data['user_login']    = $username;
	$login_data['user_password'] = $password;
	$login_data['remember']      = $remember;
	$user_verify                 = wp_signon( $login_data, false );


	$code    = 1;
	$message = '';

	if ( is_wp_error( $user_verify ) ) {
		$message = $user_verify->get_error_message();
		$code    = - 1;
	} else {
		wp_set_current_user( $user_verify->ID, $username );
		do_action( 'set_current_user' );
		$message = '<script type="text/javascript">window.location=window.location;</script>';
	}

	$response_data = array(
		'code'    => $code,
		'message' => $message
	);

	ob_end_clean();
	echo json_encode( $response_data );
	die(); // this is required to return a proper result
}

add_action( 'wp_ajax_nopriv_thim_login_ajax', 'thim_login_ajax_callback' );
add_action( 'wp_ajax_thim_login_ajax', 'thim_login_ajax_callback' );

add_action( 'wp_ajax_my_action', 'my_action_function' );
function my_action_function() {
	check_ajax_referer( 'bk-ajax-nonce', 'security' );
	wp_die();
}