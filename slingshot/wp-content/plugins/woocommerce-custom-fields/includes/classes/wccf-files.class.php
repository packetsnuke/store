<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to files
 *
 * @class WCCF_Files
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Files')) {

class WCCF_Files
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
        // Hook to WordPress 'wp_loaded' action
        add_action('wp_loaded', array($this, 'on_wp_loaded'), 99);
    }

    /**
     * WordPress 'wp_loaded'
     *
     * @access public
     * @return void
     */
    public function on_wp_loaded()
    {
        // Handle Ajax file uploads
        add_action('wp_ajax_wccf_file_upload', array($this, 'ajax_file_upload'));
        add_action('wp_ajax_nopriv_wccf_file_upload', array($this, 'ajax_file_upload'));

        // Handle Ajax file removals
        add_action('wp_ajax_wccf_remove_file', array($this, 'ajax_remove_file'));
        add_action('wp_ajax_nopriv_wccf_remove_file', array($this, 'ajax_remove_file'));

        // Intercept file download call
        if (!empty($_GET['wccf_file_download'])) {
            $this->file_download();
        }
    }

    /**
     * Store file and return file access key
     *
     * @access public
     * @param array $file_data
     * @param bool $strict
     * @return mixed
     */
    public static function store_file($file_data, $strict = false)
    {
        // Make sure file data is set
        if (empty($file_data) || !is_array($file_data)) {
            return false;
        }

        // Get subdirectory
        // Note: we are adding files to subdirectories so that we do not run into max file in a directory limit
        $subdirectory = date('Ym');

        // Get upload directory
        $upload_directory = WCCF_Files::get_temp_directory($subdirectory);

        // Check if we got upload directory
        if (!$upload_directory) {
            return false;
        }

        // Generate random file storage key
        $storage_key = WCCF_Files::get_unique_file_storage_key($upload_directory);

        // Check if storage key was generated
        if (!$storage_key) {
            return false;
        }

        // Get file path
        $file_path = $upload_directory . '/' . $storage_key;

        // Attempt to move file from temp files
        if ($strict && !move_uploaded_file($file_data['tmp_name'], $file_path)) {
            return false;
        }
        else if (!$strict && !rename($file_data['tmp_name'], $file_path)) {
            return false;
        }

        // Return subdirectory and file storage key
        return array(
            'subdirectory' => $subdirectory,
            'storage_key'   => $storage_key,
        );
    }

    /**
     * Download file uploaded via custom field
     *
     * We assume that access key can't be guessed and just output the
     * requested file here with no further checks
     *
     * @access public
     * @return void
     */
    public function file_download()
    {
        try {

            // Get access key
            if (!empty($_GET['wccf_file_download'])) {
                $access_key = $_GET['wccf_file_download'];
            }
            else {
                throw new Exception;
            }

            // Check if current user has access to file
            if (!WCCF_Files::current_user_can_access($access_key)) {
                throw new Exception;
            }

            // Get file data
            $file_data = WCCF_Files::get_data_by_access_key($access_key);

            // Check if file data was loaded
            if (empty($file_data) || empty($file_data['subdirectory']) || empty($file_data['storage_key'])) {
                throw new Exception;
            }

            // Get file path
            $file_path = WCCF_Files::locate_file($file_data['subdirectory'], $file_data['storage_key']);

            // Check if file can be located
            if (!$file_path) {
                throw new Exception;
            }

            // Open file for reading
            $fp = fopen($file_path, 'rb');

            // Check if file is open
            if (!$fp) {
                throw new Exception;
            }

            // Push file to browser
            header('Content-Type: ' . $file_data['type']);
            header('Content-Length: ' . filesize($file_path));
            header('Content-disposition: attachment; filename="' . $file_data['name'] . '"');
            fpassthru($fp);
            exit;
        }
        catch (Exception $e) {

            // Remove file download property and redirect
            $redirect_url = remove_query_arg('wccf_file_download');
            wp_redirect($redirect_url);
            exit;
        }
    }

    /**
     * Print file download link for file field
     *
     * @access public
     * @param string $access_key
     * @param array $file_data
     * @param string $prepend
     * @param string $append
     * @return void
     */
    public static function print_file_download_link_html($access_key, $file_data = array(), $prepend = '', $append = '')
    {
        // Check if current user has access to file
        if (!WCCF_Files::current_user_can_access($access_key)) {
            return;
        }

        // Print file download link
        echo WCCF_Files::get_file_download_link_html($access_key, $file_data, $prepend, $append);
    }

    /**
     * Render file download link for file field
     *
     * @access public
     * @param string $access_key
     * @param array $file_data
     * @param string $prepend
     * @param string $append
     * @return string
     */
    public static function get_file_download_link_html($access_key, $file_data = array(), $prepend = '', $append = '')
    {
        // Get file data
        $file_data = $file_data ?: WCCF_Files::get_data_by_access_key($access_key);

        // Check if file is available
        if (!empty($file_data) && WCCF_Files::is_available($file_data['subdirectory'], $file_data['storage_key'])) {

            // Format download link
            $url = home_url('/?wccf_file_download=' . $access_key);
            $html = '<a href="' . $url . '">' . $file_data['name'] . '</a>';
        }
        else {

            // Display not available notice
            $html = __('not available', 'rp_wccf');
        }

        // Return formatted link
        return $prepend . $html . $append;
    }

    /**
     * Get file upload directory
     *
     * @access public
     * @param string $subdirectory
     * @return mixed
     */
    public static function get_files_directory($subdirectory = null)
    {
        return WCCF_Files::get_directory_path('files', $subdirectory);
    }

    /**
     * Get temporary files directory
     *
     * @access public
     * @param string $subdirectory
     * @return mixed
     */
    public static function get_temp_directory($subdirectory = null)
    {
        return WCCF_Files::get_directory_path('temp', $subdirectory);
    }

    /**
     * Get directory path
     *
     * @access public
     * @param string $directory_type
     * @param string $subdirectory
     * @return mixed
     */
    public static function get_directory_path($directory_type, $subdirectory = null)
    {
        // Get uploads path
        $wp_upload_dir = wp_upload_dir();

        // Allow developers to change file storage location
        $basedir = apply_filters('wccf_file_path', $wp_upload_dir['basedir']);

        // Get final file path
        $file_path = untrailingslashit($basedir) . '/wccf/' . $directory_type;

        // Append subdirectory if provided
        if ($subdirectory) {
            $file_path .= '/' . $subdirectory;
        }

        // Set up directory
        if (!WCCF_Files::set_up_upload_directory($file_path)) {
            return false;
        }

        // Fix the path for Windows hosting
        if (preg_match('/\\\/', $file_path)) {
            $file_path = addslashes($file_path);
        }

        // Return directory path
        return $file_path;
    }

    /**
     * Set up file upload directory
     *
     * @access public
     * @param bool $skip_index_file
     * @return bool
     */
    public static function set_up_upload_directory($file_path, $skip_index_file = false)
    {
        $result = true;

        // Create directory if it does not exist yet
        if (!file_exists($file_path)) {
            $result = mkdir($file_path, 0755, true);
        }

        // Protect files from directory listing
        if (!$skip_index_file && !file_exists($file_path . '/index.php')) {
            touch($file_path . '/index.php');
        }

        return $result;
    }

    /**
     * Get unique file storage key
     *
     * @access public
     * @param string $upload_directory
     * @return string
     */
    public static function get_unique_file_storage_key($upload_directory)
    {
        return WCCF_Files::get_unique_file_key('storage', $upload_directory);
    }

    /**
     * Get unique file access key
     *
     * @access public
     * @param string $current_key
     * @param array $files
     * @return string
     */
    public static function get_unique_file_access_key($current_key = null, $files = array())
    {
        return WCCF_Files::get_unique_file_key('access', null, $current_key, $files);
    }

    /**
     * Get unique file storage or access key
     *
     * @access public
     * @param string $key_type
     * @param string $upload_directory
     * @param string $current_key
     * @param array $files
     * @return string
     */
    public static function get_unique_file_key($key_type, $upload_directory = null, $current_key = null, $files = array())
    {
        $key = false;

        // Limit attempts to prevent infinite cycle in case we have some technical glitch
        $attempts = 10;

        // Attempt to get unique file key
        while (!$key && $attempts) {

            // Use current key or generate a new one
            if (!isset($random) && $current_key !== null) {
                $random = $current_key;
            }
            else {
                $random = RightPress_Helper::get_hash(true);
            }

            // Validate storage key
            if ($key_type === 'storage' && !file_exists($upload_directory . '/' . $random)) {
                $key = $random;
                break;
            }

            // Validate access key
            if ($key_type === 'access' && !RightPress_Helper::meta_key_exists('_wccf_file_' . $random) && !isset($files[$random])) {
                $key = $random;
                break;
            }

            // Decrement attempts
            $attempts--;
        }

        return $key;
    }

    /**
     * Get file data by access key
     *
     * @access public
     * @param string $access_key
     * @param int $item_id
     * @return mixed
     */
    public static function get_data_by_access_key($access_key, $item_id = null)
    {
        // Get file data from meta
        // WC31: Fix this data retrieval method after WC implements object search by meta
        $file_data = RightPress_Helper::get_meta(WCCF_Field::get_file_data_access_key($access_key), null, $item_id);

        // Get file data from cart
        if (!$file_data && !is_admin()) {
            $file_data = WCCF_Files::get_data_from_cart($access_key);
        }

        // Get file data from user session
        if (!$file_data) {
            if ($session = WCCF_WC_Session::initialize_session()) {
                if ($file_data_from_session = $session->get(WCCF_Field::get_temp_file_data_access_key($access_key))) {
                    $file_data = $file_data_from_session;
                }
            }
        }

        // Check if file data was found
        return $file_data ? maybe_unserialize($file_data) : false;
    }

    /**
     * Get file data from cart by access key
     *
     * @access public
     * @param string $access_key
     * @return mixed
     */
    public static function get_data_from_cart($access_key)
    {
        global $woocommerce;

        // Get cart items
        $cart_items = (isset($woocommerce->cart) && is_object($woocommerce->cart)) ? $woocommerce->cart->get_cart() : array();

        // Iterate over cart items
        foreach ($cart_items as $cart_item_key => $cart_item) {

            // Check if our data array is present
            if (!empty($cart_item['wccf'])) {

                // Iterate over field values
                foreach ($cart_item['wccf'] as $field_value) {

                    // Check if file data is present
                    if (!empty($field_value['files'][$access_key])) {
                        return $field_value['files'][$access_key];
                    }
                }
            }
        }

        // Not found in cart
        return false;
    }

    /**
     * Check if file is present in filesystem
     *
     * @access public
     * @param string $subdirectory
     * @param string $storage_key
     * @return bool
     */
    public static function is_available($subdirectory, $storage_key)
    {
        // Check if file can be located
        if (WCCF_Files::locate_file($subdirectory, $storage_key)) {
            return true;
        }

        // Get temp path
        $temp_path = WCCF_Files::get_temp_directory($subdirectory) . '/' . $storage_key;

        // Check if file is present directly in temporary directory (as just uploaded file)
        return file_exists($temp_path);
    }

    /**
     * Store file temporary in the file system by original name
     *
     * Used for email attachments
     * File is deleted by the end of execution
     *
     * @access public
     * @param array $file_data
     * @return mixed
     */
    public static function get_temporary_file($file_data)
    {
        // Check if file data looks good
        if (empty($file_data['subdirectory']) || empty($file_data['storage_key']) || empty($file_data['name'])) {
            return false;
        }

        // Get directory paths
        $files_directory_path = WCCF_Files::get_files_directory($file_data['subdirectory']);
        $temp_directory_path = WCCF_Files::get_temp_directory(RightPress_Helper::get_hash());

        // Check if file exists in file system and directories are present
        if (!WCCF_Files::is_available($file_data['subdirectory'], $file_data['storage_key']) || !$files_directory_path || !$temp_directory_path) {
            return false;
        }

        // Get file paths
        $file_path = $files_directory_path . '/' . $file_data['storage_key'];

        // Get temporary file path
        $temp_file_path = $temp_directory_path . '/' . $file_data['name'];

        // Attempt to store file
        if (!file_put_contents($temp_file_path, file_get_contents($file_path))) {
            return false;
        }

        // Remove temporary directory by the end of this request
        register_shutdown_function(array('WCCF_Files', 'remove_temporary_directory'), $temp_directory_path);

        // Return temporary file path to use
        return $temp_file_path;
    }

    /**
     * Remove temporary directory
     *
     * @access public
     * @param string $directory_path
     * @return void
     */
    public static function remove_temporary_directory($directory_path)
    {
        array_map('unlink', glob("$directory_path/*.*"));
        rmdir($directory_path);
    }

    /**
     * Check if current user can access file
     *
     * @access public
     * @param string $access_key
     * @param bool $redirect_to_login
     * @return bool
     */
    public static function current_user_can_access($access_key, $redirect_to_login = true)
    {
        // Admin can access all files
        if (WCCF::is_authorized('download_all_files')) {
            return true;
        }

        // Any user can access files that are referenced in their session data
        if ($session = WCCF_WC_Session::initialize_session()) {
            if ($session->get(WCCF_Field::get_temp_file_data_access_key($access_key))) {
                return true;
            }
        }

        // Get file data from meta
        $data_access_key = WCCF_Field::get_file_data_access_key($access_key);
        $extended_file_data = RightPress_Helper::get_meta_row($data_access_key);

        // Check if file data was found
        if (!$extended_file_data) {
            return false;
        }

        // Get item id
        if (isset($extended_file_data['user_id'])) {
            $item_id = $extended_file_data['user_id'];
        }
        else if (isset($extended_file_data['post_id'])) {
            $item_id = $extended_file_data['post_id'];
        }
        else if (isset($extended_file_data['order_item_id'])) {
            $item_id = $extended_file_data['order_item_id'];
        }
        else {
            return false;
        }

        // Get field data
        $field_data = maybe_unserialize($extended_file_data['meta_value']);

        // Get field id
        $field_id = !empty($field_data['field_id']) ? $field_data['field_id'] : null;

        // Load field
        $field = WCCF_Field_Controller::get($field_id);

        // Check if field exists
        if (!$field) {
            return false;
        }

        // Product Property
        if ($field->context_is('product_prop') && $field->is_public()) {
            return true;
        }

        // Get user id
        $user_id = get_current_user_id();

        // User is not logged in
        if (!$user_id) {

            // Redirect to login page
            if ($redirect_to_login) {
                $current_url = RightPress_Helper::get_request_url();
                $redirect_url = wp_login_url($current_url);
                wp_redirect($redirect_url);
                exit;
            }

            return false;
        }

        // Product Field
        if ($field->context_is('product_field') && RightPress_Helper::user_owns_wc_order_item($user_id, $item_id)) {
            return true;
        }

        // Checkout Field
        if ($field->context_is('checkout_field') && RightPress_Helper::user_owns_wc_order($user_id, $item_id)) {
            return true;
        }

        // Order Field
        if ($field->context_is('order_field') && RightPress_Helper::user_owns_wc_order($user_id, $item_id) && $field->is_public()) {
            return true;
        }

        // User Field
        if ($field->context_is('user_field') && (int) $item_id === (int) $user_id) {
            return true;
        }

        // No reason to grant access
        return false;
    }

    /**
     * Delete file by access key
     *
     * @access public
     * @param string $access_key
     * @param mixed $item
     * @param object $field
     * @return void
     */
    public static function delete_by_access_key($access_key, $item = null, $field = null)
    {
        // Check if current user has access to file
        if (!WCCF_Files::current_user_can_access($access_key)) {
            return;
        }

        // Get file data
        $item_id = is_object($item) ? $item->get_id() : $item;
        $file_data = WCCF_Files::get_data_by_access_key($access_key, $item_id);

        // Check if file data was found
        if (!$file_data) {
            return;
        }

        // Delete file data from meta
        if ($item !== null && $field !== null) {
            $meta_access_key = WCCF_Field::get_file_data_access_key($access_key);
            $field->delete_data($item, $meta_access_key);
        }

        // Only delete actual file if it's not used anywhere else
        if (WCCF_Files::get_data_by_access_key($access_key)) {
            return;
        }

        // Delete actual file
        if ($file_path = WCCF_Files::locate_file($file_data['subdirectory'], $file_data['storage_key'])) {
            unlink($file_path);
        }
    }

    /**
     * Handle file upload
     * Only one file for one field can be uploaded in on request
     *
     * @access public
     * @return void
     */
    public function ajax_file_upload()
    {
        define('WCCF_UPLOADING_FILE', true);

        try {

            // Check if any files were passed in
            if (empty($_FILES['wccf_ignore']['name'])) {
                throw new Exception();
            }

            // Iterate over data to get field context and field id
            foreach ($_FILES['wccf_ignore']['name'] as $context => $data) {
                foreach ($data as $field_id => $file_name) {

                    // Get quantity index
                    $quantity_index = WCCF_Field_Controller::get_quantity_index_from_field_id($field_id);
                    $quantity = ($quantity_index ? ($quantity_index + 1) : 1);

                    // Get clean field id
                    if ($quantity_index) {
                        $field_id = WCCF_Field_Controller::clean_field_id($field_id);
                    }

                    // Ensure field id is integer
                    $field_id = (int) $field_id;

                    // Get field id by which we retrieve field data
                    $field_id_for_name = $quantity_index ? ($field_id . '_' . $quantity_index) : $field_id;

                    break;
                }
            }

            // Load field
            $field = WCCF_Field_Controller::get($field_id, 'wccf_' . $context);

            // Check if field was loaded
            if (!$field) {
                throw new Exception();
            }

            // Check if current user can upload file to current object
            if (!empty($_POST['item_id']) && !WCCF::is_authorized('upload_file', array('item_id' => $_POST['item_id'], 'context' => $context))) {
                throw new Exception();
            }

            // Create fields array
            $fields = array(
                $field_id => $field,
            );

            // Move values inside $_FILES (allowing ourselves to do that since we will be exit'ing soon anyway)
            $_FILES['wccf'] = $_FILES['wccf_ignore'];
            unset($_FILES['wccf_ignore']);

            // Extract posted field values by fields
            $values = WCCF_Field_Controller::extract_posted_values($context, array(
                'fields'            => $fields,
                'quantity'          => $quantity,
                'quantity_index'    => $quantity_index,
            ));

            // Sanitize field values and store file
            $sanitized_values = WCCF_Field_Controller::sanitize_field_values($values, array(
                'fields'                    => $fields,
                'quantity'                  => $quantity,
                'quantity_index'            => $quantity_index,
                'skip_frontend_validation'  => true,
                'item_id'                   => !empty($_POST['item_id']) ? $_POST['item_id'] : (($context === 'user_field' && is_user_logged_in()) ? get_current_user_id() : null),
            ));

            // Check if file has been stored
            if (empty($sanitized_values) || empty($sanitized_values[$field_id_for_name]['value'][0])) {
                throw new Exception();
            }

            // Get access key and file data
            $access_key = $sanitized_values[$field_id_for_name]['value'][0];
            $file_data = $sanitized_values[$field_id_for_name]['files'][$access_key];

            // Get meta access key
            $meta_access_key = WCCF_Field::get_temp_file_data_access_key($access_key);

            // All backend file data must be stored in meta until form is submitted
            if (!empty($_POST['item_id'])) {

                // Order item files are temporarily stored under order meta
                if ($field->context_is('product_field')) {
                    RightPress_WC_Meta::order_update_meta_data($_POST['item_id'], $meta_access_key, $file_data);
                }
                else {
                    $field->update_data($_POST['item_id'], $meta_access_key, $file_data);
                }
            }
            else {

                // All frontend file data must be stored in WC session until form is submitted
                $session = WCCF_WC_Session::initialize_session();

                if ($session) {
                    $session->set($meta_access_key, $file_data, $field->accepts_multiple_values());
                }

                // Prevent WooCommerce from overwriting our changes in case we need to write to session by ourselves
                if ($field->accepts_multiple_values()) {
                    remove_action('shutdown', array(WC()->session, 'save_data'), 20);
                }
            }

            // File stored successfully
            echo json_encode(array(
                'result'        => 'success',
                'access_key'    => $access_key,
            ));
            exit;
        }
        // Error occurred
        catch (Exception $e) {

            // Prepare response
            $response = array(
                'result' => 'error',
            );

            // Add error message if present
            if ($e->getMessage()) {
                $response['error_message'] = $e->getMessage();
            }

            // Send response
            echo json_encode($response);
            exit;
        }
    }

    /**
     * Remove uploaded file
     *
     * Note: currently this method is designed to remove file reference from
     * user session plus the file itself. If it's ever going to be used to
     * delete files from post meta, we must add proper authorization checks
     *
     * @access public
     * @return void
     */
    public function ajax_remove_file()
    {
        // Required properties not set
        if (empty($_REQUEST['access_key']) || empty($_REQUEST['field_id'])) {
            exit;
        }

        // Load field
        $field = WCCF_Field_Controller::cache($_REQUEST['field_id']);

        // No such field
        if (!$field) {
            exit;
        }

        // Get field context
        $context = $field->get_context();

        // Get temp file data access key
        $access_key = $_REQUEST['access_key'];
        $temp_file_access_key = WCCF_Field::get_temp_file_data_access_key($access_key);

        // Frontend - file reference stored in session
        if (RightPress_Helper::is_request('frontend')) {

            // Load session object
            if ($session = WCCF_WC_Session::initialize_session()) {

                // Get file data from session
                if ($file_data = $session->get($temp_file_access_key, false)) {

                    // Delete actual file
                    WCCF_Files::delete_by_access_key($access_key);

                    // Unset from session
                    unset($session->$temp_file_access_key);
                    $session->save_data();
                }
            }
        }

        exit;
    }

    /**
     * Move files from temporary directory to permanent one
     *
     * @access public
     * @param string $subdirectory
     * @param string $storage_key
     * @return void
     */
    public static function move_to_permanent($subdirectory, $storage_key)
    {
        // Get temporary location
        $temporary_location = WCCF_Files::get_temp_directory($subdirectory) . '/' . $storage_key;

        // Check if file is still in temporary location
        if (file_exists($temporary_location)) {

            // Move file to permanent location
            rename($temporary_location, WCCF_Files::get_files_directory($subdirectory) . '/' . $storage_key);
        }
    }

    /**
     * Search for file in both temporary and permanent locations
     *
     * @access public
     * @param string $subdirectory
     * @param string $storage_key
     * @return mixed
     */
    public static function locate_file($subdirectory, $storage_key)
    {
        // Get permanent location
        $permanent_location = WCCF_Files::get_files_directory($subdirectory) . '/' . $storage_key;

        // File exists in permanent location
        if (file_exists($permanent_location)) {
            return $permanent_location;
        }

        // Get temporary location
        $temporary_location = WCCF_Files::get_temp_directory($subdirectory) . '/' . $storage_key;

        // File exists in temporary location
        if (file_exists($temporary_location)) {
            return $temporary_location;
        }

        // Unable to locate file
        return false;
    }





}

WCCF_Files::get_instance();

}
