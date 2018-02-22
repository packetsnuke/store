<?php
/**
  * Plugin Name: Woocommerce Products List
* Plugin URI: https://codecanyon.net/item/woocommerce-products-list-pro/17893660
* Description: Plugin to list all your Woocommerce products
* Version: 1.1.15
* Author: Spyros Vlachopoulos
* Author URI: http://www.nitroweb.gr
* License: GPL2
* Text Domain: wcplpro
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'WCPLPRO_VER',             '1114' );
define( 'WCPLPRO_DIR',             dirname( __FILE__ ) );
define( 'WCPLPRO_URI',             rtrim(plugin_dir_url( __FILE__ ), '/') );

add_action('init', 'wcplpro_StartSession');
function wcplpro_StartSession() {
  if ( wcplpro_is_session_started() === FALSE ) { session_start(); }
}

add_action('wp_logout', 'wcplpro_EndSession');
add_action('wp_login', 'wcplpro_EndSession');

function wcplpro_EndSession() {
  if ( wcplpro_is_session_started() === TRUE ) {
    session_destroy ();
  }
}


// Load plugin textdomain
add_action( 'plugins_loaded', 'wcplpro_load_textdomain' );
function wcplpro_load_textdomain() {
  load_plugin_textdomain( 'wcplpro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}



function wcplpro_activate() {
  
  $wcplpro_order = array (
    'wcplpro_thumb' => __('Thumbnail', 'wcplpro'),
    'wcplpro_sku' => __('SKU', 'wcplpro'),
    'wcplpro_title' => __('Product Title', 'wcplpro'),
    'wcplpro_offer' => __('Offer Image', 'wcplpro'),
    'wcplpro_categories' => __('Categories', 'wcplpro'),
    'wcplpro_tags' => __('Tags', 'wcplpro'),
    'wcplpro_stock' => __('Stock', 'wcplpro'),
    'wcplpro_gift' => __('Gift Wrap', 'wcplpro'),
    'wcplpro_wishlist' => __('Wishlist', 'wcplpro'),
    'wcplpro_qty' => __('Quantity', 'wcplpro'),
    'wcplpro_price' => __('Price', 'wcplpro'),
    'wcplpro_total' => __('Total', 'wcplpro'),
    'wcplpro_cart' => __('Add to Cart Button', 'wcplpro')
  );
  
  
  // set options only if they do not exist
  if (get_option('wcplpro_title') === false) {
    update_option( 'wcplpro_title', 1 );
  }
  if (get_option('wcplpro_thumb') === false) {
    update_option( 'wcplpro_thumb', 1 );
  }
  if (get_option('wcplpro_thumb_size') === false) {
    update_option( 'wcplpro_thumb_size', 80 );
  }
  if (get_option('wcplpro_thumb_link') === false) {
    update_option( 'wcplpro_thumb_link', 'image' );
  }
  if (get_option('wcplpro_price') === false) {
    update_option( 'wcplpro_price', 1 );
  }
  if (get_option('wcplpro_total') === false) {
    update_option( 'wcplpro_total', 0 );
  }
  if (get_option('wcplpro_cart') === false) {
    update_option( 'wcplpro_cart', 1 );
  }
  if (get_option('wcplpro_qty') === false) {
    update_option( 'wcplpro_qty', 1 );
  }
  if (get_option('wcplpro_order') === false) {
    update_option( 'wcplpro_order', $wcplpro_order );
  }
  if (get_option('wcplpro_head') === false) {
    update_option( 'wcplpro_head', 1 );
  }
  if (get_option('wcplpro_sorting') === false) {
    update_option( 'wcplpro_sorting', 1 );
  }
  if (get_option('wcplpro_lightbox') === false) {
    update_option( 'wcplpro_lightbox', 1 );
  }
  if (get_option('wcplpro_default_qty') === false) { 
    update_option('wcplpro_default_qty', 1); 
  }
  if (get_option('wcplpro_qty_control') === false) { 
    update_option('wcplpro_qty_control', 0); 
  }
  if (get_option('wcplpro_globalposition') === false) { 
    update_option('wcplpro_globalposition', 'bottom'); 
  }
  if (get_option('wcplpro_globalcart') === false) { 
    update_option('wcplpro_globalcart', 0); 
  }
  if (get_option('wcplpro_desc_inline') === false) { 
    update_option('wcplpro_desc_inline', '0'); 
  }
  if (get_option('wcplpro_weight') === false) { 
    update_option('wcplpro_weight', '0'); 
  }
  if (get_option('wcplpro_dimensions') === false) { 
    update_option('wcplpro_dimensions', '0'); 
  }
  if (get_option('wcplpro_hide_global_total') === false) { 
    update_option('wcplpro_hide_global_total', '0'); 
  }
  if (get_option('wcplpro_dont_link_to_product') === false) { 
    update_option('wcplpro_dont_link_to_product', '0'); 
  }
   
}
register_activation_hook( __FILE__, 'wcplpro_activate' );


include ('grid_options_page.php');
include ('product_options_meta.php');
include ('editor_plugins.php');


function wcplpro_sc_attr() {
  
  $sc_attr = array(
    'keyword'               => '',
    'categories_exc'        => '',
    'categories_inc'        => '',
    'tag_exc'               => '',
    'tag_inc'               => '',
    'posts_inc'             => '',
    'posts_exc'             => '',
    'posts_sku_inc'         => '',
    'posts_sku_exc'         => '',
    'categories'            => '',
    'tags'                  => '',
    'sku'                   => '',
    'title'                 => '',
    'thumb'                 => '',
    'thumb_size'            => '',
    'thumb_link'            => '',
    'stock'                 => '',
    'hide_zero'             => '',
    'hide_outofstock'       => '',
    'only_outofstock'       => '',
    'zero_to_out'           => '',
    'price'                 => '',
    'total'                 => '',
    'offer'                 => '',
    'image'                 => '',
    'qty'                   => '',
    'default_qty'           => '',
    'qty_control'           => '',
    'cart'                  => '',
    'globalcart'            => '',
    'globalposition'        => '',
    'global_status'         => '',
    'custommeta'            => '',
    'metafield'             => '',
    'attributes'            => '',
    'wishlist'              => '',
    'gift'                  => '',
    'ajax'                  => '',
    'desc'                  => '',
    'weight'                => '',
    'dimensions'            => '',
    'desc_inline'           => '',
    'head'                  => '',
    'sorting'               => '',
    'lightbox'              => '',
    'order'                 => '',
    'orderby'               => '',
    'order_direction'       => '',
    'date'                  => '',
    'echo'                  => 0,
    'category_title'        => 0,
    'limit'                 => 0,
    'wcplid'                => '',
    'quickview'             => '',
    'pagination'            => '',
    'posts_per_page'        => '',
    'filter_cat'            => '',
    'filter_tag'            => '',
    'filter_search'         => '',
    'filters_position'      => '',
    'columns_names'         => '',
    'hide_global_total'     => 0,
    'dont_link_to_product'  => 0
  );
  
  
  return apply_filters('wcplpro_sc_attr', $sc_attr);
  
}


// create the shortcode
function wcplpro_func( $atts ) {
    $a = shortcode_atts(wcplpro_sc_attr(), $atts );

    // disable echo for shortcode
    $a['echo'] = 0;
    
    return (wc_products_list_pro($a));
}
add_shortcode( 'wcplpro', 'wcplpro_func' );


function wc_products_list_pro($allsets) {
  global $product, $post, $woocommerce, $wpdb;
  
  $current_post = $post;
  
  $out = '';
  
  $sc_attr = wcplpro_sc_attr();
  
  
  if (get_option('wcplpro_lightbox') === false) {
    update_option( 'wcplpro_lightbox', 1 );
  }
  
  
  // get values from shortcode
  if ($allsets) {
    foreach($sc_attr as $key => $attr) {
      ${'wcplpro_'.$key} = $allsets[$key];
    }
  } else{
    foreach($sc_attr as $key => $attr) {
      ${'wcplpro_'.$key} = null;
    }
  }
  

  // get default value if attribute is not set
  foreach($sc_attr as $key => $attr) {
    ${'wcplpro_'.$key} = (${'wcplpro_'.$key} == null         ? get_option('wcplpro_'.$key) : ${'wcplpro_'.$key});
  }
    
  
  // gift wrap option
  $default_message            = '{checkbox} '. sprintf( __( 'Gift wrap this item for %s?', 'woocommerce-product-gift-wrap' ), '{price}' );
  $gift_wrap_enabled          = get_option( 'product_gift_wrap_enabled' ) == 'yes' ? true : false;
  $gift_wrap_cost             = get_option( 'product_gift_wrap_cost', 0 );
  $product_gift_wrap_message  = get_option( 'product_gift_wrap_message');
  
  if ( ! $product_gift_wrap_message ) {
    $product_gift_wrap_message = $default_message;
  }
  
  // set list id
  $vtrand = $wcplpro_wcplid;
  $useruniq = isset($_COOKIE['PHPSESSID']) ? substr($_COOKIE['PHPSESSID'], 0, 10) : uniqid();
  
  // set default page to 1
  $wcplpro_paged = 1;
  
  // load quickview
  if (class_exists( 'YITH_WCQV_Frontend' ) && isset($wcplpro_quickview)) {
    $YITH_WCQV_Frontend = YITH_WCQV_Frontend();
  }
  
  
  $query_args = array(
    'post_type'       => 'product',
    'nopaging'        => false,
    'posts_per_page'  => (absint($wcplpro_posts_per_page) > 0 ? $wcplpro_posts_per_page : get_option( 'posts_per_page' ) ),
    'post_status'     => 'publish'
  );
  
  // add search term
  if (isset($wcplpro_keyword) && $wcplpro_keyword != null) {
    $query_args['s'] = $wcplpro_keyword;
    $wcplpro_search = $wcplpro_keyword;
    set_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_search', $wcplpro_search, 3600);
  }
  // include specific posts only
  if (isset($wcplpro_posts_inc) && $wcplpro_posts_inc != null) {
    $query_args['post__in'] = explode(',', str_replace(' ', '', $wcplpro_posts_inc));
  }
  // exclude posts by ID
  if (isset($wcplpro_posts_exc) && $wcplpro_posts_exc != null) {
    $query_args['post__not_in'] = explode(',', str_replace(' ', '', $wcplpro_posts_exc));
  }
  // include specific posts by SKU
  if (isset($wcplpro_posts_sku_inc) && $wcplpro_posts_sku_inc != null) {
    $query_args['meta_query']['sku_inc_clause'] = array(
      'key'     => '_sku',
      'value'   => explode(',', str_replace(', ', ',', $wcplpro_posts_sku_inc)),
      'compare' => 'IN',
    );
  }
  // exclude posts by SKU
  if (isset($wcplpro_posts_sku_exc) && $wcplpro_posts_sku_exc != null) {
    $query_args['meta_query'][] = array(
      'key'     => '_sku',
      'value'   => explode(',', str_replace(', ', ',', $wcplpro_posts_sku_exc)),
      'compare' => 'NOT IN',
    );
  }
  
  

  // exclude individual products
  $query_args['meta_query'][] = array(
    'relation' => 'OR',
    array(
      'key'     => 'wcplpro_remove_product',
      'compare' => 'NOT EXISTS',
      'value' => '' // This is ignored, but is necessary...
    ),
    array(
      'key'     => 'wcplpro_remove_product',
      'value'   => '1',
      'compare' => '!=',
    ),
  );


  // include in stock products only
  if (isset($wcplpro_hide_outofstock) && $wcplpro_hide_outofstock != null && $wcplpro_hide_outofstock == 1) {
    $query_args['meta_query'][] = array(
      'key'     => '_stock_status',
      'value'   => 'instock',
      'compare' => '=',
    );
  }
  
  // include in stock products only
  if (isset($wcplpro_only_outofstock) && $wcplpro_only_outofstock != null && $wcplpro_only_outofstock == 1) {
    $query_args['meta_query'][] = array(
      'key'     => '_stock_status',
      'value'   => 'instock',
      'compare' => '!=',
    );
  }
  // include in stock products only
  if (isset($wcplpro_hide_zero) && $wcplpro_hide_zero != null && $wcplpro_hide_zero == 1) {
    $query_args['meta_query'][] = array(
      'key'     => '_price',
      'value'   => '0',
      'compare' => '>',
    );
  }
  
  
  // add order_direction
  if (isset($wcplpro_order_direction) && $wcplpro_order_direction != null) {
    $query_args['order'] = $wcplpro_order_direction;
  }
  if (isset($wcplpro_orderby) && $wcplpro_orderby != null) {
    
    if ($wcplpro_orderby == 'title' || $wcplpro_orderby == 'post__in' || $wcplpro_orderby == 'date' || $wcplpro_orderby == 'menu_order') {
      $query_args['orderby'] = $wcplpro_orderby;
      
      // order by meta_query clause
      if ($wcplpro_orderby == 'post__in' && isset($query_args['meta_query']['sku_inc_clause'])) {
        $query_args['orderby'] = array(
          'sku_inc_clause' => $wcplpro_order_direction
        );
      }
    } else {
      $query_args['orderby'] = 'meta_value_num';
      $query_args['meta_key'] = $wcplpro_orderby;
    }
  }
  
  // add date filter
  if (isset($wcplpro_date) && $wcplpro_date != null) {
    
    $date_array = $date_query = array();
    $date_array = explode('/', $wcplpro_date);
    
    if (isset($date_array[0])) { $date_query['year'] = $date_array[0]; }
    if (isset($date_array[1])) { $date_query['month'] = $date_array[1]; }
    if (isset($date_array[2])) { $date_query['day'] = $date_array[2]; }
    
    $query_args['date_query'] = array($date_query);
  }
    
  // add list filters via request
  if (
    $wcplpro_wcplid != '' 
    && $wcplpro_wcplid !== null 
    && !is_admin() 
    && isset($_REQUEST['wcpl']) 
    && $_REQUEST['wcpl'] == 1 
    && isset($_REQUEST['wcplid']) 
    && $_REQUEST['wcplid'] != ''
    && $_REQUEST['wcplid'] == $wcplpro_wcplid 
    ) 
  {
    // set new cat request if exists
    if (isset($_REQUEST['wcpl_product_cat']) && $_REQUEST['wcpl_product_cat'] != '') {
      $wcplpro_categories_inc = esc_sql($_REQUEST['wcpl_product_cat']);
      set_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_cat', $wcplpro_categories_inc, 3600);
    }
    // unset the cookie if the cat request exists but empty
    if (isset($_REQUEST['wcpl_product_cat']) && $_REQUEST['wcpl_product_cat'] == '') {
      delete_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_cat');
    }
    
    
    // set new tag request if exists
    if (isset($_REQUEST['wcpl_product_tag']) && $_REQUEST['wcpl_product_tag'] != '') {
      $wcplpro_tag_inc = esc_sql($_REQUEST['wcpl_product_tag']);
      set_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_tag', $wcplpro_tag_inc, 3600);
    }
    // unset the cookie if the tag request exists but empty
    if (isset($_REQUEST['wcpl_product_tag']) && $_REQUEST['wcpl_product_tag'] == '') {
      delete_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_tag');
    }
    
    // set new search request if exists
    if (isset($_REQUEST['wcpl_search']) && $_REQUEST['wcpl_search'] != '') {
      $wcplpro_search = esc_sql($_REQUEST['wcpl_search']);
      set_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_search', $wcplpro_search, 3600);
    }
    // unset the cookie if the search request exists but empty
    if (isset($_REQUEST['wcpl_search']) && $_REQUEST['wcpl_search'] == '') {
      delete_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_search');
    }
    
    // set new request if exists
    if (isset($_REQUEST['wcpl_page']) && intval($_REQUEST['wcpl_page']) > 1) {
      $wcplpro_paged = intval($_REQUEST['wcpl_page']);
    }
    
    if ($wcplpro_paged > 1) {
      set_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_pag', $wcplpro_paged, 3600);
    } else {
    // unset the cookie if the request exists but empty
      delete_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_pag');
    }
  }
  
  // preserve the request and set it only for the specific list
  if (get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_cat')) {
    $wcplpro_categories_inc = get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_cat');
  }
  
  
  // preserve the request and set it only for the specific list
  if (get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_tag')) {
    $wcplpro_tag_inc = get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_tag');
  }
  // preserve the search request and set it only for the specific list
  if (get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_search')) {
    $wcplpro_search = get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_search');
    
    // force search term
    $query_args['s'] = $wcplpro_search;
    
  }
  
  // preserve the page number for this specific list
  if (get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_pag')) {
    $wcplpro_paged = get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_pag');
  }
  // set page
  $query_args['paged'] = $wcplpro_paged;  
  
  // add categories filters
  if ($wcplpro_categories_exc != null || $wcplpro_categories_inc != null || $wcplpro_tag_exc != null || $wcplpro_tag_inc != null) {
    $query_args['tax_query'] = array('relation' => 'AND');
  }
  if ($wcplpro_categories_exc != null){
    $query_args['tax_query'][] = array(
      'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => (is_array($wcplpro_categories_exc) ? $wcplpro_categories_exc : explode(',', $wcplpro_categories_exc )),
			'operator' => 'NOT IN',
    );
  }
  
  if ($wcplpro_categories_inc != null){
    $query_args['tax_query'][] = array(
      'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => (is_array($wcplpro_categories_inc) ? $wcplpro_categories_inc : explode(',', $wcplpro_categories_inc )),
			'operator' => 'IN',
    );
  }
  if ($wcplpro_tag_exc != null){
    $query_args['tax_query'][] = array(
      'taxonomy' => 'product_tag',
			'field'    => 'term_id',
			'terms'    => (is_array($wcplpro_tag_exc) ? $wcplpro_tag_exc : explode(',', $wcplpro_tag_exc )),
			'operator' => 'NOT IN',
    );
  }
  if ($wcplpro_tag_inc != null){
    $query_args['tax_query'][] = array(
      'taxonomy' => 'product_tag',
			'field'    => 'term_id',
			'terms'    => (is_array($wcplpro_tag_inc) ? $wcplpro_tag_inc : explode(',', $wcplpro_tag_inc )),
			'operator' => 'IN',
    );
  }
  

  $query_args = apply_filters('wcplpro_query_args', $query_args);  
  $query = new WP_Query( $query_args );
  
  // The Loop
  if ( $query->have_posts()) {
    
    // get header names
    $headenames = wcplpro_fields_func();
    $custom_meta_header = array(); // array to hold the header names of custom meta
    
    $anyextraimg = 0;
    $anydescription = 0;
    $anydimension = 0;
    $anyweight = 0;
    $head = '';
    
    
    
    
    ob_start();
    do_action('woocommerce_before_add_to_cart_form', $current_post);
    $woocommerce_before_add_to_cart_form = ob_get_clean();
    $out .= $woocommerce_before_add_to_cart_form;
    
    ob_start();
    do_action('wcplpro_before_table', $current_post);
    $wcplpro_before_table = ob_get_clean();
    $out .= $wcplpro_before_table;
    
    $wcplpro_table_class = '';
    ob_start();
    do_action('wcplpro_table_class', $current_post);
    $wcplpro_table_class = ob_get_clean();
    
    
    ob_start();
    do_action('wcplpro_after_filters_top', $current_post);
    $wcplpro_after_filters_top = ob_get_clean();
    
    $sorting_js = apply_filters( 'wcplpro_sorting_js', $wcplpro_sorting, $current_post);
    
    
    $cartredirect = get_option('woocommerce_cart_redirect_after_add');
    if (get_option('direct_checkout_enabled') == 1) {
      $cartredirect = 'yes';
    }
    
    
    ob_start();
    do_action('wcplpro_before_filters_top', $current_post);
    $wcplpro_before_filters_top = ob_get_clean();
    
    $out .= $wcplpro_before_filters_top;
    
    $out .= '<div class="wcpl_group top">';
    
    // add drops down filters
    if (($wcplpro_filters_position == 'before' || $wcplpro_filters_position == 'both') || ($wcplpro_pagination == 'before' || $wcplpro_pagination == 'both')) {
      
      $out .= wcplpro_filters_form($wcplpro_filter_cat, $wcplpro_filter_tag, $wcplpro_filter_search, get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_cat'), get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_tag'), get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_search'), $wcplpro_wcplid, 'before');
      
    }
    
    if ($wcplpro_pagination == 'before' || $wcplpro_pagination == 'both') {
      $out .= wcplpro_pagination($wcplpro_posts_per_page, $query, $wcplpro_paged, $useruniq, $wcplpro_wcplid);
    }
    
    $out .= '</div>';
    
    $out .= $wcplpro_after_filters_top;
    
    if (($wcplpro_globalcart == 1 || $wcplpro_globalcart == 2) && ($wcplpro_globalposition == 'top' || $wcplpro_globalposition == 'both')) {
      
      $out .= apply_filters('wcplpro_global_btn', '
        <div class="gc_wrap"> 
          <a data-position="top" href="#globalcart" class="globalcartbtn submit btn single_add_to_cart_button button alt" data-post_id="gc_'.$current_post->ID .'" id="gc_'. $vtrand .'_top" class="btn button alt">
            '. apply_filters('wcplpro_global_btn_text', __('Add selected to cart', 'wcplpro'), $query_args, $query) .'
            <span class="vt_products_count"></span> 
            <span class="vt_total_count '. ($wcplpro_hide_global_total == 1 ? 'wcplprohide' : '') .'"></span>
          </a>
          <span class="added2cartglobal added2cartglobal_'. $vtrand .'">&#10003;</span>
          <span class="vtspinner vtspinner_top vtspinner_'. $vtrand .'"><img src="'. plugins_url('images/spinner.png', __FILE__) .'" width="16" height="16" alt="spinner" /></span>
        </div>
      ', $current_post, 'top', $vtrand);
      
    }
    
    
    // open table
    $out .= '
    <div class="woocommerce wcplprotable_wrap">
    <table 
      id="tb_'. $vtrand .'" 
      class="table wcplprotable shop_table shop_table_responsive '. ($wcplpro_head == 0 ? 'nohead' : 'withhead') .' '. ($sorting_js == 1 ? 'is_sortable' : '') .' '. $wcplpro_table_class .'" 
      data-ver='. WCPLPRO_VER .' 
      data-random="'. $vtrand .'" 
      '. ($sorting_js == 1 ? 'data-sort="yes"' : 'data-sort="no"') .' 
      '. ($wcplpro_ajax == 1 ? 'data-wcplprotable_ajax="1"' : 'data-wcplprotable_ajax="0"').' 
      '. ($cartredirect == 'yes' ? 'data-cartredirect="yes"' : 'data-cartredirect="no"') .' 
      data-globalcart="'. $wcplpro_globalcart .'"
      >
    
      %headplaceholder%
    ';    
    
    $out .= '<tbody>
      ';
    
    
    // loop the products
    while ( $query->have_posts() ) {
      $query->the_post();
      
      $product = new WC_Product(get_the_ID());
      $product_meta = get_post_meta(get_the_ID());
      
      $terms = get_the_terms($product->get_id(), 'product_type');
      $product_type = !empty($terms) ? sanitize_title(current($terms)->name) : 'simple';

      $product_stock = $product->get_stock_quantity();
      $product_avail = $product->get_availability();
            
      $form = '';
      $yith_quickview = '';
  
      $is_wrappable = get_post_meta( $product->get_id(), '_is_gift_wrappable', true );
      
      if ( $is_wrappable == '' && $gift_wrap_enabled ) {
        $is_wrappable = 'yes';
      }
      
      
      ob_start();
      do_action('wcplpro_before_single_row', $product->get_id(), $product);
      $wcplpro_before_single_row = ob_get_clean();
      
      ob_start();
      do_action('wcplpro_inside_add_to_cart_form', $product->get_id(), $product);
      $wcplpro_inside_add_to_cart_form = ob_get_clean();
      
      
      $form = '
		<form action="'. esc_url( $product->add_to_cart_url() ) .'" method="POST" data-product_id="'.  $product->get_id() .'" id="wcplpro_product_'. $product->get_id() .'" class="vtajaxform" enctype="multipart/form-data">
			<input type="hidden" name="product_id" value="'. esc_attr( $product->get_id() ) .'" />
			<input type="hidden" name="add-to-cart" value="'. esc_attr( $product->get_id() ) .'" />
      '. $wcplpro_inside_add_to_cart_form .'
      ';
      
      $wcplpro_qty_step = 1;
      $qty_defaults = array( 
        'input_name' => 'quantity',  
        'input_value' => ($wcplpro_default_qty != '' ? apply_filters('wcplpro_default_qty', $wcplpro_default_qty, $product) : 1),  
        'max_value' => apply_filters( 'woocommerce_quantity_input_max', '', $product ),  
        'min_value' => apply_filters( 'woocommerce_quantity_input_min', '', $product ),  
        'step' => apply_filters( 'woocommerce_quantity_input_step', apply_filters('wcplpro_qty_step', $wcplpro_qty_step, $product), $product ),  
        'pattern' => apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ),  
        'inputmode' => apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' ),  
        'class' => 'hidden_quantity'
      );
      
      
      $qty_args = '';
      $qty_args = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $qty_args, $qty_defaults ), $product );
      
      // Apply sanity to min/max args - min cannot be lower than 0 
      if ( '' !== $qty_args['min_value'] && is_numeric( $qty_args['min_value'] ) && $qty_args['min_value'] < 0 ) { 
          $qty_args['min_value'] = 0; // Cannot be lower than 0 
      }
      // Max cannot be lower than 0 or min 
      if ( '' !== $qty_args['max_value'] && is_numeric( $qty_args['max_value'] ) ) { 
          $qty_args['max_value'] = $qty_args['max_value'] < 0 ? 0 : $qty_args['max_value']; 
          $qty_args['max_value'] = $qty_args['max_value'] < $qty_args['min_value'] ? $qty_args['min_value'] : $qty_args['max_value']; 
      }
      
      if ($qty_args['min_value'] == '') { $qty_args['min_value'] = 0; }
      if ($qty_args['max_value'] == '') { $qty_args['max_value'] = 9999999999999999; }
      
      ob_start(); 

      wc_get_template( 'global/quantity-input.php', $qty_args );
      
      $hidden_qty_field = ob_get_clean();
      $hidden_qty_field = str_replace('"number"', '"hidden"', $hidden_qty_field);
            
      if ($product->is_in_stock() == 1 || $product->backorders_allowed()) {
        // $form .= '<input type="hidden" class="hidden_quantity" name="quantity" value="'. ($wcplpro_default_qty != '' ? apply_filters('wcplpro_default_qty', $wcplpro_default_qty, $product) : 1) .'" />';
        $form .= $hidden_qty_field;
      }
      
      $form .= '<input type="hidden" class="gift_wrap" name="gift_wrap" value="" />';
      

      $out .= $wcplpro_before_single_row .'
          <tr class="product_id_'. $product->get_id() .' '.$product_avail['class'].' vtrow" 
              data-price="'.wc_format_decimal(wc_get_price_to_display($product), 2) .'">';
              
      $col_checker = array(); // checks if column has any data
      
      // loop ordered columns
      foreach ($wcplpro_order as $colkey => $col_title) {
        
        $allcolumns = array();
        
        /****************************/
        //categories
        if ($colkey == 'wcplpro_categories' && $wcplpro_categories == 1) {
          
          $col_checker[$colkey] = true;
          
          $allcolumns[$colkey] = '
            <td class="categoriescol"  data-title="'. apply_filters('wcplpro_dl_categories', $headenames[$colkey], $product) .'" data-sort-value="'. strip_tags(wc_get_product_category_list($product->get_id())) .'">
              '. wc_get_product_category_list($product->get_id()) .'
            </td>';
        }
        
        
        /****************************/
        //tags
        if ($colkey == 'wcplpro_tags' && $wcplpro_tags == 1) {
          
          $col_checker[$colkey] = true;
          
          $allcolumns[$colkey] = '
            <td class="tagscol"  data-title="'. apply_filters('wcplpro_dl_tags', $headenames[$colkey], $product) .'" data-sort-value="'. strip_tags(wc_get_product_tag_list($product->get_id())) .'">
              '. wc_get_product_tag_list($product->get_id(), ', ') .'
            </td>';
        }
        
        
        /****************************/
        //title
        if ($colkey == 'wcplpro_title' && $wcplpro_title == 1) {
          
          $col_checker[$colkey] = true;
          
          ob_start();
          do_action('wcplpro_after_title', $product->get_id(), $product);
          $wcplpro_after_title = ob_get_clean();
          
          ob_start();
          do_action('wcplpro_before_attributes', $product->get_id(), $product);
          $wcplpro_before_attributes = ob_get_clean();
          
          $custom_attributes = $attributes_out = '';
          $custom_attributes = $product->get_attributes();
          
          if ($wcplpro_attributes == 1 && !empty($custom_attributes)){

            foreach ($custom_attributes as $cattr) {
              if ($cattr['is_visible'] == 1 || $cattr['is_variation'] == 1) {
                $attributes_out .= '<div class="wcplpro_attributes"> <span>'.wc_attribute_label($cattr['name'], $product).':</span> '. str_replace(' | ', ', ', $product->get_attribute($cattr['name']).'</div>');
              }
            }
            
            $attributes_out = apply_filters('wcplpro_attributes', $attributes_out, $product);
          }
          
          if ($wcplpro_dont_link_to_product != 1) {
            $allcolumns[$colkey] = '
              <td class="titlecol"  data-title="'. apply_filters('wcplpro_dl_title', $headenames[$colkey], $product) .'">
                <a href="'. get_permalink($product->get_id())  .'" title="'. $product->get_title() .'">'. $product->get_title() .'</a>
                '. $wcplpro_before_attributes .'
                '. $attributes_out .'
                '. $wcplpro_after_title .'
              </td>';
          } else {
            $allcolumns[$colkey] = '
              <td class="titlecol"  data-title="'. apply_filters('wcplpro_dl_title', $headenames[$colkey], $product) .'">
                '. $product->get_title() .'
                '. $wcplpro_before_attributes .'
                '. $attributes_out .'
                '. $wcplpro_after_title .'
              </td>';
          }
            
          $allcolumns[$colkey] = apply_filters('wcplpro_colum_title', $allcolumns[$colkey], $product);
        }
        
        /****************************/
        //sku
        if ($colkey == 'wcplpro_sku' && $wcplpro_sku == 1) {
          $col_checker[$colkey] = true;
          $allcolumns[$colkey] = '<td class="skucol" data-title="'. apply_filters('wcplpro_dl_sku', $headenames[$colkey], $product) .'" >'. ($product->get_sku() != '' ? $product->get_sku() : '&nbsp;') .'</td>';
        }
        
        
        
        
        /****************************/
        //custom meta
        if ($colkey == 'wcplpro_custommeta' && $wcplpro_custommeta == 1 && trim($wcplpro_metafield) != '') {
          $col_checker[$colkey] = true;
          
          $wcplpro_metafields = explode(',', $wcplpro_metafield);
          
          $all_meta_columns = array();
          if (!empty($wcplpro_metafields)) {
            foreach ($wcplpro_metafields as $metafield) {
              
              $metafield_array = explode('|', $metafield);
              $metafield_key = $metafield_array[0];
              $metafield_label = isset($metafield_array[1]) ? $metafield_array[1] : $metafield_array[0];
              
              $headenames['wcplpro_meta_'. $metafield_key] = $metafield_label;
              $custom_meta_header['wcplpro_meta_'. $metafield_key] = $metafield_label;
              
              $get_post_meta = '';
              $get_post_meta = get_post_meta( $product->get_id(), $metafield_key, true );
              if (is_array($get_post_meta)) {
                $get_post_meta = implode(', ', $get_post_meta);
              }
              
              $all_meta_columns[] = '
                <td class="metacol meta_'. $metafield_key .'" data-title="'. apply_filters('wcplpro_dl_'. $metafield_key, $headenames['wcplpro_meta_'. $metafield_key], $product) .'" >
                '. apply_filters('wcplpro_post_meta', $get_post_meta, $metafield_key, $product) .'
                </td>';
            }
            
            $all_meta_columns = apply_filters('wcplpro_all_meta_columns', $all_meta_columns, $product);
            $allcolumns[$colkey] = apply_filters('wcplpro_custommeta', implode("\n", $all_meta_columns), $product);
          }
          
        }
        
        
        /****************************/
        //thumb
        if ($colkey == 'wcplpro_thumb' && $wcplpro_thumb == 1) {
          $col_checker[$colkey] = true;
          
          $rowimg = '';
          $var_feat_image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), array($wcplpro_thumb_size, $wcplpro_thumb_size));
          $rowimgfull = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'full');
          if (!empty($var_feat_image)) { 
            $rowimg = $var_feat_image; 
          }
        
          if (isset($rowimg[0])) {
            
            $imglink    = '';
            $imgtarget  = '';
            $imgrel     = '';
            $imglinkcss = '';
            if ($wcplpro_thumb_link == 'image' || $wcplpro_thumb_link == '') {
              $imglink = $rowimgfull[0];
              $imgrel = 'data-fancybox="wcpl_gallery_'. $vtrand .'"';
              $imglinkcss = 'wcpl_zoom';
            }
            if ($wcplpro_thumb_link == 'product') {
              $imglink = get_permalink($product->get_id());
            }
            if ($wcplpro_thumb_link == 'productnew') {
              $imglink = get_permalink($product->get_id());
              $imgtarget = 'target="_blank"';
            }
            
            $allcolumns[$colkey] = '<td class="thumbcol"  data-title="'. apply_filters('wcplpro_dl_thumb', $headenames[$colkey], $product) .'">
              <a href="'. $imglink .'" '. $imgtarget .' itemprop="image" class="wcplproimg '. $imglinkcss .' '. apply_filters( 'wcplpro_thumb_class_filter', 'thumb', $product) .'" title="'. $product->get_title() .'"  '. $imgrel .'>
                <img src="'. $rowimg[0] .'" alt="'. $product->get_title() .'" width="'. $rowimg[1] .'" height="'. $rowimg[2] .'" style="max-width: '. $wcplpro_thumb_size.'px; height: auto;" />
              </a>
            </td>';
          } else {
            $allcolumns[$colkey] = '<td class="thumbcol" data-title="'. apply_filters('wcplpro_dl_thumb', $headenames[$colkey], $product) .'">
              '. apply_filters( 'woocommerce_single_product_image_html', sprintf( '<img src="%s" alt="%s" style="width: '. $wcplpro_thumb_size.'px; height: auto;" />', wc_placeholder_img_src(), __( 'Placeholder', 'woocommerce' ) ), $product->get_id() ).'
              </td>';
          }

        }
        
        
        /****************************/
        //stock
        if ($colkey == 'wcplpro_stock' && $wcplpro_stock == 1) {
          $col_checker[$colkey] = true;
          $allcolumns[$colkey] = '<td class="stockcol" data-title="'. apply_filters('wcplpro_dl_stock', $headenames[$colkey], $product) .'"><span class="'. $product_avail['class'] .'">'. ($product_avail['availability'] != '' ? $product_avail['availability'] : '&nbsp;') .'</span></td>';
        }
        
        
        /****************************/
        //weight
        if ($colkey == 'wcplpro_weight' && $wcplpro_weight == 1) {
          $col_checker[$colkey] = true;
          $anyweight = 1;
          if ($product->has_weight()) {
            $allcolumns[$colkey] = '
              <td class="weight_col" data-sort-value="'. $product->get_weight() .'" data-title="'. apply_filters('wcplpro_dl_weight', $headenames[$colkey], $product) .'">'. $product->get_weight().($product->has_weight() ? ' '.get_option('woocommerce_weight_unit') : '') .'</td>';
              $col_checker[$colkey] = true;
            } else {
              $allcolumns[$colkey] = '
              <td class="weight_col" data-title="'. apply_filters('wcplpro_dl_weight', $headenames[$colkey], $product) .'">&nbsp;</td>';
            }
        }
        
        
        /****************************/
        //dimensions
        if ($colkey == 'wcplpro_dimensions' && $wcplpro_dimensions == 1) {
          $col_checker[$colkey] = true;
          $wcplpro_dimensions_str = '&nbsp;';
          if (wc_format_dimensions($product->get_dimensions(false))) {
            $wcplpro_dimensions_str = wc_format_dimensions($product->get_dimensions(false));
          }
          
          if ($product->has_dimensions()) {
            $anydimension = 1;
          }

          $allcolumns[$colkey] = '
            <td class="dimensions_col" data-title="'. apply_filters('wcplpro_dl_dimensions', $headenames[$colkey], $product) .'">'. $wcplpro_dimensions_str .'</td>';
        }
        
        
        /****************************/
        //offer image
        if ($colkey == 'wcplpro_offer' && $wcplpro_offer == 1 && $wcplpro_image != '' && get_post_meta( $product->get_id(), 'wcplpro_offer_status', true ) != 'disable') {
          $col_checker[$colkey] = true;
          $override_extra_image = (isset($product_meta['wcplpro_override_extra_image']) ? $product_meta['wcplpro_override_extra_image'][0] : null);
          
          if (!empty($override_extra_image)) {
            $allcolumns[$colkey] = '
              <td class="offercol"  data-title="'. apply_filters('wcplpro_dl_offer', $headenames[$colkey], $product) .'">
                <img src="'. $override_extra_image .'" alt="'.  __('offer', 'wcplpro') .'" style="max-width: '. $wcplpro_thumb_size.'px; height: auto;"  />
              </td>';
            $anyextraimg = 1;
          } 
          if ($wcplpro_image !='' && empty($override_extra_image)) {
            $allcolumns[$colkey] = '
              <td class="offercol"  data-title="'. apply_filters('wcplpro_dl_offer', $headenames[$colkey], $product) .'">
                <img src="'. $wcplpro_image .'" alt="'.  __('offer', 'wcplpro') .'" style="max-width: '. $wcplpro_thumb_size.'px; height: auto;" />
              </td>';
            $anyextraimg = 1;
          }        
        }
        
        
        /****************************/
        //quantity
        if ($colkey == 'wcplpro_qty' && $wcplpro_qty == 1) {
          $col_checker[$colkey] = true;
          $wcplpro_qty_step = 1;
          // $wcplpro_qty_step = (isset($product_meta['wcplpro_qty_step']) ? $product_meta['wcplpro_qty_step'][0] : 1);
          // $wcplpro_default_qty = (isset($product_meta['wcplpro_default_qty']) ? $product_meta['wcplpro_default_qty'][0] : 1);
          
          $allcolumns[$colkey] = '
            <td class="qtycol" data-title="'. apply_filters('wcplpro_dl_qty', $headenames[$colkey], $product) .'">';
            
          if (($product->is_in_stock() || $product->backorders_allowed()) && $product_type != 'variable') {
            
            
              $allcolumns[$colkey] .= '
              <table class="qtywrap">
                <tr>
              ';
              
            if ($wcplpro_qty_control == 1) {
              $allcolumns[$colkey] .= '
              <td><div class="minusqty qtycontrol">-</div></td>
              ';
            }
            
            
            $qty_defaults = array( 
              'input_name' => 'wcplpro_quantity',  
              'input_value' => ($wcplpro_default_qty != '' ? apply_filters('wcplpro_default_qty', $wcplpro_default_qty, $product) : 1),  
              'max_value' => apply_filters( 'woocommerce_quantity_input_max', '', $product ),  
              'min_value' => apply_filters( 'woocommerce_quantity_input_min', '', $product ),  
              'step' => apply_filters( 'woocommerce_quantity_input_step', apply_filters('wcplpro_qty_step', $wcplpro_qty_step, $product), $product ),  
              'pattern' => apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ),  
              'inputmode' => apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' ),  
              'class' => 'input-text qty text'
            );
            $qty_args = '';
            $qty_args = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( $qty_args, $qty_defaults ), $product );
            
            // Apply sanity to min/max args - min cannot be lower than 0 
            if ( '' !== $qty_args['min_value'] && is_numeric( $qty_args['min_value'] ) && $qty_args['min_value'] < 0 ) { 
                $qty_args['min_value'] = 0; // Cannot be lower than 0 
            }
            // Max cannot be lower than 0 or min 
            if ( '' !== $qty_args['max_value'] && is_numeric( $qty_args['max_value'] ) ) { 
                $qty_args['max_value'] = $qty_args['max_value'] < 0 ? 0 : $qty_args['max_value']; 
                $qty_args['max_value'] = $qty_args['max_value'] < $qty_args['min_value'] ? $qty_args['min_value'] : $qty_args['max_value']; 
            }
            
            if ($qty_args['min_value'] == '') { $qty_args['min_value'] = 0; }
            if ($qty_args['max_value'] == '') { $qty_args['max_value'] = 9999999999999999; }
            
            ob_start(); 
 
            wc_get_template( 'global/quantity-input.php', $qty_args ); 
       // <input type="number" step="'.  .'" name="wcplpro_quantity" value="'.  .'" title="Qty" class="input-text qty text" size="4" min="'. apply_filters( 'woocommerce_quantity_input_min', 0, $product) .'" '. (intval($product->get_stock_quantity()) > 0 ? 'max="'. apply_filters( 'woocommerce_quantity_input_max', $product->get_stock_quantity(), $product) .'"': '') .'>
            
            $qty_field = ob_get_clean(); 
            
            $allcolumns[$colkey] .= '<td>'.$qty_field.'</td>';
          
            if ($wcplpro_qty_control == 1) {
              $allcolumns[$colkey] .= '
                <td><div  class="plusqty qtycontrol">+</div></td>
              ';
            }
              $allcolumns[$colkey] .= '
                  </tr>
                </table>
              ';
              
          }
          $allcolumns[$colkey] .= '</td>';
          
        
        }
        
        
        /****************************/
        //gift wrap
        if ($colkey == 'wcplpro_gift' && $wcplpro_gift == 1 && $is_wrappable == 'yes') {
          $col_checker[$colkey] = true;
          $current_value = ! empty( $_REQUEST['gift_wrap'] ) ? 1 : 0;

          $cost = (isset($product_meta['_gift_wrap_cost']) ? $product_meta['_gift_wrap_cost'][0] : $gift_wrap_cost);

          $price_text = $cost > 0 ? woocommerce_price( $cost ) : __( 'free', 'woocommerce-product-gift-wrap' );
          $checkbox   = '<input type="checkbox" class="wcplpro_gift_wrap" name="wcplpro_gift_wrap" value="yes" ' . checked( $current_value, 1, false ) . ' />';
          
          
          $allcolumns[$colkey] = '
          <td class="giftcol" data-title="'. apply_filters('wcplpro_dl_gift', $headenames[$colkey], $value) .'">
            <label>'.  str_replace(array('{price}', '{checkbox}',), array($price_text, $checkbox), $product_gift_wrap_message) .'</label>
          </td>';
        
        }
        
        
        /****************************/
        //yith wishlist
        if ($colkey == 'wcplpro_wishlist' && $wcplpro_wishlist == 1 && defined( 'YITH_WCWL' )) {
          $col_checker[$colkey] = true;
          $url=strtok($_SERVER["REQUEST_URI"],'?');
          parse_str($_SERVER['QUERY_STRING'], $query_string);
          $query_string['add_to_wishlist'] = basename($product->get_id());
          $rdr_str = http_build_query($query_string);
          
          $wishlist = do_shortcode('[yith_wcwl_add_to_wishlist product_id='. $product->get_id() .' icon="'. (get_option('yith_wcwl_add_to_wishlist_icon') != '' && get_option('yith_wcwl_use_button') == 'yes' ? get_option('yith_wcwl_add_to_wishlist_icon') : 'fa-heart') .'"]');
        
          $allcolumns[$colkey] = '
            <td class="wishcol" data-title="'. apply_filters('wcplpro_dl_wishlist', $headenames[$colkey], $product) .'">
              '. wcplpro_delete_all_between('</i>', '</a>', $wishlist) .'
            </td>';
        
        }
        
        
        /****************************/
        //price
        if ($colkey == 'wcplpro_price' && $wcplpro_price == 1) {
          
          $price_html = '';
          $price_html = $product->get_price_html();
          if ($product_type == 'variable') {
            $vproduct = new WC_Product_Variable($product->get_id());
            $price_html = $vproduct->get_price_html();
            unset($vproduct);
          }
          
          $col_checker[$colkey] = true;
          $allcolumns[$colkey] = '
            <td class="pricecol" 
              data-title="'. apply_filters('wcplpro_dl_price', $headenames[$colkey], $product) .'" 
              data-price="'.wc_format_decimal(wc_get_price_to_display($product), 2) .'" 
              data-sort-value="'. wc_format_decimal(wc_get_price_to_display($product), 2) .'">
              '. $price_html .'
            </td>';
        
        }
        
        
        /****************************/
        //total
        if ($colkey == 'wcplpro_total' && $wcplpro_total == 1) {
          $col_checker[$colkey] = true;
          if ($product_type != 'variable') {
          $allcolumns[$colkey] = '
            <td class="totalcol" data-title="'. apply_filters('wcplpro_dl_total', $headenames[$colkey], $product) .'" data-sort-value="'. wc_format_decimal(wc_get_price_to_display($product) * ($wcplpro_default_qty > 0 ? $wcplpro_default_qty : apply_filters('wcplpro_default_qty', $wcplpro_default_qty, $product)), 2) .'">
              '. wc_price(wc_get_price_to_display($product) * ($wcplpro_default_qty > 0 ? $wcplpro_default_qty : apply_filters('wcplpro_default_qty', $wcplpro_default_qty, $product))) .'
              '. (get_option('woocommerce_price_display_suffix') != '' ? ' '.get_option('woocommerce_price_display_suffix') : '') .'
            </td>';
          } else {
            $allcolumns[$colkey] = '
            <td class="totalcol" data-title="'. apply_filters('wcplpro_dl_total', $headenames[$colkey], $product) .'" data-sort-value="'. wc_format_decimal(wc_get_price_to_display($product) * ($wcplpro_default_qty > 0 ? $wcplpro_default_qty : apply_filters('wcplpro_default_qty', $wcplpro_default_qty, $product)), 2) .'">
            </td>
            ';
          }
        
        }
        
        //add to cart button
        if ($colkey == 'wcplpro_cart') { // && $wcplpro_cart == 1
          $col_checker[$colkey] = true;
          ob_start();
          do_action('woocommerce_add_to_cart_class', $product->get_id(), $product);
          $woocommerce_add_to_cart_class = ob_get_clean();
          
          ob_start();
          do_action('woocommerce_before_add_to_cart_button', $product->get_id(), $product);
          $woocommerce_before_add_to_cart_button = ob_get_clean();
          
          $allcolumns['wcplpro_cart'] = '<td class="cartcol '. ($wcplpro_cart == 0 ? 'wcplprohide' : '') .' '. $woocommerce_add_to_cart_class .'" data-title="">'.$woocommerce_before_add_to_cart_button;
          
          // trick badly coded import plugins that do not set stock status
          if (!isset($product_meta['_stock_status'])) {
            if ( method_exists( $product, 'get_stock_status' ) ) {
              $product_meta['_stock_status'][0] = $product->get_stock_status(); // For version 3.0+
            } else {
              $product_meta['_stock_status'][0] = $product->stock_status; // Older than version 3.0
            }
          }
          
          // if is in stock or backorders are allowed
          if (isset($product_meta['_stock_status']) || $product->backorders_allowed()) {
            
            // if is out of stock and backorder are allowed
            if (
              (isset($product_meta['_stock_status']) && $product_meta['_stock_status'][0] != 'instock' && $product->backorders_allowed()) 
              ||
              ($wcplpro_zero_to_out == 1 && $product->get_stock_quantity() == 0 && $product->managing_stock() == true)
            ) { 
              $carttext = __( 'Backorder', 'wcplpro' ); 
            } else { 
              $carttext = __('Add to cart', 'wcplpro' ); 
            }
			
			$carttext = apply_filters('woocommerce_product_add_to_cart_text', $carttext, $product->get_type(), $product);
            
            $wcplpro_button_classes = apply_filters('wcplpro_single_button_classes', array(
                  'single_add_to_cart_button',
                  'button',
                  'button_theme',
                  'ajax',
                  'add_to_cart',
                  'avia-button',
                  'fusion-button',
                  'button-flat',
                  'button-round'
                )
              );
              
            if (class_exists( 'YITH_WCQV_Frontend' )) {
              ob_start();
              $YITH_WCQV_Frontend->yith_add_quick_view_button();
              $yith_quickview = ob_get_clean();
            }
            
            if ($product_type == 'variable' || (!(wc_format_decimal(wc_get_price_to_display($product), 2) > 0) && $wcplpro_zero_to_out == 1) || $product_meta['_stock_status'][0] == 'outofstock') {
              $allcolumns['wcplpro_cart'] .= apply_filters( 'woocommerce_loop_add_to_cart_link', '<a href="'. get_the_permalink() .'" id="add2cartbtn_'. $product->get_id() .'" data-product_id="'. $product->get_id() .'" class="'. implode(' ', $wcplpro_button_classes) .' alt">'. apply_filters('single_add_to_cart_text', $product->add_to_cart_text(), $product->get_type(), $product) .'</a>', $product);
                            
              if (class_exists( 'YITH_WCQV_Frontend' ) && isset($wcplpro_quickview) && ($wcplpro_quickview == 'all' || $wcplpro_quickview == 'variable')) {
                $allcolumns['wcplpro_cart'] .= $yith_quickview;
              }
              
            } else {
              
              $allcolumns['wcplpro_cart'] .= $form.'
                '. apply_filters( 'woocommerce_loop_add_to_cart_link', '<button id="add2cartbtn_'. $product->get_id() .'" type="submit" data-product_id="'. $product->get_id() .'" class="'. implode(' ', $wcplpro_button_classes) .'">'. apply_filters('single_add_to_cart_text', $carttext, $product->get_type(), $product) .'</button>', $product);
              if ($wcplpro_ajax == 1 || $wcplpro_globalcart == 1 || $wcplpro_globalcart == 2) {
                $allcolumns['wcplpro_cart'] .= '
                  <div class="added2cartwrap" id="added2cart_'.$product->get_id().'"><span class="added2cart" >&#10003;</span></div>
                  <span class="vtspinner singlebtn vtspinner_'. $product->get_id() .'">
                    <img src="'. plugins_url('images/spinner.png', __FILE__) .'" width="16" height="16" alt="spinner" />
                  </span>
                  ';
              } else {
                $allcolumns['wcplpro_cart'] .= '
                  <div class="added2cartwrap notvisible" id="added2cart_'.$product->get_id().'"></div>
                  <span class="vtspinner vtspinner_'. $product->get_id() .' notvisible"></span>
                  ';
              }
              
              if (class_exists( 'YITH_WCQV_Frontend' ) && isset($wcplpro_quickview) && ($wcplpro_quickview == 'all' || $wcplpro_quickview == 'simple')) {
                $allcolumns['wcplpro_cart'] .= $yith_quickview;
              }
              
              
            }
          } else {
            
            $allcolumns['wcplpro_cart'] .= '
              '. apply_filters( 'woocommerce_loop_add_to_cart_link', '<a href="'. get_the_permalink() .'" id="add2cartbtn_'. $product->get_id() .'" data-product_id="'. $product->get_id() .'" class="single_add_to_cart_button button alt ajax add_to_cart">'. apply_filters('single_add_to_cart_text', $product->add_to_cart_text(), $product->get_type(), $product) .'</a>', $product) .'
              <div class="added2cartwrap notvisible" id="added2cart_'.$product->get_id().'"></div>
              <span class="vtspinner vtspinner_'. $product->get_id() .' notvisible"></span>
            ';
            
            if (class_exists( 'YITH_WCQV_Frontend' ) && isset($wcplpro_quickview) && ($wcplpro_quickview == 'all' || $wcplpro_quickview == 'simple')) {
              $allcolumns['wcplpro_cart'] .= $yith_quickview;
            }
            
          }
          
          ob_start();
          do_action('woocommerce_after_add_to_cart_button', $product->get_id(), $product);
          $woocommerce_after_add_to_cart_button = ob_get_clean();
          
          $allcolumns['wcplpro_cart'] .= $woocommerce_after_add_to_cart_button .'</form></td>';
          
          $allcolumns['wcplpro_cart'] = apply_filters('wcplpro_add_to_cart_td', $allcolumns['wcplpro_cart'], $product);
        }
        
        //global cart checkbox
        if ($colkey == 'wcplpro_globalcart' && ($wcplpro_globalcart == 1 || $wcplpro_globalcart == 2)) {
          $col_checker[$colkey] = true;
          $allcolumns['wcplpro_globalcart'] = '<td class="globalcartcol '. ($wcplpro_globalcart == 2 ? 'vartablehide' : '') .'" data-title="'. apply_filters('wcplpro_dl_globalcart', $headenames['wcplpro_globalcart'], $product) .'">';
          if ((get_post_meta($product->get_id(), '_stock_status', true) != 'outofstock' || !empty($value['backorders_allowed'])) && $product_type != 'variable' && !(wc_format_decimal(wc_get_price_to_display($product), 2) <= 0 && $wcplpro_zero_to_out == 1) )   {   
            $allcolumns['wcplpro_globalcart'] .= '  <input type="checkbox" class="globalcheck" name="check_'. $product->get_id() .'" value="1" '. ($wcplpro_globalcart == 2 || $wcplpro_global_status == 1 ? 'checked="checked"' : '') .'>';
          }
          $allcolumns['wcplpro_globalcart'] .= '</td>';
        }
        
        // prepare the excerpt
        $excerpt = '';
        
            
        ob_start();
        do_action('wcplpro_before_excerpt', $current_post, $product);
        $wcplpro_before_excerpt = ob_get_clean();
        $wcplpro_before_excerpt_out = $wcplpro_before_excerpt;
        
        ob_start();
        do_action('wcplpro_after_excerpt', $current_post, $product);
        $wcplpro_after_excerpt = ob_get_clean();
        $wcplpro_after_excerpt_out = $wcplpro_after_excerpt;
        
        if (absint(get_option('wcplpro_excerpt_length')) > 0) {
          $excerpt = wcplpro_excerpt_max_length(absint(get_option('wcplpro_excerpt_length')), get_the_excerpt());
        } else {
          $excerpt = get_the_excerpt();
        }
        
        if ($wcplpro_desc == 2) {
          ob_start();
          the_content();
          $wcplpro_full_excerpt = ob_get_clean();
          $excerpt = $wcplpro_full_excerpt;
        }
        
        // add actions to excerpt
        $excerpt = $wcplpro_before_excerpt.$excerpt.$wcplpro_after_excerpt;
        
        //description
        if ($colkey == 'wcplpro_desc' && ($wcplpro_desc == 1 || $wcplpro_desc == 2) && $wcplpro_desc_inline == 1) {
          $col_checker[$colkey] = true;
          if ($excerpt != '') {
            $anydescription = 1;
          }
                   
          $allcolumns[$colkey] = '
            <td class="desccol" data-title="'. apply_filters('wcplpro_dl_desc', $headenames[$colkey], $product) .'">'. $excerpt .'</td>';
            
        }
        
        
        // implode all columns
        $out .= implode("\n", apply_filters('wcplpro_allcolumns', $allcolumns, $product));
        
        // $out .= '<a href="'. get_the_permalink() .'">'. get_the_title().'</a><br />';
      }
      
      $out .= '</tr>';
      
      if (($wcplpro_desc == 1 || $wcplpro_desc == 2) && $excerpt != '' && $wcplpro_desc_inline != 1) {
        $out .= '
        <tr class="descrow desc_'.$product->get_id() .'">
          <td class="desccol" colspan="'. (count($headenames) - 1) .'" data-title="'. apply_filters('wcplpro_dl_desc', $headenames['wcplpro_desc'], $product) .'">'. $excerpt .'</td>
        </tr>';
      }
      
      
      
    } // The Loop END
    
    
    $out .= '</table>
    </div>
    ';
    
    if (($wcplpro_globalcart == 1 || $wcplpro_globalcart == 2) && ($wcplpro_globalposition == 'bottom' || $wcplpro_globalposition == 'both')) {
      
      ob_start();
      do_action('wcplpro_add_gc_button', $product->get_id());
      $wcplpro_add_gc_button = ob_get_clean();
 
      $out .= apply_filters('wcplpro_global_btn', '
        <div class="gc_wrap">
          <a data-position="bottom" href="#globalcart" class="globalcartbtn submit btn single_add_to_cart_button button alt" data-product_id="gc_'.$product->get_id() .'" id="gc_'. $vtrand .'_bottom" class="btn button alt">
            '. apply_filters('wcplpro_global_btn_text', __('Add selected to cart', 'wcplpro'), $query_args, $query) .'
            <span class="vt_products_count"></span> 
            <span class="vt_total_count '. ($wcplpro_hide_global_total == 1 ? 'wcplprohide' : '') .'"></span>
          </a>
          <span class="added2cartglobal added2cartglobal_'. $vtrand .'">&#10003;</span>
          <span class="vtspinner vtspinner_bottom vtspinner_'. $vtrand .'"><img src="'. plugins_url('images/spinner.png', __FILE__) .'" width="16" height="16" alt="spinner" /></span>
        </div>
      ', $product, 'bottom', $vtrand );
    }
    
    
    ob_start();
    do_action('wcplpro_before_filters_bottom', $current_post);
    $wcplpro_before_filters_bottom = ob_get_clean();
    
    $out .= $wcplpro_before_filters_bottom;
    
    
    $out .= '<div class="wcpl_group bottom">';
    
    // add drops down filters
    if (($wcplpro_filters_position == 'after' || $wcplpro_filters_position == 'both') || ($wcplpro_pagination == 'after' || $wcplpro_pagination == 'both')) {
      
      $out .= wcplpro_filters_form($wcplpro_filter_cat, $wcplpro_filter_tag, $wcplpro_filter_search, get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_cat'), get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_tag'), get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_search'), $wcplpro_wcplid, 'after');
      
    }
    
    if ($wcplpro_pagination == 'after' || $wcplpro_pagination == 'both') {
      $out .= wcplpro_pagination($wcplpro_posts_per_page, $query, $wcplpro_paged, $useruniq, $wcplpro_wcplid);
    }
    
    $out .= '</div>';
    
    ob_start();
    do_action('wcplpro_after_filters_bottom', $current_post);
    $wcplpro_after_filters_bottom = ob_get_clean();
    
    $out .= $wcplpro_after_filters_bottom;
    
    
    ob_start();
    do_action('wcplpro_after_table', $current_post);
    $wcplpro_after_table = ob_get_clean();
    $out .= $wcplpro_after_table;
    
    
    
    // create header
    
    $remove_header_text = apply_filters('wcplpro_remove_header_text', array('wcplpro_thumb', 'wcplpro_offer', 'wcplpro_cart', 'wcplpro_globalcart'), $product);
    
    $head_array = array();
    foreach($wcplpro_order as $colkey => $colname) {
      if((${$colkey} == 1 || $colkey == 'wcplpro_cart') && isset($col_checker[$colkey]) && $col_checker[$colkey] == true) {
        
        if (in_array($colkey, $remove_header_text)) { $colname = ''; }
        if ($colkey == 'wcplpro_globalcart') { $colname = '<input type="checkbox" class="checkall wcplprotable_selectall_check" name="checkall_'. $vtrand .'" id="checkall_'. $vtrand .'" value="1" />'; }
        
        ob_start();
        do_action('wcplpro_th_class', $product);
        $wcplpro_th_class = ob_get_clean();
        
        
        $skip_sorting = apply_filters('wcplpro_skip_columns', array(
          'wcplpro_globalcart',
          'wcplpro_cart',
          'wcplpro_thumb',
          'wcplpro_qty',
          'wcplpro_offer'
        ));
        
        $sort_as_string = apply_filters('wcplpro_sort_as_string', array(
          'wcplpro_title',
          'wcplpro_sku',
          'wcplpro_tags',
          'wcplpro_categories'
        ));
        
        if ($colkey == 'wcplpro_custommeta' && !empty($custom_meta_header)) {
          
          foreach ($custom_meta_header as $meta_array_key => $meta_post_key) {
            $head_array[$meta_array_key] = '<th class="'. $colkey .' '. $meta_array_key .' '. (!in_array($meta_array_key, $skip_sorting) ? 'sortable_th' : '') .' '. $wcplpro_th_class .'" '. (!in_array($meta_array_key, $skip_sorting) ? 'data-sort="'. (in_array($meta_array_key, $sort_as_string) ? 'string' : 'float') .'"' : '') .'>'. ((isset($wcplpro_columns_names[$colkey]) && $wcplpro_columns_names[$colkey] != '') ? $wcplpro_columns_names[$colkey] : $meta_post_key) .'</th>';
          }
          
        } else {
          
          $head_array[$colkey] = '<th class="'. $colkey .' '. (($colkey == 'wcplpro_cart' && $wcplpro_cart == 0) ? 'wcplprohide' : '') .' '. (!in_array($colkey, $skip_sorting) ? 'sortable_th' : '') .' '. $wcplpro_th_class .'" '. (!in_array($colkey, $skip_sorting) ? 'data-sort="'. (in_array($colkey, $sort_as_string) ? 'string' : 'float') .'"' : '') .'>'. ((isset($wcplpro_columns_names[$colkey]) && $wcplpro_columns_names[$colkey] != '') ? $wcplpro_columns_names[$colkey] : $headenames[$colkey]) .'</th>';
          
        }
      }
    }
    
    if ($wcplpro_head != 0) {
      $head = '
        <thead>
          <tr>
            '.
            implode("\n", apply_filters( 'wcplpro_header_th', $head_array, $product->get_id()))
            .' 
          </tr>
        </thead>
      ';
    } else {
      $head = '';
    }
    
    
    
    $out = str_replace('%headplaceholder%', $head, $out);

    wp_reset_postdata();
    
  } // IF Products END
  else {
    
    $out .= wcplpro_filters_form($wcplpro_filter_cat, $wcplpro_filter_tag, $wcplpro_filter_search, get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_cat'), get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_tag'), get_transient('wcpl_'. $useruniq .'_'.$wcplpro_wcplid .'_search'), $wcplpro_wcplid, $wcplpro_filters_position);
    
    $out = '
    <div class="clearfix clear wcp  wcplpro_noproducts">'. $out .'</div>
    <br /><hr />
    <div class="clearfix clear wcp  wcplpro_noproducts"><p>'.__('No products found', 'wcplpro').'</p></div>
    ';
  }
  
  $out = apply_filters('wcplpro_output', $out);


  if($wcplpro_echo == 1) {
    echo $out;
  } else {
    return $out;
  }
}

