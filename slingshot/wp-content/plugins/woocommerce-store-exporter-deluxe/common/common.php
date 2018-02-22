<?php
/**
*
* Filename: common.php
* Description: common.php loads commonly accessed functions across the Visser Labs suite.
* 
* Premium
* - woo_ce_admin_deactivate_updater_plugin
* - woo_ce_updater_api_call
* - woo_ce_check_for_plugin_update
* - vl_common_make_request
*
* Free
* - woo_get_action
* - woo_is_wpsc_activated
* - woo_is_woo_activated
* - woo_is_jigo_activated
* - woo_is_exchange_activated
* - woo_get_woo_version
*
*/

if( is_admin() ) {

	/* Start of: WordPress Administration */

	/**
	 * Deactivate the Visser Labs Updater.
	 */
	if( class_exists( 'VL_Updater' ) ) {
		function woo_ce_admin_deactivate_updater_plugin() {

			deactivate_plugins( '/visser-labs-updater/visser-labs-updater.php' );

		}
		add_action( 'admin_init', 'woo_ce_admin_deactivate_updater_plugin' );

		function woo_ce_admin_deactivate_updater_plugin_notice() {

			ob_start(); ?>
<div class="updated">
	<p><?php printf( __( 'Store Exporter Deluxe now supports automatic updating so the legacy <em>Visser Labs Updater</em> Plugin has been automatically de-activated. You can safely delete the <em>Visser Labs Updater</em> Plugin from the Plugins screen.', 'woocommerce-exporter' ) ); ?></p>
</div>
<?php
			ob_end_flush();

		}
		add_action( 'admin_notices', 'woo_ce_admin_deactivate_updater_plugin_notice' );
	}

	if( !class_exists( 'VL_Updater' ) && !function_exists( 'woo_ce_updater_api_call' ) ) {
		function woo_ce_updater_api_call( $api, $action, $args ) {

			// Check if a Plugin Slug has been provided
			if( !isset( $args->slug ) )
				return $api;

			$plugin_slug = 'woocommerce-store-exporter-deluxe';
			$plugin_relpath = WOO_CD_RELPATH;

			// Check if we are fetching our Plugin
			if( $args->slug <> $plugin_slug )
				return $api;

			// Get the current version
			$plugin_info = get_site_transient( 'update_plugins' );
			$plugin_data = get_plugin_data( WOO_CD_PATH . '/exporter-deluxe.php' );

			$current_version = ( isset( $plugin_info->checked ) ? $plugin_info->checked[$plugin_relpath] : false );
			if( $current_version == false )
				return $api;

			$args->name = ( isset( $plugin_data['name'] ) ? $plugin_data['name'] : '' );
			$args->version = $current_version;

			// Start checking for an update
			$response = vl_common_make_request( $action, $args );

			return $response;

		}
		add_filter( 'plugins_api', 'woo_ce_updater_api_call', 10, 3 );
	}

	if( !class_exists( 'VL_Updater' ) && !function_exists( 'woo_ce_check_for_plugin_update' ) ) {
		function woo_ce_check_for_plugin_update( $checked_data ) {

			// Comment out these two lines during testing.
			if( empty( $checked_data->checked ) )
				return $checked_data;

			$plugin_slug = 'woocommerce-store-exporter-deluxe';
			$plugin_relpath = WOO_CD_RELPATH;

			$args = (object)array(
				'slug' => $plugin_slug
			);

			$plugin_info = vl_common_make_request( 'plugin_information', $args );

			if( is_wp_error( $plugin_info ) )
				return $checked_data;

			// Check if Plugin is in the Plugins list
			if( isset( $checked_data->checked[$plugin_relpath] ) && version_compare( $checked_data->checked[$plugin_relpath], $plugin_info->version, '<' ) ) {
				$checked_data->response[$plugin_relpath] = (object)array(
					'version' => $plugin_info->version,
					'new_version' => $plugin_info->version,
					'last_updated' => $plugin_info->last_updated,
					'slug' => $plugin_slug,
					'package' => $plugin_info->download_link,
					'name' => $plugin_info->slug,
					'author' => $plugin_info->author,
					'url' => $plugin_info->homepage,
					'requires' => $plugin_info->requires,
					'tested' => $plugin_info->tested
				);
			}

			return $checked_data;

		}
		// Take over the update check
		add_filter( 'pre_set_site_transient_update_plugins', 'woo_ce_check_for_plugin_update' );
	}

	if( !class_exists( 'VL_Updater' ) && !function_exists( 'vl_common_make_request' ) ) {
		function vl_common_make_request( $action, $args ) {

			global $wp_version;

			$update_uri = 'http://updates.visser.com.au/index.php';
			$request_string = array(
				'body' => array(
					'action' => $action, 
					'request' => serialize( $args ),
					'api-key' => md5( get_bloginfo( 'url' ) )
				),
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
			);
			$request = wp_remote_post( $update_uri, $request_string );

			if( is_wp_error( $request ) )
				$response = new WP_Error( 'plugins_api_failed', __( 'An Unexpected HTTP Error occurred during the API request.', 'woocommerce-exporter' ), $request->get_error_message() );
			else
				$response = maybe_unserialize( $request['body'] );

			if( $response === false )
				$response = new WP_Error( 'plugins_api_failed', __( 'An unknown error occurred.', 'woocommerce-exporter' ), $request['body'] );

			return $response;

		}
	}

	// Load Dashboard widgets
	include_once( WOO_CD_PATH . 'includes/common-dashboard_widgets.php' );

	/* End of: WordPress Administration */

}

if( !function_exists( 'woo_get_action' ) ) {
	function woo_get_action( $prefer_get = false ) {

		if ( isset( $_GET['action'] ) && $prefer_get )
			return sanitize_text_field( $_GET['action'] );

		if ( isset( $_POST['action'] ) )
			return sanitize_text_field( $_POST['action'] );

		if ( isset( $_GET['action'] ) )
			return sanitize_text_field( $_GET['action'] );

		return;

	}
}

if( !function_exists( 'woo_is_wpsc_activated' ) ) {
	function woo_is_wpsc_activated() {

		if( class_exists( 'WP_eCommerce' ) || defined( 'WPSC_VERSION' ) )
			return true;

	}
}

if( !function_exists( 'woo_is_woo_activated' ) ) {
	function woo_is_woo_activated() {

		if( class_exists( 'Woocommerce' ) )
			return true;

	}
}

if( !function_exists( 'woo_is_jigo_activated' ) ) {
	function woo_is_jigo_activated() {

		if( function_exists( 'jigoshop_init' ) )
			return true;

	}
}

if( !function_exists( 'woo_is_exchange_activated' ) ) {
	function woo_is_exchange_activated() {

		if( function_exists( 'IT_Exchange' ) )
			return true;

	}
}

if( !function_exists( 'woo_get_woo_version' ) ) {
	function woo_get_woo_version() {

		$version = false;
		if( defined( 'WC_VERSION' ) ) {
			$version = WC_VERSION;
		// Backwards compatibility
		} else if( defined( 'WOOCOMMERCE_VERSION' ) ) {
			$version = WOOCOMMERCE_VERSION;
		}
		return $version;
	
	}
}
?>