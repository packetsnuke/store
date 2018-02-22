<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WC_SA_Email')) :

    /**
     *
     * @class       WC_SA_Email
     */
    class WC_SA_Email extends WC_Email
    {

        public $status;
        public $message_text;
        public $order_info;
        public $html_filename;
        public $plain_filename;

        /**
         * Constructor.
         */
        public function __construct($st_id)
        {

            $status = wc_sa_get_status($st_id);
            $this->status = $status;
            $this->sent_to_admin = false;
            $this->id = 'wc_sa_order' . $status->label;
            $this->customer_email = $status->email_recipients === 'customer';
            $this->title = sprintf(__('Order status is set to %s', 'woocommerce_status_actions'), $status->title);
            $this->description = sprintf(__("This email is sent when order status is set to %s", 'woocommerce_status_actions'), $status->title);

            $this->heading = stripslashes($status->email_heading);
            $this->subject = stripslashes($status->email_subject);

            $this->html_filename = 'wc_custom_status_email_html_template.php';
            $this->plain_filename = 'wc_custom_status_email_plain_template.php';
            $this->template_html = WC_SA()->templates_dir . '/' . $this->html_filename;
            $this->template_plain = WC_SA()->templates_dir . '/' . $this->plain_filename;

            // Triggers for this email
            add_action('woocommerce_order_status_' . $status->label . '_notification', array($this, 'trigger'), 150, 1);


            // Call parent constuctor
            parent::__construct();


            // Other settings
            $this->email_type = $status->email_type;
            $this->from_name = stripslashes($status->email_from_name);
            $this->from_email = $status->email_from_address;
            $this->message_text = $status->email_message == 'yes' ? stripslashes($status->email_message_text) : '';
            $this->order_info = $status->email_order_info == 'yes' ? true : false;
            $this->custom_email_address = $status->email_custom_address;
            switch ($status->email_recipients) {
                case 'custom':
                    $this->recipient = $status->email_custom_address;
                    $this->sent_to_admin = false;
                    break;
                default:
                    $this->recipient = get_option('admin_email');
                    $this->sent_to_admin = true;
                    break;
            }
        }

        /**
         * Trigger.
         *
         * @param int $order_id
         */
        public function trigger($order_id)
        {

            if ($order_id) {
                $this->object = wc_get_order($order_id);
                if ($this->customer_email) {
                    $this->recipient = $this->object->get_billing_email();
                    $this->sent_to_admin = false;
                }
                $this->process_shortcodes($order_id);
            }

            if (!$this->is_enabled() || !$this->get_recipient()) {
                return;
            }

            $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());

            if ($this->status->email_recipients == 'both' && $order_id) {
                $this->recipient = $this->object->billing_email;
                $this->sent_to_admin = false;
                $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
            }
        }

        public function init_form_fields()
        {
            return array();
        }

        /**
         * Checks if this email is enabled and will be sent.
         * @return bool
         */
        public function is_enabled()
        {
            return 'yes' === $this->status->email_notification;
        }

        /**
         * Get valid recipients.
         * @return string
         */
        public function get_recipient()
        {
            if (isset($_GET['page']) && $_GET['page'] == 'wc-settings' && !$this->customer_email) {
                switch ($this->status->email_recipients) {
                    case 'admin':
                        return __('Administrator', 'woocommerce_status_actions');
                        break;
                    case 'both':
                        return __('Administrator & Customer', 'woocommerce_status_actions');
                        break;
                }
            }
            $recipient = apply_filters('woocommerce_email_recipient_' . $this->id, $this->recipient, $this->object);
            $recipients = array_map('trim', explode(',', $recipient));
            $recipients = array_filter($recipients, 'is_email');
            return implode(', ', $recipients);
        }

        public function get_message_text()
        {
            foreach ($this->replace as $key => $value) {
                if (!$value && stripos($key, 'shipping_') !== false) {
                    unset($this->replace[$key]);
                    unset($this->find[$key]);
                }
            }
            $text = apply_filters('woocommerce_email_custom_info_' . $this->id, $this->format_string($this->message_text), $this->object);
            $text = preg_replace('/\{[a-z0-9_]{1,}\}\r\n/i', '', $text);
            return nl2br($text);
        }

        public function get_content_html()
        {
            ob_start();
            extract(array(
                'order' => $this->object,
                'email_heading' => $this->get_heading(),
                'custom_info' => $this->get_message_text(),
                'order_info' => $this->order_info,
                'sent_to_admin' => $this->sent_to_admin,
                'plain_text' => false,
                'email' => $this
            ));
            include $this->template_html;
            return ob_get_clean();
        }

        public function get_content_plain()
        {
            ob_start();
            extract(array(
                'order' => $this->object,
                'email_heading' => $this->get_heading(),
                'custom_info' => $this->get_message_text(),
                'order_info' => $this->order_info,
                'sent_to_admin' => $this->sent_to_admin,
                'plain_text' => true,
                'email' => $this
            ));
            include $this->template_plain;
            return ob_get_clean();
        }

        public function get_from_address()
        {
            return sanitize_email($this->from_email);
        }

        public function get_from_name()
        {
            return wp_specialchars_decode(stripslashes($this->from_name), ENT_QUOTES);
        }

        //Process shortcodes
        public function process_shortcodes($order_id)
        {
            //$this->object = wc_get_order( $order_id );
            if (!is_array($this->find)) {
                $this->find = array();
            }
            if (!is_array($this->replace)) {
                $this->replace = array();
            }

            $this->find['order_date'] = '{order_date}';
            $this->replace['order_date'] = date_i18n(woocommerce_date_format(), strtotime($this->object->order_date));

            $this->find['order_number'] = '{order_number}';
            $this->replace['order_number'] = $this->object->get_order_number();

            $this->find['order_value'] = '{order_value}';
            $this->replace['order_value'] = $this->object->order_total;

            $this->find['payment_method'] = '{payment_method}';
            $this->replace['payment_method'] = $this->object->payment_method_title;

            $this->find['shipping_method'] = '{shipping_method}';
            $this->replace['shipping_method'] = $this->object->get_shipping_method();

            $this->find['billing_address'] = '{billing_address}';
            $this->replace['billing_address'] = $this->object->get_formatted_billing_address();

            $this->find['shipping_address'] = '{shipping_address}';
            $this->replace['shipping_address'] = $this->object->get_formatted_shipping_address();

            $customer_first_name = "";
            $customer_last_name = "";
            $customer_user_object = get_user_by('id', $this->object->customer_user);
            if ($customer_user_object) {
                $customer_first_name = $customer_user_object->get('first_name');
                $customer_last_name = $customer_user_object->get('last_name');
            }
            $this->find['customer_first_name'] = '{customer_first_name}';
            $this->find['customer_last_name'] = '{customer_last_name}';
            $this->replace['customer_first_name'] = stripslashes($customer_first_name);
            $this->replace['customer_last_name'] = stripslashes($customer_last_name);


            $default_fields = array(
                'country',
                'first_name',
                'last_name',
                'company',
                'address_1',
                'address_2',
                'city',
                'state',
                'postcode',
                'email',
                'phone',
            );
            $default_fields = array_flip($default_fields);
            $billing_fields = get_option('wc_fields_billing');
            $sufix = '';
            if (!$billing_fields || !is_array($billing_fields)) {
                $sufix = 'billing_';
                $billing_fields = $default_fields;
            }
            foreach ($billing_fields as $name => $value) {
                $key = $sufix . $name;
                $this->find[$key] = '{' . $key . '}';
                $field_val = get_post_meta($order_id, '_' . $key, true);
                if ($field_val) {
                    $this->replace[$key] = $field_val;
                } else {
                    $this->replace[$key] = '';
                }
            }
            $billing_fields = get_option('wc_fields_shipping');
            $sufix = '';
            if (!$billing_fields || !is_array($billing_fields)) {
                $sufix = 'shipping_';
                $billing_fields = $default_fields;
            }
            foreach ($billing_fields as $name => $value) {
                $key = $sufix . $name;
                $this->find[$key] = '{' . $key . '}';
                $field_val = get_post_meta($order_id, '_' . $key, true);
                if ($field_val) {
                    $this->replace[$key] = $field_val;
                } else {
                    $this->replace[$key] = '';
                }
            }

            $default_tracking_fields = array(
                'tracking_provider',
                'custom_tracking_provider',
                'tracking_number',
                'custom_tracking_link',
                'date_shipped',
            );
            $default_tracking_fields = array_flip($default_tracking_fields);
            $hipment_tracking_items = get_post_meta($order_id, '_wc_shipment_tracking_items', true);
            $items = !is_array($hipment_tracking_items) ? array() : $hipment_tracking_items;

            foreach ($default_tracking_fields as $key => $value) {
                $this->find[$key] = '{' . $key . '}';
                $this->replace[$key] = isset($items[$key]) ? $items[$key] : get_post_meta($order_id, '_' . $key, true);
            }


            $custom_fields = get_option('wc_fields_additional');
            if ($custom_fields) {
                foreach ($custom_fields as $name => $value) {
                    if (!empty($name)) {
                        $val = get_post_meta($order_id, $name, true);
                        if (is_string($val)) {
                            $this->find[$name] = '{' . $name . '}';
                            $this->replace[$name] = $val;
                        }
                    }
                }
            }

            $advanced_fields = wc_sa_get_acf_editor_btns();
            if ($advanced_fields && !empty($advanced_fields) && is_array($advanced_fields)) {
                foreach ($advanced_fields as $name => $value) {
                    if (!empty($name)) {
                        $val = get_post_meta($order_id, $name, true);
                        if (is_string($val)) {
                            $this->find[$name] = '{' . $name . '}';
                            $this->replace[$name] = $val;
                        }
                    }
                }
            }

            $aftership_tracking_provider = get_post_custom_values('_aftership_tracking_provider_name', $order_id);

            $this->find['aftership_tracking_provider_name'] = '{aftership_tracking_provider_name}';
            $this->replace['aftership_tracking_provider_name'] = isset($aftership_tracking_provider[0]) ? $aftership_tracking_provider[0] : '';

            $aftership_tracking_number = get_post_custom_values('_aftership_tracking_number', $order_id);

            $this->find['aftership_tracking_number'] = '{aftership_tracking_number}';
            $this->replace['aftership_tracking_number'] = isset($aftership_tracking_number[0]) ? $aftership_tracking_number[0] : '';


            /*$this->message_text = str_replace($shortcodes,$replacements, $this->message_text);

            $this->heading = str_replace($shortcodes,$replacements,$this->heading);
            $this->subject = str_replace($shortcodes,$replacements,$this->subject);*/
        }

        public function admin_options()
        {
            // Do admin actions.
            $this->admin_actions();
            ?>
            <h2><?php echo esc_html($this->get_title()); ?><?php wc_back_link(__('Return to emails', 'woocommerce'), admin_url('admin.php?page=wc-settings&tab=email')); ?></h2>

            <?php echo wpautop(wp_kses_post($this->get_description())); ?>

            <?php
            /**
             * woocommerce_email_settings_before action hook.
             * @param string $email The email object
             */
            do_action('woocommerce_email_settings_before', $this);
            ?>

            <table class="form-table">
                <?php $this->generate_settings_html(); ?>
            </table>

            <?php
            /**
             * woocommerce_email_settings_after action hook.
             * @param string $email The email object
             */
            do_action('woocommerce_email_settings_after', $this);
            ?>

            <?php if (current_user_can('edit_themes') && (!empty($this->template_html) || !empty($this->template_plain))) { ?>
            <div id="template">
                <?php
                $templates = array(
                    'template_html' => __('HTML template', 'woocommerce'),
                    'template_plain' => __('Plain text template', 'woocommerce'),
                );

                foreach ($templates as $template_type => $title) :
                    $template = $this->get_template($template_type);
                    $filename = $this->get_email_filename($template_type);

                    if (empty($template)) {
                        continue;
                    }

                    $local_file = $this->get_theme_template_file($filename);
                    $template_file = WC_SA()->templates_dir . '/' . $filename;
                    $template_dir = apply_filters('woocommerce_template_directory', 'woocommerce', $template);
                    ?>
                    <div class="template <?php echo $template_type; ?>">

                        <h4><?php echo wp_kses_post($title); ?></h4>

                        <?php if (file_exists($local_file)) { ?>

                            <p>
                                <a href="#" class="button toggle_editor"></a>

                                <?php if (is_writable($local_file)) : ?>
                                    <a href="<?php echo esc_url(wp_nonce_url(remove_query_arg(array('move_template', 'saved'), add_query_arg('delete_template', $template_type)), 'woocommerce_email_template_nonce', '_wc_email_nonce')); ?>"
                                       class="delete_template button"><?php _e('Delete template file', 'woocommerce'); ?></a>
                                <?php endif; ?>

                                <?php printf(__('This template has been overridden by your theme and can be found in: %s.', 'woocommerce'), '<code>' . trailingslashit(basename(get_stylesheet_directory())) . $template_dir . '/' . $template . '</code>'); ?>
                            </p>

                            <div class="editor" style="display:none">
                            <textarea class="code" cols="25" rows="20"
                                      <?php if (!is_writable($local_file)) : ?>readonly="readonly" disabled="disabled"
                                      <?php else : ?>data-name="<?php echo $template_type . '_code'; ?>"<?php endif; ?>><?php echo file_get_contents($local_file); ?></textarea>
                            </div>

                        <?php } elseif (file_exists($template_file)) { ?>

                            <p>
                                <a href="#" class="button toggle_editor"></a>

                                <?php if ((is_dir(get_stylesheet_directory() . '/' . $template_dir . '/emails/') && is_writable(get_stylesheet_directory() . '/' . $template_dir . '/emails/')) || is_writable(get_stylesheet_directory())) { ?>
                                    <a href="<?php echo esc_url(wp_nonce_url(remove_query_arg(array('delete_template', 'saved'), add_query_arg('move_template', $template_type)), 'woocommerce_email_template_nonce', '_wc_email_nonce')); ?>"
                                       class="button"><?php _e('Copy file to theme', 'woocommerce'); ?></a>
                                <?php } ?>

                                <?php printf(__('To override and edit this email template copy %1$s to your theme folder: %2$s.', 'woocommerce'), '<code>' . plugin_basename($template_file) . '</code>', '<code>' . get_stylesheet_directory() . '/' . $template_dir . '/emails/' . $filename . '</code>'); ?>
                            </p>

                            <div class="editor" style="display:none">
                            <textarea class="code" readonly="readonly" disabled="disabled" cols="25"
                                      rows="20"><?php echo file_get_contents($template_file); ?></textarea>
                            </div>

                        <?php } else { ?>

                            <p><?php _e('File was not found.', 'woocommerce'); ?></p>

                        <?php } ?>

                    </div>
                    <?php
                endforeach;
                ?>
            </div>
            <?php
            wc_enqueue_js("
				jQuery( 'select.email_type' ).change( function() {

					var val = jQuery( this ).val();

					jQuery( '.template_plain, .template_html' ).show();

					if ( val != 'multipart' && val != 'html' ) {
						jQuery('.template_html').hide();
					}

					if ( val != 'multipart' && val != 'plain' ) {
						jQuery('.template_plain').hide();
					}

				}).change();

				var view = '" . esc_js(__('View template', 'woocommerce')) . "';
				var hide = '" . esc_js(__('Hide template', 'woocommerce')) . "';

				jQuery( 'a.toggle_editor' ).text( view ).toggle( function() {
					jQuery( this ).text( hide ).closest(' .template' ).find( '.editor' ).slideToggle();
					return false;
				}, function() {
					jQuery( this ).text( view ).closest( '.template' ).find( '.editor' ).slideToggle();
					return false;
				} );

				jQuery( 'a.delete_template' ).click( function() {
					if ( window.confirm('" . esc_js(__('Are you sure you want to delete this template file?', 'woocommerce')) . "') ) {
						return true;
					}

					return false;
				});

				jQuery( '.editor textarea' ).change( function() {
					var name = jQuery( this ).attr( 'data-name' );

					if ( name ) {
						jQuery( this ).attr( 'name', name );
					}
				});
			");
        }
        }

        public function get_email_filename($type)
        {
            $type = basename($type);

            if ('template_html' === $type) {
                return $this->html_filename;
            } elseif ('template_plain' === $type) {
                return $this->plain_filename;
            }
            return '';
        }

        public function get_theme_template_file($template)
        {
            return get_stylesheet_directory() . '/' . apply_filters('woocommerce_template_directory', 'woocommerce', $template) . '/emails/' . $template;
        }

        protected function move_template_action($template_type)
        {
            if ($template = $this->get_template($template_type)) {
                if (!empty($template)) {

                    $filename = $this->get_email_filename($template_type);
                    $theme_file = $this->get_theme_template_file($filename);

                    if (wp_mkdir_p(dirname($theme_file)) && !file_exists($theme_file)) {

                        // Locate template file
                        $template_file = WC_SA()->templates_dir . '/' . $filename;

                        // Copy template file
                        copy($template_file, $theme_file);

                        /**
                         * woocommerce_copy_email_template action hook.
                         *
                         * @param string $template_type The copied template type
                         * @param string $email The email object
                         */
                        do_action('woocommerce_copy_email_template', $template_type, $this);

                        echo '<div class="updated"><p>' . __('Template file copied to theme.', 'woocommerce') . '</p></div>';
                    }
                }
            }
        }

    }

endif;