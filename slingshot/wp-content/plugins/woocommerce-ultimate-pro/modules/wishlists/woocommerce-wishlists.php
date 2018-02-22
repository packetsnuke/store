<?php
/*
  Copyright: © 2009-2017 Lucas Stark
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( is_woocommerce_active() ) {

	class WC_Wishlists_Plugin {

		/**
		 * @var string
		 * This is the database version for Wishlists, does not reflect the plugin version.
		 */
		var $version = '2.0.0';

		/**
		 * @var string
		 * This is a version id for the cron job for notifications.
		 */
		var $cron_version = '1.0.0';

		/**
		 * @var array
		 */
		private $_body_classes = array();

		/**
		 * EduPress Recruiter Constructor.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {

			// Define constants
			define( 'WC_WISHLISTS_VERSION', $this->version );

			load_plugin_textdomain( 'wc_wishlist', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

			$this->includes();

			// Installation
			if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
				$this->install();
			}

			add_filter( 'post_link', array( &$this, 'post_link' ), 10, 2 );
			add_filter( 'post_type_archive_link', array( &$this, 'post_type_archive_link' ), 10, 2 );
			add_filter( 'post_type_link', array( &$this, 'post_link' ), 10, 2 );

			add_action( 'woocommerce_init', array( &$this, 'init' ), 0 );
			add_action( 'woocommerce_init', array( &$this, 'on_woocommerce_init' ), 0 );

			add_action( 'init', array( &$this, 'init_taxonomy' ), 8 );

			add_action( 'template_redirect', array( &$this, 'process_request' ), 9 );
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_assets' ) );

			add_action( 'template_redirect', array( &$this, 'add_session_message' ) );

			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			add_action( 'wp_head', array( $this, 'wp_head' ) );


			add_action( 'wc_quick_view_before_single_product', array( &$this, 'bind_wishlist_button' ), 0 );
			add_action( 'woocommerce_single_product_summary', array( &$this, 'bind_wishlist_button' ), 0 );


			add_action( 'wp_footer', array( $this, 'add_to_wishlist_options' ) );
		}

		public function includes() {
			include 'classes/class-wc-wishlist-compatibility.php';


			//Third Party Integrations
			include 'integrations/class-wpml-integration.php';

			/* Regular function includes */
			include 'woocommerce-wishlists-functions.php';
			include 'woocommerce-wishlists-api.php';

			/* Include Models */
			include 'classes/models/class-wc-wishlists-wishlist.php';
			include 'classes/models/class-wc-wishlists-wishlist-item-collection.php';


			/* Include Class Files */
			include 'classes/class-wc-wishlists-user.php';
			include 'classes/class-wc-wishlists-pages.php';
			include 'classes/class-wc-wishlists-settings.php';
			include 'classes/class-wc-wishlists-request-handler.php';
			include 'classes/class-wc-wishlists-messages.php';

			include 'shortcodes/shortcodes-init.php';

			include 'classes/class-wc-wishlist-cart.php';

			//Cron jobs - next release will have price change notifications.  Leaving the stubs in here for now.
			include 'classes/class-wc-wishlists-cron.php';

			WC_Wishlists_Cart::register();

			//Basic email verification.  Since 2.9.0
			include 'classes/class-wc-wishlists-email-verification.php';
			WC_Wishlists_Email_Verification::register();


			if ( is_admin() ) {

				include 'classes/class-wc-wishlists-admin-settings.php';

				include 'classes/class-wc-wishlists-admin-wishlist.php';

				//Activate the settings tab in the WooCommerce Settings area.
				WC_Wishlists_Settings_Admin::instance();
				WC_Wishlists_Wishlist_Admin::instance();

				//@see classes/class-wc-wishlists-admin-controller.php for further comments. 
				//include 'classes/class-wc-wishlists-admin-controller.php';
				//WC_Wishlists_Admin_Controller::register();
			}
		}

		public function init() {
			if ( ( !is_admin() || defined( 'DOING_AJAX' ) ) && !defined( 'DOING_CRON' ) ) {
				//Setup the wishlists user, loads or creates the correct wishlist key.
				WC_Wishlists_User::init();
			}

			if ( WC_Wishlist_Compatibility::is_wc_version_gte_2_6() ) {
				add_action( 'init', array( $this, 'account_wishlists_endpoints' ) );
				add_filter( 'query_vars', array( $this, 'account_wishlists_query_vars' ), 0 );
				add_filter( 'woocommerce_account_menu_items', array( $this, 'account_menu_item' ) );
				add_action( 'woocommerce_account_account-wishlists_endpoint', array(
					$this,
					'add_lists_to_account_page'
				) );
			} else {
				$admin_location = apply_filters( 'woocommerce_wishlists_account_location', 'after' );
				add_action( 'woocommerce_' . $admin_location . '_my_account', array(
					$this,
					'add_lists_to_account_page'
				) );
			}
		}


		/**
		 * Register new endpoint to use inside My Account page.
		 *
		 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
		 */
		public function account_wishlists_endpoints() {
			add_rewrite_endpoint( 'account-wishlists', EP_ROOT | EP_PAGES );
		}

		/**
		 * Add new query var.
		 *
		 * @param array $vars
		 *
		 * @return array
		 */
		function account_wishlists_query_vars( $vars ) {
			$vars[] = 'account-wishlists';

			return $vars;
		}


		public function account_menu_item( $items ) {
			// Remove the logout menu item.
			$logout = $items['customer-logout'];
			unset( $items['customer-logout'] );

			// Insert your custom endpoint.
			$items['account-wishlists'] = __( 'Wishlists', 'ultimatewoo-pro' );

			// Insert back the logout item.
			$items['customer-logout'] = $logout;

			return $items;
		}

		public function on_woocommerce_init() {
			include 'classes/class-wc-wishlist-compatibility-functions.php';
		}

		public function process_request() {
			if ( is_page() ) {
				if ( is_page( WC_Wishlists_Pages::get_page_id( 'view-a-list' ) ) && empty( $_GET['wlid'] ) ) {

					WC_Wishlist_Compatibility::wc_add_error( __( 'Please select a list first', 'ultimatewoo-pro' ) );

					wp_redirect( get_permalink( WC_Wishlists_Pages::get_page_id( 'find-a-list' ) ) );

					die();
				}

				if ( is_page( WC_Wishlists_Pages::get_page_id( 'edit-my-list' ) ) && empty( $_GET['wlid'] ) ) {

					WC_Wishlist_Compatibility::wc_add_error( __( 'Please select a list first', 'ultimatewoo-pro' ) );

					wp_redirect( get_permalink( WC_Wishlists_Pages::get_page_id( 'my-lists' ) ) );

					die();
				}
			}

			//Allow the request handler to handle any front end wishlist actions, such as creating a list, editing, etc...
			WC_Wishlists_Request_Handler::process_request();
		}

		public function enqueue_assets() {
			wp_enqueue_script( 'woocommerce-wishlists', self::plugin_url() . '/assets/js/woocommerce-wishlists.js', array( 'jquery' ) );
			wp_enqueue_script( 'bootstrap-modal', self::plugin_url() . '/assets/js/bootstrap-modal.js', array( 'jquery' ) );

			$wishlist_params = array(
				'root_url'     => untrailingslashit( get_site_url() ),
				'current_url'  => esc_url_raw( add_query_arg( array() ) ),
				'are_you_sure' => __( 'Are you sure?', 'ultimatewoo-pro' ),
			);

			wp_localize_script( 'woocommerce-wishlists', 'wishlist_params', apply_filters( 'woocommerce_wishlist_params', $wishlist_params ) );
			wp_enqueue_style( 'woocommerce-wishlists', self::plugin_url() . '/assets/css/woocommerce-wishlists.css' );
		}

		public function bind_wishlist_button() {
			$product = wc_get_product( get_the_ID() );

			$template_hook = 'woocommerce_after_add_to_cart_button';

			if ( $product->is_type( 'variable' ) ) {
				if ( WC_Wishlist_Compatibility::is_wc_version_gte_2_4() ) {
					//Use the 2.4 hook
					$template_hook = 'woocommerce_single_variation';
				}
			}

			$template_hook = apply_filters( 'woocommerce_wishlists_template_location', $template_hook, $product->get_id() );

			if ( ( $product->is_in_stock() || $product->backorders_allowed() ) && !$product->is_type( 'external' ) ) {
				add_action( $template_hook, array( $this, 'add_to_wishlist_button' ), 1000 );
				add_action( 'wc_cvo_after_single_variation', array( $this, 'add_to_wishlist_button' ) );
			} elseif ( $product->is_type( 'simple' ) ) {
				//Use woocommerce_simple_add_to_cart action from wc-template-functions.php file. 
				add_action( 'woocommerce_simple_add_to_cart', array( $this, 'add_wishlist_form' ) );
			} elseif ( $product->is_type( 'variable' ) ) {
				add_action( $template_hook, array( $this, 'add_to_wishlist_button' ) );
			} elseif ( $product->is_type( 'external' ) ) {
				add_action( $template_hook, array( $this, 'add_wishlist_form_to_external_products' ) );
			}
		}

		public function add_to_wishlist_button() {
			global $add_to_wishlist_args;
			if ( WC_Wishlists_Settings::get_setting( 'wc_wishlist_lists_enabled', 'enabled' ) == 'enabled' || WC_Wishlists_Settings::get_setting( 'wc_wishlist_registries_enabled', 'enabled' ) == 'enabled' ) {
				$guest_setting = WC_Wishlists_Settings::get_setting( 'wc_wishlist_guest_enabled', 'enabled' );
				if ( is_user_logged_in() || $guest_setting == 'enabled' || $guest_setting == 'registration_required' ) {
					$add_to_wishlist_args              = array();
					$add_to_wishlist_args['btn_class'] = array();

					$add_to_wishlist_id = false;

					$lists = false;
					if ( WC_Wishlist_Compatibility::WC()->session->has_session() ) {
						//echo 'HAS A WC SESSION';
						//echo WC_Wishlists_User::get_wishlist_key();
						$lists = WC_Wishlists_User::get_wishlists();
					} else {
						//echo 'NO WC SESSION';
					}

					if ( $lists ) {
						$add_to_wishlist_args['btn_class'][] = 'wl-add-to';
						$add_to_wishlist_args['single_id']   = '';
					} else {
						$add_to_wishlist_args['btn_class'][] = 'wl-add-to';
						$add_to_wishlist_args['btn_class'][] = 'wl-add-to-single';

						//Updated to redirect the user to the new list screen if auto generate guest lists are disabled. 
						////Default is to automatically generate a guest list with generic information. 
						$add_to_wishlist_args['single_id'] = WC_Wishlists_Settings::get_setting( 'wc_wishlist_autocreate', 'yes' ) == 'yes' ? '' : 'session';
					}

					if ( WC_Wishlists_Settings::get_setting( 'wc_wishlist_use_button', 'button' ) == 'button' ) {
						$add_to_wishlist_args['btn_class'][] = 'wl-add-but';
						$add_to_wishlist_args['btn_class'][] = 'button';
					} else {
						$add_to_wishlist_args['btn_class'][] = ' wl-add-link';
					}

					$add_to_wishlist_args['btn_class'][] = WC_Wishlists_Settings::get_setting( 'wc_wishlist_icon', '' );

					woocommerce_wishlists_get_template( 'add-to-wishlist-link.php' );

					//remove_action( 'catalog_visibility_after_alternate_add_to_cart_button', array($this, 'add_to_wishlist_button') );
					remove_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_to_wishlist_button' ) );
				}
			}
		}

		public function add_wishlist_form() {
			$product = wc_get_product( get_the_ID() );
			?>

            <form class="cart" method="post" enctype='multipart/form-data'
                  action="<?php echo add_query_arg( array( 'add-to-wishlist-itemid' => $product->get_id() ), $product->add_to_cart_url() ); ?>">
				<?php
				if ( !$product->is_sold_individually() ) {
					woocommerce_quantity_input( array(
						'min_value' => apply_filters( 'woocommerce_quantity_input_min', 1, $product ),
						'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->backorders_allowed() ? '' : $product->get_stock_quantity(), $product )
					) );
				}
				?>

                <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"/>
				<?php $this->add_to_wishlist_button(); ?>
            </form>

			<?php
		}

		public function add_wishlist_form_to_external_products() {
			$product = wc_get_product( get_the_ID() );
			?>

            <form class="cart" method="post" enctype='multipart/form-data'
                  action="<?php echo esc_url( add_query_arg( array( 'add-to-wishlist-itemid' => $product->get_id() ) ) ); ?>">
                <input type="hidden" name="quantity" value="1"/>
                <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"/>
				<?php $this->add_to_wishlist_button(); ?>
            </form>

			<?php
		}

		public function add_to_wishlist_options() {
			woocommerce_wishlists_get_template( 'add-to-wishlist-modal.php' );
		}

		public function add_to_wishlist_shop_options() {
			woocommerce_wishlists_get_template( 'add-to-wishlist-shop-modal.php' );
		}

		public function admin_menu() {

		}

		public function add_lists_to_account_page() {
			woocommerce_wishlists_get_template( 'my-account-lists.php' );
		}

		/**
		 * Install upon activation.
		 *
		 * @access public
		 * @return void
		 */
		function install() {
			include 'classes/class-wc-wishlists-installer.php';
			register_activation_hook( __FILE__, array( 'WC_Wishlists_Installer', 'activate' ) );
			WC_Wishlists_Installer::check_install();

			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

			if ( WC_Wishlists_Settings::get_setting( 'wc_wishlists_db_version_cron' ) != $this->cron_version ) {


				$enabled = WC_Wishlists_Settings::get_setting( 'wc_wishlist_notifications_enabled', false );
				if ( empty( $enabled ) ) {
					//Disable the notifications by default. 
					WC_Wishlists_Settings::set_setting( 'wc_wishlist_notifications_enabled', 'disabled' );
				}

				WC_Wishlists_Cron::register(); //Register the cron job. 
				WC_Wishlists_Settings::set_setting( 'wc_wishlists_db_version_cron', $this->cron_version );
			}
		}

		/**
		 * @since 1.7.0
		 * Schedule the cron job for notifications.
		 */
		public function activate() {
			if ( !wp_next_scheduled( 'wc_wishlists_cron' ) ) {
				wp_schedule_event( time(), 'twicedaily', 'wc_wishlists_cron' );
			}

			add_rewrite_endpoint( 'account-wishlists', EP_ROOT | EP_PAGES );
			flush_rewrite_rules();
		}

		/**
		 * @since 1.7.0
		 * Remove the cron job for notifications.
		 */
		public function deactivate() {
			wp_clear_scheduled_hook( 'wc_wishlists_cron' );
		}

		/** Register the taxonomy * */
		public function init_taxonomy() {
			if ( current_user_can( 'manage_woocommerce' ) ) {
				$show_in_menu = 'woocommerce';
			} else {
				$show_in_menu = true;
			}

			register_post_type( 'wishlist', array(
					'labels'              => array(
						'name'               => __( 'Wishlists', 'ultimatewoo-pro' ),
						'singular_name'      => __( 'Wishlist', 'ultimatewoo-pro' ),
						'add_new'            => __( 'Add Wishlist', 'ultimatewoo-pro' ),
						'add_new_item'       => __( 'Add New Wishlist', 'ultimatewoo-pro' ),
						'edit'               => __( 'Edit', 'woocommerce' ),
						'edit_item'          => __( 'Edit Wishlist', 'ultimatewoo-pro' ),
						'new_item'           => __( 'New Wishlist', 'ultimatewoo-pro' ),
						'view'               => __( 'View Wishlist', 'ultimatewoo-pro' ),
						'view_item'          => __( 'View Wishlist', 'ultimatewoo-pro' ),
						'search_items'       => __( 'Search Wishlists', 'ultimatewoo-pro' ),
						'not_found'          => __( 'No Wishlists found', 'ultimatewoo-pro' ),
						'not_found_in_trash' => __( 'No Wishlists found in trash', 'ultimatewoo-pro' ),
						'parent'             => __( 'Parent Wishlists', 'ultimatewoo-pro' )
					),
					'description'         => __( 'This is where customer wishlists are stored.', 'ultimatewoo-pro' ),
					'show_ui'             => true,
					'capability_type'     => 'post',
					'capabilities'        => array(
						'publish_posts'       => 'manage_woocommerce',
						'edit_posts'          => 'manage_woocommerce',
						'edit_others_posts'   => 'manage_woocommerce',
						'delete_posts'        => 'manage_woocommerce',
						'delete_others_posts' => 'manage_woocommerce',
						'read_private_posts'  => 'manage_woocommerce',
						'edit_post'           => 'manage_woocommerce',
						'delete_post'         => 'manage_woocommerce',
						'read_post'           => 'manage_woocommerce'
					),
					'public'              => true,
					'publicly_queryable'  => true,
					'exclude_from_search' => false,
					'hierarchical'        => false,
					'rewrite'             => false,
					'query_var'           => false,
					'supports'            => array( 'title', 'editor' ),
					'has_archive'         => true,
					'show_in_nav_menus'   => false,
					'show_in_menu'        => $show_in_menu
				)
			);
		}

		public function post_link( $url, $post ) {
			if ( $post->post_type == 'wishlist' ) {
				$url = WC_Wishlists_Pages::get_url_for( 'view-a-list' ) . '?wlid=' . $post->ID;
			}

			return $url;
		}

		public function post_type_archive_link( $link, $post_type ) {
			if ( $post_type == 'wishlist' ) {
				$link = WC_Wishlists_Pages::get_url_for( 'wishlists' );
			}

			return $link;
		}

		/** Helper functions ***************************************************** */

		/**
		 * Get the plugin url.
		 *
		 * @access public
		 * @return string
		 */
		public static function plugin_url() {
			return ULTIMATEWOO_MODULES_URL . '/wishlists';
		}

		/**
		 * Get the plugin path.
		 *
		 * @access public
		 * @return string
		 */
		public static function plugin_path() {
			return ULTIMATEWOO_MODULES_DIR . '/wishlists';
		}

		/** Nonces and Form Utitlity *************************************************************** */
		public static function action_field( $action ) {
			return '<input type="hidden" name="wlaction" value="' . esc_attr( $action ) . '" />';
		}

		/**
		 * Return a nonce field.
		 *
		 * @access public
		 *
		 * @param mixed $action
		 * @param bool $referer (default: true)
		 * @param bool $echo (default: true)
		 *
		 * @return string
		 */
		static function nonce_field( $action, $referer = true, $echo = true ) {
			return wp_nonce_field( 'wc-wishlists-' . $action, '_n', $referer, $echo );
		}

		/**
		 * Return a url with a nonce appended.
		 *
		 * @access public
		 *
		 * @param mixed $action
		 * @param string $url (default: '')
		 *
		 * @return string
		 */
		static function nonce_url( $action, $url = '' ) {
			return esc_url( add_query_arg( '_n', wp_create_nonce( 'wc-wishlists-' . $action ), $url ) );
		}

		/**
		 * Check a nonce and sets woocommerce error in case it is invalid.
		 *
		 * To fail silently, set the error_message to an empty string
		 *
		 * @access public
		 *
		 * @param string $name the nonce name
		 * @param string $action then nonce action
		 * @param string $method the http request method _POST, _GET or _REQUEST
		 * @param string $error_message custom error message, or false for default message, or an empty string to fail silently
		 *
		 * @return bool
		 */
		static function verify_nonce( $action, $method = '_POST', $error_message = false ) {
			$name   = '_n';
			$action = 'wc-wishlists-' . $action;

			if ( $error_message === false ) {
				$error_message = __( 'Action failed. Please refresh the page and retry.', 'ultimatewoo-pro' );
			}

			if ( !in_array( $method, array( '_GET', '_POST', '_REQUEST' ) ) ) {
				$method = '_POST';
			}

			if ( !isset( $_REQUEST[ $name ] ) ) {
				wp_die( $error_message );
			}

			if ( isset( $_REQUEST[ $name ] ) && wp_verify_nonce( $_REQUEST[ $name ], $action ) ) {
				return true;
			}

			wp_die( $error_message );
		}

		public function add_session_message() {
			$session_items = WC_Wishlists_Wishlist_Item_Collection::get_items_from_session();
			if ( !is_page( WC_Wishlists_Pages::get_page_id( 'create-a-list' ) ) ) {
				if ( $session_items && count( $session_items ) ) {
					$message = sprintf( __( 'You have %s items ready to move to a new list.  <a href="%s">Create a list</a>', 'ultimatewoo-pro' ), count( $session_items ), WC_Wishlists_Pages::get_url_for( 'create-a-list' ) );
					if ( !wc_has_notice( $message ) ) {
						wc_add_notice( $message );
					}
				}
			} else {
				if ( $session_items && count( $session_items ) ) {
					//WC_Wishlist_Compatibility::wc_add_notice( sprintf( __( '%s items ready to move to a new list.', 'ultimatewoo-pro' ), count( $session_items ), WC_Wishlists_Pages::get_url_for( 'create-a-list' ) ) );
				}
			}
		}

		/** Body Classes ********************************************************* */
		public function wp_head() {
			global $post;
			if ( $post ) {
				if ( ( WC_Wishlists_Pages::is_wishlist_page( $post->post_name ) || is_product() ) ) {

					if ( WC_Wishlists_Settings::get_setting( 'wc_wishlists_use_custom_button_colors', 'no' ) == 'yes' ) {

						$colors = array_map( 'esc_attr', (array) get_option( 'wishlist_frontend_css_colors' ) );
						// Defaults
						if ( empty( $colors['primary'] ) ) {
							$colors['primary'] = '#f7f6f7';
						}

						if ( empty( $colors['link'] ) ) {
							$colors['link'] = '#fff';
						}

						$font = wc_format_hex( $colors['link'] );
						$a    = wc_format_hex( $colors['primary'] );
						$b    = wc_format_hex( $this->adjustBrightness( $colors['primary'], - 35 ) );
						$c    = wc_format_hex( $this->adjustBrightness( $colors['primary'], - 50 ) );
						?>
                        <style type="text/css">
                            #wl-wrapper .wl-add-but {
                                background: <?php echo $b; ?>;
                                background: -webkit-gradient(linear, left top, left bottom, from(<?php echo $a; ?>), to(<?php echo $b; ?>));
                                background: -webkit-linear-gradient(<?php echo $a; ?>,<?php echo $b; ?>);
                                background: -moz-linear-gradient(center top,<?php echo $a; ?> 0%,<?php echo $b; ?> 100%);
                                background: -moz-gradient(center top,<?php echo $a; ?> 0%,<?php echo $b; ?> 100%);
                                border-color: <?php echo $c; ?>;
                                color: <?php echo $font; ?>;
                                text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.6);
                            }

                            #wl-wrapper .wl-add-but:hover {
                                background: <?php echo $c; ?>;
                                background: -webkit-gradient(linear, left top, left bottom, from(<?php echo $c; ?>), to(<?php echo $c; ?>));
                                background: -webkit-linear-gradient(<?php echo $a; ?>,<?php echo $c; ?>);
                                background: -moz-linear-gradient(center top,<?php echo $a; ?> 0%,<?php echo $c; ?> 100%);
                                background: -moz-gradient(center top,<?php echo $a; ?> 0%,<?php echo $c; ?> 100%);
                                border-color: <?php echo $c; ?>;
                                color: #ffffff;
                                text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.6);
                            }

                        </style>
						<?php
					} //end is custom colors enabled

					if ( WC_Wishlists_Settings::get_setting( 'wc_wishlist_custom_css' ) ) {
						echo '<style type="text/css">' . WC_Wishlists_Settings::get_setting( 'wc_wishlist_custom_css' ) . '</style>';
					}

					if ( isset( $_REQUEST['wlid'] ) && !empty( $_REQUEST['wlid'] ) ) {
						$wishlist        = new WC_Wishlists_Wishlist( $_REQUEST['wlid'] );
						$maybe_image_url = WC_Wishlists_Wishlist_Item_Collection::get_first_image( $wishlist->id );

						$is_users_list = $wishlist->get_wishlist_owner() == WC_Wishlists_User::get_wishlist_key();
						$fb_message    = $is_users_list ? __( 'Check out my wishlist at ', 'ultimatewoo-pro' ) . get_bloginfo( 'name' ) . ' ' : __( 'Found an interesting list of products at ', 'ultimatewoo-pro' ) . get_bloginfo( 'name' ) . ' ';

						if ( $maybe_image_url ) {
							echo '<meta property="og:type" content="blog"/>';
							echo '<meta property="og:title" content="' . $fb_message . '"/>';
							echo '<meta property="og:image" content="' . $maybe_image_url . '"/>';
						}
					}
				}
			}
		}

		function adjustBrightness( $hex, $steps ) {
			// Steps should be between -255 and 255. Negative = darker, positive = lighter
			$steps = max( - 255, min( 255, $steps ) );

			// Format the hex color string
			$hex = str_replace( '#', '', $hex );
			if ( strlen( $hex ) == 3 ) {
				$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
			}

			// Get decimal values
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );

			// Adjust number of steps and keep it inside 0 to 255
			$r = max( 0, min( 255, $r + $steps ) );
			$g = max( 0, min( 255, $g + $steps ) );
			$b = max( 0, min( 255, $b + $steps ) );

			$r_hex = str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT );
			$g_hex = str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT );
			$b_hex = str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );

			return '#' . $r_hex . $g_hex . $b_hex;
		}

	}

	$GLOBALS['wishlists'] = new WC_Wishlists_Plugin();
}

function woocommerce_wishlists_write_log( $message ) {


	$filename = trailingslashit( WC_Wishlists_Plugin::plugin_path() ) . 'debug.txt';

	$mode = is_writable( $filename ) ? 'a' : 'w';


	// In our example we're opening $filename in append mode.
	// The file pointer is at the bottom of the file hence
	// that's where $somecontent will go when we fwrite() it.
	if ( !$handle = fopen( $filename, $mode ) ) {
		exit;
	}

	// Write $somecontent to our opened file.
	if ( fwrite( $handle, $message ) === false ) {
		exit;
	}

	fwrite( $handle, PHP_EOL );

	fclose( $handle );


}

//2.0.11