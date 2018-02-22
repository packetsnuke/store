<?php
$taxonomy = "product_cat";
echo '<div class="product_search">';
echo  '<form method="get" action="'.get_site_url().'">';

$args_cat = array(
	'show_option_all'    => '',
	'orderby'            => 'name',
	'order'              => 'ASC',
	'style'              => 'list',
	'show_count'         => 1,
	'hide_empty'         => 0,
	'use_desc_for_title' => 1,
	'child_of'           => 0,
	'feed'               => '',
	'feed_type'          => '',
	'feed_image'         => '',
	'exclude'            => '',
	'exclude_tree'       => '',
	'include'            => '',
	'hierarchical'       => 1,
	'title_li'           => '',
	'show_option_none'   => __( 'No categories', 'thim' ),
	'number'             => null,
	'echo'               => 0,
	'depth'              => 0,
	'current_category'   => 0,
	'pad_counts'         => 1,
	'taxonomy'           => 'product_cat',
	'walker'             => new wc_dropdown_category_walker
);


echo  '<div class="ps-selector-container">';
echo  '<a class="ps-selector" href="#"><span>'.__( 'All Categories', 'thim' ).'</span> <i class="fa fa-sort-desc"></i></a>';
$terms = get_terms($taxonomy , array( 'hide_empty'=>false));
if ( !empty( $terms ) && !is_wp_error( $terms ) ){
	echo  '<ul class="ps-option">';
	echo  '<li class="all-product"><a href="#">'.__( 'All Categories', 'thim' ).'</a></li>';
	echo wp_list_categories($args_cat);
	echo  '</ul>';
}
echo  '</div>';

echo  '<div class="ps_container">';
echo  '<input class="ps-field" type="text" name="s" placeholder="'.__( 'Search product...', 'thim' ).'" autocomplete="off">';
echo  '<a href="#" onclick="jQuery(this).closest(\'form\').submit();"><i class="fa fa-search fa-lg"></i></a>';
echo  '<input type="hidden" name="post_type" value="product">';
echo  '<input type="hidden" name="product_cat" value="">';
echo  '<ul class="product_results woocommerce"></ul>';
echo  '</div>';
echo  '</form>';
echo  '</div>';