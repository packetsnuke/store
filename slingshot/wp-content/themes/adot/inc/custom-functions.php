<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

# fixed problem of WOOF - WooCommerce Products Filter
add_filter('parse_query', 'adot_parse_query_woof', 10000);
if(!function_exists('adot_parse_query_woof')){
	function adot_parse_query_woof( $wp_query ){
		if ($wp_query->is_main_query()){
			if (isset($_GET['s']))
			{
				$_GET['woof_title'] = $_GET['s'];
			}
			if(isset($_GET['swoof']))
			{
				if(isset($wp_query->query['product_cat'])){
					$wp_query->is_tax = true;
				}
			}
		}
		return $wp_query;
	}
}


function get_html_template( $style_layout ) {
	$html_return = '';
	$html_return = get_template_part( 'inc/blog-templates/content', $style_layout );

	return $html_return;
}

add_action( 'thim_logo', 'thim_logo' );
// logo
if ( !function_exists( 'thim_logo' ) ) :
	function thim_logo() {
		global $theme_options_data;
		if ( isset( $theme_options_data['thim_logo'] ) && $theme_options_data['thim_logo'] <> '' ) {
			$thim_logo     = $theme_options_data['thim_logo'];
			$thim_logo_src = $thim_logo; // For the default value
			if ( is_numeric( $thim_logo ) ) {
				$logo_attachment = wp_get_attachment_image_src( $thim_logo, 'full' );
				$thim_logo_src   = $logo_attachment[0];
			}
			$thim_logo_size = @getimagesize( $thim_logo_src );
			$logo_size      = $thim_logo_size[3];
			$site_title     = esc_attr( get_bloginfo( 'name', 'display' ) );
			echo '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . ' - ' . esc_attr( get_bloginfo( 'description' ) ) . '" rel="home" class="no-sticky-logo"><img src="' . $thim_logo_src . '" alt="' . $site_title . '" ' . $logo_size . ' /></a>';
		} else {
			echo '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . ' - ' . esc_attr( get_bloginfo( 'description' ) ) . '" rel="home" class="no-sticky-logo">' . esc_attr( get_bloginfo( 'name' ) ) . '</a>';
		}
	}
endif; // logo

add_action( 'thim_sticky_logo', 'thim_sticky_logo' );
// get sticky logo
if ( !function_exists( 'thim_sticky_logo' ) ) :
	function thim_sticky_logo() {
		global $theme_options_data;
		if ( isset( $theme_options_data['thim_sticky_logo'] ) && $theme_options_data['thim_sticky_logo'] <> '' ) {
			$thim_logo_stick_logo     = $theme_options_data['thim_sticky_logo'];
			$thim_logo_stick_logo_src = $thim_logo_stick_logo; // For the default value
			if ( is_numeric( $thim_logo_stick_logo ) ) {
				$logo_attachment          = wp_get_attachment_image_src( $thim_logo_stick_logo, 'full' );
				$thim_logo_stick_logo_src = $logo_attachment[0];
			}
			$thim_logo_size = @getimagesize( $thim_logo_stick_logo_src );
			$logo_size      = $thim_logo_size[3];
			$site_title     = esc_attr( get_bloginfo( 'name', 'display' ) );
			echo '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . ' - ' . esc_attr( get_bloginfo( 'description' ) ) . '" rel="home" class="sticky-logo">
					<img src="' . $thim_logo_stick_logo_src . '" alt="' . $site_title . '" ' . $logo_size . ' /></a>';
		} elseif ( isset( $theme_options_data['thim_logo'] ) && $theme_options_data['thim_logo'] <> '' ) {
			$thim_logo     = $theme_options_data['thim_logo'];
			$thim_logo_src = $thim_logo; // For the default value
			if ( is_numeric( $thim_logo ) ) {
				$logo_attachment = wp_get_attachment_image_src( $thim_logo, 'full' );
				$thim_logo_src   = $logo_attachment[0];
			}
			$thim_logo_size = @getimagesize( $thim_logo_src );
			$logo_size      = $thim_logo_size[3];
			$site_title     = esc_attr( get_bloginfo( 'name', 'display' ) );
			echo '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . ' - ' . esc_attr( get_bloginfo( 'description' ) ) . '" rel="home" class="sticky-logo">
				<img src="' . $thim_logo_src . '" alt="' . $site_title . '" ' . $logo_size . ' /></a>';
		}
		if ( $theme_options_data['thim_sticky_logo'] == '' && $theme_options_data['thim_logo'] == '' ) {
			echo '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . ' - ' . esc_attr( get_bloginfo( 'description' ) ) . '" rel="home" class="sticky-logo">
			' . esc_attr( get_bloginfo( 'name' ) ) . '</a>';;
		}
	}
