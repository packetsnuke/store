<?php
/**
 *	Add help tabs to a given admin page
 */

namespace UltimateWoo\HelpTabs;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Help_Tabs' ) ) :

class Help_Tabs {

	/**
	 *	@param (object) $screen - Current screen
	 *	@param (array) $tabs - Tabs to add
	 *	@param (string) $sidebar - Content to display in the help area sidebar
	 */
	public function __construct( \WP_Screen $screen, array $tabs, $sidebar = false ) {

		$this->screen = $screen;
		$this->tabs = $tabs;
		$this->sidebar = $sidebar;

		$this->assemble_tabs();

		if ( $this->sidebar ) {
			$this->assemble_sidebar();
		}
	}

	/**
	 *	Build the help tabs
	 */
	public function assemble_tabs() {

		$screen = $this->screen;

		if ( isset( $this->tabs ) && ! empty( $this->tabs ) ) {

			foreach ( $this->tabs as $id => $tab ) {

				$screen->add_help_tab( array(
					'id'        => $id,
					'title'     => $tab['title'],
					'content'   => $tab['content']
				) );

			}
		}
	}

	/**
	 *	Build the sidebar for the help panel
	 */
	public function assemble_sidebar() {
		$this->screen->set_help_sidebar( $this->sidebar );
	}
}

endif;