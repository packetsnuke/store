<?php
global $sr_base_name, $sr_check_update_timeout, $sr_plugin_data, $sr_sku, $sr_license_key, $sr_download_url, $sr_installed_version, $sr_live_version;

$sr_sku = 'sr';

if (! function_exists( 'get_plugin_data' )) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$sr_base_name = SR_PLUGIN_FILE;
$sr_plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . SR_PLUGIN_FILE );

add_site_option( 'sr_license_key', '' );
add_site_option( 'sr_download_url', '' );
add_site_option( 'sr_installed_version', '' );
add_site_option( 'sr_live_version', '' );
add_site_option( 'sr_due_date', '' );
add_site_option( 'sr_login_link', '' );

$sr_check_update_timeout = (24 * 60 * 60); // timeout for making request to StoreApps

if ( get_site_option( 'sr_installed_version' ) != $sr_plugin_data ['Version'] ) {
    update_site_option( 'sr_installed_version', $sr_plugin_data ['Version'] );
}

if ( ( get_site_option( 'sr_live_version' ) == '' ) || ( get_site_option( 'sr_live_version' ) < get_site_option( 'sr_installed_version' ) ) ) {
    update_site_option( 'sr_live_version', $sr_plugin_data['Version'] );
}

if ( empty( $sr_license_key ) ) {
    $sr_stored_license_key = sr_get_license_key();
    $sr_license_key = ( !empty( $sr_stored_license_key ) ) ? $sr_stored_license_key : get_site_option( 'sr_license_key' );
}

// Actions for License Validation & Upgrade process
add_action( 'admin_footer', 'sr_support_ticket_content' );
add_action( "after_plugin_row_".$sr_base_name, 'sr_update_row', 10, 2 );
add_action( 'wp_ajax_sr_validate_license_key', 'sr_validate_license_key' );

// Filters for pushing Smart Reporter plugin details in Plugins API of WP
add_filter( 'site_transient_update_plugins', 'sr_overwrite_site_transient', 10, 2 );

function sr_check_for_updates() {

    global $sr_sku;

    $sr_license_key = get_site_option( 'sr_license_key');

    $license_query = ( !empty( $sr_license_key ) ) ? '&serial=' . $sr_license_key : '';

    $result = wp_remote_post( 'http://www.storeapps.org/wp-admin/admin-ajax.php?action=get_products_latest_version&sku=' . $sr_sku . $license_query . '&uuid=' . urlencode( admin_url( '/' ) ) );
    
    if (is_wp_error($result)) {
        return;
    }
    
    $response = json_decode( $result ['body'] );
    
    update_site_option( 'sr_login_link', $response->link );
    update_site_option( 'sr_due_date', $response->due_date );
    
}

function sr_overwrite_site_transient($plugin_info, $force_check_updates = false) {
    global $sr_base_name, $sr_check_update_timeout, $sr_plugin_data, $sr_sku, $sr_license_key, $sr_download_url, $sr_installed_version, $sr_live_version;
    if ( !isset( $plugin_info->response ) || empty( $plugin_info->response ) || empty( $plugin_info->response[$sr_base_name] ) || count( $plugin_info->response ) <= 0 ) return $plugin_info;

    if ( empty( $plugin_info->response [$sr_base_name]->package ) || strpos( $plugin_info->response [$sr_base_name]->package, 'downloads.wordpress.org' ) > 0 ) {
        $plugin_info->response [$sr_base_name]->package = get_site_option('sr_download_url');
    }

    if (empty( $plugin_info->checked ))
        return $plugin_info;

    $time_not_changed = isset( $plugin_info->last_checked ) && $sr_check_update_timeout > ( time() - $plugin_info->last_checked );

    if ( $force_check_updates || !$time_not_changed ) {
        sr_check_for_updates();
    }

    return $plugin_info;
}


function sr_validate_license_key() {
    global $sr_base_name, $sr_check_update_timeout, $sr_plugin_data, $sr_sku, $sr_license_key, $sr_download_url, $sr_installed_version, $sr_live_version;
    $sr_license_key = (isset($_REQUEST ['license_key']) && !empty($_REQUEST ['license_key'])) ? $_REQUEST ['license_key'] : '';
    $storeapps_validation_url = 'http://www.storeapps.org/wp-admin/admin-ajax.php?action=woocommerce_validate_serial_key&serial=' . urlencode($sr_license_key) . '&is_download=true&sku=' . $sr_sku;
    $resp_type = array('headers' => array('content-type' => 'application/text'));
    $response_info = wp_remote_post($storeapps_validation_url, $resp_type); //return WP_Error on response failure

    if (is_array($response_info)) {
        $response_code = wp_remote_retrieve_response_code($response_info);
        $response_msg = wp_remote_retrieve_response_message($response_info);

        // if ($response_code == 200 && $response_msg == 'OK') {
        if ($response_code == 200) {
            $storeapps_response = wp_remote_retrieve_body($response_info);
            $decoded_response = json_decode($storeapps_response);
            if ($decoded_response->is_valid == 1) {
                update_site_option('sr_license_key', $sr_license_key);
                update_site_option('sr_download_url', $decoded_response->download_url);
            } else {
                sr_remove_license_download_url();
            }
            echo $storeapps_response;
            exit();
        }
        sr_remove_license_download_url();
        echo json_encode(array('is_valid' => 0));
        exit();
    }
    sr_remove_license_download_url();
    echo json_encode(array('is_valid' => 0));
    exit();
}


