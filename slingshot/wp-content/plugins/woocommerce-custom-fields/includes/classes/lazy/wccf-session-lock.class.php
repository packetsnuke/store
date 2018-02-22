<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Session locking to prevent race conditions
 *
 * Adapted from https://github.com/crowdfavorite/wp-social/
 *
 * @class WCCF_Session_Lock
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Session_Lock')) {

final class WCCF_Session_Lock
{
    private $clear_stuck_lock_attempted = false;
    private $insert_lock_row_attempted = false;

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
     * Acquire session lock
     *
     * @access public
     * @param mixed $customer_id
     * @param int $seconds
     * @return bool
     */
    public function lock($customer_id, $seconds = 10)
    {
        // Reset properties
        $this->clear_stuck_lock_attempted = false;
        $this->insert_lock_row_attempted = false;

        $started = time();

        while (time() < ($started + $seconds)) {

            // Attempt to acquire lock
            if ($this->lock_attempt($customer_id)) {
                return true;
            }

            // Sleep for 100 ms
            usleep(100000);
        }

        return false;
    }

    /**
     * Attempt to acquire lock
     *
     * @access public
     * @param mixed $customer_id
     * @return bool
     */
    public function lock_attempt($customer_id)
    {
        global $wpdb;

        // Attempt to set the lock
        $result = $wpdb->query($wpdb->prepare("
            UPDATE $wpdb->options
            SET option_value = '1'
            WHERE option_name = %s
            AND option_value = '0'
        ", $this->get_lock_key($customer_id)));

        // Failed to acquire lock
        if ($result == '0' && !$this->clear_stuck_lock($customer_id) && !$this->insert_lock_row($customer_id)) {
            return false;
        }

        // Set lock time
        $wpdb->query($wpdb->prepare("
            UPDATE $wpdb->options
            SET option_value = %s
            WHERE option_name = %s
        ", current_time('mysql', 1), $this->get_lock_time_key($customer_id)));

        // Lock acquired
        return true;
    }

    /**
     * Attempt to release lock
     *
     * @access public
     * @param mixed $customer_id
     * @return bool
     */
    public function unlock($customer_id)
    {
        global $wpdb;

        // Attempt to release lock
        $result = $wpdb->query($wpdb->prepare("
            UPDATE $wpdb->options
            SET option_value = '0'
            WHERE option_name = %s
        ", $this->get_lock_key($customer_id)));

        // Check if lock was released
        return $result == '1';
    }

    /**
     * Attempt to clear stuck lock
     *
     * @access private
     * @param mixed $customer_id
     * @return bool
     */
    private function clear_stuck_lock($customer_id)
    {
        // Do not repeat this during multiple attempts
        if ($this->clear_stuck_lock_attempted) {
            return false;
        }

        global $wpdb;

        // Get current time
        $current_time = current_time('mysql', 1);

        // Clear locks older than 30 seconds
        $unlock_time = gmdate('Y-m-d H:i:s', (time() - 30));

        // Attempt to clear stuck lock
        $result = $wpdb->query($wpdb->prepare("
            UPDATE $wpdb->options
            SET option_value = %s
            WHERE option_name = %s
            AND option_value <= %s
        ", $current_time, $this->get_lock_time_key($customer_id), $unlock_time));

        // Check if stuck lock was cleared
        $this->clear_stuck_lock_attempted = true;
        return $result == '1';
    }

    /**
     * Insert lock row
     *
     * @access private
     * @param mixed $customer_id
     * @return bool
     */
    private function insert_lock_row($customer_id)
    {
        // Do not repeat this during multiple attempts
        if ($this->insert_lock_row_attempted) {
            return false;
        }

        global $wpdb;

        // Attempt to insert new lock row if none exists
        $result = $wpdb->query($wpdb->prepare("
            INSERT IGNORE $wpdb->options (option_name, option_value, autoload)
            VALUES (%s, '1', 'no')
        ", $this->get_lock_key($customer_id)));

        // Check if new row was inserted
        if ($result == '1') {

            $wpdb->query($wpdb->prepare("
                INSERT IGNORE $wpdb->options (option_name, option_value, autoload)
                VALUES (%s, %s, 'no')
            ", $this->get_lock_time_key($customer_id), current_time('mysql', 1)));

            return true;
        }

        $this->insert_lock_row_attempted = true;
        return false;
    }

    /**
     * Get lock key
     */
    private function get_lock_key($customer_id)
    {
        return 'wccf_session_' . $customer_id . '_locked';
    }

    /**
     * Get lock time key
     */
    private function get_lock_time_key($customer_id)
    {
        return 'wccf_session_' . $customer_id . '_last_lock_time';
    }


}
}