endif; // thim_sticky_logo


function thim_hex2rgb( $hex ) {
	$hex = str_replace( "#", "", $hex );
	if ( strlen( $hex ) == 3 ) {
		$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
		$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
		$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
	} else {
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
	}
	$rgb = array( $r, $g, $b );

	return $rgb; // returns an array with the rgb values
}

function thim_getExtraClass( $el_class ) {
	$output = '';
	if ( $el_class != '' ) {
		$output = " " . str_replace( ".", "", $el_class );
	}

	return $output;
}

function thim_getCSSAnimation( $css_animation ) {
	$output = '';
	if ( $css_animation != '' ) {
		$output = ' wpb_animate_when_almost_visible wpb_' . $css_animation;
	}

	return $output;
}

function excerpt( $limit ) {
	$content = get_the_excerpt();
	$content = apply_filters( 'the_content', $content );
	$content = str_replace( ']]>', ']]&gt;', $content );
	$content = explode( ' ', $content, $limit );
	array_pop( $content );
	$content = implode( " ", $content );

	return $content;
}

// function convert type int variable to string variable
function convertIntToString( $id ) {
	$src = '';
	if ( is_numeric( $id ) ) {
		$imageAttachment = wp_get_attachment_image_src( $id );
		$src             = $imageAttachment[0];
	}

	return $src;
}

/****************Breadcrumbs********************* */
if ( !function_exists( 'thim_breadcrumbs' ) ) {
	function thim_breadcrumbs() {
		global $wp_query, $post;
		// Start the UL
		echo '<ul class="ulbreadcrumbs" itemprop="breadcrumb">';
		echo '<li><a href="' . esc_url( home_url() ) . '" class="home">' . __( "Home", 'thim' ) . '</a></li>';
		if ( is_category() ) {
			$catTitle = single_cat_title( "", false );
			$cat      = get_cat_ID( $catTitle );
			echo '<li>' . get_category_parents( $cat, true, "" ) . '</li>';
		} elseif ( is_archive() && !is_category() ) {
			if ( get_post_type() == "portfolio" ) {
				//	echo " / Portfolio";
				if ( is_tax( 'portfolio_category' ) ) {
					$current_term = $wp_query->get_queried_object();
					$ancestors    = array_reverse( get_ancestors( $current_term->term_id, 'portfolio_category' ) );
					foreach ( $ancestors as $ancestor ) {
						$ancestor = get_term( $ancestor, 'portfolio_category' );
						echo '<li><a href="' . esc_url( get_term_link( $ancestor ) ) . '">' . esc_html( $ancestor->name ) . '</a></li>';
					}
					echo '<li>' . esc_html( $current_term->name ) . '</li>';
				} else {
					echo '<li>' . _e( 'Portfolio', 'thim' ) . '</li>';
				}
			} else {
				echo '<li>' . __( 'Archives', 'thim' ) . '</li>';
			}
		} elseif ( is_search() ) {
			echo '<li>' . __( 'Search Result', 'thim' ) . '</li>';
		} elseif ( is_404() ) {
			echo '<li>' . __( '404 Not Found', 'thim' ) . '</li>';
		} elseif ( is_single( $post ) ) {
			if ( get_post_type() == 'post' ) {
				$category    = get_the_category();
				$category_id = get_cat_ID( $category[0]->cat_name );
				echo ' <li>' . get_category_parents( $category_id, true, " " ) . '</li>';
				echo the_title( '<li>', ' </li>', false );
			} else {
				echo ' <li>' . get_the_title() . '</li>';
			}
		} elseif ( is_page() ) {
			$post = $wp_query->get_queried_object();
			if ( $post->post_parent == 0 ) {
				echo "<li>" . the_title( '', '', false ) . "</li>";
			} else {
				$ancestors = array_reverse( get_post_ancestors( $post->ID ) );
				array_push( $ancestors, $post->ID );
				foreach ( $ancestors as $ancestor ) {
					if ( $ancestor != end( $ancestors ) ) {
						echo '<li><a href="' . esc_url( get_permalink( $ancestor ) ) . '">' . strip_tags( apply_filters( 'single_post_title', esc_attr( get_the_title( $ancestor ) ) ) ) . '</a></li>';
					} else {
						echo '<li>' . strip_tags( apply_filters( 'single_post_title', esc_attr( get_the_title( $ancestor ) ) ) ) . '</li>';
					}
				}
			}
		} elseif ( is_attachment() ) {
			$parent = get_post( $post->post_parent );
			if ( $parent->post_type == 'page' || $parent->post_type == 'post' ) {
				$cat = get_the_category( $parent->ID );
				$cat = $cat[0];
				echo get_category_parents( $cat, true, ' ' );
			}

			echo '<li><a href="' . esc_url( get_permalink( $parent ) ) . '">' . esc_attr( $parent->post_title ) . '</a></li>';
			echo get_the_title();
		} elseif ( is_home() ) {
			echo '<li> ' . __( 'Blog', 'thim' ) . ' </li>';
		}
		// End the UL
		echo "</ul>";
	}
}
/**********end Breadcrumbs****************/

