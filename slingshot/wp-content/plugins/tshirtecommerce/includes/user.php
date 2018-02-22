<?php
/**
 * @author tshirtecommerce - www.tshirtecommerce.com
 * @date: 2016-01-17
 *
 * API user
		- Check login
		- get userinfo
		- create account
 *
 * @copyright  Copyright (C) 2015 tshirtecommerce.com. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 *
 */
 
// get user info
add_action( 'wp_ajax_tshirt_user_is_login', 'tshirt_user_is_login' );
add_action( 'wp_ajax_nopriv_tshirt_user_is_login', 'tshirt_user_is_login' );
function tshirt_user_is_login()
{
	global $wpdb;
	
	$data = array();
	if ( is_user_logged_in() )
	{
		$data['logged'] = true;
		$user 			= wp_get_current_user();
		$data['user'] 	= array(
			'id' 		=> $user->data->ID,
			'is_admin' 	=> false,
			'username' => $user->data->user_login,
			'email' => $user->data->user_email,
			'key' => md5($user->data->ID)
		);
		
		if (!session_id())
			session_start();
	
		$logged = array(
			'login' => true,
			'email' => $user->data->user_email,
			'id' => $user->data->ID,
			'is_admin' => false,
		);
		if ( is_super_admin() )
		{
			$logged['is_admin'] = true;
			$data['user']['is_admin'] = true;

			/* fix server not allow write session */
			if( isset($_POST['path']) )
			{
				$file = $_POST['path'].'ajax.php';
				include_once($file);
				$reload = tshirt_session_file($data);

				if($reload == true)
				{
					$data['reload'] = 1;
				}
			}
		}
		$_SESSION['is_logged'] = $logged;

		/* check file session */
		$path = dirname(dirname(dirname(dirname(dirname(__FILE__))))). '/tshirtecommerce/admin/';
		if( file_exists($path.'session.php') )
		{
			$session_id = session_id();
			$content 	= session_encode();
			$file 	= $path.'/temp/sess_'.$session_id;
			write_to_file($content, $file);
		}
		
	}
	else
	{
		$data['logged'] = false;
	}
	
	echo json_encode($data);
	wp_die();
}


// login website
// check user login
add_action( 'wp_ajax_tshirt_login', 'tshirt_login' );
add_action( 'wp_ajax_nopriv_tshirt_login', 'tshirt_login' );
function tshirt_login()
{
	$return = array();
	if( !empty($_REQUEST['username']) && !empty($_REQUEST['password']) && trim($_REQUEST['username']) != '' && trim($_REQUEST['password'] != '') )
	{
		$credentials = array('user_login' => $_REQUEST['username'], 'user_password'=> $_REQUEST['password'], 'remember' => true);
		$loginResult = wp_signon($credentials);
		if ( strtolower(get_class($loginResult)) == 'wp_user' )
		{			
			$return['result']	= true;
			$user 				= $loginResult;
			$return['user'] 	= array(
				'id' => $user->data->ID,
				'username' => $user->data->user_login,
				'email' => $user->data->user_email,
				'key' => md5($user->data->ID)
			);
		}
		elseif ( strtolower(get_class($loginResult)) == 'wp_error' )
		{
			$return['result'] = false;
			$return['error'] = $loginResult->get_error_message();
		}
		else
		{			
			$return['result'] = false;
			$return['error'] = __('An undefined error has ocurred', 'login-with-ajax');
		}
		
	}
	else 
	{				
		$return['result'] = false;
		$return['error'] = __('An undefined error has ocurred', 'login-with-ajax');
	}
	
	echo json_encode($return);
	wp_die();
}

// logout
function tshirt_e_logout() {
    if (isset($_SESSION['is_admin']))
	{
		unset($_SESSION['is_admin']);
	}
	
	if (isset($_SESSION['is_logged']))
	{
		unset($_SESSION['is_logged']);
	}
	
	if (isset($_SESSION['admin']))
	{
		unset($_SESSION['admin']);
	}
}
add_action('wp_logout', 'tshirt_e_logout');


// create account
add_action( 'wp_ajax_tshirt_register', 'tshirt_register' );
add_action( 'wp_ajax_nopriv_tshirt_register', 'tshirt_register' );
function tshirt_register()
{
	$return = array();	 
	if( get_option('users_can_register') )
	{
		$errors = register_new_user($_REQUEST['username'], $_REQUEST['email']);
		if ( !is_wp_error($errors) )
		{
			//Success
			$return['result'] 	= true;
			$return['message'] 	= __('Registration complete. Please check your e-mail.','login-with-ajax');
			//add user to blog if multisite
			if( is_multisite() )
			{
				add_user_to_blog(get_current_blog_id(), $errors, get_option('default_role'));
			}
			// set password
			wp_set_password( $_REQUEST['password'], $errors );
			
			//login
			$credentials = array('user_login' => $_REQUEST['username'], 'user_password'=> $_REQUEST['password'], 'remember' => true);
			$loginResult = wp_signon($credentials);
			if ( strtolower(get_class($loginResult)) == 'wp_user' )
			{	
				$return['result']	= true;
				$user 				= $loginResult;
				$return['user'] 	= array(
					'id' => $user->data->ID,
					'username' => $user->data->user_login,
					'email' => $user->data->user_email,
					'key' => md5($user->data->ID)
				);
			}
		}
		else
		{
			//Something's wrong
			$return['result'] 	= false;
			$return['error'] 	= $errors->get_error_message();
		}
		$return['action'] = 'register';
	}
	else
	{
		$return['result'] = false;
		$return['error'] = __('Registration has been disabled.','login-with-ajax');
	}
	
	echo json_encode($return);
	wp_die();
}
?>