add_action( 'admin_enqueue_scripts', 'wcplpro_wp_admin_scripts' );
function wcplpro_wp_admin_scripts($hook) {
    
  global $post;
  
  $include_s2 = 1;
  $is_page_or_post = 0;
  
  if (isset($post->post_type) && ( $post->post_type == 'post' || $post->post_type == 'page')) {
    $is_page_or_post = 1;
  }
  if ( isset($_GET['post_type']) && ($_GET['post_type']=='post' || $_GET['post_type'] =='page')) {
    $is_page_or_post = 1;
  }
  
  if (isset($post->post_type) && ( $post->post_type == 'shop_order' || $post->post_type == 'product'  || $post->post_type == 'wc_membership_plan' ) && ($hook == 'post-new.php' || $hook == 'post.php') ) {
    $include_s2 = 0;
  }
  if ( isset($_GET['post_type']) && ($_GET['post_type']=='product' || $_GET['post_type'] =='shop_order' || (isset($_GET['wc_membership_plan']) && $_GET['wc_membership_plan'] =='shop_order'))  && ($hook == 'post-new.php' || $hook == 'post.php') ) {
    $include_s2 = 0;
  }
  
  $include_s2 = apply_filters('wcplpro_include_s2', $include_s2, $post, $_GET, $hook);
  
  if (  ( 
          (isset($_GET['page']) && $_GET['page'] == 'productslistpro') || 
          (($hook == 'post-new.php' || $hook == 'post.php') && ($is_page_or_post == 1) )
        ) && 
        $include_s2 == 1 
      )  {
      wp_register_style( 'wcplpro_select2_css', plugins_url('select2/select2.css', __FILE__) );
      wp_enqueue_style('wcplpro_select2_css');
      wp_register_style( 'wcplpro_admin_css', plugins_url('assets/css/wcplpro-admin.css', __FILE__) );
      wp_enqueue_style('wcplpro_admin_css');
      
      wp_register_script( 'wcplpro_select2_js', plugins_url('select2/select2.min.js',__FILE__ ), array( 'jquery' ));
      wp_enqueue_script('wcplpro_select2_js');
  }
    
}


