<?php
class e_designer_setting
{
	private $step   	= '';
	private $error   	= array();
	private $path   	= array();
	
	public function __construct()
	{
		add_action( 'admin_menu', array($this, 'add_settings') );
		
		/* show notification config currency in plugin of page setting woocommerce. */
		add_filter('woocommerce_general_settings', array($this, 'settings_price'), 10);
		
		/* show document */
		add_action( 'admin_footer', array($this, 'introduction'), 10 );
		
		//add_action('tshirtecommerce_setting', array($this, 'hide_introduction'), 10 );
		
		/* import data */		
		add_action( 'wp_ajax_download_products', array($this, 'download_products'), 10 );
		add_action( 'wp_ajax_import_products', array($this, 'import_products'), 10 );
		
		/* active plugin */
		add_action( 'admin_init', array($this, 'ini'), 10 );
	}
	
	/*
	* call when active plugin
	*/
	public function ini()
	{
		if (get_option('tshirtecommerce_plugin_activate', false)) 
		{
			delete_option('tshirtecommerce_plugin_activate');
			exit( wp_redirect( admin_url( 'index.php?page=tshirtecommerce-setting' ) ) );
		}
	}
	
	public function settings_price($settings)
	{
		if(count($settings))
		{
			$options = array();
			foreach($settings as $setting)
			{
				if($setting['id'] == 'woocommerce_currency')
				{
					$options[] = array(
						'title' => __( 'Currency and Price displayed in design tool', 'woocommerce' ), 
						'type' => 'title', 
						'desc' => __( 'Please go to <strong>T-Shirt eCommerce > Settings > Configuration > tab "Your Price"</strong> and setting currency, price display.', 'woocommerce' ), 
						'id' => 'tshirtecommerce_settings_price' 
					);
				}
				$options[] = $setting;
			}
			$settings = $options;
		}
		return $settings;
	}

	function add_settings()
	{
		add_dashboard_page( '', '', 'manage_options', 'tshirtecommerce-setting', '' );
		add_action( 'admin_init', array($this, 'tshirtecommerce_wizard') );
	}
	
	public function tshirtecommerce_wizard()
	{
		if ( empty( $_GET['page'] ) || 'tshirtecommerce-setting' !== $_GET['page'] )
		{
			return;
		}
		
		if( empty($_GET['step']) )
		{
			$step 		= 'designer';
		}
		else
		{
			$step 		= $_GET['step'];
		}
		
		ob_start();
		include_once('setup/header.php');
		
		$this->path		= dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/tshirtecommerce';
		
		switch($step)
		{
			case 'store':
				$this->store_page();
				break;
				
			case 'product':
				$this->product_page();
				break;
				
			case 'complete':
				$this->complete_page();
				break;
			default:
				$this->designer_page();
				break;
		}
		include_once('setup/footer.php');
		exit;
	}
	
	public function designer_page()
	{
		$this->check_file('settings.json');
		$this->check_file('layouts.json');
		$this->check_file('languages.json');
		
		$error 				= $this->error;
				
		$pages 				= get_pages();
		$settings_option 	= get_option( 'online_designer' );
		if(empty($settings_option['url']))
		{
			$settings_option['url'] == '';
		}
		
		$layouts			= $this->get_data('layouts.json');
		$languages			= $this->get_data('languages.json');
		
		$currency_active	= get_option('woocommerce_currency');
		$wo_currency_pos 	= get_option( 'woocommerce_currency_pos' );
		if($wo_currency_pos != 'left')
		{
			$wo_currency_pos = 'right';
		}
		$price_thousand 	= get_option( 'woocommerce_price_thousand_sep' );
		$price_decimal 		= get_option( 'woocommerce_price_decimal_sep' );
		$price_number 		= get_option( 'woocommerce_price_num_decimals' );
		
		$currencies 		= $this->get_data('currencies.json');
		
		include_once('setup/step1.php');
	}
	
	function designer_save($data)
	{
		// update setting in plugin
		$settings_option 				= get_option( 'online_designer' );
		if(isset($data['page_url']) && $data['page_url'] > 0)
		{
			$settings_option['url'] 	= $data['page_url'];
			update_option( 'online_designer', $settings_option );
		}
		
		// active languages
		if(isset($data['language']) && $data['language'] != '')
		{
			$languages			= $this->get_data('languages.json');
			if($languages != false && count($languages) > 0)
			{
				$new_data = array();
				foreach($languages as $language)
				{
					if($language->code == $data['language'])
					{
						$language->default = 1;
					}
					else
					{
						$language->default = 0;
					}
					$new_data[] = $language;
				}
				$this->WriteFile( 'languages.json', json_encode($new_data) );
			}
		}
		
		if(isset($data['setting']))
			$setting 		= $data['setting'];
		else
			$setting 		= array();
		
		// update layout active
		if( isset($data['layout']) && $data['layout'] != '' )
		{
			$layouts					= $this->get_data('layouts.json');
			if($layouts != false && count($layouts) > 0)
			{
				$new_data = array();
				foreach($layouts as $layout)
				{
					if($layout->name == $data['layout'])
					{
						$layout->default = 1;
						
						$setting['themes'] 	= $layout->theme;
						$setting['theme']	= array(
							$layout->theme => array()
						);
						if( isset($layout->options) && count($layout->options) )
						{
							$setting['theme'][$layout->theme] = json_decode(json_encode($layout->options));
						}
					}
					else
					{
						$layout->default = 0;
					}
					$new_data[$layout->name] = $layout;
				}
				$this->WriteFile( 'layouts.json', json_encode($new_data) );
			}
		}
		
		// update setting
		if(count($setting))
		{
			$settings			= $this->get_data('settings.json');
			foreach($setting as $key => $value)
			{
				$settings->{$key} = $value;
			}
			$this->WriteFile( 'settings.json', json_encode($settings) );
		}
		
	}
	
