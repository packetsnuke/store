<tr>
	<th scope="row" colspan="2">
		<h3 style="margin: 0;">Product list page</h3>
	</th>
</tr>
<tr>
	<?php
	$checkbox_btn = '';
	if(isset($opt_val['products_btn_design']) && $opt_val['products_btn_design'] == 1)
	{
		$checkbox_btn = 'checked="checked"';
	}
	?>
	<th scope="row">Hide button design</th>
	<td><label><input type="checkbox" value="1" <?php echo $checkbox_btn; ?> name="designer[products_btn_design]"> Hide button start design, customize design on page list product</label></td>
</tr>
<tr>
	<?php
	$checkbox_btn = '';
	if(isset($opt_val['products_btn_addcart']) && $opt_val['products_btn_addcart'] == 1)
	{
		$checkbox_btn = 'checked="checked"';
	}
	?>
	<th scope="row">Hide button add to cart</th>
	<td><label><input type="checkbox" value="1" <?php echo $checkbox_btn; ?> name="designer[products_btn_addcart]"> Only hide with product design, default is showed</label></td>
</tr>
<tr>
	<?php
	$checkbox_btn = '';
	if(isset($opt_val['products_colors']) && $opt_val['products_colors'] == 1)
	{
		$checkbox_btn = 'checked="checked"';
	}
	?>
	<th scope="row">Show colors</th>
	<td><label><input type="checkbox" value="1" <?php echo $checkbox_btn; ?> name="designer[products_colors]"> Show list color of product design, default is hide</label></td>
</tr>