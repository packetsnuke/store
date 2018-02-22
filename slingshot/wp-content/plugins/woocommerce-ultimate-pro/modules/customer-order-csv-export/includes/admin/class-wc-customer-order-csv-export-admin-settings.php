<?php
/**
 * WooCommerce Customer/Order CSV Export
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Customer/Order CSV Export to newer
 * versions in the future. If you wish to customize WooCommerce Customer/Order CSV Export for your
 * needs please refer to http://docs.woocommerce.com/document/ordercustomer-csv-exporter/
 *
 * @package     WC-Customer-Order-CSV-Export/Admin
 * @author      SkyVerge
 * @copyright   Copyright (c) 2012-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Customer/Order CSV Export Admin Settings Class
 *
 * Dedicated class for admin settings
 *
 * @since 4.0.0
 */
class WC_Customer_Order_CSV_Export_Admin_Settings {


	/**
	 * Setup admin settings class
	 *
	 * @since 4.0.0
	 */
	public function __construct() {

		// Render a custom test button when using woocommerce_admin_fields()
		add_action( 'woocommerce_admin_field_csv_test_button', array( $this, 'render_test_button' ) );

		if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
			add_filter( 'woocommerce_admin_settings_sanitize_option_wc_customer_order_csv_export_orders_auto_export_products', array( $this, 'mutate_select2_product_ids' ) );
		}
	}


	/**
	 * Get sections
	 *
	 * @since 4.0.0
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			'orders'    => __( 'Orders', 'ultimatewoo-pro' ),
			'customers' => __( 'Customers', 'ultimatewoo-pro' )
		);

		/**
		 * Allow actors to change the sections for settings
		 *
		 * @since 4.0.0
		 * @param array $sections
		 */
		return apply_filters( 'wc_customer_order_csv_export_sections', $sections );
	}



	/**
	 * Output sections for settings
	 *
	 * @since 4.0.0
	 */
	public function output_sections() {

		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$section_ids = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . admin_url( 'admin.php?page=wc_customer_order_csv_export&tab=settings&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $section_ids ) === $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}


	/**
	 * Returns settings array for use by output/save functions
	 *
	 * In 4.0.0 moved here from WC_Customer_Order_CSV_Export_Admin class
	 *
	 * @since 3.0.0
	 * @param string $section_id
	 * @return array
	 */
	public static function get_settings( $section_id = null ) {

		$order_statuses     = wc_get_order_statuses();
		$product_cat_terms  = get_terms( 'product_cat' );
		$product_categories = array();

		// sanity check: get_terms() may return a WP_Error
		if ( is_array( $product_cat_terms ) && ! empty( $product_cat_terms ) ) {
			foreach ( $product_cat_terms as $term ) {
				$product_categories[ $term->term_id ] = $term->name;
			}
		}

		$export_method_options = wc_customer_order_csv_export()->get_methods_instance()->get_export_method_labels();
		$export_method_options = array( 'disabled' => __( 'Disabled', 'ultimatewoo-pro' ) ) + $export_method_options;

		$ftp_security_options = array(
			'none'    => __( 'None', 'ultimatewoo-pro' ),
			'ftp_ssl' => __( 'FTP with Implicit SSL', 'ultimatewoo-pro' ),
			'ftps'    => __( 'FTP with Explicit TLS/SSL', 'ultimatewoo-pro' ),
			'sftp'    => __( 'SFTP (FTP over SSH)', 'ultimatewoo-pro' )
		);

		$scheduled_descriptions = array(
			'orders'    => '',
			'customers' => '',
		);

		foreach ( array_keys( $scheduled_descriptions ) as $export_type ) {

			// get the scheduled export time to display to user
			if ( $scheduled_timestamp = wp_next_scheduled( 'wc_customer_order_csv_export_auto_export_' . $export_type ) ) {
				/* translators: Placeholders: %s - date */
				$scheduled_descriptions[ $export_type ] = sprintf( __( 'The next export is scheduled on %s', 'ultimatewoo-pro' ), '<code>' . get_date_from_gmt( date( 'Y-m-d H:i:s', $scheduled_timestamp ), wc_date_format() . ' ' . wc_time_format() ) . '</code>' );
			} else {
				$scheduled_descriptions[ $export_type ] = __( 'The export is not scheduled.', 'ultimatewoo-pro' );
			}
		}

		$settings = array(

			'orders' => array(

				array(
					'name' => __( 'Export Format', 'ultimatewoo-pro' ),
					'type' => 'title',
				),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_format',
						'name'     => __( 'Order Export Format', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Default is a new format for v3.0, Import matches the Customer/Order CSV Import plugin format, and legacy is prior to version 3', 'ultimatewoo-pro' ),
						'type'     => 'select',
						'options'  => array(
							'default'                  => __( 'Default', 'ultimatewoo-pro' ),
							'default_one_row_per_item' => __( 'Default - One Row per Item', 'ultimatewoo-pro' ),
							'import'                   => __( 'CSV Import', 'ultimatewoo-pro' ),
							'custom'                   => __( 'Custom', 'ultimatewoo-pro' ),
							'legacy_import'            => __( 'Legacy CSV Import', 'ultimatewoo-pro' ),
							'legacy_one_row_per_item'  => __( 'Legacy - One Row per Item', 'ultimatewoo-pro' ),
							'legacy_single_column'     => __( 'Legacy - Single Column for all Items', 'ultimatewoo-pro' ),
						),
						'default'  => 'default',
					),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_filename',
						'name'     => __( 'Order Export Filename', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'The filename for exported orders. Merge variables: %%timestamp%%, %%order_ids%%', 'ultimatewoo-pro' ),
						'default'  => 'orders-export-%%timestamp%%.csv',
						'css'      => 'min-width: 300px;',
						'type'     => 'text',
					),

					array(
						'id'      => 'wc_customer_order_csv_export_orders_add_note',
						'name'    => __( 'Add Order Notes', 'ultimatewoo-pro' ),
						'desc'    => __( 'Enable to add a note to exported orders.', 'ultimatewoo-pro' ),
						'default' => 'yes',
						'type'    => 'checkbox',
					),

				array( 'type' => 'sectionend' ),

				array(
					'name' => __( 'Automated Export Settings', 'ultimatewoo-pro' ),
					'type' => 'title'
				),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_auto_export_method',
						'name'     => __( 'Automatically Export Orders', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Enable this to automatically export orders via the method & schedule selected.', 'ultimatewoo-pro' ),
						'type'     => 'select',
						'options'  => $export_method_options,
						'default'  => 'disabled',
						'class'    => 'js-auto-export-method',
						/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
						'desc'     => sprintf( __( 'Local exports are generated, then saved to the %1$sExport List%2$s for 14 days.', 'ultimatewoo-pro' ), '<a href="' . admin_url( 'admin.php?page=wc_customer_order_csv_export&tab=export_list' ) . '">', '</a>' ),
					),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_auto_export_trigger',
						'name'     => __( 'Trigger Automatic Export', 'ultimatewoo-pro' ),
						'desc_tip' => __( "Choose whether to auto-export orders on a schedule or immediately when they're paid for.", 'ultimatewoo-pro' ),
						'type'     => 'select',
						'options'  => array(
							'schedule'  => __( 'on scheduled intervals', 'ultimatewoo-pro' ),
							'immediate' => __( 'immediately as orders are paid', 'ultimatewoo-pro' ),
						),
						'default' => 'schedule',
						'class'   => 'js-auto-export-trigger',
					),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_auto_export_start_time',
						'name'     => __( 'Export Start Time', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Any new orders will start exporting at this time.', 'ultimatewoo-pro' ),
						/* translators: Placeholders: %s - time */
						'desc'     => sprintf( 	__( 'Local time is %s.', 'ultimatewoo-pro' ), '<code>' . date_i18n( wc_time_format() ) . '</code>' ) . ' ' . $scheduled_descriptions['orders'],
						'default'  => '',
						'type'     => 'text',
						'css'      => 'max-width: 100px;',
						'class'    => 'js-auto-export-timepicker js-auto-export-schedule-field',
					),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_auto_export_interval',
						'name'     => __( 'Export Interval (in minutes)*', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Any new orders will be exported on this schedule.', 'ultimatewoo-pro' ),
						'desc'     => __( 'Required in order to schedule the automatic export.', 'ultimatewoo-pro' ),
						'default'  => '30',
						'type'     => 'text',
						'css'      => 'max-width: 50px;',
						'class'    => 'js-auto-export-schedule-field',
					),

					array(
						'id'                => 'wc_customer_order_csv_export_orders_auto_export_statuses',
						'name'              => __( 'Order Statuses', 'ultimatewoo-pro' ),
						'desc_tip'          => __( 'Orders with these statuses will be included in the export.', 'ultimatewoo-pro' ),
						'type'              => 'multiselect',
						'options'           => $order_statuses,
						'default'           => '',
						'class'             => 'wc-enhanced-select js-auto-export-schedule-field',
						'css'               => 'min-width: 250px',
						'custom_attributes' => array(
							'data-placeholder' => __( 'Leave blank to export orders with any status.', 'ultimatewoo-pro' ),
						),
					),

					array(
						'id'                => 'wc_customer_order_csv_export_orders_auto_export_product_categories',
						'name'              => __( 'Product Categories', 'ultimatewoo-pro' ),
						'desc_tip'          => __( 'Orders with products in these categories will be included in the export.', 'ultimatewoo-pro' ),
						'type'              => 'multiselect',
						'options'           => $product_categories,
						'default'           => '',
						'class'             => 'wc-enhanced-select',
						'css'               => 'min-width: 250px',
						'custom_attributes' => array(
							'data-placeholder' => __( 'Leave blank to export orders with products in any category.', 'ultimatewoo-pro' ),
						),
					),

					array(
						'id'                => 'wc_customer_order_csv_export_orders_auto_export_products',
						'name'              => __( 'Products', 'ultimatewoo-pro' ),
						'desc_tip'          => __( 'Orders with these products will be included in the export.', 'ultimatewoo-pro' ),
						'type'              => 'csv_product_search',
						'default'           => '',
						'class'             => 'wc-product-search',
						'css'               => 'min-width: 250px',
						'custom_attributes' => array(
							'data-multiple'    => 'true',
							'data-action'      => 'woocommerce_json_search_products_and_variations',
							'data-placeholder' => __( 'Leave blank to export orders with any products.', 'ultimatewoo-pro' ),
						),
					),

					array( 'type' => 'sectionend' ),

					array(
						'id'   => 'wc_customer_order_csv_export_orders_ftp_settings',
						'name' => __( 'FTP Settings', 'ultimatewoo-pro' ),
						'type' => 'title'
					),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_ftp_server',
						'name'     => __( 'Server Address', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'The address of the remote FTP server to upload to.', 'ultimatewoo-pro' ),
						'default'  => '',
						'type'     => 'text',
						'class'    => 'js-auto-export-ftp-field',
					),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_ftp_username',
						'name'     => __( 'Username', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'The username for the remote FTP server.', 'ultimatewoo-pro' ),
						'default'  => '',
						'type'     => 'text',
						'class'    => 'js-auto-export-ftp-field',
					),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_ftp_password',
						'name'     => __( 'Password', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'The password for the remote FTP server.', 'ultimatewoo-pro' ),
						'default'  => '',
						'type'     => 'password',
						'class'    => 'js-auto-export-ftp-field',
					),

					array(
						'id'                => 'wc_customer_order_csv_export_orders_ftp_port',
						'name'              => __( 'Port', 'ultimatewoo-pro' ),
						'desc_tip'          => __( 'The port for the remote FTP server.', 'ultimatewoo-pro' ),
						'default'           => '21',
						'type'              => 'number',
						'class'             => 'js-auto-export-ftp-field js-auto-export-ftp-port',
						'style'             => 'max-width: 50px;',
						'custom_attributes' => array( 'min' => 0, 'step' => 1 ),
					),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_ftp_path',
						'name'     => __( 'Initial Path', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'The initial path for the remote FTP server with trailing slash, but excluding leading slash.', 'ultimatewoo-pro' ),
						'default'  => '',
						'type'     => 'text',
						'class'    => 'js-auto-export-ftp-field',
					),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_ftp_security',
						'name'     => __( 'Security', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Select the security type for the remote FTP server.', 'ultimatewoo-pro' ),
						'default'  => 'none',
						'options'  => $ftp_security_options,
						'type'     => 'select',
						'class'    => 'js-auto-export-ftp-field js-auto-export-ftp-security',
					),

					array(
						'id'      => 'wc_customer_order_csv_export_orders_ftp_passive_mode',
						'name'    => __( 'Passive Mode', 'ultimatewoo-pro' ),
						'desc'    => __( 'Enable passive mode if you are having issues connecting to FTP, especially if you see "PORT command successful" in the error log.', 'ultimatewoo-pro' ),
						'default' => 'no',
						'type'    => 'checkbox',
						'class'   => 'js-auto-export-ftp-field',
					),

					array(
						'id'          => 'wc_customer_order_csv_export_orders_ftp_test_button',
						'name'        => __( 'Test FTP', 'ultimatewoo-pro' ),
						'method'      => 'ftp',
						'type'        => 'csv_test_button',
						'export_type' => 'orders',
						'class'       => 'js-auto-export-ftp-field js-auto-export-test-button',
					),

					array( 'type' => 'sectionend' ),

					array(
						'id'   => 'wc_customer_order_csv_export_orders_http_post_settings',
						'name' => __( 'HTTP POST Settings', 'ultimatewoo-pro' ),
						'type' => 'title'
					),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_http_post_url',
						'name'     => __( 'HTTP POST URL', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Enter the URL to POST the exported CSV to.', 'ultimatewoo-pro' ),
						'default'  => '',
						'type'     => 'text',
						'class'    => 'js-auto-export-http-post-field',
					),

					array(
						'id'          => 'wc_customer_order_csv_export_orders_http_post_test_button',
						'name'        => __( 'Test HTTP POST', 'ultimatewoo-pro' ),
						'method'      => 'http_post',
						'type'        => 'csv_test_button',
						'export_type' => 'orders',
						'class'       => 'js-auto-export-http-post-field js-auto-export-test-button',
					),

					array( 'type' => 'sectionend' ),

					array(
						'id'   => 'wc_customer_order_csv_export_orders_email_settings',
						'name' => __( 'Email Settings', 'ultimatewoo-pro' ),
						'type' => 'title'
					),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_email_recipients',
						'name'     => __( 'Recipient(s)', 'ultimatewoo-pro' ),
						/* translators: Placeholders: %s - email address */
						'desc_tip' => sprintf( __( 'Enter recipients (comma separated) the exported CSV should be emailed to. Defaults to %s.', 'ultimatewoo-pro' ), '<em>' . esc_html( get_option( 'admin_email' ) ) . '</em>' ),
						'default'  => '',
						'type'     => 'text',
						'class'    => 'js-auto-export-email-field',
					),

					array(
						'id'       => 'wc_customer_order_csv_export_orders_email_subject',
						'name'     => __( 'Email Subject', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Enter the email subject.', 'ultimatewoo-pro' ),
						/* translators: Placeholders: %s - blog name */
						'default'  => sprintf( __( '[%s] Order CSV Export', 'ultimatewoo-pro' ), get_option( 'blogname' ) ),
						'type'     => 'text',
						'class'    => 'js-auto-export-email-field',
					),

					array(
						'id'          => 'wc_customer_order_csv_export_orders_email_test_button',
						'name'        => __( 'Test Email', 'ultimatewoo-pro' ),
						'method'      => 'email',
						'type'        => 'csv_test_button',
						'export_type' => 'orders',
						'class'       => 'js-auto-export-email-field js-auto-export-test-button',
					),

				array( 'type' => 'sectionend' ),
			),

			'customers' => array(

				array(
					'name' => __( 'Export Format', 'ultimatewoo-pro' ),
					'type' => 'title'
				),

					array(
						'id'       => 'wc_customer_order_csv_export_customers_format',
						'name'     => __( 'Customer Export Format', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Default is a new format for v3.0, Import matches the Customer/Order CSV Import plugin format, Legacy is prior to version 3', 'ultimatewoo-pro' ),
						'type'     => 'select',
						'options'  => array(
							'default' => __( 'Default', 'ultimatewoo-pro' ),
							'import'  => __( 'CSV Import', 'ultimatewoo-pro' ),
							'custom'  => __( 'Custom', 'ultimatewoo-pro' ),
							'legacy'  => __( 'Legacy', 'ultimatewoo-pro' ),
						),
						'default'  => 'default',
					),

					array(
						'id'       => 'wc_customer_order_csv_export_customers_filename',
						'name'     => __( 'Customer Export Filename', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'The filename for exported customers. Merge variables: %%timestamp%%', 'ultimatewoo-pro' ),
						'default'  => 'customers-export-%%timestamp%%.csv',
						'css'      => 'min-width: 300px;',
						'type'     => 'text',
					),

				array( 'type' => 'sectionend' ),

				array(
					'name' => __( 'Automated Export Settings', 'ultimatewoo-pro' ),
					'type' => 'title'
				),

					array(
						'id'       => 'wc_customer_order_csv_export_customers_auto_export_method',
						'name'     => __( 'Automatically Export Customers', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Enable this to automatically export customers via the method & schedule selected.', 'ultimatewoo-pro' ),
						'type'     => 'select',
						'options'  => $export_method_options,
						'default'  => 'disabled',
						'class'    => 'js-auto-export-method',
						/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
						'desc'     => sprintf( __( 'Local exports are generated, then saved to the %1$sExport List%2$s for 14 days.', 'ultimatewoo-pro' ), '<a href="' . admin_url( 'admin.php?page=wc_customer_order_csv_export&tab=export_list' ) . '">', '</a>' ),
					),

					array(
						'id'       => 'wc_customer_order_csv_export_customers_auto_export_start_time',
						'name'     => __( 'Export Start Time', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Any new customers will start exporting at this time.', 'ultimatewoo-pro' ),
						/* translators: Placeholders: %s - time */
						'desc'     => sprintf( 	__( 'Local time is %s.', 'ultimatewoo-pro' ), '<code>' . date_i18n( wc_time_format() ) . '</code>' ) . ' ' . $scheduled_descriptions['customers'],
						'default'  => '',
						'type'     => 'text',
						'css'      => 'max-width: 100px;',
						'class'    => 'js-auto-export-timepicker js-auto-export-schedule-field',
					),

					array(
						'id'       => 'wc_customer_order_csv_export_customers_auto_export_interval',
						'name'     => __( 'Export Interval (in minutes)*', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Any new customers will be exported on this schedule.', 'ultimatewoo-pro' ),
						'desc'     => __( 'Required in order to schedule the automatic export.', 'ultimatewoo-pro' ),
						'default'  => '30',
						'type'     => 'text',
						'css'      => 'max-width: 50px;',
						'class'    => 'js-auto-export-schedule-field',
					),

					array( 'type' => 'sectionend' ),

					array(
						'id'   => 'wc_customer_order_csv_export_customers_ftp_settings',
						'name' => __( 'FTP Settings', 'ultimatewoo-pro' ),
						'type' => 'title'
					),

					array(
						'id'       => 'wc_customer_order_csv_export_customers_ftp_server',
						'name'     => __( 'Server Address', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'The address of the remote FTP server to upload to.', 'ultimatewoo-pro' ),
						'default'  => '',
						'type'     => 'text',
						'class'    => 'js-auto-export-ftp-field',
					),

					array(
						'id'       => 'wc_customer_order_csv_export_customers_ftp_username',
						'name'     => __( 'Username', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'The username for the remote FTP server.', 'ultimatewoo-pro' ),
						'default'  => '',
						'type'     => 'text',
						'class'    => 'js-auto-export-ftp-field',
					),

					array(
						'id'       => 'wc_customer_order_csv_export_customers_ftp_password',
						'name'     => __( 'Password', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'The password for the remote FTP server.', 'ultimatewoo-pro' ),
						'default'  => '',
						'type'     => 'password',
						'class'    => 'js-auto-export-ftp-field',
					),

					array(
						'id'                => 'wc_customer_order_csv_export_customers_ftp_port',
						'name'              => __( 'Port', 'ultimatewoo-pro' ),
						'desc_tip'          => __( 'The port for the remote FTP server.', 'ultimatewoo-pro' ),
						'default'           => '21',
						'type'              => 'number',
						'class'             => 'js-auto-export-ftp-field js-auto-export-ftp-port',
						'style'             => 'max-width: 50px;',
						'custom_attributes' => array( 'min' => 0, 'step' => 1 ),
					),

					array(
						'id'       => 'wc_customer_order_csv_export_customers_ftp_path',
						'name'     => __( 'Initial Path', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'The initial path for the remote FTP server with trailing slash, but excluding leading slash.', 'ultimatewoo-pro' ),
						'default'  => '',
						'type'     => 'text',
						'class'    => 'js-auto-export-ftp-field',
					),

					array(
						'id'       => 'wc_customer_order_csv_export_customers_ftp_security',
						'name'     => __( 'Security', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Select the security type for the remote FTP server.', 'ultimatewoo-pro' ),
						'default'  => 'none',
						'options'  => $ftp_security_options,
						'type'     => 'select',
						'class'    => 'js-auto-export-ftp-field js-auto-export-ftp-security',
					),

					array(
						'id'      => 'wc_customer_order_csv_export_customers_ftp_passive_mode',
						'name'    => __( 'Passive Mode', 'ultimatewoo-pro' ),
						'desc'    => __( 'Enable passive mode if you are having issues connecting to FTP, especially if you see "PORT command successful" in the error log.', 'ultimatewoo-pro' ),
						'default' => 'no',
						'type'    => 'checkbox',
						'class'   => 'js-auto-export-ftp-field',
					),

					array(
						'id'          => 'wc_customer_order_csv_export_customers_ftp_test_button',
						'name'        => __( 'Test FTP', 'ultimatewoo-pro' ),
						'method'      => 'ftp',
						'type'        => 'csv_test_button',
						'export_type' => 'customers',
						'class'       => 'js-auto-export-ftp-field js-auto-export-test-button',
					),

					array( 'type' => 'sectionend' ),

					array(
						'id'   => 'wc_customer_order_csv_export_customers_http_post_settings',
						'name' => __( 'HTTP POST Settings', 'ultimatewoo-pro' ),
						'type' => 'title'
					),

					array(
						'id'       => 'wc_customer_order_csv_export_customers_http_post_url',
						'name'     => __( 'HTTP POST URL', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Enter the URL to POST the exported CSV to.', 'ultimatewoo-pro' ),
						'default'  => '',
						'type'     => 'text',
						'class'    => 'js-auto-export-http-post-field',
					),

					array(
						'id'          => 'wc_customer_order_csv_export_customers_http_post_test_button',
						'name'        => __( 'Test HTTP POST', 'ultimatewoo-pro' ),
						'method'      => 'http_post',
						'type'        => 'csv_test_button',
						'export_type' => 'customers',
						'class'       => 'js-auto-export-http-post-field js-auto-export-test-button',
					),

					array( 'type' => 'sectionend' ),

					array(
						'id'   => 'wc_customer_order_csv_export_customers_email_settings',
						'name' => __( 'Email Settings', 'ultimatewoo-pro' ),
						'type' => 'title'
					),

					array(
						'id'       => 'wc_customer_order_csv_export_customers_email_recipients',
						'name'     => __( 'Recipient(s)', 'ultimatewoo-pro' ),
						/* translators: Placeholders: %s - email address */
						'desc_tip' => sprintf( __( 'Enter recipients (comma separated) the exported CSV should be emailed to. Defaults to %s.', 'ultimatewoo-pro' ), '<em>' . esc_html( get_option( 'admin_email' ) ) . '</em>' ),
						'default'  => '',
						'type'     => 'text',
						'class'    => 'js-auto-export-email-field',
					),

					array(
						'id'       => 'wc_customer_order_csv_export_customers_email_subject',
						'name'     => __( 'Email Subject', 'ultimatewoo-pro' ),
						'desc_tip' => __( 'Enter the email subject.', 'ultimatewoo-pro' ),
						/* translators: Placeholders: %s - blog name */
						'default'  => sprintf( __( '[%s] Customer CSV Export', 'ultimatewoo-pro' ), get_option( 'blogname' ) ),
						'type'     => 'text',
						'class'    => 'js-auto-export-email-field',
					),

					array(
						'id'          => 'wc_customer_order_csv_export_customers_email_test_button',
						'name'        => __( 'Test Email', 'ultimatewoo-pro' ),
						'method'      => 'email',
						'type'        => 'csv_test_button',
						'export_type' => 'customers',
						'class'       => 'js-auto-export-email-field js-auto-export-test-button',
					),

				array( 'type' => 'sectionend' ),
			),
		);

		// return all or section-specific settings
		$found_settings = $section_id ? $settings[ $section_id ] : $settings;

		/**
		 * Allow actors to add or remove settings from the CSV export settings page.
		 *
		 * In 4.0.0 renamed $tab_id arg to $section_id, moved here from
		 * WC_Customer_Order_CSV_Export_Admin class
		 *
		 * @since 3.0.6
		 * @param array $settings an array of settings for the given section
		 * @param string $section_id current section ID
		 */
		return apply_filters( 'wc_customer_order_csv_export_settings', $found_settings, $section_id );
	}


	/**
	 * Render a test button
	 *
	 * In 4.0.0 moved here from WC_Customer_Order_CSV_Export_Admin class
	 *
	 * @since 3.0.0
	 * @param array $field
	 */
	public function render_test_button( $field ) {

		$settings_exist = wc_customer_order_csv_export()->get_methods_instance()->method_settings_exist( $field['method'], $field['export_type'] );
		$name           = $field['name'];
		$atts           = array( 'data-method' => $field['method'] );
		$classes        = array_merge( array( 'secondary' ), explode( ' ', $field['class'] ) );
		$button_type    = implode( ' ', $classes );

		// disable text button and change name if required
		if ( ! $settings_exist ) {
			$name = __( 'Please save your settings before testing', 'ultimatewoo-pro' );
			$atts['disabled'] = 'disabled';
		}

		?>
			<tr valign="top">
				<th scope="row" class="titledesc">Test</th>
				<td class="forminp">
					<?php submit_button( $name, $button_type, $field['id'], true, $atts ); ?>
				</td>
			</tr>
		<?php
	}


	/**
	 * Show Settings page
	 *
	 * @since 4.0.0
	 */
	public function output() {

		global $current_section;

		// default to orders section
		if ( ! $current_section ) {
			$current_section = 'orders';
		}

		$this->output_sections();

		// render settings fields
		woocommerce_admin_fields( self::get_settings( $current_section ) );

		wp_nonce_field( __FILE__ );
		submit_button( __( 'Save settings', 'ultimatewoo-pro' ) );
	}


	/**
	 * Save settings or perform a test export
	 *
	 * @since 4.0.0
	 */
	public function save() {

		global $current_section;

		// default to orders section
		if ( ! $current_section ) {
			$current_section = 'orders';
		}

		// security check
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], __FILE__ ) ) {

			wp_die( __( 'Action failed. Please refresh the page and retry.', 'ultimatewoo-pro' ) );
		}

		if ( isset( $_POST['wc_customer_order_csv_export_test_method'] ) ) {

			// process test
			$export_handler = wc_customer_order_csv_export()->get_export_handler_instance();

			$result = $export_handler->test_export_via( $_POST['wc_customer_order_csv_export_test_method'], $current_section );

			if ( 'error' === $result[1] ) {
				wc_customer_order_csv_export()->get_message_handler()->add_error( $result[0] );
			} else {
				wc_customer_order_csv_export()->get_message_handler()->add_message( $result[0] );
			}


		} else {

			$orig_schedule_signature = $this->get_auto_export_schedule_signature( $current_section );

			// make sure export filenames are always set, otherwise bad things are going to happen
			$filename_option = 'wc_customer_order_csv_export_' . $current_section . '_filename';

			if ( isset( $_POST[ $filename_option ] ) && empty( $_POST[ $filename_option ] ) ) {
				$_POST[ $filename_option ] = $this->get_default_option_value( $current_section, $filename_option );
			}

			// save settings
			woocommerce_update_options( self::get_settings( $current_section ) );

			// clear scheduled export event if scheduled exports disabled or export interval and/or start time were changed
			if ( ! wc_customer_order_csv_export()->get_cron_instance()->scheduled_exports_enabled( $current_section ) || $orig_schedule_signature !== $this->get_auto_export_schedule_signature( $current_section ) ) {

				// note this resets the next scheduled execution time to the time options were saved + the interval
				wp_clear_scheduled_hook( 'wc_customer_order_csv_export_auto_export_' . $current_section );
			}

			wc_customer_order_csv_export()->get_message_handler()->add_message( __( 'Your settings have been saved.', 'ultimatewoo-pro' ) );
		}
	}


	/**
	 * Mutate the select2 v4 values (introduced in WC 3.0) from an array of
	 * product IDs into a comma-separated string.
	 *
	 * @since 4.2.0
	 * @param string|array $value parsed value from $_POST
	 * @return string
	 */
	public function mutate_select2_product_ids( $value ) {

		if ( is_array( $value ) ) {
			$value = implode( ',', array_map( 'absint', $value ) );
		}

		return $value;
	}


	/**
	 * Get the currently configured auto export schedule signature
	 *
	 * Helper method to get the concatenated export start time and interval,
	 * used for testing if the schedule has been changed.
	 *
	 * @since 4.0.0
	 * @param string $export_type
	 * @return string
	 */
	private function get_auto_export_schedule_signature( $export_type ) {

		return get_option( 'wc_customer_order_csv_export_' . $export_type . '_auto_export_start_time' ) . get_option( 'wc_customer_order_csv_export_' . $export_type . '_auto_export_interval' );
	}


	/**
	 * Get default option value
	 *
	 * @since 4.0.0
	 * @param string $section
	 * @param string $option_id
	 * @return mixed|null null if no default value
	 */
	private function get_default_option_value( $section, $option_id ) {

		$settings      = self::get_settings( $section );
		$default_value = null;

		foreach ( $settings as $setting ) {

			if ( isset( $setting['id'] ) && $setting['id'] === $option_id ) {

				$default_value = isset( $setting['default'] ) ? $setting['default'] : null;
				break;
			}
		}

		return $default_value;
	}


}
