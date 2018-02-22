<?php
/**
 *	Register menu/submenu pages
 */

/** Example use:

	$pages = array(
		'my-settings-page' => array(
			'page_title' => __( 'My Settings Page', 'my-plugin' ), // page title
			'menu_title' => __( 'My Settings Page', 'my-plugin' ), // menu title
			'capabilities' => 'manage_options', // capability a user must have to see the page
			'priority' => 99, // priority for menu positioning
			'icon' => 'dashicons-building', // URL to an icon, or name of a Dashicons helper class to use a font icon
			'body_content' => array( $this, 'before_top_page_metaboxes' ), // callback that prints to the page, above the metaboxes
			'parent_slug' => '', // If subpage, slug of the parent page (i.e. woocommerce), or file name of core WordPress page (i.e. edit.php); leave empty for a top-level page
			'sortable' => true, // whether the meta boxes should be sortable
			'collapsable' => true, // whether the meta boxes should be collapsable
			'contains_media' => true, // whether the page utilizes the media uploader
			'tabs' => apply_filters( 'my_admin_page_tabs', array(
				// settings tabs
				'tab1' => __( 'Tab One', 'my-plugin' ),
				'tab2' => __( 'Tab Two', 'my-plugin' ),
				'tab3' => __( 'Tab Three', 'my-plugin' ),
			)),
			'help_section' => array(
				'tabs' => array(
					'some-tab' => array(
						'title' => __( 'Tab Title', 'my-plugin' ),
						'content' => sprintf( '<h2>%s</h2><p>%s</p>', __( 'Cool Help Tabs!', 'my-plugin' ), __( 'Some text', 'my-plugin' ) ),
					),
				),
				'sidebar' => sprintf( '<p><strong>%s</strong></p><p>%s</p>', __( 'Help Sidebar', 'my-plugin' ), __( 'Sidebar content', 'my-plugin' ) ),
			)
		),
		'my-settings-subpage' => array(
			'page_title' => __( 'My Settings Subpage', 'my-plugin' ), // page title
			'menu_title' => __( 'My Settings Subpage', 'my-plugin' ), // menu title
			'capabilities' => 'manage_options', // capability a user must have to see the page
			'priority' => 99, // priority for menu positioning
			'icon' => '', // URL to an icon, or name of a Dashicons helper class to use a font icon
			'body_content' => '', // callback that prints to the page, above the metaboxes
			'parent_slug' => 'my-settings-page', // If subpage, slug of the parent page (i.e. woocommerce), or file name of core WordPress page (i.e. edit.php); leave empty for a top-level page
			'sortable' => true, // whether the meta boxes should be sortable
			'collapsable' => true, // whether the meta boxes should be collapsable
			'media' => true, // whether the page utilizes the media uploader
			'tabs' => apply_filters( 'my_admin_subpage_tabs', array(
				// settings tabs
				'subpage_tab1' => __( 'Subpage Tab One', 'my-plugin' ),
				'subpage_tab2' => __( 'Subpage Tab Two', 'my-plugin' ),
				'subpage_tab3' => __( 'Subpage Tab Three', 'my-plugin' ),
			)),
			'help_section' => array(
				'tabs' => array(
					'some-tab' => array(
						'title' => __( 'Tab Title', 'my-plugin' ),
						'content' => sprintf( '<h2>%s</h2><p>%s</p>', __( 'Cool Help Tabs!', 'my-plugin' ), __( 'Some text', 'my-plugin' ) ),
					),
				),
				'sidebar' => sprintf( '<p><strong>%s</strong></p><p>%s</p>', __( 'Help Sidebar', 'my-plugin' ), __( 'Sidebar content', 'my-plugin' ) ),
			)
		),
		'my-settings-subpage-notabs' => array(
			'page_title' => __( 'My Settings Subpage - No Tabs', 'my-plugin' ), // page title
			'menu_title' => __( 'My Settings Subpage - No Tabs', 'my-plugin' ), // menu title
			'capabilities' => 'manage_options', // capability a user must have to see the page
			'priority' => 99, // priority for menu positioning
			'icon' => '', // URL to an icon, or name of a Dashicons helper class to use a font icon
			'body_content' => '', // callback that prints to the page, above the metaboxes
			'parent_slug' => 'my-settings-page', // If subpage, slug of the parent page (i.e. woocommerce), or file name of core WordPress page (i.e. edit.php); leave empty for a top-level page
			'sortable' => true, // whether the meta boxes should be sortable
			'collapsable' => true, // whether the meta boxes should be collapsable
			'media' => true, // whether the page utilizes the media uploader
		),
	);

	new \UltimateWoo\AdminPages\Admin_Pages( $pages );

 */

namespace UltimateWoo\AdminPages;

require_once 'class-admin-page.php';

if ( ! class_exists( 'Admin_Pages' ) ) :

class Admin_Pages {

	public static $registered_pages;
	
	/**
	 *	@param (array) $pages - Pages to create
	 */
	public function __construct( array $pages ) {

		// Prepare each admin page
		foreach ( $pages as $slug => $setup ) {

			// Required
			$page_title = $setup['page_title'];
			$menu_title = $setup['menu_title'];

			// Optional
			$priority = isset( $setup['priority'] ) ? $setup['priority'] : 99;
			$capabilities = isset( $setup['capabilities'] ) ? $setup['capabilities'] : 'manage_options';
			$icon = isset( $setup['icon'] ) ? $setup['icon'] : '';
			$default_columns = isset( $setup['default_columns'] ) ? $setup['default_columns'] : '';
			$body_content = isset( $setup['body_content'] ) ? $setup['body_content'] : '__return_true';
			$parent_slug = isset( $setup['parent_slug'] ) ? $setup['parent_slug'] : '';
			$sortable = isset( $setup['sortable'] ) ? $setup['sortable'] : true;
			$collapsable = isset( $setup['collapsable'] ) ? $setup['collapsable'] : true;
			$contains_media = isset( $setup['contains_media'] ) ? $setup['contains_media'] : true;
			$tabs = isset( $setup['tabs'] ) ? $setup['tabs'] : array();
			$help_section = ! empty( $setup['help_section'] ) ? $setup['help_section'] : array();

			self::$registered_pages[$slug] = new \UltimateWoo\AdminPage\Admin_Page([
				'slug' => $slug,
				'page_title' => $page_title,
				'menu_title' => $menu_title,
				'capabilities' => $capabilities,
				'priority' => $priority,
				'icon' => $icon,
				'default_columns' => $default_columns,
				'body_content' => $body_content,
				'parent_slug' => $parent_slug,
				'sortable' => $sortable,
				'collapsable' => $collapsable,
				'contains_media' => $contains_media,
				'tabs' => $tabs,
				'help_section' => $help_section
			]);
		}
	}

	/**
	 *	Get all registered pages
	 */
	public static function get_registered_pages() {
		return self::$registered_pages;
	}

	/**
	 *	Get a single registered page
	 */
	public static function get_registered_page( $slug ) {
		return isset( self::$registered_pages[$slug] ) ? self::$registered_pages[$slug] : null;
	}

}

endif;