/************ List Comment ***************/
if ( !function_exists( 'thim_comment' ) ) {
	function thim_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		//extract( $args, EXTR_SKIP );
		if ( 'div' == $args['style'] ) {
			$tag       = 'div';
			$add_below = 'comment';
		} else {
			$tag       = 'li';
			$add_below = 'div-comment';
		}
		?>
		<<?php echo ent2ncr( $tag ) ?> <?php comment_class( 'description_comment' ) ?> id="comment-<?php comment_ID() ?>">
		<?php
		if ( $args['avatar_size'] != 0 ) {
			echo get_avatar( $comment, $args['avatar_size'] );
		}
		?>
		<div
			class="author"><?php printf( __( '<span class="author-name">%s</span>', 'thim' ), get_comment_author_link() ) ?></div>
		<?php if ( $comment->comment_approved == '0' ) : ?>
			<em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'thim' ) ?></em>
		<?php endif; ?>
		<div class="comment-extra-info">
			<div class="date" itemprop="commentTime"><?php printf( get_comment_date(), get_comment_time() ) ?></div>
			<?php comment_reply_link( array_merge( $args, array(
				'add_below' => $add_below,
				'depth'     => $depth,
				'max_depth' => $args['max_depth']
			) ) ) ?>
			<?php edit_comment_link( __( 'Edit', 'thim' ), '', '' ); ?>
		</div>
		<div class="message">
			<?php comment_text() ?>
		</div>
		<div class="clear"></div>
	<?php
	}
}
/************end list comment************/

/************List category********************/
if ( !function_exists( 'thim_list_category' ) ) :
	function thim_list_category() {
		global $theme_options_data;
		$number = '5';
		if ( get_option( 'show_on_front' ) == 'page' ) {
			if ( isset( $theme_options_data['thim_front_page_number_cat'] ) ) {
				$number = $theme_options_data['thim_front_page_number_cat'];
			}
		} else {
			if ( isset( $theme_options_data['thim_archive_number_cat'] ) ) {
				$number = $theme_options_data['thim_archive_number_cat'];
			}
		}
		$args = array(
			'show_option_all'    => __( 'All', 'thim' ),
			'orderby'            => 'name',
			'order'              => 'count',
			'style'              => 'list',
			'show_count'         => 1,
			'hide_empty'         => 1,
			'use_desc_for_title' => 1,
			'child_of'           => 0,
			'feed'               => '',
			'feed_type'          => '',
			'feed_image'         => '',
			'exclude'            => '',
			'exclude_tree'       => '',
			'include'            => '',
			'hierarchical'       => 1,
			'title_li'           => '',
			'show_option_none'   => '',
			'number'             => $number,
			'echo'               => 1,
			'depth'              => 0,
			'current_category'   => 0,
			'pad_counts'         => 0,
			'taxonomy'           => 'category'
		);
		echo '<ul class="list-category"> ';
		wp_list_categories( $args );
		echo '</ul>';
	}
