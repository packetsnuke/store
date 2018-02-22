<?php
add_action('tshirtecommerce_html', 'tshirtecommerce_html_auto_redirect_cart');
function tshirtecommerce_html_auto_redirect_cart($values)
{
	if (isset($values['redirect_cart']) && $values['redirect_cart'] == 1)
	{
		echo '<script type="text/javascript">var auto_redirect_cart = "1";</script>';
	}

	if(is_product())
	{
		$values['mobile_layout'] = 1;
	}
	
	if (isset($values['mobile_layout']) && $values['mobile_layout'] == 1)
	{
		echo '<script type="text/javascript">var disable_mobile_layout = "1";</script>';
	}
	else
	{
		echo '<script type="text/javascript">var disable_mobile_layout = "0";</script>';
	}
}

add_action('tshirtecommerce_setting_general', 'design_order_detail_setting', 10);
function design_order_detail_setting($values)
{
	echo '<tr valign="top">'
			.'<th scope="row" class="titledesc">Disable Mobile Layout:</th>'
			.'<td class="forminp-text"><label>';
	if (isset($values['mobile_layout']) && $values['mobile_layout'] == 1)
	{
		echo '<input type="checkbox" value="1" name="designer[mobile_layout]" checked="checked">';
	}
	else
	{
		echo '<input type="checkbox" value="1" name="designer[mobile_layout]">';
	}
	echo 'Disable show full layout of designer on mobile.</label>';
	echo '</td></tr>';
}

