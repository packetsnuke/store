<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

/** Compatibility **/
function TM_EPO_COMPATIBILITY() {
	return TM_EPO_COMPATIBILITY_base::instance();
}

/** HTML functions **/
function TM_EPO_HTML() {
	return TM_EPO_HTML_base::instance();
}

/** HELPER functions **/
function TM_EPO_HELPER() {
	return TM_EPO_HELPER_base::instance();
}

/** WPML functions **/
function TM_EPO_WPML() {
	return TM_EPO_WPML_base::instance();
}

/** LICENSE functions **/
function TM_EPO_LICENSE() {
	return TM_EPO_UPDATE_Licenser::instance();
}
/** UPDATE functions **/
function TM_EPO_UPDATER() {
	return TM_EPO_UPDATE_Updater::instance();
}

/** Plugin health check **/
function TM_EPO_CHECK() {
	return TM_EPO_CHECK_base::instance();
}

/** Field builder **/
function TM_EPO_BUILDER() {
	return TM_EPO_BUILDER_base::instance();
}

/** Main plugin interface **/
function TM_EPO() {
	return TM_Extra_Product_Options::instance();
}

/** Globals Admin Interface **/
function TM_EPO_ADMIN_GLOBAL() {
	return TM_EPO_ADMIN_Global_base::instance();
}

/** Admin Interface **/
function TM_EPO_ADMIN() {
	return TM_EPO_Admin_base::instance();
}

/** Settings Interface **/
function TM_EPO_SETTINGS() {
	return TM_EPO_SETTINGS_base::instance();
}

/** API helper Interface **/
function TM_EPO_API() {
	return TM_EPO_API_base::instance();
}


/** Load plugin textdomain **/
function tc_epo_load_textdomain() {
	$domain = TM_EPO_DIRECTORY;
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	$global_mo = trailingslashit( WP_LANG_DIR ) . 'plugins' . '/' . $domain . '-' . $locale . '.mo';
	$global_mo2 = trailingslashit( WP_LANG_DIR ) . 'plugins/' . $domain . '/' . $domain . '-' . $locale . '.mo';
	if ( file_exists( $global_mo ) ) {
		// wp-content/languages/plugins/plugin-name-$locale.mo
		load_textdomain( $domain, $global_mo );
	} elseif ( file_exists( $global_mo2 ) ) {
		// wp-content/languages/plugins/plugin-name/plugin-name-$locale.mo
		load_textdomain( $domain, $global_mo2 );
	} else {
		// wp-content/plugins/plugin-name/languages/plugin-name-$locale.mo
		load_plugin_textdomain( 'woocommerce-tm-extra-product-options', FALSE, TM_EPO_DIRECTORY . '/languages/' );
	}
}

/**
 * Settings Page
 *
 * @param $settings
 * @return array
 */
function tc_add_epo_admin_settings( $settings ) {
	$_setting = new TM_EPO_ADMIN_SETTINGS();
	if ( $_setting instanceof WC_Settings_Page ) {
		$settings[] = $_setting;
	}

	return $settings;
}


if ( !function_exists( 'tm_get_price_decimal_separator' ) ) {
	/**
	 * @return string
	 */
	function tm_get_price_decimal_separator() {
		if ( function_exists( 'wc_get_price_decimal_separator' ) ) {
			return wc_get_price_decimal_separator();
		}
		$separator = stripslashes( get_option( 'woocommerce_price_decimal_sep' ) );

		return $separator ? $separator : '.';
	}
}