add_action("wp_enqueue_scripts", "wcplpro_scripts", 20); 
function wcplpro_scripts() {
  
  global $woocommerce, $post;
  
  // get array of all woo pages
  $woo_pages = wcplpro_get_woo_page_ids();
  
  if (isset($post)) { $post_id = $post->ID; } else { $post_id = 0; }
  
  if (!in_array($post_id, $woo_pages)) {
    wp_register_style( 'wcplpro_select2_css', plugins_url('select2/select2.css', __FILE__) );
    wp_enqueue_style('wcplpro_select2_css');
  }
  
  if ( get_option( 'wcplpro_lightbox' ) == 1 ) {
    wp_enqueue_script( 'wcplpro_fancybox_js', plugins_url('assets/js/jquery.fancybox.min.js', __FILE__), array('jquery') );
    wp_enqueue_style( 'wcplpro_fancybox_css', plugins_url('assets/css/jquery.fancybox.min.css', __FILE__) );
  }
  
  wp_register_style( 'wcplpro_css', plugins_url('assets/css/wcplpro.css', __FILE__) );
  wp_enqueue_style('wcplpro_css');
  

  if (!in_array($post_id, $woo_pages)) {
    wp_register_script( 'wcplpro_select2_js', plugins_url('select2/select2.min.js',__FILE__ ), array( 'jquery' ));
    wp_enqueue_script('wcplpro_select2_js');
  }
  
  
  if (get_option('wcplpro_sorting') == 1) {
    wp_register_script( 'wcplpro_table_sort', plugins_url('assets/js/stupidtable.js', __FILE__), array('jquery') );
    wp_enqueue_script("wcplpro_table_sort");    
  }
  
  wp_register_script( 'wcplpro_js', plugins_url('assets/js/wcplpro.js',__FILE__ ), array( 'jquery' ));
  wp_enqueue_script('wcplpro_js');
  
   
  $vars = array( 
    'ajax_url' => admin_url( 'admin-ajax.php' ), 
    'cart_url' => apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url()),
    'currency_symbol' => get_woocommerce_currency_symbol(),
    'thousand_separator' => wc_get_price_thousand_separator(),
    'decimal_separator' => wc_get_price_decimal_separator(),
    'decimal_decimals' => wc_get_price_decimals(),
    'currency_pos' => get_option( 'woocommerce_currency_pos' ),
    'price_display_suffix' => get_option( 'woocommerce_price_display_suffix' ),
    'wcplpro_ajax' => get_option('wcplpro_ajax'),
	'lightbox' => get_option( 'wcplpro_lightbox' ),
  );
  
  $cartredirect = get_option('woocommerce_cart_redirect_after_add');
  if (get_option('direct_checkout_enabled') == 1) {
    $cartredirect = 'yes';
  }
  
  if (get_option('direct_checkout_cart_redirect_url') != '' && $cartredirect == 'yes') {
    $vars['cart_url'] = get_option('direct_checkout_cart_redirect_url');
  }
    
  wp_localize_script( 'wcplpro_js', 'wcplprovars', $vars );
  
}


