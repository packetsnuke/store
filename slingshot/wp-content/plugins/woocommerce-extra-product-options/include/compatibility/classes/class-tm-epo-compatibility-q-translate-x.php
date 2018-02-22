<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_COMPATIBILITY_q_translate_x {

	protected static $_instance = NULL;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

		//add_action( 'wc_epo_add_compatibility', array( $this, 'add_compatibility' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 5 );
		add_action( 'plugins_loaded', array( $this, 'add_compatibility' ), 2 );
	}

	public function init() {

	}

	public function add_compatibility() {
		/** Q-translate-X support **/
		add_filter( 'tm_translate', array( $this, 'tm_translate' ), 50, 1 );
		if ( function_exists( 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
			add_filter( 'tm_translate', 'qtranxf_useCurrentLanguageIfNotFoundUseDefaultLanguage', 51, 1 );
		}
	}

	/** Q-translate-X support **/
	public function tm_translate( $text = "" ) {
		return $text;
	}

	public function wp_enqueue_scripts( $text = "" ) {
		if ( defined( 'QTRANSLATE_FILE' ) ) {
			global $q_config;
			if ( isset( $q_config['enabled_languages'] ) ) {
				wp_enqueue_script( 'tm-epo-q-translate-x-clogic', TM_EPO_PLUGIN_URL . '/include/compatibility/assets/js/tm-epo-q-translate-x-clogic.js', array( 'jquery' ), TM_EPO()->version, TRUE );
				$args = array(
					'enabled_languages' => $q_config['enabled_languages'],
					'language'          => $q_config['language'],
				);
				wp_localize_script( 'tm-epo-q-translate-x-clogic', 'tm_epo_q_translate_x_clogic_js', $args );
			}
		}
	}
}


