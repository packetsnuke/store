<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package thim
 */
if ( !function_exists( 'thim_paging_nav' ) ) :

	/**
	 * Display navigation to next/previous set of posts when applicable.
	 */
	function thim_paging_nav() {
		if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
			return;
		}
		$paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
		$pagenum_link = html_entity_decode( get_pagenum_link() );

		$query_args = array();
		$url_parts  = explode( '?', $pagenum_link );

		if ( isset( $url_parts[1] ) ) {
			wp_parse_str( $url_parts[1], $query_args );
		}

		$pagenum_link = esc_url( remove_query_arg( array_keys( $query_args ), $pagenum_link ) );
		$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

		$format = $GLOBALS['wp_rewrite']->using_index_permalinks() && !strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
		$format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';

		// Set up paginated links.
		$links = paginate_links( array(
			'base'      => $pagenum_link,
			'format'    => $format,
			'total'     => $GLOBALS['wp_query']->max_num_pages,
			'current'   => $paged,
			'mid_size'  => 1,
			'add_args'  => array_map( 'urlencode', $query_args ),
			'prev_text' => __( '<i class="fa fa-long-arrow-left"></i>', 'thim' ),
			'next_text' => __( '<i class="fa fa-long-arrow-right"></i>', 'thim' ),
			'type'      => 'list'
		) );

		if ( $links ) :
			?>
			<div class="pagination loop-pagination">
				<?php //echo '<span> Page </span>'
				?>
				<?php echo ent2ncr( $links ); ?>
			</div>
			<!-- .pagination -->
		<?php
		endif;
	}
endif;

