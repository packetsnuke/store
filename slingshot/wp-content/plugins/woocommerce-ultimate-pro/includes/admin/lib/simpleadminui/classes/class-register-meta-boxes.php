<?php
/**
 *	Register Meta Boxes
 */

namespace UltimateWoo\RegisterMetaBoxes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Register_Meta_Boxes' ) ) :

class Register_Meta_Boxes {

	private $slug;

	/**
	 *	@param (string) $slug - The page's slug
	 */
	public function __construct( $slug ) {
		$this->slug = $slug;
		$this->hooks();
	}

	/**
	 *	Action/filter hooks
	 */
	public function hooks() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
	}

	/**
	 *	Register admin page's meta boxes
	 */
	public function register_meta_boxes() {

		// Get current screen
		$slug = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		if ( ! $slug ) {
			return;
		}

		$page = \UltimateWoo\AdminPages\Admin_Pages::get_registered_page( $slug );

		// Bail if not on one of our settings page
		if ( ! $page ) {
			return;
		}

		// Get the current page's info
		$tabs = $page->get_tabs();

		if ( ! empty( $tabs ) && isset( $_GET['tab'] ) ) {

			$tab = sanitize_text_field( $_GET['tab'] );

			// Do tabbed meta boxes
			do_action( "{$slug}_meta_boxes_{$tab}" );
		
		} else {

			do_action( "{$slug}_meta_boxes" );
		}
	}

}

endif;