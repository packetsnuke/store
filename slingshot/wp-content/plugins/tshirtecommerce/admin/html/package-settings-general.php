<tr>
	<th>Open Designer in</th>
	<td>
		<?php
			if(isset($opt_val['page_open']))
			{
				$page_open = $opt_val['page_open'];
			}
			else
			{
				$page_open = 'page';
			}
			if($page_open != 'page' && $page_open != 'product') $page_open = 'page';
		?>
		<p><label><input type="radio" <?php if($page_open == 'page') echo 'checked="checked"'; ?> name="designer[page_open]" value="page"> <strong>New page</strong></label> <small>(Show button design in page product and when click this button will open page designer)</small></p>
		<p><label><input type="radio" <?php if($page_open == 'product') echo 'checked="checked"'; ?> name="designer[page_open]" value="product"> <strong>Page product detail</strong></label> <small>(Will remove image of product in page product detail and load designer)</small></p>
	</td>
</tr>