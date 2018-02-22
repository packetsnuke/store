<?php
/**
 * @author tshirtecommerce - www.tshirtecommerce.com
 * @date: 2016-07-01
 *
 * Store
 *
 * @copyright  Copyright (C) 2015 tshirtecommerce.com. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 *
 */
// add design idea to url design tool
add_filter( 'tshirt_set_url_designer', 'designer_url_idea');
function designer_url_idea($url)
{
	if( isset($_GET['idea_id']) )
	{
		$idea_id = $_GET['idea_id'];
	}
	else
	{
		$idea_id 	= get_query_var('idea_id', 0);
	}
	$temp = explode('-', $idea_id);
	$idea_id = $temp[0];
	if($idea_id > 0)
	{
		$url = add_query_arg( array('idea_id'=> $idea_id, 'light_box'=>1), $url);
	}
	elseif( stripos($url, '&id=') > 0 && is_product())
	{
		$url = add_query_arg( array('light_box'=>1), $url );
	}
	
	return $url;
}
 
// check api key
add_action( 'wp_ajax_store_check_api', 'store_check_api' );
function store_check_api()
{
	if(empty($_GET['api']))
	{
		echo 0;
	}
	else
	{
		$api 	= $_GET['api'];
		$url 	= 'http://api.9file.net/api/key/api_key/'.$api;		
		$result = openURL($url);
		if($result != false)
		{
			$info = json_decode($result);
			if(isset($info->error))
			{
				echo 0;
			}
			else
			{
				// update settings
				if (defined('DS') == false)
					define('DS', DIRECTORY_SEPARATOR);
			
				if (defined('ROOT') == false)
					define('ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))) .DS. 'tshirtecommerce');

				include_once(ROOT .DS. 'includes' .DS. 'functions.php');
				
				$dg = new dg();
				$settings = $dg->getSetting();
				
				$store = array(
					'enable' => 1,
					'api' => $api,
					'verified' => 1,
					'auto_download' => 1,
					'your_clipart' => 1,
					'exchange_rate' => 0.2,
				);
				$settings->store = $store;
				$dg->WriteFile(ROOT .DS. 'data' .DS. 'settings.json', json_encode($settings));
				echo 1;
			}
		}
		else
		{
			echo 0;
		}
	}
	exit;
}

// call ajax
add_action( 'wp_ajax_store_ajax_key', 'store_ajax_key' );
add_action( 'wp_ajax_nopriv_store_ajax_key', 'store_ajax_key' );
function store_ajax_key()
{
	ini_set('max_execution_time', 3000);
	$data = array(
		'error'		=> 0,
		'msg'		=> '',
		'reload'	=> 0
	);
	if( empty($_GET['api_key']) || empty($_GET['arts']) || empty($_GET['order_id']) )
	{
		$data['error']	= 1;
		$data['msg']	= 'Data design not found!';
	}
	else
	{
		$ids 	= str_replace(':', '-', $_GET['arts']);
		$url 	= 'http://api.9file.net/api/order/ids/'.$ids.'/order_number/'.$_GET['order_id'].'/api_key/'.$_GET['api_key'];
		$result = openURL($url);
		if($result != false)
		{
			$arts 	= json_decode($result, true);
			if(isset($arts['error']))
			{
				$data['error']	= 1;
				$data['msg']	= 'Data design not found!';
			}
			else
			{
				$params		= '';
				$art_prices	= array();
				foreach($arts as $id => $art)
				{
					if($art['price'] > 0)
					{
						$art_prices[$id]	= $art['price'];
						$art['key']			= 0;
					}
					if($params == '')
						$params		= $id.':'.$art['key'];
					else
						$params		.= ';'.$id.':'.$art['key'];
				}
				if(count($art_prices) > 0)
				{
					$data['error']	= 1;
					$data['msg']	= 'Please payment before download file design!';
				}
				store_art_update($_GET['order_id'], $params, $art_prices, $_GET['api_key']);
			}
		}
		else
		{
			$data['error']	= 1;
			$data['msg']	= 'Data design not found!';
		}		
	}
	echo json_encode($data); exit;
	exit;
}

// call ajax
add_action( 'wp_ajax_store_payment_art', 'store_payment_art' );
add_action( 'wp_ajax_nopriv_store_payment_art', 'store_payment_art' );
function store_payment_art()
{
	if(isset($_POST['e_order_id']) && $_POST['e_order_id'] != '' && isset($_POST['params']) && $_POST['params'] != '')
	{
		
		$e_order_id 	= $_POST['e_order_id'];
		$params 		= $_POST['params'];
		$api 			= $_POST['api'];
		store_art_update($e_order_id, $params, array(), $api);
	}
}

// update to design info after paid
function store_art_update($design_id, $params, $art_prices = array(), $api = '')
{
	ini_set('max_execution_time', 3000);
	$array 		= explode(';', $params);
	
	$arts 		= array();
	for($i=0; $i<count($array); $i++)
	{
		$art 	= explode(':', $array[$i]);
		if(count($art) > 1)
		{
			$arts[$art[0]] = $art[1];
		}
	}
	if (count($arts))
	{
		if (defined('ROOT') == false)
			define('ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))). '/tshirtecommerce');
		
		if (defined('DS') == false)
			define('DS', DIRECTORY_SEPARATOR);
		
		include_once (ROOT .DS. 'includes' .DS. 'functions.php');
		$dg = new dg();
		
		// update sales
		$file = ROOT .DS. 'data' .DS. 'store' .DS. 'arts_info.json';
		if(file_exists($file))
		{
			// call cache
			$cache 		= $dg->cache('store');
			$sales 		= $cache->get('sales');
			if($sales == null)
				$sales 	= array();
			
			$time 		= time();
			$month 		= date('Y_m', $time);
			$day 		= date('d', $time);
			
			if(empty($sales[$month]))
				$sales[$month]	= array();
			
			if(empty($sales[$month][$day]))
				$sales[$month][$day]	= array();
		
			$rows = json_decode( file_get_contents($file), true );
			foreach($arts as $clipar_id => $value)
			{
				if(isset($art_prices[$clipar_id])) continue;
				
				if(isset($rows[$clipar_id]))
				{
					if(isset($rows[$clipar_id]['sales']))
						$rows[$clipar_id]['sales'] = $rows[$clipar_id]['sales'] + 1;
					else
						$rows[$clipar_id]['sales']	= 1;
					
					if( isset($sales[$month][$day][$clipar_id]) )
					{
						$sales[$month][$day][$clipar_id] = $sales[$month][$day][$clipar_id] + 1;
					}
					else
					{
						$sales[$month][$day][$clipar_id] = 1;
					}
				}
			}
			$dg->WriteFile($file, json_encode($rows));
			$cache->set('sales', $sales, 933120000);
		}
		
		$obj 		= explode(':', $design_id);
		if(count($obj) > 1 && $obj[0] != 'cart')
		{
			$cache 		= $dg->cache('design');
			$designs 	= $cache->get($obj[0]);
			if($designs == null)
			{
				$cache 		= $dg->cache('admin');
				$designs 	= $cache->get($obj[0]);
			}
			
			if(isset($designs[$obj[1]]))
			{
				$design		= $designs[$obj[1]];
			}
			else
			{
				$design		= array();
			}
			$design_id 		= $obj[0];
		}
		else
		{
			$cache 		= $dg->cache('cart');
			$design 		= $cache->get($design_id);
		}
		
		if(isset($design['vectors']))
		{
			$design['vector'] = $design['vectors'];
			unset($design['vectors']);
		}
		
		if(isset($design['vector']))
		{
			if(is_array($design['vector']))
			{
				$vectors = $design['vector'];
			}
			else
			{
				$vectors = json_decode($design['vector'], true);
			}			
			if(count($vectors))
			{
				foreach($vectors as $view => $items)
				{
					if (count($items))
					{
						foreach($items as $id => $item)
						{
							if (isset($item['clipar_type']) && empty($item['clipar_paid']))
							{
								if( isset($art_prices[$item['clipart_id']]) )
								{
									$items[$id]['price'] = $art_prices[$item['clipart_id']];
									continue;
								}
								if( isset( $arts[ $item['clipart_id'] ] ) )
								{									
									$items[$id]['clipar_paid'] = 1;
									if((isset($item['file']) && is_string($item['file']) && $item['file'] == 'svg') || (isset($item['file']['type']) && $item['file']['type'] == 'svg'))
									{
										$svg 	= StorestrSVG($item['svg'], $arts[ $item['clipart_id'] ]);
									}
									else
									{
										$key_active 	= str_replace(' ', '+', $arts[ $item['clipart_id'] ]);
										$svg			= $item['svg'];
										$key 			= md5( $key_active );
										
										$url 			= 'http://api.9file.net/api/orderPNG/id/'.$item['clipart_id'].'/key/'.$key.'/api_key/'.$api;
										$result 		= openURL($url);
										if($result != false)
										{
											$data	= json_decode($result, true);
											if(isset($data['content']))
											{										
												$png 	= encrypt_compress($key_active, base64_decode($data['content']));
												$img 	= 'data:image/png;base64,' . base64_encode($png);
									
												$temp1 = explode('xlink:href="', $item['svg']);
												if(count($temp1) > 1)
												{
													$temp2 = explode('">', $temp1[1]);
													if(count($temp2) > 1)
													{
														$svg 	= $temp1[0] .'xlink:href="'. $img .'">'. $temp2[1];
													}
													
												}
											}
										}
									}
									$items[$id]['svg'] = $svg;
								}
							}
						}
						$vectors[$view]	= $items;
					}
				}
				
			}
			$design['vector'] = json_encode($vectors);
			if( isset($designs) && isset($obj[1]) && isset($designs[$obj[1]]))
			{
				$designs[$obj[1]] = $design;
				$design = $designs;
			}
			$cache->set($design_id, $design);
		}
	}
}

function e_custom_class($classes)
{
	$classes[] = 'woocommerce';
	$classes[] = 'woocommerce-page';
	return $classes;
}

// page store
add_shortcode( 'tshirtecommerce_store', 'tshirtecommerce_store_page');
function tshirtecommerce_store_page($atts, $content)
{
	global $TSHIRTECOMMERCE_ROOT, $tshirt_settings;

	wp_enqueue_script( 'designer_store_js', plugins_url( 'assets/js/product.js', dirname(__FILE__) ), array(), '1.0.0', true );

	if(isset($atts['product_id']))
		$product_id = $atts['product_id'];
	
	if (defined('ROOT') == false)
		define('ROOT', $TSHIRTECOMMERCE_ROOT);
	
	if (defined('DS') == false)
		define('DS', DIRECTORY_SEPARATOR);
	
	include_once (ROOT .DS. 'includes' .DS. 'functions.php');
	$dg = new dg();
	
	$settings 	= $dg->getSetting();
	
	$is_store = false;
	if(
		isset($settings->store) 
		&& isset($settings->store->enable) 
		&& $settings->store->enable == 1
		&& isset($settings->store->verified) 
		&& $settings->store->verified == 1
		&& isset($settings->store->api) 	
		&& $settings->store->api != ''		
	)
	{
		$is_store = true;
	}
	
	if($is_store == false)
		return 'Please active store in admin page!';
	
	global $wc_cpdf, $wp_query;

	add_filter( 'body_class', 'e_custom_class' );
		
	// find all product design
	$args = array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => -1);
	$data = get_posts( $args );
		
	//get product design
	$product_ids 	= array();
	$post_ids 		= array();
	$products 		= array();
	foreach ($data as $product)
	{
		$ids = $wc_cpdf->get_value($product->ID, '_product_id');
		if ($ids != '')
		{
			$temp = explode(':', $ids);
			if (count($temp) == 1)
			{
				$product->design_id 		= $temp[0];
				$products[$product->ID]		= $product;				
				$product_ids[]			= $product->design_id;
				$post_ids[$product->design_id] = $product->ID;
			}
		}	
	}
	$product_id = 0;	
	if( isset($_GET['product_id']) )
	{
		$product_id	= $_GET['product_id'];
	}

	$lang = $dg->lang('lang.ini', false); 
	
	ob_start();
	
	// get ideas
	include_once (ROOT .DS. 'includes' .DS. 'store.php');
	$store		= new store($settings);
	$store->dg 		= $dg;

	if($product_id > 0)
	{
		$product = $products[$product_id];
		$design_id = $product->design_id;
	}

	if(isset($design_id))
	{
		$ideas		= $store->getIdeas($design_id);
	}
	else
	{
		$ideas		= $store->getIdeas(0);
	}

	//search
	$options = array();
	if( isset($_GET['cate_id']) )
	{
		$options['cate_id'] 	= $_GET['cate_id'];
	}
	if( isset($_GET['keyword']) )
	{
		$options['keyword'] 	= $_GET['keyword'];
	}
	if(count($options))
	{
		$ideas		= $store->ideas($ideas, $options);	
	}
	
	// get product design data
	$products_design 	= $dg->getProducts();
	$gallery		= array();
	$product_urls 	= array();
	if(isset($design_id))
	{
		for($i=0; $i < count($products_design); $i++)
		{
			$product = $products_design[$i];
			if ($design_id == $product->id)
			{
				$type = 0;
				if( isset($product->store) && isset($product->store->types) && count($product->store->types) > 0 )
				{
					$types = $product->store->types;
					$type = $types[0];
				}
				if(isset($product->gallery))
				{
					$gallery[$type] = $product->gallery;
				}
				$product_urls[$type] = $post_ids[$product->id];
				break;
			}
		}
	}
	else
	{
		for($i=0; $i < count($products_design); $i++)
		{
			$product = $products_design[$i];
			if(!in_array($product->id, $product_ids)) continue;

			if( isset($product->store) && isset($product->store->types) && count($product->store->types) > 0 )
			{
				$types = $product->store->types;
				if(isset($product->gallery) && $product->gallery != '')
				{
					foreach ($types as $value)
					{
						if(empty($gallery[$value]))
						{
							$gallery[$value] = $product->gallery;
							$product_urls[$value] = $post_ids[$product->id];
						}
					}
				}
				else
				{
					$product_urls[0] = $post_ids[$product->id];
				}
			}
		}
	}

	/* Begin code version free */
	$gallery_class = '';
	if(count($gallery) == 0)
	{
		$gallery_class = 'gallery-none';
		$box_width = 500;
		if(isset($product->box_width))
		{
			$box_width = (int) $product->box_width;
		}
		if( isset($product->design) && isset($product->design->front) )
		{
			$zoom 	= 220/$box_width;
			
			$front = $product->design->front;
			
			// get area design
			$area 			= json_decode(str_replace("'", '"', $product->design->area->front));
			$width 			= str_replace('px', '', $area->width);
			$area->width 	= $width * $zoom;
			
			$height 		= str_replace('px', '', $area->height);
			$area->height 	= $height * $zoom;
			
			$top 			= str_replace('px', '', $area->top);
			$area->top 		= $top * $zoom;
			
			$left 			= str_replace('px', '', $area->left);
			$area->left 	= $left * $zoom;
			
			if($area->zIndex < 1)
			{
				$area->zIndex = 100;
			}
		}
	}
	/* end code */

	include_once(dirname(__FILE__).'/store/store.php');
	return ob_get_clean();
}

function StorestrSVG($svg, $key)
{
	$key		= str_replace(' ', '+', $key);
	if ($svg == '') return '';
	
	$params = explode('/', $svg);
	$n 			= count($params);
	
	$str 		= '';
	for($i=0; $i<$n; $i++)
	{
		$number = $params[$i];
		$s 		= substr($key, $number, 1);
		$str 	.= $s;
	}
	
	$output = base64_decode($str);
	return $output;
}

function encrypt_compress($key, $str) 
{
	$s = array();
	for ($i = 0; $i < 256; $i++) {
		$s[$i] = $i;
	}
	$j = 0;
	for ($i = 0; $i < 256; $i++) {
		$j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
		$x = $s[$i];
		$s[$i] = $s[$j];
		$s[$j] = $x;
	}
	$i = 0;
	$j = 0;
	$res = '';
	$count = strlen($str);
	for ($y = 0; $y < $count; $y++) {
		$i = ($i + 1) % 256;
		$j = ($j + $s[$i]) % 256;
		$x = $s[$i];
		$s[$i] = $s[$j];
		$s[$j] = $x;
		$res .= $str[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
	}
	return $res;
}
?>