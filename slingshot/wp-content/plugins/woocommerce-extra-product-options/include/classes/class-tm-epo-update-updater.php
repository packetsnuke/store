<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

final class TM_EPO_UPDATE_Updater {

	protected static $_instance = NULL;
	var $title = 'WooCommerce TM Extra Product Options';
	protected $version_url = 'http://themecomplete.com/api/?';

	public function __construct() {
		$this->setup();
		add_filter( 'upgrader_pre_download', array( $this, 'upgradeFilterFromEnvato' ), 10, 4 );
		add_action( 'upgrader_process_complete', array( $this, 'removeTemporaryDir' ) );
	}

	public function setup() {
		$instance = new TM_EPO_UPDATE_Manager ( TM_EPO_VERSION, $this->get_url(), TM_EPO_PLUGIN_SLUG, $this );
	}

	public function get_url() {
		return $this->version_url . time();
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function init() {

	}

	public function upgradeFilterFromEnvato( $reply, $package, $updater ) {
		global $wp_filesystem;

		if ( (isset( $updater->skin->plugin ) && $updater->skin->plugin === TM_EPO_PLUGIN_SLUG) ||
			(isset( $updater->skin->plugin_info ) && isset( $updater->skin->plugin_info['Name'] ) && $updater->skin->plugin_info['Name'] === $this->title)
		) {
			$updater->strings['download_envato'] = __( 'Downloading package from envato market...', 'woocommerce-tm-extra-product-options' );
			$updater->skin->feedback( 'download_envato' );
			$package_filename = 'woocommerce-tm-extra-product-options.zip';
			$res = $updater->fs_connect( array( WP_CONTENT_DIR ) );
			if ( !$res ) {
				return new WP_Error( 'no_credentials', __( "Error! Can't connect to filesystem", 'woocommerce-tm-extra-product-options' ) );
			}
			$username = get_option( 'tm_epo_envato_username' );
			$api_key = get_option( 'tm_epo_envato_apikey' );
			$purchase_code = get_option( 'tm_epo_envato_purchasecode' );

			if ( !TM_EPO_LICENSE()->check_license() ) {
				return new WP_Error( 'no_credentials', __( 'To receive automatic updates license activation is required. Please visit <a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . TM_EPO_ADMIN_SETTINGS_ID ) . '">' . 'Settings</a> to activate WooCommerce Extra Product Options.', 'woocommerce-tm-extra-product-options' ) );
			}

			$json = wp_remote_get( $this->envatoDownloadPurchaseUrl( $username, $api_key, $purchase_code ) );
			$result = json_decode( $json['body'], TRUE );
			if ( !isset( $result['download-purchase']['download_url'] ) ) {
				return new WP_Error( 'no_credentials', __( 'Error! Envato API error' . (isset( $result['error'] ) ? ': ' . $result['error'] : '.'), 'woocommerce-tm-extra-product-options' ) );
			}
			$download_file = download_url( $result['download-purchase']['download_url'] );
			if ( is_wp_error( $download_file ) ) {
				return $download_file;
			}
			$upgrade_folder = $wp_filesystem->wp_content_dir() . 'uploads/woocommerce-tm-extra-product-options-envato-package';
			if ( is_dir( $upgrade_folder ) ) {
				$wp_filesystem->delete( $upgrade_folder );
			}
			$result = unzip_file( $download_file, $upgrade_folder );
			if ( $result && is_file( $upgrade_folder . '/' . $package_filename ) ) {
				return $upgrade_folder . '/' . $package_filename;
			}

			return new WP_Error( 'no_credentials', __( 'Error on unzipping package', 'woocommerce-tm-extra-product-options' ) );
		}

		return $reply;
	}

	protected function envatoDownloadPurchaseUrl( $username, $api_key, $purchase_code ) {
		return 'http://marketplace.envato.com/api/edge/' . rawurlencode( $username ) . '/' . rawurlencode( $api_key ) . '/download-purchase:' . rawurlencode( $purchase_code ) . '.json';
	}

	public function removeTemporaryDir() {
		global $wp_filesystem;
		if ( is_dir( $wp_filesystem->wp_content_dir() . 'uploads/woocommerce-tm-extra-product-options-envato-package' ) ) {
			$wp_filesystem->delete( $wp_filesystem->wp_content_dir() . 'uploads/woocommerce-tm-extra-product-options-envato-package', TRUE );
		}
	}
}


final class TM_EPO_UPDATE_Manager {

	public $current_version;
	public $update_path;
	public $plugin_slug;
	public $slug;
	public $TM_Updater_instance;

	protected $url = 'http://bit.ly/1syDtHe';

	function __construct( $current_version, $update_path, $plugin_slug, $instance ) {

		$this->TM_Updater_instance = $instance;
		$this->plugin_envato_id = TM_EPO_PLUGIN_ID;
		$this->current_version = $current_version;
		$this->plugin_slug = $plugin_slug;
		$this->update_path = $update_path;
		$this->slug = explode( '/', $plugin_slug );
		$this->slug = str_replace( '.php', '', $this->slug[1] );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'tm_update_plugins' ) );
		add_filter( 'plugins_api', array( $this, 'tm_plugins_api' ), 10, 3 );
		add_action( 'in_plugin_update_message-' . $this->plugin_slug, array( $this, 'tm_update_message' ) );
	}

	public function tm_update_plugins( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$remote_version = $this->remote_api_call( 'version' );

		if ( version_compare( $this->current_version, $remote_version, '<' ) ) {
			$obj = new stdClass();
			$obj->slug = $this->slug;
			$obj->new_version = $remote_version;

			if ( TM_EPO_LICENSE()->check_license() ) {
				$obj->url = $this->update_path;
				$obj->package = $this->update_path;
			} else {
				$obj->url = '';
				$obj->package = '';
			}

			$obj->name = $this->TM_Updater_instance->title;
			$transient->response[ $this->plugin_slug ] = $obj;
		}

		return $transient;
	}

	public function remote_api_call( $action = "", $is_serialized = FALSE ) {
		$request = wp_remote_post( $this->update_path,
			array( 'body' =>
					   array( 'action' => $action,
							  'id'     => $this->plugin_envato_id,
							  'type'   => 'plugin',
					   ) ) );
		if ( !is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
			return ($is_serialized) ? unserialize( ($request['body']) ) : $request['body'];
		}

		return FALSE;
	}

	public function tm_plugins_api( $false, $action, $arg ) {
		if ( isset( $arg->slug ) && $arg->slug === $this->slug ) {
			$info = $this->remote_api_call( 'info', TRUE );
			if ( $info ) {
				$info->name = $this->TM_Updater_instance->title;
				$info->slug = $this->slug;

				if ( TM_EPO_LICENSE()->check_license() ) {
					$info->download_link = $this->update_path;
				}
			}

			return $info;
		}

		return $false;
	}

	public function tm_update_message() {
		if ( !TM_EPO_LICENSE()->check_license() ) {
			echo '<br /><a href="' . $this->url . '">' . __( 'Download new version from CodeCanyon', 'woocommerce-tm-extra-product-options' ) . '</a>' . ' ' . __( 'or register the plugin to receive automatic updates.', 'woocommerce-tm-extra-product-options' );
		}
	}
}