function sr_remove_license_download_url() {
    update_site_option('sr_license_key', '');
    update_site_option('sr_download_url', '');
}


function sr_update_row($file, $sr_plugin_data) {
    global $sr_base_name, $sr_check_update_timeout, $sr_plugin_data, $sr_sku, $sr_license_key, $sr_download_url, $sr_installed_version, $sr_live_version;
    $sr_license_key = get_site_option('sr_license_key');
    $valid_color = '#AAFFAA';
    $invalid_color = '#FFAAAA';
    $color = ($sr_license_key != '') ? $valid_color : $invalid_color;
?>
    <style type="text/css">
        div#TB_window {
            background: lightgrey;
        }
        <?php if ( version_compare( get_bloginfo( 'version' ), '3.7.1', '>' ) ) { ?>
            tr.sr_license_key .key-icon-column:before {
                content: "\f112";
                display: inline-block;
                -webkit-font-smoothing: antialiased;
                font: normal 1.5em/1 'dashicons';
            }
            tr.sr_due_date .renew-icon-column:before {
                content: "\f463";
                display: inline-block;
                -webkit-font-smoothing: antialiased;
                font: normal 1.5em/1 'dashicons';
            }
        <?php } ?>
    </style>
    <script type="text/javascript">
        
        jQuery(function(){
            jQuery('input#sr_validate_license_button').click(function(){
                jQuery('img#sr_license_validity_image').show();
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        'action': 'sr_validate_license_key',
                        'license_key': jQuery('input#sr_license_key').val()
                    },
                    success: function( response ) {
                        if ( response.is_valid == 1 ) {
                            jQuery('tr.sr_license_key').css('background', '<?php echo $valid_color; ?>');
                            jQuery('#sr_license_key').hide();
                        } else {
                            jQuery('tr.sr_license_key').css('background', '<?php echo $invalid_color; ?>');
                            jQuery('input#sr_license_key').val('');
                        }
                        location.reload();
                    }
                });
            });

            jQuery(document).ready(function(){
                <?php if ( version_compare( get_bloginfo( 'version' ), '3.7.1', '>' ) ) { ?>
                    jQuery('tr.sr_license_key .key-icon-column').css( 'border-left', jQuery('tr.sr_license_key').prev().prev().prev().find('th.check-column').css( 'border-left' ) );
                    jQuery('tr.sr_due_date .renew-icon-column').css( 'border-left', jQuery('tr.sr_license_key').prev().prev().prev().find('th.check-column').css( 'border-left' ) );
                <?php } ?>
            });
        });
    </script>
    <?php if ($sr_license_key == '') { ?>
    <!--<tr class="sr_license_key" style="background: <?php echo $color; ?>">
        <td class="key-icon-column" style="vertical-align: middle;"></td>
        <td style="vertical-align: middle;"><label for="sr_license_key"><strong><?php _e('License Key', 'smart-reporter'); ?></strong></label></td>
        <td style="vertical-align: middle;">
            <input type="text" id="sr_license_key" name="sr_license_key" value="<?php echo ( ( $sr_license_key != '' ) ? $sr_license_key : '' ); ?>" size="50" style="text-align: center;" />
            <input type="button" class="button" id="sr_validate_license_button" name="sr_validate_license_button" value="<?php _e('Validate', 'smart-reporter'); ?>" />
            <img id="sr_license_validity_image" src="<?php echo esc_url(admin_url('images/wpspin_light.gif')); ?>" style="display: none; vertical-align: middle;" />
        </td>
    </tr>-->
    <?php
    }
    
    $sr_due_date = get_site_option( 'sr_due_date' );
    $sr_login_link = get_site_option( 'sr_login_link' );

    if ( !empty( $sr_due_date ) ) {
        $start = strtotime( $sr_due_date . ' -30 days' );
        $due_date = strtotime( $sr_due_date );
        $now = time();
        if ( $now >= $start ) {
            $remaining_days = round( abs( $due_date - $now )/60/60/24 );
            if ( $now > $due_date ) {
                $extended_text = __( 'has expired', 'smart_reporter' );
            } else {
                $extended_text = sprintf(__( 'will expire in %d %s', 'smart_reporter' ), $remaining_days, _n( 'day', 'days', $remaining_days, 'smart_reporter' ) );
            }
            ?>
                <tr class="sr_due_date" style="background: #FFAAAA;">
                    <td class="renew-icon-column" style="vertical-align: middle;"></td>
                    <td style="vertical-align: middle;" colspan="2">
                        <?php echo sprintf(__( 'Your license for %s %s. To continue receiving updates & support, please %s', 'smart_reporter' ), 'Smart Reporter', '<strong>' . $extended_text . '</strong>', '<a href="' . $sr_login_link . '" target="_blank">' . __( 'renew your license now', 'smart_reporter' ) . ' &rarr;</a>'); ?>
                    </td>
                </tr>
            <?php
        }
    }

}


