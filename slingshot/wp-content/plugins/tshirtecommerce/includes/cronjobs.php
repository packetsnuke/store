<?php
/**
 * @author tshirtecommerce - www.tshirtecommerce.com
 * @date: 2016-7-10
 * 
 * @copyright  Copyright (C) 2015 tshirtecommerce.com. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 *
*/

// add cronjob auto download data
add_action( 'wp_ajax_tshirt_store_add_cronjobs', 'tshirt_store_add_cronjobs' );
function tshirt_store_add_cronjobs()
{
	if (!is_super_admin())
	{
		return false;
	}
	$time = time();
	if (! wp_next_scheduled( 'tshirt_store_art_auto_download' ))
	{
		wp_schedule_event($time, 'twicedaily', 'tshirt_store_art_auto_download');
	}
	
	if (! wp_next_scheduled( 'tshirt_store_idea_auto_download' ))
	{
		wp_schedule_event($time + 3600, 'twicedaily', 'tshirt_store_idea_auto_download');
	}
}

// remove cronjob auto download data
add_action( 'wp_ajax_tshirt_store_remove_cronjobs', 'tshirt_store_remove_cronjobs' );
function tshirt_store_remove_cronjobs()
{
	if (!is_super_admin())
	{
		return false;
	}
	wp_clear_scheduled_hook('tshirt_store_art_auto_download');
	wp_clear_scheduled_hook('tshirt_store_idea_auto_download');
}

// update data
add_action('wp_ajax_tshirt_store_import_all', 'tshirt_store_import_all');
function tshirt_store_import_all()
{
	$type	= 'art';
	if(isset($_GET['type']))
	{
		$type = $_GET['type'];
	}
	if($type != 'art')
		$type	= 'idea';
	if($type == 'art')
	{
		tshirt_store_art_import();
	}
	else
	{
		tshirt_store_idea_import();
	}
}

add_action('tshirt_store_art_auto_download', 'tshirt_store_art_import');
function tshirt_store_art_import()
{
	if (defined('ROOT') == false)
		define('ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))). '/tshirtecommerce');
	
	if (defined('DS') == false)
		define('DS', DIRECTORY_SEPARATOR);
	
	include_once (ROOT .DS. 'includes' .DS. 'functions.php');
	$dg = new dg();
	
	$settings = $dg->getSetting();
	if( isset($settings->store) 
		&& isset($settings->store->api) 
		&& $settings->store->api != '' 
		&& isset($settings->store->verified) 
		&& $settings->store->verified == 1 
		&& isset($settings->store->enable) 
		&& $settings->store->enable == 1 
	)
	{
		include_once( dirname(dirname(dirname(dirname(dirname(__FILE__))))). '/tshirtecommerce/api.php');
		$api 	= new API($settings->store->api);
		
		// load clipart
		$api->updateArts();
	}
	exit;
}

// update design template
add_action('tshirt_store_idea_auto_download', 'tshirt_store_idea_import');
function tshirt_store_idea_import()
{
	if (defined('ROOT') == false)
		define('ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))). '/tshirtecommerce');
	
	if (defined('DS') == false)
		define('DS', DIRECTORY_SEPARATOR);
	
	include_once (ROOT .DS. 'includes' .DS. 'functions.php');
	$dg = new dg();
	
	$settings = $dg->getSetting();
	if( isset($settings->store) 
		&& isset($settings->store->api) 
		&& $settings->store->api != '' 
		&& isset($settings->store->verified) 
		&& $settings->store->verified == 1 
		&& isset($settings->store->enable) 
		&& $settings->store->enable == 1 
	)
	{
		include_once( dirname(dirname(dirname(dirname(dirname(__FILE__))))). '/tshirtecommerce/api.php');
		$api 	= new API($settings->store->api);
		
		// load design template
		$api->updateIdeas();
	}
	exit;
}
?>