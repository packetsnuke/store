<?php
/*
Copyright 2014 WooThemes.
Authored by David Baker.
*/
define('_WC_FB_TAB_KEY','fbtab');

/**
 * called when the user clicks the "Facebook" menu on the left.
 *
 * @access public
 * @return void
 */
function woocommerce_facebook_tab(){
    if ( current_user_can( 'manage_woocommerce' ) ){
        include( 'tabsetup.php' );
    }
}

/**
 * sets up the menu on the left.
 *
 * @access public
 * @return void
 */
function woocommerce_facebook_tab_admin_menu() {
    global $menu, $woocommerce;
    if ( current_user_can( 'manage_woocommerce' ) ){
        add_submenu_page('woocommerce', __('WooCommerce Facebook', 'ultimatewoo-pro'),  __('Facebook', 'ultimatewoo-pro') , 'manage_woocommerce', 'woocommerce_facebook', 'woocommerce_facebook_tab');
    }
}

add_action('admin_menu', 'woocommerce_facebook_tab_admin_menu', 15);

/**
 * Adds our two new sidebars for facebook
 */
function woocommerce_facebook_tab_widgits_init(){
    $args = array(
	'name'          => __( 'Facebook Archive', 'woocommerce_facebook' ),
	'id'            => 'facebook-archive',
	'description'   => __('Shown when viewing the WooCommerce product listing from Facebook', 'woocommerce_facebook'),
    'class'         => '',
	'before_widget' => '<div class="fb_widget">',
	'after_widget'  => '</div>',
	'before_title'  => '<h2 class="fb_widgettitle">',
	'after_title'   => '</h2>' );
    register_sidebar($args);
    $args = array(
	'name'          => __( 'Facebook Single', 'woocommerce_facebook' ),
	'id'            => 'facebook-single',
	'description'   => __('Shown when viewing a single WooCommerce product from Facebook', 'woocommerce_facebook'),
    'class'         => '',
	'before_widget' => '<div class="fb_widget">',
	'after_widget'  => '</div>',
	'before_title'  => '<h2 class="fb_widgettitle">',
	'after_title'   => '</h2>' );
    register_sidebar($args);
}
add_action('widgets_init', 'woocommerce_facebook_tab_widgits_init', 100);

/**
 * Attempt to find our special facebook tempplate files within the theme folder, fallback local plugin folder defaults.
 *
 * @param $templatefilename
 * @return bool|string
 */
function woocommerce_facebook_template($templatefilename){
    // backwards compatibility with old '/custom/' folder format.
    // edit: hmm, not sure if this is even needed. if someone updates this plugin it will wipe the 'custom/' folder anways. oh well leave it here for the next few versions just incase.
    switch($templatefilename){
        case 'woocommerce-facebook-header.php':
            $old_custom_file = 'header.php';
            break;
        case 'woocommerce-facebook-footer.php':
            $old_custom_file = 'footer.php';
            break;
        case 'woocommerce-facebook-single.php':
            $old_custom_file = 'product-single.php';
            break;
        case 'woocommerce-facebook-archive.php':
            $old_custom_file = 'product-listing.php';
            break;
    }
    if(isset($old_custom_file) && file_exists(dirname( __FILE__ ) . '/custom/' . $old_custom_file)){
        return dirname( __FILE__ ) . '/custom/' . $old_custom_file;
    }
    // end backwards compat
    if( file_exists( get_stylesheet_directory() .'/'.$templatefilename)){
        return get_stylesheet_directory() .'/'.$templatefilename;
    }else if( file_exists( get_template_directory() .'/'.$templatefilename)){
        return get_template_directory() .'/'.$templatefilename;
    }else if (file_exists(dirname( __FILE__ ) . '/templates/' . $templatefilename)) {
        return dirname( __FILE__ ) . '/templates/' . $templatefilename;
    }
    return false;
}


/**
 * intercepts the page load when the "fbtab" flag is passed (ie: coming from facebook)
 *
 * @access public
 * @return void
 */
function woocommerce_facebook_tab_process_post(){
    if(isset($_REQUEST[_WC_FB_TAB_KEY])) {
        global $wp_query, $woocommerce, $wp, $post;
        show_admin_bar(false);

        // include the facebook header file
        if($templatefilename = woocommerce_facebook_template('woocommerce-facebook-header.php')){
            include($templatefilename);
        }
        if($templatefilename = woocommerce_facebook_template('woocommerce-facebook-cart.php')){
            include($templatefilename);
        }
        if($templatefilename = woocommerce_facebook_template('woocommerce-facebook-breadcrumb.php')){
            include($templatefilename);
        }
        if(is_product()){
            if($templatefilename = woocommerce_facebook_template('woocommerce-facebook-single.php')){
                include($templatefilename);
            }
        }else{
            if($templatefilename = woocommerce_facebook_template('woocommerce-facebook-archive.php')){
                include($templatefilename);
            }
        }
        if($templatefilename = woocommerce_facebook_template('woocommerce-facebook-footer.php')){
            include($templatefilename);
        }


        die();

    }
}