if ( !function_exists( 'tc_convert_local_numbers' ) ) {
	/**
	 * @param string $input
	 * @return mixed|string
	 */
	function tc_convert_local_numbers( $input = "" ) {
		$locale = localeconv();
		$decimals = array( tm_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

		// Remove whitespace from string
		$input = preg_replace( '/\s+/', '', $input );

		// Remove locale from string
		$input = str_replace( $decimals, '.', $input );

		// Trim invalid start/end characters
		$input = rtrim( ltrim( $input, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

		return $input;
	}
}

if ( !function_exists( 'tc_needs_wc_db_update' ) ) {
	/**
	 * @return bool|mixed|void
	 */
	function tc_needs_wc_db_update() {
		$_tm_current_woo_version = get_option( 'woocommerce_db_version' );
		$_tc_needs_wc_db_update = FALSE;
		if ( get_option( 'woocommerce_db_version' ) !== FALSE ) {
			if ( version_compare( get_option( 'woocommerce_db_version' ), '2.3', '<' ) && version_compare( get_option( 'woocommerce_version' ), '2.4', '<' ) ) {
				$_tm_notice_check = '_wc_needs_update';
				$_tc_needs_wc_db_update = get_option( $_tm_notice_check );
				// no check after 2.6 update
			} elseif ( version_compare( get_option( 'woocommerce_db_version' ), '2.5', '>=' ) ) {
				$_tc_needs_wc_db_update = FALSE;
			} else {
				$_tm_notice_check = 'woocommerce_admin_notices';
				$_tc_needs_wc_db_update = in_array( 'update', get_option( $_tm_notice_check, array() ) );
			}
		}

		return $_tc_needs_wc_db_update;
	}
}

if ( !tc_needs_wc_db_update() && !function_exists( 'wc_get_product' ) && get_option( 'woocommerce_db_version' ) !== FALSE && version_compare( get_option( 'woocommerce_db_version' ), '2.2', '<' ) ) {
	/**
	 * @param bool $the_product
	 * @param array $args
	 * @return null|WC_Product
	 */
	function wc_get_product( $the_product = FALSE, $args = array() ) {
		return get_product( $the_product, $args );
	}
}

if ( !function_exists( 'wp_get_ext_types' ) ) {
	/**
	 * @return mixed|void
	 */
	function wp_get_ext_types() {

		/**
		 * Filters file type based on the extension name.
		 *
		 * @since 2.5.0
		 *
		 * @see wp_ext2type()
		 *
		 * @param array $ext2type Multi-dimensional array with extensions for a default set
		 *                        of file types.
		 */
		return apply_filters( 'ext2type', array(
			'image'       => array( 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tif', 'tiff', 'ico' ),
			'audio'       => array( 'aac', 'ac3', 'aif', 'aiff', 'm3a', 'm4a', 'm4b', 'mka', 'mp1', 'mp2', 'mp3', 'ogg', 'oga', 'ram', 'wav', 'wma' ),
			'video'       => array( '3g2', '3gp', '3gpp', 'asf', 'avi', 'divx', 'dv', 'flv', 'm4v', 'mkv', 'mov', 'mp4', 'mpeg', 'mpg', 'mpv', 'ogm', 'ogv', 'qt', 'rm', 'vob', 'wmv' ),
			'document'    => array( 'doc', 'docx', 'docm', 'dotm', 'odt', 'pages', 'pdf', 'xps', 'oxps', 'rtf', 'wp', 'wpd', 'psd', 'xcf' ),
			'spreadsheet' => array( 'numbers', 'ods', 'xls', 'xlsx', 'xlsm', 'xlsb' ),
			'interactive' => array( 'swf', 'key', 'ppt', 'pptx', 'pptm', 'pps', 'ppsx', 'ppsm', 'sldx', 'sldm', 'odp' ),
			'text'        => array( 'asc', 'csv', 'tsv', 'txt' ),
			'archive'     => array( 'bz2', 'cab', 'dmg', 'gz', 'rar', 'sea', 'sit', 'sqx', 'tar', 'tgz', 'zip', '7z' ),
			'code'        => array( 'css', 'htm', 'html', 'php', 'js' ),
		) );
	}
}

if ( !function_exists( 'tc_woocommerce_check' ) ) {
	/**
	 * @return bool
	 */
	function tc_woocommerce_check() {
		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return !tc_needs_wc_db_update() && in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}
}

if ( !function_exists( 'tc_woocommerce_check_only' ) ) {
	/**
	 * @return bool
	 */
	function tc_woocommerce_check_only() {
		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}
}

if ( !function_exists( 'tc_woocommerce_subscriptions_check' ) ) {
	/**
	 * @return bool
	 */
	function tc_woocommerce_subscriptions_check() {
		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( 'woocommerce-subscriptions/woocommerce-subscriptions.php', $active_plugins ) || array_key_exists( 'woocommerce-subscriptions/woocommerce-subscriptions.php', $active_plugins );
	}
}

/** Check for require json function for PHP 4 & 5.1 **/
if ( !function_exists( 'json_decode' ) ) {
	include_once(TM_EPO_PLUGIN_PATH . '/external/json/JSON.php');
	/**
	 * @param $data
	 * @return mixed
	 */
	function json_encode( $data ) {
		$json = new Services_JSON();

		return ($json->encode( $data ));
	}

	/**
	 * @param $data
	 * @return mixed
	 */
	function json_decode( $data ) {
		$json = new Services_JSON();

		return ($json->decode( $data ));
	}
}

if ( !function_exists( 'tc_get_roles' ) ) {
	/**
	 * @return array
	 */
	function tc_get_roles() {
		$result = array();
		$result["@everyone"] = __( 'Everyone', 'woocommerce-tm-extra-product-options' );
		$result["@loggedin"] = __( 'Logged in users', 'woocommerce-tm-extra-product-options' );
		global $wp_roles;
		if ( empty( $wp_roles ) ) {
			$all_roles = new WP_Roles();
		} else {
			$all_roles = $wp_roles;
		}
		$roles = $all_roles->roles;
		if ( $roles ) {
			foreach ( $roles as $role => $details ) {
				$name = translate_user_role( $details['name'] );
				$result[ $role ] = $name;
			}
		}

		return $result;
	}
}

if ( !function_exists( 'tc_price' ) ) {
	/**
	 * Format the price with a currency symbol.
	 *
	 * @param float $price
	 * @param array $args (default: array())
	 * @return string
	 */
	function tc_price( $price, $args = array() ) {
		extract( apply_filters( 'tc_price_args', wp_parse_args( $args, array(
			'ex_tax_label'       => FALSE,
			'currency'           => '',
			'decimal_separator'  => wc_get_price_decimal_separator(),
			'thousand_separator' => wc_get_price_thousand_separator(),
			'decimals'           => apply_filters( 'wc_epo_price_decimals', wc_get_price_decimals()),
			'price_format'       => get_woocommerce_price_format(),
		) ) ) );

		$negative = $price < 0;
		$price = apply_filters( 'tc_raw_woocommerce_price', floatval( $negative ? $price * -1 : $price ) );
		$price = apply_filters( 'formatted_woocommerce_price', number_format( $price, $decimals, $decimal_separator, $thousand_separator ), $price, $decimals, $decimal_separator, $thousand_separator );

		if ( apply_filters( 'woocommerce_price_trim_zeros', FALSE ) && $decimals > 0 ) {
			$price = wc_trim_zeros( $price );
		}

		$formatted_price = ($negative ? '-' : '') . sprintf( $price_format, get_woocommerce_currency_symbol( $currency ), $price );
		$return = '<span class="amount">' . $formatted_price . '</span>';

		if ( $ex_tax_label && wc_tax_enabled() ) {
			$return .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
		}

		return apply_filters( 'tc_price', $return, $price, $args );
	}
}

if ( !function_exists( 'tc_fix_woocommerce_bundle_rate_shipping_scripts' ) ) {
	/**
	 * woocommerce_bundle_rate_shipping chosen fix by removing
	 */
	function tc_fix_woocommerce_bundle_rate_shipping_scripts() {
		if ( !(isset( $_GET['page'] ) && isset( $_GET['tab'] ) && $_GET['page'] == 'wc-settings' && $_GET['tab'] == 'shipping') ) {
			wp_dequeue_script( 'woocommerce_bundle_rate_shipping_admin_js' );
		}
	}
}