endif;
//
//class Walker_Thim_List_Category extends Walker_Category {
//	function start_lvl( &$output, $depth = 1, $args = array() ) {
//	}
//
//	function end_lvl( &$output, $depth = 0, $args = array() ) {
//	}
//
//	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
//		/** This filter is documented in wp-includes/category-template.php */
//		$cat_name = apply_filters(
//			'list_cats',
//			esc_attr( $category->name ),
//			$category
//		);
//
//		$link = '<a href="' . esc_url( get_term_link( $category ) ) . '" ';
//		if ( $args['use_desc_for_title'] && ! empty( $category->description ) ) {
//			$link .= 'title="' . esc_attr( strip_tags( apply_filters( 'category_description', $category->description, $category ) ) ) . '"';
//		}
//
//		$link .= '>';
//
//		$link .= $cat_name;
//		if ( ! empty( $args['show_count'] ) ) {
//			$link .= ' (' . number_format_i18n( $category->count ) . ')';
//		}
//		$link .= '</a>';
//		if ( 'list' == $args['style'] ) {
//			$output .= "\t<li";
//			$class = 'cat-item cat-item-' . $category->term_id;
//			if ( ! empty( $args['current_category'] ) ) {
//				$_current_category = get_term( $args['current_category'], $category->taxonomy );
//				if ( $category->term_id == $args['current_category'] ) {
//					$class .= ' current-cat';
//				} elseif ( $category->term_id == $_current_category->parent ) {
//					$class .= ' current-cat-parent';
//				}
//			}
//			$output .= ' class="' . $class . '"';
//			$output .= ">$link\n";
//		} else {
//			$output .= "\t$link<br />\n";
//		}
//	}
//
//}

/*********** end List category****************/

/* unregister Thim Portfolio Widget */
function remove_portfolio_widget() {
	unregister_widget( 'THIM_Widget_Portfolio' );
}

add_action( 'widgets_init', 'remove_portfolio_widget' );


/* Product btn Paging */
add_action( 'wp_ajax_button_paging', 'thim_ajax_button_paging' );
add_action( 'wp_ajax_nopriv_button_paging', 'thim_ajax_button_paging' );

function thim_ajax_button_paging() {
	if ( isset( $_POST['cat'] ) ) {
		$cat = $_POST['cat'];
	} else {
		return;
	}

	if ( isset( $_POST['order'] ) ) {
		$order = $_POST['order'];
	} else {
		return;
	}
	if ( isset( $_POST['orderby'] ) ) {
		$orderby = $_POST['orderby'];
	} else {
		return;
	}

	if ( isset( $_POST['hide_free'] ) ) {
		$hide_free = $_POST['hide_free'];
	} else {
		return;
	}

	if ( isset( $_POST['show_hidden'] ) ) {
		$show_hidden = $_POST['show_hidden'];
	} else {
		return;
	}

	if ( isset( $_POST['column'] ) ) {
		$column = $_POST['column'];
	} else {
		return;
	}

	if ( isset( $_POST['offset'] ) ) {
		$offset = $_POST['offset'];
	} else {
		$offset = 4;
	}

	if ( isset( $_POST['df_offset'] ) ) {
		$df_offset = $_POST['df_offset'];
	} else {
		$df_offset = 4;
	}
	$default_posts_per_page   = $df_offset;
	$query_args               = array(
		'posts_per_page' => $default_posts_per_page,
		'post_status'    => 'publish',
		'post_type'      => 'product',
		'no_found_rows'  => 1,
		'offset'         => $offset,
		'order'          => $order == 'asc' ? 'asc' : 'desc'
	);
	$query_args['meta_query'] = array();

	if ( empty( $show_hidden ) ) {
		$query_args['meta_query'][] = WC()->query->visibility_meta_query();
		$query_args['post_parent']  = 0;
	}

	if ( !empty( $hide_free ) ) {
		$query_args['meta_query'][] = array(
			'key'     => '_price',
			'value'   => 0,
			'compare' => '>',
			'type'    => 'DECIMAL',
		);
	}

	$query_args['meta_query'][] = WC()->query->stock_status_meta_query();
	$query_args['meta_query']   = array_filter( $query_args['meta_query'] );


	switch ( $orderby ) {
		case 'price' :
			$query_args['meta_key'] = '_price';
			$query_args['orderby']  = 'meta_value_num';
			break;
		case 'rand' :
			$query_args['orderby'] = 'date';
			break;
		case 'sales' :
			$query_args['meta_key'] = 'total_sales';
			$query_args['orderby']  = 'meta_value_num';
			break;
		default :
			$query_args['orderby'] = 'date';
	}


	if ( $cat == "all" ) {

	} elseif ( $cat == "featured" ) {
		$query_args['meta_query'][] = array(
			'key'   => '_featured',
			'value' => 'yes'
		);
	} elseif ( $cat == "onsale" ) {
		$product_ids_on_sale    = wc_get_product_ids_on_sale();
		$product_ids_on_sale[]  = 0;
		$query_args['post__in'] = $product_ids_on_sale;
	} elseif ( $cat == "bestsellers" ) {
		$query_args['meta_query'][] = array(
			'meta_key'      => 'total_sales',
			'orderby'       => 'meta_value_num',
			'no_found_rows' => 1,
		);
	} else {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $cat
			)
		);
	}
	$arr           = array();
	$arr['data']   = "";
	$arr['offset'] = $offset;

	if ( $cat == "all" ) {
		$post_count = wp_count_posts( 'product' )->publish;
	} elseif ( $cat == "featured" ) {
		$args             = array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => - 1, 'meta_query' => array(
			array(
				'key'   => '_featured',
				'value' => 'yes'
			)
		) );
		$post_count_query = new WP_Query( $args );
		$post_count       = $post_count_query->found_posts;
	} elseif ( $cat == "onsale" ) {
		$product_ids_on_sale   = wc_get_product_ids_on_sale();
		$product_ids_on_sale[] = 0;
		$args                  = array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => - 1, 'post__in' => $product_ids_on_sale );
		$post_count_query      = new WP_Query( $args );
		$post_count            = $post_count_query->found_posts;
	} elseif ( $cat == "bestsellers" ) {
		$args             = array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => - 1, 'meta_query' => array(
			array(
				'meta_key'      => 'total_sales',
				'orderby'       => 'meta_value_num',
				'no_found_rows' => 1,
			)
		) );
		$post_count_query = new WP_Query( $args );
		$post_count       = $post_count_query->found_posts;
	} else {
		$cate       = get_term( $cat, 'product_cat' );
		$post_count = $cate->count;
	}

	if ( $post_count <= ( $offset + $df_offset ) ) {
		$arr['next_post'] = false;
	} else {
		$arr['next_post'] = true;
	}

	ob_start();
	// The Loop
	$r = new WP_Query( $query_args );
	if ( $r->have_posts() ) {
		while ( $r->have_posts() ) {
			$r->the_post();
			wc_get_template( 'content-widget/content-product.php', array(
				'show_rating' => true,
				'column'      => $column
			) );
		}
	}
	$arr['data'] .= ob_get_contents();
	ob_end_clean();
	// Reset Query
	wp_reset_query();
	wp_send_json( $arr );
}

