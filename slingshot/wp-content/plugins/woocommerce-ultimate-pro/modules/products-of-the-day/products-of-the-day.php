<?php
/*
Author: WooThemes
Author URI: http://woothemes.com/
Developer: OptArt | Piotr Szczygiel
Developer URI: http://optart.biz
*/

add_action('plugins_loaded', 'init_woocommerce_products_of_the_day', 1);
function init_woocommerce_products_of_the_day()
{
    if ( class_exists( 'Woocommerce' ) )
    {
        include( 'widgets/products-of-the-day-widget.php' );
        add_action( 'widgets_init', create_function( '', 'register_widget( "Woocommerce_Widget_Products_Of_The_Day" );' ) );

        load_plugin_textdomain( 'woocommerce_products_of_the_day', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        if ( !class_exists( 'woocommerce_products_of_the_day' ) )
        {
            class woocommerce_products_of_the_day
            {
                const CSS_PREFIX = 'potd-';

                /**
                 * Default constructor. Defines WooCommerce hooks
                 */
                public function __construct()
                {
                    /* Including assets */
                    add_action( 'admin_enqueue_scripts', array( $this, 'admin_add_styles' ), 10, 1 );
                    add_action( 'wp_enqueue_scripts', array( $this, 'front_add_styles' ) );

                    /* Add 'woocommerce' body class if it doesn't exist for CSS needs. */
                    add_filter( 'body_class', array( $this, 'add_woocommerce_body_class' ) );

                    add_action( 'admin_menu', array( $this, 'admin_menu' ) );
                    add_action( 'wp_ajax_products_of_the_day_sort' , array( $this, 'admin_sort_ajax' ) );
                    add_action( 'wp_ajax_products_of_the_day_remove_item' , array( $this, 'admin_remove_item' ) );
                    add_action( 'wp_ajax_products_of_the_day_add_item' , array( $this, 'admin_add_item' ) );
                    add_action( 'wp_ajax_products_of_the_day_list' , array( $this, 'ajax_list_products' ) );
                }

                /**
                 * AJAX request. Returns a list of products which are available to assign for given day.
                 */
                public static function ajax_list_products()
                {
                    // getting the list of products which are already assigned to current day
                    $day_products = get_posts( array(
                        'posts_per_page'    => -1,
                        'post_type'         => 'product',
                        'post_status'       => 'publish',
                        'meta_key'          => 'product_of_the_day_' . $_POST['day'],
                        'orderby'           => 'meta_value_num',
                        'order'             => 'ASC',
                        'nopaging'          => true,
                        'tax_query'        => array(array(
                                            'taxonomy' => 'product_visibility',
                                            'field'    => 'name',
                                            'terms'    => 'exclude-from-catalog',
                                            'operator' => 'NOT IN',
                        )) 
                    ) );

                    $exclude_ids = array();
                    foreach ( $day_products as $post ) {
                        $exclude_ids[] = $post->ID;
                    }

                    // getting all the products (excluding ones already assigned to that day) which met the searched phrase

                    $products = get_posts( array(
                        'post_type'     => 'product',
                        'numberposts'   => 0,                                   // all product returned in one request
                        'order'         => 'ASC',
                        'orderby'       => 'title',
                        's'             => $_POST['term'],                      // this is the string which we're looking for
                        'exclude'       => $exclude_ids
                    ) );

                    header( "Content-Type: application/json" );
                    print json_encode( $products );
                    exit;
                }

                public function widget_product_of_the_day()
                {
                    echo '<h2 class="widgettitle">My Widget Title</h2>';
                }

                /**
                 * Displays the item in product admin menu
                 */
                public function admin_menu()
                {
                    add_submenu_page(
                        'edit.php?post_type=product',
                        __( 'Products of the day', 'ultimatewoo-pro' ),
                        __( 'Products of the day', 'ultimatewoo-pro' ),
                        'manage_woocommerce',
                        'woocommerce_products_of_the_day',
                         array( $this,'products_of_the_day_page' )
                    );
                }

                /**
                 * Method renders the admin setting page (link under 'Products')
                 */
                public function products_of_the_day_page()
                {
                    $controls = array(
                        0   => array(
                            'mon' => __('Monday'),
                            'tue' => __('Tuesday'),
                            'wed' => __('Wednesday'),
                        ),
                        1   => array(
                            'thu' => __('Thursday'),
                            'fri' => __('Friday'),
                            'sat' => __('Saturday'),
                        ),
                        2   => array(
                            'sun' => __('Sunday')
                        )
                    );

                    require( 'templates/admin_settings_page.php' );
                }

                /**
                 * Method renders an unordered list element which contains the product
                 * @param string $day
                 * @param int $post_id
                 * @param stirng $post_title
                 */
                public static function product_list_element( $day, $post_id, $post_title )
                {
                    include ( 'templates/product_list_element.php' );
                }

                /**
                 * AJAX action for assigning a new prodict into given day
                 */
                public static function admin_add_item()
                {
                    $data = array();
                    add_post_meta( $_POST['post_id'], 'product_of_the_day_' . $_POST['day'] , 999, true);
                    Woocommerce_Widget_Products_Of_The_Day::flush_widget_cache();

                    ob_start();
                    self::product_list_element( $_POST['day'], $_POST['post_id'], $_POST['title'] );
                    $data['new_element'] = ob_get_clean();

                    header( "Content-Type: application/json" );
                    print json_encode( $data );
                    exit;
                }

                /**
                 * Method run by AJAX, removes the product from a particular day
                 */
                public static function admin_remove_item()
                {
                    delete_post_meta( $_POST['post_id'], 'product_of_the_day_' . $_POST['day'] );
                    Woocommerce_Widget_Products_Of_The_Day::flush_widget_cache();

                    exit;
                }

                /**
                 * AJAX method fired when editor is using drag'n'drop to sort the products in a day view
                 * @global type $wpdb
                 */
                public static function admin_sort_ajax()
                {
                    global $wpdb;

                    if (check_ajax_referer('Product-of-the-day-nonce'))
                    {
                        $order = explode(',', $_POST['order']);
                        $day = $_POST['day'];

                        if (in_array($day,array('mon','tue','wed','thu','fri','sat','sun')))
                        {
                            $counter = 1;

                            foreach ($order as $post)
                            {
                                $post_id = str_replace($day.'_','',$post);
                                $wpdb->update($wpdb->postmeta, array( 'meta_value' => $counter ), array( 'post_id' => $post_id, 'meta_key' => 'product_of_the_day_'.$day) );
                                $counter++;
                            }
                            Woocommerce_Widget_Products_Of_The_Day::flush_widget_cache();
                        }
                    }
                    exit;
                }

                /**
                 * Method includes the css and js assets in admin panel
                 * @param type $hook
                 * @return boolean
                 */
                public function admin_add_styles( $hook )
                {
                    global $woocommerce;
                    if ( !in_array( $hook, array( 'post.php', 'post-new.php', 'product_page_woocommerce_products_of_the_day' ) ) )
                    {
                        return false;
                    }

                    if ( in_array( $hook, array( 'product_page_woocommerce_products_of_the_day' ) ) )
                    {
                        wp_enqueue_script( "jquery-ui-core" );
                        wp_enqueue_script( "jquery-ui-sortable" );
                    }

                    wp_enqueue_script( 'woocommerce-products-of-the-day-scripts', ULTIMATEWOO_MODULES_URL . '/products-of-the-day/assets/js/products-of-the-day.js', array( 'jquery' ) );
                    wp_enqueue_script( 'woocommerce-products-of-the-day-scripts-ajax', ULTIMATEWOO_MODULES_URL . '/products-of-the-day/assets/js/products-of-the-day-ajax.js', array( 'jquery', 'jquery-ui-autocomplete' ) );
                    wp_localize_script( 'woocommerce-products-of-the-day-scripts', 'podt', array(
                        'potdne' => wp_create_nonce( 'Product-of-the-day-nonce' )
                    ) );
                    // declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
                    wp_localize_script( 'woocommerce-products-of-the-day-scripts-ajax', 'ajax_data', array(
                        'ajaxurl'           => admin_url( 'admin-ajax.php' ),
                        'confirm_removal'   => __( 'Are you sure you want to remove this item?', 'ultimatewoo-pro' )
                    ) );

                    wp_enqueue_style( 'woocommerce-products-of-the-day-chosen', $woocommerce->plugin_url() . '/assets/css/chosen.css'  );

                    wp_register_style( 'woocommerce-products-of-the-day-styles-admin', ULTIMATEWOO_MODULES_URL . '/products-of-the-day/assets/css/products-of-the-day-admin.css' );
                    wp_enqueue_style( 'woocommerce-products-of-the-day-styles-admin' );
                }

                /**
                 * Include the front styles
                 */
                public function add_woocommerce_body_class( $classes )
                {
                    $class_search = array_search( 'woocommerce', $classes );
                    if ( false === $class_search ) {
                        $classes[] = 'woocommerce';
                    }

                    return $classes;
                }

                /**
                 * Include the front styles
                 */
                public function front_add_styles()
                {
                    wp_register_style('woocommerce-products-of-the-day-styles', ULTIMATEWOO_MODULES_URL . '/products-of-the-day/assets/css/products-of-the-day.css' );
                    wp_enqueue_style('woocommerce-products-of-the-day-styles');
                }
            }

            new woocommerce_products_of_the_day();
        }
    }
}

//1.2.0