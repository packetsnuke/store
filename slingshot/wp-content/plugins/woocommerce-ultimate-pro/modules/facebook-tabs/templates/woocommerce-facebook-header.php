<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <title>Facebook Products</title>
    <?php
    // wp_head
    /*if(class_exists('WC_Frontend_Scripts',false)){
        // 2.1 - ??
    }else{
        $wp_scripts->query = array();
        $wp_styles->query = array();
        $GLOBALS['woocommerce']->frontend_scripts();
        $GLOBALS['woocommerce']->check_jquery();
    }*/
    define('DONOTMINIFY', true);
    add_filter('loop_shop_columns', 'woo_fb_loop_columns', 99999);
    if (!function_exists('woo_fb_loop_columns')) {
	    function woo_fb_loop_columns() {
		    return 4; // 3 products per row
	    }
    }

    do_action('wp_enqueue_scripts');
    do_action('woocommerce_enqueue_styles');
    //wp_enqueue_script( 'wc-add-to-cart-variation' );
    $tab_id = 1;
    $enabled_scripts = @unserialize(get_option('dtbaker_fbtab'.$tab_id.'_enabled_scripts',''));
    $enabled_styles = @unserialize(get_option('dtbaker_fbtab'.$tab_id.'_enabled_styles',''));
    if(!is_array($enabled_scripts))$enabled_scripts=array();
    if(!is_array($enabled_styles))$enabled_styles=array();

    echo '<!--- SCRIPTS/STYLES QUEUED'."\r\n";
    $wp_scripts = wp_scripts();
    foreach ( $wp_scripts->queue as $key => $script ) {
        echo "\r\nSCRIPT: ".$script;
	    $enabled_scripts[$script] = isset($enabled_scripts[$script]) ? $enabled_scripts[$script] : ((strpos($script,'woocommerce') === false && strpos($script,'wc') === false && stripos($script,'prettyphoto') === false) ? 0 : 1);
        if(!$enabled_scripts[$script]){
            wp_dequeue_script($script);
            echo " ---- Removing...";
        }
    }
    $wp_styles = wp_styles();
    foreach ( $wp_styles->queue as $key =>$script ) {
        echo "\r\nSTYLE: ".$script;
	    $enabled_styles[$script] = isset($enabled_styles[$script]) ? $enabled_styles[$script] : ((stripos($script,'woocommerce') === false && stripos($script,'wc') === false && stripos($script,'prettyphoto') === false) ? 0 : 1);
        if(!$enabled_styles[$script]){
            wp_dequeue_style($script);
            echo " ---- Removing...";
        }
    }
    echo "\r\n--->";
    update_option('dtbaker_fbtab'.$tab_id.'_enabled_scripts',serialize($enabled_scripts));
    update_option('dtbaker_fbtab'.$tab_id.'_enabled_styles',serialize($enabled_styles));
    wp_print_scripts(); // should only print woocommerce scripts
    wp_print_styles(); // should only print woocommerce styles
    //wp_head();
    ?>


    <!-- core plugin stylesheet -->
    <link rel="stylesheet" type="text/css" media="all" href="<?php echo plugins_url( 'css/style.css', dirname(__FILE__) ); ?>" />
    <!-- load the 'woocommerce-facebook.css' custom stylesheet if it exists -->
    <?php if(file_exists(get_stylesheet_directory().'/woocommerce-facebook.css')){ ?>
    <link rel="stylesheet" type="text/css" media="all" href="<?php echo get_stylesheet_directory_uri(). '/woocommerce-facebook.css'; ?>" />
    <?php }else if(file_exists(get_template_directory().'/woocommerce-facebook.css')){ ?>
    <link rel="stylesheet" type="text/css" media="all" href="<?php echo get_template_directory_uri(). '/woocommerce-facebook.css'; ?>" />
    <?php } ?>

    <script type="text/javascript">
        window.fbAsyncInit = function() {
            FB.Canvas.setSize();
        };
        // Do things that will sometimes call sizeChangeCallback()
        function sizeChangeCallback() {
            FB.Canvas.setSize();
        }
    </script>

</head>
<body id="fbtab">
<div id="fbtab_wrap" class="woocommerce">
    <?php
    if(function_exists('wc_print_notices')){
        wc_print_notices();
    }else{
        woocommerce_show_messages();
    }
    ?>
