<?php

/*
  Copyright: © 2009-2017 Lucas Stark.
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( is_woocommerce_active() ) {

	include 'compatibility.php';

	add_action('plugins_loaded', 'wc_gravityforms_product_addons_plugins_loaded');

	function wc_gravityforms_product_addons_plugins_loaded() {
		if ( WC_GFPA_Compatibility::is_wc_version_gte_2_7() ) {
			require_once 'gravityforms-product-addons-main.php';
		}
	}

	function wc_gfpa_get_plugin_url() {
		return plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) );
	}

}

//3.2.2