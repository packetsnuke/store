<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_theseoframework {

	protected static $_instance = NULL;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		add_action( 'wc_epo_add_compatibility', array( $this, 'add_compatibility' ) );

	}

	public function init() {

	}

	public function add_compatibility() {

		add_filter( 'the_seo_framework_do_adjust_archive_query', array( $this, 'the_seo_framework_do_adjust_archive_query' ), 10, 2 );
	
	}

	public function the_seo_framework_do_adjust_archive_query( $ret, $query ) {

		$post_type = $query->get( 'post_type', false );

		if ( $post_type == TM_EPO_GLOBAL_POST_TYPE ){
			return false;
		}

		return $ret;
	}


}


