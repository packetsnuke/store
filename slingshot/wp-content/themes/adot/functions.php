<?php

/**
 * thim functions and definitions
 *
 * @package thim
 */
/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( !isset( $content_width ) ) {
	$content_width = 640; /* pixels */
}

if ( !function_exists( 'thim_setup' ) ) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function thim_setup() {

		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on thim, use a find and replace
		 * to change 'thim' to the name of your theme in all the template files
		 */
		load_theme_textdomain( 'thim', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
		 */
		add_theme_support( 'post-thumbnails' );
		// This theme uses wp_nav_menu() in one location.
		register_nav_menus( array(
			'primary' => __( 'Primary Menu', 'thim' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		/*
		 * Enable support for Post Formats.
		 * See http://codex.wordpress.org/Post_Formats
		 */
		add_theme_support( 'post-formats', array(
			'aside',
			'image',
			'video',
			'quote',
			'link',
			'gallery',
			'audio'
		) );

		add_theme_support( "title-tag" );
		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'thim_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );
		add_theme_support( 'woocommerce' );
	}

endif; // thim_setup
add_action( 'after_setup_theme', 'thim_setup' );

/**
 * Register widget area.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */

function thim_widgets_init() {
	global $theme_options_data;
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'thim' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	register_sidebar( array(
		'name'          => 'Top Drawer',
		'id'            => 'drawer_top',
		'description'   => __( 'Drawer Top', 'thim' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Offcanvas Sidebar', 'thim' ),
		'id'            => 'offcanvas_sidebar',
		'description'   => 'Offcanvas Sidebar',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	if ( isset( $theme_options_data['thim_header_style'] ) && $theme_options_data['thim_header_style'] == 'header_v3' ) {
		register_sidebar( array(
			'name'          => __( 'Header Right', 'thim' ),
			'id'            => 'header_right',
			'description'   => 'header right using width header layout 03',
			'before_widget' => '<li id="%1$s" class="widget %2$s">',
			'after_widget'  => '</li>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );
	}
	register_sidebar( array(
		'name'          => __( 'Sidebar Page', 'thim' ),
		'id'            => 'sidebar-page',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	register_sidebar( array(
		'name'          => __( 'Menu Right', 'thim' ),
		'id'            => 'menu_right',
		'description'   => '',
		'before_widget' => '<li id="%1$s" class="widget %2$s" >',
		'after_widget'  => '</li>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	register_sidebar( array(
		'name'          => __( 'Footer', 'thim' ),
		'id'            => 'footer',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	register_sidebar( array(
		'name'          => __( 'Copyright', 'thim' ),
		'id'            => 'copyright',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	register_sidebar( array(
		'name'          => __( 'Sidebar Shop', 'thim' ),
		'id'            => 'sidebar-shop',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}

add_action( 'widgets_init', 'thim_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function thim_scripts() {
	global $current_blog;
	global $theme_options_data;
	wp_enqueue_style( 'thim-fonts', TP_THEME_URI . 'fonts/fonts.css', array() );
	wp_enqueue_style( 'thim-bootstrap', TP_THEME_URI . 'bootstrap.css', array() );

	// include style css
	if ( is_multisite() ) {
		if ( file_exists( TP_THEME_DIR . 'style-' . $current_blog->blog_id . '.css' ) ) {
			wp_enqueue_style( 'thim-style', TP_THEME_URI . 'style-' . $current_blog->blog_id . '.css', array( 'thim-bootstrap' ) );
		} else {
			wp_enqueue_style( 'thim-style', get_stylesheet_uri(), array( 'thim-bootstrap' ) );
		}
	} else {
		wp_enqueue_style( 'thim-style', TP_THEME_URI . 'style.css', array( 'thim-bootstrap' ) );
	}

	if ( isset( $theme_options_data['thim_rtl_support'] ) && $theme_options_data['thim_rtl_support'] == '1' ) {
		wp_enqueue_style( 'thim-rtl', TP_THEME_URI . '/rtl.css', array() );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

//	wp_deregister_script( 'thim-main-min' );
//	wp_register_script( 'thim-main-min', TP_THEME_URI . '/js/main.min.js', array( 'jquery' ), '', true );
//	wp_enqueue_script( 'thim-main-min' );

	if ( thim_plugin_active( 'siteorigin-panels/siteorigin-panels.php' ) ) {
		wp_enqueue_script( 'thim-main', TP_THEME_URI . '/js/main.min.js', array(
			'jquery',
			'siteorigin-panels-front-styles'
		), '', true );
	} else {
		wp_enqueue_script( 'thim-main', TP_THEME_URI . '/js/main.min.js', array(
			'jquery'
		), '', true );
	}

	if ( class_exists( 'THIM_Portfolio' ) ) {
		wp_deregister_script( 'jquery-portfolio' );
		wp_enqueue_script( 'thim-portfolio-new', JS_URL . 'portfolio.js', array( 'siteorigin-panels-front-styles' ), false, false );
		wp_enqueue_script( 'thim-portfolio-new' );
	}

	// js products
	wp_deregister_script( 'thim-isotope' );
	wp_register_script( 'thim-isotope', TP_THEME_URI . '/js/isotope.pkgd.min.js', array( 'jquery' ), '', true );

	wp_deregister_script( 'thim-retina' );
	wp_register_script( 'thim-retina', TP_THEME_URI . '/js/jquery.retina.min.js', array( 'jquery' ), '', false );

	wp_deregister_script( 'thim-product' );
	wp_register_script( 'thim-product', TP_THEME_URI . '/js/product.js', array( 'jquery' ), '', true );
	wp_enqueue_script( 'thim-product' );

	if ( isset( $theme_options_data['thim_preload'] ) && $theme_options_data['thim_preload'] == '1' ) {
		wp_enqueue_script( 'thim-TweenMax', TP_THEME_URI . '/js/TweenMax.min.js', array( 'jquery' ), '', true );
	}

	wp_deregister_script( 'thim-custom-script' );
	wp_register_script( 'thim-custom-script', TP_THEME_URI . '/js/custom-script.js', array( 'jquery' ), '', true );
	wp_enqueue_script( 'thim-custom-script' );

	wp_enqueue_script( 'wc-add-to-cart-variation' );

}

add_action( 'wp_enqueue_scripts', 'thim_scripts' );

function admin_thim_scripts() {
	wp_enqueue_script( 'thim-admin-custom-script', TP_THEME_URI . '/js/admin-custom-script.js', array( 'jquery' ), '', true );
}

add_action( 'admin_init', 'admin_thim_scripts' );
/**
 * load framework
 */
require_once get_template_directory() . '/framework/tp-framework.php';

// require
require_once TP_THEME_DIR . 'inc/custom-functions.php';

/**
 * Implement the Custom Header feature.
 */
//require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

if ( class_exists( 'WooCommerce' ) ) {
	// Woocomerce
	WC_Post_types::register_taxonomies();
	require get_template_directory() . '/woocommerce/woocommerce.php';
}
/**
 * Customizer additions.
 */
//require get_template_directory() . '/inc/customizer.php';
require TP_THEME_DIR . 'inc/admin/customize-options.php';

require TP_THEME_DIR . 'inc/widgets/widgets.php';

// tax meta
require TP_THEME_DIR . 'inc/tax-meta.php';

// dislay setting layout
require TP_THEME_DIR . 'inc/wrapper-before-after.php';

if ( is_admin() ) {
	require TP_THEME_DIR . 'inc/admin/plugins-require.php';
}

/******************************************************************************/
/****************************** Ajax url **************************************/
/******************************************************************************/

add_action( 'wp_head', 'thim_wishlist_ajaxurl' );
function thim_wishlist_ajaxurl() {
	?>
	<script type="text/javascript">
		var thim_wishlist_ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
	</script>
	<?php
}

/******************************************************************************/
/************************ Ajax calls ******************************************/
/******************************************************************************/

function thim_refresh_dynamic_contents() {
	$data = array(
		'wishlist_count_products' => class_exists( 'YITH_WCWL' ) ? yith_wcwl_count_products() : 0,
	);
	wp_send_json( $data );
}

add_action( 'wp_ajax_thim_refresh_dynamic_contents', 'thim_refresh_dynamic_contents' );
add_action( 'wp_ajax_nopriv_thim_refresh_dynamic_contents', 'thim_refresh_dynamic_contents' );


//pannel Widget Group
function thim_widget_group( $tabs ) {
	$tabs[] = array(
		'title'  => __( 'Thim Widget', 'thim' ),
		'filter' => array(
			'groups' => array( 'thim_widget_group' )
		)
	);

	return $tabs;
}

add_filter( 'siteorigin_panels_widget_dialog_tabs', 'thim_widget_group', 19 );

// fix before load
function thim_row_style_attributes( $attributes, $args ) {
	//var_dump($args['row_stretch']);
	if ( !empty( $args['row_stretch'] ) && $args['row_stretch'] == 'full-stretched' ) {
		array_push( $attributes['class'], 'thim-fix-stretched' );
	}

	return $attributes;
}

add_filter( 'siteorigin_panels_row_style_attributes', 'thim_row_style_attributes', 10, 2 );
add_filter( 'thim_mtb_setting_after_created', 'thim_mtb_setting_after_created', 10, 2 );
function thim_mtb_setting_after_created( $mtb_setting ) {
	$mtb_setting->removeOption( array( 11 ) );

	return $mtb_setting;
}