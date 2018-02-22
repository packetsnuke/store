<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

/**
 * Auto-load classes on demand
 * @param $class
 */
function tc_epo_autoload( $class ) {

	$path = NULL;
	$original_class = $class;
	$class = strtolower( $class );
	$file = 'class-' . str_replace( '_', '-', $class ) . '.php';

	if ( strpos( $class, 'tm_epo_fields' ) === 0 ) {
		$path = TM_EPO_PLUGIN_PATH . '/include/fields/';
	} elseif ( strpos( $class, 'tm_epo_admin_' ) === 0 ) {
		$path = TM_EPO_PLUGIN_PATH . '/admin/';
	} elseif ( strpos( $class, 'tm_extra_' ) === 0 ) {
		$path = TM_EPO_PLUGIN_PATH . '/include/';
	} elseif ( strpos( $class, 'tm_epo_' ) === 0 ) {
		if ( strpos( $class, 'tm_epo_compatibility_base' ) === 0 ) {
			$path = TM_EPO_PLUGIN_PATH . '/include/compatibility/';
		} elseif ( strpos( $class, 'tm_epo_compatibility' ) === 0 ) {
			$path = TM_EPO_PLUGIN_PATH . '/include/compatibility/classes/';
		} else {
			$path = TM_EPO_PLUGIN_PATH . '/include/classes/';
		}
	}

	$path = apply_filters( 'wc_epo_autoload_path', $path, $original_class );
	$file = apply_filters( 'wc_epo_autoload_file', $file, $original_class );

	if ( $path && is_readable( $path . $file ) ) {
		include_once($path . $file);

		return;
	}

}

define( 'TC_AUTOLOADER_LOADER', TRUE );
