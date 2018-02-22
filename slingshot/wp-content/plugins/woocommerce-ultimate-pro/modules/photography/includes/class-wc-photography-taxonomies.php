<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WooCommerce Photography Taxonomies.
 *
 * @package  WC_Photography/Taxonomies
 * @category Class
 * @author   WooThemes
 */
class WC_Photography_Taxonomies {

	/**
	 * Initialize the taxonomies.
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register_collections' ), 6 );
	}

	/**
	 * Register collections product taxonomy.
	 *
	 * @return void
	 */
	public static function register_collections() {

		$objects = apply_filters( 'wc_photography_collections_taxonomy_objects', array( 'product' ) );

		$args = apply_filters( 'wc_photography_collections_taxonomy_args', array(
			'hierarchical'          => true,
			'label'                 => __( 'Photography Collections', 'ultimatewoo-pro' ),
			'labels' => array(
				'name'              => __( 'Photography Collections', 'ultimatewoo-pro' ),
				'singular_name'     => __( 'Photography Collection', 'ultimatewoo-pro' ),
				'menu_name'         => _x( 'Collections', 'Admin menu name', 'ultimatewoo-pro' ),
				'all_items'         => __( 'All Photography Collections', 'ultimatewoo-pro' ),
				'edit_item'         => __( 'Edit Photography Collection', 'ultimatewoo-pro' ),
				'view_item'         => __( 'View Photography Collection', 'ultimatewoo-pro' ),
				'update_item'       => __( 'Update Photography Collection', 'ultimatewoo-pro' ),
				'add_new_item'      => __( 'Add New Photography Collection', 'ultimatewoo-pro' ),
				'new_item_name'     => __( 'New Photography Collection name', 'ultimatewoo-pro' ),
				'parent_item'       => __( 'Parent Photography Collection', 'ultimatewoo-pro' ),
				'parent_item_colon' => __( 'Parent Photography Collection:', 'ultimatewoo-pro' ),
				'search_items'      => __( 'Search Photography Collections', 'ultimatewoo-pro' ),
				'not_found'         => __( 'No Photography Collections found', 'ultimatewoo-pro' ),
			),
			'query_var'             => true,
			'show_ui'               => true,
			'show_in_menu'          => false,
			'show_in_nav_menus'     => true,
			'show_tagcloud'         => false,
			'capabilities'          => array(
				'manage_terms' => 'manage_product_terms',
				'edit_terms'   => 'edit_product_terms',
				'delete_terms' => 'delete_product_terms',
				'assign_terms' => 'assign_product_terms',
			),
			'rewrite'               => array(
				'slug'         => _x( 'collection', 'slug', 'ultimatewoo-pro' ),
				'with_front'   => false,
				'hierarchical' => true,
			),
		) );

		register_taxonomy( 'images_collections', $objects, $args );
	}

}

new WC_Photography_Taxonomies();
