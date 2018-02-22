<?php
/**
 * @package thim
 */
global $theme_options_data;
$classes                 = array();
$classes[]               = 'article';
$style_layout_front_page = 'cat-style-1';

if ( isset( $theme_options_data['thim_front_page_style_layout'] ) ) {
	$style_layout_front_page = 'cat-' . $theme_options_data['thim_front_page_style_layout'];
}
$classes[] = $style_layout_front_page;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $classes ); ?>>
	<?php
	$sidebar_thumb_size = 'full';
	if ( has_post_format( 'link' ) && thim_meta( 'thim_url' ) && thim_meta( 'thim_text' ) ) {
		?>
		<div class="article-header">
			<div class="entry-meta">
				<?php thim_posted_on_v1(); ?>
			</div>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
		</div>
		<?php
		$url  = thim_meta( 'thim_url' );
		$text = thim_meta( 'thim_text' );

		if ( $url && $text ) {
			echo '<div class="article-content">
						<a class="link" href="' . esc_url( $url ) . '">' . esc_attr( $text ) . '</a>
					</div>';
		}
	} elseif ( has_post_format( 'quote' ) && thim_meta( 'thim_quote' ) && thim_meta( 'thim_author_url' ) ) {
		$quote      = thim_meta( 'thim_quote' );
		$author     = thim_meta( 'thim_author' );
		$author_url = thim_meta( 'thim_author_url' );
		if ( $author_url ) {
			$author = ' <a href=' . esc_url( $author_url ) . '>' . $author . '</a>';
		}
		if ( $quote && $author ) {
			echo '
					<header class="entry-header">
					<div class="box-header box-quote">
						<blockquote>' . $quote . '<cite>' . $author . '</cite></blockquote>
					</div>
					</header>
					';
		}
	} //elseif ( has_post_format( 'audio' ) ) {
//        echo '
//					 <header class="entry-header">
// 						<h3 class="blog_title"><a href="' . esc_url( get_permalink( get_the_ID() ) ) . '" rel="bookmark">' . esc_attr( get_the_title( get_the_ID() ) ) . '</a></h3>
// 					</header>
//				 ';
	//}
	else {
		?>
		<div class="article-header">
			<div class="entry-meta">
				<?php thim_posted_on_v1(); ?>
			</div>
			<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
		</div>
		<?php
		do_action( 'thim_entry_top', $sidebar_thumb_size );
		?>

		<div class="article-content">
			<?php
			global $theme_options_data;
			$length = '50';
			if ( isset( $theme_options_data['thim_archive_excerpt_length'] ) ) {
				$length = $theme_options_data['thim_archive_excerpt_length'];
			}
			echo excerpt( $length ) . '... ';
			?>

		</div>

		<div class="article-footer">
			<?php thim_posted_on_footer(); ?>
			<div class="article-read-more">
				<a href="<?php the_permalink(); ?>" class="read-more"><?php echo _e( 'Continue reading', 'thim' ); ?>
				<span class="arrow">
 					<svg xmlns="http://www.w3.org/2000/svg" width="34" height="12" viewBox="-30 0 52 12">
						<path d="M22 6l-6-6v5h-46v2h46v5l6-6z"></path>
					</svg>
				</span>
				</a>
			</div>
			<div class="clear-article"></div>
		</div><!--end article-footer-->

	<?php } ?>

</article><!-- #post-## -->