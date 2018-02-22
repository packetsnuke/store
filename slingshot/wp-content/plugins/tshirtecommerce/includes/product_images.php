<?php
// add custom product image
add_action( 'wp_enqueue_scripts', 'tshirtecommerce_product_image_scr' );
function tshirtecommerce_product_image_scr()
{
	if(is_product())
	{
		echo '<script type="text/javascript">var URL_d_home = "'.home_url().'";var design = {};</script>';
		wp_enqueue_script( 'designer_js_image', plugins_url( 'assets/js/product_detail.js', dirname(__FILE__) ), array(), '1.0.0', true );
		//wp_enqueue_script( 'designer_js_store', get_site_url() . '/tshirtecommerce/addons/js/store.js', array(), '1.0.0', true );
		wp_enqueue_script( 'designer_js_spectrum', get_site_url() . '/tshirtecommerce/addons/js/spectrum.js', array(), '1.0.0', true );
		wp_enqueue_style( 'designer_css_spectrum', get_site_url() . '/tshirtecommerce/addons/css/spectrum.css', array());
	}
}

// ajax create image thumb
add_action( 'wp_ajax_tshirtecommerce_quick_add_cart', 'tshirtecommerce_quick_add_cart' );
add_action( 'wp_ajax_nopriv_tshirtecommerce_quick_add_cart', 'tshirtecommerce_quick_add_cart' );
function tshirtecommerce_quick_add_cart()
{
	$result = array(
		'error' => 1
	);
	if(empty($_POST['views']))
	{
		echo json_encode($result);
		exit;
	}
	
	$data = $_POST['views'];
	if(count($data))
	{
		if (defined('ROOT') == false) // fix define is exists.
			define('ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))). '/tshirtecommerce');
		if (defined('DS') == false)
			define('DS', DIRECTORY_SEPARATOR);
		include_once (ROOT .DS. 'includes' .DS. 'functions.php');
		$dg = new dg();
		
		// get design idea
		$rowid		= $_POST['rowid'];
		$params = explode(':', $rowid);
		if (count($params) > 1)
		{
			$cache = $dg->cache();
			$designs = $cache->get($params[0]);
			if ($designs == null || empty($designs[$params[1]]))
			{
				$cache = $dg->cache('admin');
				$designs = $cache->get($params[0]);
			}
			
			if (isset($designs[$params[1]]))
			{
				$design = $designs[$params[1]];
			}
		}
		if(empty($design))
		{
			echo json_encode($result);
			exit;
		}
		
		$design['vectors'] = $_POST['vectors'];
		
		$result['error']	= 0;
		$images = array();
		
		$box_width = $_POST['box_width'];
		$box_height = $_POST['box_height'];
		foreach($data as $view=>$items)
		{
			$html = '<svg  width="'.$box_width.'" height="'.$box_height.'" viewBox="0 0 '.$box_width.' '.$box_height.'" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="none">';
			if(count($items)> 0)
			{
				foreach($items as $item)
				{
					if( isset($item['svg']) )
					{						
						$svg = $item['svg'];
						$svg = str_replace('\\"', '"', $svg);
						$svg = str_replace('\'', '"', $svg);
						$svg = str_replace('\"', '"', $svg);
						if(strpos($svg, 'data:image') === false)
						{
							preg_match_all("/xlink:href=\"(.*)\">/i", $svg, $links);
							if (isset($links[1][0]))
							{
								if (strpos($links[1][0], 'image/PNG;base64') === false && strpos($links[1][0], '#textPath') === false)
								{
									if(strpos($links[1][0], 'http') === false)
									{
										$temp = explode('tshirtecommerce', $links[1][0]);
										$src = get_site_url() . '/tshirtecommerce'.$temp[1];
									}
									else
									{
										$src = $links[1][0];
									}
									$image 	= openURL($src);
									$base64 = 'data:image/PNG;base64,' . base64_encode($image);
									$svg = str_replace($links[1][0], $base64, $svg);
								}
							}
						}
								
						$html .= $svg;
					}
					elseif( isset($item['type']) && $item['type'] == 'product' )
					{
						if(strpos($item['src'], 'http') === false)
						{
							$temp = explode('tshirtecommerce', $item['src']);
							$src = get_site_url() . '/tshirtecommerce'.$temp[1];
						}
						else
						{
							$src	= $item['src'];
						}
						
						$image = openURL($src);
						$base64 = 'data:image/PNG;base64,' . base64_encode($image);
						
						$html .= '<svg  width="'.$item['width'].'" x="'.$item['left'].'" y="'.$item['top'].'" height="'.$item['height'].'" viewBox="0 0 '.$item['width'].' '.$item['height'].'" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" preserveAspectRatio="none">'
								.'<image x="0" y="0" width="'.$item['width'].'" height="'.$item['height'].'" preserveAspectRatio="none" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="'.$base64.'"></image>'
								.'</svg>';
					}
				}
			}
			$html .= '</svg>';
			$filename	= $view.'_'.md5( microtime().rand() ).'.svg';
			
			$path = $dg->folder() .DS. $filename;
			
			write_to_file($html, ROOT .DS. $path);
			
			$images[$view] = str_replace(DS, '/', $path);
			$design['images'][$view] = $images[$view];
		}
					
		$cache 					= $dg->cache();
		$id 					= md5( microtime().rand() );
		$designs 				= array();
		$designs[$params[1]]	= $design;
		$cache->set($id, $designs);
		
		$result['thumbs'] = $images;
		$result['rowid'] = $id.':'.$params[1];
		echo json_encode($result);
		exit;
	}
}

// load product design
add_action( 'wp_ajax_tshirtecommerce_product_image_load', 'tshirtecommerce_product_image_load' );
add_action( 'wp_ajax_nopriv_tshirtecommerce_product_image_load', 'tshirtecommerce_product_image_load' );
function tshirtecommerce_product_image_load()
{
	$result = array(
		'error'	=> 1,
	);
	if(isset($_POST['product_id']) && isset($_POST['index']))
	{
		$product_id	= (int) $_POST['product_id'];
		$index		= (int) $_POST['index'];
		
		// find product
		if($product_id > 0)
		{
			$json = dirname(dirname(dirname(dirname(dirname(__FILE__))))). '/tshirtecommerce/data/products.json';
			if (file_exists($json))
			{
				$string = file_get_contents($json);
				if ($string != false)
				{
					$products = json_decode($string);
					if ( isset($products->products) && count($products->products) > 0)
					{
						foreach($products->products as $product)
						{
							if ($product->id == $product_id)
							{
								$data = $product;
								break;
							}
						}
					}
				}
			}
			
			if(isset($data) && isset($data->design))
			{
				$design 			= $data->design;
				
				$result['hide_quickview'] = 0;
				if(isset($data->hide_quickview) && $data->hide_quickview == 1)
				{
					$result['hide_quickview'] = 1;
				}
				$result['error']	= 0;
				$result['design']	= $design;
			}
		}
	}
	
	header('Content-Type: application/json');
	echo json_encode($result);
	exit;
}

// load design template
add_action( 'wp_ajax_tshirtecommerce_design_load', 'tshirtecommerce_design_load' );
add_action( 'wp_ajax_nopriv_tshirtecommerce_design_load', 'tshirtecommerce_design_load' );
function tshirtecommerce_design_load()
{
	if(isset($_POST['design_id']))
	{
		$design_id	= $_POST['design_id'];
		
		$params 	= explode(':', $design_id);
		if(count($params) > 1)
		{
			if (defined('ROOT') == false)
				define('ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))). '/tshirtecommerce');
			if (defined('DS') == false)
				define('DS', DIRECTORY_SEPARATOR);
			include_once (ROOT .DS. 'includes' .DS. 'functions.php');
			$dg = new dg();
			
			if ($params[0] == 'cart')
			{
				$cache = $dg->cache('cart');
				$designs = $cache->get($params[1]);
				if ($designs != null)
				{
					$array[$params[1]] = $designs;
					$designs = $array;
				}
			}
			else
			{
				$cache = $dg->cache();
				$designs = $cache->get($params[0]);
			}
			if ($designs == null || empty($designs[$params[1]]))
			{
				$cache = $dg->cache('admin');
				$designs = $cache->get($params[0]);
			}
			
			if ($designs == null || empty($designs[$params[1]]))
			{
				return false;
			}
			else
			{
				$design = $designs[$params[1]];
				
				$lang = $dg->lang();
				$design['lang'] = array(
					'title' => $lang['quick_designer_head'],
					'image' => $lang['designer_menu_upload_image'],
					'change' => $lang['quick_designer_change'],
					'remove' => $lang['designer_team_remove'],
					'text_line' => $lang['quick_designer_text_line'],
					'apply' => $lang['quick_designer_apply'],
				);
				
				$file = ROOT .DS. 'data' .DS. 'store' .DS. 'fields.json';
				$fields = array();
				if(file_exists($file))
				{
					$content = file_get_contents($file);
					if($content != false)
					{
						$fields = json_decode($content, true);
					}
				}
				if(count($fields))
				{
					foreach($fields as $field)
					{
						$design['fields'][$field['id']]	= $field['title'];
					}
					
				}
				
				echo json_encode($design);
			}
		}
	}
	exit;
}

// load quick edit product
add_action('tshirtecommerce_product_attribute', 'designer_product_quick_edit', 10, 2);
function designer_product_quick_edit($values)
{
	echo '<div class="customize-design" style="display:none;"></div>';
}
?>