if ( !function_exists( 'thim_post_nav' ) ) :

	/**
	 * Display navigation to next/previous post when applicable.
	 */
	function thim_post_nav() {
		// Don't print empty markup if there's nowhere to navigate.
		$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
		$next     = get_adjacent_post( false, '', false );

		if ( !$next && !$previous ) {
			return;
		}
		?>
		<nav class="navigation post-navigation" role="navigation">
			<div class="nav-links">
				<?php
				previous_post_link( '<div class="nav-previous">%link</div>', _x( '<span class="arrow"><svg xmlns="http://www.w3.org/2000/svg" width="42" height="12" viewBox="-1 0 52 12"><path fill="#333333 " d="M0 6l6 6V7h46V5H6V0L0 6z"></path></svg></span>PREVIOUS', 'Previous post link', 'thim' ) );
				next_post_link( '<div class="nav-next">%link</div>', _x( 'Next<span class="arrow"><svg xmlns="http://www.w3.org/2000/svg" width="42" height="12" viewBox="-30 0 52 12"><path fill="#333333 " d="M22 6l-6-6v5h-46v2h46v5l6-6z"></path></svg></span>
', 'Next post link', 'thim' ) );
				?>
				<div class="clear"></div>
			</div>
			<!-- .nav-links -->
		</nav><!-- .navigation -->
	<?php
	}

endif;

if ( !function_exists( 'thim_posted_on_v2' ) ) :

	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function thim_posted_on_v2() {
		global $theme_options_data;

		if ( !isset( $theme_options_data['thim_show_date'] ) ) {
			$theme_options_data['thim_show_date']   = 1;
			$theme_options_data['thim_date_format'] = "F j, Y";
		}
		if ( !isset( $theme_options_data['thim_show_author'] ) ) {
			$theme_options_data['thim_show_author'] = 1;
		}
		if ( !isset( $theme_options_data['thim_show_tag'] ) ) {
			$theme_options_data['thim_show_tag'] = 1;
		}
		if ( !isset( $theme_options_data['thim_show_category'] ) ) {
			$theme_options_data['thim_show_category'] = 1;
		}
		if ( !isset( $theme_options_data['thim_show_comment'] ) ) {
			$theme_options_data['thim_show_comment'] = 1;
		}
		?>
		<div class="article-extra-info">
			<?php
			if ( isset( $theme_options_data['thim_show_category'] ) && $theme_options_data['thim_show_category'] ) {
				?>
				<span class="entry-post-fomat ">
					<?php $categories = get_the_category();
					foreach ( $categories as $category ) {
						$bg_color  = get_tax_meta( $category->term_id, 'thim_cat_bg_cat_color', true );
						$cat_style = 'style="background: ' . $bg_color . ';"';
						echo '<a class="post-item" ' . $cat_style . ' href="' . get_category_link( $category->term_id ) . '">' . $category->cat_name . '</a>';
					} ?>
				</span>
			<?php
			}
			?>

			<?php if ( isset( $theme_options_data['thim_show_author'] ) && $theme_options_data['thim_show_author'] == 1 ) {
				?>
				<span class="author vcard"><?php echo _e( 'by ', 'thim' ); ?><?php printf( '<a href="%1$s" rel="author">%2$s</a>',
						esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
						esc_html( get_the_author() )
					); ?></span>
			<?php
			}
			if ( isset( $theme_options_data['thim_show_date'] ) && $theme_options_data['thim_show_date'] == 1 ) {
				?>
				<span class="entry-date"><?php echo _e( 'on', 'thim' ); ?>
					<time> <?php the_time( $theme_options_data['thim_date_format'] ); ?></time>
                    </span>
			<?php
			}
			?>
			<?php

			if ( isset( $theme_options_data['thim_show_comment'] ) && $theme_options_data['thim_show_comment'] == 1 ) {
				?>
				<?php if ( !post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) :
					?>
					<span class="comment-total">
						<?php comments_popup_link( __( '0 Comment', 'thim' ), __( '1 Comment', 'thim' ), __( '% Comments', 'thim' ) ); ?>
					</span>
				<?php
				endif;
			}
			?>
			<?php
			if ( isset( $theme_options_data['thim_show_tag'] ) && $theme_options_data['thim_show_tag'] == 1 ) {
				?>
				<span class="link-tag"><?php the_tags( 'Tags: ', ', ', ' ' ); ?></span>
			<?php
			}
			?>
		</div>
	<?php
	}
endif;
//thim_post_on Style 1
if ( !function_exists( 'thim_posted_on_v1' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function thim_posted_on_v1() {
		global $theme_options_data;

		if ( !isset( $theme_options_data['thim_show_date'] ) ) {
			$theme_options_data['thim_show_date']   = 1;
			$theme_options_data['thim_date_format'] = "F j, Y";
		}
		if ( !isset( $theme_options_data['thim_show_author'] ) ) {
			$theme_options_data['thim_show_author'] = 1;
		}
		if ( !isset( $theme_options_data['thim_show_avata'] ) ) {
			$theme_options_data['thim_show_avata'] = 1;
		}
		if ( !isset( $theme_options_data['show_date_v1'] ) ) {
			$theme_options_data['show_date_v1'] = 1;
		}
		?>
		<span class="posted-on">
		<?php if ( isset( $theme_options_data['thim_show_author'] ) && $theme_options_data['thim_show_author'] == 1 ) {
			?>
			<span class="author vcard"><?php echo _e( 'by ', 'thim' ); ?><?php printf( '<a href="%1$s" rel="author">%2$s</a>',
					esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
					esc_html( get_the_author() )
				); ?></span>
			<?php
			echo '</span>';
		}
		?>
		<?php if ( isset( $theme_options_data['thim_show_avata'] ) && $theme_options_data['thim_show_avata'] == 1 ) {
			?>
			<span class="author-avata">
				<?php
				echo get_avatar( get_the_author_meta( 'ID' ), 46 );
				?>
			</span>
		<?php
		}
		?>

		<?php if ( isset( $theme_options_data['thim_show_date'] ) && $theme_options_data['thim_show_date'] == 1 ) {
			?>
			<span class="byline">
				<span class="entry-date"><?php echo _e( 'on', 'thim' ); ?>
					<time> <?php the_time( $theme_options_data['thim_date_format'] ); ?></time>
                    </span>
			</span>
		<?php
		}

	}

endif;

if ( !function_exists( 'thim_posted_on' ) ) :

	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function thim_posted_on() {
		global $theme_options_data;
		if ( !isset( $theme_options_data['thim_show_date'] ) ) {
			$theme_options_data['thim_show_date']   = 1;
			$theme_options_data['thim_date_format'] = "F j, Y";
		}
		if ( !isset( $theme_options_data['thim_show_author'] ) ) {
			$theme_options_data['thim_show_author'] = 1;
		}
		if ( !isset( $theme_options_data['thim_show_avata'] ) ) {
			$theme_options_data['thim_show_avata'] = 1;
		}
		if ( !isset( $theme_options_data['thim_show_comment'] ) ) {
			$theme_options_data['thim_show_comment'] = 1;
		}
		if ( !isset( $theme_options_data['thim_show_category'] ) ) {
			$theme_options_data['thim_show_category'] = 1;
		}
		if ( !isset( $theme_options_data['thim_show_tag'] ) ) {
			$theme_options_data['thim_show_tag'] = 1;
		}

		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf( $time_string, esc_attr( get_the_date( 'c' ) ), esc_html( get_the_date() ), esc_attr( get_the_modified_date( 'c' ) ), esc_html( get_the_modified_date() )
		);

		$posted_on = sprintf(
			_x( ' on %s', 'post date', 'thim' ), '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
		);

		$byline = sprintf(
			_x( 'by %s', 'post author', 'thim' ), '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);


		echo '<span class="posted-on">' . $byline . '</span><span class="author-avata">' . get_avatar( get_the_author_meta( 'ID' ), 46 ) . '</span> <span class="byline"> ' . $posted_on . '</span>';
	}

endif;

if ( !function_exists( 'thim_posted_on_v3' ) ) :

	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function thim_posted_on_v3() {
		global $theme_options_data;
		if ( !isset( $theme_options_data['thim_show_author'] ) ) {
			$theme_options_data['thim_show_author'] = 1;
		}
		if ( !isset( $theme_options_data['thim_show_category'] ) ) {
			$theme_options_data['thim_show_category'] = 1;
		}

		$byline = sprintf(
			_x( 'by %s', 'post author', 'thim' ), '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
		);
		?>
		<?php if ( isset( $theme_options_data['thim_show_author'] ) && $theme_options_data['thim_show_author'] == 1 ) { ?>
			<span class="posted-on"><?php echo ent2ncr( $byline ) ?></span>
		<?php
		}
		?>
		<?php if ( isset( $theme_options_data['thim_show_category'] ) && $theme_options_data['thim_show_category'] == 1 ) { ?>
			<span class="link-category"> in <?php the_category( ', ', '' ) ?> </span>
		<?php
		}
		?>

	<?php
	}

endif;

if ( !function_exists( 'thim_posted_on_date_v3' ) ) :

	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function thim_posted_on_date_v3() {
		global $theme_options_data;
		if ( !isset( $theme_options_data['thim_show_date'] ) ) {
			$theme_options_data['thim_show_date']   = 1;
			$theme_options_data['thim_date_format'] = "F j, Y";
		}
		?>
		<?php if ( isset( $theme_options_data['thim_show_date'] ) && $theme_options_data['thim_show_date'] == 1 ) { ?>
			<div class="entry-meta">
				<time>
					<span class="day"><?php the_time( 'd' ) ?></span>
					<span class="month"><?php the_time( 'M Y' ) ?></span>
				</time>
			</div>
		<?php
		}
	}

endif;

if ( !function_exists( 'thim_posted_on_comment_v3' ) ) :

	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function thim_posted_on_comment_v3() {
		global $theme_options_data;
		?>
		<div class="article-extra-info">
			<?php
			if ( isset( $theme_options_data['thim_show_comment'] ) && $theme_options_data['thim_show_comment'] == 1 ) {
				?>
				<?php if ( !post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) :
					?>
					<span class="comment-total">
						<?php comments_popup_link( __( '0 Comment', 'thim' ), __( '1 Comment', 'thim' ), __( '% Comments', 'thim' ) ); ?>
					</span>
				<?php
				endif;
			}
			?>
		</div>
	<?php
	}
endif;

if ( !function_exists( 'thim_posted_on_footer' ) ) :

	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function thim_posted_on_footer() {
		if ( !post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) :
			?>
			<span class="comment-total">
				<?php comments_popup_link( __( '0 comment', 'thim' ), __( '1 comment', 'thim' ), __( '% comments', 'thim' ) ); ?>
			</span>
		<?php
		endif;
	}
endif;

if ( !function_exists( 'thim_posted_on_v4' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function thim_posted_on_v4() {
		global $theme_options_data;

		if ( !isset( $theme_options_data['thim_show_date'] ) ) {
			$theme_options_data['thim_show_date']   = 1;
			$theme_options_data['thim_date_format'] = "F j, Y";
		}
		if ( !isset( $theme_options_data['thim_show_author'] ) ) {
			$theme_options_data['thim_show_author'] = 1;
		}
		if ( !isset( $theme_options_data['thim_show_comment'] ) ) {
			$theme_options_data['thim_show_comment'] = 1;
		}
		?>
		<div class="meta">
		<span class="posted-on">
		<?php if ( isset( $theme_options_data['thim_show_author'] ) && $theme_options_data['thim_show_author'] == 1 ) {
			?>
			<span class="author vcard"><?php echo _e( 'by ', 'thim' ); ?><?php printf( '<a href="%1$s" rel="author">%2$s</a>',
					esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
					esc_html( get_the_author() )
				); ?></span>
			<?php
			echo '</span>';
		}
		?>
			<?php if ( isset( $theme_options_data['thim_show_date'] ) && $theme_options_data['thim_show_date'] == 1 ) {
				?>
				<span class="byline">
				<span class="entry-date"><?php echo _e( 'on', 'thim' ); ?>
					<time> <?php the_time( $theme_options_data['thim_date_format'] ); ?></time>
                    </span>
			</span>
			<?php
			}
			?>
		</div>
		<div class="article-extra-info">
			<?php
			if ( isset( $theme_options_data['thim_show_comment'] ) && $theme_options_data['thim_show_comment'] == 1 ) {
				?>
				<?php if ( !post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) :
					?>
					<span class="comment-total">
						<?php comments_popup_link( __( '0 Comment', 'thim' ), __( '1 Comment', 'thim' ), __( '% Comments', 'thim' ) ); ?>
					</span>
				<?php
				endif;
			}
			?>
		</div>
	<?php

	}

endif;


if ( !function_exists( 'thim_posted_on_v5' ) ) :
	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function thim_posted_on_v5() {
		global $theme_options_data;

		if ( !isset( $theme_options_data['thim_show_date'] ) ) {
			$theme_options_data['thim_show_date']   = 1;
			$theme_options_data['thim_date_format'] = "F j, Y";
		}
		if ( !isset( $theme_options_data['thim_show_author'] ) ) {
			$theme_options_data['thim_show_author'] = 1;
		}
		if ( !isset( $theme_options_data['thim_show_comment'] ) ) {
			$theme_options_data['thim_show_comment'] = 1;
		}
		?>
		<div class="meta">
		<span class="posted-on">
		<?php if ( isset( $theme_options_data['thim_show_author'] ) && $theme_options_data['thim_show_author'] == 1 ) {
			?>
			<span class="author vcard"><?php echo _e( 'Posted ', 'thim' ); ?><?php printf( '<a href="%1$s" rel="author">%2$s</a>',
					esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
					esc_html( get_the_author() )
				); ?></span>
			<?php
			echo '</span>';
		}
		?>
			<?php if ( isset( $theme_options_data['thim_show_date'] ) && $theme_options_data['thim_show_date'] == 1 ) {
				?>
				<span class="byline">
				<span class="entry-date"><?php echo _e( 'on', 'thim' ); ?>
					<time> <?php the_time( $theme_options_data['thim_date_format'] ); ?></time>
                    </span>
			</span>
			<?php
			}
			?>
		</div>
		<div class="article-extra-info">
			<?php
			if ( isset( $theme_options_data['thim_show_comment'] ) && $theme_options_data['thim_show_comment'] == 1 ) {
				?>
				<?php if ( !post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) :
					?>
					<span class="comment-total">
						<?php comments_popup_link( __( '0 Comment', 'thim' ), __( '1 Comment', 'thim' ), __( '% Comments', 'thim' ) ); ?>
					</span>
				<?php
				endif;
			}
			?>
		</div>
	<?php

	}

endif;

if ( !function_exists( 'thim_posted_on_single' ) ) :

	/**
	 * Prints HTML with meta information for the current post-date/time and author.
	 */
	function thim_posted_on_single() {
		global $theme_options_data;

		if ( !isset( $theme_options_data['thim_show_date'] ) ) {
			$theme_options_data['thim_show_date']    = 1;
			$theme_options_data['thim_show_comment'] = 1;
			$theme_options_data['thim_date_format']  = "F j, Y";
		}
		if ( !isset( $theme_options_data['thim_show_author'] ) ) {
			$theme_options_data['thim_show_author']   = 1;
			$theme_options_data['thim_show_category'] = 1;
		}
		?>
		<div class="article-extra-info">
			<?php
		if ( isset( $theme_options_data['thim_single_show_date'] ) && $theme_options_data['thim_single_show_date'] == 1 ) {
			?>
			<span class="entry-date"><?php //echo _e( 'on', 'thim' ); ?>
				<time> <?php the_time( $theme_options_data['thim_date_format'] ); ?></time>
                    </span>
		<?php
		}

		if ( isset( $theme_options_data['thim_single_show_category'] ) && $theme_options_data['thim_single_show_category'] == 1 ) {
			?>
			<span class="link-category"><?php the_category( ',', '' ); ?></span>
		<?php
		}

		if ( isset( $theme_options_data['thim_single_show_author'] ) && $theme_options_data['thim_single_show_author'] == 1 ) {
			?>
			<span class="author vcard"></span><?php printf( '<a href="%1$s" rel="author">%2$s</a>',
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				esc_html( get_the_author() )
			); ?>
		<?php
		}
		if ( isset( $theme_options_data['thim_single_show_comment'] ) && $theme_options_data['thim_single_show_comment'] == 1 ) {
			?>
			<?php if ( !post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) :
				?>
				<span class="comment-total">
						<?php comments_popup_link( __( '0', 'thim' ), __( '1', 'thim' ), __( '%', 'thim' ) ); ?>
					</span>
			<?php
			endif;
		}
		?>
		</div>
	<?php
	}
endif;

if ( !function_exists( 'thim_entry_footer' ) ) :

	/**
	 * Prints HTML with meta information for the categories, tags and comments.
	 */
	function thim_entry_footer() {
		// Hide category and tag text for pages.
		if ( 'post' == get_post_type() ) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list( __( ', ', 'thim' ) );
			if ( $categories_list && thim_categorized_blog() ) {
				printf( '<span class="cat-links">' . __( 'Posted in %1$s', 'thim' ) . '</span>', $categories_list );
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list( '', __( ', ', 'thim' ) );
			if ( $tags_list ) {
				printf( '<span class="tags-links">' . __( 'Tagged %1$s', 'thim' ) . '</span>', $tags_list );
			}
		}

		if ( !is_single() && !post_password_required() && ( comments_open() || get_comments_number() ) ) {
			echo '<span class="comments-link">';
			comments_popup_link( __( 'Leave a comment', 'thim' ), __( '1 Comment', 'thim' ), __( '% Comments', 'thim' ) );
			echo '</span>';
		}

		edit_post_link( __( 'Edit', 'thim' ), '<span class="edit-link">', '</span>' );
	}

endif;

if ( !function_exists( 'the_archive_title' ) ) :

	/**
	 * Shim for `the_archive_title()`.
	 *
	 * Display the archive title based on the queried object.
	 *
	 *
	 * @param string $before Optional. Content to prepend to the title. Default empty.
	 * @param string $after  Optional. Content to append to the title. Default empty.
	 */
	function the_archive_title( $before = '', $after = '' ) {
		if ( is_category() ) {
			$title = sprintf( __( '%s', 'thim' ), single_cat_title( '', false ) );
		} elseif ( is_tag() ) {
			$title = sprintf( __( 'Tag: %s', 'thim' ), single_tag_title( '', false ) );
		} elseif ( is_author() ) {
			$title = sprintf( __( 'Author: %s', 'thim' ), '<span class="vcard">' . get_the_author() . '</span>' );
		} elseif ( is_year() ) {
			$title = sprintf( __( 'Year: %s', 'thim' ), get_the_date( _x( 'Y', 'yearly archives date format', 'thim' ) ) );
		} elseif ( is_month() ) {
			$title = sprintf( __( 'Month: %s', 'thim' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'thim' ) ) );
		} elseif ( is_day() ) {
			$title = sprintf( __( 'Day: %s', 'thim' ), get_the_date( _x( 'F j, Y', 'daily archives date format', 'thim' ) ) );
		} elseif ( is_tax( 'post_format', 'post-format-aside' ) ) {
			$title = _x( 'Asides', 'post format archive title', 'thim' );
		} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
			$title = _x( 'Galleries', 'post format archive title', 'thim' );
		} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
			$title = _x( 'Images', 'post format archive title', 'thim' );
		} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
			$title = _x( 'Videos', 'post format archive title', 'thim' );
		} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
			$title = _x( 'Quotes', 'post format archive title', 'thim' );
		} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
			$title = _x( 'Links', 'post format archive title', 'thim' );
		} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
			$title = _x( 'Statuses', 'post format archive title', 'thim' );
		} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
			$title = _x( 'Audio', 'post format archive title', 'thim' );
		} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
			$title = _x( 'Chats', 'post format archive title', 'thim' );
		} elseif ( is_post_type_archive() ) {
			$title = sprintf( __( '%s', 'thim' ), post_type_archive_title( '', false ) );
		} elseif ( is_tax() ) {
			$tax = get_taxonomy( get_queried_object()->taxonomy );
			/* translators: 1: Taxonomy singular name, 2: Current taxonomy term */
			$title = sprintf( __( '%1$s: %2$s', 'thim' ), $tax->labels->singular_name, single_term_title( '', false ) );
		}  else {
			$title = __( 'Archives', 'thim' );
		}

		/**
		 * Filter the archive title.
		 *
		 * @param string $title Archive title to be displayed.
		 */
		$title = apply_filters( 'get_the_archive_title', $title );

		if ( !empty( $title ) ) {
			echo ent2ncr( $before . $title . $after );
		}
	}

endif;

if ( !function_exists( 'the_archive_description' ) ) :

	/**
	 * Shim for `the_archive_description()`.
	 *
	 * Display category, tag, or term description.
	 *
	 * @todo Remove this function when WordPress 4.3 is released.
	 *
	 * @param string $before Optional. Content to prepend to the description. Default empty.
	 * @param string $after  Optional. Content to append to the description. Default empty.
	 */
	function the_archive_description( $before = '', $after = '' ) {
		$description = apply_filters( 'get_the_archive_description', term_description() );

		if ( !empty( $description ) ) {
			/**
			 * Filter the archive description.
			 *
			 * @see term_description()
			 *
			 * @param string $description Archive description to be displayed.
			 */
			echo ent2ncr( $before . $description . $after );
		}
	}

endif;

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function thim_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'thim_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,
			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'thim_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so thim_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so thim_categorized_blog should return false.
		return false;
	}
}

/**
 * Flush out the transients used in thim_categorized_blog.
 */
function thim_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'thim_categories' );
}

add_action( 'edit_category', 'thim_category_transient_flusher' );
add_action( 'save_post', 'thim_category_transient_flusher' );