add_action('woocommerce_order_item_meta_end', 'design_order_detail', 1, 30);
function design_order_detail($item_id, $item, $order)
{
	if (defined('ROOT') == false)
		define('ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))). '/tshirtecommerce');
	if (defined('DS') == false)
		define('DS', DIRECTORY_SEPARATOR);
	include_once (ROOT .DS. 'includes' .DS. 'functions.php');
	$dg = new dg();
	$lang = $dg->lang('lang.ini', false);
	
	$settings = get_option( 'online_designer' );
	if ( isset($settings['allow_download']) && $settings['allow_download'] == 1 )
	{
		$download = true;
	}
	else
	{
		$download = false;
		if(isset($_SESSION['download_design']))
			unset($_SESSION['download_design']);
	}
	
	$data = @WC_Abstract_Order::get_item_meta( $item_id, "custom_designer", true );	
	
	echo '<div style="float: right;text-align: left;">';
	if (isset($data['design_id']) && $data['design_id']!= '' && $data != null && count($data) > 0)
	{
		if($download)
		{
			$download_design = array();
			if(isset($_SESSION['download_design']))
				$download_design = $_SESSION['download_design'];
			
			if(!in_array($data['design_id'], $download_design))
				$download_design[] = $data['design_id'];
			
			$_SESSION['download_design'] = $download_design;
		}
		if (isset($data['images']))
		{

		$images = json_decode(str_replace('\\', '', $data['images']));
			if (count($images))
			{
				echo '<table><tr>';
				foreach($images as $view => $image)
				{
					echo '<td>';
					echo '<img style="width: 100px;" src="'. network_site_url('tshirtecommerce').'/'.$image .'" with="100">';
					if ($download == true)
					{
						echo '<br /><a target="_blank" href="'.network_site_url('tshirtecommerce/design.php?key='.$data['design_id'].'&view='.$view).'">'.$lang['order_download_design'].'</a>';
					}
					echo '</td>';
				}
				echo '</tr></table>';
			}				
		}
		
		if (isset($data['teams']['name']) && count($data['teams']['name']) > 0)
		{
			echo '<table>'
				. 		'<thead>'
				. 			'<tr>'
				. 				'<th>'.$lang['designer_team_name'].'</th>'
				. 				'<th>'.$lang['designer_team_number'].'</th>'
				. 				'<th>'.$lang['designer_team_size'].'</th>'
				. 			'</tr>'
				. 		'</thead>'
				. 		'<tbody>';
				

		for($i=1; $i<=count($data['teams']['name']); $i++ )
			{
				$size = explode('::', $data['teams']['size'][$i]);
				echo 		'<tr>'
					.			'<td>'.$data['teams']['name'][$i].'</td>'
					.			'<td>'.$data['teams']['number'][$i].'</td>'
					.			'<td>'.$size[0].'</td>'
					.		'</tr>';
			}
			
			echo 		'</tbody></table>';
		}
				
		if (isset($data['color_title']))
		{
			echo '<p>'.$lang['designer_color'].': '.$data['color_title'].'</p>';
		}
		
		if ($data['options'] != '' && $data['options'] != '[]')
		{
			if (is_string($data['options']))
				$options = json_decode( str_replace('\\"', '"', $data['options']), true);
			else
				$options = $data['options'];
			
			
			if (count($options) > 0)
			{
				foreach($options as $i => $option)
				{
					
					if (isset($option['type']) && file_exists( dirname(__FILE__) .'/options/'.$option['type'].'.php' ) )
					{
						require_once(dirname(__FILE__) .'/options/'.$option['type'].'.php');
						continue;
					}
					
					if (isset($options[$i]) && isset($options[$i]['value']))
					{
						if (is_string($options[$i]['value']) && $options[$i]['value'] == '') continue;
						if (is_array($options[$i]['value']) && count($options[$i]['value']) == 0) continue;
							
						echo '<dt>'.$options[$i]['name'].': </dt>';
						
						echo '<dd>';
						if (is_array($options[$i]['value']))
						{							
							foreach ($options[$i]['value'] as $name => $value)
							{
								if ($value == '0' || $value == '') continue;
								echo $name.'  -  '.$value. '; ';
							}
						}
						else
						{
							echo $options[$i]['value'];
						}
						echo '</dd>';
					}
				}
			}
		}
		
		if ($data['design_id'] != 'blank')
		{
			$product_id = $item['product_id'];
			$page = get_page_link($settings['url']);
			$page = apply_filters( 'update_link_edit_design', $page, $product_id, $settings);
			$link = add_query_arg( array('product_id'=>$product_id, 'cart_id'=>$data['design_id']), $page );
			
			echo '<p><a href="'.$link.'" title="'.$lang['designer_cart_edit_des'].'">'.$lang['designer_cart_edit'].'</a></p>';
		}
	}
	else
	{
		global $wc_cpdf;		
		$product_id = $wc_cpdf->get_value($item['product_id'], '_product_id');
		
		if($download && $product_id != '')
		{
			$download_design = array();
			if(isset($_SESSION['download_design']))
				$download_design = $_SESSION['download_design'];
			
			if(!in_array($product_id, $download_design))
				$download_design[] = $product_id;
			$_SESSION['download_design'] = $download_design;
		
			$page = get_page_link($settings['url']);				
			$link = add_query_arg( array('design'=>$product_id, 'parent_id'=>$item['product_id']), $page );
			
			$ids = explode(':', $product_id);
			echo '<p>'
					.'<a href="'.$link.'" target="_blank" title="'.$lang['order_click_to_view_design'].'">'.$lang['order_view_design'].'<strong></strong></a> '.$lang['order_or_download_design'].':'
					.' <a href="'.network_site_url('tshirtecommerce/design.php?idea=1&key='.$product_id).'&view=front" target="_blank" title="'.$lang['order_click_to_view_design'].'">'.$lang['front'].'<strong></strong></a> - '
				.' <a href="'.network_site_url('tshirtecommerce/design.php?idea=1&key='.$product_id).'&view=back" target="_blank" title="'.$lang['order_click_to_view_design'].'"><strong>'.$lang['back'].'</strong></a> - '
					.' <a href="'.network_site_url('tshirtecommerce/design.php?idea=1&key='.$product_id).'&view=left" target="_blank" title="'.$lang['order_click_to_view_design'].'"><strong>'.$lang['left'].'</strong></a> - '
					.' <a href="'.network_site_url('tshirtecommerce/design.php?idea=1&key='.$product_id).'&view=right" target="_blank" title="'.$lang['order_click_to_view_design'].'"><strong>'.$lang['right'].'</strong></a>'
				. '</p>';
		}
	}
	echo '</div>';
}
?>