add_action( 'wp_ajax_add_product_to_cart', 'wcplpro_ajax_add_product_to_cart' );
add_action( 'wp_ajax_nopriv_add_product_to_cart', 'wcplpro_ajax_add_product_to_cart' );

function wcplpro_ajax_add_product_to_cart() {

    ob_start();
    
    $productids  = json_decode(stripslashes($_POST['product_id']), true);
    $quantities   = json_decode(stripslashes($_POST['quantity']), true);
    
    $cartredirect = get_option('woocommerce_cart_redirect_after_add');
    if (get_option('direct_checkout_enabled') == 1) {
      $cartredirect = 'yes';
    }
    
    foreach($productids as $index => $product_id) {

      $product_id   = apply_filters( 'wcplpro_add_to_cart_product_id', absint( $product_id ) );
      $quantity     = empty( $quantities[$index] ) ? 1 : wc_stock_amount( $quantities[$index] );

      // todo variation support
      // $variation_id      = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : '';
      // $variations         = vartable_get_variation_data_from_variation_id($variation_id);

      $passed_validation = apply_filters( 'wcplpro_add_to_cart_validation', true, $product_id, $quantity);

      if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity ) ) {
          
          do_action( 'woocommerce_set_cart_cookies', TRUE );
          do_action( 'wcplpro_ajax_added_to_cart', $product_id );

          if ( $cartredirect == 'yes' && get_option('wcplpro_ajax') != 1) {

            wc_add_to_cart_message( array( $product_id => $quantity ), true );

          }


      } else {

          // If there was an error adding to the cart, redirect to the product page to show any errors
          $data = array(
              'error' => true,
              'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
          );

          wp_send_json( $data );

      }
      
    }
    
    //clear notices if any
    if (get_option('wcplpro_ajax') == 1 || $cartredirect != 'yes') {
      wc_clear_notices();
    }
    // Return fragments
    if (get_option('wcplpro_ajax') == 1) {
      WC_AJAX::get_refreshed_fragments();
    }


    die();
}


