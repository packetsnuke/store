<?php
add_action('tshirtecommerce_setting_general', 'design_purchased_code_setting', 50);
function design_purchased_code_setting($values)
{
	echo '<tr valign="top">'
			.'<th scope="row" class="titledesc">Purchase code:</th>'
			.'<td class="forminp-text">';
	if (isset($values['purchased_code']) && $values['purchased_code'] != '')
	{
		$values['purchased_code'] = str_replace(' ', '', $values['purchased_code']);

		if (empty($values['verified_code'])) $values['verified_code'] = 0;
		if ($values['verified_code'] == 1)
		{
			echo '<input type="text" size="50" value="'.$values['purchased_code'].'" name="designer[purchased_code]"> <span style="background-color:#5CB85C;font-size:12px;color:#fff;font-weight:bold;padding:6px 11px;">Verified</span>';
		}
		else
		{
			echo '<input type="text" size="50" value="'.$values['purchased_code'].'" name="designer[purchased_code]"> <span style="background-color:#D9534F;font-size:12px;color:#fff;font-weight:bold;padding:6px 11px;">NO-Verified</span>';
		}
		echo '<p><a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-can-I-find-my-Purchase-Code-" target="_bank">Click here</a> for instructions to find your purchase code.</p>';
	}
	else
	{
		echo '<input type="text" size="50" value="" name="designer[purchased_code]">';
		echo '<p><a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-can-I-find-my-Purchase-Code-" target="_bank">Click here</a> for instructions to find your purchase code.</p>';
	}	
	echo '</td></tr>';
}

// veriry purchase code
add_filter('tshirtecommerce_settings_save', 'tshirtecommerce_settings_purchase_code');
function tshirtecommerce_settings_purchase_code($values)
{
	$values['verified_code'] = 0;
	if(isset($values['purchased_code']))
	{
		$values['purchased_code'] = str_replace(' ', '', $values['purchased_code']);
		$url 		= 'http://updates.tshirtecommerce.com/verify_purchase.php?code='.$values['purchased_code'].'&platform=woocommerce';
		$content 	= openURL($url);
		if ($content != false)
		{
			$result = json_decode($content, true);
			if( isset($result['error']) && $result['error'] == 0 )
			{
				$values['verified_code'] = 1;
			}
		}
	}
	
	return $values;
}
?>