	function import_products()
	{
		$result = array(
			'error' => 0,
			'msg'	=> ''
		);
		
		if(empty($_GET['step']))
		{
			$step = 1;
		}
		else
		{
			$step = $_GET['step'];
		}
		
		$file = dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/tshirtecommerce/data/products.json';
		if( file_exists($file) )
		{
			ini_set('max_execution_time', 3000);
			ini_set("memory_limit",-1);
			$content 		= file_get_contents($file);
			if( $content != false )
			{
				$products 	= json_decode($content);
				if( count($products) && isset($products->products) && count($products->products))
				{
					include_once( dirname(dirname(__FILE__)).'/helper/class-wc-api-products.php' );
					$api 		= new TShirt_API_Products();
					$number		= 5;
					$min		= ($step - 1) * $number;
					$max 		= $step * $number;
					foreach($products->products as $i => $product)
					{
						if($i < $min)
						{
							continue;
						}
						if($i >= $max)
						{
							echo 1; exit;
						}
						$data = array();
						$data['title']					= $product->title;
						$data['short_description']		= $product->short_description;
						$data['description']			= $product->description;
						$data['regular_price']			= $product->price;
						$data['sku']					= $product->sku;
						
						if($product->image != '')
						{
							$data['images']			= array();
							$data['images'][] = array(
								'src' => $product->image,
								'position' => 0
							);
						}

						$id = $api->create_product($data);
						// update product
						if ($id > 0)
						{
							$options_value = array(
								array(
									'_product_id' => $product->id,
									'_product_title_img' => $product->title,
								)
							);				
							update_post_meta( $id, 'wc_productdata_options', $options_value );
						}
					}
				}
			}
		}
		else
		{
			$result['error']	= 1;
			$result['msg'] 		= 'File not found!';
		}
		
		echo json_encode($result);
		exit;
	}
	
	function download_products()
	{
		$result = array(
			'error' => 0,
			'msg'	=> ''
		);
		$file 		= 'http://updates.tshirtecommerce.com/products_import.zip';
		$data 		= openURL($file);
		if ($data != false)
		{
			$path = dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/products_import.zip';
			if(file_put_contents($path, $data))
			{
				
				WP_Filesystem();
				$unzipfile = unzip_file( $path, dirname(dirname(dirname(dirname(dirname(__FILE__))))) .'/' );
				unlink($path);
				if ( $unzipfile ) 
				{
					$result['msg'] = 'Update successful!';
				}
				else
				{
					$result['error']	= 1;
					$result['msg'] 		= 'Sorry, you can not import products because your server not support unzip. Please check config your server and try again!';
				}
			}
			else
			{
				$result['error']	= 1;
				$result['msg'] 		= 'Sorry, you can not import products because your server not allow writable file. Please check your server and try again!';
			}
		}
		else
		{
			$result['error']	= 1;
			$result['msg'] 	= 'Sorry, you can not import products because your server not allow download data from our site!';
		}
		echo json_encode($result);
		exit;
	}
	
	public function store_page()
	{
		if(isset($_POST))
		{
			$this->designer_save($_POST);
		}
		
		$error 				= $this->error;
		include_once('setup/step2.php');
	}
	
	public function product_page()
	{
		$error 				= $this->error;
		include_once('setup/step3.php');
	}
	
	public function complete_page()
	{
		$settings_option 	= get_option( 'online_designer' );
		if(isset($settings_option['url']))
		{
			$page 	= get_page_link($settings_option['url']);
		}
		else
		{
			$page	= '';
		}
		$error 				= $this->error;
		include_once('setup/step4.php');
	}
	
	public function introduction()
	{
		$settings_option 	= get_option( 'online_designer' );
		if (isset($settings_option['hide_help']) && $settings_option['hide_help'] == 1)
		{
			return '';
		}
		//include_once('setup/help.php');
	}
	
	public function hide_introduction($values)
	{
		echo '<tr valign="top">'
			.'<th scope="row" class="titledesc">Hide box help</th>'
			.'<td class="forminp-text">';
		if (isset($values['hide_help']) && $values['hide_help'] == 1)
		{
			echo '<input type="checkbox" value="1" name="designer[hide_help]" checked="checked">';
		}
		else
		{
			echo '<input type="checkbox" value="1" name="designer[hide_help]">';
		}	
		echo '</td></tr>';
	}
	
	private function check_file($file, $folder = 'data')
	{
		$file = $this->path .'/'. $folder .'/'. $file;

		if(!file_exists($file))
		{
			$this->error[] = 'File <strong>'.$file.'</strong> not found!';
		}
		elseif(is_writable($file) !== true)
		{
			$this->error[] = 'Server not allow write file <strong>'.$file.'</strong>. Please set permissions this file and try again.';
		}
	}
	
	private function get_data($file, $folder = 'data')
	{
		$file = $this->path .'/'. $folder .'/'. $file;
		
		if(!file_exists($file))
		{
			return false;
		}
		elseif(is_writable($file) !== true)
		{
			return false;
		}
		else
		{
			$content = @file_get_contents($file);
			if($content !== false)
			{
				$data = json_decode($content);
				return $data;
			}
		}
		return false;
	}
	
	private function WriteFile($file, $data, $folder = 'data')
	{
		$path = $this->path .'/'. $folder .'/'. $file;
		
		if ( ! $fp = @fopen($path, 'w'))
		{
			return FALSE;
		}

		flock($fp, LOCK_EX);
		fwrite($fp, $data);
		flock($fp, LOCK_UN);
		fclose($fp);

		return TRUE;
	}
}
$t_introduction = new e_designer_setting();
?>