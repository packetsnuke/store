<?php
require_once get_template_directory() . '/framework/libs/tax-meta-class/Tax-meta-class.php';

if ( is_admin() ) {
	/*
	   * prefix of meta keys, optional
	   */
	$prefix = 'thim_';
	/*
	   * configure your meta box
	   */
	$config = array(
		'id'             => 'category_meta_box',
		// meta box id, unique per meta box
		'title'          => 'Category Meta Box',
		// meta box title
		'pages'          => array( 'category' ),
		// taxonomy name, accept categories, post_tag and custom taxonomies
		'context'        => 'normal',
		// where the meta box appear: normal (default), advanced, side; optional
		'fields'         => array(),
		// list of meta fields (can be added by field arrays)
		'local_images'   => false,
		// Use local or hosted images (meta box images for add/remove)
		'use_with_theme' => false
		//change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
	);

	$config_product_cat = array(
		'id'             => 'product_cat_meta_box',
		// meta box id, unique per meta box
		'title'          => 'Category Meta Box',
		// meta box title
		'pages'          => array( 'product_cat' ),
		// taxonomy name, accept categories, post_tag and custom taxonomies
		'context'        => 'normal',
		// where the meta box appear: normal (default), advanced, side; optional
		'fields'         => array(),
		// list of meta fields (can be added by field arrays)
		'local_images'   => false,
		// Use local or hosted images (meta box images for add/remove)
		'use_with_theme' => false
		//change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
	);
	/*
	   * Initiate your meta box
	   */
	$my_meta          = new Tax_Meta_Class( $config );
	$product_cat_meta = new Tax_Meta_Class( $config_product_cat );
 	/*
   * Add fields to your meta box
   */
	/* blog */
	$my_meta = new Tax_Meta_Class( $config );
	$my_meta->addSelect( $prefix . 'layout', array(
		''              => 'Using in Theme Option',
		'full-content'    => 'No Sidebar',
		'sidebar-left'  => 'Left Sidebar',
		'sidebar-right' => 'Right Sidebar'
	),
		array( 'name' => __( 'Custom Layout ', 'thim' ), 'std' => array( '' ) ) );
	$my_meta->addSelect( $prefix . 'style_archive', array(
		''        => 'Using in Theme Option',
		'style-1' => 'Style 1',
		'style-2' => 'Style 2',
		'style-3' => 'Style 3',
		'masonry' => 'Masonry',
		'timeline' => 'Timeline',
	),
		array( 'name' => __( 'Custom Style ', 'thim' ), 'std' => array( '' ) ) );

	$my_meta->addSelect( $prefix . 'style_archive_columns', array(
		''      => 'Using in Theme Option',
		'col-2' => '2 Columns',
		'col-3' => '3 Columns',
		'col-4' => '4 Columns',
	),
		array( 'name' => __( 'Custom Columns ', 'thim' ), 'std' => array( '' ) ) );
	$my_meta->addSelect( $prefix . 'custom_heading', array(
		''       => 'Using in Theme Option',
		'custom' => 'Custom',
	),
		array( 'name' => __( 'Custom Heading ', 'thim' ), 'std' => array( '' ) ) );

	$my_meta->addImage( $prefix . 'archive_top_image', array( 'name' => __( 'Background images Heading', 'aloxo' ) ) );
	$my_meta->addColor( $prefix . 'archive_cate_heading_bg_color', array( 'name' => __( 'Background Color Heading', 'thim' ) ) );
	$my_meta->addColor( $prefix . 'archive_cate_heading_text_color', array( 'name' => __( 'Text Color Heading', 'thim' ) ) );
	$my_meta->addCheckbox( $prefix . 'archive_cate_hide_title', array( 'name' => __( 'Hide Title', 'thim' ) ) );
	$my_meta->addCheckbox( $prefix . 'archive_cate_hide_breadcrumbs', array( 'name' => __( 'Hide Breadcrumbs', 'thim' ) ) );

	$my_meta->Finish();

	// option woocommerce
	$product_cat_meta->addSelect( $prefix . 'layout', array(
		''              => 'Using in Theme Option',
		'full-content'    => 'No Sidebar',
		'sidebar-left'  => 'Left Sidebar',
		'sidebar-right' => 'Right Sidebar'
	), array( 'name' => __( 'Custom Layout ', 'thim' ), 'std' => array( '' ) ) );

	$product_cat_meta->addSelect( $prefix . 'custom_column', array(
		''  => 'Using in Theme Option',
		'2' => '2',
		'3' => '3',
		'4' => '4',
		'5' => '5',
		'6' => '6',
	), array( 'name' => __( 'Custom Column ', 'thim' ), 'std' => array( '' ) ) );

	$product_cat_meta->addSelect( $prefix . 'custom_heading', array(
		''       => 'Using in Theme Option',
		'custom' => 'Custom',
	),
		array( 'name' => __( 'Custom Heading ', 'thim' ), 'std' => array( '' ) ) );

	$product_cat_meta->addImage( $prefix . 'woo_top_image', array( 'name' => __( 'Background images Heading', 'aloxo' ) ) );
	$product_cat_meta->addColor( $prefix . 'woo_cate_heading_bg_color', array( 'name' => __( 'Background Color Heading', 'thim' ) ) );
	$product_cat_meta->addColor( $prefix . 'woo_cate_heading_text_color', array( 'name' => __( 'Text Color Heading', 'thim' ) ) );
	$product_cat_meta->addCheckbox( $prefix . 'woo_cate_hide_title', array( 'name' => __( 'Hide Title', 'thim' ) ) );
	$product_cat_meta->addCheckbox( $prefix . 'woo_cate_hide_breadcrumbs', array( 'name' => __( 'Hide Breadcrumbs', 'thim' ) ) );
	$product_cat_meta->Finish();
}
