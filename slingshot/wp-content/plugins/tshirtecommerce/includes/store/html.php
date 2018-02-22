<div class="store-page woocommerce">
	<h2>What of product you interesting today?</h2>
	
	<?php if(count($products) > 0) { ?>
	<div class="store-products">
		<ul class="products">
		
			<?php
			$page = get_page_link();
			foreach($products as $post) 
			{
				$product 	= get_product($post->ID);
				$link 		= add_query_arg( array('product_id'=>$post->ID), $page);
			?>
			<li class="product type-product status-publish">
				<div class="product-box">
					<a href="<?php echo $link; ?>">
						<?php
						echo $product->get_image($size = 'shop_thumbnail');
						?>
						<h3><?php echo $product->post->post_title; ?></h3>
					</a>
				</div>
			</li>
			<?php } ?>
		</ul>
	</div>
	<?php } ?>
	
</div>