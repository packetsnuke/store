<?php
$page 	= get_page_link();
$page_link 	= $page;

if (isset($tshirt_settings['url']) && $tshirt_settings['url'] > 0)
{
	$id = $tshirt_settings['url'];
}
else
{
	$id = 'design-your-own';
}
$product_url = get_page_link($id);

if(isset($_GET['color']) && $_GET['color'] != '')
{
	$page = add_query_arg( array('view'=>$_GET['color']), $page);
}
if(isset($_GET['keyword']) && $_GET['keyword'] != '')
{
	$page = add_query_arg( array('keyword'=>$_GET['keyword']), $page);
}
if(isset($_GET['cate_id']))
{
	$page = add_query_arg( array('cate_id'=>$_GET['cate_id']), $page);
}

$paged 		= ( get_query_var('paged') ) ? get_query_var('paged') : 1;
if($paged > 1)
{
	$page = add_query_arg( array('paged'=>$paged), $page);
}
?>
<div class="store-full">
	<div id="dg-secondary">
		<div class="dg-body-left">
			<form action="" method="GET" id="form-store-search">
				<div class="dg-box">
					<h3 class="box-title"><?php echo $lang['designer_store_find']; ?></h3>
					<span class="dg-finter-close" onclick="jQuery('#dg-secondary').hide('slow')">x</span>
					<div class="box-content">
						<?php
						if(isset($_GET['keyword']))
							$keyword = $_GET['keyword'];
						else
							$keyword	= '';
						?>
						<input type="text" name="keyword" value="<?php echo $keyword; ?>" placeholder="<?php echo $lang['designer_clipart_search']; ?>">
					</div>

					<?php if(isset($ideas['categories']) && count($ideas['categories']) > 0){ ?>
					<h3 class="box-title"><?php echo $lang['designer_store_find_categories']; ?></h3>
					<div class="box-content">
						<?php
						if(isset($_GET['cate_id']))
							$cate_id = $_GET['cate_id'];
						else
							$cate_id	= 0;
						?>
						<select name="cate_id" onchange="jQuery('#form-store-search').submit()">
							<option value="0"><?php echo $lang['designer_store_find_categories_all'];?></option>
							
							<?php foreach($ideas['categories'] as $cate) { ?>
								
								<option value="<?php echo $cate['id']; ?>" <?php if($cate_id == $cate['id']) echo 'selected="selected"'; ?> ><?php echo $cate['title']; ?></option>
												
								<?php if( isset($cate['children']) && count($cate['children']) > 0 ) { ?>
									
									<?php foreach($cate['children'] as $children) { ?>
										<option value="<?php echo $children['id']; ?>" <?php if($cate_id == $children['id']) echo 'selected="selected"'; ?>> &nbsp;&nbsp;&nbsp;- <?php echo $children['title']; ?></option>
									<?php } ?>
								
								<?php } ?>
							
							<?php } ?>
							
						</select>
					</div>
					<?php } ?>
				</div>
			
				<?php if ( count($products) ) { ?>
				<div class="dg-box">
					<h3 class="box-title"><?php echo $lang['design_products']; ?></h3>
					<div class="box-content">
						<ul class="list-product">
							<?php 
							foreach($products as $product) {
								$link = add_query_arg( array('product_id'=>$product->ID), $page);
							?>
							<li <?php if($product->ID == $product_id) echo 'class="active"'; ?>>
								<a href="<?php echo $link; ?>" title=""><?php echo $product->post_title; ?></a>
							</li>
							<?php } ?>
						</ul>
					</div>
				</div>
				<?php } ?>
			
				<input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
			</form>
		</div>
	</div>

	<div class="dg-content">
		<button type="button" class="dg-finter" onclick="jQuery('#dg-secondary').toggle('slow')"><?php echo $lang['designer_store_find']; ?></button>
		<div class="dg-body-right">
			<?php include_once('ideas.php'); ?>
		</div>
	</div>
</div>
<script type="text/javascript">
	var product_gallery = '<?php echo base64_encode(json_encode($gallery)); ?>';
	jQuery(document).ready(function(){
		setTimeout(function(){
			jQuery('.product-bg img').each(function(){
				var color = jQuery(this).parent().data('color');
				if(jQuery(this).hasClass('is_change_color'))
				{
					jQuery(this).css('background-color', '#'+color);
				}
			});
			jQuery('.gallery-none .item-design').load(function(){
				move_image_design(this);
			}).each(function(){
				if(this.complete) jQuery(this).load();
			});
		}, 200);
	});
function move_image_design(e)
{
	var max_h = jQuery(e).css('max-height');
	var max_w = jQuery(e).css('max-width');
	if(typeof max_h != 'undefined')
	{
		max_h = max_h.replace('px', '');
		var img_h = jQuery(e).height();
		var img_top = (max_h - img_h)/2;
		var top = jQuery(e).position().top;
		img_top = parseInt(top) + img_top;

		var left = jQuery(e).position().left;
		max_w = max_w.replace('px', '');
		var img_w = jQuery(e).width();
		var img_left = (max_w - img_w)/2;
		img_left = parseInt(left) + img_left;

		jQuery(e).css({
			'top': img_top+'px',
			'left': img_left+'px',
		});
	}
}
</script>