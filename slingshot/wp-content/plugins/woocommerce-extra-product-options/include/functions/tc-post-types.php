<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

/** Register post types **/
function tc_epo_register_post_type() {
	/** This is used in Normal mode **/
	register_post_type( TM_EPO_LOCAL_POST_TYPE,
		array(
			'labels'              => array(
				'name' => _x( 'TM Extra Product Options', 'post type general name', 'woocommerce-tm-extra-product-options' ),
			),
			'publicly_queryable'  => FALSE,
			'exclude_from_search' => TRUE,
			'rewrite'             => FALSE,
			'show_in_nav_menus'   => FALSE,
			'public'              => FALSE,
			'hierarchical'        => FALSE,
			'supports'            => FALSE,
			'_edit_link'          => 'post.php?post=%d' //WordPress 4.4 fix
		)
	);

	/** This is used in Global builder forms mode **/
	register_post_type( TM_EPO_GLOBAL_POST_TYPE,
		array(
			'labels'              => array(
				'name'               => __( 'TM Global Forms', 'woocommerce-tm-extra-product-options' ),
				'singular_name'      => __( 'TM Global Form', 'woocommerce-tm-extra-product-options' ),
				'menu_name'          => _x( 'TM Global Product Options', 'post type general name', 'woocommerce-tm-extra-product-options' ),
				'add_new'            => __( 'Add Global Form', 'woocommerce-tm-extra-product-options' ),
				'add_new_item'       => __( 'Add New Global Form', 'woocommerce-tm-extra-product-options' ),
				'edit'               => __( 'Edit', 'woocommerce-tm-extra-product-options' ),
				'edit_item'          => __( 'Edit Global Form', 'woocommerce-tm-extra-product-options' ),
				'new_item'           => __( 'New Global Form', 'woocommerce-tm-extra-product-options' ),
				'view'               => __( 'View Global Form', 'woocommerce-tm-extra-product-options' ),
				'view_item'          => __( 'View Global Form', 'woocommerce-tm-extra-product-options' ),
				'search_items'       => __( 'Search Global Form', 'woocommerce-tm-extra-product-options' ),
				'not_found'          => __( 'No Global Form found', 'woocommerce-tm-extra-product-options' ),
				'not_found_in_trash' => __( 'No Global Form found in trash', 'woocommerce-tm-extra-product-options' ),
				'parent'             => __( 'Parent Global Form', 'woocommerce-tm-extra-product-options' ),
			),
			'description'         => esc_attr__( 'This is where you can add new global options to your store.', 'woocommerce' ),
			'public'              => FALSE,
			'show_ui'             => FALSE,
			'capability_type'     => 'product',
			'map_meta_cap'        => TRUE,
			'publicly_queryable'  => FALSE,
			'exclude_from_search' => TRUE,
			'hierarchical'        => FALSE,
			'rewrite'             => FALSE,
			'query_var'           => FALSE,
			'supports'            => array( 'title', 'excerpt' ),
			'has_archive'         => FALSE,
			'show_in_nav_menus'   => FALSE,
			'_edit_link'          => 'post.php?post=%d' //WordPress 4.4 fix
		)

	);

	tc_epo_register_taxonomy();
}

/** Register post types **/
function tc_epo_register_taxonomy() {
	register_taxonomy_for_object_type( 'product_cat', TM_EPO_GLOBAL_POST_TYPE );
}