function wcplpro_footer_code() {
  global $woocommerce;
  ?>
  <div id="wcplpro_added_to_cart_notification" class="<?php echo (get_option('wcplpro_panel_manualclose') == 1 ? '': 'autoclose'); ?>" style="display: none;">
    <a href="<?php echo wc_get_cart_url(); ?>" title="<?php echo __('Go to cart', 'wcplpro'); ?>"><span></span> <?php echo __('&times; product(s) added to cart', 'wcplpro'); ?> &rarr;</a> <a href="#" class="slideup_panel">&times;</a>
  </div>
  <?php
}
add_action('wp_footer', 'wcplpro_footer_code');






// Add settings link on plugin page
function wcplpro_plugin_settings_link($links) { 
  $settings_link = '<a href="admin.php?page=productslistpro">'. __('Settings', 'wcplpro') .'</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$wcplpro_plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$wcplpro_plugin", 'wcplpro_plugin_settings_link' );


// remove gift wrap frontend hook
function wcplpro_gifthook_the_remove() {
  require_once 'wp-filters-extra.php';
  if (class_exists('WC_Product_Gift_Wrap')) {
    wcplpro_remove_filters_for_anonymous_class( 'woocommerce_after_add_to_cart_button', 'WC_Product_Gift_Wrap', 'gift_option_html', 10 );
  }
}

add_action( 'plugins_loaded', 'wcplpro_gifthook_the_remove', 1) ;



/**
* @return bool
*/
function wcplpro_is_session_started()
{
    if ( php_sapi_name() !== 'cli' ) {
        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        } else {
            return session_id() === '' ? FALSE : TRUE;
        }
    }
    return FALSE;
}