//add_action( 'get_header', 'woocommerce_facebook_tab_process_post' );

/**
 * handle some more re-writing
 *
 * @access public
 * @return void
 */
function woocommerce_facebook_tab_process_rewrites(){
    if (isset($_GET['tabs_added'])) {
        // after intial tab activation, take user back to wordpress admin page.
        header("Location: ".admin_url().'admin.php?page=woocommerce_facebook&done');
        exit;
    }

    if(isset($_REQUEST[_WC_FB_TAB_KEY])){

        $styles = array(
            'woocommerce-layout' => array(
                'src'     => str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/css/woocommerce-layout.css',
                'deps'    => '',
                'version' => WC_VERSION,
                'media'   => 'all'
            ),
            'woocommerce-smallscreen' => array(
                'src'     => str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/css/woocommerce-smallscreen.css',
                'deps'    => 'woocommerce-layout',
                'version' => WC_VERSION,
                'media'   => 'only screen and (max-width: ' . apply_filters( 'woocommerce_style_smallscreen_breakpoint', $breakpoint = '768px' ) . ')'
            ),
            'woocommerce-general' => array(
                'src'     => str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/css/woocommerce.css',
                'deps'    => '',
                'version' => WC_VERSION,
                'media'   => 'all'
            ),
        );
        foreach ( $styles as $handle => $args ) {
            wp_enqueue_style( $handle, $args['src'], $args['deps'], $args['version'], $args['media'] );
        }

    	add_filter('the_permalink', 'woocommerce_facebook_tab_the_permalink',1);
    	add_filter('post_link', 'woocommerce_facebook_tab_the_permalink',1);
        add_filter('term_link', 'woocommerce_facebook_tab_the_permalink',1);
        add_filter('pre_option_woocommerce_prepend_shop_page_to_urls','woocommerce_facebook_tab_prepend_shop_page_to_urls',1);
        add_filter('pre_option_woocommerce_enable_ajax_add_to_cart','woocommerce_facebook_tab_enable_ajax_add_to_cart',1);
        add_filter('pre_option_woocommerce_cart_redirect_after_add','woocommerce_facebook_tab_cart_redirect_after_add',1);
        add_action("template_redirect", "woocommerce_facebook_tab_process_post", 1);
        add_filter("woocommerce_add_to_cart_message", "woocommerce_facebook_add_to_cart_message",1);
        add_filter("wc_add_to_cart_message", "woocommerce_facebook_add_to_cart_message",1);
        // filter needed for the 'select options' button on variable products:
        add_filter("woocommerce_loop_add_to_cart_link", "woocommerce_facebook_woocommerce_loop_add_to_cart_link",1,3);

    }else if(isset($_GET['signed_request'])){
        if($templatefilename = woocommerce_facebook_template('woocommerce-facebook-header.php')){
            include($templatefilename);
        }
        ?>
    Please <a href="<?php echo esc_attr("http://" . $_SERVER['HTTP_HOST']  . $_SERVER['REQUEST_URI']);?>" target="_blank">click here</a> to continue browsing our Online Shop.
    <?php
        if($templatefilename = woocommerce_facebook_template('woocommerce-facebook-footer.php')){
            include($templatefilename);
        }
        exit;
    }
}

add_action('init', 'woocommerce_facebook_tab_process_rewrites');

// add the fbtab flag to our woocommerce product permalinks
// this is so when they click on a product in facebook it will keep the "fbtab" flag in the url
function woocommerce_facebook_tab_the_permalink( $url ){

	$cart_link = get_permalink( woocommerce_get_page_id( 'cart' ) );

	if ( strstr( $url, $cart_link ) ) {
		$url = remove_query_arg( _WC_FB_TAB_KEY, $url );

		return $url;
	}

    return add_query_arg( _WC_FB_TAB_KEY, 'true', $url );
}

function woocommerce_facebook_tab_prepend_shop_page_to_urls($option){
    return 'no';
}

function woocommerce_facebook_tab_enable_ajax_add_to_cart($option){
    return 'no';
}

// stop cart redirection
function woocommerce_facebook_tab_cart_redirect_after_add($option){
    return 'no';
}
// 'view cart' link opens in popup
function woocommerce_facebook_add_to_cart_message($message){
    return str_replace('<a','<a target="_blank"',$message);
}

function woocommerce_facebook_woocommerce_loop_add_to_cart_link($old_a_href, $product, $link){
    if(strpos($link['url'],_WC_FB_TAB_KEY)===false)
        $link['url'] = woocommerce_facebook_tab_the_permalink($link['url']);
    return sprintf('<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="%s button product_type_%s">%s</a>', esc_url( $link['url'] ), esc_attr( $product->id ), esc_attr( $product->get_sku() ), esc_attr( $link['class'] ), esc_attr( $product->product_type ), esc_html( $link['label'] ) );
}

//1.2.0