<tr>
	<th scope="row">Hide button add to cart</th>
	<td>
		<?php
		$product_btn_addcart = 0;
		if(isset($opt_val['product_btn_addcart']) )
		{
			$product_btn_addcart = $opt_val['product_btn_addcart'];
		}
		?>
		<p><label><input type="radio" value="0" <?php if($product_btn_addcart == 0) echo 'checked="checked"'; ?> name="designer[product_btn_addcart]"> No</label></p>
		<p><label><input type="radio" value="1" <?php if($product_btn_addcart == 1) echo 'checked="checked"'; ?> name="designer[product_btn_addcart]"> Only hide with product blank</label></p>
		<p><label><input type="radio" value="2" <?php if($product_btn_addcart == 2) echo 'checked="checked"'; ?> name="designer[product_btn_addcart]"> with all product design</label></p>
	</td>
</tr>
<tr>
	<th scope="row">Hide button design</th>
	<td>
		<?php
		$product_btn_design = 0;
		if(isset($opt_val['product_btn_design']) )
		{
			$product_btn_design = $opt_val['product_btn_design'];
		}
		?>
		<p><label><input type="radio" value="0" <?php if($product_btn_design == 0) echo 'checked="checked"'; ?> name="designer[product_btn_design]"> No</label></p>
		<p><label><input type="radio" value="1" <?php if($product_btn_design == 1) echo 'checked="checked"'; ?> name="designer[product_btn_design]"> Only hide with product template</label></p>
		<p><label><input type="radio" value="2" <?php if($product_btn_design == 2) echo 'checked="checked"'; ?> name="designer[product_btn_design]"> with all product design</label></p>
	</td>
</tr>
<tr>
	<th scope="row">Show list products</th>
	<td>
		<?php
		$show_product = 0;
		if(isset($opt_val['show_product']) )
		{
			$show_product = $opt_val['show_product'];
		}
		?>
		<p><label><input type="radio" value="0" <?php if($show_product == 0) echo 'checked="checked"'; ?> name="designer[show_product]"> No</label></p>
		<p><label><input type="radio" value="1" <?php if($show_product == 1) echo 'checked="checked"'; ?> name="designer[show_product]"> Yes</label></p>
		<p><small>Show list product with same design, this option only works with design template</small></p>
	</td>
</tr>
<tr>
	<th scope="row">Show list design</th>
	<td>
		<?php
		$show_design = 0;
		if(isset($opt_val['show_design']) )
		{
			$show_design = $opt_val['show_design'];
		}
		?>
		<p><label><input type="radio" value="0" <?php if($show_design == 0) echo 'checked="checked"'; ?> name="designer[show_design]"> No</label></p>
		<p><label><input type="radio" value="1" <?php if($show_design == 1) echo 'checked="checked"'; ?> name="designer[show_design]"> Yes</label></p>
		<p><small>Show list design with same product</small></p>
	</td>
</tr>