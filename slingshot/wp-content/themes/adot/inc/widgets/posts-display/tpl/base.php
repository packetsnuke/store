<?php
global $theme_options_data;
$custom_font_size = $width = $des = $layout = '';

$number_posts = 4;
if ( $instance['number_posts'] <> '' ) {
	$number_posts = $instance['number_posts'];
}
if ( $instance['layout'] <> '' ) {
	$layout = $instance['layout'];
}
$query_args = array(
	'posts_per_page' => $number_posts,
	'order'          => $instance['order'] == 'asc' ? 'asc' : 'desc',
);
switch ( $instance['orderby'] ) {
	case 'recent' :
		$query_args['orderby'] = 'post_date';
		break;
	case 'title' :
		$query_args['orderby'] = 'post_title';
		break;
	case 'popular' :
		$query_args['orderby'] = 'comment_count';
		break;
	default : //random
		$query_args['orderby'] = 'rand';
}

$posts_display = new WP_Query( $query_args );
$post_format   = $style_title = '';
$border_title = $instance['border_title'];
if( ! $border_title ){
	$style_title = 'style="border: 0;"';
}

if ( $posts_display->have_posts() ) {
	$img_size = "full";
	echo '<div class="kbm-recent-article ' . esc_attr( $layout ) . '">
	<div class="box kuler-module">';
	if ( $instance['title'] ) {
		echo '<div class="box-heading"><span ' . $style_title . '>' . esc_attr( $instance['title'] ) . '</span></div>';
	}
	echo '<ul class="articles">';
	while ( $posts_display->have_posts() ) {
		$posts_display->the_post();
		$format = $post_format = get_post_format();
		$p_format = 'format-' . $format;
		if ( $instance['column'] ) {
			$column = 12 / $instance['column'];
			$col    = ' col-md-' . $column . ' col-sm-6';
		} else {
			$col = " col-md-3 col-sm-6";
		}
		if ( false === $format ) {
			$format = 'format-standard' . $col;
		} else {
			$format = 'format-' . $format . $col;
		}
		if ( $layout == 'layout-1' ) {
			echo '<li class="wow fadeInRight ' . $format . '">';
			$attr = array(
				'title' => get_the_title(),
				'alt'   => get_the_title()
			);

			switch ( get_post_format() ) {
				case 'video':
					echo '<div class="wrapper-video">';
					do_action( 'thim_entry_top', $img_size );
					echo '</div>';
					echo '<h3 class="post-title"><a href="' . esc_url( get_permalink( get_the_ID() ) ) . '" class="article-title">' . esc_attr( get_the_title() ) . '</a></h3>';
					echo '<div class="entry-meta">';
					if ( isset( $instance['show_author'] ) && $instance['show_author'] == true ) :
						?>
						<span class="author vcard"><?php echo _e( 'by ', 'thim' ); ?>
							<?php
							printf( '<a href="%1$s" rel="author">%2$s</a>',
								esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
								esc_html( get_the_author() )
							);
							?>
						</span>
					<?php
					endif;
					?>
					<?php
					if ( isset( $instance['show_date'] ) && $instance['show_date'] == true ) :
						?>
						<span class="entry-date"><?php echo _e( 'on', 'thim' ); ?>
							<?php the_time( $theme_options_data['thim_date_format'] ); ?>
						</span>
					<?php endif; ?>
					<?php
					if ( isset( $instance['show_comment'] ) && $instance['show_comment'] == true ) :
						?>
						<?php if ( !post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
						<span class="comment-total">
								<?php comments_popup_link( __( '0 Comment', 'thim' ), __( '1 Comment', 'thim' ), __( '% Comments', 'thim' ) ); ?>
							</span>
					<?php
					endif;
						?>
						<div class="clear"></div>
					<?php endif; ?>
					<?php
					echo '</div>';
					$length = $instance['excerpt_words'];
					echo '<div class="article-description">' . excerpt( $length ) . '</div>';
					if ( $instance['hide_read_more'] == 1 ) {
						echo '<a href="' . esc_url( get_permalink( get_the_ID() ) ) . '" class="button"><span>' . __( 'Read More', 'thim' ) . '</span></a>';
					}
					break;
				case 'audio':
					wp_enqueue_script( 'thim-jplayer' );
					wp_enqueue_style( 'thim-pixel-industry' );
					echo '<h3 class="post-title"><a href="' . esc_url( get_permalink( get_the_ID() ) ) . '" class="article-title">' . esc_attr( get_the_title() ) . '</a></h3>';
					echo '<div class="entry-meta">';
					if ( isset( $instance['show_author'] ) && $instance['show_author'] == 1 ) :
						?>
						<span class="author vcard"><?php echo _e( 'by ', 'thim' ); ?>
							<?php
							printf( '<a href="%1$s" rel="author">%2$s</a>',
								esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
								esc_html( get_the_author() )
							);
							?>
						</span>
					<?php
					endif;
					?>
					<?php
					if ( isset( $instance['show_date'] ) && $instance['show_date'] == 1 ) :
						?>
						<span class="entry-date"><?php echo _e( 'on', 'thim' ); ?>
							<?php the_time( $theme_options_data['thim_date_format'] ); ?>
						</span>
					<?php endif; ?>
					<?php
					if ( isset( $instance['show_comment'] ) && $instance['show_comment'] == 1 ) :
						?>
						<?php if ( !post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
						<span class="comment-total">
								<?php comments_popup_link( __( '0 Comment', 'thim' ), __( '1 Comment', 'thim' ), __( '% Comments', 'thim' ) ); ?>
							</span>
					<?php
					endif;
						?>
						<div class="clear"></div>
					<?php endif; ?>
					<?php
					echo '</div>';
					$length = $instance['excerpt_words'];
					echo '<div class="article-description">' . excerpt( $length ) . '</div>';
					echo '<div class="wrapper-audio">';
					do_action( 'thim_entry_top', $img_size );
					echo '</div>';
					if ( $instance['hide_read_more'] == 1 ) {
						echo '<a href="' . esc_url( get_permalink( get_the_ID() ) ) . '" class="button"><span>' . __( 'Read More', 'thim' ) . '</span></a>';
					}
					break;
				default:
					do_action( 'thim_entry_top', $img_size );
					echo '<h3 class="post-title"><a href="' . esc_url( get_permalink( get_the_ID() ) ) . '" class="article-title">' . esc_attr( get_the_title() ) . '</a></h3>';
					echo '<div class="entry-meta">';
					if ( isset( $instance['show_author'] ) && $instance['show_author'] == 1 ) :
						?>
						<span class="author vcard"><?php echo _e( 'by ', 'thim' ); ?>
							<?php
							printf( '<a href="%1$s" rel="author">%2$s</a>',
								esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
								esc_html( get_the_author() )
							);
							?>
						</span>
					<?php
					endif;
					?>
					<?php
					if ( isset( $instance['show_date'] ) && $instance['show_date'] == 1 ) :
						?>
						<span class="entry-date"><?php echo _e( 'on', 'thim' ); ?>
							<?php the_time( $theme_options_data['thim_date_format'] ); ?>
						</span>
					<?php endif; ?>
					<?php
					if ( isset( $instance['show_comment'] ) && $instance['show_comment'] == 1 ) :
						?>
						<?php if ( !post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
						<span class="comment-total">
								<?php comments_popup_link( __( '0 Comment', 'thim' ), __( '1 Comment', 'thim' ), __( '% Comments', 'thim' ) ); ?>
							</span>
					<?php
					endif;
						?>
						<div class="clear"></div>
					<?php endif; ?>
					<?php
					echo '</div>';
					$length = $instance['excerpt_words'];
					echo '<div class="article-description">' . excerpt( $length ) . '</div>';
					if ( $instance['hide_read_more'] == 1 ) {
						echo '<a href="' . esc_url( get_permalink( get_the_ID() ) ) . '" class="button"><span>' . __( 'Read More', 'thim' ) . '</span></a>';
					}
			}
			echo '</li>';
		} else {
			echo '<li class="' . $p_format . '">';
			echo '<h3 class="post-title"><a href="' . esc_url( get_permalink( get_the_ID() ) ) . '" class="article-title">' . esc_attr( get_the_title() ) . '</a></h3>';
			echo '<div class="entry-meta">';
			if ( isset( $instance['show_author'] ) && $instance['show_author'] == 1 ) :
				?>
				<span class="author vcard"><?php echo _e( 'by ', 'thim' ); ?>
					<?php
					printf( '<a href="%1$s" rel="author">%2$s</a>',
						esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
						esc_html( get_the_author() )
					);
					?>
						</span>
			<?php
			endif;
			?>
			<?php
			if ( isset( $instance['show_date'] ) && $instance['show_date'] == 1 ) :
				?>
				<span class="entry-date"><?php echo _e( 'on', 'thim' ); ?>
					 <?php the_time( $theme_options_data['thim_date_format'] ); ?>
						</span>
			<?php endif; ?>
			<?php
			if ( isset( $instance['show_comment'] ) && $instance['show_comment'] == 1 ) :
				?>
				<?php if ( !post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
				<span class="comment-total">
								<?php comments_popup_link( __( '0 Comment', 'thim' ), __( '1 Comment', 'thim' ), __( '% Comments', 'thim' ) ); ?>
							</span>
			<?php
			endif;
				?>
				<div class="clear"></div>
			<?php endif; ?>
			<?php
			echo '</div>';
			$length = $instance['excerpt_words'];
			if ( $length > 0 ) {
				echo '<div class="article-description">' . excerpt( $length ) . '</div>';
			}
			if ( $instance['hide_read_more'] == 1 ) {
				echo '<a href="' . esc_url( get_permalink( get_the_ID() ) ) . '" class="button"><span>' . __( 'Detail', 'thim' ) . '</span></a>';
			}
			echo '<hr />';
			echo '</li>';
		}
		//echo '<div class="item">';
	}
	wp_reset_postdata();
	echo '</ul>';
	echo '</div>';
	if( isset( $instance['text_view_all']  ) && $instance['text_view_all'] != '' ){
		echo '<div class="view-all-blog"> <a href="' . $instance['link_view_all'] . ' " class="thim-link"> ' . $instance['text_view_all'] . ' </a></div>';
	}
	echo '</div>';

}
?>