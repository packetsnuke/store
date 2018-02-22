<?php
/**
 * Exit if accesses directly
 */
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'Pie_WCWL_Waitlist' ) ) {
	/**
	 * Pie_WCWL_Waitlist
	 *
	 * @package WooCommerce Waitlist
	 */
	class Pie_WCWL_Waitlist {

		/**
		 * Array of user IDs on the current waitlist
		 *
		 * @var array
		 */
		public $waitlist;
		/**
		 * An array of user objects
		 *
		 * @var array
		 */
		public $users;
		/**
		 * Product unique ID
		 *
		 * @var int
		 * @access public
		 */
		public $product_id;
		/**
		 * Array of the products parents. This could be variable/grouped or both
		 *
		 * @var array
		 * @access public
		 */
		public $parent_ids;

		/**
		 * Constructor function to hook up actions and filters and class properties
		 *
		 * @param $product
		 *
		 * @access   public
		 */
		public function __construct( $product ) {
			$this->setup_product( $product );
			$this->setup_waitlist();
			$this->setup_text_strings();
		}

		/**
		 * Setup product class variables
		 *
		 * @param $product
		 *
		 * @access   public
		 */
		public function setup_product( $product ) {
			$this->product_id = Pie_WCWL_Compatibility::get_product_id( $product );
			$this->parent_ids = Pie_WCWL_Compatibility::get_parent_id( $product );
		}

		/**
		 * Setup waitlist and users array
		 *
		 * Adjust old meta to new format ( $waitlist[user_id] = date_added )
		 *
		 * @access public
		 * @return void
		 */
		public function setup_waitlist() {
			$waitlist = get_post_meta( $this->product_id, WCWL_SLUG, true );
			if ( ! is_array( $waitlist ) || empty( $waitlist ) ) {
				$this->users    = array();
				$this->waitlist = array();
			} else {
				if ( $this->waitlist_has_new_meta() ) {
					$this->load_waitlist( $waitlist, 'new' );
				} else {
					$this->load_waitlist( $waitlist, 'old' );
				}
			}
		}

		/**
		 * Check if waitlist has been updated to the new meta format
		 *
		 * @return bool
		 */
		public function waitlist_has_new_meta() {
			$has_dates = get_post_meta( $this->product_id, WCWL_SLUG . '_has_dates', true );
			if ( $has_dates ) {
				return true;
			}

			return false;
		}

		/**
		 * Load up waitlist
		 *
		 * Meta has changed to incorporate the date added for each user so a check is required
		 * If waitlist has old meta we want to bring this up to speed
		 *
		 * @param $waitlist
		 * @param $type
		 */
		public function load_waitlist( $waitlist, $type ) {
			if ( $type == 'old' ) {
				foreach ( $waitlist as $user_id ) {
					if ( get_user_by( 'id', $user_id ) != false ) {
						$this->users[] = get_user_by( 'id', $user_id );
					}
					$this->waitlist[ $user_id ] = 'unknown';
				}
			} else {
				foreach ( $waitlist as $user_id => $date_added ) {
					if ( get_user_by( 'id', $user_id ) != false ) {
						$this->users[] = get_user_by( 'id', $user_id );
					}
				}
				$this->waitlist = $waitlist;
			}
		}

		/**
		 * Save the current waitlist into the database
		 *
		 * Update meta to notify us that meta format has been updated
		 *
		 * @return void
		 */
		public function save_waitlist() {
			update_post_meta( $this->product_id, WCWL_SLUG, $this->waitlist );
			update_post_meta( $this->product_id, WCWL_SLUG . '_has_dates', true );
		}

		/**
		 * For some bizarre reason around 1.2.0, this function has started emitting notices. It is caused by the original
		 * assignment of WCWL_Frontend_UI->User being set to false when a user is not logged in. All around the application,
		 * this is now being called on as an object.
		 *
		 * @param $user
		 *
		 * @return bool Whether or not the User is registered to this waitlist, if they are a valid user
		 *
		 * @access   public
		 */
		public function user_is_registered( $user ) {
			return $user && array_key_exists( $user->ID, $this->waitlist );
		}

		/**
		 * Remove user from the current waitlist
		 *
		 * @param $user
		 *
		 * @return bool true|false depending on success of removal
		 *
		 * @access   public
		 */
		public function unregister_user( $user ) {
			if ( $this->user_is_registered( $user ) ) {
				do_action( 'wcwl_before_remove_user_from_waitlist', $this->product_id, $user );
				unset( $this->waitlist[ $user->ID ] );
				do_action( 'wcwl_after_remove_user_from_waitlist', $this->product_id, $user );
				$this->update_waitlist_count();

				return true;
			}

			return false;
		}

		/**
		 * For some bizarre reason around 1.2.0, this function has started emitting notices. It is caused by the original
		 * assignment of WCWL_Frontend_UI->User being set to false when a user is not logged in. All around the application,
		 * this is now being called on as an object.
		 *
		 * @param $user
		 *
		 * @return bool
		 *
		 * @access   public
		 */
		public function register_user( $user ) {
			if ( $user && ! $this->user_is_registered( $user ) ) {
				do_action( 'wcwl_before_add_user_to_waitlist', $this->product_id, $user );
				$this->waitlist[ $user->ID ] = strtotime( 'now' );
				do_action( 'wcwl_after_add_user_to_waitlist', $this->product_id, $user );
				$this->update_waitlist_count();

				return true;
			}

			return false;
		}

		/**
		 * Adjust waitlist count in database when a user is registered/unregistered
		 * $product->get_parent_id() works for grouped products but not variables
		 * $product->parent is available for variables
		 */
		public function update_waitlist_count() {
			update_post_meta( $this->product_id, WCWL_SLUG . '_count', count( $this->waitlist ) );
			if ( ! empty( $this->parent_ids ) ) {
				foreach ( $this->parent_ids as $parent_id ) {
					$parent = wc_get_product( $parent_id );
					$count  = $this->get_number_of_registrations( Pie_WCWL_Compatibility::get_product_id( $parent ), $this->product_id );
					update_post_meta( Pie_WCWL_Compatibility::get_product_id( $parent ), WCWL_SLUG . '_count', $count + count( $this->waitlist ) );
				}
			}
			$this->save_waitlist();
		}

		/**
		 * Return number of users on requested waitlist, required for variable/grouped waitlist counts
		 *
		 * @param  int $product    parent product ID
		 * @param  int $exclude_id current child product ID to be excluded from count
		 *
		 * @access public
		 * @static
		 * @return int
		 */
		public function get_number_of_registrations( $product, $exclude_id ) {
			$product  = wc_get_product( $product );
			$waitlist = array();
			foreach ( $product->get_children() as $child_id ) {
				if ( $exclude_id == $child_id ) {
					continue;
				}
				$current_waitlist = get_post_meta( $child_id, WCWL_SLUG, true );
				$current_waitlist = is_array( $current_waitlist ) ? $current_waitlist : array();
				$waitlist         = array_merge( $waitlist, $current_waitlist );
			}

			return count( $waitlist );
		}

		/**
		 * Return an array of the users on the current waitlist
		 *
		 * @access public
		 * @return array user_ids
		 */
		public function get_registered_users() {
			return $this->users;
		}

		/**
		 * Return an array of users emails from current waitlist
		 *
		 * @access public
		 * @return array user_emails
		 * @since  1.0.2
		 */
		public function get_registered_users_email_addresses() {
			return wp_list_pluck( $this->get_registered_users(), 'user_email' );
		}

		/**
		 * Checks if user is registered, if not creates a new customer and sends welcome email
		 *
		 * This function overrides woocommerce options to ensure that the user is created when joining the waitlist,
		 * options are reset afterwards
		 *
		 * @param  string $email users email address
		 *
		 * @access public
		 * @return object $current_user the customer's user object
		 * @since  1.3
		 */
		public function create_new_customer_from_email( $email ) {
			if ( email_exists( $email ) ) {
				$current_user = email_exists( $email );
			} else {
				add_filter( 'pre_option_woocommerce_registration_generate_password', array( $this, 'return_option_setting_yes', ), 10 );
				add_filter( 'pre_option_woocommerce_registration_generate_username', array( $this, 'return_option_setting_yes', ), 10 );
				$current_user = $this->create_new_customer( $email );
				remove_filter( 'pre_option_woocommerce_registration_generate_password', array( $this, 'return_option_setting_yes', ), 10 );
				remove_filter( 'pre_option_woocommerce_registration_generate_username', array( $this, 'return_option_setting_yes', ), 10 );
			}

			return $current_user;
		}

		/**
		 * Create new customer using the given email and send user a welcome email with login details
		 *
		 * This function is required before woocommerce v2.1 as handling user creation is handled differently from then
		 *
		 * @access     public
		 *
		 * @param  string $email users email address
		 *
		 * @return int $user_id current user ID
		 * @since      1.3
		 */
		public function create_new_customer( $email ) {
			$username = sanitize_user( current( explode( '@', $email ) ) );
			// Ensure username is unique
			$append     = 1;
			$o_username = $username;
			while ( username_exists( $username ) ) {
				$username = $o_username . $append;
				$append ++;
			}
			$password = wp_generate_password();
			$userdata = array(
				'user_login' => $username,
				'user_email' => $email,
				'user_pass'  => $password,
				'role'       => 'customer',
			);
			$user_id  = wp_insert_user( $userdata );
			// send the user a confirmation email and their login details
			if ( is_wp_error( $user_id ) ) {
				return new WP_Error( 'registration-error', '<strong>' . __( 'ERROR', 'woocommerce' ) . '</strong>: ' . __( 'Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.', 'woocommerce' ) );
			}
			do_action( 'woocommerce_created_customer', $user_id, $userdata, true );

			return $user_id;
		}

		/**
		 * Returns 'yes' for options to be overidden when creating new users
		 *
		 * @access public
		 * @return string 'yes'
		 * @since  1.3
		 */
		public function return_option_setting_yes() {
			return 'yes';
		}

		/**
		 * Triggers instock notification email to each user on the waitlist for a product, then deletes the waitlist
		 *
		 * @param int $post_id
		 *
		 * @access public
		 * @return void
		 */
		public function waitlist_mailout( $post_id ) {
			if ( ! empty( $this->waitlist ) ) {
				global $woocommerce;
				$woocommerce->mailer();
				foreach ( $this->waitlist as $user_id => $date_added ) {
					if ( ! WooCommerce_Waitlist_Plugin::automatic_mailouts_are_disabled( $post_id ) ) {
						do_action( 'wcwl_mailout_send_email', $user_id, $post_id );
					}
					if ( WooCommerce_Waitlist_Plugin::persistent_waitlists_are_disabled( $post_id ) ) {
						$user = get_user_by( 'id', $user_id );
						$this->unregister_user( $user );
					}
				}
			}
		}

		/**
		 * Create message to be sent out to user
		 *
		 * @param  int $user_id ID of the user we are sending to
		 * @param  int $post_id ID of the product that is now back in stock
		 *
		 * @access public
		 * @return string the completed email message
		 * @since  1.3
		 */
		public function create_message_for_mailout( $user_id, $post_id ) {
			if ( WooCommerce_Waitlist_Plugin::is_variation( wc_get_product( $post_id ) ) ) {
				$post         = get_post( $post_id );
				$permalink_id = $post->post_parent;
			}
			$user          = get_user_by( 'id', $user_id );
			$username      = $user->display_name;
			$product_title = get_the_title( $post_id );
			$product_link  = get_permalink( isset( $permalink_id ) ? $permalink_id : $post_id );
			$message       = '<p>' . apply_filters( 'wcwl_email_salutation', sprintf( $this->email_salutation, $username ) ) . '</p><p>';
			$message      .= apply_filters( 'wcwl_email_product_back_in_stock_text', sprintf( $this->specific_product_back_in_stock_text, $product_title, get_bloginfo( 'title' ) ) ) . '. ';
			$message      .= apply_filters( 'wcwl_email_mailout_disclaimer_text', $this->mailout_disclaimer_text ) . '. ';
			$message      .= apply_filters( 'wcwl_email_visit_this_link_to_purchase_text', sprintf( $this->visit_this_link_to_purchase_text, $product_title, $product_link, $product_link ) );
			$message      .= '</p><p>' . apply_filters( 'wcwl_email_mailout_signoff', $this->mailout_signoff ) . get_bloginfo( 'title' ) . '</p>';

			return apply_filters( 'wcwl_mailout_html', $message );
		}

		/**
		 * Sets up the text strings used by the plugin
		 *
		 * @hooked action plugins_loaded
		 * @access private
		 * @return void
		 */
		private function setup_text_strings() {
			$this->mailout_signoff                     = _x( 'Regards,<br>', 'Email signoff', 'ultimatewoo-pro' );
			$this->mailout_disclaimer_text             = __( 'You have been sent this email because your email address was registered on a waitlist for this product', 'ultimatewoo-pro' );
			$this->visit_this_link_to_purchase_text    = __( 'If you would like to purchase %1$s please visit the following link: <a href="%2$s">%3$s</a>', 'ultimatewoo-pro' );
			$this->specific_product_back_in_stock_text = __( '%1$s is now back in stock at %2$s', 'ultimatewoo-pro' );
			$this->email_salutation                    = _x( 'Hi %s,', 'Email Salutation', 'ultimatewoo-pro' );
			$this->generic_product_back_in_stock_text  = __( 'A product you are waiting for is back in stock', 'ultimatewoo-pro' );
			$this->join_waitlist_button_text           = __( 'Join waitlist', 'ultimatewoo-pro' );
		}
	}
}