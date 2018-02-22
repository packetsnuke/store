<?php
/*
Plugin Name: Smart Reporter for e-commerce (shared on wplocker.com)
Plugin URI: http://www.storeapps.org/product/smart-reporter/
Description: <strong>Pro Version Installed.</strong> Store analysis like never before. 
Version: 2.8.1
Author: Store Apps
Author URI: http://www.storeapps.org/about/
Copyright (c) 2011, 2012, 2013, 2014, 2015 Store Apps All rights reserved.
*/

//Hooks
register_activation_hook ( __FILE__, 'sr_activate' );
register_deactivation_hook ( __FILE__, 'sr_deactivate' );

//Defining globals
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {

	$woo_version = get_option('woocommerce_version');

	if (version_compare ( $woo_version, '2.2.0', '<' )) {

		if (version_compare ( $woo_version, '2.0', '<' )) { // Flag for Handling Woo 2.0 and above

			if (version_compare ( $woo_version, '1.4', '<' )) {
				define ( 'SR_IS_WOO13', "true" );
				define ( 'SR_IS_WOO16', "false" );
			} else {
				define ( 'SR_IS_WOO13', "false" );
				define ( 'SR_IS_WOO16', "true" );
			}
        } else {
        	define ( 'SR_IS_WOO16', "false" );
        }

        define ( 'SR_IS_WOO22', "false" );
	} else {
		define ( 'SR_IS_WOO13', "false" );
		define ( 'SR_IS_WOO16', "false" );
		define ( 'SR_IS_WOO22', "true" );
	}
}


// Function for custom order searches

function woocommerce_shop_order_search_custom_fields1( $wp ) {
    global $pagenow, $wpdb;

    // if(empty($_COOKIE['sr_woo_search_post_ids']))
    if(!(isset($_GET['source']) && $_GET['source'] == 'sr'))
    	return;

    $post_ids = (!empty($_COOKIE['sr_woo_search_post_ids'])) ? explode(",",$_COOKIE['sr_woo_search_post_ids']) : 0;

    // Remove s - we don't want to search order name
    unset( $wp->query_vars['s'] );

    // Remove the post_ids from $_COOKIE
    unset($_COOKIE['sr_woo_search_post_ids']);

    // so we know we're doing this
    $wp->query_vars['shop_order_search'] = true;

    // Search by found posts
    $wp->query_vars['post__in'] = $post_ids;

}
add_action( 'parse_request', 'woocommerce_shop_order_search_custom_fields1' );

/**
 * Registers a plugin function to be run when the plugin is activated.
 */
function sr_activate() {
	global $wpdb, $blog_id;
	
        if ( false === get_site_option( 'sr_is_auto_refresh' ) ) {
            update_site_option( 'sr_is_auto_refresh', 'no' );
            update_site_option( 'sr_what_to_refresh', 'all' );
            update_site_option( 'sr_refresh_duration', '5' );
        }
        
        if ( is_multisite() ) {
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}", 0 );
		} else {
			$blog_ids = array( $blog_id );
		}

	foreach ( $blog_ids as $blog_id ) {
		if ( ( file_exists ( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) && ( is_plugin_active ( 'woocommerce/woocommerce.php' ) ) ) {
			$wpdb_obj = clone $wpdb;
			$wpdb->blogid = $blog_id;
			$wpdb->set_prefix( $wpdb->base_prefix );
			$create_table_order_items_query = "
				CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sr_woo_order_items` (
				  `product_id` bigint(20) unsigned NOT NULL default '0',
				  `order_id` bigint(20) unsigned NOT NULL default '0',
				  `product_name` text NOT NULL,
				  `quantity` int(10) unsigned NOT NULL default '0',
				  `sales` decimal(11,2) NOT NULL default '0.00',
				  `discount` decimal(11,2) NOT NULL default '0.00',
				  KEY `product_id` (`product_id`),
				  KEY `order_id` (`order_id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
			";

			$create_table_abandoned_items_query = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}sr_woo_abandoned_items` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `user_id` bigint(20) unsigned NOT NULL default '0',
				  `product_id` bigint(20) unsigned NOT NULL default '0',
				  `quantity` int(10) unsigned NOT NULL default '0',
				  `cart_id` bigint(20),
				  `abandoned_cart_time` int(11) unsigned NOT NULL,
				  `product_abandoned` int(1) unsigned NOT NULL default '0',
				  `order_id` bigint(20),
				  PRIMARY KEY (`id`),
				  KEY `product_id` (`product_id`),
				  KEY `user_id` (`user_id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	   		dbDelta( $create_table_order_items_query );
	   		dbDelta( $create_table_abandoned_items_query );
	   		
			add_action( 'load_sr_woo_order_items', 'load_sr_woo_order_items' );
	   		do_action( 'load_sr_woo_order_items', $wpdb );
	   		$wpdb = clone $wpdb_obj;
		}
	}
}

/**
 * Registers a plugin function to be run when the plugin is deactivated.
 */
function sr_deactivate() {
	global $wpdb, $blog_id;
	if ( is_multisite() ) {
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}", 0 );
	} else {
		$blog_ids = array( $blog_id );
	}
	foreach ( $blog_ids as $blog_id ) {
		$wpdb_obj = clone $wpdb;
		$wpdb->blogid = $blog_id;
		$wpdb->set_prefix( $wpdb->base_prefix );
		$wpdb->query( "DROP TABLE {$wpdb->prefix}sr_woo_order_items" );
		$wpdb = clone $wpdb_obj;
	}

	wp_clear_scheduled_hook( 'sr_send_summary_mails' ); //For clearing the scheduled daily summary mails event
}

function get_latest_version($plugin_file) {

	$latest_version = '';

	$sr_plugin_info = get_site_transient ( 'update_plugins' );
	// if ( property_exists($sr_plugin_info, 'response [$plugin_file]') && property_exists('response [$plugin_file]', 'new_version') ) {
	if ( property_exists($sr_plugin_info, 'response [$plugin_file]') ) {
		$latest_version = $sr_plugin_info->response [$plugin_file]->new_version;	
	}
	return $latest_version;
}

function get_user_sr_version($plugin_file) {
	$sr_plugin_info = get_plugins ();
	$user_version = $sr_plugin_info [$plugin_file] ['Version'];
	return $user_version;
}