/* End Blog btn Paging */

add_action( 'wp_ajax_button_posts_paging', 'thim_ajax_button_posts_paging' );
add_action( 'wp_ajax_nopriv_button_posts_paging', 'thim_ajax_button_posts_paging' );

function thim_ajax_button_posts_paging() {
	if ( isset( $_POST['offset'] ) ) {
		$offset = $_POST['offset'];
	} else {
		$offset = 10;
	}
	if ( isset( $_POST['lastmonth'] ) ) {
		$lastmonth = $_POST['lastmonth'];
	} else {
		return;
	}
	if ( isset( $_POST['df_offset'] ) ) {
		$df_offset = $_POST['df_offset'];
	} else {
		$df_offset = 10;
	}

	$today = getdate();
	$query_args             = array(
		'posts_per_page' => $_POST['df_offset'],
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'offset'         => $_POST['offset'],
		'order'          => 'DESC',
	);
	$post_count = wp_count_posts( 'post' )->publish;
	if ( $post_count <= ( $offset + $df_offset ) ) {
		$arr['next_post'] = false;
	} else {
		$arr['next_post'] = true;
	}

	ob_start();
	global $post;
	$postslist = get_posts( $query_args );
	$months_years = array();
	foreach ( $postslist as $post ) : setup_postdata( $post );
		$day = mysql2date('d', $post->post_date);
		$month = mysql2date('F', $post->post_date);
		$year  = mysql2date('Y', $post->post_date);
		if($lastmonth == strtotime($month.'-'.$year)) { ?>
			<div class="provisional">
				<?php get_template_part( 'inc/blog-templates/content', 'timeline' ); ?>
			</div>
		<?php }
		else {
			if ( !in_array( strtotime( $month . '-' . $year ), $months_years ) ) {
				if ( strtotime( $today['mday'] . '-' . $today['month'] . '-' . $today['year'] ) == strtotime( $day . '-' . $month . '-' . $year ) ) { ?>
					</div><h3 class="month-year"><span>Today</span></h3>
					<div class="posts <?php echo strtotime( $month . '-' . $year ); ?>">
					<?php get_template_part( 'inc/blog-templates/content', 'timeline' );
				} else {
					array_push( $months_years, strtotime( $month . '-' . $year ) ); ?>
					</div><h3 class="month-year"><span><?php echo( $month . ' ' . $year ); ?></span></h3>
					<div class="posts <?php echo strtotime( $month . '-' . $year ); ?>">
					<?php get_template_part( 'inc/blog-templates/content', 'timeline' );
				}
			} else {
				get_template_part( 'inc/blog-templates/content', 'timeline' );
			}
		}
	endforeach; ?>
	</div>
	<?php wp_reset_postdata();
	$arr['data'] .= ob_get_contents();
	ob_end_clean();
	// Reset Query
	wp_reset_query();
	wp_send_json( $arr );
}

