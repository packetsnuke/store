<?php
/**
 * Exit if accesses directly
 */
defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Pie_WCWL_Custom_Tab' ) ) {
	/**
	 * Pie_WCWL_Custom_Tab
	 *
	 * @package WooCommerce Waitlist
	 */
	class Pie_WCWL_Custom_Tab {

		private $product;
		private $waitlist;

		/**
		 * Assigns the settings that have been passed in to the appropriate parameters
		 *
		 * @access protected
		 *
		 * @param  object $product current product
		 */
		function __construct( $product ) {
			// Init
			$this->product = $product;
			$this->setup_text_strings();
			// Setup panel
			add_action( 'woocommerce_product_write_panel_tabs', array( &$this, 'custom_tab_options_tab' ) );
			add_action( 'woocommerce_product_data_panels', array( &$this, 'custom_tab_options' ) );
			// Update waitlists
			add_action( 'save_post', array( &$this, 'update_waitlist_for_simple_product' ) );
			global $woocommerce;
			if ( version_compare( $woocommerce->version, '2.4.0', '<' ) ) {
				add_action( 'woocommerce_process_product_meta_variable', array( &$this, 'save_variable_product_data', ), 10, 1 );
				add_action( 'woocommerce_process_product_meta_variable-subscription', array( &$this, 'save_variable_product_data', ), 10, 1 );
			} else {
				add_action( 'woocommerce_process_product_meta_variable', array( &$this, 'update_waitlists_for_variations', ), 10, 1 );
				add_action( 'woocommerce_process_product_meta_variable-subscription', array( &$this, 'update_waitlists_for_variations', ), 10, 1 );
			}
			// Scripts and styles
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
		}

		/**
		 * Check if product is out of stock
		 * Checks to see if at least one variation is out of stock if variable product
		 *
		 * @param  object $product current product
		 *
		 * @return bool
		 */
		public function product_is_out_of_stock( $product ) {
			if ( WooCommerce_Waitlist_Plugin::is_variable( $product ) ) {
				return $this->variation_is_out_of_stock( $product );
			}
			if ( WooCommerce_Waitlist_Plugin::is_simple( $product ) ) {
				return ! $product->is_in_stock();
			} else {
				return false;
			}
		}

		/**
		 * Check that at least one variation is out of stock before displaying waitlist
		 *
		 * @param  object $product current product
		 *
		 * @return bool
		 */
		public function variation_is_out_of_stock( $product ) {
			$variations = $product->get_available_variations();
			foreach ( $variations as $variation ) {
				if ( ! $variation['is_in_stock'] ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Enqueue any styles and scripts used for the custom tab
		 *
		 * @access public
		 * @return void
		 */
		public function enqueue_scripts() {
			wp_enqueue_style( 'wcwl_admin_custom_tab_css', ULTIMATEWOO_MODULES_URL . '/waitlist/includes/css/wcwl_admin_custom_tab.css' );
			wp_enqueue_script( 'wcwl_admin_custom_tab_js', ULTIMATEWOO_MODULES_URL . '/waitlist/includes/js/wcwl_admin_custom_tab.js', array(), '1.0.0', true );
			wp_localize_script( 'wcwl_admin_custom_tab_js', 'wcwl_tab', array( 'invalid_email' => __( 'Email address is invalid', 'ultimatewoo-pro' ) ) );
		}

		/**
		 * Add custom waitlist tab to the product
		 */
		public function custom_tab_options_tab() {
			?>
			<li class="wcwl_waitlist_tab">
				<a href="#wcwl_waitlist_data"><span><?php _e( 'Waitlists', 'woocommerce_waitlist' ); ?></span></a>
			</li>
			<?php
		}

		/**
		 * Output the HTML required for the custom tab
		 */
		public function custom_tab_options() {
			?>
			<div id="wcwl_waitlist_data" class="panel woocommerce_options_panel"><?php
				if ( WooCommerce_Waitlist_Plugin::is_variable( $this->product ) ) {
					$this->build_custom_tab_for_variable();
				} else {
					$this->build_custom_tab();
				}
				echo $this->return_link_for_archive();
				?>
			</div>
			<?php
		}

		/**
		 * Return link for archive page for this product
		 *
		 * @return string html required for archive link
		 */
		public function return_link_for_archive() {
			return '<div class="wcwl_archive_wrapper"><a class="wcwl_view_archive_link" href="' . admin_url( '?page=wcwl-waitlist-archive&product_id=' . Pie_WCWL_Compatibility::get_product_id( $this->product ) ) . '" >' . esc_html( apply_filters( 'wcwl_waitlist_view_waitlist_archive_text', $this->view_waitlist_archive_text ) ) . '</a></div>';
		}

		/**
		 * Output required HTML for the custom tab for simple products
		 *
		 * @access public
		 * @return void
		 */
		public function build_custom_tab() {
			$this->waitlist = new Pie_WCWL_Waitlist( $this->product );
			$users          = $this->waitlist->waitlist;
			if ( ! empty( $users ) || ! $this->product->is_in_stock() ) {
				echo '<div class="wcwl_product_tab_wrap">';
				if ( empty( $users ) ) {
					echo '<p class="wcwl_no_users_text">' . esc_html( apply_filters( 'wcwl_empty_waitlist_introduction', $this->empty_waitlist_introduction ) ) . '</p>';
					echo $this->return_option_to_add_user( $this->waitlist );
				} else {
					echo '<p class="wcwl_intro_tab">' . esc_html( apply_filters( 'wcwl_waitlist_introduction', $this->waitlist_introduction ) ) . '</p>';
					echo '<div id="wcwl_waitlist_tab"><table class="widefat wcwl_product_tab"><tr>';
					echo '<th>' . __( 'User', 'ultimatewoo-pro' ) . '</th>';
					echo '<th>' . __( 'Added', 'ultimatewoo-pro' ) . '</th>';
					echo '<th>' . __( 'Email', 'ultimatewoo-pro' ) . '</th>';
					echo '<th>' . __( 'Remove', 'ultimatewoo-pro' ) . '</th></tr>';
					foreach ( $users as $user_id => $date_added ) {
						echo $this->return_user_info( $user_id, $date_added, $this->waitlist );
					}
					echo '</table></div>';
					echo $this->return_option_to_add_user( $this->waitlist );
					echo '<p><div class="dashicons dashicons-email-alt wcwl_email_all_tab"></div><a href="' . esc_url_raw( $this->get_mailto_link_content( $this->waitlist ) ) . '" >' . esc_html( $this->email_all_users_on_list_text ) . '</a></p>';
				}
				echo '</div>';
			} else {
				echo '<p id="wcwl_in_stock_notice" class="wcwl_in_stock_notice">' . esc_html( apply_filters( 'wcwl_waitlist_variation_instock_introduction', $this->product_instock_intro ) ) . '</p>';
			}
		}

		/**
		 * Output required HTML for the custom tab for variable products
		 */
		public function build_custom_tab_for_variable() {
			$children = $this->product->get_available_variations();
			foreach ( $children as $key => $child ) {
				$variation          = wc_get_product( $child['variation_id'] );
				if ( $variation ) {
					$variation_waitlist = new Pie_WCWL_Waitlist( $variation );
					$users              = $variation_waitlist->waitlist;
					if ( ! empty( $users ) || ! $variation->is_in_stock() ) {
						echo '<div id="wcwl_variation_' . $child['variation_id'] . '" class="wcwl_product_tab_wrap">';
						echo '<div class="wcwl_header_wrap"><h3>' . $this->return_variation_tab_title( $variation_waitlist, $child ) . '</h3></div>';
						echo '<div class="wcwl_body_wrap">';
						if ( empty( $users ) ) {
							echo '<p class="wcwl_no_users_text">' . esc_html( apply_filters( 'wcwl_empty_waitlist_introduction', $this->empty_waitlist_introduction ) ) . '</p>';
							echo $this->return_option_to_add_user( $variation_waitlist );
						} else {
							echo '<p class="wcwl_intro_tab">' . esc_html( apply_filters( 'wcwl_waitlist_introduction', $this->waitlist_introduction ) ) . '</p>';
							echo '<div id="wcwl_waitlist_tab"><table class="widefat wcwl_product_tab"><tr>';
							echo '<th>' . __( 'User', 'ultimatewoo-pro' ) . '</th>';
							echo '<th>' . __( 'Added', 'ultimatewoo-pro' ) . '</th>';
							echo '<th>' . __( 'Email', 'ultimatewoo-pro' ) . '</th>';
							echo '<th>' . __( 'Remove', 'ultimatewoo-pro' ) . '</th></tr>';
							foreach ( $users as $user_id => $date_added ) {
								echo $this->return_user_info( $user_id, $date_added, $variation_waitlist );
							}
							echo '</table></div>';
							echo $this->return_option_to_add_user( $variation_waitlist );
							echo '<p><div class="dashicons dashicons-email-alt wcwl_email_all_tab"></div><a href="' . esc_url_raw( $this->get_mailto_link_content( $variation_waitlist ) ) . '" >' . esc_html( $this->email_all_users_on_list_text ) . '</a></p>';
						}
						echo '</div></div>';
					}
				}
				unset( $children[ $key ] );
			}
			echo '<p id="wcwl_in_stock_notice" class="wcwl_in_stock_notice">' . esc_html( apply_filters( 'wcwl_waitlist_variation_instock_introduction', $this->variable_instock_intro ) ) . '</p>';
		}

		/**
		 * Return title to be applied to the custom tab for variations
		 *
		 * @access public
		 *
		 * @param $waitlist
		 * @param $variation
		 *
		 * @return string
		 */
		public function return_variation_tab_title( $waitlist, $variation ) {
			$title          = $this->get_variation_name( $variation );
			$variable_title = sprintf( $title . ' : %d', count( $waitlist->waitlist ) );

			return $variable_title;
		}

		/**
		 * Get the name of the variation that matches the given ID - returning each attribute
		 * To be used as the title for each variation waitlist on the tab
		 *
		 * @param  int $variation the current variation
		 *
		 * @access public
		 * @return string the attribute of the required variation
		 */
		public function get_variation_name( $variation ) {
			$title      = '#' . $variation['variation_id'];
			$attributes = $variation['attributes'];
			if ( ! empty( $attributes ) ) {
				$title .= ' - ';
				$keys     = array_keys( $attributes );
				$last_key = end( $keys );
				foreach ( $attributes as $key => $attribute ) {
					$title .= ucwords( $attribute );
					if ( $key != $last_key ) {
						$title .= ', ';
					}
				}
			}

			return $title;
		}

		/**
		 * Return table row for current user to add to waitlist tab
		 *
		 * @param $user
		 * @param $date
		 * @param $waitlist
		 *
		 * @return string html for table elements
		 * @internal param $user_id
		 */
		public function return_user_info( $user, $date, $waitlist ) {
			$date = is_numeric( $date ) ? date( 'd M, y', $date ) : '-';
			$user = get_user_by( 'id', $user );

			return '<tr>
					<td><strong><a title="' . esc_attr( $this->view_user_profile_text ) . '" href="' . admin_url( 'user-edit.php?user_id=' . $user->ID ) . '">' . $user->display_name . '</a></strong></td>
					<td>' . $date . '</td>
					<td><a href="mailto:' . $user->user_email . '" title="' . esc_attr( $this->email_user_text ) . '" ><div class="dashicons dashicons-email-alt"></div></a></td>
					<td><input class="wcwl_remove_check_tab" type="checkbox" name="' . WCWL_SLUG . '_unregister_' . $waitlist->product_id . '_tab[]" value="' . $user->ID . '" /></td>
				</tr>';
		}

		/**
		 * Return option to add user using waitlist tab
		 *
		 * @param $waitlist
		 *
		 * @return string html for table elements
		 */
		public function return_option_to_add_user( $waitlist ) {
			$id = $waitlist->product_id;

			return '<div class="wcwl_add_new_emails_tab wcwl_reveal_tab" >
					<p class="wcwl_add_user_link_tab"><a href="#" onclick="return false;">Add new user</a></p>
					<p class="wcwl_hidden_tab">' . $this->must_update_text . '</p>
					<p class="wcwl_hidden_tab wcwl_emails_tab" >
					  <input class="wcwl_email_text_tab" type="email" name="' . WCWL_SLUG . '_add_email_tab_' . $id . '" />
					  <input type="button" class="button wcwl_email_button_tab" value="Add"></p>
					</p>
					<input type="text" name="' . WCWL_SLUG . '_email_list_' . $id . '_tab" class="wcwl_email_list_tab" style="display:none;" ></td>
                 </div>
                 <table class="wcwl_new_users_tab" ><tbody></tbody></table>';
		}

		/**
		 * Carries out checks to make sure user is allowed to save current product then modifies waitlist accordingly
		 *
		 * Check whether manage stock is checked - if so use stock quantity to determine whether product is in or out of
		 * stock
		 *
		 * @hooked action save_post
		 *
		 * @param  int $post_id current post ID
		 */
		public function update_waitlist_for_simple_product( $post_id ) {
			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
			if ( WooCommerce_Waitlist_Plugin::is_simple( $this->product ) ) {
				$this->update_waitlist( $post_id );
			}
		}

		/**
		 * Updates the waitlist for each variation when post is saved
		 *
		 * $_POST array is different before WC 2.2.0 and so this needs to be checked in order to find stock status
		 *
		 * @hooked action woocommerce_process_product_meta_variable
		 */
		public function save_variable_product_data() {
			if ( isset( $_POST['variable_post_id'] ) ) {
				$variations = $_POST['variable_post_id'];
				$this->update_variation_waitlists( $variations );
			}
		}

		/**
		 * Updates the waitlist for each variation when post is saved
		 *
		 * $_POST array is different again after WC 2.4.0 so this function is only hooked for these versions
		 *
		 * @hooked action woocommerce_process_product_meta_variable
		 */
		public function update_waitlists_for_variations() {
			$variations = $this->product->get_children();
			if ( $variations ) {
				$this->update_variation_waitlists( $variations );
			}
		}

		/**
		 * Run through each updated variation and update the waitlist as required
		 *
		 * @param $variations
		 */
		public function update_variation_waitlists( $variations ) {
			for ( $i = 0; $i < sizeof( $variations ); $i ++ ) {
				$variation_id = (int) $variations[ $i ];
				$this->update_waitlist( $variation_id );
			}
		}

		/**
		 * Update waitlist by adding/removing users as defined in the admin
		 *
		 * @param $product_id
		 */
		public function update_waitlist( $product_id ) {
			$product  = wc_get_product( $product_id );
			if ( $product ) {
				$waitlist = new Pie_WCWL_Waitlist( $product );
				$this->remove_users_from_waitlist( $waitlist );
				$this->add_users_to_waitlist( $waitlist );
			}
		}

		/**
		 * Removes selected users from the waitlist
		 *
		 * @access public
		 *
		 * @param $waitlist
		 */
		public function remove_users_from_waitlist( $waitlist ) {
			$value = isset( $_POST[ 'woocommerce_waitlist_unregister_' . $waitlist->product_id . '_tab' ] ) ? $_POST[ 'woocommerce_waitlist_unregister_' . $waitlist->product_id . '_tab' ] : '';
			if ( '' == $value || empty( $value ) || ! is_array( $value ) ) {
				return;
			}
			foreach ( $value as $user ) {
				$waitlist->unregister_user( get_user_by( 'id', $user ) );
			}
			$waitlist->save_waitlist();
		}

		/**
		 * Adds the entered email to the waitlist for the current product/variation
		 *
		 * @access public
		 *
		 * @param $waitlist
		 */
		public function add_users_to_waitlist( $waitlist ) {
			$value = isset( $_POST[ 'woocommerce_waitlist_email_list_' . $waitlist->product_id . '_tab' ] ) ? $_POST[ 'woocommerce_waitlist_email_list_' . $waitlist->product_id . '_tab' ] : '';
			if ( '' == $value || empty( $value ) ) {
				return;
			}
			$emails = array_unique( explode( ',', $value ) );
			foreach ( $emails as $email ) {
				$email = trim( $email );
				if ( ! is_email( $email ) ) {
					continue;
				}
				$current_user = get_user_by( 'id', $waitlist->create_new_customer_from_email( $email ) );
				$waitlist->register_user( $current_user );
			}
		}

		/**
		 * Sets up text strings used by the Waitlist Custom Tab
		 *
		 * @access public
		 * @return void
		 */
		public function setup_text_strings() {
			$this->variation_tab_title              = __( 'Waitlist for variation - %1$s: %2$d', 'ultimatewoo-pro' );
			$this->waitlist_introduction            = __( 'The following users are currently on the waiting list for this product:', 'ultimatewoo-pro' );
			$this->empty_waitlist_introduction      = __( 'There are no users on the waiting list for this product.', 'ultimatewoo-pro' );
			$this->email_user_text                  = __( 'Email User', 'ultimatewoo-pro' );
			$this->view_user_profile_text           = __( 'View User Profile', 'ultimatewoo-pro' );
			$this->email_all_users_on_list_text     = __( 'Email all users on list', 'ultimatewoo-pro' );
			$this->remove_user_from_waitlist_text   = __( 'Remove user from waitlist', 'ultimatewoo-pro' );
			$this->remove_text                      = __( 'Remove:', 'ultimatewoo-pro' );
			$this->must_update_text                 = __( 'Product must be updated to save users onto the waitlist', 'ultimatewoo-pro' );
			$this->variable_instock_intro           = __( 'This product has no out of stock variations and there are no users registered on any variation waitlists.', 'ultimatewoo-pro' );
			$this->product_instock_intro            = __( 'This product is currently in stock and there are no users registered on the waitlist.', 'ultimatewoo-pro' );
			$this->view_waitlist_archive_text       = __( 'View archived waitlists', 'ultimatewoo-pro' );
			$this->persistent_waitlist_notification = __( 'Waitlists will remain visible regardless of stock status as persistent waitlists are currently enabled.', 'ultimatewoo-pro' );
		}

		/**
		 * Returns information needed for the 'email user' links in product tab
		 *
		 * @access private
		 *
		 * @param $waitlist
		 *
		 * @return string 'mailto' information required
		 */
		private function get_mailto_link_content( $waitlist ) {
			return 'mailto:' . get_option( 'woocommerce_email_from_address' ) . '?bcc=' . implode( ',', $waitlist->get_registered_users_email_addresses() );
		}
	}
}