function wcplpro_media_upload($fname, $value = '', $ai='') {
 
// This will enqueue the Media Uploader script
wp_enqueue_media();
?>

    <input type="text" name="<?php echo $fname; ?>" id="<?php echo $fname; ?>" value="<?php echo $value; ?>" class="regular-text">
    <input type="button" name="upload-btn<?php echo $ai; ?>" id="upload-btn<?php echo $ai; ?>" class="button-secondary button button-action" value="<?php echo __('Open Media Manager', 'wcplpro'); ?>"><br />
    <img class="img_<?php echo $ai; ?>" src="<?php echo $value; ?>" />


<script type="text/javascript">
jQuery(document).ready(function($){
    jQuery('#upload-btn<?php echo $ai; ?>').click(function(e) {
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Image',
            // mutiple: true if you want to upload multiple files at once
            multiple: false
        }).open()
        .on('select', function(e){
            // This will return the selected image from the Media Uploader, the result is an object
            var uploaded_image = image.state().get('selection').first();
            // We convert uploaded_image to a JSON object to make accessing it easier
            // Output to the console uploaded_image
            // console.log(uploaded_image);
            var image_url = uploaded_image.toJSON().url;
            // console.log(image_url);
            // Let's assign the url value to the input field
            jQuery('input[name="<?php echo $fname; ?>"]').val(image_url);
            jQuery('img.img_<?php echo $ai; ?>').attr('src', image_url);
        });
    });
});
</script>
  <?php
}

