<?php
// Remove each style one by one
add_filter( 'woocommerce_enqueue_styles', 'jk_dequeue_styles' );
function jk_dequeue_styles( $enqueue_styles ) {
	unset( $enqueue_styles['woocommerce-smallscreen'] );    // Remove the smallscreen optimisation
	return $enqueue_styles;
}

// custom hook content product
add_action( 'woocommerce_shop_description', 'woocommerce_content_description', 20 );
if ( ! function_exists( 'woocommerce_content_description' ) ) {
	function woocommerce_content_description() {
		global $post;
		if ( ! $post->post_excerpt ) {
			return;
		}
		?>
		<div class="description">
			<?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ) ?>
		</div>
		<?php
	}
}

// remove woocommerce_breadcrumb
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );

//product list/grid
add_action( 'woocommerce_before_shop_loop', 'woocommerce_product_filter', 15 );
if ( ! function_exists( 'woocommerce_product_filter' ) ) {
	function woocommerce_product_filter() {
		echo '
		<div class="product-filter">
		 <div class="grid-list-count">
					<a href="javascript:;" class="list switchToGrid"><i class="fa fa-th-large"></i></a>
					<a href="javascript:;" class="grid switchToList"><i class="fa fa-th-list"></i></a></div>
		</div>';
	}
}

add_filter( 'loop_shop_per_page', 'thim_loop_shop_per_page' );
function thim_loop_shop_per_page() {
	global $theme_options_data;
	parse_str( $_SERVER['QUERY_STRING'], $params );
	if ( isset( $theme_options_data['thim_woo_product_per_page'] ) && $theme_options_data['thim_woo_product_per_page'] ) {
		$per_page = $theme_options_data['thim_woo_product_per_page'];
	} else {
		$per_page = 12;
	}
	$pc = ! empty( $params['product_count'] ) ? $params['product_count'] : $per_page;

	return $pc;
}

// add button compare before button wishlist in single product
global $yith_woocompare;
if ( isset( $yith_woocompare ) ) {
	remove_action( 'woocommerce_single_product_summary', array( $yith_woocompare->obj, 'add_compare_link' ), 35 );
	add_action( 'woocommerce_single_product_summary', array( $yith_woocompare->obj, 'add_compare_link' ), 30 );
}
// single product
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 25 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_show_product_thumbnails', 26 );

/*****************quick view*****************/
//remove_action( 'woocommerce_single_product_summary_quick', 'woocommerce_show_product_sale_flash', 10 );
//add_action( 'woocommerce_single_product_summary_quick', 'woocommerce_show_product_sale_flash', 1 );
add_action( 'woocommerce_single_product_summary_quick', 'woocommerce_template_single_title', 5 );
add_action( 'woocommerce_single_product_summary_quick', 'woocommerce_template_single_price', 10 );
add_action( 'woocommerce_single_product_summary_quick', 'woocommerce_template_single_rating', 15 );
add_action( 'woocommerce_single_product_summary_quick', 'woocommerce_template_loop_add_to_cart_quick_view', 20 );
add_action( 'woocommerce_single_product_summary_quick', 'woocommerce_template_single_excerpt', 30 );

//remove_action( 'woocommerce_single_product_summary_quick', 'woocommerce_template_single_meta', 40 );
add_action( 'woocommerce_single_product_summary_quick', 'woocommerce_template_single_meta', 7 );

add_action( 'woocommerce_single_product_summary_quick', 'woocommerce_template_single_sharing', 50 );

//overwrite content product.
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
add_action( 'woocommerce_after_shop_loop_item_title_rating', 'woocommerce_template_loop_rating', 5 );

remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

if ( ! function_exists( 'woocommerce_template_loop_add_to_cart_quick_view' ) ) {
	function woocommerce_template_loop_add_to_cart_quick_view() {
		global $product;
		do_action( 'woocommerce_' . $product->product_type . '_add_to_cart' );
	}
}

/* PRODUCT QUICK VIEW */
add_action( 'wp_head', 'lazy_ajax', 0, 0 );
function lazy_ajax() {
	?>
	<script type="text/javascript">
		/* <![CDATA[ */
		var ajaxurl = "<?php echo esc_js(admin_url('admin-ajax.php')); ?>";
		/* ]]> */
	</script>
	<?php
}

add_action( 'wp_ajax_jck_quickview', 'jck_quickview' );
add_action( 'wp_ajax_nopriv_jck_quickview', 'jck_quickview' );
/** The Quickview Ajax Output **/
function jck_quickview() {
	global $post, $product;
	$prod_id = $_POST["product"];
	$post    = get_post( $prod_id );
	$product = wc_get_product( $prod_id );
	// Get category permalink
	ob_start();
	?>
	<?php wc_get_template( 'content-single-product-lightbox.php' ); ?>
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	echo ent2ncr( $output );
	die();
}

