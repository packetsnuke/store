<?php

/**
 * This file represents an example of the code that themes would use to register
 * the required plugins.
 *
 * It is expected that theme authors would copy and paste this code into their
 * functions.php file, and amend to suit.
 *
 * @package       TGM-Plugin-Activation
 * @subpackage    Example
 * @version       2.3.6
 * @author        Thomas Griffin <thomas@thomasgriffinmedia.com>
 * @author        Gary Jones <gamajo@gamajo.com>
 * @copyright     Copyright (c) 2012, Thomas Griffin
 * @license       http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 * @link          https://github.com/thomasgriffin/TGM-Plugin-Activation
 */
/**
 * Include the TGM_Plugin_Activation class.
 */
require_once TP_FRAMEWORK_LIBS_DIR . 'class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'tp_register_required_plugins' );

/**
 * Register the required plugins for this theme.
 *
 * In this example, we register two plugins - one included with the TGMPA library
 * and one from the .org repo.
 *
 * The variable passed to tgmpa_register_plugins() should be an array of plugin
 * arrays.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function tp_register_required_plugins() {
	$plugins = array(
		array(
			'name'     => 'Regenerate Thumbnails',
			'slug'     => 'regenerate-thumbnails',
			'required' => false,
		),
		array(
			'name'     => 'SiteOrigin Page Builder',
			'slug'     => 'siteorigin-panels',
			'source'   => 'https://downloads.wordpress.org/plugin/siteorigin-panels.zip',
			'required' => true,
		),
		array(
			'name'     => 'WooCommerce Products Filter',
			'slug'     => 'woocommerce-products-filter',
			'source'   => 'https://downloads.wordpress.org/plugin/woocommerce-products-filter.zip',
			'required' => true,
		),
		array(
			'name'     => 'Contact Form 7',
			'slug'     => 'contact-form-7',
			'required' => false,
		),
		array(
			'name'     => 'MailChimp for WordPress',
			'slug'     => 'mailchimp-for-wp',
			'required' => false,
		),
		array(
			'name'     => 'WooCommerce',
			'slug'     => 'woocommerce',
			'required' => false,

		),
		array(
			'name'     => 'YITH WooCommerce Wishlist',
			'slug'     => 'yith-woocommerce-wishlist',
			'required' => false,
		),
		array(
			'name'               => 'Portfolio By ThimPress',
			'slug'               => 'thim-portfolio',
			'source'             => TP_THEME_DIR . '/inc/plugins/thim-portfolio.zip',
			'required'           => false,
			'force_activation'   => false,
			'force_deactivation' => false,
			'external_url'       => '',
		),
		array(
			'name'               => 'WooCommerce Sales Countdown',
			'slug'               => 'woosalescountdown',
			'source'             => TP_THEME_DIR . '/inc/plugins/woosalescountdown.zip',
			'required'           => false,
			'force_activation'   => false,
			'force_deactivation' => false,
			'external_url'       => '',
		),
		array(
			'name'               => 'Revolution Slider',
			'slug'               => 'revslider',
			'source'             => TP_THEME_DIR . '/inc/plugins/revslider.zip',
			'version'            => '5.2.5.3',
			'required'           => false,
			'force_activation'   => false,
			'force_deactivation' => false,
			'external_url'       => '',
		),
	);


	/**
	 * Array of configuration settings. Amend each line as needed.
	 * If you want the default strings to be available under your own theme domain,
	 * leave the strings uncommented.
	 * Some of the strings are added into a sprintf, so see the comments at the
	 * end of each line for what each argument will be.
	 */

	$config = array(
		'id'           => 'thim', // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '', // Default absolute path to pre-packaged plugins.
		'menu'         => 'install-required-plugins', // Menu slug.
		'parent_slug'  => 'themes.php', // Parent menu slug.
		'capability'   => 'edit_theme_options', // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true, // Show admin notices or not.
		'dismissable'  => true, // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '', // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false, // Automatically activate plugins after installation or not.
		'message'      => '', // Message to output right before the plugins table.
		'strings'      => array(
			'page_title'                      => __( 'Install Required Plugins', 'thim' ),
			'menu_title'                      => __( 'Install Plugins', 'thim' ),
			'installing'                      => __( 'Installing Plugin: %s', 'thim' ), // %s = plugin name.
			'oops'                            => __( 'Something went wrong with the plugin API.', 'thim' ),
			'notice_can_install_required'     => _n_noop(
				'This theme requires the following plugin: %1$s.',
				'This theme requires the following plugins: %1$s.',
				'thim'
			), // %1$s = plugin name(s).
			'notice_can_install_recommended'  => _n_noop(
				'This theme recommends the following plugin: %1$s.',
				'This theme recommends the following plugins: %1$s.',
				'thim'
			), // %1$s = plugin name(s).
			'notice_cannot_install'           => _n_noop(
				'Sorry, but you do not have the correct permissions to install the %1$s plugin.',
				'Sorry, but you do not have the correct permissions to install the %1$s plugins.',
				'thim'
			), // %1$s = plugin name(s).
			'notice_ask_to_update'            => _n_noop(
				'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.',
				'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.',
				'thim'
			), // %1$s = plugin name(s).
			'notice_ask_to_update_maybe'      => _n_noop(
				'There is an update available for: %1$s.',
				'There are updates available for the following plugins: %1$s.',
				'thim'
			), // %1$s = plugin name(s).
			'notice_cannot_update'            => _n_noop(
				'Sorry, but you do not have the correct permissions to update the %1$s plugin.',
				'Sorry, but you do not have the correct permissions to update the %1$s plugins.',
				'thim'
			), // %1$s = plugin name(s).
			'notice_can_activate_required'    => _n_noop(
				'The following required plugin is currently inactive: %1$s.',
				'The following required plugins are currently inactive: %1$s.',
				'thim'
			), // %1$s = plugin name(s).
			'notice_can_activate_recommended' => _n_noop(
				'The following recommended plugin is currently inactive: %1$s.',
				'The following recommended plugins are currently inactive: %1$s.',
				'thim'
			), // %1$s = plugin name(s).
			'notice_cannot_activate'          => _n_noop(
				'Sorry, but you do not have the correct permissions to activate the %1$s plugin.',
				'Sorry, but you do not have the correct permissions to activate the %1$s plugins.',
				'thim'
			), // %1$s = plugin name(s).
			'install_link'                    => _n_noop(
				'Begin installing plugin',
				'Begin installing plugins',
				'thim'
			),
			'update_link'                     => _n_noop(
				'Begin updating plugin',
				'Begin updating plugins',
				'thim'
			),
			'activate_link'                   => _n_noop(
				'Begin activating plugin',
				'Begin activating plugins',
				'thim'
			),
			'return'                          => __( 'Return to Required Plugins Installer', 'thim' ),
			'plugin_activated'                => __( 'Plugin activated successfully.', 'thim' ),
			'activated_successfully'          => __( 'The following plugin was activated successfully:', 'thim' ),
			'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'thim' ), // %1$s = plugin name(s).
			'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'thim' ), // %1$s = plugin name(s).
			'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'thim' ), // %s = dashboard link.
			'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'thim' ),
			'nag_type'                        => 'updated', // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
		)
	);
	tgmpa( $plugins, $config );
}