/* Portfolio Hook */
function filter_custom_thim_portfolio_options( $options ) {
	$prefix = 'thim_portfolio_option_';

	unset( $options[$prefix . 'item_style'] );
	$options[$prefix . 'item_effect'] = array(
		'name'    => __( 'Images Hover Effects', 'thim' ),
		'id'      => $prefix . 'item_effect',
		'type'    => 'select',
		'std'     => 'effects_classic',
		'options' => array(
			'style01' => 'Caption Hover Effects 01',
			'style02' => 'Caption Hover Effects 02',
			'style03' => 'Caption Hover Effects 03',
			'style04' => 'Caption Hover Effects 04',
			'style05' => 'Caption Hover Effects 05',
			'style06' => 'Caption Hover Effects 06',
			'style07' => 'Caption Hover Effects 07',
			'style08' => 'Caption Hover Effects 08',
		)
	);

	return $options;
}

add_filter( 'custom_thim_portfolio_options', 'filter_custom_thim_portfolio_options' );

/* Blog btn Paging */
add_action('wp_ajax_button_paging', 'aloxo_ajax_button_paging');
add_action('wp_ajax_nopriv_button_paging', 'aloxo_ajax_button_paging');

function aloxo_ajax_button_paging() {
	global $theme_options_data;
	if (isset($_POST['cat'])) {
		$cat = $_POST['cat'];
	} else {
		return;
	}
	$date_posts = array();
	if (isset($_POST['post_date'])) {
		$date_posts = json_decode(base64_decode($_POST['post_date']));
	} else {
		return;
	}

	if (isset($_POST['size'])) {
		$size = $_POST['size'];
	} else {
		return;
	}

	$default_posts_per_page = get_option('posts_per_page');
	if (isset($_POST['offset'])) {
		$offset = $_POST['offset'];
	} else {
		$offset = get_option('posts_per_page');
	}

//	$select_style = $type;

	global $sidebar_thumb_size;
	$sidebar_thumb_size = $size;

	// The Query
	if ($cat == "all")
		query_posts("posts_per_page=$default_posts_per_page&offset=$offset&orderby=date");
	else
		query_posts("cat=$cat&posts_per_page=$default_posts_per_page&offset=$offset&orderby=date");

	$arr = array();
	$arr['data'] = "";
	$arr['offset'] = get_option( 'posts_per_page' );

	if ($cat == "all") {
		$post_count = wp_count_posts()->publish;
	}else {
		$cate = get_category($cat);
		$post_count = $cate->category_count;
	}

	if ($post_count <= ($offset + get_option( 'posts_per_page' )))
		$arr['next_post'] = false;
	else
		$arr['next_post'] = true;

	ob_start();
	// The Loop
	//$date_posts = array();
	while (have_posts()) : the_post();
		$date_post = get_the_date( 'F Y' );
		if ( !in_array( $date_post, $date_posts ) ) {
			$date_posts[] = $date_post;
			echo '<article class="time-line full month-year" id="date-' . str_replace( ' ', '-', $date_post ) . '"><h4><span>' . $date_post . '</span></h4></article>';
		};
		get_template_part( 'inc/blog-templates/content', 'timeline' );
	endwhile;
	$arr['data'] .= ob_get_contents();
	$arr['date_post']=base64_encode(json_encode($date_posts));
	ob_end_clean();

	// Reset Query
	wp_reset_query();

	wp_send_json($arr);
}
/* End Blog btn Paging */

function thim_plugin_active( $plugin ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( $plugin ) ) {
		return true;
	}

	return false;
}