function sr_support_ticket_content() {

    global $current_user, $wpdb, $woocommerce, $pagenow;
    global $sr_base_name, $sr_check_update_timeout, $sr_plugin_data, $sr_sku, $sr_license_key, $sr_download_url, $sr_installed_version, $sr_live_version;

    $hide_page = "";
    
    if ((current_user_can( 'edit_pages' ) && is_plugin_active ( 'woocommerce/woocommerce.php' ))
        || (current_user_can( 'edit_posts' ) && is_plugin_active ( 'wp-e-commerce/wp-shopping-cart.php' ))) {
        if ( ($pagenow != 'admin.php' && is_plugin_active ( 'woocommerce/woocommerce.php' ))||
             ($pagenow != 'edit.php' && is_plugin_active ( 'wp-e-commerce/wp-shopping-cart.php' ))    ) return;
        
        if (is_plugin_active ( 'woocommerce/woocommerce.php' )) {
            $hide_page = "admin.php";    
        }
        else if(is_plugin_active ( 'wp-e-commerce/wp-shopping-cart.php' )) {
            $hide_page = "edit.php";       
        }
    }
    else {
        if (is_plugin_active ( 'woocommerce/woocommerce.php' )) {
            $hide_page = "admin.php";    
        }
        else if(is_plugin_active ( 'wp-e-commerce/wp-shopping-cart.php' )) {
            $hide_page = "edit.php";       
        }
    }
    
    if ( !( $current_user instanceof WP_User ) ) return;

    if ( isset( $_GET['result'] ) && isset( $_GET['plugin'] ) && $_GET['plugin'] == 'sr' ) {
        if ( $_GET['result'] == 'success' ) {
            
            if (is_plugin_active ( 'wp-e-commerce/wp-shopping-cart.php' )) {
                $plug_page = 'wpsc';
                $type = 'wpsc-product';
            }
            elseif (is_plugin_active ( 'woocommerce/woocommerce.php' )) {
                $plug_page = 'woo';
                $type = 'product';
            }
            
            ?>
                <div id="message" class="updated fade">
                    <script type="text/javascript"> 
                        var pathname = window.location.pathname;
                    </script>
                    
                    <p><?php _e('Support query has been successfully submitted', 'smart-reporter'); ?>. <a href="http://members.appsmagnet.com/viewticket.php?tid=<?php echo $_GET['tid']; ?>&c=<?php echo $_GET['c']; ?>" target="_blank"><?php _e('View sent query'); ?></a>
                        <a href="<?php echo $hide_page ?>?post_type=<?php echo $type ?>&amp;page=smart-reporter-<?php echo $plug_page ?>" style="float:right"> <?php _e('Hide this Message') ?></a>
                    </p>
                </div>
            <?php
        } else {
            ?>
                <div id="notice" class="error">
                    <p><?php _e('Query submission failed', 'smart-reporter'); ?>. <?php _e('Reason: ' . $_GET['message'], 'smart-reporter'); ?></p>
                </div>
            <?php
        }
    }
    ?>
    <div id="sr_post_query_form" style="display: none;">
        <style>
            table#sr_post_query_table {
                padding: 5px;
            }
            table#sr_post_query_table tr td {
                padding: 5px;
            }
            input.sr_text_field {
                padding: 5px;
            }
            label {
                font-weight: bold;
            }
        </style>
        <?php
            if ( !wp_script_is('jquery') ) {
                wp_enqueue_script('jquery');
                wp_enqueue_style('jquery');
            }

            $first_name = get_user_meta($current_user->ID, 'first_name', true);
            $last_name = get_user_meta($current_user->ID, 'last_name', true);
            $name = $first_name . ' ' . $last_name;
            $customer_name = ( !empty( $name ) ) ? $name : $current_user->data->display_name;
            $customer_email = $current_user->data->user_email;
            $ecom_plugin_version = '';

            if ( isset( $_GET['post_type'] ) && !empty( $_GET['post_type'] ) ) {
                switch ( $_GET['post_type'] ) {
                    case 'wpsc-product':
                        $ecom_plugin_version = 'WPeC ' . ( defined( 'WPSC_VERSION' ) ? WPSC_VERSION : '' );
                        break;
                    case 'product':
                        $ecom_plugin_version = 'WooCommerce ' . ( ( defined( 'WOOCOMMERCE_VERSION' ) ) ? WOOCOMMERCE_VERSION : $woocommerce->version );
                        break;
                    default:
                        $ecom_plugin_version = '';
                        break;
                }
            }
            
            $wp_version = ( is_multisite() ) ? 'WPMU ' . get_bloginfo('version') : 'WP ' . get_bloginfo('version');
            $admin_url = admin_url();
            $php_version = ( function_exists( 'phpversion' ) ) ? phpversion() : '';
            // $wp_max_upload_size = wp_convert_bytes_to_hr( wp_max_upload_size() );
            $wp_max_upload_size = size_format( wp_max_upload_size() );
            $server_max_upload_size = ini_get('upload_max_filesize');
            $server_post_max_size = ini_get('post_max_size');
            $wp_memory_limit = WP_MEMORY_LIMIT;
            $wp_debug = ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) ? 'On' : 'Off';
            $this_plugins_version = $sr_plugin_data['Name'] . ' ' . $sr_plugin_data['Version'];
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $additional_information = "===== Additional Information =====
                                       [E-Commerce Plugin: $ecom_plugin_version] =====
                                       [WP Version: $wp_version] =====
                                       [Admin URL: $admin_url] =====
                                       [PHP Version: $php_version] =====
                                       [WP Max Upload Size: $wp_max_upload_size] =====
                                       [Server Max Upload Size: $server_max_upload_size] =====
                                       [Server Post Max Size: $server_post_max_size] =====
                                       [WP Memory Limit: $wp_memory_limit] =====
                                       [WP Debug: $wp_debug] =====
                                       [" . $sr_plugin_data['Name'] . " Version: $this_plugins_version] =====
                                       [License Key: $sr_license_key]=====
                                       [IP Address: $ip_address] =====
                                      ";



                            if( isset( $_POST['submit_query'] ) && $_POST['submit_query'] == "Send" ){


                                // wp_mail( 'support@storeapps.org', 'subject', 'message' );
                               $additional_info = ( isset( $_POST['additional_information'] ) && !empty( $_POST['additional_information'] ) ) ? woocommerce_clean( $_POST['additional_information'] ) : '';
                               $additional_info = str_replace( '=====', '<br />', $additional_info );
                               $additional_info = str_replace( array( '[', ']' ), '', $additional_info );

                               $headers = 'From: ';
                               $headers .= ( isset( $_POST['client_name'] ) && !empty( $_POST['client_name'] ) ) ? woocommerce_clean( $_POST['client_name'] ) : '';
                               $headers .= ' <' . woocommerce_clean( $_POST['client_email'] ) . '>' . "\r\n";
                               $headers .= 'MIME-Version: 1.0' . "\r\n";
                               $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

                               ob_start();
                               echo $additional_info . '<br /><br />';
                               // echo woocommerce_clean( nl2br($_POST['message']) );
                               echo nl2br($_POST['message']) ;
                               $message = ob_get_clean();
                               wp_mail( 'support@storeapps.org', $_POST['subject'], $message, $headers );
                               header('Location: ' . $_SERVER['HTTP_REFERER'] ); 
                            } 

        ?>
        <!-- <form id="sr_form_post_query" method="POST" action="http://www.storeapps.org/api/supportticket.php" enctype="multipart/form-data"> -->
		
        <form id="sr_form_post_query" method="POST" action="" enctype="multipart/form-data">
            <script type="text/javascript">
                jQuery(function(){

                    //Code for handling the sizing of the thickbox w.r.to. Window size

                    jQuery(document).ready(function(){

                        var width = jQuery(window).width();
                        var H = jQuery(window).height();
                        var W = ( 720 < width ) ? 720 : width;

                        var adminbar_height = 0;

                        if ( jQuery('body.admin-bar').length )
                            adminbar_height = 28;

                        jQuery("#TB_window").css({"max-height": 390 +'px'});

                        ajaxContentW = W - 110;
                        ajaxContentH = H - 130 - adminbar_height;
                        jQuery("#TB_ajaxContent").css({"width": ajaxContentW +'px', "height": ajaxContentH +'px'});

                    });
                
                    jQuery(window).resize(function(){

                        var width = jQuery(window).width();
                        var H = jQuery(window).height();
                        var W = ( 720 < width ) ? 720 : width;

                        var adminbar_height = 0;

                        if ( jQuery('body.admin-bar').length )
                            adminbar_height = 28;

                        jQuery('#TB_window').css('margin-top', '');
                        jQuery("#TB_window").css({"max-height": 390 +'px',"top":48 +'px'});


                        ajaxContentW = W - 110;
                        ajaxContentH = H - 130 - adminbar_height;
                        jQuery("#TB_ajaxContent").css({"width": ajaxContentW +'px', "height": ajaxContentH +'px'});

                    });
           
                    jQuery('input#sr_submit_query').click(function(e){
                        var error = false;

                        var client_name = jQuery('input#client_name').val();
                        if ( client_name == '' ) {
                            jQuery('input#client_name').css('border-color', 'red');
                            error = true;
                        } else {
                            jQuery('input#client_name').css('border-color', '');
                        }

                        var client_email = jQuery('input#client_email').val();
                        if ( client_email == '' ) {
                            jQuery('input#client_email').css('border-color', 'red');
                            error = true;
                        } else {
                            jQuery('input#client_email').css('border-color', '');
                        }

                        var message = jQuery('table#sr_post_query_table textarea#message').val();

                        if ( message == '' ) {
                            jQuery('textarea#message').css('border-color', 'red');
                            error = true;
                        } else {
                            jQuery('textarea#message').css('border-color', '');
                        }

                        var subject = jQuery('table#sr_post_query_table input#subject').val();
                        if ( subject == '' ) {
                            var msg_len = message.length;
                            
                            if (msg_len <= 50) {
                                subject = message;
                            }
                            else
                            {
                                subject = message.substr(0,50) + '...';
                            }
                            
                            jQuery('input#subject').val(subject);
                            
                        } else {
                           jQuery('input#subject').css('border-color', '');
                        }

                        if ( error == true ) {
                            jQuery('label#error_message').text('* All fields are compulsory.');
                            e.preventDefault();
                        } else {
                            jQuery('label#error_message').text('');
                        }

                    });

                    jQuery('input,textarea').keyup(function(){
                        var value = jQuery(this).val();
                        if ( value.length > 0 ) {
                            jQuery(this).css('border-color', '');
                            jQuery('label#error_message').text('');
                        }
                    });

                });
            </script>
            <table id="sr_post_query_table">
                <tr>
                    <td><label for="client_name"><?php _e('Name', 'smart-reporter'); ?>*</label></td>
                    <td><input type="text" class="regular-text sr_text_field" id="client_name" name="client_name" value="<?php echo $customer_name; ?>" autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;" /></td>
                </tr>
                <tr>
                    <td><label for="client_email"><?php _e('E-mail', 'smart-reporter'); ?>*</label></td>
                    <td><input type="email" class="regular-text sr_text_field" id="client_email" name="client_email" value="<?php echo $customer_email; ?>" autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;" /></td>
                </tr>
                <tr>
                    <td><label for="subject"><?php _e('Subject', 'smart-reporter'); ?></label></td>
                    <td><input type="text" class="regular-text sr_text_field" id="subject" name="subject" value="<?php echo ( !empty( $subject ) ) ? $subject : ''; ?>" autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;" /></td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding-top: 12px;"><label for="message"><?php _e('Message', 'smart-reporter'); ?>*</label></td>
                    <td><textarea id="message" name="message" rows="10" cols="60" autocomplete="off" oncopy="return false;" onpaste="return false;" oncut="return false;"><?php echo ( !empty( $message ) ) ? $message : ''; ?></textarea></td>
                </tr>
                <tr>
                    <td></td>
                    <td><label id="error_message" style="color: red;"></label></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" class="button" id="sr_submit_query" name="submit_query" value="Send" /></td>
                </tr>
            </table>
            <input type="hidden" name="license_key" value="<?php echo $sr_license_key; ?>" />
            <input type="hidden" name="sku" value="<?php echo $sr_sku; ?>" />
            <input type="hidden" class="hidden_field" name="ecom_plugin_version" value="<?php echo $ecom_plugin_version; ?>" />
            <input type="hidden" class="hidden_field" name="wp_version" value="<?php echo $wp_version; ?>" />
            <input type="hidden" class="hidden_field" name="admin_url" value="<?php echo $admin_url; ?>" />
            <input type="hidden" class="hidden_field" name="php_version" value="<?php echo $php_version; ?>" />
            <input type="hidden" class="hidden_field" name="wp_max_upload_size" value="<?php echo $wp_max_upload_size; ?>" />
            <input type="hidden" class="hidden_field" name="server_max_upload_size" value="<?php echo $server_max_upload_size; ?>" />
            <input type="hidden" class="hidden_field" name="server_post_max_size" value="<?php echo $server_post_max_size; ?>" />
            <input type="hidden" class="hidden_field" name="wp_memory_limit" value="<?php echo $wp_memory_limit; ?>" />
            <input type="hidden" class="hidden_field" name="wp_debug" value="<?php echo $wp_debug; ?>" />
            <input type="hidden" class="hidden_field" name="current_plugin" value="<?php echo $this_plugins_version; ?>" />
            <input type="hidden" class="hidden_field" name="ip_address" value="<?php echo $ip_address; ?>" />
            <input type="hidden" class="hidden_field" name="additional_information" value='<?php echo $additional_information; ?>' />
        </form>
    </div>
    <?php
}