function wcplpro_delete_all_between($beginning, $end, $string) {
  $beginningPos = strpos($string, $beginning);
  $endPos = strpos($string, $end);
  if ($beginningPos === false || $endPos === false) {
    return $string;
  }

  $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);

  return str_replace($textToDelete, $beginning.$end, $string);
}


if (!function_exists('wcplpro_excerpt_max_length')) {
  function wcplpro_excerpt_max_length($charlength, $excerpt) {
    
    if ($excerpt == '') { return; }
    
    $out = '';

    if ( mb_strlen( $excerpt ) > absint($charlength) ) {
      $out = mb_substr( $excerpt, 0, $charlength ).'&hellip;';
    } else {
      $out = $excerpt;
    }
    
    return apply_filters('wcplpro_excerpt', $out);
  }
}


function wcplpro_filters_form($wcplpro_filter_cat, $wcplpro_filter_tag, $wcplpro_filter_search, $cat_transient, $tag_transient, $search_transient, $wcplpro_wcplid, $place){
  
  global $wp_query;
  
  $out = '';
  
  $display = ($place == get_option('wcplpro_filters_position') || get_option('wcplpro_filters_position') == 'both' ? 1 : 0);
  
  if ($wcplpro_filter_cat == 'yes' && $display == 1) {
    
    $cat_transient_arr = array();
    if (!empty($cat_transient)) {
      $cat_transient_arr = explode(',', $cat_transient);
    }
    
    // get woo categories
    $terms = get_categories( array(
      'taxonomy' => 'product_cat',
      'hide_empty' => true
    ) );
    
    
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
      $out .= '
      <div class="wcpl_span wcpl_span3">
        <select data-bindto="wcpl_product_cat" class="wcplpro_filter wcplproselect2" id="wcplpro_filter_cat" multiple="multiple" placeholder="'. __('Filter by category', 'wcplpro') .'">';
      foreach ( $terms as $term ) {
        $out .= '<option value="'. $term->term_id .'" '. (in_array($term->term_id, $cat_transient_arr) ? 'selected="selected"' : '') .'>'. $term->name .'</option>';
      }
      $out .= '
        </select>
        <input type="hidden" name="wcpl_product_cat" value="'. $cat_transient .'" />
      </div>
      ';
    }

    
  }
  
  
  if ($wcplpro_filter_tag == 'yes' && $display == 1) {
    
    $tag_transient_arr = array();
    if (!empty($tag_transient)) {
      $tag_transient_arr = explode(',', $tag_transient);
    }
    
    // get woo tags
    $tags = get_categories( array(
      'taxonomy' => 'product_tag',
      'hide_empty' => true
    ) );
    
    
    if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
      $out .= '
      <div class="wcpl_span wcpl_span3 last">
        <select data-bindto="wcpl_product_tag" class="wcplpro_filter wcplproselect2" id="wcplpro_filter_tag" multiple="multiple" placeholder="'. __('Filter by tag', 'wcplpro') .'">';
      foreach ( $tags as $tag ) {
        $out .= '<option value="'. $tag->term_id .'" '. (in_array($tag->term_id, $tag_transient_arr) ? 'selected="selected"' : '') .'>'. $tag->name .'</option>';
      }
      $out .= '
        </select>
        <input type="hidden" name="wcpl_product_tag" value="'. $tag_transient .'" />
      </div>  
      ';
    }
    
  }

  if ($wcplpro_filter_search == 'yes' && $display == 1) {
    
    $out .= '
    <div class="wcpl_span wcpl_span3 last">
      <input type="text" name="wcpl_search" class="wcpl_search" value="'. $search_transient .'" placeholder="'. __('Search', 'wcplpro') .'" />
    </div>  
    ';
    
  }
  
  if ($out != '') {
    
    $wcplpro_button_classes = apply_filters('wcplpro_single_button_classes', array(
        'single_add_to_cart_button',
        'button',
        'button_theme',
        'ajax',
        'add_to_cart',
        'avia-button',
        'fusion-button',
        'button-flat',
        'button-round'
      )
    );
    

    $out = '
      <div class="wcplpro_filters_wrap">
        <div class="wcpl_span wcpl_span6">
          <form class="wcplpro_filters_form"  action="'. get_the_permalink($wp_query->post->ID) .'" method="post" >
            <input type="hidden" name="wcpl" value="1" />
            <input type="hidden" name="wcplid" value="'. $wcplpro_wcplid .'" />
            <input type="hidden" name="wcpl_filters" value="1" />
            '. $out .'
            <div class="wcpl_span wcpl_span2">
              <input type="submit" class="wcplpro_submit" value="'. __('Filter', 'wcplpro') .'">
            </div>
            <div class="wcpl_span wcpl_span1 last">
              <a href="'. get_the_permalink($wp_query->post->ID) .'" class="'. implode(' ', $wcplpro_button_classes) .' alt wcplpro_reset" title="'. __('Reset Filters', 'wcplpro') .'" >'. __('Reset', 'wcplpro') .'</a>
            </div>

          </form>
        </div>
      </div>
    ';
    
  } else {
    
    if (get_option('wcplpro_pagination') == 'both' || get_option('wcplpro_pagination') == 'before' || get_option('wcplpro_pagination') == 'after') {
      $out = '
      <div class="wcplpro_filters_wrap">
        <div class="wcpl_span wcpl_span6">
        &nbsp;
        </div>
      </div>
      ';
    }
    
  }
  
  return $out;
  
}