/* End PRODUCT QUICK VIEW */

/* custom WC_Widget_Cart */
function thim_get_current_cart_info() {
	global $woocommerce;
	$items = count( $woocommerce->cart->get_cart() );

	return array(
		$items,
		get_woocommerce_currency_symbol()
	);
}

add_filter( 'add_to_cart_fragments', 'thim_add_to_cart_success_ajax' );
function thim_add_to_cart_success_ajax( $count_cat_product ) {
	list( $cart_items ) = thim_get_current_cart_info();
	if ( $cart_items < 0 ) {
		$cart_items = '0';
	} else {
		$cart_items = $cart_items;
	}
	$count_cat_product['#header-mini-cart .cart-items-number .items-number'] = '<span class="items-number">' . $cart_items . '</span>';

	return $count_cat_product;
}

// Override WooCommerce Widgets
add_action( 'widgets_init', 'override_woocommerce_widgets', 15 );
function override_woocommerce_widgets() {
	if ( class_exists( 'WC_Widget_Cart' ) ) {
		unregister_widget( 'WC_Widget_Cart' );
		include_once( 'widgets/class-wc-widget-cart.php' );
		register_widget( 'Custom_WC_Widget_Cart' );
	}
}


/* Share Product */
add_action( 'woocommerce_share', 'wooshare' );

function wooshare() {
	global $theme_options_data;
	$html = '';
	if ( $theme_options_data['thim_woo_sharing_facebook'] == 1 ||
	     $theme_options_data['thim_woo_sharing_twitter'] == 1 ||
	     $theme_options_data['thim_woo_sharing_pinterest'] == 1 ||
	     $theme_options_data['thim_woo_sharing_google'] == 1
	) {
		$html .= '<div class="woo-share">';
		$html .= '<span><i class="fa fa-share"></i><br/>share item</span>';
		$html .= '<ul class="share_show">';
		if ( $theme_options_data['thim_woo_sharing_facebook'] == 1 ) {
			$html .= '<li><a target="_blank" class="facebook" href="https://www.facebook.com/sharer.php?s=100&amp;p[title]=' . get_the_title() . '&amp;p[url]=' . urlencode( get_permalink() ) . '&amp;p[images][0]=' . urlencode( wp_get_attachment_url( get_post_thumbnail_id() ) ) . '" title="' . __( 'Facebook', 'thim' ) . '"><i class="fa fa-facebook"></i></a></li>';
		}
		if ( $theme_options_data['thim_woo_sharing_twitter'] == 1 ) {
			$html .= '<li><a target="_blank" class="twitter" href="https://twitter.com/share?url=' . urlencode( get_permalink() ) . '&amp;text=' . esc_attr( get_the_title() ) . '" title="' . __( 'Twitter', 'thim' ) . '"><i class="fa fa-twitter"></i></a></li>';
		}
		if ( $theme_options_data['thim_woo_sharing_pinterest'] == 1 ) {
			$html .= '<li><a target="_blank" class="pinterest" href="http://pinterest.com/pin/create/button/?url=' . urlencode( get_permalink() ) . '&amp;description=' . get_the_excerpt() . '&media=' . urlencode( wp_get_attachment_url( get_post_thumbnail_id() ) ) . '" onclick="window.open(this.href); return false;" title="' . __( 'Pinterest', 'thim' ) . '"><i class="fa fa-pinterest"></i></a></li>';
		}
		if ( $theme_options_data['thim_woo_sharing_google'] == 1 ) {
			$html .= '<li><a target="_blank" class="googleplus" href="https://plus.google.com/share?url=' . urlencode( get_permalink() ) . '&amp;title=' . esc_attr( get_the_title() ) . '" title="' . __( 'Google Plus', 'thim' ) . '" onclick=\'javascript:window.open(this.href, "", "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600");return false;\'><i class="fa fa-google"></i></a></li>';
		}
		$html .= '</ul>';
		$html .= '</div>';
	}
	echo ent2ncr( $html );

}

// Change the breadcrumb separator
add_filter( 'woocommerce_breadcrumb_defaults', 'thim_change_breadcrumb_delimiter' );
function thim_change_breadcrumb_delimiter( $defaults ) {
	if ( is_singular( 'product' ) ) {
		$defaults['delimiter'] = '';

		return $defaults;
	} else {
		$defaults['delimiter'] = '';

		return $defaults;
	}
}