function sr_settings_page($plugin_name) {
    global $sr_download_url, $wpdb;
    
    $is_pro_updated = is_pro_updated ();
    $license_key = sr_get_license_key();
    if (isset ( $_POST ['submit'] )) {
        $latest_version = get_latest_version ($plugin_name);
        $license_key = $wpdb->_real_escape( $_POST ['license_key'] );
        $sr_dir = dirname($plugin_name);
        $sr_post_url = STORE_APPS_URL . 'wp-admin/admin-ajax.php?action=woocommerce_validate_serial_key&serial=' . urlencode($license_key) . '&sku=sr';                
        $sr_response_result = smart_get_sr_response ( $sr_post_url );   
        if ($license_key != '') {
            if ($sr_response_result->is_valid == 1) {
                if ( is_multisite() ) {
                    $delete_query = "DELETE FROM $wpdb->sitemeta WHERE meta_key = 'sr_license_key'";
                    $wpdb->query ( $delete_query );
                    $query  = "REPLACE INTO $wpdb->sitemeta (`meta_key`,`meta_value`) VALUES('sr_license_key','$license_key')";
                } else {
                    $query  = "REPLACE INTO `{$wpdb->prefix}options`(`option_name`,`option_value`) VALUES('sr_license_key','$license_key')";
                }
                $result = $wpdb->query ( $query );
                $msg    = 'Your key is valid. Automatic Upgrades and support are now activated.';
                sr_display_notice ( $msg );
            } else {
                sr_display_err( $sr_response_result->msg );
            }
        } else {
            $msg = 'Please enter license key';
            sr_display_err ( $msg );
        }
    }
    ?>
</br>
<form method="post" action="">
<div class="wrap">
<div id="icon-smart-reporter" class="icon32"><br/></div>
<h2>Smart Reporter Pro Settings</h2>
<!-- Your Smart Reporter Pro license key is used to verify your support
package, enable automatic updates and receive support. --></div>
<br />
<div id="sr_auto_refresh_setting">
    <script type="text/javascript">
        jQuery(function(){
            jQuery('#sr_set_auto_refresh').click(function(){

                var regex_duration = /^\d*\.?\d+$/;
                var sr_refresh_duration = Number(jQuery('input[name=sr_refresh_duration]').val());
                var error = false;
                if ( ! regex_duration.test( sr_refresh_duration ) || sr_refresh_duration < 1 ) {
                    jQuery('input[name=sr_refresh_duration]').css('border-color', 'red');
                    jQuery('#sr_save_refresh_setting_result').css('color', 'red');
                    jQuery('#sr_save_refresh_setting_result').text('<?php _e('Invalid value'); ?>');
                    error = true;
                } else {
                    jQuery('input[name=sr_refresh_duration]').css('border-color', '');
                    jQuery('#sr_save_refresh_setting_result').css('color', '');
                    jQuery('#sr_save_refresh_setting_result').text('');
                    error = false;
                }
                
                if ( !error ) {



                    // jQuery('img#sr_update_progress').show();
                    jQuery.ajax({
                        type: 'POST',
                        // url: "<?php echo WP_PLUGIN_URL.'/smart-reporter-for-wp-e-commerce/pro/upgrade.php'; ?>",
                        url: ajaxurl + "?action=sr_save_settings",
                        data: {
                            sr_is_auto_refresh:             jQuery('input#sr_is_auto_refresh').is(':checked'),
                            sr_what_to_refresh:             jQuery('select#sr_what_to_refresh').val(),
                            sr_refresh_duration:            jQuery('input#sr_refresh_duration').val(),
                            sr_send_summary_mails:          jQuery('input#sr_send_summary_mails').is(':checked'),
                            sr_summary_mail_interval:       jQuery('select#sr_summary_mail_interval').val(),
                            sr_summary_week_start_day:      jQuery('select#sr_summary_week_start_day').val(),
                            sr_summary_month_start_day:     jQuery('select#sr_summary_month_start_day').val(),
                            sr_send_summary_mails_email:    jQuery('input#sr_send_summary_mails_email').val()
                        },
                        success: function( response ) {
                            $("#sr_settings_updated_message").show();
                        }
                    });
                }
            });
            
            jQuery('#sr_is_auto_refresh').click(function(){
                jQuery('#sr_what_to_refresh').attr('disabled', !jQuery('#sr_what_to_refresh').attr('disabled'));
                jQuery('#sr_refresh_duration').attr('disabled', !jQuery('#sr_refresh_duration').attr('disabled'));
            });

            jQuery('#sr_send_summary_mails').click(function(){
                jQuery('#sr_summary_mail_interval').attr('disabled', !jQuery('#sr_send_summary_mails_email').attr('disabled'));
                jQuery('#sr_send_summary_mails_email').attr('disabled', !jQuery('#sr_send_summary_mails_email').attr('disabled'));
            });

            jQuery('#sr_summary_mail_interval').on('change',function(){
                if (jQuery('#sr_summary_mail_interval').val() == 'weekly') {
                    jQuery('#sr_summary_month_start_day_span').hide();
                    jQuery('#sr_summary_week_start_day_span').show();
                } else if (jQuery('#sr_summary_mail_interval').val() == 'monthly') {
                    jQuery('#sr_summary_week_start_day_span').hide();
                    jQuery('#sr_summary_month_start_day_span').show();
                } else {
                    jQuery('#sr_summary_week_start_day_span').hide();
                    jQuery('#sr_summary_month_start_day_span').hide();
                }
            });

        });
    </script>
    <label for="sr_is_auto_refresh"><input type="checkbox" id="sr_is_auto_refresh" name="sr_is_auto_refresh" value="yes" <?php checked( get_site_option( 'sr_is_auto_refresh' ), 'yes' ); ?> /> <?php _e('Enable Auto Refresh'); ?></label> &rArr;
    <select id="sr_what_to_refresh" name="sr_what_to_refresh" <?php echo ( get_site_option( 'sr_is_auto_refresh' ) != 'yes' ) ? "disabled" : ""; ?>>
        <option value="select" <?php selected( get_site_option( 'sr_what_to_refresh' ), 'select' ); ?>><?php _e('Select'); ?></option>
        <option value="kpi" <?php selected( get_site_option( 'sr_what_to_refresh' ), 'kpi' ); ?>><?php _e('KPI'); ?></option>
        <option value="dashboard" <?php selected( get_site_option( 'sr_what_to_refresh' ), 'dashboard' ); ?>><?php _e('Dashboard'); ?></option>
        <option value="all" <?php selected( get_site_option( 'sr_what_to_refresh' ), 'all' ); ?>><?php _e('All'); ?></option>
    </select> &rArr;
    <?php _e('Auto Refresh Every'); ?> <input type="text" id="sr_refresh_duration" name="sr_refresh_duration" size="4" value="<?php echo get_site_option( 'sr_refresh_duration' ); ?>" <?php echo ( get_site_option( 'sr_is_auto_refresh' ) != 'yes' ) ? "disabled" : ""; ?> style="text-align: center;" /> <?php _e('minutes') ?>
        
</div>

<br/>

<div id="sr_summary_mails">
    <label for="sr_send_summary_mails"><input type="checkbox" id="sr_send_summary_mails" name="sr_send_summary_mails" value="yes" <?php checked( get_site_option( 'sr_send_summary_mails' ), 'yes' ); ?> /> <?php _e('Enable Daily Summary Mails'); ?></label> &rArr;
    <select id="sr_summary_mail_interval" name="sr_summary_mail_interval"  <?php echo ( get_site_option( 'sr_send_summary_mails' ) != 'yes' ) ? "disabled" : ""; ?>>
        <option value="daily" <?php selected( get_site_option( 'sr_summary_mail_interval' ), 'daily' ); ?>><?php _e('Daily'); ?></option>
        <option value="weekly" <?php selected( get_site_option( 'sr_summary_mail_interval' ), 'weekly' ); ?>><?php _e('Weekly'); ?></option>
        <option value="monthly" <?php selected( get_site_option( 'sr_summary_mail_interval' ), 'monthly' ); ?>><?php _e('Monthly'); ?></option>
    </select> &rArr;
    
    <span id="sr_summary_week_start_day_span" <?php echo (get_site_option( 'sr_summary_mail_interval' ) != 'weekly') ? "style='display:none;'" : ""; ?>>
        <label for="sr_summary_week_start_day"><b><?php _e( 'Week starts from ', 'smart-reporter' ); ?></b></label>
        <select id="sr_summary_week_start_day" name="sr_summary_week_start_day">
            <option value="sunday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'sunday' ); ?>><?php _e('Sunday'); ?></option>
            <option value="monday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'monday' ); ?>><?php _e('Monday'); ?></option>
            <option value="tuesday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'tuesday' ); ?>><?php _e('Tueday'); ?></option>
            <option value="wednesday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'wednesday' ); ?>><?php _e('Wednesday'); ?></option>
            <option value="thursday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'thursday' ); ?>><?php _e('Thursday'); ?></option>
            <option value="friday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'friday' ); ?>><?php _e('Friday'); ?></option>
            <option value="saturday" <?php selected( get_site_option( 'sr_summary_week_start_day' ), 'saturday' ); ?>><?php _e('Saturday'); ?></option>
        </select> &rArr;
    </span>

    <span id="sr_summary_month_start_day_span" <?php echo (get_site_option( 'sr_summary_mail_interval' ) != 'monthly') ? "style='display:none;'" : ""; ?>>
        <label for="sr_summary_month_start_day"><b><?php _e( 'Month starts from ', 'smart-reporter' ); ?></b></label>
        <select id="sr_summary_month_start_day" name="sr_summary_month_start_day">
            <?php 
                for ($i=1;$i<=31;$i++) {
                    echo '<option value="'.$i.'"'.selected( get_site_option( 'sr_summary_month_start_day' ), $i ).'>'. $i .'</option>';
                }
            ?>
        </select> &rArr;
    </span>

    <label for="sr_send_summary_mails_email"><b><?php _e( 'Email:', 'smart-reporter' ); ?></b></label>
    <input type="text" id="sr_send_summary_mails_email" placeholder="john@yourdomain.com, marry@yourdomain.com, ..." style="margin:1px; padding:3px;" size="38" name="sr_send_summary_mails_email" value="<?php echo get_site_option( 'sr_send_summary_mails_email' ); ?>" <?php echo ( get_site_option( 'sr_send_summary_mails' ) != 'yes' ) ? "disabled" : ""; ?> style="text-align: left;" />
    
</div>

<br/>

<input class="button" type="button" id="sr_set_auto_refresh" value="<?php _e('Save Settings'); ?>" />

<div id='sr_settings_updated_message' style="display:none;" class='updated fade'><p><?php _e( 'Smart Reporter Settings <b>Updated</b>','smart-reporter' ); ?></p></div>
<div id="notification" name="notification"></div>
<?php
}

