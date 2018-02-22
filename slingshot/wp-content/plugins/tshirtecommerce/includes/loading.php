<?php
add_action('tshirtecommerce_setting_general', 'design_loading_setting', 20);
function design_loading_setting($values)
{
	if(empty($values['logo_loading']))
	{
		$values['logo_loading'] = 'tshirtecommerce/assets/images/logo-loading.png';
	}
	if(empty($values['text_loading']))
	{
		$values['text_loading'] = 'Designer is Loading...';
	}
	if(empty($values['bg_loading']))
	{
		$values['bg_loading'] = 'FFFFFF';
	}
?>
	<tr valign="top">
		<th>
			<strong>Setting loading designer</strong>
		</th>
		<td>
			<p>
				<strong>URL logo</strong><br />
				<input type="text" size="60" value="<?php echo $values['logo_loading']; ?>" name="designer[logo_loading]"> 
			</p>
			<br />
			<p>
				<strong>Text loading</strong><br />
				<input type="text" size="60" value="<?php echo $values['text_loading']; ?>" name="designer[text_loading]">
			</p>
			<br />
			<p>
				<strong>Background color</strong><br />
				<input type="text" size="10" value="<?php echo $values['bg_loading']; ?>" name="designer[bg_loading]">
			</p>
		</td>
	</tr>
<?php
}

add_action('tshirtecommerce_html', 'tshirtecommerce_loading', 10);
function tshirtecommerce_loading($values)
{
	$logo_loading 	= 'tshirtecommerce/assets/images/logo-loading.png';
	if (isset($values['logo_loading']))
	{
		$logo_loading 	= $values['logo_loading'];
	}
	
	$text_loading 	= 'Designer is Loading...';
	if (isset($values['text_loading']))
	{
		$text_loading 	= $values['text_loading'];
	}
	
	$bg_loading 	= '#FFFFFF';
	if (isset($values['bg_loading']))
	{
		$bg_loading 	= $values['bg_loading'];
	}
	$bg_loading = str_replace('#', '', $bg_loading);
	
	echo '<style>.mask-loading{background:#'.$bg_loading.'!important;}</style>';
	
	echo '<script type="text/javascript"> var logo_loading = "'.$logo_loading.'"; var text_loading = "'.$text_loading.'";</script>';
}
?>