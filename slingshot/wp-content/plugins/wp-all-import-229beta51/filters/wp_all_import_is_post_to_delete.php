<?php

function pmwi_wp_all_import_is_post_to_delete( $to_delete, $pid, $import )
{	
	if ( $import->options['custom_type'] == 'product')
	{
		$post_to_delete = get_post($pid);

		if ( $to_delete and $post_to_delete->post_type == 'product' and class_exists('WooCommerce'))
		{
			$children = get_posts( array(
				'post_parent' 	=> $pid,
				'posts_per_page'=> -1,
				'post_type' 	=> 'product_variation',
				'fields' 		=> 'ids',
				'orderby'		=> 'ID',
				'order'			=> 'ASC',
				'post_status'	=> array('draft', 'publish', 'trash', 'pending', 'future', 'private')
			) );	

			if ( count($children) ){	
				$to_delete = false;
			}
					
		}
	}	
	return $to_delete;
}