function sr_get_license_key() {
    global $wpdb;
    $key     = '';
    
    if ( is_multisite() ) {
        $query = "SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = 'sr_license_key'";
    } else {
        $query = "SELECT option_value FROM {$wpdb->prefix}options WHERE option_name = 'sr_license_key'";
    }
    $records = $wpdb->get_results ( $query, ARRAY_A );
    if (count ( $records ) == 1) {
        $key = is_multisite() ? $records [0] ['meta_value'] : $records [0] ['option_value'];
    }
    return $key;
}

// if ( isset( $_POST['action'] ) && $_POST['action'] == 'set_auto_refresh' ) {
function sr_save_settings() {
    ob_clean();
    try {
        if ( !function_exists( 'update_site_option' ) ) {
            if ( ! defined('ABSPATH') ) {
                include_once ('../../../../wp-load.php');
            }
            include_once ABSPATH . 'wp-includes/option.php';
        }
        $sr_is_auto_refresh = ( $_POST['sr_is_auto_refresh'] == 'true' ) ? 'yes' : 'no';
        update_site_option( 'sr_is_auto_refresh', $sr_is_auto_refresh );
        update_site_option( 'sr_what_to_refresh', $_POST['sr_what_to_refresh'] );
        update_site_option( 'sr_refresh_duration', $_POST['sr_refresh_duration'] );

        $sr_send_summary_mails = ( $_POST['sr_send_summary_mails'] == 'true' ) ? 'yes' : 'no';
        update_site_option( 'sr_send_summary_mails', $sr_send_summary_mails );
        update_site_option( 'sr_summary_mail_interval', $_POST['sr_summary_mail_interval'] );
        update_site_option( 'sr_summary_week_start_day', $_POST['sr_summary_week_start_day'] );
        update_site_option( 'sr_summary_month_start_day', $_POST['sr_summary_month_start_day'] );
        update_site_option( 'sr_send_summary_mails_email', $_POST['sr_send_summary_mails_email'] );
        echo 'success';
    } catch( Exception $e ) {
        echo 'fail';
    };
    exit();
}
