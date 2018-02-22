<?php
/*
Author: WooCommerce
Author URI: https://woocommerce.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Plugin page links
 */
function wc_splash_popup_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="http://support.woothemes.com/">' . __( 'Support', 'ultimatewoo-pro' ) . '</a>',
		'<a href="http://docs.woothemes.com/document/woocommerce-splash-popup">' . __( 'Docs', 'ultimatewoo-pro' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_splash_popup_plugin_links' );

if ( is_woocommerce_active() ) {

	/**
	 * Localisation
	 **/
	load_plugin_textdomain( 'wc_splash', false, dirname( plugin_basename( __FILE__ ) ) . '/' );

	/**
	 * WC_Splash class
	 **/
	if ( ! class_exists( 'WC_Splash' ) ) {

		class WC_Splash {

			public function __construct() {
				// Hooks
  				add_action( 'wp' , array( $this, 'setup_wc_splash' ) , 20 );

  				$this->current_tab = ( isset($_GET['tab']) ) ? $_GET['tab'] : 'general';
	            $this->settings_tabs = array(
	                'wc_splash' => __( 'Splash Popup', 'ultimatewoo-pro' )
	            );

	            add_action( 'woocommerce_settings_tabs', array( $this, 'on_add_tab' ), 10 );

	            // Run these actions when generating the settings tabs.
	            foreach ($this->settings_tabs as $name => $label) {
	                add_action( 'woocommerce_settings_tabs_' . $name, array( $this, 'settings_tab_action' ), 10 );
	                add_action( 'woocommerce_update_options_' . $name, array( $this, 'save_settings' ), 10 );
	            }

	            // Add the settings fields to each tab.
	            add_action( 'woocommerce_splash_options_settings', array( $this, 'add_settings_fields' ), 10 );

				// Default options
				add_option( 'wc_splash_force_display', 'no' );

			}

			/* ----------------------------------------------------------------------------------- */
	        /* Admin Tabs */
	        /* ----------------------------------------------------------------------------------- */

	        public function on_add_tab() {
	            foreach ($this->settings_tabs as $name => $label) :
	                $class = 'nav-tab';
	                if ($this->current_tab == $name)
	                    $class .= ' nav-tab-active';
	                echo '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $name ) . '" class="' . $class . '">' . $label . '</a>';
	            endforeach;
	        }

	        /**
	         * settings_tab_action()
	         *
	         * Do this when viewing our custom settings tab(s). One function for all tabs.
	         */
	        public function settings_tab_action() {
	            global $woocommerce_settings;

	            // Determine the current tab in effect.
	            $current_tab = $this->get_tab_in_view( current_filter(), 'woocommerce_settings_tabs_' );

	            do_action( 'woocommerce_splash_options_settings' );

	            // Display settings for this tab (make sure to add the settings to the tab).
	            woocommerce_admin_fields( $woocommerce_settings[$current_tab] );
	        }

	        /**
	         * add_settings_fields()
	         *
	         * Add settings fields for each tab.
	         */
	        public function add_settings_fields() {
	            global $woocommerce_settings;

	            // Load the prepared form fields.
	            $this->init_form_fields();

	            if ( is_array( $this->fields ) ) :
	                foreach ( $this->fields as $k => $v ) :
	                    $woocommerce_settings[$k] = $v;
	                endforeach;
	            endif;
	        }

	        /**
	         * get_tab_in_view()
	         *
	         * Get the tab current in view/processing.
	         */
	        public function get_tab_in_view( $current_filter, $filter_base ) {
	            return str_replace( $filter_base, '', $current_filter );
	        }

	        /**
	         * init_form_fields()
	         *
	         * Prepare form fields to be used in the various tabs.
	         */
	        public function init_form_fields() {

	            // Define settings
	            $this->fields['wc_splash'] = apply_filters( 'woocommerce_wc_splash_settings_fields', array(
					array(
						'name' 	=> __( 'Splash Popup Options', 'ultimatewoo-pro' ),
						'type' 	=> 'title',
						'id' 	=> 'wc_splash_options'
					),
					array(
						'title' 	=> __( 'Logged Out Users See', 'ultimatewoo-pro' ),
						'desc' 		=> __( 'The content of this page will be displayed in your splash popup to logged out users.', 'ultimatewoo-pro' ),
						'id' 		=> 'wc_splash_page_content_logged_out',
						'type' 		=> 'single_select_page',
						'default'	=> '',
						'class'		=> 'chosen_select_nostd',
						'css' 		=> 'min-width:300px;',
						'desc_tip'	=>  true
					),
					array(
						'title' 	=> __( 'Logged In Users See', 'ultimatewoo-pro' ),
						'desc' 		=> __( 'The content of this page will be displayed in your splash popup to logged in users.', 'ultimatewoo-pro' ),
						'id' 		=> 'wc_splash_page_content_logged_in',
						'type' 		=> 'single_select_page',
						'default'	=> '',
						'class'		=> 'chosen_select_nostd',
						'css' 		=> 'min-width:300px;',
						'desc_tip'	=>  true
					),
					array(
						'title' 	=> __( 'Logged In Customers See', 'ultimatewoo-pro' ),
						'desc' 		=> __( 'The content of this page will be displayed in your splash popup to logged in customers.', 'ultimatewoo-pro' ),
						'id' 		=> 'wc_splash_page_content_logged_in_customer',
						'type' 		=> 'single_select_page',
						'default'	=> '',
						'class'		=> 'chosen_select_nostd',
						'css' 		=> 'min-width:300px;',
						'desc_tip'	=>  true
					),
					array(
						'name' 		=> __( 'Cookie Expiration (days)', 'ultimatewoo-pro' ),
						'desc' 		=> __( 'Define how many consecutive days the popup will stay hidden for once closed.', 'ultimatewoo-pro' ),
						'id' 		=> 'wc_splash_expiration',
						'default'	=> '30',
						'type' 		=> 'number',
					),
					array(
						'name' 		=> __( 'Force Display', 'ultimatewoo-pro' ),
						'desc' 		=> __( 'Force the pop up to display regardless of the cookie (only recommended for testing purposes).', 'ultimatewoo-pro' ),
						'id' 		=> 'wc_splash_force_display',
						'type' 		=> 'checkbox',
					),
					array(
						'type' 		=> 'sectionend',
						'id' 		=> 'wc_splash_options'
					),
				) );
	        }

	        /**
	         * save_settings()
	         *
	         * Save settings in a single field in the database for each tab's fields (one field per tab).
	         */
	        public function save_settings() {
	            global $woocommerce_settings;

	            // Make sure our settings fields are recognised.
	            $this->add_settings_fields();

	            $current_tab = $this->get_tab_in_view( current_filter(), 'woocommerce_update_options_' );

	            woocommerce_update_options( $woocommerce_settings[$current_tab] );
	        }

			/*-----------------------------------------------------------------------------------*/
			/* Class Functions */
			/*-----------------------------------------------------------------------------------*/

			/**
			 * setup_wc_splash function.
			 *
			 * @access public
			 * @return void
			 */
			public function setup_wc_splash() {
				add_action( 'wp_enqueue_scripts', array( $this, 'wc_splash_scripts' ) );
				add_action( 'wp_footer', array( $this, 'wc_splash_content' ) );
			}

			/**
			 * wc_splash_scripts function.
			 *
			 * @access public
			 * @return void
			 */
			public function wc_splash_scripts() {
				global $woocommerce;

				$expiration 	= get_option( 'wc_splash_expiration' );

				wp_enqueue_script( 'prettyPhoto', $woocommerce->plugin_url() . '/assets/js/prettyPhoto/jquery.prettyPhoto.min.js', array( 'jquery' ), $woocommerce->version, true );
				wp_enqueue_style( 'woocommerce_prettyPhoto_css', $woocommerce->plugin_url() . '/assets/css/prettyPhoto.css' );
				wp_enqueue_script( 'jquery-cookie', plugins_url( '/assets/js/jquery.cookie.min.js', __FILE__ ), array( 'jquery' ) );
				wp_enqueue_style( 'splash-styles', plugins_url( '/assets/css/style.css', __FILE__ ) );

				if ( ! isset( $expiration ) || $expiration == '' ) {
					$expiration = 30;
				}

				$js = 'jQuery(document).ready(function(){
						// Set the splash cookie as open by default
						if (jQuery.cookie( "splash" ) == null) {
							jQuery.cookie( "splash", "open", { expires: ' . $expiration . ', path: "/" } );
						}

						// Hide the splash content
						jQuery( "#splash-content, .reveal-splash" ).hide();

						// Open splash window via prettyPhoto
						jQuery( "a.reveal-splash, a.force-reveal-splash" ).prettyPhoto({
							social_tools: 	false,
							modal: 			true,
							theme: 			"pp_woocommerce pp_splash_popup",
							opacity: 		0.8,
							default_width: 	800,
							default_height: 600,
							horizontal_padding: 40,
							show_title: 	false,
							callback: 		function(){ jQuery.cookie( "splash", "closed", { expires: ' . $expiration . ', path: "/" } ); }, // Set the cookie when closed
						});

						// Set the cookie to hidden when a link is clicked.
						jQuery( "a" ).click( function() {
							jQuery.cookie( "splash", "closed", { expires: ' . $expiration . ', path: "/" } );
						});

						// Open the splash window automatically if cookie dicates it
						if (jQuery.cookie("splash") == "open") {
					        jQuery(".reveal-splash").trigger("click");
					    }
					    // Or force it to open if specified
					    jQuery(".force-reveal-splash").trigger("click");
					});';

				if ( function_exists( 'wc_enqueue_js' ) ) {
					wc_enqueue_js( $js );
				} else {
					$woocommerce->add_inline_js( $js );
				}
			}

			/**
			 * wc_splash_content function.
			 *
			 * @access public
			 * @return void
			 */
			public function wc_splash_content() {
      			$current_user = wp_get_current_user();

				// Customer orders query
				$customer_orders = get_posts( array(
				    'meta_key'    => '_customer_user',
				    'meta_value'  => get_current_user_id(),
				    'post_type'   => 'shop_order',
				    'post_status' => array( 'wc-processing', 'wc-completed' ),
				) );

				$logged_out_content 		= get_option( 'wc_splash_page_content_logged_out' );
				$logged_in_content 			= get_option( 'wc_splash_page_content_logged_in' );
				$logged_in_customer_content = get_option( 'wc_splash_page_content_logged_in_customer' );

				// Define the splash content
				if ( ! is_user_logged_in() ) { 																	// If the user is not logged in
					$content_id 	= get_option( 'wc_splash_page_content_logged_out' );
				} elseif ( is_user_logged_in() && ! $customer_orders && isset( $logged_out_content ) ) { 		// If the user is logged in but has no orders
					$content_id 	= get_option( 'wc_splash_page_content_logged_in' );
				} elseif ( is_user_logged_in() && $customer_orders && isset( $logged_in_customer_content ) ) { 	// If the user is logged in and has orders
					$content_id 	= get_option( 'wc_splash_page_content_logged_in_customer' );
				}

				$_COOKIE["splash"]	= 'open';
				$splash_cookie 		= $_COOKIE["splash"];
				$forcecookie 		= get_option( 'wc_splash_force_display' );
				$post 				= get_page($content_id);

				if ( $splash_cookie == 'open' || $forcecookie == 'yes' && $content_id !== '' ) { // Only display the content if the cookie is set to 'open' or force display is enabled
					?>
					<section id="splash-content" class="splash-content">
						<?php
							if ( ! is_user_logged_in() ) {
								echo '<h1 class="splash-title">' . apply_filters( 'the_title', get_the_title($content_id) ) . '</h1>';
							} else {
								echo '<h1 class="splash-title">' . __( 'Welcome back ', 'ultimatewoo-pro' ) . $current_user->display_name . '</h1>';
							}
							echo '<div class="splash-content">' . apply_filters( 'the_content', $post->post_content ) . '</div>';
						?>
					</section>
					<a href="#splash-content" title="" class="<?php if ( $forcecookie == 'yes' ) { ?>force-reveal-splash<?php } else { ?>reveal-splash <?php } ?>"></a>
					<?php
				}
			}
		}

		new WC_Splash();
	}
}

//1.2.2