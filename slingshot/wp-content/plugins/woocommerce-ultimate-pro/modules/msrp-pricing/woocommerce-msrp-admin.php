<?php

if ( ! class_exists( 'woocommerce_msrp_admin' ) ) {
	class woocommerce_msrp_admin {

		/**
		 * Add required hooks
		 */
		function __construct() {

			add_action( 'admin_init', array( $this, 'admin_init' ) );

			// Add meta box to the product page.
			add_action( 'woocommerce_product_options_pricing', array( $this, 'product_meta_field' ) );
			// Show the fields in the variation data.
			add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'variation_show_fields' ), 10, 3 );
			// Save the variation data.
			add_action( 'woocommerce_save_product_variation', array( $this, 'variation_save_fields' ), 10, 2 );
			// Save the main MSRP price information.
			add_action( 'save_post', array( $this, 'save_product' ) );

			// Support composite products extension.
			add_action( 'woocommerce_composite_product_options_pricing', array( $this, 'product_meta_field' ) );

			// Support product add-ons extension.
			add_action( 'woocommerce_product_addons_panel_option_heading', array( $this, 'product_addon_option_heading' ), 10, 3 );
			add_action( 'woocommerce_product_addons_panel_option_row', array( $this, 'product_addon_option_row' ), 10, 4 );
			add_filter( 'woocommerce_product_addons_save_data', array( $this, 'product_addon_save' ), 10, 2 );

			// Support for bulk modifying MSRP price on variations
			add_action( 'woocommerce_variable_product_bulk_edit_actions', array( $this, 'variable_product_bulk_edit_actions' ) );
			add_action( 'woocommerce_bulk_edit_variations', array( $this, 'variable_product_bulk_edit_actions_cb' ), 10, 4 );
		}

		/**
		 * Set up the plugin for translation
		 */
		function admin_init() {

			$domain = 'woocommerce_msrp';
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			load_textdomain( $domain, WP_LANG_DIR . '/woocommerce_msrp/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( 'woocommerce_msrp', null, basename( dirname( __FILE__ ) ) . '/languages' );

			// Add settings to the WooCommerce settings page
			if ( $this->is_wc_2_1() ) {
				add_filter( 'woocommerce_general_settings', array( $this, 'settings_array' ) ); // WC2.1
			} else {
				add_filter( 'woocommerce_catalog_settings', array( $this, 'settings_array' ) ); // WC2.0
			}

			// Enqueue admin JS.
			$this->enqueue_js();
		}

		/**
		 * Add the settings to the WooCommerce settings page
		 */
		function settings_array( $settings ) {
			// Find the end of the pricing section
			foreach ( $settings as $key => $setting ) {
				if ( 'sectionend' === $setting['type']  && 'pricing_options' === $setting['id'] ) {
					$cutoff = $key;
					break;
				}
			}

			// Move the first chunk over
			$new_settings = array_slice( $settings, 0, $cutoff + 1 );

			// Add the new fields

			// Heading
			$new_settings[] = array(
				'title' => __( 'MSRP pricing options', 'ultimatewoo-pro' ),
				'type'  => 'title',
				'id'    => 'woocommerce_msrp',
				'desc'  => __( 'Options controlling when, and how to display MSRP pricing', 'ultimatewoo-pro' ),
			);

			// Show always / only if different / never
			$new_settings[] = array(
				'name'     => __( 'Show MSRP Pricing?', 'ultimatewoo-pro' ),
				'desc'     => __( 'When to show MSRP pricing', 'ultimatewoo-pro' ),
				'tip'      => '',
				'id'       => 'woocommerce_msrp_status',
				'css'      => '',
				'std'      => 'always',
				'type'     => 'select',
				'options'  => array(
					'always'    => __( 'Always', 'ultimatewoo-pro' ),
					'different' => __( 'Only if different', 'ultimatewoo-pro' ),
					'never'     => __( 'Never', 'ultimatewoo-pro' ),
				),
				'desc_tip' => __( 'Choose whether to always display MSRP prices (Always), only display the MSRP if it is different from your price (Only if different), or never display the MSRP price (Never).', 'ultimatewoo-pro' ),
			);

			// Description - text field
			$new_settings[] = array(
				'name'     => __( 'MSRP Labelling', 'ultimatewoo-pro' ),
				'desc'     => __( 'MSRP prices will be labelled with this description', 'ultimatewoo-pro' ),
				'tip'      => '',
				'id'       => 'woocommerce_msrp_description',
				'css'      => '',
				'std'      => __( 'MSRP', 'ultimatewoo-pro' ),
				'type'     => 'text',
				'desc_tip' => __( 'MSRP prices will be labelled with this description', 'ultimatewoo-pro' ),
			);

			$new_settings[] = array(
				'type' => 'sectionend',
				'id'   => 'woocommerce_msrp',
			);

			// Add the remainder back in
			$new_settings = array_merge( $new_settings, array_slice( $settings, $cutoff + 1, 999 ) );

			return $new_settings;

		}

		/**
		 * Display the meta field for MSRP prices on the product page
		 */
		function product_meta_field() {
			woocommerce_wp_text_input(
				array(
					'id'          => '_msrp_price',
					'class'       => 'wc_input_price short',
					'label'       => __( 'MSRP Price', 'ultimatewoo-pro' ) . ' (' . get_woocommerce_currency_symbol() . ')',
					'description' => '',
					'data_type'   => 'price',
				)
			);
		}

		/**
		 * Show the fields for editing the MSRP on the variations panel on the post edit screen
		 * @param  array $variation_data The variation data for this variation
		 * @param  [type] $loop          Unused
		 */
		function variation_show_fields( $loop, $variation_data, $variation ) {

			$msrp = get_post_meta( $variation->ID, '_msrp', true );
			$msrp = ! empty( $msrp ) ? $msrp : '';
?>
			<tr>
				<td>
					<label><?php echo __( 'MSRP Price', 'ultimatewoo-pro' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label><input type="text" size="5" name="variable_msrp[<?php echo $loop; ?>]" value="<?php echo esc_attr( wc_format_localized_price( $msrp ) ); ?>" />
				</td>
			</tr>
			<?php

		}

		/**
		 * Save MSRP values for variable products
		 * @param  int $product_id The parent product ID (Unused)
		 */
		function variation_save_fields( $product_id, $idx ) {
			if ( ! isset( $_POST['variable_post_id'] ) ) { // WPCS: csrf ok.
				return;
			}
			$variation_id = (int) $_POST['variable_post_id'][ $idx ];
			$msrp         = $_POST['variable_msrp'][ $idx ]; // WPCS: csrf ok.
			$msrp         = wc_format_decimal( $msrp );
			update_post_meta( $variation_id, '_msrp', $msrp );
		}

		/**
		 * Save the product meta information
		 * @param int $product_id The product ID
		 */
		function save_product( $product_id ) {
			// Verify if this is an auto save routine.
			// If it is our form has not been submitted, so we dont want to do anything
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( ! isset( $_POST['_msrp_price'] ) ) { // WPCS: csrf ok
				return;
			}

			$msrp = wc_format_decimal( $_POST['_msrp_price'] ); // WPCS: csrf ok.
			update_post_meta( $product_id, '_msrp_price', $msrp );
		}

		/**
		 * Output a heading for the product add-ons table.
		 */
		public function product_addon_option_heading( $post, $addon, $loop ) {
			?><th class="msrp_product_addon_column"><?php _e( 'MSRP', 'woocommerce-msrp' ); ?></th><?php
		}

		/**
		 * Output the markup for an option in the product add-ons table.
		 */
		public function product_addon_option_row( $post, $addon, $loop, $option ) {
			$msrp = isset( $option['msrp'] ) ?  $option['msrp'] : '';
			?>

			<td class="msrp_product_addon_column">
				<input type="number" name="product_addon_option_msrp[<?php esc_attr_e( $loop ); ?>][]" value="<?php esc_attr_e( $msrp ) ?>" placeholder="N/A" min="0" step="any" />
			</td>
			<?php
		}

		/**
		 * Save the MSRP for product addons if they've been passed in.
		 */
		public function product_addon_save( $data, $idx ) {
			if ( isset( $_POST['product_addon_option_msrp'][ $idx ] ) ) { // WPCS: csrf ok.
				foreach ( $_POST['product_addon_option_msrp'][ $idx ] as $option_idx => $value ) { // WPCS: csrf ok.
					$data['options'][ $option_idx ]['msrp'] = $value;
				}
			}
			return $data;
		}

		/**
		 * Render the MSRP bulk-action options in the dropdown.
		 */
		public function variable_product_bulk_edit_actions() {
			?>
			<optgroup label="<?php _e( 'MSRP Prices', 'woocommerce-msrp' ); ?>">
				<option value="msrp_set_prices"><?php _e( 'Set prices', 'woocommerce-msrp' ); ?></option>
				<option value="msrp_clear_prices"><?php _e( 'Clear MSRP prices', 'woocommerce-msrp' ); ?></option>
			</optgroup>
			<?php
		}

		/**
		 * Handler a request to perform a bulk action.
		 *
		 * Calls the relevant function depending on the action being requested.
		 */
		public function variable_product_bulk_edit_actions_cb( $bulk_action, $data, $product_id, $variations ) {
			switch ( $bulk_action ) {
				case 'msrp_set_prices':
					return $this->bulk_action_set_prices( $data, $product_id, $variations );
					break;
				case 'msrp_clear_prices':
					return $this->bulk_action_clear_prices( $data, $product_id, $variations );
					break;
				default:
					return;
					break;
			}
		}

		/**
		 * Update a set of variations with a given MSRP price.
		 */
		private function bulk_action_set_prices( $data, $product_id, $variations ) {
			if ( ! isset( $data['value'] ) ) {
				return;
			}
			foreach ( $variations as $variation_id ) {
				update_post_meta( $variation_id, '_msrp', $data['value'] );
			}
		}

		/**
		 * Clear the MSRP prices off a set of variations.
		 */
		private function bulk_action_clear_prices( $data, $product_id, $variations ) {
			foreach ( $variations as $variation_id ) {
				delete_post_meta( $variation_id, '_msrp' );
			}
		}

		/**
		 * From:
		 * https://github.com/skyverge/wc-plugin-compatibility/
		 */
		private function get_wc_version() {
			if ( defined( 'WC_VERSION' ) && WC_VERSION ) {
				return WC_VERSION;
			}
			if ( defined( 'WOOCOMMERCE_VERSION' ) && WOOCOMMERCE_VERSION ) {
				return WOOCOMMERCE_VERSION;
			}
			return null;
		}

		/**
		 * From:
		 * https://github.com/skyverge/wc-plugin-compatibility/
		 */
		private function is_wc_2_1() {
			return version_compare( $this->get_wc_version(), '2.1-beta', '>' );
		}

		private function enqueue_js() {
			$suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';
			wp_enqueue_script( 'woocommerce_msrp_admin', plugins_url( "js/admin{$suffix}.js", __FILE__ ), array( 'jquery' ) );
		}
	}
}

$woocommerce_msrp_admin = new woocommerce_msrp_admin();