function is_pro_updated() {
	$user_version = get_user_sr_version (SR_PLUGIN_FILE);
	$latest_version = get_latest_version (SR_PLUGIN_FILE);
	return version_compare ( $user_version, $latest_version, '>=' );
}

	// Action on cart updation
	add_action('woocommerce_cart_updated', 'sr_abandoned_cart_updated');

	// Action on removal of order Item
	add_action('woocommerce_before_cart_item_quantity_zero', 'sr_abandoned_remove_cart_item');

	// Action on order creation
	add_filter('woocommerce_order_details_after_order_table', 'sr_abandoned_order_placed');



	function sr_abandoned_remove_cart_item ($cart_item_key) {

		global $woocommerce, $wpdb;

		$user_id = get_current_user_id();
		
		$car_items_count = $woocommerce->cart->get_cart_contents_count();

		
		$cart_contents = $woocommerce->cart->cart_contents[$cart_item_key];

		$product_id = (!empty($cart_contents['variation_id'])) ? $cart_contents['variation_id'] : ((version_compare ( WOOCOMMERCE_VERSION, '2.0', '<' )) ? $cart_contents['id'] : $cart_contents['product_id']);

		$cart_update = "";

		if($car_items_count > 1) {

			$query_cart_id = "SELECT MAX(cart_id) FROM {$wpdb->prefix}sr_woo_abandoned_items";
			$results_cart_id = $wpdb->get_col( $query_cart_id );
			$rows_cart_id = $wpdb->num_rows;			

			if ($rows_cart_id > 0) {
				$cart_id = $results_cart_id[0] + 1;
			} else {
				$cart_id = 1;
			}

			$cart_update = ",cart_id	= ".$cart_id."";

		}

		//Updating the cart id for the removed item

		$query_max_id = "SELECT MAX(id) 
						FROM {$wpdb->prefix}sr_woo_abandoned_items
						WHERE user_id = ".$user_id."
						AND product_id = ".$product_id;
		$results_max_id = $wpdb->get_col( $query_max_id );				
		$results_max_id = implode (",", $results_max_id);

		$query_update_cart_id = "UPDATE {$wpdb->prefix}sr_woo_abandoned_items
								SET product_abandoned = 1
									$cart_update
								WHERE user_id = ".$user_id."
									AND product_id = ".$product_id."
									AND id IN (".$results_max_id.")";

		$wpdb->query ($query_update_cart_id);


	}


	function sr_abandoned_order_placed($order) {
		global $woocommerce, $wpdb;

		$user_id = get_current_user_id();

		$order_id = $order->id;
		$order_items = $order->get_items();

		if (empty($order_items)) return;

		foreach ( $order_items as $item ) {

			$product_id = (!empty($item['variation_id'])) ? $item['variation_id'] : ((version_compare ( WOOCOMMERCE_VERSION, '2.0', '<' )) ? $item['id'] : $item['product_id']);

			$query_abandoned = "SELECT * FROM {$wpdb->prefix}sr_woo_abandoned_items
								WHERE user_id = ".$user_id."
								AND product_id IN (". $product_id .")
								AND product_abandoned = 0";

			$results_abandoned = $wpdb->get_results( $query_abandoned, 'ARRAY_A' );
			$rows_abandoned = $wpdb->num_rows;

			if ($rows_abandoned > 0) {
				$query_update_order = "UPDATE {$wpdb->prefix}sr_woo_abandoned_items
									SET product_abandoned = 1,
										order_id = ". $order_id ."
									WHERE user_id=".$user_id."
										AND product_id IN (". $product_id .")
										AND product_abandoned='0'";
				$wpdb->query( $query_update_order );
			}

		}
		
	}


	function sr_abandoned_cart_updated() {

		global $woocommerce, $wpdb;

		$user_id = get_current_user_id();
		$current_time = current_time('timestamp');
		$cut_off_time = (get_option('sr_abandoned_cutoff_time')) ? get_option('sr_abandoned_cutoff_time') : 6 * 60;

		$cut_off_period = (get_option('sr_abandoned_cutoff_period')) ? get_option('sr_abandoned_cutoff_period') : 'minutes';

		if($cut_off_period == "hours") {
            $cut_off_time = $cut_off_time * 60;
        } elseif ($cut_off_period == "days") {
        	$cut_off_time = $cut_off_time * 24 * 60;
        }

		$cart_cut_off_time = $cut_off_time * 60;
		$compare_time = $current_time - $cart_cut_off_time;

		$cart_contents = array();
		$cart_contents = $woocommerce->cart->cart_contents;


		//Query to get the max cart id

		$query_cart_id = "SELECT cart_id, abandoned_cart_time
							FROM {$wpdb->prefix}sr_woo_abandoned_items
							WHERE product_abandoned = 0
								AND user_id=".$user_id;
		$results_cart_id = $wpdb->get_results( $query_cart_id, 'ARRAY_A' );
		$rows_cart_id = $wpdb->num_rows;
		
		if ($rows_cart_id > 0 && $compare_time < $results_cart_id[0]['abandoned_cart_time']) {
			$cart_id = $results_cart_id[0]['cart_id'];	
		} else {
			$query_cart_id = "SELECT MAX(cart_id) FROM {$wpdb->prefix}sr_woo_abandoned_items";
			$results_cart_id_max = $wpdb->get_col( $query_cart_id );
			$rows_cart_id = $wpdb->num_rows;			

			if ($rows_cart_id > 0) {
				$cart_id = $results_cart_id_max[0] + 1;
			} else {
				$cart_id = 1;
			}
		}


		foreach ($cart_contents as $key => $cart_content) {

			$product_id = ( $cart_content['variation_id'] > 0 ) ? $cart_content['variation_id'] : $cart_content['product_id'];
			
            $query_abandoned = "SELECT * FROM {$wpdb->prefix}sr_woo_abandoned_items
					WHERE user_id = ".$user_id."
						AND product_id IN (". $product_id .")
						AND product_abandoned = 0";

			$results_abandoned = $wpdb->get_results( $query_abandoned, 'ARRAY_A' );
			$rows_abandoned = $wpdb->num_rows;


			$insert_query = "INSERT INTO {$wpdb->prefix}sr_woo_abandoned_items
						(user_id, product_id, quantity, cart_id, abandoned_cart_time, product_abandoned)
						VALUES ('".$user_id."', '".$product_id."', '".$cart_content['quantity']."','".$cart_id."', '".$current_time."', '0')";


			if ($rows_abandoned == 0) {
				
				$wpdb->query( $insert_query );

			} else if ($compare_time > $results_abandoned[0]['abandoned_cart_time']) {

				$query_ignored = "UPDATE {$wpdb->prefix}sr_woo_abandoned_items
						SET product_abandoned = 1
						WHERE user_id=".$user_id."
							AND product_id IN (". $product_id .")";

				$wpdb->query( $query_ignored );

				//Inserting a new entry
				$wpdb->query( $insert_query );

			} else {
				$query_update = "UPDATE {$wpdb->prefix}sr_woo_abandoned_items
						SET quantity = ". $cart_content['quantity'] .",
							abandoned_cart_time = ". $current_time ."
						WHERE user_id=".$user_id."
							AND product_id IN (". $product_id .")
							AND product_abandoned='0'";
				$wpdb->query( $query_update );
			}


		}
    	
    }


	add_action ( 'init', 'sr_schedule_daily_summary_mails' );

	function sr_schedule_daily_summary_mails() {

		if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) || ( is_multisite() && in_array( 'woocommerce/woocommerce.php', get_option( 'active_sitewide_plugins' ) ) ) ) {

			if (file_exists ( (dirname ( __FILE__ )) . '/pro/sr-summary-mails.php' )) {
				include ('pro/sr-summary-mails.php');
			}

		}
	}


/**
 * Throw an error on admin page when WP e-Commerece plugin is not activated.
 */
