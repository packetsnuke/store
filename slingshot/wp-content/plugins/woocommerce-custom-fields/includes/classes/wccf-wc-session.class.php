<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Sessions
 *
 * Need to monitor status of https://github.com/woothemes/woocommerce/issues/11062
 * so that we can stop doing this by ourselves when no longer needed
 *
 * @class WCCF_WC_Session
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WC_Session')) {

class WCCF_WC_Session
{
    // Singleton instance
    protected static $instance = false;

    /**
     * Singleton control
     */
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Ajax handler to get customer id cookie data
        add_action('wp_ajax_wccf_get_customer_id_cookie_data', array($this, 'ajax_get_customer_id_cookie_data'));
        add_action('wp_ajax_nopriv_wccf_get_customer_id_cookie_data', array($this, 'ajax_get_customer_id_cookie_data'));
    }

    /**
     * Initialize session
     *
     * @access public
     * @return object
     */
    public static function initialize_session()
    {
        // Include custom session handler class
        require_once WCCF_PLUGIN_PATH . 'includes/classes/lazy/wccf-wc-session-handler.class.php';

        // Initialize session object
        $session = new WCCF_WC_Session_Handler();

        if (RightPress_Helper::wc_version_gte('3.3')) {
            $session->init();
        }

        return $session;
    }

    /**
     * Get customer id cookie data
     *
     * @access public
     * @return void
     */
    public function ajax_get_customer_id_cookie_data()
    {
        // Check if cookies can only be transferred over secure connection
        $secure = apply_filters('wc_session_use_secure_cookie', false);

        // Check if connection is secure ir required
        if ($secure && !is_ssl()) {
            echo json_encode(array(
                'result' => 'error',
            ));
            exit;
        }

        // Load WC session object
        $session = WCCF_WC_Session::initialize_session();

        // Check if session object was loaded
        if (!$session) {
            echo json_encode(array(
                'result' => 'error',
            ));
            exit;
        }

        // Imitating WC_Session_Handler
        $customer_id        = $session->generate_customer_id();
        $session_expiring   = time() + intval(apply_filters('wc_session_expiring', 60 * 60 * 47));
        $session_expiration = time() + intval(apply_filters('wc_session_expiration', 60 * 60 * 48));
        $to_hash            = $customer_id . '|' . $session_expiration;
        $cookie_hash        = hash_hmac('md5', $to_hash, wp_hash($to_hash));
        $cookie_value       = $customer_id . '||' . $session_expiration . '||' . $session_expiring . '||' . $cookie_hash;

        // Return cookie data
        echo json_encode(array(
            'result' => 'success',
            'data'  => array(
                'name'          => 'wp_woocommerce_session_' . COOKIEHASH,
                'value'         => $cookie_value,
                'expiration'    => date('r', $session_expiration),
                'path'          => COOKIEPATH,
                'domain'        => COOKIE_DOMAIN,
                'secure'        => $secure,
            ),
        ));
        exit;
    }


}

WCCF_WC_Session::get_instance();

}
