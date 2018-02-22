<?php
/**
 * @package     WC-Tab-Manager
 * @author      SkyVerge
 * @category    Plugin
 * @copyright   Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */

defined( 'ABSPATH' ) or exit;

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library classss
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once SV_WC_FRAMEWORK_FILE;
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.6.5', __( 'WooCommerce Tab Manager', 'ultimatewoo-pro' ), __FILE__, 'init_woocommerce_tab_manager', array(
	'minimum_wc_version'   => '2.5.5',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4',
) );

function init_woocommerce_tab_manager() {


/**
 * The WooCommerce Tab Manager allows product tabs to be ordered, created,
 * updated, and removed.
 *
 * For the purposes of this plugin the tabs available for management can be
 * broken down into the following categories:
 *
 * - core tabs: The default Description, Additional Information and Reviews tabs
 * - global tabs: These are tabs which can be added to any product
 * - 3rd party tabs: Tabs added by 3rd party plugins, ie WooCommerce Product Enquiry Form
 * - product level tabs: Tabs added to a particular product
 *
 * Note: the terminology surrounding "product tabs" is somewhat confusing in
 * this plugin as I use the term to refer both to any tab which can be added to
 * a product, as well as the "product level tabs".  Hopefully the code context
 * makes it clear enough though.
 *
 * Global and Product Level tabs themselves are represented by a new post_type
 * named wc_product_tab.
 *
 * Tabs can be configured globally from within the Tab Manager submenu, and
 * overridden at the individual product level.  At the global level the tab layout
 * is stored as a wordpress option.  At the product level, the tab layout is
 * stored in the exact same structure, as a post meta.
 *
 * Database:
 * - Option named 'wc_tab_manager_db_version' with the current plugin version
 * - Option named 'wc_tab_manager_default_layout' with the global default layout
 * - Postmeta named '_override_tab_layout' attached to products, indicating
 *   whether the global default layout is to be used
 * - Postmeta named '_product_tabs' attached to products, with the product-level
 *   tab layout (same structure as the global default layout)
 *
 * @since 1.0
 */
class WC_Tab_Manager extends SV_WC_Plugin {


	/** Plugin version */
	const VERSION = '1.8.4';

	/** @var WC_Tab_Manager single instance of this plugin */
	protected static $instance;

	/** The plugins id, used for various slugs and such */
	const PLUGIN_ID = 'tab_manager';

	/** @var array Local cache array of product tabs, keyed off product id. */
	private $product_tabs = array();

	/** @var array Third party tab. */
	private $third_party_tabs;

	/** @var \WC_Tab_Manager_Search instance. */
	protected $search;

	/** @var \WC_Tab_Manager_Settings instance. */
	protected $wc_settings;

	/** @var \WC_Tab_Manager_Ajax_Events instance. */
	protected $ajax;


	/**
	 * Setup main plugin class
	 *
	 * @since 1.0
	 * @see SV_WC_Plugin::__construct()
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'text_domain' => 'woocommerce-tab-manager',
			)
		);

		$this->includes();

		// allow direct linking to tabs
		if ( ! is_admin() ) {
			add_action( 'wp', array( $this, 'use_tab_anchor_links' ) );
		}

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'include_template_functions' ), 25 );

		add_filter( 'woocommerce_product_tabs', array( $this, 'setup_tabs' ), 98 );

		// allow the use of shortcodes within the tab content
		add_filter( 'woocommerce_tab_manager_tab_panel_content', 'do_shortcode' );

		add_action( 'wp_print_footer_scripts',    array( $this, 'include_js_templates' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'include_js_templates' ) );
	}


	/**
	 * Return the tab manager search class instance
	 *
	 * @since 1.6.0
	 * @return \WC_Tab_Manager_Search
	 */
	public function get_search_instance() {
		return $this->search;
	}


	/**
	 * Return the tab manager settings class instance
	 *
	 * @since 1.6.0
	 * @return \WC_Tab_Manager_Settings
	 */
	public function get_settings_instance() {
		return $this->wc_settings;
	}


	/**
	 * Return the tab manager ajax class instance
	 *
	 * @since 1.6.0
	 * @return \WC_Tab_Manager_Ajax_Events
	 */
	public function get_ajax_instance() {
		return $this->ajax;
	}


	/**
	 * Init WooCommerce Tab Manager
	 */
	public function init() {

		// Init user roles
		$this->init_user_roles();

		// Init WooCommerce Product Tab taxonomy
		$this->init_taxonomy();
	}


	/**
	 * Function used to init WooCommerce Tab Manager Template Functions, making them pluggable by plugins and themes
	 */
	public function include_template_functions() {
		require_once( $this->get_plugin_path() . '/woocommerce-tab-manager-template.php' );
	}


	/**
	 * Files required by both the admin and frontend
	 */
	private function includes() {

		if ( is_admin() ) {
			$this->admin_includes();
		}

		if ( is_ajax() ) {
			$this->ajax_includes();
		}

		if ( ! class_exists( 'WC_Tab_Manager_Search' ) ) {
			$this->search = $this->load_class( '/includes/class-wc-tab-manager-search.php', 'WC_Tab_Manager_Search' );
		}
	}


	/**
	 * Include required admin files
	 */
	private function admin_includes() {
		// Admin functions.
		include_once( $this->get_plugin_path() . '/admin/woocommerce-tab-manager-admin-functions.php' );

		// Product Tab-specific admin functions.
		include_once( $this->get_plugin_path() . '/admin/post-types/wc_product_tab.php' );

		// Product-Tab metaboxes
		include_once( $this->get_plugin_path() . '/admin/post-types/wc_product_tab_metabox.php' );

		// Default Tab Layout admin screen and persistance code.
		include_once( $this->get_plugin_path() . '/admin/woocommerce-tab-manager-admin-global-layout.php' );
		require_once( $this->get_plugin_path() . '/admin/woocommerce-tab-manager-admin-init.php' );

		// Custom WooCommerce settings.
		if ( ! class_exists( 'WC_Tab_Manager_Settings' ) ) {
			$this->wc_settings = $this->load_class( '/includes/class-wc-tab-manager-settings.php', 'WC_Tab_Manager_Settings' );
		}
	}


	/**
	 * Include required ajax files.
	 */
	private function ajax_includes() {

		if ( ! class_exists( 'WC_Tab_Manager_Ajax_Events' ) ) {
			$this->wc_settings = $this->load_class( '/includes/class-wc-tab-manager-ajax-events.php', 'WC_Tab_Manager_Ajax_Events' );
		}
	}


	/**
	 * Include frontend scripts.
	 *
	 * @since 1.8.1
	 */
	public function use_tab_anchor_links() {
		global $post;

		if ( is_product() ) {

			$tabs = json_encode( $this->get_product_tabs( $post->ID ) );

			// adding an entire frontend js file feels sort of heavy for this, perhaps consider it if we add more js in the future {BR 2017-05-30}
			wc_enqueue_js( "
			jQuery(document).ready(function($) {
				var hash = window.location.hash;
				var tabs = $( this ).find( '.wc-tabs, ul.tabs' ).first();
				var ref  = $tabs;
				
				for ( index in ref ) {
					tab = ref[index];
					
					/* global tabs */
					if ( tab.name && ( hash === '#' + tab.name || hash === '#tab-' + tab.name ) ) {
						tabs.find( 'li.' + tab.name + '_tab a' ).trigger( 'click' );
					
					/* third-party tabs */
					} else if ( hash === '#' + tab.id || hash === '#tab-' + tab.id ) {
						tabs.find( 'li.' + tab.id + '_tab a' ).trigger( 'click' );
					}
				}
			});" );
		}
	}


	/**
	 * Init WooCommerce Tab Manager user role.
	 */
	private function init_user_roles() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) && class_exists( 'WP_Roles' ) ) {
			$wp_roles = new WP_Roles();
		}

		// it's fine if this gets executed more than once
		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'shop_manager',  'manage_woocommerce_tab_manager' );
			$wp_roles->add_cap( 'administrator', 'manage_woocommerce_tab_manager' );
		}
	}


	/**
	 * Init custom post type
	 */
	private function init_taxonomy() {

		// bail if the post type was already registered
		if ( post_type_exists( 'wc_product_tab' ) ) {
			return;
		}

		if ( current_user_can( 'manage_woocommerce' ) ) {
			$show_in_menu = 'woocommerce';
		} else {
			$show_in_menu = true;
		}

		register_post_type( "wc_product_tab",
			array(
				'labels' => array(
					'name'               => __( 'Tabs', 'ultimatewoo-pro' ),
					'singular_name'      => __( 'Tab', 'ultimatewoo-pro' ),
					'menu_name'          => _x( 'Tab Manager', 'Admin menu name', 'ultimatewoo-pro' ),
					'add_new'            => __( 'Add Tab', 'ultimatewoo-pro' ),
					'add_new_item'       => __( 'Add New Tab', 'ultimatewoo-pro' ),
					'edit'               => __( 'Edit', 'ultimatewoo-pro' ),
					'edit_item'          => __( 'Edit Tab', 'ultimatewoo-pro' ),
					'new_item'           => __( 'New Tab', 'ultimatewoo-pro' ),
					'view'               => __( 'View Tabs', 'ultimatewoo-pro' ),
					'view_item'          => __( 'View Tab', 'ultimatewoo-pro' ),
					'search_items'       => __( 'Search Tabs', 'ultimatewoo-pro' ),
					'not_found'          => __( 'No Tabs found', 'ultimatewoo-pro' ),
					'not_found_in_trash' => __( 'No Tabs found in trash', 'ultimatewoo-pro' ),
				),
				'description'     => __( 'This is where you can add new tabs that you can add to products.', 'ultimatewoo-pro' ),
				'public'          => true,
				'show_ui'         => true,
				'capability_type' => 'post',
				'capabilities' => array(
					'publish_posts'       => 'manage_woocommerce_tab_manager',
					'edit_posts'          => 'manage_woocommerce_tab_manager',
					'edit_others_posts'   => 'manage_woocommerce_tab_manager',
					'delete_posts'        => 'manage_woocommerce_tab_manager',
					'delete_others_posts' => 'manage_woocommerce_tab_manager',
					'read_private_posts'  => 'manage_woocommerce_tab_manager',
					'edit_post'           => 'manage_woocommerce_tab_manager',
					'delete_post'         => 'manage_woocommerce_tab_manager',
					'read_post'           => 'manage_woocommerce_tab_manager',
				),
				'publicly_queryable'  => true,
				'exclude_from_search' => true,
				'show_in_menu'        => $show_in_menu,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title', 'editor' ),
				'show_in_nav_menus'   => false,
			)
		);
	}


	/**
	 * Return the plugin action links.  This will only be called if the plugin
	 * is active.
	 *
	 * @since 1.0.9
	 * @param array $actions associative array of action names to anchor tags
	 * @return array associative array of plugin action links
	 */
	public function add_plugin_action_links( $actions ) {

		$custom_actions = array(
			'configure' => sprintf( '<a href="%1$s">%2$s</a>', admin_url( 'edit.php?post_type=wc_product_tab' ), __( 'Configure', 'ultimatewoo-pro' ) ),
			'docs'      => sprintf( '<a href="%1$s">%2$s</a>', 'http://docs.woocommerce.com/document/tab-manager/', __( 'Docs', 'ultimatewoo-pro' ) ),
			'support'   => sprintf( '<a href="%1$s">%2$s</a>', 'http://support.woocommerce.com/', __( 'Support', 'ultimatewoo-pro' ) ),
			'review'    => sprintf( '<a href="%1$s">%2$s</a>', $this->get_review_url(), __( 'Write a Review', 'ultimatewoo-pro' ) ),
		);

		// add the links to the front of the actions list
		return array_merge( $custom_actions, $actions );
	}


	/** Frontend methods ******************************************************/


	/**
	 * Organizes the product tabs as configured within the Tab Manager
	 *
	 * $tabs structure:
	 * Array(
	 *   id => Array(
	 *     'title'    => (string) Tab title,
	 *     'priority' => (string) Tab priority,
	 *     'callback' => (mixed) callback function,
	 *   )
	 * )
	 *
	 * @since 1.0.5
	 * @param array $tabs array representing the product tabs
	 * @return array representing the product tabs
	 */
	public function setup_tabs( $tabs ) {
		global $product;

		// first off, make sure that we're dealing with an array rather than null
		if ( null === $tabs ) {
			$tabs = array();
		}

		$new_tabs     = $tabs;
		$product_tabs = $product instanceof WC_Product ? $this->get_product_tabs( $product->get_id() ) : null;

		// if product tabs have been configured for this product or globally (otherwise, allow default behavior)
		if ( is_array( $product_tabs ) ) {

			// start fresh
			$new_tabs = array();

			// unhook and load any third party tabs that have been added by this point
			$third_party_tabs = $this->get_third_party_tabs( $tabs );

			foreach ( $product_tabs as $key => $tab ) {

				$priority = ( $tab['position'] + 1 ) * 10;

				/**
				 * Filter the tab id
				 *
				 * @since 1.5.2
				 * @param int|string $tab_id Note that this could be a string or integer
				 */
				$tab_id = apply_filters( 'wc_tab_manager_tab_id', $tab['id'] );

				if ( 'core' === $tab['type'] ) {

					// the core tabs can be suppressed for a variety of reasons: description tab due to no content, attributes tab due to no attributes, etc
					if ( ! isset( $tabs[ $tab_id ] ) ) {
						continue;
					}

					// set the review (comment) count for the reviews tab, if they used the '%d' substitution
					if ( 'reviews' === $tab_id && false !== strpos( $tab['title'], '%d' ) )  {

						/**
						 * Filters the review count in the Reviews (%d) tab title
						 *
						 * @since 1.5.2
						 * @param int $review_count array representing the product tabs
						 * @param \WC_Product The product
						 */
						$tab['title'] = str_replace( '%d', apply_filters( 'wc_tab_manager_reviews_tab_title_review_count', $product->get_review_count(), $product ), $tab['title'] );
					}

					// add the core tab to the new tab set
					$new_tabs[ $tab_id ] = array(
						'title'    => $tab['title'],                 // modified title
						'priority' => $priority,                     // modified priority
						'callback' => $tabs[ $tab_id ]['callback'],  // get the core tab callback
					);

					// handle core tab headings (displays just before the tab content)
					if ( 'additional_information' === $tab_id ) {
						add_filter( 'woocommerce_product_additional_information_heading', array( $this, 'core_tab_heading' ) );
					} elseif ( 'description' === $tab_id ) {
						add_filter( 'woocommerce_product_description_heading', array( $this, 'core_tab_heading' ) );
					}

				} elseif ( 'third_party' === $tab['type'] ) {

					// third-party provided tab: ensure it's still available
					if ( ! isset( $third_party_tabs[ $key ] ) || ( isset( $third_party_tabs[ $key ]['ignore'] ) && true == $third_party_tabs[ $key ]['ignore'] ) ) {
						continue;
					}

					// add the 3rd party tab in with the new priority
					$new_tabs[ $tab_id ] = $third_party_tabs[ $key ];
					$new_tabs[ $tab_id ]['priority'] = $priority;

				// Product/Global tabs:
				} else {

					$post_id  = (int) $tab_id;
					$tab_post = get_post( $post_id );

					// skip any global/product tabs that have been deleted
					if ( ! $tab_post || 'publish' !== $tab_post->post_status || ! $tab_post->post_title ) {
						continue;
					}

					// global tabs
					if ( ! $tab_post->post_parent ) {

						$product_terms = get_the_terms( $product->get_id(), 'product_cat' );

						if ( is_wp_error( $product_terms ) ) {
							continue;
						}

						$tab_cats     = get_post_meta( $post_id, '_wc_tab_categories', true );
						$product_cats = wp_list_pluck( (array) $product_terms, 'term_id' );

						// compare selected categories for the tab vs the product's categories
						$cat_check = empty( $tab_cats ) ? array() : array_intersect( $product_cats, $tab_cats );

						// Hacky fix for WooCommerce Multilingual conflict -- revisit this in the rewrite {TZ 2016-12-06}
						// We try to generate a clean unique tab name using the tab title in wc_tab_manager_process_tabs() and handle
						// unicode tab titles there as well (tab switching doesn't work with unicode tab titles). However, sites using
						// WooCommerce Multilingual don't benefit from this unicode handling as that function is not run for translated
						// tabs. Therefore, we must take into account unicode titles later in the process (right before display)
						if ( strlen( $tab_post->post_title ) !== strlen( utf8_encode( $tab_post->post_title ) ) ) {
							$tab['name'] = 'global-tab-' . uniqid();
						}

						// add the global tab if there are no categories, or if selected ones match this product
						if ( ! empty( $cat_check ) || empty( $tab_cats ) ) {
							$new_tabs[ $tab['name'] ] = array(
								'title'    => $tab_post->post_title,
								'priority' => $priority,
								'callback' => 'woocommerce_tab_manager_tab_content',
								'id'       => $tab_id,
							);
						}

					} else {

						// Hacky fix for WooCommerce Multilingual conflict -- revisit this in the rewrite {TZ 2016-12-06}
						// We try to generate a clean unique tab name using the tab title in wc_tab_manager_process_tabs() and handle
						// unicode tab titles there as well (tab switching doesn't work with unicode tab titles). However, sites using
						// WooCommerce Multilingual don't benefit from this unicode handling as that function is not run for translated
						// tabs. Therefore, we must take into account unicode titles later in the process (right before display)
						if ( strlen( $tab_post->post_title ) !== strlen( utf8_encode( $tab_post->post_title ) ) ) {
							$tab['name'] = 'product-tab-' . uniqid();
						}

						// add any product tabs
						$new_tabs[ $tab['name'] ] = array(
							'title'    => $tab_post->post_title,
							'priority' => $priority,
							'callback' => 'woocommerce_tab_manager_tab_content',
							'id'       => $tab_id,
						);
					}
				}
			}

			// finally add in any non-managed 3rd party tabs with their own priority
			foreach ( $third_party_tabs as $key => $tab ) {
				if ( isset( $tab['ignore'] ) && true === $tab['ignore'] ) {
					$new_tabs[ $key ] = $tab;
				}
			}
		}

		return apply_filters( 'wc_tab_manager_product_tabs', $new_tabs );
	}


	/**
	 * Filter to modify the Description and Additional Information core tab headings.
	 * The heading is not what shows up in the "tab" itself, this is the
	 * heading for the tab content area.
	 *
	 * @param string $heading the tab heading
	 * @return string the tab heading
	 */
	public function core_tab_heading( $heading ) {
		global $product;

		$tabs           = $this->get_product_tabs( $product->get_id() );
		$current_filter = current_filter();

		if ( 'woocommerce_product_additional_information_heading' === $current_filter ) {
			$heading = $tabs['core_tab_additional_information']['heading'];
		} elseif ( 'woocommerce_product_description_heading' === $current_filter ) {
			$heading = $tabs['core_tab_description']['heading'];
		}

		return $heading;
	}


	/** Helper methods ******************************************************/


	/**
	 * Main Tab Manager Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.2.0
	 * @see wc_tab_manager()
	 * @return WC_Tab_Manager
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Get any third party tabs which have been added via the
	 * woocommerce_product_tabs action.  Any third party tabs so found are collected
	 * so they can be re-added by the manager in the appropriate order.  In the
	 * admin a human readable title is automatically generated (allowing for
	 * automatic integration of 3rd party plugin tabs) and three filters are
	 * fired to allow for improved integration with customized titles/descriptions:
	 *
	 * - woocommerce_tab_manager_integration_tab_allowed: allows plugin to mark tab as not available for management, its priority will not be modified and it will not appear within the Tab Manager Admin UI
	 * - woocommerce_tab_manager_integration_tab_title: allows plugin to provide a more descriptive tab title to display within the Tab Manager Admin UI
	 * - woocommerce_tab_manager_integration_tab_description: allows plugin to provide a description to display within the Tab Manager Admin UI
	 *
	 * $tabs structure:
	 * Array(
	 *   key => Array(
	 *     'title'       => (string) Tab title,
	 *     'priority'    => (string) Tab priority,
	 *     'callback'    => (mixed) callback function,
	 *     'description' => (string) An optional tab description added by this method to the return array,
	 *     'ignore'      => (boolean) Optional marker indicating this tab is not managed by the Tab Manager plugin and added by this method to the return array,
	 *     'id'          => (string) the original tab key,
	 *   )
	 * )
	 * Where key is: third_party_tab_{id}
	 *
	 * @since 1.0.5
	 * @param array $tabs optional array representing the product tabs
	 * @return array representing the product tabs
	 */
	public function get_third_party_tabs( $tabs = null ) {
		global $wp_filter;

		if ( null === $this->third_party_tabs ) {

			// gather the tabs if not provided
			if ( null === $tabs ) {

				// In WC 2.1+ the woocommerce_default_product_tabs filter (which
				//  requires a global $post/$product) is hooked into from the admin
				//  so unhook to avoid a fatal error (has no effect in pre WC 2.1)
				if ( is_admin() ) {
					remove_filter( 'woocommerce_product_tabs', 'woocommerce_default_product_tabs' );
					remove_filter( 'woocommerce_product_tabs', 'woocommerce_sort_product_tabs', 99 );
				}

				$tabs = (array) apply_filters( 'woocommerce_product_tabs', array() );
			}

			$this->third_party_tabs = array();

			// remove the core tabs (if any) leaving only 3rd party tabs (if any)
			unset( $tabs['additional_information'], $tabs['reviews'], $tabs['description'] );

			foreach ( $tabs as $key => $tab ) {

				// is this tab available for management by the Tab Manager plugin?
				if ( apply_filters( 'woocommerce_tab_manager_integration_tab_allowed', true, $tab ) ) {

					if ( is_admin() ) {
						if ( ! isset( $tab['title'] ) || ! $tab['title'] ) {
							// on the off chance that the 3rd party tab doesn't have a title, provide it a default one based on the callback so it can be identified within the admin

							// get a title for the tab.  Default to humanizing the function name, or class name
							if ( is_array( $tab['callback'] ) ) {
								$tab_title = ( is_object( $tab['callback'][0] ) ? get_class( $tab['callback'][0] ) : $tab['callback'][0] );
							} else {
								$tab_title = (string) $tab['callback'];
							}

							$tab_title = ucwords( str_replace( '_', ' ', $tab_title ) );
							$tab_title = str_ireplace( array( 'woocommerce', 'wordpress' ), array( 'WooCommerce', 'WordPress' ), $tab_title );  // fix some common words

							$tab['title'] = $tab_title;
						}

						// improved 3rd party integration by allowing plugins to provide a more descriptive title/description for their tabs
						$tab['title']       = apply_filters( 'woocommerce_tab_manager_integration_tab_title',       $tab['title'], $tab );
						$tab['description'] = apply_filters( 'woocommerce_tab_manager_integration_tab_description', '',            $tab );
					}

					$tab['id'] = $key;

				} else {

					// this tab is not managed by the Tab Manager, so mark it as such
					$tab['ignore'] = true;
				}

				// save the tab
				$this->third_party_tabs[ 'third_party_tab_' . $key ] = $tab;
			}
		}

		return $this->third_party_tabs;
	}


	/**
	 * Get the default WooCommerce core tabs datastructure
	 *
	 * @return array the core tabs
	 */
	public function get_core_tabs() {
		return array(
			'core_tab_description'            => array(
				'id'          => 'description',
				'position'    => 0,
				'type'        => 'core',
				'title'       => __( 'Description', 'ultimatewoo-pro' ),
				'description' => __( 'Displays the product content set in the main content editor.', 'ultimatewoo-pro' ),
				'heading'     => __( 'Product Description', 'ultimatewoo-pro' ),
			),
			'core_tab_additional_information' => array(
				'id'          => 'additional_information',
				'position'    => 1,
				'type'        => 'core',
				'title'       => __( 'Additional Information', 'ultimatewoo-pro' ),
				'description' => __( 'Displays the product attributes and properties configured in the Product Data panel.', 'ultimatewoo-pro' ),
				'heading'     => __( 'Additional Information', 'ultimatewoo-pro' ),
			),
			'core_tab_reviews'                => array(
				'id'          => 'reviews',
				'position'    => 2,
				'type'        => 'core',
				'title'       => __( 'Reviews (%d)', 'ultimatewoo-pro' ),
				'description' => __( 'Displays the product review form and any reviews. Use %d in the Title to substitute the number of reviews for the product.', 'ultimatewoo-pro' ),
			),
		);
	}


	/**
	 * Gets the product tabs (if any) for the identified product.  If not
	 * configured at the product level, the default layout (if any) will be
	 * returned.
	 *
	 * returned tabs structure:
	 * Array(
	 *   key => Array(
	 *     'position' => (int) 0-indexed ordered position from the Tab Manager Admin UI,
	 *     'type'     => (string) one of 'core', 'global', 'third_party' or 'product',
	 *     'id'       => (string) Tab identifier, ie 'description', 'reviews', 'additional_information' for the core tabs, post id for product/global, and woocommerce_product_tabs key for third party tabs,
	 *     'title'    => (string) The tab title to display on the frontend (not used for 3rd party tabs, though it could be),
	 *     'heading'  => (string) Tab heading (core description/additional_information tabs only),
	 *     'name'     => (string) Product/Global tabs only, this is the sanitized title, and is used to key the tab in the final woocommerce tab data structure,
	 *   )
	 * )
	 * Where key is: {type}_tab_{id}
	 *
	 * @param int $product_id product identifier
	 * @return array product tabs data
	 */
	public function get_product_tabs( $product_id ) {

		if ( ! isset( $this->product_tabs[ $product_id ] ) ) {

			$override_tab_layout = get_post_meta( $product_id, '_override_tab_layout', true );

			if ( 'yes' === $override_tab_layout ) {
				// product defines its own tab layout?
				$this->product_tabs[ $product_id ] = get_post_meta( $product_id, '_product_tabs', true );
			} else {
				// otherwise, get the default layout if any
				$this->product_tabs[ $product_id ] = get_option( 'wc_tab_manager_default_layout', false );
			}
		}

		return $this->product_tabs[ $product_id ];
	}


	/**
	 * Gets the product tab or null if the tab cannot be found
	 *
	 * @param int $product_id product identifier
	 * @param int $tab_id tab identifier
	 * @param boolean $get_the_content whether to get the tab content and title
	 * @return array tab array, or null
	 */
	public function get_product_tab( $product_id, $tab_id, $get_the_content = false ) {

		$tab = null;

		// load the tabs
		$this->get_product_tabs( $product_id );

		if ( is_array( $this->product_tabs[ $product_id ] ) ) {

			foreach ( $this->product_tabs[ $product_id ] as $id => $tab ) {

				if ( $tab['id'] == $tab_id ) {

					// get the tab content, if needed
					if ( $get_the_content && ! isset( $tab['content'] ) ) {

						/** this filter is documented in /woocommerce-tab-manager.php */
						$tab_id   = apply_filters( 'wc_tab_manager_tab_id', $tab_id );
						$tab_post = get_post( $tab_id );

						$content = apply_filters( 'the_content', $tab_post->post_content );
						$content = str_replace( ']]>', ']]&gt;', $content );

						$this->product_tabs[ $product_id ][ $id ]['content'] = $content;
						$this->product_tabs[ $product_id ][ $id ]['title']   = $tab_post->post_title;
					}

					$tab = $this->product_tabs[ $product_id ][ $id ];
					break;
				}
			}
		}

		return apply_filters( 'wc_tab_manager_get_product_tab', $tab, $product_id, $tab_id, $get_the_content );
	}


	/**
	 * Processes a batch of products.
	 *
	 * @since 1.4.0
	 * @param array $args Optional. An array of arguments. Default empty array.
	 * @return array
	 */
	public function batch_update_products( array $args = array() ) {

		$args = wp_parse_args( $args, array(
			'step'  => 0,
			'limit' => 100,
		) );

		$step   = absint( $args['step'] );
		$limit  = absint( $args['limit'] );
		$offset = $step * $limit;

		$product_posts = get_posts( array(
			'post_type'      => 'product',
			'posts_per_page' => $limit,
			'offset'         => $offset,
		) );

		$products_count = $offset + count( $product_posts );
		$products_total = absint( wp_count_posts( 'product' )->publish );

		$response = array(
			'complete' => false,
			'step'     => ++$step,
			'offset'   => $offset,
			'limit'    => $limit,
			'current'  => $products_count,
			'total'    => $products_total,
		);

		if ( 1 === $step ) {
			$this->get_search_instance()->update_relevanssi_searchable_tab_meta();
			$this->get_search_instance()->maybe_build_relevanssi_index();
		}

		// Loop through the current set of products.
		foreach ( $product_posts as $product_post ) {

			// Get any tabs associated with the current product.
			$tabs = $this->get_product_tabs( $product_post->ID );

			if ( ! empty( $tabs ) ) {

				// If any tabs were found, update the tab content meta for the
				// current product.
				$tab_id_list = wp_list_pluck( array_values( $tabs ), 'id' );
				$tab_id_list = array_filter( $tab_id_list, 'is_numeric' );
				$args        = array(
					'target'     => 'custom',
					'action'     => 'update',
					'product_id' => $product_post->ID,
				);

				$this->get_search_instance()->update_products_for_tabs( $tab_id_list, $args );

				// Also update the Relevanssi index for the current product if
				// the plugin is installed.
				$this->get_search_instance()->update_relevanssi_index_for_product( $product_post->ID );
			}
		}

		// We've processed all of the products.
		if ( $products_count >= $products_total ) {
			$response['complete'] = true;
		}

		return $response;
	}


	/**
	 * @since 1.4.0
	 */
	public function include_js_templates() {

		if ( is_admin() ) {
			include_once( $this->get_plugin_path() . '/templates/js/admin.tmpl.php' );
		}
	}


	/**
	 * Tries to determine the current post type based on available global
	 * values. Useful for when you need to determine if a specific post type is
	 * being edited on an admin page or when you need to know the post type in
	 * an action callback but the action is fired before `get_post_type()` has
	 * been defined.
	 *
	 * @since 1.4.0
	 * @global \WP_Post $post The current post object.
	 * @global string $typenow The current post type.
	 * @global \WP_Screen $current_screen The current admin screen object.
	 * @return string|bool The current post type if one was found or false if not.
	 */
	public function get_current_post_type() {
		global $post, $typenow, $current_screen;

		if ( isset( $post->post_type ) ) {
			return $post->post_type;
		}
		if ( $typenow ) {
			return $typenow;
		}
		if ( $current_screen && $current_screen->post_type ) {
			return $current_screen->post_type;
		}
		if ( isset( $_REQUEST['post_type'] ) ) {
			return sanitize_key( $_REQUEST['post_type'] );
		}

		return false;
	}


	/**
	 * Tries to determine the current product ID based on context. If a product
	 * is being editing, that product's ID is used. If a product-level tab is
	 * being edited, the parent product's ID is used.
	 *
	 * @since 1.4.0
	 * @global WP_Post $post The current post object.
	 * @return int|bool The current product ID if one was found or false if not.
	 */
	public function maybe_get_tab_product_id() {
		global $post;

		$post_type = $this->get_current_post_type();

		if ( 'product' === $post_type ) {
			return $post->ID;
		}

		if ( 'wc_product_tab' === $post_type ) {
			if ( isset( $post->post_parent ) ) {
				return $post->post_parent;
			}
		}

		return false;
	}


	/**
	 * Extracts numeric IDs from an array.
	 *
	 * @since 1.4.0
	 * @param  array $id_list An array where each value is either a numeric ID or an associative array where at least one of the following keys has a numeric value: 'tab_ID', 'tab_id', 'post_ID', 'post_id', 'ID', or 'id'
	 * @return array An array containing the extracted integer IDs.
	 */
	public function get_numeric_ids( array $id_list ) {

		$valid_ids = array();

		foreach ( array_values( $id_list ) as $value ) {

			// If the current value is an array, check commonly-used key
			// names to see if we can find a numeric ID somewhere.
			if ( is_array( $value ) ) {

				$id_keys = array( 'tab_ID', 'tab_id', 'post_ID', 'post_id', 'ID', 'id' );

				foreach ( $id_keys as $key ) {
					$id = isset( $value[ $key ] ) ? $value[ $key ] : null;

					if ( ! $id || ! is_numeric( $id ) ) {
						continue;
					}

					// Stop once we find a valid ID.
					$valid_ids[] = absint( $id );
					break;
				}

				continue;
			}

			// Bail if the current value isn't numeric.
			if ( ! is_numeric( $value ) ) {
				continue;
			}

			$valid_ids[] = absint( $value );
		}

		// Remove any duplicate or falsey values.
		$valid_ids = array_filter( array_unique( $valid_ids ) );

		return $valid_ids;
	}


	/**
	 * Verifies that the post ID / object / array passed corresponds to an existing post and, if so, returns the WP_Post instance.
	 *
	 * @since 1.4.0
	 * @param  mixed $post A WP_Post instance, numeric post ID, object, or array. If an object or array is passed it should match the structure of a WP_Post and contain all of the same data (or at least the data that you need returned).
	 * @return WP_Post|bool WP_Post if the post exists or false if not.
	 */
	public function ensure_post( $post = null ) {

		// If no post data was passed return the current global instance.
		if ( empty( $post ) && $GLOBALS['post'] ) {
			return $GLOBALS['post'];
		}

		// If a valid WP_Post instance was passed just return it.
		$is_wp_post  = ( $post instanceof WP_Post );

		if ( $is_wp_post && isset( $post->ID ) ) {
			return $post;
		}

		// Bail if the post data isn't an ID, object, or array.
		$is_post_id     = is_numeric( $post );
		$is_post_object = ( is_object( $post ) && isset( $post->ID ) );
		$is_post_array  = ( is_array( $post ) && isset( $post['ID'] ) );

		if ( ! $is_post_id && ! $is_post_object && ! $is_post_array ) {
			return false;
		}

		// If post data is an array, convert it to an object.
		if ( $is_post_array ) {
			$post = $this->array_to_object( $post );
		}

		// Try to get the post instance.
		$post = get_post( $post );

		if ( ! empty( $post ) ) {
			return $post;
		}

		return false;
	}


	/**
	 * Converts an array into an object. Works with associative and
	 * multi-dimensional arrays.
	 *
	 * @since 1.4.0
	 * @param  array $array The array to convert.
	 * @return stdClass The resulting object.
	 */
	public function array_to_object( array $array ) {

		foreach ( $array as $key => $value ) {

			if ( is_array( $value ) ) {

				$array[ $key ] = $this->array_to_object( $value );
			}
		}

		return (object) $array;
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce Tab Manager', 'ultimatewoo-pro' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


	/**
	 * Gets the plugin configuration URL
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::get_settings_url()()
	 * @see SV_WC_Plugin::get_settings_link()
	 * @param string $plugin_id optional plugin identifier.  Note that this can be a
	 *        sub-identifier for plugins with multiple parallel settings pages
	 *        (ie a gateway that supports both credit cards and echecks)
	 * @return string plugin settings URL
	 */
	public function get_settings_url( $plugin_id = null ) {
		return admin_url( 'edit.php?post_type=wc_product_tab' );
	}


	/**
	 * Gets the plugin documentation URL
	 *
	 * @since  1.3.0
	 * @see    SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {
		return 'https://docs.woocommerce.com/document/tab-manager/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since  1.3.0
	 * @see    SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'https://woocommerce.com/my-account/marketplace-ticket-form/';
	}


	/**
	 * Returns true if on the admin tab configuration page
	 *
	 * @since 1.0.1
	 * @see   SV_WC_Plugin::is_plugin_settings()
	 * @return boolean true if on the admin plugin settings page
	 */
	public function is_plugin_settings() {
		return isset( $_GET['post_type'] ) && 'wc_product_tab' === $_GET['post_type'];
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Run every time.  Used since the activation hook is not executed when updating a plugin
	 *
	 * @see SV_WC_Plugin::install()
	 */
	protected function install() {
		global $wpdb;

		// check for a pre 1.1 version
		$legacy_version = get_option( 'wc_tab_manager_db_version' );

		if ( false !== $legacy_version ) {

			// upgrade path from previous version, trash old version option
			delete_option( 'wc_tab_manager_db_version' );

			// upgrade path
			$this->upgrade( $legacy_version );

			// and we're done
			return;
		}

		// any Custom Product Lite Tabs?
		$results = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key='frs_woo_product_tabs'" );

		// prepare the core tabs
		$core_tabs = $this->get_core_tabs();
		foreach ( $core_tabs as $id => $tab ) {
			unset( $core_tabs[ $id ]['description'] );
		}

		// foreach product with a custom lite tab
		foreach ( $results as $result ) {

			$old_tabs = maybe_unserialize( $result->meta_value );

			$new_tabs = array( 'core_tab_description' => $core_tabs['core_tab_description'], 'core_tab_additional_information' => $core_tabs['core_tab_additional_information'] );

			// keep track of tab names to avoid clashes
			$found_names = array( 'description' => 1, 'additional_information' => 1, 'reviews' => 1 );

			foreach ( $old_tabs as $tab ) {

				if ( $tab['title'] && $tab['content'] ) {
					// create the product tab

					$new_tab = array( 'position' => count( $new_tabs ), 'type' => 'product' );

					$new_tab_data = array(
						'post_title'    => $tab['title'],
						'post_content'  => $tab['content'],
						'post_status'   => 'publish',
						'ping_status'   => 'closed',
						'post_author'   => get_current_user_id(),
						'post_type'     => 'wc_product_tab',
						'post_parent'   => $result->post_id,
						'post_password' => uniqid( 'tab_', false ), // Protects the post just in case
					);

					// create the post and get the id
					$id = wp_insert_post( $new_tab_data );
					$new_tab['id'] = $id;

					// determine the unique tab name
					$tab_name = sanitize_title( $tab['title'] );
					if ( ! isset( $found_names[ $tab_name ] ) ) {
						$found_names[ $tab_name ] = 1;
					} else {
						$found_names[ $tab_name ]++;
					}
					if ( $found_names[ $tab_name ] > 1 ) {
						$tab_name .= '-' . ( $found_names[ $tab_name ] - 1 );
					}
					$new_tab['name'] = $tab_name;

					// tab is complete
					$new_tabs[ 'product_tab_' . $id ] = $new_tab;
				}
			}

			// add the core reviews tab on at the end
			$new_tabs['core_tab_reviews'] = $core_tabs['core_tab_reviews'];
			$new_tabs['core_tab_reviews']['position'] = count( $new_tabs ) - 1;


			if ( count( $new_tabs ) > 3 ) {
				// if we actually had any product tabs
				add_post_meta( $result->post_id, '_product_tabs',        $new_tabs, true );
				add_post_meta( $result->post_id, '_override_tab_layout', 'yes',     true );
			}
		}
	}



	/**
	 * Run when plugin version number changes
	 *
	 * @see SV_WC_Plugin::upgrade()
	 * @param string $installed_version Current version.
	 */
	protected function upgrade( $installed_version ) {

		global $wpdb;

		if ( version_compare( $installed_version, '1.0.4.1', '<=' ) ) {

			// in this version and before:
			// * custom product lite tabs were imported but their status was set
			//   to 'future' meaning they appeared in the Tab Manager menu, but
			//   not at the product level
			// * product tab layout had 'tab_name' rather than 'name' for imported
			//   custom product lite tabs
			// * imported custom product lite tabs attached to products did not
			//   have the '_override_tab_layout' meta set
			$tabs = get_posts( array( 'numberposts' => '', 'post_type' => 'wc_product_tab', 'nopaging' => true, 'post_status' => 'future' ) );

			if ( is_array( $tabs ) ) {

				foreach( $tabs as $tab ) {

					// make the tab post status 'publish'
					$update_tab = array(
						'ID'          => $tab->ID,
						'post_status' => 'publish',
					);
					wp_update_post( $update_tab );
					add_post_meta( $tab->ID, '_migrated_future', 'yes' );  // mark the tab as migrated, in case we need to reference them one day

					// fix the product tab layout 'tab_name' field, which should be 'name'
					$fixed        = false;
					$product_tabs = get_post_meta( $tab->post_parent, '_product_tabs', true );

					foreach ( $product_tabs as $index => $product_tab ) {

						if ( isset( $product_tab['tab_name'] ) && $product_tab['tab_name'] && ! isset( $product_tab['name'] ) ) {

							$product_tabs[ $index ]['name'] = $product_tab['tab_name'];

							unset( $product_tabs[ $index ]['tab_name'] );

							$fixed = true;
						}
					}

					if ( $fixed ) {
						update_post_meta( $tab->post_parent, '_product_tabs', $product_tabs );
					}

					// It seems that setting the tab layout override in existing stores would be too dangerous, so for now the following is not used
					// enable the product '_override_tab_layout' so the product tab is actually used
					// update_post_meta( $tab->post_parent, '_override_tab_layout', 'yes' );
				}
			}
			unset( $tabs );
		}

		// In version 1.0.5 the core tab previously referred to as 'attributes' now
		//  needs to be referred to as 'additional_information' for consistency with
		//  WC 2.0+, so fix the global and any product tab layouts
		if ( version_compare( $installed_version, '1.0.5', '<=' ) ) {

			// fix global tab layout
			$tab_layout = get_option( 'wc_tab_manager_default_layout', false );
			if ( $tab_layout && isset( $tab_layout['core_tab_attributes'] ) ) {

				$tab_layout['core_tab_additional_information'] = $tab_layout['core_tab_attributes'];
				$tab_layout['core_tab_additional_information']['id'] = 'additional_information';
				unset( $tab_layout['core_tab_attributes'] );

				update_option( 'wc_tab_manager_default_layout', $tab_layout );
			}

			// fix any product-level tab layouts
			$results = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key='_product_tabs'" );

			if ( is_array( $results ) ) {

				foreach ( $results as $row ) {

					$tab_layout = maybe_unserialize( $row->meta_value );

					if ( $tab_layout && isset( $tab_layout['core_tab_attributes'] ) ) {
						$tab_layout['core_tab_additional_information'] = $tab_layout['core_tab_attributes'];
						$tab_layout['core_tab_additional_information']['id'] = 'additional_information';
						unset( $tab_layout['core_tab_attributes'] );

						update_post_meta( $row->post_id, '_product_tabs', $tab_layout );
					}
				}
			}
			unset( $results );
		}

		if ( version_compare( $installed_version, '1.3.1', '<=' ) ) {

			// Enable the batch product update nag.
			$user = wp_get_current_user();

			if ( $user ) {
				update_user_meta( $user->ID, 'wc_tab_manager_show_update_products_nag', 'true' );
			}

			// Ensures that tabs are searchable if Relevanssi is installed.
			$this->get_search_instance()->update_relevanssi_searchable_tab_meta();
			$this->get_search_instance()->maybe_build_relevanssi_index();
		}
	}


} // class WC_Tab_Manager


/**
 * Returns the One True Instance of Tab Manager
 *
 * @since 1.2.0
 * @return WC_Tab_Manager
 */
function wc_tab_manager() {
	return WC_Tab_Manager::instance();
}

// Fire the missiles!
wc_tab_manager();

} // init_woocommerce_tab_manager()

//1.8.4