if ( is_admin () || ( is_multisite() && is_network_admin() ) ) {
	// BOF automatic upgrades
	// if (!function_exists('wp_get_current_user')) {
 //        require_once (ABSPATH . 'wp-includes/pluggable.php'); // Sometimes conflict with SB-Welcome Email Editor
 //    }
	
	$plugin = plugin_basename ( __FILE__ );
	define ( 'SR_PLUGIN_DIR',dirname($plugin));
	define ( 'SR_PLUGIN_DIR_ABSPATH', dirname(__FILE__) );
	define ( 'SR_PLUGIN_FILE', $plugin );
	if (!defined('STORE_APPS_URL')) {
		define ( 'STORE_APPS_URL', 'http://www.storeapps.org/' );	
	}
	
	define ( 'ADMIN_URL', get_admin_url () ); //defining the admin url
	define ( 'SR_PLUGIN_DIRNAME', plugins_url ( '', __FILE__ ) );
	define ( 'SR_IMG_URL', SR_PLUGIN_DIRNAME . '/resources/themes/images/' );        

	// EOF
	
	add_action ( 'admin_notices', 'sr_admin_notices' );
	add_action ( 'admin_init', 'sr_admin_init' );
	
	if ( is_multisite() && is_network_admin() ) {
		
		function sr_add_license_key_page() {
			$page = add_submenu_page ('settings.php', 'Smart Reporter', 'Smart Reporter', 'manage_options', 'sr-settings', 'sr_settings_page' );
			add_action ( 'admin_print_styles-' . $page, 'sr_admin_styles' );
		}
		
		if (file_exists ( (dirname ( __FILE__ )) . '/pro/sr.js' ))
			add_action ('network_admin_menu', 'sr_add_license_key_page', 11);
			
	}

	// add_action('woocommerce_cart_updated', 'sr_demo');


	
	
	function sr_admin_init() {

	

		$plugin_info 	= get_plugins ();
		$sr_plugin_info = $plugin_info [SR_PLUGIN_FILE];
		$ext_version 	= '4.0.1';
		if (is_plugin_active ( 'woocommerce/woocommerce.php' ) && (defined('WPSC_URL') && is_plugin_active ( basename(WPSC_URL).'/wp-shopping-cart.php' )) && (!defined('SR_WPSC_WOO_ACTIVATED'))) {
			define('SR_WPSC_WOO_ACTIVATED',true);
		} elseif ( defined('WPSC_URL') && is_plugin_active ( basename(WPSC_URL).'/wp-shopping-cart.php' )) {
			define('SR_WPSC_ACTIVATED',true);
		} elseif (is_plugin_active ( 'woocommerce/woocommerce.php' )) {
			define('SR_WOO_ACTIVATED', true);
		}
		
		wp_register_script ( 'sr_ext_all', plugins_url ( 'resources/ext/ext-all.js', __FILE__ ), array (), $ext_version );
		if ( ( isset($_GET['post_type']) && $_GET['post_type'] == 'wpsc-product') || ( isset($_GET['page']) && $_GET['page'] == 'smart-reporter-wpsc')) {
			wp_register_script ( 'sr_main', plugins_url ( '/sr/smart-reporter.js', __FILE__ ), array ('sr_ext_all' ), $sr_plugin_info ['Version'] );
			if (!defined('SR_WPSC_RUNNING')) {
				define('SR_WPSC_RUNNING', true);	
			}
			
			if (!defined('SR_WOO_RUNNING')) {
				define('SR_WOO_RUNNING', false);
			}
			// checking the version for WPSC plugin

			if (!defined('SR_IS_WPSC37')) {
				define ( 'SR_IS_WPSC37', version_compare ( WPSC_VERSION, '3.8', '<' ) );
			}

			if (!defined('SR_IS_WPSC38')) {
				define ( 'SR_IS_WPSC38', version_compare ( WPSC_VERSION, '3.8', '>=' ) );
			}

			if ( SR_IS_WPSC38 ) {		// WPEC 3.8.7 OR 3.8.8
				if (!defined('SR_IS_WPSC387')) {
					define('SR_IS_WPSC387', version_compare ( WPSC_VERSION, '3.8.8', '<' ));
				}

				if (!defined('SR_IS_WPSC388')) {
					define('SR_IS_WPSC388', version_compare ( WPSC_VERSION, '3.8.8', '>=' ));
				}
			}
		} else if ( ( isset($_GET['post_type']) && $_GET['post_type'] == 'product') || ( isset($_GET['page']) && $_GET['page'] == 'smart-reporter-woo') )  {
			if (isset($_GET['tab']) && $_GET['tab'] == "smart_reporter_old") {
				wp_register_script ( 'sr_main', plugins_url ( '/sr/smart-reporter-woo.js', __FILE__ ), array ('sr_ext_all' ), $sr_plugin_info ['Version'] );	
			}

			if (!defined('SR_WPSC_RUNNING')) {
				define('SR_WPSC_RUNNING', false);
			}

			if (!defined('SR_WOO_RUNNING')) {
				define('SR_WOO_RUNNING', true);
			}
			
			//WooCommerce Currency Constants
			define ( 'SR_CURRENCY_SYMBOL', get_woocommerce_currency_symbol());
			define ( 'SR_DECIMAL_PLACES', get_option( 'woocommerce_price_num_decimals' ));
		}
		wp_register_style ( 'sr_ext_all', plugins_url ( 'resources/css/ext-all.css', __FILE__ ), array (), $ext_version );
		wp_register_style ( 'sr_main', plugins_url ( '/sr/smart-reporter.css', __FILE__ ), array ('sr_ext_all' ), $sr_plugin_info ['Version'] );
		
		if (file_exists ( (dirname ( __FILE__ )) . '/pro/sr.js' )) {
			wp_register_script ( 'sr_functions', plugins_url ( '/pro/sr.js', __FILE__ ), array ('sr_main' ), $sr_plugin_info ['Version'] );
			define ( 'SRPRO', true );
		} else {
			define ( 'SRPRO', false );
		}


		if (SRPRO === true) {

			include ('pro/upgrade.php');

			//wp-ajax action
			if (is_admin() ) {
	            add_action ( 'wp_ajax_top_ababdoned_products_export', 'sr_top_ababdoned_products_export' );
	            add_action ( 'wp_ajax_sr_save_settings', 'sr_save_settings' );
	        }

			
		}

		if (is_plugin_active ( 'woocommerce/woocommerce.php' )) {
	    	add_action( 'wp_dashboard_setup', 'sr_wp_dashboard_widget' );
	    }

		// ================================================================================================
		//Registering scripts and stylesheets for SR Beta Version
		// ================================================================================================

		if ( !wp_script_is( 'jquery' ) ) {
            wp_enqueue_script( 'jquery' );
        }

        wp_enqueue_script ( 'sr_jqplot_js', plugins_url ( 'resources/jqplot/jquery.jqplot.min.js', __FILE__ ),array('jquery'));
        wp_register_script ( 'sr_jqplot_high', plugins_url ( 'resources/jqplot/jqplot.highlighter.min.js', __FILE__ ), array ('sr_jqplot_js' ));
        wp_register_script ( 'sr_jqplot_cur', plugins_url ( 'resources/jqplot/jqplot.cursor.min.js', __FILE__ ), array ('sr_jqplot_high' ));
        wp_register_script ( 'sr_jqplot_render', plugins_url ( 'resources/jqplot/jqplot.categoryAxisRenderer.min.js', __FILE__ ), array ('sr_jqplot_cur' ));
        wp_register_script ( 'sr_jqplot_date_render', plugins_url ( 'resources/jqplot/jqplot.dateAxisRenderer.min.js', __FILE__ ), array ('sr_jqplot_render' ));
        wp_register_script ( 'sr_jqplot_pie_render', plugins_url ( 'resources/jqplot/jqplot.pieRenderer.min.js', __FILE__ ), array ('sr_jqplot_date_render' ));
        wp_register_script ( 'sr_jqplot_donout_render', plugins_url ( 'resources/jqplot/jqplot.donutRenderer.min.js', __FILE__ ), array ('sr_jqplot_pie_render' ));
        wp_register_script ( 'sr_jqplot_funnel_render', plugins_url ( 'resources/jqplot/jqplot.funnelRenderer.min.js', __FILE__ ), array ('sr_jqplot_donout_render' ));
        wp_enqueue_script ( 'sr_datepicker', plugins_url ( 'resources/jquery.datepick.package/jquery.datepick.js', __FILE__ ), array ('sr_jqplot_funnel_render' ));
        wp_enqueue_script ( 'sr_jvectormap', plugins_url ( 'resources/jvectormap/jquery-jvectormap-1.2.2.min.js', __FILE__ ), array ('sr_datepicker' ));
        wp_enqueue_script ( 'sr_jvectormap_world_map', plugins_url ( 'resources/jvectormap/jquery-jvectormap-world-mill-en.js', __FILE__ ), array ('sr_jvectormap' ));
        // wp_enqueue_script ( 'sr_jvectormap_world_map', plugins_url ( 'resources/jvectormap/world-map.js', __FILE__ ), array ('sr_jvectormap' ));

        // wp_enqueue_script ( 'sr_jvectormap', plugins_url ( 'resources/jqvmap/jquery.vmap.min.js', __FILE__ ), array ('sr_datepicker' ));
        // wp_enqueue_script ( 'sr_jvectormap_world_map', plugins_url ( 'resources/jqvmap/jquery.vmap.world.js', __FILE__ ), array ('sr_jvectormap' ));

        wp_enqueue_script ( 'sr_magnific_popup', plugins_url ( 'resources/magnific-popup/jquery.magnific-popup.js', __FILE__ ), array ('sr_jvectormap_world_map' ));
        wp_register_script ( 'sr_jqplot_all_scripts', plugins_url ( 'resources/jqplot/jqplot.BezierCurveRenderer.min.js', __FILE__ ), array ('sr_magnific_popup' ), $sr_plugin_info ['Version']);

        wp_register_style ( 'font_awesome', plugins_url ( "resources/font-awesome/css/font-awesome.min.css", __FILE__ ), array ());
        // wp_register_style ( 'font_awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css', array ());
		// wp_register_style ( 'sr_datepicker_css', plugins_url ( 'resources/jquery.datepick.package/redmond.datepick.css', __FILE__ ), array ('font_awesome'));
		wp_register_style ( 'sr_datepicker_css', plugins_url ( 'resources/jquery.datepick.package/smoothness.datepick.css', __FILE__ ), array ('font_awesome'));
		wp_register_style ( 'sr_jqplot_all', plugins_url ( 'resources/jqplot/jquery.jqplot.min.css', __FILE__ ), array ('sr_datepicker_css'));
		wp_register_style ( 'sr_jvectormap', plugins_url ( 'resources/jvectormap/jquery-jvectormap-1.2.2.css', __FILE__ ), array ('sr_jqplot_all'));
		
		wp_register_style ( 'sr_magnific_popup', plugins_url ( 'resources/magnific-popup/magnific-popup.css', __FILE__ ), array ('sr_jvectormap'));
		// wp_register_style ( 'sr_jvectormap', plugins_url ( 'resources/jqvmap/jqvmap.css', __FILE__ ), array ('sr_jqplot_all'));
		wp_register_style ( 'sr_main_beta', plugins_url ( '/sr/smart-reporter.css', __FILE__ ), array ('sr_magnific_popup' ), $sr_plugin_info ['Version'] );
		// ================================================================================================


	}

	
	// is_plugin_active ( basename(WPSC_URL).'/wp-shopping-cart.php' )
	function sr_admin_notices() {
		if (! is_plugin_active ( 'woocommerce/woocommerce.php' ) && ! is_plugin_active( 'wp-e-commerce/wp-shopping-cart.php' )) {
			echo '<div id="notice" class="error"><p>';
			_e ( '<b>Smart Reporter</b> add-on requires <a href="http://www.storeapps.org/wpec/">WP e-Commerce</a> plugin or <a href="http://www.storeapps.org/woocommerce/">WooCommerce</a> plugin. Please install and activate it.' );
			echo '</p></div>', "\n";
		}
	}
	
	function sr_admin_scripts() {		
		if (file_exists ( (dirname ( __FILE__ )) . '/pro/sr.js' )) {
			wp_enqueue_script ( 'sr_functions' );
		}
	}
	
	function sr_admin_styles() {
		wp_enqueue_style ( 'sr_main' );
	}
	
	function woo_add_modules_sr_admin_pages() {


		$page = add_submenu_page ('woocommerce', 'Smart Reporter', 'Smart Reporter', 'manage_woocommerce', 'smart-reporter-woo','sr_admin_page');

		// if ( $_GET ['action'] != 'sr-settings') { // not be include for settings page
		if ( !isset($_GET ['action']) ) { // not be include for settings page
			add_action ( 'admin_print_scripts-' . $page, 'sr_admin_scripts' );
		}
		add_action ( 'admin_print_styles-' . $page, 'sr_admin_styles' );
	}
	add_action ('admin_menu', 'woo_add_modules_sr_admin_pages');
	
	
	function sr_admin_page(){
        global $woocommerce;
        

        $tab = ( !empty($_GET['tab'] )  ? ( $_GET['tab'] ) : 'smart_reporter_beta' )   ;

        ?>

        <div style = "margin:0.7em 0.5em 0 0" class="wrap woocommerce">

            <h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
                <!-- <a href="<?php echo admin_url('admin.php?page=smart-reporter-woo') ?>" class="nav-tab <?php echo ($tab == 'smart_reporter_beta') ? 'nav-tab-active' : ''; ?>">Smart Reporter <sup style="vertical-align: super;color:red; font-size:15px">Beta</sub></a> -->
                <a href="<?php echo admin_url('admin.php?page=smart-reporter-woo') ?>" class="nav-tab <?php echo ($tab == 'smart_reporter_beta') ? 'nav-tab-active' : ''; ?>">Smart Reporter</a>
                <a href="<?php echo admin_url('admin.php?page=smart-reporter-woo&tab=smart_reporter_old') ?>" class="nav-tab <?php echo ($tab == 'smart_reporter_old') ? 'nav-tab-active' : ''; ?>">Smart Reporter Old</a>
            </h2>

            <?php
                switch ($tab) {
                    case "smart_reporter_old" :
                        sr_show_console();
                    break;
                    default :
                    	sr_beta_show_console();
                    break;
                }

            ?>

	    </div>
	    <?php

    }
    

	function wpsc_add_modules_sr_admin_pages($page_hooks, $base_page) {
		$page = add_submenu_page ( $base_page, 'Smart Reporter', 'Smart Reporter', 'manage_options', 'smart-reporter-wpsc', 'sr_show_console' );
		add_action ( 'admin_print_styles-' . $page, 'sr_admin_styles' );
		// if ( $_GET ['action'] != 'sr-settings') { // not be include for settings page
		if ( !isset($_GET ['action']) ) { // not be include for settings page
			add_action ( 'admin_print_scripts-' . $page, 'sr_admin_scripts' );
		}
		$page_hooks [] = $page;
		return $page_hooks;
	}
	add_filter ( 'wpsc_additional_pages', 'wpsc_add_modules_sr_admin_pages', 10, 2 );
	
	add_action( 'woocommerce_order_actions_start', 'sr_woo_refresh_order' );			// Action to be performed on clicking 'Save Order' button from Order panel

	

	// Actions on order change
	add_action( 'woocommerce_order_status_pending', 	'sr_woo_remove_order' );
	add_action( 'woocommerce_order_status_failed', 		'sr_woo_remove_order' );
	add_action( 'woocommerce_order_status_refunded', 	'sr_woo_remove_order' );
	add_action( 'woocommerce_order_status_cancelled', 	'sr_woo_remove_order' );
	add_action( 'woocommerce_order_status_on-hold', 	'sr_woo_add_order' );
	add_action( 'woocommerce_order_status_processing', 	'sr_woo_add_order' );
	add_action( 'woocommerce_order_status_complete', 	'sr_woo_add_order' );

	function sr_woo_refresh_order( $order_id ) {
		sr_woo_remove_order( $order_id );

		//Condn for woo 2.2 compatibility
		if (defined('SR_IS_WOO22') && SR_IS_WOO22 == "true") {
			$order_status = substr(get_post_status( $order_id ), 3);
		} else {
			$order_status = wp_get_object_terms( $order_id, 'shop_order_status', array('fields' => 'slugs') );
			$order_status = (!empty($order_status)) ? $order_status[0] : '';
		}

		if ( $order_status == 'on-hold' || $order_status == 'processing' || $order_status == 'completed' ) {
			sr_woo_add_order( $order_id );
		}
	}
        
        function sr_get_attributes_name_to_slug() {
            global $wpdb;
            
            $attributes_name_to_slug = array();
            
            $query = "SELECT DISTINCT meta_value AS product_attributes,
                             post_id AS product_id
                      FROM {$wpdb->prefix}postmeta
                      WHERE meta_key LIKE '_product_attributes'
                    ";
            $results = $wpdb->get_results( $query, 'ARRAY_A' );
            $num_rows = $wpdb->num_rows;

            if ($num_rows > 0) {
            	foreach ( $results as $result ) {
	                $attributes = maybe_unserialize( $result['product_attributes'] );
	                if ( is_array($attributes) && !empty($attributes) ) {
	                    foreach ( $attributes as $slug => $attribute ) {
	                        $attributes_name_to_slug[ $result['product_id'] ][ $attribute['name'] ] = $slug;
	                    }
	                }
	            }	
            }
            
            return $attributes_name_to_slug;
        }
        
        function sr_get_term_name_to_slug( $taxonomy_prefix = '' ) {
            global $wpdb;
            
            if ( !empty( $taxonomy_prefix ) ) {
                $where = "WHERE term_taxonomy.taxonomy LIKE '$taxonomy_prefix%'";
            } else {
                $where = '';
            }
            
            $query = "SELECT terms.slug, terms.name, term_taxonomy.taxonomy
                      FROM {$wpdb->prefix}terms AS terms
                          LEFT JOIN {$wpdb->prefix}term_taxonomy AS term_taxonomy USING ( term_id )
                      $where
                    ";
            $results = $wpdb->get_results( $query, 'ARRAY_A' );
            $num_rows = $wpdb->num_rows;

            $term_name_to_slug = array();

            if ($num_rows > 0) {
            	foreach ( $results as $result ) {
	                if ( count( $result ) <= 0 ) continue;
	                if ( !isset( $term_name_to_slug[ $result['taxonomy'] ] ) ) {
	                    $term_name_to_slug[ $result['taxonomy'] ] = array();
	                }
	                $term_name_to_slug[ $result['taxonomy'] ][ $result['name'] ] = $result['slug'];
	            }	
            }
            
            return $term_name_to_slug;
        }
	
        function sr_get_variation_attribute( $order_id ) {
            
                global  $wpdb;
                $query_variation_ids = "SELECT order_itemmeta.meta_value
                                        FROM {$wpdb->prefix}woocommerce_order_items AS order_items
                                        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_itemmeta
                                        ON (order_items.order_item_id = order_itemmeta.order_item_id)
                                        WHERE order_itemmeta.meta_key LIKE '_variation_id'
                                        AND order_itemmeta.meta_value > 0
                                        AND order_items.order_id IN ($order_id)";
                                        
                $result_variation_ids  = $wpdb->get_col ( $query_variation_ids );

                $query_variation_att = "SELECT postmeta.post_id AS post_id,
                                        GROUP_CONCAT(postmeta.meta_value
                                        ORDER BY postmeta.meta_id
                                        SEPARATOR ', ' ) AS meta_value
                                        FROM {$wpdb->prefix}postmeta AS postmeta
                                        WHERE postmeta.meta_key LIKE 'attribute_%'
                                        AND postmeta.post_id IN (". implode(",",$result_variation_ids) .")
                                        GROUP BY postmeta.post_id";

                $results_variation_att  = $wpdb->get_results ( $query_variation_att , 'ARRAY_A');

                $variation_att_all = array(); 

                for ( $i=0;$i<sizeof($results_variation_att);$i++ ) {
                    $variation_att_all [$results_variation_att [$i]['post_id']] = $results_variation_att [$i]['meta_value'];
                }
        }

        function sr_items_to_values( $all_order_items = array() ) {
            global $wpdb;

            if ( count( $all_order_items ) <= 0 || !defined( 'SR_IS_WOO16' ) || !defined( 'SR_IS_WOO22' ) ) return $all_order_items;
            $values = array();
            $attributes_name_to_slug = sr_get_attributes_name_to_slug();
            $prefix = ( (defined( 'SR_IS_WOO16' ) && SR_IS_WOO16 == "true") ) ? '' : '_';

            foreach ( $all_order_items as $order_id => $order_items ) {
                foreach ( $order_items as $item ) {
                        $order_item = array();

                        $order_item['order_id'] = $order_id;

                        if( ! function_exists( 'get_product' ) ) {
                            $product_id = ( !empty( $prefix ) && isset( $item[$prefix.'id'] ) ) ? $item[$prefix.'id'] : $item['id'];
                        } else {
                        	$product_id = (isset($item['product_id'])) ? $item['product_id'] : '';
                            $product_id = ( !empty( $prefix ) && isset( $item[$prefix.'product_id'] ) ) ? $item[$prefix.'product_id'] : $product_id;
                        }// end if

                        $order_item['product_name'] = get_the_title( $product_id );
                        $variation_id = (isset( $item['variation_id'] ) ) ? $item['variation_id'] : '';
                        $variation_id = ( !empty( $prefix ) && isset( $item[$prefix.'variation_id'] ) ) ? $item[$prefix.'variation_id'] : $variation_id;
                        $order_item['product_id'] = ( $variation_id > 0 ) ? $variation_id : $product_id;

                        if ( $variation_id > 0 ) {
                                $variation_name = array();
                                if( ! function_exists( 'get_product' ) && count( $item['item_meta'] ) > 0 ) {
                                    foreach ( $item['item_meta'] as $items ) {
                                        $variation_name[ 'attribute_' . $items['meta_name'] ] = $items['meta_value'];
                                    }
                                } else {

                                	$att_name_to_slug_prod = (!empty($attributes_name_to_slug[$product_id])) ? $attributes_name_to_slug[$product_id] : array();

                                    foreach ( $item as $item_meta_key => $item_meta_value ) {
                                        if ( array_key_exists( $item_meta_key, $att_name_to_slug_prod ) ) {
                                            $variation_name[ 'attribute_' . $item_meta_key ] = ( is_array( $item_meta_value ) && isset( $item_meta_value[0] ) ) ? $item_meta_value[0] : $item_meta_value;
                                        } elseif ( in_array( $item_meta_key, $att_name_to_slug_prod ) ) {
                                            $variation_name[ 'attribute_' . $item_meta_key ] = ( is_array( $item_meta_value ) && isset( $item_meta_value[0] ) ) ? $item_meta_value[0] : $item_meta_value;
                                        }
                                    }
                                }
                                
                                $order_item['product_name'] .= ' (' . woocommerce_get_formatted_variation( $variation_name, true ) . ')'; 
                        }

                        $qty = (isset( $item['qty'] ) ) ? $item['qty']: '';
                        $order_item['quantity'] = ( !empty( $prefix ) && isset( $item[$prefix.'qty'] ) ) ? $item[$prefix.'qty'] : $qty;
                        $line_total = ( isset( $item['line_total'] ) ) ? $item['line_total'] : '' ;
                        $line_total = ( !empty( $prefix ) && isset( $item[$prefix.'line_total'] ) ) ? $item[$prefix.'line_total'] : $line_total;
                        $order_item['sales'] = $line_total;
                        $line_subtotal = ( isset( $item['line_subtotal'] ) ) ? $item['line_subtotal'] : '';
                        $line_subtotal = ( !empty( $prefix ) && isset( $item[$prefix.'line_subtotal'] ) ) ? $item[$prefix.'line_subtotal'] : $line_subtotal;
                        $order_item['discount'] = $line_subtotal - $line_total;

                        if ( empty( $order_item['product_id'] ) || empty( $order_item['order_id'] ) || empty( $order_item['quantity'] ) ) 
                            continue;
                        $values[] = "( {$order_item['product_id']}, {$order_item['order_id']}, '{$order_item['product_name']}', {$order_item['quantity']}, " . (empty($order_item['sales']) ? 0 : $order_item['sales'] ) . ", " . (empty($order_item['discount']) ? 0 : $order_item['discount'] ) . " )";
                }
            }

            return $values;
        }
        
        function sr_woo_add_order( $order_id ) {
        	global $wpdb;
			$order = new WC_Order( $order_id );
			$order_items = array( $order_id => $order->get_items() );
		
			$insert_query = "INSERT INTO {$wpdb->prefix}sr_woo_order_items 
							( `product_id`, `order_id`, `product_name`, `quantity`, `sales`, `discount` ) VALUES ";
                
            $values = sr_items_to_values( $order_items );
            if ( count( $values ) > 0 ) {
            	$insert_query .= implode(",",$values);
                $wpdb->query( $insert_query );
            }
        }
	
	function sr_woo_remove_order( $order_id ) {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->prefix}sr_woo_order_items WHERE order_id = {$order_id}" );
	}
	
	// Function to load table sr_woo_order_items
	function load_sr_woo_order_items( $wpdb ) {

        $insert_query = "REPLACE INTO {$wpdb->prefix}sr_woo_order_items 
                            ( `product_id`, `order_id`, `product_name`, `quantity`, `sales`, `discount` ) VALUES ";

        $all_order_items = array();

		// WC's code to get all order items
        if( defined('SR_IS_WOO16') && SR_IS_WOO16 == "true" ) {
            $results = $wpdb->get_results ("
                    SELECT meta.post_id AS order_id, meta.meta_value AS items 
                    FROM {$wpdb->prefix}posts AS posts
	                    LEFT JOIN {$wpdb->prefix}postmeta AS meta ON posts.ID = meta.post_id
	                    LEFT JOIN {$wpdb->prefix}term_relationships AS rel ON posts.ID=rel.object_ID
	                    LEFT JOIN {$wpdb->prefix}term_taxonomy AS tax USING( term_taxonomy_id )
	                    LEFT JOIN {$wpdb->prefix}terms AS term USING( term_id )

                    WHERE 	meta.meta_key 		= '_order_items'
                    AND 	posts.post_type 	= 'shop_order'
                    AND 	posts.post_status 	= 'publish'
                    AND 	tax.taxonomy		= 'shop_order_status'
                    AND		term.slug			IN ('completed', 'processing', 'on-hold')
            		", 'ARRAY_A');

            $num_rows = $wpdb->num_rows;

            if ($num_rows > 0) {
            	foreach ( $results as $result ) {
	                    $all_order_items[ $result['order_id'] ] = maybe_unserialize( $result['items'] ); 
	            }	
            }
                    
        } else {
        	if( defined('SR_IS_WOO22') && SR_IS_WOO22 == "true" ) {

        		$results = $wpdb->get_col ("
	                            SELECT posts.ID AS order_id 
	                            FROM {$wpdb->prefix}posts AS posts
	                            WHERE 	posts.post_type LIKE 'shop_order'
		                            AND posts.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
	                            ");
        	} else {
        		$results = $wpdb->get_col ("
	                            SELECT posts.ID AS order_id 
	                            FROM {$wpdb->prefix}posts AS posts
		                            LEFT JOIN {$wpdb->prefix}term_relationships AS rel ON posts.ID=rel.object_ID
		                            LEFT JOIN {$wpdb->prefix}term_taxonomy AS tax USING( term_taxonomy_id )
		                            LEFT JOIN {$wpdb->prefix}terms AS term USING( term_id )

	                            WHERE 	posts.post_type 	= 'shop_order'
		                            AND 	posts.post_status 	= 'publish'
		                            AND 	tax.taxonomy		= 'shop_order_status'
		                            AND	term.slug	IN ('completed', 'processing', 'on-hold')
	                            ");
        	}

        	if ( !empty( $results ) ) {
        		$order_id = implode( ", ", $results);
	            $order_id = trim( $order_id );

                $query_order_items = "SELECT order_items.order_item_id,
                                            order_items.order_id    ,
                                            order_items.order_item_name AS order_prod,
		                                    GROUP_CONCAT(order_itemmeta.meta_key
		                                    ORDER BY order_itemmeta.meta_id
		                                    SEPARATOR '###' ) AS meta_key,
		                                    GROUP_CONCAT(order_itemmeta.meta_value
		                                    ORDER BY order_itemmeta.meta_id
		                                    SEPARATOR '###' ) AS meta_value
                                    FROM {$wpdb->prefix}woocommerce_order_items AS order_items
                                    	LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_itemmeta
                                    		ON (order_items.order_item_id = order_itemmeta.order_item_id)
                                    WHERE order_items.order_id IN ($order_id)
                                    GROUP BY order_items.order_item_id
                                    ORDER BY FIND_IN_SET(order_items.order_id,'$order_id')";
                                
                $results  = $wpdb->get_results ( $query_order_items , 'ARRAY_A');          
                $num_rows = $wpdb->num_rows;

                if ($num_rows > 0) {
                	foreach ( $results as $result ) {
	                    $order_item_meta_values = explode('###', $result ['meta_value'] );
	                    $order_item_meta_key = explode('###', $result ['meta_key'] );
	                    if ( count( $order_item_meta_values ) != count( $order_item_meta_key ) )
	                        continue; 
	                    $order_item_meta_key_values = array_combine($order_item_meta_key, $order_item_meta_values);
	                    if ( !isset( $all_order_items[ $result['order_id'] ] ) ) {
	                        $all_order_items[ $result['order_id'] ] = array();
	                    }
	                    $all_order_items[ $result['order_id'] ][] = $order_item_meta_key_values;
	                }	
                }
                
            }

        } //end if
              
	    $values = sr_items_to_values( $all_order_items );
	    
	    if ( count( $values ) > 0 ) {
	        $insert_query .= implode( ',', $values );
	        $wpdb->query( $insert_query );
	    }
	}
	

	$support_func_flag = 0;

	function sr_console_common() {

		?>
		<div class="wrap">
		<!-- <div id="icon-smart-reporter" class="icon32"><br /> -->
		</div>
		<style>
		    div#TB_window {
		        background: lightgrey;
		    }
		</style>    
		<?php 
		
		if (SR_WPSC_RUNNING === true) {
			$json_filename = 'json';
		} else if (SR_WOO_RUNNING === true) {
			$json_filename = 'json-woo';
		}
		define ( 'SR_JSON_URL', SR_PLUGIN_DIRNAME . "/sr/$json_filename.php" );
		
		//set the number of days data to show in lite version.
		define ( 'SR_AVAIL_DAYS', 30);
		
		$latest_version = get_latest_version (SR_PLUGIN_FILE );
		$is_pro_updated = is_pro_updated ();
		
		if ( isset($_GET ['action']) && $_GET ['action'] == 'sr-settings') {


			sr_settings_page (SR_PLUGIN_FILE);
		} else {
			$base_path = WP_PLUGIN_DIR . '/' . str_replace ( basename ( __FILE__ ), "", plugin_basename ( __FILE__ ) ) . 'sr/';
		?>
		<div class="wrap">
		<div id="icon-smart-reporter" class="icon32"><img alt="Smart Reporter"
			src="<?php echo SR_IMG_URL.'/logo.png'; ?>"></div>
		<h2><?php
		echo _e ( 'Smart Reporter' );
		echo ' ';
			if (SRPRO === true) {
				echo _e ( 'Pro' );
			} else {
				echo _e ( 'Lite' );
			}
		?>


   	<p class="wrap" style="font-size: 12px">
	   	<span style="float: right;margin-right: 2.25em;"> <?php
			if ( SRPRO === true && ! is_multisite() ) {
				
				if (SR_WPSC_RUNNING == true) {
					$plug_page = 'wpsc';
				} elseif (SR_WOO_RUNNING == true) {
					$plug_page = 'woo';
				}
			} else {
				$before_plug_page = '';
				$after_plug_page = '';
				$plug_page = '';
			}

			if ( SRPRO === true ) {


	            if ( !wp_script_is( 'thickbox' ) ) {
	                if ( !function_exists( 'add_thickbox' ) ) {
	                    require_once ABSPATH . 'wp-includes/general-template.php';
	                }
	                add_thickbox();
	            }


	            // <a href="edit.php#TB_inline?max-height=420px&inlineId=smart_manager_post_query_form" title="Send your query" class="thickbox" id="support_link">Need Help?</a>
	            $before_plug_page = '<a href="admin.php#TB_inline?max-height=420px&inlineId=sr_post_query_form" title="Send your query" class="thickbox" id="support_link">Feedback / Help?</a>';
	            
	            // if ( !isset($_GET['tab']) && ( isset($_GET['page']) && $_GET['page'] == 'smart-reporter-woo') && SR_BETA == "true") {
	            // 	// $before_plug_page .= ' | <a href="#" class="show_hide" rel="#slidingDiv">Settings</a>';
	            // 	$after_plug_page = '';
	            // 	$plug_page = '';
	            // }
	            // else {
	            	$before_plug_page .= ' | <a href="admin.php?page=smart-reporter-';
	            	$after_plug_page = '&action=sr-settings">Settings</a>';
	            // }

	        }

			printf ( __ ( '%1s%2s%3s'), $before_plug_page, $plug_page, $after_plug_page);		
		?>
		</span>
		<?php
			echo __ ( 'Store analysis like never before.' );
		?>
	</p>
	<h6 align="right"><?php
			if (isset($is_pro_updated) && ! $is_pro_updated) {
				$admin_url = ADMIN_URL . "plugins.php";
				$update_link = "An upgrade for Smart Reporter Pro  $latest_version is available. <a align='right' href=$admin_url> Click to upgrade. </a>";
				sr_display_notice ( $update_link );
			}
			?>
   </h6>
   <h6 align="right">
</h2>
</div>

<?php
if (SRPRO === false) {
				?>
<div id="message" class="updated fade">
<p><?php
printf ( __ ( "<b>Important:</b> To get the sales and sales KPI's for more than 30 days upgrade to Pro . Take a <a href='%2s' target=_livedemo> Live Demo here </a>." ), 'http://demo.storeapps.org/' );
				?></p>
</div>
<?php
}
			?>
<?php
			$error_message = '';
			if ((file_exists( WP_PLUGIN_DIR . '/wp-e-commerce/wp-shopping-cart.php' )) && (file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ))) {
			
			if ( ( isset($_GET['post_type']) && $_GET['post_type'] == 'wpsc-product') || ( isset($_GET['page']) && $_GET['page'] == 'smart-reporter-wpsc')) {

				if (is_plugin_active( 'wp-e-commerce/wp-shopping-cart.php' )) {
	                require_once (WPSC_FILE_PATH . '/wp-shopping-cart.php');
	                	if ( ((defined('SR_IS_WPSC37')) && SR_IS_WPSC37) || (defined('SR_IS_WPSC38') && SR_IS_WPSC38) ) {

	                        if (file_exists( $base_path . 'reporter-console.php' )) {
	                                include_once ($base_path . 'reporter-console.php');
	                                return;
	                        } else {
	                                $error_message = __( "A required Smart Reporter file is missing. Can't continue.", 'smart-reporter' );
	                        }
	                    } else {
	                        $error_message = __( 'Smart Reporter currently works only with WP e-Commerce 3.7 or above.', 'smart-reporter' );
	                    }
                }

			} else if (is_plugin_active( 'woocommerce/woocommerce.php' )) {
                if ((defined('SR_IS_WOO13')) && SR_IS_WOO13 == "true") {
                        $error_message = __( 'Smart Reporter currently works only with WooCommerce 1.4 or above.', 'smart-reporter' );
                } else {
                    if (file_exists( $base_path . 'reporter-console.php' )) {
                            include_once ($base_path . 'reporter-console.php');
                            return;
                    } else {
                            $error_message = __( "A required Smart Reporter file is missing. Can't continue.", 'smart-reporter' );
                    }
                }
			}
                        else {
                            $error_message = "<b>" . __( 'Smart Reporter', 'smart-reporter' ) . "</b> " . __( 'add-on requires', 'smart-reporter' ) . " " .'<a href="http://www.storeapps.org/wpec/">' . __( 'WP e-Commerce', 'smart-reporter' ) . "</a>" . " " . __( 'plugin or', 'smart-reporter' ) . " " . '<a href="http://www.storeapps.org/woocommerce/">' . __( 'WooCommerce', 'smart-reporter' ) . "</a>" . " " . __( 'plugin. Please install and activate it.', 'smart-reporter' );
                        }
                    } else if (file_exists( WP_PLUGIN_DIR . '/wp-e-commerce/wp-shopping-cart.php' )) {
                        if (is_plugin_active( 'wp-e-commerce/wp-shopping-cart.php' )) {
                            require_once (WPSC_FILE_PATH . '/wp-shopping-cart.php');
                            if ((defined('SR_IS_WPSC37') && SR_IS_WPSC37) || (defined('SR_IS_WPSC38') && SR_IS_WPSC38)) {
                                if (file_exists( $base_path . 'reporter-console.php' )) {
                                        include_once ($base_path . 'reporter-console.php');
                                        return;
                                } else {
                                        $error_message = __( "A required Smart Reporter file is missing. Can't continue.", 'smart-reporter' );
                                }
                            } else {
                                $error_message = __( 'Smart Reporter currently works only with WP e-Commerce 3.7 or above.', 'smart-reporter' );
                            }
                        } else {
                                $error_message = __( 'WP e-Commerce plugin is not activated.', 'smart-reporter' ) . "<br/><b>" . _e( 'Smart Reporter', 'smart-reporter' ) . "</b> " . _e( 'add-on requires WP e-Commerce plugin, please activate it.', 'smart-reporter' );
                        }
                    } else if (file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' )) {
                        if (is_plugin_active( 'woocommerce/woocommerce.php' )) {
                            if ((defined('SR_IS_WOO13')) && SR_IS_WOO13 == "true") {
                                    $error_message = __( 'Smart Reporter currently works only with WooCommerce 1.4 or above.', 'smart-reporter' );
                            } else {
                                if (file_exists( $base_path . 'reporter-console.php' )) {
                                    include_once ($base_path . 'reporter-console.php');
                                    return;
                                } else {
                                    $error_message = __( "A required Smart Reporter file is missing. Can't continue.", 'smart-reporter' );
                                }
                            }
                        } else {
                            $error_message = __( 'WooCommerce plugin is not activated.', 'smart-reporter' ) . "<br/><b>" . __( 'Smart Reporter', 'smart-reporter' ) . "</b> " . __( 'add-on requires WooCommerce plugin, please activate it.', 'smart-reporter' );
                        }
                    }
                    else {
                        $error_message = "<b>" . __( 'Smart Reporter', 'smart-reporter' ) . "</b> " . __( 'add-on requires', 'smart-reporter' ) . " " .'<a href="http://www.storeapps.org/wpec/">' . __( 'WP e-Commerce', 'smart-reporter' ) . "</a>" . " " . __( 'plugin or', 'smart-reporter' ) . " " . '<a href="http://www.storeapps.org/woocommerce/">' . __( 'WooCommerce', 'smart-reporter' ) . "</a>" . " " . __( 'plugin. Please install and activate it.', 'smart-reporter' );
                    }

			if ($error_message != '') {
				sr_display_err ( $error_message );
				?>
<?php
			}
		}
	};


	// if (is_plugin_active ( 'woocommerce/woocommerce.php' )) {
 //    	add_action( 'wp_dashboard_setup', 'sr_wp_dashboard_widget' );
 //    }
	
	function sr_wp_dashboard_widget() {
		$base_path = WP_PLUGIN_DIR . '/' . str_replace ( basename ( __FILE__ ), "", plugin_basename ( __FILE__ ) ) . 'sr/';
		if (file_exists( $base_path . 'reporter-console.php' )) {
            include_once ($base_path . 'reporter-console.php');
            wp_enqueue_script ( 'sr_jqplot_all_scripts' );
			wp_enqueue_style ( 'sr_main_beta' );
		
			//Constants for the arrow indicators
		    define ('SR_IMG_UP_GREEN', 'fa fa-angle-double-up icon_cumm_indicator_green');
		    define ('SR_IMG_UP_RED', 'fa fa-angle-double-up icon_cumm_indicator_red');
		    define ('SR_IMG_DOWN_RED', 'fa fa-angle-double-down icon_cumm_indicator_red');

			wp_add_dashboard_widget( 'sr_dashboard_kpi', __( 'Sales Summary', 'smart_reporter' ), 'sr_dashboard_widget_kpi' );
		}
	}

	function sr_beta_show_console() {
		

		//Constants for the arrow indicators
	    define ('SR_IMG_UP_GREEN', 'fa fa-angle-double-up icon_cumm_indicator_green');
	    define ('SR_IMG_UP_RED', 'fa fa-angle-double-up icon_cumm_indicator_red');
	    define ('SR_IMG_DOWN_RED', 'fa fa-angle-double-down icon_cumm_indicator_red');
	    
	    //Constant for DatePicker Icon    
	    define ('SR_IMG_DATE_PICKER', SR_IMG_URL . 'calendar-blue.gif');

	    define("SR_BETA","true");

	    //Enqueing the Scripts and StyleSheets

        wp_enqueue_script ( 'sr_jqplot_all_scripts' );
		wp_enqueue_style ( 'sr_main_beta' );

		if (file_exists ( (dirname ( __FILE__ )) . '/pro/sr.js' )) {

			$plugin_info 	= get_plugins ();
			$sr_plugin_info = $plugin_info [SR_PLUGIN_FILE];

			wp_register_script ( 'sr_pro', plugins_url ( 'pro/sr.js', __FILE__ ), array ('sr_jqplot_all_scripts' ), $sr_plugin_info ['Version']);
			wp_enqueue_script ( 'sr_pro' );
		}
		
		sr_console_common();

		// Code for overriding the wooCommerce orders module search functionality code

		add_action('wp_ajax_get_monthly_sales','get_monthly_sales');

		


	};

	function sr_show_console() {

		//Enqueing the Scripts and StyleSheets
		wp_enqueue_script ( 'sr_main' );
		wp_enqueue_style ( 'sr_main' );

		sr_console_common();
	}
	
	function sr_update_notice() {
		if ( !function_exists( 'sr_get_download_url_from_db' ) ) return;
                $download_details = sr_get_download_url_from_db();
//                $plugins = get_site_transient ( 'update_plugins' );
		$link = $download_details['results'][0]->option_value;                                //$plugins->response [SR_PLUGIN_FILE]->package;
		
                if ( !empty( $link ) ) {
                    $current  = get_site_transient ( 'update_plugins' );
                    $r1       = sr_plugin_reset_upgrade_link ( $current, $link );
                    set_site_transient ( 'update_plugins', $r1 );
                    echo $man_download_link = " Or <a href='$link'>click here to download the latest version.</a>";
                }
	}
		
	if (! function_exists ( 'sr_display_err' )) {
		function sr_display_err($error_message) {
			echo "<div id='notice' class='error'>";
			echo _e ( '<b>Error: </b>' . $error_message );
			echo "</div>";
		}
	}
	
	if (! function_exists ('sr_display_notice')) {
		function sr_display_notice($notice) {
			echo "<div id='message' class='updated fade'>
             <p>";
			echo _e ( $notice );
			echo "</p></div>";
		}
	}
// EOF auto upgrade code
}
?>