/* ------------------------------------------------------------------*/
/* PAGINATION */
/* ------------------------------------------------------------------*/

function wcplpro_pagination($wcplpro_posts_per_page, $wcplpro_query, $wcplpro_paged = 1, $useruniq, $wcplpro_wcplid) {
  
  global $wp_query;
  
  $out = '';
  $total = $wcplpro_query->max_num_pages;
  
  // only bother with the rest if we have more than 1 page!
  if ( $total > 1 )  {
    
    $format = '';
    
    $out = 
    '<div class="wcplpro_pagination_wrap">
        <div class="wcpl_span wcpl_span6 wcpl_page_wrap">
          <form class="wcplpro_pagination_form" action="'. get_the_permalink($wp_query->post->ID) .'" method="post" >
            <input type="hidden" name="wcpl" value="1">
            <input type="hidden" name="wcplid" value="'. $wcplpro_wcplid .'">
            <input type="hidden" name="wcpl_page" class="wcpl_page" value="'. $wcplpro_paged .'">
          </form>
    '. paginate_links(array(
        'base'     => get_pagenum_link(1) . '%_%',
        'format'   => $format,
        'current'  => $wcplpro_paged,
        'total'    => $total,
        'mid_size' => 3,
        'type'     => 'list'
    )).'
        </div>
      </div>
    ';
  }
  
  return $out;
}



// get Woocommerce pages IDs
function wcplpro_get_woo_page_ids() {
  
  $pages = array(
    'woocommerce_shop_page_id'          => get_option( 'woocommerce_shop_page_id' ),
    'woocommerce_cart_page_id'          => get_option( 'woocommerce_cart_page_id' ), 
    'woocommerce_checkout_page_id'      => get_option( 'woocommerce_checkout_page_id' ),
    'woocommerce_pay_page_id'           => get_option( 'woocommerce_pay_page_id' ),
    'woocommerce_thanks_page_id'        => get_option( 'woocommerce_thanks_page_id' ),
    'woocommerce_myaccount_page_id'     => get_option( 'woocommerce_myaccount_page_id' ),
    'woocommerce_edit_address_page_id'  => get_option( 'woocommerce_edit_address_page_id' ),
    'woocommerce_view_order_page_id'    => get_option( 'woocommerce_view_order_page_id' ),
    'woocommerce_terms_page_id'         => get_option( 'woocommerce_terms_page_id' )
  );
  
  return $pages;
  
}