// New Product
function thim_woo_add_custom_general_fields() {
	echo '<div class="options_group" id="product_custom_affiliate">';
	woocommerce_wp_checkbox(
		array(
			'id'       => 'thim_product_new',
			'label'    => __( 'Product New', 'thim' ),
			'desc_tip' => 'true',
		)
	);
	woocommerce_wp_checkbox(
		array(
			'id'       => 'thim_product_hot',
			'label'    => __( 'Product Hot', 'thim' ),
			'desc_tip' => 'true',
		)
	);
	echo '</div>';
}

function thim_woo_add_custom_general_fields_save( $post_id ) {
	$thim_product_new = isset( $_POST['thim_product_new'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, 'thim_product_new', $thim_product_new );
	// Checkbox
	$thim_product_hot = isset( $_POST['thim_product_hot'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, 'thim_product_hot', $thim_product_hot );
}

// Display Fields
add_action( 'woocommerce_product_options_general_product_data', 'thim_woo_add_custom_general_fields' );

// Save Fields
add_action( 'woocommerce_process_product_meta', 'thim_woo_add_custom_general_fields_save' );

function woo_add_style_yith_compare() {
	$css_file = get_template_directory_uri() . '/css/yith_compare.css';
	echo '<link rel="stylesheet" type="text/css" media="all" href="' . esc_url( $css_file ) . '" />';
}

if ( isset( $_GET['action'], $_GET['iframe'] ) && $_GET['action'] == 'yith-woocompare-view-table' && $_GET['iframe'] == "true" ) {
	add_action( 'wp_head', 'woo_add_style_yith_compare' );
}

if ( ! function_exists( 'get_product_search_form' ) ) {

	/**
	 * Display product search form.
	 *
	 * Will first attempt to locate the product-searchform.php file in either the child or
	 * the parent, then load it. If it doesn't exist, then the default search form
	 * will be displayed.
	 *
	 * The default searchform uses html5.
	 *
	 * @subpackage    Forms
	 *
	 * @param bool $echo (default: true)
	 *
	 * @return string
	 */
	function get_product_search_form( $echo = true ) {
		ob_start();

		do_action( 'pre_get_product_search_form' );

		wc_get_template( 'loop/product-searchform.php' );

		$form = apply_filters( 'get_product_search_form', ob_get_clean() );

		if ( $echo ) {
			echo ent2ncr( $form );
		} else {
			return $form;
		}
	}
}

/************List Category Product********************/
if ( ! function_exists( 'thim_list_category_product' ) ) :
	function thim_list_category_product() {
		$term            = get_queried_object();
		$parent_id       = empty( $term->term_id ) ? 0 : $term->term_id;
		$categories      = get_terms( 'product_cat', array( 'hide_empty' => 0, 'parent' => $parent_id ) );
		$show_categories = false;

		if ( is_shop() && ( get_option( 'woocommerce_shop_page_display' ) == '' ) ) {
			$show_categories = false;
		}
		if ( is_shop() && ( get_option( 'woocommerce_shop_page_display' ) == 'products' ) ) {
			$show_categories = false;
		}
		if ( is_shop() && ( get_option( 'woocommerce_shop_page_display' ) == 'subcategories' ) ) {
			$show_categories = false;
		}
		if ( is_shop() && ( get_option( 'woocommerce_shop_page_display' ) == 'both' ) ) {
			$show_categories = true;
		}

		if ( is_product_category() && ( get_option( 'woocommerce_category_archive_display' ) == '' ) ) {
			$show_categories = false;
		}
		if ( is_product_category() && ( get_option( 'woocommerce_category_archive_display' ) == 'products' ) ) {
			$show_categories = false;
		}
		if ( is_product_category() && ( get_option( 'woocommerce_category_archive_display' ) == 'subcategories' ) ) {
			$show_categories = false;
		}
		if ( is_product_category() && ( get_option( 'woocommerce_category_archive_display' ) == 'both' ) ) {
			$show_categories = true;
		}

		if ( is_product_category() && ( get_woocommerce_term_meta( $parent_id, 'display_type', true ) == 'products' ) ) {
			$show_categories = false;
		}
		if ( is_product_category() && ( get_woocommerce_term_meta( $parent_id, 'display_type', true ) == 'subcategories' ) ) {
			$show_categories = false;
		}
		if ( is_product_category() && ( get_woocommerce_term_meta( $parent_id, 'display_type', true ) == 'both' ) ) {
			$show_categories = true;
		}

		if ( isset( $_GET["s"] ) && $_GET["s"] != '' ) {
			$show_categories = false;
		}
		if ( $show_categories == true ) :
			if ( $categories ) : ?>
				<ul class="list-category">
					<?php foreach ( $categories as $category ) :
						?>
						<li class="category_item">
							<a href="<?php echo get_term_link( $category->slug, 'product_cat' ); ?>">
								<?php echo esc_html( $category->name ) . ' (' . $category->count . ')'; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul><!-- .list_shop_categories-->
			<?php endif;
		endif;
	}
endif;
/*********** end List category product****************/

// Change "Default Sorting" to "Our sorting" on shop page and in WC Product Settings
function thim_change_default_sorting_name( $catalog_orderby ) {
	$catalog_orderby = str_replace( "Sort by", "", $catalog_orderby );

	return $catalog_orderby;
}

add_filter( 'woocommerce_catalog_orderby', 'thim_change_default_sorting_name' );
add_filter( 'woocommerce_default_catalog_orderby_options', 'thim_change_default_sorting_name' );


if ( ! class_exists( 'wc_dropdown_category_walker' ) ) {
	class wc_dropdown_category_walker extends Walker_Nav_Menu {
		/**
		 * What the class handles.
		 *
		 * @see   Walker::$tree_type
		 * @since 2.1.0
		 * @var string
		 */
		var $tree_type = 'product_cat';

		var $db_fields = array( 'parent' => 'parent', 'id' => 'term_id', 'slug' => 'slug' );

		/**
		 * Starts the list before the elements are added.
		 *
		 * @see   Walker::start_lvl()
		 *
		 * @since 2.1.0
		 *
		 * @param string $output Passed by reference. Used to append additional content.
		 * @param int    $depth  Depth of category. Used for tab indentation.
		 * @param array  $args   An array of arguments. Will only append content if style argument value is 'list'.
		 *
		 * @see   wp_list_categories()
		 */
		function start_lvl( &$output, $depth = 0, $args = array() ) {

			$indent = str_repeat( "\t", $depth );
			$output .= "$indent<ul class='children'>\n";
		}

		/**
		 * Ends the list of after the elements are added.
		 *
		 * @see   Walker::end_lvl()
		 *
		 * @since 2.1.0
		 *
		 * @param string $output Passed by reference. Used to append additional content.
		 * @param int    $depth  Depth of category. Used for tab indentation.
		 * @param array  $args   An array of arguments. Will only append content if style argument value is 'list'.
		 *
		 * @wsee  wp_list_categories()
		 */
		function end_lvl( &$output, $depth = 0, $args = array() ) {
			$indent = str_repeat( "\t", $depth );
			$output .= "$indent</ul>\n";
		}

		/**
		 * Start the element output.
		 *
		 * @see   Walker::start_el()
		 *
		 * @since 2.1.0
		 *
		 * @param string $output   Passed by reference. Used to append additional content.
		 * @param object $category Category data object.
		 * @param int    $depth    Depth of category in reference to parents. Default 0.
		 * @param array  $args     An array of arguments. @see wp_list_categories()
		 * @param int    $id       ID of the current category.
		 */
		function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
			extract( $args );
			$cat_name = esc_attr( $category->name );

			/** This filter is documented in wp-includes/category-template.php */
			$cat_name = apply_filters( 'list_cats', $cat_name, $category );
			$link     = '<a href="' . esc_url( get_term_link( $category ) ) . '" ';
			$link .= '>';
			$link .= $cat_name;
			$link .= '</a>';
			if ( ! empty( $show_count ) ) {
				$link .= ' (' . number_format_i18n( $category->count ) . ')';
			}


			$output .= "\t<li";
			$class = 'cat-item cat-item-' . $category->term_id;
			if ( ! empty( $current_category ) ) {
				$_current_category = get_term( $current_category, $category->taxonomy );
				if ( $category->term_id == $current_category ) {
					$class .= ' current-cat';
				} elseif ( $category->term_id == $_current_category->parent ) {
					$class .= ' current-cat-parent';
				}
			}

			if ( $has_children && $hierarchical ) {
				$class .= ' cat-parent';
			}

			$output .= ' class="' . $class . '"';

			$output .= "><div>";


			if ( $category->parent == 0 ) {
				$output .= $link;
			} else {
				$output .= $link;
			}


			if ( $has_children && $hierarchical && ! isset( $disable_plus ) ) {
				$output .= '<span class="icon-plus"><i class="fa fa-plus"></i></span>';
			}
			$output .= "</div>";
		}

		/**
		 * Ends the element output, if needed.
		 *
		 * @see   Walker::end_el()
		 *
		 * @since 2.1.0
		 *
		 * @param string $output Passed by reference. Used to append additional content.
		 * @param object $page   Not used.
		 * @param int    $depth  Depth of category. Not used.
		 * @param array  $args   An array of arguments. Only uses 'list' for whether should append to output. @see wp_list_categories()
		 */
		function end_el( &$output, $page, $depth = 0, $args = array() ) {
			$output .= "</li>\n";
		}

		public function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args, &$output ) {
			if ( ! $element || 0 === $element->count ) {
				return;
			}
			parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
		}

	}
}