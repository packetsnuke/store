<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_elasticpress {

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

		add_filter( 'ep_skip_query_integration', array( $this, 'ep_skip_query_integration' ), 10, 2 );
	
	}

	public function ep_skip_query_integration( $ret, $query ) {

		$post_type = $query->get( 'post_type', false );

		if ( $post_type == TM_EPO_GLOBAL_POST_TYPE ){
			return true;
		}

		return $ret;
	}


}


