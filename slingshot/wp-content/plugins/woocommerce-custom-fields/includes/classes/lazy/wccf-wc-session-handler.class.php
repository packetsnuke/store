<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Session Handler
 *
 * @class WCCF_WC_Session_Handler
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WC_Session_Handler')) {

class WCCF_WC_Session_Handler extends WC_Session_Handler
{

    /**
     * Set a session variable
     *
     * @access public
     * @param string $key
     * @param mixed $value
     * @param bool $locking
     * @return void
     */
    public function set($key, $value, $locking = false)
    {
        global $wpdb;

        // Get sessions table name (can't access this property from parent as visibility is set to private)
        $table = $wpdb->prefix . 'woocommerce_sessions';

        // Value already exists in session
        if ($value === $this->get($key)) {
            return true;
        }

        // Get session data from database if needed
        if ($locking) {

            // Attempt to create a session if user does not have one
            if (!$this->has_session() && !headers_sent() && did_action('wp_loaded')) {
                $this->set_customer_session_cookie(true);
            }

            // Load session locking handler
            require_once(WCCF_PLUGIN_PATH . 'includes/classes/lazy/wccf-session-lock.class.php');
            $wccf_session_lock = WCCF_Session_Lock::get_instance();

            // Acquire session lock
            if (!$wccf_session_lock->lock($this->_customer_id)) {
                return false;
            }

            // Get fresh session data from database
            if ($this->has_session()) {
                $session_data = $wpdb->get_var($wpdb->prepare("SELECT session_value FROM $table WHERE session_key = %s", $this->_customer_id));
                $this->_data = (array) maybe_unserialize($session_data);
            }
        }

        // Set value
        $this->_data[sanitize_key($key)] = maybe_serialize($value);
        $this->_dirty = true;

        // Update session data in database if needed
        if ($locking) {

            // Store session data immediately
            $this->save_data();

            // Release session lock
            $wccf_session_lock->unlock($this->_customer_id);
        }

        // Session variable set
        return true;
    }


}
}
