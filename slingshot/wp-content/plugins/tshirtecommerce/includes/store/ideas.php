<?php
$page 			= get_page_link();
$link 			= add_query_arg( array('view'=>'products'), $page);

$designer = get_option( 'online_designer' );
if (isset($designer['url']) && $designer['url'] > 0)
{
	$id = $designer['url'];
}
else
{
	$id = 'design-your-own';
}
$product_url = get_page_link($id);

if(isset($_GET['color']))
{
	$color = add_query_arg( array('view'=>$_GET['color']), $page);
}

$paged 		= ( get_query_var('paged') ) ? get_query_var('paged') : 1;
?>
<div class="store-page woocommerce">
	<?php if( isset($ideas) && isset($ideas['count']) && $ideas['count'] > 0 ){ ?>
	<div class="store-ideas">
	
		<?php
		$thumbs = '';
			
		// get image of product
		if(!isset($color_index))
			$color_index = 0;
		$index = $color_index;
		if(isset($front[$index]) && $front[$index] != '')
		{
			$design = json_decode(str_replace("'", '"', $front[$index]));
		}
		if(isset($design))
		{
			foreach($design as $item)
			{
				if($item->id != 'area-design')
				{
					$width 		= str_replace('px', '', $item->width);
					$width		= $width * $zoom;
					
					$height 	= str_replace('px', '', $item->height);
					$height		= $height * $zoom;
					
					$top 		= str_replace('px', '', $item->top);
					$top		= $top * $zoom;
					
					$left 	= str_replace('px', '', $item->left);
					$left		= $left * $zoom;
					
					if($item->zIndex == 'auto')
					{
						$item->zIndex = 0;
					}
					
					if( strpos($item->img, 'http') === false)
					{
						$img = site_url('tshirtecommerce/'. $item->img);
					}
					else
					{
						$img = $item->img;
					}
					$extra_color = '';
					if(isset($item->is_change_color) && $item->is_change_color == 1)
					{
						$extra_color = 'is_change_color';
					}
					$thumbs	.= '<img class="product-img '.$extra_color.'" src="'.$img.'" alt="" style="width:'.$width.'px; height:'.$height.'px; top:'.$top.'px; left:'.$left.'px; z-index:'.$item->zIndex.';">';
				}
			}
		}
		
		// list page
		$number 		= 24;
		$start 		= ($paged - 1) * $number;
		$end 			= $paged*24;
		$i 			= 0;
		foreach($ideas['rows'] as $idea) {
			$i++;
			if($i<$start) continue;
			
			if($start > $end) break;
			$start++;
			
			$product_url 	= add_query_arg( array('product_id'=>$product_id, 'idea_id'=> $idea['id']), $product_url);
		?>
		
		<div class="store-idea <?php echo $gallery_class; ?>">
			
			<div class="store-idea-thumb" style="background-color:#<?php echo $idea['color']; ?>">
				<a href="<?php echo $product_url; ?>" target="_bank" title="<?php echo $idea['title']; ?>">
					<img src="<?php echo $idea['thumb']; ?>" atl="<?php echo $idea['title']; ?>" width="220">
				</a>
				<a href="<?php echo $product_url; ?>" target="_bank" class="store-idea-title"><?php echo $lang['designer_cart_edit']; ?></a>
			</div>
			
			<div class="store-idea-product">
				<a href="<?php echo $product_url; ?>" target="_bank" title="<?php echo $idea['title']; ?>">
					<div class="product-bg" data-color="<?php echo $idea['color']; ?>">
						<?php echo $thumbs; ?>
					</div>
					<img class="item-design" src="<?php echo $idea['thumb']; ?>" atl="<?php echo $idea['title']; ?>" style="max-width:<?php echo $area->width; ?>px;max-height:<?php echo $area->height; ?>px; left:<?php echo $area->left; ?>px;top:<?php echo $area->top; ?>px; z-index:<?php echo $area->zIndex; ?>;">
				</a>
			</div>
			
		</div>
		
		<?php } ?>
		
		<?php
		$pages = (int) (count($ideas['rows'])/24);
		if(count($ideas['rows']) % 24 > 0)
		{
			$pages = $pages + 1;
		}
		
		$args = array(
			'base'               => '%_%',
			'format'             => '?paged=%#%',
			'total'              => $pages,
			'current' 		=> max( 1, get_query_var('paged') ),
			'type' 				 => 'list',
		);
		?>
	</div>
	
	<br />
	<hr />
	
	<nav class="woocommerce-pagination">
		<?php echo paginate_links( $args ); ?>
	</nav>
	
	<?php }else{ echo $lang['design_msg_save_found']; } ?>
</div>