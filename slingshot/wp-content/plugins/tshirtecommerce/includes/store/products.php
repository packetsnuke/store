<div class="store-page woocommerce">
	<h4><?php echo $lang['designer_store_product']; ?></h4>
	
	<?php if ( $wp_query->have_posts() ) { ?>
	<div class="store-products">
		<ul class="products">
		
			<?php
			$link = get_page_link();
			while ( $wp_query->have_posts() ) : $wp_query->the_post();
				$product 	= get_product($wp_query->post->ID);
				$link 		= add_query_arg( array('product_id'=>$wp_query->post->ID), $link);
			?>
			<li class="product type-product status-publish">
				<div class="product-box">
					<a href="<?php echo $link; ?>">
						<?php
						echo $product->get_image($size = 'shop_thumbnail');
						?>
						<h3><?php echo get_the_title(); ?></h3>
					</a>
				</div>
			</li>
			<?php endwhile; ?>
		</ul>
		<?php wc_get_template( 'loop/pagination.php' ); ?>
		
		
		<?php wp_reset_query(); ?>
	</div>
	<?php }else{ echo 'Product not found!'; } ?>
	
</div>